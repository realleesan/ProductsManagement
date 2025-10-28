<?php 
$page_title = 'Thêm khách hàng mới';
$active_page = 'customers';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="fas fa-user-plus text-primary me-2"></i> Thêm khách hàng mới</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/controllers/ProductController.php?action=dashboard">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?controller=OrderController&action=create">Tạo đơn hàng</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Thêm khách hàng</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>?controller=OrderController&action=create" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                </div>
            </div>
            
            <form method="post" action="<?= BASE_URL ?>?controller=CustomerController&action=store">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0"><i class="fas fa-user me-2 text-primary"></i> Thông tin khách hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fullname" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Số điện thoại</label>
                                            <input type="tel" class="form-control" id="phone" name="phone">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Địa chỉ</label>
                                            <input type="text" class="form-control" id="address" name="address">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-save me-1"></i> Lưu khách hàng
                                </button>
                                
                                <a href="<?= BASE_URL ?>?controller=OrderController&action=create" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times me-1"></i> Hủy bỏ
                                </a>
                                
                                <div class="text-muted small text-center mt-3">
                                    Sau khi lưu, bạn sẽ được chuyển về trang tạo đơn hàng.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
