-- =====================================================
-- HỆ THỐNG QUẢN LÝ KHO
-- Phiên bản: 1.0
-- Mục đích: Học tập và kiểm thử
-- =====================================================

USE quanlysanpham;

-- =====================================================
-- BẢNG PHIẾU NHẬP KHO
-- =====================================================
CREATE TABLE warehouse_import (
    import_id INT AUTO_INCREMENT PRIMARY KEY,
    import_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã phiếu nhập, định dạng PNYYYYMMDDXXX',
    product_id INT NOT NULL COMMENT 'Sản phẩm nhập',
    quantity INT NOT NULL COMMENT 'Số lượng nhập >= 1',
    import_date DATE NOT NULL COMMENT 'Ngày nhập hàng',
    import_by VARCHAR(50) NOT NULL COMMENT 'Người nhập hàng',
    note TEXT COMMENT 'Ghi chú phiếu nhập',
    status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending' COMMENT 'Trạng thái phiếu nhập',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Khóa ngoại
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG PHIẾU XUẤT KHO
-- =====================================================
CREATE TABLE warehouse_export (
    export_id INT AUTO_INCREMENT PRIMARY KEY,
    export_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã phiếu xuất, định dạng PXYYYYMMDDXXX',
    product_id INT NOT NULL COMMENT 'Sản phẩm xuất',
    quantity INT NOT NULL COMMENT 'Số lượng xuất >= 1',
    export_date DATE NOT NULL COMMENT 'Ngày xuất hàng',
    export_by VARCHAR(50) NOT NULL COMMENT 'Người xuất hàng',
    reason ENUM('Sale', 'Return', 'Damaged', 'Expired', 'Other') NOT NULL COMMENT 'Lý do xuất kho',
    note TEXT COMMENT 'Ghi chú phiếu xuất',
    status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending' COMMENT 'Trạng thái phiếu xuất',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Khóa ngoại
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG LỊCH SỬ THAO TÁC KHO
-- =====================================================
CREATE TABLE warehouse_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_code VARCHAR(20) NOT NULL COMMENT 'Mã phiếu nhập/xuất',
    action_type ENUM('Import', 'Export', 'Update', 'Cancel') NOT NULL COMMENT 'Loại thao tác',
    product_id INT NOT NULL COMMENT 'Sản phẩm liên quan',
    quantity INT NOT NULL COMMENT 'Số lượng thay đổi',
    old_stock INT COMMENT 'Tồn kho trước khi thay đổi',
    new_stock INT COMMENT 'Tồn kho sau khi thay đổi',
    action_by VARCHAR(50) NOT NULL COMMENT 'Người thực hiện',
    action_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    note TEXT COMMENT 'Ghi chú',
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TRIGGER CẬP NHẬT TỒN KHO
-- =====================================================
DELIMITER $$

-- Trigger kiểm tra ràng buộc khi thêm phiếu nhập
CREATE TRIGGER trg_validate_warehouse_import
BEFORE INSERT ON warehouse_import
FOR EACH ROW
BEGIN
    -- Kiểm tra định dạng mã phiếu nhập
    IF NEW.import_code NOT REGEXP '^PN[0-9]{8}[0-9]{3}$' THEN 
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Mã phiếu nhập không hợp lệ. Định dạng phải là PNYYYYMMDDXXX';
    END IF;
    
    -- Kiểm tra số lượng nhập
    IF NEW.quantity < 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Số lượng nhập phải lớn hơn 0';
    END IF;
    
    -- Kiểm tra ngày nhập không được vượt quá ngày hiện tại
    IF NEW.import_date > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ngày nhập không được vượt quá ngày hiện tại';
    END IF;
END$$

-- Trigger kiểm tra ràng buộc khi cập nhật phiếu nhập
CREATE TRIGGER trg_validate_warehouse_import_update
BEFORE UPDATE ON warehouse_import
FOR EACH ROW
BEGIN
    -- Kiểm tra định dạng mã phiếu nhập
    IF NEW.import_code NOT REGEXP '^PN[0-9]{8}[0-9]{3}$' THEN 
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Mã phiếu nhập không hợp lệ. Định dạng phải là PNYYYYMMDDXXX';
    END IF;
    
    -- Kiểm tra số lượng nhập
    IF NEW.quantity < 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Số lượng nhập phải lớn hơn 0';
    END IF;
    
    -- Kiểm tra ngày nhập không được vượt quá ngày hiện tại
    IF NEW.import_date > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ngày nhập không được vượt quá ngày hiện tại';
    END IF;
END$$

-- Trigger khi hoàn tất phiếu nhập
CREATE TRIGGER trg_warehouse_import_complete
AFTER UPDATE ON warehouse_import
FOR EACH ROW
BEGIN
    IF NEW.status = 'Completed' AND OLD.status = 'Pending' THEN
        -- Cập nhật số lượng tồn kho
        UPDATE products 
        SET stock_quantity = stock_quantity + NEW.quantity,
            updated_at = CURRENT_TIMESTAMP
        WHERE product_id = NEW.product_id;
        
        -- Ghi lịch sử
        INSERT INTO warehouse_history (
            reference_code, action_type, product_id, quantity, 
            old_stock, new_stock, action_by, note
        )
        SELECT 
            NEW.import_code, 'Import', NEW.product_id, NEW.quantity,
            stock_quantity - NEW.quantity, stock_quantity,
            NEW.import_by, CONCAT('Nhập kho: ', COALESCE(NEW.note, ''))
        FROM products 
        WHERE product_id = NEW.product_id;
    END IF;
END$$

-- Trigger kiểm tra ràng buộc khi thêm phiếu xuất
CREATE TRIGGER trg_validate_warehouse_export
BEFORE INSERT ON warehouse_export
FOR EACH ROW
BEGIN
    -- Kiểm tra định dạng mã phiếu xuất
    IF NEW.export_code NOT REGEXP '^PX[0-9]{8}[0-9]{3}$' THEN 
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Mã phiếu xuất không hợp lệ. Định dạng phải là PXYYYYMMDDXXX';
    END IF;
    
    -- Kiểm tra số lượng xuất
    IF NEW.quantity < 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Số lượng xuất phải lớn hơn 0';
    END IF;
    
    -- Kiểm tra ngày xuất không được vượt quá ngày hiện tại
    IF NEW.export_date > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ngày xuất không được vượt quá ngày hiện tại';
    END IF;
END$$

-- Trigger kiểm tra ràng buộc khi cập nhật phiếu xuất
CREATE TRIGGER trg_validate_warehouse_export_update
BEFORE UPDATE ON warehouse_export
FOR EACH ROW
BEGIN
    -- Kiểm tra định dạng mã phiếu xuất
    IF NEW.export_code NOT REGEXP '^PX[0-9]{8}[0-9]{3}$' THEN 
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Mã phiếu xuất không hợp lệ. Định dạng phải là PXYYYYMMDDXXX';
    END IF;
    
    -- Kiểm tra số lượng xuất
    IF NEW.quantity < 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Số lượng xuất phải lớn hơn 0';
    END IF;
    
    -- Kiểm tra ngày xuất không được vượt quá ngày hiện tại
    IF NEW.export_date > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ngày xuất không được vượt quá ngày hiện tại';
    END IF;
END$$

-- Trigger khi hoàn tất phiếu xuất
CREATE TRIGGER trg_warehouse_export_complete
AFTER UPDATE ON warehouse_export
FOR EACH ROW
BEGIN
    IF NEW.status = 'Completed' AND OLD.status = 'Pending' THEN
        -- Kiểm tra và cập nhật số lượng tồn kho
        UPDATE products 
        SET stock_quantity = stock_quantity - NEW.quantity,
            updated_at = CURRENT_TIMESTAMP
        WHERE product_id = NEW.product_id
        AND stock_quantity >= NEW.quantity;
        
        -- Ghi lịch sử
        INSERT INTO warehouse_history (
            reference_code, action_type, product_id, quantity,
            old_stock, new_stock, action_by, note
        )
        SELECT 
            NEW.export_code, 'Export', NEW.product_id, NEW.quantity,
            stock_quantity + NEW.quantity, stock_quantity,
            NEW.export_by, CONCAT('Xuất kho: ', NEW.reason, ' - ', COALESCE(NEW.note, ''))
        FROM products 
        WHERE product_id = NEW.product_id;
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- VIEW HỖ TRỢ QUẢN LÝ KHO
-- =====================================================

-- View thống kê nhập xuất theo sản phẩm
CREATE VIEW v_warehouse_product_summary AS
SELECT 
    p.product_id,
    p.product_code,
    p.product_name,
    p.stock_quantity,
    COALESCE(SUM(CASE WHEN wi.status = 'Completed' THEN wi.quantity ELSE 0 END), 0) as total_imported,
    COALESCE(SUM(CASE WHEN we.status = 'Completed' THEN we.quantity ELSE 0 END), 0) as total_exported,
    COALESCE(COUNT(DISTINCT wi.import_id), 0) as import_count,
    COALESCE(COUNT(DISTINCT we.export_id), 0) as export_count
FROM products p
LEFT JOIN warehouse_import wi ON p.product_id = wi.product_id
LEFT JOIN warehouse_export we ON p.product_id = we.product_id
GROUP BY p.product_id, p.product_code, p.product_name, p.stock_quantity;

-- View phiếu nhập/xuất đang chờ xử lý
CREATE VIEW v_warehouse_pending_documents AS
SELECT 
    'Import' as doc_type,
    wi.import_code as doc_code,
    p.product_code,
    p.product_name,
    wi.quantity,
    wi.import_date as doc_date,
    wi.import_by as created_by,
    wi.created_at
FROM warehouse_import wi
JOIN products p ON wi.product_id = p.product_id
WHERE wi.status = 'Pending'
UNION ALL
SELECT 
    'Export' as doc_type,
    we.export_code as doc_code,
    p.product_code,
    p.product_name,
    we.quantity,
    we.export_date as doc_date,
    we.export_by as created_by,
    we.created_at
FROM warehouse_export we
JOIN products p ON we.product_id = p.product_id
WHERE we.status = 'Pending'
ORDER BY created_at DESC;

-- =====================================================
-- DỮ LIỆU MẪU
-- =====================================================

-- Thêm phiếu nhập mẫu
INSERT INTO warehouse_import (import_code, product_id, quantity, import_date, import_by, note, status) VALUES
('PN20240101001', 1, 50, '2024-01-01', 'admin', 'Nhập hàng đầu năm', 'Completed'),
('PN20240115002', 2, 30, '2024-01-15', 'admin', 'Bổ sung tồn kho', 'Completed');

-- Thêm phiếu xuất mẫu
INSERT INTO warehouse_export (export_code, product_id, quantity, export_date, export_by, reason, note, status) VALUES
('PX20240105001', 1, 10, '2024-01-05', 'admin', 'Sale', 'Xuất bán hàng', 'Completed'),
('PX20240120002', 2, 5, '2024-01-20', 'admin', 'Return', 'Hàng lỗi', 'Completed');

-- =====================================================
-- KẾT THÚC SCRIPT
-- =====================================================