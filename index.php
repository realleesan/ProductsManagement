<?php
/**
 * File index.php - Trang chủ của hệ thống
 * Redirect đến Dashboard
 */

// Redirect đến dashboard
header('Location: controllers/ProductController.php?action=dashboard');
exit();
