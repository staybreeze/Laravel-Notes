<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>找不到頁面 - 404</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Microsoft JhengHei', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        }

        .error-code {
            font-size: 8rem;
            font-weight: bold;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            line-height: 1;
        }

        .error-message {
            font-size: 1.5rem;
            margin: 1rem 0;
            font-weight: 300;
        }

        .error-description {
            font-size: 1rem;
            margin: 1rem 0 2rem 0;
            opacity: 0.9;
            line-height: 1.6;
        }

        .home-link {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            margin-top: 2rem;
            transition: all 0.3s ease;
            border: 2px solid rgba(255,255,255,0.3);
            font-weight: 500;
        }

        .home-link:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .search-box {
            margin: 2rem 0;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }

        .search-input {
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 25px;
            width: 300px;
            font-size: 1rem;
            outline: none;
        }

        .search-button {
            padding: 0.8rem 1.5rem;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-button:hover {
            background: rgba(255,255,255,0.3);
        }

        .helpful-links {
            margin-top: 3rem;
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .helpful-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .helpful-link:hover {
            color: white;
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 6rem;
            }
            
            .error-message {
                font-size: 1.2rem;
            }
            
            .search-input {
                width: 250px;
            }
            
            .helpful-links {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <p class="error-message">糟糕！找不到您要的頁面</p>
        <p class="error-description">
            這個頁面可能已經被移除、重新命名，或者暫時無法使用。
            <br>請檢查網址是否正確，或使用搜尋功能找到您需要的內容。
        </p>
        
        <div class="search-box">
            <input type="text" class="search-input" placeholder="搜尋網站內容..." id="searchInput">
            <button class="search-button" onclick="performSearch()">搜尋</button>
        </div>
        
        <a href="{{ url('/') }}" class="home-link">回到首頁</a>
        
        <div class="helpful-links">
            <a href="{{ url('/') }}" class="helpful-link">首頁</a>
            <a href="{{ url('/about') }}" class="helpful-link">關於我們</a>
            <a href="{{ url('/contact') }}" class="helpful-link">聯絡我們</a>
            <a href="{{ url('/help') }}" class="helpful-link">幫助中心</a>
        </div>
    </div>

    <script>
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value;
            if (searchTerm.trim()) {
                // 這裡可以實作搜尋功能
                alert('搜尋功能：' + searchTerm);
            }
        }

        // 按 Enter 鍵搜尋
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    </script>
</body>
</html> 