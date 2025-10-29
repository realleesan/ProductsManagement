<?php
/**
 * Model Customer - Quản lý khách hàng
 * Chủ yếu phục vụ cho việc thêm khách hàng nhanh trong quá trình tạo đơn hàng
 */

class Customer {
    private $conn;
    private $table_name = "customers";
    
    // Thuộc tính
    public $customer_id;
    public $customer_code;
    public $fullname;
    public $phone;
    public $email;
    public $address;
    public $status;
    public $created_at;
    public $updated_at;
    
    /**
     * Constructor
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Tạo mã khách hàng mới
     */
    private function generateCustomerCode() {
        $prefix = 'KH';
        $year = date('Y');
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE customer_code LIKE :prefix";
        $stmt = $this->conn->prepare($query);
        $like = $prefix . $year . '%';
        $stmt->bindParam(":prefix", $like);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row ? $row['count'] + 1 : 1;
        return $prefix . $year . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Lấy tất cả khách hàng (chỉ lấy khách hàng đang hoạt động)
     */
    public function getAll() {
        $query = "SELECT customer_id, customer_code, fullname, phone, email, address, status
                  FROM " . $this->table_name . " 
                  WHERE status = 'Active'
                  ORDER BY fullname ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Lấy thông tin khách hàng theo ID
     */
    public function getById($id) {
        $query = "SELECT customer_id, customer_code, fullname, phone, email, address, status, created_at, updated_at
                  FROM " . $this->table_name . " 
                  WHERE customer_id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Gán giá trị cho đối tượng
            $this->customer_id = $row['customer_id'];
            $this->customer_code = $row['customer_code'];
            $this->fullname = $row['fullname'];
            $this->phone = $row['phone'];
            $this->email = $row['email'];
            $this->address = $row['address'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return $row;
        }
        
        return false;
    }
    
    /**
     * Tạo khách hàng mới
     */
    public function create() {
        try {
            // Tạo mã khách hàng
            $this->customer_code = $this->generateCustomerCode();
            
            // Kiểm tra email trùng lặp nếu có
            if (!empty($this->email)) {
                $checkQuery = "SELECT customer_id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->bindParam(':email', $this->email);
                $checkStmt->execute();
                
                if ($checkStmt->rowCount() > 0) {
                    throw new Exception("Email đã tồn tại trong hệ thống");
                }
            }
            
            // Kiểm tra số điện thoại trùng lặp
            $checkPhoneQuery = "SELECT customer_id FROM " . $this->table_name . " WHERE phone = :phone LIMIT 1";
            $checkPhoneStmt = $this->conn->prepare($checkPhoneQuery);
            $checkPhoneStmt->bindParam(':phone', $this->phone);
            $checkPhoneStmt->execute();
            
            if ($checkPhoneStmt->rowCount() > 0) {
                throw new Exception("Số điện thoại đã tồn tại trong hệ thống");
            }
            
            // Tạo khách hàng mới
            $query = "INSERT INTO " . $this->table_name . " 
                     (customer_code, fullname, phone, email, address, status) 
                     VALUES (:customer_code, :fullname, :phone, :email, :address, :status)";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':customer_code', $this->customer_code);
            $stmt->bindParam(':fullname', $this->fullname);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':status', $this->status);
            
            if ($stmt->execute()) {
                $this->customer_id = $this->conn->lastInsertId();
                return $this->customer_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Cập nhật thông tin khách hàng
     */
    public function update() {
        try {
            // Kiểm tra email trùng lặp nếu có (trừ khách hàng hiện tại)
            if (!empty($this->email)) {
                $checkQuery = "SELECT customer_id FROM " . $this->table_name . " 
                              WHERE email = :email AND customer_id != :customer_id LIMIT 1";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->bindParam(':email', $this->email);
                $checkStmt->bindParam(':customer_id', $this->customer_id, PDO::PARAM_INT);
                $checkStmt->execute();
                
                if ($checkStmt->rowCount() > 0) {
                    throw new Exception("Email đã tồn tại trong hệ thống");
                }
            }
            
            // Kiểm tra số điện thoại trùng lặp (trừ khách hàng hiện tại)
            $checkPhoneQuery = "SELECT customer_id FROM " . $this->table_name . " 
                               WHERE phone = :phone AND customer_id != :customer_id LIMIT 1";
            $checkPhoneStmt = $this->conn->prepare($checkPhoneQuery);
            $checkPhoneStmt->bindParam(':phone', $this->phone);
            $checkPhoneStmt->bindParam(':customer_id', $this->customer_id, PDO::PARAM_INT);
            $checkPhoneStmt->execute();
            
            if ($checkPhoneStmt->rowCount() > 0) {
                throw new Exception("Số điện thoại đã tồn tại trong hệ thống");
            }
            
            $query = "UPDATE " . $this->table_name . " 
                     SET fullname = :fullname, phone = :phone, email = :email, 
                         address = :address, status = :status, updated_at = CURRENT_TIMESTAMP
                     WHERE customer_id = :customer_id";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':fullname', $this->fullname);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':customer_id', $this->customer_id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Xóa khách hàng (chỉ đổi trạng thái thành Blocked)
     */
    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET status = 'Blocked', updated_at = CURRENT_TIMESTAMP
                 WHERE customer_id = :customer_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':customer_id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Đếm tổng số khách hàng
     */
    public function countAll($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";
        
        // Thêm điều kiện lọc nếu có
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query .= " AND (fullname LIKE :search OR phone LIKE :search OR email LIKE :search)";
        }
        
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query .= " AND status = :status";
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $stmt->bindParam(':search', $search);
        }
        
        if (isset($filters['status']) && !empty($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
    
    /**
     * Lấy thống kê khách hàng
     */
    public function getStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_customers,
                    COUNT(CASE WHEN status = 'Active' THEN 1 END) as active_customers,
                    COUNT(CASE WHEN status = 'Blocked' THEN 1 END) as blocked_customers,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_customers_30days
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
