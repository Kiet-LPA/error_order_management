<!-- Navbar -->
<nav class="navbar navbar-light bg-white shadow-sm" style="position: fixed; top: 0; left: 0; right: 0; z-index: 99999; width: 100%;">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-primary" href="{{ route('kanban') }}">
      <i class="bi bi-clipboard-data me-2"></i>📋 Quản lý công việc
    </a>
    
    <div class="d-flex align-items-center">
      <!-- Shortcuts -->
      <div class="navbar-nav me-3 d-flex flex-row align-items-center">
        <div class="dropdown">
          <button class="btn btn-outline-primary dropdown-toggle" type="button" id="shortcutsDropdown" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
            <i class="bi bi-grid me-1"></i>Phím tắt
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="shortcutsDropdown" style="min-width: 250px; max-width: 90vw;">
            <li class="dropdown-header">Chọn hệ thống</li>
            <li>
              <a class="dropdown-item" href="{{ route('kanban') }}">
                <div class="d-flex align-items-center">
                  <div class="me-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                      <i class="bi bi-kanban"></i>
                    </div>
                  </div>
                  <div>
                    <div class="fw-bold">Quy trình làm việc</div>
                    <small class="text-muted">Quản lý công việc</small>
                  </div>
                </div>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="{{ route('checkin.index') }}">
                <div class="d-flex align-items-center">
                  <div class="me-3">
                    <div class="text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #2E7D32;">
                      <i class="bi bi-geo-alt"></i>
                    </div>
                  </div>
                  <div>
                    <div class="fw-bold">Điểm danh</div>
                    <small class="text-muted">Check-in GPS</small>
                  </div>
                </div>
              </a>
            </li>
            @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
            <li>
              <a class="dropdown-item" href="{{ route('admin.checkin.index') }}">
                <div class="d-flex align-items-center">
                  <div class="me-3">
                    <div class="text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #4CAF50;">
                      <i class="bi bi-gear"></i>
                    </div>
                  </div>
                  <div>
                    <div class="fw-bold">Quản lý điểm danh</div>
                    <small class="text-muted">{{ auth()->user()->isManager() ? 'Phòng ban' : 'Toàn công ty' }}</small>
                  </div>
                </div>
              </a>
            </li>
            @endif
            @if(auth()->user()->canManageCars())
            <li>
              <a class="dropdown-item" href="{{ route('rental.admin') }}">
                <div class="d-flex align-items-center">
                  <div class="me-3">
                    <div class="text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #1976D2;">
                      <i class="bi bi-tools"></i>
                    </div>
                  </div>
                  <div>
                    <div class="fw-bold">Quản lý xe</div>
                    <small class="text-muted">Quản trị xe</small>
                  </div>
                </div>
              </a>
            </li>
            @endif
            <li>
              <a class="dropdown-item" href="{{ route('rental.index') }}">
                <div class="d-flex align-items-center">
                  <div class="me-3">
                    <div class="text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #42A5F5;">
                      <i class="bi bi-car-front"></i>
                    </div>
                  </div>
                  <div>
                    <div class="fw-bold">Mượn xe</div>
                    <small class="text-muted">Đặt xe công ty</small>
                  </div>
                </div>
              </a>
            </li>
          </ul>
        </div>
      </div>
      
      <!-- Notification Dropdown -->
      @include('components.notification-dropdown')
      
      <!-- User Menu -->
      <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
          <img src="{{ auth()->user()->avatar_url }}" 
               alt="{{ auth()->user()->name }}" 
               class="rounded-circle me-2" 
               style="width: 24px; height: 24px; object-fit: cover; border: 1px solid #dee2e6;">
          {{ auth()->user()->name }}
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
    z-index: 10000 !important;
}

/* Simple shortcuts dropdown styling */
.dropdown-menu[aria-labelledby="shortcutsDropdown"] {
    right: 0 !important;
    left: auto !important;
    transform: none !important;
    top: 100% !important;
    min-width: 250px;
    max-width: 90vw;
    z-index: 10001 !important;
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
    margin-right: 0.5rem;
}

.nav-link:hover {
    transform: translateY(-1px);
}

/* Điểm danh button special style */
.nav-link[href*="checkin"] {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white !important;
    border-radius: 0.375rem;
    padding: 0.375rem 0.75rem !important;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
}

.nav-link[href*="checkin"]:hover {
    background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    color: white !important;
}

.nav-link[href*="checkin"]:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
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

/* Ensure navbar stays on top */
.navbar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 99999 !important;
    background-color: white !important;
    border-bottom: 1px solid #dee2e6;
    width: 100% !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
}

/* Fix any potential overflow issues */
.navbar .container-fluid {
    max-width: 100%;
    overflow: visible;
}

/* Responsive navbar styles */
@media (max-width: 768px) {
    .navbar-brand {
        font-size: 1rem;
    }
    
    .navbar-brand i {
        font-size: 0.9rem;
    }
    
    .btn {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    
    .dropdown-toggle {
        font-size: 0.8rem;
    }
    
    .navbar .d-flex {
        gap: 0.5rem;
    }
    
    .navbar .me-3 {
        margin-right: 0.5rem !important;
    }
    
    /* Ensure dropdowns are visible on mobile */
    .dropdown-menu {
        z-index: 100000 !important;
        max-width: 90vw;
    }
    
    /* Make navbar more compact on mobile */
    .navbar {
        padding: 0.5rem 1rem;
        min-height: 56px;
    }
    
    .navbar .container-fluid {
        padding: 0;
    }
}

@media (max-width: 576px) {
    .navbar-brand {
        font-size: 0.9rem;
    }
    
    .navbar-brand span {
        display: none;
    }
    
    .btn {
        font-size: 0.75rem;
        padding: 0.2rem 0.4rem;
    }
    
    .dropdown-toggle {
        font-size: 0.75rem;
    }
    
    /* Hide user name on very small screens */
    .btn-outline-secondary .me-1 {
        margin-right: 0 !important;
    }
    
    .btn-outline-secondary {
        padding: 0.25rem 0.5rem !important;
    }
}

/* Force dropdown visibility on all pages */
.dropdown-menu.show {
    display: block !important;
    z-index: 10001 !important;
    opacity: 1 !important;
    visibility: visible !important;
}



</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const shortcutsBtn = document.getElementById("shortcutsDropdown");
  const shortcutsMenu = document.querySelector('[aria-labelledby="shortcutsDropdown"]');

  if (shortcutsBtn && shortcutsMenu) {
    shortcutsBtn.addEventListener("show.bs.dropdown", () => {
      shortcutsMenu.style.right = "0";
      shortcutsMenu.style.left = "auto";
      shortcutsMenu.style.top = "100%";
    });
  }
});
</script>

