<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Server.php';

class AdminServerController {
    private $model;
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Check quyền Admin
        if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'ADMIN') {
            header("Location: ../../../public/index.php"); 
            exit;
        }

        $database = new Database();
        $this->db = $database->connect();
        $this->model = new Server($this->db);
    }

    public function getPackagePrices() {
        return [
            'BASIC'     => 0,
            'VIP'       => 100, 
            'SUPER_VIP' => 200  
        ];
    }

    // File: controllers/AdminServerController.php

public function index() {
    // 1. Cấu hình phân trang
    $limit = 10; // Số server hiển thị trên 1 trang
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    
    // 2. Tính Offset (Vị trí bắt đầu lấy dữ liệu)
    $offset = ($page - 1) * $limit;

    // 3. Lấy tổng số dòng dữ liệu để tính tổng số trang
    $total_records = $this->model->countAllForAdmin();
    $total_pages = ceil($total_records / $limit);

    // 4. Lấy danh sách server theo limit và offset
    $servers = $this->model->getAllForAdmin($limit, $offset);
    $prices = $this->getPackagePrices();
    
    // 5. Trả về mảng dữ liệu bao gồm cả thông tin phân trang
    return [
        'servers' => $servers,
        'prices'  => $prices,
        'pagination' => [
            'current_page'  => $page,
            'total_pages'   => $total_pages,
            'total_records' => $total_records,
            'limit'         => $limit
        ]
    ];
}
    public function edit($id) {
        return $this->model->getById($id);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $imageUrl = '';
            if (!empty($_FILES['banner_file']['name'])) {
                $imageUrl = $this->handleUpload();
            }

            $data = [
                'server_id'     => $_POST['server_id'],
                // Thông tin chung
                'server_name'   => $_POST['server_name'],
                'mu_name'       => $_POST['mu_name'],
                'slogan'        => $_POST['slogan'],
                'banner_package'=> $_POST['banner_package'],
                'status'        => $_POST['status'],
// Trong Controller, đoạn này ĐÚNG:
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'banner_image'  => $imageUrl,
                // Config
                'version_id'    => $_POST['version_id'],
                'type_id'       => $_POST['type_id'],
                'reset_id'      => $_POST['reset_id'],
                'website_url'   => $_POST['website_url'],
                'fanpage_url'   => $_POST['fanpage_url'],
                'description'   => $_POST['description'],
                // Stats
                'exp_rate'      => $_POST['exp_rate'],
                'drop_rate'     => $_POST['drop_rate'],
                'anti_hack'     => $_POST['anti_hack'],
                'point_id'      => $_POST['point_id'],
                // Schedule
                'alpha_date'    => $_POST['alpha_date'],
                'alpha_time'    => $_POST['alpha_time'],
                'open_date'     => $_POST['open_date'],
                'open_time'     => $_POST['open_time']
            ];

            if ($this->model->updateFull($data)) {
                header("Location: index.php?msg=updated");
            } else {
                echo "Lỗi cập nhật!";
            }
        }
    }

    public function delete($id) {
        $img = $this->model->delete($id);
        if ($img) {
             $filePath = __DIR__ . "/../public/" . $img;
             if (file_exists($filePath) && is_file($filePath)) unlink($filePath);
        }
        header("Location: index.php?msg=deleted");
    }

    private function handleUpload() {
        if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === 0) {
            $targetDir = __DIR__ . "/../public/uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            
            $filename = time() . "_" . $_FILES['banner_file']['name'];
            if (move_uploaded_file($_FILES['banner_file']['tmp_name'], $targetDir . $filename)) {
                return "uploads/" . $filename;
            }
        }
        return null;
    }
    
}
?>