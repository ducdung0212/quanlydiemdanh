<!DOCTYPE html>
<html lang="vi">
<head>
    @include('layouts_main.header')
</head>
<body>
    @include('layouts_main.sidebar')
    <div class="container mt-4">
        @yield('content')
    </div>
     @include('layouts_main.footer')

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
