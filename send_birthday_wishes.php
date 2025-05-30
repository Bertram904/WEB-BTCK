<?php
require_once 'config.php';
require_once 'classes/BirthdayWishes.php';

// Kiểm tra xem script có được chạy từ command line không
$isCLI = (php_sapi_name() === 'cli');

try {
    $birthdayWishes = new BirthdayWishes();
    $results = $birthdayWishes->sendAutomaticWishes();
    
    if ($isCLI) {
        // Output cho command line
        echo "=== Kết quả gửi lời chúc sinh nhật ===\n\n";
        
        if (!empty($results['success'])) {
            echo "Đã gửi thành công " . count($results['success']) . " lời chúc:\n";
            foreach ($results['success'] as $success) {
                echo "- {$success['name']}: {$success['message']}\n";
            }
        } else {
            echo "Không có lời chúc nào được gửi.\n";
        }
        
        if (!empty($results['failed'])) {
            echo "\nLỗi khi gửi " . count($results['failed']) . " lời chúc:\n";
            foreach ($results['failed'] as $fail) {
                echo "- {$fail['name']}: {$fail['error']}\n";
            }
        }
    } else {
        // Output cho web interface
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Đã gửi ' . count($results['success']) . ' lời chúc sinh nhật thành công',
            'data' => $results
        ]);
    }
} catch (Exception $e) {
    if ($isCLI) {
        echo "Lỗi: " . $e->getMessage() . "\n";
        exit(1);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>