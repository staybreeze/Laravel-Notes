# *Laravel Service Providers 筆記*

---

## 1. **簡介**（Introduction）

Service Provider 是 Laravel 應用啟動的核心。
*所有服務*（包含 _核心 mailer、queue、cache_ 等）皆透過 provider 啟動與註冊。

- __服務（Service）__：通常指`可以直接取得、呼叫的功能`（_資料庫連線、快取、郵件、日誌_ 等）。
- __元件（Component）__：偏向`框架內部的功能模組`（_路由、事件、Session、Queue_ 等）。

Provider 主要負責：
- 註冊 _service container_ 綁定
- 註冊 _事件監聽、middleware、route_ 等

部分 provider 為[__延遲載入__」（deferred），僅在實際需要時才載入。

- 所有 _自訂 provider_ 註冊於 `bootstrap/providers.php`。

<!-- 
config/app.php 只用來設定預設的 providers，

'providers' => [
    Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Broadcasting\BroadcastServiceProvider::class,
    // ...其他預設 provider
],

---

自訂 provider 建議集中管理於 bootstrap/providers.php。 
return [
    App\Providers\DemoServiceProvider::class,
    App\Providers\CustomServiceProvider::class,
    // ...其他自訂 provider
];
-->

---

## 2. **撰寫 Service Provider**（Writing Service Providers）

所有 provider 需繼承 `Illuminate\Support\ServiceProvider`，通常包含 `register` 與 `boot` 方法。

---

### 2.1 *Artisan 產生 provider*

```bash
# 實作位置：終端機命令
php artisan make:provider RiakServiceProvider
```

- Laravel 會自動將 __新 provider__ 加入 `bootstrap/providers.php`。

---

### 2.2 *register 方法*

僅用於 **service container** 綁定，不可註冊事件、route 等（_`避免依賴`尚未載入的服務_）。

```php
// 實作位置：app/Providers/RiakServiceProvider.php
// 宣告這個檔案所屬的命名空間，方便自動載入與組織
namespace App\Providers;

// 匯入 Riak 連線服務的類別，之後可以直接用 Connection 這個名稱
use App\Services\Riak\Connection;
// 匯入 Application 介面，作為型別提示用
use Illuminate\Contracts\Foundation\Application;
// 匯入 Laravel 的 ServiceProvider 基底類別
use Illuminate\Support\ServiceProvider;

// 定義一個自訂的 Service Provider，繼承 Laravel 的 ServiceProvider
class RiakServiceProvider extends ServiceProvider
{
    // 覆寫 ServiceProvider 的 register 方法，註冊服務到 Service Container
    public function register(): void
    {
        // 在 Service Container 綁定一個單例（singleton），key 是 Connection::class
        $this->app->singleton(Connection::class, function (Application $app) {
            // 這個 closure 會在第一次解析 Connection::class 時被執行
            // Laravel 會自動把 Application 實例（Service Container）注入進來

            // 建立一個新的 Connection 物件，並把 config/riak.php 的設定陣列傳進去
            return new Connection(config('riak'));

        });
    }
}
```
- `config('riak')` 是 Laravel 的`全域輔助函式`，會回傳 `config/riak.php` 的內容
- `config('riak')` 只會 __讀取__ 設定檔並回傳內容，__不會寫入或新增__ 資料
- `config()` 其實底層也是呼叫 *app('config')->get('riak')*。

- 也可以寫成 `return new Connection($app['config']['riak'])`;
    - 這是透過 Service Container 取得 config 服務（ConfigRepository），再取出 riak 設定。
    - **$app['config']** 其實就是 `app()->make('config')`，是一個 `ConfigRepository` 物件。
    - **$app['config']['riak']** 會回傳 `config/riak.php` 的內容。
    - 兩種寫法取得的資料完全一樣

- 推薦用 **$app['config']['riak']**的原因：
    - 在 _ServiceProvider 的 closure_ 內，推薦用 $app['config']['riak']，因為這樣 __依賴都來自容器__，未來測試或重構更彈性，更符合依賴注入與可測試性原則
    - `config('riak')` 雖然方便，但會*直接依賴全域函式*，對於測試、解耦合稍微不利

<!-- 
在 ServiceProvider 的 closure 內，推薦用 $app['config']['riak']，因為這樣依賴都來自容器，方便測試與重構。
如果是在專案其他地方（如 controller、service），用 config('riak') helper 沒有太大問題，
因為這些地方本來就會依賴全域函式，對測試和解耦合影響較小。 
-->

---

### 2.3 *bindings / singletons 屬性*

可用 **public** 屬性快速註冊多個 `binding/singleton`：

```php
// 實作位置：app/Providers/AppServiceProvider.php
class AppServiceProvider extends ServiceProvider // 定義一個自訂的 Service Provider，繼承 Laravel 的 ServiceProvider
{
    // 透過 public $bindings 屬性，可以一次註冊多個「一般綁定」到 Service Container
    public $bindings = [
        // 當需要 ServerProvider 時，自動解析為 DigitalOceanServerProvider 實例
        ServerProvider::class => DigitalOceanServerProvider::class,
    ];
    // 透過 public $singletons 屬性，可以一次註冊多個「單例綁定」到 Service Container
    public $singletons = [
        // 當需要 DowntimeNotifier 時，回傳同一個 PingdomDowntimeNotifier 實例（單例）

        // 為什麼不是 DowntimeNotifier::class => DowntimeNotifier::class？
        // 因為 DowntimeNotifier 是介面（interface），不能被實例化，必須綁定到一個具體的實作類別（如 PingdomDowntimeNotifier）
        // 這樣當你 type-hint DowntimeNotifier 時，Service Container 才能自動注入對應的實作物件
        DowntimeNotifier::class => PingdomDowntimeNotifier::class,
        // 當需要 ServerProvider 時，回傳同一個 ServerToolsProvider 實例（單例）
        ServerProvider::class => ServerToolsProvider::class,
    ];
}
```

<!-- 
public $bindings 和 public $singletons 屬性，
本質上和你在 Service Provider 裡用 $this->app->bind() 或 $this->app->singleton() 的效果一樣，
都是把類別綁定到 Service Container，
只是用屬性可以一次註冊多個，比逐一呼叫方法更簡潔。 
-->

<!-- 
果需要自訂物件生成邏輯（如傳參數、依賴注入、條件判斷），
還是要用 register() 方法和 closure，
這樣才能靈活控制物件的建立方式。
屬性適合簡單綁定，複雜情境還是要用方法。 
-->

---

### 2.4 *boot 方法*

用於「__啟動__」`事件監聽、view composer、route` 等 _副作用操作_，並保證此時所有 provider 都已註冊完畢、所有服務都可安全使用。

- **補充說明**：
  - `register` 方法是在 Laravel 啟動時「_最先_」呼叫，用來**註冊服務到 Service Container**，只做綁定，不做副作用。
  - `boot` 方法則是在「_所有 provider 都註冊完畢_」後才會呼叫，這時候所有服務都已經可以安全使用，適合做**事件監聽、view composer、route 註冊等`副作用操作`**。

  - 所以 `register` 是「_註冊_」服務，
        `boot` 則是「_啟動」或「初始化_」需要依賴其他服務的行為。

```php
// 實作位置：app/Providers/ComposerServiceProvider.php
namespace App\Providers; // 宣告命名空間，方便自動載入

use Illuminate\Support\Facades\View; // 匯入 View 門面，用於註冊 view composer
use Illuminate\Support\ServiceProvider; // 匯入 ServiceProvider 基底類別

class ComposerServiceProvider extends ServiceProvider // 定義一個自訂的 Service Provider
{
    public function boot(): void // boot 方法會在所有 provider 註冊完畢後自動執行
    {
        // 註冊 view composer，當渲染 'view' 這個視圖時會執行下方閉包
        View::composer('view', function () {
            // ... 這裡可以放資料綁定、邏輯等
        });
    }
}
```

---

#### 2.4.1 **boot 方法依賴注入**

可 *type-hint* 依賴，容器自動注入：

```php
// 實作位置：app/Providers/AppServiceProvider.php
use Illuminate\Contracts\Routing\ResponseFactory; // 匯入 ResponseFactory 介面，作為 type-hint 用

public function boot(ResponseFactory $response): void // boot 方法可以 type-hint 依賴，容器會自動注入
{
    // 使用 ResponseFactory 的 macro 方法，註冊一個名為 'serialized' 的回應巨集
    $response->macro('serialized', function (mixed $value) {
        // ... 這裡可以自訂回應的序列化邏輯
    });
}
```

---

- __boot 方法依賴注入可以幹嘛？__

  - 讓 boot 方法可以像 Controller、建構子一樣，`直接 type-hint` 需要的服務，Laravel 會自動注入，*不用手動 `app()->make()`*。
  - 常用於`註冊事件、macro、view composer、route` 等時，*直接取得需要的服務物件*（如 __ResponseFactory、Event Dispatcher、Router...__）。

  - *優點*：

    - 自動依賴注入，減少樣板程式碼。
    - 依賴關係一目了然，維護更容易。
    - 開發體驗一致，和 Controller、建構子注入一樣直覺。

  ```php
  // 實作位置：app/Providers/AppServiceProvider.php
  // 依賴注入寫法（推薦）
  public function boot(ResponseFactory $response) {
      // $response 會自動注入，不用自己 app()->make(...)
  }
  // 傳統寫法（未用依賴注入）
  public function boot() {
      $response = app()->make(ResponseFactory::class); // 需手動解析服務
  }
  ```

---

## 3. **註冊 Provider**（Registering Providers）

所有 provider 註冊於 `bootstrap/providers.php`，內容為 __provider class 陣列__：

```php
// 實作位置：bootstrap/providers.php
return [
    App\Providers\AppServiceProvider::class,         // 註冊 AppServiceProvider
    App\Providers\ComposerServiceProvider::class,    // 註冊 ComposerServiceProvider
];
```

- *註冊 Provider 的作用*

  - __集中管理__ 所有服務註冊邏輯，只要在 `provider 註冊清單`裡列出，Laravel 啟動時就會自動執行這些註冊。
  - 會 __自動 new 出這些 Provider 實例__，並呼叫它們的 `register()`（註冊服務）和 `boot()`（啟動副作用）方法。
  - 讓服務被註冊進 `Application 實例`（Service Container）後，__可以在任何地方 `type-hint` 或 `app()->make` 取得這些服務__。
  - 支援 __延遲載入（deferred loading）__，只有在需要時才載入特定 Provider，提升效能。

---

- *Provider 註冊 vs. 服務綁定的差異*

  - 上面這種寫法（`App\Providers\AppServiceProvider::class, ...`）是「_Service Provider 的註冊」_，代表 Laravel 啟動時會 `new` 這些 Provider 實例，並自動呼叫其 `register()/boot()` 方法，執行裡面的服務註冊邏輯。
    - __只要在 provider 清單裡，Laravel 啟動時就會 new 這個 Provider，不管你有沒有用到裡面註冊的服務__。

  - 你常看到的 `ServerProvider::class => ServerToolsProvider::class,` 這種「__服務綁定__」寫法，通常是寫在 *ServiceProvider 內部* 的 `$bindings` 屬性或 `register()` 方法。
    - 代表當你在程式中 `type-hint` 或解析 `ServerProvider::class` 時，Service Container 會自動 `new` 一個 __ServerToolsProvider 實例__ 給你。
    - 這是 *服務解析時的對應關係*，只有在實際需要 `ServerProvider::class` 時才會 `new ServerToolsProvider`。

  - 兩者層級不同： __Provider 註冊__ 是「註冊服務的工廠」，
                __服務綁定__ 是「遇到這個抽象，給你哪個實作」。

---

- *Provider 註冊清單與服務綁定語法的區別*
  - Provider 註冊清單（如 `App\Providers\AppServiceProvider::class, ...`）只會出現 __Provider 類別名稱__
  - 目的是告訴 Laravel __啟動時要 new 哪些 Service Provider 實例，並執行其 register/boot 方法__。

  - 不會出現 `ServerProvider::class => ServerToolsProvider::class,` 這種 __服務綁定語法__。
  - __服務綁定語法__（如 `ServerProvider::class => ServerToolsProvider::class,`）只會出現在 *ServiceProvider 內部*的 `$bindings` 屬性或 `register()` 方法裡，
  - 代表「__遇到這個抽象，給你哪個實作__」。

---

## 4. **延遲載入 Provider**（Deferred Providers）

若 provider *僅註冊（register）* container 綁定，可延遲載入以提升效能。
_因為 `boot` 方法會在 Provider 啟動時，就執行（即時載入）_。

需實作 `\Illuminate\Contracts\Support\DeferrableProvider` 並定義 `provides` 方法：

```php
// 實作位置：app/Providers/RiakServiceProvider.php
namespace App\Providers; // 宣告命名空間

use App\Services\Riak\Connection; // 匯入要註冊的服務類別
use Illuminate\Contracts\Foundation\Application; // 匯入 Application 介面，作為型別提示用
use Illuminate\Contracts\Support\DeferrableProvider; // 匯入延遲載入 Provider 需要實作的介面
use Illuminate\Support\ServiceProvider; // 匯入 ServiceProvider 基底類別

class RiakServiceProvider extends ServiceProvider implements DeferrableProvider // 定義一個延遲載入的 Service Provider
{
    public function register(): void // 覆寫 register 方法，註冊服務到容器
    {
        // 綁定 Connection::class 為單例，並指定如何產生
        $this->app->singleton(Connection::class, function (Application $app) {
            // 產生新的 Connection 實例，並傳入 riak 設定
            return new Connection($app['config']['riak']);
        });
    }
    public function provides(): array // 實作 provides 方法，回傳這個 Provider 提供的服務清單
    {
        return [Connection::class]; // 只有當解析 Connection::class 時才會載入這個 Provider
    }
}
``` 

<!-- 
當你在程式碼裡解析（取得）Connection::class 時，
Laravel 會自動判斷這個服務是由哪個 Provider 提供，
然後才載入並執行這個 Provider 的 register() 方法。
provides() 只是用來告訴 Laravel「這個 Provider 會提供哪些服務」，
啟用時機是解析服務時自動觸發，不用手動呼叫。 
-->

---

- __常見疑問說明__：

  - 很多人會覺得 `register` 跟 `boot` 好像都能寫註冊相關的程式，甚至內容很像，是不是都要重複寫？
  - 其實兩者分工明確：
    - *register* 只做「__服務綁定__」，不能依賴其他服務，不應有副作用。
    - *boot* 做「__副作用」或「初始化__」，可以依賴其他服務，適合`註冊事件、view composer、route`等。
  - 大多數情況下，`register` 和 boot 的內容完全不同，不會重複，只要依照用途分開寫即可。

```php
 // 實作位置：app/Providers/AppServiceProvider.php
 public function register()
 {
    // 在 register 綁定 FooService 到容器
    $this->app->singleton(FooService::class, function ($app) {
        return new FooService();
    });
 }

 public function boot()
 {
    // 在 boot 取得 FooService，並用來註冊事件監聽
    $foo = $this->app->make(FooService::class);
    Event::listen('user.registered', function ($event) use ($foo) {
        $foo->doSomething($event->user);
            });
 }
```
- *register*：只負責把 __服務綁定進容器__，不能直接用服務。
- *boot*：等所有 provider 都註冊好後，才可以安全地 __取出服務並做副作用操作__。

- 比喻：`register` 就像「_先把食材準備好放進冰箱_」；
       `boot` 就像「_等所有食材都備齊了，開始下廚做菜_」。

---

- __補充註解__

  - Service Provider 的 `register` 方法註冊的服務，實際上是`存進 Application 實例`（也就是 Service Container 實例）的 *內部屬性*（如 __bindings、instances__ 等），而`不是寫`進 Application 類別本身。
  - __每次請求都會產生一個`新的 Application 實例`，這些註冊的服務`只屬於`本次請求的 Application 實例。__
  - `Application 類別`只是 _定義容器的行為_ ，真正的服務資料都存在每個 __Application `實例的屬性`__ 中。 

---

- __實例 `register` 註冊與實例化服務的過程__

```php
 // 實作位置：app/Providers/AppServiceProvider.php
 public function register()
 {
    // 將 FooService 綁定到容器，並指定如何產生
    $this->app->singleton(FooService::class, function ($app) {
        return new FooService('bar');
    });
 }
```

- 上面這段程式碼，`singleton` 只是把「_如何產生 FooService_」的規則（closure）存進 Application 實例的 *bindings* 屬性，這時候並不會真的 `new FooService`。

```php
 // 實作位置：Controller 或其他地方 (boot、route 等)
 public function show(FooService $foo) // type-hint FooService
 {
    // 這裡第一次需要 FooService，Service Container 會根據之前 register 存的規則，
    // 執行 closure 並 new 出 FooService 實例，然後注入進來
    $foo->doSomething();
 }
``` 
  - 只有在實際「_解析_」這個服務時（如 *type-hint、app()->make()、resolve()*），__Service Container__ 才會根據 `register` 時存的規則產生物件。
  - 這就是「__延遲實例化__」的機制，註冊和實際產生物件是分開的。 

---

- __`register` 與 `Application` 生命週期的關係__

  - *register* 方法是在 Laravel Application 啟動流程中、很早的階段被呼叫，這時 Application 實例已經建立好。
  - register 只是把「__服務綁定規則__」存進 *Application 實例*，是啟動流程的一部分，*但本身不會 new 物件*。
  - 只有在你實際需要這個服務時（如 `type-hint、app()->make()`），Service Container 才會根據規則 new 出物件，這叫「_延遲實例化_」。 

---

- __Application 實例與 Service Provider 註冊的時序關係__

  - Laravel 啟動時，會 *「先」* new 一個 Application 實例（這個實例同時也是 Service Container）-- *空的* 。
  - 接著才會 *依序* `new` 出所有 Service Provider，並呼叫每個 provider 的 `register()` 方法，把服務綁定到這個 Application 實例裡。
  - 也就是說，`Application 實例`的生成 *不依賴* `Service Provider`，反而是 `Service Provider` 的註冊 *必須等 Application 實例化後* 才能進行。

---

- __Application、Service Container、Service Provider 的生成與註冊順序註解__

  - 1. `Application` 類別（`Illuminate\Foundation\Application`）本身就是 `Service Container`，因為它繼承自 `Illuminate\Container\Container`。

    - 也就是說，__Laravel 啟動時，第一步就是 `new` 一個 `Application 實例`，這個實例同時就是 Service Container 實例，兩者本質上是同一個物件，沒有兩份資料、沒有兩個容器。__

    - 這個 Application 實例一開始是 *「空的容器」* ，裡面沒有任何服務綁定。

  ---

  - 2. *Service Provider* 只是 「_把服務`註冊進`這個 Application/Service Container 實例_」 的工具。

    - Service Provider 本身 _不會產生_ Service Container，_也不會產生_ Application 實例。
    
    - 它的 `register()` 方法會把 「_服務綁定規則_」 存進 Application 實例的 __屬性（如 `bindings、instances` 等）__。

  ---

  - 3. *啟動流程順序*：

    - __先 new Application 實例__（同時是 Service Container 實例，內容一開始是*空的*）
    - __再載入 Service Provider 清單__（如 `config/app.php` 的 _providers 陣列_）
    - __依序 `new` 出每個 Service Provider 實例，並呼叫 `register()` 方法，把服務綁定規則存進 Application 實例__
    - 之後才會進入 __`boot` 階段、解析服務、處理請求等流程__

  ---

  - 4. *白話比喻*：

    - `Application/Service Container` 就像一個「_空的倉庫_」。
    - `Service Provider` 就像「_工人_」負責把各種物品（_服務綁定規則_）放進這個倉庫。
    - 倉庫（Application 實例）__一定要先蓋好__，工人才有地方放東西。
    - 工人（Service Provider）只是把東西放進倉庫，不會自己蓋倉庫。

  ---

  - 5. *流程圖*

    ```php
    *[Laravel 啟動]*
        ↓
    new Application 實例（同時是 Service Container，內容一開始是空的）
        ↓
    依序 new 出 Service Provider 實例，呼叫 register()，把服務綁定規則存進 Application 實例
        ↓
    進入 `boot` 階段、解析服務、處理請求等
    ```
  - __重點__：`Application` 實例（Service Container）一定是先有，`Service Provider` 只是把服務註冊進去，內容才會豐富起來。

---

- __Application 與 Service Container 的關係__

  - Application 實例（`Illuminate\Foundation\Application`）本身就是 Service Container（`Illuminate\Container\Container`）的子類別。

  - 所以 *「存進 Application 實例」＝「存進 Service Container 實例」＝「存進同一個物件」*；沒有兩份資料，也沒有兩個不同的容器。

  - Service Provider 的 *register()* 方法其實就是把服務綁定規則存進這個 `Application/Service Container` *實例的內部屬性*（如 __bindings、instances__ 等）。

  - `Service Provider` 的運作 _完全依賴於_ `Service Container`（也就是 Application 實例），兩者密不可分。

---

## **重要說明**

### *推薦的實作位置*

__主要位置：`app/Providers/` 目錄__
- 所有 *自訂的 Service Provider* 都 *放* 在這個目錄
- 檔案命名慣例：`{Name}ServiceProvider.php`
`AppServiceProvider.php`、`RiakServiceProvider.php`

__註冊位置：`bootstrap/providers.php`__
- 所有 *Service Provider* 都需要在這裡 *註冊*
- Laravel 會自動將新建立的 Provider 加入此檔案

__設定檔案：`config/` 目錄__
- Service Provider 中使用的 *設定檔案* 放在這裡
`config/riak.php` 用於 RiakServiceProvider

---

### *為什麼要使用 Service Provider？*

1. __生命週期管理__：在 Laravel `啟動時自動執行註冊和啟動流程`
2. __依賴管理__：可以正確處理 `類別之間` 的依賴關係
3. __可維護性__：所有服務註冊邏輯`集中在`一個地方
4. __Laravel 慣例__：符合 Laravel 的`架構設計原則`
5. __延遲載入__：支援延遲載入以提升效能

---

### *檔案結構範例*

```bash
app/
├── Providers/
│   ├── AppServiceProvider.php          # 主要的服務提供者
│   ├── RiakServiceProvider.php         # 自訂服務提供者
│   └── ComposerServiceProvider.php     # 視圖組合器提供者
├── Services/
│   └── Riak/
│       └── Connection.php              # 服務類別
config/
├── app.php                             # 應用程式設定
└── riak.php                            # Riak 設定
bootstrap/
└── providers.php                       # Provider 註冊清單
```

## __總結__

整個 Service Providers 筆記檔案中的所有內容，__主要實作位置都是 app/Providers/ 目錄下的 Service Provider 類別__。這些 Provider 需要在 `bootstrap/providers.php` 中註冊，這是 Laravel 官方推薦的做法，也是最符合 Laravel 架構設計的方式。
