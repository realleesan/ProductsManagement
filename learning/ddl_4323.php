<?php
/**
 * Kiểm thử hộp trắng - Dòng dữ liệu
 * 4.3.2.3. Chức năng quản lý đơn hàng (Xóa đơn hàng)
 * 
 * Input từ bảng giá trị:
 * - Mã đơn hàng (string) - được lấy từ ID
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Order.php';

// Giả lập context của controller
$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

/**
 * Xóa đơn hàng (chỉ dòng dữ liệu)
 */
function deleteOrder() {
    global $order;
    
    // Bước 1: Nhận ID đơn hàng từ GET
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    try {
        // Bước 2: Kiểm tra đơn hàng tồn tại
        if (!$order->getById($id)) {
            throw new Exception("Không tìm thấy đơn hàng");
        }
        
        // Bước 3: Kiểm tra trạng thái đơn hàng (chỉ cho phép xóa khi ở trạng thái "Chờ xác nhận")
        if ($order->status !== 'Chờ xác nhận') {
            throw new Exception("Chỉ có thể xóa đơn hàng ở trạng thái 'Chờ xác nhận'");
        }
        
        // Bước 4: Xóa đơn hàng
        if ($order->delete($id)) {
            setFlashMessage('success', 'Xóa đơn hàng thành công');
        } else {
            throw new Exception("Có lỗi xảy ra khi xóa đơn hàng");
        }
        
    } catch (Exception $e) {
        setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
    }
    
    // Bước 5: Redirect về danh sách đơn hàng
    redirect('?controller=OrderController&action=index');
}

