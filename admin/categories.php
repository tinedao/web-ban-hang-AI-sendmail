<?php include 'header.php'; ?>

<?php
// Thêm danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        try {
            insertData('categories', ['name' => $name]);
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Thêm danh mục thành công!'];
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Danh mục đã tồn tại hoặc dữ liệu không hợp lệ.'];
        }
        echo "<script>window.location.href='categories.php';</script>";
        exit;
    }
}

// Sửa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name']);
    try {
        if ($id > 0 && $name !== '') {
            updateData('categories', ['name' => $name], ['id' => $id]);
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cập nhật danh mục thành công!'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Dữ liệu danh mục không hợp lệ.'];
        }
    } catch (Throwable $e) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không thể cập nhật danh mục.'];
    }
    echo "<script>window.location.href='categories.php';</script>";
    exit;
}

// Xóa danh mục
if (isset($_GET['delete_category'])) {
    $catId = (int)$_GET['delete_category'];
    // Kiểm tra xem có sản phẩm nào thuộc danh mục này không
    $count = getCount('products', [
        'where' => ['category_id' => $catId],
        'skip_event_filter' => true
    ]);
    if ($count > 0) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => "Không thể xóa! Có $count sản phẩm đang thuộc danh mục này."];
        echo "<script>window.location.href='categories.php';</script>";
        exit;
    } else {
        try {
            deleteData('categories', ['id' => $catId]);
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đã xóa danh mục.'];
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không thể xóa danh mục.'];
        }
        echo "<script>window.location.href='categories.php';</script>";
        exit;
    }
}

$categories = getData('categories');
?>

<h2 class="mb-4">Quản lý Danh mục</h2>

<?php include '../includes/alert.php'; ?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fa-solid fa-plus me-2"></i>Thêm danh mục
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên danh mục</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning text-white me-1" onclick='openEditCategoryModal(<?= json_encode($c) ?>)'>
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <a href="?delete_category=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa danh mục này? Lưu ý: Không thể xóa nếu đang có sản phẩm.')"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Thêm Danh Mục -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Thêm danh mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Tên danh mục</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" name="add_category" class="btn btn-primary">Lưu danh mục</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sửa Danh Mục -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_cat_id">
                <div class="mb-3">
                    <label>Tên danh mục</label>
                    <input type="text" name="name" id="edit_cat_name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" name="update_category" class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openEditCategoryModal(category) {
    document.getElementById('edit_cat_id').value = category.id;
    document.getElementById('edit_cat_name').value = category.name;
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
</script>
</body>
</html>
