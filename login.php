<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Replace with your authentication logic (e.g., check against a database)
    if ($username === 'admin' && $password === 'password') {
        $_SESSION['user_id'] = 1; // Example user ID
        header('Location: index.php');
        exit();
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Đăng nhập</h2>
        <?php if (isset($error)): ?>
            <p class="text-red-600 mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700">Tên đăng nhập</label>
                <input type="text" name="username" class="w-full p-2 border rounded-md" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Mật khẩu</label>
                <input type="password" name="password" class="w-full p-2 border rounded-md" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700">Đăng nhập</button>
        </form>
    </div>
</body>
</html>