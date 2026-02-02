<?php
session_start();
require_once '../../../../controllers/AdminHomeBannerController.php';
$controller = new AdminHomeBannerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->update();
}

$id = $_GET['id'] ?? 0;
$banner = $controller->edit($id);

if (!$banner) {
    die("Banner không tồn tại!");
}

// Convert date format for HTML input
$startDate = date('Y-m-d\TH:i', strtotime($banner['start_date']));
$endDate = date('Y-m-d\TH:i', strtotime($banner['end_date']));
?>
    
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Banner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h4>Chỉnh Sửa Banner #<?= $banner['id'] ?></h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $banner['id'] ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Vị trí</label>
                                    <select name="position_code" class="form-select">
                                        <?php 
                                        $positions = ['HERO', 'LEFT_SIDEBAR', 'RIGHT_SIDEBAR', 'STD'];
                                        foreach($positions as $pos): 
                                        ?>
                                            <option value="<?= $pos ?>" <?= $banner['position_code'] == $pos ? 'selected' : '' ?>>
                                                <?= $pos ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thứ tự hiển thị</label>
                                    <input type="number" name="display_order" class="form-control" value="<?= $banner['display_order'] ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ảnh hiện tại</label><br>
                                <?php if(strpos($banner['image_url'], 'http') === 0): ?>
                                    <img src="<?= $banner['image_url'] ?>" style="height: 80px;">
                                <?php else: ?>
                                    <img src="../../../public/<?= $banner['image_url'] ?>" style="height: 80px;">
                                <?php endif; ?>
                                <div class="mt-2">
                                    <label class="text-muted small">Thay ảnh mới (bỏ trống nếu giữ nguyên)</label>
                                    <input type="file" name="imageFile" class="form-control" accept="image/*">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Link đích</label>
                                <input type="text" name="target_url" class="form-control" value="<?= $banner['target_url'] ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày bắt đầu</label>
                                    <input type="datetime-local" name="start_date" class="form-control" value="<?= $startDate ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày kết thúc</label>
                                    <input type="datetime-local" name="end_date" class="form-control" value="<?= $endDate ?>">
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" id="isActive" <?= $banner['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">Kích hoạt (Hiển thị ngay)</label>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">Quay lại</a>
                                <button type="submit" class="btn btn-primary">Cập nhật</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>