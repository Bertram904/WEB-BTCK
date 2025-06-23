<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php?error=' . urlencode('ID khách hàng không hợp lệ'));
    exit();
}

try {
    $customer = new Customer();
    $data = $customer->getById((int)$_GET['id']);
    if (!$data) {
        header('Location: dashboard.php?error=' . urlencode('Không tìm thấy khách hàng'));
        exit();
    }
} catch (Exception $e) {
    header('Location: dashboard.php?error=' . urlencode('Lỗi: ' . $e->getMessage()));
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa khách hàng - Matcha Vibe</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0fdf4; }
        .sidebar { background: linear-gradient(180deg, #1a4731, #2f855a); color: #ecfdf5; width: 260px; height: 100vh; position: fixed; padding: 24px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .sidebar a { color: #ecfdf5; padding: 12px; display: flex; align-items: center; border-radius: 8px; margin-bottom: 8px; transition: all 0.3s ease; }
        .sidebar a:hover { background: rgba(255, 255, 255, 0.2); }
        .main-content { margin-left: 260px; padding: 32px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); padding: 24px; }
        .input-field { transition: border-color 0.3s ease, box-shadow 0.3s ease; }
        .input-field:focus { border-color: #2f855a; box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.1); }
        .btn { transition: all 0.3s ease; }
        .btn:hover { transform: translateY(-2px); }
        .error-message { color: #dc2626; font-size: 0.875rem; }
        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .main-content { margin-left: 200px; padding: 16px; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="text-xl font-bold mb-6 flex items-center"><i data-feather="coffee" class="mr-2"></i> Matcha Vibe</h2>
        <a href="dashboard.php" class="font-medium"><i data-feather="home" class="mr-2"></i> Trang chủ</a>
        <a href="birthday_wishes.php" class="font-medium"><i data-feather="gift" class="mr-2"></i> Lời chúc sinh nhật</a>
        <a href="purchase_history.php" class="font-medium"><i data-feather="shopping-cart" class="mr-2"></i> Lịch sử mua hàng</a>
        <a href="#" class="font-medium"><i data-feather="bar-chart-2" class="mr-2"></i> Thống kê</a>
        <a href="#" class="font-medium"><i data-feather="calendar" class="mr-2"></i> Lịch hẹn</a>
        <a href="dashboard.php?logout=true" class="text-red-200 hover:bg-red-600 font-medium"><i data-feather="log-out" class="mr-2"></i> Đăng xuất</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Sửa khách hàng</h1>
        <div class="card">
            <form id="edit-customer-form">
                <input type="hidden" name="id" value="<?php echo $data['MaKhachHang']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Tên khách hàng</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($data['TenKhachHang']); ?>" class="mt-1 block w-full border rounded-lg p-3 input-field focus:outline-none" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data['Email']); ?>" class="mt-1 block w-full border rounded-lg p-3 input-field focus:outline-none" required>
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($data['DienThoai']); ?>" class="mt-1 block w-full border rounded-lg p-3 input-field focus:outline-none">
                    </div>
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Địa chỉ</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($data['DiaChi']); ?>" class="mt-1 block w-full border rounded-lg p-3 input-field focus:outline-none">
                    </div>
                    <div>
                        <label for="birthday" class="block text-sm font-medium text-gray-700">Ngày sinh</label>
                        <input type="date" id="birthday" name="birthday" value="<?php echo $data['NgaySinh'] ? htmlspecialchars($data['NgaySinh']) : ''; ?>" class="mt-1 block w-full border rounded-lg p-3 input-field focus:outline-none">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                        <select id="status" name="status" class="mt-1 block w-full border rounded-lg p-3 input-field focus:outline-none">
                            <option value="active" <?php echo $data['TrangThai'] === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                            <option value="inactive" <?php echo $data['TrangThai'] === 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex space-x-4">
                    <button type="submit" class="bg-green-600 text-white btn rounded-lg px-4 py-2 font-semibold hover:bg-green-700">Lưu thay đổi</button>
                    <a href="dashboard.php" class="bg-gray-300 text-gray-700 btn rounded-lg px-4 py-2 font-semibold hover:bg-gray-400">Hủy</a>
                </div>
                <div id="edit-result" class="mt-4 text-sm"></div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
    <script>
        feather.replace();
        document.getElementById('edit-customer-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const resultDiv = document.getElementById('edit-result');
            resultDiv.textContent = 'Đang lưu...';
            fetch('xu_ly_sua_khach_hang.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    resultDiv.textContent = 'Cập nhật thành công!';
                    resultDiv.className = 'mt-4 text-green-600 text-sm';
                    setTimeout(() => { window.location.href = 'dashboard.php?success=1'; }, 1200);
                } else {
                    resultDiv.textContent = data.message || 'Có lỗi xảy ra.';
                    resultDiv.className = 'mt-4 text-red-600 text-sm';
                }
            })
            .catch(() => {
                resultDiv.textContent = 'Lỗi kết nối máy chủ.';
                resultDiv.className = 'mt-4 text-red-600 text-sm';
            });
        });
    </script>
</body>
</html>