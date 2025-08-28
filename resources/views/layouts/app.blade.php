<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','HP Foods')</title>
  
  <!-- FontAwesome CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-light">
  @include('partials.navbar')
  <main class="container py-4">
    @yield('content')
  </main>
</body>
</html>
