# *Laravel Concurrency 併發*

## 1. **簡介**（Introduction）

有時需`同時執行多個互不相依的耗時任務`，Laravel 提供 Concurrency facade，讓你能簡單地併發執行 closure，顯著提升效能。

---

## 2. **運作原理**（How it Works）

Concurrency 會`序列化 closure`，分派給隱藏的`Artisan CLI 指令`，於`獨立 PHP process 反序列化並執行 closure`，最後將結果序列化回主程序。

支援三種 driver：
- *process（預設）*：每個 closure 啟動一個 PHP **process**
- *fork*：效能更佳，僅限 CLI，需安裝 **spatie/fork**
- *sync*：僅於**測試**用，全部 closure 於主程序依序執行

安裝 fork driver：
```bash
composer require spatie/fork
```

---

## 3. **執行併發任務**（Running Concurrent Tasks）

用 `Concurrency::run` *同時執行多個 closure*，回傳結果陣列：

```php
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;

[$userCount, $orderCount] = Concurrency::run([
    fn () => DB::table('users')->count(),
    fn () => DB::table('orders')->count(),
]);
```

*指定 driver*：

```php
$results = Concurrency::driver('fork')->run([...]);
```

*更改預設 driver*：

```bash
php artisan config:publish concurrency
```

---

## 4. **延遲併發任務**（Deferring Concurrent Tasks）

若*只需執行 closure 而不需回傳結果*，可用 `defer`，Laravel 會`於 HTTP response 傳送後`才執行 closure：

```php
use App\Services\Metrics;
use Illuminate\Support\Facades\Concurrency;

Concurrency::defer([
    fn () => Metrics::report('users'),
    fn () => Metrics::report('orders'),
]);
``` 
