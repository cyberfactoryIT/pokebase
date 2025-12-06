<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>@yield('mail_title', config('app.name'))</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9fafb; color: #222; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #e5e7eb; padding: 32px; }
        h1 { color: #2563eb; }
        .footer { margin-top: 32px; font-size: 12px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        @yield('mail_content')
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
