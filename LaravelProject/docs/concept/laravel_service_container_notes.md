# Laravel Service Container 筆記

## 1. *簡介*（Introduction）

Service Container（服務容器）是 Laravel 管理**類別依賴**與**依賴注入**的核心工具。依賴注入即「**將依賴物件注入類別建構子或 setter**」。

> *服務容器* 就是 Laravel 的「自動物件工廠」或「倉庫」，*你只要宣告需要什麼，容器會自動幫你組裝好所有依賴並交給你*。
> 技術上，服務容器是 `Illuminate\Container\Container` 這個類別的實例，負責 *綁定（註冊）*、*解析（生成）*、*依賴注入*、*單例管理* 等功能。
> 這讓你的程式碼更乾淨、可測試、易維護。
>
> 你可以把服務容器想像成一個「自動幫你準備工具的櫃子」。你只要說「我要一把螺絲起子（某個物件）」，它不只會給你螺絲起子，還會自動幫你把相關的零件（依賴）都準備好，組裝好再交給你。你不用自己一層層 new 物件，全部交給服務容器自動處理。

> 依賴注入常見有三種方式：  
> 1. **建構子注入**：依賴物件透過建構子參數傳入，Laravel 最常用、最推薦。  
> 2. **方法注入**：依賴物件透過方法參數傳入，常見於 Controller action。  
> 3. **屬性注入**：依賴物件直接指定到類別屬性（PHP 8.1+ 支援，Laravel 原生較少用）。  
> Laravel Service Container 主要支援前兩種方式。

---

## 2. *自動解析與依賴注入*（Zero Configuration Resolution）

若類別僅依賴 *具體類別（非 interface）* ，容器可自動解析：

> 只要你的類別建構子裡面「直接寫需要什麼類別」，Laravel 服務容器就會自動幫你 new 好、組裝好，不用自己手動 new，也不用特別設定。你只要「要什麼，Laravel 就給你什麼」。

```php
// 實作位置：任何類別中，Laravel 會自動注入
class UserService { // 宣告一個服務類別
    public function __construct(Mailer $mailer) { // 白話：我需要 Mailer，請幫我準備好
        $this->mailer = $mailer; // 白話：Laravel 會自動把 Mailer 塞進來
    }
}
```

常見於 controller、event listener、middleware、job 等，無需手動綁定。

---

## 3. *何時需要手動操作容器*（When to Utilize the Container）

- 需將 **interface** 綁定到實作時
- 開發 **package** 需註冊服務時
> 大多數時候 Laravel 都會自動幫你注入依賴，你不用自己動手。但有些特殊情況，像是在 **普通 function、helper、或閉包裡** ，Laravel 不會自動幫你注入，這時你就要用 `app()`、`resolve()`、`make()` 這些方法手動跟容器要東西。
>
> 什麼時候要手動操作容器？
> 1. 在「Laravel 不會自動注入」的地方（如普通 function、閉包）需要物件時。
> 2. 你需要動態決定要哪個服務時。
> 3. 你要從容器裡拿出已經註冊的單例或服務時。
>
> 例子：
> ```php
> // 實作位置：路由閉包中
> Route::get('/test', function () {
>     $mailer = app(Mailer::class); // 手動跟容器要 Mailer
>     $mailer->send(...);
> });
> ```
> 小結：大部分情況不用手動操作容器，只有在 Laravel 不會自動注入的地方才需要。

---

## 4. *Binding 綁定*（Binding）

> **Binding（綁定）** 就是「*把一個名稱（key）和一個物件生成方式（value）註冊到服務容器裡*」。
> 白話來說，就是你跟服務容器說：「以後只要有人要這個東西（key），你就用這個方法（value）幫我生出來。」
> 這個「*key」可以是類別名稱、介面名稱、字串等*；*「value」可以是類別、閉包、物件實例*。
> 綁定後，當你用 `app()`、`resolve()`、`make()` 跟容器要這個東西時，容器就會照你綁定的方式幫你生出來。
>
> 例子：
> ```php
> // 實作位置：Service Provider 的 register() 方法
> // 綁定一個 key 到一個類別
> app()->bind('foo', FooService::class);
>
> // 綁定一個 key 到一個閉包（自訂生成邏輯）
> app()->bind('bar', function() {
>     return new BarService('參數');
> });
> ```

### 4.1 **基本綁定**

> 基本綁定（bind）是最常見的服務容器綁定方式。*每次有人跟容器要這個服務時，容器都會「重新執行一次你提供的閉包」，產生一個全新的物件*。適合用在「每次都要新的實例」的情境，例如：每次都要一個新的資料處理器。
> 差異：與 singleton 不同，bind 每次都 new 新的，singleton 只 new 一次。

**綁定程式碼通常寫在 Service Provider 的 register() 方法裡**，但這些方法都是 Service Container 提供的：

```php
// 實作位置：Service Provider 的 register() 方法
// 檔案：app/Providers/DemoServiceProvider.php
class DemoServiceProvider extends ServiceProvider
{
    public function register()
    {
        // $this->app 就是 Service Container 實例
        $this->app->bind(Transistor::class, function (Application $app) {
            return new Transistor($app->make(PodcastParser::class));
        });
    }
}
```

**為什麼寫在 Service Provider 裡？**
- Service Provider 是 Laravel 啟動時會自動執行的類別
- 在 register() 方法裡，$this->app 就是 Service Container 實例
- 這樣可以 *集中管理* 所有服務的註冊邏輯

**你也可以在其他地方手動綁定：**
```php
// 實作位置：bootstrap/app.php 或其他地方
app()->bind(Transistor::class, function () {
    return new Transistor();
});
```

於 service provider 內用 `$this->app->bind`：

```php
// 實作位置：Service Provider 的 register() 方法
use App\Services\Transistor; // 告訴 PHP：等等會用到 Transistor 這個類別
// Transistor 只是範例用的服務名稱，可以想像成「一個負責處理 Podcast 上傳、管理的服務」
use App\Services\PodcastParser; // 告訴 PHP：等等會用到 PodcastParser 這個類別
// PodcastParser 也是範例用的服務名稱，可以想像成「一個負責解析 Podcast 資料的工具」
use Illuminate\Contracts\Foundation\Application; // 告訴 PHP：等等會用到 Application 這個介面

// 在服務提供者裡註冊一個「綁定」：以後有人要 Transistor，就照這個方法生一個新的
$this->app->bind(Transistor::class, function (Application $app) { 
    // 這裡就是「怎麼生出一個 Transistor」的做法
    // 先請容器幫我生一個 PodcastParser，然後 new 一個 Transistor，把 PodcastParser 塞進去
    return new Transistor($app->make(PodcastParser::class)); 
});
```

也可用 **Facade**：

```php
// 實作位置：Service Provider 的 register() 方法
use Illuminate\Support\Facades\App; // 告訴 PHP：等等會用到 App 這個 Laravel 的 Facade

// 也可以用 App 這個 Facade 來綁定，效果一樣
App::bind(Transistor::class, function (Application $app) { 
    // 這裡可以寫怎麼生 Transistor
    // ...
});
```

只在**未綁定時註冊**：

```php
// 實作位置：Service Provider 的 register() 方法
// 只有「還沒綁定過」這個服務時，才會執行這個綁定
$this->app->bindIf(Transistor::class, function (Application $app) { 
    // 這裡可以寫怎麼生 Transistor
    // ...
});
```

**可省略型別**，讓 Laravel 由 closure 回傳型別自動推斷：

```php
// 實作位置：Service Provider 的 register() 方法
// 這種寫法不用指定 key，Laravel 會自動根據回傳型別（Transistor）來綁定
// 這種寫法不用自己指定 key，Laravel 會自動根據 closure 的回傳型別（這裡是 Transistor）來當作 key。
// 也就是說，這行等同於 App::bind(Transistor::class, ...)，只是 key 省略了，Laravel 會自動幫你補上。
// 這種寫法比較少見，主要是讓程式碼更簡潔。
App::bind(function (Application $app): Transistor { 
    // 這裡可以寫怎麼生 Transistor
    // ...
});
```

### 4.2 **Singleton 綁定**

> singleton 綁定 *只會產生一個物件實例，所有人拿到的都是同一份* 。適合用在「全程只要一份」的服務，例如：*設定管理、連線池、全域快取* 等。
> 差異：bind 每次都 new，singleton 只 new 一次。

**Singleton 的生命週期：**
- **整個應用程式生命週期**：從 Laravel 啟動到結束
- **跨請求共享**：所有 HTTP 請求都使用同一個實例
- **跨任務共享**：所有 Queue 任務都使用同一個實例

**重要說明：生命週期依賴於 Laravel 架構**

```php
// 實作位置：Service Provider 的 register() 方法
// 檔案：app/Providers/AppServiceProvider.php

// 註冊單例，整個應用只會有一個 Transistor 實例
$this->app->singleton(Transistor::class, function (Application $app) { 
    // 在傳統 Laravel 中：每個請求都是新的 Application 實例，所以每個請求都會有新的 Transistor
    // 在 Laravel Octane 中：Application 實例長駐，所以所有請求共享同一個 Transistor
    return new Transistor($app->make(PodcastParser::class));
});

// 僅當尚未綁定 Transistor 時才註冊單例
$this->app->singletonIf(Transistor::class, function (Application $app) { 
    // 避免重複綁定，適合在條件性註冊時使用
    return new Transistor($app->make(PodcastParser::class));
});
```

**傳統 Laravel vs Laravel Octane 的差異：**

```php
// 實作位置：Service Provider 的 register() 方法
// 傳統 Laravel 架構（每個請求新實例）
$this->app->singleton(UserSession::class, function ($app) {
    return new UserSession();
});

// 請求 A
$app1 = new Application(); // 新的 Application 實例
$session1 = $app1->make(UserSession::class); // 新的 UserSession 實例
$session1->setUserId(123);

// 請求 A 結束，$app1 被銷毀

// 請求 B
$app2 = new Application(); // 全新的 Application 實例
$session2 = $app2->make(UserSession::class); // 全新的 UserSession 實例
$session2->setUserId(456); // 不會受到請求 A 影響
```

```php
// 實作位置：Service Provider 的 register() 方法
// Laravel Octane 架構（長駐實例）
$this->app->singleton(UserSession::class, function ($app) {
    return new UserSession();
});

// 應用程式啟動
$app = new Application(); // 長駐的 Application 實例

// 請求 A
$session1 = $app->make(UserSession::class); // 新的 UserSession 實例
$session1->setUserId(123);

// 請求 A 結束，但 $app 和 $session1 仍然存在

// 請求 B
$session2 = $app->make(UserSession::class); // 同一個 UserSession 實例！
$session2->setUserId(456); // 會覆蓋請求 A 的資料
echo $session1->getUserId(); // 輸出：456（被請求 B 影響了！）
```

**適用場景：**

```php
// 實作位置：Service Provider 的 register() 方法
// 適合 Singleton 的服務（無狀態、全域共享）
$this->app->singleton(ConfigManager::class, function ($app) {
    return new ConfigManager();
});

$this->app->singleton(CacheManager::class, function ($app) {
    return new CacheManager();
});

$this->app->singleton(DatabaseConnection::class, function ($app) {
    return new DatabaseConnection();
});

// 不適合 Singleton 的服務（有狀態、需要隔離）
// 在 Laravel Octane 環境中，這些服務應該使用 scoped
// $this->app->singleton(UserContext::class, function ($app) {
//     return new UserContext(); // 錯誤！會跨請求共享狀態
// });
```

**注意事項：**
- *在傳統 Laravel 中，Singleton 和 Scoped 行為相同*
- 在 Laravel Octane 中，Singleton 會跨請求共享狀態
- 有狀態的服務在 Octane 環境中應使用 Scoped 而非 Singleton

### 4.3 **Scoped Singleton（請求/任務生命週期）**

> scoped 綁定 *會在「每個請求」或「每個任務」的生命週期內只產生一個實例* 。適合用在 Laravel Octane、Queue 等長駐型應用， *確保同一請求/任務共用同一份，但不同請求/任務彼此獨立* 。
> 差異：singleton 是全程唯一，scoped 是每個請求/任務唯一。

**為什麼需要 Scoped？**
- **Singleton 問題**：在長駐環境（如 Laravel Octane）中，singleton 會跨請求共享狀態，可能造成資料洩漏
- **Scoped 解決方案**：每個請求/任務都有獨立的實例，確保狀態隔離
- **適用場景**：用戶會話、請求上下文、任務狀態等有狀態的服務

```php
// 實作位置：Service Provider 的 register() 方法
// 檔案：app/Providers/AppServiceProvider.php

// 註冊請求/任務生命週期單例
$this->app->scoped(Transistor::class, function (Application $app) { 
    // 每個請求/任務都會得到新的實例
    // 同一請求/任務內多次解析會得到相同實例
    return new Transistor($app->make(PodcastParser::class));
});

// 僅當尚未綁定 Transistor 時才註冊 scoped
$this->app->scopedIf(Transistor::class, function (Application $app) { 
    // 避免重複綁定，適合在條件性註冊時使用
    return new Transistor($app->make(PodcastParser::class));
});
```

**實際應用範例：**

```php
// 實作位置：app/Services/UserSession.php
class UserSession
{
    private $userId;
    
    public function setUserId($id) {
        $this->userId = $id;
    }
    
    public function getUserId() {
        return $this->userId;
    }
}

// 實作位置：Service Provider 的 register() 方法
// 錯誤示範：使用 singleton（會跨請求共享狀態）
$this->app->singleton(UserSession::class, function ($app) {
    return new UserSession();
});

// 正確示範：使用 scoped（每個請求獨立）
$this->app->scoped(UserSession::class, function ($app) {
    return new UserSession();
});
```

**生命週期對比：**

```php
// 實作位置：Controller 或其他地方
// Singleton 問題場景：
// 請求 A：用戶 ID 123
$session = app(UserSession::class);
$session->setUserId(123);

// 請求 B：用戶 ID 456（但會拿到用戶 123 的資料！）
$session = app(UserSession::class);
echo $session->getUserId(); // 輸出：123（錯誤！應該是 456）

// Scoped 解決方案：
// 請求 A：用戶 ID 123
$session = app(UserSession::class);
$session->setUserId(123);

// 請求 B：用戶 ID 456（會得到新的實例）
$session = app(UserSession::class);
$session->setUserId(456);
echo $session->getUserId(); // 輸出：456（正確！）
```

**適用場景：**

```php
// 實作位置：Service Provider 的 register() 方法
// 適合 Scoped 的服務（有狀態、需要隔離）
$this->app->scoped(UserContext::class, function ($app) {
    return new UserContext();
});

$this->app->scoped(RequestLogger::class, function ($app) {
    return new RequestLogger();
});

$this->app->scoped(JobContext::class, function ($app) {
    return new JobContext();
});

// 適合 Singleton 的服務（無狀態、全域共享）
$this->app->singleton(ConfigManager::class, function ($app) {
    return new ConfigManager();
});

$this->app->singleton(CacheManager::class, function ($app) {
    return new CacheManager();
});
```

### 4.4 **Instance 綁定**

> instance 綁定 *是「你自己 new 好物件」後，直接把這個實例註冊到容器。之後所有人拿到的都是這一份，不會再 new 新的* 。適合用在你需要先自訂初始化流程的情境。
> 差異：bind/singleton/scoped 都是用 *閉包生成* ，instance 是直接 *給一個現成的物件* 。

```php
// 實作位置：Service Provider 的 register() 方法
$service = new Transistor(new PodcastParser); // 先手動建立 Transistor 實例
$this->app->instance(Transistor::class, $service); // 直接將實例註冊到容器，之後取得的都是這個實例
```

### 4.5 **Interface 綁定實作**

> 介面綁定是 *把一個介面（interface）綁定到一個具體實作（class），讓你在程式碼裡只依賴介面，容器會自動給你對應的實作* 。這是實現「依賴反轉」和「可替換性」的關鍵。
> 差異：這種綁定通常用在 *多型、測試替換* 等場景。

```php
// 實作位置：Service Provider 的 register() 方法
// 以後只要有人跟容器要 EventPusher（介面），就給他一個 RedisEventPusher（實作類別）
// 這樣你在程式裡只依賴介面，未來要換成別的實作（例如 KafkaEventPusher）只要改這裡就好，其他地方不用動
$this->app->bind(EventPusher::class, RedisEventPusher::class);

// 例如：
interface EventPusher {
    public function push($event);
}

class RedisEventPusher implements EventPusher {
    public function push($event) { /* ... */ }
}

class KafkaEventPusher implements EventPusher {
    public function push($event) { /* ... */ }
}

// 只要改成：
// $this->app->bind(EventPusher::class, KafkaEventPusher::class);
// 其他程式不用動，容器就會自動給你 KafkaEventPusher
```

---

## 5. *Contextual Binding 與屬性注入*（Contextual Binding & Attributes）

### 5.1 **Contextual Binding**

針對 *不同類別注入不同實作*：

```php
// 實作位置：Service Provider 的 register() 方法
$this->app->when(PhotoController::class) // 當容器要解析 PhotoController 時
    ->needs(Filesystem::class)           // 並且這個 Controller 需要 Filesystem 這個依賴
    ->give(function () {                 // 就給它這個東西（用這個方法產生）
        return Storage::disk('local');   // 回傳本地端的 Storage 實例
    });
$this->app->when([VideoController::class, UploadController::class]) // 當容器要解析 VideoController 或 UploadController 時
    ->needs(Filesystem::class)                                      // 並且這些 Controller 需要 Filesystem 這個依賴
    ->give(function () {                                            // 就給它這個東西
        return Storage::disk('s3');                                 // 回傳 S3 的 Storage 實例
    });
```

### 5.2 **Contextual Attributes（屬性注入）**
> *屬性標註注入（Attribute Injection）* 是 Laravel 10+ 新增的語法糖，讓你可以用 PHP 的 Attribute（*#[...]*）直接在 *建構子* 或 *方法參數* 標註，
> 指定要注入的服務細節（例如 Storage driver、Config、目前登入的 User 等）。
> 這樣可以讓依賴注入更直觀、更彈性，尤其是當你需要注入不同設定或不同來源的服務時。

> 屬性注入就是用 PHP 8+ 的 Attribute（像 #[Storage('local')] 這種寫法），直接在建構子或方法參數上標註，告訴 Laravel：「這個參數我要注入什麼東西」。
> Contextual 指的是「根據不同標註，注入不同的服務或設定」。
> 這種語法糖讓你不用自己寫一堆 if/else 或手動決定要注入什麼，直接在參數上標註就好，Laravel 會自動幫你搞定。
> 例如：
> - 你有多個 Storage driver，可以直接 #[Storage('s3')] 或 #[Storage('local')]，Laravel 會自動注入對應的 Storage。
> - 你要注入不同的 config、資料庫連線、目前登入的 user 等，都可以用標註直接指定。
> 
> 括號裡面要填什麼？
> - 以 `#[Storage('local')]` 為例，'local' 就是你要注入的 Storage driver 名稱，也可以填 's3'、'public' 等。
> - 以 `#[Config('app.timezone')]` 為例，'app.timezone' 就是你要注入的 config key。
> - 以 `#[DB('mysql')]` 為例，'mysql' 就是你要注入的資料庫連線名稱。
> 
> 範例：

> 沒有語法糖：
> ```php
> // 實作位置：Controller 或 Service 中
> public function __construct(FilesystemManager $manager) {
>     $this->filesystem = $manager->disk('local');
> }
> ```
> 有語法糖：
> ```php
> // 實作位置：Controller 或 Service 中
> public function __construct(
>     #[Storage('s3')] protected Filesystem $filesystem, // 注入 S3 driver
>     #[Config('app.timezone')] protected string $timezone, // 注入 config('app.timezone')
>     #[DB('mysql')] protected \Illuminate\Database\Connection $db, // 注入 mysql 連線
>     #[Storage('local')] protected Filesystem $filesystem
> ) {}
> ```
> 這樣程式碼更簡潔、易懂，也方便維護。

支援：Auth、Cache、Config、Context、DB、Give、Log、RouteParameter、Tag、CurrentUser 等。

```php
// 實作位置：Service Provider 的 register() 方法
use Illuminate\Container\Attributes\Auth; // 匯入 Auth 標註
use Illuminate\Container\Attributes\Cache; // 匯入 Cache 標註
use Illuminate\Container\Attributes\Config; // 匯入 Config 標註
use Illuminate\Container\Attributes\Context; // 匯入 Context 標註
use Illuminate\Container\Attributes\DB; // 匯入 DB 標註
use Illuminate\Container\Attributes\Give; // 匯入 Give 標註
use Illuminate\Container\Attributes\Log; // 匯入 Log 標註
use Illuminate\Container\Attributes\RouteParameter; // 匯入 RouteParameter 標註
use Illuminate\Container\Attributes\Tag; // 匯入 Tag 標註
```
> 以上這些 Attribute（如 Auth、Cache、Config、DB 等）都是 Laravel 10+ 內建的屬性標註，定義在 `Illuminate\Container\Attributes` 目錄下。
> 它們本質上都是 PHP 8+ 的 Attribute 類別，讓你可以在參數上標註要注入什麼服務或設定。
> 例如：
> ```php
> #[Config('app.timezone')] // 會自動注入 config('app.timezone') 的值
> #[DB('mysql')] // 會自動注入 mysql 連線
> #[Auth('web')] // 會自動注入 web guard 的 user
> ```
> 這些 Attribute 的類別定義通常長這樣：
> ```php
> #[Attribute(Attribute::TARGET_PARAMETER)]
> class Config {
>     public function __construct(public string $key) {}
> }
> #[Attribute(Attribute::TARGET_PARAMETER)]
> class DB {
>     public function __construct(public string $connection) {}
> }
> #[Attribute(Attribute::TARGET_PARAMETER)]
> class Auth {
>     public function __construct(public ?string $guard = null) {}
> }
> ```
> 可以在 Laravel 原始碼的 `Illuminate\Container\Attributes` 目錄下找到這些類別的定義。 

CurrentUser 範例：

```php
// 實作位置：路由檔案中
use App\Models\User; // 匯入 User 模型
use Illuminate\Container\Attributes\CurrentUser; // 匯入 CurrentUser 標註

Route::get('/user', function (#[CurrentUser] User $user) { // 用 CurrentUser 標註，讓 Laravel 自動注入目前登入的 User
    return $user;
})->middleware('auth'); // 這個路由有 auth middleware，確保有登入
```

### 5.3 **自訂 Contextual Attribute**

> 除了用 Laravel 內建的 Attribute，你也可以自己寫一個屬性標註，只要實作 ContextualAttribute 介面並定義 *resolve* 方法，Laravel 就會自動幫你注入你想要的東西。

實作 `Illuminate\Contracts\Container\ContextualAttribute`，如自訂 Config：

```php
// 實作位置：自訂的 Attribute 類別檔案
#[Attribute(Attribute::TARGET_PARAMETER)] // 宣告這是一個 Attribute，且只能用在參數上
class Config implements ContextualAttribute // 定義一個 Config Attribute，並實作 ContextualAttribute 介面
{
    public function __construct(public string $key, public mixed $default = null) {} // 建構子，接收 config key 和預設值
    public static function resolve(self $attribute, Container $container) // resolve 方法，決定怎麼取得要注入的值
    {
        // 從容器拿出 config 服務，取得指定 key 的值，若沒有就用 default
        return $container->make('config')->get($attribute->key, $attribute->default);
    }
}
```

> 用法範例：
> ```php
> // 實作位置：Controller 或 Service 中
> public function __construct(
>     #[Config('app.timezone', 'UTC')] protected string $timezone // 會自動注入 config('app.timezone')，找不到就用 'UTC'
> ) {}
> ```

---

## 6. *Binding Primitives / Variadics / Tagging*（原始型別、可變參數、標籤）

### 6.1 **Binding Primitives**

```php
// 實作位置：Service Provider 的 register() 方法
$this->app->when(UserController::class) // 當容器要解析 UserController 時
    ->needs('$variableName')            // 並且這個 Controller 需要名為 $variableName 的原始型別參數
    ->give($value);                     // 就給它 $value（可以是字串、數字等原始型別）
```
// 用途：讓你可以針對 *「原始型別」* 的參數（如 int、string）指定注入的值。

---

### 6.2 **giveTagged / giveConfig**

```php
// 實作位置：Service Provider 的 register() 方法
$this->app->when(ReportAggregator::class) // 當容器要解析 ReportAggregator 時
    ->needs('$reports')                   // 需要 $reports 這個參數
    ->giveTagged('reports');              // 注入所有被標記為 'reports' 的服務（陣列）
    // 會自動 new 出所有被標記為 'reports' 的服務實例，並組成陣列注入
$this->app->when(ReportAggregator::class) // 同上
    ->needs('$timezone')                  // 需要 $timezone 這個參數
    ->giveConfig('app.timezone');         // 注入 config('app.timezone') 的值
    // 會直接取得 config('app.timezone') 設定值注入
```
// 用途：自動注入一組被標籤的服務，或直接注入 config 設定值。

---

### 6.3 **Binding Typed Variadics**

> 這裡的 needs(Filter::class) 是指 Firewall 的建構子或方法有一個型別為 Filter 的參數。
> 是不是「可變參數」要看你的建構子或方法是不是 ...$filters 這種寫法，Laravel 會自動判斷並依序注入多個物件。
> 例如：__construct(Filter ...$filters) 會注入多個，__construct(Filter $filter) 只會注入一個。
>
> 範例：
> ```php
> // 實作位置：Service 類別中
> class Firewall {
>     public function __construct(Filter ...$filters) {
>         $this->filters = $filters;
>     }
> }
>
> // 實作位置：Service Provider 的 register() 方法
> $this->app->when(Firewall::class) // 當容器要解析 Firewall 時
>     ->needs(Filter::class)        // 需要 Filter 這個型別的參數（可變參數 ...$filters）
>     ->give([                      // 注入這個陣列裡的所有類別（會自動 new）
>         NullFilter::class,
>         ProfanityFilter::class,
>         TooLongFilter::class,
>     ]);
> // 這樣 Laravel 會自動 new 這三個 Filter 物件，依序傳給 Firewall 的建構子。
> // 用途：自動注入多個型別相同的依賴（例如 ...$filters），常用於策略、過濾器等。
> ```

---

### 6.4 **Variadic Tag Dependencies**

```php
// 實作位置：Service Provider 的 register() 方法
$this->app->when(ReportAggregator::class) // 當容器要解析 ReportAggregator 時
    ->needs(Report::class)                // 需要 Report 這個型別的可變參數
    ->giveTagged('reports');              // 注入所有被標記為 'reports' 的服務
```
// 用途：自動注入所有被標籤的服務作為可變參數（...$reports）。

---

### 6.5 **Tagging**

```php
// 實作位置：Service Provider 的 register() 方法
$this->app->bind(CpuReport::class, function () {});      // 綁定 CpuReport 服務
$this->app->bind(MemoryReport::class, function () {});   // 綁定 MemoryReport 服務
$this->app->tag([CpuReport::class, MemoryReport::class], 'reports'); // 把這兩個服務都標記為 'reports'

$this->app->bind(ReportAnalyzer::class, function (Application $app) {
    return new ReportAnalyzer($app->tagged('reports')); // 取得所有被標記為 'reports' 的服務，注入 ReportAnalyzer
});
```
// 用途：將多個服務標記為同一個 tag，之後可以一次性取得所有被標記的服務（常用於 plugin、模組化設計等）。

---

## 7. *Extending Bindings*（擴充綁定）

> Extending Bindings（擴充綁定）是指你可以在原本已經註冊到服務容器的服務上，再加一層包裝或加工，而不是直接覆蓋原本的服務。
> 這通常用在「裝飾器模式」（Decorator Pattern），例如你想在原本的服務功能外再加上 *日誌、快取、權限檢查* 等。
> 適合用在：
> - 想要在不改動原本服務的情況下，動態加上新功能
> - 想要攔截或包裝原本的服務，做額外處理
> 
> 例子：
> ```php
> // 實作位置：Service Provider 的 register() 方法
> $this->app->extend(Service::class, function (Service $service, Application $app) {
>     return new DecoratedService($service); // DecoratedService 裡面包了原本的 Service
> });
> // DecoratedService 可以在呼叫原本 Service 方法前後加上日誌、快取等
> ```

---

## 8. *解析與注入*（Resolving & Injection）

### 8.1 **make / makeWith / bound**

```php
// 實作位置：任何需要手動解析服務的地方
$transistor = $this->app->make(Transistor::class); // 解析並產生 Transistor 實例
$transistor = $this->app->makeWith(Transistor::class, ['id' => 1]); // 解析時傳入額外參數
if ($this->app->bound(Transistor::class)) { /* ... */ } // 判斷容器是否有綁定 Transistor
```

也可用 *Facade* 或 *helper*：

```php
// 實作位置：任何地方
use Illuminate\Support\Facades\App; // 匯入 App Facade
$transistor = App::make(Transistor::class); // 用 Facade 解析
$transistor = app(Transistor::class); // 用全域 helper 解析
```

### 8.2 **注入 Container 本身**

```php
// 實作位置：Controller 或 Service 中
use Illuminate\Container\Container; // 匯入 Laravel 的 Container 類別
public function __construct(protected Container $container) {} // 直接在建構子注入整個容器實例

// 這樣你就可以在類別內部用 $this->container 來手動解析其他服務。
```

> 注入 Container 讓你可以在類別內部「隨時、動態」取得任何已經註冊到容器的服務。
> - *動態決定要用哪個服務*：根據條件決定要 new 哪個物件。
> - *延遲解析*：等到真的要用時才 new 服務。
> - 寫 *Library/Package*：不確定會被注入什麼服務時，可以用容器來取得。
>
> 例子：
> ```php
> // 實作位置：Service 類別中
> class MyService {
>     public function __construct(protected Container $container) {}
>     public function doSomething($type) {
>         // 動態決定要用哪個服務
>         $service = $type === 'foo'
>             ? $this->container->make(FooService::class)
>             : $this->container->make(BarService::class);
>         $service->run();
>     }
> }
> ```
> 這種做法比直接在建構子注入所有依賴更有彈性，但也要注意不要濫用，否則會讓依賴關係變得不明確。

### 8.3 **自動注入**

> ```php
> // 實作位置：Service 類別中
> // 假設你有一個服務
> class AppleMusic {
>     public function play() {
>         echo "播放 Apple Music\n";
>     }
> }
>
> // 在 Controller、Listener、Middleware、Job 等都可以這樣自動注入
> class MusicController
> {
>     protected AppleMusic $apple;
>
>     // Laravel 會自動 new AppleMusic 並注入
>     public function __construct(AppleMusic $apple)
>     {
>         $this->apple = $apple;
>     }
>
>     public function play()
>     {
>         $this->apple->play();
>     }
> }
>
> // 使用
> $controller = app()->make(MusicController::class);
> $controller->play(); // 輸出：播放 Apple Music
> ```

### 8.4 **方法注入與 call**

> ```php
> // 實作位置：任何地方
> use Illuminate\Support\Facades\App;
>
> // 假設你有一個服務
> class AppleMusic {
>     public function play() {
>         echo "播放 Apple Music\n";
>     }
> }
>
> // 1. 方法注入（用 App::call 執行物件方法，Laravel 會自動注入依賴）
> class PodcastStats {
>     public function generate(AppleMusic $apple) {
>         $apple->play();
>         return "產生 Podcast 統計";
>     }
> }
>
> $stats = App::call([new PodcastStats, 'generate']); // 會自動 new AppleMusic 並注入
> echo $stats; // 輸出：播放 Apple Music\n產生 Podcast 統計
>
> // 2. 閉包注入
> $result = App::call(function (AppleMusic $apple) {
>     $apple->play();
>     return "用閉包產生 Apple Music";
> });
> echo $result; // 輸出：播放 Apple Music\n用閉包產生 Apple Music
> ```

---

## 9. *Container 事件*（Container Events）

### 9.1 **resolving**

```php
// 實作位置：Service Provider 的 register() 方法
$this->app->resolving(Transistor::class, function (Transistor $transistor, Application $app) {
    // 當容器解析（make）Transistor 這個服務時，會自動執行這個 callback
    // 你可以在這裡對 Transistor 實例做額外設定或初始化
    // 例如：$transistor->setLogger($app->make(Logger::class));
});
$this->app->resolving(function (mixed $object, Application $app) {
    // 這個 callback 會在解析「任何服務」時都被呼叫
    // 可以用來做全域的物件初始化、監控、debug 等
});
```
// 用途：讓你可以在服務*被解析出來時*，做額外的初始化、注入、監控等動作。
// 時機：每次用 make()、自動注入、或 app()->resolve() 解析服務時都會觸發。

### 9.2 **rebinding**

```php
// 實作位置：Service Provider 的 register() 方法
$this->app->rebinding(
    PodcastPublisher::class,
    function (Application $app, PodcastPublisher $newInstance) {
        // 當 PodcastPublisher 這個服務被「重新綁定」時會觸發這個 callback
        // 你可以在這裡更新已經注入過的物件的依賴
        // 例如：通知其他物件用新的 PodcastPublisher
    },
);
```
// 用途：當某個服務*被重新綁定*（例如單例被覆蓋、或 config 變更時），可以*自動更新所有依賴這個服務的物件*。
// 時機：呼叫 $this->app->bind()、instance()、singleton() 等重新綁定同一個 key 時會觸發。

// 小結：
// *resolving*：每次解析服務時都會觸發，適合做**初始化、注入、監控**。
// *rebinding*：服務被重新綁定時觸發，適合做**依賴更新、資源釋放、通知**等。

---

## 10. *PSR-11 支援*（PSR-11）

```php
// 實作位置：任何需要檢查服務是否存在的地方
use Psr\Container\ContainerInterface; // 匯入 PSR-11 標準的 Container 介面

Route::get('/', function (ContainerInterface $container) { // 你可以 type-hint PSR-11 的 ContainerInterface
    $service = $container->get(Transistor::class); // 用 PSR-11 標準的 get() 方法取得服務
    // ...
});
```

> PSR-11 是 *PHP-FIG* 制定的「*容器介面標準*」，讓 *不同框架的 DI 容器* 都能用同一套 API 操作。
> Laravel 的服務容器實作了 `Psr\Container\ContainerInterface`，所以你可以 type-hint 這個介面，讓你的程式碼更通用、可攜。
> `get()` 方法會回傳你要的服務實例（等同於 Laravel 的 make()）。
> 這種寫法常用於寫 Library、Package、或想讓程式碼能在*多個框架間共用*時。
>
> 無法解析時會丟出 **NotFoundExceptionInterface** 或 **ContainerExceptionInterface**。
> 如果你用 get() 取得一個不存在的服務，會丟出 NotFoundExceptionInterface。
> 如果解析過程有其他錯誤，會丟出 ContainerExceptionInterface。
> 這是 PSR-11 標準規定的錯誤處理方式。
>
> 小結：
> - PSR-11 讓你的程式碼可以不依賴 Laravel 專屬的容器 API，而是用業界通用的標準介面。
> - 適合寫 Library、Package、或需要跨框架的專案。
> - Laravel 100% 支援 PSR-11，你可以放心用 ContainerInterface 來 type-hint。

> 補充說明：
> 如果你只用 Laravel 框架自己開發專案，完全可以不用理會 PSR-11 相關語法，直接用 Laravel 內建的容器 API（如 app()、App::make()、依賴注入）就很夠用、也更方便。
> 只有在你要寫 Library/Package、做跨框架整合，或希望程式碼能在多個框架共用時，才會用到 PSR-11。

## 重要說明

### 推薦的實作位置

**主要位置：Service Provider 的 register() 方法**
- 這是 Laravel 官方推薦的做法
- 便於管理和維護
- 符合 Laravel 的架構設計

**次要位置：bootstrap/app.php**
- 適用於簡單的應用程式
- 不適合複雜的綁定邏輯

**不推薦：路由檔案**
- 僅供學習和測試使用
- 不適合生產環境

### 為什麼要在 Service Provider 中實作？

1. **生命週期管理**：Service Provider 在應用程式啟動時就會執行
2. **依賴管理**：可以正確處理類別之間的依賴關係
3. **可維護性**：所有綁定邏輯集中在一個地方
4. **Laravel 慣例**：符合 Laravel 的架構設計原則

### 註冊 Service Provider

```php
// 實作位置：config/app.php
'providers' => [
    // 其他 providers...
    App\Providers\DemoServiceProvider::class,
],
```

## 總結

整個 Service Container 筆記檔案中的所有內容，**主要實作位置都是 Service Provider 的 register() 方法**。這是 Laravel 官方推薦的做法，也是最符合 Laravel 架構設計的方式。
