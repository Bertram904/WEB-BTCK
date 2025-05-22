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
// Database connection
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Matcha Vibe Co., Ltd.</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" integrity="sha384-oD3b9D9Y7Xq8f1v0z0z0z0z0z0z0z0z0z0z0z0z0z0z0z0z0z0z0z0z0z0z0z0z0" crossorigin="anonymous">
    <script src="https://unpkg.com/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" integrity="sha384-1iL2Q9mKWk7d5+0U1IB9g1jU2jD2vC1H1J5iL3eL3eL3eL3eL3eL3eL3eL3eL3eL" crossorigin="anonymous" defer></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            scroll-behavior: smooth;
            background-color: #f0fdf4;
        }
        .navbar {
            background: linear-gradient(90deg, #1a4731, #2f855a);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .sidebar {
            background: #1a4731;
            color: #ecfdf5;
            height: calc(100vh - 80px);
            position: fixed;
            top: 80px;
            left: 0;
            width: 250px;
            padding: 1.5rem;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 40;
        }
        .sidebar a {
            color: #a7f3d0;
            padding: 0.75rem;
            display: block;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-0.5rem);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        .btn-custom {
            padding: 0.75rem 1.5rem;
            border-radius: 0.625rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-custom:hover {
            transform: translateY(-0.125rem);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .fade-in {
            opacity: 0;
            transform: translateY(1.25rem);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .navbar .nav-links {
                display: none;
            }
            .navbar .nav-links.active {
                display: flex;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: #1a4731;
                padding: 1rem;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            }
        }
    </style>
</head>
<body>
    <!-- oke-->
    <!-- Navbar -->
    <header class="navbar">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between" aria-label="Main navigation">
            <div class="flex items-center space-x-4">
                <img src="https://th.bing.com/th/id/OIP.joBZlDpB5bVLwkpt4r3w8gHaHa?w=626&h=626&rs=1&pid=ImgDetMain" alt="Matcha Vibe Logo" class="h-12 w-12 rounded-full">
                <span class="text-ecfdf5 text-2xl font-bold">Matcha Vibe</span>
            </div>
            <div class="hidden md:flex items-center space-x-2 nav-links">
                <a href="dashboard.php">Quản lý khách hàng</a>
                <a href="statistics.php" class="bg-green-700">Thống kê</a>
                <a href="appointments.php">Đặt lịch hẹn</a>
                <a href="chatbot.php">Chatbot AI</a>
                <a href="settings.php">Cài đặt</a>
                <a href="?logout=true" class="text-red-200 hover:bg-red-600">Đăng xuất</a>
                <a href="#cart" aria-label="Giỏ hàng"><i data-feather="shopping-cart" class="ml-4"></i></a>
            </div>
            <button class="md:hidden hamburger" aria-label="Toggle menu">
                <i data-feather="menu" class="text-ecfdf5 h-8 w-8"></i>
            </button>
        </nav>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar hidden md:block" aria-label="Sidebar navigation">
        <h2 class="text-2xl font-bold mb-6">Admin Menu</h2>
        <a href="dashboard.php"><i data-feather="users" class="mr-2"></i> Quản lý khách hàng</a>
        <a href="statistics.php" class="bg-green-700"><i data-feather="bar-chart-2" class="mr-2"></i> Thống kê</a>
        <a href="appointments.php"><i data-feather="calendar" class="mr-2"></i> Đặt lịch hẹn</a>
        <a href="chatbot.php"><i data-feather="message-square" class="mr-2"></i> Chatbot AI</a>
        <a href="settings.php"><i data-feather="settings" class="mr-2"></i> Cài đặt</a>
        <a href="?logout=true" class="text-red-200 hover:bg-red-600"><i data-feather="log-out" class="mr-2"></i> Đăng xuất</a>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-12 fade-in">Thống kê Doanh nghiệp</h1>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 mb-12">
                <div class="card text-center fade-in">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Tổng Doanh Thu</h3>
                    <p class="text-4xl font-bold text-green-600"><?php echo number_format($total_sales, 2); ?> VND</p>
                </div>
                <div class="card text-center fade-in">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Tổng Đơn Hàng</h3>
                    <p class="text-4xl font-bold text-green-600"><?php echo $total_orders; ?></p>
                </div>
                <div class="card text-center fade-in">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Tổng Khách Hàng</h3>
                    <p class="text-4xl font-bold text-green-600"><?php echo $total_customers; ?></p>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="card fade-in">
                <h3 class="text-2xl font-semibold text-gray-900 mb-6">Doanh Thu Theo Tháng (12 Tháng Gần Nhất)</h3>
                <canvas id="salesChart" aria-label="Biểu đồ doanh thu theo tháng"></canvas>
            </div>
        </section>
    </main>

    <script>
        // Initialize Feather Icons
        feather.replace();

        // Hamburger menu toggle
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');
        const sidebar = document.querySelector('.sidebar');
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            sidebar.classList.toggle('active');
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

        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const monthlySalesData = <?php echo json_encode($monthly_sales_data); ?>;
        const labels = monthlySalesData.map(data => data.month);
        const sales = monthlySalesData.map(data => parseFloat(data.sales));
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh Thu (VND)',
                    data: sales,
                    borderColor: '#2f855a',
                    backgroundColor: 'rgba(47, 133, 90, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Doanh Thu (VND)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tháng'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    </script>
</body>
</html>