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

    /**
     * [MỚI] Hiển thị trang chi tiết Server
     * Dữ liệu sẽ được map vào các Class ViewModel (ở cuối file)
     */
    public function detail() {
        $id = $_GET['id'] ?? 0;
        
        if ($id <= 0) {
            die("ID Server không hợp lệ.");
        }

        // Gọi hàm mới lấy Full data từ Model
        $rawData = $this->serverModel->getDetailFull($id);

        if (!$rawData) {
            die("Server không tồn tại hoặc chưa được duyệt.");
        }

        // --- MAP DỮ LIỆU SANG OBJECT CHO VIEW DỄ DÙNG ---
        
        $server = new ServerViewModel();
        $server->id             = $rawData['server_id'];
        $server->serverName     = $rawData['server_name'];
        $server->muName         = $rawData['mu_name'];
        $server->slogan         = $rawData['slogan'];
        $server->bannerPackage  = $rawData['banner_package'];
        // Xử lý đường dẫn ảnh (thêm prefix public nếu cần)
        $server->bannerImage    = !empty($rawData['banner_image']) ? $rawData['banner_image'] : 'assets/images/no-image.jpg';
        $server->websiteUrl     = $rawData['website_url'];
        $server->fanpageUrl     = $rawData['fanpage_url'];
        $server->description    = $rawData['description'];
        $server->serverTypeName = $rawData['server_type_name'] ?? 'Normal';

        // Map Stats
        $server->stats = new ServerStatsViewModel();
        $server->stats->muVersionName = $rawData['version_name'] ?? 'Unknown';
        $server->stats->expRate       = $rawData['exp_rate'];
        $server->stats->dropRate      = $rawData['drop_rate'];
        $server->stats->antiHack      = $rawData['anti_hack'];
        $server->stats->resetTypeName = $rawData['reset_name'] ?? 'Keep Reset';
        $server->stats->pointTypeName = $rawData['point_name'] ?? '5/7';

        // Map Schedule
        $server->schedule = new ServerScheduleViewModel();
        $server->schedule->alphaDate = $rawData['alpha_date'];
        $server->schedule->alphaTime = $rawData['alpha_time'];
        $server->schedule->betaDate  = $rawData['beta_date'];
        $server->schedule->betaTime  = $rawData['beta_time'];

        // Truyền biến $server sang View
        require_once '../public/server_detail.php';
    }

    /**
     * Xử lý upload banner
     */
    private function handleBannerUpload() {
        $uploadType = $_POST['uploadType'] ?? 'file';
        $bannerString = '';

        if ($uploadType === 'file') {
            if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === 0) {
                $targetDir = "../public/uploads/";
                if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

                $fileName = $_FILES['banner_file']['name'];
                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowTypes = ['jpg','jpeg','png','gif','webp'];

                if (in_array($fileType, $allowTypes) && $_FILES['banner_file']['size'] <= 5 * 1024 * 1024) {
                    $newFileName = time() . '_' . rand(1000,9999) . '.' . $fileType;
                    if (move_uploaded_file($_FILES['banner_file']['tmp_name'], $targetDir . $newFileName)) {
                        $bannerString = "uploads/" . $newFileName;
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

    /**
     * Lưu server mới
     */
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
}

// =======================================================
// CÁC CLASS DỮ LIỆU (DTO) CHO VIEW - ĐẶT Ở CUỐI FILE
// =======================================================

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
    public $stats;    // Object ServerStatsViewModel
    public $schedule; // Object ServerScheduleViewModel
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