<?php 
$page_title = $data['title'];
$active_menu = 'orders';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-shopping-cart"></i> <?php echo $data['title']; ?></h1>
        <div>
            <a href="/orders/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tạo đơn hàng
            </a>
            <a href="/orders/stats" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Thống kê
            </a>
        </div>
    </div>
</div>

<!-- Bộ lọc đơn hàng -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="/orders" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="pending" <?php echo $data['filters']['status'] === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                    <option value="processing" <?php echo $data['filters']['status'] === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="shipped" <?php echo $data['filters']['status'] === 'shipped' ? 'selected' : ''; ?>>Đang giao hàng</option>
                    <option value="delivered" <?php echo $data['filters']['status'] === 'delivered' ? 'selected' : ''; ?>>Đã giao hàng</option>
                    <option value="cancelled" <?php echo $data['filters']['status'] === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="from_date" class="form-label">Từ ngày</label>
                <input type="date" name="from_date" id="from_date" class="form-control" 
                       value="<?php echo $data['filters']['from_date']; ?>">
            </div>
            <div class="col-md-3">
                <label for="to_date" class="form-label">Đến ngày</label>
                <input type="date" name="to_date" id="to_date" class="form-control" 
                       value="<?php echo $data['filters']['to_date']; ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter"></i> Lọc
                </button>
                <a href="/orders" class="btn btn-secondary">
                    <i class="fas fa-sync"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách đơn hàng -->
<div class="card">
    <div class="card-body p-0">
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success m-3">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']); 
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger m-3">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']); 
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($data['orders'])): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Ngày đặt</th>
                            <th>Số lượng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['orders'] as $order): 
                            $statusClass = [
                                'pending' => 'warning',
                                'processing' => 'primary',
                                'shipped' => 'info',
                                'delivered' => 'success',
                                'cancelled' => 'danger'
                            ][$order['status']] ?? 'secondary';
                            
                            $statusText = [
                                'pending' => 'Chờ xử lý',
                                'processing' => 'Đang xử lý',
                                'shipped' => 'Đang giao hàng',
                                'delivered' => 'Đã giao hàng',
                                'cancelled' => 'Đã hủy'
                            ][$order['status']] ?? $order['status'];
                        ?>
                            <tr>
                                <td>
                                    <a href="/orders/<?php echo $order['order_id']; ?>" class="fw-bold">
                                        #<?php echo $order['order_code']; ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-title bg-light rounded-circle text-primary">
                                                <?php echo strtoupper(substr($order['customer_name'] ?? '?', 0, 1)); ?>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo $order['customer_name'] ?? 'Khách vãng lai'; ?></h6>
                                            <small class="text-muted"><?php echo $order['phone'] ?? ''; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                                <td><?php echo $order['item_count']; ?> sản phẩm</td>
                                <td class="fw-bold"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> đ</td>
                                <td>
                                    <span class="badge bg-<?php echo $statusClass; ?> bg-opacity-10 text-<?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="/orders/<?php echo $order['order_id']; ?>">
                                                    <i class="fas fa-eye me-2"></i> Xem chi tiết
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="/orders/print/<?php echo $order['order_id']; ?>" target="_blank">
                                                    <i class="fas fa-print me-2"></i> In hóa đơn
                                                </a>
                                            </li>
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-success" href="#" 
                                                       onclick="updateStatus(<?php echo $order['order_id']; ?>, 'processing')">
                                                        <i class="fas fa-check-circle me-2"></i> Xác nhận đơn hàng
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" 
                                                       onclick="confirmCancel(<?php echo $order['order_id']; ?>)">
                                                        <i class="fas fa-times-circle me-2"></i> Hủy đơn hàng
                                                    </a>
                                                </li>
                                            <?php elseif ($order['status'] === 'processing'): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-info" href="#" 
                                                       onclick="updateStatus(<?php echo $order['order_id']; ?>, 'shipped')">
                                                        <i class="fas fa-truck me-2"></i> Đang giao hàng
                                                    </a>
                                                </li>
                                            <?php elseif ($order['status'] === 'shipped'): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-success" href="#" 
                                                       onclick="updateStatus(<?php echo $order['order_id']; ?>, 'delivered')">
                                                        <i class="fas fa-check-double me-2"></i> Đã giao hàng
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Phân trang -->
            <div class="p-3">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Trước</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Tiếp</a>
                        </li>
                    </ul>
                </nav>
            </div>
            
        <?php else: ?>
            <div class="text-center p-5">
                <div class="mb-3">
                    <i class="fas fa-shopping-cart fa-4x text-muted"></i>
                </div>
                <h5 class="text-muted">Không có đơn hàng nào</h5>
                <p class="text-muted">Hãy tạo đơn hàng mới để bắt đầu</p>
                <a href="/orders/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Tạo đơn hàng mới
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal cập nhật trạng thái -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="statusForm" method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật trạng thái đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" id="statusInput">
                    <div class="mb-3">
                        <label for="statusNote" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="statusNote" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal hủy đơn hàng -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="cancelForm" method="post" action="">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Xác nhận hủy đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn hủy đơn hàng này không?</p>
                    <div class="mb-3">
                        <label for="cancelReason" class="form-label">Lý do hủy</label>
                        <textarea class="form-control" id="cancelReason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Hàm cập nhật trạng thái đơn hàng
function updateStatus(orderId, status) {
    const form = document.getElementById('statusForm');
    form.action = `/orders/${orderId}/status`;
    document.getElementById('statusInput').value = status;
    
    // Đặt tiêu đề phù hợp với trạng thái
    const modalTitle = document.querySelector('#statusModal .modal-title');
    const statusText = {
        'processing': 'Xác nhận đơn hàng',
        'shipped': 'Xác nhận đang giao hàng',
        'delivered': 'Xác nhận đã giao hàng'
    }[status] || 'Cập nhật trạng thái';
    
    modalTitle.textContent = statusText;
    
    // Hiển thị modal
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

// Hàm xác nhận hủy đơn hàng
function confirmCancel(orderId) {
    const form = document.getElementById('cancelForm');
    form.action = `/orders/${orderId}/cancel`;
    
    // Hiển thị modal
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}

// Khởi tạo datepicker
if (document.getElementById('from_date') && document.getElementById('to_date')) {
    flatpickr("#from_date, #to_date", {
        dateFormat: "Y-m-d",
        locale: "vi"
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
