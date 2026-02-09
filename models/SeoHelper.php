<?php
/**
 * SeoHelper.mobi
 * Hỗ trợ tạo Meta Tags, Open Graph và Slug chuẩn SEO
 */

class SeoHelper {

    // Cấu hình domain gốc (Nên sửa lại đúng domain thật của bạn)
    const BASE_DOMAIN = 'https://mumoira.mobi';

    /**
     * Làm sạch văn bản đầu vào
     */
    public static function cleanText($str) {
        return trim(strip_tags($str ?? ''));
    }

    /**
     * Tạo Slug chuẩn SEO (Tiếng Việt không dấu)
     */
    public static function createSlug($str) {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '', $str); // Bỏ ký tự đặc biệt
        $str = preg_replace('/([\s]+)/', '-', $str); // Chuyển khoảng trắng thành -
        $str = preg_replace('/-+/', '-', $str); // Xóa dấu gạch ngang liên tiếp (mu--ha-noi -> mu-ha-noi)
        $str = trim($str, '-'); // Cắt gạch ngang ở đầu và cuối
        return $str;
    }

    /**
     * Tạo bộ dữ liệu SEO từ thông tin Server
     * @param array $server Mảng dữ liệu từ DB (ServerModel)
     */
    public static function generateMeta($server) {
        // 1. Xử lý dữ liệu an toàn (Dùng ?? để tránh lỗi nếu key không tồn tại)
        $name    = self::cleanText($server['server_name'] ?? 'Private');
        $muName  = self::cleanText($server['mu_name'] ?? 'Mới Ra');
        $ver     = self::cleanText($server['version_name'] ?? 'Season 6');
        
        // Controller trả về 'type_name' hoặc 'server_type_name', kiểm tra cả 2
        $type    = self::cleanText($server['type_name'] ?? $server['server_type_name'] ?? 'Non-Reset'); 

        // 2. Format ngày tháng
        $openDateStr = $server['beta_date'] ?? null;
        $openDate    = !empty($openDateStr) ? date('d/m', strtotime($openDateStr)) : 'Ngay hôm nay';
        
        $alphaDateStr = $server['alpha_date'] ?? null;
        $alphaDate    = !empty($alphaDateStr) ? date('d/m', strtotime($alphaDateStr)) : 'Đang cập nhật';

        // 3. Tạo URL Canonical (Link đích danh)
        // Format: domain.com/ten-slug-sID
        $slug = self::createSlug($name);
        $canonicalUrl = self::BASE_DOMAIN . '/' . $slug . '-s' . ($server['server_id'] ?? 0);

        // 4. Xử lý ảnh (Quan trọng cho Facebook Share)
        $banner = $server['banner_image'] ?? '';
        $imageUrl = '';
        
        if (filter_var($banner, FILTER_VALIDATE_URL)) {
            // Nếu là link ảnh (imgur, facebook...)
            $imageUrl = $banner;
        } elseif (!empty($banner)) {
            // Nếu là file upload (uploads/abc.jpg) -> Phải thêm domain vào trước
            // Lưu ý: Đảm bảo $banner trong DB lưu dạng 'uploads/filename.jpg'
            $imageUrl = self::BASE_DOMAIN . '/' . ltrim($banner, '/');
        } else {
            // Ảnh mặc định
            $imageUrl = self::BASE_DOMAIN . '/assets/images/default-mu.jpg'; 
        }

        // 5. Tạo Title (Tiêu đề tab trình duyệt)
        // Mẫu: Mu Hà Nội - SS6 | Open 10/10 | Autoreset
        $title = "Mu $name - $muName | Open $openDate | $ver";

        // 6. Tạo Description (Mô tả hiển thị trên Google/Facebook)
        $desc = "Mu $name ($muName) phiên bản $ver. ";
        $desc .= "Alpha Test: $alphaDate - Open Beta: $openDate. ";
        $desc .= "Máy chủ $type, đông người chơi, ổn định lâu dài. ";
        $desc .= "Tải game và nhận Giftcode tân thủ ngay tại Mumoira.mobi";

        // 7. Tạo Keywords
        $keywords = "mu moi ra, mu online, $name, $muName, mu private, game mu, mu open $openDate, mu $ver, mu hom nay";

        // Trả về Object để View dễ gọi ($seo->title)
        return (object) [
            'title'         => $title,
            'desc'          => $desc,
            'keywords'      => $keywords,
            'image'         => $imageUrl,
            'url'           => $canonicalUrl,
            'author'        => $muName,
            'published_time'=> !empty($openDateStr) ? date('c', strtotime($openDateStr)) : '',
            'site_name'     => 'Mumoira.mobi' // Tên website chung
        ];
    }
}
?>