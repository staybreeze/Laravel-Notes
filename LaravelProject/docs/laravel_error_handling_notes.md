# Laravel 錯誤處理實作指南

## 📁 檔案結構

```
LaravelProject/
├── app/
│   ├── Exceptions/
│   │   ├── InvalidOrderException.php      # 自訂訂單例外
│   │   └── PodcastProcessingException.php # 不記錄的例外
│   └── Http/Controllers/
│       └── ErrorHandlingDemoController.php # 錯誤處理示範控制器
├── resources/views/
│   └── errors/
│       ├── 404.blade.php                  # 美觀的 404 錯誤頁面
│       └── invalid-order.blade.php        # 自訂業務錯誤頁面
├── config/
│   └── logging.php                        # 包含自訂 orders log channel
├── routes/
│   └── web.php                           # 包含示範路由
└── resources/views/notes/
    └── error-handling.blade.php          # 完整教學筆記
```

## 🚀 快速開始

### 1. 測試基本錯誤處理
```bash
# 基本錯誤處理示範
curl http://localhost:8000/demo/basic-error-handling

# 自訂例外示範
curl http://localhost:8000/demo/custom-exception

# 不記錄的例外示範
curl http://localhost:8000/demo/non-reportable
```

### 2. 測試 abort 輔助函式
```bash
# 測試不同類型的錯誤
curl http://localhost:8000/demo/abort?action=not_found
curl http://localhost:8000/demo/abort?action=unauthorized
curl http://localhost:8000/demo/abort?action=forbidden
curl http://localhost:8000/demo/abort?action=validation
curl http://localhost:8000/demo/abort?action=server_error
```

### 3. 測試條件性錯誤處理
```bash
# 測試 JSON 請求
curl -H "Accept: application/json" http://localhost:8000/demo/conditional?user_id=123&action=edit

# 測試 HTML 請求
curl http://localhost:8000/demo/conditional?user_id=123&action=edit
```

### 4. 測試錯誤頁面
```bash
# 測試 404 錯誤頁面
curl http://localhost:8000/test-404

# 測試自訂錯誤頁面
curl http://localhost:8000/test-invalid-order
```

## 📚 實作範例說明

### 1. 自訂例外類別

#### InvalidOrderException.php
- **功能**：處理訂單相關錯誤
- **特色**：
  - 自訂 `report()` 方法，記錄到專門的 orders log
  - 自訂 `render()` 方法，支援 JSON 和 HTML 回應
  - 自訂 `context()` 方法，提供額外上下文資訊
  - 包含訂單 ID 等業務資料

#### PodcastProcessingException.php
- **功能**：處理播客處理錯誤
- **特色**：
  - 實作 `ShouldntReport` 介面，不會被記錄
  - 自訂 `render()` 方法，提供友善的錯誤訊息
  - 適用於暫時性、可恢復的錯誤

### 2. 錯誤處理控制器

#### ErrorHandlingDemoController.php
包含多種錯誤處理示範：

1. **基本錯誤處理** (`basicErrorHandling`)
   - 使用 try-catch 捕獲錯誤
   - 使用 `report()` 記錄但不中斷流程

2. **自訂例外** (`customException`)
   - 拋出自訂例外類別
   - 展示例外如何被處理

3. **不記錄的例外** (`nonReportableException`)
   - 展示 `ShouldntReport` 介面的使用

4. **abort 輔助函式** (`abortDemo`)
   - 展示不同 HTTP 狀態碼的使用
   - 包含 404、401、403、422、500 等

5. **條件性錯誤處理** (`conditionalErrorHandling`)
   - 根據請求類型返回不同格式
   - 展示 JSON vs HTML 回應的處理

### 3. 自訂錯誤頁面

#### 404.blade.php
- **特色**：
  - 美觀的漸層背景設計
  - 響應式設計，支援手機瀏覽
  - 包含搜尋功能
  - 提供有用的連結

#### invalid-order.blade.php
- **特色**：
  - 業務導向的錯誤頁面
  - 顯示訂單詳細資訊
  - 提供明確的後續動作
  - 包含客服聯絡資訊

### 4. 日誌配置

#### logging.php
- **新增 orders channel**：
  - 專門記錄訂單相關錯誤
  - 使用 daily driver，保留 30 天
  - 獨立於主要應用程式日誌

## 🎯 最佳實踐

### 1. 例外分類
- **業務例外**：如 `InvalidOrderException`，包含業務邏輯
- **技術例外**：如資料庫連接錯誤，純技術問題
- **暫時性例外**：如 `PodcastProcessingException`，可恢復

### 2. 錯誤記錄
- **重要錯誤**：完整記錄，包含上下文
- **一般錯誤**：基本記錄
- **預期錯誤**：可忽略或輕量記錄

### 3. 用戶體驗
- **友善訊息**：避免技術術語
- **明確指引**：告訴用戶下一步該做什麼
- **一致設計**：錯誤頁面風格統一

### 4. 開發 vs 生產
- **開發環境**：詳細錯誤資訊，方便除錯
- **生產環境**：保護敏感資訊，提供統一錯誤頁面

## 🔧 進階配置

### 1. 在 bootstrap/app.php 中配置全域錯誤處理
```php
->withExceptions(function (Exceptions $exceptions) {
    // 全域 log context
    $exceptions->context(fn () => [
        'app_version' => config('app.version'),
        'environment' => config('app.env'),
    ]);

    // 去重複記錄
    $exceptions->dontReportDuplicates();

    // 自訂例外報告
    $exceptions->report(function (InvalidOrderException $e) {
        // 特殊處理邏輯
    });

    // 自訂例外渲染
    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        if ($request->is('api/*')) {
            return response()->json(['message' => 'Resource not found'], 404);
        }
    });

    // 節流設定
    $exceptions->throttle(function (Throwable $e) {
        if ($e instanceof BroadcastException) {
            return Limit::perMinute(300);
        }
    });
})
```

### 2. 監控與警報
- 使用 Sentry、Flare 等外部服務
- 設定錯誤統計和趨勢分析
- 配置重要錯誤的自動警報

## 📖 學習資源

- **教學筆記**：`resources/views/notes/error-handling.blade.php`
- **官方文件**：https://laravel.com/docs/error-handling
- **實作範例**：本專案中的所有檔案

## 🎉 總結

這個實作展示了 Laravel 錯誤處理的完整流程：
1. **理論學習**：透過教學筆記了解概念
2. **實作練習**：透過範例程式碼學習實作
3. **實際應用**：透過示範路由測試功能
4. **最佳實踐**：透過配置和設計學習最佳做法

透過這些實作，您可以建立穩定、友善且易於維護的錯誤處理系統！ 