<?php
/**
 * Kiểm thử hộp trắng - Dòng điều khiển
 * 4.3.1.1. Chức năng quản lý sản phẩm (Cập nhật sản phẩm)
 * 
 * Input từ bảng giá trị:
 * - Mã sản phẩm (string)
 * - Tên sản phẩm (string)
 * - Mô tả (string)
 * - Giá bán [1.000, 1.000.000.000]
 * - Số lượng tồn kho [0, maxint]
 * - Ngày sản xuất (date)
 * - Hạn sử dụng (date)
 * - Trạng thái {Active, Disabled, Out of stock, Expired}
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Product.php';

// Giả lập context của controller
$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

/**
 * Xử lý cập nhật sản phẩm (chỉ dòng điều khiển)
 */
function updateProduct() {
    global $product, $db;
    
    // Điều kiện 1: Kiểm tra REQUEST_METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('?controller=ProductController&action=index');
        return;
    }
    
    try {
        $id = (int)$_POST['product_id'];
        
        // Điều kiện 2: Kiểm tra sản phẩm tồn tại
        if (!$product->getById($id)) {
            setFlashMessage('error', 'Không tìm thấy sản phẩm');
            redirect('?controller=ProductController&action=index');
            return;
        }
        
        // Cập nhật dữ liệu (chỉ các input trong bảng giá trị)
        $product->product_code = strtoupper(sanitizeInput($_POST['product_code']));
        $product->product_name = sanitizeInput($_POST['product_name']);
        $product->description = sanitizeInput($_POST['description']);
        $product->price = (float)$_POST['price'];
        $product->stock_quantity = (int)$_POST['stock_quantity'];
        $product->manufacture_date = $_POST['manufacture_date'];
        $product->expiry_date = $_POST['expiry_date'];
        $product->status = sanitizeInput($_POST['status']);
        
        // Thiết lập giá trị mặc định cho các trường không có trong bảng giá trị
        // (để code vẫn chạy được khi ghép vào file gốc)
        if (!isset($product->category_id)) {
            $product->category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : $product->category_id;
        }
        if (empty($product->main_image)) {
            $product->main_image = isset($_POST['main_image']) ? sanitizeInput($_POST['main_image']) : $product->main_image;
        }
        
        // Điều kiện 3: Kiểm tra mã sản phẩm trùng lặp
        if ($product->codeExists($product->product_code, $id)) {
            setFlashMessage('error', 'Mã sản phẩm đã tồn tại trong hệ thống');
            redirect('?controller=ProductController&action=edit&id=' . $id);
            return;
        }
        
        // Điều kiện 4: Validate dữ liệu
        $errors = $product->validate();
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect('?controller=ProductController&action=edit&id=' . $id);
            return;
        }
        
        // Điều kiện 5: Cập nhật sản phẩm (thành công/thất bại)
        if ($product->update()) {
            setFlashMessage('success', 'Cập nhật sản phẩm thành công');
            redirect('?controller=ProductController&action=index');
        } else {
            setFlashMessage('error', 'Có lỗi xảy ra khi cập nhật sản phẩm');
            redirect('?controller=ProductController&action=edit&id=' . $id);
        }
        
    } catch (Exception $e) {
        setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
        redirect('/controllers/ProductController.php?action=edit&id=' . $id);
    }
}

