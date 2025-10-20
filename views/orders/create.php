<?php include '../../views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Thêm đơn hàng mới</h2>
    
    <form action="/quanlysanpham/orders/store" method="POST" id="orderForm">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Thông tin khách hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Khách hàng</label>
                            <select class="form-select" id="customer_id" name="customer_id" required>
                                <option value="">Chọn khách hàng</option>
                                <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['customer_id']; ?>">
                                    <?php echo htmlspecialchars($customer['customer_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="phone" name="phone" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ giao hàng</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Chi tiết đơn hàng</h5>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="fas fa-plus"></i> Thêm sản phẩm
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Đơn giá</th>
                                        <th>Số lượng</th>
                                        <th>Thành tiền</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="orderItems">
                                    <!-- Order items will be added here dynamically -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                        <td colspan="2" class="text-danger fw-bold" id="orderTotal">0 đ</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/quanlysanpham/orders" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu đơn hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchProduct" placeholder="Tìm kiếm sản phẩm...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên sản phẩm</th>
                                <th>Mã sản phẩm</th>
                                <th>Giá bán</th>
                                <th>Tồn kho</th>
                                <th>Số lượng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="productList">
                            <?php foreach ($products as $product): ?>
                            <tr data-product-id="<?php echo $product['product_id']; ?>">
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['product_code']); ?></td>
                                <td class="price"><?php echo number_format($product['price']); ?> đ</td>
                                <td class="stock"><?php echo $product['quantity_in_stock']; ?></td>
                                <td>
                                    <input type="number" class="form-control form-control-sm quantity-input" 
                                           min="1" max="<?php echo $product['quantity_in_stock']; ?>" 
                                           value="1">
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-success add-to-order">
                                        <i class="fas fa-plus"></i> Thêm
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden template for order items -->
<template id="orderItemTemplate">
    <tr data-product-id="">
        <td>
            <input type="hidden" name="product_id[]" value="">
            <input type="hidden" name="product_name[]" value="">
            <span class="product-name"></span>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm price-input" name="price[]" value="" min="0" step="1000" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm quantity-input" name="quantity[]" value="1" min="1" required>
        </td>
        <td class="item-total">0 đ</td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-item">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load customer info when selected
    const customerSelect = document.getElementById('customer_id');
    if (customerSelect) {
        customerSelect.addEventListener('change', function() {
            const customerId = this.value;
            if (customerId) {
                // In a real application, you would fetch customer details from the server
                // This is just a placeholder
                fetch(`/quanlysanpham/api/customers/${customerId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('phone').value = data.phone || '';
                        document.getElementById('email').value = data.email || '';
                        document.getElementById('address').value = data.address || '';
                    });
            } else {
                document.getElementById('phone').value = '';
                document.getElementById('email').value = '';
                document.getElementById('address').value = '';
            }
        });
    }

    // Add product to order
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-order')) {
            const row = e.target.closest('tr');
            const productId = row.dataset.productId;
            const productName = row.cells[0].textContent.trim();
            const price = parseFloat(row.cells[2].textContent.replace(/[^\d]/g, ''));
            const quantityInput = row.querySelector('.quantity-input');
            const quantity = parseInt(quantityInput.value) || 1;
            const maxQuantity = parseInt(row.querySelector('.stock').textContent) || 1;
            
            // Validate quantity
            if (quantity < 1) {
                alert('Số lượng phải lớn hơn 0');
                quantityInput.value = 1;
                return;
            }
            
            if (quantity > maxQuantity) {
                alert(`Số lượng vượt quá tồn kho (${maxQuantity})`);
                quantityInput.value = maxQuantity;
                return;
            }
            
            addProductToOrder({
                product_id: productId,
                product_name: productName,
                price: price,
                quantity: quantity,
                max_quantity: maxQuantity
            });
            
            // Reset quantity input
            quantityInput.value = 1;
        }
    });
    
    // Remove item from order
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
            const button = e.target.classList.contains('remove-item') ? e.target : e.target.closest('.remove-item');
            const row = button.closest('tr');
            row.remove();
            updateOrderTotal();
        }
    });
    
    // Update item total when quantity or price changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input')) {
            updateOrderTotal();
        }
    });
    
    // Search products
    const searchInput = document.getElementById('searchProduct');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#productList tr');
            
            rows.forEach(row => {
                const productName = row.cells[0].textContent.toLowerCase();
                const productCode = row.cells[1].textContent.toLowerCase();
                
                if (productName.includes(searchTerm) || productCode.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Form submission
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            const items = document.querySelectorAll('#orderItems tr');
            if (items.length === 0) {
                e.preventDefault();
                alert('Vui lòng thêm ít nhất một sản phẩm vào đơn hàng');
                return false;
            }
            
            // You can add additional validation here before form submission
            return true;
        });
    }
});

// Function to add product to order
function addProductToOrder(product) {
    const orderItems = document.getElementById('orderItems');
    const template = document.getElementById('orderItemTemplate');
    const clone = template.content.cloneNode(true);
    
    const row = clone.querySelector('tr');
    row.dataset.productId = product.product_id;
    
    const productIdInput = clone.querySelector('input[name="product_id[]"]');
    productIdInput.value = product.product_id;
    
    const productNameInput = clone.querySelector('input[name="product_name[]"]');
    productNameInput.value = product.product_name;
    
    const productNameSpan = clone.querySelector('.product-name');
    productNameSpan.textContent = product.product_name;
    
    const priceInput = clone.querySelector('.price-input');
    priceInput.value = product.price;
    priceInput.max = product.price * 2; // Allow some markup
    
    const quantityInput = clone.querySelector('.quantity-input');
    quantityInput.value = product.quantity;
    quantityInput.max = product.max_quantity;
    
    // Calculate initial total
    updateRowTotal(row);
    
    // Check if product already exists in order
    const existingRow = orderItems.querySelector(`tr[data-product-id="${product.product_id}"]`);
    if (existingRow) {
        const existingQuantityInput = existingRow.querySelector('.quantity-input');
        const newQuantity = parseInt(existingQuantityInput.value) + product.quantity;
        const maxQuantity = parseInt(existingRow.querySelector('.quantity-input').max) || 999;
        
        if (newQuantity <= maxQuantity) {
            existingQuantityInput.value = newQuantity;
            updateRowTotal(existingRow);
            return;
        } else {
            alert(`Số lượng vượt quá tồn kho (${maxQuantity})`);
            existingQuantityInput.value = maxQuantity;
            updateRowTotal(existingRow);
            return;
        }
    }
    
    orderItems.appendChild(clone);
    updateOrderTotal();
}

// Function to update row total
function updateRowTotal(row) {
    const priceInput = row.querySelector('.price-input');
    const quantityInput = row.querySelector('.quantity-input');
    const totalCell = row.querySelector('.item-total');
    
    const price = parseFloat(priceInput.value) || 0;
    const quantity = parseInt(quantityInput.value) || 0;
    const total = price * quantity;
    
    totalCell.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
    return total;
}

// Function to update order total
function updateOrderTotal() {
    const rows = document.querySelectorAll('#orderItems tr');
    let total = 0;
    
    rows.forEach(row => {
        total += updateRowTotal(row);
    });
    
    const totalCell = document.getElementById('orderTotal');
    if (totalCell) {
        totalCell.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
    }
}
</script>

<?php include '../../views/layouts/footer.php'; ?>
