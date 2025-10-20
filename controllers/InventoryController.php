<?php
require_once __DIR__ . '/../models/Inventory.php';

class InventoryController {
    private $inventoryModel;

    public function __construct($db) {
        $this->inventoryModel = new Inventory($db);
    }

    // Hiển thị danh sách nhập/xuất kho
    public function index($type = 'import') {
        $data = [
            'title' => $type === 'import' ? 'Quản lý nhập kho' : 'Quản lý xuất kho',
            'active_menu' => 'inventory',
            'type' => $type,
            'transactions' => $type === 'import' 
                ? $this->inventoryModel->getAllImports() 
                : $this->inventoryModel->getAllExports()
        ];
        
        require_once __DIR__ . '/../views/inventory/index.php';
    }

    // Hiển thị form tạo mới phiếu nhập/xuất
    public function create($type = 'import') {
        $data = [
            'title' => $type === 'import' ? 'Tạo phiếu nhập kho' : 'Tạo phiếu xuất kho',
            'active_menu' => 'inventory',
            'type' => $type,
            'products' => $this->inventoryModel->getAllProducts()
        ];
        
        require_once __DIR__ . '/../views/inventory/create.php';
    }

    // Xử lý tạo mới phiếu nhập/xuất
    public function store() {
        $type = $_POST['type'] ?? 'import';
        
        $data = [
            'product_id' => $_POST['product_id'],
            'quantity' => $_POST['quantity'],
            'date' => $_POST['date'],
            'user' => $_SESSION['user_id'] ?? 1, // Lấy từ session user đăng nhập
            'note' => $_POST['note'] ?? '',
        ];

        if ($type === 'import') {
            $result = $this->inventoryModel->createImport($data);
        } else {
            $data['reason'] = $_POST['reason'];
            $result = $this->inventoryModel->createExport($data);
        }

        if ($result) {
            $_SESSION['success'] = $type === 'import' 
                ? 'Tạo phiếu nhập kho thành công!' 
                : 'Tạo phiếu xuất kho thành công!';
            header('Location: /inventory?type=' . $type);
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra. Vui lòng thử lại!';
            header('Location: /inventory/create?type=' . $type);
        }
    }

    // Xem chi tiết phiếu nhập/xuất
    public function show($id, $type = 'import') {
        $transaction = $type === 'import' 
            ? $this->inventoryModel->getImportById($id)
            : $this->inventoryModel->getExportById($id);

        if (!$transaction) {
            $_SESSION['error'] = 'Không tìm thấy thông tin phiếu ' . ($type === 'import' ? 'nhập' : 'xuất') . ' kho';
            header('Location: /inventory?type=' . $type);
            exit();
        }

        $data = [
            'title' => 'Chi tiết phiếu ' . ($type === 'import' ? 'nhập' : 'xuất') . ' kho',
            'active_menu' => 'inventory',
            'type' => $type,
            'transaction' => $transaction
        ];
        
        require_once __DIR__ . '/../views/inventory/show.php';
    }
}
