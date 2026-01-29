<?php
// =========================================================================
// 1. KHỞI ĐỘNG SESSION
// =========================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Cấu hình
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 3. Kết nối Database & MasterData
require_once '../config/Database.php';
require_once '../models/MasterData.php'; 

$database = new Database();
$db = $database->connect();

// Lấy Menu (Dùng chung cho toàn web)
$masterData = new MasterData($db);
$menuVersions = $masterData->getList('versions'); 
$menuTypes    = $masterData->getList('resets');

// =========================================================================
// 4. BỘ ĐIỀU HƯỚNG (ROUTER)
// =========================================================================

$url = isset($_GET['url']) ? $_GET['url'] : 'home';

switch ($url) {
    
    // --- TRANG CHỦ ---
    case 'home':
        require_once 'includes/header.php';
        if (file_exists('home.php')) {
            require_once 'home.php';
        } else {
            echo "<h3 style='color:white; text-align:center;'>File home.php chưa tồn tại</h3>";
        }
        break;

    // --- AUTH ---
    case 'login':
        require_once 'includes/header.php'; // Header có thể cần
        require_once 'auth.php';
        break;

    case 'register':
        $_GET['mode'] = 'register'; 
        require_once 'includes/header.php';
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

    // --- SERVER (MU) ---
    case 'create-server':
        require_once 'includes/header.php';
        if (!isset($_SESSION['user_id'])) { // Kiểm tra user_id cho chắc chắn
            header("Location: index.php?url=login&error=" . urlencode("Bạn cần đăng nhập để đăng bài"));
            exit;
        }
        require_once 'create_server.php';
        break;

    case 'create-server-action':
        require_once '../controllers/ServerController.php';
        $serverController = new ServerController($db);
        $serverController->store(); 
        break;

    // =====================================================================
    // [ĐÃ SỬA] PHẦN XỬ LÝ QUẢNG CÁO (BANNER)
    // =====================================================================
    
    // 1. Hiển thị form đăng ký (GỌI QUA CONTROLLER)
    case 'banner-register':
        // Không require header ở đây nữa, Controller sẽ require.
        require_once '../controllers/HomeBannerController.php';
        $adsController = new HomeBannerController($db);
        
        // Gọi hàm index() để tính toán giá, số lượng, user coin...
        // Hàm này sẽ tự include 'header.php' và 'banner-register.php' bên trong nó
        $adsController->index(); 
        break;

    // 2. Xử lý POST đăng ký
    case 'banner-register-action':
        require_once '../controllers/HomeBannerController.php';
        $adsController = new HomeBannerController($db);
        $adsController->register(); 
        break;

    // --- 404 ---
    default:
        require_once 'includes/header.php';
        echo "<div style='text-align:center; margin-top:100px; color: #fff;'>";
        echo "<h1>404 - NOT FOUND</h1>";
        echo "<a href='index.php'>Quay về trang chủ</a>";
        echo "</div>";
        break;
}
?>