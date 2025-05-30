<!-- Sidebar -->
<div class="sidebar">
    <h2 class="text-xl font-bold mb-6 flex items-center"><i data-feather="coffee" class="mr-2"></i> Matcha Vibe</h2>
    <a href="index.php" class="font-medium <?php echo $activePage === 'dashboard' ? 'bg-white bg-opacity-20' : ''; ?>">
        <i data-feather="home" class="mr-2"></i> Quản lý khách hàng
    </a>
    <a href="birthday_wishes.php" class="font-medium <?php echo $activePage === 'birthday' ? 'bg-white bg-opacity-20' : ''; ?>">
        <i data-feather="gift" class="mr-2"></i> Lời chúc sinh nhật
    </a>
    <a href="purchase_history.php" class="font-medium <?php echo $activePage === 'purchases' ? 'bg-white bg-opacity-20' : ''; ?>">
        <i data-feather="shopping-cart" class="mr-2"></i> Lịch sử mua hàng
    </a>
    <a href="statistics.php" class="font-medium <?php echo $activePage === 'statistics' ? 'bg-white bg-opacity-20' : ''; ?>">
        <i data-feather="bar-chart-2" class="mr-2"></i> Thống kê
    </a>
    <a href="chatbot.php" class="font-medium <?php echo $activePage === 'settings' ? 'bg-white bg-opacity-20' : ''; ?>">
        <i data-feather="message-circle" class="mr-2"></i> Chatbot
    <a href="dashboard.php?logout=true" class="text-red-200 hover:bg-red-600 font-medium">
        <i data-feather="log-out" class="mr-2"></i> Đăng xuất
    </a>
</div>