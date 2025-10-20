<?php 
$page_title = 'Dashboard';
$active_page = 'dashboard';
require_once __DIR__ . '/layouts/header.php'; 
?>

<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Dashboard - Tổng quan</h1>
</div>

<!-- Thống kê tổng quan -->
<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($statistics['total_products']); ?></h3>
            <p>Tổng sản phẩm</p>
        </div>
    </div>
    
    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($statistics['active_products']); ?></h3>
            <p>Sản phẩm Active</p>
        </div>
    </div>
    
    <div class="stat-card stat-warning">
        <div class="stat-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($statistics['out_of_stock']); ?></h3>
            <p>Hết hàng</p>
        </div>
    </div>
    
    <div class="stat-card stat-danger">
        <div class="stat-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($statistics['expired_products']); ?></h3>
            <p>Đã hết hạn</p>
        </div>
    </div>
    
    <div class="stat-card stat-info">
        <div class="stat-icon">
            <i class="fas fa-warehouse"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($statistics['total_stock']); ?></h3>
            <p>Tổng tồn kho</p>
        </div>
    </div>
    
    <div class="stat-card stat-secondary">
        <div class="stat-icon">
            <i class="fas fa-ban"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($statistics['disabled_products']); ?></h3>
            <p>Đã vô hiệu hóa</p>
        </div>
    </div>
</div>

<!-- Cảnh báo -->
<div class="dashboard-grid">
    <!-- Sản phẩm sắp hết hạn -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2><i class="fas fa-clock"></i> Sản phẩm sắp hết hạn (60 ngày)</h2>
        </div>
        <div class="card-body">
            <?php if (count($expiring_products) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã SP</th>
                            <th>Tên sản phẩm</th>
                            <th>Hạn sử dụng</th>
                            <th>Còn lại</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expiring_products as $product): ?>
                        <tr>
                            <td><?php echo $product['product_code']; ?></td>
                            <td><?php echo $product['product_name']; ?></td>
                            <td><?php echo formatDate($product['expiry_date']); ?></td>
                            <td>
                                <span class="badge badge-warning">
                                    <?php echo $product['days_until_expiry']; ?> ngày
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">Không có sản phẩm nào sắp hết hạn</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Sản phẩm tồn kho thấp -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2><i class="fas fa-boxes"></i> Sản phẩm tồn kho thấp (< 20)</h2>
        </div>
        <div class="card-body">
            <?php if (count($low_stock_products) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã SP</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Tồn kho</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock_products as $product): ?>
                        <tr>
                            <td><?php echo $product['product_code']; ?></td>
                            <td><?php echo $product['product_name']; ?></td>
                            <td><?php echo $product['category_name']; ?></td>
                            <td>
                                <span class="badge badge-danger">
                                    <?php echo $product['stock_quantity']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">Không có sản phẩm nào tồn kho thấp</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="action-buttons">
    <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=index" class="btn btn-primary">
        <i class="fas fa-list"></i> Xem tất cả sản phẩm
    </a>
    <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=create" class="btn btn-success">
        <i class="fas fa-plus"></i> Thêm sản phẩm mới
    </a>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
