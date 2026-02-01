<?php
// 1. Logic xử lý Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu đã login thì đá về trang chủ
if (isset($_SESSION['user'])) {
    exit();
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$activeTab = (isset($_GET['mode']) && $_GET['mode'] == 'register') ? 'register' : 'login';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tài Khoản | MUNORIA.MOBILE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        
        /* === 1. GLOBAL VARIABLES & BACKGROUND === */
        :root {
            --mu-bg: #050505;
            --mu-gold: #cfaa56;     /* Vàng kim loại */
            --mu-red: #8b0000;      /* Đỏ máu */
            --mu-red-hover: #b91c1c;
            --mu-border: #3d2b1f;   /* Nâu đất */
            --mu-glass: rgba(10, 10, 10, 0.95);
        }

        body {
            background-color: var(--mu-bg);
            color: #ccc;
            font-family: 'Rajdhani', sans-serif;
            background-image: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.95)), url('https://toquoc.mediacdn.vn/280518851207290880/2022/6/7/-1654571912757628297204.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            
            /* [QUAN TRỌNG] Thay đổi Flexbox để hỗ trợ Footer nằm đáy */
            display: flex;
            flex-direction: column; /* Xếp dọc: Header -> Content -> Footer */
            /* align-items: center;  <-- Bỏ dòng này (để footer full width) */
            /* justify-content: center; <-- Bỏ dòng này (để dùng flex-grow) */
            position: relative;
        }

        /* [MỚI] Class để ghim Header lên trên cùng */
        .header-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        /* === 2. AUTH CARD STYLE === */
        .auth-card {
            background: var(--mu-glass);
            border: 1px solid var(--mu-border);
            border-top: 3px solid var(--mu-gold);
            width: 100%;
            max-width: 500px;
            padding: 0;
            box-shadow: 0 0 30px rgba(0,0,0,0.9), 0 0 15px rgba(207, 170, 86, 0.15);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            /* Margin để tránh header và tạo khoảng cách */
            margin-top: 180px; 
            margin-bottom: 120px;
        }

        .auth-card::before, .auth-card::after {
            content: ''; position: absolute; width: 15px; height: 15px;
            border: 2px solid var(--mu-gold); pointer-events: none; z-index: 10;
        }
        .auth-card::before { top: 0; left: 0; border-right: none; border-bottom: none; }
        .auth-card::after { bottom: 0; right: 0; border-left: none; border-top: none; }

        /* === 3. TABS NAVIGATION === */
        .auth-tabs {
            display: flex;
            background: rgba(0,0,0,0.5);
            border-bottom: 1px solid var(--mu-border);
        }

        .auth-tab-btn {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            font-family: 'Cinzel', serif;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }

        .auth-tab-btn:hover { color: #aaa; background: rgba(255,255,255,0.02); }

        .auth-tab-btn.active {
            color: var(--mu-gold);
            border-bottom-color: var(--mu-gold);
            background: linear-gradient(180deg, rgba(207, 170, 86, 0.05) 0%, rgba(0,0,0,0) 100%);
            text-shadow: 0 0 10px rgba(207, 170, 86, 0.4);
        }

        /* === 4. FORM CONTENT === */
        .auth-body { padding: 30px; }
        .form-title { text-align: center; font-family: 'Cinzel', serif; color: #fff; margin-bottom: 5px; font-size: 1.5rem; }
        .form-subtitle { text-align: center; font-size: 0.85rem; color: #777; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 1px; }

        /* Input Styles */
        .mu-form-group { margin-bottom: 18px; position: relative; }
        
        .mu-input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid #333;
            padding: 12px 15px 12px 45px;
            color: #fff;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.05rem;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .mu-input:focus {
            background: rgba(0,0,0,0.8);
            border-color: var(--mu-gold);
            outline: none;
            box-shadow: 0 0 10px rgba(207, 170, 86, 0.2);
        }

        .mu-input-icon {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            color: #555; font-size: 1rem; transition: 0.3s;
        }
        .mu-input:focus ~ .mu-input-icon { color: var(--mu-gold); }

        /* Button */
        .btn-mu-submit {
            width: 100%;
            background: linear-gradient(180deg, var(--mu-red-hover) 0%, var(--mu-red) 100%);
            border: 1px solid #ff4444; color: white; padding: 12px;
            font-family: 'Cinzel', serif; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            transition: all 0.3s; margin-top: 10px;
            clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px);
        }
        .btn-mu-submit:hover {
            background: linear-gradient(180deg, #ff4d4d 0%, #a00000 100%);
            box-shadow: 0 0 20px rgba(220, 38, 38, 0.5); transform: translateY(-2px); border-color: #fff;
        }

        /* Alerts */
        .alert-mu {
            background: rgba(139, 0, 0, 0.15); border: 1px solid var(--mu-red);
            color: #ff8888; font-size: 0.9rem; padding: 10px; margin-bottom: 20px;
            display: flex; align-items: center; border-radius: 0;
        }
        .alert-mu.success {
            background: rgba(25, 135, 84, 0.15); border-color: #198754; color: #75b798;
        }

        .mu-link { color: #888; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .mu-link:hover { color: var(--mu-gold); }
        .divider { border-top: 1px solid #333; margin: 25px 0 15px; }
        .auth-mode { display: none; opacity: 0; transition: opacity 0.3s ease-in-out; }
        .auth-mode.active { display: block; opacity: 1; }
    </style>
</head>
<body>

<div class="header-wrapper">
  <?php include 'includes/header.php'; ?>
</div>

<div class="container d-flex justify-content-center align-items-center flex-grow-1">
    
    <div class="auth-card">
        <div class="auth-tabs">
            <div class="auth-tab-btn <?= $activeTab == 'login' ? 'active' : '' ?>" onclick="switchMode('login')">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Đăng Nhập
            </div>
            <div class="auth-tab-btn <?= $activeTab == 'register' ? 'active' : '' ?>" onclick="switchMode('register')">
                <i class="fa-solid fa-user-plus me-2"></i> Đăng Ký
            </div>
        </div>

        <div class="auth-body">
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-mu">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-mu success">
                    <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div id="mode-login" class="auth-mode <?= $activeTab == 'login' ? 'active' : '' ?>">
                <h3 class="form-title">QUẢN LÝ TÀI KHOẢN</h3>
                <p class="form-subtitle">Đăng nhập để quản lý Server & Quảng cáo</p>

                <form action="index.php?url=login-action" method="post">
                    <div class="mu-form-group">
                        <input type="text" class="mu-input" name="username" placeholder="Tên tài khoản" required>
                        <i class="fa-solid fa-user mu-input-icon"></i>
                    </div>

                    <div class="mu-form-group">
                        <input type="password" class="mu-input" name="password" placeholder="Mật khẩu" required>
                        <i class="fa-solid fa-lock mu-input-icon"></i>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 px-1">
                        <div class="form-check form-switch">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" id="rememberMe" name="remember-me">
                            <label class="form-check-label text-secondary small" for="rememberMe">Ghi nhớ</label>
                        </div>
                        <a href="#" class="mu-link small">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn btn-mu-submit">VÀO HỆ THỐNG</button>
                </form>
            </div>

            <div id="mode-register" class="auth-mode <?= $activeTab == 'register' ? 'active' : '' ?>">
                <h3 class="form-title">THÀNH VIÊN MỚI</h3>
                <p class="form-subtitle">Gia nhập cộng đồng MU lớn nhất VN</p>

                <form action="index.php?url=register-action" method="post">
                    <div class="mu-form-group">
                        <input type="text" class="mu-input" name="username" placeholder="Tên đăng nhập (viết liền)" required>
                        <i class="fa-solid fa-user mu-input-icon"></i>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <div class="mu-form-group">
                                <input type="email" class="mu-input" name="email" placeholder="Email" required>
                                <i class="fa-solid fa-envelope mu-input-icon"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mu-form-group">
                                <input type="text" class="mu-input" name="phone" placeholder="SĐT / Zalo" required>
                                <i class="fa-solid fa-phone mu-input-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <div class="mu-form-group">
                                <input type="password" class="mu-input" name="password" placeholder="Mật khẩu" required>
                                <i class="fa-solid fa-lock mu-input-icon"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mu-form-group">
                                <input type="password" class="mu-input" name="confirmPassword" placeholder="Nhập lại MK" required>
                                <i class="fa-solid fa-shield-halved mu-input-icon"></i>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-mu-submit">KHỞI TẠO TÀI KHOẢN</button>
                </form>
            </div>

            <div class="divider"></div>

            <div class="text-center">
                <a href="index.php" class="mu-link"><i class="fa-solid fa-arrow-left me-1"></i> Quay lại trang chủ</a>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    function switchMode(mode) {
        document.querySelectorAll('.auth-tab-btn').forEach(btn => btn.classList.remove('active'));
        const tabs = document.querySelectorAll('.auth-tab-btn');
        if(mode === 'login') tabs[0].classList.add('active'); else tabs[1].classList.add('active');

        document.querySelectorAll('.auth-mode').forEach(el => {
            el.classList.remove('active');
            el.style.opacity = '0';
            setTimeout(() => { if(!el.classList.contains('active')) el.style.display = 'none'; }, 300);
        });

        const target = document.getElementById('mode-' + mode);
        target.style.display = 'block';
        setTimeout(() => { target.classList.add('active'); target.style.opacity = '1'; }, 50);
        
        const alerts = document.querySelectorAll('.alert-mu');
        alerts.forEach(alert => alert.style.display = 'none');
    }

    document.addEventListener("DOMContentLoaded", function() {
        const activeMode = '<?= $activeTab ?>';
        document.getElementById('mode-' + (activeMode === 'login' ? 'register' : 'login')).style.display = 'none';
        
        const activeEl = document.getElementById('mode-' + activeMode);
        activeEl.style.display = 'block';
        setTimeout(() => activeEl.style.opacity = '1', 10);
    });
</script>

</body>
</html>