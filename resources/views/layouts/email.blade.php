<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Notification')</title>
</head>
<body style="font-family: sans-serif; background-color: #f8fafc; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="max-height: 50px;">
            <h1 style="color: #0f172a; font-size: 24px; font-weight: bold; margin-top: 10px;">@yield('header')</h1>
        </div>
        
        @yield('content')
        
    </div>
</body>
</html>
