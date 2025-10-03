<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','HP Foods')</title>
  
  {{-- Favicon --}}
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="{{ asset('css/home.css') }}">
  @stack('styles')
</head>
<body class="bg-light">

  <div class="container-fluid">
    {{-- Nội dung full-width, không navbar, không sidebar --}}
    <main class="py-3">
      @yield('content')
    </main>
  </div>

  @auth
          @if(Auth::user()->isAdmin() || Auth::user()->isDirector() || Auth::user()->isManager())
    <a href="{{ route('create-task') }}"
       class="btn btn-success position-fixed"
       style="right:24px; bottom:24px; z-index:1050;">
       ➕ Tạo công việc
    </a>
  @endif
  @endauth

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
  <script src="{{ asset('js/home.js') }}"></script>
  @stack('scripts')
</body>
</html>
