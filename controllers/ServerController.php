<?php
require_once '../models/Server.php';

class ServerController {

    private $serverModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->serverModel = new Server($db);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // =================================================================
    // PHẦN 1: QUẢN LÝ USER (MỚI BỔ SUNG)
    // =================================================================

    
public function manage() {
    // 1. Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?url=login&error=" . urlencode("Vui lòng đăng nhập!"));
        exit;
    }

    // 2. Lấy danh sách server từ Model
    $rawServers = $this->serverModel->getServersByUserId($_SESSION['user_id']);

    $servers = [];
    foreach ($rawServers as $sv) {
        $packageKey = $sv['banner_package']; // Ví dụ: 'VIP', 'BASIC'
        
        // Lấy cấu hình từ Model (File Server.php)
        // Nếu không tìm thấy gói thì lấy mặc định là 0 đồng, 7 ngày
        $packConfig = $this->serverModel->packages[$packageKey] ?? [
            'price' => 0, 
            'days'  => 7, 
            'label' => $packageKey
        ];

        // ========================================================
        // [QUAN TRỌNG] BỔ SUNG CÁC TRƯỜNG CHO VIEW SỬ DỤNG
        // ========================================================
        
        // 1. Label hiển thị (VD: VIP, BASIC)
        $sv['package_label'] = $packConfig['label']; 
        
        // 2. Giá tiền (để echo $sv['package_price'])
        $sv['package_price'] = $packConfig['price']; 
        
        // 3. Số ngày (để echo $sv['package_days'])
        $sv['package_days']  = $packConfig['days'];

        // 4. Object dùng cho JavaScript (Popup gia hạn)
        $sv['bannerPackage'] = [
            'label' => $packConfig['label'],
            'price' => $packConfig['price'],
            'durationDays' => $packConfig['days']
        ];

        // Xử lý ảnh banner
        if (empty($sv['banner_image'])) {
            $sv['banner_image'] = 'assets/images/no-image.jpg';
        }

        $servers[] = $sv;
    }

    // Gọi View
    require_once '../public/manage_servers.php'; 
}

    /**
     * Xử lý hành động Gia hạn (Action)
     */
    public function renew() {
    // 1. Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?url=login");
        exit;
    }

    $serverId = $_GET['id'] ?? 0;
    $userId = $_SESSION['user_id'];
  
    $server = $this->serverModel->getServerById($serverId);

    if (!$server || $server['user_id'] != $userId) {
        header("Location: index.php?url=manage-server&status=error&message=" . urlencode("Máy chủ không tồn tại hoặc không thuộc quyền quản lý của bạn!"));
        exit;
    }

    if ($server['package_price'] <= 0) {
        header("Location: index.php?url=manage-server&status=error&message=" . urlencode("Gói miễn phí không thể gia hạn (chống Spam)!"));
        exit;
    }


    $result = $this->serverModel->renew($serverId, $userId);

    if ($result === true) {
        header("Location: index.php?url=manage-server&status=success&message=" . urlencode("Gia hạn thành công!"));
    } else {
        header("Location: index.php?url=manage-server&status=error&message=" . urlencode($result));
    }
    exit;
}

    
    public function detail() {
        // 1. Lấy ID từ URL
        $id = $_GET['id'] ?? 0;
        if ($id <= 0) {
            header("Location: index.php");
            exit;
        }

        // 2. Lấy dữ liệu đầy đủ từ Database thông qua Model
        // Đảm bảo getDetailFull đã JOIN với các bảng versions, server_types, reset_types
        $rawData = $this->serverModel->getDetailFull($id);
        
        if (!$rawData) {
            // Nếu không tìm thấy server, trả về 404 hoặc về trang chủ
            header("HTTP/1.0 404 Not Found");
            die("Máy chủ không tồn tại hoặc đã hết hạn.");
        }

        // 3. XỬ LÝ SEO (PHẦN QUAN TRỌNG NHẤT)
        // Nhúng class SeoHelper
        require_once '../models/SeoHelper.php';
        
        // Tạo đối tượng SEO từ dữ liệu thô trong DB
        $seoData = SeoHelper::generateMeta($rawData);
        
        // Đưa vào biến Global để file header.php có thể truy cập được
        $GLOBALS['seo'] = $seoData;

        // 4. ĐỔ DỮ LIỆU VÀO VIEWMODEL (Giữ nguyên cấu trúc của bạn nhưng làm sạch dữ liệu)
        $server = new ServerViewModel();
        $server->id             = $rawData['server_id'];
        $server->serverName     = htmlspecialchars($rawData['server_name']);
        $server->muName         = htmlspecialchars($rawData['mu_name']);
        $server->slogan         = htmlspecialchars($rawData['slogan']);
        $server->bannerPackage  = $rawData['banner_package'];
        
        // Xử lý ảnh Banner (Ưu tiên ảnh upload, nếu không có dùng ảnh mặc định)
        if (!empty($rawData['banner_image'])) {
            // Nếu là link URL từ web khác thì giữ nguyên, nếu là file thì nối đường dẫn
            $server->bannerImage = (filter_var($rawData['banner_image'], FILTER_VALIDATE_URL)) 
                                    ? $rawData['banner_image'] 
                                    : 'uploads/' . basename($rawData['banner_image']);
        } else {
            $server->bannerImage = 'assets/images/no-image.jpg';
        }

        $server->websiteUrl     = $rawData['website_url'];
        $server->fanpageUrl     = $rawData['fanpage_url'];
        $server->description    = $rawData['description']; // Giữ nguyên HTML nếu dùng trình soạn thảo
        $server->serverTypeName = $rawData['type_name'] ?? 'Normal'; // Lấy từ bảng server_types

        // 5. CHI TIẾT THÔNG SỐ (STATS)
        $server->stats = new ServerStatsViewModel();
        $server->stats->muVersionName = $rawData['version_name'] ?? 'Chưa xác định';
        $server->stats->expRate       = number_format($rawData['exp_rate']) . 'x';
        $server->stats->dropRate      = $rawData['drop_rate'] . '%';
        $server->stats->antiHack      = htmlspecialchars($rawData['anti_hack'] ?? 'N/A');
        $server->stats->resetTypeName = $rawData['reset_name'] ?? 'No Reset';
        $server->stats->pointTypeName = $rawData['point_name'] ?? 'Cơ bản';

        // 6. LỊCH TRÌNH (SCHEDULE)
        $server->schedule = new ServerScheduleViewModel();
        $server->schedule->alphaDate = !empty($rawData['alpha_date']) ? date('d/m/Y', strtotime($rawData['alpha_date'])) : '---';
        $server->schedule->alphaTime = !empty($rawData['alpha_time']) ? date('H:i', strtotime($rawData['alpha_time'])) : '';
        $server->schedule->betaDate  = !empty($rawData['beta_date']) ? date('d/m/Y', strtotime($rawData['beta_date'])) : '---';
        $server->schedule->betaTime  = !empty($rawData['beta_time']) ? date('H:i', strtotime($rawData['beta_time'])) : '';

        // 7. GỌI VIEW HIỂN THỊ
        require_once '../public/server_detail.php';
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=login&error=" . urlencode("Bạn phải đăng nhập!"));
            exit;
        }

        $finalBanner = $this->handleBannerUpload();

        $data = [
            'user_id'       => $_SESSION['user_id'],
            'server_name'   => trim($_POST['server_name'] ?? ''),
            'mu_name'       => trim($_POST['mu_name'] ?? ''),
            'slogan'        => trim($_POST['slogan'] ?? ''),
            'website_url'   => trim($_POST['website_url'] ?? ''),
            'fanpage_url'   => trim($_POST['fanpage_url'] ?? ''),
            'description'   => trim($_POST['description'] ?? ''),
            'banner_image'  => $finalBanner,
            'banner_package'=> $_POST['banner_package'] ?? 'BASIC',
            'version_id'    => $_POST['version_id'] ?? null,
            'type_id'       => $_POST['type_id'] ?? null,
            'reset_id'      => $_POST['reset_id'] ?? null,
            'point_id'      => $_POST['point_id'] ?? null,
            'exp_rate'      => $_POST['exp_rate'] ?? 0,
            'drop_rate'     => $_POST['drop_rate'] ?? 0,
            'anti_hack'     => trim($_POST['anti_hack'] ?? ''),
            'alpha_date'    => $_POST['alpha_date'] ?? null,
            'alpha_time'    => $_POST['alpha_time'] ?? null,
            'open_date'     => $_POST['beta_date'] ?? null,
            'open_time'     => $_POST['beta_time'] ?? null
        ];

        $result = $this->serverModel->createFull($data);
        header("Location: index.php?url=create-server&status=" . ($result ? "success" : "error"));
        exit;
    }

    private function handleBannerUpload() {
        $uploadType = $_POST['uploadType'] ?? 'file';
        $bannerString = '';

        if ($uploadType === 'file') {
            if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === 0) {
                $targetDir = "uploads/"; // Lưu ý đường dẫn tương đối
                if (!file_exists("../public/" . $targetDir)) mkdir("../public/" . $targetDir, 0777, true);

                $fileName = $_FILES['banner_file']['name'];
                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowTypes = ['jpg','jpeg','png','gif','webp'];

                if (in_array($fileType, $allowTypes) && $_FILES['banner_file']['size'] <= 5 * 1024 * 1024) {
                    $newFileName = time() . '_' . rand(1000,9999) . '.' . $fileType;
                    if (move_uploaded_file($_FILES['banner_file']['tmp_name'], "../public/" . $targetDir . $newFileName)) {
                        $bannerString = $targetDir . $newFileName;
                    }
                }
            }
        } else {
            $url = trim($_POST['banner_url'] ?? '');
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                $bannerString = $url;
            }
        }
        return $bannerString;
    }
}

class ServerViewModel {
    public $id;
    public $serverName;
    public $muName;
    public $slogan;
    public $bannerPackage;
    public $bannerImage;
    public $websiteUrl;
    public $fanpageUrl;
    public $description;
    public $serverTypeName;
    public $stats;    
    public $schedule; 
}

class ServerStatsViewModel {
    public $muVersionName;
    public $expRate;
    public $dropRate;
    public $antiHack;
    public $resetTypeName;
    public $pointTypeName;
}

class ServerScheduleViewModel {
    public $alphaDate;
    public $alphaTime;
    public $betaDate;
    public $betaTime;
}
?>