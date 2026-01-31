<?php
class Server {
    private $conn;
    private $table_name = "servers";

    public function __construct($db) { $this->conn = $db; }

    // HÀM QUAN TRỌNG: Lấy danh sách server hiển thị ra trang chủ (có lọc)
    public function getHomeServers($filterType = 'open', $versionId = null, $resetId = null) {
        // Chọn cột ngày dựa theo bộ lọc (Test hay Open)
        $dateCol = ($filterType == 'test') ? 'sch.alpha_date' : 'sch.beta_date';
        
        $sql = "SELECT s.*, 
                       v.version_name, 
                       r.reset_name,
                       sch.alpha_date as date_alpha, 
                       sch.beta_date as date_open,
                       st.exp_rate
                FROM " . $this->table_name . " s
                LEFT JOIN mu_versions v ON s.version_id = v.version_id
                LEFT JOIN reset_types r ON s.reset_id = r.reset_id
                LEFT JOIN server_schedules sch ON s.server_id = sch.server_id
                LEFT JOIN server_stats st ON s.server_id = st.server_id
                WHERE s.status = 'APPROVED' AND s.is_active = 1";

        // Thêm điều kiện lọc
        if (!empty($versionId)) {
            $sql .= " AND s.version_id = :verId";
        }
        if (!empty($resetId)) {
            $sql .= " AND s.reset_id = :resetId";
        }

        // Sắp xếp: Gói VIP cao nhất lên đầu, sau đó đến Ngày gần nhất
        $sql .= " ORDER BY s.banner_package DESC, " . $dateCol . " DESC";

        $stmt = $this->conn->prepare($sql);

        // Bind giá trị
        if (!empty($versionId)) $stmt->bindValue(':verId', $versionId);
        if (!empty($resetId)) $stmt->bindValue(':resetId', $resetId);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ... (Giữ nguyên hàm createFull của bạn ở đây) ...
    public function createFull($data) {
        // ... Code cũ của bạn ...
        // Lưu ý: Nếu code cũ đang dùng bindParam thì ok, 
        // nhưng đảm bảo Database connect trả về PDO object chuẩn.
        try {
            $this->conn->beginTransaction();
            // ... (Logic insert như cũ) ...
            // Bạn copy lại đoạn createFull cũ vào đây
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
                ':img'      => $data['banner_image'],
                ':uid'      => $data['user_id']
            ]);
            
            $serverId = $this->conn->lastInsertId();

            $query2 = "INSERT INTO server_stats (server_id, exp_rate, drop_rate, anti_hack, point_id) 
                       VALUES (:sid, :exp, :drop, :anti, :point)";
            $this->conn->prepare($query2)->execute([
                ':sid'   => $serverId,
                ':exp'   => $data['exp_rate'],
                ':drop'  => $data['drop_rate'],
                ':anti'  => $data['anti_hack'],
                ':point' => $data['point_id']
            ]);

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
            return false;
        }
    }
}
?>