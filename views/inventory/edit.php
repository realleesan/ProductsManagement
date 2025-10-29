<?php 
$page_title = 'Chỉnh sửa giao dịch kho';
$active_page = 'inventory';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-edit"></i> Chỉnh sửa giao dịch</h1>
    <div>
        <a href="<?php echo BASE_URL; ?>/controllers/InventoryController.php?action=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    
    
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/InventoryController.php?action=update">
            <input type="hidden" name="history_id" value="<?php echo (int)$history['history_id']; ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Mã tham chiếu</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($history['reference_code']); ?>" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Loại giao dịch</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($history['action_type'] === 'Import' ? 'Nhập kho' : 'Xuất kho'); ?>" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sản phẩm</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars(($history['product_code'] ?? '') . ' - ' . ($history['product_name'] ?? '')); ?>" disabled>
                </div>

                <div class="col-md-4">
                    <label for="quantity" class="form-label">Số lượng</label>
                    <input type="number" min="0" class="form-control" id="quantity" name="quantity" value="<?php echo (int)$history['quantity']; ?>" required>
                </div>

                <div class="col-md-8">
                    <label for="note" class="form-label">Ghi chú</label>
                    <input type="text" class="form-control" id="note" name="note" value="<?php echo htmlspecialchars($history['note'] ?? ''); ?>" placeholder="Ghi chú">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
                <a class="btn btn-light" href="<?php echo BASE_URL; ?>/controllers/InventoryController.php?action=index">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>


