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
('DH20240201001', 3, '2024-02-01 09:30:00', 'COD', 'Số 123 Đường Lê Lợi, Quận 1, TP. Đà Nẵng', 'Chờ xác nhận', 'Giao hàng trong giờ hành chính');

-- Đơn hàng 2: Đang xử lý
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, shipping_note) VALUES
('DH20240202001', 4, '2024-02-02 14:15:00', 'Bank Transfer', 'Số 456 Đường Nguyễn Huệ, Quận Ninh Kiều, TP. Cần Thơ', 'Đang xử lý', 'admin', '2024-02-02 15:00:00', 'Kiểm tra kỹ trước khi đóng gói');

-- Đơn hàng 3: Đang giao
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, shipping_note) VALUES
('DH20240203001', 5, '2024-02-03 11:20:00', 'E-Wallet', 'Số 789 Đường Trần Hưng Đạo, Quận Hải Châu, TP. Đà Nẵng', 'Đang giao', 'admin', '2024-02-03 12:00:00', 'Giao hàng nhanh');

-- Đơn hàng 4: Hoàn tất
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, completed_by, completed_at, shipping_note) VALUES
('DH20240204001', 6, '2024-02-04 16:45:00', 'COD', 'Số 321 Đường Lê Duẩn, Quận Thanh Khê, TP. Đà Nẵng', 'Hoàn tất', 'admin', '2024-02-04 17:00:00', 'staff1', '2024-02-05 10:30:00', 'Đã giao thành công');

-- Đơn hàng 5: Đã thanh toán
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, completed_by, completed_at, paid_by, paid_at, shipping_note) VALUES
('DH20240205001', 7, '2024-02-05 13:10:00', 'Bank Transfer', 'Số 654 Đường Nguyễn Văn Linh, Quận 7, TP. Hồ Chí Minh', 'Đã thanh toán', 'admin', '2024-02-05 13:30:00', 'staff2', '2024-02-06 09:15:00', 'staff2', '2024-02-06 14:20:00', 'Thanh toán qua chuyển khoản');

-- Đơn hàng 6: Đã hủy
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, cancelled_by, cancelled_at, cancel_reason, shipping_note) VALUES
('DH20240206001', 8, '2024-02-06 10:30:00', 'COD', 'Số 987 Đường Lý Thường Kiệt, Quận Hoàn Kiếm, TP. Hà Nội', 'Đã hủy', 'admin', '2024-02-06 11:00:00', 'admin', '2024-02-06 15:30:00', 'Khách hàng yêu cầu hủy do thay đổi ý định', 'Đơn hàng đã hủy');

-- Đơn hàng 7: Chờ xác nhận (khách mới)
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, shipping_note) VALUES
('DH20240207001', 9, '2024-02-07 08:45:00', 'E-Wallet', 'Số 147 Đường Võ Văn Tần, Quận 3, TP. Hồ Chí Minh', 'Chờ xác nhận', 'Khách hàng mới, ưu tiên xử lý');

-- Đơn hàng 8: Đang xử lý
INSERT INTO orders (order_code, customer_id, order_date, payment_method, shipping_address, status, confirmed_by, confirmed_at, shipping_note) VALUES
('DH20240208001', 10, '2024-02-08 15:20:00', 'Bank Transfer', 'Số 258 Đường Lê Thánh Tôn, Quận 1, TP. Hồ Chí Minh', 'Đang xử lý', 'admin', '2024-02-08 16:00:00', 'Đơn hàng lớn, cần kiểm tra kỹ');

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
