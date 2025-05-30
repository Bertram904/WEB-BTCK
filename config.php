<?php
// Ngăn xuất đầu ra ngoài ý muốn
ob_start();

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tải các class
require_once 'classes/Database.php';
require_once 'classes/Customer.php';
require_once 'classes/User.php';
require_once 'classes/Message.php';
require_once 'classes/BirthdayWish.php';
require_once 'classes/Order.php';

// Dọn dẹp buffer
ob_end_clean();
?>