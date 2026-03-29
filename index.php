<?php
require 'config/database.php';
$pageTitle = 'Trang chủ';
include 'includes/header.php';
include 'includes/alert.php';

// Fetch featured products (Latest 4)
$featured_products = getData('products', [
    'order_by' => 'created_at DESC',
    'limit' => 4,
]);

$themeSlug = $THEME['slug'] ?? 'default';
$homeContentByTheme = [
    'default' => [
        'hero_title' => 'Cửa Hàng Đồ Lưu Niệm & Thời Trang',
        'hero_desc' => 'Khám phá quần áo, mũ, giày, vòng cổ và quà tặng được tuyển chọn.',
        'hero_btn_primary' => 'Khám phá bộ sưu tập',
        'hero_btn_secondary' => 'Xem sản phẩm',
        'collection_title' => 'Sản phẩm nổi bật',
        'collection_desc' => 'Lựa chọn nổi bật từ thời trang và đồ lưu niệm',
        'product_specs' => 'Gợi ý hot',
        'view_details' => 'Xem chi tiết',
        'no_products' => 'Hiện chưa có sản phẩm.',
        'view_more' => 'Xem thêm',
        'highlight_tag' => 'Mới về',
        'highlight_title' => 'Phong Cách Dạo Phố & Du Lịch',
        'highlight_desc' => 'Trang phục và phụ kiện thoải mái cho sử dụng hằng ngày.',
        'highlight_link_text' => 'Khám phá ngay',
        'highlight_search' => 'cap',
        'craft_title' => 'Tuyển chọn cho cuộc sống hằng ngày',
        'craft_desc' => 'Mỗi sản phẩm được chọn theo tiêu chí đẹp, thoải mái và dễ làm quà tặng.',
        'trust_1_title' => 'Kiểm tra chất lượng',
        'trust_1_desc' => 'Sản phẩm được kiểm tra kỹ trước khi giao',
        'trust_2_title' => 'Giao hàng toàn quốc',
        'trust_2_desc' => 'Đóng gói chắc chắn, giao tận nơi',
        'trust_3_title' => 'Đổi trả dễ dàng',
        'trust_3_desc' => 'Hỗ trợ nhanh khi cần đổi size hoặc mẫu',
        'cta_title' => 'Cần tư vấn phối đồ?',
        'cta_desc' => 'Liên hệ để được gợi ý trang phục và quà tặng phù hợp.',
        'cta_btn' => 'Liên hệ ngay',
    ],
    'tet' => [
        'hero_title' => 'Bộ Sưu Tập Quà Tết',
        'hero_desc' => 'Đón Tết với trang phục lễ hội và quà lưu niệm ý nghĩa.',
        'hero_btn_primary' => 'Mua ngay dịp Tết',
        'hero_btn_secondary' => 'Xem sản phẩm Tết',
        'collection_title' => 'Sản phẩm Tết nổi bật',
        'collection_desc' => 'Gợi ý quà tặng tông đỏ - vàng cho gia đình và bạn bè',
        'product_specs' => 'Phiên bản Tết',
        'view_details' => 'Xem chi tiết',
        'no_products' => 'Bộ sưu tập Tết đang được cập nhật, vui lòng quay lại sau.',
        'view_more' => 'Xem thêm',
        'highlight_tag' => 'Tết đặc biệt',
        'highlight_title' => 'Set Trang Phục & Quà May Mắn',
        'highlight_desc' => 'Lựa chọn phù hợp cho du xuân và tặng quà đầu năm.',
        'highlight_link_text' => 'Khám phá series Tết',
        'highlight_search' => 'shirt',
        'craft_title' => 'Thiết kế cho khởi đầu mới',
        'craft_desc' => 'Sản phẩm theo mùa phù hợp không khí Tết và văn hóa quà tặng.',
        'trust_1_title' => 'Kiểm tra chất lượng',
        'trust_1_desc' => 'Chất lượng ổn định cho mùa mua sắm Tết',
        'trust_2_title' => 'Giao nhanh dịp lễ',
        'trust_2_desc' => 'Ưu tiên đơn quà tặng dịp Tết',
        'trust_3_title' => 'Sẵn sàng làm quà',
        'trust_3_desc' => 'Đóng gói đẹp để tặng ngay',
        'cta_title' => 'Tư vấn quà Tết cá nhân hóa',
        'cta_desc' => 'Đặt lịch tư vấn riêng cho quà tặng đầu năm cao cấp.',
        'cta_btn' => 'Đặt lịch ngay',
    ],
    'gpmnam' => [
        'hero_title' => 'Bộ Sưu Tập Kỷ Niệm 30/4',
        'hero_desc' => 'Sản phẩm thời trang và lưu niệm gợi cảm hứng tự hào dân tộc.',
        'hero_btn_primary' => 'Khám phá chủ đề',
        'hero_btn_secondary' => 'Xem bộ sưu tập',
        'collection_title' => 'Lựa chọn kỷ niệm',
        'collection_desc' => 'Sản phẩm phù hợp sự kiện, lễ hội và du lịch',
        'product_specs' => 'Phiên bản đặc biệt',
        'view_details' => 'Xem chi tiết',
        'no_products' => 'Sản phẩm kỷ niệm đang được cập nhật.',
        'view_more' => 'Xem thêm',
        'highlight_tag' => 'Phiên bản 30/4',
        'highlight_title' => 'Set Tự Hào & Du Lịch',
        'highlight_desc' => 'Mũ, áo và phụ kiện phù hợp cho hoạt động dịp lễ.',
        'highlight_link_text' => 'Khám phá series',
        'highlight_search' => 'cap',
        'craft_title' => 'Thiết kế mang giá trị kỷ niệm',
        'craft_desc' => 'Mẫu mã ý nghĩa, phù hợp phong cách hiện đại hằng ngày.',
        'trust_1_title' => 'Kiểm tra chất lượng',
        'trust_1_desc' => 'Kiểm soát tốt từ chất liệu đến hoàn thiện',
        'trust_2_title' => 'Giao hàng toàn quốc',
        'trust_2_desc' => 'Vận chuyển an toàn trên toàn quốc',
        'trust_3_title' => 'Hỗ trợ cao cấp',
        'trust_3_desc' => 'Chăm sóc sau bán hàng tận tâm',
        'cta_title' => 'Thiết kế theo chủ đề riêng',
        'cta_desc' => 'Tạo sản phẩm tùy chỉnh cho dịp kỷ niệm đặc biệt.',
        'cta_btn' => 'Bắt đầu đặt riêng',
    ],
    'quockhanh' => [
        'hero_title' => 'Bộ Sưu Tập Quốc Khánh 2/9',
        'hero_desc' => 'Lựa chọn thời trang và quà lưu niệm cho dịp lễ và sự kiện.',
        'hero_btn_primary' => 'Khám phá lựa chọn 2/9',
        'hero_btn_secondary' => 'Xem sản phẩm Quốc Khánh',
        'collection_title' => 'Điểm nhấn dịp Quốc Khánh',
        'collection_desc' => 'Trang phục và phụ kiện sẵn sàng cho sự kiện',
        'product_specs' => 'Bản phối 2/9',
        'view_details' => 'Xem chi tiết',
        'no_products' => 'Sản phẩm 2/9 sẽ sớm được cập nhật.',
        'view_more' => 'Xem thêm',
        'highlight_tag' => 'Đặc biệt 2/9',
        'highlight_title' => 'Set Dự Sự Kiện & Dạo Phố',
        'highlight_desc' => 'Phong cách linh hoạt cho lễ hội, du lịch và làm quà.',
        'highlight_link_text' => 'Xem set nổi bật',
        'highlight_search' => 'shoes',
        'craft_title' => 'Thanh lịch cho ngày lễ',
        'craft_desc' => 'Cân bằng giữa thoải mái và thẩm mỹ cho hoạt động dịp lễ.',
        'trust_1_title' => 'Kiểm tra chất lượng',
        'trust_1_desc' => 'Duy trì tiêu chuẩn chất lượng đồng đều',
        'trust_2_title' => 'Giao hàng bảo đảm',
        'trust_2_desc' => 'Vận chuyển tin cậy, bảo vệ đơn hàng',
        'trust_3_title' => 'Đổi trả dễ dàng',
        'trust_3_desc' => 'Hỗ trợ nhanh cho đổi mới và hoàn trả',
        'cta_title' => 'Tư vấn phối đồ riêng',
        'cta_desc' => 'Đặt lịch tư vấn 1-1 cho trang phục và quà tặng dịp lễ.',
        'cta_btn' => 'Đặt tư vấn',
    ],
    'noel' => [
        'hero_title' => 'Bộ Sưu Tập Quà Noel',
        'hero_desc' => 'Thời trang mùa lễ hội và quà lưu niệm cho dịp cuối năm.',
        'hero_btn_primary' => 'Mua ngay mùa lễ',
        'hero_btn_secondary' => 'Xem bộ sưu tập Noel',
        'collection_title' => 'Sản phẩm bán chạy mùa lễ',
        'collection_desc' => 'Trang phục ấm áp và quà tặng tinh tế cho mùa Noel',
        'product_specs' => 'Phiên bản mùa lễ',
        'view_details' => 'Xem chi tiết',
        'no_products' => 'Bộ sưu tập Noel đang được làm mới.',
        'view_more' => 'Xem thêm',
        'highlight_tag' => 'Noel đặc biệt',
        'highlight_title' => 'Set Mùa Đông & Quà Tặng',
        'highlight_desc' => 'Trang phục mùa lạnh thoải mái cùng quà lưu niệm tiện dụng.',
        'highlight_link_text' => 'Khám phá mùa đông',
        'highlight_search' => 'hoodie',
        'craft_title' => 'Thiết kế cho mùa lễ hội',
        'craft_desc' => 'Tôn lên trải nghiệm tặng quà và không khí lễ hội cuối năm.',
        'trust_1_title' => 'Kiểm tra chất lượng',
        'trust_1_desc' => 'Chất lượng đáng tin cậy cho đơn hàng mùa lễ',
        'trust_2_title' => 'Giao hàng đúng dịp',
        'trust_2_desc' => 'Ưu tiên thời gian giao trước lễ',
        'trust_3_title' => 'Hỗ trợ chọn quà',
        'trust_3_desc' => 'Tư vấn chọn quà phù hợp theo ngân sách',
        'cta_title' => 'Tư vấn quà mùa lễ',
        'cta_desc' => 'Đội ngũ của chúng tôi sẽ giúp bạn chọn quà thật ấn tượng.',
        'cta_btn' => 'Nhận hỗ trợ',
    ],
];

$homeContent = $homeContentByTheme[$themeSlug] ?? $homeContentByTheme['default'];
$heroImage = $THEME['hero'] ?? '';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="bg-image-holder<?= empty($heroImage) ? ' bg-hero' : '' ?>"<?= !empty($heroImage) ? ' style="background-image:url(\'' . htmlspecialchars($heroImage) . '\')"' : '' ?>></div>
    <div class="overlay-dark"></div>
    <div class="container">
        <div class="row justify-content-center hero-content text-center">
            <div class="col-lg-10">
                <h1 class="hero-title display-4"><?= htmlspecialchars($homeContent['hero_title']) ?></h1>
                <p class="lead mb-5 text-white-50 fs-4"><?= htmlspecialchars($homeContent['hero_desc']) ?></p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="#collection" class="btn btn-luxury btn-luxury-white"><?= htmlspecialchars($homeContent['hero_btn_primary']) ?></a>
                    <a href="category.php" class="btn btn-luxury btn-luxury-white" style="background: rgba(255,255,255,0.1); border: none;"><?= htmlspecialchars($homeContent['hero_btn_secondary']) ?></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5" id="collection">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="mb-3"><?= htmlspecialchars($homeContent['collection_title']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($homeContent['collection_desc']) ?></p>
        </div>

        <div class="row g-4">
            <?php if (!empty($featured_products)): ?>
                <?php foreach ($featured_products as $product): ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="card product-card h-100">
                            <div class="product-img-wrapper">
                                <img src="assets/images/<?= !empty($product['image']) ? htmlspecialchars($product['image']) : 'placeholder.jpg' ?>"
                                     class="product-img"
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            </div>
                            <div class="card-body product-info">
                                <span class="product-specs"><?= htmlspecialchars($homeContent['product_specs']) ?></span>
                                <h5 class="product-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <p class="product-price"><?= formatVND($product['price']) ?></p>

                                <div class="product-actions">
                                    <a href="product.php?id=<?= $product['id'] ?>" class="btn-view-details"><?= htmlspecialchars($homeContent['view_details']) ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p><?= htmlspecialchars($homeContent['no_products']) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="category.php" class="btn btn-luxury"><?= htmlspecialchars($homeContent['view_more']) ?></a>
        </div>
    </div>
</section>

<!-- Featured Collection (Background Based) -->
<section class="section-bg">
    <div class="bg-image-holder bg-collection"></div>
    <div class="overlay-dark" style="background: rgba(0,0,0,0.5);"></div>
    <div class="container section-content">
        <div class="row align-items-center">
            <div class="col-md-6 text-white">
                <span class="text-uppercase letter-spacing-2 mb-2 d-block text-warning"><?= htmlspecialchars($homeContent['highlight_tag']) ?></span>
                <h2 class="display-5 mb-4"><?= htmlspecialchars($homeContent['highlight_title']) ?></h2>
                <p class="mb-4 text-white-50"><?= htmlspecialchars($homeContent['highlight_desc']) ?></p>
                <a href="category.php?search=<?= urlencode($homeContent['highlight_search']) ?>" class="btn btn-luxury btn-luxury-white"><?= htmlspecialchars($homeContent['highlight_link_text']) ?></a>
            </div>
        </div>
    </div>
</section>

<!-- Brand Story / Craftsmanship -->
<section class="section-bg text-center">
    <div class="bg-image-holder bg-craft"></div>
    <div class="overlay-dark" style="background: rgba(0,0,0,0.6);"></div>
    <div class="container section-content">
        <div class="brand-story-text">
            <i class="fa-regular fa-gem fs-1 mb-4 text-warning"></i>
            <h2 class="mb-4"><?= htmlspecialchars($homeContent['craft_title']) ?></h2>
            <p class="lead text-white-50"><?= htmlspecialchars($homeContent['craft_desc']) ?></p>
        </div>
    </div>
</section>

<!-- Trust Section -->
<section class="py-5">
    <div class="container text-center">
        <div class="row">
            <div class="col-md-4 mb-3">
                <i class="fa-solid fa-certificate fs-2 mb-3 d-block" style="color: var(--accent-color)"></i>
                <h5><?= htmlspecialchars($homeContent['trust_1_title']) ?></h5>
                <small class="text-muted"><?= htmlspecialchars($homeContent['trust_1_desc']) ?></small>
            </div>
            <div class="col-md-4 mb-3">
                <i class="fa-solid fa-earth-americas fs-2 mb-3 d-block" style="color: var(--accent-color)"></i>
                <h5><?= htmlspecialchars($homeContent['trust_2_title']) ?></h5>
                <small class="text-muted"><?= htmlspecialchars($homeContent['trust_2_desc']) ?></small>
            </div>
            <div class="col-md-4 mb-3">
                <i class="fa-regular fa-gem fs-2 mb-3 d-block" style="color: var(--accent-color)"></i>
                <h5><?= htmlspecialchars($homeContent['trust_3_title']) ?></h5>
                <small class="text-muted"><?= htmlspecialchars($homeContent['trust_3_desc']) ?></small>
            </div>
        </div>
    </div>
</section>

<!-- Bottom Call To Action -->
<section class="section-bg text-center py-5">
    <div class="bg-image-holder bg-cta"></div>
    <div class="overlay-dark" style="background: rgba(17, 17, 17, 0.8);"></div>
    <div class="container section-content">
        <h2 class="mb-3"><?= htmlspecialchars($homeContent['cta_title']) ?></h2>
        <p class="mb-4 text-white-50"><?= htmlspecialchars($homeContent['cta_desc']) ?></p>
        <a href="contact.php" class="btn btn-luxury btn-luxury-white"><?= htmlspecialchars($homeContent['cta_btn']) ?></a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
