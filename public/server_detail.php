<?php
/**
 * @var object $server Biến chứa thông tin server được truyền từ Controller
 */

// ========================================================
// 1. SEO LOGIC: XỬ LÝ DỮ LIỆU (PHP thuần thay cho JSTL)
// ========================================================

// Tạo Slug giả lập
$rawName = strtolower($server->serverName);
$slug = str_replace([' ', 'đ'], ['-', 'd'], $rawName);
// Xử lý tiếng việt có dấu thành không dấu kỹ hơn nếu cần (ở đây làm đơn giản theo JSP cũ)
$finalUrl = "https://mumoira.mobi/server/" . $slug . "-" . $server->id;

// Format ngày tháng hiển thị
$alphaDateDisplay = $server->schedule->alphaDate ? date('d/m/Y', strtotime($server->schedule->alphaDate)) : 'Chưa cập nhật';
$betaDateDisplay = $server->schedule->betaDate ? date('d/m/Y', strtotime($server->schedule->betaDate)) : 'Coming Soon';

// Tạo Meta Description
$metaDesc = "Mu Online {$server->serverName}. Phiên bản {$server->stats->muVersionName} miễn phí. ";
$metaDesc .= "Alpha Test: {$alphaDateDisplay}. Open Beta: {$betaDateDisplay}. ";
$metaDesc .= "Reset: {$server->stats->resetTypeName}. Thể loại: {$server->serverTypeName}. Đăng ký ngay!";

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $server->serverName ?> - Mu Mới Ra Hôm Nay | MuMoiRa Portal</title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta name="keywords" content="mu moi ra, mu online, <?= $server->serverName ?>, mu private, game mu, mu mobile, mu pc, <?= $slug ?>, <?= $server->serverTypeName ?>">
    <meta name="robots" content="index, follow">
    <meta name="author" content="<?= htmlspecialchars($server->muName) ?>">

    <link rel="canonical" href="<?= $finalUrl ?>" />

    <meta property="og:type" content="website" />
    <meta property="og:title" content="Mu <?= $server->serverName ?> - Ra Mắt Máy Chủ Mới" />
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>" />
    <meta property="og:url" content="<?= $finalUrl ?>" />
    <meta property="og:site_name" content="Mumoira Mobi" />
    <?php if (!empty($server->bannerImage)): ?>
        <meta property="og:image" content="https://mumoia.mobi<?= $server->bannerImage ?>" />
        <meta property="og:image:alt" content="Banner Mu <?= $server->serverName ?>" />
    <?php endif; ?>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [{
            "@type": "ListItem",
            "position": 1,
            "name": "Trang Chủ",
            "item": "https://mumoira.mobi/"
        },{
            "@type": "ListItem",
            "position": 2,
            "name": "Danh Sách Server",
            "item": "https://mumoira.mobi/#result-list"
        },{
            "@type": "ListItem",
            "position": 3,
            "name": "<?= $server->serverName ?>"
        }]
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "VideoGame",
        "name": "Mu Online: <?= $server->serverName ?>",
        "description": "<?= htmlspecialchars($metaDesc) ?>",
        "genre": ["MMORPG", "Fantasy", "<?= $server->serverTypeName ?>"],
        "playMode": "MultiPlayer",
        "applicationCategory": "Game",
        "operatingSystem": "Windows, Android, iOS",
        "datePublished": "<?= $server->schedule->betaDate ?? '' ?>",
        "author": {
            "@type": "Organization",
            "name": "<?= $server->muName ?>"
        }
    }
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --mu-bg: #050505;
            --mu-panel-bg: rgba(15, 10, 10, 0.9);
            --mu-gold: #ffcc00;
            --mu-gold-dark: #b8860b;
            --mu-red: #ff0000;
            --mu-border: #3d2b1f;
        }

        body {
            background-color: var(--mu-bg);
            background-image: radial-gradient(circle at 50% 30%, #2a0505 0%, #000000 70%);
            background-attachment: fixed;
            font-family: 'Rajdhani', sans-serif;
            color: #ccc;
            min-height: 100vh;
        }

        /* BREADCRUMB STYLE */
        .breadcrumb-item a { color: #888; transition: 0.3s; }
        .breadcrumb-item a:hover { color: var(--mu-gold); }
        .breadcrumb-item.active { color: var(--mu-gold); font-weight: 600; }
        .breadcrumb-item+.breadcrumb-item::before { color: #444; }

        /* HEADER & TYPOGRAPHY */
        .server-header h1 {
            font-family: 'Cinzel', serif;
            font-weight: 900;
            text-transform: uppercase;
            background: linear-gradient(180deg, #fff 10%, #ffcc00 50%, #b8860b 90%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(255, 204, 0, 0.3);
            margin-bottom: 5px;
            font-size: 2.8rem;
            line-height: 1.2;
        }
        .server-mu-name {
            font-family: 'Cinzel', serif;
            letter-spacing: 3px;
            color: #888;
            font-size: 1.1rem;
            display: inline-block;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        /* LAYOUT PANELS */
        .detail-panel {
            background: var(--mu-panel-bg);
            border: 1px solid var(--mu-border);
            padding: 20px;
            height: fit-content;
            min-height: auto;
            backdrop-filter: blur(5px);
            clip-path: polygon(15px 0, 100% 0, 100% calc(100% - 15px), calc(100% - 15px) 100%, 0 100%, 0 15px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
            display: flex;
            flex-direction: column;
        }

        /* BANNER */
        .server-banner-container {
            width: 100%;
            border: 1px solid #444;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .server-banner-img {
            width: 100%; height: auto; max-height: 280px; object-fit: cover; display: block;
            transition: transform 0.5s;
        }
        .server-banner-container:hover .server-banner-img { transform: scale(1.02); }

        /* SCHEDULE BOXES */
        .schedule-row { display: flex; gap: 15px; margin-bottom: 20px; }
        .schedule-item {
            flex: 1; background: rgba(0,0,0,0.4); border: 1px solid #333;
            padding: 15px; text-align: center; border-radius: 4px; transition: 0.3s;
        }
        .schedule-title {
            font-family: 'Cinzel', serif; font-weight: 700; text-transform: uppercase;
            font-size: 0.9rem; margin-bottom: 5px;
        }
        .schedule-time { font-family: 'Rajdhani', sans-serif; font-size: 1.4rem; font-weight: 700; color: #fff; }
        .schedule-date { font-size: 1.2rem; color: #aaa; }

        .sch-alpha { border-color: #004466; }
        .sch-alpha .schedule-title { color: #00d2ff; }
        .sch-alpha:hover { box-shadow: 0 0 15px rgba(0, 210, 255, 0.2); }

        .sch-beta { border-color: #660000; background: linear-gradient(180deg, rgba(40,0,0,0.5) 0%, rgba(10,0,0,0.5) 100%); }
        .sch-beta .schedule-title { color: #ff3333; }
        .sch-beta:hover { box-shadow: 0 0 25px rgba(255, 0, 0, 0.4); border-color: var(--mu-red); }

        /* SPECS & INFO */
        .spec-list { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        .spec-item {
            background: rgba(255, 255, 255, 0.03); border-left: 3px solid #333;
            padding: 8px 12px; display: flex; justify-content: space-between; align-items: center;
        }
        .spec-item:hover { background: rgba(255, 204, 0, 0.05); border-left-color: var(--mu-gold); }
        .spec-label { font-size: 0.85rem; text-transform: uppercase; color: #888; }
        .spec-value { font-weight: 700; color: #e0e0e0; font-size: 1.1rem; }
        .spec-highlight { color: var(--mu-gold); }

        /* BUTTONS */
        .btn-mu-main {
            background: linear-gradient(90deg, #550000 0%, #8b0000 100%);
            color: #fff; border: 1px solid #a00000;
            font-family: 'Cinzel', serif; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            width: 100%; padding: 12px;
            clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px);
            transition: all 0.3s; text-decoration: none; display: block; text-align: center; margin-bottom: 10px;
        }
        .btn-mu-main:hover {
            background: linear-gradient(90deg, #8b0000 0%, #ff0000 100%);
            color: #fff; box-shadow: 0 0 15px rgba(255,0,0,0.6); border-color: #ff3333;
        }
        .btn-mu-sub {
            background: linear-gradient(90deg, #b8860b 0%, #daa520 100%);
            color: #000; border: 1px solid #ffd700;
        }
        .btn-mu-sub:hover {
            background: linear-gradient(90deg, #daa520 0%, #ffd700 100%); color: #000;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.4);
        }
        .status-badge {
            font-family: 'Cinzel', serif; font-size: 0.8rem; padding: 4px 10px;
            border: 1px solid; text-transform: uppercase; display: inline-block; margin-right: 5px;
        }

        /* DESCRIPTION CONTENT */
        .desc-container {
            flex-grow: 1; background: rgba(0,0,0,0.3); border: 1px solid #333;
            padding: 15px; overflow-y: auto; max-height: 500px;
            color: #ccc; font-size: 0.95rem; line-height: 1.7; white-space: pre-wrap;
        }
        .desc-container h2, .desc-container h3 { color: var(--mu-gold); font-family: 'Cinzel', serif; margin-top: 15px; font-size: 1.3rem; }
        .desc-container ul { list-style: square; padding-left: 20px; }
        .desc-container img { max-width: 100%; height: auto; display: block; margin: 10px auto; border: 1px solid #444; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #111; }
        ::-webkit-scrollbar-thumb { background: #444; }
        ::-webkit-scrollbar-thumb:hover { background: var(--mu-gold); }
    </style>
</head>
<body>

<div class="container pt-4 pb-5">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none"><i class="fa-solid fa-house"></i> Trang Chủ</a></li>
            <li class="breadcrumb-item"><a href="/#result-list" class="text-decoration-none">Danh Sách Server</a></li>
            <li class="breadcrumb-item active text-warning" aria-current="page"><?= $server->serverName ?></li>
        </ol>
    </nav>

    <div class="row align-items-center mb-4">
        <div class="col-lg-4 text-center text-lg-start">
            <div class="server-header">
                <h1><?= $server->serverName ?></h1>
                <div class="server-mu-name"><?= $server->muName ?></div>
                <div>
                    <?php if ($server->bannerPackage == 'VIP' || $server->bannerPackage == 'SUPER_VIP'): ?>
                        <span class="status-badge" style="color: var(--mu-gold); border-color: var(--mu-gold);">
                            <i class="fa-solid fa-crown me-1"></i> VIP SERVER
                        </span>
                    <?php endif; ?>
                    <span class="status-badge" style="color: #aaa; border-color: #555;">
                        <i class="fa-solid fa-tag me-1"></i> <?= $server->slogan ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <?php if (!empty($server->bannerImage)): ?>
                <div class="server-banner-container">
                    <img src="<?= $server->bannerImage ?>" alt="Mu Online <?= $server->serverName ?> - <?= $server->muName ?>" class="server-banner-img">
                </div>
            <?php endif; ?>
        </div>
    </div>


    <div class="row g-4 align-items-start">
        
        <div class="col-lg-4">
            <div class="detail-panel">
                <div style="font-family: 'Cinzel', serif; color: var(--mu-gold); margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 5px;">
                    <i class="fa-solid fa-gears me-2"></i> Thông Tin Server
                </div>

                <div class="spec-list">
                    <div class="spec-item">
                        <span class="spec-label">Phiên bản</span>
                        <span class="spec-value text-white"><?= $server->stats->muVersionName ?></span>
                    </div>
                    
                    <div class="spec-item">
                        <span class="spec-label">Thể loại</span>
                        <span class="spec-value text-warning"><?= $server->serverTypeName ?></span>
                    </div>

                    <div class="spec-item">
                        <span class="spec-label">Exp Rate</span>
                        <span class="spec-value spec-highlight">x<?= $server->stats->expRate ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Drop Rate</span>
                        <span class="spec-value"><?= $server->stats->dropRate ?>%</span>
                    </div>
                </div>
                
                <div class="spec-item mb-2">
                    <span class="spec-label">Anti-Hack</span>
                    <span class="spec-value text-success"><?= $server->stats->antiHack ?></span>
                </div>

                <div class="spec-item mb-2">
                    <span class="spec-label">Kiểu Reset</span>
                    <span class="spec-value">
                        <?= !empty($server->stats->resetTypeName) ? $server->stats->resetTypeName : 'Reset' ?>
                    </span>
                </div>
                <div class="spec-item mb-4">
                    <span class="spec-label">Point / Level</span>
                    <span class="spec-value">
                         <?= !empty($server->stats->pointTypeName) ? $server->stats->pointTypeName : '5/7' ?>
                    </span>
                </div>

                <div class="mt-auto">
                    <?php if (!empty($server->websiteUrl)): ?>
                        <a href="<?= $server->websiteUrl ?>" target="_blank" rel="nofollow" class="btn-mu-main btn-mu-sub">
                            <i class="fa-solid fa-globe me-2"></i> Trang Chủ Game
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($server->fanpageUrl)): ?>
                        <a href="<?= $server->fanpageUrl ?>" target="_blank" rel="nofollow" class="btn-mu-main">
                            <i class="fa-brands fa-facebook me-2"></i> Fanpage
                        </a>
                    <?php endif; ?>

                    <a href="/" class="btn btn-sm btn-outline-secondary w-100 mt-2 border-0">
                        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="detail-panel">
                
                <div class="schedule-row">
                    <div class="schedule-item sch-alpha">
                        <div class="schedule-title">Alpha Test</div>
                        <?php if (!empty($server->schedule->alphaDate)): ?>
                            <div class="schedule-time"><?= date('H:i', strtotime($server->schedule->alphaTime ?? '00:00')) ?></div>
                            <time class="schedule-date" datetime="<?= $server->schedule->alphaDate ?>">
                                <?= date('d/m/Y', strtotime($server->schedule->alphaDate)) ?>
                            </time>
                        <?php else: ?>
                            <div class="schedule-date text-muted mt-2">Chưa cập nhật</div>
                        <?php endif; ?>
                    </div>

                    <div class="schedule-item sch-beta">
                        <div class="schedule-title"><i class="fa-solid fa-fire me-1"></i> Open Beta</div>
                        <?php if (!empty($server->schedule->betaDate)): ?>
                            <div class="schedule-time"><?= date('H:i', strtotime($server->schedule->betaTime ?? '00:00')) ?></div>
                            <time class="schedule-date" datetime="<?= $server->schedule->betaDate ?>">
                                <?= date('d/m/Y', strtotime($server->schedule->betaDate)) ?>
                            </time>
                        <?php else: ?>
                            <div class="schedule-time" style="font-size: 1.2rem; margin-top: 5px;">Coming Soon</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="font-family: 'Cinzel', serif; color: #fff; margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 5px;">
                    <i class="fa-solid fa-scroll me-2 text-warning"></i> Giới Thiệu Server
                </div>

                <div class="desc-container">
                    <?= $server->description ?>
                </div>

            </div>
        </div>
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>