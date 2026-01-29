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

        $data = [
            'username' => $username,
            'password' => $hashedPassword,
            'email'    => $email,
            'phone'    => $phone
        ];

        if ($this->userModel->create($data)) {
            // Đăng ký thành công -> Chuyển sang Tab Login
            // url=login & mode=login & success=...
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

            // Lưu các biến Session quan trọng
            $_SESSION['user'] = $user;             // Dùng để hiển thị Header
            $_SESSION['username'] = $user['username'];
            
            // QUAN TRỌNG: Kiểm tra tên cột ID trong DB của bạn là 'user_id' hay 'id'
            // Code này tự động lấy cái nào tồn tại để tránh lỗi
            $_SESSION['user_id'] = $user['user_id'] ?? $user['id']; 

            // Chuyển hướng về Trang Chủ
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

    // Hàm phụ trợ để redirect kèm thông báo lỗi cho gọn code
    private function redirect($mode, $errorMsg) {
        // mode = login hoặc register
        header("Location: index.php?url=$mode&mode=$mode&error=" . urlencode($errorMsg));
        exit;
    }
}
?>