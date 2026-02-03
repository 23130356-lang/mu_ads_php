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

        $this->serverModel->autoRejectExpired();

        // =================================================================================
        // 2. [QUAN TRỌNG] Lấy tham số từ URL (Đã sửa để khớp với Header)
        // =================================================================================
        
        // Ưu tiên lấy 'filter_version' từ Header, nếu không có thì lấy 'versionId' cũ
        $selectedVersion = $_GET['filter_version'] ?? ($_GET['versionId'] ?? '');
        
        // Ưu tiên lấy 'filter_reset' từ Header, nếu không có thì lấy 'reset' cũ
        $selectedReset   = $_GET['filter_reset'] ?? ($_GET['reset'] ?? '');
        
        $filterType      = $_GET['filterType'] ?? 'open'; 
        $filterDay       = $_GET['filterDay'] ?? '';      

        // Cờ kiểm tra xem có đang lọc hay không
        $isSearching = (!empty($selectedVersion) || !empty($selectedReset) || !empty($filterDay));
        
        $filterDisplay = "DANH SÁCH SERVER";
        $currentFilterDay = null;

        // 3. Lấy dữ liệu Menu lọc (để hiển thị lại trên view nếu cần)
        $menuVersions = $this->masterData->getList('versions');
        $menuTypes    = $this->masterData->getList('resets');

        // 4. Xây dựng Query SQL
        $dateCol = ($filterType == 'test') ? 'sch.alpha_date' : 'sch.beta_date';

        $query = "SELECT s.*, v.version_name, rt.reset_name, 
                          sch.alpha_date, sch.alpha_time, sch.beta_date, sch.beta_time
                  FROM servers s
                  LEFT JOIN mu_versions v ON s.version_id = v.version_id
                  LEFT JOIN reset_types rt ON s.reset_id = rt.reset_id
                  LEFT JOIN server_schedules sch ON s.server_id = sch.server_id
                  WHERE s.status = 'APPROVED' AND s.is_active = 1";

        $params = [];

        // --- Lọc theo Phiên bản (Season) ---
        if ($selectedVersion) {
            $verId = (int)$selectedVersion; 
            
            // Logic gom nhóm (giữ nguyên logic cũ của bạn)
            if (in_array($verId, [1, 2, 3])) {
                // Nhóm Season thấp (SS1, SS2, SS3)
                $query .= " AND s.version_id IN (1, 2, 3)";
            } elseif (in_array($verId, [5, 6, 7])) {
                // Nhóm Season trung (SS5, SS6, SS7)
                $query .= " AND s.version_id IN (5, 6, 7)";
            } else {
                // Các Season khác (SS4, SS8, SS15...)
                $query .= " AND s.version_id = :ver";
                $params[':ver'] = $verId;
            }
        }

        // --- Lọc theo Loại Reset ---
        if ($selectedReset) {
            $query .= " AND s.reset_id = :res";
            $params[':res'] = (int)$selectedReset;
        }

        // --- Lọc theo Ngày (Hôm nay / Ngày mai) ---
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
            $query .= " AND $dateCol BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $yesterday;
            $params[':endDate']   = $tomorrow;
            $filterDisplay = ($filterType == 'test') ? "MU ALPHA TEST GẦN ĐÂY" : "MU OPEN BETA GẦN ĐÂY";
            $currentFilterDay = '3days';
        }

        // --- Sắp xếp: Ưu tiên gói VIP -> Ngày -> Bài mới nhất ---
        $query .= " ORDER BY FIELD(s.banner_package, 'SUPER_VIP', 'VIP', 'BASIC'), $dateCol ASC, s.created_at DESC";

        // 5. Thực thi Query
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $allServers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Xử lý lỗi nếu query sai (ví dụ debug)
            $allServers = [];
        }

        // 6. Phân loại Server vào các danh sách (SuperVIP, VIP, Normal)
        $superVips = [];
        $vips      = [];
        $normals   = [];

        foreach ($allServers as $sv) {
            // Xử lý hiển thị ngày
            $rawDate = ($filterType == 'test') ? ($sv['alpha_date'] ?? null) : ($sv['beta_date'] ?? null);
            $sv['date_display'] = $rawDate ? date('d/m/Y', strtotime($rawDate)) : 'Đang cập nhật';
            
            $sv['date_open']  = (!empty($sv['beta_date'])) ? date('d/m/Y', strtotime($sv['beta_date'])) : '';
            $sv['date_alpha'] = (!empty($sv['alpha_date'])) ? date('d/m/Y', strtotime($sv['alpha_date'])) : '';

            // Xử lý ảnh (fallback nếu lỗi ảnh)
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

        // 7. Lấy Banner Quảng cáo (Giữ nguyên)
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

        // 8. Meta Data
        $pageTitle = "Mu Mới Ra | Munoria Portal - Cổng Game Mu Online";
        $metaDescription = "Danh sách Mu Online mới ra, Mu Alpha Test hôm nay.";
        $canonicalUrl = "https://munoria.mobile/";

        // 9. Load View
        if (file_exists('home.php')) {
            require_once 'home.php';
        } else {
            // Fallback nếu cấu trúc thư mục khác (thường là ../views/home/index.php)
            if (file_exists('../views/home/index.php')) {
                require_once '../views/home/index.php';
            } else {
                echo "Lỗi: Không tìm thấy file view home.php hoặc ../views/home/index.php";
            }
        }
    }
}
?>