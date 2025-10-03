<?php
require_once '../includes/config.php';
requireManager();

$page_title = 'Dashboard - Manager';

// Get statistics
$stats = [];

// Total cars
$stmt = $pdo->query("SELECT COUNT(*) as total FROM cars");
$stats['total_cars'] = $stmt->fetch()['total'];

// Available cars
$stmt = $pdo->query("SELECT COUNT(*) as total FROM cars WHERE status = 'active' AND (available_from IS NULL OR available_from <= NOW())");
$stats['available_cars'] = $stmt->fetch()['total'];

// Rented cars
$stmt = $pdo->query("SELECT COUNT(*) as total FROM cars WHERE status = 'rented'");
$stats['rented_cars'] = $stmt->fetch()['total'];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $stmt->fetch()['total'];

// Active rentals
$stmt = $pdo->query("SELECT COUNT(*) as total FROM rentals WHERE status = 'active'");
$stats['active_rentals'] = $stmt->fetch()['total'];

// Pending extensions
$stmt = $pdo->query("SELECT COUNT(*) as total FROM rental_extensions WHERE status = 'pending'");
$stats['pending_extensions'] = $stmt->fetch()['total'];

// Recent rentals
$stmt = $pdo->prepare("
    SELECT r.*, u.name as user_name, c.license_plate, c.car_type 
    FROM rentals r 
    JOIN users u ON r.user_id = u.id 
    JOIN cars c ON r.car_id = c.id 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$recent_rentals = $stmt->fetchAll();

// Overdue rentals
$stmt = $pdo->prepare("
    SELECT r.*, u.name as user_name, c.license_plate, c.car_type 
    FROM rentals r 
    JOIN users u ON r.user_id = u.id 
    JOIN cars c ON r.car_id = c.id 
    WHERE r.status = 'active' AND r.rental_end < NOW()
    ORDER BY r.rental_end ASC
");
$stmt->execute();
$overdue_rentals = $stmt->fetchAll();

// Get manager's active rentals
$stmt = $pdo->prepare("
    SELECT r.*, c.license_plate, c.car_type, c.color 
    FROM rentals r 
    JOIN cars c ON r.car_id = c.id 
    WHERE r.user_id = ? AND r.status = 'active'
    ORDER BY r.rental_end ASC
");
$stmt->execute([$_SESSION['user_id']]);
$manager_active_rentals = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard - Manager</h2>
        <hr>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2 mb-3" style="margin-bottom: 2rem !important; padding: 0 1rem; margin-right: 1rem;">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary"><?php echo $stats['total_cars']; ?></h5>
                <p class="card-text">Tổng số xe</p>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3" style="margin-bottom: 2rem !important; padding: 0 1rem; margin-right: 1rem;">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success"><?php echo $stats['available_cars']; ?></h5>
                <p class="card-text">Xe có sẵn</p>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3" style="margin-bottom: 2rem !important; padding: 0 1rem; margin-right: 1rem;">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning"><?php echo $stats['rented_cars']; ?></h5>
                <p class="card-text">Xe đang thuê</p>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3" style="margin-bottom: 2rem !important; padding: 0 1rem; margin-right: 1rem;">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info"><?php echo $stats['total_users']; ?></h5>
                <p class="card-text">Tổng người dùng</p>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3" style="margin-bottom: 2rem !important; padding: 0 1rem; margin-right: 1rem;">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary"><?php echo $stats['active_rentals']; ?></h5>
                <p class="card-text">Mượn xe hoạt động</p>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3" style="margin-bottom: 2rem !important; padding: 0 1rem; margin-right: 1rem;">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-danger"><?php echo $stats['pending_extensions']; ?></h5>
                <p class="card-text">Chờ duyệt gia hạn</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Rentals -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-clock"></i> Mượn xe gần đây</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_rentals)): ?>
                    <p class="text-muted">Chưa có mượn xe nào.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Người thuê</th>
                                    <th>Biển số</th>
                                    <th>Loại xe</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_rentals as $rental): ?>
                                    <tr>
                                        <td><?php echo sanitize($rental['user_name']); ?></td>
                                        <td><?php echo sanitize($rental['license_plate']); ?></td>
                                        <td><?php echo sanitize($rental['car_type']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $rental['status'] === 'active' ? 'success' : ($rental['status'] === 'completed' ? 'primary' : 'danger'); ?>">
                                                <?php echo ucfirst($rental['status']); ?>
                                            </span>
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

    <!-- Overdue Rentals -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-exclamation-triangle text-danger"></i> Mượn xe quá hạn</h5>
            </div>
            <div class="card-body">
                <?php if (empty($overdue_rentals)): ?>
                    <p class="text-muted">Không có mượn xe quá hạn.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Người thuê</th>
                                    <th>Biển số</th>
                                    <th>Hạn trả</th>
                                    <th>Quá hạn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdue_rentals as $rental): ?>
                                    <tr>
                                        <td><?php echo sanitize($rental['user_name']); ?></td>
                                        <td><?php echo sanitize($rental['license_plate']); ?></td>
                                        <td><?php echo formatDateTime($rental['rental_end']); ?></td>
                                        <td>
                                            <?php 
                                            $overdue = time() - strtotime($rental['rental_end']);
                                            $hours = floor($overdue / 3600);
                                            echo $hours . ' giờ';
                                            ?>
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

<!-- Manager's Active Rentals -->
<?php if (!empty($manager_active_rentals)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-car"></i> Mượn xe đang hoạt động của tôi</h5>
            </div>
            <div class="card-body">
                <?php foreach ($manager_active_rentals as $rental): ?>
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
                                    if ($endTime < $now) {
                                        $overdue = $now - $endTime;
                                        $hours = floor($overdue / 3600);
                                        echo '<span class="badge bg-danger">Quá hạn ' . $hours . 'h</span>';
                                    } else {
                                        $timeLeft = $endTime - $now;
                                        $hours = floor($timeLeft / 3600);
                                        $minutes = floor(($timeLeft % 3600) / 60);
                                        echo '<span class="badge bg-success">Còn ' . $hours . 'h ' . $minutes . 'm</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="mt-2">
                                <a href="my_rentals.php?id=<?php echo $rental['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

