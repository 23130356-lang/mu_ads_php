<?php
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. Kiểm tra xem tài khoản, email hoặc sđt đã tồn tại chưa
    public function checkExists($username, $email, $phone) {
        $query = "SELECT user_id FROM " . $this->table . " 
                  WHERE username = :username 
                  OR email = :email 
                  OR phone = :phone 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        
        // Làm sạch dữ liệu đầu vào
        $username = htmlspecialchars(strip_tags($username));
        $email = htmlspecialchars(strip_tags($email));
        $phone = htmlspecialchars(strip_tags($phone));

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // 2. Tạo tài khoản mới
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (username, password, email, phone, role, created_at) 
                  VALUES 
                  (:username, :password, :email, :phone, 'member', NOW())";

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu
        $username = htmlspecialchars(strip_tags($data['username']));
        $email    = htmlspecialchars(strip_tags($data['email']));
        $phone    = htmlspecialchars(strip_tags($data['phone']));
        // Password đã được mã hóa ở Controller nên không cần strip_tags

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $data['password']); // Đã Hash
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 3. Tìm thông tin user để đăng nhập
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>