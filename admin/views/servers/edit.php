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
    <title>Sửa Server</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4 mb-5">
    <h3>Chỉnh sửa / Duyệt Server: <?= $server['server_name'] ?></h3>
    <a href="index.php" class="btn btn-secondary mb-3">Quay lại</a>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="server_id" value="<?= $server['server_id'] ?>">

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">Thông tin chính</div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col">
                                <label>Tên Server</label>
                                <input type="text" name="server_name" class="form-control" value="<?= $server['server_name'] ?>" required>
                            </div>
                            <div class="col">
                                <label>Tên MU</label>
                                <input type="text" name="mu_name" class="form-control" value="<?= $server['mu_name'] ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Slogan</label>
                            <input type="text" name="slogan" class="form-control" value="<?= $server['slogan'] ?>">
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label>Website</label>
                                <input type="text" name="website_url" class="form-control" value="<?= $server['website_url'] ?>">
                            </div>
                            <div class="col">
                                <label>Fanpage</label>
                                <input type="text" name="fanpage_url" class="form-control" value="<?= $server['fanpage_url'] ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Mô tả</label>
                            <textarea name="description" class="form-control" rows="3"><?= $server['description'] ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-info text-white">Cấu hình Stats</div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col">
                                <label>Exp Rate</label>
                                <input type="number" name="exp_rate" class="form-control" value="<?= $server['exp_rate'] ?>">
                            </div>
                            <div class="col">
                                <label>Drop Rate</label>
                                <input type="number" name="drop_rate" class="form-control" value="<?= $server['drop_rate'] ?>">
                            </div>
                            <div class="col">
                                <label>Anti Hack</label>
                                <input type="text" name="anti_hack" class="form-control" value="<?= $server['anti_hack'] ?>">
                            </div>
                        </div>
                        <div class="row">
                             <div class="col">
                                <label>Alpha Test</label>
                                <input type="date" name="alpha_date" class="form-control" value="<?= $server['alpha_date'] ?>">
                                <input type="time" name="alpha_time" class="form-control mt-1" value="<?= date('H:i', strtotime($server['alpha_time'])) ?>">
                            </div>
                            <div class="col">
                                <label>Open Beta</label>
                                <input type="date" name="open_date" class="form-control" value="<?= $server['open_date'] ?>">
                                <input type="time" name="open_time" class="form-control mt-1" value="<?= date('H:i', strtotime($server['open_time'])) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">Duyệt & Gói</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Trạng thái</label>
                            <select name="status" class="form-select fw-bold">
                                <option value="PENDING" <?= $server['status']=='PENDING'?'selected':'' ?>>Chờ duyệt</option>
                                <option value="APPROVED" <?= $server['status']=='APPROVED'?'selected':'' ?>>Đã Duyệt</option>
                                <option value="REJECTED" <?= $server['status']=='REJECTED'?'selected':'' ?>>Từ chối</option>
                                <option value="EXPIRED" <?= $server['status']=='EXPIRED'?'selected':'' ?>>Hết hạn</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Gói Banner</label>
                            <select name="banner_package" class="form-select">
                                <option value="BASIC" <?= $server['banner_package']=='BASIC'?'selected':'' ?>>BASIC</option>
                                <option value="VIP" <?= $server['banner_package']=='VIP'?'selected':'' ?>>VIP</option>
                                <option value="SUPER_VIP" <?= $server['banner_package']=='SUPER_VIP'?'selected':'' ?>>SUPER VIP</option>
                            </select>
                        </div>
                         <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?= $server['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">Hiển thị (Active)</label>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="mb-2">
                            <label>Version ID</label>
                            <input type="number" name="version_id" class="form-control" value="<?= $server['version_id'] ?>">
                        </div>
                        <div class="mb-2">
                            <label>Reset ID</label>
                            <input type="number" name="reset_id" class="form-control" value="<?= $server['reset_id'] ?>">
                        </div>
                         <div class="mb-2">
                            <label>Type ID</label>
                            <input type="number" name="type_id" class="form-control" value="<?= $server['type_id'] ?>">
                        </div>
                         <div class="mb-2">
                            <label>Point ID</label>
                            <input type="number" name="point_id" class="form-control" value="<?= $server['point_id'] ?>">
                        </div>

                        <div class="mt-3">
                            <label>Banner Ảnh</label><br>
                            <img src="../../../public/<?= $server['banner_image'] ?>" class="img-fluid mb-2 border rounded">
                            <input type="file" name="banner_file" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-grid mt-3">
            <button type="submit" class="btn btn-success btn-lg">LƯU THAY ĐỔI</button>
        </div>
    </form>
</div>
</body>
</html>