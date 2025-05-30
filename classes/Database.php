<?php
class Database {
    private $host = 'localhost';
    private $dbname = 'matchavibe';
    private $username = 'root';
    private $password = '';
    private $conn = null;

    public function connect() {
        try {
            if ($this->conn === null) {
                $this->conn = new PDO(
                    "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            }
            return $this->conn;
        } catch (PDOException $e) {
            throw new Exception("Lỗi kết nối database: " . $e->getMessage());
        }
    }

    public function close() {
        $this->conn = null;
    }
}
?>