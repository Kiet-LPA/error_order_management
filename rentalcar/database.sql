-- Database: rental_car_management
CREATE DATABASE IF NOT EXISTS rental_car_management;
USE rental_car_management;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('employee', 'manager') DEFAULT 'employee',
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cars table
CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_plate VARCHAR(20) UNIQUE NOT NULL,
    weight DECIMAL(8,2) NOT NULL,
    car_type VARCHAR(100) NOT NULL,
    color VARCHAR(50) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive', 'rented') DEFAULT 'active',
    available_from TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Rentals table
CREATE TABLE rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    rental_start TIMESTAMP NOT NULL,
    rental_end TIMESTAMP NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

-- Rental extensions table
CREATE TABLE rental_extensions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rental_id INT NOT NULL,
    reason TEXT NOT NULL,
    new_rental_end TIMESTAMP NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES rentals(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default manager account
INSERT INTO users (name, email, password, role, phone, address) VALUES 
('Admin Manager', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', '0123456789', '123 Main Street');

-- Insert sample cars
INSERT INTO cars (license_plate, weight, car_type, color, description) VALUES 
('30A-12345', 1500.00, 'Sedan', 'Trắng', 'Xe sedan 4 chỗ, tiết kiệm nhiên liệu'),
('30A-67890', 2000.00, 'SUV', 'Đen', 'Xe SUV 7 chỗ, phù hợp gia đình'),
('30A-11111', 1200.00, 'Hatchback', 'Xanh', 'Xe hatchback nhỏ gọn, dễ lái'),
('30A-22222', 1800.00, 'Sedan', 'Bạc', 'Xe sedan cao cấp, đầy đủ tiện nghi'),
('30A-33333', 2200.00, 'SUV', 'Trắng', 'Xe SUV 4WD, mạnh mẽ');

