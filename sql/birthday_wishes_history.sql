CREATE TABLE IF NOT EXISTS BirthdayWishesHistory (
    MaLoiChuc INT PRIMARY KEY AUTO_INCREMENT,
    MaKhachHang INT NOT NULL,
    NoiDung TEXT NOT NULL,
    NgayGui TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    NguoiGui VARCHAR(255) NOT NULL,
    PhuongThucGui ENUM('email', 'sms', 'system') NOT NULL,
    TrangThai ENUM('success', 'failed') NOT NULL,
    GhiChu TEXT,
    FOREIGN KEY (MaKhachHang) REFERENCES KhachHang(MaKhachHang) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 