<?php
// FILE: controllers/AdminHomeBannerController.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/HomeBanner.php';

class AdminHomeBannerController {
    private $model;
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            // Chuyển hướng về login nếu chưa đăng nhập
            header("Location: ../../../public/index.php?url=login&error=" . urlencode("Vui lòng đăng nhập quản trị"));
            exit;
        }

        // Kiểm tra quyền Admin
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

    // --- XỬ LÝ THÊM MỚI ---
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $imageUrl = $this->handleUpload();
            
            if (!$imageUrl) {
                echo "<script>alert('Vui lòng chọn ảnh hợp lệ!'); window.history.back();</script>";
                exit;
            }

            $data = [
                'user_id'       => $_SESSION['user_id'] ?? 1,
                'image_url'     => $imageUrl,
                'target_url'    => $_POST['target_url'],
                'position_code' => $_POST['position_code'],
                'end_date'      => $_POST['end_date']
            ];

            if ($this->model->create($data)) {
                // [ĐÃ SỬA]: Quay về banners.php (Lùi 2 cấp từ thư mục views/home_banners)
                header("Location: ../../banners.php?msg=created"); 
                exit;
            } else {
                echo "Lỗi hệ thống!";
            }
        }
    }

    // --- XỬ LÝ CẬP NHẬT ---
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
                // [ĐÃ SỬA]: Quay về banners.php (Lùi 2 cấp từ thư mục views/home_banners)
                header("Location: ../../banners.php?msg=updated");
                exit;
            } else {
                echo "Lỗi cập nhật!";
            }
        }
    }

    // --- XỬ LÝ XÓA ---
    public function delete($id) {
        // Lấy đường dẫn ảnh cũ để xóa file vật lý
        $imageUrl = $this->model->delete($id); 
        
        if ($imageUrl) {
            // Xóa file ảnh trong thư mục uploads nếu tồn tại
            if (strpos($imageUrl, 'uploads/') !== false) {
                $filePath = __DIR__ . "/../public/" . $imageUrl;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // [ĐÃ SỬA]: Quay về banners.php
            // Vì hàm delete được gọi trực tiếp từ file banners.php nên không cần ../
            header("Location: banners.php?msg=deleted");
            exit;
        } else {
            echo "Lỗi xóa!";
        }
    }

    // --- HÀM UPLOAD ẢNH ---
    private function handleUpload() {
        if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['imageFile']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $newName = "banner_" . time() . "_" . uniqid() . "." . $ext;
                // [CHECK LẠI ĐƯỜNG DẪN]: Đảm bảo thư mục này tồn tại
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