# *Laravel Context 筆記*

## 1. **簡介**（Introduction）

Context 讓你能在 *請求、任務、指令* 間，捕捉、取得、共享資訊，並 *自動附加於 log* ，方便追蹤分散式系統的執行流程。

---

## 2. **運作原理**（How it Works）

- 透過 *Context facade* 新增資料，log 會自動附加 *context metadata*。
- context 也會自動傳遞到 `queue` `job`，job 執行時 context 會自動還原。

**範例：middleware 新增 context**
```php
// Context::add() 會導致 Log::info() 自動附加資料的原因，是因為 Laravel 的日誌系統與 Context 整合在一起。當您使用 Context::add() 新增資料後，這些資料會被存儲為全域的 context metadata，並且在記錄日誌時，這些資料會自動附加到日誌的上下文（context）中。

// Context::add() 的作用：

// Context::add() 是用來新增全域的 context 資料。
// 這些資料會存儲在 Laravel 的 Context 系統中，並在請求的生命週期內有效。

Context::add('url', $request->url());
Context::add('trace_id', Str::uuid()->toString());
// url 和 trace_id 被存儲為全域的 context 資料。

```

**log 範例**
```php
Log::info('User authenticated.', ['auth_id' => Auth::id()]);
// log 會包含 context 的 url, trace_id
// [YYYY-MM-DD HH:MM:SS] local.INFO: User authenticated. {"auth_id":123,"url":"http://example.com","trace_id":"a1b2c3d4-e5f6-7890-1234-56789abcdef0"}

// Laravel 的日誌系統會：
// 將訊息 'User authenticated.' 和上下文資料 ['auth_id' => Auth::id()] 傳遞給日誌處理器。
// 自動將全域的 context 資料（例如 url 和 trace_id）合併到上下文中。
// 最終記錄到日誌檔案中。
```

**queue job 範例**
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

### 3.3 *條件新增（when）*
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

### 3.4 *scope（區塊暫時 context）*

`Context::scope()` 是 Laravel 的 Context 系統中**用來建立暫時性 Context 區塊**的方法。
它的主要功能是**允許您在特定區塊內暫時覆蓋或新增 Context 資料**，並在區塊執行結束後自動還原到之前的 Context 狀態。

- **Context::scope() 的主要用途**
  - *暫時覆蓋 Context 資料*：
    - 在區塊內可以覆蓋現有的 Context 資料（公開或隱藏），而不影響全域的 Context。
    - 區塊執行結束後，Context 會自動還原到之前的狀態。

  - *新增區塊專屬的 Context 資料*：
    - 可以在區塊內新增公開或隱藏的 Context 資料，這些資料僅在區塊內有效。

  - *安全操作 Context*：
    - 適合用於需要`臨時修改` Context 的場景，例如記錄特定操作的上下文資訊，而不影響其他部分的 Context。

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

## 4. **Stack 操作**（Stacks）

Context 的 Stack 操作主要用於*管理和操作堆疊資料（Stack）*，適合用於需要`累積、追蹤或檢查`多個值的場景。

```php
Context::push('breadcrumbs', 'first_value');
Context::push('breadcrumbs', 'second_value', 'third_value');
Context::get('breadcrumbs'); // ['first_value', 'second_value', 'third_value']

// 監聽 query 並 push stack
DB::listen(function ($event) {
    Context::push('queries', [$event->time, $event->sql]);
});

// 判斷 stack 內容
Context::stackContains('breadcrumbs', 'first_value');
Context::hiddenStackContains('secrets', 'first_value');
Context::stackContains('breadcrumbs', fn($v) => Str::startsWith($v, 'query_'));
```

---

## 5. **取得/判斷/移除 Context**（Retrieving/Determining/Removing）

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

### 5.2 *判斷存在*
```php
// 檢查指定鍵是否存在
Context::has('key'); 
// 如果 'key' 存在於 Context 中，回傳 true，否則回傳 false。

// 檢查指定鍵是否不存在
Context::missing('key'); 
// 如果 'key' 不存在於 Context 中，回傳 true，否則回傳 false。
```

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

## 6. **Hidden Context**（隱藏資料）

提供一個隱藏的上下文儲存機制，主要用於*在應用程式中傳遞敏感或不公開的資料*，並確保這些資料不會被意外暴露或記錄到日誌中。

- *不會寫入 log*，也不會被一般 `get/only/all` 取出
- 用於`跨請求/任務傳遞`，但不公開的資料

- *與 Session 的比較與區別*：

- **相似之處**
  - *資料儲存與取用*
   - 像 `Session` 一樣，這種`隱藏資料`的機制允許你儲存資料並**在不同的地方取用**。

  - *跨請求傳遞資料*
    - `隱藏資料`可以用於**跨請求或任務傳遞資料**，這與 `Session` 的用途類似。

  - *臨時性資料*
    - `隱藏資料`和 `Session` 都適合**儲存臨時性資料**，這些資料通常不需要永久儲存。

- **不同之處**
  - *資料的可見性*
    - `Session` 是**全域性**的，通常可以被應用程式的任何部分存取。
    - `隱藏資料` 是**更隱密**的，通常只在特定的上下文中可用，並且不會被記錄到日誌或公開。

  - *用途的側重點*
    - `Session` 通常用於儲存與**使用者相關**的狀態資料（例如登入狀態、購物車內容）。
    - `隱藏資料` 更適合用於**敏感資料的短期傳遞**，例如 `API 金鑰、驗證 Token 或內部追蹤資訊`。

  - *存續時間*
    - `Session` 的存續時間通常與使用者的會話相關，可能會持續數分鐘到數小時，甚至更長。
    - `隱藏資料` 的存續時間通常更短，可能只在**單次請求或任務中有效**。

  - *儲存位置*
    - `Session` 通常儲存在**伺服器端**（例如檔案、資料庫、記憶體）或**用戶端**（例如 Cookie）。
    - `隱藏資料` 通常儲存在**應用程式的執行上下文中**，並不會持久化。

  - *安全性*
    - `Session` 雖然也可以儲存敏感資料，但**需要額外的保護措施**（例如加密）。
    - `隱藏資料` 的設計目的是避免敏感資料被意外暴露或記錄，**安全性更高**。

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

### 7.1 *dehydrating（序列化前）*

可於 `AppServiceProvider` boot 註冊 callback，於 **job dispatch** 時將 context 資料序列化：

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

### 7.2 *hydrated（還原時）*

`job` 執行時 context 還原，可於 boot 註冊 hydrated callback：

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