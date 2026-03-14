<?php
/*************************************************
 * DATABASE CORE FILE
 * Secure – Flexible – Reusable
 *************************************************/

declare(strict_types=1);
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/theme.php';

/* =======================
   LOAD ENV
======================= */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

/* =======================
   CONNECT DATABASE
======================= */
try {
    $conn = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

/* =====================================================
   QUERY CORE
===================================================== */

/**
 * Check table existence with request-level cache.
 */
function hasTable(string $table): bool {
    global $conn;

    static $cache = [];
    $table = trim($table);
    if ($table === '') {
        return false;
    }

    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    try {
        $stmt = $conn->prepare(
            "SELECT COUNT(*)
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name"
        );
        $stmt->execute([':table_name' => $table]);
        $cache[$table] = ((int)$stmt->fetchColumn()) > 0;
    } catch (Throwable $e) {
        $cache[$table] = false;
    }

    return $cache[$table];
}

/**
 * Detect MySQL missing-table error.
 */
function isMissingTableError(Throwable $e): bool {
    if (!$e instanceof PDOException) {
        return false;
    }
    return $e->getCode() === '42S02' || str_contains($e->getMessage(), 'Base table or view not found');
}

/**
 * GET DATA (SELECT)
 */
function getData(string $table, array $options = []): array {
    global $conn;

    if (!hasTable($table)) {
        return [];
    }

    if ($table === 'products' && empty($options['skip_event_filter']) && hasProductEventColumn()) {
        if (!isset($options['where']) || !is_array($options['where'])) {
            $options['where'] = [];
        }
        if (!array_key_exists('event_slug', $options['where'])) {
            $activeSaleEventSlug = getActiveSaleEventSlug();
            $options['where']['event_slug'] = $activeSaleEventSlug ?? '__event_closed__';
        }
    }

    $sql = "SELECT " . ($options['select'] ?? '*') . " FROM `$table`";
    $params = [];
    $where = [];

    // WHERE
    if (!empty($options['where'])) {
        foreach ($options['where'] as $col => $val) {
            $key = ":w_$col";
            $where[] = "`$col` = $key";
            $params[$key] = $val;
        }
    }

    // SEARCH
    if (!empty($options['search_keyword']) && !empty($options['search_col'])) {
        $where[] = "`" . $options['search_col'] . "` LIKE :search_kw";
        $params[':search_kw'] = "%" . $options['search_keyword'] . "%";
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    // ORDER BY
    if (!empty($options['order_by'])) {
        $sql .= " ORDER BY " . $options['order_by'];
    }

    // LIMIT & OFFSET
    if (isset($options['limit'])) {
        $sql .= " LIMIT " . (int)$options['limit'];
        if (isset($options['offset'])) {
            $sql .= " OFFSET " . (int)$options['offset'];
        }
    }

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        if (isMissingTableError($e)) {
            return [];
        }
        throw $e;
    }
}

/**
 * GET COUNT
 */
function getCount(string $table, array $options = []): int {
    global $conn;

    if (!hasTable($table)) {
        return 0;
    }

    if ($table === 'products' && empty($options['skip_event_filter']) && hasProductEventColumn()) {
        if (!isset($options['where']) || !is_array($options['where'])) {
            $options['where'] = [];
        }
        if (!array_key_exists('event_slug', $options['where'])) {
            $activeSaleEventSlug = getActiveSaleEventSlug();
            $options['where']['event_slug'] = $activeSaleEventSlug ?? '__event_closed__';
        }
    }

    $sql = "SELECT COUNT(*) FROM `$table`";
    $params = [];
    $where = [];

    // WHERE
    if (!empty($options['where'])) {
        foreach ($options['where'] as $col => $val) {
            $key = ":w_$col";
            $where[] = "`$col` = $key";
            $params[$key] = $val;
        }
    }

    // SEARCH
    if (!empty($options['search_keyword']) && !empty($options['search_col'])) {
        $where[] = "`" . $options['search_col'] . "` LIKE :search_kw";
        $params[':search_kw'] = "%" . $options['search_keyword'] . "%";
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        if (isMissingTableError($e)) {
            return 0;
        }
        throw $e;
    }
}

/**
 * INSERT DATA
 */
function insertData(string $table, array $data): bool {
    global $conn;

    if (!hasTable($table)) {
        return false;
    }

    $fields = array_keys($data);
    $placeholders = array_map(fn($f) => ":$f", $fields);

    $sql = "INSERT INTO `$table` (" . implode(',', $fields) . ")
            VALUES (" . implode(',', $placeholders) . ")";

    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute($data);
    } catch (Throwable $e) {
        if (isMissingTableError($e)) {
            return false;
        }
        throw $e;
    }
}

/**
 * GET LAST INSERT ID
 */
function getLastId(): int {
    global $conn;
    return (int)$conn->lastInsertId();
}

/**
 * UPDATE DATA
 */
function updateData(string $table, array $data, array $where): bool {
    global $conn;

    if (!hasTable($table)) {
        return false;
    }

    $set = [];
    foreach ($data as $col => $val) {
        $set[] = "`$col` = :$col";
    }

    $cond = [];
    foreach ($where as $col => $val) {
        $cond[] = "`$col` = :w_$col";
        $data["w_$col"] = $val;
    }

    $sql = "UPDATE `$table` SET " . implode(',', $set) .
           " WHERE " . implode(' AND ', $cond);

    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute($data);
    } catch (Throwable $e) {
        if (isMissingTableError($e)) {
            return false;
        }
        throw $e;
    }
}

/**
 * DELETE DATA
 */
function deleteData(string $table, array $where): bool {
    global $conn;

    if (!hasTable($table)) {
        return false;
    }

    $cond = [];
    $params = [];

    foreach ($where as $col => $val) {
        $key = ":$col";
        $cond[] = "`$col` = $key";
        $params[$key] = $val;
    }

    $sql = "DELETE FROM `$table` WHERE " . implode(' AND ', $cond);
    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch (Throwable $e) {
        if (isMissingTableError($e)) {
            return false;
        }
        throw $e;
    }
}

/* =====================================================
   FILE UPLOAD (IMAGE ONLY – SECURE)
===================================================== */

function uploadImage(array $file, string $folder = 'assets/uploads/', int $maxSize = 2097152): ?string {
    $allowMime = ['image/jpeg', 'image/png', 'image/webp'];

    if (
        $file['error'] !== UPLOAD_ERR_OK ||
        !in_array(mime_content_type($file['tmp_name']), $allowMime) ||
        $file['size'] > $maxSize
    ) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('img_', true) . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], $folder . $filename)) {
        return null;
    }

    return $filename;
}

/**
 * DELETE IMAGE
 */
function deleteImage(?string $filename, string $folder = 'assets/uploads/'): void {
    if ($filename) {
        $path = $folder . $filename;
        if (is_file($path)) unlink($path);
    }
}

/**
 * UPDATE IMAGE (DELETE OLD)
 */
function updateImage(array $newFile, ?string $oldFile, string $folder = 'assets/uploads/'): ?string {
    $new = uploadImage($newFile, $folder);
    if ($new && $oldFile) {
        deleteImage($oldFile, $folder);
    }
    return $new;
}

/* =====================================================
   AUTH – PASSWORD SECURITY
===================================================== */

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/* =====================================================
   HELPER
===================================================== */

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function isLogin(): bool {
    return isset($_SESSION['user_id']);
}

function formatVND(float|int|string|null $amount): string {
    $value = (float)($amount ?? 0);
    return number_format($value, 0, ',', '.') . ' VNĐ';
}

/* =====================================================
   CART FUNCTIONS
===================================================== */

function addToCart(int $productId, int $quantity = 1): void {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function getCartCount(): int {
    if (!isset($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

function updateCart(int $productId, int $quantity): void {
    if (isset($_SESSION['cart'][$productId])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }
}

function removeFromCart(int $productId): void {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function getActiveEventSlug(): string {
    global $THEME;

    $allowed = ['tet', 'gpmnam', 'quockhanh', 'noel', 'default'];
    $slug = isset($THEME['slug']) ? (string)$THEME['slug'] : 'default';

    if (!in_array($slug, $allowed, true)) {
        return 'default';
    }

    return $slug;
}

function getActiveSaleEventSlug(): ?string {
    static $cached = false;
    static $slug = null;

    if ($cached) {
        return $slug;
    }

    $cached = true;

    $themeSlug = getActiveEventSlug();
    if ($themeSlug !== 'default') {
        $slug = $themeSlug;
        return $slug;
    }

    if (hasEventsTable()) {
        $event = getActiveSaleEvent();
        $slug = !empty($event['slug']) ? (string)$event['slug'] : null;
        return $slug;
    }

    $slug = $themeSlug;
    return $slug;
}

function getActiveSaleEvent(): ?array {
    global $conn;

    static $cached = false;
    static $event = null;

    if ($cached) {
        return $event;
    }

    $cached = true;

    if (!hasEventsTable()) {
        return null;
    }

    try {
        $today = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
        $stmt = $conn->prepare(
            "SELECT id, slug, name, start_date, end_date
             FROM events
             WHERE is_enabled = 1
               AND start_date <= :today
               AND end_date >= :today
             ORDER BY priority DESC, start_date DESC, id DESC
             LIMIT 1"
        );
        $stmt->execute([':today' => $today]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $event = $row ?: null;
    } catch (Throwable $e) {
        $event = null;
    }

    return $event;
}

function hasEventsTable(): bool {
    static $checked = false;
    static $exists = false;

    if ($checked) {
        return $exists;
    }

    $checked = true;

    $exists = hasTable('events');

    return $exists;
}

function hasProductEventColumn(): bool {
    global $conn;

    static $checked = false;
    static $exists = false;

    if ($checked) {
        return $exists;
    }

    $checked = true;

    if (!hasTable('products')) {
        $exists = false;
        return $exists;
    }

    try {
        $stmt = $conn->prepare(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'products'
               AND COLUMN_NAME = 'event_slug'"
        );
        $stmt->execute();
        $exists = ((int)$stmt->fetchColumn()) > 0;
    } catch (Throwable $e) {
        $exists = false;
    }

    return $exists;
}

/**
 * Catalog query helper (single source for listing + pagination count).
 * This keeps product/category data eager-loaded to avoid N+1 patterns.
 */
function getCatalogProducts(array $filters = []): array {
    global $conn;

    if (!hasTable('products')) {
        return [
            'products' => [],
            'total' => 0,
            'total_pages' => 0,
            'page' => max(1, (int)($filters['page'] ?? 1)),
            'limit' => max(1, (int)($filters['limit'] ?? 9)),
        ];
    }

    $categoryId = isset($filters['category_id']) ? (int)$filters['category_id'] : null;
    if ($categoryId !== null && $categoryId <= 0) {
        $categoryId = null;
    }

    $search = trim((string)($filters['search'] ?? ''));
    $sort = (string)($filters['sort'] ?? 'newest');
    $page = max(1, (int)($filters['page'] ?? 1));
    $limit = max(1, (int)($filters['limit'] ?? 9));
    $offset = ($page - 1) * $limit;

    $orderBy = 'p.created_at DESC';
    if ($sort === 'price_asc') {
        $orderBy = 'p.price ASC';
    } elseif ($sort === 'price_desc') {
        $orderBy = 'p.price DESC';
    }

    $whereParts = [];
    $params = [];

    if (hasProductEventColumn()) {
        $eventSlug = getActiveSaleEventSlug();
        if (empty($eventSlug)) {
            return [
                'products' => [],
                'total' => 0,
                'total_pages' => 0,
                'page' => $page,
                'limit' => $limit,
            ];
        }
        $whereParts[] = 'p.event_slug = :event_slug';
        $params[':event_slug'] = $eventSlug;
    }

    if ($categoryId !== null) {
        $whereParts[] = 'p.category_id = :category_id';
        $params[':category_id'] = $categoryId;
    }

    if ($search !== '') {
        $whereParts[] = 'p.name LIKE :search';
        $params[':search'] = '%' . $search . '%';
    }

    $whereSql = !empty($whereParts) ? ' WHERE ' . implode(' AND ', $whereParts) : '';

    $hasCategories = hasTable('categories');
    $categorySelect = $hasCategories ? 'c.name AS category_name' : "NULL AS category_name";
    $categoryJoin = $hasCategories ? 'LEFT JOIN categories c ON c.id = p.category_id' : '';

    try {
        $listSql = "SELECT p.*, $categorySelect
                    FROM products p
                    $categoryJoin
                    $whereSql
                    ORDER BY $orderBy
                    LIMIT $limit OFFSET $offset";
        $listStmt = $conn->prepare($listSql);
        $listStmt->execute($params);
        $products = $listStmt->fetchAll(PDO::FETCH_ASSOC);

        $countSql = "SELECT COUNT(*)
                     FROM products p
                     $whereSql";
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
    } catch (Throwable $e) {
        if (isMissingTableError($e)) {
            $products = [];
            $total = 0;
        } else {
            throw $e;
        }
    }

    return [
        'products' => $products,
        'total' => $total,
        'total_pages' => (int)ceil($total / $limit),
        'page' => $page,
        'limit' => $limit,
    ];
}

/**
 * Build a cart snapshot in one batched product query.
 */
function getCartSnapshot(): array {
    global $conn;

    if (!hasTable('products')) {
        $_SESSION['cart'] = [];
        return ['items' => [], 'total' => 0.0];
    }

    $sessionCart = $_SESSION['cart'] ?? [];
    if (!is_array($sessionCart) || empty($sessionCart)) {
        return ['items' => [], 'total' => 0.0];
    }

    $normalizedCart = [];
    foreach ($sessionCart as $productId => $qty) {
        $id = (int)$productId;
        $quantity = (int)$qty;
        if ($id > 0 && $quantity > 0) {
            $normalizedCart[$id] = $quantity;
        }
    }

    if (empty($normalizedCart)) {
        $_SESSION['cart'] = [];
        return ['items' => [], 'total' => 0.0];
    }

    $ids = array_keys($normalizedCart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $useEventFilter = hasProductEventColumn();
    $params = $ids;

    if ($useEventFilter) {
        $eventSlug = getActiveSaleEventSlug();
        if (empty($eventSlug)) {
            $_SESSION['cart'] = [];
            return ['items' => [], 'total' => 0.0];
        }
        $sql = "SELECT * FROM products WHERE event_slug = ? AND id IN ($placeholders)";
        $params = array_merge([$eventSlug], $ids);
    } else {
        $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
    }

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        if (isMissingTableError($e)) {
            $_SESSION['cart'] = [];
            return ['items' => [], 'total' => 0.0];
        }
        throw $e;
    }

    $productsById = [];
    foreach ($products as $product) {
        $productsById[(int)$product['id']] = $product;
    }

    $items = [];
    $total = 0.0;
    $cleanCart = [];

    foreach ($normalizedCart as $id => $qty) {
        if (!isset($productsById[$id])) {
            continue;
        }
        $product = $productsById[$id];
        $product['qty'] = $qty;
        $product['subtotal'] = (float)$product['price'] * $qty;
        $total += $product['subtotal'];
        $items[] = $product;
        $cleanCart[$id] = $qty;
    }

    $_SESSION['cart'] = $cleanCart;

    return ['items' => $items, 'total' => $total];
}
