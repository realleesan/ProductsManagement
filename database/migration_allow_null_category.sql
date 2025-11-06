-- Migration: Cho phép category_id và main_image NULL trong bảng products
-- Mục đích: Loại bỏ ràng buộc bắt buộc cho danh mục và ảnh chính để thuận tiện cho kiểm thử

-- Bước 1: Xóa foreign key constraint cũ
ALTER TABLE products DROP FOREIGN KEY products_ibfk_1;

-- Bước 2: Sửa cột category_id để cho phép NULL
ALTER TABLE products MODIFY category_id INT NULL COMMENT 'Danh mục sản phẩm (có thể NULL)';

-- Bước 3: Tạo lại foreign key constraint (foreign key vẫn hoạt động với NULL)
ALTER TABLE products 
ADD CONSTRAINT fk_products_category 
FOREIGN KEY (category_id) 
REFERENCES categories(category_id) 
ON DELETE RESTRICT 
ON UPDATE CASCADE;

-- Lưu ý: main_image đã cho phép NULL trong schema gốc, không cần sửa

