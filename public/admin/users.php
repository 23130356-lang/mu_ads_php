<?php
session_start();
// FILE: public/admin/users.php

// 1. GỌI CONTROLLER (Đường dẫn tương tự banners.php)
require_once '../../controllers/AdminUserController.php';

$controller = new AdminUserController();
$msg = "";

// --- XỬ LÝ FORM SUBMIT & GET ACTION ---

// 1. Xóa User
if (isset($_GET['delete_id'])) {
    if ($controller->delete($_GET['delete_id'])) {
        $msg = "Đã xóa thành viên thành công!";
    }
}

// 2. Đổi quyền
if (isset($_GET['role_id']) && isset($_GET['current_role'])) {
    if ($controller->changeRole($_GET['role_id'], $_GET['current_role'])) {
        $msg = "Đã thay đổi quyền thành công!";
    }
}

// 3. Cộng/Trừ tiền (Xử lý POST từ Modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_coin') {
    $user_id = $_POST['user_id'];
    $amount = intval($_POST['amount']);
    $type = $_POST['type'];
    
    if ($controller->updateCoin($user_id, $amount, $type)) {
        $msg = "Đã cập nhật số dư thành công!";
    }
}

// Lấy danh sách User để hiển thị
$users = $controller->index();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Thành viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .thumb-img { width: 100px; height: 60px; object-fit: cover; border-radius: 4px; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
        /* Style riêng cho user */
        .role-admin { background-color: #dc3545; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }
        .role-user { background-color: #6c757d; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }
        .coin-text { font-weight: bold; color: #198754; }
    </style>
</head>
<body>
    
    <div class="d-flex">
        <?php require_once 'includes/sidebar.php'; ?>

        <div class="flex-grow-1 bg-light p-4" style="height: 100vh; overflow-y: auto;">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Quản lý Thành viên</h2>
                <button class="btn btn-secondary" disabled>Tổng: Đang cập nhật...</button>
            </div>

            <?php if(!empty($msg)): ?>
                <div class="alert alert-success"><?= $msg ?></div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tên đăng nhập</th>
                                <th>Thông tin liên hệ</th>
                                <th>Số dư (Coin)</th>
                                <th>Vai trò</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $users->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?= $row['user_id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['username']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($row['full_name']) ?></small>
                                </td>
                                <td>
                                    <div>Email: <?= htmlspecialchars($row['email']) ?></div>
                                    <div>SĐT: <?= htmlspecialchars($row['phone']) ?></div>
                                </td>
                                <td class="coin-text">
                                    <?= number_format($row['coin']) ?> xu
                                </td>
                                <td>
                                    <?php if($row['role'] === 'ADMIN'): ?>
                                        <span class="role-admin">ADMIN</span>
                                    <?php else: ?>
                                        <span class="role-user">MEMBER</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#coinModal<?= $row['user_id'] ?>">
                                        $ Nạp
                                    </button>

                                    <a href="users.php?role_id=<?= $row['user_id'] ?>&current_role=<?= $row['role'] ?>" 
                                       onclick="return confirm('Đổi quyền user này?')"
                                       class="btn btn-sm btn-warning">Quyền</a>

                                    <a href="users.php?delete_id=<?= $row['user_id'] ?>" 
                                       onclick="return confirm('Cảnh báo: Xóa user sẽ xóa luôn các server của họ. Tiếp tục?')" 
                                       class="btn btn-sm btn-danger">Xóa</a>

                                    <div class="modal fade" id="coinModal<?= $row['user_id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="users.php">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Nạp/Trừ tiền: <?= $row['username'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update_coin">
                                                        <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Loại giao dịch</label>
                                                            <select name="type" class="form-select">
                                                                <option value="add">Cộng tiền (+)</option>
                                                                <option value="minus">Trừ tiền (-)</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Số tiền (Coin)</label>
                                                            <input type="number" name="amount" class="form-control" required min="1" placeholder="Nhập số lượng...">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-primary">Xác nhận</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>