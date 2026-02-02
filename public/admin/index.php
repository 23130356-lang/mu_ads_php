<?php
session_start();
// FILE: public/admin/index.php

// 1. SỬA ĐƯỜNG DẪN REQUIRE (Lùi 2 cấp: ../../)
require_once '../../controllers/AdminServerController.php';

$controller = new AdminServerController();

// Xử lý xóa
if (isset($_GET['delete_id'])) {
    $controller->delete($_GET['delete_id']);
}

// LẤY DỮ LIỆU
$data = $controller->index();
$servers = $data['servers']; 
$prices  = $data['prices'];  

// Lấy thông tin phân trang
$pagination = $data['pagination'];
$page       = $pagination['current_page'];
$totalPages = $pagination['total_pages'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Server | Admin CP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .table-card { border: none; border-radius: 12px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05); }
        .table thead th { background-color: #2c3e50; color: white; font-weight: 500; border: none; }
        .server-thumb { width: 80px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6; }
        .avatar-circle { width: 32px; height: 32px; background-color: #e9ecef; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; color: #495057; margin-right: 8px; }
        .status-badge { font-size: 0.75rem; padding: 0.35em 0.65em; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0">Quản lý Server</h3>
                <span class="text-muted small">Danh sách toàn bộ server đăng ký trên hệ thống</span>
            </div>
            <div>
                <button class="btn btn-outline-primary btn-sm"><i class="bi bi-funnel"></i> Bộ lọc</button>
            </div>
        </div>
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Thao tác thành công!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card table-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Server / Banner</th>
                                <th>Chủ sở hữu & Số dư</th>
                                <th>Gói Dịch Vụ</th>
                                <th>Trạng thái</th>
                                <th class="text-end pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $servers->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 position-relative">
                                            <?php if(!empty($row['banner_image'])): ?>
                                                <img src="../<?= $row['banner_image'] ?>" class="server-thumb">
                                            <?php else: ?>
                                                <div class="server-thumb d-flex align-items-center justify-content-center bg-light text-muted">No Img</div>
                                            <?php endif; ?>
                                            <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-dark border border-white">
                                                #<?= $row['server_id'] ?>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-primary text-truncate" style="max-width: 180px;"><?= $row['server_name'] ?></div>
                                            <div class="small text-muted fst-italic">MU: <?= $row['mu_name'] ?></div>
                                            <a href="<?= $row['website_url'] ?>" target="_blank" class="small text-decoration-none"><i class="bi bi-link-45deg"></i> Web</a>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center mb-1">
                                        <div class="avatar-circle"><?= strtoupper(substr($row['username'], 0, 1)) ?></div>
                                        <span class="fw-semibold"><?= $row['username'] ?></span>
                                    </div>
                                    <?php 
                                        $balance = $row['user_balance'] ?? 0;
                                        $pkgPrice = $prices[$row['banner_package']] ?? 0;
                                        $isLowBalance = ($row['status'] == 'PENDING' && $balance < $pkgPrice);
                                    ?>
                                    <div class="small <?= $isLowBalance ? 'text-danger fw-bold' : 'text-secondary' ?>">
                                        <i class="bi bi-wallet2"></i> <?= number_format($balance) ?> đ
                                        <?php if($isLowBalance): ?>
                                            <i class="bi bi-exclamation-circle" title="Không đủ tiền duyệt"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <?php 
                                        $badgeClass = match($row['banner_package']) {
                                            'SUPER_VIP' => 'bg-danger bg-gradient',
                                            'VIP'       => 'bg-warning bg-gradient text-dark',
                                            default     => 'bg-secondary bg-opacity-50 text-dark'
                                        };
                                    ?>
                                    <span class="badge rounded-pill <?= $badgeClass ?> mb-1"><?= $row['banner_package'] ?></span>
                                    <div class="small text-muted"><?= number_format($pkgPrice) ?> đ</div>
                                </td>

                                <td>
                                    <?php 
                                        $sttClass = match($row['status']) {
                                            'APPROVED' => 'success',
                                            'PENDING'  => 'warning',
                                            'REJECTED' => 'danger',
                                            'EXPIRED'  => 'secondary',
                                            default    => 'light'
                                        };
                                    ?>
                                    <span class="badge status-badge bg-<?= $sttClass ?>-subtle text-<?= $sttClass ?> border border-<?= $sttClass ?>-subtle rounded-pill">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>

                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="views/servers/edit.php?id=<?= $row['server_id'] ?>" class="btn btn-sm btn-outline-primary" title="Sửa / Duyệt">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="index.php?delete_id=<?= $row['server_id'] ?>" 
                                           onclick="return confirm('Xóa vĩnh viễn server này? Hành động không thể phục hồi!')" 
                                           class="btn btn-sm btn-outline-danger" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    
                    <?php if($servers->rowCount() > 0): ?>
                    <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light">
                        <div class="small text-muted">Trang <?= $page ?> / <?= $totalPages ?></div>
                        <?php if($totalPages > 1): ?>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                        <div class="text-center p-5 text-muted">Chưa có dữ liệu.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>