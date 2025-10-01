-- ===================================================================
-- Script thêm cột avatar vào database checkin và rentalcar
-- ===================================================================

-- Thêm avatar vào checkin_new database
USE checkin_new;

-- Kiểm tra nếu cột chưa tồn tại thì thêm
SET @dbname = 'checkin_new';
SET @tablename = 'users';
SET @columnname = 'avatar';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1;',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL AFTER full_name;')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Thêm avatar vào rental_car_management database
USE rental_car_management;

SET @dbname = 'rental_car_management';
SET @tablename = 'users';
SET @columnname = 'avatar';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1;',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL AFTER name;')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kiểm tra kết quả
SELECT 'Đã thêm cột avatar vào checkin_new.users' as message;
SELECT 'Đã thêm cột avatar vào rental_car_management.users' as message;

-- Hiển thị cấu trúc bảng
SHOW COLUMNS FROM checkin_new.users LIKE 'avatar';
SHOW COLUMNS FROM rental_car_management.users LIKE 'avatar';

