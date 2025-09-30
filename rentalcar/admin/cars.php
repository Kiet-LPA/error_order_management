<?php
require_once '../includes/config.php';
requireManager();

$page_title = 'Quản lý xe - Manager';

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $license_plate = sanitize($_POST['license_plate']);
                $weight = floatval($_POST['weight']);
                $car_type = sanitize($_POST['car_type']);
                $color = sanitize($_POST['color']);
                $description = sanitize($_POST['description']);
                $status = sanitize($_POST['status']);
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO cars (license_plate, weight, car_type, color, description, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$license_plate, $weight, $car_type, $color, $description, $status]);
                    $message = 'Thêm xe thành công!';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Lỗi: ' . $e->getMessage();
                    $message_type = 'danger';
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $license_plate = sanitize($_POST['license_plate']);
                $weight = floatval($_POST['weight']);
                $car_type = sanitize($_POST['car_type']);
                $color = sanitize($_POST['color']);
                $description = sanitize($_POST['description']);
                $status = sanitize($_POST['status']);
                
                try {
                    $stmt = $pdo->prepare("UPDATE cars SET license_plate = ?, weight = ?, car_type = ?, color = ?, description = ?, status = ? WHERE id = ?");
                    $stmt->execute([$license_plate, $weight, $car_type, $color, $description, $status, $id]);
                    $message = 'Cập nhật xe thành công!';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Lỗi: ' . $e->getMessage();
                    $message_type = 'danger';
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                
                // Check if car has active rentals
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rentals WHERE car_id = ? AND status = 'active'");
                $stmt->execute([$id]);
                $hasActiveRentals = $stmt->fetch()['count'] > 0;
                
                if ($hasActiveRentals) {
                    $message = 'Không thể xóa xe đang được thuê!';
                    $message_type = 'danger';
                } else {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
                        $stmt->execute([$id]);
                        $message = 'Xóa xe thành công!';
                        $message_type = 'success';
                    } catch (PDOException $e) {
                        $message = 'Lỗi: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
                break;
        }
    }
}

// Get cars with rental information
$stmt = $pdo->prepare("
    SELECT c.*, 
           r.id as rental_id, 
           r.rental_start, 
           r.rental_end, 
           r.status as rental_status,
           u.name as renter_name
    FROM cars c
    LEFT JOIN rentals r ON c.id = r.car_id AND r.status = 'active'
    LEFT JOIN users u ON r.user_id = u.id
    ORDER BY c.created_at DESC
");
$stmt->execute();
$cars = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-car"></i> Quản lý xe</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCarModal">
                <i class="fas fa-plus"></i> Thêm xe mới
            </button>
        </div>
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
                                <th>Biển số</th>
                                <th>Loại xe</th>
                                <th>Màu sắc</th>
                                <th>Trọng lượng (kg)</th>
                                <th>Trạng thái</th>
                                <th>Người thuê</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $car): ?>
                                <tr>
                                    <td><?php echo $car['id']; ?></td>
                                    <td><?php echo sanitize($car['license_plate']); ?></td>
                                    <td><?php echo sanitize($car['car_type']); ?></td>
                                    <td><?php echo sanitize($car['color']); ?></td>
                                    <td><?php echo number_format($car['weight'], 2); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        $statusText = '';
                                        switch ($car['status']) {
                                            case 'active':
                                                $statusClass = 'success';
                                                $statusText = 'Hoạt động';
                                                break;
                                            case 'inactive':
                                                $statusClass = 'danger';
                                                $statusText = 'Không hoạt động';
                                                break;
                                            case 'rented':
                                                $statusClass = 'warning';
                                                $statusText = 'Đang thuê';
                                                break;
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($car['renter_name']): ?>
                                            <?php echo sanitize($car['renter_name']); ?>
                                            <br><small class="text-muted">
                                                Đến: <?php echo formatDateTime($car['rental_end']); ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewCar(<?php echo $car['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editCar(<?php echo htmlspecialchars(json_encode($car)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteCar(<?php echo $car['id']; ?>, '<?php echo sanitize($car['license_plate']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Add Car Modal -->
<div class="modal fade" id="addCarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm xe mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="license_plate" class="form-label">Biển số *</label>
                        <input type="text" class="form-control" id="license_plate" name="license_plate" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="car_type" class="form-label">Loại xe *</label>
                        <input type="text" class="form-control" id="car_type" name="car_type" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="color" class="form-label">Màu sắc *</label>
                        <input type="text" class="form-control" id="color" name="color" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="weight" class="form-label">Trọng lượng (kg) *</label>
                        <input type="number" step="0.01" class="form-control" id="weight" name="weight" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái *</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Không hoạt động</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm xe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Car Modal -->
<div class="modal fade" id="editCarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sửa thông tin xe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_license_plate" class="form-label">Biển số *</label>
                        <input type="text" class="form-control" id="edit_license_plate" name="license_plate" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_car_type" class="form-label">Loại xe *</label>
                        <input type="text" class="form-control" id="edit_car_type" name="car_type" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_color" class="form-label">Màu sắc *</label>
                        <input type="text" class="form-control" id="edit_color" name="color" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_weight" class="form-label">Trọng lượng (kg) *</label>
                        <input type="number" step="0.01" class="form-control" id="edit_weight" name="weight" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Trạng thái *</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Không hoạt động</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Car Modal -->
<div class="modal fade" id="viewCarModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết xe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewCarContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function editCar(car) {
    document.getElementById('edit_id').value = car.id;
    document.getElementById('edit_license_plate').value = car.license_plate;
    document.getElementById('edit_car_type').value = car.car_type;
    document.getElementById('edit_color').value = car.color;
    document.getElementById('edit_weight').value = car.weight;
    document.getElementById('edit_description').value = car.description || '';
    document.getElementById('edit_status').value = car.status;
    
    new bootstrap.Modal(document.getElementById('editCarModal')).show();
}

function deleteCar(id, licensePlate) {
    if (confirm('Bạn có chắc chắn muốn xóa xe ' + licensePlate + '?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function viewCar(id) {
    // This would typically load car details via AJAX
    // For now, we'll just show a placeholder
    document.getElementById('viewCarContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Đang tải thông tin xe...</p>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('viewCarModal')).show();
    
    // Simulate loading
    setTimeout(() => {
        document.getElementById('viewCarContent').innerHTML = `
            <p>Chi tiết xe ID: ${id}</p>
            <p>Thông tin chi tiết sẽ được hiển thị ở đây.</p>
        `;
    }, 1000);
}
</script>

<?php include '../includes/footer.php'; ?>

