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
                                <div class="dropdown-item product-option p-2 border-bottom" 
                                     style="cursor: pointer;"
                                     data-id="<?php echo $product['product_id']; ?>"
                                     data-stock="<?php echo $product['stock_quantity']; ?>"
                                     data-price="<?php echo $product['price']; ?>"
                                     data-status="<?php echo $product['status']; ?>"
                                     data-manufacture="<?php echo $product['manufacture_date']; ?>"
                                     data-expiry="<?php echo $product['expiry_date']; ?>"
                                     data-code="<?php echo htmlspecialchars(strtolower($product['product_code'])); ?>"
                                     data-name="<?php echo htmlspecialchars(strtolower($product['product_name'])); ?>"
                                     data-display="<?php echo htmlspecialchars($product['product_code'] . ' - ' . $product['product_name']); ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?php echo htmlspecialchars($product['product_code'] . ' - ' . $product['product_name']); ?></strong>
                                            <div class="text-muted small">(Tồn: <?php echo number_format($product['stock_quantity']); ?>)</div>
                                        </div>
                                        <span class="<?php echo $product['status'] === 'Expired' ? 'text-danger fw-bold' : ($product['status'] === 'Out of stock' ? 'text-warning fw-bold' : 'text-success'); ?>">
                                            <?php 
                                                switch($product['status']) {
                                                    case 'Expired': echo 'HẾT HẠN'; break;
                                                    case 'Out of stock': echo 'HẾT HÀNG'; break;
                                                    case 'Active': echo 'CÒN HẠN'; break;
                                                    default: echo strtoupper($product['status']); break;
                                                }
                                            ?>
                                        </span>
                                    </div>
                                    <?php if ($product['status'] === 'Expired'): ?>
                                        <div class="text-muted small mt-1">(HSD: <?php echo date('d/m/Y', strtotime($product['expiry_date'])); ?>)</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="no-results" class="p-3 text-center text-muted" style="display: none;">
                            Không tìm thấy sản phẩm
                        </div>
                    </div>
                    
                    <!-- Hidden input for form submission -->
                    <input type="hidden" id="product_id" name="product_id" required>
                </div>
                
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

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="unit_price" class="form-label fw-medium">Đơn giá nhập <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <input type="number" class="form-control form-control-lg" id="unit_price" name="unit_price" 
                                   min="1000" max="1000000000" step="1000" 
                                   value="<?php echo isset($_SESSION['form_data']['unit_price']) ? htmlspecialchars($_SESSION['form_data']['unit_price']) : ''; ?>" 
                                   required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <div class="form-text">Đơn giá từ 1.000 đến 1.000.000.000 VNĐ</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-medium">Thành tiền</label>
                        <div class="form-control form-control-lg bg-light fw-bold text-primary" id="total_amount">0 VNĐ</div>
                        <input type="hidden" id="total_amount_input" name="total_amount" value="0">
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
    const unitPriceInput = document.getElementById('unit_price');
    const totalAmountSpan = document.getElementById('total_amount');
    const totalAmountInput = document.getElementById('total_amount_input');
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
        const status = option.getAttribute('data-status');
        const manufacture = option.getAttribute('data-manufacture');
        const expiry = option.getAttribute('data-expiry');
        
        productIdInput.value = productId;
        productSearch.value = displayText;
        const price = parseFloat(option.getAttribute('data-price')) || 0;
        selectedProduct = {
            id: productId,
            stock: parseInt(stock) || 0,
            status: status,
            manufacture: manufacture,
            expiry: expiry,
            price: price
        };
        
        closeDropdown();
        updateStockAfter();
        updateUnitPriceFromProduct();
        toggleExpiredProductInfo();
        toggleProductWarning();
        
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

    // Cập nhật tồn kho sau khi nhập
    function updateStockAfter() {
        if (selectedProduct && selectedProduct.id) {
            const currentStock = selectedProduct.stock || 0;
            const quantity = parseInt(quantityInput.value) || 0;
            stockAfterSpan.textContent = currentStock + quantity;
        } else {
            stockAfterSpan.textContent = '-';
        }
        updateTotalAmount();
    }

    // Cập nhật thành tiền
    function updateTotalAmount() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const totalAmount = quantity * unitPrice;
        
        if (totalAmount > 0) {
            totalAmountSpan.textContent = new Intl.NumberFormat('vi-VN').format(totalAmount) + ' VNĐ';
            totalAmountInput.value = totalAmount;
        } else {
            totalAmountSpan.textContent = '0 VNĐ';
            totalAmountInput.value = 0;
        }
    }

    // Lắng nghe sự kiện thay đổi sản phẩm
    function onProductChange() {
        updateStockAfter();
        toggleExpiredProductInfo();
        toggleProductWarning();
    }
    
        // Hiển thị/ẩn cảnh báo sản phẩm hết hạn
    function toggleProductWarning() {
        const warningDiv = document.getElementById('product-warning');

        if (selectedProduct && selectedProduct.id && selectedProduct.status === 'Expired') {
            warningDiv.style.display = 'block';
        } else {
            warningDiv.style.display = 'none';
        }
    }
    
    // Lắng nghe sự kiện thay đổi số lượng
    quantityInput.addEventListener('input', updateStockAfter);
    
    // Lắng nghe sự kiện thay đổi đơn giá
    unitPriceInput.addEventListener('input', updateTotalAmount);
    
    // Kiểm tra đơn giá khi khởi tạo - tự động điền giá hiện tại của sản phẩm
    function updateUnitPriceFromProduct() {
        if (selectedProduct && selectedProduct.id && selectedProduct.price) {
            const currentPrice = parseFloat(selectedProduct.price) || 0;
            if (!unitPriceInput.value || unitPriceInput.value === '0') {
                unitPriceInput.value = currentPrice;
                updateTotalAmount();
            }
        }
    }
    
        // Hiển thị/ẩn form cập nhật thông tin cho sản phẩm hết hạn
    function toggleExpiredProductInfo() {
        const expiredInfo = document.getElementById('expired-product-info');
        const newManufactureDate = document.getElementById('new_manufacture_date');
        const newExpiryDate = document.getElementById('new_expiry_date');
        const confirmCheckbox = document.getElementById('confirm_expired_update');

        if (!expiredInfo || !newManufactureDate || !newExpiryDate) {
            return;
        }

        // Chỉ hiện khi chọn sản phẩm hết hạn
        if (selectedProduct && selectedProduct.id && selectedProduct.status === 'Expired') {
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
    document.getElementById('importForm').addEventListener('submit', function(e) {
        // Validate đơn giá
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        if (unitPrice < 1000 || unitPrice > 1000000000) {
            alert('Đơn giá nhập phải từ 1.000 đến 1.000.000.000 VNĐ!');
            e.preventDefault();
            return false;
        }
        
        // Validate số lượng
        const quantity = parseInt(quantityInput.value) || 0;
        if (quantity <= 0) {
            alert('Số lượng nhập phải lớn hơn 0!');
            e.preventDefault();
            return false;
        }
        
        // Validate sản phẩm
        if (!selectedProduct || !selectedProduct.id) {
            alert('Vui lòng chọn sản phẩm!');
            e.preventDefault();
            return false;
        }
        
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
