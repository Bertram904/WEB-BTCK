<?php
require_once 'Database.php';

class Order {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function add($customerId, $items) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // Thêm đơn hàng với tổng tiền tạm thời là 0
            $stmt = $conn->prepare("INSERT INTO Orders (MaKhachHang, TongTien, NgayMua) VALUES (:customer_id, 0, NOW())");
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            $orderId = $conn->lastInsertId();

            // Tính tổng tiền trước khi thêm chi tiết
            $total = 0;

            // Thêm chi tiết đơn hàng
            foreach ($items as $item) {
                // Chuyển đổi và làm sạch dữ liệu
                $quantity = intval($item['quantity']);
                $price = floatval(str_replace([',', '.'], '', $item['price']));
                
                if ($quantity <= 0 || $price <= 0) {
                    throw new Exception("Số lượng và đơn giá phải lớn hơn 0");
                }

                $stmt = $conn->prepare("INSERT INTO OrderDetails (MaDonHang, SanPham, SoLuong, DonGia) 
                                      VALUES (:order_id, :product, :quantity, :price)");
                $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
                $stmt->bindParam(':product', $item['product'], PDO::PARAM_STR);
                $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stmt->bindParam(':price', $price, PDO::PARAM_STR);
                $stmt->execute();

                // Cộng dồn vào tổng tiền
                $total += $quantity * $price;
            }

            // Cập nhật tổng tiền
            $stmt = $conn->prepare("UPDATE Orders SET TongTien = :total WHERE MaDonHang = :order_id");
            $stmt->bindParam(':total', $total, PDO::PARAM_STR);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();

            $conn->commit();
            $this->db->close();
            return true;
        } catch (PDOException $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            throw new Exception("Lỗi thêm đơn hàng: " . $e->getMessage());
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    public function getByCustomer($customerId) {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("SELECT o.MaDonHang, o.TongTien, o.NgayMua, GROUP_CONCAT(od.SanPham) as SanPham 
                                    FROM Orders o 
                                    LEFT JOIN OrderDetails od ON o.MaDonHang = od.MaDonHang 
                                    WHERE o.MaKhachHang = :customer_id 
                                    GROUP BY o.MaDonHang 
                                    ORDER BY o.NgayMua DESC");
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy lịch sử mua hàng: " . $e->getMessage());
        }
    }

    public function calculateOrderTotal($orderId) {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("SELECT COALESCE(SUM(CASE 
                                    WHEN SoLuong > 0 AND DonGia > 0 
                                    THEN SoLuong * DonGia 
                                    ELSE 0 
                                  END), 0) as TongTien 
                                  FROM OrderDetails 
                                  WHERE MaDonHang = :order_id");
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->db->close();
            return max(0, floatval($result['TongTien']));
        } catch (PDOException $e) {
            throw new Exception("Lỗi tính tổng tiền đơn hàng: " . $e->getMessage());
        }
    }

    public function updateOrderTotal($orderId) {
        try {
            $total = $this->calculateOrderTotal($orderId);
            
            $conn = $this->db->connect();
            $stmt = $conn->prepare("UPDATE Orders SET TongTien = :total WHERE MaDonHang = :order_id");
            $stmt->bindParam(':total', $total, PDO::PARAM_STR);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            $this->db->close();
            return true;
        } catch (PDOException $e) {
            throw new Exception("Lỗi cập nhật tổng tiền đơn hàng: " . $e->getMessage());
        }
    }
}
?>