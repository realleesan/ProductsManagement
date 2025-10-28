<?php 
$page_title = 'Quản lý đơn hàng';
$active_page = 'orders';
require_once __DIR__ . '/../layouts/header.php'; 

// Include alert partial if exists
$alert_path = __DIR__ . '/../partials/alert.php';
if (file_exists($alert_path)) {
    include $alert_path;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-shopping-cart text-primary me-2"></i> Quản lý đơn hàng</h1>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>/controllers/OrderController.php?action=create" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Tạo đơn hàng mới
                    </a>
                </div>
            </div>
            
<!-- Thống kê nhanh -->
<div class="quick-stats mb-4">
    <div class="stat-item">
        <strong><?= number_format($statistics['total_orders']) ?></strong>
        <span>Tổng đơn</span>
    </div>
    <div class="stat-item stat-warning">
        <strong><?= number_format($statistics['pending_orders']) ?></strong>
        <span>Chờ xác nhận</span>
    </div>
    <div class="stat-item stat-info">
        <strong><?= number_format($statistics['processing_orders']) ?></strong>
        <span>Đang xử lý</span>
    </div>
    <div class="stat-item stat-purple">
        <strong><?= number_format($statistics['shipping_orders']) ?></strong>
        <span>Đang giao</span>
    </div>
    <div class="stat-item stat-success">
        <strong><?= number_format($statistics['completed_orders']) ?></strong>
        <span>Hoàn tất</span>
    </div>
    <div class="stat-item stat-danger">
        <strong><?= number_format($statistics['cancelled_orders']) ?></strong>
        <span>Đã hủy</span>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="filter-section mb-4">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="action" value="index">
        
        <div class="filter-group">
            <input type="text" 
                   name="search" 
                   placeholder="Tìm theo mã đơn, tên KH, SĐT..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                   class="form-control">
        </div>
        
        <div class="filter-group">
            <select name="status" class="form-select">
                <option value="">Tất cả trạng thái</option>
                <option value="Chờ xác nhận" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Chờ xác nhận') ? 'selected' : ''; ?>>Chờ xác nhận</option>
                <option value="Đang xử lý" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đang xử lý') ? 'selected' : ''; ?>>Đang xử lý</option>
                <option value="Đang giao" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đang giao') ? 'selected' : ''; ?>>Đang giao</option>
                <option value="Hoàn tất" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Hoàn tất') ? 'selected' : ''; ?>>Hoàn tất</option>
                <option value="Đã hủy" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đã hủy') ? 'selected' : ''; ?>>Đã hủy</option>
            </select>
        </div>
        
        <div class="filter-group">
            <input type="date" 
                   name="from_date" 
                   class="form-control" 
                   value="<?php echo isset($_GET['from_date']) ? htmlspecialchars($_GET['from_date']) : ''; ?>"
                   placeholder="Từ ngày">
        </div>
        
        <div class="filter-group">
            <input type="date" 
                   name="to_date" 
                   class="form-control" 
                   value="<?php echo isset($_GET['to_date']) ? htmlspecialchars($_GET['to_date']) : ''; ?>"
                   placeholder="Đến ngày">
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
        
        <a href="<?php echo BASE_URL; ?>/controllers/OrderController.php?action=index" class="btn btn-secondary">
            <i class="fas fa-redo"></i> Làm mới
        </a>
    </form>
</div>

<!-- Danh sách đơn hàng -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th class="text-end pe-4">Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p class="mb-0">Không có đơn hàng nào</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): 
                            $status_class = [
                                'Chờ xác nhận' => 'warning',
                                'Đang xử lý' => 'info',
                                'Đang giao' => 'primary',
                                'Hoàn tất' => 'success',
                                'Đã hủy' => 'danger'
                            ][$order['status']] ?? 'secondary';
                        ?>
                            <tr class="order-row">
                                <td class="ps-4" data-label="Mã đơn">
                                    <div class="d-flex flex-column">
                                        <a href="<?= BASE_URL ?>/controllers/OrderController.php?action=view&id=<?= $order['order_id'] ?>" class="fw-bold text-primary text-decoration-none">
                                            <?= htmlspecialchars($order['order_code']) ?>
                                        </a>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i> 
                                            <?= date('H:i d/m/Y', strtotime($order['order_date'])) ?>
                                        </small>
                                    </div>
                                </td>
                                <td data-label="Khách hàng">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-title bg-light text-dark rounded-circle">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($order['customer_name'] ?? 'Khách lẻ') ?></div>
                                            <?php if (!empty($order['customer_phone'])): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone-alt me-1"></i> 
                                                    <?= htmlspecialchars($order['customer_phone']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Ngày đặt">
                                    <div class="text-nowrap">
                                        <?= date('d/m/Y', strtotime($order['order_date'])) ?>
                                    </div>
                                </td>
                                <td class="text-end fw-bold pe-4" data-label="Tổng tiền">
                                    <?= number_format($order['total_amount'], 0, ',', '.') ?> ₫
                                </td>
                                <td data-label="Trạng thái">
                                    <span class="badge bg-<?= $status_class ?>-lighten text-<?= $status_class ?> px-3 py-2">
                                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                        <?= $order['status'] ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4" data-label="Thao tác">
                                    <div class="d-flex flex-wrap justify-content-end gap-1" style="min-width: 0;">
                                        <a href="<?= BASE_URL ?>/controllers/OrderController.php?action=view&id=<?= $order['order_id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="Xem chi tiết"
                                           style="min-width: 32px;">
                                            <i class="far fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/controllers/OrderController.php?action=exportInvoice&id=<?= $order['order_id'] ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           data-bs-toggle="tooltip" 
                                           title="In hóa đơn"
                                           style="min-width: 32px;">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <?php if ($order['status'] === 'Chờ xác nhận'): ?>
                                            <a href="<?= BASE_URL ?>/controllers/OrderController.php?action=delete&id=<?= $order['order_id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')" 
                                               data-bs-toggle="tooltip" 
                                               title="Xóa đơn hàng"
                                               style="min-width: 32px;">
                                                <i class="far fa-trash-alt"></i>
                                            </a>
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary" 
                                                    disabled
                                                    style="min-width: 32px;">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white border-top-0">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?controller=orders&action=index&page=<?= $page - 1 ?><?= !empty($query_string) ? '&' . $query_string : '' ?>" 
                                   aria-label="Previous">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $start + 4);
                        $start = max(1, $end - 4);
                        
                        if ($start > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?controller=orders&action=index&page=1' . (!empty($query_string) ? '&' . $query_string : '') . '">1</a></li>';
                            if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?controller=orders&action=index&page=<?= $i ?><?= !empty($query_string) ? '&' . $query_string : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php 
                        endfor; 
                        
                        if ($end < $total_pages) {
                            if ($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            echo '<li class="page-item"><a class="page-link" href="?controller=orders&action=index&page=' . $total_pages . (!empty($query_string) ? '&' . $query_string : '') . '">' . $total_pages . '</a></li>';
                        }
                        ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?controller=orders&action=index&page=<?= $page + 1 ?><?= !empty($query_string) ? '&' . $query_string : '' ?>" 
                                   aria-label="Next">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Hàm xác nhận xóa (giữ lại nếu có chỗ khác sử dụng)
function confirmDelete(orderId) {
    if (confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')) {
        window.location.href = '<?= BASE_URL ?>/controllers/OrderController.php?action=delete&id=' + orderId;
    }
}

// Khởi tạo tooltip
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover',
            placement: 'top'
        });
    });
    
    // Thêm hiệu ứng hover cho hàng đơn hàng
    const orderRows = document.querySelectorAll('.order-row');
    orderRows.forEach(row => {
        row.style.transition = 'all 0.2s ease';
        row.addEventListener('mouseenter', function() {
            this.classList.add('bg-light');
        });
        row.addEventListener('mouseleave', function() {
            this.classList.remove('bg-light');
        });
    });
});
</script>

<style>
/* Tùy chỉnh giao diện bảng */
.table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 0 auto;
    padding: 0 15px;
    box-sizing: border-box;
}

.table {
    --bs-table-hover-bg: rgba(var(--bs-primary-rgb), 0.03);
}

.table > :not(:first-child) {
    border-top: 1px solid var(--bs-border-color);
}

.table > thead > tr > th {
    border-bottom: 1px solid var(--bs-border-color);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 1rem 1.5rem;
    color: var(--bs-gray-600);
}

.table > tbody > tr > td {
    padding: 1rem 1.5rem;
    vertical-align: middle;
}

/* Tùy chỉnh badge trạng thái */
.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
    font-size: 0.75em;
    letter-spacing: 0.5px;
}

/* Tùy chỉnh nút phân trang */
.page-link {
    min-width: 38px;
    text-align: center;
    margin: 0 2px;
    border-radius: 4px !important;
    font-weight: 500;
}

/* Tùy chỉnh avatar người dùng */
.avatar-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 14px;
}

/* Tùy chỉnh nút thao tác */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Màu sắc cho các trạng thái */
.bg-warning-lighten {
    background-color: rgba(var(--bs-warning-rgb), 0.1);
}

.bg-info-lighten {
    background-color: rgba(var(--bs-info-rgb), 0.1);
}

.bg-primary-lighten {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}

.bg-success-lighten {
    background-color: rgba(var(--bs-success-rgb), 0.1);
}

.bg-danger-lighten {
    background-color: rgba(var(--bs-danger-rgb), 0.1);
}

/* Tùy chỉnh card */
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1.5rem;
}

.card-header {
    background-color: var(--bs-white);
    border-bottom: 1px solid var(--bs-border-color);
    padding: 1rem 1.5rem;
}

/* Tùy chỉnh form control */
.form-control, .form-select {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
}

/* Tùy chỉnh nút */
.btn {
    font-weight: 500;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
}

/* Tùy chỉnh modal */
.modal-content {
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--bs-border-color);
}

.modal-footer {
    padding: 1.25rem 1.5rem;
    border-top: 1px solid var(--bs-border-color);
}

/* Responsive cho bảng */
@media (max-width: 768px) {
    .table thead {
        display: none;
    }
    
    .table, .table tbody, .table tr {
        display: block;
        width: 100%;
        box-sizing: border-box;
    }
    
    .table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: 0.75rem 1rem;
        box-sizing: border-box;
    }
    
    .table tr {
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .table td {
        padding: 0.75rem 1rem;
        text-align: right;
        position: relative;
        min-height: 60px;
        align-items: center;
    }
    
    .table td::before {
        content: attr(data-label);
        text-align: left;
        font-weight: 600;
        color: #495057;
        margin-right: 1rem;
        flex: 0 0 40%;
        max-width: 40%;
    }
    
    .table td .btn-group {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.25rem;
        flex: 0 0 60%;
        max-width: 60%;
    }
    
    /* Ẩn cột thừa trên mobile */
    .table tr {
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .table td[data-label="Mã đơn"] {
        background-color: #f8f9fa;
        font-weight: 600;
        padding-top: 1rem;
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }
    
    .table td[data-label="Khách hàng"] {
        background-color: #f8f9fa;
        font-weight: 600;
        padding-top: 1rem;
    }
    
    .table td[data-label="Thao tác"] {
        padding-bottom: 1rem;
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
    }
}

/* Responsive cho các thành phần khác */
@media (max-width: 768px) {
    .table-responsive {
        border-radius: 0.375rem;
        border: 1px solid var(--bs-border-color);
    }
    
    .filter-form .row {
        display: flex;
        flex-wrap: nowrap;
        align-items: flex-end;
        margin: 0 -0.25rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
    }
    
    .filter-form .row > div {
        flex: 1 0 auto;
        padding: 0 0.25rem;
        min-width: 150px;
        margin-bottom: 0;
    }
    
    .filter-form .form-control,
    .filter-form .form-select {
        height: calc(1.5em + 0.75rem + 2px);
        font-size: 0.875rem;
        padding: 0.375rem 0.5rem;
    }
    
    .filter-form .input-group-text {
        padding: 0.375rem 0.5rem;
    }
    
    .filter-form .btn {
        width: auto;
        margin-bottom: 0;
        white-space: nowrap;
        padding: 0.375rem 0.75rem;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
