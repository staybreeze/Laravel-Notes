# *Laravel Error Handling 錯誤處理 筆記*

---

## **目錄**

1.  錯誤處理介紹與基本概念
2.  錯誤處理配置與設定
3.  錯誤與例外的報告（`Reporting Exceptions`）
4.  全域 Log Context 與 Exception Context
5.  `report()` 輔助函式與去重複（`deduplicate`）
6.  Log Level（`例外分級`）
7.  忽略例外（`dontReport、ShouldntReport、stopIgnorin`g）
8.  渲染例外（`render 方法自訂回應`）
9.  JSON/HTML 回應與 `shouldRenderJsonWhen`
10. `respond()` 全域自訂回應
11. 例外類別內自訂 report/render 方法
12. 節流與速率限制（`throttle、Lottery、Limit`）
13. HTTP 例外與 abort 輔助函式
14. 自訂 HTTP 錯誤頁面（`404、500、fallback、vendor:publish`）

---

## 1. **錯誤處理介紹與基本概念**

### *什麼是錯誤處理？*

錯誤處理是應用程式中`處理異常情況的機制`，就像「安全網」一樣，當程式出現問題時，能夠優雅地處理並提供適當的回應，而不是讓整個應用程式崩潰。

---

### *Laravel 錯誤處理的特點*

1. **開箱即用**：`新專案建立`時，錯誤處理已經配置完成
2. **靈活配置**：可以`自訂`錯誤處理邏輯
3. **環境適應**：開發和生產`環境有不同`的錯誤顯示策略
4. **記錄完整**：`自動記錄`錯誤資訊供除錯使用

---

#### **生活化比喻**

- 錯誤處理就像「_醫院急診室_」：有預設流程、可自訂、分環境、完整記錄
- 錯誤處理就像「_汽車安全系統_」：安全氣囊、故障燈、備用系統、維修手冊

---

## 2. **錯誤處理配置與設定**

### *基本配置檔案*

- `bootstrap/app.php`（Laravel 11 新配置）
- `config/app.php`（舊版配置）

---

### *環境變數設定*

- `.env` 檔案

  - __開發環境__：`APP_DEBUG=true`、`APP_ENV=local`
  - __生產環境__：`APP_DEBUG=false`、`APP_ENV=production`

---

### *APP_DEBUG 設定*

- __true__：顯示`詳細`錯誤資訊（開發環境）
- __false__：只顯示`基本`錯誤訊息（生產環境）

---

#### **為什麼生產環境要關閉 DEBUG？**

- *安全性*：`避免暴露`敏感資訊（__資料庫密碼、檔案路徑__ 等）
- *用戶體驗*：不讓用戶看到`技術細節`
- *效能*：`減少`錯誤處理的開銷
- *專業性*：提供`統一的`錯誤頁面

---

#### **生活化比喻**

- `APP_DEBUG` 就像「汽車儀表板」：技師模式 vs 一般駕駛
- 環境配置就像「_餐廳服務_」：廚房內部 vs 用餐區

---

## 3. **錯誤與例外的報告**（Reporting Exceptions）

### *什麼是 Exception 報告？*

__報告（report）__ 就是`「記錄」或「上報」錯誤`，可以寫進 log，也可以送到外部服務（如 `Sentry、Flare`）。
<!-- config/logging.php -->
Laravel 預設會根據 `logging` 設定 __自動記錄所有例外__。


---

#### **進階自訂報告**

你可以在 `bootstrap/app.php` 用 `withExceptions` 的 `report()` 方法，針對特定例外 _自訂報告方式_：

```php
// 例：自訂 InvalidOrderException 的報告
use App\Exceptions\InvalidOrderException;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->report(function (InvalidOrderException $e) {
        // 這裡可以自訂 log、通知、外部上報等
    });
})
```

- *生活化比喻*：報告就像「__醫院遇到特殊病例時，會通知專科醫師或上報衛生局__」。

---

#### **停止預設 log 行為**

*預設* 自訂 `report callback` __執行後，Laravel 還是會照 logging 設定記錄一次__。

如果你想「__只自訂，不要預設 log__」，可以：

- 用 `->stop()` 或 `callback return false`

```php
// 通常放在 bootstrap/app.php 或 Exception Handler 註冊區塊
// 例如 Laravel 11 可在 bootstrap/app.php 用 withExceptions 註冊：

->withExceptions(function ($exceptions) {
    $exceptions->report(function (InvalidOrderException $e) {
        // ...自訂上報邏輯
    })->stop();
});
// 或
$exceptions->report(function (InvalidOrderException $e) {
    return false;
});
```

---

#### **reportable 例外**

你也可以在 *Exception 類別內* 直接定義 `report()` 方法，Laravel 會自動呼叫。

```php
// 定義自訂例外並覆寫 report 方法
namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    public function report()
    {
        // 這裡可以寫入 log 或上報到外部服務
        \Log::error('CustomException: ' . $this->getMessage());
        // 也可以整合 Sentry、Flare 等外部錯誤追蹤服務
    }
}
```

<!-- [日期 時間] local.ERROR: CustomException: 你的錯誤訊息 -->

---

## 4. **全域 Log Context 與 Exception Context**

### *全域 Log Context*

Laravel 會`自動把目前登入用戶的 ID 加到每個 log（如果有登入）`。

你也可以用 *context()* 方法，加入自訂*全域 context*：

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->context(fn () => [
        'foo' => 'bar',
    ]);
})
```
<!-- [日期 時間] local.INFO: ... {"foo":"bar"} -->

<!-- 如果有登入用戶，Laravel 也會自動加上 user_id，
     [日期 時間] local.INFO: ... {"user_id":1,"foo":"bar"} -->
---

### *例外類別自訂 context*

有時候某個例外需要`額外資訊`，可以在 `Exception 類別` 內加 `context()` 方法：

```php
class InvalidOrderException extends Exception {
    public function context(): array {
        return ['order_id' => $this->orderId];
    }
}
```

<!-- [日期 時間] local.ERROR: ... {"order_id":12345} -->

- *生活化比喻*：`context` 就像「__病歷註記__」，`每次`記錄都可以加上當下的特殊狀態。

---

## 5. **`report()` 輔助函式與去重複（`deduplicate`）**

### *`report()` 輔助函式*

有時你只想「記錄」例外，但**不中斷流程**，可以用 `report($e)`：

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
<!-- 
當驗證邏輯發生例外時，
Laravel 只會把例外記錄到 log 檔案（如 storage/logs/laravel.log），
但程式流程不會中斷，
isValid() 會回傳 false，
後續程式可以繼續執行。 
-->

---

### *去重複*（`deduplicate`）

如果同一個例外被 report 多次，會**產生重複 log**。

可用 `dontReportDuplicates()`，__只記錄第一次__：

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->dontReportDuplicates();
})
```

- *生活化比喻*：`deduplicate` 就像「__醫院同一個病人一天只掛一次號__」，避免重複記錄。

---

## 6. **Log Level**（例外分級）

### *log level 介紹*

log 有分等級（`debug/info/warning/error/critical...`），影響訊息嚴重性與記錄管道。

你可以針對`特定例外`自訂 log level：

```php
use PDOException;
use Psr\Log\LogLevel;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->level(PDOException::class, LogLevel::CRITICAL); // 針對 PDOException 設定為 critical 等級
})
```

- __註解__

  - 這些 log level 並不是 Laravel 自己定義的，而是來自 [PSR-3 Logger Interface 標準](https://www.php-fig.org/psr/psr-3/)。Laravel 內建的 `log 系統（Monolog` __直接支援這些層級__，讓你可以用 `Log::debug()`、`Log::info()` 等方法記錄**不同嚴重程度**的訊息。

<!-- Laravel 的 logging 系統底層用的是 Monolog，
     Monolog 是一個 PHP 的 log 處理套件，
     Laravel 只是封裝、擴充它，讓你可以用更簡單的方式記錄 log。 -->

---

### *Log Level 詳細分級說明*

| 層級          | 何時用？（實務建議）                                               |
|--------------|-----------------------------------------------------------------|
| __debug__    | `開發時`追蹤細節、變數內容、流程（**不建議上 production**）            |
| __info__     | 記錄`正常`流程、重要事件（如 _用戶登入、訂單成立、排程啟動_）              |
| __notice__   | 較 info 嚴重一點，`非預期但不影響運作`（如：_快取失效、使用過時 API_）    |
| __warning__  | `潛在問題`、需注意但不影響主流程（如：_磁碟空間快滿、外部 API 回應慢_）    |
| __error__    | `功能`失敗、`用戶操作`失敗（如：_資料庫寫入失敗、第三方服務錯誤_）        |
| __critical__ | `關鍵功能`失效、`資料遺失`風險（如：_金流失敗、資料庫連線斷線_）          |
| __alert__    | `需立即處理`，否則系統會嚴重受損（如：_主要服務掛掉、資料庫全斷_）         |
| __emergency__| 系統`完全無法運作`（如：_整個網站掛掉、核心服務崩潰_）                   |

- __註解__

 - 這些層級是 _PHP 生態圈_ 的通用標準，方便與各種 `log/監控/警報`系統整合。
 - 你可以根據訊息`嚴重性`選擇適合的層級，讓 log 更有層次，方便日後查詢與自動警報。

---

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

- _註解_

 - `debug`、`info` 適合 __日常追蹤與營運分析__。
 - `warning` 以上建議設 __監控__，`error` 以上可考慮 __自動通知工程師__。
 - `critical`、`alert`、`emergency` 通常會觸發 __即時警報__（如 _Slack、Email、SMS_）。
 - 實務上，**log channel** 可以設定 __只記錄某些等級以上的訊息，避免雜訊__。

---

### *等級優先順序*

_DEBUG_ < _INFO_ < _NOTICE_ < __WARNING__ < `ERROR` < `CRITICAL` < `ALERT` < `EMERGENCY`

---

### *配置建議*

- __開發環境__：記錄 `DEBUG` 以上所有等級
- __測試環境__：記錄 `INFO` 以上等級
- __生產環境__：記錄 `WARNING` 以上等級
- __監控系統__：重點關注 `ERROR` 以上等級

- *生活化比喻*：log level 就像「__醫院分級__」，從門診（`DEBUG`）到急診（`ERROR`）到重症監護（`CRITICAL`），根據嚴重程度分流處理，確保資源合理分配。

---

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

  - __註解__：這些方法會自動把訊息標記為對應的 `log level`，並寫入 log 檔或送到你設定的 `log channel`。

- 你也可以針對 `Exception` 類別指定 `log level`：

  ```php
  $exceptions->level(PDOException::class, LogLevel::CRITICAL); // PDOException 都記為 critical
  ```

---

#### 2. **什麼時候會啟動？**

- *主動呼叫*：你在程式碼中主動呼叫 `Log::xxx()` 時，會立即寫入 log。
- *Exception 報告*：當 Laravel 捕捉到 `Exception` 並進行 `report` 時，會根據你設定的等級記錄。
- *系統事件*：某些 Laravel `內建事件`（如 __任務失敗、排程錯誤__）也會自動寫入 log。

---

#### 3. **debug 跟 emergency 差在哪？**

- *debug*：

  - 只用於`開發、除錯，記錄細節`（如變數內容、流程追蹤）。
  - 通常只在`本機`或 debug 環境啟用，**生產環境建議關閉**，避免 log 爆量。
  - 不會觸發警報，也不影響系統運作。
  
- *emergency*：
  - 代表「__系統完全無法運作__」的最高等級。
  - 例如：資料庫全斷、網站全掛、核心服務崩潰。
  - 通常會觸發`即時警報`（如 _Email、SMS、Slack_），通知工程師緊急處理。
  - 這類 log 會被`監控系統`特別關注。

- __註解__
 - 兩者的差異在於「嚴重性」與「用途」：`debug` 是給 _開發者除錯用_，`emergency` 是 _給維運/監控用_，代表系統已經癱瘓。
 - 你可以根據訊息的重要性，選擇適合的 `log level`，讓 log 更有層次，也方便自動化監控與警報。

---

### *常見疑問：log level 只是標籤嗎？emergency 會讓系統停掉嗎？*

#### 1. **log level 只是「標籤」**

- 這些分級（`debug/info/warning/error/emergency...`）本質上只是 log 訊息的「`嚴重性標籤」`。
- 主要目的是讓 log 檔、監控系統、維運人員能夠`分辨訊息的重要性與優先處理順序`。
- 例如：你可以只看 `error` 以上的 log，或只針對 `critical/emergency` _設警報_。

---

#### 2. **不會影響程式流程**

- 不論你用什麼等級記錄 log，`Laravel/Monolog` *都不會因此中斷程式或讓系統停掉*。
- 也就是說，`Log::emergency()` 只會產生 __一條「emergency」等級的 log 訊息__ ，不會讓系統自動停止。
- 除非你*自己在程式碼裡寫明*：「遇到某等級就 `exit/abort/throw`」，否則 log 只負責記錄，不會主動影響系統。

---

#### 3. **行為差異來自「你自己」或「log/監控系統」**

- 你可以設定 `log channel` 只記錄某些等級以上的訊息（如 `production` 只記錄 `warning` 以上）。
- 你可以設定監控系統（如 _Sentry、Slack、Email_）只針對 `error/critical/emergency` 發送通知。
- 但這些都是 *你自己或第三方服務根據 log level 做的額外處理* ，不是 log level 本身的功能。

---

#### 4. **emergency 並不會讓系統自動停掉**

- `emergency` 只是「_最高嚴重等級_」的 log 標籤。
- `Laravel/Monolog` 不會因為你寫了 `Log::emergency()` 就自動讓系統 crash。
- 如果你希望遇到某些情況就讓系統停掉，必須自己在程式碼裡加上 `exit()`、`abort()`、`throw` 等指令。

- *總結*：`log level` __只是「訊息分級」的標籤，預設不會影響系統運作__。只有你自己或監控系統根據這些等級做額外行為時，才會有「自動通知」、「自動重啟」等效果。

---

## 7. **忽略例外**（`dontReport、ShouldntReport、stopIgnoring`）

### *dontReport 忽略例外*

有些例外你**永遠不想記錄**，可用 `dontReport`：

```php
use App\Exceptions\InvalidOrderException;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->dontReport([
        InvalidOrderException::class,
    ]);
})
```

---

### *ShouldntReport 介面*

也可以讓 `Exception` 實作 `ShouldntReport` 介面，Laravel 會**自動忽略**：

```php
use Illuminate\Contracts\Debug\ShouldntReport;
class PodcastProcessingException extends Exception implements ShouldntReport {}
```

---

### *stopIgnoring 取消忽略*

Laravel 內建會**自動忽略 404/419 等例外**。

如果你想讓某些例外「**不要被忽略**」，可用 `stopIgnoring`：

```php
use Symfony\Component\HttpKernel\Exception\HttpException;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->stopIgnoring(HttpException::class);
})
```

<!-- 
Laravel 會自動忽略像 404（使用者請求不存在的頁面）、419（CSRF token 過期或失效）這類常見例外，
因為它們通常不是程式錯誤，而是正常的使用情境或安全機制，不需要記錄到 log 或通知開發者，
這樣可以避免 log 檔案被大量無意義的錯誤訊息淹沒，
讓你能專注在真正需要處理的錯誤上。
-->

- *生活化比喻*：`dontReport/ShouldntReport` 就像「__醫院不記錄小感冒__」，`stopIgnoring` 則是「__這個特殊病例要記錄__」。

---

## 8. **渲染例外**（render 方法自訂回應）

### *什麼是渲染例外？*

渲染（render）就是「__把例外轉成 HTTP 回應__」，決定用戶看到什麼頁面。

---

### *自訂渲染邏輯*

你可以用 `render` 方法，針對特定例外自訂回應：

```php
use App\Exceptions\InvalidOrderException;
use Illuminate\Http\Request;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (InvalidOrderException $e, Request $request) {
        return response()->view('errors.invalid_order', status: 500);
    });
})
```

---

### *覆蓋內建例外渲染*

也可以 __覆蓋__ Laravel 內建的例外，如 `NotFoundHttpException`：

```php
// bootstrap/app.php
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        // 如果是 API 路徑，回傳 JSON 格式的 404 錯誤訊息
        if ($request->is('api/*')) {
            return response()->json([
                'message' => 'Record not found.'
            ], 404);
        }
    });
})
```

- *生活化比喻*`：render` 就像「__翻譯官__」，把技術錯誤翻譯成用戶能理解的語言。

---

## 9. **JSON/HTML 回應與 `shouldRenderJsonWhen`**

### *自動判斷回應格式*

Laravel 會根據請求的` Accept header` 自動判斷要回 JSON 還是 HTML。

---

### *自訂判斷邏輯*

你可以用 `shouldRenderJsonWhen` 自訂判斷邏輯：

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

- *生活化比喻*：`shouldRenderJsonWhen` 就像「__接待員__」，根據客人身份決定用什麼語言溝通。

---

## 10. **`respond()` 全域自訂回應**

### *全域回應自訂*

很少用到，但可以自訂`整個 HTTP 回應`：

```php
use Symfony\Component\HttpFoundation\Response;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->respond(function (Response $response) {
        // 如果狀態碼是 419（CSRF 過期），則重導回上一頁並顯示提示訊息
        if ($response->getStatusCode() === 419) {
            return back()->with([
                'message' => 'The page expired, please try again.',
            ]);
        }
        // 其他狀態碼則維持原本回應
        return $response;
    });
})
```

- *生活化比喻*：`respond` 就像「__總經理__」，可以修改任何對外的回應內容。

---

## 11. **例外類別內自訂 `report/render` 方法**

### *在例外類別內定義方法*

除了在 `bootstrap/app.php` 配置，也可以 __直接在例外類別內定義__：

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

---

#### **條件性渲染**

如果例外 *繼承* 自已可渲染的例外，可以 *return false* 使用**預設**：

```php
public function render(Request $request): Response|bool
{
    if (/* 判斷是否需要自訂渲染 */) {
        return response(/* ... */);
    }
    return false; // 使用預設渲染
}
```

---

#### **條件性報告**

也可以條件性決定`是否報告`：

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

- *生活化比喻*：例外類別內的方法就像「__個人專屬處理流程__」，每個例外都有自己的 SOP。

---

## 12. **節流與速率限制**（`throttle、Lottery、Limit`）

### *為什麼需要節流？*

當應用程式報告`大量例外`時，可能會：

- 塞爆 log 檔案
- 耗盡外部服務配額
- 影響效能

---

### *隨機採樣*（`Lottery`）

用**機率決定**是否記錄例外：

```php
use Illuminate\Support\Lottery;
use Throwable;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->throttle(function (Throwable $e) {
        return Lottery::odds(1, 1000); // 千分之一機率記錄
        // odds 的意思是「機率」或「中獎率」。
    });
})
```
<!-- throttle 的意思是「限流」或「限制頻率」。
     在 Laravel 例外處理裡，throttle 用來「控制某些例外被記錄或上報的頻率」，
     可以用機率（Lottery）或速率（Limit）來減少高頻率例外的處理量，
     避免系統過度記錄或通知。 -->

<!-- throttle 是 Laravel 例外處理裡用來「限流」的統一方法，
     不管你用 Lottery（機率）或 Limit（速率），
     都要透過 throttle 來設定例外的記錄頻率或機率。 -->

<!-- Limit 是用來設定「固定速率」限制（例如每分鐘最多 300 次），
     Lottery 則是用「機率」隨機採樣（例如千分之一機率），
     兩者用途不同，可以根據需求選擇，
     如果只想限制總次數，用 Limit 就好；
     如果想隨機記錄，用 Lottery。 -->

<!-- `Throwable` 是 PHP 7 新增的介面，  
     所有例外（Exception）和錯誤（Error）都繼承自它。  
     這代表你可以用 `catch (Throwable $e)` 一次捕捉所有錯誤和例外，  
     而不只限於 Exception 類型。  
     例如：
     ```php
     try {
         // 可能發生錯誤或例外的程式
     } catch (Throwable $e) {
         // 這裡可以處理所有錯誤和例外
     } -->

---

### *條件性採樣*

只對**特定例外**採樣：

```php
use App\Exceptions\ApiMonitoringException;
use Illuminate\Support\Lottery;
use Throwable;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->throttle(function (Throwable $e) {
        // 只有 ApiMonitoringException 例外會啟用 throttle
        // 這裡設定 1/1000 機率才會真正處理（例如上報或記錄）
        if ($e instanceof ApiMonitoringException) {
            return Lottery::odds(1, 1000);
        }
    });
})
```

---

### *速率限制*（`Limit`）

限制**每分鐘記錄**數量：

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

---

### *自訂限制鍵值*

**預設用`例外類別`當鍵值**，可以自訂：

```php
use Illuminate\Broadcasting\BroadcastException; // 匯入廣播相關的 Exception
use Illuminate\Cache\RateLimiting\Limit;        // 匯入速率限制工具
use Throwable;                                  // 匯入所有可丟擲的例外

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

---

### *混合使用*

可以同時使用 `Lottery` 和 `Limit`：

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
<!--  match 是 PHP 8 的運算式語法，
     用來根據條件回傳不同的值，類似 switch，但更簡潔且支援回傳值。
     match (true) 可以用來寫多個布林條件判斷，
     每個 case 都是條件，符合就回傳對應結果。 -->

<!-- 
     $value = 15;

     $result = match (true) {
         $value < 10 => '小於 10',
         $value < 20 => '10 到 19',
         $value >= 20 => '20 以上',
         default => '未知',
     };

     echo $result; // 輸出：10 到 19 -->

- __補充說明__：

 - `Limit::perMinute(300)`：每分鐘最多記錄 300 次這類例外，_超過就不再記錄_，避免 log 爆量。
 - `->by($e->getMessage())`：以 _錯誤訊息內容_ 作為 _分流 key_，不同訊息分開計算速率。
 - `Lottery::odds(1, 1000)`：千分之一機率 _才記錄_，適合高頻但不重要的例外。
 - `Limit::none()`：不做任何限制，_全部記錄_。

- *生活化比喻*：`throttle` 就像「__交通管制__」，避免例外「塞車」影響系統效能。

---

## 13. **HTTP 例外與 abort 輔助函式**

### *HTTP 例外介紹*

有些例外描述 HTTP 錯誤碼，如 `404`（找不到頁面）、`401`（未授權）、`500`（伺服器錯誤）。

---

### *`abort` 輔助函式*

從應用程式`任何地方`產生 HTTP 錯誤回應：

```php
// 基本用法
abort(404); // 產生 404 錯誤

// 帶訊息
abort(403, 'Unauthorized action.');

// 帶自訂標題
abort(404, 'Page not found.', ['title' => 'Custom Title']);
```

---

#### **常見 HTTP 狀態碼**

- *400*：Bad Request（`請求錯誤`）  
  用戶送出的請求 __格式或參數有誤__，伺服器無法理解。

- *401*：Unauthorized（`未授權`）  
  用戶 __未登入或認證失敗__，無法存取資源。
  `authentization`

- *403*：Forbidden（`禁止訪問`）  
  用戶雖然 __`已認證`，但沒有權限__ 存取該資源。
  `authorization`

- *404*：Not Found（`找不到`）  
  請求的 __資源不存在__ 或 __網址錯誤__。

- *419*：Page Expired（`頁面過期，CSRF 錯誤`）  
  表單或頁面過期，通常是 __CSRF token__ 驗證失敗。

- *422*：Unprocessable Entity（`驗證錯誤`）  
  請求 __格式`正確`，但資料驗證失敗__（如`表單欄位`不符規則）。

- *429*：Too Many Requests（`請求過多`）  
  用戶在短時間內發送 __太多請求__，被**速率限制**。

- *500*：Internal Server Error（`伺服器錯誤`）  
  伺服器 __內部程式錯誤__，導致無法處理請求。

- *503*：Service Unavailable（`服務不可用`）  
  伺服器暫時無法處理請求，可能在 __維護或過載__。

---

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

- *生活化比喻*：`abort` 就像「__緊急按鈕__」，當遇到無法處理的情況時，立即停止並顯示適當的錯誤訊息。

---

## 14. **自訂 HTTP 錯誤頁面**

### *自訂錯誤頁面*

Laravel 可以為 __不同 HTTP 狀態碼__ 建立自訂錯誤頁面。

---

### *建立錯誤頁面*

在 `resources/views/errors/` 目錄下建立對應的 Blade 檔案：

__404 錯誤頁面__：`resources/views/errors/404.blade.php`

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

__500 錯誤頁面__：`resources/views/errors/500.blade.php`

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

---

### *錯誤頁面變數*

- `$exception`：__例外物件__
- `$exception->getMessage()`：__錯誤訊息__
- `$exception->getCode()`：__錯誤代碼__
<!-- Laravel 在處理例外時，
     會自動產生 Exception 物件並傳給錯誤頁面（如 Blade），
     你不需要手動引入，Blade 裡可以直接用 $exception 取得相關資訊。 -->

---

### *發佈預設錯誤頁面*

可以發佈 Laravel **預設的錯誤頁面範本**：

```bash
php artisan vendor:publish --tag=laravel-errors
```

---

### *後備錯誤頁面*（Fallback）

可以建立**通用的後備頁面**：

__4xx 錯誤後備頁面__：`resources/views/errors/4xx.blade.php`

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

__5xx 錯誤後備頁面__：`resources/views/errors/5xx.blade.php`

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

---

#### **注意事項**

- __404、500、503__ 不會使用後備頁面，Laravel 有內建專用頁面
- 要自訂這些頁面，需要建立對應的 `404.blade.php、500.blade.php、503.blade.php`
- 錯誤頁面應該`簡潔、友善`，避免技術細節

---

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

- *生活化比喻*：`自訂錯誤頁面`就像「__客製化道歉信__」，當服務出問題時，用友善的方式向用戶說明情況。

---

## **最佳實踐與總結**

### *錯誤處理最佳實踐*

1. __開發環境 vs 生產環境__：開發顯示`詳細錯誤`，生產保護`敏感資料`
2. __錯誤分類與處理__：用戶錯誤、系統錯誤、網路錯誤
3. __記錄策略__：重要錯誤`完整記錄`，預期錯誤`可忽略`
4. __用戶體驗__：友善訊息、明確指引、一致設計
5. __監控與警報__：即時監控、錯誤統計、自動警報

---

### *生活化總結*

- 錯誤處理就像「__完整的醫療體系__」：急診室、分診、專科、病歷、康復指導
- 錯誤處理就像「__智慧交通系統__」：交通號誌、替代道路、即時資訊、事故處理、預防措施

---

### *結語*

Laravel 的錯誤處理系統提供了完整、靈活且強大的`錯誤管理機制`。透過適當的配置和自訂，可以建立`穩定、友善且易於維護`的應用程式。
__記住__：好的錯誤處理不僅是`技術問題`，更是`用戶體驗`的重要組成部分。 