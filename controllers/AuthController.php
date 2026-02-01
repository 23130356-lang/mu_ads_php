<?php
// Nhúng Model User
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    // --- XỬ LÝ ĐĂNG KÝ ---
    public function register() {
        // 1. Nhận dữ liệu từ $_POST (Form HTML)
        $username = $_POST['username'] ?? '';
        $email    = $_POST['email'] ?? '';
        $phone    = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirmPassword'] ?? '';

        // 2. Validate cơ bản
        if (empty($username) || empty($password) || empty($email) || empty($phone)) {
            $this->redirect('register', 'Vui lòng điền đầy đủ thông tin!');
            return;
        }

        if ($password !== $confirm) {
            $this->redirect('register', 'Mật khẩu nhập lại không khớp!');
            return;
        }

        // 3. Kiểm tra trùng lặp trong DB
        if ($this->userModel->checkExists($username, $email, $phone)) {
            $this->redirect('register', 'Tài khoản, Email hoặc SĐT đã tồn tại!');
            return;
        }

        // 4. Mã hóa mật khẩu & Tạo User
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Mặc định đăng ký mới là role USER (tránh ai đó hack form gửi role ADMIN lên)
        $data = [
            'username' => $username,
            'password' => $hashedPassword,
            'email'    => $email,
            'phone'    => $phone,
            'role'     => 'USER' // Gán cứng role USER
        ];

        if ($this->userModel->create($data)) {
            header("Location: index.php?url=login&mode=login&success=" . urlencode("Đăng ký thành công! Hãy đăng nhập."));
            exit;
        } else {
            $this->redirect('register', 'Lỗi hệ thống, vui lòng thử lại sau.');
        }
    }

    // --- XỬ LÝ ĐĂNG NHẬP ---
    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // 1. Kiểm tra rỗng
        if (empty($username) || empty($password)) {
            $this->redirect('login', 'Vui lòng nhập tài khoản và mật khẩu!');
            return;
        }

        // 2. Tìm User trong DB
        $user = $this->userModel->findByUsername($username);

        // 3. Kiểm tra Password
        if ($user && password_verify($password, $user['password'])) {
            // --- ĐĂNG NHẬP THÀNH CÔNG ---
            
            // Khởi tạo Session nếu chưa có
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Xóa mật khẩu trước khi lưu vào session (Bảo mật)
            unset($user['password']);

            // [QUAN TRỌNG] Lưu toàn bộ thông tin user (bao gồm role) vào session
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $user['user_id'] ?? $user['id']; 

            // 4. ĐIỀU HƯỚNG DỰA TRÊN QUYỀN (ROLE)
            if (isset($user['role']) && $user['role'] === 'ADMIN') {
                
                header("Location: ../admin/views/home_banners/index.php"); 
                exit; 
            }

            // Nếu là User thường -> Về trang chủ
            header("Location: index.php");
            exit;

        } else {
            // Sai mật khẩu hoặc tài khoản
            $this->redirect('login', 'Sai tài khoản hoặc mật khẩu!');
        }
    }

    // --- XỬ LÝ ĐĂNG XUẤT ---
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Hủy toàn bộ session
        session_unset();
        session_destroy();

        // Quay về trang chủ
        header("Location: index.php");
        exit;
    }

    // Hàm phụ trợ
    private function redirect($mode, $errorMsg) {
        header("Location: index.php?url=$mode&mode=$mode&error=" . urlencode($errorMsg));
        exit;
    }
    public function updateProfile() {
        // 1. Kiểm tra đăng nhập
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?url=login");
            exit;
        }

        // 2. Lấy dữ liệu
        $userId   = $_SESSION['user_id'];
        $fullName = $_POST['fullName'] ?? '';
        $email    = $_POST['email'] ?? '';

        // 3. Validate
        if (empty($email)) {
            header("Location: profile.php?error=" . urlencode("Email không được để trống!"));
            exit;
        }

        // 4. Gọi Model cập nhật
        if ($this->userModel->updateInfo($userId, $fullName, $email)) {
            // [QUAN TRỌNG] Cập nhật lại Session để hiển thị ngay lập tức
            $_SESSION['user']['full_name'] = $fullName;
            $_SESSION['user']['email'] = $email;
            
            header("Location: profile.php?success=" . urlencode("Cập nhật thông tin thành công!"));
        } else {
            header("Location: profile.php?error=" . urlencode("Có lỗi xảy ra, vui lòng thử lại."));
        }
        exit;
    }
}   
?>