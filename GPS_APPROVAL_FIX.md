# Sửa lỗi GPS Approval - Lỗi 500

## Vấn đề hiện tại
Lỗi 500 khi phê duyệt GPS request với thông báo:
```
SQLSTATE[HY000]: General error: 1364 Field 'department_id' doesn't have a default value
```

## Nguyên nhân
- Bảng `checkins` thiếu cột `department_id` hoặc cột này không có giá trị mặc định
- Model `Checkin` đã có `department_id` trong `$fillable` nhưng database chưa được cập nhật

## Giải pháp

### 1. Chạy Migration
```bash
php artisan migrate
```

### 2. Kiểm tra cấu trúc database
Truy cập: `/admin/checkin/debug-database`

### 3. Nếu vẫn lỗi, chạy migration thủ công
```sql
-- Kiểm tra cấu trúc bảng checkins
DESCRIBE checkins;

-- Nếu thiếu cột department_id, thêm vào
ALTER TABLE checkins ADD COLUMN department_id BIGINT UNSIGNED NOT NULL AFTER user_id;
ALTER TABLE checkins ADD CONSTRAINT checkins_department_id_foreign FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE;
```

### 4. Test GPS Approval
Truy cập: `/admin/checkin/gps-requests/{id}/test`

## Debug Tools

### 1. Debug Database Structure
```
GET /admin/checkin/debug-database
```
- Kiểm tra cấu trúc bảng `checkins`
- Kiểm tra model `fillable` fields
- Test tạo checkin record

### 2. Test GPS Approval
```
GET /admin/checkin/gps-requests/{gpsRequest}/test
```
- Test GPS approval mà không ảnh hưởng đến dữ liệu thật

### 3. Xem Logs
```bash
tail -f storage/logs/laravel.log | grep -E "(GPS|approval|checkin)"
```

## Kiểm tra Database

### 1. Kiểm tra cấu trúc bảng
```sql
DESCRIBE checkins;
```

### 2. Kiểm tra dữ liệu mẫu
```sql
SELECT * FROM checkins LIMIT 5;
SELECT * FROM departments LIMIT 5;
```

### 3. Kiểm tra GPS requests
```sql
SELECT * FROM gps_requests WHERE status = 'pending';
```

## Nếu vẫn lỗi

### 1. Kiểm tra migration status
```bash
php artisan migrate:status
```

### 2. Rollback và chạy lại
```bash
php artisan migrate:rollback --step=5
php artisan migrate
```

### 3. Tạo migration mới
```bash
php artisan make:migration fix_checkins_department_id_column
```

## Kết quả mong đợi
- GPS approval hoạt động bình thường
- Không còn lỗi 500
- Checkin record được tạo với `department_id` đúng

## Liên hệ
Nếu vẫn có vấn đề, hãy cung cấp:
1. Output từ `/admin/checkin/debug-database`
2. Kết quả `DESCRIBE checkins;`
3. Logs từ `storage/logs/laravel.log`
