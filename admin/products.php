<?php include 'header.php'; ?>

<?php
$eventOptions = [
    'default' => 'Mặc định',
    'tet' => 'Tết Âm lịch',
    'gpmnam' => '30/4 - Giải phóng miền Nam',
    'quockhanh' => '2/9 - Quốc khánh',
    'noel' => 'Noel',
];

if (function_exists('hasEventsTable') && hasEventsTable()) {
    $eventOptions = [];
    $events = getData('events', ['order_by' => 'start_date ASC']);
    foreach ($events as $event) {
        if (!empty($event['slug'])) {
            $eventOptions[(string)$event['slug']] = (string)($event['name'] ?? $event['slug']);
        }
    }
}

if (empty($eventOptions)) {
    $eventOptions = ['default' => 'Không có sự kiện'];
}

// Thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim((string)($_POST['name'] ?? ''));
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $desc = trim((string)($_POST['description'] ?? ''));
    $eventSlug = (string)($_POST['event_slug'] ?? 'default');
    if (!array_key_exists($eventSlug, $eventOptions)) {
        $eventSlug = 'default';
    }

    $image = 'placeholder.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = '../assets/images/';
        $fileName = time() . '_' . basename((string)$_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $fileName)) {
            $image = $fileName;
        }
    }

    $data = [
        'name' => $name,
        'price' => max(0, $price),
        'stock' => max(0, $stock),
        'category_id' => $categoryId > 0 ? $categoryId : null,
        'description' => $desc,
        'image' => $image,
        'event_slug' => $eventSlug
    ];

    try {
        if ($name === '') {
            throw new RuntimeException('invalid_name');
        }
        insertData('products', $data);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thêm sản phẩm thành công!'];
    } catch (Throwable $e) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không thể thêm sản phẩm. Vui lòng kiểm tra dữ liệu.'];
    }

    echo "<script>window.location.href='products.php';</script>";
    exit;
}

// Cập nhật sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim((string)($_POST['name'] ?? ''));
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $desc = trim((string)($_POST['description'] ?? ''));
    $eventSlug = (string)($_POST['event_slug'] ?? 'default');
    if (!array_key_exists($eventSlug, $eventOptions)) {
        $eventSlug = 'default';
    }
    $oldImage = (string)($_POST['old_image'] ?? 'placeholder.jpg');

    $image = $oldImage;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = '../assets/images/';
        $fileName = time() . '_' . basename((string)$_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $fileName)) {
            $image = $fileName;
        }
    }

    $data = [
        'name' => $name,
        'price' => max(0, $price),
        'stock' => max(0, $stock),
        'category_id' => $categoryId > 0 ? $categoryId : null,
        'description' => $desc,
        'image' => $image,
        'event_slug' => $eventSlug
    ];

    try {
        if ($id <= 0 || $name === '') {
            throw new RuntimeException('invalid_data');
        }
        updateData('products', $data, ['id' => $id]);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật sản phẩm thành công!'];
    } catch (Throwable $e) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không thể cập nhật sản phẩm.'];
    }

    echo "<script>window.location.href='products.php';</script>";
    exit;
}

// Xóa sản phẩm
if (isset($_GET['delete'])) {
    try {
        deleteData('products', ['id' => (int)$_GET['delete']]);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đã xóa sản phẩm.'];
    } catch (Throwable $e) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không thể xóa sản phẩm.'];
    }
    echo "<script>window.location.href='products.php';</script>";
    exit;
}

$categories = getData('categories');
$categoryMap = [];
foreach ($categories as $categoryRow) {
    $categoryMap[(int)$categoryRow['id']] = (string)$categoryRow['name'];
}

// Lọc theo danh mục
$currentCat = isset($_GET['cat_id']) && $_GET['cat_id'] !== '' ? (int)$_GET['cat_id'] : 'all';
$filterOptions = ['order_by' => 'id DESC', 'skip_event_filter' => true];

if ($currentCat !== 'all') {
    $filterOptions['where'] = ['category_id' => $currentCat];
}

$products = getData('products', $filterOptions);
?>

<h2 class="mb-4">Quản lý sản phẩm</h2>

<?php include '../includes/alert.php'; ?>

<!-- Tabs danh mục -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $currentCat === 'all' ? 'active' : '' ?>" href="products.php">Tất cả</a>
    </li>
    <?php foreach ($categories as $c): ?>
        <li class="nav-item">
            <a class="nav-link <?= $currentCat === (int)$c['id'] ? 'active' : '' ?>" href="?cat_id=<?= (int)$c['id'] ?>">
                <?= htmlspecialchars((string)$c['name']) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="fa-solid fa-plus me-2"></i>Thêm sản phẩm
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hình ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Kho</th>
                    <th>Sự kiện</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Không có sản phẩm nào trong danh mục này.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= (int)$p['id'] ?></td>
                            <td>
                                <img src="../assets/images/<?= htmlspecialchars((string)$p['image']) ?>" width="50" height="50" class="object-fit-cover rounded" alt="">
                            </td>
                            <td><?= htmlspecialchars((string)$p['name']) ?></td>
                            <td><?= htmlspecialchars($categoryMap[(int)($p['category_id'] ?? 0)] ?? 'Không phân loại') ?></td>
                            <td><?= formatVND($p['price']) ?></td>
                            <td><?= (int)$p['stock'] ?></td>
                            <td><?= htmlspecialchars($eventOptions[$p['event_slug'] ?? 'default'] ?? ($p['event_slug'] ?? 'default')) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning text-white me-1" onclick='openEditModal(<?= json_encode($p, JSON_UNESCAPED_UNICODE) ?>)'>
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <a href="?delete=<?= (int)$p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal thêm sản phẩm -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Thêm sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label>Giá</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label>Số lượng</label>
                        <input type="number" name="stock" class="form-control" value="10">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Danh mục</label>
                    <select name="category_id" class="form-select">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars((string)$c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Sự kiện</label>
                    <select name="event_slug" class="form-select">
                        <?php foreach ($eventOptions as $slug => $label): ?>
                            <option value="<?= htmlspecialchars((string)$slug) ?>"><?= htmlspecialchars((string)$label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Hình ảnh</label>
                    <input type="file" name="image" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" name="add_product" class="btn btn-primary">Lưu sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal sửa sản phẩm -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="old_image" id="edit_old_image">

                <div class="mb-3">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label>Giá</label>
                        <input type="number" name="price" id="edit_price" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label>Số lượng</label>
                        <input type="number" name="stock" id="edit_stock" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Danh mục</label>
                    <select name="category_id" id="edit_category_id" class="form-select">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars((string)$c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Sự kiện</label>
                    <select name="event_slug" id="edit_event_slug" class="form-select">
                        <?php foreach ($eventOptions as $slug => $label): ?>
                            <option value="<?= htmlspecialchars((string)$slug) ?>"><?= htmlspecialchars((string)$label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Hình ảnh (để trống nếu không đổi)</label>
                    <input type="file" name="image" class="form-control">
                    <div class="mt-2">
                        <img id="edit_preview_image" src="" width="60" class="rounded border" alt="">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Mô tả</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" name="update_product" class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openEditModal(product) {
    document.getElementById('edit_id').value = product.id || '';
    document.getElementById('edit_name').value = product.name || '';
    document.getElementById('edit_price').value = product.price || 0;
    document.getElementById('edit_stock').value = product.stock || 0;
    document.getElementById('edit_category_id').value = product.category_id || '';
    document.getElementById('edit_event_slug').value = product.event_slug || 'default';
    document.getElementById('edit_description').value = product.description || '';
    document.getElementById('edit_old_image').value = product.image || 'placeholder.jpg';
    document.getElementById('edit_preview_image').src = '../assets/images/' + (product.image || 'placeholder.jpg');

    new bootstrap.Modal(document.getElementById('editProductModal')).show();
}
</script>
</body>
</html>
