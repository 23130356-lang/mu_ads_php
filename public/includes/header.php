<?php
// =========================================================================
// 1. CẤU HÌNH CƠ BẢN & KẾT NỐI DB
// =========================================================================
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

// Lấy đường dẫn gốc sạch sẽ
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$scriptDir = ($scriptDir == '/') ? '' : $scriptDir;
$baseUrl   = $protocol . $_SERVER['HTTP_HOST'] . $scriptDir . '/';

// Kết nối Database nếu chưa có
if (!isset($db)) {
    $dbPath = __DIR__ . '/../../config/Database.php';
    if (file_exists($dbPath)) {
        require_once $dbPath;
        $database = new Database();
        $db = $database->connect(); 
    } else {
        die("Lỗi: Không tìm thấy file cấu hình Database tại $dbPath");
    }
}

// Xử lý SEO Meta
$seo = isset($GLOBALS['seo']) ? $GLOBALS['seo'] : null;
$title = $seo ? $seo->title : "MU Mới Ra 2026 - Danh Sách Server MU Online Mới Nhất Hôm Nay";
$desc  = $seo ? $seo->desc : "Tổng hợp danh sách MU mới ra, MU Online sắp Open Beta, Alpha Test hôm nay.";
$key   = $seo ? $seo->keywords : "mu moi ra, mu online, mu moi open, danh sach mu";
$img   = ($seo && !empty($seo->image)) ? $seo->image : $baseUrl . "assets/images/og-default.jpg";
$canonical_url = $seo ? $seo->url : ($protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

// Ghi đè SEO nếu là trang chi tiết Server (Logic cũ của bạn)
if (isset($_GET['url']) && strpos($_GET['url'], 'mu-') === 0 && isset($server)) {
    $title = !empty($server['meta_title']) ? $server['meta_title'] : $server['server_name'] . " - MU Mới Ra";
    $desc  = !empty($server['meta_desc']) ? $server['meta_desc'] : substr(strip_tags($server['description']), 0, 160);
    $key   = !empty($server['meta_keywords']) ? $server['meta_keywords'] : $server['server_name'] . ", mu moi ra";
    if(!empty($server['banner_image'])) $img = $baseUrl . "public/uploads/" . $server['banner_image'];
}

// Lấy dữ liệu Menu (MasterData)
require_once __DIR__ . '/../../models/MasterData.php';
if (!isset($menuVersions) || !isset($menuTypes)) {
    $masterData = new MasterData($db);
    $menuVersions = $masterData->getList('versions');
    $menuTypes    = $masterData->getList('resets');
}

// Hàm Slug (Giữ nguyên)
if (!function_exists('createSlug')) {
    function createSlug($str) {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
        $str = preg_replace('/([\s]+)/', '-', $str);
        return $str;
    }
}
?>

<title><?php echo htmlspecialchars($title); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($desc); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars($key); ?>">
<link rel="canonical" href="<?php echo $canonical_url; ?>" />

<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo htmlspecialchars($title); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($desc); ?>">
<meta property="og:image" content="<?php echo $img; ?>">
<meta property="og:url" content="<?php echo $canonical_url; ?>">

<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

<style>
    /* ... (CSS cũ của bạn giữ nguyên, không thay đổi) ... */
    #muxua-unique-header { all: initial; font-family: 'Rajdhani', sans-serif; display: block; width: 100%; background: #180303; border-bottom: 1px solid #3d2b1f; box-sizing: border-box; position: sticky; top: 0; z-index: 9999; box-shadow: 0 4px 15px rgba(0,0,0,0.8); }
    #muxua-unique-header *, #muxua-unique-header *::before, #muxua-unique-header *::after { box-sizing: border-box; margin: 0; padding: 0; outline: none; }
    #muxua-unique-header a { text-decoration: none; color: inherit; transition: 0.3s; cursor: pointer; }
    #muxua-unique-header ul { list-style: none; }
    #muxua-unique-header .mh-container { max-width: 1320px; margin: 0 auto; padding: 0 15px; height: 70px; display: flex; align-items: center; justify-content: space-between; position: relative; }
    #muxua-unique-header .mh-logo-link { display: flex; flex-direction: column; justify-content: center; line-height: 1; margin-right: 40px; }
    #muxua-unique-header .mh-brand-main { font-family: 'Metal Mania', cursive; font-weight: 400; font-size: 36px; letter-spacing: 3px; text-transform: uppercase; display: flex; align-items: center; transform: skewX(-10deg); filter: drop-shadow(2px 2px 0px #000); }
    .metal-text { background-clip: text; -webkit-background-clip: text; color: transparent; background-size: 200% auto; animation: shineMetal 3s infinite linear; -webkit-text-stroke: 0.5px rgba(0,0,0,0.3); }
    #muxua-unique-header .mh-brand-gold { margin-right: 5px; background-image: linear-gradient(180deg, #ffeb3b 0%, #d4af37 40%, #ffffff 50%, #8a5d18 51%, #634211 100%); }
    #muxua-unique-header .mh-brand-platinum { background-image: linear-gradient(180deg, #e6f0ff 0%, #aaccff 40%, #ffffff 50%, #7392ae 51%, #8db2d6 100%); }
    #muxua-unique-header .mh-brand-desc { font-size: 10px; color: #888; letter-spacing: 2px; text-transform: uppercase; margin-top: 4px; }
    #muxua-unique-header .mh-nav { flex-grow: 1; height: 100%; display: flex; align-items: center; }
    #muxua-unique-header .mh-menu-list { display: flex; gap: 5px; height: 100%; }
    #muxua-unique-header .mh-menu-item { position: relative; height: 100%; display: flex; align-items: center; }
    #muxua-unique-header .mh-menu-link { font-family: 'Cinzel', serif; font-weight: 700; font-size: 12px; padding: 0 15px; text-transform: uppercase; color: #aaa; display: flex; align-items: center; height: 100%; border-bottom: 2px solid transparent; }
    #muxua-unique-header .mh-menu-link i { margin-right: 6px; font-size: 12px; color: #555; transition: 0.3s; }
    #muxua-unique-header .mh-menu-link:hover { color: #cfaa56; text-shadow: 0 0 8px rgba(207, 170, 86, 0.4); background: rgba(255,255,255,0.02); }
    #muxua-unique-header .mh-menu-link:hover i { color: #8b0000; }
    #muxua-unique-header .mh-link-ads { color: #ffd706 !important; }
    #muxua-unique-header .mh-link-ads i { color: #ffd706 !important; }
    #muxua-unique-header .mh-dropdown { display: none; position: absolute; top: 100%; left: 0; background: #0a0a0a; border: 1px solid #3d2b1f; border-top: 2px solid #8b0000; min-width: 220px; box-shadow: 0 10px 30px rgba(0,0,0,0.9); z-index: 10000; animation: mhFadeIn 0.2s ease-in-out; }
    #muxua-unique-header .mh-menu-item:hover .mh-dropdown { display: block; }
    #muxua-unique-header .mh-dropdown-item { display: block; padding: 12px 15px; color: #ccc; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 13px; font-weight: 600; font-family: 'Rajdhani', sans-serif; text-transform: uppercase; }
    #muxua-unique-header .mh-dropdown-item:hover { background-color: rgba(139, 0, 0, 0.2); color: #fff; padding-left: 20px; }
    #muxua-unique-header .mh-actions { display: flex; align-items: center; gap: 15px; }
    #muxua-unique-header .mh-login-link { font-size: 13px; font-weight: 700; color: #fff; white-space: nowrap; display: flex; align-items: center; }
    #muxua-unique-header .mh-login-link:hover { color: #cfaa56; }
    #muxua-unique-header .mh-btn-post { background: linear-gradient(180deg, #b91c1c 0%, #7f1d1d 100%); color: #fff; font-family: 'Cinzel', serif; font-weight: 700; font-size: 12px; padding: 8px 18px; border: 1px solid #ff5555; text-transform: uppercase; white-space: nowrap; }
    #muxua-unique-header .mh-btn-post:hover { background: linear-gradient(180deg, #dc2626 0%, #991b1b 100%); box-shadow: 0 0 10px rgba(220, 38, 38, 0.6); }
    #muxua-unique-header .mh-user-box { position: relative; cursor: pointer; }
    #muxua-unique-header .mh-user-display { display: flex; align-items: center; gap: 8px; }
    #muxua-unique-header .mh-avatar { width: 32px; height: 32px; border-radius: 4px; border: 1px solid #cfaa56; object-fit: cover; }
    #muxua-unique-header .mh-username { font-weight: 700; color: #cfaa56; font-size: 13px; }
    #muxua-unique-header .mh-user-dropdown { right: 0; left: auto; }
    #muxua-unique-header .mh-user-box:hover .mh-user-dropdown { display: block; }
    #muxua-unique-header .mh-mobile-toggle { display: none; font-size: 24px; color: #cfaa56; padding: 10px; #muxua-unique-header button { border: none; background: none; cursor: pointer; } }
    @media (max-width: 991px) { #muxua-unique-header .mh-container { height: auto; flex-wrap: wrap; padding: 10px 15px; } #muxua-unique-header .mh-mobile-toggle { display: block; margin-left: auto; } #muxua-unique-header .mh-actions { display: none; } #muxua-unique-header .mh-nav { display: none; width: 100%; border-top: 1px solid #333; margin-top: 10px; } #muxua-unique-header .mh-nav.active { display: block; } #muxua-unique-header .mh-menu-list { flex-direction: column; height: auto; gap: 0; } #muxua-unique-header .mh-menu-item { width: 100%; display: block; height: auto; } #muxua-unique-header .mh-menu-link { padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.05); } #muxua-unique-header .mh-dropdown { position: static; box-shadow: none; border: none; background: rgba(255,255,255,0.02); padding-left: 20px; } }
    @keyframes mhFadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes shineMetal { 0% { background-position: -200% center; } 20% { background-position: 200% center; } 100% { background-position: 200% center; } }
</style>

<div id="muxua-unique-header">
    <div class="mh-container">
        <a href="<?php echo $baseUrl; ?>" class="mh-logo-link">
            <div class="mh-brand-main">
                <span class="mh-brand-gold metal-text">MUMOIRA</span>
                <span class="mh-brand-platinum metal-text">.MOBI</span>
            </div>
            <div class="mh-brand-desc">Huyền Thoại Trở Lại</div>
        </a>

        <button class="mh-mobile-toggle" onclick="document.querySelector('#muxua-unique-header .mh-nav').classList.toggle('active')">
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav class="mh-nav">
            <ul class="mh-menu-list">
                <li class="mh-menu-item">
                    <a href="<?php echo $baseUrl; ?>" class="mh-menu-link">
                        <i class="fa-solid fa-house-chimney"></i> Trang Chủ <i class="fa-solid fa-caret-down" style="margin-left: 5px; font-size: 10px;"></i>
                    </a>
                    <ul class="mh-dropdown">
                        <li><a class="mh-dropdown-item" href="<?php echo $baseUrl; ?>?filterType=open&filterDay=today"><i class="fa-solid fa-fire me-2" style="color: #ff4444; width: 20px;"></i> Open Beta Hôm Nay</a></li>
                        <li><a class="mh-dropdown-item" href="<?php echo $baseUrl; ?>?filterType=test&filterDay=today"><i class="fa-solid fa-flask me-2" style="color: #44ff44; width: 20px;"></i> Alpha Test Hôm Nay</a></li>
                    </ul>
                </li>

                <li class="mh-menu-item">
                    <a href="#" class="mh-menu-link">
                        <i class="fa-solid fa-scroll"></i> Phiên Bản <i class="fa-solid fa-caret-down" style="margin-left: 5px; font-size: 10px;"></i>
                    </a>
                    <ul class="mh-dropdown">
                        <?php if (!empty($menuVersions)): ?>
                            <?php foreach ($menuVersions as $ver): ?>
                                <li>
                                    <a class="mh-dropdown-item" href="<?php echo $baseUrl; ?><?php echo createSlug($ver['version_name']); ?>-v<?php echo $ver['version_id']; ?>">
                                        <?php echo htmlspecialchars($ver['version_name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a class="mh-dropdown-item" href="#">Đang cập nhật...</a></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <li class="mh-menu-item">
                    <a href="#" class="mh-menu-link">
                        <i class="fa-solid fa-shield-halved"></i> Loại Reset <i class="fa-solid fa-caret-down" style="margin-left: 5px; font-size: 10px;"></i>
                    </a>
                    <ul class="mh-dropdown">
                        <?php if (!empty($menuTypes)): ?>
                            <?php foreach ($menuTypes as $type): ?>
                                <li>
                                    <a class="mh-dropdown-item" href="<?php echo $baseUrl; ?><?php echo createSlug($type['reset_name']); ?>-r<?php echo $type['reset_id']; ?>">
                                        <?php echo htmlspecialchars($type['reset_name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                         <?php else: ?>
                            <li><a class="mh-dropdown-item" href="#">Đang cập nhật...</a></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <li class="mh-menu-item">
                    <a href="<?php echo $baseUrl; ?>huong-dan" class="mh-menu-link"><i class="fa-solid fa-book-open"></i> Hướng Dẫn</a>
                </li>
                <li class="mh-menu-item">
                    <a href="<?php echo $baseUrl; ?>banner-register" class="mh-menu-link mh-link-ads"><i class="fa-solid fa-crown" style="font-size: 16px !important;"></i> Quảng Cáo</a>
                </li>
            </ul>
        </nav>

        <div class="mh-actions">
                    <?php if (isset($_SESSION['user_id'])): // CHÚ Ý: Đổi 'user' thành 'user_id' để khớp session chuẩn ?>
                        <?php 
                            // Nếu session lưu nguyên mảng user thì dùng dòng dưới
                            // $user = $_SESSION['user'];
                            // Nếu chỉ lưu ID thì cần truy vấn lại hoặc dùng $_SESSION['username']
                            $username = $_SESSION['username'] ?? 'Thành viên';
                        ?>
                        <div class="mh-user-box">
                            <div class="mh-user-display">
                                <i class="fa-solid fa-circle-user" style="font-size: 32px; color: #cfaa56;"></i>
                                <span class="mh-username"><?php echo htmlspecialchars($username); ?></span>
                                <i class="fa-solid fa-caret-down" style="color: #666; font-size: 12px; margin-left: 5px;"></i>
                            </div>
                            
                            <ul class="mh-dropdown mh-user-dropdown">
                                <li><a class="mh-dropdown-item" href="<?php echo $baseUrl; ?>profile">Thông tin cá nhân</a></li>
                                <li><a class="mh-dropdown-item" href="<?php echo $baseUrl; ?>manage-server">Quản lý tin đăng</a></li>
                                <li><a class="mh-dropdown-item" href="<?php echo $baseUrl; ?>huong-dan#section-payment">Nạp Coins</a></li>
                               
                                <li style="border-top: 1px solid #333;">
                                    <a class="mh-dropdown-item" href="<?php echo $baseUrl; ?>logout" style="color: #ff5555;">
                                        <i class="fa-solid fa-power-off"></i> Đăng Xuất
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $baseUrl; ?>login" class="mh-login-link">
                            <i class="fa-solid fa-right-to-bracket" style="margin-right:5px;"></i> Đăng nhập
                        </a>
                    <?php endif; ?>

                    <a href="<?php echo $baseUrl; ?>create-server" class="mh-btn-post">
                        <i class="fa-solid fa-plus"></i> Đăng MU
                    </a>
                </div>
            </div>
        </div>  
    </div>
</div>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "MU Mới Ra",
  "url": "<?php echo $baseUrl; ?>",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "<?php echo $baseUrl; ?>?s={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>