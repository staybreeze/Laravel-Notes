# Laravel Queue 基本觀念整理

---

## Queue、Job、Worker 是什麼？

### *什麼是 Queue Job？*
**Queue Job（佇列任務）** 就是你要丟到背景執行的「工作」。  
例如：寄信、處理上傳檔案、產生報表、推播通知等，這些通常比較耗時，不適合在 HTTP 請求流程中直接執行。

在 Laravel 裡，你會建立一個 Job 類別（通常在 `app/Jobs`），裡面寫好要執行的邏輯，然後用 `dispatch()` 把它丟到 queue（佇列）裡。

**簡單來說：**  
Job = 你要做的「一件事」的程式碼（任務）。

---

### *什麼是 Worker？*
**Worker（佇列工人）** 是一個在背景執行、負責「撿」queue 裡的 job 來執行的程式。  
你可以把 worker 想像成一個不斷輪詢佇列、看到有新任務就拿出來執行的「小幫手」。

在 Laravel 裡，worker 通常用這個指令啟動：
```bash
php artisan queue:work
```
你可以開很多個 worker（多工），讓他們同時處理多個任務。

**簡單來說：**  
Worker = 幫你執行 queue 裡 job 的背景程式。

---

### *什麼是 Queue？*
**Queue（佇列）** 就是「任務的排隊區」，像是「待辦清單」。
你把 Job 丟進 Queue，這些任務就會在這裡排隊，等著被執行。
Queue 可以有很多種（例如：high、default、low），也可以有不同的後端（如 Redis、資料庫）。

---

## 三者的關係

### 關係圖解
```
    A[你寫的 Job 任務] -- dispatch() --> B[Queue 佇列]
    B -- 等待 --> C[Worker 工人]
    C -- 執行 --> D[Job 任務完成]
```
- 你用 `dispatch()` 把 Job 丟進 Queue。
- Queue 負責「排隊」。
- Worker 會「撿」Queue 裡的 Job 來執行。
- Job 執行完畢，Queue 就少一個任務。

---

### 生活化比喻
- **Job**：一張待辦卡片（上面寫著要做什麼）
- **Queue**：待辦箱（所有卡片都丟進這裡排隊）
- **Worker**：工人（負責從箱子裡拿卡片出來，照著上面指示去做）

---

### 總結
- **Job** 是「要做什麼」。
- **Queue** 是「排隊等著做」。
- **Worker** 是「真的去做的人」。

三者合作，讓你的系統可以把耗時的工作丟到背景，讓主流程更快回應使用者！

---

# Laravel Queue（佇列）筆記

---

## *什麼是 Laravel Queue？*

- **Laravel Queue（佇列）** 讓你可以將耗時的任務（如：解析與儲存上傳的 CSV 檔案）丟到背景執行，讓網頁請求能夠快速回應，提升使用者體驗。
- Laravel 提供統一的 Queue API，可支援多種後端（Amazon SQS、Redis、資料庫等）。
- 佇列設定檔在 `config/queue.php`，可設定多種連線（connections）與驅動（drivers）。
- Laravel 也有 *Horizon*（專為 Redis 佇列設計的漂亮儀表板與管理工具）。

---

## *Connections vs. Queues*

- **Connection（連線）**：指向一個後端服務（如 SQS、Redis、資料庫等），在 `config/queue.php` 的 `connections` 陣列中設定。
- **Queue（佇列）**：每個 connection 可有多個 queue（可想像成不同的工作堆疊）。
- 每個 connection 設定檔內有 `queue` 屬性，代表預設的 queue 名稱。
- 派送（dispatch）Job 時可指定 queue 名稱：
```php
use App\Jobs\ProcessPodcast;
// 派送到預設 connection 的預設 queue
ProcessPodcast::dispatch();
// 派送到預設 connection 的 "emails" queue
ProcessPodcast::dispatch()->onQueue('emails');
```
- 多 queue 可用於分流、優先處理：
```bash
php artisan queue:work --queue=high,default
```

---

## *各 Driver 注意事項與前置作業*

### **Database**
- 需有 jobs 資料表，預設 migration：`0001_01_01_000002_create_jobs_table.php`
- 若無此 migration，可用：
```bash
php artisan make:queue-table
php artisan migrate
```

### **Redis**
- 需在 `config/database.php` 設定 Redis 連線。
   ```php
   // 範例：config/database.php 內 redis 設定
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
- Redis Cluster 需 queue 名稱加 hash tag（如 `{default}`），確保同一 queue 的 key 在同一 hash slot。
   // 範例：
   // 在 *queue.php* 設定 'queue' => '{default}'，或 dispatch(new Job)->onQueue('{default}')
   // 加上大括號 hash tag，讓 cluster 下同一 queue 的 key 分配到同一 slot，避免分散導致 queue 無法正確消費。

- `block_for` 設定：指定 worker 等待新任務的秒數（可提升效能）。
   ```php
   // 範例：config/queue.php 內 redis 連線設定
   'connections' => [
       'redis' => [
           'driver' => 'redis',
           'block_for' => 5, // worker 最多等待 5 秒（建議設 1~5 秒，減少輪詢）
       ],
   ]
   ```
- 設為 0 會無限阻塞，直到有新任務。
   ```php
   // 範例：
    'block_for' => 0 // worker 會一直阻塞直到有新任務
   // 適合高併發場景，worker 不會閒置浪費資源。
   ```
### **其他 Driver 依賴**
- Amazon SQS：`aws/aws-sdk-php ~3.0`
- Beanstalkd：`pda/pheanstalk ~5.0`
- Redis：`predis/predis ~2.0` 或 phpredis PHP extension
- MongoDB：`mongodb/laravel-mongodb`

---

## *建立 Job*

### **產生 Job 類別**
```bash
php artisan make:job ProcessPodcast
```
- 產生於 `app/Jobs` 目錄。
- 預設會實作 `Illuminate\Contracts\Queue\ShouldQueue`，代表此 Job 會進入佇列。

### **Job 類別結構**
```php
namespace App\Jobs; // Job 類別的命名空間，通常放在 app/Jobs 目錄

use Illuminate\Bus\Queueable; // Queueable trait，提供 queue 相關功能
use Illuminate\Contracts\Queue\ShouldQueue; // 代表這個 Job 會進入佇列
use Illuminate\Foundation\Bus\Dispatchable; // 提供 dispatch() 方法
use Illuminate\Queue\InteractsWithQueue; // 提供與 queue 互動的方法
use Illuminate\Queue\SerializesModels; // 讓 Eloquent model 能安全序列化

class ProcessPodcast implements ShouldQueue // 實作 ShouldQueue 介面，這個 Job 會被放進 queue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; // 引入多個 trait，讓 Job 支援 dispatch、序列化、queue 操作等

    public $podcast; // Job 需要處理的資料（可為 model、array、primitive）

    /**
     * 建構子，接收要處理的資料
     */
    public function __construct($podcast)
    {
        $this->podcast = $podcast; // 將資料存到屬性，供 handle() 使用
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
- 可直接將 Eloquent Model 傳入建構子，Laravel 會自動序列化/還原。
- 只會序列化 model 的主鍵，執行時自動查回完整 model。

### **handle 方法依賴注入**
- handle 方法可型別提示依賴，Laravel 會自動注入。
- 進階：可用 `bindMethod` 自訂注入邏輯。
```php
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
- 二進位資料（如圖片內容）建議先 `base64_encode` 再傳入 Job，避免序列化失敗。

### **關聯序列化注意**
- Model 的已載入關聯也會被序列化，可能導致 payload 很大。
- 若只需主 model，可用 `withoutRelations()`：
```php
public function __construct(Podcast $podcast) {
    $this->podcast = $podcast->withoutRelations();
}
```
- *PHP 8+ 可用屬性標註*：
```php
use Illuminate\Queue\Attributes\WithoutRelations;
public function __construct(
    #[WithoutRelations]
    public Podcast $podcast,
) {}
```
- 若傳入 model 集合，集合內的 model 不會自動還原關聯。

---

## **Unique Jobs（唯一任務）**

- 需支援 **lock** 的 cache driver（memcached、redis、dynamodb、database、file、array）。
- 實作 `ShouldBeUnique` 介面即可：
```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
class UpdateSearchIndex implements ShouldQueue, ShouldBeUnique {}
```
- **可自訂唯一 key 與逾時秒數**：
```php
public $uniqueFor = 3600; // 1 小時
public function uniqueId(): string { return $this->product->id; }
```
- **可自訂 lock 用的 cache driver**：
```php
public function uniqueVia(): Repository { return Cache::driver('redis'); }
```
- 若要「直到開始處理前」都唯一，改實作 `ShouldBeUniqueUntilProcessing`。

---

## **Encrypted Jobs（加密任務）**

- 實作 `ShouldBeEncrypted` 介面即可：
```php
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
class UpdateSearchIndex implements ShouldQueue, ShouldBeEncrypted {}
```
- Laravel 會自動加密 job payload，確保資料隱私與完整性。

---

## **其他補充**

- 若只需*限制同時執行數量*，請用 `WithoutOverlapping` middleware。
- *Laravel Horizon* 適用於 Redis 佇列，提供即時監控與管理。
- 佇列 worker 可指定多個 queue，依優先順序處理。
- `php artisan queue:work` 啟動 worker，`php artisan queue:listen`（已不建議使用）。
- 可用 `php artisan queue:failed` 管理失敗任務。 

---

## **Job Middleware（任務中介層）**

### *什麼是 Job Middleware？*
- Job middleware 讓你可以將自訂邏輯包裹在 queued job 執行前後，減少重複程式碼。
- 常見用途：
            *限流（Rate Limiting）*、
            *防止重疊（Without Overlapping）*、
            *異常節流（ThrottlesExceptions）*、
            *條件跳過（Skip）*等。

- middleware 實作類似 route middleware，會接收 job 與下一步 callback。
- artisan 指令：
```bash
php artisan make:job-middleware RateLimited
```

### *自訂 Job Middleware 範例*
```php
namespace App\Jobs\Middleware; // 定義命名空間，方便自動載入
use Closure; // 匿名函式型別，用於 middleware 的下一步 callback
use Illuminate\Support\Facades\Redis; // 匯入 Redis 門面，方便操作 Redis

class RateLimited { // 定義一個自訂的 Middleware 類別
    public function handle(object $job, Closure $next): void { // handle 方法，接收當前 job 與下一步 callback
        Redis::throttle('key') // 建立一個名為 'key' 的限流器（可自訂 key 名稱）
            ->block(0) // 取得鎖時最多等待 0 秒（不等待，立即回應）
            ->allow(1) // 每個時間區間只允許 1 次通過
            ->every(5) // 每 5 秒為一個區間
            ->then(function () use ($job, $next) { // 如果取得鎖，執行這個 callback
                $next($job); // 執行下一步（即真正執行 job）
            }, function () use ($job) { // 如果沒取得鎖，執行這個 callback
                $job->release(5); // 釋放 job，5 秒後再重試
            });
    }
}
```
- job 內加上 middleware 方法：
```php
public function middleware(): array { // 定義 middleware 方法，回傳要套用的中介層陣列
    return [new \App\Jobs\Middleware\RateLimited]; // 套用剛剛自訂的 RateLimited middleware，讓這個 Job 執行時會限流
}
```

---

### *Rate Limiting（限流）*
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
- job 內 middleware：
```php
use Illuminate\Queue\Middleware\RateLimited; // 匯入 Laravel 內建的 RateLimited middleware
public function middleware(): array { // 定義 middleware 方法
    return [new RateLimited('backups')]; // 套用剛剛註冊的 'backups' 限流器
}
// 可用 releaseAfter(秒) 或 dontRelease() 控制重派行為
// RateLimitedWithRedis 效能更佳，適合 Redis driver
```

---

### *Preventing Job Overlaps（防止重疊）*
- 使用 `Illuminate\Queue\Middleware\WithoutOverlapping`。
- 適合同一資源只允許一個 job 處理的情境。
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
- 需支援 lock 的 cache driver（memcached、redis、dynamodb、database、file、array）。

---

### *Throttling Exceptions（異常節流）*
- 使用 `Illuminate\Queue\Middleware\ThrottlesExceptions` 或 `ThrottlesExceptionsWithRedis`。
- 當 job 連續丟出異常達指定次數，延遲一段時間再重試。
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

### *Skipping Jobs（條件跳過）*
- 使用 `Illuminate\Queue\Middleware\Skip`。
- 可根據條件直接刪除 job，不進行處理。
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

### 補充
- middleware 方法需回傳「*物件陣列*」而非字串。
- middleware 也可用於 *queueable* *event* *listeners*、*mailables*、*notifications*。
- **release/dontRelease** 會影響 job attempts 次數，請調整 tries/maxExceptions/retryUntil。
- **Redis** driver 請優先用 WithRedis 版本 middleware，效能最佳。 

---

## Job **派送（Dispatching Jobs）**

### 1.1 *基本派送*
- `Job::dispatch($arg1, $arg2, ...)`：將任務派送到 queue，參數會傳給建構子。
- `dispatch(new Job(...))`：等同於上方寫法。

### 1.2 *條件派送*
- `Job::dispatchIf($condition, $args...)`：條件為 true 才派送。
- `Job::dispatchUnless($condition, $args...)`：條件為 false 才派送。

### 1.3 *延遲派送*
- `->delay($datetimeOrSeconds)`：延遲一段時間後才可被 worker 處理。
- `->withoutDelay()`：忽略 Job 預設 delay，立即派送。

### 1.4 *回應後派送*
- `Job::dispatchAfterResponse($args...)`：HTTP 回應送出後才執行（不需 worker，適合短任務）。
- `dispatch(fn()=>...)->afterResponse()`：派送 closure，回應後執行。

### 1.5 *同步派送*
- `Job::dispatchSync($args...)`：立即同步執行，不進 queue。

### 1.6 *指定 queue/connection*
- `->onQueue('queue_name')`：指定 queue 名稱。
- `->onConnection('connection_name')`：指定 queue 連線。
- 也可在 Job 建構子內呼叫 `$this->onQueue()`、`$this->onConnection()`。

### 1.7 *交易後派送*
- `->afterCommit()`：資料庫交易 commit 後才派送（需 driver 支援）。
- `->beforeCommit()`：即使 after_commit 設定為 true，也可強制立即派送。

### 1.8 *Job Chaining*
- `Bus::chain([...])->dispatch()`：多個 Job 依序執行，前一個成功才執行下一個。
- 可用 `catch(fn($e)=>...)` 捕捉鏈中任一 Job 失敗。
- 可用 `prependToChain()`、`appendToChain()` 動態插入 Job。

---

## **Job 失敗、重試、逾時、例外處理（詳細說明與範例）**

### 1. *artisan 指令操作*（在終端機執行，不寫在 PHP 程式碼裡）

這些指令用於啟動 queue worker、查詢/重試/刪除失敗任務等，請在終端機（Terminal）輸入：

```bash
php artisan queue:work --tries=3 # 啟動 worker，最多重試 3 次
php artisan queue:failed         # 查詢所有失敗任務
php artisan queue:retry {id}     # 重試指定失敗任務
php artisan queue:forget {id}    # 刪除指定失敗任務
php artisan queue:flush          # 清空所有失敗任務
```
- 這些指令**不用寫在 PHP 程式碼裡**，只要在命令列執行即可。
- `queue:work` 會啟動一個 worker 持續處理 queue 裡的任務。
- `queue:failed` 會列出所有失敗的 job（例如資料庫連線失敗、API timeout 等）。
- `queue:retry` 可以讓你針對失敗的 job 再次嘗試執行。
- `queue:forget` 可以刪除單一失敗 job 記錄。
- `queue:flush` 會清空所有失敗 job 記錄。

---

### 2. *Job 類別內的屬性與方法*（要寫在 PHP 程式碼裡）

這些屬性與方法要寫在你自訂的 Job 類別（通常在 app/Jobs/ 目錄下），用來控制這個 job 的重試、逾時、失敗行為。

#### 範例：
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
- `$tries`：這個 job 最多會重試幾次（不含第一次執行）。
- `$timeout`：單次執行最多允許幾秒，超過會被強制終止。
- `$failOnTimeout`：如果逾時，是否直接標記為失敗（true = 不再重試）。
- `retryUntil()`：回傳一個時間點，超過這個時間就不再重試。
- `$maxExceptions`：允許最多幾次未處理例外，超過就直接失敗。
- `failed(Exception $e)`：job 失敗時自動呼叫，可在這裡做通知、補償、記錄等。

---

#### *進階補充與常見細節*

##### (1) `$this->release()` 與 `$this->fail()` 用法
你可以在 Job 的 handle() 內 **主動釋放（重派** 或 **標記失敗**：

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
- `$this->release(10)`：將 job 釋放回 queue，10 秒後再重試。
- `$this->fail($e)`：直接標記 job 失敗，並觸發 `failed()` 方法。

##### (2) `failed_jobs` 資料表
- 所有失敗的 job 都會被記錄在 `failed_jobs` 資料表（除非你用 sync driver）。
- 你可以用 artisan 指令查詢、重試、刪除這些失敗任務。

##### (3) `FailOnException` Middleware 用法
遇到特定例外時 job 直接失敗，不再重試：

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

##### (4) 釋放與重試注意事項
- 若 Job *逾時*，會被 worker 標記為失敗（根據 `$failOnTimeout` 設定）。
- 若 Job *釋放*（release），會重新進 queue，重試次數會累加。
- 若 Job *失敗*（fail），會進入 `failed_jobs` 表。

##### (5) *tries()*、*backoff()* 動態設定補充
- 你可以用方法動態決定最大嘗試次數或重試間隔：

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

### 3. 總結
- **artisan 指令**：只在終端機執行，不寫在 PHP 程式裡。
- **Job 屬性/方法**：要寫在你自訂的 Job 類別裡，控制重試、逾時、失敗等行為。
- 兩者搭配，讓你能彈性管理 queue 任務的執行與失敗處理。

---

## Job **派送方法與屬性表格**

### *派送方法總覽*
| 方法                        | 說明                                 |
|-----------------------------|--------------------------------------|
| dispatch                    | 一般派送                             |
| dispatchIf                  | 條件為 true 才派送                   |
| dispatchUnless              | 條件為 false 才派送                  |
| dispatchAfterResponse       | 回應後派送（不需 worker）            |
| dispatchSync                | 立即同步執行                         |
| onQueue                     | 指定 queue 名稱                      |
| onConnection                | 指定 queue 連線                      |
| delay                       | 延遲派送                             |
| withoutDelay                | 忽略預設 delay                       |
| afterCommit                 | 交易 commit 後才派送                  |
| beforeCommit                | 強制立即派送（即使 after_commit=true）|
| chain (Bus::chain)          | Job 鏈                               |

### *失敗/重試/逾時屬性與方法*
| 屬性/方法         | 說明                                 |
|-------------------|--------------------------------------|
| $tries            | 最大嘗試次數                         |
| tries()           | 動態回傳最大嘗試次數                 |
| $timeout          | 單一 job 執行逾時秒數                |
| $failOnTimeout    | 逾時時直接標記為失敗                 |
| retryUntil()      | 指定重試截止時間                     |
| $maxExceptions    | 最大未處理例外次數                   |
| failed(Exception) | Job 失敗時自動呼叫                   |
| $this->release()  | 手動釋放回 queue                     |
| $this->fail()     | 手動標記為失敗                       |

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
- **after_commit** 設定建議於所有生產環境開啟，避免交易未 commit 就執行 Job。
- **dispatchAfterResponse** 適合短任務（如寄信），不建議用於長任務。
- **Job Chaining** 只要有一個 Job 失敗，後續不會執行。
- **FailOnException** 適合區分可重試與不可重試的例外。
- **Job 逾時** 請確保 timeout < retry_after，否則可能重複執行。

---

## **Job Batching（任務批次）**

### 前置作業
- 需建立 *job_batches* 資料表：
```bash
php artisan make:queue-batches-table
php artisan migrate
```

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

### *派送批次任務*
- 使用 `Bus::batch([...])`，可搭配 before/progress/then/catch/finally callback：
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
- *$batch->id* 可用於查詢批次狀態。
- callback 內不可用 $this。
- 批次 job 會包在 *DB transaction*，勿在 job 內執行隱性 commit 的 SQL。

### *命名批次*
- 方便 Horizon/Telescope 顯示：
```php
Bus::batch([...])->name('Import CSV')->dispatch();
```

### *指定連線與 queue*
- 批次內所有 job 必須同一 connection/queue：
```php
Bus::batch([...])->onConnection('redis')->onQueue('imports')->dispatch();
```

### *批次內鏈（chains）*
- 批次陣列內可放 **job 陣列**，代表一組 chain 會平行執行：
```php
Bus::batch([
    [new ReleasePodcast(1), new SendPodcastReleaseNotification(1)], // 這一組 chain：先 ReleasePodcast(1)，完成後再 SendPodcastReleaseNotification(1)；(1) 代表第1個 Podcast 的 id
    [new ReleasePodcast(2), new SendPodcastReleaseNotification(2)], // 這一組 chain：先 ReleasePodcast(2)，完成後再 SendPodcastReleaseNotification(2)；(2) 代表第2個 Podcast 的 id
    // ...可依需求加入更多 chain
])->then(function (Batch $batch) { /* ... */ })->dispatch(); // then callback：全部 chain 都成功時觸發，dispatch() 派送批次
```
- 每一個陣列（[]）代表一組 chain，會依序執行，所有 chain 會平行處理。

```php
Bus::chain([
    new FlushPodcastCache, // 先執行 FlushPodcastCache job
    Bus::batch([new ReleasePodcast(1), new ReleasePodcast(2)]), // 再平行執行這兩個 ReleasePodcast job，(1)(2) 代表不同 Podcast 的 id
    Bus::batch([new SendPodcastReleaseNotification(1), new SendPodcastReleaseNotification(2)]), // 最後平行執行這兩個通知 job，(1)(2) 代表不同 Podcast 的 id
])->dispatch(); // dispatch() 派送整個 chain
```
- 你可以在 chain 裡包 batch，也可以在 batch 裡包 chain，彈性組合多種執行順序。

### *動態加入 job*
- 在 batch job 內可用 `$this->batch()->add([...])` 動態加入 job：
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
- 只能在同一 batch 內的 job 動態加入。

### *檢查與操作批次*
- Batch 實例常用屬性/方法：
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

### *路由查詢批次狀態*
- Batch 可直接回傳為 JSON，方便前端查詢進度：
```php
Route::get('/batch/{batchId}', fn($id) => Bus::findBatch($id)); // 定義一個 GET 路由，路徑為 /batch/{batchId}
// $id 會自動對應到 URL 的 {batchId} 參數
// Bus::findBatch($id) 會查詢該批次的狀態並回傳 Batch 實例（可自動轉成 JSON 給前端）
```

### *取消批次*
- job 內可用 `$this->batch()->cancel();` 取消整個批次。
- job 內建議先判斷 cancelled 再執行。
- 也可用 middleware：
```php
use Illuminate\Queue\Middleware\SkipIfBatchCancelled; // 匯入 Laravel 內建的批次取消判斷 middleware
public function middleware(): array { // 定義 middleware 方法
    return [new SkipIfBatchCancelled]; // 若批次已被取消，這個 job 會自動跳過不執行
}
```

### *失敗與允許失敗*
- 預設 job 失敗會取消整個 batch，catch callback 只會觸發一次。
- 若允許部分 job 失敗不影響整體：
```php
Bus::batch([...])->allowFailures()->dispatch(); // 派送批次任務，allowFailures 允許部分 job 失敗，整個 batch 不會被取消
```
- queue:retry-batch 可重試失敗 job：
```bash
php artisan queue:retry-batch 批次UUID # 針對指定批次的失敗 job 重新派送執行
```

### *批次資料清理（prune）*
- **job_batches** 表會快速累積，建議定期清理：
```php
Schedule::command('queue:prune-batches')->daily();
// 只保留 48 小時內：
Schedule::command('queue:prune-batches --hours=48')->daily();
// 清理未完成批次：
Schedule::command('queue:prune-batches --hours=48 --unfinished=72')->daily();
// 清理已取消批次：
Schedule::command('queue:prune-batches --hours=48 --cancelled=72')->daily();
```

### *DynamoDB 支援*
- 可將批次資料存 DynamoDB，需先建表（主鍵 application, id）。
- 設定 queue.batching.driver = dynamodb，並設 key/secret/region/table。
- 建議設 ttl 屬性，啟用自動清理：
```php
'batching' => [
    'driver' => 'dynamodb',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'table' => 'job_batches',
    'ttl_attribute' => 'ttl',
    'ttl' => 60 * 60 * 24 * 7, // 7 天
],
```
- DynamoDB 批次清理靠 TTL，不用 queue:prune-batches。

---

### *補充*
- 批次 callback 內不可用 $this。
- 批次 job 會包在 DB transaction，勿執行隱性 commit SQL。
- **allowFailures** 適合大量任務容忍部分失敗。
- **SkipIfBatchCancelled** middleware 可自動略過已取消批次的 job。
- **Bus::findBatch** 可查詢批次進度，適合前端輪詢。 

---

## **Queueing Closures 與 Worker 實務**

### *派送 Closure 到 Queue*
- 可直接用 `dispatch(function(){ ... })` 派送 closure 到 queue，適合簡單、臨時性任務。
- closure 內容會*加密簽名*，確保傳輸安全。
```php
$podcast = App\Podcast::find(1);
dispatch(function () use ($podcast) {
    $podcast->publish();
});
```
- 可用 name() 指定 closure 任務名稱，方便 `queue:work/Horizon` 顯示：
```php
dispatch(function () { /* ... */ })->name('Publish Podcast');
```
- 可用 catch() 註冊失敗 callback（不可用 $this）：
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
- 用 `php artisan queue:work` 啟動 worker，持續處理新進任務，直到手動停止。
```bash
php artisan queue:work
```
- 建議用 Supervisor 等 process manager 讓 worker 永遠在背景執行。
- 加 -v 參數可顯示詳細 job id、connection、queue 名稱：
```bash
php artisan queue:work -v
```
- worker 啟動後不會自動載入新程式碼，部署時需 `queue:restart`。
- `php artisan queue:listen` 會每次 job 都重載程式碼，適合開發但效能較差。

---

### *Worker 參數與多工*
- 指定連線與 queue：
```bash
php artisan queue:work redis --queue=emails # 啟動 worker，指定使用 redis 連線，並只處理 emails 這個 queue
```
- 多 worker 可同時處理多個 queue，或同一 queue 多工：
  - 多開終端機或用 Supervisor 設定 *numprocs*。 # numprocs 代表同時啟動幾個 worker 處理任務，可提升併發處理能力
  - 指令範例：
    ```php
    # 多個終端機分別執行，處理不同 queue
    php artisan queue:work redis --queue=emails
    php artisan queue:work redis --queue=notifications
    php artisan queue:work redis --queue=default
    # 或同一 queue 開多個 worker
    php artisan queue:work redis --queue=emails
    php artisan queue:work redis --queue=emails
    ```
  - Supervisor 設定範例（推薦生產環境自動多工）：
    ```php
    [program:laravel-worker]
    process_name=%(program_name)s_%(process_num)02d
    command=php /path/to/artisan queue:work redis --queue=emails
    numprocs=5 # 啟動 5 個 worker
    autostart=true
    autorestart=true
    user=forge
    redirect_stderr=true
    stdout_logfile=/path/to/worker.log
    ```

- 只處理一個 job：
```bash
php artisan queue:work --once
```

- 處理指定數量後自動結束：
```bash
php artisan queue:work --max-jobs=1000
```

- 處理完所有任務後自動結束（適合 Docker）：
```bash
php artisan queue:work --stop-when-empty
```

- 處理指定秒數後自動結束：
```bash
php artisan queue:work --max-time=3600
```

- 無任務時 sleep 秒數：
```bash
php artisan queue:work --sleep=3
```

- 維護模式下預設不處理任務，--force 可強制執行：
```bash
php artisan queue:work --force
```

---

### *Worker 資源與部署*
- worker 為**長駐程式**，記憶體不會自動釋放，建議定期重啟。
- 處理大量圖片等資源時，請**主動釋放**（如 imagedestroy）。
- worker 啟動後**不會自動載入新程式碼**，部署時務必 queue:restart：
```bash
php artisan queue:restart
```
- queue:restart 會讓 worker 處理完當前 job 後優雅結束，需搭配 Supervisor 自動重啟。
- queue:restart 需正確設定 cache driver。

---

### *任務優先權與多 queue*
- 可將重要任務派送到 **high queue**，次要任務派送到 **low queue**：
```php
dispatch((new Job)->onQueue('high'));
```
- worker 可指定多個 queue，依序優先處理：
```bash
php artisan queue:work --queue=high,low
```

---

### *任務過期與 timeout*
- `config/queue.php` 每個 connection 有 retry_after（秒），超過未完成會重派。
- *SQS* 由 AWS 控制 visibility timeout。
- *worker --timeout（預設 60 秒）*，超過會強制終止 worker。
- *retry_after* 應大於 **timeout**，否則可能重複執行同一 job。

---

### *Supervisor 配置（Linux）*
- 安裝 Supervisor：
```bash
sudo apt-get install supervisor
```
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
- *numprocs* 控制同時 worker 數量。
- *stopwaitsecs* 應大於最長任務秒數。
- **重新載入 Supervisor 並啟動 worker**：
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start "laravel-worker:*"
```
- 詳細請參考 Supervisor 官方文件。

---

### *補充*
- closure/catch callback 內不可用 $this。
- **worker 長駐**，靜態變數/狀態不會自動重置。
- 建議所有 queue 相關部署都搭配 **process manager**（如 Supervisor）。
- **retry_after/timeout** 設定需謹慎，避免 job 重複執行。 

---

## *Dealing With Failed Jobs（處理失敗任務）*

### **failed_jobs 資料表**
- 預設 migration 已包含，若無可用：
```bash
php artisan make:queue-failed-table
php artisan migrate
```
【補充】同步派送（*dispatchSync*）失敗的 job 不會進入 failed_jobs，例外會直接拋出。

### *最大嘗試次數與 backoff*
- **worker 層級**：
```bash
php artisan queue:work redis --tries=3 --backoff=3
```
- **job 層級**：
```php
public $tries = 3;
public $backoff = 3;
// 或
public function backoff(): int|array { return [1, 5, 10]; }
```
- backoff 可回傳陣列，實現 exponential backoff。

### *failed 方法（job 失敗後自動呼叫）*
- 可在 job 類別內定義 failed(Throwable $e) 處理失敗後續：
```php
public function failed(?Throwable $exception): void {
    // 通知用戶、回滾操作等
}
```
【補充】failed 方法執行時，job 會重新 new 一個實例，handle 內的屬性變動不會保留。

### *查詢與重試失敗任務*
- 查詢所有失敗任務：
```bash
php artisan queue:failed
```
- 重試單一/多個/全部失敗任務：
```bash
php artisan queue:retry job-uuid
php artisan queue:retry job-uuid1 job-uuid2
php artisan queue:retry --queue=name
php artisan queue:retry all
```
- 刪除單一失敗任務：
```bash
php artisan queue:forget job-uuid
```
- 刪除全部失敗任務：
```bash
php artisan queue:flush
```
- *Horizon* 請用 `horizon:forget/horizon:clear`

### *缺失 Model 自動刪除*
- 若 job 依賴的 Eloquent model 已被刪除，可自動丟棄 job：
```php
public $deleteWhenMissingModels = true;
```

### *queue:prune-failed 清理失敗紀錄*
- 預設清理 24 小時前的失敗紀錄，可用 --hours 指定：
```bash
php artisan queue:prune-failed --hours=48
```

### *DynamoDB 支援*
- 可將失敗任務存 DynamoDB，需先建表（主鍵 application, uuid）。
- 設定 `queue.failed.driver = dynamodb`，並設 key/secret/region/table。
```php
'failed' => [
    'driver' => env('QUEUE_FAILED_DRIVER', 'dynamodb'),
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'table' => 'failed_jobs',
],
```
- 若不需儲存失敗任務，QUEUE_FAILED_DRIVER=null。

### *失敗事件監聽*
- 可用 `Queue::failing` 註冊失敗事件 listener（如通知/記錄）：
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

### *queue:clear/monitor*
- queue:clear 可清空 queue（僅支援 SQS/Redis/DB driver）：
```bash
php artisan queue:clear
php artisan queue:clear redis --queue=emails
```
- **queue:clear** 只支援 SQS/Redis/DB driver，SQS 最多 60 秒才會真正清空。

- **queue:monitor** 可監控 queue 長度，超過門檻會觸發 QueueBusy 事件：
```bash
php artisan queue:monitor redis:default,redis:deployments --max=100
```

- *監控事件可用於自動通知*：
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

### *測試 queue*
- **Queue::fake** 可攔截所有 job，不實際派送，方便測試：
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
- **只 fake 部分 job**：
```php
Queue::fake([ShipOrder::class]); // 只 fake ShipOrder 這個 job，其他 job 會正常派送
Queue::fake()->except([ShipOrder::class]); // fake 除 ShipOrder 以外的所有 job，ShipOrder 會正常派送
```
- **Bus::fake** 可測試 job chain/batch：
```php
Bus::fake(); // 啟用 Bus fake，攔截所有 job chain/batch，不會真的派送
Bus::assertChained([...]); // 斷言有指定的 job chain 被派送
Bus::assertBatched(fn(PendingBatch $batch) => ...); // 斷言有符合條件的 batch 被派送
Bus::assertBatchCount(3); // 斷言總共被派送了 3 個 batch
Bus::assertNothingBatched(); // 斷言沒有任何 batch 被派送
```
- **測試 job chain 內容**：
```php
$job = new ProcessPodcast; // 建立一個 ProcessPodcast job 實例
$job->handle(); // 執行 job 的 handle 方法
$job->assertHasChain([...]); // 斷言這個 job 有指定的 chain（鏈式任務）
$job->assertDoesntHaveChain(); // 斷言這個 job 沒有 chain
```
- **測試 batch 互動**：
```php
[$job, $batch] = (new ShipOrder)->withFakeBatch(); // 建立 ShipOrder job 並 fake 一個 batch，回傳 job 與 batch 物件
$job->handle(); // 執行 job 的 handle 方法
$this->assertTrue($batch->cancelled()); // 斷言 batch 已被取消
$this->assertEmpty($batch->added); // 斷言 batch 內沒有新增 job
```
- **測試 queue 互動**：
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
【補充】
可用 *withFakeBatch*、
    *withFakeQueueInteractions*、
    *assertHasChain*、
    *assertDoesntHaveChain*、
    *assertReleased*、
    *assertDeleted*、
    *assertFailedWith*
 等進階方法，測試 job chain、batch、queue 互動。

### *Job Events*
- **Queue::before/after** 可註冊 job 執行前/後 callback（如記錄/統計）：
```php
Queue::before(function (JobProcessing $event) { /* ... */ }); // 註冊一個監聽器，job 執行前會觸發，$event 內有 job 相關資訊，可用於記錄、統計等
Queue::after(function (JobProcessed $event) { /* ... */ }); // 註冊一個監聽器，job 執行後會觸發，$event 內有 job 相關資訊，可用於記錄、統計等
```
- **Queue::looping** 可在每次取 job 前執行（如回滾未結束 transaction）：
```php
Queue::looping(function () { // 註冊一個監聽器，每次 worker 準備取出新 job 前都會執行這個 callback
    while (DB::transactionLevel() > 0) { // 檢查資料庫是否還有未結束的 transaction（交易）
        DB::rollBack(); // 若有，則回滾（rollback）所有未結束的 transaction，避免資料異常
    }
});
```

---

### *補充*
- **同步派送失敗**不會進入 failed_jobs，例外直接拋出。
- **failed 方法**適合通知/回滾/記錄。
- **deleteWhenMissingModels** 可避免因 model 被刪除導致 job 失敗。
- **queue:prune-failed/queue:flus** 請定期清理失敗紀錄。
- 測試 queue 請用 **Queue::fake/Bus::fake**，避免真的派送任務。
- 監控 queue 長度可自動通知團隊，避免任務堆積。 

---

### *補充細節與範例*

- **Laravel 新專案預設 queue driver 為 `sync`**
  ```php
  // config/queue.php
  'default' => env('QUEUE_CONNECTION', 'sync'),
  // 若要改用 redis：
  'default' => env('QUEUE_CONNECTION', 'redis'),
  ```
  > 預設為 sync，所有 job 會同步執行。改成 redis/database 才會進 queue。

- **Amazon SQS queue 的最大 delay 時間為 15 分鐘（900 秒）**
  ```php
  // 超過 900 秒會失敗
  ProcessPodcast::dispatch($podcast)->delay(now()->addSeconds(900));
  ```
  > SQS driver 下，delay 最多 15 分鐘。

- **設定 `after_commit` 為 true，不只影響 job，也會影響 queued event listeners、mailables、notifications、broadcast events**
  ```php
  // config/queue.php
  'connections' => [
      'redis' => [
          'driver' => 'redis',
          'after_commit' => true,
      ],
  ]
  ```
  > 交易 commit 後才會派送 job、事件、通知等。

- **chain/catch callback 會被序列化，不能用 `$this`**
  ```php
  Bus::chain([
      new ProcessPodcast,
      new OptimizePodcast,
  ])->catch(function (Throwable $e) {
      // 不可用 $this，僅能用 function 參數
      Log::error($e->getMessage());
  })->dispatch();
  ```

- **job 依賴的 Eloquent model 已被刪除，可設 `$deleteWhenMissingModels = true;`**
  ```php
  class ProcessPodcast implements ShouldQueue {
      public $deleteWhenMissingModels = true;
      public function __construct(public Podcast $podcast) {}
      // ...
  }
  ```
  > 若 $podcast 被刪除，job 會自動丟棄。

- **派送 closure 到 queue 並命名**
  ```php
  dispatch(function () {
      // 任務內容
  })->name('MyClosureJob');
  ```
  > closure 內容會自動加密簽名，適合臨時性任務。

- **Horizon 失敗任務管理**
  ```bash
  php artisan horizon:forget job-uuid
  php artisan horizon:clear
  ```
  > 用於清除 Horizon 監控下的失敗任務。

- **設定 job timeout 需安裝 PCNTL PHP extension**
  ```bash
  php -m | grep pcntl
  # 若無，請安裝對應 PHP 擴充
  ```
  > 沒有 PCNTL，timeout 設定無法強制終止逾時 job。

- **IO 阻塞需額外設定 timeout**
  ```php
  // 以 Guzzle 為例
  $client = new \GuzzleHttp\Client([
      'timeout' => 10, // 秒
      'connect_timeout' => 5,
  ]);
  ```
  > Laravel 的 timeout 無法終止 socket/HTTP 阻塞，需在 client 設定。

- **retry_after 應大於 job 的 $timeout**
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
  > retry_after 必須大於 timeout，否則同一 job 可能被多 worker 重複執行。 

---

### *補充細節與範例*

- **progress callback 範例**  
  ```php
  Bus::batch([...])
      ->progress(function (Batch $batch) {
          // 每個 job 完成時觸發，可用於即時進度條
          Log::info('Batch progress: ' . $batch->progress() . '%');
      })
      ->dispatch();
  ```

- **before callback 範例**  
  ```php
  Bus::batch([...])
      ->before(function (Batch $batch) {
          // 批次建立但尚未加入 job
          Log::info('Batch created: ' . $batch->id);
      })
      ->dispatch();
  ```

- **批次 callback 內不可用 $this**  
  > 注意：then/catch/finally/progress/before callback 會被序列化，**不可用 $this**，只能用 function 參數。

- **批次 job 內勿執行隱性 commit SQL**  
  > 批次 job 會包在 DB transaction，勿在 job 內執行 ALTER TABLE、CREATE INDEX 等隱性 commit 的 SQL，否則會導致 transaction 提前 commit。

- **Bus::findBatch 查詢批次進度 RESTful 路由範例**  
  ```php
  // routes/web.php
  use Illuminate\Support\Facades\Bus;
  use Illuminate\Support\Facades\Route;
  Route::get('/batch/{batchId}', function ($batchId) {
      return Bus::findBatch($batchId);
  });
  ```

- **allowFailures 實務提醒**  
  > allowFailures 允許部分 job 失敗，catch callback 只會觸發一次（第一個失敗 job）。

- **queue:prune-batches --unfinished/--cancelled 清理建議**  
  > 建議定期清理未完成批次（如 queue:prune-batches --unfinished=72）與已取消批次（如 --cancelled=72），避免 job_batches 表過大。

- **DynamoDB TTL 屬性需為 UNIX timestamp（秒）**  
  > DynamoDB TTL 屬性必須是 UNIX timestamp（秒），否則不會自動清理。 