<?php
ob_start();
header('Content-Type: application/json; charset=UTF-8');

try {
    require_once 'config.php';

    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Phương thức không hợp lệ']);
        exit();
    }

    $customerId = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
    $content = isset($_POST['message']) ? trim($_POST['message']) : '';
    if ($customerId <= 0 || empty($content)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Thiếu thông tin khách hàng hoặc nội dung']);
        exit();
    }

    require_once 'classes/Message.php';
    $message = new Message();
    $message->send($customerId, $content, true, false);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Lỗi server: ' . $e->getMessage()]);
} finally {
    ob_end_flush();
}
?>