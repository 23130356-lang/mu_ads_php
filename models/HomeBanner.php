<?php
class HomeBanner {
    private $conn;
    private $table = 'home_banners';

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. Đếm số lượng banner đang chạy (Active & Còn hạn)
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

    // 2. Lấy ngày trống slot tiếp theo
    public function getNextAvailableTime($positionCode) {
        $query = "SELECT MIN(end_date) as next_open FROM " . $this->table . " 
                  WHERE position_code = :pos AND is_active = 1 AND end_date >= NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pos', $positionCode);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['next_open'];
    }

    // 3. Tạo Banner Mới
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, image_url, target_url, position_code, start_date, end_date, is_active, display_order, created_at)
                  VALUES 
                  (:user_id, :image_url, :target_url, :position_code, NOW(), :end_date, 1, 0, NOW())";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':target_url', $data['target_url']);
        $stmt->bindParam(':position_code', $data['position_code']);
        $stmt->bindParam(':end_date', $data['end_date']);

        return $stmt->execute();
    }
    
    // 4. Lấy danh sách để hiển thị ra trang chủ (Active = 1)
    public function getRunningBanners() {
        // Logic lấy banner active để show ra ngoài Index
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE is_active = 1 AND end_date >= NOW() 
                  ORDER BY display_order ASC, created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>