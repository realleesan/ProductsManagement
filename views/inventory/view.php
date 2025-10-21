<?php include_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item">
                        <a href="/controllers/InventoryController.php">
                            <i class="fas fa-warehouse me-1"></i>Quản lý kho
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-eye me-1"></i>Chi tiết giao dịch
                    </li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-eye me-2"></i>Chi tiết giao dịch
                        </h5>
                        <div>
                            <a href="/controllers/InventoryController.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                            <?php if ($transaction['status'] === 'Pending'): ?>
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
                                        <span class="badge bg-<?php echo $transaction['action_type'] === 'Import' ? 'success' : 'warning text-dark'; ?>">
                                            <?php echo $transaction['reference_code']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Loại giao dịch:</th>
                                    <td>
                                        <?php if ($transaction['action_type'] === 'Import'): ?>
                                            <span class="badge bg-success">Nhập kho</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Xuất kho</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Thời gian:</th>
                                    <td><?php echo date('H:i:s d/m/Y', strtotime($transaction['action_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Người thực hiện:</th>
                                    <td><?php echo htmlspecialchars($transaction['action_by']); ?></td>
                                </tr>
                                <tr>
                                    <th>Trạng thái:</th>
                                    <td>
                                        <?php 
                                        $status_class = [
                                            'Completed' => 'success',
                                            'Pending' => 'warning',
                                            'Cancelled' => 'danger'
                                        ][$transaction['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $status_class; ?>">
                                            <?php echo $transaction['status']; ?>
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
                                    <td><?php echo htmlspecialchars($transaction['product_code']); ?></td>
                                </tr>
                                <tr>
                                    <th>Tên sản phẩm:</th>
                                    <td><?php echo htmlspecialchars($transaction['product_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Số lượng:</th>
                                    <td>
                                        <span class="fw-bold"><?php echo number_format($transaction['quantity']); ?></span> cái
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tồn kho trước:</th>
                                    <td><?php echo number_format($transaction['old_stock']); ?> cái</td>
                                </tr>
                                <tr>
                                    <th>Tồn kho sau:</th>
                                    <td>
                                        <span class="fw-bold"><?php echo number_format($transaction['new_stock']); ?></span> cái
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
                                                <td><?php echo htmlspecialchars($row['reference_code']); ?></td>
                                                <td>
                                                    <?php if ($row['action_type'] === 'Import'): ?>
                                                        <span class="badge bg-success">Nhập kho</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Xuất kho</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <?php echo number_format($row['quantity']); ?>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted"><?php echo number_format($row['old_stock']); ?></small>
                                                        <div class="text-end">
                                                            <i class="fas fa-arrow-right text-muted small"></i>
                                                            <strong class="ms-1"><?php echo number_format($row['new_stock']); ?></strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['action_by']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['action_at'])); ?></td>
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
