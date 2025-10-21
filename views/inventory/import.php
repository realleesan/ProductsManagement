<?php 
$page_title = 'Nhập kho';
$active_page = 'inventory';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="page-header">
    <h1><i class="fas fa-plus-circle"></i> Tạo phiếu nhập kho</h1>
    <a href="/quanlysanpham/controllers/InventoryController.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-plus-circle me-2 text-primary"></i>Thông tin nhập kho
        </h5>
    </div>
    <div class="card-body">
        <form method="post" action="?action=import" id="importForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="import_code" class="form-label fw-medium">Mã phiếu nhập <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="import_code" name="import_code" 
                               value="<?php echo $import_code; ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="import_date" class="form-label fw-medium">Ngày nhập <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control form-control-lg" id="import_date" name="import_date" 
                               value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label for="product_id" class="form-label fw-medium">Sản phẩm <span class="text-danger">*</span></label>
                <select class="form-select form-select-lg" id="product_id" name="product_id" required>
                    <option value="">Chọn sản phẩm</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['product_id']; ?>" 
                                data-stock="<?php echo $product['stock_quantity']; ?>"
                                data-price="<?php echo $product['price']; ?>"
                                <?php echo (isset($_SESSION['form_data']['product_id']) && $_SESSION['form_data']['product_id'] == $product['product_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($product['product_code'] . ' - ' . $product['product_name']); ?>
                            <span class="text-muted">(Tồn: <?php echo number_format($product['stock_quantity']); ?>)</span>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity" class="form-label fw-medium">Số lượng nhập <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <input type="number" class="form-control form-control-lg" id="quantity" name="quantity" 
                                   min="1" step="1" value="1" required>
                            <span class="input-group-text">cái</span>
                        </div>
                        <div class="form-text">Số lượng tối thiểu: 1</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-medium">Tồn kho sau nhập</label>
                        <div class="form-control form-control-lg bg-light" id="stock_after">-</div>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label for="note" class="form-label fw-medium">Ghi chú</label>
                <textarea class="form-control form-control-lg" id="note" name="note" rows="3" placeholder="Nhập ghi chú (nếu có)"><?php echo isset($_SESSION['form_data']['note']) ? htmlspecialchars($_SESSION['form_data']['note']) : ''; ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="/quanlysanpham/controllers/InventoryController.php" class="btn btn-lg btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Hủy bỏ
                </a>
                <button type="submit" class="btn btn-lg btn-primary">
                    <i class="fas fa-save me-1"></i> Lưu phiếu nhập
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity');
    const stockAfterSpan = document.getElementById('stock_after');
    
    // Cập nhật tồn kho sau khi nhập
    function updateStockAfter() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (selectedOption.value) {
            const currentStock = parseInt(selectedOption.dataset.stock) || 0;
            const quantity = parseInt(quantityInput.value) || 0;
            stockAfterSpan.textContent = currentStock + quantity;
        } else {
            stockAfterSpan.textContent = '-';
        }
    }
    
    // Lắng nghe sự kiện thay đổi sản phẩm
    productSelect.addEventListener('change', updateStockAfter);
    
    // Lắng nghe sự kiện thay đổi số lượng
    quantityInput.addEventListener('input', updateStockAfter);
    
    // Khởi tạo giá trị ban đầu
    updateStockAfter();
    
    // Xác nhận trước khi gửi form
    document.getElementById('importForm').addEventListener('submit', function(e) {
        if (!confirm('Bạn có chắc chắn muốn lưu phiếu nhập kho này?')) {
            e.preventDefault();
            return false;
        }
        return true;
    });
});
</script>

<?php 
// Xóa dữ liệu form đã lưu trong session
unset($_SESSION['form_data']); 
include_once __DIR__ . '/../layouts/footer.php'; 
?>
