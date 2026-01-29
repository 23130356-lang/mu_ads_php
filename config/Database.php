<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'mu_ads_platform'; // Đảm bảo tên DB đúng
    private $username = 'root';
    private $password = '';
    public $conn;

    // --- ĐÂY LÀ HÀM MÀ INDEX.PHP ĐANG TÌM KIẾM ---
    public function connect() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>