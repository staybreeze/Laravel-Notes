# *Laravel Contracts 筆記*

---

## 1. **簡介**（Introduction）

Contracts 是一組 _interface_，定義 Laravel 核心服務的 *標準介面*（如 `queue、mail、cache` 等）。每個 contract 都有對應的 framework 實作，方便 `decoupling` 與 `package` 開發。

<!-- Contracts 是 Laravel 的介面規範，定義服務該有的功能，
     facade 則是服務容器物件的靜態代理，讓你用靜態方法快速存取服務。
     Contracts 強調「規範與可替換」，facade 強調「方便呼叫」。 -->

---

## 2. **Contracts vs. Facades**（差異比較）

- _Facade/Helper function_：簡單、`靜態語法，無需注入`
- _Contract_：`明確型別依賴`，利於測試與替換實作
- 多數 facade 有對應 contract

---

## 3. **何時使用 Contracts**（When to Use Contracts）

- 依團隊/個人偏好選擇 contract 或 facade
- `package` 開發建議用 contract（可 `decouple`，不需依賴 Laravel 實作）
- 專案內可混用 contract 與 facade，只要職責明確即可

---

## 4. **如何使用 Contracts**（How to Use Contracts）

大多數 Laravel 類別（`controller、listener、middleware、job、route closure`）都可 __type-hint contract__，`service container` 會自動注入對應實作。

---

**範例**

```php
namespace App\Listeners;

use App\Events\OrderWasPlaced;
use Illuminate\Contracts\Redis\Factory;

class CacheOrderInformation
{
    public function __construct(protected Factory $redis) {}
    public function handle(OrderWasPlaced $event): void
    {
        // ...
    }
}
```

---

## 5. **Contract 對照表**（Contract Reference）

| *Contract*                                        | *References Facade*              |
|---------------------------------------------------|-----------------------------------|
| `Illuminate\Contracts\Auth\Access\Authorizable`   |                                   |
| `Illuminate\Contracts\Auth\Access\Gate`           | Gate                              |
| `Illuminate\Contracts\Auth\Authenticatable`       |                                   |
| `Illuminate\Contracts\Auth\CanResetPassword`      |                                   |
| `Illuminate\Contracts\Auth\Factory`               | Auth                              |
| `Illuminate\Contracts\Auth\Guard`                 | Auth::guard()                    |
| `Illuminate\Contracts\Auth\PasswordBroker`        | Password::broker()               |
| `Illuminate\Contracts\Auth\PasswordBrokerFactory` | Password                         |
| `Illuminate\Contracts\Auth\StatefulGuard`         |                                   |
| `Illuminate\Contracts\Auth\SupportsBasicAuth`     |                                   |
| `Illuminate\Contracts\Auth\UserProvider`          |                                   |
| `Illuminate\Contracts\Broadcasting\Broadcaster`   | Broadcast::connection()          |
| `Illuminate\Contracts\Broadcasting\Factory`       | Broadcast                        |
| `Illuminate\Contracts\Broadcasting\ShouldBroadcast` |                                 |
| `Illuminate\Contracts\Broadcasting\ShouldBroadcastNow` |                              |
| `Illuminate\Contracts\Bus\Dispatcher`             | Bus                              |
| `Illuminate\Contracts\Bus\QueueingDispatcher`     | Bus::dispatchToQueue()           |
| `Illuminate\Contracts\Cache\Factory`              | Cache                            |
| `Illuminate\Contracts\Cache\Lock`                 |                                   |
| `Illuminate\Contracts\Cache\LockProvider`         |                                   |
| `Illuminate\Contracts\Cache\Repository`           | Cache::driver()                  |
| `Illuminate\Contracts\Cache\Store`                |                                   |
| `Illuminate\Contracts\Config\Repository`          | Config                           |
| `Illuminate\Contracts\Console\Application`        |                                   |
| `Illuminate\Contracts\Console\Kernel`            | Artisan                          |
| `Illuminate\Contracts\Container\Container`        | App                              |
| `Illuminate\Contracts\Cookie\Factory`             | Cookie                           |
| `Illuminate\Contracts\Cookie\QueueingFactory`     | Cookie::queue()                  |
| `Illuminate\Contracts\Database\ModelIdentifier`   |                                   |
| `Illuminate\Contracts\Debug\ExceptionHandler`     |                                   |
| `Illuminate\Contracts\Encryption\Encrypter`       | Crypt                            |
| `Illuminate\Contracts\Events\Dispatcher`          | Event                            |
| `Illuminate\Contracts\Filesystem\Cloud`           | Storage::cloud()                 |
| `Illuminate\Contracts\Filesystem\Factory`         | Storage                          |
| `Illuminate\Contracts\Filesystem\Filesystem`      | Storage::disk()                  |
| `Illuminate\Contracts\Foundation\Application`     | App                              |
| `Illuminate\Contracts\Hashing\Hasher`             | Hash                             |
| `Illuminate\Contracts\Http\Kernel`                |                                   |
| `Illuminate\Contracts\Mail\Mailable`              |                                   |
| `Illuminate\Contracts\Mail\Mailer`                | Mail                             |
| `Illuminate\Contracts\Mail\MailQueue`             | Mail::queue()                    |
| `Illuminate\Contracts\Notifications\Dispatcher`   | Notification                     |
| `Illuminate\Contracts\Notifications\Factory`      | Notification                     |
| `Illuminate\Contracts\Pagination\LengthAwarePaginator` |                              |
| `Illuminate\Contracts\Pagination\Paginator`       |                                   |
| `Illuminate\Contracts\Pipeline\Hub`               |                                   |
| `Illuminate\Contracts\Pipeline\Pipeline`          | Pipeline                         |
| `Illuminate\Contracts\Queue\EntityResolver`       |                                   |
| `Illuminate\Contracts\Queue\Factory`              | Queue                            |
| `Illuminate\Contracts\Queue\Job`                  |                                   |
| `Illuminate\Contracts\Queue\Monitor`              | Queue                            |
| `Illuminate\Contracts\Queue\Queue`                | Queue::connection()              |
| `Illuminate\Contracts\Queue\QueueableCollection`  |                                   |
| `Illuminate\Contracts\Queue\QueueableEntity`      |                                   |
| `Illuminate\Contracts\Queue\ShouldQueue`          |                                   |
| `Illuminate\Contracts\Redis\Factory`              | Redis                            |
| `Illuminate\Contracts\Routing\BindingRegistrar`   | Route                            |
| `Illuminate\Contracts\Routing\Registrar`          | Route                            |
| `Illuminate\Contracts\Routing\ResponseFactory`    | Response                         |
| `Illuminate\Contracts\Routing\UrlGenerator`       | URL                              |
| `Illuminate\Contracts\Routing\UrlRoutable`        |                                   |
| `Illuminate\Contracts\Session\Session`            | Session::driver()                |
| `Illuminate\Contracts\Support\Arrayable`          |                                   |
| `Illuminate\Contracts\Support\Htmlable`           |                                   |
| `Illuminate\Contracts\Support\Jsonable`           |                                   |
| `Illuminate\Contracts\Support\MessageBag`         |                                   |
| `Illuminate\Contracts\Support\MessageProvider`    |                                   |
| `Illuminate\Contracts\Support\Renderable`         |                                   |
| `Illuminate\Contracts\Support\Responsable`        |                                   |
| `Illuminate\Contracts\Translation\Loader`         |                                   |
| `Illuminate\Contracts\Translation\Translator`     | Lang                             |
| `Illuminate\Contracts\Validation\Factory`         | Validator                        |
| `Illuminate\Contracts\Validation\ValidatesWhenResolved` |                              |
| `Illuminate\Contracts\Validation\ValidationRule`  |                                   |
| `Illuminate\Contracts\Validation\Validator`       | Validator::make()                |
| `Illuminate\Contracts\View\Engine`                |                                   |
| `Illuminate\Contracts\View\Factory`               | View                             |
| `Illuminate\Contracts\View\View`                  | View::make()                     |

---

- `Contracts` 的主要目的是提供一組 __標準介面__（interface），用來定義 Laravel __核心功能__ 的行為。
- 它的核心價值在於 __解耦（decoupling）__ 和 __靈活性__，讓開發者可以更方便地 __替換實作__ 或 __進行測試__。

以下是更清晰的解釋：

- *Contracts 的用途*

  - __標準化行為__

    Contracts 定義了 Laravel 核心功能的標準介面，例如 Cache、Queue、Mail 等。
    `任何實作（例如 Redis、Memcached、SQS 等）都必須遵循這些介面，確保行為一致`。

  - __解耦__

    使用 Contracts 可以 *避免直接依賴* Laravel 的 *具體實作*（例如 `Redis` 或 `File Cache`）。
    `這樣可以更輕鬆地替換底層實作，而不需要修改使用這些功能的程式碼`。

  - __方便測試__

    Contracts 讓你可以輕鬆地 *替換實作* 為 `mock` 或 `stub`，進行`單元測試`，而不需要依賴實際的服務（例如 `Redis` 或 `Mail Server`）。
    
---

- *Contracts 的核心概念*

  - __介面（Interface）__

    Contracts 是一組介面，定義了 *功能的行為*（例如 `Illuminate\Contracts\Cache\Factory` 定義了 `Cache` 的行為）。
    介面本身不包含具體的邏輯，只是`規範`。

  - __實作（Implementation）__

    Laravel 提供了這些介面的`具體實作`（例如 _Redis Cache、File Cache_）。
    你也可以自行開發符合介面的實作，替換 Laravel 預設的功能。

---

- *Contracts 的使用場景*

  - __Package 開發__

    如果你在開發 Laravel 的`擴充套件（Package）`，使用 Contracts 可以 *避免直接依賴 Laravel 的具體實作*，讓你的套件更通用。

  - __專案開發__

    在專案中，使用 Contracts 可以讓程式碼更具彈性，`方便替換底層實作`（例如從 `File Cache` 切換到 `Redis Cache`）。
        
  - __測試__

    使用 Contracts 可以輕鬆替換實作為 `mock`，進行`單元測試`，而不需要依賴實際的服務。

---

- *範例：使用 Contracts*

  - __使用 Contracts 替代具體實作__

    ```php
    namespace App\Services;

    use Illuminate\Contracts\Cache\Factory as CacheFactory;

    class UserService
    {
        protected $cache;

        /**
        * 建構子，注入 CacheFactory Contract
        *
        * @param CacheFactory $cache
        */
        public function __construct(CacheFactory $cache)
        {
            $this->cache = $cache;
        }

        /**
        * 從 Cache 中取得使用者資料
        *
        * @param int $id 使用者 ID
        * @return mixed 使用者資料
        */
        public function getUser($id)
        {
            // 使用 Redis 作為 Cache 存儲，並取得指定的使用者資料
            return $this->cache->store('redis')->get("user_{$id}");
        }
    }
    ```

---

  - __測試時替換實作__

    ```php

    use Illuminate\Support\Facades\Cache;
    use Tests\TestCase;
    use App\Services\UserService;

    class UserServiceTest extends TestCase
    {
        /**
        * 測試從 Cache 中取得使用者資料
        */
        public function testGetUser()
        {
            // 使用 Mock 替代 Cache 的行為
            Cache::shouldReceive('store')
                   ->with('redis') // 模擬 store('redis') 方法
                   ->andReturnSelf(); // 返回自身，允許鏈式調用

            Cache::shouldReceive('get')
                   ->with('user_1') // 模擬 get('user_1') 方法
                   ->andReturn(['id' => 1, 'name' => 'John']); // 返回假資料

            // 注入 Mock 到 UserService
            $service = new UserService(Cache::getFacadeRoot());
            // 原始行為：
            // 在正常情況下，Cache::getFacadeRoot() 返回的是 Laravel 服務容器中綁定的 cache 服務（例如 Redis 或 File Cache）。

            // Mock 替換：
            // 在測試中，使用 Cache::shouldReceive() 替換 Facade 背後的物件為 Mock。
            // Mock 物件模擬 store() 和 get() 的行為。

            // getFacadeRoot() 返回 Mock：
            // 當你調用 Cache::getFacadeRoot() 時，返回的就是 Mock，而不是原始的 cache 服務。

            // 為什麼使用 getFacadeRoot()？
            // 目的：
            // 在測試中，getFacadeRoot() 用於取得 Facade 背後的物件（此時已被替換為 Mock）。
            // 與 shouldReceive() 的關係：
            // shouldReceive() 定義 Mock 的行為。
            // getFacadeRoot() 返回 Mock，讓你可以將 Mock 注入到其他類別（如 UserService）。

            // 測試 getUser 方法
            $user = $service->getUser(1);

            // 斷言結果是否正確
            $this->assertEquals(['id' => 1, 'name' => 'John'], $user);
        }
    }
    ```

- *總結*

    - Contracts 的主要目的是 __提供標準化的介面__，讓程式碼更具彈性、可測試性和可維護性。它適合用於需要`解耦`或`替換底層實作`的場景，例如 `Package` 開發或需要進行 `大量測試` 的專案。如果你的專案不需要這些特性，直接使用 `Facades` 也完全沒問題。

    - Contracts 提供了一個統一的介面，讓程式碼可以 __依據共同的方法名稱操作不同的實作__（例如 Redis、File Cache）。透過 Laravel 的 `Service Container`，Contracts 可以根據條件生成 `不同的實例`，實現解耦和靈活性。這樣的設計讓程式碼更具彈性、可維護性和可測試性。

    - Laravel 的 `Cache Contract`（如 `Illuminate\Contracts\Cache\Factory` 和 `Repository`） __提供統一的介面__，定義 __共同的方法名稱__（如 `get()、put()` 等），讓程式碼可以 __不依賴具體實作__（如 Redis 或 File Cache）進行操作。透過 `設定檔` 或 `Service Container`，Cache Contract 可 __動態生成不同的快取實例__，實現解耦、靈活替換和方便測試的特性。