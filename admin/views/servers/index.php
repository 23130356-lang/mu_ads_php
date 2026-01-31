<?php
session_start();
require_once '../../../controllers/AdminServerController.php';
$controller = new AdminServerController();

// Xử lý xóa nếu có yêu cầu
if (isset($_GET['delete_id'])) {
    $controller->delete($_GET['delete_id']);
}

// LẤY DỮ LIỆU: Lúc này index() trả về mảng ['servers' => ..., 'prices' => ...]
$data = $controller->index();
$servers = $data['servers']; // Danh sách server (PDO Object)
$prices  = $data['prices'];  // Mảng giá tiền (Array)
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Server</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .thumb-sm { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; }
        .text-price { font-size: 0.85rem; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <div class="flex-grow-1 bg-light p-4" style="height: 100vh; overflow-y: auto;">
        <h2 class="mb-4">Danh sách Server MU</h2>
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success">Thao tác thành công!</div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Banner</th>
                            <th style="width: 20%;">Thông tin Server</th>
                            <th>Chủ Server / Số dư</th> <th>Gói / Giá</th>          <th>Trạng thái</th>
                            <th>Active</th>
                            <th style="width: 15%;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $servers->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= $row['server_id'] ?></td>
                            
                            <td>
                                <?php if(!empty($row['banner_image'])): ?>
                                    <img src="../../../public/<?= $row['banner_image'] ?>" class="thumb-sm">
                                <?php else: ?>
                                    <span class="text-muted small">No Image</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <div class="fw-bold text-primary"><?= $row['server_name'] ?></div>
                                <small class="text-muted">MU: <?= $row['mu_name'] ?></small>
                            </td>

                            <td>
                                <div class="fw-bold">👤 <?= $row['username'] ?></div>
                                <div class="text-danger small">
                                    💰 Dư: <?= number_format($row['user_balance'] ?? 0) ?> Coin
                                </div>
                            </td>

                            <td>
                                <?php 
                                    // Lấy giá từ mảng $prices dựa theo gói package
                                    $pkg = $row['banner_package'];
                                    $currentPrice = $prices[$pkg] ?? 0;
                                    
                                    // Màu sắc badge
                                    $badgeColor = match($pkg) {
                                        'SUPER_VIP' => 'bg-danger',
                                        'VIP'       => 'bg-warning text-dark',
                                        default     => 'bg-secondary'
                                    };
                                ?>
                                <span class="badge <?= $badgeColor ?> mb-1"><?= $pkg ?></span><br>
                                <span class="text-muted text-price">Giá: <?= number_format($currentPrice) ?></span>

                                <?php if ($row['status'] == 'PENDING' && ($row['user_balance'] ?? 0) < $currentPrice): ?>
                                    <div class="text-danger fw-bold small mt-1 border border-danger px-1 rounded bg-white">
                                        ⚠️ Không đủ tiền
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php 
                                    $statusClass = match($row['status']) {
                                        'APPROVED' => 'text-success',
                                        'PENDING'  => 'text-warning',
                                        'REJECTED' => 'text-danger',
                                        default    => 'text-muted'
                                    };
                                ?>
                                <strong class="<?= $statusClass ?>"><?= $row['status'] ?></strong>
                            </td>

                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" disabled <?= $row['is_active'] ? 'checked' : '' ?>>
                                </div>
                            </td>

                            <td>
                                <a href="edit.php?id=<?= $row['server_id'] ?>" class="btn btn-sm btn-primary mb-1">
                                    <i class="bi bi-pencil"></i> Sửa/Duyệt
                                </a>
                                <a href="index.php?delete_id=<?= $row['server_id'] ?>" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa server này?')" 
                                   class="btn btn-sm btn-danger mb-1">
                                    <i class="bi bi-trash"></i> Xóa
                                </a>
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