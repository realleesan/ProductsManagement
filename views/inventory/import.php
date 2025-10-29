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
                                data-status="<?php echo $product['status']; ?>"
                                data-manufacture="<?php echo $product['manufacture_date']; ?>"
                                data-expiry="<?php echo $product['expiry_date']; ?>"
                                <?php echo (isset($_SESSION['form_data']['product_id']) && $_SESSION['form_data']['product_id'] == $product['product_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($product['product_code'] . ' - ' . $product['product_name']); ?>
                            <span class="text-muted">(Tồn: <?php echo number_format($product['stock_quantity']); ?>)</span>
                            <span class="<?php echo $product['status'] === 'Expired' ? 'text-danger fw-bold' : ($product['status'] === 'Out of stock' ? 'text-warning fw-bold' : 'text-success'); ?>">
                                - <?php 
                                    switch($product['status']) {
                                        case 'Expired': echo 'HẾT HẠN'; break;
                                        case 'Out of stock': echo 'HẾT HÀNG'; break;
                                        case 'Active': echo 'CÒN HẠN'; break;
                                        default: echo strtoupper($product['status']); break;
                                    }
                                ?>
                            </span>
                            <?php if ($product['status'] === 'Expired'): ?>
                                <span class="text-muted small">(HSD: <?php echo date('d/m/Y', strtotime($product['expiry_date'])); ?>)</span>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="product-warning" class="alert alert-warning mt-2" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Cảnh báo:</strong> Sản phẩm này đã hết hạn. Bạn sẽ cần cập nhật thông tin ngày sản xuất và hạn sử dụng mới để tiếp tục nhập kho.
                </div>
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

            <!-- Thông tin cập nhật cho sản phẩm hết hạn -->
            <div id="expired-product-info" class="card border-warning mb-4" style="display: none;">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Sản phẩm hết hạn - Cần cập nhật thông tin</strong>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Lưu ý:</strong> Sản phẩm này đã hết hạn. Vui lòng cập nhật thông tin ngày sản xuất và hạn sử dụng mới để tiếp tục nhập kho.
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="new_manufacture_date" class="form-label fw-medium">
                                <i class="fas fa-calendar-plus me-1"></i>
                                Ngày sản xuất mới <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-lg" id="new_manufacture_date" name="new_manufacture_date" 
                                   value="<?php echo isset($_SESSION['form_data']['new_manufacture_date']) ? $_SESSION['form_data']['new_manufacture_date'] : date('Y-m-d'); ?>"
                                   max="<?php echo date('Y-m-d'); ?>">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Ngày sản xuất của lô hàng mới (không được vượt quá ngày hiện tại)
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="new_expiry_date" class="form-label fw-medium">
                                <i class="fas fa-calendar-times me-1"></i>
                                Ngày hết hạn mới <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-lg" id="new_expiry_date" name="new_expiry_date" 
                                   value="<?php echo isset($_SESSION['form_data']['new_expiry_date']) ? $_SESSION['form_data']['new_expiry_date'] : ''; ?>"
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Ngày hết hạn của lô hàng mới (phải sau ngày sản xuất)
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirm_expired_update" name="confirm_expired_update">
                            <label class="form-check-label fw-medium" for="confirm_expired_update">
                                <i class="fas fa-check-circle me-1"></i>
                                Tôi xác nhận đã kiểm tra và cập nhật đúng thông tin ngày sản xuất, hạn sử dụng cho sản phẩm này
                            </label>
                        </div>
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
    productSelect.addEventListener('change', function() {
        updateStockAfter();
        toggleExpiredProductInfo();
        toggleProductWarning();
    });
    
    // Hiển thị/ẩn cảnh báo sản phẩm hết hạn
    function toggleProductWarning() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const warningDiv = document.getElementById('product-warning');
        
        if (selectedOption && selectedOption.value && selectedOption.dataset.status === 'Expired') {
            warningDiv.style.display = 'block';
        } else {
            warningDiv.style.display = 'none';
        }
    }
    
    // Lắng nghe sự kiện thay đổi số lượng
    quantityInput.addEventListener('input', updateStockAfter);
    
    // Hiển thị/ẩn form cập nhật thông tin cho sản phẩm hết hạn
    function toggleExpiredProductInfo() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const expiredInfo = document.getElementById('expired-product-info');
        const newManufactureDate = document.getElementById('new_manufacture_date');
        const newExpiryDate = document.getElementById('new_expiry_date');
        const confirmCheckbox = document.getElementById('confirm_expired_update');
        
        if (!expiredInfo || !newManufactureDate || !newExpiryDate) {
            return;
        }
        
        // Chỉ hiện khi chọn sản phẩm hết hạn
        if (selectedOption && selectedOption.value && selectedOption.dataset.status === 'Expired') {
            expiredInfo.style.display = 'block';
            newManufactureDate.required = true;
            newExpiryDate.required = true;
            confirmCheckbox.required = true;
            
            // Đặt giá trị mặc định cho ngày sản xuất (hôm nay)
            if (!newManufactureDate.value) {
                newManufactureDate.value = new Date().toISOString().split('T')[0];
            }
            
            // Đặt giá trị mặc định cho ngày hết hạn (30 ngày sau)
            if (!newExpiryDate.value) {
                const futureDate = new Date();
                futureDate.setDate(futureDate.getDate() + 30);
                newExpiryDate.value = futureDate.toISOString().split('T')[0];
            }
            
            // Cập nhật min/max cho các input date
            newManufactureDate.max = new Date().toISOString().split('T')[0];
            newExpiryDate.min = new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            
        } else {
            expiredInfo.style.display = 'none';
            newManufactureDate.required = false;
            newExpiryDate.required = false;
            confirmCheckbox.required = false;
            
            // Reset giá trị
            newManufactureDate.value = '';
            newExpiryDate.value = '';
            confirmCheckbox.checked = false;
        }
    }
    
    // Xử lý thay đổi ngày sản xuất
    document.getElementById('new_manufacture_date').addEventListener('change', function() {
        const manufactureDate = new Date(this.value);
        const expiryDateInput = document.getElementById('new_expiry_date');
        
        if (manufactureDate) {
            // Đặt min cho ngày hết hạn là ngày sau ngày sản xuất
            const minExpiryDate = new Date(manufactureDate);
            minExpiryDate.setDate(minExpiryDate.getDate() + 1);
            expiryDateInput.min = minExpiryDate.toISOString().split('T')[0];
            
            // Nếu ngày hết hạn hiện tại nhỏ hơn ngày sản xuất, reset nó
            if (expiryDateInput.value && new Date(expiryDateInput.value) <= manufactureDate) {
                expiryDateInput.value = '';
            }
        }
    });
    
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
