<?php
// File này chỉ chứa View, dữ liệu đã được HomeController chuẩn bị sẵn.
// Các biến có sẵn: $superVips, $vips, $normals, $bannersHero, $bannersLeft, v.v.

// Hàm hỗ trợ lấy banner an toàn (chỉ cho View)
if (!function_exists('getBanner')) {
    function getBanner($list, $index) {
        return isset($list[$index]) ? $list[$index] : null;
    }
}
?>
<?php
// --- Xử lý Logic Ngày Tháng (Tương đương đoạn đầu của JSP) ---
date_default_timezone_set('Asia/Ho_Chi_Minh');
$todayStr     = date('d/m/Y');
$yesterdayStr = date('d/m/Y', strtotime('-1 day'));
$tomorrowStr  = date('d/m/Y', strtotime('+1 day'));

// Giả lập biến title/description nếu chưa có (tránh lỗi undefined)
$pageTitle = $pageTitle ?? 'Mu Mới Ra | Mumoira Portal';
$metaDescription = $metaDescription ?? 'Cổng game Mu Online, Mu Mobile mới ra mắt.';
$canonicalUrl = $canonicalUrl ?? '';

// Hàm hỗ trợ kiểm tra mảng banner (an toàn)
function getBanner($list, $index) {
    return isset($list[$index]) ? $list[$index] : null;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <meta name="description" content="<?= $metaDescription ?>">
    <meta name="keywords" content="mu moi ra, mu online, mu private, game mu, mu ss6, mu reset, mu non reset">

    <?php if (!empty($canonicalUrl)): ?>
        <link rel="canonical" href="<?= $canonicalUrl ?>" />
    <?php endif; ?>

    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?= $pageTitle ?>" />
    <meta property="og:description" content="<?= $metaDescription ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= !empty($canonicalUrl) ? $canonicalUrl : 'https://mumoira.mobile/' ?>" />
    <meta property="og:image" content="https://mumoira.mobile/images/thumbnail-share.jpg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --mu-bg: #050505;
            --mu-panel-bg: rgba(12, 12, 12, 0.95);
            --mu-gold: #cfaa56;
            --mu-gold-dark: #8a6d3b;
            --mu-red: #cc0000;
            --mu-border: #3d2b1f;
            --mu-text: #ccc;
        }

        body {
            background-color: var(--mu-bg);
            background-image: radial-gradient(circle at 50% 0%, #1a0505 0%, #000000 80%);
            background-attachment: fixed;
            font-family: 'Rajdhani', sans-serif;
            color: var(--mu-text);
            min-width: 1400px;
            overflow-x: auto;
            margin: 0;
        }

        a { text-decoration: none; transition: 0.3s; }

        /* Layout */
        .main-wrapper { display: flex; justify-content: center; gap: 15px; padding: 15px; max-width: 1900px; margin: 0 auto; }
        .sidebar { width: 280px; min-width: 280px; flex-shrink: 0; display: flex; flex-direction: column; gap: 15px; }
        .content-area { flex-grow: 1; max-width: 750px; }

        /* Banners */
        .mu-item-frame {
            display: block; width: 100%; border: 1px solid var(--mu-border); background: #000;
            position: relative; transition: all 0.3s ease; box-shadow: 0 0 10px rgba(0,0,0,0.8);
            outline: 1px solid rgba(255, 255, 255, 0.05); outline-offset: -5px; overflow: hidden;
        }
        .mu-item-frame:hover { border-color: var(--mu-gold); box-shadow: 0 0 15px rgba(207, 170, 86, 0.3); transform: translateY(-2px); z-index: 10; }
        .real-ad-img { width: 100%; height: 100%; display: block; object-fit: fill; opacity: 0.9; transition: 0.3s; }
        .mu-item-frame:hover .real-ad-img { opacity: 1; scale: 1.02; }
        .h-banner-box { margin-bottom: 12px; height: 90px; width: 100%; }
        .v-banner-box { height: 450px; }

        .hero-frame { border: 1px solid var(--mu-gold-dark); padding: 3px; background: rgba(0,0,0,0.5); margin-bottom: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.8); }
        .ads-placeholder { display: flex; justify-content: center; align-items: center; background: rgba(255, 255, 255, 0.03); border: 1px dashed var(--mu-gold-dark); color: #666; }
        .ads-text { font-family: 'Cinzel', serif; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; }

        /* Animations */
        @keyframes spinBorder { 0% { transform: translate(-50%, -50%) rotate(0deg); } 100% { transform: translate(-50%, -50%) rotate(360deg); } }
        @keyframes shimmerGold { 0% { background-position: -150% 0; } 100% { background-position: 150% 0; } }
        @keyframes pulseRed { 0% { box-shadow: 0 0 10px rgba(255, 0, 0, 0.3); } 50% { box-shadow: 0 0 25px rgba(255, 0, 0, 0.7); } 100% { box-shadow: 0 0 10px rgba(255, 0, 0, 0.3); } }

        /* Server Section */
        .server-section { background: var(--mu-panel-bg); border: 1px solid var(--mu-border); box-shadow: 0 0 30px rgba(0,0,0,0.8); margin-bottom: 15px; width: 100%; }
        .section-header { background: linear-gradient(90deg, #330000 0%, #1a0505 100%); border-bottom: 1px solid var(--mu-red); padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; }
        .section-title { font-family: 'Cinzel', serif; font-weight: 700; color: var(--mu-gold); font-size: 1.1rem; margin: 0; }

        /* Filter Bar */
        .filter-bar { padding: 10px 15px; background: rgba(0,0,0,0.5); border-bottom: 1px solid #333; display: flex; gap: 10px; align-items: center; }
        .mu-select { background-color: #000; color: #ccc; border: 1px solid #444; font-size: 0.85rem; padding: 5px 10px; font-family: 'Rajdhani', sans-serif; cursor: pointer; }
        .mu-select:focus { outline: none; border-color: var(--mu-gold); box-shadow: 0 0 5px rgba(207, 170, 86, 0.5); }
        .btn-filter { background: var(--mu-red); color: white; border: none; font-size: 0.85rem; font-weight: bold; padding: 6px 15px; transition: 0.2s; }
        .btn-filter:hover { background: #ff0000; box-shadow: 0 0 10px #ff0000; }
        .btn-reset-filter { background: #333; color: #ccc; border: 1px solid #555; font-size: 0.85rem; padding: 6px 12px; }
        .btn-reset-filter:hover { background: #555; color: white; }

        .srv-header { display: flex; background: linear-gradient(180deg, #1f0a0a 0%, #0a0505 100%); border-top: 1px solid var(--mu-gold-dark); border-bottom: 1px solid var(--mu-gold-dark); padding: 12px 0; font-family: 'Cinzel', serif; font-size: 0.8rem; color: var(--mu-gold); text-transform: uppercase; font-weight: 800; z-index: 5; position: relative; }
        .hdr-left { width: 30%; padding-left: 20px; }
        .hdr-right { width: 70%; display: flex; justify-content: space-between; padding-right: 15px; text-align: center; }
        .hdr-item { flex: 1; }

        /* Row Styles */
.srv-row-inner {
    display: flex;
    align-items: center;
    padding: 6px 0; /* giảm 50% chiều cao */
}
.col-left-identity {
    width: 30%;
    padding-left: 12px;
    padding-right: 8px;
    line-height: 1.2; /* giảm độ giãn dòng */
}
        .col-right-wrapper { width: 70%; display: flex; flex-direction: column; padding: 0 15px; justify-content: center; gap: 8px; }
        .stats-line { display: flex; justify-content: space-between; align-items: center; width: 100%; text-align: center; }
        .stat-box { flex: 1; font-size: 0.9rem; }
        .banner-line { width: 100%; display: flex; justify-content: center; align-items: center; margin-top: 5px; }
.inner-banner-img {
    width: 95%;
    height: 50px; /* thấp hơn cho gọn */
    object-fit: fill;
}


        /* SVIP Wrapper */
        .svip-wrapper { position: relative; margin-bottom: 14px; border-radius: 6px; padding: 4px; overflow: hidden; animation: pulseRed 1s infinite alternate; background: #000; }
        .svip-wrapper::before, .svip-wrapper::after { content: ''; position: absolute; top: 50%; left: 50%; width: 300%; height: 1000%; background: conic-gradient(transparent 0deg, transparent 140deg, #da0000 160deg, #ff7300 170deg, #ffff00 175deg, #ffffff 180deg, #ffff00 185deg, #ff7300 190deg, #da0000 200deg, transparent 220deg); transform: translate(-50%, -50%); animation: spinBorder 2.5s linear infinite; z-index: 1; }
        .svip-wrapper::after { animation-delay: -1.25s; }
.svip-content {
    position: relative;
    z-index: 2;
    background: linear-gradient(90deg, #250000 0%, #150000 100%);
    border-radius: 4px;
    width: 100%;
    height: auto;          /* QUAN TRỌNG: bỏ height 100% */
    padding: 6px 10px;     /* thêm đệm nhỏ bên trong cho gọn */
}
        .name-super-vip { color: rgb(212 253 13); font-size: 1.25rem; font-weight: 700; text-shadow: 0 0 10px #f7ff00; font-family: 'Cinzel', serif; }
        .btn-view-svip { background: linear-gradient(180deg, #cc0000 0%, #660000 100%); border: 1px solid #ff3333; color: #fff; padding: 4px 15px; font-size: 0.75rem; }
        .btn-view-svip:hover { box-shadow: 0 0 15px red; color: #fff; transform: scale(1.05); }

        /* VIP Wrapper */
        .vip-wrapper { position: relative; margin-bottom: 10px; border-radius: 4px; padding: 2px; background: linear-gradient(110deg, #333 30%, #cfaa56 45%, #fff 50%, #cfaa56 55%, #333 70%); background-size: 200% 100%; animation: shimmerGold 2.5s linear infinite; box-shadow: 0 0 5px rgba(207, 170, 86, 0.2); }
        .vip-content { position: relative; z-index: 2; background: linear-gradient(90deg, #1a1a1a 0%, #0c0c0c 100%); border-radius: 3px; }
        .name-vip { color: var(--mu-gold); font-size: 1.15rem; font-weight: 700; font-family: 'Cinzel', serif; }
        .btn-view { border: 1px solid var(--mu-gold-dark); color: var(--mu-gold); padding: 4px 12px; font-size: 0.75rem; font-family: 'Cinzel', serif; }
        .btn-view:hover { background: var(--mu-gold); color: #000; }
        .vip-content .inner-banner-img { height: 45px; border-color: #444; }

        /* Normal Wrapper */
        .normal-wrapper { border-bottom: 1px solid #5b5b5b; transition: 0.5s; }
        .normal-wrapper:hover { background-color: rgba(220, 220, 220, 0.35); }
        .name-normal { color: #dddddd; font-weight: 600; font-size: 1.2rem; }
        .badge-ver { background: #222; border: 1px solid #aeaeae; color: #fefefe; padding: 2px 6px; font-size: 0.7rem; }
        .badge-svip { background: #d00; color: #fff; font-size: 0.7rem; padding: 1px 5px; font-weight: bold; border-radius: 2px; }

        /* Widget Lịch & Badge Ngày */
        .schedule-widget { margin-top: 20px; border: 1px solid var(--mu-border); background: #000; padding: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.8); }
        .sw-header { color: #fff; font-family: 'Cinzel'; margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 5px; font-size: 1rem; font-weight: bold; }
        .sw-grid { display: flex; gap: 15px; }
        .sw-col { flex: 1; }
        .sw-btn-big { display: block; width: 100%; padding: 20px 0; text-align: center; text-decoration: none; transition: 0.3s; border: 1px solid #333; }
        .sw-btn-big:hover { transform: translateY(-3px); box-shadow: 0 5px 20px rgba(0,0,0,0.6); }

        .open-btn { background: linear-gradient(180deg, #2b1d00 0%, #000 100%); border-bottom: 3px solid var(--mu-gold); }
        .open-btn .big-title { color: var(--mu-gold); font-family: 'Cinzel'; font-size: 1.2rem; font-weight: bold; text-shadow: 0 0 10px rgba(207, 170, 86, 0.3); }
        .open-btn:hover { border-color: #ffd700; background: linear-gradient(180deg, #3d2b05 0%, #050505 100%); }

        .test-btn { background: linear-gradient(180deg, #001a2b 0%, #000 100%); border-bottom: 3px solid #00eaff; }
        .test-btn .big-title { color: #00eaff; font-family: 'Cinzel'; font-size: 1.2rem; font-weight: bold; text-shadow: 0 0 10px rgba(0, 234, 255, 0.3); }
        .test-btn:hover { border-color: #fff; background: linear-gradient(180deg, #002a45 0%, #050505 100%); }

        .big-sub { color: #888; font-size: 0.8rem; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px; }
        .sw-btn-big:hover .big-sub { color: #ccc; }

        /* Badge Ngày */
        .day-badge { font-size: 0.65rem; padding: 2px 6px; border-radius: 3px; margin-left: 8px; text-transform: uppercase; font-weight: bold; display: inline-block; vertical-align: middle; position: relative; top: -1px; }
        .day-today { background: #008000; color: #fff; box-shadow: 0 0 5px #00ff00; border: 1px solid #00ff00; }
        .day-tomorrow { background: #0056b3; color: #fff; border: 1px solid #00aaff; }
        .day-yesterday { background: #333; color: #999; border: 1px solid #555; }
    </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>

<?php if(file_exists('header.php')) include 'header.php'; ?>

<div class="main-wrapper">

    <aside class="sidebar">
        <?php for ($i = 0; $i < 3; $i++): ?>
            <?php $banner = getBanner($bannersLeft, $i); ?>
            <?php if ($banner): ?>
                <a href="<?= $banner['target_url'] ?>" class="mu-item-frame v-banner-box" target="_blank">
                    <img src="<?= $banner['image_url'] ?>" class="real-ad-img" alt="Quảng cáo">
                </a>
            <?php else: ?>
                <a href="index.php?url=banner-register" class="mu-item-frame ads-placeholder v-banner-box">
                    <div class="ads-content text-center">
                        <i class="fa-solid fa-plus click-icon"></i>
                        <div class="ads-text">Banner Trái<br>Vị trí <?= $i + 1 ?></div>
                    </div>
                </a>
            <?php endif; ?>
        <?php endfor; ?>
    </aside>

    <main class="content-area">

        <?php if (!empty($bannersHero)): ?>
            <div class="hero-frame">
                <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <?php foreach ($bannersHero as $index => $banner): ?>
                            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach ($bannersHero as $index => $banner): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" data-bs-interval="3000">
                                <a href="<?= $banner['target_url'] ?>" target="_blank">
                                    <img src="<?= $banner['image_url'] ?>" class="d-block w-100" style="height: 320px; object-fit: fill;">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="index.php?url=banner-register" class="mu-item-frame ads-placeholder mb-3" style="width: 100%; height: 320px;">
                <div class="ads-content text-center">
                    <span class="ads-text fs-5 text-warning">VỊ TRÍ HERO BANNER (VIP)</span>
                </div>
            </a>
        <?php endif; ?>

        <?php for ($i = 0; $i < 7; $i++): ?>
            <?php $banner = getBanner($bannersStd, $i); ?>
            <?php if ($banner): ?>
                <a href="<?= $banner['target_url'] ?>" class="mu-item-frame h-banner-box" target="_blank">
                    <img src="<?= $banner['image_url'] ?>" class="real-ad-img">
                </a>
            <?php else: ?>
                <a href="index.php?url=banner-register" class="mu-item-frame ads-placeholder h-banner-box">
                    <div class="ads-content d-flex align-items-center gap-3">
                        <i class="fa-regular fa-image click-icon mb-0"></i>
                        <div class="text-start">
                            <div class="ads-text">Banner Ngang <?= $i + 1 ?></div>
                        </div>
                    </div>
                </a>
            <?php endif; ?>
        <?php endfor; ?>

        <div class="server-section" id="result-list">
            <div class="section-header">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-dragon text-danger me-2 fs-5"></i>
                    <h3 class="section-title">
                        <?php if (!empty($isSearching)): ?>
                            <?= !empty($filterDisplay) ? $filterDisplay : 'KẾT QUẢ TÌM KIẾM' ?>
                        <?php else: ?>
                            DANH SÁCH SERVER VIP
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="small text-secondary fst-italic">
                    <i class="fa-solid fa-clock me-1"></i> <?= date('d/m/Y') ?>
                </div>
            </div>

            <form action="" method="GET" class="filter-bar">
                <input type="hidden" name="filterType" value="<?= $filterType ?? 'open' ?>">
                <div class="flex-grow-1">
                    <select name="versionId" class="form-select form-select-sm mu-select">
                        <option value="">-- Tất cả phiên bản --</option>
                        <?php if (!empty($menuVersions)): ?>
                            <?php foreach ($menuVersions as $ver): ?>
                                <option value="<?= $ver['version_id'] ?>" <?= ($ver['version_id'] == ($selectedVersion ?? '')) ? 'selected' : '' ?>>
                                    <?= $ver['version_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="flex-grow-1">
                    <select name="reset" class="form-select form-select-sm mu-select">
                        <option value="">-- Tất cả kiểu Reset --</option>
                        <?php if (!empty($menuTypes)): ?>
                            <?php foreach ($menuTypes as $rt): ?>
                                <option value="<?= $rt['reset_id'] ?>" <?= ($rt['reset_id'] == ($selectedReset ?? '')) ? 'selected' : '' ?>>
                                    <?= $rt['reset_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <button type="submit" class="btn-filter"><i class="fa-solid fa-filter me-1"></i> LỌC NGAY</button>

                <?php if (!empty($isSearching)): ?>
                    <a href="?" class="btn-reset-filter" title="Xóa lọc"><i class="fa-solid fa-rotate-left"></i></a>
                <?php endif; ?>
            </form>

            <div class="srv-header">
                <div class="hdr-left">Thông tin Server</div>
                <div class="hdr-right">
                    <div class="hdr-item">Phiên bản</div>
                    <div class="hdr-item">Reset</div>
                    <div class="hdr-item">
                        <?= ($filterType == 'test') ? 'Alpha Test' : 'Open Beta' ?>
                    </div>
                    <div class="hdr-item">Chi tiết</div>
                </div>
            </div>

            <div style="padding: 15px;">

                <?php foreach ($superVips as $sv): ?>
                    <?php 
                        $svDate = ($filterType == 'test') ? $sv['date_alpha'] : $sv['date_open'];
                        $badgeHtml = '';
                        if (isset($currentFilterDay)) {
                            if ($svDate == $todayStr) $badgeHtml = "<span class='day-badge day-today'>HÔM NAY</span>";
                            elseif ($svDate == $tomorrowStr) $badgeHtml = "<span class='day-badge day-tomorrow'>NGÀY MAI</span>";
                            elseif ($svDate == $yesterdayStr) $badgeHtml = "<span class='day-badge day-yesterday'>HÔM QUA</span>";
                        }
                    ?>
                    <div class="svip-wrapper">
                        <div class="svip-content">
                            <div class="srv-row-inner">
                                <div class="col-left-identity">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="badge badge-svip me-2">HOT</span>
                                        <a href="<?= $sv['website_url'] ?>" target="_blank" class="name-super-vip"><?= $sv['server_name'] ?></a>
                                        <?= $badgeHtml ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: #ddd; line-height: 1.2;">
                                        <img src="https://cdn.pixabay.com/animation/2025/11/03/21/48/21-48-26-427_512.gif" style="width: 20px; margin-right: 5px;" alt="icon">
                                        <?= $sv['mu_name'] ?? 'Mu Online' ?>
                                    </div>
                                    <div class="text-warning fst-italic mt-1 text-truncate" style="font-size: 0.75rem;">
                                        "<?= $sv['slogan'] ?? 'Chào đón game thủ' ?>"
                                    </div>
                                </div>

                                <div class="col-right-wrapper">
                                    <div class="stats-line">
                                        <div class="stat-box"><span class="badge-ver text-warning"><?= $sv['version_name'] ?></span></div>
                                        <div class="stat-box text-light fw-bold"><?= $sv['reset_name'] ?></div>

                                        <div class="stat-box text-danger fw-bold" style="font-size: 1rem;">
                                            <?= $svDate ?>
                                        </div>

                                        <div class="stat-box">
                                            <a href="index.php?url=server-detail&id=<?= $sv['server_id'] ?>" class="btn-view btn-view-svip">XEM NGAY</a>
                                        </div>                                    </div>
                                    <div class="banner-line">
                                        <?php if (!empty($sv['image_url'])): ?>
                                            <img src="<?= $sv['image_url'] ?>" class="inner-banner-img" alt="<?= $sv['server_name'] ?>">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/600x60/550000/FFFFFF?text=MU+ONLINE+VIP" class="inner-banner-img">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($vips as $sv): ?>
                    <?php 
                        $svDate = ($filterType == 'test') ? $sv['date_alpha'] : $sv['date_open'];
                        $badgeHtml = '';
                        if (isset($currentFilterDay)) {
                            if ($svDate == $todayStr) $badgeHtml = "<span class='day-badge day-today'>HÔM NAY</span>";
                            elseif ($svDate == $tomorrowStr) $badgeHtml = "<span class='day-badge day-tomorrow'>NGÀY MAI</span>";
                            elseif ($svDate == $yesterdayStr) $badgeHtml = "<span class='day-badge day-yesterday'>HÔM QUA</span>";
                        }
                    ?>
                    <div class="vip-wrapper">
                        <div class="vip-content">
                            <div class="srv-row-inner">
                                <div class="col-left-identity">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fa-solid fa-star text-warning me-2" style="font-size: 0.7rem;"></i>
                                        <a href="<?= $sv['website_url'] ?>" target="_blank" class="name-vip"><?= $sv['server_name'] ?></a>
                                        <?= $badgeHtml ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #888;"><?= $sv['mu_name'] ?? 'Mu Online' ?></div>
                                </div>

                                <div class="col-right-wrapper">
                                    <div class="stats-line">
                                        <div class="stat-box"><span class="badge-ver"><?= $sv['version_name'] ?></span></div>
                                        <div class="stat-box" style="color: #dbd2b2;">
                                            <?= $sv['reset_name'] ?>
                                        </div>
                                        <div class="stat-box text-light"><?= $svDate ?></div>
                                        <div class="stat-box"><a href="index.php?url=server-detail&id=<?= $sv['server_id'] ?>" class="btn-view">XEM</a></div>
                                    </div>
                                    <div class="banner-line">
                                        <?php if (!empty($sv['image_url'])): ?>
                                            <img src="<?= $sv['image_url'] ?>" class="inner-banner-img" alt="<?= $sv['server_name'] ?>">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/600x50/333333/888888?text=VIP+SERVER" class="inner-banner-img">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($normals as $sv): ?>
                    <?php 
                        $svDate = ($filterType == 'test') ? $sv['date_alpha'] : $sv['date_open'];
                        $badgeHtml = '';
                        if (isset($currentFilterDay)) {
                            if ($svDate == $todayStr) $badgeHtml = "<span class='day-badge day-today'>HÔM NAY</span>";
                            elseif ($svDate == $tomorrowStr) $badgeHtml = "<span class='day-badge day-tomorrow'>NGÀY MAI</span>";
                            elseif ($svDate == $yesterdayStr) $badgeHtml = "<span class='day-badge day-yesterday'>HÔM QUA</span>";
                        }
                    ?>
                    <div class="normal-wrapper">
                        <div class="srv-row-inner">
                            <div class="col-left-identity">
                                <div class="d-flex align-items-center">
                                    <a href="<?= $sv['website_url'] ?>" target="_blank" class="name-normal mb-1"><?= $sv['server_name'] ?></a>
                                    <?= $badgeHtml ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #555;"><?= $sv['mu_name'] ?? 'Mu Online' ?></div>
                            </div>

                            <div class="col-right-wrapper" style="justify-content: center;">
                                <div class="stats-line mb-0">
                                    <div class="stat-box">
                                        <span class="badge-ver" style="border:none; background:transparent;"><?= $sv['version_name'] ?></span>
                                    </div>
                                    <div class="stat-box small" style="color: #aeb5b6;"><?= $sv['reset_name'] ?></div>
                                     <div class="stat-box small" style="color: #aeb5b6;"><?= $svDate ?></div>

                                    <div class="stat-box">
                                        <a href="index.php?url=server-detail&id=<?= $sv['server_id'] ?>" class="btn-view" style="color: #666; border-color: #444;">Xem</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($superVips) && empty($vips) && empty($normals)): ?>
                    <div class="text-center text-muted py-5 fst-italic">
                        <i class="fa-solid fa-search fa-2x mb-3 text-secondary"></i><br>
                        <?php if (!empty($isSearching)): ?>
                            Không tìm thấy Server nào khớp với tiêu chí tìm kiếm.<br>
                            <a href="?" class="text-warning mt-2 d-inline-block">Xóa bộ lọc & Quay lại</a>
                        <?php else: ?>
                            Hiện chưa có server nào ra mắt hôm nay.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <div class="schedule-widget">
            <div class="sw-header"><i class="fa-regular fa-calendar-days me-2"></i> Lịch Ra Mắt Server (Hôm qua - Nay - Mai)</div>
            <div class="sw-grid">
                <div class="sw-col">
                    <a href="?filterType=open&filterDay=3days" class="sw-btn-big open-btn">
                        <div class="big-title">LỊCH OPEN BETA</div>
                        <div class="big-sub">Danh sách 3 ngày gần nhất</div>
                    </a>
                </div>
                <div class="sw-col">
                    <a href="?filterType=test&filterDay=3days" class="sw-btn-big test-btn">
                        <div class="big-title">LỊCH ALPHA TEST</div>
                        <div class="big-sub">Danh sách 3 ngày gần nhất</div>
                    </a>
                </div>
            </div>
        </div>

    </main>

    <aside class="sidebar">
        <?php for ($i = 0; $i < 3; $i++): ?>
            <?php $banner = getBanner($bannersRight, $i); ?>
            <?php if ($banner): ?>
                <a href="<?= $banner['target_url'] ?>" class="mu-item-frame v-banner-box" target="_blank">
                    <img src="<?= $banner['image_url'] ?>" class="real-ad-img" alt="Quảng cáo">
                </a>
            <?php else: ?>
                <a href="index.php?url=banner-register" class="mu-item-frame ads-placeholder v-banner-box">
                    <div class="ads-content text-center">
                        <i class="fa-solid fa-plus click-icon"></i>
                        <div class="ads-text">Banner Phải<br>Vị trí <?= $i + 4 ?></div>
                    </div>
                </a>
            <?php endif; ?>
        <?php endfor; ?>
    </aside>

</div>

  <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>