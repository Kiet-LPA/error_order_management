-- ===================================================================
-- Script thêm dữ liệu test có Avatar
-- Để xem avatar hiển thị trong Checkin và RentalCar
-- ===================================================================

-- ===================================================================
-- 1. CHECKIN SYSTEM - Thêm users với avatar
-- ===================================================================
USE checkin_new;

-- Thêm users test với avatar
INSERT INTO users (username, email, password, full_name, role_id, region_id, avatar, is_active) VALUES
('test_user1', 'testuser1@hpfoods.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn Test', 3, 1, NULL, 1),
('test_user2', 'testuser2@hpfoods.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Demo', 3, 1, NULL, 1),
('test_manager', 'testmanager@hpfoods.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Văn Quản Lý', 2, 1, NULL, 1)
ON DUPLICATE KEY UPDATE 
    full_name = VALUES(full_name),
    is_active = VALUES(is_active);

-- Lấy ID của users vừa tạo
SET @test_user1_id = (SELECT id FROM users WHERE username = 'test_user1' LIMIT 1);
SET @test_user2_id = (SELECT id FROM users WHERE username = 'test_user2' LIMIT 1);

-- Thêm dữ liệu điểm danh (checkins) cho 30 ngày qua
INSERT INTO checkins (user_id, region_id, checkin_date, session, checkin_time, latitude, longitude, distance_meters, status, notes)
SELECT 
    @test_user1_id,
    1,
    DATE_SUB(CURDATE(), INTERVAL d.day DAY),
    CASE WHEN MOD(d.day, 2) = 0 THEN 'morning' ELSE 'evening' END,
    DATE_SUB(NOW(), INTERVAL d.day DAY),
    10.0259,
    105.7692,
    FLOOR(50 + (RAND() * 100)),
    'success',
    CONCAT('Điểm danh test ngày ', DATE_SUB(CURDATE(), INTERVAL d.day DAY))
FROM (
    SELECT 0 as day UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION 
    SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
    SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION
    SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
    SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION
    SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
) d
WHERE DATE_SUB(CURDATE(), INTERVAL d.day DAY) NOT IN (
    SELECT checkin_date FROM checkins WHERE user_id = @test_user1_id
)
LIMIT 20;

-- Thêm 1 GPS request để test
INSERT INTO gps_requests (user_id, region_id, request_date, distance_meters, gps_code, status, admin_notes)
VALUES 
    (@test_user2_id, 1, CURDATE(), 350.5, 'TEST1234', 'pending', NULL)
ON DUPLICATE KEY UPDATE 
    distance_meters = VALUES(distance_meters),
    gps_code = VALUES(gps_code);

-- ===================================================================
-- 2. RENTAL CAR SYSTEM - Thêm users với avatar  
-- ===================================================================
USE rental_car_management;

-- Kiểm tra và tạo bảng nếu chưa có
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('manager', 'employee') DEFAULT 'employee',
    avatar VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cars (
    id INT PRIMARY KEY AUTO_INCREMENT,
    license_plate VARCHAR(20) UNIQUE NOT NULL,
    car_type VARCHAR(100) NOT NULL,
    color VARCHAR(50) NOT NULL,
    status ENUM('active', 'rented', 'maintenance') DEFAULT 'active',
    available_from DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rentals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    rental_start DATETIME NOT NULL,
    rental_end DATETIME NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (car_id) REFERENCES cars(id)
);

CREATE TABLE IF NOT EXISTS rental_extensions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rental_id INT NOT NULL,
    extension_start DATETIME NOT NULL,
    extension_end DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES rentals(id)
);

-- Thêm users test
INSERT INTO users (username, email, password, name, role, avatar) VALUES
('test_emp1', 'testemp1@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Văn Xe', 'employee', NULL),
('test_emp2', 'testemp2@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hoàng Thị Thuê', 'employee', NULL),
('test_mgr', 'testmgr@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vũ Văn Quản Lý', 'manager', NULL)
ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    role = VALUES(role);

-- Thêm xe test
INSERT INTO cars (license_plate, car_type, color, status) VALUES
('92A-12345', 'Toyota Vios', 'Trắng', 'active'),
('92B-67890', 'Honda City', 'Đen', 'active'),
('92C-11111', 'Mazda 3', 'Xanh', 'rented')
ON DUPLICATE KEY UPDATE 
    car_type = VALUES(car_type),
    color = VALUES(color);

-- Lấy ID
SET @rental_user1_id = (SELECT id FROM users WHERE username = 'test_emp1' LIMIT 1);
SET @rental_user2_id = (SELECT id FROM users WHERE username = 'test_emp2' LIMIT 1);
SET @car1_id = (SELECT id FROM cars WHERE license_plate = '92A-12345' LIMIT 1);
SET @car2_id = (SELECT id FROM cars WHERE license_plate = '92B-67890' LIMIT 1);
SET @car3_id = (SELECT id FROM cars WHERE license_plate = '92C-11111' LIMIT 1);

-- Thêm rental records
INSERT INTO rentals (user_id, car_id, rental_start, rental_end, status, notes) VALUES
(@rental_user1_id, @car1_id, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 2 DAY), 'active', 'Thuê xe đi công tác'),
(@rental_user2_id, @car3_id, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY), 'active', 'Thuê xe đi gặp khách hàng'),
(@rental_user1_id, @car2_id, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), 'completed', 'Đã hoàn thành thuê xe')
ON DUPLICATE KEY UPDATE 
    status = VALUES(status);

-- Cập nhật trạng thái xe
UPDATE cars SET status = 'rented' WHERE id IN (@car1_id, @car3_id);

-- Thêm extension request
SET @rental_id = (SELECT id FROM rentals WHERE user_id = @rental_user2_id AND status = 'active' LIMIT 1);
INSERT INTO rental_extensions (rental_id, extension_start, extension_end, status, reason)
VALUES (@rental_id, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY), 'pending', 'Cần thêm thời gian để hoàn thành công việc')
ON DUPLICATE KEY UPDATE status = VALUES(status);

-- ===================================================================
-- 3. Hiển thị kết quả
-- ===================================================================

-- Checkin users
SELECT '=== CHECKIN USERS ===' as '----------------';
SELECT id, username, full_name, email, 
       CASE WHEN avatar IS NOT NULL THEN '✅ Có avatar' ELSE '❌ Chưa có avatar' END as avatar_status,
       role_id, is_active
FROM checkin_new.users 
WHERE username LIKE 'test%'
ORDER BY id;

-- Checkin records
SELECT '=== CHECKIN RECORDS ===' as '----------------';
SELECT COUNT(*) as total_checkins, 
       MIN(checkin_date) as first_date, 
       MAX(checkin_date) as last_date
FROM checkin_new.checkins 
WHERE user_id IN (SELECT id FROM checkin_new.users WHERE username LIKE 'test%');

-- GPS Requests
SELECT '=== GPS REQUESTS ===' as '----------------';
SELECT gr.id, u.full_name, gr.request_date, gr.distance_meters, gr.gps_code, gr.status
FROM checkin_new.gps_requests gr
JOIN checkin_new.users u ON gr.user_id = u.id
WHERE u.username LIKE 'test%';

-- Rental users
SELECT '=== RENTAL CAR USERS ===' as '----------------';
SELECT id, username, name, email,
       CASE WHEN avatar IS NOT NULL THEN '✅ Có avatar' ELSE '❌ Chưa có avatar' END as avatar_status,
       role
FROM rental_car_management.users 
WHERE username LIKE 'test%'
ORDER BY id;

-- Rental records
SELECT '=== RENTAL RECORDS ===' as '----------------';
SELECT r.id, u.name as user_name, c.license_plate, c.car_type, 
       r.rental_start, r.rental_end, r.status
FROM rental_car_management.rentals r
JOIN rental_car_management.users u ON r.user_id = u.id
JOIN rental_car_management.cars c ON r.car_id = c.id
WHERE u.username LIKE 'test%'
ORDER BY r.created_at DESC;

-- ===================================================================
-- 4. Hướng dẫn tiếp theo
-- ===================================================================
SELECT '
╔══════════════════════════════════════════════════════════════╗
║  ✅ ĐÃ TẠO DỮ LIỆU TEST THÀNH CÔNG!                         ║
╚══════════════════════════════════════════════════════════════╝

📝 THÔNG TIN ĐĂNG NHẬP (Password: 123456):
   
   🔹 CHECKIN:
      - test_user1 / testuser1@hpfoods.com (Employee)
      - test_user2 / testuser2@hpfoods.com (Employee)  
      - test_manager / testmanager@hpfoods.com (Manager)
   
   🔹 RENTAL CAR:
      - test_emp1 / testemp1@company.com (Employee)
      - test_emp2 / testemp2@company.com (Employee)
      - test_mgr / testmgr@company.com (Manager)

🚀 BƯỚC TIẾP THEO - Đồng bộ Avatar:

   1️⃣  Chạy command đồng bộ avatar:
       php artisan sync:avatars
   
   2️⃣  Hoặc đồng bộ thủ công trong Laravel:
       - Tạo user trong Laravel với email giống checkin/rentalcar
       - Upload avatar cho user đó
       - Avatar sẽ tự động đồng bộ
   
   3️⃣  Xem kết quả:
       - Checkin: http://localhost/checkin/reports.php
       - RentalCar: http://localhost/rentalcar/admin/rentals.php

💡 LƯU Ý:
   - Avatar sẽ hiển thị sau khi chạy sync:avatars
   - Nếu chưa có avatar trong Laravel, hệ thống tự tạo SVG
   - SVG avatar dùng chữ cái đầu của tên

' as INSTRUCTIONS;

