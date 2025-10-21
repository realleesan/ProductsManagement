<?php include '../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Chi tiết đơn hàng</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/quanlysanpham">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="/quanlysanpham/orders">Đơn hàng</a></li>
                            <li class="breadcrumb-item active" aria-current="page">#<?= htmlspecialchars($order['order_code']) ?></li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="/quanlysanpham/orders" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                    <a href="/quanlysanpham/orders/print/<?= $order['order_id'] ?>" target="_blank" class="btn btn-primary">
                        <i class="fas fa-print me-1"></i> In hóa đơn
                    </a>
                </div>
            </div>
            
            <?php include '../partials/alert.php'; ?>
            
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
                                        <?php endforeach; ?>
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
                                            <td class="text-end fw-bold fs-5"><?= number_format($order['total_amount']) ?> ₫</td>
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
                    
                    <!-- Thông tin thanh toán -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Thông tin thanh toán</h5>
                        </div>
                        <div class="card-body">
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
                            
                            <?php if ($order['payment_status'] === 'paid'): ?>
                                <div class="alert alert-success p-2 mb-0">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Đã thanh toán
                                    <?php if (!empty($order['payment_date'])): ?>
                                        vào <?= date('H:i d/m/Y', strtotime($order['payment_date'])) ?>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning p-2 mb-0">
                                    <i class="fas fa-clock me-1"></i>
                                    Chưa thanh toán
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Cập nhật trạng thái -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Cập nhật trạng thái</h5>
                        </div>
                        <div class="card-body">
                            <form id="updateStatusForm" method="post" action="/quanlysanpham/orders/update-status/<?= $order['order_id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái hiện tại:</label>
                                    <div class="alert alert-<?= 
                                        ['Chờ xác nhận' => 'warning', 'Đang xử lý' => 'info', 'Đang giao' => 'primary', 'Hoàn tất' => 'success', 'Đã hủy' => 'danger'][$order['status']] 
                                    ?> p-2 mb-3 text-center fw-bold">
                                        <?= $order['status'] ?>
                                    </div>
                                    
                                    <label for="status" class="form-label">Cập nhật trạng thái:</label>
                                    <select class="form-select mb-3" id="status" name="status" onchange="toggleCancelReason()">
                                        <?php 
                                            $statuses = [
                                                'Chờ xác nhận' => 'Chờ xác nhận',
                                                'Đang xử lý' => 'Đang xử lý',
                                                'Đang giao' => 'Đang giao',
                                                'Hoàn tất' => 'Hoàn tất',
                                                'Đã hủy' => 'Đã hủy'
                                            ];
                                            
                                            foreach ($statuses as $value => $label):
                                                if ($order['status'] === $value) continue; // Bỏ qua trạng thái hiện tại
                                                echo "<option value=\"$value\">$label</option>";
                                            endforeach;
                                        ?>
                                    </select>
                                    
                                    <div id="cancelReasonContainer" class="d-none">
                                        <label for="cancel_reason" class="form-label">Lý do hủy đơn hàng:</label>
                                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3" required></textarea>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Cập nhật
                                </button>
                            </form>
                            
                            <?php if ($order['status'] === 'Chờ xác nhận'): ?>
                                <hr>
                                <form method="post" action="/quanlysanpham/orders/delete/<?= $order['order_id'] ?>" 
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
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                // Làm mới trang sau 1.5 giây
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('danger', data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Có lỗi xảy ra khi kết nối đến máy chủ');
        });
    });
}

// Hàm hiển thị thông báo
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Tự động ẩn thông báo sau 5 giây
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(alertDiv);
        }, 150);
    }, 5000);
}
</script>

<?php include '../layouts/footer.php'; ?>
