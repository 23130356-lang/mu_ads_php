<style>
    /* === FOOTER STYLES === */
    .mu-footer {
        background-color: #050505;
        background-image: linear-gradient(0deg, #1a0505 0%, #000 100%);
        border-top: 2px solid var(--mu-gold-dark, #8a6d3b);
        color: #888;
        font-family: 'Rajdhani', sans-serif;
        padding-top: 3rem;
        padding-bottom: 1.5rem;
        margin-top: auto; /* QUAN TRỌNG: Đẩy footer xuống đáy khi dùng Flexbox */
        position: relative;
    }

    /* Hiệu ứng ánh sáng ngăn cách */
    .mu-footer::before {
        content: '';
        position: absolute;
        top: -2px; left: 0; width: 100%; height: 2px;
        background: linear-gradient(90deg, transparent, var(--mu-gold, #ffcc00), transparent);
        box-shadow: 0 0 15px var(--mu-gold, #ffcc00);
    }

    .footer-title {
        font-family: 'Cinzel', serif;
        font-weight: 700;
        color: var(--mu-gold, #ffcc00);
        margin-bottom: 1.2rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 1.1rem;
        text-shadow: 0 0 5px rgba(207, 170, 86, 0.3);
    }

    .footer-desc {
        font-size: 0.9rem;
        line-height: 1.6;
        color: #666;
    }

    .footer-links {
        list-style: none;
        padding: 0;
    }

    .footer-links li {
        margin-bottom: 0.8rem;
    }

    .footer-links a {
        color: #999;
        text-decoration: none;
        transition: all 0.3s;
        display: flex;
        align-items: center;
    }

    .footer-links a:hover {
        color: var(--mu-gold, #ffcc00);
        padding-left: 5px; /* Hiệu ứng trượt nhẹ */
    }

    .footer-links a i { font-size: 0.8rem; margin-right: 8px; color: var(--mu-red, #8b0000); }

    .footer-social .social-btn {
        display: inline-flex; justify-content: center; align-items: center;
        width: 36px; height: 36px;
        background: rgba(255,255,255,0.05);
        border: 1px solid #333;
        color: #aaa;
        border-radius: 4px;
        margin-right: 8px;
        transition: 0.3s;
        text-decoration: none;
    }
    .footer-social .social-btn:hover {
        background: var(--mu-red, #8b0000);
        color: #fff;
        border-color: var(--mu-red, #8b0000);
        box-shadow: 0 0 10px rgba(139, 0, 0, 0.5);
    }

    .copyright-bar {
        border-top: 1px solid #222;
        margin-top: 2rem;
        padding-top: 1.5rem;
        text-align: center;
        font-size: 0.85rem;
        color: #555;
    }
    .copyright-bar a { color: #777; text-decoration: none; }
    .copyright-bar a:hover { color: #ccc; }
</style>

<footer class="mu-footer">
    <div class="container">
        <div class="row gy-4">

            <div class="col-lg-4 col-md-6">
                <h5 class="footer-title"><i class="fa-solid fa-dragon me-2"></i> MUMOIRA.MOBI PORTAL</h5>
                <p class="footer-desc">
                    Cổng thông tin Mu Online hàng đầu Việt Nam. Nơi cập nhật lịch ra mắt các máy chủ mới nhất, uy tín nhất và đông người chơi nhất.
                </p>
                <div class="footer-social mt-3">
                    <a href="#" class="social-btn"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="social-btn"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#" class="social-btn"><i class="fa-brands fa-discord"></i></a>
                    <a href="#" class="social-btn"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 ps-lg-5">
                <h5 class="footer-title">DANH MỤC</h5>
                <ul class="footer-links">
                    <li><a href="index.php"><i class="fa-solid fa-chevron-right"></i> Trang Chủ</a></li>
                    <li><a href="index.php?versionId=1#result-listw"><i class="fa-solid fa-chevron-right"></i> Server Mới Ra</a></li>
                    <li><a href="index.php?url=banner-register"><i class="fa-solid fa-chevron-right"></i> Thuê Quảng Cáo</a></li>
                    <li><a href="index.php?url=login"><i class="fa-solid fa-chevron-right"></i> Đăng Nhập Admin</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h5 class="footer-title">LIÊN HỆ HỖ TRỢ</h5>
                <ul class="footer-links">
                    <li>
                        <div class="d-flex text-secondary">
                            <i class="fa-solid fa-envelope mt-1 me-2 text-danger"></i>
                            <span>ads.mumoira@gmail.com</span>
                        </div>
                    </li>
                    <li>
                        <div class="d-flex text-secondary">
                            <i class="fa-brands fa-telegram mt-1 me-2 text-info"></i>
                            <span>@MuMoiRaSupport (Telegram)</span>
                        </div>
                    </li>
                    <li class="mt-3">
                        <div class="p-2 border border-secondary rounded bg-black bg-opacity-25 text-center">
                            <small class="text-warning text-uppercase fw-bold d-block mb-1">Thời gian làm việc</small>
                            <span class="small">08:00 - 22:00 (Hàng ngày)</span>
                        </div>
                    </li>
                </ul>
            </div>

        </div>

        <div class="copyright-bar">
            &copy; 2025 <strong>MuMoiRa.mobi</strong>. All Rights Reserved. <br>
            <span style="font-size: 0.75rem;">Designed for true MU Fans.</span>
        </div>
    </div>
</footer>