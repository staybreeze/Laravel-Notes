# *Laravel 部署 筆記*

---

## 1. **伺服器設定**（Server Configuration)

### 1.1 *Nginx 伺服器設定*

- Nginx 伺服器設定檔，確保所有請求都導向到 `public/index.php`。
- **重要**：勿將 `index.php` *搬到專案根目錄，否則會暴露敏感設定檔給公網*。

```yaml
server {
    listen 80; # 監聽 IPv4 的 80 埠（HTTP）
    listen [::]:80; # 監聽 IPv6 的 80 埠
    server_name example.com; # 伺服器網域名稱
    root /srv/example.com/public; # 網站根目錄

    # 安全性標頭
    add_header X-Frame-Options "SAMEORIGIN"; # 防止網頁被嵌入 iframe
    add_header X-Content-Type-Options "nosniff"; # 防止瀏覽器猜測 MIME 類型

    index index.php; # 預設首頁檔案
    charset utf-8; # 頁面編碼

    # 主要路由處理
    location / {
        try_files $uri $uri/ /index.php?$query_string; # 優先尋找靜態檔案，否則導向 index.php
    }

    # 靜態檔案處理
    location = /favicon.ico { access_log off; log_not_found off; } # 關閉 favicon.ico 的存取紀錄
    location = /robots.txt  { access_log off; log_not_found off; } # 關閉 robots.txt 的存取紀錄

    # 錯誤頁面處理
    error_page 404 /index.php; # 404 錯誤導向 index.php

    # PHP 處理
    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; # 交給 PHP-FPM 處理
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name; # 設定 PHP 執行檔案路徑
        include fastcgi_params; # 載入 FastCGI 參數
        fastcgi_hide_header X-Powered-By; # 隱藏 X-Powered-By 標頭
    }

    # 隱藏檔案保護
    location ~ /\.(?!well-known).* {
        deny all; # 禁止存取 . 開頭的隱藏檔案（除了 .well-known）
    }
}
```

---

### 1.2 *FrankenPHP 設定*

- FrankenPHP 是一個 __內建 `HTTP 伺服器`（可以直接處理網頁請求）、`FastCGI`（執行 PHP 程式）、`Worker`（支援背景任務） 與多種現代功能的 PHP 執行環境__，讓你可以直接用一個`二進位檔`啟動 PHP 專案，不需要額外安裝 Nginx、Apache 或 PHP-FPM，適合快速開發、測試和部署現代 PHP 應用程式。

<!-- 
CGI（Common Gateway Interface，通用閘道介面）
是伺服器和外部程式（如腳本、應用程式）溝通的標準端點，
讓伺服器可以呼叫外部程式處理請求並回傳結果，
不是伺服器內部通訊，而是伺服器和外部程式的橋樑。
 -->

<!-- 
「標準端點」指的是一種固定的溝通介面或入口，
讓不同系統或程式可以用一致的方式互相傳遞資料或請求，
例如 CGI 就是 Web 伺服器和外部程式之間的標準端點，
大家都遵守同樣的規則來溝通。 
-->

- __一般專案__ 還是多用 _Nginx、Apache 搭配 PHP-FPM_

- `FrankenPHP` 適合快速開發、測試或特殊部署需求
- 但在 __大型或傳統__ 生產環境，主流還是 _Nginx/Apache + PHP-FPM_

- `FastCGI` 是**一種網頁伺服器和應用程式（如 PHP）之間溝通的協定**，它可以讓伺服器高效地執行和管理 PHP 程式，比傳統 CGI 更快、更省資源，常用於 _Nginx、Apache 搭配 PHP-FPM_。

```bash
# 使用 FrankenPHP 啟動伺服器
frankenphp php-server -r public/
```

---

## 2. **目錄權限設定**（Directory Permissions)

### 2.1 *必要寫入權限*

- Laravel 必須能寫入以下目錄，請確保這些目錄對 Web 伺服器執行者（如 `www-data`）有寫入權限。

```bash
# 設定目錄權限
sudo chown -R www-data:www-data /path/to/laravel      # 設定擁有者為 www-data
sudo chmod -R 755 /path/to/laravel                    # 設定目錄權限為 755

# 特別設定寫入目錄
sudo chmod -R 775 /path/to/laravel/bootstrap/cache    # 允許群組寫入
sudo chmod -R 775 /path/to/laravel/storage            # 允許群組寫入

# 或者使用更寬鬆的權限（僅限開發環境）
sudo chmod -R 777 /path/to/laravel/bootstrap/cache    # 允許所有人寫入
sudo chmod -R 777 /path/to/laravel/storage            # 允許所有人寫入
```

---

### 2.2 *權限檢查指令*

```bash
# 檢查目錄權限
# ls -la 是用來列出目錄和檔案詳細資訊
ls -la /path/to/laravel/bootstrap/cache   # 列出 bootstrap/cache 目錄的詳細檔案權限和檔案擁有者，確認權限設定是否正確
ls -la /path/to/laravel/storage           # 列出 storage 目錄的詳細檔案權限和檔案擁有者，確認權限設定是否正確

# 檢查 Web 伺服器使用者
# ps 是用來查詢執行中的程序
# grep 是用來篩選文字
ps aux | grep nginx                       # ps aux 會列出所有執行中的程序，grep nginx 會從中篩選出包含 nginx 的行
ps aux | grep apache                      # ps aux 會列出所有執行中的程序，grep apache 會從中篩選出包含 apache 的行
# 這樣可以快速找到 nginx 或 apache 伺服器的執行狀態和啟動帳號（USER 欄位）
# ps aux 指令的輸出第一欄就是 USER

# aux 是 ps 指令的選項組合，意思如下：
                                    # a：顯示所有使用者的程序（不只自己的）
                                    # u：以使用者導向格式顯示（包含 USER 欄位）
                                    # x：顯示沒有控制終端機的程序

# - `ps aux` 會顯示所有執行中的程序，包含 USER（啟動帳號）、PID（程序編號）、%CPU、%MEM、COMMAND（執行指令）等欄位。
# - `ps aux | grep nginx` 只會顯示包含 nginx 的程序行，可以看到 nginx 相關的執行資訊。
# - `ps aux | grep apache` 只會顯示包含 apache 的程序行，可以看到 apache 相關的執行資訊。
```

---

## 3. **效能優化**（Performance Optimization)

### 3.1 *快取全部設定*

- 部署至**正式環境**時，應使用 Laravel 提供的`快取指令`來提升效能。

```bash
# 快取全部設定（推薦用於生產環境）
php artisan optimize
```

---

### 3.2 *清除快取*

- `清除所有快取`（設定、路由、事件、視圖、預設快取驅動）。

```bash
# 清除所有快取
php artisan optimize:clear
```

---

### 3.3 *個別快取指令*

#### 3.3.1 **快取設定檔**

```bash
# 快取設定檔
php artisan config:cache

# ⚠️ 重要提醒：使用 config:cache 後，.env 不會再被載入
# 要確保 env() 只出現在設定檔中
# 執行 php artisan config:cache 之後，Laravel 只會讀取快取的設定，不會再讀取 .env 檔案。
# 所以 env() 只能用在設定檔（如 config/*.php），不能在程式其他地方直接呼叫 env()，否則會拿不到最新的環境變數。
```

```php
// 先把需要的環境變數寫進設定檔（config），
// 程式裡要用時再透過 config() 來取得，不要直接用 env()，這樣才能確保設定值正確且不受快取影響。

// config/app.php
'debug' => env('APP_DEBUG', false), // 從 .env 讀取 APP_DEBUG，設定到 config

// 不要在控制器、模型等其他地方直接用 env()
// 例如：不要這樣
public function index()
{
    $value = env('APP_DEBUG'); // 這樣在 config:cache 後可能會拿不到值，建議改用 config('app.debug')
}

// 控制器或其他程式
if (config('app.debug')) { // 用 config() 取得 debug 設定，這樣不受 config:cache 影響
    // 這裡可以寫除錯相關的程式
}
```

---

#### 3.3.2 **快取事件與監聽器**

```bash
# 快取事件與監聽器
php artisan event:cache
# 可以將所有事件與監聽器的對應關係快取起來，加速 Laravel 事件系統的載入速度，適合在生產環境使用。
```

---

#### 3.3.3 **快取路由**

```bash
# 快取路由
php artisan route:cache

# 適合路由數量多的大型應用，能有效加速 route 註冊
```

---

#### 3.3.4 **快取視圖**

```bash
# 快取視圖（Blade 編譯結果）
php artisan view:cache

# 預先編譯 Blade 模板，避免每次請求都重新編譯
```

---

## 4. **除錯模式設定**（Debug Mode Configuration)

### 4.1 *除錯模式控制*

- `config/app.php` 中的 `debug` 設定值會依據 `.env` 中的 `APP_DEBUG` 變數控制**錯誤顯示程度**。

```php
// config/app.php
'debug' => env('APP_DEBUG', false),
```

---

### 4.2 *生產環境設定*

- **正式環境請務必設定**：`APP_DEBUG=false`，否則可能導致 __機敏資訊洩漏__ 給使用者。

```bash
# .env 檔案設定
APP_DEBUG=false
APP_ENV=production
APP_LOG=error
```

---

## 5. **健康檢查路由**（Health Check Route)

### 5.1 *內建健康檢查*

- Laravel 內建 `/up` **健康檢查路由**，用於 __服務監控__（如 `uptime` 檢查、`Kubernetes` 健康探針 等）。
- 當你存取 `/up` 路徑時，Laravel 會 __自動檢查__ _應用程式是否正常運作_。
- 如果一切正常，會回傳 `HTTP 200` 狀態碼（代表`服務健康`）。
- 如果有錯誤或異常，則回傳 `HTTP 500` 狀態碼（代表`服務異常`）。
- 這個路由不需要額外設定，**預設**就會啟用，方便自動化監控工具判斷服務狀態。

```php

// Laravel 內建健康檢查路由，不需額外程式碼
// 只要啟用 Laravel 10+，預設就有 /up 路由
// 例如：瀏覽器或監控工具請求 http://your-domain/up
// 狀態正常時回傳 HTTP 200，異常時回傳 HTTP 500
```

---

### 5.2 *自訂健康檢查路徑*

- 也可以**修改此路由**路徑。

```php
// bootstrap/app.php
->withRouting(
    web: __DIR__.'/../routes/web.php',         // 網頁路由
    commands: __DIR__.'/../routes/console.php',// Artisan 指令路由
    health: '/status',                         // 自訂健康檢查 URI（預設是 /up，可改成 /status）
)
```

---

### 5.3 *健康檢查事件*

- Laravel 會在`/up`此路由觸發 `Illuminate\Foundation\Events\DiagnosingHealth` 事件，你可透過**監聽器**檢查`資料庫、快取`等狀況，並在異常時擲出`例外`。

```php
// 建立健康檢查監聽器
`php artisan make:listener HealthCheckListener`

// app/Listeners/HealthCheckListener.php
namespace App\Listeners;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthCheckListener
{
    public function handle(DiagnosingHealth $event): void
    {
        // 檢查資料庫連線，若失敗則拋出例外
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            throw new \Exception('Database connection failed');
        }

        // 檢查快取連線，若失敗則拋出例外
        try {
            Cache::store()->get('health-check');
        } catch (\Exception $e) {
            throw new \Exception('Cache connection failed');
        }
    }
}
```

<!-- 
PDO（PHP Data Objects）是 PHP 的一個資料庫存取抽象層，提供統一的 API 來連接和操作多種資料庫（如 MySQL、SQLite、PostgreSQL）。它支援預備語句（prepared statement）、交易（transaction）等功能，讓程式碼更安全、彈性更高，也能防止 SQL Injection 並簡化多資料庫切換。
-->

<!-- 
「抽象層」這個概念來自軟體工程和設計模式，
指的是把複雜的底層細節包裝起來，
只暴露簡單、統一的操作介面給使用者，
讓開發者不用關心底層實作，
只要操作抽象層提供的方法即可，
這樣可以提升程式的彈性、可維護性和可擴充性。
-->

<!-- 
Laravel 內部一定會用到 PDO，
因為 Laravel 的資料庫連線（Eloquent、Query Builder）都是建立在 PDO 之上，
你不用自己直接寫 PDO 程式碼，
但系統底層會自動用 PDO 來操作資料庫。
 -->

<!--  
PDO 就是一套 API（裡面有多種方法） 可以支援多種資料庫
支援預備語句（防止 SQL Injection）
支援交易（transaction）
支援例外處理（Exception） 
-->

```php
// 建立 PDO 連線
$pdo = new PDO('mysql:host=localhost;dbname=test', 'username', 'password');

// 預備語句
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute(['email' => 'test@example.com']);

// 取得結果
$user = $stmt->fetch(PDO::FETCH_ASSOC);

print_r($user);
```

<!-- 
說明：
new PDO(...)：建立資料庫連線
prepare()：建立預備語句
execute()：執行查詢並綁定參數
fetch()：取得查詢結果
這樣寫可以防止 SQL Injection，也能輕鬆切換不同資料庫。 
-->

---

## 6. **Laravel 官方部署平台**（Official Deployment Platforms)

### 6.1 *Laravel Cloud*

- Laravel 官方的`全託管部署平台`，支援 _自動擴展_，可 **部署** `Laravel 應用、資料庫、快取、物件儲存`等。
- **適合**：想專注寫 Laravel，不想煩惱伺服器細節的開發者。

```bash
# 使用 Laravel Cloud 部署
# 1. 註冊 Laravel Cloud 帳號
# 2. 連接 GitHub/GitLab 專案
# 3. 選擇部署環境
# 4. 自動部署和擴展
```

---

### 6.2 *Laravel Forge*

- Laravel 為 `VPS` 設計的`伺服器管理工具`，適合希望自己控管主機但不想自己安裝一堆服務的團隊。
- `VPS`（Virtual Private Server，_虛擬私人伺服器_）是指**在一台實體伺服器上分割出多個獨立的虛擬主機**。
－ 每個 VPS 都有自己**的作業系統、資源和管理權限**，可以像獨立主機一樣安裝和運行應用程式，常用於網站、API、專案部署等。

- 像 `AWS、DigitalOcean、Linode` 都可以租 VPS，你可以在一台主機上開多個虛擬伺服器，再用`負載平衡`（Load Balancer）分散流量，提升網站效能和穩定性。

- **可用於**：_DigitalOcean、AWS、Linode_ 等主機。
- **功能**：安裝並管理 _Nginx、MySQL、Redis、Memcached、Beanstalk_ 等工具。

```bash
# 使用 Laravel Forge 部署
# 1. 註冊 Forge 帳號
# 2. 連接 VPS 主機
# 3. 選擇要安裝的服務
# 4. 自動配置伺服器環境
```

---

## 7. **部署檢查清單**（Deployment Checklist)

### 7.1 *伺服器環境檢查*

- ✅ **確認**：PHP `版本`符合 Laravel 要求
- ✅ **確認**：必要的 PHP `擴展`已安裝
- ✅ **確認**：`Web 伺服器`（Nginx/Apache）已正確配置
- ✅ **確認**：`資料庫`連線正常
- ✅ **確認**：`目錄權限`設定正確

- `Proxy`（如 Nginx）負責 _負載平衡_，把請求分配給多台 `Web Server`，
- `Web Server` 可以直接 _回傳靜態檔案_，或把 _動態請求交給 PHP 後端_ 處理。
- 這樣可以提升效能、擴充性和安全性。

- `Nginx/Apache` 可以**同時扮演** `Proxy` 和 `Web Server`，
在大型架構裡，`Proxy` 通常負責 _負載平衡_ 和 _流量分配_，`Web Server` 處理 _靜態檔案_ 或 _轉給後端_（如 PHP）。
---

### 7.2 *應用程式設定檢查*

- ✅ **確認**：`.env` 檔案已正確設定
- ✅ **確認**：`APP_DEBUG=false`（生產環境）
- ✅ **確認**：`APP_ENV=production`
- ✅ **確認**：`資料庫`連線資訊正確
- ✅ **確認**：`快取驅動`設定正確

---

### 7.3 *效能優化檢查*

- ✅ **確認**：已執行 `php artisan optimize`
- ✅ **確認**：已執行 `php artisan config:cache`
- ✅ **確認**：已執行 `php artisan route:cache`
- ✅ **確認**：已執行 `php artisan view:cache`
- ✅ **確認**：已執行 `php artisan event:cache`

---

## 8. **常見部署問題**（Common Deployment Issues)

### 8.1 *權限問題*

```bash
# 問題：storage 目錄無法寫入
# 解決：檢查目錄權限和擁有者
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage

# 問題：bootstrap/cache 目錄無法寫入
# 解決：檢查目錄權限和擁有者
sudo chown -R www-data:www-data bootstrap/cache
sudo chmod -R 775 bootstrap/cache
```

---

### 8.2 *快取問題*

```bash
# 問題：設定變更後未生效
# 解決：清除配置快取
php artisan config:clear

# 問題：路由變更後未生效
# 解決：清除路由快取
php artisan route:clear

# 問題：視圖變更後未生效
# 解決：清除視圖快取
php artisan view:clear
```

---

### 8.3 *連線問題*

```bash
# 問題：資料庫連線失敗
# 解決：檢查 .env 中的資料庫設定
# 解決：確認資料庫服務是否運行

# 問題：快取連線失敗
# 解決：檢查快取驅動設定
# 解決：確認 Redis/Memcached 服務是否運行
```

---

## 9. **監控與維護**（Monitoring & Maintenance)

### 9.1 *日誌監控*

```bash
# tail -f 是 Linux 指令，用來即時顯示檔案最後幾行內容並持續更新
# tail 預設會顯示檔案最後 10 行內容
# -f 參數代表「持續追蹤」
# tail -f 不只顯示當下內容，還會持續監控檔案，只要有新日誌寫入，就會即時顯示在終端機，這樣你不用一直重複執行指令，就能看到最新訊息。

# 查看應用程式日誌
tail -f storage/logs/laravel.log

# 查看錯誤日誌（所有 laravel 開頭的日誌檔案）
tail -f storage/logs/laravel-*.log

# 查看 Nginx 錯誤日誌
tail -f /var/log/nginx/error.log
```

---

### 9.2 *效能監控*

```bash

# -t 是「test」的意思，用來測試設定檔是否正確（如 nginx -t、php-fpm8.2 -t）。
# -h 是「human readable」的意思，讓輸出結果更容易閱讀（如 df -h、free -h）。

# 檢查 PHP-FPM 配置是否正確
php-fpm8.2 -t

# 檢查 Nginx 配置是否正確
nginx -t

# 檢查系統資源使用狀況
htop      # 互動式查看 CPU、記憶體等使用情況
df -h     # 查看磁碟空間使用情況
free -h   # 查看記憶體使用情況
```

---

### 9.3 *定期維護*

```bash
# 定期清理日誌檔案
php artisan log:clear           # 清除 Laravel 應用程式的日誌檔案

# 定期清理快取
php artisan cache:clear         # 清除所有快取資料

# 定期更新依賴套件
composer update --no-dev --optimize-autoloader
# --no-dev：只安裝正式環境套件，不安裝 dev 套件
# --optimize-autoloader：優化自動載入效能，加快專案執行速度
```

---

## 10. **安全建議**（Security Recommendations)

### 10.1 *伺服器安全*

- 🔒 **重要**：`定期更新`作業系統和軟體套件
- 🔒 **重要**：設定`防火牆規則`，只開放必要端口
- 🔒 **重要**：使用 `SSH 金鑰認證`，停用密碼登入
- 🔒 **重要**：定期`備份`資料庫和檔案

---

### 10.2 *應用程式安全*

- 🔒 **重要**：確保 `APP_DEBUG=false` 在生產環境
- 🔒 **重要**：使用 `HTTPS` 加密傳輸
- 🔒 **重要**：`定期更新` Laravel 和依賴套件
- 🔒 **重要**：監控應用程式`日誌`，及時發現異常

---

### 10.3 *資料安全*

- 🔒 **重要**：`定期備份`資料庫
- 🔒 **重要**：`加密`敏感資料
- 🔒 **重要**：`限制`資料庫存取權限
- 🔒 **重要**：`監控`資料庫連線和查詢 