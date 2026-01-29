<?php
// File: controllers/AuthController.php
require_once '../models/User.php';

class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            echo json_encode(["status" => "error", "message" => "Thiếu thông tin bắt buộc"]);
            return;
        }

        if ($this->userModel->checkExists($data['username'], $data['email'], $data['phone'])) {
            echo json_encode(["status" => "error", "message" => "Tài khoản, Email hoặc Số điện thoại đã tồn tại"]);
            return;
        }

        if ($this->userModel->create($data)) {
            echo json_encode(["status" => "success", "message" => "Đăng ký thành công!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Lỗi hệ thống, vui lòng thử lại"]);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Kiểm tra dữ liệu đầu vào
        if (empty($data['username']) || empty($data['password'])) {
            echo json_encode(["status" => "error", "message" => "Vui lòng nhập tài khoản và mật khẩu"]);
            return;
        }

        $user = $this->userModel->findByUsername($data['username']);

        if ($user && password_verify($data['password'], $user['password'])) {
            // 1. Khởi tạo Session an toàn (chỉ start nếu chưa có)
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // 2. Bảo mật: Xóa mật khẩu đã mã hóa trước khi lưu vào session hoặc trả về client
            unset($user['password']);

            // 3. --- QUAN TRỌNG: SỬA LỖI TẠI ĐÂY ---
            // Lưu riêng user_id ra ngoài để ServerController dễ dàng truy cập
            // Dòng này kiểm tra: nếu DB trả về cột 'user_id' thì lấy, nếu không thì lấy 'id'
            $_SESSION['user_id'] = $user['user_id'] ?? $user['id']; 
            
            // Lưu thêm các thông tin cần thiết khác
            $_SESSION['username'] = $user['username'];
            $_SESSION['user'] = $user; // Lưu mảng user (đã xóa password) để dùng cho việc khác nếu cần

            // 4. Trả về kết quả
            echo json_encode([
                "status" => "success", 
                "message" => "Đăng nhập thành công", 
                "user" => $user
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Sai tài khoản hoặc mật khẩu"]);
        }
    }
    
    // Hàm đăng xuất (Bổ sung thêm nếu bạn cần)
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        echo json_encode(["status" => "success", "message" => "Đã đăng xuất"]);
    }
}
?>