<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $user = new User();
        $user->login($username, $password);
        header('Location: dashboard.php');
        exit();
    } catch (Exception $e) {
        $error = htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Matcha Vibe</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f0fdf4, #dcfce7); }
        .login-container { max-width: 400px; margin: 60px auto; }
        .card { background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1); padding: 32px; }
        .input-field { transition: border-color 0.3s ease, box-shadow 0.3s ease; }
        .input-field:focus { border-color: #2f855a; box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.1); }
        .btn { background: #2f855a; transition: transform 0.2s ease, background 0.3s ease; }
        .btn:hover { background: #15803d; transform: translateY(-2px); }
        .error { color: #dc2626; font-size: 0.875rem; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-6">Đăng nhập Matcha Vibe</h2>
            <form id="loginForm" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" class="mt-1 block w-full border rounded-lg p-3 input-field focus:outline-none" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                    <input type="password" id="password" name="password" class="mt-1 block w-full border rounded-lg p-3 input-field focus:outline-none" required>
                </div>
                <button type="submit" class="w-full bg-green-600 text-white btn rounded-lg p-3 font-semibold flex justify-center items-center" id="loginBtn">
                    <span id="btnText">Đăng nhập</span>
                    <svg id="loadingSpinner" class="hidden animate-spin h-5 w-5 ml-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
                <?php if ($error): ?>
                    <p class="error mt-4 text-center"><?php echo $error; ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const spinner = document.getElementById('loadingSpinner');
            btn.disabled = true;
            btnText.textContent = 'Đang đăng nhập...';
            spinner.classList.remove('hidden');
        });
    </script>
</body>
</html>