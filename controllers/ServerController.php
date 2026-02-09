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
    // 1. HIỂN THỊ CHI TIẾT (QUAN TRỌNG NHẤT CHO YÊU CẦU CỦA BẠN)
    // =================================================================
    
    /**
     * Hiển thị chi tiết server
     * @param int $urlId ID nhận từ Router (nếu có)
     */
    public function detail($urlId = 0) {
        // Ưu tiên lấy ID từ tham số truyền vào (Router SEO), nếu không có thì lấy $_GET
        $id = ($urlId > 0) ? $urlId : ($_GET['id'] ?? 0);

        if ($id <= 0) {
            header("Location: /");
            exit;
        }

        // Lấy dữ liệu đầy đủ từ Model
        $rawData = $this->serverModel->getDetailFull($id);
        
        if (!$rawData) {
            // Không tìm thấy server
            require_once '../public/includes/header.php';
            echo '<div class="container pt-5 text-center text-white"><h3>Máy chủ không tồn tại hoặc đã bị xóa.</h3><a href="/" class="btn btn-secondary mt-3">Quay lại</a></div>';
            require_once '../public/includes/footer.php';
            exit;
        }

        // --- XỬ LÝ SEO ---
        // (Giả sử bạn có class SeoHelper, nếu không có thể bỏ qua đoạn này)
        if (class_exists('SeoHelper')) {
            $seoData = SeoHelper::generateMeta($rawData);
            $GLOBALS['seo'] = $seoData; 
        }

        // --- CHUẨN BỊ DỮ LIỆU CHO VIEW ---
        // View yêu cầu cấu trúc nested object ($server->stats->expRate)
        // Nên ta dùng các ViewModel Class ở cuối file để định hình dữ liệu
        
        $server = new ServerViewModel();
        $server->id             = $rawData['server_id'];
        $server->serverName     = htmlspecialchars($rawData['server_name']);
        $server->muName         = htmlspecialchars($rawData['mu_name']);
        $server->slogan         = htmlspecialchars($rawData['slogan']);
        $server->bannerPackage  = $rawData['banner_package'];
        
        // Xử lý link ảnh banner
        if (!empty($rawData['banner_image'])) {
            $server->bannerImage = (filter_var($rawData['banner_image'], FILTER_VALIDATE_URL)) 
                                    ? $rawData['banner_image'] 
                                    : 'uploads/' . basename($rawData['banner_image']);
        } else {
            $server->bannerImage = 'assets/images/no-image.jpg'; // Ảnh mặc định
        }

        $server->websiteUrl     = $rawData['website_url'];
        $server->fanpageUrl     = $rawData['fanpage_url'];
        $server->description    = $rawData['description']; // HTML content
        $server->serverTypeName = $rawData['type_name'] ?? 'Normal';

        // Object con: Thống kê (Stats)
        $server->stats = new ServerStatsViewModel();
        $server->stats->muVersionName = $rawData['version_name'] ?? 'Unknown';
        $server->stats->expRate       = number_format($rawData['exp_rate']) . 'x';
        $server->stats->dropRate      = $rawData['drop_rate'];
        $server->stats->antiHack      = htmlspecialchars($rawData['anti_hack'] ?? 'N/A');
        $server->stats->resetTypeName = $rawData['reset_name'] ?? 'Reset';
        $server->stats->pointTypeName = $rawData['point_name'] ?? '5/7';

        // Object con: Lịch trình (Schedule)
        $server->schedule = new ServerScheduleViewModel();
        $server->schedule->alphaDate = $rawData['alpha_date']; // View sẽ tự format date()
        $server->schedule->alphaTime = $rawData['alpha_time'];
        $server->schedule->betaDate  = $rawData['beta_date'];
        $server->schedule->betaTime  = $rawData['beta_time'];

        // --- GỌI VIEW ---
        // $server sẽ được dùng bên trong file view này
        require_once '../public/server_detail.php';
    }

    // =================================================================
    // 2. CÁC CHỨC NĂNG QUẢN LÝ (MANAGE, STORE, RENEW)
    // =================================================================

    public function manage() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login?error=" . urlencode("Vui lòng đăng nhập!"));
            exit;
        }

        $rawServers = $this->serverModel->getServersByUserId($_SESSION['user_id']);
        $servers = [];

        foreach ($rawServers as $sv) {
            $packageKey = $sv['banner_package'];
            $packConfig = $this->serverModel->packages[$packageKey] ?? ['price'=>0, 'days'=>7, 'label'=>$packageKey];

            $sv['package_label'] = $packConfig['label']; 
            $sv['package_price'] = $packConfig['price']; 
            $sv['package_days']  = $packConfig['days'];

            $sv['bannerPackage'] = [
                'label' => $packConfig['label'],
                'price' => $packConfig['price'],
                'durationDays' => $packConfig['days']
            ];

            if (empty($sv['banner_image'])) {
                $sv['banner_image'] = 'assets/images/no-image.jpg';
            }
            $servers[] = $sv;
        }

        require_once '../public/manage_servers.php'; 
    }

    public function renew() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login");
            exit;
        }

        $serverId = $_GET['id'] ?? 0;
        $userId = $_SESSION['user_id'];
        $server = $this->serverModel->getServerById($serverId);

        if (!$server || $server['user_id'] != $userId) {
            header("Location: manage-server?status=error&message=" . urlencode("Lỗi quyền truy cập!"));
            exit;
        }

        if ($server['package_price'] <= 0) {
            header("Location: manage-server?status=error&message=" . urlencode("Gói miễn phí không cần gia hạn!"));
            exit;
        }

        $result = $this->serverModel->renew($serverId, $userId);
        $status = ($result === true) ? 'success' : 'error';
        $msg    = ($result === true) ? 'Gia hạn thành công!' : $result;

        header("Location: manage-server?status=$status&message=" . urlencode($msg));
        exit;
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login");
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
        header("Location: create-server?status=" . ($result ? "success" : "error"));
        exit;
    }

    private function handleBannerUpload() {
        $uploadType = $_POST['uploadType'] ?? 'file';
        if ($uploadType !== 'file') {
            $url = trim($_POST['banner_url'] ?? '');
            return (filter_var($url, FILTER_VALIDATE_URL)) ? $url : '';
        }

        if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === 0) {
            $targetDir = "uploads/";
            if (!file_exists("../public/" . $targetDir)) mkdir("../public/" . $targetDir, 0777, true);

            $fileName = $_FILES['banner_file']['name'];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (in_array($fileType, ['jpg','jpeg','png','gif','webp']) && $_FILES['banner_file']['size'] <= 5242880) {
                $newFileName = time() . '_' . rand(1000,9999) . '.' . $fileType;
                if (move_uploaded_file($_FILES['banner_file']['tmp_name'], "../public/" . $targetDir . $newFileName)) {
                    return $targetDir . $newFileName;
                }
            }
        }
        return '';
    }
}

// =================================================================
// 3. VIEW MODELS (Cấu trúc dữ liệu để View sử dụng)
// =================================================================

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
    public $stats;    // Chứa instance của ServerStatsViewModel
    public $schedule; // Chứa instance của ServerScheduleViewModel
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