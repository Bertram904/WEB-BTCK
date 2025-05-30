<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?error=' . urlencode('Phương thức không hợp lệ'));
    exit();
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$birthday = trim($_POST['birthday'] ?? '') ?: null;
$status = trim($_POST['status'] ?? 'active');

if ($id <= 0 || empty($name) || empty($email)) {
    header('Location: sua_khach_hang.php?id=' . $id . '&error=' . urlencode('Vui lòng nhập đầy đủ tên và email'));
    exit();
}

try {
    $customer = new Customer();
    if ($customer->update($id, $name, $phone, $email, $address, $birthday, $status)) {
        header('Location: dashboard.php?message=' . urlencode('Cập nhật khách hàng thành công'));
        exit();
    } else {
        header('Location: sua_khach_hang.php?id=' . $id . '&error=' . urlencode('Không thể cập nhật khách hàng'));
        exit();
    }
} catch (Exception $e) {
    header('Location: sua_khach_hang.php?id=' . $id . '&error=' . urlencode('Lỗi: ' . $e->getMessage()));
    exit();
}
?>