# Laravel Rate Limiting（速率限制）完整筆記與實作

---

## 1. 理論說明
- Rate Limiting（速率限制）是限制某個動作在一段時間內最多只能執行幾次，防止濫用、暴力攻擊、資源耗盡。
- 常見應用：API 請求、發送簡訊/郵件、登入嘗試、投票、留言等。
- Laravel Rate Limiter 會用快取系統（如 Redis、database、memcached）來記錄次數。
- 適合所有需要「防止短時間內重複操作」的場景。

---

## 2. 設定方式
- 預設用 `config/cache.php` 的 `default` driver。
- 你可以在 `config/cache.php` 設定專用 driver：
    ```php
    'default' => env('CACHE_STORE', 'database'),
    'limiter' => 'redis', // 指定 rate limiter 用 redis
    ```
- 這樣 RateLimiter 會用 redis 來記錄次數，效能更佳。

---

## 3. 常用 API

### 3.1 **attempt**

- 方法說明：
  **ateLimiter::attempt(key, maxAttempts, callback, decaySeconds)**
- 用途：自動判斷是否超過限流，沒超過就執行 callback 並自動加 1。適合「只要沒超過就執行」的場景。
- 特色：自動判斷＋自動加 1，callback 只會在沒超過時執行。
- 實作片段：
- $executed = RateLimiter::attempt('key', 5, function() { ... });

```php
$executed = RateLimiter::attempt(
    'send-message:' . $user->id, // key：這是限流的唯一識別字串，格式通常為「動作名稱:用戶ID」或「動作:IP」
    5, // 幾次
    function() {
        // 這裡放要執行的動作
    }
);
if (! $executed) {
    return 'Too many messages sent!';
}
```

### 3.2 **tooManyAttempts / increment**
- 方法說明：
  **RateLimiter::tooManyAttempts(key, maxAttempts)**
- 用途：判斷這個 key 是否已超過最大次數。適合你要自己控制流程時用。
  **RateLimiter::increment(key)**
- 用途：手動將這個 key 的次數 +1。適合你要自己決定「什麼時候算一次」的場景。
- 差異：tooManyAttempts 只判斷，increment 只加 1，兩者常搭配使用。
- 實作片段：
- if (RateLimiter::tooManyAttempts('key', 5)) { ... } RateLimiter::increment('key');

```php
if (RateLimiter::tooManyAttempts('send-message:' . $user->id, 5)) { // key：這裡的 key 代表「這個用戶發送訊息」的限流次數
    return 'Too many attempts!';
}
RateLimiter::increment('send-message:' . $user->id); // key：同上，記錄這個用戶的發送次數。這一行的意思是「這個用戶發送訊息的次數 +1」。通常用在你要自己控制「什麼時候算一次」的場景，例如只在發送成功時才加 1。與 attempt 不同，attempt 會自動判斷＋自動加 1，increment 則是手動加 1。你可以把 key 想成打卡機，increment 就是「這個人打了一次卡」。
// 執行動作...
```

### 3.3 **remaining**
- 方法說明：
  **RateLimiter::remaining(key, maxAttempts)**
- 用途：取得這個 key 還剩下幾次可用。適合顯示剩餘次數或條件判斷。
- 實作片段：
- if (RateLimiter::remaining('key', 5)) { ... }

```php
// 例如：
// 假設最大允許 5 次，已經執行 3 次，remaining 會回傳 2，if 條件成立，可以執行動作。
// 如果已經執行 5 次，remaining 會回傳 0，if 條件不成立，不會執行動作。
// 只要 remaining > 0（quota 還沒用完），就可以執行。
// 不是「低於 5 次就不能執行」，而是「還沒達到 5 次都可以執行」。
//
// | 已執行次數 | remaining 回傳 | if 條件 | 可以執行嗎？ |
// |------------|---------------|---------|--------------|
// | 0          | 5             | 成立    | 可以         |
// | 1          | 4             | 成立    | 可以         |
// | 2          | 3             | 成立    | 可以         |
// | 3          | 2             | 成立    | 可以         |
// | 4          | 1             | 成立    | 可以         |
// | 5          | 0             | 不成立  | 不行         |
//
// 白話：你有 5 張票，只要還有票（剩 1~5 張），都可以進場。剩 0 張時就不能進場。
if (RateLimiter::remaining('send-message:' . $user->id, 5)) { // key：同上，判斷這個用戶在這個視窗內還有沒有剩餘次數（例如每分鐘最多 5 次）
    RateLimiter::increment('send-message:' . $user->id); // 如果還有 quota，就手動加 1，記錄這次行為
    // 執行動作...（例如發送訊息）
    // 這樣寫的好處是你可以完全掌控「什麼時候算一次」，例如只在動作成功時才加 1
} else {
    // 如果 quota 用完，這裡可以回傳錯誤訊息或提示用戶稍後再試
}
```

### 3.4 **availableIn**
- 方法說明：
  **RateLimiter::availableIn(key)**
- 用途：取得這個 key 距離下次可用還要幾秒。適合回應用戶「請幾秒後再試」。
- 實作片段：
- seconds = RateLimiter::availableIn('key');

```php
if (RateLimiter::tooManyAttempts('send-message:' . $user->id, 5)) { // key：同上
    $seconds = RateLimiter::availableIn('send-message:' . $user->id);
    return 'You may try again in ' . $seconds . ' seconds.';
}
```

### 3.5 **clear**
- 方法說明：
  **RateLimiter::clear(key)**
- 用途：重置這個 key 的次數（如登入成功時重置失敗次數）。
- 實作片段：
- RateLimiter::clear('key');

```php
RateLimiter::clear('send-message:' . $user->id); // key：同上，重置這個用戶的發送訊息次數
```

### 3.6 **hit**
- 方法說明：
  **RateLimiter::hit(key, decaySeconds)**
- 用途：手動將 key 次數 +1，並指定這次計數的視窗秒數（比 increment 多 decaySeconds 參數，increment 只能用預設視窗）。
- 適合需要自訂每次計數的有效期（如每次操作都重設視窗、或不同動作有不同視窗長度）的場景。

- 參數說明：
  key：唯一識別字串（如 'send-message:用戶ID'）
  decaySeconds：這次計數的有效秒數（例如 60 代表 1 分鐘內最多幾次）

- hit 與 increment 差異：
  increment 只能用預設視窗（通常在 RateLimiter 註冊時設定），無法自訂每次的 decay 秒數。
  hit 可以每次呼叫時指定 decaySeconds，彈性更高。

- hit 的回傳值：
  回傳目前這個 key 的總計數（int）。

- 常見用法：
  在自訂 Middleware 內，針對不同 API 或用戶動態調整視窗長度。
  需要「每次操作都重設視窗」的特殊限流需求。

```php
RateLimiter::hit('send-message:' . $user->id, 60); // 這次計數的視窗為 60 秒

// 例如：每次用戶發送訊息時，將這個 key 的次數 +1，並讓這次計數在 60 秒後自動失效。
// 如果你需要完全自訂每次的視窗長度，建議用 hit 而不是 increment。
```

### 3.7 **throttle Middleware**（路由限流中介層）

- 方法說明：
  throttle 是 Laravel 內建的路由 Middleware，用來快速對 API 或路由進行限流。
  用法簡單，無需自訂 Middleware，適合大多數情境。

- 基本用法：
```php
// routes/api.php
Route::middleware('throttle:60,1')->group(function () {
    // 這裡的所有路由，每分鐘最多 60 次
    Route::get('/user', function () { /* ... */ });
});
```
- `throttle:60,1` 代表「每 1 分鐘最多 60 次」。
- 也可以直接加在單一路由上：
```php
Route::get('/profile', function () { /* ... */ })->middleware('throttle:10,2');
// 代表每 2 分鐘最多 10 次
```

// 進階用法：
- 可以在 **RouteServiceProvider** 註冊自訂規則，然後用名稱套用：
```php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('custom', function ($request) {
    return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
});

// routes/api.php
Route::middleware('throttle:custom')->get('/special', function () { /* ... */ });


// 參數說明：
- `throttle:<maxAttempts>,<decayMinutes>`
  - maxAttempts：最大嘗試次數
  - decayMinutes：重置時間（分鐘）

// 注意事項：
- 預設 key 為用戶 ID 或 IP。
- 可搭配自訂 RateLimiter 規則，彈性極高。
- 超過限制時會自動回傳 429 Too Many Requests。
```

---

## 4. 專案級實作

### 4.1 **發送訊息 API 限流**
```php
// app/Http/Controllers/MessageController.php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class MessageController extends Controller
{
    public function send(Request $request)
    {
        $user = $request->user();
        $executed = RateLimiter::attempt(
            'send-message:' . $user->id, // key：這裡的 key 代表「這個用戶發送訊息」的限流次數，通常格式為「動作:用戶ID」
            5, // 每分鐘最多 5 次
            function() use ($request, $user) {
                // 這裡放實際發送訊息的邏輯
                // Message::create([...]);
            }
        );
        if (! $executed) {
            $seconds = RateLimiter::availableIn('send-message:' . $user->id); // key：同上
            return response()->json([
                'message' => 'Too many messages sent! Try again in ' . $seconds . ' seconds.'
            ], 429);
        }
        return response()->json(['message' => 'Message sent!']);
    }
}
```

### 4.2 **登入防暴力破解**
```php
// app/Http/Controllers/Auth/LoginController.php
if (RateLimiter::tooManyAttempts('login:' . $request->ip(), 5)) { // key：這裡的 key 代表「這個 IP 嘗試登入」的限流次數
    $seconds = RateLimiter::availableIn('login:' . $request->ip()); // key：同上
    return back()->withErrors(['email' => "請稍後 {$seconds} 秒再嘗試登入"]);
}
if ($authSuccess) {
    RateLimiter::clear('login:' . $request->ip()); // key：同上，登入成功時重置次數
} else {
    RateLimiter::increment('login:' . $request->ip()); // key：同上，登入失敗時累加次數。這一行的意思是「這個 IP 嘗試登入的次數 +1」。適合用在只在登入失敗時才加 1，這樣用戶只要成功登入就不會被鎖死。increment 讓你完全掌控什麼時候要加 1。
}
```

### 4.3 **自訂 Middleware 實作 API 限流**

#### 理論說明
- 除了在 Controller 內用 RateLimiter，也可以寫成 Middleware，讓 API 路由自動套用限流規則。
- 適合需要統一管理、重複利用、或多條路由共用同一限流邏輯的場景。

#### 完整 Middleware 程式碼
```php
// app/Http/Middleware/ApiRateLimit.php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class ApiRateLimit
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $key = 'api:' . ($user ? $user->id : $request->ip()); // key：依用戶 ID 或 IP 組成，確保每個用戶或 IP 各自限流
        $maxAttempts = 10; // 每分鐘最多 10 次
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Too many requests. Try again in ' . $seconds . ' seconds.'
            ], 429);
        }
        RateLimiter::hit($key, 60); // 60 秒視窗
        return $next($request);
    }
}
```

#### API 路由註冊範例
```php
// routes/api.php
use App\Http\Middleware\ApiRateLimit;

Route::middleware([ApiRateLimit::class])->group(function() {
    Route::post('/message/send', [MessageController::class, 'send']);
    Route::post('/vote', [VoteController::class, 'store']);
});
```

#### 使用說明
- 將 Middleware 檔案放到 `app/Http/Middleware/ApiRateLimit.php`。
- 在 `app/Http/Kernel.php` 註冊：
    ```php
    protected $routeMiddleware = [
        // ...
        'api.rate' => \App\Http\Middleware\ApiRateLimit::class,
    ];
    ```
- 路由可用 `middleware('api.rate')` 或直接用類別。
- 可根據需求調整 key、maxAttempts、視窗秒數。

### 4.4 **進階限流範例與最佳實踐**

#### 1. **依不同 API 路徑/動作限流（動態 key）**
- 可根據請求路徑、方法、參數等組合 key，讓不同 API 有獨立限流。
```php
// app/Http/Middleware/DynamicApiRateLimit.php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class DynamicApiRateLimit
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $route = $request->route() ? $request->route()->getName() : $request->path();
        $key = 'api:' . ($user ? $user->id : $request->ip()) . ':' . $route; // key：依用戶/路徑組成，讓每個 API 路徑各自限流
        $maxAttempts = 5;
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Too many requests for this API. Try again in ' . $seconds . ' seconds.'
            ], 429);
        }
        RateLimiter::hit($key, 60);
        return $next($request);
    }
}
```

#### 2. **依用戶角色/權限動態調整限流**
- 讓 VIP 用戶、管理員有更寬鬆的限流規則。
```php
// app/Http/Middleware/RoleBasedRateLimit.php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class RoleBasedRateLimit
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $key = 'api:' . ($user ? $user->id : $request->ip()); // key：依用戶角色動態調整限流，VIP/管理員可自訂
        $maxAttempts = 10;
        if ($user && $user->is_vip) {
            $maxAttempts = 100; // VIP 用戶更寬鬆
        } elseif ($user && $user->is_admin) {
            return $next($request); // 管理員不限制
        }
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Too many requests. Try again in ' . $seconds . ' seconds.'
            ], 429);
        }
        RateLimiter::hit($key, 60);
        return $next($request);
    }
}
```

#### 3. **測試用例（Feature Test）**
- 如何測試限流行為。
```php
// tests/Feature/ApiRateLimitTest.php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ApiRateLimitTest extends TestCase
{
    // 測試 API 限流功能
    public function test_api_rate_limit()
    {
        $user = User::factory()->create(); // 建立一個測試用的用戶

        // 連續發送 10 次訊息（假設限流上限是 10 次）
        for ($i = 0; $i < 10; $i++) {
            // 使用 actingAs 模擬登入，發送 POST 請求到 /api/message/send
            $response = $this->actingAs($user)->postJson('/api/message/send', ['content' => 'hi']);
        }

        // 第 11 次發送，應該會超過限流
        $response = $this->actingAs($user)->postJson('/api/message/send', ['content' => 'hi']);

        $response->assertStatus(429); // 斷言回應狀態碼為 429（Too Many Requests，代表被限流）

        $response->assertJsonFragment(['message' => true]); // 斷言回應 JSON 內容有 message 欄位（通常會有錯誤訊息）
    }
}
```

#### 4. 常見陷阱與最佳實踐
- **陷阱**：
  - key 設計不良，導致所有人共用同一限流（如只用 'api' 當 key）。
  - 忘記考慮未登入用戶（建議用 IP 當 key）。
  - hit/attempt/availableIn 用法混淆。
- **最佳實踐**：
  - key 應包含用戶 ID 或 IP、API 路徑/動作。
  - 視窗秒數、次數應根據業務需求調整。
  - 測試限流行為，確保不會誤傷正常用戶。
  - 可結合事件/通知，提醒用戶即將達到限流。

---

## 5. 常見 Q&A
- Q: RateLimiter 會用什麼儲存？
  - A: 預設用 cache driver，可指定 limiter driver（如 redis）。
- Q: 可以限制不同用戶/動作嗎？
  - A: 可以，key 可自訂（如 `send-message:用戶ID`、`login:IP`）。
- Q: 超過限制會自動重置嗎？
  - A: 會，根據 decay rate（秒數）自動重置。
- Q: 如何搭配 Middleware 做 API 限流？
  - A: 用 `throttle` middleware，或自訂 middleware 內用 RateLimiter。

--- 