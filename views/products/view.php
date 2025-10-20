<?php 
$page_title = 'Chi tiết sản phẩm';
$active_page = 'products';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="page-header">
    <h1><i class="fas fa-info-circle"></i> Chi tiết sản phẩm</h1>
    <div>
        <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=edit&id=<?php echo $product->product_id; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Sửa
        </a>
        <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="product-detail">
    <div class="product-images">
        <div class="main-image">
            <?php if (!empty($product->main_image)): ?>
            <img src="<?php echo UPLOAD_URL . $product->main_image; ?>" alt="<?php echo $product->product_name; ?>">
            <?php else: ?>
            <div class="no-image-large">
                <i class="fas fa-image"></i>
                <p>Không có ảnh</p>
            </div>
            <?php endif; ?>
        </div>

        <?php
        $thumbnails = [];
        if (!empty($product->main_image)) {
            $thumbnails[] = [
                'path' => $product->main_image,
                'label' => 'Ảnh chính'
            ];
        }
        if (!empty($product->gallery_images)) {
            foreach ($product->gallery_images as $gallery_image) {
                $thumbnails[] = [
                    'path' => $gallery_image['image_path'],
                    'label' => 'Ảnh phụ'
                ];
            }
        }
        ?>

        <?php 
        // Filter out non-existent images
        $validThumbnails = array_filter($thumbnails, function($thumb) {
            return !empty($thumb['path']) && file_exists(UPLOAD_PATH . $thumb['path']);
        });
        
        if (!empty($validThumbnails)): 
        ?>
        <div class="thumbnail-images">
            <?php foreach ($validThumbnails as $thumb): ?>
            <img src="<?php echo UPLOAD_URL . $thumb['path']; ?>"
                 alt="<?php echo htmlspecialchars($thumb['label'], ENT_QUOTES, 'UTF-8'); ?>"
                 onclick="changeMainImage('<?php echo htmlspecialchars(UPLOAD_URL . $thumb['path'], ENT_QUOTES, 'UTF-8'); ?>')">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="product-info">
        <h2><?php echo $product->product_name; ?></h2>
        
        <div class="info-group">
            <label>Mã sản phẩm:</label>
            <span class="info-value"><?php echo $product->product_code; ?></span>
        </div>
        
        <div class="info-group">
            <label>Danh mục:</label>
            <span class="info-value"><?php echo $product->category_name; ?></span>
        </div>
        
        <div class="info-group">
            <label>Giá bán:</label>
            <span class="info-value price"><?php echo formatCurrency($product->price); ?></span>
        </div>
        
        <div class="info-group">
            <label>Số lượng tồn kho:</label>
            <span class="info-value">
                <span class="badge <?php echo $product->stock_quantity == 0 ? 'badge-danger' : ($product->stock_quantity < 20 ? 'badge-warning' : 'badge-success'); ?>">
                    <?php echo $product->stock_quantity; ?> sản phẩm
                </span>
            </span>
        </div>
        
        <div class="info-group">
            <label>Trạng thái:</label>
            <span class="info-value">
                <?php 
                $status_class = '';
                switch($product->status) {
                    case 'Active': $status_class = 'badge-success'; break;
                    case 'Disabled': $status_class = 'badge-secondary'; break;
                    case 'Out of stock': $status_class = 'badge-warning'; break;
                    case 'Expired': $status_class = 'badge-danger'; break;
                }
                ?>
                <span class="badge <?php echo $status_class; ?>">
                    <?php echo $product->status; ?>
                </span>
            </span>
        </div>
        
        <div class="info-group">
            <label>Ngày sản xuất:</label>
            <span class="info-value"><?php echo formatDate($product->manufacture_date); ?></span>
        </div>
        
        <div class="info-group">
            <label>Hạn sử dụng:</label>
            <span class="info-value">
                <?php 
                echo formatDate($product->expiry_date);
                $days_left = (strtotime($product->expiry_date) - time()) / (60 * 60 * 24);
                if ($days_left > 0 && $days_left <= 60) {
                    echo ' <span class="badge badge-warning">Còn ' . round($days_left) . ' ngày</span>';
                } elseif ($days_left <= 0) {
                    echo ' <span class="badge badge-danger">Đã hết hạn</span>';
                }
                ?>
            </span>
        </div>
        
        <?php if (!empty($product->description)): ?>
        <div class="info-group">
            <label>Mô tả:</label>
            <p class="description"><?php echo nl2br(htmlspecialchars($product->description)); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="info-group">
            <label>Ngày tạo:</label>
            <span class="info-value"><?php echo formatDateTime($product->created_at); ?></span>
        </div>
        
        <?php if (!empty($product->created_by)): ?>
        <div class="info-group">
            <label>Người tạo:</label>
            <span class="info-value"><?php echo $product->created_by; ?></span>
        </div>
        <?php endif; ?>
        
        <div class="info-group">
            <label>Cập nhật lần cuối:</label>
            <span class="info-value"><?php echo formatDateTime($product->updated_at); ?></span>
        </div>
        
        <?php if (!empty($product->updated_by)): ?>
        <div class="info-group">
            <label>Người cập nhật:</label>
            <span class="info-value"><?php echo $product->updated_by; ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="action-buttons">
    <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=edit&id=<?php echo $product->product_id; ?>" class="btn btn-warning">
        <i class="fas fa-edit"></i> Sửa sản phẩm
    </a>
    <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=delete&id=<?php echo $product->product_id; ?>" 
       class="btn btn-danger"
       onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
        <i class="fas fa-trash"></i> Xóa sản phẩm
    </a>
    <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=index" class="btn btn-secondary">
        <i class="fas fa-list"></i> Danh sách sản phẩm
    </a>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
