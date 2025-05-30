<?php
require_once 'config.php';

// Kiểm tra session và quyền truy cập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set active page for sidebar
$activePage = 'dashboard';
$pageTitle = 'Thêm khách hàng';

// Xử lý form thêm khách hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $customer = new Customer();
        
        // Validate required fields
        $requiredFields = ['ten_khach_hang', 'email', 'dien_thoai', 'ngay_sinh'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Vui lòng điền đầy đủ thông tin bắt buộc");
            }
        }

        // Sanitize input
        $tenKhachHang = htmlspecialchars(trim($_POST['ten_khach_hang']));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $dienThoai = htmlspecialchars(trim($_POST['dien_thoai']));
        $ngaySinh = htmlspecialchars(trim($_POST['ngay_sinh']));
        $diaChi = isset($_POST['dia_chi']) ? htmlspecialchars(trim($_POST['dia_chi'])) : '';
        $ghiChu = isset($_POST['ghi_chu']) ? htmlspecialchars(trim($_POST['ghi_chu'])) : '';

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email không hợp lệ");
        }

        // Validate phone number (Vietnamese format)
        if (!preg_match('/^(0|\+84)\d{9,10}$/', $dienThoai)) {
            throw new Exception("Số điện thoại không hợp lệ");
        }

        // Validate birth date
        $birthDate = DateTime::createFromFormat('Y-m-d', $ngaySinh);
        if (!$birthDate || $birthDate > new DateTime()) {
            throw new Exception("Ngày sinh không hợp lệ");
        }

        $result = $customer->add(
            $tenKhachHang,
            $dienThoai,
            $email,
            $diaChi,
            $ngaySinh,
            'active'
        );

        if ($result) {
            header('Location: dashboard.php?message=' . urlencode('Thêm khách hàng thành công'));
            exit();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Include header
require_once 'includes/header.php';
// Include sidebar
require_once 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm khách hàng - Matcha Vibe</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style1.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="text-xl font-bold mb-6 flex items-center"><i data-feather="coffee" class="mr-2"></i> Matcha Vibe</h2>
        <a href="dashboard.php" class="font-medium bg-white bg-opacity-20"><i data-feather="home" class="mr-2"></i> Trang chủ</a>
        <a href="birthday_wishes.php" class="font-medium"><i data-feather="gift" class="mr-2"></i> Lời chúc sinh nhật</a>
        <a href="purchase_history.php" class="font-medium"><i data-feather="shopping-cart" class="mr-2"></i> Lịch sử mua hàng</a>
        <a href="statistics.php" class="font-medium"><i data-feather="bar-chart-2" class="mr-2"></i> Thống kê</a>
        <a href="#" class="font-medium"><i data-feather="calendar" class="mr-2"></i> Lịch hẹn</a>
        <a href="dashboard.php?logout=true" class="text-red-200 hover:bg-red-600 font-medium"><i data-feather="log-out" class="mr-2"></i> Đăng xuất</a>
    </div>

    <main class="main-content">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Thêm khách hàng mới</h1>
            <a href="dashboard.php" class="bg-gray-100 text-gray-700 btn rounded-lg px-4 py-2 font-semibold hover:bg-gray-200">
                <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
                Quay lại
            </a>
        </div>

        <!-- Form Card -->
        <div class="card">
            <?php if (isset($error)): ?>
                <div class="error-message mb-6"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="them_khach_hang.php" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tên khách hàng -->
                    <div>
                        <label for="ten_khach_hang" class="block text-sm font-medium text-gray-700 mb-2">
                            Tên khách hàng <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="ten_khach_hang" name="ten_khach_hang" required
                               class="form-input" placeholder="Nhập tên khách hàng">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-600">*</span>
                        </label>
                        <input type="email" id="email" name="email" required
                               class="form-input" placeholder="Nhập địa chỉ email">
                    </div>

                    <!-- Số điện thoại -->
                    <div>
                        <label for="dien_thoai" class="block text-sm font-medium text-gray-700 mb-2">
                            Số điện thoại <span class="text-red-600">*</span>
                        </label>
                        <input type="tel" id="dien_thoai" name="dien_thoai" required
                               class="form-input" placeholder="Nhập số điện thoại">
                    </div>

                    <!-- Ngày sinh -->
                    <div>
                        <label for="ngay_sinh" class="block text-sm font-medium text-gray-700 mb-2">
                            Ngày sinh <span class="text-red-600">*</span>
                        </label>
                        <input type="date" id="ngay_sinh" name="ngay_sinh" required
                               class="form-input">
                    </div>

                    <!-- Địa chỉ -->
                    <div class="md:col-span-2">
                        <label for="dia_chi" class="block text-sm font-medium text-gray-700 mb-2">
                            Địa chỉ
                        </label>
                        <input type="text" id="dia_chi" name="dia_chi"
                               class="form-input" placeholder="Nhập địa chỉ">
                    </div>

                    <!-- Ghi chú -->
                    <div class="md:col-span-2">
                        <label for="ghi_chu" class="block text-sm font-medium text-gray-700 mb-2">
                            Ghi chú
                        </label>
                        <textarea id="ghi_chu" name="ghi_chu" rows="4"
                                  class="form-input" placeholder="Nhập ghi chú (nếu có)"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <button type="reset" class="bg-gray-100 text-gray-700 btn rounded-lg px-6 py-2 font-semibold hover:bg-gray-200">
                        <i data-feather="refresh-cw" class="w-4 h-4 mr-2"></i>
                        Đặt lại
                    </button>
                    <button type="submit" class="bg-green-600 text-white btn rounded-lg px-6 py-2 font-semibold hover:bg-green-700">
                        <i data-feather="user-plus" class="w-4 h-4 mr-2"></i>
                        Thêm khách hàng
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        feather.replace();

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const phone = document.getElementById('dien_thoai').value;
            const email = document.getElementById('email').value;
            const birthDate = new Date(document.getElementById('ngay_sinh').value);
            const today = new Date();
            
            // Validate phone number
            if (!/^(0|\+84)\d{9,10}$/.test(phone)) {
                e.preventDefault();
                alert('Số điện thoại không hợp lệ. Vui lòng nhập số điện thoại Việt Nam hợp lệ.');
                return;
            }
            
            // Validate email
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Email không hợp lệ. Vui lòng kiểm tra lại.');
                return;
            }
            
            // Validate birth date
            if (birthDate > today) {
                e.preventDefault();
                alert('Ngày sinh không hợp lệ. Ngày sinh không thể là ngày trong tương lai.');
                return;
            }
        });

        // Phone number formatting
        document.getElementById('dien_thoai').addEventListener('input', function(e) {
            let phone = e.target.value.replace(/\D/g, '');
            if (phone.length > 0 && phone[0] !== '0') {
                phone = '0' + phone;
            }
            if (phone.length > 11) {
                phone = phone.substr(0, 11);
            }
            e.target.value = phone;
        });
    </script>
</body>
</html>