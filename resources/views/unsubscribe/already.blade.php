<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Already Unsubscribed</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        .info-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            color: #17a2b8;
            margin-bottom: 20px;
            font-size: 28px;
        }
        p {
            color: #666;
            line-height: 1.6;
        }
        .email {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            font-family: monospace;
            color: #495057;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="info-icon">ℹ️</div>
        <h1>Already Unsubscribed</h1>
        <p>This email address is already unsubscribed from our mailing list.</p>
        
        <div class="email">{{ $subscriber->email }}</div>
        
        <p style="font-size: 14px; color: #999;">
            You will not receive any emails from us.
        </p>
    </div>
</body>
</html>
