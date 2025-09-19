# *Laravel URL 產生 筆記*

## 1. **簡介與核心概念**

- *Laravel 提供多種 URL 產生輔助函式*，方便在模板、API、重導等情境下產生**正確連結**。
- 支援`路徑、查詢參數、命名路由、簽名網址、Controller action、流暢 URI 物件`等。
- *生活化比喻*： URL 產生器就像「導航地圖」，讓你不用死記路徑，隨時產生正確連結。

---

## 2. **基礎用法與 url() 輔助函式**

- *產生任意網址*

  ```php
  $post = App\Models\Post::find(1);
  echo url("/posts/{$post->id}");
  // http://example.com/posts/1
  ```

---

- *帶查詢參數*

  ```php
  echo url()->query('/posts', ['search' => 'Laravel']);
  // 產生 /posts 路徑並加上 search 查詢參數
  // 結果: https://example.com/posts?search=Laravel

  echo url()->query('/posts?sort=latest', ['search' => 'Laravel']);
  // 原本有 sort=latest，再加上 search 查詢參數
  // 結果: http://example.com/posts?sort=latest&search=Laravel

  echo url()->query('/posts?sort=latest', ['sort' => 'oldest']);
  // 原本有 sort=latest，傳入 sort=oldest 會覆蓋原本的 sort
  // 結果: http://example.com/posts?sort=oldest

  echo url()->query('/posts', ['columns' => ['標題', 'body']]);
  // 結果: http://example.com/posts?columns[0]=%E6%A8%99%E9%A1%8C&columns[1]=body
  // 編碼規則是「URL 編碼」（percent-encoding），
  // 會把「非英文字母、數字」的字元轉成 % 加上兩位十六進位數字，
  // 例如「標題」會變成 %E6%A8%99%E9%A1%8C。
  // 這樣可以確保網址在網路傳輸時不會出錯。

  echo urldecode(url()->query('/posts', ['columns' => ['標題', 'body']]));
  // 結果: http://example.com/posts?columns[0]=標題&columns[1]=body
  ```

---

- *取得目前網址*

  ```php
  echo url()->current(); // 不含 query string
  echo url()->full();    // 含 query string
  echo url()->previous(); // 上一頁完整網址
  echo url()->previousPath(); // 上一頁路徑
  ```

---

- *URL Facade 也可用*

  ```php
  use Illuminate\Support\Facades\URL;
  echo URL::current();
  ```

---

## 3. **命名路由與 route() 輔助函式**

- *產生命名路由網址*

  ```php
  Route::get('/post/{post}', ...)->name('post.show');
  echo route('post.show', ['post' => 1]);
  // http://example.com/post/1
  ```

---

- *多參數路由*

  ```php
  Route::get('/post/{post}/comment/{comment}', ...)->name('comment.show');
  echo route('comment.show', ['post' => 1, 'comment' => 3]);
  // http://example.com/post/1/comment/3
  ```

---

- *額外參數自動變成 query string*

  ```php
  echo route('post.show', ['post' => 1, 'search' => 'rocket']);
  // http://example.com/post/1?search=rocket
  ```

---

- *Eloquent Model 可直接傳入*

  ```php
  echo route('post.show', ['post' => $post]);
  // 如果路由使用了 Eloquent Model 綁定，您可以直接將 Model 實例傳入 route() 函數。
  // 例如，假設路由定義如下：
  Route::get('/posts/{post}', [PostController::class, 'show'])->name('post.show');
  // 並且 {post} 是通過 Eloquent Model 綁定的。

  // 在控制器或其他地方，您可以這樣生成 URL：
  $post = App\Models\Post::find(1); // 假設取得 ID 為 1 的 Post 實例
  echo route('post.show', ['post' => $post]);
  // 這將生成 URL，例如 '/posts/1'，其中 '1' 是 Post 的主鍵。
  ```

---

## 4. **簽名網址（Signed URLs）**

- 簽名網址是一種`帶有簽章的安全網址`，用於防止被竄改。常用於`公開取消訂閱、驗證信`等場景，確保網址的完整性和安全性。

- *產生簽名網址*

  ```php
  use Illuminate\Support\Facades\URL;

  // 產生絕對網址（包含 domain）並加上簽章
  // 例如：https://example.com/unsubscribe/1?signature=abc123
  URL::signedRoute('unsubscribe', ['user' => 1]);

  // 產生相對網址（只包含路徑）並加上簽章
  // 例如：/unsubscribe/1?signature=xyz789
  // signature 一樣根據路徑、參數和密鑰產生
  URL::signedRoute('unsubscribe', ['user' => 1], absolute: false);
  ```

---

- *產生限時簽名網址*

  ```php
  URL::temporarySignedRoute('unsubscribe', now()->addMinutes(30), ['user' => 1]);
  // 生成一個限時有效的簽名網址，30 分鐘後失效。
  ```

---

- *驗證簽名網址*

  ```php
  use Illuminate\Http\Request;

  // 定義 /unsubscribe/{user} 路由，並命名為 unsubscribe
  Route::get('/unsubscribe/{user}', function (Request $request) {
      // 驗證網址簽章是否有效
      // 如果簽章無效（網址被竄改），則回傳 401 未授權錯誤
      if (! $request->hasValidSignature()) { abort(401); }
      // ...其他處理邏輯
  })->name('unsubscribe');

  // 忽略部分 query 參數進行簽名驗證
  // 例如：/unsubscribe/1?page=2&order=desc&signature=abc123
  // 驗證時會忽略 'page' 和 'order' 參數，只檢查主要路徑和簽章
  if (! $request->hasValidSignatureWhileIgnoring(['page', 'order'])) { abort(401); }
  ```

---

- *Middleware 驗證*

  ```php
  Route::post('/unsubscribe/{user}', function (Request $request) {
      // ...
  })->name('unsubscribe')->middleware('signed');
  // 使用 'signed' middleware，自動驗證簽名網址的有效性（包含 domain）

  // 若簽名不含 domain，使用 'signed:relative'
  Route::post('/unsubscribe/{user}', function (Request $request) {
      // ...
  })->name('unsubscribe')->middleware('signed:relative');
  // 適用於簽名僅包含路徑部分的情況（相對網址）
  ```

---

- *自訂過期頁面*

  ```php
  // bootstrap/app.php
  use Illuminate\Routing\Exceptions\InvalidSignatureException;

  ->withExceptions(function ($exceptions) {
      // 當遇到 InvalidSignatureException（簽名驗證失敗或過期）
      $exceptions->render(function (InvalidSignatureException $e) {
          // 回傳自訂錯誤頁面，顯示 "連結已過期"，HTTP 狀態碼 403
          return response()->view('errors.link-expired', status: 403);
      });
  })
  // 這樣可以自訂簽名驗證失敗或過期時的錯誤頁面
  ```

---

## 5. **Controller Action 產生網址**
bootstrap/app.php
- *action() 輔助函式*

  ```php
  use App\Http\Controllers\HomeController;
  use App\Http\Controllers\UserController;
  use App\Http\Controllers\InvokableController;

  $url = action([HomeController::class, 'index']);
  // 產生指向 HomeController 的 index 方法的 URL。

  $url = action([UserController::class, 'profile'], ['id' => 1]);
  // 產生指向 UserController 的 profile 方法的 URL，並傳遞參數 'id'。

  $url = action(InvokableController::class);
  // 產生指向 InvokableController 的 URL（適用於單一方法的控制器）。

---

## 6. **流暢 URI 物件（Fluent URI Objects）**

URI：`Uniform Resource Identifier`（統一資源標識符）。
URL：`Uniform Resource Locator`（統一資源定位符）或 URN（`Uniform Resource Name`，統一資源名稱）。

- 可用 _物件方式_ 流暢組合、修改 URI，底層用 `League URI 套件`

- *建立 URI 實例*

  ```php
  // Laravel 的 Illuminate\Support\Uri 類別已經把底層 League URI 套件封裝起來
  use Illuminate\Support\Uri;
  // Uri::of() 用於建立完整（絕對）URL 的 URI 實例
  $uri = Uri::of('https://example.com/path');
  // 建立一個 URI 實例，指定完整的 URL。

  // Uri::to() 用於建立相對路徑的 URI 實例，
  $uri = Uri::to('/dashboard');
  // 建立一個 URI 實例，指定相對路徑。

  $uri = Uri::route('users.show', ['user' => 1]);
  // 使用命名路由生成 URI。

  $uri = Uri::signedRoute('users.show', ['user' => 1]);
  // 使用簽名路由生成 URI。

  $uri = Uri::temporarySignedRoute('user.index', now()->addMinutes(5));
  // 使用限時簽名路由生成 URI。

  $uri = Uri::action([UserController::class, 'index']);
  // 使用 Controller Action 生成 URI。

  $uri = Uri::action(InvokableController::class);
  // 使用 Invokable Controller 生成 URI。

  $uri = $request->uri();
  // 從當前請求中取得 URI 實例。
  ```

---

- *流暢修改 URI*

  ```php
  $uri = Uri::of('https://example.com')
      ->withScheme('http')          // 修改協議為 'http'
      ->withHost('test.com')        // 修改主機為 'test.com'
      ->withPort(8000)              // 修改埠號為 8000
      ->withPath('/users')          // 修改路徑為 '/users'
      ->withQuery(['page' => 2])    // 添加查詢參數 'page=2'
      ->withFragment('section-1');  // 添加片段標記 'section-1'
  ```

---

## 7. **URL 預設參數（Default Values）**

- 可 *全域預設* 路由參數，常用於多語系網址（如 `{locale}`）

- *設定預設值*

<!-- 通常寫在中介層（Middleware）或服務提供者（ServiceProvider）裡，
     例如 AppServiceProvider 的 boot() 方法，
     或自訂的 Middleware，
     以便在每次請求時自動設定全域預設路由參數。 -->

  ```php
  use Illuminate\Support\Facades\URL;

  URL::defaults(['locale' => $request->user()->locale]);
  // 設定全域的預設路由參數，例如根據用戶的語言偏好設置 `locale`。
  // 當生成 URL 時，若未指定 `locale`，會自動使用預設值。
  ```
  ```php
  // 然後在 app/Http/Kernel.php 註冊這個 middleware，
  // 就能在每次請求時自動設定全域預設路由參數。
  namespace App\Http\Middleware;

  use Closure;
  use Illuminate\Support\Facades\URL;

  class SetLocaleDefault
  {
      public function handle($request, Closure $next)
      {
          // 根據使用者語言偏好設定全域預設路由參數
          URL::defaults(['locale' => $request->user()?->locale ?? 'zh-TW']);
          return $next($request);
      }
  }
  ```

---

- *建議在 middleware 設定，並優先於 `SubstituteBindings` 執行*

  ```php
  // 這是因為 Laravel 11（或新版）支援在 bootstrap/app.php 用物件方式管理 middleware 執行順序，
  // 而舊版或傳統寫法是在 app/Http/Kernel.php 註冊 middleware。
  // app/Http/Kernel.php：註冊 middleware 到全域或群組，適合大多數專案。
  // bootstrap/app.php：新版可用物件 API（如 prependToPriorityList）細緻控制 middleware 執行順序，

  // bootstrap/app.php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->prependToPriorityList(
          before: \Illuminate\Routing\Middleware\SubstituteBindings::class,
          prepend: \App\Http\Middleware\SetDefaultLocaleForUrls::class,
      );
  })
  // 將自訂的 `SetDefaultLocaleForUrls` 中介層加入執行優先順序，
  // 確保在 SubstituteBindings 中介層之前執行，正確設置預設參數。
  // 因為 SubstituteBindings middleware 會根據路由參數自動綁定模型，
  // 如果你要用 middleware 設定預設路由參數（如 locale），
  // 必須在 SubstituteBindings 執行之前設置，
  // 這樣模型綁定時才能正確取得你設定的預設參數，
  // 避免參數還沒設好就被綁定，導致資料錯誤或找不到。
  ```

---

- *middleware 範例*

  ```php
  namespace App\Http\Middleware;
  use Closure;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\URL;
  use Symfony\Component\HttpFoundation\Response;
  
  class SetDefaultLocaleForUrls {
      public function handle(Request $request, Closure $next): Response {
          URL::defaults(['locale' => $request->user()->locale]);
          // 根據用戶的語言偏好設置全域的 `locale` 預設值。
          return $next($request);
      }
  }
  ```

---