<?php
/**
 * File cấu hình kết nối cơ sở dữ liệu
 * Sử dụng PDO để đảm bảo bảo mật và chống SQL Injection
 */

class Database {
    // Thông tin kết nối
    private $host = "localhost";
    private $db_name = "quanlysanpham";
    private $username = "root";
    private $password = "";
    private $charset = "utf8mb4";
    
    public $conn;
    
    /**
     * Kết nối đến database sử dụng PDO
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng kiểm tra lại cấu hình.");
        }
        
        return $this->conn;
    }
    
    /**
     * Đóng kết nối database
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
