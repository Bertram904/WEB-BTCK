<?php
// Kiểm tra session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy trang hiện tại
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<div class="sidebar">
    <h2 class="text-xl font-bold mb-6 flex items-center">
        <i data-feather="coffee" class="mr-2"></i> Matcha Vibe
    </h2>
    <a href="dashboard.php" class="font-medium <?php echo $currentPage === 'dashboard' ? 'bg-white bg-opacity-20' : ''; ?>">
        <i data-feather="home" class="mr-2"></i> Trang chủ
    </a>
    <a href="birthday_wishes.php" class="font-medium <?php echo $currentPage === 'birthday_wishes' ? 'bg-white bg-opacity-20' : ''; ?>">
        <i data-feather="gift" class="mr-2"></i> Lời chúc sinh nhật
    </a>
    <a href="purchase_history.php" class="font-medium <?php echo $currentPage === 'purchase_history' ? 'bg-white bg-opacity-20' : ''; ?>">
        <i data-feather="shopping-cart" class="mr-2"></i> Lịch sử mua hàng
    </a>
    <a href="statistics.php" class="font-medium <?php echo $currentPage === 'statistics' ? 'bg-white bg-opacity-20' : ''; ?>">
        <i data-feather="bar-chart-2" class="mr-2"></i> Thống kê
    </a>
    <a href="dashboard.php?logout=true" class="text-red-200 hover:bg-red-600 font-medium">
        <i data-feather="log-out" class="mr-2"></i> Đăng xuất
    </a>
</div> 