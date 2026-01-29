<?php
// 1. Khởi động Session
session_start();

// 2. Cấu hình hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. Nhúng các file cấu hình và Model cần thiết
require_once '../config/Database.php';
require_once '../models/MasterData.php'; // [MỚI] Nhúng Model MasterData

// 4. Khởi tạo kết nối DB
$database = new Database();
$db = $database->connect();

// =========================================================================
// [PHẦN BỔ SUNG] CHUẨN BỊ DỮ LIỆU TOÀN CỤC CHO HEADER (GLOBAL DATA)
// =========================================================================

// Khởi tạo MasterData để lấy menu
$masterData = new MasterData($db);

// Lấy danh sách Version và Reset Types (Biến này sẽ dùng được ở mọi file được require bên dưới)
$menuVersions = $masterData->getList('versions'); 
$menuTypes    = $masterData->getList('resets');

// Hàm hỗ trợ tạo Slug URL (VD: Season 6.9 -> season-6-9)
if (!function_exists('createSlug')) {
    function createSlug($str) {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
        $str = preg_replace('/([\s]+)/', '-', $str);
        return $str;
    }
}
// =========================================================================

// 5. Lấy yêu cầu từ URL (Router)
$url = isset($_GET['url']) ? $_GET['url'] : 'home';

/**
 * --- BẢNG ĐIỀU KHIỂN ROUTING ---
 */
switch ($url) {
    
    case 'home':
        // Vì $menuVersions đã có ở trên, trong home.php chỉ cần include header là chạy
        if (file_exists('home.php')) {
            require_once 'home.php';
        } else {
            // Fallback nếu chưa có home.php
            echo "File home.php chưa tồn tại. Vui lòng tạo file.";
        }
        break;

    case 'create-server':
        require_once 'create_server.php';
        break;

    case 'create-server-action':
        require_once '../controllers/ServerController.php';
        $serverController = new ServerController($db);
        $serverController->store(); 
        break;

    case 'login':
        echo "Trang đăng nhập đang xây dựng...";
        break;

    case 'register':
        echo "Trang đăng ký đang xây dựng...";
        break;
        
    case 'ads':
        require_once '../controllers/HomeBannerController.php';
        $adsController = new HomeBannerController($db);
        $adsController->index();
        break;

    case 'banner-register-action':
        require_once '../controllers/HomeBannerController.php';
        $adsController = new HomeBannerController($db);
        $adsController->register(); 
        break;

    // --- TRANG 404 ---
    default:
        echo "<div style='text-align:center; margin-top:50px;'>";
        echo "<h1>404 Not Found</h1>";
        echo "<p>Đường dẫn bạn truy cập không tồn tại.</p>";
        echo "<a href='index.php'>Quay về trang chủ</a>";
        echo "</div>";
        break;
}
?>