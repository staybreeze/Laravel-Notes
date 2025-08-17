# *Laravel Redis 筆記*

---

## 1. **Redis 介紹**

- Redis 是開源、先進的 `key-value` 資料庫，支援 _多種資料結構_（__string、hash、list、set、sorted set__）。
- 常用於`快取、Session、排行榜、即時訊息、分散式鎖`等。
- 特色：高效能、支援持久化、支援叢集、支援 pub/sub。

---

## 2. **PhpRedis/Predis 安裝與選擇**

### 2.1 **PhpRedis**

- 官方推薦，`C 語言寫`的 PHP 擴充，效能最佳。
- 安裝：

```bash
  pecl install redis
  # 或用 apt/yum/brew 安裝
  ```
- `Laravel Sail、Homestead` **預設** 已安裝。
- *優點*：效能高、支援進階功能。
- *缺點*：需`伺服器權限`安裝。

---

### 2.2 **Predis**

- __純 PHP 實作__，`composer 安裝即`可。
- 安裝：

```bash
  composer require predis/predis
  ```

- *優點*：安裝簡單，無需額外擴充。
- *缺點*：效能略低於 `PhpRedis`。

---

## 3. **Laravel Redis 設定**（`config/database.php`）

### 3.1 *基本設定*

<!-- 在 Laravel 設定檔中，資料庫（database）通常指 MySQL、PostgreSQL 等關聯式資料庫，
     用於儲存應用程式的主要資料（如使用者、文章、訂單等），
     和 Redis 的用途不同，Redis 多用於快取、session、queue 等暫存資料。 -->

```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'), // Redis 用戶端類型（預設 phpredis）
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'), // 是否啟用 cluster
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'), // key 前綴
    ],
    'default' => [
        'url' => env('REDIS_URL'), // 連線字串（可選）
        'host' => env('REDIS_HOST', '127.0.0.1'), // 主機位址
        'username' => env('REDIS_USERNAME'),      // 使用者名稱（可選）
        'password' => env('REDIS_PASSWORD'),      // 密碼（可選）
        'port' => env('REDIS_PORT', '6379'),      // 連接埠
        'database' => env('REDIS_DB', '0'),       // 資料庫編號
    ],

    // 'default' 用於一般 Redis 操作（如 session、queue 等），
    // 'cache' 專門用於 Laravel 的 cache 功能，通常分開資料庫編號，
    // 避免不同用途的資料互相覆蓋或干擾。
    'cache' => [
        'url' => env('REDIS_URL'),                // 連線字串（可選）
        'host' => env('REDIS_HOST', '127.0.0.1'), // 主機位址
        'username' => env('REDIS_USERNAME'),      // 使用者名稱（可選）
        'password' => env('REDIS_PASSWORD'),      // 密碼（可選）
        'port' => env('REDIS_PORT', '6379'),      // 連接埠
        'database' => env('REDIS_CACHE_DB', '1'), // cache 用的資料庫編號
    ],
],
```
- `client`：_phpredis_ 或 _predis_。
- `options.cluster`：redis（_原生 cluster_）、predis（_client-side sharding_）。
- `options.prefix`：所有 __key 前綴__，避免多專案衝突。

<!-- Redis 可以被多個專案共用，
     如果沒有設定 key 前綴，不同專案的資料可能會互相覆蓋或混淆，
     加上前綴可以區分每個專案的資料，避免衝突。 -->

- 可設定 __多個連線__（default、cache、session…）。

---

### 3.2 *URL 寫法*

```php
'default' => [
    'url' => 'tcp://127.0.0.1:6379?database=0', // 預設 Redis 連線（TCP，使用第 0 號資料庫）
],
'cache' => [
    'url' => 'tls://user:password@127.0.0.1:6380?database=1', // cache Redis 連線（TLS，指定帳號密碼，使用第 1 號資料庫）
],
```

---

### 3.3 *TLS/SSL 加密*

<!-- TLS（Transport Layer Security）其實是 SSL（Secure Sockets Layer）的新版標準，
     現在大家都用 TLS 來取代舊的 SSL，
     所以設定 scheme 為 tls 就是啟用加密連線， -->

```php
'default' => [
    'scheme' => 'tls',             // 指定連線協定為 TLS（加密連線）
    'url' => env('REDIS_URL'),     // 連線字串（可設定帳號、密碼、主機、port、database）
    // ...
],
```
- `scheme` 設為 `tls` 可啟用加密連線。

---

### 3.4 *Unix Socket*

<!-- Unix Socket 是一種在同一台伺服器上，程式之間直接通訊的端點，
     通常用檔案路徑（如 /run/redis/redis.sock）代表，
     比網路連線（TCP/IP）更快、更省資源，常用於本機服務（如資料庫、Redis）。 -->

<!-- Unix Socket 這名稱是因為它最早是在 Unix 作業系統上設計和實作的，
     後來 Linux、macOS 等類 Unix 系統也都支援，
     所以叫「Unix Socket」。
     它和 Windows 的命名管道（Named Pipe）類似，但用在 Unix-like 系統。 -->

```env
REDIS_HOST=/run/redis/redis.sock  # 使用 Unix socket 路徑連接 Redis
REDIS_PORT=0                      # 使用 socket 時 port 設為 0（不需指定 port）
```
- 適合 __本機__ 高效能需求。

---

### 3.5 *序列化與壓縮*（`PhpRedis`）

```php
'options' => [
    'serializer' => Redis::SERIALIZER_MSGPACK, // 支援 NONE, PHP, JSON, IGBINARY, MSGPACK
    'compression' => Redis::COMPRESSION_LZ4,   // 支援 NONE, LZF, ZSTD, LZ4
],
```
- __節省__ 空間、提升傳輸效率，需 `client` 兼容。

---

## 4. **Redis Cluster 與 Predis Sharding**

### 4.1 *Redis Cluster*

```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'), // Redis 用戶端類型
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'), // 啟用 cluster 模式
        'prefix' => env('REDIS_PREFIX', ...),       // key 前綴
    ],
    'clusters' => [
        'default' => [
            [
                'url' => env('REDIS_URL'),                // 連線字串
                'host' => env('REDIS_HOST', '127.0.0.1'), // 主機位址
                'username' => env('REDIS_USERNAME'),      // 使用者名稱
                'password' => env('REDIS_PASSWORD'),      // 密碼
                'port' => env('REDIS_PORT', '6379'),      // 連接埠
                'database' => env('REDIS_DB', '0'),       // 資料庫編號
            ],
            // ...可多台，支援多節點分散式架構
        ],
    ],
],
```

- 支援 __自動 failover__。

<!-- failover（容錯切換） 是指當主要伺服器（如 Redis 主節點）發生故障時，系統會自動切換到備用伺服器（如 Redis 從節點），以確保服務不中斷。 -->
<!-- 這種機制常用於高可用性架構，讓系統能自動恢復運作，減少停機時間。 -->

---

### 4.2 *Predis Sharding*

- Predis 支援 `client-side sharding`（分片），但 __不支援自動 failover__。
- 適合 __暫存資料__。
- 設定方式：移除 `options.cluster`，只保留 `clusters`。

---

## 5. **進階連線參數**

### 5.1 *Predis*

```php
'default' => [
    ...
    'read_write_timeout' => 60, // 讀寫逾時秒數
],
```

---

### 5.2 *PhpRedis*

```php
'default' => [
    ...
    'read_timeout' => 60, // 讀取逾時
    'context' => [
        // 'auth' => ['username', 'secret'],
        // 'stream' => ['verify_peer' => false],
    ],
],
```

- 其他支援：__name, persistent, persistent_id, prefix, retry_interval, max_retries, backoff_algorithm, timeout, ...__

---

## 6. **Redis Facade 操作**

### 6.1 *動態方法*

```php
use Illuminate\Support\Facades\Redis;

Redis::set('name', 'Vincent');
$name = Redis::get('name');
$values = Redis::lrange('names', 0, 10);
```

- 任何 `Redis` 指令都可直接呼叫。

---

### 6.2 *command 方法*

```php
$values = Redis::command('lrange', ['names', 0, 10]); // 取得 Redis 名為 names 的 list，從第 0 筆到第 10 筆的所有值
// lrange 是 Redis 的 list 指令，用來取得指定 list 的一段元素，
// 語法：LRANGE key start stop
// 例如 lrange names 0 10 會取得 names 這個 list 的第 0 到第 10 筆資料。

// l 代表 list（串列），range 代表範圍，
// 意思是「取得 list 的某個範圍資料」。
```
- __明確__ 指定指令與參數。

---

### 6.3 *多連線*

```php
$redis = Redis::connection('cache');
$redis->set('foo', 'bar');
```
- 用於 __分流、分群、主從__ 架構。

---

## 7. **交易**（Transaction）

### 7.1 *理論*

- Redis `MULTI/EXEC` 是 Redis 的`交易機制`，可 __以把多個指令包成一次「原子操作」__，確保這些指令 __要嘛全部執行，要嘛全部不執行__，不會只執行部分，避免資料不一致。 

- 在 Laravel 裡，用 `Redis::transaction` 並傳入 `closure，closure` 內的所有 Redis 指令都會被包進同一個 `MULTI/EXEC` 交易，確保操作的原子性。

- _注意_：Redis 交易（`MULTI/EXEC`）裡**不能讀取資料，只能寫入**，因為交易執行前不會馬上執行指令，只有在 `EXEC` 時才一次執行所有指令，所以無法在交易內取得即時資料結果。

---

### 7.2 *範例*

```php
Redis::transaction(function ($redis) {
    $redis->incr('user:1:visits');   // 使用者 1 的訪問次數 +1
    $redis->incr('total:visits');    // 全站訪問次數 +1
});
// 這段程式會在 Redis 交易中同時執行兩個自增操作，確保原子性
```

---

## 8. **Lua 腳本**（Eval）

### 8.1 *理論*

- Redis 支援 `Lua 腳本（eval）`，可 __在腳本內`讀寫`資料、做`邏輯判斷`，確保複雜操作原子性__。
- 適合`搶購、排行榜、分散式鎖`等場景。

---

### 8.2 *範例*

```php
// 使用 Lua 腳本在 Redis 執行原子操作
$value = Redis::eval(<<<'LUA'
    -- 將第一個 key 的值加一
    local counter = redis.call("incr", KEYS[1])
    -- 如果加一後的值大於 5，則將第二個 key 的值加一
    if counter > 5 then
        redis.call("incr", KEYS[2])
    end
    -- 回傳第一個 key 的最新值
    return counter
LUA, 2, 'first-counter', 'second-counter');
```

---

## 9. **Pipeline 批次指令**

### 9.1 *理論*

- `Pipeline` 可 __將多個 Redis 指令一次送到伺服器，減少網路延遲__（但不保證原子性）。
- 適合`大量寫入、初始化資料、批次操作`。

---

### 9.2 *範例*

```php
// 使用 Redis pipeline 批次設定 1000 個使用者分數，提升效能
Redis::pipeline(function ($pipe) {
    for ($i = 0; $i < 1000; $i++) {
        // 設定 key 為 user:score:$i，值為 1~100 的隨機數
        $pipe->set("user:score:$i", rand(1, 100));
    }
});
```

---

## 10. **Pub/Sub 與 psubscribe**

### 10.1 *Pub/Sub*

```php
// Artisan Command 訂閱頻道
Redis::subscribe(['news'], function ($message) {
    echo "收到訊息: $message\n";
});

// Controller 發佈訊息
Redis::publish('news', json_encode(['title' => '新消息']));
```

---

### 10.2 *psubscribe*

```php
// 使用 Redis 的 psubscribe 監聽所有符合 users.* 的頻道
Redis::psubscribe(['users.*'], function ($message, $channel) {
    // 當有訊息時，輸出頻道名稱與訊息內容
    echo "[$channel] $message\n";
});
```

---

## **常見 Q&A**

- Q: _Laravel Redis 支援哪些資料結構？_
  - A: `string、hash、list、set、sorted set`。

- Q: _如何切換 Predis/PhpRedis？_
  - A: 設定 __.env__ 的 `REDIS_CLIENT=predis 或 phpredis`。

- Q: _Redis cluster 與 sharding 差異？_
  - A: __cluster__ 支援自動 `failover`，__sharding__ `只分片不自動容錯`。

- Q: _Redis 可以做分散式鎖嗎？_
  - A: 可以，Laravel 提供 `Cache::lock()` API。

- Q: _Redis 可以用來做什麼？_
  - A: `快取、Session、Queue、排行榜、即時訊息、分散式鎖`等。

--- 

## 1. **動態方法**（Magic Methods）

### *專案級實作：User Profile Controller*

```php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\View;

class UserController extends Controller
{
    // 顯示用戶個人檔案，資料來自 Redis
    public function show(string $id): View
    {
        $user = Redis::get('user:profile:' . $id); // 直接用動態方法 get 取得資料
        return view('user.profile', ['user' => $user]);
    }
}
```

---

## 2. **多連線**（Multiple Connections）


### *專案級實作：多 Redis 連線 Service*

```php
// app/Services/MultiRedisService.php
namespace App\Services;
use Illuminate\Support\Facades\Redis;

class MultiRedisService
{
    // 寫入 cache 連線
    public function cacheSet($key, $value)
    {
        Redis::connection('cache')->set($key, $value);
    }
    // 讀取 session 連線
    public function sessionGet($key)
    {
        return Redis::connection('session')->get($key);
    }
}
```

---

## 3. **交易**（Transaction）

### *專案級實作：購物車結帳交易*

```php
// app/Services/CheckoutService.php
namespace App\Services;
use Illuminate\Support\Facades\Redis;

class CheckoutService
{
    // 結帳時同時扣庫存與記錄訂單數，確保原子性
    public function checkout($userId, $productId)
    {
        Redis::transaction(function ($redis) use ($userId, $productId) {
            $redis->decr('stock:product:' . $productId); // 扣庫存
            $redis->incr('user:' . $userId . ':orders'); // 記錄用戶訂單數
        });
    }
}
```

---

## 4. **Lua 腳本**（Eval）

### *專案級實作：搶購原子操作*

```php
// app/Services/SeckillService.php

namespace App\Services;
use Illuminate\Support\Facades\Redis;

class SeckillService
{
    // 原子性搶購，庫存大於 0 才能扣減
    public function seckill($productId, $userId)
    {
        $lua = <<<'LUA'
            local stock = tonumber(redis.call('get', KEYS[1]))         -- 取得商品庫存
            if stock > 0 then
                redis.call('decr', KEYS[1])                            -- 庫存扣減 1
                redis.call('sadd', KEYS[2], ARGV[1])                   -- 記錄搶購成功的 userId
                return 1                                               -- 回傳成功
            else
                return 0                                               -- 庫存不足，回傳失敗
            end
        LUA;
        $result = Redis::eval($lua, 2, 'stock:product:' . $productId, 'buyers:product:' . $productId, $userId);
        return $result === 1; // 回傳是否搶購成功
    }
}
```

---

## 5. **Pipeline 批次指令**

### *專案級實作：批次初始化排行榜*

```php
// app/Services/RankingInitService.php

namespace App\Services;
use Illuminate\Support\Facades\Redis;

class RankingInitService
{
    // 批次寫入 1000 筆用戶分數到排行榜
    public function initRanking()
    {
        Redis::pipeline(function ($pipe) {
            for ($i = 1; $i <= 1000; $i++) {
                $pipe->zadd('ranking', rand(1, 1000), 'user' . $i); // 將 user 分數加入 Redis 排行榜
            }
        }); // 使用 pipeline 批次執行，提升效能
    }
}
```

---

## 6. **Pub/Sub**（訂閱/發佈）

### *專案級實作：Artisan Command 訂閱與 Controller 發佈*

```php
// app/Console/Commands/NewsSubscriber.php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class NewsSubscriber extends Command
{
    protected $signature = 'redis:news-subscribe'; // 指令名稱
    protected $description = '訂閱 news 頻道';      // 指令說明

    public function handle()
    {
        // 訂閱 news 頻道，收到訊息時顯示內容
        Redis::subscribe(['news'], function ($message) {
            $this->info('收到訊息: ' . $message);
        });
    }
}

// app/Http/Controllers/NewsController.php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    // 發佈新聞訊息
    public function publish(Request $request)
    {
        Redis::publish('news', $request->input('content')); // 發佈訊息到 news 頻道
        return response()->json(['status' => 'ok']);        // 回傳成功狀態
    }
}
```

---

## 7. **萬用訂閱**（PSubscribe）

### *專案級實作：Artisan Command 萬用訂閱*

```php
// app/Console/Commands/UserEventSubscriber.php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class UserEventSubscriber extends Command
{
    protected $signature = 'redis:user-event-subscribe'; // 指令名稱
    protected $description = '萬用訂閱 users.* 頻道';    // 指令說明

    public function handle()
    {
        // 訂閱所有 users.* 頻道，收到訊息時顯示頻道與內容
        Redis::psubscribe(['users.*'], function ($message, $channel) {
            $this->info("[$channel] $message");
        });
    }
}
```

---

## 8. **Cluster 實作**

### *專案級實作：Cluster 連線操作*

```php
// app/Services/ClusterService.php
namespace App\Services;
use Illuminate\Support\Facades\Redis;

class ClusterService
{
    // 寫入 cluster 連線
    public function setClusterValue($key, $value)
    {
        Redis::connection('clusters.default')->set($key, $value); // 在 clusters.default 連線寫入 key/value
    }
}
```

---

## 9. **序列化壓縮**

### *專案級實作：設定與存取 JSON 序列化資料*

```php
// config/database.php
'options' => [
    'serializer' => Redis::SERIALIZER_JSON,
],

// app/Services/JsonCacheService.php
namespace App\Services;
use Illuminate\Support\Facades\Redis;

class JsonCacheService
{
    public function setUser($id, array $data)
    {
        Redis::set('user:json:' . $id, $data); // 自動用 JSON 序列化
    }
    public function getUser($id)
    {
        return Redis::get('user:json:' . $id); // 自動反序列化
    }
}
```

---

## 10. **Unix socket 實作**

### *專案級實作：Unix socket 連線*

```env
# .env 設定
REDIS_HOST=/run/redis/redis.sock
REDIS_PORT=0
```

```php
// app/Services/UnixSocketService.php
namespace App\Services;
use Illuminate\Support\Facades\Redis;

class UnixSocketService
{
    public function setValue($key, $value)
    {
        Redis::set($key, $value); // 會自動用 unix socket 連線
    }
}
```

---

## 11. **Predis/PhpRedis 切換**

### *專案級實作：.env 切換*

```env
# .env
REDIS_CLIENT=predis
# 或
REDIS_CLIENT=phpredis
```

- 只要切換 __.env__，所有 Redis 操作`自動切換` __底層 client__，程式碼不需更動。

--- 