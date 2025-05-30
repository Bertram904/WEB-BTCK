<?php
require_once 'Database.php';

class Customer {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAll($filters = []) {
        try {
            $conn = $this->db->connect();
            
            // Base query with customer info and order statistics
            $sql = "SELECT 
                    k.*,
                    COUNT(DISTINCT o.MaDonHang) as SoLuongMua,
                    COALESCE(SUM(o.TongTien), 0) as TongChiTieu,
                    MAX(o.NgayMua) as LanMuaCuoi
                   FROM KhachHang k 
                   LEFT JOIN Orders o ON k.MaKhachHang = o.MaKhachHang";
            
            $whereConditions = [];
            $params = [];
            
            // Filter by birth month
            if (!empty($filters['month_birthday'])) {
                $whereConditions[] = "MONTH(k.NgaySinh) = :month_birthday";
                $params[':month_birthday'] = $filters['month_birthday'];
            }
            
            // Filter by minimum purchases
            if (!empty($filters['min_purchases'])) {
                $havingConditions[] = "SoLuongMua >= :min_purchases";
                $params[':min_purchases'] = $filters['min_purchases'];
            }
            
            // Filter by age range
            if (!empty($filters['age_min'])) {
                $whereConditions[] = "TIMESTAMPDIFF(YEAR, k.NgaySinh, CURDATE()) >= :age_min";
                $params[':age_min'] = $filters['age_min'];
            }
            
            if (!empty($filters['age_max'])) {
                $whereConditions[] = "TIMESTAMPDIFF(YEAR, k.NgaySinh, CURDATE()) <= :age_max";
                $params[':age_max'] = $filters['age_max'];
            }

            // Add status filter if provided
            if (!empty($filters['status'])) {
                $whereConditions[] = "k.TrangThai = :status";
                $params[':status'] = $filters['status'];
            }
            
            // Add search filter if provided
            if (!empty($filters['search'])) {
                $whereConditions[] = "(k.TenKhachHang LIKE :search OR k.Email LIKE :search OR k.DienThoai LIKE :search)";
                $params[':search'] = "%{$filters['search']}%";
            }
            
            // Combine WHERE conditions
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(" AND ", $whereConditions);
            }
            
            // Group by customer
            $sql .= " GROUP BY k.MaKhachHang";
            
            // Add HAVING conditions for aggregated filters
            if (!empty($havingConditions)) {
                $sql .= " HAVING " . implode(" AND ", $havingConditions);
            }
            
            // Add sorting
            if (!empty($filters['sort_by'])) {
                $sortField = $filters['sort_by'];
                $sortDirection = !empty($filters['sort_dir']) ? $filters['sort_dir'] : 'ASC';
                $validFields = ['TenKhachHang', 'Email', 'SoLuongMua', 'TongChiTieu', 'LanMuaCuoi'];
                $validDirections = ['ASC', 'DESC'];
                
                if (in_array($sortField, $validFields) && in_array(strtoupper($sortDirection), $validDirections)) {
                    $sql .= " ORDER BY $sortField $sortDirection";
                }
            } else {
                // Default sorting by name
                $sql .= " ORDER BY k.TenKhachHang ASC";
            }
            
            $stmt = $conn->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy danh sách khách hàng: " . $e->getMessage());
        }
    }

    public function getById($id) {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT 
                    k.*,
                    COUNT(DISTINCT o.MaDonHang) as SoLuongMua,
                    COALESCE(SUM(o.TongTien), 0) as TongChiTieu,
                    MAX(o.NgayMua) as LanMuaCuoi
                   FROM KhachHang k 
                   LEFT JOIN Orders o ON k.MaKhachHang = o.MaKhachHang
                   WHERE k.MaKhachHang = :id
                   GROUP BY k.MaKhachHang";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy thông tin khách hàng: " . $e->getMessage());
        }
    }

    public function getByBirthdayRange($start, $end) {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT 
                    k.*,
                    COUNT(DISTINCT o.MaDonHang) as SoLuongMua,
                    COALESCE(SUM(o.TongTien), 0) as TongChiTieu
                   FROM KhachHang k 
                   LEFT JOIN Orders o ON k.MaKhachHang = o.MaKhachHang
                   WHERE DATE_FORMAT(k.NgaySinh, '%m-%d') BETWEEN :start AND :end
                   GROUP BY k.MaKhachHang
                   ORDER BY DATE_FORMAT(k.NgaySinh, '%m-%d')";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':start', $start, PDO::PARAM_STR);
            $stmt->bindParam(':end', $end, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy khách hàng theo ngày sinh: " . $e->getMessage());
        }
    }

    public function add($name, $phone, $email, $address, $birthday, $status = 'active') {
        try {
            if (empty($name) || empty($email)) {
                throw new Exception("Tên và email không được để trống");
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email không hợp lệ");
            }

            $conn = $this->db->connect();
            
            // Check for duplicate email
            $stmt = $conn->prepare("SELECT COUNT(*) FROM KhachHang WHERE Email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Email đã tồn tại trong hệ thống");
            }

            $stmt = $conn->prepare("INSERT INTO KhachHang (TenKhachHang, DienThoai, Email, DiaChi, NgaySinh, TrangThai) 
                                  VALUES (:name, :phone, :email, :address, :birthday, :status)");
            
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':birthday', $birthday, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            
            $result = $stmt->execute();
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi thêm khách hàng: " . $e->getMessage());
        }
    }

    public function update($id, $name, $phone, $email, $address, $birthday, $status) {
        try {
            if (empty($name) || empty($email)) {
                throw new Exception("Tên và email không được để trống");
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email không hợp lệ");
            }

            $conn = $this->db->connect();
            
            // Check for duplicate email, excluding current customer
            $stmt = $conn->prepare("SELECT COUNT(*) FROM KhachHang WHERE Email = :email AND MaKhachHang != :id");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Email đã tồn tại trong hệ thống");
            }

            $stmt = $conn->prepare("UPDATE KhachHang 
                                  SET TenKhachHang = :name, 
                                      DienThoai = :phone, 
                                      Email = :email, 
                                      DiaChi = :address, 
                                      NgaySinh = :birthday, 
                                      TrangThai = :status 
                                  WHERE MaKhachHang = :id");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':birthday', $birthday, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            
            $result = $stmt->execute();
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi cập nhật khách hàng: " . $e->getMessage());
        }
    }

    

    public function delete($id) {
        try {
            $conn = $this->db->connect();
            
            // Begin transaction
            $conn->beginTransaction();
            
            try {
                // Check if customer exists
                $checkSql = "SELECT MaKhachHang, TenKhachHang FROM KhachHang WHERE MaKhachHang = :id";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
                $checkStmt->execute();
                $customer = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$customer) {
                    throw new Exception("Không tìm thấy khách hàng với ID: " . $id);
                }

                // Delete from all related tables in correct order to respect foreign key constraints
                $relatedTables = [
                    'OrderDetails',  // Delete order details first
                    'Orders',          // Then delete orders
                    'TinNhan',         // Delete messages
                    'LoiChucSinhNhat', // Delete birthday wishes
                    'KhachHang'        // Finally delete customer
                ];

                foreach ($relatedTables as $table) {
                    // For OrderDetails, we need to delete via Orders
                    if ($table === 'OrderDetails') {
                        $sql = "DELETE ct FROM OrderDetails ct 
                               INNER JOIN Orders o ON ct.MaDonHang = o.MaDonHang 
                               WHERE o.MaKhachHang = :id";
                    } else {
                        $sql = "DELETE FROM $table WHERE " . ($table === 'KhachHang' ? 'MaKhachHang' : 'MaKhachHang') . " = :id";
                    }
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                }
                
                // Commit transaction
                $conn->commit();
                $this->db->close();
                return [
                    'success' => true,
                    'message' => "Đã xóa thành công khách hàng: " . $customer['TenKhachHang']
                ];
                
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollBack();
                throw new Exception("Không thể xóa khách hàng. Chi tiết lỗi: " . $e->getMessage());
            }
        } catch (PDOException $e) {
            // Check for foreign key constraint violation
            if ($e->getCode() == 23000) {
                throw new Exception("Không thể xóa khách hàng vì còn dữ liệu liên quan. Vui lòng xóa các dữ liệu liên quan trước.");
            }
            throw new Exception("Lỗi xóa khách hàng: " . $e->getMessage());
        }
    }

    public function getTopCustomers($timeRange = 30) {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT 
                    k.*,
                    COUNT(DISTINCT o.MaDonHang) as SoLuongMua,
                    COALESCE(SUM(o.TongTien), 0) as TongChiTieu,
                    MAX(o.NgayMua) as LanMuaCuoi
                   FROM KhachHang k
                   LEFT JOIN Orders o ON k.MaKhachHang = o.MaKhachHang
                   WHERE o.NgayMua >= DATE_SUB(CURDATE(), INTERVAL :range DAY)
                   GROUP BY k.MaKhachHang
                   HAVING SoLuongMua > 0
                   ORDER BY TongChiTieu DESC
                   LIMIT 10";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':range', $timeRange, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy top khách hàng: " . $e->getMessage());
        }
    }

    public function getPurchaseTrends($timeRange = 30) {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT 
                    DATE(o.NgayMua) as date,
                    COUNT(DISTINCT o.MaDonHang) as orders
                   FROM Orders o
                   WHERE o.NgayMua >= DATE_SUB(CURDATE(), INTERVAL :range DAY)
                   GROUP BY DATE(o.NgayMua)
                   ORDER BY date ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':range', $timeRange, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy xu hướng mua hàng: " . $e->getMessage());
        }
    }

    public function getAgeDistribution() {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT 
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, NgaySinh, CURDATE()) < 18 THEN 'Dưới 18'
                        WHEN TIMESTAMPDIFF(YEAR, NgaySinh, CURDATE()) BETWEEN 18 AND 24 THEN '18-24'
                        WHEN TIMESTAMPDIFF(YEAR, NgaySinh, CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
                        WHEN TIMESTAMPDIFF(YEAR, NgaySinh, CURDATE()) BETWEEN 35 AND 44 THEN '35-44'
                        WHEN TIMESTAMPDIFF(YEAR, NgaySinh, CURDATE()) BETWEEN 45 AND 54 THEN '45-54'
                        ELSE 'Trên 54'
                    END as age_group,
                    COUNT(*) as count
                   FROM KhachHang
                   WHERE NgaySinh IS NOT NULL
                   GROUP BY age_group
                   ORDER BY FIELD(age_group, 'Dưới 18', '18-24', '25-34', '35-44', '45-54', 'Trên 54')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy phân bố độ tuổi: " . $e->getMessage());
        }
    }

    public function getMonthlyRevenue() {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT 
                    DATE_FORMAT(o.NgayMua, '%m/%Y') as month,
                    COALESCE(SUM(o.TongTien), 0) as revenue
                   FROM Orders o
                   WHERE o.NgayMua >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                   GROUP BY month
                   ORDER BY o.NgayMua ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy doanh thu theo tháng: " . $e->getMessage());
        }
    }

    public function getRetentionRate($timeRange = 30) {
        try {
            $conn = $this->db->connect();
            
            // Get customers who made purchases in both periods
            $sql = "SELECT 
                    (COUNT(DISTINCT CASE 
                        WHEN o.NgayMua BETWEEN 
                            DATE_SUB(CURDATE(), INTERVAL :range DAY) 
                            AND CURDATE() 
                        THEN k.MaKhachHang 
                    END) * 100.0 / 
                    NULLIF(COUNT(DISTINCT CASE 
                        WHEN o.NgayMua BETWEEN 
                            DATE_SUB(DATE_SUB(CURDATE(), INTERVAL :range DAY), INTERVAL :range DAY)
                            AND DATE_SUB(CURDATE(), INTERVAL :range DAY)
                        THEN k.MaKhachHang
                    END), 0)) as retention_rate
                   FROM KhachHang k
                   JOIN Orders o ON k.MaKhachHang = o.MaKhachHang
                   WHERE o.NgayMua >= DATE_SUB(DATE_SUB(CURDATE(), INTERVAL :range DAY), INTERVAL :range DAY)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':range', $timeRange, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            $this->db->close();
            return $result ?: 0;
        } catch (PDOException $e) {
            throw new Exception("Lỗi tính tỷ lệ giữ chân: " . $e->getMessage());
        }
    }

    /**
     * Lấy thống kê khách hàng
     * @return array Mảng chứa các thống kê
     */
    public function getStats() {
        try {
            $conn = $this->db->connect();
            $stats = [
                'total' => 0,
                'vip' => 0,
                'loyal' => 0,
                'regular' => 0,
                'new' => 0,
                'active' => 0,
                'orders_last_30_days' => 0,
                'revenue_last_30_days' => 0
            ];

            // Tổng số khách hàng
            $sql = "SELECT COUNT(*) as total FROM KhachHang";
            $stmt = $conn->query($sql);
            $stats['total'] = $stmt->fetch()['total'];

            // Số khách hàng đang hoạt động
            $sql = "SELECT COUNT(*) as active FROM KhachHang WHERE TrangThai = 'active'";
            $stmt = $conn->query($sql);
            $stats['active'] = $stmt->fetch()['active'];

            // Thống kê đơn hàng và doanh thu 30 ngày gần đây
            $sql = "SELECT 
                    COUNT(DISTINCT MaDonHang) as orders_count,
                    COALESCE(SUM(TongTien), 0) as total_revenue
                    FROM Orders 
                    WHERE NgayMua >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
            $stmt = $conn->query($sql);
            $recentStats = $stmt->fetch();
            $stats['orders_last_30_days'] = $recentStats['orders_count'];
            $stats['revenue_last_30_days'] = $recentStats['total_revenue'];

            // Thống kê theo phân khúc khách hàng
            $sql = "SELECT 
                    k.MaKhachHang,
                    COALESCE(SUM(o.TongTien), 0) as TongChiTieu,
                    COUNT(o.MaDonHang) as SoLuongMua,
                    MONTH(k.NgayTao) as ThangTao,
                    YEAR(k.NgayTao) as NamTao
                FROM KhachHang k
                LEFT JOIN Orders o ON k.MaKhachHang = o.MaKhachHang
                GROUP BY k.MaKhachHang";
            
            $stmt = $conn->query($sql);
            $currentMonth = date('n');
            $currentYear = date('Y');

            while ($row = $stmt->fetch()) {
                $tongChiTieu = floatval($row['TongChiTieu']);
                $soLuongMua = intval($row['SoLuongMua']);
                
                // Phân loại khách hàng
                if ($tongChiTieu >= 10000000) {
                    $stats['vip']++;
                } elseif ($tongChiTieu >= 5000000) {
                    $stats['loyal']++;
                } elseif ($tongChiTieu >= 2000000) {
                    $stats['regular']++;
                }

                // Đếm khách hàng mới (trong tháng hiện tại)
                if ($row['ThangTao'] == $currentMonth && $row['NamTao'] == $currentYear) {
                    $stats['new']++;
                }
            }

            return $stats;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy thống kê: " . $e->getMessage());
        }
    }

    public function getMonthlyStats() {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT 
                    DATE_FORMAT(NgayTao, '%Y-%m') as month,
                    COUNT(*) as count
                    FROM KhachHang
                    GROUP BY DATE_FORMAT(NgayTao, '%Y-%m')
                    ORDER BY month ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi truy vấn thống kê theo tháng: " . $e->getMessage());
        }
    }

    public function getAgeGroups() {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT 
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, NgaySinh, CURDATE()) < 18 THEN 'Dưới 18'
                        WHEN TIMESTAMPDIFF(YEAR, NgaySinh, CURDATE()) BETWEEN 18 AND 24 THEN '18-24'
                        WHEN TIMESTAMPDIFF(YEAR, NgaySinh, CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
                        WHEN TIMESTAMPDIFF(YEAR, NgaySinh, CURDATE()) BETWEEN 35 AND 44 THEN '35-44'
                        WHEN TIMESTAMPDIFF(YEAR, NgaySinh, CURDATE()) BETWEEN 45 AND 54 THEN '45-54'
                        ELSE 'Trên 54'
                    END as age_range,
                    COUNT(*) as count
                    FROM KhachHang
                    WHERE NgaySinh IS NOT NULL
                    GROUP BY age_range
                    ORDER BY MIN(TIMESTAMPDIFF(YEAR, NgaySinh, CURDATE()))";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi truy vấn thống kê độ tuổi: " . $e->getMessage());
        }
    }

    /**
     * Lấy xu hướng doanh thu theo thời gian
     * @param string $timeRange Khoảng thời gian (7, 30, 90, 365 ngày)
     * @return array Mảng dữ liệu xu hướng doanh thu
     */
    public function getRevenueTrends($timeRange = '30') {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT 
                        DATE(o.NgayMua) as ngay,
                        COUNT(DISTINCT o.MaDonHang) as so_don_hang,
                        SUM(o.TongTien) as doanh_thu,
                        COUNT(DISTINCT o.MaKhachHang) as so_khach_hang
                    FROM Orders o
                    WHERE o.NgayMua >= DATE_SUB(CURRENT_DATE, INTERVAL :time_range DAY)
                    GROUP BY DATE(o.NgayMua)
                    ORDER BY ngay ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':time_range', $timeRange, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Điền dữ liệu cho các ngày không có đơn hàng
            $trends = [];
            $startDate = new DateTime(date('Y-m-d', strtotime("-{$timeRange} days")));
            $endDate = new DateTime(date('Y-m-d'));
            $interval = new DateInterval('P1D');
            $dateRange = new DatePeriod($startDate, $interval, $endDate);
            
            foreach ($dateRange as $date) {
                $dateStr = $date->format('Y-m-d');
                $found = false;
                
                foreach ($result as $row) {
                    if ($row['ngay'] === $dateStr) {
                        $trends[] = [
                            'date' => $dateStr,
                            'orders' => (int)$row['so_don_hang'],
                            'revenue' => (float)$row['doanh_thu'],
                            'customers' => (int)$row['so_khach_hang']
                        ];
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $trends[] = [
                        'date' => $dateStr,
                        'orders' => 0,
                        'revenue' => 0,
                        'customers' => 0
                    ];
                }
            }
            
            $this->db->close();
            return $trends;
            
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy xu hướng doanh thu: " . $e->getMessage());
        }
    }

    /**
     * Lấy thống kê tần suất mua hàng của khách
     * @return array Mảng thống kê tần suất mua hàng
     */
    public function getPurchaseFrequency() {
        try {
            $conn = $this->db->connect();
            
            // Tính khoảng cách trung bình giữa các lần mua hàng
            $sql = "WITH CustomerPurchases AS (
                        SELECT 
                            o.MaKhachHang,
                            o.NgayMua,
                            LAG(o.NgayMua) OVER (PARTITION BY o.MaKhachHang ORDER BY o.NgayMua) as prev_purchase,
                            COUNT(*) OVER (PARTITION BY o.MaKhachHang) as total_purchases
                        FROM Orders o
                    ),
                    CustomerGaps AS (
                        SELECT 
                            MaKhachHang,
                            AVG(DATEDIFF(NgayMua, prev_purchase)) as avg_gap_days,
                            MAX(total_purchases) as total_purchases
                        FROM CustomerPurchases
                        WHERE prev_purchase IS NOT NULL
                        GROUP BY MaKhachHang
                    ),
                    FrequencyGroups AS (
                        SELECT 
                            MaKhachHang,
                            CASE 
                                WHEN avg_gap_days <= 7 THEN 'Thường xuyên (≤ 7 ngày)'
                                WHEN avg_gap_days <= 30 THEN 'Đều đặn (8-30 ngày)'
                                WHEN avg_gap_days <= 90 THEN 'Thỉnh thoảng (31-90 ngày)'
                                ELSE 'Hiếm khi (> 90 ngày)'
                            END as frequency_group,
                            avg_gap_days,
                            total_purchases
                        FROM CustomerGaps
                    )
                    SELECT 
                        frequency_group,
                        COUNT(*) as customer_count,
                        ROUND(AVG(avg_gap_days)) as avg_gap,
                        ROUND(AVG(total_purchases)) as avg_purchases
                    FROM FrequencyGroups
                    GROUP BY frequency_group
                    ORDER BY 
                        CASE frequency_group
                            WHEN 'Thường xuyên (≤ 7 ngày)' THEN 1
                            WHEN 'Đều đặn (8-30 ngày)' THEN 2
                            WHEN 'Thỉnh thoảng (31-90 ngày)' THEN 3
                            ELSE 4
                        END";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $frequencyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Thêm thống kê khách hàng mới (chỉ mua 1 lần)
            $sqlNewCustomers = "SELECT 
                                COUNT(DISTINCT o.MaKhachHang) as customer_count,
                                1 as avg_purchases,
                                0 as avg_gap
                              FROM Orders o
                              GROUP BY o.MaKhachHang
                              HAVING COUNT(*) = 1";
            
            $stmt = $conn->prepare($sqlNewCustomers);
            $stmt->execute();
            $newCustomerStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Thêm vào đầu kết quả
            if ($newCustomerStats) {
                array_unshift($frequencyStats, [
                    'frequency_group' => 'Mới (1 lần mua)',
                    'customer_count' => (int)$newCustomerStats['customer_count'],
                    'avg_gap' => 0,
                    'avg_purchases' => 1
                ]);
            }
            
            $this->db->close();
            return $frequencyStats;
            
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy thống kê tần suất mua hàng: " . $e->getMessage());
        }
    }

    /**
     * Lấy thống kê chi tiêu trung bình của khách hàng
     * @return array Mảng thống kê chi tiêu
     */
    public function getCustomerSpendingStats() {
        try {
            $conn = $this->db->connect();
            $sql = "WITH CustomerStats AS (
                        SELECT 
                            o.MaKhachHang,
                            COUNT(DISTINCT o.MaDonHang) as total_orders,
                            SUM(o.TongTien) as total_spent,
                            SUM(o.TongTien) / COUNT(DISTINCT o.MaDonHang) as avg_order_value,
                            DATEDIFF(MAX(o.NgayMua), MIN(o.NgayMua)) as customer_lifetime_days
                        FROM Orders o
                        GROUP BY o.MaKhachHang
                    )
                    SELECT 
                        CASE 
                            WHEN total_spent >= 10000000 THEN 'VIP (≥ 10M)'
                            WHEN total_spent >= 5000000 THEN 'Cao cấp (5M-10M)'
                            WHEN total_spent >= 2000000 THEN 'Trung bình (2M-5M)'
                            ELSE 'Cơ bản (< 2M)'
                        END as spending_group,
                        COUNT(*) as customer_count,
                        ROUND(AVG(total_orders)) as avg_orders,
                        ROUND(AVG(total_spent)) as avg_total_spent,
                        ROUND(AVG(avg_order_value)) as avg_order_value,
                        ROUND(AVG(customer_lifetime_days)) as avg_lifetime_days
                    FROM CustomerStats
                    GROUP BY 
                        CASE 
                            WHEN total_spent >= 10000000 THEN 'VIP (≥ 10M)'
                            WHEN total_spent >= 5000000 THEN 'Cao cấp (5M-10M)'
                            WHEN total_spent >= 2000000 THEN 'Trung bình (2M-5M)'
                            ELSE 'Cơ bản (< 2M)'
                        END
                    ORDER BY avg_total_spent DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->db->close();
            return $result;
            
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy thống kê chi tiêu khách hàng: " . $e->getMessage());
        }
    }

    public function getPotentialCustomers($limit = 3) {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT 
                    k.*,
                    COUNT(DISTINCT o.MaDonHang) as SoLuongMua,
                    COALESCE(SUM(o.TongTien), 0) as TongChiTieu,
                    MAX(o.NgayMua) as LanMuaCuoi,
                    DATEDIFF(CURDATE(), MAX(o.NgayMua)) as NgayTuLanCuoi,
                    (
                        -- Tính điểm tiềm năng dựa trên:
                        -- 1. Tổng chi tiêu (40%)
                        (COALESCE(SUM(o.TongTien), 0) / 1000000 * 40) +
                        -- 2. Tần suất mua hàng (30%)
                        (COUNT(DISTINCT o.MaDonHang) * 30) +
                        -- 3. Độ gần đây của lần mua cuối (30%)
                        (CASE 
                            WHEN MAX(o.NgayMua) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 30
                            WHEN MAX(o.NgayMua) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 20
                            WHEN MAX(o.NgayMua) >= DATE_SUB(CURDATE(), INTERVAL 180 DAY) THEN 10
                            ELSE 0
                        END)
                    ) as DiemTiemNang
                   FROM KhachHang k
                   LEFT JOIN Orders o ON k.MaKhachHang = o.MaKhachHang
                   WHERE k.TrangThai = 'active'
                   GROUP BY k.MaKhachHang
                   HAVING SoLuongMua > 0
                   ORDER BY DiemTiemNang DESC, TongChiTieu DESC
                   LIMIT :limit";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy khách hàng tiềm năng: " . $e->getMessage());
        }
    }

    /**
     * Kiểm tra dữ liệu liên quan của khách hàng
     * @param int $id ID của khách hàng
     * @return array Mảng chứa thông tin về dữ liệu liên quan
     */
    public function checkRelatedData($id) {
        try {
            $conn = $this->db->connect();
            $result = [];

            // Kiểm tra đơn hàng
            $sql = "SELECT COUNT(*) as count FROM Orders WHERE MaKhachHang = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result['orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Kiểm tra chi tiết đơn hàng
            $sql = "SELECT COUNT(*) as count FROM OrderDetails ct 
                   INNER JOIN Orders o ON ct.MaDonHang = o.MaDonHang 
                   WHERE o.MaKhachHang = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result['order_details'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Kiểm tra tin nhắn
            $sql = "SELECT COUNT(*) as count FROM TinNhan WHERE MaKhachHang = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result['messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Kiểm tra lời chúc sinh nhật
            $sql = "SELECT COUNT(*) as count FROM LoiChucSinhNhat WHERE MaKhachHang = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result['birthday_wishes'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Lấy thông tin khách hàng
            $sql = "SELECT * FROM KhachHang WHERE MaKhachHang = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result['customer'] = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi kiểm tra dữ liệu liên quan: " . $e->getMessage());
        }
    }

    /**
     * Xóa tất cả dữ liệu liên quan của khách hàng
     * @param int $id ID của khách hàng
     * @return array Kết quả xóa
     */
    public function deleteWithRelated($id) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            try {
                // Kiểm tra tồn tại của khách hàng
                $customer = $this->checkRelatedData($id);
                if (!$customer['customer']) {
                    throw new Exception("Không tìm thấy khách hàng với ID: " . $id);
                }

                // Xóa chi tiết đơn hàng trước
                $sql = "DELETE ct FROM OrderDetails ct 
                       INNER JOIN Orders o ON ct.MaDonHang = o.MaDonHang 
                       WHERE o.MaKhachHang = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                // Xóa đơn hàng
                $sql = "DELETE FROM Orders WHERE MaKhachHang = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                // Xóa tin nhắn
                $sql = "DELETE FROM TinNhan WHERE MaKhachHang = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                // Xóa lời chúc sinh nhật
                $sql = "DELETE FROM LoiChucSinhNhat WHERE MaKhachHang = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                // Cuối cùng xóa khách hàng
                $sql = "DELETE FROM KhachHang WHERE MaKhachHang = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                $conn->commit();
                return [
                    'success' => true,
                    'message' => "Đã xóa thành công khách hàng: " . $customer['customer']['TenKhachHang'],
                    'deleted_data' => [
                        'orders' => $customer['orders'],
                        'order_details' => $customer['order_details'],
                        'messages' => $customer['messages'],
                        'birthday_wishes' => $customer['birthday_wishes']
                    ]
                ];
            } catch (Exception $e) {
                $conn->rollBack();
                throw new Exception("Lỗi khi xóa dữ liệu: " . $e->getMessage());
            }
        } catch (PDOException $e) {
            throw new Exception("Lỗi kết nối database: " . $e->getMessage());
        }
    }

    public function getAllPurchases($filters = []) {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT 
                    o.MaDonHang,
                    o.MaKhachHang,
                    o.NgayMua,
                    o.TongTien,
                    o.PhanTramGiamGia,
                    o.TrangThaiThanhToan,
                    kh.TenKhachHang, 
                    kh.Email 
                    FROM Orders o 
                    JOIN KhachHang kh ON o.MaKhachHang = kh.MaKhachHang 
                    WHERE 1=1";
            $params = [];

            // Áp dụng các bộ lọc
            if (!empty($filters['customer_id'])) {
                $sql .= " AND o.MaKhachHang = :customer_id";
                $params[':customer_id'] = $filters['customer_id'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(o.NgayMua) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(o.NgayMua) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            if (!empty($filters['min_amount'])) {
                $sql .= " AND o.TongTien >= :min_amount";
                $params[':min_amount'] = $filters['min_amount'];
            }

            // Thêm bộ lọc cho trạng thái thanh toán nếu có
            if (isset($filters['payment_status']) && $filters['payment_status'] !== '') {
                $sql .= " AND o.TrangThaiThanhToan = :payment_status";
                $params[':payment_status'] = $filters['payment_status'];
            }

            $sql .= " ORDER BY o.NgayMua DESC";

            $stmt = $conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi khi lấy danh sách đơn hàng: " . $e->getMessage());
        }
    }

    public function getAllCustomers() {
        try {
            $conn = $this->db->connect();
            $sql = "SELECT MaKhachHang, TenKhachHang FROM KhachHang ORDER BY TenKhachHang";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->close();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Lỗi khi lấy danh sách khách hàng: " . $e->getMessage());
        }
    }

    /**
     * Tính toán phần trăm giảm giá dựa trên phân khúc khách hàng
     * @param array $customerStats Thống kê của khách hàng
     * @return array Thông tin giảm giá và phân khúc
     */
    private function calculateCustomerDiscount($customerStats) {
        $totalSpent = floatval($customerStats['TongChiTieu'] ?? 0);
        $purchaseCount = intval($customerStats['SoLuongMua'] ?? 0);
        
        // Phân khúc khách hàng dựa trên chi tiêu
        if ($totalSpent >= 10000000) { // VIP (>= 10M)
            return [
                'segment' => 'VIP',
                'discount' => 15.0,
                'auto_payment' => true
            ];
        } elseif ($totalSpent >= 5000000) { // Khách hàng thân thiết (5M-10M)
            return [
                'segment' => 'Thân thiết',
                'discount' => 10.0,
                'auto_payment' => true
            ];
        } elseif ($totalSpent >= 2000000) { // Khách hàng thường xuyên (2M-5M)
            return [
                'segment' => 'Thường xuyên',
                'discount' => 5.0,
                'auto_payment' => false
            ];
        } else { // Khách hàng mới/cơ bản (< 2M)
            // Tính giảm giá dựa trên số lượt mua
            if ($purchaseCount >= 5) {
                return [
                    'segment' => 'Tiềm năng',
                    'discount' => 3.0,
                    'auto_payment' => false
                ];
            }
            return [
                'segment' => 'Mới',
                'discount' => 0.0,
                'auto_payment' => false
            ];
        }
    }

    /**
     * Cập nhật giảm giá và trạng thái thanh toán cho đơn hàng
     */
    public function updateOrderDiscounts() {
        try {
            $conn = $this->db->connect();
            
            // Lấy thống kê của tất cả khách hàng
            $sql = "SELECT 
                    k.MaKhachHang,
                    COUNT(DISTINCT o.MaDonHang) as SoLuongMua,
                    COALESCE(SUM(o.TongTien), 0) as TongChiTieu
                    FROM KhachHang k
                    LEFT JOIN Orders o ON k.MaKhachHang = o.MaKhachHang
                    GROUP BY k.MaKhachHang";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $customerStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Cập nhật giảm giá và trạng thái thanh toán cho từng khách hàng
            foreach ($customerStats as $stats) {
                $discount = $this->calculateCustomerDiscount($stats);
                
                $updateSql = "UPDATE Orders 
                             SET PhanTramGiamGia = :discount,
                                 TrangThaiThanhToan = :payment_status
                             WHERE MaKhachHang = :customer_id";
                
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bindValue(':discount', $discount['discount'], PDO::PARAM_STR);
                $updateStmt->bindValue(':payment_status', $discount['auto_payment'] ? 1 : 0, PDO::PARAM_INT);
                $updateStmt->bindValue(':customer_id', $stats['MaKhachHang'], PDO::PARAM_INT);
                $updateStmt->execute();
            }
            
            $this->db->close();
            return true;
        } catch (PDOException $e) {
            throw new Exception("Lỗi cập nhật giảm giá: " . $e->getMessage());
        }
    }

    /**
     * Đánh dấu tất cả đơn hàng là đã thanh toán
     */
    public function markAllOrdersAsPaid() {
        try {
            $conn = $this->db->connect();
            $sql = "UPDATE Orders SET TrangThaiThanhToan = 1 WHERE TrangThaiThanhToan = 0";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $affectedRows = $stmt->rowCount();
            $this->db->close();
            return $affectedRows;
        } catch (PDOException $e) {
            throw new Exception("Lỗi cập nhật trạng thái thanh toán: " . $e->getMessage());
        }
    }

    /**
     * Lấy thông tin phân khúc khách hàng
     * @param int $customerId ID của khách hàng (tùy chọn)
     * @return array Thông tin phân khúc khách hàng
     */
    public function getCustomerSegments($customerId = null) {
        try {
            $conn = $this->db->connect();
            
            // Base query to get customer stats
            $sql = "SELECT 
                    k.MaKhachHang,
                    k.TenKhachHang,
                    k.Email,
                    COUNT(DISTINCT o.MaDonHang) as SoLuongMua,
                    COALESCE(SUM(o.TongTien), 0) as TongChiTieu
                    FROM KhachHang k
                    LEFT JOIN Orders o ON k.MaKhachHang = o.MaKhachHang";
            
            // Add customer filter if ID is provided
            if ($customerId) {
                $sql .= " WHERE k.MaKhachHang = :customer_id";
            }
            
            $sql .= " GROUP BY k.MaKhachHang";
            
            $stmt = $conn->prepare($sql);
            if ($customerId) {
                $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            }
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate segment for each customer
            $results = [];
            foreach ($customers as $customer) {
                $segment = $this->calculateCustomerDiscount($customer);
                $results[] = array_merge($customer, [
                    'PhanKhuc' => $segment['segment'],
                    'PhanTramGiamGia' => $segment['discount'],
                    'TuDongThanhToan' => $segment['auto_payment'],
                    'TieuChiPhanKhuc' => $this->getSegmentCriteria($segment['segment'])
                ]);
            }
            
            $this->db->close();
            return $customerId ? ($results[0] ?? null) : $results;
        } catch (PDOException $e) {
            throw new Exception("Lỗi lấy thông tin phân khúc khách hàng: " . $e->getMessage());
        }
    }

    /**
     * Lấy tiêu chí của từng phân khúc khách hàng
     * @param string $segment Tên phân khúc
     * @return string Mô tả tiêu chí
     */
    private function getSegmentCriteria($segment) {
        switch ($segment) {
            case 'VIP':
                return 'Tổng chi tiêu ≥ 10M, Tự động thanh toán, Giảm giá 15%';
            case 'Thân thiết':
                return 'Tổng chi tiêu 5M-10M, Tự động thanh toán, Giảm giá 10%';
            case 'Thường xuyên':
                return 'Tổng chi tiêu 2M-5M, Thanh toán thủ công, Giảm giá 5%';
            case 'Tiềm năng':
                return 'Tổng chi tiêu < 2M, ≥ 5 lượt mua, Thanh toán thủ công, Giảm giá 3%';
            case 'Mới':
                return 'Tổng chi tiêu < 2M, < 5 lượt mua, Thanh toán thủ công, Không giảm giá';
            default:
                return 'Chưa phân loại';
        }
    }
}
?>