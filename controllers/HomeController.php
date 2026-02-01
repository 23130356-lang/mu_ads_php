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
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $todayStr     = date('d/m/Y');
        $today        = date('Y-m-d');
        $tomorrow     = date('Y-m-d', strtotime('+1 day'));
        $yesterday    = date('Y-m-d', strtotime('-1 day'));

        $this->serverModel->autoRejectExpired();


        $selectedVersion = $_GET['versionId'] ?? '';
        $selectedReset   = $_GET['reset'] ?? '';
        $filterType      = $_GET['filterType'] ?? 'open'; // 'open' hoặc 'test'
        $filterDay       = $_GET['filterDay'] ?? '';      // 'today', 'tomorrow', '3days'

        $isSearching = (!empty($selectedVersion) || !empty($selectedReset));
        $filterDisplay = "KẾT QUẢ TÌM KIẾM";

        $menuVersions = $this->masterData->getList('versions');
        $menuTypes    = $this->masterData->getList('resets');

        
        $query = "SELECT s.*, v.version_name, rt.reset_name, 
                         sch.alpha_date, sch.alpha_time, sch.beta_date, sch.beta_time
                  FROM servers s
                  LEFT JOIN mu_versions v ON s.version_id = v.version_id
                  LEFT JOIN reset_types rt ON s.reset_id = rt.reset_id
                  LEFT JOIN server_schedules sch ON s.server_id = sch.server_id
                  WHERE s.status = 'APPROVED' AND s.is_active = 1";

        $params = [];

        if ($selectedVersion) {
            $query .= " AND s.version_id = :ver";
            $params[':ver'] = $selectedVersion;
        }
        if ($selectedReset) {
            $query .= " AND s.reset_id = :res";
            $params[':res'] = $selectedReset;
        }
        if ($filterDay === 'today') {
            $col = ($filterType == 'test') ? 'sch.alpha_date' : 'sch.beta_date';
            $query .= " AND $col = :today";
            $params[':today'] = $today;
        }

        $sortDate = ($filterType == 'test') ? 'sch.alpha_date' : 'sch.beta_date';
        $query .= " ORDER BY FIELD(s.banner_package, 'SUPER_VIP', 'VIP', 'BASIC'), $sortDate DESC, s.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $allServers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $superVips = [];
        $vips      = [];
        $normals   = [];

        foreach ($allServers as $sv) {
            $rawDate = ($filterType == 'test') ? $sv['alpha_date'] : $sv['beta_date'];
            
            $sv['date_display'] = $rawDate ? date('d/m/Y', strtotime($rawDate)) : 'Đang cập nhật';
            
            $sv['date_open']  = (!empty($sv['beta_date'])) ? date('d/m/Y', strtotime($sv['beta_date'])) : '';
            $sv['date_alpha'] = (!empty($sv['alpha_date'])) ? date('d/m/Y', strtotime($sv['alpha_date'])) : '';

            if (empty($sv['image_url']) && !empty($sv['banner_image'])) {
                $sv['image_url'] = $sv['banner_image'];
            }
            if (empty($sv['image_url'])) {
                $sv['image_url'] = "https://via.placeholder.com/600x60/222/999?text=MU+ONLINE";
            }

            $pkg = strtoupper($sv['banner_package'] ?? 'BASIC');
            if ($pkg === 'SUPER_VIP') {
                $superVips[] = $sv;
            } elseif ($pkg === 'VIP') {
                $vips[] = $sv;
            } else {
                $normals[] = $sv;
            }
        }

        $stmtBan = $this->bannerModel->getRunningBanners();
        $rawBanners = $stmtBan->fetchAll(PDO::FETCH_ASSOC);

        $bannersHero  = [];
        $bannersLeft  = [];
        $bannersRight = [];
        $bannersStd   = [];

        foreach ($rawBanners as $b) {
            $pos = strtoupper($b['position_code']);
            if ($pos === 'HERO') $bannersHero[] = $b;
            elseif ($pos === 'LEFT' || $pos === 'LEFT_SIDEBAR') $bannersLeft[] = $b;
            elseif ($pos === 'RIGHT' || $pos === 'RIGHT_SIDEBAR') $bannersRight[] = $b;
            elseif ($pos === 'STD' || $pos === 'STANDARD') $bannersStd[] = $b;
        }

        $pageTitle = "Mu Mới Ra | Munoria Portal - Cổng Game Mu Online";
        $metaDescription = "Danh sách Mu Online mới ra, Mu Alpha Test hôm nay.";
        $canonicalUrl = "https://munoria.mobile/";

        if (file_exists('home.php')) {
            require_once 'home.php';
        } else {
            echo "Lỗi: Không tìm thấy file view home.php";
        }
    }
}
?>