-- Tạo database
DROP DATABASE IF EXISTS matchavibe;
CREATE DATABASE matchavibe;
USE matchavibe;

SET FOREIGN_KEY_CHECKS=0;

-- Xóa các bảng nếu tồn tại
DROP TABLE IF EXISTS `tinnhan`;
DROP TABLE IF EXISTS `khuyenmai`;
DROP TABLE IF EXISTS `birthdaywisheshistory`;
DROP TABLE IF EXISTS `loichucsinhnhat`;
DROP TABLE IF EXISTS `orderdetails`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `khachhang`;
DROP TABLE IF EXISTS `nguoidung`;

-- Bảng người dùng (Admin/Staff)
CREATE TABLE `nguoidung` (
  `MaNguoiDung` int(11) NOT NULL AUTO_INCREMENT,
  `TenDangNhap` varchar(50) NOT NULL,
  `MatKhau` varchar(255) NOT NULL,
  `NgayTao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`MaNguoiDung`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu cho bảng nguoidung
INSERT INTO `nguoidung` (`TenDangNhap`, `MatKhau`) VALUES
('admin', 'admin123'),
('staff1', 'staff123'),
('staff2', 'staff456');

-- Bảng khách hàng
CREATE TABLE `khachhang` (
  `MaKhachHang` int(11) NOT NULL AUTO_INCREMENT,
  `TenKhachHang` varchar(50) NOT NULL,
  `DienThoai` varchar(15) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `DiaChi` varchar(100) DEFAULT NULL,
  `NgaySinh` date DEFAULT NULL,
  `NgayTao` datetime DEFAULT current_timestamp(),
  `TrangThai` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`MaKhachHang`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu cho bảng khachhang
INSERT INTO `khachhang` (`TenKhachHang`, `DienThoai`, `Email`, `DiaChi`, `NgaySinh`, `TrangThai`) VALUES
('Trần Thị Bình', '0912345678', 'tranthibinh@example.com', '456 Đường Nguyễn Huệ, Hà Nội', '1985-06-15', 'active'),
('Lê Văn Cường', '0923456789', 'levancuong@example.com', '789 Đường Trần Phú, Đà Nẵng', '1995-05-22', 'active'),
('Phạm Thị Dung', '0934567892', 'phamthidung@example.com', '101 Đường Võ Văn Tần, TP.HCM', '1992-07-20', 'active'),
('Hoàng Văn Em', '0945678901', 'hoangvanem@example.com', '202 Đường Nguyễn Trãi, Hà Nội', '1988-05-23', 'active'),
('Vũ Thị Hoa', '0956789012', 'vuthihoa@example.com', '303 Đường Lý Thường Kiệt, Đà Nẵng', '1993-08-10', 'inactive');

-- Bảng khuyến mãi
CREATE TABLE `khuyenmai` (
  `MaKhuyenMai` int(11) NOT NULL AUTO_INCREMENT,
  `MaKhachHang` int(11) NOT NULL,
  `LoaiKhuyenMai` varchar(20) NOT NULL,
  `GiaTri` decimal(5,2) NOT NULL,
  `MoTa` text NOT NULL,
  `DonHangToiThieu` decimal(10,2) NOT NULL DEFAULT 0,
  `CapBac` varchar(20) NOT NULL,
  `NgayTao` datetime DEFAULT current_timestamp(),
  `NgayHetHan` datetime NOT NULL,
  PRIMARY KEY (`MaKhuyenMai`),
  KEY `MaKhachHang` (`MaKhachHang`),
  CONSTRAINT `fk_khuyenmai_khachhang` FOREIGN KEY (`MaKhachHang`) 
    REFERENCES `khachhang` (`MaKhachHang`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu cho bảng khuyenmai
INSERT INTO `khuyenmai` (`MaKhachHang`, `LoaiKhuyenMai`, `GiaTri`, `MoTa`, `DonHangToiThieu`, `CapBac`, `NgayHetHan`) VALUES
(1, 'discount', 15.00, 'Giảm 15% cho mọi đơn hàng', 0.00, 'VIP', DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'birthday', 20.00, 'Giảm 20% trong tháng sinh nhật', 300000.00, 'Cao cấp', DATE_ADD(NOW(), INTERVAL 30 DAY)),
(3, 'loyalty', 5.00, 'Giảm 5% cho khách hàng thân thiết', 200000.00, 'Trung bình', DATE_ADD(NOW(), INTERVAL 30 DAY));

-- Bảng đơn hàng
CREATE TABLE `orders` (
  `MaDonHang` int(11) NOT NULL AUTO_INCREMENT,
  `MaKhachHang` int(11) NOT NULL,
  `TongTien` decimal(10,2) NOT NULL DEFAULT 0.00,
  `NgayMua` datetime DEFAULT current_timestamp(),
  `PhanTramGiamGia` decimal(5,2) DEFAULT 0.00,
  `TrangThaiThanhToan` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`MaDonHang`),
  KEY `MaKhachHang` (`MaKhachHang`),
  CONSTRAINT `fk_orders_khachhang` FOREIGN KEY (`MaKhachHang`) 
    REFERENCES `khachhang` (`MaKhachHang`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu cho bảng orders
INSERT INTO `orders` (`MaKhachHang`, `TongTien`, `PhanTramGiamGia`, `TrangThaiThanhToan`) VALUES
(1, 500000.00, 15.00, 1),
(2, 300000.00, 10.00, 1),
(3, 200000.00, 5.00, 0),
(4, 150000.00, 0.00, 0);

-- Bảng chi tiết đơn hàng
CREATE TABLE `orderdetails` (
  `MaChiTiet` int(11) NOT NULL AUTO_INCREMENT,
  `MaDonHang` int(11) NOT NULL,
  `SanPham` varchar(100) NOT NULL,
  `SoLuong` int(11) NOT NULL DEFAULT 1,
  `DonGia` decimal(10,2) NOT NULL,
  PRIMARY KEY (`MaChiTiet`),
  KEY `MaDonHang` (`MaDonHang`),
  CONSTRAINT `fk_orderdetails_orders` FOREIGN KEY (`MaDonHang`) 
    REFERENCES `orders` (`MaDonHang`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu cho bảng orderdetails
INSERT INTO `orderdetails` (`MaDonHang`, `SanPham`, `SoLuong`, `DonGia`) VALUES
(1, 'Matcha Latte', 2, 150000.00),
(1, 'Green Tea Cake', 1, 200000.00),
(2, 'Matcha Ice Blended', 3, 100000.00),
(3, 'Matcha Cookie Set', 2, 100000.00),
(4, 'Matcha Latte', 1, 150000.00);

-- Bảng lời chúc sinh nhật
CREATE TABLE `loichucsinhnhat` (
  `MaLoiChuc` int(11) NOT NULL AUTO_INCREMENT,
  `MaKhachHang` int(11) NOT NULL,
  `NoiDung` varchar(255) NOT NULL,
  `ThoiGianGui` datetime DEFAULT current_timestamp(),
  `TrangThai` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`MaLoiChuc`),
  KEY `MaKhachHang` (`MaKhachHang`),
  CONSTRAINT `fk_loichuc_khachhang` FOREIGN KEY (`MaKhachHang`) 
    REFERENCES `khachhang` (`MaKhachHang`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu cho bảng loichucsinhnhat
INSERT INTO `loichucsinhnhat` (`MaKhachHang`, `NoiDung`, `TrangThai`) VALUES
(1, 'Chúc mừng sinh nhật! Matcha Vibe tặng bạn voucher giảm giá 20%', 1),
(2, 'Happy Birthday! Chúc bạn một ngày sinh nhật thật vui vẻ', 1),
(3, 'Sinh nhật vui vẻ! Ghé Matcha Vibe để nhận ưu đãi đặc biệt nhé', 0);

-- Bảng lịch sử lời chúc sinh nhật
CREATE TABLE `birthdaywisheshistory` (
  `MaLichSu` int(11) NOT NULL AUTO_INCREMENT,
  `MaKhachHang` int(11) NOT NULL,
  `Nam` int(4) NOT NULL,
  `DaGui` tinyint(1) DEFAULT 0,
  `NgayGui` datetime DEFAULT NULL,
  `NoiDung` text DEFAULT NULL,
  PRIMARY KEY (`MaLichSu`),
  UNIQUE KEY `unique_wish` (`MaKhachHang`, `Nam`),
  KEY `MaKhachHang` (`MaKhachHang`),
  CONSTRAINT `fk_history_khachhang` FOREIGN KEY (`MaKhachHang`) 
    REFERENCES `khachhang` (`MaKhachHang`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu cho bảng birthdaywisheshistory
INSERT INTO `birthdaywisheshistory` (`MaKhachHang`, `Nam`, `DaGui`, `NgayGui`, `NoiDung`) VALUES
(1, 2024, 1, '2024-06-15 09:00:00', 'Chúc mừng sinh nhật! Matcha Vibe tặng bạn voucher giảm giá 20%'),
(2, 2024, 1, '2024-05-22 10:00:00', 'Happy Birthday! Chúc bạn một ngày sinh nhật thật vui vẻ'),
(3, 2024, 0, NULL, NULL);

-- Bảng tin nhắn
CREATE TABLE `tinnhan` (
  `MaTinNhan` int(11) NOT NULL AUTO_INCREMENT,
  `MaKhachHang` int(11) NOT NULL,
  `NoiDung` text NOT NULL,
  `ThoiGian` datetime DEFAULT current_timestamp(),
  `LaNhanVien` tinyint(1) DEFAULT 0,
  `LaSinhNhat` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`MaTinNhan`),
  KEY `MaKhachHang` (`MaKhachHang`),
  CONSTRAINT `fk_tinnhan_khachhang` FOREIGN KEY (`MaKhachHang`) 
    REFERENCES `khachhang` (`MaKhachHang`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu cho bảng tinnhan
INSERT INTO `tinnhan` (`MaKhachHang`, `NoiDung`, `LaNhanVien`, `LaSinhNhat`) VALUES
(1, 'Chào anh/chị, Matcha Vibe có thể giúp gì được ạ?', 1, 0),
(1, 'Cho mình hỏi về menu mới', 0, 0),
(2, 'Chúc mừng sinh nhật! Matcha Vibe tặng bạn voucher 20%', 1, 1),
(3, 'Đơn hàng của bạn đã được xác nhận', 1, 0);

SET FOREIGN_KEY_CHECKS=1;