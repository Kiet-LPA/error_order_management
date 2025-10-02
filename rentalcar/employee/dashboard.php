<?php
require_once '../includes/config.php';
requireEmployee();

$page_title = 'Dashboard - Employee';

// Get user's active rentals
$stmt = $pdo->prepare("
    SELECT r.*, c.license_plate, c.car_type, c.color 
    FROM rentals r 
    JOIN cars c ON r.car_id = c.id 
    WHERE r.user_id = ? AND r.status = 'active'
    ORDER BY r.rental_end ASC
");
$stmt->execute([$_SESSION['user_id']]);
$active_rentals = $stmt->fetchAll();

// Get user's rental history
$stmt = $pdo->prepare("
    SELECT r.*, c.license_plate, c.car_type, c.color 
    FROM rentals r 
    JOIN cars c ON r.car_id = c.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$rental_history = $stmt->fetchAll();

// Get pending extensions
$stmt = $pdo->prepare("
    SELECT re.*, c.license_plate, c.car_type 
    FROM rental_extensions re 
    JOIN rentals r ON re.rental_id = r.id 
    JOIN cars c ON r.car_id = c.id 
    WHERE r.user_id = ? AND re.status = 'pending'
    ORDER BY re.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$pending_extensions = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard - Employee</h2>
        <p class="text-muted">Xin chào, <?php echo sanitize($_SESSION['user_name']); ?>!</p>
        <hr>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Mượn xe mới</h5>
                <p class="card-text">Tìm và mượn xe có sẵn</p>
                <a href="cars.php" class="btn btn-primary">
                    <i class="fas fa-car"></i> Xem danh sách xe
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Lịch sử mượn xe</h5>
                <p class="card-text">Xem tất cả mượn xe của bạn</p>
                <a href="rentals.php" class="btn btn-info">
                    <i class="fas fa-list"></i> Xem lịch sử
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Active Rentals -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-car"></i> Mượn xe đang hoạt động</h5>
            </div>
            <div class="card-body">
                <?php if (empty($active_rentals)): ?>
                    <p class="text-muted">Bạn chưa có mượn xe nào đang hoạt động.</p>
                    <a href="cars.php" class="btn btn-primary btn-sm">Mượn xe ngay</a>
                <?php else: ?>
                    <?php foreach ($active_rentals as $rental): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="card-title"><?php echo sanitize($rental['car_type']); ?></h6>
                                        <p class="card-text">
                                            <strong>Biển số:</strong> <?php echo sanitize($rental['license_plate']); ?><br>
                                            <strong>Màu sắc:</strong> <?php echo sanitize($rental['color']); ?><br>
                                            <strong>Bắt đầu:</strong> <?php echo formatDateTime($rental['rental_start']); ?><br>
                                            <strong>Kết thúc:</strong> <?php echo formatDateTime($rental['rental_end']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <?php
                                        $now = time();
                                        $endTime = strtotime($rental['rental_end']);
                                        $timeLeft = $endTime - $now;
                                        
                                        if ($timeLeft > 0) {
                                            $hours = floor($timeLeft / 3600);
                                            $minutes = floor(($timeLeft % 3600) / 60);
                                            echo '<span class="badge bg-success">Còn ' . $hours . 'h ' . $minutes . 'm</span>';
                                        } else {
                                            echo '<span class="badge bg-danger">Quá hạn</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="rentals.php?id=<?php echo $rental['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Chi tiết
                                    </a>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="requestExtension(<?php echo $rental['id']; ?>)">
                                        <i class="fas fa-clock"></i> Gia hạn
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pending Extensions -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-clock"></i> Yêu cầu gia hạn đang chờ</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pending_extensions)): ?>
                    <p class="text-muted">Không có yêu cầu gia hạn nào đang chờ duyệt.</p>
                <?php else: ?>
                    <?php foreach ($pending_extensions as $extension): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo sanitize($extension['car_type']); ?></h6>
                                <p class="card-text">
                                    <strong>Biển số:</strong> <?php echo sanitize($extension['license_plate']); ?><br>
                                    <strong>Thời gian gia hạn:</strong> <?php echo formatDateTime($extension['new_rental_end']); ?><br>
                                    <strong>Lý do:</strong> <?php echo sanitize($extension['reason']); ?><br>
                                    <strong>Ngày gửi:</strong> <?php echo formatDateTime($extension['created_at']); ?>
                                </p>
                                <span class="badge bg-warning">Đang chờ duyệt</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Rental History -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Lịch sử mượn xe gần đây</h5>
            </div>
            <div class="card-body">
                <?php if (empty($rental_history)): ?>
                    <p class="text-muted">Chưa có lịch sử mượn xe.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Biển số</th>
                                    <th>Loại xe</th>
                                    <th>Màu sắc</th>
                                    <th>Bắt đầu</th>
                                    <th>Kết thúc</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rental_history as $rental): ?>
                                    <tr>
                                        <td><?php echo sanitize($rental['license_plate']); ?></td>
                                        <td><?php echo sanitize($rental['car_type']); ?></td>
                                        <td><?php echo sanitize($rental['color']); ?></td>
                                        <td><?php echo formatDateTime($rental['rental_start']); ?></td>
                                        <td><?php echo formatDateTime($rental['rental_end']); ?></td>
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
                                            <a href="rentals.php?id=<?php echo $rental['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="rentals.php" class="btn btn-outline-primary">Xem tất cả</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Extension Request Modal -->
<div class="modal fade" id="extensionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yêu cầu gia hạn mượn xe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="request_extension.php">
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

