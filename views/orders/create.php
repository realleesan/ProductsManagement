<?php 
$page_title = 'Tạo đơn hàng mới';
$active_page = 'orders';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>Tạo đơn hàng mới</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?controller=ProductController&action=dashboard">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?controller=OrderController&action=index">Đơn hàng</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tạo mới</li>
                        </ol>
                    </nav>
    </div>
    <div>
                    <a href="<?= BASE_URL ?>?controller=OrderController&action=index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

            <?php 
            $flashMessage = getFlashMessage();
            if ($flashMessage): ?>
                <div class="alert alert-<?= $flashMessage['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                    <?= $flashMessage['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form id="orderForm" method="post" action="?controller=OrderController&action=store">
                <div class="row">
                    <div class="col-lg-8">

                        <!-- Thông tin sản phẩm -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0"><i class="fas fa-box me-2 text-primary"></i> Sản phẩm</h5>
                            </div>
                            <div class="card-body">
                                <!-- Tìm kiếm và thêm sản phẩm -->
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" class="form-control" id="searchProduct" placeholder="Tìm kiếm sản phẩm...">
                        </div>
                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-primary w-100" id="toggleProductList">
                                    <i class="fas fa-plus me-1"></i> Thêm sản phẩm
                                </button>
    </div>
</div>

                                <!-- Danh sách sản phẩm để chọn -->
                                <div id="productSelectionArea" class="mb-3" style="display: none;">
                                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem;">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light sticky-top" style="top: 0;">
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
                                                                         class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 0.25rem;">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center me-2" 
                                                                         style="width: 40px; height: 40px; border-radius: 0.25rem;">
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
                                
                                <!-- Bảng sản phẩm đã chọn -->
                            <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                                <th width="50">STT</th>
                                            <th>Sản phẩm</th>
                                                <th class="text-end">Đơn giá</th>
                                                <th class="text-center" width="150">Số lượng</th>
                                                <th class="text-end">Thành tiền</th>
                                                <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderItems">
                                        <tr id="emptyCart">
                                                <td colspan="6" class="text-center py-4">
                                                    <div class="text-muted">
                                                <i class="fas fa-shopping-cart fa-2x d-block mb-2"></i>
                                                Chưa có sản phẩm nào
                                                    </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                        <tfoot id="orderSummary" class="d-none">
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                                <td class="text-center" id="subtotalItems">0 sản phẩm</td>
                                                <td class="text-end fw-bold" id="totalAmount">0 ₫</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
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
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Thông tin khách hàng -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0"><i class="fas fa-user me-2 text-primary"></i> Thông tin khách hàng</h5>
                            </div>
                            <div class="card-body">
                                <!-- Mã đơn hàng -->
                                <div class="mb-3">
                                    <label for="order_code" class="form-label">Mã đơn hàng <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="order_code" name="order_code" 
                                           pattern="^DH[0-9]{7}$" 
                                           maxlength="9" 
                                           placeholder="DH1234567" 
                                           required>
                                    <div class="form-text">Nhập mã đơn hàng theo định dạng: DH + 7 chữ số (ví dụ: DH1234567)</div>
                                    <div class="invalid-feedback" id="order_code_error"></div>
                                </div>
                                
                                <!-- Thông tin khách hàng mới -->
                                <div class="mb-3">
                                    <label for="customer_fullname" class="form-label">Họ và tên khách hàng <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="customer_fullname" name="customer_fullname" required>
                                    <div class="form-text">Nhập họ tên đầy đủ của khách hàng</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customer_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="customer_phone" name="customer_phone" pattern="[0-9]{10,11}" required>
                                    <div class="form-text">Nhập số điện thoại 10-11 chữ số</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customer_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="customer_email" name="customer_email">
                                    <div class="form-text">Email khách hàng (không bắt buộc)</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customer_address" class="form-label">Địa chỉ</label>
                                    <input type="text" class="form-control" id="customer_address" name="customer_address">
                                    <div class="form-text">Địa chỉ khách hàng (không bắt buộc)</div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="shipping_note" class="form-label">Ghi chú giao hàng</label>
                                    <textarea class="form-control" id="shipping_note" name="shipping_note" rows="2" placeholder="Ghi chú cho việc giao hàng (nếu có)"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Phương thức thanh toán <span class="text-danger">*</span></label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">-- Chọn phương thức thanh toán --</option>
                                        <option value="COD">COD (Thanh toán khi nhận hàng)</option>
                                        <option value="Bank Transfer">Chuyển khoản</option>
                                        <option value="E-Wallet">Ví điện tử</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="note" class="form-label">Ghi chú đơn hàng</label>
                                    <textarea class="form-control" id="note" name="note" rows="2" placeholder="Ghi chú cho đơn hàng (nếu có)"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Hành động -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-save me-1"></i> Tạo đơn hàng
                                </button>
                                
                                <div class="text-muted small text-center">
                                    Nhấn "Tạo đơn hàng" đồng nghĩa với việc bạn đồng ý với các điều khoản và điều kiện của cửa hàng.
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
</div>


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
    // Cart items array
    let cartItems = [];
    
    // Toggle product selection area
    const toggleProductListBtn = document.getElementById('toggleProductList');
    const productSelectionArea = document.getElementById('productSelectionArea');
    
    toggleProductListBtn.addEventListener('click', function() {
        if (productSelectionArea.style.display === 'none') {
            productSelectionArea.style.display = 'block';
            this.innerHTML = '<i class="fas fa-times me-1"></i> Đóng';
            this.classList.remove('btn-primary');
            this.classList.add('btn-secondary');
        } else {
            productSelectionArea.style.display = 'none';
            this.innerHTML = '<i class="fas fa-plus me-1"></i> Thêm sản phẩm';
            this.classList.remove('btn-secondary');
            this.classList.add('btn-primary');
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
            
            // Auto-hide product selection area after adding
            if (productSelectionArea.style.display === 'block') {
                productSelectionArea.style.display = 'none';
                toggleProductListBtn.innerHTML = '<i class="fas fa-plus me-1"></i> Thêm sản phẩm';
                toggleProductListBtn.classList.remove('btn-secondary');
                toggleProductListBtn.classList.add('btn-primary');
            }
            
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
        const orderSummary = document.getElementById('orderSummary');
        
        // Show empty cart message if no items
        if (cartItems.length === 0) {
            if (emptyCartRow) {
                emptyCartRow.style.display = '';
            }
            if (orderSummary) {
                orderSummary.classList.add('d-none');
            }
            if (submitBtn) {
                submitBtn.disabled = true;
            }
        } else {
            if (emptyCartRow) {
                emptyCartRow.style.display = 'none';
            }
            if (orderSummary) {
                orderSummary.classList.remove('d-none');
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
        
        // Update UI
        document.getElementById('subtotalItems').textContent = totalItems + ' sản phẩm';
        document.getElementById('totalAmount').textContent = subtotal.toLocaleString() + ' ₫';
        
        // Update hidden fields for form submission
        updateFormData();
    }
    
    // Update form data before submission
    function updateFormData() {
        console.log('Updating form data...');
        console.log('Cart items before mapping:', cartItems);
        
        // Prepare order items data
        const orderItemsData = cartItems.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            unit_price: item.price,
            total_price: item.price * item.quantity
        }));
        
        console.log('Order items data:', orderItemsData);
        
        // Update hidden fields
        orderItemsInput.value = JSON.stringify(orderItemsData);
        totalAmountInput.value = document.getElementById('totalAmount').textContent.replace(/[^0-9]/g, '');
        
        console.log('Hidden fields updated:');
        console.log('orderItemsInput.value:', orderItemsInput.value);
        console.log('totalAmountInput.value:', totalAmountInput.value);
    }
    
    // Form submission
    orderForm.addEventListener('submit', function(e) {
        console.log('Form submitting...');
        console.log('Cart items:', cartItems);
        console.log('Cart items length:', cartItems.length);
        
        // Validate form
        if (cartItems.length === 0) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất một sản phẩm vào đơn hàng');
            return false;
        }
        
        const customerFullname = document.getElementById('customer_fullname').value.trim();
        const customerPhone = document.getElementById('customer_phone').value.trim();
        const shippingAddress = document.getElementById('shipping_address').value.trim();
        const paymentMethod = document.getElementById('payment_method').value;
        const orderCode = document.getElementById('order_code').value.trim().toUpperCase();
        
        // Validate mã đơn hàng
        if (!orderCode) {
            e.preventDefault();
            alert('Vui lòng nhập mã đơn hàng');
            document.getElementById('order_code').focus();
            return false;
        }
        
        // Validate format mã đơn hàng: DH + 7 chữ số
        if (!/^DH[0-9]{7}$/.test(orderCode)) {
            e.preventDefault();
            alert('Mã đơn hàng phải có định dạng DH + 7 chữ số (ví dụ: DH1234567)');
            document.getElementById('order_code').focus();
            return false;
        }
        
        if (!customerFullname) {
            e.preventDefault();
            alert('Vui lòng nhập họ và tên khách hàng');
            document.getElementById('customer_fullname').focus();
            return false;
        }
        
        if (!customerPhone) {
            e.preventDefault();
            alert('Vui lòng nhập số điện thoại khách hàng');
            document.getElementById('customer_phone').focus();
            return false;
        }
        
        // Validate số điện thoại
        if (!/^[0-9]{10,11}$/.test(customerPhone)) {
            e.preventDefault();
            alert('Số điện thoại phải có 10-11 chữ số');
            document.getElementById('customer_phone').focus();
            return false;
        }
        
        // Validate email nếu có
        const customerEmail = document.getElementById('customer_email').value.trim();
        if (customerEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(customerEmail)) {
            e.preventDefault();
            alert('Email không hợp lệ');
            document.getElementById('customer_email').focus();
            return false;
        }
        
        if (!shippingAddress) {
            e.preventDefault();
            alert('Vui lòng nhập địa chỉ giao hàng');
            document.getElementById('shipping_address').focus();
            return false;
        }
        
        if (!paymentMethod) {
            e.preventDefault();
            alert('Vui lòng chọn phương thức thanh toán');
            document.getElementById('payment_method').focus();
            return false;
        }
        
        // Update form data before submission
        updateFormData();
        
        // Đảm bảo mã đơn hàng được gửi với chữ hoa
        document.getElementById('order_code').value = orderCode;
        
        console.log('Form validation passed, submitting...');
        
        // Allow form to submit normally
        return true;
    });
    
    // Show alert message (removed UI alerts)
    
    // Auto-format số điện thoại
    const customerPhoneInput = document.getElementById('customer_phone');
    if (customerPhoneInput) {
        customerPhoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            e.target.value = value;
        });
    }
    
    // Auto-format mã đơn hàng
    const orderCodeInput = document.getElementById('order_code');
    if (orderCodeInput) {
        orderCodeInput.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            // Chỉ cho phép chữ cái và số
            value = value.replace(/[^A-Z0-9]/g, '');
            
            // Nếu bắt đầu bằng DH, chỉ cho phép số sau đó
            if (value.startsWith('DH')) {
                const numbers = value.substring(2).replace(/\D/g, '');
                if (numbers.length > 7) {
                    value = 'DH' + numbers.substring(0, 7);
                } else {
                    value = 'DH' + numbers;
                }
            } else if (value.length > 0 && !value.startsWith('DH')) {
                // Nếu không bắt đầu bằng DH, tự động thêm DH nếu bắt đầu bằng số
                if (/^[0-9]/.test(value)) {
                    const numbers = value.replace(/\D/g, '');
                    if (numbers.length > 7) {
                        value = 'DH' + numbers.substring(0, 7);
                    } else {
                        value = 'DH' + numbers;
                    }
                } else {
                    // Nếu bắt đầu bằng chữ khác, chỉ giữ lại DH nếu có
                    value = value.replace(/^[^D]*D?H?/i, 'DH');
                    const numbers = value.substring(2).replace(/\D/g, '');
                    if (numbers.length > 7) {
                        value = 'DH' + numbers.substring(0, 7);
                    } else {
                        value = 'DH' + numbers;
                    }
                }
            }
            
            e.target.value = value;
        });
        
        // Validate khi blur
        orderCodeInput.addEventListener('blur', function(e) {
            const value = e.target.value.trim().toUpperCase();
            const errorDiv = document.getElementById('order_code_error');
            
            if (value && !/^DH[0-9]{7}$/.test(value)) {
                e.target.classList.add('is-invalid');
                if (errorDiv) {
                    errorDiv.textContent = 'Mã đơn hàng phải có định dạng DH + 7 chữ số (ví dụ: DH1234567)';
                }
            } else {
                e.target.classList.remove('is-invalid');
                if (errorDiv) {
                    errorDiv.textContent = '';
                }
            }
        });
    }
    
    // Tự động điền địa chỉ giao hàng từ địa chỉ khách hàng (nếu có)
    const customerAddressInput = document.getElementById('customer_address');
    const shippingAddressInput = document.getElementById('shipping_address');
    if (customerAddressInput && shippingAddressInput) {
        customerAddressInput.addEventListener('blur', function() {
            // Chỉ tự động điền nếu địa chỉ giao hàng đang trống
            if (!shippingAddressInput.value.trim() && this.value.trim()) {
                shippingAddressInput.value = this.value.trim();
            }
        });
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>