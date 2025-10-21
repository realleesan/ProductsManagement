<?php
/**
 * Class OrderDetail - Xử lý các thao tác với chi tiết đơn hàng
 */
class OrderDetail {
    private $conn;
    private $table_name = "order_details";
    
    // Thuộc tính
    public $detail_id;
    public $order_id;
    public $product_id;
    public $quantity;
    public $unit_price;
    public $total_price;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Thêm chi tiết đơn hàng
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (order_id, product_id, quantity, unit_price, total_price) 
                 VALUES (:order_id, :product_id, :quantity, :unit_price, :total_price)";
        
        $stmt = $this->conn->prepare($query);
        
        // Làm sạch dữ liệu
        $this->order_id = (int)$this->order_id;
        $this->product_id = (int)$this->product_id;
        $this->quantity = (int)$this->quantity;
        $this->unit_price = (float)$this->unit_price;
        $this->total_price = (float)$this->total_price;
        
        // Gán giá trị
        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit_price", $this->unit_price);
        $stmt->bindParam(":total_price", $this->total_price);
        
        if ($stmt->execute()) {
            $this->detail_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Lấy chi tiết đơn hàng theo ID đơn hàng
     */
    public function getByOrderId($order_id) {
        $query = "SELECT d.*, p.product_code, p.product_name, p.image_url 
                 FROM " . $this->table_name . " d
                 JOIN products p ON d.product_id = p.product_id
                 WHERE d.order_id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Xóa tất cả chi tiết đơn hàng theo ID đơn hàng
     */
    public function deleteByOrderId($order_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Cập nhật số lượng sản phẩm trong đơn hàng
     */
    public function updateQuantity($detail_id, $quantity) {
        // Lấy thông tin chi tiết đơn hàng
        $query = "SELECT * FROM " . $this->table_name . " WHERE detail_id = :detail_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':detail_id', $detail_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            return false;
        }
        
        $order_detail = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Cập nhật số lượng và tổng tiền
        $new_quantity = (int)$quantity;
        $unit_price = (float)$order_detail['unit_price'];
        $total_price = $unit_price * $new_quantity;
        
        $query = "UPDATE " . $this->table_name . " 
                 SET quantity = :quantity, total_price = :total_price 
                 WHERE detail_id = :detail_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
        $stmt->bindParam(':total_price', $total_price);
        $stmt->bindParam(':detail_id', $detail_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Xóa một sản phẩm khỏi đơn hàng
     */
    public function delete($detail_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE detail_id = :detail_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':detail_id', $detail_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Tính tổng tiền của đơn hàng
     */
    public function getOrderTotal($order_id) {
        $query = "SELECT SUM(total_price) as total FROM " . $this->table_name . " 
                 WHERE order_id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['total'] : 0;
    }
    
    /**
     * Đếm số lượng sản phẩm trong đơn hàng
     */
    public function countItems($order_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE order_id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['count'] : 0;
    }
}
