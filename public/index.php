<?php
// 1. Khởi động Session (BẮT BUỘC PHẢI Ở DÒNG ĐẦU TIÊN)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Cấu hình hiển thị lỗi (Tắt đi khi chạy thực tế/production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. Nhúng các file cấu hình và Model cần thiết
// Đảm bảo đường dẫn chính xác tới thư mục config và models
require_once '../config/Database.php';
require_once '../models/MasterData.php'; 

// 4. Khởi tạo kết nối DB
$database = new Database();
$db = $database->connect();

// Khởi tạo MasterData để lấy menu chung cho toàn trang
$masterData = new MasterData($db);

// Lấy danh sách Version và Reset Types (Biến này sẽ dùng được ở mọi file view được require bên dưới)
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
// 5. BỘ ĐIỀU HƯỚNG (ROUTER)
// =========================================================================

$url = isset($_GET['url']) ? $_GET['url'] : 'home';

switch ($url) {
    
    // --- TRANG CHỦ ---
    case 'home':
        if (file_exists('home.php')) {
            require_once 'home.php';
        } else {
            // Nếu chưa có file home, hiển thị tạm Header để test
            require_once 'includes/header.php';
            echo "<div style='color:white; text-align:center; margin-top:50px;'>File home.php chưa tồn tại. Header đã hoạt động.</div>";
        }
        break;

    // --- CÁC CHỨC NĂNG AUTH (ĐĂNG NHẬP / ĐĂNG KÝ / LOGOUT) ---
    
    // 1. Hiển thị form Đăng nhập
    case 'login':
        require_once 'auth.php';
        break;

    // 2. Hiển thị form Đăng ký (Set mode để auth.php biết cần mở tab đăng ký)
    case 'register':
        $_GET['mode'] = 'register'; 
        require_once 'auth.php';
        break;

    // 3. Xử lý logic khi bấm nút Đăng nhập
    case 'login-action':
        require_once '../controllers/AuthController.php';
        $auth = new AuthController($db);
        $auth->login();
        break;

    // 4. Xử lý logic khi bấm nút Đăng ký
    case 'register-action':
        require_once '../controllers/AuthController.php';
        $auth = new AuthController($db);
        $auth->register();
        break;

    // 5. Đăng xuất
    case 'logout':
        require_once '../controllers/AuthController.php';
        $auth = new AuthController($db);
        $auth->logout();
        break;


    // --- CÁC CHỨC NĂNG SERVER (MU) ---

    // Form đăng server mới
    case 'create-server':
        // Kiểm tra login đơn giản tại router (hoặc kiểm tra trong Controller)
        if (!isset($_SESSION['user_id'])) {
            // Chưa đăng nhập thì đá về trang login
            header("Location: index.php?url=login&error=" . urlencode("Bạn cần đăng nhập để đăng bài"));
            exit;
        }
        require_once 'create_server.php';
        break;

    // Xử lý lưu server vào DB
    case 'create-server-action':
        require_once '../controllers/ServerController.php';
        $serverController = new ServerController($db);
        $serverController->store(); 
        break;

    // --- CÁC CHỨC NĂNG QUẢNG CÁO ---
    
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

    // --- TRANG LỖI 404 ---
    default:
        // Có thể include header ở đây để trang lỗi vẫn đẹp
        require_once 'includes/header.php';
        echo "<div style='text-align:center; margin-top:100px; color: #fff;'>";
        echo "<h1 style='color: #8b0000; font-size: 4rem;'>404</h1>";
        echo "<h2>KHÔNG TÌM THẤY TRANG</h2>";
        echo "<p>Đường dẫn bạn truy cập không tồn tại hoặc đã bị xóa.</p>";
        echo "<a href='index.php' style='color: #cfaa56; text-decoration: underline;'>Quay về trang chủ</a>";
        echo "</div>";
        break;
}
?>