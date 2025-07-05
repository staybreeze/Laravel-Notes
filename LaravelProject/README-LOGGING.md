# Laravel Logging 實作說明

## 概述

本專案包含完整的 Laravel Logging 實作範例，展示如何使用 Laravel 的日誌系統進行各種類型的日誌記錄。

## 檔案結構

```
app/
├── Http/
│   ├── Controllers/
│   │   └── LoggingDemoController.php    # 日誌示範控制器
│   └── Middleware/
│       └── AssignRequestId.php          # 請求 ID 中間件
├── Logging/
│   ├── CustomizeFormatter.php           # 自訂日誌格式器
│   └── CreateCustomLogger.php           # 自訂日誌建立器
config/
└── logging.php                          # 日誌配置檔案
resources/
└── views/
    └── demo/
        └── logging/
            └── index.blade.php          # 日誌示範頁面
routes/
└── web.php                              # 路由配置
```

## 功能特色

### 1. 基本日誌記錄
- 支援所有 8 個日誌等級（emergency 到 debug）
- 自動記錄到預設 channel

### 2. 情境資料記錄
- 支援傳遞額外上下文資訊
- 自動記錄請求相關資訊（IP、User Agent 等）

### 3. 多 Channel 支援
- 指定特定 channel 記錄
- 動態建立 channel
- Stack 多通道聚合

### 4. 自訂配置
- 自訂日誌格式
- **自訂 Logger Factory：自己寫一個工廠，專門生產你想要的 Logger（記錄器），讓 log 系統彈性最大化。適合需要完全控制 log 行為、串接特殊服務、做複合邏輯時使用。**
    - 生活化比喻：Laravel 預設的 log channel 就像「買現成便當」，自訂 Logger Factory 就像「自己下廚，想加什麼料都可以」。
- **多種 Handler 和 Formatter：Handler 決定 log 要送去哪裡（如檔案、slack、email），Formatter 決定 log 的內容長什麼樣子（如純文字、JSON、加上 trace id）。一條 log 可以同時送多個地方、用不同格式。**
    - 生活化比喻：Handler 就像「快遞公司」，Formatter 就像「包裝方式」。
- 多種 Handler 和 Formatter

### 5. 業務邏輯日誌
- 訂單記錄
- 付款記錄
- 安全事件記錄

### 6. 效能監控
- 執行時間記錄
- 記憶體使用量記錄
- 效能分析

## 配置說明

### 主要 Channels

1. **stack** - 預設聚合通道
   - 包含：single, daily
   - 用途：一般應用日誌

2. **custom_single** - 自訂格式通道
   - 使用自訂 Formatter
   - 用途：特殊格式需求

3. **orders** - 訂單日誌
   - 保留 30 天
   - 用途：訂單相關記錄

4. **payments** - 付款日誌
   - 保留 90 天
   - 用途：付款相關記錄

5. **security** - 安全日誌
   - 保留 365 天
   - 用途：安全事件記錄

6. **performance** - 效能日誌
   - JSON 格式
   - 用途：效能監控

7. **web** - Web 請求日誌
   - 包含 Web 處理器
   - 用途：請求追蹤

## 使用方法

### 1. 啟動應用程式
```bash
php artisan serve
```

### 2. 訪問示範頁面
```
http://localhost:8000/logging-demo
```

### 3. 測試各種功能
- 點擊各功能卡片進行測試
- 查看測試結果
- 檢查日誌檔案

### 4. 查看日誌檔案
```bash
# 主要日誌
tail -f storage/logs/laravel.log

# 自訂日誌
tail -f storage/logs/custom.log

# 業務日誌
tail -f storage/logs/orders.log
tail -f storage/logs/payments.log
tail -f storage/logs/security.log
```

### 5. 使用 Pail 即時監控
```bash
# 安裝 Pail
composer require --dev laravel/pail

# 即時監控
php artisan pail

# 過濾特定等級
php artisan pail --level=error

# 過濾特定訊息
php artisan pail --message="User created"
```

## API 端點

### 基本日誌記錄
```
GET /logging-demo/basic
```

### 情境資料記錄
```
GET /logging-demo/contextual?user_id=1&action=login
```

### Channel 記錄
```
GET /logging-demo/channel
```

### 例外處理
```
GET /logging-demo/exception
```

### 效能監控
```
GET /logging-demo/performance
```

### 業務邏輯
```
GET /logging-demo/business?amount=100
```

### 測試所有等級
```
GET /logging-demo/test-levels
```

## 自訂類別說明

### CustomizeFormatter
- 自訂日誌格式
- 支援時間戳、等級、訊息、上下文
- 可設定日期格式

### CreateCustomLogger
- **自訂 Logger Factory：讓你完全掌控 log 行為，想怎麼組合都可以。適合進階需求、團隊有特殊規範時。**
- **多種 Handler：一條 log 可以同時送多個地方（如同時寫檔案、發 slack、寄 email）**
- **多種 Formatter：log 內容格式可以完全自訂（如純文字、JSON、加上 trace id）**
- 功用：彈性、可擴充、滿足各種進階需求
- 生活化比喻：Handler 就像「快遞公司」，Formatter 就像「包裝方式」。
- 範例：
```php
public function __invoke(array $config): Logger
{
    $logger = new Logger('custom');
    // Handler：同時寫檔案和 Slack
    $logger->pushHandler(new StreamHandler(storage_path('logs/custom.log')));
    $logger->pushHandler(new SlackWebhookHandler('webhook_url'));
    // Formatter：自訂格式
    foreach ($logger->getHandlers() as $handler) {
        $handler->setFormatter(new LineFormatter('[%datetime%] %message%'));
    }
    return $logger;
}
```

### AssignRequestId
- 為每個請求分配唯一 ID
- 自動記錄請求資訊
- 支援上下文傳遞

## 最佳實踐

### 1. 日誌等級使用
- **emergency**: 系統完全無法運作
- **alert**: 需要立即行動
- **critical**: 嚴重錯誤
- **error**: 一般錯誤
- **warning**: 警告訊息
- **notice**: 注意事項
- **info**: 一般資訊
- **debug**: 除錯資訊

### 2. 情境資料
- 記錄用戶 ID、請求 ID
- 包含相關業務資料
- 避免記錄敏感資訊

### 3. 效能考量
- 避免在生產環境記錄 debug 等級
- 適當使用 channel 分離
- 定期清理舊日誌

### 4. 安全性
- 不記錄密碼、token 等敏感資訊
- 使用適當的日誌等級
- 控制日誌檔案權限

## 故障排除

### 1. 日誌檔案不存在
```bash
# 確保 storage/logs 目錄存在
mkdir -p storage/logs

# 設定適當權限
chmod 755 storage/logs
```

### 2. 權限問題
```bash
# 設定 storage 目錄權限
chmod -R 755 storage
chown -R www-data:www-data storage
```

### 3. 配置問題
```bash
# 清除配置快取
php artisan config:clear

# 重新載入配置
php artisan config:cache
```

## 擴展建議

### 1. 新增 Handler
- 新增 Slack 通知
- 新增 Email 通知
- 新增資料庫記錄

### 2. 新增 Formatter
- JSON 格式
- XML 格式
- 自訂格式

### 3. 新增 Processor
- 用戶資訊處理器
- 環境資訊處理器
- 自訂處理器

### 4. 監控整合
- 整合 ELK Stack
- 整合 Grafana
- 整合 Prometheus

## 相關資源

- [Laravel Logging 官方文件](https://laravel.com/docs/logging)
- [Monolog 文件](https://github.com/Seldaek/monolog)
- [Laravel Pail](https://github.com/laravel/pail) 