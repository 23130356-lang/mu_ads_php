<?php
class HomeBanner {
    private $conn;
    private $table = 'home_banners';

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. Lấy danh sách đang chạy (Cho trang chủ)
    public function getRunningBanners() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE is_active = 1 
                  AND (start_date <= NOW() OR start_date IS NULL)
                  AND (end_date >= NOW() OR end_date IS NULL)
                  ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 2. Đếm số lượng banner đang chạy theo vị trí (để biết đã Full chưa)
    public function countByPosition($positionCode) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE position_code = :pos 
                  AND is_active = 1 
                  AND end_date >= NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pos', $positionCode);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // 3. Lấy thời gian kết thúc của banner sắp hết hạn nhất (để hiển thị Countdown)
    public function getNextAvailableTime($positionCode) {
        $query = "SELECT MIN(end_date) as next_open FROM " . $this->table . " 
                  WHERE position_code = :pos 
                  AND is_active = 1 
                  AND end_date >= NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pos', $positionCode);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['next_open']; // Trả về chuỗi ngày giờ hoặc NULL
    }

    // 4. Tạo Banner mới (Lưu vào DB)
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, image_url, target_url, position_code, start_date, end_date, is_active, display_order, created_at)
                  VALUES 
                  (:user_id, :image_url, :target_url, :position_code, NOW(), :end_date, 1, 0, NOW())";

        $stmt = $this->conn->prepare($query);

        // Bind dữ liệu
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':target_url', $data['target_url']);
        $stmt->bindParam(':position_code', $data['position_code']);
        $stmt->bindParam(':end_date', $data['end_date']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 5. Trừ tiền user (Hàm phụ trợ nhanh, đúng ra nên ở UserModel)
    public function deductUserCoin($userId, $amount) {
        $query = "UPDATE users SET coin = coin - :amount WHERE id = :id AND coin >= :amount";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':id', $userId);
        if($stmt->execute()) {
            return $stmt->rowCount() > 0; // Trả về true nếu trừ thành công (số dư đủ)
        }
        return false;
    }
    
    // 6. Lấy số dư hiện tại
    public function getUserCoin($userId) {
        $query = "SELECT coin FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['coin'] : 0;
    }
}
?>