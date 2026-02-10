<?php
// 1. Lấy đường dẫn hiện tại
$current_page = $_SERVER['PHP_SELF'];

// 2. Xác định vị trí (Prefix) để tạo link
// Nếu đường dẫn chứa '/views/' nghĩa là đang ở sâu (vd: public/admin/views/home_banners/edit.php)
// Cần lùi ra 2 cấp (../../) để về lại thư mục public/admin
// Ngược lại nếu đang ở public/admin/index.php thì không cần lùi (rỗng)
$path_to_root = (strpos($current_page, '/views/') !== false) ? '../../' : '';
?>

<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px; min-height: 100vh;">
    <a href="<?= $path_to_root ?>index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4 fw-bold">ADMIN PANEL</span>
    </a>
    <hr>
    
    <ul class="nav nav-pills flex-column mb-auto">
        
        <li class="nav-item">
            <a href="<?= $path_to_root ?>banners.php" 
               class="nav-link text-white <?= (strpos($current_page, 'banners.php') !== false || strpos($current_page, 'home_banners') !== false) ? 'active bg-primary' : '' ?>">
                🖼️ Quản lý Banner
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= $path_to_root ?>index.php" 
               class="nav-link text-white <?= (basename($current_page) == 'index.php' && strpos($current_page, '/views/') === false) || strpos($current_page, 'servers') !== false ? 'active bg-primary' : '' ?>">
                🖥️ Quản lý Server
            </a>
        </li>

        <li class="nav-item">
    <a class="nav-link" href="index.php?url=admin-users">
        <i class="fas fa-fw fa-users"></i>
        <span>Quản lý Thành viên</span>
    </a>
</li>
    </ul>
    
    <hr>
    
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <strong>Admin: <?= $_SESSION['user']['username'] ?? 'Root' ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="<?= $path_to_root ?>../index.php">Về trang chủ Web</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= $path_to_root ?>../index.php?url=logout">Đăng xuất</a></li>
        </ul>
    </div>
</div>