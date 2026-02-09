<?php
/**
 * Mumoira.mobi - Main Router
 * Viết lại ngày: 09/02/2026
 * Mục tiêu: Tối ưu SEO, URL thân thiện, loại bỏ index.php
 */

// =========================================================================
// 1. CẤU HÌNH HỆ THỐNG
// =========================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bật thông báo lỗi để debug (Tắt khi chạy chính thức)
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Đường dẫn tương đối đến thư mục gốc
$rootPath = dirname(__DIR__); 

// Nhúng Database
require_once $rootPath . '/config/Database.php';

// Khởi tạo kết nối DB
$database = new Database();
$db = $database->connect();

// Lấy URL từ .htaccess truyền vào
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home';

// =========================================================================
// 2. BỘ ĐIỀU HƯỚNG (ROUTER)
// =========================================================================

switch ($url) {
    
    // --- TRANG CHỦ ---
    case 'home':
    case '':
        require_once $rootPath . '/controllers/HomeController.php';
        $homeCtrl = new HomeController($db);
        $homeCtrl->index();
        break;

    // --- CÁC TRANG TĨNH & HỖ TRỢ ---
    case 'huong-dan':
        require_once 'includes/header.php';
        require_once 'guide.php';
        require_once 'includes/footer.php';
        break;

    // --- HỆ THỐNG TÀI KHOẢN (AUTH) ---
    case 'login':
        require_once 'includes/header.php';
        require_once 'auth.php';
        require_once 'includes/footer.php';
        break;

    case 'register':
        $_GET['mode'] = 'register'; // Báo cho file auth.php biết là đang đăng ký
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

    // --- HỒ SƠ CÁ NHÂN (USER PROFILE) ---
    case 'profile':
        if (!isset($_SESSION['user_id'])) {
            header("Location: login");
            exit;
        }
        require_once 'profile.php';
        break;

    case 'update-profile':
        require_once $rootPath . '/controllers/AuthController.php';
        (new AuthController($db))->updateProfile();
        break;

    // --- QUẢN LÝ SERVER (DÀNH CHO THÀNH VIÊN) ---
    case 'manage-server':
        require_once $rootPath . '/controllers/ServerController.php';
        $serverCtrl = new ServerController($db);
        $serverCtrl->manage();
        require_once 'includes/footer.php'; // Nếu view chưa include footer
        break;

    case 'create-server':
        if (!isset($_SESSION['user_id'])) {
            header("Location: login?error=" . urlencode("Bạn cần đăng nhập để đăng bài"));
            exit;
        }
        require_once 'includes/header.php';
        require_once 'create_server.php';
        require_once 'includes/footer.php';
        break;

    case 'create-server-action':
        require_once $rootPath . '/controllers/ServerController.php';
        (new ServerController($db))->store();
        break;

    case 'renew-server':
        require_once $rootPath . '/controllers/ServerController.php';
        (new ServerController($db))->renew();
        break;

    // --- QUẢNG CÁO (BANNER) ---
    case 'banner-register':
        require_once $rootPath . '/controllers/HomeBannerController.php';
        (new HomeBannerController($db))->index();
        break;

    case 'banner-register-action':
        require_once $rootPath . '/controllers/HomeBannerController.php';
        (new HomeBannerController($db))->register();
        break;

    // --- XỬ LÝ CÁC URL ĐỘNG (SEO) ---
    default:
        // 1. CHI TIẾT SERVER (SEO URL)
        // Mẫu: domain.com/mu-ha-noi-xua-s15 (Lấy ID là 15)
        if (preg_match('/-s(\d+)$/', $url, $matches)) {
            $_GET['id'] = $matches[1]; // Gán ID vào $_GET để controller dùng
            
            require_once 'includes/header.php'; // Header phải load trước để nhận biến SEO
            require_once $rootPath . '/controllers/ServerController.php';
            
            $serverCtrl = new ServerController($db);
            $serverCtrl->detail(); 
            
            require_once 'includes/footer.php';
            break;
        }

        // 2. LỌC THEO PHIÊN BẢN (VERSION)
        // Mẫu: domain.com/season-6-v5 (Lấy ID là 5)
        if (preg_match('/-v(\d+)$/', $url, $matches)) {
            $_GET['filter_version'] = $matches[1];
            
            require_once $rootPath . '/controllers/HomeController.php';
            $homeCtrl = new HomeController($db);
            $homeCtrl->index();
            break;
        }

        // 3. LỌC THEO LOẠI RESET
        // Mẫu: domain.com/reset-vip-r2 (Lấy ID là 2)
        if (preg_match('/-r(\d+)$/', $url, $matches)) {
            $_GET['filter_reset'] = $matches[1];
            
            require_once $rootPath . '/controllers/HomeController.php';
            $homeCtrl = new HomeController($db);
            $homeCtrl->index();
            break;
        }

        // --- TRANG LỖI 404 (NẾU KHÔNG KHỚP CÁI NÀO) ---
        require_once 'includes/header.php';
        ?>
        <div class="container text-center text-white" style="min-height: 60vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <h1 style="font-size: 8rem; font-family: 'Metal Mania', cursive; color: #b91c1c; text-shadow: 0 0 10px #000;">404</h1>
            <h3 class="text-uppercase mb-4" style="letter-spacing: 2px;">Không tìm thấy trang yêu cầu</h3>
            <p class="text-muted mb-5">Đường dẫn: <em>/<?php echo htmlspecialchars($url); ?></em> không tồn tại.</p>
            <a href="<?php echo $baseUrl; ?>" class="mh-btn-post px-5 py-3">
                <i class="fa-solid fa-house me-2"></i> Quay Về Trang Chủ
            </a>
        </div>
        <?php
        require_once 'includes/footer.php';
        break;
}
?>