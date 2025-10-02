-- ===================================================================
-- CHECKIN HPFOODS - Complete Database Setup
-- Version: 1.0 (Production Ready)
-- Created: 2025-09-22
-- Description: Complete database setup for HP Foods Check-in System
-- ===================================================================

-- Drop and create database
DROP DATABASE IF EXISTS checkin_new;
CREATE DATABASE checkin_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE checkin_new;

-- ===================================================================
-- 1. CORE TABLES
-- ===================================================================

-- Roles table
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255)
);

-- Regions table (GPS areas for check-in)
CREATE TABLE regions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    radius_meters INT NOT NULL DEFAULT 200,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    region_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (region_id) REFERENCES regions(id)
);

-- Check-ins table (only successful check-ins are stored)
CREATE TABLE checkins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    region_id INT NOT NULL,
    checkin_date DATE NOT NULL,
    session ENUM('morning', 'evening') NOT NULL,
    checkin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    distance_meters DECIMAL(8, 2),
    ip_address VARCHAR(45),
    status ENUM('success', 'failed') DEFAULT 'success',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (region_id) REFERENCES regions(id),
    UNIQUE KEY unique_checkin (user_id, checkin_date, session)
);

-- GPS requests table (for failed check-in notifications)
CREATE TABLE gps_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    region_id INT NOT NULL,
    request_date DATE NOT NULL,
    distance_meters DECIMAL(8, 2) NOT NULL,
    gps_code VARCHAR(10) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (region_id) REFERENCES regions(id),
    UNIQUE KEY unique_request (user_id, request_date)
);

-- ===================================================================
-- 2. INDEXES FOR PERFORMANCE
-- ===================================================================

CREATE INDEX idx_checkins_user_date ON checkins(user_id, checkin_date);
CREATE INDEX idx_checkins_region ON checkins(region_id);
CREATE INDEX idx_checkins_status ON checkins(status);
CREATE INDEX idx_users_role ON users(role_id);
CREATE INDEX idx_users_region ON users(region_id);
CREATE INDEX idx_users_active ON users(is_active);
CREATE INDEX idx_gps_requests_status ON gps_requests(status);
CREATE INDEX idx_gps_requests_date ON gps_requests(request_date);
CREATE INDEX idx_gps_requests_user ON gps_requests(user_id);

-- ===================================================================
-- 3. INITIAL DATA SETUP
-- ===================================================================

-- Insert roles
INSERT INTO roles (name, description) VALUES
('admin', 'Administrator - Full system access, CRUD users & regions'),
('manager', 'Manager - Can fix attendance errors for employees'),
('employee', 'Employee - Check-in and view history only');

-- Insert regions with CORRECTED GPS coordinates
INSERT INTO regions (name, latitude, longitude, radius_meters, address) VALUES
('Khu vực Kiến Thành', 10.0259, 105.7692, 200, '19 Đường Châu Văn Liêm, Tân An, Ninh Kiều, Cần Thơ, Việt Nam'),
('Văn phòng Hà Nội', 21.0285, 105.8542, 200, 'Hà Nội, Việt Nam'),
('Văn phòng TP.HCM', 10.8231, 106.6297, 200, 'TP. Hồ Chí Minh, Việt Nam');

-- Insert default users (password: 123456 for all)
-- Note: Password hash for "123456" is $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (username, email, password, full_name, role_id, region_id) VALUES
('admin', 'admin@hpfoods.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator HP Foods', 1, NULL),
('manager1', 'manager1@hpfoods.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager Kiến Thành', 2, 1),
('emp001', 'emp001@hpfoods.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhân viên A', 3, 1),
('emp002', 'emp002@hpfoods.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhân viên B', 3, 1);

-- ===================================================================
-- 4. DATA CLEANUP & MAINTENANCE
-- ===================================================================

-- Clean up any existing failed check-ins (since we only store successful ones now)
DELETE FROM checkins WHERE status = 'failed';

-- ===================================================================
-- 5. VERIFICATION QUERIES
-- ===================================================================

-- Show all tables
SHOW TABLES;

-- Show user count by role
SELECT r.name as role_name, COUNT(u.id) as user_count 
FROM roles r 
LEFT JOIN users u ON r.id = u.role_id 
GROUP BY r.id, r.name;

-- Show regions with corrected coordinates
SELECT id, name, latitude, longitude, radius_meters, address 
FROM regions 
ORDER BY id;

-- Show initial setup summary
SELECT 
    (SELECT COUNT(*) FROM roles) as total_roles,
    (SELECT COUNT(*) FROM regions) as total_regions,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM checkins) as total_checkins,
    (SELECT COUNT(*) FROM gps_requests) as total_gps_requests;

-- ===================================================================
-- 6. NOTES FOR DEPLOYMENT
-- ===================================================================

/*
DEPLOYMENT CHECKLIST:
1. ✅ All tables created with proper relationships
2. ✅ Indexes added for performance
3. ✅ GPS coordinates corrected for Kiến Thành region
4. ✅ Only successful check-ins are stored (failed ones generate GPS requests)
5. ✅ Default admin user: admin/123456
6. ✅ UTF-8 encoding for Vietnamese text support
7. ✅ Timezone should be set to Asia/Ho_Chi_Minh in PHP

SECURITY REMINDERS:
- Change default passwords after deployment
- Update email domains to actual company domain
- Review and adjust region coordinates as needed
- Set up proper backup strategy

SYSTEM FEATURES:
- Admin: Full CRUD for users & regions, GPS location tool
- Manager: Fix attendance errors for employees
- Employee: Check-in and view history only
- Failed check-ins generate GPS request codes for Google Maps verification
*/

-- ===================================================================
-- END OF SETUP
-- ===================================================================
