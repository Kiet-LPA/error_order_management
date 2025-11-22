-- ===================================================================
-- Fix GPS Requests Unique Constraint để hỗ trợ checkin và checkout riêng biệt
-- Ngày: 2025-11-03
-- ===================================================================

-- Bước 1: Kiểm tra và đảm bảo cột session tồn tại (nếu chưa có thì thêm)
SET @session_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'gps_requests' 
    AND COLUMN_NAME = 'session'
);

-- Nếu cột session chưa tồn tại, thêm nó
SET @sql = IF(@session_exists = 0,
    'ALTER TABLE `gps_requests` ADD COLUMN `session` ENUM(''checkin'', ''checkout'') NULL AFTER `request_date`;',
    'SELECT "Column session already exists" AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Cập nhật các record hiện tại chưa có session (mặc định là 'checkin')
UPDATE `gps_requests` 
SET `session` = 'checkin' 
WHERE `session` IS NULL;

-- Nếu cột session là NULL, đổi thành NOT NULL
SET @sql2 = IF(@session_exists = 0,
    'ALTER TABLE `gps_requests` MODIFY COLUMN `session` ENUM(''checkin'', ''checkout'') NOT NULL AFTER `request_date`;',
    'SELECT "Column session already exists, skipping modification" AS message;'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Bước 2: Xóa unique constraint cũ (nếu tồn tại)
ALTER TABLE `gps_requests` 
DROP INDEX IF EXISTS `unique_request`;

-- Bước 3: Tạo unique constraint mới bao gồm session
-- Cho phép 1 user có thể có 2 GPS requests trong 1 ngày (1 cho checkin, 1 cho checkout)
ALTER TABLE `gps_requests` 
ADD UNIQUE KEY `unique_request` (`user_id`, `request_date`, `session`);

-- Bước 4: Kiểm tra kết quả
SELECT 
    'Migration completed successfully!' AS status,
    'Unique constraint updated to include session column' AS message;









