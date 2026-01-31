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

// Khởi tạo Database một lần duy nhất
$database = new Database();
$db = $database->connect();

// Lấy tham số URL
$url = isset($_GET['url']) ? $_GET['url'] : 'home';

// =========================================================================
// 3. ROUTER (ĐIỀU HƯỚNG)
// =========================================================================

switch ($url) {
    
    // --- TRANG CHỦ (GỌI CONTROLLER) ---
    case 'home':
        require_once '../controllers/HomeController.php';
        // Truyền $db vào constructor để controller dùng
        $homeCtrl = new HomeController($db); 
        $homeCtrl->index();
        break;

    // --- AUTH ---
    case 'login':
        require_once 'auth.php'; // Giả sử view login tên là auth.php
        break;

    case 'register':
        $_GET['mode'] = 'register'; 
        require_once 'auth.php';
        break;

    case 'login-action':
        require_once '../controllers/AuthController.php';
        $auth = new AuthController($db);
        $auth->login();
        break;

    case 'register-action':
        require_once '../controllers/AuthController.php';
        $auth = new AuthController($db);
        $auth->register();
        break;

    case 'logout':
        require_once '../controllers/AuthController.php';
        $auth = new AuthController($db);
        $auth->logout();
        break;

    // --- SERVER (ĐĂNG BÀI) ---
    case 'create-server':
        require_once 'includes/header.php';
        if (!isset($_SESSION['user_id'])) {
            // Redirect thông minh
            header("Location: index.php?url=login&error=" . urlencode("Bạn cần đăng nhập để đăng bài"));
            exit;
        }
        require_once 'create_server.php';
        break;

    case 'create-server-action':
        require_once '../controllers/ServerController.php';
        $srvCtrl = new ServerController($db);
        $srvCtrl->store(); 
        break;

    // --- QUẢNG CÁO ---
    case 'banner-register':
        require_once '../controllers/HomeBannerController.php';
        $adsCtrl = new HomeBannerController($db);
        $adsCtrl->index(); 
        break;

    case 'banner-register-action':
        require_once '../controllers/HomeBannerController.php';
        $adsCtrl = new HomeBannerController($db);
        $adsCtrl->register(); 
        break;

    // --- 404 ---
    default:
        // Đảm bảo header được load nếu có file
        if(file_exists('includes/header.php')) include 'includes/header.php';
        echo "<div class='container text-center text-white mt-5'>
                <h1 class='text-danger'>404</h1>
                <h3>Trang không tồn tại</h3>
                <a href='index.php' class='btn btn-warning mt-3'>Về Trang Chủ</a>
              </div>";
        break;
}
?>