<?php 
$page_title = 'Xuất kho';
$active_page = 'inventory';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="page-header">
    <h1><i class="fas fa-minus-circle"></i> Tạo phiếu xuất kho</h1>
    <a href="/quanlysanpham/controllers/InventoryController.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-minus-circle me-2 text-primary"></i>Thông tin xuất kho
        </h5>
    </div>
    <div class="card-body">
        <form method="post" action="?action=export" id="exportForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="export_code" class="form-label fw-medium">Mã phiếu xuất <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="export_code" name="export_code" 
                               value="<?php echo $export_code; ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="export_date" class="form-label fw-medium">Ngày xuất <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control form-control-lg" id="export_date" name="export_date" 
                               value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label for="product_id" class="form-label fw-medium">Sản phẩm <span class="text-danger">*</span></label>
                <select class="form-select form-select-lg" id="product_id" name="product_id" required>
                    <option value="">Chọn sản phẩm</option>
                    <?php foreach ($products as $product): ?>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <option value="<?php echo $product['product_id']; ?>" 
                                    data-stock="<?php echo $product['stock_quantity']; ?>"
                                    data-price="<?php echo $product['price']; ?>"
                                    data-status="<?php echo $product['status']; ?>"
                                    <?php echo (isset($_SESSION['form_data']['product_id']) && $_SESSION['form_data']['product_id'] == $product['product_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['product_code'] . ' - ' . $product['product_name']); ?>
                                <span class="text-muted">(Tồn: <?php echo number_format($product['stock_quantity']); ?>)</span>
                                <span class="<?php echo $product['status'] === 'Expired' ? 'text-danger fw-bold' : 'text-success'; ?>">
                                    - <?php echo $product['status'] === 'Expired' ? 'HẾT HẠN' : 'CÒN HẠN'; ?>
                                </span>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Chỉ hiển thị sản phẩm còn hàng trong kho</div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity" class="form-label fw-medium">Số lượng xuất <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <input type="number" class="form-control form-control-lg" id="quantity" name="quantity" 
                                   min="1" step="1" value="1" required>
                            <span class="input-group-text">cái</span>
                        </div>
                        <div class="form-text">Số lượng tối đa: <span id="max_quantity" class="fw-bold">0</span></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-medium">Tồn kho sau xuất</label>
                        <div class="form-control form-control-lg bg-light" id="stock_after">-</div>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label for="reason" class="form-label fw-medium">Lý do xuất <span class="text-danger">*</span></label>
                <select class="form-select form-select-lg" id="reason" name="reason" required>
                    <option value="">Chọn lý do xuất</option>
                    <option value="Sale" <?php echo (isset($_SESSION['form_data']['reason']) && $_SESSION['form_data']['reason'] === 'Sale') ? 'selected' : ''; ?>>Bán hàng</option>
                    <option value="Return" <?php echo (isset($_SESSION['form_data']['reason']) && $_SESSION['form_data']['reason'] === 'Return') ? 'selected' : ''; ?>>Trả hàng nhà cung cấp</option>
                    <option value="Damaged" <?php echo (isset($_SESSION['form_data']['reason']) && $_SESSION['form_data']['reason'] === 'Damaged') ? 'selected' : ''; ?>>Hỏng hóc</option>
                    <option value="Expired" <?php echo (isset($_SESSION['form_data']['reason']) && $_SESSION['form_data']['reason'] === 'Expired') ? 'selected' : ''; ?>>Hết hạn</option>
                    <option value="Other" <?php echo (isset($_SESSION['form_data']['reason']) && $_SESSION['form_data']['reason'] === 'Other') ? 'selected' : ''; ?>>Lý do khác</option>
                </select>
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
                    <i class="fas fa-save me-1"></i> Lưu phiếu xuất
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
    const maxQuantitySpan = document.getElementById('max_quantity');
    
    // Cập nhật tồn kho sau khi xuất
    function updateStockAfter() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (selectedOption.value) {
            const currentStock = parseInt(selectedOption.dataset.stock) || 0;
            const quantity = parseInt(quantityInput.value) || 0;
            const newStock = currentStock - quantity;
            stockAfterSpan.textContent = newStock >= 0 ? newStock : 'Không đủ hàng';
            
            // Cập nhật số lượng tối đa có thể xuất
            maxQuantitySpan.textContent = currentStock;
            
            // Đặt giá trị tối đa cho input số lượng
            quantityInput.max = currentStock;
            
            // Hiển thị cảnh báo nếu số lượng vượt quá tồn kho
            if (quantity > currentStock) {
                stockAfterSpan.classList.add('text-danger', 'fw-bold');
                document.querySelector('button[type="submit"]').disabled = true;
            } else {
                stockAfterSpan.classList.remove('text-danger', 'fw-bold');
                document.querySelector('button[type="submit"]').disabled = false;
            }
        } else {
            stockAfterSpan.textContent = '-';
            maxQuantitySpan.textContent = '0';
        }
    }
    
    // Lắng nghe sự kiện thay đổi sản phẩm
    productSelect.addEventListener('change', updateStockAfter);
    
    // Lắng nghe sự kiện thay đổi số lượng
    quantityInput.addEventListener('input', updateStockAfter);
    
    // Khởi tạo giá trị ban đầu
    updateStockAfter();
    
    // Xác nhận trước khi gửi form
    document.getElementById('exportForm').addEventListener('submit', function(e) {
        if (!confirm('Bạn có chắc chắn muốn lưu phiếu xuất kho này?')) {
            e.preventDefault();
            return false;
        }
        
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const currentStock = parseInt(selectedOption.dataset.stock) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        
        if (quantity > currentStock) {
            alert('Số lượng xuất vượt quá tồn kho hiện có!');
            e.preventDefault();
            return false;
        }
        
        if (!document.getElementById('reason').value) {
            alert('Vui lòng chọn lý do xuất kho!');
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
