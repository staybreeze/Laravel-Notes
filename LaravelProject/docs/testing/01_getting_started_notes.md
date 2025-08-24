# 1. *Laravel Testing: Getting Started 筆記*

---

## 1.1 **簡介**

Laravel 內建 `Pest` 與 `PHPUnit` 測試支援，專案**預設**已包含 `phpunit.xml` 設定檔與測試目錄。Laravel 也提供許多方便的測試輔助方法。

- `tests/Feature`：*功能測試*，測試 __多物件互動__ 或 __完整 HTTP 請求__，*會* 啟動 Laravel 應用程式。
- `tests/Unit`：*單元測試*，聚焦 __單一方法__ 或 __小範圍邏輯__ ，*不會* 啟動 Laravel 應用程式。

- 大多數測試建議寫在 `Feature` 目錄，能更有信心驗證整體系統。

- **預設**提供 `ExampleTest.php` 範例。

- 執行測試：

```bash
vendor/bin/pest
vendor/bin/phpunit
php artisan test
```

---

## 1.2 **測試環境**

- 執行測試時，Laravel 會 _自動將環境_ 設為 `testing`，*session* 與 *cache* 皆用 *array driver*（不會持久化），只會 __暫存在記憶體的陣列裡，不會寫入檔案、資料庫或 Redis，程式結束後資料就會消失__。

- 可於 `phpunit.xml` 設定其他 __測試環境變數__ ，修改後請執行 `php artisan config:clear`。
- 可建立 `.env.testing`，測試時會自動取代 `.env`。

- Laravel 支援多種 _儲存方式_（driver），常見有：

  - `file`：儲存到 _本機檔案_（**預設** _session driver_）。
  - `database`：儲存到 _資料庫_。
  <!-- 
  資料庫可以部署在獨立的雲端服務（如 AWS RDS、Azure SQL），也可以直接安裝在自己的伺服器裡。不論是哪種方式，資料庫本質上都是在「伺服器端」，只是伺服器可能是你自己的主機，也可能是雲端平台提供的主機。
  -->
  - `redis`：儲存到 _Redis_。（也支援 __分散式__）
  <!-- 
  Redis 也是一種伺服器端資料庫（記憶體型），
  可以部署在自己的主機、雲端服務（如 AWS ElastiCache）、
  內部網路的專用伺服器，
  本質上和一般資料庫一樣，屬於伺服器端資源。 
  -->

  - `memcached`：儲存到 _Memcached_。
    - Memcached 是一種 __高效能的分散式記憶體快取系統__。(也可支援單台 Memcached 進行記憶體快取，但就不算分散式)
      - __分散式記憶體快取__ 是指：
        - 把資料暫存在 *`多台`伺服器的記憶體裡*，讓多台應用程式可以快速存取、共享這些資料，而不是每次都去查資料庫，可以大幅提升效能和擴充性。
    - 用來*暫存資料*，減少資料庫查詢次數，加速網站或應用程式的回應速度。
    - 常用於`快取 session、cache、查詢結果`等。
  <!-- Memcached 也是伺服器端的快取系統，
  可以部署在自己的主機、雲端服務或內部網路伺服器，
  用來加速資料存取，屬於伺服器端資源。 -->

  - `array`：只存在 _記憶體_，__程式結束即消失__（常用於測試）。
  - `cookie`：*session* 可直接儲存在 cookie（不常用）。

<!-- 
在單一主機上開多個伺服器（或多個進程、執行緒）屬於併發處理（concurrency），
可以同時處理多個請求或任務，
但「並行（parallelism）」通常指多台主機或多核心同時真正執行多個任務。 
-->

---

## 1.3 **建立測試**

- Artisan 指令建立 `Feature` 測試：

```bash
php artisan make:test UserTest
```

---

- 建立 _Unit_ 測試：

```bash
php artisan make:test UserTest --unit
```

---

- _測試 stub_ 可自訂。
  - 是一種 __假物件或假方法__
    - 用來`在測試時取代真實的物件或方法`，讓你可以 __控制回傳結果、模擬特定情境__，不需要真的執行外部依賴（像 API、資料庫等）。
    
- 建立後可用 `Pest` 或 `PHPUnit` 撰寫測試：

```php
test('basic', function () {
    expect(true)->toBeTrue();
});
```

---

- 若自訂 `setUp/tearDown`，請記得呼叫 `parent::setUp()/parent::tearDown()`。

```php
use Tests\TestCase;

class UserTest extends TestCase
{
    protected function setUp(): void
    {   // 一定要先呼叫父類別的 setUp
        parent::setUp(); // 初始化 Laravel 測試環境（資料庫、服務等）

        // 你的自訂初始化邏輯
        // 這裡可以做自訂初始化，例如建立測試資料、設定 mock 等
        // 舉例：建立一個測試用的 User
        $this->user = \App\Models\User::factory()->create([
            'name' => '測試用戶',
        ]);
    }

    protected function tearDown(): void
    {
        // 你的自訂清理邏輯
        // 這裡可以做自訂清理，例如刪除測試檔案、重設狀態等
        // 舉例：刪除測試用的 User
        $this->user->delete();

        // 最後呼叫父類別的 tearDown
        parent::tearDown(); // 清理 Laravel 測試環境，釋放資源
    }

    public function testUserName()
    {
        // 測試 User 的 name 是否正確
        $this->assertEquals('測試用戶', $this->user->name);
    }
}
```

---

## 1.4 **執行測試**

- 執行所有測試：

```bash
./vendor/bin/pest
./vendor/bin/phpunit
php artisan test
```
---

- _Artisan test_ 支援`更詳細報告`與`參數轉遞`：

```bash
php artisan test --testsuite=Feature --stop-on-failure
```

- `--testsuite=Feature`：*只* 執行 Feature 測試。
- `--stop-on-failure`：遇到第一個失敗就停止測試。

---

## 1.5 **平行測試**（Parallel Testing）

- 平行測試是指`同時啟動多個測試進程`，讓多個測試案例可以「_同時_」執行，加快整體測試速度。

- 安裝套件：

```bash
composer require brianium/paratest --dev
```

---

- Laravel 內建支援 __平行測試__，可用 `Artisan` 指令啟動：

```bash
php artisan test --parallel
```

- 每個測試進程會使用 _獨立的資料庫_，避免資料衝突。
- 適合大型專案或測試案例很多時使用，可大幅 _縮短測試時間_。
- 常見參數：
  - `--processes=4`：指定 __同時啟動__ 4 個測試進程。
  - `--stop-on-failure`：遇到失敗即停止所有進程。

---

- **預設** 會 __依 CPU 核心數__ 建立 `process`，可用 `--processes` 指定：

```bash
php artisan test --parallel --processes=4
```

---

- 重新建立 _測試資料庫_：

```bash
php artisan test --parallel --recreate-databases
```
  - `--recreate-databases`：執行平行測試時，__強制重新建立__ 所有測試用資料庫，確保資料乾淨、不受前次測試影響。

---

### 1.5.1 *平行測試 hooks*

- 可用 `ParallelTesting` facade 註冊 _process_ 或 _test case_ 的 `setUp/tearDown`，讓你 __在`平行測試`不同階段執行自訂邏輯__。

- __什麼是 hook？__  
  hook（掛鉤）是一種程式設計概念，指的是在 _特定流程或事件發生時_，`你可以「掛」上自訂的程式碼，讓系統自動執行你的邏輯`。  
  常用於 _初始化、清理、或事件觸發_ 時執行自訂動作。

- __這裡的 hook 作用__  
  在 Laravel 平行測試裡，這些 hooks 讓你可以 _在測試進程、測試案例、資料庫建立_ 等階段，自動執行`初始化、資料準備、清理`等流程，確保每個平行測試環境都正確、獨立。


```php
use Illuminate\Support\Facades\ParallelTesting;
use PHPUnit\Framework\TestCase;

ParallelTesting::setUpProcess(function (int $token) {
    // 每個平行測試進程啟動時執行
});

ParallelTesting::setUpTestCase(function (int $token, TestCase $testCase) {
    // 每個測試案例執行前執行
});

ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
    // 每個測試資料庫建立時執行，例如自動執行 seeder
    Artisan::call('db:seed');
});

ParallelTesting::tearDownTestCase(function (int $token, TestCase $testCase) {
    // 每個測試案例結束後執行
});

ParallelTesting::tearDownProcess(function (int $token) {
    // 每個平行測試進程結束時執行
});
```
---

- 取得目前 `process token`：

```php
$token = ParallelTesting::token();
```
  - `process token` 是每個平行測試進程的 _唯一識別碼_，可用來 _區分_ 不同測試環境或資料庫。

---

## 1.6 **測試覆蓋率**（Coverage）

- 測試覆蓋率是用來檢查你的測試有 _覆蓋到多少程式碼_，幫助你了解哪些邏輯還沒被測試到。

- 需安裝 `Xdebug` 或 `PCOV`。
- 執行時加上 `--coverage`：

```bash
php artisan test --coverage
```
  - 執行後會顯示每個檔案、每行程式碼的 _覆蓋率報告_，方便你補強測試。

---

- 設定*最低*覆蓋率門檻：

```bash
php artisan test --coverage --min=80.3
```
  - `--min=80.3`：要求測試覆蓋率至少達到 80.3%，__低於此門檻會讓測試失敗__。
---

## 1.7 **測試效能分析**（Profiling）

- *顯示最慢的* 10 筆測試：

```bash
php artisan test --profile
``` 
  - `--profile` 參數會 __在測試結束後__，列出 _執行時間最長_ 的 10 筆測試，方便你找出效能瓶頸並優化測試流程。