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
// 2. INCLUDE MODEL & DATABASE
// =========================================================================
// Lưu ý: Đảm bảo đường dẫn file chính xác so với thư mục public/
require_once '../config/Database.php';
require_once '../models/MasterData.php'; 
require_once '../models/Server.php';      // Include Server Model
require_once '../models/HomeBanner.php';  // Include Banner Model

$database = new Database();
$db = $database->connect();

// Khởi tạo các đối tượng dùng chung
$masterData = new MasterData($db);
$menuVersions = $masterData->getList('versions'); 
$menuTypes    = $masterData->getList('resets');

// =========================================================================
// 3. BỘ ĐIỀU HƯỚNG (ROUTER)
// =========================================================================

$url = isset($_GET['url']) ? $_GET['url'] : 'home';

switch ($url) {
    
    // --- TRANG CHỦ (LOGIC CHÍNH ĐỂ SỬA LỖI) ---
    case 'home':
        // 1. Khởi tạo Model
        $srvModel = new Server($db);
        $banModel = new HomeBanner($db);

        // 2. Lấy tham số lọc từ URL
        $filterType = $_GET['filterType'] ?? 'open'; // Mặc định là Open Beta
        $selectedVersion = $_GET['versionId'] ?? null;
        $selectedReset = $_GET['reset'] ?? null;
        
        // Cờ kiểm tra đang tìm kiếm
        $isSearching = (!empty($selectedVersion) || !empty($selectedReset));
        $filterDisplay = "KẾT QUẢ TÌM KIẾM";

        // 3. Lấy danh sách Server từ Database
        $allServers = $srvModel->getHomeServers($filterType, $selectedVersion, $selectedReset);

        // 4. Phân loại Server (SuperVIP, VIP, Normal)
        $superVips = [];
        $vips = [];
        $normals = [];

        if ($allServers) {
            foreach ($allServers as $sv) {
                // Format lại định dạng ngày hiển thị (d/m/Y)
                if (!empty($sv['date_alpha'])) $sv['date_alpha'] = date('d/m/Y', strtotime($sv['date_alpha']));
                if (!empty($sv['date_open']))  $sv['date_open']  = date('d/m/Y', strtotime($sv['date_open']));
                
                // Gán ảnh mặc định nếu thiếu
                if (empty($sv['banner_image'])) {
                     // Gán ảnh placeholder để tránh lỗi hiển thị ảnh vỡ
                     $sv['image_url'] = "https://via.placeholder.com/600x60/333/ccc?text=No+Image"; 
                } else {
                     // Mapping cột banner_image trong DB sang image_url mà home.php cần
                     $sv['image_url'] = $sv['banner_image']; 
                }

                // Phân loại
                if ($sv['banner_package'] === 'SUPER_VIP') {
                    $superVips[] = $sv;
                } elseif ($sv['banner_package'] === 'VIP') {
                    $vips[] = $sv;
                } else {
                    $normals[] = $sv;
                }
            }
        }

        // 5. Lấy Banner & Phân loại vị trí
        // Hàm getRunningBanners() trả về PDOStatement, cần fetchAll
        $stmtBanners = $banModel->getRunningBanners();
        $rawBanners = $stmtBanners->fetchAll(PDO::FETCH_ASSOC);

        $bannersLeft = [];
        $bannersRight = [];
        $bannersHero = [];
        $bannersStd = [];

        foreach ($rawBanners as $b) {
            // Mapping đúng tên cột trong DB (position_code)
            switch ($b['position_code']) {
                case 'left':    $bannersLeft[] = $b; break;
                case 'right':   $bannersRight[] = $b; break;
                case 'hero':    $bannersHero[] = $b; break;
                case 'standard': $bannersStd[] = $b; break;
                // Nếu DB lưu mã khác (ví dụ: 'left_wing'), hãy sửa case này
            }
        }

        // 6. Gọi giao diện hiển thị
        require_once 'includes/header.php'; // Header chung
        if (file_exists('home.php')) {
            require_once 'home.php';
        } else {
            echo "Lỗi: Không tìm thấy file home.php";
        }
        break;

    // --- AUTH ---
    case 'login':
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
        if (!isset($_SESSION['user_id'])) {
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

    // --- QUẢNG CÁO ---
    case 'banner-register':
        require_once '../controllers/HomeBannerController.php';
        $adsController = new HomeBannerController($db);
        $adsController->index(); 
        break;

    case 'banner-register-action':
        require_once '../controllers/HomeBannerController.php';
        $adsController = new HomeBannerController($db);
        $adsController->register(); 
        break;

    // --- 404 ---
    default:
        require_once 'includes/header.php';
        echo "<div class='container text-center text-white mt-5'><h1>404 - Trang không tồn tại</h1><a href='index.php' class='btn btn-warning'>Về Trang Chủ</a></div>";
        break;
}
?>