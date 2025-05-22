<?php
session_start();

// Check if user is logged in
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

// Mock customer data (replace with database query in production)
$customers = [
    ['id' => 1, 'name' => 'Nguyễn Văn A', 'email' => 'nguyenvana@example.com', 'phone' => '0901234567', 'status' => 'active'],
    ['id' => 2, 'name' => 'Trần Thị B', 'email' => 'tranb@example.com', 'phone' => '0912345678', 'status' => 'inactive'],
    ['id' => 3, 'name' => 'Lê Văn C', 'email' => 'levanc@example.com', 'phone' => '0923456789', 'status' => 'active'],
];

// Mock purchase history data (replace with database query)
$purchase_history = [
    1 => [
        ['order_id' => 101, 'date' => '2025-04-10', 'products' => 'Ceremonial Matcha, Matcha Latte', 'total' => 450000],
        ['order_id' => 102, 'date' => '2025-05-01', 'products' => 'Culinary Matcha', 'total' => 200000],
    ],
    2 => [],
    3 => [
        ['order_id' => 103, 'date' => '2025-03-15', 'products' => 'Matcha Latte', 'total' => 150000],
    ],
];

// Calculate stats
$totalCustomers = count($customers);
$activeCustomers = count(array_filter($customers, fn($c) => $c['status'] === 'active'));
$inactiveCustomers = $totalCustomers - $activeCustomers;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management Dashboard - Matcha Vibe</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0fdf4;
        }
        .sidebar {
            background: linear-gradient(180deg, #1a4731, #2f855a);
            color: #ecfdf5;
            height: 100vh;
            position: fixed;
            width: 260px;
            padding: 30px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .sidebar h2 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
        }
        .sidebar a {
            color: #ecfdf5;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #a7f3d0;
        }
        .main-content {
            margin-left: 260px;
            padding: 40px;
            min-height: 100vh;
        }
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 32px;
            margin-bottom: 24px;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-4px);
        }
        .table-header {
            background: #dcfce7;
            padding: 12px;
            font-weight: 600;
            cursor: pointer;
            color: #1a4731;
            border-radius: 8px;
        }
        .table-header:hover {
            background: #a7f3d0;
        }
        .table-row:hover {
            background: #f0fdf4;
        }
        .status-active {
            background: #dcfce7;
            color: #2f855a;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .search-bar {
            width: 100%;
            max-width: 320px;
            padding: 10px 16px;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            background: #ffffff;
            transition: all 0.3s ease;
        }
        .search-bar:focus {
            border-color: #2f855a;
            box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.1);
        }
        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
                padding: 20px;
            }
            .search-bar {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>
            <i data-feather="grid" class="mr-2"></i> Matcha Vibe CRM
        </h2>
        <a href="index.php"><i data-feather="home" class="mr-2"></i> Trang chủ</a>
        <a href="statistics.php"><i data-feather="bar-chart-2" class="mr-2"></i> Thống kê</a>
        <a href="appointments.php"><i data-feather="calendar" class="mr-2"></i> Đặt lịch hẹn</a>
        <a href="chatbot.php"><i data-feather="message-circle" class="mr-2"></i> Chatbot AI</a>
        <a href="settings.php"><i data-feather="settings" class="mr-2"></i> Cài đặt</a>
        <?php if ($isLoggedIn): ?>
            <a href="?logout=true" class="text-red-200 hover:bg-red-600"><i data-feather="log-out" class="mr-2"></i> Đăng xuất</a>
        <?php else: ?>
            <a href="login.php"><i data-feather="log-in" class="mr-2"></i> Đăng nhập</a>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Quản lý khách hàng</h1>
            <div class="flex items-center space-x-4 mt-4 sm:mt-0">
                <input type="text" id="searchInput" class="search-bar" placeholder="Tìm kiếm khách hàng...">
                <button class="bg-green-600 text-white btn hover:bg-green-700">Thêm khách hàng</button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div class="card flex items-center">
                <div class="mr-4">
                    <i data-feather="users" class="w-10 h-10 text-green-600"></i>
                </div>
                <div>
                    <p class="text-gray-500">Tổng khách hàng</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $totalCustomers; ?></h3>
                </div>
            </div>
            <div class="card flex items-center">
                <div class="mr-4">
                    <i data-feather="check-circle" class="w-10 h-10 text-green-600"></i>
                </div>
                <div>
                    <p class="text-gray-500">Hoạt động</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $activeCustomers; ?></h3>
                </div>
            </div>
            <div class="card flex items-center">
                <div class="mr-4">
                    <i data-feather="x-circle" class="w-10 h-10 text-red-600"></i>
                </div>
                <div>
                    <p class="text-gray-500">Không hoạt động</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $inactiveCustomers; ?></h3>
                </div>
            </div>
        </div>

        <!-- Customer Table -->
        <div class="card">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Danh sách khách hàng</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="customerTable">
                    <thead>
                        <tr class="table-header">
                            <th class="p-4" data-sort="name">Tên</th>
                            <th data-sort="email">Email</th>
                            <th data-sort="phone">Số điện thoại</th>
                            <th data-sort="status">Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr class="table-row border-t border-gray-200">
                                <td class="p-4"><?php echo htmlspecialchars($customer['name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                <td>
                                    <span class="<?php echo $customer['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $customer['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                    </span>
                                </td>
                                <td class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-800 edit-btn" data-id="<?php echo $customer['id']; ?>">Sửa</button>
                                    <button class="text-red-600 hover:text-red-800 delete-btn" data-id="<?php echo $customer['id']; ?>">Xóa</button>
                                    <button class="text-green-600 hover:text-green-800 history-btn" data-id="<?php echo $customer['id']; ?>">Lịch sử</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        feather.replace(); // Load feather icons

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const table = document.getElementById('customerTable');
        const rows = table.querySelectorAll('tbody tr');

        searchInput.addEventListener('input', () => {
            const searchTerm = searchInput.value.toLowerCase();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Sort functionality
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const sortKey = header.dataset.sort;
                const isAscending = header.classList.toggle('asc');
                const tbody = table.querySelector('tbody');
                const rowsArray = Array.from(rows);

                rowsArray.sort((a, b) => {
                    const aText = a.querySelector(`td:nth-child(${Array.from(headers).indexOf(header) + 1})`).textContent;
                    const bText = b.querySelector(`td:nth-child(${Array.from(headers).indexOf(header) + 1})`).textContent;
                    return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
                });

                tbody.innerHTML = '';
                rowsArray.forEach(row => tbody.appendChild(row));
            });
        });

        // Button actions
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                alert(`Chức năng sửa cho khách hàng ID ${id} đang được phát triển!`);
            });
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                if (confirm(`Bạn có chắc chắn muốn xóa khách hàng ID ${id}?`)) {
                    alert(`Khách hàng ID ${id} đã được xóa!`);
                }
            });
        });

        // Purchase history button
        document.querySelectorAll('.history-btn').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const history = <?php echo json_encode($purchase_history); ?>[id] || [];
                if (history.length === 0) {
                    alert(`Khách hàng ID ${id} chưa có lịch sử mua hàng.`);
                } else {
                    let message = `Lịch sử mua hàng của khách hàng ID ${id}:\n\n`;
                    history.forEach(order => {
                        message += `Mã đơn: ${order.order_id}\nNgày: ${order.date}\nSản phẩm: ${order.products}\nTổng: ${order.total.toLocaleString()} VND\n\n`;
                    });
                    alert(message);
                }
            });
        });

        // Add customer button
        document.querySelector('.bg-green-600').addEventListener('click', () => {
            alert('Chức năng thêm khách hàng đang được phát triển!');
        });
    </script>
</body>
</html>