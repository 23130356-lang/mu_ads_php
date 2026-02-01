<?php
session_start();
require_once '../../../controllers/AdminServerController.php';
$controller = new AdminServerController();

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->update();
    exit;
}

$server = null;
if (isset($_GET['id'])) {
    $server = $controller->edit($_GET['id']);
}
if (!$server) die("Không tìm thấy server");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa Server #<?= $server['server_id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .card-header-custom { background: white; border-bottom: 1px solid #eee; padding: 15px 20px; font-weight: 600; color: #495057; display: flex; align-items: center; }
        .card-header-custom i { margin-right: 10px; font-size: 1.1rem; }
        .form-floating > label { color: #6c757d; }
        .img-preview-box { width: 100%; height: 180px; border: 2px dashed #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #fff; cursor: pointer; position: relative; }
        .img-preview-box:hover { border-color: #0d6efd; background: #f1f8ff; }
        .img-preview-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .sticky-col { position: sticky; top: 20px; }
    </style>
</head>
<body>

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Chỉnh sửa thông tin</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Danh sách</a></li>
                    <li class="breadcrumb-item active">Server #<?= $server['server_id'] ?></li>
                </ol>
            </nav>
        </div>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="server_id" value="<?= $server['server_id'] ?>">

        <div class="row g-4">
            <div class="col-lg-8">
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header-custom text-primary">
                        <i class="bi bi-info-circle-fill"></i> Thông tin cơ bản
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="serverName" name="server_name" value="<?= $server['server_name'] ?>" required placeholder="Tên Server">
                                    <label for="serverName">Tên Server (*)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="muName" name="mu_name" value="<?= $server['mu_name'] ?>" placeholder="Tên MU">
                                    <label for="muName">Tên phiên bản MU</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="slogan" name="slogan" value="<?= $server['slogan'] ?>" placeholder="Slogan">
                                    <label for="slogan">Slogan hiển thị</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-globe"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="text" class="form-control" name="website_url" value="<?= $server['website_url'] ?>" placeholder="Web">
                                        <label>Trang chủ</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-facebook"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="text" class="form-control" name="fanpage_url" value="<?= $server['fanpage_url'] ?>" placeholder="Fb">
                                        <label>Fanpage</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" name="description" style="height: 100px" placeholder="Mô tả"><?= $server['description'] ?></textarea>
                                    <label>Mô tả chi tiết</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header-custom text-success">
                        <i class="bi bi-calendar-event"></i> Lịch trình & Cấu hình Game
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6 border-end">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3">Thời gian sự kiện</h6>
                                <div class="mb-3">
                                    <label class="form-label text-info fw-bold"><i class="bi bi-flag"></i> Alpha Test</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" name="alpha_date" value="<?= $server['alpha_date'] ?>">
                                        <input type="time" class="form-control" name="alpha_time" value="<?= date('H:i', strtotime($server['alpha_time'])) ?>">
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label text-success fw-bold"><i class="bi bi-play-circle"></i> Open Beta</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" name="open_date" value="<?= $server['open_date'] ?>">
                                        <input type="time" class="form-control" name="open_time" value="<?= date('H:i', strtotime($server['open_time'])) ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 ps-md-4">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3">Thông số Server</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small">Exp Rate</label>
                                        <input type="number" class="form-control form-control-sm" name="exp_rate" value="<?= $server['exp_rate'] ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Drop Rate</label>
                                        <input type="number" class="form-control form-control-sm" name="drop_rate" value="<?= $server['drop_rate'] ?>">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="form-label small">Anti Hack</label>
                                        <input type="text" class="form-control form-control-sm" name="anti_hack" value="<?= $server['anti_hack'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                 <div class="card shadow-sm border-0">
                    <div class="card-header-custom text-secondary">
                        <i class="bi bi-tags"></i> Phân loại (Master Data)
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="version_id" value="<?= $server['version_id'] ?>">
                                    <label>Version ID</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="reset_id" value="<?= $server['reset_id'] ?>">
                                    <label>Reset ID</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="type_id" value="<?= $server['type_id'] ?>">
                                    <label>Type ID</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="point_id" value="<?= $server['point_id'] ?>">
                                    <label>Point ID</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-text mt-2"><i class="bi bi-info-circle"></i> Nhập ID tương ứng từ bảng Master Data (Hoặc nâng cấp thành Select Box sau).</div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                <div class="sticky-col">
                    
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white py-3">
                            <h6 class="m-0 fw-bold"><i class="bi bi-gear-wide-connected"></i> Kiểm duyệt</h6>
                        </div>
                        <div class="card-body">
                            <label class="form-label fw-bold">Trạng thái Server</label>
                            <select name="status" class="form-select form-select-lg mb-3 fw-bold <?= $server['status']=='APPROVED'?'text-success':($server['status']=='PENDING'?'text-warning':'text-danger') ?>">
                                <option value="PENDING" class="text-warning" <?= $server['status']=='PENDING'?'selected':'' ?>>⏳ Chờ duyệt (PENDING)</option>
                                <option value="APPROVED" class="text-success" <?= $server['status']=='APPROVED'?'selected':'' ?>>✅ Duyệt (APPROVED)</option>
                                <option value="REJECTED" class="text-danger" <?= $server['status']=='REJECTED'?'selected':'' ?>>🚫 Từ chối (REJECTED)</option>
                                <option value="EXPIRED" class="text-muted" <?= $server['status']=='EXPIRED'?'selected':'' ?>>⚫ Hết hạn (EXPIRED)</option>
                            </select>

                            <label class="form-label fw-bold">Gói Quảng cáo</label>
                            <select name="banner_package" class="form-select mb-3">
                                <option value="BASIC" <?= $server['banner_package']=='BASIC'?'selected':'' ?>>Tường (BASIC)</option>
                                <option value="VIP" <?= $server['banner_package']=='VIP'?'selected':'' ?>>⭐ VIP</option>
                                <option value="SUPER_VIP" <?= $server['banner_package']=='SUPER_VIP'?'selected':'' ?>>👑 SUPER VIP</option>
                            </select>

                            <div class="form-check form-switch p-3 bg-light rounded border mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?= $server['is_active'] ? 'checked' : '' ?> style="transform: scale(1.3); margin-left: -2em; margin-right: 1em;">
                                <label class="form-check-label fw-bold ms-2" for="isActive">Hiển thị công khai</label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save"></i> Cập nhật ngay</button>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header-custom">
                            <i class="bi bi-image"></i> Hình ảnh Banner
                        </div>
                        <div class="card-body text-center">
                            <label for="bannerInput" class="img-preview-box mb-3" id="previewBox">
                                <?php if(!empty($server['banner_image'])): ?>
                                    <img src="../../../public/<?= $server['banner_image'] ?>" id="previewImg">
                                <?php else: ?>
                                    <div class="text-muted" id="placeholderText">
                                        <i class="bi bi-cloud-upload fs-1"></i><br>Bấm để chọn ảnh
                                    </div>
                                    <img src="" id="previewImg" style="display:none;">
                                <?php endif; ?>
                            </label>
                            <input type="file" name="banner_file" id="bannerInput" class="form-control form-control-sm" accept="image/*" onchange="previewImage(this)">
                            <div class="form-text mt-2 small">Kích thước chuẩn: 900x300px (JPG/PNG)</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Script xem trước ảnh đơn giản
    function previewImage(input) {
        var preview = document.getElementById('previewImg');
        var placeholder = document.getElementById('placeholderText');
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if(placeholder) placeholder.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>