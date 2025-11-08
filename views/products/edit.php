<?php 
$page_title = 'Sửa sản phẩm';
$active_page = 'products';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="page-header">
    <h1><i class="fas fa-edit"></i> Sửa sản phẩm: <?php echo $product->product_name; ?></h1>
    <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=index" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=update" enctype="multipart/form-data" class="product-form">
            
            <input type="hidden" name="product_id" value="<?php echo $product->product_id; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="product_code">Mã sản phẩm <span class="required">*</span></label>
                    <input type="text" 
                           id="product_code" 
                           name="product_code" 
                           class="form-control" 
                           pattern="^SP[0-9]{7}$"
                           maxlength="9"
                           value="<?php echo htmlspecialchars($product->product_code); ?>"
                           required>
                    <small class="form-text">Định dạng: SP + 7 chữ số (ví dụ: SP1234567)</small>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Danh mục</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>"
                                <?php echo ($product->category_id == $cat['category_id']) ? 'selected' : ''; ?>>
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
                       value="<?php echo htmlspecialchars($product->product_name); ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="description">Mô tả sản phẩm</label>
                <textarea id="description" 
                          name="description" 
                          class="form-control" 
                          rows="4" 
                          maxlength="500"><?php echo htmlspecialchars($product->description); ?></textarea>
                <small class="form-text">Còn lại: <span id="char-count">500</span> ký tự</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Giá bán (VNĐ) <span class="required">*</span></label>
                    <input type="number" 
                           id="price" 
                           name="price" 
                           class="form-control" 
                           min="1000"
                           max="1000000000"
                           step="1000"
                           value="<?php echo $product->price; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="stock_quantity">Số lượng tồn kho <span class="required">*</span></label>
                    <input type="number" 
                           id="stock_quantity" 
                           name="stock_quantity" 
                           class="form-control" 
                           min="0"
                           value="<?php echo $product->stock_quantity; ?>"
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
                           value="<?php echo $product->manufacture_date; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="expiry_date">Hạn sử dụng <span class="required">*</span></label>
                    <input type="date" 
                           id="expiry_date" 
                           name="expiry_date" 
                           class="form-control"
                           value="<?php echo $product->expiry_date; ?>"
                           required>
                    <small class="form-text">Phải sau ngày sản xuất ít nhất 90 ngày</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="status">Trạng thái <span class="required">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    <option value="Active" <?php echo ($product->status == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Disabled" <?php echo ($product->status == 'Disabled') ? 'selected' : ''; ?>>Disabled</option>
                    <option value="Out of stock" <?php echo ($product->status == 'Out of stock') ? 'selected' : ''; ?>>Out of stock</option>
                    <option value="Expired" <?php echo ($product->status == 'Expired') ? 'selected' : ''; ?>>Expired</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Ảnh chính</label>
                <small class="form-text">Chấp nhận: JPG, JPEG, PNG. Tối đa 5MB. Để trống nếu giữ nguyên ảnh hiện tại.</small>
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
                    <div id="main-image-preview" class="image-preview large">
                        <?php if (!empty($product->main_image)): ?>
                        <div class="main-image-container">
                            <img src="<?php echo UPLOAD_URL . $product->main_image; ?>" alt="Ảnh chính">
                            <button type="button" class="remove-main-image" title="Xóa ảnh" onclick="return removeMainImage(this);">
                                <i class="fas fa-times"></i>
                            </button>
                            <input type="hidden" name="keep_main_image" value="1">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <small class="form-text">Chấp nhận: JPG, JPEG, PNG. Tối đa 5MB/ảnh. Có thể chọn nhiều ảnh cùng lúc.</small>
                
                <div class="gallery-upload-container">
                    <!-- Existing gallery images -->
                    <?php 
                    // Filter out any empty gallery images
                    $gallery_images = !empty($product->gallery_images) ? array_filter($product->gallery_images, function($img) {
                        return !empty($img['image_path']) && file_exists(UPLOAD_PATH . $img['image_path']);
                    }) : [];
                    
                    $existingGalleryCount = count($gallery_images);
                    $maxGalleryImages = defined('MAX_GALLERY_IMAGES') ? MAX_GALLERY_IMAGES : 5;
                    $canUploadMore = $existingGalleryCount < $maxGalleryImages;
                    ?>
                    
                    <?php if (!empty($gallery_images)): ?>
                        <?php foreach ($gallery_images as $gallery): ?>
                            <?php if (!empty($gallery['image_path']) && file_exists(UPLOAD_PATH . $gallery['image_path'])): ?>
                            <div class="gallery-image-item" data-image-id="<?php echo (int)$gallery['image_id']; ?>">
                                <img src="<?php echo UPLOAD_URL . $gallery['image_path']; ?>" alt="Gallery image" onerror="this.parentNode.remove()">
                                <div class="image-actions">
                                    <button type="button" class="remove-gallery-image" 
                                            title="Xóa ảnh"
                                            data-image-id="<?php echo (int)$gallery['image_id']; ?>"
                                            onclick="removeGalleryImage(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <input type="hidden" name="keep_gallery[]" value="<?php echo (int)$gallery['image_id']; ?>">
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Upload area -->
                    <?php if ($canUploadMore): ?>
                    <div class="gallery-upload-area" id="gallery-upload-area">
                        <label for="gallery_images" class="image-upload-label">
                            <i class="fas fa-images"></i>
                            <span>Chọn ảnh phụ</span>
                            <input type="file" 
                                   id="gallery_images" 
                                   name="gallery_images[]" 
                                   accept="image/jpeg,image/jpg,image/png"
                                   multiple
                                   data-max-images="<?php echo $maxGalleryImages - $existingGalleryCount; ?>"
                                   onchange="previewGalleryImages(this)">
                        </label>
                        <small class="form-text">Còn lại: <span id="remaining-images"><?php echo $maxGalleryImages - $existingGalleryCount; ?></span> ảnh có thể tải lên</small>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Đã đạt giới hạn tối đa <?php echo $maxGalleryImages; ?> ảnh phụ.
                        </div>
                    <?php endif; ?>
                    
                    <!-- Preview container for new images -->
                    <div id="gallery-preview" class="gallery-preview-container"></div>
                    
                    <!-- Hidden field to track deleted images -->
                    <input type="hidden" id="deleted_gallery" name="deleted_gallery" value="">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
                <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=index" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Function to remove the main image
function removeMainImage(button) {
    if (confirm('Bạn có chắc chắn muốn xóa ảnh chính này?')) {
        const previewContainer = document.getElementById('main-image-preview');
        const fileInput = document.getElementById('main_image');
        
        // Add a hidden input to indicate the main image should be removed
        let removeInput = document.createElement('input');
        removeInput.type = 'hidden';
        removeInput.name = 'remove_main_image';
        removeInput.value = '1';
        document.querySelector('form').appendChild(removeInput);
        
        // Clear the preview and reset the file input
        previewContainer.innerHTML = '';
        fileInput.value = '';
        
        // Remove the keep_main_image hidden input if it exists
        const keepInput = document.querySelector('input[name="keep_main_image"]');
        if (keepInput) {
            keepInput.remove();
        }
    }
    return false;
}

// Function to preview the main image
function previewMainImage(input) {
    const previewContainer = document.getElementById('main-image-preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Clear existing content
            previewContainer.innerHTML = '';
            
            // Create new preview
            const container = document.createElement('div');
            container.className = 'main-image-container';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Ảnh chính';
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-main-image';
            removeBtn.title = 'Xóa ảnh';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = function() {
                removeMainImage(this);
            };
            
            container.appendChild(img);
            container.appendChild(removeBtn);
            previewContainer.appendChild(container);
            
            // Remove the remove_main_image input if it exists
            const removeInput = document.querySelector('input[name="remove_main_image"]');
            if (removeInput) {
                removeInput.remove();
            }
            
            // Add keep_main_image input
            const keepInput = document.createElement('input');
            keepInput.type = 'hidden';
            keepInput.name = 'keep_main_image';
            keepInput.value = '1';
            container.appendChild(keepInput);
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Function to remove a gallery image
function removeGalleryImage(button) {
    if (confirm('Bạn có chắc chắn muốn xóa ảnh phụ này?')) {
        const item = button.closest('.gallery-image-item') || button.closest('.gallery-preview-item');
        if (item) {
            const imageId = item.getAttribute('data-image-id');
            if (imageId) {
                // Get or create the deleted_gallery input
                let deletedInput = document.getElementById('deleted_gallery');
                if (!deletedInput) {
                    deletedInput = document.createElement('input');
                    deletedInput.type = 'hidden';
                    deletedInput.id = 'deleted_gallery';
                    deletedInput.name = 'deleted_gallery';
                    document.querySelector('form').appendChild(deletedInput);
                }
                
                // Add the image ID to the deleted_gallery input
                const deletedIds = deletedInput.value ? deletedInput.value.split(',').filter(id => id !== '') : [];
                if (!deletedIds.includes(imageId)) {
                    deletedIds.push(imageId);
                    deletedInput.value = deletedIds.join(',');
                }
                
                // Remove the keep_gallery hidden input if it exists
                const keepInputs = document.querySelectorAll('input[name="keep_gallery[]"]');
                keepInputs.forEach(input => {
                    if (input.value === imageId) {
                        input.remove();
                    }
                });
            }
            
            // Add animation class and remove after animation completes
            item.classList.add('gallery-image-item-removing');
            setTimeout(() => {
                item.remove();
                checkGalleryUploadArea();
            }, 300);
        }
    }
    return false;
}
            
// Function to update the gallery upload area visibility
    const maxImages = <?php echo $maxGalleryImages; ?>;
    const currentImages = document.querySelectorAll('.gallery-image-item').length;
    const remaining = maxImages - currentImages;
    
    const remainingEl = document.getElementById('remaining-images');
    if (remainingEl) remainingEl.textContent = remaining > 0 ? remaining : 0;
    
    const uploadArea = document.getElementById('gallery-upload-area');
    const fileInput = document.getElementById('gallery_images');
    
    if (remaining > 0) {
        if (uploadArea) uploadArea.style.display = 'block';
        if (fileInput) {
            fileInput.disabled = false;
            fileInput.dataset.maxImages = remaining;
        }
    } else {
        if (uploadArea) uploadArea.style.display = 'none';
        if (fileInput) fileInput.disabled = true;
    }


// Function to preview gallery images before upload
function previewGalleryImages(input) {
    const previewContainer = document.getElementById('gallery-preview');
    const maxImages = parseInt(input.dataset.maxImages) || 0;
    const currentPreviews = document.querySelectorAll('#gallery-preview .gallery-preview-item').length;
    
    if (input.files) {
        const files = Array.from(input.files);
        const remainingSlots = maxImages - currentPreviews;
        
        if (files.length > remainingSlots) {
            alert(`Chỉ có thể tải lên tối đa ${remainingSlots} ảnh. ${files.length - remainingSlots} ảnh đã bị bỏ qua.`);
            files.length = remainingSlots; // Truncate the files array
        }
        
        files.forEach(file => {
            if (!file.type.match('image.*')) {
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'gallery-preview-item';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-preview';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.title = 'Xóa ảnh';
                removeBtn.onclick = function() {
                    previewItem.remove();
                    checkGalleryUploadArea();
                    // Reset file input to allow re-uploading the same file
                    input.value = '';
                };
                
                previewItem.appendChild(img);
                previewItem.appendChild(removeBtn);
                previewContainer.appendChild(previewItem);
                
                checkGalleryUploadArea();
            };
            
            reader.readAsDataURL(file);
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    checkGalleryUploadArea();
    
    // Add event listeners to existing remove buttons for gallery images
    document.querySelectorAll('.remove-gallery-image').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            removeGalleryImage(this);
        });
    });
    
    // Add event listener for the main image remove button
    const removeMainImageBtn = document.querySelector('.remove-main-image');
    if (removeMainImageBtn) {
        removeMainImageBtn.addEventListener('click', function(e) {
            e.preventDefault();
            removeMainImage(this);
        });
    }
    
    // Handle form submission
    const form = document.querySelector('.product-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Form validation is handled by validateProductForm() function
            return true;
        });
    }
});
            </script>
