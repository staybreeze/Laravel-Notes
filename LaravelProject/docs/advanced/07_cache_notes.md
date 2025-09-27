# *Laravel Cache 快取系統 筆記*

---

## 1. **什麼是 Cache？為什麼要用？**

- Cache（快取）是`將耗時或重複查詢的資料暫存起來，加速下次存取`。
- *常見用途*：API 回應、熱門文章、排行榜、設定檔、Session、第三方 API 結果等。
- 快取資料通常存於 __高速儲存__（如 `Redis、Memcached`），__大幅減少資料庫壓力、提升效能__。

---

## 2. **Laravel Cache 設定**

- 設定檔：`config/cache.php`
- 可設定預設「__快取驅動（driver）__」：如 `redis、memcached、database、file、array、null、dynamodb、mongodb`。
- 可針對不同用途`設定多個快取 store`。
- `.env` 需設定 `CACHE_DRIVER、REDIS/MEMCACHED/DYNAMODB/MONGODB` 相關參數。

---

## 3. **各快取驅動安裝與設定**

### 3.1 *Database*

- 需有一個 __快取用的資料表__（**預設** migration: `create_cache_table.php`）。

- 若無此 `migration`，可用指令建立：

  ```bash
  php artisan make:cache-table
  php artisan migrate
  ```

- `config/cache.php` 範例：

  ```php
  'default' => env('CACHE_DRIVER', 'database'),
  ```

---

#### **為什麼 cache 需要專屬 table？**

  - _快取資料結構特殊_：快取是 **key-value 形式**，內容可為*序列化字串、陣列、物件*，和一般業務資料表結構不同。
    - 快取資料不像用戶、訂單那樣有多欄位，通常`只需要一個 key 和一個 value`，value 可以存任何型別（序列化後的字串、陣列、物件等），所以設計上要簡單、彈性。

  - _生命週期短且頻繁變動_：快取資料常**自動過期**、被覆蓋、清空，和一般資料（如用戶、訂單）存取模式不同。
    - 快取資料可能幾秒、幾分鐘就會自動失效或被新資料覆蓋，不像業務資料需要長期保存。

  - _效能最佳化_：快取表**只需查 key、寫入 key-value、刪除 key**，設計上會針對這些操作加索引，避免影響其他表效能。
    - 快取操作很頻繁，查詢和寫入都`只針對 key`，設計上會針對 key 加索引，讓查詢/覆蓋/刪除都很快，不會拖慢資料庫。

  - _安全隔離_：快取資料可隨時 `flush`，若和業務資料混在一起，容易誤刪，__專屬 table 可避免風險__。
    - 快取資料可以 __全部清空__（flush），如果和重要資料混在一起，可能會誤刪到業務資料，所以一定要分開存放。

---

#### **Laravel cache table 標準結構**

```php
// database/migrations/xxxx_xx_xx_xxxxxx_create_cache_table.php
public function up()
{
    Schema::create('cache', function (Blueprint $table) {
        $table->string('key')->unique();      // 快取 key，唯一索引，查詢快
        $table->text('value');                // 存序列化後的資料，型態彈性
        $table->integer('expiration');        // 過期時間（timestamp），方便自動清除
    });
}
```

<!-- Schema 是 Laravel 用來定義和管理「資料庫結構」的工具，  
     可以用程式碼描述資料表欄位、索引、型態等，  
     讓你用 migration 自動建立或修改資料表，  
     而不用手動寫 SQL。 -->

- __簡單扁平__：只存 `key、value、expiration`，_避免複雜欄位_，提升效能。
- __唯一索引__：key 欄位加 `unique`，確保 _查詢/覆蓋_ 快。
- __過期機制__：`expiration` 欄位 _方便定期清除_ 過期快取。

---

### 3.2 *Memcached*

<!-- memcached 全稱是 「 memory cache daemon」，  
     意思是「記憶體快取守護程式」。

     daemon（守護程式）是指在背景執行、長時間運作的服務程式，  
     通常不直接與使用者互動，像 memcached 就是「一直在背景提供快取服務」。 -->

<!-- memcached 是一種高效能的分散式記憶體快取系統，  
     用來暫存資料，減少資料庫查詢次數，加速網站或應用程式效能。  
     Laravel 支援 memcached 作為快取儲存方式，可用來存放 session、查詢結果等。 -->

<!-- 
分散式可以是多臺主機（實體機器）也可以是單一主機上的多個虛擬伺服器，
重點是「資源分散、協同運作」，不侷限於硬體型態。
只要系統設計讓多個節點共同處理任務，就算分散式。 
-->

- 需安裝 `Memcached PECL` 套件（伺服器端也要有 memcached）。
- 設定 servers 於 `config/cache.php`：

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

- 支援 __UNIX socket__：

  ```php
  'host' => '/var/run/memcached/memcached.sock',
  'port' => 0,
  ```

<!-- memcached 和 redis 都是常用的記憶體快取系統，但有些差異：
     - memcached 只支援 key-value 字串快取，功能簡單、速度快，適合純快取用途。
     - redis 除了 key-value，還支援資料結構（如 list、set、hash）、持久化、訂閱/發布等進階功能，彈性更高。
     - redis 適合需要複雜資料操作或持久化的場景，memcached 適合純快取、效能要求高的場景。 -->

<!-- UNIX socket 全稱是 「 UNIX domain socket」，  
     是一種在「同一台主機上」，讓「程式彼此溝通」的特殊檔案（通訊端），  
     比網路 TCP 連線更快、更安全，  
     常用於資料庫、快取等服務的本機溝通。 -->

<!-- socket（中文：通訊端）是一種「程式之間用來傳送資料的介面」，  
     可以讓「不同程式或伺服器之間」建立連線、交換訊息，  
     常用於「網路通訊或本機程式溝通」。 -->

---

### 3.3 *Redis*

- 需安裝 `redis` 伺服器，PHP 端需安裝 `phpredis`（推薦）或 `predis/predis` 套件。
- _Laravel Sail、Forge、Cloud_ **預設** 已安裝 `phpredis`。
- .env 設定：

  ```php
  CACHE_DRIVER=redis           // 設定 Laravel 使用 redis 作為快取系統
  REDIS_HOST=127.0.0.1        // redis 伺服器主機位置（本機）
  REDIS_PASSWORD=null         // redis 連線密碼（預設無密碼）
  REDIS_PORT=6379             // redis 伺服器連接埠（預設 6379）
  ```

- `config/database.php` 設定 redis 連線資訊。

---

### 3.4 *DynamoDB*

- 需建立 `DynamoDB 資料表`（**預設**名稱 `cache`，可自訂）。
- `Partition key` 預設為 `key`（可自訂）。
- 建議啟用 `TTL（Time to Live）`，屬性名稱為 `expires_at`。
- 需安裝 __AWS SDK__：

  ```bash
  composer require aws/aws-sdk-php
  ```

- .env 設定：

  ```php
  AWS_ACCESS_KEY_ID=xxx              // AWS 帳號的 Access Key，用於 API 認證
  AWS_SECRET_ACCESS_KEY=xxx          // AWS 帳號的 Secret Key，用於 API 認證
  AWS_DEFAULT_REGION=ap-northeast-1  // 預設 AWS 區域（如東京）
  DYNAMODB_CACHE_TABLE=cache         // DynamoDB 快取資料表名稱
  ```

- `config/cache.php` 範例：

  ```php
  'dynamodb' => [
      'driver' => 'dynamodb',                          // 使用 DynamoDB 作為快取驅動
      'key' => env('AWS_ACCESS_KEY_ID'),               // AWS Access Key
      'secret' => env('AWS_SECRET_ACCESS_KEY'),        // AWS Secret Key
      'region' => env('AWS_DEFAULT_REGION', 'us-east-1'), // AWS 區域（預設 us-east-1）
      'table' => env('DYNAMODB_CACHE_TABLE', 'cache'), // DynamoDB 資料表名稱
      'endpoint' => env('DYNAMODB_ENDPOINT'),          // 自訂 DynamoDB 端點（可選）
  ],
  ```

---

### 3.5 *MongoDB*

- 需安裝官方套件：

  ```bash
  composer require mongodb/laravel-mongodb
  ```

- 支援 `TTL index`，__自動清除__ 過期快取。
- 參考官方文件設定 __連線與快取 store__。

---

### 3.6 *其他驅動*

- __file__：存於 `storage/framework/cache/data`，適合小型專案。
- __array__：只存在`記憶體`，適合測試。
- __null__：`不存`任何東西，關閉快取用。

---

## 4. **快取常用 API 與實作範例**

### 4.1 *put / get*

```php
// app/Http/Controllers/CacheDemoController.php
// 寫入快取（60 秒）
Cache::put('key', 'value', 60); // 將 key 對應的值設為 value，並設定 60 秒後自動過期

// 讀取快取
$value = Cache::get('key'); // 取得 key 對應的快取值，若不存在回傳 null
```

---

### 4.2 *remember*

```php
// app/Http/Controllers/CacheDemoController.php
// 若無快取則執行 closure 並快取 10 分鐘
$data = Cache::remember('users.all', 600, function() {
    return User::all(); // 查詢所有用戶，並將結果快取 600 秒
}); // 若快取存在直接回傳，否則執行 closure 並快取
```

---

### 4.3 *rememberForever*

```php
// 永久快取（直到手動清除）
Cache::rememberForever('site.settings', function() {
    return Setting::all(); // 查詢所有設定，並永久快取
}); // 若快取存在直接回傳，否則執行 closure 並永久快取
```

---

### 4.4 *forget*

```php
// 刪除快取
Cache::forget('key'); // 刪除 key 對應的快取
```

---

### 4.5 *tags*（僅支援 `redis/memcached`）

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

- **說明**：

  - `tags` 適合 __需要分群管理快取、批次失效__ 的場景（`如分類、群組、作者`等）。
  - 只有 `redis、memcached` _支援_ tags，`file/database/array driver` _不支援_。
  - `flush` __只會清除指定 tag 下的快取__，__不會影響其他 tag 或`全域快取`__。
  - tags 讓快取失效更有彈性，適合中大型專案。

---

### 4.6 *清除所有快取*

```bash
php artisan cache:clear # 清除所有快取（所有 driver 都會被清空）
```

---

## 5. **實作範例**

### 5.1 *熱門文章查詢快取*

```php
// app/Http/Controllers/ArticleController.php
public function hot()
{
    $articles = Cache::remember('hot_articles', 300, function() {
        return Article::orderBy('views', 'desc')->take(10)->get(); // 查詢瀏覽數最多的前 10 篇文章
    }); // 若快取存在直接回傳，否則查詢並快取 300 秒
    return view('articles.hot', compact('articles')); // 回傳熱門文章頁面
}
```

---

### 5.2 *快取 API 回應*

```php
// app/Http/Controllers/ApiController.php
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

- **說明**：

  - '`api.stats'` 是快取 __key__，對應的 __value__ 是一個陣列（裡面有 `users/posts` 欄位）。
  - 這裡的 __點號（.）__ 在快取 key 僅是 *命名慣例* ，方便分群與閱讀，對快取系統來說完全沒有語法意義，__純粹是字串的一部分__。
  - 在 Laravel 其他地方（如 `config 設定、翻譯檔、Eloquent 關聯`）點號有特殊意義（如`分層、關聯`），但在快取 key 這裡完全沒有，只是為了可讀性與分群。
  - 你可以用 __底線、冒號、dash__ 等其他符號命名，只要`全專案唯一、不會衝突`即可。

---

### 5.3 *快取設定檔*

```php
// app/Http/Controllers/SettingController.php
public function getSettings()
{
    $settings = Cache::rememberForever('site.settings', function() {
        return Setting::all(); // 查詢所有設定，並永久快取
    }); // 若快取存在直接回傳，否則查詢並永久快取
    return $settings; // 回傳設定資料
}
```

---

## 6. **常見問題 Q&A**

- Q: *Cache 會自動過期嗎？*
  - A: 會，根據設定的秒數自動失效。`DynamoDB/MongoDB` 需設 `TTL`。

<!-- 
TTL（Time To Live，存活時間）是指資料或快取的有效期限，
超過這個時間，資料就會被自動移除或失效。
常用於快取、DNS、網路封包等場景。 
-->

- Q: *如何清除所有快取？*
  - A: `php artisan cache:clear`

- Q: *可以快取`資料庫查詢`結果嗎？*
  - A: 可以，建議用 `remember` 包住查詢。

- Q: *tags 只能用在 redis/memcached 嗎？*
  - A: 是，`file/database driver` 不支援 tags。

- Q: *如何永久快取？*
  - A: 用 `rememberForever`。

- Q: *`DynamoDB/MongoDB` 如何自動清除過期？*
  - A: 需設 __TTL 屬性__，`DynamoDB` 設 __expires_at__，`MongoDB` 設 __TTL index__。

- Q: *測試時如何關閉快取？*
  - A: 設定 `CACHE_DRIVER=array 或 null`。

---

## 7. **文件與註解建議**

- 重要程式碼區塊皆加上`檔案路徑註解`。
- 團隊文件建議多用 `Q&A、條列、註解`，方便維護與學習。

---

## 8. **Cache 使用方式**（Cache Usage）

### 8.1 *取得快取實例*

- 使用 `Cache facade` 取得快取 `store` 實例，提供統一 API 操作各種快取後端。

  <!-- 這裡的 store 是指「快取的儲存後端」，  
      不是資料庫的 store，  
      是 Laravel 快取系統專屬的概念，  
      用來代表不同的快取方式（如 file、redis、memcached 等）。 -->
      
<!-- store 實例 是指某一種快取後端的物件，  
     例如 Redis、Memcached、File 等，  
     你可以透過它來存取、讀取、刪除快取資料，  
     每種 store 都有自己的操作方式，但都用統一的 API。 -->

- 範例：

```php
use Illuminate\Support\Facades\Cache;

$value = Cache::get('key');
```

---

### 8.2 *存取多個快取 store*

- 可用 `store` 方法 __切換不同__ 快取 store（對應 `config/cache.php` 的 `stores` 設定）。
- 範例：

```php
$value = Cache::store('file')->get('foo');
Cache::store('redis')->put('bar', 'baz', 600); // 10 分鐘
```

- **說明**：

  - `store('file')、store('redis')` 代表 __明確指定__ 要用哪一個快取 store（對應 `config/cache.php` 的 sto`res 設定）。

  - 每個 store __可以用不同的 driver__（`file、redis、database…`），資料互不干擾。
  - Laravel 會 __根據 store 名稱__ 自動找到對應的快取系統（如 `file` 存 __本地檔案__、`redis` 存 __雲端 Redis__）。

  - 這種設計讓你可以靈活分流、分群、分層管理快取資料。
  - 適合有多種快取需求、或需要分開管理不同類型快取的專案。

  - 例如 __session__ 存 `redis`，
        __設定檔__ 快取存 `file`，
        __臨時資料__ 存 `database`。

  - 你可以在 `config/cache.php` 設定多個 store，Laravel 會自動幫你連到正確的快取系統。

---

### 8.3 *取得快取資料*（`get`）

- get 方法可取得快取資料，**若不存在回傳 null**。
- 可傳 __第二參數__ 作為`預設值`，或傳 `closure` 延遲取得預設值。
- 範例：

```php
$value = Cache::get('key');
$value = Cache::get('key', 'default');
$value = Cache::get('key', function () { // 取得 key 這個快取的值，如果不存在就執行後面的 function
    return DB::table('users')->get();   // 這個 function 會查詢 users 資料表的所有資料
}); // $value 會是 key 的快取值，若不存在則是查詢結果
```

---

### 8.4 *判斷快取是否存在*（`has`）

- has 方法可判斷快取`是否`存在，__若值為 null 也會回傳 false__。
- 範例：

```php
if (Cache::has('key')) {
    // ...
}
```

---

### 8.5 *整數快取自增/自減*（`increment/decrement`）

- increment/decrement 可對快取中的`整數值加減`。
- `add` 方法 __可初始化不存在的 key__。
- 範例：

```php
Cache::add('key', 0, now()->addHours(4)); // 如果 key 不存在，將其設為 0，並設定 4 小時後自動過期（只會初始化一次）
Cache::increment('key'); // 將 key 的值加 1
Cache::increment('key', 5); // 將 key 的值加 5
Cache::decrement('key'); // 將 key 的值減 1
Cache::decrement('key', 2); // 將 key 的值減 2
```

---

### 8.6 *取得並快取*（`remember/rememberForever`）

- __remember__：若快取不存在，執行 `closure` 並快取指定秒數。
- __rememberForever__：若快取不存在，執行 `closure` 並永久快取。
- 範例：

```php
$value = Cache::remember('users', 600, function () {
    return DB::table('users')->get();
});

$value = Cache::rememberForever('users', function () {
    return DB::table('users')->get();
});
```

---

### 8.7 *Stale While Revalidate*（`彈性快取`）

- `flexible` 方法可設定 `fresh/stale` 兩階段，
  `fresh` 期間 __直接回傳__，
  `stale` 期間 __回傳舊值並背景刷新__。
  
- 範例：

```php
$value = Cache::flexible('users', [5, 10], function () { // 對 'users' 這個 key 使用 flexible 快取策略
    return DB::table('users')->get(); // 當快取需要重新整理時，執行這個 closure 查詢所有用戶
}); // $value 會根據快取狀態，回傳 fresh、stale 或重新查詢的資料
```

- **說明**：

  - `[5, 10]` 代表 `fresh` 5 秒、`stale` 5 秒（第 6~10 秒），__超過 10 秒完全過期__。
    - _5 秒內（fresh）_：直接回傳 __快取值__。
    - _6~10 秒（stale）_：回傳 __舊快取值，並在背景自動刷新快取__。
    - _超過 10 秒（expired）_：__等 `closure` 查詢完才回傳新資料__。

  - 適合 __查詢成本高、但又希望大部分用戶都能快速拿到資料__ 的場景（如`排行榜、熱門文章、統計數據`等）。
  - _提升用戶體驗、降低資料庫壓力，兼顧`效能`與`資料新鮮度`_。
  - `背景刷新`是 __非同步__，`stale` 期間用戶拿到的是`舊資料`，但很快會被新資料取代。

---

### 8.8 *取得並刪除*（`pull`）

- pull 方法可`取得快取並同時刪除`。
- 範例：

```php
$value = Cache::pull('key'); // 取得 key 這個快取的值，並同時從快取中刪除，如果不存在回傳 null
$value = Cache::pull('key', 'default'); // 取得 key 這個快取的值，並同時刪除，如果不存在則回傳 'default'（預設值）
```

- **說明**：

  - pull 會「`取出並刪除`」快取，常用於 __一次性資料__（如`驗證碼、臨時 token、一次性通知`等）。
  - 第二個參數 `default` 讓你不用再判斷 null，直接給一個預設值，讓程式更安全、簡潔。
  - 這是 Laravel 設計上為了方便開發者、減少 if 判斷的貼心設計。

---

### 8.9 *寫入快取*（`put/add/forever`）

- __put__：寫入快取，可指定`秒數或 DateTime`。
- __add__：僅當 `key 不存在` 時，寫入。
- __forever__：`永久快取`，需手動刪除。

- 範例：

```php
Cache::put('key', 'value', 10); // 10 秒
Cache::put('key', 'value', now()->addMinutes(10));
Cache::add('key', 'value', 60); // 僅當 key 不存在時
Cache::forever('key', 'value');
```

---

### 8.10 *刪除快取*（`forget/flush`）

- __forget__：刪除`單一` key。
- __flush__：清空`所有`快取（不分 `prefix`，請小心使用）。
- 也可用 __put('key', 'value', 0)__ `立即過期`。

- 範例：

```php
Cache::forget('key');
Cache::put('key', 'value', 0);
Cache::flush();
```

---

### 8.11 *Memoization*（`記憶快取`）

- `memo driver` 可於 __`單一請求/任務內`暫存快取值，__ 避免重複查詢。
- 範例：

```php
$value = Cache::memo()->get('key'); // 第一次會查快取，並把結果暫存於記憶體
$value = Cache::memo('redis')->get('key'); // 指定底層使用 redis store，第一次查快取，之後同請求內都用記憶體
```

- 第一次執行時會去 `Redis` 查詢 key 的快取值，並把結果暫存到 PHP 這個請求的 __記憶體__ 裡。
- 之後`同一個 HTTP 請求`或 `queue 任務`內，再查同一個 key，Laravel 會直接 __從 PHP `記憶體`拿值__，不會再查 `Redis`。
- 這樣可以大幅減少對 `Redis` 的查詢，提升效能，特別適合 __同一請求內多次用到同一快取的情境__。
- 請求結束後，__PHP 記憶體會釋放，下次新請求還是會查 Redis__。
- 這種設計能減少快取伺服器壓力，提升高併發下的效能。

`Cache::memo()->put('name', 'Taylor')`; __寫入快取並同步更新記憶體暫存__

- **說明**：

  - `memo driver` 會在同一個請求或任務內，__把查到的快取值暫存於記憶體，之後同一個 key 不會再查快取伺服器__。
  - 這裡的「記憶體」是指 _PHP 伺服器端、單一請求/任務內的暫存_，不是用戶端 RAM，也不是 Redis/Memcached 的記憶體
  - 這樣可以大幅減少重複查詢，提升效能（尤其是 _同一請求內多次用到同一快取時_）。
  - 只在 __單一請求/任務內__ 有效，`跨請求`還是會查快取伺服器。
  - `put、increment` 等會 __自動同步更新記憶體暫存__，確保資料一致。
  - 適合高效能需求、同一請求內多次存取同一快取的場景。


---

### 8.12 *Cache 輔助函式*（`cache helper`）

- `cache()` 可快速 __取得/寫入__ 快取。

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

- **說明**：

  - `cache()` 是 Laravel 提供的全域輔助函式，功能等同於` Cache facade`，但語法更簡潔。
  - 傳入一個 key 字串時，等同於 `Cache::get('key')`。
  - 傳入陣列和過期時間時，等同於 `Cache::put()`。
  - 不帶參數時，回傳 `Cache manager 實例`，可呼叫所有快取 API（如 `remember、put、forget` 等）。
  - 適合在 `blade、controller、service` 等各種地方快速操作快取。

---

### 8.13 *Q&A*

- Q: **flexible 什麼時候適合用？**
  - A: 資料 _查詢成本高、可容忍短暫過期_ 時（如排行榜、熱門文章）。

- Q: **memo driver 會寫入實體快取嗎？**
  - A: 只在 _單一請求/任務內_ 記憶，`跨請求`仍會查詢快取。

  <!-- 意思是 memo driver 只會在目前這次請求或任務的程式記憶快取資料，  
       如果有多次查詢同一 key，會直接回傳記憶的結果，  
       但下一次請求（或任務）還是會重新查詢快取後端，  
       不會把資料真正寫入 Redis、Memcached 等外部快取系統。

       跨請求是指不同的 HTTP 請求或 CLI 任務，  
       每次請求都是獨立的程式執行，  
       memo driver 只在單一請求內記憶快取，  
       下一次請求就不會記得上一次的資料。   -->

- Q: **flush 會清掉所有快取嗎？**
  - A: 是，會清除`所有快取`（不分 prefix），請小心。

--- 

## 9. **原子鎖**（`Atomic Locks`）

### *什麼是分散式鎖（Distributed Lock）？*

- __白話解釋__
  - 分散式鎖就是「_讓多台伺服器、多個程式、多個 worker 在`同一時間只能有一個人`做某件事_」的機制。
  - 就像一間有很多門的倉庫，大家都想進去搬貨，但規定「`同一時間只能有一個人進去`」，所以大家要搶同一把鑰匙，誰拿到誰進去，其他人只能等。

- __技術解釋__
  - 在`單一伺服器`上可以用 _資料庫鎖、檔案鎖_，但 `分散式系統`（多台伺服器、多個 worker）時，這些鎖 _無法跨機器同步_。
  - 分散式鎖就是利用「`大家都能存取的資源`」（如 Redis、Memcached、Database、ZooKeeper 等）來實現「`全世界同時只能有一個人拿到鎖`」的效果。

- __實務舉例__
  1. *排程任務防重複*：多台伺服器都跑 `schedule:run`，只希望 _同一任務同時只執行一次_。
  2. *秒殺/搶購*：多台伺服器`同時下單，庫存只能被扣一次`。
  3. *分散式資源搶佔*：多個 queue worker 處理同一任務池，某些任務只能被一個 worker 處理。

- __常見實作方式__
  - `Redis`（setnx/expire）、
    `Database`（唯一索引/for update）、
    `ZooKeeper`、`Memcached`（add 指令）等。

- __小結__
  - `分散式鎖`的本質是「_大家都搶同一把鑰匙_」，誰搶到誰做事，做完要記得還鑰匙（`釋放鎖`）。
  - 這是 _分散式系統、微服務、雲端架構_ 下防止重複執行、資料衝突的關鍵技術。
  - Laravel 的 `Cache::lock()` 幫你包好這些細節，讓你專心寫業務邏輯。

---

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

- `lock('foo', 10)`：建立一個名為 foo 的鎖，10 秒後自動失效（__避免死鎖__）。
- `get()`：嘗試 __搶鎖__，搶到才會進入 if。
- `release()`：用完一定要 __釋放鎖__，否則其他人會一直搶不到。

---

#### **也可以直接用 closure，執行完自動釋放**

```php
Cache::lock('foo', 10)->get(function () {
    // 這裡的程式碼只有一個 process 能進來
    // 執行完 closure 會自動釋放鎖
});
```

- 這種寫法不用自己 `release`，Laravel 幫你 __自動釋放鎖__。

---

### 9.3 *等待鎖*（`block`）

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

- `block(5)`：代表「__我最多等 5 秒__」來搶鎖，這段期間如果鎖被釋放你就能進來。
- 如果 5 秒都沒搶到鎖，Laravel 會丟 `LockTimeoutException` 例外。
- 適合「__不想馬上放棄、但又不想等太久__」的場景。
- `finally` 裡的 `release()` 是保險，確保你有搶到鎖時會釋放。

---

#### **block 也可以用 closure 寫法**

```php
Cache::lock('foo', 10)->block(5, function () {
    // 只要搶到鎖就會執行這裡，執行完自動釋放鎖
});
```

- 這種 closure 寫法 __不用自己釋放鎖__，Laravel 幫你自動處理。
- 實務上更安全，避免忘記釋放鎖。

- **補充**：

  - block 很像「__廁所門鎖著時你願意等幾分鐘__」，等到就進去，等不到就放棄。
  - 適合排程、API 高併發、需要搶資源但又`不想無限等待`的場景。

---

### 9.4 *跨程序釋放鎖*（`owner token`）

```php
$lock = Cache::lock('processing', 120);
if ($lock->get()) {
    $token = $lock->owner(); // 取得 owner token
    ProcessPodcast::dispatch($podcast, $token); // 把 token 傳給 queue job
}
```

- `owner()`：__取得這把鎖的唯一 token，之後可以交給其他 process 釋放__。

---

__在 queue job 裡釋放鎖__：

```php
Cache::restoreLock('processing', $this->owner)->release();
```

- `restoreLock('processing', $token)`：_用 token 恢復鎖，然後釋放_。

---

#### **強制釋放**（`不檢查 owner`）

```php
Cache::lock('processing')->forceRelease();
```

- 不管 owner 是誰，直接把鎖釋放掉（小心用，避免 `race condition`）。

<!-- race condition（競爭狀況）是指多個程式或執行緒「同時存取、修改同一份資料」時，  
     因為執行順序不確定，可能導致資料錯亂或非預期結果，  
     常見於多工、並行處理時。 -->

---

### 9.5 *Q&A 與補充筆記*

- Q: **什麼情境要用原子鎖？**
  - A: `排程、批次、分散式任務、避免重複執行`（如同一個訂單不能同時被兩個 worker 處理）。

- Q: **`file/array driver` 適合生產環境嗎？**
  - A: 不適合，只能本機測試，生產建議用 `redis/memcached/dynamodb/database`。

- Q: **鎖會不會死鎖？**
  - A: Laravel 的鎖有`自動過期機制`（如 10 秒），即使忘記釋放也不會永遠卡住。

- Q: **一定要 release 嗎？**
  - A: 建議用完就 release，或用 `closure` 讓 Laravel 幫你自動釋放。

- Q: **多台伺服器怎麼同步？**
  - A: 只要連`同一個快取伺服器`（如同一台 Redis），`鎖就能跨伺服器同步`。

- **補充**：

  - _原子鎖_ 是分散式系統常見的「`搶鎖`」機制，能有效避免 `race condition`。
  - 適合 __高併發、分散式、批次、排程__ 等場景，能有效避免重複執行、資料衝突。
  - Laravel 提供簡單 API，讓你不用自己處理 `race condition、死鎖`等複雜問題。
  - 記得用完要 _釋放鎖_，或用 `closure` 讓 Laravel 幫你自動釋放。

---

## 10. **自訂快取驅動**（`Custom Cache Driver`）

### 10.1 *撰寫自訂驅動*

- 實作 `Illuminate\Contracts\Cache\Store` 介面。
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

- 參考 `Illuminate\Cache\MemcachedStore` 實作細節。

---

### 10.2 *註冊自訂驅動*

- 在 `AppServiceProvider` 的 `register` 方法內註冊：
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
- *register()*`：ServiceProvider` 的註冊階段，適合註冊自訂驅動。
- *booting()*：__在 `boot` 之前註冊__，避免快取還沒註冊就被其他 `provider` 用到。
- *extend('mongo', ...)*：註冊一個名為 `mongo` 的快取驅動，讓 `config/cache.php` 可以指定 `driver => 'mongo'`。
- *回傳 Cache::repository(new MongoStore)*：建立一個快取 `repository`，底層用自訂的 `MongoStore`。
- 這樣就能在 `config/cache.php` 設定 driver 為 `mongo`，或 .env 設定 `CACHE_DRIVER=mongo`。
- 註冊後，所有 Laravel 快取 API 都能用你的自訂驅動。

---

### 10.3 *Q&A*

- Q: **自訂驅動可用於所有快取 API 嗎？**
  - A: 只要實作 `Store` 介面即可。

- Q: **驅動程式放哪裡？**
  - A: 建議 `app/Extensions` 目錄。

---

## 11. **快取事件**（`Cache Events`）

### 11.1 *支援事件*

- 可 __監聽__ 以下事件：

```php
- `Illuminate\Cache\Events\CacheFlushed`           // 快取全部清除後觸發
- `Illuminate\Cache\Events\CacheFlushing`          // 快取即將清除時觸發

- `Illuminate\Cache\Events\CacheHit`               // 快取命中（有資料）時觸發
- `Illuminate\Cache\Events\CacheMissed`            // 快取未命中（沒資料）時觸發

- `Illuminate\Cache\Events\ForgettingKey`          // 即將刪除快取 key 時觸發
- `Illuminate\Cache\Events\KeyForgetFailed`        // 刪除快取 key 失敗時觸發
- `Illuminate\Cache\Events\KeyForgotten`           // 成功刪除快取 key 時觸發

- `Illuminate\Cache\Events\KeyWriteFailed`         // 寫入快取 key 失敗時觸發
- `Illuminate\Cache\Events\KeyWritten`             // 成功寫入快取 key 時觸發

- `Illuminate\Cache\Events\RetrievingKey`          // 取得單一快取 key 時觸發
- `Illuminate\Cache\Events\RetrievingManyKeys`     // 取得多個快取 key 時觸發

- `Illuminate\Cache\Events\WritingKey`             // 寫入單一快取 key 時觸發
- `Illuminate\Cache\Events\WritingManyKeys`        // 寫入多個快取 key 時觸發
```

---

### 11.2 *監聽範例*

```php
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Support\Facades\Event;

Event::listen(CacheHit::class, function ($event) {
    // 每次快取命中時執行
});
```

- **說明**：

  - 這種寫法是直接在程式碼中`註冊事件監聽`，適合臨時、簡單用途。
  - 正式專案建議寫 *Listener 類別*（如 `app/Listeners/CacheEventLogger.php`），並在 **EventServiceProvider** 註冊，這樣更好維護、可複用、可測試。
  - Listener 類別可 __集中管理多個事件邏輯__，`EventServiceProvider` 的 `$listen` 屬性可對應事件與 `handler` 方法。
  - 你目前專案已經是最佳實踐寫法，推薦團隊都用 Listener 類別搭配 `ServiceProvider` 註冊。

---

### 11.3 *關閉事件提升效能*

- 可於 `config/cache.php` 指定 `store` __關閉事件__：

```php
'database' => [
    'driver' => 'database',
    // ...
    'events' => false,
],
```

--- 