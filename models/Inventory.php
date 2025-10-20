<?php
class Inventory {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    // Lấy danh sách tất cả sản phẩm
    public function getAllProducts() {
        $query = "SELECT product_id, product_code, product_name, stock_quantity 
                 FROM products 
                 WHERE is_active = 1 
                 ORDER BY product_name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tạo mã phiếu tự động
    private function generateTransactionCode($prefix) {
        $date = date('Ymd');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        return $prefix . $date . $random;
    }

    // Lấy tất cả phiếu nhập kho
    public function getAllImports() {
        $query = "SELECT wi.*, p.product_code, p.product_name 
                 FROM warehouse_import wi
                 JOIN products p ON wi.product_id = p.product_id
                 ORDER BY wi.import_date DESC, wi.import_id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy tất cả phiếu xuất kho
    public function getAllExports() {
        $query = "SELECT we.*, p.product_code, p.product_name 
                 FROM warehouse_export we
                 JOIN products p ON we.product_id = p.product_id
                 ORDER BY we.export_date DESC, we.export_id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy chi tiết phiếu nhập kho theo ID
    public function getImportById($id) {
        $query = "SELECT wi.*, p.product_code, p.product_name, p.price, p.expiry_date, 
                        p.stock_quantity, p.min_stock
                 FROM warehouse_import wi
                 JOIN products p ON wi.product_id = p.product_id
                 WHERE wi.import_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy chi tiết phiếu xuất kho theo ID
    public function getExportById($id) {
        $query = "SELECT we.*, p.product_code, p.product_name, p.price, p.expiry_date, 
                        p.stock_quantity, p.min_stock
                 FROM warehouse_export we
                 JOIN products p ON we.product_id = p.product_id
                 WHERE we.export_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo phiếu nhập kho
    public function createImport($data) {
        try {
            $this->db->beginTransaction();

            // Tạo mã phiếu nhập
            $importCode = $this->generateTransactionCode('PN');
            
            // Thêm phiếu nhập
            $query = "INSERT INTO warehouse_import 
                     (import_code, product_id, quantity, import_date, import_by, note, status)
                     VALUES (:import_code, :product_id, :quantity, :import_date, :import_by, :note, 'Completed')";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':import_code', $importCode);
            $stmt->bindParam(':product_id', $data['product_id'], PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $data['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':import_date', $data['date']);
            $stmt->bindParam(':import_by', $data['user']);
            $stmt->bindParam(':note', $data['note']);
            $stmt->execute();

            // Cập nhật tồn kho
            $updateQuery = "UPDATE products 
                           SET stock_quantity = stock_quantity + :quantity,
                               updated_at = NOW()
                           WHERE product_id = :product_id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':quantity', $data['quantity'], PDO::PARAM_INT);
            $updateStmt->bindParam(':product_id', $data['product_id'], PDO::PARAM_INT);
            $updateStmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating import: " . $e->getMessage());
            return false;
        }
    }

    // Tạo phiếu xuất kho
    public function createExport($data) {
        try {
            $this->db->beginTransaction();

            // Kiểm tra tồn kho
            $checkStock = $this->db->prepare(
                "SELECT stock_quantity FROM products WHERE product_id = :product_id FOR UPDATE"
            );
            $checkStock->bindParam(':product_id', $data['product_id'], PDO::PARAM_INT);
            $checkStock->execute();
            $stock = $checkStock->fetch(PDO::FETCH_ASSOC);

            if (!$stock || $stock['stock_quantity'] < $data['quantity']) {
                throw new Exception('Số lượng tồn kho không đủ để xuất');
            }

            // Tạo mã phiếu xuất
            $exportCode = $this->generateTransactionCode('PX');
            
            // Thêm phiếu xuất
            $query = "INSERT INTO warehouse_export 
                     (export_code, product_id, quantity, export_date, export_by, reason, note, status)
                     VALUES (:export_code, :product_id, :quantity, :export_date, :export_by, :reason, :note, 'Completed')";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':export_code', $exportCode);
            $stmt->bindParam(':product_id', $data['product_id'], PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $data['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':export_date', $data['date']);
            $stmt->bindParam(':export_by', $data['user']);
            $stmt->bindParam(':reason', $data['reason']);
            $stmt->bindParam(':note', $data['note']);
            $stmt->execute();

            // Cập nhật tồn kho
            $updateQuery = "UPDATE products 
                           SET stock_quantity = stock_quantity - :quantity,
                               updated_at = NOW()
                           WHERE product_id = :product_id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':quantity', $data['quantity'], PDO::PARAM_INT);
            $updateStmt->bindParam(':product_id', $data['product_id'], PDO::PARAM_INT);
            $updateStmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating export: " . $e->getMessage());
            throw $e; // Ném lại để controller xử lý thông báo lỗi
        }
    }

    // Lấy tổng số lượng nhập/xuất trong khoảng thời gian
    public function getInventoryStats($startDate = null, $endDate = null) {
        $stats = [
            'total_import' => 0,
            'total_export' => 0,
            'import_by_month' => [],
            'export_by_month' => [],
            'top_import_products' => [],
            'top_export_products' => []
        ];

        // Tổng nhập/xuất
        $importQuery = "SELECT COALESCE(SUM(quantity), 0) as total 
                       FROM warehouse_import 
                       WHERE status = 'Completed'";
        
        $exportQuery = "SELECT COALESCE(SUM(quantity), 0) as total 
                       FROM warehouse_export 
                       WHERE status = 'Completed'";

        if ($startDate && $endDate) {
            $importQuery .= " AND import_date BETWEEN :start_date AND :end_date";
            $exportQuery .= " AND export_date BETWEEN :start_date AND :end_date";
        }

        // Thực hiện truy vấn tổng nhập
        $stmt = $this->db->prepare($importQuery);
        if ($startDate && $endDate) {
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
        }
        $stmt->execute();
        $stats['total_import'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Thực hiện truy vấn tổng xuất
        $stmt = $this->db->prepare($exportQuery);
        if ($startDate && $endDate) {
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
        }
        $stmt->execute();
        $stats['total_export'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Thống kê nhập/xuất theo tháng (12 tháng gần nhất)
        $endDate = $endDate ?: date('Y-m-d');
        $startDate = $startDate ?: date('Y-m-d', strtotime('-12 months', strtotime($endDate)));
        
        // Lấy dữ liệu nhập theo tháng
        $monthlyImportQuery = "SELECT 
            DATE_FORMAT(import_date, '%Y-%m') as month,
            SUM(quantity) as total
            FROM warehouse_import
            WHERE import_date BETWEEN :start_date AND :end_date
            GROUP BY DATE_FORMAT(import_date, '%Y-%m')
            ORDER BY month ASC";
            
        $stmt = $this->db->prepare($monthlyImportQuery);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        $stats['import_by_month'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Lấy dữ liệu xuất theo tháng
        $monthlyExportQuery = "SELECT 
            DATE_FORMAT(export_date, '%Y-%m') as month,
            SUM(quantity) as total
            FROM warehouse_export
            WHERE export_date BETWEEN :start_date AND :end_date
            GROUP BY DATE_FORMAT(export_date, '%Y-%m')
            ORDER BY month ASC";
            
        $stmt = $this->db->prepare($monthlyExportQuery);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        $stats['export_by_month'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Top sản phẩm nhập nhiều nhất
        $topImportQuery = "SELECT 
            p.product_name,
            SUM(wi.quantity) as total_quantity
            FROM warehouse_import wi
            JOIN products p ON wi.product_id = p.product_id
            WHERE wi.import_date BETWEEN :start_date AND :end_date
            GROUP BY wi.product_id
            ORDER BY total_quantity DESC
            LIMIT 5";
            
        $stmt = $this->db->prepare($topImportQuery);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        $stats['top_import_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top sản phẩm xuất nhiều nhất
        $topExportQuery = "SELECT 
            p.product_name,
            SUM(we.quantity) as total_quantity
            FROM warehouse_export we
            JOIN products p ON we.product_id = p.product_id
            WHERE we.export_date BETWEEN :start_date AND :end_date
            GROUP BY we.product_id
            ORDER BY total_quantity DESC
            LIMIT 5";
            
        $stmt = $this->db->prepare($topExportQuery);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        $stats['top_export_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
}
