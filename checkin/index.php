<?php
require_once 'config.php';

// Redirect to appropriate page based on login status
if (isLoggedIn()) {
    redirectByRole($_SESSION['role']);
} else {
    header('Location: login.php');
    exit;
}
?>
