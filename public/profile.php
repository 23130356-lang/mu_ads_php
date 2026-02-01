<?php
// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: index.php?url=login&error=" . urlencode("Vui lòng đăng nhập để xem hồ sơ!"));
    exit;
}

// 2. Load lại thông tin User mới nhất từ DB (Để lấy Coin chính xác nhất)
require_once '../config/Database.php';
require_once '../models/User.php';

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

// Lấy thông tin user hiện tại (để lấy coin real-time)
$currentUser = $userModel->findByUsername($_SESSION['user']['username']);
$coinBalance = $currentUser['coin'] ?? 0;
// Cập nhật lại session nếu cần thiết
$_SESSION['user'] = $currentUser; 

$profile = $_SESSION['user']; // Biến dùng để hiển thị HTML
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Game Thủ | MUNORIA.MOBILE</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* === 1. GLOBAL THEME === */
        :root {
            --mu-bg: #050505;
            --mu-gold: #cfaa56;
            --mu-red: #8b0000;
            --mu-red-bright: #dc3545;
            --mu-glass: rgba(15, 15, 15, 0.95);
            --mu-border: #3d2b1f;
        }

        body {
            background-color: var(--mu-bg);
            color: #d1d1d1;
            font-family: 'Rajdhani', sans-serif;
            /* Đổi đường dẫn ảnh nền nếu cần */
            background-image: linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(0,0,0,0.95)), url('https://wallpaperaccess.com/full/1524368.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Cinzel', serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .text-gold { color: var(--mu-gold) !important; }
        .text-red { color: var(--mu-red-bright) !important; }

        /* === 2. CONTENT BOX === */
        .content-section {
            background: var(--mu-glass);
            border: 1px solid var(--mu-border);
            border-radius: 4px;
            padding: 30px;
            margin-bottom: 30px;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.8);
        }

        /* Decorative Corners */
        .content-section::after {
            content: ''; position: absolute; top: -1px; right: -1px;
            width: 20px; height: 20px;
            border-top: 2px solid var(--mu-gold);
            border-right: 2px solid var(--mu-gold);
        }
        .content-section::before {
            content: ''; position: absolute; bottom: -1px; left: -1px;
            width: 20px; height: 20px;
            border-bottom: 2px solid var(--mu-gold);
            border-left: 2px solid var(--mu-gold);
        }

        /* === 3. PROFILE SPECIFIC === */
        .avatar-frame {
            width: 120px; height: 120px; margin: 0 auto 20px;
            border-radius: 50%; border: 3px solid var(--mu-gold);
            background: #000; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 20px rgba(207, 170, 86, 0.3); position: relative;
        }
        .avatar-frame i { font-size: 3rem; color: var(--mu-gold); }
        .vip-badge {
            background: linear-gradient(45deg, #b90000, #ff0000);
            color: white; font-size: 0.7rem; padding: 2px 10px;
            border-radius: 10px; position: absolute; bottom: -5px;
            font-weight: bold; box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }

        .wallet-card {
            background: linear-gradient(135deg, #2c0a0a 0%, #0d0d0d 100%);
            border: 1px dashed var(--mu-gold); border-radius: 8px;
            padding: 20px; margin-top: 20px; text-align: center;
            position: relative; overflow: hidden;
        }
        .wallet-card::before {
            content: "\f51e"; font-family: "Font Awesome 6 Free"; font-weight: 900;
            position: absolute; top: -20px; right: -20px; font-size: 8rem;
            opacity: 0.05; color: var(--mu-gold); transform: rotate(-20deg);
        }
        .coin-amount {
            font-size: 2.5rem; font-family: 'Cinzel', serif;
            color: var(--mu-gold); text-shadow: 0 0 10px rgba(207, 170, 86, 0.5);
            font-weight: 700;
        }

        /* Form Inputs */
        .form-label { color: var(--mu-gold); font-weight: 600; font-family: 'Cinzel', serif; font-size: 0.9rem; }
        .form-control-mu {
            background-color: rgba(255, 255, 255, 0.05); border: 1px solid #333;
            color: #fff; padding: 10px 15px; font-family: 'Rajdhani', sans-serif; font-size: 1.1rem;
        }
        .form-control-mu:focus {
            background-color: rgba(0, 0, 0, 0.5); border-color: var(--mu-gold);
            color: #fff; box-shadow: 0 0 10px rgba(207, 170, 86, 0.2);
        }
        .form-control-mu[readonly] {
            background-color: rgba(0, 0, 0, 0.3); border-color: transparent;
            color: #aaa; cursor: not-allowed;
        }

        /* Buttons */
        .btn-gold {
            background: linear-gradient(to bottom, #d4af37, #aa8822);
            border: 1px solid #886611; color: #000; font-weight: 700;
            font-family: 'Cinzel', serif; text-transform: uppercase; transition: all 0.3s;
        }
        .btn-gold:hover {
            background: linear-gradient(to bottom, #ffdb58, #ccaa33);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.5); color: #000;
        }
        .btn-outline-red {
            border: 1px solid var(--mu-red); color: var(--mu-red-bright);
            font-family: 'Cinzel', serif; background: transparent; transition: all 0.3s;
        }
        .btn-outline-red:hover {
            background: var(--mu-red); color: #fff; box-shadow: 0 0 15px rgba(139, 0, 0, 0.5);
        }
        .text-muted { color: #f1f1f1 !important; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row">

        <div class="col-lg-4 mb-4">
            <div class="content-section text-center">
                <div class="avatar-frame">
                    <i class="fa-solid fa-user-ninja"></i>
                    <span class="vip-badge">MEMBER</span>
                </div>

                <h3 class="text-gold mb-1"><?php echo htmlspecialchars($profile['username']); ?></h3>
                <p class="text-muted small">Tham gia từ <?php echo date("Y", strtotime($profile['created_at'] ?? 'now')); ?></p>

                <div class="wallet-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase text-muted" style="font-size: 0.8rem;">Số dư khả dụng</span>
                        <i class="fa-solid fa-coins text-gold"></i>
                    </div>
                    <div class="coin-amount">
                        <?php echo number_format($coinBalance); ?>
                    </div>
                    <div class="text-end text-muted fst-italic small">MU COIN</div>

                    <div class="mt-3">
                        <a href="guide.php#section-payment" class="btn btn-gold w-100 py-2">
                            <i class="fa-solid fa-cart-plus me-2"></i> NẠP XU NGAY
                        </a>
                    </div>
                </div>

                <div class="mt-4 d-grid gap-2">
                    <a href="index.php?url=logout" class="btn btn-outline-red">
                        <i class="fa-solid fa-power-off me-2"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="content-section">
                <h4 class="mb-4 pb-2 border-bottom border-secondary">
                    <i class="fa-solid fa-scroll text-gold me-2"></i> Thông Tin Tài Khoản
                </h4>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success bg-dark text-success border-success mb-3">
                        <i class="fa-solid fa-check-circle me-2"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger bg-dark text-danger border-danger mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?url=update_profile" method="post">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên Đăng Nhập</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-user"></i></span>
                                <input type="text" class="form-control form-control-mu" value="<?php echo htmlspecialchars($profile['username']); ?>" readonly>
                            </div>
                            <div class="form-text mt-1" style="color: #adadad; font-size: 0.8rem; font-style: italic;">
                                * Tên đăng nhập không thể thay đổi.
                            </div>
                        </div>

                        <div class="col-md-6 mt-3 mt-md-0">
                            <label class="form-label">Cấp Độ</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-medal"></i></span>
                                <input type="text" class="form-control form-control-mu text-gold" value="Thành Viên Chính Thức" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Họ và Tên</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-id-card"></i></span>
                            <input type="text" name="fullName" class="form-control form-control-mu"
                                   value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>" placeholder="Nhập họ và tên hiển thị">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email Xác Thực</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control form-control-mu"
                                       value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6 mt-3 mt-md-0">
                            <label class="form-label">Số Điện Thoại</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-phone"></i></span>
                                <input type="text" class="form-control form-control-mu"
                                       value="<?php echo htmlspecialchars($profile['phone'] ?? 'Chưa cập nhật'); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-gold px-4">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Lưu Thay Đổi
                        </button>
                    </div>

                </form>
            </div>

            <div class="content-section">
                <h4 class="mb-4 pb-2 border-bottom border-secondary">
                    <i class="fa-solid fa-shield-halved text-gold me-2"></i> Bảo Mật & Lịch Sử
                </h4>

                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border border-secondary bg-dark rounded h-100 d-flex flex-column justify-content-center align-items-center">
                            <i class="fa-solid fa-key text-muted fs-2 mb-2"></i>
                            <h5 class="text-white">Đổi Mật Khẩu</h5>
                            <p class="text-muted small">Cập nhật mật khẩu thường xuyên để bảo vệ tài khoản.</p>
                            <a href="#" class="btn btn-sm btn-outline-light mt-auto">Thực hiện</a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border border-secondary bg-dark rounded h-100 d-flex flex-column justify-content-center align-items-center">
                            <i class="fa-solid fa-clock-rotate-left text-muted fs-2 mb-2"></i>
                            <h5 class="text-white">Lịch Sử Giao Dịch</h5>
                            <p class="text-muted small">Xem lại lịch sử nạp xu và đăng ký server.</p>
                            <a href="#" class="btn btn-sm btn-outline-light mt-auto">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>