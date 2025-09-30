<?php
require_once '../includes/config.php';
requireManager();

$page_title = 'Duyệt gia hạn - Manager';

$message = '';
$message_type = '';

// Handle extension approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $extension_id = intval($_POST['extension_id']);
        
        // Get extension details
        $stmt = $pdo->prepare("
            SELECT re.*, r.user_id, r.car_id, r.rental_end as current_end
            FROM rental_extensions re 
            JOIN rentals r ON re.rental_id = r.id 
            WHERE re.id = ? AND re.status = 'pending'
        ");
        $stmt->execute([$extension_id]);
        $extension = $stmt->fetch();
        
        if (!$extension) {
            $message = 'Không tìm thấy yêu cầu gia hạn!';
            $message_type = 'danger';
        } else {
            switch ($_POST['action']) {
                case 'approve':
                    try {
                        $pdo->beginTransaction();
                        
                        // Update extension status
                        $stmt = $pdo->prepare("UPDATE rental_extensions SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id'], $extension_id]);
                        
                        // Update rental end time
                        $stmt = $pdo->prepare("UPDATE rentals SET rental_end = ? WHERE id = ?");
                        $stmt->execute([$extension['new_rental_end'], $extension['rental_id']]);
                        
                        // Update car available time (add 6 hours buffer)
                        $newAvailableTime = date('Y-m-d H:i:s', strtotime($extension['new_rental_end']) + (6 * 3600));
                        $stmt = $pdo->prepare("UPDATE cars SET available_from = ? WHERE id = ?");
                        $stmt->execute([$newAvailableTime, $extension['car_id']]);
                        
                        $pdo->commit();
                        $message = 'Duyệt gia hạn thành công!';
                        $message_type = 'success';
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        $message = 'Lỗi: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                    break;
                    
                case 'reject':
                    $rejection_reason = sanitize($_POST['rejection_reason']);
                    
                    try {
                        $stmt = $pdo->prepare("UPDATE rental_extensions SET status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id'], $rejection_reason, $extension_id]);
                        
                        $message = 'Từ chối gia hạn thành công!';
                        $message_type = 'success';
                    } catch (PDOException $e) {
                        $message = 'Lỗi: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                    break;
            }
        }
    }
}

// Get pending extensions
$stmt = $pdo->prepare("
    SELECT re.*, 
           r.rental_start, 
           r.rental_end as current_end,
           u.name as user_name, 
           u.email as user_email,
           c.license_plate, 
           c.car_type, 
           c.color
    FROM rental_extensions re 
    JOIN rentals r ON re.rental_id = r.id 
    JOIN users u ON r.user_id = u.id 
    JOIN cars c ON r.car_id = c.id 
    WHERE re.status = 'pending'
    ORDER BY re.created_at ASC
");
$stmt->execute();
$extensions = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-clock"></i> Duyệt gia hạn thuê xe</h2>
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
        <?php if (empty($extensions)): ?>
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> Không có yêu cầu gia hạn nào</h5>
                <p>Hiện tại không có yêu cầu gia hạn nào đang chờ duyệt.</p>
            </div>
        <?php else: ?>
            <?php foreach ($extensions as $extension): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-0">
                                    <i class="fas fa-clock text-warning"></i>
                                    Yêu cầu gia hạn từ <?php echo sanitize($extension['user_name']); ?>
                                </h5>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-warning">Đang chờ duyệt</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Thông tin xe</h6>
                                <p>
                                    <strong>Biển số:</strong> <?php echo sanitize($extension['license_plate']); ?><br>
                                    <strong>Loại xe:</strong> <?php echo sanitize($extension['car_type']); ?><br>
                                    <strong>Màu sắc:</strong> <?php echo sanitize($extension['color']); ?>
                                </p>
                                
                                <h6>Thông tin người thuê</h6>
                                <p>
                                    <strong>Tên:</strong> <?php echo sanitize($extension['user_name']); ?><br>
                                    <strong>Email:</strong> <?php echo sanitize($extension['user_email']); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Thời gian thuê xe</h6>
                                <p>
                                    <strong>Bắt đầu:</strong> <?php echo formatDateTime($extension['rental_start']); ?><br>
                                    <strong>Kết thúc hiện tại:</strong> <?php echo formatDateTime($extension['current_end']); ?><br>
                                    <strong>Kết thúc mới:</strong> 
                                    <span class="text-primary fw-bold"><?php echo formatDateTime($extension['new_rental_end']); ?></span>
                                </p>
                                
                                <h6>Lý do gia hạn</h6>
                                <div class="bg-light p-3 rounded">
                                    <?php echo nl2br(sanitize($extension['reason'])); ?>
                                </div>
                                
                                <p class="text-muted mt-2">
                                    <small>
                                        <i class="fas fa-calendar"></i>
                                        Yêu cầu gửi lúc: <?php echo formatDateTime($extension['created_at']); ?>
                                    </small>
                                </p>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success" onclick="approveExtension(<?php echo $extension['id']; ?>)">
                                        <i class="fas fa-check"></i> Duyệt
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="rejectExtension(<?php echo $extension['id']; ?>)">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Từ chối gia hạn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="extension_id" id="reject_extension_id">
                    
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Lý do từ chối *</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="Vui lòng nêu rõ lý do từ chối gia hạn..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Từ chối gia hạn</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveExtension(extensionId) {
    if (confirm('Bạn có chắc chắn muốn duyệt gia hạn này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="extension_id" value="${extensionId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectExtension(extensionId) {
    document.getElementById('reject_extension_id').value = extensionId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>

