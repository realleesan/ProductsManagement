<?php include '../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="fas fa-edit text-primary me-2"></i> Chỉnh sửa đơn hàng #<?= htmlspecialchars($order['order_code']) ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/controllers/ProductController.php?action=dashboard">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?controller=OrderController&action=index">Đơn hàng</a></li>
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?controller=OrderController&action=view&id=<?= $order['order_id'] ?>">#<?= htmlspecialchars($order['order_code']) ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>?controller=OrderController&action=view&id=<?= $order['order_id'] ?>" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-times me-1"></i> Hủy
                    </a>
                    <button type="submit" form="orderForm" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Lưu thay đổi
                    </button>
                </div>
            </div>
            
            <?php include '../partials/alert.php'; ?>
            
            <form id="orderForm" method="post" action="<?= BASE_URL ?>?controller=OrderController&action=update&id=<?= $order['order_id'] ?>">
                <input type="hidden" name="_method" value="PUT">
                
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Thông tin sản phẩm -->
                        <div class="card mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Sản phẩm</h5>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <i class="fas fa-plus me-1"></i> Thêm sản phẩm
                                </button>
                            </div>
                            <div class="card-body p-0">
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
                                            <!-- Sản phẩm sẽ được thêm vào đây bằng JavaScript -->
                                            <?php if (empty($order['order_items'])): ?>
                                                <tr id="emptyCart">
                                                    <td colspan="6" class="text-center py-4">
                                                        <div class="text-muted">Chưa có sản phẩm nào</div>
                                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                                            <i class="fas fa-plus me-1"></i> Thêm sản phẩm
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <tfoot id="orderSummary">
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Tạm tính:</strong></td>
                                                <td class="text-center" id="subtotalItems">0</td>
                                                <td class="text-end" id="subtotalAmount">0 ₫</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end">
                                                    <div class="input-group input-group-sm justify-content-end">
                                                        <span class="input-group-text">Giảm giá</span>
                                                        <input type="number" class="form-control text-end" id="discount" name="discount" value="<?= $order['discount_amount'] ?? 0 ?>" min="0" style="width: 100px;">
                                                        <span class="input-group-text">₫</span>
                                                    </div>
                                                </td>
                                                <td class="text-end" id="discountAmount"><?= isset($order['discount_amount']) ? number_format($order['discount_amount']) . ' ₫' : '0 ₫' ?></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end">
                                                    <div class="input-group input-group-sm justify-content-end">
                                                        <span class="input-group-text">Phí vận chuyển</span>
                                                        <input type="number" class="form-control text-end" id="shippingFee" name="shipping_fee" value="<?= $order['shipping_fee'] ?? 0 ?>" min="0" style="width: 100px;">
                                                        <span class="input-group-text">₫</span>
                                                    </div>
                                                </td>
                                                <td class="text-end" id="shippingFeeAmount"><?= isset($order['shipping_fee']) ? number_format($order['shipping_fee']) . ' ₫' : '0 ₫' ?></td>
                                                <td></td>
                                            </tr>
                                            <tr class="table-active">
                                                <td colspan="4" class="text-end"><strong>Tổng cộng:</strong></td>
                                                <td class="text-end fw-bold fs-5" id="totalAmount"><?= isset($order['total_amount']) ? number_format($order['total_amount']) . ' ₫' : '0 ₫' ?></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Thông tin bổ sung -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Thông tin bổ sung</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="note" class="form-label">Ghi chú đơn hàng</label>
                                    <textarea class="form-control" id="note" name="note" rows="3" placeholder="Ghi chú cho đơn hàng (không bắt buộc)"><?= htmlspecialchars($order['note'] ?? '') ?></textarea>
                                </div>
                                
                                <?php if (!empty($order['cancel_reason'])): ?>
                                    <div class="alert alert-warning">
                                        <h6 class="alert-heading">Lý do hủy đơn hàng:</h6>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($order['cancel_reason'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Lịch sử thay đổi -->
                        <?php if (!empty($order_history)): ?>
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">Lịch sử thay đổi</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($order_history as $history): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <?php 
                                                                $action_text = [
                                                                    'Created' => 'Tạo đơn hàng',
                                                                    'Confirmed' => 'Xác nhận đơn hàng',
                                                                    'Processing' => 'Đang xử lý',
                                                                    'Shipping' => 'Đang giao hàng',
                                                                    'Completed' => 'Hoàn tất đơn hàng',
                                                                    'Cancelled' => 'Hủy đơn hàng',
                                                                    'Updated' => 'Cập nhật đơn hàng'
                                                                ][$history['action_type']] ?? $history['action_type'];
                                                                
                                                                $action_icon = [
                                                                    'Created' => 'plus-circle',
                                                                    'Confirmed' => 'check-circle',
                                                                    'Processing' => 'cog',
                                                                    'Shipping' => 'truck',
                                                                    'Completed' => 'check-double',
                                                                    'Cancelled' => 'times-circle',
                                                                    'Updated' => 'edit'
                                                                ][$history['action_type']] ?? 'info-circle';
                                                            ?>
                                                            <i class="fas fa-<?= $action_icon ?> text-primary me-2"></i>
                                                            <?= $action_text ?>
                                                        </h6>
                                                        <?php if (!empty($history['note'])): ?>
                                                            <p class="mb-1"><?= nl2br(htmlspecialchars($history['note'])) ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($history['old_status']) && !empty($history['new_status'])): ?>
                                                            <div class="small text-muted">
                                                                <span class="badge bg-secondary"><?= $history['old_status'] ?></span>
                                                                <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                                                <span class="badge bg-<?= $history['new_status'] === 'Đã hủy' ? 'danger' : 'success' ?>">
                                                                    <?= $history['new_status'] ?>
                                                                </span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="text-muted small">
                                                            <?= date('H:i d/m/Y', strtotime($history['action_at'])) ?>
                                                        </div>
                                                        <div class="text-muted small">
                                                            <?= htmlspecialchars($history['action_by']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Thông tin khách hàng -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Thông tin khách hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="customer_id" class="form-label">Khách hàng <span class="text-danger">*</span></label>
                                    <select class="form-select" id="customer_id" name="customer_id" required>
                                        <option value="">-- Chọn khách hàng --</option>
                                        <?php foreach ($customers as $customer): ?>
                                            <option value="<?= $customer['customer_id'] ?>" 
                                                <?= ($customer['customer_id'] == $order['customer_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($customer['fullname']) ?> 
                                                (<?= !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : 'Chưa có SĐT' ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <a href="/quanlysanpham/customers/create" target="_blank">
                                            <i class="fas fa-plus"></i> Thêm khách hàng mới
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?= htmlspecialchars($order['shipping_address']) ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="shipping_note" class="form-label">Ghi chú giao hàng</label>
                                    <textarea class="form-control" id="shipping_note" name="shipping_note" rows="2"><?= htmlspecialchars($order['shipping_note'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Trạng thái đơn hàng -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Trạng thái đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Trạng thái hiện tại</label>
                                    <select class="form-select" id="status" name="status" <?= $order['status'] === 'Đã hủy' || $order['status'] === 'Hoàn tất' ? 'disabled' : '' ?>>
                                        <?php 
                                            $statuses = [
                                                'Chờ xác nhận' => 'Chờ xác nhận',
                                                'Đang xử lý' => 'Đang xử lý',
                                                'Đang giao' => 'Đang giao',
                                                'Hoàn tất' => 'Hoàn tất',
                                                'Đã hủy' => 'Đã hủy'
                                            ];
                                            
                                            foreach ($statuses as $value => $label):
                                                $selected = ($order['status'] === $value) ? 'selected' : '';
                                                echo "<option value=\"$value\" $selected>$label</option>";
                                            endforeach;
                                        ?>
                                    </select>
                                    <?php if ($order['status'] === 'Đã hủy' || $order['status'] === 'Hoàn tất'): ?>
                                        <input type="hidden" name="status" value="<?= $order['status'] ?>">
                                        <div class="form-text text-muted">Không thể thay đổi trạng thái đơn hàng đã <?= strtolower($order['status']) ?>.</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3" id="cancelReasonContainer" style="display: <?= $order['status'] === 'Đã hủy' ? 'block' : 'none' ?>">
                                    <label for="cancel_reason" class="form-label">Lý do hủy đơn hàng</label>
                                    <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3" <?= $order['status'] === 'Đã hủy' ? '' : 'disabled' ?>><?= htmlspecialchars($order['cancel_reason'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Đơn hàng được tạo bởi <strong><?= htmlspecialchars($order['created_by']) ?></strong> 
                                    vào <?= date('H:i d/m/Y', strtotime($order['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Thanh toán -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Thanh toán</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Phương thức thanh toán <span class="text-danger">*</span></label>
                                    <div class="list-group">
                                        <?php 
                                            $payment_methods = [
                                                'cod' => 'Thanh toán khi nhận hàng (COD)',
                                                'bank' => 'Chuyển khoản ngân hàng',
                                                'momo' => 'Ví điện tử MoMo',
                                                'vnpay' => 'VNPAY',
                                                'zalopay' => 'ZaloPay'
                                            ];
                                            
                                            foreach ($payment_methods as $value => $label):
                                                $checked = ($order['payment_method'] === $value) ? 'checked' : '';
                                        ?>
                                            <label class="list-group-item">
                                                <input class="form-check-input me-2" type="radio" name="payment_method" value="<?= $value ?>" <?= $checked ?>>
                                                <?= $label ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái thanh toán</label>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid" <?= $order['payment_status'] === 'paid' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_paid">Đã thanh toán</label>
                                    </div>
                                    
                                    <div id="paymentInfo" class="<?= $order['payment_status'] === 'paid' ? '' : 'd-none' ?>">
                                        <div class="mb-3">
                                            <label for="payment_date" class="form-label">Ngày thanh toán</label>
                                            <input type="datetime-local" class="form-control" id="payment_date" name="payment_date" 
                                                   value="<?= $order['payment_date'] ? date('Y-m-d\TH:i', strtotime($order['payment_date'])) : date('Y-m-d\TH:i') ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="payment_note" class="form-label">Ghi chú thanh toán</label>
                                            <input type="text" class="form-control" id="payment_note" name="payment_note" 
                                                   value="<?= htmlspecialchars($order['payment_note'] ?? '') ?>" 
                                                   placeholder="Số giao dịch, mã tham chiếu...">
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($order['payment_status'] === 'paid'): ?>
                                    <div class="alert alert-success p-2 small mb-0">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Đã thanh toán
                                        <?php if (!empty($order['payment_date'])): ?>
                                            vào <?= date('H:i d/m/Y', strtotime($order['payment_date'])) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($order['payment_note'])): ?>
                                            <div class="mt-1">
                                                <strong>Ghi chú:</strong> <?= htmlspecialchars($order['payment_note']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Hành động -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-save me-1"></i> Lưu thay đổi
                                </button>
                                
                                
                                <?php if ($order['status'] === 'Chờ xác nhận'): ?>
                                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                                        <i class="fas fa-times me-1"></i> Hủy đơn hàng
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal chọn sản phẩm -->
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
                            <?php foreach ($products as $index => $product): 
                                $inCart = false;
                                $cartQuantity = 0;
                                
                                foreach ($order['order_items'] as $item) {
                                    if ($item['product_id'] == $product['product_id']) {
                                        $inCart = true;
                                        $cartQuantity = $item['quantity'];
                                        break;
                                    }
                                }
                                
                                $availableStock = $inCart ? $product['stock_quantity'] + $cartQuantity : $product['stock_quantity'];
                            ?>
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
                                        <span class="badge bg-<?= $availableStock > 0 ? 'success' : 'danger' ?>">
                                            <?= $availableStock ?>
                                        </span>
                                        <?php if ($inCart): ?>
                                            <div class="text-muted small">Đã chọn: <?= $cartQuantity ?></div>
                                        <?php endif; ?>
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
                                                   value="1" min="1" max="<?= $availableStock ?>" 
                                                   style="width: 50px;" <?= $availableStock <= 0 ? 'disabled' : '' ?>>
                                            <button type="button" class="btn btn-outline-secondary btn-sm quantity-increase" 
                                                    data-product-id="<?= $product['product_id'] ?>"
                                                    <?= $availableStock <= 0 ? 'disabled' : '' ?>>+</button>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-<?= $inCart ? 'warning' : 'primary' ?> add-to-cart" 
                                                data-product-id="<?= $product['product_id'] ?>"
                                                data-product-name="<?= htmlspecialchars($product['product_name']) ?>"
                                                data-product-code="<?= htmlspecialchars($product['product_code']) ?>"
                                                data-product-price="<?= $product['price'] ?>"
                                                data-stock-quantity="<?= $availableStock ?>"
                                                <?= $availableStock <= 0 ? 'disabled' : '' ?>>
                                            <i class="fas fa-<?= $inCart ? 'edit' : 'plus' ?>"></i>
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

<!-- Modal xác nhận hủy đơn hàng -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận hủy đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn hủy đơn hàng <strong>#<?= htmlspecialchars($order['order_code']) ?></strong> không?</p>
                <div class="mb-3">
                    <label for="cancel_reason_modal" class="form-label">Lý do hủy <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="cancel_reason_modal" rows="3" required placeholder="Vui lòng nhập lý do hủy đơn hàng"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="button" class="btn btn-danger" id="confirmCancelOrder">
                    <i class="fas fa-times me-1"></i> Xác nhận hủy
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo dữ liệu đơn hàng
    const orderItems = <?= json_encode($order['order_items'] ?? []) ?>;
    const orderData = <?= json_encode($order) ?>;
    
    // Khởi tạo giỏ hàng từ dữ liệu đơn hàng
    let cartItems = [];
    
    orderItems.forEach(item => {
        cartItems.push({
            id: item.product_id,
            name: item.product_name,
            code: item.product_code,
            price: parseFloat(item.unit_price),
            quantity: parseInt(item.quantity),
            stock: parseInt(item.stock_quantity),
            image: item.image_url ? `/quanlysanpham/uploads/products/${item.image_url}` : ''
        });
    });
    
    // Render giỏ hàng
    renderCart();
    updateOrderSummary();
    
    // Xử lý hiển thị/ẩn lý do hủy đơn hàng
    const statusSelect = document.getElementById('status');
    const cancelReasonContainer = document.getElementById('cancelReasonContainer');
    const cancelReasonInput = document.getElementById('cancel_reason');
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'Đã hủy') {
                cancelReasonContainer.style.display = 'block';
                if (cancelReasonInput) {
                    cancelReasonInput.disabled = false;
                    cancelReasonInput.required = true;
                }
            } else {
                cancelReasonContainer.style.display = 'none';
                if (cancelReasonInput) {
                    cancelReasonInput.disabled = true;
                    cancelReasonInput.required = false;
                }
            }
        });
    }
    
    // Xử lý nút xác nhận hủy đơn hàng
    const confirmCancelBtn = document.getElementById('confirmCancelOrder');
    if (confirmCancelBtn) {
        confirmCancelBtn.addEventListener('click', function() {
            const cancelReason = document.getElementById('cancel_reason_modal').value.trim();
            
            if (!cancelReason) {
                showAlert('Vui lòng nhập lý do hủy đơn hàng', 'warning');
                return;
            }
            
            // Cập nhật trạng thái và lý do hủy
            if (statusSelect) {
                statusSelect.value = 'Đã hủy';
                statusSelect.dispatchEvent(new Event('change'));
            }
            
            if (cancelReasonInput) {
                cancelReasonInput.value = cancelReason;
            }
            
            // Đóng modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('cancelOrderModal'));
            if (modal) {
                modal.hide();
            }
            
            // Cuộn đến phần lý do hủy
            cancelReasonContainer.scrollIntoView({ behavior: 'smooth' });
            
            showAlert('Đã cập nhật trạng thái hủy đơn hàng. Vui lòng lưu thay đổi.', 'info');
        });
    }
    
    // Xử lý thanh toán
    const isPaidCheckbox = document.getElementById('is_paid');
    const paymentInfo = document.getElementById('paymentInfo');
    
    if (isPaidCheckbox && paymentInfo) {
        isPaidCheckbox.addEventListener('change', function() {
            if (this.checked) {
                paymentInfo.classList.remove('d-none');
            } else {
                paymentInfo.classList.add('d-none');
            }
        });
    }
    
    // Tìm kiếm sản phẩm
    const searchProduct = document.getElementById('searchProduct');
    if (searchProduct) {
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
    }
    
    // Tăng/giảm số lượng sản phẩm
    document.addEventListener('click', function(e) {
        // Tăng/giảm số lượng trong modal
        if (e.target.classList.contains('quantity-increase') || e.target.classList.contains('quantity-decrease')) {
            const input = e.target.classList.contains('quantity-increase') 
                ? e.target.previousElementSibling 
                : e.target.nextElementSibling;
                
            if (e.target.classList.contains('quantity-increase')) {
                const max = parseInt(input.getAttribute('max')) || 9999;
                if (parseInt(input.value) < max) {
                    input.value = parseInt(input.value) + 1;
                }
            } else {
                if (parseInt(input.value) > 1) {
                    input.value = parseInt(input.value) - 1;
                }
            }
        }
        
        // Thêm sản phẩm vào giỏ hàng
        if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
            const button = e.target.classList.contains('add-to-cart') ? e.target : e.target.closest('.add-to-cart');
            const productId = parseInt(button.getAttribute('data-product-id'));
            const productName = button.getAttribute('data-product-name');
            const productCode = button.getAttribute('data-product-code');
            const productPrice = parseFloat(button.getAttribute('data-product-price'));
            const stockQuantity = parseInt(button.getAttribute('data-stock-quantity'));
            const quantityInput = button.closest('tr').querySelector('.quantity-input');
            const quantity = parseInt(quantityInput ? quantityInput.value : 1);
            
            // Kiểm tra nếu sản phẩm đã có trong giỏ hàng
            const existingItemIndex = cartItems.findIndex(item => item.id === productId);
            
            if (existingItemIndex >= 0) {
                // Cập nhật số lượng nếu sản phẩm đã có trong giỏ
                cartItems[existingItemIndex].quantity = quantity;
                showAlert(`Đã cập nhật số lượng ${productName} thành ${quantity}`, 'success');
            } else {
                // Thêm sản phẩm mới vào giỏ hàng
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
                
                showAlert(`Đã thêm ${quantity} ${productName} vào đơn hàng`, 'success');
            }
            
            // Cập nhật giao diện
            renderCart();
            updateOrderSummary();
        }
    });
    
    // Render giỏ hàng
    function renderCart() {
        const orderItemsContainer = document.getElementById('orderItems');
        const emptyCartRow = document.getElementById('emptyCart');
        const orderSummary = document.getElementById('orderSummary');
        
        // Xóa tất cả sản phẩm hiện có
        orderItemsContainer.querySelectorAll('tr:not(#emptyCart)').forEach(row => row.remove());
        
        if (cartItems.length === 0) {
            if (emptyCartRow) emptyCartRow.style.display = '';
            if (orderSummary) orderSummary.classList.add('d-none');
            return;
        }
        
        if (emptyCartRow) emptyCartRow.style.display = 'none';
        if (orderSummary) orderSummary.classList.remove('d-none');
        
        // Thêm từng sản phẩm vào giỏ hàng
        cartItems.forEach((item, index) => {
            const row = document.createElement('tr');
            row.setAttribute('data-product-id', item.id);
            row.innerHTML = `
                <td class="align-middle">${index + 1}</td>
                <td>
                    <div class="d-flex align-items-center">
                        ${item.image ? 
                            `<div class="flex-shrink-0 me-2">
                                <img src="${item.image}" alt="${item.name}" class="product-image" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='/quanlysanpham/assets/img/no-image.png'">
                            </div>` : 
                            `<div class="flex-shrink-0 me-2">
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-box text-muted"></i>
                                </div>
                            </div>`
                        }
                        <div>
                            <div class="fw-bold product-name">${item.name}</div>
                            <div class="text-muted small product-code">${item.code}</div>
                            <div class="text-muted small">Tồn kho: <span class="stock-quantity">${item.stock}</span></div>
                        </div>
                    </div>
                </td>
                <td class="align-middle text-end">
                    <span class="product-price">${item.price.toLocaleString()}</span> ₫
                </td>
                <td class="align-middle">
                    <div class="input-group input-group-sm">
                        <button type="button" class="btn btn-outline-secondary btn-sm cart-quantity-decrease">-</button>
                        <input type="number" class="form-control text-center cart-quantity" 
                               value="${item.quantity}" min="1" max="${item.stock}">
                        <button type="button" class="btn btn-outline-secondary btn-sm cart-quantity-increase">+</button>
                    </div>
                </td>
                <td class="align-middle text-end fw-bold item-total">${(item.price * item.quantity).toLocaleString()} ₫</td>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            
            orderItemsContainer.appendChild(row);
        });
        
        // Thêm sự kiện cho các nút tăng/giảm số lượng
        document.querySelectorAll('.cart-quantity-increase').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const max = parseInt(input.getAttribute('max')) || 9999;
                if (parseInt(input.value) < max) {
                    input.value = parseInt(input.value) + 1;
                    updateCartItemQuantity(parseInt(this.closest('tr').getAttribute('data-product-id')), parseInt(input.value));
                    updateOrderSummary();
                }
            });
        });
        
        document.querySelectorAll('.cart-quantity-decrease').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.nextElementSibling;
                if (parseInt(input.value) > 1) {
                    input.value = parseInt(input.value) - 1;
                    updateCartItemQuantity(parseInt(this.closest('tr').getAttribute('data-product-id')), parseInt(input.value));
                    updateOrderSummary();
                }
            });
        });
        
        // Thêm sự kiện cho nút xóa sản phẩm
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const productId = parseInt(this.closest('tr').getAttribute('data-product-id'));
                removeFromCart(productId);
            });
        });
        
        // Thêm sự kiện thay đổi số lượng trực tiếp
        document.querySelectorAll('.cart-quantity').forEach(input => {
            input.addEventListener('change', function() {
                const productId = parseInt(this.closest('tr').getAttribute('data-product-id'));
                const quantity = parseInt(this.value) || 1;
                
                if (quantity < 1) {
                    this.value = 1;
                    updateCartItemQuantity(productId, 1);
                } else {
                    updateCartItemQuantity(productId, quantity);
                }
                
                updateOrderSummary();
            });
        });
    }
    
    // Cập nhật số lượng sản phẩm trong giỏ hàng
    function updateCartItemQuantity(productId, quantity) {
        const itemIndex = cartItems.findIndex(item => item.id === productId);
        
        if (itemIndex >= 0) {
            cartItems[itemIndex].quantity = quantity;
            
            // Cập nhật tổng tiền trong dòng
            const row = document.querySelector(`#orderItems tr[data-product-id="${productId}"]`);
            if (row) {
                const price = parseFloat(row.querySelector('.product-price').textContent.replace(/[^0-9]/g, ''));
                const total = price * quantity;
                row.querySelector('.item-total').textContent = total.toLocaleString() + ' ₫';
            }
            
            // Cập nhật số thứ tự
            updateRowNumbers();
        }
    }
    
    // Xóa sản phẩm khỏi giỏ hàng
    function removeFromCart(productId) {
        const itemIndex = cartItems.findIndex(item => item.id === productId);
        
        if (itemIndex >= 0) {
            const itemName = cartItems[itemIndex].name;
            cartItems.splice(itemIndex, 1);
            
            // Cập nhật giao diện
            renderCart();
            updateOrderSummary();
            
            showAlert(`Đã xóa ${itemName} khỏi đơn hàng`, 'info');
        }
    }
    
    // Cập nhật số thứ tự các dòng
    function updateRowNumbers() {
        const rows = document.querySelectorAll('#orderItems tr:not(#emptyCart)');
        rows.forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }
    
    // Cập nhật tổng kết đơn hàng
    function updateOrderSummary() {
        let subtotal = 0;
        let totalItems = 0;
        
        cartItems.forEach(item => {
            subtotal += item.price * item.quantity;
            totalItems += item.quantity;
        });
        
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const shippingFee = parseFloat(document.getElementById('shippingFee').value) || 0;
        const total = subtotal - discount + shippingFee;
        
        // Cập nhật giao diện
        document.getElementById('subtotalItems').textContent = totalItems;
        document.getElementById('subtotalAmount').textContent = subtotal.toLocaleString() + ' ₫';
        document.getElementById('discountAmount').textContent = '-' + discount.toLocaleString() + ' ₫';
        document.getElementById('shippingFeeAmount').textContent = shippingFee.toLocaleString() + ' ₫';
        document.getElementById('totalAmount').textContent = total.toLocaleString() + ' ₫';
        
        // Cập nhật giá trị ẩn cho form
        document.getElementById('totalAmountInput').value = total;
        document.getElementById('orderItemsInput').value = JSON.stringify(cartItems.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            unit_price: item.price
        })));
    }
    
    // Xử lý sự kiện thay đổi giảm giá và phí vận chuyển
    [document.getElementById('discount'), document.getElementById('shippingFee')].forEach(input => {
        if (input) {
            input.addEventListener('change', updateOrderSummary);
            input.addEventListener('input', updateOrderSummary);
        }
    });
    
    // Xử lý gửi form
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            if (cartItems.length === 0) {
                e.preventDefault();
                showAlert('Vui lòng thêm ít nhất một sản phẩm vào đơn hàng', 'warning');
                return false;
            }
            
            // Kiểm tra thông tin khách hàng
            const customerId = document.getElementById('customer_id').value;
            const shippingAddress = document.getElementById('shipping_address').value;
            
            if (!customerId) {
                e.preventDefault();
                showAlert('Vui lòng chọn khách hàng', 'warning');
                return false;
            }
            
            if (!shippingAddress.trim()) {
                e.preventDefault();
                showAlert('Vui lòng nhập địa chỉ giao hàng', 'warning');
                return false;
            }
            
            // Vô hiệu hóa nút gửi để tránh gửi nhiều lần
            const submitButton = orderForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Đang lưu...';
            }
            
            return true;
        });
    }
});

// Hàm hiển thị thông báo
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Tự động ẩn thông báo sau 5 giây
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(alertDiv);
        }, 150);
    }, 5000);
}
</script>

<?php include '../layouts/footer.php'; ?>
