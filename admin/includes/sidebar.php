<?php
// Lấy tên file/đường dẫn hiện tại để set class 'active'
$current_page = $_SERVER['PHP_SELF'];

// KIỂM TRA VỊ TRÍ ĐỨNG ĐỂ TẠO ĐƯỜNG DẪN (PATH)
// Nếu file đang chạy tìm thấy folder 'includes' ngay cạnh nó -> đang ở admin/ (Root)
// Nếu không -> đang ở trong views/server/..., cần lùi ra 2 cấp (../../)
$is_root = file_exists(__DIR__ . '/../../index.php') && !file_exists(__DIR__ . '/../../admin'); 
// (Logic trên có thể phức tạp tùy server, dùng cách đơn giản hơn bên dưới):

// Cách đơn giản nhất: Kiểm tra xem đang chạy file nào
$path_prefix = '';
if (strpos($current_page, '/views/') !== false) {
    $path_prefix = '../../'; // Nếu đang ở trong views thì lùi 2 cấp
}
?>

<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px; min-height: 100vh;">
    <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4 fw-bold">ADMIN PANEL</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="<?= $path_prefix ?>banners.php" 
               class="nav-link text-white <?= (strpos($current_page, 'banners.php') !== false || strpos($current_page, 'home_banners') !== false) ? 'active bg-primary' : '' ?>">
                🖼️ Quản lý Banner
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= $path_prefix ?>index.php" 
               class="nav-link text-white <?= (basename($current_page) == 'index.php' && strpos($current_page, '/views/') === false) || strpos($current_page, '/servers/') !== false ? 'active bg-primary' : '' ?>">
                🖥️ Quản lý Server
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= $path_prefix ?>views/users/index.php" 
               class="nav-link text-white <?= strpos($current_page, 'users') !== false ? 'active bg-primary' : '' ?>">
                👤 Quản lý User
            </a>
        </li>
    </ul>
    
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <strong>Admin: <?= $_SESSION['user']['username'] ?? 'Root' ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="<?= $path_prefix ?>../public/index.php">Về trang chủ</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= $path_prefix ?>../public/index.php?url=logout">Đăng xuất</a></li>
        </ul>
    </div>
</div>