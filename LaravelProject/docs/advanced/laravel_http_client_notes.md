# Laravel HTTP Client 筆記

## 介紹
Laravel HTTP Client 是基於 Guzzle 封裝的簡潔 API，讓你能快速、優雅地發送 HTTP 請求，與其他 Web 應用程式溝通。Laravel 封裝後的 API 著重於最常見的用法與良好的開發體驗。

```php
【補充說明：什麼是 Guzzle？】
// Guzzle（Guzzle HTTP Client）是 PHP 最主流的 HTTP 請求函式庫，
// 讓你可以在 PHP 程式中很方便地發送 HTTP 請求（GET、POST、PUT、DELETE 等），
// 並處理回應、設定 headers、認證、timeout、上傳下載檔案等。
//
// Laravel 的 HTTP Client（Http::get()、Http::post() 等）
// 就是基於 Guzzle 再包裝，讓你用更簡單、直覺的語法操作。
//
// 【Guzzle 原生用法範例】
use GuzzleHttp\Client;
$client = new Client();
$response = $client->get('https://api.example.com/data');
data = $response->getBody()->getContents();
//
// 【Laravel HTTP Client（底層用 Guzzle）】
use Illuminate\Support\Facades\Http;
$response = Http::get('https://api.example.com/data');
$data = $response->body();
//
// 小結：Guzzle 就像 PHP 的「超強 HTTP 工具箱」，Laravel 幫你包裝成更好用的 API。


【補充說明：什麼是 Guzzle？Laravel HTTP Client 與 Guzzle 的關係】
- Guzzle 是 PHP 最主流的 HTTP 請求函式庫，讓你可以很方便地在 PHP 程式中發送 HTTP 請求、處理回應、設定 headers、認證、timeout、上傳下載檔案等。
- Laravel 的 HTTP Client （Http::get()、Http::post() 等）其實是「包裝」了 Guzzle，讓你用更簡潔、直覺的語法操作。
- Laravel 預設會安裝 Guzzle（composer 會自動帶入 guzzlehttp/guzzle 套件），只要你用 Laravel 7 以上版本，基本都會有。
- 如果沒有 Guzzle，Laravel 的 HTTP Client 會完全失效，因為底層就是靠 Guzzle 實作，會出現找不到 Guzzle 或 Class not found 的錯誤。
- 總結：Guzzle 是底層引擎，Laravel HTTP Client 是包裝後的好用介面，兩者缺一不可。

```
【Guzzle、HTTP Client、Route 差異與關係】
- **Guzzle**：PHP 的 HTTP 請求函式庫，讓你「**主動**」去呼叫別人的 API（例如你寫 PHP 程式去抓天氣、查匯率等）。
- **Laravel HTTP Client**：Laravel 幫你包裝 Guzzle，讓你用更簡單的語法（如 Http::get()）去「**主動**」發送 HTTP 請求。
- **Route（路由）**：Laravel 的路由是「**讓別人來呼叫你**」的入口，也就是你寫 API、網站，讓外部（瀏覽器、App、其他伺服器）來存取你的程式。

【三者的關係與影響】
- **Guzzle/HTTP Client** 是「你去找別人」；
  **Route** 是「別人來找你」。
- 兩者完全獨立，互不影響。

【如果沒有 Guzzle 會怎樣？】
- 你不能用 Laravel 的 HTTP Client（Http::get()、Http::post() 等）去**主動發送** HTTP 請求，因為底層就是靠 Guzzle。
- 但是！你的 Laravel 路由（Route::get、Route::post 等）完全不受影響，因為這是 Laravel 處理「**接收請求**」的功能，跟 Guzzle 無關。
- 你可以只用 Route 完成一個網站或 API，完全不用 Guzzle。
- 只有當你要「主動去抓資料」時，才需要 Guzzle 或 HTTP Client。

【圖解】
- 你 →（Guzzle/HTTP Client）→ 別人（API、網站）
- 別人（瀏覽器、App）→（Route）→ 你

- 這一區塊是理解 Laravel HTTP Client 與 Guzzle、Route 差異的重點，務必熟記！
---

## 基本用法

### 1. 發送請求
可用 Http facade 的 head、get、post、put、patch、delete 方法發送請求。

```php
use Illuminate\Support\Facades\Http;

$response = Http::get('http://example.com');
// 發送 GET 請求，回傳 Response 物件
```

---

## 2. 回應物件（Response）常用方法

```php
$response->body();
// 取得 HTTP 回應的原始內容（字串），不做任何解析。適合回應不是 JSON 或你只想拿到原始資料時使用。
$response->json($key = null, $default = null);
// 將回應內容解析為陣列（假設回應是 JSON 格式）。
// $key 可指定要取得的 JSON 欄位（支援點號語法，如 'data.user.name'）。
// $default 若找不到指定 key，回傳預設值。
$response->object(); // 轉為物件
// 將 JSON 回應內容轉成 PHP 標準物件（stdClass），方便用物件屬性存取資料。
$response->collect($key = null); // 轉為 Collection
// 將 JSON 回應內容轉成 Laravel Collection 物件，方便用 Collection 的各種方法（如 map、filter、pluck 等）處理資料。
// $key 可指定要轉換的子欄位。
$response->resource(); // 取得資源 resource
// 取得底層的 stream resource，通常用於需要以 stream 方式處理大量資料（如檔案下載）時。
$response->status(); // 取得 HTTP 狀態碼
// 取得 HTTP 回應的狀態碼（如 200、404、500 等），可用來判斷請求是否成功。
$response->successful(); // 狀態碼 2xx
// 判斷回應狀態碼是否為 2xx（代表請求成功），回傳布林值。
$response->redirect(); // 是否為重導
// 判斷回應是否為 HTTP 重導（3xx 狀態碼），回傳布林值。
$response->failed(); // 狀態碼 >= 400
// 判斷回應是否為失敗（狀態碼大於等於 400），回傳布林值。
$response->clientError(); // 狀態碼 4xx
// 判斷回應是否為 client error（4xx 狀態碼），回傳布林值。
$response->header($header); // 取得單一 header
// 取得指定名稱的 HTTP 回應 header 值。
$response->headers(); // 取得所有 header 陣列
// 取得所有 HTTP 回應 header，回傳關聯陣列（header 名稱為 key）。
```

- Response 物件支援 ArrayAccess，可直接用陣列方式存取 JSON 欄位：
```php
return Http::get('http://example.com/users/1')['name'];
// 直接取得 name 欄位
```

---

## 3. 狀態碼判斷方法
基本上都是回傳 **boolean（布林值）**，用來判斷 HTTP 回應的狀態
```php
$response->ok(); // 200 OK
$response->created(); // 201 Created
$response->accepted(); // 202 Accepted
$response->noContent(); // 204 No Content
$response->movedPermanently(); // 301 Moved Permanently
$response->found(); // 302 Found
$response->badRequest(); // 400 Bad Request
$response->unauthorized(); // 401 Unauthorized
$response->paymentRequired(); // 402 Payment Required
$response->forbidden(); // 403 Forbidden
$response->notFound(); // 404 Not Found
$response->requestTimeout(); // 408 Request Timeout
$response->conflict(); // 409 Conflict
$response->unprocessableEntity(); // 422 Unprocessable Entity
$response->tooManyRequests(); // 429 Too Many Requests
$response->serverError(); // 500 Internal Server Error
```

---

## 4. **URI Templates**

可用 **withUrlParameters** 定義 URI 參數，並用模板展開：
```php
Http::withUrlParameters([
    'endpoint' => 'https://laravel.com',
    'page' => 'docs',
    'version' => '12.x',
    'topic' => 'validation',
])->get('{+endpoint}/{page}/{version}/{topic}');
// 產生 https://laravel.com/docs/12.x/validation
```

---

## 5. **Dumping Requests**

```php
return Http::dd()->get('http://example.com');
// 送出前 dump 請求內容並終止執行
```

---

## 6. 請求資料

### (1) **傳送 JSON 資料（預設）**
```php
$response = Http::post('http://example.com/users', [
    'name' => 'Steve',
    'role' => 'Network Administrator',
]);
// 以 application/json 傳送資料
```

### (2) **GET 請求帶查詢參數**
```php
$response = Http::get('http://example.com/users', [
    'name' => 'Taylor',
    'page' => 1,
]);
// 產生 http://example.com/users?name=Taylor&page=1
```
// 或用 **withQueryParameters**：
```php
Http::retry(3, 100)->withQueryParameters([
    'name' => 'Taylor',
    'page' => 1,
])->get('http://example.com/users');
```

### (3) **傳送 Form URL Encoded**
```php
$response = Http::asForm()->post('http://example.com/users', [
    'name' => 'Sara',
    'role' => 'Privacy Consultant',
]);
// 以 application/x-www-form-urlencoded 傳送
```

### (4) **傳送 Raw Body**
```php
$response = Http::withBody(
    base64_encode($photo), 'image/jpeg'
)->post('http://example.com/photo');
// 傳送原始資料內容，指定 content-type
```

### (5) **Multi-Part 檔案上傳**
```php
$response = Http::attach(
    'attachment', file_get_contents('photo.jpg'), 'photo.jpg', ['Content-Type' => 'image/jpeg']
)->post('http://example.com/attachments');
// 上傳檔案，指定檔名與 header

// 也可用 stream resource：
$photo = fopen('photo.jpg', 'r');
$response = Http::attach(
    'attachment', $photo, 'photo.jpg'
)->post('http://example.com/attachments');
```

---

## 7. **Header 設定**

```php
$response = Http::withHeaders([
    'X-First' => 'foo',
    'X-Second' => 'bar'
])->post('http://example.com/users', [
    'name' => 'Taylor',
]);
// 設定自訂 header

$response = Http::accept('application/json')->get('http://example.com/users');
// 指定期望回應 content-type

$response = Http::acceptJson()->get('http://example.com/users');
// 快速指定期望 application/json

// 取代所有 header
$response = Http::withHeaders([
    'X-Original' => 'foo',
])->replaceHeaders([
    'X-Replacement' => 'bar',
])->post('http://example.com/users', [
    'name' => 'Taylor',
]);
```

---

## 8. **認證**

```php
// Basic Auth
$response = Http::withBasicAuth('taylor@laravel.com', 'secret')->post(/* ... */);
// Digest Auth
$response = Http::withDigestAuth('taylor@laravel.com', 'secret')->post(/* ... */);
// Bearer Token
$response = Http::withToken('token')->post(/* ... */);
```

---

## 9. **Timeout 與 Retry**

```php
$response = Http::timeout(3)->get(/* ... */);
// 設定回應最長等待 3 秒

$response = Http::connectTimeout(3)->get(/* ... */);
// 設定連線最長等待 3 秒

$response = Http::retry(3, 100)->post(/* ... */);
// 最多重試 3 次，每次間隔 100 毫秒

// 自訂 sleep 間隔
use Exception; // 引入 PHP 內建 Exception 類別，callback 會用到
$response = Http::retry(3, function (int $attempt, Exception $exception) {
    // 設定最多重試 3 次，第二個參數是 callback，決定每次重試的間隔（毫秒）
    // $attempt：第幾次重試（從 1 開始）
    // $exception：這次發生的 Exception 物件
    return $attempt * 100; // 每次重試間隔遞增（100ms、200ms、300ms）
})->post(/* ... */); // 發送 POST 請求

// 以陣列指定每次間隔
$response = Http::retry([100, 200])->post(/* ... */);
// 用陣列直接指定每次重試的間隔（毫秒），第一次 100ms，第二次 200ms

// 只在特定例外時重試
use Exception; // 引入 Exception
use Illuminate\Http\Client\PendingRequest; // 引入 PendingRequest
$response = Http::retry(3, 100, function (Exception $exception, PendingRequest $request) {
    // 設定最多重試 3 次，每次間隔 100ms，第三個參數 callback 決定是否要重試
    return $exception instanceof ConnectionException; // 只有遇到 ConnectionException 才重試
})->post(/* ... */); // 發送 POST 請求

// 失敗時可修改 request 再重試
use Exception; // 引入 Exception
use Illuminate\Http\Client\PendingRequest; // 引入 PendingRequest
use Illuminate\Http\Client\RequestException; // 引入 RequestException
$response = Http::withToken($this->getToken()) // 先設定 Bearer Token
    ->retry(2, 0, function (Exception $exception, PendingRequest $request) {
        // 最多重試 2 次，每次間隔 0ms，第三個參數 callback 可修改 request 並決定是否重試
        if (! $exception instanceof RequestException || $exception->response->status() !== 401) {
            // 只有遇到 RequestException 且狀態碼為 401（未授權）才重試
            return false;
        }
        $request->withToken($this->getNewToken()); // 重試前自動刷新 token
        return true; // 回傳 true 代表要重試
    })->post(/* ... */); // 發送 POST 請求

// 關閉自動丟出例外
$response = Http::retry(3, 100, throw: false)->post(/* ... */);
```

---

## 10. 錯誤處理與例外

```php
// 狀態碼 2xx
$response->successful();
// 狀態碼 >= 400
$response->failed();
// 狀態碼 4xx
$response->clientError();
// 狀態碼 5xx
$response->serverError();
// 發生錯誤時執行 callback
$response->onError(callable $callback);

// 丟出例外
$response->throw();
$response->throwIf($condition);
$response->throwIf(fn (Response $response) => true);
$response->throwUnless($condition);
$response->throwUnless(fn (Response $response) => false);
$response->throwIfStatus(403); // 如果回應狀態碼是 403（禁止存取），就丟出例外（Exception）
$response->throwUnlessStatus(200); // 如果回應狀態碼不是 200（成功），就丟出例外（Exception）

// 例外物件可取得 $response 屬性
// throw() 回傳 response 物件，可繼續鏈式操作
return Http::post(/* ... */)->throw()->json();

// 丟出前執行自訂邏輯
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;
return Http::post(/* ... */)->throw(function (Response $response, RequestException $e) {
    // ...
})->json();

// 設定例外訊息截斷長度
use Illuminate\Foundation\Configuration\Exceptions; // 引入 Exceptions 設定類
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->truncateRequestExceptionsAt(240); // 設定 request 例外訊息最長 240 字元，超過會自動截斷
    $exceptions->dontTruncateRequestExceptions(); // 取消截斷，讓例外訊息完整顯示
})
// 或單次請求
return Http::truncateExceptionsAt(240)->post(/* ... */); // 只針對這次請求，例外訊息最長 240 字元
```

---

## 11. **Guzzle Middleware**

### (1) **請求** Middleware
```php
use Illuminate\Support\Facades\Http; // 匯入 Laravel HTTP Facade
use Psr\Http\Message\RequestInterface; // 匯入 PSR-7 Request 介面
$response = Http::withRequestMiddleware(
    function (RequestInterface $request) {
        // 這個 middleware 會在每次發送 HTTP 請求前執行
        // $request 代表即將送出的請求物件
        return $request->withHeader('X-Example', 'Value'); // 加入自訂 header
    }
)->get('http://example.com'); // 發送 GET 請求，會帶上剛剛加的 header
// 發送前自訂 header
```

### (2) **回應** Middleware
```php
use Illuminate\Support\Facades\Http; // 匯入 Laravel HTTP Facade
use Psr\Http\Message\ResponseInterface; // 匯入 PSR-7 Response 介面
$response = Http::withResponseMiddleware(
    function (ResponseInterface $response) {
        // 這個 middleware 會在每次收到 HTTP 回應後執行
        // $response 代表收到的回應物件
        $header = $response->getHeader('X-Example'); // 取得回應中的 X-Example header
        // ... 這裡可以根據 header 或內容做進一步處理
        return $response; // 回傳（可選擇修改後的）response 物件
    }
)->get('http://example.com'); // 發送 GET 請求，收到回應後會執行 middleware
// 回應後可檢查/處理 header
```

---

## 12. **全域 Middleware**

```php
use Illuminate\Support\Facades\Http;
Http::globalRequestMiddleware(fn ($request) => $request->withHeader(
    'User-Agent', 'Example Application/1.0'
));
// 所有請求都會帶上 User-Agent header
// User-Agent 是 HTTP 標準請求標頭，通常用來標示發送端的應用程式名稱與版本（這裡是固定字串 'Example Application/1.0'，讓伺服器知道請求來源）
// globalRequestMiddleware 的 callback 只會收到 $request 物件，因為這是「發送前」的請求，可以修改 request 內容

Http::globalResponseMiddleware(fn ($response) => $response->withHeader(
    'X-Finished-At', now()->toDateTimeString()
));
// 所有回應都會帶上 X-Finished-At header
// X-Finished-At 是自訂的回應標頭，這裡用來記錄「這個回應產生的時間」，每次都會是現在時間（now()->toDateTimeString()）
// globalResponseMiddleware 的 callback 只會收到 $response 物件，因為這是「收到回應後」的階段，可以檢查或修改 response 內容
// 兩個方法雖然名稱類似，但一個處理 request，一個處理 response，所以參數型別不同
```

---

## 13. **Guzzle Options**

```php
$response = Http::withOptions([
    'debug' => true, // 啟用 Guzzle 的 debug 模式，會將 HTTP 請求與回應詳細資訊輸出到螢幕（通常用於除錯）
])->get('http://example.com/users'); // 發送 GET 請求，這次請求會套用上面的 Guzzle options
// 傳遞 Guzzle 參數
```

---

## 14. **全域 Options**

```php
use Illuminate\Support\Facades\Http; // 匯入 Laravel HTTP Facade
public function boot(): void
{
    Http::globalOptions([
        'allow_redirects' => false, // 設定所有 HTTP 請求預設不允許自動重導（Guzzle 參數）
    ]);
    // 設定所有請求的預設 Guzzle 參數，這裡是全域有效
}
// 這樣所有透過 Http 發送的請求都會套用這些 Guzzle options
``` 

---

## 15. **並列請求（Concurrent Requests）**

有時你需要同時發送多個 HTTP 請求（非依序），可用 **pool** 方法大幅提升效能。

```php
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

$responses = Http::pool(fn (Pool $pool) => [
    $pool->get('http://localhost/first'),   // 發送第一個 GET 請求
    $pool->get('http://localhost/second'),  // 發送第二個 GET 請求
    $pool->get('http://localhost/third'),   // 發送第三個 GET 請求
]);
// $responses 會是一個 Response 物件的陣列，依照上面順序存放每個請求的回應
// 例如 $responses[0] 是第一個網址的回應，$responses[1] 是第二個，以此類推
// 注意：$responses 不是「request 請求物件」的陣列，而是「response 回應物件」的陣列
// 因為 pool 方法會自動幫你發送所有請求，並把每個請求的回應（Response 物件）依序收集回來
// 這樣設計是因為開發者最常需要處理的是伺服器的回應內容，而不是送出的 request
// 所以 $responses[0]、$responses[1]... 都是 Illuminate\Http\Client\Response 物件，可以直接用來取得資料或狀態碼
```

### **命名請求**

```php
$responses = Http::pool(fn (Pool $pool) => [
    $pool->as('first')->get('http://localhost/first'),
    $pool->as('second')->get('http://localhost/second'),
    $pool->as('third')->get('http://localhost/third'),
]);
// 可用名稱存取回應
return $responses['first']->ok();
```

### **客製化 headers/middleware**

**pool** 不能鏈式 withHeaders/middleware，需在每個請求個別設定：
```php
$headers = [
    'X-Example' => 'example',
];
$responses = Http::pool(fn (Pool $pool) => [
    $pool->withHeaders($headers)->get('http://laravel.test/test'),
    $pool->withHeaders($headers)->get('http://laravel.test/test'),
]);
```

---

## 16. **HTTP Client Macro**

可自訂常用的 request 設定，方便全專案重複使用。

```php
use Illuminate\Support\Facades\Http;

// 在 AppServiceProvider boot 方法中註冊 macro
public function boot(): void
{
    Http::macro('github', function () {
        return Http::withHeaders([
            'X-Example' => 'example', // 這是自訂 header，名稱和值都可以依需求更換，這裡僅作為範例
        ])->baseUrl('https://github.com');
    });
}

// 之後可直接呼叫
$response = Http::github()->get('/');
// 會自動帶上 headers 與 baseUrl
```

---

## 17. **測試與假資料（Faking & Testing）**

### **假資料回應**

```php
use Illuminate\Support\Facades\Http;

Http::fake();
// 所有請求都回傳空 200 回應
$response = Http::post(/* ... */);
```

### **指定 URL 假資料**

```php
Http::fake([
    'github.com/*' => Http::response(['foo' => 'bar'], 200, $headers), // 當網址符合 github.com/* 時，回傳內容為陣列 ['foo' => 'bar']，狀態碼 200，並帶上 $headers
    'google.com/*' => Http::response('Hello World', 200, $headers),    // 當網址符合 google.com/* 時，回傳內容為字串 'Hello World'，狀態碼 200，並帶上 $headers
]);
// 指定不同網址回傳不同假資料，測試時不會真的發送 HTTP 請求
// 'github.com/*'、'google.com/*' 都是萬用字元路徑，可以對應多個網址
// Http::response(內容, 狀態碼, headers) 可自訂回傳內容、狀態碼與 header

```
### **萬用字元與 fallback**

```php
Http::fake([
    'github.com/*' => Http::response(['foo' => 'bar'], 200, ['Headers']), // 當網址符合 github.com/* 時，回傳內容為陣列 ['foo' => 'bar']，狀態碼 200，帶上 headers
    '*' => Http::response('Hello World', 200, ['Headers']),               // 其他所有網址（未被上面條件覆蓋到的）都回傳 'Hello World'，狀態碼 200，帶上 headers
]);
// '*' 是萬用字元，代表所有未指定網址都回傳這個假資料，確保所有請求都能被 fake
// 這樣設計可以避免測試時有漏網之魚，所有請求都能被攔截與控制回應

```

### **直接用字串/陣列/數字**

```php
Http::fake([
    'google.com/*' => 'Hello World',           // google.com/* 回傳字串 'Hello World'，狀態碼預設 200
    'github.com/*' => ['foo' => 'bar'],        // github.com/* 回傳陣列 ['foo' => 'bar']，狀態碼預設 200
    'chatgpt.com/*' => 200,                    // chatgpt.com/* 回傳空內容，狀態碼 200
]);
// 直接用字串/陣列/數字快速產生假回應，適合簡單測試
```

### **假例外**

```php
Http::fake([
    'github.com/*' => Http::failedConnection(), // 模擬 github.com/* 連線失敗（如網路斷線）
]);
// 模擬連線失敗

Http::fake([
    'github.com/*' => Http::failedRequest(['code' => 'not_found'], 404), // 模擬 github.com/* 回傳 404 錯誤，內容為 ['code' => 'not_found']
]);
// 模擬 404 例外
```

### **假回應序列**

```php
Http::fake([
    'github.com/*' => Http::sequence()         // 對 github.com/* 設定一個回應序列
        ->push('Hello World', 200)             // 第一次回傳 'Hello World'，狀態碼 200
        ->push(['foo' => 'bar'], 200)          // 第二次回傳陣列，狀態碼 200
        ->pushStatus(404),                     // 第三次回傳 404 狀態，內容為空
]);
// 依序回傳多個假回應，超過次數會丟例外

// 指定序列用完後預設回應
Http::fake([
    'github.com/*' => Http::sequence()
        ->push('Hello World', 200)             // 先回傳 'Hello World'
        ->push(['foo' => 'bar'], 200)          // 再回傳陣列
        ->whenEmpty(Http::response()),         // 序列用完後預設回傳空 200 回應
]);

// 全域假序列
Http::fakeSequence()
    ->push('Hello World', 200)                 // 第一次所有請求都回傳 'Hello World'
    ->whenEmpty(Http::response());             // 用完後預設回傳空 200 回應
```

### **假 callback**

```php
use Illuminate\Http\Client\Request;
Http::fake(function (Request $request) {
    // 可根據 $request 決定回應內容，例如根據網址、header、body 等動態產生假回應
    return Http::response('Hello World', 200); // 這裡所有請求都回傳 'Hello World'，狀態碼 200
});
```

### **驗證請求**

```php
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

Http::fake(); // 啟用假資料，攔截所有 HTTP 請求
Http::withHeaders([
    'X-First' => 'foo',                        // 設定自訂 header
])->post('http://example.com/users', [
    'name' => 'Taylor',                        // 傳送 name 欄位
    'role' => 'Developer',                     // 傳送 role 欄位
]);

Http::assertSent(function (Request $request) {
    // 驗證是否有發送指定內容的請求
    return $request->hasHeader('X-First', 'foo') && // 檢查 header
           $request->url() == 'http://example.com/users' && // 檢查網址
           $request['name'] == 'Taylor' &&                 // 檢查 name
           $request['role'] == 'Developer';                // 檢查 role
});
// 驗證是否有發送指定內容的請求
```

### **驗證未發送**

```php
Http::fake();
Http::post('http://example.com/users', [
    'name' => 'Taylor',
    'role' => 'Developer',
]);
Http::assertNotSent(function (Request $request) {
    // 驗證沒有發送到 http://example.com/posts 這個網址
    return $request->url() === 'http://example.com/posts';
});
// 驗證未發送特定請求
```

### **驗證發送次數**

```php
Http::fake();
Http::assertSentCount(5); // 驗證共發送 5 次請求
```

### **驗證完全未發送**

```php
Http::fake();
Http::assertNothingSent(); // 驗證完全沒有發送任何請求
```

### **記錄所有請求/回應**

```php
Http::fake([
    'https://laravel.com' => Http::response(status: 500), // laravel.com 回傳 500
    'https://nova.laravel.com/' => Http::response(),      // nova.laravel.com 回傳預設 200
]);
Http::get('https://laravel.com');
Http::get('https://nova.laravel.com/');
$recorded = Http::recorded(); // 取得所有請求與對應回應的陣列
[$request, $response] = $recorded[0]; // 第一筆請求與回應
// 取得所有請求與對應回應

// 可用 closure 過濾
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
$recorded = Http::recorded(function (Request $request, Response $response) {
    // 只取得網址不是 laravel.com 且回應成功的紀錄
    return $request->url() !== 'https://laravel.com' &&
           $response->successful();
});
```

### **防止未 fake 請求**

```php
use Illuminate\Support\Facades\Http;
Http::preventStrayRequests(); // 啟用防護，未 fake 的請求會丟例外
Http::fake([
    'github.com/*' => Http::response('ok'), // 只允許 github.com/*
]);
Http::get('https://github.com/laravel/framework'); // ok，因有 fake
Http::get('https://laravel.com'); // 會丟例外，因未 fake
```

---

## 18. **HTTP Client 事件**

Laravel 會在發送 HTTP 請求過程中觸發三個事件：
- **RequestSending**：發送前
  // 這個事件會在 HTTP 請求即將送出時觸發，可以取得即將發送的 request 物件
- **ResponseReceived**：收到回應後
  // 這個事件會在收到 HTTP 回應時觸發，可以同時取得 request 物件和 response 物件，方便比對請求與回應內容
- **ConnectionFailed**：連線失敗
  // 這個事件會在連線失敗（如網路斷線、DNS 錯誤）時觸發，可以取得 request 物件

每個事件都可取得 $request，ResponseReceived 可同時取得 $response。

```php
use Illuminate\Http\Client\Events\RequestSending; // 匯入 RequestSending 事件類別

class LogRequest
{
    /**
     * Handle the event.
     */
    public function handle(RequestSending $event): void
    {
        // $event->request ... 可記錄請求內容
        // 這裡可以取得即將發送的 request 物件，做日誌、驗證等用途
    }
}
// 可在 EventServiceProvider 註冊監聽器，讓這個事件處理器自動監聽 HTTP 請求發送前的事件 
