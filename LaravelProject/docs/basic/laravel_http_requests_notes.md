# *Laravel HTTP Requests 請求*

---

## 1. **簡介與核心概念**
- `Illuminate\Http\Request` 提供 *物件導向方式* 存取 HTTP 請求、輸入、cookie、檔案等。
- *生活化比喻*： Request 就像「快遞包裹」，裡面裝著用戶送來的所有資料。

---

## 2. **取得 Request 實例與 DI**
- *Controller/Route 自動注入*
  ```php
  public function store(Request $request) { ... }
  Route::get('/', function (Request $request) { ... });
  ```

- *Route 參數與 DI 混用*：參數寫在 Request 之後
  ```php
  public function update(Request $request, string $id) { ... }
  ```
  
- *官方範例*：
  ```php
  namespace App\Http\Controllers;
  use Illuminate\Http\RedirectResponse;
  use Illuminate\Http\Request;
  class UserController extends Controller {
      // store 方法示範如何使用 Request 存取表單資料
      public function store(Request $request): RedirectResponse {
          // 使用 $request->input('name') 取得名為 'name' 的表單輸入值
          $name = $request->input('name');
          // 執行其他邏輯，例如儲存資料到資料庫
          // ...
          // 最後重定向到 /users 頁面
          return redirect('/users');
      }

      // update 方法示範如何同時處理 Request 和 Route 參數
      public function update(Request $request, string $id): RedirectResponse {
          // 使用 $request 存取請求資料
          // 使用 $id 存取 Route 中的參數
          // ...
          // 最後重定向到 /users 頁面
          return redirect('/users');
      }
  }
  ```

---

## 3. **路徑、主機、方法、URL 取得**

- *path()*：取得`路徑（不含 domain）`
  ```php
  $uri = $request->path(); 
  // 例如：若 URL 為 'http://example.com/admin/dashboard'，回傳 'admin/dashboard'
  ```
- *is() / routeIs()*：判斷`路徑/命名路由`是否符合指定模式，回傳**布林值**
  ```php
  // 判斷請求的路徑是否符合 'admin/*' 模式
  if ($request->is('admin/*')) { 
      // 'admin/*' 表示以 'admin/' 開頭的所有路徑，例如 'admin/dashboard' 或 'admin/settings'
      // 如果符合，執行此區塊
  }

  // 判斷請求的命名路由是否符合 'admin.*' 模式
  if ($request->routeIs('admin.*')) { 
      // 'admin.*' 表示以 'admin.' 開頭的所有命名路由，例如 'admin.dashboard' 或 'admin.settings'
      // 如果符合，執行此區塊
  }
  ```
- *url() / fullUrl()*：取得`完整網址`
  ```php
  $url = $request->url(); 
  // 取得不含查詢參數的完整 URL，例如 'http://example.com/admin/dashboard'
  $urlWithQueryString = $request->fullUrl();
  // 取得包含查詢參數的完整 URL，例如 'http://example.com/admin/dashboard?type=phone'
  ```
- *fullUrlWithQuery/WithoutQuery*：加/去`查詢參數`
  ```php
  $request->fullUrlWithQuery(['type' => 'phone']); 
  // 加入查詢參數，回傳完整 URL，例如 'http://example.com/admin/dashboard?type=phone'
  $request->fullUrlWithoutQuery(['type']); 
  // 移除指定查詢參數，回傳完整 URL，例如 'http://example.com/admin/dashboard'
  ```
- *host() / httpHost() / schemeAndHttpHost()*：`主機資訊`
  ```php
  $request->host(); 
  // 取得主機名稱，例如 'example.com'
  $request->httpHost(); 
  // 取得主機名稱和埠號，例如 'example.com:8080'
  $request->schemeAndHttpHost(); 
  // 取得協議和主機，例如 'http://example.com'
  ```
- *method() / isMethod()*：`HTTP 動詞`
  ```php
  $method = $request->method(); 
  // 取得 HTTP 方法，例如 'GET' 或 'POST'
  if ($request->isMethod('post')) { 
      // 判斷是否為 POST 方法，若是則執行此區塊
  }
  ```

---

## 4. **Header、IP、Content Negotiation**
- *header() / hasHeader()*：取得/判斷 `header`
  ```php
  // 取得指定 header 的值
  $value = $request->header('X-Header-Name'); 

  // 取得指定 header 的值，若不存在則回傳預設值 'default'
  $value = $request->header('X-Header-Name', 'default'); 

  // 判斷是否存在指定的 header
  if ($request->hasHeader('X-Header-Name')) { 
      // 如果存在，執行此區塊
  }
  ```
- *bearerToken()*：取得 `Authorization Bearer token`
  - **Bearer token** 是`用於 API 身份驗證的安全令牌`，通常包含在 Authorization header 中，格式為 "Bearer <token>"

  ```php
  // 取得 Authorization header 中的 Bearer token
  $token = $request->bearerToken();
  $token = $request->bearerToken();
  ```
- *ip() / ips()*：取得`用戶 IP`（多重代理時用 ips）
  ```php
  // 取得用戶的 IP 地址
  $ipAddress = $request->ip();
  
  // 取得所有代理的 IP 地址（多重代理情況下）
  $ipAddresses = $request->ips();
  ```
- *getAcceptableContentTypes / accepts / prefers / expectsJson*：`內容協商`
  ```php
  // 取得用戶可接受的內容類型（根據 Accept header）
  $contentTypes = $request->getAcceptableContentTypes(); 
  // Accept header 是 HTTP 請求中的一部分，用於告訴伺服器用戶端希望接收的內容類型。
  // 例如：Accept: application/json 表示用戶端希望伺服器回應 JSON 格式的資料。

  // 判斷用戶是否接受指定的內容類型
  if ($request->accepts(['text/html', 'application/json'])) { 
      // 如果 Accept header 包含 text/html 或 application/json，執行此區塊
  }

  // 取得用戶偏好的內容類型（根據 Accept header）
  $preferred = $request->prefers(['text/html', 'application/json']); 
  // 根據 Accept header 的排序，取得用戶最偏好的內容類型，例如 text/html 或 application/json。

  // 判斷用戶是否期望 JSON 格式的回應
  if ($request->expectsJson()) { 
      // 如果 Accept header 表示用戶期望 JSON 格式的回應，執行此區塊
  }
  ```

---

## 5. **PSR-7 Request 支援**
- 安裝：

  `composer require symfony/psr-http-message-bridge nyholm/psr7`
    - 安裝必要的套件以支援 PSR-7 標準的 HTTP 請求與回應。
    - PSR-7 是 **PHP 的 HTTP 消息介面標準** ，由 PHP-FIG 定義，提供統一的方式處理 HTTP 請求與回應。
    - 它定義了**一組介面**，例如 Request 和 Response，用於描述 HTTP 消息的結構。
    - PSR-7 的目的是 **提高框架和套件之間的互操作性** ，讓開發者能以一致的方式處理 HTTP 消息。  
  
  - *PHP-FIG*
    - PHP-FIG 是 **PHP Framework Interoperability Group** 的縮寫。
    - 它是一個由 **多個** PHP 框架和工具的代表組成的組織，目的是 **促進 PHP 社群的標準化** 。
    - PHP-FIG 定義了一系列 PSR（`PHP Standards Recommendations`），例如 PSR-7，用 於**統一** 框架和工具的行為和介面。
    - 這些標準提高了框架和套件之間的互操作性，讓開發者能更輕鬆地整合不同的工具和系統。

- *路由/Controller* 可 `type-hint` PSR-7 Request
  ```php
  use Psr\Http\Message\ServerRequestInterface;
  Route::get('/', function (ServerRequestInterface $request) { 
  });
 `` 
    - 使用 PSR-7 的 `ServerRequestInterface` 作為 type-hint。
    - `ServerRequestInterface` 是 PSR-7 定義的介面，用於表示 HTTP 請求。
    - 它包含 *HTTP 方法、URI、Header、Body* 等資訊，提供標準化的方式存取這些資料。
    - 在 Laravel 中使用 PSR-7，可以方便地與其他遵循 PSR-7 標準的工具或系統進行整合。
---

## 6. **Input 取得與轉型**
- *all() / collect()*：取得`所有輸入`（陣列/Collection）
```php
  $input = $request->all(); 
  // 取得所有輸入資料，回傳為陣列。

  $input = $request->collect(); 
  // 取得所有輸入資料，回傳為 Laravel Collection，方便進行集合操作。

  $request->collect('users')->each(function (string $user) { /* ... */ });
  // 取得名為 'users' 的輸入資料，並使用集合操作逐一處理。
  ```
- *input()*：`單一欄位`，支援 dot 語法、預設值
  ```php
  $name = $request->input('name'); 
  // 取得名為 'name' 的輸入值。
  
  $name = $request->input('name', 'Sally'); 
  // 取得名為 'name' 的輸入值，若不存在則回傳預設值 'Sally'。
  
  $name = $request->input('products.0.name'); 
  // 使用 dot 語法存取嵌套資料，取得 'products' 陣列中第一個元素的 'name'。
  
  $names = $request->input('products.*.name'); 
  // 使用通配符 '*' 取得 'products' 陣列中所有元素的 'name'。
  
  $input = $request->input(); 
  // 取得所有輸入資料，與 all() 類似。
  ```
- *query()*：只取 `query string`
  ```php
  $name = $request->query('name'); 
  // 取得 URL query string 中名為 'name' 的值。
  
  $name = $request->query('name', 'Helen'); 
  // 取得 URL query string 中名為 'name' 的值，若不存在則回傳預設值 'Helen'。
  
  $query = $request->query(); 
  // 取得所有 URL query string 資料。
  ```
- *JSON 輸入*：
  ```php
  $name = $request->input('user.name'); 
  // 使用 dot 語法存取 JSON 輸入資料中的嵌套欄位，例如 'user' 物件中的 'name'。
  ```
- *string() / integer() / boolean() / array() / date() / enum() / enums()*：`型別轉換`
  ```php
  $name = $request->string('name')->trim(); 
  // 取得名為 'name' 的輸入值並轉為字串，使用 trim() 去除空白。
  
  $perPage = $request->integer('per_page'); 
  // 取得名為 'per_page' 的輸入值並轉為整數。
  
  $archived = $request->boolean('archived'); 
  // 取得名為 'archived' 的輸入值並轉為布林值。
  
  $versions = $request->array('versions'); 
  // 取得名為 'versions' 的輸入值並轉為陣列。
  
  $birthday = $request->date('birthday'); 
  // 取得名為 'birthday' 的輸入值並轉為日期。
  
  $elapsed = $request->date('elapsed', '!H:i', 'Europe/Madrid'); 
  // 使用指定格式和時區轉換日期。
  
  use App\Enums\Status;
  $status = $request->enum('status', Status::class); 
  // 取得名為 'status' 的輸入值並轉為指定的 Enum 類型。
  
  $status = $request->enum('status', Status::class, Status::Pending); 
  // 若輸入值不存在，使用預設 Enum 值。
  
  use App\Enums\Product;
  $products = $request->enums('products', Product::class); 
  // 取得名為 'products' 的輸入值並轉為 Enum 類型的集合。
  ```
- *動態屬性*：
  ```php
  $name = $request->name; 
  // 動態屬性存取輸入資料，先查 payload，再查 route 參數。
  ```
- *only / except*：`取/排除`部分欄位
  ```php
  $input = $request->only(['username', 'password']); 
  // 只取得指定欄位的輸入資料。
  
  $input = $request->only('username', 'password'); 
  // 只取得指定欄位的輸入資料（另一種語法）。
  
  $input = $request->except(['credit_card']); 
  // 排除指定欄位的輸入資料。
  
  $input = $request->except('credit_card'); 
  // 排除指定欄位的輸入資料（另一種語法）。
  ```
- *has / hasAny / whenHas / filled / isNotFilled / anyFilled / whenFilled / missing / whenMissing*：判斷`欄位存在/有值/缺值`
  ```php
  if ($request->has('name')) { 
      // 判斷名為 'name' 的欄位是否存在。
  }
  
  if ($request->has(['name', 'email'])) { 
      // 判斷多個欄位是否都存在。
  }
  
  if ($request->hasAny(['name', 'email'])) { 
      // 判斷多個欄位是否至少有一個存在。
  }
  
  $request->whenHas('name', function (string $input) { 
      // 當名為 'name' 的欄位存在時執行。
  });
  
  $request->whenHas('name', function (string $input) { /* ... */ }, function () { 
      // 當名為 'name' 的欄位不存在時執行。
  });
  
  if ($request->filled('name')) { 
      // 判斷名為 'name' 的欄位是否有值。
  }
  
  if ($request->isNotFilled('name')) { 
      // 判斷名為 'name' 的欄位是否沒有值。
  }
  
  if ($request->isNotFilled(['name', 'email'])) { 
      // 判斷多個欄位是否都沒有值。
  }
  
  if ($request->anyFilled(['name', 'email'])) { 
      // 判斷多個欄位是否至少有一個有值。
  }
  
  $request->whenFilled('name', function (string $input) { 
      // 當名為 'name' 的欄位有值時執行。
  });
  
  $request->whenFilled('name', function (string $input) { /* ... */ }, function () { 
      // 當名為 'name' 的欄位沒有值時執行。
  });
  
  if ($request->missing('name')) { 
      // 判斷名為 'name' 的欄位是否不存在。
  }
  
  $request->whenMissing('name', function () { 
      // 當名為 'name' 的欄位不存在時執行。
  }, function () { 
      // 當名為 'name' 的欄位存在時執行。
  });
  ```
- *merge / mergeIfMissing*：`合併`額外輸入
  ```php
  $request->merge(['votes' => 0]); 
  // 合併額外的輸入資料。
  
  $request->mergeIfMissing(['votes' => 0]); 
  // 合併額外的輸入資料，僅當該欄位不存在時執行。
  ```

---

## 7. **Old Input 與表單回填**
- *flash / flashOnly / flashExcept*：將輸入`存入 session`
  ```php
  $request->flash(); 
  // 將所有輸入資料存入 session，方便後續使用。

  $request->flashOnly(['username', 'email']); 
  // 只將指定的欄位存入 session，例如 'username' 和 'email'。

  $request->flashExcept('password'); 
  // 將所有輸入資料存入 session，但排除指定的欄位，例如 'password'。
  ```
- *withInput()*：`重導時`自動閃存輸入
  ```php
  return redirect('/form')->withInput(); 
  // 重導到指定路徑，並將所有輸入資料存入 session。

  return redirect()->route('user.create')->withInput(); 
  // 重導到命名路由，並將所有輸入資料存入 session。

  return redirect('/form')->withInput($request->except('password')); 
  // 重導到指定路徑，並將輸入資料存入 session，但排除指定的欄位，例如 'password'。
  ```
- *old() / old helper*：取得`上次輸入`，Blade 表單回填
  ```php
  $username = $request->old('username'); 
  // 從 session 中取得上次輸入的 'username' 值。
  ```
  ```html
  <input type="text" name="username" value="{{ old('username') }}"> 
  <!-- 在 Blade 模板中使用 old() helper，回填表單欄位的上次輸入值。 -->
  ```

---

## 8. **Cookie 取得**
- *cookie()*：取得加密 cookie 值
  ```php
  $value = $request->cookie('name'); 
  // 從請求中取得名為 'name' 的加密 cookie 值。
  // Laravel 的 cookie 預設是加密的，使用此方法可以安全地存取 cookie 資料。
  ```

---
## 9. **輸入自動修剪與轉 null**
- 預設有 `TrimStrings`、`ConvertEmptyStringsToNull middleware`
  - Laravel 預設會使用 `TrimStrings` 和 `ConvertEmptyStringsToNull` 中介層。
  - TrimStrings：*自動移除輸入值的前後空白*。
  - ConvertEmptyStringsToNull：*將空字串自動轉換為 null*。

- 可 *全域移除* 或 *條件停用*
  ```php
  use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
  use Illuminate\Foundation\Http\Middleware\TrimStrings;

  ->withMiddleware(function ($middleware) {
      // 全域移除指定的中介層
      $middleware->remove([
          ConvertEmptyStringsToNull::class,
          TrimStrings::class,
      ]);

      // 條件停用 ConvertEmptyStringsToNull 中介層
      $middleware->convertEmptyStringsToNull(except: [fn ($req) => $req->is('admin/*')]);

      // 條件停用 TrimStrings 中介層
      $middleware->trimStrings(except: [fn ($req) => $req->is('admin/*')]);
  })

---

## 10. **檔案上傳與操作**
- *file() / 動態屬性*：取得 `UploadedFile` 實例
  ```php
  $file = $request->file('photo'); 
  // 使用 file() 方法取得名為 'photo' 的檔案，回傳 UploadedFile 實例。

  $file = $request->photo; 
  // 使用動態屬性存取名為 'photo' 的檔案，回傳 UploadedFile 實例。
  ```
- *hasFile() / isValid()*：判斷`檔案存在/上傳成功`
  ```php
  if ($request->hasFile('photo')) { 
      // 判斷名為 'photo' 的檔案是否存在於請求中。
  }
  
  if ($request->file('photo')->isValid()) { 
      // 判斷名為 'photo' 的檔案是否成功上傳且有效。
  }
  ```
- *path() / extension()*：取得`檔案路徑/副檔名`
  ```php
  $path = $request->photo->path(); 
  // 取得名為 'photo' 的檔案的暫存路徑。
  
  $extension = $request->photo->extension(); 
  // 取得名為 'photo' 的檔案的副檔名，例如 'jpg' 或 'png'。
  ```
- *store / storeAs*：`儲存檔案（本地/雲端）`
  ```php
  $path = $request->photo->store('images'); 
  // 將名為 'photo' 的檔案儲存到 'images' 資料夾，使用自動生成的檔名。
  
  $path = $request->photo->store('images', 's3'); 
  // 將名為 'photo' 的檔案儲存到 'images' 資料夾，並使用 S3 雲端存儲。
  
  $path = $request->photo->storeAs('images', 'filename.jpg'); 
  // 將名為 'photo' 的檔案儲存到 'images' 資料夾，並指定檔名為 'filename.jpg'。
  
  $path = $request->photo->storeAs('images', 'filename.jpg', 's3'); 
  // 將名為 'photo' 的檔案儲存到 'images' 資料夾，指定檔名為 'filename.jpg'，並使用 S3 雲端存儲。
  ```
---

## 11. **信任 Proxy 與 Host**
- *trustProxies*：設定可信 `Proxy IP/網段/headers`
  ```php
  use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
  use Illuminate\Foundation\Http\Middleware\TrimStrings;
  use Illuminate\Http\Request;

  ->withMiddleware(function ($middleware) {
      $middleware->trustProxies(at: [
          '192.168.1.1', 
          // 信任特定的 Proxy IP，例如 '192.168.1.1'。

          '10.0.0.0/8', 
          // 信任特定的網段，例如 '10.0.0.0/8'。
      ]);

      $middleware->trustProxies(headers: Request::HEADER_X_FORWARDED_AWS_ELB); 
      // 信任特定的 Proxy headers，例如 AWS ELB 的 X-Forwarded-* headers。
  });

  # 信任所有 Proxy：at: '*'
  ->withMiddleware(function ($middleware) {
      $middleware->trustProxies(at: '*'); 
      // 信任所有 Proxy，不限制 IP 或網段。
  });
  ```
- *trustHosts*：設定`可信 Host`
  ```php
  ->withMiddleware(function ($middleware) {
      $middleware->trustHosts(at: ['laravel.test']); 
      // 信任特定的 Host，例如 'laravel.test'。
  });
  
  ->withMiddleware(function ($middleware) {
      $middleware->trustHosts(at: ['laravel.test'], subdomains: false); 
      // 信任特定的 Host，但不包含子網域。
  });
  
  ->withMiddleware(function ($middleware) {
      $middleware->trustHosts(at: fn () => config('app.trusted_hosts')); 
      // 根據應用程式的設定檔動態信任 Host，例如從 'app.trusted_hosts' 配置中取得可信 Host。
  });
  ```

---
