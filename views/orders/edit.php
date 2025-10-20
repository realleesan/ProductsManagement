<?php include '../../views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Chỉnh sửa đơn hàng #<?php echo $order['order_code']; ?></h2>
    
    <form action="/quanlysanpham/orders/update/<?php echo $order['order_id']; ?>" method="POST" id="orderForm">
        <input type="hidden" name="_method" value="PUT">
        
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
                                <option value="<?php echo $customer['customer_id']; ?>" 
                                    <?php echo ($customer['customer_id'] == $order['customer_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($customer['customer_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($order['phone'] ?? ''); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($order['email'] ?? ''); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ giao hàng</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required><?php 
                                echo htmlspecialchars($order['shipping_address'] ?? ''); 
                            ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái đơn hàng</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Chờ xử lý</option>
                                <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Đang xử lý</option>
                                <option value="shipped" <?php echo ($order['status'] == 'shipped') ? 'selected' : ''; ?>>Đang giao hàng</option>
                                <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Đã giao hàng</option>
                                <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                                <option value="refunded" <?php echo ($order['status'] == 'refunded') ? 'selected' : ''; ?>>Đã hoàn tiền</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Thông tin thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Phương thức thanh toán</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="cod" <?php echo ($order['payment_method'] == 'cod') ? 'selected' : ''; ?>>Thanh toán khi nhận hàng (COD)</option>
                                <option value="bank_transfer" <?php echo ($order['payment_method'] == 'bank_transfer') ? 'selected' : ''; ?>>Chuyển khoản ngân hàng</option>
                                <option value="momo" <?php echo ($order['payment_method'] == 'momo') ? 'selected' : ''; ?>>Ví điện tử MoMo</option>
                                <option value="vnpay" <?php echo ($order['payment_method'] == 'vnpay') ? 'selected' : ''; ?>>VNPay</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Trạng thái thanh toán</label>
                            <select class="form-select" id="payment_status" name="payment_status" required>
                                <option value="pending" <?php echo ($order['payment_status'] == 'pending') ? 'selected' : ''; ?>>Chưa thanh toán</option>
                                <option value="partial" <?php echo ($order['payment_status'] == 'partial') ? 'selected' : ''; ?>>Thanh toán một phần</option>
                                <option value="paid" <?php echo ($order['payment_status'] == 'paid') ? 'selected' : ''; ?>>Đã thanh toán</option>
                                <option value="refunded" <?php echo ($order['payment_status'] == 'refunded') ? 'selected' : ''; ?>>Đã hoàn tiền</option>
                                <option value="cancelled" <?php echo ($order['payment_status'] == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="paid_amount" class="form-label">Số tiền đã thanh toán</label>
                            <input type="number" class="form-control" id="paid_amount" name="paid_amount" 
                                   value="<?php echo $order['paid_amount'] ?? 0; ?>" min="0">
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
                                    <?php foreach ($order['items'] as $item): ?>
                                    <tr data-product-id="<?php echo $item['product_id']; ?>">
                                        <td>
                                            <input type="hidden" name="order_item_id[]" value="<?php echo $item['order_item_id']; ?>">
                                            <input type="hidden" name="product_id[]" value="<?php echo $item['product_id']; ?>">
                                            <input type="hidden" name="product_name[]" value="<?php echo htmlspecialchars($item['product_name']); ?>">
                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                            <?php if (!empty($item['product_code'])): ?>
                                                <br><small class="text-muted">Mã: <?php echo htmlspecialchars($item['product_code']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm price-input" 
                                                   name="price[]" value="<?php echo $item['price']; ?>" min="0" step="1000" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm quantity-input" 
                                                   name="quantity[]" value="<?php echo $item['quantity']; ?>" min="1" required>
                                            <input type="hidden" class="original-quantity" 
                                                   value="<?php echo $item['quantity']; ?>">
                                        </td>
                                        <td class="item-total">
                                            <?php echo number_format($item['price'] * $item['quantity']); ?> đ
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">Tạm tính:</td>
                                        <td colspan="3" class="text-end" id="subtotal">
                                            <?php echo number_format($order['subtotal'] ?? 0); ?> đ
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">Phí vận chuyển:</td>
                                        <td colspan="3" class="text-end">
                                            <input type="number" class="form-control form-control-sm d-inline-block w-50 text-end" 
                                                   id="shipping_fee" name="shipping_fee" 
                                                   value="<?php echo $order['shipping_fee'] ?? 0; ?>" min="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">Giảm giá:</td>
                                        <td colspan="3" class="text-end">
                                            <input type="number" class="form-control form-control-sm d-inline-block w-50 text-end" 
                                                   id="discount" name="discount" 
                                                   value="<?php echo $order['discount'] ?? 0; ?>" min="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">Tổng cộng:</td>
                                        <td colspan="3" class="text-end text-danger fw-bold" id="orderTotal">
                                            <?php echo number_format($order['total_amount'] ?? 0); ?> đ
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">Đã thanh toán:</td>
                                        <td colspan="3" class="text-end text-success fw-bold" id="paidAmount">
                                            <?php echo number_format($order['paid_amount'] ?? 0); ?> đ
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">Còn lại:</td>
                                        <td colspan="3" class="text-end text-primary fw-bold" id="remainingAmount">
                                            <?php 
                                                $remaining = ($order['total_amount'] ?? 0) - ($order['paid_amount'] ?? 0);
                                                echo number_format($remaining > 0 ? $remaining : 0); 
                                            ?> đ
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú đơn hàng</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?php 
                                echo htmlspecialchars($order['notes'] ?? ''); 
                            ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/quanlysanpham/orders" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                            <div>
                                <button type="button" class="btn btn-warning me-2" id="saveDraft">
                                    <i class="fas fa-save"></i> Lưu nháp
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Cập nhật đơn hàng
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($orderHistory)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Lịch sử cập nhật đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach ($orderHistory as $history): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($history['status_label'] ?? ''); ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('H:i d/m/Y', strtotime($history['created_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1">
                                        <?php if (!empty($history['notes'])): ?>
                                            <small><?php echo nl2br(htmlspecialchars($history['notes'])); ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">Không có ghi chú</small>
                                        <?php endif; ?>
                                    </p>
                                    <div class="text-muted small">
                                        <i class="fas fa-user"></i> 
                                        <?php echo !empty($history['updated_by_name']) ? 
                                            htmlspecialchars($history['updated_by_name']) : 'Hệ thống'; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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
                            <?php foreach ($products as $product): 
                                // Skip products that are already in the order
                                $inOrder = false;
                                foreach ($order['items'] as $item) {
                                    if ($item['product_id'] == $product['product_id']) {
                                        $inOrder = true;
                                        break;
                                    }
                                }
                                if ($inOrder) continue;
                            ?>
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

<!-- Hidden template for new order items -->
<template id="orderItemTemplate">
    <tr data-product-id="">
        <td>
            <input type="hidden" name="order_item_id[]" value="new">
            <input type="hidden" name="product_id[]" value="">
            <input type="hidden" name="product_name[]" value="">
            <span class="product-name"></span>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm price-input" name="price[]" value="" min="0" step="1000" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm quantity-input" name="quantity[]" value="1" min="1" required>
            <input type="hidden" class="original-quantity" value="0">
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
                        if (!document.getElementById('address').value) {
                            document.getElementById('address').value = data.address || '';
                        }
                    });
            } else {
                document.getElementById('phone').value = '';
                document.getElementById('email').value = '';
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
            
            // If this is an existing item, add a hidden input to mark it for deletion
            const orderItemId = row.querySelector('input[name^="order_item_id"]')?.value;
            if (orderItemId && orderItemId !== 'new') {
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'deleted_items[]';
                deleteInput.value = orderItemId;
                document.getElementById('orderForm').appendChild(deleteInput);
            }
            
            row.remove();
            updateOrderTotal();
        }
    });
    
    // Update item total when quantity or price changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input') || 
            e.target.id === 'shipping_fee' || e.target.id === 'discount' || e.target.id === 'paid_amount') {
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
    
    // Save as draft
    const saveDraftBtn = document.getElementById('saveDraft');
    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', function() {
            const form = document.getElementById('orderForm');
            const draftInput = document.createElement('input');
            draftInput.type = 'hidden';
            draftInput.name = 'save_as_draft';
            draftInput.value = '1';
            form.appendChild(draftInput);
            form.submit();
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
            
            // Validate payment amount
            const totalAmount = parseFloat(document.getElementById('orderTotal').textContent.replace(/[^\d]/g, '')) || 0;
            const paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;
            
            if (paidAmount > totalAmount) {
                e.preventDefault();
                alert('Số tiền đã thanh toán không được lớn hơn tổng tiền đơn hàng');
                return false;
            }
            
            return true;
        });
    }
    
    // Initialize order total calculation
    updateOrderTotal();
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
            updateOrderTotal();
            return;
        } else {
            alert(`Số lượng vượt quá tồn kho (${maxQuantity})`);
            existingQuantityInput.value = maxQuantity;
            updateRowTotal(existingRow);
            updateOrderTotal();
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
    let subtotal = 0;
    
    // Calculate subtotal from all items
    rows.forEach(row => {
        subtotal += updateRowTotal(row);
    });
    
    // Get other values
    const shippingFee = parseFloat(document.getElementById('shipping_fee').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;
    
    // Calculate total
    const total = subtotal + shippingFee - discount;
    const remaining = Math.max(0, total - paidAmount);
    
    // Update display
    document.getElementById('subtotal').textContent = new Intl.NumberFormat('vi-VN').format(subtotal) + ' đ';
    document.getElementById('orderTotal').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
    document.getElementById('paidAmount').textContent = new Intl.NumberFormat('vi-VN').format(paidAmount) + ' đ';
    document.getElementById('remainingAmount').textContent = new Intl.NumberFormat('vi-VN').format(remaining) + ' đ';
    
    return total;
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
    margin: 0 0 0 1rem;
    border-left: 2px solid #dee2e6;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -1.7rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background-color: #0d6efd;
    border: 2px solid #fff;
}

.timeline-content {
    padding: 0.5rem 1rem;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
}

.timeline-item:last-child .timeline-content {
    margin-bottom: 0;
}
</style>

<?php include '../../views/layouts/footer.php'; ?>
