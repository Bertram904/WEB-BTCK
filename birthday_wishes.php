<?php
require_once 'config.php';
require_once 'classes/BirthdayWish.php';

// Kiểm tra session và quyền truy cập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set active page for sidebar
$activePage = 'birthday';
$pageTitle = 'Lời chúc sinh nhật';

// Khởi tạo các đối tượng với xử lý lỗi
try {
    $birthdayWish = new BirthdayWish();
    $customer = new Customer();
} catch (Exception $e) {
    die("Lỗi khởi tạo: " . htmlspecialchars($e->getMessage()));
}

// Xử lý form gửi lời chúc
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_wishes'])) {
    try {
        $results = $birthdayWish->sendAutomaticWishes();
        $_SESSION['success_message'] = "Đã gửi " . count($results['success']) . " lời chúc sinh nhật thành công";
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

// Xử lý gửi lời chúc thủ công từ modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_wish'])) {
    try {
        $customerId = $_POST['customer_id'];
        $content = $_POST['content'];
        $method = $_POST['method'];
        $notes = $_POST['notes'] ?? '';
        $sender = $_SESSION['user_id'] ?? 'System';
        
        $birthdayWish->send($customerId, $content, $sender, $method, $notes);
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Lỗi khi gửi lời chúc: " . $e->getMessage();
    }
}

// Lấy danh sách khách hàng sinh nhật và lời chúc gần đây
try {
    $currentMonth = date('m');
    $customers = $customer->getByBirthdayRange("$currentMonth-01", "$currentMonth-31");
    $recentWishes = $birthdayWish->getRecentWishes(5);
    
    // Get statistics
    $totalBirthdays = count($customers);
    $sentWishes = 0;
    $pendingWishes = $totalBirthdays;
    
    if ($totalBirthdays > 0) {
        foreach ($customers as $c) {
            $customerWishes = $birthdayWish->getCustomerWishes($c['MaKhachHang']);
            if (!empty($customerWishes)) {
                $sentWishes++;
                $pendingWishes--;
            }
        }
    }
} catch (Exception $e) {
    $error = "Lỗi tải dữ liệu: " . htmlspecialchars($e->getMessage());
    $customers = [];
    $recentWishes = [];
    $totalBirthdays = 0;
    $sentWishes = 0;
    $pendingWishes = 0;
}
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lời chúc sinh nhật - Matcha Vibe</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style1.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
       
</head>
<body>

    <div class="main-content">
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6 flex items-center">
                    <i data-feather="check-circle" class="w-5 h-5 mr-2"></i>
                    ' . htmlspecialchars($_SESSION['success_message']) . '
                  </div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message flex items-center">
                    <i data-feather="alert-circle" class="w-5 h-5 mr-2"></i>
                    ' . htmlspecialchars($_SESSION['error_message']) . '
                  </div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Lời chúc sinh nhật tháng <?php echo date('m/Y'); ?></h1>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <div class="card flex items-center">
                <div class="p-3 rounded-full bg-green-500 bg-opacity-10 mr-4">
                    <i data-feather="gift" class="w-8 h-8 text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Sinh nhật tháng này</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo count($customers); ?></h3>
                </div>
            </div>
            
            <div class="card flex items-center">
                <div class="p-3 rounded-full bg-green-500 bg-opacity-10 mr-4">
                    <i data-feather="mail" class="w-8 h-8 text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Đã gửi lời chúc</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $sentWishes; ?></h3>
                </div>
            </div>
            
            <div class="card flex items-center">
                <div class="p-3 rounded-full bg-yellow-500 bg-opacity-10 mr-4">
                    <i data-feather="clock" class="w-8 h-8 text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Chưa gửi lời chúc</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $pendingWishes; ?></h3>
                </div>
            </div>
        </div>

        <!-- Birthday List -->
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">Danh sách sinh nhật</h2>
                <form method="POST" class="flex items-center">
                    <button type="submit" name="send_wishes" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center">
                        <i data-feather="send" class="w-4 h-4 mr-2"></i>
                        Gửi lời chúc tự động
                    </button>
                </form>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (empty($customers)): ?>
                <div class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i data-feather="calendar" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <p class="text-lg font-medium text-gray-900">Không có sinh nhật nào trong tháng này</p>
                    <p class="text-sm text-gray-500">Hãy quay lại vào tháng sau</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($customers as $customer): ?>
                        <div class="birthday-card">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                    <i data-feather="user" class="w-6 h-6 text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($customer['TenKhachHang']); ?></h3>
                                    <p class="text-sm text-gray-500">
                                        Sinh nhật: <?php echo date('d/m', strtotime($customer['NgaySinh'])); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">
                                        <i data-feather="shopping-bag" class="w-4 h-4 inline mr-1"></i>
                                        <?php echo number_format($customer['SoLuongMua'] ?? 0); ?> đơn hàng
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <i data-feather="dollar-sign" class="w-4 h-4 inline mr-1"></i>
                                        <?php echo number_format($customer['TongChiTieu'] ?? 0, 0, ',', '.'); ?>đ
                                    </p>
                                </div>
                                <button onclick="openSendWishModal(<?php echo $customer['MaKhachHang']; ?>, '<?php echo htmlspecialchars($customer['TenKhachHang']); ?>')" 
                                        class="bg-green-600 text-white btn rounded-lg px-3 py-2 font-medium hover:bg-green-700">
                                    <i data-feather="send" class="w-4 h-4 mr-1"></i>
                                    Gửi lời chúc
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Wishes History -->
        <div class="card mt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">Lịch sử lời chúc đã gửi</h2>
            </div>

            <?php if (empty($recentWishes)): ?>
                <div class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i data-feather="mail" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <p class="text-lg font-medium text-gray-900">Chưa có lời chúc nào được gửi</p>
                    <p class="text-sm text-gray-500">Các lời chúc đã gửi sẽ xuất hiện ở đây</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="table-header">
                                <th class="p-3">Khách hàng</th>
                                <th class="p-3">Nội dung</th>
                                <th class="p-3">Phương thức</th>
                                <th class="p-3">Người gửi</th>
                                <th class="p-3">Thời gian</th>
                                <th class="p-3">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentWishes as $wish): ?>
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="p-3">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                                <i data-feather="user" class="w-5 h-5 text-green-600"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($wish['TenKhachHang'] ?? 'Unknown'); ?></div>
                                                <div class="text-sm text-gray-500">
                                                    <?php if (!empty($wish['Email'])): ?>
                                                        <span class="mr-2"><i data-feather="mail" class="w-3 h-3 inline"></i> <?php echo htmlspecialchars($wish['Email']); ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($wish['DienThoai'])): ?>
                                                        <span><i data-feather="phone" class="w-3 h-3 inline"></i> <?php echo htmlspecialchars($wish['DienThoai']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <div class="max-w-xs overflow-hidden">
                                            <?php echo nl2br(htmlspecialchars($wish['NoiDung'] ?? '')); ?>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <?php
                                        $phuongThucGui = $wish['PhuongThucGui'] ?? 'system';
                                        $phuongThucClass = match ($phuongThucGui) {
                                            'email' => 'bg-blue-100 text-blue-800',
                                            'sms' => 'bg-purple-100 text-purple-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $phuongThucIcon = match ($phuongThucGui) {
                                            'email' => 'mail',
                                            'sms' => 'message-square',
                                            default => 'bell',
                                        };
                                        $phuongThucLabel = match ($phuongThucGui) {
                                            'email' => 'Email',
                                            'sms' => 'SMS',
                                            default => 'Hệ thống',
                                        };
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $phuongThucClass; ?>">
                                            <i data-feather="<?php echo $phuongThucIcon; ?>" class="w-3 h-3 mr-1"></i>
                                            <?php echo $phuongThucLabel; ?>
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($wish['NguoiGui'] ?? 'System'); ?></div>
                                    </td>
                                    <td class="p-3">
                                        <div class="text-sm text-gray-500">
                                            <?php echo !empty($wish['NgayGui']) ? date('d/m/Y H:i', strtotime($wish['NgayGui'])) : 'N/A'; ?>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <?php
                                        $trangThai = $wish['TrangThai'] ?? 'failed';
                                        $trangThaiClass = $trangThai === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                        $trangThaiIcon = $trangThai === 'success' ? 'check-circle' : 'x-circle';
                                        $trangThaiLabel = $trangThai === 'success' ? 'Thành công' : 'Thất bại';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $trangThaiClass; ?>">
                                            <i data-feather="<?php echo $trangThaiIcon; ?>" class="w-3 h-3 mr-1"></i>
                                            <?php echo $trangThaiLabel; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Send Wish Modal -->
    <div id="sendWishModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Gửi lời chúc sinh nhật</h3>
                <button onclick="closeSendWishModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-feather="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form id="sendWishForm" method="POST" class="space-y-4">
                <input type="hidden" name="send_wish" value="1">
                <input type="hidden" id="customer_id" name="customer_id">

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        Nội dung lời chúc <span class="text-red-600">*</span>
                    </label>
                    <textarea id="content" name="content" rows="4" required
                              class="form-input" placeholder="Nhập lời chúc sinh nhật..."></textarea>
                </div>

                <div>
                    <label for="method" class="block text-sm font-medium text-gray-700 mb-2">
                        Phương thức gửi <span class="text-red-600">*</span>
                    </label>
                    <select id="method" name="method" required class="form-input">
                        <option value="system">Hệ thống</option>
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                    </select>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Ghi chú
                    </label>
                    
                    <textarea id="notes" name="notes" rows="2"
                              class="form-input" placeholder="Nhập ghi chú (nếu có)"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeSendWishModal()"
                            class="bg-gray-100 text-gray-700 btn rounded-lg px-4 py-2 font-medium hover:bg-gray-200">
                        Hủy bỏ
                    </button>
                    <button type="submit"
                            class="bg-green-600 text-white btn rounded-lg px-4 py-2 font-medium hover:bg-green-700">
                        <i data-feather="send" class="w-4 h-4 mr-2"></i>
                        Gửi lời chúc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
    <script>
        feather.replace();

        function openSendWishModal(customerId, customerName) {
            document.getElementById('customer_id').value = customerId;
            document.getElementById('content').value = `Chúc mừng sinh nhật ${customerName}!\n\nChúc bạn một ngày sinh nhật thật vui vẻ và hạnh phúc. Cảm ơn bạn đã luôn đồng hành cùng Matcha Vibe.`;
            document.getElementById('sendWishModal').classList.remove('hidden');
            document.getElementById('sendWishModal').classList.add('flex');
        }

        function closeSendWishModal() {
            document.getElementById('sendWishModal').classList.remove('flex');
            document.getElementById('sendWishModal').classList.add('hidden');
            document.getElementById('sendWishForm').reset();
        }

        // Form validation
        document.getElementById('sendWishForm').addEventListener('submit', function(e) {
            const content = document.getElementById('content').value.trim();
            if (!content) {
                e.preventDefault();
                alert('Vui lòng nhập nội dung lời chúc.');
                return;
            }
        });
    </script>
</body>
</html>