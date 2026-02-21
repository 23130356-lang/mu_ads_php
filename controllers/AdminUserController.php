<?php
// FILE: controllers/AdminUserController.php
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/config/Database.php';

class AdminUserController {
    private $conn;
    private $table = 'users';
    private $userModel;
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
        $this->userModel = new User($this->conn);
    }

    // Lấy danh sách user (Trả về PDO Statement để dùng fetch() bên view)
    public function index() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY user_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Xóa user
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Cộng/Trừ tiền
    public function updateCoin($user_id, $amount, $type) {
        if ($type === 'minus') {
            $amount = -$amount;
        }
        $query = "UPDATE " . $this->table . " SET coin = coin + :amount WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':id', $user_id);
        return $stmt->execute();
    }
    
    // Đổi quyền (Admin <-> User)
    public function changeRole($user_id, $current_role) {
        $new_role = ($current_role === 'ADMIN') ? 'USER' : 'ADMIN';
        $query = "UPDATE " . $this->table . " SET role = :role WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $new_role);
        $stmt->bindParam(':id', $user_id);
        return $stmt->execute();
    }
    public function ajaxSearch($page = 1, $limit = 10, $keyword = '') {
        $offset = ($page - 1) * $limit;
        
        // Lấy data
        $users = $this->userModel->getUsers($limit, $offset, $keyword);
        $totalUsers = $this->userModel->countUsers($keyword);
        
        // Tính tổng số trang
        $totalPages = ceil($totalUsers / $limit);

        return [
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalUsers
            ]
        ];
    }
}
?>