# Laravel Error Handling 錯誤處理完整教學

---

## **目錄**
1. 錯誤處理介紹與基本概念
2. 錯誤處理配置與設定
3. 錯誤與例外的報告（Reporting Exceptions）
4. 全域 Log Context 與 Exception Context
5. report() 輔助函式與去重複（deduplicate）
6. Log Level（例外分級）
7. 忽略例外（dontReport、ShouldntReport、stopIgnoring）
8. 渲染例外（render 方法自訂回應）
9. JSON/HTML 回應與 shouldRenderJsonWhen
10. respond() 全域自訂回應
11. 例外類別內自訂 report/render 方法
12. 節流與速率限制（throttle、Lottery、Limit）
13. HTTP 例外與 abort 輔助函式
14. 自訂 HTTP 錯誤頁面（404、500、fallback、vendor:publish）

---

## 1. **錯誤處理介紹與基本概念**

### *什麼是錯誤處理？*
錯誤處理是應用程式中處理異常情況的機制，就像「安全網」一樣，當程式出現問題時，能夠優雅地處理並提供適當的回應，而不是讓整個應用程式崩潰。

### *Laravel 錯誤處理的特點*
1. **開箱即用**：新專案建立時，錯誤處理已經配置完成
2. **靈活配置**：可以自訂錯誤處理邏輯
3. **環境適應**：開發和生產環境有不同的錯誤顯示策略
4. **記錄完整**：自動記錄錯誤資訊供除錯使用

#### *生活化比喻**
- 錯誤處理就像「醫院急診室」：有預設流程、可自訂、分環境、完整記錄
- 錯誤處理就像「汽車安全系統」：安全氣囊、故障燈、備用系統、維修手冊

---

## 2. **錯誤處理配置與設定**

### *基本配置檔案*
- `bootstrap/app.php`（Laravel 11 新配置）
- `config/app.php`（舊版配置）

### *環境變數設定*
- `.env` 檔案
  - 開發環境：`APP_DEBUG=true`、`APP_ENV=local`
  - 生產環境：`APP_DEBUG=false`、`APP_ENV=production`

### *APP_DEBUG 設定*
- true：顯示詳細錯誤資訊（開發環境）
- false：只顯示基本錯誤訊息（生產環境）

#### **為什麼生產環境要關閉 DEBUG？**
- 安全性：避免暴露敏感資訊（資料庫密碼、檔案路徑等）
- 用戶體驗：不讓用戶看到技術細節
- 效能：減少錯誤處理的開銷
- 專業性：提供統一的錯誤頁面

#### **生活化比喻**
- APP_DEBUG 就像「汽車儀表板」：技師模式 vs 一般駕駛
- 環境配置就像「餐廳服務」：廚房內部 vs 用餐區

---

## 3. **錯誤與例外的報告（Reporting Exceptions）**

### *什麼是 Exception 報告？*
報告（report）就是「記錄」或「上報」錯誤，可以寫進 log，也可以送到外部服務（如 Sentry、Flare）。
Laravel 預設會根據 logging 設定自動記錄所有例外。

#### **進階自訂報告**
你可以在 `bootstrap/app.php` 用 **withExceptions** 的 **report** 方法，針對特定例外自訂報告方式：

```php
// 例：自訂 InvalidOrderException 的報告
use App\Exceptions\InvalidOrderException;
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->report(function (InvalidOrderException $e) {
        // 這裡可以自訂 log、通知、外部上報等
    });
})
```

> 生活化比喻：報告就像「醫院遇到特殊病例時，會通知專科醫師或上報衛生局」。

#### **停止預設 log 行為**
預設自訂 report callback 執行後，Laravel 還是會照 logging 設定記錄一次。
如果你想「只自訂，不要預設 log」，可以：
- 用 ->stop() 或 callback return false

```php
$exceptions->report(function (InvalidOrderException $e) {
    // ...
})->stop();
// 或
$exceptions->report(function (InvalidOrderException $e) {
    return false;
});
```

#### **reportable 例外**
你也可以在 Exception 類別內直接定義 report() 方法，Laravel 會自動呼叫。

---

## 4. **全域 Log Context 與 Exception Context**

### *全域 Log Context*
Laravel 會自動把目前登入用戶的 ID 加到每個 log（如果有登入）。
你也可以用 context() 方法，加入自訂全域 context：

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->context(fn () => [
        'foo' => 'bar',
    ]);
})
```

### *例外類別自訂 context*
有時候某個例外需要額外資訊，可以在 Exception 類別內加 context() 方法：

```php
class InvalidOrderException extends Exception {
    public function context(): array {
        return ['order_id' => $this->orderId];
    }
}
```

> 生活化比喻：context 就像「病歷註記」，每次記錄都可以加上當下的特殊狀態。

---

## 5. **report() 輔助函式與去重複（deduplicate）**

### *report() 輔助函式*
有時你只想「記錄」例外但**不中斷流程**，可以用 report($e)：

```php
public function isValid(string $value): bool {
    try {
        // 驗證邏輯...
    } catch (Throwable $e) {
        report($e); // 只記錄，不中斷
        return false;
    }
}
```

### *去重複（deduplicate）*
如果同一個例外被 report 多次，會產生重複 log。
可用 **dontReportDuplicates()**，只記錄第一次：

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->dontReportDuplicates();
})
```

> 生活化比喻：deduplicate 就像「醫院同一個病人一天只掛一次號」，避免重複記錄。

---

## 6. **Log Level（例外分級）**

### *log level 介紹*
log 有分等級（debug/info/warning/error/critical...），影響訊息嚴重性與記錄管道。
你可以針對特定例外自訂 log level：

```php
use PDOException;
use Psr\Log\LogLevel;
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->level(PDOException::class, LogLevel::CRITICAL); // 針對 PDOException 設定為 critical 等級
})
```

> **註解**
> 這些 log level 並不是 Laravel 自己定義的，而是來自 [PSR-3 Logger Interface 標準](https://www.php-fig.org/psr/psr-3/)。Laravel 內建的 log 系統（Monolog）直接支援這些層級，讓你可以用 `Log::debug()`、`Log::info()` 等方法記錄不同嚴重程度的訊息。

### *Log Level 詳細分級說明*

| 層級         | 何時用？（實務建議）                                                                                 |
|--------------|-----------------------------------------------------------------------------------------------------|
| **debug**    | 開發時追蹤細節、變數內容、流程（**不建議上 production**）                                            |
| **info**     | 記錄正常流程、重要事件（如用戶登入、訂單成立、排程啟動）                                             |
| **notice**   | 較 info 嚴重一點，非預期但不影響運作（如：快取失效、使用過時 API）                                   |
| **warning**  | 潛在問題、需注意但不影響主流程（如：磁碟空間快滿、外部 API 回應慢）                                  |
| **error**    | 功能失敗、用戶操作失敗（如：資料庫寫入失敗、第三方服務錯誤）                                         |
| **critical** | 關鍵功能失效、資料遺失風險（如：金流失敗、資料庫連線斷線）                                           |
| **alert**    | 需立即處理，否則系統會嚴重受損（如：主要服務掛掉、資料庫全斷）                                       |
| **emergency**| 系統完全無法運作（如：整個網站掛掉、核心服務崩潰）                                                   |

> **註解：**
> - 這些層級是 PHP 生態圈的通用標準，方便與各種 log/監控/警報系統整合。
> - 你可以根據訊息嚴重性選擇適合的層級，讓 log 更有層次，方便日後查詢與自動警報。

#### **範例與實務註解**

```php
// debug：只在開發環境用，記錄細節
Log::debug('查詢結果', ['result' => $result]);

// info：記錄重要但正常的事件
Log::info('用戶註冊', ['user_id' => $user->id]);

// notice：非預期但不影響運作
Log::notice('使用過時 API', ['endpoint' => $url]);

// warning：潛在問題，需注意
Log::warning('磁碟空間低於 10%', ['free' => $freeSpace]);

// error：功能失敗，需修復
Log::error('訂單建立失敗', ['order_id' => $orderId, 'error' => $e->getMessage()]);

// critical：關鍵功能失效
Log::critical('金流服務斷線', ['gateway' => 'ECPay']);

// alert：需立即處理
Log::alert('主資料庫離線', ['db_host' => $host]);

// emergency：系統全掛
Log::emergency('網站全站無法存取');
```

> **註解：**
> - `debug`、`info` 適合日常追蹤與營運分析。
> - `warning` 以上建議設監控，`error` 以上可考慮自動通知工程師。
> - `critical`、`alert`、`emergency` 通常會觸發即時警報（如 Slack、Email、SMS）。
> - 實務上，log channel 可以設定只記錄某些等級以上的訊息，避免雜訊。

### *等級優先順序*
DEBUG < INFO < NOTICE < WARNING < ERROR < CRITICAL < ALERT < EMERGENCY

### *配置建議*
- **開發環境**：記錄 DEBUG 以上所有等級
- **測試環境**：記錄 INFO 以上等級
- **生產環境**：記錄 WARNING 以上等級
- **監控系統**：重點關注 ERROR 以上等級

> 生活化比喻：log level 就像「醫院分級」，從門診（DEBUG）到急診（ERROR）到重症監護（CRITICAL），根據嚴重程度分流處理，確保資源合理分配。

### *log level 實務補充與常見疑問*

#### 1. **這些 log level 怎麼設定？**
- 你可以在程式碼中直接呼叫對應方法：
  ```php
  Log::debug('除錯訊息');      // DEBUG 等級
  Log::info('一般資訊');      // INFO 等級
  Log::warning('警告');       // WARNING 等級
  Log::error('錯誤');         // ERROR 等級
  Log::critical('嚴重錯誤');  // CRITICAL 等級
  Log::alert('警報');         // ALERT 等級
  Log::emergency('緊急');     // EMERGENCY 等級
  ```
  > **註解**：這些方法會自動把訊息標記為對應的 log level，並寫入 log 檔或送到你設定的 log channel。

- 你也可以針對 Exception 類別指定 log level：
  ```php
  $exceptions->level(PDOException::class, LogLevel::CRITICAL); // PDOException 都記為 critical
  ```

#### 2. **什麼時候會啟動？**
- *主動呼叫*：你在程式碼中主動呼叫 `Log::xxx()` 時，會立即寫入 log。
- *Exception 報告*：當 Laravel 捕捉到 Exception 並進行 report 時，會根據你設定的等級記錄。
- *系統事件*：某些 Laravel 內建事件（如任務失敗、排程錯誤）也會自動寫入 log。

#### 3. **debug 跟 emergency 差在哪？**
- *debug*：
  - 只用於開發、除錯，記錄細節（如變數內容、流程追蹤）。
  - 通常只在本機或 debug 環境啟用，**生產環境建議關閉**，避免 log 爆量。
  - 不會觸發警報，也不影響系統運作。
- *emergency*：
  - 代表「系統完全無法運作」的最高等級。
  - 例如：資料庫全斷、網站全掛、核心服務崩潰。
  - 通常會觸發即時警報（如 Email、SMS、Slack），通知工程師緊急處理。
  - 這類 log 會被監控系統特別關注。

> **註解**：
> - 兩者的差異在於「嚴重性」與「用途」：debug 是給開發者除錯用，emergency 是給維運/監控用，代表系統已經癱瘓。
> - 你可以根據訊息的重要性，選擇適合的 log level，讓 log 更有層次，也方便自動化監控與警報。

### *log level 分級的本質與系統行為*

> **常見疑問：log level 只是標籤嗎？emergency 會讓系統停掉嗎？**

#### 1. **log level 只是「標籤」**
- 這些分級（debug/info/warning/error/emergency...）本質上只是 log 訊息的「嚴重性標籤」。
- 主要目的是讓 log 檔、監控系統、維運人員能夠分辨訊息的重要性與優先處理順序。
- 例如：你可以只看 error 以上的 log，或只針對 critical/emergency 設警報。

#### 2. **不會影響程式流程**
- 不論你用什麼等級記錄 log，Laravel/Monolog *都不會因此中斷程式或讓系統停掉*。
- 也就是說，`Log::emergency()` 只會產生一條「emergency」等級的 log 訊息，不會讓系統自動停止。
- 除非你*自己在程式碼裡寫明*：「遇到某等級就 exit/abort/throw」，否則 log 只負責記錄，不會主動影響系統。

#### 3. **行為差異來自「你自己」或「log/監控系統」**
- 你可以設定 log channel 只記錄某些等級以上的訊息（如 production 只記錄 warning 以上）。
- 你可以設定監控系統（如 Sentry、Slack、Email）只針對 error/critical/emergency 發送通知。
- 但這些都是*你自己或第三方服務根據 log level 做的額外處理*，不是 log level 本身的功能。

#### 4. **emergency 並不會讓系統自動停掉**
- emergency 只是「最高嚴重等級」的 log 標籤。
- Laravel/Monolog 不會因為你寫了 `Log::emergency()` 就自動讓系統 crash。
- 如果你希望遇到某些情況就讓系統停掉，必須自己在程式碼裡加上 `exit()`、`abort()`、`throw` 等指令。

> **總結**：log level 只是「訊息分級」的標籤，預設不會影響系統運作。只有你自己或監控系統根據這些等級做額外行為時，才會有「自動通知」、「自動重啟」等效果。

---

## 7. **忽略例外（dontReport、ShouldntReport、stopIgnoring）**

### *dontReport 忽略例外*
有些例外你**永遠不想記錄**，可用 dontReport：

```php
use App\Exceptions\InvalidOrderException;
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->dontReport([
        InvalidOrderException::class,
    ]);
})
```

### *ShouldntReport 介面*
也可以讓 Exception 實作 ShouldntReport 介面，Laravel 會**自動忽略**：

```php
use Illuminate\Contracts\Debug\ShouldntReport;
class PodcastProcessingException extends Exception implements ShouldntReport {}
```

### *stopIgnoring 取消忽略*
Laravel 內建會**自動忽略 404/419 等例外**。
如果你想讓某些例外「**不要被忽略**」，可用 stopIgnoring：

```php
use Symfony\Component\HttpKernel\Exception\HttpException;
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->stopIgnoring(HttpException::class);
})
```

> 生活化比喻：dontReport/ShouldntReport 就像「醫院不記錄小感冒」，stopIgnoring 則是「這個特殊病例要記錄」。

---

## 8. **渲染例外（render 方法自訂回應）**

### *什麼是渲染例外？*
渲染（render）就是「**把例外轉成 HTTP 回應**」，決定用戶看到什麼頁面。

### *自訂渲染邏輯*
你可以用 render 方法，針對特定例外自訂回應：

```php
use App\Exceptions\InvalidOrderException;
use Illuminate\Http\Request;
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (InvalidOrderException $e, Request $request) {
        return response()->view('errors.invalid_order', status: 500);
    });
})
```

### *覆蓋內建例外渲染*
也可以覆蓋 Laravel 內建的例外，如 NotFoundHttpException：

```php
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        if ($request->is('api/*')) {
            return response()->json([
                'message' => 'Record not found.'
            ], 404);
        }
    });
})
```

> 生活化比喻：render 就像「翻譯官」，把技術錯誤翻譯成用戶能理解的語言。

---

## 9. **JSON/HTML 回應與 shouldRenderJsonWhen**

### *自動判斷回應格式*
Laravel 會根據請求的 Accept header 自動判斷要回 JSON 還是 HTML。

### *自訂判斷邏輯*
你可以用 shouldRenderJsonWhen 自訂判斷邏輯：

```php
use Illuminate\Http\Request;
use Throwable;
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
        if ($request->is('admin/*')) {
            return true; // admin 路徑都回 JSON
        }
        return $request->expectsJson(); // 其他看 Accept header
    });
})
```

> 生活化比喻：shouldRenderJsonWhen 就像「接待員」，根據客人身份決定用什麼語言溝通。

---

## 10. **respond() 全域自訂回應**

### *全域回應自訂*
很少用到，但可以自訂整個 HTTP 回應：

```php
use Symfony\Component\HttpFoundation\Response;
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->respond(function (Response $response) {
        if ($response->getStatusCode() === 419) {
            return back()->with([
                'message' => 'The page expired, please try again.',
            ]);
        }
        return $response;
    });
})
```

> 生活化比喻：respond 就像「總經理」，可以修改任何對外的回應內容。

---

## 11. **例外類別內自訂 report/render 方法**

### *在例外類別內定義方法*
除了在 `bootstrap/app.php` 配置，也可以直接在例外類別內定義：

```php
namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvalidOrderException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        // 自訂報告邏輯
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): Response
    {
        return response(/* ... */);
    }
}
```

#### **條件性渲染**
如果例外*繼承*自已可渲染的例外，可以 *return false* 使用預設：

```php
public function render(Request $request): Response|bool
{
    if (/* 判斷是否需要自訂渲染 */) {
        return response(/* ... */);
    }
    return false; // 使用預設渲染
}
```

#### **條件性報告**
也可以條件性決定是否報告：

```php
public function report(): bool
{
    if (/* 判斷是否需要自訂報告 */) {
        // 自訂報告邏輯
        return true;
    }
    return false; // 使用預設報告
}
```

> 生活化比喻：例外類別內的方法就像「個人專屬處理流程」，每個例外都有自己的 SOP。

---

## 12. **節流與速率限制（throttle、Lottery、Limit）**

### *為什麼需要節流？*
當應用程式報告大量例外時，可能會：
- 塞爆 log 檔案
- 耗盡外部服務配額
- 影響效能

### *隨機採樣（Lottery）*
用**機率決定**是否記錄例外：

```php
use Illuminate\Support\Lottery;
use Throwable;
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->throttle(function (Throwable $e) {
        return Lottery::odds(1, 1000); // 千分之一機率記錄
    });
})
```

### *條件性採樣*
只對**特定例外**採樣：

```php
use App\Exceptions\ApiMonitoringException;
use Illuminate\Support\Lottery;
use Throwable;
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->throttle(function (Throwable $e) {
        if ($e instanceof ApiMonitoringException) {
            return Lottery::odds(1, 1000);
        }
    });
})
```

### *速率限制（Limit）*
限制**每分鐘記**錄數量：

```php
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Cache\RateLimiting\Limit;
use Throwable;
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->throttle(function (Throwable $e) {
        if ($e instanceof BroadcastException) {
            return Limit::perMinute(300); // 每分鐘最多 300 次
        }
    });
})
```

### *自訂限制鍵值*
**預設用例外類別當鍵值**，可以自訂：

```php
use Illuminate\Broadcasting\BroadcastException; // 匯入廣播相關的 Exception
use Illuminate\Cache\RateLimiting\Limit;        // 匯入速率限制工具
use Throwable;                                    // 匯入所有可丟擲的例外
->withExceptions(function (Exceptions $exceptions) { // 註冊例外處理設定
    $exceptions->throttle(function (Throwable $e) {  // 定義節流（throttle）邏輯
        if ($e instanceof BroadcastException) {      // 如果這個例外是廣播相關
            // 設定每分鐘最多 300 次，並以錯誤訊息內容作為分流 key
            return Limit::perMinute(300)->by($e->getMessage());
            // 這樣同一種訊息只會被限制，不同訊息分開計算
        }
    });
})
```

### *混合使用*
可以同時使用 Lottery 和 Limit：

```php
use App\Exceptions\ApiMonitoringException;      // 匯入自訂 API 監控例外
use Illuminate\Broadcasting\BroadcastException; // 匯入廣播相關的 Exception
use Illuminate\Cache\RateLimiting\Limit;        // 匯入速率限制工具
use Illuminate\Support\Lottery;                 // 匯入隨機抽樣工具
use Throwable;                                    // 匯入所有可丟擲的例外
->withExceptions(function (Exceptions $exceptions) { // 註冊例外處理設定
    $exceptions->throttle(function (Throwable $e) {  // 定義節流（throttle）邏輯
        // 使用 match 判斷例外型別，對不同例外採用不同策略
        return match (true) {
            // 如果是廣播例外，每分鐘最多 300 次
            $e instanceof BroadcastException => Limit::perMinute(300),
            // 如果是 API 監控例外，千分之一機率才記錄（隨機抽樣）
            $e instanceof ApiMonitoringException => Lottery::odds(1, 1000),
            // 其他例外不做限制
            default => Limit::none(),
        };
    });
})
```

> 補充說明：
> - `Limit::perMinute(300)`：每分鐘最多記錄 300 次這類例外，超過就不再記錄，避免 log 爆量。
> - `->by($e->getMessage())`：以錯誤訊息內容作為分流 key，不同訊息分開計算速率。
> - `Lottery::odds(1, 1000)`：千分之一機率才記錄，適合高頻但不重要的例外。
> - `Limit::none()`：不做任何限制，全部記錄。

> 生活化比喻：throttle 就像「交通管制」，避免例外「塞車」影響系統效能。

---

## 13. **HTTP 例外與 abort 輔助函式**

### *HTTP 例外介紹*
有些例外描述 HTTP 錯誤碼，如 404（找不到頁面）、401（未授權）、500（伺服器錯誤）。

### *abort 輔助函式*
從應用程式任何地方產生 HTTP 錯誤回應：

```php
// 基本用法
abort(404); // 產生 404 錯誤

// 帶訊息
abort(403, 'Unauthorized action.');

// 帶自訂標題
abort(404, 'Page not found.', ['title' => 'Custom Title']);
```

#### **常見 HTTP 狀態碼**
- *400*：Bad Request（請求錯誤）
- *401*：Unauthorized（未授權）
- *403*：Forbidden（禁止訪問）
- *404*：Not Found（找不到）
- *419*：Page Expired（頁面過期，CSRF 錯誤）
- *422*：Unprocessable Entity（驗證錯誤）
- *429*：Too Many Requests（請求過多）
- *500*：Internal Server Error（伺服器錯誤）
- *503*：Service Unavailable（服務不可用）

#### **實際應用範例**
```php
// 在控制器中
public function show($id)
{
    $user = User::find($id);
    
    if (!$user) {
        abort(404, 'User not found.');
    }
    
    if (!auth()->user()->can('view', $user)) {
        abort(403, 'You cannot view this user.');
    }
    
    return view('users.show', compact('user'));
}
```

> 生活化比喻：abort 就像「緊急按鈕」，當遇到無法處理的情況時，立即停止並顯示適當的錯誤訊息。

---

## 14. **自訂 HTTP 錯誤頁面**

### *自訂錯誤頁面*
Laravel 可以為不同 HTTP 狀態碼建立自訂錯誤頁面。

### *建立錯誤頁面*
在 `resources/views/errors/` 目錄下建立對應的 Blade 檔案：

**404 錯誤頁面**：`resources/views/errors/404.blade.php`
```html
<!DOCTYPE html>
<html>
<head>
    <title>Page Not Found</title>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>{{ $exception->getMessage() }}</p>
    <a href="{{ url('/') }}">Back to Home</a>
</body>
</html>
```

**500 錯誤頁面**：`resources/views/errors/500.blade.php`
```html
<!DOCTYPE html>
<html>
<head>
    <title>Server Error</title>
</head>
<body>
    <h1>500 - Server Error</h1>
    <p>Something went wrong. Please try again later.</p>
    <a href="{{ url('/') }}">Back to Home</a>
</body>
</html>
```

### *錯誤頁面變數*
- `$exception`：例外物件
- `$exception->getMessage()`：錯誤訊息
- `$exception->getCode()`：錯誤代碼

### *發佈預設錯誤頁面*
可以發佈 Laravel **預設的錯誤頁面範本**：

```bash
php artisan vendor:publish --tag=laravel-errors
```

### *後備錯誤頁面（Fallback）*
可以建立**通用的後備頁面**：

**4xx 錯誤後備頁面**：`resources/views/errors/4xx.blade.php`
```html
<!DOCTYPE html>
<html>
<head>
    <title>Client Error</title>
</head>
<body>
    <h1>Client Error</h1>
    <p>Something went wrong with your request.</p>
    <a href="{{ url('/') }}">Back to Home</a>
</body>
</html>
```

**5xx 錯誤後備頁面**：`resources/views/errors/5xx.blade.php`
```html
<!DOCTYPE html>
<html>
<head>
    <title>Server Error</title>
</head>
<body>
    <h1>Server Error</h1>
    <p>Something went wrong on our end.</p>
    <a href="{{ url('/') }}">Back to Home</a>
</body>
</html>
```

#### **注意事項**
- *404、500、503* 不會使用後備頁面，Laravel 有內建專用頁面
- 要自訂這些頁面，需要建立對應的 404.blade.php、500.blade.php、503.blade.php
- 錯誤頁面應該簡潔、友善，避免技術細節

#### **實際應用範例**
*美觀的 404 頁面範例*：
```html
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>找不到頁面 - 404</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .error-container {
            text-align: center;
            color: white;
            padding: 2rem;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .error-message {
            font-size: 1.5rem;
            margin: 1rem 0;
        }
        .home-link {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            margin-top: 2rem;
            transition: background 0.3s;
        }
        .home-link:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <p class="error-message">糟糕！找不到您要的頁面</p>
        <p>這個頁面可能已經被移除、重新命名，或者暫時無法使用。</p>
        <a href="{{ url('/') }}" class="home-link">回到首頁</a>
    </div>
</body>
</html>
```

> 生活化比喻：自訂錯誤頁面就像「客製化道歉信」，當服務出問題時，用友善的方式向用戶說明情況。

---

## **最佳實踐與總結**

### *錯誤處理最佳實踐*
1. **開發環境 vs 生產環境**：開發顯示詳細錯誤，生產保護敏感資料
2. **錯誤分類與處理**：用戶錯誤、系統錯誤、網路錯誤
3. **記錄策略**：重要錯誤完整記錄，預期錯誤可忽略
4. **用戶體驗**：友善訊息、明確指引、一致設計
5. **監控與警報**：即時監控、錯誤統計、自動警報

### *生活化總結*
- 錯誤處理就像「**完整的醫療體系**」：急診室、分診、專科、病歷、康復指導
- 錯誤處理就像「**智慧交通系統**」：交通號誌、替代道路、即時資訊、事故處理、預防措施

### *結語*
Laravel 的錯誤處理系統提供了完整、靈活且強大的錯誤管理機制。透過適當的配置和自訂，可以建立穩定、友善且易於維護的應用程式。記住：好的錯誤處理不僅是技術問題，更是用戶體驗的重要組成部分。 