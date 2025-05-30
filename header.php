<?php
// Kiểm tra session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy thông tin người dùng từ session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet">
    
    <!-- Scripts -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f0fdf4;
        }

        .navbar {
            background: linear-gradient(180deg, #1a4731, #2f855a);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .admin-dropdown {
            position: relative;
        }

        .admin-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 50;
        }

        .admin-dropdown:hover .admin-menu {
            display: block;
        }

        .admin-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #1a4731;
            transition: all 0.3s ease;
        }

        .admin-menu a:hover {
            background: #f0fdf4;
        }

        .admin-menu a i {
            margin-right: 0.5rem;
            width: 1.25rem;
            height: 1.25rem;
        }

        /* Custom styles */
        <?php if (isset($customStyles)): ?>
            <?php echo $customStyles; ?>
        <?php endif; ?>
    </style>
</head>
<body class="bg-green-50">
    <!-- Navbar -->
    <nav class="navbar fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Tiêu đề trang -->
                <?php if (isset($pageTitle)): ?>
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-white"><?php echo $pageTitle; ?></h1>
                    </div>
                <?php endif; ?>

                <!-- Admin Account -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="admin-dropdown">
                        <button class="flex items-center space-x-3 text-white focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                                <i data-feather="user" class="w-5 h-5 text-white"></i>
                            </div>
                            <span class="font-medium"><?php echo htmlspecialchars($username); ?></span>
                            <i data-feather="chevron-down" class="w-4 h-4"></i>
                        </button>
                        <div class="admin-menu">
                            <a href="profile.php">
                                <i data-feather="user"></i>
                                Thông tin tài khoản
                            </a>
                            <a href="settings.php">
                                <i data-feather="settings"></i>
                                Cài đặt
                            </a>
                            <hr class="my-1 border-gray-200">
                            <a href="dashboard.php?logout=true" class="text-red-600 hover:text-red-700">
                                <i data-feather="log-out"></i>
                                Đăng xuất
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="fixed top-20 right-4 z-50 animate-fade-in" id="successMessage">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg flex items-center gap-2">
                <i data-feather="check-circle" class="w-5 h-5"></i>
                <p><?php echo $_SESSION['message']; ?></p>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                    <i data-feather="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="fixed top-20 right-4 z-50 animate-fade-in" id="errorMessage">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg flex items-center gap-2">
                <i data-feather="alert-circle" class="w-5 h-5"></i>
                <p><?php echo $_SESSION['error']; ?></p>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                    <i data-feather="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Main Content Container -->
    <div class="pt-16"> <!-- Add padding-top to account for fixed navbar -->

    <script>
        // Initialize Feather Icons
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();

            // Auto-hide flash messages after 5 seconds
            const messages = document.querySelectorAll('#successMessage, #errorMessage');
            messages.forEach(msg => {
                setTimeout(() => {
                    msg.classList.add('opacity-0', 'transition-opacity');
                    setTimeout(() => msg.remove(), 300);
                }, 5000);
            });
        });
    </script> 