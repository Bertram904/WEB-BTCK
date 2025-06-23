<?php
session_start();

// Authentication check
$isLoggedIn = isset($_SESSION['user_id']);
if (!$isLoggedIn && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $customer = new Customer();
    $timeRange = 30; // Default to 30 days
    
    // Get top customers
    $topCustomers = $customer->getTopCustomers($timeRange);
    
} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html(lang='vi')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matcha Vibe Co., Ltd. - Premium Matcha Products</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"/>
    <link href="assets/css/style_index.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>

</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <img src="https://th.bing.com/th/id/OIP.joBZlDpB5bVLwkpt4r3w8gHaHa?w=626&h=626&rs=1&pid=ImgDetMain" alt="Matcha Vibe Logo" class="h-12 w-12 rounded-full">
                <span class="text-2xl font-bold text-white">Matcha Vibe</span>
            </div>
            <div class="hidden md:flex items-center space-x-2 nav-links">
                <a href="dashboard.php">Quản lý khách hàng</a>
                <a href="statistics.php">Thống kê</a>

 
                <a href="settings.php">Cài đặt</a>
                <?php if ($isLoggedIn): ?>
                    <a href="?logout=true" class="text-red-200 hover:bg-red-600">Đăng xuất</a>
                <?php else: ?>
                    <a href="login.php">Đăng nhập</a>
                <?php endif; ?>
                <a href="#cart"><i data-feather="shopping-cart" class="ml-2"></i></a>
            </div>
            <div class="md:hidden hamburger">
                <i data-feather="menu" class="text-ecfdf5 h-8 w-8 cursor-pointer"></i>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10">
            <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 fade-in">Chào Mừng Đến Với Matcha Vibe</h1>
            <p class="text-xl text-gray-700 mb-8 fade-in">Khám phá hương vị matcha thượng hạng từ Nhật Bản, mang đến sức khỏe và sự thư thái.</p>
            <a href="#products" class="bg-green-600 text-white btn hover:bg-green-700">Khám phá ngay</a>
        </div>
    </section>

    <!-- Top Customers Section -->
    <section class="section bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card fade-in">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i data-feather="award" class="w-6 h-6 mr-2 text-green-600"></i>
                        Top 3 Khách Hàng (<?php echo $timeRange; ?> ngày qua)
                    </h2>
                    <a href="statistics.php" class="text-green-600 hover:text-green-700 flex items-center">
                        <span class="mr-2">Xem thêm</span>
                        <i data-feather="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($topCustomers)): ?>
                    <div class="text-center py-8">
                        <i data-feather="users" class="w-12 h-12 text-gray-400 mx-auto mb-2"></i>
                        <p class="text-lg font-medium text-gray-900">Chưa có dữ liệu khách hàng</p>
                        <p class="text-sm text-gray-500">Thêm khách hàng mới để xem thống kê</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php 
                        $medals = [
                            ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-600', 'icon' => 'crown', 'title' => 'Khách Hàng Vàng'],
                            ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'text' => 'text-gray-600', 'icon' => 'award', 'title' => 'Khách Hàng Bạc'],
                            ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-600', 'icon' => 'award', 'title' => 'Khách Hàng Đồng']
                        ];
                        
                        for ($i = 0; $i < min(3, count($topCustomers)); $i++): 
                            $customer = $topCustomers[$i];
                            $medal = $medals[$i];
                        ?>
                            <div class="rounded-xl border-2 <?php echo $medal['border']; ?> <?php echo $medal['bg']; ?> p-6 transition-transform duration-300 hover:-translate-y-2">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-full <?php echo $medal['bg']; ?> border-2 <?php echo $medal['border']; ?> flex items-center justify-center mr-4">
                                            <i data-feather="<?php echo $medal['icon']; ?>" class="w-6 h-6 <?php echo $medal['text']; ?>"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900"><?php echo $medal['title']; ?></h3>
                                            <p class="text-sm text-gray-600">Hạng <?php echo $i + 1; ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($customer['TenKhachHang']); ?></h4>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($customer['Email']); ?></p>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Số đơn hàng:</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?php echo number_format($customer['SoLuongMua']); ?> đơn
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Tổng chi tiêu:</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <?php echo number_format($customer['TongChiTieu'], 0, ',', '.'); ?>đ
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Giá trị TB/đơn:</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <?php echo number_format($customer['TongChiTieu'] / $customer['SoLuongMua'], 0, ',', '.'); ?>đ
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Lần cuối mua:</span>
                                        <span class="text-sm text-gray-900">
                                            <?php echo date('d/m/Y', strtotime($customer['LanMuaCuoi'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="#products" class="section bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-bold text-gray-900 mb-12 text-center fade-in">Sản Phẩm Matcha Cao Cấp</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="card product-card flex flex-col items-center text-center fade-in">
                    <div class="overflow-hidden rounded-xl mb-6">
                        <img src="https://th.bing.com/th/id/OIP.q3G-9rq4CJSeYPD-jVLTbwHaE7?rs=1&pid=ImgDetMain" alt="Ceremonial Matcha" class="w-full object-cover">
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Matcha Lễ Trà</h3>
                    <p class="text-gray-600 mb-4">Matcha cao cấp từ Uji, Nhật Bản, lý tưởng cho nghi thức trà đạo truyền thống.</p>
                    <a href="#products" class="btn">Mua ngay</a>
                </div>
                <div class="card product-card flex flex-col items-center text-center fade-in">
                    <div class="overflow-hidden rounded-xl mb-6">
                        <img src="https://img.freepik.com/premium-photo/japanese-matcha-tea-detox-elixir_535844-2996.jpg" alt="Culinary Matcha" class="w-full object-cover">
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Matcha Ẩm Thực</h3>
                    <p class="text-gray-600 mb-4">Matcha đậm đà, hoàn hảo cho bánh ngọt, latte, và các món ăn sáng tạo.</p>
                    <a href="#products" class="btn">Mua ngay</a>
                </div>
                <div class="card product-card flex flex-col items-center text-center fade-in">
                    <div class="overflow-hidden rounded-xl mb-6">
                        <img src="https://th.bing.com/th/id/OIP.q3G-9rq4CJSeYPD-jVLTbwHaE7?rs=1&pid=ImgDetMain" alt="Matcha Latte" class="w-full object-cover">
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Matcha Latte</h3>
                    <p class="text-gray-600 mb-4">Hỗn hợp matcha pha sẵn, tiện lợi cho ly latte thơm ngon mỗi ngày.</p>
                    <a href="#products" class="btn">Mua ngay</a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center gap-12">
            <div class="md:w-1/2">
                <img src="https://www.foodandwine.com/thmb/2tI8aL1Z8hKhfV48_c8b6uWG-TQ=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Iced-Matcha-Latte-FT-RECIPE0622-2000-9c2e116d3bc54bdaacda10e62e8e0205.jpg" alt="About Matcha Vibe" class="w-full rounded-2xl shadow-lg fade-in">
            </div>
            <div class="md:w-1/2">
                <h2 class="text-4xl font-bold text-gray-900 mb-6 fade-in">Về Matcha Vibe</h2>
                <p class="text-gray-600 mb-4 fade-in">Matcha Vibe Co., Ltd. mang đến matcha hữu cơ chất lượng cao từ các nông trại danh tiếng ở Nhật Bản. Chúng tôi cam kết cung cấp sản phẩm giàu chất chống oxy hóa, hỗ trợ sức khỏe và phong cách sống bền vững.</p>
                <p class="text-gray-600 fade-in">Hệ thống CRM tiên tiến giúp quản lý khách hàng hiệu quả, theo dõi lịch sử mua hàng, và phân tích dữ liệu để tối ưu hóa kinh doanh, mang lại trải nghiệm tuyệt vời cho khách hàng.</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-bold text-gray-900 mb-12 text-center fade-in">Khách Hàng Nói Gì</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card text-center fade-in">
                    <img src="https://toigingiuvedep.vn/wp-content/uploads/2023/03/anh-nguoi-dep-trung-quoc.jpg" alt="Customer Avatar" class="w-16 h-16 rounded-full mx-auto mb-4">
                    <p class="text-gray-600 italic mb-4">"Matcha lễ trà của Matcha Vibe là sản phẩm tuyệt vời nhất tôi từng thử. Hoàn hảo cho nghi thức buổi sáng!"</p>
                    <p class="text-gray-900 font-semibold">Nguyễn Thị An</p>
                </div>
                <div class="card text-center fade-in">
                    <img src="https://th.bing.com/th/id/OIP.vqVCErz79VT-P6xthmwz5AAAAA?w=377&h=626&rs=1&pid=ImgDetMain" alt="Customer Avatar" class="w-16 h-16 rounded-full mx-auto mb-4">
                    <p class="text-gray-600 italic mb-4">"Matcha ẩm thực đã thay đổi hoàn toàn cách tôi làm bánh. Màu sắc và hương vị thật tuyệt vời!"</p>
                    <p class="text-gray-900 font-semibold">Trần Khánh Linh</p>
                </div>
                <div class="card text-center fade-in">
                    <img src="https://i.pinimg.com/736x/ce/68/d6/ce68d6adb2357bfc24e089949f8a0f62.jpg" alt="Customer Avatar" class="w-16 h-16 rounded-full mx-auto mb-4">
                    <p class="text-gray-600 italic mb-4">"Hỗn hợp matcha latte giúp buổi sáng của tôi trở nên dễ dàng và ngon miệng."</p>
                    <p class="text-gray-900 font-semibold">Lê Thị Cẩm</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-bold text-gray-900 mb-12 text-center fade-in">Liên Hệ Với Matcha Vibe</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="card fade-in">
                    <h3 class="text-2xl font-semibold mb-6">Thông Tin Liên Hệ</h3>
                    <p class="text-gray-600 mb-4"><i data-feather="map-pin" class="inline-block mr-2"></i> 123 Đường Matcha, Quận 1, TP.HCM, Việt Nam</p>
                    <p class="text-gray-600 mb-4"><i data-feather="phone" class="inline-block mr-2"></i> +84 123 456 789</p>
                    <p class="text-gray-600 mb-4"><i data-feather="mail" class="inline-block mr-2"></i> info@matchavibe.vn</p>
                    <img src="https://th.bing.com/th/id/R.f4a6d477b87257d023e0e26840f6205a?rik=PAXGvBE23z6Z0g&pid=ImgRaw&r=0" alt="Map" class="w-full rounded-xl shadow-lg mt-6">
                </div>
                <div class="card fade-in">
                    <h3 class="text-2xl font-semibold mb-6">Gửi Tin Nhắn</h3>
                    <form>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2 font-medium">Họ Tên</label>
                            <input type="text" class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-green-600" placeholder="Nhập họ tên" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2 font-medium">Email</label>
                            <input type="email" class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-green-600" placeholder="Nhập email" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2 font-medium">Tin Nhắn</label>
                            <textarea class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-green-600" rows="5" placeholder="Nhập tin nhắn" required></textarea>
                        </div>
                        <button type="submit" class="btn w-full">Gửi Tin Nhắn</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
                <h3 class="text-2xl font-bold mb-6">Matcha Vibe Co., Ltd.</h3>
                <p class="text-gray-300 mb-6">Mang hương vị matcha thượng hạng từ Nhật Bản đến mọi nhà, với cam kết về chất lượng và sức khỏe.</p>
                <img src="https://th.bing.com/th/id/OIP.joBZlDpB5bVLwkpt4r3w8gHaHa?w=626&h=626&rs=1&pid=ImgDetMain" alt="Footer Logo" class="h-12">
            </div>
            <div>
                <h3 class="text-xl font-bold mb-6">Liên Kết Nhanh</h3>
                <a href="dashboard.php" class="block text-gray-300 hover:text-white mb-3">Quản lý khách hàng</a>
                <a href="statistics.php" class="block text-gray-300 hover:text-white mb-3">Thống kê</a>
                <a href="appointments.php" class="block text-gray-300 hover:text-white mb-3">Đặt lịch hẹn</a>
                <a href="chatbot.php" class="block text-gray-300 hover:text-white">Chatbot AI</a>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-6">Kết Nối Với Chúng Tôi</h3>
                <div class="flex space-x-4 mb-6">
                    <a href="#facebook"><i data-feather="facebook" class="h-6 w-6 text-gray-300 hover:text-white"></i></a>
                    <a href="#instagram"><i data-feather="instagram" class="h-6 w-6 text-gray-300 hover:text-white"></i></a>
                    <a href="#twitter"><i data-feather="twitter" class="h-6 w-6 text-gray-300 hover:text-white"></i></a>
                </div>
                <h3 class="text-xl font-bold mb-4">Đăng Ký Nhận Tin</h3>
                <form class="flex">
                    <input type="email" class="flex-grow p-3 rounded-l-xl border-none focus:ring-2 focus:ring-green-600" placeholder="Nhập email" required>
                    <button type="submit" class="bg-green-600 text-white px-4 py-3 rounded-r-xl hover:bg-green-700">Đăng ký</button>
                </form>
            </div>
        </div>
        <div class="mt-12 text-center text-gray-400">
            <p>© <?php echo date('Y'); ?> Matcha Vibe Co., Ltd. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        // Initialize feather icons
        feather.replace();

        // Hamburger menu toggle
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelector(anchor.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Fade-in animations on scroll
        const fadeIns = document.querySelectorAll('.fade-in');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        fadeIns.forEach(element => observer.observe(element));

        // Initialize Swiper (if used)
        const swiper = new Swiper('.mySwiper', {
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
        });
    </script>
    <!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/68399a1e70b16107011a15dd/1isgg41pq';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
</body>
</html>