<?php 
$page_title = 'Danh sách sản phẩm';
$active_page = 'products';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="page-header">
    <h1><i class="fas fa-box"></i> Quản lý sản phẩm</h1>
    <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=create" class="btn btn-success">
        <i class="fas fa-plus"></i> Thêm sản phẩm mới
    </a>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="filter-section">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="action" value="index">
        
        <div class="filter-group">
            <input type="text" 
                   name="search" 
                   placeholder="Tìm kiếm theo tên sản phẩm..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                   class="form-control">
        </div>
        
        <div class="filter-group">
            <select name="category_id" class="form-control">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['category_id']; ?>" 
                        <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                    <?php echo $cat['category_name']; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <select name="status" class="form-control">
                <option value="">Tất cả trạng thái</option>
                <option value="Active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Disabled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Disabled') ? 'selected' : ''; ?>>Disabled</option>
                <option value="Out of stock" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Out of stock') ? 'selected' : ''; ?>>Out of stock</option>
                <option value="Expired" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Expired') ? 'selected' : ''; ?>>Expired</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
        
        <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=index" class="btn btn-secondary">
            <i class="fas fa-redo"></i> Làm mới
        </a>
    </form>
</div>

<!-- Thống kê nhanh -->
<div class="quick-stats">
    <div class="stat-item">
        <strong><?php echo number_format($statistics['total_products']); ?></strong>
        <span>Tổng SP</span>
    </div>
    <div class="stat-item stat-success">
        <strong><?php echo number_format($statistics['active_products']); ?></strong>
        <span>Active</span>
    </div>
    <div class="stat-item stat-warning">
        <strong><?php echo number_format($statistics['out_of_stock']); ?></strong>
        <span>Hết hàng</span>
    </div>
    <div class="stat-item stat-danger">
        <strong><?php echo number_format($statistics['expired_products']); ?></strong>
        <span>Hết hạn</span>
    </div>
</div>

<!-- Bảng danh sách sản phẩm -->
<div class="card">
    <div class="card-body">
        <?php if (count($products) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Hạn SD</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if (!empty($product['main_image'])): ?>
                            <img src="<?php echo UPLOAD_URL . $product['main_image']; ?>" 
                                 alt="<?php echo $product['product_name']; ?>"
                                 class="product-thumbnail">
                            <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-image"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo $product['product_code']; ?></strong></td>
                        <td><?php echo $product['product_name']; ?></td>
                        <td><?php echo $product['category_name']; ?></td>
                        <td><?php echo formatCurrency($product['price']); ?></td>
                        <td>
                            <span class="badge <?php echo $product['stock_quantity'] == 0 ? 'badge-danger' : ($product['stock_quantity'] < 20 ? 'badge-warning' : 'badge-success'); ?>">
                                <?php echo $product['stock_quantity']; ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($product['expiry_date']); ?></td>
                        <td>
                            <?php 
                            $status_class = '';
                            switch($product['status']) {
                                case 'Active': $status_class = 'badge-success'; break;
                                case 'Disabled': $status_class = 'badge-secondary'; break;
                                case 'Out of stock': $status_class = 'badge-warning'; break;
                                case 'Expired': $status_class = 'badge-danger'; break;
                            }
                            ?>
                            <span class="badge <?php echo $status_class; ?>">
                                <?php echo $product['status']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=view&id=<?php echo $product['product_id']; ?>" 
                                   class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=edit&id=<?php echo $product['product_id']; ?>" 
                                   class="btn btn-sm btn-warning" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=delete&id=<?php echo $product['product_id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Xóa"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?action=index&page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo $category_id; ?>&status=<?php echo $status; ?>" 
               class="btn btn-sm btn-secondary">
                <i class="fas fa-chevron-left"></i> Trước
            </a>
            <?php endif; ?>
            
            <span class="page-info">
                Trang <?php echo $page; ?> / <?php echo $total_pages; ?>
            </span>
            
            <?php if ($page < $total_pages): ?>
            <a href="?action=index&page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo $category_id; ?>&status=<?php echo $status; ?>" 
               class="btn btn-sm btn-secondary">
                Sau <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <p>Không tìm thấy sản phẩm nào</p>
            <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm sản phẩm đầu tiên
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
