<?php
require_once 'Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            throw new Exception("Tên đăng nhập và mật khẩu không được để trống");
        }

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("SELECT MaNguoiDung, TenDangNhap, MatKhau FROM NguoiDung WHERE TenDangNhap = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['MatKhau'] === $password) {
                $_SESSION['user_id'] = $user['MaNguoiDung'];
                $_SESSION['username'] = $user['TenDangNhap'];
                $this->db->close();
                return true;
            } else {
                $this->db->close();
                throw new Exception("Tên đăng nhập hoặc mật khẩu không đúng");
            }
        } catch (PDOException $e) {
            throw new Exception("Lỗi đăng nhập: " . $e->getMessage());
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
    }
}
?>