<?php
require_once '../models/HomeBanner.php';
require_once '../models/User.php';

class HomeBannerController {
    private $bannerModel;
    private $userModel;
    private $db;

    // CẤU HÌNH (Khớp với View)
    private $configs = [
        'HERO'          => ['price' => 500000, 'limit' => 1, 'days' => 30],
        'LEFT_SIDEBAR'  => ['price' => 200000, 'limit' => 5, 'days' => 30],
        'RIGHT_SIDEBAR' => ['price' => 200000, 'limit' => 5, 'days' => 30],
        'STD'           => ['price' => 50000,  'limit' => 10, 'days' => 7]
    ];

    public function __construct($db) {
        $this->db = $db;
        $this->bannerModel = new HomeBanner($db);
        $this->userModel = new User($db);
    }

    // --- [QUAN TRỌNG] HÀM CHUẨN BỊ DỮ LIỆU ĐỂ HIỂN THỊ GIAO DIỆN ---
    public function index() {
        // 1. Kiểm tra session và cập nhật lại Coin mới nhất từ DB
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
        
        if ($currentUser && isset($currentUser['user_id'])) {
            // Lấy coin thực tế từ DB để tránh sai lệch session cũ
            $realCoin = $this->userModel->getCoin($currentUser['user_id']);
            $currentUser['coin'] = $realCoin;
            // Cập nhật ngược lại session
            $_SESSION['user']['coin'] = $realCoin;
        }

        // 2. Chuẩn bị mảng dữ liệu mặc định để tránh lỗi Undefined variable
        $viewData = [
            'currentUser' => $currentUser,
            'qtyInfo' => [],
            'availability' => [],
            'nextAvailableMap' => [],
            'prices' => [],
            'isFullLeft' => false,
            'isFullRight' => false,
            'isFullHero' => false,
            'isFullStd' => false
        ];

        // 3. Loop qua config để lấy số liệu thực tế từ DB
        foreach ($this->configs as $code => $cfg) {
            // Giá tiền
            $viewData['prices'][$code] = $cfg['price'];

            // Đếm số lượng đang chạy
            $count = $this->bannerModel->countByPosition($code);
            // Format hiển thị: "2 / 5"
            $viewData['qtyInfo'][$code] = $count . ' / ' . $cfg['limit'];

            // Kiểm tra xem đã đầy chưa
            $isFull = ($count >= $cfg['limit']);

            // Gán cờ boolean (để view dùng if/else)
            if ($code == 'LEFT_SIDEBAR') $viewData['isFullLeft'] = $isFull;
            if ($code == 'RIGHT_SIDEBAR') $viewData['isFullRight'] = $isFull;
            if ($code == 'HERO') $viewData['isFullHero'] = $isFull;
            if ($code == 'STD') $viewData['isFullStd'] = $isFull;

            // Gán text trạng thái và ngày mở lại
            if ($isFull) {
                $viewData['availability'][$code] = "<span style='color:red; font-weight:bold;'>ĐÃ FULL</span>";
                $nextOpen = $this->bannerModel->getNextAvailableTime($code);
                $viewData['nextAvailableMap'][$code] = $nextOpen; // View sẽ dùng JS để countdown
            } else {
                $viewData['availability'][$code] = "<span style='color:green; font-weight:bold;'>CÒN TRỐNG</span>";
                $viewData['nextAvailableMap'][$code] = null;
            }
        }

        // 4. Bung mảng $viewData thành các biến lẻ ($qtyInfo, $prices...)
        extract($viewData);

        // 5. Gọi View hiển thị (Include Header tại đây để Header cũng nhận được biến $currentUser)
        // Lưu ý: Đường dẫn tính từ file index.php ở thư mục public
        require_once 'includes/header.php'; 
        require_once 'banner-register.php'; 
    }

    // --- XỬ LÝ MUA BANNER (POST) ---
    public function register() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=login&error=" . urlencode("Bạn cần đăng nhập!"));
            exit;
        }

        $userId = $_SESSION['user_id'];
        $posCode = $_POST['positionCode'] ?? '';
        
        // 1. Validate Config
        if (!array_key_exists($posCode, $this->configs)) {
            $this->redirectBack("Vị trí không hợp lệ");
        }

        $config = $this->configs[$posCode];
        $price = $config['price'];
        $days = $config['days'];

        // 2. Validate Slot
        $currentCount = $this->bannerModel->countByPosition($posCode);
        if ($currentCount >= $config['limit']) {
            $this->redirectBack("Vị trí này vừa hết slot!");
        }

        // 3. Xử lý Ảnh
        $finalImage = $this->handleImageUpload();
        if (!$finalImage) {
            $this->redirectBack("Lỗi ảnh: Vui lòng upload file ảnh hợp lệ hoặc nhập URL đúng.");
        }

        // 4. Transaction (Trừ tiền + Tạo Banner)
        try {
            $this->db->beginTransaction();

            $isDeducted = $this->userModel->deductCoin($userId, $price);
            if (!$isDeducted) {
                throw new Exception("Số dư không đủ (Cần " . number_format($price) . " coin).");
            }

            $endDate = date('Y-m-d H:i:s', strtotime("+$days days"));
            $bannerData = [
                'user_id' => $userId,
                'image_url' => $finalImage,
                'target_url' => $_POST['targetUrl'] ?? '',
                'position_code' => $posCode,
                'end_date' => $endDate
            ];

            if (!$this->bannerModel->create($bannerData)) {
                throw new Exception("Lỗi hệ thống khi tạo banner.");
            }

            $this->db->commit();
            
            // Cập nhật lại session coin
            $_SESSION['user']['coin'] = $this->userModel->getCoin($userId);

            header("Location: index.php?url=banner-register&success=" . urlencode("Đăng ký thành công!"));
            exit;

        } catch (Exception $e) {
            $this->db->rollBack();
            // Xóa ảnh rác nếu có
            if (strpos($finalImage, 'uploads/') !== false && file_exists("../public/" . $finalImage)) {
                @unlink("../public/" . $finalImage);
            }
            $this->redirectBack($e->getMessage());
        }
    }

    private function handleImageUpload() {
        $uploadType = $_POST['uploadType'] ?? 'file';

        if ($uploadType === 'file') {
            if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $filename = $_FILES['imageFile']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {
                    $newName = "banner_" . time() . "_" . uniqid() . "." . $ext;
                    // Chú ý đường dẫn lưu file
                    $targetDir = "../public/uploads/banners/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                    
                    if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $targetDir . $newName)) {
                        return "uploads/banners/" . $newName; 
                    }
                }
            }
        } else {
            $url = trim($_POST['imageUrl'] ?? '');
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
        }
        return false;
    }

    private function redirectBack($msg) {
        header("Location: index.php?url=banner-register&error=" . urlencode($msg));
        exit;
    }
}
?>