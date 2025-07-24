# *aravel Events 事件系統完整筆記*

---

## 1. **事件系統簡介**

Laravel 的事件（Events）提供了簡單的*觀察者模式（Observer Pattern）*實作，讓你可以「訂閱」與「監聽」應用程式中發生的各種事件。

- **事件類別** 通常放在 `app/Events` 目錄下。
- **監聽器（Listener）類別** 通常放在 `app/Listeners` 目錄下。
- 如果這些目錄不存在，當你用 Artisan 指令產生事件或監聽器時，Laravel 會自動建立。

### *事件的用途*

事件可以讓你的程式碼「**解耦**」，例如：
- 當訂單出貨時，你想通知用戶（如發 Slack 通知），可以發出一個 `App\Events\OrderShipped` 事件，讓監聽器去處理通知，而**不用把通知邏輯寫在訂單流程裡**。

---

## 2. **產生事件與監聽器**

### 2.1 *產生事件類別*

// 終端機指令
```bash
php artisan make:event PodcastProcessed
```
- 會在 `app/Events/PodcastProcessed.php` 產生事件類別。

// app/Events/PodcastProcessed.php
```php


namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PodcastProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 建構子：可在這裡傳入事件相關資料
     */
    public function __construct()
    {
        //
    }

    /**
     * 若要廣播事件，可自訂頻道
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
```

---

### 2.2 *產生監聽器類別*

// 終端機指令
```bash
php artisan make:listener SendPodcastNotification --event=PodcastProcessed
```
- 會在 `app/Listeners/SendPodcastNotification.php` 產生監聽器類別，並自動綁定到 `PodcastProcessed` 事件。

// app/Listeners/SendPodcastNotification.php
```php


namespace App\Listeners;

use App\Events\PodcastProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPodcastNotification
{
    /**
     * 建構子
     */
    public function __construct()
    {
        //
    }

    /**
     * 處理事件
     */
    public function handle(PodcastProcessed $event): void
    {
        // 在這裡撰寫收到事件後要執行的邏輯
    }
}
```

---

### 2.3 *互動式產生（可省略參數，Laravel 會互動詢問）*

// 終端機指令
```bash
php artisan make:event
php artisan make:listener
```
- 不加參數時，Laravel 會互動詢問你要建立的事件或監聽器名稱，以及監聽器要綁定哪個事件。

---

## 3. **事件與監聽器的註冊**

Laravel 會自動掃描 `app/Listeners` 目錄下的監聽器，並根據監聽器的 `handle` 方法型別提示自動綁定事件。
如果要手動註冊或自訂事件與監聽器的對應關係，可在 `app/Providers/EventServiceProvider.php` 設定：

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    \App\Events\PodcastProcessed::class => [
        \App\Listeners\SendPodcastNotification::class,
    ],
];
```

---

### 3.1 *事件自動發現（Event Discovery）與 union types*

Laravel 會自動註冊 `app/Listeners` 目錄下所有監聽器，當監聽器的 `handle` 或 `__invoke` 方法型別提示對應事件時，自動完成註冊。

// app/Listeners/SendPodcastNotification.php
```php
namespace App\Listeners;

use App\Events\PodcastProcessed;

class SendPodcastNotification
{
    /**
     * 處理事件
     */
    public function handle(PodcastProcessed $event): void
    {
        // 處理事件邏輯
    }
}
```

#### **監聽多個事件（PHP 8+ union types）**
```php
public function handle(PodcastProcessed|PodcastPublished $event): void
{
    // 可同時監聽多個事件
}
```

---

### 3.2 *多目錄自動發現（withEvents）*

如果監聽器分散在多個目錄，可在 `bootstrap/app.php` 用 `withEvents` 指定：

// bootstrap/app.php
```php
->withEvents(discover: [
    __DIR__.'/../app/Domain/Orders/Listeners',
])
```
- 支援萬用字元 `*`：
```php
->withEvents(discover: [
    __DIR__.'/../app/Domain/*/Listeners',
])
```

---

### 3.3 *查看所有已註冊監聽器*

// 終端機指令
```bash
php artisan event:list
```
- 可列出所有已註冊的事件與監聽器對應關係。

---

### 3.4 *事件註冊快取與清除*

// 終端機指令
```bash
php artisan event:cache
# 或
php artisan optimize
```
- 快取檔案會加速事件註冊。
- 若需清除快取：
```bash
php artisan event:clear
```

---

### 3.5 *手動註冊事件與監聽器*


```php
// app/Providers/AppServiceProvider.php

use App\Domain\Orders\Events\PodcastProcessed;
use App\Domain\Orders\Listeners\SendPodcastNotification;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    Event::listen(
        PodcastProcessed::class,
        SendPodcastNotification::class,
    );
}
```

---

### 3.6 *Closure 監聽器（匿名函式）*


```php
// app/Providers/AppServiceProvider.php

use App\Events\PodcastProcessed;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    Event::listen(function (PodcastProcessed $event) {
        // 處理事件邏輯
    });
}
```

---

### 3.7 *Queueable 匿名監聽器*

// Q: queueable是？
// A: queueable 是 Laravel 提供的輔助函式，可以讓你用 closure 方式註冊一個「可排入佇列」的事件監聽器。這樣事件觸發時，監聽器會非同步丟到 queue 執行，不會卡住主流程。適合臨時、簡單、匿名的 queue 監聽需求，也支援 onQueue、delay、catch 等鏈式設定。

```php
use App\Events\PodcastProcessed;
use function Illuminate\Events\queueable;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    Event::listen(queueable(function (PodcastProcessed $event) {
        // 非同步處理事件
    }));
}
```


#### **進階：自訂 queue 連線、佇列名稱、延遲**
```php
Event::listen(queueable(function (PodcastProcessed $event) {
    // ...
})->onConnection('redis')->onQueue('podcasts')->delay(now()->addSeconds(10)));
```

#### **監聽失敗處理**
```php
use Throwable;

Event::listen(queueable(function (PodcastProcessed $event) {
    // ...
})->catch(function (PodcastProcessed $event, Throwable $e) {
    // 監聽器失敗時的處理
}));
```

---

### 3.8 *Wildcard 萬用字元監聽器*

Q: **萬用字監聽器是？**
A: 萬用字監聽器（Wildcard Listener）是 Laravel 事件系統的一種特殊監聽方式，可以同時監聽多個事件（例如 user.*），而不需要一個一個指定事件名稱。這種監聽器適合做「統一記錄」、「統一通知」、「統一審計」等需求。$eventName 會是實際事件名稱，$data 是事件資料陣列。

```php
Event::listen('event.*', function (string $eventName, array $data) {
    // $eventName 事件名稱
    // $data 事件資料
});
```

---

## 4. **觸發事件**

你可以在任何地方用 `event()` 輔助函式或 `Event::dispatch()` 來觸發事件：

// 例如在 Controller、Service、Job 等
```php
use App\Events\PodcastProcessed;

// 觸發事件
event(new PodcastProcessed());

// 或
PodcastProcessed::dispatch();
```
Q: *PodcastProcessed::dispatch(); 這個 dispatch() 是 listener 的函式嗎？*
A: 不是，dispatch() 是事件類別（如 PodcastProcessed）的靜態方法，用來「觸發事件」。當你呼叫 dispatch() 時，Laravel 會自動去執行所有有監聽這個事件的 listener 的 handle() 方法。dispatch() 來自 Dispatchable trait，常見於事件類別。

---

## 5. **事件與監聽器的應用場景**

- 訂單出貨通知
- 用戶註冊後發送歡迎信
- 任務完成後推播通知
- 系統日誌、審計記錄
- 任何需要「解耦」的流程

---

## 6. **進階：事件廣播、佇列、同步/非同步**

- 若監聽器實作 `ShouldQueue` 介面，會自動進入 queue 非同步執行。
- 事件可實作 `ShouldBroadcast` 介面，讓事件可被前端（如 WebSocket）即時接收。

Q: *什麼是 ShouldQueue？*
A: 只要監聽器（Listener）class 有 implements ShouldQueue，Laravel 會自動把這個監聽器丟進 queue，讓它「非同步」執行，不會卡住主流程。適合處理寄信、推播等不需即時完成的工作。

Q: *什麼是 ShouldBroadcast？*
A: 只要事件（Event）class 有 implements ShouldBroadcast，這個事件就會被「廣播」出去，通常用在 WebSocket、Pusher、Laravel Echo 等即時前端通訊，前端可即時收到事件通知。

Q: *同步與非同步有什麼差別？*
A: 預設監聽器是「同步」執行（事件觸發時馬上執行 handle），若 implements ShouldQueue 則會「非同步」執行（事件觸發時丟到 queue，等 worker 處理）。

Q: *queueable 跟 ShouldQueue 有什麼不同？*
A: queueable 是 closure 監聽器的語法糖，讓 closure 也能進 queue；ShouldQueue 是 class 監聽器的標準做法。

－ **範例**：
*非同步監聽器*
```php
class SendPodcastNotification implements ShouldQueue {
    public function handle(PodcastProcessed $event) {
        這裡會被丟到 queue 執行
    }
}
*廣播事件*
class PodcastProcessed implements ShouldBroadcast {
    // 事件內容
}
```
---

## 7. **小結**

- 事件（Event）用來「發出訊號」。
- 監聽器（Listener）用來「接收訊號並處理」。
- Artisan 指令會自動幫你建立好檔案與目錄。
- 事件系統讓你的程式碼更模組化、易於維護與擴充。

---

如需更進階的事件流程、廣播、佇列、測試等範例，或要我幫你自動產生更多事件/監聽器檔案，請隨時告訴我！

---

## 8. **事件與監聽器的資料結構與進階用法**

### 8.1 *定義事件（Defining Events）*

事件類別本質上是一個「資料容器」，用來攜帶事件相關的資料。
例如：`App\Events\OrderShipped` 事件會攜帶一個 Eloquent ORM 的 Order 物件。


```php
// app/Events/OrderShipped.php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderShipped
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 建構子：注入訂單模型
     */
    public function __construct(
        public Order $order,
    ) {}
}
```
- 這個事件類別**沒有邏輯**，只是一個攜帶 `Order` 實例的資料容器。
- `SerializesModels` trait 會自動序列化 Eloquent 模型，方便事件進入 queue 時正確還原。

---

### 8.2 *定義監聽器（Defining Listeners）*

監聽器會在 `handle` 方法中接收事件實例，並根據事件內容執行對應邏輯。


```php
// app/Listeners/SendShipmentNotification.php

namespace App\Listeners;

use App\Events\OrderShipped;

class SendShipmentNotification
{
    /**
     * 建構子，可注入依賴
     */
    public function __construct() {}

    /**
     * 處理事件
     */
    public function handle(OrderShipped $event): void
    {
        // 可透過 $event->order 取得訂單資料
    }
}
```
- 監聽器的建構子可 type-hint 依賴，Laravel 會自動注入。
- `handle` 方法回傳 `false` 可中止事件傳遞給其他監聽器。

---

### 8.3 *Queue 監聽器（Queued Event Listeners）*

如果監聽器會執行較慢的任務（如寄信、HTTP 請求），建議讓監聽器進入 queue 非同步執行。

#### 8.3.1 **基本用法**

只要讓監聽器實作 `ShouldQueue` 介面即可：


```php
// app/Listeners/SendShipmentNotification.php

use Illuminate\Contracts\Queue\ShouldQueue;

class SendShipmentNotification implements ShouldQueue
{
    // ...
}
```
- 事件被 dispatch 時，監聽器會自動進入 queue 執行。

#### 8.3.2 **自訂 queue 連線、名稱、延遲**

可透過屬性或方法自訂 queue 行為：

```php
public $connection = 'sqs';   // 指定 queue 連線
public $queue = 'listeners';  // 指定 queue 名稱
public $delay = 60;           // 延遲秒數

public function viaConnection(): string { return 'sqs'; }
public function viaQueue(): string { return 'listeners'; }
public function withDelay(OrderShipped $event): int { return $event->highPriority ? 0 : 60; }
```

#### 8.3.3 **條件式 Queue**

可用 shouldQueue 方法決定是否進入 queue：

```php
public function shouldQueue(OrderShipped $event): bool
{
    return $event->order->subtotal >= 5000;
}
```

#### 8.3.4 **Queue 失敗處理**

- 可用 `failed` 方法處理 queue 監聽器失敗時的情境：

```php
public function failed(OrderShipped $event, Throwable $exception): void
{
    // 記錄 log、通知等
}
```

#### 8.3.5 **最大重試次數與重試間隔**

- `$tries` 屬性：最大重試次數
- `$backoff` 屬性或 `backoff()` 方法：重試間隔（可回傳陣列做 exponential backoff）
- `retryUntil()` 方法：指定重試截止時間

```php
public $tries = 5;
public $backoff = 3;
public function backoff(): array { return [1, 5, 10]; }
public function retryUntil(): DateTime { return now()->addMinutes(5); }
```

Q: *$tries 是什麼？*
A: $tries 屬性用來設定這個 queue job 最多可以重試幾次（包含第一次執行），超過次數後 job 會被標記為失敗（進入 failed_jobs）。

Q: *$backoff 跟 backoff() 有什麼差別？*
A: $backoff 屬性可以設定每次重試的間隔秒數（固定值），backoff() 方法則可以回傳陣列，讓每次重試間隔不同（例如指數型退避：1秒、5秒、10秒...）。

Q: *retryUntil() 有什麼用途？*
A: retryUntil() 方法可以設定一個「重試截止時間」，超過這個時間即使還沒達到 $tries 次數，也不再重試，直接標記為失敗。

Q: *什麼時候會用到這些屬性？*
A: 當 queue job 可能因外部資源（如 API、資料庫）暫時失效時，可以用 $tries 和 $backoff 控制重試策略，避免 job 無限重跑或太快重跑造成資源浪費。

Q: *如果 $tries 和 retryUntil() 都有設定，哪個優先？*
A: 兩者會同時生效，只要其中一個條件達成（次數用完或超過截止時間），job 就會被標記為失敗。

---

### 8.4 *Queue 與資料庫交易（Transactions）*

- 若 queue 監聽器在資料庫交易中被 dispatch，可能會在交易 commit 前被執行，導致資料尚未寫入。
- 可實作 `ShouldQueueAfterCommit` 介面，確保事件在交易 commit 後才 dispatch。

Q: **為什麼 queue 監聽器在交易中 dispatch 會有問題？**
A: 如果你在資料庫交易（DB::transaction）內 dispatch 事件，queue 監聽器可能會在交易 commit 前就被 queue worker 執行，這時資料還沒寫進資料庫，導致監聽器讀不到正確資料或產生 race condition。

Q: **ShouldQueueAfterCommit 有什麼作用？**
A: 只要監聽器 implements ShouldQueueAfterCommit，Laravel 會等到資料庫交易 commit 後才 dispatch 事件，確保監聽器執行時資料已經寫入資料庫。

Q: **什麼時候要用 ShouldQueueAfterCommit？**
A: 當你的事件是在 DB::transaction 內 dispatch，且監聽器需要依賴交易內寫入的資料時，建議 implements ShouldQueueAfterCommit，避免資料不一致。

Q: **如果沒有用 ShouldQueueAfterCommit，會發生什麼事？**
A: 監聽器可能會讀到還沒 commit 的資料（甚至查不到資料），造成資料不一致、通知失敗、重複執行等問題。

Q: **事件本身也可以 implements ShouldDispatchAfterCommit 嗎？**
A: 可以，事件類別 implements ShouldDispatchAfterCommit 時，事件會等交易 commit 後才 dispatch，這樣所有監聽器都會在交易後才執行。

---

### 8.5 *小結*

- 事件類別是資料容器，監聽器負責處理事件。
- 監聽器可進入 queue 非同步執行，支援條件、延遲、重試、失敗處理等進階功能。
- Laravel 事件系統高度彈性，適合 decouple 業務邏輯與後續處理。

---

## 9. **事件的觸發（Dispatching Events）**

### 9.1 *基本觸發*

事件可用靜態 `dispatch` 方法觸發，這是由 `Dispatchable` trait 提供。


```php
// app/Http/Controllers/OrderShipmentController.php

namespace App\Http\Controllers;

use App\Events\OrderShipped;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderShipmentController extends Controller
{
    /**
     * Ship the given order.
     */
    public function store(Request $request): RedirectResponse
    {
        $order = Order::findOrFail($request->order_id);

        // 訂單出貨邏輯...

        // 觸發事件
        OrderShipped::dispatch($order);

        return redirect('/orders');
    }
}
```
- `dispatch` 方法的參數會傳給事件的建構子。

---

### 9.2 *條件式觸發*

可用 `dispatchIf` 與 `dispatchUnless` 依條件觸發事件：

```php
OrderShipped::dispatchIf($condition, $order);
OrderShipped::dispatchUnless($condition, $order);
```

---

### 9.3 *測試事件觸發*

Laravel 測試輔助工具可斷言事件是否被 dispatch，而不會真的執行監聽器。


```php
// 測試範例（可放在 tests/Feature/ 內）

use Illuminate\Support\Facades\Event;
use App\Events\OrderShipped;

Event::fake();

// 執行觸發事件的程式
OrderShipped::dispatch($order);

Event::assertDispatched(OrderShipped::class);
```

---

### 9.4 *交易後才觸發事件（ShouldDispatchAfterCommit）*

有時你希望事件在資料庫交易 commit 後才 dispatch，可讓事件類別實作 `ShouldDispatchAfterCommit` 介面。


```php
// app/Events/OrderShipped.php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderShipped implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 建構子：注入訂單模型
     */
    public function __construct(
        public Order $order,
    ) {}
}
```
- 若有交易，事件會等 commit 後才 dispatch；若無交易，則立即 dispatch。

---

## 10. **事件訂閱者（Event Subscribers）**

### 10.1 *什麼是 Event Subscriber？*

Event Subscriber（事件訂閱者）是一種可以在同一個類別中同時訂閱多個事件的設計，讓你可以把多個事件 handler 寫在同一個 class 裡，方便管理與維護。

---

### 10.2 *Subscriber 類別寫法*

#### 寫法一：**subscribe 方法內用 listen 註冊**


```php
// app/Listeners/UserEventSubscriber.php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class UserEventSubscriber
{
    /**
     * 處理登入事件
     */
    public function handleUserLogin(Login $event): void {}

    /**
     * 處理登出事件
     */
    public function handleUserLogout(Logout $event): void {}

    /**
     * 註冊訂閱的事件與 handler
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            Login::class,
            [UserEventSubscriber::class, 'handleUserLogin']
        );

        $events->listen(
            Logout::class,
            [UserEventSubscriber::class, 'handleUserLogout']
        );
    }
}
```

#### 寫法二：**subscribe 方法直接回傳 array**


```php
// app/Listeners/UserEventSubscriber.php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class UserEventSubscriber
{
    public function handleUserLogin(Login $event): void {}
    public function handleUserLogout(Logout $event): void {}

    /**
     * 註冊訂閱的事件與 handler
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleUserLogin',
            Logout::class => 'handleUserLogout',
        ];
    }
}
```

#### **實務範例**


```php
// 範例一：用戶登入/登出審計

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class UserAuditSubscriber
{
    public function onUserLogin(Login $event)
    {
        \Log::info('User login', ['user_id' => $event->user->id]);
    }

    public function onUserLogout(Logout $event)
    {
        \Log::info('User logout', ['user_id' => $event->user->id]);
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Login::class, [self::class, 'onUserLogin']);
        $events->listen(Logout::class, [self::class, 'onUserLogout']);
    }
}
```

```php
// 註冊方式：
// app/Providers/EventServiceProvider.php
protected $subscribe = [
    \App\Listeners\UserAuditSubscriber::class,
];
```


```php
// 範例二：訂單相關事件集中處理

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Events\OrderShipped;
use Illuminate\Events\Dispatcher;

class OrderEventSubscriber
{
    public function onOrderCreated(OrderCreated $event) {}
    public function onOrderPaid(OrderPaid $event) {}
    public function onOrderShipped(OrderShipped $event) {}

    public function subscribe(Dispatcher $events)
    {
        $events->listen(OrderCreated::class, [self::class, 'onOrderCreated']);
        $events->listen(OrderPaid::class, [self::class, 'onOrderPaid']);
        $events->listen(OrderShipped::class, [self::class, 'onOrderShipped']);
    }
}
```


```php
// 範例三：多語系通知集中處理
namespace App\Listeners;

use App\Events\UserRegistered;
use App\Events\PasswordReset;
use Illuminate\Events\Dispatcher;

class NotificationSubscriber
{
    public function sendWelcomeMail(UserRegistered $event) {}
    public function sendPasswordResetMail(PasswordReset $event) {}

    public function subscribe(Dispatcher $events)
    {
        $events->listen(UserRegistered::class, [self::class, 'sendWelcomeMail']);
        $events->listen(PasswordReset::class, [self::class, 'sendPasswordResetMail']);
    }
}
```

- **小結**：
    - Subscriber 適合一個主題/領域下有多個事件要集中管理，或需要共用依賴、狀態時。
    - 讓事件與 handler 關係更清楚、易於維護。
    - 在 EventServiceProvider 的 $subscribe 屬性註冊 subscriber class。