# HƯỚNG DẪN CÀI ĐẶT VÀ SỬ DỤNG

## Hệ thống Quản lý Sản phẩm Mỹ phẩm

---

## 📋 YÊU CẦU HỆ THỐNG

- **XAMPP** (hoặc LAMP/WAMP/MAMP)
  - PHP 8.0 trở lên
  - MySQL/MariaDB 10.4 trở lên
  - Apache 2.4 trở lên
- **Trình duyệt**: Chrome, Firefox, Edge (phiên bản mới nhất)
- **Dung lượng**: Tối thiểu 100MB cho ứng dụng và database

---

## 🚀 HƯỚNG DẪN CÀI ĐẶT

### Bước 1: Chuẩn bị môi trường

1. **Cài đặt XAMPP** (nếu chưa có):
   - Tải XAMPP từ: https://www.apachefriends.org/
   - Cài đặt và khởi động Apache và MySQL

2. **Kiểm tra XAMPP hoạt động**:
   - Mở trình duyệt, truy cập: `http://localhost`
   - Nếu thấy trang XAMPP Dashboard → Thành công

### Bước 2: Copy source code

1. Copy toàn bộ thư mục `quanlysanpham` vào thư mục:
   ```
   C:\xampp\htdocs\
   ```

2. Cấu trúc thư mục sau khi copy:
   ```
   C:\xampp\htdocs\quanlysanpham\
   ├── assets/
   │   ├── css/
   │   └── js/
   ├── config/
   ├── controllers/
   ├── database/
   ├── models/
   ├── uploads/
   ├── views/
   ├── index.php
   └── ...
   ```

### Bước 3: Tạo cơ sở dữ liệu

1. **Mở phpMyAdmin**:
   - Truy cập: `http://localhost/phpmyadmin`
   - Đăng nhập (mặc định: username `root`, password để trống)

2. **Import database**:
   - Click tab **"SQL"** ở menu trên
   - Copy toàn bộ nội dung file `database/quanlysanpham.sql`
   - Paste vào ô SQL và click **"Go"** (hoặc "Thực hiện")
   - Đợi đến khi thấy thông báo thành công

3. **Kiểm tra database**:
   - Bên trái sẽ thấy database `quanlysanpham`
   - Click vào sẽ thấy các bảng: `categories`, `products`, `product_history`

### Bước 4: Cấu hình kết nối database (nếu cần)

Nếu MySQL của bạn có password hoặc cấu hình khác:

1. Mở file: `config/database.php`
2. Chỉnh sửa các thông số:
   ```php
   private $host = "localhost";        // Địa chỉ MySQL
   private $db_name = "quanlysanpham"; // Tên database
   private $username = "root";          // Username MySQL
   private $password = "";              // Password MySQL (nếu có)
   ```

### Bước 5: Phân quyền thư mục upload (quan trọng)

Đảm bảo thư mục `uploads/products/` có quyền ghi:

**Windows:**
- Thư mục đã được tự động tạo khi chạy lần đầu
- Nếu gặp lỗi upload ảnh, chuột phải vào thư mục → Properties → Security → Edit → Cho phép Full Control

**Linux/Mac:**
```bash
chmod -R 777 uploads/products/
```

### Bước 6: Chạy ứng dụng

1. Đảm bảo Apache và MySQL đang chạy trong XAMPP Control Panel
2. Mở trình duyệt và truy cập:
   ```
   http://localhost/quanlysanpham
   ```
3. Hệ thống sẽ tự động redirect đến Dashboard

---

## 📖 HƯỚNG DẪN SỬ DỤNG

### 1. Dashboard (Trang tổng quan)

- **Truy cập**: `http://localhost/quanlysanpham`
- **Chức năng**:
  - Xem thống kê tổng quan (tổng sản phẩm, active, hết hàng, hết hạn)
  - Xem sản phẩm sắp hết hạn (còn dưới 60 ngày)
  - Xem sản phẩm tồn kho thấp (dưới 20 sản phẩm)

### 2. Quản lý sản phẩm

#### 2.1. Xem danh sách sản phẩm

- Click menu **"Sản phẩm"** hoặc truy cập: `http://localhost/quanlysanpham/controllers/ProductController.php?action=index`
- **Tính năng**:
  - Tìm kiếm theo tên sản phẩm
  - Lọc theo danh mục
  - Lọc theo trạng thái (Active, Disabled, Out of stock, Expired)
  - Phân trang tự động
  - Xem thống kê nhanh

#### 2.2. Thêm sản phẩm mới

1. Click nút **"Thêm sản phẩm mới"** (màu xanh lá)
2. Điền đầy đủ thông tin:
   - **Mã sản phẩm**: Định dạng SPXX...X (VD: SP001, SPABC123)
   - **Tên sản phẩm**: 5-150 ký tự
   - **Danh mục**: Chọn từ dropdown
   - **Giá bán**: Từ 1.000 đến 1.000.000.000 VNĐ
   - **Số lượng tồn kho**: >= 0
   - **Ngày sản xuất**: Chọn từ calendar
   - **Hạn sử dụng**: Phải sau ngày sản xuất ít nhất 30 ngày
   - **Mô tả**: Tối đa 500 ký tự (không bắt buộc)
   - **Hình ảnh**: Tối đa 3 ảnh, mỗi ảnh <= 5MB, định dạng JPG/PNG
3. Click **"Lưu sản phẩm"**

**Lưu ý**:
- Mã sản phẩm phải duy nhất trong hệ thống
- Hệ thống tự động validate dữ liệu theo ràng buộc
- Nếu tồn kho = 0, trạng thái tự động chuyển thành "Out of stock"
- Nếu hết hạn, trạng thái tự động chuyển thành "Expired"

#### 2.3. Sửa sản phẩm

1. Tại danh sách sản phẩm, click nút **"Sửa"** (icon bút, màu vàng)
2. Chỉnh sửa thông tin cần thiết
3. Click **"Cập nhật sản phẩm"**

**Lưu ý**:
- Nếu không upload ảnh mới, ảnh cũ sẽ được giữ nguyên
- Có thể thay đổi trạng thái thủ công

#### 2.4. Xem chi tiết sản phẩm

1. Click nút **"Xem"** (icon mắt, màu xanh dương)
2. Xem đầy đủ thông tin sản phẩm
3. Click vào ảnh thumbnail để xem ảnh lớn

#### 2.5. Xóa sản phẩm

1. Click nút **"Xóa"** (icon thùng rác, màu đỏ)
2. Xác nhận xóa trong popup
3. Sản phẩm và ảnh liên quan sẽ bị xóa vĩnh viễn

**Cảnh báo**: Thao tác xóa không thể hoàn tác!

---

## 🔧 CẤU TRÚC DỰ ÁN

```
quanlysanpham/
│
├── assets/                      # Tài nguyên tĩnh
│   ├── css/
│   │   └── style.css           # CSS chính
│   └── js/
│       └── script.js           # JavaScript chính
│
├── config/                      # Cấu hình
│   ├── config.php              # Cấu hình chung
│   └── database.php            # Kết nối database
│
├── controllers/                 # Controllers (xử lý logic)
│   └── ProductController.php   # Controller sản phẩm
│
├── database/                    # Database
│   └── quanlysanpham.sql       # Script SQL
│
├── models/                      # Models (xử lý dữ liệu)
│   ├── Category.php            # Model danh mục
│   └── Product.php             # Model sản phẩm
│
├── uploads/                     # Thư mục upload
│   └── products/               # Ảnh sản phẩm
│
├── views/                       # Views (giao diện)
│   ├── layouts/
│   │   ├── header.php          # Header chung
│   │   └── footer.php          # Footer chung
│   ├── products/
│   │   ├── index.php           # Danh sách sản phẩm
│   │   ├── create.php          # Thêm sản phẩm
│   │   ├── edit.php            # Sửa sản phẩm
│   │   └── view.php            # Chi tiết sản phẩm
│   └── dashboard.php           # Dashboard
│
├── .htaccess                    # Cấu hình Apache
├── index.php                    # File chính
└── readme.txt                   # Tài liệu yêu cầu
```

---

## 🎯 TÍNH NĂNG CHÍNH

### ✅ Đã triển khai

1. **Quản lý sản phẩm**:
   - ✅ Thêm, sửa, xóa, xem sản phẩm
   - ✅ Tìm kiếm và lọc sản phẩm
   - ✅ Upload tối đa 3 ảnh/sản phẩm
   - ✅ Validate dữ liệu theo ràng buộc
   - ✅ Tự động cập nhật trạng thái

2. **Dashboard**:
   - ✅ Thống kê tổng quan
   - ✅ Cảnh báo sản phẩm sắp hết hạn
   - ✅ Cảnh báo tồn kho thấp

3. **Cơ sở dữ liệu**:
   - ✅ Ràng buộc chặt chẽ (CHECK constraints)
   - ✅ Khóa ngoại (Foreign keys)
   - ✅ Trigger tự động
   - ✅ View hỗ trợ
   - ✅ Stored procedures

4. **Bảo mật**:
   - ✅ Prepared statements (chống SQL Injection)
   - ✅ Sanitize input
   - ✅ Validate dữ liệu server-side
   - ✅ Validate file upload

5. **Giao diện**:
   - ✅ Responsive design
   - ✅ Modern UI với CSS3
   - ✅ Icons Font Awesome
   - ✅ Flash messages
   - ✅ Form validation

---

## 📊 DỮ LIỆU MẪU

Hệ thống đã có sẵn dữ liệu mẫu:

- **5 danh mục**: Chăm sóc da, Trang điểm, Dưỡng tóc, Nước hoa, Chăm sóc cơ thể
- **10 sản phẩm mẫu**: Bao gồm các sản phẩm từ các thương hiệu nổi tiếng

Bạn có thể:
- Xóa dữ liệu mẫu và thêm dữ liệu thực
- Giữ dữ liệu mẫu để test

---

## 🐛 XỬ LÝ LỖI THƯỜNG GẶP

### 1. Lỗi "Cannot connect to database"

**Nguyên nhân**: MySQL chưa chạy hoặc thông tin kết nối sai

**Giải pháp**:
- Kiểm tra MySQL đang chạy trong XAMPP Control Panel
- Kiểm tra file `config/database.php`
- Đảm bảo database `quanlysanpham` đã được tạo

### 2. Lỗi "Access denied for user"

**Nguyên nhân**: Username/password MySQL không đúng

**Giải pháp**:
- Mở file `config/database.php`
- Sửa username và password cho đúng với MySQL của bạn

### 3. Lỗi "Table doesn't exist"

**Nguyên nhân**: Chưa import database

**Giải pháp**:
- Mở phpMyAdmin
- Import file `database/quanlysanpham.sql`

### 4. Lỗi upload ảnh "Failed to move uploaded file"

**Nguyên nhân**: Thư mục uploads không có quyền ghi

**Giải pháp**:
- Windows: Chuột phải thư mục `uploads` → Properties → Security → Cho phép Full Control
- Linux/Mac: `chmod -R 777 uploads/`

### 5. Lỗi "Page not found" hoặc CSS không load

**Nguyên nhân**: Đường dẫn BASE_URL không đúng

**Giải pháp**:
- Mở file `config/config.php`
- Sửa dòng: `define('BASE_URL', '/quanlysanpham');`
- Nếu bạn đặt source ở thư mục khác, sửa cho đúng

### 6. Lỗi "Call to undefined function"

**Nguyên nhân**: PHP extension chưa được bật

**Giải pháp**:
- Mở file `php.ini` trong XAMPP
- Tìm và bỏ dấu `;` trước các dòng:
  ```
  extension=pdo_mysql
  extension=mysqli
  extension=gd
  ```
- Restart Apache

---

## 🔐 BẢO MẬT

### Các biện pháp đã áp dụng:

1. **SQL Injection**: Sử dụng PDO Prepared Statements
2. **XSS**: Sanitize tất cả input với `htmlspecialchars()`
3. **File Upload**: Validate type, size, extension
4. **Session**: Quản lý session an toàn
5. **CSRF**: Có thể mở rộng thêm CSRF token

### Khuyến nghị:

- Không sử dụng trong môi trường production mà không có thêm các biện pháp bảo mật
- Thay đổi thông tin database mặc định
- Bật HTTPS khi deploy
- Thêm authentication/authorization nếu cần

---

## 📝 GHI CHÚ

- Hệ thống được xây dựng cho mục đích **học tập và kiểm thử**
- Tuân thủ đầy đủ yêu cầu trong file `readme.txt`
- Kiến trúc MVC chuẩn, dễ mở rộng
- Code có comment đầy đủ bằng tiếng Việt

---

## 🆘 HỖ TRỢ

Nếu gặp vấn đề:

1. Kiểm tra lại từng bước trong hướng dẫn
2. Xem phần "Xử lý lỗi thường gặp"
3. Kiểm tra error log của Apache/PHP
4. Kiểm tra console của trình duyệt (F12)

---

## 📌 CHECKLIST CÀI ĐẶT

- [ ] XAMPP đã cài đặt và chạy
- [ ] Apache đang chạy
- [ ] MySQL đang chạy
- [ ] Source code đã copy vào `htdocs/quanlysanpham`
- [ ] Database đã import thành công
- [ ] Thư mục uploads có quyền ghi
- [ ] Truy cập `http://localhost/quanlysanpham` thành công
- [ ] Dashboard hiển thị đúng
- [ ] Có thể thêm/sửa/xóa sản phẩm
- [ ] Upload ảnh hoạt động

---

**Chúc bạn sử dụng thành công! 🎉**
