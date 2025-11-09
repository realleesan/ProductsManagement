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
        $search_code = isset($_GET['search_code']) ? sanitizeInput($_GET['search_code']) : null;
        $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
        $type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
        $start_date = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : null;
        $end_date = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Tính offset cho phân trang
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        // Lấy danh sách lịch sử
        $transactions = $this->inventory->getInventoryHistory($product_id, $type, $start_date, $end_date, $search_code, $limit, $offset)->fetchAll(PDO::FETCH_ASSOC);
        $total_items = $this->inventory->countInventoryHistory($product_id, $type, $start_date, $end_date, $search_code);
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
            $import_code = isset($_POST['import_code']) ? strtoupper(trim($_POST['import_code'])) : '';
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            $unit_price = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : 0;
            $total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
            $import_date_str = isset($_POST['import_date']) ? trim($_POST['import_date']) : '';
            $import_by = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'admin_demo';
            $note = sanitizeInput($_POST['note']);
            
            // Validate và chuyển đổi ngày nhập
            $import_date = null;
            if (!empty($import_date_str)) {
                // Kiểm tra định dạng YYYY/MM/DD hoặc YYYY/MM/DD HH:MM
                if (preg_match('/^(\d{4})\/(\d{2})\/(\d{2})(\s+(\d{2}):(\d{2}))?$/', $import_date_str, $matches)) {
                    $year = (int)$matches[1];
                    $month = (int)$matches[2];
                    $day = (int)$matches[3];
                    $hour = isset($matches[5]) ? (int)$matches[5] : 0;
                    $minute = isset($matches[6]) ? (int)$matches[6] : 0;
                    
                    if (checkdate($month, $day, $year) && $hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                        $import_date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    }
                }
            }
            
            // Lấy thông tin cập nhật cho sản phẩm hết hạn
            $new_manufacture_date = isset($_POST['new_manufacture_date']) ? $_POST['new_manufacture_date'] : null;
            $new_expiry_date = isset($_POST['new_expiry_date']) ? $_POST['new_expiry_date'] : null;
            
            // Validate dữ liệu
            $errors = [];
            
            // Validate ghi chú
            if (empty(trim($note))) {
                $errors[] = 'Vui lòng nhập ghi chú';
            } elseif (mb_strlen($note) > 500) {
                $errors[] = 'Ghi chú không được vượt quá 500 ký tự';
            }
            
            // Validate mã phiếu nhập
            if (empty($import_code)) {
                $errors[] = 'Vui lòng nhập mã phiếu nhập';
            } elseif (!preg_match('/^PN[0-9]{7}$/', $import_code)) {
                $errors[] = 'Mã phiếu nhập không hợp lệ. Định dạng phải là PN + 7 số (ví dụ: PN1234567)';
            } else {
                // Kiểm tra mã trùng lặp
                $check_code_query = "SELECT COUNT(*) FROM warehouse_import WHERE import_code = :import_code";
                $check_code_stmt = $this->db->prepare($check_code_query);
                $check_code_stmt->bindValue(":import_code", $import_code);
                $check_code_stmt->execute();
                if ($check_code_stmt->fetchColumn() > 0) {
                    $errors[] = 'Mã phiếu nhập đã tồn tại. Vui lòng nhập mã khác';
                }
            }
            
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
            
            // Validate ngày nhập
            if (empty($import_date_str)) {
                $errors[] = 'Vui lòng nhập ngày nhập';
            } elseif (!$import_date) {
                $errors[] = 'Định dạng ngày nhập không hợp lệ. Vui lòng nhập theo định dạng YYYY/MM/DD (ví dụ: 2024/01/15)';
            } else {
                // So sánh chỉ ngày, không so sánh giờ
                $input_date_timestamp = strtotime($import_date);
                $today_timestamp = strtotime(date('Y-m-d'));
                
                if ($input_date_timestamp > $today_timestamp) {
                    $errors[] = 'Ngày nhập không được vượt quá ngày hiện tại';
                } elseif ($input_date_timestamp < $today_timestamp) {
                    $errors[] = 'Ngày nhập không được là ngày quá khứ';
                }
            }
            
            // Validate ghi chú
            if (empty(trim($note))) {
                $errors[] = 'Vui lòng nhập ghi chú';
            } elseif (mb_strlen($note) > 500) {
                $errors[] = 'Ghi chú không được vượt quá 500 ký tự';
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
                    // Lấy giá hiện tại từ database TRƯỚC KHI cập nhật giá mới
                    $get_price_query = "SELECT price FROM products WHERE product_id = :product_id";
                    $get_price_stmt = $this->db->prepare($get_price_query);
                    $get_price_stmt->bindValue(":product_id", $product_id);
                    $get_price_stmt->execute();
                    $price_row = $get_price_stmt->fetch(PDO::FETCH_ASSOC);
                    $current_price = $price_row ? (float)$price_row['price'] : 0;
                    
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
            $export_code = isset($_POST['export_code']) ? strtoupper(trim($_POST['export_code'])) : '';
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            $unit_price = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : 0;
            $total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
            $export_date_str = isset($_POST['export_date']) ? trim($_POST['export_date']) : '';
            $export_by = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'admin_demo';
            $note = sanitizeInput($_POST['note']); // Lý do xuất được nhập vào trường note
            $reason = 'Other'; // Set mặc định là 'Other' vì database yêu cầu reason là ENUM
            
            // Validate và chuyển đổi ngày xuất
            $export_date = null;
            if (!empty($export_date_str)) {
                // Kiểm tra định dạng YYYY/MM/DD hoặc YYYY/MM/DD HH:MM
                if (preg_match('/^(\d{4})\/(\d{2})\/(\d{2})(\s+(\d{2}):(\d{2}))?$/', $export_date_str, $matches)) {
                    $year = (int)$matches[1];
                    $month = (int)$matches[2];
                    $day = (int)$matches[3];
                    $hour = isset($matches[5]) ? (int)$matches[5] : 0;
                    $minute = isset($matches[6]) ? (int)$matches[6] : 0;
                    
                    if (checkdate($month, $day, $year) && $hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                        $export_date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    }
                }
            }
            
            // Validate dữ liệu
            $errors = [];
            
            // Validate ngày xuất
            if (empty($export_date_str)) {
                $errors[] = 'Vui lòng nhập ngày xuất';
            } elseif (!$export_date) {
                $errors[] = 'Định dạng ngày xuất không hợp lệ. Vui lòng nhập theo định dạng YYYY/MM/DD (ví dụ: 2024/01/15)';
            } else {
                // So sánh chỉ ngày, không so sánh giờ
                $input_date_timestamp = strtotime($export_date);
                $today_timestamp = strtotime(date('Y-m-d'));
                
                if ($input_date_timestamp > $today_timestamp) {
                    $errors[] = 'Ngày xuất không được vượt quá ngày hiện tại';
                } elseif ($input_date_timestamp < $today_timestamp) {
                    $errors[] = 'Ngày xuất không được là ngày quá khứ';
                }
            }
            
            // Validate lý do xuất (note)
            if (empty(trim($note))) {
                $errors[] = 'Vui lòng nhập lý do xuất kho';
            } elseif (mb_strlen($note) > 500) {
                $errors[] = 'Lý do xuất không được vượt quá 500 ký tự';
            }
            
            // Validate mã phiếu xuất
            if (empty($export_code)) {
                $errors[] = 'Vui lòng nhập mã phiếu xuất';
            } elseif (!preg_match('/^PX[0-9]{7}$/', $export_code)) {
                $errors[] = 'Mã phiếu xuất không hợp lệ. Định dạng phải là PX + 7 số (ví dụ: PX1234567)';
            } else {
                // Kiểm tra mã trùng lặp
                $check_code_query = "SELECT COUNT(*) FROM warehouse_export WHERE export_code = :export_code";
                $check_code_stmt = $this->db->prepare($check_code_query);
                $check_code_stmt->bindValue(":export_code", $export_code);
                $check_code_stmt->execute();
                if ($check_code_stmt->fetchColumn() > 0) {
                    $errors[] = 'Mã phiếu xuất đã tồn tại. Vui lòng nhập mã khác';
                }
            }
            
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
                
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(":export_code", $export_code);
                $stmt->bindValue(":product_id", $product_id);
                $stmt->bindValue(":quantity", $quantity);
                $stmt->bindValue(":unit_price", $unit_price);
                $stmt->bindValue(":total_amount", $total_amount);
                $stmt->bindValue(":export_date", $export_date);
                $stmt->bindValue(":export_by", $export_by);
                $stmt->bindValue(":reason", $reason);
                $stmt->bindValue(":note", $note); // Lưu lý do xuất vào note
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
                $stmt->bindValue(":note", $note); // Lưu lý do xuất vào note
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
            $query = "SELECT h.*, p.product_name, p.product_code,
                             COALESCE(wi.import_date, we.export_date) as transaction_date
                     FROM warehouse_history h
                     LEFT JOIN products p ON h.product_id = p.product_id 
                     LEFT JOIN warehouse_import wi ON h.reference_code = wi.import_code AND h.action_type = 'Import'
                     LEFT JOIN warehouse_export we ON h.reference_code = we.export_code AND h.action_type = 'Export'
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
                null,
                10,
                0
            );
        } else {
            // Xử lý import/export như cũ
            $table = $type === 'import' ? 'warehouse_import' : 'warehouse_export';
            $id_field = $type === 'import' ? 'import_id' : 'export_id';
            $date_field = $type === 'import' ? 'import_date' : 'export_date';
            
            $query = "SELECT t.*, p.product_name, p.product_code,
                             t.$date_field as transaction_date
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
                null,
                10,
                0
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

        // Lấy thông tin lịch sử và đơn giá từ warehouse_import hoặc warehouse_export
        $stmt = $this->db->prepare("SELECT h.*, p.product_name, p.product_code,
                                         COALESCE(wi.unit_price, we.unit_price, 0) as unit_price,
                                         COALESCE(wi.total_amount, we.total_amount, 0) as total_amount
                                     FROM warehouse_history h 
                                     LEFT JOIN products p ON p.product_id = h.product_id 
                                     LEFT JOIN warehouse_import wi ON h.reference_code = wi.import_code AND h.action_type = 'Import'
                                     LEFT JOIN warehouse_export we ON h.reference_code = we.export_code AND h.action_type = 'Export'
                                     WHERE h.history_id = :id");
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
        $new_unit_price = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : 0;
        $note = sanitizeInput($_POST['note'] ?? '');

        if ($id <= 0 || $new_quantity < 0) {
            setFlashMessage('error', 'Dữ liệu không hợp lệ');
            redirect('?controller=InventoryController&action=index');
            return;
        }

        // Validate đơn giá
        if ($new_unit_price < 1000 || $new_unit_price > 1000000000) {
            setFlashMessage('error', 'Đơn giá phải từ 1.000 đến 1.000.000.000 VNĐ');
            redirect('?controller=InventoryController&action=edit&id=' . $id);
            return;
        }

        // Tính lại thành tiền
        $new_total_amount = $new_quantity * $new_unit_price;

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
            $reference_code = $history['reference_code'];
            $updated_by = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'admin_demo';

            // Lấy đơn giá cũ từ warehouse_import hoặc warehouse_export
            if ($action_type === 'Import') {
                $get_old_price_query = "SELECT unit_price FROM warehouse_import WHERE import_code = :reference_code";
            } else {
                $get_old_price_query = "SELECT unit_price FROM warehouse_export WHERE export_code = :reference_code";
            }
            $get_old_price_stmt = $this->db->prepare($get_old_price_query);
            $get_old_price_stmt->bindValue(':reference_code', $reference_code);
            $get_old_price_stmt->execute();
            $old_price_row = $get_old_price_stmt->fetch(PDO::FETCH_ASSOC);
            $old_unit_price = $old_price_row ? (float)$old_price_row['unit_price'] : 0;

            // Lấy giá hiện tại của sản phẩm từ database TRƯỚC KHI cập nhật
            $get_current_price_query = "SELECT price FROM products WHERE product_id = :product_id";
            $get_current_price_stmt = $this->db->prepare($get_current_price_query);
            $get_current_price_stmt->bindValue(":product_id", $product_id);
            $get_current_price_stmt->execute();
            $current_price_row = $get_current_price_stmt->fetch(PDO::FETCH_ASSOC);
            $current_product_price = $current_price_row ? (float)$current_price_row['price'] : 0;

            // Tính chênh lệch để điều chỉnh tồn kho
            $delta = $new_quantity - $old_quantity; // dương: tăng, âm: giảm

            $this->db->beginTransaction();

            // Cập nhật đơn giá và thành tiền trong warehouse_import hoặc warehouse_export
            if ($action_type === 'Import') {
                $update_table_query = "UPDATE warehouse_import 
                                       SET unit_price = :unit_price, 
                                           total_amount = :total_amount,
                                           updated_at = NOW()
                                       WHERE import_code = :reference_code";
            } else {
                $update_table_query = "UPDATE warehouse_export 
                                       SET unit_price = :unit_price, 
                                           total_amount = :total_amount,
                                           updated_at = NOW()
                                       WHERE export_code = :reference_code";
            }
            
            $update_table_stmt = $this->db->prepare($update_table_query);
            $update_table_stmt->bindValue(':unit_price', $new_unit_price);
            $update_table_stmt->bindValue(':total_amount', $new_total_amount);
            $update_table_stmt->bindValue(':reference_code', $reference_code);
            $update_table_stmt->execute();

            // Cập nhật giá sản phẩm nếu đơn giá trong phiếu nhập thay đổi (chỉ với phiếu nhập)
            if ($action_type === 'Import' && abs($new_unit_price - $old_unit_price) > 0.01) {
                // Cập nhật giá sản phẩm nếu đơn giá mới khác với giá hiện tại
                if (abs($new_unit_price - $current_product_price) > 0.01) {
                    $update_product_price_query = "UPDATE products 
                                                 SET price = :price,
                                                     updated_at = NOW(),
                                                     updated_by = :updated_by
                                                 WHERE product_id = :product_id";
                    
                    $update_product_price_stmt = $this->db->prepare($update_product_price_query);
                    $update_product_price_stmt->bindValue(":price", $new_unit_price);
                    $update_product_price_stmt->bindValue(":updated_by", $updated_by);
                    $update_product_price_stmt->bindValue(":product_id", $product_id);
                    $update_product_price_stmt->execute();
                }
                
                // Cập nhật note để ghi nhận việc cập nhật giá (luôn cập nhật ghi chú khi đơn giá thay đổi)
                $price_note = "Cập nhật giá bán: " . number_format($current_product_price, 0, ',', '.') . " VNĐ → " . number_format($new_unit_price, 0, ',', '.') . " VNĐ";
                
                // Xóa phần ghi chú về giá cũ (nếu có) và thêm ghi chú mới
                // Tìm và xóa các phần ghi chú về giá cũ có định dạng "Cập nhật giá bán: ..."
                $note = preg_replace('/\s*\|\s*Cập nhật giá bán:.*?(?=\s*\||$)/i', '', $note);
                $note = preg_replace('/^Cập nhật giá bán:.*?(?=\s*\||$)/i', '', $note);
                $note = trim($note);
                
                // Thêm ghi chú mới về giá
                if (!empty($note)) {
                    $note .= " | " . $price_note;
                } else {
                    $note = $price_note;
                }
            }

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
