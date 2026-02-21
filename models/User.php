<?php
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function checkExists($username, $email, $phone) {
        $query = "SELECT user_id FROM " . $this->table . " 
                  WHERE username = :username OR email = :email OR phone = :phone LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // 2. Tạo tài khoản mới
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (username, password, email, full_name, phone, role, coin, created_at) 
                  VALUES 
                  (:username, :password, :email, :full_name, :phone, 'USER', 0, NOW())"; // Mặc định role USER, coin 0

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $username = htmlspecialchars(strip_tags($data['username']));
        $email    = htmlspecialchars(strip_tags($data['email']));
        $phone    = htmlspecialchars(strip_tags($data['phone']));
        $fullName = htmlspecialchars(strip_tags($data['full_name']));

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':full_name', $fullName);

        return $stmt->execute();
    }

    // 3. Tìm user theo username (Dùng cho Login)
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. Lấy số dư hiện tại (Quan trọng: Luôn lấy từ DB, không tin Session cũ)
    public function getCoin($userId) {
        $query = "SELECT coin FROM " . $this->table . " WHERE user_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['coin'] : 0;
    }

    // 5. Trừ tiền (Sử dụng logic an toàn: coin >= amount)
    public function deductCoin($userId, $amount) {
        $query = "UPDATE " . $this->table . " 
                  SET coin = coin - :amount 
                  WHERE user_id = :id AND coin >= :amount";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':id', $userId);
        
        if ($stmt->execute()) {
            // Kiểm tra xem có dòng nào được update không (rowCount > 0 nghĩa là trừ thành công)
            return $stmt->rowCount() > 0;
        }
        return false;
    }
    public function updateInfo($userId, $fullName, $email) {
        $query = "UPDATE " . $this->table . " 
                  SET full_name = :full_name, email = :email 
                  WHERE user_id = :id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $fullName = htmlspecialchars(strip_tags($fullName));
        $email    = htmlspecialchars(strip_tags($email));

        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $userId);

        return $stmt->execute();
    }
    public function getUsers($limit, $offset, $keyword = '') {
        $sql = "SELECT * FROM " . $this->table;
        
        // Điều kiện tìm kiếm
        if (!empty($keyword)) {
            $sql .= " WHERE username LIKE :kw 
                      OR email LIKE :kw 
                      OR phone LIKE :kw 
                      OR full_name LIKE :kw";
        }
        
        $sql .= " ORDER BY user_id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($keyword)) {
            $kw = "%{$keyword}%";
            $stmt->bindParam(':kw', $kw);
        }
        
        // PDO LIMIT/OFFSET cần bind dạng INT
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Trả về mảng dữ liệu
    }

    // 7. Đếm tổng số user (để tính số trang)
    public function countUsers($keyword = '') {
        $sql = "SELECT COUNT(*) as total FROM " . $this->table;
        
        if (!empty($keyword)) {
            $sql .= " WHERE username LIKE :kw 
                      OR email LIKE :kw 
                      OR phone LIKE :kw 
                      OR full_name LIKE :kw";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($keyword)) {
            $kw = "%{$keyword}%";
            $stmt->bindParam(':kw', $kw);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>