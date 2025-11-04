<?php
/**
 * Class Inventory - Xử lý các thao tác liên quan đến quản lý kho
 */
class Inventory {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Lấy tồn kho hiện tại của sản phẩm
     */
    public function getCurrentStock($product_id) {
        $query = "SELECT stock_quantity FROM products WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['stock_quantity'] : 0;
    }
    
    /**
     * Cập nhật tồn kho sản phẩm
     */
    public function updateStock($product_id, $quantity_change, $type = 'import') {
        $current_stock = $this->getCurrentStock($product_id);
        
        // Xử lý số lượng thay đổi (có thể âm hoặc dương)
        if ($type === 'import') {
            $new_stock = $current_stock + $quantity_change;
        } else {
            $new_stock = $current_stock - $quantity_change;
        }
            
        // Đảm bảo tồn kho không âm
        $new_stock = max(0, $new_stock);
        
        $query = "UPDATE products SET stock_quantity = :stock_quantity, 
                  status = CASE 
                    WHEN stock_quantity <= 0 AND :new_stock_1 > 0 THEN 'Active' 
                    WHEN stock_quantity > 0 AND :new_stock_2 <= 0 THEN 'Out of stock' 
                    ELSE status 
                  END 
                  WHERE product_id = :product_id";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":stock_quantity", $new_stock);
        $stmt->bindValue(":new_stock_1", $new_stock);
        $stmt->bindValue(":new_stock_2", $new_stock);
        $stmt->bindValue(":product_id", $product_id);
        
        return $stmt->execute();
    }
    
    /**
     * Lấy lịch sử nhập/xuất kho
     */
    public function getInventoryHistory($product_id = null, $type = null, $start_date = null, $end_date = null, $limit = 10, $offset = 0) {
        $query = "SELECT h.*, p.product_name, p.product_code,
                         COALESCE(wi.unit_price, we.unit_price, 0) as unit_price,
                         COALESCE(wi.total_amount, we.total_amount, 0) as total_amount
                 FROM warehouse_history h 
                 LEFT JOIN products p ON h.product_id = p.product_id 
                 LEFT JOIN warehouse_import wi ON h.reference_code = wi.import_code AND h.action_type = 'Import'
                 LEFT JOIN warehouse_export we ON h.reference_code = we.export_code AND h.action_type = 'Export'
                 WHERE 1=1";
                 
        $params = [];
        
        if ($product_id) {
            $query .= " AND h.product_id = :product_id";
            $params[':product_id'] = $product_id;
        }
        
        if ($type) {
            $query .= " AND h.action_type = :action_type";
            $params[':action_type'] = $type;
        }
        
        if ($start_date) {
            $query .= " AND DATE(h.action_at) >= :start_date";
            $params[':start_date'] = $start_date;
        }
        
        if ($end_date) {
            $query .= " AND DATE(h.action_at) <= :end_date";
            $params[':end_date'] = $end_date;
        }
        
        $query .= " ORDER BY h.action_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind các tham số
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Lấy tổng số bản ghi lịch sử
     */
    public function countInventoryHistory($product_id = null, $type = null, $start_date = null, $end_date = null) {
        $query = "SELECT COUNT(*) as total 
                 FROM warehouse_history h 
                 WHERE 1=1";
                 
        $params = [];
        
        if ($product_id) {
            $query .= " AND h.product_id = :product_id";
            $params[':product_id'] = $product_id;
        }
        
        if ($type) {
            $query .= " AND h.action_type = :action_type";
            $params[':action_type'] = $type;
        }
        
        if ($start_date) {
            $query .= " AND DATE(h.action_at) >= :start_date";
            $params[':start_date'] = $start_date;
        }
        
        if ($end_date) {
            $query .= " AND DATE(h.action_at) <= :end_date";
            $params[':end_date'] = $end_date;
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind các tham số
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? (int)$row['total'] : 0;
    }
    
    /**
     * Lấy thống kê tồn kho
     */
    public function getInventoryStatistics() {
        $stats = [
            'total_products' => 0,
            'total_stock' => 0,
            'out_of_stock' => 0,
            'low_stock' => 0,
            'expiring_soon' => 0
        ];
        
        // Tổng số sản phẩm
        $query = "SELECT COUNT(*) as total FROM products WHERE status != 'Disabled'";
        $stmt = $this->conn->query($query);
        $stats['total_products'] = (int)$stmt->fetchColumn();
        
        // Tổng tồn kho (bao gồm cả sản phẩm hết hạn)
        $query = "SELECT SUM(stock_quantity) as total FROM products WHERE status IN ('Active', 'Expired')";
        $stmt = $this->conn->query($query);
        $stats['total_stock'] = (int)$stmt->fetchColumn();
        
        // Sản phẩm hết hàng
        $query = "SELECT COUNT(*) as total FROM products WHERE status = 'Out of stock'";
        $stmt = $this->conn->query($query);
        $stats['out_of_stock'] = (int)$stmt->fetchColumn();
        
        // Sản phẩm sắp hết hàng (dưới 20 sản phẩm, bao gồm cả sản phẩm hết hạn)
        $query = "SELECT COUNT(*) as total FROM products WHERE stock_quantity > 0 AND stock_quantity <= 20 AND status IN ('Active', 'Expired')";
        $stmt = $this->conn->query($query);
        $stats['low_stock'] = (int)$stmt->fetchColumn();
        
        // Sản phẩm hết hạn
        $query = "SELECT COUNT(*) as total FROM products WHERE status = 'Expired'";
        $stmt = $this->conn->query($query);
        $stats['expired'] = (int)$stmt->fetchColumn();
        
        // Sản phẩm sắp hết hạn (trong vòng 30 ngày tới)
        $query = "SELECT COUNT(*) as total FROM products 
                 WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
                 AND status = 'Active'";
        $stmt = $this->conn->query($query);
        $stats['expiring_soon'] = (int)$stmt->fetchColumn();
        
        return $stats;
    }
}
