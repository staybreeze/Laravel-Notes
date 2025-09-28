# *Laravel Concurrency 併發 筆記*

## 1. **簡介**（Introduction）

有時需`同時執行多個互不相依的耗時任務`，Laravel 提供 Concurrency facade，讓你能簡單地併發執行 closure，顯著提升效能。

---

## 2. **運作原理**（How it Works）

Concurrency 會先把 __closure 序列化（轉成可儲存的資料）__，
__分派給隱藏的__ `Artisan CLI 指令`，
__在獨立的 PHP process 裡反序列化（還原）並執行 closure__，
執行完後再把`結果序列化`，回傳給主程序。

<!-- 反序列化（deserialize）是指把「序列化後的資料」還原成原本的物件或結構，
     例如：把字串或檔案內容轉回 PHP 物件，讓程式可以直接操作。 -->

1. 主程序序列化 closure
      ↓
2. 分派給隱藏的 Artisan CLI 指令
      ↓
3. CLI 指令在獨立 PHP process 反序列化 closure
      ↓
4. 執行 closure
      ↓
5. 將結果序列化
      ↓
6. 回傳結果給主程序

<!-- 
這裡的 closure 指的是「匿名函式」或「可攜帶環境的函式物件」，
在 PHP 裡通常是用 function () { ... } 這種語法。 
-->

<!-- 
你可以把要執行的程式邏輯包在 closure 裡，
例如：function () { return DB::table('users')->count(); }
Concurrency 會把這個 closure 序列化、分派到獨立的 PHP process 執行，
讓你可以同時執行多個任務（並行）。 
-->

---

支援三種 driver：
- *process（預設）*：每個 `closure` 啟動一個 PHP `process`
- *fork*：效能更佳，僅限 CLI，需安裝 `spatie/fork`
- *sync*：僅於`測試`用，全部 closure 於主程序依序執行

---

*安裝 fork driver*：

```bash
composer require spatie/fork
```

---

## 3. **執行併發任務**（Running Concurrent Tasks）

用 `Concurrency::run` *同時執行多個 closure*，回傳結果`陣列`：

```php
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;

[$userCount, $orderCount] = Concurrency::run([
    fn () => DB::table('users')->count(),
    fn () => DB::table('orders')->count(),
]);
```

---

*指定 driver*：

```php
$results = Concurrency::driver('fork')->run([...]);
```

---

*更改預設 driver*：

```bash
php artisan config:publish concurrency
```

---

## 4. **延遲併發任務**（Deferring Concurrent Tasks）

若 *只需執行 closure 而不需回傳結果*，可用 `defer`，Laravel 會 __於 HTTP response 傳送後__ 才執行 closure：

```php
use App\Services\Metrics;
use Illuminate\Support\Facades\Concurrency;

Concurrency::defer([
    fn () => Metrics::report('users'),
    fn () => Metrics::report('orders'),
]);
``` 