# *Laravel Eloquent: Getting Started 筆記*

## 1. **簡介**（Introduction)

Eloquent 是 Laravel 內建的 *ORM（物件關聯對映）*，讓你能以`物件方式`愉快地操作資料庫。__每個資料表`對應`一個「Model」__，可用來查詢、插入、更新、刪除資料。

- 使用前，請先於 `config/database.php` 設定 __資料庫連線__。

---

## 2. **產生 Model 類別**（Generating Model Classes)

Model 通常放在 `app/Models` 目錄，並繼承 `Illuminate\Database\Eloquent\Model`。

---

### 2.1 *artisan 指令*

```bash
# 產生 Flight model
php artisan make:model Flight
```

---

- 若要 __同時產生 migration__：

```bash
php artisan make:model Flight --migration
```

---

- 產生 __其他類型 class__（可組合使用）：

```bash
# 產生 model 與 factory
php artisan make:model Flight --factory
php artisan make:model Flight -f
# 產生名為 Flight 的模型，並同時生成對應的 Factory，用於生成假資料

# 產生 model 與 seeder
php artisan make:model Flight --seed
php artisan make:model Flight -s
# 產生名為 Flight 的模型，並同時生成對應的 Seeder，用於填充資料庫

# 產生 model 與 controller
php artisan make:model Flight --controller
php artisan make:model Flight -c
# 產生名為 Flight 的模型，並同時生成對應的 Controller，用於處理 HTTP 請求

# 產生 model、controller(resource)、form request
php artisan make:model Flight --controller --resource --requests
php artisan make:model Flight -crR
# 產生名為 Flight 的模型，並同時生成：
# - Resource 型別的 Controller（包含 RESTful 方法）
# - Form Request 類別，用於驗證表單資料

# 產生 model 與 policy
php artisan make:model Flight --policy
# 產生名為 Flight 的模型，並同時生成對應的 Policy，用於處理授權邏輯

# 產生 model、migration、factory、seeder、controller
php artisan make:model Flight -mfsc
# 產生名為 Flight 的模型，並同時生成：
# - Migration：用於建立資料表
# - Factory：用於生成假資料
# - Seeder：用於填充資料庫
# - Controller：用於處理 HTTP 請求

# 一次產生所有（migration、factory、seeder、policy、controller、form requests）
php artisan make:model Flight --all
php artisan make:model Flight -a
# 產生名為 Flight 的模型，並同時生成：
# - Migration
# - Factory
# - Seeder
# - Policy
# - Controller
# - Form Request

# 產生 pivot model
php artisan make:model Member --pivot
php artisan make:model Member -p
# 產生名為 Member 的模型，並設定為 Pivot 模型（用於多對多關聯的中介表）
```

---

## 3. **檢查 Model 結構**（Inspecting Models)

有時候想`快速檢查` model 的 __屬性與關聯__，可用：

```bash
php artisan model:show Flight
```

---

## 4. **Eloquent Model 慣例**（Conventions)

### 4.1 *Model 範例*

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    // ...
}
```

---

### 4.2 *Table 名稱*

- **預設** 會用「__snake case 複數__」作為 `table 名稱`。

- 例：`Flight` → `flights`，
     `AirTrafficController` → `air_traffic_controllers`

---

- 若需 __自訂__ table 名稱：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    /**
     * 指定對應的資料表名稱。
     *
     * @var string
     */
    protected $table = 'my_flights';
}
```

---

### 4.3 *主鍵*（Primary Keys）

- **預設** 主鍵為 `id` 欄位。

- 若需 __自訂__ 主鍵名稱：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    /**
     * 指定主鍵欄位名稱。
     *
     * @var string
     */
    protected $primaryKey = 'flight_id';
}
```

---

- 若主鍵**非**自動遞增：

```php

class Flight extends Model
{
    /**
     * 是否自動遞增主鍵。
     *
     * @var bool
     */
    public $incrementing = false;
}
```

---

- 若主鍵型別**非 int**：

```php

class Flight extends Model
{
    /**
     * 主鍵型別。
     *
     * @var string
     */
    protected $keyType = 'string';
}
```

---

- Eloquent __不支援複合主鍵__（Composite Primary Keys），但可在資料庫設 __多欄唯一索引__。
- __複合主鍵__（Composite Primary Keys） 是指`一個資料表的主鍵由多個欄位組成，而不是單一欄位`。Laravel 的 Eloquent 不支援複合主鍵，這意味著 __你無法直接在 Eloquent 模型中定義多個欄位作為主鍵__。
  - 適合用於 __多對多關聯__ 的`中介表`（例如 `user_posts 表`）。
  - `主鍵由多個欄位組成`，確保唯一性。

<!-- 主鍵（Primary Key）可以由單一欄位或多個欄位組成。
     如果主鍵是多個欄位，稱為「複合主鍵」（Composite Primary Key）。
     只要這幾個欄位的組合在資料表中是唯一的，就能確保唯一性。
     不是只有單一主鍵才有唯一性，複合主鍵也可以。 -->

```sql
-- 使用複合主鍵
CREATE TABLE role_user (
    user_id BIGINT UNSIGNED,
    role_id BIGINT UNSIGNED,
    PRIMARY KEY (user_id, role_id), -- 設定複合主鍵
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

```sql
-- 使用多欄唯一索引
CREATE TABLE role_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- 單一主鍵
    user_id BIGINT UNSIGNED,
    role_id BIGINT UNSIGNED,
    UNIQUE KEY (user_id, role_id), -- 設定多欄唯一索引
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```
---

## 5. **UUID 與 ULID 主鍵**

- *UUID*（Universally Unique Identifier）
  - 是一種 `128-bit` 的唯一識別碼。
  - 通常以 `32 個十六進位`字元表示，並分成 `5個部分`（用 - 分隔）。
  - 格式範例：123e4567-e89b-12d3-a456-426614174000

  - __用途__
    - 用於`分散式系統`中，確保不同系統生成的`識別碼不會重複`。
    - 適合用於`資料表的主鍵`，*避免使用 `自動遞增的整數主鍵`*。

  - __優勢__
    - 全球`唯一性`，適合分散式系統。
    - `不依賴`資料庫的`自動遞增機制`。

  - __缺點__
    - 相比*整數主鍵*，UUID 的存儲和索引`性能較差`。

---

- *ULID*（Universally Unique Lexicographically Sortable Identifier）
  - 是一種 `128-bit` 的唯一識別碼，類似 UUID，但具有 __可排序性__。
  - 格式範例：01H5A7X4Y8KZ9PQR2TUVWX3YZF

  - __用途__
    - 適合需要`唯一性`且需`要按時間排序`的場景，例如`日誌記錄`或`事件追蹤`。

  - __優勢__
    - *可排序性*：ULID 的 __前__ 48-bit 是基於`時間戳`生成的，__後__ 80-bit 是`隨機數`。
    - *更易讀*：相比 UUID，ULID 的格式更簡潔。

  - __缺點__
    - 與 UUID 一樣，存儲和索引 *性能不如 `整數主鍵`*。

---

- 使用 `UUID` 作為主鍵：

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    // Trait 必須放在 引用類別的內部，通常放在類別的 第一行 或 靠近類別屬性定義的地方。
    use HasUuids; // 使用 HasUuids Trait，自動生成 UUID 作為主鍵
    // ...
}

$article = Article::create(['title' => 'Traveling to Europe']);
$article->id; // "8f8e8478-9035-4d23-b9a7-62f4d2612ce5"
```

---

- __自訂__ `UUID` 產生方式與欄位：

```php
use Ramsey\Uuid\Uuid;

/**
 * 產生新的 UUID。
 */
public function newUniqueId(): string
{
    return (string) Uuid::uuid4();
}

// 使用自訂方法
class Discount extends Model
{
    /**
     * 指定哪些欄位要自動產生唯一識別碼。
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id', 'discount_code']; // 自訂欄位需要生成唯一識別碼
    }

    /**
     * 為指定欄位生成唯一識別碼。
     */
    public function generateUniqueIds()
    {
        foreach ($this->uniqueIds() as $field) {
            if (empty($this->{$field})) { // 如果欄位尚未有值
                $this->{$field} = Str::uuid(); // 使用 UUID 生成唯一識別碼
            }
        }
    }
}

// 使用模型事件
class Discount extends Model
{
    /**
     * 指定哪些欄位要自動產生唯一識別碼。
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id', 'discount_code']; // 自訂欄位需要生成唯一識別碼
    }

    /**
     * 模型的靜態事件。
     */
    protected static function boot()
    {
        parent::boot();

        // 在模型創建時生成唯一識別碼
        static::creating(function ($model) {
            foreach ($model->uniqueIds() as $field) {
                if (empty($model->{$field})) { // 如果欄位尚未有值
                    $model->{$field} = Str::uuid(); // 使用 UUID 生成唯一識別碼
                }
            }
        });
    }
}
```

---

- 使用 `ULID` 作為主鍵：

```php
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasUlids;
    // ...
}

$article = Article::create(['title' => 'Traveling to Asia']);
$article->id; // "01gd4d3tgrrfqeda94gdbtdk5c"
```

---

## 6. **時間戳記**（Timestamps)

- *預設* 會自動維護 `created_at`、`updated_at` 欄位。

- 若*不需自動維護*：
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    /**
     * 是否自動維護時間戳記。
     *
     * @var bool
     */
    public $timestamps = false;
}
```

---

- *自訂時間格式*：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    /**
     * 時間欄位儲存格式。
     *
     * @var string
     */
    protected $dateFormat = 'U'; // 使用 Unix 時間戳格式
    // U 表示 Unix 時間戳（從 1970 年 1 月 1 日 00:00:00 UTC 開始的秒數）
    // 時間以秒數表示，例如：
    // 2023-01-01 00:00:00 → 1672531200
    // 2023-01-01 12:00:00 → 1672574400
    // 當從資料庫讀取時間欄位時，Laravel 會自動將 Unix 時間戳轉換為 PHP 的 Carbon 時間物件。
}
```

---

- *自訂欄位名稱*：

```php

class Flight extends Model
{
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';
}
```

---

- *不要更新 updated_at*：

```php
$post = Post::find(1);

// 將 reads 欄位加 1，但不更新 updated_at
Model::withoutTimestamps(fn () => $post->increment('reads'));

// withoutTimestamps() 的作用：
// 暫時停用模型的 timestamps 功能。
// 使用 withoutTimestamps() 時，created_at 和 updated_at 都不會被自動設定或更新。

// created_at：
// 通常只在資料插入（create 或 save）時自動設定。
// 如果在 create() 或 save() 操作中使用 withoutTimestamps()，created_at 也不會被設定。

// updated_at：
// 每次資料更新（例如 update() 或 increment()）時，Laravel 會自動更新 updated_at。
// 使用 withoutTimestamps() 可以暫時停用 updated_at 的自動更新。
```

---

## 7. **指定資料庫連線**（Database Connections)

- *預設連線*，可自訂：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    /**
     * 指定使用的資料庫連線。
     *
     * @var string
     */
    protected $connection = 'pgsql'; // 使用 PostgreSQL 連線
}
```
```php
// config/database.php
return [
    'default' => env('DB_CONNECTION', 'mysql'), // 預設連線名稱
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            // ...
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel_pgsql'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            // ...
        ],
    ],
];
```
---

## 8. **Model 屬性預設值**（Default Attribute Values)

- 可設定 `$attributes` 屬性，指定欄位 *預設值*：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    /**
     * Model 屬性預設值。
     *
     * @var array
     */
    protected $attributes = [
        'options' => '[]',
        'delayed' => false,
    ];
}
```

---

## 9. **Eloquent 嚴謹模式設定**（Configuring Eloquent Strictness)

- 可設定 *防止* `lazy loading`（僅於 __非 production__）：

```php
use Illuminate\Database\Eloquent\Model;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    // 使用 Model::preventLazyLoading() 方法來防止模型的 Lazy Loading（延遲載入）
    // 如果應用程式不是在正式環境（Production），則啟用防止 Lazy Loading 的功能
    Model::preventLazyLoading(! $this->app->isProduction());
    // Lazy Loading 是指「 在使用模型時，未明確定義關聯的資料會在需要時自動載入。」
    // 防止 Lazy Loading 可以幫助開發者在開發或測試環境中，更快地發現性能問題或潛在的錯誤。
    
}
```
- *Lazy Loading*
  - 當模型的關聯資料`未被明確載入`時，Laravel 會在`需要時`自動查詢資料庫並載入關聯。
  - 雖然方便，但可能導致隱藏的性能問題（例如 `N+1` 查詢問題）。

<!-- 當你查詢一個主模型（例如 1 次查詢拿到 10 個 User），然後每個 User 都要查一次關聯（例如 Profile），
     這樣就會多出 10 次查詢（共 1+10 次）。
     這是因為 Lazy Loading 會在每次存取關聯時才查資料庫，導致查詢次數暴增，影響效能。 -->

- *防止 Lazy Loading 的好處*：
  - 在`開發`或`測試環境`中， __強制開發者`明確定義關聯資料的載入方式`__（例如使用 `with()` 或 `load()`）。
  - 幫助開發者更快地發現潛在的性能問題。

- **防止** 未宣告 `fillable` 屬性時，__靜默丟棄__：
  - 當你使用 **批量賦值**（例如 `create()` 或 `update()`）時，如果模型的 `fillable` 屬性 *未定義* 或 *未包含某些欄位* ，Laravel 會 *悄悄地忽略（丟棄／不處理／不插入／不更新）* 這些欄位 ，而 __不會拋出錯誤或警告__。

```php
Model::preventSilentlyDiscardingAttributes(true); // 啟用防止靜默丟棄
Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
// 使用 Model::preventSilentlyDiscardingAttributes() 方法
// 防止模型在未定義 fillable 屬性時，靜默丟棄未授權的欄位
// 如果應用程式不是在正式環境（Production），則啟用此功能
```

---

## 10. **檢索與查詢模型**（Retrieving Models & Building Queries)

### 10.1 *Retrieving Models*

- 建立好 Model 與資料表後，可以開始從資料庫撈取資料。
- 每個 `Eloquent model` 都像一個強大的查詢產生器，可以流暢地查詢對應的資料表。

```php
use App\Models\Flight;

foreach (Flight::all() as $flight) {
    echo $flight->name;
}
```
- 取得所有 `Flight` 資料表的紀錄，並逐筆輸出 `name`。

---

### 10.2 *Building Queries*

`all` 方法會撈出所有資料，但你可以像 `Query Builder` 一樣加上條件、排序、限制等，最後用 `get()` 取得結果：

- **終止方法**

```php
        get()               獲取`結果集`（所有記錄）。
        all()	            獲取資料表的`所有記錄`。
        first()	            獲取`第一筆`記錄。
        find()	            根據主鍵`查詢`單筆記錄。
        pluck()	            獲取`指定欄位的值`。
        count()	            `計算`記錄數量。
        exists()	        判斷`是否`有符合條件的記錄。
        doesntExist()	    判斷`是否`沒有符合條件的記錄。
        paginate()	        返回`分頁結果`。
        simplePaginate()	返回`簡單分頁結果`。
        toSql()	            返回 `SQL 字串`（不執行查詢）。
        delete()	        `刪除`符合條件的記錄。
        update()	        `更新`符合條件的記錄。
```

```php
$flights = Flight::where('active', 1)
         ->orderBy('name')
         ->limit(10)
         ->get();
         // 查詢 active=1 的航班，依 name 排序，取前 10 筆。
```

- `Eloquent model` 本身就是 `query builder`，可以用 **所有 query builder 的方法** 。

---

### 10.3 *Refreshing Models*

已取得的 model 實例，可以用 `fresh` 或 `refresh` **重新** 從資料庫 **取得最新資料**。

- `fresh()`：**重新查詢**，回傳新實例，原本的 model 不變。（_新_）
- `refresh()`：直接 **更新原本** 的 model 實例（連同關聯）。（_舅_）

<!-- 如果你後續還要用原本物件，或有 reference，這差異就會影響程式行為。
     一般來說，refresh() 用於更新現有物件，fresh() 用於取得最新資料的新物件。 -->

---

```php
$flight = Flight::where('number', 'FR 900')->first();

// fresh()：回傳一個新物件，原本物件不會被改動。
$freshFlight = $flight->fresh();
```
- 取得最新資料，`$flight` 不變，`$freshFlight` 為**新物件**。

---

```php
$flight = Flight::where('number', 'FR 900')->first();

$flight->number = 'FR 456';

$flight->refresh();

// refresh()：直接更新原本物件的屬性，物件本身不變。
$flight->number; // "FR 900"
```
- 直接將 `$flight` 物件的資料重設為資料庫**最新狀態**。

---

### 10.4 *Collections*

Eloquent 的 `all`、`get` 會回傳 `Illuminate\Database\Eloquent\Collection`，**不是純陣列**。

- `Eloquent Collection` 繼承自 Laravel 的 `Illuminate\Support\Collection`，有許多方便的操作方法。


```php
$flights = Flight::where('destination', 'Paris')->get();

$flights = $flights->reject(function (Flight $flight) {
    return $flight->cancelled;
});
```
- 用 `reject` 過濾掉 `cancelled` 的航班。

<!-- filter：保留 callback 回傳 true 的項目。 -->
<!-- reject：移除 callback 回傳 true 的項目。 -->

- 你可以像陣列一樣 foreach 迴圈：

```php
foreach ($flights as $flight) {
    echo $flight->name;
}
```

---

- Laravel 的 **集合（Collection）** 是`基於 PHP 的物件導向設計`，但它實現了以下介面：

  - *ArrayAccess*：
    - 允許集合像陣列一樣`使用索引來存取元素`。
    - 範例：
    ```php
    $flights = collect([
        ['name' => 'Flight 1', 'cancelled' => false],
        ['name' => 'Flight 2', 'cancelled' => true],
    ]);

    echo $flights[0]['name']; // 輸出：Flight 1
    ```
<!-- Laravel 的集合（Collection）本質上是物件，但它實作了 ArrayAccess 介面，
     讓你可以像操作陣列一樣用 $collection[$key] 存取元素。

     這是 PHP 的設計：
     只要物件實作 ArrayAccess，就能用陣列語法操作物件。
     所以集合同時有物件方法（如 filter()、map()），也能用陣列索引，兩者可以並存。 -->

---

  - *Iterator*( 迭代器 )：
    - 允許集合像陣列一樣`使用 foreach 迴圈`進行遍歷。
    - 範例：
    ```php
    foreach ($flights as $flight) {
        echo $flight['name'];
    }
    ```

---

### 10.5 *Chunking Results*

若一次撈大量資料，建議用 `chunk` 方法**分批處理**，避免記憶體爆掉。


```php
use App\Models\Flight;
use Illuminate\Database\Eloquent\Collection;
// 每次取 200 筆，分批處理。
Flight::chunk(200, function (Collection $flights) {
    foreach ($flights as $flight) {
        // ...
    }
});
```

---

- 若你根據**某欄位過濾**且**同時會更新該欄位**，請用 `chunkById`，__避免資料不一致__：

```php
Flight::where('departed', true) // 查詢 departed 欄位為 true 的航班
      ->chunkById(200, function (Collection $flights) { // 使用 chunkById 方法，每次分批處理 200 筆資料
          $flights->each->update(['departed' => false]); // 將每筆資料的 departed 欄位更新為 false
     }, column: 'id'); // 指定分批的欄位為 id，確保分批處理的順序正確
```

---

- 若 `chunkById/lazyById` 會自動加 `where` 條件，建議用 `closure` 包住自己的條件：

```php
Flight::where(function ($query) { // 使用 where 條件篩選航班
    $query->where('delayed', true) // 篩選延遲的航班
          ->orWhere('cancelled', true); // 或篩選已取消的航班
})->chunkById(200, function (Collection $flights) { // 使用 chunkById 方法分批處理，每次處理 200 筆資料
    $flights->each->update([ // 更新每筆資料
        'departed' => false, // 將 departed 欄位更新為 false，表示航班未出發
        'cancelled' => true // 將 cancelled 欄位更新為 true，表示航班已取消
    ]);
}, column: 'id'); // 指定分批的欄位為 id，確保分批處理的順序正確
```

---

### 10.6 *Chunking Using Lazy Collections*

`lazy` 方法類似 `chunk`，但回傳 `LazyCollection`，可以**像串流一樣逐筆處理**。


```php
use App\Models\Flight;

foreach (Flight::lazy() as $flight) {
    // ...
}
```

---

- 若**同時過濾與更新欄位**，請用 `lazyById`：

```php
Flight::where('departed', true)
    ->lazyById(200, column: 'id')
    ->each->update(['departed' => false]);
```

- 也可用 `lazyByIdDesc` **依 id 反向排序**。

---

### 10.7 *Cursors*

`cursor` 方法也 __能大幅降低記憶體用量__，適合大量資料**逐筆處理**。

- cursor **只會執行一次查詢，但每次只載入一筆 model**，記憶體佔用極低，並返回 `LazyCollection`。
- **不能** `eager load` 關聯，**若需關聯**建議用 `lazy()`。

<!-- 用 cursor() 取資料時，無法一次載入關聯資料（例如 with('profile')），因為 cursor 是逐筆查詢、逐筆產生 model。 -->
<!-- 如果你需要同時載入關聯資料（例如每個 User 都要有 Profile），建議用 lazy()，它支援 eager load（可以用 with() 一次載入所有關聯）。 -->

```php
// 取得所有 User，並一次載入 profile 關聯，逐筆處理
User::with('profile')->lazy()->each(function ($user) {
    // $user 已經有 profile 關聯資料
    echo $user->profile->bio;
});

// 這方法很適合大量資料且需要關聯資料時使用，
// 因為 lazy() 可以搭配 with() 一次載入關聯，
// 又能逐筆處理、降低記憶體用量，比直接全部載入更有效率。

// 但要注意：
// 如果關聯資料非常多，with() 還是會一次查詢全部關聯，
// 資料量極大時，記憶體還是可能吃緊。
// 一般情境下，這是很好的做法。

// lazy()->each() 或 foreach 會逐筆處理資料，每次只載入一筆 model，處理完就釋放記憶體。
// 這樣即使資料量很大，也不會一次佔用大量記憶體，非常適合大量資料逐筆運算。

// 不管有沒有 lazy()，在 foreach 階段都是一筆一筆處理資料。

// 但差異在於：

// 沒有 lazy() 時，with() 會一次把所有主資料和關聯資料載入記憶體，然後再逐筆處理。資料量大時，記憶體用量很高。
// 有 lazy() 時，主資料是逐筆產生、逐筆釋放，但關聯資料還是一次載入。
// 所以 foreach 處理方式一樣，但資料載入到記憶體的方式不同，這才是記憶體用量的關鍵。

// 有 lazy() 時：每筆主資料處理完就釋放記憶體，記憶體用量低。
// 沒有 lazy() 時：所有主資料和關聯資料一次載入，foreach 處理時記憶體會一直累積，不會釋放，資料量大時容易爆記憶體。
// 差異就在於資料載入與釋放的時機。
```

```php
use App\Models\Flight;

foreach (Flight::where('destination', 'Zurich')->cursor() as $flight) {
    // ...
}
```

---

- cursor 回傳 `LazyCollection`，可用 collection 方法處理：

```php
use App\Models\User;

$users = User::cursor()->filter(function (User $user) {
    // User::cursor() 是 Laravel 提供的一種方法，用於以 LazyCollection 的形式逐筆處理資料庫中的記錄。
    return $user->id > 500;
});

foreach ($users as $user) {
    echo $user->id;
}
```

- **注意**：cursor 仍會因 `PDO buffer` 耗盡記憶體，極大量資料建議用 `lazy`。

<!-- cursor() 會用 PDO 的「unbuffered query」模式，一筆一筆從資料庫取資料，但PDO 仍會有 buffer 限制，
     如果資料量極大，PDO 可能還是會把資料暫存到記憶體，導致耗盡。 -->

<!-- cursor() 會一筆一筆處理資料，原則上能逐筆釋放記憶體，但底層的 PDO 連線會維持資料流，
     如果資料量極大，PDO 可能會把資料暫存到記憶體，導致記憶體耗盡，
     所以不是每筆都能即時釋放，這是 PDO buffer 的限制。 
     因此，極大量資料時建議用 lazy() 分批查詢，記憶體管理更安全。 -->

<!-- lazy() 則是 Laravel 內部用「分批查詢」（例如每次查 1000 筆），每批處理完就釋放記憶體，
     不會一次把所有資料留在 PDO buffer 裡，所以更適合極大量資料。 -->


---

### 10.8 *Advanced Subqueries*

#### 10.8.1 **Subquery Selects**

可用 `subquery select` 直接查詢 *關聯表* 資訊。

```php
use App\Models\Destination;
use App\Models\Flight;

// 查詢每個 destination，並帶出最新抵達的 flight name。
return Destination::addSelect([
        'last_flight' => Flight::select('name') // 子查詢：選擇 flight 表中的 name 欄位
                               ->whereColumn('destination_id', 'destinations.id') 
                               // 條件：flight 的 destination_id 必須匹配 destinations 表的 id
                               ->orderByDesc('arrived_at') 
                               // 按抵達時間（arrived_at）降序排列，取最新的航班
                               ->limit(1) // 只取最新的一筆記錄
])->get(); // 執行查詢，返回所有 destinations 資料，並附加子查詢的結果
// 這段程式碼的執行結果會返回每個 Destination，並附加一個名為 last_flight 的欄位，其值是最新抵達的航班名稱。

// 使用 addSelect：
// 如果只需要額外的欄位（例如最新航班的名稱），且不需要完整的關聯資料。

// 使用 join：
// 如果需要結合多個資料表，或需要高效處理大量資料。
Destination::join('flights', 'flights.destination_id', '=', 'destinations.id')
           ->select('destinations.*', 'flights.name as last_flight')
           ->orderBy('flights.arrived_at', 'desc')
           ->groupBy('destinations.id')
           ->get();
```

---

#### 10.8.2 **Subquery Ordering**

`orderBy` 也支援 `subquery`，可依 *關聯表* 欄位排序：

```php
return Destination::orderByDesc( // 對 Destination 資料表進行降序排列
        Flight::select('arrived_at') // 子查詢：選擇 Flight 資料表中的 arrived_at 欄位
                ->whereColumn('destination_id', 'destinations.id') 
                // 條件：Flight 資料表的 destination_id 必須匹配 Destination 資料表的 id
                ->orderByDesc('arrived_at') 
                // 按抵達時間（arrived_at）降序排列，取最新的航班抵達時間
                ->limit(1) // 只取最新的一筆抵達時間
)->get(); // 執行查詢，返回所有 Destination 資料，並根據子查詢的結果進行排序
// 即使子查詢已經降序排列，主查詢仍需要明確指定排序邏輯，否則主查詢的結果可能不會按子查詢的結果排序。
```

---

## 11. **單筆檢索、聚合與新增/更新模型**（Retrieving Single Models, Aggregates, Inserts & Updates）

### 11.1 *Retrieving Single Models*

可用 `find`、`first`、`firstWhere` 取得**單一模型實例**，不會回傳 collection，而是單一 model：

```php
use App\Models\Flight;

// 依主鍵查詢
$flight = Flight::find(1);

// 查詢第一筆符合條件的紀錄
$flight = Flight::where('active', 1)->first();

// firstWhere 語法糖
$flight = Flight::firstWhere('active', 1);
```

---

- 若查無資料，可用 `findOr`、`firstOr` 傳入 `closure`，回傳 closure 結果：

```php
$flight = Flight::findOr(1, function () {
    // ...
});

$flight = Flight::where('legs', '>', 3)->firstOr(function () {
    // ...
});
```

---

### 11.2 *Not Found Exceptions*

- `findOrFail`、`firstOrFail` **查無資料**時，會丟出 `ModelNotFoundException`，常用於 **route/controller**。

```php
$flight = Flight::findOrFail(1);

$flight = Flight::where('legs', '>', 3)->firstOrFail();
```

---

- __未捕捉例外__ 的意思是，`程式執行時發生了例外（Exception），但你沒有在程式中使用 try-catch 或其他方式來處理（捕捉）這個例外`。

- 若未捕捉例外，Laravel 會根據例外的類型回傳適當的 HTTP 狀態碼，如 **404**。

`ModelNotFoundException`：回傳 `404` Not Found。
`AuthorizationException`：回傳 `403` Forbidden。
`ValidationException`：回傳 `422` Unprocessable Entity。
__其他未處理的例外__：通常回傳 `500` Internal Server Error。

```php
// 未捕捉例外
use App\Models\Flight;

Route::get('/api/flights/{id}', function (string $id) {
    return Flight::findOrFail($id);
});
```

---


```php
// 捕捉例外
use Illuminate\Database\Eloquent\ModelNotFoundException;

Route::get('/api/flights/{id}', function ($id) {
    try {
        return Flight::findOrFail($id); // 嘗試查詢
    } catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Flight not found'], 404); // 自訂回應
    }
});
```
---

### 11.3 *Retrieving or Creating Models*

- `firstOrCreate`：查無資料時，**自動新增，並回傳 model**。
- `firstOrNew`：查無資料時，**回傳新 model 實例**（未存入 DB，`需手動 save）`。


```php
use App\Models\Flight;

// 查詢 name，查無則新增
$flight = Flight::firstOrCreate([
    'name' => 'London to Paris'
]);

// 查詢 name，查無則新增並帶入更多欄位
$flight = Flight::firstOrCreate(
    ['name' => 'London to Paris'],
    ['delayed' => 1, 'arrival_time' => '11:30']
);

// 查詢 name，查無則回傳新 model 實例
$flight = Flight::firstOrNew([
    'name' => 'London to Paris'
]);

// 查詢 name，查無則回傳新 model 實例並帶入更多欄位
$flight = Flight::firstOrNew(
    ['name' => 'Tokyo to Sydney'],
    ['delayed' => 1, 'arrival_time' => '11:30']
);
```

---

### 11.4 *Retrieving Aggregates*

可用 `count`、`sum`、`max` 等聚合方法，回傳**純量值**：

```php
$count = Flight::where('active', 1)->count();

$max = Flight::where('active', 1)->max('price');
```

---

### 11.5 *Inserts*

- **新增資料**：建立 model 實例、設定屬性、呼叫 `save()`。


```php
namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    /**
     * Store a new flight in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the request...

        $flight = new Flight;

        $flight->name = $request->name;

        $flight->save();
        // 新增時 created_at、updated_at 會自動設定。
        return redirect('/flights');
    }
}
```

---

- 也可用 `create` **批量新增**（需先設定 `fillable/guarded`）：

```php
use App\Models\Flight;

$flight = Flight::create([
    'name' => 'London to Paris',
]);
```

- **create()**
  - create 是 Eloquent 提供的 *`靜態`方法*，用於`直接插入一筆新的資料到資料庫`。
  - 它需要模型的 `fillable` __屬性正確定義，並且會自動保存資料到資料庫__。
    ```php
    use App\Models\User;

    $user = User::create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => bcrypt('password'),
    ]);
    ```

---

- **save()**
  - Laravel Eloquent 模型的 *`動態`方法*（Instance Method）。它是 __模型實例上的方法__，用於`保存模型的資料到資料庫`。
  - 它可以用於 _插入新資料_ 或 _更新現有資料_。
    ```php
    use App\Models\User;

    $user = new User(); // 創建模型實例
    $user->name = 'Alice';
    $user->email = 'alice@example.com';
    $user->password = bcrypt('password');
    $user->save(); // 保存到資料庫
    ```

---

### 11.6 *Updates*

- **先查詢、再設定**屬性、呼叫 `save()` 更新。


```php
use App\Models\Flight;

$flight = Flight::find(1);

$flight->name = 'Paris to London';

$flight->save();
```

```php
// 動態方法單一更新範例：直接用模型實例的 update 方法
use App\Models\User;

$user = User::find(5); // 查詢 id 為 5 的使用者
$user->update([
    'email' => 'new@example.com', // 更新 email 屬性
    'name' => 'New Name',         // 更新 name 屬性
]); // 儲存更新到資料庫
```

---

- `updateOrCreate`：__有則更新，無則新增__。

```php
$flight = Flight::updateOrCreate(
    ['departure' => 'Oakland', 'destination' => 'San Diego'],
    ['price' => 99, 'discounted' => 1]
);
```

---

### 11.7 *Mass Updates*

- update() 用於`批次更新資料，並回傳受影響的資料筆數`。
- **不觸發模型事件**：批次更新不會觸發模型的事件，適合`高效處理`。
- **使用場景**：`一次性更新多筆資料`，例如修改狀態或標記。

```php
Flight::where('active', 1) // 篩選條件：只更新 active 欄位為 1 的航班
      ->where('destination', 'San Diego') // 篩選條件：只更新目的地為 San Diego 的航班
      ->update(['delayed' => 1]); // 將符合條件的航班的 delayed 欄位更新為 1
      // update 方法會直接執行批次更新，並回傳受影響的資料筆數
      // 如果有符合條件的資料被更新，回傳值是更新的筆數（例如 2）。
      // 如果沒有符合條件的資料，回傳值是 0。
```

---

### 11.8 *Examining Attribute Changes*

- 用 `isDirty`、`isClean`、`wasChanged` **檢查 model 屬性有無異動**。

```php
use App\Models\User;

// 使用 create 方法建立一個新的 User 實例並保存到資料庫
$user = User::create([
    'first_name' => 'Taylor', // 名字
    'last_name' => 'Otwell',  // 姓氏
    'title' => 'Developer',   // 職稱
]);

// 修改 title 屬性，將其值改為 'Painter'
$user->title = 'Painter';

// 檢查模型是否有未保存的變更
$user->isDirty(); // true，模型有未保存的變更（title 屬性已被修改）
$user->isDirty('title'); // true，title 屬性已被修改
$user->isDirty('first_name'); // false，first_name 屬性未被修改
$user->isDirty(['first_name', 'title']); // true，至少有一個屬性（title）被修改

// 檢查模型是否沒有未保存的變更
$user->isClean(); // false，模型有未保存的變更
$user->isClean('title'); // false，title 屬性有未保存的變更
$user->isClean('first_name'); // true，first_name 屬性未被修改
$user->isClean(['first_name', 'title']); // false，至少有一個屬性（title）被修改

// 保存模型的變更到資料庫
$user->save();

// 檢查模型是否有未保存的變更
$user->isDirty(); // false，模型的所有變更已保存到資料庫
$user->isClean(); // true，模型沒有未保存的變更
```

---

- `wasChanged` **檢查上次 save 時有無異動**：

```php
$user = User::create([
    'first_name' => 'Taylor', // 名字
    'last_name' => 'Otwell',  // 姓氏
    'title' => 'Developer',   // 職稱
]);

// 修改 title 屬性，將其值改為 'Painter'
$user->title = 'Painter';

// 保存模型的變更到資料庫
$user->save();

// 檢查模型的屬性是否在保存後發生了變更
$user->wasChanged(); // true，模型的某些屬性在保存後發生了變更
$user->wasChanged('title'); // true，title 屬性在保存後發生了變更
$user->wasChanged(['title', 'slug']); // true，至少有一個屬性（title）在保存後發生了變更
$user->wasChanged('first_name'); // false，first_name 屬性在保存後未發生變更
$user->wasChanged(['first_name', 'title']); // true，至少有一個屬性（title）在保存後發生了變更
```

---

- `getOriginal` 取得**原始屬性值**。
- `getChanges` 取得**異動欄位**。
- `getPrevious` 取得**上次 save 前的值**。

```php
$user = User::find(1); // 查詢主鍵為 1 的使用者

$user->name; // John，從資料庫中獲取 name 屬性的值
$user->email; // john@example.com，從資料庫中獲取 email 屬性的值

$user->name = 'Jack'; // 修改 name 屬性，將其值改為 'Jack'
$user->name; // Jack，現在 name 屬性的值是 'Jack'

$user->getOriginal('name'); // John，獲取 name 屬性在資料庫中的原始值
$user->getOriginal(); // 返回所有屬性的原始值（陣列）

$user->update([
    'name' => 'Jack', // 更新 name 屬性為 'Jack'
    'email' => 'jack@example.com', // 更新 email 屬性為 'jack@example.com'
]);

$user->getChanges();
/*
    返回模型的變更（已保存到資料庫的屬性），格式為陣列：
    [
        'name' => 'Jack',
        'email' => 'jack@example.com',
    ]
*/

$user->getPrevious();
/*
    返回模型的原始屬性（保存前的屬性值），格式為陣列：
    [
        'name' => 'John',
        'email' => 'john@example.com',
    ]
*/
```

---

### 11.9 *Mass Assignment*

- 使用 `create`、`fill`、`update` 等方法時，必須設定 `$fillable` 或 `$guarded`，以防止 **Mass Assignment 漏洞**。
- `fill` 批量**賦值**模型屬性，但**不保存**到資料庫。
- `update` 批量**更新**模型屬性，並**保存**到資料庫。
- `create`	批量**賦值**模型屬性，並**保存**到資料庫。

```php
// fill()
// 批量賦值屬性（基於 fillable 限制）。
// 不會自動保存到資料庫，必須手動執行 save()。
// 回傳值是 模型實例本身。
$flight = new Flight();
$flight->fill([
    'name' => 'Amsterdam to Frankfurt',
    'status' => 'Delayed',
    'departure_time' => '2023-01-01 12:00:00',
]);
$flight->save(); // 必須手動保存到資料庫
```

```php
// update()
// 批量更新屬性（基於 fillable 限制）。
// 自動保存到資料庫。
// 回傳值是 受影響的資料筆數。
$flight = Flight::find(1); // 查詢主鍵為 1 的航班
$flight->update([
    'name' => 'Amsterdam to Frankfurt',
    'status' => 'Delayed',
    'departure_time' => '2023-01-01 12:00:00',
]);
```

```php
// create()
// 批量賦值屬性（基於 fillable 限制）。
// 自動保存到資料庫。
// 回傳值是 保存後的模型實例。
use App\Models\Flight;

$flight = Flight::create([
    'name' => 'Amsterdam to Frankfurt',
    'status' => 'Delayed',
    'departure_time' => '2023-01-01 12:00:00',
]);
```

---

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name'];
}
```

---

- 設定好 `fillable` 後可直接 **create**：

<!-- create()：批量賦值並直接新增資料到資料庫。 -->

```php
$flight = Flight::create(['name' => 'London to Paris']);
```

---

- 也可用 **fill** `批量賦值`，__同時更新多個屬性__：

<!-- fill()：批量賦值到模型實例，但不會自動儲存，需再呼叫 save()。 -->

<!-- 批量賦值（Mass Assignment）是指一次給模型多個欄位的值，
     例如用陣列設定多個屬性，不是指生成多個檔案或資料。 -->

<!-- 只要一次設定超過一個欄位，就是批量賦值。 -->

```php
$flight->fill([
    'name' => 'Amsterdam to Frankfurt',
    'status' => 'Delayed',
    'departure_time' => '2023-01-01 12:00:00',
]);
```

---

- 若有 `JSON` 欄位，必須在 `fillable` **指定完整 key**：

```php
protected $fillable = [
    'options->enabled', // 指定 JSON 欄位中的完整 key，允許批量賦值
    // options 是資料表中的 JSON 欄位。
    // enabled 是 JSON 欄位中的一個 key。
    // 在 fillable 中指定完整的 key（options->enabled），表示允許批量賦值該 JSON key 的值。
    // 當資料表中有 JSON 欄位（例如 options），撈出來的資料中，該欄位會以 完整的 JSON 格式 返回。
];
```

---

- 若要**全部欄位**都可批量賦值，設 `$guarded = []`：

```php
protected $guarded = [];
// 空陣列，不保護任何欄位
```

---

- **開發時**可啟用**嚴格模式**，`未宣告欄位時丟出例外`：

```php
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    // 使用 Model::preventSilentlyDiscardingAttributes() 方法
    // 防止模型在未定義 fillable 屬性時，靜默丟棄未授權的欄位
    // 如果應用程式處於本地環境（Local），則啟用此功能
    Model::preventSilentlyDiscardingAttributes($this->app->isLocal());
}
```

---

### 11.10 *Upserts*

- `upsert` 可一次批量 **upsert（insert or update）**，三個參數分別為：`資料陣列`、`唯一索引欄位`、`要更新的欄位`。


```php
Flight::upsert([
    // 第一筆資料：從 Oakland 到 San Diego 的航班，價格 $99
    ['departure' => 'Oakland', 'destination' => 'San Diego', 'price' => 99],
    // 第二筆資料：從 Chicago 到 New York 的航班，價格 $150
    ['departure' => 'Chicago', 'destination' => 'New York', 'price' => 150]
], 
uniqueBy: ['departure', 'destination'],  // 使用出發地＋目的地作為唯一識別條件
update: ['price']                        // 當記錄已存在時，只更新 price 欄位
);
// 注意：uniqueBy 指定的欄位組合必須在資料表中有唯一索引
// 否則 upsert 無法判斷記錄是否重複，會執行失敗
// MySQL 會優先使用主鍵，其次是唯一索引來執行此操作
```

---

## 12. **刪除、軟刪除、Prune 與複製模型**（Deleting, Soft Deleting, Pruning & Replicating Models）

### 12.1 *Deleting Models*

- __模型時例__ `代表資料表的一筆資料`（_1 個 Model 實例 = 1 個資料列_）。
- __刪除模型實例__：呼叫模型實例的 `delete()` 方法，_會刪除資料表中的那一筆記錄（資料列）_。

<!-- 模型（Model）不是資料表，
     模型是資料表的物件化表示，用來操作資料表中的資料。

     資料表：資料庫裡的結構，存放多筆資料（多個資料列）。
     模型：對應資料表，是類別（class），代表資料表的結構與操作方法。
     模型實例：對應資料表中的一筆資料（資料列），是模型類別產生的物件。 
 -->

```php
use App\Models\Flight;

$flight = Flight::find(1);

$flight->delete();
```

---

### 12.2 *Deleting an Existing Model by its Primary Key*

- 若**已知主鍵**，可直接用 `destroy`，可接受 __單一、多個、陣列、collection 主鍵__。

```php
Flight::destroy(1);

Flight::destroy(1, 2, 3);

Flight::destroy([1, 2, 3]);

Flight::destroy(collect([1, 2, 3]));
```

---

- __軟刪除__ 模型可用 `forceDestroy` **永久刪除**：

```php
Flight::forceDestroy(1);
```

---

- `destroy` 會逐一載入 model 並呼叫 delete，**確保事件觸發**。

- **軟刪除**

```php
//  資料表需要 deleted_at 欄位
// migration 檔案
Schema::table('flights', function (Blueprint $table) {
    $table->softDeletes();  // 自動建立 deleted_at 欄位
});
```

```php
// Model 必須使用 SoftDeletes trait
use Illuminate\Database\Eloquent\SoftDeletes;

class Flight extends Model
{
    use SoftDeletes;  // 這個很重要！
}
$flight = Flight::find(1);
$flight->delete();  // 現在才是軟刪除
```

---

- **一般刪除**

```php
// 如果只有資料表欄位，沒有 trait
class Flight extends Model
{
    // 沒有 use SoftDeletes;
}

$flight = Flight::find(1);
$flight->delete();  // 還是會真正刪除記錄！
```

---

### 12.3 *Deleting Models Using Queries*

- 可用**查詢條件**批次刪除，`回傳刪除筆數`，__不會觸發 model 事件__。

```php
$deleted = Flight::where('active', 0)->delete();
```

---

- 若要刪除**整個資料表所有資料**：

```php
$deleted = Flight::query()->delete();
```

---

### 12.4 *Soft Deleting*

- 加入 `SoftDeletes trait`，可啟用**軟刪除**，`delete` 時只會設 `deleted_at` 欄位。


```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flight extends Model
{
    use SoftDeletes;
}
```

---

- `deleted_at` 欄位需在 migration 加入：

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::table('flights', function (Blueprint $table) {
    $table->softDeletes();
});

Schema::table('flights', function (Blueprint $table) {
    $table->dropSoftDeletes();
});
```

---

- `delete` 時只會設 `deleted_at`，不會真的刪除。
- 查詢時會**自動排除**軟刪除資料。
- 可用 `trashed()` 判斷是否已軟刪除：

```php
if ($flight->trashed()) {
    // ...
}
```

---

### 12.5 *Restoring Soft Deleted Models*

- **還原軟刪除**：呼叫 `restore()`，會將 `deleted_at` 設為 `null`。

```php
$flight->restore();
```

---

- 也可用 `query` **批次還原**：

```php
Flight::withTrashed()
      ->where('airline_id', 1)
      ->restore();
```

---

- **關聯查詢**也可 `restore`：

```php
$flight->history()->restore();
```

---

### 12.6 *Permanently Deleting Models*

- **永久刪除**（真正從資料庫移除）：呼叫 `forceDelete()`。

```php
$flight->forceDelete();
```

---

- **關聯查詢**也可 `forceDelete`：

```php
$flight->history()->forceDelete();
```

---

### 12.7 *Querying Soft Deleted Models*

#### 12.7.1 **Including Soft Deleted Models**

- 用 `withTrashed()` __查詢包含軟刪除資料__：

```php
use App\Models\Flight;

$flights = Flight::withTrashed()
    ->where('account_id', 1)
    ->get();
```

---

- **關聯查詢**也可 `withTrashed`：

```php
$flight->history()->withTrashed()->get();
```

---

#### 12.7.2 **Retrieving Only Soft Deleted Models**

- 用 `onlyTrashed()` __只查__ 軟刪除資料：

```php
$flights = Flight::onlyTrashed()
    ->where('airline_id', 1)
    ->get();
```

---

### 12.8 *Pruning Models*

- 加入 `Prunable trait`，實作 `prunable()`，可**定期自動清理不需要的資料**。


```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable; // 一般 Prunable
class Flight extends Model
{
    use Prunable;

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subMonth());
    }
}
```

---

- 可定義 `pruning()`，在**刪除前**做額外處理：

```php
/**
 * Prepare the model for pruning.
 */
protected function pruning(): void
{
    // ...
}
```

---

- **設定排程**執行 `model:prune` 指令：

```php
use Illuminate\Support\Facades\Schedule;

// 每天自動清理所有有定義 prunable() 的 model
Schedule::command('model:prune')->daily();

// 只清理指定的 model
Schedule::command('model:prune', [
    '--model' => [Address::class, Flight::class],
])->daily();

// 清理所有 model，但排除指定的
Schedule::command('model:prune', [
    '--except' => [Address::class, Flight::class],
])->daily();
```

---

- 可用 `--pretend` 測試 `prune` 效果：

```bash
php artisan model:prune --pretend
```

---

- **軟刪除資料** `若符合 prune 條件`會被 `forceDelete`。

---

### 12.9 *Mass Pruning*

- 加入 `MassPrunable trait`，`prune `時直接用 `mass delete`，**不會觸發事件** `/pruning()`，效率更高。

- **一般 Prunable 的執行方式**
  - *執行過程*
    - 找出符合條件的記錄
    - 逐一載入每個 Model 實例
    - 逐一呼叫每個實例的 `delete()` 方法
    - 每次刪除都`觸發事件`（deleting, deleted 等）
    - 執行 `pruning()` 方法

    ```sql
    -- 實際執行類似這樣
    SELECT * FROM flights WHERE created_at <= '2024-12-06';  -- 載入所有記錄
    DELETE FROM flights WHERE id = 1;                        -- 逐一刪除
    DELETE FROM flights WHERE id = 2;
    DELETE FROM flights WHERE id = 3;
    -- ... 每筆都是獨立的 DELETE 語句
    ```

    - 1 次 SELECT 查詢載入 10,000 個 Model
    - 10,000 次 DELETE 語句
    - 10,000 次事件觸發
    - **記憶體使用**：載入 10,000 個物件

---

- **MassPrunable 的執行方式**
  - *執行過程*
    - 找出`符合條件`的記錄
    - 直接`執行一次`批量 DELETE
    - 不載入 Model 實例
    - 不觸發任何事件
    - 不執行 `pruning()` 方法

    ```sql
    -- 實際執行只有一條語句
    DELETE FROM flights WHERE created_at <= '2024-12-06';
    ```

    - 1 次 DELETE 語句
    - 0 次事件觸發
    - **記憶體使用**：幾乎不耗費記憶體

---

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\MassPrunable; // 批量

class Flight extends Model
{
    use MassPrunable;

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subMonth());
    }
}
```

---

### 12.10 *Replicating Models*

- 用 `replicate()` 可 **複製** 現有 model 實例（**未存入 DB**，要`save()`），可用 `fill()` **覆蓋部分欄位**。


```php
use App\Models\Address;

$shipping = Address::create([
    'type' => 'shipping',
    'line_1' => '123 Example Street',
    'city' => 'Victorville',
    'state' => 'CA',
    'postcode' => '90001',
]);

$billing = $shipping->replicate()->fill([
    'type' => 'billing'
]);

$billing->save();
```

---

- 可**排除欄位**不複製：

```php
$flight = Flight::create([
    'destination' => 'LAX',
    'origin' => 'LHR',
    'last_flown' => '2020-03-04 11:00:00',
    'last_pilot_id' => 747,
]);

$flight = $flight->replicate([
    'last_flown',
    'last_pilot_id'
]);
// 新的 Model 實例（尚未儲存到資料庫）
Flight {
    id: null,                           // 主鍵被重置
    destination: 'LAX',                 // 保留
    origin: 'LHR',                      // 保留
    last_flown: null,                   // 被排除，設為 null
    last_pilot_id: null,                // 被排除，設為 null
    created_at: null,                   // timestamp 被重置
    updated_at: null,                   // timestamp 被重置
}
```

---

## 13. **查詢 Scope、事件與 Observer**（Query Scopes, Events & Observers）

### 13.1 *Global Scopes*

- `全域 Scope` 可為 Model **所有查詢** 自動加上條件，Laravel **軟刪除** 即用此機制。

---

#### 13.1.1 **產生 Scope 類別**

```bash
php artisan make:scope AncientScope
```

---

#### 13.1.2 **撰寫 Global Scope**

```php
// app/Models/Scopes/AncientScope.php
// app/
// └── Models/
//     ├── User.php
//     ├── Flight.php
//     └── Scopes/
//         ├── AncientScope.php
//         ├── ActiveScope.php
//         ├── PublishedScope.php
//         └── PopularScope.php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AncientScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // ✅ 會自動執行，因為在 apply() 中
        $this->addTimeCondition($builder);
        $this->addStatusCondition($builder);
    }
    
    private function addTimeCondition(Builder $builder): void
    {
        // ✅ 會被執行（被 apply() 呼叫）
        $builder->where('created_at', '<', now()->subYears(2000));
    }
    
    private function addStatusCondition(Builder $builder): void
    {
        // ✅ 會被執行（被 apply() 呼叫）
        $builder->where('status', 'active');
    }
        public function customMethod()
    {
        // ❌ 這個不會自動執行
        return 'This will not be called automatically';
    }
    
    private function helperMethod()
    {
        // ❌ 這個也不會自動執行
        return 'Helper logic';
    }
}
```

---

- 若要加 `select` 欄位，請用 `addSelect` *以免覆蓋原查詢*。

---

#### 13.1.3 **套用 Global Scope**

- 可用 `attribute` 標註：

```php
namespace App\Models;

use App\Models\Scopes\AncientScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

// 新方式：只需要標註
// Global Scope 需要明確註冊到特定 Model
#[ScopedBy([AncientScope::class])]  // ← 這就是 attribute 標註
// #[ScopedBy(陣列)]
// 可以同時使用多個 Scope
// #[ScopedBy([
//     AncientScope::class,
//     ActiveScope::class,
//     PublishedScope::class
// ])]

class User extends Model  // 只有 User Model 會套用
{
    // 不需要寫 booted() 方法
    // Laravel 會自動讀取標註並註冊 Scope
}

class Post extends Model  // Post Model 不會套用 AncientScope
{
}

// 對 User 的所有查詢都會自動套用 AncientScope
User::all();                    // ✅ 套用
User::where('name', 'John');    // ✅ 套用  
User::orderBy('email');         // ✅ 套用
User::paginate(10);             // ✅ 套用

// 但 Post 不會套用
Post::all();                    // ❌ 不套用
```

---

- 或在 `booted` 方法中註冊：

```php
namespace App\Models;

use App\Models\Scopes\AncientScope;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // 舊方式：需要寫程式碼
    protected static function booted()
    {
        static::addGlobalScope(new AncientScope);
    }
}
```

---

- 套用後，`User::all()` 會自動加上 `where` 條件。

```php
User::all();
// Laravel 內部自動執行：
// $scope = new AncientScope();
// $scope->apply($queryBuilder, $userModel);
// 
// 等同於：
// $builder->where('created_at', '<', now()->subYears(2000));
User::where('name', 'John')->get();
// 實際 SQL: SELECT * FROM users WHERE name = 'John' AND created_at < '2025-01-01'

User::orderBy('email')->paginate(10);
// 實際 SQL: SELECT * FROM users WHERE created_at < '2025-01-01' ORDER BY email LIMIT 10

// 如果你需要使用 Global Scope 的其他方法
// 需要建立 Global Scope 實例
$scope = new AncientScope();
$result = $scope->customMethod();  // 手動呼叫
```

---

- *`Global Scope` 是獨立的類別*
```php
// 這是 Global Scope 類別
class AncientScope implements Scope  // 必須實作 Scope interface
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('created_at', '<', now()->subYears(2000));
    }
}

// 如果你需要使用 Global Scope 的其他方法
// 需要建立 Global Scope 實例
$scope = new AncientScope();
$result = $scope->customMethod();  // 手動呼叫
```

---

- *`Local Scope` 是 Model 內部的方法*
```php
// Local Scope 是 Model 內部的方法
class User extends Model
{
    public function scopeAncient($query)  // 這是 Local Scope
    {
        return $query->where('created_at', '<', now()->subYears(2000));
    }
}
```
---

#### 13.1.4 **匿名 Global Scope**

- 可用 `closure` 直接定義 *簡單的全域 scope*：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope('ancient', function (Builder $builder) {
            $builder->where('created_at', '<', now()->subYears(2000));
        });
    }
}
```

---

#### 13.1.5 **移除 Global Scope**

- 用 `withoutGlobalScope/withoutGlobalScopes` *移除全域 scope*：

```php
User::withoutGlobalScope(AncientScope::class)->get();
User::withoutGlobalScope('ancient')->get();
User::withoutGlobalScopes()->get();
User::withoutGlobalScopes([
    FirstScope::class, SecondScope::class
])->get();
```

---

### 13.2 *Local Scopes*

- `Local scope` 可重複使用常見查詢條件，方法加上 `#[Scope] attribute`。


```php
// 新的 Attribute 寫法
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Scope a query to only include popular users.
     */
    // 使用 #[Scope] Attribute，方法名不用 scope 前綴
    #[Scope]
    protected function popular(Builder $query): void
    {
        $query->where('votes', '>', 100);
    }

    /**
     * Scope a query to only include active users.
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('active', 1);
    }
}
```

```php
// 傳統寫法（不用 Attribute）
class User extends Model
{
    // 方法名必須以 scope 開頭
    public function scopePopular($query)
    {
        return $query->where('votes', '>', 100);
    }
    
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
```

---

#### 13.2.1 **使用 Local Scope**

- 查詢時可直接呼叫 `scope` 方法，並可 *鏈式呼叫*：

```php
use App\Models\User;

$users = User::popular()->active()->orderBy('created_at')->get();
```

---

- 若需 `orWhere` 組合，可用 `closure` 或 `higher order orWhere`：

```php
$users = User::popular()->orWhere(function (Builder $query) {
    $query->active();  // 在 closure 內呼叫 scope
})->get();

$users = User::popular()->orWhere->active()->get();
//                      ^^^^^^^^^ 
//                      這就是 Higher Order
```

```sql
-- 兩種寫法會產生相同的 SQL
SELECT * FROM users 
WHERE votes > 100 
OR active = 1
```

---

### 13.3 *Dynamic Scopes*

- `Scope` 方法可**接受參數**，直接`在方法簽名加上`即可。


```php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Scope a query to only include users of a given type.
     */
    // 使用 #[Scope] Attribute，方法名不用 scope 前綴，所以方法名可以直接ofType
    #[Scope]
    protected function ofType(Builder $query, string $type): void
    //        ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    //        這整行就是方法簽名
    {
        $query->where('type', $type);
    }
}
protected function ofType(Builder $query, string $type): void
//   ↑        ↑      ↑           ↑              ↑        ↑
//   |        |      |           |              |        |
// 可見性   關鍵字  方法名    第一個參數     第二個參數   返回類型
```

---

- **呼叫時直接傳參數**：

```php
$users = User::ofType('admin')->get();
```

---

### 13.4 *Pending Attributes*

- 用 `scope + withAttributes` 可讓 `create` 時**自動帶入預設屬性**。


```php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    #[Scope]
    protected function draft(Builder $query): void
    {
        $query->withAttributes([
            'hidden' => true,
        ]);
    }
}

$draft = Post::draft()->create(['title' => 'In Progress']);
$draft->hidden; // true

// 不加 where 條件：
$query->withAttributes([
    'hidden' => true,
], asConditions: false);
```

---

- **Laravel 內部的處理邏輯**

```php
// 當你呼叫 withAttributes 時，Laravel 內部會：

public function withAttributes(array $attributes, bool $asConditions = true)
{
    // 1. 儲存預設屬性（用於 create 時）
    $this->pendingAttributes = $attributes;
    
    // 2. 如果 asConditions 為 true，自動加入 where 條件
    if ($asConditions) {
        foreach ($attributes as $key => $value) {
            $this->where($key, $value);  // 這裡自動加入 where！
            // 為什麼看不到 where？
            // 因為 withAttributes 是一個高階方法，它會：
            // 自動處理查詢條件（你看不到的部分）
            // 處理預設屬性（你看得到的部分）
        }
    }
    
    return $this;
}
// 當你執行查詢時
$posts = Post::draft()->get();

// 實際的 SQL 會是：
// SELECT * FROM posts WHERE hidden = 1
```

---

- **傳統寫法**（明確的 where）

```php
#[Scope]
protected function draft(Builder $query): void
{
    $query->where('hidden', true);  // 明確的 where
}
```

---

- **新的 withAttributes 寫法**

```php
#[Scope]
protected function draft(Builder $query): void
{
    $query->withAttributes([
        'hidden' => true,
    ]); // where 條件被包裝在 withAttributes 內部
}
```

---

### 13.5 *Comparing Models*

- 用 `is/isNot` 判斷兩個 model **是否** 為`同一筆資料`。

```php
if ($post->is($anotherPost)) {
    // ...
}

if ($post->isNot($anotherPost)) {
    // ...
}

// 關聯也可用 is
if ($post->author()->is($user)) {
    // ...
}
```

---

### 13.6 *Events*

- `Eloquent` 支援多種**事件**：
                                `retrieved`、
                                `creating`、
                                `created`、
                                `updating`、
                                `updated`、
                                `saving`、
                                `saved`、
                                `deleting`、
                                `deleted`、
                                `trashed`、
                                `forceDeleting`、
                                `forceDeleted`、
                                `restoring`、
                                `restored`、
                                `replicating`。

- 可用 `$dispatchesEvents` 屬性對應*事件*與*自訂事件類別*。

---

- **設定 Model 事件對應**

```php
namespace App\Models;

use App\Events\UserDeleted;
use App\Events\UserSaved;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The event map for the model.
     *
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'saved' => UserSaved::class,      // Model事件 -> 自訂事件
        'deleted' => UserDeleted::class,  // Model事件 -> 自訂事件
    ];
}
```

---

- **建立自訂事件類別**

```php
// app/Events/UserSaved.php
class UserSaved  // 這是事件類別，不是監聽器
{
    public function __construct(
        public User $user
    ) {}
}

// app/Events/UserDeleted.php  
class UserDeleted  // 這是事件類別，不是監聽器
{
    public function __construct(
        public User $user
    ) {}
}
```

---

- **建立監聽器類別**

```php
// app/Listeners/SendWelcomeEmail.php
class SendWelcomeEmail  // 這才是監聽器
{
    public function handle(UserSaved $event)
    {
        // 處理 UserSaved 事件的邏輯
    }
}

// app/Listeners/CleanupUserData.php
class CleanupUserData  // 這才是監聽器
{
    public function handle(UserDeleted $event)
    {
        // 處理 UserDeleted 事件的邏輯
    }
}
```

---

- **註冊事件與監聽器的對應**

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    UserSaved::class => [           // 事件類別
        SendWelcomeEmail::class,    // 監聽器類別
        UpdateUserStats::class,     // 監聽器類別
    ],
    UserDeleted::class => [         // 事件類別
        CleanupUserData::class,     // 監聽器類別
        SendGoodbyeEmail::class,    // 監聽器類別
    ],
];
```

---

- **大量更新/刪除** 不會觸發事件。

---

#### 13.6.1 **使用 Closure 註冊事件**

- 可在 `booted` 方法中用 `closure` 註冊事件：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected static function booted(): void
    {
        static::created(function (User $user) {
            // ...
        });
    }
}
```

---

- 也可用 `queueable` **匿名事件監聽器**：

- 會放在
  - `Model::booted()`
    - 邏輯集中、自動載入
    - Model 檔案可能變大

  - `ServiceProvider`
    - 統一管理、可跨 Model
    - 需要手動註冊

  - `Observer`
    - 完整的事件處理、可測試
    - 需要建立額外檔案

```php
use function Illuminate\Events\queueable;

// 使用 queueable 包裝事件監聽器，讓它在背景佇列中執行
static::created(queueable(function (User $user) {
    // 這個 closure 會在 User 建立後以佇列方式執行
    // 不會阻塞主要的請求流程，提升應用程式效能
    
    // 常見的佇列任務範例：
    // 1. 發送歡迎郵件（耗時操作）
    Mail::to($user->email)->send(new WelcomeEmail($user));
    
    // 2. 建立用戶資料夾結構
    Storage::makeDirectory("users/{$user->id}");
    
    // 3. 同步到第三方服務（可能網路延遲）
    Http::post('https://api.example.com/users', $user->toArray());
    
    // 4. 產生用戶統計資料
    UserStatistics::create(['user_id' => $user->id, 'total_posts' => 0]);
    
}));
```

---

-  **沒有 queueable**（同步執行）

```php
static::created(function (User $user) {
    // 這會在主要請求中立即執行
    // 如果發送郵件很慢，用戶要等待才能看到回應
    Mail::to($user->email)->send(new WelcomeEmail($user));
});

// 執行流程：
// 用戶註冊 -> 儲存到資料庫 -> 發送郵件(等待 3秒) -> 回應用戶
// 總時間：3.5秒
```

---

- **使用 queueable**（非同步執行）

```php
static::created(queueable(function (User $user) {
    // 這會被放入佇列，在背景執行
    // 用戶立即得到回應，郵件在背景發送
    Mail::to($user->email)->send(new WelcomeEmail($user));
}));

// 執行流程：
// 用戶註冊 -> 儲存到資料庫 -> 加入佇列 -> 立即回應用戶
// 總時間：0.5秒（郵件在背景處理）
```

---

### 13.7 *Observers*

- **監聽多個事件** 時，可用 `Observer` `類別，Artisan` 可快速產生 `observer`：

```bash
php artisan make:observer UserObserver --model=User
```

---

- `Observer` 會放在 `app/Observers` 目錄，**方法名稱**對應**事件**。


```php
namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user): void { /* ... */ }
    public function updated(User $user): void { /* ... */ }
    public function deleted(User $user): void { /* ... */ }
    public function restored(User $user): void { /* ... */ }
    public function forceDeleted(User $user): void { /* ... */ }
}
```

---

- 註冊 `observer` 可用 `attribute` 或 `observe 方法`：

- **新方法**

```php
// app/Models/User.php
namespace App\Models;

use App\Observers\UserObserver; // 需要引入 Observer
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// app/Models/User.php
#[ObservedBy([UserObserver::class])]  // 自動註冊 Observer
class User extends Authenticatable
{
    // Laravel 會自動處理 Observer 註冊
}

// app/Providers/AppServiceProvider.php
public function boot()
{
    // 不需要手動註冊，Laravel 會自動讀取 Attribute
}
```

---

- **傳統方法**

```php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use App\Models\User;                    // 必須引入
use App\Observers\UserObserver;         // 必須引入
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        User::observe(UserObserver::class);  // 手動註冊
    }
}
```

```php
// app/Observers/UserObserver.php
namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user)
    {
        // 處理邏輯
    }
}
```

```php
// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    
    // ✅ 傳統方式：Model 只負責定義自己的邏輯
    // ❌ 不需要引入 Observer
    // ❌ 不需要 #[ObservedBy] Attribute
    // ❌ 不需要任何 Observer 相關程式碼
    
    protected $fillable = [
        'name', 'email', 'password',
    ];
    
    // 其他 Model 的方法和關聯...
}
```

---

- Observer 也可監聽 `saving、retrieved` 等事件。

---

#### 13.7.1 **Observers and Database Transactions**

- 若 observer 實作 `ShouldHandleEventsAfterCommit`，事件會等 `transaction commit 後` 才執行。

- *Transaction*（交易）

  - `Transaction` 是一組 __資料庫操作的集合__，這**些操作要麼全部成功，要麼全部失敗**。

  - __目的__：`確保資料一致性`，避免部分成功部分失敗的情況
  - __四個階段__：Begin → 執行操作 → Commit/Rollback
  - __ACID 特性__：
                    `原子性`（Atomicity）、
                    `一致性`（Consistency）、
                    `隔離性`（Isolation）、
                    `持久性`（Durability）

- *Commit*（提交）
  - `Commit` 是資料庫交易術語，意思是「__確認並永久保存__」資料庫的變更。
  - __執行時機__：`所有操作都成功完成後`
  - __效果__：將所有變更從「暫存狀態」變為「`永久保存`」
  - __不可逆__：一旦 commit，變更就`無法撤銷`

- *Rollback*（回滾）
  - __目的__：`取消`交易中的所有變更
  - __使用時機__：當任何`操作失敗`時，自動或手動觸發
  - __效果__：`回到交易開始前的狀態`，就像什麼都沒發生過

- *Laravel Observer 與 Transaction*
  - __預設行為__`：Observer` 在操作完成後 __立即執行__（`commit 前`）
  - `ShouldHandleEventsAfterCommit`：`Observer` 等到 `commit 後 `才執行
    - __好處__：確保 `Observer` 只在資料 __真正成功保存後__ 執行，避免資料不一致

---

- *實際執行流程*

```php
DB::beginTransaction();        // 1. 開始交易
User::create([...]);          // 2. 執行操作（暫存狀態）
$user->posts()->create([...]); // 3. 更多操作（暫存狀態）
DB::commit();                 // 4. 確認保存（永久狀態）
// Observer 在此時執行（如果有 ShouldHandleEventsAfterCommit）
```

---

```php
namespace App\Observers;

use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    public function created(User $user): void { /* ... */ }
}
```

---

### 13.8 *Muting Events*

- 可用 `withoutEvents` **暫時靜音** 所有事件，`closure` 內的操作 **不會觸發事件** ：

```php
use App\Models\User;

$user = User::withoutEvents(function () {
    User::findOrFail(1)->delete();
    return User::find(2);
});
```

---

### 13.9 *Saving a Single Model Without Events*

- 用    `saveQuietly`、
        `deleteQuietly`、
        `forceDeleteQuietly`、
        `restoreQuietly`、
        `replicateQuietly` 可靜音 **單一操作**：

```php
// 找到 ID 為 1 的用戶，如果找不到會拋出 ModelNotFoundException
$user = User::findOrFail(1);

// 修改用戶名稱（只是在記憶體中修改，還沒存到資料庫）
$user->name = 'Victoria Faith';

// 靜音儲存：儲存變更但不觸發任何 Model 事件
// 不會觸發：saving, saved, updating, updated 事件
// 不會執行：Observer 的對應方法
// 不會觸發：$dispatchesEvents 設定的事件
$user->saveQuietly();

// 靜音軟刪除：標記為刪除但不觸發事件  
// 不會觸發：deleting, deleted 事件
// 不會執行：Observer::deleting(), Observer::deleted()
$user->deleteQuietly();

// 靜音強制刪除：永久刪除記錄但不觸發事件
// 不會觸發：deleting, deleted 事件
// 直接從資料庫移除，跳過所有事件處理
$user->forceDeleteQuietly();

// 靜音復原：復原軟刪除但不觸發事件
// 不會觸發：restoring, restored 事件
// 直接將 deleted_at 設為 null，跳過事件處理
$user->restoreQuietly();
```
