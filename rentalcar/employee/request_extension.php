<?php
require_once '../includes/config.php';
requireEmployee();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rental_id = intval($_POST['rental_id']);
    $reason = sanitize($_POST['reason']);
    $new_rental_end = sanitize($_POST['new_rental_end']);
    
    // Validate rental belongs to user
    $stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = ? AND user_id = ? AND status = 'active'");
    $stmt->execute([$rental_id, $_SESSION['user_id']]);
    $rental = $stmt->fetch();
    
    if (!$rental) {
        $message = 'Không tìm thấy thuê xe hoặc thuê xe không hợp lệ!';
        $message_type = 'danger';
    } else {
        // Check if new end time is after current end time
        $currentEndTime = strtotime($rental['rental_end']);
        $newEndTime = strtotime($new_rental_end);
        
        if ($newEndTime <= $currentEndTime) {
            $message = 'Thời gian gia hạn phải sau thời gian trả xe hiện tại!';
            $message_type = 'danger';
        } else {
            // Check if there's already a pending extension
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rental_extensions WHERE rental_id = ? AND status = 'pending'");
            $stmt->execute([$rental_id]);
            $hasPendingExtension = $stmt->fetch()['count'] > 0;
            
            if ($hasPendingExtension) {
                $message = 'Đã có yêu cầu gia hạn đang chờ duyệt!';
                $message_type = 'danger';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO rental_extensions (rental_id, reason, new_rental_end) VALUES (?, ?, ?)");
                    $stmt->execute([$rental_id, $reason, $new_rental_end]);
                    
                    $message = 'Gửi yêu cầu gia hạn thành công! Vui lòng chờ manager duyệt.';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Lỗi: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            }
        }
    }
}

// Redirect back to dashboard with message
$_SESSION['message'] = $message;
$_SESSION['message_type'] = $message_type;
redirect('dashboard.php');
?>

