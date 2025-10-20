<?php 
$page_title = $data['title'];
$active_page = 'inventory';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="page-header">
    <h1><i class="fas fa-plus-circle"></i> <?php echo $data['title']; ?></h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/inventory?type=<?php echo $data['type']; ?>">
                <?php echo $data['type'] === 'import' ? 'Nhập kho' : 'Xuất kho'; ?>
            </a></li>
            <li class="breadcrumb-item active" aria-current="page">Thêm mới</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form action="/inventory/store" method="POST" id="inventoryForm">
            <input type="hidden" name="type" value="<?php echo $data['type']; ?>">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="product_id" class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                    <select class="form-select" id="product_id" name="product_id" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        <?php foreach ($data['products'] as $product): ?>
                            <option value="<?php echo $product['product_id']; ?>" 
                                    data-stock="<?php echo $product['stock_quantity']; ?>">
                                <?php echo htmlspecialchars($product['product_name'] . ' (' . $product['product_code'] . ') - Tồn: ' . $product['stock_quantity']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Tồn kho hiện tại: <span id="currentStock">0</span></div>
                </div>
                
                <div class="col-md-6">
                    <label for="quantity" class="form-label">Số lượng <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               min="1" value="1" required>
                        <span class="input-group-text">cái</span>
                    </div>
                    <div id="quantityHelp" class="form-text"></div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="date" class="form-label">Ngày <?php echo $data['type'] === 'import' ? 'nhập' : 'xuất'; ?> <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <?php if ($data['type'] === 'export'): ?>
                <div class="col-md-6">
                    <label for="reason" class="form-label">Lý do xuất <span class="text-danger">*</span></label>
                    <select class="form-select" id="reason" name="reason" required>
                        <option value="Sale">Bán hàng</option>
                        <option value="Return">Trả hàng</option>
                        <option value="Damaged">Hỏng hóc</option>
                        <option value="Expired">Hết hạn</option>
                        <option value="Other">Khác</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label for="note" class="form-label">Ghi chú</label>
                <textarea class="form-control" id="note" name="note" rows="3"></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="/inventory?type=<?php echo $data['type']; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu lại
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity');
    const currentStockSpan = document.getElementById('currentStock');
    const quantityHelp = document.getElementById('quantityHelp');
    const form = document.getElementById('inventoryForm');
    const isExport = '<?php echo $data['type'] === 'export'; ?>' === '1';
    
    // Cập nhật số lượng tồn kho khi chọn sản phẩm
    function updateStockInfo() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const stock = selectedOption ? parseInt(selectedOption.dataset.stock) || 0 : 0;
        currentStockSpan.textContent = stock.toLocaleString();
        
        // Nếu là xuất kho, đặt giá trị tối đa cho số lượng
        if (isExport) {
            quantityInput.max = stock;
            if (quantityInput.value > stock) {
                quantityInput.value = stock;
            }
            updateQuantityHelp(stock);
        }
    }
    
    // Cập nhật thông báo số lượng
    function updateQuantityHelp(stock) {
        if (!isExport) return;
        
        const quantity = parseInt(quantityInput.value) || 0;
        if (quantity > stock) {
            quantityHelp.textContent = `Số lượng vượt quá tồn kho (${stock})`;
            quantityHelp.className = 'form-text text-danger';
            quantityInput.setCustomValidity('Số lượng không được vượt quá tồn kho');
        } else {
            quantityHelp.textContent = `Còn lại: ${stock - quantity}`;
            quantityHelp.className = 'form-text text-success';
            quantityInput.setCustomValidity('');
        }
    }
    
    // Sự kiện thay đổi sản phẩm
    productSelect.addEventListener('change', updateStockInfo);
    
    // Sự kiện thay đổi số lượng
    quantityInput.addEventListener('input', function() {
        const stock = parseInt(productSelect.options[productSelect.selectedIndex]?.dataset.stock || 0);
        updateQuantityHelp(stock);
    });
    
    // Validate form trước khi submit
    form.addEventListener('submit', function(e) {
        if (isExport) {
            const stock = parseInt(productSelect.options[productSelect.selectedIndex]?.dataset.stock || 0);
            const quantity = parseInt(quantityInput.value) || 0;
            
            if (quantity > stock) {
                e.preventDefault();
                alert('Số lượng xuất không được vượt quá tồn kho hiện có!');
                return false;
            }
        }
        return true;
    });
    
    // Khởi tạo ban đầu
    updateStockInfo();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
