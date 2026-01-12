<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe Confirmation</title>
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
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .email {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            font-family: monospace;
            color: #495057;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        }
        form {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📭 Unsubscribe</h1>
        <p>Are you sure you want to unsubscribe from our mailing list?</p>
        
        <div class="email">{{ $subscriber->email }}</div>
        
        <p style="font-size: 14px; color: #999;">
            You will no longer receive emails from us.
        </p>
        
        <form action="{{ route('unsubscribe.confirm', $subscriber) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">
                Yes, Unsubscribe Me
            </button>
        </form>
    </div>
</body>
</html>
