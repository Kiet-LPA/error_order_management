<?php
require_once '../includes/config.php';
requireEmployee();

$page_title = 'Mượn xe - Employee';

$message = '';
$message_type = '';

// Handle rental request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = intval($_POST['car_id']);
    $rental_start = sanitize($_POST['rental_start']);
    $rental_end = sanitize($_POST['rental_end']);
    $notes = sanitize($_POST['notes']);
    
    // Validate dates
    $startTime = strtotime($rental_start);
    $endTime = strtotime($rental_end);
    $now = time();
    
    if ($startTime < $now) {
        $message = 'Thời gian bắt đầu thuê phải sau thời điểm hiện tại!';
        $message_type = 'danger';
    } elseif ($endTime <= $startTime) {
        $message = 'Thời gian kết thúc phải sau thời gian bắt đầu!';
        $message_type = 'danger';
    } else {
        // Check if car is available
        $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND status = 'active'");
        $stmt->execute([$car_id]);
        $car = $stmt->fetch();
        
        if (!$car) {
            $message = 'Xe không khả dụng để thuê!';
            $message_type = 'danger';
        } elseif ($car['available_from'] && $startTime < strtotime($car['available_from'])) {
            $message = 'Xe này chỉ có thể thuê từ ' . formatDateTime($car['available_from']) . '!';
            $message_type = 'danger';
        } else {
            try {
                // Create rental
                $stmt = $pdo->prepare("INSERT INTO rentals (user_id, car_id, rental_start, rental_end, notes) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $car_id, $rental_start, $rental_end, $notes]);
                
                // Update car status
                $availableFrom = date('Y-m-d H:i:s', $endTime + (6 * 3600)); // 6 hours buffer
                $stmt = $pdo->prepare("UPDATE cars SET status = 'rented', available_from = ? WHERE id = ?");
                $stmt->execute([$availableFrom, $car_id]);
                
                $message = 'Mượn xe thành công!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Lỗi: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }
}

// Get available cars
$stmt = $pdo->prepare("
    SELECT * FROM cars 
    WHERE status = 'active' 
    AND (available_from IS NULL OR available_from <= NOW())
    ORDER BY created_at DESC
");
$stmt->execute();
$cars = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-car"></i> Mượn xe</h2>
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
    <?php if (empty($cars)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> Không có xe nào khả dụng</h5>
                <p>Hiện tại không có xe nào có thể thuê. Vui lòng thử lại sau.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($cars as $car): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo sanitize($car['car_type']); ?></h5>
                        <p class="card-text">
                            <strong>Biển số:</strong> <?php echo sanitize($car['license_plate']); ?><br>
                            <strong>Màu sắc:</strong> <?php echo sanitize($car['color']); ?><br>
                            <strong>Trọng lượng:</strong> <?php echo number_format($car['weight'], 2); ?> kg<br>
                            <?php if ($car['description']): ?>
                                <strong>Mô tả:</strong> <?php echo sanitize($car['description']); ?><br>
                            <?php endif; ?>
                            <?php if ($car['available_from']): ?>
                                <strong>Có thể thuê từ:</strong> <?php echo formatDateTime($car['available_from']); ?>
                            <?php else: ?>
                                <span class="badge bg-success">Có sẵn ngay</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary w-100" onclick="rentCar(<?php echo htmlspecialchars(json_encode($car)); ?>)">
                            <i class="fas fa-car"></i> Mượn xe này
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Rental Modal -->
<div class="modal fade" id="rentalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mượn xe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" onsubmit="return validateRentalForm()">
                <div class="modal-body">
                    <input type="hidden" name="car_id" id="rental_car_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Thông tin xe</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 id="rental_car_type"></h6>
                                <p class="mb-0">
                                    <strong>Biển số:</strong> <span id="rental_license_plate"></span><br>
                                    <strong>Màu sắc:</strong> <span id="rental_color"></span><br>
                                    <strong>Trọng lượng:</strong> <span id="rental_weight"></span> kg
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rental_start" class="form-label">Thời gian bắt đầu thuê *</label>
                        <input type="datetime-local" class="form-control" id="rental_start" name="rental_start" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rental_end" class="form-label">Thời gian trả xe *</label>
                        <input type="datetime-local" class="form-control" id="rental_end" name="rental_end" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Ghi chú thêm (không bắt buộc)"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong> Sau khi trả xe, xe sẽ có thời gian nghỉ 6 tiếng trước khi có thể thuê lại.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Xác nhận mượn xe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function rentCar(car) {
    document.getElementById('rental_car_id').value = car.id;
    document.getElementById('rental_car_type').textContent = car.car_type;
    document.getElementById('rental_license_plate').textContent = car.license_plate;
    document.getElementById('rental_color').textContent = car.color;
    document.getElementById('rental_weight').textContent = parseFloat(car.weight).toFixed(2);
    
    // Set minimum date
    const now = new Date();
    const minDate = now.toISOString().slice(0, 16);
    document.getElementById('rental_start').min = minDate;
    document.getElementById('rental_end').min = minDate;
    
    // If car has available_from, set it as minimum start time
    if (car.available_from) {
        const availableFrom = new Date(car.available_from);
        if (availableFrom > now) {
            document.getElementById('rental_start').min = availableFrom.toISOString().slice(0, 16);
        }
    }
    
    new bootstrap.Modal(document.getElementById('rentalModal')).show();
}

// Auto-update end time when start time changes
document.getElementById('rental_start').addEventListener('change', function() {
    const startTime = new Date(this.value);
    const endTime = new Date(startTime.getTime() + (2 * 60 * 60 * 1000)); // Add 2 hours
    document.getElementById('rental_end').value = endTime.toISOString().slice(0, 16);
    document.getElementById('rental_end').min = this.value;
});
</script>

<?php include '../includes/footer.php'; ?>

