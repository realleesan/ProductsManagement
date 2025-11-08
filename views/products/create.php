<?php 
$page_title = 'Thêm sản phẩm mới';
$active_page = 'products';
require_once __DIR__ . '/../layouts/header.php'; 

// Lấy dữ liệu form cũ nếu có lỗi
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['form_data']);
?>

<div class="page-header">
    <h1><i class="fas fa-plus-circle"></i> Thêm sản phẩm mới</h1>
    <a href="<?php echo BASE_URL; ?>/?controller=ProductController&action=index" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo BASE_URL; ?>/?controller=ProductController&action=store" enctype="multipart/form-data" class="product-form">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="product_code">Mã sản phẩm <span class="required">*</span></label>
                    <input type="text" 
                           id="product_code" 
                           name="product_code" 
                           class="form-control" 
                           pattern="^SP[0-9]{7}$"
                           maxlength="9"
                           placeholder="VD: SP1234567"
                           value="<?php echo isset($form_data['product_code']) ? htmlspecialchars($form_data['product_code']) : ''; ?>"
                           required>
                    <small class="form-text">Định dạng: SP + 7 chữ số (ví dụ: SP1234567)</small>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Danh mục</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>"
                                <?php echo (isset($form_data['category_id']) && $form_data['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                            <?php echo $cat['category_name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="product_name">Tên sản phẩm <span class="required">*</span></label>
                <input type="text" 
                       id="product_name" 
                       name="product_name" 
                       class="form-control" 
                       placeholder="Nhập tên sản phẩm (5-150 ký tự)"
                       value="<?php echo isset($form_data['product_name']) ? htmlspecialchars($form_data['product_name']) : ''; ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="description">Mô tả sản phẩm</label>
                <textarea id="description" 
                          name="description" 
                          class="form-control" 
                          rows="4" 
                          placeholder="Mô tả công dụng sản phẩm (tối đa 500 ký tự)"
                          maxlength="500"><?php echo isset($form_data['description']) ? htmlspecialchars($form_data['description']) : ''; ?></textarea>
                <small class="form-text">Còn lại: <span id="char-count">500</span> ký tự</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Giá bán (VNĐ) <span class="required">*</span></label>
                    <input type="number" 
                           id="price" 
                           name="price" 
                           class="form-control" 
                           placeholder="Từ 1.000 đến 1.000.000.000"
                           min="1000"
                           max="1000000000"
                           step="1000"
                           value="<?php echo isset($form_data['price']) ? $form_data['price'] : ''; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="stock_quantity">Số lượng tồn kho <span class="required">*</span></label>
                    <input type="number" 
                           id="stock_quantity" 
                           name="stock_quantity" 
                           class="form-control" 
                           placeholder="Số lượng >= 0"
                           min="0"
                           value="<?php echo isset($form_data['stock_quantity']) ? $form_data['stock_quantity'] : '0'; ?>"
                           required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="manufacture_date">Ngày sản xuất <span class="required">*</span></label>
                    <input type="date" 
                           id="manufacture_date" 
                           name="manufacture_date" 
                           class="form-control"
                           value="<?php echo isset($form_data['manufacture_date']) ? $form_data['manufacture_date'] : ''; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="expiry_date">Hạn sử dụng <span class="required">*</span></label>
                    <input type="date" 
                           id="expiry_date" 
                           name="expiry_date" 
                           class="form-control"
                           value="<?php echo isset($form_data['expiry_date']) ? $form_data['expiry_date'] : ''; ?>"
                           required>
                    <small class="form-text">Phải sau ngày sản xuất ít nhất 90 ngày</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="status">Trạng thái <span class="required">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    <option value="Active" <?php echo (isset($form_data['status']) && $form_data['status'] == 'Active') ? 'selected' : 'selected'; ?>>Active</option>
                    <option value="Disabled" <?php echo (isset($form_data['status']) && $form_data['status'] == 'Disabled') ? 'selected' : ''; ?>>Disabled</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Ảnh chính</label>
                <small class="form-text">Chấp nhận: JPG, JPEG, PNG. Tối đa 5MB.</small>
                <div class="main-image-upload">
                    <label for="main_image" class="image-upload-label">
                        <i class="fas fa-camera"></i>
                        <span>Chọn ảnh chính</span>
                        <input type="file" 
                               id="main_image" 
                               name="main_image" 
                               accept="image/jpeg,image/jpg,image/png"
                               onchange="previewMainImage(this)">
                    </label>
                    <div id="main-image-preview" class="image-preview large"></div>
                </div>
            </div>

            <div class="form-group">
                <label>Ảnh phụ (tối đa <?php echo MAX_GALLERY_IMAGES; ?> ảnh)</label>
                <small class="form-text">Chấp nhận: JPG, JPEG, PNG. Tối đa 5MB/ảnh. Có thể chọn nhiều ảnh cùng lúc.</small>
                <input type="file" 
                       id="gallery_images" 
                       name="gallery_images[]" 
                       class="form-control"
                       accept="image/jpeg,image/jpg,image/png"
                       multiple
                       onchange="previewGalleryImages(this)">
                <div id="gallery-preview" class="gallery-preview-grid"></div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Lưu sản phẩm
                </button>
                <a href="<?php echo BASE_URL; ?>/?controller=ProductController&action=index" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
