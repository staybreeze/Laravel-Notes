# *Laravel Service Container 筆記*

---

## 1. **簡介**（Introduction）

Service Container（服務容器）是 Laravel 管理 *類別依賴* 與 *依賴注入* 的核心工具。依賴注入即「__將依賴物件注入`類別建構子` 或 `setter`__」。

- *服務容器* 就是 Laravel 的「自動物件工廠」或「倉庫」，**你只要宣告需要什麼，容器會自動幫你組裝好所有依賴並交給你**。
- 技術上，服務容器是 `Illuminate\Container\Container` 這個類別的實例，
    負責 
        *綁定（註冊）*、
        *解析（生成）*、
        *依賴注入*、
        *單例管理* 等功能。

- 這讓你的程式碼更乾淨、可測試、易維護。

- 你可以把服務容器想像成一個「_自動幫你準備工具的櫃子_」。你只要說「我要一把螺絲起子（某個物件）」，它不只會給你螺絲起子，還會自動幫你把相關的零件（依賴）都準備好，組裝好再交給你。你不用自己一層層 `new` 物件，全部交給服務容器自動處理。

- 依賴注入常見有三種方式：  

  1. __建構子注入__：依賴物件`透過建構子參數傳入`，Laravel 最常用、最推薦。  
  2. __方法注入__：依賴物件`透過方法參數傳入`，常見於 _Controller action_。  
  3. __屬性注入__：依賴物件直接`指定到類別屬性`（PHP 8.1+ 支援，Laravel 原生較少用）。  

- Laravel _Service Container_ 主要支援前兩種方式。

---

## 2. **自動解析與依賴注入**（Zero Configuration Resolution）

若類別僅依賴 *具體類別（非 interface）* ，容器可自動解析：

- 只要你的類別建構子裡面「_直接寫需要什麼類別_」，Laravel 服務容器就會自動幫你 `new` 好、組裝好，不用自己手動 `new`，也不用特別設定。你只要「_要什麼，Laravel 就給你什麼_」。

```php
// 實作位置：任何類別中，Laravel 會自動注入
class UserService { // 宣告一個服務類別
    public function __construct(Mailer $mailer) { // 白話：我需要 Mailer，請幫我準備好
        $this->mailer = $mailer; // 白話：Laravel 會自動把 Mailer 塞進來
    }
}
```

常見於 `controller、event listener、middleware、job` 等，無需手動綁定。

---

## 3. **何時需要手動操作容器**（When to Utilize the Container）

- 需將 *interface* `綁定到實作`時
- 開發 *package* `需註冊服務`時

- 大多數時候 Laravel 都會自動幫你注入依賴，你不用自己動手。但有些特殊情況，像是在 __普通 function、helper、或閉包裡__ ，Laravel 不會自動幫你注入，這時你就要用 `app()`、`resolve()`、`make()` 這些方法手動跟容器要東西。

- *什麼時候要手動操作容器？*

  1. 在「__Laravel 不會自動注入__」的地方（如 **普通 function、閉包** ）__需要`物件`時__。
  2. 你需要 **動態決定** 要哪個服務時。
  3. 你要 __從容器裡拿出`已經註冊`的單例或服務__ 時。

 ```php
 // 實作位置：路由閉包中
 Route::get('/test', function () {
     $mailer = app(Mailer::class); // 手動跟容器要 Mailer
     $mailer-send(...);
 });
 ```
- 大部分情況不用手動操作容器，只有在 Laravel __不會自動注入的地方才需要__。

---

## 4. **Binding 綁定**（Binding）

<!-- 
自訂字串（如 'foo'）當 key，
取得時用 app('foo')。 
-->

- *Binding（綁定）* 就是「__把一個`名稱（key）`和一個`物件生成方式（value）`註冊到服務容器裡__」。

- 白話來說，就是你跟服務容器說：「以後只要有人要這個東西（key），你就用這個方法（value）幫我生出來。」

- 這個 **「key」可以是`類別名稱、介面名稱、字串`等**；
      **「value」可以是`類別、閉包、物件實例`**。*

- 綁定後，當你用 `app()`、`resolve()`、`make()` 跟容器要這個東西時，容器就會照你綁定的方式幫你生出來。

<!--
 app() 是 Laravel 的全域輔助函式，
可以在任何地方取得服務容器實例。
-->

<!-- 自訂字串（如 'foo'）當 key，取得時用 app('foo')。 -->

 ```php
  // 實作位置：Service Provider 的 register() 方法
  // 綁定一個 key 到一個類別
  app()->bind('foo', FooService::class);

  // 綁定一個 key 到一個閉包（自訂生成邏輯）
  app()->bind('bar', function() {
     return new BarService('參數');
  });

//    app('foo') 或 resolve('foo') 取得你綁定的物件
```

### 4.1 *基本綁定（bind）*

<!-- 用類別綁定時，取得要用 app(Transistor::class) -->

- 基本綁定（bind）是最常見的服務容器綁定方式。__每次有人跟容器要這個服務時，容器都會「`重新執行一次你提供的閉包`」，產生一個全新的物件__。適合用在「__每次都要新的實例__」的情境，例如：每次都要一個新的資料處理器。

- 差異：與 singleton 不同，`bind` 每次都 `new` `新的，singleton` 只 `new` 一次。

__綁定程式碼通常寫在 Service Provider 的 *register()* 方法裡__，但這些方法都是 **Service Container** 提供的：

<!-- 
$this->app 是 Service Provider 裡的屬性，
代表目前的服務容器，
通常在 Service Provider 的 register() 或 boot() 方法裡使用。 
-->

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

---

__為什麼寫在 Service Provider 裡？__

- Service Provider 是 Laravel 啟動時會 __自動執行__ 的類別
- 在 `register()` 方法裡，*$this->app* 就是 `Service Container 實例`
- 這樣可以 *集中管理* 所有服務的註冊邏輯

---

__你也可以在其他地方手動綁定__：不利於集中管理

```php
// 實作位置：bootstrap/app.php 或其他地方
app()->bind(Transistor::class, function () {
    return new Transistor();
});
```

---

於 __service provider__ 內用 `$this->app->bind`：

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

---

也可用 __Facade__：

```php
// 實作位置：Service Provider 的 register() 方法
use Illuminate\Support\Facades\App; // 告訴 PHP：等等會用到 App 這個 Laravel 的 Facade

// 也可以用 App 這個 Facade 來綁定，效果一樣
App::bind(Transistor::class, function (Application $app) { 
    // 這裡可以寫怎麼生 Transistor
    // ...
});
```

---

只在 __未綁定時註冊__：

```php
// 實作位置：Service Provider 的 register() 方法
// 只有「還沒綁定過」這個服務時，才會執行這個綁定
$this->app->bindIf(Transistor::class, function (Application $app) { 
    // 這裡可以寫怎麼生 Transistor
    // ...
});
```

---

__可省略型別__，讓 Laravel 由 `closure` 回傳型別自動推斷：

- 實作位置：*Service Provider* 的 `register()` 方法
- 這種寫法 *不用指定 key* ，Laravel 會自動根據 closure 的 __回傳型別（Transistor）__ 來來當作 key（綁定）
- 也就是說，這行等同於 *App::bind(Transistor::class, ...)*，只是 key 省略了，Laravel 會自動幫你補上。
- 這種寫法比較少見，主要是讓程式碼更簡潔。

```php
App::bind(function (Application $app): Transistor { 
    // 這裡可以寫怎麼生 Transistor
    // ...
});
```

---

### 4.2 *Singleton 綁定*

- singleton 綁定 __只會產生一個物件實例，所有人拿到的都是同一份__。適合用在「「`全域唯一`」的服務，例如：*設定管理、連線池、全域快取* 等。

<!-- 
Config：設定資料通常在啟動時載入一次，整個應用程式都要用同一份，避免每次都重新讀取設定檔，確保一致性。
Cache：快取服務（如 Redis、Memcached）只需要一個連線實例，重複建立連線會浪費資源，也可能造成資料不一致。
資料庫連線：同一個資料庫連線可以被多個物件共用，重複建立連線會增加伺服器負擔，且容易出現連線管理問題。 
-->


- 差異：`bind` 每次都 `new`，
       `singleton` 只 `new` 一次。

---

__Singleton 的生命週期__：

- *整個應用程式生命週期*：從 Laravel __啟動到結束__
- *跨請求共享*：所有 __HTTP 請求__ 都使用同一個實例
- *跨任務共享*：所有 __Queue 任務__ 都使用同一個實例

<!-- 
在傳統 Laravel 架構下，
「應用程式生命週期」通常指一次 HTTP 請求的過程，
所以 singleton 在每個請求內只會有一個實例，
但在 Laravel Octane（長駐程式）下，
生命週期是整個伺服器啟動到關閉，
singleton 會被所有請求共用。 
-->

<!-- 
「生命週期」在 Laravel 有兩種情境：

傳統架構：生命週期是「一次 HTTP 請求」，singleton 只在單一請求內唯一。
Octane（長駐程式）：生命週期是「整個伺服器運行期間」，singleton 會被所有請求共用。 
-->


---

__重要說明：`生命週期`依賴於 Laravel 架構__

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

---

__傳統 Laravel vs Laravel Octane 的差異__：

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

---

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

---

__適用場景__：

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

```

---

__不適合 Singleton 的服務（有狀態、需要隔離）__

- 在 Laravel `Octane` 環境中，這些服務應該使用 `scoped綁定`（__每個請求都會產生新的物件__)
```php
 $this->app->singleton(UserContext::class, function ($app) {
     return new UserContext(); // 錯誤！會跨請求共享狀態
 });
```

---

__注意事項__：

- *在傳統 Laravel 中，`Singleton` 和 `Scoped` 行為相同*
- 在 *Laravel Octane* 中，*`Singleton` 會跨請求共享狀態*
- __有狀態的服務__ 在 `Octane` 環境中應使用 `Scoped `而非 Singleton

---

### 4.3 *Scoped Singleton（請求/任務生命週期）*

- scoped 綁定 __會在「`每個請求`」或「`每個任務`」的生命週期內只產生一個實例__ 。適合用在 Laravel Octane、Queue 等長駐型應用， __確保同一請求/任務共用同一份，但不同請求/任務彼此獨立__ 。

- 差異：singleton 是`全程唯一`，scoped 是`每個請求/任務唯一`。

- scoped 是每個請求只產生一個物件實例，而 _transient_（或`普通綁定`）是`每次取得都 new 一個新物件`，即使在同一個請求裡也會有多個實例，__scoped__ 是`「每請求一個」`， _transient_ 是`「每次一個」`。

```php
// Singleton 範例：整個程式只產生一個物件
app()->singleton(BazService::class, function () {
    return new BazService();
});
// 不論多少請求或取得，BazService 都是同一個實例

// Scoped 範例：每個請求只產生一個物件
app()->scoped(FooService::class, function () {
    return new FooService();
});
// 在同一個 request 內，取得 FooService 都是同一個實例，但不同 request 會有不同實例

// Transient 範例：每次取得都 new 新物件
app()->bind(BarService::class, function () {
    return new BarService();
});
// 每次 app(BarService::class) 都會 new 一個新的 BarService 實例
```

<!-- 
bind
同一個請求生命週期內，每次解析（呼叫）都會產生新物件，多次取得會有多個不同實例。
-->

<!-- 
singleton
傳統 Laravel 架構：每個請求生命週期只產生一個實例，該請求內多次取得都是同一個物件。
Octane（長駐程式）：整個伺服器運行期間只產生一個實例，所有請求都共用同一個物件。
-->

<!-- 
scoped
每個請求生命週期只產生一個實例，不同請求有不同物件。
Octane（長駐程式）：適用。
-->

---

__為什麼需要 Scoped？__

- *Singleton 問題*：在`長駐環境`（如 Laravel Octane）中，`singleton` __會跨請求共享狀態__，可能`造成資料洩漏`
- *Scoped 解決方案*：每個請求/任務都有 __獨立的實例__，確保狀態隔離
- *適用場景*：用戶會話、請求上下文、任務狀態等有狀態的服務

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

---

__實際應用範例__

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

---

__生命週期對比__

```php
// 實作位置：Controller 或其他地方
// Singleton 問題場景：
// 請求 A：用戶 ID 123
$session = app(UserSession::class);
$session->setUserId(123);

// 請求 B：用戶 ID 456（但會拿到用戶 123 的資料！）
$session = app(UserSession::class);
echo $session->getUserId(); // 輸出：123（錯誤！應該是 456）

---

// Scoped 解決方案：
// 請求 A：用戶 ID 123
$session = app(UserSession::class);
$session->setUserId(123);

// 請求 B：用戶 ID 456（會得到新的實例）
$session = app(UserSession::class);
$session->setUserId(456);
echo $session->getUserId(); // 輸出：456（正確！）
```

---

__適用場景__

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

---

// 適合 Singleton 的服務（無狀態、全域共享）
$this->app->singleton(ConfigManager::class, function ($app) {
    return new ConfigManager();
});

$this->app->singleton(CacheManager::class, function ($app) {
    return new CacheManager();
});
```

---

### 4.4 *Instance 綁定*

- instance 綁定 __是「`你自己 new 好物件`」後，直接把這個實例註冊到容器。之後所有人拿到的都是這一份，不會再 new 新的__ 。適合用在`你需要先自訂初始化流程的情境`。

- 差異：`bind/singleton/scoped` 都是用 __閉包生成__ ，
       `instance` 是直接 __給一個現成的物件__ 。

- `instance` 是直接註冊 *你手動 new 的物件* 。

- `singleton` 是註冊一個 *產生物件的方法* ，__容器會自己 new 並保存唯一實例__。

- 兩者取得的都是同一個物件，但 `instance` 你要 __自己 new__ ，
                          `singleton` 由 __容器管理建立時機__ 。

```php
// 實作位置：Service Provider 的 register() 方法
$service = new Transistor(new PodcastParser); // 先手動建立 Transistor 實例
$this->app->instance(Transistor::class, $service); // 直接將實例註冊到容器，之後取得的都是這個實例
```
```php
// 只要用 app(Transistor::class) 或 resolve(Transistor::class) 取得 Transistor
$transistor1 = app(Transistor::class);
$transistor2 = resolve(Transistor::class);

// $transistor1 和 $transistor2 都會是你註冊的那個 $service 實例
var_dump($transistor1 === $service); // true
var_dump($transistor2 === $service); // true
```

---

### 4.5 *Interface 綁定實作*

- 介面綁定是 __把一個介面（interface）綁定到一個具體實作（class），讓你在程式碼裡只依賴介面，容器會自動給你對應的實作__ 。這是實現「依賴反轉」和「可替換性」的關鍵。

- 差異：這種綁定通常用在 __多型、測試替換__ 等場景。

- 以後只要有人跟容器要 `EventPusher`（介面），就給他一個 `RedisEventPusher`（實作類別）
- 這樣你在程式裡 __只依賴介面__，未來要換成別的實作（例如 `KafkaEventPusher`）只要改這裡就好，其他地方不用動

```php
// 實作位置：Service Provider 的 register() 方法

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

```

- 只要改成：
  - `$this->app->bind(EventPusher::class, KafkaEventPusher::class);`
  - 其他程式不用動，容器就會自動給你 `KafkaEventPusher`

---

## 5. **Contextual Binding 與屬性注入**（Contextual Binding & Attributes）

### 5.1 *Contextual Binding*（情境式綁定）

<!-- 
「 情境式綁定語法」是 Laravel 服務容器提供的一種語法，
讓你可以根據「不同類別」和「不同需求」注入「不同的依賴實作」。
常用語法有：

when()：指定情境（哪個類別）
needs()：指定需要的依賴
give()：指定要給的實作 
-->

針對 __不同類別注入不同實作__：特殊狀況

```php
// 實作位置：Service Provider 的 register() 方法
$this->app->when(PhotoController::class) // 當容器要解析 PhotoController 時
    ->needs(Filesystem::class)           // 並且這個 PhotoController 需要 Filesystem 這個依賴
    ->give(function () {                 // 就給它這個東西（用這個方法產生）
        return Storage::disk('local');   // 回傳本地端的 Storage 實例
    });
// 當 Laravel 服務容器要建立 PhotoController 時，
// 如果它的建構子需要 Filesystem 這個依賴，
// 容器就會自動注入 Storage::disk('local') 作為 Filesystem 實例。

// 如果 PhotoController 沒有需要 Filesystem 這個依賴，
// 或建構子參數型別不是 Filesystem，
// 這個情境式綁定就不會發生作用，
// 容器會用預設方式解析其他依賴。

$this->app->when([VideoController::class, UploadController::class]) // 當容器要解析 VideoController 或 UploadController 時
    ->needs(Filesystem::class)                                      // 並且這些 Controller 需要 Filesystem 這個依賴
    ->give(function () {                                            // 就給它這個東西
        return Storage::disk('s3');                                 // 回傳 S3 的 Storage 實例
    });
```

---

### 5.2 *Contextual Attributes（屬性注入）*

- __屬性標註注入（Attribute Injection）__ 是 `Laravel 10+` 新增的`語法糖`，讓你可以用 PHP 的 Attribute（*#[...]*）直接在 *建構子* 或 *方法參數* 標註。

- 指定要注入的`服務細節`（例如 __Storage driver、Config、目前登入的 User__ 等）。

- 這樣可以讓依賴注入更直觀、更彈性，尤其是當你需要注入不同設定或不同來源的服務時。

- __屬性注入__ 就是用 `PHP 8+` 的 Attribute（像 #[Storage('local')] 這種寫法），直接在建構子或方法參數上標註，告訴 Laravel：「_這個參數我要注入什麼東西_」。

---

- *Contextual* 指的是「__根據不同標註，注入不同的服務或設定__」。

- 這種語法糖讓你 __不用自己寫一堆 if/else__ 或 __手動決定__ 要注入什麼，直接在參數上標註就好，Laravel 會自動幫你搞定。

- 例如：
  - 你有多個 `Storage driver`，可以直接 #[Storage('s3')] 或 #[Storage('local')]，Laravel 會自動注入對應的 Storage。
  - 你要注入不同的 config、資料庫連線、目前登入的 user 等，都可以用標註直接指定。

---

- *括號裡面要填什麼？*

  - 以 `#[Storage('local')]` 為例，`'local'` 就是你要注入的 `Storage driver `名稱，也可以填 `'s3'`、`'public'` 等。
  - 以 `#[Config('app.timezone')]` 為例，`'app.timezone'` 就是你要注入的 __config key__。
  - 以 `#[DB('mysql')]` 為例，`'mysql'` 就是你要注入的 __資料庫連線名稱__。

---

- __沒有語法糖__：

 ```php
// 實作位置：Controller 或 Service 中
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemManager;

class ExampleService
{
    protected $filesystem;

    // 傳統寫法：手動注入 FilesystemManager，並取得 local driver 的 Filesystem
    public function __construct(FilesystemManager $manager)
    {
        $this->filesystem = $manager->disk('local');
    }
}
```

```php
// 有語法糖
// Laravel 11 語法糖寫法：自動注入 local driver 的 Filesystem
use Illuminate\Container\Attributes\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

class ExampleServiceWithAttribute
{
    public function __construct(
        #[Storage('local')] protected Filesystem $filesystem
    ) {
        // $filesystem 已自動注入 local driver 的 Filesystem 實例
    }
}
// 這是因為 Laravel 11 的屬性注入語法糖，
// #[Storage('local')] 會告訴 Laravel：「我要注入 local driver 的 Filesystem 實例」，
// Laravel 會自動呼叫 Storage::disk('local')，
// 取得 Filesystem 物件並注入。

// 你不用自己取得 FilesystemManager，
// Laravel 會根據屬性標註自動解析並注入正確型別（Filesystem），
// // 這就是語法糖的作用，讓你寫法更簡潔。
```

- __有語法糖__：

 ```php
  // 實作位置：Controller 或 Service 中
  public function __construct(
     #[Storage('s3')] protected Filesystem $filesystem, // 注入 S3 driver
     #[Config('app.timezone')] protected string $timezone, // 注入 config('app.timezone')
     #[DB('mysql')] protected \Illuminate\Database\Connection $db, // 注入 mysql 連線
     #[Storage('local')] protected Filesystem $filesystem
  ) {}
```

```php
// 沒語法糖
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

public function __construct()
{
    $this->filesystem = Storage::disk('s3');
    $this->timezone = config('app.timezone');
    $this->db = DB::connection('mysql');
}
```

- 這樣程式碼更簡潔、易懂，也方便維護。

---

支援：_Auth、Cache、Config、Context、DB、Give、Log、RouteParameter、Tag、CurrentUser_ 等。

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
- 以上這些 Attribute（如 __Auth、Cache、Config、DB__ 等）都是 __Laravel 10+__ 內建的屬性標註，定義在 `Illuminate\Container\Attributes` 目錄下。

- 它們本質上都是 __PHP 8+__ 的 Attribute 類別，讓你可以 __在參數上標註要注入什麼服務或設定__。
- 例如：
 ```php
    #[Config('app.timezone')] // 會自動注入 config('app.timezone') 的值
    #[DB('mysql')] // 會自動注入 mysql 連線
    #[Auth('web')] // 會自動注入 web guard 的 user
```

---

- 這些 __Attribute 的類別__ 定義通常長這樣：

 ```php
#[Attribute(Attribute::TARGET_PARAMETER)] // 宣告這個 Attribute 只能用在參數上
class Config {
    public function __construct(public string $key) {} // 用來標註參數，指定要注入的 config key
}

#[Attribute(Attribute::TARGET_PARAMETER)] // 宣告這個 Attribute 只能用在參數上
class DB {
    public function __construct(public string $connection) {} // 用來標註參數，指定要注入的資料庫連線名稱
}

#[Attribute(Attribute::TARGET_PARAMETER)] // 宣告這個 Attribute 只能用在參數上
class Auth {
    public function __construct(public ?string $guard = null) {} // 用來標註參數，指定要注入的認證 guard
}
```

---

- 可以在 Laravel 原始碼的 `Illuminate\Container\Attributes` 目錄下找到這些類別的定義。 

__CurrentUser 範例__

```php
// 實作位置：路由檔案中
use App\Models\User; // 匯入 User 模型
use Illuminate\Container\Attributes\CurrentUser; // 匯入 CurrentUser 標註

Route::get('/user', function (#[CurrentUser] User $user) { // 用 CurrentUser 標註，讓 Laravel 自動注入目前登入的 User
    return $user;
})->middleware('auth'); // 這個路由有 auth middleware，確保有登入
```

---

### 5.3 *自訂 Contextual Attribute*

- 除了用 Laravel 內建的 Attribute，你也可以自己寫一個 __屬性標註__，只要實作 `ContextualAttribute 介面`，並定義 `resolve` 方法，Laravel 就會自動幫你注入你想要的東西。

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

- **用法範例**：

 ```php
  // 實作位置：Controller 或 Service 中
  public function __construct(
     #[Config('app.timezone', 'UTC')] protected string $timezone // 會自動注入 config('app.timezone')，找不到就用 'UTC'
  ) {}
```

---

## 6. **Binding Primitives / Variadics / Tagging**（原始型別、可變參數、標籤）

### 6.1 *Binding Primitives*

```php
// 實作位置：Service Provider 的 register() 方法
$this->app->when(UserController::class) // 當容器要解析 UserController 時
    ->needs('$variableName')            // 並且這個 Controller 需要名為 $variableName 的原始型別參數 
    // 如果建構式有名為 $variableName 的原始型別參數（如 string、int）
    ->give($value);                     // 就給它 $value（可以是字串、數字等原始型別）
```
- 用途：讓你可以針對 *「原始型別」* 的參數（如 int、string）__指定注入的值__。

---

### 6.2 *giveTagged / giveConfig*

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
- 用途：`自動注入一組被標籤的服務`，或 __直接注入 config 設定值__。

---

### 6.3 *Binding Typed Variadics*

- 這裡的 __needs(Filter::class)__ 是指 `Firewall 的建構子或方法有一個型別為 Filter 的參數`。
  - Firewall 在程式裡通常指「__防火牆__」元件，用來`過濾、檢查或攔截請求`，例如 _判斷是否允許存取、驗證資料安全_ 等。

- 是不是「_可變參數_」要看你的建構子或方法是不是 `...$filters` 這種寫法，Laravel 會自動判斷並依序注入多個物件。
- 例如：`__construct(Filter ...$filters)` 會注入 __多個__，`__construct(Filter $filter)` 只會注入 __一個__。

- 範例：
 ```php
  // 實作位置：Service 類別中
  class Firewall {
     public function __construct(Filter ...$filters) {
         $this->filters = $filters;
     }
  }

  // 實作位置：Service Provider 的 register() 方法
  $this->app->when(Firewall::class) // 當容器要解析 Firewall 時
     ->needs(Filter::class)        // 需要 Filter 這個型別的參數（可變參數 ...$filters）
     ->give([                      // 注入這個陣列裡的所有類別（會自動 new）
         NullFilter::class,
         ProfanityFilter::class,
         TooLongFilter::class,
     ]);
  // 這樣 Laravel 會自動 new 這三個 Filter 物件，依序傳給 Firewall 的建構子。
  // 用途：自動注入多個型別相同的依賴（例如 ...$filters），常用於策略、過濾器等。
```

---

### 6.4 *Variadic Tag Dependencies*

```php
// 實作位置：Service Provider 的 register() 方法
$this->app->when(ReportAggregator::class) // 當容器要解析 ReportAggregator 時
    ->needs(Report::class)                // 如果建構式有 ...$reports 這種可變參數，型別是 Report
    ->giveTagged('reports');              // 就自動注入所有被標記為 'reports' 的服務（組成陣列）

// 這代表：ReportAggregator 的建構式如果是
// public function __construct(Report ...$reports)
// 容器會自動 new 出所有被標記為 'reports' 的 Report 物件，
// 並依序注入到 ...$reports 參數裡。
```
- 用途：自動注入 __所有被標籤的服務__ 作為`可變參數`（_...$reports_）。

---

### 6.5 *Tagging*

```php
// 實作位置：Service Provider 的 register() 方法
$this->app->bind(CpuReport::class, function () {});      // 綁定 CpuReport 服務
$this->app->bind(MemoryReport::class, function () {});   // 綁定 MemoryReport 服務
$this->app->tag([CpuReport::class, MemoryReport::class], 'reports'); // 把這兩個服務都標記為 'reports'

$this->app->bind(ReportAnalyzer::class, function (Application $app) {
    return new ReportAnalyzer($app->tagged('reports')); // 取得所有被標記為 'reports' 的服務，注入 ReportAnalyzer
});
```
- 用途：將 __多個服務標記為同一個 tag，之後可以`一次性取得`所有被標記的服務__（常用於 _plugin、模組化設計_ 等）。

---

## 7. **Extending Bindings**（擴充綁定）

- Extending Bindings（擴充綁定）是指你可以 _在原本已經註冊到服務容器的服務上，再加一層包裝或加工_，而 __不是直接覆蓋原本的服務__。

- 這通常用在「_裝飾器模式_」（Decorator Pattern），例如你想在原本的服務功能外再加上 *日誌、快取、權限檢查* 等。
- 適合用在：
  - 想要在 __不改動原本服務的情況下__，`動態加上新功能`
  - 想要 __攔截__ 或 __包裝__ 原本的服務，做`額外處理`


 ```php
  // 實作位置：Service Provider 的 register() 方法
  $this->app->extend(Service::class, function (Service $service, Application $app) {
     return new DecoratedService($service); // DecoratedService 裡面包了原本的 Service
  });
  // DecoratedService 可以在呼叫原本 Service 方法前後加上日誌、快取等
```

---

## 8. **解析與注入**（Resolving & Injection）

### 8.1 *make / makeWith / bound*

```php
// 實作位置：任何需要手動解析服務的地方
$transistor = $this->app->make(Transistor::class); // 解析並產生 Transistor 實例
$transistor = $this->app->makeWith(Transistor::class, ['id' => 1]); // 解析時傳入額外參數
if ($this->app->bound(Transistor::class)) { /* ... */ } // 判斷容器是否有綁定 Transistor
```

---

也可用 __Facade__ 或 __helper__：

```php
// 實作位置：任何地方
use Illuminate\Support\Facades\App; // 匯入 App Facade
$transistor = App::make(Transistor::class); // 用 Facade 解析
$transistor = app(Transistor::class); // 用全域 helper 解析
```

---

### 8.2 *注入 Container 本身*

```php
// 實作位置：Controller 或 Service 中
use Illuminate\Container\Container; // 匯入 Laravel 的 Container 類別

public function __construct(protected Container $container) {} // 直接在建構子注入整個容器實例，也就是 Laravel 的 Service Container（服務容器），

// 這樣你就可以在類別內部用 $this->container 來手動解析其他服務。
```

- 注入 Container 讓你可以在`類別內部`「_隨時、動態_」`取得任何已經註冊到容器的服務`。

  - __動態決定要用哪個服務__：根據條件決定要 `new` 哪個物件。
  - __延遲解析__：等到真的要用時才 `new` 服務。
  - 寫 __Library/Package__：不確定會被注入什麼服務時，可以用容器來取得。

 ```php
  // 實作位置：Service 類別中
  class MyService {
     public function __construct(protected Container $container) {}
     public function doSomething($type) {
         // 動態決定要用哪個服務
         $service = $type === 'foo'
             ? $this->container->make(FooService::class)
             : $this->container->make(BarService::class);
         $service->run();
     }
  }
```
－ 這是在 `service layer`（服務層）直接注入 `Service Container`，而不是在 `Service Provider` 的 `register` 方法裡綁定，這種做法 __適合需要動態解析服務的情境__，但要注意不要濫用，避免依賴關係混亂。

- 這種做法__比直接在建構子注入所有依賴__`更有彈性`，但也要注意不要濫用，否則會讓依賴關係變得不明確。

---

### 8.3 *自動注入*

 ```php
  // 實作位置：Service 類別中
  // 假設你有一個服務
  class AppleMusic {
     public function play() {
         echo "播放 Apple Music\n";
     }
  }

  // 在 Controller、Listener、Middleware、Job 等都可以這樣自動注入
  class MusicController
  {
     protected AppleMusic $apple;

     // Laravel 會自動 new AppleMusic 並注入
     public function __construct(AppleMusic $apple)
     {
         $this->apple = $apple;
     }

     public function play()
     {
         $this->apple->play();
     }
  }

  // 使用
  $controller = app()->make(MusicController::class);
  $controller->play(); // 輸出：播放 Apple Music
```

---

### 8.4 *方法注入與 call*

 ```php
  // 實作位置：任何地方
  use Illuminate\Support\Facades\App;

  // 假設你有一個服務
  class AppleMusic {
     public function play() {
         echo "播放 Apple Music\n";
     }
  }

  // 1. 方法注入（用 App::call 執行物件方法，Laravel 會自動注入依賴）
  class PodcastStats {
     public function generate(AppleMusic $apple) {
         $apple->play();
         return "產生 Podcast 統計";
     }
  }

  $stats = App::call([new PodcastStats, 'generate']); // 會自動 new AppleMusic 並注入
  echo $stats; // 輸出：播放 Apple Music\n產生 Podcast 統計

---

  // 2. 閉包注入
  $result = App::call(function (AppleMusic $apple) {
     $apple->play();
     return "用閉包產生 Apple Music";
  });
  echo $result; // 輸出：播放 Apple Music\n用閉包產生 Apple Music
```

---

## 9. **Container 事件**（Container Events）

### 9.1 *resolving*

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
- 用途：讓你可以在服務 __被解析出來時__ ，做額外的 __初始化、注入、監控__ 等動作。
- 時機：每次用 `make()`、`自動注入`、或 `app()->resolve()` 解析服務時都會觸發。

---

### 9.2 *rebinding*

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
- 用途：當某個服務 __被重新綁定__（例如`單例被覆蓋、或 config 變更`時），可以自動更新所有依賴這個服務的物件。
- 時機：呼叫 `$this->app->bind()`、`instance()`、`singleton()` 等 __重新綁定同一個 key 時會觸發__。

- 小結：
    - *resolving*：每次 *解析服務時* 都會觸發，適合做 __初始化、注入、監控__。
    - *rebinding*：服務 *被重新綁定時* 觸發，適合做 __依賴更新、資源釋放、通知__ 等。

---

## 10. **PSR-11 支援**（PSR-11）

```php
// 實作位置：任何需要檢查服務是否存在的地方
use Psr\Container\ContainerInterface; // 匯入 PSR-11 標準的 Container 介面

Route::get('/', function (ContainerInterface $container) { // 你可以 type-hint PSR-11 的 ContainerInterface
    $service = $container->get(Transistor::class); // 用 PSR-11 標準的 get() 方法取得服務
    // ...
});
```

- PSR-11 是 *PHP-FIG* 制定的「_容器介面標準_」，讓 *不同框架的 DI 容器* 都能`用同一套 API 操作`。

- Laravel 的服務容器實作了 `Psr\Container\ContainerInterface`，所以你可以 `type-hint` 這個介面，讓你的程式碼更通用、可攜。

- `get()` 方法會`回傳你要的服務實例`（等同於 Laravel 的 `make()`）。

- 這種寫法常用於寫 `Library、Package`、或想讓程式碼能在 _多個框架間共用_ 時。

- *無法解析* 時會丟出 `NotFoundExceptionInterface` 或 `ContainerExceptionInterface`。

- 如果你用 *`get()` 取得一個不存在的服務*，會丟出 `NotFoundExceptionInterface`。

- 如果*解析過程有其他錯誤*，會丟出 `ContainerExceptionInterface`。

- 這是 PSR-11 標準規定的 __錯誤處理__ 方式。

- __小結__

  - PSR-11 讓你的程式碼可以不依賴 Laravel 專屬的容器 API，而是用 _業界通用的標準介面_。
  - 適合寫 `Library、Package、或需要跨框架的`專案。
  - Laravel `100% 支援` PSR-11，你可以放心用 `ContainerInterface` 來 `type-hint`。

- __補充說明__

  - 如果你只用 Laravel 框架自己開發專案，完全可以不用理會 PSR-11 相關語法，直接用 Laravel 內建的容器 API（如 `app()`、`App::make()`、`依賴注入`）就很夠用、也更方便。
  - 只有在你要寫 `Library/Package`、做`跨框架整合`，或希望程式碼能在多個框架共用時，才會用到 PSR-11。

---

## __重要說明__

### *推薦的實作位置*

__主要位置：`Service Provider` 的 register() 方法__
- 這是 Laravel 官方推薦的做法
- 便於管理和維護
- 符合 Laravel 的架構設計

__次要位置：`bootstrap/app.php`__
- 適用於簡單的應用程式
- 不適合複雜的綁定邏輯

__不推薦：`路由檔案`__
- 僅供學習和測試使用
- 不適合生產環境

---

### *為什麼要在 `Service Provider` 中實作？*

1. __生命週期管理__：Service Provider 在應用程式`啟動時就會執行`
2. __依賴管理__：可以`正確處理`類別之間的依賴關係
3. __可維護性__：所有綁定邏輯`集中`在一個地方
4. __Laravel 慣例__：符合 Laravel 的`架構設計原則`

---

### *註冊 Service Provider*

```php
// 實作位置：config/app.php
'providers' => [
    // 其他 providers...
    App\Providers\DemoServiceProvider::class,
],
```

---

## __總結__

整個 Service Container 筆記檔案中的所有內容，__主要實作位置__ 都是 `Service Provider` 的 `register()` 方法。這是 Laravel 官方推薦的做法，也是最符合 Laravel 架構設計的方式。
