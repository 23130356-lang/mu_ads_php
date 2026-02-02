<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function register() {
        $username = $_POST['username'] ?? '';
        $email    = $_POST['email'] ?? '';
        $phone    = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirmPassword'] ?? '';

        if (empty($username) || empty($password) || empty($email) || empty($phone)) {
            $this->redirect('register', 'Vui lòng điền đầy đủ thông tin!');
            return;
        }

        if ($password !== $confirm) {
            $this->redirect('register', 'Mật khẩu nhập lại không khớp!');
            return;
        }

        if ($this->userModel->checkExists($username, $email, $phone)) {
            $this->redirect('register', 'Tài khoản, Email hoặc SĐT đã tồn tại!');
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $data = [
            'username' => $username,
            'password' => $hashedPassword,
            'email'    => $email,
            'phone'    => $phone,
            'role'     => 'USER' 
        ];

        if ($this->userModel->create($data)) {
            header("Location: index.php?url=login&mode=login&success=" . urlencode("Đăng ký thành công! Hãy đăng nhập."));
            exit;
        } else {
            $this->redirect('register', 'Lỗi hệ thống, vui lòng thử lại sau.');
        }
    }

    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->redirect('login', 'Vui lòng nhập tài khoản và mật khẩu!');
            return;
        }

        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            unset($user['password']);

            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $user['user_id'] ?? $user['id']; 

            if (isset($user['role']) && $user['role'] === 'ADMIN') {
                
                header("Location: ../admin/index.php"); 
                exit; 
            }

            header("Location: index.php");
            exit;

        } else {
            $this->redirect('login', 'Sai tài khoản hoặc mật khẩu!');
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_unset();
        session_destroy();

        header("Location: index.php");
        exit;
    }

    private function redirect($mode, $errorMsg) {
        header("Location: index.php?url=$mode&mode=$mode&error=" . urlencode($errorMsg));
        exit;
    }
    public function updateProfile() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?url=login");
            exit;
        }

        $userId   = $_SESSION['user_id'];
        $fullName = $_POST['fullName'] ?? '';
        $email    = $_POST['email'] ?? '';

        if (empty($email)) {
            header("Location: profile.php?error=" . urlencode("Email không được để trống!"));
            exit;
        }

        if ($this->userModel->updateInfo($userId, $fullName, $email)) {
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