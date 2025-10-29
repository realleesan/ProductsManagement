<?php
/**
 * Form thêm khách hàng nhanh
 * Được sử dụng trong quá trình tạo đơn hàng
 */

require_once __DIR__ . '/../../config/config.php';

// Lấy URL redirect nếu có
$redirect_url = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : BASE_URL . '?controller=OrderController&action=create';
$form_data = $_SESSION['form_data'] ?? [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm khách hàng mới - Hệ thống quản lý bán hàng</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .btn-back {
            position: absolute;
            top: 1rem;
            left: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Nút quay lại -->
                <a href="<?= htmlspecialchars($redirect_url) ?>" class="btn btn-outline-secondary btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại tạo đơn hàng
                </a>
                
                <div class="form-container">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-user-plus me-2"></i>Thêm khách hàng mới
                            </h4>
                            <small>Thông tin khách hàng sẽ được thêm vào hệ thống để sử dụng cho đơn hàng</small>
                        </div>
                        <div class="card-body">
                            <!-- Hiển thị thông báo -->
                            <?php if (isset($_SESSION['flash_message'])): ?>
                                <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                                    <?= htmlspecialchars($_SESSION['flash_message']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                            <?php endif; ?>
                            
                            <form method="POST" action="/quanlysanpham/?controller=CustomerController&action=store" id="customerForm">
                                <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($redirect_url) ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fullname" class="form-label">
                                                Họ và tên <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="fullname" 
                                                   name="fullname" 
                                                   value="<?= htmlspecialchars($form_data['fullname'] ?? '') ?>"
                                                   required>
                                            <div class="form-text">Nhập họ tên đầy đủ của khách hàng</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">
                                                Số điện thoại <span class="text-danger">*</span>
                                            </label>
                                            <input type="tel" 
                                                   class="form-control" 
                                                   id="phone" 
                                                   name="phone" 
                                                   value="<?= htmlspecialchars($form_data['phone'] ?? '') ?>"
                                                   pattern="[0-9]{10,11}"
                                                   required>
                                            <div class="form-text">Nhập số điện thoại 10-11 chữ số</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?= htmlspecialchars($form_data['email'] ?? '') ?>">
                                            <div class="form-text">Email khách hàng (không bắt buộc)</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Địa chỉ</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="address" 
                                                   name="address" 
                                                   value="<?= htmlspecialchars($form_data['address'] ?? '') ?>">
                                            <div class="form-text">Địa chỉ khách hàng (không bắt buộc)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="<?= htmlspecialchars($redirect_url) ?>" class="btn btn-outline-secondary me-md-2">
                                        <i class="fas fa-times me-2"></i>Hủy
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Thêm khách hàng
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Hướng dẫn -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-info-circle text-info me-2"></i>Hướng dẫn
                            </h6>
                            <ul class="mb-0 small text-muted">
                                <li>Sau khi thêm khách hàng thành công, bạn sẽ được chuyển về trang tạo đơn hàng</li>
                                <li>Khách hàng mới sẽ xuất hiện trong danh sách chọn khách hàng</li>
                                <li>Thông tin khách hàng có thể được chỉnh sửa sau này</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validate form
        document.getElementById('customerForm').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            const email = document.getElementById('email').value;
            
            // Validate số điện thoại
            if (!/^[0-9]{10,11}$/.test(phone)) {
                e.preventDefault();
                alert('Số điện thoại phải có 10-11 chữ số');
                document.getElementById('phone').focus();
                return false;
            }
            
            // Validate email nếu có
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Email không hợp lệ');
                document.getElementById('email').focus();
                return false;
            }
        });
        
        // Auto-format số điện thoại
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
