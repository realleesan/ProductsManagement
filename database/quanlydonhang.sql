-- =====================================================
-- HỆ THỐNG QUẢN LÝ ĐƠN HÀNG
-- Phiên bản: 1.0
-- Mục đích: Học tập và kiểm thử
-- =====================================================

USE quanlysanpham;

-- =====================================================
-- BẢNG KHÁCH HÀNG
-- =====================================================
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã khách hàng, định dạng KHYYYYXXX',
    fullname VARCHAR(100) NOT NULL COMMENT 'Họ tên khách hàng',
    phone VARCHAR(15) NOT NULL COMMENT 'Số điện thoại',
    email VARCHAR(100) COMMENT 'Email khách hàng',
    address TEXT COMMENT 'Địa chỉ giao hàng',
    status ENUM('Active', 'Blocked') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Ràng buộc
    CONSTRAINT chk_customer_code_format CHECK (customer_code REGEXP '^KH[0-9]{7}$'),
    CONSTRAINT chk_customer_phone CHECK (phone REGEXP '^[0-9]{10,11}$'),
    CONSTRAINT chk_customer_email CHECK (email IS NULL OR email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG ĐƠN HÀNG
-- =====================================================
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã đơn hàng, định dạng DHYYYYMMDDXXX',
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Khóa ngoại và ràng buộc
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE RESTRICT,
    CONSTRAINT chk_order_code_format CHECK (order_code REGEXP '^DH[0-9]{8}[0-9]{3}$'),
    CONSTRAINT chk_order_amount CHECK (total_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG CHI TIẾT ĐƠN HÀNG
-- =====================================================
CREATE TABLE order_details (
    detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL COMMENT 'Số lượng mua >= 1',
    unit_price DECIMAL(12,2) NOT NULL COMMENT 'Giá bán tại thời điểm đặt hàng',
    total_price DECIMAL(12,2) NOT NULL COMMENT 'Thành tiền = số lượng * đơn giá',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Khóa ngoại và ràng buộc
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT,
    CONSTRAINT chk_order_detail_quantity CHECK (quantity >= 1),
    CONSTRAINT chk_order_detail_price CHECK (unit_price > 0 AND total_price > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG LỊCH SỬ ĐƠN HÀNG
-- =====================================================
CREATE TABLE order_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    action_type ENUM('Created', 'Confirmed', 'Processing', 'Shipping', 'Completed', 'Cancelled') NOT NULL,
    old_status VARCHAR(50) COMMENT 'Trạng thái cũ',
    new_status VARCHAR(50) COMMENT 'Trạng thái mới',
    action_by VARCHAR(50) NOT NULL COMMENT 'Người thực hiện',
    action_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    note TEXT COMMENT 'Ghi chú',
    
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TRIGGER QUẢN LÝ ĐƠN HÀNG
-- =====================================================
DELIMITER $$

-- Trigger cập nhật tổng tiền đơn hàng khi thêm/sửa chi tiết
CREATE TRIGGER trg_update_order_total
AFTER INSERT ON order_details
FOR EACH ROW
BEGIN
    UPDATE orders 
    SET total_amount = (
        SELECT SUM(total_price) 
        FROM order_details 
        WHERE order_id = NEW.order_id
    )
    WHERE order_id = NEW.order_id;
END$$

-- Trigger kiểm tra và tạo phiếu xuất kho khi đơn hàng hoàn tất
CREATE TRIGGER trg_create_export_on_order_complete
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    DECLARE export_code VARCHAR(20);
    
    IF NEW.status = 'Hoàn tất' AND OLD.status != 'Hoàn tất' THEN
        -- Tạo mã phiếu xuất
        SET export_code = CONCAT('PX', DATE_FORMAT(NOW(), '%Y%m%d'),
            LPAD((SELECT COUNT(*) + 1 FROM warehouse_export 
                  WHERE DATE(created_at) = CURDATE()), 3, '0'));
        
        -- Tạo phiếu xuất cho từng sản phẩm trong đơn
        INSERT INTO warehouse_export (
            export_code, product_id, quantity, export_date, 
            export_by, reason, note, status
        )
        SELECT 
            export_code,
            od.product_id,
            od.quantity,
            CURDATE(),
            NEW.completed_by,
            'Sale',
            CONCAT('Xuất cho đơn hàng ', NEW.order_code),
            'Completed'
        FROM order_details od
        WHERE od.order_id = NEW.order_id;
    END IF;
END$$

-- Trigger ghi lịch sử khi thay đổi trạng thái đơn hàng
CREATE TRIGGER trg_order_status_history
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status != OLD.status THEN
        INSERT INTO order_history (
            order_id, action_type, old_status, new_status, 
            action_by, note
        )
        VALUES (
            NEW.order_id,
            CASE NEW.status
                WHEN 'Chờ xác nhận' THEN 'Created'
                WHEN 'Đang xử lý' THEN 'Processing'
                WHEN 'Đang giao' THEN 'Shipping'
                WHEN 'Hoàn tất' THEN 'Completed'
                WHEN 'Đã hủy' THEN 'Cancelled'
                ELSE 'Updated'
            END,
            OLD.status,
            NEW.status,
            COALESCE(
                CASE NEW.status
                    WHEN 'Hoàn tất' THEN NEW.completed_by
                    WHEN 'Đã hủy' THEN NEW.cancelled_by
                    ELSE NEW.confirmed_by
                END,
                'system'
            ),
            CASE
                WHEN NEW.status = 'Đã hủy' THEN NEW.cancel_reason
                ELSE NULL
            END
        );
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- VIEW HỖ TRỢ QUẢN LÝ ĐƠN HÀNG
-- =====================================================

-- View tổng hợp đơn hàng
CREATE VIEW v_order_summary AS
SELECT 
    o.order_id,
    o.order_code,
    c.customer_code,
    c.fullname as customer_name,
    o.order_date,
    o.total_amount,
    o.status,
    o.payment_method,
    COUNT(od.detail_id) as total_items,
    SUM(od.quantity) as total_quantity
FROM orders o
JOIN customers c ON o.customer_id = c.customer_id
JOIN order_details od ON o.order_id = od.order_id
GROUP BY o.order_id;

-- View chi tiết đơn hàng
CREATE VIEW v_order_details AS
SELECT 
    o.order_code,
    o.order_date,
    c.customer_code,
    c.fullname as customer_name,
    p.product_code,
    p.product_name,
    od.quantity,
    od.unit_price,
    od.total_price,
    o.status,
    o.payment_method
FROM orders o
JOIN customers c ON o.customer_id = c.customer_id
JOIN order_details od ON o.order_id = od.order_id
JOIN products p ON od.product_id = p.product_id;

-- =====================================================
-- DỮ LIỆU MẪU
-- =====================================================

-- Thêm khách hàng mẫu
INSERT INTO customers (customer_code, fullname, phone, email, address, status) VALUES
('KH2024001', 'Nguyễn Văn A', '0901234567', 'nguyenvana@email.com', 'Hà Nội', 'Active'),
('KH2024002', 'Trần Thị B', '0912345678', 'tranthib@email.com', 'TP.HCM', 'Active');

-- Thêm đơn hàng mẫu
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at) VALUES
('DH20240101001', 1, '2024-01-01 10:00:00', 'COD', 'Hà Nội', 'Đang xử lý', 'admin', '2024-01-01 10:30:00'),
('DH20240102001', 2, '2024-01-02 14:00:00', 'Bank Transfer', 'TP.HCM', 'Chờ xác nhận', NULL, NULL);

-- Thêm chi tiết đơn hàng mẫu
INSERT INTO order_details (order_id, product_id, quantity, unit_price, total_price) VALUES
(1, 1, 2, 350000, 700000),
(1, 2, 1, 650000, 650000),
(2, 3, 3, 180000, 540000);

-- =====================================================
-- KẾT THÚC SCRIPT
-- =====================================================