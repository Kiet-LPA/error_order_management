<?php
require_once '../includes/config.php';
requireManager();

$page_title = 'Quản lý người dùng - Manager';

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = sanitize($_POST['name']);
                $email = sanitize($_POST['email']);
                $password = $_POST['password'];
                $role = sanitize($_POST['role']);
                $phone = sanitize($_POST['phone']);
                $address = sanitize($_POST['address']);
                
                // Validate email uniqueness
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $emailExists = $stmt->fetch()['count'] > 0;
                
                if ($emailExists) {
                    $message = 'Email đã tồn tại!';
                    $message_type = 'danger';
                } else {
                    try {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $email, $hashedPassword, $role, $phone, $address]);
                        $message = 'Thêm người dùng thành công!';
                        $message_type = 'success';
                    } catch (PDOException $e) {
                        $message = 'Lỗi: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $name = sanitize($_POST['name']);
                $email = sanitize($_POST['email']);
                $role = sanitize($_POST['role']);
                $phone = sanitize($_POST['phone']);
                $address = sanitize($_POST['address']);
                
                // Validate email uniqueness (excluding current user)
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $id]);
                $emailExists = $stmt->fetch()['count'] > 0;
                
                if ($emailExists) {
                    $message = 'Email đã tồn tại!';
                    $message_type = 'danger';
                } else {
                    try {
                        $updateData = [$name, $email, $role, $phone, $address, $id];
                        
                        // Update password if provided
                        if (!empty($_POST['password'])) {
                            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ?, phone = ?, address = ? WHERE id = ?");
                            $updateData = [$name, $email, $hashedPassword, $role, $phone, $address, $id];
                        } else {
                            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, phone = ?, address = ? WHERE id = ?");
                        }
                        
                        $stmt->execute($updateData);
                        $message = 'Cập nhật người dùng thành công!';
                        $message_type = 'success';
                    } catch (PDOException $e) {
                        $message = 'Lỗi: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                
                // Prevent deleting own account
                if ($id === $_SESSION['user_id']) {
                    $message = 'Không thể xóa tài khoản của chính mình!';
                    $message_type = 'danger';
                } else {
                    // Check if user has active rentals
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rentals WHERE user_id = ? AND status = 'active'");
                    $stmt->execute([$id]);
                    $hasActiveRentals = $stmt->fetch()['count'] > 0;
                    
                    if ($hasActiveRentals) {
                        $message = 'Không thể xóa người dùng đang có xe thuê!';
                        $message_type = 'danger';
                    } else {
                        try {
                            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                            $stmt->execute([$id]);
                            $message = 'Xóa người dùng thành công!';
                            $message_type = 'success';
                        } catch (PDOException $e) {
                            $message = 'Lỗi: ' . $e->getMessage();
                            $message_type = 'danger';
                        }
                    }
                }
                break;
        }
    }
}

// Get all users with rental statistics
$stmt = $pdo->prepare("
    SELECT u.*, 
           COUNT(r.id) as total_rentals,
           COUNT(CASE WHEN r.status = 'active' THEN 1 END) as active_rentals
    FROM users u 
    LEFT JOIN rentals r ON u.id = r.user_id 
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-users"></i> Quản lý người dùng</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus"></i> Thêm người dùng mới
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
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Số điện thoại</th>
                                <th>Thuê xe</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <strong><?php echo sanitize($user['name']); ?></strong>
                                        <?php if ($user['id'] === $_SESSION['user_id']): ?>
                                            <span class="badge bg-primary">Bạn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo sanitize($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] === 'manager' ? 'danger' : 'info'; ?>">
                                            <?php echo $user['role'] === 'manager' ? 'Manager' : 'Employee'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo sanitize($user['phone']); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $user['total_rentals']; ?> tổng</span>
                                        <?php if ($user['active_rentals'] > 0): ?>
                                            <span class="badge bg-success"><?php echo $user['active_rentals']; ?> đang thuê</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDateTime($user['created_at']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo sanitize($user['name']); ?>')">
                                                <i class="fas fa-trash"></i>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm người dùng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Vai trò *</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="employee">Employee</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm người dùng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sửa thông tin người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Tên *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="edit_password" name="password" placeholder="Để trống nếu không muốn đổi">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Vai trò *</label>
                        <select class="form-control" id="edit_role" name="role" required>
                            <option value="employee">Employee</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
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

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewUserContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_address').value = user.address || '';
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function deleteUser(id, name) {
    if (confirm('Bạn có chắc chắn muốn xóa người dùng ' + name + '?')) {
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

function viewUser(id) {
    // This would typically load user details via AJAX
    // For now, we'll just show a placeholder
    document.getElementById('viewUserContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Đang tải thông tin người dùng...</p>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('viewUserModal')).show();
    
    // Simulate loading
    setTimeout(() => {
        document.getElementById('viewUserContent').innerHTML = `
            <p>Chi tiết người dùng ID: ${id}</p>
            <p>Thông tin chi tiết sẽ được hiển thị ở đây.</p>
        `;
    }, 1000);
}
</script>

<?php include '../includes/footer.php'; ?>

