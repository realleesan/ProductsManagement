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
        // Lấy danh sách sản phẩm còn hoạt động
        $products = $this->product->getAll('', '', 'Active')->fetchAll();
        
        // Tạo mã phiếu nhập tự động
        $import_code = 'PN' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        require_once __DIR__ . '/../views/inventory/import.php';
    }
    
    /**
     * Xử lý nhập kho
     */
    public function import() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/controllers/InventoryController.php?action=index');
            return;
        }
        
        try {
            // Lấy dữ liệu từ form
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            $import_date = $_POST['import_date'];
            $import_by = $_SESSION['user']['username'];
            $note = sanitizeInput($_POST['note']);
            
            // Validate dữ liệu
            $errors = [];
            
            if ($quantity <= 0) {
                $errors[] = 'Số lượng nhập phải lớn hơn 0';
            }
            
            if (strtotime($import_date) > time()) {
                $errors[] = 'Ngày nhập không được vượt quá ngày hiện tại';
            }
            
            // Kiểm tra sản phẩm tồn tại
            $product = $this->product->getById($product_id);
            if (!$product) {
                $errors[] = 'Sản phẩm không tồn tại';
            }
            
            if (!empty($errors)) {
                setFlashMessage('error', implode('<br>', $errors));
                $_SESSION['form_data'] = $_POST;
                redirect('/controllers/InventoryController.php?action=importForm');
                return;
            }
            
            // Bắt đầu transaction
            $this->db->beginTransaction();
            
            try {
                // 1. Tạo phiếu nhập
                $query = "INSERT INTO warehouse_import 
                         (import_code, product_id, quantity, import_date, import_by, note, status) 
                         VALUES (:import_code, :product_id, :quantity, :import_date, :import_by, :note, 'Completed')";
                
                $import_code = 'PN' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":import_code", $import_code);
                $stmt->bindParam(":product_id", $product_id);
                $stmt->bindParam(":quantity", $quantity);
                $stmt->bindParam(":import_date", $import_date);
                $stmt->bindParam(":import_by", $import_by);
                $stmt->bindParam(":note", $note);
                $stmt->execute();
                
                $import_id = $this->db->lastInsertId();
                
                // 2. Cập nhật tồn kho
                $this->inventory->updateStock($product_id, $quantity, 'import');
                
                // 3. Ghi log lịch sử
                $current_stock = $this->inventory->getCurrentStock($product_id);
                
                $query = "INSERT INTO warehouse_history 
                         (reference_code, action_type, product_id, quantity, old_stock, new_stock, action_by, note) 
                         VALUES (:ref_code, 'Import', :product_id, :quantity, :old_stock, :new_stock, :action_by, :note)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(":ref_code", $import_code);
                $stmt->bindValue(":product_id", $product_id);
                $stmt->bindValue(":quantity", $quantity);
                $stmt->bindValue(":old_stock", $current_stock - $quantity);
                $stmt->bindValue(":new_stock", $current_stock);
                $stmt->bindValue(":action_by", $import_by);
                $stmt->bindValue(":note", $note);
                $stmt->execute();
                
                // Commit transaction
                $this->db->commit();
                
                setFlashMessage('success', 'Nhập kho thành công');
                redirect('/controllers/InventoryController.php?action=index');
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log('Lỗi khi nhập kho: ' . $e->getMessage());
            setFlashMessage('error', 'Có lỗi xảy ra khi nhập kho. Vui lòng thử lại.');
            $_SESSION['form_data'] = $_POST;
            redirect('/controllers/InventoryController.php?action=importForm');
        }
    }
    
    /**
     * Hiển thị form xuất kho
     */
    public function exportForm() {
        // Lấy danh sách sản phẩm còn hàng
        $products = $this->product->getAll('', '', 'active')->fetchAll();
        
        // Lọc sản phẩm còn hàng (số lượng > 0)
        $products = array_filter($products, function($product) {
            return $product['stock_quantity'] > 0;
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
            redirect('/controllers/InventoryController.php?action=index');
            return;
        }
        
        try {
            // Lấy dữ liệu từ form
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            $export_date = $_POST['export_date'];
            $export_by = $_SESSION['user']['username'];
            $reason = sanitizeInput($_POST['reason']);
            $note = sanitizeInput($_POST['note']);
            
            // Validate dữ liệu
            $errors = [];
            
            if ($quantity <= 0) {
                $errors[] = 'Số lượng xuất phải lớn hơn 0';
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
                redirect('/controllers/InventoryController.php?action=exportForm');
                return;
            }
            
            // Bắt đầu transaction
            $this->db->beginTransaction();
            
            try {
                // 1. Tạo phiếu xuất
                $query = "INSERT INTO warehouse_export 
                         (export_code, product_id, quantity, export_date, export_by, reason, note, status) 
                         VALUES (:export_code, :product_id, :quantity, :export_date, :export_by, :reason, :note, 'Completed')";
                
                $export_code = 'PX' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":export_code", $export_code);
                $stmt->bindParam(":product_id", $product_id);
                $stmt->bindParam(":quantity", $quantity);
                $stmt->bindParam(":export_date", $export_date);
                $stmt->bindParam(":export_by", $export_by);
                $stmt->bindParam(":reason", $reason);
                $stmt->bindParam(":note", $note);
                $stmt->execute();
                
                $export_id = $this->db->lastInsertId();
                
                // 2. Cập nhật tồn kho
                $this->inventory->updateStock($product_id, $quantity, 'export');
                
                // 3. Ghi log lịch sử
                $new_stock = $this->inventory->getCurrentStock($product_id);
                
                $query = "INSERT INTO warehouse_history 
                         (reference_code, action_type, product_id, quantity, old_stock, new_stock, action_by, note) 
                         VALUES (:ref_code, 'Export', :product_id, :quantity, :old_stock, :new_stock, :action_by, :note)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(":ref_code", $export_code);
                $stmt->bindValue(":product_id", $product_id);
                $stmt->bindValue(":quantity", $quantity);
                $stmt->bindValue(":old_stock", $current_stock);
                $stmt->bindValue(":new_stock", $new_stock);
                $stmt->bindValue(":action_by", $export_by);
                $stmt->bindValue(":note", "$reason. $note");
                $stmt->execute();
                
                // Commit transaction
                $this->db->commit();
                
                setFlashMessage('success', 'Xuất kho thành công');
                redirect('/controllers/InventoryController.php?action=index');
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log('Lỗi khi xuất kho: ' . $e->getMessage());
            setFlashMessage('error', 'Có lỗi xảy ra khi xuất kho. Vui lòng thử lại.');
            $_SESSION['form_data'] = $_POST;
            redirect('/controllers/InventoryController.php?action=exportForm');
        }
    }
    
    /**
     * Xem chi tiết giao dịch kho
     */
    public function view($id, $type = 'import') {
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
            redirect('/controllers/InventoryController.php?action=index');
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
        
        require_once __DIR__ . "/../views/inventory/view.php";
    }
}

// Xử lý routing
$controller = new InventoryController();
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Gọi phương thức tương ứng
if (method_exists($controller, $action)) {
    $controller->$action();
} else if (isset($_GET['id']) && $action === 'view') {
    $type = isset($_GET['type']) ? $_GET['type'] : 'import';
    $controller->view($_GET['id'], $type);
} else {
    $controller->index();
}
