<?php
/**
 * Model Product - Quản lý sản phẩm
 * Tuân thủ các ràng buộc trong tài liệu
 */

class Product {
    private $conn;
    private $table_name = "products";
    
    // Thuộc tính
    public $product_id;
    public $product_code;
    public $product_name;
    public $description;
    public $price;
    public $stock_quantity;
    public $category_id;
    public $category_name;
    public $manufacture_date;
    public $expiry_date;
    public $status;
    public $main_image;
    public $gallery_images = [];
    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;
    
    /**
     * Constructor
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Lấy tất cả sản phẩm với thông tin danh mục
     */
    public function getAll($search = '', $category_id = '', $status = '', $limit = 10, $offset = 0) {
        $query = "SELECT p.*, c.category_name, c.status as category_status
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  WHERE 1=1";
        
        // Tìm kiếm theo tên
        if (!empty($search)) {
            $query .= " AND p.product_name LIKE :search";
        }
        
        // Lọc theo danh mục
        if (!empty($category_id)) {
            $query .= " AND p.category_id = :category_id";
        }
        
        // Lọc theo trạng thái
        if (!empty($status)) {
            $query .= " AND p.status = :status";
        }
        
        $query .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(':search', $search_param);
        }
        
        if (!empty($category_id)) {
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        }
        
        if (!empty($status)) {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Lấy danh sách sản phẩm đang hoạt động (status = 'Active')
     */
    public function getActiveProducts() {
        $query = "SELECT p.*, c.category_name 
                 FROM " . $this->table_name . " p
                 LEFT JOIN categories c ON p.category_id = c.category_id
                 WHERE p.status = 'Active' 
                 AND (p.stock_quantity > 0)
                 ORDER BY p.product_name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Đếm tổng số sản phẩm (cho phân trang)
     */
    public function countAll($search = '', $category_id = '', $status = '') {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";
        
        if (!empty($search)) {
            $query .= " AND product_name LIKE :search";
        }
        
        if (!empty($category_id)) {
            $query .= " AND category_id = :category_id";
        }
        
        if (!empty($status)) {
            $query .= " AND status = :status";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(':search', $search_param);
        }
        
        if (!empty($category_id)) {
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        }
        
        if (!empty($status)) {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'];
    }
    
    /**
     * Lấy sản phẩm theo ID
     */
    public function getById($id) {
        $query = "SELECT p.*, c.category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  WHERE p.product_id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->product_id = $row['product_id'];
            $this->product_code = $row['product_code'];
            $this->product_name = $row['product_name'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->stock_quantity = $row['stock_quantity'];
            $this->category_id = $row['category_id'];
            $this->category_name = $row['category_name'] ?? null;
            $this->manufacture_date = $row['manufacture_date'];
            $this->expiry_date = $row['expiry_date'];
            $this->status = $row['status'];
            $this->main_image = $row['main_image'] ?? null;
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'] ?? null;
            $this->created_by = $row['created_by'] ?? null;
            $this->updated_by = $row['updated_by'] ?? null;
            
            // Load gallery images
            $this->gallery_images = $this->getGalleryImages($this->product_id);

            return true;
        }
        return false;
    }

    /**
     * Kiểm tra mã sản phẩm đã tồn tại chưa
     */
    public function codeExists($code, $exclude_id = null) {
        $query = "SELECT product_id FROM " . $this->table_name . " WHERE product_code = :code";
        
        if ($exclude_id) {
            $query .= " AND product_id != :exclude_id";
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
     * Validate dữ liệu sản phẩm theo ràng buộc
     */
    public function validate() {
        $errors = [];
        
        // Validate mã sản phẩm
        if (empty($this->product_code)) {
            $errors[] = "Mã sản phẩm không được để trống";
        } elseif (!preg_match('/^SP[A-Z0-9]+$/', $this->product_code)) {
            $errors[] = "Mã sản phẩm phải có định dạng SPXX...X (chỉ chữ in hoa và số)";
        }
        
        // Validate tên sản phẩm
        if (empty($this->product_name)) {
            $errors[] = "Tên sản phẩm không được để trống";
        } elseif (mb_strlen($this->product_name) < 5 || mb_strlen($this->product_name) > 150) {
            $errors[] = "Tên sản phẩm phải từ 5-150 ký tự";
        }
        
        // Validate giá
        if (empty($this->price) || !is_numeric($this->price)) {
            $errors[] = "Giá sản phẩm không hợp lệ";
        } elseif ($this->price < 1000 || $this->price > 1000000000) {
            $errors[] = "Giá sản phẩm phải từ 1.000 đến 1.000.000.000 VNĐ";
        }
        
        // Validate tồn kho
        if (!isset($this->stock_quantity) || !is_numeric($this->stock_quantity) || $this->stock_quantity < 0) {
            $errors[] = "Số lượng tồn kho phải >= 0";
        }
        
        // Validate danh mục
        if (empty($this->category_id)) {
            $errors[] = "Vui lòng chọn danh mục sản phẩm";
        }
        
        // Validate ngày sản xuất và hạn sử dụng
        if (empty($this->manufacture_date)) {
            $errors[] = "Ngày sản xuất không được để trống";
        }
        
        if (empty($this->expiry_date)) {
            $errors[] = "Hạn sử dụng không được để trống";
        }
        
        if (!empty($this->manufacture_date) && !empty($this->expiry_date)) {
            $mfg = strtotime($this->manufacture_date);
            $exp = strtotime($this->expiry_date);
            $diff_days = ($exp - $mfg) / (60 * 60 * 24);
            
            if ($diff_days < 30) {
                $errors[] = "Hạn sử dụng phải sau ngày sản xuất ít nhất 30 ngày";
            }
        }
        
        // Validate mô tả
        if (!empty($this->description) && mb_strlen($this->description) > 500) {
            $errors[] = "Mô tả không được vượt quá 500 ký tự";
        }

        // Validate trạng thái
        $allowed_statuses = ['Active', 'Disabled', 'Out of stock', 'Expired'];
        if (empty($this->status) || !in_array($this->status, $allowed_statuses)) {
            $errors[] = "Trạng thái sản phẩm không hợp lệ";
        } else {
            $today = strtotime(date('Y-m-d'));
            $expiry_timestamp = !empty($this->expiry_date) ? strtotime($this->expiry_date) : null;

            if ($this->status === 'Expired' && $expiry_timestamp !== null && $expiry_timestamp >= $today) {
                $errors[] = "Chỉ có thể đặt trạng thái 'Expired' khi sản phẩm đã quá hạn";
            }

            if ($this->status === 'Active') {
                if ($this->stock_quantity <= 0) {
                    $errors[] = "Không thể đặt trạng thái 'Active' khi số lượng tồn kho bằng 0";
                }
                if ($expiry_timestamp !== null && $expiry_timestamp < $today) {
                    $errors[] = "Không thể đặt trạng thái 'Active' khi sản phẩm đã hết hạn";
                }
            }

            if ($this->status === 'Out of stock' && $this->stock_quantity > 0) {
                $errors[] = "Không thể đặt trạng thái 'Out of stock' khi sản phẩm vẫn còn hàng";
            }
        }

        return $errors;
    }
    
    /**
     * Thêm sản phẩm mới
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (product_code, product_name, description, price, stock_quantity, 
                   category_id, manufacture_date, expiry_date, status,
                   main_image, created_by) 
                  VALUES 
                  (:code, :name, :description, :price, :stock, 
                   :category_id, :mfg_date, :exp_date, :status,
                   :main_image, :created_by)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->product_code = htmlspecialchars(strip_tags($this->product_code));
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->main_image = htmlspecialchars(strip_tags($this->main_image));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));
        
        // Bind
        $stmt->bindParam(':code', $this->product_code);
        $stmt->bindParam(':name', $this->product_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':stock', $this->stock_quantity, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $this->category_id, PDO::PARAM_INT);
        $stmt->bindParam(':mfg_date', $this->manufacture_date);
        $stmt->bindParam(':exp_date', $this->expiry_date);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':main_image', $this->main_image);
        $stmt->bindParam(':created_by', $this->created_by);
        
        if ($stmt->execute()) {
            $this->product_id = $this->conn->lastInsertId();
            if (!empty($this->gallery_images)) {
                $this->addGalleryImages($this->product_id, $this->gallery_images);
            }
            return true;
        }
        return false;
    }
    
    /**
     * Cập nhật sản phẩm
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET product_code = :code,
                      product_name = :name,
                      description = :description,
                      price = :price,
                      stock_quantity = :stock,
                      category_id = :category_id,
                      manufacture_date = :mfg_date,
                      expiry_date = :exp_date,
                      status = :status,
                      main_image = :main_image,
                      updated_by = :updated_by
                  WHERE product_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->product_code = htmlspecialchars(strip_tags($this->product_code));
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->main_image = htmlspecialchars(strip_tags($this->main_image));
        $this->updated_by = htmlspecialchars(strip_tags($this->updated_by));
        
        // Bind
        $stmt->bindParam(':code', $this->product_code);
        $stmt->bindParam(':name', $this->product_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':stock', $this->stock_quantity, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $this->category_id, PDO::PARAM_INT);
        $stmt->bindParam(':mfg_date', $this->manufacture_date);
        $stmt->bindParam(':exp_date', $this->expiry_date);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':main_image', $this->main_image);
        $stmt->bindParam(':updated_by', $this->updated_by);
        $stmt->bindParam(':id', $this->product_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Lấy danh sách ảnh gallery của sản phẩm
     */
    public function getGalleryImages($product_id = null) {
        $product_id = $product_id ?? $this->product_id;
        $query = "SELECT image_id, image_path, sort_order 
                  FROM product_images 
                  WHERE product_id = :product_id 
                  ORDER BY sort_order ASC, image_id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm ảnh gallery mới cho sản phẩm
     */
    public function addGalleryImages($product_id, array $images) {
        if (empty($images)) {
            return;
        }

        $baseOrderQuery = "SELECT COALESCE(MAX(sort_order), -1) as max_order FROM product_images WHERE product_id = :product_id";
        $orderStmt = $this->conn->prepare($baseOrderQuery);
        $orderStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $orderStmt->execute();
        $result = $orderStmt->fetch(PDO::FETCH_ASSOC);
        $currentOrder = isset($result['max_order']) ? (int)$result['max_order'] : -1;

        $insertQuery = "INSERT INTO product_images (product_id, image_path, sort_order) VALUES (:product_id, :image_path, :sort_order)";
        $insertStmt = $this->conn->prepare($insertQuery);

        foreach ($images as $image_path) {
            $currentOrder++;
            $cleanPath = htmlspecialchars(strip_tags($image_path));
            $insertStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $insertStmt->bindParam(':image_path', $cleanPath);
            $insertStmt->bindParam(':sort_order', $currentOrder, PDO::PARAM_INT);
            $insertStmt->execute();
        }

        // Cập nhật lại thuộc tính gallery_images khi cần
        if ($this->product_id == $product_id) {
            $this->gallery_images = $this->getGalleryImages($product_id);
        }
    }

    /**
     * Xóa ảnh gallery theo danh sách ID (hoặc tất cả nếu không truyền)
     */
    public function deleteGalleryImages($product_id, array $image_ids = []) {
        if (!empty($image_ids)) {
            $placeholders = implode(',', array_fill(0, count($image_ids), '?'));
            $query = "DELETE FROM product_images WHERE product_id = ? AND image_id IN ($placeholders)";
            $stmt = $this->conn->prepare($query);
            $params = array_merge([$product_id], $image_ids);
            $stmt->execute($params);
        } else {
            $query = "DELETE FROM product_images WHERE product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        if ($this->product_id == $product_id) {
            $this->gallery_images = $this->getGalleryImages($product_id);
        }
    }
    
    /**
     * Xóa sản phẩm
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE product_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->product_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Cập nhật trạng thái sản phẩm
     */
    public function updateStatus($product_id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE product_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Lấy thống kê sản phẩm
     */
    public function getStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_products,
                    SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_products,
                    SUM(CASE WHEN status = 'Out of stock' THEN 1 ELSE 0 END) as out_of_stock,
                    SUM(CASE WHEN status = 'Expired' THEN 1 ELSE 0 END) as expired_products,
                    SUM(CASE WHEN status = 'Disabled' THEN 1 ELSE 0 END) as disabled_products,
                    SUM(stock_quantity) as total_stock
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Lấy sản phẩm sắp hết hạn
     */
    public function getExpiringSoon($days = 60) {
        $query = "SELECT p.*, c.category_name,
                         DATEDIFF(p.expiry_date, CURDATE()) as days_until_expiry
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  WHERE p.expiry_date >= CURDATE() 
                    AND DATEDIFF(p.expiry_date, CURDATE()) <= :days
                    AND p.status != 'Expired'
                  ORDER BY days_until_expiry ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Lấy sản phẩm tồn kho thấp
     */
    public function getLowStock($threshold = 20) {
        $query = "SELECT p.*, c.category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  WHERE p.stock_quantity > 0 
                    AND p.stock_quantity < :threshold
                    AND p.status = 'Active'
                  ORDER BY p.stock_quantity ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Cập nhật số lượng tồn kho sản phẩm
     * @param int $product_id ID sản phẩm
     * @param int $quantity_change Số lượng thay đổi (dương để tăng, âm để giảm)
     * @param string $note Ghi chú lý do thay đổi
     * @return bool Kết quả cập nhật
     */
    public function updateStock($product_id, $quantity_change, $note = '') {
        // Bắt đầu transaction
        $this->conn->beginTransaction();
        
        try {
            // Lấy thông tin sản phẩm hiện tại
            $query = "SELECT stock_quantity, status FROM " . $this->table_name . " WHERE product_id = :id FOR UPDATE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Không tìm thấy sản phẩm");
            }
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            $new_quantity = $product['stock_quantity'] + $quantity_change;
            
            // Kiểm tra nếu số lượng mới âm
            if ($new_quantity < 0) {
                throw new Exception("Số lượng tồn kho không đủ");
            }
            
            // Cập nhật số lượng tồn kho
            $update_query = "UPDATE " . $this->table_name . " 
                           SET stock_quantity = :new_quantity,
                               updated_at = NOW()
                           WHERE product_id = :id";
            
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':new_quantity', $new_quantity, PDO::PARAM_INT);
            $update_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Lỗi khi cập nhật số lượng tồn kho");
            }
            
            // Ghi log thay đổi tồn kho
            $log_query = "INSERT INTO inventory_logs 
                         (product_id, quantity_change, new_quantity, note, created_at) 
                         VALUES 
                         (:product_id, :quantity_change, :new_quantity, :note, NOW())";
            
            $log_stmt = $this->conn->prepare($log_query);
            $log_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $log_stmt->bindParam(':quantity_change', $quantity_change, PDO::PARAM_INT);
            $log_stmt->bindParam(':new_quantity', $new_quantity, PDO::PARAM_INT);
            $log_stmt->bindParam(':note', $note);
            $log_stmt->execute();
            
            // Cập nhật trạng thái sản phẩm nếu cần
            $new_status = $product['status'];
            if ($new_quantity <= 0 && $product['status'] !== 'Out of stock') {
                $new_status = 'Out of stock';
            } elseif ($new_quantity > 0 && $product['status'] === 'Out of stock') {
                $new_status = 'Active';
            }
            
            if ($new_status !== $product['status']) {
                $status_query = "UPDATE " . $this->table_name . " SET status = :status WHERE product_id = :id";
                $status_stmt = $this->conn->prepare($status_query);
                $status_stmt->bindParam(':status', $new_status);
                $status_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
                $status_stmt->execute();
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
