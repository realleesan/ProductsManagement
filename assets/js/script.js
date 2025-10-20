/**
 * JavaScript cho hệ thống quản lý sản phẩm mỹ phẩm
 */

// Đợi DOM load xong
document.addEventListener('DOMContentLoaded', function() {
    
    // Tự động ẩn alert sau 5 giây
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Đếm ký tự mô tả
    const descriptionTextarea = document.getElementById('description');
    if (descriptionTextarea) {
        const charCount = document.getElementById('char-count');
        
        descriptionTextarea.addEventListener('input', function() {
            const remaining = 500 - this.value.length;
            charCount.textContent = remaining;
            
            if (remaining < 50) {
                charCount.style.color = 'var(--danger-color)';
            } else if (remaining < 100) {
                charCount.style.color = 'var(--warning-color)';
            } else {
                charCount.style.color = 'var(--text-muted)';
            }
        });
        
        // Trigger initial count
        descriptionTextarea.dispatchEvent(new Event('input'));
    }
    
    // Validate form trước khi submit
    const productForms = document.querySelectorAll('.product-form');
    productForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateProductForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Validate mã sản phẩm khi nhập
    const productCodeInput = document.getElementById('product_code');
    if (productCodeInput) {
        productCodeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            
            // Validate format
            const pattern = /^SP[A-Z0-9]*$/;
            if (this.value && !pattern.test(this.value)) {
                this.setCustomValidity('Mã sản phẩm phải có định dạng SPXX...X (chỉ chữ in hoa và số)');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
    // Validate ngày sản xuất và hạn sử dụng
    const mfgDateInput = document.getElementById('manufacture_date');
    const expDateInput = document.getElementById('expiry_date');
    
    if (mfgDateInput && expDateInput) {
        function validateDates() {
            if (mfgDateInput.value && expDateInput.value) {
                const mfgDate = new Date(mfgDateInput.value);
                const expDate = new Date(expDateInput.value);
                const diffDays = (expDate - mfgDate) / (1000 * 60 * 60 * 24);
                
                if (diffDays < 30) {
                    expDateInput.setCustomValidity('Hạn sử dụng phải sau ngày sản xuất ít nhất 30 ngày');
                    return false;
                } else {
                    expDateInput.setCustomValidity('');
                    return true;
                }
            }
            return true;
        }
        
        mfgDateInput.addEventListener('change', validateDates);
        expDateInput.addEventListener('change', validateDates);
    }
    
    // Format số tiền khi nhập
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('blur', function() {
            if (this.value) {
                // Round to nearest 1000
                const value = Math.round(parseFloat(this.value) / 1000) * 1000;
                this.value = value;
            }
        });
    }
});

/**
 * Validate và preview ảnh chính
 */
function previewMainImage(input) {
    const preview = document.getElementById('main-image-preview');
    if (!preview) return;

    if (input.files && input.files[0]) {
        if (!validateImageFile(input.files[0], input)) {
            preview.innerHTML = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = '';
    }
}

/**
 * Validate và preview ảnh gallery
 */
function previewGalleryImages(input) {
    const preview = document.getElementById('gallery-preview');
    const uploadArea = input.closest('.gallery-upload-area');
    
    if (!preview || !uploadArea) return;

    // Clear any existing previews
    preview.innerHTML = '';

    if (!input.files || input.files.length === 0) {
        return;
    }

    const maxImages = parseInt(input.dataset.maxImages, 10) || 5;
    const fileList = Array.from(input.files);
    const existingImages = document.querySelectorAll('.gallery-image-item').length;
    
    if (fileList.length + existingImages > maxImages) {
        alert(`Bạn chỉ có thể tải tối đa ${maxImages} ảnh phụ. Hiện đã có ${existingImages} ảnh.`);
        input.value = '';
        return;
    }

    // Hide upload area if we're at max images
    if (existingImages + fileList.length >= maxImages) {
        uploadArea.style.display = 'none';
    }

    fileList.forEach(file => {
        if (!validateImageFile(file, input)) {
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const item = document.createElement('div');
            item.className = 'gallery-image-item';
            item.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <div class="image-actions">
                    <button type="button" class="remove-image" title="Xóa ảnh" onclick="this.closest('.gallery-image-item').remove(); checkGalleryUploadArea();">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <input type="hidden" name="new_gallery_images[]" value="${e.target.result}">
            `;
            preview.appendChild(item);
        };
        reader.readAsDataURL(file);
    });
}

/**
 * Kiểm tra và hiển thị lại nút upload gallery khi cần thiết
 */
function checkGalleryUploadArea() {
    const uploadArea = document.querySelector('.gallery-upload-area');
    if (!uploadArea) return;
    
    const maxImages = parseInt(document.querySelector('#gallery_images')?.dataset.maxImages, 10) || 5;
    const existingImages = document.querySelectorAll('.gallery-image-item').length;
    
    if (existingImages < maxImages) {
        uploadArea.style.display = 'block';
    }
}

/**
 * Validate file ảnh theo định dạng/kích thước
 */
function validateImageFile(file, inputElement) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
        alert('Chỉ chấp nhận file ảnh định dạng JPG, JPEG, PNG');
        if (inputElement) inputElement.value = '';
        return false;
    }

    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('Kích thước file không được vượt quá 5MB');
        if (inputElement) inputElement.value = '';
        return false;
    }

    return true;
}

/**
 * Thay đổi ảnh chính trong trang chi tiết
 */
function changeMainImage(src) {
    const mainImage = document.querySelector('.main-image img');
    if (mainImage) {
        mainImage.src = src;
        
        // Add animation
        mainImage.style.opacity = '0';
        setTimeout(() => {
            mainImage.style.transition = 'opacity 0.3s';
            mainImage.style.opacity = '1';
        }, 50);
    }
}

/**
 * Validate form sản phẩm
 */
function validateProductForm(form) {
    let isValid = true;
    const errors = [];
    
    // Validate mã sản phẩm
    const productCode = form.querySelector('#product_code');
    if (productCode) {
        const pattern = /^SP[A-Z0-9]+$/;
        if (!pattern.test(productCode.value)) {
            errors.push('Mã sản phẩm phải có định dạng SPXX...X');
            isValid = false;
        }
    }
    
    // Validate tên sản phẩm
    const productName = form.querySelector('#product_name');
    if (productName) {
        const length = productName.value.trim().length;
        if (length < 5 || length > 150) {
            errors.push('Tên sản phẩm phải từ 5-150 ký tự');
            isValid = false;
        }
    }
    
    // Validate giá
    const price = form.querySelector('#price');
    if (price) {
        const value = parseFloat(price.value);
        if (value < 1000 || value > 1000000000) {
            errors.push('Giá sản phẩm phải từ 1.000 đến 1.000.000.000 VNĐ');
            isValid = false;
        }
    }
    
    // Validate tồn kho
    const stock = form.querySelector('#stock_quantity');
    if (stock) {
        const value = parseInt(stock.value);
        if (value < 0) {
            errors.push('Số lượng tồn kho phải >= 0');
            isValid = false;
        }
    }
    
    // Validate ngày
    const mfgDate = form.querySelector('#manufacture_date');
    const expDate = form.querySelector('#expiry_date');
    if (mfgDate && expDate && mfgDate.value && expDate.value) {
        const mfg = new Date(mfgDate.value);
        const exp = new Date(expDate.value);
        const diffDays = (exp - mfg) / (1000 * 60 * 60 * 24);
        
        if (diffDays < 30) {
            errors.push('Hạn sử dụng phải sau ngày sản xuất ít nhất 30 ngày');
            isValid = false;
        }
    }
    
    // Validate mô tả
    const description = form.querySelector('#description');
    if (description && description.value.length > 500) {
        errors.push('Mô tả không được vượt quá 500 ký tự');
        isValid = false;
    }
    
    // Hiển thị lỗi nếu có
    if (!isValid) {
        alert('Vui lòng kiểm tra lại:\n\n' + errors.join('\n'));
    }
    
    return isValid;
}

/**
 * Confirm xóa
 */
function confirmDelete(message) {
    return confirm(message || 'Bạn có chắc chắn muốn xóa?');
}

/**
 * Format số thành tiền tệ VNĐ
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

/**
 * Format ngày tháng
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

/**
 * Debounce function cho search
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Show loading spinner
 */
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loading-overlay';
    loading.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                    background: rgba(0,0,0,0.5); display: flex; align-items: center; 
                    justify-content: center; z-index: 9999;">
            <div style="background: white; padding: 2rem; border-radius: 0.5rem; 
                        text-align: center;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                <p style="margin-top: 1rem;">Đang xử lý...</p>
            </div>
        </div>
    `;
    document.body.appendChild(loading);
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        loading.remove();
    }
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Đã copy: ' + text);
        });
    } else {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Đã copy: ' + text);
    }
}

/**
 * Print page
 */
function printPage() {
    window.print();
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            row.push(cols[j].innerText);
        }
        
        csv.push(row.join(','));
    }
    
    // Download CSV
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename || 'export.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Smooth scroll to element
 */
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/**
 * Toggle element visibility
 */
function toggleElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}
