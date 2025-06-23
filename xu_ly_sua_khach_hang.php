<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập!']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
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
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ tên và email']);
    exit();
}

try {
    $customer = new Customer();
    if ($customer->update($id, $name, $phone, $email, $address, $birthday, $status)) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật khách hàng thành công']);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật khách hàng']);
        exit();
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    exit();
}
?>