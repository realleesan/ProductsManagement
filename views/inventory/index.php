<?php 
$page_title = 'Quản lý kho hàng';
$active_page = 'inventory';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="page-header">
    <h1><i class="fas fa-warehouse"></i> Quản lý kho hàng</h1>
    <div>
        <a href="?action=importForm" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Nhập kho
        </a>
        <a href="?action=exportForm" class="btn btn-warning">
            <i class="fas fa-minus-circle"></i> Xuất kho
        </a>
    </div>
</div>

<!-- Thống kê nhanh -->
<div class="quick-stats">
    <div class="stat-item">
        <strong><?php echo number_format($statistics['total_products']); ?></strong>
        <span>Tổng SP</span>
    </div>
    <div class="stat-item stat-success">
        <strong><?php echo number_format($statistics['total_stock']); ?></strong>
        <span>Tồn kho</span>
    </div>
    <div class="stat-item stat-warning">
        <strong><?php echo number_format($statistics['low_stock']); ?></strong>
        <span>Sắp hết hàng</span>
    </div>
    <div class="stat-item stat-danger">
        <strong><?php echo number_format($statistics['out_of_stock']); ?></strong>
        <span>Hết hàng</span>
    </div>
    <div class="stat-item stat-danger">
        <strong><?php echo number_format($statistics['expired']); ?></strong>
        <span>Hết hạn</span>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="filter-section">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="action" value="index">
        
        <div class="filter-group">
            <select name="product_id" class="form-control">
                <option value="">Tất cả sản phẩm</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?php echo $p['product_id']; ?>" <?php echo (isset($_GET['product_id']) && $_GET['product_id'] == $p['product_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['product_code'] . ' - ' . $p['product_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
                        
        <div class="filter-group">
            <select name="type" class="form-control">
                <option value="">Tất cả loại</option>
                                <option value="Import" <?php echo (isset($_GET['type']) && $_GET['type'] === 'Import') ? 'selected' : ''; ?>>Nhập kho</option>
                                <option value="Export" <?php echo (isset($_GET['type']) && $_GET['type'] === 'Export') ? 'selected' : ''; ?>>Xuất kho</option>
                            </select>
                        </div>
                        
        <div class="filter-group">
            <input type="date" name="start_date" class="form-control" 
                   value="<?php echo $_GET['start_date'] ?? ''; ?>" 
                   placeholder="Từ ngày">
        </div>
        
        <div class="filter-group">
            <input type="date" name="end_date" class="form-control" 
                   value="<?php echo $_GET['end_date'] ?? ''; ?>"
                   placeholder="Đến ngày">
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
        
        <a href="?action=index" class="btn btn-secondary">
            <i class="fas fa-redo"></i> Làm mới
        </a>
    </form>
</div>

<!-- Bảng lịch sử giao dịch -->
<div class="card">
    <div class="card-body">
        <?php if (count($transactions) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã giao dịch</th>
                        <th>Loại</th>
                        <th>Sản phẩm</th>
                        <th class="text-end">Số lượng</th>
                        <th class="text-end">Tồn kho sau</th>
                        <th>Người thực hiện</th>
                        <th>Thời gian</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['reference_code'] ?? ''); ?></td>
                        <td>
                            <?php if (($transaction['action_type'] ?? '') === 'Import'): ?>
                                <span class="badge bg-success">Nhập kho</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Xuất kho</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars(($transaction['product_code'] ?? '') . ' - ' . ($transaction['product_name'] ?? '')); ?>
                        </td>
                        <td class="text-end"><?php echo number_format($transaction['quantity'] ?? 0); ?></td>
                        <td class="text-end"><?php echo number_format($transaction['new_stock'] ?? 0); ?></td>
                        <td><?php echo htmlspecialchars($transaction['action_by'] ?? ''); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($transaction['action_at'] ?? 'now')); ?></td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="?action=view&id=<?php echo $transaction['history_id'] ?? ''; ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?action=edit&id=<?php echo $transaction['history_id'] ?? ''; ?>" class="btn btn-sm btn-warning" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?action=delete&id=<?php echo $transaction['history_id'] ?? ''; ?>" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa giao dịch này? Tồn kho sẽ được điều chỉnh lại.');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>Không có giao dịch nào được tìm thấy.
            </div>
        <?php endif; ?>
        
        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?action=index&page=<?php echo ($page - 1); ?><?php echo isset($_GET['product_id']) ? '&product_id=' . $_GET['product_id'] : ''; ?><?php echo isset($_GET['type']) ? '&type=' . $_GET['type'] : ''; ?><?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?><?php echo isset($_GET['end_date']) ? '&end_date=' . $_GET['end_date'] : ''; ?>" 
                   class="btn btn-sm btn-secondary">
                    <i class="fas fa-chevron-left"></i> Trước
                </a>
                <?php endif; ?>
                
                <span class="page-info">Trang <?php echo $page; ?>/<?php echo $total_pages; ?></span>
                
                <?php if ($page < $total_pages): ?>
                <a href="?action=index&page=<?php echo ($page + 1); ?><?php echo isset($_GET['product_id']) ? '&product_id=' . $_GET['product_id'] : ''; ?><?php echo isset($_GET['type']) ? '&type=' . $_GET['type'] : ''; ?><?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?><?php echo isset($_GET['end_date']) ? '&end_date=' . $_GET['end_date'] : ''; ?>" 
                   class="btn btn-sm btn-secondary">
                    Tiếp <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
