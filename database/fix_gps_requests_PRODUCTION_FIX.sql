-- ===================================================================
-- Fix GPS Requests Unique Constraint - PRODUCTION
-- Chạy TOÀN BỘ script này cùng lúc (không chạy từng dòng)
-- ===================================================================

-- Bước 0: Đảm bảo tất cả records có giá trị session
UPDATE `gps_requests` 
SET `session` = 'checkin' 
WHERE `session` IS NULL;

-- Bước 1: TẮT foreign key checks (BẮT BUỘC phải chạy trước DROP INDEX)
SET FOREIGN_KEY_CHECKS = 0;

-- Bước 2: Xóa unique constraint cũ
ALTER TABLE `gps_requests` DROP INDEX `unique_request`;

-- Bước 3: Tạo unique constraint mới bao gồm session
ALTER TABLE `gps_requests` 
ADD UNIQUE KEY `unique_request` (`user_id`, `request_date`, `session`);

-- Bước 4: BẬT lại foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Bước 5: Kiểm tra kết quả
SHOW INDEX FROM `gps_requests` WHERE Key_name = 'unique_request';









