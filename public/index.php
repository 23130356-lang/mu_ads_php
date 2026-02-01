<?php
// =========================================================================
// 1. KHỞI ĐỘNG SESSION & CẤU HÌNH
// =========================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

    // ================= TẠO SERVER (ĐĂNG BÀI) =================
    case 'create-server':
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=login&error=" . urlencode("Bạn cần đăng nhập để đăng bài"));
            exit;
        }
        // Gọi Controller để lấy danh sách Version, Type... truyền vào View (Nếu cần)
        // Hoặc include thẳng view nếu view tự xử lý
        require_once 'includes/header.php';
        require_once 'create_server.php'; 
        require_once 'includes/footer.php'; 
        break;

    case 'create-server-action':
        // Xử lý Form submit từ create_server.php
        require_once '../controllers/ServerController.php';
        $serverCtrl = new ServerController($db);
        $serverCtrl->store(); // Hàm store() xử lý upload ảnh và lưu DB
        break;

    // ================= [MỚI] CHI TIẾT SERVER =================
    case 'server-detail':
        // 1. Load Header
        require_once 'includes/header.php';
        
        // 2. Gọi Controller lấy dữ liệu và hiển thị View nội dung
        require_once '../controllers/ServerController.php';
        $serverCtrl = new ServerController($db);
        $serverCtrl->detail(); // Hàm này sẽ require '../views/server_detail.php'
        
        // 3. Load Footer
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

    // ================= 404 =================
    default:
        require_once 'includes/header.php';
        echo "<div class='container text-center text-white mt-5'>
                <h1 class='text-danger'>404</h1>
                <h3>Trang không tồn tại</h3>
                <a href='index.php' class='btn btn-warning mt-3'>Về Trang Chủ</a>
              </div>";
        require_once 'includes/footer.php';
        break;
}
?>