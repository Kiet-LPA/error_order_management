<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'rental_car_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost/remaining_order_management');
define('SITE_NAME', 'Rental Car Management System');

// Session configuration
session_start();

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set charset for connection
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper functions
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isManager() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'manager';
}

function isEmployee() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'employee';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireManager() {
    requireLogin();
    if (!isManager()) {
        die('Access denied. Manager role required.');
    }
}

function requireEmployee() {
    requireLogin();
    if (!isEmployee()) {
        die('Access denied. Employee role required.');
    }
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>

