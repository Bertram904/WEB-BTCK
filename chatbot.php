<?php
session_start();

// --- PROMPT CHO AI ---
//$INTENT_ANALYSIS_PROMPT = "\nBạn là một AI phân tích ý định người dùng cho hệ thống quản lý khách hàng Matcha Vibe. \nPhân tích câu hỏi của người dùng và trả về JSON với định dạng:\n\n{\n    \"intent\": \"tên_hành_động\",\n    \"entities\": {\n        \"id\": \"mã_khách_hàng_nếu_có\",\n        \"name\": \"tên_khách_hàng_nếu_có\",\n        \"phone\": \"số_điện_thoại_nếu_có\",\n        \"address\": \"địa_chỉ_nếu_có\",\n        \"birthday\": \"ngày_sinh_nếu_có_định_dạng_YYYY-MM-DD\",\n        \"keyword\": \"từ_khóa_tìm_kiếm_tên_khách_hàng\",\n        \"order_id\": \"mã_đơn_hàng_nếu_có\",\n        \"message\": \"tin_nhắn_nếu_có\"\n    },\n    \"confidence\": \"độ_tin_cậy_từ_0_đến_1\"\n}\n\nDanh sách các intent khả thi:\n- list_customers: liệt kê tất cả khách hàng\n- add_customer: thêm khách hàng mới\n- find_customer_by_id: tìm khách hàng theo ID\n- search_customers: tìm kiếm khách hàng theo tên\n- check_promotions: kiểm tra khuyến mãi theo ID\n- check_promotions_by_name: kiểm tra khuyến mãi theo tên\n- view_detailed_promotions: xem chi tiết khuyến mãi theo ID\n- view_detailed_promotions_by_name: xem chi tiết khuyến mãi theo tên\n- view_order_history: xem lịch sử đơn hàng theo ID\n- view_order_history_by_name: xem lịch sử đơn hàng theo tên\n- view_order_details: xem chi tiết đơn hàng theo mã đơn\n- view_order_details_by_name: xem chi tiết đơn hàng theo tên\n- edit_customer: chỉnh sửa thông tin khách hàng\n- copy_customer: sao chép thông tin khách hàng\n- delete_customer: xóa khách hàng\n- check_birthday_by_id: kiểm tra thông tin sinh nhật theo ID\n- check_birthday_by_name: kiểm tra thông tin sinh nhật theo tên\n- upcoming_birthdays: xem danh sách khách hàng có sinh nhật trong 30 ngày tới\n- general_question: câu hỏi chung hoặc không xác định được ý định\n\nHướng dẫn:\n- Chào hỏi người dùng một cách thân thiện và lịch sự.\n- Phân tích câu hỏi để xác định intent và entities cần thiết.\n- Xác định intent dựa trên nội dung câu hỏi, ưu tiên khớp chính xác với từ khóa hoặc ngữ cảnh.\n- Trích xuất entities chỉ khi thông tin rõ ràng trong câu hỏi (e.g., ID, tên, số điện thoại, địa chỉ, ngày sinh, mã đơn hàng).\n- Nếu câu hỏi chứa 'sinh nhật' và '30 ngày' hoặc 'sắp tới', gán intent là 'upcoming_birthdays' và không cần entities.\n- Nếu câu hỏi không khớp với intent cụ thể, gán intent là 'general_question' với confidence thấp.\n- Đảm bảo confidence phản ánh độ chắc chắn của phân tích, từ 0.0 đến 1.0 (e.g., 0.95 cho khớp chính xác, 0.6 cho câu hỏi mơ hồ).\n- Định dạng ngày sinh phải là YYYY-MM-DD nếu được trích xuất.\n\nVí dụ:\n- 'Liệt kê danh sách khách hàng' -> {\"intent\": \"list_customers\", \"entities\": {}, \"confidence\": 0.9}\n- 'Tìm khách hàng Nguyễn Văn A' -> {\"intent\": \"search_customers\", \"entities\": {\"keyword\": \"Nguyễn Văn A\"}, \"confidence\": 0.9}\n- 'Tìm khách hàng ID 1' -> {\"intent\": \"find_customer_by_id\", \"entities\": {\"id\": \"1\"}, \"confidence\": 0.95}\n- 'Sinh nhật của Nguyễn Văn A' -> {\"intent\": \"check_birthday_by_name\", \"entities\": {\"keyword\": \"Nguyễn Văn A\"}, \"confidence\": 0.85}\n- 'Chi tiết đơn hàng của Nguyễn Văn B' -> {\"intent\": \"view_order_details_by_name\", \"entities\": {\"keyword\": \"Nguyễn Văn B\"}, \"confidence\": 0.85}\n- 'Thêm khách hàng tên Nguyễn Văn A, số điện thoại 0912345678, địa chỉ Hà Nội, ngày sinh 1990-01-01' -> {\"intent\": \"add_customer\", \"entities\": {\"name\": \"Nguyễn Văn A\", \"phone\": \"0912345678\", \"address\": \"Hà Nội\", \"birthday\": \"1990-01-01\"}, \"confidence\": 0.95}\n- 'Sửa khách hàng ID 1 với tên Nguyễn Văn B, địa chỉ TP.HCM' -> {\"intent\": \"edit_customer\", \"entities\": {\"id\": \"1\", \"name\": \"Nguyễn Văn B\", \"address\": \"TP.HCM\"}, \"confidence\": 0.9}\n- 'Sao chép khách hàng ID 2' -> {\"intent\": \"copy_customer\", \"entities\": {\"id\": \"2\"}, \"confidence\": 0.95}\n- 'Xóa khách hàng ID 3' -> {\"intent\": \"delete_customer\", \"entities\": {\"id\": \"3\"}, \"confidence\": 0.95}\n- 'Kiểm tra sinh nhật ID 1' -> {\"intent\": \"check_birthday_by_id\", \"entities\": {\"id\": \"1\"}, \"confidence\": 0.95}\n- 'Xem chi tiết đơn hàng mã 123' -> {\"intent\": \"view_order_details\", \"entities\": {\"order_id\": \"123\"}, \"confidence\": 0.95}\n- 'Xem khuyến mãi của khách hàng ID 4' -> {\"intent\": \"check_promotions\", \"entities\": {\"id\": \"4\"}, \"confidence\": 0.95}\n- 'Xem chi tiết khuyến mãi của Nguyễn Văn A' -> {\"intent\": \"view_detailed_promotions_by_name\", \"entities\": {\"keyword\": \"Nguyễn Văn A\"}, \"confidence\": 0.85}\n- 'Xem lịch sử đơn hàng của Nguyễn Văn A' -> {\"intent\": \"view_order_history_by_name\", \"entities\": {\"keyword\": \"Nguyễn Văn A\"}, \"confidence\": 0.85}\n- 'Câu hỏi chung về dịch vụ' -> {\"intent\": \"general_question\", \"entities\": {}, \"confidence\": 0.7}\n- 'Sinh nhật trong 30 ngày tới' -> {\"intent\": \"upcoming_birthdays\", \"entities\": {}, \"confidence\": 0.9}\n- 'Khách nào sắp có sinh nhật?' -> {\"intent\": \"upcoming_birthdays\", \"entities\": {}, \"confidence\": 0.85}\n- 'Danh sách sinh nhật sắp tới' -> {\"intent\": \"upcoming_birthdays\", \"entities\": {}, \"confidence\": 0.9}\n\nPhân tích: \"{{user_message}}\"";

//$RESPONSE_GENERATION_PROMPT = "\nBạn là trợ lý quản lý khách hàng thân thiện, chuyên nghiệp của Matcha Vibe. \nDựa trên dữ liệu ngữ cảnh, trả lời câu hỏi của người dùng một cách tự nhiên, dễ hiểu bằng tiếng Việt:\n\nNgữ cảnh: {{context_text}}\n\nYêu cầu: {{user_message}}\n\nHướng dẫn:\n- Sử dụng ngôn ngữ thân thiện, lịch sự, phù hợp với thương hiệu Matcha Vibe.\n- Sử dụng bảng HTML để hiển thị danh sách khách hàng, kết quả tìm kiếm, lịch sử đơn hàng, chi tiết đơn hàng, chi tiết khuyến mãi, hoặc danh sách sinh nhật sắp tới.\n- Không sử dụng HTML cho các hành động như thêm, sửa, xóa, sao chép khách hàng, hoặc hiển thị thông tin sinh nhật của một khách hàng cụ thể.\n- Nếu tìm kiếm theo tên trả về nhiều khách hàng, liệt kê danh sách với ID và tên trong bảng HTML, yêu cầu người dùng cung cấp ID cụ thể.\n- Nếu thiếu thông tin (e.g., ID, tên, hoặc thông tin cần thiết để thêm/sửa khách hàng), hướng dẫn người dùng cung cấp chi tiết một cách thân thiện.\n- Chào khách hang bằng tên nếu có, hoặc sử dụng 'Quý khách' nếu không rõ tên.\n- Nếu intent là 'general_question', trả lời câu hỏi một cách tự nhiên, không cần hiển thị bảng HTML.\n- Tránh lặp lại thông tin không cần thiết, chỉ hiển thị dữ liệu liên quan đến yêu cầu.\n- Nếu intent là 'upcoming_birthdays', hiển thị danh sách khách hàng có sinh nhật trong 30 ngày tới trong bảng HTML, bao gồm ID, tên, và ngày sinh.\n- Học từ các phản hồi trước để cải thiện độ chính xác và tự nhiên trong câu trả lời.\n";

// Initialize variables
$messages = isset($_SESSION['messages']) ? $_SESSION['messages'] : [];
$is_loading = false;
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Include prompt and database configuration
require_once 'prompt.php';

// Database class
class Database {
    private $host = 'localhost';
    private $dbname = 'matchavibe';
    private $username = 'root';
    private $password = '';
    private $conn = null;

    public function connect() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
            }
        }
        return $this->conn;
    }

    public function listCustomers() {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT MaKhachHang, TenKhachHang, DienThoai, DiaChi, NgaySinh, NgayTao, TrangThai 
                                FROM khachhang 
                                ORDER BY MaKhachHang DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function searchCustomers($keyword) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT MaKhachHang, TenKhachHang, DienThoai, DiaChi, NgaySinh, NgayTao, TrangThai 
                                FROM khachhang 
                                WHERE LOWER(TenKhachHang) LIKE LOWER(:tenKhach) 
                                ORDER BY TenKhachHang ASC");
        $kw = '%' . $keyword . '%';
        $stmt->bindParam(':tenKhach', $kw);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findCustomerById($id) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT * FROM khachhang WHERE MaKhachHang = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function addCustomer($name, $email, $phone, $address, $birthday) {
        $conn = $this->connect();
        $stmt = $conn->prepare("INSERT INTO khachhang (TenKhachHang, Email, DienThoai, DiaChi, NgaySinh, NgayTao, TrangThai) 
                                VALUES (:name, :email, :phone, :address, :birthday, NOW(), 'active')");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':birthday', $birthday);
        return $stmt->execute();
    }

    public function updateCustomer($id, $name, $email, $phone, $address, $birthday) {
        $conn = $this->connect();
        $stmt = $conn->prepare("UPDATE khachhang 
                                SET TenKhachHang = :name, Email = :email, DienThoai = :phone, 
                                    DiaChi = :address, NgaySinh = :birthday 
                                WHERE MaKhachHang = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':birthday', $birthday);
        return $stmt->execute();
    }

    public function copyCustomer($id) {
        $customer = $this->findCustomerById($id);
        if (!$customer) {
            return false;
        }
        $new_email = $customer['Email'] . '_copy_' . time();
        return $this->addCustomer(
            $customer['TenKhachHang'] . ' (Sao chép)',
            $new_email,
            $customer['DienThoai'],
            $customer['DiaChi'],
            $customer['NgaySinh']
        );
    }

    public function deleteCustomer($id) {
        $conn = $this->connect();
        $stmt = $conn->prepare("DELETE FROM khachhang WHERE MaKhachHang = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function checkPromotions($id) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT LoaiKhuyenMai, GiaTri, MoTa, DonHangToiThieu, CapBac, NgayTao, NgayHetHan 
                                FROM khuyenmai 
                                WHERE MaKhachHang = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOrderHistory($id) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT MaDonHang, TongTien, NgayMua, PhanTramGiamGia, TrangThaiThanhToan 
                                FROM orders 
                                WHERE MaKhachHang = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCustomerBirthday($id) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT NgaySinh FROM khachhang WHERE MaKhachHang = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['NgaySinh'] : null;
    }

    public function findUpcomingBirthdays() {
        $conn = $this->connect();
        $stmt = $conn->prepare("
            SELECT MaKhachHang, TenKhachHang, NgaySinh 
            FROM khachhang 
            WHERE 
                (DAYOFYEAR(NgaySinh) BETWEEN DAYOFYEAR(CURDATE()) AND DAYOFYEAR(DATE_ADD(CURDATE(), INTERVAL 30 DAY))
                OR DAYOFYEAR(NgaySinh) + 365 BETWEEN DAYOFYEAR(CURDATE()) AND DAYOFYEAR(DATE_ADD(CURDATE(), INTERVAL 30 DAY)))
                AND TrangThai = 'active'
            ORDER BY MONTH(NgaySinh), DAY(NgaySinh)
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBirthdayWishes($id) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT NoiDung, ThoiGianGui, TrangThai 
                                FROM loichucsinhnhat 
                                WHERE MaKhachHang = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOrderDetails($order_id) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT SanPham, SoLuong, DonGia 
                                FROM orderdetails 
                                WHERE MaDonHang = :order_id");
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDetailedPromotions($id) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT MaKhuyenMai, LoaiKhuyenMai, GiaTri, MoTa, DonHangToiThieu, CapBac, NgayTao, NgayHetHan 
                                FROM khuyenmai 
                                WHERE MaKhachHang = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function saveChatbotSession($session_id, $ma_khach_hang, $request_count, $status, $final_response, $loi_api = null) {
        // Placeholder for session saving logic
    }
}

// Intent Analysis Class
class IntentAnalyzer {
    private $gemini_api_key;
    private $gemini_url;

    public function __construct($api_key, $api_url) {
        $this->gemini_api_key = $api_key;
        $this->gemini_url = $api_url;
    }

    public function analyzeIntent($user_message) {
        global $INTENT_ANALYSIS_PROMPT;
        $system_prompt = str_replace('{{user_message}}', $user_message, $INTENT_ANALYSIS_PROMPT);
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $system_prompt]
                    ]
                ]
            ]
        ];
        //gửi yêu cầu đến Gemini API
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $this->gemini_api_key;
        // thiết lập cURL để gửi yêu cầu POST
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        // gửi yêu cầu và nhận phản hồi HTTP
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // trích xuất ý định từ phản hồi
        if ($http_code === 200 && $response) {
            $result = json_decode($response, true);
            $text_response = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (preg_match('/\{.*\}/s', $text_response, $matches)) {
                $intent_data = json_decode($matches[0], true);
                if ($intent_data) {
                    return $intent_data;
                }
            }
        }
        return $this->fallbackIntentAnalysis($user_message);
    }

    private function fallbackIntentAnalysis($message) {
        $message = mb_strtolower(trim($message), 'UTF-8');
        
        // Prioritize upcoming birthdays intent
        if ((strpos($message, 'sinh nhật') !== false && (strpos($message, '30 ngày') !== false || strpos($message, 'sắp tới') !== false))
            || strpos($message, 'sinh nhật 30 ngày') !== false
            || strpos($message, 'sinh nhật sắp tới') !== false) {
            return [
                'intent' => 'upcoming_birthdays',
                'entities' => [],
                'confidence' => 0.95
            ];
        }
        if (in_array($message, ['hello', 'hi', 'xin chào', 'chào', 'chào bạn', 'chào bot', 'hi bot', 'hello bot'])) {
        return [
            'intent' => 'greeting',
            'entities' => [],
            'confidence' => 1.0
        ];
    }

        if (strpos($message, 'liệt kê') !== false || strpos($message, 'danh sách') !== false) {
            return ['intent' => 'list_customers', 'entities' => [], 'confidence' => 0.8];
        }
        
        if (strpos($message, 'thêm') !== false || strpos($message, 'tạo') !== false) {
            $entities = [];
            if (preg_match('/tên\s+([^\s].*?)(?:\s+email|$)/i', $message, $matches)) {
                $entities['name'] = trim($matches[1]);
            }
            if (preg_match('/email\s+([\w\.-]+@[\w\.-]+)/i', $message, $matches)) {
                $entities['email'] = trim($matches[1]);
            }
            if (preg_match('/số\s+điện\s+thoại\s+(\d+)/i', $message, $matches)) {
                $entities['phone'] = trim($matches[1]);
            }
            if (preg_match('/địa\s+chỉ\s+([^\s].*?)(?:\s+ngày\s+sinh|$)/i', $message, $matches)) {
                $entities['address'] = trim($matches[1]);
            }
            if (preg_match('/ngày\s+sinh\s+(\d{4}-\d{2}-\d{2})/i', $message, $matches)) {
                $entities['birthday'] = trim($matches[1]);
            }
            return ['intent' => 'add_customer', 'entities' => $entities, 'confidence' => 0.7];
        }
        
        if (strpos($message, 'tìm') !== false || strpos($message, 'tra cứu') !== false) {
            if (preg_match('/id\s*(\d+)/i', $message, $matches)) {
                return [
                    'intent' => 'find_customer_by_id',
                    'entities' => ['id' => trim($matches[1])],
                    'confidence' => 0.9
                ];
            }
            $keyword = trim(preg_replace('/tìm\s*(khách\s*hàng)?\s*/i', '', $message));
            if (!empty($keyword)) {
                return [
                    'intent' => 'search_customers',
                    'entities' => ['keyword' => $keyword],
                    'confidence' => 0.7
                ];
            }
        }
        
        if (strpos($message, 'khuyến mãi') !== false && strpos($message, 'chi tiết') === false) {
            if (preg_match('/id\s*(\d+)/i', $message, $matches)) {
                return [
                    'intent' => 'check_promotions',
                    'entities' => ['id' => trim($matches[1])],
                    'confidence' => 0.9
                ];
            }
            $keyword = trim(preg_replace('/kiểm\s*tra\s*khuyến\s*mãi\s*(khách\s*hàng)?\s*/i', '', $message));
            if (!empty($keyword)) {
                return [
                    'intent' => 'check_promotions_by_name',
                    'entities' => ['keyword' => $keyword],
                    'confidence' => 0.7
                ];
            }
        }
        
        if (strpos($message, 'sinh nhật') !== false && strpos($message, 'sắp tới') === false && strpos($message, '30 ngày') === false) {
            if (preg_match('/id\s*(\d+)/i', $message, $matches)) {
                return [
                    'intent' => 'check_birthday_by_id',
                    'entities' => ['id' => trim($matches[1])],
                    'confidence' => 0.9
                ];
            }
            $keyword = trim(preg_replace('/kiểm\s*tra\s*sinh\s*nhật\s*(khách\s*hàng)?\s*/i', '', $message));
            if (!empty($keyword)) {
                return [
                    'intent' => 'check_birthday_by_name',
                    'entities' => ['keyword' => $keyword],
                    'confidence' => 0.7
                ];
            }
        }
        
        if (strpos($message, 'lịch sử') !== false || (strpos($message, 'đơn hàng') !== false && strpos($message, 'chi tiết') === false)) {
            if (preg_match('/id\s*(\d+)/i', $message, $matches)) {
                return [
                    'intent' => 'view_order_history',
                    'entities' => ['id' => trim($matches[1])],
                    'confidence' => 0.8
                ];
            }
            $keyword = trim(preg_replace('/(xem\s*(lịch\s*sử\s*)?đơn\s*hàng\s*(khách\s*hàng)?|lịch\s*sử\s*đơn\s*hàng\s*)/i', '', $message));
            if (!empty($keyword)) {
                return [
                    'intent' => 'view_order_history_by_name',
                    'entities' => ['keyword' => $keyword],
                    'confidence' => 0.7
                ];
            }
        }
        
        if (strpos($message, 'sửa') !== false || strpos($message, 'chỉnh sửa') !== false) {
            $entities = [];
            if (preg_match('/id\s*(\d+)/i', $message, $matches)) {
                $entities['id'] = trim($matches[1]);
            }
            if (preg_match('/tên\s+([^\s].*?)(?:\s+email|$)/i', $message, $matches)) {
                $entities['name'] = trim($matches[1]);
            }
            if (preg_match('/email\s+([\w\.-]+@[\w\.-]+)/i', $message, $matches)) {
                $entities['email'] = trim($matches[1]);
            }
            if (preg_match('/số\s+điện\s+thoại\s+(\d+)/i', $message, $matches)) {
                $entities['phone'] = trim($matches[1]);
            }
            if (preg_match('/địa\s+chỉ\s+([^\s].*?)(?:\s+ngày\s+sinh|$)/i', $message, $matches)) {
                $entities['address'] = trim($matches[1]);
            }
            if (preg_match('/ngày\s+sinh\s+(\d{4}-\d{2}-\d{2})/i', $message, $matches)) {
                $entities['birthday'] = trim($matches[1]);
            }
            return ['intent' => 'edit_customer', 'entities' => $entities, 'confidence' => 0.85];
        }
        
        if (strpos($message, 'chép') !== false || strpos($message, 'sao chép') !== false) {
            if (preg_match('/id\s*(\d+)/i', $message, $matches)) {
                return [
                    'intent' => 'copy_customer',
                    'entities' => ['id' => trim($matches[1])],
                    'confidence' => 0.85
                ];
            }
        }
        
        if (strpos($message, 'xóa') !== false || strpos($message, 'xóa bỏ') !== false) {
            if (preg_match('/id\s*(\d+)/i', $message, $matches)) {
                return [
                    'intent' => 'delete_customer',
                    'entities' => ['id' => trim($matches[1])],
                    'confidence' => 0.85
                ];
            }
        }
        
        if (strpos($message, 'chi tiết') !== false && strpos($message, 'đơn hàng') !== false) {
            if (preg_match('/mã\s*đơn\s*(\d+)/i', $message, $matches)) {
                return [
                    'intent' => 'view_order_details',
                    'entities' => ['order_id' => trim($matches[1])],
                    'confidence' => 0.9
                ];
            }
            $keyword = trim(preg_replace('/xem\s*chi\s*tiết\s*đơn\s*hàng\s*(khách\s*hàng)?\s*/i', '', $message));
            if (!empty($keyword)) {
                return [
                    'intent' => 'view_order_details_by_name',
                    'entities' => ['keyword' => $keyword],
                    'confidence' => 0.7
                ];
            }
        }
        
        if (strpos($message, 'khuyến mãi') !== false && strpos($message, 'chi tiết') !== false) {
            if (preg_match('/id\s*(\d+)/i', $message, $matches)) {
                return [
                    'intent' => 'view_detailed_promotions',
                    'entities' => ['id' => trim($matches[1])],
                    'confidence' => 0.9
                ];
            }
            $keyword = trim(preg_replace('/xem\s*chi\s*tiết\s*khuyến\s*mãi\s*(khách\s*hàng)?\s*/i', '', $message));
            if (!empty($keyword)) {
                return [
                    'intent' => 'view_detailed_promotions_by_name',
                    'entities' => ['keyword' => $keyword],
                    'confidence' => 0.7
                ];
            }
        }

        return ['intent' => 'general_question', 'entities' => [], 'confidence' => 0.5];
    }
}

// Enhanced Chatbot Class
class EnhancedChatbot {
    private $db;
    private $intent_analyzer;
    private $gemini_api_key;
    private $gemini_url;
    private $request_count;

    public function __construct($gemini_key, $gemini_url, Database $db) {
        $this->db = $db;
        $this->gemini_api_key = $gemini_key;
        $this->gemini_url = $gemini_url;
        $this->intent_analyzer = new IntentAnalyzer($gemini_key, $gemini_url);
        $this->request_count = 0;
    }

    public function processMessage($user_message, $session_id) {
        $this->request_count++;
        
        $intent_data = $this->intent_analyzer->analyzeIntent($user_message);
        $intent = $intent_data['intent'] ?? 'general_question';
        $entities = $intent_data['entities'] ?? [];
        
        $context_data = $this->executeIntent($intent, $entities, $user_message);
        
        $final_response = $this->generateNaturalResponse($user_message, $context_data, $intent);
        
        $ma_khach_hang = $this->extractCustomerId($entities);
        $this->db->saveChatbotSession($session_id, $ma_khach_hang, $this->request_count, 'active', $final_response);
        
        return $final_response;
    }

    /**
     * Lấy danh sách khách hàng có sinh nhật trong 30 ngày tới
     * @return array
     */
    public function getUpcoming30Days() {
        $customers = $this->db->findUpcomingBirthdays();
        if (empty($customers)) {
            return [
                'type' => 'upcoming_birthdays',
                'data' => [],
                'message' => 'Không có khách hàng nào có sinh nhật trong 30 ngày tới.'
            ];
        }
        return [
            'type' => 'upcoming_birthdays',
            'data' => $customers,
            'message' => 'Danh sách khách hàng có sinh nhật trong 30 ngày tới'
        ];
    }

    private function executeIntent($intent, $entities, $user_message) {
        switch ($intent) {
            case 'list_customers':
                $customers = $this->db->listCustomers();
                return [
                    'type' => 'customer_list',
                    'data' => $customers,
                    'message' => 'Danh sách khách hàng'
                ];

            case 'find_customer_by_id':
                if (isset($entities['id'])) {
                    $customer = $this->db->findCustomerById($entities['id']);
                    return [
                        'type' => 'customer_info',
                        'data' => $customer ? [$customer] : [],
                        'message' => $customer ? 'Thông tin khách hàng' : 'Không tìm thấy khách hàng với ID: ' . $entities['id']
                    ];
                }
                break;

            case 'upcoming_birthdays':
                return $this->getUpcoming30Days();

            case 'search_customers':
                $keyword = $entities['keyword'] ?? $user_message;
                $customers = $this->db->searchCustomers($keyword);
                return [
                    'type' => 'customer_search',
                    'data' => $customers,
                    'message' => 'Kết quả tìm kiếm theo tên: ' . count($customers) . ' khách hàng'
                ];

            case 'check_promotions':
                if (isset($entities['id'])) {
                    $promotions = $this->db->checkPromotions($entities['id']);
                    return [
                        'type' => 'promotions',
                        'data' => $promotions,
                        'message' => 'Thông tin khuyến mãi'
                    ];
                }
                break;
            case 'greeting':
               return [
                'type' => 'greeting',
                'data' => null,
                'message' => 'Xin chào! Tôi là trợ lý Matcha Vibe, rất vui được hỗ trợ ạ!🌱'
            ];
            case 'check_promotions_by_name':
                $keyword = $entities['keyword'] ?? $user_message;
                $customers = $this->db->searchCustomers($keyword);
                if (count($customers) === 1) {
                    $promotions = $this->db->checkPromotions($customers[0]['MaKhachHang']);
                    return [
                        'type' => 'promotions',
                        'data' => $promotions,
                        'message' => 'Thông tin khuyến mãi cho khách hàng ' . $customers[0]['TenKhachHang']
                    ];
                } elseif (count($customers) > 1) {
                    return [
                        'type' => 'customer_search',
                        'data' => $customers,
                        'message' => 'Tìm thấy nhiều khách hàng với tên tương tự. Vui lòng cung cấp ID cụ thể.'
                    ];
                }
                return [
                    'type' => 'error',
                    'data' => null,
                    'message' => 'Không tìm thấy khách hàng với tên: ' . $keyword
                ];

            case 'view_order_history':
                if (isset($entities['id'])) {
                    $orders = $this->db->getOrderHistory($entities['id']);
                    return [
                        'type' => 'order_history',
                        'data' => $orders,
                        'message' => 'Lịch sử đơn hàng'
                    ];
                }
                break;

            case 'view_order_history_by_name':
                $keyword = $entities['keyword'] ?? $user_message;
                $customers = $this->db->searchCustomers($keyword);
                if (count($customers) === 1) {
                    $orders = $this->db->getOrderHistory($customers[0]['MaKhachHang']);
                    return [
                        'type' => 'order_history',
                        'data' => $orders,
                        'message' => 'Lịch sử đơn hàng của khách hàng ' . $customers[0]['TenKhachHang']
                    ];
                } elseif (count($customers) > 1) {
                    return [
                        'type' => 'customer_search',
                        'data' => $customers,
                        'message' => 'Tìm thấy nhiều khách hàng với tên tương tự. Vui lòng cung cấp ID cụ thể.'
                    ];
                }
                return [
                    'type' => 'error',
                    'data' => null,
                    'message' => 'Không tìm thấy khách hàng với tên: ' . $keyword
                ];

            case 'check_birthday_by_id':
                if (isset($entities['id'])) {
                    $birthday = $this->db->getCustomerBirthday($entities['id']);
                    $wishes = $this->db->getBirthdayWishes($entities['id']);
                    return [
                        'type' => 'birthday_info',
                        'data' => ['birthday' => $birthday, 'wishes' => $wishes],
                        'message' => $birthday ? 'Thông tin sinh nhật' : 'Không tìm thấy thông tin sinh nhật cho ID: ' . $entities['id']
                    ];
                }
                break;

            case 'check_birthday_by_name':
                $keyword = $entities['keyword'] ?? $user_message;
                $customers = $this->db->searchCustomers($keyword);
                if (count($customers) === 1) {
                    $birthday = $this->db->getCustomerBirthday($customers[0]['MaKhachHang']);
                    $wishes = $this->db->getBirthdayWishes($customers[0]['MaKhachHang']);
                    return [
                        'type' => 'birthday_info',
                        'data' => ['birthday' => $birthday, 'wishes' => $wishes],
                        'message' => $birthday ? 'Thông tin sinh nhật của khách hàng ' . $customers[0]['TenKhachHang'] : 'Không tìm thấy thông tin sinh nhật cho khách hàng ' . $customers[0]['TenKhachHang']
                    ];
                } elseif (count($customers) > 1) {
                    return [
                        'type' => 'customer_search',
                        'data' => $customers,
                        'message' => 'Tìm thấy nhiều khách hàng với tên tương tự. Vui lòng cung cấp ID cụ thể.'
                    ];
                }
                return [
                    'type' => 'error',
                    'data' => null,
                    'message' => 'Không tìm thấy khách hàng với tên: ' . $keyword
                ];

            case 'view_order_details':
                if (isset($entities['order_id'])) {
                    $details = $this->db->getOrderDetails($entities['order_id']);
                    return [
                        'type' => 'order_details',
                        'data' => $details,
                        'message' => $details ? 'Chi tiết đơn hàng' : 'Không tìm thấy chi tiết đơn hàng'
                    ];
                }
                break;

            case 'view_order_details_by_name':
                $keyword = $entities['keyword'] ?? $user_message;
                $customers = $this->db->searchCustomers($keyword);
                if (count($customers) === 1) {
                    $orders = $this->db->getOrderHistory($customers[0]['MaKhachHang']);
                    if ($orders) {
                        $order_details = [];
                        foreach ($orders as $order) {
                            $details = $this->db->getOrderDetails($order['MaDonHang']);
                            $order_details[] = ['order' => $order, 'details' => $details];
                        }
                        return [
                            'type' => 'order_details_by_name',
                            'data' => $order_details,
                            'message' => 'Chi tiết đơn hàng của khách hàng ' . $customers[0]['TenKhachHang']
                        ];
                    }
                    return [
                        'type' => 'error',
                        'data' => null,
                        'message' => 'Không tìm thấy đơn hàng cho khách hàng ' . $customers[0]['TenKhachHang']
                    ];
                } elseif (count($customers) > 1) {
                    return [
                        'type' => 'customer_search',
                        'data' => $customers,
                        'message' => 'Tìm thấy nhiều khách hàng với tên tương tự. Vui lòng cung cấp ID cụ thể.'
                    ];
                }
                return [
                    'type' => 'error',
                    'data' => null,
                    'message' => 'Không tìm thấy khách hàng với tên: ' . $keyword
                ];

            case 'view_detailed_promotions':
                if (isset($entities['id'])) {
                    $promotions = $this->db->getDetailedPromotions($entities['id']);
                    return [
                        'type' => 'detailed_promotions',
                        'data' => $promotions,
                        'message' => $promotions ? 'Chi tiết khuyến mãi' : 'Không có khuyến mãi'
                    ];
                }
                break;

            case 'view_detailed_promotions_by_name':
                $keyword = $entities['keyword'] ?? $user_message;
                $customers = $this->db->searchCustomers($keyword);
                if (count($customers) === 1) {
                    $promotions = $this->db->getDetailedPromotions($customers[0]['MaKhachHang']);
                    return [
                        'type' => 'detailed_promotions',
                        'data' => $promotions,
                        'message' => $promotions ? 'Chi tiết khuyến mãi cho khách hàng ' . $customers[0]['TenKhachHang'] : 'Không có khuyến mãi cho khách hàng ' . $customers[0]['TenKhachHang']
                    ];
                } elseif (count($customers) > 1) {
                    return [
                        'type' => 'customer_search',
                        'data' => $customers,
                        'message' => 'Tìm thấy nhiều khách hàng với tên tương tự. Vui lòng cung cấp ID cụ thể.'
                    ];
                }
                return [
                    'type' => 'error',
                    'data' => null,
                    'message' => 'Không tìm thấy khách hàng với tên: ' . $keyword
                ];

            case 'add_customer':
                if (isset($entities['name'], $entities['email'], $entities['phone'], $entities['address'], $entities['birthday'])) {
                    $result = $this->db->addCustomer(
                        $entities['name'],
                        $entities['email'],
                        $entities['phone'],
                        $entities['address'],
                        $entities['birthday']
                    );
                    return [
                        'type' => 'add_customer',
                        'data' => $result,
                        'message' => $result ? 'Đã thêm khách hàng thành công' : 'Lỗi khi thêm khách hàng'
                    ];
                }
                return [
                    'type' => 'request_info',
                    'data' => null,
                    'message' => 'Để thêm khách hàng, cung cấp: Tên, Email, Số điện thoại, Địa chỉ, Ngày sinh (YYYY-MM-DD)'
                ];

            case 'edit_customer':
                if (isset($entities['id'], $entities['name'], $entities['email'], $entities['phone'], $entities['address'], $entities['birthday'])) {
                    $result = $this->db->updateCustomer(
                        $entities['id'],
                        $entities['name'],
                        $entities['email'],
                        $entities['phone'],
                        $entities['address'],
                        $entities['birthday']
                    );
                    return [
                        'type' => 'edit_customer',
                        'data' => $result,
                        'message' => $result ? 'Đã cập nhật khách hàng' : 'Lỗi khi cập nhật'
                    ];
                }
                return [
                    'type' => 'request_info',
                    'data' => ['id' => $entities['id'] ?? null],
                    'message' => 'Cung cấp thông tin cập nhật cho ID ' . ($entities['id'] ?? 'chưa xác định') . ': Tên, Email, Số điện thoại, Địa chỉ, Ngày sinh'
                ];

            case 'copy_customer':
                if (isset($entities['id'])) {
                    $result = $this->db->copyCustomer($entities['id']);
                    return [
                        'type' => 'copy_customer',
                        'data' => $result,
                        'message' => $result ? 'Đã sao chép khách hàng' : 'Không tìm thấy khách hàng để sao chép'
                    ];
                }
                break;

            case 'delete_customer':
                if (isset($entities['id'])) {
                    $result = $this->db->deleteCustomer($entities['id']);
                    return [
                        'type' => 'delete_customer',
                        'data' => $result,
                        'message' => $result ? 'Đã xóa khách hàng' : 'Không tìm thấy khách hàng để xóa'
                    ];
                }
                break;

            default:
                return [
                    'type' => 'general',
                    'data' => null,
                    'message' => 'Tôi có thể giúp quản lý khách hàng. Xin lỗi bạn vì hiện tại tôi không thể xử lý yêu cầu này. Mời bạn nhập lại câu hỏi hoặc yêu cầu khác.'
                ];
        }

        return [
            'type' => 'error',
            'data' => null,
            'message' => 'Không thể xử lý yêu cầu. Vui lòng cung cấp tên khách hàng hoặc ID.'
        ];
    }

    private function generateNaturalResponse($user_message, $context_data, $intent) {
        global $RESPONSE_GENERATION_PROMPT;

        $context_text = $this->formatContextForGemini($context_data);
        $system_prompt = str_replace(
            ['{{context_text}}', '{{user_message}}'],
            [$context_text, $user_message],
            $RESPONSE_GENERATION_PROMPT
        );

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $system_prompt]
                    ]
                ]
            ]
        ];

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $this->gemini_api_key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && $response) {
            $result = json_decode($response, true);
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (trim($text) !== '') {
                return $text;
            }
        }
        return $context_text;
    }

    private function formatContextForGemini($context_data) {
        $type = $context_data['type'];
        $data = $context_data['data'];
        $message = $context_data['message'];

        switch ($type) {
            case 'customer_list':
            case 'customer_search':
            case 'customer_info':
                if (empty($data)) return htmlspecialchars($message);
                $table = "<table class='table-auto w-full border-collapse border border-gray-300'>";
                $table .= "<thead><tr class='bg-green-100'>";
                $table .= "<th class='border px-2 py-1'>ID</th>";
                $table .= "<th class='border px-2 py-1'>Tên</th>";
                $table .= "<th class='border px-2 py-1'>SĐT</th>";
                $table .= "<th class='border px-2 py-1'>Địa chỉ</th>";
                $table .= "<th class='border px-2 py-1'>Ngày sinh</th>";
                $table .= "<th class='border px-2 py-1'>Ngày tạo</th>";
                $table .= "<th class='border px-2 py-1'>Trạng thái</th>";
                $table .= "</tr></thead><tbody>";
                foreach ($data as $customer) {
                    $table .= "<tr>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($customer['MaKhachHang']) . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($customer['TenKhachHang']) . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($customer['DienThoai'] ?? '') . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($customer['DiaChi'] ?? '') . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($customer['NgaySinh'] ?? '') . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($customer['NgayTao'] ?? '') . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars(($customer['TrangThai'] == 'active') ? 'Hoạt động' : 'Không hoạt động') . "</td>";
                    $table .= "</tr>";
                }
                $table .= "</tbody></table>";
                return htmlspecialchars($message) . ":<br>" . $table;

            case 'promotions':
                if (empty($data)) return htmlspecialchars($message);
                $text = htmlspecialchars($message) . ":<br>";
                foreach ($data as $promo) {
                    $text .= "- " . htmlspecialchars($promo['LoaiKhuyenMai']) . ": Giảm " . htmlspecialchars($promo['GiaTri']) . "%, Tối thiểu " . number_format($promo['DonHangToiThieu']) . " VND, Cấp bậc: " . htmlspecialchars($promo['CapBac']) . "<br>";
                }
                return $text;

            case 'detailed_promotions':
                if (empty($data)) return htmlspecialchars($message);
                $table = "<table class='table-auto w-full border-collapse border border-gray-300'>";
                $table .= "<thead><tr class='bg-green-100'>";
                $table .= "<th class='border px-2 py-1'>Mã KM</th>";
                $table .= "<th class='border px-2 py-1'>Loại</th>";
                $table .= "<th class='border px-2 py-1'>Giá trị</th>";
                $table .= "<th class='border px-2 py-1'>Mô tả</th>";
                $table .= "<th class='border px-2 py-1'>Đơn tối thiểu</th>";
                $table .= "<th class='border px-2 py-1'>Cấp bậc</th>";
                $table .= "<th class='border px-2 py-1'>Ngày tạo</th>";
                $table .= "<th class='border px-2 py-1'>Ngày hết hạn</th>";
                $table .= "</tr></thead><tbody>";
                foreach ($data as $promo) {
                    $table .= "<tr>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($promo['MaKhuyenMai']) . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($promo['LoaiKhuyenMai']) . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($promo['GiaTri']) . "%</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($promo['MoTa']) . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . number_format($promo['DonHangToiThieu']) . " VND</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($promo['CapBac']) . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($promo['NgayTao']) . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($promo['NgayHetHan']) . "</td>";
                    $table .= "</tr>";
                }
                $table .= "</tbody></table>";
                return htmlspecialchars($message) . ":<br>" . $table;

            case 'upcoming_birthdays':
                if (empty($data)) return htmlspecialchars($message);
                $table = "<table class='table-auto w-full border-collapse border border-gray-300'>";
                $table .= "<thead><tr class='bg-green-100'>";
                $table .= "<th class='border px-2 py-1'>ID</th>";
                $table .= "<th class='border px-2 py-1'>Tên khách hàng</th>";
                $table .= "<th class='border px-2 py-1'>Ngày sinh</th>";
                $table .= "</tr></thead><tbody>";
                foreach ($data as $customer) {
                    $table .= "<tr>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($customer['MaKhachHang']) . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($customer['TenKhachHang']) . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($customer['NgaySinh']) . "</td>";
                    $table .= "</tr>";
                }
                $table .= "</tbody></table>";
                return htmlspecialchars($message) . ":<br>" . $table;
            case 'greeting':
                return htmlspecialchars($message);
            case 'order_history':
                if (empty($data)) return htmlspecialchars($message);
                $table = "<table class='table-auto w-full border-collapse border border-gray-300'>";
                $table .= "<thead><tr class='bg-green-100'>";
                $table .= "<th class='border px-2 py-1'>Mã đơn</th>";
                $table .= "<th class='border px-2 py-1'>Tổng tiền</th>";
                $table .= "<th class='border px-2 py-1'>Ngày mua</th>";
                $table .= "<th class='border px-2 py-1'>Giảm giá</th>";
                $table .= "<th class='border px-2 py-1'>Thanh toán</th>";
                $table .= "</tr></thead><tbody>";
                foreach ($data as $order) {
                    $table .= "<tr>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($order['MaDonHang']) . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . number_format($order['TongTien']) . " VND</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($order['NgayMua']) . "</td>";
                    $table .= "<td class='border px-2 py-1'>" . htmlspecialchars($order['PhanTramGiamGia']) . "%</td>";
                    $table .= "<td class='border px-2 py-1'>" . ($order['TrangThaiThanhToan'] ? 'Đã thanh toán' : 'Chưa thanh toán') . "</td>";
                    $table .= "</tr>";
                }
                $table .= "</tbody></table>";
                return htmlspecialchars($message) . ":<br>" . $table;

            case 'order_details':
            case 'order_details_by_name':
                if (empty($data)) return htmlspecialchars($message);
                $output = "";
                if ($type === 'order_details') {
                    $output .= "<table class='table-auto w-full border-collapse border border-gray-300'>";
                    $output .= "<thead><tr class='bg-green-100'>";
                    $output .= "<th class='border px-2 py-1'>Sản phẩm</th>";
                    $output .= "<th class='border px-2 py-1'>Số lượng</th>";
                    $output .= "<th class='border px-2 py-1'>Đơn giá</th>";
                    $output .= "<th class='border px-2 py-1'>Thành tiền</th>";
                    $output .= "</tr></thead><tbody>";
                    foreach ($data as $detail) {
                        $total = $detail['SoLuong'] * $detail['DonGia'];
                        $output .= "<tr>";
                        $output .= "<td class='border px-2 py-1'>" . htmlspecialchars($detail['SanPham']) . "</td>";
                        $output .= "<td class='border px-2 py-1'>" . htmlspecialchars($detail['SoLuong']) . "</td>";
                        $output .= "<td class='border px-2 py-1'>" . number_format($detail['DonGia']) . " VND</td>";
                        $output .= "<td class='border px-2 py-1'>" . number_format($total) . " VND</td>";
                        $output .= "</tr>";
                    }
                    $output .= "</tbody></table>";
                } else {
                    foreach ($data as $order_data) {
                        $order = $order_data['order'];
                        $details = $order_data['details'];
                        $output .= "<p><strong>Đơn hàng " . htmlspecialchars($order['MaDonHang']) . " (Ngày mua: " . htmlspecialchars($order['NgayMua']) . ", Tổng tiền: " . number_format($order['TongTien']) . " VND)</strong></p>";
                        $output .= "<table class='table-auto w-full border-collapse border border-gray-300'>";
                        $output .= "<thead><tr class='bg-green-100'>";
                        $output .= "<th class='border px-2 py-1'>Sản phẩm</th>";
                        $output .= "<th class='border px-2 py-1'>Số lượng</th>";
                        $output .= "<th class='border px-2 py-1'>Đơn giá</th>";
                        $output .= "<th class='border px-2 py-1'>Thành tiền</th>";
                        $output .= "</tr></thead><tbody>";
                        foreach ($details as $detail) {
                            $total = $detail['SoLuong'] * $detail['DonGia'];
                            $output .= "<tr>";
                            $output .= "<td class='border px-2 py-1'>" . htmlspecialchars($detail['SanPham']) . "</td>";
                            $output .= "<td class='border px-2 py-1'>" . htmlspecialchars($detail['SoLuong']) . "</td>";
                            $output .= "<td class='border px-2 py-1'>" . number_format($detail['DonGia']) . " VND</td>";
                            $output .= "<td class='border px-2 py-1'>" . number_format($total) . " VND</td>";
                            $output .= "</tr>";
                        }
                        $output .= "</tbody></table>";
                    }
                }
                return htmlspecialchars($message) . ":<br>" . $output;

            case 'birthday_info':
                if (!$data['birthday']) return htmlspecialchars($message);
                $text = htmlspecialchars($message) . ":<br>";
                $text .= "Ngày sinh: " . htmlspecialchars($data['birthday']) . "<br>";
                $text .= "Lời chúc sinh nhật:<br>";
                if (empty($data['wishes'])) {
                    $text .= "Chưa có lời chúc sinh nhật.";
                } else {
                    foreach ($data['wishes'] as $wish) {
                        $text .= "- " . htmlspecialchars($wish['NoiDung']) . " (Gửi: " . htmlspecialchars($wish['ThoiGianGui']) . ", Trạng thái: " . ($wish['TrangThai'] ? 'Đã gửi' : 'Chưa gửi') . ")<br>";
                    }
                }
                return $text;

            case 'add_customer':
            case 'edit_customer':
            case 'copy_customer':
            case 'delete_customer':
            case 'request_info':
                return htmlspecialchars($message);

            default:
                return htmlspecialchars($message);
        }
    }

    private function extractCustomerId($entities) {
        if (isset($entities['id'])) {
            $customer = $this->db->findCustomerById($entities['id']);
            return $customer ? $customer['MaKhachHang'] : null;
        }
        return null;
    }
}

// Khởi tạo
$db = new Database();
$gemini_api_key = 'AIzaSyDTT-nNKiJnb81LowUsEk_2FsnOXb09ekk';
$gemini_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
$chatbot = new EnhancedChatbot($gemini_api_key, $gemini_url, $db);

// Xử lý POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $user_message = trim($_POST['message']);
    $messages[] = ['sender' => 'user', 'text' => $user_message];
    $_SESSION['messages'] = $messages;
    $is_loading = true;
    // Lưu trạng thái loading để hiển thị hiệu ứng dấu ba chấm
    $_SESSION['is_loading'] = true;
    // Xử lý trả lời bot
    $bot_response = $chatbot->processMessage($user_message, session_id());
    $messages[] = ['sender' => 'bot', 'text' => $bot_response];
    $_SESSION['messages'] = $messages;
    $is_loading = false;
    $_SESSION['is_loading'] = false;
}

// --- AJAX partial rendering for chat container ---
if ($is_ajax) {
    ob_start();
    ?>
    <div id="chat-container" class="h-96 overflow-y-auto p-6 bg-gray-50 space-y-4">
        <?php if (empty($messages)): ?>
            <div class="text-center text-gray-500 py-8">
                <div class="text-4xl mb-4">🤖</div>
                <p class="text-lg">Xin chào! Tôi có thể giúp bạn:</p>
                <div class="mt-4 text-sm text-left max-w-md mx-auto bg-white p-4 rounded-lg">
                    <ul class="space-y-2">
                        <li>• Tìm khách hàng theo tên (VD: Tìm Nguyễn Văn A)</li>
                        <li>• Xem sinh nhật theo tên (VD: Sinh nhật của Nguyễn Văn A)</li>
                        <li>• Xem chi tiết đơn hàng theo tên (VD: Chi tiết đơn hàng của Nguyễn Văn A)</li>
                        <li>• Liệt kê tất cả khách hàng</li>
                        <li>• Kiểm tra khuyến mãi, đơn hàng theo ID</li>
                        <li>• Thêm, sửa, sao chép, hoặc xóa khách hàng</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($messages as $msg): ?>
            <?php if ($msg['sender'] === 'user'): ?>
                <div class="flex justify-end">
                    <div class="bg-emerald-200 text-gray-900 rounded-lg px-4 py-2 mb-2 max-w-xl shadow">
                        <?php echo htmlspecialchars($msg['text']); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex justify-start">
                    <div class="bg-white border border-emerald-200 text-gray-800 rounded-lg px-4 py-2 mb-2 max-w-xl shadow chat-container">
                        <?php echo $msg['text']; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!empty($_SESSION['is_loading']) && $_SESSION['is_loading']): ?>
            <div class="flex justify-start">
                <div class="typing-indicator">
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    echo ob_get_clean();
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Quản Lý Khách Hàng - Matcha Vibe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body, input, button { font-family: 'Roboto', sans-serif; }
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            animation: typing 1.4s infinite ease-in-out;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typing {
            0%, 80%, 100% { transform: scale(0); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }
        table { font-size: 0.875rem; }
        th, td { text-align: left; }
        .chat-container { max-width: 100%; overflow-x: auto; }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-emerald-100 min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-emerald-600 text-white p-6">
                <h1 class="text-2xl font-bold text-center">🍃 Matcha Vibe Chatbot</h1>
                <p class="text-center text-green-100 mt-2">Trợ lý quản lý khách hàng thông minh</p>
            </div>

            <!-- Chat Container -->
            <div id="chat-container" class="h-96 overflow-y-auto p-6 bg-gray-50 space-y-4">
                <?php if (empty($messages)): ?>
                    <div class="text-center text-gray-500 py-8">
                        <div class="text-4xl mb-4">🤖</div>
                        <p class="text-lg">Xin chào! Tôi có thể giúp bạn:</p>
                        <div class="mt-4 text-sm text-left max-w-md mx-auto bg-white p-4 rounded-lg">
                            <ul class="space-y-2">
                                <li>• Tìm khách hàng theo tên (VD: Tìm Nguyễn Văn A)</li>
                                <li>• Xem sinh nhật theo tên (VD: Sinh nhật của Nguyễn Văn A)</li>
                                <li>• Xem chi tiết đơn hàng theo tên (VD: Chi tiết đơn hàng của Nguyễn Văn A)</li>
                                <li>• Liệt kê tất cả khách hàng</li>
                                <li>• Kiểm tra khuyến mãi, đơn hàng theo ID</li>
                                <li>• Thêm, sửa, sao chép, hoặc xóa khách hàng</li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php foreach ($messages as $msg): ?>
                    <?php if ($msg['sender'] === 'user'): ?>
                        <div class="flex justify-end">
                            <div class="bg-emerald-200 text-gray-900 rounded-lg px-4 py-2 mb-2 max-w-xl shadow">
                                <?php echo htmlspecialchars($msg['text']); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex justify-start">
                            <div class="bg-white border border-emerald-200 text-gray-800 rounded-lg px-4 py-2 mb-2 max-w-xl shadow chat-container">
                                <?php echo $msg['text']; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (!empty($_SESSION['is_loading']) && $_SESSION['is_loading']): ?>
                    <div class="flex justify-start">
                        <div class="typing-indicator">
                            <span class="typing-dot"></span>
                            <span class="typing-dot"></span>
                            <span class="typing-dot"></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Chat Form -->
            <form id="chat-form" class="flex items-center gap-2 border-t px-6 py-4 bg-white" autocomplete="off">
            <input 
                 type="text" 
                 name="message" 
                 id="message-input"
        class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" 
        placeholder="Xin mời bạn nhập câu hỏi hoặc yêu cầu của mình..." 
        autocomplete="off"
        required
    >
    <button 
        type="submit" 
        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400">
        Gửi
    </button>
</form>
        </div>
    </div>
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();
        window.onload = function() {
            var chat = document.getElementById('chat-container');
            if (chat) chat.scrollTop = chat.scrollHeight;
        };
    </script>
    <script>
feather.replace();
window.onload = function() {
    var chat = document.getElementById('chat-container');
    if (chat) chat.scrollTop = chat.scrollHeight;
};

document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    if (!message) return;

    // 1. Hiển thị tin nhắn người dùng ngay lập tức
    const chatContainer = document.getElementById('chat-container');
    const userDiv = document.createElement('div');
    userDiv.className = 'flex justify-end';
    userDiv.innerHTML = `
        <div class="bg-emerald-200 text-gray-900 rounded-lg px-4 py-2 mb-2 max-w-xl shadow">
            ${escapeHtml(message)}
        </div>
    `;
    chatContainer.appendChild(userDiv);

    // 2. Hiển thị hiệu ứng typing ngay sau tin nhắn user
    const typingDiv = document.createElement('div');
    typingDiv.className = 'flex justify-start typing-indicator-wrapper';
    typingDiv.innerHTML = `
        <div class="typing-indicator">
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
        </div>
    `;
    chatContainer.appendChild(typingDiv);
    chatContainer.scrollTop = chatContainer.scrollHeight;

    input.value = '';
    input.focus();

    // 3. Gửi AJAX
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(message)
    })
    .then(res => res.text())
    .then(html => {
        // Parse lại phần chat-container từ response
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newChat = doc.getElementById('chat-container');
        if (newChat) {
            chatContainer.innerHTML = newChat.innerHTML;
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    });

    // Hàm escape HTML để tránh lỗi XSS
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
</script>
</body>
</html>