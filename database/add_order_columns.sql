-- Thêm cột PhanTramGiamGia và TrangThaiThanhToan vào bảng Orders
ALTER TABLE Orders
ADD COLUMN PhanTramGiamGia DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Phần trăm giảm giá (0-100)',
ADD COLUMN TrangThaiThanhToan TINYINT(1) DEFAULT 0 COMMENT '0: Chưa thanh toán, 1: Đã thanh toán';

-- Cập nhật dữ liệu mặc định cho các đơn hàng hiện có
UPDATE Orders SET PhanTramGiamGia = 0, TrangThaiThanhToan = 0 WHERE PhanTramGiamGia IS NULL; 