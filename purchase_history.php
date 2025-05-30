<?php
require_once 'config.php';
require_once 'classes/Customer.php';

// Kiểm tra session và quyền truy cập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set active page for sidebar
$activePage = 'purchase_history';
$pageTitle = 'Lịch sử mua hàng';

try {
        $customer = new Customer();
    
    // Đánh dấu tất cả đơn hàng là đã thanh toán
    $conn = new PDO("mysql:host=localhost;dbname=matchavibe", "root", "");
    $sql = "UPDATE Orders SET TrangThaiThanhToan = 1 WHERE TrangThaiThanhToan = 0";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Lấy thông tin phân khúc khách hàng
    $customerSegments = $customer->getCustomerSegments();
    
    // Xử lý yêu cầu đánh dấu tất cả đơn hàng là đã thanh toán
    if (isset($_POST['mark_all_paid'])) {
        try {
            $affectedRows = $customer->markAllOrdersAsPaid();
            $success = "Đã cập nhật {$affectedRows} đơn hàng thành trạng thái đã thanh toán!";
            // Refresh lại trang để hiển thị dữ liệu mới
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=" . urlencode($success));
            exit();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // Lấy các bộ lọc từ URL
    $filters = [];
    if (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) {
        $filters['customer_id'] = $_GET['customer_id'];
    }
    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $filters['date_from'] = $_GET['date_from'];
    }
    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $filters['date_to'] = $_GET['date_to'];
    }
    if (isset($_GET['min_amount']) && !empty($_GET['min_amount'])) {
        $filters['min_amount'] = $_GET['min_amount'];
    }
    
    // Lấy danh sách đơn hàng với bộ lọc
    $purchases = $customer->getAllPurchases($filters);
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Thêm xử lý cập nhật giảm giá
if (isset($_POST['update_discounts'])) {
    try {
        $customer->updateOrderDiscounts();
        $success = "Đã cập nhật giảm giá và trạng thái thanh toán thành công!";
        // Refresh lại trang để hiển thị dữ liệu mới
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Include header
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử mua hàng - Matcha Vibe</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style1.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    
</head>
<body>


    <main class="main-content">
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Lỗi!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php else: ?>
            <!-- Phân khúc khách hàng -->
        <div class="card mb-6">
            <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Phân khúc khách hàng</h2>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <i data-feather="search" class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                            <input type="text" id="segmentSearchInput" class="form-input pl-10 rounded-lg" placeholder="Tìm kiếm khách hàng...">
                        </div>
            </div>
                </div>

            <div class="overflow-x-auto">
                    <table class="w-full" id="segmentTable">
                    <thead>
                            <tr class="text-left bg-gray-50">
                                <th class="p-4 font-semibold text-gray-600">Khách hàng</th>
                                <th class="p-4 font-semibold text-gray-600">Phân khúc</th>
                                <th class="p-4 font-semibold text-gray-600">Lượt mua</th>
                                <th class="p-4 font-semibold text-gray-600">Tổng chi tiêu</th>
                                <th class="p-4 font-semibold text-gray-600">Giảm giá</th>
                                <th class="p-4 font-semibold text-gray-600">Thanh toán</th>
                                <th class="p-4 font-semibold text-gray-600">Tiêu chí</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($customerSegments as $segment): ?>
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="p-4">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                                                <i data-feather="user" class="w-4 h-4 text-gray-500"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($segment['TenKhachHang']); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($segment['Email']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <?php 
                                        $segmentColors = [
                                            'VIP' => 'purple',
                                            'Thân thiết' => 'blue',
                                            'Thường xuyên' => 'green',
                                            'Tiềm năng' => 'yellow',
                                            'Mới' => 'gray'
                                        ];
                                        $color = $segmentColors[$segment['PhanKhuc']] ?? 'gray';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800">
                                            <?php echo htmlspecialchars($segment['PhanKhuc']); ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="font-medium"><?php echo number_format($segment['SoLuongMua']); ?></span>
                                    </td>
                                    <td class="p-4">
                                        <span class="font-medium"><?php echo number_format($segment['TongChiTieu'], 0, ',', '.'); ?>đ</span>
                                    </td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <?php echo number_format($segment['PhanTramGiamGia'], 1); ?>%
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $segment['TuDongThanhToan'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo $segment['TuDongThanhToan'] ? 'Tự động' : 'Thủ công'; ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-sm text-gray-600"><?php echo htmlspecialchars($segment['TieuChiPhanKhuc']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bộ lọc -->
            <div class="card mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Bộ lọc đơn hàng</h2>
                    <div class="flex gap-2">
                        <form method="POST" class="inline">
                            <button type="submit" name="mark_all_paid" class="bg-blue-600 text-white btn rounded-lg px-4 py-2 font-semibold hover:bg-blue-700">
                                <i data-feather="check-circle" class="w-4 h-4 mr-2"></i>
                                Đánh dấu tất cả là đã thanh toán
                            </button>
                        </form>
                        <form method="POST" class="inline">
                            <button type="submit" name="update_discounts" class="bg-green-600 text-white btn rounded-lg px-4 py-2 font-semibold hover:bg-green-700">
                                <i data-feather="refresh-cw" class="w-4 h-4 mr-2"></i>
                                Cập nhật giảm giá tự động
                            </button>
                        </form>
                        <button type="button" class="bg-gray-100 text-gray-700 btn rounded-lg px-4 py-2 font-semibold hover:bg-gray-200" onclick="document.getElementById('filterForm').reset()">
                            <i data-feather="refresh-cw" class="w-4 h-4 mr-2"></i>
                            Đặt lại bộ lọc
                        </button>
                    </div>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($_GET['success']); ?></span>
                    </div>
                <?php endif; ?>

                <form method="GET" action="purchase_history.php" id="filterForm" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">Khách hàng</label>
                        <select id="customer_id" name="customer_id" class="form-input w-full rounded-lg">
                            <option value="">Tất cả khách hàng</option>
                            <?php foreach ($customer->getAllCustomers() as $cust): ?>
                                <option value="<?php echo $cust['MaKhachHang']; ?>" 
                                        <?php echo isset($_GET['customer_id']) && $_GET['customer_id'] == $cust['MaKhachHang'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cust['TenKhachHang']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Từ ngày</label>
                        <input type="date" id="date_from" name="date_from" 
                               value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>" 
                               class="form-input w-full rounded-lg">
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Đến ngày</label>
                        <input type="date" id="date_to" name="date_to" 
                               value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>" 
                               class="form-input w-full rounded-lg">
                    </div>

                    <div>
                        <label for="min_amount" class="block text-sm font-medium text-gray-700 mb-2">Giá trị tối thiểu</label>
                        <input type="number" id="min_amount" name="min_amount" 
                               value="<?php echo isset($_GET['min_amount']) ? htmlspecialchars($_GET['min_amount']) : ''; ?>" 
                               class="form-input w-full rounded-lg" min="0" step="1000" placeholder="VNĐ">
                    </div>

                    <div>
                        <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">Trạng thái thanh toán</label>
                        <select id="payment_status" name="payment_status" class="form-input w-full rounded-lg">
                            <option value="">Tất cả</option>
                            <option value="1" <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] == '1' ? 'selected' : ''; ?>>Đã thanh toán</option>
                            <option value="0" <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] == '0' ? 'selected' : ''; ?>>Chưa thanh toán</option>
                        </select>
                    </div>

                    <div class="lg:col-span-4 flex gap-2">
                        <button type="submit" class="bg-green-600 text-white btn rounded-lg px-4 py-2 font-semibold hover:bg-green-700">
                            <i data-feather="filter" class="w-4 h-4 mr-2"></i>
                            Lọc kết quả
                        </button>
                        <a href="purchase_history.php" class="bg-gray-100 text-gray-700 btn rounded-lg px-4 py-2 font-semibold hover:bg-gray-200">
                            <i data-feather="x" class="w-4 h-4 mr-2"></i>
                            Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>

            <!-- Danh sách đơn hàng -->
            <div class="card">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Danh sách đơn hàng</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            <?php 
                            $totalOrders = count($purchases);
                            $totalAmount = array_sum(array_column($purchases, 'TongTien'));
                            echo "Tổng {$totalOrders} đơn hàng, tổng giá trị " . number_format($totalAmount, 0, ',', '.') . 'đ';
                            ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <i data-feather="search" class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                            <input type="text" id="searchInput" class="form-input pl-10 rounded-lg" placeholder="Tìm kiếm đơn hàng...">
                        </div>
                        <a href="add_purchase.php" class="bg-green-600 text-white btn rounded-lg px-4 py-2 font-semibold hover:bg-green-700">
                            <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                            Thêm đơn hàng
                        </a>
                    </div>
                </div>

                <!-- Thống kê nhanh -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center">
                            <div class="p-2 rounded-full bg-blue-100 mr-3">
                                <i data-feather="shopping-bag" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Tổng đơn hàng</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo number_format($totalOrders); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center">
                            <div class="p-2 rounded-full bg-green-100 mr-3">
                                <i data-feather="dollar-sign" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Tổng doanh thu</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo number_format($totalAmount, 0, ',', '.'); ?>đ</p>
                            </div>
                        </div>
                    </div>
                    
                    <?php
                    $paidOrders = array_filter($purchases, function($order) {
                        return $order['TrangThaiThanhToan'] == 1;
                    });
                    $paidAmount = array_sum(array_column($paidOrders, 'TongTien'));
                    $unpaidAmount = $totalAmount - $paidAmount;
                    ?>
                    
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center">
                            <div class="p-2 rounded-full bg-purple-100 mr-3">
                                <i data-feather="check-circle" class="w-5 h-5 text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Đã thanh toán</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo number_format($paidAmount, 0, ',', '.'); ?>đ</p>
                                <p class="text-xs text-gray-500"><?php echo count($paidOrders) . ' đơn hàng'; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center">
                            <div class="p-2 rounded-full bg-yellow-100 mr-3">
                                <i data-feather="clock" class="w-5 h-5 text-yellow-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Chưa thanh toán</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo number_format($unpaidAmount, 0, ',', '.'); ?>đ</p>
                                <p class="text-xs text-gray-500"><?php echo ($totalOrders - count($paidOrders)) . ' đơn hàng'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left bg-gray-50">
                                <th class="p-4 font-semibold text-gray-600">Mã đơn</th>
                                <th class="p-4 font-semibold text-gray-600">Khách hàng</th>
                                <th class="p-4 font-semibold text-gray-600">Ngày mua</th>
                                <th class="p-4 font-semibold text-gray-600">Tổng tiền</th>
                                <th class="p-4 font-semibold text-gray-600">Giảm giá</th>
                                <th class="p-4 font-semibold text-gray-600">Thanh toán sau giảm</th>
                                <th class="p-4 font-semibold text-gray-600">Trạng thái</th>
                                <th class="p-4 font-semibold text-gray-600 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($purchases)): ?>
                                <tr>
                                    <td colspan="8" class="p-4 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center py-6">
                                            <i data-feather="shopping-cart" class="w-12 h-12 text-gray-400 mb-2"></i>
                                            <p class="text-lg font-medium">Không tìm thấy đơn hàng nào</p>
                                            <p class="text-sm text-gray-500">Thử thay đổi bộ lọc để tìm kiếm</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($purchases as $purchase): ?>
                                    <?php
                                    $discountAmount = $purchase['TongTien'] * ($purchase['PhanTramGiamGia'] / 100);
                                    $finalAmount = $purchase['TongTien'] - $discountAmount;
                                    $orderDate = new DateTime($purchase['NgayMua']);
                                    $now = new DateTime();
                                    $daysDiff = $now->diff($orderDate)->days;
                                    ?>
                                    <tr class="border-t border-gray-200 hover:bg-gray-50">
                                        <td class="p-4">
                                            <span class="font-medium text-gray-900">#<?php echo $purchase['MaDonHang']; ?></span>
                                            <?php if ($daysDiff < 7): ?>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Mới</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                                                    <i data-feather="user" class="w-4 h-4 text-gray-500"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($purchase['TenKhachHang']); ?></p>
                                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($purchase['Email']); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div>
                                                <p class="font-medium text-gray-900"><?php echo $orderDate->format('d/m/Y'); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo $orderDate->format('H:i'); ?></p>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <span class="font-medium text-gray-900"><?php echo number_format($purchase['TongTien'], 0, ',', '.'); ?>đ</span>
                                        </td>
                                        <td class="p-4">
                                            <?php if ($purchase['PhanTramGiamGia'] > 0): ?>
                                                <div>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        -<?php echo number_format($purchase['PhanTramGiamGia'], 1); ?>%
                                                    </span>
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        -<?php echo number_format($discountAmount, 0, ',', '.'); ?>đ
                                                    </p>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-sm text-gray-500">Không giảm giá</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4">
                                            <span class="font-medium text-gray-900"><?php echo number_format($finalAmount, 0, ',', '.'); ?>đ</span>
                                        </td>
                                        <td class="p-4">
                                            <?php 
                                            // Đã cập nhật để luôn hiển thị là đã thanh toán
                                            $statusColor = 'green';
                                            $statusIcon = 'check-circle';
                                            ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo $statusColor; ?>-100 text-<?php echo $statusColor; ?>-800">
                                                <i data-feather="<?php echo $statusIcon; ?>" class="w-3 h-3 mr-1"></i>
                                                Đã thanh toán
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a href="xem_don_hang.php?id=<?php echo $purchase['MaDonHang']; ?>" 
                                                   class="text-blue-600 hover:text-blue-800 btn p-2" title="Xem chi tiết">
                                                    <i data-feather="eye" class="w-4 h-4"></i>
                                                </a>
                                                <a href="sua_don_hang.php?id=<?php echo $purchase['MaDonHang']; ?>" 
                                                   class="text-green-600 hover:text-green-800 btn p-2" title="Sửa">
                                                    <i data-feather="edit" class="w-4 h-4"></i>
                                                </a>
                                                <?php if (!$purchase['TrangThaiThanhToan']): ?>
                                                    <form method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn đánh dấu đơn hàng này là đã thanh toán?');">
                                                        <input type="hidden" name="mark_paid" value="<?php echo $purchase['MaDonHang']; ?>">
                                                        <button type="submit" class="text-purple-600 hover:text-purple-800 btn p-2" title="Đánh dấu đã thanh toán">
                                                            <i data-feather="check-square" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <button onclick="if(confirm('Bạn có chắc muốn xóa đơn hàng này?')) window.location.href='xoa_don_hang.php?id=<?php echo $purchase['MaDonHang']; ?>'" 
                                                        class="text-red-600 hover:text-red-800 btn p-2" title="Xóa">
                                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script>
        // Initialize Feather Icons
        feather.replace();

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const table = document.querySelector('table');
        const rows = table.querySelectorAll('tbody tr');

        searchInput.addEventListener('input', () => {
            const term = searchInput.value.toLowerCase();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });

        // Search functionality for customer segments
        const segmentSearchInput = document.getElementById('segmentSearchInput');
        const segmentTable = document.getElementById('segmentTable');
        const segmentRows = segmentTable.querySelectorAll('tbody tr');

        segmentSearchInput.addEventListener('input', () => {
            const term = segmentSearchInput.value.toLowerCase();
            segmentRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    </script>
</body>
</html>