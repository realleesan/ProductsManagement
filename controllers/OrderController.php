<?php
/**
 * Controller xử lý các thao tác với đơn hàng
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderDetail.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Product.php';


class OrderController {
    private $db;

    /** @var Order */
    private $order;
    
    /** @var Customer */
    private $customer;
    
    /** @var Product */
    private $product;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        $this->order = new Order($this->db);
        $this->customer = new Customer($this->db);
        $this->product = new Product($this->db);
    }
    
    /**
     * Hiển thị danh sách đơn hàng
     */
    public function index() {
        // Lấy tham số tìm kiếm và lọc
        $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Tính offset cho phân trang
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        // Lấy danh sách đơn hàng
        $filters = [
            'search' => $search,
            'status' => $status,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        $orders = $this->order->getAll($filters, $limit, $offset);
        
        // Đếm tổng số đơn hàng
        $total_orders = $this->order->countAll($filters);
        $total_pages = ceil($total_orders / $limit);
        
        // Lấy thống kê
        $statistics = $this->order->getStatistics();
        
        // Load view
        require_once __DIR__ . '/../views/orders/index.php';
    }
    
    /**
     * Hiển thị form tạo đơn hàng mới
     */
    public function create() {
        // Lấy danh sách sản phẩm
        $products = $this->product->getActiveProducts()->fetchAll();
        
        require_once __DIR__ . '/../views/orders/create.php';
    }
    
    /**
     * Xử lý tạo đơn hàng mới
     */
    public function store() {
        error_log("OrderController::store() called");
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Not POST request, redirecting...");
            redirect('?controller=OrderController&action=index');
            return;
        }
        
        try {
            // Validate mã đơn hàng
            $order_code = !empty($_POST['order_code']) ? strtoupper(trim(sanitizeInput($_POST['order_code']))) : '';
            
            if (empty($order_code)) {
                throw new Exception("Mã đơn hàng không được để trống");
            }
            
            // Validate format mã đơn hàng: DH + 7 chữ số
            if (!preg_match('/^DH[0-9]{7}$/', $order_code)) {
                throw new Exception("Mã đơn hàng phải có định dạng DH + 7 chữ số (ví dụ: DH1234567)");
            }
            
            // Kiểm tra mã đơn hàng đã tồn tại chưa
            $checkOrderCodeQuery = "SELECT order_id FROM orders WHERE order_code = :order_code LIMIT 1";
            $checkOrderCodeStmt = $this->db->prepare($checkOrderCodeQuery);
            $checkOrderCodeStmt->bindParam(':order_code', $order_code);
            $checkOrderCodeStmt->execute();
            
            if ($checkOrderCodeStmt->rowCount() > 0) {
                throw new Exception("Mã đơn hàng đã tồn tại. Vui lòng nhập mã khác");
            }
            
            // Validate và xử lý thông tin khách hàng
            $customer_fullname = sanitizeInput($_POST['customer_fullname']);
            $customer_phone = sanitizeInput($_POST['customer_phone']);
            $customer_email = !empty($_POST['customer_email']) ? sanitizeInput($_POST['customer_email']) : null;
            $customer_address = !empty($_POST['customer_address']) ? sanitizeInput($_POST['customer_address']) : null;
            
            // Validate thông tin khách hàng
            if (empty($customer_fullname)) {
                throw new Exception("Họ tên khách hàng không được để trống");
            }
            
            if (empty($customer_phone)) {
                throw new Exception("Số điện thoại khách hàng không được để trống");
            }
            
            // Validate số điện thoại
            if (!preg_match('/^[0-9]{10,11}$/', $customer_phone)) {
                throw new Exception("Số điện thoại phải có 10-11 chữ số");
            }
            
            // Validate email nếu có
            if (!empty($customer_email) && !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email không hợp lệ");
            }
            
            // Kiểm tra xem số điện thoại đã tồn tại chưa
            $checkQuery = "SELECT customer_id FROM customers WHERE phone = :phone AND status = 'Active' LIMIT 1";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':phone', $customer_phone);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                // Nếu số điện thoại đã tồn tại, lấy ID của khách hàng đó
                $existing_customer = $checkStmt->fetch(PDO::FETCH_ASSOC);
                $customer_id = $existing_customer['customer_id'];
                
                // Cập nhật thông tin khách hàng nếu có thay đổi
                $this->customer->getById($customer_id);
                $update_needed = false;
                
                if ($this->customer->fullname !== $customer_fullname) {
                    $this->customer->fullname = $customer_fullname;
                    $update_needed = true;
                }
                
                if (!empty($customer_email) && $this->customer->email !== $customer_email) {
                    // Kiểm tra email trùng lặp trước khi cập nhật
                    $emailCheckQuery = "SELECT customer_id FROM customers WHERE email = :email AND customer_id != :customer_id LIMIT 1";
                    $emailCheckStmt = $this->db->prepare($emailCheckQuery);
                    $emailCheckStmt->bindParam(':email', $customer_email);
                    $emailCheckStmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
                    $emailCheckStmt->execute();
                    
                    if ($emailCheckStmt->rowCount() === 0) {
                        $this->customer->email = $customer_email;
                        $update_needed = true;
                    }
                }
                
                if (!empty($customer_address) && $this->customer->address !== $customer_address) {
                    $this->customer->address = $customer_address;
                    $update_needed = true;
                }
                
                if ($update_needed) {
                    $this->customer->update();
                }
            } else {
                // Nếu chưa tồn tại, tạo khách hàng mới
                try {
                    $this->customer->fullname = $customer_fullname;
                    $this->customer->phone = $customer_phone;
                    $this->customer->email = $customer_email;
                    $this->customer->address = $customer_address;
                    $this->customer->status = 'Active';
                    
                    $customer_id = $this->customer->create();
                    
                    if (!$customer_id) {
                        throw new Exception("Có lỗi xảy ra khi tạo khách hàng mới");
                    }
                } catch (Exception $e) {
                    // Nếu có lỗi khi tạo khách hàng (ví dụ: email trùng lặp), throw lại exception
                    throw new Exception("Lỗi khi tạo khách hàng: " . $e->getMessage());
                }
            }
            
            // Lấy dữ liệu từ form
            $this->order->order_code = $order_code;
            $this->order->customer_id = $customer_id;
            $this->order->shipping_address = sanitizeInput($_POST['shipping_address']);
            $this->order->shipping_note = isset($_POST['shipping_note']) ? sanitizeInput($_POST['shipping_note']) : '';
            $this->order->payment_method = isset($_POST['payment_method']) ? sanitizeInput($_POST['payment_method']) : 'COD';
            $this->order->status = 'Chờ xác nhận'; // Mặc định trạng thái khi tạo mới
            
            // Xử lý danh sách sản phẩm từ JSON
            $order_items = [];
            $total_amount = 0;
            
            if (isset($_POST['order_items']) && !empty($_POST['order_items'])) {
                $order_items_data = json_decode($_POST['order_items'], true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Dữ liệu sản phẩm không hợp lệ");
                }
                
                foreach ($order_items_data as $item) {
                    $product = new Product($this->db);
                    
                    if ($product->getById($item['product_id']) && $item['quantity'] > 0) {
                        $order_items[] = [
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'product_name' => $product->product_name
                        ];
                        $total_amount += $item['unit_price'] * $item['quantity'];
                    }
                }
            }
            
            if (empty($order_items)) {
                throw new Exception("Vui lòng chọn ít nhất một sản phẩm");
            }
            
            $this->order->total_amount = $total_amount;
            $this->order->order_items = $order_items;
            
            // Tạo đơn hàng
            $order_id = $this->order->create();
            
            if ($order_id) {
                setFlashMessage('success', 'Tạo đơn hàng thành công');
                redirect('?controller=OrderController&action=view&id=' . $order_id);
            } else {
                throw new Exception("Có lỗi xảy ra khi tạo đơn hàng");
            }
            
        } catch (Exception $e) {
            error_log("Error creating order: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            redirect('?controller=OrderController&action=create');
        }
    }
    
    /**
     * Xem chi tiết đơn hàng
     */
    public function view() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$this->order->getById($id)) {
            setFlashMessage('error', 'Không tìm thấy đơn hàng');
            redirect('?controller=OrderController&action=index');
            return;
        }
        
        // Lấy thông tin khách hàng
        $customer = new Customer($this->db);
        $customer->getById($this->order->customer_id);
        
        // Lấy chi tiết đơn hàng
        $orderDetail = new OrderDetail($this->db);
        $order_items = $orderDetail->getByOrderId($id);
        
        // Chuẩn bị dữ liệu cho view
        $order = [
            'order_id' => $this->order->order_id,
            'order_code' => $this->order->order_code,
            'customer_id' => $this->order->customer_id,
            'order_date' => $this->order->order_date,
            'total_amount' => $this->order->total_amount,
            'status' => $this->order->status,
            'payment_method' => $this->order->payment_method,
            'shipping_address' => $this->order->shipping_address,
            'shipping_note' => $this->order->shipping_note,
            'cancel_reason' => $this->order->cancel_reason,
            'customer_name' => $this->order->customer_name,
            'customer_phone' => $this->order->customer_phone,
            'customer_email' => $this->order->customer_email,
            'order_items' => $order_items,
            'discount_amount' => 0, // Chưa có trong database
            'shipping_fee' => 0, // Chưa có trong database
            'payment_status' => 'Chưa thanh toán' // Chưa có trong database
        ];
        
        // Load view
        require_once __DIR__ . '/../views/orders/view.php';
    }
    
    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
                exit;
            }
            redirect('?controller=OrderController&action=index');
            return;
        }
        
        try {
            $order_id = (int)$_POST['order_id'];
            $new_status = sanitizeInput($_POST['status']);
            $cancel_reason = isset($_POST['cancel_reason']) ? sanitizeInput($_POST['cancel_reason']) : '';
            
            if (!$this->order->getById($order_id)) {
                throw new Exception("Không tìm thấy đơn hàng");
            }
            
            // Lưu trạng thái cũ để kiểm tra
            $old_status = $this->order->status;
            
            // Cập nhật trạng thái
            $this->order->status = $new_status;
            
            // Nếu hủy đơn hàng, lưu lý do
            if ($new_status === 'Đã hủy') {
                $this->order->cancel_reason = $cancel_reason;
            }
            
            if ($this->order->updateStatus($order_id, $new_status, $cancel_reason)) {
                // Nếu chuyển sang trạng thái "Đã thanh toán", trừ tồn kho
                if ($new_status === 'Đã thanh toán' && $old_status !== 'Đã thanh toán') {
                    // Lấy danh sách sản phẩm trong đơn hàng
                    $orderDetail = new OrderDetail($this->db);
                    $order_items = $orderDetail->getByOrderId($order_id);
                    
                    // Trừ tồn kho cho từng sản phẩm
                    foreach ($order_items as $item) {
                        $product_id = $item['product_id'];
                        $quantity = $item['quantity'];
                        
                        // Trừ tồn kho (số âm để trừ)
                        $this->product->updateStock($product_id, -$quantity, "Đơn hàng #{$this->order->order_code} - Đã thanh toán");
                    }
                }
                
                // Kiểm tra nếu là AJAX request
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái đơn hàng thành công']);
                    exit;
                } else {
                    setFlashMessage('success', 'Cập nhật trạng thái đơn hàng thành công');
                }
            } else {
                throw new Exception("Có lỗi xảy ra khi cập nhật trạng thái đơn hàng");
            }
            
        } catch (Exception $e) {
            // Kiểm tra nếu là AJAX request
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
                exit;
            } else {
                setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
            }
        }
        
        // Chỉ redirect nếu không phải AJAX request
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            redirect('?controller=OrderController&action=view&id=' . $order_id);
        }
    }
    
    
    /**
     * Xóa đơn hàng (chỉ xóa khi ở trạng thái chờ xác nhận)
     */
    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        try {
            if (!$this->order->getById($id)) {
                throw new Exception("Không tìm thấy đơn hàng");
            }
            
            // Chỉ cho phép xóa đơn hàng ở trạng thái chờ xác nhận
            if ($this->order->status !== 'Chờ xác nhận') {
                throw new Exception("Chỉ có thể xóa đơn hàng ở trạng thái 'Chờ xác nhận'");
            }
            
            if ($this->order->delete($id)) {
                setFlashMessage('success', 'Xóa đơn hàng thành công');
            } else {
                throw new Exception("Có lỗi xảy ra khi xóa đơn hàng");
            }
            
        } catch (Exception $e) {
            setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
        }
        
        redirect('?controller=OrderController&action=index');
    }
}

// Xử lý routing - chỉ chạy khi được gọi trực tiếp
if (basename($_SERVER['PHP_SELF']) === 'OrderController.php') {
    $controller = new OrderController();
    $action = isset($_GET['action']) ? $_GET['action'] : 'index';

    // Kiểm tra xem action có tồn tại không
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        // Nếu không tìm thấy action, chuyển hướng về trang chủ
        redirect('?controller=OrderController&action=index');
    }
}