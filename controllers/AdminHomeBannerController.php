<?php
// SỬA LẠI ĐOẠN REQUIRE NÀY
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/HomeBanner.php';

class AdminHomeBannerController {
    private $model;
    private $db;

    public function __construct() {
        // 1. Khởi động Session nếu chưa có
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Kiểm tra đã đăng nhập chưa? (Dựa vào cấu trúc session của bạn)
        // Giả sử khi login bạn lưu $_SESSION['user'] hoặc $_SESSION['user_id']
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            // Chưa đăng nhập -> Đá về trang login
            // Đường dẫn này tính từ file View đang chạy (admin/views/home_banners/...)
            header("Location: ../../../public/index.php?url=login&error=" . urlencode("Vui lòng đăng nhập quản trị"));
            exit;
        }


        $userRole = $_SESSION['user']['role'] ?? ''; 
        
        if ($userRole !== 'ADMIN') {
       
            echo "<div style='color:red; text-align:center; margin-top:50px; font-family:sans-serif;'>";
            echo "<h1>TRUY CẬP BỊ TỪ CHỐI!</h1>";
            echo "<p>Tài khoản của bạn không có quyền Admin.</p>";
            echo "<a href='../../../public/index.php'>Quay về trang chủ</a>";
            echo "</div>";
            exit;
        }

        $database = new Database();
        $this->db = $database->connect();
        $this->model = new HomeBanner($this->db);
    }

    // Lấy danh sách banner
    public function index() {
        return $this->model->getAllForAdmin();
    }

    // Lấy chi tiết 1 banner
    public function edit($id) {
        return $this->model->getById($id);
    }

    // Xử lý Thêm mới
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $imageUrl = $this->handleUpload();
            
            if (!$imageUrl) {
                echo "<script>alert('Vui lòng chọn ảnh hợp lệ!'); window.history.back();</script>";
                exit;
            }

            $data = [
                'user_id'       => $_SESSION['user_id'] ?? 1, // ID Admin (tạm thời để 1 nếu chưa login)
                'image_url'     => $imageUrl,
                'target_url'    => $_POST['target_url'],
                'position_code' => $_POST['position_code'],
                'end_date'      => $_POST['end_date']
            ];

            if ($this->model->create($data)) {
                // Sửa lại đường dẫn chuyển hướng cho đúng với view
                header("Location: index.php?msg=created"); 
                exit;
            } else {
                echo "Lỗi hệ thống!";
            }
        }
    }

    // Xử lý Cập nhật
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            
            $imageUrl = '';
            if (!empty($_FILES['imageFile']['name'])) {
                $imageUrl = $this->handleUpload();
            }

            $data = [
                'id'            => $id,
                'position_code' => $_POST['position_code'],
                'target_url'    => $_POST['target_url'],
                'start_date'    => $_POST['start_date'],
                'end_date'      => $_POST['end_date'],
                'display_order' => $_POST['display_order'],
                'is_active'     => isset($_POST['is_active']) ? 1 : 0,
                'image_url'     => $imageUrl
            ];

            if ($this->model->update($data)) {
                header("Location: index.php?msg=updated");
                exit;
            } else {
                echo "Lỗi cập nhật!";
            }
        }
    }

    // Xử lý Xóa
    public function delete($id) {
        $imageUrl = $this->model->delete($id);
        if ($imageUrl) {

            if (strpos($imageUrl, 'uploads/') !== false) {
                $filePath = __DIR__ . "/../public/" . $imageUrl;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            header("Location: index.php?msg=deleted");
            exit;
        } else {
            echo "Lỗi xóa!";
        }
    }

    // Hàm Upload ảnh
    private function handleUpload() {
        if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['imageFile']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $newName = "banner_" . time() . "_" . uniqid() . "." . $ext;
                
                // Dùng __DIR__ để định vị chính xác thư mục public/uploads/banners/
                $targetDir = __DIR__ . "/../public/uploads/banners/";
                
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                
                if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $targetDir . $newName)) {
                    return "uploads/banners/" . $newName; 
                }
            }
        }
        return false;
    }
}
?>