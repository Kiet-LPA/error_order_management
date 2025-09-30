# Cấu trúc dự án Rental Car Management System

## 📁 Cấu trúc thư mục cuối cùng:

```
remaining_order_management/
├── admin/                          # Trang quản trị cho Manager
│   ├── cars.php                   # Quản lý xe
│   ├── dashboard.php              # Dashboard manager
│   ├── extensions.php             # Duyệt gia hạn
│   ├── my_rentals.php             # Lịch sử thuê xe của manager
│   ├── rent_car.php               # Thuê xe (cho manager)
│   ├── rentals.php                # Quản lý thuê xe
│   └── users.php                  # Quản lý người dùng
├── employee/                       # Trang cho Employee
│   ├── cars.php                   # Thuê xe
│   ├── dashboard.php              # Dashboard employee
│   ├── rentals.php                # Lịch sử thuê xe
│   └── request_extension.php      # Yêu cầu gia hạn
├── includes/                       # File cấu hình và helper
│   ├── config.php                 # Cấu hình database và functions
│   ├── footer.php                 # Footer template
│   └── header.php                 # Header template với navigation
├── assets/                         # CSS, JS, hình ảnh
│   ├── css/
│   │   └── style.css              # Custom styles
│   └── js/
│       └── script.js              # Custom JavaScript
├── index.php                       # Trang chủ (redirect to login)
├── login.php                       # Trang đăng nhập
├── logout.php                      # Đăng xuất
├── database.sql                    # File SQL tạo database
├── .htaccess                       # Apache configuration
└── README.md                       # Hướng dẫn sử dụng
```

## 🎯 Tính năng chính:

### Manager:
- ✅ **Dashboard** - Thống kê tổng quan
- ✅ **Quản lý** - Xe, người dùng, thuê xe, duyệt gia hạn
- ✅ **Thuê xe** - Thuê xe mới, lịch sử thuê xe của mình
- ✅ **Giao diện tối ưu** - Navigation dropdown, responsive

### Employee:
- ✅ Thuê xe có sẵn
- ✅ Xem lịch sử thuê xe
- ✅ Yêu cầu gia hạn thuê xe
- ✅ Dashboard cá nhân

## 🔧 Cấu hình:

### Database:
- **Name**: rental_car_management
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci

### Tài khoản mặc định:
- **Email**: admin@example.com
- **Password**: password
- **Role**: Manager

## 🚀 Cách tích hợp vào hệ thống lớn:

1. **Copy toàn bộ thư mục** vào hệ thống chính
2. **Import database.sql** vào database chính
3. **Cập nhật config.php** với thông tin database mới
4. **Cập nhật SITE_URL** trong config.php
5. **Tích hợp navigation** vào hệ thống chính
6. **Cập nhật authentication** nếu cần

## 📋 File quan trọng cần chú ý:

- `includes/config.php` - Cấu hình chính
- `database.sql` - Cấu trúc database
- `includes/header.php` - Navigation template
- `.htaccess` - Apache configuration
