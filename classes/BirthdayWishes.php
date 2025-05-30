<?php
require_once 'Database.php';
require_once 'Customer.php';

class BirthdayWishes {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Lấy danh sách khách hàng có sinh nhật trong ngày
     * @return array Danh sách khách hàng
     */
    public function getTodayBirthdays() {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT k.*, 
                          COUNT(DISTINCT o.MaDonHang) as SoLuongMua,
                          COALESCE(SUM(o.TongTien), 0) as TongChiTieu
                   FROM KhachHang k
                   LEFT JOIN Orders o ON k.MaKhachHang = o.MaKhachHang
                   WHERE DATE_FORMAT(k.NgaySinh, '%m-%d') = DATE_FORMAT(CURDATE(), '%m-%d')
                   AND k.TrangThai = 'active'
                   GROUP BY k.MaKhachHang";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy danh sách sinh nhật: " . $e->getMessage());
        }
    }

    /**
     * Tạo lời chúc sinh nhật tự động dựa trên thông tin khách hàng
     * @param array $customer Thông tin khách hàng
     * @return string Lời chúc sinh nhật
     */
    private function generateWishMessage($customer) {
        $templates = [
            "Chúc mừng sinh nhật {name}! 🎂 Matcha Vibe gửi tặng bạn {voucher} để cảm ơn vì đã đồng hành cùng chúng tôi.",
            "Happy Birthday {name}! 🎉 Nhân dịp sinh nhật của bạn, Matcha Vibe xin tặng {voucher} để bạn thưởng thức những món ngon của chúng tôi.",
            "Sinh nhật vui vẻ {name}! 🎈 Để kỷ niệm ngày đặc biệt này, chúng tôi tặng bạn {voucher}. Cảm ơn vì đã luôn ủng hộ Matcha Vibe!"
        ];

        // Chọn template ngẫu nhiên
        $template = $templates[array_rand($templates)];

        // Xác định voucher dựa trên lịch sử mua hàng
        $voucher = $this->determineVoucher($customer);

        // Thay thế các placeholder
        $message = str_replace(
            ['{name}', '{voucher}'],
            [$customer['TenKhachHang'], $voucher],
            $template
        );

        return $message;
    }

    /**
     * Xác định voucher phù hợp dựa trên lịch sử mua hàng
     * @param array $customer Thông tin khách hàng
     * @return string Thông tin voucher
     */
    private function determineVoucher($customer) {
        $totalSpent = floatval($customer['TongChiTieu']);
        $purchaseCount = intval($customer['SoLuongMua']);

        if ($totalSpent >= 10000000) {
            return "voucher giảm 30% cho hóa đơn từ 500k";
        } elseif ($totalSpent >= 5000000) {
            return "voucher giảm 20% cho hóa đơn từ 300k";
        } elseif ($purchaseCount >= 5) {
            return "voucher giảm 15% cho hóa đơn từ 200k";
        } else {
            return "voucher giảm 10% cho hóa đơn từ 100k";
        }
    }

    /**
     * Gửi lời chúc sinh nhật tự động
     * @return array Kết quả gửi lời chúc
     */
    public function sendAutomaticWishes() {
        try {
            $conn = $this->db->connect();
            $birthdays = $this->getTodayBirthdays();
            $results = ['success' => [], 'failed' => []];

            foreach ($birthdays as $customer) {
                try {
                    // Kiểm tra xem đã gửi lời chúc chưa
                    $checkSql = "SELECT COUNT(*) FROM LoiChucSinhNhat 
                                WHERE MaKhachHang = :customer_id 
                                AND DATE(NgayGui) = CURDATE()";
                    $checkStmt = $conn->prepare($checkSql);
                    $checkStmt->bindParam(':customer_id', $customer['MaKhachHang'], PDO::PARAM_INT);
                    $checkStmt->execute();
                    
                    if ($checkStmt->fetchColumn() > 0) {
                        continue; // Bỏ qua nếu đã gửi
                    }

                    // Tạo lời chúc
                    $message = $this->generateWishMessage($customer);

                    // Lưu vào database
                    $sql = "INSERT INTO LoiChucSinhNhat (MaKhachHang, NoiDung, NgayGui, TrangThai) 
                           VALUES (:customer_id, :message, NOW(), 'sent')";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':customer_id', $customer['MaKhachHang'], PDO::PARAM_INT);
                    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
                    
                    if ($stmt->execute()) {
                        // Gửi tin nhắn thông báo
                        $notifySql = "INSERT INTO TinNhan (MaKhachHang, NoiDung, ThoiGian, LaNhanVien, LaSinhNhat) 
                                    VALUES (:customer_id, :message, NOW(), 1, 1)";
                        
                        $notifyStmt = $conn->prepare($notifySql);
                        $notifyStmt->bindParam(':customer_id', $customer['MaKhachHang'], PDO::PARAM_INT);
                        $notifyStmt->bindParam(':message', $message, PDO::PARAM_STR);
                        $notifyStmt->execute();

                        $results['success'][] = [
                            'customer_id' => $customer['MaKhachHang'],
                            'name' => $customer['TenKhachHang'],
                            'message' => $message
                        ];
                    }
                } catch (Exception $e) {
                    $results['failed'][] = [
                        'customer_id' => $customer['MaKhachHang'],
                        'name' => $customer['TenKhachHang'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            $this->db->close();
            return $results;
        } catch (PDOException $e) {
            throw new Exception("Lỗi gửi lời chúc sinh nhật: " . $e->getMessage());
        }
    }

    /**
     * Lấy lịch sử lời chúc sinh nhật
     * @param int $days Số ngày gần đây (mặc định 30 ngày)
     * @return array Lịch sử lời chúc
     */
    public function getWishHistory($days = 30) {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT lc.*, k.TenKhachHang, k.Email
                   FROM LoiChucSinhNhat lc
                   JOIN KhachHang k ON lc.MaKhachHang = k.MaKhachHang
                   WHERE lc.NgayGui >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                   ORDER BY lc.NgayGui DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy lịch sử lời chúc: " . $e->getMessage());
        }
    }
} 