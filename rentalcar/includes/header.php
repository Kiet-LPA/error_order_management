<?php
if (!isset($page_title)) {
    $page_title = SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo isManager() ? 'dashboard.php' : '../employee/dashboard.php'; ?>">
                <i class="fas fa-car"></i> <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isManager()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="managementDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cogs"></i> Quản lý
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="cars.php">
                                    <i class="fas fa-car"></i> Quản lý xe
                                </a></li>
                                <li><a class="dropdown-item" href="users.php">
                                    <i class="fas fa-users"></i> Quản lý người dùng
                                </a></li>
                                <li><a class="dropdown-item" href="rentals.php">
                                    <i class="fas fa-list"></i> Quản lý mượn xe
                                </a></li>
                                <li><a class="dropdown-item" href="extensions.php">
                                    <i class="fas fa-clock"></i> Duyệt gia hạn
                                </a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="rentalDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-car"></i> Mượn xe
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="rent_car.php">
                                    <i class="fas fa-car"></i> Mượn xe mới
                                </a></li>
                                <li><a class="dropdown-item" href="my_rentals.php">
                                    <i class="fas fa-history"></i> Lịch sử mượn xe
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cars.php">
                                <i class="fas fa-car"></i> Mượn xe
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="rentals.php">
                                <i class="fas fa-list"></i> Lịch sử mượn xe
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
