<?php
class Order {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    // Lấy danh sách đơn hàng
    public function getAllOrders($filters = []) {
        $query = "SELECT o.*, c.customer_name, c.phone, c.email, 
                         u.fullname as created_by_name, 
                         COUNT(oi.item_id) as item_count,
                         SUM(oi.quantity * oi.price) as total_amount
                  FROM orders o
                  LEFT JOIN customers c ON o.customer_id = c.customer_id
                  LEFT JOIN users u ON o.created_by = u.user_id
                  LEFT JOIN order_items oi ON o.order_id = oi.order_id";
        
        $where = [];
        $params = [];
        
        // Áp dụng bộ lọc nếu có
        if (!empty($filters['status'])) {
            $where[] = "o.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['from_date'])) {
            $where[] = "o.order_date >= :from_date";
            $params[':from_date'] = $filters['from_date'] . ' 00:00:00';
        }
        
        if (!empty($filters['to_date'])) {
            $where[] = "o.order_date <= :to_date";
            $params[':to_date'] = $filters['to_date'] . ' 23:59:59';
        }
        
        if (!empty($filters['customer_id'])) {
            $where[] = "o.customer_id = :customer_id";
            $params[':customer_id'] = $filters['customer_id'];
        }
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }
        
        $query .= " GROUP BY o.order_id ORDER BY o.order_date DESC";
        
        $stmt = $this->db->prepare($query);
        
        // Bind các tham số
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy chi tiết đơn hàng
    public function getOrderById($orderId) {
        // Lấy thông tin cơ bản của đơn hàng
        $query = "SELECT o.*, c.customer_name, c.phone, c.email, c.address,
                         u.fullname as created_by_name, u.phone as created_by_phone,
                         (SELECT SUM(quantity * price) FROM order_items WHERE order_id = :order_id) as total_amount
                  FROM orders o
                  LEFT JOIN customers c ON o.customer_id = c.customer_id
                  LEFT JOIN users u ON o.created_by = u.user_id
                  WHERE o.order_id = :order_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return null;
        }
        
        // Lấy danh sách sản phẩm trong đơn hàng
        $query = "SELECT oi.*, p.product_name, p.product_code, p.image_url
                 FROM order_items oi
                 JOIN products p ON oi.product_id = p.product_id
                 WHERE oi.order_id = :order_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Lấy lịch sử cập nhật đơn hàng
        $query = "SELECT oh.*, u.fullname as updated_by_name
                 FROM order_history oh
                 LEFT JOIN users u ON oh.updated_by = u.user_id
                 WHERE oh.order_id = :order_id
                 ORDER BY oh.updated_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        $order['history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $order;
    }
    
    // Tạo đơn hàng mới
    public function createOrder($data) {
        try {
            $this->db->beginTransaction();
            
            // Tạo mã đơn hàng
            $orderCode = 'DH' . date('Ymd') . strtoupper(uniqid());
            
            // Thêm đơn hàng
            $query = "INSERT INTO orders 
                     (order_code, customer_id, order_date, delivery_date, 
                      payment_method, shipping_address, note, status, 
                      created_by, created_at, updated_at)
                     VALUES 
                     (:order_code, :customer_id, :order_date, :delivery_date, 
                      :payment_method, :shipping_address, :note, :status, 
                      :created_by, NOW(), NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':order_code', $orderCode);
            $stmt->bindParam(':customer_id', $data['customer_id'], PDO::PARAM_INT);
            $stmt->bindParam(':order_date', $data['order_date']);
            $stmt->bindParam(':delivery_date', $data['delivery_date']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->bindParam(':shipping_address', $data['shipping_address']);
            $stmt->bindParam(':note', $data['note']);
            $stmt->bindValue(':status', 'pending');
            $stmt->bindParam(':created_by', $_SESSION['user_id'] ?? 1, PDO::PARAM_INT);
            $stmt->execute();
            
            $orderId = $this->db->lastInsertId();
            
            // Thêm các sản phẩm vào đơn hàng
            $this->addOrderItems($orderId, $data['items']);
            
            // Thêm vào lịch sử đơn hàng
            $this->addOrderHistory($orderId, 'pending', 'Tạo đơn hàng mới');
            
            $this->db->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating order: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Cập nhật đơn hàng
    public function updateOrder($orderId, $data) {
        try {
            $this->db->beginTransaction();
            
            // Cập nhật thông tin đơn hàng
            $query = "UPDATE orders SET
                     customer_id = :customer_id,
                     delivery_date = :delivery_date,
                     payment_method = :payment_method,
                     shipping_address = :shipping_address,
                     note = :note,
                     updated_at = NOW()
                     WHERE order_id = :order_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':customer_id', $data['customer_id'], PDO::PARAM_INT);
            $stmt->bindParam(':delivery_date', $data['delivery_date']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->bindParam(':shipping_address', $data['shipping_address']);
            $stmt->bindParam(':note', $data['note']);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Xóa các sản phẩm cũ và thêm lại
            $this->deleteOrderItems($orderId);
            $this->addOrderItems($orderId, $data['items']);
            
            // Thêm vào lịch sử đơn hàng
            $this->addOrderHistory($orderId, 'updated', 'Cập nhật thông tin đơn hàng');
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating order: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Cập nhật trạng thái đơn hàng
    public function updateOrderStatus($orderId, $status, $note = '') {
        try {
            $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($status, $statuses)) {
                throw new Exception('Trạng thái không hợp lệ');
            }
            
            $query = "UPDATE orders SET 
                     status = :status,
                     updated_at = NOW()
                     WHERE order_id = :order_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Thêm vào lịch sử đơn hàng
            $statusLabels = [
                'pending' => 'Chờ xử lý',
                'processing' => 'Đang xử lý',
                'shipped' => 'Đã giao hàng',
                'delivered' => 'Đã nhận hàng',
                'cancelled' => 'Đã hủy'
            ];
            
            $statusText = $statusLabels[$status] ?? $status;
            $historyNote = $note ?: "Cập nhật trạng thái: " . $statusText;
            
            $this->addOrderHistory($orderId, $status, $historyNote);
            
            // Nếu đơn hàng bị hủy hoặc đã giao, cập nhật lại tồn kho
            if ($status === 'cancelled' || $status === 'delivered') {
                $this->updateInventoryOnOrderStatusChange($orderId, $status);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Hủy đơn hàng
    public function cancelOrder($orderId, $reason = '') {
        return $this->updateOrderStatus($orderId, 'cancelled', $reason ? "Hủy đơn hàng: $reason" : 'Hủy đơn hàng');
    }
    
    // Xóa đơn hàng (chỉ xóa khi ở trạng thái chờ xử lý)
    public function deleteOrder($orderId) {
        try {
            // Kiểm tra xem đơn hàng có thể xóa không
            $order = $this->getOrderById($orderId);
            if (!$order) {
                throw new Exception('Đơn hàng không tồn tại');
            }
            
            if ($order['status'] !== 'pending') {
                throw new Exception('Chỉ được phép xóa đơn hàng ở trạng thái chờ xử lý');
            }
            
            $this->db->beginTransaction();
            
            // Xóa các sản phẩm trong đơn hàng
            $this->deleteOrderItems($orderId);
            
            // Xóa lịch sử đơn hàng
            $stmt = $this->db->prepare("DELETE FROM order_history WHERE order_id = :order_id");
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Xóa đơn hàng
            $stmt = $this->db->prepare("DELETE FROM orders WHERE order_id = :order_id");
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting order: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Thêm sản phẩm vào đơn hàng
    private function addOrderItems($orderId, $items) {
        if (empty($items)) {
            return;
        }
        
        $query = "INSERT INTO order_items 
                 (order_id, product_id, quantity, price, note, created_at)
                 VALUES 
                 (:order_id, :product_id, :quantity, :price, :note, NOW())";
        
        $stmt = $this->db->prepare($query);
        
        foreach ($items as $item) {
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':price', $item['price']);
            $stmt->bindValue(':note', $item['note'] ?? '');
            $stmt->execute();
        }
    }
    
    // Xóa tất cả sản phẩm trong đơn hàng
    private function deleteOrderItems($orderId) {
        $query = "DELETE FROM order_items WHERE order_id = :order_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    // Thêm lịch sử đơn hàng
    private function addOrderHistory($orderId, $status, $note = '') {
        $query = "INSERT INTO order_history 
                 (order_id, status, note, updated_by, updated_at)
                 VALUES 
                 (:order_id, :status, :note, :updated_by, NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':note', $note);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? 1, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    // Cập nhật tồn kho khi trạng thái đơn hàng thay đổi
    private function updateInventoryOnOrderStatusChange($orderId, $newStatus) {
        $order = $this->getOrderById($orderId);
        if (!$order || empty($order['items'])) {
            return;
        }
        
        // Nếu đơn hàng bị hủy, trả lại số lượng tồn kho
        if ($newStatus === 'cancelled') {
            $this->returnItemsToInventory($orderId);
        }
        // Nếu đơn hàng đã giao, trừ số lượng tồn kho
        elseif ($newStatus === 'delivered') {
            $this->deductItemsFromInventory($orderId);
        }
    }
    
    // Trả sản phẩm về kho khi hủy đơn hàng
    private function returnItemsToInventory($orderId) {
        $query = "SELECT oi.product_id, oi.quantity, p.product_name
                 FROM order_items oi
                 JOIN products p ON oi.product_id = p.product_id
                 WHERE oi.order_id = :order_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($items as $item) {
            $updateQuery = "UPDATE products 
                           SET stock_quantity = stock_quantity + :quantity,
                               updated_at = NOW()
                           WHERE product_id = :product_id";
            
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $updateStmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
            $updateStmt->execute();
        }
    }
    
    // Trừ số lượng tồn kho khi đơn hàng đã giao
    private function deductItemsFromInventory($orderId) {
        $query = "SELECT oi.product_id, oi.quantity, p.product_name, p.stock_quantity
                 FROM order_items oi
                 JOIN products p ON oi.product_id = p.product_id
                 WHERE oi.order_id = :order_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($items as $item) {
            if ($item['stock_quantity'] < $item['quantity']) {
                throw new Exception("Không đủ tồn kho cho sản phẩm: " . $item['product_name']);
            }
            
            $updateQuery = "UPDATE products 
                           SET stock_quantity = stock_quantity - :quantity,
                               updated_at = NOW()
                           WHERE product_id = :product_id";
            
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $updateStmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
            $updateStmt->execute();
        }
    }
    
    // Lấy thống kê đơn hàng
    public function getOrderStats($startDate = null, $endDate = null) {
        $stats = [
            'total_orders' => 0,
            'total_revenue' => 0,
            'pending_orders' => 0,
            'processing_orders' => 0,
            'shipped_orders' => 0,
            'delivered_orders' => 0,
            'cancelled_orders' => 0,
            'orders_by_month' => [],
            'revenue_by_month' => [],
            'top_products' => []
        ];
        
        // Điều kiện thời gian
        $where = [];
        $params = [];
        
        if ($startDate) {
            $where[] = "o.order_date >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $where[] = "o.order_date <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Tổng số đơn hàng và doanh thu
        $query = "SELECT 
                    COUNT(DISTINCT o.order_id) as total_orders,
                    COALESCE(SUM(oi.quantity * oi.price), 0) as total_revenue
                  FROM orders o
                  LEFT JOIN order_items oi ON o.order_id = oi.order_id
                  $whereClause";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['total_orders'] = (int)$result['total_orders'];
        $stats['total_revenue'] = (float)$result['total_revenue'];
        
        // Thống kê theo trạng thái
        $statusQuery = "SELECT 
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                        SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
                       FROM orders";
        
        $stmt = $this->db->query($statusQuery);
        $statusStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['pending_orders'] = (int)$statusStats['pending_orders'];
        $stats['processing_orders'] = (int)$statusStats['processing_orders'];
        $stats['shipped_orders'] = (int)$statusStats['shipped_orders'];
        $stats['delivered_orders'] = (int)$statusStats['delivered_orders'];
        $stats['cancelled_orders'] = (int)$statusStats['cancelled_orders'];
        
        // Thống kê đơn hàng theo tháng (12 tháng gần nhất)
        $endDate = $endDate ?: date('Y-m-d');
        $startDate = $startDate ?: date('Y-m-d', strtotime('-12 months', strtotime($endDate)));
        
        $monthlyQuery = "SELECT 
            DATE_FORMAT(order_date, '%Y-%m') as month,
            COUNT(DISTINCT o.order_id) as order_count,
            COALESCE(SUM(oi.quantity * oi.price), 0) as revenue
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            WHERE order_date BETWEEN :start_date AND :end_date
            GROUP BY DATE_FORMAT(order_date, '%Y-%m')
            ORDER BY month ASC";
        
        $stmt = $this->db->prepare($monthlyQuery);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($monthlyData as $row) {
            $stats['orders_by_month'][$row['month']] = (int)$row['order_count'];
            $stats['revenue_by_month'][$row['month']] = (float)$row['revenue'];
        }
        
        // Top sản phẩm bán chạy
        $topProductsQuery = "SELECT 
            p.product_id,
            p.product_name,
            p.product_code,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.quantity * oi.price) as total_revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.status NOT IN ('cancelled')
            GROUP BY p.product_id, p.product_name, p.product_code
            ORDER BY total_quantity DESC
            LIMIT 5";
        
        $stmt = $this->db->query($topProductsQuery);
        $stats['top_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    // Lấy danh sách khách hàng
    public function getCustomers($search = '') {
        $query = "SELECT customer_id, customer_name, phone, email, address 
                 FROM customers 
                 WHERE customer_name LIKE :search 
                 OR phone LIKE :search 
                 OR email LIKE :search
                 ORDER BY customer_name ASC";
        
        $stmt = $this->db->prepare($query);
        $searchTerm = "%$search%";
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Tìm kiếm sản phẩm
    public function searchProducts($search = '') {
        $query = "SELECT product_id, product_code, product_name, price, stock_quantity 
                 FROM products 
                 WHERE (product_name LIKE :search OR product_code LIKE :search)
                 AND is_active = 1
                 ORDER BY product_name ASC";
        
        $stmt = $this->db->prepare($query);
        $searchTerm = "%$search%";
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
