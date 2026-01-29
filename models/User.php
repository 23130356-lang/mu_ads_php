<?php
class User {
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $username;
    public $email;
    public $password;
    public $coin;
    public $role;

    public function __construct($db) { $this->conn = $db; }

    public function readAll() {
        $query = "SELECT user_id, username, email, coin, role FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function updateCoin($id, $amount) {
        $query = "UPDATE " . $this->table_name . " SET coin = coin + ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$amount, $id]);
    }
    // Kiểm tra xem username, email hoặc phone đã tồn tại chưa
    public function checkExists($username, $email, $phone) {
        $query = "SELECT user_id FROM " . $this->table_name . " 
                  WHERE username = ? OR email = ? OR phone = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username, $email, $phone]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo user mới
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password, phone, role, coin) 
                  VALUES (:username, :email, :password, :phone, 'USER', 0)";
        $stmt = $this->conn->prepare($query);
        
        // Mã hóa mật khẩu trước khi lưu
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        
        return $stmt->execute($data);
    }

    // Tìm user phục vụ đăng nhập
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}