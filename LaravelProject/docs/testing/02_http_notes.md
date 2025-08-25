# 1. *Laravel HTTP 測試 筆記*

---

## 1.1 **簡介**

Laravel 提供流暢的 `API` 來模擬 `HTTP` 請求並檢查回應，適合 `Feature` 測試。可檢查 *狀態碼、標頭、內容、JSON 結構* 等。

```php
test('the application returns a successful response', function () {
    // 發送 GET 請求到首頁
    $response = $this->get('/');
    // 斷言回應狀態碼為 200（成功）
    $response->assertStatus(200);
});
```

---

## 1.2 **發送請求**

- 可用 `get、post、put、patch、delete` 方法模擬請求（不會真的發送 HTTP）：

```php
test('basic request', function () {
    // 定義一個測試案例，名稱為 'basic request'
    $response = $this->get('/'); // 發送 GET 請求到首頁
    $response->assertStatus(200); // 斷言回應狀態碼為 200（成功）
});
```

- 每個測試 *建議* 只發送一次請求。
- 測試時 `CSRF middleware` 會自動停用。
- 回傳 `Illuminate\Testing\TestResponse`，可用多種 *assertion*。

---

## 1.3 **自訂 Request Headers**

- 用 `withHeaders` 設定 _自訂標頭_ ：

```php
test('interacting with headers', function () {
    // 定義一個測試案例，名稱為 'interacting with headers'
    
    $response = $this->withHeaders([
        'X-Header' => 'Value', // 設定自訂 HTTP header
    ])->post('/user', ['name' => 'Sally']); // 發送 POST 請求到 /user，帶入 name 參數
    
    $response->assertStatus(201); // 斷言回應狀態碼為 201（建立成功）
});
```

---

## 1.4 **Cookies**

- 用 `withCookie` 或 `withCookies` 設定 *cookie*：

```php
test('interacting with cookies', function () {
    // 定義一個測試案例，名稱為 'interacting with cookies'

    // 設定單一 cookie 並發送 GET 請求到首頁
    $response = $this->withCookie('color', 'blue')->get('/');

    // 設定多個 cookies 並發送 GET 請求到首頁
    $response = $this->withCookies([
        'color' => 'blue',
        'name' => 'Taylor',
    ])->get('/');
});
```

---

## 1.5 **Session / Authentication**

- 用 `withSession` 預先設定 *session*：

```php
test('interacting with the session', function () {
    // 定義一個測試案例，名稱為 'interacting with the session'

    // 設定 session 變數 banned 為 false，並發送 GET 請求到首頁
    $response = $this->withSession(['banned' => false])->get('/');
});
```

---

- 用 `actingAs` 快速 __登入指定使用者__：

```php
use App\Models\User;

test('an action that requires authentication', function () {
    // 建立一個測試用的 User
    $user = User::factory()->create();

    // 以該 User 身份登入，並設定 session 變數 banned 為 false，發送 GET 請求到首頁
    $response = $this->actingAs($user)
        ->withSession(['banned' => false])
        ->get('/');
});
```

---

- 可指定 `guard`：

```php
// actingAs 第二個參數可指定使用哪個 guard（如 'web', 'api'）

$this->actingAs($user, 'web');
// 以 $user 身份登入，使用 'web' guard 驗證
```

---

## 1.6 **Debugging Responses**

- 用 `dump`、`dumpHeaders`、`dumpSession` 輸出 *除錯資訊*：

```php
test('basic test', function () {
    $response = $this->get('/');         // 發送 GET 請求到首頁
    $response->dumpHeaders();            // 輸出回應的 HTTP headers
    $response->dumpSession();            // 輸出 session 資料
    $response->dump();                   // 輸出回應內容（body）
});
```

---

- 用 `dd`、`ddHeaders`、`ddBody`、`ddJson`、`ddSession` 直接中斷並輸出：

```php
test('basic test', function () {
    $response = $this->get('/');      // 發送 GET 請求到首頁

    $response->dd();                  // 終止測試並輸出完整回應內容
    $response->ddHeaders();           // 終止測試並輸出 HTTP headers
    $response->ddBody();              // 終止測試並輸出回應 body
    $response->ddJson();              // 終止測試並輸出 JSON 內容（如有）
    $response->ddSession();           // 終止測試並輸出 session 資料
});
```

---

## 1.7 **Exception Handling**

- 用 `Exceptions::fake()` 可*攔截例外*，並用 `assertReported`/`assertNotReported` 驗證：

```php
use App\Exceptions\InvalidOrderException;
use Illuminate\Support\Facades\Exceptions;

test('exception is thrown', function () {
    Exceptions::fake(); // 偽造例外回報，方便測試例外是否被回報

    $response = $this->get('/order/1'); // 發送 GET 請求，觸發例外

    Exceptions::assertReported(InvalidOrderException::class); // 斷言 InvalidOrderException 有被回報

    Exceptions::assertReported(function (InvalidOrderException $e) {
        // 斷言回報的 InvalidOrderException 內容正確
        return $e->getMessage() === 'The order was invalid.';
        // 判斷這個例外 $e 的訊息是否等於 'The order was invalid.'。
        // $e->getMessage() 會取得例外物件的錯誤訊息。
        // === 'The order was invalid.' 是比對訊息內容。
        // 保你回報的例外，訊息內容正確無誤。
        // 如果訊息不是 'The order was invalid.'，這個斷言就會失敗。
    });
});


// 測試某些情境下，程式不應該回報例外。
// 確保程式流程正確，不會多報錯或漏報錯。

// 斷言 InvalidOrderException 沒有被回報
Exceptions::assertNotReported(InvalidOrderException::class);
// 確認「InvalidOrderException」這個特定例外沒有被回報。

// 斷言沒有任何例外被回報
Exceptions::assertNothingReported();
// 確認完全沒有任何例外被回報（不只 InvalidOrderException）。
```

---

- *完全停用* `exception handling`：

```php
$response = $this->withoutExceptionHandling()->get('/');
// 關閉 Laravel 的例外處理，讓例外直接拋出（方便測試時看到詳細錯誤訊息或堆疊）
```
---

- *停用* `deprecation handling`（將 __`警告`轉為`例外`__）：

```php
$response = $this->withoutDeprecationHandling()->get('/');
// 關閉 Laravel 的棄用警告處理，讓 deprecation（棄用）警告直接顯示，方便測試時追蹤哪些功能已經不建議使用
```
- `deprecation`（棄用）是指 __某些功能或方法`未來可能會被移除`__，Laravel 會發出警告提醒你改用新寫法。

---

- 驗證 `closure` *會* 丟出例外：

```php
// 使用 assertThrows 方法來測試是否會拋出指定的例外
// 第一個參數：要執行的 closure 函數，這裡會執行 ProcessOrder 的 execute 方法
// 第二個參數：預期拋出的例外類別名稱
$this->assertThrows(
    fn () => (new ProcessOrder)->execute(),  // 執行會拋出例外的程式碼
    OrderInvalid::class                      // 預期拋出的例外類別
);
```

- 驗證 `closure` 並*檢查例外內容*：

```php
// 使用 assertThrows 方法來測試例外，並驗證例外的具體內容
// 第一個參數：要執行的 closure 函數
// 第二個參數：驗證例外的 closure 函數，用來檢查例外的屬性或方法
$this->assertThrows(
    fn () => (new ProcessOrder)->execute(),                    
    // 執行會拋出例外的程式碼
    fn (OrderInvalid $e) => $e->orderId() === 123;           
    // 驗證例外的 orderId 是否等於 123
);
```

- 驗證 `closure` *不會* 丟出例外：

```php
$this->assertDoesntThrow(fn () => (new ProcessOrder)->execute()); 
// 驗證 closure 不會丟出任何例外
``` 

---

# 2. *Testing JSON APIs*

## 2.1 **發送 JSON 請求**

- 可用  `json`、
        `getJson`、
        `postJson`、
        `putJson`、
        `patchJson`、
        `deleteJson`、
        `optionsJson` 方法發送 JSON 請求。
        
- 可直接 *傳遞資料* 與 *headers*。

```php
test('making an api request', function () {
    // 定義一個測試案例，名稱為 'making an api request'
    $response = $this->postJson('/api/user', ['name' => 'Sally']); // 發送 POST JSON 請求到 /api/user，帶入 name 參數
    $response
        ->assertStatus(201) // 斷言回應狀態碼為 201（建立成功）
        ->assertJson([ // 斷言回應包含指定的 JSON 片段
            'created' => true, // 檢查 created 欄位值為 true
        ]);
        // 斷言回應的 JSON 內容包含 'created' => true 這個片段，通常用在 Laravel 的 HTTP 測試回應物件。
});
```

---

- 回應資料可直接用*陣列方式*存取：

```php
test('created field is true in response', function () {
    // 假設這是一個 API 請求，回傳陣列包含 'created' 欄位
    $response = [
        'created' => true,
        'id' => 1,
        'name' => 'Alice',
    ];

    // 使用 Pest 語法斷言回應陣列中的 created 欄位值為 true
    expect($response['created'])->toBeTrue();
    // 直接斷言 $response['created'] 的值是 true，通常用在 Pest 測試。
});
```

- `assertJson` 只要**片段存在**即可通過。

---

## 2.2 **精確 JSON 比對**

- 用 `assertExactJson` 驗證回應 JSON 必須*完全相符*：

```php
test('asserting an exact json match', function () {
    // 定義一個測試案例，名稱為 'asserting an exact json match'
    $response = $this->postJson('/user', ['name' => 'Sally']); // 發送 POST JSON 請求到 /user，帶入 name 參數
    $response
        ->assertStatus(201) // 斷言回應狀態碼為 201（建立成功）
        ->assertExactJson([ // 斷言回應 JSON 必須完全相符（不能有其他欄位）
            'created' => true, // 檢查 created 欄位值為 true
        ]);
});
```

---

## 2.3 **JSON 路徑斷言**

- 用 `assertJsonPath` 驗證 *指定路徑的值*：

```php
test('asserting a json path value', function () {
    // 定義一個測試案例，名稱為 'asserting a json path value'
    $response = $this->postJson('/user', ['name' => 'Sally']); // 發送 POST JSON 請求到 /user，帶入 name 參數
    $response
        ->assertStatus(201) // 斷言回應狀態碼為 201（建立成功）
        ->assertJsonPath('team.owner.name', 'Darian'); // 斷言 JSON 路徑 team.owner.name 的值為 'Darian'
});
```

---

- 也可用 `closure` *動態驗證*：

```php
$response->assertJsonPath('team.owner.name', fn (string $name) => strlen($name) >= 3); // 使用 closure 動態驗證 JSON 路徑值，檢查名稱長度是否大於等於 3
```

---

## 2.4 **Fluent JSON 測試**

- 用 `assertJson(fn (AssertableJson $json) => ...)` 進行 *流暢式* JSON 斷言：

```php
use Illuminate\Testing\Fluent\AssertableJson;

test('fluent json', function () {
    // 定義一個測試案例，名稱為 'fluent json'
    $response = $this->getJson('/users/1'); // 發送 GET JSON 請求到 /users/1
    $response
        ->assertJson(fn (AssertableJson $json) => // 使用流暢式 JSON 斷言，傳入 closure 函數
            $json->where('id', 1) // 斷言 id 欄位值為 1
                ->where('name', 'Victoria Faith') // 斷言 name 欄位值為 'Victoria Faith'
                ->where('email', fn (string $email) => str($email)->is('victoria@gmail.com')) // 使用 closure 驗證 email 格式
                ->whereNot('status', 'pending') // 斷言 status 欄位值不為 'pending'
                ->missing('password') // 斷言 password 欄位不存在
                ->etc() // 允許該層級有其他屬性存在
        );
});
```

- `etc()` **允許** 該層級`有`其他屬性存在。
- 不加 `etc()` 會 **強制** 該層級`不能有`未驗證的屬性。

**該層級** 指的是 _目前斷言的 JSON 結構所在_ 的「__物件層級__」。

假設 `API 回傳`內容如下：

```json
{
  "id": 1,
  "name": "Victoria Faith",
  "email": "victoria@gmail.com",
  "status": "active",
  "created_at": "2025-08-10T12:00:00Z"
}
```

如果你只驗證 `id`、`name`、`email`、`status`，  
但回傳還有 `created_at` 等其他欄位：

- **加上 `etc()`**：_允許_ 這個物件還有其他欄位（如 `created_at`），不會報錯。
- **不加 `etc()`**：_只允許_ 你驗證的欄位存在，其他欄位（如 `created_at`）就會導致測試失敗。

__總結__

- 「_該層級_」就是你目前驗證的 JSON 物件（例如`整個回傳物件、某個陣列元素、某個巢狀物件`）。
- `etc()` 讓你*只驗證重點欄位，其他欄位可忽略*。

---

## 2.5 **屬性存在/不存在斷言**

- `has`/`missing` 驗證 *屬性* __存在/不存在__：

```php
$response->assertJson(fn (AssertableJson $json) => // 使用流暢式 JSON 斷言
    $json->has('data') // 斷言存在 'data' 屬性
        ->missing('message') // 斷言不存在 'message' 屬性
);
```

---

- `hasAll`/`missingAll` 驗證 *多個屬性*：

```php
$response->assertJson(fn (AssertableJson $json) => // 使用流暢式 JSON 斷言
    $json->hasAll(['status', 'data']) // 斷言同時存在 'status' 和 'data' 屬性
        ->missingAll(['message', 'code']) // 斷言同時不存在 'message' 和 'code' 屬性
);
```

---

- `hasAny` 驗證 *至少有一個* 屬性存在：

```php
$response->assertJson(fn (AssertableJson $json) => // 使用流暢式 JSON 斷言
    $json->has('status') // 斷言存在 'status' 屬性
        ->hasAny('data', 'message', 'code') // 斷言至少存在 'data'、'message'、'code' 中的一個屬性
);
```

---

## 2.6 **JSON 集合斷言**

- 驗證回應為 *多筆資料* 時，可用 `has/first` 斷言：

```php
$response
    ->assertJson(fn (AssertableJson $json) => // 使用流暢式 JSON 斷言
        $json->has(3) // 斷言回應包含 3 個項目
            ->first(fn (AssertableJson $json) => // 對第一個項目進行斷言
                $json->where('id', 1) // 斷言第一個項目的 id 為 1
                    ->where('name', 'Victoria Faith') // 斷言第一個項目的 name 為 'Victoria Faith'
                    ->where('email', fn (string $email) => str($email)->is('victoria@gmail.com')) // 驗證 email 格式
                    ->missing('password') // 斷言第一個項目沒有 password 欄位
                    ->etc() // 允許該層級有其他屬性存在
            )
    );
```

---

## 2.7 **巢狀集合斷言與作用域**

因為**回應**是 JSON，
所以「_集合_」指的是 JSON 的 __陣列（array）__，
「_物件_」指的是 JSON 的 __物件（object）__。

_集合（array）_：用中括號 `[]`，是一組**物件或值**的列表：`"users": [ {...}, {...} ]`
_物件（object）_：用大括號 `{}`，是一組 **key-value** 配對，像 `{ "id": 1, "name": "Victoria Faith" }`

---

*具名集合* 是`指 JSON 回應裡，用「名稱」標示的陣列資料`，例如 `users、items、products` 這種欄位，它們的 __`值`是一個`陣列（集合）`，而`不是`單一物件__。

```php
{
  "meta": { ... },
  "users": [
    { "id": 1, "name": "Victoria Faith", "email": "victoria@gmail.com" },
    { "id": 2, "name": "Bob", "email": "bob@gmail.com" },
    { "id": 3, "name": "Alice", "email": "alice@gmail.com" }
  ]
}
// 這裡的 users 就是「具名集合」，
// 你可以用 has('users', 3) 斷言它有 3 個項目，
// 再用 has('users.0', ...) 斷言集合裡的內容。
```

---

- 回應為 *具名集合* 時，可用 `has` 斷言 *集合數量與內容*：

```php
$response
    ->assertJson(fn (AssertableJson $json) => // 使用流暢式 JSON 斷言
        $json->has('meta') // 斷言存在 'meta' 屬性
            ->has('users', 3) // 斷言 'users' 屬性包含 3 個項目
            ->has('users.0', fn (AssertableJson $json) => // 對 'users' 陣列的第一個項目進行斷言
                $json->where('id', 1) // 斷言第一個使用者的 id 為 1
                    ->where('name', 'Victoria Faith') // 斷言第一個使用者的 name 為 'Victoria Faith'
                    ->where('email', fn (string $email) => str($email)->is('victoria@gmail.com')) // 驗證 email 格式
                    ->missing('password') // 斷言第一個使用者沒有 password 欄位
                    ->etc() // 允許該層級有其他屬性存在
            )
    );
```

---

- 也可直接用 `has('users', 3, closure)` 針對 *第一筆* 斷言：

```php
$response
    ->assertJson(fn (AssertableJson $json) => // 使用流暢式 JSON 斷言
        $json->has('meta') // 斷言存在 'meta' 屬性
            ->has('users', 3, fn (AssertableJson $json) => // 對 'users' 陣列進行斷言，指定數量為 3，並對第一個項目進行驗證
                $json->where('id', 1) // 斷言第一個使用者的 id 為 1
                    ->where('name', 'Victoria Faith') // 斷言第一個使用者的 name 為 'Victoria Faith'
                    ->where('email', fn (string $email) => str($email)->is('victoria@gmail.com')) // 驗證 email 格式
                    ->missing('password') // 斷言第一個使用者沒有 password 欄位
                    ->etc() // 允許該層級有其他屬性存在
            )
    );
```

---

## 2.8 **JSON 型別斷言**

- 用 `whereType`/`whereAllType` 驗證 *屬性型別*：

```php
$response->assertJson(fn (AssertableJson $json) => // 使用流暢式 JSON 斷言
    $json->whereType('id', 'integer') // 斷言 'id' 欄位的型別為整數
        ->whereAllType([ // 斷言多個欄位的型別
            'users.0.name' => 'string', // 斷言 'users' 陣列第一個項目的 'name' 欄位型別為字串
            'meta' => 'array' // 斷言 'meta' 欄位型別為陣列
        ])
);
```

---

- 可用 `|` 或`陣列`指定多型別：

```php
$response->assertJson(fn (AssertableJson $json) => // 使用流暢式 JSON 斷言
    $json->whereType('name', 'string|null') // 斷言 'name' 欄位型別為字串或 null（使用 | 分隔多型別）
        ->whereType('id', ['string', 'integer']) // 斷言 'id' 欄位型別為字串或整數（使用陣列指定多型別）
);
```


- 支援型別：  `string`、
            `integer`、
            `double`、
            `boolean`、
            `array`、
            `null`。 

---

# 3. *檔案上傳測試*（Testing File Uploads）

## 3.1 **使用 UploadedFile 與 Storage 假資料測試上傳**

Laravel 提供 `Illuminate\Http\UploadedFile` 的 `fake` 方法，可產生 *假檔案*，搭配 `Storage` facade 的 `fake` 方法，能 _輕鬆測試檔案上傳流程_。

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('avatars can be uploaded', function () {
    // 定義一個測試案例，名稱為 'avatars can be uploaded'
    Storage::fake('avatars'); // 建立名為 avatars 的假儲存磁碟

    $file = UploadedFile::fake()->image('avatar.jpg'); // 產生假圖片檔案

    $response = $this->post('/avatar', [ // 發送 POST 請求到 /avatar，上傳檔案
        'avatar' => $file, // 將假檔案作為 avatar 欄位值
    ]);
    // hashName() 是 Laravel 內建的上傳檔案物件方法，
    Storage::disk('avatars')->assertExists($file->hashName()); // 斷言檔案已成功儲存到 avatars 磁碟
});
```

---

### 3.1.1 **斷言檔案不存在**

可用 `assertMissing` 斷言檔案 *不存在*：

```php
Storage::fake('avatars'); // 建立名為 avatars 的假儲存磁碟

// ... 其他測試程式碼 ...

Storage::disk('avatars')->assertMissing('missing.jpg'); // 斷言檔案 'missing.jpg' 不存在於 avatars 磁碟
```

---

## 3.2 **自訂假檔案屬性**

可自訂假檔案的`寬度、高度、大小（KB）`，以測試 _驗證規則_：

```php
$width = 200;    // 定義圖片寬度為 200px
$height = 200;   // 定義圖片高度為 200px

$file = UploadedFile::fake()->image('avatar.jpg', $width, $height)->size(100);
// 產生一個 200x200、大小為 100KB 的假圖片檔案
```

---

也可產生 *其他類型* 檔案：

```php
$sizeInKilobytes = 100; // 定義檔案大小為 100KB
$file = UploadedFile::fake()->create('document.pdf', $sizeInKilobytes);
```

---

可自訂 `MIME type`：

_MIME type_（媒體類型）是用來`描述檔案內容格式的標準字串`，  例如 `application/pdf` 代表 PDF 檔案，`image/jpeg` 代表 JPEG 圖片。瀏覽器和伺服器會根據` MIME type` 判斷 __如何處理或顯示檔案__。

```php
UploadedFile::fake()->create(
    'document.pdf', $sizeInKilobytes, 'application/pdf' // 產生指定大小和 MIME 型別的假 PDF 檔案
);
```

---

# 4. *View 測試*（Testing Views）

## 4.1 **直接渲染 View 並斷言內容**

可用 `view` 方法 *直接渲染 view，不需模擬 HTTP 請求*。回傳 `Illuminate\Testing\TestView` 實例，可用多種斷言方法。
你可以在測試時直接載入並渲染 `Blade view`，__不用像平常那樣發送 HTTP 請求__（例如 `$this->get('/')`），這樣可以更快、更方便測試 view 的內容和邏輯。

__不會看到畫面__
在測試時，view 方法 _只會回傳 view 的內容（HTML）_，
讓你可以用程式斷言檢查內容，
不會真的在瀏覽器顯示畫面。

```php
test('a welcome view can be rendered', function () {
    // 定義一個測試案例，名稱為 'a welcome view can be rendered'
    $view = $this->view('welcome', ['name' => 'Taylor']); // 直接渲染 'welcome' view，並傳入 name 變數

    $view->assertSee('Taylor'); // 斷言渲染後的內容包含 'Taylor' 字串
});
```

---

_TestView_ 支援：

  - `assertSee`：斷言 view 內容 _包含_ 指定文字或 HTML。
  - `assertSeeInOrder`：斷言多個文字 _依指定順序_ 出現在 view 內容中。

  - `assertSeeText`：斷言 view 內容 _包含指定純文字_（忽略 HTML 標籤）。
  - `assertSeeTextInOrder`：斷言 _多個純文字依指定順序_ 出現在 view 內容中。

  - `assertDontSee`：斷言 view 內容 __不包含__ 指定文字或 HTML。
  - `assertDontSeeText`：斷言 view 內容 __不包含__ 指定純文字。

---

取得 _渲染後內容_：

```php
use Tests\TestCase;

class ViewTest extends TestCase
{
    public function testWelcomeViewContainsText()
    {
        // 直接渲染 welcome.blade.php，不需模擬 HTTP 請求
        $contents = (string) $this->view('welcome'); // 取得渲染後的 view 內容，並轉換為字串

        // 斷言 view 內容包含特定文字
        $this->assertStringContainsString('Laravel', $contents);
    }
}
```

---

## 4.2 **共享錯誤訊息**（withViewErrors）

有些 view 依賴 _全域 error bag_，可用 `withViewErrors` __注入錯誤訊息__：

```php
$view = $this->withViewErrors([ // 注入錯誤訊息到 view
    'name' => ['Please provide a valid name.'] // 設定 'name' 欄位的錯誤訊息
])->view('form'); // 渲染 'form' view

$view->assertSee('Please provide a valid name.'); // 斷言渲染後的內容包含錯誤訊息
```

- 這樣可以在測試時模擬`表單驗證失敗的情境`，檢查 view 是否正確顯示錯誤訊息。

---

## 4.3 **渲染 Blade 字串與元件**

可用 `blade` 方法渲染 *原始 Blade 字串*：

```php
$view = $this->blade( // 渲染原始 Blade 字串
    '<x-component :name="$name" />', // Blade 字串內容，包含一個 component
    ['name' => 'Taylor'] // 傳入的變數資料
);

$view->assertSee('Taylor'); // 斷言渲染後的內容包含 'Taylor' 字串
```

---

渲染 `Blade component`：

```php
$view = $this->component(Profile::class, ['name' => 'Taylor']); // 渲染 Profile 元件，並傳入 name 變數

$view->assertSee('Taylor'); // 斷言渲染後的內容包含 'Taylor' 字串
``` 

---

# 5. *可用斷言方法總覽*（Available Assertions）

## 5.1 **Response 斷言方法**

Laravel 的 `Illuminate\Testing\TestResponse` 類別，針對 __HTTP 回應__ 提供大量自訂斷言方法，適用於 `json`、`get`、`post`、`put`、`delete` 等測試方法回傳的 _response_。

---

### 5.1.1 *HTTP 狀態碼斷言*

```php
$response->assertSuccessful(); // 2xx
$response->assertOk(); // 200
$response->assertCreated(); // 201
$response->assertAccepted(); // 202

$response->assertMovedPermanently(); // 301
$response->assertFound(); // 302

$response->assertClientError(); // 4xx
$response->assertBadRequest(); // 400
$response->assertUnauthorized(); // 401
$response->assertPaymentRequired(); // 402
$response->assertForbidden(); // 403
$response->assertNotFound(); // 404
$response->assertMethodNotAllowed(); // 405
$response->assertRequestTimeout(); // 408
$response->assertConflict(); // 409
$response->assertGone(); // 410
$response->assertUnsupportedMediaType(); // 415
$response->assertUnprocessable(); // 422
$response->assertTooManyRequests(); // 429

$response->assertServerError(); // 5xx
$response->assertInternalServerError(); // 500
$response->assertServiceUnavailable(); // 503

$response->assertStatus($code); // 任意狀態碼
```

---

### 5.1.2 *Cookie 斷言*

```php
$response->assertCookie($cookieName, $value = null); // 存在指定 cookie
$response->assertCookieMissing($cookieName); // 不存在指定 cookie

$response->assertCookieExpired($cookieName); // cookie 已過期
$response->assertCookieNotExpired($cookieName); // cookie 未過期

$response->assertPlainCookie($cookieName, $value = null); // 未加密 cookie
```

---

### 5.1.3 *Header 斷言*

```php
$response->assertHeader($headerName, $value = null); // 存在 header
$response->assertHeaderMissing($headerName); // 不存在 header
$response->assertLocation($uri); // Location header
```

---

### 5.1.4 *內容斷言*

```php
$response->assertContent($value); // 內容完全相符
$response->assertNoContent($status = 204); // 無內容

$response->assertDownload(); // 為下載回應
$response->assertDownload('image.jpg'); // 指定檔名

$response->assertStreamed(); // 為串流回應
$response->assertStreamedContent($value); // 串流內容
```

---

### 5.1.5 *HTML 與文字斷言*

```php
$response->assertSee($value, $escape = true); // 包含字串
$response->assertDontSee($value, $escape = true); // 不包含字串

$response->assertSeeText($value, $escape = true); // 純文字包含
$response->assertDontSeeText($value, $escape = true); // 純文字不包含

$response->assertSeeInOrder(array $values, $escape = true); // 順序包含
$response->assertSeeTextInOrder(array $values, $escape = true); // 純文字順序包含
```

---

### 5.1.6 *JSON 斷言*

```php
$response->assertJson(array $data, $strict = false); // 包含 JSON 片段
$response->assertExactJson(array $data); // 完全相符

$response->assertJsonFragment(array $data); // 包含片段
$response->assertJsonMissing(array $data); // 不包含片段
$response->assertJsonMissingExact(array $data); // 不包含完全片段

$response->assertJsonCount($count, $key = null); // 陣列數量
$response->assertJsonIsArray(); // 為陣列

$response->assertJsonIsObject(); // 為物件

$response->assertJsonPath($path, $expectedValue); // 指定路徑值
$response->assertJsonMissingPath($path); // 路徑不存在

$response->assertJsonStructure(array $structure); // 結構
$response->assertExactJsonStructure(array $data); // 嚴格結構
```

---

__JSON 結構範例__：

```php
$response->assertJsonStructure([
    'user' => [
        'name',
    ]
]);

$response->assertJsonStructure([
    'user' => [
        '*' => [
            'name', 'age', 'location'
        ]
    ]
]);
```

---

### 5.1.7 *驗證錯誤斷言*

```php
$response->assertJsonValidationErrors(array $data, $responseKey = 'errors'); // JSON 驗證錯誤
$response->assertJsonValidationErrorFor(string $key, $responseKey = 'errors'); // 指定欄位錯誤

$response->assertJsonMissingValidationErrors($keys); // 無驗證錯誤

$response->assertValid(); // 無驗證錯誤
$response->assertValid(['name', 'email']); // 指定欄位無錯誤

$response->assertInvalid(['name', 'email']); // 指定欄位有錯誤
$response->assertInvalid([
    'name' => 'The name field is required.', // 斷言 name 欄位驗證失敗，錯誤訊息正確
    'email' => 'valid email address',        // 斷言 email 欄位驗證失敗，錯誤訊息正確
]);
// 用於測試表單驗證失敗時，回應是否包含指定欄位的錯誤訊息
$response->assertOnlyInvalid(['name', 'email']); // 僅這些欄位有錯誤
```

---

### 5.1.8 *Session 斷言*

```php
$response->assertSessionHas($key, $value = null); // session 有資料
$response->assertSessionHas($key, function ($value) { return ...; }); // 用 closure 驗證

$response->assertSessionHasInput($key, $value = null); // session 有輸入
$response->assertSessionHasInput($key, function ($value) { return ...; });

$response->assertSessionHasAll(array $data); // 多筆資料

$response->assertSessionHasErrors(array $keys = [], $format = null, $errorBag = 'default'); // 有錯誤
$response->assertSessionHasErrors(['name', 'email']);
$response->assertSessionHasErrors([
    'name' => 'The given name was invalid.'
]);

$response->assertSessionHasErrorsIn($errorBag, $keys = [], $format = null); // 指定 error bag

$response->assertSessionHasNoErrors(); // 無錯誤
$response->assertSessionDoesntHaveErrors($keys = [], $format = null, $errorBag = 'default'); // 指定欄位無錯誤
$response->assertSessionMissing($key); // session 無資料
```

---

### 5.1.9 *Redirect 斷言*

```php
$response->assertRedirect($uri = null); // 重新導向

$response->assertRedirectBack(); // 返回上一頁
$response->assertRedirectBackWithErrors(array $keys = [], $format = null, $errorBag = 'default'); // 返回且有錯誤
$response->assertRedirectBackWithoutErrors(); // 返回且無錯誤

$response->assertRedirectContains($string); // URI 包含字串

$response->assertRedirectToRoute($name, $parameters = []); // 導向 route
$response->assertRedirectToSignedRoute($name = null, $parameters = []); // 導向簽名 route
```

---

### 5.1.10 *View 斷言*

```php
$response->assertViewHas($key, $value = null); // view 有資料
$response->assertViewHas('user', function (User $user) { return $user->name === 'Taylor'; }); // 用 closure 驗證
$response->assertViewHasAll(array $data); // 多筆資料
$response->assertViewMissing($key); // view 無資料
$response->assertViewIs($value); // 指定 view
```

---

__view 資料可直接存取__：

```php
expect($response['name'])->toBe('Taylor');
// 直接存取 view 回傳的資料欄位，並斷言其值是否正確
```

---

## 5.2 **認證相關斷言**

- *注意*：這些方法 __直接呼叫__ 於 **測試類別本身**，不是 `response 實例` 。

如 `TestCase` 或你自己寫的 `UserTest、ExampleTest`，
在這些類別裡可以直接用 `$this->assertAuthenticated()` 這種方法，
而不是 `$response->assertAuthenticated()`。

```php
$this->assertAuthenticated($guard = null); // 已認證
$this->assertGuest($guard = null); // 未認證
$this->assertAuthenticatedAs($user, $guard = null); // 指定 user 已認證
```

---

## 5.3 **驗證相關斷言**

```php
$response->assertValid(); // 無驗證錯誤
$response->assertValid(['name', 'email']); // 指定欄位無錯誤
$response->assertInvalid(['name', 'email']); // 指定欄位有錯誤
$response->assertInvalid([
    'name' => 'The name field is required.',
    'email' => 'valid email address',
]);
```

---

# 6. *測試資料庫處理*（Database Testing）

## 6.1 **測試資料庫 Traits**

Laravel 提供多種 traits 來處理測試中的`資料庫操作`：

---

### 6.1.1 *RefreshDatabase*

`每個測試後`，**重新建立** 資料庫結構（推薦用於 __功能測試__ ）：

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase; // 使用 RefreshDatabase trait，每個測試後重新建立資料庫結構
    
    // 測試方法...
}
```

---

### 6.1.2 *DatabaseTransactions*

`每個測試後`，**回滾**資料庫變更（較快但需注意隔離）：

__概念__  
使用 `DatabaseTransactions` trait 後，  
每個測試方法執行完畢，Laravel 會自動把資料庫的所有變更「_回滾_」掉（rollback），  
讓_資料庫回到`測試前`的狀態_，確保每次測試都是乾淨的環境。

__為什麼要這樣做？__  

- 測試資料`不會殘留`，避免影響其他測試。
- 測試速度較快，因為不用每次都重建資料庫。
- 但要 _注意_：如果多個測試 __同時__ 操作`同一筆資料`，可能會有隔離問題（資料互相影響）。

__用途__
  
適合大多數資料庫測試，確保測試資料`不會污染`正式資料或其他測試。


```php
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    use DatabaseTransactions; // 使用 DatabaseTransactions trait，每個測試後回滾資料庫變更
    
    // 測試方法...
}
```

---

### 6.1.3 *DatabaseMigrations*

`每個測試後`，執行資料庫**遷移**：

__概念__
  
使用 `DatabaseMigrations` trait 後，  
每個測試方法執行 _前_ 都會 __重新執行一次資料庫遷移（migrate）__，  
確保`資料庫結構`是 _最新、乾淨_ 的狀態。

__用途__
  
- 適合需要 _完全重建資料庫結構_ 的測試。
- 可避免資料表結構或資料 _殘留影響測試結果_。
- 速度比 `DatabaseTransactions` 慢，但隔離性更好。

```php
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ExampleTest extends TestCase
{
    use DatabaseMigrations; // 使用 DatabaseMigrations trait，每個測試後執行資料庫遷移
    
    // 測試方法...
}
```

---

## 6.2 **測試資料庫斷言**

```php
// 斷言資料庫中有記錄
$this->assertDatabaseHas('users', [ // 檢查 'users' 資料表中是否存在符合條件的記錄
    'email' => 'sally@example.com', // 條件：email 欄位值為 'sally@example.com'
]);

// 斷言資料庫中沒有記錄
$this->assertDatabaseMissing('users', [ // 檢查 'users' 資料表中是否不存在符合條件的記錄
    'email' => 'sally@example.com', // 條件：email 欄位值為 'sally@example.com'
]);

// 斷言資料庫記錄數量
$this->assertDatabaseCount('users', 5); // 檢查 'users' 資料表中的記錄數量是否為 5

// 斷言資料庫記錄數量在範圍內
$this->assertDatabaseCount('users', 3, 7); // 檢查 'users' 資料表中的記錄數量是否在 3 到 7 之間
```

---

# 7. *測試環境設定*（Testing Environment）

## 7.1 **環境變數設定**

建立 `.env.testing` 檔案來設定 _測試環境_：

```php
APP_ENV=testing           # 設定應用程式環境為 testing（測試模式）
DB_CONNECTION=sqlite      # 資料庫連線使用 sqlite
DB_DATABASE=:memory:      # 資料庫使用記憶體（不會產生實體檔案）
CACHE_DRIVER=array        # 快取使用 array（只在記憶體，測試用）
SESSION_DRIVER=array      # Session 使用 array（只在記憶體，測試用）
QUEUE_DRIVER=sync         # 隊列使用 sync（同步執行，不排入背景）
```

---

## 7.2 **測試資料庫設定**

在 `config/database.php` 中設定 _測試資料庫_：

```php
'connections' => [
    'testing' => [
        'driver' => 'sqlite',      // 使用 SQLite 資料庫
        'database' => ':memory:',  // 使用記憶體資料庫（不會寫入檔案，測試結束即消失）
        'prefix' => '',            // 無資料表前綴
    ],
],
// 這是 Laravel 測試環境常用的資料庫設定，速度快且不會污染正式資料
```

---

## 7.3 **測試配置檔案**

建立 `phpunit.xml` 來設定 _測試環境_：

```xml

<!--
這是設定 Laravel 測試環境的 .env 配置範例，
指定 APP_ENV 為 testing，資料庫連線使用 SQLite 並採用記憶體模式。
-->

<!-- 裡面的 <php> 標籤用來設定測試時的環境變數（env），
不是一般的 PHP 程式碼格式，而是 XML 格式，
專門給 PHPUnit 使用。 -->

<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
</php>
```

---

# 8. *測試隔離與資料準備*（Test Isolation & Data Preparation）

## 8.1 **測試隔離機制**

### 8.1.1 *使用 setUp 和 tearDown*

```php
class ExampleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); // 呼叫父類別的 setUp 方法
        // 每個測試前的準備工作
    }
    
    protected function tearDown(): void
    {
        // 每個測試後的清理工作
        parent::tearDown(); // 呼叫父類別的 tearDown 方法
    }
}
```
- `setUp`：每個測試執行 _前_ 自動呼叫，可用來`初始化資料、設定環境`。
- `tearDown`：每個測試執行 _後_ 自動呼叫，可用來`清理資源、重設狀態`。
- 這樣可以確保每個測試都是獨立執行，互不影響，達到「**測試隔離**」效果。

---

### 8.1.2 *使用 RefreshDatabase 確保隔離*

```php
use RefreshDatabase; // 引入 RefreshDatabase trait

public function test_user_can_be_created()
{
    // 這個測試會使用乾淨的資料庫
    $user = User::factory()->create(); // 使用 Factory 建立一個測試使用者
    $this->assertDatabaseHas('users', ['id' => $user->id]); // 斷言資料庫中存在該使用者記錄
}
```

---

## 8.2 **測試資料準備**

### 8.2.1 *使用 Factory 建立測試資料*

```php
// 建立單一模型
$user = User::factory()->create(); // 建立一個使用者並儲存到資料庫

// 建立多個模型
$users = User::factory()->count(3)->create(); // 建立 3 個使用者並儲存到資料庫

// 建立特定狀態的模型
$adminUser = User::factory()->admin()->create(); // 建立一個具有 admin 狀態的使用者並儲存到資料庫

// 建立但不儲存到資料庫
$user = User::factory()->make(); // 建立一個使用者模型實例，但不儲存到資料庫
```

---

### 8.2.2 *使用 Seeder 準備測試資料*

```php
use DatabaseSeeder; // 引入 DatabaseSeeder 類別

class ExampleTest extends TestCase
{
    // trait 是用 use TraitName; 放在 class 裡，
    use RefreshDatabase; // 使用 RefreshDatabase trait
    // 這個 trait 會在每個測試前自動重設資料庫（執行 migrate），
    // 確保每次測試都是乾淨的資料庫狀態，避免資料殘留或互相影響。
    // 適合需要資料庫隔離的測試。

    public function test_with_seeded_data()
    {
        $this->seed(DatabaseSeeder::class); // 執行 DatabaseSeeder，將預設測試資料寫入資料庫
        // 這樣可以在測試前準備好需要的資料，方便測試資料查詢、驗證等邏輯

        // 測試邏輯...
    }
}
```

---

# 9. *效能測試*（Performance Testing）

## 9.1 **測試執行時間斷言**

```php
// 斷言測試執行時間不超過指定秒數
$this->assertLessThan(0.1, function() { // 斷言執行時間少於 0.1 秒
    // 要測試的程式碼
    $response = $this->get('/api/users'); // 發送 GET 請求到 /api/users
    $response->assertStatus(200); // 斷言回應狀態碼為 200
});

// 使用 Laravel 的效能測試輔助方法
$start = microtime(true); // 記錄開始時間
$response = $this->get('/api/users'); // 發送 GET 請求到 /api/users
$end = microtime(true); // 記錄結束時間

$this->assertLessThan(0.1, $end - $start); // 斷言執行時間少於 0.1 秒
```

---

## 9.2 **記憶體使用測試**

```php
// 斷言記憶體使用量不超過指定值
$memoryBefore = memory_get_usage(); // 記錄執行前的記憶體使用量
$response = $this->get('/api/users'); // 發送 GET 請求到 /api/users
$memoryAfter = memory_get_usage(); // 記錄執行後的記憶體使用量

$this->assertLessThan(1024 * 1024, $memoryAfter - $memoryBefore); // 斷言記憶體使用量增加不超過 1MB
```

---

# 10. *測試報告與輸出*（Test Reports & Output）

## 10.1 **測試覆蓋率報告**

使用 `PHPUnit` 的 _覆蓋率_ 報告功能：

```bash
# 生成 HTML 覆蓋率報告
./vendor/bin/phpunit --coverage-html coverage/

# 生成 Clover XML 報告
./vendor/bin/phpunit --coverage-clover coverage.xml
```

---

## 10.2 **測試結果輸出格式**

```bash
# 詳細輸出
./vendor/bin/phpunit --verbose

# 輸出到檔案
./vendor/bin/phpunit --log-junit junit.xml

# 使用 Pest 的詳細輸出
./vendor/bin/pest --verbose
```
`--verbose`：顯示 _更詳細_ 的測試過程與結果，方便除錯。
`--log-junit junit.xml`：把測試結果輸出成 _JUnit XML_ 格式檔案，方便 `CI/CD` 或其他工具分析。
`--coverage-html coverage/`：產生 _HTML_ 格式的測試 __覆蓋率報告__。
`--coverage-clover coverage.xml`：產生 _Clover XML_ 格式的 __覆蓋率報告__。
`--log-junit、--coverage-*` 都是讓測試結果可以 __被其他系統或工具讀取、分析__ 。

---

# 11. *整合測試與 Mock*（Integration Testing & Mocking）

## 11.1 **外部服務 Mock**

### 11.1.1 *使用 Mockery*

```php
use Mockery; // 引入 Mockery 套件

class ExampleTest extends TestCase
{
    public function test_external_service()
    {
        $mock = Mockery::mock('App\Services\ExternalService'); // 建立 ExternalService 的 mock 物件
        $mock->shouldReceive('getData') // 設定 mock 物件應該接收 getData 方法呼叫
             ->once() // 設定該方法應該被呼叫一次
             ->andReturn(['result' => 'success']); // 設定該方法應該回傳的結果
             
        $this->app->instance('App\Services\ExternalService', $mock); // 將 mock 物件註冊到 Laravel 容器中
        
        $response = $this->get('/api/data'); // 發送 GET 請求到 /api/data
        $response->assertJson(['result' => 'success']); // 斷言回應包含預期的 JSON 資料
    }
}
```

---

### 11.1.2 *使用 Laravel 的 Fake*

```php
use Illuminate\Support\Facades\Mail; // 引入 Mail facade
use Illuminate\Support\Facades\Notification; // 引入 Notification facade

class ExampleTest extends TestCase
{
    public function test_sends_email()
    {
        // 也是 mock（模擬）的一種，只是 Laravel 用 fake() 來命名
        Mail::fake(); // 偽造 Mail 服務，避免真的發送郵件
        
        $response = $this->post('/api/register', [ // 發送 POST 請求到 /api/register
            'email' => 'test@example.com' // 傳入 email 參數
        ]);
        
        Mail::assertSent(WelcomeEmail::class); // 斷言有發送 WelcomeEmail 郵件
    }
    
    public function test_sends_notification()
    {
        Notification::fake(); // 偽造 Notification 服務，避免真的發送通知
        
        $response = $this->post('/api/order', [ // 發送 POST 請求到 /api/order
            'user_id' => 1 // 傳入 user_id 參數
        ]);
        
        Notification::assertSentTo(User::find(1), OrderConfirmation::class); // 斷言有發送 OrderConfirmation 通知給指定使用者
    }
}
```

---

## 11.2 **HTTP 客戶端 Mock**

```php
use Illuminate\Support\Facades\Http; // 引入 Http facade

class ExampleTest extends TestCase
{
    public function test_external_api_call()
    {
        Http::fake([ // 偽造 HTTP 客戶端，攔截外部 API 呼叫
            'api.example.com/*' => Http::response([ // 攔截所有 api.example.com 的請求
                'status' => 'success' // 回傳模擬的 API 回應資料
            ], 200) // 設定 HTTP 狀態碼為 200
        ]);
        
        $response = $this->get('/api/external-data'); // 發送 GET 請求到 /api/external-data
        $response->assertJson(['status' => 'success']); // 斷言回應包含預期的 JSON 資料
    }
}
```

---

# 12. *自訂測試輔助方法*（Custom Test Helpers）

## 12.1 **建立測試輔助方法**

在 `tests/TestCase.php` 中建立 *共用方法*：

```php
// TestCase 通常是測試的母檔案（基底類別），
// 所有測試類別都會繼承它，
// 你可以在裡面建立共用方法（像 createAuthenticatedUser、assertValidationError），
// 讓所有測試都能方便使用。
abstract class TestCase extends BaseTestCase
{
    protected function createAuthenticatedUser($attributes = [])
    {
        $user = User::factory()->create($attributes); // 使用 Factory 建立使用者，可傳入自訂屬性
        $this->actingAs($user); // 以該使用者身份登入
        return $user; // 回傳建立的使用者
    }
    
    protected function createAdminUser($attributes = [])
    {
        $user = User::factory()->admin()->create($attributes); // 建立具有 admin 狀態的使用者
        $this->actingAs($user); // 以該使用者身份登入
        return $user; // 回傳建立的管理員使用者
    }
    
    protected function assertValidationError($response, $field)
    {
        $response->assertStatus(422); // 斷言回應狀態碼為 422（驗證錯誤）
        $response->assertJsonValidationErrors([$field]); // 斷言指定欄位有驗證錯誤
    }
}
```

---

## 12.2 **使用測試輔助方法**

```php
class UserTest extends TestCase
{
    public function test_user_can_access_admin_panel()
    {
        $admin = $this->createAdminUser(); // 建立並登入管理員使用者
        
        $response = $this->get('/admin'); // 發送 GET 請求到管理員面板
        $response->assertStatus(200); // 斷言回應狀態碼為 200（成功）
    }
    
    public function test_user_cannot_access_admin_panel()
    {
        $user = $this->createAuthenticatedUser(); // 建立並登入一般使用者
        
        $response = $this->get('/admin'); // 發送 GET 請求到管理員面板
        $response->assertStatus(403); // 斷言回應狀態碼為 403（禁止存取）
    }
}
```

---

# 13. *測試最佳實踐*（Testing Best Practices）

## 13.1 **測試命名慣例**

好的測試命名（如 `test_user_can_login_with_valid_credentials`）  
能清楚描述 _測試的行為、角色、條件與預期結果_，  
讓你一看就知道 __這個測試在驗證什麼情境__，  
方便維護、閱讀、追蹤錯誤。

而像 `test_login`、`test_delete` 這種命名太過簡略，  
看不出具體測試內容，容易造成混淆或誤解。

```php
// 好的測試命名
public function test_user_can_login_with_valid_credentials()
public function test_user_cannot_login_with_invalid_credentials()
public function test_admin_can_delete_user()
public function test_guest_cannot_access_protected_route()

// 避免的命名
public function test_login()
public function test_delete()
public function test_access()
```

---

## 13.2 **測試結構**（AAA 模式）

```php
public function test_user_can_create_post()
{
    // Arrange - 準備測試資料
    $user = User::factory()->create(); // 建立測試使用者
    $postData = ['title' => 'Test Post', 'content' => 'Test Content']; // 準備文章資料
    
    // Act - 執行被測試的行為
    $response = $this->actingAs($user) // 以該使用者身份登入
        ->post('/posts', $postData); // 發送 POST 請求建立文章
    
    // Assert - 驗證結果
    $response->assertStatus(201); // 斷言回應狀態碼為 201（建立成功）
    $this->assertDatabaseHas('posts', $postData); // 斷言資料庫中存在該文章記錄
}
```

---

## 13.3 **測試隔離原則**

- 每個測試應該 _獨立執行_
- 測試之間 _不應該_ 有依賴關係
- 使用 `RefreshDatabase` 或 `DatabaseTransactions` 確保資料隔離
- *避免* 在測試中使用 __全域變數__ 或 __靜態屬性__

---

## 13.4 **測試資料管理**

- 使用 `Factory` *建立* 測試資料
- *避免* 在測試中 __直接操作資料庫__
- 測試完成後 __清理測試資料__
- 使用 *有意義的* 測試 __資料名稱__