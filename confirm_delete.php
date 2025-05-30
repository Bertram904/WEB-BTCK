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

// Kiểm tra ID khách hàng
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID khách hàng không được cung cấp";
    header('Location: dashboard.php');
    exit();
}

$customerId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($customerId === false) {
    $_SESSION['error'] = "ID khách hàng không hợp lệ";
    header('Location: dashboard.php');
    exit();
}

try {
    $customer = new Customer();
    $customerInfo = $customer->getById($customerId);
    
    if (!$customerInfo) {
        throw new Exception("Không tìm thấy thông tin khách hàng");
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận xóa khách hàng - Matcha Vibe</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f0fdf4;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="card max-w-2xl w-full p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Xác nhận xóa khách hàng</h1>
            <p class="text-gray-600">Bạn có chắc chắn muốn xóa khách hàng này?</p>
        </div>

        <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mr-4">
                    <i data-feather="user" class="w-6 h-6 text-primary"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        <?php echo htmlspecialchars($customerInfo['TenKhachHang']); ?>
                    </h2>
                    <p class="text-sm text-gray-500">ID: <?php echo $customerId; ?></p>
                </div>
            </div>

            <?php if (isset($_SESSION['warning'])): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-feather="alert-triangle" class="w-5 h-5 text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <?php echo $_SESSION['warning']; ?>
                            </p>
                            <?php if (isset($_SESSION['related_data'])): ?>
                                <ul class="mt-2 text-sm text-yellow-700 list-disc list-inside">
                                    <?php foreach ($_SESSION['related_data'] as $item): ?>
                                        <li><?php echo htmlspecialchars($item); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php unset($_SESSION['related_data']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['warning']); ?>
            <?php endif; ?>
        </div>

        <div class="flex justify-end gap-4">
            <a href="dashboard.php" 
               class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                Hủy
            </a>
            <a href="xoa_khach_hang.php?id=<?php echo $customerId; ?>&confirm=true" 
               class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                Xác nhận xóa
            </a>
        </div>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html> 