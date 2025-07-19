# Laravel Service Providers 筆記

## 1. *簡介*（Introduction）

Service Provider 是 Laravel 應用啟動的核心。
所有服務（包含核心 mailer、queue、cache 等）皆透過 provider 啟動與註冊。Provider 主要負責：
- 註冊 **service container** 綁定
- 註冊 **事件監聽**、**middleware**、**route** 等

部分 provider 為「**延遲載入**」（deferred），僅在實際需要時才載入。

**實作位置**： 所有自訂 provider 註冊於 `bootstrap/providers.php`。

---

## 2. *撰寫 Service Provider*（Writing Service Providers）

所有 provider 需繼承 `Illuminate\Support\ServiceProvider`，通常包含 `register` 與 `boot` 方法。

### 2.1 **Artisan 產生 provider**

```bash
# 實作位置：終端機命令
php artisan make:provider RiakServiceProvider
```

**實作位置**： Laravel 會自動將新 provider 加入 `bootstrap/providers.php`。

### 2.2 **register 方法**

僅用於 *service container* 綁定，不可註冊事件、route 等（避免依賴尚未載入的服務）。

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
            // config('riak') 是 Laravel 的全域輔助函式，會回傳 config/riak.php 的內容
            // config('riak') 只會讀取設定檔並回傳內容，不會寫入或新增資料
            // config() 其實底層也是呼叫 app('config')->get('riak')。

            // 補充：也可以寫成 return new Connection($app['config']['riak']);
            // 這是透過 Service Container 取得 config 服務（ConfigRepository），再取出 riak 設定。
            // $app['config'] 其實就是 app()->make('config')，是一個 ConfigRepository 物件。
            // $app['config']['riak'] 會回傳 config/riak.php 的內容。
            // 兩種寫法取得的資料完全一樣

            // 推薦用 $app['config']['riak'] 的原因：
            // 在 ServiceProvider 的 closure 內，推薦用 $app['config']['riak']，因為這樣依賴都來自容器，未來測試或重構更彈性，更符合依賴注入與可測試性原則
            // config('riak') 雖然方便，但會直接依賴全域函式，對於測試、解耦合稍微不利
        });
    }
}
```

### 2.3 **bindings / singletons 屬性**

可用 *public* 屬性快速註冊多個 binding/singleton：

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

### 2.4 **boot 方法**

用於「*啟動*」事件監聽、view composer、route 等副作用操作，並保證此時所有 provider 都已註冊完畢、所有服務都可安全使用。

> **補充說明：**
> - `register` 方法是在 Laravel 啟動時「最先」呼叫，用來**註冊服務到 Service Container**，只做綁定，不做副作用。
> - `boot` 方法則是在「所有 provider 都註冊完畢」後才會呼叫，這時候所有服務都已經可以安全使用，適合做**事件監聽、view composer、route 註冊等副作用操作**。
> - 所以 `register` 是「註冊」服務，`boot` 則是「啟動」或「初始化」需要依賴其他服務的行為。

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

#### 2.4.1 *boot 方法依賴注入*

可 **type-hint** 依賴，容器自動注入：

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

> **說明：boot 方法依賴注入可以幹嘛？**
> - 讓 boot 方法可以像 Controller、建構子一樣，直接 type-hint 需要的服務，Laravel 會自動注入，*不用手動 app()->make()*。
> - 常用於註冊事件、macro、view composer、route 等時，*直接取得需要的服務物件*（如 ResponseFactory、Event Dispatcher、Router...）。
> - 優點：
>   - 自動依賴注入，減少樣板程式碼。
>   - 依賴關係一目了然，維護更容易。
>   - 開發體驗一致，和 Controller、建構子注入一樣直覺。
> - 範例：
>   ```php
>   // 實作位置：app/Providers/AppServiceProvider.php
>   // 依賴注入寫法（推薦）
>   public function boot(ResponseFactory $response) {
>       // $response 會自動注入，不用自己 app()->make(...)
>   }
>   // 傳統寫法（未用依賴注入）
>   public function boot() {
>       $response = app()->make(ResponseFactory::class); // 需手動解析服務
>   }
>   ```

---

## 3. *註冊 Provider*（Registering Providers）

所有 provider 註冊於 `bootstrap/providers.php`，內容為 **provider class 陣列**：

```php
// 實作位置：bootstrap/providers.php
return [
    App\Providers\AppServiceProvider::class,         // 註冊 AppServiceProvider
    App\Providers\ComposerServiceProvider::class,    // 註冊 ComposerServiceProvider
];
```

> **說明：註冊 Provider 的作用**
> - 集中管理所有服務註冊邏輯，只要在 provider 註冊清單裡列出，Laravel 啟動時就會自動執行這些註冊。
> - 會自動 new 出這些 Provider 實例，並呼叫它們的 register()（註冊服務）和 boot()（啟動副作用）方法。
> - 讓服務被註冊進 Application 實例（Service Container）後，可以在任何地方 type-hint 或 app()->make 取得這些服務。
> - 支援延遲載入（deferred loading），只有在需要時才載入特定 Provider，提升效能。

> **補充：Provider 註冊 vs. 服務綁定的差異**
> - 上面這種寫法（App\Providers\AppServiceProvider::class, ...）是「Service Provider 的註冊」，代表 Laravel 啟動時會 new 這些 Provider 實例，並自動呼叫其 register()/boot() 方法，執行裡面的服務註冊邏輯。
>   - *只要在 provider 清單裡，Laravel 啟動時就會 new 這個 Provider，不管你有沒有用到裡面註冊的服務*。
> 
> - 你常看到的 `ServerProvider::class => ServerToolsProvider::class,` 這種「服務綁定」寫法，通常是寫在 ServiceProvider 內部的 `$bindings` 屬性或 `register()` 方法裡，
>   代表當你在程式中 type-hint 或解析 ServerProvider::class 時，Service Container 會自動 new 一個 ServerToolsProvider 實例給你。
>   - 這是服務解析時的對應關係，只有在實際需要 ServerProvider::class 時才會 new ServerToolsProvider。
> 
> - 兩者層級不同：Provider 註冊是「註冊服務的工廠」，服務綁定是「遇到這個抽象，給你哪個實作」。

> **補充：Provider 註冊清單與服務綁定語法的區別**
> - Provider 註冊清單（如 `App\Providers\AppServiceProvider::class, ...`）只會出現 Provider 類別名稱，
>   目的是告訴 Laravel 啟動時要 new 哪些 Service Provider 實例，並執行其 register/boot 方法。
> - 不會出現 `ServerProvider::class => ServerToolsProvider::class,` 這種服務綁定語法。
> - 服務綁定語法（如 `ServerProvider::class => ServerToolsProvider::class,`）只會出現在 ServiceProvider 內部的 `$bindings` 屬性或 `register()` 方法裡，
>   代表「遇到這個抽象，給你哪個實作」。

---

## 4. *延遲載入 Provider*（Deferred Providers）

若 provider **僅註冊** container 綁定，可延遲載入以提升效能。
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

> **常見疑問說明：**
> - 很多人會覺得 register 跟 boot 好像都能寫註冊相關的程式，甚至內容很像，是不是都要重複寫？
> - 其實兩者分工明確：
>   - **register** 只做「服務綁定」，不能依賴其他服務，不應有副作用。
>   - **boot** 做「副作用」或「初始化」，可以依賴其他服務，適合註冊事件、view composer、route 等。
> - 大多數情況下，register 和 boot 的內容完全不同，不會重複，只要依照用途分開寫即可。

>  - 例如：
> ```php
> // 實作位置：app/Providers/AppServiceProvider.php
> public function register()
> {
>     // 在 register 綁定 FooService 到容器
>     $this->app->singleton(FooService::class, function ($app) {
>         return new FooService();
>     });
> }
>
> public function boot()
> {
>     // 在 boot 取得 FooService，並用來註冊事件監聽
>     $foo = $this->app->make(FooService::class);
>     Event::listen('user.registered', function ($event) use ($foo) {
>         $foo->doSomething($event->user);
>     });
> }
> ```

> - register：只負責把服務綁定進容器，不能直接用服務。
> - boot：等所有 provider 都註冊好後，才可以安全地取出服務並做副作用操作。
> 
> **比喻：register 就像「先把食材準備好放進冰箱」；boot 就像「等所有食材都備齊了，開始下廚做菜」。** 

> **補充註解：**
> - Service Provider 的 register 方法註冊的服務，實際上是存進 Application 實例（也就是 Service Container 實例）的 *內部屬性*（如 bindings、instances 等），而不是寫進 Application 類別本身。
> - 每次請求都會產生一個新的 Application 實例，這些註冊的服務只屬於本次請求的 Application 實例。
> - Application 類別只是定義容器的行為，真正的服務資料都存在每個 Application 實例的屬性中。 

> **實例說明：register 註冊與實例化服務的過程**
> 
> ```php
> // 實作位置：app/Providers/AppServiceProvider.php
> public function register()
> {
>     // 將 FooService 綁定到容器，並指定如何產生
>     $this->app->singleton(FooService::class, function ($app) {
>         return new FooService('bar');
>     });
> }
> ```
> 
> - 上面這段程式碼，`singleton` 只是把「如何產生 FooService」的規則（closure）存進 Application 實例的 *bindings* 屬性，這時候並不會真的 new FooService。
> 
> ```php
> // 實作位置：Controller 或其他地方 (boot、route 等)
> public function show(FooService $foo) // type-hint FooService
> {
>     // 這裡第一次需要 FooService，Service Container 會根據之前 register 存的規則，
>     // 執行 closure 並 new 出 FooService 實例，然後注入進來
>     $foo->doSomething();
> }
> ```
> 
> - 只有在實際「*解析*」這個服務時（如 *type-hint、app()->make()、resolve()*），
>   *Service Container* 才會根據 register 時存的規則產生物件。
> - 這就是「**延遲實例化**」的機制，註冊和實際產生物件是分開的。 

> **補充註解：register 與 Application 生命週期的關係**
> - *register* 方法是在 Laravel Application 啟動流程中、很早的階段被呼叫，這時 Application 實例已經建立好。
> - register 只是把「服務綁定規則」存進 *Application 實例*，是啟動流程的一部分，但本身不會 new 物件。
> - 只有在你實際需要這個服務時（如 type-hint、app()->make()），Service Container 才會根據規則 new 出物件，這叫「延遲實例化」。 

> **補充註解：Application 實例與 Service Provider 註冊的時序關係**
> - Laravel 啟動時，會 *「先」* new 一個 Application 實例（這個實例同時也是 Service Container）-> *空的* 。
> - 接著才會 *依序* new 出所有 Service Provider，並呼叫每個 provider 的 register() 方法，把服務綁定到這個 Application 實例裡。
> - 也就是說，Application 實例的生成 **不依賴** Service Provider，反而是 Service Provider 的註冊 **必須等 Application 實例化後** 才能進行。

> **補充註解：Application、Service Container、Service Provider 的生成與註冊順序註解**
>
> 1. `Application` 類別（`Illuminate\Foundation\Application`）本身就是 Service Container，因為它繼承自 `Illuminate\Container\Container`。
>    - 也就是說，**Laravel 啟動時，第一步就是 new 一個 Application 實例，這個實例同時就是 Service Container 實例，兩者本質上是同一個物件，沒有兩份資料、沒有兩個容器。**
>    - 這個 Application 實例一開始是 *「空的容器」* ，裡面沒有任何服務綁定。

> 2. **Service Provider** 只是 *「把服務註冊進這個 Application/Service Container 實例」* 的工具。
>    - Service Provider 本身不會產生 Service Container，也不會產生 Application 實例。
>    - 它的 register() 方法會把 *「服務綁定規則」* 存進 Application 實例的 *屬性（如 bindings、instances 等）*。
> 
> 3. **啟動流程順序**：
>    - *先 new Application 實例*（同時是 Service Container 實例，內容一開始是*空的*）
>    - *再載入 Service Provider 清單*（如 config/app.php 的 providers 陣列）
>    - 依*序 new 出每個 Service Provider 實例，並呼叫 register() 方法，把服務綁定規則存進 Application 實例*
>    - 之後才會進入 *boot 階段、解析服務、處理請求等流程*
> 
> 4. **白話比喻**：
>    - Application/Service Container 就像一個「空的倉庫」。
>    - Service Provider 就像「工人」負責把各種物品（服務綁定規則）放進這個倉庫。
>    - 倉庫（Application 實例）**一定要先蓋好**，工人才有地方放東西。
>    - 工人（Service Provider）只是把東西放進倉庫，不會自己蓋倉庫。
> 
> 5. **流程圖（純文字版）**：
> 
> ```
> *[Laravel 啟動]*
>     ↓
> new Application 實例（同時是 Service Container，內容一開始是空的）
>     ↓
> 依序 new 出 Service Provider 實例，呼叫 register()，把服務綁定規則存進 Application 實例
>     ↓
> 進入 boot 階段、解析服務、處理請求等
> ```
> 
> - **重點：Application 實例（Service Container）一定是先有，Service Provider 只是把服務註冊進去，內容才會豐富起來。**

> **補充註解：Application 與 Service Container 的關係**
> - Application 實例（Illuminate\Foundation\Application）本身就是 Service Container（Illuminate\Container\Container）的子類別。
> - 所以 *「存進 Application 實例」＝「存進 Service Container 實例」＝「存進同一個物件」*；沒有兩份資料，也沒有兩個不同的容器。
> - Service Provider 的 *register()* 方法其實就是把服務綁定規則存進這個 Application/Service Container *實例的內部屬性*（如 bindings、instances 等）。
> - Service Provider 的運作完全依賴於 Service Container（也就是 Application 實例），兩者密不可分。

## 重要說明

### 推薦的實作位置

**主要位置：app/Providers/ 目錄**
- 所有 *自訂的 Service Provider* 都 *放* 在這個目錄
- 檔案命名慣例：`{Name}ServiceProvider.php`
- 例如：`AppServiceProvider.php`、`RiakServiceProvider.php`

**註冊位置：bootstrap/providers.php**
- 所有 *Service Provider* 都需要在這裡 *註冊*
- Laravel 會自動將新建立的 Provider 加入此檔案

**設定檔案：config/ 目錄**
- Service Provider 中使用的 *設定檔案* 放在這裡
- 例如：`config/riak.php` 用於 RiakServiceProvider

### *為什麼要使用 Service Provider？*

1. **生命週期管理**：在 Laravel 啟動時自動執行註冊和啟動流程
2. **依賴管理**：可以正確處理 *類別之間* 的依賴關係
3. **可維護性**：所有服務註冊邏輯集中在一個地方
4. **Laravel 慣例**：符合 Laravel 的架構設計原則
5. **延遲載入**：支援延遲載入以提升效能

### *檔案結構範例*

```
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

## 總結

整個 Service Providers 筆記檔案中的所有內容，**主要實作位置都是 app/Providers/ 目錄下的 Service Provider 類別**。這些 Provider 需要在 `bootstrap/providers.php` 中註冊，這是 Laravel 官方推薦的做法，也是最符合 Laravel 架構設計的方式。
