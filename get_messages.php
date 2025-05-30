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

    $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
    if ($customerId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID khách hàng không hợp lệ']);
        exit();
    }

    require_once 'classes/Message.php';
    $message = new Message();
    $messages = $message->getByCustomer($customerId);
    
    // Format messages for frontend
    $formattedMessages = array_map(function($msg) {
        return [
            'message' => $msg['NoiDung'],
            'sender_type' => $msg['LaNhanVien'] ? 'admin' : 'customer',
            'sent_at' => date('d/m/Y H:i', strtotime($msg['ThoiGian']))
        ];
    }, $messages);
    
    echo json_encode($formattedMessages);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Lỗi server: ' . $e->getMessage()]);
} finally {
    ob_end_flush();
}
?>