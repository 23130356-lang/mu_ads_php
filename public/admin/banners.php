<?php
session_start();
// FILE: public/admin/banners.php

// 1. SỬA ĐƯỜNG DẪN REQUIRE (Lùi 2 cấp: ../../)
require_once '../../controllers/AdminHomeBannerController.php';

$controller = new AdminHomeBannerController();

if (isset($_GET['delete_id'])) {
    $controller->delete($_GET['delete_id']);
}

$banners = $controller->index();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Banner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .thumb-img { width: 100px; height: 60px; object-fit: cover; border-radius: 4px; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
    </style>
</head>
<body>
    
    <div class="d-flex">
        <?php require_once 'includes/sidebar.php'; ?>

        <div class="flex-grow-1 bg-light p-4" style="height: 100vh; overflow-y: auto;">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Quản lý Home Banner</h2>
                <a href="views/home_banners/create.php" class="btn btn-primary">+ Thêm Banner Mới</a>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success">Thao tác thành công!</div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Hình ảnh</th>
                                <th>Vị trí</th>
                                <th>Người tạo</th>
                                <th>Ngày kết thúc</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $banners->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td>
                                    <?php if(strpos($row['image_url'], 'http') === 0): ?>
                                        <img src="<?= $row['image_url'] ?>" class="thumb-img">
                                    <?php else: ?>
                                        <img src="../<?= $row['image_url'] ?>" class="thumb-img">
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info"><?= $row['position_code'] ?></span></td>
                                <td><?= $row['username'] ?? 'Admin' ?></td>
                                <td><?= date('d/m/Y', strtotime($row['end_date'])) ?></td>
                                <td>
                                    <?php if($row['is_active']): ?>
                                        <span class="status-active">Hiển thị</span>
                                    <?php else: ?>
                                        <span class="status-inactive">Ẩn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="views/home_banners/edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                                    <a href="banners.php?delete_id=<?= $row['id'] ?>" 
                                       onclick="return confirm('Bạn có chắc muốn xóa?')" 
                                       class="btn btn-sm btn-danger">Xóa</a>
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