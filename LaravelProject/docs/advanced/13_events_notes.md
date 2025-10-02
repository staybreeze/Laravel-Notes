# *Laravel Events 事件系統 筆記*

---

## 1. **事件系統簡介**

Laravel 的事件（Events）提供了簡單的 *觀察者模式（Observer Pattern）* 實作，讓你可以「`訂閱`」與「`監聽`」應用程式中發生的各種事件。

- __事件類別__ 通常放在 `app/Events` 目錄下。
- __監聽器（Listener）類別__ 通常放在 `app/Listeners` 目錄下。
- 如果這些目錄不存在，當你用 Artisan 指令產生事件或監聽器時，Laravel 會自動建立。

---

### *事件的用途*

事件可以讓你的程式碼「__解耦__」，例如：
- 當訂單出貨時，你想通知用戶（如發 Slack 通知），可以發出一個 `App\Events\OrderShipped` 事件，讓 __`監聽器`去處理通知__，而 __不用把通知邏輯寫在訂單流程裡__。

---

## 2. **產生事件與監聽器**


### 2.1 *產生事件類別*

```bash
php artisan make:event PodcastProcessed
```

- 會在 `app/Events/PodcastProcessed.php` 產生事件類別。


```php
// app/Events/PodcastProcessed.php
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


```bash
php artisan make:listener SendPodcastNotification --event=PodcastProcessed
```

- 會在 `app/Listeners/SendPodcastNotification.php` 產生監聽器類別，並自動綁定到 `PodcastProcessed` 事件。

```php
// app/Listeners/SendPodcastNotification.php
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

### 2.3 *互動式產生*（可省略參數，Laravel 會互動詢問）

```bash
php artisan make:event
php artisan make:listener
```

- __不加參數時__，Laravel 會互動詢問你要建立的事件或監聽器名稱，以及監聽器要綁定哪個事件。

---

## 3. **事件與監聽器的註冊**

Laravel 會自動掃描 `app/Listeners` 目錄下的監聽器，並根據監聽器的 `handle` 方法型別提示 __自動綁定事件__。
如果要 _手動註冊_ 或 _自訂_ 事件與監聽器的對應關係，可在 `app/Providers/EventServiceProvider.php` 設定：

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

Laravel 會自動註冊 `app/Listeners` 目錄下所有監聽器，當監聽器的 `handle` 或 `__invoke` 方法型別提示 __對應事件__ 時，`自動完成註冊`。

```php
// app/Listeners/SendPodcastNotification.php
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

---

#### **監聽多個事件**（PHP 8+ `union types`）

```php
public function handle(PodcastProcessed|PodcastPublished $event): void
{
    // 可同時監聽多個事件
}
```

---

### 3.2 *多目錄自動發現*（`withEvents`）

如果監聽器 __分散在多個目錄__，可在 `bootstrap/app.php` 用 `withEvents` 指定：

```php
// bootstrap/app.php
->withEvents(discover: [
    __DIR__.'/../app/Domain/Orders/Listeners',
])
```

---

- 支援萬用字元 `*`：

```php
->withEvents(discover: [
    __DIR__.'/../app/Domain/*/Listeners',
])
```

---

### 3.3 *查看所有已註冊監聽器*

```bash
php artisan event:list
```

- 可列出 __所有已註冊__ 的事件與監聽器對應關係。

---

### 3.4 *事件註冊快取與清除*

```bash
php artisan event:cache
# 或
php artisan optimize
```

- 快取檔案會`加速`事件註冊。

___

- 若需 __清除快取__：

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

    Event::listen(
        UserRegistered::class,
        SendWelcomeEmail::class,
    );

    // 可以繼續加更多事件與監聽器
}
```

<!-- 
Event::listen() 不支援陣列對應寫法，
必須一組一組分開註冊，不能用 key-value 陣列一次註冊多組。
-->

---

### 3.6 *Closure 監聽器*（`匿名函式`）


```php
// app/Providers/AppServiceProvider.php

use App\Events\PodcastProcessed;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    // 用 Closure（匿名函式）當監聽器時，不需要另外寫監聽類別檔案
    Event::listen(function (PodcastProcessed $event) {
        // 處理事件邏輯
    });
}
```

---

### 3.7 *Queueable 匿名監聽器*

- Q: **queueable是？**
- A: `queueable` 是 Laravel 提供的 __輔助函式__，可以讓你`用 closure 方式註冊一個「可排入佇列」的事件監聽器`。
     這樣事件觸發時，監聽器會非同步丟到 queue 執行，不會卡住主流程。適合臨時、簡單、匿名的 queue 監聽需求，也支援 `onQueue、delay、catch` 等鏈式設定。

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


---

#### **進階：自訂 queue 連線、佇列名稱、延遲**

```php
Event::listen(
    queueable(function (PodcastProcessed $event) {
        // ...事件處理邏輯...
    })
    ->onConnection('redis')           // 指定使用 redis 連線
    ->onQueue('podcasts')             // 指定佇列名稱為 podcasts
    ->delay(now()->addSeconds(10))    // 延遲 10 秒後執行
);
```

---

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
A: 萬用字監聽器（Wildcard Listener）是 Laravel 事件系統的一種 __特殊監聽方式__，可以`同時監聽多個事件（例如 user.*）`，而
   不需要一個一個指定事件名稱。這種監聽器適合做「統一記錄」、「統一通知」、「統一審計」等需求。`$eventName` 會是實際事件名稱，`$data` 是事件資料陣列。

```php
use Illuminate\Support\Facades\Event;

// 監聽多個事件（event.* 代表所有 event. 開頭的事件）
Event::listen('event.*', function (string $eventName, array $data) {
    // $eventName 可能是 event.created、event.updated、event.deleted 等
    logger()->info("收到事件：{$eventName}", $data);

    // 根據事件名稱分別處理
    if ($eventName === 'event.created') {
        // 處理建立事件
    } elseif ($eventName === 'event.updated') {
        // 處理更新事件
    } elseif ($eventName === 'event.deleted') {
        // 處理刪除事件
    }
});
```

---

## 4. **觸發事件**

你可以在任何地方用 `event()` 輔助函式或 `Event::dispatch()` 來 __觸發事件__：

```php
// 例如在 Controller、Service、Job 等

use App\Events\PodcastProcessed;

// 觸發事件
event(new PodcastProcessed());

// 或
PodcastProcessed::dispatch();
```

Q: *PodcastProcessed::dispatch(); 這個 dispatch() 是 listener 的函式嗎？*
A: 不是，`dispatch()` 是 _事件類別_（如 `PodcastProcessed`）的 _靜態方法_，用來「__觸發事件__」。
   當你呼叫 `dispatch()` 時，Laravel 會 __自動去執行所有有監聽這個事件的 listener 的 `handle()` 方法__。

   `dispatch()` 來自 `Dispatchable trait`，常見於事件類別。

---

## 5. **事件與監聽器的應用場景**

- `訂單出貨`通知
- 用戶註冊後發送`歡迎信`
- 任務完成後`推播通知`
- 系統日誌、審計記錄
- 任何需要「__解耦__」的流程

---

## 6. **進階：事件廣播、佇列、同步/非同步**

- 若 __監聽器__ 實作 `ShouldQueue` 介面，會 _自動進入 queue 非同步執行_。
- __事件__ 可實作 `ShouldBroadcast` 介面，讓事件 _可被前端（如 WebSocket）即時接收_。

Q: *什麼是 ShouldQueue？*
A: 只要`監聽器`（Listener）class 有` implements ShouldQueue`，Laravel 會自動把這個監聽器丟進 queue，讓它「__非同步__」執行，不會卡住主流程。適合處理寄信、推播等不需即時完成的工作。

Q: *什麼是 ShouldBroadcast？*
A: 只要`事件`（Event）class 有 `implements ShouldBroadcast`，這個事件就會被「_廣播_」出去，通常用在`WebSocket、Pusher、Laravel Echo` 等 __即時前端通訊__，前端可即時收到事件通知。

Q: *同步與非同步有什麼差別？*
A: **預設** 監聽器是「__同步__」執行（事件觸發時馬上執行 `handle`），若 `implements ShouldQueue` 則會「__非同步__」執行（__事件觸發時丟到 queue，等 worker 處理__）。

Q: *queueable 跟 ShouldQueue 有什麼不同？*
A: `queueable` 是 closure 監聽器的 __語法糖__，讓 closure 也能進 `queue`；`ShouldQueue` 是 class 監聽器的 __標準做法__。

---

*非同步監聽器*

```php
class SendPodcastNotification implements ShouldQueue {
    public function handle(PodcastProcessed $event) {
        // 這裡會被丟到 queue 執行
    }
}
```
---

*廣播事件*

```php
class PodcastProcessed implements ShouldBroadcast {
    // 事件內容
}
```

---

## 7. **小結**

- `事件`（Event）用來「__發出訊號__」。
- `監聽器`（Listener）用來「__接收訊號並處理__」。
- Artisan 指令會自動幫你建立好檔案與目錄。
- 事件系統讓你的程式碼`更模組化、易於維護與擴充`。

---

## 8. **事件與監聽器的`資料結構`與進階用法**


### 8.1 *定義事件*（`Defining Events`）

__事件類別__ 本質上是一個「_資料容器_」，用來攜帶事件相關的資料。

例如：`App\Events\OrderShipped` 事件會攜帶一個 `Eloquent ORM` 的 `Order 物件`。

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
- `SerializesModels` trait 會 __自動序列化 Eloquent 模型__，方便事件進入 `queue` 時正確還原。

<!-- SerializesModels trait 會自動把 Eloquent 模型「序列化」成可儲存的資料格式（通常是陣列或字串），
     這樣事件物件在進入 queue（佇列）時，可以安全地存到資料庫或傳送到其他伺服器。
     等 queue job 執行時，會自動「反序列化」還原成原本的 Eloquent 模型物件，
     讓你在事件處理器裡可以直接操作模型，不用自己處理資料轉換。
     這樣可以避免模型在序列化過程中遺失關聯、狀態等重要資訊，
     讓 Laravel 的事件與 queue 系統更穩定、好用。 -->

<!-- queue 需要序列化資料，
     因為任務會被儲存到資料庫、Redis 或其他快取系統，
     必須把物件轉成可儲存的格式（如字串或陣列），
     等任務被取出執行時再還原成原本的物件，
     這樣才能跨 process、跨伺服器安全傳遞和執行。 -->

<!-- 如果不序列化，物件無法正確儲存到 queue 後端，
     資料可能遺失、格式錯誤，
     任務執行時也無法還原原本的物件，
     導致 queue 任務失敗或資料不完整。 -->

---

### 8.2 *定義監聽器*（`Defining Listeners`）

監聽器會在 `handle` 方法中 __接收事件實例，並根據事件內容執行對應邏輯__。


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
- 監聽器的建構子可 `type-hint` 依賴，Laravel 會 __自動注入__。
- `handle` 方法回傳 `false` 可 __中止__ 事件傳遞給其他監聽器。

---

### 8.3 *Queue 監聽器*（`Queued Event Listeners`）

如果監聽器會執行 __較慢的任務__（如`寄信、HTTP 請求`），建議讓監聽器 __進入 queue 非同步執行__。

---

#### 8.3.1 **基本用法**

只要讓 __監聽器__ 實作 `ShouldQueue` 介面即可：

```php
// app/Listeners/SendShipmentNotification.php

use Illuminate\Contracts\Queue\ShouldQueue;

class SendShipmentNotification implements ShouldQueue
{
    // ...
}
```

- 事件被 `dispatch` 時，監聽器會 __自動進入 queue 執行__。

---

#### 8.3.2 **自訂 queue 連線、名稱、延遲**

可透過屬性或方法自訂 `queue` 行為：

```php
public $connection = 'sqs';   // 指定 queue 連線
public $queue = 'listeners';  // 指定 queue 名稱
public $delay = 60;           // 延遲秒數

public function viaConnection(): string { return 'sqs'; }
public function viaQueue(): string { return 'listeners'; }
public function withDelay(OrderShipped $event): int { return $event->highPriority ? 0 : 60; }
```

---

#### 8.3.3 **條件式 Queue**

可用 `shouldQueue` 方法 __決定是否__ 進入 queue：

```php
public function shouldQueue(OrderShipped $event): bool
{
    return $event->order->subtotal >= 5000;
}
```

<!-- 
shouldQueue 這個方法名稱是Laravel 事件監聽器的固定命名，
只要你的監聽器類別裡有這個方法，Laravel 會自動在事件觸發時，判斷是否要將事件監聽器排入 queue。 
-->

<!-- 
shouldQueue 方法不是必須實作，
只有當你需要「條件式排入 queue」時才要加上。
如果不實作，Laravel 會預設所有監聽器都進 queue（只要監聽器 implements ShouldQueue）。 
-->

---

#### 8.3.4 **Queue 失敗處理**

- 可用 `failed` 方法處理 `queue` __監聽器失敗__ 時的情境：

```php
use App\Events\OrderShipped;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderShipped $event)
    {
        // 正常處理事件
        // 例如：發送通知
    }

    // 任務失敗時會自動呼叫這個方法
    public function failed(OrderShipped $event, \Throwable $exception): void
    {
        // 記錄錯誤 log
        Log::error('OrderShipped 事件處理失敗', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage(),
        ]);
        // 也可以在這裡通知管理員或做其他補救措施
    }
}
```

---

#### 8.3.5 **最大重試次數與重試間隔**

- `$tries` 屬性：__最大__ 重試次數
- `$backoff` 屬性或 `backoff()` 方法：__重試間隔__（可 *回傳陣列* 做 `exponential backoff`）
- `retryUntil()` 方法：指定 __重試截止時間__

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use DateTime;

class SendBigOrderNotification implements ShouldQueue
{
    public $tries = 5;                  // 最多重試 5 次
    public $backoff = 3;                // 每次重試間隔 3 秒

    public function backoff(): array    // 自訂每次重試間隔（秒）
    {
        return [1, 5, 10];
    }

    public function retryUntil(): DateTime // 最長重試到 5 分鐘後
    {
        return now()->addMinutes(5);
    }

    public function handle(OrderShipped $event)
    {
        // 實際處理邏輯
    }

    public function shouldQueue(OrderShipped $event): bool
    {
        return $event->order->subtotal >= 5000;
    }
}
```

---

Q: *$tries 是什麼？*
A: `$tries` 屬性用來設定 __這個 queue job 最多可以重試幾次__（包含第一次執行），超過次數後 job 會被標記為`失敗`（進入 `failed_jobs`）。

Q: *$backoff 跟 backoff() 有什麼差別？*
A: `$backoff` 屬性可以設定 __每次重試的間隔秒數（固定值）__，`backoff()` 方法則 __可以回傳陣列，讓每次重試間隔不同__（例如指數型退避：1秒、5秒、10秒...）。

Q: *retryUntil() 有什麼用途？*
A: `retryUntil()` 方法可以設定一個「__重試截止時間__」，超過這個時間即使還沒達到 $tries 次數，也`不再重試，直接標記為失敗`。

Q: *什麼時候會用到這些屬性？*
A: 當 `queue job` __可能因外部資源（如 API、資料庫）暫時失效時__，可以用 `$tries` 和 `$backoff` __控制重試策略__，避免 job 無限重跑或太快重跑造成資源浪費。

Q: *如果 $tries 和 retryUntil() 都有設定，哪個優先？*
A: 兩者會 __同時生效__，只要其中一個條件達成（`次數用完`或`超過截止時間`），job 就會被標記為失敗。

---

### 8.4 *Queue 與資料庫交易*（`Transactions`）

- 若 `queue` 監聽器在 __資料庫交易中__ 被 `dispatch`，可能會 _在交易 commit 前被執行，導致資料尚未寫入_。
- 可實作 `ShouldQueueAfterCommit` 介面，__確保事件在交易 commit 後才 dispatch__。

Q: **為什麼 queue 監聽器在交易中 dispatch 會有問題？**
A: 如果你在`資料庫交易（DB::transaction` 內 `dispatch` `事件，queue` 監聽器可能會 _在交易 commit 前_ 就被 queue worker 執行，這時資料還沒寫進資料庫，導致監聽器讀不到正確資料或產生 race condition。

Q: **ShouldQueueAfterCommit 有什麼作用？**
A: 只要監聽器 `implements ShouldQueueAfterCommit`，Laravel 會等到資料庫交易 commit 後才 dispatch 事件，確保監聽器執行時資料已經寫入資料庫。

Q: **什麼時候要用 ShouldQueueAfterCommit？**
A: 當你的事件是在 `DB::transaction` 內 `dispatch`，且監聽器需要依賴交易內寫入的資料時，建議 `implements ShouldQueueAfterCommit`，避免資料不一致。

Q: **如果沒有用 ShouldQueueAfterCommit，會發生什麼事？**
A: 監聽器可能會 _讀到還沒 commit 的資料_（甚至查不到資料），造成資料不一致、通知失敗、重複執行等問題。

Q: **事件本身也可以 implements `ShouldDispatchAfterCommit` 嗎？**
A: 可以，事件類別 `implements ShouldDispatchAfterCommit` 時，事件會 __等交易 commit 後才 dispatch__，這樣所有監聽器都會在交易後才執行。

---

### 8.5 *小結*

- `事件`類別是 __資料容器__，`監聽器`負責 __處理事件__。
- `監聽器` __可進入 queue 非同步執行__，支援條件、延遲、重試、失敗處理等進階功能。
- Laravel 事件系統高度彈性，適合 `decouple` 業務邏輯與後續處理。

---

## 9. **事件的觸發**（`Dispatching Events`）

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
        // OrderShipped 事件類別本身有 use Dispatchable trait
        OrderShipped::dispatch($order)
        ;

        return redirect('/orders');
    }
}
```
- `dispatch` 方法的參數會 __傳給事件的建構子__。

---

### 9.2 *條件式觸發*

可用 `dispatchIf` 與 `dispatchUnless` 依條件觸發事件：

```php
OrderShipped::dispatchIf($condition, $order);
OrderShipped::dispatchUnless($condition, $order);
```

---

### 9.3 *測試事件觸發*

Laravel 測試輔助工具可`斷言事件`是否被 dispatch，而不會真的執行監聽器。


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

### 9.4 *交易後才觸發事件*（`ShouldDispatchAfterCommit`）

有時你希望事件 __在資料庫交易 commit 後才 dispatch__，可讓事件類別實作 `ShouldDispatchAfterCommit` 介面。


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
- 若 __有交易__，事件會等 commit 後才 dispatch；若 __無交易__，則立即 dispatch。

---

## 10. **事件訂閱者**（`Event Subscribers`）

### 10.1 *什麼是 Event Subscriber？*

Event Subscriber（事件訂閱者）是一種 __可以在`同一個類別`中同時訂閱`多個事件`的設計__，讓你可以把多個事件 `handler` 寫在同一個 class 裡，方便管理與維護。

<!-- 
事件訂閱者（有 subscribe 方法的類別）本質上也是一種「監聽器」，
只是可以同時監聽多個事件，
而一般監聽器通常只監聽一個事件。 
-->

---

### 10.2 *Subscriber 類別寫法*

#### 寫法一：**`subscribe` 方法內用 listen 註冊**

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

---

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

---

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

---

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

---

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
    - `Subscriber` 適合 __一個主題/領域下__ 有`多個事件要集中管理`，或需要`共用依賴、狀態`時。
    - 讓事件與 `handler` 關係更清楚、易於維護。
    - 在 `EventServiceProvider` 的 __$subscribe 屬性__ 註冊 __subscriber class__。