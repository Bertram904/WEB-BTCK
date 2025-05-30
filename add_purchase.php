<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
    $items = [];

    // Lấy danh sách sản phẩm từ form
    $products = $_POST['product'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['price'] ?? [];

    for ($i = 0; $i < count($products); $i++) {
        if (!empty($products[$i]) && !empty($quantities[$i]) && !empty($prices[$i])) {
            $items[] = [
                'product' => trim($products[$i]),
                'quantity' => (int)$quantities[$i],
                'price' => (float)$prices[$i]
            ];
        }
    }

    if ($customerId <= 0 || empty($items)) {
        $error = 'Vui lòng nhập đầy đủ thông tin đơn hàng';
    } else {
        try {
            $order = new Order();
            if ($order->add($customerId, $items)) {
                header('Location: purchase_history.php?customer_id=' . $customerId . '&message=' . urlencode('Thêm đơn hàng thành công'));
                exit();
            } else {
                $error = 'Không thể thêm đơn hàng';
            }
        } catch (Exception $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

try {
    $customer = new Customer();
    $customers = $customer->getAll();
} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm đơn hàng - Matcha Vibe</title>
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
        .item-row { display: grid; grid-template-columns: 2fr 1fr 1fr 50px; gap: 16px; align-items: center; }
        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .main-content { margin-left: 200px; padding: 16px; }
            .item-row { grid-template-columns: 1fr; gap: 8px; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="text-xl font-bold mb-6 flex items-center"><i data-feather="coffee" class="mr-2"></i> Matcha Vibe</h2>
        <a href="dashboard.php" class="font-medium"><i data-feather="home" class="mr-2"></i> Trang chủ</a>
        <a href="birthday_wishes.php" class="font-medium"><i data-feather="gift" class="mr-2"></i> Lời chúc sinh nhật</a>
        <a href="purchase_history.php" class="font-medium bg-white bg-opacity-20"><i data-feather="shopping-cart" class="mr-2"></i> Lịch sử mua hàng</a>
        <a href="#" class="font-medium"><i data-feather="bar-chart-2" class="mr-2"></i> Thống kê</a>
        <a href="#" class="font-medium"><i data-feather="calendar" class="mr-2"></i> Lịch hẹn</a>
        <a href="dashboard.php?logout=true" class="text-red-200 hover:bg-red-600 font-medium"><i data-feather="log-out" class="mr-2"></i> Đăng xuất</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Thêm đơn hàng</h1>
        <div class="card">
            <?php if (isset($error)): ?>
                <p class="error-message mb-4"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST" action="add_purchase.php">
                <div class="mb-6">
                    <label for="customer_id" class="block text-sm font-medium text-gray-700">Khách hàng</label>
                    <select id="customer_id" name="customer_id" class="mt-1 block w-full border rounded-lg p-3 input-field focus:outline-none" required>
                        <option value="">Chọn khách hàng</option>
                        <?php foreach ($customers as $cust): ?>
                            <option value="<?php echo $cust['MaKhachHang']; ?>"><?php echo htmlspecialchars($cust['TenKhachHang']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sản phẩm</label>
                    <div id="items">
                        <div class="item-row mb-4">
                            <input type="text" name="product[]" class="border rounded-lg p-3 input-field" placeholder="Tên sản phẩm" required>
                            <input type="number" name="quantity[]" class="border rounded-lg p-3 input-field" placeholder="Số lượng" min="1" required onchange="updateTotal()" onkeyup="updateTotal()">
                            <input type="number" name="price[]" class="border rounded-lg p-3 input-field" placeholder="Đơn giá" min="0" step="1000" required onchange="updateTotal()" onkeyup="updateTotal()">
                            <button type="button" class="text-red-600 hover:text-red-800 remove-item"><i data-feather="trash-2"></i></button>
                        </div>
                    </div>
                    <button type="button" id="addItem" class="text-green-600 hover:text-green-800 font-semibold"><i data-feather="plus-circle" class="inline w-5 h-5"></i> Thêm sản phẩm</button>
                </div>
                
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-medium text-gray-700">Tổng tiền:</span>
                        <span id="totalDisplay" class="text-2xl font-bold text-green-600">0 ₫</span>
                    </div>
                </div>

                <div class="flex space-x-4">
                    <button type="submit" class="bg-green-600 text-white btn rounded-lg px-4 py-2 font-semibold hover:bg-green-700">Thêm đơn hàng</button>
                    <a href="purchase_history.php" class="bg-gray-300 text-gray-700 btn rounded-lg px-4 py-2 font-semibold hover:bg-gray-400">Hủy</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
    <script>
        feather.replace();

        const itemsContainer = document.getElementById('items');
        const addItemBtn = document.getElementById('addItem');

        function calculateTotal() {
            let total = 0;
            const quantities = document.getElementsByName('quantity[]');
            const prices = document.getElementsByName('price[]');
            
            for (let i = 0; i < quantities.length; i++) {
                const quantity = parseInt(quantities[i].value) || 0;
                const price = parseFloat(prices[i].value) || 0;
                total += quantity * price;
            }
            
            // Display total
            const totalDisplay = document.getElementById('totalDisplay');
            if (totalDisplay) {
                totalDisplay.textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(total);
            }
        }

        function updateTotal() {
            calculateTotal();
        }

        addItemBtn.addEventListener('click', () => {
            const newItem = document.createElement('div');
            newItem.className = 'item-row mb-4';
            newItem.innerHTML = `
                <input type="text" name="product[]" class="border rounded-lg p-3 input-field" placeholder="Tên sản phẩm" required>
                <input type="number" name="quantity[]" class="border rounded-lg p-3 input-field" placeholder="Số lượng" min="1" required onchange="updateTotal()" onkeyup="updateTotal()">
                <input type="number" name="price[]" class="border rounded-lg p-3 input-field" placeholder="Đơn giá" min="0" step="1000" required onchange="updateTotal()" onkeyup="updateTotal()">
                <button type="button" class="text-red-600 hover:text-red-800 remove-item"><i data-feather="trash-2"></i></button>
            `;
            itemsContainer.appendChild(newItem);
            feather.replace();
            updateRemoveButtons();
            calculateTotal();
        });

        function updateRemoveButtons() {
            const removeButtons = document.querySelectorAll('.remove-item');
            removeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    if (itemsContainer.children.length > 1) {
                        button.parentElement.remove();
                        calculateTotal();
                    }
                });
            });
        }

        // Add event listeners to initial inputs
        document.addEventListener('DOMContentLoaded', () => {
            const initialQuantity = document.querySelector('input[name="quantity[]"]');
            const initialPrice = document.querySelector('input[name="price[]"]');
            if (initialQuantity && initialPrice) {
                initialQuantity.addEventListener('input', updateTotal);
                initialPrice.addEventListener('input', updateTotal);
            }
            updateRemoveButtons();
            calculateTotal();
        });
    </script>
</body>
</html>