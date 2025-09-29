<?php
// Simple configuration
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_start();

// Database connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=checkin_new;charset=utf8mb4', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $pdo;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = getDB();
    $stmt = $db->prepare("
        SELECT u.*, r.name as role_name, reg.name as region_name, 
               reg.latitude, reg.longitude, reg.radius_meters
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        LEFT JOIN regions reg ON u.region_id = reg.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Redirect based on role
function redirectByRole($role) {
    switch ($role) {
        case 'admin':
            header('Location: admin.php');
            break;
        case 'manager':
            header('Location: manager.php');
            break;
        case 'employee':
            header('Location: employee.php');
            break;
        default:
            header('Location: login.php');
    }
    exit;
}

// Calculate distance between two GPS points
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // meters
    
    $lat1Rad = deg2rad($lat1);
    $lon1Rad = deg2rad($lon1);
    $lat2Rad = deg2rad($lat2);
    $lon2Rad = deg2rad($lon2);
    
    $deltaLat = $lat2Rad - $lat1Rad;
    $deltaLon = $lon2Rad - $lon1Rad;
    
    $a = sin($deltaLat/2) * sin($deltaLat/2) +
         cos($lat1Rad) * cos($lat2Rad) *
         sin($deltaLon/2) * sin($deltaLon/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

// Get current session (morning/evening)
function getCurrentSession() {
    $hour = (int)date('H');
    
    if ($hour >= 4 && $hour <= 11) {
        return 'morning';
    } elseif ($hour >= 13 && $hour <= 20) {
        return 'evening';
    }
    
    return null;
}

// Check if user can check-in now
function canCheckIn() {
    return getCurrentSession() !== null;
}

// Generate GPS code for failed check-ins
function generateGPSCode($userId, $regionId) {
    return strtoupper(substr(md5($userId . '_' . $regionId . '_' . date('Y-m-d')), 0, 8));
}

// Send GPS request notification
function sendGPSRequest($userId, $regionId, $distance, $gpsCode) {
    $db = getDB();
    
    // Insert GPS request
    $stmt = $db->prepare("
        INSERT INTO gps_requests (user_id, region_id, request_date, distance_meters, gps_code, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ON DUPLICATE KEY UPDATE 
        distance_meters = VALUES(distance_meters),
        gps_code = VALUES(gps_code),
        created_at = NOW()
    ");
    
    $stmt->execute([$userId, $regionId, date('Y-m-d'), $distance, $gpsCode]);
    return $gpsCode;
}
?>
