<?php
/**
 * File index.php - Router chính của hệ thống
 */

require_once __DIR__ . '/config/config.php';

// Lấy controller và action từ URL
$controller = $_GET['controller'] ?? 'ProductController';
$action = $_GET['action'] ?? 'dashboard';

// Xử lý routing
switch ($controller) {
    case 'ProductController':
        require_once __DIR__ . '/controllers/ProductController.php';
        $productController = new ProductController();
        $productController->$action();
        break;
        
    case 'InventoryController':
        require_once __DIR__ . '/controllers/InventoryController.php';
        $inventoryController = new InventoryController();
        $inventoryController->$action();
        break;
        
    case 'OrderController':
        require_once __DIR__ . '/controllers/OrderController.php';
        $orderController = new OrderController();
        $orderController->$action();
        break;
        
    case 'CustomerController':
        require_once __DIR__ . '/controllers/CustomerController.php';
        $customerController = new CustomerController();
        $customerController->$action();
        break;
        
    default:
        // Redirect đến dashboard nếu không tìm thấy controller
        header('Location: ' . BASE_URL . '/controllers/ProductController.php?action=dashboard');
        exit();
}
