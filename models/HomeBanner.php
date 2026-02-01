<?php
class HomeBanner {
    private $conn;
    private $table = 'home_banners';

    public function __construct($db) {
        $this->conn = $db;
    }

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

    public function getNextAvailableTime($positionCode) {
        $query = "SELECT MIN(end_date) as next_open FROM " . $this->table . " 
                  WHERE position_code = :pos AND is_active = 1 AND end_date >= NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pos', $positionCode);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['next_open'];
    }

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
    
    public function getRunningBanners() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE is_active = 1 AND end_date >= NOW() 
                  ORDER BY display_order ASC, created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getAllForAdmin() {
        $query = "SELECT h.*, u.username 
                  FROM " . $this->table . " h
                  LEFT JOIN users u ON h.user_id = u.user_id
                  ORDER BY h.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 6. Admin: Lấy 1 banner theo ID để sửa
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data) {
        // Cập nhật linh hoạt: nếu image_url rỗng (không up ảnh mới) thì giữ nguyên ảnh cũ
        $query = "UPDATE " . $this->table . " 
                  SET position_code = :position_code,
                      target_url = :target_url,
                      start_date = :start_date,
                      end_date = :end_date,
                      display_order = :display_order,
                      is_active = :is_active";

        if (!empty($data['image_url'])) {
            $query .= ", image_url = :image_url";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':position_code', $data['position_code']);
        $stmt->bindParam(':target_url', $data['target_url']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':display_order', $data['display_order']);
$stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);        $stmt->bindParam(':id', $data['id']);

        if (!empty($data['image_url'])) {
            $stmt->bindParam(':image_url', $data['image_url']);
        }

        return $stmt->execute();
    }

    public function delete($id) {
        $banner = $this->getById($id);
        
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return $banner['image_url']; // Trả về đường dẫn ảnh để Controller xóa file
        }
        return false;
    }

    public function toggleStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET is_active = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>