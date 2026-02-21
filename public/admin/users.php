<?php
session_start();
// FILE: public/admin/users.php
require_once '../../controllers/AdminUserController.php';

$controller = new AdminUserController();

// --- 1. XỬ LÝ REQUEST AJAX (Trả về JSON và dừng code ngay) ---
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $limit = 10; // Số dòng mỗi trang

    $result = $controller->ajaxSearch($page, $limit, $keyword);
    echo json_encode($result);
    exit; // Dừng chạy code phía dưới
}

// --- XỬ LÝ FORM SUBMIT PHP THUẦN (Delete, Role, Coin) ---
$msg = "";
if (isset($_GET['delete_id'])) {
    if ($controller->delete($_GET['delete_id'])) $msg = "Đã xóa thành viên thành công!";
}
if (isset($_GET['role_id']) && isset($_GET['current_role'])) {
    if ($controller->changeRole($_GET['role_id'], $_GET['current_role'])) $msg = "Đã thay đổi quyền thành công!";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_coin') {
    $user_id = $_POST['user_id'];
    $amount = intval($_POST['amount']);
    $type = $_POST['type'];
    if ($controller->updateCoin($user_id, $amount, $type)) $msg = "Đã cập nhật số dư thành công!";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Thành viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .role-admin { background-color: #dc3545; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }
        .role-user { background-color: #6c757d; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }
        .coin-text { font-weight: bold; color: #198754; }
        /* Loading spinner overlay */
        #loading-overlay { display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); z-index: 10; align-items: center; justify-content: center; }
    </style>
</head>
<body>
    
    <div class="d-flex">
        <?php require_once 'includes/sidebar.php'; ?>

        <div class="flex-grow-1 bg-light p-4" style="height: 100vh; overflow-y: auto;">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Quản lý Thành viên</h2>
                <div class="input-group w-25">
                    <input type="text" id="searchInput" class="form-control" placeholder="Tìm tên, email, sđt...">
                    <button class="btn btn-primary" onclick="fetchUsers(1)">Tìm</button>
                </div>
            </div>

            <?php if(!empty($msg)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow position-relative">
                <div id="loading-overlay"><div class="spinner-border text-primary"></div></div>

                <div class="card-body">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Thành viên</th>
                                <th>Liên hệ</th>
                                <th>Số dư</th>
                                <th>Vai trò</th>
                                <th width="200">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            </tbody>
                    </table>

                    <nav class="d-flex justify-content-between align-items-center mt-3">
                        <span class="text-muted" id="pageInfo">Đang tải...</span>
                        <ul class="pagination mb-0" id="pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="coinModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="users.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Giao dịch với: <span id="modalUsername" class="fw-bold text-primary"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_coin">
                        <input type="hidden" name="user_id" id="modalUserId">
                        
                        <div class="mb-3">
                            <label class="form-label">Loại giao dịch</label>
                            <select name="type" class="form-select">
                                <option value="add">Cộng tiền (+)</option>
                                <option value="minus">Trừ tiền (-)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số tiền (Coin)</label>
                            <input type="number" name="amount" class="form-control" required min="1" placeholder="Nhập số lượng...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Xác nhận</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const tableBody = document.getElementById('userTableBody');
        const pagination = document.getElementById('pagination');
        const pageInfo = document.getElementById('pageInfo');
        const loading = document.getElementById('loading-overlay');
        let searchTimeout = null;

        // Hàm gọi API
        function fetchUsers(page = 1) {
            const keyword = document.getElementById('searchInput').value;
            loading.style.display = 'flex';

            fetch(`users.php?ajax=1&page=${page}&keyword=${keyword}`)
                .then(response => response.json())
                .then(data => {
                    renderTable(data.users);
                    renderPagination(data.pagination);
                    loading.style.display = 'none';
                })
                .catch(err => {
                    console.error(err);
                    loading.style.display = 'none';
                });
        }

        // Hàm render bảng
        function renderTable(users) {
            tableBody.innerHTML = '';
            if (users.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Không tìm thấy dữ liệu</td></tr>';
                return;
            }

            users.forEach(user => {
                const roleBadge = user.role === 'ADMIN' 
                    ? '<span class="role-admin">ADMIN</span>' 
                    : '<span class="role-user">MEMBER</span>';

                // Format số tiền
                const coinFormatted = new Intl.NumberFormat('vi-VN').format(user.coin);

                const html = `
                    <tr>
                        <td>${user.user_id}</td>
                        <td>
                            <strong>${escapeHtml(user.username)}</strong><br>
                            <small class="text-muted">${escapeHtml(user.full_name || '')}</small>
                        </td>
                        <td>
                            <div>Email: ${escapeHtml(user.email)}</div>
                            <div>SĐT: ${escapeHtml(user.phone)}</div>
                        </td>
                        <td class="coin-text">${coinFormatted} xu</td>
                        <td>${roleBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="openCoinModal(${user.user_id}, '${escapeHtml(user.username)}')">
                                $ Nạp
                            </button>
                            <a href="users.php?role_id=${user.user_id}&current_role=${user.role}" 
                               onclick="return confirm('Đổi quyền user này?')"
                               class="btn btn-sm btn-warning">Quyền</a>
                            <a href="users.php?delete_id=${user.user_id}" 
                               onclick="return confirm('Cảnh báo: Xóa user sẽ xóa luôn các server của họ?')"
                               class="btn btn-sm btn-danger">Xóa</a>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', html);
            });
        }

        // Hàm render phân trang
        function renderPagination(paging) {
            pageInfo.innerText = `Hiển thị trang ${paging.current_page} / ${paging.total_pages} (Tổng ${paging.total_records} users)`;
            pagination.innerHTML = '';

            if (paging.total_pages <= 1) return;

            // Nút Prev
            const prevDisabled = paging.current_page === 1 ? 'disabled' : '';
            pagination.insertAdjacentHTML('beforeend', `
                <li class="page-item ${prevDisabled}">
                    <button class="page-link" onclick="fetchUsers(${paging.current_page - 1})">«</button>
                </li>
            `);

            // Các số trang (Logic hiển thị gọn)
            for (let i = 1; i <= paging.total_pages; i++) {
                // Chỉ hiện trang đầu, cuối, và xung quanh trang hiện tại
                if (i === 1 || i === paging.total_pages || (i >= paging.current_page - 1 && i <= paging.current_page + 1)) {
                    const active = i === paging.current_page ? 'active' : '';
                    pagination.insertAdjacentHTML('beforeend', `
                        <li class="page-item ${active}">
                            <button class="page-link" onclick="fetchUsers(${i})">${i}</button>
                        </li>
                    `);
                } else if (i === paging.current_page - 2 || i === paging.current_page + 2) {
                    pagination.insertAdjacentHTML('beforeend', `<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
            }

            // Nút Next
            const nextDisabled = paging.current_page === paging.total_pages ? 'disabled' : '';
            pagination.insertAdjacentHTML('beforeend', `
                <li class="page-item ${nextDisabled}">
                    <button class="page-link" onclick="fetchUsers(${paging.current_page + 1})">»</button>
                </li>
            `);
        }

        // Hàm mở Modal Nạp tiền (Xử lý DOM động)
        function openCoinModal(id, username) {
            document.getElementById('modalUserId').value = id;
            document.getElementById('modalUsername').innerText = username;
            var myModal = new bootstrap.Modal(document.getElementById('coinModal'));
            myModal.show();
        }

        // Hàm chống XSS đơn giản
        function escapeHtml(text) {
            if (!text) return "";
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Sự kiện tìm kiếm (Debounce: Đợi người dùng ngừng gõ 500ms mới tìm)
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchUsers(1);
            }, 500);
        });

        // Load lần đầu
        fetchUsers(1);
    </script>
</body>
</html>