<?php
require_once 'Database.php';

class Message {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function send($customerId, $content, $isStaff = true) {
        if (empty($content) || $customerId <= 0) {
            throw new Exception("Nội dung hoặc ID khách hàng không hợp lệ");
        }

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("INSERT INTO TinNhan (MaKhachHang, NoiDung, ThoiGian, LaNhanVien) VALUES (:customer_id, :content, NOW(), :is_staff)");
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':is_staff', $isStaff, PDO::PARAM_BOOL);
            $stmt->execute();
            $this->db->close();
            return true;
        } catch (PDOException $e) {
            throw new Exception("Lỗi gửi tin nhắn: " . $e->getMessage());
        }
    }

    public function getByCustomer($customerId) {
        if ($customerId <= 0) {
            throw new Exception("ID khách hàng không hợp lệ");
        }

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("SELECT MaTinNhan, MaKhachHang, NoiDung, ThoiGian, LaNhanVien 
                                    FROM TinNhan 
                                    WHERE MaKhachHang = :customer_id 
                                    ORDER BY ThoiGian DESC 
                                    LIMIT 50");
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $this->db->close();
            return array_reverse($result);
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy tin nhắn: " . $e->getMessage());
        }
    }
}
?>