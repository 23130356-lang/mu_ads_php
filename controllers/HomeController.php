<?php
require_once '../config/Database.php';
require_once '../models/Server.php';
require_once '../models/HomeBanner.php';
require_once '../models/MasterData.php';

class HomeController {
    private $db;
    private $bannerModel;
    private $masterData;
    private $serverModel;

    public function __construct($dbConnection) {
        // Nhận kết nối DB từ bên ngoài (index.php truyền vào)
        $this->db = $dbConnection;
        $this->bannerModel = new HomeBanner($this->db);
        $this->masterData = new MasterData($this->db);
        $this->serverModel = new Server($this->db);
    }

    public function index() {
        // --- 1. Thiết lập thời gian ---
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $todayStr     = date('d/m/Y');
        $today        = date('Y-m-d');
        $tomorrow     = date('Y-m-d', strtotime('+1 day'));
        $yesterday    = date('Y-m-d', strtotime('-1 day'));

        // --- 2. Lấy tham số Filter từ URL ---
        $selectedVersion = $_GET['versionId'] ?? '';
        $selectedReset   = $_GET['reset'] ?? '';
        $filterType      = $_GET['filterType'] ?? 'open'; // 'open' hoặc 'test'
        $filterDay       = $_GET['filterDay'] ?? '';      // 'today', 'tomorrow', '3days'

        // Cờ kiểm tra đang tìm kiếm
        $isSearching = (!empty($selectedVersion) || !empty($selectedReset));
        $filterDisplay = "KẾT QUẢ TÌM KIẾM";

        // --- 3. Lấy dữ liệu Dropdown (Master Data) ---
        $menuVersions = $this->masterData->getList('versions');
        $menuTypes    = $this->masterData->getList('resets');

        // --- 4. QUERY LẤY SERVER (Logic gộp từ index.php và model) ---
        // Sử dụng ServerModel để code gọn hơn, giả định bạn đã có hàm getHomeServers
        // Nếu chưa có, đây là logic Raw SQL tương đương:
        
        $query = "SELECT s.*, v.version_name, rt.reset_name, 
                         sch.alpha_date, sch.alpha_time, sch.beta_date, sch.beta_time
                  FROM servers s
                  LEFT JOIN mu_versions v ON s.version_id = v.version_id
                  LEFT JOIN reset_types rt ON s.reset_id = rt.reset_id
                  LEFT JOIN server_schedules sch ON s.server_id = sch.server_id
                  WHERE s.status = 'APPROVED' AND s.is_active = 1";

        $params = [];

        // Filter Version
        if ($selectedVersion) {
            $query .= " AND s.version_id = :ver";
            $params[':ver'] = $selectedVersion;
        }
        // Filter Reset
        if ($selectedReset) {
            $query .= " AND s.reset_id = :res";
            $params[':res'] = $selectedReset;
        }
        // Filter Ngày (nếu có logic lọc ngày cụ thể)
        if ($filterDay === 'today') {
            $col = ($filterType == 'test') ? 'sch.alpha_date' : 'sch.beta_date';
            $query .= " AND $col = :today";
            $params[':today'] = $today;
        }

        // Sort: SuperVIP -> VIP -> Basic -> Date -> Created
        $sortDate = ($filterType == 'test') ? 'sch.alpha_date' : 'sch.beta_date';
        $query .= " ORDER BY FIELD(s.banner_package, 'SUPER_VIP', 'VIP', 'BASIC'), $sortDate DESC, s.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $allServers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- 5. Phân loại Server (SVIP, VIP, Normal) & Format dữ liệu ---
        $superVips = [];
        $vips      = [];
        $normals   = [];

        foreach ($allServers as $sv) {
            // Xác định ngày hiển thị
            $rawDate = ($filterType == 'test') ? $sv['alpha_date'] : $sv['beta_date'];
            
            // Format ngày hiển thị (d/m/Y cho View)
            $sv['date_display'] = $rawDate ? date('d/m/Y', strtotime($rawDate)) : 'Đang cập nhật';
            
            // Tạo biến date_open/alpha cho View dùng
            $sv['date_open']  = (!empty($sv['beta_date'])) ? date('d/m/Y', strtotime($sv['beta_date'])) : '';
            $sv['date_alpha'] = (!empty($sv['alpha_date'])) ? date('d/m/Y', strtotime($sv['alpha_date'])) : '';

            // Xử lý ảnh (Fallback nếu ảnh lỗi)
            if (empty($sv['image_url']) && !empty($sv['banner_image'])) {
                $sv['image_url'] = $sv['banner_image'];
            }
            if (empty($sv['image_url'])) {
                $sv['image_url'] = "https://via.placeholder.com/600x60/222/999?text=MU+ONLINE";
            }

            // Phân loại vào mảng
            $pkg = strtoupper($sv['banner_package'] ?? 'BASIC');
            if ($pkg === 'SUPER_VIP') {
                $superVips[] = $sv;
            } elseif ($pkg === 'VIP') {
                $vips[] = $sv;
            } else {
                $normals[] = $sv;
            }
        }

        // --- 6. Lấy Banner ---
        $stmtBan = $this->bannerModel->getRunningBanners();
        $rawBanners = $stmtBan->fetchAll(PDO::FETCH_ASSOC);

        $bannersHero  = [];
        $bannersLeft  = [];
        $bannersRight = [];
        $bannersStd   = [];

        foreach ($rawBanners as $b) {
            $pos = strtoupper($b['position_code']);
            // Chấp nhận cả mã chữ thường và hoa để an toàn
            if ($pos === 'HERO') $bannersHero[] = $b;
            elseif ($pos === 'LEFT' || $pos === 'LEFT_SIDEBAR') $bannersLeft[] = $b;
            elseif ($pos === 'RIGHT' || $pos === 'RIGHT_SIDEBAR') $bannersRight[] = $b;
            elseif ($pos === 'STD' || $pos === 'STANDARD') $bannersStd[] = $b;
        }

        // --- 7. Meta Data cho SEO ---
        $pageTitle = "Mu Mới Ra | Munoria Portal - Cổng Game Mu Online";
        $metaDescription = "Danh sách Mu Online mới ra, Mu Alpha Test hôm nay.";
        $canonicalUrl = "https://munoria.mobile/";

        // --- 8. Load View ---
        // Vì file này được gọi từ public/index.php, nên đường dẫn include là cùng cấp
        if (file_exists('home.php')) {
            require_once 'home.php';
        } else {
            echo "Lỗi: Không tìm thấy file view home.php";
        }
    }
}
?>