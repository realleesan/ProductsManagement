<?php
/**
 * Class Customer - Xử lý các thao tác với khách hàng
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
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Tạo mã khách hàng mới
     */
    private function generateCustomerCode() {
        $prefix = 'KH';
        $year = date('Y');
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE customer_code LIKE :prefix";
        $stmt = $this->conn->prepare($query);
        $like = $prefix . '%';
        $stmt->bindParam(":prefix", $like);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row['count'] + 1;
        return $prefix . $year . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Kiểm tra số điện thoại đã tồn tại chưa
     */
    public function phoneExists($phone, $exclude_id = null) {
        $query = "SELECT customer_id FROM " . $this->table_name . " 
                 WHERE phone = :phone" . 
                 ($exclude_id ? " AND customer_id != :exclude_id" : "") . 
                 " LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":phone", $phone);
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Tạo mới khách hàng
     */
    public function create() {
        // Tạo mã khách hàng
        $this->customer_code = $this->generateCustomerCode();
        
        // Kiểm tra số điện thoại đã tồn tại chưa
        if ($this->phoneExists($this->phone)) {
            throw new Exception("Số điện thoại đã được đăng ký cho khách hàng khác");
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                 (customer_code, fullname, phone, email, address, status) 
                 VALUES (:customer_code, :fullname, :phone, :email, :address, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        // Làm sạch dữ liệu
        $this->fullname = htmlspecialchars(strip_tags($this->fullname));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->status = $this->status ?: 'Active';
        
        // Gán giá trị
        $stmt->bindParam(":customer_code", $this->customer_code);
        $stmt->bindParam(":fullname", $this->fullname);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":status", $this->status);
        
        if ($stmt->execute()) {
            $this->customer_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Cập nhật thông tin khách hàng
     */
    public function update() {
        // Kiểm tra số điện thoại đã tồn tại chưa (trừ chính nó)
        if ($this->phoneExists($this->phone, $this->customer_id)) {
            throw new Exception("Số điện thoại đã được đăng ký cho khách hàng khác");
        }
        
        $query = "UPDATE " . $this->table_name . " 
                 SET fullname = :fullname, 
                     phone = :phone, 
                     email = :email, 
                     address = :address, 
                     status = :status,
                     updated_at = NOW()
                 WHERE customer_id = :customer_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Làm sạch dữ liệu
        $this->fullname = htmlspecialchars(strip_tags($this->fullname));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->address = htmlspecialchars(strip_tags($this->address));
        
        // Gán giá trị
        $stmt->bindParam(":fullname", $this->fullname);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":customer_id", $this->customer_id);
        
        return $stmt->execute();
    }
    
    /**
     * Lấy thông tin khách hàng theo ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE customer_id = :id LIMIT 1";
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
     * Lấy tất cả khách hàng
     */
    public function getAll($search = '', $status = '', $limit = 10, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $query .= " AND (fullname LIKE :search OR phone LIKE :search OR email LIKE :search OR customer_code LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($status)) {
            $query .= " AND status = :status";
            $params[':status'] = $status;
        }
        
        $query .= " ORDER BY created_at DESC";
        
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
        return $stmt;
    }
    
    /**
     * Đếm tổng số khách hàng
     */
    public function countAll($search = '', $status = '') {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $query .= " AND (fullname LIKE :search OR phone LIKE :search OR email LIKE :search OR customer_code LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($status)) {
            $query .= " AND status = :status";
            $params[':status'] = $status;
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
     * Tìm kiếm khách hàng theo số điện thoại hoặc tên
     */
    public function search($keyword, $limit = 10) {
        $query = "SELECT customer_id, customer_code, fullname, phone, email, address 
                 FROM " . $this->table_name . " 
                 WHERE (phone LIKE :keyword OR fullname LIKE :keyword) 
                 AND status = 'Active' 
                 ORDER BY fullname ASC 
                 LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%$keyword%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Xóa khách hàng (đánh dấu là đã xóa)
     */
    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET status = 'Inactive', updated_at = NOW() 
                 WHERE customer_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
