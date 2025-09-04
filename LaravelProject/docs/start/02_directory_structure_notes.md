# *Laravel 目錄結構 筆記*

---

## 1. **根目錄**（The Root Directory)

- Laravel 專案的根目錄包含了所有應用程式的`核心結構`與`設定檔`。

---

## 2. **核心目錄結構**（Core Directory Structure)

### 2.1 *app 目錄*（The App Directory)

- `app` 目錄包含應用程式的*核心程式碼*。幾乎所有的類別都會定義在這裡。

```bash
app/
├── Console/          # Artisan 指令
├── Events/           # 事件類別
├── Exceptions/       # 例外類別
├── Http/             # HTTP 相關類別
├── Jobs/             # 佇列任務
├── Listeners/        # 事件監聽器
├── Mail/             # 郵件類別
├── Models/           # Eloquent 模型
├── Notifications/    # 通知類別
├── Policies/         # 授權策略
├── Providers/        # 服務提供者
└── Rules/            # 驗證規則
```

---

### 2.2 *bootstrap 目錄*（The Bootstrap Directory)

- `bootstrap` 目錄包含用來**啟動**框架的 `app.php` 檔案。
- 這個目錄中也有一個 `cache` 子目錄，裡面儲存**由框架自動產生、用於提升效能的快取檔案**，例如 _路由快取_ 與 _服務快取_ 等。

```bash
bootstrap/
├── app.php           # 框架啟動檔案
└── cache/            # 框架快取檔案
    ├── routes.php    # 路由快取
    ├── services.php  # 服務快取
    └── packages.php  # 套件快取
```

---

### 2.3 *config 目錄*（The Config Directory)

- `config` 目錄包含應用程式所有的**設定檔**。

```bash
config/
├── app.php           # 應用程式基本設定
├── database.php      # 資料庫設定
├── cache.php         # 快取設定
├── session.php       # 會話設定
├── mail.php          # 郵件設定
├── queue.php         # 佇列設定
└── ...
```

---

### 2.4 *database 目錄*（The Database Directory)

- `database` 目錄包含資料庫的**遷移檔**（migrations）、**模型工廠**（factories）和**種子檔**（seeds）。

```bash
database/
├── factories/        # 模型工廠
├── migrations/       # 資料庫遷移檔
├── seeders/          # 資料庫種子檔
└── ...
```

---

### 2.5 *public 目錄*（The Public Directory)

- `public` 目錄包含 `index.php` 檔案，這是**所有進入應用程式請求的入口點**，同時也設定了 _自動載入機制_ 。
- 這個目錄還包含**靜態資源**，例如圖片、JavaScript 和 CSS 檔案。

```bash
public/
├── index.php         # 應用程式入口點
├── css/              # CSS 檔案
├── js/               # JavaScript 檔案
├── images/           # 圖片檔案
├── storage/          # 公開儲存連結
└── ...
```

---

### 2.6 *resources 目錄*（The Resources Directory)

- `resources` 目錄包含應用程式的**視圖**（views），以及 _尚未編譯的原始資源_ ，如 __SCSS、JavaScript__ 等前端資源。

```bash
resources/
├── css/              # 原始 CSS/SCSS 檔案
├── js/               # 原始 JavaScript 檔案
├── views/            # Blade 視圖模板
├── lang/             # 語言檔案
└── ...
```

---

### 2.7 *routes 目錄*（The Routes Directory)

- `routes` 目錄包含應用程式所有的**路由定義**。Laravel 預設包含兩個路由檔案：`web.php` 與 `console.php`。

---

#### 2.7.1 **web.php 檔案**

- 包含被 Laravel 分類在 `web` middleware 群組中的路由，這些路由會啟用 _sesion 狀態、CSRF 保護與 Cookie 加密_。
- 如果應用程式不是提供**無狀態**（stateless）的 **RESTful API**（__純API__），那麼大部分的路由應該都會寫在 `web.php` 中。

```php
// routes/web.php
Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

<!-- 
RESTful API 被稱為無狀態（stateless），
是因為每次請求都必須自帶所有必要資訊（如認證 token），
伺服器不會記住前一次請求的狀態或資料，
每次請求都是獨立的。

一般 Web request（像瀏覽器登入、購物車等）通常是有狀態，
因為會用 session、cookie 等方式在伺服器端記住使用者的登入狀態或操作紀錄，
所以 Web 路由（web.php）會啟用 session、CSRF 等功能。 
-->

<!-- 
一般的 request（像網頁表單、瀏覽器操作）通常是發送到你自己的後端伺服器，
RESTful API 可以是你自己提供，也可以是第三方服務提供，
重點是 RESTful API 通常用於前後端分離或跨系統溝通，
而且設計上是無狀態的。 
-->

<!-- 
不管是 Web 還是 API，
後端都會去資料庫取資料。

差別在於：

Web（有狀態）：伺服器會記住使用者狀態（如 session），每次請求可以根據 session 做不同處理。
API（無狀態）：每次請求都要自帶所有必要資訊（如 token），伺服器不記住任何狀態。
資料庫存取本質一樣，差別只在「狀態管理」和「認證方式」。 
-->

<!-- 
這樣的差異是為了設計彈性、擴充性和安全性：

Web（有狀態）方便管理使用者登入、購物車等互動功能。
API（無狀態）方便前後端分離、跨平台、第三方整合，
每次請求獨立，安全性高，容易擴充和維護。
這樣可以根據不同需求選擇最適合的架構。 
-->

<!-- 
前後分離時，通常不會用傳統 session，
而是用 token（如 JWT）來管理使用者身分，
前端儲存 token，每次 API 請求都帶上，
後端根據 token 驗證使用者，
這樣就能實現登入、購物車等互動功能，
不需要依賴 session。 
-->

<!-- 
在前後端分離（純 API）架構下，
通常不需要 CSRF 防護，因為 API 請求不依賴瀏覽器 cookie/session，
也不會有 session fixation 問題，
因為身分驗證是靠 token（如 JWT），
不是靠 session。
這些防護主要是針對傳統 Web（有 session/cookie）才需要。 
-->

<!-- 
JWT（JSON Web Token）是一種用來在網路上安全傳遞資訊的身分驗證令牌。
它是一個加密的字串，通常包含使用者資訊和簽章，
前端儲存 JWT，每次 API 請求都帶上，
後端根據 JWT 驗證使用者身分，
常用於前後端分離、RESTful API 的認證機制。 
-->

<!-- 
RESTful API 的 JWT 驗證機制通常會寫在middleware或認證套件裡，
不是直接寫在每個 API 程式碼內，
所以你在 controller 或 route 裡看不到，
但實際上請求進來時，middleware 會自動驗證 JWT，
確保安全性和身分驗證。 
-->

---

#### 2.7.2 **console.php 檔案**

- 可以定義所有 _基於 `閉包（closure）` 的 Artisan 指令_。
- 每個 **closure** 都會綁定到一個 **command 實體**，這樣可以更簡單地與指令的輸入／輸出方法互動。
- *儘管這個檔案不定義 HTTP 路由*，但它定義了 __進入應用程式的「`主控台入口（console routes）`」__。可以在這裡 __排定定時任務__。

```php
// routes/console.php
Artisan::command('inspire', function () {
    // 定義一個名為 inspire 的 Artisan 指令
    $this->comment(Inspiring::quote());
    // 輸出一則勵志語錄到終端機
})->purpose('Display an inspiring quote');
// 指令用途說明：顯示一則勵志語錄
```

---

#### 2.7.3 **api.php 檔案**

- 用來定義*無狀態的 API 路由*，這些進入應用程式的請求`通常使用 Token 驗證，且不會存取 session 狀態`。

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::post('/posts', [PostController::class, 'store']);
});
```
---

#### 2.7.4 **channels.php 檔案**

- 用來`註冊`應用程式所支援的*所有事件廣播頻道*。

```php
// routes/channels.php
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

---

### 2.8 *storage 目錄*（The Storage Directory)

- `storage` 目錄包含應用程式的 **日誌（logs）**、
                            **已編譯的 Blade 模板**、
                            **基於檔案的 session**、
                            **檔案快取**
                            以及其他由 Laravel 框架產生的檔案。

```bash
storage/
├── app/              # 應用程式產生的檔案
│   └── public/       # 公開存取的檔案
├── framework/        # 框架產生的檔案與快取
│   ├── cache/        # 檔案快取
│   ├── sessions/     # 會話檔案
│   └── views/        # 編譯後的 Blade 模板
└── logs/             # 應用程式日誌
```

---

#### 2.8.1 **storage/app/public**

- 這個目錄可以用來儲存像是 *使用者頭像* 等，應 _公開存取的檔案_ 。

- 在 `public/storage` 建立一個**符號連結（symlink）**指向 `storage/app/public` 的捷徑，
  - 這樣瀏覽器存取 `public/storage/xxx.jpg` 時，實際會讀取 `storage/app/public/xxx.jpg` 檔案，
  - 讓儲存的檔案 _可以公開存取但又不直接暴露原始目錄_ 。

- 可使用以下指令 __建立連結__：

```bash
# 建立公開儲存連結
php artisan storage:link
```

---

### 2.9 *tests 目錄*（The Tests Directory)

- `tests` 目錄包含**自動化測試檔案**。Laravel 預設提供使用 `Pest` 或 `PHPUnit` 撰寫的 __單元測試__ 與 __功能測試__ （feature tests）。

```bash
tests/
├── Feature/          # 功能測試
├── Unit/             # 單元測試
└── ...
```

- 每個**測試類別**的命名應以 `Test` 為結尾，例如：`UserTest.php`

---

#### 2.9.1 **執行測試**

```bash
# 使用 PHPUnit
./vendor/bin/phpunit

# 使用 Pest
./vendor/bin/pest

# 使用 Laravel 提供的美觀格式輸出指令
php artisan test
```

---

### 2.10 *vendor 目錄*（The Vendor Directory)

- `vendor` 目錄是由 `Composer` 管理的，裡面包含 __所有第三方套件__ 與 __依賴程式碼（dependencies）__ 。
- __依賴程式碼__ 是指：
  - 你的專案需要用到的 __其他套件、函式庫或工具__ 的程式碼，_這些不是你自己寫的，而是由第三方提供_，例如 `Laravel、Guzzle、Carbon` 等，都會被 `Composer` 安裝到 `vendor` 目錄裡。

---

## 3. **app 目錄詳細說明**（The App Directory - Extended)

### 3.1 *基本概念*

- 大部分應用程式的程式碼都位於 `app` 目錄下。
- 預設情況下，這個目錄使用 `App` 命名空間，並透過 `Composer` 的 `PSR-4 autoloading 標準`自動載入。

---

### 3.2 *預設子目錄*

```bash
app/
├── Http/             # 包含 controller、middleware、request 等
├── Models/           # 資料模型
└── Providers/        # 服務提供者
```

---

### 3.3 *動態建立目錄*

- 當使用 Artisan 的 `make` 系列指令產生類別時，會逐漸增加其他子目錄。
- 例如：執行 `php artisan make:command` 會自動建立 `app/Console` 目錄（如果尚未存在）

---

## 4. **Console 與 Http 的關係**（Console vs Http Relationship)

### 4.1 *概念理解*

可以這樣理解 `Console` 與 `Http`：

它們都作為「**與`核心應用`互動的界面**」

- **Http** 代表透過`瀏覽器 / API 請求`（HTTP 協議）進入應用
- **Console** 則代表透過`指令列介面 CLI`（Artisan 指令）進入應用

---

### 4.2 *職責分工*

- **Console 目錄** → 包含所有 `Artisan` 指令
- **Http 目錄** → 包含 `controllers、middleware、form requests` 等

---

### 4.3 *共同特點*

- 兩者本身 __不包含業務邏輯__ ，而是讓使用者可以以**不同方式**「__向你的應用下達命令__」。

---

## 5. **app 目錄子目錄詳解**（App Directory Subdirectories)

### 5.1 *Broadcasting 目錄*

- **Broadcasting** 目錄包含應用程式中所有的`廣播頻道`類別。
- 這些類別是使用 `make:channel` 指令產生的。
- 此目錄預設不存在，會在建立第一個 channel 時自動產生。

```bash
# 建立廣播頻道
php artisan make:channel OrderChannel
```

---

### 5.2 *Console 目錄*

- **Console** 目錄包含應用程式中所有`自定義的 Artisan 指令`。
- 這些指令可透過 `make:command` 指令產生。

```bash
# 建立自定義指令
php artisan make:command SendReminders
```

---

### 5.3 *Events 目錄*

- 這個目錄預設不存在，但在執行 `event:generate` 或 `make:event` 指令後會自動建立。
- **Events** 目錄存放`事件類別`（event classes）。
- 事件可用於 __通知應用中的其他部分某個動作已發生__ ，讓應用更具彈性與低耦合。

```bash
# 建立事件
php artisan make:event UserRegistered
```

---

### 5.4 *Exceptions 目錄*

- **Exceptions** 目錄包含應用程式中所有的`自定義例外類別`。
- 這些類別可以透過 `make:exception` 指令產生。

```bash
# 建立自定義例外
php artisan make:exception CustomException
```

---

### 5.5 *Http 目錄*

- **Http** 目錄包含 `controllers（控制器）、middleware（中介層）和 form requests（表單請求驗證）`。
- 幾乎所有處理`進入請求`的邏輯都會放在這裡。

```bash
app/Http/
├── Controllers/      # 控制器
├── Middleware/       # 中介層
├── Requests/         # 表單請求驗證
└── ...
```

---

### 5.6 *Jobs 目錄*

- 這個目錄預設不存在，當執行 `make:job` 指令時會自動建立。
- **Jobs** 目錄用來存放`可排入佇列的任務`（queueable jobs）。
- 這些任務可以 __非同步執行__ ，也可以在請求過程中 __同步執行__ 。

- **指令**（commands） 是 *命令模式*（Command Pattern）的一種實作，指的是把`一個動作封裝成物件，方便呼叫和管理。`
- 不論是 __同步任務__（立即執行並回傳結果）或 __非同步任務__（如 `queue job`，排入佇列等待背景處理），都可以被稱為「指令」，只是在執行時機和方式上有所不同。

```bash
# 建立佇列任務
php artisan make:job ProcessPodcast
```

---

### 5.7 *Listeners 目錄*

- 這個目錄預設不存在，但在執行 `event:generate` 或 `make:listener` 指令時會自動建立。
- **Listeners** 目錄包含所有處理事件的*事件監聽器類別*（listeners）。
- __每個`監聽器`會接收一個`事件實體`，並根據事件進行對應處理__。
- 例如，`UserRegistered` _事件_ 可以由 `SendWelcomeEmail` _監聽器_ 來處理。

```bash
# 建立事件監聽器
php artisan make:listener SendWelcomeEmail
```

---

### 5.8 *Mail 目錄*

- 這個目錄預設不存在，但在執行 `make:mail` 指令時會自動建立。
- **Mail** 目錄包含所有代表應用程式發送的*郵件類別*（mailables）。
- 這些類別`可封裝寄送電子郵件的所有邏輯`，並透過 `Mail::send` 方法發送。

```bash
# 建立郵件類別
php artisan make:mail WelcomeMail
```

---

### 5.9 *Models 目錄*

- **Models** 目錄包含所有 Eloquent 資料*模型類別*。
- Laravel 的 Eloquent ORM 提供簡潔優雅的 `Active Record` 風格，可輕鬆與資料表互動。
- __每個資料表__ 會對應 __一個模型類別__，可用來查詢與插入資料。

```bash
# 建立模型
php artisan make:model User
```

<!-- 
Active Record 是一種物件關聯對應（ORM）設計模式，
意思是「每個資料表對應一個類別，每個資料列對應一個物件」，
物件本身就能直接查詢、儲存、更新、刪除資料，
常見於 Laravel 的 Eloquent、Ruby on Rails 等框架。 
-->

---

### 5.10 *Notifications 目錄*

- 這個目錄預設不存在，會在執行 `make:notification` 指令時建立。
- **Notifications** 目錄包含所有應用程式中使用的*即時通知類別*，例如事件發生時的提示訊息。
- Laravel 的通知系統可透過 `email、Slack、簡訊`或儲存到資料庫等方式傳送訊息。

```bash
# 建立通知類別
php artisan make:notification InvoicePaid
```

---

### 5.11 *Policies 目錄*

- 這個目錄預設不存在，會在執行 `make:policy` 指令時自動建立。
- **Policies** 目錄包含應用程式的*授權策略類別*。
- 這些策略用來判斷使用者`是否有權執行某項資源操作`。

```bash
# 建立授權策略
php artisan make:policy PostPolicy
```

---

### 5.12 *Providers 目錄*

- **Providers** 目錄包含應用程式所有的*服務提供者*（Service Providers）。
- 這些類別用來`綁定服務、註冊事件、初始化應用所需設定`，是 _應用啟動_ 的關鍵組件。
- 在新的 Laravel 專案中，這個目錄預設已包含 `AppServiceProvider`。
- 也可以根據需求新增自己的服務提供者。

```bash
# 建立服務提供者
php artisan make:provider CustomServiceProvider
```

---

### 5.13 *Rules 目錄*

- 這個目錄預設不存在，會在執行 `make:rule` 指令時建立。
- **Rules** 目錄用來存放*自定義的驗證規則類別*（`validation rules`），可將複雜的驗證邏輯封裝在一個簡單的物件中。

```bash
# 建立自定義驗證規則
php artisan make:rule Uppercase
```

---

## 6. **目錄結構最佳實踐**（Directory Structure Best Practices)

### 6.1 *命名規範*

- ✅ **建議**：使用 `PascalCase` 命名目錄和檔案（每個單的首字母都大寫且不使用分隔符號）
- ✅ **建議**：_目錄_ 名稱使用 __複數形式__（如 Controllers、Models）
- ✅ **建議**：_檔案_ 名稱使用 __單數形式__（如 UserController、UserModel）
- ❌ **避免**：使用特殊字元或空格命名

---

### 6.2 *組織原則*

- ✅ **建議**：_相關功能_ 放在同一目錄下
- ✅ **建議**：保持 _目錄結構_ 的邏輯性和一致性
- ❌ **避免**：過深的 _目錄巢狀結構_
- ❌ **避免**：將 __不相關的__ 類別放在同一目錄

---

### 6.3 *擴展性考慮*

- ✅ **建議**：_預留_ 擴展空間，避免過度設計
- ✅ **建議**：根據專案規模調整目錄結構
- ✅ **建議**：保持目錄結構的靈活性
- ❌ **避免**：過早優化或過度抽象化
  - *過早優化* 是指`在程式還沒遇到效能瓶頸前就花太多心力做優化`
  - *過度抽象化* 是指`把程式設計得太複雜、太多層結構，導致難以維護或理解`

---

## 7. **常見目錄結構模式**（Common Directory Structure Patterns)

### 7.1 *小型專案結構*

```bash
app/
├── Http/
│   ├── Controllers/
│   └── Middleware/
├── Models/
└── Providers/
```

---

### 7.2 *中型專案結構*

```bash
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Services/          # 業務邏輯服務
├── Repositories/      # 資料存取層
└── Providers/
```

---

### 7.3 *大型專案結構*

```bash
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/     # API 資源
├── Models/
├── Services/
├── Repositories/
├── DTOs/              # 資料傳輸物件
├── Enums/             # 列舉類別
└── Providers/
```

---

## 8. **相關指令參考**（Related Commands)

| 指令                              | 說明               | 建立目錄               |
|-----------------------------------|--------------------|----------------------|
| `php artisan make:controller`     | 建立 *控制器*      | app/Http/Controllers   |
| `php artisan make:model`          | 建立 *模型*        | app/Models             |
| `php artisan make:middleware`     | 建立 *中介層*      | app/Http/Middleware    |
| `php artisan make:request`        | 建立 *表單請求*    | app/Http/Requests      |
| `php artisan make:service`        | 建立 *服務類別*    | app/Services           |
| `php artisan make:repository`     | 建立 *儲存庫*      | app/Repositories       |
| `php artisan make:command`        | 建立 *Artisan 指令*| app/Console            |
| `php artisan make:event`          | 建立 *事件*        | app/Events             |
| `php artisan make:listener`       | 建立 *監聽器*      | app/Listeners          |
| `php artisan make:mail`           | 建立 *郵件類別*     | app/Mail               |
| `php artisan make:notification`   | 建立 *通知*        | app/Notifications      |
| `php artisan make:policy`         | 建立 *授權策略*     | app/Policies           |
| `php artisan make:provider`       | 建立 *服務提供者*   | app/Providers          |
| `php artisan make:rule`           | 建立 *驗證規則*     | app/Rules              |