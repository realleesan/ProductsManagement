<?php
/**
 * Kiểm thử hộp trắng - Dòng điều khiển
 * 4.3.1.3. Chức năng quản lý đơn hàng (Cập nhật trạng thái đơn)
 * 
 * Input từ bảng giá trị:
 * - Mã đơn hàng (string)
 * - Trạng thái đơn hàng {Chờ xác nhận, Đang xử lý, Đang giao, Hoàn tất, Đã thanh toán, Đã hủy}
 * - Lý do hủy đơn (string) - chỉ khi trạng thái = "Đã hủy"
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderDetail.php';
require_once __DIR__ . '/../models/Product.php';

// Giả lập context của controller
$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$product = new Product($db);

/**
 * Cập nhật trạng thái đơn hàng (chỉ dòng điều khiển)
 */
function updateOrderStatus() {
    global $order, $product, $db;
    
    // Điều kiện 1: Kiểm tra REQUEST_METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
            exit;
        }
        redirect('?controller=OrderController&action=index');
        return;
    }
    
    try {
        $order_id = (int)$_POST['order_id'];
        $new_status = sanitizeInput($_POST['status']);
        $cancel_reason = isset($_POST['cancel_reason']) ? sanitizeInput($_POST['cancel_reason']) : '';
        
        // Điều kiện 2: Kiểm tra đơn hàng tồn tại
        if (!$order->getById($order_id)) {
            throw new Exception("Không tìm thấy đơn hàng");
        }
        
        // Lưu trạng thái cũ để kiểm tra
        $old_status = $order->status;
        
        // Cập nhật trạng thái
        $order->status = $new_status;
        
        // Điều kiện 3: Xử lý khi hủy đơn hàng
        if ($new_status === 'Đã hủy') {
            $order->cancel_reason = $cancel_reason;
        }
        
        // Điều kiện 4: Cập nhật trạng thái thành công/thất bại
        if ($order->updateStatus($order_id, $new_status, $cancel_reason)) {
            // Điều kiện 5: Xử lý khi chuyển sang "Đã thanh toán"
            if ($new_status === 'Đã thanh toán' && $old_status !== 'Đã thanh toán') {
                // Lấy danh sách sản phẩm trong đơn hàng
                $orderDetail = new OrderDetail($db);
                $order_items = $orderDetail->getByOrderId($order_id);
                
                // Trừ tồn kho cho từng sản phẩm
                foreach ($order_items as $item) {
                    $product_id = $item['product_id'];
                    $quantity = $item['quantity'];
                    
                    // Trừ tồn kho (số âm để trừ)
                    $product->updateStock($product_id, -$quantity, "Đơn hàng #{$order->order_code} - Đã thanh toán");
                }
            }
            
            // Điều kiện 6: Xử lý AJAX request
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái đơn hàng thành công']);
                exit;
            } else {
                setFlashMessage('success', 'Cập nhật trạng thái đơn hàng thành công');
            }
        } else {
            throw new Exception("Có lỗi xảy ra khi cập nhật trạng thái đơn hàng");
        }
        
    } catch (Exception $e) {
        // Điều kiện 7: Xử lý exception
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
            exit;
        } else {
            setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
        }
    }
    
    // Chỉ redirect nếu không phải AJAX request
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
        redirect('?controller=OrderController&action=view&id=' . $order_id);
    }
}

