<?php include '../../views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Chi tiết đơn hàng #<?php echo $order['order_code']; ?></h2>
        <div class="btn-group">
            <a href="/quanlysanpham/orders" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="/quanlysanpham/orders/edit/<?php echo $order['order_id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#printModal">
                <i class="fas fa-print"></i> In đơn hàng
            </button>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin khách hàng</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Khách hàng:</strong> 
                        <span class="float-end"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                    </p>
                    <p class="mb-2">
                        <strong>Điện thoại:</strong> 
                        <span class="float-end"><?php echo htmlspecialchars($order['phone']); ?></span>
                    </p>
                    <p class="mb-2">
                        <strong>Email:</strong> 
                        <span class="float-end"><?php echo htmlspecialchars($order['email']); ?></span>
                    </p>
                    <p class="mb-0">
                        <strong>Địa chỉ:</strong>
                    </p>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin thanh toán</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Phương thức thanh toán:</strong> 
                        <span class="float-end">
                            <?php 
                                $paymentMethods = [
                                    'cod' => 'Thanh toán khi nhận hàng (COD)',
                                    'bank_transfer' => 'Chuyển khoản ngân hàng',
                                    'momo' => 'Ví điện tử MoMo',
                                    'vnpay' => 'VNPay'
                                ];
                                echo $paymentMethods[$order['payment_method']] ?? $order['payment_method'];
                            ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <strong>Trạng thái thanh toán:</strong> 
                        <span class="float-end">
                            <?php 
                                $paymentStatuses = [
                                    'pending' => 'Chưa thanh toán',
                                    'partial' => 'Thanh toán một phần',
                                    'paid' => 'Đã thanh toán',
                                    'refunded' => 'Đã hoàn tiền',
                                    'cancelled' => 'Đã hủy'
                                ];
                                $statusClass = [
                                    'pending' => 'text-warning',
                                    'partial' => 'text-info',
                                    'paid' => 'text-success',
                                    'refunded' => 'text-secondary',
                                    'cancelled' => 'text-danger'
                                ];
                                echo '<span class="' . ($statusClass[$order['payment_status']] ?? '') . '">' . 
                                     ($paymentStatuses[$order['payment_status']] ?? $order['payment_status']) . 
                                     '</span>';
                            ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <strong>Đã thanh toán:</strong> 
                        <span class="float-end text-success">
                            <?php echo number_format($order['paid_amount']); ?> đ
                        </span>
                    </p>
                    <?php if (!empty($order['payment_date'])): ?>
                    <p class="mb-0">
                        <strong>Ngày thanh toán:</strong> 
                        <span class="float-end">
                            <?php echo date('d/m/Y H:i', strtotime($order['payment_date'])); ?>
                        </span>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin vận chuyển</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Trạng thái đơn hàng:</strong> 
                        <span class="float-end">
                            <?php 
                                $orderStatuses = [
                                    'pending' => 'Chờ xử lý',
                                    'processing' => 'Đang xử lý',
                                    'shipped' => 'Đang giao hàng',
                                    'delivered' => 'Đã giao hàng',
                                    'cancelled' => 'Đã hủy',
                                    'refunded' => 'Đã hoàn tiền'
                                ];
                                $statusClass = [
                                    'pending' => 'text-warning',
                                    'processing' => 'text-info',
                                    'shipped' => 'text-primary',
                                    'delivered' => 'text-success',
                                    'cancelled' => 'text-danger',
                                    'refunded' => 'text-secondary'
                                ];
                                echo '<span class="' . ($statusClass[$order['status']] ?? '') . '">' . 
                                     ($orderStatuses[$order['status']] ?? $order['status']) . 
                                     '</span>';
                            ?>
                        </span>
                    </p>
                    <?php if (!empty($order['shipping_carrier'])): ?>
                    <p class="mb-2">
                        <strong>Đơn vị vận chuyển:</strong> 
                        <span class="float-end"><?php echo htmlspecialchars($order['shipping_carrier']); ?></span>
                    </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($order['tracking_number'])): ?>
                    <p class="mb-2">
                        <strong>Mã vận đơn:</strong> 
                        <span class="float-end"><?php echo htmlspecialchars($order['tracking_number']); ?></span>
                    </p>
                    <?php endif; ?>
                    
                    <p class="mb-0">
                        <strong>Ngày đặt hàng:</strong> 
                        <span class="float-end">
                            <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?>
                        </span>
                    </p>
                    
                    <?php if ($order['status'] === 'delivered' && !empty($order['delivered_date'])): ?>
                    <p class="mb-0">
                        <strong>Ngày giao hàng:</strong> 
                        <span class="float-end">
                            <?php echo date('d/m/Y H:i', strtotime($order['delivered_date'])); ?>
                        </span>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($order['notes'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ghi chú đơn hàng</h5>
                </div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Chi tiết đơn hàng</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">STT</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-end" style="width: 100px;">Đơn giá</th>
                                    <th class="text-center" style="width: 100px;">Số lượng</th>
                                    <th class="text-end" style="width: 120px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $index => $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo $index + 1; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($item['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                 class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-box text-muted"></i>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                <?php if (!empty($item['product_code'])): ?>
                                                <small class="text-muted">Mã: <?php echo htmlspecialchars($item['product_code']); ?></small>
                                                <?php endif; ?>
                                                <?php if (!empty($item['options'])): ?>
                                                <div class="mt-1">
                                                    <?php 
                                                    $options = json_decode($item['options'], true);
                                                    if (is_array($options)): 
                                                        foreach ($options as $key => $value): 
                                                            if (!empty($value)): ?>
                                                            <span class="badge bg-light text-dark me-1 mb-1">
                                                                <?php echo htmlspecialchars($key); ?>: <?php echo htmlspecialchars($value); ?>
                                                            </span>
                                                    <?php   endif;
                                                        endforeach;
                                                    endif; 
                                                    ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end"><?php echo number_format($item['price']); ?> đ</td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end">
                                        <?php echo number_format($item['price'] * $item['quantity']); ?> đ
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Tạm tính:</td>
                                    <td colspan="2" class="text-end"><?php echo number_format($order['subtotal']); ?> đ</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Phí vận chuyển:</td>
                                    <td colspan="2" class="text-end"><?php echo number_format($order['shipping_fee']); ?> đ</td>
                                </tr>
                                <?php if ($order['discount'] > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Giảm giá:</td>
                                    <td colspan="2" class="text-end text-danger">-<?php echo number_format($order['discount']); ?> đ</td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                    <td colspan="2" class="text-end fw-bold text-primary">
                                        <?php echo number_format($order['total_amount']); ?> đ
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Đã thanh toán:</td>
                                    <td colspan="2" class="text-end text-success">
                                        <?php echo number_format($order['paid_amount']); ?> đ
                                    </td>
                                </tr>
                                <?php if (($order['total_amount'] - $order['paid_amount']) > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Còn lại:</td>
                                    <td colspan="2" class="text-end text-danger fw-bold">
                                        <?php echo number_format($order['total_amount'] - $order['paid_amount']); ?> đ
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($orderHistory)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Lịch sử cập nhật đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($orderHistory as $history): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1">
                                        <?php 
                                            $statuses = array_merge(
                                                $orderStatuses ?? [],
                                                $paymentStatuses ?? [],
                                                ['status_updated' => 'Cập nhật trạng thái']
                                            );
                                            echo $statuses[$history['status']] ?? $history['status']; 
                                        ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?php echo date('H:i d/m/Y', strtotime($history['created_at'])); ?>
                                    </small>
                                </div>
                                <p class="mb-1">
                                    <?php if (!empty($history['notes'])): ?>
                                        <small><?php echo nl2br(htmlspecialchars($history['notes'])); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Không có ghi chú</small>
                                    <?php endif; ?>
                                </p>
                                <div class="text-muted small">
                                    <i class="fas fa-user"></i> 
                                    <?php echo !empty($history['updated_by_name']) ? 
                                        htmlspecialchars($history['updated_by_name']) : 'Hệ thống'; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Print Modal -->
<div class="modal fade" id="printModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">In đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Chọn mẫu in:</p>
                <div class="d-grid gap-2">
                    <a href="/quanlysanpham/orders/print/<?php echo $order['order_id']; ?>?template=default" 
                       class="btn btn-outline-primary text-start" target="_blank">
                        <i class="fas fa-print me-2"></i> Mẫu in thông thường
                    </a>
                    <a href="/quanlysanpham/orders/print/<?php echo $order['order_id']; ?>?template=thermal" 
                       class="btn btn-outline-secondary text-start" target="_blank">
                        <i class="fas fa-receipt me-2"></i> Mẫu in nhiệt (máy in hóa đơn)
                    </a>
                    <a href="/quanlysanpham/orders/print/<?php echo $order['order_id']; ?>?template=shipping" 
                       class="btn btn-outline-info text-start" target="_blank">
                        <i class="fas fa-truck me-2"></i> Mẫu in vận đơn
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
    margin: 0 0 0 1rem;
    border-left: 2px solid #dee2e6;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -1.7rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background-color: #0d6efd;
    border: 2px solid #fff;
}

.timeline-content {
    padding: 0.5rem 1rem;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
}

.timeline-item:last-child .timeline-content {
    margin-bottom: 0;
}

@media print {
    .no-print {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        width: 100% !important;
        margin-bottom: 1rem;
        color: #212529;
    }
    
    .table th,
    .table td {
        padding: 0.5rem;
        border: 1px solid #dee2e6;
    }
    
    .table thead th {
        border-bottom: 2px solid #dee2e6;
    }
    
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    
    .text-end {
        text-align: right !important;
    }
    
    .text-center {
        text-align: center !important;
    }
    
    .mb-4 {
        margin-bottom: 1.5rem !important;
    }
    
    .mt-4 {
        margin-top: 1.5rem !important;
    }
    
    .py-3 {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }
    
    .border-bottom {
        border-bottom: 1px solid #dee2e6 !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Print functionality
    const printButtons = document.querySelectorAll('.btn-print');
    printButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.open(this.href, '_blank');
        });
    });
    
    // Update order status
    const statusSelect = document.getElementById('updateStatus');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const status = this.value;
            const orderId = this.dataset.orderId;
            const notes = prompt('Nhập ghi chú (nếu có):');
            
            if (status && orderId) {
                const formData = new FormData();
                formData.append('status', status);
                if (notes !== null) {
                    formData.append('notes', notes);
                }
                
                fetch(`/quanlysanpham/api/orders/${orderId}/status`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra khi cập nhật trạng thái: ' + (data.message || 'Lỗi không xác định'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi kết nối đến máy chủ');
                });
            }
        });
    }
});
</script>

<?php include '../../views/layouts/footer.php'; ?>
