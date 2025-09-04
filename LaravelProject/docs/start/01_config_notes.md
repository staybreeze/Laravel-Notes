# *Laravel 配置設定 筆記*

---

## 1. **專案基本資訊查詢**（Project Information)

- 使用 `php artisan about` 指令可查看 Laravel 專案的 _基本資訊_。

```bash
# 查看專案基本資訊
php artisan about

# 只查看環境設定
php artisan about --only=environment

# 查看資料庫配置
php artisan config:show database
```

---

## 2. **環境變數設定**（Environment Variables)

### 2.1 *基本語法*

- 從**環境變數**取得設定值，若未設定則使用預設值。

```php
// 從環境變數 APP_DEBUG 取得設定值，若未設定則預設為 false
'debug' => env('APP_DEBUG', false),

// 公式說明：env('變數名稱', 預設值)
// → 取得指定環境變數的值，若不存在則回傳預設值
```

---

### 2.2 *環境判斷*

- 使用 `App::environment()` 來判斷**應用程式目前運行的環境**，並根據不同環境**執行**不同的程式邏輯。

```php
use Illuminate\Support\Facades\App;

// 取得目前應用程式的執行環境（例如：local、production、staging 等）
$environment = App::environment();

// 判斷目前環境是否為 'local'
if (App::environment('local')) {
    // 如果是 local 環境，會執行這裡的程式碼
}

// 判斷目前環境是否為 'local' 或 'staging'
if (App::environment(['local', 'staging'])) {
    // 如果是 local 或 staging 環境，會執行這裡的程式碼
}
```

---

## 3. **配置值讀取與設定**（Reading & Setting Configuration)

### 3.1 *讀取配置值*

- 可用 `Config` facade 或 `global config` 函式，透過「**點語法**」__從任何地方__ 讀取設定值，並可指定預設值。

```php
use Illuminate\Support\Facades\Config;

// 使用 Config facade
$value = Config::get('app.timezone');

// 使用 global config 函式
$value = config('app.timezone');

// 如果設定值不存在，則取得預設值
$value = config('app.timezone', 'Asia/Seoul');
```

---

### 3.2 *設定配置值*

- _動態_ 設定配置值，可用於**執行時修改**設定。

<!-- 
「動態」指的是在程式執行過程中直接修改設定值，
例如你可以根據條件、使用者輸入或程式邏輯，
隨時用 Config::set() 或 config() 來改變設定內容，
而不是只在設定檔裡寫死。
-->

```php
// 使用 Config facade 設定
Config::set('app.timezone', 'America/Chicago');

// 使用 global config 函式設定（陣列格式）
config(['app.timezone' => 'America/Chicago']);
```

---

## 4. **配置值型別驗證**（Configuration Type Validation)

- 如果取得的 _設定值型別不符預期_，會拋出例外。使用型別驗證方法可確保配置值的正確性。

```php
// 確保配置值為字串型別
Config::string('config-key');

// 確保配置值為整數型別
Config::integer('config-key');

// 確保配置值為浮點數型別
Config::float('config-key');

// 確保配置值為布林值型別
Config::boolean('config-key');

// 確保配置值為陣列型別
Config::array('config-key');
```

---

## 5. **配置快取管理**（Configuration Cache Management)

### 5.1 *清除配置快取*

- __清除快取__ 殘存的配置設定。

```bash
# 清除配置快取
php artisan config:clear
```

---

### 5.2 *快取配置設定*

- 將所有設定檔快 __取成一個檔案__ ，加速載入。

```bash
# 快取所有配置設定
php artisan config:cache
```

---

### 5.3 *發布配置檔案*

- 生成 Laravel 專案建立時 __沒有預設的配置檔案__。

```bash
# 發布特定配置檔案
php artisan config:publish

# 發布所有配置檔案
php artisan config:publish --all
```

---

## 6. **維護模式管理**（Maintenance Mode Management)

### 6.1 *啟用維護模式*

- 啟用 __維護模式__ ，讓 **網站暫時無法存取**。

```bash
# 啟用維護模式
php artisan down
```

---

### 6.2 *維護模式進階選項*

#### 6.2.1 **自動重新整理**

- 透過設定 `Refresh` _HTTP header_，可以*讓瀏覽器在指定秒數後自動重新整理頁面*，用來判斷維護是否結束。

*例如*：`Refresh: 10` 代表 __10 秒後自動刷新頁面，若刷新後頁面恢復正常，就表示維護已完成__。

```bash
# 設定 15 秒後自動重新整理
php artisan down --refresh=15
```
---

#### 6.2.2 **重試機制**

- 另一種可以*確認維護是否結束*的指令。

```bash
# 設定 60 秒後重試
php artisan down --retry=60
```

---

#### 6.2.3 **秘密存取**

- 使用 `秘密 token` 可以*跳過維護頁面*到指定頁面。

```bash

# （先）生成新的 secret token
php artisan down --with-secret

# （後）使用指定的 secret token
php artisan down --secret="1630542a-246b-4b66-afa1-dd72a4c43515"
```

---

#### 6.2.4 **自訂錯誤頁面**

- 避免部分用戶因 Laravel 渲染出錯導致錯誤，_直接標定好要導往的頁面_。

```bash
# 指定錯誤頁面
php artisan down --render="errors::503"
# 會讓維護模式時顯示你自訂的 resources/views/errors/503.blade.php 頁面，
# 而不是 Laravel 預設的維護畫面。
```

---

#### 6.2.5 **重導向設定**

- 維護期間*導向指定頁面*。

```bash
# 維護期間重導向到首頁
php artisan down --redirect=/
```

---

### 6.3 *停用維護模式*

- 維護結束，重啟網站。

```bash
# 停用維護模式，重啟網站
php artisan up
```

---

## 7. **最佳實踐建議**（Best Practices)

### 7.1 *環境變數管理*

- ✅ **建議**：使用 `.env` 檔案管理敏感配置
- ✅ **建議**：為 __所有配置值提供合理的預設值__
- ❌ **避免**：在程式碼中硬編碼配置值

---

### 7.2 *配置快取策略*

- ✅ **建議**：在生產環境使用 `php artisan config:cache`
- ✅ **建議**：開發環境使用 `php artisan config:clear`
- ❌ **避免**：在開發環境快取配置，會影響開發效率

---- 

### 7.3 *維護模式使用*

- ✅ **建議**：使用 `--secret` 選項提供管理員存取
- ✅ **建議**：設定適當的 `--retry` 時間
- ❌ **避免**：長時間維護而不通知用戶

---

## 8. **常見使用場景**（Common Use Cases)

### 8.1 *開發環境配置*

```php
// 在開發環境啟用除錯模式
if (App::environment('local')) {
    config(['app.debug' => true]);
    config(['app.env' => 'local']);
}
```

---

### 8.2 *生產環境配置*

```php
// 在生產環境停用除錯模式
if (App::environment('production')) {
    config(['app.debug' => false]); // 關閉 debug，避免洩漏錯誤訊息
    config(['app.env' => 'production']); // 確保環境為 production
}
```

```php
// 例：控制器裡故意使用未定義變數
public function index()
{
    return $undefinedVariable; // 這裡會拋出錯誤
}

// 如果 config(['app.debug' => true]) 已開啟，瀏覽器會顯示類似下方錯誤畫面：
//
// ErrorException
// Undefined variable: undefinedVariable
// in app/Http/Controllers/ExampleController.php:10
//
// 堆疊追蹤（Stack trace）
// #0 app/Http/Controllers/ExampleController.php(10): ...
// #1 ...
//
// 這樣可以幫助開發者快速找到錯誤位置和原因。

// 如果 config(['app.debug' => false]) 已關閉，瀏覽器只會顯示通用錯誤頁面：
//
// 500 Server Error
// Sorry, something went wrong.
// 
// 不會顯示詳細錯誤訊息和堆疊追蹤，保護系統安全，避免洩漏敏感資訊。
```

<!-- 
堆疊追蹤（Stack trace） 是指程式發生錯誤時，
顯示目前執行到哪一行、經過哪些函式或方法呼叫，
可以幫助開發者快速找到錯誤發生的位置和原因，
通常會列出呼叫路徑和檔案行號。 
-->

---

### 8.3 *動態配置修改*

```php
// 根據用戶設定動態修改時區
$userTimezone = $user->timezone ?? 'UTC';
config(['app.timezone' => $userTimezone]);
```

---

## 9. **故障排除**（Troubleshooting)

### 9.1 *配置值讀取問題*

```bash
# 檢查配置是否正確載入
php artisan config:show

# 清除配置快取
php artisan config:clear

# 重新快取配置
php artisan config:cache
```

---

### 9.2 *維護模式問題*

```bash
# 檢查維護模式狀態
php artisan about --only=environment

# 強制停用維護模式
php artisan up
```

---

## 10. **相關指令參考**（Related Commands)

| 指令                        | 說明                  | 使用場景         |
|-----------------------------|----------------------|----------------|
| `php artisan about`         | 查看專案 *基本資訊*     | 專案狀態檢查     |
| `php artisan config:show`   | 顯示 *配置值*          | 配置檢查        |
| `php artisan config:clear`  | *清除* 配置快取         | 開發環境        |
| `php artisan config:cache`  | *快取* 配置設定         | 生產環境        |
| `php artisan config:publish`| *發布* 配置檔案         | 擴展配置        |
| `php artisan down`          | *啟用* 維護模式         | 系統維護        |
| `php artisan up`            | *停用* 維護模式         | 維護完成        |