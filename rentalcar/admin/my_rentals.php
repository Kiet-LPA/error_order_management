<?php
require_once '../includes/config.php';
requireManager();

$page_title = 'Lịch sử mượn xe của tôi - Manager';

// Get specific rental if ID is provided
$rental = null;
if (isset($_GET['id'])) {
    $rental_id = intval($_GET['id']);
    $stmt = $pdo->prepare("
        SELECT r.*, c.license_plate, c.car_type, c.color, c.description 
        FROM rentals r 
        JOIN cars c ON r.car_id = c.id 
        WHERE r.id = ? AND r.user_id = ?
    ");
    $stmt->execute([$rental_id, $_SESSION['user_id']]);
    $rental = $stmt->fetch();
    
    if (!$rental) {
        redirect('my_rentals.php');
    }
    
    // Get extensions for this rental
    $stmt = $pdo->prepare("
        SELECT re.*, u.name as approver_name 
        FROM rental_extensions re 
        LEFT JOIN users u ON re.approved_by = u.id 
        WHERE re.rental_id = ? 
        ORDER BY re.created_at DESC
    ");
    $stmt->execute([$rental_id]);
    $extensions = $stmt->fetchAll();
}

// Get all manager's rentals
$stmt = $pdo->prepare("
    SELECT r.*, c.license_plate, c.car_type, c.color 
    FROM rentals r 
    JOIN cars c ON r.car_id = c.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$rentals = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-list"></i> Lịch sử mượn xe của tôi</h2>
        <hr>
    </div>
</div>

<?php if ($rental): ?>
    <!-- Rental Detail View -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-car"></i> Chi tiết mượn xe #<?php echo $rental['id']; ?></h5>
                        <a href="my_rentals.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Thông tin xe</h6>
                            <p>
                                <strong>Biển số:</strong> <?php echo sanitize($rental['license_plate']); ?><br>
                                <strong>Loại xe:</strong> <?php echo sanitize($rental['car_type']); ?><br>
                                <strong>Màu sắc:</strong> <?php echo sanitize($rental['color']); ?><br>
                                <?php if ($rental['description']): ?>
                                    <strong>Mô tả:</strong> <?php echo sanitize($rental['description']); ?><br>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Thông tin mượn xe</h6>
                            <p>
                                <strong>Bắt đầu:</strong> <?php echo formatDateTime($rental['rental_start']); ?><br>
                                <strong>Kết thúc:</strong> <?php echo formatDateTime($rental['rental_end']); ?><br>
                                <strong>Trạng thái:</strong> 
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
                                <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span><br>
                                <strong>Ngày tạo:</strong> <?php echo formatDateTime($rental['created_at']); ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($rental['notes']): ?>
                        <div class="row">
                            <div class="col-12">
                                <h6>Ghi chú</h6>
                                <div class="bg-light p-3 rounded">
                                    <?php echo nl2br(sanitize($rental['notes'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($rental['status'] === 'active'): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-warning" onclick="requestExtension(<?php echo $rental['id']; ?>)">
                                        <i class="fas fa-clock"></i> Yêu cầu gia hạn
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Extensions History -->
    <?php if (!empty($extensions)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-clock"></i> Lịch sử gia hạn</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($extensions as $extension): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6>Yêu cầu gia hạn đến <?php echo formatDateTime($extension['new_rental_end']); ?></h6>
                                            <p class="mb-2">
                                                <strong>Lý do:</strong> <?php echo sanitize($extension['reason']); ?>
                                            </p>
                                            <p class="mb-0">
                                                <strong>Ngày gửi:</strong> <?php echo formatDateTime($extension['created_at']); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <?php
                                            $statusClass = '';
                                            $statusText = '';
                                            switch ($extension['status']) {
                                                case 'pending':
                                                    $statusClass = 'warning';
                                                    $statusText = 'Đang chờ duyệt';
                                                    break;
                                                case 'approved':
                                                    $statusClass = 'success';
                                                    $statusText = 'Đã duyệt';
                                                    break;
                                                case 'rejected':
                                                    $statusClass = 'danger';
                                                    $statusText = 'Đã từ chối';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            
                                            <?php if ($extension['status'] !== 'pending'): ?>
                                                <p class="mt-2 mb-0">
                                                    <small class="text-muted">
                                                        <?php if ($extension['approver_name']): ?>
                                                            Bởi: <?php echo sanitize($extension['approver_name']); ?><br>
                                                        <?php endif; ?>
                                                        <?php echo formatDateTime($extension['approved_at']); ?>
                                                    </small>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($extension['status'] === 'rejected' && $extension['rejection_reason']): ?>
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="alert alert-danger">
                                                    <strong>Lý do từ chối:</strong> <?php echo sanitize($extension['rejection_reason']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
<?php else: ?>
    <!-- Rental List View -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($rentals)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-car fa-3x text-muted mb-3"></i>
                            <h5>Chưa có lịch sử mượn xe</h5>
                            <p class="text-muted">Bạn chưa có mượn xe nào. Hãy bắt đầu mượn xe đầu tiên!</p>
                            <a href="rent_car.php" class="btn btn-primary">
                                <i class="fas fa-car"></i> Mượn xe ngay
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Xe</th>
                                        <th>Thời gian thuê</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rentals as $rental): ?>
                                        <tr>
                                            <td><?php echo $rental['id']; ?></td>
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
                                                <a href="my_rentals.php?id=<?php echo $rental['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Chi tiết
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Extension Request Modal -->
<div class="modal fade" id="extensionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yêu cầu gia hạn mượn xe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../employee/request_extension.php">
                <div class="modal-body">
                    <input type="hidden" name="rental_id" id="extension_rental_id">
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Lý do gia hạn *</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required placeholder="Vui lòng nêu rõ lý do cần gia hạn..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_rental_end" class="form-label">Thời gian trả xe mới *</label>
                        <input type="datetime-local" class="form-control" id="new_rental_end" name="new_rental_end" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function requestExtension(rentalId) {
    document.getElementById('extension_rental_id').value = rentalId;
    new bootstrap.Modal(document.getElementById('extensionModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>
