# Sửa lỗi GPS Approval - Production Database

## ✅ Đã xác nhận: Bảng `checkins` có cột `department_id`

## 🔍 Kiểm tra dữ liệu hiện tại

### 1. Kiểm tra dữ liệu NULL trong department_id
```sql
-- Kiểm tra xem có checkin nào thiếu department_id không
SELECT COUNT(*) as total_checkins,
       COUNT(department_id) as checkins_with_department_id,
       COUNT(*) - COUNT(department_id) as checkins_without_department_id
FROM checkins;
```

### 2. Kiểm tra dữ liệu cụ thể
```sql
-- Xem các checkin thiếu department_id
SELECT c.id, c.user_id, c.department_id, u.name as user_name, u.department_id as user_department_id
FROM checkins c 
JOIN users u ON c.user_id = u.id 
WHERE c.department_id IS NULL OR c.department_id = 0;
```

## 🔧 Sửa lỗi dữ liệu

### 1. Cập nhật dữ liệu NULL với department_id từ user
```sql
-- Cập nhật tất cả checkin thiếu department_id
UPDATE checkins c 
JOIN users u ON c.user_id = u.id 
SET c.department_id = u.department_id 
WHERE c.department_id IS NULL OR c.department_id = 0;
```

### 2. Kiểm tra kết quả sau khi cập nhật
```sql
-- Kiểm tra lại sau khi cập nhật
SELECT COUNT(*) as total_checkins,
       COUNT(department_id) as checkins_with_department_id,
       COUNT(*) - COUNT(department_id) as checkins_without_department_id
FROM checkins;
```

## 🚨 Nếu vẫn có dữ liệu NULL

### 1. Kiểm tra user có department_id không
```sql
-- Kiểm tra user thiếu department_id
SELECT u.id, u.name, u.department_id, d.name as department_name
FROM users u 
LEFT JOIN departments d ON u.department_id = d.id 
WHERE u.department_id IS NULL;
```

### 2. Cập nhật user thiếu department_id (nếu cần)
```sql
-- Cập nhật user với department_id mặc định (thay 1 bằng ID department thực tế)
UPDATE users 
SET department_id = 1 
WHERE department_id IS NULL;
```

### 3. Cập nhật lại checkins
```sql
-- Cập nhật lại checkins sau khi sửa users
UPDATE checkins c 
JOIN users u ON c.user_id = u.id 
SET c.department_id = u.department_id 
WHERE c.department_id IS NULL OR c.department_id = 0;
```

## 🔍 Kiểm tra foreign key constraint

### 1. Kiểm tra constraint hiện tại
```sql
-- Kiểm tra foreign key constraints
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'checkins' 
AND COLUMN_NAME = 'department_id';
```

### 2. Thêm foreign key constraint nếu thiếu
```sql
-- Thêm foreign key constraint nếu chưa có
ALTER TABLE checkins 
ADD CONSTRAINT checkins_department_id_foreign 
FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE;
```

## 🧪 Test sau khi sửa

### 1. Test tạo checkin mới
```sql
-- Test tạo checkin mới
INSERT INTO checkins (user_id, department_id, checkin_date, session, checkin_time, latitude, longitude, distance_meters, ip_address, status, notes)
VALUES (1, 1, CURDATE(), 'morning', NOW(), 0, 0, 0, '127.0.0.1', 'success', 'Test checkin');
```

### 2. Xóa test data
```sql
-- Xóa test data
DELETE FROM checkins WHERE notes = 'Test checkin';
```

## 📋 Script hoàn chỉnh để chạy

```sql
-- 1. Backup dữ liệu quan trọng
CREATE TABLE checkins_backup AS SELECT * FROM checkins;

-- 2. Kiểm tra dữ liệu hiện tại
SELECT COUNT(*) as total_checkins,
       COUNT(department_id) as checkins_with_department_id,
       COUNT(*) - COUNT(department_id) as checkins_without_department_id
FROM checkins;

-- 3. Cập nhật dữ liệu NULL
UPDATE checkins c 
JOIN users u ON c.user_id = u.id 
SET c.department_id = u.department_id 
WHERE c.department_id IS NULL OR c.department_id = 0;

-- 4. Kiểm tra kết quả
SELECT COUNT(*) as total_checkins,
       COUNT(department_id) as checkins_with_department_id,
       COUNT(*) - COUNT(department_id) as checkins_without_department_id
FROM checkins;

-- 5. Thêm foreign key constraint nếu cần
ALTER TABLE checkins 
ADD CONSTRAINT checkins_department_id_foreign 
FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE;

-- 6. Test tạo checkin mới
INSERT INTO checkins (user_id, department_id, checkin_date, session, checkin_time, latitude, longitude, distance_meters, ip_address, status, notes)
VALUES (1, 1, CURDATE(), 'morning', NOW(), 0, 0, 0, '127.0.0.1', 'success', 'Test checkin');

-- 7. Xóa test data
DELETE FROM checkins WHERE notes = 'Test checkin';

-- 8. Xóa backup nếu mọi thứ OK
-- DROP TABLE checkins_backup;
```

## ✅ Kết quả mong đợi

Sau khi chạy script:
- Tất cả checkins có `department_id` hợp lệ
- GPS approval hoạt động bình thường
- Không còn lỗi 500
