<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thuê Quảng Cáo | MuNoria.mobile Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        .pb-5 {
    padding-bottom: 0.5rem !important;
}
        .mu-info-container {
    max-width: 1300px;   /* Giới hạn chiều ngang */
    width: 100%;
    margin: 0 auto;     /* Căn giữa */
}

        /* === GIỮ NGUYÊN 100% CSS CỦA BẠN === */
        .mu-info-container { background: rgba(10, 5, 5, 0.8); border: 1px solid #3d2b1f; box-shadow: 0 0 30px rgba(0,0,0,0.5); backdrop-filter: blur(10px); }
        .mu-tabs { border-bottom: 1px solid #3d2b1f; padding: 0 1rem; }
        .mu-tabs .nav-link { background: transparent; color: #888; font-family: 'Cinzel', serif; font-weight: 700; border: none; border-bottom: 3px solid transparent; padding: 1rem 1.5rem; transition: all 0.3s; text-transform: uppercase; }
        .mu-tabs .nav-link:hover { color: #e0e0e0; }
        .mu-tabs .nav-link.active { background: transparent; color: var(--mu-gold); border-bottom-color: var(--mu-gold); text-shadow: 0 0 10px rgba(255, 204, 0, 0.4); }
        .tab-pane-content { padding: 2rem; font-family: 'Rajdhani', sans-serif; color: #ccc; font-size: 1.05rem; line-height: 1.6; }
        .stat-card { background: linear-gradient(180deg, #1a0505 0%, #000 100%); border: 1px solid #3d2b1f; padding: 1.5rem; text-align: center; height: 100%; transition: transform 0.3s; }
        .stat-card:hover { border-color: var(--mu-gold-dark); transform: translateY(-5px); }
        .stat-num { font-family: 'Cinzel', serif; font-size: 2rem; color: var(--mu-gold); font-weight: 900; display: block; margin-bottom: 0.5rem; }
        .mu-spec-table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.95rem; }
        .mu-spec-table th { text-align: left; padding: 12px; color: var(--mu-text-gold); font-family: 'Cinzel', serif; border-bottom: 2px solid #3d2b1f; background: rgba(255, 204, 0, 0.05); }
        .mu-spec-table td { padding: 12px; border-bottom: 1px solid #222; color: #bbb; }
        .mu-spec-table tr:hover td { background: rgba(255, 255, 255, 0.02); color: #fff; }
        .algo-box { background: rgba(0, 50, 0, 0.2); border-left: 3px solid #00ff00; padding: 1rem; margin-top: 1rem; font-size: 0.9rem; }
        .algo-title { color: #00ff00; font-weight: bold; text-transform: uppercase; margin-bottom: 0.5rem; display: block; }
        :root { --mu-bg: #050505; --mu-panel-bg: rgba(15, 10, 10, 0.9); --mu-gold: #ffcc00; --mu-gold-dark: #b8860b; --mu-text-gold: #deb887; --mu-red: #ff0000; --mu-red-dark: #550000; --mu-border: #3d2b1f; --mu-glow-gold: 0 0 20px rgba(255, 204, 0, 0.3); --mu-glow-red: 0 0 25px rgba(255, 0, 0, 0.5); }
        body { background-color: var(--mu-bg); background-image: radial-gradient(circle at 50% 30%, #2a0505 0%, #000000 70%); background-attachment: fixed; font-family: 'Rajdhani', sans-serif; color: #ccc; min-height: 100vh; }
        .page-header h3 { font-family: 'Cinzel', serif; font-weight: 900; text-transform: uppercase; background: linear-gradient(180deg, #fff 10%, #ffcc00 50%, #b8860b 90%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 0 0 30px rgba(255, 204, 0, 0.3); letter-spacing: 2px; margin-bottom: 5px; }
        .page-header p { font-family: 'Cinzel', serif; letter-spacing: 3px; color: #888; font-size: 0.85rem; border-bottom: 1px solid #333; display: inline-block; padding-bottom: 10px; }
        .user-balance-box { background: linear-gradient(90deg, rgba(0,0,0,0) 0%, rgba(255, 204, 0, 0.1) 50%, rgba(0,0,0,0) 100%); border-top: 1px solid #333; border-bottom: 1px solid #333; padding: 10px 0; margin-bottom: 20px; text-align: center; }
        .coin-amount { font-family: 'Cinzel', serif; color: var(--mu-gold); font-size: 1.2rem; font-weight: bold; text-shadow: 0 0 10px rgba(255, 204, 0, 0.4); }
        .banner-slot { background: var(--mu-panel-bg); border: 1px solid var(--mu-border); padding: 20px; position: relative; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease-in-out; backdrop-filter: blur(5px); clip-path: polygon( 20px 0, 100% 0, 100% calc(100% - 20px), calc(100% - 20px) 100%, 0 100%, 0 20px ); box-shadow: 0 10px 30px rgba(0,0,0,0.8); }
        .banner-slot:hover { transform: translateY(-5px); border-color: var(--mu-gold); box-shadow: var(--mu-glow-gold); z-index: 10; }
        .banner-slot::before { content: ''; position: absolute; top: 0; left: 0; width: 15px; height: 15px; border-top: 2px solid #555; border-left: 2px solid #555; opacity: 0.5; }
        .banner-slot:hover::before { border-color: var(--mu-gold); }
        .slot-title { font-family: 'Cinzel', serif; font-weight: 700; color: #e0e0e0; font-size: 1.25rem; text-transform: uppercase; border-bottom: 1px solid #444; padding-bottom: 8px; margin-bottom: 15px; text-align: center; }
        .banner-slot:hover .slot-title { color: var(--mu-gold); border-color: var(--mu-gold-dark); }
        .slot-info { font-size: 0.95rem; color: #888; margin-bottom: 8px; }
        .slot-size { color: var(--mu-text-gold); font-weight: 600; font-family: 'Rajdhani', sans-serif; letter-spacing: 1px; }
        .qty-badge { background: #222; border: 1px solid #444; color: #fff; padding: 2px 8px; font-size: 0.8rem; vertical-align: middle; }
        .slot-price-tag { font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 1.1rem; color: #fff; background: rgba(0,0,0,0.6); border: 1px solid #444; padding: 5px 10px; display: inline-block; margin-bottom: 15px; border-radius: 4px; }
        .slot-price-tag i { color: var(--mu-gold); margin-right: 5px; }
        .slot-status-label { font-size: 0.75rem; text-transform: uppercase; color: #666; letter-spacing: 1px; text-align: center; }
        .slot-status-text { font-family: 'Cinzel', serif; font-size: 1.1rem; font-weight: bold; margin-bottom: 10px; text-align: center; }
        .status-available { color: #00ff00; text-shadow: 0 0 8px rgba(0,255,0,0.4); }
        .status-full { color: #ffaa00; text-shadow: 0 0 8px rgba(255,170,0,0.4); }
        .status-vip { color: #ff3333; text-shadow: 0 0 10px rgba(255,51,51,0.6); }
        .slot-vip { background: linear-gradient(180deg, rgba(40,0,0,0.8) 0%, rgba(10,0,0,0.9) 100%); border-color: var(--mu-red-dark); }
        .slot-vip .slot-title { color: var(--mu-red); font-size: 1.5rem; text-shadow: 0 0 10px rgba(139,0,0,0.5); }
        .slot-vip:hover { border-color: var(--mu-red); box-shadow: var(--mu-glow-red); }
        .slot-vip:hover .slot-title { color: #ff5555; text-shadow: 0 0 15px red; }
        .btn-mu-action { background: linear-gradient(90deg, #550000 0%, #8b0000 100%); color: #e0e0e0; border: 1px solid #a00000; font-family: 'Cinzel', serif; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; transition: all 0.3s; clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px); }
        .btn-mu-action:hover { background: linear-gradient(90deg, #8b0000 0%, #ff0000 100%); color: #fff; box-shadow: 0 0 15px rgba(255,0,0,0.6); border-color: #ff3333; }
        .btn-mu-waitlist { background: linear-gradient(90deg, #0f2027 0%, #203a43 100%); border: 1px solid #444; color: #ccc; font-family: 'Cinzel', serif; text-transform: uppercase; clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px); }
        .btn-mu-waitlist:hover { background: #2c5364; color: #fff; border-color: #666; box-shadow: 0 0 15px rgba(44, 83, 100, 0.5); }
        .btn-mu-login { border: 1px dashed #555; color: #888; text-transform: uppercase; font-size: 0.85rem; }
        .btn-mu-login:hover { border-color: var(--mu-gold); color: var(--mu-gold); }
        .vip-nav-box { display: block; position: relative; text-decoration: none; background: linear-gradient(90deg, rgba(20,0,0,0.9) 0%, rgba(50,0,0,0.9) 50%, rgba(20,0,0,0.9) 100%); border: 1px solid var(--mu-border); border-top: 1px solid #7a5c00; border-bottom: 1px solid #7a5c00; padding: 15px 25px; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); overflow: hidden; clip-path: polygon(15px 0, 100% 0, 100% calc(100% - 15px), calc(100% - 15px) 100%, 0 100%, 0 15px); }
        .vip-nav-box:hover { transform: translateY(-3px) scale(1.01); border-color: var(--mu-gold); box-shadow: 0 0 25px rgba(255, 204, 0, 0.2); background: linear-gradient(90deg, #2b0505 0%, #4a0a0a 50%, #2b0505 100%); }
        .vip-nav-box::after { content: ''; position: absolute; top: 0; left: -120%; width: 60%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.4), transparent); transform: skewX(-25deg); transition: none; }
        .vip-nav-box:hover::after { left: 120%; transition: all 0.7s ease-in-out; }
        .vip-nav-title { font-family: 'Cinzel', serif; font-weight: 900; font-size: 1.3rem; color: var(--mu-gold); text-transform: uppercase; letter-spacing: 1px; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
        .vip-nav-desc { font-family: 'Rajdhani', sans-serif; color: #aaa; font-size: 0.95rem; margin-top: 2px; }
        .vip-nav-icon { font-size: 1.8rem; background: -webkit-linear-gradient(#fff, #ffcc00); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; filter: drop-shadow(0 0 5px rgba(255, 204, 0, 0.5)); }
        .vip-nav-arrow { color: var(--mu-red); transition: margin-right 0.3s; }
        .vip-nav-box:hover .vip-nav-arrow { margin-right: -5px; color: var(--mu-gold); }
        .marketing-section { margin-top: 5rem; padding: 3rem; background: rgba(0, 0, 0, 0.6); border: 1px solid var(--mu-gold-dark); position: relative; }
        .marketing-section::after { content: ''; position: absolute; top: -1px; left: -1px; width: 30px; height: 30px; border-top: 3px solid var(--mu-gold); border-left: 3px solid var(--mu-gold); }
        .marketing-section::before { content: ''; position: absolute; bottom: -1px; right: -1px; width: 30px; height: 30px; border-bottom: 3px solid var(--mu-gold); border-right: 3px solid var(--mu-gold); }
        .marketing-title { color: var(--mu-gold); font-family: 'Cinzel', serif; text-align: center; font-weight: 700; text-shadow: 0 0 10px rgba(184, 134, 11, 0.3); margin-bottom: 2rem; }
        .marketing-subtitle { font-family: 'Cinzel', serif; border-left: 4px solid var(--mu-red); padding-left: 15px; margin-top: 30px; margin-bottom: 15px; }
        .marketing-list li { margin-bottom: 10px; font-size: 1.05rem; }
        .highlight-text { color: var(--mu-text-gold); font-weight: bold; }
        .modal-content { background: #0f0f0f; border: 1px solid var(--mu-gold-dark); box-shadow: 0 0 50px rgba(0,0,0,0.9); }
        .modal-custom-header { background: linear-gradient(90deg, #330000, #110000); border-bottom: 1px solid #333; }
        .form-control { background: #1a1a1a; border: 1px solid #333; color: #eee; }
        .form-control:focus { background: #222; border-color: var(--mu-gold); color: #fff; box-shadow: none; }
        .modal-price-box { background: rgba(0,0,0,0.5); border: 1px dashed #555; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .price-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9rem; }
        .price-row.total { border-top: 1px solid #444; padding-top: 8px; margin-top: 8px; font-weight: bold; font-size: 1rem; color: #fff; }
        .text-coin { color: var(--mu-gold); }
        .btn-locked { background-color: #343a40 !important; border-color: #454d55 !important; color: #adb5bd !important; cursor: not-allowed; opacity: 0.8; }
        .next-available-text { color: #ffc107; font-size: 0.8rem; font-weight: bold; margin-top: 8px; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 0.8; } 50% { opacity: 1; text-shadow: 0 0 5px #ffc107; } 100% { opacity: 0.8; } }
    </style>
</head>
<body>

<div class="page-wrapper pt-5 pb-5">
    <div class="container">

        <div class="page-header text-center">
            <h3><i class="fa-solid fa-dragon me-2"></i> Bảng Giá & Đăng Ký Quảng Cáo</h3>
            <p>Khẳng định đẳng cấp Server - Tiếp cận cộng đồng Game thủ</p>
        </div>

        <?php if (!empty($currentUser)): ?>
            <div class="user-balance-box">
                <span class="text-secondary text-uppercase small me-2">Số dư của bạn:</span>
                <span class="coin-amount">
                    <i class="fa-solid fa-coins"></i>
                    <?= number_format($currentUser['coin']) ?> Coin
                </span>
                <a href="index.php?url=nap-tien"
                   class="btn btn-sm btn-outline-warning text-uppercase fw-bold"
                   style="border: 1px solid var(--mu-gold); color: var(--mu-gold);">
                    <i class="fa-solid fa-plus"></i> Nạp thêm
                </a>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success bg-dark border-success text-success text-center mb-4">
                <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger bg-dark border-danger text-danger text-center mb-4">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <div class="row g-4 align-items-stretch">

            <div class="col-md-3 d-flex align-items-center">
                <div class="banner-slot w-100" style="height: 380px;">
                    <div>
                        <div class="slot-title">Banner Trái</div>
                        <div class="text-center px-2">
                            <div class="slot-info mt-4"> <i class="bi bi-aspect-ratio me-1"></i> <span class="slot-size">280 x 500 px</span>
                            </div>
                            <div class="slot-info">
                                Đã đặt: <span class="qty-badge"><?= $qtyInfo['LEFT_SIDEBAR'] ?></span>
                            </div>

                            <div class="slot-status-label mt-3">Tình trạng</div>
                            <div class="slot-status-text <?= $isFullLeft ? 'status-full' : 'status-available' ?>">
                                <?= $availability['LEFT_SIDEBAR'] ?>
                            </div>

                            <?php if ($isFullLeft): ?>
                                <div class="mt-2">
                                    <small class="text-secondary" style="font-size: 0.75rem">Trống sau:</small>
                                    <div class="countdown-timer fw-bold text-warning" style="font-size: 0.9rem"
                                         data-countdown="<?= $nextAvailableMap['LEFT_SIDEBAR'] ?>">
                                        Calculating...
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="w-100 mt-4 px-2">
                        <a href="https://zalo.me/SDT_CUA_BAN" target="_blank" class="btn btn-outline-warning w-100 py-2 fw-bold" style="border: 1px solid var(--mu-gold); color: var(--mu-gold);">
                            <i class="fa-solid fa-comments me-2"></i> LIÊN HỆ ADMIN
                        </a>
                        <div class="text-center mt-2">
                            <small class="text-secondary" style="font-size: 0.75rem">Liên hệ để đặt quảng cáo vị trí này</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="d-flex flex-column h-100 gap-4">

                    <div class="banner-slot slot-vip flex-fill">
                        <div class="position-absolute top-0 end-0 p-3 text-danger" style="filter: drop-shadow(0 0 5px red);"><i class="fa-solid fa-crown fa-2x"></i></div>

                        <div>
                            <div class="slot-title">BANNER GIỮA (VIP)</div>
                            <div class="text-center px-4">
                                <div class="slot-info text-light mt-4">
                                    <i class="bi bi-aspect-ratio me-1"></i> <span class="slot-size text-warning" style="font-size: 1.1rem">1200 x 250 px</span>
                                </div>
                                <div class="slot-info">
                                    Đã đặt: <span class="qty-badge bg-danger border-danger"><?= $qtyInfo['HERO'] ?></span>
                                </div>

                                <div class="slot-status-label mt-3 text-warning">Tình trạng</div>
                                <div class="slot-status-text <?= $isFullHero ? 'status-full' : 'status-vip' ?>">
                                    <?= $availability['HERO'] ?>
                                </div>

                                <?php if ($isFullHero): ?>
                                    <div class="mt-2">
                                        <small class="text-secondary">Trống sau:</small>
                                        <div class="countdown-timer fw-bold text-warning"
                                             data-countdown="<?= $nextAvailableMap['HERO'] ?>">
                                            Calculating...
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="w-75 mx-auto mt-3">
                            <a href="https://zalo.me/0372229527_blank" class="btn btn-mu-action w-100 py-3 fs-5">
                                <i class="fa-solid fa-phone-volume me-2"></i> LIÊN HỆ ĐẶT VIP
                            </a>
                            <div class="text-center mt-2">
                                <small class="text-secondary">Vui lòng inbox trực tiếp BQT</small>
                            </div>
                        </div>
                    </div>

                    <div class="banner-slot flex-fill" style="min-height: auto;">
                        <div class="d-flex align-items-center justify-content-between w-100 px-2">
                            <div class="text-start">
                                <div class="slot-title border-0 mb-0 text-start" style="font-size: 1.1rem">Banner Giữa (Nhỏ)</div>
                                <div class="text-warning small mb-1 fw-bold">
                                    <i class="fa-solid fa-tag"></i> <?= number_format($prices['STD']) ?> Coin
                                </div>
                                <div class="slot-size mb-1">1200 x 120 px</div>
                                <div class="slot-info mb-0" style="font-size: 0.9rem;">
                                    Đã đặt: <span class="qty-badge"><?= $qtyInfo['STD'] ?></span>
                                </div>
                            </div>

                            <div class="text-end">
                                <div class="slot-status-text mb-1 <?= $isFullStd ? 'status-full' : 'status-available' ?>" style="font-size: 1rem">
                                    <?= $availability['STD'] ?>
                                </div>
                                
                                <?php if (!empty($currentUser) && !$isFullStd): ?>
                                    <button class="btn btn-mu-action btn-sm px-4"
                                            onclick="openRegisterModal('STD', 'Banner Giữa Nhỏ', <?= $prices['STD'] ?>)">
                                        Thuê Ngay
                                    </button>
                                <?php elseif (!empty($currentUser) && $isFullStd): ?>
                                    <button class="btn btn-locked btn-sm px-3" disabled>
                                        <i class="fa-solid fa-lock"></i> Khóa
                                    </button>
                                    <div class="text-center mt-2">
                                        <small class="text-secondary">Trống sau:</small>
                                        <div class="countdown-timer fw-bold text-warning fs-6"
                                             data-countdown="<?= $nextAvailableMap['STD'] ?>">
                                            Calculating...
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <a href="index.php?url=login" class="btn btn-mu-login btn-sm px-3">Đăng nhập</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <a href="index.php?url=server-register" class="vip-nav-box">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="me-3">
                                <i class="fa-solid fa-gem vip-nav-icon"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="vip-nav-title">
                                    ĐĂNG MU VIP TẠI ĐÂY
                                    <span class="badge bg-danger text-white border border-warning ms-2" style="font-size: 0.6rem; vertical-align: middle;">HOT</span>
                                </div>
                                <div class="vip-nav-desc">
                                    <i class="bi bi-stars text-warning me-1"></i> Tổng hợp các máy chủ Alpha Test & Open Beta đáng chơi nhất
                                </div>
                            </div>
                            <div class="ms-3">
                                <i class="fa-solid fa-chevron-right fa-lg vip-nav-arrow"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3 d-flex align-items-center">
                <div class="banner-slot w-100" style="height: 380px;">
                    <div>
                        <div class="slot-title">Banner Phải</div>
                        <div class="text-center px-2">
                            <div class="slot-info mt-4">
                                <i class="bi bi-aspect-ratio me-1"></i> <span class="slot-size">280 x 500 px</span>
                            </div>
                            <div class="slot-info">
                                Đã đặt: <span class="qty-badge"><?= $qtyInfo['RIGHT_SIDEBAR'] ?></span>
                            </div>

                            <div class="slot-status-label mt-3">Tình trạng</div>
                            <div class="slot-status-text <?= $isFullRight ? 'status-full' : 'status-available' ?>">
                                <?= $availability['RIGHT_SIDEBAR'] ?>
                            </div>

                            <?php if ($isFullRight): ?>
                                <div class="mt-2">
                                    <small class="text-secondary" style="font-size: 0.75rem">Trống sau:</small>
                                    <div class="countdown-timer fw-bold text-warning" style="font-size: 0.9rem"
                                         data-countdown="<?= $nextAvailableMap['RIGHT_SIDEBAR'] ?>">
                                        Calculating...
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="w-100 mt-4 px-2">
                        <a href="https://zalo.me/SDT_CUA_BAN" target="_blank" class="btn btn-outline-warning w-100 py-2 fw-bold" style="border: 1px solid var(--mu-gold); color: var(--mu-gold);">
                            <i class="fa-solid fa-comments me-2"></i> LIÊN HỆ ADMIN
                        </a>
                        <div class="text-center mt-2">
                            <small class="text-secondary" style="font-size: 0.75rem">Liên hệ để đặt quảng cáo vị trí này</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header modal-custom-header" id="modalHeaderBg">
                <h5 class="modal-title fw-bold text-warning" id="modalTitle">ĐĂNG KÝ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="index.php?url=banner-register-action" method="post" enctype="multipart/form-data" id="bannerForm">
                    <input type="hidden" name="positionCode" id="hiddenPosCode">
                    <div class="modal-price-box">
                        <div class="price-row">
                            <span class="text-secondary">Số dư hiện tại:</span>
                            <span class="text-white" id="modalCurrentBalance">0</span>
                        </div>
                        <div class="price-row">
                            <span class="text-secondary">Chi phí:</span>
                            <span class="text-danger" id="modalPrice">- 0</span>
                        </div>
                        <div class="price-row total">
                            <span>Số dư sau mua:</span>
                            <span id="modalRemaining" class="text-coin">0</span>
                        </div>
                        <div id="insufficientFundsMsg" class="text-danger text-center mt-2 small d-none fw-bold">
                            <i class="bi bi-x-circle"></i> Số dư không đủ! Vui lòng nạp thêm.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase fw-bold">Chọn nguồn ảnh</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="uploadType" id="optUpload" value="file" checked onchange="toggleImageInput()">
                                <label class="form-check-label text-white" for="optUpload">
                                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải ảnh lên
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="uploadType" id="optLink" value="url" onchange="toggleImageInput()">
                                <label class="form-check-label text-white" for="optLink">
                                    <i class="fa-solid fa-link me-1"></i> Dùng Link ảnh
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="groupFile">
                        <label class="form-label text-secondary small text-uppercase fw-bold">Chọn file (Ảnh/Gif)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-file-image"></i></span>
                            <input type="file" name="imageFile" id="inputImageFile" class="form-control" accept="image/png, image/jpeg, image/gif" required>
                        </div>
                        <div class="form-text text-muted small">Hỗ trợ: JPG, PNG, GIF (Tối đa 5MB)</div>
                    </div>
                    <div class="mb-3 d-none" id="groupUrl">
                        <label class="form-label text-secondary small text-uppercase fw-bold">Dán Link Ảnh</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-globe"></i></span>
                            <input type="url" name="imageUrl" id="inputImageUrl" class="form-control" placeholder="https://imgur.com/example.gif">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small text-uppercase fw-bold">Link Đích (Target URL)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-link"></i></span>
                            <input type="url" name="targetUrl" class="form-control" placeholder="https://mu-server-cuaban.com" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-mu-action w-100 py-2" id="btnSubmit">
                        <i class="fa-solid fa-scroll me-2"></i> THANH TOÁN & ĐĂNG KÝ
                    </button>

                    <a href="index.php?url=nap-tien" id="btnRecharge" class="btn btn-outline-warning w-100 py-2 d-none">
                        <i class="fa-solid fa-coins me-2"></i> NẠP TIỀN NGAY
                    </a>
                </form>
            </div>
        </div>
        
    </div>
    
</div>
 <div class="row mt-5">
            <div class="col-12">
                <div class="mu-info-container rounded">
                    <ul class="nav nav-tabs mu-tabs" id="infoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                                <i class="fa-solid fa-chart-line me-2"></i>Tổng Quan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="banner-tab" data-bs-toggle="tab" data-bs-target="#banner" type="button" role="tab">
                                <i class="fa-regular fa-image me-2"></i>Thông Số Banner
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="vip-tab" data-bs-toggle="tab" data-bs-target="#vip" type="button" role="tab">
                                <i class="fa-solid fa-crown me-2"></i>Cơ Chế VIP
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ads-tab" data-bs-toggle="tab" data-bs-target="#ads" type="button" role="tab">
                                <i class="fa-brands fa-facebook me-2"></i>Facebook Ads
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">

                        <div class="tab-pane fade show active tab-pane-content" id="overview" role="tabpanel">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <i class="fa-solid fa-users-gear text-secondary fa-2x mb-3"></i>
                                        <span class="stat-num">6.000+</span>
                                        <div class="text-uppercase text-secondary small fw-bold">Admin Tin Tưởng</div>
                                        <div class="text-white mt-2 small">Đã sử dụng dịch vụ quảng cáo</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <i class="fa-solid fa-gamepad text-secondary fa-2x mb-3"></i>
                                        <span class="stat-num">16.800+</span>
                                        <div class="text-uppercase text-secondary small fw-bold">Game Mu Online</div>
                                        <div class="text-white mt-2 small">Đã đăng bài giới thiệu chi tiết</div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center my-5">
                                <h4 class="marketing-title" style="font-size: 1.5rem; margin-bottom: 1rem;">HIỆU QUẢ QUẢNG CÁO TỐI ĐA</h4>
                                <div class="p-4 fst-italic position-relative" style="background: rgba(255,0,0,0.05); border: 1px dashed var(--mu-red-dark); display: inline-block;">
                                    <i class="fa-solid fa-quote-left text-danger position-absolute top-0 start-0 m-2"></i>
                                    <div class="px-4">
                                        <p class="mb-1 text-light">"Ngay cả khi... Mu bạn mới không ai tới?"</p>
                                        <p class="mb-1 text-light">"Ngay cả khi... Mu bạn cũ bị gamer phũ?"</p>
                                        <p class="mb-0 fw-bold text-warning">"Ngay cả khi... Mu chà bá, cần bứt phá?"</p>
                                    </div>
                                    <i class="fa-solid fa-quote-right text-danger position-absolute bottom-0 end-0 m-2"></i>
                                </div>
                                <p class="mt-4 text-secondary">
                                    Chúng tôi là nơi tập hợp cộng đồng đam mê MU Online lớn nhất. Game thủ truy cập hàng ngày để tìm kiếm Server mới ra.<br>
                                    Mọi quảng cáo đều được đo lường tự động bằng <strong>bitly.com</strong> để đảm bảo minh bạch.
                                </p>
                            </div>
                        </div>

                        <div class="tab-pane fade tab-pane-content" id="banner" role="tabpanel">
                            <h5 class="text-warning font-cinzel mb-3"><i class="fa-solid fa-tag me-2"></i>DỊCH VỤ TREO BANNER</h5>
                            <p>Hiển thị ở tất cả các trang và mục hướng dẫn chơi game. Hỗ trợ ảnh động, trailer Youtube, Canvas 3D HTML5.</p>

                            <div class="table-responsive">
                                <table class="mu-spec-table">
                                    <thead>
                                    <tr>
                                        <th>Loại Banner</th>
                                        <th>Kích thước (px)</th>
                                        <th>Số lượng Slot</th>
                                        <th>Định dạng hỗ trợ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="fw-bold text-white">Banner Giữa To (VIP)</td>
                                        <td><span class="badge bg-danger">780 x 280</span></td>
                                        <td>01 (Độc quyền)</td>
                                        <td>Ảnh động, Youtube, HTML5</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-light">Banner Giữa (Nhỏ)</td>
                                        <td>1200 x 120 <small class="text-secondary">(hoặc 780x110)</small></td>
                                        <td>08</td>
                                        <td>Ảnh động, HTML5</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-light">Banner Trái</td>
                                        <td>280 x 500 <small class="text-secondary">(hoặc 210x400)</small></td>
                                        <td>06</td>
                                        <td>Ảnh động, HTML5</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-light">Banner Phải</td>
                                        <td>280 x 500 <small class="text-secondary">(hoặc 210x400)</small></td>
                                        <td>06</td>
                                        <td>Ảnh động, HTML5</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 text-secondary small">
                                <i class="fa-solid fa-circle-info me-1 text-warning"></i>
                                Lưu ý: Dung lượng banner không quá 2MB. Hệ thống tự động hiển thị sau 30 phút đăng ký.
                                (nếu banner không đúng kích thước, hệ thống sẽ tự điều chỉnh để khớp với kích thước mẫu)
                            </div>
                        </div>

                        <div class="tab-pane fade tab-pane-content" id="vip" role="tabpanel">
                            <h5 class="text-danger font-cinzel mb-3"><i class="fa-solid fa-crown me-2"></i>DỊCH VỤ GHIM BÀI VIP</h5>
                            <p>Bài viết của bạn được <strong>PIN (Ghim)</strong> lên trang đầu tiên tại:</p>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <ul class="marketing-list fa-ul text-secondary">
                                        <li><span class="fa-li"><i class="fa-solid fa-check text-success"></i></span>Trang chủ Mu mới ra</li>
                                        <li><span class="fa-li"><i class="fa-solid fa-check text-success"></i></span>Trang tìm kiếm Alpha Test / Open Beta hôm nay</li>
                                        <li><span class="fa-li"><i class="fa-solid fa-check text-success"></i></span>Trang lọc Season (SS2, SS6, SS16, Non-Reset...)</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border border-secondary rounded bg-dark">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Kích thước ảnh VIP:</span>
                                            <span class="text-warning fw-bold">780 x 110 px</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Giới hạn slot:</span>
                                            <span class="text-white fw-bold">20 vị trí</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Dung lượng tối đa:</span>
                                            <span class="text-white fw-bold">200 Kb</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="algo-box">
                                <span class="algo-title"><i class="fa-solid fa-calculator me-2"></i>Cơ Chế Chia Sẻ Hiển Thị (Smart Rotation)</span>
                                <p class="mb-2">Khi người dùng xem chi tiết một MU khác, hệ thống sẽ đề xuất 2 vị trí VIP liên quan. Tỷ lệ hiển thị của bạn phụ thuộc vào độ cạnh tranh của phiên bản đó:</p>

                                <div class="ps-3 border-start border-success my-3">
                                    <p class="mb-1 text-light"><small>VÍ DỤ 1 (Cạnh tranh cao):</small></p>
                                    <p class="mb-0 text-secondary fst-italic">Bạn chạy SS6.3, có 10 Admin khác cũng chạy VIP SS6.<br>
                                        ➜ Tỷ lệ hiển thị = 2 vị trí / 10 Server = <strong>20%</strong>.</p>
                                </div>

                                <div class="ps-3 border-start border-success">
                                    <p class="mb-1 text-light"><small>VÍ DỤ 2 (Độc quyền):</small></p>
                                    <p class="mb-0 text-secondary fst-italic">Bạn chạy SS2, chỉ có 1 Admin khác chạy VIP SS2.<br>
                                        ➜ Tỷ lệ hiển thị = 2 vị trí / 2 Server = <strong>100% (Luôn hiển thị)</strong>.</p>
                                </div>

                                <div class="mt-3 small text-secondary">
                                    * Hệ thống tự động nhóm các phiên bản liên quan (Ví dụ: SS1, SS2, SS3, SS4... được tính chung là nhóm SS thấp).
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade tab-pane-content" id="ads" role="tabpanel">
                            <div class="text-center py-5">
                                <i class="fa-brands fa-facebook fa-4x text-primary mb-3"></i>
                                <h4 class="text-white font-cinzel">CHẠY QUẢNG CÁO FACEBOOK ADS</h4>
                                <p class="text-secondary mx-auto" style="max-width: 600px;">
                                    Sử dụng tệp khách hàng <strong>Re-marketing</strong> chất lượng cao từ những game thủ đã truy cập website.
                                    Giúp quảng cáo của bạn tiếp cận đúng đối tượng đam mê Mu Online, tối ưu chi phí chuyển đổi.
                                </p>
                                <span class="badge bg-secondary border border-secondary mt-3 p-2 px-3">
                            <i class="fa-solid fa-flask me-1"></i> TÍNH NĂNG ĐANG THỬ NGHIỆM
                        </span>
                                <p class="mt-2 text-muted small">Chúng tôi sẽ sớm cập nhật thông tin chi tiết.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php 
// include 'footer.php'; 
?>

<script>
    // Hàm chạy đếm ngược
    function startCountdowns() {
        const timers = document.querySelectorAll('[data-countdown]');

        timers.forEach(timer => {
            const dateString = timer.getAttribute('data-countdown');
            if (!dateString || dateString === 'null') {
                timer.innerHTML = "Liên hệ Admin";
                return;
            }

            // Chuyển chuỗi ISO từ PHP thành đối tượng Date của JS
            const countDownDate = new Date(dateString).getTime();
            const now = new Date().getTime();
            const distance = countDownDate - now;

            if (distance < 0) {
                // Nếu hết giờ đếm ngược -> Reload trang để hiện nút Mua
                timer.innerHTML = '<span class="text-success">Đã có slot! Vui lòng F5</span>';
                // Tùy chọn: location.reload();
                return;
            }

            // Tính toán thời gian
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Hiển thị kết quả đẹp
            let output = "";
            if (days > 0) output += days + "d : ";
            output += (hours < 10 ? "0" + hours : hours) + "h : ";
            output += (minutes < 10 ? "0" + minutes : minutes) + "m : ";
            output += (seconds < 10 ? "0" + seconds : seconds) + "s";

            timer.innerHTML = '<i class="bi bi-alarm"></i> ' + output;
        });
    }

    // Cập nhật mỗi 1 giây
    setInterval(startCountdowns, 1000);

    startCountdowns();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Chèn biến PHP vào JS
    const userCoin = <?= !empty($currentUser) && isset($currentUser['coin']) ? $currentUser['coin'] : 0 ?>;

    function toggleImageInput() {
        const isUpload = document.getElementById('optUpload').checked;
        const groupFile = document.getElementById('groupFile');
        const groupUrl = document.getElementById('groupUrl');
        const inputFile = document.getElementById('inputImageFile');
        const inputUrl = document.getElementById('inputImageUrl');

        if (isUpload) {
            groupFile.classList.remove('d-none');
            groupUrl.classList.add('d-none');
            inputFile.setAttribute('required', '');
            inputUrl.removeAttribute('required');
            inputUrl.value = '';
        } else {
            groupFile.classList.add('d-none');
            groupUrl.classList.remove('d-none');
            inputUrl.setAttribute('required', '');
            inputFile.removeAttribute('required');
            inputFile.value = '';
        }
    }

    function formatMoney(n) {
        if (!n) return '0';
        return n.toFixed(0).replace(/./g, function(c, i, a) {
            return i > 0 && c !== "." && (a.length - i) % 3 === 0 ? "." + c : c;
        });
    }

    // Đã xóa tham số isWaitlist vì không dùng nữa
    function openRegisterModal(code, name, price) {
        if (price === undefined || price === null) price = 0;

        document.getElementById('hiddenPosCode').value = code;

        var headerBg = document.getElementById('modalHeaderBg');
        var title = document.getElementById('modalTitle');
        var btnSubmit = document.getElementById('btnSubmit');
        var btnRecharge = document.getElementById('btnRecharge');
        var msgError = document.getElementById('insufficientFundsMsg');

        // Render thông tin
        document.getElementById('modalCurrentBalance').innerText = formatMoney(userCoin) + ' Coin';
        document.getElementById('modalPrice').innerText = '- ' + formatMoney(price) + ' Coin';

        var remaining = userCoin - price;
        document.getElementById('modalRemaining').innerText = formatMoney(remaining) + ' Coin';

        var isEnoughMoney = remaining >= 0;

        // Luôn hiển thị giao diện Mua ngay (vì nếu Full thì nút ở ngoài đã bị khóa rồi)
        headerBg.style.background = 'linear-gradient(90deg, #550000, #220000)';
        title.innerHTML = '<i class="fa-solid fa-bolt me-2"></i> THUÊ NGAY: ' + name;
        title.className = "modal-title fw-bold text-warning";

        if (!isEnoughMoney) {
            document.getElementById('modalRemaining').classList.remove('text-coin');
            document.getElementById('modalRemaining').classList.add('text-danger');

            btnSubmit.classList.add('d-none');
            btnRecharge.classList.remove('d-none');
            msgError.classList.remove('d-none');
        } else {
            document.getElementById('modalRemaining').classList.add('text-coin');
            document.getElementById('modalRemaining').classList.remove('text-danger');

            btnSubmit.classList.remove('d-none');
            btnRecharge.classList.add('d-none');
            msgError.classList.add('d-none');
        }

        var myModal = new bootstrap.Modal(document.getElementById('registerModal'));
        myModal.show();
    }
</script>
</body>
</html>