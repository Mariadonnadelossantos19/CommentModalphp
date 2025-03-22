<!DOCTYPE html>
<html>
<head>
    <title>Comments System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('styles')
</head>
<body>
    <div class="container">
        @yield('content')
    </div>

    @yield('scripts')
</body>
</html> 