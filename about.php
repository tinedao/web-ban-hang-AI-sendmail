<?php
require 'config/database.php';
$pageTitle = "Về chúng tôi";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section" style="height: 60vh; min-height: 400px;">
    <div class="bg-image-holder" style="background-image: url('https://images.unsplash.com/photo-1601121141461-9d6647bca1ed?q=80&w=1920&auto=format&fit=crop');"></div>
    <div class="overlay-dark"></div>
    <div class="container">
        <div class="row justify-content-center hero-content text-center">
            <div class="col-lg-8">
                <span class="text-uppercase letter-spacing-2 text-white-50 mb-2 d-block">Câu chuyện thương hiệu</span>
                <h1 class="hero-title display-4 mb-4">Di sản của sự tinh tế</h1>
                <p class="lead text-white-50 fs-5">Hơn 30 năm phát triển sản phẩm lưu niệm và thời trang cho các dịp sự kiện trong năm.</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Story -->
<section class="py-5">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="position-relative pe-lg-4">
                    <div class="ratio ratio-4x3">
                        <img src="https://i.pinimg.com/736x/d5/81/8b/d5818b080b974134a260aa2374479dd0.jpg" alt="Chế tác trang sức" class="img-fluid rounded shadow-lg object-fit-cover">
                    </div>
                    <div class="position-absolute bottom-0 end-0 bg-white p-4 shadow-lg rounded d-none d-md-block" style="max-width: 280px; margin-bottom: -30px; margin-right: -20px; border-left: 4px solid var(--accent-color);">
                        <p class="font-heading mb-0 text-primary fs-5 fst-italic">"Trang sức không chỉ là phụ kiện, đó là cảm xúc được kết tinh."</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <h6 class="text-uppercase text-accent letter-spacing-2 mb-2">Từ năm 1995</h6>
                <h2 class="display-5 font-heading mb-4">Hành trình đam mê</h2>
                <p class="text-muted mb-4">
                    Được thành lập với tầm nhìn mang các bộ sưu tập quà tặng và thời trang sự kiện đến gần hơn với mọi khách hàng, <strong><?= $_ENV['APP_NAME'] ?></strong> đã trở thành địa chỉ tin cậy cho các dịp lễ quanh năm.
                </p>
                <p class="text-muted mb-4">
                    Hành trình của chúng tôi bắt đầu từ một xưởng sản xuất nhỏ với niềm đam mê tạo ra sản phẩm có ý nghĩa cho từng mùa lễ hội. Qua nhiều năm, đội ngũ đã mở rộng danh mục từ quần áo, phụ kiện đến đồ trang trí lưu niệm để khách hàng dễ dàng chọn mua và làm quà tặng.
                </p>
                <div class="row g-4 mt-2">
                    <div class="col-6">
                        <h3 class="font-heading mb-1 text-primary">25+</h3>
                        <p class="small text-muted text-uppercase letter-spacing-1">Năm kinh nghiệm</p>
                    </div>
                    <div class="col-6">
                        <h3 class="font-heading mb-1 text-primary">5k+</h3>
                        <p class="small text-muted text-uppercase letter-spacing-1">Khách hàng hài lòng</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values -->
<section class="section-bg text-center">
    <div class="bg-image-holder" style="background-image: url('https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?q=80&w=1920&auto=format&fit=crop');"></div>
    <div class="overlay-dark" style="background: rgba(17, 17, 17, 0.9);"></div>
    <div class="container section-content py-5">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <h2 class="mb-3">Giá trị cốt lõi</h2>
                <p class="text-white-50">Chúng tôi tin vào sự minh bạch, chất lượng ổn định và giá trị sử dụng thực tế cho từng sản phẩm sự kiện.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="p-4 border border-secondary border-opacity-25 h-100 hover-lift transition-all">
                    <i class="fa-regular fa-gem fs-1 mb-4 text-accent"></i>
                    <h4 class="h5 mb-3">Chất lượng tuyệt đối</h4>
                    <p class="text-white-50 small mb-0">Mỗi sản phẩm đều được kiểm tra chất lượng, đường may/chất liệu và đóng gói cẩn thận trước khi giao.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border border-secondary border-opacity-25 h-100 hover-lift transition-all">
                    <i class="fa-solid fa-hand-holding-heart fs-1 mb-4 text-accent"></i>
                    <h4 class="h5 mb-3">Nguồn gốc đạo đức</h4>
                    <p class="text-white-50 small mb-0">Chúng tôi cam kết nguồn cung ứng không xung đột và hỗ trợ cộng đồng khai thác bền vững.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border border-secondary border-opacity-25 h-100 hover-lift transition-all">
                    <i class="fa-solid fa-compass-drafting fs-1 mb-4 text-accent"></i>
                    <h4 class="h5 mb-3">Thiết kế độc bản</h4>
                    <p class="text-white-50 small mb-0">Các nghệ nhân bậc thầy của chúng tôi hiện thực hóa tầm nhìn của bạn với sự chính xác và tinh tế nghệ thuật.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Founder -->
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-5">
                            <img src="assets/images/founder.jpg" class="img-fluid h-100 object-fit-cover" alt="Founder" style="min-height: 300px;">
                        </div>
                        <div class="col-md-7 d-flex align-items-center">
                            <div class="card-body p-4 p-lg-5">
                                <h6 class="text-uppercase text-muted letter-spacing-2 mb-2">Người sáng lập</h6>
                                <h3 class="font-heading mb-3">Tiến Đào</h3>
                                <p class="text-muted mb-4 fst-italic">"Đam mê của tôi là tạo ra các bộ sưu tập giúp mọi người lưu giữ khoảnh khắc trong từng dịp lễ. Mỗi sản phẩm phải đẹp, dễ dùng và có thể trở thành món quà đáng nhớ."</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
