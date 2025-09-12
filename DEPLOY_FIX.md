# Sửa lỗi Deploy Laravel - Session Error

## Lỗi hiện tại
```
ErrorException: file_put_contents(C:\laragon\www\error_order_management\storage\framework/sessions/...): Failed to open stream: No such file or directory
```

## Nguyên nhân
Laravel đang cố ghi session vào file thay vì database do cấu hình SESSION_DRIVER không đúng.

## ✅ Đã sửa xong
1. ✅ Xóa migration trùng lặp `2025_01_15_000000_create_sessions_table.php`
2. ✅ Xóa migration trùng lặp `2025_09_11_181759_create_forward_requests_table.php`
3. ✅ Bảng `sessions` đã tồn tại trong database
4. ✅ Cấu hình `SESSION_DRIVER=database` trong `config/session.php`

## 🔧 Cần làm trên server

### 1. Cấu hình file .env
Tạo file `.env` trên server với nội dung:

```env
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://thienanminh.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# QUAN TRỌNG: Cấu hình session driver
SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### 2. Chạy migration trên server
```bash
php artisan migrate
```

### 3. Cấp quyền cho thư mục storage
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 4. Tạo APP_KEY nếu chưa có
```bash
php artisan key:generate
```

### 5. Clear cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 🎯 Kết quả mong đợi
- Lỗi session sẽ biến mất
- Ứng dụng hoạt động bình thường
- Session được lưu trong database thay vì file

## 📝 Lưu ý
- Đảm bảo database connection đúng
- Kiểm tra quyền ghi cho thư mục storage
- SESSION_DRIVER phải là 'database' không phải 'file'
