<!-- Navbar -->
<nav class="navbar navbar-light bg-white shadow-sm sticky-top" style="z-index: 1030;">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-primary" href="{{ route('dashboard') }}">
      <i class="bi bi-clipboard-data me-2"></i>📋 Quản lý công việc
    </a>
    
    <div class="d-flex align-items-center">
      <!-- Navigation Links -->
      <div class="navbar-nav me-3">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-bold' : '' }}" href="{{ route('dashboard') }}">
          <i class="bi bi-speedometer2 me-1"></i>Bảng điều khiển
        </a>
      </div>
      
      <!-- Notification Dropdown -->
      @include('components.notification-dropdown')
      
      <!-- User Menu -->
      <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
          <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
          <li>
            <a class="dropdown-item" href="{{ route('profile.edit') }}">
              <i class="bi bi-person-gear me-2"></i>Hồ sơ
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
              @csrf
              <button type="submit" class="dropdown-item text-danger">
                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
              </button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<!-- Add Bootstrap Icons CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
/* Custom dropdown styles */
.dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
    padding: 0.5rem 0;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
    border-radius: 0.25rem;
    margin: 0 0.25rem;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
}

.dropdown-item.active {
    background-color: #0d6efd !important;
    color: white !important;
}

.dropdown-item.active:hover {
    background-color: #0b5ed7 !important;
}

/* Navbar brand hover effect */
.navbar-brand:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

/* Navigation links */
.nav-link {
    position: relative;
    transition: all 0.2s ease;
}

.nav-link:hover {
    transform: translateY(-1px);
}

.nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #0d6efd;
    border-radius: 1px;
}

/* Button hover effects */
.btn-outline-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.25rem 0.5rem rgba(108, 117, 125, 0.25);
}

</style>
