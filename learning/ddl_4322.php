<?php
/**
 * Kiểm thử hộp trắng - Dòng dữ liệu
 * 4.3.2.2. Chức năng quản lý kho (Tạo phiếu nhập kho)
 * 
 * Input từ bảng giá trị:
 * - Mã phiếu nhập (string) - tự động tạo
 * - Tên sản phẩm (string) - được lấy từ product_id
 * - Số lượng nhập [1, maxint]
 * - Ngày nhập (date)
 * - Đơn giá [1.000, 1.000.000.000]
 * - Ghi chú (string)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/Product.php';

// Giả lập context của controller
$database = new Database();
$db = $database->getConnection();
$inventory = new Inventory($db);
$product = new Product($db);

/**
 * Xử lý nhập kho (chỉ dòng dữ liệu)
 */
function importWarehouse() {
    global $db, $inventory, $product;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('?controller=InventoryController&action=index');
        return;
    }
    
    try {
        // Bước 1: Nhận dữ liệu từ POST (chỉ các input trong bảng giá trị)
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        $unit_price = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : 0;
        $import_date = date('Y-m-d', strtotime($_POST['import_date']));
        $note = sanitizeInput($_POST['note']);
        
        // Validate dữ liệu
        $errors = [];
        
        if ($quantity <= 0) {
            $errors[] = 'Số lượng nhập phải lớn hơn 0';
        }
        
        if ($unit_price < 1000 || $unit_price > 1000000000) {
            $errors[] = 'Đơn giá nhập phải từ 1.000 đến 1.000.000.000 VNĐ';
        }
        
        if (strtotime($import_date) > time()) {
            $errors[] = 'Ngày nhập không được vượt quá ngày hiện tại';
        }
        
        // Bước 2: Kiểm tra sản phẩm tồn tại
        $productFound = $product->getById($product_id);
        if (!$productFound) {
            $errors[] = 'Sản phẩm không tồn tại';
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            $_SESSION['form_data'] = $_POST;
            redirect('?controller=InventoryController&action=importForm');
            return;
        }
        
        // Bước 3: Tính toán giá trị phụ (không có trong bảng giá trị nhưng cần thiết)
        $total_amount = $quantity * $unit_price;
        $import_code = 'PN' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        // Bước 4: Bắt đầu transaction
        $db->beginTransaction();
        
        try {
            // Bước 5: Tạo phiếu nhập
            $query = "INSERT INTO warehouse_import 
                     (import_code, product_id, quantity, unit_price, total_amount, import_date, import_by, note, status) 
                     VALUES (:import_code, :product_id, :quantity, :unit_price, :total_amount, :import_date, :import_by, :note, :status)";
            
            $stmt = $db->prepare($query);
            $stmt->bindValue(":import_code", $import_code);
            $stmt->bindValue(":product_id", $product_id);
            $stmt->bindValue(":quantity", $quantity);
            $stmt->bindValue(":unit_price", $unit_price);
            $stmt->bindValue(":total_amount", $total_amount);
            $stmt->bindValue(":import_date", $import_date);
            $stmt->bindValue(":import_by", 'system');
            $stmt->bindValue(":note", $note);
            $stmt->bindValue(":status", 'Completed');
            $stmt->execute();
            
            // Bước 6: Lấy tồn kho trước khi cập nhật
            $old_stock = $inventory->getCurrentStock($product_id);
            
            // Bước 7: Cập nhật tồn kho
            $inventory->updateStock($product_id, $quantity, 'import');
            
            // Bước 8: Lấy tồn kho sau khi cập nhật
            $new_stock = $inventory->getCurrentStock($product_id);
            
            // Bước 9: Ghi log lịch sử
            $query = "INSERT INTO warehouse_history 
                     (reference_code, action_type, product_id, quantity, old_stock, new_stock, action_by, note) 
                     VALUES (:ref_code, :action_type, :product_id, :quantity, :old_stock, :new_stock, :action_by, :note)";
            
            $stmt = $db->prepare($query);
            $stmt->bindValue(":ref_code", $import_code);
            $stmt->bindValue(":action_type", 'Import');
            $stmt->bindValue(":product_id", $product_id);
            $stmt->bindValue(":quantity", $quantity);
            $stmt->bindValue(":old_stock", $old_stock);
            $stmt->bindValue(":new_stock", $new_stock);
            $stmt->bindValue(":action_by", 'system');
            $stmt->bindValue(":note", $note);
            $stmt->execute();
            
            // Bước 10: Commit transaction
            $db->commit();
            
            setFlashMessage('success', 'Nhập kho thành công');
            redirect('?controller=InventoryController&action=index');
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log('Lỗi khi nhập kho: ' . $e->getMessage());
        setFlashMessage('error', 'Có lỗi xảy ra khi nhập kho: ' . $e->getMessage());
        $_SESSION['form_data'] = $_POST;
        redirect('?controller=InventoryController&action=importForm');
    }
}

