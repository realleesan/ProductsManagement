<?php include_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item">
                        <a href="/quanlysanpham/controllers/InventoryController.php">
                            <i class="fas fa-warehouse me-1"></i>Quản lý kho
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-eye me-1"></i>Chi tiết giao dịch
                    </li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header bg-gradient bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-eye me-2"></i>Chi tiết giao dịch
                            <small class="ms-2 opacity-75">
                                <?php echo htmlspecialchars($transaction['reference_code']); ?>
                            </small>
                        </h5>
                        <div>
                            <a href="/quanlysanpham/controllers/InventoryController.php" class="btn btn-sm btn-light">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                            <?php if (isset($transaction['status']) && $transaction['status'] === 'Pending'): ?>
                                <a href="#" class="btn btn-sm btn-warning ms-2">
                                    <i class="fas fa-edit me-1"></i> Chỉnh sửa
                                </a>
                                <a href="#" class="btn btn-sm btn-danger ms-2">
                                    <i class="fas fa-times me-1"></i> Hủy
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted mb-3">Thông tin giao dịch</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 40%;">Mã giao dịch:</th>
                                    <td>
                                        <code class="fs-6 bg-light px-2 py-1 rounded"><?php echo htmlspecialchars($transaction['reference_code']); ?></code>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Loại giao dịch:</th>
                                    <td>
                                        <?php if ($transaction['action_type'] === 'Import'): ?>
                                            <span class="badge bg-success fs-6 px-3 py-2">
                                                <i class="fas fa-arrow-down me-1"></i>Nhập kho
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                                <i class="fas fa-arrow-up me-1"></i>Xuất kho
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Thời gian:</th>
                                    <td>
                                        <span class="text-dark">
                                            <i class="fas fa-clock me-1 text-muted"></i>
                                            <?php echo date('H:i:s d/m/Y', strtotime($transaction['action_at'])); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Người thực hiện:</th>
                                    <td>
                                        <span class="text-dark">
                                            <i class="fas fa-user me-1 text-muted"></i>
                                            <?php echo htmlspecialchars($transaction['action_by']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Trạng thái:</th>
                                    <td>
                                        <?php 
                                        $status = $transaction['status'] ?? 'Completed';
                                        $status_class = [
                                            'Completed' => 'success',
                                            'Pending' => 'warning',
                                            'Cancelled' => 'danger'
                                        ][$status] ?? 'secondary';
                                        $status_text = [
                                            'Completed' => 'Hoàn thành',
                                            'Pending' => 'Chờ xử lý',
                                            'Cancelled' => 'Đã hủy'
                                        ][$status] ?? $status;
                                        ?>
                                        <span class="badge bg-<?php echo $status_class; ?> fs-6 px-3 py-2">
                                            <i class="fas fa-<?php echo $status === 'Completed' ? 'check-circle' : ($status === 'Pending' ? 'clock' : 'times-circle'); ?> me-1"></i>
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted mb-3">Thông tin sản phẩm</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 40%;">Mã sản phẩm:</th>
                                    <td>
                                        <code class="fs-6 bg-light px-2 py-1 rounded"><?php echo htmlspecialchars($transaction['product_code']); ?></code>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tên sản phẩm:</th>
                                    <td>
                                        <span class="fw-medium text-dark"><?php echo htmlspecialchars($transaction['product_name']); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Số lượng:</th>
                                    <td>
                                        <span class="badge bg-primary fs-6 px-3 py-2">
                                            <i class="fas fa-boxes me-1"></i><?php echo number_format($transaction['quantity']); ?> cái
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tồn kho trước:</th>
                                    <td>
                                        <span class="text-muted"><?php echo number_format($transaction['old_stock']); ?> cái</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tồn kho sau:</th>
                                    <td>
                                        <span class="badge bg-info text-dark fs-6 px-3 py-2">
                                            <i class="fas fa-warehouse me-1"></i><?php echo number_format($transaction['new_stock']); ?> cái
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php if (!empty($transaction['note'])): ?>
                        <div class="card bg-light mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-sticky-note me-2"></i>Ghi chú
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($transaction['note'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-history me-2"></i>Lịch sử giao dịch gần đây
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Mã giao dịch</th>
                                            <th>Loại</th>
                                            <th class="text-end">Số lượng</th>
                                            <th class="text-end">Tồn kho</th>
                                            <th>Người thực hiện</th>
                                            <th>Thời gian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $history->fetch(PDO::FETCH_ASSOC)): ?>
                                            <tr <?php echo ($row['reference_code'] === $transaction['reference_code']) ? 'class="table-primary"' : ''; ?>>
                                                <td>
                                                    <code class="fs-6"><?php echo htmlspecialchars($row['reference_code']); ?></code>
                                                </td>
                                                <td>
                                                    <?php if ($row['action_type'] === 'Import'): ?>
                                                        <span class="badge bg-success fs-6 px-2 py-1">
                                                            <i class="fas fa-arrow-down me-1"></i>Nhập kho
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark fs-6 px-2 py-1">
                                                            <i class="fas fa-arrow-up me-1"></i>Xuất kho
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <span class="badge bg-primary fs-6 px-2 py-1">
                                                        <?php echo number_format($row['quantity']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex flex-column align-items-end">
                                                        <small class="text-muted"><?php echo number_format($row['old_stock']); ?></small>
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-arrow-right text-muted small me-1"></i>
                                                            <span class="badge bg-info text-dark fs-6 px-2 py-1">
                                                                <?php echo number_format($row['new_stock']); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <i class="fas fa-user me-1 text-muted"></i>
                                                    <?php echo htmlspecialchars($row['action_by']); ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-clock me-1 text-muted"></i>
                                                    <?php echo date('d/m/Y H:i', strtotime($row['action_at'])); ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        
                                        <?php if ($history->rowCount() === 0): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                                    Không có dữ liệu
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
