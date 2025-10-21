<?php 
$page_title = 'Tạo đơn hàng mới';
$active_page = 'orders';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>Tạo đơn hàng mới</h1>
    </div>
    <div>
        <a href="/quanlysanpham/orders" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">
            <i class="fas fa-shopping-cart me-2 text-primary"></i> Thông tin đơn hàng
        </h5>
    </div>
    <div class="card-body">
        <form id="orderForm" method="post" action="/quanlysanpham/orders/store">
            <div class="row g-4">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Customer Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0"><i class="fas fa-user me-2 text-primary"></i> Thông tin khách hàng</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="customer_id" class="form-label small fw-bold">Khách hàng <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" id="customer_id" name="customer_id" required>
                                        <option value="">-- Chọn khách hàng --</option>
                                        <?php foreach ($customers as $customer): ?>
                                            <option value="<?= $customer['customer_id'] ?>">
                                                <?= htmlspecialchars($customer['fullname']) ?> 
                                                (<?= !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : 'Chưa có SĐT' ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="shipping_address" class="form-label small fw-bold">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="shipping_address" name="shipping_address" required>
                                </div>
                                <div class="col-12">
                                    <label for="shipping_note" class="form-label small fw-bold">Ghi chú giao hàng</label>
                                    <textarea class="form-control form-control-sm" id="shipping_note" name="shipping_note" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Section -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-box me-2 text-primary"></i> Sản phẩm</h6>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <i class="fas fa-plus me-1"></i> Thêm sản phẩm
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40" class="ps-3">#</th>
                                            <th>Sản phẩm</th>
                                            <th class="text-end pe-3" width="120">Đơn giá</th>
                                            <th class="text-center" width="140">Số lượng</th>
                                            <th class="text-end pe-3" width="140">Thành tiền</th>
                                            <th width="50" class="pe-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderItems">
                                        <tr id="emptyCart">
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="fas fa-shopping-cart fa-2x d-block mb-2"></i>
                                                Chưa có sản phẩm nào
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        <span id="totalItems">0</span> sản phẩm
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                        <i class="fas fa-plus me-1"></i> Thêm sản phẩm
                                    </button>
                                </div>
                            </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i> Ghi chú đơn hàng</h6>
                        </div>
                        <div class="card-body py-3">
                            <textarea class="form-control form-control-sm" id="note" name="note" rows="2" 
                                placeholder="Nhập ghi chú cho đơn hàng (nếu có)"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">

                    <!-- Payment Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0"><i class="fas fa-credit-card me-2 text-primary"></i> Thanh toán</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2">Phương thức thanh toán <span class="text-danger">*</span></label>
                                <div class="d-grid gap-2">
                                    <div class="form-check p-2 border rounded mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                        <label class="form-check-label d-flex align-items-center" for="cod">
                                            <i class="fas fa-money-bill-wave me-2 text-success"></i>
                                            <div>
                                                <div class="fw-medium">Thanh toán khi nhận hàng</div>
                                                <small class="text-muted">(COD - Tiền mặt khi nhận hàng)</small>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="form-check p-2 border rounded mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                        <label class="form-check-label d-flex align-items-center" for="bank_transfer">
                                            <i class="fas fa-university me-2 text-primary"></i>
                                            <div>
                                                <div class="fw-medium">Chuyển khoản ngân hàng</div>
                                                <small class="text-muted">(Chuyển khoản qua ngân hàng)</small>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="form-check p-2 border rounded">
                                        <input class="form-check-input" type="radio" name="payment_method" id="momo" value="momo">
                                        <label class="form-check-label d-flex align-items-center" for="momo">
                                            <i class="fas fa-mobile-alt me-2" style="color: #a50064;"></i>
                                            <div>
                                                <div class="fw-medium">Ví điện tử MoMo</div>
                                                <small class="text-muted">(Thanh toán qua ứng dụng MoMo)</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check form-switch mb-3 border-top pt-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_paid" name="is_paid">
                                <label class="form-check-label fw-medium" for="is_paid">Đã thanh toán</label>
                            </div>
                            
                            <div class="mb-3" id="paymentDateContainer" style="display: none;">
                                <label for="payment_date" class="form-label small fw-bold mb-1">Ngày thanh toán</label>
                                <input type="datetime-local" class="form-control form-control-sm" id="payment_date" name="payment_date">
                            </div>
                            <div class="mb-3" id="paymentNoteContainer" style="display: none;">
                                <label for="payment_note" class="form-label small fw-bold mb-1">Ghi chú thanh toán</label>
                                <textarea class="form-control form-control-sm" id="payment_note" name="payment_note" rows="2" placeholder="Nhập ghi chú thanh toán (nếu có)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0"><i class="fas fa-receipt me-2 text-primary"></i> Tổng đơn hàng</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-3 border-bottom">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tạm tính:</span>
                                    <span id="subtotal" class="fw-medium">0 ₫</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Giảm giá:</span>
                                    <span id="discount" class="text-success">0 ₫</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Phí vận chuyển:</span>
                                    <span id="shipping_fee">0 ₫</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2 mt-2">
                                    <span>Thành tiền:</span>
                                    <span id="total" class="text-primary">0 ₫</span>
                                </div>
                            </div>
                            <div class="p-3">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-medium">
                                    <i class="fas fa-save me-2"></i> Tạo đơn hàng
                                </button>
                                <div class="text-muted small mt-2 text-center">
                                    Nhấn "Tạo đơn hàng" đồng nghĩa với việc bạn đồng ý với các điều khoản và điều kiện của cửa hàng.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Hidden fields for form submission -->
            <input type="hidden" name="order_items" id="orderItemsInput">
            <input type="hidden" name="total_amount" id="totalAmountInput">
        </form>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chọn sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchProduct" placeholder="Tìm kiếm sản phẩm...">
                    </div>
                </div>
                
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead class="sticky-top bg-white" style="top: 0;">
                            <tr>
                                <th width="50">#</th>
                                <th>Sản phẩm</th>
                                <th class="text-end">Tồn kho</th>
                                <th class="text-end">Giá bán</th>
                                <th class="text-center" width="150">Số lượng</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="productList">
                            <?php foreach ($products as $index => $product): ?>
                                <tr data-product-id="<?= $product['product_id'] ?>">
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($product['image_url'])): ?>
                                                <img src="/quanlysanpham/uploads/products/<?= htmlspecialchars($product['image_url']) ?>" 
                                                     alt="<?= htmlspecialchars($product['product_name']) ?>" 
                                                     class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-box text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($product['product_name']) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($product['product_code']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-<?= $product['stock_quantity'] > 0 ? 'success' : 'danger' ?>">
                                            <?= $product['stock_quantity'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold">
                                        <?= number_format($product['price']) ?> ₫
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <button type="button" class="btn btn-outline-secondary btn-sm quantity-decrease" 
                                                    data-product-id="<?= $product['product_id'] ?>">-</button>
                                            <input type="number" class="form-control text-center quantity-input" 
                                                   data-product-id="<?= $product['product_id'] ?>" 
                                                   value="1" min="1" max="<?= $product['stock_quantity'] ?>" 
                                                   style="width: 50px;">
                                            <button type="button" class="btn btn-outline-secondary btn-sm quantity-increase" 
                                                    data-product-id="<?= $product['product_id'] ?>">+</button>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-primary add-to-cart" 
                                                data-product-id="<?= $product['product_id'] ?>"
                                                data-product-name="<?= htmlspecialchars($product['product_name']) ?>"
                                                data-product-code="<?= htmlspecialchars($product['product_code']) ?>"
                                                data-product-price="<?= $product['price'] ?>"
                                                data-stock-quantity="<?= $product['stock_quantity'] ?>"
                                                <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                                            <i class="fas fa-plus"></i>
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
                <button type="button" class="btn btn-primary" id="addSelectedProducts" data-bs-dismiss="modal">
                    Thêm vào đơn hàng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Template for cart items -->
<template id="cartItemTemplate">
    <tr data-product-id="">
        <td class="align-middle"></td>
        <td>
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-2">
                    <img src="" alt="" class="product-image" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='/quanlysanpham/assets/img/no-image.png'">
                </div>
                <div>
                    <div class="fw-bold product-name"></div>
                    <div class="text-muted small product-code"></div>
                    <div class="text-muted small">Tồn kho: <span class="stock-quantity"></span></div>
                </div>
            </div>
        </td>
        <td class="align-middle text-end">
            <span class="product-price"></span> ₫
        </td>
        <td class="align-middle">
            <div class="input-group input-group-sm">
                <button type="button" class="btn btn-outline-secondary btn-sm cart-quantity-decrease">-</button>
                <input type="number" class="form-control text-center cart-quantity" value="1" min="1">
                <button type="button" class="btn btn-outline-secondary btn-sm cart-quantity-increase">+</button>
            </div>
        </td>
        <td class="align-middle text-end fw-bold item-total"></td>
        <td class="align-middle text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Global variables
    const orderItemsContainer = document.getElementById('orderItems');
    const emptyCartRow = document.getElementById('emptyCart');
    const cartItemTemplate = document.getElementById('cartItemTemplate');
    const orderForm = document.getElementById('orderForm');
    const orderItemsInput = document.getElementById('orderItemsInput');
    const totalAmountInput = document.getElementById('totalAmountInput');
    const submitBtn = orderForm.querySelector('button[type="submit"]');
    const isPaidCheckbox = document.getElementById('is_paid');
    const paymentDateContainer = document.getElementById('paymentDateContainer');
    const paymentNoteContainer = document.getElementById('paymentNoteContainer');
    
    // Cart items array
    let cartItems = [];
    
    // Toggle payment date and note fields
    isPaidCheckbox.addEventListener('change', function() {
        if (this.checked) {
            paymentDateContainer.style.display = 'block';
            paymentNoteContainer.style.display = 'block';
            // Set current datetime as default
            const now = new Date();
            const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            document.getElementById('payment_date').value = localDateTime;
        } else {
            paymentDateContainer.style.display = 'none';
            paymentNoteContainer.style.display = 'none';
        }
    });
    
    // Search functionality
    const searchProduct = document.getElementById('searchProduct');
    searchProduct.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#productList tr');
        
        rows.forEach(row => {
            const productName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const productCode = row.querySelector('td:nth-child(2) .small').textContent.toLowerCase();
            
            if (productName.includes(searchTerm) || productCode.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Handle quantity changes in the modal
    document.addEventListener('click', function(e) {
        // Increase quantity
        if (e.target.classList.contains('quantity-increase')) {
            const input = e.target.previousElementSibling;
            const max = parseInt(input.getAttribute('max')) || 9999;
            if (parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
            }
        } 
        // Decrease quantity
        else if (e.target.classList.contains('quantity-decrease')) {
            const input = e.target.nextElementSibling;
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }
        // Add to cart
        else if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
            const button = e.target.classList.contains('add-to-cart') ? e.target : e.target.closest('.add-to-cart');
            const productId = parseInt(button.getAttribute('data-product-id'));
            const productName = button.getAttribute('data-product-name');
            const productCode = button.getAttribute('data-product-code');
            const productPrice = parseFloat(button.getAttribute('data-product-price'));
            const stockQuantity = parseInt(button.getAttribute('data-stock-quantity'));
            const quantityInput = button.closest('tr').querySelector('.quantity-input');
            const quantity = parseInt(quantityInput ? quantityInput.value : 1);
            
            // Check if product already exists in cart
            const existingItemIndex = cartItems.findIndex(item => item.id === productId);
            
            if (existingItemIndex >= 0) {
                // Update quantity if product exists
                const newQuantity = cartItems[existingItemIndex].quantity + quantity;
                
                if (newQuantity > stockQuantity) {
                        return;
                }
                
                cartItems[existingItemIndex].quantity = newQuantity;
                updateCartItemInDOM(productId, newQuantity);
            } else {
                // Add new product to cart
                const productImage = button.closest('tr').querySelector('img');
                const imageUrl = productImage ? productImage.src : '';
                
                cartItems.push({
                    id: productId,
                    name: productName,
                    code: productCode,
                    price: productPrice,
                    quantity: quantity,
                    stock: stockQuantity,
                    image: imageUrl
                });
                
                addCartItemToDOM({
                    id: productId,
                    name: productName,
                    code: productCode,
                    price: productPrice,
                    quantity: quantity,
                    stock: stockQuantity,
                    image: imageUrl
                });
            }
            
            // Update UI
            updateCartUI();
            updateOrderSummary();
            
        }
        // Remove item from cart
        else if (e.target.closest('.remove-item')) {
            const row = e.target.closest('tr');
            const productId = parseInt(row.getAttribute('data-product-id'));
            removeFromCart(productId);
        }
        // Increase quantity in cart
        else if (e.target.classList.contains('cart-quantity-increase')) {
            const input = e.target.previousElementSibling;
            const row = e.target.closest('tr');
            const productId = parseInt(row.getAttribute('data-product-id'));
            const maxStock = parseInt(row.querySelector('.stock-quantity').textContent);
            const currentQuantity = parseInt(input.value);
            
            if (currentQuantity < maxStock) {
                input.value = currentQuantity + 1;
                updateCartItemQuantity(productId, currentQuantity + 1);
                updateOrderSummary();
            } else {
            }
        }
        // Decrease quantity in cart
        else if (e.target.classList.contains('cart-quantity-decrease')) {
            const input = e.target.nextElementSibling;
            const currentQuantity = parseInt(input.value);
            
            if (currentQuantity > 1) {
                input.value = currentQuantity - 1;
                const productId = parseInt(e.target.closest('tr').getAttribute('data-product-id'));
                updateCartItemQuantity(productId, currentQuantity - 1);
                updateOrderSummary();
            }
        }
    });
    
    // Handle direct quantity input changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('cart-quantity')) {
            const productId = parseInt(e.target.closest('tr').getAttribute('data-product-id'));
            const quantity = parseInt(e.target.value) || 1;
            const maxStock = parseInt(e.target.closest('tr').querySelector('.stock-quantity').textContent);
            
            if (quantity < 1) {
                e.target.value = 1;
                updateCartItemQuantity(productId, 1);
            } else if (quantity > maxStock) {
                e.target.value = maxStock;
                updateCartItemQuantity(productId, maxStock);
            } else {
                updateCartItemQuantity(productId, quantity);
            }
            
            updateOrderSummary();
        }
    });
    
    // Add cart item to DOM
    function addCartItemToDOM(product) {
        if (emptyCartRow) {
            emptyCartRow.style.display = 'none';
        }
        
        const row = cartItemTemplate.content.cloneNode(true);
        const tr = row.querySelector('tr');
        tr.setAttribute('data-product-id', product.id);
        
        // Fill in the template
        tr.querySelector('.product-name').textContent = product.name;
        tr.querySelector('.product-code').textContent = product.code;
        tr.querySelector('.stock-quantity').textContent = product.stock;
        tr.querySelector('.product-price').textContent = product.price.toLocaleString();
        tr.querySelector('.cart-quantity').value = product.quantity;
        tr.querySelector('.cart-quantity').setAttribute('max', product.stock);
        tr.querySelector('.item-total').textContent = (product.price * product.quantity).toLocaleString() + ' ₫';
        
        if (product.image) {
            const img = tr.querySelector('.product-image');
            img.src = product.image;
            img.alt = product.name;
        }
        
        orderItemsContainer.appendChild(row);
    }
    
    // Update cart item quantity
    function updateCartItemQuantity(productId, quantity) {
        const itemIndex = cartItems.findIndex(item => item.id === productId);
        
        if (itemIndex >= 0) {
            cartItems[itemIndex].quantity = quantity;
            updateCartItemInDOM(productId, quantity);
        }
    }
    
    // Update cart item in DOM
    function updateCartItemInDOM(productId, quantity) {
        const row = document.querySelector(`#orderItems tr[data-product-id="${productId}"]`);
        
        if (row) {
            const price = parseFloat(row.querySelector('.product-price').textContent.replace(/,/g, ''));
            const total = price * quantity;
            
            row.querySelector('.cart-quantity').value = quantity;
            row.querySelector('.item-total').textContent = total.toLocaleString() + ' ₫';
            
            // Update the cart items array
            const itemIndex = cartItems.findIndex(item => item.id === productId);
            if (itemIndex >= 0) {
                cartItems[itemIndex].quantity = quantity;
            }
        }
    }
    
    // Remove item from cart
    function removeFromCart(productId) {
        const itemIndex = cartItems.findIndex(item => item.id === productId);
        
        if (itemIndex >= 0) {
            const productName = cartItems[itemIndex].name;
            cartItems.splice(itemIndex, 1);
            
            const row = document.querySelector(`#orderItems tr[data-product-id="${productId}"]`);
            if (row) {
                row.remove();
            }
            
            // Update UI
            updateCartUI();
            updateOrderSummary();
            
        }
    }
    
    // Update cart UI
    function updateCartUI() {
        // Show empty cart message if no items
        if (cartItems.length === 0) {
            if (emptyCartRow) {
                emptyCartRow.style.display = '';
            }
            if (submitBtn) {
                submitBtn.disabled = true;
            }
        } else {
            if (emptyCartRow) {
                emptyCartRow.style.display = 'none';
            }
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }
        
        // Update row numbers
        const rows = orderItemsContainer.querySelectorAll('tr:not(#emptyCart)');
        rows.forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }
    
    // Update order summary
    function updateOrderSummary() {
        let subtotal = 0;
        let totalItems = 0;
        
        // Calculate subtotal and total items
        cartItems.forEach(item => {
            subtotal += item.price * item.quantity;
            totalItems += item.quantity;
        });
        
        // Get discount and shipping fee
        const discount = 0; // Add discount logic if needed
        const shippingFee = 0; // Add shipping fee logic if needed
        const total = subtotal - discount + shippingFee;
        
        // Update UI
        document.getElementById('subtotal').textContent = subtotal.toLocaleString() + ' ₫';
        document.getElementById('discount').textContent = discount.toLocaleString() + ' ₫';
        document.getElementById('shipping_fee').textContent = shippingFee.toLocaleString() + ' ₫';
        document.getElementById('total').textContent = total.toLocaleString() + ' ₫';
        
        // Update hidden fields for form submission
        updateFormData();
    }
    
    // Update form data before submission
    function updateFormData() {
        // Prepare order items data
        const orderItemsData = cartItems.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            unit_price: item.price,
            total_price: item.price * item.quantity
        }));
        
        // Update hidden fields
        orderItemsInput.value = JSON.stringify(orderItemsData);
        totalAmountInput.value = document.getElementById('total').textContent.replace(/[^0-9]/g, '');
    }
    
    // Form submission
    orderForm.addEventListener('submit', function(e) {
        // Prevent default form submission for demo
        e.preventDefault();
        
        // Validate form
        if (cartItems.length === 0) {
            alert('Vui lòng thêm ít nhất một sản phẩm vào đơn hàng');
            return false;
        }
        
        const customerId = document.getElementById('customer_id').value;
        const shippingAddress = document.getElementById('shipping_address').value;
        
        if (!customerId) {
            alert('Vui lòng chọn khách hàng');
            return false;
        }
        
        if (!shippingAddress.trim()) {
            alert('Vui lòng nhập địa chỉ giao hàng');
            return false;
        }
        
        // Update form data before submission
        updateFormData();
        
        // Submit the form
        orderForm.submit();
        
        return false;
    });
    
    // Show alert message (removed UI alerts)
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>