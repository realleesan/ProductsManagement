<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Customer.php';

class CustomerController {
    private $customerModel;
    
    public function __construct() {
        // Lấy kết nối database từ config
        require_once __DIR__ . '/../config/database.php';
        global $conn;
        $this->customerModel = new Customer($conn);
    }
    
    public function create() {
        $page_title = 'Thêm khách hàng mới';
        $active_page = 'customers';
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/customers/create.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'fullname' => $_POST['fullname'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'email' => $_POST['email'] ?? '',
                'address' => $_POST['address'] ?? ''
            ];
            
            if ($this->customerModel->create($data)) {
                setFlashMessage('Thêm khách hàng thành công!', 'success');
                header('Location: ' . BASE_URL . '?controller=OrderController&action=create');
                exit;
            } else {
                setFlashMessage('Có lỗi xảy ra khi thêm khách hàng!', 'error');
            }
        }
        
        $this->create();
    }
}
