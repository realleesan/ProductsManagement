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
                <label for="product_search" class="form-label fw-medium">Sản phẩm <span class="text-danger">*</span></label>
                
                <!-- Custom searchable dropdown -->
                <div class="position-relative mb-3" id="product-dropdown-wrapper" style="position: relative !important; z-index: 100;">
                    <!-- Search input -->
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               class="form-control" 
                               id="product_search" 
                               placeholder="Tìm kiếm theo mã hoặc tên sản phẩm..."
                               autocomplete="off"
                               readonly
                               style="cursor: pointer;">
                        <span class="input-group-text" style="cursor: pointer;" id="dropdown-toggle">
                            <i class="fas fa-chevron-down" id="dropdown-icon"></i>
                        </span>
                    </div>
                    
                    <!-- Dropdown list -->
                    <div class="border rounded bg-white shadow-lg" id="product-dropdown" style="display: none; max-height: 300px; position: absolute; width: 100%; z-index: 1050; top: 100%; left: 0; margin-top: 2px; background: white; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);">
                        <div class="p-2 border-bottom bg-white" style="position: sticky; top: 0; z-index: 11;">
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   id="product_filter" 
                                   placeholder="Nhập mã hoặc tên để tìm kiếm..."
                                   autocomplete="off">
                        </div>
                        <div id="product-list" style="max-height: 250px; overflow-y: auto; overflow-x: hidden;">
                            <?php foreach ($products as $product): ?>
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <div class="dropdown-item product-option p-2 border-bottom" 
                                         style="cursor: pointer;"
                                         data-id="<?php echo $product['product_id']; ?>"
                                         data-stock="<?php echo $product['stock_quantity']; ?>"
                                         data-price="<?php echo $product['price']; ?>"
                                         data-status="<?php echo $product['status']; ?>"
                                         data-code="<?php echo htmlspecialchars(strtolower($product['product_code'])); ?>"
                                         data-name="<?php echo htmlspecialchars(strtolower($product['product_name'])); ?>"
                                         data-display="<?php echo htmlspecialchars($product['product_code'] . ' - ' . $product['product_name']); ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong><?php echo htmlspecialchars($product['product_code'] . ' - ' . $product['product_name']); ?></strong>
                                                <div class="text-muted small">(Tồn: <?php echo number_format($product['stock_quantity']); ?>)</div>
                                            </div>
                                            <span class="<?php echo $product['status'] === 'Expired' ? 'text-danger fw-bold' : 'text-success'; ?>">
                                                <?php echo $product['status'] === 'Expired' ? 'HẾT HẠN' : 'CÒN HẠN'; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div id="no-results" class="p-3 text-center text-muted" style="display: none;">
                            Không tìm thấy sản phẩm
                        </div>
                    </div>
                    
                    <!-- Hidden input for form submission -->
                    <input type="hidden" id="product_id" name="product_id" required>
                </div>
                
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
    const productSearch = document.getElementById('product_search');
    const productFilter = document.getElementById('product_filter');
    const productDropdown = document.getElementById('product-dropdown');
    const productList = document.getElementById('product-list');
    const productOptions = document.querySelectorAll('.product-option');
    const productIdInput = document.getElementById('product_id');
    const dropdownToggle = document.getElementById('dropdown-toggle');
    const dropdownIcon = document.getElementById('dropdown-icon');
    const quantityInput = document.getElementById('quantity');
    const stockAfterSpan = document.getElementById('stock_after');
    const maxQuantitySpan = document.getElementById('max_quantity');
    const noResults = document.getElementById('no-results');
    
    let selectedProduct = null;

    // Toggle dropdown
    function toggleDropdown(e) {
        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }
        const isVisible = productDropdown.style.display === 'block';
        if (isVisible) {
            closeDropdown();
        } else {
            openDropdown();
        }
    }

    // Mở dropdown
    function openDropdown(e) {
        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }
        productDropdown.style.display = 'block';
        dropdownIcon.className = 'fas fa-chevron-up';
        productFilter.focus();
        filterProducts('');
    }

    // Đóng dropdown
    function closeDropdown() {
        productDropdown.style.display = 'none';
        dropdownIcon.className = 'fas fa-chevron-down';
    }

    // Filter sản phẩm
    function filterProducts(searchTerm) {
        searchTerm = searchTerm.toLowerCase().trim();
        let visibleCount = 0;

        productOptions.forEach(function(option) {
            const productCode = option.getAttribute('data-code') || '';
            const productName = option.getAttribute('data-name') || '';
            
            if (searchTerm === '' || productCode.includes(searchTerm) || productName.includes(searchTerm)) {
                option.style.display = '';
                visibleCount++;
            } else {
                option.style.display = 'none';
            }
        });

        if (visibleCount === 0 && searchTerm !== '') {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    }

    // Chọn sản phẩm
    function selectProduct(option) {
        const productId = option.getAttribute('data-id');
        const displayText = option.getAttribute('data-display');
        const stock = option.getAttribute('data-stock');
        
        productIdInput.value = productId;
        productSearch.value = displayText;
        selectedProduct = {
            id: productId,
            stock: parseInt(stock) || 0
        };
        
        closeDropdown();
        updateStockAfter();
        
        // Highlight selected option
        productOptions.forEach(function(opt) {
            opt.style.backgroundColor = '';
        });
        option.style.backgroundColor = '#e7f3ff';
    }

    // Event listeners
    productSearch.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        openDropdown(e);
    });
    
    dropdownToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleDropdown(e);
    });

    productFilter.addEventListener('input', function() {
        filterProducts(this.value);
    });
    
    productFilter.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Ngăn đóng dropdown khi click vào bất kỳ phần nào của dropdown
    if (productDropdown) {
        productDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    productOptions.forEach(function(option) {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            selectProduct(this);
        });
        
        option.addEventListener('mouseenter', function() {
            if (this.style.backgroundColor !== '#e7f3ff') {
                this.style.backgroundColor = '#f8f9fa';
            }
        });
        
        option.addEventListener('mouseleave', function() {
            if (this.style.backgroundColor !== '#e7f3ff') {
                this.style.backgroundColor = '';
            }
        });
    });

    // Chỉ đóng dropdown khi click vào toggle hoặc chọn sản phẩm

    // Không cho phép nhập trực tiếp vào search box
    productSearch.addEventListener('keydown', function(e) {
        e.preventDefault();
        if (e.key === 'Enter' || e.key === ' ') {
            openDropdown(e);
        }
    });

    // Cập nhật tồn kho sau khi xuất
    function updateStockAfter() {
        if (selectedProduct && selectedProduct.id) {
            const currentStock = selectedProduct.stock || 0;
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
    
    // Lắng nghe sự kiện thay đổi số lượng
    quantityInput.addEventListener('input', updateStockAfter);
    
    // Đảm bảo dropdown đóng khi khởi tạo
    closeDropdown();
    
    // Khởi tạo giá trị ban đầu - khôi phục sản phẩm đã chọn nếu có
    <?php if (isset($_SESSION['form_data']['product_id'])): ?>
        const savedProductId = <?php echo (int)$_SESSION['form_data']['product_id']; ?>;
        productOptions.forEach(function(option) {
            if (parseInt(option.getAttribute('data-id')) === savedProductId) {
                selectProduct(option);
            }
        });
    <?php endif; ?>
    
    updateStockAfter();

    // Xác nhận trước khi gửi form
    document.getElementById('exportForm').addEventListener('submit', function(e) {
        if (!confirm('Bạn có chắc chắn muốn lưu phiếu xuất kho này?')) {
            e.preventDefault();
            return false;
        }

        if (!selectedProduct || !selectedProduct.id) {
            alert('Vui lòng chọn sản phẩm!');
            e.preventDefault();
            return false;
        }

        const currentStock = selectedProduct.stock || 0;
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
