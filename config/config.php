<?php
/**
 * File cấu hình chung của ứng dụng
 */

// Cấu hình hiển thị lỗi (chỉ bật khi development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Cấu hình session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Đường dẫn gốc của ứng dụng
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/quanlysanpham');

// Đường dẫn upload ảnh
define('UPLOAD_PATH', BASE_PATH . '/uploads/products/');
define('UPLOAD_URL', BASE_URL . '/uploads/products/');

// Tạo thư mục upload nếu chưa có
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}

// Cấu hình upload ảnh
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png']);
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_GALLERY_IMAGES', 5);

// Cấu hình phân trang
define('ITEMS_PER_PAGE', 10);

// Thông tin người dùng mặc định (có thể mở rộng thành hệ thống đăng nhập)
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'username' => 'admin',
        'fullname' => 'Quản trị viên',
        'role' => 'admin'
    ];
}

/**
 * Hàm helper để redirect
 */
function redirect($url) {
    // Nếu URL đã bắt đầu bằng / thì không cần thêm BASE_URL
    if (strpos($url, '/') === 0) {
        header("Location: " . $url);
    } else {
        header("Location: " . BASE_URL . $url);
    }
    exit();
}

/**
 * Hàm helper để hiển thị thông báo
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

/**
 * Hàm helper để lấy và xóa thông báo
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Hàm helper để format tiền VNĐ
 */
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

/**
 * Hàm helper để format ngày tháng
 */
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * Hàm helper để format ngày giờ
 */
function formatDateTime($datetime) {
    return date('d/m/Y H:i:s', strtotime($datetime));
}

/**
 * Hàm helper để sanitize input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Hàm helper để validate định dạng mã sản phẩm
 */
function validateProductCode($code) {
    return preg_match('/^SP[A-Z0-9]+$/', $code);
}

/**
 * Hàm helper để validate định dạng mã danh mục
 */
function validateCategoryCode($code) {
    return preg_match('/^DM[A-Z0-9]+$/', $code);
}
