<?php
/**
 * Controller xử lý các thao tác quản lý kho
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/Product.php';

class InventoryController {
    private $db;
    private $inventory;
    private $product;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->inventory = new Inventory($this->db);
        $this->product = new Product($this->db);
    }
    
    /**
     * Hiển thị danh sách lịch sử nhập/xuất kho
     */
    public function index() {
        // Lấy tham số tìm kiếm và lọc
        $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
        $type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
        $start_date = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : null;
        $end_date = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Tính offset cho phân trang
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        // Lấy danh sách lịch sử
        $transactions = $this->inventory->getInventoryHistory($product_id, $type, $start_date, $end_date, $limit, $offset)->fetchAll(PDO::FETCH_ASSOC);
        $total_items = $this->inventory->countInventoryHistory($product_id, $type, $start_date, $end_date);
        $total_pages = ceil($total_items / $limit);
        
        // Lấy danh sách sản phẩm cho filter
        $products = $this->product->getAll()->fetchAll();
        
        // Lấy thống kê
        $statistics = $this->inventory->getInventoryStatistics();
        
        // Load view
        require_once __DIR__ . '/../views/inventory/index.php';
    }
    
    /**
     * Hiển thị form nhập kho
     */
    public function importForm() {
        // Lấy danh sách sản phẩm (bao gồm cả sản phẩm hết hạn để có thể nhập lại)
        $products = $this->product->getAll('', '', '')->fetchAll();
        
        // Lọc sản phẩm không bị vô hiệu hóa (cho phép cả Expired)
        $products = array_filter($products, function($product) {
            return $product['status'] != 'Disabled';
        });
        
        // Tạo mã phiếu nhập tự động
        $import_code = 'PN' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        require_once __DIR__ . '/../views/inventory/import.php';
    }
    
    /**
     * Xử lý nhập kho
     */
    public function import() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?controller=InventoryController&action=index');
            return;
        }
        
        try {
            // Lấy dữ liệu từ form
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            $unit_price = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : 0;
            $total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
            $import_date = date('Y-m-d', strtotime($_POST['import_date']));
            $import_by = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'admin_demo';
            $note = sanitizeInput($_POST['note']);
            
            // Lấy thông tin cập nhật cho sản phẩm hết hạn
            $new_manufacture_date = isset($_POST['new_manufacture_date']) ? $_POST['new_manufacture_date'] : null;
            $new_expiry_date = isset($_POST['new_expiry_date']) ? $_POST['new_expiry_date'] : null;
            
            // Validate dữ liệu
            $errors = [];
            
            if ($quantity <= 0) {
                $errors[] = 'Số lượng nhập phải lớn hơn 0';
            }
            
            // Validate đơn giá
            if ($unit_price < 1000 || $unit_price > 1000000000) {
                $errors[] = 'Đơn giá nhập phải từ 1.000 đến 1.000.000.000 VNĐ';
            }
            
            // Tính lại thành tiền để đảm bảo tính chính xác
            $calculated_total = $quantity * $unit_price;
            if (abs($total_amount - $calculated_total) > 0.01) {
                $total_amount = $calculated_total; // Sử dụng giá trị tính toán từ server
            }
            
            if (strtotime($import_date) > time()) {
                $errors[] = 'Ngày nhập không được vượt quá ngày hiện tại';
            }
            
            // Kiểm tra sản phẩm tồn tại
            $productFound = $this->product->getById($product_id);
            if (!$productFound) {
                $errors[] = 'Sản phẩm không tồn tại';
            }
            
            // Validate thông tin cập nhật cho sản phẩm hết hạn
            if ($productFound && $this->product->status === 'Expired') {
                $confirm_expired_update = isset($_POST['confirm_expired_update']) ? $_POST['confirm_expired_update'] : false;
                
                if (empty($new_manufacture_date)) {
                    $errors[] = 'Vui lòng nhập ngày sản xuất mới cho sản phẩm hết hạn';
                }
                if (empty($new_expiry_date)) {
                    $errors[] = 'Vui lòng nhập ngày hết hạn mới cho sản phẩm hết hạn';
                }
                if (!$confirm_expired_update) {
                    $errors[] = 'Vui lòng xác nhận đã kiểm tra và cập nhật đúng thông tin cho sản phẩm hết hạn';
                }
                if ($new_manufacture_date && $new_expiry_date && strtotime($new_manufacture_date) >= strtotime($new_expiry_date)) {
                    $errors[] = 'Ngày sản xuất phải trước ngày hết hạn';
                }
                if ($new_manufacture_date && strtotime($new_manufacture_date) > time()) {
                    $errors[] = 'Ngày sản xuất không được vượt quá ngày hiện tại';
                }
                if ($new_expiry_date && strtotime($new_expiry_date) <= time()) {
                    $errors[] = 'Ngày hết hạn phải sau ngày hiện tại';
                }
            }
            
            if (!empty($errors)) {
                setFlashMessage('error', implode('<br>', $errors));
                $_SESSION['form_data'] = $_POST;
                redirect('?controller=InventoryController&action=importForm');
                return;
            }
            
            // Bắt đầu transaction
            $this->db->beginTransaction();
            
            try {
                // 1. Tạo phiếu nhập
                $query = "INSERT INTO warehouse_import 
                         (import_code, product_id, quantity, unit_price, total_amount, import_date, import_by, note, status) 
                         VALUES (:import_code, :product_id, :quantity, :unit_price, :total_amount, :import_date, :import_by, :note, :status)";
                
                $import_code = 'PN' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                error_log("DEBUG: Import query: " . $query);
                error_log("DEBUG: Import params: " . print_r([
                    'import_code' => $import_code,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'total_amount' => $total_amount,
                    'import_date' => $import_date,
                    'import_by' => $import_by,
                    'note' => $note,
                    'status' => 'Completed'
                ], true));
                
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(":import_code", $import_code);
                $stmt->bindValue(":product_id", $product_id);
                $stmt->bindValue(":quantity", $quantity);
                $stmt->bindValue(":unit_price", $unit_price);
                $stmt->bindValue(":total_amount", $total_amount);
                $stmt->bindValue(":import_date", $import_date);
                $stmt->bindValue(":import_by", $import_by);
                $stmt->bindValue(":note", $note);
                $stmt->bindValue(":status", 'Completed');
                $stmt->execute();
                
                $import_id = $this->db->lastInsertId();
                
                // 2. Lấy tồn kho trước khi cập nhật
                $old_stock = $this->inventory->getCurrentStock($product_id);
                
                // 3. Cập nhật tồn kho
                $this->inventory->updateStock($product_id, $quantity, 'import');
                
                // 4. Cập nhật giá sản phẩm nếu đơn giá nhập khác với giá hiện tại
                if ($productFound) {
                    $current_price = (float)$productFound['price'];
                    if (abs($unit_price - $current_price) > 0.01) {
                        // Đơn giá nhập khác với giá hiện tại, cập nhật giá bán mới
                        $update_price_query = "UPDATE products 
                                             SET price = :price,
                                                 updated_at = NOW(),
                                                 updated_by = :updated_by
                                             WHERE product_id = :product_id";
                        
                        $update_price_stmt = $this->db->prepare($update_price_query);
                        $update_price_stmt->bindValue(":price", $unit_price);
                        $update_price_stmt->bindValue(":updated_by", $import_by);
                        $update_price_stmt->bindValue(":product_id", $product_id);
                        $update_price_stmt->execute();
                        
                        // Cập nhật note để ghi nhận việc cập nhật giá
                        $note .= " | Cập nhật giá bán: " . number_format($current_price, 0, ',', '.') . " VNĐ → " . number_format($unit_price, 0, ',', '.') . " VNĐ";
                    }
                }
                
                // 5. Cập nhật thông tin sản phẩm nếu là sản phẩm hết hạn
                if ($productFound && $this->product->status === 'Expired' && $new_manufacture_date && $new_expiry_date) {
                    // Xác định trạng thái mới dựa trên ngày hết hạn mới
                    $new_status = 'Active'; // Mặc định là Active vì đã có ngày hết hạn mới
                    $current_date = date('Y-m-d');
                    
                    // Nếu ngày hết hạn mới vẫn trong quá khứ, giữ nguyên Expired
                    if (strtotime($new_expiry_date) < strtotime($current_date)) {
                        $new_status = 'Expired';
                    }
                    
                    $update_query = "UPDATE products 
                                   SET manufacture_date = :manufacture_date, 
                                       expiry_date = :expiry_date,
                                       status = :status,
                                       updated_at = NOW(),
                                       updated_by = :updated_by
                                   WHERE product_id = :product_id";
                    
                    $update_stmt = $this->db->prepare($update_query);
                    $update_stmt->bindValue(":manufacture_date", $new_manufacture_date);
                    $update_stmt->bindValue(":expiry_date", $new_expiry_date);
                    $update_stmt->bindValue(":status", $new_status);
                    $update_stmt->bindValue(":updated_by", $import_by);
                    $update_stmt->bindValue(":product_id", $product_id);
                    $update_stmt->execute();
                    
                    // Cập nhật note để ghi nhận việc cập nhật thông tin
                    $note .= " | Cập nhật thông tin: NSX: {$new_manufacture_date}, HSD: {$new_expiry_date}, Trạng thái: {$new_status}";
                }
                
                // 6. Lấy tồn kho sau khi cập nhật
                $new_stock = $this->inventory->getCurrentStock($product_id);
                
                // 7. Ghi log lịch sử (cập nhật note với thông tin đầy đủ)
                $query = "INSERT INTO warehouse_history 
                         (reference_code, action_type, product_id, quantity, old_stock, new_stock, action_by, note) 
                         VALUES (:ref_code, :action_type, :product_id, :quantity, :old_stock, :new_stock, :action_by, :note)";
                
                error_log("DEBUG: History query: " . $query);
                error_log("DEBUG: History params: " . print_r([
                    'ref_code' => $import_code,
                    'action_type' => 'Import',
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'old_stock' => $old_stock,
                    'new_stock' => $new_stock,
                    'action_by' => $import_by,
                    'note' => $note
                ], true));
                
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(":ref_code", $import_code);
                $stmt->bindValue(":action_type", 'Import');
                $stmt->bindValue(":product_id", $product_id);
                $stmt->bindValue(":quantity", $quantity);
                $stmt->bindValue(":old_stock", $old_stock);
                $stmt->bindValue(":new_stock", $new_stock);
                $stmt->bindValue(":action_by", $import_by);
                $stmt->bindValue(":note", $note);
                $stmt->execute();
                
                // Commit transaction
                $this->db->commit();
                
                setFlashMessage('success', 'Nhập kho thành công');
                redirect('?controller=InventoryController&action=index');
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log('Lỗi khi nhập kho: ' . $e->getMessage());
            error_log('Chi tiết lỗi: ' . $e->getTraceAsString());
            error_log('Dữ liệu POST: ' . print_r($_POST, true));
            setFlashMessage('error', 'Có lỗi xảy ra khi nhập kho: ' . $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            redirect('?controller=InventoryController&action=importForm');
        }
    }
    
    /**
     * Hiển thị form xuất kho
     */
    public function exportForm() {
        // Lấy danh sách sản phẩm còn hàng (bao gồm cả sản phẩm hết hạn)
        $products = $this->product->getAll('', '', '')->fetchAll();
        
        // Lọc sản phẩm còn hàng (số lượng > 0) và không bị vô hiệu hóa
        $products = array_filter($products, function($product) {
            return $product['stock_quantity'] > 0 && $product['status'] != 'Disabled';
        });
        
        // Tạo mã phiếu xuất tự động
        $export_code = 'PX' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        require_once __DIR__ . '/../views/inventory/export.php';
    }
    
    /**
     * Xử lý xuất kho
     */
    public function export() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?controller=InventoryController&action=index');
            return;
        }
        
        try {
            // Lấy dữ liệu từ form
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            $unit_price = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : 0;
            $total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
            $export_date = date('Y-m-d', strtotime($_POST['export_date']));
            $export_by = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'admin_demo';
            $reason = sanitizeInput($_POST['reason']);
            $note = sanitizeInput($_POST['note']);
            
            // Validate dữ liệu
            $errors = [];
            
            if ($quantity <= 0) {
                $errors[] = 'Số lượng xuất phải lớn hơn 0';
            }
            
            // Validate đơn giá
            if ($unit_price < 1000 || $unit_price > 1000000000) {
                $errors[] = 'Đơn giá xuất phải từ 1.000 đến 1.000.000.000 VNĐ';
            }
            
            // Tính lại thành tiền để đảm bảo tính chính xác
            $calculated_total = $quantity * $unit_price;
            if (abs($total_amount - $calculated_total) > 0.01) {
                $total_amount = $calculated_total; // Sử dụng giá trị tính toán từ server
            }
            
            if (strtotime($export_date) > time()) {
                $errors[] = 'Ngày xuất không được vượt quá ngày hiện tại';
            }
            
            // Kiểm tra sản phẩm tồn tại và còn hàng
            $product = $this->product->getById($product_id);
            if (!$product) {
                $errors[] = 'Sản phẩm không tồn tại';
            } else {
                $current_stock = $this->inventory->getCurrentStock($product_id);
                if ($current_stock < $quantity) {
                    $errors[] = 'Số lượng xuất vượt quá tồn kho hiện có';
                }
            }
            
            if (empty($reason)) {
                $errors[] = 'Vui lòng chọn lý do xuất kho';
            }
            
            if (!empty($errors)) {
                setFlashMessage('error', implode('<br>', $errors));
                $_SESSION['form_data'] = $_POST;
                redirect('?controller=InventoryController&action=exportForm');
                return;
            }
            
            // Bắt đầu transaction
            $this->db->beginTransaction();
            
            try {
                // 1. Tạo phiếu xuất
                $query = "INSERT INTO warehouse_export 
                         (export_code, product_id, quantity, unit_price, total_amount, export_date, export_by, reason, note, status) 
                         VALUES (:export_code, :product_id, :quantity, :unit_price, :total_amount, :export_date, :export_by, :reason, :note, :status)";
                
                $export_code = 'PX' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(":export_code", $export_code);
                $stmt->bindValue(":product_id", $product_id);
                $stmt->bindValue(":quantity", $quantity);
                $stmt->bindValue(":unit_price", $unit_price);
                $stmt->bindValue(":total_amount", $total_amount);
                $stmt->bindValue(":export_date", $export_date);
                $stmt->bindValue(":export_by", $export_by);
                $stmt->bindValue(":reason", $reason);
                $stmt->bindValue(":note", $note);
                $stmt->bindValue(":status", 'Completed');
                $stmt->execute();
                
                $export_id = $this->db->lastInsertId();
                
                // 2. Lấy tồn kho trước khi cập nhật
                $old_stock = $this->inventory->getCurrentStock($product_id);
                
                // 3. Cập nhật tồn kho
                $this->inventory->updateStock($product_id, $quantity, 'export');
                
                // 4. Lấy tồn kho sau khi cập nhật
                $new_stock = $this->inventory->getCurrentStock($product_id);
                
                $query = "INSERT INTO warehouse_history 
                         (reference_code, action_type, product_id, quantity, old_stock, new_stock, action_by, note) 
                         VALUES (:ref_code, :action_type, :product_id, :quantity, :old_stock, :new_stock, :action_by, :note)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(":ref_code", $export_code);
                $stmt->bindValue(":action_type", 'Export');
                $stmt->bindValue(":product_id", $product_id);
                $stmt->bindValue(":quantity", $quantity);
                $stmt->bindValue(":old_stock", $old_stock);
                $stmt->bindValue(":new_stock", $new_stock);
                $stmt->bindValue(":action_by", $export_by);
                $stmt->bindValue(":note", "$reason. $note");
                $stmt->execute();
                
                // Commit transaction
                $this->db->commit();
                
                setFlashMessage('success', 'Xuất kho thành công');
                redirect('?controller=InventoryController&action=index');
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log('Lỗi khi xuất kho: ' . $e->getMessage());
            setFlashMessage('error', 'Có lỗi xảy ra khi xuất kho. Vui lòng thử lại.');
            $_SESSION['form_data'] = $_POST;
            redirect('?controller=InventoryController&action=exportForm');
        }
    }
    
    /**
     * Xem chi tiết giao dịch kho
     */
    public function view($id, $type = 'history') {
        // Nếu type là history, lấy từ bảng warehouse_history
        if ($type === 'history') {
            $query = "SELECT h.*, p.product_name, p.product_code 
                     FROM warehouse_history h
                     LEFT JOIN products p ON h.product_id = p.product_id 
                     WHERE h.history_id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$transaction) {
                setFlashMessage('error', 'Không tìm thấy giao dịch');
                redirect('?controller=InventoryController&action=index');
                return;
            }
            
            // Lấy lịch sử liên quan
            $history = $this->inventory->getInventoryHistory(
                $transaction['product_id'], 
                $transaction['action_type'],
                null, 
                null, 
                10
            );
        } else {
            // Xử lý import/export như cũ
            $table = $type === 'import' ? 'warehouse_import' : 'warehouse_export';
            $id_field = $type === 'import' ? 'import_id' : 'export_id';
            
            $query = "SELECT t.*, p.product_name, p.product_code 
                     FROM $table t
                     JOIN products p ON t.product_id = p.product_id 
                     WHERE t.$id_field = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$transaction) {
                setFlashMessage('error', 'Không tìm thấy giao dịch');
                redirect('?controller=InventoryController&action=index');
                return;
            }
            
            // Lấy lịch sử liên quan
            $history = $this->inventory->getInventoryHistory(
                $transaction['product_id'], 
                $type === 'import' ? 'Import' : 'Export',
                null, 
                null, 
                10
            );
        }
        
        require_once __DIR__ . "/../views/inventory/view.php";
    }

    /**
     * Xóa bản ghi lịch sử kho và hoàn tác tồn kho tương ứng
     */
    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            setFlashMessage('error', 'Thiếu mã giao dịch cần xóa');
            redirect('?controller=InventoryController&action=index');
            return;
        }

        try {
            // Lấy bản ghi lịch sử
            $query = "SELECT * FROM warehouse_history WHERE history_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $history = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$history) {
                setFlashMessage('error', 'Không tìm thấy giao dịch cần xóa');
                redirect('?controller=InventoryController&action=index');
                return;
            }

            $product_id = (int)$history['product_id'];
            $quantity = (int)$history['quantity'];
            $action_type = $history['action_type']; // Import | Export

            $this->db->beginTransaction();

            // Hoàn tác tồn kho theo loại giao dịch
            if ($action_type === 'Import') {
                // Xóa nhập -> trừ tồn kho (dùng export để trừ)
                $this->inventory->updateStock($product_id, $quantity, 'export');
            } else {
                // Xóa xuất -> cộng tồn kho (dùng import để cộng)
                $this->inventory->updateStock($product_id, $quantity, 'import');
            }

            // Xóa bản ghi lịch sử
            $del = $this->db->prepare("DELETE FROM warehouse_history WHERE history_id = :id");
            $del->bindValue(':id', $id, PDO::PARAM_INT);
            $del->execute();

            $this->db->commit();

            setFlashMessage('success', 'Đã xóa giao dịch và cập nhật tồn kho');
            redirect('?controller=InventoryController&action=index');
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Xóa lịch sử kho lỗi: ' . $e->getMessage());
            setFlashMessage('error', 'Không thể xóa giao dịch. Vui lòng thử lại.');
            redirect('?controller=InventoryController&action=index');
        }
    }

    /**
     * Hiển thị form chỉnh sửa lịch sử kho
     */
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            setFlashMessage('error', 'Thiếu mã giao dịch');
            redirect('?controller=InventoryController&action=index');
            return;
        }

        $stmt = $this->db->prepare("SELECT h.*, p.product_name, p.product_code FROM warehouse_history h LEFT JOIN products p ON p.product_id = h.product_id WHERE h.history_id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $history = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$history) {
            setFlashMessage('error', 'Không tìm thấy giao dịch');
            redirect('?controller=InventoryController&action=index');
            return;
        }

        require_once __DIR__ . '/../views/inventory/edit.php';
    }

    /**
     * Cập nhật lịch sử kho (điều chỉnh tồn kho theo chênh lệch số lượng)
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?controller=InventoryController&action=index');
            return;
        }

        $id = (int)($_POST['history_id'] ?? 0);
        $new_quantity = (int)($_POST['quantity'] ?? 0);
        $note = sanitizeInput($_POST['note'] ?? '');

        if ($id <= 0 || $new_quantity < 0) {
            setFlashMessage('error', 'Dữ liệu không hợp lệ');
            redirect('?controller=InventoryController&action=index');
            return;
        }

        try {
            // Lấy bản ghi hiện tại
            $stmt = $this->db->prepare("SELECT * FROM warehouse_history WHERE history_id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $history = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$history) {
                setFlashMessage('error', 'Không tìm thấy giao dịch');
                redirect('?controller=InventoryController&action=index');
                return;
            }

            $product_id = (int)$history['product_id'];
            $old_quantity = (int)$history['quantity'];
            $action_type = $history['action_type'];

            // Tính chênh lệch để điều chỉnh tồn kho
            $delta = $new_quantity - $old_quantity; // dương: tăng, âm: giảm

            $this->db->beginTransaction();

            if ($delta !== 0) {
                if ($action_type === 'Import') {
                    // Import: delta dương -> cộng thêm; delta âm -> trừ bớt
                    if ($delta > 0) {
                        $this->inventory->updateStock($product_id, $delta, 'import');
                    } else {
                        $this->inventory->updateStock($product_id, abs($delta), 'export');
                    }
                } else { // Export
                    // Export: delta dương -> xuất thêm (trừ tồn); delta âm -> hoàn lại (cộng tồn)
                    if ($delta > 0) {
                        $this->inventory->updateStock($product_id, $delta, 'export');
                    } else {
                        $this->inventory->updateStock($product_id, abs($delta), 'import');
                    }
                }
            }

            // Cập nhật lại record lịch sử theo tồn kho hiện tại
            $old_stock = $this->inventory->getCurrentStock($product_id); // tồn sau khi điều chỉnh ở trên
            $new_stock = $old_stock; // giữ đồng nhất

            $upd = $this->db->prepare("UPDATE warehouse_history SET quantity = :q, note = :note, new_stock = :new_stock WHERE history_id = :id");
            $upd->bindValue(':q', $new_quantity, PDO::PARAM_INT);
            $upd->bindValue(':note', $note);
            $upd->bindValue(':new_stock', $new_stock, PDO::PARAM_INT);
            $upd->bindValue(':id', $id, PDO::PARAM_INT);
            $upd->execute();

            $this->db->commit();

            setFlashMessage('success', 'Đã cập nhật giao dịch');
            redirect('?controller=InventoryController&action=index');
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Cập nhật lịch sử kho lỗi: ' . $e->getMessage());
            setFlashMessage('error', 'Không thể cập nhật giao dịch.');
            redirect('?controller=InventoryController&action=index');
        }
    }
}

// Xử lý routing
$controller = new InventoryController();
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Gọi phương thức tương ứng
if (isset($_GET['id']) && $action === 'view') {
    $type = isset($_GET['type']) ? $_GET['type'] : 'history';
    $controller->view($_GET['id'], $type);
} else if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    $controller->index();
}
