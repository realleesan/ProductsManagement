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
    
    -- Tự động cập nhật trạng thái
    IF NEW.stock_quantity = 0 THEN
        SET NEW.status = 'Out of stock';
    ELSEIF NEW.expiry_date < CURDATE() THEN
        SET NEW.status = 'Expired';
    ELSEIF OLD.status IN ('Out of stock', 'Expired') 
        AND NEW.stock_quantity > 0 
        AND NEW.expiry_date >= CURDATE() THEN
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
('SP006', 'Serum Vitamin C The Ordinary', 'Serum vitamin C 23% giúp làm sáng da và mờ thâm nám', 280000, 0, 1, '2024-02-15', '2025-08-15', 'Out of stock', 'sample_ordinary_main.jpg', 'admin'),
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
