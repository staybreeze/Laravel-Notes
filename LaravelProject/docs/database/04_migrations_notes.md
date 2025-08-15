# *Laravel Migrations 筆記*

---

## 1. **Migrations 簡介**

Migrations 就像`資料庫的版本控制`，讓團隊可以定義、分享資料庫 `schema`，避免每次 pull code 後還要手動改資料表。
- Laravel 的 `Schema facade` 提供*跨資料庫的表格建立與修改支援*。
- migrations 通常用來建立、修改資料表與欄位。

---

## 2. **產生 Migration**

```bash
php artisan make:migration create_flights_table
```
- 產生的 migration 會放在 `database/migrations` 目錄下，檔名含 `timestamp` 以決定執行順序。
- Laravel 會根據 `migration` 名稱*自動推測* table 名稱與建立/修改行為。
- 若無法推測，需手動指定 table。

```bash
php artisan make:migration create_flights_table --path=custom/path
```
- 可用 `--path` *指定自訂路徑*（相對於專案根目錄）。
- `migration stub` 可用 `stub publishing` 客製化。

---

## 3. **Squashing Migrations（壓縮合併）**

```bash
php artisan schema:dump
# Dump the current database schema and prune all existing migrations...
php artisan schema:dump --prune
```
- *作用*：
  - 將目前的`資料庫結構（Schema）`匯出為一個 **SQL 檔案** ，並`清除所有已執行的 migrations`。
  - 這樣可以減少 migrations 的數量，避免執行大量的歷史 migrations。

- *檔案位置*：
  - 會在 `database/schema` 目錄產生 schema 檔案。
  - **檔名** 會對應資料庫連線名稱（例如 mysql-schema.sql）。

- *執行行為*：
  - 如果 **尚未執行** 任何 migrations：
    - Laravel 會先執行 schema 檔案（快速建立資料庫結構）。
    - 然後執行剩餘的 migrations（新增或修改資料表）。

- *測試用不同連線時的行為*
  - 當使用不同的資料庫連線（例如測試環境），需要針對該連線 `dump schema`。

```bash
php artisan schema:dump
php artisan schema:dump --database=testing --prune
# --database=testing：指定測試環境的資料庫連線。
# --prune：清除所有已執行的 migrations。
```
- *建議*
  - `Schema 檔案`建議 **commit 進版控**：
    - 方便新同事快速建立資料庫，無需執行所有歷史 migrations。
    - 直接使用 schema 檔案建立資料庫結構。

- *僅支援以下資料庫*
    - MariaDB
    - MySQL
    - PostgreSQL
    - SQLite

- *注意*
  - 必須使用資料庫的 `CLI 工具`（例如 mysql、psql）來執行 `schema dump`。


---

## 4. **Migration 結構**

一個 migration class 會有兩個方法：`up`（建立/修改）與 `down`（還原）。

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('airline');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('flights');
    }
};
```
- `up` 用來*建立/修改*資料表、欄位、索引。
- `down` 用來*還原* `up` 的操作。

---

## 5. **指定 Migration 連線**

```php
/**
 * The database connection that should be used by the migration.
 *
 * @var string
 */
protected $connection = 'pgsql';

public function up(): void
{
    // ...
}
```
- 若要操作*非預設*連線，設 `$connection` 屬性。

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

## 6. **跳過 Migration 執行**

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Pennant\Feature;

class CreateFlightsTable extends Migration
{
    /**
     * Determine if this migration should run.
     */
    public function shouldRun(): bool
    {
        // 判斷是否啟用 Flights 功能，若未啟用則跳過此 Migration
        return Feature::active('flights');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!$this->shouldRun()) {
            return; // 如果 shouldRun 回傳 false，跳過執行
        }

        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!$this->shouldRun()) {
            return; // 如果 shouldRun 回傳 false，跳過執行
        }

        Schema::dropIfExists('flights');
    }
}
```
- 若 `shouldRun` 回傳 *false*，migration 會被跳過。

- *Feature Facade*
  - `Feature` 是 Laravel 提供的 Facade，對應 `Pennant` 套件的**功能旗標管理**。
  - `Facade` 的底層實現會指向 `Pennant` 的核心類別（通常是 `Laravel\Pennant\FeatureManager`）。

```php
namespace Laravel\Pennant;

class FeatureManager
{
    protected $features = [];

    public function active(string $feature): bool
    {
        // 判斷功能旗標是否啟用
        return isset($this->features[$feature]) && $this->features[$feature] === true;
    }

    public function activate(string $feature): void
    {
        // 啟用功能旗標
        $this->features[$feature] = true;
    }

    public function deactivate(string $feature): void
    {
        // 停用功能旗標
        $this->features[$feature] = false;
    }
}
```
- *功能旗標*（Feature Flag）：
  - 是一種`動態控制`功能啟用或停用的技術。
  - 你可以透過程式或介面（例如按鈕）來設定功能的開關。
  - 在程式中使用 `Feature::active()` 判斷功能是否啟用，並根據結果執行不同的邏輯。

```php
// 設定功能旗標
Feature::activate('flights'); // 啟用 flights 功能
Feature::deactivate('flights'); // 停用 flights 功能
```

```php
// 在程式中檢查功能旗標
if (Feature::active('flights')) {
    // Flights 功能啟用時執行的邏輯
    Flights::enable();
} else {
    // Flights 功能未啟用時執行的邏輯
    Flights::disable();
}
```

```php
// 在 Laravel 各處使用
public function shouldRun(): bool
{
    return Feature::active('flights'); // 如果 Flights 功能未啟用，Migration 會被跳過
}
```
---

## 7. **執行 Migration**

- *執行*所有尚未執行的 migration。
```bash
php artisan migrate
```

- *查看*哪些 migration 已執行。
```bash
php artisan migrate:status
```

- *顯示*將執行的 SQL，但不實際執行。
```bash
php artisan migrate --pretend
```

---

## 8. **Isolating Migration Execution（隔離執行）**

```bash
php artisan migrate --isolated
# 用於多台伺服器部署時，確保只有一台伺服器能執行 Migration，避免重複執行。
```
- 多台伺服器部署時，*避免同時執行* migration。
- *Cache Driver*：
  - 必須使用`共享的 Cache`（例如 memcached、redis 等），讓所有伺服器能透過同一個 Cache 來協調 Migration 的執行。
  - Cache 用來鎖定 Migration，確保其他伺服器等待鎖釋放後再執行。

---

## 9. **強制 Production 執行 Migration**

```bash
php artisan migrate --force
```
- 跳過生產環境的確認提示，直接執行。

---

## 10. **回滾 Migration**

```bash
php artisan migrate:rollback
```
- 回滾最新一批 migration（可能包含多個檔案）。

```bash
php artisan migrate:rollback --step=5
```
- 回滾*最近* 5 個 migration。

```bash
php artisan migrate:rollback --batch=3
```
- 回滾 第 3 *批次*（batch=3） 的所有 migration。
- Laravel 在執行 Migration 時，會自動為每次執行的 Migration 分配一個*批次號（batch）*。
- 此指令只回滾指定批次的 Migration，而不影響其他批次。

```bash
php artisan migrate:rollback --pretend
```
- *顯示*將執行的 SQL，但不實際執行。

```bash
php artisan migrate:reset
```
- 回滾*所有* migration。

---

## 11. **Roll Back and Migrate Using a Single Command**

```bash
php artisan migrate:refresh
# Refresh the database and run all database seeds...
php artisan migrate:refresh --seed
# --seed：在重新執行 Migration 後執行 Seeder。
```
- *先回滾* 所有 migration，*再重新執行* migrate。
- *不會刪除* 資料表，只是透過回滾和重新執行來重置資料表的狀態。

```bash
php artisan migrate:refresh --step=5
```
- 回滾並重新執行最近 5 個 migration。

---

## 12. **Drop All Tables and Migrate**

```bash
php artisan migrate:fresh
php artisan migrate:fresh --seed
# --seed：在重新執行 Migration 後執行 Seeder。
```
- 會*刪除*所有資料表，*再執行* migrate。

```bash
php artisan migrate:fresh --database=admin
# 使用 --database=admin 參數，告訴 Laravel 使用名為 admin 的資料庫連線來執行 migrate:fresh。
# 這個連線必須在 config/database.php 中定義。
```
- 指定連線執行 `migrate:fresh`。
- `migrate:fresh` 會刪除所有資料表（不管有無 prefix），請小心使用。

---

## 13. **Tables 操作**

### 13.1 *建立資料表*

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->timestamps();
});
```
- 可用 `schema builder` 的各種欄位方法定義欄位。

---

### 13.2 *判斷表格/欄位/索引是否存在*

```php
if (Schema::hasTable('users')) {
    // The "users" table exists...
}

if (Schema::hasColumn('users', 'email')) {
    // The "users" table exists and has an "email" column...
}

if (Schema::hasIndex('users', ['email'], 'unique')) {
    // The "users" table exists and has a unique index on the "email" column...
}
```

---

### 13.3 *指定連線與表格選項*

```php
Schema::connection('sqlite')->create('users', function (Blueprint $table) {
    $table->id();
});
```
- 指定**非預設**連線。

---

```php
Schema::create('users', function (Blueprint $table) {
    $table->engine('InnoDB');
    // ...
});
```
- 指定 `MySQL/MariaDB` 的 `storage engine`。

---

```php
Schema::create('users', function (Blueprint $table) {
    $table->charset('utf8mb4');
    // charset 是指資料表的字元編碼，用來定義資料如何存儲和表示。
    // 常見的字元編碼：
    //   utf8mb4：支援完整的 UTF-8 編碼，包括表情符號（Emoji）。
    //   utf8：支援基本的 UTF-8 編碼，但不支援表情符號。
    $table->collation('utf8mb4_unicode_ci');
    // collation 是指資料表的排序規則，用來定義字元如何比較和排序。
    // 常見的排序規則：
    //   utf8mb4_unicode_ci：
    //     大小寫不敏感（Case Insensitive）。
    //     使用 Unicode 規則進行排序。
    //   utf8mb4_bin：
    //     大小寫敏感（Case Sensitive）。
    //     大小寫敏感 是指在比較或排序字元時，區分字母的大小寫。例如，字母 A 和 a 被視為不同的字元。
    //     使用二進制方式進行排序。
    // ...
});
```
- 指定 `charset/collation`。

---

```php
Schema::create('calculations', function (Blueprint $table) {
    $table->temporary();
    // temporary table（暫時表） 是指在資料庫中建立的 臨時性資料表，它的生命週期僅限於當前資料庫連線。
    // ...
});
```
- 建立 `temporary table`。

---

```php
Schema::create('calculations', function (Blueprint $table) {
    $table->comment('Business calculations');
    // ...
});
```
- 資料表**註解**（僅支援 MariaDB、MySQL、PostgreSQL）。

---

### 13.4 *更新資料表*

```php
Schema::table('users', function (Blueprint $table) {
    $table->integer('votes');
});
// 使用 Schema::table 方法來更新現有的資料表結構（例如新增欄位、修改欄位或刪除欄位）。雖然沒有明確的 update 關鍵字，但這段程式碼的作用就是 更新資料表結構。
```
- 用 `table` 方法更新現有資料表。

---

### 13.5 *重新命名/刪除資料表*

```php
Schema::rename($from, $to);
Schema::drop('users');
Schema::dropIfExists('users');
```
- `rename` **重新命名**資料表。
- `drop/dropIfExists` **刪除**資料表。

---

## 14. **Columns 欄位操作**

### 14.1 *新增欄位*

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::table('users', function (Blueprint $table) {
    $table->integer('votes');
});
```
- 用 `Schema::table` 方法可在現有資料表**新增欄位**。

---

### 14.2 *可用欄位型別一覽*

- Boolean Types: `boolean`
- String & Text Types:  `char`, 
                        `longText`, 
                        `mediumText`, 
                        `string`, 
                        `text`, 
                        `tinyText`

- Numeric Types:    `bigIncrements`, 
                    `bigInteger`, 
                    `decimal`, 
                    `double`, 
                    `float`, 
                    `id`,
                    `increments`, 
                    `integer`, 
                    `mediumIncrements`, 
                    `mediumInteger`, 
                    `smallIncrements`, 
                    `smallInteger`, 
                    `tinyIncrements`, 
                    `tinyInteger`, 
                    `unsignedBigInteger`, 
                    `unsignedInteger`, 
                    `unsignedMediumInteger`, 
                    `unsignedSmallInteger`, 
                    `unsignedTinyInteger`

- Date & Time Types:    `dateTime`, 
                        `dateTimeTz`, 
                        `date`, 
                        `time`, 
                        `timeTz`, 
                        `timestamp`, 
                        `timestamps`, 
                        `timestampsTz`, 
                        `softDeletes`, 
                        `softDeletesTz`, 
                        `year`

- Binary Types: `binary`

- Object & Json Types: `json`, `jsonb`

- UUID & ULID Types:    `ulid`, 
                        `ulidMorphs`, 
                        `uuid`, 
                        `uuidMorphs`, 
                        `nullableUlidMorphs`, 
                        `nullableUuidMorphs`

- Spatial Types: `geography`, `geometry`

- Relationship Types:   `foreignId`, 
                        `foreignIdFor`, 
                        `foreignUlid`, 
                        `foreignUuid`, 
                        `morphs`, 
                        `nullableMorphs`

- Specialty Types:  `enum`, 
                    `set`, 
                    `macAddress`, 
                    `ipAddress`, 
                    `rememberToken`, 
                    `vector`

---

### 14.3 *常用欄位型別語法範例*

```php
$table->bigIncrements('id'); // 自動遞增 UNSIGNED BIGINT 主鍵
$table->bigInteger('votes'); // BIGINT 欄位

$table->binary('photo'); // BLOB 欄位
$table->binary('data', length: 16); // VARBINARY(16)
$table->binary('data', length: 16, fixed: true); // BINARY(16)

$table->boolean('confirmed'); // BOOLEAN 欄位

$table->char('name', length: 100); // CHAR 欄位

$table->dateTimeTz('created_at', precision: 0); // DATETIME(含時區)
$table->dateTime('created_at', precision: 0); // DATETIME
$table->date('created_at'); // DATE

$table->decimal('amount', total: 8, places: 2); // DECIMAL

$table->double('amount'); // DOUBLE

$table->enum('difficulty', ['easy', 'hard']); // ENUM

$table->float('amount', precision: 53); // FLOAT

$table->foreignId('user_id'); // UNSIGNED BIGINT
$table->foreignIdFor(User::class); // 根據 model key type
$table->foreignUlid('user_id'); // ULID
$table->foreignUuid('user_id'); // UUID

$table->geography('coordinates', subtype: 'point', srid: 4326); // GEOGRAPHY
$table->geometry('positions', subtype: 'point', srid: 0); // GEOMETRY

$table->id(); // bigIncrements 的別名

$table->increments('id'); // 自動遞增 UNSIGNED INTEGER 主鍵
$table->integer('votes'); // INTEGER
$table->ipAddress('visitor'); // VARCHAR/INET

$table->json('options'); // JSON
$table->jsonb('options'); // JSONB

$table->longText('description'); // LONGTEXT
$table->longText('data')->charset('binary'); // LONGBLOB

$table->macAddress('device'); // MAC address
$table->mediumIncrements('id'); // UNSIGNED MEDIUMINT 主鍵
$table->mediumInteger('votes'); // MEDIUMINT
$table->mediumText('description'); // MEDIUMTEXT
$table->mediumText('data')->charset('binary'); // MEDIUMBLOB
$table->morphs('taggable'); // 多型關聯欄位

$table->nullableMorphs('taggable'); // nullable 多型關聯
$table->nullableUlidMorphs('taggable'); // nullable ulid 多型
$table->nullableUuidMorphs('taggable'); // nullable uuid 多型

$table->rememberToken(); // remember_token 欄位

$table->set('flavors', ['strawberry', 'vanilla']); // SET

$table->smallIncrements('id'); // UNSIGNED SMALLINT 主鍵
$table->smallInteger('votes'); // SMALLINT
$table->softDeletesTz('deleted_at', precision: 0); // TIMESTAMP(含時區) for soft delete
$table->softDeletes('deleted_at', precision: 0); // TIMESTAMP for soft delete

$table->string('name', length: 100); // VARCHAR

$table->text('description'); // TEXT
$table->text('data')->charset('binary'); // BLOB

$table->timeTz('sunrise', precision: 0); // TIME(含時區)
$table->time('sunrise', precision: 0); // TIME
$table->timestampTz('added_at', precision: 0); // TIMESTAMP(含時區)
$table->timestamp('added_at', precision: 0); // TIMESTAMP
$table->timestampsTz(precision: 0); // created_at/updated_at 含時區
$table->timestamps(precision: 0); // created_at/updated_at

$table->tinyIncrements('id'); // UNSIGNED TINYINT 主鍵
$table->tinyInteger('votes'); // TINYINT
$table->tinyText('notes'); // TINYTEXT
$table->tinyText('data')->charset('binary'); // TINYBLOB

$table->unsignedBigInteger('votes'); // UNSIGNED BIGINT
$table->unsignedInteger('votes'); // UNSIGNED INTEGER
$table->unsignedMediumInteger('votes'); // UNSIGNED MEDIUMINT
$table->unsignedSmallInteger('votes'); // UNSIGNED SMALLINT
$table->unsignedTinyInteger('votes'); // UNSIGNED TINYINT

$table->ulidMorphs('taggable'); // ulid 多型
$table->uuidMorphs('taggable'); // uuid 多型
$table->ulid('id'); // ULID
$table->uuid('id'); // UUID

$table->vector('embedding', dimensions: 100); // 向量

$table->year('birth_year'); // YEAR
```

---

### 14.4 *欄位修飾子（Modifiers）*

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('email')->nullable(); // 允許 NULL
});
```
- 常用修飾子：
```php
        ->after('column')、 // 將欄位放置在指定欄位之後
        ->autoIncrement()、 // 設定欄位為自動遞增
        ->charset()、 // 設定欄位的字元編碼
        ->collation()、 // 設定欄位的排序規則
        ->comment()、 // 為欄位添加註解
        ->default()、 // 設定欄位的預設值
        ->first()、 // 將欄位放置在資料表的第一個位置
        ->from()、 // 設定欄位的起始值（通常用於自動遞增）
        ->invisible()、 // 設定欄位為不可見（僅適用於某些資料庫）
        ->nullable()、 // 允許欄位值為 NULL
        ->storedAs()、 // 設定欄位為儲存的計算欄位
        ->unsigned()、 // 設定欄位為無符號（僅存儲正數）
        ->useCurrent()、 // 設定欄位的預設值為當前時間
        ->useCurrentOnUpdate()、 // 設定欄位在更新時自動設為當前時間
        ->virtualAs()、 // 設定欄位為虛擬的計算欄位
        ->generatedAs()、 // 設定欄位為生成的欄位（通常用於序列或計算）
        ->always()。 // 配合 generatedAs，表示欄位值總是由生成規則計算
```
---

### 14.5 *default expressions（預設值表達式）*

- *default expressions*（預設值表達式） 是指`在資料表欄位中使用 SQL 表達式 來設定欄位的預設值，而不是直接使用靜態值`（例如字串或數字）。

```php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->json('movies')->default(new Expression('(JSON_ARRAY())'));
            $table->timestamps();
        });
    }
};
```
- `json('movies')`：
  - 定義一個 *JSON 型別的欄位* ，名稱為 *movies。*
- `default(new Expression('(JSON_ARRAY())'))`：
  - 使用 SQL 表達式 (JSON_ARRAY()) 作為*欄位的預設值*。
  - `JSON_ARRAY()` 是資料庫的*內建函式*，用於*生成一個空的 JSON 陣列（[]）*。

- **使用場景**
  - *動態生成預設值*：
    - 使用 `SQL 表達式` 可以生成更複雜的預設值，例如`空的 JSON 陣列`或其他計算結果。
    - **範例**：
      - 預設值為`空的 JSON 陣列`：[]
      - 預設值為 `JSON 物件`：{"key": "value"}
  - *適合 JSON 型別欄位*：
    - 在資料庫中，**JSON 型別欄位**通常需要使用 `SQL 表達式` 來生成**預設值**。

- **SQL 表達式的作用**
  - *靜態值 vs 表達式*：
    - `靜態值`：直接設定`固定的值`，例如 `default('example')`。
    - `表達式`：使用 SQL 函式或計算來生成值，例如 `default(new Expression('(JSON_ARRAY())'))`。
 - *`JSON_ARRAY()` 的作用*：
   - 生成一個空的 JSON 陣列（[]），作為欄位的預設值。

---

### 14.6 *欄位順序*

```php
$table->after('password', function (Blueprint $table) {
    $table->string('address_line1');
    $table->string('address_line2');
    $table->string('city');
});
```
- 只支援 `MariaDB/MySQL`。

---

### 14.7 *修改欄位型別與屬性*

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('name', 50)->change();
});
```
- **修改欄位**時，所有`要保留的修飾子`都要明確寫出。

```php
Schema::table('users', function (Blueprint $table) {
    $table->integer('votes')->unsigned()->default(1)->comment('my comment')->change();
});
```

---

- `change` **不會改變索引**，需用 `index modifiers` 顯式加/移除索引。
- `index modifier`s 是指在 Laravel 中，**透過明確的方式（顯式）來新增或移除索引**，而不是依賴其他方法（例如 change）自動處理索引。

- **change 方法的限制**
  - `change` 方法用於*修改欄位的結構*（例如型別、長度等）。
  - *不會自動處理索引*：
    - 如果欄位原本有索引，使用 `change` 修改欄位時，索引不會被自動移除或重新建立。
    - 必須使用 `index modifiers` 來顯式地新增或移除索引。
    
- **`index modifiers` 的作用**
  - *新增索引*：
    - 使用 `unique()`、`index()` 等方法，為欄位顯式新增索引。
  - *移除索引*：
    - 使用 `dropUnique()`、`dropIndex()` 等方法，顯式移除欄位的索引。

```php
$table->string('email')->unique()->change(); // 新增唯一索引
```

```php
$table->string('email')->unique(false)->change(); // 移除唯一索引
// $table->string('email')->dropUnique()->change();
```

```php
// Add an index...
$table->bigIncrements('id')->primary()->change();
// Drop an index...
$table->char('postal_code', 10)->unique(false)->change();
```

---

### 14.8**重新命名欄位*

```php
Schema::table('users', function (Blueprint $table) {
    $table->renameColumn('from', 'to');
});
```

---

### 14.9 *刪除欄位*

```php
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('votes');
});

Schema::table('users', function (Blueprint $table) {
    $table->dropColumn(['votes', 'avatar', 'location']);
});
```

---

### 14.10 *常用 drop 指令別名*

```php
$table->dropMorphs('morphable'); // 刪除 morphable_id 和 morphable_type
$table->dropRememberToken(); // 刪除 remember_token
$table->dropSoftDeletes(); // 刪除 deleted_at
$table->dropSoftDeletesTz(); // dropSoftDeletes() 別名
$table->dropTimestamps(); // 刪除 created_at 和 updated_at
$table->dropTimestampsTz(); // dropTimestamps() 別名 
```
---

## 15. **Indexes 索引操作**

### 15.1 *建立索引*

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::table('users', function (Blueprint $table) {
    $table->string('email')->unique(); // 直接在欄位後加 unique
});

// 或者分開建立
$table->unique('email');

// 複合索引（多欄位）
$table->index(['account_id', 'created_at']);

// 自訂索引名稱
$table->unique('email', 'unique_email');
```
- Laravel 會**自動產生**索引名稱，也可自訂。

---

### 15.2 *可用索引型別*

```php
$table->primary('id'); // 主鍵
$table->primary(['id', 'parent_id']); // 複合主鍵
$table->unique('email'); // 唯一索引
$table->index('state'); // 一般索引
$table->fullText('body'); // 全文索引（MariaDB/MySQL/PostgreSQL）
$table->fullText('body')->language('english'); // 指定語言全文索引（PostgreSQL）
$table->spatialIndex('location'); // 空間索引（除 SQLite）
```

---

### 15.3 *重新命名索引*

```php
$table->renameIndex('from', 'to');
```

---

### 15.4 *刪除索引*

```php
$table->dropPrimary('users_id_primary'); // 刪除主鍵
$table->dropUnique('users_email_unique'); // 刪除唯一索引
$table->dropIndex('geo_state_index'); // 刪除一般索引
$table->dropFullText('posts_body_fulltext'); // 刪除全文索引
$table->dropSpatialIndex('geo_location_spatialindex'); // 刪除空間索引
```

```php
Schema::table('geo', function (Blueprint $table) {
    $table->dropIndex(['state']); // 會自動產生 conventional 名稱
    // $table->dropIndex(['state']); 的作用是移除 state 欄位上的索引，而 「會自動產生慣例（conventional）名稱」 的意思是，Laravel 會根據欄位名稱自動推斷索引的名稱（如果你沒有手動指定索引名稱）。
    // Laravel 會根據欄位名稱推斷索引的名稱（例如 geo_state_index），並移除該索引。
    // 如果索引名稱是慣例生成的，你不需要手動指定索引名稱，Laravel 會自動匹配
    // 索引名稱的格式通常是：table_name_column_name_index
});
```

---

## 16. **Foreign Key Constraints 外鍵約束**

### 16.1 *建立外鍵*

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::table('posts', function (Blueprint $table) {
    $table->unsignedBigInteger('user_id'); 
    // 新增一個無符號的 BigInteger 欄位，名稱為 user_id，用於存儲關聯的使用者 ID
    
    $table->foreign('user_id') // 設定 user_id 欄位為外鍵
          ->references('id') // 外鍵參考 users 資料表的 id 欄位
          ->on('users'); // 指定外鍵關聯的資料表為 users
});
```

---

- 可用 `foreignId` + `constrained` 快速建立：

```php
Schema::table('posts', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained();
});
```
- `foreignId` 會建立 **UNSIGNED BIGINT** 欄位
- `constrained` 會**自動推測參照表與欄位**。

---

- 若表名不符慣例，可**手動指定**：

```php
Schema::table('posts', function (Blueprint $table) {
    $table->foreignId('user_id') 
    // 新增一個名為 user_id 的外鍵欄位，型別為無符號 BigInteger
          ->constrained(table: 'users', indexName: 'posts_user_id'); 
          // 設定外鍵關聯到 users 資料表，並指定索引名稱為 posts_user_id
});
```

---

### 16.2 *onDelete/onUpdate 行為*

```php
$table->foreignId('user_id') // 新增一個名為 user_id 的外鍵欄位，型別為無符號 BigInteger
      ->constrained() // 自動關聯到名為 users 的資料表（根據 Laravel 的命名慣例）
      ->onUpdate('cascade') // 當 users 表的 id 欄位更新時，user_id 欄位也會自動更新
      ->onDelete('cascade'); // 當 users 表的 id 欄位被刪除時，user_id 欄位的相關記錄也會自動刪除
```
- 也可用更簡潔的語法：
```php
$table->cascadeOnUpdate(); // 更新時連動
$table->restrictOnUpdate(); // 更新時限制
$table->nullOnUpdate(); // 更新時設為 null
$table->noActionOnUpdate(); // 更新時不動作
$table->cascadeOnDelete(); // 刪除時連動
$table->restrictOnDelete(); // 刪除時限制
$table->nullOnDelete(); // 刪除時設為 null
$table->noActionOnDelete(); // 刪除時不動作
```
- **注意**：所有欄位修飾子要在 `constrained` 前呼叫。
- `onUpdate` 和 `onDelete` 是**外鍵行為規則**，必須在 `constrained()` 方法*之後*使用。
- **其他修飾子**（例如 `unsigned()、nullable()、default()` 等）通常需要在 `constrained()` 方法*之前*使用，因為它們是用來**定義欄位的屬性**。

```php
$table->foreignId('user_id')
      ->nullable()
      ->constrained();
```

---

### 16.3 *刪除外鍵*

```php
$table->dropForeign('posts_user_id_foreign');
$table->dropForeign(['user_id']);
```
- 可**直接傳欄位陣列**，Laravel 會自動產生 `constraint` 名稱。

---

### 16.4 *切換外鍵約束狀態*

```php
Schema::enableForeignKeyConstraints(); // 啟用外鍵約束，讓資料庫開始檢查外鍵的完整性
Schema::disableForeignKeyConstraints(); // 停用外鍵約束，暫時不檢查外鍵的完整性
Schema::withoutForeignKeyConstraints(function () {
    // Constraints disabled within this closure...
    // 在此閉包內，外鍵約束被暫時停用
    // 可以執行不受外鍵約束影響的操作，例如刪除或修改資料表
});
```
- `SQLite` **預設**關閉外鍵，需在 `config/database.php` 開啟。

- **關閉外鍵約束** 確實`會暫時失去關聯式資料庫的完整性檢查功能`，但這些方法的設計是為了解決某些特殊情況，而不是常規使用。

- **為什麼需要關閉外鍵約束？**(特殊情況)

  - *刪除或修改資料表結構*
    - 當需要刪除或修改資料表結構時，外鍵約束可能會阻止操作。
    - 例如：如果 `posts` 表有外鍵依賴於 `users` 表，直接刪除 `users` 表會失敗。
    - 暫時關閉外鍵約束`可以允許刪除或修改資料表`。

  - *批量操作資料*
    - 在`批量插入`或`刪除大量資料`時，外鍵約束的檢查可能`會降低性能`。
    - 暫時關閉外鍵約束可以加快操作速度，然後再啟用外鍵約束。

  - *初始化資料庫*
    - 在執行 `Migration` 或 `Seeder` 時，可能需要先建立資料表，再插入資料。
    - 如果外鍵約束阻止操作，可以暫時關閉外鍵約束。

- **關閉外鍵約束的風險**

  - *資料不一致*
    - 關閉外鍵約束後，`可能插入或刪除不符合外鍵約束的資料`，導致資料庫的`完整性受損`。

  - *僅適合臨時操作*
    - 關閉外鍵約束應僅用於特殊情況（例如`刪除資料表`或`批量操作`），不應長時間停用。

---

## 17. **Migration Events**

- `Illuminate\Database\Events\MigrationsStarted`：**一批** migrations *即將執行*
- `Illuminate\Database\Events\MigrationsEnded`：**一批** migrations *執行完畢*

- `Illuminate\Database\Events\MigrationStarted`：**單一** migration *即將執行*
- `Illuminate\Database\Events\MigrationEnded`：**單一** migration *執行完畢*

- `Illuminate\Database\Events\NoPendingMigrations`：*沒有待執行* migration

- `Illuminate\Database\Events\SchemaDumped`：*schema dump 完成*

- `Illuminate\Database\Events\SchemaLoaded`：*schema dump 載入完成*

--- 