<?php
// --- 1. PHẦN XỬ LÝ LOGIC (BACKEND) ---
require_once '../config/Database.php';

$db = (new Database())->connect();

// Helper function để lấy dữ liệu danh mục
function getMasterData($db, $table) {
    $stmt = $db->prepare("SELECT * FROM $table ORDER BY sort_order ASC LIMIT 100"); 
    // Lưu ý: bảng point_types ko có sort_order nên query riêng nếu cần, ở đây mình select all cơ bản
    if($table == 'point_types') $stmt = $db->prepare("SELECT * FROM $table");
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 1. Lấy dữ liệu cho Dropdown
$versions   = getMasterData($db, 'mu_versions');
$resetTypes = getMasterData($db, 'reset_types'); // <-- Yêu cầu của bạn: Lấy Reset từ DB
$pointTypes = getMasterData($db, 'point_types');
$serverTypes = getMasterData($db, 'server_types');
// 2. Tính toán số lượng Slot VIP còn lại (Logic giống JSP mẫu)
// Giả định: 
// - Max Super VIP = 30
// - Max VIP = 8
// - Chỉ đếm các server đang APPROVED và còn hạn (is_active = 1)

$maxSuperVip = 30;
$maxVip = 8;

$stmtStats = $db->prepare("SELECT banner_package, COUNT(*) as total FROM servers WHERE status = 'APPROVED' AND is_active = 1 GROUP BY banner_package");
$stmtStats->execute();
$stats = $stmtStats->fetchAll(PDO::FETCH_KEY_PAIR); // Trả về dạng ['VIP' => 2, 'SUPER_VIP' => 5]

$usedVip = $stats['VIP'] ?? 0;
$usedSuperVip = $stats['SUPER_VIP'] ?? 0;

$remVip = $maxVip - $usedVip;
$remSvip = $maxSuperVip - $usedSuperVip;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khởi Tạo Máy Chủ | MUNORIA.MOBILE</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* === GIỮ NGUYÊN CSS TỪ FILE JSP CỦA BẠN === */
        :root {
            --mu-bg: #050505;
            --mu-gold: #cfaa56;
            --mu-red: #8b0000;
            --mu-border: #3d2b1f;
        }

        body {
            background-color: var(--mu-bg);
            color: #ccc;
            font-family: 'Rajdhani', sans-serif;
            background-image: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.95)), url('https://toquoc.mediacdn.vn/280518851207290880/2022/6/7/-1654571912757628297204.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* CARD STYLE */
        .create-server-card {
            background: rgba(10, 10, 10, 0.95);
            border: 1px solid var(--mu-border);
            border-top: 3px solid var(--mu-gold);
            box-shadow: 0 0 30px rgba(0,0,0,0.9), 0 0 10px rgba(207, 170, 86, 0.15);
            position: relative;
            margin-bottom: 40px;
            padding: 40px;
        }

        .create-server-card::before, .create-server-card::after {
            content: ''; position: absolute; width: 15px; height: 15px;
            border: 2px solid var(--mu-gold); transition: 0.3s;
        }
        .create-server-card::before { top: -2px; left: -2px; border-right: none; border-bottom: none; }
        .create-server-card::after { bottom: -2px; right: -2px; border-left: none; border-top: none; }

        .page-title {
            font-family: 'Cinzel', serif;
            font-weight: 700;
            color: var(--mu-gold);
            text-transform: uppercase;
            text-align: center;
            font-size: 2rem;
            margin-bottom: 10px;
            text-shadow: 0 0 10px rgba(207, 170, 86, 0.4);
        }
        .page-subtitle {
            text-align: center;
            color: #777;
            font-size: 0.9rem;
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* SECTION STYLES */
        .mu-section-title {
            font-family: 'Cinzel', serif;
            color: #e0e0e0;
            font-size: 1.2rem;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            margin-bottom: 25px;
            margin-top: 10px;
        }
        .mu-section-title i { color: var(--mu-gold); margin-right: 10px; }

        /* INPUT FIELDS */
        .mu-form-group { margin-bottom: 20px; position: relative; }

        .mu-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #aaa;
            margin-bottom: 6px;
            display: block;
            text-transform: uppercase;
        }

        .mu-input, .mu-select, .mu-textarea {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid #333;
            color: #fff;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1rem;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .mu-input { padding: 10px 15px 10px 40px; }
        .mu-select { padding: 10px 15px; cursor: pointer; }
        .mu-textarea { padding: 15px; }

        .mu-input:focus, .mu-select:focus, .mu-textarea:focus {
            background: rgba(0,0,0,0.6);
            border-color: var(--mu-gold);
            outline: none;
            box-shadow: 0 0 8px rgba(207, 170, 86, 0.25);
        }

        .mu-input-icon {
            position: absolute;
            left: 12px;
            bottom: 13px;
            color: #555;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .mu-input:focus ~ .mu-input-icon { color: var(--mu-gold); }
        .mu-select option { background-color: #111; color: #fff; padding: 10px; }
        input[type="date"], input[type="time"] { color-scheme: dark; }
        input[type="file"] { padding-left: 10px !important; }

        /* SCHEDULE BOX */
        .schedule-box {
            border: 1px dashed #333;
            background: rgba(0,0,0,0.2);
            padding: 15px;
            height: 100%;
            transition: 0.3s;
        }
        .schedule-box:hover { border-color: #555; background: rgba(0,0,0,0.4); }

        .sch-title { font-family: 'Cinzel', serif; font-weight: 700; margin-bottom: 10px; display: block; }
        .text-alpha { color: #00d2ff; text-shadow: 0 0 5px rgba(0, 210, 255, 0.4); }
        .text-beta { color: #ff4444; text-shadow: 0 0 5px rgba(255, 68, 68, 0.4); }

        /* VIP PACKAGE */
        .vip-package-box {
            border: 1px solid var(--mu-gold);
            background: linear-gradient(45deg, rgba(207, 170, 86, 0.05) 0%, rgba(0,0,0,0) 100%);
            padding: 20px;
            position: relative;
        }
        .vip-header {
            color: var(--mu-gold);
            font-family: 'Cinzel', serif;
            font-weight: 700;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(207, 170, 86, 0.2);
            padding-bottom: 5px;
        }

        /* BUTTON */
        .btn-mu-create {
            background: linear-gradient(180deg, #b91c1c 0%, #7f1d1d 100%);
            border: 1px solid #ff5555;
            color: white;
            padding: 15px 40px;
            font-family: 'Cinzel', serif;
            font-weight: 700;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            margin-top: 20px;
            width: 100%;
        }
        .btn-mu-create:hover {
            background: linear-gradient(180deg, #dc2626 0%, #991b1b 100%);
            box-shadow: 0 0 25px rgba(220, 38, 38, 0.5);
            transform: translateY(-2px);
            color: #fff;
        }

        /* RADIO CUSTOM */
        .form-check-input:checked {
            background-color: var(--mu-gold);
            border-color: var(--mu-gold);
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">



<div class="flex-grow-1 d-flex flex-column py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <form action="index.php?url=create-server-action" method="post" enctype="multipart/form-data" class="create-server-card">

                    <h1 class="page-title">Khởi Tạo Máy Chủ</h1>
                    <p class="page-subtitle">Đăng ký chiến dịch quảng bá MU Online</p>

                    <div class="mu-section-title">
                        <i class="fa-solid fa-scroll"></i> 1. Thông Tin Cơ Bản
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mu-form-group">
                                <label class="mu-label">Tên Máy Chủ <span class="text-danger">*</span></label>
                                <input type="text" class="mu-input" name="server_name" placeholder="VD: Máy chủ Lorencia - SS6" required>
                                <i class="fa-solid fa-server mu-input-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mu-form-group">
                                <label class="mu-label">Tên Cụm (MU Name)</label>
                                <input type="text" class="mu-input" name="mu_name" placeholder="VD: MU Hà Nội" required>
                                <i class="fa-solid fa-dungeon mu-input-icon"></i>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mu-form-group">
                                <label class="mu-label">Slogan (Tiêu đề quảng cáo)</label>
                                <input type="text" class="mu-input" name="slogan" placeholder="Đông người chơi nhất - Đồ xanh chín - Admin nhiệt tình...">
                                <i class="fa-solid fa-bullhorn mu-input-icon"></i>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mu-form-group">
                                <label class="mu-label">Trang Chủ</label>
                                <input type="url" class="mu-input" name="website_url" placeholder="https://mu-game.vn" required>
                                <i class="fa-solid fa-globe mu-input-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mu-form-group">
                                <label class="mu-label">Fanpage</label>
                                <input type="url" class="mu-input" name="fanpage_url" placeholder="https://facebook.com/mu-game" required>
                                <i class="fa-brands fa-facebook mu-input-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mu-section-title mt-4">
                        <i class="fa-solid fa-hourglass-half"></i> 2. Lịch Trình Ra Mắt
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="schedule-box">
                                <span class="sch-title text-alpha"><i class="fa-solid fa-flask me-2"></i>Alpha Test</span>
                                <div class="row">
                                    <div class="col-7">
                                        <label class="mu-label">Ngày</label>
                                        <input type="date" class="mu-input ps-2" name="alpha_date" style="padding-left: 10px;">
                                    </div>
                                    <div class="col-5">
                                        <label class="mu-label">Giờ</label>
                                        <input type="time" class="mu-input ps-2" name="alpha_time" style="padding-left: 10px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="schedule-box" style="border-color: rgba(139, 0, 0, 0.5); background: rgba(139, 0, 0, 0.05);">
                                <span class="sch-title text-beta"><i class="fa-solid fa-fire me-2"></i>Open Beta</span>
                                <div class="row">
                                    <div class="col-7">
                                        <label class="mu-label">Ngày Open <span class="text-danger">*</span></label>
                                        <input type="date" class="mu-input ps-2" name="beta_date" required style="padding-left: 10px;">
                                    </div>
                                    <div class="col-5">
                                        <label class="mu-label">Giờ</label>
                                        <input type="time" class="mu-input ps-2" name="beta_time" required style="padding-left: 10px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mu-section-title mt-5">
                                <i class="fa-solid fa-gears"></i> 3. Cấu Hình & Tính Năng
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-lg-3">
                                    <div class="mu-form-group">
                                        <label class="mu-label">Phiên bản</label>
                                        <select class="mu-select" name="version_id">
                                            <?php foreach ($versions as $v): ?>
                                                <option value="<?= $v['version_id'] ?>"><?= $v['version_name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 col-lg-3">
                                    <div class="mu-form-group">
                                        <label class="mu-label">Thể loại</label>
                                        <select class="mu-select" name="type_id">
                                            <?php if(!empty($serverTypes)): ?>
                                                <?php foreach ($serverTypes as $t): ?>
                                                    <option value="<?= $t['type_id'] ?>"><?= $t['type_name'] ?></option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="1">Cày cuốc</option>
                                                <option value="2">PK Ngay</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <div class="mu-form-group">
                                        <label class="mu-label">Exp Rate (x?)</label>
                                        <input type="number" class="mu-input ps-3" name="exp_rate" value="150" placeholder="150" style="padding-left: 15px;">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 col-lg-3">
                                    <div class="mu-form-group">
                                        <label class="mu-label">Drop Rate (%)</label>
                                        <input type="number" class="mu-input ps-3" name="drop_rate" value="20" placeholder="20" style="padding-left: 15px;">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mu-form-group">
                                        <label class="mu-label">Kiểu Reset</label>
                                        <select class="mu-select" name="reset_id">
                                            <?php foreach ($resetTypes as $r): ?>
                                                <option value="<?= $r['reset_id'] ?>">
                                                    <?= $r['reset_name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mu-form-group">
                                        <label class="mu-label">Kiểu Point</label>
                                        <select class="mu-select" name="point_id">
                                            <?php foreach ($pointTypes as $p): ?>
                                                <option value="<?= $p['point_id'] ?>"><?= $p['point_name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mu-form-group">
                                        <label class="mu-label">Anti-Hack</label>
                                        <input type="text" class="mu-input ps-3" name="anti_hack" placeholder="VietGuard, UGK..." style="padding-left: 15px;">
                                    </div>
                                </div>
                            </div>  

                    <div class="mu-section-title mt-4">
                        <i class="fa-solid fa-image"></i> 4. Banner Quảng Cáo
                    </div>

                    <div class="row mb-3 ps-2">
                        <div class="col-12 mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="uploadType" id="typeFile" value="file" checked onchange="toggleBannerInput()">
                                <label class="form-check-label text-light fw-bold" for="typeFile">
                                    <i class="fa-solid fa-upload me-1 text-warning"></i> Upload từ máy
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="uploadType" id="typeUrl" value="url" onchange="toggleBannerInput()">
                                <label class="form-check-label text-light fw-bold" for="typeUrl">
                                    <i class="fa-solid fa-link me-1 text-info"></i> Link ảnh Online
                                </label>
                            </div>
                        </div>

                        <div class="col-12" id="input-file-group">
                            <div class="mu-form-group">
                                <label class="mu-label">Chọn File Ảnh (JPG, PNG, GIF)</label>
                                <input type="file" class="mu-input ps-2" name="banner_file" accept="image/*" style="padding-left: 10px;">
                                <div class="form-text text-secondary fst-italic mt-1">* Dung lượng tối đa 2MB.</div>
                            </div>
                        </div>

                        <div class="col-12" id="input-url-group" style="display: none;">
                            <div class="mu-form-group">
                                <label class="mu-label">Dán Đường Dẫn Ảnh</label>
                                <input type="url" class="mu-input" name="banner_url" placeholder="https://imgur.com/example.jpg">
                                <i class="fa-solid fa-link mu-input-icon"></i>
                                <div class="form-text text-secondary fst-italic mt-1">* Hãy đảm bảo link ảnh công khai và hoạt động.</div>
                            </div>
                        </div>
                    </div>

                    <div class="vip-package-box mt-4 mb-4">
                        <div class="vip-header"><i class="fa-solid fa-gem me-2"></i> Chọn Gói Quảng Cáo</div>
                        
                        <div class="mb-2">
                            <select name="banner_package" id="bannerPackage" class="mu-select">
                                <option value="BASIC" selected>Gói Cơ Bản - Miễn phí (Vô hạn)</option>

                                <?php if ($remVip > 0): ?>
                                    <option value="VIP" style="color: #ffd700;">
                                        ★ Gói VIP - 5.000 Xu (Còn <?= $remVip ?> slot)
                                    </option>
                                <?php else: ?>
                                    <option value="VIP" disabled style="color: #666;">
                                        ★ Gói VIP - Đã hết chỗ (Full 8/8)
                                    </option>
                                <?php endif; ?>

                                <?php if ($remSvip > 0): ?>
                                    <option value="SUPER_VIP" style="color: #ff4444; font-weight: bold;">
                                        ♛ Gói Super VIP - 10.000 Xu (Còn <?= $remSvip ?> slot)
                                    </option>
                                <?php else: ?>
                                    <option value="SUPER_VIP" disabled style="color: #666;">
                                        ♛ Gói Super VIP - Đã hết chỗ (Full 30/30)
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <?php if ($remSvip <= 5 && $remSvip > 0): ?>
                            <div class="small fst-italic text-warning mt-1">
                                <i class="fa-solid fa-fire me-1"></i> Gói Super VIP sắp hết chỗ, hãy đăng ký ngay!
                            </div>
                        <?php endif; ?>

                        <div class="small fst-italic text-danger mt-1">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> Phí trừ trực tiếp vào Coin khi Admin duyệt.
                        </div>
                    </div>

                    <div class="mu-form-group">
                        <label class="mu-label">Nội dung bài viết</label>
                        <textarea class="mu-textarea" name="description" rows="6" placeholder="Mô tả chi tiết về server, tính năng, sự kiện..."></textarea>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-mu-create">
                            <i class="fa-solid fa-dragon me-2"></i> Xác Nhận Đăng Ký
                        </button>
                        <p class="text-secondary mt-3 small">Vui lòng kiểm tra kỹ thông tin trước khi gửi duyệt.</p>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php // include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. Script xử lý ẩn hiện Upload / URL ảnh
    function toggleBannerInput() {
        const typeFile = document.getElementById('typeFile');
        const fileGroup = document.getElementById('input-file-group');
        const urlGroup = document.getElementById('input-url-group');

        if (typeFile.checked) {
            fileGroup.style.display = 'block';
            urlGroup.style.display = 'none';
            // Clear URL input nếu chuyển sang upload file để tránh gửi dữ liệu rác
            const urlInput = document.querySelector('input[name="banner_url"]');
            if (urlInput) urlInput.value = '';
        } else {
            fileGroup.style.display = 'none';
            urlGroup.style.display = 'block';
            // Clear File input nếu chuyển sang link
            const fileInput = document.querySelector('input[name="banner_file"]');
            if (fileInput) fileInput.value = '';
        }
    }

    // 2. Script kiểm tra URL để hiển thị thông báo
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'success') {
            Swal.fire({
                title: '<span style="color: #cfaa56; font-family: Cinzel, serif;">ĐĂNG KÝ THÀNH CÔNG!</span>',
                html: '<span style="color: #ccc;">Bài viết của bạn đã được gửi lên hệ thống.<br>Vui lòng chờ Admin phê duyệt trong ít phút.</span>',
                icon: 'success',
                background: 'rgba(15, 15, 15, 0.95)',
                confirmButtonText: 'ĐÃ HIỂU',
                confirmButtonColor: '#8b0000',
                customClass: { popup: 'border border-warning' }
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        } else if (status === 'error') {
            Swal.fire({
                icon: 'error',
                title: '<span style="color: #dc3545;">CÓ LỖI XẢY RA!</span>',
                html: '<span style="color: #ccc;">Đăng ký thất bại. Vui lòng kiểm tra lại.</span>',
                background: '#1a1a1a',
                confirmButtonColor: '#444'
            });
        }
    });
</script>

</body>
</html>