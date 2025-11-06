-- ===================================================================
-- Fix GPS Requests Unique Constraint để hỗ trợ checkin và checkout riêng biệt
-- Ngày: 2025-11-03
-- GIẢI QUYẾT LỖI: Cannot drop index 'unique_request': needed in a foreign key constraint
-- ===================================================================

-- Bước 0: Đảm bảo tất cả records có giá trị session (QUAN TRỌNG!)
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

-- Bước 2: TẮT foreign key checks tạm thời
SET FOREIGN_KEY_CHECKS = 0;

-- Bước 3: Xóa unique constraint cũ
ALTER TABLE `gps_requests` DROP INDEX `unique_request`;

-- Bước 4: Tạo unique constraint mới bao gồm session
ALTER TABLE `gps_requests` 
ADD UNIQUE KEY `unique_request` (`user_id`, `request_date`, `session`);

-- Bước 5: BẬT lại foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Bước 6: Kiểm tra kết quả
SHOW INDEX FROM `gps_requests` WHERE Key_name = 'unique_request';

-- Done! Migration hoàn tất

