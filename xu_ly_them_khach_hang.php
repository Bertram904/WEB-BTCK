<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '') ?: null;
    $status = trim($_POST['status'] ?? 'active');

    if (empty($name) || empty($email)) {
        header('Location: them_khach_hang.php?error=' . urlencode('Vui lòng nhập đầy đủ tên và email'));
        exit();
    }

    try {
        $customer = new Customer();
        if ($customer->add($name, $phone, $email, $address, $birthday, $status)) {
            header('Location: dashboard.php?message=' . urlencode('Thêm khách hàng thành công'));
            exit();
        } else {
            header('Location: them_khach_hang.php?error=' . urlencode('Không thể thêm khách hàng'));
            exit();
        }
    } catch (Exception $e) {
        header('Location: them_khach_hang.php?error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: them_khach_hang.php');
    exit();
}
?>