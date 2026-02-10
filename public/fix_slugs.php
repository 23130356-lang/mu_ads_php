<?php
// fix_slugs.php - Chạy 1 lần để cập nhật lại toàn bộ Slug cũ
require_once 'config/database.php'; // Đường dẫn tới file connect DB của bạn
require_once 'models/Server.php';

$database = new Database();
$db = $database->connect();
$server = new Server($db);

// 1. Lấy toàn bộ server kèm thông tin Version và Reset
$sql = "SELECT s.server_id, s.server_name, v.version_name, r.reset_name 
        FROM servers s
        LEFT JOIN mu_versions v ON s.version_id = v.version_id
        LEFT JOIN reset_types r ON s.reset_id = r.reset_id";
$stmt = $db->prepare($sql);
$stmt->execute();
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Đang cập nhật Slug...</h2>";

foreach ($list as $item) {
    // Tự viết lại hàm createSlug vì hàm trong Class là private (hoặc bạn đổi private -> public trong Server.php để gọi)
    $rawString = $item['server_name'] . ' ' . $item['version_name'] . ' ' . $item['reset_name'];
    
    // Logic tạo slug (Copy từ model ra cho nhanh)
    $slug = createSlug_Helper($rawString) . '-' . rand(100, 999);
    
    // Cập nhật vào DB
    $updateParams = [':slug' => $slug, ':id' => $item['server_id']];
    $updateSql = "UPDATE servers SET slug = :slug WHERE server_id = :id";
    $db->prepare($updateSql)->execute($updateParams);
    
    echo "Đã cập nhật ID: " . $item['server_id'] . " -> <b>$slug</b><br>";
}

echo "<h3>Hoàn tất! Hãy xóa file này.</h3>";

// Hàm hỗ trợ (Copy từ Server.php)
function createSlug_Helper($string) {
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
?>