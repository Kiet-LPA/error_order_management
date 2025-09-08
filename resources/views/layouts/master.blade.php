<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>@yield('title','HP Foods')</title>
  
  {{-- Favicon --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/work-reports.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ time() }}">
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
      background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
      border-right: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar .list-group {
      background: transparent;
      border: none;
    }
    
    .sidebar .list-group-item {
      background: transparent;
      border: none;
      color: #ecf0f1;
      padding: 12px 20px;
      transition: all 0.3s ease;
      border-radius: 0;
      position: relative;
      overflow: hidden;
    }
    
    .sidebar .list-group-item:hover {
      background: rgba(255,255,255,0.1);
      color: #fff;
      transform: translateX(5px);
    }
    
    .sidebar .list-group-item.active {
      background: linear-gradient(90deg, #3498db 0%, #2980b9 100%);
      color: #fff;
      box-shadow: 0 2px 10px rgba(52, 152, 219, 0.3);
    }
    
    .sidebar .list-group-item.active::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      background: #e74c3c;
    }
    
    /* Section header styling */
    .sidebar-section-header {
      background: rgba(255,255,255,0.05) !important;
      color: #bdc3c7 !important;
      font-weight: 600;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 15px 20px 10px 20px !important;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar-section-header:hover {
      background: rgba(255,255,255,0.05) !important;
      transform: none !important;
    }
    
    /* Sub-item styling */
    .sidebar-sub-item {
      padding-left: 35px !important;
      font-size: 0.9rem;
      position: relative;
    }
    
    .sidebar-sub-item::before {
      content: '';
      position: absolute;
      left: 20px;
      top: 50%;
      width: 8px;
      height: 2px;
      background: rgba(255,255,255,0.3);
      transform: translateY(-50%);
    }
    
    .sidebar-sub-item:hover::before {
      background: rgba(255,255,255,0.6);
    }
    
    .sidebar-sub-item.active::before {
      background: #e74c3c;
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
    
    .sidebar.collapsed .sidebar-section-header {
      padding: 15px 8px 10px 8px !important;
      font-size: 0.7rem;
    }
    
    .sidebar.collapsed .sidebar-sub-item {
      padding-left: 8px !important;
    }
    
    .sidebar.collapsed .sidebar-sub-item::before {
      display: none;
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
    
    /* Dropdown styling for bottom nav */
    .bottom-nav .dropdown-nav {
      position: relative;
    }
    
    .bottom-nav .dropdown-menu {
      position: absolute;
      bottom: 100%;
      left: 50%;
      transform: translateX(-50%);
      margin-bottom: 8px;
      min-width: 200px;
      max-width: 250px;
      background: var(--bg-color);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      box-shadow: 0 -4px 12px var(--shadow-color);
      z-index: 1060;
      display: none;
      opacity: 0;
      transition: all 0.3s ease;
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    
    .bottom-nav .dropdown-menu.show {
      display: block;
      opacity: 1;
      animation: slideUp 0.3s ease;
    }
    
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateX(-50%) translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
      }
    }
    
    .bottom-nav .dropdown-item {
      color: var(--text-color);
      padding: 10px 15px;
      font-size: 0.9rem;
      border-bottom: 1px solid var(--border-color);
      transition: all 0.3s ease;
      display: block;
      text-decoration: none;
      width: 100%;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .bottom-nav .dropdown-item:last-child {
      border-bottom: none;
    }
    
    .bottom-nav .dropdown-item:hover {
      background: var(--hover-bg);
      color: var(--active-color);
    }
    
    .bottom-nav .dropdown-item.active {
      background: var(--active-bg);
      color: var(--active-color);
    }
    
    .bottom-nav .dropdown-item i {
      font-size: 1rem;
    }
    
    .bottom-nav .dropdown-menu li {
      list-style: none;
      margin: 0;
      padding: 0;
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
      
      .bottom-nav .dropdown-menu {
        background: #1a1a1a;
        border-color: #333;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.3);
      }
      
      .bottom-nav .dropdown-item {
        color: #b0b0b0;
        border-bottom-color: #333;
      }
      
      .bottom-nav .dropdown-item:hover {
        background: rgba(85, 142, 193, 0.2);
        color: #7ba3d4;
      }
      
      .bottom-nav .dropdown-item.active {
        background: rgba(85, 142, 193, 0.25);
        color: #7ba3d4;
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
    
    .bottom-nav.dark-mode .dropdown-menu {
      background: #1a1a1a !important;
      border-color: #333 !important;
      box-shadow: 0 -4px 12px rgba(0,0,0,0.3) !important;
    }
    
    .bottom-nav.dark-mode .dropdown-item {
      color: #b0b0b0 !important;
      border-bottom-color: #333 !important;
    }
    
    .bottom-nav.dark-mode .dropdown-item:hover {
      background: rgba(85, 142, 193, 0.2) !important;
      color: #7ba3d4 !important;
    }
    
    .bottom-nav.dark-mode .dropdown-item.active {
      background: rgba(85, 142, 193, 0.25) !important;
      color: #7ba3d4 !important;
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
          <!-- 1. Trang chủ -->
          <a href="{{ route('kanban') }}"
             class="list-group-item {{ request()->routeIs('kanban')?'active':'' }}"
             data-title="Trang Chủ">
            <i class="bi bi-house me-2"></i> <span>Trang chủ</span>
          </a>

          <!-- 2. Danh sách -->
          <a href="{{ route('dashboard') }}"
             class="list-group-item {{ request()->routeIs('dashboard')?'active':'' }}"
             data-title="Danh sách">
            <i class="bi bi-list-task me-2"></i> <span>Danh sách</span>
          </a>

          <!-- 3. Tạo công việc -->
          @if(Auth::user()->isAdmin() || Auth::user()->isDirector() || Auth::user()->isManager())
            <a href="{{ route('create-task') }}"
               class="list-group-item {{ request()->routeIs('create-task')?'active':'' }}"
               data-title="Tạo công việc">
              <i class="bi bi-plus-square me-2"></i> <span>Tạo công việc</span>
            </a>
          @endif

          <!-- 4. Quản lý yêu cầu -->
          @if(Auth::user()->isAdmin() || Auth::user()->isDirector() || Auth::user()->isManager())
          <a href="{{ route('support-requests.quest-detail') }}"
             class="list-group-item {{ request()->routeIs('support-requests.quest-detail')?'active':'' }}"
             data-title="Quản lý yêu cầu">
            <i class="bi bi-gear me-2"></i> <span>Quản lý yêu cầu</span>
          </a>
          @endif

          @auth
          @if(Auth::user()->isAdmin() || Auth::user()->isDirector())
            <!-- 5. Quản lý nhân viên - Header -->
            <div class="list-group-item sidebar-section-header">
              <i class="bi bi-people me-2"></i> <span>Quản lý nhân viên</span>
            </div>
            
            <!-- 5.1. Nhân viên chính thức -->
            <a href="{{ route('users.index') }}" 
               class="list-group-item sidebar-sub-item {{ request()->routeIs('users.*')?'active':'' }}"
               data-title="Nhân viên chính thức">
              <i class="bi bi-person-check me-2"></i> <span>Nhân viên chính thức</span>
            </a>
            
            <!-- 5.2. Nhân viên mới -->
            <a href="{{ route('employees.new.index') }}" 
               class="list-group-item sidebar-sub-item {{ request()->routeIs('employees.new.*')?'active':'' }}"
               data-title="Nhân viên mới">
              <i class="bi bi-person-plus me-2"></i> <span>Nhân viên mới</span>
            </a>
            
            <!-- 6. Phòng ban -->
            <a href="{{ route('departments.index') }}"
               class="list-group-item {{ request()->routeIs('departments.*')?'active':'' }}"
               data-title="Phòng ban">
              <i class="bi bi-building me-2"></i> <span>Phòng ban</span>
            </a>
          @endif

          <!-- 7. Báo cáo -->
          @if(Auth::user()->isAdmin() || Auth::user()->isDirector() || Auth::user()->isManager())
            <a href="{{ route('reports.index') }}"
               class="list-group-item {{ request()->routeIs('reports.index')?'active':'' }}"
               data-title="Báo cáo">
              <i class="bi bi-bar-chart me-2"></i> <span>Báo cáo</span>
            </a>
          @endif

          <!-- 8. Báo cáo công việc - cho tất cả role -->
          <a href="{{ route('work-reports.index') }}"
             class="list-group-item {{ request()->routeIs('work-reports.*')?'active':'' }}"
             data-title="Báo cáo công việc">
            <i class="bi bi-file-earmark-text me-2"></i> <span>Báo cáo công việc</span>
          </a>
          @endauth
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
        @if(Auth::user()->isAdmin() || Auth::user()->isDirector())
          <!-- Dropdown cho Quản lý nhân viên -->
          <div class="col nav-item dropdown-nav">
            <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('users.*') || request()->routeIs('employees.new.*') || request()->routeIs('departments.*')?'active':'' }}" 
               onclick="toggleBottomDropdown(this)" aria-expanded="false">
              <i class="bi bi-people"></i>
              <span>Quản lý</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item {{ request()->routeIs('users.*')?'active':'' }}" href="{{ route('users.index') }}">
                <i class="bi bi-person-check me-2"></i>Nhân viên
              </a></li>
              <li><a class="dropdown-item {{ request()->routeIs('employees.new.*')?'active':'' }}" href="{{ route('employees.new.index') }}">
                <i class="bi bi-person-plus me-2"></i>Nhân viên mới
              </a></li>
              <li><a class="dropdown-item {{ request()->routeIs('departments.*')?'active':'' }}" href="{{ route('departments.index') }}">
                <i class="bi bi-building me-2"></i>Phòng ban
              </a></li>
            </ul>
          </div>
        @endif
        
        <!-- Dropdown cho Báo cáo -->
        <div class="col nav-item dropdown-nav">
          <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('work-reports.*') || request()->routeIs('reports.index')?'active':'' }}" 
             onclick="toggleBottomDropdown(this)" aria-expanded="false">
            <i class="bi bi-file-earmark-text"></i>
            <span>Báo cáo</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item {{ request()->routeIs('work-reports.*')?'active':'' }}" href="{{ route('work-reports.index') }}">
              <i class="bi bi-file-earmark-text me-2"></i>Báo cáo công việc
            </a></li>
            @auth
            @if(Auth::user()->isAdmin() || Auth::user()->isDirector() || Auth::user()->isManager())
            <li><a class="dropdown-item {{ request()->routeIs('reports.index')?'active':'' }}" href="{{ route('reports.index') }}">
              <i class="bi bi-bar-chart me-2"></i>Báo cáo thống kê
            </a></li>
            @endif
            @endauth
          </ul>
        </div>

        <div class="col nav-item">
          <a href="{{ route('kanban') }}" 
             class="nav-link {{ request()->routeIs('kanban')?'active':'' }}">
            <i class="bi bi-columns"></i>
            <span>Kanban</span>
          </a>
        </div>

        @if(Auth::user()->isAdmin() || Auth::user()->isDirector() || Auth::user()->isManager())
          <div class="col nav-item">
            <a href="{{ route('create-task') }}" 
               class="nav-link {{ request()->routeIs('create-task')?'active':'' }}">
              <i class="bi bi-plus-square"></i>
              <span>Tạo việc</span>
            </a>
          </div>
        @endif
        @endauth
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
    
    // Bottom navigation dropdown functionality
    function toggleBottomDropdown(toggleElement) {
      event.preventDefault();
      event.stopPropagation();
      
      const dropdown = toggleElement.closest('.dropdown-nav');
      const dropdownMenu = dropdown.querySelector('.dropdown-menu');
      const isOpen = dropdownMenu.classList.contains('show');
      
      console.log('Toggle dropdown:', dropdown, dropdownMenu, isOpen);
      
      // Close all other dropdowns
      document.querySelectorAll('.bottom-nav .dropdown-menu').forEach(function(menu) {
        menu.classList.remove('show');
      });
      document.querySelectorAll('.bottom-nav .dropdown-toggle').forEach(function(toggle) {
        toggle.setAttribute('aria-expanded', 'false');
      });
      
      // Toggle current dropdown
      if (!isOpen) {
        dropdownMenu.classList.add('show');
        toggleElement.setAttribute('aria-expanded', 'true');
        console.log('Dropdown opened');
      } else {
        console.log('Dropdown closed');
      }
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
      if (!event.target.closest('.bottom-nav .dropdown-nav')) {
        document.querySelectorAll('.bottom-nav .dropdown-menu').forEach(function(menu) {
          menu.classList.remove('show');
        });
        document.querySelectorAll('.bottom-nav .dropdown-toggle').forEach(function(toggle) {
          toggle.setAttribute('aria-expanded', 'false');
        });
      }
    });
    
    // Close dropdown when selecting an item
    document.addEventListener('click', function(event) {
      if (event.target.closest('.bottom-nav .dropdown-item')) {
        const dropdown = event.target.closest('.dropdown-nav');
        const dropdownMenu = dropdown.querySelector('.dropdown-menu');
        const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
        
        dropdownMenu.classList.remove('show');
        dropdownToggle.setAttribute('aria-expanded', 'false');
      }
    });
  </script>
  @stack('scripts')
</body>
</html>
