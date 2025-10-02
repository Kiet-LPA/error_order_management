# Hệ thống quản lý mượn xe (Rental Car Management System)

## Mô tả
Hệ thống quản lý mượn xe được xây dựng bằng PHP thuần, hỗ trợ 2 vai trò người dùng: **Manager** và **Employee**.

## Tính năng chính

### Cho Manager:
- **Quản lý xe**: Thêm, sửa, xóa, xem danh sách xe
- **Quản lý người dùng**: Tạo, sửa, xóa tài khoản employee/manager
- **Quản lý mượn xe**: Xem tất cả mượn xe, hủy mượn xe
- **Duyệt gia hạn**: Duyệt hoặc từ chối yêu cầu gia hạn mượn xe
- **Dashboard**: Thống kê tổng quan về hệ thống

### Cho Employee:
- **Mượn xe**: Xem danh sách xe có sẵn và mượn xe
- **Lịch sử mượn xe**: Xem tất cả mượn xe của mình
- **Gia hạn mượn xe**: Yêu cầu gia hạn thời gian mượn xe
- **Dashboard**: Xem mượn xe đang hoạt động và yêu cầu gia hạn đang chờ

## Cài đặt

### 1. Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Web server (Apache/Nginx)

### 2. Cài đặt database
1. Tạo database mới:
```sql
CREATE DATABASE rental_car_management;
```

2. Import file `database.sql` để tạo các bảng và dữ liệu mẫu:
```bash
mysql -u root -p rental_car_management < database.sql
```

### 3. Cấu hình
1. Sao chép file cấu hình:
```bash
cp includes/config.php.example includes/config.php
```

2. Chỉnh sửa file `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'rental_car_management');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('SITE_URL', 'http://localhost/remaining_order_management');
```

### 4. Cấu trúc thư mục
```
remaining_order_management/
├── admin/                  # Trang quản trị cho Manager
│   ├── dashboard.php
│   ├── cars.php
│   ├── users.php
│   ├── rentals.php
│   └── extensions.php
├── employee/               # Trang cho Employee
│   ├── dashboard.php
│   ├── cars.php
│   ├── rentals.php
│   └── request_extension.php
├── includes/               # File cấu hình và helper
│   ├── config.php
│   ├── header.php
│   └── footer.php
├── assets/                 # CSS, JS, hình ảnh
│   ├── css/
│   └── js/
├── login.php              # Trang đăng nhập
├── logout.php             # Đăng xuất
├── database.sql           # File SQL tạo database
└── README.md
```

## Sử dụng

### Tài khoản mặc định
- **Email**: admin@example.com
- **Mật khẩu**: password
- **Vai trò**: Manager

### Quy trình mượn xe
1. **Employee đăng nhập** và vào trang "Mượn xe"
2. **Chọn xe** có sẵn và nhập thời gian thuê
3. **Xác nhận mượn xe** - xe sẽ chuyển sang trạng thái "Đang thuê"
4. **Sau khi trả xe** - xe sẽ có thời gian nghỉ 6 tiếng trước khi có thể thuê lại

### Quy trình gia hạn
1. **Employee** yêu cầu gia hạn từ trang dashboard hoặc chi tiết mượn xe
2. **Nhập lý do** và thời gian trả xe mới
3. **Manager** xem và duyệt/từ chối yêu cầu từ trang "Duyệt gia hạn"
4. **Nếu được duyệt** - thời gian trả xe sẽ được cập nhật tự động

## Tính năng đặc biệt

### Quản lý trạng thái xe
- **Active**: Xe có thể thuê
- **Inactive**: Xe không hoạt động
- **Rented**: Xe đang được thuê

### Thời gian nghỉ xe
- Sau khi trả xe, xe sẽ có thời gian nghỉ **6 tiếng** trước khi có thể thuê lại
- Thời gian này được tính tự động và hiển thị cho người dùng

### Bảo mật
- Mã hóa mật khẩu bằng `password_hash()`
- Kiểm tra quyền truy cập cho từng trang
- Sanitize dữ liệu đầu vào để tránh XSS

## Công nghệ sử dụng
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Icons**: Font Awesome 6

## Hỗ trợ
Nếu gặp vấn đề, vui lòng kiểm tra:
1. Cấu hình database trong `includes/config.php`
2. Quyền truy cập thư mục
3. Log lỗi PHP và MySQL

## Phiên bản
- **Version**: 1.0.0
- **Ngày tạo**: 2024
- **Tác giả**: AI Assistant

