<?php
/**
 * Model Category - Quản lý danh mục sản phẩm
 */

class Category {
    private $conn;
    private $table_name = "categories";
    
    // Thuộc tính
    public $category_id;
    public $category_code;
    public $category_name;
    public $description;
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
     * Lấy tất cả danh mục
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY category_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Lấy danh mục Active
     */
    public function getActive() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'Active' ORDER BY category_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Lấy danh mục theo ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->category_id = $row['category_id'];
            $this->category_code = $row['category_code'];
            $this->category_name = $row['category_name'];
            $this->description = $row['description'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }
    
    /**
     * Kiểm tra mã danh mục đã tồn tại chưa
     */
    public function codeExists($code, $exclude_id = null) {
        $query = "SELECT category_id FROM " . $this->table_name . " WHERE category_code = :code";
        
        if ($exclude_id) {
            $query .= " AND category_id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $code);
        
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Thêm danh mục mới
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (category_code, category_name, description, status) 
                  VALUES (:code, :name, :description, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->category_code = htmlspecialchars(strip_tags($this->category_code));
        $this->category_name = htmlspecialchars(strip_tags($this->category_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind
        $stmt->bindParam(':code', $this->category_code);
        $stmt->bindParam(':name', $this->category_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);
        
        if ($stmt->execute()) {
            $this->category_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    /**
     * Cập nhật danh mục
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET category_code = :code,
                      category_name = :name,
                      description = :description,
                      status = :status
                  WHERE category_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->category_code = htmlspecialchars(strip_tags($this->category_code));
        $this->category_name = htmlspecialchars(strip_tags($this->category_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        
        // Bind
        $stmt->bindParam(':code', $this->category_code);
        $stmt->bindParam(':name', $this->category_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->category_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Xóa danh mục
     */
    public function delete() {
        // Kiểm tra xem có sản phẩm nào đang sử dụng danh mục này không
        $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = :id";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':id', $this->category_id, PDO::PARAM_INT);
        $check_stmt->execute();
        $result = $check_stmt->fetch();
        
        if ($result['count'] > 0) {
            return false; // Không thể xóa vì còn sản phẩm
        }
        
        $query = "DELETE FROM " . $this->table_name . " WHERE category_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->category_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Đếm số sản phẩm trong danh mục
     */
    public function countProducts($category_id) {
        $query = "SELECT COUNT(*) as count FROM products WHERE category_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
}
