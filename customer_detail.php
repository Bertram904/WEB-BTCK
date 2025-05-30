<?php
require_once 'config.php';
require_once 'classes/Customer.php';
require_once 'classes/Order.php';
require_once 'classes/BirthdayWish.php';
require_once 'classes/Promotion.php';
require_once 'classes/Message.php';

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
$pageTitle = 'Chi tiết khách hàng';

// Kiểm tra ID khách hàng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$customerId = intval($_GET['id']);

try {
    $customer = new Customer();
    $order = new Order();
    $birthdayWish = new BirthdayWish();
    $promotion = new Promotion();
    $message = new Message();

    // Lấy thông tin chi tiết khách hàng
    $customerInfo = $customer->getById($customerId);
    if (!$customerInfo) {
        throw new Exception("Không tìm thấy thông tin khách hàng");
    }

    // Lấy lịch sử đơn hàng
    $orderHistory = $order->getByCustomer($customerId);

    // Lấy lịch sử lời chúc sinh nhật
    $birthdayWishes = $birthdayWish->getCustomerWishes($customerId);

    // Cập nhật và lấy khuyến mãi
    $promotion->updateCustomerPromotions($customerId);
    $promotions = $promotion->getActivePromotions($customerId);

    // Lấy lịch sử mua hàng (sử dụng phương thức đúng)
    $orders = $order->getByCustomer($customerId);
    
    // Lấy lịch sử chat
    $messages = $message->getByCustomer($customerId);

} catch (Exception $e) {
    $error = $e->getMessage();
}

// Include header
require_once 'header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết khách hàng - Matcha Vibe</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f0fdf4; 
        }
        
        .sidebar { 
            background: linear-gradient(180deg, #1a4731, #2f855a);
            color: #ecfdf5; 
            width: 260px; 
            height: 100vh; 
            position: fixed; 
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar a { 
            color: #ecfdf5; 
            padding: 12px; 
            display: flex; 
            align-items: center; 
            border-radius: 8px; 
            margin-bottom: 8px; 
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .sidebar a:hover, 
        .sidebar a.active { 
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(4px);
        }

        .main-content { 
            margin-left: 260px; 
            padding: 32px;
            max-width: 1600px;
        }

        .card { 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); 
            padding: 24px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }

        .btn { 
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }
        
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table-header { 
            background: #dcfce7; 
            padding: 12px; 
            font-weight: 600; 
            color: #1a4731; 
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .sidebar { 
                width: 200px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 50;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content { 
                margin-left: 0;
                padding: 16px;
            }
        }

        /* Promotion badges */
        .promotion-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .promotion-badge.vip {
            background: #1a4731;
            color: #ffffff;
        }

        .promotion-badge.cao-cap {
            background: #2f855a;
            color: #ffffff;
        }

        .promotion-badge.trung-binh {
            background: #059669;
            color: #ffffff;
        }

        .promotion-badge.co-ban {
            background: #34d399;
            color: #1a4731;
        }

        .promotion-badge.than-thiet {
            background: #6ee7b7;
            color: #1a4731;
        }

        .promotion-badge i {
            margin-right: 0.5rem;
            width: 1rem;
            height: 1rem;
        }

        .tab-active {
            border-bottom: 2px solid #1a4731;
            color: #1a4731;
        }

        /* Thêm các class màu mới */
        .bg-primary {
            background-color: #1a4731;
        }

        .text-primary {
            color: #1a4731;
        }

        .border-primary {
            border-color: #1a4731;
        }

        .hover\:bg-primary-dark:hover {
            background-color: #153725;
        }

        /* Màu cho các nút */
        .btn-primary {
            background-color: #1a4731;
            color: white;
        }

        .btn-primary:hover {
            background-color: #153725;
        }

        /* Màu cho các icon */
        .icon-primary {
            color: #1a4731;
        }

        /* Màu cho các badge trạng thái */
        .status-badge-active {
            background-color: #dcfce7;
            color: #1a4731;
        }

        .status-badge-inactive {
            background-color: #fee2e2;
            color: #dc2626;
        }
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
        <a href="dashboard.php?logout=true" class="text-red-200 hover:bg-red-600 font-medium"><i data-feather="log-out" class="mr-2"></i> Đăng xuất</a>
    </div>

    <main class="main-content">
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Lỗi!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php else: ?>
            <!-- Thông tin cơ bản -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">
                    <?php echo htmlspecialchars($customerInfo['TenKhachHang']); ?>
                </h1>
                <div class="flex gap-3">
                    <a href="add_purchase.php?customer_id=<?php echo $customerId; ?>" 
                       class="bg-green-600 text-white btn rounded-lg px-4 py-2 font-semibold hover:bg-green-700">
                        <i data-feather="shopping-cart" class="w-4 h-4"></i>
                        Thêm đơn hàng
                    </a>
                    <a href="sua_khach_hang.php?id=<?php echo $customerId; ?>" 
                       class="bg-primary text-white btn rounded-lg px-4 py-2 font-semibold hover:bg-primary-dark">
                        <i data-feather="edit" class="w-4 h-4"></i>
                        Sửa thông tin
                    </a>
                    <button onclick="if(confirm('Bạn có chắc muốn xóa khách hàng này?')) window.location.href='xoa_khach_hang.php?id=<?php echo $customerId; ?>'" 
                            class="bg-red-600 text-white btn rounded-lg px-4 py-2 font-semibold hover:bg-red-700">
                        <i data-feather="trash-2" class="w-4 h-4"></i>
                        Xóa khách hàng
                    </button>
                </div>
            </div>

            <!-- Thẻ thông tin -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="card flex items-center">
                    <div class="p-3 rounded-full bg-primary bg-opacity-10 mr-4">
                        <i data-feather="shopping-bag" class="w-8 h-8 text-primary"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Tổng đơn hàng</p>
                        <h3 class="text-2xl font-bold text-gray-900">
                            <?php echo number_format(count($orders)); ?>
                        </h3>
                    </div>
                </div>

                <div class="card flex items-center">
                    <div class="p-3 rounded-full bg-primary bg-opacity-10 mr-4">
                        <i data-feather="dollar-sign" class="w-8 h-8 text-primary"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Tổng chi tiêu</p>
                        <h3 class="text-2xl font-bold text-gray-900">
                            <?php 
                            $totalSpent = array_sum(array_column($orders, 'TongTien'));
                            echo number_format($totalSpent) . 'đ';
                            ?>
                        </h3>
                    </div>
                </div>

                <div class="card flex items-center">
                    <div class="p-3 rounded-full bg-<?php echo $customerInfo['TrangThai'] === 'active' ? 'primary' : 'red'; ?>-500 bg-opacity-10 mr-4">
                        <i data-feather="<?php echo $customerInfo['TrangThai'] === 'active' ? 'check-circle' : 'x-circle'; ?>" 
                           class="w-8 h-8 text-<?php echo $customerInfo['TrangThai'] === 'active' ? 'primary' : 'red'; ?>"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Trạng thái</p>
                        <h3 class="text-2xl font-bold text-<?php echo $customerInfo['TrangThai'] === 'active' ? 'primary' : 'red-600'; ?>">
                            <?php echo $customerInfo['TrangThai'] === 'active' ? 'Đang hoạt động' : 'Không hoạt động'; ?>
                        </h3>
                    </div>
                </div>
            </div>

            <!-- Thông tin chi tiết -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Thông tin liên hệ -->
                <div class="card">
                    <h2 class="text-xl font-bold text-primary mb-4">Thông tin liên hệ</h2>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i data-feather="mail" class="w-5 h-5 text-primary mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($customerInfo['Email']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i data-feather="phone" class="w-5 h-5 text-primary mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Số điện thoại</p>
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($customerInfo['DienThoai']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i data-feather="map-pin" class="w-5 h-5 text-primary mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Địa chỉ</p>
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($customerInfo['DiaChi']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i data-feather="gift" class="w-5 h-5 text-primary mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Ngày sinh</p>
                                <p class="font-medium text-gray-900"><?php echo date('d/m/Y', strtotime($customerInfo['NgaySinh'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Khuyến mãi hiện có -->
                <div class="card">
                    <h2 class="text-xl font-bold text-primary mb-4">Khuyến mãi hiện có</h2>
                    <?php if (empty($promotions)): ?>
                        <div class="text-center py-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary bg-opacity-10 mb-4">
                                <i data-feather="tag" class="w-8 h-8 text-primary"></i>
                            </div>
                            <p class="text-gray-500">Chưa có khuyến mãi nào</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($promotions as $promo): ?>
                                <?php
                                    $badgeClass = strtolower(str_replace(' ', '-', $promo['CapBac']));
                                    $icon = 'tag';
                                    switch ($promo['LoaiKhuyenMai']) {
                                        case 'birthday':
                                            $icon = 'gift';
                                            break;
                                        case 'loyalty':
                                            $icon = 'award';
                                            break;
                                        default:
                                            $icon = 'tag';
                                    }
                                ?>
                                <div class="promotion-badge <?php echo $badgeClass; ?>">
                                    <i data-feather="<?php echo $icon; ?>"></i>
                                    <div>
                                        <p class="font-medium"><?php echo htmlspecialchars($promo['MoTa']); ?></p>
                                        <p class="text-sm opacity-75">
                                            Hết hạn: <?php echo date('d/m/Y', strtotime($promo['NgayHetHan'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <button class="tab-button px-1 py-4 text-sm font-medium tab-active" data-tab="orders">
                        Lịch sử mua hàng
                    </button>
                    <button class="tab-button px-1 py-4 text-sm font-medium" data-tab="chat">
                        Lịch sử chat
                    </button>
                </nav>
            </div>

            <!-- Tab contents -->
            <div id="orders" class="tab-content card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left bg-primary bg-opacity-5">
                                <th class="p-4 font-semibold text-primary">Mã đơn hàng</th>
                                <th class="p-4 font-semibold text-primary">Ngày đặt</th>
                                <th class="p-4 font-semibold text-primary">Tổng tiền</th>
                                <th class="p-4 font-semibold text-primary">Giảm giá</th>
                                <th class="p-4 font-semibold text-primary">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-gray-500">
                                        Chưa có đơn hàng nào
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr class="border-t border-gray-100">
                                        <td class="p-4 text-primary font-medium">#<?php echo $order['MaDonHang']; ?></td>
                                        <td class="p-4 text-gray-600">
                                            <?php echo date('d/m/Y H:i', strtotime($order['NgayMua'])); ?>
                                        </td>
                                        <td class="p-4 text-gray-900">
                                            <?php echo number_format($order['TongTien']) . 'đ'; ?>
                                        </td>
                                        <td class="p-4 text-gray-900">
                                            <?php echo isset($order['PhanTramGiamGia']) ? $order['PhanTramGiamGia'] : '0'; ?>%
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo isset($order['TrangThaiThanhToan']) && $order['TrangThaiThanhToan'] ? 'status-badge-active' : 'status-badge-inactive'; ?>">
                                                <?php echo isset($order['TrangThaiThanhToan']) && $order['TrangThaiThanhToan'] ? 'Đã thanh toán' : 'Chưa thanh toán'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="chat" class="tab-content card hidden">
                <div class="space-y-4">
                    <?php if (empty($messages)): ?>
                        <p class="text-center text-gray-500">Chưa có tin nhắn nào</p>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="flex <?php echo $msg['LaNhanVien'] ? 'justify-end' : 'justify-start'; ?>">
                                <div class="max-w-[70%] <?php echo $msg['LaNhanVien'] ? 'bg-primary text-white' : 'bg-gray-100 text-gray-900'; ?> rounded-lg px-4 py-2">
                                    <p class="text-sm"><?php echo htmlspecialchars($msg['NoiDung']); ?></p>
                                    <p class="text-xs <?php echo $msg['LaNhanVien'] ? 'text-green-100' : 'text-gray-500'; ?> mt-1">
                                        <?php echo date('d/m/Y H:i', strtotime($msg['ThoiGian'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        feather.replace();

        // Tab switching
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('tab-active'));
                tabContents.forEach(content => content.classList.add('hidden'));

                // Add active class to clicked button and show corresponding content
                button.classList.add('tab-active');
                document.getElementById(button.dataset.tab).classList.remove('hidden');
            });
        });
    </script>
</body>
</html> 