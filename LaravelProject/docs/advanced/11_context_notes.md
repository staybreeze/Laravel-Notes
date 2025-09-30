# *Laravel Context 筆記*

## 1. **簡介**（Introduction）

Context 讓你能在 *請求、任務、指令* 間，捕捉、取得、共享資訊，並 *自動附加於 log* ，方便追蹤分散式系統的執行流程。

<!-- 
context 通常是用物件或資料結構在程式內部傳遞資訊，
讓不同元件或函式都能存取同一份狀態或參數。 
-->

<!-- 
全域 context

所有流程都能存取、共用
例如：config 設定檔、session 使用者狀態
流程／請求 context

只在某次請求、某個任務、某個 transaction 有效
例如：Laravel 的 Context 機制、$request 物件、Queue Context
-->

<!-- 
當前請求：
指的是「一次 HTTP 請求」的處理過程，
從瀏覽器送出請求，到 Laravel 回傳結果，
這期間的資料（如 $request、Context）只在這次請求有效，
請求結束後就會被清除，不會留到下一次請求。

開始：瀏覽器發送 HTTP 請求到 Laravel（例如點擊網頁連結）
上下文：$request 物件、Context 資料只在這次請求有效
結束：Laravel 回傳結果（網頁、API 回應），請求結束，資料被清除
 -->

```php
// middleware
public function handle($request, Closure $next)
{
    Context::add('trace_id', uniqid());
    return $next($request);
}

// controller
public function index()
{
    $traceId = Context::get('trace_id'); // 只在這次請求有效
}
```

<!-- 
當前任務：
指的是「一次 queue job 的執行過程」，
例如你 dispatch 一個任務到 queue，
這個任務執行時的 Context 只在這個 job 有效，
job 執行完畢後資料就消失。

開始：dispatch 一個 job 到 queue（例如：ProcessPodcast::dispatch($podcast)）
上下文：Context 資料在 job 執行期間有效
結束：job 執行完畢，資料消失
 -->

```php
// dispatch job
// 其實 Laravel 的 Context 機制會自動把當前上下文資料「複製」到 job 裡，
// 只要你在 dispatch job 前用 Context::add()，
// Laravel 會把這些 context 資料一起傳給 job，
// job 執行時就能用 Context::get() 取得。
Context::add('trace_id', uniqid());
ProcessPodcast::dispatch($podcast);

// job
class ProcessPodcast implements ShouldQueue
{
    public function handle()
    {
        $traceId = Context::get('trace_id'); // 只在這個 job 執行期間有效
    }
}
```

<!-- 
當前交易（transaction）：
指的是「一次資料庫交易」的範圍，
例如你用 DB transaction 包住多個查詢，
這些查詢共享同一個交易狀態，
交易結束後（commit/rollback）資料就不再共享。 

開始：啟動資料庫交易（DB::beginTransaction() 或 DB::transaction()）
上下文：所有查詢都在同一個交易裡，共享 commit/rollback 狀態
結束：交易 commit 或 rollback，交易範圍結束，資料不再共享
 -->

```php
DB::transaction(function () {
    // 這裡的所有查詢都在同一個交易上下文
    User::create([...]);
    Order::create([...]);
    // 交易結束後，這個上下文消失
});
```

```php
// Laravel 官方 Context 機制（推薦做法）
// middleware 新增 context 資訊
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

public function handle($request, Closure $next)
{
    // 在 Context 裡存一個值（如 trace_id）
    Context::add('trace_id', Str::uuid()->toString());
    return $next($request);
}

---

// controller 取 context 資訊
use Illuminate\Support\Facades\Context;

public function index()
{
    // 從 Context 取出 trace_id
    $traceId = Context::get('trace_id');
    // 使用 traceId 做紀錄或其他用途
}

// middleware 和 controller 都能存取同一個 Context 資訊，
// 這就是 Laravel 官方的上下文共享與管理機制。

```

---

## 2. **運作原理**（How it Works）

- 透過 Context facade 新增資料時，__log 記錄會自動附加 context metadata__（例如`使用者 ID、請求 ID` 等），這樣每一筆 log 都會帶有額外的背景資訊，方便追蹤和分析。

- 當你把 context 設定好後，這些 context 也會 __自動傳遞到 queue job__，
  也就是說，當 job 被分派到佇列並在背景執行時，
  `job 會自動還原原本的 context`，
  讓 log 或其他需要 context 的操作都能正確取得這些背景資料，
  __不會因為非同步或跨請求而遺失 context 資訊__。

---

*範例：middleware 新增 context*

```php
// Context::add() 會導致 Log::info() 自動附加資料的原因，是因為 Laravel 的日誌系統與 Context 整合在一起。當您使用 Context::add() 新增資料後，這些資料會被存儲為全域的 context metadata，並且在記錄日誌時，這些資料會自動附加到日誌的上下文（context）中。

// Context::add() 的作用：

// Context::add() 是用來新增全域的 context 資料。
// 這些資料會存儲在 Laravel 的 Context 系統中，並在請求的生命週期內有效。

Context::add('url', $request->url());
Context::add('trace_id', Str::uuid()->toString());
// url 和 trace_id 被存儲為全域的 context 資料。

```

---

*log 範例*

```php
Log::info('User authenticated.', ['auth_id' => Auth::id()]);
// log 會包含 context 的 url, trace_id
// [YYYY-MM-DD HH:MM:SS] local.INFO: User authenticated. {"auth_id":123,"url":"http://example.com","trace_id":"a1b2c3d4-e5f6-7890-1234-56789abcdef0"}

// Laravel 的日誌系統會：
// 將訊息 'User authenticated.' 和上下文資料 ['auth_id' => Auth::id()] 傳遞給日誌處理器。
// 自動將全域的 context 資料（例如 url 和 trace_id）合併到上下文中。
// 最終記錄到日誌檔案中。
```

---

*queue job 範例*

```php
// middleware 新增 context
Context::add('url', $request->url());
Context::add('trace_id', Str::uuid()->toString());
// controller dispatch job
ProcessPodcast::dispatch($podcast);
// job 內 log 也會帶 context
```

---

## 3. **捕捉與操作 Context**（Capturing Context）

### 3.1 *新增/取得/條件新增*

```php
// 新增一個鍵值對到 Context
Context::add('key', 'value'); 
// 將 'key' 對應的值設為 'value'，如果 'key' 已存在，則覆蓋原值。

// 新增多個鍵值對到 Context
Context::add(['first_key' => 'value', 'second_key' => 'value']); 
// 將 'first_key' 和 'second_key' 對應的值分別設為 'value'。

// 僅當鍵 'key' 不存在時新增
Context::addIf('key', 'second'); 
// 如果 'key' 尚未存在於 Context 中，則新增 'key' 並設為 'second'。

// 取得指定鍵的值
Context::get('key'); 
// 回傳 'key' 對應的值，如果 'key' 不存在，則回傳 null。
```

---


### 3.2 *遞增/遞減*

```php
// 將指定鍵的值遞增 1
Context::increment('records_added'); 
// 如果 'records_added' 不存在，則初始化為 1。

// 將指定鍵的值遞增指定數值（例如 5）
Context::increment('records_added', 5); 
// 如果 'records_added' 不存在，則初始化為 5。

// 將指定鍵的值遞減 1
Context::decrement('records_added'); 
// 如果 'records_added' 不存在，則初始化為 -1。

// 將指定鍵的值遞減指定數值（例如 5）
Context::decrement('records_added', 5); 
// 如果 'records_added' 不存在，則初始化為 -5。
```

---

### 3.3 *條件新增*（`when`）

```php
// 根據條件執行不同的操作
Context::when(
    Auth::user()->isAdmin(), // 條件：檢查使用者是否為管理員
    fn ($context) => $context->add('permissions', Auth::user()->permissions), 
    // 如果條件為 true，執行此回呼，將使用者的權限新增到 Context。

    fn ($context) => $context->add('permissions', []),
    // 如果條件為 false，執行此回呼，將空的權限陣列新增到 Context。
);
```

---

### 3.4 *scope*（`區塊暫時 context`）

`Context::scope()` 是 Laravel 的 Context 系統中 __用來建立暫時性 Context 區塊__ 的方法。
它的主要功能是 __允許您在特定區塊內暫時覆蓋或新增 Context 資料__，並在區塊執行結束後`自動還原`到之前的 Context 狀態。


---

- **`Context::scope()` 的主要用途**
  - *暫時覆蓋 Context 資料*：
    - 在區塊內可以 __覆蓋現有的 Context 資料__（`公開或隱藏`），而不影響全域的 Context。
    - 區塊執行結束後，Context 會自動還原到之前的狀態。

  - *新增區塊專屬的 Context 資料*：
    - 可以在區塊內新增`公開或隱藏`的 Context 資料，這些資料僅在區塊內有效。

  - *安全操作 Context*：
    - 適合用於需要 `臨時修改` Context 的場景，例如 `記錄特定操作的上下文資訊`，而不影響其他部分的 Context。

  - *方便日誌記錄與追蹤*：
    - 在區塊內新增特定的 Context 資料，並將其附加到日誌中，方便追蹤特定操作的上下文。

```php
// 新增公開 Context 資料
Context::add('trace_id', 'abc-999');
// 公開 Context 現在是：['trace_id' => 'abc-999']

// 新增隱藏 Context 資料
Context::addHidden('user_id', 123);
// 隱藏 Context 現在是：['user_id' => 123]

// 使用 Context::scope() 建立暫時的 Context 區塊
Context::scope(
    function () {
        // 在 scope 區塊內，新增公開 Context 資料
        Context::add('action', 'adding_friend');
        // 公開 Context 現在是：
        // ['trace_id' => 'abc-999', 'user_name' => 'taylor_otwell', 'action' => 'adding_friend']

        // 取得隱藏 Context 中的 user_id
        $userId = Context::getHidden('user_id');
        // 此時隱藏 Context 是：
        // ['user_id' => 987]（因為在 scope 中被覆蓋）

        // 記錄日誌，會包含當前的公開 Context 資料
        Log::debug("Adding user [{$userId}] to friends list.");
        // 日誌輸出：
        // [YYYY-MM-DD HH:MM:SS] local.DEBUG: Adding user [987] to friends list. 
        // {"trace_id":"abc-999","user_name":"taylor_otwell","action":"adding_friend"}
    },
    data: ['user_name' => 'taylor_otwell'], // 在 scope 中新增的公開 Context 資料
    // 公開 Context 在 scope 中變為：
    // ['trace_id' => 'abc-999', 'user_name' => 'taylor_otwell']

    hidden: ['user_id' => 987] // 在 scope 中覆蓋的隱藏 Context 資料
    // 隱藏 Context 在 scope 中變為：
    // ['user_id' => 987]
);

// 離開 scope 區塊後，Context 自動還原
// 公開 Context 還原為：['trace_id' => 'abc-999']
// 隱藏 Context 還原為：['user_id' => 123]
```

---

## 4. **Stack 操作**（`Stacks`）

Context 的 Stack 操作主要用於 *管理和操作堆疊資料（Stack）*，適合用於需要`累積、追蹤或檢查`多個值的場景。

<!-- Context 的 stack 概念，就是在「同一個 key」下累積多個值，
     像 breadcrumbs、queries、secrets 都是 key，
     你可以用 push 把資料一個一個加進去，
     然後用 get 取出全部、用 pop 取出最後一個（LIFO），
     或用 stackContains 檢查 stack 裡有沒有某個值。

     這些操作讓你可以追蹤、累積、檢查同一類型的資料，
     很適合用在像「麵包屑」、「查詢紀錄」、「暫存資料」這種場景。 -->

<!-- 這些 Context 的 stack 方法會自動把資料累積成 stack（堆疊）結構，
     不像一般 context 只儲存單一值，
     你可以 push 多個值、pop 最後一個值，
     並且一次取得全部 stack 內容，
     方便管理和追蹤多筆相關資料。 -->

```php
Context::push('breadcrumbs', 'first_value'); // 將 'first_value' 加入 breadcrumbs 堆疊
Context::push('breadcrumbs', 'second_value', 'third_value'); // 再加入 'second_value' 和 'third_value'
Context::get('breadcrumbs'); // 取得 breadcrumbs 堆疊內容：['first_value', 'second_value', 'third_value']

// 監聽 query 並 push stack
DB::listen(function ($event) {
    // 每次有 SQL 查詢事件時，把查詢時間和 SQL 語句加入 queries 堆疊
    Context::push('queries', [$event->time, $event->sql]);
});

// 判斷 stack 內容
Context::stackContains('breadcrumbs', 'first_value'); 
// 檢查 breadcrumbs 堆疊裡是否有 'first_value'

Context::hiddenStackContains('secrets', 'first_value'); 
// 檢查 secrets 隱藏堆疊裡是否有 'first_value'

Context::stackContains('breadcrumbs', fn($v) => Str::startsWith($v, 'query_')); 
// 用條件函式檢查 breadcrumbs 堆疊裡是否有以 'query_' 開頭的值
```
<!-- Context 的 stack（堆疊）概念和一般資料結構的 stack 一樣，
     如果你用 Context::pop('breadcrumbs') 取資料，
     會從最後一個（也就是最「後面」）開始取出，
     這叫做「先進後出」（LIFO, Last-In-First-Out）。 -->

---

## 5. **取得/判斷/移除 Context**（`Retrieving/Determining/Removing`）

### 5.1 *取得*

```php
// 取得指定鍵的值
Context::get('key'); 
// 回傳 'key' 對應的值，如果 'key' 不存在，則回傳 null。

// 取得指定鍵的值（僅限這些鍵）
Context::only(['first_key', 'second_key']); 
// 回傳一個陣列，包含 'first_key' 和 'second_key' 的值，如果鍵不存在，則不包含該鍵。

// 取得所有鍵的值，但排除指定的鍵
Context::except(['first_key']); 
// 回傳一個陣列，包含所有鍵的值，但排除 'first_key'。

// 取出並移除指定鍵的值
Context::pull('key'); 
// 回傳 'key' 對應的值，並從 Context 中移除該鍵。

// 從堆疊中彈出最後一個值
Context::pop('breadcrumbs'); 
// 如果 'breadcrumbs' 是一個堆疊，則移除並回傳堆疊中的最後一個值。

// 取得所有鍵值對
Context::all(); 
// 回傳一個陣列，包含 Context 中的所有鍵值對。
```

---

### 5.2 *判斷存在*

```php
// 檢查指定鍵是否存在
Context::has('key'); 
// 如果 'key' 存在於 Context 中，回傳 true，否則回傳 false。

// 檢查指定鍵是否不存在
Context::missing('key'); 
// 如果 'key' 不存在於 Context 中，回傳 true，否則回傳 false。
```

---

### 5.3 *移除*

```php
// 移除指定的鍵
Context::forget('first_key'); 
// 從 Context 中移除 'first_key'，如果該鍵不存在則不執行任何操作。

// 移除多個鍵
Context::forget(['first_key', 'second_key']); 
// 從 Context 中移除 'first_key' 和 'second_key'，如果鍵不存在則不執行任何操作。
```

---

## 6. **Hidden Context**（`隱藏資料`）

提供一個隱藏的上下文儲存機制，主要用於 *在應用程式中傳遞敏感或不公開的資料*，並確保這些資料不會被意外暴露或記錄到日誌中。

- *不會寫入 log*，也不會被一般 `get/only/all` 取出
- 用於`跨請求/任務傳遞`，但不公開的資料

---

- *與 Session 的比較與區別*：

- __相似之處__

  - *資料儲存與取用*
   - 像 `Session` 一樣，這種`隱藏資料`的機制允許你儲存資料並 __在不同的地方取用__。

  - *跨請求傳遞資料*
    - `隱藏資料`可以用於 __跨請求或任務傳遞資料__，這與 `Session` 的用途類似。

  - *臨時性資料*
    - `隱藏資料`和 `Session` 都適合 __儲存臨時性資料__，這些資料通常不需要永久儲存。

- __不同之處__

  - *資料的可見性*
    - `Session` 是 __全域性__ 的，通常可以被應用程式的任何部分存取。
    - `隱藏資料` 是 __更隱密__ 的，通常只在 __特定的上下文中__ 可用，並且不會被記錄到日誌或公開。

  - *用途的側重點*
    - `Session` 通常用於儲存與 __使用者相關__ 的狀態資料（例如`登入狀態、購物車`內容）。
    - `隱藏資料` 更適合用於 __敏感資料的短期傳遞__，例如 `API 金鑰、驗證 Token 或內部追蹤資訊`。

  - *存續時間*
    - `Session` 的存續時間通常與使用者的會話相關，可能會持續數分鐘到數小時，甚至更長。
    - `隱藏資料` 的存續時間通常更短，可能只在 __單次請求或任務中有效__。

  - *儲存位置*
    - `Session` 通常儲存在 __伺服器端__（例如`檔案、資料庫、記憶體`）或 __用戶端__（例如 `Cookie`）。
    - `隱藏資料` 通常儲存在 __應用程式的執行上下文中__，並不會持久化。

  - *安全性*
    - `Session` 雖然也可以儲存敏感資料，但 __需要額外的保護措施__（例如`加密`）。
    - `隱藏資料` 的設計目的是避免敏感資料被意外暴露或記錄，__安全性更高__。

```php
// 新增一個隱藏的鍵值對
Context::addHidden('key', 'value'); 
// 將 'key' 對應的值設為 'value'，該資料不會被公開或記錄到日誌中。

// 取得指定鍵的隱藏資料
Context::getHidden('key'); 
// 回傳 'key' 對應的隱藏值，如果不存在則回傳 null。

// 將值推入隱藏的堆疊
Context::pushHidden('stack', 'value'); 
// 如果 'stack' 是一個隱藏的堆疊，則將 'value' 推入該堆疊。

// 取出並移除指定鍵的隱藏資料
Context::pullHidden('key'); 
// 回傳 'key' 對應的隱藏值，並從隱藏資料中移除該鍵。

// 從隱藏的堆疊中彈出最後一個值
Context::popHidden('stack'); 
// 如果 'stack' 是一個隱藏的堆疊，則移除並回傳堆疊中的最後一個值。

// 取得指定鍵的隱藏資料（僅限這些鍵）
Context::onlyHidden(['key']); 
// 回傳一個陣列，包含指定鍵的隱藏資料。

// 取得隱藏資料，但排除指定的鍵
Context::exceptHidden(['key']); 
// 回傳一個陣列，包含所有隱藏資料，但排除指定的鍵。

// 取得所有隱藏資料
Context::allHidden(); 
// 回傳一個陣列，包含所有隱藏的鍵值對。

// 檢查是否存在指定的隱藏鍵
Context::hasHidden('key'); 
// 如果 'key' 存在於隱藏資料中，回傳 true，否則回傳 false。

// 檢查指定的隱藏鍵是否不存在
Context::missingHidden('key'); 
// 如果 'key' 不存在於隱藏資料中，回傳 true，否則回傳 false。

// 移除指定的隱藏鍵
Context::forgetHidden('key'); 
// 從隱藏資料中移除 'key'，如果該鍵不存在則不執行任何操作。
```

---

## 7. **Context 事件**（Events）

### 7.1 *dehydrating*（`序列化前`）

可於 `AppServiceProvider` boot 註冊 callback，於 `job dispatch` 時，__將 context 資料序列化__：

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;
use Illuminate\Log\Context\Repository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 應用程式啟動時執行的邏輯
     */
    public function boot()
    {
        // 註冊 Context 的 dehydrating 事件
        // 當 Context 資料即將被序列化時（例如在 Job dispatch 時），觸發此事件
        Context::dehydrating(function (Repository $context) {
            // 將應用程式的語系（locale）加入到隱藏的 Context 中
            $context->addHidden('locale', Config::get('app.locale'));
            // 這樣可以確保在序列化時，語系資料被安全地傳遞到後續的處理中
        });
    }
}
```

---

### 7.2 *hydrated*（`還原時`）

`job` 執行時 __context 還原__，可於 `boot` 註冊 `hydrated callback`：

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;
use Illuminate\Log\Context\Repository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 應用程式啟動時執行的邏輯
     */
    public function boot()
    {
        // 註冊 Context 的 hydrated 事件
        // 當 Context 資料被還原時（例如在 Job 執行時），觸發此事件
        Context::hydrated(function (Repository $context) {
            // 檢查 Context 中是否有隱藏的 'locale' 資料
            if ($context->hasHidden('locale')) {
                // 如果存在，將其設置為應用程式的語系
                Config::set('app.locale', $context->getHidden('locale'));
                // 這樣可以確保在還原時，應用程式的語系與序列化前一致
            }
        });
    }
}
``` 