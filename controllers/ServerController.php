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
    // 1. HIỂN THỊ CHI TIẾT (DETAIL) - HỖ TRỢ ID HOẶC SLUG
    // =================================================================
    
    public function detail($identifier = null) {
        // 1. Lấy ID hoặc Slug từ Router hoặc $_GET
        $id = $identifier ?? ($_GET['id'] ?? 0);

        if (empty($id)) {
            header("Location: /");
            exit;
        }

        // 2. Gọi Model (Model mới đã tự xử lý ID hoặc Slug)
        $rawData = $this->serverModel->getDetailFull($id);
        
        // 3. Xử lý khi không tìm thấy Server
        if (!$rawData) {
            $this->renderError("Máy chủ không tồn tại hoặc đã bị xóa.");
            exit;
        }

        // 4. Xử lý SEO (Giữ nguyên logic của bạn)
        if (class_exists('SeoHelper')) {
            $GLOBALS['seo'] = SeoHelper::generateMeta($rawData);
        }

        // 5. MAP DỮ LIỆU VÀO VIEW MODEL
        // Việc này giúp tách biệt logic Database và View
        $server = new ServerViewModel();
        
        // Thông tin cơ bản
        $server->id             = $rawData['server_id'];
        $server->serverName     = htmlspecialchars($rawData['server_name']);
        $server->slug           = $rawData['slug'] ?? ''; // Thêm Slug
        $server->muName         = htmlspecialchars($rawData['mu_name']);
        $server->slogan         = htmlspecialchars($rawData['slogan']);
        $server->bannerPackage  = $rawData['banner_package'];
        
        // Xử lý ảnh (URL ngoài hoặc Upload nội bộ)
        $server->bannerImage    = $this->formatImageUrl($rawData['banner_image']);

        $server->websiteUrl     = $rawData['website_url'];
        $server->fanpageUrl     = $rawData['fanpage_url'];
        $server->description    = $rawData['description']; // HTML (CKEditor)
        $server->serverTypeName = $rawData['server_type_name'] ?? 'Normal';

        // Object con: Thống kê (Stats)
        $server->stats = new ServerStatsViewModel();
        $server->stats->muVersionName = $rawData['version_name'] ?? 'Unknown';
        $server->stats->expRate       = number_format($rawData['exp_rate']) . 'x';
        $server->stats->dropRate      = $rawData['drop_rate'] . '%';
        $server->stats->antiHack      = htmlspecialchars($rawData['anti_hack'] ?? 'N/A');
        $server->stats->resetTypeName = $rawData['reset_type_name'] ?? 'Reset';
        $server->stats->pointTypeName = $rawData['point_type_name'] ?? 'N/A'; // Fix map key từ model

        // Object con: Lịch trình (Schedule)
        $server->schedule = new ServerScheduleViewModel();
        $server->schedule->alphaDate = $rawData['alpha_date']; 
        $server->schedule->alphaTime = $rawData['alpha_time'];
        $server->schedule->betaDate  = $rawData['beta_date'];
        $server->schedule->betaTime  = $rawData['beta_time'];

        // 6. Gọi View
        require_once '../public/server_detail.php';
    }

    // =================================================================
    // 2. QUẢN LÝ SERVER CÁ NHÂN (MANAGE)
    // =================================================================

    public function manage() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login?return=" . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }

        $rawServers = $this->serverModel->getServersByUserId($_SESSION['user_id']);
        $servers = [];

        foreach ($rawServers as $sv) {
            // Lấy thông tin gói cước từ cấu hình Model
            $packageKey = $sv['banner_package'];
            $packConfig = $this->serverModel->packages[$packageKey] ?? $this->serverModel->packages['BASIC'];

            // Format lại dữ liệu cho view dễ dùng
            $sv['package_label'] = $packConfig['label'];
            $sv['package_color'] = $packConfig['color'] ?? 'secondary'; // Thêm màu sắc
            $sv['banner_image']  = $this->formatImageUrl($sv['banner_image']);
            
            // Tính toán trạng thái hiển thị
            $sv['display_status'] = $this->calculateStatus($sv);

            $servers[] = $sv;
        }

        require_once '../public/manage_servers.php'; 
    }

    // =================================================================
    // 3. GIA HẠN SERVER (RENEW)
    // =================================================================

    public function renew() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login");
            exit;
        }

        $serverId = $_GET['id'] ?? 0;
        $userId   = $_SESSION['user_id'];

        // Gọi hàm renew trong Model (đã có transaction và check tiền)
        $result = $this->serverModel->renew($serverId, $userId);

        if ($result === true) {
            $status = 'success';
            $msg = 'Gia hạn thành công! Server của bạn đã được đẩy lên.';
        } else {
            $status = 'error';
            $msg = $result; // Lỗi trả về từ Exception trong Model
        }

        header("Location: manage-server?status=$status&message=" . urlencode($msg));
        exit;
    }

    // =================================================================
    // 4. TẠO MỚI SERVER (STORE)
    // =================================================================

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login");
            exit;
        }

        // 1. Xử lý Upload ảnh
        $finalBanner = $this->handleBannerUpload();

        // 2. Chuẩn bị data (Validate cơ bản)
        $data = [
            'user_id'        => $_SESSION['user_id'],
            'server_name'    => trim($_POST['server_name'] ?? ''),
            'mu_name'        => trim($_POST['mu_name'] ?? ''),
            'slogan'         => trim($_POST['slogan'] ?? ''),
            'website_url'    => trim($_POST['website_url'] ?? ''),
            'fanpage_url'    => trim($_POST['fanpage_url'] ?? ''),
            'description'    => trim($_POST['description'] ?? ''), // HTML
            
            'banner_image'   => $finalBanner,
            'banner_package' => $_POST['banner_package'] ?? 'BASIC',
            
            'version_id'     => $_POST['version_id'] ?? null,
            'type_id'        => $_POST['type_id'] ?? null,
            'reset_id'       => $_POST['reset_id'] ?? null,
            'point_id'       => $_POST['point_id'] ?? null, // Quan trọng: ID kiểu point
            
            'exp_rate'       => $_POST['exp_rate'] ?? 0,
            'drop_rate'      => $_POST['drop_rate'] ?? 0,
            'anti_hack'      => trim($_POST['anti_hack'] ?? ''),
            
            // Mapping ngày tháng
            'alpha_date'     => !empty($_POST['alpha_date']) ? $_POST['alpha_date'] : null,
            'alpha_time'     => $_POST['alpha_time'] ?? null,
            'open_date'      => !empty($_POST['beta_date']) ? $_POST['beta_date'] : null, // View name="beta_date"
            'open_time'      => $_POST['beta_time'] ?? null
        ];

        // 3. Validate bắt buộc
        if (empty($data['server_name']) || empty($data['mu_name'])) {
            header("Location: create-server?status=error&message=" . urlencode("Vui lòng nhập tên Server và MU Name"));
            exit;
        }

        // 4. Gọi Model
        $result = $this->serverModel->createFull($data);

        if ($result) {
            header("Location: manage-server?status=success&message=" . urlencode("Đăng ký thành công! Đang chờ duyệt."));
        } else {
            header("Location: create-server?status=error&message=" . urlencode("Lỗi hệ thống, vui lòng thử lại."));
        }
        exit;
    }

    // =================================================================
    // CÁC HÀM PRIVATE HỖ TRỢ (HELPERS)
    // =================================================================

    /**
     * Xử lý upload ảnh (File hoặc URL)
     */
    private function handleBannerUpload() {
        $uploadType = $_POST['uploadType'] ?? 'file';

        // Trường hợp 1: Nhập URL ảnh trực tiếp
        if ($uploadType !== 'file') {
            $url = trim($_POST['banner_url'] ?? '');
            return (filter_var($url, FILTER_VALIDATE_URL)) ? $url : '';
        }

        // Trường hợp 2: Upload File
        if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === 0) {
            $targetDir = "uploads/servers/";
            
            // Tạo thư mục nếu chưa có
            if (!file_exists("../public/" . $targetDir)) {
                mkdir("../public/" . $targetDir, 0777, true);
            }

            $fileName = $_FILES['banner_file']['name'];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            // Validate file
            if (in_array($fileType, $allowedTypes) && $_FILES['banner_file']['size'] <= 5242880) { // Max 5MB
                $newFileName = time() . '_' . uniqid() . '.' . $fileType; // Tên file ngẫu nhiên
                
                if (move_uploaded_file($_FILES['banner_file']['tmp_name'], "../public/" . $targetDir . $newFileName)) {
                    return $targetDir . $newFileName;
                }
            }
        }
        return ''; // Trả về rỗng nếu lỗi
    }

    /**
     * Format đường dẫn ảnh để hiển thị ở View
     */
    private function formatImageUrl($path) {
        if (empty($path)) {
            return 'assets/images/no-image.jpg';
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return $path; // Path nội bộ (uploads/...)
    }

    /**
     * Helper render trang lỗi đơn giản
     */
    private function renderError($message) {
        require_once '../public/includes/header.php';
        echo '<div class="container pt-5 text-center text-white">
                <h3 class="text-danger">LỖI!</h3>
                <p class="lead">'.$message.'</p>
                <a href="/" class="btn btn-secondary mt-3">Quay lại trang chủ</a>
              </div>';
        require_once '../public/includes/footer.php';
    }

    /**
     * Tính toán trạng thái hiển thị (Text Badge)
     */
    private function calculateStatus($server) {
        if ($server['status'] == 'PENDING') return '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
        if ($server['status'] == 'EXPIRED') return '<span class="badge bg-secondary">Hết hạn</span>';
        if ($server['status'] == 'APPROVED') {
             if (strtotime($server['expired_at']) < time()) {
                 return '<span class="badge bg-danger">Vừa hết hạn</span>';
             }
             return '<span class="badge bg-success">Đang hoạt động</span>';
        }
        return '<span class="badge bg-secondary">Unknown</span>';
    }
}

// =================================================================
// VIEW MODELS - GIỮ NGUYÊN ĐỂ KHÔNG LỖI VIEW CŨ
// =================================================================

class ServerViewModel {
    public $id;
    public $serverName;
    public $slug;
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