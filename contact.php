<?php
require 'config/database.php';
$pageTitle = "Liên hệ";
include 'includes/header.php';
?>

<style>
    /* Custom style for social buttons on light background */
    .social-btn.light-theme {
        color: var(--primary-color);
        border-color: rgba(0,0,0,0.1);
    }
    .social-btn.light-theme:hover {
        color: #fff;
        background-color: var(--accent-color);
        border-color: var(--accent-color);
    }
</style>

<!-- Hero Section -->
<section class="hero-section" style="height: 50vh; min-height: 400px;">
    <div class="bg-image-holder" style="background-image: url('https://i.pinimg.com/736x/d5/81/8b/d5818b080b974134a260aa2374479dd0.jpg');"></div>
    <div class="overlay-dark"></div>
    <div class="container">
        <div class="row justify-content-center hero-content text-center">
            <div class="col-lg-8">
                <span class="text-uppercase letter-spacing-2 text-white-50 mb-2 d-block">Hỗ trợ khách hàng</span>
                <h1 class="hero-title display-4 mb-4">Liên hệ với chúng tôi</h1>
                <p class="lead text-white-50 fs-5">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn.</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info & Form -->
<section class="py-5">
    <div class="container py-5">
        <div class="row g-5">
            <!-- Contact Info -->
            <div class="col-lg-5">
                <div class="pe-lg-4">
                    <h6 class="text-uppercase text-accent letter-spacing-2 mb-4">Thông tin liên hệ</h6>
                    <h2 class="font-heading mb-4">Ghé thăm Showroom</h2>
                    <p class="text-muted mb-5">Đến showroom để xem trực tiếp quần áo, phụ kiện và đồ lưu niệm theo mùa sự kiện. Đội ngũ tư vấn luôn sẵn sàng hỗ trợ chọn sản phẩm phù hợp ngân sách.</p>
                    
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-location-dot text-primary"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1 font-heading">Địa chỉ</h6>
                            <p class="text-muted small mb-0"><?= $_ENV['ADDRESS_ADMIN'] ?? 'Quận 1, TP. Hồ Chí Minh, Việt Nam' ?></p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-phone text-primary"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1 font-heading">Hotline</h6>
                            <p class="text-muted small mb-0"><?= $_ENV['PHONE_ADMIN'] ?? '0912345678' ?></p>
                            <small class="text-muted" style="font-size: 0.75rem;">(8:00 - 21:00 hàng ngày)</small>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-envelope text-primary"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1 font-heading">Email</h6>
                            <p class="text-muted small mb-0"><?= $_ENV['EMAIL_ADMIN'] ?? 'support@tiendaoluxury.com' ?></p>
                        </div>
                    </div>

                    <hr class="my-5 opacity-25">

                    <h6 class="text-uppercase text-accent letter-spacing-2 mb-3">Mạng xã hội</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="social-btn light-theme"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="social-btn light-theme"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-btn light-theme"><i class="fa-brands fa-tiktok"></i></a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="bg-light p-4 p-lg-5 rounded-3 h-100">
                    <h3 class="font-heading mb-4">Gửi tin nhắn</h3>
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-luxury">Họ tên</label>
                                <input type="text" class="form-control form-control-luxury" placeholder="Nhập họ tên của bạn">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-luxury">Email</label>
                                <input type="email" class="form-control form-control-luxury" placeholder="name@example.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label-luxury">Số điện thoại</label>
                                <input type="tel" class="form-control form-control-luxury" placeholder="Số điện thoại liên hệ">
                            </div>
                            <div class="col-12">
                                <label class="form-label-luxury">Chủ đề</label>
                                <select class="form-select form-control-luxury">
                                    <option selected>Tư vấn sản phẩm</option>
                                    <option>Chế độ bảo hành</option>
                                    <option>Hợp tác kinh doanh</option>
                                    <option>Khác</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label-luxury">Nội dung</label>
                                <textarea class="form-control form-control-luxury" rows="5" placeholder="Bạn cần hỗ trợ gì?"></textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-luxury w-100">Gửi tin nhắn</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map -->
<section class="map-section">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3725.093957775924!2d105.4307523154016!3d21.32061298584065!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31348d97b5600001%3A0x632559501f010c8!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBow7luZyBWxrDGoW5n!5e0!3m2!1svi!2s!4v1646817832645!5m2!1svi!2s" width="100%" height="450" style="border:0; filter: grayscale(100%) invert(92%) contrast(83%);" allowfullscreen="" loading="lazy"></iframe>
</section>

<?php include 'includes/footer.php'; ?>
