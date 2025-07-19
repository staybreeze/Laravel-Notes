# Laravel Facades 筆記

## 1. *簡介*（Introduction）

Facade 提供「**靜態**」介面，實際為 **service container** 內物件的代理。
語法簡潔、易於測試，Laravel **幾乎所有功能皆有對應 facade**。

Facade 提供「靜態介面」，意思是：你可以用 Cache::get()、DB::table() 這種「**看起來像靜態方法**」的語法來操作 Laravel 的各種服務。
這裡的「介面」指的是 *「對外提供的操作方式」* 或 *「API 」* ，不是 PHP 的 interface 物件導向語法。

**Facade 的本質**
Laravel 的 Facade 實際上是一個「靜態代理類別」，透過 *__callStatic 魔術方法* (語法糖)，它會把你的 *靜態呼叫轉發給 Service Container 裡的實例物件* 。
例如：Cache::get() 其實會轉成 app('cache')->get()。
這讓你寫起來很簡潔，但底層依然保有 Laravel 的依賴注入、可測試性等優點。

> **真正的靜態方法定義：**
> ```php
> class MyClass {
>     public static function myStaticMethod() {
>         return '這是真正的靜態方法';
>     }
> }
> // 呼叫：MyClass::myStaticMethod()
> ```
> - 真正的靜態方法是在類別中直接用 `public static function` 定義的方法。
> - *不需要實例化類別*，直接透過類別名稱呼叫。
> - *不會有依賴注入*，因為沒有物件實例。

> **補Facade、靜態屬性與物件實例的狀態共用**
> - 如果 Facade 代理的物件是 *singleton（單例）*，所有地方都會共用同一個物件實例，狀態會同步、互相影響。
> - 如果是 PHP 類別的 *靜態屬性* ，則「同一個類別」的所有地方（不管是靜態呼叫還是物件呼叫）都共用同一份資料，誰改都會影響到其他地方。
>   - 例如：A::$foo = 2; 之後，所有 A 的物件和靜態呼叫看到的都是 2。
>     // 註解：A 類別的靜態屬性 $foo 是「類別共用」的，無論你用 A::$foo 或 (new A)->$foo，看到的都是同一份值。
>     // 範例：
>     // class A { public static $foo = 1; }
>     // A::$foo = 2;
>     // echo A::$foo; // 2
>     // echo (new A)->$foo; // 2
>   - 但不同類別的靜態屬性互不影響（A::$foo 和 B::$foo 各自獨立）。
>     // 註解：A::$foo 和 B::$foo 是完全不同的記憶體空間，互不干擾。
>     // 範例：
>     // class A { public static $foo = 1; }
>     // class B { public static $foo = 10; }
>     // A::$foo = 2;
>     // B::$foo = 20;
>     // echo A::$foo; // 2
>     // echo B::$foo; // 20
> - 如果 new 出不同的物件實例，則彼此之間的 *物件屬性* 不會互相影響，各自獨立。
>   - 例如：$a1 = new A(); $a2 = new A(); $a1->bar = 1; $a2->bar = 2; 彼此 bar 屬性互不影響。
> - 不過你 new 出多個物件實例，*靜態屬性（static property）*大家都共用同一份，誰改都會影響到所有地方。
>   - 這裡的「大家」指的是同一個類別的所有地方（不管是靜態呼叫還是物件呼叫），不同類別的靜態屬性互不影響。
>   // 註解：即使你 new 出多個 A 的物件，A::$foo 這個靜態屬性只有一份，所有物件和靜態呼叫都共用。
>   // 範例：
>   // class A { public static $foo = 1; }
>   // $a1 = new A();
>   // $a2 = new A();
>   // $a1::$foo = 5;
>   // echo $a2::$foo; // 5
>   // echo A::$foo;   // 5
>   // $a2::$foo = 9;
>   // echo $a1::$foo; // 9
>   // echo A::$foo;   // 9

```php
class A {
    public static $foo = 1;
}
class B {
    public static $foo = 9;
}

$a1 = new A();
$a2 = new A();

A::$foo = 5;
echo $a1::$foo; // 5
echo $a2::$foo; // 5
echo A::$foo;   // 5

B::$foo = 99;
echo B::$foo;   // 99
echo A::$foo;   // 5
```
> 你可以看到，A 的所有實例和靜態呼叫都共用同一份 $foo，B 的 $foo 則完全獨立。

> - 物件屬性（instance property）則是每個物件各自獨立，彼此不會互相影響。

> **簡明註解：**
> - 不同類別的靜態屬性互不影響，A::$foo 和 B::$foo 各自獨立。
> - 同個類別的靜態屬性，即使你 new 很多物件，看起來每個物件都獨立，實際上大家都共用同一份，誰改都會影響到所有地方。

```php
class Example {
    public static $staticProp = '靜態屬性';  // 類別層級，所有實例共用
    public $instanceProp = '物件屬性';       // 物件層級，每個實例獨立
}

$obj1 = new Example();
$obj2 = new Example();

// 修改靜態屬性 - 所有地方都會改變
Example::$staticProp = '新的靜態值';
echo $obj1::$staticProp; // '新的靜態值'
echo $obj2::$staticProp; // '新的靜態值'

// 修改物件屬性 - 只影響該物件
$obj1->instanceProp = 'obj1的物件屬性';
$obj2->instanceProp = 'obj2的物件屬性';
echo $obj1->instanceProp; // 'obj1的物件屬性'
echo $obj2->instanceProp; // 'obj2的物件屬性'
```

> - **靜態屬性**和**物件屬性**是完全不同的記憶體空間：
>   - **靜態屬性** 存在 *類別層級* 的記憶體空間，所有實例都指向同一份資料
>     - 當 PHP 載入類別時，靜態屬性就會在記憶體中分配一塊空間
>     - 這塊空間屬於類別本身，不屬於任何物件實例
>     - 所有透過該類別建立的物件實例都會共用這塊記憶體空間
>   - **物件屬性** 存在 *物件實例層級* 的記憶體空間，每個物件都有自己獨立的副本
>     - 每次 `new` 建立物件時，PHP 會在記憶體中分配一塊新的空間給該物件
>     - 這塊空間只屬於該特定物件實例
>     - 不同物件實例的屬性存在不同的記憶體位置，彼此完全獨立
>   - 兩者不會互相包含或干擾，是完全獨立的兩個空間

```php
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/cache', function () {
    return Cache::get('key');
});
```

---

## 2. *Helper Functions*（輔助函式）

Laravel 也提供大量**全域 helper function**（如 view、response、url、config 等），可直接呼叫，**無需 import class**。

```php
// 引入 Response Facade 類別
use Illuminate\Support\Facades\Response;

// 使用 Facade 方式：透過 Response 類別呼叫 json 方法
Route::get('/users', function () {
    return Response::json([/* ... */]);  // Response::json() 透過 __callStatic 魔術方法，實際會轉發給 Service Container 中的 response 服務
});

// 使用 Helper Function 方式：直接呼叫 response() 函式
Route::get('/users', function () {
    return response()->json([/* ... */]);  // response() 是全域函式，內部會呼叫 Response Facade
});
```

---

## 3. *何時使用 Facade*（When to Utilize Facades）

- 語法簡潔、易記
- 測試時可 **mock**
- 注意：過度使用易造成 class 過大，建議注意職責分離

---

## 4. *Facade vs. Dependency Injection*（與依賴注入比較）

### **Facade 寫法**
```php
// 引入 Cache Facade
use Illuminate\Support\Facades\Cache;

// 定義一個 /cache 路由
Route::get('/cache', function () {
    // 直接用 Facade 靜態呼叫取得快取內容
    return Cache::get('key');
});
```
- **優點**：寫法簡單、快速。
- **缺點**：測試時要用 Facade 的 mock 工具，耦合度較高。

#### *Facade 測試 mock 範例*
> - mock（模擬）就是在測試時「假裝」某個方法回傳你想要的結果，不會真的執行原本的邏輯。
> - mock Facade 就是讓 Facade（如 Cache、Log、DB 等）的靜態方法，在測試時回傳你指定的值，而不是去存取真實的外部資源。
> - 讓 Facade（例如 Cache）的某些方法（如 get）不要真的去存取資料庫、Redis、檔案系統，而是「假裝」回傳你指定的值。
> - 這樣可以讓測試更快、更穩定，也能測試各種情境。

```php
// 引入 Cache Facade
use Illuminate\Support\Facades\Cache;

// 設定 Cache Facade 的 mock 行為，假裝 get('key') 會回傳 'value'
Cache::shouldReceive('get')
    ->with('key')
    ->andReturn('value');

// 執行 HTTP 請求，觸發 /cache 路由
$response = $this->get('/cache');
// 驗證回應內容，確保 mock 有生效
$response->assertSee('value');
```
- **說明**：這是單元測試時，如何「假裝」Cache::get('key') 回傳你想要的值，不會真的去存取 Redis 或檔案。

---

### **依賴注入寫法**
```php
// 引入 CacheRepository 介面
use Illuminate\Contracts\Cache\Repository as CacheRepository;

// 定義一個 /cache 路由，並透過依賴注入取得 cache 實例
Route::get('/cache', function (CacheRepository $cache) {
    // 透過依賴注入取得 cache 實例，取得快取內容
    return $cache->get('key');
});
```
- **優點**：更彈性、可替換性高，測試時可直接注入 mock 物件。
- **缺點**：寫法稍微多一點，但更明確。

#### *依賴注入測試 mock 範例*
```php
// 引入 CacheRepository 介面
use Illuminate\Contracts\Cache\Repository as CacheRepository;
// 引入 Mockery
use Mockery;

// 建立一個假的 CacheRepository 物件
$mock = Mockery::mock(CacheRepository::class);
// 設定 mock 行為，get('key') 會回傳 'value'
$mock->shouldReceive('get')->with('key')->andReturn('value');
// 把 mock 物件注入 Service Container，之後解析 CacheRepository 會拿到這個 mock
$this->app->instance(CacheRepository::class, $mock);

// 執行 HTTP 請求，觸發 /cache 路由
$response = $this->get('/cache');
// 驗證回應內容，確保 mock 有生效
$response->assertSee('value');
```
- **說明**：這是單元測試時，直接把假的 CacheRepository 注入 Service Container，測試更直觀。

> **補充說明：**
> - Facade 寫法主程式碼完全看不到 mock，mock 只會出現在測試時（用 shouldReceive... 來攔截靜態呼叫）。
> - 依賴注入寫法 mock 很明確，直接在測試時用 $this->app->instance(...) 注入假的物件。
> - 這是兩種寫法在測試時的最大差異之一：Facade mock 是「隱式」的，DI mock 是「顯式」的。
---

### *比較重點總結*

| 方式         | 開發便利性 | 可測試性 | 彈性 | 適合場合         |
|--------------|------------|----------|------|------------------|
| Facade       | 高         | 需 mock  | 較低 | 小專案/快速開發  |
| 依賴注入     | 中         | 最高     | 高   | 大型/可維護專案  |

> **重點：**
> - Facade 靜態呼叫簡單，適合快速開發，但測試時要 mock Facade。
> - 依賴注入更明確、可替換性高，測試時可直接注入 mock 物件，維護性較佳。

---

## 5. *Facade vs. Helper Functions*（與輔助函式比較）

- helper function 與 facade **實質等價**
- 測試 helper function 也可 mock 對應 facade

```php
// 兩種方式實質等價，選擇哪種主要看團隊習慣
return Illuminate\Support\Facades\View::make('profile');  // 完整路徑，明確顯示這是 Facade。直接使用 View Facade 的 make 方法。
return view('profile');  // Helper 函式，語法更簡潔。view() 是全域函式，內部會呼叫 View Facade。

// Facade 的優勢：可以 mock 測試，因為底層是 Service Container 綁定
use Illuminate\Support\Facades\Cache;  // 引入 Cache Facade 用於測試
Cache::shouldReceive('get')            // 測試時可以替換 Service Container 中的 cache 實例。設定 mock：當呼叫 get 方法時。
    ->with('key')                      // 驗證呼叫參數。參數是 'key'。
    ->andReturn('value');              // 設定回傳值，避免實際呼叫外部服務。回傳 'value'。
```

---

## 6. *Facade 運作原理*（How Facades Work）

Facade 實際為 `Illuminate\Support\Facades\Facade` 子類，透過 `__callStatic` 代理靜態呼叫至 container 物件。

```php
// Cache Facade 類別定義：繼承自基礎 Facade 類別
// Facade 的核心機制：透過 getFacadeAccessor() 定義要代理的 Service Container 綁定
class Cache extends Facade
{
    // 定義這個 Facade 要代理的 Service Container 綁定名稱
    protected static function getFacadeAccessor(): string
    {
        return 'cache';  // 關鍵：告訴 Facade 要從 Service Container 中取得 'cache' 綁定。當呼叫 Cache::method() 時，實際會呼叫 app('cache')->method()。
    }
}
// 當呼叫 Cache::get('key') 時，實際執行流程：
// 1. Facade 的 __callStatic 魔術方法被觸發
// 2. 呼叫 getFacadeAccessor() 取得 'cache'
// 3. 從 Service Container 中解析 app('cache')
// 4. 呼叫該實例的 get('key') 方法
```

呼叫 `Cache::get()`，實際會解析 container 內 `cache` 綁定並呼叫其 `get` 方法。

---

## 7. *Real-Time Facades*（即時 Facade）

可將任意 class 以 `Facades\` 前綴 **即時轉為 facade**，*方便測試與 mock*。

```php

// Real-Time Facade 的魔法：Laravel 會動態建立代理類別
use Facades\App\Contracts\Publisher;  // 加上 Facades\ 前綴，Laravel 自動建立 Facades\App\Contracts\Publisher 類別

// 設定 mock：Publisher Facade 的 publish 方法應該被呼叫一次，參數是 $podcast
// 測試優勢：不需要修改原始類別，就能 mock 任何類別
Publisher::shouldReceive('publish')    // 動態建立的 Facade 也支援 mock 測試。當呼叫 publish 方法時。
    ->once()                           // 驗證方法被呼叫的次數。應該被呼叫一次。
    ->with($podcast);                  // 驗證呼叫參（$podcast）。

$podcast->publish();                   // 實際執行時，會透過動態建立的 Facade 代理。這會觸發 Publisher::publish($podcast) 的呼叫。
// 原理：Laravel 的 ClassLoader 會攔截 Facades\ 開頭的類別，動態建立代理類別
```

---

## 8. *Facade Class 對照表*（Facade Class Reference）

| Facade         | Class                                    | Service Container Binding         |
|----------------|------------------------------------------|-----------------------------------|
| App            | Illuminate\Foundation\Application        | app                               |
| Artisan        | Illuminate\Contracts\Console\Kernel      | artisan                           |
| Auth (Instance)| Illuminate\Contracts\Auth\Guard         | auth.driver                       |
| Auth           | Illuminate\Auth\AuthManager              | auth                              |
| Blade          | Illuminate\View\Compilers\BladeCompiler | blade.compiler                    |
| Broadcast      | Illuminate\Contracts\Broadcasting\Factory|                                   |
| Bus            | Illuminate\Contracts\Bus\Dispatcher      |                                   |
| Cache (Instance)| Illuminate\Cache\Repository             | cache.store                       |
| Cache          | Illuminate\Cache\CacheManager            | cache                             |
| Config         | Illuminate\Config\Repository             | config                            |
| Context        | Illuminate\Log\Context\Repository        |                                   |
| Cookie         | Illuminate\Cookie\CookieJar              | cookie                            |
| Crypt          | Illuminate\Encryption\Encrypter          | encrypter                         |
| Date           | Illuminate\Support\DateFactory           | date                              |
| DB (Instance)  | Illuminate\Database\Connection           | db.connection                     |
| DB             | Illuminate\Database\DatabaseManager      | db                                |
| Event          | Illuminate\Events\Dispatcher             | events                            |
| Exceptions     | Illuminate\Foundation\Exceptions\Handler |                                   |
| File           | Illuminate\Filesystem\Filesystem         | files                             |
| Gate           | Illuminate\Contracts\Auth\Access\Gate   |                                   |
| Hash           | Illuminate\Contracts\Hashing\Hasher      | hash                              |
| Http           | Illuminate\Http\Client\Factory           |                                   |
| Lang           | Illuminate\Translation\Translator        | translator                        |
| Log            | Illuminate\Log\LogManager                | log                               |
| Mail           | Illuminate\Mail\Mailer                   | mailer                            |
| Notification   | Illuminate\Notifications\ChannelManager  |                                   |
| Password       | Illuminate\Auth\Passwords\PasswordBrokerManager | auth.password           |
| Pipeline       | Illuminate\Pipeline\Pipeline             |                                   |
| Process        | Illuminate\Process\Factory               |                                   |
| Queue          | Illuminate\Queue\QueueManager            | queue                             |
| RateLimiter    | Illuminate\Cache\RateLimiter             |                                   |
| Redirect       | Illuminate\Routing\Redirector            | redirect                          |
| Redis          | Illuminate\Redis\RedisManager            | redis                             |
| Request        | Illuminate\Http\Request                  | request                           |
| Response       | Illuminate\Contracts\Routing\ResponseFactory |                           |
| Route          | Illuminate\Routing\Router                | router                            |
| Schedule       | Illuminate\Console\Scheduling\Schedule   |                                   |
| Schema         | Illuminate\Database\Schema\Builder       |                                   |
| Session        | Illuminate\Session\SessionManager        | session                           |
| Storage        | Illuminate\Filesystem\FilesystemManager  | filesystem                        |
| URL            | Illuminate\Routing\UrlGenerator          | url                               |
| Validator      | Illuminate\Validation\Factory            | validator                         |
| View           | Illuminate\View\Factory                  | view                              |
| Vite           | Illuminate\Foundation\Vite               |                                   | 