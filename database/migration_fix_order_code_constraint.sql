-- Migration: Sửa constraint định dạng mã đơn hàng từ DH + 11 chữ số sang DH + 7 chữ số
-- Ngày tạo: 2024
-- Mô tả: Sửa constraint chk_order_code_format để khớp với validation trong code
-- Tương thích với MariaDB và MySQL

-- BƯỚC 1: Xóa hoặc cập nhật dữ liệu cũ không khớp với định dạng mới
-- Nếu bạn muốn giữ lại dữ liệu cũ, hãy cập nhật mã đơn hàng sang định dạng mới
-- Nếu không cần dữ liệu cũ, có thể xóa chúng

-- Tùy chọn 1: Xóa các đơn hàng có mã không khớp định dạng mới (DH + 7 chữ số)
DELETE FROM order_details WHERE order_id IN (
    SELECT order_id FROM orders WHERE order_code NOT REGEXP '^DH[0-9]{7}$'
);
DELETE FROM orders WHERE order_code NOT REGEXP '^DH[0-9]{7}$';

-- Tùy chọn 2: Cập nhật mã đơn hàng cũ sang định dạng mới (DH + 7 chữ số)
-- Nếu bạn muốn giữ lại dữ liệu cũ, hãy bỏ comment các dòng dưới và comment Tùy chọn 1
-- UPDATE orders SET order_code = CONCAT('DH', LPAD(order_id, 7, '0')) 
-- WHERE order_code NOT REGEXP '^DH[0-9]{7}$';

-- BƯỚC 2: Xóa constraint cũ và thêm constraint mới
-- MariaDB/MySQL không hỗ trợ DROP CHECK trực tiếp, cần tạo lại bảng

-- Tắt kiểm tra foreign key tạm thời
SET FOREIGN_KEY_CHECKS = 0;

-- Tạo bảng tạm với cấu trúc giống bảng cũ nhưng không có constraint cũ
CREATE TABLE orders_new (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã đơn hàng, định dạng DH + 7 chữ số (ví dụ: DH1234567)',
    customer_id INT NOT NULL COMMENT 'Khách hàng đặt hàng',
    order_date DATETIME NOT NULL COMMENT 'Ngày đặt hàng',
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Tổng tiền đơn hàng',
    status ENUM('Chờ xác nhận', 'Đang xử lý', 'Đang giao', 'Hoàn tất', 'Đã thanh toán', 'Đã hủy') DEFAULT 'Chờ xác nhận',
    payment_method ENUM('COD', 'Bank Transfer', 'E-Wallet') NOT NULL DEFAULT 'COD',
    shipping_address TEXT NOT NULL COMMENT 'Địa chỉ giao hàng',
    shipping_note TEXT COMMENT 'Ghi chú giao hàng',
    cancel_reason VARCHAR(200) COMMENT 'Lý do hủy đơn',
    confirmed_by VARCHAR(50) COMMENT 'Người xác nhận đơn',
    confirmed_at DATETIME COMMENT 'Thời điểm xác nhận',
    completed_by VARCHAR(50) COMMENT 'Người hoàn tất đơn',
    completed_at DATETIME COMMENT 'Thời điểm hoàn tất',
    cancelled_by VARCHAR(50) COMMENT 'Người hủy đơn',
    cancelled_at DATETIME COMMENT 'Thời điểm hủy',
    paid_by VARCHAR(50) COMMENT 'Người thanh toán đơn',
    paid_at DATETIME COMMENT 'Thời điểm thanh toán',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE RESTRICT,
    CONSTRAINT chk_order_code_format CHECK (order_code REGEXP '^DH[0-9]{7}$'),
    CONSTRAINT chk_order_amount CHECK (total_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copy dữ liệu từ bảng cũ sang bảng mới
INSERT INTO orders_new SELECT * FROM orders;

-- Xóa bảng cũ
DROP TABLE orders;

-- Đổi tên bảng mới thành tên cũ
RENAME TABLE orders_new TO orders;

-- Bật lại kiểm tra foreign key
SET FOREIGN_KEY_CHECKS = 1;

-- Hoàn thành!
-- Bây giờ bạn có thể tạo đơn hàng mới với mã định dạng DH + 7 chữ số (ví dụ: DH1234567)

