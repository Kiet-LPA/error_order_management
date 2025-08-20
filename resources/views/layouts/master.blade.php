<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','HP Foods')</title>
  
  {{-- Favicon --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="{{ asset('css/home.css') }}">
  <style>
    /* Dark mode variables */
    :root {
      --bg-color: #fff;
      --text-color: #6c757d;
      --border-color: #dee2e6;
      --shadow-color: rgba(0,0,0,0.1);
      --hover-bg: rgba(85, 142, 193, 0.1);
      --active-bg: rgba(85, 142, 193, 0.15);
      --active-color: #558EC1;
    }
    
    [data-theme="dark"] {
      --bg-color: #1a1a1a;
      --text-color: #b0b0b0;
      --border-color: #333;
      --shadow-color: rgba(0,0,0,0.3);
      --hover-bg: rgba(85, 142, 193, 0.2);
      --active-bg: rgba(85, 142, 193, 0.25);
      --active-color: #7ba3d4;
    }
    
    /* Apply dark mode to body */
    [data-theme="dark"] body {
      background-color: #121212;
      color: #e0e0e0;
    }
    
    /* Dark mode for cards and other elements */
    [data-theme="dark"] .card {
      background-color: #1e1e1e;
      border-color: #333;
      color: #e0e0e0;
    }
    
    [data-theme="dark"] .list-group-item {
      background-color: #1e1e1e;
      border-color: #333;
      color: #e0e0e0;
    }
    
    [data-theme="dark"] .list-group-item:hover {
      background-color: #2a2a2a;
    }
    
    [data-theme="dark"] .list-group-item.active {
      background-color: var(--active-bg);
      border-color: var(--active-color);
      color: var(--active-color);
    }
    
    /* Sidebar toggle styles */
    .sidebar {
      transition: all 0.3s ease;
      position: relative;
    }
    
    .sidebar.collapsed {
      width: 60px !important;
      min-width: 60px !important;
      flex: 0 0 60px !important;
      max-width: 60px !important;
    }
    
    .sidebar.collapsed .list-group-item {
      padding: 12px 8px;
      text-align: center;
      font-size: 0.9rem;
      white-space: nowrap;
      overflow: hidden;
    }
    
    .sidebar.collapsed .list-group-item span {
      display: none;
    }
    
    .sidebar.collapsed .list-group-item i {
      margin-right: 0 !important;
      font-size: 1.1rem;
    }
    
    .sidebar-toggle {
      position: absolute;
      top: 30%;
      right: -15px;
      width: 30px;
      height: 30px;
      background: linear-gradient(135deg, #558EC1 0%, #5DA444 100%);
      border: 2px solid #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 1000;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      transform: translateY(-30%);
    }
    
    .sidebar-toggle:hover {
      transform: translateY(-30%) scale(1.1);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .sidebar-toggle i {
      color: #fff;
      font-size: 0.8rem;
      transition: transform 0.3s ease;
    }
    
    .sidebar.collapsed .sidebar-toggle i {
      transform: rotate(180deg);
    }
    
    .main-content {
      transition: all 0.3s ease;
    }
    
    .main-content.expanded {
      margin-left: 0;
    }
    
    /* Bottom Navigation for Mobile */
    .bottom-nav {
      display: none;
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: var(--bg-color);
      border-top: 1px solid var(--border-color);
      z-index: 1050;
      padding: 8px 0;
      box-shadow: 0 -2px 10px var(--shadow-color);
      transition: all 0.3s ease;
    }
    
    .bottom-nav .nav-item {
      flex: 1;
      text-align: center;
    }
    
    .bottom-nav .nav-link {
      color: var(--text-color);
      text-decoration: none;
      padding: 8px 4px;
      border-radius: 8px;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      font-size: 0.75rem;
      min-height: 50px;
      justify-content: center;
    }
    
    .bottom-nav .nav-link:hover {
      color: var(--active-color);
      background: var(--hover-bg);
    }
    
    .bottom-nav .nav-link.active {
      color: var(--active-color);
      background: var(--active-bg);
    }
    
    .bottom-nav .nav-link i {
      font-size: 1.2rem;
      margin-bottom: 4px;
    }
    
    .bottom-nav .nav-link span {
      font-size: 0.7rem;
      line-height: 1;
    }
    
    /* Ensure proper spacing for bottom nav */
    .bottom-nav {
      height: 70px;
    }
    
    .bottom-nav .row {
      height: 100%;
    }
    
    .bottom-nav .nav-item {
      height: 100%;
    }
    
    .bottom-nav .nav-link {
      height: 100%;
      border-radius: 0;
    }
    
    /* Auto dark mode for bottom navigation */
    @media (prefers-color-scheme: dark) {
      .bottom-nav {
        background: #1a1a1a;
        border-top-color: #333;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
      }
      
      .bottom-nav .nav-link {
        color: #b0b0b0;
      }
      
      .bottom-nav .nav-link:hover {
        color: #7ba3d4;
        background: rgba(85, 142, 193, 0.2);
      }
      
      .bottom-nav .nav-link.active {
        color: #7ba3d4;
        background: rgba(85, 142, 193, 0.25);
      }
    }
    
    /* Force dark mode for bottom navigation */
    .bottom-nav.dark-mode {
      background: #1a1a1a !important;
      border-top-color: #333 !important;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.3) !important;
    }
    
    .bottom-nav.dark-mode .nav-link {
      color: #b0b0b0 !important;
    }
    
    .bottom-nav.dark-mode .nav-link:hover {
      color: #7ba3d4 !important;
      background: rgba(85, 142, 193, 0.2) !important;
    }
    
    .bottom-nav.dark-mode .nav-link.active {
      color: #7ba3d4 !important;
      background: rgba(85, 142, 193, 0.25) !important;
    }
    
    /* Scroll to top button */
    .scroll-to-top {
      position: fixed;
      bottom: 90px;
      right: 20px;
      width: 45px;
      height: 45px;
      background: linear-gradient(135deg, #558EC1 0%, #5DA444 100%);
      border: none;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 1040;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      opacity: 0;
      visibility: hidden;
      transform: translateY(20px);
    }
    
    .scroll-to-top.show {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }
    
    .scroll-to-top:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .scroll-to-top i {
      color: #fff;
      font-size: 1.2rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .sidebar {
        display: none !important;
      }
      
      .main-content {
        margin-bottom: 90px !important;
        padding-bottom: 20px !important;
      }
      
      .bottom-nav {
        display: flex !important;
      }
      
      .sidebar-toggle {
        display: none;
      }
      
      /* Hide floating create task button on mobile */
      .btn-success.position-fixed {
        display: none !important;
      }
      
      /* Position scroll to top button for mobile */
      .scroll-to-top {
        bottom: 100px;
        right: 15px;
        width: 40px;
        height: 40px;
      }
      
      .scroll-to-top i {
        font-size: 1.1rem;
      }
    }
    
    /* Tooltip for collapsed sidebar */
    .sidebar.collapsed .list-group-item {
      position: relative;
    }
    
    .sidebar.collapsed .list-group-item:hover::after {
      content: attr(data-title);
      position: absolute;
      left: 100%;
      top: 50%;
      transform: translateY(-50%);
      background: #333;
      color: #fff;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
      white-space: nowrap;
      z-index: 1001;
      margin-left: 8px;
    }
  </style>
  @stack('styles')
</head>
<body class="bg-light">

  @include('partials.navbar')

  <div class="container-fluid">
    <div class="row">
      {{-- Sidebar trái --}}
      <aside class="col-12 col-md-3 col-lg-2 sidebar p-0" id="sidebar">
        <div class="sidebar-toggle" onclick="toggleSidebar()">
          <i class="bi bi-chevron-left"></i>
        </div>
        <div class="list-group rounded-0">
          <a href="{{ route('dashboard') }}"
             class="list-group-item {{ request()->routeIs('dashboard')?'active':'' }}"
             data-title="Danh sách">
            <i class="bi bi-list-task me-2"></i> <span>Danh sách</span>
          </a>

          @auth
          @if(Auth::user()->isAdmin())
            <div class="list-group-item p-0">
              <a href="#" class="list-group-item d-flex justify-content-between align-items-center {{ request()->routeIs('users.*') || request()->routeIs('employees.*')?'active':'' }}" 
                 data-bs-toggle="collapse" data-bs-target="#employeeSubmenu"
                 aria-expanded="{{ request()->routeIs('users.*') || request()->routeIs('employees.*')?'true':'false' }}" aria-controls="employeeSubmenu">
                <div>
                  <i class="bi bi-people me-2"></i> <span>Quản lý nhân viên</span>
                </div>
                <i class="bi bi-chevron-down"></i>
              </a>
              <div class="collapse {{ request()->routeIs('users.*') || request()->routeIs('employees.*')?'show':'' }}" id="employeeSubmenu">
                <div class="list-group list-group-flush">
                  <a href="{{ route('users.index') }}" 
                     class="list-group-item {{ request()->routeIs('users.*')?'active':'' }}"
                     data-title="Nhân viên chính thức">
                    <i class="bi bi-person-check me-2"></i> <span>Nhân viên chính thức</span>
                  </a>
                  <a href="{{ route('employees.new.index') }}" 
                     class="list-group-item {{ request()->routeIs('employees.new.*')?'active':'' }}"
                     data-title="Nhân viên mới">
                    <i class="bi bi-person-plus me-2"></i> <span>Nhân viên mới</span>
                  </a>
                </div>
              </div>
            </div>
            <a href="{{ route('departments.index') }}"
               class="list-group-item {{ request()->routeIs('departments.*')?'active':'' }}"
               data-title="Phòng ban">
              <i class="bi bi-building me-2"></i> <span>Phòng ban</span>
            </a>
          @endif
          @if(Auth::user()->isAdmin() || Auth::user()->isManager())
            <a href="{{ route('create-task') }}"
               class="list-group-item {{ request()->routeIs('create-task')?'active':'' }}"
               data-title="Tạo công việc">
              <i class="bi bi-plus-square me-2"></i> <span>Tạo công việc</span>
            </a>
          @endif
          @endauth

          @if(Auth::user()->isAdmin() || Auth::user()->isManager())
            <a href="{{ route('reports.index') }}"
               class="list-group-item {{ request()->routeIs('reports.index')?'active':'' }}"
               data-title="Báo cáo">
              <i class="bi bi-bar-chart me-2"></i> <span>Báo cáo</span>
            </a>
          @endif
        </div>
      </aside>

      {{-- Nội dung --}}
      <main class="col-12 col-md-9 col-lg-10 py-3 main-content" id="main-content">
        @yield('content')
      </main>
    </div>
  </div>

  @auth
  @endauth

  {{-- Scroll to top button --}}
  <button class="scroll-to-top" onclick="scrollToTop()" title="Lên đầu trang">
    <i class="bi bi-arrow-up-circle"></i>
  </button>

  {{-- Bottom Navigation for Mobile --}}
  <nav class="bottom-nav">
    <div class="container-fluid">
      <div class="row g-0">
        <div class="col nav-item">
          <a href="{{ route('dashboard') }}" 
             class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}">
            <i class="bi bi-list-task"></i>
            <span>Danh sách</span>
          </a>
        </div>
        
        @auth
        @if(Auth::user()->isAdmin())
          <div class="col nav-item">
            <a href="{{ route('users.index') }}" 
               class="nav-link {{ request()->routeIs('users.*')?'active':'' }}">
              <i class="bi bi-people"></i>
              <span>Nhân viên</span>
            </a>
          </div>
          <div class="col nav-item">
            <a href="{{ route('departments.index') }}" 
               class="nav-link {{ request()->routeIs('departments.*')?'active':'' }}">
              <i class="bi bi-building"></i>
              <span>Phòng ban</span>
            </a>
          </div>
        @endif
        @if(Auth::user()->isAdmin() || Auth::user()->isManager())
          <div class="col nav-item">
            <a href="{{ route('create-task') }}" 
               class="nav-link {{ request()->routeIs('create-task')?'active':'' }}">
              <i class="bi bi-plus-square"></i>
              <span>Tạo việc</span>
            </a>
          </div>
        @endif
        @endauth

        @if(Auth::user()->isAdmin() || Auth::user()->isManager())
          <div class="col nav-item">
            <a href="{{ route('reports.index') }}" 
               class="nav-link {{ request()->routeIs('reports.index')?'active':'' }}">
              <i class="bi bi-bar-chart"></i>
              <span>Báo cáo</span>
            </a>
          </div>
        @endif
      </div>
    </div>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
  <script src="{{ asset('js/home.js') }}"></script>
  <script>
    // Sidebar toggle functionality
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('main-content');
      const toggleIcon = document.querySelector('.sidebar-toggle i');
      
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('expanded');
      
      // Save state to localStorage
      const isCollapsed = sidebar.classList.contains('collapsed');
      localStorage.setItem('sidebarCollapsed', isCollapsed);
      
      // Update icon with animation
      if (isCollapsed) {
        toggleIcon.style.transform = 'rotate(180deg)';
        toggleIcon.classList.remove('bi-chevron-left');
        toggleIcon.classList.add('bi-chevron-right');
      } else {
        toggleIcon.style.transform = 'rotate(0deg)';
        toggleIcon.classList.remove('bi-chevron-right');
        toggleIcon.classList.add('bi-chevron-left');
      }
      
      // Trigger resize event for charts if they exist
      if (typeof Chart !== 'undefined') {
        Chart.instances.forEach(chart => {
          chart.resize();
        });
      }
    }
    
    // Load sidebar state on page load
    document.addEventListener('DOMContentLoaded', function() {
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('main-content');
      const toggleIcon = document.querySelector('.sidebar-toggle i');
      
      const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
      
      if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
        toggleIcon.style.transform = 'rotate(180deg)';
        toggleIcon.classList.remove('bi-chevron-left');
        toggleIcon.classList.add('bi-chevron-right');
      }
      
      // Add keyboard shortcut (Ctrl + B) to toggle sidebar
      document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'b') {
          e.preventDefault();
          toggleSidebar();
        }
      });
      
      // Auto apply dark mode to bottom navigation
      applyDarkModeToBottomNav();
    });
    
    // Function to apply dark mode to bottom navigation
    function applyDarkModeToBottomNav() {
      const bottomNav = document.querySelector('.bottom-nav');
      if (bottomNav) {
        // Check if system prefers dark mode
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        // Check if it's evening/night time (after 6 PM or before 6 AM)
        const now = new Date();
        const hour = now.getHours();
        const isNightTime = hour >= 18 || hour < 6;
        
        // Apply dark mode if system prefers dark OR it's night time
        if (prefersDark || isNightTime) {
          bottomNav.classList.add('dark-mode');
        }
      }
    }
    
    // Scroll to top functionality
    function scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
    
    // Show scroll to top button when scrolling down
    window.addEventListener('scroll', function() {
      const scrollToTopBtn = document.querySelector('.scroll-to-top');
      if (scrollToTopBtn) {
        if (window.pageYOffset > 300) { // Show after scrolling down 300px
          scrollToTopBtn.classList.add('show');
        } else {
          scrollToTopBtn.classList.remove('show');
        }
      }
    });
  </script>
  @stack('scripts')
</body>
</html>
