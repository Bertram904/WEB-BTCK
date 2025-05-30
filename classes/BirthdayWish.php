<?php
require_once 'Database.php';

class BirthdayWish {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = null;
    }

    private function connect() {
        if (!$this->conn) {
            $this->conn = $this->db->connect();
        }
        return $this->conn;
    }

    private function disconnect() {
        if ($this->conn) {
            $this->db->close();
            $this->conn = null;
        }
    }

    public function getCustomersWithBirthday($range = 7) {
        try {
            $conn = $this->connect();
            $sql = "SELECT k.*, 
                          COUNT(DISTINCT o.MaDonHang) as SoLuongMua,
                          COALESCE(SUM(o.TongTien), 0) as TongChiTieu,
                          DATEDIFF(
                              DATE_ADD(
                                  DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(k.NgaySinh), '-', DAY(k.NgaySinh))),
                                  INTERVAL CASE 
                                      WHEN DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(k.NgaySinh), '-', DAY(k.NgaySinh))) < CURDATE() 
                                      THEN 1 
                                      ELSE 0 
                                  END YEAR
                              ),
                              CURDATE()
                          ) as NgayConLai
                   FROM khachhang k
                   LEFT JOIN orders o ON k.MaKhachHang = o.MaKhachHang
                   WHERE 
                       k.NgaySinh IS NOT NULL 
                       AND (
                           (
                               DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(k.NgaySinh), '-', DAY(k.NgaySinh))) >= CURDATE()
                               AND DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(k.NgaySinh), '-', DAY(k.NgaySinh))) <= DATE_ADD(CURDATE(), INTERVAL :range DAY)
                           )
                           OR (
                               DATE(CONCAT(YEAR(CURDATE()) + 1, '-', MONTH(k.NgaySinh), '-', DAY(k.NgaySinh))) <= DATE_ADD(CURDATE(), INTERVAL :range DAY)
                           )
                       )
                   GROUP BY k.MaKhachHang
                   ORDER BY NgayConLai ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':range', $range, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy danh sách sinh nhật: " . $e->getMessage());
        } finally {
            $this->disconnect();
        }
    }

    public function send($customerId, $content, $sender, $method = 'system', $notes = '') {
        try {
            $conn = $this->connect();
            $conn->beginTransaction();
            
            // Validate customer exists
            $stmt = $conn->prepare("SELECT Email, DienThoai FROM khachhang WHERE MaKhachHang = :id");
            $stmt->bindParam(':id', $customerId);
            $stmt->execute();
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$customer) {
                throw new Exception("Không tìm thấy khách hàng");
            }

            $status = 1; // 1 for success
            $errorMessage = '';
            
            // Send wish based on method
            try {
                switch ($method) {
                    case 'email':
                        if (empty($customer['Email'])) {
                            throw new Exception("Khách hàng không có email");
                        }
                        // Add email sending logic here (e.g., PHPMailer)
                        break;
                        
                    case 'sms':
                        if (empty($customer['DienThoai'])) {
                            throw new Exception("Khách hàng không có số điện thoại");
                        }
                        // Add SMS sending logic here
                        break;
                        
                    case 'system':
                        // System notification is always successful
                        break;
                        
                    default:
                        throw new Exception("Phương thức gửi không hợp lệ");
                }
            } catch (Exception $e) {
                $status = 0; // 0 for failed
                $errorMessage = $e->getMessage();
            }

            // Save to loichucsinhnhat
            $sql = "INSERT INTO loichucsinhnhat (MaKhachHang, NoiDung, ThoiGianGui, TrangThai) 
                    VALUES (:customerId, :content, NOW(), :status)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
            // Include sender and method in content if needed
            $finalContent = $content;
            if ($notes || $errorMessage) {
                $finalContent .= "\n\nGhi chú: " . ($notes ?: '') . ($errorMessage ? " Lỗi: $errorMessage" : '');
            }
            if ($sender !== 'System') {
                $finalContent .= "\nNgười gửi: $sender";
            }
            if ($method !== 'system') {
                $finalContent .= "\nPhương thức: $method";
            }
            $stmt->bindParam(':content', $finalContent);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Không thể lưu lời chúc");
            }

            $conn->commit();
            
            // Set success message in session
            session_start();
            $_SESSION['success_message'] = "Lời chúc sinh nhật đã được gửi và lưu thành công!";
            
            return $status === 1;
            
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            // Set error message in session
            session_start();
            $_SESSION['error_message'] = "Lỗi khi gửi lời chúc: " . $e->getMessage();
            throw $e;
        } finally {
            $this->disconnect();
        }
    }

    public function getHistory($customerId = null, $limit = null) {
        try {
            $conn = $this->connect();
            
            $sql = "SELECT l.MaLoiChuc, l.MaKhachHang, l.NoiDung, l.ThoiGianGui AS NgayGui,
                           CASE WHEN l.TrangThai = 1 THEN 'success' ELSE 'failed' END AS TrangThai,
                           k.TenKhachHang, k.Email, k.DienThoai,
                           'System' AS NguoiGui, 'system' AS PhuongThucGui
                    FROM loichucsinhnhat l
                    JOIN khachhang k ON l.MaKhachHang = k.MaKhachHang
                    WHERE 1=1";
            
            $params = [];
            
            if ($customerId) {
                $sql .= " AND l.MaKhachHang = :customerId";
                $params[':customerId'] = $customerId;
            }
            
            $sql .= " ORDER BY l.ThoiGianGui DESC";
            
            if ($limit) {
                $sql .= " LIMIT :limit";
                $params[':limit'] = $limit;
            }
            
            $stmt = $conn->prepare($sql);
            foreach ($params as $key => $value) {
                if ($key === ':limit') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy lịch sử lời chúc: " . $e->getMessage());
        } finally {
            $this->disconnect();
        }
    }

    public function getRecentWishes($limit = 5) {
        return $this->getHistory(null, $limit);
    }

    public function getCustomerWishes($customerId) {
        if (!$customerId) {
            throw new Exception("Mã khách hàng không hợp lệ");
        }
        return $this->getHistory($customerId);
    }
}
?>