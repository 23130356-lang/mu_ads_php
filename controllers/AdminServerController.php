<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Server.php';

class AdminServerController {
    private $model;
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Kiểm tra quyền Admin
        if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'ADMIN') {
            // Đường dẫn redirect này tùy thuộc vào cấu trúc thư mục của bạn, hãy điều chỉnh nếu cần
            header("Location: /index.php"); 
            exit;
        }

        $database = new Database();
        $this->db = $database->connect();
        $this->model = new Server($this->db);
    }

    // Lấy giá các gói để hiển thị (Hardcode hoặc lấy từ DB tùy logic)
    public function getPackagePrices() {
        return [
            'BASIC'     => 0,
            'VIP'       => 100, 
            'SUPER_VIP' => 200  
        ];
    }

    // Hiển thị danh sách Server (Có phân trang)
    public function index() {
        $limit = 10; 
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        
        $offset = ($page - 1) * $limit;

        $total_records = $this->model->countAllForAdmin();
        $total_pages = ceil($total_records / $limit);

        $servers = $this->model->getAllForAdmin($limit, $offset);
        $prices = $this->getPackagePrices();
        
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

    // Lấy thông tin Server để sửa (Đã sửa tên hàm cho khớp Model)
    public function edit($id) {
        // QUAN TRỌNG: Sửa getById -> getServerById
        return $this->model->getServerById($id);
    }

    // Xử lý cập nhật thông tin Server
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $imageUrl = '';
            
            // Xử lý upload ảnh nếu có file mới
            if (!empty($_FILES['banner_file']['name'])) {
                $imageUrl = $this->handleUpload();
            }

            // Gom dữ liệu từ Form
            $data = [
                'server_id'     => $_POST['server_id'],
                'server_name'   => $_POST['server_name'],
                'mu_name'       => $_POST['mu_name'],
                'slogan'        => $_POST['slogan'],
                'banner_package'=> $_POST['banner_package'],
                'status'        => $_POST['status'],
                'is_active'     => isset($_POST['is_active']) ? 1 : 0,
                'banner_image'  => $imageUrl, // Nếu rỗng, Model sẽ giữ ảnh cũ
                'version_id'    => $_POST['version_id'],
                'type_id'       => $_POST['type_id'],
                'reset_id'      => $_POST['reset_id'],
                'website_url'   => $_POST['website_url'],
                'fanpage_url'   => $_POST['fanpage_url'],
                'description'   => $_POST['description'],
                'exp_rate'      => $_POST['exp_rate'],
                'drop_rate'     => $_POST['drop_rate'],
                'anti_hack'     => $_POST['anti_hack'],
                'point_id'      => $_POST['point_id'],
                'alpha_date'    => $_POST['alpha_date'],
                'alpha_time'    => $_POST['alpha_time'],
                'open_date'     => $_POST['open_date'],
                'open_time'     => $_POST['open_time']
            ];

            if ($this->model->updateFull($data)) {
                // Redirect về trang danh sách (Điều chỉnh đường dẫn cho phù hợp với router của bạn)
                header("Location: ../../index.php?msg=updated");
                exit;
            } else {
                echo "Lỗi cập nhật Server! Vui lòng kiểm tra lại dữ liệu.";
            }
        }
    }

    // Xóa Server
    public function delete($id) {
        // Hàm delete trong model trả về đường dẫn ảnh cũ để xóa file
        $oldImage = $this->model->delete($id);
        
        if ($oldImage) {
             $filePath = __DIR__ . "/../../public/" . $oldImage; // Chú ý đường dẫn thư mục public
             if (file_exists($filePath) && is_file($filePath)) {
                 unlink($filePath);
             }
        }
        header("Location: index.php?msg=deleted");
        exit;
    }

    // Hàm hỗ trợ Upload ảnh
    private function handleUpload() {
        if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === 0) {
            // Đường dẫn lưu ảnh: public/uploads/
            $targetDir = __DIR__ . "/../../public/uploads/";
            
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            // Đổi tên file để tránh trùng: time_tên_file
            $filename = time() . "_" . basename($_FILES['banner_file']['name']);
            $targetFile = $targetDir . $filename;
            
            if (move_uploaded_file($_FILES['banner_file']['tmp_name'], $targetFile)) {
                return "uploads/" . $filename; // Trả về đường dẫn để lưu vào DB
            }
        }
        return null;
    }
}
?>