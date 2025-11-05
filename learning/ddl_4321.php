<?php
/**
 * Kiểm thử hộp trắng - Dòng dữ liệu
 * 4.3.2.1. Chức năng quản lý sản phẩm (Thêm mới sản phẩm)
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
 * Xử lý thêm sản phẩm mới (chỉ dòng dữ liệu)
 */
function storeProduct() {
    global $product;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('?controller=ProductController&action=index');
        return;
    }
    
    try {
        // Bước 1: Nhận dữ liệu từ POST (chỉ các input trong bảng giá trị)
        $product->product_code = strtoupper(sanitizeInput($_POST['product_code']));
        $product->product_name = sanitizeInput($_POST['product_name']);
        $product->description = sanitizeInput($_POST['description']);
        $product->price = (float)$_POST['price'];
        $product->stock_quantity = (int)$_POST['stock_quantity'];
        $product->manufacture_date = $_POST['manufacture_date'];
        $product->expiry_date = $_POST['expiry_date'];
        $product->status = sanitizeInput($_POST['status']);
        
        // Bước 2: Kiểm tra mã sản phẩm đã tồn tại
        if ($product->codeExists($product->product_code)) {
            setFlashMessage('error', 'Mã sản phẩm đã tồn tại trong hệ thống');
            $_SESSION['form_data'] = $_POST;
            redirect('?controller=ProductController&action=create');
            return;
        }
        
        // Bước 3: Validate dữ liệu
        $errors = $product->validate();
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            $_SESSION['form_data'] = $_POST;
            redirect('?controller=ProductController&action=create');
            return;
        }
        
        // Bước 4: Thiết lập giá trị mặc định cho các trường không có trong bảng giá trị
        // (để code vẫn chạy được khi ghép vào file gốc)
        // Lưu ý: category_id và main_image không có trong bảng giá trị nhưng cần thiết cho DB
        $product->category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 1;
        $product->main_image = isset($_POST['main_image']) ? sanitizeInput($_POST['main_image']) : 'default_product.jpg';
        $product->created_by = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'system';
        
        // Bước 5: Thêm sản phẩm
        if ($product->create()) {
            setFlashMessage('success', 'Thêm sản phẩm thành công');
            redirect('?controller=ProductController&action=index');
        } else {
            setFlashMessage('error', 'Có lỗi xảy ra khi thêm sản phẩm');
            $_SESSION['form_data'] = $_POST;
            redirect('?controller=ProductController&action=create');
        }
        
    } catch (Exception $e) {
        setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
        $_SESSION['form_data'] = $_POST;
        redirect('/controllers/ProductController.php?action=create');
    }
}

