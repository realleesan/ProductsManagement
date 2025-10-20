<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Inventory.php';

class OrderController {
    private $orderModel;
    private $inventoryModel;
    
    public function __construct($db) {
        $this->orderModel = new Order($db);
        $this->inventoryModel = new Inventory($db);
    }
    
    // Hiển thị danh sách đơn hàng
    public function index() {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'from_date' => $_GET['from_date'] ?? '',
            'to_date' => $_GET['to_date'] ?? '',
            'customer_id' => $_GET['customer_id'] ?? ''
        ];
        
        $data = [
            'title' => 'Quản lý đơn hàng',
            'active_menu' => 'orders',
            'orders' => $this->orderModel->getAllOrders($filters),
            'filters' => $filters
        ];
        
        require_once __DIR__ . '/../views/orders/index.php';
    }
    
    // Hiển thị form tạo đơn hàng mới
    public function create() {
        $data = [
            'title' => 'Tạo đơn hàng mới',
            'active_menu' => 'orders',
            'customers' => $this->orderModel->getCustomers(),
            'products' => $this->inventoryModel->getAllProducts()
        ];
        
        require_once __DIR__ . '/../views/orders/create.php';
    }
    
    // Xử lý tạo đơn hàng mới
    public function store() {
        try {
            $orderData = [
                'customer_id' => $_POST['customer_id'],
                'order_date' => date('Y-m-d H:i:s'),
                'delivery_date' => $_POST['delivery_date'] ?? null,
                'payment_method' => $_POST['payment_method'] ?? 'cod',
                'shipping_address' => $_POST['shipping_address'] ?? '',
                'note' => $_POST['note'] ?? '',
                'items' => []
            ];
            
            // Xử lý các sản phẩm trong đơn hàng
            if (!empty($_POST['products'])) {
                foreach ($_POST['products'] as $product) {
                    if (!empty($product['product_id']) && !empty($product['quantity']) && $product['quantity'] > 0) {
                        $orderData['items'][] = [
                            'product_id' => $product['product_id'],
                            'quantity' => $product['quantity'],
                            'price' => $product['price'],
                            'note' => $product['note'] ?? ''
                        ];
                    }
                }
            }
            
            if (empty($orderData['items'])) {
                throw new Exception('Vui lòng thêm ít nhất một sản phẩm vào đơn hàng');
            }
            
            $orderId = $this->orderModel->createOrder($orderData);
            
            $_SESSION['success'] = 'Tạo đơn hàng thành công!';
            header('Location: /orders/' . $orderId);
            exit();
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }
    
    // Xem chi tiết đơn hàng
    public function show($orderId) {
        $order = $this->orderModel->getOrderById($orderId);
        
        if (!$order) {
            $_SESSION['error'] = 'Không tìm thấy đơn hàng';
            header('Location: /orders');
            exit();
        }
        
        $data = [
            'title' => 'Chi tiết đơn hàng #' . $order['order_code'],
            'active_menu' => 'orders',
            'order' => $order
        ];
        
        require_once __DIR__ . '/../views/orders/show.php';
    }
    
    // Cập nhật trạng thái đơn hàng
    public function updateStatus($orderId) {
        try {
            if (!isset($_POST['status'])) {
                throw new Exception('Thiếu thông tin trạng thái');
            }
            
            $status = $_POST['status'];
            $note = $_POST['note'] ?? '';
            
            $this->orderModel->updateOrderStatus($orderId, $status, $note);
            
            $_SESSION['success'] = 'Cập nhật trạng thái đơn hàng thành công!';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/orders'));
        exit();
    }
    
    // Hủy đơn hàng
    public function cancel($orderId) {
        try {
            $reason = $_POST['reason'] ?? '';
            $this->orderModel->cancelOrder($orderId, $reason);
            $_SESSION['success'] = 'Đã hủy đơn hàng thành công!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/orders'));
        exit();
    }
    
    // Xóa đơn hàng
    public function delete($orderId) {
        try {
            $this->orderModel->deleteOrder($orderId);
            $_SESSION['success'] = 'Đã xóa đơn hàng thành công!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        
        header('Location: /orders');
        exit();
    }
    
    // API tìm kiếm khách hàng (dùng cho autocomplete)
    public function searchCustomers() {
        $search = $_GET['q'] ?? '';
        $customers = $this->orderModel->getCustomers($search);
        
        header('Content-Type: application/json');
        echo json_encode($customers);
        exit();
    }
    
    // API tìm kiếm sản phẩm (dùng cho autocomplete)
    public function searchProducts() {
        $search = $_GET['q'] ?? '';
        $products = $this->orderModel->searchProducts($search);
        
        header('Content-Type: application/json');
        echo json_encode($products);
        exit();
    }
    
    // Thống kê đơn hàng
    public function stats() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $stats = $this->orderModel->getOrderStats($startDate, $endDate);
        
        $data = [
            'title' => 'Thống kê đơn hàng',
            'active_menu' => 'order_stats',
            'stats' => $stats,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        require_once __DIR__ . '/../views/orders/stats.php';
    }
    
    // In hóa đơn
    public function printInvoice($orderId) {
        $order = $this->orderModel->getOrderById($orderId);
        
        if (!$order) {
            $_SESSION['error'] = 'Không tìm thấy đơn hàng';
            header('Location: /orders');
            exit();
        }
        
        $data = [
            'title' => 'Hóa đơn #' . $order['order_code'],
            'order' => $order
        ];
        
        // Sử dụng view riêng cho việc in ấn
        require_once __DIR__ . '/../views/orders/print_invoice.php';
    }
}
