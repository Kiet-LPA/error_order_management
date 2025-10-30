-- Fix session column issue in gps_requests table
-- Add session column if it doesn't exist

ALTER TABLE `gps_requests` 
ADD COLUMN `session` ENUM('checkin', 'checkout') NULL AFTER `request_date`;

-- Update existing records to have 'checkin' as default session
UPDATE `gps_requests` SET `session` = 'checkin' WHERE `session` IS NULL;

-- Make session column NOT NULL after updating existing records
ALTER TABLE `gps_requests` 
MODIFY COLUMN `session` ENUM('checkin', 'checkout') NOT NULL AFTER `request_date`;

-- Show the updated table structure
DESCRIBE `gps_requests`;

