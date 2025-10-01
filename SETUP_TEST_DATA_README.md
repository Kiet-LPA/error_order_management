# 🧪 Hướng dẫn tạo dữ liệu test có Avatar

## 📋 Tổng quan
Guide này giúp bạn tạo dữ liệu test hoàn chỉnh với avatar để xem demo trong:
- ✅ Checkin System (điểm danh)
- ✅ RentalCar System (thuê xe)

## 🚀 Cách 1: Tự động (Khuyến nghị)

### Bước 1: Tạo users có avatar trong Laravel
```bash
php artisan test:create-users-with-avatar
```

**Kết quả:**
- ✅ Tạo 6 users test trong Laravel
- ✅ Mỗi user có avatar SVG đẹp với chữ cái đầu
- ✅ Tự động đồng bộ avatar sang checkin và rentalcar
- ✅ Password mặc định: `123456`

### Bước 2: Thêm dữ liệu checkin và rental
```bash
# Trong phpMyAdmin hoặc MySQL client
mysql -u root -p < database/seed_test_data_with_avatar.sql
```

**Kết quả:**
- ✅ 20 lần điểm danh cho test users
- ✅ 1 GPS request đang chờ duyệt
- ✅ 3 xe test
- ✅ 3 rental records (2 active, 1 completed)
- ✅ 1 extension request chờ duyệt

### Bước 3: Xem kết quả
```
🌐 Truy cập các trang:
- Checkin Reports: http://localhost/checkin/reports.php
- Checkin Users: http://localhost/checkin/manage_users.php  
- RentalCar: http://localhost/rentalcar/admin/rentals.php

🔑 Đăng nhập với:
- Password: 123456
- Email: xem danh sách bên dưới
```

## 🔧 Cách 2: Thủ công

### Bước 1: Thêm cột avatar (nếu chưa có)
```bash
# Chạy migration
php artisan migrate

# Hoặc import SQL
mysql -u root -p < database/add_avatar_columns.sql
```

### Bước 2: Import dữ liệu test
```bash
mysql -u root -p < database/seed_test_data_with_avatar.sql
```

### Bước 3: Tạo users trong Laravel
```bash
php artisan test:create-users-with-avatar
```

## 👥 Danh sách Users Test

### Checkin System

| Username | Email | Role | Họ tên |
|----------|-------|------|--------|
| test_user1 | testuser1@hpfoods.com | Employee | Nguyễn Văn Test |
| test_user2 | testuser2@hpfoods.com | Employee | Trần Thị Demo |
| test_manager | testmanager@hpfoods.com | Manager | Lê Văn Quản Lý |

**Login URL:** `/checkin/login.php`

### RentalCar System

| Username | Email | Role | Họ tên |
|----------|-------|------|--------|
| test_emp1 | testemp1@company.com | Employee | Phạm Văn Xe |
| test_emp2 | testemp2@company.com | Employee | Hoàng Thị Thuê |
| test_mgr | testmgr@company.com | Manager | Vũ Văn Quản Lý |

**Login URL:** `/rentalcar/login.php`

## 📊 Dữ liệu test bao gồm

### Checkin:
- ✅ **20 lần điểm danh** trong 30 ngày qua
- ✅ **1 GPS request** đang chờ duyệt (test_user2)
- ✅ Tất cả users có **avatar đẹp**

### RentalCar:
- ✅ **3 xe test**: Toyota Vios, Honda City, Mazda 3
- ✅ **2 rental đang active**:
  - Phạm Văn Xe thuê Toyota Vios (2 ngày trước → 2 ngày sau)
  - Hoàng Thị Thuê thuê Mazda 3 (5 ngày trước → 1 ngày sau)
- ✅ **1 rental hoàn thành**:
  - Phạm Văn Xe thuê Honda City (đã hoàn thành)
- ✅ **1 extension request** đang chờ duyệt

## 🎨 Avatar Features

### Tự động tạo avatar SVG:
- ✅ Gradient background đẹp
- ✅ Chữ cái đầu của tên
- ✅ Màu sắc đa dạng
- ✅ Tự động fallback nếu không có ảnh

### Hiển thị avatar:
- ✅ Avatar tròn với border trắng
- ✅ Box shadow đẹp
- ✅ Hover effect
- ✅ Responsive trên mobile

## 🔄 Đồng bộ Avatar

### Tự động:
Avatar sẽ tự động đồng bộ từ Laravel khi:
- Upload avatar mới trong Laravel
- Chạy command `test:create-users-with-avatar`

### Thủ công:
```bash
php artisan sync:avatars
```

## 📸 Xem Avatar ở đâu?

### Checkin System:
1. **Reports Page** (`/checkin/reports.php`)
   - Bảng "Thống kê điểm danh theo nhân viên"
   - Bảng "Yêu cầu GPS"
   
2. **Manage Users** (`/checkin/manage_users.php`)
   - Danh sách tất cả users

### RentalCar System:
1. **Rentals Page** (`/rentalcar/admin/rentals.php`)
   - Cột "Người thuê" có avatar
   
2. **Dashboard** (các trang khác)
   - Sẽ được cập nhật

## 🐛 Troubleshooting

### Avatar không hiển thị?

**Kiểm tra:**
```bash
# 1. Kiểm tra avatar đã được tạo
ls -la storage/app/public/avatars/

# 2. Kiểm tra symlink
php artisan storage:link

# 3. Chạy lại đồng bộ
php artisan sync:avatars
```

### Users không login được?

**Kiểm tra:**
```sql
-- Kiểm tra users trong checkin
SELECT username, email, is_active FROM checkin_new.users 
WHERE username LIKE 'test%';

-- Kiểm tra users trong rentalcar
SELECT username, email, role FROM rental_car_management.users 
WHERE username LIKE 'test%';
```

**Password mặc định:** `123456`

### Database không tồn tại?

```bash
# Tạo database checkin_new
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS checkin_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Tạo database rental_car_management
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS rental_car_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## 📁 Files liên quan

```
app/Console/Commands/
├── CreateTestUsersWithAvatar.php  ← Tạo users có avatar
└── SyncUserAvatars.php            ← Đồng bộ avatar

database/
├── seed_test_data_with_avatar.sql ← Dữ liệu test
├── add_avatar_columns.sql         ← Thêm cột avatar
└── migrations/
    └── 2025_01_10_add_avatar_to_checkin_rentalcar.php

public/
├── css/avatar-styles.css          ← CSS avatar
└── avatar-demo.html               ← Demo components

checkin/
├── config.php                     ← getUserAvatar()
├── reports.php                    ← Avatar UI
└── manage_users.php               ← Avatar UI

rentalcar/
├── includes/config.php            ← getUserAvatar()
└── admin/rentals.php              ← Avatar UI
```

## 🎯 Quick Start (TL;DR)

```bash
# 1 command để setup tất cả
php artisan test:create-users-with-avatar && \
mysql -u root -p < database/seed_test_data_with_avatar.sql
```

Sau đó truy cập:
- http://localhost/checkin/reports.php
- http://localhost/rentalcar/admin/rentals.php

Password: `123456`

## 📞 Support

Nếu cần thêm hỗ trợ:
1. Xem `AVATAR_UI_GUIDE.md` - Hướng dẫn UI
2. Xem `SYNC_AVATAR_GUIDE.md` - Hướng dẫn đồng bộ
3. Xem demo: http://localhost/public/avatar-demo.html

---

**Happy Testing! 🎉**

