<?php
require_once '../config/Database.php';
require_once '../models/Server.php';
require_once '../models/HomeBanner.php';
require_once '../models/MasterData.php';

class HomeController {
    private $db;
    private $bannerModel;
    private $masterData;

    public function __construct() {
        // Kết nối Database
        $database = new Database();
        $this->db = $database->connect();
        
        $this->bannerModel = new HomeBanner($this->db);
        $this->masterData = new MasterData($this->db);
    }

    public function index() {
        // --- 1. Thiết lập thời gian ---
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // --- 2. Xử lý Filter (Bộ lọc từ URL) ---
        $selectedVersion = $_GET['versionId'] ?? '';
        $selectedReset = $_GET['reset'] ?? '';
        $filterType = $_GET['filterType'] ?? 'open'; // Mặc định là xem lịch OPEN
        $filterDay = $_GET['filterDay'] ?? '';       // 'today', 'tomorrow'

        // --- 3. Lấy Banner (Hero, Left, Right) ---
        $stmtBanners = $this->bannerModel->getRunningBanners();
        $allBanners = $stmtBanners->fetchAll(PDO::FETCH_ASSOC);

        $bannersHero = [];
        $bannersLeft = [];
        $bannersRight = [];

        foreach ($allBanners as $b) {
            // Map theo position_code trong DB của bạn
            switch (strtoupper($b['position_code'])) {
                case 'HERO': $bannersHero[] = $b; break;
                case 'LEFT': $bannersLeft[] = $b; break; // Banner dọc trái
                case 'RIGHT': $bannersRight[] = $b; break; // Banner dọc phải
            }
        }

        // --- 4. Lấy dữ liệu cho Dropdown tìm kiếm ---
        $menuVersions = $this->masterData->getList('versions'); // mu_versions
        $menuTypes = $this->masterData->getList('resets');     // reset_types

        // --- 5. QUERY LẤY DANH SÁCH SERVER (Quan trọng nhất) ---
        // Join các bảng: servers, versions, server_stats, reset_types, server_schedules
        $query = "SELECT s.*, 
                         v.version_name, 
                         st.exp_rate, 
                         rt.reset_name, 
                         sch.alpha_date, sch.alpha_time,
                         sch.beta_date, sch.beta_time
                  FROM servers s
                  LEFT JOIN mu_versions v ON s.version_id = v.version_id
                  LEFT JOIN server_stats st ON s.server_id = st.server_id
                  LEFT JOIN reset_types rt ON s.reset_id = rt.reset_id
                  LEFT JOIN server_schedules sch ON s.server_id = sch.server_id
                  WHERE s.status = 'APPROVED' AND s.is_active = 1";

        // Áp dụng bộ lọc SQL
        $params = [];

        // Lọc theo Version
        if ($selectedVersion) {
            $query .= " AND s.version_id = :ver";
            $params[':ver'] = $selectedVersion;
        }

        // Lọc theo Reset/Non-reset
        if ($selectedReset) {
            $query .= " AND s.reset_id = :res";
            $params[':res'] = $selectedReset;
        }

        // Lọc theo ngày (Hôm nay/Ngày mai) dựa trên loại lịch (Open hay Alpha)
        if ($filterDay) {
            $dateCol = ($filterType === 'test') ? 'sch.alpha_date' : 'sch.beta_date';
            
            if ($filterDay === 'today') {
                $query .= " AND $dateCol = :fdate";
                $params[':fdate'] = $today;
            } elseif ($filterDay === 'tomorrow') {
                $query .= " AND $dateCol = :fdate";
                $params[':fdate'] = $tomorrow;
            }
        }

        // Sắp xếp: Ưu tiên gói Banner (SUPER_VIP > VIP > BASIC) -> Sau đó đến ngày giờ
        // Sử dụng FIELD để sort theo ENUM
        $sortDateCol = ($filterType === 'test') ? 'sch.alpha_date' : 'sch.beta_date';
        $query .= " ORDER BY FIELD(s.banner_package, 'SUPER_VIP', 'VIP', 'BASIC'), $sortDateCol DESC, s.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $allServers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- 6. Phân loại Server (SVIP, VIP, NORMAL) và Xử lý hiển thị ---
        $superVips = [];
        $vips = [];
        $normals = [];

        foreach ($allServers as &$sv) {
            // Xử lý ngày hiển thị dựa trên đang xem Tab Open hay Tab Test
            if ($filterType === 'test') {
                $rawDate = $sv['alpha_date'];
                $rawTime = $sv['alpha_time'];
            } else {
                $rawDate = $sv['beta_date'];
                $rawTime = $sv['beta_time'];
            }

            // Format ngày hiển thị (d/m)
            $sv['display_date'] = $rawDate ? date('d/m', strtotime($rawDate)) : 'N/A';
            
            // Format giờ hiển thị (H:i)
            $sv['display_time'] = $rawTime ? date('H:i', strtotime($rawTime)) : '';

            // Tạo Badge "Hôm nay", "Ngày mai"
            $sv['date_badge'] = '';
            $sv['date_class'] = ''; // class css

            if ($rawDate === $today) {
                $sv['date_badge'] = 'HÔM NAY';
                $sv['date_class'] = 'day-today';
            } elseif ($rawDate === $tomorrow) {
                $sv['date_badge'] = 'NGÀY MAI';
                $sv['date_class'] = 'day-tomorrow';
            } elseif ($rawDate === $yesterday) {
                $sv['date_badge'] = 'HÔM QUA';
                $sv['date_class'] = 'day-yesterday';
            }

            // Phân loại vào mảng con dựa trên ENUM banner_package
            if ($sv['banner_package'] === 'SUPER_VIP') {
                $superVips[] = $sv;
            } elseif ($sv['banner_package'] === 'VIP') {
                $vips[] = $sv;
            } else {
                $normals[] = $sv; // BASIC hoặc NULL
            }
        }

        // --- 7. Chuẩn bị Meta Data ---
        $pageTitle = "Mu Mới Ra | Munoria Portal - Cổng Game Mu Online";
        $canonicalUrl = "https://munoria.mobile/";
        
        // --- 8. Load View ---
        // Lưu ý: Đường dẫn này giả định cấu trúc thư mục là:
        // root/
        //   controllers/HomeController.php
        //   public/home.php
        require_once '../public/home.php'; 
    }
}
?>