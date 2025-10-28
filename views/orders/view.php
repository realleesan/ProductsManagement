<?php 
$page_title = 'Chi tiết đơn hàng';
$active_page = 'orders';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="fas fa-eye text-primary me-2"></i> Chi tiết đơn hàng</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/controllers/ProductController.php?action=dashboard">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/controllers/OrderController.php?action=index">Đơn hàng</a></li>
                            <li class="breadcrumb-item active" aria-current="page">#<?= htmlspecialchars($order['order_code']) ?></li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>/controllers/OrderController.php?action=index" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                    <a href="<?= BASE_URL ?>/controllers/OrderController.php?action=exportInvoice&id=<?= $order['order_id'] ?>" target="_blank" class="btn btn-primary">
                        <i class="fas fa-print me-1"></i> In hóa đơn
                    </a>
                </div>
            </div>
            
            <?php
            // Hiển thị thông báo flash message
            $flash = getFlashMessage();
            if ($flash): 
            ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php 
                    echo $flash['type'] == 'success' ? 'check-circle' : 
                        ($flash['type'] == 'error' ? 'exclamation-circle' : 
                        ($flash['type'] == 'warning' ? 'exclamation-triangle' : 'info-circle')); 
                ?>"></i>
                <span><?php echo $flash['message']; ?></span>
                <button class="alert-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- Thông tin đơn hàng -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Thông tin đơn hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Mã sản phẩm</th>
                                            <th>Sản phẩm</th>
                                            <th class="text-end">Đơn giá</th>
                                            <th class="text-center">Số lượng</th>
                                            <th class="text-end">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total_items = 0;
                                        $subtotal = 0;
                                        
                                        if (!empty($order['order_items']) && is_array($order['order_items'])):
                                            foreach ($order['order_items'] as $item): 
                                                $total_items += $item['quantity'];
                                                $item_total = $item['quantity'] * $item['unit_price'];
                                                $subtotal += $item_total;
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['product_code']) ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($item['image_url'])): ?>
                                                            <div class="flex-shrink-0 me-2">
                                                                <img src="/quanlysanpham/uploads/products/<?= htmlspecialchars($item['image_url']) ?>" 
                                                                     alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                                     class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></div>
                                                            <div class="text-muted small"><?= htmlspecialchars($item['product_code']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end"><?= number_format($item['unit_price']) ?> ₫</td>
                                                <td class="text-center"><?= $item['quantity'] ?></td>
                                                <td class="text-end fw-bold"><?= number_format($item_total) ?> ₫</td>
                                            </tr>
                                        <?php 
                                            endforeach;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <br>Không có sản phẩm nào trong đơn hàng
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                            <td class="text-center"><?= $total_items ?> sản phẩm</td>
                                            <td class="text-end fw-bold"><?= number_format($subtotal) ?> ₫</td>
                                        </tr>
                                        <?php if ($order['discount_amount'] > 0): ?>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Giảm giá:</strong></td>
                                                <td class="text-end text-danger">-<?= number_format($order['discount_amount']) ?> ₫</td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($order['shipping_fee'] > 0): ?>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Phí vận chuyển:</strong></td>
                                                <td class="text-end"><?= number_format($order['shipping_fee']) ?> ₫</td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr class="table-active">
                                            <td colspan="4" class="text-end"><strong>Thành tiền:</strong></td>
                                            <td class="text-end fw-bold fs-5"><?= number_format($subtotal) ?> ₫</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-muted small">
                                                <i class="fas fa-info-circle me-1"></i> 
                                                Đã bao gồm VAT (nếu có)
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <?php if (!empty($order['note'])): ?>
                                <div class="alert alert-light mt-3">
                                    <h6 class="alert-heading">Ghi chú đơn hàng:</h6>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($order['note'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Lịch sử đơn hàng -->
                    <?php if (!empty($order_history)): ?>
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Lịch sử đơn hàng</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($order_history as $history): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <?php 
                                                            $action_text = [
                                                                'Created' => 'Tạo đơn hàng',
                                                                'Confirmed' => 'Xác nhận đơn hàng',
                                                                'Processing' => 'Đang xử lý',
                                                                'Shipping' => 'Đang giao hàng',
                                                                'Completed' => 'Hoàn tất đơn hàng',
                                                                'Cancelled' => 'Hủy đơn hàng'
                                                            ][$history['action_type']] ?? $history['action_type'];
                                                            
                                                            $action_icon = [
                                                                'Created' => 'plus-circle',
                                                                'Confirmed' => 'check-circle',
                                                                'Processing' => 'cog',
                                                                'Shipping' => 'truck',
                                                                'Completed' => 'check-double',
                                                                'Cancelled' => 'times-circle'
                                                            ][$history['action_type']] ?? 'info-circle';
                                                        ?>
                                                        <i class="fas fa-<?= $action_icon ?> text-primary me-2"></i>
                                                        <?= $action_text ?>
                                                    </h6>
                                                    <?php if (!empty($history['note'])): ?>
                                                        <p class="mb-1"><?= nl2br(htmlspecialchars($history['note'])) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-end">
                                                    <div class="text-muted small">
                                                        <?= date('H:i d/m/Y', strtotime($history['action_at'])) ?>
                                                    </div>
                                                    <div class="text-muted small">
                                                        <?= htmlspecialchars($history['action_by']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-lg-4">
                    <!-- Thông tin khách hàng -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Thông tin khách hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-lg bg-light text-primary rounded-circle">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0"><?= htmlspecialchars($order['customer_name']) ?></h5>
                                    <div class="text-muted">
                                        <?php if (!empty($order['customer_phone'])): ?>
                                            <i class="fas fa-phone me-1"></i> <?= htmlspecialchars($order['customer_phone']) ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($order['customer_email'])): ?>
                                            <i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($order['customer_email']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6 class="mb-2">Địa chỉ giao hàng:</h6>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                            
                            <?php if (!empty($order['shipping_note'])): ?>
                                <div class="alert alert-light mt-3 mb-0 p-2 small">
                                    <strong>Ghi chú giao hàng:</strong>
                                    <?= nl2br(htmlspecialchars($order['shipping_note'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Trạng thái đơn hàng -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Trạng thái đơn hàng</h5>
                        </div>
                        <div class="card-body">
                            <form id="updateStatusForm" method="post" action="<?= BASE_URL ?>/controllers/OrderController.php?action=updateStatus">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                
                                <!-- Phương thức thanh toán -->
                                <div class="mb-3">
                                    <label class="form-label">Phương thức thanh toán:</label>
                                    <div class="fw-bold">
                                        <?php 
                                            $payment_methods = [
                                                'cod' => 'Thanh toán khi nhận hàng (COD)',
                                                'bank' => 'Chuyển khoản ngân hàng',
                                                'momo' => 'Ví điện tử MoMo',
                                                'vnpay' => 'VNPAY',
                                                'zalopay' => 'ZaloPay'
                                            ];
                                            echo $payment_methods[strtolower($order['payment_method'])] ?? $order['payment_method'];
                                        ?>
                                    </div>
                                </div>
                                
                                <!-- Trạng thái đơn hàng -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">Trạng thái đơn hàng:</label>
                                    <select class="form-select mb-3" id="status" name="status" onchange="toggleCancelReason()">
                                        <?php 
                                            $statuses = [
                                                'Chờ xác nhận' => 'Chờ xác nhận',
                                                'Đang xử lý' => 'Đang xử lý',
                                                'Đang giao' => 'Đang giao',
                                                'Hoàn tất' => 'Hoàn tất',
                                                'Đã thanh toán' => 'Đã thanh toán',
                                                'Đã hủy' => 'Đã hủy'
                                            ];
                                            
                                            foreach ($statuses as $value => $label):
                                                $selected = ($order['status'] === $value) ? 'selected' : '';
                                                echo "<option value=\"$value\" $selected>$label</option>";
                                            endforeach;
                                        ?>
                                    </select>
                                    
                                    <!-- Lý do hủy đơn hàng -->
                                    <div id="cancelReasonContainer" class="d-none mt-3">
                                        <label for="cancel_reason" class="form-label">Lý do hủy đơn hàng:</label>
                                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3" required></textarea>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100" onclick="return confirmUpdate()">
                                    <i class="fas fa-save me-1"></i> Cập nhật
                                </button>
                            </form>
                            
                            <?php if ($order['status'] === 'Chờ xác nhận'): ?>
                                <hr>
                                <form method="post" action="<?= BASE_URL ?>/controllers/OrderController.php?action=delete&id=<?= $order['order_id'] ?>" 
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')">
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-trash me-1"></i> Xóa đơn hàng
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Hiển thị/ẩn trường lý do hủy
function toggleCancelReason() {
    const status = document.getElementById('status').value;
    const cancelReasonContainer = document.getElementById('cancelReasonContainer');
    const cancelReasonInput = document.getElementById('cancel_reason');
    
    if (status === 'Đã hủy') {
        cancelReasonContainer.classList.remove('d-none');
        cancelReasonInput.required = true;
    } else {
        cancelReasonContainer.classList.add('d-none');
        cancelReasonInput.required = false;
    }
}

// Xử lý form cập nhật trạng thái
const form = document.getElementById('updateStatusForm');
if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const url = form.getAttribute('action');
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hiển thị thông báo thành công
                showAlert(data.message, 'success');
                // Làm mới trang sau 1.5 giây
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert(data.message || 'Có lỗi xảy ra', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Có lỗi xảy ra khi kết nối đến máy chủ', 'danger');
        });
    });
}

// Hàm xác nhận cập nhật
function confirmUpdate() {
    const status = document.getElementById('status').value;
    const currentStatus = '<?= $order['status'] ?>';
    
    if (status === currentStatus) {
        alert('Trạng thái hiện tại đã được chọn. Vui lòng chọn trạng thái khác để cập nhật.');
        return false;
    }
    
    return confirm('Bạn có chắc chắn muốn cập nhật trạng thái đơn hàng?');
}

// Hàm hiển thị thông báo
function showAlert(message, type = 'success') {
    // Xóa thông báo cũ nếu có
    const existingAlert = document.querySelector('.alert.position-fixed');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} position-fixed`;
    alertDiv.style.cssText = `
        position: fixed !important;
        top: 20px !important;
        right: 20px !important;
        z-index: 9999 !important;
        max-width: 400px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        margin: 0 !important;
    `;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Tự động ẩn thông báo sau 5 giây
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.style.opacity = '0';
            alertDiv.style.transition = 'opacity 0.3s';
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    document.body.removeChild(alertDiv);
                }
            }, 300);
        }
    }, 5000);
}

</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
