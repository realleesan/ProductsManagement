<?php
/**
 * Kiểm thử hộp trắng - Dòng điều khiển
 * 4.3.1.2. Chức năng quản lý kho (Tạo phiếu xuất kho)
 * 
 * Input từ bảng giá trị:
 * - Mã phiếu xuất (string)
 * - Tên sản phẩm (string) - được lấy từ product_id
 * - Số lượng xuất [1, Số lượng tồn kho hiện tại]
 * - Ngày xuất (date)
 * - Lý do xuất (string)
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
 * Xử lý xuất kho (chỉ dòng điều khiển)
 */
function exportWarehouse() {
    global $db, $inventory, $product;
    
    // Điều kiện 1: Kiểm tra REQUEST_METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('?controller=InventoryController&action=index');
        return;
    }
    
    try {
        // Lấy dữ liệu từ form (chỉ các input trong bảng giá trị)
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        $unit_price = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : 0;
        $export_date = date('Y-m-d', strtotime($_POST['export_date']));
        $reason = sanitizeInput($_POST['reason']);
        $note = sanitizeInput($_POST['note']);
        
        // Validate dữ liệu
        $errors = [];
        
        // Điều kiện 2: Kiểm tra số lượng xuất > 0
        if ($quantity <= 0) {
            $errors[] = 'Số lượng xuất phải lớn hơn 0';
        }
        
        // Điều kiện 3: Kiểm tra đơn giá hợp lệ
        if ($unit_price < 1000 || $unit_price > 1000000000) {
            $errors[] = 'Đơn giá xuất phải từ 1.000 đến 1.000.000.000 VNĐ';
        }
        
        // Điều kiện 4: Kiểm tra ngày xuất <= ngày hiện tại
        if (strtotime($export_date) > time()) {
            $errors[] = 'Ngày xuất không được vượt quá ngày hiện tại';
        }
        
        // Điều kiện 5: Kiểm tra sản phẩm tồn tại và tồn kho đủ
        $productData = $product->getById($product_id);
        if (!$productData) {
            $errors[] = 'Sản phẩm không tồn tại';
        } else {
            $current_stock = $inventory->getCurrentStock($product_id);
            if ($current_stock < $quantity) {
                $errors[] = 'Số lượng xuất vượt quá tồn kho hiện có';
            }
        }
        
        // Điều kiện 6: Kiểm tra lý do xuất không rỗng
        if (empty($reason)) {
            $errors[] = 'Vui lòng chọn lý do xuất kho';
        }
        
        // Điều kiện 7: Xử lý validation errors
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            $_SESSION['form_data'] = $_POST;
            redirect('?controller=InventoryController&action=exportForm');
            return;
        }
        
        // Điều kiện 8: Transaction xử lý xuất kho
        $db->beginTransaction();
        
        try {
            // Tạo phiếu xuất
            $export_code = 'PX' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $total_amount = $quantity * $unit_price;
            
            $query = "INSERT INTO warehouse_export 
                     (export_code, product_id, quantity, unit_price, total_amount, export_date, export_by, reason, note, status) 
                     VALUES (:export_code, :product_id, :quantity, :unit_price, :total_amount, :export_date, :export_by, :reason, :note, :status)";
            
            $stmt = $db->prepare($query);
            $stmt->bindValue(":export_code", $export_code);
            $stmt->bindValue(":product_id", $product_id);
            $stmt->bindValue(":quantity", $quantity);
            $stmt->bindValue(":unit_price", $unit_price);
            $stmt->bindValue(":total_amount", $total_amount);
            $stmt->bindValue(":export_date", $export_date);
            $stmt->bindValue(":export_by", 'system');
            $stmt->bindValue(":reason", $reason);
            $stmt->bindValue(":note", $note);
            $stmt->bindValue(":status", 'Completed');
            $stmt->execute();
            
            // Cập nhật tồn kho
            $old_stock = $inventory->getCurrentStock($product_id);
            $inventory->updateStock($product_id, $quantity, 'export');
            $new_stock = $inventory->getCurrentStock($product_id);
            
            // Ghi lịch sử
            $query = "INSERT INTO warehouse_history 
                     (reference_code, action_type, product_id, quantity, old_stock, new_stock, action_by, note) 
                     VALUES (:ref_code, :action_type, :product_id, :quantity, :old_stock, :new_stock, :action_by, :note)";
            
            $stmt = $db->prepare($query);
            $stmt->bindValue(":ref_code", $export_code);
            $stmt->bindValue(":action_type", 'Export');
            $stmt->bindValue(":product_id", $product_id);
            $stmt->bindValue(":quantity", $quantity);
            $stmt->bindValue(":old_stock", $old_stock);
            $stmt->bindValue(":new_stock", $new_stock);
            $stmt->bindValue(":action_by", 'system');
            $stmt->bindValue(":note", "$reason. $note");
            $stmt->execute();
            
            // Commit transaction
            $db->commit();
            
            setFlashMessage('success', 'Xuất kho thành công');
            redirect('?controller=InventoryController&action=index');
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log('Lỗi khi xuất kho: ' . $e->getMessage());
        setFlashMessage('error', 'Có lỗi xảy ra khi xuất kho. Vui lòng thử lại.');
        $_SESSION['form_data'] = $_POST;
        redirect('?controller=InventoryController&action=exportForm');
    }
}

