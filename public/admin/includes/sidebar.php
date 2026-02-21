<?php
// 1. Lấy đường dẫn hiện tại và cấu hình root (giữ nguyên logic cũ)
$current_page = $_SERVER['PHP_SELF'];
$path_to_root = (strpos($current_page, '/views/') !== false) ? '../../' : '';

// 2. Hàm helper để kiểm tra active class (giữ nguyên)
function isActive($current, $keywords) {
    if (!is_array($keywords)) $keywords = [$keywords];
    foreach ($keywords as $kw) {
        if (strpos($current, $kw) !== false) return 'active';
    }
    if (in_array('index.php', $keywords) && basename($current) == 'index.php' && strpos($current, '/views/') === false) {
        return 'active';
    }
    return '';
}
?>

<style>
    /* --- CẤU TRÚC CHUNG (Giữ nguyên tính năng Responsive) --- */
    @media (min-width: 992px) {
        .sidebar-container {
            width: 280px;
            height: 100vh;
            position: sticky;
            top: 0;
            display: flex !important;
            background: #1a1c23; /* Màu nền tối đậm hơn chút cho sang */
        }
        .mobile-toggle { display: none !important; }
    }
    @media (max-width: 991.98px) {
        .sidebar-container { width: 280px; background: #1a1c23; }
    }

    .sidebar-logo {
        border-bottom: 1px solid rgba(255,255,255,0.05);
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* --- STYLE CHO CÁC LINK MENU --- */
    .nav-link {
        color: #a0aec0; /* Màu chữ mặc định xám sáng */
        transition: all 0.3s ease;
        border-radius: 12px; /* Bo góc tròn hơn */
        margin-bottom: 8px;
        font-weight: 600;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
    }
    
    /* Style chung cho Icon */
    .nav-link i {
        font-size: 1.3rem; /* Icon to hơn */
        width: 35px;
        text-align: center;
        margin-right: 12px;
        transition: all 0.3s;
    }

    /* Hiệu ứng hover chung: chữ trắng, đẩy nhẹ sang phải */
    .nav-link:hover {
        color: #fff;
        transform: translateX(5px);
    }
    /* Khi active thì icon chuyển màu trắng */
    .nav-link.active i {
        color: #fff !important;
    }


    /* --- ĐỊNH NGHĨA MÀU SẮC RIÊNG CHO TỪNG MỤC --- */

    /* 1. MỤC SERVER - Tông Xanh Dương (Blue) */
    .nav-link-server i { color: #3498db; } /* Màu icon mặc định */
    .nav-link-server:hover {
        background: rgba(52, 152, 219, 0.15); /* Nền hover nhạt */
        box-shadow: 0 0 15px rgba(52, 152, 219, 0.3); /* Glow nhẹ khi hover */
    }
    .nav-link-server.active {
        background: linear-gradient(135deg, #3498db, #2980b9); /* Gradient active */
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.5); /* Glow mạnh khi active */
        color: #fff;
    }

    /* 2. MỤC BANNER - Tông Xanh Lá (Green) */
    .nav-link-banner i { color: #2ecc71; }
    .nav-link-banner:hover {
        background: rgba(46, 204, 113, 0.15);
        box-shadow: 0 0 15px rgba(46, 204, 113, 0.3);
    }
    .nav-link-banner.active {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        box-shadow: 0 5px 15px rgba(46, 204, 113, 0.5);
        color: #fff;
    }

    /* 3. MỤC THÀNH VIÊN - Tông Cam (Orange) */
    .nav-link-user i { color: #e67e22; }
    .nav-link-user:hover {
        background: rgba(230, 126, 34, 0.15);
        box-shadow: 0 0 15px rgba(230, 126, 34, 0.3);
    }
    .nav-link-user.active {
        background: linear-gradient(135deg, #e67e22, #d35400);
        box-shadow: 0 5px 15px rgba(230, 126, 34, 0.5);
        color: #fff;
    }

    /* User Dropdown footer */
    .user-dropdown-btn {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.05);
        backdrop-filter: blur(10px);
    }
    .user-dropdown-btn:hover {
        background: rgba(255,255,255,0.1);
    }

</style>

<nav class="navbar navbar-dark bg-dark d-lg-none mb-3 p-3 mobile-toggle shadow-sm">
    <button class="btn btn-outline-light border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar">
        <i class="fas fa-bars fa-lg"></i>
    </button>
    <span class="text-white fw-bold fs-5 ms-3">ADMIN PANEL</span>
</nav>

<div class="offcanvas-lg offcanvas-start text-white sidebar-container d-flex flex-column flex-shrink-0 p-4 shadow-lg" 
     tabindex="-1" id="adminSidebar">
    
    <div class="d-flex align-items-center sidebar-logo w-100">
        <div class="rounded-circle bg-gradient p-2 me-3 d-flex align-items-center justify-content-center shadow-sm" 
             style="width: 50px; height: 50px; background: linear-gradient(45deg, #ff00cc, #333399);">
            <i class="fas fa-gamepad fa-2x text-white"></i>
        </div>
        <div>
<h5 class="fw-bold mb-0" style="color: #231717;">MU Admin</h5>
            <small class="text-muted" style="font-size: 0.75rem;">Control Panel V2</small>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto d-lg-none" data-bs-dismiss="offcanvas"></button>
    </div>
    
    <ul class="nav nav-pills flex-column mb-auto mt-2">
        
        <li class="nav-item">
            <a href="<?= $path_to_root ?>index.php" 
               class="nav-link nav-link-server <?= isActive($current_page, ['index.php', 'servers', 'manage_servers']) ?>">
                <i class="fas fa-server"></i> <span>Quản lý Server</span>
            </a>
        </li>

        <li class="nav-item mt-2">
            <a href="<?= $path_to_root ?>banners.php" 
               class="nav-link nav-link-banner <?= isActive($current_page, ['banners.php', 'home_banners']) ?>">
                <i class="fas fa-images"></i> <span>Quản lý Banner</span>
            </a>
        </li>

        <li class="nav-item mt-2">
            <a href="<?= $path_to_root ?>users.php" 
               class="nav-link nav-link-user <?= isActive($current_page, ['users.php']) ?>">
                <i class="fas fa-users-cog"></i> <span>Quản lý Thành viên</span>
            </a>
        </li>
    </ul>
    
    <div class="dropdown mt-4">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle p-3 rounded-3 user-dropdown-btn transition-all" 
           id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" 
                 style="width: 40px; height: 40px; background: linear-gradient(to right, #f12711, #f5af19);">
                <i class="fas fa-user-tie fa-lg"></i>
            </div>
            <div class="d-flex flex-column lh-sm">
                <strong class="fs-6"><?= htmlspecialchars($_SESSION['user']['username'] ?? 'Administrator') ?></strong>
                <small class="text-muted" style="font-size: 0.7rem;">Super Admin</small>
            </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow border-0 mt-2 p-2" style="background: #2a2d35;" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item rounded-2 mb-1 p-2" href="<?= $path_to_root ?>../index.php"><i class="fas fa-globe-asia me-2 text-info"></i>Xem trang chủ Web</a></li>
            <li><hr class="dropdown-divider bg-secondary opacity-25 my-2"></li>
            <li><a class="dropdown-item rounded-2 p-2 text-danger fw-bold" href="<?= $path_to_root ?>../auth.php?logout=true"><i class="fas fa-power-off me-2"></i>Đăng xuất</a></li>
        </ul>
    </div>
</div>