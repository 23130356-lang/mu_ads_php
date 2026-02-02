<?php
session_start();
require_once '../../../../controllers/AdminHomeBannerController.php';
$controller = new AdminHomeBannerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->store();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Banner Mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4>Thêm Banner Mới</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-3">
                                <label class="form-label">Vị trí hiển thị</label>
                                <select name="position_code" class="form-select" required>
                                    <option value="HERO">HERO (Slide chính)</option>
                                    <option value="LEFT_SIDEBAR">LEFT_SIDEBAR (Trái)</option>
                                    <option value="RIGHT_SIDEBAR">RIGHT_SIDEBAR (Phải)</option>
                                    <option value="STD">STD (Banner thường)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ảnh Banner</label>
                                <input type="file" name="imageFile" class="form-control" accept="image/*" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Link đích (Target URL)</label>
                                <input type="text" name="target_url" class="form-control" placeholder="https://...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ngày kết thúc</label>
                                <input type="datetime-local" name="end_date" class="form-control" required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">Quay lại</a>
                                <button type="submit" class="btn btn-success">Lưu Banner</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>