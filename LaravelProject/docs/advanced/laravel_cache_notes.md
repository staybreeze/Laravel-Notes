# *Laravel Cache 快取系統完整筆記*

## 1. **什麼是 Cache？為什麼要用？**
- Cache（快取）是將耗時或重複查詢的資料暫存起來，加速下次存取。
- *常見用途*：API 回應、熱門文章、排行榜、設定檔、Session、第三方 API 結果等。
- 快取資料通常存於高速儲存（如 Redis、Memcached），大幅減少資料庫壓力、提升效能。

## 2. **Laravel Cache 設定**
- 設定檔：`config/cache.php`
- 可設定預設「快取驅動（driver）」：如 redis、memcached、database、file、array、null、dynamodb、mongodb。
- 可針對不同用途設定多個快取 store。
- `.env` 需設定 CACHE_DRIVER、REDIS/MEMCACHED/DYNAMODB/MONGODB 相關參數。

## 3. **各快取驅動安裝與設定**

### 3.1 *Database*
- 需有一個**快取用的資料表**（預設 migration: `create_cache_table.php`）。
- 若無此 migration，可用指令建立：
  ```bash
  php artisan make:cache-table
  php artisan migrate
  ```
- config/cache.php 範例：
  ```php
  'default' => env('CACHE_DRIVER', 'database'),
  ```

#### **為什麼 cache 需要專屬 table？**
  - *快取資料結構特殊*：快取是 **key-value 形式**，內容可為*序列化字串、陣列、物件*，和一般業務資料表結構不同。
    - 快取資料不像用戶、訂單那樣有多欄位，通常只需要一個 key 和一個 value，value 可以存任何型別（序列化後的字串、陣列、物件等），所以設計上要簡單、彈性。
  - *生命週期短且頻繁變動*：快取資料常**自動過期**、被覆蓋、清空，和一般資料（如用戶、訂單）存取模式不同。
    - 快取資料可能幾秒、幾分鐘就會自動失效或被新資料覆蓋，不像業務資料需要長期保存。
  - *效能最佳化*：快取表**只需查 key、寫入 key-value、刪除 key**，設計上會針對這些操作加索引，避免影響其他表效能。
    - 快取操作很頻繁，查詢和寫入都只針對 key，設計上會針對 key 加索引，讓查詢/覆蓋/刪除都很快，不會拖慢資料庫。
  - *安全隔離*：快取資料可隨時 flush，若和業務資料混在一起，容易誤刪，**專屬 table 可避免風險**。
    - 快取資料可以全部清空（flush），如果和重要資料混在一起，可能會誤刪到業務資料，所以一定要分開存放。

#### **Laravel cache table 標準結構**
```php
Schema::create('cache', function (Blueprint $table) {
    $table->string('key')->unique();      // 快取 key，唯一索引，查詢快
    $table->text('value');                // 存序列化後的資料，型態彈性
    $table->integer('expiration');        // 過期時間（timestamp），方便自動清除
});
```
- **簡單扁平**：只存 key、value、expiration，避免複雜欄位，提升效能。
- **唯一索引**：key 欄位加 unique，確保查詢/覆蓋快。
- **過期機制**：expiration 欄位方便定期清除過期快取。

### 3.2 *Memcached*
- 需安裝 Memcached PECL 套件（伺服器端也要有 memcached）。
- 設定 servers 於 config/cache.php：
  ```php
  'memcached' => [
      'servers' => [
          [
              'host' => env('MEMCACHED_HOST', '127.0.0.1'),
              'port' => env('MEMCACHED_PORT', 11211),
              'weight' => 100,
          ],
      ],
  ],
  ```
- 支援 UNIX socket：
  ```php
  'host' => '/var/run/memcached/memcached.sock',
  'port' => 0,
  ```

### 3.3 *Redis*
- 需安裝 redis 伺服器，PHP 端需安裝 phpredis（推薦）或 predis/predis 套件。
- Laravel Sail、Forge、Cloud 預設已安裝 phpredis。
- .env 設定：
  ```
  CACHE_DRIVER=redis
  REDIS_HOST=127.0.0.1
  REDIS_PASSWORD=null
  REDIS_PORT=6379
  ```
- config/database.php 設定 redis 連線資訊。

### 3.4 *DynamoDB*
- 需建立 DynamoDB 資料表（預設名稱 cache，可自訂）。
- Partition key 預設為 key（可自訂）。
- 建議啟用 TTL（Time to Live），屬性名稱為 expires_at。
- 需安裝 AWS SDK：
  ```bash
  composer require aws/aws-sdk-php
  ```
- .env 設定：
  ```
  AWS_ACCESS_KEY_ID=xxx
  AWS_SECRET_ACCESS_KEY=xxx
  AWS_DEFAULT_REGION=ap-northeast-1
  DYNAMODB_CACHE_TABLE=cache
  ```
- config/cache.php 範例：
  ```php
  'dynamodb' => [
      'driver' => 'dynamodb',
      'key' => env('AWS_ACCESS_KEY_ID'),
      'secret' => env('AWS_SECRET_ACCESS_KEY'),
      'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
      'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
      'endpoint' => env('DYNAMODB_ENDPOINT'),
  ],
  ```

### 3.5 *MongoDB*
- 需安裝官方套件：
  ```bash
  composer require mongodb/laravel-mongodb
  ```
- 支援 TTL index，自動清除過期快取。
- 參考官方文件設定連線與快取 store。

### 3.6 *其他驅動*
- file：存於 storage/framework/cache/data，適合小型專案。
- array：只存在記憶體，適合測試。
- null：不存任何東西，關閉快取用。

## 4. **快取常用 API 與實作範例**

### 4.1 *put / get*
```php
// 路徑：app/Http/Controllers/CacheDemoController.php
// 寫入快取（60 秒）
Cache::put('key', 'value', 60); // 將 key 對應的值設為 value，並設定 60 秒後自動過期
// 讀取快取
$value = Cache::get('key'); // 取得 key 對應的快取值，若不存在回傳 null
```

### 4.2 *remember*
```php
// 路徑：app/Http/Controllers/CacheDemoController.php
// 若無快取則執行 closure 並快取 10 分鐘
$data = Cache::remember('users.all', 600, function() {
    return User::all(); // 查詢所有用戶，並將結果快取 600 秒
}); // 若快取存在直接回傳，否則執行 closure 並快取
```

### 4.3 *rememberForever*
```php
// 永久快取（直到手動清除）
Cache::rememberForever('site.settings', function() {
    return Setting::all(); // 查詢所有設定，並永久快取
}); // 若快取存在直接回傳，否則執行 closure 並永久快取
```

### 4.4 *forget*
```php
// 刪除快取
Cache::forget('key'); // 刪除 key 對應的快取
```

### 4.5 *tags*（僅支援 redis/memcached）
```php
// 設定 tag 快取
Cache::tags(['people', 'artists'])->put('John', $john, 600); // 將 John 這個 key 加入 people、artists 兩個 tag，快取 600 秒
// 1. 這行會把 key 為 'John' 的快取資料，同時歸類到 'people' 和 'artists' 這兩個 tag。
// 2. $john 可以是任意資料（如物件、陣列、字串等），會被序列化存進快取。
// 3. 600 代表這筆快取 600 秒後自動過期。
// 4. 只有 redis、memcached 這兩種 driver 支援 tag 功能，file/database/array driver 都不支援。
// 5. 這種設計讓你可以用 tag 批次管理一群相關快取（例如同一類型的資料）。

// 清除 tag 下所有快取
Cache::tags('people')->flush(); // 清除 people 這個 tag 下所有快取
// 1. 這行會把所有被標記為 'people' 這個 tag 的快取資料全部清除（不論 key 是什麼）。
// 2. 這樣你就可以很方便地批次清除一群相關快取（例如某個類別的所有資料變動時）。
// 3. flush 只會影響這個 tag 下的快取，不會影響其他 tag 或未標記的快取。
// 4. 這種設計非常適合「一群資料同時失效」的場景（如某個分類下的所有商品、某個作者的所有文章等）。
```
- **筆記**：
  - tags 適合需要分群管理快取、批次失效的場景（如分類、群組、作者等）。
  - 只有 redis、memcached 支援 tags，file/database/array driver 不支援。
  - flush 只會清除指定 tag 下的快取，不會影響其他 tag 或全域快取。
  - tags 讓快取失效更有彈性，適合中大型專案。

### 4.6 *清除所有快取*
```bash
php artisan cache:clear # 清除所有快取（所有 driver 都會被清空）
```

## 5. **實作範例**

### 5.1 *熱門文章查詢快取*
```php
// 路徑：app/Http/Controllers/ArticleController.php
public function hot()
{
    $articles = Cache::remember('hot_articles', 300, function() {
        return Article::orderBy('views', 'desc')->take(10)->get(); // 查詢瀏覽數最多的前 10 篇文章
    }); // 若快取存在直接回傳，否則查詢並快取 300 秒
    return view('articles.hot', compact('articles')); // 回傳熱門文章頁面
}
```

### 5.2 *快取 API 回應*
```php
// 路徑：app/Http/Controllers/ApiController.php
public function stats()
{
    $stats = Cache::remember('api.stats', 60, function() {
        // 'api.stats' 是這份快取的 key（名稱），用來標記這份資料
        return [
            'users' => User::count(), // 查詢用戶總數，存在 value 陣列的 users 欄位
            'posts' => Post::count(), // 查詢貼文總數，存在 value 陣列的 posts 欄位
        ];
    }); // 如果 'api.stats' 這個 key 的快取不存在，才會執行 closure 查詢並快取 60 秒
    // $stats 會是 ['users' => 數字, 'posts' => 數字]，來源可能是快取，也可能是剛查出來的
    return response()->json($stats); // 回傳 JSON 格式的統計資料
}
```
－ **說明**：
  - 'api.stats' 是快取 key，對應的 value 是一個陣列（裡面有 users/posts 欄位）。
  - 這裡的點號（.）在快取 key 僅是*命名慣例*，方便分群與閱讀，對快取系統來說完全沒有語法意義，純粹是字串的一部分。
  - 在 Laravel 其他地方（如 config 設定、翻譯檔、Eloquent 關聯）點號有特殊意義（如分層、關聯），但在快取 key 這裡完全沒有，只是為了可讀性與分群。
  - 你可以用底線、冒號、dash 等其他符號命名，只要全專案唯一、不會衝突即可。
  - **注意**：快取 key 的點號和 config、翻譯、Eloquent 關聯的點號意義完全不同，這裡只是字串，沒有任何分層或物件屬性意義。
  - 只要快取還在（60 秒內），users/posts 的值都從快取拿，不會再查資料庫。
  - 快取不存在時才會查資料庫並更新快取。
  - 這種設計可以大幅減少資料庫查詢，提升 API 效能。


### 5.3 *快取設定檔*
```php
// 路徑：app/Http/Controllers/SettingController.php
public function getSettings()
{
    $settings = Cache::rememberForever('site.settings', function() {
        return Setting::all(); // 查詢所有設定，並永久快取
    }); // 若快取存在直接回傳，否則查詢並永久快取
    return $settings; // 回傳設定資料
}
```

## 6. **常見問題 Q&A**
- Q: *Cache 會自動過期嗎？*
  - A: 會，根據設定的秒數自動失效。DynamoDB/MongoDB 需設 TTL。

- Q: *如何清除所有快取？*
  - A: `php artisan cache:clear`

- Q: *可以快取資料庫查詢結果嗎？*
  - A: 可以，建議用 remember 包住查詢。

- Q: *tags 只能用在 redis/memcached 嗎？*
  - A: 是，file/database driver 不支援 tags。

- Q: *如何永久快取？*
  - A: 用 `rememberForever`。

- Q: *DynamoDB/MongoDB 如何自動清除過期？*
  - A: 需設 TTL 屬性，DynamoDB 設 expires_at，MongoDB 設 TTL index。

- Q: *測試時如何關閉快取？*
  - A: 設定 CACHE_DRIVER=array 或 null。

## 7. **文件與註解建議**
- 重要程式碼區塊皆加上檔案路徑註解。
- 團隊文件建議多用 Q&A、條列、註解，方便維護與學習。
- 若有進階需求（如分層快取、Cache Lock、分散式快取），可再補充。

---

## 8. **Cache 使用方式（Cache Usage）**

### 8.1 *取得快取實例*
- 使用 Cache facade 取得快取 store 實例，提供統一 API 操作各種快取後端。
- 範例：
```php
use Illuminate\Support\Facades\Cache;

$value = Cache::get('key');
```

### 8.2 *存取多個快取 store*
- 可用 store 方法切換不同快取 store（對應 config/cache.php 的 stores 設定）。
- 範例：
```php
$value = Cache::store('file')->get('foo');
Cache::store('redis')->put('bar', 'baz', 600); // 10 分鐘
```
- **註解**：
  - store('file')、store('redis') 代表 *明確指定* 要用哪一個快取 store（對應 config/cache.php 的 stores 設定）。
  - 每個 store *可以用不同的 driver*（file、redis、database…），資料互不干擾。
  - Laravel 會根據 store 名稱自動找到對應的快取系統（如 file 存本地檔案、redis 存雲端 Redis）。
  - 這種設計讓你可以靈活分流、分群、分層管理快取資料。
  - 適合有多種快取需求、或需要分開管理不同類型快取的專案。
  - 例如 *session* 存 redis，*設定檔*快取存 file，*臨時資料*存 database。
  - 你可以在 config/cache.php 設定多個 store，Laravel 會自動幫你連到正確的快取系統。

### 8.3 *取得快取資料（get）*
- get 方法可取得快取資料，**若不存在回傳 null**。
- 可傳第二參數作為**預設值**，或傳 closure 延遲取得預設值。
- 範例：
```php
$value = Cache::get('key');
$value = Cache::get('key', 'default');
$value = Cache::get('key', function () { // 取得 key 這個快取的值，如果不存在就執行後面的 function
    return DB::table('users')->get();   // 這個 function 會查詢 users 資料表的所有資料
}); // $value 會是 key 的快取值，若不存在則是查詢結果
```

### 8.4 *判斷快取是否存在（has）*
- has 方法可判斷快取是否存在，若值為 null 也會回傳 false。
- 範例：
```php
if (Cache::has('key')) {
    // ...
}
```

### 8.5 *整數快取自增/自減（increment/decrement）*
- increment/decrement 可對快取中的整數值加減。
- add 方法可初始化不存在的 key。
- 範例：
```php
Cache::add('key', 0, now()->addHours(4)); // 如果 key 不存在，將其設為 0，並設定 4 小時後自動過期（只會初始化一次）
Cache::increment('key'); // 將 key 的值加 1
Cache::increment('key', 5); // 將 key 的值加 5
Cache::decrement('key'); // 將 key 的值減 1
Cache::decrement('key', 2); // 將 key 的值減 2
```

### 8.6 *取得並快取（remember/rememberForever）*
- **remember**：若快取不存在，執行 closure 並快取指定秒數。
- **rememberForever**：若快取不存在，執行 closure 並永久快取。
- 範例：
```php
$value = Cache::remember('users', 600, function () {
    return DB::table('users')->get();
});

$value = Cache::rememberForever('users', function () {
    return DB::table('users')->get();
});
```

### 8.7 *Stale While Revalidate（彈性快取）*
- flexible 方法可設定 fresh/stale 兩階段，fresh 期間直接回傳，stale 期間回傳舊值並背景刷新。
- 範例：
```php
$value = Cache::flexible('users', [5, 10], function () { // 對 'users' 這個 key 使用 flexible 快取策略
    return DB::table('users')->get(); // 當快取需要重新整理時，執行這個 closure 查詢所有用戶
}); // $value 會根據快取狀態，回傳 fresh、stale 或重新查詢的資料
```
- **筆記**：
  - [5, 10] 代表 fresh 5 秒、stale 5 秒（第 6~10 秒），超過 10 秒完全過期。
    - 5 秒內（fresh）：直接回傳快取值。
    - 6~10 秒（stale）：回傳舊快取值，並在背景自動刷新快取。
    - 超過 10 秒（expired）：等 closure 查詢完才回傳新資料。
  - 適合查詢成本高、但又希望大部分用戶都能快速拿到資料的場景（如排行榜、熱門文章、統計數據等）。
  - 提升用戶體驗、降低資料庫壓力，兼顧效能與資料新鮮度。
  - 背景刷新是非同步，stale 期間用戶拿到的是舊資料，但很快會被新資料取代。

### 8.8 *取得並刪除（pull）*
- pull 方法可取得快取並同時刪除。
- 範例：
```php
$value = Cache::pull('key'); // 取得 key 這個快取的值，並同時從快取中刪除，如果不存在回傳 null
$value = Cache::pull('key', 'default'); // 取得 key 這個快取的值，並同時刪除，如果不存在則回傳 'default'（預設值）
```
- **註解**：
  - pull 會「取出並刪除」快取，常用於一次性資料（如驗證碼、臨時 token、一次性通知等）。
  - 第二個參數 default 讓你不用再判斷 null，直接給一個預設值，讓程式更安全、簡潔。
  - 這是 Laravel 設計上為了方便開發者、減少 if 判斷的貼心設計。

### 8.9 *寫入快取（put/add/forever）*
- **put**：寫入快取，可指定秒數或 DateTime。
- **add**：僅當 key 不存在時寫入。
- **forever**：永久快取，需手動刪除。
- 範例：
```php
Cache::put('key', 'value', 10); // 10 秒
Cache::put('key', 'value', now()->addMinutes(10));
Cache::add('key', 'value', 60); // 僅當 key 不存在時
Cache::forever('key', 'value');
```

### 8.10 *刪除快取（forget/flush）*
- **forget**：刪除單一 key。
- **flush**：清空所有快取（不分 prefix，請小心使用）。
- 也可用 **put('key', 'value', 0)** 立即過期。
- 範例：
```php
Cache::forget('key');
Cache::put('key', 'value', 0);
Cache::flush();
```

### 8.11 *Memoization（記憶快取）*
- memo driver 可於單一請求/任務內暫存快取值，避免重複查詢。
- 範例：
```php
$value = Cache::memo()->get('key'); // 第一次會查快取，並把結果暫存於記憶體
$value = Cache::memo('redis')->get('key'); // 指定底層使用 redis store，第一次查快取，之後同請求內都用記憶體
```
- 第一次執行時會去 Redis 查詢 key 的快取值，並把結果暫存到 PHP 這個請求的記憶體裡。
- 之後同一個 HTTP 請求或 queue 任務內，再查同一個 key，Laravel 會直接從 PHP 記憶體拿值，不會再查 Redis。
- 這樣可以大幅減少對 Redis 的查詢，提升效能，特別適合同一請求內多次用到同一快取的情境。
- 請求結束後，PHP 記憶體會釋放，下次新請求還是會查 Redis。
- 這種設計能減少快取伺服器壓力，提升高併發下的效能。
Cache::memo()->put('name', 'Taylor'); 寫入快取並同步更新記憶體暫存
－ **註解**：
  - memo driver 會在同一個請求或任務內，*把查到的快取值暫存於記憶體，之後同一個 key 不會再查快取伺服器*。
  - （這裡的「記憶體」是指 *PHP 伺服器端、單一請求/任務內的暫存*，不是用戶端 RAM，也不是 Redis/Memcached 的記憶體）
  - 這樣可以大幅減少重複查詢，提升效能（尤其是同一請求內多次用到同一快取時）。
  - 只在單一請求/任務內有效，跨請求還是會查快取伺服器。
  - put、increment 等會自動同步更新記憶體暫存，確保資料一致。
  - 適合高效能需求、同一請求內多次存取同一快取的場景。


### 8.12 *Cache 輔助函式（cache helper）*
- cache() 可快速取得/寫入快取。
- 範例：
```php
$value = cache('key'); // 取得 key 這個快取的值，若不存在回傳 null
cache(['key' => 'value'], 60); // 將 key 設為 value，快取 60 秒
// - 這裡的 'key' 是快取名稱，'value' 是你要存的資料內容（不是另一個 key）。
// - value 可以是字串、數字、陣列、物件等任何資料。
// - 你可以一次存多個 key-value 配對，例如 cache(['a' => 1, 'b' => 2], 60);
cache(['key' => 'value'], now()->addMinutes(10)); // 將 key 設為 value，10 分鐘後過期
$value = cache()->remember('users', 600, function () { // 取得 users 這個快取，若不存在則查詢並快取 600 秒
    return DB::table('users')->get(); // 查詢所有用戶
});
```
- 註解：
  - cache() 是 Laravel 提供的全域輔助函式，功能等同於 Cache facade，但語法更簡潔。
  - 傳入一個 key 字串時，等同於 Cache::get('key')。
  - 傳入陣列和過期時間時，等同於 Cache::put()。
  - 不帶參數時，回傳 Cache manager 實例，可呼叫所有快取 API（如 remember、put、forget 等）。
  - 適合在 blade、controller、service 等各種地方快速操作快取。


### 8.13 *Q&A*
- Q: **flexible 什麼時候適合用？**
  - A: 資料查詢成本高、可容忍短暫過期時（如排行榜、熱門文章）。

- Q: **memo driver 會寫入實體快取嗎？**
  - A: 只在單一請求/任務內記憶，跨請求仍會查詢快取。
  
- Q: **flush 會清掉所有快取嗎？**
  - A: 是，會清除所有快取（不分 prefix），請小心。

--- 

## 9. **原子鎖（Atomic Locks）**

### *什麼是分散式鎖（Distributed Lock）？*
- **白話解釋**：分散式鎖就是「*讓多台伺服器、多個程式、多個 worker 在同一時間只能有一個人做某件事*」的機制。
  - 就像一間有很多門的倉庫，大家都想進去搬貨，但規定「同一時間只能有一個人進去」，所以大家要搶同一把鑰匙，誰拿到誰進去，其他人只能等。
- **技術解釋**：
  - 在單一伺服器上可以用資料庫鎖、檔案鎖，但 *分散式系統（多台伺服器、多個 worker）時*，這些鎖無法跨機器同步。
  - 分散式鎖就是利用「大家都能存取的資源」（如 Redis、Memcached、Database、ZooKeeper 等）來實現「全世界同時只能有一個人拿到鎖」的效果。
- **實務舉例**：
  1. *排程任務防重複*：多台伺服器都跑 schedule:run，只希望同一任務同時只執行一次。
  2. *秒殺/搶購*：多台伺服器同時下單，庫存只能被扣一次。
  3. *分散式資源搶佔：*多個 queue worker 處理同一任務池，某些任務只能被一個 worker 處理。
- **常見實作方式**：
  - Redis（setnx/expire）、Database（唯一索引/for update）、ZooKeeper、Memcached（add 指令）等。
- **小結**：
  - 分散式鎖的本質是「大家都搶同一把鑰匙」，誰搶到誰做事，做完要記得還鑰匙（釋放鎖）。
  - 這是分散式系統、微服務、雲端架構下防止重複執行、資料衝突的關鍵技術。
  - Laravel 的 Cache::lock() 幫你包好這些細節，讓你專心寫業務邏輯。

### 9.2 *基本用法*
```php
use Illuminate\Support\Facades\Cache;

$lock = Cache::lock('foo', 10); // 建立一個名為 foo、存活 10 秒的鎖
if ($lock->get()) { // 嘗試取得鎖，成功才會回傳 true
    // 這裡的程式碼只有一個 process 能進來
    $lock->release(); // 用完記得釋放鎖
} else {
    // 沒搶到鎖，這裡會被執行
}
```
- lock('foo', 10)：建立一個名為 foo 的鎖，10 秒後自動失效（避免死鎖）。
- get()：嘗試搶鎖，搶到才會進入 if。
- release()：用完一定要釋放鎖，否則其他人會一直搶不到。

#### **也可以直接用 closure，執行完自動釋放**
```php
Cache::lock('foo', 10)->get(function () {
    // 這裡的程式碼只有一個 process 能進來
    // 執行完 closure 會自動釋放鎖
});
```
- 這種寫法不用自己 release，Laravel 幫你自動釋放鎖。

### 9.3 *等待鎖（block）*
```php
use Illuminate\Contracts\Cache\LockTimeoutException;

$lock = Cache::lock('foo', 10); // 建立一個名為 foo、存活 10 秒的鎖
try {
    $lock->block(5); // 最多等 5 秒來搶鎖（如果鎖被別人拿走了，這裡會等最多 5 秒，看對方會不會釋放）
    // 如果 5 秒內搶到鎖，會執行這裡的程式（你就是唯一能進來的人）
} catch (LockTimeoutException $e) {
    // 如果 5 秒內都搶不到鎖，會進到這裡（你可以記 log、回報錯誤、重試等）
} finally {
    $lock->release(); // 用完記得釋放鎖（只有真的搶到鎖才需要釋放）
}
```
- block(5)：代表「我最多等 5 秒」來搶鎖，這段期間如果鎖被釋放你就能進來。
- 如果 5 秒都沒搶到鎖，Laravel 會丟 LockTimeoutException 例外。
- 適合「不想馬上放棄、但又不想等太久」的場景。
- finally 裡的 release() 是保險，確保你有搶到鎖時會釋放。

#### **block 也可以用 closure 寫法**
```php
Cache::lock('foo', 10)->block(5, function () {
    // 只要搶到鎖就會執行這裡，執行完自動釋放鎖
});
```
- 這種 closure 寫法不用自己釋放鎖，Laravel 幫你自動處理。
- 實務上更安全，避免忘記釋放鎖。

－ *補充*：
  - block 很像「廁所門鎖著時你願意等幾分鐘」，等到就進去，等不到就放棄。
  - 適合排程、API 高併發、需要搶資源但又不想無限等待的場景。

### 9.4 *跨程序釋放鎖（owner token）*
```php
$lock = Cache::lock('processing', 120);
if ($lock->get()) {
    $token = $lock->owner(); // 取得 owner token
    ProcessPodcast::dispatch($podcast, $token); // 把 token 傳給 queue job
}
```
- owner()：取得這把鎖的唯一 token，之後可以交給其他 process 釋放。

**在 queue job 裡釋放鎖**：
```php
Cache::restoreLock('processing', $this->owner)->release();
```
- restoreLock('processing', $token)：用 token 恢復鎖，然後釋放。

#### **強制釋放（不檢查 owner）**
```php
Cache::lock('processing')->forceRelease();
```
- 不管 owner 是誰，直接把鎖釋放掉（小心用，避免 race condition）。

### 9.5 *Q&A 與補充筆記*
- **Q: 什麼情境要用原子鎖？**
  - A: 排程、批次、分散式任務、避免重複執行（如同一個訂單不能同時被兩個 worker 處理）。
- **Q: file/array driver 適合生產環境嗎？**
  - A: 不適合，只能本機測試，生產建議用 redis/memcached/dynamodb/database。
- **Q: 鎖會不會死鎖？**
  - A: Laravel 的鎖有自動過期機制（如 10 秒），即使忘記釋放也不會永遠卡住。
- **Q: 一定要 release 嗎？**
  - A: 建議用完就 release，或用 closure 讓 Laravel 幫你自動釋放。
- **Q: 多台伺服器怎麼同步？**
  - A: 只要連同一個快取伺服器（如同一台 Redis），鎖就能跨伺服器同步。
- **補充**：
  - *原子鎖*是分散式系統常見的「搶鎖」機制，能有效避免 race condition。
  - 適合高併發、分散式、批次、排程等場景，能有效避免重複執行、資料衝突。
  - Laravel 提供簡單 API，讓你不用自己處理 race condition、死鎖等複雜問題。
  - 記得用完要*釋放鎖*，或用 closure 讓 Laravel 幫你自動釋放。

## 10. **自訂快取驅動（Custom Cache Driver）**

### 10.1 *撰寫自訂驅動*
- 實作 Illuminate\Contracts\Cache\Store 介面。
- 範例：
```php
namespace App\Extensions;

use Illuminate\Contracts\Cache\Store;

class MongoStore implements Store
{
    public function get($key) {}
    public function many(array $keys) {}
    public function put($key, $value, $seconds) {}
    public function putMany(array $values, $seconds) {}
    public function increment($key, $value = 1) {}
    public function decrement($key, $value = 1) {}
    public function forever($key, $value) {}
    public function forget($key) {}
    public function flush() {}
    public function getPrefix() {}
}
```
- 參考 Illuminate\Cache\MemcachedStore 實作細節。

### 10.2 *註冊自訂驅動*
- 在 AppServiceProvider 的 register 方法內註冊：
```php
use App\Extensions\MongoStore; // 引入自訂的 Store 實作（你自己寫的快取邏輯）
use Illuminate\Contracts\Foundation\Application; // 引入 Laravel 應用程式容器型別
use Illuminate\Support\Facades\Cache; // 引入 Cache facade，才能註冊驅動

public function register(): void
{
    $this->app->booting(function () { // 在 booting 階段註冊自訂快取驅動（比 boot 更早，確保所有 provider 都能用）
        Cache::extend('mongo', function (Application $app) { // 註冊一個名為 mongo 的快取驅動，對應 config/cache.php 的 driver 名稱
            return Cache::repository(new MongoStore); // 回傳一個新的快取 repository，底層用自訂的 MongoStore 實作
        });
    });
}
```
- *use ...*：引入自訂驅動、Application 型別、Cache facade。
- *register()*：ServiceProvider 的註冊階段，適合註冊自訂驅動。
- *booting()*：在 boot 之前註冊，避免快取還沒註冊就被其他 provider 用到。
- *extend('mongo', ...)*：註冊一個名為 mongo 的快取驅動，讓 config/cache.php 可以指定 driver => 'mongo'。
- *回傳 Cache::repository(new MongoStore)*：建立一個快取 repository，底層用自訂的 MongoStore。
- 這樣就能在 config/cache.php 設定 driver 為 mongo，或 .env 設定 CACHE_DRIVER=mongo。
- 註冊後，所有 Laravel 快取 API 都能用你的自訂驅動。

### 10.3 *Q&A*
- Q: **自訂驅動可用於所有快取 API 嗎？**
  - A: 只要實作 Store 介面即可。
- Q: **驅動程式放哪裡？**
  - A: 建議 app/Extensions 目錄。

---

## 11. **快取事件（Cache Events）**

### 11.1 *支援事件*
- 可監聽以下事件：
  - Illuminate\Cache\Events\CacheFlushed
  - Illuminate\Cache\Events\CacheFlushing
  - Illuminate\Cache\Events\CacheHit
  - Illuminate\Cache\Events\CacheMissed
  - Illuminate\Cache\Events\ForgettingKey
  - Illuminate\Cache\Events\KeyForgetFailed
  - Illuminate\Cache\Events\KeyForgotten
  - Illuminate\Cache\Events\KeyWriteFailed
  - Illuminate\Cache\Events\KeyWritten
  - Illuminate\Cache\Events\RetrievingKey
  - Illuminate\Cache\Events\RetrievingManyKeys
  - Illuminate\Cache\Events\WritingKey
  - Illuminate\Cache\Events\WritingManyKeys

### 11.2 *監聽範例*
```php
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Support\Facades\Event;

Event::listen(CacheHit::class, function ($event) {
    // 每次快取命中時執行
});
```
- **註解**：
  - 這種寫法是直接在程式碼中註冊事件監聽，適合臨時、簡單用途。
  - 正式專案建議寫 *Listener 類別*（如 app/Listeners/CacheEventLogger.php），並在 **EventServiceProvider** 註冊，這樣更好維護、可複用、可測試。
  - Listener 類別可集中管理多個事件邏輯，EventServiceProvider 的 $listen 屬性可對應事件與 handler 方法。
  - 你目前專案已經是最佳實踐寫法，推薦團隊都用 Listener 類別搭配 ServiceProvider 註冊。

### 11.3 *關閉事件提升效能*
- 可於 `config/cache.php` 指定 store 關閉事件：
```php
'database' => [
    'driver' => 'database',
    // ...
    'events' => false,
],
```

--- 