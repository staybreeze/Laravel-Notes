# *Laravel URL 產生*

## 1. **簡介與核心概念**

- *Laravel 提供多種 URL 產生輔助函式*，方便在模板、API、重導等情境下產生**正確連結**。
- 支援路徑、查詢參數、命名路由、簽名網址、Controller action、流暢 URI 物件等。
- *生活化比喻*： URL 產生器就像「導航地圖」，讓你不用死記路徑，隨時產生正確連結。

---

## 2. **基礎用法與 url() 輔助函式**

- *產生任意網址*
  ```php
  $post = App\Models\Post::find(1);
  echo url("/posts/{$post->id}");
  // http://example.com/posts/1
  ```
- *帶查詢參數*
  ```php
  echo url()->query('/posts', ['search' => 'Laravel']);
  // https://example.com/posts?search=Laravel
  echo url()->query('/posts?sort=latest', ['search' => 'Laravel']);
  // http://example.com/posts?sort=latest&search=Laravel
  echo url()->query('/posts?sort=latest', ['sort' => 'oldest']);
  // http://example.com/posts?sort=oldest
  echo url()->query('/posts', ['columns' => ['title', 'body']]);
  // http://example.com/posts?columns[0]=title&columns[1]=body
  echo urldecode(url()->query('/posts', ['columns' => ['title', 'body']]));
  // http://example.com/posts?columns[0]=title&columns[1]=body
  ```
- *取得目前網址*
  ```php
  echo url()->current(); // 不含 query string
  echo url()->full();    // 含 query string
  echo url()->previous(); // 上一頁完整網址
  echo url()->previousPath(); // 上一頁路徑
  ```
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
- *多參數路由*
  ```php
  Route::get('/post/{post}/comment/{comment}', ...)->name('comment.show');
  echo route('comment.show', ['post' => 1, 'comment' => 3]);
  // http://example.com/post/1/comment/3
  ```
- *額外參數自動變成 query string*
  ```php
  echo route('post.show', ['post' => 1, 'search' => 'rocket']);
  // http://example.com/post/1?search=rocket
  ```
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

- 簽名網址是一種`帶有簽章的安全網址`，用於防止被竄改。常用於公開取消訂閱、驗證信等場景，確保網址的完整性和安全性。

- *產生簽名網址*
  ```php
  use Illuminate\Support\Facades\URL;
  URL::signedRoute('unsubscribe', ['user' => 1]);
  // 生成帶有簽章的安全網址，例如 '/unsubscribe/1?signature=abc123'。
  // 簽章確保網址未被竄改。

  // 可加 absolute: false 只簽路徑
  URL::signedRoute('unsubscribe', ['user' => 1], absolute: false);
  // 生成的簽名僅包含路徑部分，不包含 domain。
  ```

- *產生限時簽名網址*
  ```php
  URL::temporarySignedRoute('unsubscribe', now()->addMinutes(30), ['user' => 1]);
  // 生成一個限時有效的簽名網址，30 分鐘後失效。
  ```

- *驗證簽名網址*
  ```php
  use Illuminate\Http\Request;
  Route::get('/unsubscribe/{user}', function (Request $request) {
      if (! $request->hasValidSignature()) { abort(401); }
      // 驗證簽名是否有效，若無效則返回 401 未授權錯誤。
      // ...
  })->name('unsubscribe');
  
  // 忽略部分 query 參數
  if (! $request->hasValidSignatureWhileIgnoring(['page', 'order'])) { abort(401); }
  // 忽略指定的 query 參數進行簽名驗證，例如 'page' 和 'order'。
  ```

- *Middleware 驗證*
  ```php
  Route::post('/unsubscribe/{user}', function (Request $request) {
      // ...
  })->name('unsubscribe')->middleware('signed');
  // 使用 'signed' 中介層自動驗證簽名網址的有效性。
  
  // 若簽名不含 domain，使用 'signed:relative'
  Route::post('/unsubscribe/{user}', function (Request $request) {
      // ...
  })->name('unsubscribe')->middleware('signed:relative');
  // 適用於簽名僅包含路徑部分的情況。
  ```

- *自訂過期頁面*
  ```php
  use Illuminate\Routing\Exceptions\InvalidSignatureException;
  ->withExceptions(function ($exceptions) {
      $exceptions->render(function (InvalidSignatureException $e) {
          return response()->view('errors.link-expired', status: 403);
      });
  })
  // 自訂簽名驗證失敗或過期時的錯誤頁面，例如顯示 "連結已過期"。
  ```

---

## 5. **Controller Action 產生網址**

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
URL：`Uniform Resource Locator`（統一資源定位符）或 URN（Uniform Resource Name，統一資源名稱）。
- *可用物件方式流暢組合、修改 URI，底層用 League URI 套件*

- *建立 URI 實例*
  ```php
  use Illuminate\Support\Uri;

  $uri = Uri::of('https://example.com/path');
  // 建立一個 URI 實例，指定完整的 URL。

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

- *可全域預設路由參數，常用於多語系網址（如 `{locale}`）*

- *設定預設值*
  ```php
  use Illuminate\Support\Facades\URL;
  URL::defaults(['locale' => $request->user()->locale]);
  // 設定全域的預設路由參數，例如根據用戶的語言偏好設置 `locale`。
  // 當生成 URL 時，若未指定 `locale`，會自動使用預設值。
  ```
- *建議在 middleware 設定，並優先於 `SubstituteBindings` 執行*
  ```php
  // bootstrap/app.php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->prependToPriorityList(
          before: \Illuminate\Routing\Middleware\SubstituteBindings::class,
          prepend: \App\Http\Middleware\SetDefaultLocaleForUrls::class,
      );
  })
  // 將自訂的 `SetDefaultLocaleForUrls` 中介層加入執行優先順序，
  // 確保在 SubstituteBindings 中介層之前執行，正確設置預設參數。
  ```
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