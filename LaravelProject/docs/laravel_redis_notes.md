# Laravel Redis 超完整中文筆記與實作範例

---

## 1. Redis 介紹
- Redis 是開源、先進的 key-value 資料庫，支援多種資料結構（string、hash、list、set、sorted set）。
- 常用於快取、Session、排行榜、即時訊息、分散式鎖等。
- 特色：高效能、支援持久化、支援叢集、支援 pub/sub。

---

## 2. PhpRedis/Predis 安裝與選擇

### 2.1 PhpRedis
- 官方推薦，C 語言寫的 PHP 擴充，效能最佳。
- 安裝：
  ```bash
  pecl install redis
  # 或用 apt/yum/brew 安裝
  ```
- Laravel Sail、Homestead 預設已安裝。
- 優點：效能高、支援進階功能。
- 缺點：需伺服器權限安裝。

### 2.2 Predis
- 純 PHP 實作，composer 安裝即可。
- 安裝：
  ```bash
  composer require predis/predis
  ```
- 優點：安裝簡單，無需額外擴充。
- 缺點：效能略低於 PhpRedis。

---

## 3. Laravel Redis 設定（config/database.php）

### 3.1 基本設定
```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],
    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
],
```
- `client`：phpredis 或 predis。
- `options.cluster`：redis（原生 cluster）、predis（client-side sharding）。
- `options.prefix`：所有 key 前綴，避免多專案衝突。
- 可設定多個連線（default、cache、session…）。

### 3.2 URL 寫法
```php
'default' => [
    'url' => 'tcp://127.0.0.1:6379?database=0',
],
'cache' => [
    'url' => 'tls://user:password@127.0.0.1:6380?database=1',
],
```

### 3.3 TLS/SSL 加密
```php
'default' => [
    'scheme' => 'tls',
    'url' => env('REDIS_URL'),
    ...
],
```
- `scheme` 設為 `tls` 可啟用加密連線。

### 3.4 Unix Socket
```env
REDIS_HOST=/run/redis/redis.sock
REDIS_PORT=0
```
- 適合本機高效能需求。

### 3.5 序列化與壓縮（PhpRedis）
```php
'options' => [
    'serializer' => Redis::SERIALIZER_MSGPACK, // 支援 NONE, PHP, JSON, IGBINARY, MSGPACK
    'compression' => Redis::COMPRESSION_LZ4,   // 支援 NONE, LZF, ZSTD, LZ4
],
```
- 節省空間、提升傳輸效率，需 client 兼容。

---

## 4. Redis Cluster 與 Predis Sharding

### 4.1 Redis Cluster
```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', ...),
    ],
    'clusters' => [
        'default' => [
            [
                'url' => env('REDIS_URL'),
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'username' => env('REDIS_USERNAME'),
                'password' => env('REDIS_PASSWORD'),
                'port' => env('REDIS_PORT', '6379'),
                'database' => env('REDIS_DB', '0'),
            ],
            // ...可多台
        ],
    ],
],
```
- 支援自動 failover。

### 4.2 Predis Sharding
- Predis 支援 client-side sharding（分片），但不支援自動 failover。
- 適合暫存資料。
- 設定方式：移除 `options.cluster`，只保留 `clusters`。

---

## 5. 進階連線參數

### 5.1 Predis
```php
'default' => [
    ...
    'read_write_timeout' => 60, // 讀寫逾時秒數
],
```

### 5.2 PhpRedis
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
- 其他支援：name, persistent, persistent_id, prefix, retry_interval, max_retries, backoff_algorithm, timeout, ...

---

## 6. Redis Facade 操作

### 6.1 動態方法
```php
use Illuminate\Support\Facades\Redis;

Redis::set('name', 'Vincent');
$name = Redis::get('name');
$values = Redis::lrange('names', 0, 10);
```
- 任何 Redis 指令都可直接呼叫。

### 6.2 command 方法
```php
$values = Redis::command('lrange', ['names', 0, 10]);
```
- 明確指定指令與參數。

### 6.3 多連線
```php
$redis = Redis::connection('cache');
$redis->set('foo', 'bar');
```
- 用於分流、分群、主從架構。

---

## 7. 交易（Transaction）

### 7.1 理論
- Redis MULTI/EXEC 可將多個指令包成原子操作。
- Laravel 用 Redis::transaction 傳入 closure，closure 內所有指令會包成一個 MULTI/EXEC。
- 交易內不能讀取資料，只能寫入。

### 7.2 範例
```php
Redis::transaction(function ($redis) {
    $redis->incr('user:1:visits');
    $redis->incr('total:visits');
});
```

---

## 8. Lua 腳本（Eval）

### 8.1 理論
- Redis 支援 Lua 腳本（eval），可在腳本內讀寫資料、做邏輯判斷，確保複雜操作原子性。
- 適合搶購、排行榜、分散式鎖等場景。

### 8.2 範例
```php
$value = Redis::eval(<<<'LUA'
    local counter = redis.call("incr", KEYS[1])
    if counter > 5 then
        redis.call("incr", KEYS[2])
    end
    return counter
LUA, 2, 'first-counter', 'second-counter');
```

---

## 9. Pipeline 批次指令

### 9.1 理論
- Pipeline 可將多個 Redis 指令一次送到伺服器，減少網路延遲（但不保證原子性）。
- 適合大量寫入、初始化資料、批次操作。

### 9.2 範例
```php
Redis::pipeline(function ($pipe) {
    for ($i = 0; $i < 1000; $i++) {
        $pipe->set("user:score:$i", rand(1, 100));
    }
});
```

---

## 10. Pub/Sub 與 psubscribe

### 10.1 Pub/Sub
```php
// Artisan Command 訂閱頻道
Redis::subscribe(['news'], function ($message) {
    echo "收到訊息: $message\n";
});

// Controller 發佈訊息
Redis::publish('news', json_encode(['title' => '新消息']));
```

### 10.2 psubscribe
```php
Redis::psubscribe(['users.*'], function ($message, $channel) {
    echo "[$channel] $message\n";
});
```

---

## 常見 Q&A
- Q: Laravel Redis 支援哪些資料結構？
  - A: string、hash、list、set、sorted set。
- Q: 如何切換 Predis/PhpRedis？
  - A: 設定 .env 的 REDIS_CLIENT=predis 或 phpredis。
- Q: Redis cluster 與 sharding 差異？
  - A: cluster 支援自動 failover，sharding 只分片不自動容錯。
- Q: Redis 可以做分散式鎖嗎？
  - A: 可以，Laravel 提供 Cache::lock() API。
- Q: Redis 可以用來做什麼？
  - A: 快取、Session、Queue、排行榜、即時訊息、分散式鎖等。

--- 

## 1. 動態方法（Magic Methods）

### 專案級實作：User Profile Controller
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

## 2. 多連線（Multiple Connections）

### 專案級實作：多 Redis 連線 Service
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

## 3. 交易（Transaction）

### 專案級實作：購物車結帳交易
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

## 4. Lua 腳本（Eval）

### 專案級實作：搶購原子操作
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
            local stock = tonumber(redis.call('get', KEYS[1]))
            if stock > 0 then
                redis.call('decr', KEYS[1])
                redis.call('sadd', KEYS[2], ARGV[1])
                return 1
            else
                return 0
            end
        LUA;
        $result = Redis::eval($lua, 2, 'stock:product:' . $productId, 'buyers:product:' . $productId, $userId);
        return $result === 1;
    }
}
```

---

## 5. Pipeline 批次指令

### 專案級實作：批次初始化排行榜
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
                $pipe->zadd('ranking', rand(1, 1000), 'user' . $i);
            }
        });
    }
}
```

---

## 6. Pub/Sub（訂閱/發佈）

### 專案級實作：Artisan Command 訂閱與 Controller 發佈
```php
// app/Console/Commands/NewsSubscriber.php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class NewsSubscriber extends Command
{
    protected $signature = 'redis:news-subscribe';
    protected $description = '訂閱 news 頻道';
    public function handle()
    {
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
        Redis::publish('news', $request->input('content'));
        return response()->json(['status' => 'ok']);
    }
}
```

---

## 7. 萬用訂閱（PSubscribe）

### 專案級實作：Artisan Command 萬用訂閱
```php
// app/Console/Commands/UserEventSubscriber.php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class UserEventSubscriber extends Command
{
    protected $signature = 'redis:user-event-subscribe';
    protected $description = '萬用訂閱 users.* 頻道';
    public function handle()
    {
        Redis::psubscribe(['users.*'], function ($message, $channel) {
            $this->info("[$channel] $message");
        });
    }
}
```

---

## 8. Cluster 實作

### 專案級實作：Cluster 連線操作
```php
// app/Services/ClusterService.php
namespace App\Services;
use Illuminate\Support\Facades\Redis;

class ClusterService
{
    // 寫入 cluster 連線
    public function setClusterValue($key, $value)
    {
        Redis::connection('clusters.default')->set($key, $value);
    }
}
```

---

## 9. 序列化壓縮

### 專案級實作：設定與存取 JSON 序列化資料
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

## 10. Unix socket 實作

### 專案級實作：Unix socket 連線
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

## 11. Predis/PhpRedis 切換

### 專案級實作：.env 切換
```env
# .env
REDIS_CLIENT=predis
# 或
REDIS_CLIENT=phpredis
```
- 只要切換 .env，所有 Redis 操作自動切換底層 client，程式碼不需更動。

--- 