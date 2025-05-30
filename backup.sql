-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: matchavibe
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `khachhang`
--

DROP TABLE IF EXISTS `khachhang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `khachhang`
--

LOCK TABLES `khachhang` WRITE;
/*!40000 ALTER TABLE `khachhang` DISABLE KEYS */;
INSERT INTO `khachhang` VALUES (2,'Trần Thị Bình','0912345678','tranthibinh@example.com','456 Đường Nguyễn Huệ, Hà Nội','1985-06-15','2025-05-22 20:53:05','inactive'),(3,'Lê Văn Cường','0923456789','levancuong@example.com','789 Đường Trần Phú, Đà Nẵng','1995-05-22','2025-05-22 20:53:05','active'),(4,'Phạm Thị Dung','0934567892','phamthidung@example.com','101 Đường Võ Văn Tần, TP.HCM','1992-07-20','2025-05-22 20:53:05','active'),(5,'Hoàng Văn Em','0945678901','hoangvanem@example.com','202 Đường Nguyễn Trãi, Hà Nội','1988-05-23','2025-05-22 20:53:05','active'),(6,'Vũ Thị Hoa','0956789012','vuthihoa@example.com','303 Đường Lý Thường Kiệt, Đà Nẵng','1993-08-10','2025-05-22 20:53:05','inactive'),(7,'Đỗ Văn Khang','0967890123','dovankhang@example.com','404 Đường Phạm Ngũ Lão, TP.HCM','1990-09-05','2025-05-22 20:53:05','active'),(8,'Bùi Thị Lan','0978901234','buithilan@example.com','505 Đường Hai Bà Trưng, Hà Nội','1987-05-22','2025-05-22 20:53:05','active'),(9,'Ngô Văn Minh','0989012345','ngovanminh@example.com','606 Đường Điện Biên Phủ, Đà Nẵng','1994-10-12','2025-05-22 20:53:05','inactive'),(10,'Mai Thị Ngọc','0990123456','maithingoc@example.com','707 Đường Cách Mạng Tháng Tám, TP.HCM','1991-11-25','2025-05-22 20:53:05','active'),(11,'BertramNgo','0865492201','ngotuananh9922@gmail.com','HaDong','2025-05-29','2025-05-22 22:40:03','active'),(14,'Bertram','0987657849','john@gmail.com','Hai Duong','2025-05-07','2025-05-22 22:42:59','active'),(18,'Tuan Anh Ngo','0987657849','john1@gmail.com','Hai Duong','2025-05-24','2025-05-22 23:13:55','active'),(19,'Pham Trung Kien','0875638321','kienpt@gmail.com','Nam Dinh','2025-05-25','2025-05-22 23:51:14','active');
/*!40000 ALTER TABLE `khachhang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loichucsinhnhat`
--

DROP TABLE IF EXISTS `loichucsinhnhat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loichucsinhnhat` (
  `MaLoiChuc` int(11) NOT NULL AUTO_INCREMENT,
  `MaKhachHang` int(11) DEFAULT NULL,
  `NoiDung` varchar(255) NOT NULL,
  `ThoiGianGui` datetime DEFAULT NULL,
  `TrangThai` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`MaLoiChuc`),
  KEY `MaKhachHang` (`MaKhachHang`),
  CONSTRAINT `loichucsinhnhat_ibfk_1` FOREIGN KEY (`MaKhachHang`) REFERENCES `khachhang` (`MaKhachHang`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loichucsinhnhat`
--

LOCK TABLES `loichucsinhnhat` WRITE;
/*!40000 ALTER TABLE `loichucsinhnhat` DISABLE KEYS */;
INSERT INTO `loichucsinhnhat` VALUES (2,3,'Chúc mừng sinh nhật anh Lê Văn Cường! Chúc anh một năm mới nhiều niềm vui và thành công!','2025-05-22 00:00:00',1),(3,8,'Chúc mừng sinh nhật chị Bùi Thị Lan! Matcha Vibe gửi tặng chị voucher giảm giá 20% nhé!','2025-05-22 00:00:00',1),(4,5,'Chúc mừng sinh nhật anh Hoàng Văn Em! Chúc anh ngày mai thật rực rỡ!','2025-05-23 00:00:00',0),(5,3,'Chúc mừng sinh nhật Lê Văn Cường! Matcha Vibe chúc bạn một ngày thật vui vẻ và hạnh phúc!','2025-05-22 23:10:31',1),(6,18,'chuc anh suc khoe doi dao a','2025-05-22 23:14:51',1),(7,3,'Chúc mừng sinh nhật Lê Văn Cường! Matcha Vibe chúc bạn một ngày thật vui vẻ và hạnh phúc!','2025-05-23 00:06:31',1),(8,5,'Chúc mừng sinh nhật Hoàng Văn Em! Matcha Vibe chúc bạn một ngày thật vui vẻ và hạnh phúc!','2025-05-23 00:06:46',1);
/*!40000 ALTER TABLE `loichucsinhnhat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nguoidung`
--

DROP TABLE IF EXISTS `nguoidung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nguoidung` (
  `MaNguoiDung` int(11) NOT NULL AUTO_INCREMENT,
  `TenDangNhap` varchar(50) NOT NULL,
  `MatKhau` varchar(255) NOT NULL,
  `NgayTao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`MaNguoiDung`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nguoidung`
--

LOCK TABLES `nguoidung` WRITE;
/*!40000 ALTER TABLE `nguoidung` DISABLE KEYS */;
INSERT INTO `nguoidung` VALUES (3,'admin','admin123','2025-05-22 22:59:07'),(4,'staff1','staff123','2025-05-22 22:59:07'),(5,'staff2','staff456','2025-05-22 22:59:07');
/*!40000 ALTER TABLE `nguoidung` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orderdetails`
--

DROP TABLE IF EXISTS `orderdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orderdetails` (
  `MaChiTiet` int(11) NOT NULL AUTO_INCREMENT,
  `MaDonHang` int(11) DEFAULT NULL,
  `SanPham` varchar(100) DEFAULT NULL,
  `SoLuong` int(11) DEFAULT NULL,
  `DonGia` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`MaChiTiet`),
  KEY `MaDonHang` (`MaDonHang`),
  CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`MaDonHang`) REFERENCES `orders` (`MaDonHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orderdetails`
--

LOCK TABLES `orderdetails` WRITE;
/*!40000 ALTER TABLE `orderdetails` DISABLE KEYS */;
/*!40000 ALTER TABLE `orderdetails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `MaDonHang` int(11) NOT NULL AUTO_INCREMENT,
  `MaKhachHang` int(11) DEFAULT NULL,
  `TongTien` decimal(10,2) DEFAULT NULL,
  `NgayMua` datetime DEFAULT NULL,
  PRIMARY KEY (`MaDonHang`),
  KEY `MaKhachHang` (`MaKhachHang`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`MaKhachHang`) REFERENCES `khachhang` (`MaKhachHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchasehistory`
--

DROP TABLE IF EXISTS `purchasehistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchasehistory` (
  `MaMuaHang` int(11) NOT NULL AUTO_INCREMENT,
  `MaKhachHang` int(11) DEFAULT NULL,
  `SanPham` varchar(100) DEFAULT NULL,
  `SoLuong` int(11) DEFAULT NULL,
  `NgayMua` datetime DEFAULT NULL,
  PRIMARY KEY (`MaMuaHang`),
  KEY `MaKhachHang` (`MaKhachHang`),
  CONSTRAINT `purchasehistory_ibfk_1` FOREIGN KEY (`MaKhachHang`) REFERENCES `khachhang` (`MaKhachHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchasehistory`
--

LOCK TABLES `purchasehistory` WRITE;
/*!40000 ALTER TABLE `purchasehistory` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchasehistory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tinnhan`
--

DROP TABLE IF EXISTS `tinnhan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tinnhan` (
  `MaTinNhan` int(11) NOT NULL AUTO_INCREMENT,
  `MaKhachHang` int(11) DEFAULT NULL,
  `NoiDung` text NOT NULL,
  `ThoiGian` datetime DEFAULT current_timestamp(),
  `LaNhanVien` tinyint(1) DEFAULT 0,
  `LaSinhNhat` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`MaTinNhan`),
  KEY `MaKhachHang` (`MaKhachHang`),
  CONSTRAINT `tinnhan_ibfk_1` FOREIGN KEY (`MaKhachHang`) REFERENCES `khachhang` (`MaKhachHang`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tinnhan`
--

LOCK TABLES `tinnhan` WRITE;
/*!40000 ALTER TABLE `tinnhan` DISABLE KEYS */;
INSERT INTO `tinnhan` VALUES (4,3,'Chào anh Cường, đơn hàng Matcha Latte của anh đã được giao chưa?','2025-05-22 09:00:00',1,0),(5,3,'Rồi, cảm ơn shop! Hàng rất ngon.','2025-05-22 09:15:00',0,0),(6,4,'Chị Dung ơi, chương trình khuyến mãi tháng này đã bắt đầu, chị tham gia nhé!','2025-05-22 10:00:00',1,0),(7,4,'Ok, tôi sẽ xem. Có ưu đãi gì đặc biệt không?','2025-05-22 10:05:00',0,0),(18,2,'hello chi','2025-05-22 23:49:51',1,0),(19,2,'chi co khoe khong a','2025-05-22 23:50:34',1,0),(20,19,'Chao Anh Kien','2025-05-22 23:51:24',1,0),(21,19,'Em thich anh','2025-05-22 23:51:29',1,0);
/*!40000 ALTER TABLE `tinnhan` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-23  0:27:21
