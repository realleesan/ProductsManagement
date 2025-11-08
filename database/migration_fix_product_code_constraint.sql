-- Migration: Sửa constraint định dạng mã sản phẩm từ SP + chữ cái/số sang SP + 7 chữ số
-- Ngày tạo: 2024
-- Mô tả: Sửa constraint chk_product_code_format để khớp với validation trong code
-- Tương thích với MariaDB và MySQL

-- BƯỚC 1: Tự động cập nhật mã sản phẩm cũ sang định dạng mới (SP + 7 chữ số)
-- Cập nhật mã dựa trên product_id để đảm bảo không trùng lặp
UPDATE products 
SET product_code = CONCAT('SP', LPAD(product_id, 7, '0')) 
WHERE product_code NOT REGEXP '^SP[0-9]{7}$';

-- BƯỚC 2: Xóa constraint cũ và thêm constraint mới
-- MariaDB/MySQL không hỗ trợ DROP CHECK trực tiếp, cần tạo lại bảng

-- Tắt kiểm tra foreign key tạm thời
SET FOREIGN_KEY_CHECKS = 0;

-- Tạo bảng tạm với cấu trúc giống bảng cũ nhưng có constraint mới
CREATE TABLE products_new (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã sản phẩm duy nhất, định dạng SP + 7 chữ số (ví dụ: SP1234567)',
    product_name VARCHAR(150) NOT NULL COMMENT 'Tên sản phẩm, 5-150 ký tự',
    description VARCHAR(500) COMMENT 'Mô tả sản phẩm, tối đa 500 ký tự',
    price DECIMAL(12,2) NOT NULL COMMENT 'Giá bán, từ 1.000 đến 1.000.000.000 VNĐ',
    stock_quantity INT NOT NULL DEFAULT 0 COMMENT 'Số lượng tồn kho, >= 0',
    category_id INT COMMENT 'Danh mục sản phẩm',
    manufacture_date DATE NOT NULL COMMENT 'Ngày sản xuất',
    expiry_date DATE NOT NULL COMMENT 'Hạn sử dụng, phải sau ngày sản xuất >= 30 ngày',
    status ENUM('Active', 'Disabled', 'Out of stock', 'Expired') DEFAULT 'Active' COMMENT 'Trạng thái sản phẩm',
    main_image VARCHAR(255) COMMENT 'Ảnh chính của sản phẩm',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(50) COMMENT 'Người tạo',
    updated_by VARCHAR(50) COMMENT 'Người cập nhật cuối',
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_product_code_format CHECK (product_code REGEXP '^SP[0-9]{7}$'),
    CONSTRAINT chk_product_name_length CHECK (CHAR_LENGTH(product_name) BETWEEN 5 AND 150),
    CONSTRAINT chk_price_range CHECK (price >= 1000 AND price <= 1000000000),
    CONSTRAINT chk_stock_quantity CHECK (stock_quantity >= 0),
    CONSTRAINT chk_expiry_after_manufacture CHECK (DATEDIFF(expiry_date, manufacture_date) >= 30),
    CONSTRAINT chk_description_length CHECK (description IS NULL OR CHAR_LENGTH(description) <= 500)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copy dữ liệu từ bảng cũ sang bảng mới
INSERT INTO products_new SELECT * FROM products;

-- Xóa bảng cũ
DROP TABLE products;

-- Đổi tên bảng mới thành tên cũ
RENAME TABLE products_new TO products;

-- Đặt lại AUTO_INCREMENT để tiếp tục từ ID lớn nhất
SET @max_id = (SELECT MAX(product_id) FROM products);
SET @sql = CONCAT('ALTER TABLE products AUTO_INCREMENT = ', IFNULL(@max_id, 0) + 1);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Bật lại kiểm tra foreign key
SET FOREIGN_KEY_CHECKS = 1;

-- Hoàn thành!
-- Bây giờ bạn có thể tạo sản phẩm mới với mã định dạng SP + 7 chữ số (ví dụ: SP1234567)

