<?php
/**
 * Mumoira.mobi - Main Router (index.php)
 * Phiên bản ổn định - Đã fix lỗi 404 Gia hạn & URL
 */

// =========================================================================
// 1. CẤU HÌNH & KHỞI TẠO
// =========================================================================

// Khởi động Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cấu hình hiển thị lỗi (Bật = 1 khi dev, Tắt = 0 khi chạy thật)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Đường dẫn gốc hệ thống (Thư mục cha của public)
$rootPath = dirname(__DIR__); 

// --- [QUAN TRỌNG] TỰ ĐỘNG XÁC ĐỊNH BASE URL ---
// Giúp link không bị lỗi khi chạy ở localhost hay hosting, thư mục con hay root
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
// Chuẩn hóa dấu gạch chéo để tránh lỗi trên Windows/Linux
$scriptDir = str_replace('\\', '/', $scriptDir);
// $baseUrl sẽ là: http://localhost/mu-ads-platform/public hoặc https://domain.com
$baseUrl = rtrim($protocol . "://" . $host . $scriptDir, '/');

// Nhúng file kết nối Database
require_once $rootPath . '/config/Database.php';

// Kết nối Database
$database = new Database();
$db = $database->connect();

// Lấy tham số URL từ .htaccess (Mặc định là 'home')
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home';

// =========================================================================
// 2. BỘ ĐIỀU HƯỚNG (ROUTER SWITCH)
// =========================================================================

switch ($url) {
    
    // ---------------------------------------------------------------------
    // NHÓM 1: TRANG CHỦ & THÔNG TIN CHUNG
    // ---------------------------------------------------------------------
    case 'home':
    case '':
        require_once $rootPath . '/controllers/HomeController.php';
        $homeCtrl = new HomeController($db);
        $homeCtrl->index();
        break;

    case 'huong-dan':
        require_once 'includes/header.php';
        require_once 'guide.php';
        require_once 'includes/footer.php';
        break;

    // ---------------------------------------------------------------------
    // NHÓM 2: TÀI KHOẢN (ĐĂNG NHẬP, ĐĂNG KÝ, PROFILE)
    // ---------------------------------------------------------------------
    case 'login':
        require_once 'includes/header.php';
        require_once 'auth.php';
        require_once 'includes/footer.php';
        break;

    case 'register':
        $_GET['mode'] = 'register'; // Cờ hiệu báo trang auth hiển thị form đăng ký
        require_once 'includes/header.php';
        require_once 'auth.php';
        require_once 'includes/footer.php';
        break;

    case 'login-action':
        require_once $rootPath . '/controllers/AuthController.php';
        (new AuthController($db))->login();
        break;

    case 'register-action':
        require_once $rootPath . '/controllers/AuthController.php';
        (new AuthController($db))->register();
        break;

    case 'logout':
        require_once $rootPath . '/controllers/AuthController.php';
        (new AuthController($db))->logout();
        break;

    case 'profile':
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $baseUrl . "/index.php?url=login");
            exit;
        }
        require_once 'profile.php';
        break;

    case 'update-profile':
        require_once $rootPath . '/controllers/AuthController.php';
        (new AuthController($db))->updateProfile();
        break;

    // ---------------------------------------------------------------------
    // NHÓM 3: QUẢN LÝ SERVER (THÀNH VIÊN)
    // ---------------------------------------------------------------------
    case 'manage-server':
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $baseUrl . "/index.php?url=login");
            exit;
        }
        require_once $rootPath . '/controllers/ServerController.php';
        $serverCtrl = new ServerController($db);
        $serverCtrl->manage();
        require_once 'includes/footer.php';
        break;

    case 'create-server':
        if (!isset($_SESSION['user_id'])) {
// Thay dấu ? thứ hai thành dấu &
header("Location: " . $baseUrl . "/index.php?url=login&error=" . urlencode("Vui lòng đăng nhập"));            exit;
        }
        require_once 'includes/header.php';
        require_once 'create_server.php';
        require_once 'includes/footer.php';
        break;

    case 'create-server-action':
        require_once $rootPath . '/controllers/ServerController.php';
        (new ServerController($db))->store();
        break;

    // [QUAN TRỌNG] Xử lý gia hạn - Khớp với JS confirmRenew
    case 'renew':
    case 'renew-server':
        require_once $rootPath . '/controllers/ServerController.php';
        (new ServerController($db))->renew();
        break;

    // ---------------------------------------------------------------------
    // NHÓM 4: QUẢNG CÁO (BANNER)
    // ---------------------------------------------------------------------
    case 'banner-register':
        require_once $rootPath . '/controllers/HomeBannerController.php';
        (new HomeBannerController($db))->index();
        break;

    case 'banner-register-action':
        require_once $rootPath . '/controllers/HomeBannerController.php';
        (new HomeBannerController($db))->register();
        break;

    // ---------------------------------------------------------------------
    // NHÓM 5: URL ĐỘNG (SEO) & 404
    // ---------------------------------------------------------------------
    default:
        // 5.1: Chi tiết Server (Ví dụ: mu-ha-noi-xua-s15)
        if (preg_match('/-s(\d+)$/', $url, $matches)) {
            $_GET['id'] = $matches[1];
            
            require_once 'includes/header.php'; 
            require_once $rootPath . '/controllers/ServerController.php';
            
            $serverCtrl = new ServerController($db);
            $serverCtrl->detail(); 
            
            require_once 'includes/footer.php';
            break;
        }

        // 5.2: Lọc theo Version (Ví dụ: season-6-v5)
        if (preg_match('/-v(\d+)$/', $url, $matches)) {
            $_GET['filter_version'] = $matches[1];
            require_once $rootPath . '/controllers/HomeController.php';
            (new HomeController($db))->index();
            break;
        }

        // 5.3: Lọc theo Reset (Ví dụ: reset-vip-r2)
        if (preg_match('/-r(\d+)$/', $url, $matches)) {
            $_GET['filter_reset'] = $matches[1];
            require_once $rootPath . '/controllers/HomeController.php';
            (new HomeController($db))->index();
            break;
        }

        // -----------------------------------------------------------------
        // TRANG LỖI 404 (KHÔNG TÌM THẤY)
        // -----------------------------------------------------------------
        require_once 'includes/header.php';
        ?>
        <div class="container text-center text-white" style="min-height: 60vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <h1 style="font-size: 8rem; font-family: 'Arial', sans-serif; font-weight: bold; color: #b91c1c;">404</h1>
            <h3 class="text-uppercase mb-4">Không tìm thấy trang này</h3>
            <p class="text-muted mb-4">
                Đường dẫn <em>/<?php echo htmlspecialchars($url); ?></em> không tồn tại trên hệ thống.
            </p>
            <a href="<?php echo $baseUrl; ?>" class="btn btn-secondary px-4 py-2">
                <i class="fa-solid fa-house me-2"></i> Về Trang Chủ
            </a>
        </div>
        <?php
        require_once 'includes/footer.php';
        break;
}
?>