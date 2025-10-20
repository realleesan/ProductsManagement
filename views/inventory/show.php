<?php 
$page_title = $data['title'];
$active_page = 'inventory';
$transaction = $data['transaction'];
$type = $data['type'];
$isImport = $type === 'import';

require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="page-header">
    <h1><i class="fas fa-file-invoice"></i> <?php echo $page_title; ?></h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/inventory?type=<?php echo $type; ?>">
                <?php echo $isImport ? 'Nhập kho' : 'Xuất kho'; ?>
            </a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Thông tin phiếu <?php echo $isImport ? 'nhập' : 'xuất'; ?> kho</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%;">Mã phiếu:</th>
                            <td><?php echo $isImport ? $transaction['import_code'] : $transaction['export_code']; ?></td>
                        </tr>
                        <tr>
                            <th>Ngày <?php echo $isImport ? 'nhập' : 'xuất'; ?>:</th>
                            <td><?php echo date('d/m/Y', strtotime($isImport ? $transaction['import_date'] : $transaction['export_date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Người thực hiện:</th>
                            <td><?php echo $isImport ? $transaction['import_by'] : $transaction['export_by']; ?></td>
                        </tr>
                        <?php if (!$isImport): ?>
                        <tr>
                            <th>Lý do:</th>
                            <td>
                                <?php 
                                $reasons = [
                                    'Sale' => 'Bán hàng',
                                    'Return' => 'Trả hàng',
                                    'Damaged' => 'Hỏng hóc',
                                    'Expired' => 'Hết hạn',
                                    'Other' => 'Khác'
                                ];
                                echo $reasons[$transaction['reason']] ?? $transaction['reason'];
                                ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Trạng thái:</th>
                            <td>
                                <?php 
                                $statusClass = $transaction['status'] === 'Completed' ? 'success' : 
                                             ($transaction['status'] === 'Pending' ? 'warning' : 'danger');
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo $transaction['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php if (!empty($transaction['note'])): ?>
                        <tr>
                            <th>Ghi chú:</th>
                            <td><?php echo nl2br(htmlspecialchars($transaction['note'])); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Thông tin sản phẩm</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <?php 
                        $imagePath = !empty($transaction['image_url']) 
                            ? '/uploads/products/' . $transaction['image_url'] 
                            : '/assets/img/no-image.png';
                        ?>
                        <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($transaction['product_name']); ?>" 
                             class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 30%;">Mã sản phẩm:</th>
                                <td><?php echo $transaction['product_code']; ?></td>
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
                                <th>Đơn giá:</th>
                                <td><?php echo number_format($transaction['price'], 0, ',', '.'); ?> đ</td>
                            </tr>
                            <tr>
                                <th>Thành tiền:</th>
                                <td class="text-danger fw-bold">
                                    <?php echo number_format($transaction['price'] * $transaction['quantity'], 0, ',', '.'); ?> đ
                                </td>
                            </tr>
                            <tr>
                                <th>Hạn sử dụng:</th>
                                <td>
                                    <?php 
                                    $expiryDate = !empty($transaction['expiry_date']) 
                                        ? date('d/m/Y', strtotime($transaction['expiry_date'])) 
                                        : 'Không xác định';
                                    echo $expiryDate;
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tồn kho hiện tại:</th>
                                <td>
                                    <?php 
                                    $stockClass = $transaction['stock_quantity'] <= $transaction['min_stock'] 
                                        ? 'text-danger' 
                                        : '';
                                    ?>
                                    <span class="<?php echo $stockClass; ?>">
                                        <?php echo number_format($transaction['stock_quantity']); ?> cái
                                        <?php if ($transaction['stock_quantity'] <= $transaction['min_stock']): ?>
                                            <i class="fas fa-exclamation-triangle ms-2" title="Sắp hết hàng"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Thông tin bổ sung -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Thao tác</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/inventory?type=<?php echo $type; ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                    
                    <?php if ($transaction['status'] === 'Pending'): ?>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="fas fa-check"></i> Duyệt phiếu
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times"></i> Từ chối
                        </button>
                    <?php endif; ?>
                    
                    <a href="/inventory/print/<?php echo $isImport ? $transaction['import_id'] : $transaction['export_id']; ?>?type=<?php echo $type; ?>" 
                       class="btn btn-info" target="_blank">
                        <i class="fas fa-print"></i> In phiếu
                    </a>
                    
                    <?php if ($transaction['status'] === 'Pending'): ?>
                        <a href="/inventory/edit/<?php echo $isImport ? $transaction['import_id'] : $transaction['export_id']; ?>?type=<?php echo $type; ?>" 
                           class="btn btn-warning">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Lịch sử thay đổi -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Lịch sử thay đổi</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <small class="text-muted">Tạo mới</small>
                            <small class="text-muted">
                                <?php echo date('d/m/Y H:i', strtotime($isImport ? $transaction['created_at'] : $transaction['created_at'])); ?>
                            </small>
                        </div>
                        <p class="mb-0">
                            <i class="fas fa-user me-1"></i> 
                            <?php echo $isImport ? $transaction['import_by'] : $transaction['export_by']; ?>
                        </p>
                    </div>
                    
                    <?php if ($isImport && $transaction['status'] === 'Completed'): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <small class="text-muted">Đã duyệt</small>
                            <small class="text-muted">
                                <?php echo date('d/m/Y H:i', strtotime($transaction['updated_at'])); ?>
                            </small>
                        </div>
                        <p class="mb-0">
                            <i class="fas fa-user-check me-1"></i> 
                            <?php echo $transaction['import_by']; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận duyệt -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">Xác nhận duyệt phiếu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn duyệt phiếu này không?</p>
                <p class="mb-0"><strong>Lưu ý:</strong> Hệ thống sẽ cập nhật số lượng tồn kho sau khi duyệt.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form action="/inventory/approve/<?php echo $isImport ? $transaction['import_id'] : $transaction['export_id']; ?>" method="POST" class="d-inline">
                    <input type="hidden" name="type" value="<?php echo $type; ?>">
                    <button type="submit" class="btn btn-success">Xác nhận duyệt</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal từ chối -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">Xác nhận từ chối</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form action="/inventory/reject/<?php echo $isImport ? $transaction['import_id'] : $transaction['export_id']; ?>" method="POST">
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn từ chối phiếu này không?</p>
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">Lý do từ chối:</label>
                        <textarea class="form-control" id="rejectReason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="type" value="<?php echo $type; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
