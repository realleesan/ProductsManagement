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
        // Lấy danh sách khách hàng và sản phẩm
        $customers = $this->customer->getAll()->fetchAll();
        $products = $this->product->getActiveProducts()->fetchAll();
        
        require_once __DIR__ . '/../views/orders/create.php';
    }
    
    /**
     * Xử lý tạo đơn hàng mới
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/controllers/OrderController.php?action=index');
            return;
        }
        
        try {
            // Lấy dữ liệu từ form
            $this->order->customer_id = (int)$_POST['customer_id'];
            $this->order->shipping_address = sanitizeInput($_POST['shipping_address']);
            $this->order->shipping_note = isset($_POST['shipping_note']) ? sanitizeInput($_POST['shipping_note']) : '';
            $this->order->payment_method = sanitizeInput($_POST['payment_method']);
            $this->order->status = 'Chờ xác nhận'; // Mặc định trạng thái khi tạo mới
            
            // Xử lý danh sách sản phẩm
            $order_items = [];
            $total_amount = 0;
            
            if (isset($_POST['product_id']) && is_array($_POST['product_id'])) {
                foreach ($_POST['product_id'] as $index => $product_id) {
                    $quantity = (int)$_POST['quantity'][$index];
                    $product = new Product($this->db);
                    
                    if ($product->getById($product_id) && $quantity > 0) {
                        $order_items[] = [
                            'product_id' => $product_id,
                            'quantity' => $quantity,
                            'unit_price' => $product->price,
                            'product_name' => $product->product_name
                        ];
                        $total_amount += $product->price * $quantity;
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
                redirect('/controllers/OrderController.php?action=view&id=' . $order_id);
            } else {
                throw new Exception("Có lỗi xảy ra khi tạo đơn hàng");
            }
            
        } catch (Exception $e) {
            setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            redirect('/controllers/OrderController.php?action=create');
        }
    }
    
    /**
     * Xem chi tiết đơn hàng
     */
    public function view() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$this->order->getById($id)) {
            setFlashMessage('error', 'Không tìm thấy đơn hàng');
            redirect('/controllers/OrderController.php?action=index');
            return;
        }
        
        // Lấy thông tin khách hàng
        $customer = new Customer($this->db);
        $customer->getById($this->order->customer_id);
        
        // Lấy chi tiết đơn hàng
        $orderDetail = new OrderDetail($this->db);
        $order_items = $orderDetail->getByOrderId($id)->fetchAll(PDO::FETCH_ASSOC);
        
        // Load view
        require_once __DIR__ . '/../views/orders/view.php';
    }
    
    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/controllers/OrderController.php?action=index');
            return;
        }
        
        try {
            $order_id = (int)$_POST['order_id'];
            $new_status = sanitizeInput($_POST['status']);
            $cancel_reason = isset($_POST['cancel_reason']) ? sanitizeInput($_POST['cancel_reason']) : '';
            
            if (!$this->order->getById($order_id)) {
                throw new Exception("Không tìm thấy đơn hàng");
            }
            
            // Cập nhật trạng thái
            $this->order->status = $new_status;
            
            // Nếu hủy đơn hàng, lưu lý do
            if ($new_status === 'Đã hủy') {
                $this->order->cancel_reason = $cancel_reason;
            }
            
            if ($this->order->update()) {
                setFlashMessage('success', 'Cập nhật trạng thái đơn hàng thành công');
            } else {
                throw new Exception("Có lỗi xảy ra khi cập nhật trạng thái đơn hàng");
            }
            
        } catch (Exception $e) {
            setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
        }
        
        redirect('/controllers/OrderController.php?action=view&id=' . $order_id);
    }
    
    /**
     * Xuất hóa đơn
     */
    public function exportInvoice() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$this->order->getById($id)) {
            setFlashMessage('error', 'Không tìm thấy đơn hàng');
            redirect('/controllers/OrderController.php?action=index');
            return;
        }
        
        // Lấy thông tin khách hàng
        $customer = new Customer($this->db);
        $customer->getById($this->order->customer_id);
        
        // Lấy chi tiết đơn hàng
        $orderDetail = new OrderDetail($this->db);
        $order_items = $orderDetail->getByOrderId($id)->fetchAll(PDO::FETCH_ASSOC);
        
        // Tạo nội dung hóa đơn
        ob_start();
        require_once __DIR__ . '/../views/orders/export.php';
        $content = ob_get_clean();
        
        // Tạo file PDF
        require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Thiết lập thông tin tài liệu
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Hệ thống quản lý bán hàng');
        $pdf->SetTitle('Hóa đơn #' . $this->order->order_code);
        
        // Xóa header và footer mặc định
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Thêm một trang mới
        $pdf->AddPage();
        
        // Ghi nội dung
        $pdf->writeHTML($content, true, false, true, false, '');
        
        // Đóng và xuất file PDF
        $pdf->Output('hoadon_' . $this->order->order_code . '.pdf', 'D');
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
        
        redirect('/controllers/OrderController.php?action=index');
    }
}

// Xử lý routing
$controller = new OrderController();
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Kiểm tra xem action có tồn tại không
if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    // Nếu không tìm thấy action, chuyển hướng về trang chủ
    redirect('/controllers/OrderController.php');
}