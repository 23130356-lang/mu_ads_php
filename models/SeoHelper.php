<?php
class SeoHelper {
    public static function cleanText($str) {
        return trim(strip_tags($str));
    }

    public static function generateMeta($server) {
        // Xử lý dữ liệu đầu vào (tương thích mảng từ DB)
        $name = self::cleanText($server['server_name']);
        $muName = self::cleanText($server['mu_name']);
        $ver = self::cleanText($server['version_name']);
        $type = self::cleanText($server['server_type_name']); // Lấy từ alias trong query
        
        // Format ngày tháng
        $openDate = !empty($server['beta_date']) ? date('d/m', strtotime($server['beta_date'])) : 'Sắp ra mắt';
        $alphaDate = !empty($server['alpha_date']) ? date('d/m', strtotime($server['alpha_date'])) : 'Đang cập nhật';

        // 1. Tạo Title
        $title = "Mu $name - $muName | Mới Ra $openDate | Phiên bản $ver";

        // 2. Tạo Description
        $desc = "Mu Mới Ra: $name ($muName). Phiên bản $ver chuẩn Webzen. ";
        $desc .= "Alpha Test: $alphaDate. Open Beta: $openDate. ";
        $desc .= "Lối chơi: $type. Đăng ký nhận Giftcode tân thủ ngay hôm nay!";

        // 3. Tạo Keywords
        $keywords = "mu moi ra, mu online, $name, $muName, mu private, game mu, mu open $openDate, mu $ver";

        // 4. Tạo URL Canonical
        $slug = self::createSlug($name);
        $canonical = "https://mumoira.mobi/" . $slug . "-s" . $server['server_id'];

        return (object) [
            'title' => $title,
            'desc' => $desc,
            'keywords' => $keywords,
            'image' => !empty($server['banner_image']) ? $server['banner_image'] : 'https://mumoira.mobi/images/default-mu.jpg',
            'url' => $canonical,
            'author' => $muName,
            'open_date_iso' => !empty($server['beta_date']) ? date('c', strtotime($server['beta_date'])) : ''
        ];
    }

    public static function createSlug($str) {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
        $str = preg_replace('/([\s]+)/', '-', $str);
        return $str;
    }
}
?>