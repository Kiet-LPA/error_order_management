<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','HP Foods')</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-light">
  @include('partials.navbar')
  <main class="container py-4">
    @yield('content')
  </main>
</body>
</html>
