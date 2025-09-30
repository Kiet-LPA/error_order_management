<?php
require_once '../includes/config.php';
requireManager();

$page_title = 'Quản lý thuê xe - Manager';

$message = '';
$message_type = '';

// Handle rental cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $rental_id = intval($_POST['rental_id']);
    
    try {
        $pdo->beginTransaction();
        
        // Get rental details
        $stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = ? AND status = 'active'");
        $stmt->execute([$rental_id]);
        $rental = $stmt->fetch();
        
        if (!$rental) {
            throw new Exception('Không tìm thấy thuê xe hoặc thuê xe không hợp lệ!');
        }
        
        // Update rental status
        $stmt = $pdo->prepare("UPDATE rentals SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$rental_id]);
        
        // Update car status to available
        $stmt = $pdo->prepare("UPDATE cars SET status = 'active', available_from = NULL WHERE id = ?");
        $stmt->execute([$rental['car_id']]);
        
        $pdo->commit();
        $message = 'Hủy thuê xe thành công!';
        $message_type = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = 'Lỗi: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Get all rentals with user and car information
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.name as user_name, 
           u.email as user_email,
           c.license_plate, 
           c.car_type, 
           c.color,
           (SELECT COUNT(*) FROM rental_extensions WHERE rental_id = r.id AND status = 'pending') as pending_extensions
    FROM rentals r 
    JOIN users u ON r.user_id = u.id 
    JOIN cars c ON r.car_id = c.id 
    ORDER BY r.created_at DESC
");
$stmt->execute();
$rentals = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-list"></i> Quản lý thuê xe</h2>
        <hr>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Người thuê</th>
                                <th>Xe</th>
                                <th>Thời gian thuê</th>
                                <th>Trạng thái</th>
                                <th>Gia hạn</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rentals as $rental): ?>
                                <tr>
                                    <td><?php echo $rental['id']; ?></td>
                                    <td>
                                        <strong><?php echo sanitize($rental['user_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo sanitize($rental['user_email']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo sanitize($rental['car_type']); ?></strong><br>
                                        <small class="text-muted">
                                            <?php echo sanitize($rental['license_plate']); ?> - <?php echo sanitize($rental['color']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong>Bắt đầu:</strong> <?php echo formatDateTime($rental['rental_start']); ?><br>
                                        <strong>Kết thúc:</strong> <?php echo formatDateTime($rental['rental_end']); ?>
                                        <?php if ($rental['status'] === 'active'): ?>
                                            <?php
                                            $now = time();
                                            $endTime = strtotime($rental['rental_end']);
                                            if ($endTime < $now) {
                                                $overdue = $now - $endTime;
                                                $hours = floor($overdue / 3600);
                                                echo '<br><span class="badge bg-danger">Quá hạn ' . $hours . 'h</span>';
                                            } else {
                                                $timeLeft = $endTime - $now;
                                                $hours = floor($timeLeft / 3600);
                                                $minutes = floor(($timeLeft % 3600) / 60);
                                                echo '<br><span class="badge bg-success">Còn ' . $hours . 'h ' . $minutes . 'm</span>';
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        $statusText = '';
                                        switch ($rental['status']) {
                                            case 'active':
                                                $statusClass = 'success';
                                                $statusText = 'Đang thuê';
                                                break;
                                            case 'completed':
                                                $statusClass = 'primary';
                                                $statusText = 'Hoàn thành';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'danger';
                                                $statusText = 'Đã hủy';
                                                break;
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($rental['pending_extensions'] > 0): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> <?php echo $rental['pending_extensions']; ?> chờ duyệt
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewRental(<?php echo $rental['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($rental['status'] === 'active'): ?>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="cancelRental(<?php echo $rental['id']; ?>, '<?php echo sanitize($rental['user_name']); ?>')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Rental Modal -->
<div class="modal fade" id="viewRentalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết thuê xe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewRentalContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewRental(rentalId) {
    // This would typically load rental details via AJAX
    // For now, we'll just show a placeholder
    document.getElementById('viewRentalContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Đang tải thông tin thuê xe...</p>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('viewRentalModal')).show();
    
    // Simulate loading
    setTimeout(() => {
        document.getElementById('viewRentalContent').innerHTML = `
            <p>Chi tiết thuê xe ID: ${rentalId}</p>
            <p>Thông tin chi tiết sẽ được hiển thị ở đây.</p>
        `;
    }, 1000);
}

function cancelRental(rentalId, userName) {
    if (confirm(`Bạn có chắc chắn muốn hủy thuê xe của ${userName}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="cancel">
            <input type="hidden" name="rental_id" value="${rentalId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>

