<?php
require_once 'Database.php';

class Promotion {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Lấy mức khuyến mãi dựa trên tổng chi tiêu và số lượt mua
     * @param float $totalSpent Tổng chi tiêu
     * @param int $purchaseCount Số lượt mua
     * @return array Thông tin khuyến mãi
     */
    public function getCustomerPromotions($totalSpent, $purchaseCount) {
        $promotions = [];

        // Khuyến mãi dựa trên tổng chi tiêu
        if ($totalSpent >= 10000000) { // VIP (>= 10M)
            $promotions[] = [
                'type' => 'discount',
                'value' => 15,
                'description' => 'Giảm 15% cho mọi đơn hàng',
                'min_order' => 0,
                'level' => 'VIP'
            ];
            $promotions[] = [
                'type' => 'birthday',
                'value' => 30,
                'description' => 'Giảm 30% trong tháng sinh nhật',
                'min_order' => 500000,
                'level' => 'VIP'
            ];
        } 
        elseif ($totalSpent >= 5000000) { // Cao cấp (5M-10M)
            $promotions[] = [
                'type' => 'discount',
                'value' => 10,
                'description' => 'Giảm 10% cho mọi đơn hàng',
                'min_order' => 0,
                'level' => 'Cao cấp'
            ];
            $promotions[] = [
                'type' => 'birthday',
                'value' => 20,
                'description' => 'Giảm 20% trong tháng sinh nhật',
                'min_order' => 300000,
                'level' => 'Cao cấp'
            ];
        }
        elseif ($totalSpent >= 2000000) { // Trung bình (2M-5M)
            $promotions[] = [
                'type' => 'discount',
                'value' => 5,
                'description' => 'Giảm 5% cho mọi đơn hàng',
                'min_order' => 200000,
                'level' => 'Trung bình'
            ];
            $promotions[] = [
                'type' => 'birthday',
                'value' => 15,
                'description' => 'Giảm 15% trong tháng sinh nhật',
                'min_order' => 200000,
                'level' => 'Trung bình'
            ];
        }
        else { // Cơ bản (< 2M)
            if ($purchaseCount >= 5) {
                $promotions[] = [
                    'type' => 'discount',
                    'value' => 3,
                    'description' => 'Giảm 3% cho đơn hàng từ 200.000đ',
                    'min_order' => 200000,
                    'level' => 'Cơ bản'
                ];
            }
            $promotions[] = [
                'type' => 'birthday',
                'value' => 10,
                'description' => 'Giảm 10% trong tháng sinh nhật',
                'min_order' => 100000,
                'level' => 'Cơ bản'
            ];
        }

        // Khuyến mãi dựa trên số lượt mua
        if ($purchaseCount >= 20) {
            $promotions[] = [
                'type' => 'loyalty',
                'value' => 5,
                'description' => 'Thêm 5% giảm giá cho khách hàng thân thiết',
                'min_order' => 0,
                'level' => 'Thân thiết'
            ];
        }
        elseif ($purchaseCount >= 10) {
            $promotions[] = [
                'type' => 'loyalty',
                'value' => 3,
                'description' => 'Thêm 3% giảm giá cho khách hàng thân thiết',
                'min_order' => 0,
                'level' => 'Thân thiết'
            ];
        }

        return $promotions;
    }

    /**
     * Lưu khuyến mãi vào cơ sở dữ liệu
     * @param int $customerId ID khách hàng
     * @param array $promotion Thông tin khuyến mãi
     * @return bool Kết quả lưu
     */
    public function savePromotion($customerId, $promotion) {
        try {
            $conn = $this->db->connect();
            $sql = "INSERT INTO KhuyenMai (MaKhachHang, LoaiKhuyenMai, GiaTri, MoTa, DonHangToiThieu, CapBac, NgayTao, NgayHetHan) 
                    VALUES (:customer_id, :type, :value, :description, :min_order, :level, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->bindParam(':type', $promotion['type'], PDO::PARAM_STR);
            $stmt->bindParam(':value', $promotion['value'], PDO::PARAM_INT);
            $stmt->bindParam(':description', $promotion['description'], PDO::PARAM_STR);
            $stmt->bindParam(':min_order', $promotion['min_order'], PDO::PARAM_INT);
            $stmt->bindParam(':level', $promotion['level'], PDO::PARAM_STR);
            
            $result = $stmt->execute();
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lưu khuyến mãi: " . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách khuyến mãi hiện có của khách hàng
     * @param int $customerId ID khách hàng
     * @return array Danh sách khuyến mãi
     */
    public function getActivePromotions($customerId) {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT * FROM KhuyenMai 
                    WHERE MaKhachHang = :customer_id 
                    AND NgayHetHan >= CURDATE()
                    ORDER BY NgayTao DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy khuyến mãi: " . $e->getMessage());
        }
    }

    /**
     * Cập nhật khuyến mãi cho khách hàng
     * @param int $customerId ID khách hàng
     */
    public function updateCustomerPromotions($customerId) {
        try {
            $conn = $this->db->connect();
            
            // Lấy thông tin mua hàng của khách
            $sql = "SELECT COUNT(DISTINCT o.MaDonHang) as SoLuongMua,
                           COALESCE(SUM(o.TongTien), 0) as TongChiTieu
                    FROM Orders o 
                    WHERE o.MaKhachHang = :customer_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Xóa khuyến mãi cũ
            $sql = "DELETE FROM KhuyenMai 
                    WHERE MaKhachHang = :customer_id 
                    AND NgayHetHan >= CURDATE()";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Tạo khuyến mãi mới
            $promotions = $this->getCustomerPromotions(
                $stats['TongChiTieu'], 
                $stats['SoLuongMua']
            );
            
            foreach ($promotions as $promotion) {
                $this->savePromotion($customerId, $promotion);
            }
            
            $this->db->close();
            return true;
        } catch (PDOException $e) {
            throw new Exception("Lỗi cập nhật khuyến mãi: " . $e->getMessage());
        }
    }
} 