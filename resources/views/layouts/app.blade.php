<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laravel OTP Verification')</title>
    <style>
        html, body { height: 100%; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
        }
        .container { width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        @if(session('success'))
            <div style="padding: 10px; margin-bottom: 20px; border-radius: 4px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="padding: 10px; margin-bottom: 20px; border-radius: 4px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
