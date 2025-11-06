-- ===================================================================
-- Fix GPS Requests Unique Constraint để hỗ trợ checkin và checkout riêng biệt
-- Ngày: 2025-11-03
-- Chạy trên production
-- ===================================================================

-- Bước 0: Kiểm tra cột session có tồn tại không
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'gps_requests' 
AND COLUMN_NAME = 'session';

-- Nếu cột session chưa có, cần thêm trước:
-- ALTER TABLE `gps_requests` ADD COLUMN `session` ENUM('checkin', 'checkout') NULL AFTER `request_date`;

-- Bước 0.5: Đảm bảo tất cả records có giá trị session (nếu có NULL thì set mặc định là 'checkin')
UPDATE `gps_requests` 
SET `session` = 'checkin' 
WHERE `session` IS NULL;

-- Bước 1: Kiểm tra duplicate records (chạy để xem, không sửa)
SELECT 
    user_id, 
    request_date, 
    COUNT(*) as count_records
FROM `gps_requests`
GROUP BY user_id, request_date
HAVING count_records > 1;

-- Bước 2: Xóa unique constraint cũ
ALTER TABLE `gps_requests` DROP INDEX `unique_request`;

-- Bước 3: Tạo unique constraint mới bao gồm session
-- Cho phép 1 user có thể có 2 GPS requests trong 1 ngày (1 cho checkin, 1 cho checkout)
ALTER TABLE `gps_requests` 
ADD UNIQUE KEY `unique_request` (`user_id`, `request_date`, `session`);

-- Bước 4: Kiểm tra kết quả
SHOW INDEX FROM `gps_requests` WHERE Key_name = 'unique_request';

-- Done! Migration hoàn tất

