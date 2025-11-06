-- ===================================================================
-- Fix GPS Requests Unique Constraint để hỗ trợ checkin và checkout riêng biệt
-- Ngày: 2025-11-03
-- Chạy trên production
-- ===================================================================

-- Bước 1: Kiểm tra xem có duplicate records không (trước khi sửa constraint)
-- Nếu có, bạn cần xử lý chúng trước
SELECT 
    user_id, 
    request_date, 
    COUNT(*) as count_records
FROM `gps_requests`
GROUP BY user_id, request_date
HAVING count_records > 1;

-- Bước 2: Đảm bảo cột session tồn tại và có giá trị
-- (Bỏ qua bước này nếu cột session đã tồn tại)
-- Nếu cần, uncomment các dòng dưới:
-- ALTER TABLE `gps_requests` ADD COLUMN `session` ENUM('checkin', 'checkout') NULL AFTER `request_date`;
-- UPDATE `gps_requests` SET `session` = 'checkin' WHERE `session` IS NULL;
-- ALTER TABLE `gps_requests` MODIFY COLUMN `session` ENUM('checkin', 'checkout') NOT NULL;

-- Bước 3: Xóa unique constraint cũ
-- Sử dụng cách này để tránh lỗi nếu constraint không tồn tại
SET @exist := (
    SELECT COUNT(*) 
    FROM information_schema.table_constraints 
    WHERE table_schema = DATABASE()
    AND table_name = 'gps_requests' 
    AND constraint_name = 'unique_request'
);

SET @sqlstmt := IF(@exist > 0, 
    'ALTER TABLE `gps_requests` DROP INDEX `unique_request`', 
    'SELECT "Index unique_request does not exist, skipping" AS message'
);

PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Bước 4: Tạo unique constraint mới bao gồm session
-- Cho phép 1 user có thể có 2 GPS requests trong 1 ngày (1 cho checkin, 1 cho checkout)
ALTER TABLE `gps_requests` 
ADD UNIQUE KEY `unique_request` (`user_id`, `request_date`, `session`);

-- Bước 5: Xác nhận constraint đã được tạo
SHOW INDEX FROM `gps_requests` WHERE Key_name = 'unique_request';

-- Done! Migration hoàn tất

