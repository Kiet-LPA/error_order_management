<?php
require_once 'config.php';

// Check if user is admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$db = getDB();
$message = '';
$messageType = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_user') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $role_id = (int)$_POST['role_id'];
        $region_id = $_POST['region_id'] ? (int)$_POST['region_id'] : null;
        $password = $_POST['password'];
        
        if ($username && $email && $full_name && $role_id && $password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO users (username, email, password, full_name, role_id, region_id)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$username, $email, $hashedPassword, $full_name, $role_id, $region_id]);
                $message = "Thêm người dùng thành công!";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = "Lỗi: " . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = "Vui lòng điền đầy đủ thông tin!";
            $messageType = 'error';
        }
    }
    
    if ($action === 'toggle_status') {
        $userId = (int)$_POST['user_id'];
        $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$userId]);
        $message = "Cập nhật trạng thái thành công!";
        $messageType = 'success';
    }
    
    if ($action === 'edit_user') {
        $userId = (int)$_POST['user_id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $role_id = (int)$_POST['role_id'];
        $region_id = $_POST['region_id'] ? (int)$_POST['region_id'] : null;
        $password = $_POST['password'] ?? '';
        
        if ($userId && $username && $email && $full_name && $role_id) {
            try {
                if ($password) {
                    // Update with new password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("
                        UPDATE users 
                        SET username = ?, email = ?, full_name = ?, role_id = ?, region_id = ?, password = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$username, $email, $full_name, $role_id, $region_id, $hashedPassword, $userId]);
                } else {
                    // Update without changing password
                    $stmt = $db->prepare("
                        UPDATE users 
                        SET username = ?, email = ?, full_name = ?, role_id = ?, region_id = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$username, $email, $full_name, $role_id, $region_id, $userId]);
                }
                $message = "Cập nhật người dùng thành công!";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = "Lỗi: " . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = "Vui lòng điền đầy đủ thông tin!";
            $messageType = 'error';
        }
    }
    
    if ($action === 'delete_user') {
        $userId = (int)$_POST['user_id'];
        
        if ($userId) {
            try {
                // Check if user has check-ins
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM checkins WHERE user_id = ?");
                $stmt->execute([$userId]);
                $checkinCount = $stmt->fetch()['count'];
                
                if ($checkinCount > 0) {
                    // Don't delete, just deactivate
                    $stmt = $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
                    $stmt->execute([$userId]);
                    $message = "Người dùng có lịch sử điểm danh, đã chuyển sang trạng thái không hoạt động!";
                    $messageType = 'success';
                } else {
                    // Safe to delete
                    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $message = "Xóa người dùng thành công!";
                    $messageType = 'success';
                }
            } catch (PDOException $e) {
                $message = "Lỗi: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get all users
$stmt = $db->query("
    SELECT u.*, r.name as role_name, reg.name as region_name
    FROM users u
    JOIN roles r ON u.role_id = r.id
    LEFT JOIN regions reg ON u.region_id = reg.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

// Get roles and regions for form
$roles = $db->query("SELECT * FROM roles ORDER BY name")->fetchAll();
$regions = $db->query("SELECT * FROM regions ORDER BY name")->fetchAll();

// Get user for editing
$editUser = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Checkin HPFoods</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="responsive.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .header {
            background: linear-gradient(135deg, #dc3545 0%, #6f42c1 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size: 1.5rem; font-weight: 700; }
        .nav-links {
            display: flex;
            gap: 1rem;
        }
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.2);
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #dc3545 0%, #6f42c1 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-body { padding: 1.5rem; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group select {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        .table th, .table td {
            padding: 16px 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        .table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
        }
        .table tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .table tbody tr {
            transition: all 0.3s ease;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-active {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            box-shadow: 0 2px 4px rgba(21, 87, 36, 0.2);
        }
        .status-inactive {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            box-shadow: 0 2px 4px rgba(114, 28, 36, 0.2);
        }
        .user-info {
            display: flex;
            flex-direction: column;
        }
        .user-name {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.2rem;
        }
        .user-username {
            font-size: 0.85rem;
            color: #6c757d;
            font-family: 'Courier New', monospace;
        }
        .user-email {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .user-email:hover {
            text-decoration: underline;
        }
        .role-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .role-admin {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
        }
        .role-manager {
            background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(253, 126, 20, 0.3);
        }
        .role-employee {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
        }
        .region-info {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .action-buttons {
            display: flex;
            gap: 0.3rem;
            flex-wrap: wrap;
        }
        .btn {
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .table-responsive {
            overflow-x: auto;
            border-radius: 12px;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .empty-state img {
            width: 120px;
            opacity: 0.5;
            margin-bottom: 1rem;
        }
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                gap: 0.2rem;
            }
            .action-buttons .btn {
                width: 100%;
                justify-content: center;
            }
            .table th, .table td {
                padding: 8px 6px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="logo">👥 Quản lý người dùng</div>
            <div class="nav-links">
                <a href="admin.php" class="nav-link">📊 Dashboard</a>
                <a href="manage_users.php" class="nav-link">👥 Người dùng</a>
                <a href="manage_regions.php" class="nav-link">🗺️ Vùng</a>
                <a href="reports.php" class="nav-link">📈 Báo cáo</a>
                <a href="logout.php" class="nav-link">🚪 Đăng xuất</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Add/Edit User Form -->
        <div class="card">
            <div class="card-header">
                <h3><?= $editUser ? '✏️ Chỉnh sửa người dùng' : '➕ Thêm người dùng mới' ?></h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $editUser ? 'edit_user' : 'add_user' ?>">
                    <?php if ($editUser): ?>
                        <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tên đăng nhập:</label>
                            <input type="text" name="username" required 
                                   value="<?= $editUser ? htmlspecialchars($editUser['username']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" required 
                                   value="<?= $editUser ? htmlspecialchars($editUser['email']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Họ tên đầy đủ:</label>
                            <input type="text" name="full_name" required 
                                   value="<?= $editUser ? htmlspecialchars($editUser['full_name']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Vai trò:</label>
                            <select name="role_id" required>
                                <option value="">Chọn vai trò</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= $editUser && $editUser['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Vùng làm việc:</label>
                            <select name="region_id">
                                <option value="">Không phân công</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= $region['id'] ?>" <?= $editUser && $editUser['region_id'] == $region['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($region['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Mật khẩu:</label>
                            <input type="password" name="password" <?= $editUser ? '' : 'required' ?>
                                   placeholder="<?= $editUser ? 'Để trống nếu không đổi mật khẩu' : 'Nhập mật khẩu' ?>">
                        </div>
                    </div>
                    
                    <div style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-success">
                            <?= $editUser ? '💾 Cập nhật người dùng' : '➕ Thêm người dùng' ?>
                        </button>
                        <?php if ($editUser): ?>
                            <a href="manage_users.php" class="btn" style="background: #6c757d; color: white; margin-left: 0.5rem;">
                                ❌ Hủy
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users List -->
        <div class="card">
            <div class="card-header">
                <h3>📋 Danh sách người dùng (<?= count($users) ?>)</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th style="width: 200px;">👤 Người dùng</th>
                                <th style="width: 220px;">📧 Email</th>
                                <th style="width: 120px;">🎭 Vai trò</th>
                                <th style="width: 180px;">📍 Vùng làm việc</th>
                                <th style="width: 120px;">⚡ Trạng thái</th>
                                <th style="width: 140px;">📅 Ngày tạo</th>
                                <th style="width: 200px;">🔧 Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                                            <h4>Chưa có người dùng nào</h4>
                                            <p>Hãy thêm người dùng đầu tiên để bắt đầu!</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                            <tr>
                                <td><strong>#<?= $u['id'] ?></strong></td>
                                <td>
                                    <div class="user-info" style="display: flex; align-items: center; gap: 12px;">
                                        <img src="<?= getUserAvatar($u['avatar'], $u['full_name']) ?>" 
                                             alt="<?= htmlspecialchars($u['full_name']) ?>" 
                                             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                        <div>
                                            <div class="user-name"><?= htmlspecialchars($u['full_name']) ?></div>
                                            <div class="user-username">@<?= htmlspecialchars($u['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($u['email']) ?>" class="user-email">
                                        <?= htmlspecialchars($u['email']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php 
                                    $roleClass = '';
                                    switch(strtolower($u['role_name'])) {
                                        case 'admin': $roleClass = 'role-admin'; break;
                                        case 'manager': $roleClass = 'role-manager'; break;
                                        case 'employee': $roleClass = 'role-employee'; break;
                                        default: $roleClass = 'role-employee';
                                    }
                                    ?>
                                    <span class="role-badge <?= $roleClass ?>">
                                        <?= htmlspecialchars($u['role_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="region-info">
                                        <?= $u['region_name'] ? '📍 ' . htmlspecialchars($u['region_name']) : '❌ Chưa phân công' ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-<?= $u['is_active'] ? 'active' : 'inactive' ?>">
                                        <?= $u['is_active'] ? '✅ Hoạt động' : '❌ Tạm khóa' ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.9rem; color: #6c757d;">
                                        📅 <?= date('d/m/Y', strtotime($u['created_at'])) ?><br>
                                        🕐 <?= date('H:i', strtotime($u['created_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="manage_users.php?edit=<?= $u['id'] ?>" class="btn" 
                                           style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; font-size: 0.8rem; padding: 0.4rem 0.6rem;">
                                            ✏️ Sửa
                                        </a>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn <?= $u['is_active'] ? 'btn-warning' : 'btn-success' ?>" 
                                                    style="font-size: 0.8rem; padding: 0.4rem 0.6rem; 
                                                           background: <?= $u['is_active'] ? 'linear-gradient(135deg, #ffc107 0%, #e0a800 100%)' : 'linear-gradient(135deg, #28a745 0%, #1e7e34 100%)' ?>; 
                                                           color: <?= $u['is_active'] ? '#212529' : 'white' ?>;">
                                                <?= $u['is_active'] ? '🔒 Khóa' : '🔓 Mở' ?>
                                            </button>
                                        </form>
                                        
                                        <?php if ($u['username'] !== 'admin'): ?>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('⚠️ Xác nhận xóa?\n\n• Nếu có lịch sử điểm danh: Sẽ vô hiệu hóa\n• Nếu chưa có lịch sử: Sẽ xóa hoàn toàn\n\nBạn có chắc chắn?')">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="btn" 
                                                        style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; font-size: 0.8rem; padding: 0.4rem 0.6rem;">
                                                    🗑️ Xóa
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

