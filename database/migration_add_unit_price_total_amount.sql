-- =====================================================
-- MIGRATION: THÊM ĐƠN GIÁ VÀ THÀNH TIỀN CHO PHIẾU NHẬP/XUẤT KHO
-- Phiên bản: 1.1
-- Ngày tạo: 2025-01-XX
-- =====================================================

USE quanlysanpham;

-- =====================================================
-- THÊM CỘT ĐƠN GIÁ VÀ THÀNH TIỀN VÀO BẢNG PHIẾU NHẬP KHO
-- =====================================================
ALTER TABLE warehouse_import
ADD COLUMN unit_price DECIMAL(12,2) NULL COMMENT 'Đơn giá nhập (VNĐ), từ 1.000 đến 1.000.000.000' AFTER quantity,
ADD COLUMN total_amount DECIMAL(12,2) NULL COMMENT 'Thành tiền = số lượng × đơn giá' AFTER unit_price;

-- Cập nhật dữ liệu hiện có: Lấy giá hiện tại của sản phẩm làm đơn giá nhập
UPDATE warehouse_import wi
INNER JOIN products p ON wi.product_id = p.product_id
SET wi.unit_price = CASE 
    WHEN p.price >= 1000 AND p.price <= 1000000000 THEN p.price
    WHEN p.price < 1000 THEN 1000
    ELSE 1000000000
END,
wi.total_amount = wi.quantity * CASE 
    WHEN p.price >= 1000 AND p.price <= 1000000000 THEN p.price
    WHEN p.price < 1000 THEN 1000
    ELSE 1000000000
END;

-- Đặt giá trị mặc định cho các bản ghi không có giá (nếu có)
UPDATE warehouse_import
SET unit_price = 1000, total_amount = quantity * 1000
WHERE unit_price IS NULL;

-- Thay đổi cột thành NOT NULL sau khi đã cập nhật dữ liệu
ALTER TABLE warehouse_import
MODIFY COLUMN unit_price DECIMAL(12,2) NOT NULL DEFAULT 1000,
MODIFY COLUMN total_amount DECIMAL(12,2) NOT NULL DEFAULT 0;

-- Thêm ràng buộc cho đơn giá nhập
ALTER TABLE warehouse_import
ADD CONSTRAINT chk_import_unit_price CHECK (unit_price >= 1000 AND unit_price <= 1000000000),
ADD CONSTRAINT chk_import_total_amount CHECK (total_amount >= 0);

-- =====================================================
-- THÊM CỘT ĐƠN GIÁ VÀ THÀNH TIỀN VÀO BẢNG PHIẾU XUẤT KHO
-- =====================================================
ALTER TABLE warehouse_export
ADD COLUMN unit_price DECIMAL(12,2) NULL COMMENT 'Đơn giá xuất (VNĐ), từ 1.000 đến 1.000.000.000' AFTER quantity,
ADD COLUMN total_amount DECIMAL(12,2) NULL COMMENT 'Thành tiền = số lượng × đơn giá' AFTER unit_price;

-- Cập nhật dữ liệu hiện có: Lấy giá hiện tại của sản phẩm làm đơn giá xuất
UPDATE warehouse_export we
INNER JOIN products p ON we.product_id = p.product_id
SET we.unit_price = CASE 
    WHEN p.price >= 1000 AND p.price <= 1000000000 THEN p.price
    WHEN p.price < 1000 THEN 1000
    ELSE 1000000000
END,
we.total_amount = we.quantity * CASE 
    WHEN p.price >= 1000 AND p.price <= 1000000000 THEN p.price
    WHEN p.price < 1000 THEN 1000
    ELSE 1000000000
END;

-- Đặt giá trị mặc định cho các bản ghi không có giá (nếu có)
UPDATE warehouse_export
SET unit_price = 1000, total_amount = quantity * 1000
WHERE unit_price IS NULL;

-- Thay đổi cột thành NOT NULL sau khi đã cập nhật dữ liệu
ALTER TABLE warehouse_export
MODIFY COLUMN unit_price DECIMAL(12,2) NOT NULL DEFAULT 1000,
MODIFY COLUMN total_amount DECIMAL(12,2) NOT NULL DEFAULT 0;

-- Thêm ràng buộc cho đơn giá xuất
ALTER TABLE warehouse_export
ADD CONSTRAINT chk_export_unit_price CHECK (unit_price >= 1000 AND unit_price <= 1000000000),
ADD CONSTRAINT chk_export_total_amount CHECK (total_amount >= 0);

-- =====================================================
-- CẬP NHẬT DỮ LIỆU MẪU (nếu có)
-- =====================================================
-- Cập nhật đơn giá và thành tiền cho các bản ghi hiện có (nếu cần)
-- UPDATE warehouse_import 
-- SET unit_price = (SELECT price FROM products WHERE products.product_id = warehouse_import.product_id),
--     total_amount = quantity * unit_price
-- WHERE unit_price = 0;

-- UPDATE warehouse_export 
-- SET unit_price = (SELECT price FROM products WHERE products.product_id = warehouse_export.product_id),
--     total_amount = quantity * unit_price
-- WHERE unit_price = 0;

-- =====================================================
-- KẾT THÚC MIGRATION
-- =====================================================

