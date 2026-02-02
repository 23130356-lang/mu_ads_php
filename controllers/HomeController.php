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
        $this->db = $dbConnection;
        $this->bannerModel = new HomeBanner($this->db);
        $this->masterData = new MasterData($this->db);
        $this->serverModel = new Server($this->db);
    }

    public function index() {
        // 1. Thiết lập thời gian
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $todayStr     = date('d/m/Y');
        $today        = date('Y-m-d');
        $tomorrow     = date('Y-m-d', strtotime('+1 day'));
        $yesterday    = date('Y-m-d', strtotime('-1 day'));

        // Tự động ẩn server hết hạn (nếu cần)
        $this->serverModel->autoRejectExpired();

        // 2. Lấy tham số từ URL
        $selectedVersion = $_GET['versionId'] ?? '';
        $selectedReset   = $_GET['reset'] ?? '';
        $filterType      = $_GET['filterType'] ?? 'open'; // 'open' hoặc 'test'
        $filterDay       = $_GET['filterDay'] ?? '';      // 'today', 'tomorrow', '3days'

        // Cờ xác định có đang tìm kiếm/lọc không
        $isSearching = (!empty($selectedVersion) || !empty($selectedReset) || !empty($filterDay));
        $filterDisplay = "DANH SÁCH SERVER"; // Tiêu đề mặc định
        $currentFilterDay = null; // Biến quan trọng để View hiện Badge ngày

        // 3. Lấy dữ liệu Menu lọc
        $menuVersions = $this->masterData->getList('versions');
        $menuTypes    = $this->masterData->getList('resets');

        // 4. Xây dựng Query SQL
        // Chọn cột ngày dựa theo loại (Open hay Test)
        $dateCol = ($filterType == 'test') ? 'sch.alpha_date' : 'sch.beta_date';

        $query = "SELECT s.*, v.version_name, rt.reset_name, 
                          sch.alpha_date, sch.alpha_time, sch.beta_date, sch.beta_time
                  FROM servers s
                  LEFT JOIN mu_versions v ON s.version_id = v.version_id
                  LEFT JOIN reset_types rt ON s.reset_id = rt.reset_id
                  LEFT JOIN server_schedules sch ON s.server_id = sch.server_id
                  WHERE s.status = 'APPROVED' AND s.is_active = 1";

        $params = [];

        // --- Lọc theo Phiên bản ---
        if ($selectedVersion) {
            $query .= " AND s.version_id = :ver";
            $params[':ver'] = $selectedVersion;
        }

        // --- Lọc theo Reset ---
        if ($selectedReset) {
            $query .= " AND s.reset_id = :res";
            $params[':res'] = $selectedReset;
        }

        // --- [QUAN TRỌNG] Lọc theo Ngày (Logic mới bổ sung) ---
        if ($filterDay === 'today') {
            $query .= " AND $dateCol = :today";
            $params[':today'] = $today;
            $filterDisplay = ($filterType == 'test') ? "ALPHA TEST HÔM NAY" : "OPEN BETA HÔM NAY";
            $currentFilterDay = 'today';
        } 
        elseif ($filterDay === 'tomorrow') {
            $query .= " AND $dateCol = :tomorrow";
            $params[':tomorrow'] = $tomorrow;
            $filterDisplay = ($filterType == 'test') ? "ALPHA TEST NGÀY MAI" : "OPEN BETA NGÀY MAI";
            $currentFilterDay = 'tomorrow';
        }
        elseif ($filterDay === '3days') {
            // Lấy khoảng: Hôm qua <= Ngày <= Ngày mai
            $query .= " AND $dateCol BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $yesterday;
            $params[':endDate']   = $tomorrow;
            
            $filterDisplay = ($filterType == 'test') ? "MU ALPHA TEST GẦN ĐÂY" : "MU OPEN BETA GẦN ĐÂY";
            $currentFilterDay = '3days'; // Cờ này kích hoạt logic Badge trong View
        }
        else {
            // Mặc định nếu không chọn ngày: Có thể lấy tất cả hoặc chỉ lấy hôm nay tùy logic của bạn.
            // Ở đây tôi để mặc định hiển thị tất cả server sắp tới (>= hôm nay) để danh sách không bị trống
             /* $query .= " AND $dateCol >= :today";
             $params[':today'] = $today;
             */
        }

        // --- Sắp xếp ---
        // Ưu tiên gói VIP -> Sau đó đến Ngày (Gần nhất lên đầu) -> Ngày tạo
        $query .= " ORDER BY FIELD(s.banner_package, 'SUPER_VIP', 'VIP', 'BASIC'), $dateCol ASC, s.created_at DESC";

        // 5. Thực thi Query
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $allServers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 6. Phân loại Server (SuperVIP, VIP, Normal)
        $superVips = [];
        $vips      = [];
        $normals   = [];

        foreach ($allServers as $sv) {
            // Format ngày hiển thị
            $rawDate = ($filterType == 'test') ? $sv['alpha_date'] : $sv['beta_date'];
            $sv['date_display'] = $rawDate ? date('d/m/Y', strtotime($rawDate)) : 'Đang cập nhật';
            
            // Format riêng để View dùng so sánh Badge
            $sv['date_open']  = (!empty($sv['beta_date'])) ? date('d/m/Y', strtotime($sv['beta_date'])) : '';
            $sv['date_alpha'] = (!empty($sv['alpha_date'])) ? date('d/m/Y', strtotime($sv['alpha_date'])) : '';

            // Xử lý ảnh mặc định
            if (empty($sv['image_url']) && !empty($sv['banner_image'])) {
                $sv['image_url'] = $sv['banner_image'];
            }
            if (empty($sv['image_url'])) {
                $sv['image_url'] = "https://via.placeholder.com/600x60/222/999?text=MU+ONLINE";
            }

            // Phân loại
            $pkg = strtoupper($sv['banner_package'] ?? 'BASIC');
            if ($pkg === 'SUPER_VIP') {
                $superVips[] = $sv;
            } elseif ($pkg === 'VIP') {
                $vips[] = $sv;
            } else {
                $normals[] = $sv;
            }
        }

        // 7. Lấy Banner Quảng cáo
        $stmtBan = $this->bannerModel->getRunningBanners();
        $rawBanners = $stmtBan->fetchAll(PDO::FETCH_ASSOC);

        $bannersHero  = [];
        $bannersLeft  = [];
        $bannersRight = [];
        $bannersStd   = [];

        foreach ($rawBanners as $b) {
            $pos = strtoupper($b['position_code']);
            if ($pos === 'HERO') $bannersHero[] = $b;
            elseif (strpos($pos, 'LEFT') !== false) $bannersLeft[] = $b;
            elseif (strpos($pos, 'RIGHT') !== false) $bannersRight[] = $b;
            else $bannersStd[] = $b;
        }

        // 8. Dữ liệu Meta SEO
        $pageTitle = "Mu Mới Ra | Munoria Portal - Cổng Game Mu Online";
        $metaDescription = "Danh sách Mu Online mới ra, Mu Alpha Test hôm nay.";
        $canonicalUrl = "https://munoria.mobile/";

        // 9. Load View
        if (file_exists('home.php')) {
            // Extract biến ra để View dùng trực tiếp ($superVips, $vips...)
            // Lưu ý: View home.php của bạn cần biến $currentFilterDay
            require_once 'home.php';
        } else {
            echo "Lỗi: Không tìm thấy file view home.php";
        }
    }
}
?>