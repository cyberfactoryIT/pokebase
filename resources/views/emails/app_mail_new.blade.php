<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('mail_title', config('app.name'))</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #000000;
            color: #ffffff;
            line-height: 1.6;
            padding: 20px;
        }
        
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #000000;
        }
        
        .logo-container {
            text-align: center;
            padding: 40px 20px 20px;
        }
        
        .logo {
            max-width: 120px;
            height: auto;
        }
        
        .email-container {
            background-color: #161615;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 32px;
            margin: 20px 0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }
        
        h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        p {
            color: #d1d5db;
            font-size: 16px;
            margin-bottom: 16px;
        }
        
        .button {
            display: inline-block;
            padding: 12px 32px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            transition: all 0.3s ease;
        }
        
        .button:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.6);
        }
        
        .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 24px 0;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
        }
        
        .footer a {
            color: #9ca3af;
            text-decoration: none;
        }
        
        .footer a:hover {
            color: #ffffff;
        }
        
        .info-box {
            background-color: rgba(59, 130, 246, 0.1);
            border-left: 4px solid #3b82f6;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .info-box p {
            margin-bottom: 0;
            color: #d1d5db;
        }
        
        .warning-box {
            background-color: rgba(251, 146, 60, 0.1);
            border-left: 4px solid #fb923c;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .warning-box p {
            margin-bottom: 0;
            color: #fed7aa;
        }
        
        @media only screen and (max-width: 600px) {
            .email-container {
                padding: 24px;
            }
            
            h1 {
                font-size: 20px;
            }
            
            p {
                font-size: 14px;
            }
            
            .button {
                padding: 10px 24px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Logo -->
        <div class="logo-container">
            <img src="{{ asset('images/logo_basecard.svg') }}" alt="{{ config('app.name') }}" class="logo">
        </div>
        
        <!-- Main Content -->
        <div class="email-container">
            @yield('mail_content')
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>
                <a href="{{ config('app.url') }}">{{ __('Visit our website') }}</a>
            </p>
        </div>
    </div>
</body>
</html>
