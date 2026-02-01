<?php
require_once '../models/HomeBanner.php';
require_once '../models/User.php';

class HomeBannerController {
    private $bannerModel;
    private $userModel;
    private $db;

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

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
        
        if ($currentUser && isset($currentUser['user_id'])) {
            $realCoin = $this->userModel->getCoin($currentUser['user_id']);
            $currentUser['coin'] = $realCoin;
            $_SESSION['user']['coin'] = $realCoin;
        }

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

        foreach ($this->configs as $code => $cfg) {
            $viewData['prices'][$code] = $cfg['price'];

            $count = $this->bannerModel->countByPosition($code);
            $viewData['qtyInfo'][$code] = $count . ' / ' . $cfg['limit'];

            $isFull = ($count >= $cfg['limit']);

            if ($code == 'LEFT_SIDEBAR') $viewData['isFullLeft'] = $isFull;
            if ($code == 'RIGHT_SIDEBAR') $viewData['isFullRight'] = $isFull;
            if ($code == 'HERO') $viewData['isFullHero'] = $isFull;
            if ($code == 'STD') $viewData['isFullStd'] = $isFull;

            if ($isFull) {
                $viewData['availability'][$code] = "<span style='color:red; font-weight:bold;'>ĐÃ FULL</span>";
                $nextOpen = $this->bannerModel->getNextAvailableTime($code);
                $viewData['nextAvailableMap'][$code] = $nextOpen; // View sẽ dùng JS để countdown
            } else {
                $viewData['availability'][$code] = "<span style='color:green; font-weight:bold;'>CÒN TRỐNG</span>";
                $viewData['nextAvailableMap'][$code] = null;
            }
        }

        extract($viewData);

       
        require_once 'includes/header.php'; 
        require_once 'banner-register.php'; 
    }

    public function register() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=login&error=" . urlencode("Bạn cần đăng nhập!"));
            exit;
        }

        $userId = $_SESSION['user_id'];
        $posCode = $_POST['positionCode'] ?? '';
        
        if (!array_key_exists($posCode, $this->configs)) {
            $this->redirectBack("Vị trí không hợp lệ");
        }

        $config = $this->configs[$posCode];
        $price = $config['price'];
        $days = $config['days'];

        $currentCount = $this->bannerModel->countByPosition($posCode);
        if ($currentCount >= $config['limit']) {
            $this->redirectBack("Vị trí này vừa hết slot!");
        }

        $finalImage = $this->handleImageUpload();
        if (!$finalImage) {
            $this->redirectBack("Lỗi ảnh: Vui lòng upload file ảnh hợp lệ hoặc nhập URL đúng.");
        }

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
            
            $_SESSION['user']['coin'] = $this->userModel->getCoin($userId);

            header("Location: index.php?url=banner-register&success=" . urlencode("Đăng ký thành công!"));
            exit;

        } catch (Exception $e) {
            $this->db->rollBack();
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