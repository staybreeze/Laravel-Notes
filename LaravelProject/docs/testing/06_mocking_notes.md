# *Laravel Mocking 測試 筆記*

---

## 1. **簡介**（Introduction）

Laravel 測試時可 _mock_ `事件、job、facade` 等，*避免實際執行副作用*，聚焦 _單元測試_。Laravel 提供便利的 mock 方法，底層以 Mockery 實作。

---

## 2. **物件 Mocking**（Mocking Objects）

### 2.1 *手動綁定 mock 物件*

```php
// 例如 tests/Feature/ServiceTest.php
use App\Service;
use Mockery;
use Mockery\MockInterface;

// 用 instance 方法將 Service 類別注入一個 mock 物件
$this->instance(
    Service::class,
    Mockery::mock(Service::class, function (MockInterface $mock) {
        // 斷言 mock 物件必須呼叫 process 方法
        $mock->expects('process');
    })
);
```
```php
// 假設你有一個 UserService 類別
use App\Services\UserService;
use Mockery;
use Mockery\MockInterface;

// 用 instance 方法將 UserService 類別注入一個 mock 物件
$this->instance(
    UserService::class,
    Mockery::mock(UserService::class, function (MockInterface $mock) {
        // 斷言 mock 物件必須呼叫 findUser 方法
        $mock->expects('findUser');
    })
);

// 之後程式裡只要用 app(UserService::class) 或依賴注入取得 UserService，
// 都會拿到這個 mock 物件，而不是原本的 UserService 實例。
```

- 這樣可以在測試時用 `mock` 取代*真實 Service 類別*，並驗證 `process` 方法有被呼叫。

- 拿到 __mock 物件__，你可以：

  - 控制`方法回傳值`（例如指定 `findUser` 回傳什麼資料）
  - `斷言方法`有被呼叫（驗證程式流程）
  - `不會執行`原本物件的真實邏輯（例如不真的查資料庫）

- 跟 __原本物件__ 的差別是：

  - `mock` 物件只用來測試，不會影響真實資料或外部服務
  - 可以模擬各種情境（成功、失敗、例外等）
  - 測試更快、更安全、更可控

<!-- 
也可以用 $this->instance() 來註冊 controller 的 mock 物件，
但在 Laravel 測試裡，通常只 mock service、repository 等，
controller 比較少直接 mock，
因為 controller 主要負責接收請求和呼叫 service，
mock controller 用途較少，但技術上是可以的。
-->

---

### 2.2 *mock/partialMock/spy 方法*

更簡便的 `mock` 寫法：

```php
use App\Service;
use Mockery\MockInterface;

// 使用 mock 方法建立 Service 類別的 mock 物件
$mock = $this->mock(Service::class, function (MockInterface $mock) {
    // 斷言 mock 物件必須呼叫 process 方法
    $mock->expects('process');
});
```
```php
// 假設你有一個 UserService 類別
use App\Services\UserService;

// 你可以用 UserService::class 來取得類別名稱字串
$service = app(UserService::class); // 取得 UserService 實例

// 在測試時，可以 mock 這個類別
$this->mock(UserService::class, function ($mock) {
    $mock->expects('findUser')->andReturn(['id' => 1, 'name' => 'Alice']);
});

```

- `$this->instance(...)`  

  - 是 __直接將 mock 物件注入到 Laravel 的服務容器__，之後程式裡只要解析 `Service::class`，都會拿到這個 mock，不用每個測試都重複建立 mock，適合需要 _全域取代 Service_ 的情境。

  - 當你在程式裡用 `app(Service::class)`、`resolve(Service::class)` 或`依賴注入`（__constructor type-hint__）取得`Service 實例`時，Laravel 會 __自動從服務容器裡拿出你注入的 mock 物件，而不是原本的 Service 類別__。

  - 這樣可以__讓所有用到 Service 的地方都用 mock，方便測試和驗證行為__。

  - 不用擔心，這種 mock 注入只會在 _測試執行期間_ 有效，__正式環境或一般程式運作時__，服務容器還是會解析原本的 Service 類別， 不會拿到測試用的 mock 資料。

---

- `$this->mock(...)`  

  是 __建立一個 mock 物件並回傳給你__，
  你可以在測試裡直接操作這個 mock，
  不會自動注入到服務容器，適合只在 _單一測試_ 中使用。
  需要的地方都要自己建立 mock，較適合局部測試。

__總結__

- `instance`：_全域注入_，影響整個測試流程。
- `mock`：_只建立物件_，影響範圍較小。

---

只 mock `部分方法`：

```php
$mock = $this->partialMock(Service::class, function (MockInterface $mock) {
    $mock->expects('process');
});

use App\Services\UserService;
use Mockery\MockInterface;

// 建立 UserService 的部分 mock，只 mock findUser 方法，其他方法保留原本行為
$mock = $this->partialMock(UserService::class, function (MockInterface $mock) {
    $mock->expects('findUser')->andReturn(['id' => 1, 'name' => 'Alice']);
});

// 這樣你可以測試 findUser 方法的行為，其他 UserService 方法還是會執行原本邏輯
```
- 使用 `partialMock` 建立 Service 類別的「__部分 mock__」物件，
- 只有你 _指定的方法_（如 `process`）會被 mock，其他方法會保留原本的行為。

---

`Spy` 物件（可 _驗證互動_）：

```php
$spy = $this->spy(Service::class); // 建立 Service 類別的 spy 物件（可記錄方法呼叫）
$spy->process(); // 呼叫 process 方法（可選，測試時可能由其他程式呼叫）

$spy->shouldHaveReceived('process'); // 斷言 process 方法有被呼叫過
```

---

- `Spy 物件`可以 __記錄方法呼叫情形__，方便你 _驗證某個方法是否真的被執行過_。

```php
// 假設你有一個 UserService 類別
use App\Services\UserService;

// 建立 spy 物件
$spy = $this->spy(UserService::class);

// 呼叫 findUser 方法（可能在程式其他地方被呼叫）
$spy->findUser(1);

// 驗證 findUser 方法有被呼叫過
$spy->shouldHaveReceived('findUser')->with(1);
// 結果會是：  
// 如果 `$spy->findUser(1);` 這行有執行，
// `$spy->shouldHaveReceived('findUser')->with(1);` 這個斷言會通過（測試成功）。

// 如果 `findUser(1)` 沒有被呼叫，或參數不是 1，
// 這個斷言會失敗（測試失敗），並顯示錯誤訊息。

// 說明：
// spy 物件會記錄所有方法呼叫情形，
// 你可以用 shouldHaveReceived 來斷言某個方法是否真的被呼叫過，
// 並且可以指定參數（例如 with(1)），確保程式流程正確。
```

---

__Spy 物件__ 常用的方法：

- `shouldHaveReceived('method')`：斷言指定方法 _有被呼叫過_。
- `shouldHaveReceived('method')->with(args...)`：斷言方法 _有被呼叫_，且`參數正確`。

- `shouldNotHaveReceived('method')`：斷言指定方法 __沒有__ 被呼叫過。

- `shouldHaveReceivedOnce('method')`：斷言方法 _只被呼叫一次_。
- `shouldHaveReceivedTimes('method', $times)`：斷言方法被呼叫 _指定次數_。

---

## 3. **Facade Mocking**（Mocking Facades）

`facade`（含 _real-time facade_）可直接 `mock`，方便驗證 `controller` 等呼叫。

<!-- 
當你在 Laravel 測試中使用 facade（例如 Cache、Bus、Mail），
可以直接用 expects() 來 mock 這些 facade 的方法，
不需要額外建立 mock 物件，
這讓你能方便驗證 controller 或 service 是否有呼叫到特定 facade 方法。

Real-time facade 是 Laravel 的一種特殊語法，
可以讓你把任何類別即時轉換成 facade，
這樣也能直接用 expects() 來 mock，
讓測試更靈活，
不論是內建 facade 或 real-time facade，都可以用同樣方式 mock 方法呼叫。
-->

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_uses_cache()
    {
        // mock Cache facade 的 get 方法，預期會用 'key' 參數並回傳 'value'
        Cache::expects('get')
            ->with('key')
            ->andReturn('value');

        // 執行 /users 路由，驗證 controller 是否有呼叫 Cache::get('key')
        $response = $this->get('/users');

        // 這裡可以加入更多斷言，例如檢查回應內容
        $response->assertStatus(200);
    }
}
// Cache::expects('get') 就是 mock Cache facade 的 get 方法。
// 當 controller 執行 users 路由時，如果有呼叫 Cache::get('key')，mock 就會驗證通過。
// 這種 mock 方式只適用於 Laravel 的 facade（包含 real-time facade），
// 讓你可以直接在測試裡驗證 controller 是否有呼叫到 facade 的方法。
```

- 不建議 mock `Request/Config` facade，請直接用 `HTTP 測試方法` 或 `Config::set`。

  - _Request facade_ 代表 __HTTP 請求__，意思是它 __用來取得目前的請求資料__（如`參數、header、body` 等）。

    但在測試時，直接 `mock Request facade` 可能會讓測試結果不準確，因為 __你無法完整模擬真實的 HTTP 請求流程__。
    所以建議用 `$this->get()`、`$this->post()` 這類測試方法，__直接模擬一個完整的 HTTP 請求__，
    這樣 `controller、middleware` 等都會依照真實流程執行，測試更可靠。

  - _Config facade_ 代表 __設定值__，意思是它 __用來讀取或設定 Laravel 的 config 內容__。

    直接 `mock Config facade` 可能會讓測試變得複雜且難維護，
    建議用 `Config::set('key', 'value')` __直接設定你要的測試值__，
    這樣程式裡用 `config('key')` 取得的就是你設定的值，
    測試更簡單、直覺，也不會影響其他測試。

__總結__  

這兩個 `facade` 都是 Laravel 重要的系統元件，
`直接 mock` 它們容易造成 _測試不準確或難維護_，
所以建議 __用更貼近`真實流程`的方式來測試__。

```php

// 錯誤範例：mock Request/Config facade（不建議這樣做）
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config;

// mock Request facade（不建議）
Request::expects('input')
    ->with('name')
    ->andReturn('Taylor');

// mock Config facade（不建議）
Config::expects('get')
    ->with('app.name')
    ->andReturn('MyApp');

// 正確做法：
//   Request 請用 $this->get()、$this->post() 等 HTTP 測試方法模擬請求
//   Config 請用 Config::set('app.name', 'MyApp') 直接設定測試用的 config
```

---

### 3.1 *Facade Spy*

```php
Cache::spy(); // 建立 Cache facade 的 spy 物件，記錄方法呼叫情形

$response = $this->get('/'); // 執行 GET 請求

$response->assertStatus(200); // 斷言回應狀態碼為 200

Cache::shouldHaveReceived('put')->with('name', 'Taylor', 10); 
// 斷言 Cache::put 方法有被呼叫過，且參數為 'name', 'Taylor', 10
```
- 這樣可以驗證 `controller` 或其他程式 __有正確呼叫__ `Cache::put` 方法，並 __記錄呼叫參數__。
- 對於 Laravel 的 `facade`（像 `Cache`），可以直接用 `Cache::shouldHaveReceived('put')` 這種語法，不需要像一般物件那樣 `$spy->shouldHaveReceived()`。

- 這是 Laravel 對 facade 提供的 _特殊測試語法_，讓你可以 _直接驗證 facade 方法_ 呼叫情形，__不用自己取得 spy 物件再呼叫方法__。

---

## 4. **時間操作**（Interacting With Time）

Laravel 測試可用 `travel/freezeTime `等方法操作 `now()` 或 `Carbon::now()`。

```php
$this->travel(5)->milliseconds(); // 時間往前移動 5 毫秒
$this->travel(5)->seconds();      // 時間往前移動 5 秒
$this->travel(5)->minutes();      // 時間往前移動 5 分鐘
$this->travel(5)->hours();        // 時間往前移動 5 小時
$this->travel(5)->days();         // 時間往前移動 5 天
$this->travel(5)->weeks();        // 時間往前移動 5 週
$this->travel(5)->years();        // 時間往前移動 5 年
$this->travel(-5)->hours();       // 時間往後移動 5 小時
$this->travelTo(now()->subHours(6)); // 直接移動到指定時間（例如 6 小時前）
$this->travelBack();              // 時間回復到原本狀態
```
- 這些方法可用於測試 __時間相關邏輯__（如 _排程、過期、有效期限_ 等）。

---

可傳 `closure`，__時間凍結__ 於區塊內：

```php
$this->travel(5)->days(function () {
    // 在這個區塊內，時間會往前移動 5 天
    // 可測試時間相關邏輯
});

$this->travelTo(now()->subDays(10), function () {
    // 在這個區塊內，時間會被設定為 10 天前
    // 區塊結束後時間會自動回復
});
```

---

_凍結時間_：

```php
use Illuminate\Support\Carbon;

$this->freezeTime(function (Carbon $time) {
    // 在這個區塊內，時間會被凍結（不會流動）
    // $time 代表目前凍結的時間，可以用來測試時間相關邏輯
});

$this->freezeSecond(function (Carbon $time) {
    // 在這個區塊內，秒數會被凍結（不會流動）
    // 適合測試秒級的時間判斷
});

// 假設你有一個檢查是否過期的方法
use Illuminate\Support\Carbon;

function isExpired(Carbon $expiresAt)
{
    return now()->greaterThan($expiresAt);
}

// 測試範例：凍結時間在 2025-08-10
$this->freezeTime(function (Carbon $time) {
    $expiresAt = $time->copy()->subDay(); // 設定過期時間為昨天
    $this->assertTrue(isExpired($expiresAt)); // 應該已過期

    $expiresAt = $time->copy()->addDay(); // 設定過期時間為明天
    $this->assertFalse(isExpired($expiresAt)); // 應該未過期
});

```

- `travel` 是「_移動_」時間：  
  你可以把「_現在_」__往前或往後移動，或直接跳到某個時間點__，
  但 __時間還是會流動__（例如程式執行時 `now()` 會隨著時間改變）。

- `freezeTime` 是「_凍結_」時間：  
  讓「_現在_」__完全停住，程式執行期間 `now()` 都是同一個時間__，
  不會隨著程式執行而改變，適合測試「時間不流動」的情境。

---

__舉例__

- 用 `travel(5)->days()` 可以測試 5 天後的行為，但如果你要確保整個測試期間時間**都不變**，就用 `freezeTime()`。
- `freezeTime()` 適合測試「__同一個時刻__」的判斷，例如 `token 產生、過期判斷`等。

__總結__

- `travel`：_移動到某個時間點_，時間還會流動。
- `freezeTime`：_時間完全停住_

```php
// 假設你有一個檢查是否過期的方法
use Illuminate\Support\Carbon;

function isExpired(Carbon $expiresAt)
{
    return now()->greaterThan($expiresAt);
}

// 用 travel 移動時間（時間會流動）
$this->travel(1)->days(function () {
    $expiresAt = now()->subDay(); // 設定過期時間為昨天
    // 這時 now() 是「明天」，但如果程式執行很久，now() 會隨著時間流動

    // 因為 `$this->travel(1)->days()` 會把「現在」時間往前移動 1 天。

    // 假設今天是 8/11，執行 `$this->travel(1)->days()` 後，
    // `now()` 會變成 8/12（也就是「明天」）。

    // 所以 `now()->subDay()` 就是 8/11（昨天），
    // 而 `now()` 是 8/12（明天）。
    // 這樣就可以測試「明天」的行為，
    // 而不是用原本的「今天」時間。
    $this->assertTrue(isExpired($expiresAt));
});

// 用 freezeTime 凍結時間（時間不會流動）\
$this->freezeTime(function (Carbon $time) {
    $expiresAt = $time->subDay(); // 設定過期時間為昨天
    // 這時 now() 永遠是凍結的時間，不會隨程式執行而改變
    $this->assertTrue(isExpired($expiresAt));
});
```

---

- 適用於測試 _時間敏感邏輯_（如 `討論串一週未動自動鎖定`）：

```php
use App\Models\Thread;

test('forum threads lock after one week of inactivity', function () {
    $thread = Thread::factory()->create(); // 建立一個討論串
    $this->travel(1)->week();              // 時間往前移動一週
    expect($thread->isLockedByInactivity())->toBeTrue(); // 斷言討論串已因一週未動而鎖定
});
```
- 這樣可以模擬「_一週後_」的情境，_驗證時間相關的自動鎖定邏輯是否正確_。

- 這是時間敏感邏輯，因為「_討論串是否鎖定_」會根據 __時間的流逝__來 判斷。
- 例如：`如果一週都沒有人回覆或互動，系統就會自動把討論串鎖定`。

- 這種功能必須依賴「_現在距離最後活動時間_」的計算，所以測試時`要模擬時間的變化`，才能驗證邏輯是否正確。

- 這個例子是在測試「__討論串（Thread）一週未動自動鎖定__」的功能。

---

- `Thread::factory()->create();`  
  這行會建立一個 __新的討論串__（Thread），通常代表 __剛發表的主題__。

- `$this->travel(1)->week();`  
  這行會把「_現在_」時間 __往前移動一週(下週)__，
  模擬這個討論串 __已經存在一週，期間沒有任何活動__。

- `expect($thread->isLockedByInactivity())->toBeTrue();`  
  這行斷言討論串的 `isLockedByInactivity()` 方法會回傳 `true`，
  代表討論串 __因為一週沒活動而自動鎖定__。

---

__用途__  

這種測試 _可以驗證你的時間敏感邏輯是否正確_，
例如討論串、文章、帳號等，過了一段時間會自動變更狀態（如鎖定、過期、提醒等）。

__補充__  

`Thread` 是你 _專案裡的模型_，代表 _討論區的主題或串_，
`isLockedByInactivity()` 是你自己定義的方法，用來判斷 _討論串是否因為長時間沒活動而被鎖定_。

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Thread extends Model
{
    protected $fillable = ['title', 'last_activity_at', 'locked'];

    // 判斷討論串是否因為長時間沒活動而被鎖定
    public function isLockedByInactivity(): bool
    {
        // 假設 locked 欄位代表是否鎖定
        // last_activity_at 是最後活動時間
        // 超過一週未動就鎖定
        return $this->locked || Carbon::parse($this->last_activity_at)->lt(now()->subWeek());
    }
}

// 測試範例
use App\Models\Thread;

test('forum threads lock after one week of inactivity', function () {
    $thread = Thread::factory()->create([
        'last_activity_at' => now(), // 剛建立，最後活動是現在
        'locked' => false,
    ]);
    $this->travel(1)->week(); // 時間往前移動一週
    expect($thread->isLockedByInactivity())->toBeTrue(); // 應該自動鎖定
});
```