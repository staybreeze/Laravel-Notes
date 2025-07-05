<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Logging 示範</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- 標題 -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-2">Laravel Logging 示範</h1>
                <p class="text-gray-600">測試各種日誌記錄功能與配置</p>
            </div>

            <!-- 功能卡片 -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- 基本日誌記錄 -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">基本日誌記錄</h3>
                    <p class="text-gray-600 mb-4">測試所有日誌等級（emergency 到 debug）</p>
                    <button onclick="testLogging('basic')" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded transition duration-200">
                        執行測試
                    </button>
                </div>

                <!-- 情境資料記錄 -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">情境資料記錄</h3>
                    <p class="text-gray-600 mb-4">測試帶有上下文資訊的日誌記錄</p>
                    <div class="mb-3">
                        <input type="number" id="user_id" placeholder="用戶 ID" value="1" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-3">
                        <select id="action" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="login">登入</option>
                            <option value="logout">登出</option>
                            <option value="purchase">購買</option>
                        </select>
                    </div>
                    <button onclick="testLogging('contextual')" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded transition duration-200">
                        執行測試
                    </button>
                </div>

                <!-- Channel 記錄 -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Channel 記錄</h3>
                    <p class="text-gray-600 mb-4">測試指定 channel 和動態 channel</p>
                    <button onclick="testLogging('channel')" class="w-full bg-purple-500 hover:bg-purple-600 text-white font-medium py-2 px-4 rounded transition duration-200">
                        執行測試
                    </button>
                </div>

                <!-- 例外處理 -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">例外處理</h3>
                    <p class="text-gray-600 mb-4">測試例外記錄與錯誤處理</p>
                    <button onclick="testLogging('exception')" class="w-full bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded transition duration-200">
                        執行測試
                    </button>
                </div>

                <!-- 效能監控 -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">效能監控</h3>
                    <p class="text-gray-600 mb-4">測試效能相關的日誌記錄</p>
                    <button onclick="testLogging('performance')" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded transition duration-200">
                        執行測試
                    </button>
                </div>

                <!-- 業務邏輯 -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">業務邏輯</h3>
                    <p class="text-gray-600 mb-4">測試業務相關的日誌記錄</p>
                    <div class="mb-3">
                        <input type="number" id="amount" placeholder="訂單金額" value="100" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button onclick="testLogging('business')" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-medium py-2 px-4 rounded transition duration-200">
                        執行測試
                    </button>
                </div>

                <!-- 測試所有等級 -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">測試所有等級</h3>
                    <p class="text-gray-600 mb-4">一次性測試所有日誌等級</p>
                    <button onclick="testLogging('test-levels')" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded transition duration-200">
                        執行測試
                    </button>
                </div>
            </div>

            <!-- 結果顯示區域 -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">測試結果</h3>
                <div id="result" class="bg-gray-100 rounded p-4 min-h-[100px]">
                    <p class="text-gray-500">點擊上方按鈕開始測試...</p>
                </div>
            </div>

            <!-- 日誌檔案資訊 -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">日誌檔案位置</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">主要日誌檔案：</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• <code class="bg-gray-200 px-1 rounded">storage/logs/laravel.log</code></li>
                            <li>• <code class="bg-gray-200 px-1 rounded">storage/logs/custom.log</code></li>
                            <li>• <code class="bg-gray-200 px-1 rounded">storage/logs/dynamic.log</code></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">業務日誌檔案：</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• <code class="bg-gray-200 px-1 rounded">storage/logs/orders.log</code></li>
                            <li>• <code class="bg-gray-200 px-1 rounded">storage/logs/payments.log</code></li>
                            <li>• <code class="bg-gray-200 px-1 rounded">storage/logs/security.log</code></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 使用說明 -->
            <div class="bg-blue-50 rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold text-blue-800 mb-3">使用說明</h3>
                <div class="text-blue-700 space-y-2">
                    <p>• 點擊各功能卡片中的按鈕來測試不同的日誌記錄功能</p>
                    <p>• 測試完成後，可以在 <code class="bg-blue-200 px-1 rounded">storage/logs/</code> 目錄下查看對應的日誌檔案</p>
                    <p>• 使用 <code class="bg-blue-200 px-1 rounded">php artisan pail</code> 命令可以即時監控日誌</p>
                    <p>• 不同等級的日誌會根據配置寫入不同的檔案或發送到不同的服務</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function testLogging(type) {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p class="text-blue-500">測試執行中...</p>';

            try {
                let url = `/logging-demo/${type}`;
                let params = new URLSearchParams();

                // 根據測試類型添加參數
                if (type === 'contextual') {
                    params.append('user_id', document.getElementById('user_id').value);
                    params.append('action', document.getElementById('action').value);
                } else if (type === 'business') {
                    params.append('amount', document.getElementById('amount').value);
                }

                if (params.toString()) {
                    url += '?' + params.toString();
                }

                const response = await fetch(url);
                const data = await response.json();

                resultDiv.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        <strong>測試成功！</strong><br>
                        <p class="mt-2">${data.message}</p>
                        ${data.logs ? `<p class="mt-1 text-sm">${data.logs}</p>` : ''}
                        ${data.user_id ? `<p class="mt-1 text-sm">用戶 ID: ${data.user_id}</p>` : ''}
                        ${data.action ? `<p class="mt-1 text-sm">動作: ${data.action}</p>` : ''}
                        ${data.order_id ? `<p class="mt-1 text-sm">訂單 ID: ${data.order_id}</p>` : ''}
                        ${data.amount ? `<p class="mt-1 text-sm">金額: ${data.amount}</p>` : ''}
                        ${data.execution_time_ms ? `<p class="mt-1 text-sm">執行時間: ${data.execution_time_ms}ms</p>` : ''}
                        ${data.levels ? `<p class="mt-1 text-sm">測試等級: ${data.levels.join(', ')}</p>` : ''}
                    </div>
                `;
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <strong>測試失敗！</strong><br>
                        <p class="mt-2">${error.message}</p>
                    </div>
                `;
            }
        }
    </script>
</body>
</html> 