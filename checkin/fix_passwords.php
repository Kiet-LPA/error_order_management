<?php
// Fix passwords for all users
require_once 'config.php';

$db = getDB();

// New password: 123456
$newPassword = '123456';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

echo "Updating passwords...\n";

// Update all users with the same password
$stmt = $db->prepare("UPDATE users SET password = ? WHERE 1=1");
$result = $stmt->execute([$hashedPassword]);

if ($result) {
    echo "✅ Successfully updated passwords for all users\n";
    echo "Password for all accounts: 123456\n";
    
    // Verify by checking one user
    $stmt = $db->prepare("SELECT username, password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if (password_verify('123456', $admin['password'])) {
        echo "✅ Password verification successful for admin\n";
    } else {
        echo "❌ Password verification failed\n";
    }
    
    echo "\nAccounts:\n";
    echo "- admin / 123456\n";
    echo "- manager1 / 123456\n";
    echo "- emp001 / 123456\n";
    echo "- emp002 / 123456\n";
} else {
    echo "❌ Failed to update passwords\n";
}
?>
