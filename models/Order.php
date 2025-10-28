<?php
/**
 * Class Order - Xử lý các thao tác với đơn hàng
 */
class Order {
    private $conn;
    private $table_name = "orders";
    
    // Order properties
    public $order_id;
    public $order_code;
    public $customer_id;
    public $order_date;
    public $total_amount;
    public $status;
    public $payment_method;
    public $shipping_address;
    public $shipping_note;
    public $cancel_reason;
    public $created_by;
    public $created_at;
    public $updated_at;
    
    // Get database connection
    public function getConnection() {
        return $this->conn;
    }
    
    // Customer information
    public $customer_name;
    public $customer_phone;
    public $customer_email;
    
    // Order items
    public $order_items = [];
    
    // All properties are already declared above
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Tạo mã đơn hàng mới
     */
    private function generateOrderCode() {
        $prefix = 'DH';
        $date = date('Ymd');
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE order_code LIKE :prefix";
        $stmt = $this->conn->prepare($query);
        $like = $prefix . $date . '%';
        $stmt->bindParam(":prefix", $like);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row ? $row['count'] + 1 : 1;
        return $prefix . $date . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Tạo mới đơn hàng
     */
    public function create() {
        // Bắt đầu transaction
        $this->conn->beginTransaction();
        
        try {
            // Tạo mã đơn hàng
            $this->order_code = $this->generateOrderCode();
            
            // Nếu chưa có ngày đặt, sử dụng thời gian hiện tại
            if (empty($this->order_date)) {
                $this->order_date = date('Y-m-d H:i:s');
            }
            
            // Nếu chưa có trạng thái, mặc định là 'Chờ xác nhận'
            if (empty($this->status)) {
                $this->status = 'Chờ xác nhận';
            }
            
            // Nếu chưa có phương thức thanh toán, mặc định là 'COD'
            if (empty($this->payment_method)) {
                $this->payment_method = 'COD';
            }
            
            // Nếu chưa có người tạo, sử dụng session hiện tại
            if (empty($this->created_by) && isset($_SESSION['user']['username'])) {
                $this->created_by = $_SESSION['user']['username'];
            }
            
            // Thêm đơn hàng vào database
            $query = "INSERT INTO " . $this->table_name . " 
                     (order_code, customer_id, order_date, total_amount, status, 
                     payment_method, shipping_address, shipping_note) 
                     VALUES (:order_code, :customer_id, :order_date, :total_amount, :status, 
                     :payment_method, :shipping_address, :shipping_note)";
            
            $stmt = $this->conn->prepare($query);
            
            // Làm sạch dữ liệu
            $this->customer_id = (int)$this->customer_id;
            $this->total_amount = (float)$this->total_amount;
            $this->shipping_address = htmlspecialchars(strip_tags($this->shipping_address));
            $this->shipping_note = !empty($this->shipping_note) ? htmlspecialchars(strip_tags($this->shipping_note)) : null;
            
            // Gán giá trị
            $stmt->bindParam(":order_code", $this->order_code);
            $stmt->bindParam(":customer_id", $this->customer_id);
            $stmt->bindParam(":order_date", $this->order_date);
            $stmt->bindParam(":total_amount", $this->total_amount);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":payment_method", $this->payment_method);
            $stmt->bindParam(":shipping_address", $this->shipping_address);
            $stmt->bindParam(":shipping_note", $this->shipping_note);
            
            if (!$stmt->execute()) {
                throw new Exception("Lỗi khi tạo đơn hàng");
            }
            
            $this->order_id = $this->conn->lastInsertId();
            
            // Thêm chi tiết đơn hàng
            if (!empty($this->order_items) && is_array($this->order_items)) {
                $orderDetail = new OrderDetail($this->conn);
                $orderDetail->order_id = $this->order_id;
                
                foreach ($this->order_items as $item) {
                    $orderDetail->product_id = $item['product_id'];
                    $orderDetail->quantity = $item['quantity'];
                    $orderDetail->unit_price = $item['unit_price'];
                    $orderDetail->total_price = $item['quantity'] * $item['unit_price'];
                    
                    if (!$orderDetail->create()) {
                        throw new Exception("Lỗi khi thêm chi tiết đơn hàng");
                    }
                }
            }
            
            // Commit transaction
            $this->conn->commit();
            
            return $this->order_id;
            
        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            $this->conn->rollBack();
            throw $e;
        }
    }
    
    /**
     * Cập nhật thông tin đơn hàng
     */
    public function update() {
        // Bắt đầu transaction
        $this->conn->beginTransaction();
        
        try {
            // Cập nhật thông tin đơn hàng
            $query = "UPDATE " . $this->table_name . " 
                     SET customer_id = :customer_id,
                         total_amount = :total_amount,
                         status = :status,
                         payment_method = :payment_method,
                         shipping_address = :shipping_address,
                         shipping_note = :shipping_note,
                         updated_at = NOW()";
            
            // Nếu có lý do hủy, thêm vào câu truy vấn
            if (!empty($this->cancel_reason)) {
                $query .= ", cancel_reason = :cancel_reason, 
                          cancelled_by = :cancelled_by, 
                          cancelled_at = NOW()";
            }
            
            // Nếu xác nhận đơn hàng
            if ($this->status === 'Đang xử lý' && empty($this->confirmed_by)) {
                $query .= ", confirmed_by = :confirmed_by, confirmed_at = NOW()";
            }
            
            // Nếu hoàn tất đơn hàng
            if ($this->status === 'Hoàn tất' && empty($this->completed_by)) {
                $query .= ", completed_by = :completed_by, completed_at = NOW()";
            }
            
            $query .= " WHERE order_id = :order_id";
            
            $stmt = $this->conn->prepare($query);
            
            // Làm sạch dữ liệu
            $this->customer_id = (int)$this->customer_id;
            $this->total_amount = (float)$this->total_amount;
            $this->shipping_address = htmlspecialchars(strip_tags($this->shipping_address));
            $this->shipping_note = !empty($this->shipping_note) ? htmlspecialchars(strip_tags($this->shipping_note)) : null;
            
            // Gán giá trị
            $stmt->bindParam(":customer_id", $this->customer_id);
            $stmt->bindParam(":total_amount", $this->total_amount);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":payment_method", $this->payment_method);
            $stmt->bindParam(":shipping_address", $this->shipping_address);
            $stmt->bindParam(":shipping_note", $this->shipping_note);
            $stmt->bindParam(":order_id", $this->order_id, PDO::PARAM_INT);
            
            // Gán giá trị cho lý do hủy nếu có
            if (!empty($this->cancel_reason)) {
                $this->cancel_reason = htmlspecialchars(strip_tags($this->cancel_reason));
                $stmt->bindParam(":cancel_reason", $this->cancel_reason);
                $stmt->bindParam(":cancelled_by", $_SESSION['user']['username']);
            }
            
            // Gán giá trị cho người xác nhận nếu có
            if ($this->status === 'Đang xử lý' && empty($this->confirmed_by)) {
                $stmt->bindValue(":confirmed_by", $_SESSION['user']['username']);
            }
            
            // Gán giá trị cho người hoàn tất nếu có
            if ($this->status === 'Hoàn tất' && empty($this->completed_by)) {
                $stmt->bindValue(":completed_by", $_SESSION['user']['username']);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Lỗi khi cập nhật đơn hàng");
            }
            
            // Nếu có thay đổi chi tiết đơn hàng
            if (!empty($this->order_items) && is_array($this->order_items)) {
                // Xóa tất cả chi tiết cũ
                $orderDetail = new OrderDetail($this->conn);
                $orderDetail->deleteByOrderId($this->order_id);
                
                // Thêm lại chi tiết mới
                $orderDetail->order_id = $this->order_id;
                
                foreach ($this->order_items as $item) {
                    $orderDetail->product_id = $item['product_id'];
                    $orderDetail->quantity = $item['quantity'];
                    $orderDetail->unit_price = $item['unit_price'];
                    $orderDetail->total_price = $item['quantity'] * $item['unit_price'];
                    
                    if (!$orderDetail->create()) {
                        throw new Exception("Lỗi khi cập nhật chi tiết đơn hàng");
                    }
                }
            }
            
            // Commit transaction
            $this->conn->commit();
            
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            $this->conn->rollBack();
            throw $e;
        }
    }
    
    /**
     * Lấy thông tin đơn hàng theo ID
     */
    public function getById($id) {
        $query = "SELECT o.*, c.fullname as customer_name, c.phone as customer_phone, c.email as customer_email 
                 FROM " . $this->table_name . " o
                 LEFT JOIN customers c ON o.customer_id = c.customer_id
                 WHERE o.order_id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Gán giá trị cho đối tượng
            $this->order_id = $row['order_id'];
            $this->order_code = $row['order_code'];
            $this->customer_id = $row['customer_id'];
            $this->order_date = $row['order_date'];
            $this->total_amount = $row['total_amount'];
            $this->status = $row['status'];
            $this->payment_method = $row['payment_method'];
            $this->shipping_address = $row['shipping_address'];
            $this->shipping_note = $row['shipping_note'];
            $this->cancel_reason = $row['cancel_reason'];
            $this->created_by = isset($row['created_by']) ? $row['created_by'] : null;
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            // Thông tin khách hàng
            $this->customer_name = $row['customer_name'];
            $this->customer_phone = $row['customer_phone'];
            $this->customer_email = $row['customer_email'];
            
            // Lấy chi tiết đơn hàng
            $orderDetail = new OrderDetail($this->conn);
            $this->order_items = $orderDetail->getByOrderId($this->order_id);
            
            return $row;
        }
        
        return false;
    }
    
    /**
     * Lấy tất cả đơn hàng
     */
    public function getAll($filters = [], $limit = 10, $offset = 0) {
        $query = "SELECT o.*, c.fullname as customer_name, c.phone as customer_phone 
                 FROM " . $this->table_name . " o
                 LEFT JOIN customers c ON o.customer_id = c.customer_id
                 WHERE 1=1";
        
        $params = [];
        
        // Áp dụng bộ lọc nếu có
        if (!empty($filters['search'])) {
            $query .= " AND (o.order_code LIKE :search OR c.fullname LIKE :search OR c.phone LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND o.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $query .= " AND DATE(o.order_date) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " AND DATE(o.order_date) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        // Sắp xếp
        $query .= " ORDER BY o.order_date DESC";
        
        // Phân trang
        if ($limit > 0) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind các tham số
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($limit > 0) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Đếm tổng số đơn hàng
     */
    public function countAll($filters = []) {
        $query = "SELECT COUNT(*) as total 
                 FROM " . $this->table_name . " o
                 LEFT JOIN customers c ON o.customer_id = c.customer_id
                 WHERE 1=1";
        
        $params = [];
        
        // Áp dụng bộ lọc nếu có
        if (!empty($filters['search'])) {
            $query .= " AND (o.order_code LIKE :search OR c.fullname LIKE :search OR c.phone LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND o.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $query .= " AND DATE(o.order_date) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " AND DATE(o.order_date) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
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
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus($order_id, $new_status, $cancel_reason = null) {
        $query = "UPDATE " . $this->table_name . " 
                 SET status = :status, updated_at = NOW()";
        
        // Thêm lý do hủy nếu có
        if (!empty($cancel_reason)) {
            $query .= ", cancel_reason = :cancel_reason, 
                      cancelled_by = :cancelled_by, 
                      cancelled_at = NOW()";
        }
        
        // Nếu xác nhận đơn hàng
        if ($new_status === 'Đang xử lý') {
            $query .= ", confirmed_by = :confirmed_by, confirmed_at = NOW()";
        }
        
        // Nếu hoàn tất đơn hàng
        if ($new_status === 'Hoàn tất') {
            $query .= ", completed_by = :completed_by, completed_at = NOW()";
        }
        
        $query .= " WHERE order_id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Gán giá trị
        $stmt->bindParam(":status", $new_status);
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
        
        // Gán giá trị cho lý do hủy nếu có
        if (!empty($cancel_reason)) {
            $cancel_reason = htmlspecialchars(strip_tags($cancel_reason));
            $stmt->bindParam(":cancel_reason", $cancel_reason);
            $stmt->bindValue(":cancelled_by", $_SESSION['user']['username']);
        }
        
        // Gán giá trị cho người xác nhận nếu có
        if ($new_status === 'Đang xử lý') {
            $stmt->bindValue(":confirmed_by", $_SESSION['user']['username']);
        }
        
        // Gán giá trị cho người hoàn tất nếu có
        if ($new_status === 'Hoàn tất') {
            $stmt->bindValue(":completed_by", $_SESSION['user']['username']);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Xóa đơn hàng (chỉ xóa được khi đơn hàng chưa được xác nhận)
     */
    public function delete($order_id) {
        // Kiểm tra xem đơn hàng có thể xóa không (chỉ xóa được khi đơn hàng chưa được xác nhận)
        $query = "SELECT status FROM " . $this->table_name . " WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception("Đơn hàng không tồn tại");
        }
        
        if (!in_array($order['status'], ['Chờ xác nhận'])) {
            throw new Exception("Chỉ có thể xóa đơn hàng ở trạng thái 'Chờ xác nhận'");
        }
        
        // Bắt đầu transaction
        $this->conn->beginTransaction();
        
        try {
            // Xóa chi tiết đơn hàng
            $orderDetail = new OrderDetail($this->conn);
            if (!$orderDetail->deleteByOrderId($order_id)) {
                throw new Exception("Lỗi khi xóa chi tiết đơn hàng");
            }
            
            // Xóa đơn hàng
            $query = "DELETE FROM " . $this->table_name . " WHERE order_id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception("Lỗi khi xóa đơn hàng");
            }
            
            // Commit transaction
            $this->conn->commit();
            
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            $this->conn->rollBack();
            throw $e;
        }
    }
    
    /**
     * Lấy thống kê đơn hàng
     */
    public function getStatistics() {
        $stats = [
            'total_orders' => 0,
            'pending_orders' => 0,
            'processing_orders' => 0,
            'shipping_orders' => 0,
            'completed_orders' => 0,
            'cancelled_orders' => 0,
            'total_revenue' => 0,
            'today_orders' => 0,
            'today_revenue' => 0
        ];
        
        // Tổng số đơn hàng
        $query = "SELECT COUNT(*) as total, 
                         SUM(CASE WHEN status = 'Chờ xác nhận' THEN 1 ELSE 0 END) as pending,
                         SUM(CASE WHEN status = 'Đang xử lý' THEN 1 ELSE 0 END) as processing,
                         SUM(CASE WHEN status = 'Đang giao' THEN 1 ELSE 0 END) as shipping,
                         SUM(CASE WHEN status = 'Hoàn tất' THEN 1 ELSE 0 END) as completed,
                         SUM(CASE WHEN status = 'Đã hủy' THEN 1 ELSE 0 END) as cancelled,
                         COALESCE(SUM(total_amount), 0) as total_revenue
                  FROM " . $this->table_name . "
                  WHERE status != 'Đã hủy'";
        
        $stmt = $this->conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $stats['total_orders'] = (int)$result['total'];
            $stats['pending_orders'] = (int)$result['pending'];
            $stats['processing_orders'] = (int)$result['processing'];
            $stats['shipping_orders'] = (int)$result['shipping'];
            $stats['completed_orders'] = (int)$result['completed'];
            $stats['cancelled_orders'] = (int)$result['cancelled'];
            $stats['total_revenue'] = (float)$result['total_revenue'];
        }
        
        // Đơn hàng hôm nay
        $query = "SELECT COUNT(*) as total_orders, 
                         COALESCE(SUM(total_amount), 0) as total_revenue
                  FROM " . $this->table_name . "
                  WHERE DATE(order_date) = CURDATE()
                  AND status != 'Đã hủy'";
        
        $stmt = $this->conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $stats['today_orders'] = (int)$result['total_orders'];
            $stats['today_revenue'] = (float)$result['total_revenue'];
        }
        
        return $stats;
    }
}
