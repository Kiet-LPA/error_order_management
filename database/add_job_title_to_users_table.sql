-- Thêm trường job_title (Chức vụ) vào bảng users
-- Chạy script này trên production database để thêm trường mới mà không cần deploy migration

ALTER TABLE `users` 
ADD COLUMN `job_title` VARCHAR(255) NULL 
AFTER `position`;

-- Kiểm tra kết quả
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() 
-- AND TABLE_NAME = 'users' 
-- AND COLUMN_NAME = 'job_title';


