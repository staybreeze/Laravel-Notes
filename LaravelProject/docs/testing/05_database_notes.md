# *Laravel 資料庫 測試 筆記*

---

## 1. **簡介**（Introduction）

Laravel 提供多種 *工具* 與 *斷言*，讓資料庫導向的應用測試更簡單。`Model Factory` 與 `Seeder` 也能輕鬆建立測試資料。

---

## 2. **測試後重置資料庫**（Resetting the Database After Each Test）

### 2.1 *RefreshDatabase trait*

使用 `Illuminate\Foundation\Testing\RefreshDatabase` trait，可於**每次測試後**，_自動重置資料庫_（以交易方式回復）：

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

// 使用 RefreshDatabase trait，讓每次測試都重設資料庫，確保資料乾淨
uses(RefreshDatabase::class);

test('basic example', function () {
    $response = $this->get('/'); // 發送 GET 請求到首頁
    // ... 這裡可以加入更多斷言或測試邏輯
});
```

- **注意**：此 trait 只會在 `schema` 已 `up-to-date` 時，以 __交易方式__ 回復，__若 schema 有異動才會 migrate__。

 - 如果`資料庫結構`（schema）已經是 _最新狀態_，`RefreshDatabase` 會用「_交易（transaction）_」方式回復資料，速度快。
   - 在每個測試開始前，開啟一個 _資料庫交易_（transaction）。
     - 在資料庫中，「_交易（transaction）_」`是一種機制，可以把一連串操作包在一起，只有在你「提交（commit）」時才會永久儲存，如果「回滾（rollback）」，所有操作都會被取消，資料回到原本狀態`。
     - 測試時用交易，可以快速還原資料，不會影響正式資料庫內容。

   - 測試結束後，_回滾_（rollback）這個交易，所有資料變更都會消失。
   - 這樣可以確保每次測試資料庫都是乾淨的，不會殘留資料，也不需要重建資料庫。

 - 如果`偵測到 schema 有異動`（例如 migration 有更新），才會 _重新執行 migrate_，確保資料庫結構正確。
 - 這樣可以兼顧測試速度和資料庫正確性。

---

### 2.2 *DatabaseMigrations / DatabaseTruncation*

若需**完全重建**資料表，可用：

```php
use Illuminate\Foundation\Testing\DatabaseMigrations; // 每次測試前都重新 migrate 資料表
use Illuminate\Foundation\Testing\DatabaseTruncation; // 每次測試前都清空（truncate）所有資料表
```

- `DatabaseMigrations`：確保資料表結構是**最新**，適合 migration 有異動時使用。
- `DatabaseTruncation`：確保資料表內容**完全清空**，適合需要乾淨資料的測試。

但速度較**慢**，通常建議優先用 `RefreshDatabase`。

---

## 3. **Model Factory 建立測試資料**（Model Factories）

可用 `Factory` 快速*建立*測試資料：

```php
use App\Models\User;

test('models can be instantiated', function () {
    // 使用 Factory 建立一個 User 實例並存入資料庫
    // 根據 `UserFactory`。  
    // 當你呼叫 `User::factory()` 時，Laravel 會自動使用 `database/factories/UserFactory.php` 這個檔案，  
    // 裡面定義了如何產生 User 模型的假資料。
    $user = User::factory()->create();
    // ... 這裡可以加入更多斷言或測試邏輯
});
```

- 詳細 `Factory` 用法請參考 `laravel_eloquent_factories_notes.md` 文件。

---

## 4. **執行 Seeder**（Running Seeders）

可於測試中呼叫 `seed` 方法執行 seeder：

```php
// 執行 DatabaseSeeder
$this->seed();

// 執行指定 seeder
$this->seed(OrderStatusSeeder::class);

// 執行多個 seeder
$this->seed([
    OrderStatusSeeder::class,
    TransactionStatusSeeder::class,
]);
```

---

可於 base test class 設定 `$seed` 屬性，自動於**每次測試前執行** `DatabaseSeeder`：

`base test class`（基底測試類別）是**所有測試類別的父類別**，  
通常命名為 `TestCase`，放在 `tests/TestCase.php`。  
你可以在這裡定義 __共用__ 設定、方法或屬性，  
讓**所有測試都能繼承並使用**這些功能。

```php

use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use RefreshDatabase;

    // 每次測試前自動執行 DatabaseSeeder
    protected $seed = true;

    // 如需指定 seeder 類別，每次測試前只執行該 seeder
    // protected $seeder = OrderStatusSeeder::class;

    // 其他共用方法或設定...
}
```

---

## 5. **資料庫斷言方法**（Available Assertions）

### 5.1 *assertDatabaseCount*

斷言資料表**有指定筆數**：

```php
$this->assertDatabaseCount('users', 5);
// 斷言資料庫中的 users 資料表有 5 筆資料
```
- **斷言成功**：資料庫中的 `users` 資料表*剛好有 5 筆資料*，測試通過。
- **斷言失敗**：資料表筆數不是 5（例如 4 或 6），測試失敗，會顯示 _錯誤訊息_。
---

### 5.2 *assertDatabaseEmpty*

斷言資料表為**空**：

```php
$this->assertDatabaseEmpty('users');
```

---

### 5.3 *assertDatabaseHas*

斷言`資料表`**有符合條件**的資料：

```php
$this->assertDatabaseHas('users', [
    'email' => 'sally@example.com',
]);
```

---

### 5.4 *assertDatabaseMissing*

斷言資料表**無符合條件**的資料：

```php
$this->assertDatabaseMissing('users', [
    'email' => 'sally@example.com',
]);
```

---

### 5.5 *assertSoftDeleted / assertNotSoftDeleted*

斷言模型已 `soft delete`：

```php
$this->assertSoftDeleted($user);
$this->assertNotSoftDeleted($user);
```

---

### 5.6 *assertModelExists / assertModelMissing*

斷言**模型**`存在/不存在`於資料庫：

```php

use App\Models\User;

// 建立一個 User 實例並存入資料庫
$user = User::factory()->create();

// 斷言資料庫中存在這個 User
$this->assertModelExists($user);

$user->delete(); // 刪除這個 User

// 斷言資料庫中已經找不到這個 User
$this->assertModelMissing($user);
```

---

### 5.7 *expectsDatabaseQueryCount*

斷言測試期間`執行的 SQL 查詢數量`：

```php
$this->expectsDatabaseQueryCount(5);
// 斷言測試過程中執行了 5 次資料庫查詢
// ...測試內容...
``` 