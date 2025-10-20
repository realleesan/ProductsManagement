<?php
/**
 * Controller xử lý các thao tác với sản phẩm
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController {
    private $db;

    /** @var Product */
    private $product;

    /** @var Category */
    private $category;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
        $this->category = new Category($this->db);
    }
    
    /**
     * Hiển thị danh sách sản phẩm
     */
    public function index() {
        // Lấy tham số tìm kiếm và lọc
        $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
        $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : '';
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Tính offset cho phân trang
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        // Lấy danh sách sản phẩm
        $stmt = $this->product->getAll($search, $category_id, $status, $limit, $offset);
        $products = $stmt->fetchAll();
        
        // Đếm tổng số sản phẩm
        $total_products = $this->product->countAll($search, $category_id, $status);
        $total_pages = ceil($total_products / $limit);
        
        // Lấy danh sách danh mục cho filter
        $categories = $this->category->getAll()->fetchAll();
        
        // Lấy thống kê
        $statistics = $this->product->getStatistics();
        
        // Load view
        require_once __DIR__ . '/../views/products/index.php';
    }
    
    /**
     * Hiển thị form thêm sản phẩm
     */
    public function create() {
        $categories = $this->category->getActive()->fetchAll();
        require_once __DIR__ . '/../views/products/create.php';
    }
    
    /**
     * Xử lý thêm sản phẩm mới
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/controllers/ProductController.php?action=index');
            return;
        }
        
        try {
            // Lấy dữ liệu từ form
            $this->product->product_code = strtoupper(sanitizeInput($_POST['product_code']));
            $this->product->product_name = sanitizeInput($_POST['product_name']);
            $this->product->description = sanitizeInput($_POST['description']);
            $this->product->price = (float)$_POST['price'];
            $this->product->stock_quantity = (int)$_POST['stock_quantity'];
            $this->product->category_id = (int)$_POST['category_id'];
            $this->product->manufacture_date = $_POST['manufacture_date'];
            $this->product->expiry_date = $_POST['expiry_date'];
            $this->product->status = sanitizeInput($_POST['status']);
            $this->product->created_by = $_SESSION['user']['username'];
            
            // Kiểm tra mã sản phẩm đã tồn tại
            if ($this->product->codeExists($this->product->product_code)) {
                setFlashMessage('error', 'Mã sản phẩm đã tồn tại trong hệ thống');
                $_SESSION['form_data'] = $_POST;
                redirect('/controllers/ProductController.php?action=create');
                return;
            }
            
            // Validate dữ liệu
            $errors = $this->product->validate();
            if (!empty($errors)) {
                setFlashMessage('error', implode('<br>', $errors));
                $_SESSION['form_data'] = $_POST;
                redirect('/controllers/ProductController.php?action=create');
                return;
            }
            
            $uploadResult = $this->handleImageUpload();
            $this->product->main_image = $uploadResult['main_image'];
            $this->product->gallery_images = $uploadResult['new_gallery_images'];

            if (empty($this->product->main_image)) {
                setFlashMessage('error', 'Vui lòng chọn ảnh chính cho sản phẩm');
                $_SESSION['form_data'] = $_POST;
                redirect('/controllers/ProductController.php?action=create');
                return;
            }
            
            // Thêm sản phẩm
            if ($this->product->create()) {
                $message = 'Thêm sản phẩm thành công';
                if ($uploadResult['skipped_gallery'] > 0) {
                    $message .= ' (' . $uploadResult['skipped_gallery'] . ' ảnh phụ vượt quá giới hạn và không được tải lên)';
                }
                setFlashMessage('success', $message);
                redirect('/controllers/ProductController.php?action=index');
            } else {
                setFlashMessage('error', 'Có lỗi xảy ra khi thêm sản phẩm');
                $_SESSION['form_data'] = $_POST;
                redirect('/controllers/ProductController.php?action=create');
            }
            
        } catch (Exception $e) {
            setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            redirect('/controllers/ProductController.php?action=create');
        }
    }
    
    /**
     * Hiển thị form sửa sản phẩm
     */
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$this->product->getById($id)) {
            setFlashMessage('error', 'Không tìm thấy sản phẩm');
            redirect('/controllers/ProductController.php?action=index');
            return;
        }

        $categories = $this->category->getActive()->fetchAll();
        $product = clone $this->product;
        require_once __DIR__ . '/../views/products/edit.php';
    }
    
    /**
     * Xử lý cập nhật sản phẩm
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/controllers/ProductController.php?action=index');
            return;
        }
        
        try {
            $id = (int)$_POST['product_id'];
            
            // Lấy thông tin sản phẩm hiện tại
            if (!$this->product->getById($id)) {
                setFlashMessage('error', 'Không tìm thấy sản phẩm');
                redirect('/controllers/ProductController.php?action=index');
                return;
            }
            
            // Lưu ảnh hiện tại
            $old_main_image = $this->product->main_image;
            $current_gallery_images = $this->product->gallery_images;
            
            // Cập nhật dữ liệu
            $this->product->product_code = strtoupper(sanitizeInput($_POST['product_code']));
            $this->product->product_name = sanitizeInput($_POST['product_name']);
            $this->product->description = sanitizeInput($_POST['description']);
            $this->product->price = (float)$_POST['price'];
            $this->product->stock_quantity = (int)$_POST['stock_quantity'];
            $this->product->category_id = (int)$_POST['category_id'];
            $this->product->manufacture_date = $_POST['manufacture_date'];
            $this->product->expiry_date = $_POST['expiry_date'];
            $this->product->status = sanitizeInput($_POST['status']);
            $this->product->updated_by = $_SESSION['user']['username'];
            
            // Kiểm tra mã sản phẩm
            if ($this->product->codeExists($this->product->product_code, $id)) {
                setFlashMessage('error', 'Mã sản phẩm đã tồn tại trong hệ thống');
                redirect('/controllers/ProductController.php?action=edit&id=' . $id);
                return;
            }
            
            // Validate
            $errors = $this->product->validate();
            if (!empty($errors)) {
                setFlashMessage('error', implode('<br>', $errors));
                redirect('/controllers/ProductController.php?action=edit&id=' . $id);
                return;
            }
            
            // Get deleted gallery image IDs from hidden input
            $deleted_gallery_ids = [];
            if (!empty($_POST['deleted_gallery']) && is_string($_POST['deleted_gallery'])) {
                $deleted_gallery_ids = array_filter(
                    array_map('intval', explode(',', $_POST['deleted_gallery'])),
                    function($id) { return $id > 0; }
                );
            }
            
            // Process image uploads
            $uploadResult = $this->handleImageUpload(
                $old_main_image, 
                $current_gallery_images,
                $deleted_gallery_ids
            );
            
            // Update main image if a new one was uploaded
            if (!empty($uploadResult['main_image'])) {
                $this->product->main_image = $uploadResult['main_image'];
            } else if (empty($this->product->main_image)) {
                // If no main image is set and no new one was uploaded, keep the old one
                $this->product->main_image = $old_main_image;
            }

            // Check if we have at least one image (main or gallery)
            $hasMainImage = !empty($this->product->main_image);
            $hasGalleryImages = !empty($this->product->gallery_images) && 
                              (count($this->product->gallery_images) > count($deleted_gallery_ids));
            
            if (!$hasMainImage && !$hasGalleryImages) {
                setFlashMessage('error', 'Sản phẩm phải có ít nhất một ảnh (ảnh chính hoặc ảnh phụ)');
                redirect('/controllers/ProductController.php?action=edit&id=' . $id);
                return;
            }
            
            // If main image is removed but there are gallery images, use the first gallery image as main
            if (!$hasMainImage && $hasGalleryImages) {
                foreach ($this->product->gallery_images as $gallery_image) {
                    if (!in_array((int)$gallery_image['image_id'], $deleted_gallery_ids, true)) {
                        $this->product->main_image = $gallery_image['image_path'];
                        // Remove this image from gallery since it's now the main image
                        $deleted_gallery_ids[] = (int)$gallery_image['image_id'];
                        break;
                    }
                }
            }
            
            // Update the product
            if ($this->product->update()) {
                // Xóa ảnh gallery được chọn
                if (!empty($deleted_gallery_ids)) {
                    $files_to_delete = [];
                    foreach ($current_gallery_images as $gallery_image) {
                        if (in_array((int)$gallery_image['image_id'], $deleted_gallery_ids, true)) {
                            $files_to_delete[] = $gallery_image['image_path'];
                        }
                    }
                    
                    // Delete from database
                    if (!empty($files_to_delete)) {
                        $this->product->deleteGalleryImages($id, $deleted_gallery_ids);
                        
                        // Delete the actual files
                        foreach ($files_to_delete as $file) {
                            $this->deleteImageFile($file);
                        }
                    }
                }

                // Thêm ảnh gallery mới
                if (!empty($uploadResult['new_gallery_images'])) {
                    $this->product->addGalleryImages($id, $uploadResult['new_gallery_images']);
                }

                // Xóa ảnh chính cũ nếu đã thay
                if (!empty($uploadResult['replaced_main_image']) && !empty($old_main_image)) {
                    $this->deleteImageFile($old_main_image);
                }

                $message = 'Cập nhật sản phẩm thành công';
                if (isset($uploadResult['skipped_gallery']) && $uploadResult['skipped_gallery'] > 0) {
                    $message .= ' (' . $uploadResult['skipped_gallery'] . ' ảnh phụ vượt quá giới hạn và không được tải lên)';
                }

                setFlashMessage('success', $message);
                redirect('/controllers/ProductController.php?action=index');
            } else {
                // Rollback: Delete any newly uploaded files if update failed
                if (!empty($uploadResult['main_image']) && $uploadResult['replaced_main_image']) {
                    $this->deleteImageFile($uploadResult['main_image']);
                }
                
                if (!empty($uploadResult['new_gallery_images'])) {
                    foreach ($uploadResult['new_gallery_images'] as $image) {
                        $this->deleteImageFile($image);
                    }
                }
                
                setFlashMessage('error', 'Có lỗi xảy ra khi cập nhật sản phẩm');
                redirect('/controllers/ProductController.php?action=edit&id=' . $id);
            }
            
        } catch (Exception $e) {
            setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
            redirect('/controllers/ProductController.php?action=edit&id=' . $id);
        }
    }
    
    /**
     * Xóa sản phẩm
     */
    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$this->product->getById($id)) {
            setFlashMessage('error', 'Không tìm thấy sản phẩm');
            redirect('/controllers/ProductController.php?action=index');
            return;
        }
        
        // Xóa ảnh
        $this->deleteProductImages($id);
        
        // Xóa sản phẩm
        $this->product->product_id = $id;
        if ($this->product->delete()) {
            setFlashMessage('success', 'Xóa sản phẩm thành công');
        } else {
            setFlashMessage('error', 'Có lỗi xảy ra khi xóa sản phẩm');
        }
        
        redirect('/controllers/ProductController.php?action=index');
    }
    
    /**
     * Xem chi tiết sản phẩm
     */
    public function view() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$this->product->getById($id)) {
            setFlashMessage('error', 'Không tìm thấy sản phẩm');
            redirect('/controllers/ProductController.php?action=index');
            return;
        }

        // Tạo bản sao dữ liệu sản phẩm để truyền cho view
        $product = clone $this->product;

        require_once __DIR__ . '/../views/products/view.php';
    }
    
    /**
     * Cập nhật trạng thái sản phẩm
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : '';
        
        if ($this->product->updateStatus($id, $status)) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }
    
    /**
     * Xử lý upload ảnh
     */
    private function handleImageUpload($existingMainImage = null, $existingGallery = [], $deletedGalleryIds = []) {
        $result = [
            'main_image' => $existingMainImage,
            'replaced_main_image' => false,
            'new_gallery_images' => [],
            'skipped_gallery' => 0
        ];

        // Handle main image removal
        if (isset($_POST['remove_main_image']) && $_POST['remove_main_image'] == '1') {
            if (!empty($existingMainImage)) {
                $this->deleteImageFile($existingMainImage);
            }
            $result['main_image'] = '';
            $result['replaced_main_image'] = true;
        }
        // Handle main image upload
        else if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->processUploadedFile(
                $_FILES['main_image']['tmp_name'],
                $_FILES['main_image']['type'],
                $_FILES['main_image']['size'],
                $_FILES['main_image']['name']
            );

            if ($uploaded) {
                // Delete old main image if it exists and is being replaced
                if (!empty($existingMainImage)) {
                    $this->deleteImageFile($existingMainImage);
                }
                $result['main_image'] = $uploaded;
                $result['replaced_main_image'] = true;
            }
        }

        // Calculate current gallery count after deletions
        $currentGalleryCount = is_array($existingGallery) ? count($existingGallery) : 0;
        if (!empty($deletedGalleryIds) && is_array($existingGallery)) {
            $currentGalleryCount = 0;
            foreach ($existingGallery as $gallery_image) {
                if (!in_array((int)$gallery_image['image_id'], $deletedGalleryIds, true)) {
                    $currentGalleryCount++;
                }
            }
        }

        // Handle gallery images upload
        if (isset($_FILES['gallery_images']) && is_array($_FILES['gallery_images']['name'])) {
            $files = $_FILES['gallery_images'];
            $fileCount = count($files['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                // Skip if no file was uploaded or there was an error
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                // Check if we've reached the maximum number of gallery images
                if (($currentGalleryCount + count($result['new_gallery_images'])) >= MAX_GALLERY_IMAGES) {
                    $result['skipped_gallery'] = ($fileCount - $i);
                    break;
                }

                // Process the uploaded file
                $uploaded = $this->processUploadedFile(
                    $files['tmp_name'][$i],
                    $files['type'][$i],
                    $files['size'][$i],
                    $files['name'][$i]
                );

                if ($uploaded) {
                    $result['new_gallery_images'][] = $uploaded;
                }
            }
        }

        return $result;
    }

    private function processUploadedFile($tmpName, $mimeType, $size, $originalName) {
        if (empty($tmpName) || empty($originalName)) {
            return null;
        }

        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
            return null;
        }

        if ($size > MAX_IMAGE_SIZE) {
            return null;
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return null;
        }

        $filename = uniqid('product_', true) . '.' . $extension;
        $filepath = UPLOAD_PATH . $filename;

        if (move_uploaded_file($tmpName, $filepath)) {
            return $filename;
        }

        return null;
    }

    private function deleteImageFile($filename) {
        if (empty($filename)) {
            return;
        }

        $filepath = UPLOAD_PATH . $filename;
        if (file_exists($filepath)) {
            @unlink($filepath);
        }
    }
    
    /**
     * Xóa ảnh sản phẩm
     */
    private function deleteProductImages($product_id) {
        if ($this->product->getById($product_id)) {
            if (!empty($this->product->main_image)) {
                $this->deleteImageFile($this->product->main_image);
            }

            if (!empty($this->product->gallery_images)) {
                foreach ($this->product->gallery_images as $gallery_image) {
                    $this->deleteImageFile($gallery_image['image_path']);
                }
            }
        }
    }
    
    /**
     * Dashboard - Trang tổng quan
     */
    public function dashboard() {
        // Thống kê tổng quan
        $statistics = $this->product->getStatistics();
        
        // Sản phẩm sắp hết hạn
        $expiring_products = $this->product->getExpiringSoon(60)->fetchAll();
        
        // Sản phẩm tồn kho thấp
        $low_stock_products = $this->product->getLowStock(20)->fetchAll();
        
        require_once __DIR__ . '/../views/dashboard.php';
    }
}

// Xử lý routing
$controller = new ProductController();
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    $controller->index();
}
