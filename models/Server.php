<?php
class Server {
    private $conn;
    private $table_name = "servers";

    // Cấu hình các gói dịch vụ (Dùng để tính tiền mà không cần bảng packages trong DB)
    public $packages = [
        'BASIC'     => ['price' => 0,   'days' => 7,  'label' => 'BASIC', 'color' => 'secondary'],
        'VIP'       => ['price' => 100, 'days' => 10, 'label' => 'VIP', 'color' => 'warning'],
        'SUPER_VIP' => ['price' => 200, 'days' => 14, 'label' => 'Super VIP', 'color' => 'danger']
    ];

    public function __construct($db) { 
        $this->conn = $db; 
    }

    // --- HÀM HỖ TRỢ: TẠO SLUG URL THÂN THIỆN ---
    private function createSlug($string) {
        $search = array(
            '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#', '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
            '#(ì|í|ị|ỉ|ĩ)#', '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
            '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#', '#(ỳ|ý|ỵ|ỷ|ỹ)#', '#(đ)#',
            '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#', '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
            '#(Ì|Í|Ị|Ỉ|Ĩ)#', '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
            '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#', '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#', '#(Đ)#', "/[^a-zA-Z0-9\-\_]/",
        );
        $replace = array('a', 'e', 'i', 'o', 'u', 'y', 'd', 'A', 'E', 'I', 'O', 'U', 'Y', 'D', '-');
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', '-', $string);
        return strtolower(trim($string, '-'));
    }

    /**
     * HÀM HỖ TRỢ MỚI: Lấy tên từ ID (Dùng để tạo Slug chi tiết)
     */
    private function getNameById($table, $idColumn, $nameColumn, $idValue) {
        if (empty($idValue)) return '';
        try {
            // Chỉ sử dụng nội bộ, $table và $column do code truyền vào nên an toàn
            $stmt = $this->conn->prepare("SELECT $nameColumn FROM $table WHERE $idColumn = ? LIMIT 1");
            $stmt->execute([$idValue]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result[$nameColumn] : '';
        } catch (Exception $e) {
            return '';
        }
    }

    // =================================================================
    // PHẦN 1: QUẢN LÝ SERVER CÁ NHÂN & GIA HẠN
    // =================================================================

    /**
     * Lấy danh sách server của User đang đăng nhập
     */
    public function getServersByUserId($userId) {
        $sql = "SELECT s.*, 
                       v.version_name 
                FROM " . $this->table_name . " s
                LEFT JOIN mu_versions v ON s.version_id = v.version_id
                WHERE s.user_id = :uid 
                ORDER BY s.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Xử lý gia hạn Server (Trừ tiền + Cộng ngày)
     */
    public function renew($serverId, $userId) {
        try {
            $this->conn->beginTransaction();

            // 1. Lấy thông tin Server hiện tại
            $stmt = $this->conn->prepare("SELECT server_name, banner_package, expired_at, status FROM " . $this->table_name . " WHERE server_id = :sid AND user_id = :uid FOR UPDATE");
            $stmt->execute([':sid' => $serverId, ':uid' => $userId]);
            $server = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$server) {
                throw new Exception("Server không tồn tại hoặc bạn không có quyền sở hữu.");
            }

            // 2. Lấy thông tin giá từ cấu hình PHP
            $packKey = $server['banner_package'] ?? 'BASIC';
            $packInfo = $this->packages[$packKey] ?? $this->packages['BASIC'];
            
            $cost = $packInfo['price'];
            $daysToAdd = $packInfo['days'];

            // 3. Kiểm tra và Trừ tiền User
            $userStmt = $this->conn->prepare("SELECT coin FROM users WHERE user_id = :uid");
            $userStmt->execute([':uid' => $userId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || $user['coin'] < $cost) {
                throw new Exception("Số dư không đủ để gia hạn (Cần: " . number_format($cost) . " Xu).");
            }

            if ($cost > 0) {
                $this->conn->prepare("UPDATE users SET coin = coin - :cost WHERE user_id = :uid")
                            ->execute([':cost' => $cost, ':uid' => $userId]);
            }

            // 4. Tính ngày hết hạn mới
            $currentExpire = $server['expired_at'];
            $now = date('Y-m-d H:i:s');
            // Nếu còn hạn thì cộng tiếp, nếu hết hạn thì tính từ thời điểm hiện tại
            $baseDate = ($currentExpire && $currentExpire > $now) ? $currentExpire : $now;
            $newExpire = date('Y-m-d H:i:s', strtotime($baseDate . " + $daysToAdd days"));

            // 5. Cập nhật Server
            $updateSql = "UPDATE " . $this->table_name . " 
                          SET expired_at = :exp, 
                              status = 'APPROVED', 
                              is_active = 1 
                          WHERE server_id = :sid";
            
            $this->conn->prepare($updateSql)->execute([
                ':exp' => $newExpire,
                ':sid' => $serverId
            ]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return $e->getMessage();
        }
    }

    // =================================================================
    // PHẦN 2: CLIENT VIEW (HIỂN THỊ)
    // =================================================================

    /**
     * Lấy danh sách server trang chủ
     * Mapping: beta_date -> ngày Open
     */
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

        // Sắp xếp: Gói VIP cao nhất lên đầu -> Ngày Open mới nhất
        $sql .= " ORDER BY FIELD(s.banner_package, 'SUPER_VIP', 'VIP', 'BASIC'), " . $dateCol . " DESC";

        $stmt = $this->conn->prepare($sql);
        if (!empty($versionId)) $stmt->bindValue(':verId', $versionId);
        if (!empty($resetId))   $stmt->bindValue(':resetId', $resetId);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết server (Full Join) theo ID hoặc Slug
     */
    public function getDetailFull($identifier) {
        // Tự động nhận diện ID hoặc Slug
        $field = is_numeric($identifier) ? 's.server_id' : 's.slug';

        $sql = "SELECT s.*, 
                       v.version_name, 
                       t.type_name as server_type_name, 
                       r.reset_name as reset_type_name, 
                       p.point_name as point_type_name,
                       st.exp_rate, st.drop_rate, st.anti_hack, st.point_id,
                       sch.alpha_date, sch.alpha_time, sch.beta_date, sch.beta_time
                FROM " . $this->table_name . " s
                LEFT JOIN mu_versions v ON s.version_id = v.version_id
                LEFT JOIN server_types t ON s.type_id = t.type_id
                LEFT JOIN reset_types r ON s.reset_id = r.reset_id
                LEFT JOIN server_stats st ON s.server_id = st.server_id
                LEFT JOIN point_types p ON st.point_id = p.point_id
                LEFT JOIN server_schedules sch ON s.server_id = sch.server_id
                WHERE $field = :id AND s.is_active = 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $identifier);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =================================================================
    // PHẦN 3: CREATE / UPDATE / ADMIN
    // =================================================================

    /**
     * Tạo mới Server (Transaction 3 bảng) - SLUG NÂNG CẤP
     */
    public function createFull($data) {
        try {
             $this->conn->beginTransaction();
             
             // --- XỬ LÝ SLUG NÂNG CAO ---
             // 1. Lấy tên Version và Reset từ ID
             $verName = $this->getNameById('mu_versions', 'version_id', 'version_name', $data['version_id']);
             $resName = $this->getNameById('reset_types', 'reset_id', 'reset_name', $data['reset_id']);

             // 2. Ghép chuỗi: Tên Server - Version - Reset
             $rawString = $data['server_name'] . ' ' . $verName . ' ' . $resName;

             // 3. Tạo Slug + Số ngẫu nhiên
             $slug = $this->createSlug($rawString) . '-' . rand(100, 999);
             // ---------------------------

             // 4. Insert bảng servers
             $query1 = "INSERT INTO servers (
                  server_name, slug, mu_name, website_url, fanpage_url, description, slogan, 
                  version_id, type_id, reset_id, banner_package, banner_image, user_id, 
                  status, is_active, created_at
             ) VALUES (
                  :name, :slug, :mu, :web, :fan, :desc, :slogan, 
                  :ver, :type, :reset, :pkg, :img, :uid, 
                  'PENDING', 1, NOW()
             )";
             
             $stmt1 = $this->conn->prepare($query1);
             $stmt1->execute([
                 ':name' => $data['server_name'], 
                 ':slug' => $slug, // Sử dụng slug mới tạo
                 ':mu' => $data['mu_name'], ':web' => $data['website_url'], ':fan' => $data['fanpage_url'], 
                 ':desc' => $data['description'], ':slogan' => $data['slogan'], 
                 ':ver' => $data['version_id'], ':type' => $data['type_id'], ':reset' => $data['reset_id'], 
                 ':pkg' => $data['banner_package'], ':img' => $data['banner_image'], 
                 ':uid' => $data['user_id']
             ]);
             $serverId = $this->conn->lastInsertId();

             // 5. Insert bảng server_stats
             $this->conn->prepare("INSERT INTO server_stats (server_id, exp_rate, drop_rate, anti_hack, point_id) VALUES (?, ?, ?, ?, ?)")
                        ->execute([$serverId, $data['exp_rate'], $data['drop_rate'], $data['anti_hack'], $data['point_id']]);

             // 6. Insert bảng server_schedules
             $this->conn->prepare("INSERT INTO server_schedules (server_id, alpha_date, alpha_time, beta_date, beta_time) VALUES (?, ?, ?, ?, ?)")
                        ->execute([
                            $serverId, 
                            !empty($data['alpha_date']) ? $data['alpha_date'] : NULL, 
                            $data['alpha_time'], 
                            !empty($data['open_date']) ? $data['open_date'] : NULL, 
                            $data['open_time']
                        ]);

             $this->conn->commit();
             return true;
        } catch(Exception $e) { 
            $this->conn->rollBack(); 
            return false; 
        }
    }

    /**
     * Cập nhật Server - SLUG NÂNG CẤP
     */
    public function updateFull($data) {
        try {
            $this->conn->beginTransaction();

            // 1. Kiểm tra trạng thái hiện tại
            $checkStmt = $this->conn->prepare("SELECT status, user_id, banner_package FROM servers WHERE server_id = :id FOR UPDATE");
            $checkStmt->execute([':id' => $data['server_id']]);
            $currentServer = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentServer) throw new Exception("Server không tồn tại!");

            $currentStatus = $currentServer['status'];
            $newStatus     = $data['status'];
            $userId        = $currentServer['user_id'];
            
            $dateSql = ""; 
            $dateParams = [];

            // 2. Logic Trừ tiền nếu chuyển từ Chưa duyệt -> Duyệt
            if ($currentStatus !== 'APPROVED' && $newStatus === 'APPROVED') {
                $package = $data['banner_package'];
                
                $packInfo = $this->packages[$package] ?? ['price' => 0, 'days' => 7];
                $price = $packInfo['price'];
                $daysToAdd = $packInfo['days'];

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

                $now = date('Y-m-d H:i:s');
                $expired = date('Y-m-d H:i:s', strtotime("+$daysToAdd days"));

                $dateSql = ", approved_at = :app_at, expired_at = :exp_at";
                $dateParams = [':app_at' => $now, ':exp_at' => $expired];
            }

            // --- XỬ LÝ SLUG NÂNG CAO CHO UPDATE ---
            // Đồng bộ slug khi update thông tin
            $verName = $this->getNameById('mu_versions', 'version_id', 'version_name', $data['version_id']);
            $resName = $this->getNameById('reset_types', 'reset_id', 'reset_name', $data['reset_id']);
            $rawString = $data['server_name'] . ' ' . $verName . ' ' . $resName;
            
            $newSlug = $this->createSlug($rawString) . '-' . rand(100,999);
            // --------------------------------------

            $imgSql = !empty($data['banner_image']) ? ", banner_image = :img" : "";
            
            // 4. Update bảng servers
            $sql1 = "UPDATE servers SET 
                        server_name = :name, slug = :slug, mu_name = :mu, website_url = :web, fanpage_url = :fan, 
                        description = :desc, slogan = :slogan, version_id = :ver, type_id = :type, 
                        reset_id = :reset, banner_package = :pkg, status = :status, is_active = :active
                        $imgSql 
                        $dateSql 
                     WHERE server_id = :id";
            
            $stmt1 = $this->conn->prepare($sql1);

            $params1 = [
                ':name' => $data['server_name'], 
                ':slug' => $newSlug, // Slug mới
                ':mu' => $data['mu_name'], ':web' => $data['website_url'], 
                ':fan' => $data['fanpage_url'], ':desc' => $data['description'], 
                ':slogan' => $data['slogan'], ':ver' => $data['version_id'], 
                ':type' => $data['type_id'], ':reset' => $data['reset_id'], 
                ':pkg' => $data['banner_package'], ':status' => $data['status'], 
                ':id' => $data['server_id'], ':active' => (!empty($data['is_active']) ? 1 : 0)
            ];

            if(!empty($data['banner_image'])) $params1[':img'] = $data['banner_image'];
            if (!empty($dateParams)) $params1 = array_merge($params1, $dateParams);
            
            $stmt1->execute($params1);

            // 5. Update bảng server_stats
            $this->conn->prepare("UPDATE server_stats SET exp_rate = :exp, drop_rate = :drop, anti_hack = :anti, point_id = :point WHERE server_id = :id")
                 ->execute([
                     ':exp' => $data['exp_rate'], ':drop' => $data['drop_rate'], 
                     ':anti' => $data['anti_hack'], ':point' => $data['point_id'], 
                     ':id' => $data['server_id']
                 ]);

            // 6. Update bảng server_schedules
            $this->conn->prepare("UPDATE server_schedules SET alpha_date = :ad, alpha_time = :at, beta_date = :bd, beta_time = :bt WHERE server_id = :id")
                 ->execute([
                    ':ad' => !empty($data['alpha_date']) ? $data['alpha_date'] : NULL, 
                    ':at' => $data['alpha_time'],
                    ':bd' => !empty($data['open_date']) ? $data['open_date'] : NULL, 
                    ':bt' => $data['open_time'], 
                    ':id' => $data['server_id']
                 ]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return $e->getMessage();
        }
    }

    /**
     * Lấy thông tin Server theo ID để Edit
     */
    public function getServerById($id) {
        $query = "SELECT s.*, u.username, u.email,
                         st.exp_rate, st.drop_rate, st.anti_hack, st.point_id,
                         sch.alpha_date, sch.alpha_time, sch.beta_date as open_date, sch.beta_time as open_time
                  FROM " . $this->table_name . " s
                  LEFT JOIN users u ON s.user_id = u.user_id
                  LEFT JOIN server_stats st ON s.server_id = st.server_id 
                  LEFT JOIN server_schedules sch ON s.server_id = sch.server_id 
                  WHERE s.server_id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $packKey = $row['banner_package'] ?? 'BASIC';
                $packInfo = $this->packages[$packKey] ?? $this->packages['BASIC'];

                $row['package_price'] = $packInfo['price'];
                $row['package_days']  = $packInfo['days'];
                $row['package_label'] = $packInfo['label'];
                
                return $row;
            }
        }
        return false;
    }

    // Các hàm Admin List / Delete giữ nguyên logic
    public function countAllForAdmin() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM " . $this->table_name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getAllForAdmin($limit = 10, $offset = 0) {
        $sql = "SELECT s.*, u.username, u.coin as user_balance, v.version_name, r.reset_name 
                FROM " . $this->table_name . " s 
                LEFT JOIN users u ON s.user_id = u.user_id 
                LEFT JOIN mu_versions v ON s.version_id = v.version_id 
                LEFT JOIN reset_types r ON s.reset_id = r.reset_id 
                ORDER BY s.created_at DESC
                LIMIT :limit OFFSET :offset"; 
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function delete($id) {
        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare("SELECT banner_image FROM servers WHERE server_id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Xóa các bảng con trước (FK constraints)
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
    
    /**
     * Tự động chuyển trạng thái EXPIRED cho các server hết hạn
     */
    public function autoRejectExpired() {
        try {
            $sql = "UPDATE " . $this->table_name . " 
                    SET status = 'EXPIRED', is_active = 0 
                    WHERE status = 'APPROVED' 
                    AND expired_at IS NOT NULL 
                    AND expired_at < NOW()";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>