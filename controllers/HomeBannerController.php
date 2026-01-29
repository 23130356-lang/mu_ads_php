<?php
require_once '../models/HomeBanner.php';

class HomeBannerController {
    private $model;
    private $db;

    // CẤU HÌNH GIÁ VÀ GIỚI HẠN SLOT (Bạn có thể sửa ở đây hoặc lưu DB)
    private $configs = [
        'HERO' => ['price' => 500000, 'limit' => 1, 'days' => 30, 'name' => 'Banner Giữa (VIP)'],
        'LEFT_SIDEBAR' => ['price' => 200000, 'limit' => 1, 'days' => 30, 'name' => 'Banner Trái'],
        'RIGHT_SIDEBAR' => ['price' => 200000, 'limit' => 1, 'days' => 30, 'name' => 'Banner Phải'],
        'STD' => ['price' => 50000, 'limit' => 5, 'days' => 7, 'name' => 'Banner Giữa (Nhỏ)']
    ];

    public function __construct($db) {
        $this->db = $db;
        $this->model = new HomeBanner($db);
    }

    // --- HIỂN THỊ TRANG ĐĂNG KÝ ---
    public function index() {
        // Lấy thông tin user (giả lập session nếu chưa login)
        $currentUser = isset($_SESSION['user_id']) ? ['id' => $_SESSION['user_id'], 'coin' => $this->model->getUserCoin($_SESSION['user_id'])] : null;

        // Chuẩn bị dữ liệu view
        $viewData = [
            'currentUser' => $currentUser,
            'qtyInfo' => [],
            'availability' => [],
            'nextAvailableMap' => [],
            'prices' => [],
            'isFullLeft' => false,
            'isFullRight' => false,
            'isFullHero' => false,
            'isFullStd' => false,
        ];

        // Loop qua các vị trí để lấy thống kê
        foreach ($this->configs as $code => $cfg) {
            // 1. Lấy giá
            $viewData['prices'][$code] = $cfg['price'];

            // 2. Đếm số lượng đang chạy
            $count = $this->model->countByPosition($code);
            $viewData['qtyInfo'][$code] = $count . ' / ' . $cfg['limit'];

            // 3. Kiểm tra Full chưa
            $isFull = ($count >= $cfg['limit']);
            
            // Set flag cho View
            if ($code == 'LEFT_SIDEBAR') $viewData['isFullLeft'] = $isFull;
            if ($code == 'RIGHT_SIDEBAR') $viewData['isFullRight'] = $isFull;
            if ($code == 'HERO') $viewData['isFullHero'] = $isFull;
            if ($code == 'STD') $viewData['isFullStd'] = $isFull;

            // 4. Text hiển thị
            if ($isFull) {
                $viewData['availability'][$code] = "Đã Full";
                // Lấy thời gian mở lại
                $nextOpen = $this->model->getNextAvailableTime($code);
                // Convert định dạng cho Javascript (ISO 8601)
                $viewData['nextAvailableMap'][$code] = $nextOpen ? date('Y-m-d\TH:i:s', strtotime($nextOpen)) : null;
            } else {
                $viewData['availability'][$code] = "Còn Trống";
                $viewData['nextAvailableMap'][$code] = null;
            }
        }

        // Include view và truyền biến
        extract($viewData); // Biến mảng thành các biến $prices, $qtyInfo...
        require_once '../public/home_ads.php';
    }

    // --- XỬ LÝ ĐĂNG KÝ (POST) ---
    public function register() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=login");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $posCode = $_POST['positionCode'];
        
        // 1. Kiểm tra vị trí hợp lệ
        if (!array_key_exists($posCode, $this->configs)) {
            die("Vị trí không hợp lệ");
        }

        $config = $this->configs[$posCode];
        $price = $config['price'];
        $days = $config['days'];

        // 2. Kiểm tra slot còn trống không
        $currentCount = $this->model->countByPosition($posCode);
        if ($currentCount >= $config['limit']) {
            header("Location: index.php?url=ads&error=Vị trí này vừa hết slot!");
            exit;
        }

        // 3. Xử lý Ảnh (File hoặc URL) - Logic giống ServerController
        $finalImage = '';
        $uploadType = $_POST['uploadType'] ?? 'file';

        if ($uploadType === 'file') {
            if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === 0) {
                $targetDir = "../public/uploads/banners/";
                if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

                $fileName = time() . '_bn_' . basename($_FILES["imageFile"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
                
                if (in_array($fileType, ['jpg', 'png', 'gif', 'jpeg', 'webp'])) {
                    if (move_uploaded_file($_FILES["imageFile"]["tmp_name"], $targetFilePath)) {
                        $finalImage = "uploads/banners/" . $fileName;
                    }
                }
            }
        } else {
            $url = trim($_POST['imageUrl'] ?? '');
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $finalImage = $url;
            }
        }

        if (empty($finalImage)) {
            header("Location: index.php?url=ads&error=Ảnh không hợp lệ!");
            exit;
        }

        // 4. Trừ tiền User
        if (!$this->model->deductUserCoin($userId, $price)) {
            header("Location: index.php?url=ads&error=Số dư không đủ!");
            exit;
        }

        // 5. Tính ngày hết hạn
        $endDate = date('Y-m-d H:i:s', strtotime("+$days days"));

        // 6. Lưu vào DB
        $data = [
            'user_id' => $userId,
            'image_url' => $finalImage,
            'target_url' => $_POST['targetUrl'],
            'position_code' => $posCode,
            'end_date' => $endDate
        ];

        if ($this->model->create($data)) {
            header("Location: index.php?url=ads&success=Đăng ký thành công!");
        } else {
            // Lưu ý: Thực tế nên hoàn tiền lại nếu insert lỗi (dùng transaction)
            header("Location: index.php?url=ads&error=Lỗi hệ thống!");
        }
    }
}
?>