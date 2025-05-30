<?php
/**
 * Statistics Page
 * Hiển thị thống kê và biểu đồ về hoạt động của khách hàng
 * 
 */

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
$activePage = 'statistics';
$pageTitle = 'Thống kê';

try {
    $customer = new Customer();
    $order = new Order();
    
    // Lấy thống kê tổng quan
    $stats = $customer->getStats();
    
    // Lấy dữ liệu cho biểu đồ
    $monthlyRevenue = $customer->getMonthlyRevenue();
    $ageGroups = $customer->getAgeGroups();
    $customerSegments = $customer->getCustomerSpendingStats();
    $purchaseFrequency = $customer->getPurchaseFrequency();
    
    // Format dữ liệu cho biểu đồ
    $revenueLabels = [];
    $revenueData = [];
    foreach ($monthlyRevenue as $data) {
        $revenueLabels[] = $data['month'];
        $revenueData[] = $data['revenue'];
    }
    
    $ageLabels = [];
    $ageData = [];
    foreach ($ageGroups as $group) {
        $ageLabels[] = $group['age_range'];
        $ageData[] = $group['count'];
    }
    
    $segmentLabels = [];
    $segmentData = [];
    foreach ($customerSegments as $segment) {
        $segmentLabels[] = $segment['spending_group'];
        $segmentData[] = $segment['customer_count'];
    }
    
    $frequencyLabels = [];
    $frequencyData = [];
    foreach ($purchaseFrequency as $frequency) {
        $frequencyLabels[] = $frequency['frequency_group'];
        $frequencyData[] = $frequency['customer_count'];
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Matcha Vibe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/style1.css">
    <style>
        
    </style>
</head>
<body>
    

    <main class="main-content">
        <!-- Thống kê tổng quan -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Tổng số khách hàng -->
            <div class="card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <i data-feather="users" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tổng khách hàng</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Đơn hàng 30 ngày -->
            <div class="card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i data-feather="shopping-bag" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Đơn hàng (30 ngày)</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['orders_last_30_days']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Doanh thu 30 ngày -->
            <div class="card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <i data-feather="dollar-sign" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Doanh thu (30 ngày)</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['revenue_last_30_days']); ?>đ</p>
                    </div>
                </div>
            </div>

            <!-- Khách hàng hoạt động -->
            <div class="card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100">
                        <i data-feather="user-check" class="w-6 h-6 text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Khách hàng hoạt động</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['active']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Biểu đồ doanh thu -->
            <div class="card">
                <h2 class="text-lg font-semibold mb-4">Doanh thu theo tháng</h2>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Biểu đồ độ tuổi -->
            <div class="card">
                <h2 class="text-lg font-semibold mb-4">Phân bố độ tuổi khách hàng</h2>
                <div class="chart-container">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Biểu đồ phân khúc -->
            <div class="card">
                <h2 class="text-lg font-semibold mb-4">Phân khúc khách hàng</h2>
                <div class="chart-container">
                    <canvas id="segmentChart"></canvas>
                </div>
            </div>

            <!-- Biểu đồ tần suất mua -->
            <div class="card">
                <h2 class="text-lg font-semibold mb-4">Tần suất mua hàng</h2>
                <div class="chart-container">
                    <canvas id="frequencyChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Khởi tạo icons
        feather.replace();

        // Cấu hình chung cho biểu đồ
        const chartConfig = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        };

        // Biểu đồ doanh thu
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($revenueLabels); ?>,
                datasets: [{
                    label: 'Doanh thu',
                    data: <?php echo json_encode($revenueData); ?>,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                ...chartConfig,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + 'đ';
                            }
                        }
                    }
                }
            }
        });

        // Biểu đồ độ tuổi
        new Chart(document.getElementById('ageChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($ageLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($ageData); ?>,
                    backgroundColor: [
                        '#059669',
                        '#047857',
                        '#065f46',
                        '#064e3b',
                        '#022c22',
                        '#1a4731'
                    ]
                }]
            },
            options: chartConfig
        });

        // Biểu đồ phân khúc
        new Chart(document.getElementById('segmentChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($segmentLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($segmentData); ?>,
                    backgroundColor: [
                        '#059669',
                        '#047857',
                        '#065f46',
                        '#064e3b'
                    ]
                }]
            },
            options: {
                ...chartConfig,
                cutout: '60%'
            }
        });

        // Biểu đồ tần suất
        new Chart(document.getElementById('frequencyChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($frequencyLabels); ?>,
                datasets: [{
                    label: 'Số lượng khách hàng',
                    data: <?php echo json_encode($frequencyData); ?>,
                    backgroundColor: '#059669'
                }]
            },
            options: {
                ...chartConfig,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>