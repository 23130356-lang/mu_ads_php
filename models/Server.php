<?php
class Server {
    private $conn;
    private $table_name = "servers";

    private $prices = [
        'BASIC'     => 0,
        'VIP'       => 100,
        'SUPER_VIP' => 200
    ];

    public function __construct($db) { 
        $this->conn = $db; 
    }

    public function getHomeServers($filterType = 'open', $versionId = null, $resetId = null) {
        $dateCol = ($filterType == 'test') ? 'sch.alpha_date' : 'sch.beta_date';
        
        $sql = "SELECT s.*, v.version_name, r.reset_name, 
                       sch.alpha_date as date_alpha, sch.beta_date as date_open, 
                       st.exp_rate
                FROM " . $this->table_name . " s
                LEFT JOIN mu_versions v ON s.version_id = v.version_id
                LEFT JOIN reset_types r ON s.reset_id = r.reset_id
                LEFT JOIN server_schedules sch ON s.server_id = sch.server_id
                LEFT JOIN server_stats st ON s.server_id = st.server_id
                WHERE s.status = 'APPROVED' AND s.is_active = 1";

        if (!empty($versionId)) $sql .= " AND s.version_id = :verId";
        if (!empty($resetId))   $sql .= " AND s.reset_id = :resetId";

        $sql .= " ORDER BY s.banner_package DESC, " . $dateCol . " DESC";

        $stmt = $this->conn->prepare($sql);
        if (!empty($versionId)) $stmt->bindValue(':verId', $versionId);
        if (!empty($resetId))   $stmt->bindValue(':resetId', $resetId);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetailFull($id) {
        $sql = "SELECT 
                    s.*, 
                    v.version_name, 
                    t.type_name as server_type_name, 
                    r.reset_name, 
                    p.point_name,
                    st.exp_rate, st.drop_rate, st.anti_hack, st.point_id,
                    sch.alpha_date, sch.alpha_time, sch.beta_date, sch.beta_time
                FROM " . $this->table_name . " s
                LEFT JOIN mu_versions v ON s.version_id = v.version_id
                LEFT JOIN server_types t ON s.type_id = t.type_id
                LEFT JOIN reset_types r ON s.reset_id = r.reset_id
                LEFT JOIN server_stats st ON s.server_id = st.server_id
                LEFT JOIN point_types p ON st.point_id = p.point_id
                LEFT JOIN server_schedules sch ON s.server_id = sch.server_id
                WHERE s.server_id = :id AND s.is_active = 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tạo mới Server (Giữ nguyên logic cũ)
     */
    public function createFull($data) {
        try {
             $this->conn->beginTransaction();
             
             // A. Insert Server
             $query1 = "INSERT INTO servers (server_name, mu_name, website_url, fanpage_url, description, slogan, version_id, type_id, reset_id, banner_package, banner_image, user_id, status, is_active, created_at) VALUES (:name, :mu, :web, :fan, :desc, :slogan, :ver, :type, :reset, :pkg, :img, :uid, 'PENDING', 1, NOW())";
             $stmt1 = $this->conn->prepare($query1);
             $stmt1->execute([
                 ':name' => $data['server_name'], ':mu' => $data['mu_name'], ':web' => $data['website_url'], ':fan' => $data['fanpage_url'], ':desc' => $data['description'], ':slogan' => $data['slogan'], ':ver' => $data['version_id'], ':type' => $data['type_id'], ':reset' => $data['reset_id'], ':pkg' => $data['banner_package'], ':img' => $data['banner_image'], ':uid' => $data['user_id']
             ]);
             $serverId = $this->conn->lastInsertId();

             // B. Insert Stats
             $this->conn->prepare("INSERT INTO server_stats (server_id, exp_rate, drop_rate, anti_hack, point_id) VALUES (?, ?, ?, ?, ?)")->execute([$serverId, $data['exp_rate'], $data['drop_rate'], $data['anti_hack'], $data['point_id']]);

             // C. Insert Schedule
             $this->conn->prepare("INSERT INTO server_schedules (server_id, alpha_date, alpha_time, beta_date, beta_time) VALUES (?, ?, ?, ?, ?)")->execute([$serverId, !empty($data['alpha_date'])?$data['alpha_date']:NULL, $data['alpha_time'], !empty($data['open_date'])?$data['open_date']:NULL, $data['open_time']]);

             $this->conn->commit();
             return true;
        } catch(Exception $e) { 
            $this->conn->rollBack(); 
            return false; 
        }
    }


    public function getAllForAdmin() {
        $sql = "SELECT s.*, u.username, u.coin as user_balance, v.version_name, r.reset_name 
                FROM " . $this->table_name . " s 
                LEFT JOIN users u ON s.user_id = u.user_id 
                LEFT JOIN mu_versions v ON s.version_id = v.version_id 
                LEFT JOIN reset_types r ON s.reset_id = r.reset_id 
                ORDER BY s.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Lấy chi tiết cơ bản theo ID (Dùng cho trang Edit của Admin/User)
     */
    public function getById($id) {
        $sql = "SELECT s.*, st.exp_rate, st.drop_rate, st.anti_hack, st.point_id, 
                       sch.alpha_date, sch.alpha_time, sch.beta_date as open_date, sch.beta_time as open_time 
                FROM " . $this->table_name . " s 
                LEFT JOIN server_stats st ON s.server_id = st.server_id 
                LEFT JOIN server_schedules sch ON s.server_id = sch.server_id 
                WHERE s.server_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật Server + Trừ tiền (Giữ nguyên logic cũ)
     */
    public function updateFull($data) {
        try {
            $this->conn->beginTransaction();

            // 1. Kiểm tra trạng thái và trừ tiền
            $checkStmt = $this->conn->prepare("SELECT status, user_id, banner_package FROM servers WHERE server_id = :id");
            $checkStmt->execute([':id' => $data['server_id']]);
            $currentServer = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentServer) throw new Exception("Server không tồn tại!");

            $currentStatus = $currentServer['status'];
            $newStatus     = $data['status'];
            $userId        = $currentServer['user_id'];
            
            if ($currentStatus !== 'APPROVED' && $newStatus === 'APPROVED') {
                $package = $data['banner_package'];
                $price   = $this->prices[$package] ?? 0;

                if ($price > 0) {
                    $userStmt = $this->conn->prepare("SELECT coin FROM users WHERE user_id = :uid");
                    $userStmt->execute([':uid' => $userId]);
                    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$user || $user['coin'] < $price) {
                        throw new Exception("Thành viên không đủ Coin để duyệt gói $package");
                    }

                    $this->conn->prepare("UPDATE users SET coin = coin - :price WHERE user_id = :uid")
                         ->execute([':price' => $price, ':uid' => $userId]);
                }
            }

            // 2. Update Server Info
            $imgSql = !empty($data['banner_image']) ? ", banner_image = :img" : "";
            $sql1 = "UPDATE servers SET 
                        server_name = :name, mu_name = :mu, website_url = :web, fanpage_url = :fan, 
                        description = :desc, slogan = :slogan, version_id = :ver, type_id = :type, 
                        reset_id = :reset, banner_package = :pkg, status = :status, is_active = :active
                        $imgSql
                     WHERE server_id = :id";
            
            $stmt1 = $this->conn->prepare($sql1);
           $params1 = [
                ':name' => $data['server_name'], 
                ':mu' => $data['mu_name'], 
                ':web' => $data['website_url'], 
                ':fan' => $data['fanpage_url'], 
                ':desc' => $data['description'], 
                ':slogan' => $data['slogan'], 
                ':ver' => $data['version_id'], 
                ':type' => $data['type_id'], 
                ':reset' => $data['reset_id'], 
                ':pkg' => $data['banner_package'], 
                ':status' => $data['status'], 
                ':id' => $data['server_id'], 
                // SỬA DÒNG NÀY: Đảm bảo chỉ trả về 0 hoặc 1
                ':active' => (!empty($data['is_active']) ? 1 : 0)
            ];
            if(!empty($data['banner_image'])) $params1[':img'] = $data['banner_image'];
            
            $stmt1->execute($params1);

            // 3. Update Stats
            $this->conn->prepare("UPDATE server_stats SET exp_rate = :exp, drop_rate = :drop, anti_hack = :anti, point_id = :point WHERE server_id = :id")
                 ->execute([':exp' => $data['exp_rate'], ':drop' => $data['drop_rate'], ':anti' => $data['anti_hack'], ':point' => $data['point_id'], ':id' => $data['server_id']]);

            // 4. Update Schedule
            $this->conn->prepare("UPDATE server_schedules SET alpha_date = :ad, alpha_time = :at, beta_date = :bd, beta_time = :bt WHERE server_id = :id")
                 ->execute([
                    ':ad' => !empty($data['alpha_date']) ? $data['alpha_date'] : NULL, ':at' => $data['alpha_time'],
                    ':bd' => !empty($data['open_date']) ? $data['open_date'] : NULL, ':bt' => $data['open_time'], 
                    ':id' => $data['server_id']
                 ]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            die("Lỗi: " . $e->getMessage()); 
        }
    }
    
    public function delete($id) {
         try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare("SELECT banner_image FROM servers WHERE server_id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->conn->prepare("DELETE FROM server_stats WHERE server_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM server_schedules WHERE server_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM servers WHERE server_id = ?")->execute([$id]);
            $this->conn->commit();
            return $row['banner_image'] ?? null;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>