<?php
// =========================================================================
// 1. KHỞI ĐỘNG SESSION & CẤU HÌNH
// =========================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Tắt hiển thị lỗi khi chạy thực tế (để security), bật khi dev
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Ho_Chi_Minh');

// =========================================================================
// 2. INCLUDE FILE CẤU HÌNH & CONTROLLER
// =========================================================================
require_once '../config/Database.php';

$database = new Database();
$db = $database->connect();

$url = isset($_GET['url']) ? $_GET['url'] : 'home';

// =========================================================================
// 3. ROUTER
// =========================================================================

switch ($url) {
    
    // ================= HOME =================
    case 'home':
        require_once '../controllers/HomeController.php';
        $homeCtrl = new HomeController($db); 
        $homeCtrl->index();
        break;

    // ================= HƯỚNG DẪN =================
    case 'huong-dan':
        require_once 'includes/header.php';
        require_once 'guide.php';
        require_once 'includes/footer.php';
        break;

    // ================= AUTH (ĐĂNG NHẬP/ĐĂNG KÝ) =================
    case 'login':
        require_once 'includes/header.php';
        require_once 'auth.php';
        require_once 'includes/footer.php';
        break;

    case 'register':
        $_GET['mode'] = 'register';
        require_once 'includes/header.php';
        require_once 'auth.php';
        require_once 'includes/footer.php';
        break;

    case 'login-action':
        require_once '../controllers/AuthController.php';
        (new AuthController($db))->login();
        break;

    case 'register-action':
        require_once '../controllers/AuthController.php';
        (new AuthController($db))->register();
        break;

    case 'logout':
        require_once '../controllers/AuthController.php';
        (new AuthController($db))->logout();
        break;

    // ================= PROFILE (HỒ SƠ CÁ NHÂN) =================
    case 'profile':
        require_once 'profile.php';
        break;

    case 'update_profile':
        require_once '../controllers/AuthController.php';
        (new AuthController($db))->updateProfile();
        break;
    // ================= QUẢN LÝ SERVER (MỚI BỔ SUNG) =================
    
    // 1. Xem danh sách server của tôi
    case 'manage-server':
        require_once '../controllers/ServerController.php';
        $serverCtrl = new ServerController($db);
        $serverCtrl->manage(); // Hàm này sẽ include file view manage_servers.php
        require_once 'includes/footer.php';
        break;

    // 2. Xử lý gia hạn server
        case 'renew': 
        require_once '../controllers/ServerController.php';
        $serverCtrl = new ServerController($db);
        $serverCtrl->renew(); // Xử lý logic và redirect
        break;

    // ================= TẠO SERVER (ĐĂNG BÀI) =================
    case 'create-server':
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=login&error=" . urlencode("Bạn cần đăng nhập để đăng bài"));
            exit;
        }
        require_once 'includes/header.php';
        require_once 'create_server.php'; 
        require_once 'includes/footer.php'; 
        break;

    case 'create-server-action':
        require_once '../controllers/ServerController.php';
        $serverCtrl = new ServerController($db);
        $serverCtrl->store(); 
        break;

    // ================= CHI TIẾT SERVER =================
    case 'server-detail':
        require_once 'includes/header.php';
        require_once '../controllers/ServerController.php';
        $serverCtrl = new ServerController($db);
        $serverCtrl->detail(); 
        require_once 'includes/footer.php';
        break;

    // ================= QUẢNG CÁO HOME =================
    case 'banner-register':
        require_once '../controllers/HomeBannerController.php';
        (new HomeBannerController($db))->index();
        break;

    case 'banner-register-action':
        require_once '../controllers/HomeBannerController.php';
        (new HomeBannerController($db))->register();
        break;

    // ================= 404 - NOT FOUND =================
    default:
        require_once 'includes/header.php';
        echo "<div class='container text-center text-white mt-5' style='min-height: 50vh; display: flex; flex-direction: column; justify-content: center;'>
                <h1 class='text-danger display-1 fw-bold'>404</h1>
                <h3 class='mb-4'>Trang không tồn tại hoặc đã bị xóa</h3>
                <div>
                    <a href='index.php' class='btn btn-warning px-4 py-2 fw-bold'>
                        <i class='fa-solid fa-house me-2'></i>Về Trang Chủ
                    </a>
                </div>
              </div>";
        require_once 'includes/footer.php';
        break;
}
?>