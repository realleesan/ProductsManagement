<?php
/**
 * Controller xử lý các thao tác với khách hàng
 * Chủ yếu phục vụ cho việc thêm khách hàng nhanh trong quá trình tạo đơn hàng
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Customer.php';

class CustomerController {
    private $db;
    private $customer;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->customer = new Customer($this->db);
    }
    
    /**
     * Hiển thị form thêm khách hàng nhanh
     */
    public function create() {
        // Lấy URL redirect từ GET parameter
        $redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : '?controller=OrderController&action=create';
        
        // Load view
        require_once __DIR__ . '/../views/customers/create.php';
    }
    
    /**
     * Xử lý thêm khách hàng mới
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?controller=CustomerController&action=create');
            return;
        }
        
        try {
            // Lấy dữ liệu từ form
            $this->customer->fullname = sanitizeInput($_POST['fullname']);
            $this->customer->phone = sanitizeInput($_POST['phone']);
            $this->customer->email = !empty($_POST['email']) ? sanitizeInput($_POST['email']) : null;
            $this->customer->address = !empty($_POST['address']) ? sanitizeInput($_POST['address']) : null;
            $this->customer->status = 'Active';
            
            // Validate dữ liệu
            if (empty($this->customer->fullname)) {
                throw new Exception("Họ tên không được để trống");
            }
            
            if (empty($this->customer->phone)) {
                throw new Exception("Số điện thoại không được để trống");
            }
            
            // Validate số điện thoại
            if (!preg_match('/^[0-9]{10,11}$/', $this->customer->phone)) {
                throw new Exception("Số điện thoại phải có 10-11 chữ số");
            }
            
            // Validate email nếu có
            if (!empty($this->customer->email) && !filter_var($this->customer->email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email không hợp lệ");
            }
            
            // Tạo khách hàng
            $customer_id = $this->customer->create();
            
            if ($customer_id) {
                // Kiểm tra nếu được gọi từ AJAX (trong quá trình tạo đơn hàng)
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Thêm khách hàng thành công',
                        'customer_id' => $customer_id,
                        'customer_name' => $this->customer->fullname,
                        'customer_phone' => $this->customer->phone
                    ]);
                    exit;
                } else {
                    setFlashMessage('success', 'Thêm khách hàng thành công');
                    
                    // Kiểm tra nếu có redirect_url trong POST (từ form tạo đơn hàng)
                    if (isset($_POST['redirect_url']) && !empty($_POST['redirect_url'])) {
                        // Decode URL nếu cần
                        $redirect_url = urldecode($_POST['redirect_url']);
                        redirect($redirect_url);
                    } else {
                        redirect('?controller=OrderController&action=create');
                    }
                }
            } else {
                throw new Exception("Có lỗi xảy ra khi tạo khách hàng");
            }
            
        } catch (Exception $e) {
            // Kiểm tra nếu được gọi từ AJAX
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Lỗi: ' . $e->getMessage()
                ]);
                exit;
            } else {
                setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
                $_SESSION['form_data'] = $_POST;
                redirect('?controller=CustomerController&action=create');
            }
        }
    }
    
    /**
     * Lấy danh sách khách hàng (cho AJAX)
     */
    public function getCustomers() {
        try {
            $customers = $this->customer->getAll()->fetchAll();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'customers' => $customers
            ]);
            exit;
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}

// Xử lý routing - chỉ chạy khi được gọi trực tiếp hoặc từ index.php
if (basename($_SERVER['PHP_SELF']) === 'CustomerController.php' || 
    (isset($_GET['controller']) && $_GET['controller'] === 'CustomerController')) {
    $controller = new CustomerController();
    $action = isset($_GET['action']) ? $_GET['action'] : 'create';

    // Kiểm tra xem action có tồn tại không
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        // Nếu không tìm thấy action, chuyển hướng về form tạo
        redirect('?controller=CustomerController&action=create');
    }
}
