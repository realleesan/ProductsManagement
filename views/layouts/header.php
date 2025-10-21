<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Quản Lý Sản Phẩm Mỹ Phẩm</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-spa"></i>
                    <span>Quản Lý Mỹ Phẩm</span>
                </div>
                <nav class="nav">
                    <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=dashboard" 
                       class="nav-link <?php echo (isset($active_page) && $active_page == 'dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/controllers/ProductController.php?action=index" 
                       class="nav-link <?php echo (isset($active_page) && $active_page == 'products') ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i> Sản phẩm
                    </a>
                    <a href="<?php echo BASE_URL; ?>/controllers/InventoryController.php?action=index" 
                       class="nav-link <?php echo (isset($active_page) && $active_page == 'inventory') ? 'active' : ''; ?>">
                        <i class="fas fa-warehouse"></i> Kho
                    </a>
                    <a href="<?php echo BASE_URL; ?>/controllers/OrderController.php?action=index" 
                       class="nav-link <?php echo (isset($active_page) && $active_page == 'orders') ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i> Đơn hàng
                    </a>
                </nav>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['user']['fullname']; ?></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php 
            // Hiển thị thông báo flash message
            $flash = getFlashMessage();
            if ($flash): 
            ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php 
                    echo $flash['type'] == 'success' ? 'check-circle' : 
                        ($flash['type'] == 'error' ? 'exclamation-circle' : 
                        ($flash['type'] == 'warning' ? 'exclamation-triangle' : 'info-circle')); 
                ?>"></i>
                <span><?php echo $flash['message']; ?></span>
                <button class="alert-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>
