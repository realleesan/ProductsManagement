-- =====================================================
-- HỆ THỐNG QUẢN LÝ SẢN PHẨM MỸ PHẨM
-- Phiên bản: 1.0
-- Mục đích: Học tập và kiểm thử
-- =====================================================

-- Tạo database
CREATE DATABASE IF NOT EXISTS quanlysanpham 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE quanlysanpham;

-- =====================================================
-- BẢNG DANH MỤC SẢN PHẨM
-- =====================================================
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã danh mục duy nhất',
    category_name VARCHAR(100) NOT NULL COMMENT 'Tên danh mục',
    description TEXT COMMENT 'Mô tả danh mục',
    status ENUM('Active', 'Disabled') DEFAULT 'Active' COMMENT 'Trạng thái danh mục',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Ràng buộc
    CONSTRAINT chk_category_name_length CHECK (CHAR_LENGTH(category_name) BETWEEN 3 AND 100),
    CONSTRAINT chk_category_code_format CHECK (category_code REGEXP '^DM[A-Z0-9]+$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã sản phẩm duy nhất, định dạng SPXX...X',
    product_name VARCHAR(150) NOT NULL COMMENT 'Tên sản phẩm, 5-150 ký tự',
    description VARCHAR(500) COMMENT 'Mô tả sản phẩm, tối đa 500 ký tự',
    price DECIMAL(12,2) NOT NULL COMMENT 'Giá bán, từ 1.000 đến 1.000.000.000 VNĐ',
    stock_quantity INT NOT NULL DEFAULT 0 COMMENT 'Số lượng tồn kho, >= 0',
    category_id INT NOT NULL COMMENT 'Danh mục sản phẩm',
    manufacture_date DATE NOT NULL COMMENT 'Ngày sản xuất',
    expiry_date DATE NOT NULL COMMENT 'Hạn sử dụng, phải sau ngày sản xuất >= 30 ngày',
    status ENUM('Active', 'Disabled', 'Out of stock', 'Expired') DEFAULT 'Active' COMMENT 'Trạng thái sản phẩm',
    main_image VARCHAR(255) COMMENT 'Ảnh chính của sản phẩm',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(50) COMMENT 'Người tạo',
    updated_by VARCHAR(50) COMMENT 'Người cập nhật cuối',
    
    -- Khóa ngoại
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    -- Ràng buộc theo tài liệu
    CONSTRAINT chk_product_code_format CHECK (product_code REGEXP '^SP[A-Z0-9]+$'),
    CONSTRAINT chk_product_name_length CHECK (CHAR_LENGTH(product_name) BETWEEN 5 AND 150),
    CONSTRAINT chk_price_range CHECK (price >= 1000 AND price <= 1000000000),
    CONSTRAINT chk_stock_quantity CHECK (stock_quantity >= 0),
    CONSTRAINT chk_expiry_after_manufacture CHECK (DATEDIFF(expiry_date, manufacture_date) >= 30),
    CONSTRAINT chk_description_length CHECK (description IS NULL OR CHAR_LENGTH(description) <= 500)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG ẢNH PHỤ CỦA SẢN PHẨM
-- =====================================================
CREATE TABLE product_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order TINYINT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG LỊCH SỬ THAO TÁC SẢN PHẨM
-- =====================================================
CREATE TABLE product_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    action_type ENUM('CREATE', 'UPDATE', 'DELETE', 'STATUS_CHANGE') NOT NULL COMMENT 'Loại thao tác',
    old_value TEXT COMMENT 'Giá trị cũ (JSON)',
    new_value TEXT COMMENT 'Giá trị mới (JSON)',
    action_by VARCHAR(50) COMMENT 'Người thực hiện',
    action_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    note VARCHAR(200) COMMENT 'Ghi chú',
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TRIGGER TỰ ĐỘNG CẬP NHẬT TRẠNG THÁI SẢN PHẨM
-- =====================================================

-- Trigger khi thêm sản phẩm mới
DELIMITER $$
CREATE TRIGGER trg_product_status_on_insert
BEFORE INSERT ON products
FOR EACH ROW
BEGIN
    -- Kiểm tra danh mục phải Active
    DECLARE cat_status VARCHAR(20);
    SELECT status INTO cat_status FROM categories WHERE category_id = NEW.category_id;
    
    IF cat_status = 'Disabled' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Không thể thêm sản phẩm vào danh mục Disabled';
    END IF;
    
    -- Tự động set trạng thái dựa trên tồn kho và hạn sử dụng
    IF NEW.stock_quantity = 0 THEN
        SET NEW.status = 'Out of stock';
    ELSEIF NEW.expiry_date < CURDATE() THEN
        SET NEW.status = 'Expired';
    ELSEIF NEW.status IS NULL THEN
        SET NEW.status = 'Active';
    END IF;
END$$

-- Trigger khi cập nhật sản phẩm
CREATE TRIGGER trg_product_status_on_update
BEFORE UPDATE ON products
FOR EACH ROW
BEGIN
    -- Kiểm tra danh mục phải Active
    DECLARE cat_status VARCHAR(20);
    SELECT status INTO cat_status FROM categories WHERE category_id = NEW.category_id;
    
    IF cat_status = 'Disabled' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Không thể cập nhật sản phẩm với danh mục Disabled';
    END IF;
    
    -- Tự động cập nhật tồn kho dựa trên trạng thái (chạy trước)
    IF NEW.status = 'Out of stock' AND OLD.status != 'Out of stock' AND NEW.stock_quantity > 0 THEN
        SET NEW.stock_quantity = 0;
    ELSEIF NEW.status = 'Active' AND OLD.status = 'Out of stock' AND NEW.stock_quantity = 0 THEN
        SET NEW.stock_quantity = 1; -- Đặt mặc định là 1 khi chuyển từ Out of stock về Active
    END IF;
    
    -- Tự động cập nhật trạng thái dựa trên tồn kho (chạy sau)
    -- Chỉ tự động chuyển sang Expired khi hết hạn thực sự
    IF (NEW.expiry_date < CURDATE() AND OLD.expiry_date >= CURDATE()) 
       OR (NEW.expiry_date < CURDATE() AND NEW.status != 'Disabled' AND NEW.status != 'Expired') THEN
        SET NEW.status = 'Expired';
    ELSEIF NEW.stock_quantity = 0 AND NEW.status NOT IN ('Out of stock', 'Disabled', 'Expired') THEN
        SET NEW.status = 'Out of stock';
    ELSEIF OLD.status IN ('Out of stock', 'Expired') 
        AND NEW.stock_quantity > 0 
        AND NEW.expiry_date >= CURDATE() 
        AND NEW.status != 'Disabled' THEN
        SET NEW.status = 'Active';
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- DỮ LIỆU MẪU
-- =====================================================

-- Thêm danh mục mẫu
INSERT INTO categories (category_code, category_name, description, status) VALUES
('DMSKC', 'Sản phẩm chăm sóc da', 'Các sản phẩm chăm sóc da mặt và cơ thể', 'Active'),
('DMTRANG', 'Sản phẩm trang điểm', 'Các sản phẩm trang điểm như son, phấn, mascara', 'Active'),
('DMDUONG', 'Sản phẩm dưỡng tóc', 'Các sản phẩm chăm sóc và dưỡng tóc', 'Active'),
('DMNUOCHOA', 'Nước hoa', 'Các loại nước hoa cao cấp', 'Active'),
('DMCN', 'Chăm sóc cơ thể', 'Sữa tắm, kem dưỡng thể', 'Active');

-- Thêm sản phẩm mẫu
INSERT INTO products (product_code, product_name, description, price, stock_quantity, category_id, manufacture_date, expiry_date, status, main_image, created_by) VALUES
('SP001', 'Kem dưỡng ẩm Neutrogena Hydro Boost', 'Kem dưỡng ẩm chuyên sâu với công nghệ Hydro Boost, giúp da mềm mại và căng mọng', 350000, 50, 1, '2024-01-15', '2026-01-15', 'Active', 'sample_neutrogena_main.jpg', 'admin'),
('SP002', 'Son môi MAC Ruby Woo', 'Son môi lì màu đỏ ruby kinh điển, lâu trôi và bền màu', 650000, 30, 2, '2024-02-01', '2026-02-01', 'Active', 'sample_mac_main.jpg', 'admin'),
('SP003', 'Dầu gội Tresemme Keratin Smooth', 'Dầu gội phục hồi tóc hư tổn với keratin, giúp tóc mượt mà và bóng khỏe', 180000, 100, 3, '2024-03-10', '2026-03-10', 'Active', 'sample_tresemme_main.jpg', 'admin'),
('SP004', 'Nước hoa Chanel No.5 EDP 100ml', 'Nước hoa nữ huyền thoại với hương thơm quyến rũ và sang trọng', 3500000, 15, 4, '2024-01-20', '2027-01-20', 'Active', 'sample_chanel_main.jpg', 'admin'),
('SP005', 'Sữa tắm Dove Deep Moisture', 'Sữa tắm dưỡng ẩm sâu với 1/4 kem dưỡng ẩm, cho làn da mềm mại', 120000, 200, 5, '2024-04-01', '2026-04-01', 'Active', 'sample_dove_main.jpg', 'admin'),
('SP006', 'Serum Vitamin C The Ordinary', 'Serum vitamin C 23% giúp làm sáng da và mờ thâm nám', 280000, 0, 1, '2024-02-15', '2025-08-15', 'Expired', 'sample_ordinary_main.jpg', 'admin'),
('SP007', 'Phấn phủ Innisfree No Sebum Mineral', 'Phấn phủ kiềm dầu hiệu quả, giữ lớp makeup lâu trôi', 195000, 80, 2, '2024-03-20', '2026-03-20', 'Active', 'sample_innisfree_main.jpg', 'admin'),
('SP008', 'Mặt nạ ngủ Laneige Water Sleeping Mask', 'Mặt nạ ngủ cấp ẩm chuyên sâu, giúp da tươi sáng vào buổi sáng', 520000, 45, 1, '2024-01-10', '2026-01-10', 'Active', 'sample_laneige_main.jpg', 'admin'),
('SP009', 'Kem chống nắng La Roche-Posay SPF50+', 'Kem chống nắng phổ rộng, phù hợp cho da nhạy cảm', 420000, 60, 1, '2023-12-01', '2025-06-01', 'Active', 'sample_laroche_main.jpg', 'admin'),
('SP010', 'Xịt khoáng Avene Thermal Spring Water', 'Xịt khoáng làm dịu và cân bằng da, phù hợp mọi loại da', 250000, 120, 1, '2024-02-20', '2027-02-20', 'Active', 'sample_avene_main.jpg', 'admin');

INSERT INTO product_images (product_id, image_path, sort_order) VALUES
(1, 'sample_neutrogena_1.jpg', 1),
(1, 'sample_neutrogena_2.jpg', 2),
(2, 'sample_mac_1.jpg', 1),
(3, 'sample_tresemme_1.jpg', 1),
(4, 'sample_chanel_1.jpg', 1),
(7, 'sample_innisfree_1.jpg', 1);

-- =====================================================
-- INDEX ĐỂ TỐI ƯU HIỆU NĂNG
-- =====================================================
CREATE INDEX idx_product_status ON products(status);
CREATE INDEX idx_product_category ON products(category_id);
CREATE INDEX idx_product_expiry ON products(expiry_date);
CREATE INDEX idx_product_name ON products(product_name);
CREATE INDEX idx_category_status ON categories(status);

-- =====================================================
-- VIEW HỖ TRỢ TRUY VẤN
-- =====================================================

-- View sản phẩm có thể bán (Active và chưa hết hạn)
CREATE VIEW v_products_available AS
SELECT 
    p.*,
    c.category_name,
    c.status as category_status
FROM products p
INNER JOIN categories c ON p.category_id = c.category_id
WHERE p.status = 'Active' 
  AND p.expiry_date >= CURDATE()
  AND c.status = 'Active';

-- View sản phẩm sắp hết hạn (còn dưới 60 ngày)
CREATE VIEW v_products_expiring_soon AS
SELECT 
    p.*,
    c.category_name,
    DATEDIFF(p.expiry_date, CURDATE()) as days_until_expiry
FROM products p
INNER JOIN categories c ON p.category_id = c.category_id
WHERE p.expiry_date >= CURDATE() 
  AND DATEDIFF(p.expiry_date, CURDATE()) <= 60
  AND p.status != 'Expired'
ORDER BY days_until_expiry ASC;

-- View sản phẩm tồn kho thấp (dưới 20 sản phẩm)
CREATE VIEW v_products_low_stock AS
SELECT 
    p.*,
    c.category_name
FROM products p
INNER JOIN categories c ON p.category_id = c.category_id
WHERE p.stock_quantity > 0 
  AND p.stock_quantity < 20
  AND p.status = 'Active'
ORDER BY p.stock_quantity ASC;

-- =====================================================
-- STORED PROCEDURE HỖ TRỢ
-- =====================================================

-- Procedure cập nhật trạng thái sản phẩm hết hạn (chạy định kỳ)
DELIMITER $$
CREATE PROCEDURE sp_update_expired_products()
BEGIN
    UPDATE products 
    SET status = 'Expired',
        updated_at = CURRENT_TIMESTAMP
    WHERE expiry_date < CURDATE() 
      AND status != 'Expired';
    
    SELECT ROW_COUNT() as updated_count;
END$$

-- Procedure thống kê sản phẩm theo danh mục
CREATE PROCEDURE sp_product_statistics_by_category()
BEGIN
    SELECT 
        c.category_name,
        COUNT(p.product_id) as total_products,
        SUM(CASE WHEN p.status = 'Active' THEN 1 ELSE 0 END) as active_products,
        SUM(CASE WHEN p.status = 'Out of stock' THEN 1 ELSE 0 END) as out_of_stock,
        SUM(CASE WHEN p.status = 'Expired' THEN 1 ELSE 0 END) as expired_products,
        SUM(p.stock_quantity) as total_stock,
        AVG(p.price) as avg_price
    FROM categories c
    LEFT JOIN products p ON c.category_id = p.category_id
    WHERE c.status = 'Active'
    GROUP BY c.category_id, c.category_name
    ORDER BY total_products DESC;
END$$

DELIMITER ;

-- =====================================================
-- QUYỀN TRUY CẬP (Tùy chọn)
-- =====================================================
-- Tạo user cho ứng dụng (nếu cần)
-- CREATE USER 'quanlysanpham_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON quanlysanpham.* TO 'quanlysanpham_user'@'localhost';
-- FLUSH PRIVILEGES;

-- =====================================================
-- KẾT THÚC SCRIPT
-- =====================================================

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
    
    -- Khóa ngoại và ràng buộc
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE RESTRICT,
    CONSTRAINT chk_order_code_format CHECK (order_code REGEXP '^DH[0-9]{7}$'),
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
('DH0000001', 1, '2024-01-01 10:00:00', 'COD', 'Hà Nội', 'Đang xử lý', 'admin', '2024-01-01 10:30:00'),
('DH0000002', 2, '2024-01-02 14:00:00', 'Bank Transfer', 'TP.HCM', 'Chờ xác nhận', NULL, NULL);

-- Thêm chi tiết đơn hàng mẫu
INSERT INTO order_details (order_id, product_id, quantity, unit_price, total_price) VALUES
(1, 1, 2, 350000, 700000),
(1, 2, 1, 650000, 650000),
(2, 3, 3, 180000, 540000);

-- =====================================================
-- KẾT THÚC SCRIPT
-- =====================================================

-- =====================================================
-- DỮ LIỆU MẪU BỔ SUNG CHO HỆ THỐNG QUẢN LÝ SẢN PHẨM
-- Phiên bản: 1.0
-- Mục đích: Bổ sung dữ liệu mẫu để test và demo
-- =====================================================

USE quanlysanpham;

-- =====================================================
-- 1. BỔ SUNG 5 DANH MỤC MẪU VÀO CATEGORIES
-- =====================================================

INSERT INTO categories (category_code, category_name, description, status) VALUES
('DMMASK', 'Mặt nạ', 'Các loại mặt nạ chăm sóc da mặt', 'Active'),
('DMLIP', 'Son môi', 'Các loại son môi và sản phẩm trang điểm môi', 'Active'),
('DMHAIR', 'Chăm sóc tóc', 'Dầu gội, dầu xả, serum tóc và các sản phẩm chăm sóc tóc', 'Active'),
('DMBATH', 'Sản phẩm tắm', 'Sữa tắm, gel tắm, muối tắm và các sản phẩm vệ sinh cơ thể', 'Active'),
('DMCREAM', 'Kem dưỡng', 'Các loại kem dưỡng da mặt và cơ thể', 'Active');

-- =====================================================
-- 2. BỔ SUNG 10 PHIẾU KHO MẪU (5 NHẬP + 5 XUẤT)
-- =====================================================

-- 5 phiếu nhập kho
INSERT INTO warehouse_import (import_code, product_id, quantity, import_date, import_by, note, status) VALUES
('PN20240301001', 1, 25, '2024-03-01', 'tester', 'Nhập bổ sung kem dưỡng ẩm', 'Completed'),
('PN20240302002', 2, 15, '2024-03-02', 'tester', 'Nhập son môi MAC mới', 'Completed'),
('PN20240303003', 3, 50, '2024-03-03', 'tester', 'Nhập dầu gội Tresemme', 'Completed'),
('PN20240304004', 4, 8, '2024-03-04', 'tester', 'Nhập nước hoa Chanel', 'Completed'),
('PN20240305005', 5, 100, '2024-03-05', 'tester', 'Nhập sữa tắm Dove', 'Completed');

-- 5 phiếu xuất kho
INSERT INTO warehouse_export (export_code, product_id, quantity, export_date, export_by, reason, note, status) VALUES
('PX20240310001', 1, 5, '2024-03-10', 'tester', 'Sale', 'Xuất bán lẻ', 'Completed'),
('PX20240311002', 2, 3, '2024-03-11', 'tester', 'Sale', 'Xuất cho khách VIP', 'Completed'),
('PX20240312003', 3, 20, '2024-03-12', 'tester', 'Sale', 'Xuất bán sỉ', 'Completed'),
('PX20240313004', 4, 2, '2024-03-13', 'tester', 'Return', 'Hàng lỗi cần trả', 'Completed'),
('PX20240314005', 5, 30, '2024-03-14', 'tester', 'Sale', 'Xuất cho đại lý', 'Completed');

-- =====================================================
-- BỔ SUNG DỮ LIỆU CHO WAREHOUSE_HISTORY
-- =====================================================

-- Lịch sử cho 5 phiếu nhập kho
INSERT INTO warehouse_history (reference_code, action_type, product_id, quantity, old_stock, new_stock, action_by, note) VALUES
('PN20240301001', 'Import', 1, 25, 50, 75, 'tester', 'Nhập bổ sung kem dưỡng ẩm'),
('PN20240302002', 'Import', 2, 15, 30, 45, 'tester', 'Nhập son môi MAC mới'),
('PN20240303003', 'Import', 3, 50, 100, 150, 'tester', 'Nhập dầu gội Tresemme'),
('PN20240304004', 'Import', 4, 8, 15, 23, 'tester', 'Nhập nước hoa Chanel'),
('PN20240305005', 'Import', 5, 100, 200, 300, 'tester', 'Nhập sữa tắm Dove');

-- Lịch sử cho 5 phiếu xuất kho
INSERT INTO warehouse_history (reference_code, action_type, product_id, quantity, old_stock, new_stock, action_by, note) VALUES
('PX20240310001', 'Export', 1, 5, 75, 70, 'tester', 'Xuất bán lẻ'),
('PX20240311002', 'Export', 2, 3, 45, 42, 'tester', 'Xuất cho khách VIP'),
('PX20240312003', 'Export', 3, 20, 150, 130, 'tester', 'Xuất bán sỉ'),
('PX20240313004', 'Export', 4, 2, 23, 21, 'tester', 'Hàng lỗi cần trả'),
('PX20240314005', 'Export', 5, 30, 300, 270, 'tester', 'Xuất cho đại lý');

-- =====================================================
-- 3. BỔ SUNG 8 KHÁCH HÀNG MẪU
-- =====================================================

INSERT INTO customers (customer_code, fullname, phone, email, address, status) VALUES
('KH2024003', 'Lê Minh Tuấn', '0987654321', 'leminhtuan@gmail.com', 'Số 123 Đường Lê Lợi, Quận 1, TP. Đà Nẵng', 'Active'),
('KH2024004', 'Phạm Thị Hương', '0912345678', 'phamthihuong@yahoo.com', 'Số 456 Đường Nguyễn Huệ, Quận Ninh Kiều, TP. Cần Thơ', 'Active'),
('KH2024005', 'Hoàng Văn Đức', '0923456789', 'hoangvanduc@outlook.com', 'Số 789 Đường Trần Hưng Đạo, Quận Hải Châu, TP. Đà Nẵng', 'Active'),
('KH2024006', 'Nguyễn Thị Mai', '0934567890', 'nguyenthimai@gmail.com', 'Số 321 Đường Lê Duẩn, Quận Thanh Khê, TP. Đà Nẵng', 'Active'),
('KH2024007', 'Trần Văn Nam', '0945678901', 'tranvannam@yahoo.com', 'Số 654 Đường Nguyễn Văn Linh, Quận 7, TP. Hồ Chí Minh', 'Active'),
('KH2024008', 'Lê Thị Lan', '0956789012', 'lethilan@gmail.com', 'Số 987 Đường Lý Thường Kiệt, Quận Hoàn Kiếm, TP. Hà Nội', 'Active'),
('KH2024009', 'Phạm Văn Hùng', '0967890123', 'phamvanhung@outlook.com', 'Số 147 Đường Võ Văn Tần, Quận 3, TP. Hồ Chí Minh', 'Active'),
('KH2024010', 'Nguyễn Thị Hoa', '0978901234', 'nguyenthihoa@gmail.com', 'Số 258 Đường Lê Thánh Tôn, Quận 1, TP. Hồ Chí Minh', 'Active');

-- =====================================================
-- 4. BỔ SUNG 8 ĐƠN HÀNG MẪU VỚI CÁC TRẠNG THÁI KHÁC NHAU
-- =====================================================

-- Đơn hàng 1: Chờ xác nhận
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, shipping_note) VALUES
('DH0000003', 3, '2024-02-01 09:30:00', 'COD', 'Số 123 Đường Lê Lợi, Quận 1, TP. Đà Nẵng', 'Chờ xác nhận', 'Giao hàng trong giờ hành chính');

-- Đơn hàng 2: Đang xử lý
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, shipping_note) VALUES
('DH0000004', 4, '2024-02-02 14:15:00', 'Bank Transfer', 'Số 456 Đường Nguyễn Huệ, Quận Ninh Kiều, TP. Cần Thơ', 'Đang xử lý', 'admin', '2024-02-02 15:00:00', 'Kiểm tra kỹ trước khi đóng gói');

-- Đơn hàng 3: Đang giao
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, shipping_note) VALUES
('DH0000005', 5, '2024-02-03 11:20:00', 'E-Wallet', 'Số 789 Đường Trần Hưng Đạo, Quận Hải Châu, TP. Đà Nẵng', 'Đang giao', 'admin', '2024-02-03 12:00:00', 'Giao hàng nhanh');

-- Đơn hàng 4: Hoàn tất
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, completed_by, completed_at, shipping_note) VALUES
('DH0000006', 6, '2024-02-04 16:45:00', 'COD', 'Số 321 Đường Lê Duẩn, Quận Thanh Khê, TP. Đà Nẵng', 'Hoàn tất', 'admin', '2024-02-04 17:00:00', 'staff1', '2024-02-05 10:30:00', 'Đã giao thành công');

-- Đơn hàng 5: Đã thanh toán
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, completed_by, completed_at, paid_by, paid_at, shipping_note) VALUES
('DH0000007', 7, '2024-02-05 13:10:00', 'Bank Transfer', 'Số 654 Đường Nguyễn Văn Linh, Quận 7, TP. Hồ Chí Minh', 'Đã thanh toán', 'admin', '2024-02-05 13:30:00', 'staff2', '2024-02-06 09:15:00', 'staff2', '2024-02-06 14:20:00', 'Thanh toán qua chuyển khoản');

-- Đơn hàng 6: Đã hủy
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, cancelled_by, cancelled_at, cancel_reason, shipping_note) VALUES
('DH0000008', 8, '2024-02-06 10:30:00', 'COD', 'Số 987 Đường Lý Thường Kiệt, Quận Hoàn Kiếm, TP. Hà Nội', 'Đã hủy', 'admin', '2024-02-06 11:00:00', 'admin', '2024-02-06 15:30:00', 'Khách hàng yêu cầu hủy do thay đổi ý định', 'Đơn hàng đã hủy');

-- Đơn hàng 7: Chờ xác nhận (khách mới)
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, shipping_note) VALUES
('DH0000009', 9, '2024-02-07 08:45:00', 'E-Wallet', 'Số 147 Đường Võ Văn Tần, Quận 3, TP. Hồ Chí Minh', 'Chờ xác nhận', 'Khách hàng mới, ưu tiên xử lý');

-- Đơn hàng 8: Đang xử lý
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, shipping_note) VALUES
('DH0000010', 10, '2024-02-08 15:20:00', 'Bank Transfer', 'Số 258 Đường Lê Thánh Tôn, Quận 1, TP. Hồ Chí Minh', 'Đang xử lý', 'admin', '2024-02-08 16:00:00', 'Đơn hàng lớn, cần kiểm tra kỹ');

-- =====================================================
-- 5. BỔ SUNG CHI TIẾT ĐƠN HÀNG (ORDER_DETAILS)
-- =====================================================

-- Chi tiết đơn hàng 1 (Chờ xác nhận)
INSERT INTO order_details (order_id, product_id, quantity, unit_price, total_price) VALUES
(3, 1, 2, 350000, 700000),
(3, 7, 1, 195000, 195000);

-- Chi tiết đơn hàng 2 (Đang xử lý)
INSERT INTO order_details (order_id, product_id, quantity, unit_price, total_price) VALUES
(4, 2, 1, 650000, 650000),
(4, 8, 2, 520000, 1040000);

-- Chi tiết đơn hàng 3 (Đang giao)
INSERT INTO order_details (order_id, product_id, quantity, unit_price, total_price) VALUES
(5, 3, 3, 180000, 540000),
(5, 5, 2, 120000, 240000);

-- Chi tiết đơn hàng 4 (Hoàn tất)
INSERT INTO order_details (order_id, product_id, quantity, unit_price, total_price) VALUES
(6, 4, 1, 3500000, 3500000),
(6, 1, 1, 350000, 350000);

-- Chi tiết đơn hàng 5 (Đã thanh toán)
INSERT INTO order_details (order_id, product_id, quantity, unit_price, total_price) VALUES
(7, 9, 2, 420000, 840000),
(7, 10, 3, 250000, 750000);

-- Chi tiết đơn hàng 6 (Đã hủy)
INSERT INTO order_details (order_id, product_id, quantity, unit_price, total_price) VALUES
(8, 2, 1, 650000, 650000);

-- Chi tiết đơn hàng 7 (Chờ xác nhận)
INSERT INTO order_details (order_id, product_id, quantity, unit_price, total_price) VALUES
(9, 1, 3, 350000, 1050000),
(9, 3, 2, 180000, 360000),
(9, 5, 1, 120000, 120000);

-- Chi tiết đơn hàng 8 (Đang xử lý)
INSERT INTO order_details (order_id, product_id, quantity, unit_price, total_price) VALUES
(10, 4, 1, 3500000, 3500000),
(10, 2, 2, 650000, 1300000),
(10, 8, 1, 520000, 520000);

-- =====================================================
-- 6. CẬP NHẬT TỔNG TIỀN CHO CÁC ĐƠN HÀNG
-- =====================================================

-- Cập nhật tổng tiền cho đơn hàng 1
UPDATE orders SET total_amount = 895000 WHERE order_id = 3;

-- Cập nhật tổng tiền cho đơn hàng 2
UPDATE orders SET total_amount = 1690000 WHERE order_id = 4;

-- Cập nhật tổng tiền cho đơn hàng 3
UPDATE orders SET total_amount = 780000 WHERE order_id = 5;

-- Cập nhật tổng tiền cho đơn hàng 4
UPDATE orders SET total_amount = 3850000 WHERE order_id = 6;

-- Cập nhật tổng tiền cho đơn hàng 5
UPDATE orders SET total_amount = 1590000 WHERE order_id = 7;

-- Cập nhật tổng tiền cho đơn hàng 6
UPDATE orders SET total_amount = 650000 WHERE order_id = 8;

-- Cập nhật tổng tiền cho đơn hàng 7
UPDATE orders SET total_amount = 1530000 WHERE order_id = 9;

-- Cập nhật tổng tiền cho đơn hàng 8
UPDATE orders SET total_amount = 5320000 WHERE order_id = 10;

-- =====================================================
-- KẾT THÚC SCRIPT
-- =====================================================
