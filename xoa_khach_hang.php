<?php
require_once 'config.php';

// Kiểm tra session và quyền truy cập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    if (!isset($_GET['id'])) {
        throw new Exception("ID khách hàng không được cung cấp");
    }

    $customerId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($customerId === false) {
        throw new Exception("ID khách hàng không hợp lệ");
    }

    $customer = new Customer();
    
    // Kiểm tra dữ liệu liên quan trước khi xóa
    $relatedData = $customer->checkRelatedData($customerId);
    
    // Nếu có dữ liệu liên quan và chưa xác nhận xóa
    if (!isset($_GET['confirm']) && ($relatedData['orders'] > 0 || $relatedData['order_details'] > 0 || $relatedData['messages'] > 0 || $relatedData['birthday_wishes'] > 0)) {
        $_SESSION['warning'] = "Khách hàng này có dữ liệu liên quan. Bạn có chắc chắn muốn xóa?";
        $_SESSION['related_data'] = [
            'orders' => $relatedData['orders'] . ' đơn hàng',
            'order_details' => $relatedData['order_details'] . ' chi tiết đơn hàng',
            'messages' => $relatedData['messages'] . ' tin nhắn',
            'birthday_wishes' => $relatedData['birthday_wishes'] . ' lời chúc sinh nhật'
        ];
        header('Location: confirm_delete.php?id=' . $customerId);
        exit();
    }

    // Thực hiện xóa
    $result = $customer->delete($customerId);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        throw new Exception("Không thể xóa khách hàng");
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

// Chuyển hướng về trang dashboard
header('Location: dashboard.php');
exit();
?>