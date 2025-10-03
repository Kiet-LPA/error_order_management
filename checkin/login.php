<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectByRole($_SESSION['role']);
}

$error = '';

// Handle login
if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['region_id'] = $user['region_id'];
            
            redirectByRole($user['role_name']);
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Checkin HP Foods</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            margin: 1rem;
        }
        @media (max-width: 768px) {
            .login-container {
                padding: 1.5rem;
                margin: 0.5rem;
                border-radius: 12px;
            }
        }
        @media (max-width: 480px) {
            .login-container {
                padding: 1rem;
                margin: 0.25rem;
                border-radius: 10px;
            }
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo h1 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        .logo p {
            color: #666;
            font-size: 1rem;
        }
        @media (max-width: 768px) {
            .logo {
                margin-bottom: 1.5rem;
            }
            .logo h1 {
                font-size: 1.6rem;
            }
            .logo p {
                font-size: 0.95rem;
            }
        }
        @media (max-width: 480px) {
            .logo {
                margin-bottom: 1rem;
            }
            .logo h1 {
                font-size: 1.4rem;
            }
            .logo p {
                font-size: 0.9rem;
            }
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        @media (max-width: 768px) {
            .form-group {
                margin-bottom: 0.8rem;
            }
            label {
                font-size: 0.9rem;
            }
            input[type="text"], input[type="password"] {
                padding: 10px;
                font-size: 0.95rem;
            }
        }
        @media (max-width: 480px) {
            input[type="text"], input[type="password"] {
                padding: 8px;
                font-size: 0.9rem;
            }
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            box-sizing: border-box;
        }
        @media (max-width: 768px) {
            .btn {
                padding: 10px;
                font-size: 0.95rem;
            }
        }
        @media (max-width: 480px) {
            .btn {
                padding: 8px;
                font-size: 0.9rem;
            }
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }
        .test-accounts {
            margin-top: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        .test-accounts h4 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        .account {
            margin-bottom: 0.5rem;
        }
        .account strong {
            color: #333;
        }
        .account small {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="favicon.png" alt="HP Foods Logo" style="width: 64px; height: 64px; margin-bottom: 1rem; border-radius: 8px;">
            <h1>Checkin HP Foods</h1>
            <p>Hệ thống điểm danh GPS</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Tên đăng nhập hoặc Email:</label>
                <input type="text" id="username" name="username" required 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Đăng nhập</button>
        </form>
    </div>
</body>
</html>
