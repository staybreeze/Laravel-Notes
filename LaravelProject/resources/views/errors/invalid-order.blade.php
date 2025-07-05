<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>無效訂單 - 錯誤</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Microsoft JhengHei', sans-serif;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: white;
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .error-title {
            font-size: 2rem;
            font-weight: bold;
            margin: 1rem 0;
        }

        .error-message {
            font-size: 1.1rem;
            margin: 1rem 0;
            opacity: 0.9;
            line-height: 1.6;
        }

        .order-details {
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 10px;
            margin: 1.5rem 0;
            text-align: left;
        }

        .order-details h3 {
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .order-details p {
            margin: 0.3rem 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-primary {
            background: rgba(255,255,255,0.2);
            color: white;
            border-color: rgba(255,255,255,0.3);
        }

        .btn-primary:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border-color: rgba(255,255,255,0.5);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.1);
        }

        .help-text {
            margin-top: 2rem;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .help-text a {
            color: white;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .error-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">無效訂單</h1>
        <p class="error-message">
            {{ $message ?? '抱歉，您的訂單無法處理。' }}
        </p>
        
        @if(isset($orderId))
        <div class="order-details">
            <h3>訂單資訊</h3>
            <p><strong>訂單編號：</strong>{{ $orderId }}</p>
            <p><strong>錯誤時間：</strong>{{ now()->format('Y-m-d H:i:s') }}</p>
            <p><strong>錯誤代碼：</strong>422</p>
        </div>
        @endif
        
        <div class="action-buttons">
            <a href="{{ url('/orders') }}" class="btn btn-primary">查看我的訂單</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">回到首頁</a>
        </div>
        
        <div class="help-text">
            如果問題持續發生，請 <a href="{{ url('/contact') }}">聯絡客服</a> 或撥打服務專線：0800-123-456
        </div>
    </div>
</body>
</html> 