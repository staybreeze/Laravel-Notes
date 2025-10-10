# *Laravel Queue 基本觀念整理*

---

## **Queue、Job、Worker 是什麼？**

### *什麼是 Job？*

__Job（佇列任務）__ 就是你要丟到背景執行的「_工作_」。  

例如：`寄信、處理上傳檔案、產生報表、推播通知`等，這些通常比較耗時，不適合在 HTTP 請求流程中直接執行。

在 Laravel 裡，你會建立一個 Job 類別（通常在 `app/Jobs`），裡面寫好要執行的邏輯，然後用 `dispatch()` 把它 _丟到 queue（佇列）裡_。

__簡單來說__：  

Job = _你要做的「一件事」的程式碼（任務）_。

---

### *什麼是 Queue？*

__Queue（佇列）__ 就是「_任務的排隊區_」，像是「_待辦清單_」。

你把 Job 丟進 Queue，這些任務就會在這裡排隊，等著被執行。

Queue 可以有很多種（例如：`high、default、low`），也可以有不同的 _後端服務_（如 `Redis、資料庫`）。

<!-- 這裡的「後端服務」是指「 儲存和管理 queue 資料的技術 」，
     例如 Redis、資料庫（MySQL、PostgreSQL）、Amazon SQS 等，
     Laravel 只是提供 queue 功能，
     真正的 queue 資料會存放在這些後端服務裡。 -->

<!-- 後端服務是指「 獨立運作、提供特定功能的伺服器或系統 」，
     例如資料庫、Redis、Amazon SQS、郵件伺服器等，
     它們負責儲存、處理或傳遞資料，
     而後端框架（如 Laravel）則是用來開發和管理這些服務的應用程式，
     兩者是不同層次的概念。 -->

---

### *什麼是 Worker？*

__Worker（佇列工人）__ 是一個在背景執行、負責 __「_撿_」queue 裡的 job__ 來執行的程式。  
你可以把 worker 想像成一個不斷輪詢佇列、看到有新任務就拿出來執行的「小幫手」。

在 Laravel 裡，worker 通常用這個`指令啟動`：

```bash
php artisan queue:work
```

你可以開很多個 worker（多工），讓他們同時處理多個任務。

__簡單來說__：  

Worker = _幫你執行 queue 裡 job 的背景程式_。

---

## **三者的關係**

### *關係圖解*

```php
    A[你寫的 Job 任務] -- dispatch() --> B[Queue 佇列]
    B -- 等待 --> C[Worker 工人]
    C -- 執行 --> D[Job 任務完成]
```

- 你用 `dispatch()` 把 Job 丟進 Queue。

- __Queue__ 負責「_排隊_」。
- __Worker__ 會「_撿_」Queue 裡的 Job 來執行。
- __Job__ 執行完畢，Queue 就少一個任務。

---

### *生活化比喻*

- __Job__：一張`待辦卡片`（上面 _寫著要做什麼_）
- __Queue__：`待辦箱`（所有卡片都丟進這裡排隊）
- __Worker__：`工人`（負責從箱子裡拿卡片出來，照著上面指示去做）

---

### *總結*

- __Job__ 是「_要做什麼_」。
- __Queue__ 是「_排隊等著做_」。
- __Worker__ 是「_真的去做的人_」。

三者合作，讓你的系統可以 _把耗時的工作丟到背景_，讓主流程更快回應使用者！

---

# *Laravel Queue（佇列）筆記*


## **什麼是 Laravel Queue？**

- `Laravel Queue（佇列）` 讓你可以 __將耗時的任務__（如：解析與儲存上傳的 CSV 檔案）__丟到背景執行__，讓網頁請求能夠快速回應，提升使用者體驗。
- Laravel 提供 __統一的 Queue API__，可支援多種後端（`Amazon SQS、Redis、資料庫`等）。
- 佇列設定檔在 `config/queue.php`，可設定 __多種連線__（`connections`）與 __驅動__（`drivers`）。
- Laravel 也有 *Horizon*（__專為 Redis 佇列設計的漂亮儀表板與管理工具__）。

---

## **Connections vs. Queues**

- *Connection（連線）*：__指向一個後端服務__（如 SQS、Redis、資料庫等），在 `config/queue.php` 的 `connections` 陣列中設定。
- *Queue（佇列）*：__每個 connection 可有多個 queue__（可想像成不同的工作堆疊）。

- 每個 `connection` 設定檔內有 queue `屬性`，代表預設的 queue `名稱`。

- `派送（dispatch）` Job 時可指定 queue `名稱`：

```php
use App\Jobs\ProcessPodcast;
// 派送到預設 connection 的預設 queue
ProcessPodcast::dispatch();
// 派送到預設 connection 的 "emails" queue
ProcessPodcast::dispatch()->onQueue('emails');
```

---

- 多 `queue` 可用於 __分流、優先處理__：

```bash
php artisan queue:work --queue=high,default
```

---

## **各 Driver 注意事項與前置作業**

### *Database*

- 需有 _jobs 資料表_，預設 migration：`0001_01_01_000002_create_jobs_table.php`

- 若無此 migration，可用：

```bash
php artisan make:queue-table
php artisan migrate
```

---

### *Redis*

- 需在 `config/database.php` 設定 `Redis` 連線。

   ```php
   // config/database.php 內 redis 設定
   'redis' => [
       'client' => 'phpredis', // 指定 Redis client
       'default' => [
           'host' => env('REDIS_HOST', '127.0.0.1'), // Redis 主機位置
           'password' => env('REDIS_PASSWORD', null), // Redis 密碼
           'port' => env('REDIS_PORT', 6379), // Redis 連接埠
           'database' => env('REDIS_DB', 0), // 使用的資料庫編號
       ],
   ]
   ```

---

- `Redis Cluster` 需 `queue 名稱`加 _hash tag_（如 `{default}`），確保 __同一 queue 的 key 在同一 hash slot__。
   - 在 *queue.php* 設定 `'queue' => '{default}'`，或 `dispatch(new Job)->onQueue('{default}')`
   - 加上 _大括號 hash tag_，__讓 cluster 下同一 queue 的 key 分配到同一 slot，避免分散導致 queue 無法正確消費__。

<!-- Redis Cluster 是 Redis 的分散式架構，
     能把資料分散到多台伺服器（節點），
     提升儲存容量與可用性，
     並自動處理資料分片和故障轉移。 -->

<!-- slot 在 Redis Cluster 裡是指「資料分配的區塊」，
     Redis Cluster 會把所有 key 根據 hash 計算分配到 16384 個 slot，
     每個 slot 由不同節點管理，
     加上大括號 hash tag 可以讓同一 queue 的 key 都分配到同一 slot，
     確保 queue 資料都在同一節點，方便正確消費。 -->

<!-- 這些伺服器是 Redis 的節點（Redis server），
     是獨立運作的 Redis 服務，不是 Laravel。
     Laravel 只是連接和使用 Redis Cluster，
     不負責管理這些伺服器。 -->

<!-- 放在同一個節點 slot 可以讓 queue 的所有資料都集中在同一台 Redis 伺服器，
     這樣消費 queue 時不會跨節點查詢，
     提升效能並避免資料分散導致 queue 無法正確消費。 -->
     ```php
     dispatch(new JobA)->onQueue('{default}'); // 這個任務會分配到 Redis 的同一 slot（同一節點）
     dispatch(new JobB)->onQueue('{default}'); // 這個也會分配到同一 slot

     dispatch(new JobC)->onQueue('other');     // 這個任務沒加 hash tag，可能分配到不同 slot（不同節點）
     dispatch(new JobD)->onQueue('other');     // 這個也可能分配到另一個 slot

     dispatch(new JobE)->onQueue('{other}');   // 這個任務加了 hash tag，會分配到同一 slot（同一節點，與 {other} 相關的 queue 都集中）
     ```

---

- `block_for` 設定：指定 `worker` **等待新任務的秒數**（可提升效能）。

   ```php
   // config/queue.php 內 redis 連線設定
   'connections' => [
       'redis' => [
           'driver' => 'redis',
           'block_for' => 5, // worker 最多等待 5 秒（建議設 1~5 秒，減少輪詢）
       ],
   ]
   ```

---

- 設為 **0 會無限阻塞**，_直到有新任務_。

   ```php
   // 
    'block_for' => 0 // worker 會一直阻塞直到有新任務
   // 適合高併發場景，worker 不會閒置浪費資源。
   ```

---

### *其他 Driver 依賴*

- __Amazon SQS__：`aws/aws-sdk-php ~3.0`
- __Beanstalkd__：`pda/pheanstalk ~5.0`
- __Redis__：`predis/predis ~2.0` 或 `phpredis PHP extension`
- __MongoDB__：`mongodb/laravel-mongodb`

---

## **建立 Job**

### *產生 Job 類別*

```bash
php artisan make:job ProcessPodcast
```

- 產生於 `app/Jobs` 目錄。
- **預設** 會實作 `Illuminate\Contracts\Queue\ShouldQueue`，代表 __此 Job 會進入佇列__。

---

### *Job 類別結構*

```php
namespace App\Jobs; // Job 類別的命名空間，通常放在 app/Jobs 目錄

use Illuminate\Bus\Queueable; // Queueable trait，提供 queue 相關功能
use Illuminate\Contracts\Queue\ShouldQueue; // 代表這個 Job 會進入佇列
use Illuminate\Foundation\Bus\Dispatchable; // 提供 dispatch() 方法
use Illuminate\Queue\InteractsWithQueue; // 提供與 queue 互動的方法
use Illuminate\Queue\SerializesModels; // 讓 Eloquent model 能安全序列化
use App\Models\Podcast;

class ProcessPodcast implements ShouldQueue // 實作 ShouldQueue 介面，這個 Job 會被放進 queue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; // 引入多個 trait，讓 Job 支援 dispatch、序列化、queue 操作等

    public $podcast; // Job 需要處理的資料（可為 model、array、primitive）

    /**
     * 建構子，接收要處理的資料
     */
    public function __construct(Podcast $podcast)
    {
        $this->podcast = $podcast; // 將 Podcast model 存到屬性，供 handle() 使用
    }

    /**
     * Job 執行的實際邏輯
     */
    public function handle()
    {
        // 這裡寫實際要執行的程式碼，例如：處理 podcast 上傳、轉檔、通知等
    }
}
```

- 可直接將 `Eloquent Model` 傳入 `建構子`，Laravel 會 __自動序列化/還原__。
- 只會 __序列化 model 的主鍵__，`執行時自動查回完整 model`。

---

### *handle 方法依賴注入*

- `handle` 方法可 `型別提示` 依賴，Laravel 會自動注入。
- 進階：可用 `bindMethod` **自訂注入邏輯**。

```php
// AppServiceProvider
use App\Jobs\ProcessPodcast; // 載入你自訂的 Job 類別
use App\Services\AudioProcessor; // 載入你要注入的服務類別
use Illuminate\Contracts\Foundation\Application; // 載入 Laravel 應用程式容器介面

// 透過 app()->bindMethod 綁定 Job 的 handle 方法，客製化依賴注入邏輯
$this->app->bindMethod([
    ProcessPodcast::class, // 指定要綁定的 Job 類別
    'handle' // 指定要綁定的方法名稱
], function (ProcessPodcast $job, Application $app) { // 這裡會自動注入 Job 實例與 Application 容器
    // 使用容器解析出 AudioProcessor 服務，並傳給 handle 方法
    return $job->handle($app->make(AudioProcessor::class));
    // 這樣 handle 方法就能取得 AudioProcessor 物件作為參數
});
```

- _二進位資料_（如圖片內容）建議先 `base64_encode` 再傳入 Job，`避免序列化失敗`。

---

### *關聯序列化注意*

- __Model 的`已載入關聯`也會被序列化，可能`導致 payload 很大`__。
- 若只需`主 model`，可用 `withoutRelations()`：

```php
public function __construct(Podcast $podcast) {
    $this->podcast = $podcast->withoutRelations();
}
```

---

- *PHP 8+ 可用屬性標註*：

```php
use Illuminate\Queue\Attributes\WithoutRelations;
public function __construct(
    #[WithoutRelations]
    public Podcast $podcast,
) {}
```

---

- 若傳入 __model 集合__，集合內的 model __不會自動還原`關聯`__。

<!-- 不會自動還原是指：
     當 model 被序列化後放進 queue（例如 Redis、資料庫），
     只會序列化 model 本身的屬性，
     不會序列化 model 的關聯資料（如 $model->user、$model->comments）。
     等 queue worker 執行 job 時，會反序列化還原 job 和 model，
     但 model 的關聯資料還是空的，
     你要自己用 $model->load('user') 等方法重新載入關聯。 -->

<!-- 流程說明：
     1. job 放進 queue 時，會序列化 job（包含 model）並存到 queue 後端（如 Redis）。
     2. queue worker 執行時，會從 queue 取出 job，反序列化還原 job 和 model。
     3. 還原後只會有 model 本身的資料，關聯資料需要自行載入。
     4. 最後執行 job 的 handle 方法。
-->

---

## **Unique Jobs**（唯一任務）

- 需支援 __lock__ 的 cache driver（`memcached、redis、dynamodb、database、file、array`）。
- 實作 `ShouldBeUnique` 介面即可：

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
class UpdateSearchIndex implements ShouldQueue, ShouldBeUnique {}
```

---

- __可自訂唯一 key 與逾時秒數__：

```php
public $uniqueFor = 3600; // 1 小時
public function uniqueId(): string { 
    return $this->product->id; 
    }
```

---

- __可自訂 lock 用的 cache driver__：

```php
public function uniqueVia(): Repository { return Cache::driver('redis'); }
```

- 若要「__直到開始處理前__」都唯一，改實作 `ShouldBeUniqueUntilProcessing`。

---

## **Encrypted Jobs**（加密任務）

- 實作 `ShouldBeEncrypted` 介面即可：

```php
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
class UpdateSearchIndex implements ShouldQueue, ShouldBeEncrypted {}
```

- Laravel 會 __自動加密 job payload__，確保資料隱私與完整性。

---

## **其他補充**

- 若只需 *限制同時執行數量*，請用 `WithoutOverlapping` middleware。
<!-- 讓排程任務「不重疊執行」，避免多個相同任務同時進行造成資源衝突。 -->

<!-- 
如果沒有加 withoutOverlapping，
排程任務有可能會重疊執行，
例如前一次還沒跑完，下一次排程又啟動，
就會同時執行多個相同任務。 
-->

- *Laravel Horizon* 適用於 __Redis 佇列__，提供即時監控與管理。
- 佇列 worker 可指定多個 queue，依優先順序處理。
- `php artisan queue:work` 啟動 worker，`php artisan queue:listen`（已不建議使用）。
- 可用 `php artisan queue:failed` __管理失敗任務__。 

---

## **Job Middleware**（任務中介層）

### *什麼是 Job Middleware？*

- Job middleware 讓你可以 _將自訂邏輯包裹在 queued job 執行前後_，減少重複程式碼。
- 常見用途：
            __限流（Rate Limiting）__、
            __防止重疊（Without Overlapping）__、
            __異常節流（ThrottlesExceptions）__、
            __條件跳過（Skip）__ 等。

- middleware 實作類似 route middleware，_會接收 job 與下一步 callback_。

---

- artisan 指令：

```bash
php artisan make:job-middleware RateLimited
```

---

### *自訂 Job Middleware 範例*

```php
namespace App\Jobs\Middleware; // 定義命名空間，方便自動載入
use Closure; // 匿名函式型別，用於 middleware 的下一步 callback
use Illuminate\Support\Facades\Redis; // 匯入 Redis 門面，方便操作 Redis

class RateLimited { // 定義一個自訂的 Middleware 類別
    public function handle(object $job, Closure $next): void { // handle 方法，接收當前 job 與下一步 callback
        Redis::throttle('key') // 建立一個名為 'key' 的限流器（可自訂 key 名稱）
            ->block(0) // 「取得鎖」時最多等待 0 秒（不等待，立即回應）
            ->allow(1) // 每個時間區間只允許 1 次通過
            ->every(5) // 每 5 秒為一個區間
            ->then(function () use ($job, $next) { // 如果「取得鎖」，執行這個 callback
                $next($job); // 執行下一步（即真正執行 job）
            }, function () use ($job) { // 如果「沒取得鎖」，執行這個 callback
                $job->release(5); // 釋放 job，5 秒後再重試
            });
    }
}
```

---

- __job 內__ 加上 middleware 方法：

```php
namespace App\Jobs;

use App\Jobs\Middleware\RateLimited;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExampleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function middleware(): array
    {
        // 定義 middleware 方法，回傳要套用的中介層陣列
        return [new RateLimited]; // 套用自訂的 RateLimited middleware，讓這個 Job 執行時會限流
    }

    public function handle()
    {
        // Job 的執行邏輯
    }
}
```

---

### *Rate Limiting*（限流）

- Laravel 內建 `Illuminate\Queue\Middleware\RateLimited` 與 `RateLimitedWithRedis`。
- 先在 `AppServiceProvider` 註冊 rate limiter：

```php
use Illuminate\Cache\RateLimiting\Limit; // 匯入限流器的 Limit 類別
use Illuminate\Support\Facades\RateLimiter; // 匯入 RateLimiter 門面
public function boot(): void { // AppServiceProvider 的 boot 方法
    RateLimiter::for('backups', function (object $job) { // 註冊一個名為 'backups' 的限流器，針對 job 物件
        return $job->user->vipCustomer() // 判斷這個 job 的 user 是否為 VIP
            ? Limit::none() // VIP 不限流
            : Limit::perHour(1)->by($job->user->id); // 非 VIP 每小時只允許 1 次，依 user id 區分
    });
}
```

---

- __job 內__ middleware：

```php
use Illuminate\Queue\Middleware\RateLimited; // 匯入 Laravel 內建的 RateLimited middleware
public function middleware(): array { // 定義 middleware 方法
    return [new RateLimited('backups')]; // 套用剛剛註冊的 'backups' 限流器
}
// 可用 releaseAfter(秒) 或 dontRelease() 控制重派行為
// RateLimitedWithRedis 效能更佳，適合 Redis driver
```

---

### *Preventing Job Overlaps*（防止重疊）

- 使用 `Illuminate\Queue\Middleware\WithoutOverlapping`。
- 適合 __同一資源只允許一個 job 處理__ 的情境。

```php
use Illuminate\Queue\Middleware\WithoutOverlapping; // 匯入 Laravel 內建的防重疊 middleware

public function middleware(): array { // 定義 middleware 方法
    // 套用 WithoutOverlapping middleware，傳入唯一 key（如 user id），確保同一資源同時只會有一個 job 執行
    return [new WithoutOverlapping($this->user->id)];
}
// 可用 releaseAfter(秒) 設定重派延遲秒數
// 可用 dontRelease() 遇到重疊時不重派
// 可用 expireAfter(秒) 設定 lock 過期秒數
// 可用 shared() 讓多個 job class 共用同一 lock key
```

- 需支援 __lock__ 的 cache driver（`memcached、redis、dynamodb、database、file、array`）。

---

### *Throttling Exceptions*（異常節流）

- 使用 `Illuminate\Queue\Middleware\ThrottlesExceptions` 或 `ThrottlesExceptionsWithRedis`。
- 當 job __連續丟出異常`達`指定次數__，延遲一段時間再重試。

```php
use Illuminate\Queue\Middleware\ThrottlesExceptions; // 匯入 Laravel 內建的異常節流 middleware
public function middleware(): array { // 定義 middleware 方法
    return [new ThrottlesExceptions(10, 5 * 60)]; // 10 次異常，延遲 5 分鐘再重試
}
public function retryUntil(): \DateTime { // Laravel Job 內建可覆寫的方法，用來設定這個 Job 最晚可以重試到什麼時候
    return now()->addMinutes(30); // 只會在 30 分鐘內重試，超過這個時間就不再重試
}
// 可用 backoff(分鐘) 設定每次重試的延遲時間
// by('key') 可自訂唯一 key
// when(fn($e)=>...) 可根據例外類型決定是否節流
// deleteWhen(例外類) 指定遇到哪些例外直接刪除 job
// report(fn($e)=>...) 可自訂例外回報行為
```

---

### *Skipping Jobs*（條件跳過）

- 使用 `Illuminate\Queue\Middleware\Skip`。
- 可 __根據條件 `直接刪除` job__，不進行處理。

```php
use Illuminate\Queue\Middleware\Skip; // 匯入 Laravel 內建的條件跳過 middleware

public function middleware(): array { // 定義 middleware 方法，回傳要套用的中介層陣列
    return [
        Skip::when($someCondition), // 如果 $someCondition 為 true，這個 job 會被直接刪除（不執行 handle）
        // 或 Skip::unless(fn()=>...) // 也可以用函式動態判斷條件，為 false 時跳過 job
    ];
}
```

---

### *補充*

- middleware 方法需回傳「_物件陣列_」而非字串。
- middleware 也可用於 __queueable__ __event__ __listeners__、__mailables__、__notifications__。
- `release/dontRelease` 會影響 _job attempts_ 次數，請調整 `tries/maxExceptions/retryUntil`。
- `Redis` driver 請優先用 `WithRedis` 版本 middleware（`RateLimitedWithRedis`），效能最佳。 

---

## Job **派送**（Dispatching Jobs）

### 1.1 *基本派送*

- `Job::dispatch($arg1, $arg2, ...)`：__將任務派送到 queue__，參數會傳給`建構子`。
<!-- 靜態方法，直接呼叫 Job 類別的 dispatch，
     這種寫法通常是 Job 類別內建的（不是 Facade）。 -->

- `dispatch(new Job(...))`：等同於上方寫法。
<!-- Laravel 的全域 helper 函式，
     可以派送任何 Job 物件到 queue。 -->
---

### 1.2 *條件派送*

- `Job::dispatchIf($condition, $args...)`：條件為 `true` 才派送。
- `Job::dispatchUnless($condition, $args...)`：條件為 `false` 才派送。

---

### 1.3 *延遲派送*

- `->delay($datetimeOrSeconds)`：__延遲一段時間後__，才可被 `worker` 處理。
- `->withoutDelay()`：__忽略 Job 預設 delay__，*立即派送*。

---

### 1.4 *回應後派送*

- `Job::dispatchAfterResponse($args...)`：__HTTP 回應送出後__，才執行（不需 worker，適合短任務）。
- `dispatch(fn()=>...)->afterResponse()`：派送 closure，回應後執行。

---

### 1.5 *同步派送*

- `Job::dispatchSync($args...)`：立即 __同步執行__，_不進 queue_。

---

### 1.6 *指定 queue/connection*

- `->onQueue('queue_name')`：指定 _queue 名稱_。
- `->onConnection('connection_name')`：指定 `queue 連線`。
- 也可 __在 Job 建構子內__ 呼叫 `$this->onQueue()`、`$this->onConnection()`。

<!-- 
「連線名稱」就是指後端服務的名稱，用來指定後端 queue 服務的連線名稱，
例如你有多個 queue 伺服器（如 redis、database、sqs），
可以用這個方法指定要用哪一個後端服務來處理任務。 
-->

---

### 1.7 *交易後派送*

- `->afterCommit()`：資料庫交易 `commit` 後才派送（需 driver 支援）。
- `->beforeCommit()`：即使 `after_commit` 設定為 `true`，__也可強制立即派送__。

---

### 1.8 *Job Chaining*

- `Bus::chain([...])->dispatch()`：__多個 Job 依序執行__，前一個成功才執行下一個。
- 可用 `catch(fn($e)=>...)` __捕捉__ 鏈中任一 Job 失敗。
- 可用 `prependToChain()`、`appendToChain()` __動態插入 Job__。

---

## **Job 失敗、重試、逾時、例外處理**

### 1. *artisan 指令操作*（在終端機執行，不寫在 PHP 程式碼裡）

這些指令用於啟動 __queue worker、查詢/重試/刪除失敗任務__ 等，請在`終端機`（Terminal）輸入：

```bash
php artisan queue:work --tries=3 # 啟動 worker，最多重試 3 次

# --tries=3 的意思是：
# 如果 queue 任務執行失敗，
# Laravel 會最多重試 3 次（包含第一次執行），
# 如果還是失敗，這個任務就不再執行，會進入失敗任務（failed jobs）記錄。

php artisan queue:failed         # 「查詢」所有失敗任務
php artisan queue:retry {id}     # 「重試」指定失敗任務
php artisan queue:forget {id}    # 「刪除」指定失敗任務
php artisan queue:flush          # 「清空」所有失敗任務
```

---

- 這些指令*不用寫在 PHP 程式碼裡*，只要在命令列執行即可。

- `queue:work` 會 __啟動一個 worker 持續處理 queue 裡的任務__。
- `queue:failed` 會 __列出所有失敗__ 的 job（例如 `資料庫連線失敗、API timeout` 等）。
- `queue:retry` 可以讓你針對失敗的 job __再次嘗試執行__。
- `queue:forget` 可以 __刪除單一失敗__ job 記錄。
- `queue:flush` 會 __清空所有失敗__ job 記錄。

---

### 2. *Job 類別內的屬性與方法*（要寫在 PHP 程式碼裡）

這些 `屬性` 與 `方法` 要寫在你自訂的 `Job 類別`（通常在 `app/Jobs/ 目錄下`），用來控制這個 job 的 __重試、逾時、失敗__ 行為。

---

#### **範例**

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailJob implements ShouldQueue // 這個 job 會進 queue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // 最大重試次數，失敗最多重派 3 次
    public $timeout = 60; // 單次執行最多 60 秒，超過會被強制終止
    public $failOnTimeout = true; // 逾時時直接標記為失敗，不再重試

    public function handle()
    {
        // 這裡寫實際要執行的邏輯，例如寄信
        // ...
    }

    public function retryUntil(): \DateTime // 設定重試截止時間
    {
        return now()->addMinutes(10); // 只會在 10 分鐘內重試，超過就不再重試
    }

    public $maxExceptions = 2; // 允許最多 2 次未處理例外，超過就直接失敗

    public function failed(\Exception $e) // job 失敗時自動呼叫
    {
        Log::error('寄信任務失敗：' . $e->getMessage()); // 可在這裡通知管理員、寫 log、補償等
    }
}
```

- `$tries`：這個 job __最多會重試幾次__（不含第一次執行）。
- `$timeout`：__單次執行最多允許幾秒__，超過會被強制終止。
- `$failOnTimeout`：如果逾時，是否直接標記為失敗（true = 不再重試）。
- `retryUntil()`：回傳一個時間點，__超過這個時間就不再重試__。
- `$maxExceptions`：允許 __最多幾次未處理例外__，超過就直接失敗。
- `failed(Exception $e)`：job __失敗時自動呼叫__，可在這裡做`通知、補償、記錄`等。

---

#### **進階補充與常見細節**

##### (1) *`$this->release()` 與 `$this->fail()` 用法*

你可以在 Job 的 `handle()` 內 **主動釋放（並重試）** 或 **標記失敗**：

```php
public function handle()
{
    try {
        // ...執行主要邏輯
    } catch (\SomeTemporaryException $e) {
        $this->release(10); // 10秒後重試這個 job
    } catch (\Exception $e) {
        $this->fail($e); // 直接標記這個 job 失敗，並觸發 failed() 方法
    }
}
```

- `$this->release(10)`：將 job __釋放__ 回 queue，10 秒後再 __重試__。
- `$this->fail($e)`：直接 __標記__ job 失敗，並觸發 `failed()` 方法。

---

##### (2) *`failed_jobs` 資料表*

- 所有失敗的 job 都會被記錄在 `failed_jobs` 資料表（除非你用 `sync` driver）。
- 你可以用 artisan 指令查詢、重試、刪除這些失敗任務。

---

##### (3) *`FailOnException` Middleware 用法*

遇到 __特定例外時__ job 直接失敗，不再重試：

```php
// app/Jobs/SendEmailJob.php
use App\Exceptions\AuthorizationException; // 匯入你自訂的例外類別，這裡假設有一個授權例外
use Illuminate\Queue\Middleware\FailOnException; // 匯入 Laravel 內建的 FailOnException middleware

public function middleware(): array // 定義 middleware 方法，回傳要套用的中介層陣列
{
    return [
        new FailOnException([AuthorizationException::class]) // 當遇到 AuthorizationException 這個例外時，job 會直接標記為失敗，不再重試
    ];
}
```

---

##### (4) *釋放與重試注意事項*

- 若 Job **逾時**，會 __被 worker 標記為失敗__（根據 `$failOnTimeout` 設定）。
- 若 Job **釋放**（`release`），__會重新進 queue__，重試次數會累加。
- 若 Job **失敗**（`fail`），會進入 `failed_jobs` 表。

---

##### (5) *`tries()`、`backoff()` 動態設定補充*

- 你可以用方法動態決定 __最大嘗試次數__ 或 __重試間隔__：

```php
public function tries(): int // 動態回傳最大嘗試次數
{
    return $this->user->isVip() ? 10 : 3;
}

public function backoff(): int|array // 動態設定每次重試的延遲秒數
{
    return [10, 30, 60]; // 第一次重試等10秒，第二次30秒，第三次60秒...
}
```

---

### 3. *總結*

- __artisan 指令__：只在 `終端機` 執行，不寫在 PHP 程式裡。
- __Job 屬性/方法__：要寫在你自訂的 `Job 類別` 裡，控制 *重試、逾時、失敗* 等行為。
- 兩者搭配，讓你能彈性管理 queue 任務的執行與失敗處理。

---

## Job **派送方法與屬性表格**

### *派送方法總覽*

| 方法                     | 說明                                   |
|-------------------------|----------------------------------------|
| `dispatch`              | 一般派送                               |
| `dispatchIf`            | 條件為 true 才派送                     |
| `dispatchUnless`        | 條件為 false 才派送                    |
| `dispatchAfterResponse` | 回應後派送（不需 worker）               |
| `dispatchSync`          | 立即同步執行                           |
| `onQueue`               | 指定 queue 名稱                        |
| `onConnection`          | 指定 queue 連線                        |
| `delay`                 | 延遲派送                               |
| `withoutDelay`          | 忽略預設 delay                         |
| `afterCommit`           | 交易 commit 後才派送                    |
| `beforeCommit`          | 強制立即派送（即使 after_commit=true）  |
| `chain` (__Bus::chain__)| Job 鏈                                 |

---

### *失敗/重試/逾時屬性與方法*

| 屬性/方法            | 說明                              |
|---------------------|----------------------------------|
| `$tries`            | 最大嘗試次數                       |
| `tries()`           | 動態回傳最大嘗試次數                |
| `$timeout`          | 單一 job 執行逾時秒數              |
| `$failOnTimeout`    | 逾時時直接標記為失敗                |
| `retryUntil()`      | 指定重試截止時間                   |
| `$maxExceptions`    | 最大未處理例外次數                  |
| `failed(Exception)` | Job 失敗時自動呼叫                 |
| `$this->release()`  | 手動釋放回 queue                   |
| `$this->fail()`     | 手動標記為失敗                     |

---

## **範例輔助**

```php
// 條件派送
ProcessPodcast::dispatchIf($user->isActive(), $podcast);
ProcessPodcast::dispatchUnless($user->isBanned(), $podcast);

// 延遲派送
ProcessPodcast::dispatch($podcast)->delay(now()->addMinutes(5));

// 回應後派送
ProcessPodcast::dispatchAfterResponse($podcast);

// 同步派送
ProcessPodcast::dispatchSync($podcast);

// 指定 queue/connection
ProcessPodcast::dispatch($podcast)->onQueue('audio')->onConnection('redis');

// 交易後派送
ProcessPodcast::dispatch($podcast)->afterCommit();

// Job Chaining
Bus::chain([
    new ProcessPodcast($podcast),
    new OptimizePodcast($podcast),
    new ReleasePodcast($podcast),
])->catch(function(Throwable $e) {
    // 任一 Job 失敗時處理
})->dispatch();
```

---

## **實務提醒**

- _after commit_ 設定建議於所有`生產環境`開啟，避免交易未 commit 就執行 Job。
- _dispatchAfterResponse_ 適合`短任務`（如 *寄信* ），不建議用於長任務。
- _Job Chaining_ 只要`有一個 Job 失敗`，`後續不會執行`。
- _FailOnException_ 適合區分`可重試`與`不可重試`的例外。
- _Job 逾時_ 請確保 `timeout < retry_after`，否則可能重複執行。

<!-- 如果 `timeout > retry_after`，queue worker 可能在前一個 Job 還沒逾時結束時就重新分派同一個 Job，
     導致同一個 Job 被重複執行，造成資料不一致或重複處理問題。 -->

<!-- 
timeout
指 queue worker 執行一個 Job 的「最大允許時間」
超過這個時間，worker 會強制終止 Job 
-->

<!-- 
retry_after
指 queue 系統認定 Job「執行失敗」後，多久才允許重新分派這個 Job
這個時間是 queue 服務（如 Redis、Database）用來判斷 Job 是否卡住、可重派 
-->

<!-- 
Worker 領取 Job 後，queue 服務就開始計算 retry_after 時間。
如果 worker 在 retry_after 之前沒回報 Job 完成，queue 會認為 Job 卡住，重新分派給其他 worker。
如果 timeout 設得比 retry_after 大，worker 可能還在執行，但 queue 已經重派同一個 Job，造成重複執行，導致資料重複或不一致。
-->

<!-- 
timeout 和 retry_after 是同時開始計算的：
當 worker 領取 Job 時，
timeout 是 worker 最長執行時間
retry_after 是 queue 服務等待 worker 完成的時間
如果 worker 沒在 retry_after 內完成，queue 會重派；
如果 timeout 設得比 retry_after 大，就有重複執行的風險。 
-->

```php
$timeout = 60;      // Job 執行逾時秒數（例如 60 秒）
$retry_after = 90;  // queue 設定的重試等待秒數（例如 90 秒）

// 這樣 timeout < retry_after，確保 Job 逾時後不會被 queue worker 重複執行
```
---

## **Job Batching**（任務批次）

### *前置作業*

- 需建立 *job_batches* 資料表：

```bash
php artisan make:queue-batches-table
php artisan migrate
```

---

### *定義 Batchable Job*

- job 類別需 use `Illuminate\Bus\Batchable` trait：

```php
use Illuminate\Bus\Batchable; // 匯入 Batchable trait，讓 job 支援批次功能

class ImportCsv implements ShouldQueue { // 定義一個批次匯入 CSV 的 job，需實作 ShouldQueue
    use Batchable, Queueable; // 使用 Batchable trait（批次功能）和 Queueable trait（queue 相關功能）

    public function handle(): void {
        // if ($this->batch()->cancelled()) { // 檢查這個批次是否已被取消
        //     return; // 若已取消，直接結束，不執行後續邏輯
        // }
        if ($this->batch()?->cancelled()) return; 
        // 取得 batch 物件（若有），若 batch 已被取消則直接 return，不執行後續邏輯。
        // 這裡的 ?-> 是 PHP 8+ 的 nullsafe operator，代表 batch() 回傳 null 時不會報錯，直接回傳 null。cancelled() 判斷批次是否被取消。
        // ...這裡寫實際要執行的批次任務內容
    }
}
```

<!-- 外面 use 是引入 class、trait、interface 的名稱，讓你可以在程式裡用這個名稱（例如 new ClassName() 或    ClassName::class）。
     trait 只有在 class 裡用 use TraitName; 才會把 trait 的方法加進 class，讓你可以直接用 $this->method()。
     外面 use 不能直接用 trait 的方法，只有 class 用了 trait 才能用。
     外面 use 引入 class，可以用 new 來建立實例並呼叫方法；
     外面 use 引入 trait，不能直接 new，也不能直接呼叫 trait 的方法。 -->

---

### *派送批次任務*

- 使用 `Bus::batch([...])`，可搭配 __before/progress/then/catch/finally callback__：

```php
use Illuminate\Support\Facades\Bus; // 匯入 Bus 門面，提供批次派送功能
use Illuminate\Bus\Batch; // 匯入 Batch 型別，callback 會用到

$batch = Bus::batch([
    new ImportCsv(1, 100), // 建立一個 ImportCsv job，處理第 1~100 筆
    new ImportCsv(101, 200), // 再建立一個 ImportCsv job，處理第 101~200 筆
    // ...可依需求加入更多 job
])->before(function (Batch $batch) { // before callback：批次建立但尚未加入 job 時觸發
    // 這裡可做初始化、記錄等
})->progress(function (Batch $batch) { // progress callback：每個 job 完成時觸發
    // 可用於即時進度條、記錄進度
})->then(function (Batch $batch) { // then callback：全部 job 成功時觸發
    // 可在這裡做後續處理（如通知、彙整結果）
})->catch(function (Batch $batch, Throwable $e) { // catch callback：第一個 job 失敗時觸發
    // 可在這裡記錄錯誤、通知管理員
})->finally(function (Batch $batch) { // finally callback：批次結束時（不論成功或失敗）都會觸發
    // 可做收尾、資源釋放等
})->dispatch(); // 派送批次任務到 queue，開始執行
```

- *$batch->id* 可用於 __查詢批次狀態__。
- __callback 內不可用 $this__。
- 批次 job 會包在 *DB transaction*，勿在 job 內執行`隱性 commit` 的 SQL。

<!-- 
callback 內不可用 $this：
這些 callback 是獨立的閉包（closure），不是某個物件的方法，
所以 $this 在 callback 內沒有指向任何物件，會出現錯誤。
-->

<!-- 
隱性 commit是指：
當你在資料庫交易（transaction）中執行某些 SQL 指令時，
資料庫會自動提交（commit）交易，
即使你沒有手動執行 COMMIT，交易也會提前結束。

常見會造成隱性 commit 的 SQL 指令：

CREATE TABLE
ALTER TABLE
DROP TABLE
CREATE INDEX
DROP INDEX
TRUNCATE TABLE
RENAME TABLE
這些指令屬於 DDL（資料定義語言），
只要執行就會讓資料庫自動 commit，
導致原本的交易提前結束，
可能造成資料不一致或交易失效。

一般 DML（資料操作語言，如 INSERT、UPDATE、DELETE）不會隱性 commit。
-->

<!-- 
隱性 commit通常是因為執行「跟資料表結構有關」的指令（DDL），
像是新增、修改、刪除資料表或索引，
這些操作會讓資料庫自動提交交易，
和一般只操作資料內容（DML）不同。 
-->

<!-- 
批次 job 會包在 DB transaction，勿在 job 內執行隱性 commit 的 SQL：
Laravel 批次任務會自動用資料庫交易包住所有 job，
如果你在 job 內執行隱性 commit（如 DDL、ALTER、CREATE），
會導致交易提前結束或失效，造成資料不一致或交易失敗。 
-->

---

### *命名批次*

- 方便 `Horizon/Telescope` 顯示：

```php
Bus::batch([...])->name('Import CSV')->dispatch();
```

---

### *指定連線與 queue*

- 批次內所有 job __必須同一 `connection/queue`__：

```php
Bus::batch([...])->onConnection('redis')->onQueue('imports')->dispatch();
```

---

### *批次內鏈*（chains）

- 批次陣列內可放 **job 陣列**，代表一組 chain 會平行執行：

```php
Bus::batch([
    [new ReleasePodcast(1), new SendPodcastReleaseNotification(1)], // 這一組 chain：先 ReleasePodcast(1)，完成後再 SendPodcastReleaseNotification(1)；(1) 代表第1個 Podcast 的 id
    [new ReleasePodcast(2), new SendPodcastReleaseNotification(2)], // 這一組 chain：先 ReleasePodcast(2)，完成後再 SendPodcastReleaseNotification(2)；(2) 代表第2個 Podcast 的 id
    // ...可依需求加入更多 chain
])->then(function (Batch $batch) { /* ... */ })->dispatch(); // then callback：全部 chain 都成功時觸發，dispatch() 派送批次
```

---

- __每一個`陣列`（[]）代表一組 `chain`__，會依序執行，所有 chain 會`平行處理`。

```php
Bus::chain([
    new FlushPodcastCache, // 先執行 FlushPodcastCache job
    Bus::batch([new ReleasePodcast(1), new ReleasePodcast(2)]), // 再平行執行這兩個 ReleasePodcast job，(1)(2) 代表不同 Podcast 的 id
    Bus::batch([new SendPodcastReleaseNotification(1), new SendPodcastReleaseNotification(2)]), // 最後平行執行這兩個通知 job，(1)(2) 代表不同 Podcast 的 id
])->dispatch(); // dispatch() 派送整個 chain
```

- 你可以在 __chain 裡包 batch__，也可以在 __batch 裡包 chain__，彈性組合多種執行順序。

---

### *動態加入 job*

- 在 batch job 內可用 `$this->batch()->add([...])` __動態加入 job__：

```php
public function handle(): void { // Job 的 handle 方法，執行批次任務的主邏輯
    // if ($this->batch()->cancelled()) return; 
    if ($this->batch()?->cancelled()) return; 
    // 若批次已被取消，直接結束，不執行後續。
    // 這裡的 ?-> 是 PHP 8+ 的 nullsafe operator，代表 batch() 回傳 null 時不會報錯，直接回傳 null。cancelled() 判斷批次是否被取消。
    $this->batch()->add( // 動態加入新的 job 到同一個 batch
        Collection::times(1000, fn()=>new ImportContacts) // 建立 1000 個 ImportContacts job 並加入 batch
    );
}
```

- 只能在 __同一 batch 內__ 的 job 動態加入。

---

### *檢查與操作批次*

- Batch 實例常用 __屬性/方法__：

```php
$batch->id; // 批次的唯一識別碼（UUID）
$batch->name; // 批次名稱
$batch->totalJobs; // 批次內所有 job 的總數
$batch->pendingJobs; // 尚未執行的 job 數量
$batch->failedJobs; // 失敗的 job 數量
$batch->processedJobs(); // 已處理（成功+失敗）的 job 數量
$batch->progress(); // 批次完成百分比（0~100）
$batch->finished(); // 批次是否已完成（布林值）
$batch->cancel(); // 取消整個批次
$batch->cancelled(); // 批次是否已被取消（布林值）
```

---

### *路由查詢批次狀態*

- Batch 可直接回傳為 `JSON`，方便前端查詢進度：

```php
Route::get('/batch/{batchId}', fn($id) => Bus::findBatch($id)); // 定義一個 GET 路由，路徑為 /batch/{batchId}
// $id 會自動對應到 URL 的 {batchId} 參數
// Bus::findBatch($id) 會查詢該批次的狀態並回傳 Batch 實例（可自動轉成 JSON 給前端）
```

---

### *取消批次*

- job 內可用 `$this->batch()->cancel();` __取消整個批次__。
- job 內建議 __先判斷 cancelled__ 再執行。
- 也可用 `middleware`：

```php
use Illuminate\Queue\Middleware\SkipIfBatchCancelled; // 匯入 Laravel 內建的批次取消判斷 middleware

public function middleware(): array { // 定義 middleware 方法
    return [new SkipIfBatchCancelled]; // 若批次已被取消，這個 job 會自動跳過不執行
}
```

---

### *失敗與允許失敗*

- **預設** __job 失敗會取消整個 batch__，catch callback 只會觸發一次。

- 若 __允許部分 job 失敗__，不影響整體：

```php
Bus::batch([...])->allowFailures()->dispatch(); // 派送批次任務，allowFailures 允許部分 job 失敗，整個 batch 不會被取消
```

---

- `queue:retry-batch` 可重試失敗 job：

```bash
php artisan queue:retry-batch 批次UUID # 針對指定批次（以 UUID 標識）的失敗 job 重新派送執行。
```

---

### *批次資料清理*（`prune`）

- **job_batches** 表 _會快速累積_，建議定期清理：

```php
Schedule::command('queue:prune-batches')->daily();

// 只保留 48 小時內：
Schedule::command('queue:prune-batches --hours=48')->daily();

// 清理未完成批次：
Schedule::command('queue:prune-batches --hours=48 --unfinished=72')->daily();

// 清理已取消批次：
Schedule::command('queue:prune-batches --hours=48 --cancelled=72')->daily();
// 72 代表「超過 72 小時」的批次任務才會被清理。
```

---

### *DynamoDB 支援*

- 可將`批次資料`存 DynamoDB，需先建表（主鍵 `application, id`）。
- 設定 `queue.batching.driver = dynamodb`，並設 `key/secret/region/table`。
- 建議設 `ttl 屬性`，__啟用自動清理__：

```php
'batching' => [
    'driver' => 'dynamodb', // 使用 DynamoDB 作為批次任務儲存後端
    'key' => env('AWS_ACCESS_KEY_ID'), // AWS 金鑰
    'secret' => env('AWS_SECRET_ACCESS_KEY'), // AWS 密鑰
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'), // AWS 區域
    'table' => 'job_batches', // 批次任務儲存的 DynamoDB 資料表名稱
    'ttl_attribute' => 'ttl', // 設定 DynamoDB 的 TTL 欄位名稱
    'ttl' => 60 * 60 * 24 * 7, // 批次任務的存活時間（7 天）
],
```

- `DynamoDB` 批次清理靠 `TTL`，不用 `queue:prune-batches`。

---

### *補充*

- __批次 callback 內不可用 $this__。
- 批次 job 會包在 `DB transaction`，勿執行 _隱性 commit SQL_。
- `allowFailures` 適合大量任務 _容忍部分失敗_。
- `SkipIfBatchCancelled` middleware 可自動 _略過_ 已取消批次的 job。
- `Bus::findBatch` 可 _查詢_ 批次進度，適合前端輪詢。 

---

## **Queueing Closures 與 Worker 實務**

### *派送 Closure 到 Queue*

- 可直接用 `dispatch(function(){ ... })` 派送 `closure` 到 `queue`，適合簡單、臨時性任務。
- closure 內容會 *加密簽名*，確保傳輸安全。

```php
$podcast = App\Podcast::find(1);
dispatch(function () use ($podcast) {
    $podcast->publish();
});
```

---

- 可用 `name()` 指定 __closure 任務名稱__，方便 `queue:work/Horizon` 顯示：

```php
dispatch(function () { /* ... */ })->name('Publish Podcast');
```

---

- 可用 `catch()` 註冊失敗 callback（__不可用 $this__）：

```php
use Throwable;
dispatch(function () use ($podcast) {
    $podcast->publish();
})->catch(function (Throwable $e) {
    // 任務失敗時執行
});
```

---

### *啟動 Queue Worker*

- 用 `php artisan queue:work` __啟動 worker，持續處理新進任務，直到手動停止__。

```bash
php artisan queue:work
```

---

- 建議用 `Supervisor` 等 `process manager` __讓 worker 永遠在背景執行__。
- 加 `-v` 參數可顯示 __詳細 job id、connection、queue 名稱__：

```bash
php artisan queue:work -v
```

- __worker 啟動後不會自動載入新程式碼__，部署時需 `queue:restart`。
- `php artisan queue:listen` 會每次 job 都重載程式碼，__適合開發但效能較差__。

---

### *Worker 參數與多工*

- 指定 __連線與 queue__：

```bash
php artisan queue:work redis --queue=emails # 啟動 worker，指定使用 redis 連線，並只處理 emails 這個 queue
```

- __多 worker 可同時處理多個 queue__，或 __同一 queue 多 worker__：
  - 多開`終端機`或用 **Supervisor** 設定 `numprocs`。 
    - numprocs 代表 *同時啟動幾個 worker 處理任務* ，可提升併發處理能力

  - 指令範例：
    ```php
    # 多個終端機分別執行，處理不同 queue
    `php artisan queue:work redis --queue=emails`
    `php artisan queue:work redis --queue=notifications`
    `php artisan queue:work redis --queue=default`
    # 或同一 queue 開多個 worker
    `php artisan queue:work redis --queue=emails`
    ```

---

  - `Supervisor` 設定範例（推薦生產環境自動多工）：

    ```php
    [program:laravel-worker]
        process_name=%(program_name)s_%(process_num)02d  ; 設定 process 名稱格式
        command=php /path/to/artisan queue:work redis --queue=emails ; 執行 queue worker 指令，指定 redis 與 emails queue
        numprocs=5                                       ; 啟動 5 個 worker
        autostart=true                                   ; 系統啟動時自動啟動 worker
        autorestart=true                                 ; worker 異常時自動重啟
        user=forge                                       ; 指定執行 worker 的使用者
        redirect_stderr=true                             ; 錯誤訊息導向標準輸出
        stdout_logfile=/path/to/worker.log               ; 設定 worker 的 log 檔案路徑
    ```
---

- *只處理一個 job*：

```bash
php artisan queue:work --once
```

---

- 處理 __指定數量後自動結束__：

```bash
php artisan queue:work --max-jobs=1000
```

---

- 處理完 __所有任務後自動結束__（適合 Docker）：

```bash
php artisan queue:work --stop-when-empty
```

---

- 處理 __指定秒數後自動結束__：

```bash
php artisan queue:work --max-time=3600
```

---

- __無任務時 sleep 秒數__：

```bash
php artisan queue:work --sleep=3
```

---

- __維護模式__ 下，**預設**不處理任務，`--force` 可強制執行：

```bash
php artisan queue:work --force
# 當 queue 沒有新任務時，
# worker 會暫停（sleep）3 秒再繼續檢查是否有新任務，
# 可以降低 CPU 使用率，避免持續空轉。
```

---

### *Worker 資源與部署*

- worker 為 **長駐程式**，_記憶體不會自動釋放，建議定期重啟_。
- 處理大量圖片等資源時，請 **主動釋放**（如 `imagedestroy`）。
- worker 啟動後 **不會自動載入新程式碼**，部署時務必 `queue:restart`：

```bash
php artisan queue:restart
```

- `queue:restart` 會讓 worker 處理完當前 job 後優雅結束，需搭配 `Supervisor` 自動重啟。
- `queue:restart` 需正確設定 __cache driver__。

---

### *任務優先權與多 queue*

- 可將 _重要任務_ 派送到 **high queue**，_次要任務_ 派送到 **low queue**：

```php
dispatch((new Job)->onQueue('high'));
```

---

- worker 可指定多個 queue，_依序優先處理_：

```bash
php artisan queue:work --queue=high,low
```

---

### *任務過期與 timeout*

- `config/queue.php` 每個 connection 有 `retry_after`（秒），_超過未完成會重派_。
- __SQS__ 由 `AWS` 控制 `visibility` timeout。
- __worker --timeout（預設 60 秒）__，_超過會強制終止 worker_。
- __retry_after__ 應大於（>） __timeout__，否則可能重複執行同一 job。

---

### *Supervisor 配置*（Linux）

- 安裝 `Supervisor`：

```bash
sudo apt-get install supervisor
```

---

- 建立 `/etc/supervisor/conf.d/laravel-worker.conf`：

```php
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/forge/app.com/artisan queue:work sqs --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=forge
numprocs=8
redirect_stderr=true
stdout_logfile=/home/forge/app.com/worker.log
stopwaitsecs=3600
```

---

- *numprocs* __控制同時 worker 數量__。
- *stopwaitsecs* 應 _大於_ `最長任務秒數`。

<!-- 
stopwaitsecs 指 Supervisor 停止 worker 時，最多等待 worker 結束的秒數。
必須大於「最長任務秒數」，否則 worker 還沒做完就被強制終止，可能造成任務遺失或資料不一致。 
-->

<!-- 
retry_after > timeout（Laravel queue 設定）：
指 queue 服務等待 worker 完成任務的時間（retry_after）要大於 worker 執行任務的最大時間（timeout）。
否則 queue 可能在 worker 還沒結束時就重派同一個 job，造成重複執行。 
-->

<!-- 
兩者都是為了避免任務還沒做完就被強制終止或重複執行，
但一個是 Supervisor 管理 worker 的等待時間，
一個是 Laravel queue 控制 job 分派的等待時間。 
-->

---

- **重新載入 Supervisor 並啟動 worker**：

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start "laravel-worker:*"
```

---

### *補充*

- `closure/catch callback` 內不可用 `$this`。
- **worker 長駐**，__靜態變數/狀態__ _不會自動重置。_
- 建議所有 queue 相關部署都搭配 **process manager**（如 `Supervisor`）。
- `retry_after/timeout` 設定需謹慎，避免 job 重複執行。 

---

## **Dealing With Failed Jobs**（處理失敗任務）

### *failed_jobs 資料表*

- **預設** `migration` 已包含，若無可用：

```bash
php artisan make:queue-failed-table
php artisan migrate
```

- 同步派送（*dispatchSync*）失敗的 job __不會進入__ `failed_jobs`，_例外會直接拋出_。

---

### *最大嘗試次數與 backoff*

- __worker 層級__：

```bash
php artisan queue:work redis --tries=3 --backoff=3
# --backoff=3 的意思是：
# 每次重試 queue 任務失敗時，會等待 3 秒再重試下一次。
```

---

- __job 層級__：

```php
public $tries = 3;
public $backoff = 3;
// 或
public function backoff(): int|array { return [1, 5, 10]; }
```

- `backoff` 可回傳陣列，實現 `exponential backoff`。

---

### *failed 方法*（job 失敗後自動呼叫）

- 可在 job 類別內定義 `failed(Throwable $e)` 處理失敗後續：

```php
public function failed(?Throwable $exception): void {
    // 通知用戶、回滾操作等
}
```
- failed 方法執行時，__job 會重新 new 一個實例，handle 內的屬性變動不會保留__。

---

### *查詢與重試失敗任務*

- _查詢_ 所有失敗任務：

```bash
php artisan queue:failed
```

---

- _重試_ 單一/多個/全部失敗任務：

```bash
php artisan queue:retry job-uuid
php artisan queue:retry job-uuid1 job-uuid2
php artisan queue:retry --queue=name
php artisan queue:retry all
```

---

- _刪除單一_ 失敗任務：

```bash
php artisan queue:forget job-uuid
```

---

- _刪除全部_ 失敗任務：

```bash
php artisan queue:flush
```

- **Horizon** 請用 `horizon:forget/horizon:clear`

---

### *缺失 Model 自動刪除*

- 若 job 依賴的 `Eloquent model` 已被刪除，可自動丟棄 job：

```php
public $deleteWhenMissingModels = true;
```

---

### *queue:prune-failed 清理失敗紀錄*

- **預設** 清理 `24 小時前` 的失敗紀錄，可用 `--hours` 指定：

```bash
php artisan queue:prune-failed --hours=48
```

---

### *DynamoDB 支援*

- 可將失敗任務存 DynamoDB，需先 __建表__（主鍵 application, uuid）。
- 設定 `queue.failed.driver = dynamodb`，並設 `key/secret/region/table`。

```php
'failed' => [
    'driver' => env('QUEUE_FAILED_DRIVER', 'dynamodb'),
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'table' => 'failed_jobs',
],
```

- 若 __不需__ 儲存失敗任務，`QUEUE_FAILED_DRIVER=null`。

---

### *失敗事件監聽*

- 可用 `Queue::failing` 註冊失敗事件 `listener`（如通知/記錄）：

```php
use Illuminate\Queue\Events\JobFailed; // 匯入 JobFailed 事件類別，代表 queue 任務失敗時會觸發的事件
Queue::failing(function (JobFailed $event) { // 註冊一個監聽器，當有 job 執行失敗時會自動呼叫這個匿名函式，並注入 JobFailed 事件物件
    // $event->connectionName, $event->job, $event->exception
    // $event->connectionName：失敗任務所屬的 queue 連線名稱（如 redis、database）
    // $event->job：失敗的 job 實例，可取得 job 內容、payload 等
    // $event->exception：導致失敗的例外（Exception）物件，可取得錯誤訊息、堆疊等
    // 這裡可以自訂通知、記錄、警報等處理邏輯，例如：寫 log、寄信、發 Slack 通知
});
```

---

### *queue:clear/monitor*

- `queue:clear` 可清空 queue（僅支援 SQS/Redis/DB driver）：

```bash
php artisan queue:clear
php artisan queue:clear redis --queue=emails
```

- `queue:clear` 只支援 __SQS/Redis/DB driver__，SQS 最多 60 秒才會真正清空。

---

- `queue:monitor` 可 __監控 queue 長度__，超過門檻會觸發 `QueueBusy` 事件：

```bash
php artisan queue:monitor redis:default,redis:deployments --max=100
```

---

- __監控事件可用於自動通知__：

```php
use Illuminate\Queue\Events\QueueBusy; // 匯入 QueueBusy 事件類別，代表 queue 長度超過門檻時會觸發的事件
Event::listen(function (QueueBusy $event) { // 註冊一個監聽器，當 queue 長度過高時會自動呼叫這個匿名函式，並注入 QueueBusy 事件物件
    // 通知開發團隊
    // 這裡可以自訂通知邏輯，例如：寫 log、寄信、發 Slack 通知等
    // $event->connection：queue 連線名稱（如 redis、database）
    // $event->queue：queue 名稱（如 default、emails）
    // $event->size：目前 queue 長度（任務數量）
});
```

---

### *測試 queue*

- __Queue::fake__ 可 `攔截所有 job，不實際派送`，方便測試：

```php
Queue::fake(); // 啟用 queue fake，攔截所有 job，不會真的派送到 queue，適合單元測試

Queue::assertNothingPushed(); // 斷言沒有任何 job 被派送到 queue
Queue::assertPushedOn('queue-name', ShipOrder::class); // 斷言有 ShipOrder 這個 job 被派送到指定 queue（queue-name）
Queue::assertPushed(ShipOrder::class, 2); // 斷言 ShipOrder 這個 job 被派送了 2 次

Queue::assertNotPushed(AnotherJob::class); // 斷言 AnotherJob 這個 job 沒有被派送
Queue::assertClosurePushed(); // 斷言有 closure（匿名函式）型態的 job 被派送

Queue::assertCount(3); // 斷言總共被派送了 3 個 job

Queue::assertPushed(fn(ShipOrder $job) => $job->order->id === $order->id); // 斷言有 ShipOrder job，其 order id 等於指定 $order->id
```

---

- __只 fake 部分 job__：

```php
Queue::fake([ShipOrder::class]); // 只 fake ShipOrder 這個 job，其他 job 會正常派送
Queue::fake()->except([ShipOrder::class]); // fake 除 ShipOrder 以外的所有 job，ShipOrder 會正常派送
```

---

- __Bus::fake__ 可測試 job `chain/batch`：

```php
Bus::fake(); // 啟用 Bus fake，攔截所有 job chain/batch，不會真的派送
Bus::assertChained([...]); // 斷言有指定的 job chain 被派送

Bus::assertBatched(fn(PendingBatch $batch) => ...); // 斷言有符合條件的 batch 被派送
Bus::assertBatchCount(3); // 斷言總共被派送了 3 個 batch

Bus::assertNothingBatched(); // 斷言沒有任何 batch 被派送
```

---

- __測試 job chain 內容__：

```php
$job = new ProcessPodcast; // 建立一個 ProcessPodcast job 實例
$job->handle(); // 執行 job 的 handle 方法
$job->assertHasChain([...]); // 斷言這個 job 有指定的 chain（鏈式任務）
$job->assertDoesntHaveChain(); // 斷言這個 job 沒有 chain
```

---

- __測試 batch 互動__：

```php
[$job, $batch] = (new ShipOrder)->withFakeBatch(); // 建立 ShipOrder job 並 fake 一個 batch，回傳 job 與 batch 物件
$job->handle(); // 執行 job 的 handle 方法
$this->assertTrue($batch->cancelled()); // 斷言 batch 已被取消
$this->assertEmpty($batch->added); // 斷言 batch 內沒有新增 job
```

---

- __測試 queue 互動__：

```php
$job = (new ProcessPodcast)->withFakeQueueInteractions(); // 建立 job 並 fake queue 互動，回傳 job 物件
$job->handle(); // 執行 job 的 handle 方法
$job->assertReleased(delay: 30); // 斷言 job 有被釋放（release），且延遲 30 秒
$job->assertDeleted(); // 斷言 job 有被刪除（delete）
$job->assertNotDeleted(); // 斷言 job 沒有被刪除
$job->assertFailed(); // 斷言 job 執行失敗
$job->assertFailedWith(CorruptedAudioException::class); // 斷言 job 失敗時拋出指定例外
$job->assertNotFailed(); // 斷言 job 沒有失敗
```
- 
可用 `withFakeBatch`、
    `withFakeQueueInteractions`、
    `assertHasChain`、
    `assertDoesntHaveChain`、
    `assertReleased`、
    `assertDeleted`、
    `assertFailedWith`

 等進階方法，測試 __job chain、batch、queue__ 互動。

---

### *Job Events*

- __Queue::before/after__ 可註冊 `job 執行前/後 `callback（如記錄/統計）：

```php
Queue::before(function (JobProcessing $event) { /* ... */ }); // 註冊一個監聽器，job 執行前會觸發，$event 內有 job 相關資訊，可用於記錄、統計等
Queue::after(function (JobProcessed $event) { /* ... */ }); // 註冊一個監聽器，job 執行後會觸發，$event 內有 job 相關資訊，可用於記錄、統計等
```

---

- __Queue::looping__ 可在每次 `取 job 前` 執行（如回滾未結束 transaction）：

```php
Queue::looping(function () { // 註冊一個監聽器，每次 worker 準備取出新 job 前都會執行這個 callback
    while (DB::transactionLevel() > 0) { // 檢查資料庫是否還有未結束的 transaction（交易）
        DB::rollBack(); // 若有，則回滾（rollback）所有未結束的 transaction，避免資料異常
    }
});
```

---

### *補充*

- __同步派送失敗__ 不會進入 `failed_jobs`，例外直接拋出。
- __failed 方法__ 適合`通知/回滾/記錄`。
- `deleteWhenMissingModels` 可避免因 model 被刪除導致 job 失敗。
- `queue:prune-failed/queue:flus` 請 __定期清理__ 失敗紀錄。
- 測試 queue 請用 `Queue::fake/Bus::fake`，避免真的派送任務。
- __監控 queue 長度__ 可自動通知團隊，避免任務堆積。 

---

### *補充細節與範例*

- __Laravel 新專案預設 queue driver 為 `sync`__

  ```php
  // config/queue.php
  'default' => env('QUEUE_CONNECTION', 'sync'),
  // 若要改用 redis：
  'default' => env('QUEUE_CONNECTION', 'redis'),
  ```
  - **預設** 為 `sync`，_所有 job 會同步執行_。改成 `redis/database` 才會進 `queue`。

---

- __Amazon SQS queue 的最大 delay 時間為 15 分鐘__（900 秒）

  ```php
  // 超過 900 秒會失敗
  ProcessPodcast::dispatch($podcast)->delay(now()->addSeconds(900));
  ```
  - `SQS` driver 下，delay 最多 15 分鐘。

--- 

- __設定 `after_commit` 為` true`，不只影響 job，也會影響 `queued event listeners、mailables、notifications、broadcast events`__

  ```php
  // config/queue.php
  'connections' => [
      'redis' => [
          'driver' => 'redis',
          'after_commit' => true,
      ],
  ]
  ```
  - 交易 `commit` 後才會派送 job、事件、通知等。

---

- __chain/catch callback 會被`序列化`，不能用 `$this`__

  ```php
  Bus::chain([
      new ProcessPodcast,
      new OptimizePodcast,
  ])->catch(function (Throwable $e) {
      // 不可用 $this，僅能用 function 參數
      Log::error($e->getMessage());
  })->dispatch();
  ```

---

- __job 依賴的 `Eloquent model` 已被刪除，可設 `$deleteWhenMissingModels = true;`__

  ```php
  class ProcessPodcast implements ShouldQueue {
      public $deleteWhenMissingModels = true;
      public function __construct(public Podcast $podcast) {}
      // ...
  }
  ```
  - 若 `$podcast` 被刪除，job 會自動丟棄。

---

- __派送 closure 到 queue 並命名__

  ```php
  dispatch(function () {
      // 任務內容
  })->name('MyClosureJob');
  ```
  - closure 內容會 _自動加密簽名_，適合`臨時性任務`。

---

- __Horizon 失敗任務管理__

  ```bash
  php artisan horizon:forget job-uuid
  php artisan horizon:clear
  ```
  - 用於 *清除* `Horizon` 監控下的失敗任務。

---

- __設定 job `timeout` 需安裝 `PCNTL PHP extension`__

  ```bash
  php -m | grep pcntl
  # 若無，請安裝對應 PHP 擴充
  ```
  - 沒有 `PCNTL`，`timeout 設定` _無法強制終止逾時 job_。

---

- __`IO 阻塞`需額外設定 timeout__

  ```php
  // 以 Guzzle 為例
  $client = new \GuzzleHttp\Client([
      'timeout' => 10, // 秒
      'connect_timeout' => 5,
  ]);
  ```
  - Laravel 的 `timeout` _無法終止_ `socket/HTTP` 阻塞，需在 client 設定。

---

- __retry_after 應大於 job 的 $timeout__

  ```php
  // config/queue.php
  'connections' => [
      'redis' => [
          'retry_after' => 120, // 秒
      ],
  ]
  // job 內
  public $timeout = 90;
  ```
  - `retry_after` 必須大於 `timeout`，否則 _同一 job 可能被多 worker 重複執行_。 

---

### *補充細節與範例*

- __progress callback 範例__  

  ```php
  Bus::batch([...])
      ->progress(function (Batch $batch) {
          // 每個 job 完成時觸發，可用於即時進度條
          Log::info('Batch progress: ' . $batch->progress() . '%');
      })
      ->dispatch();
  ```

---

- __before callback 範例__  

  ```php
  Bus::batch([...])
      ->before(function (Batch $batch) {
          // 批次建立但尚未加入 job
          Log::info('Batch created: ' . $batch->id);
      })
      ->dispatch();
  ```

---

- __批次 callback 內不可用 $this__  

  - 注意：`then/catch/finally/progress/before callback` 會被 _序列化_，__不可用 $this__，只能用 __function 參數__。

---

- __批次 job 內勿執行隱性 commit SQL__  

  - 批次 job 會包在 DB transaction，勿在 job 內執行 `ALTER TABLE、CREATE INDEX` 等 _隱性 commit 的 SQL_，否則會 _導致 transaction 提前 commit_。

---

- __`Bus::findBatch` 查詢批次進度 RESTful 路由範例__  

  ```php
  // routes/web.php
  use Illuminate\Support\Facades\Bus;
  use Illuminate\Support\Facades\Route;
  Route::get('/batch/{batchId}', function ($batchId) {
      return Bus::findBatch($batchId);
  });
  ```

---

- __allowFailures 實務提醒__  

  - `allowFailures` 允許部分 job 失敗，_catch callback 只會觸發一次_（第一個失敗 job）。

---

- __`queue:prune-batches --unfinished/--cancelled` 清理建議__  

  - 建議 _定期清理_ 未完成批次（如 `queue:prune-batches --unfinished=72`）與已取消批次（如 `--cancelled=72`），避免 `job_batches` 表過大。

----

- __DynamoDB TTL 屬性需為 `UNIX timestamp`（秒）__，否則不會自動清理。 