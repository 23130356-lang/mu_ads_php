<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng Dẫn & Quy Định | MUNORIA.MOBILE</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* === 1. GLOBAL THEME === */
        :root {
            --mu-bg: #050505;
            --mu-gold: #cfaa56;
            --mu-red: #8b0000;
            --mu-red-bright: #dc3545;
            --mu-glass: rgba(15, 15, 15, 0.95);
            --mu-border: #3d2b1f;
        }

        body {
            background-color: var(--mu-bg);
            color: #d1d1d1;
            font-family: 'Rajdhani', sans-serif;
            background-image: linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(0,0,0,0.95)), url('https://wallpaperaccess.com/full/1524368.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }

        h1, h2, h3, h4 {
            font-family: 'Cinzel', serif;
            text-transform: uppercase;
        }

        .text-gold { color: var(--mu-gold) !important; }
        .text-red { color: var(--mu-red-bright) !important; }

        /* === 2. SIDEBAR STYLES === */
        .guide-sidebar {
            position: sticky;
            top: 100px; /* Điều chỉnh vị trí sticky nếu header che mất */
            background: rgba(10, 10, 10, 0.9);
            border: 1px solid var(--mu-border);
            border-top: 3px solid var(--mu-red);
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
            z-index: 10;
        }

        .sidebar-header {
            background: linear-gradient(45deg, #3d0a0a, #1a0505);
            padding: 15px;
            color: var(--mu-gold);
            font-weight: 700;
            text-align: center;
            border-bottom: 1px solid var(--mu-border);
            font-family: 'Cinzel', serif;
        }

        .list-group-item {
            background-color: transparent;
            color: #aaa;
            border: none;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
        }

        .list-group-item:hover {
            background-color: rgba(139, 0, 0, 0.1);
            color: #fff;
            padding-left: 25px; /* Hiệu ứng trượt sang phải */
        }

        .list-group-item:hover::before {
            content: "►";
            position: absolute;
            left: 10px;
            color: var(--mu-red-bright);
            font-size: 0.8rem;
        }

        /* === 3. CONTENT BOX === */
        .content-section {
            background: var(--mu-glass);
            border: 1px solid var(--mu-border);
            border-radius: 4px;
            padding: 40px;
            margin-bottom: 30px;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.8);
            scroll-margin-top: 110px; /* Để khi click mục lục không bị header che mất tiêu đề */
        }

        /* Decorative Corners */
        .content-section::after {
            content: ''; position: absolute; top: -1px; right: -1px;
            width: 20px; height: 20px;
            border-top: 2px solid var(--mu-gold);
            border-right: 2px solid var(--mu-gold);
        }
        .content-section::before {
            content: ''; position: absolute; bottom: -1px; left: -1px;
            width: 20px; height: 20px;
            border-bottom: 2px solid var(--mu-gold);
            border-left: 2px solid var(--mu-gold);
        }

        .section-header {
            border-bottom: 1px solid rgba(207, 170, 86, 0.3);
            padding-bottom: 15px;
            margin-bottom: 25px;
            color: #e0e0e0;
            display: flex;
            align-items: center;
        }
        .section-header i { color: var(--mu-red-bright); margin-right: 15px; font-size: 1.5rem; }

        /* === 4. TABLE STYLES === */
        .table-custom {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            border: 1px solid #333;
        }
        .table-custom thead {
            background: linear-gradient(to bottom, #2c1a1a, #1a0f0f);
        }
        .table-custom th {
            color: var(--mu-gold);
            font-family: 'Cinzel', serif;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid var(--mu-red);
        }
        .table-custom td {
            background: rgba(255,255,255,0.02);
            padding: 15px;
            border-bottom: 1px solid #333;
            color: #ccc;
            vertical-align: middle;
        }

        /* VIP Highlighting */
        .row-vip td { background: rgba(207, 170, 86, 0.05); }
        .row-super-vip td { background: rgba(139, 0, 0, 0.1); border-top: 1px solid var(--mu-red); border-bottom: 1px solid var(--mu-red); }

        /* === 5. BANK CARD === */
        .bank-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
            border: 1px dashed var(--mu-gold);
            padding: 25px;
            border-radius: 8px;
            position: relative;
        }
        .copy-btn {
            background: #333; color: #fff; border: none; padding: 2px 8px; font-size: 0.8rem; cursor: pointer;
        }

        /* === 6. FAQ ACCORDION === */
        .accordion-button {
            background-color: rgba(255,255,255,0.05);
            color: var(--mu-gold);
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            border: none;
        }
        .accordion-button:not(.collapsed) {
            background-color: rgba(139, 0, 0, 0.2);
            color: #fff;
            box-shadow: none;
        }
        .accordion-body {
            background-color: rgba(0,0,0,0.6);
            color: #ccc;
            border-top: 1px solid #333;
        }
        .accordion-item {
            background: transparent;
            border: 1px solid #333;
            margin-bottom: 10px;
        }

        /* Highlight box */
        .alert-mu {
            background: rgba(139, 0, 0, 0.15);
            border-left: 4px solid var(--mu-red-bright);
            color: #e0e0e0;
            padding: 15px;
            margin: 20px 0;
        }
        .text-muted {
            color: #e6e6e6 !important;
        }
        
        /* Fix khoảng cách Header theo yêu cầu cũ */
     
    </style>
</head>
<body>


<div class="container py-5 main-content-wrapper" style="margin-top: 80px;">
    <div class="row">

        <div class="col-lg-3 mb-4">
            <div class="guide-sidebar list-group">
                <div class="sidebar-header">
                    <i class="fa-solid fa-book-open me-2"></i> MỤC LỤC
                </div>
                <a href="#section-rules" class="list-group-item list-group-item-action">
                    1. Quy định đăng tin
                </a>
                <a href="#section-process" class="list-group-item list-group-item-action">
                    2. Quy trình duyệt bài
                </a>
                <a href="#section-packages" class="list-group-item list-group-item-action">
                    3. Bảng giá & Quyền lợi
                </a>
                <a href="#section-payment" class="list-group-item list-group-item-action">
                    4. Hướng dẫn nạp Xu
                </a>
                <a href="#section-faq" class="list-group-item list-group-item-action">
                    5. Câu hỏi thường gặp (FAQ)
                </a>
                <a href="#section-contact" class="list-group-item list-group-item-action">
                    6. Liên hệ hỗ trợ
                </a>
            </div>
        </div>

        <div class="col-lg-9">

            <div id="section-rules" class="content-section">
                <h2 class="section-header"><i class="fa-solid fa-scale-balanced"></i> 1. Quy định đăng tin</h2>
                <p>Chào mừng các Admin MU Online đến với <strong>MUNORIA.MOBILE</strong>. Để xây dựng một cộng đồng game lành mạnh, uy tín và chất lượng, chúng tôi yêu cầu tất cả các bài đăng phải tuân thủ nghiêm ngặt các quy định sau:</p>

                <ul class="list-unstyled mt-3">
                    <li class="mb-3"><i class="fa-solid fa-check text-gold me-2"></i> <strong>Thông tin trung thực:</strong> Các thông tin về phiên bản (Season), tỉ lệ Drop, Exp, tính năng reset/keep point phải chính xác 100% với thực tế trong game.</li>
                    <li class="mb-3"><i class="fa-solid fa-check text-gold me-2"></i> <strong>Chất lượng hình ảnh:</strong> Banner và Logo phải rõ nét, không bị vỡ hạt, không chứa các hình ảnh phản cảm, đồi trụy hoặc vi phạm thuần phong mỹ tục.</li>
                    <li class="mb-3"><i class="fa-solid fa-check text-gold me-2"></i> <strong>Nghiêm cấm lừa đảo:</strong> Tuyệt đối cấm các server "mì ăn liền" (mở vài ngày để thu tiền rồi đóng). Nếu bị report và xác minh là lừa đảo, chúng tôi sẽ công khai danh tính (Blacklist) và cấm vĩnh viễn.</li>
                    <li class="mb-3"><i class="fa-solid fa-check text-gold me-2"></i> <strong>Không spam:</strong> Không tạo nhiều tài khoản để đăng cùng 1 server. Mỗi server chỉ được tồn tại 1 bài đăng duy nhất trong cùng thời điểm.</li>
                </ul>

                <div class="alert-mu">
                    <i class="fa-solid fa-triangle-exclamation text-red me-2"></i>
                    <strong>Lưu ý quan trọng:</strong> Ban Quản Trị có quyền từ chối duyệt hoặc xóa bài đăng mà không cần báo trước nếu phát hiện vi phạm. Xu đã thanh toán cho các bài vi phạm sẽ <u>không được hoàn lại</u>.
                </div>
            </div>

            <div id="section-process" class="content-section">
                <h2 class="section-header"><i class="fa-solid fa-clipboard-check"></i> 2. Quy trình duyệt bài</h2>
                <p>Để bài viết của bạn được hiển thị lên trang chủ, vui lòng thực hiện theo các bước sau:</p>
                <div class="row text-center mt-4">
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border border-secondary rounded bg-dark">
                            <h1 class="text-gold mb-2">01</h1>
                            <h5 class="fw-bold">Đăng ký & Nạp Xu</h5>
                            <p class="small text-muted">Tạo tài khoản và nạp đủ số Xu tương ứng với gói quảng cáo bạn muốn chọn.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border border-secondary rounded bg-dark">
                            <h1 class="text-gold mb-2">02</h1>
                            <h5 class="fw-bold">Gửi bài viết</h5>
                            <p class="small text-muted">Điền đầy đủ thông tin Server tại trang "Khởi Tạo Máy Chủ" và bấm Gửi.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border border-secondary rounded bg-dark">
                            <h1 class="text-gold mb-2">03</h1>
                            <h5 class="fw-bold">Chờ duyệt (5-30p)</h5>
                            <p class="small text-muted">Admin sẽ kiểm tra nội dung. Nếu hợp lệ, bài sẽ tự động lên TOP ngay lập tức.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-packages" class="content-section">
                <h2 class="section-header"><i class="fa-solid fa-crown"></i> 3. Các gói quảng cáo</h2>
                <p>Chúng tôi cung cấp các gói hiển thị giúp Server của bạn tiếp cận hàng nghìn game thủ đam mê MU mỗi ngày. Hãy chọn gói phù hợp với ngân sách của bạn.</p>

                <div class="table-responsive mt-4">
                    <table class="table-custom">
                        <thead>
                        <tr>
                            <th>Tên Gói</th>
                            <th>Chi phí (Xu)</th>
                            <th>Thời gian</th>
                            <th>Quyền lợi đặc biệt</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="fw-bold">CƠ BẢN</td>
                            <td>1.000</td>
                            <td>7 Ngày</td>
                            <td class="text-start ps-4">
                                <small>• Hiển thị danh sách thường</small><br>
                                <small>• Link trỏ về Website/Fanpage</small>
                            </td>
                        </tr>
                        <tr class="row-vip">
                            <td class="fw-bold text-gold"><i class="fa-solid fa-star me-1"></i> VIP</td>
                            <td class="fw-bold text-gold">5.000</td>
                            <td>10 Ngày</td>
                            <td class="text-start ps-4">
                                <small class="text-gold">• Ưu tiên xếp trên gói thường</small><br>
                                <small>• Khung viền vàng nổi bật</small><br>
                                <small>• Tên Server in đậm màu vàng</small>
                            </td>
                        </tr>
                        <tr class="row-super-vip">
                            <td class="fw-bold text-red"><i class="fa-solid fa-crown me-1"></i> SUPER VIP</td>
                            <td class="fw-bold text-red">10.000</td>
                            <td>14 Ngày</td>
                            <td class="text-start ps-4">
                                <strong class="text-red">• VỊ TRÍ TOP 1 TRANG CHỦ</strong><br>
                                <small>• Huy hiệu S-VIP Độc quyền</small><br>
                                <small>• Hiệu ứng phát sáng (Glowing)</small><br>
                                <small>• Hỗ trợ ghim bài trên Fanpage</small>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="section-payment" class="content-section">
                <h2 class="section-header"><i class="fa-solid fa-money-bill-transfer"></i> 4. Hướng dẫn nạp Xu</h2>
                <p>Hệ thống nạp tiền tự động qua chuyển khoản ngân hàng 24/7. Tỉ lệ quy đổi: <strong class="text-gold">1.000 VNĐ = 1 Xu</strong>.</p>

                <div class="bank-card mt-3">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h4 class="text-gold mb-3">THÔNG TIN CHUYỂN KHOẢN</h4>
                            <div class="mb-2">
                                <span class="text-muted d-inline-block" style="width: 120px;">Ngân hàng:</span>
                                <strong class="text-white">BIDV - CN Sở Giao Dịch 1</strong>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted d-inline-block" style="width: 120px;">Số tài khoản:</span>
                                <strong class="text-gold fs-5">052205001613</strong>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted d-inline-block" style="width: 120px;">Chủ tài khoản:</span>
                                <strong class="text-uppercase text-white">LE THANH TRUONG</strong>
                            </div>
                            <div class="mt-3 p-2 bg-dark border border-secondary rounded">
                                <span class="text-muted me-2">Nội dung CK:</span>
                                <code class="text-red fs-5 fw-bold">NAP [Tên_Tài_Khoản]</code>
                            </div>
                        </div>
                        <div class="col-md-5 text-center mt-3 mt-md-0">
                            <div style="background: white; padding: 10px; display: inline-block; border-radius: 4px;">
                                <img src="uploads/qr.png" 
                                     alt="QR Code"
                                     width="180">
                            </div>
                            <p class="small text-muted mt-2">Quét QR để chuyển khoản nhanh</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-faq" class="content-section">
                <h2 class="section-header"><i class="fa-solid fa-circle-question"></i> 5. Câu hỏi thường gặp (FAQ)</h2>

                <div class="accordion mu-accordion" id="accordionFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Tôi nạp tiền nhưng quên ghi nội dung chuyển khoản?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body">
                                Đừng lo lắng! Hãy chụp lại biên lai chuyển khoản (rõ mã giao dịch) và gửi ngay cho Admin qua Fanpage hoặc Telegram. Chúng tôi sẽ kiểm tra và cộng Xu thủ công cho bạn trong vòng 15 phút.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Tôi có thể sửa bài viết sau khi đã đăng không?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body">
                                Có. Bạn có thể vào mục "Quản lý bài đăng" để chỉnh sửa thông tin như: Ngày Open, Link tải, Slogan... Tuy nhiên, nếu thay đổi tên Server hoặc Gói quảng cáo, bài viết sẽ cần được duyệt lại.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Gói Super VIP có được bảo hành vị trí không?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body">
                                Gói Super VIP cam kết hiển thị trong TOP 5 vị trí đầu tiên của trang chủ. Thứ tự giữa các server Super VIP sẽ được sắp xếp ngẫu nhiên (Random) sau mỗi lần tải lại trang để đảm bảo công bằng cho tất cả khách hàng.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Quảng cáo có tác dụng với những phiên bản Mu season mấy?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body">
                                <p>Thống kê của chúng tôi chỉ ra rằng đã có rất nhiều phiên bản Mu từng quảng cáo tại đây, bao gồm các phiên bản <strong>Season 2 rất cũ</strong> cho đến phiên bản <strong>Season 15, 16 mới nhất</strong>.</p>
                                <p class="mb-0">Do đó, việc bạn chọn phiên bản nào thì website vẫn đáp ứng được do số lượng gamer tìm kiếm Mu mới ra hàng ngày tại Mumoira.tv rất đông.</p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Server Mu Online mới làm, chưa có thương hiệu thì quảng cáo có hiệu quả không?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body">
                                <p>Các server Mu Online quảng cáo tại đây đa số đến từ các thương hiệu mới. Thực tế gamer rất thích trải nghiệm liên tục các server Mu mới (khái niệm "nhảy server"), do đó việc bạn mới bắt đầu làm game sẽ rất thích hợp để quảng cáo tại đây.</p>
                                <div class="alert alert-warning bg-transparent border-warning text-warning p-2 mt-2">
                                    <i class="fa-solid fa-lightbulb me-2"></i><strong>Gợi ý:</strong> Bạn nên đăng bài giới thiệu về server tại mục <em>đăng bài miễn phí</em> trước. Hãy giới thiệu thật rõ ràng, chi tiết những yếu tố hay và lôi cuốn. Bài viết càng hay thì càng được nhiều gamer quan tâm và muốn ghé chơi thử.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                Tôi có thể yêu cầu xem thống kê chất lượng quảng cáo không?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body">
                                Hoàn toàn có thể. Quảng cáo của bạn sẽ được đo đạc tự động bằng công cụ <strong>bitly.com</strong> để đảm bảo tính khách quan và chính xác. Khi bạn cần xem kết quả, chúng tôi sẽ xuất báo cáo để gửi cho bạn.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                Thời gian treo Banner/VIP quảng cáo tối thiểu là bao lâu?
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body">
                                <p>Thời gian tối thiểu là <strong>1 tháng</strong>. Lưu ý: Nếu tất cả vị trí quảng cáo đã đầy, bạn cần phải đợi cho đến khi có vị trí trống.</p>
                                <p class="mb-0 text-white-50"><em>Lời khuyên: Bạn nên tính toán thời gian Open Game để liên hệ đặt chỗ sớm nhất có thể.</em></p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                Tôi cần liên hệ ai để tiến hành treo quảng cáo?
                            </button>
                        </h2>
                        <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body">
                                <p>Hệ thống treo Banner, VIP Vàng cũng như nạp Coin của <strong>Mumoira.tv</strong> là hoàn toàn tự động 100%.</p>
                                <ul>
                                    <li>Ở phần trên cùng của trang này có thể hiện số slot đã treo và thời gian trống nếu đang Full chỗ.</li>
                                    <li>Bạn chỉ cần <strong>Đăng nhập</strong> vào website để có thể đăng bài miễn phí và thuê vị trí quảng cáo ngay lập tức.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-contact" class="content-section text-center">
                <h2 class="section-header justify-content-center"><i class="fa-solid fa-headset"></i> 6. Liên hệ hỗ trợ</h2>
                <p class="mb-4">Đội ngũ hỗ trợ của chúng tôi hoạt động từ <strong>08:00 - 24:00</strong> hàng ngày (Kể cả Lễ, Tết).</p>

                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="#" class="btn btn-outline-light px-4 py-2">
                        <i class="fa-brands fa-facebook text-primary me-2"></i> Fanpage
                    </a>
                    <a href="#" class="btn btn-outline-light px-4 py-2">
                        <i class="fa-brands fa-telegram text-info me-2"></i> Telegram
                    </a>
                    <a href="#" class="btn btn-outline-light px-4 py-2">
                        <i class="fa-solid fa-envelope text-danger me-2"></i> Gửi Email
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

  <?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>