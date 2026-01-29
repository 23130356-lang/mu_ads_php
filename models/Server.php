<?php
class Server {
    private $conn;
    private $table_name = "servers";

    public function __construct($db) { $this->conn = $db; }

    // Lấy danh sách server
    public function getActiveServers() {
        // Đảm bảo SELECT lấy đủ các cột cần thiết
        $query = "SELECT s.*, v.version_name, st.exp_rate 
                  FROM " . $this->table_name . " s
                  LEFT JOIN mu_versions v ON s.version_id = v.version_id
                  LEFT JOIN server_stats st ON s.server_id = st.server_id
                  WHERE s.status = 'APPROVED' AND s.is_active = 1
                  ORDER BY s.banner_package DESC, s.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

   public function createFull($data) {
    try {
        $this->conn->beginTransaction();

        // --- SỬA DÒNG NÀY ---
        // Đổi 'image' thành 'banner_image'
        $query1 = "INSERT INTO servers 
            (server_name, mu_name, website_url, fanpage_url, description, slogan, 
             version_id, type_id, reset_id, banner_package, banner_image, user_id, status, is_active, created_at)
            VALUES 
            (:name, :mu, :web, :fan, :desc, :slogan, :ver, :type, :reset, :pkg, :img, :uid, 'PENDING', 1, NOW())";
        
        $stmt1 = $this->conn->prepare($query1);
        
        $stmt1->execute([
            ':name'     => $data['server_name'],
            ':mu'       => $data['mu_name'],
            ':web'      => $data['website_url'],
            ':fan'      => $data['fanpage_url'],
            ':desc'     => $data['description'],
            ':slogan'   => $data['slogan'],
            ':ver'      => $data['version_id'],
            ':type'     => $data['type_id'],
            ':reset'    => $data['reset_id'],
            ':pkg'      => $data['banner_package'],
            ':img'      => $data['banner_image'], // Dữ liệu đường dẫn ảnh
            ':uid'      => $data['user_id']
        ]);
        
        $serverId = $this->conn->lastInsertId();

            // --- BƯỚC 2: Insert bảng SERVER_STATS ---
            $query2 = "INSERT INTO server_stats (server_id, exp_rate, drop_rate, anti_hack, point_id) 
                       VALUES (:sid, :exp, :drop, :anti, :point)";
            $this->conn->prepare($query2)->execute([
                ':sid'   => $serverId,
                ':exp'   => $data['exp_rate'],
                ':drop'  => $data['drop_rate'],
                ':anti'  => $data['anti_hack'],
                ':point' => $data['point_id']
            ]);

            // --- BƯỚC 3: Insert bảng SERVER_SCHEDULES ---
            $query3 = "INSERT INTO server_schedules (server_id, alpha_date, alpha_time, beta_date, beta_time)
                       VALUES (:sid, :ad, :at, :bd, :bt)";
            $this->conn->prepare($query3)->execute([
                ':sid' => $serverId,
                ':ad'  => !empty($data['alpha_date']) ? $data['alpha_date'] : NULL,
                ':at'  => $data['alpha_time'],
                ':bd'  => !empty($data['open_date']) ? $data['open_date'] : NULL,
                ':bt'  => $data['open_time']
            ]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            // Ghi log lỗi ra file để debug nếu cần
            error_log("Lỗi tạo server: " . $e->getMessage());
            return false;
        }
    }
}
?>