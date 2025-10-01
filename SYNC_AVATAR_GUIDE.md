# Hướng dẫn đồng bộ Avatar

## Tổng quan
Hệ thống sẽ đồng bộ avatar từ hệ thống Laravel chính sang 2 hệ thống phụ:
- **Checkin** (checkin_new database)
- **RentalCar** (rental_car_management database)

## Bước 1: Thêm cột avatar vào database

### Cách 1: Chạy migration (Khuyến nghị)
```bash
php artisan migrate
```

### Cách 2: Chạy SQL thủ công
Nếu migration không hoạt động, chạy các lệnh SQL sau trong phpMyAdmin:

```sql
-- Thêm cột avatar vào bảng users của checkin
ALTER TABLE checkin_new.users 
ADD COLUMN avatar VARCHAR(255) NULL AFTER full_name;

-- Thêm cột avatar vào bảng users của rentalcar
ALTER TABLE rental_car_management.users 
ADD COLUMN avatar VARCHAR(255) NULL AFTER name;
```

## Bước 2: Đồng bộ avatar

### Chạy lệnh đồng bộ
```bash
php artisan sync:avatars
```

### Đồng bộ tự động
Để đồng bộ tự động mỗi khi có thay đổi, thêm vào `app/Observers/UserObserver.php`:

```php
<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class UserObserver
{
    public function updated(User $user)
    {
        // Đồng bộ avatar khi user được cập nhật
        if ($user->wasChanged('avatar')) {
            $this->syncAvatar($user);
        }
    }
    
    private function syncAvatar(User $user)
    {
        $avatarPath = $user->avatar ? "/storage/avatars/{$user->avatar}" : null;
        
        try {
            // Đồng bộ sang checkin
            DB::connection('mysql')->update("
                UPDATE checkin_new.users 
                SET avatar = ? 
                WHERE email = ?
            ", [$avatarPath, $user->email]);
            
            // Đồng bộ sang rentalcar
            DB::connection('mysql')->update("
                UPDATE rental_car_management.users 
                SET avatar = ? 
                WHERE email = ?
            ", [$avatarPath, $user->email]);
        } catch (\Exception $e) {
            \Log::warning("Failed to sync avatar: " . $e->getMessage());
        }
    }
}
```

Đăng ký Observer trong `app/Providers/AppServiceProvider.php`:

```php
public function boot(): void
{
    \App\Models\User::observe(\App\Observers\UserObserver::class);
}
```

## Bước 3: Kiểm tra

### Xem avatar đã đồng bộ
```sql
-- Kiểm tra checkin
SELECT id, username, full_name, avatar FROM checkin_new.users WHERE avatar IS NOT NULL;

-- Kiểm tra rentalcar
SELECT id, username, name, avatar FROM rental_car_management.users WHERE avatar IS NOT NULL;
```

### Test trên trang web
1. **Checkin**: Truy cập `/checkin/reports.php` để xem danh sách người dùng với avatar
2. **RentalCar**: Truy cập các trang quản lý thuê xe để xem avatar người thuê

## Lưu ý

1. **Đường dẫn avatar**: Avatar được lưu ở `/storage/avatars/` trong Laravel
2. **Đồng bộ theo email**: Hệ thống sử dụng email để khớp user giữa các database
3. **Avatar mặc định**: Nếu không có avatar, hệ thống tự tạo avatar SVG với chữ cái đầu

## Troubleshooting

### Lỗi database connection
Nếu gặp lỗi kết nối database, kiểm tra:
- Database `checkin_new` đã tồn tại chưa
- Database `rental_car_management` đã tồn tại chưa
- User MySQL có quyền truy cập các database này

### Avatar không hiển thị
1. Kiểm tra file avatar có tồn tại: `storage/app/public/avatars/`
2. Chạy: `php artisan storage:link`
3. Kiểm tra quyền file: `chmod -R 775 storage`

## Tính năng mới

### Hiển thị avatar
- ✅ Trang báo cáo điểm danh (checkin/reports.php)
- ✅ Avatar tròn với border đẹp
- ✅ Fallback sang avatar SVG nếu không có ảnh
- ✅ Hover effect

### Cải thiện UI
- Avatar 36x36px với border trắng
- Box shadow nhẹ
- Hiển thị cùng với tên và username
- Responsive trên mobile

