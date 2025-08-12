# *Laravel 資料庫入門與 SQL 查詢 筆記*

---

## **介紹**

- 幾乎所有現代 Web 應用都會與資料庫互動。
- Laravel 提供多種資料庫操作方式：*原生 SQL、Query Builder、Eloquent ORM*。
- 官方支援五種主流資料庫：
  - `MariaDB 10.3+`
  - `MySQL 5.7+`
  - `PostgreSQL 10.0+`
  - `SQLite 3.26.0+`
  - `SQL Server 2017+`
- 另有 `MongoDB`（需安裝 mongodb/laravel-mongodb 套件，由 MongoDB 官方維護）。

---

## 1. **設定與連線**

- 設定檔：`config/database.php`，可定義*多組連線*與*預設連線*。
- 大多數設定來自 `.env` 環境變數。
- 範例：
  ```bash
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=laravel
  DB_USERNAME=root
  DB_PASSWORD=
  ```
- `Laravel Sail（Docker）`預設已設定好。
- 可依需求修改為*本地*或*正式環境*的資料庫。

---

### 1.1 *SQLite 設定*

- `SQLite` 資料庫是一個**檔案**，無需安裝伺服器。

- 建立新資料庫：
  ```bash
  touch database/database.sqlite
  ```

- 設定 `.env`：
  ```bash
  DB_CONNECTION=sqlite
  DB_DATABASE=/絕對/路徑/database.sqlite
  ```
- 預設啟用 `foreign key constraints`。

- 若要關閉：
  ```bash
  DB_FOREIGN_KEYS=false
  ```
- 用 `Laravel Installer` 建立專案並選 `SQLite`，會自動建立檔案並執行 migration。

---

### 1.2 *SQL Server 設定*

- 需安裝 `sqlsrv` 與 `pdo_sqlsrv` PHP 擴充，及 `Microsoft SQL ODBC driver`。
- 設定方式與其他資料庫類似。

---

### 1.3 *使用 URL 配置*

- 可用`單一 URL 字串`設定所有連線資訊，適合 `Heroku、AWS` 等雲端服務。

- 範例：
  ```bash
  DB_URL=mysql://root:password@127.0.0.1/forge?charset=UTF-8
  ```
- URL 格式：
  ```php
  driver://username:password@host:port/database?options
  ```
- 若 `.env` 有 `DB_URL，Laravel` 會自動解析並覆蓋其他設定。

---

### 1.4 *讀寫分離（Read/Write Connections）*

- 可分別指定「**讀取**」與「**寫入**」資料庫主機。
- 設定範例：
  ```php
  'mysql' => [
      'read' => [
          'host' => [
              '192.168.1.1',
              '196.168.1.2',
          ],
      ],
      'write' => [
          'host' => [
              '196.168.1.3',
          ],
      ],
      'sticky' => true,
      'database' => env('DB_DATABASE', 'laravel'),
      'username' => env('DB_USERNAME', 'root'),
      'password' => env('DB_PASSWORD', ''),
      'unix_socket' => env('DB_SOCKET', ''),
      'charset' => env('DB_CHARSET', 'utf8mb4'),
      'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
      'prefix' => '',
      'prefix_indexes' => true,
      'strict' => true,
      'engine' => null,
      'options' => extension_loaded('pdo_mysql') ? array_filter([
          PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
      ]) : [],
  ],
  ```
- 只需在 `read/write` 陣列內放要覆蓋的設定，其他會自動繼承主設定。
- 多個 `host` 會隨機選一個。
- **sticky 設為 true** 時，若本次請求有寫入操作，之後的讀取也會用寫入連線，確保資料一致性。

---

## 2. **執行 SQL 查詢（DB facade）**

- *DB facade* 提供 
                  `select`、
                  `insert`、
                  `update`、
                  `delete`、
                  `statement`、
                  `unprepared `等方法。

---

### 2.1 *select 查詢*

- 用法：`DB::select($sql, $bindings)`
- 第一個參數為 **SQL 字串** ，第二個為**綁定參數陣列**。
- **綁定參數** 可防止 `SQL injection`。
- 回傳值為「陣列」，每個元素為 `stdClass 物件`。

```php
$users = DB::select('select * from users where active = ?', [1]);
foreach ($users as $user) {
    echo $user->name;
}
```

---

#### **Controller 實例**：

```php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class UserController extends Controller
{
    public function index(): View
    {
        $users = DB::select('select * from users where active = ?', [1]);
        return view('user.index', ['users' => $users]);
    }
}
```

---

### 2.2 *select 標量值*

- 若只需取得**單一標量值**，可用 `DB::scalar()`：

```php
$burgers = DB::scalar(
    "select count(case when food = 'burger' then 1 end) as burgers from menu"
);
```

---

### 2.3 *多結果集（stored procedure）*

- 用 `DB::selectResultSets()` 取得**多個結果集**：

```php
[$options, $notifications] = DB::selectResultSets(
    "CALL get_user_options_and_notifications(?)", $request->user()->id
);
```

---

### 2.4 *Named Bindings*

- 可用**命名綁定**（:id）：

```php
$results = DB::select('select * from users where id = :id', ['id' => 1]);
```

---

### 2.5 *insert*

- 用法：`DB::insert($sql, $bindings)`

- 範例：
```php
DB::insert('insert into users (id, name) values (?, ?)', [1, 'Marc']);
```

---

### 2.6 *update*

- 用法：`DB::update($sql, $bindings)`
- 回傳受影響的列數。

- 範例：
```php
$affected = DB::update(
    'update users set votes = 100 where name = ?',
    ['Anita']
);
```

---

### 2.7 *delete*

- 用法：`DB::delete($sql, $bindings)`
- 回傳受影響的列數。

- 範例：
```php
$deleted = DB::delete('delete from users');
```

---

### 2.8 *statement*

- 用於執行**無回傳值的 SQL**（如 drop table）：

```php
DB::statement('drop table users');
```

---

### 2.9 *unprepared*

- 執行**無綁定參數的 SQL**（不建議用於有用戶輸入的情境，易受 `SQL injection` 攻擊）：

```php
DB::unprepared('update users set votes = 100 where name = "Dries"');
```

- 也可用於**建立資料表**等操作：
```php
DB::unprepared('create table a (col varchar(1) null)');
```

---

#### **注意**：

- `statement、unprepared` 可能`觸發隱性 commit`（如 create table），需小心交易一致性。

---

## 3. **多資料庫連線**

- 可用 `DB::connection('connection_name')` *切換連線*。

- 範例：
```php
$users = DB::connection('sqlite')->select(/* ... */);
```
- 取得*原生 PDO 實例*：
```php
$pdo = DB::connection()->getPdo();
```

---

## 4. **查詢監聽與效能監控**

### 4.1 *查詢監聽*

- 可用 `DB::listen` **監聽所有 SQL 查詢**，常用於 `log/debug`。

```php
namespace App\Providers;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        DB::listen(function (QueryExecuted $query) {
            // $query->sql;
            // $query->bindings;
            // $query->time;
            // $query->toRawSql();
        });
    }
}
```

---

### 4.2 *查詢效能監控*

- 可用 `DB::whenQueryingForLongerThan($ms, $closure)` **監控單一請求查詢總時長**。

- 範例：
```php
namespace App\Providers; // 定義命名空間，表示這段程式碼屬於 App\Providers

use Illuminate\Database\Connection; // 引入 Connection 類，用於表示資料庫連線
use Illuminate\Support\Facades\DB; // 引入 DB Facade，用於操作資料庫
use Illuminate\Support\ServiceProvider; // 引入 ServiceProvider 類，用於擴展 Laravel 的功能
use Illuminate\Database\Events\QueryExecuted; // 引入 QueryExecuted 事件，用於表示查詢已執行的事件

class AppServiceProvider extends ServiceProvider // 定義 AppServiceProvider 類，擴展 Laravel 的功能
{
    public function boot(): void // 定義 boot 方法，用於在應用啟動時執行
    {
        // 設定慢查詢監控，當查詢執行時間超過 500 毫秒時觸發回呼函數
        DB::whenQueryingForLongerThan(500, function (Connection $connection, QueryExecuted $event) {
            // Notify development team...
            // 在這裡可以記錄慢查詢或通知開發團隊
        });
    }
}
```

---

## 5. **資料庫交易（Database Transactions）**

### 5.1 *自動交易（transaction 方法）*

- 用 `DB::transaction` **執行一組操作，遇到例外自動 rollback，否則自動 commit**。
- 不需手動處理 commit/rollback。

```php
use Illuminate\Support\Facades\DB; // 引入 DB Facade，用於操作資料庫

DB::transaction(function () { // 開啟資料庫交易，確保所有操作在同一個交易中執行
    DB::update('update users set votes = 1'); // 更新 users 表中的所有記錄，將 votes 欄位設為 1
    DB::delete('delete from posts'); // 刪除 posts 表中的所有記錄
}); // 如果交易中的所有操作成功，則提交；如果有任何錯誤，則回滾
```

---

### 5.2 *處理死結（Deadlocks）*

- `transaction` 方法可接受第二個參數，指定**遇到死結時重試次數**。

```php
DB::transaction(function () { // 開啟資料庫交易，確保所有操作在同一個交易中執行
    DB::update('update users set votes = 1'); // 更新 users 表中的所有記錄，將 votes 欄位設為 1
    DB::delete('delete from posts'); // 刪除 posts 表中的所有記錄
}, 5); // 第二個參數 5 表示最多重試 5 次，如果交易失敗，Laravel 會自動重試
```

---

### 5.3 *手動交易控制*

- 可用 `beginTransaction`/`commit`/`rollBack` 完全手動控制。

```php
DB::beginTransaction();
// ...
DB::rollBack();
// ...
DB::commit();
```
- 這些方法同時適用於 `Query Builder` 與 `Eloquent ORM`。

---

## 6. **資料庫 CLI 與檢查**

### 6.1 *連線 CLI*

- 用 `php artisan db` 連線**預設資料庫** CLI。

- 指定連線：
  ```bash
  php artisan db mysql
  ```

---

### 6.2 *檢查資料庫與資料表*

- 用 `php artisan db:show` **檢查資料庫概況**（大小、型別、連線數、表摘要）。

- **指定連線**：
  ```bash
  php artisan db:show --database=pgsql
  ```

- 顯示 **row counts** 與 **view 詳細資訊**（大型資料庫會較慢）：

`--counts`
  - *作用*：
    - 顯示`每個資料表`的 row count（行數）。
    - Laravel 會執行查詢來計算每個資料表的`行數`，並將結果顯示在輸出中。
  - *注意*：
    - 如果資料庫中有大量資料表或行數，這個操作可能會較慢，因為需要執行 `COUNT(*)` 查詢。

`--views`
  - *作用*：
    - 顯示資料庫中的 `views（檢視表）`的詳細資訊。
    - 檢視表是資料庫中的`虛擬表`，通常是`基於 SQL 查詢的結果`。
  - *注意*：
    - 如果資料庫中有大量檢視表，這個操作可能會較慢，因為需要檢索檢視表的結構和內容。

  ```bash
  php artisan db:show --counts --views
  ```
    
  - *輸出結果*
  +----------------+---------+-------------------+
  | Table Name     | Row Count | View Information |
  +----------------+---------+-------------------+
  | users          | 1500    | -                 |
  | posts          | 5000    | -                 |
  | comments       | 12000   | -                 |
  | user_activity  | -       | SELECT * FROM ... |
  +----------------+---------+-------------------+

- 檢查**單一資料表**：
  ```bash
  php artisan db:table users
  ```

---

### 6.3 *用 Schema 檢查結構*

```php
use Illuminate\Support\Facades\Schema; // 引入 Schema Facade，用於操作資料庫結構

$tables = Schema::getTables(); // 獲取資料庫中所有的資料表名稱，回傳一個陣列

$views = Schema::getViews(); // 獲取資料庫中所有的檢視表名稱，回傳一個陣列

$columns = Schema::getColumns('users'); // 獲取指定資料表（users）的所有欄位資訊，回傳一個陣列，包含欄位名稱、類型等詳細資訊

$indexes = Schema::getIndexes('users'); // 獲取指定資料表（users）的所有索引資訊，回傳一個陣列，包含索引名稱及相關欄位

$foreignKeys = Schema::getForeignKeys('users'); // 獲取指定資料表（users）的所有外鍵資訊，回傳一個陣列，包含外鍵名稱及其約束條件

// 指定連線：
$columns = Schema::connection('sqlite')->getColumns('users'); // 使用指定的資料庫連線（sqlite），獲取 users 表的所有欄位資訊
```

---

## 7. **資料庫監控（db:monitor 與事件）**

### 7.1 *監控連線數*

- 用 `php artisan db:monitor --databases=mysql,pgsql --max=100` **監控多個連線**，超過上限會 `dispatch DatabaseBusy` 事件。
- 建議排程**每分鐘執行一次**。

---

### 7.2 *事件通知範例*

- 監控**超過連線數**時，監聽 `DatabaseBusy` 事件並通知開發團隊：

```php
// Event::listen() 是一種簡化的方式，直接在程式碼中定義監聽邏輯。
use App\Notifications\DatabaseApproachingMaxConnections; // 引入自訂通知類，用於通知資料庫連線接近最大限制
use Illuminate\Database\Events\DatabaseBusy; // 引入 DatabaseBusy 事件，用於監控資料庫繁忙狀態
use Illuminate\Support\Facades\Event; // 引入 Event Facade，用於監聽事件
use Illuminate\Support\Facades\Notification; // 引入 Notification Facade，用於發送通知

public function boot(): void // 定義 boot 方法，當應用啟動時執行
{
    Event::listen(function (DatabaseBusy $event) { // 監聽 DatabaseBusy 事件，當資料庫繁忙時觸發
        Notification::route('mail', 'dev@example.com') // 指定通知的接收者（開發團隊的 Email）
            ->notify(new DatabaseApproachingMaxConnections( // 發送通知
                $event->connectionName, // 傳遞資料庫連線名稱
                $event->connections // 傳遞當前的資料庫連線數量
            ));
    });
}
```

- `Event::listen()`：
  - 適合快速定義監聽邏輯，通常用於*簡單的事件*處理。
  - *不需要額外定義*監聽器類。
- `Listener`：
  - 適合*複雜的事件*處理邏輯，通常用於大型應用。
  - 需要*定義監聽器類*，並在 `EventServiceProvider` 中註冊

---