# *Laravel Middleware 中介層*

---

## 1. **簡介與核心概念**
- *Middleware（中介層）* 可攔截、檢查、過濾進入應用的 HTTP 請求。
- 例如：驗證用戶是否登入、CSRF 防護、日誌記錄等。
- *生活化比喻*： Middleware 就像「門禁管制」，每層都能檢查、放行或拒絕訪客。
- 所有自訂 middleware 通常放在 `app/Http/Middleware` 目錄。

---

## 2. **定義 Middleware**
- 使用 artisan 指令建立：
  ```bash
  `php artisan make:middleware EnsureTokenIsValid`
  ```
- 範例：只允許 token 正確才放行，否則重導回 /home：
  ```php
  namespace App\Http\Middleware;
  use Closure;
  use Illuminate\Http\Request;
  use Symfony\Component\HttpFoundation\Response;
  class EnsureTokenIsValid {
      public function handle(Request $request, Closure $next): Response {
          if ($request->input('token') !== 'my-secret-token') {
              return redirect('/home');
          }
          return $next($request);
      }
  }
  ```
- *$next($request)* 代表「放行」到下一層 middleware 或 controller。
- Middleware 可視為「多層洋蔥」，每層都能檢查、處理請求。
- 所有 middleware 由 *service container* 注入，可在建構子 *type-hint* 依賴。

---

## 3. **Middleware 與 Response 前後處理**
- *前置處理*（請求進入前）：
  ```php
  class BeforeMiddleware {
      public function handle(Request $request, Closure $next): Response {
          // 前置動作
          return $next($request);
      }
  }
  ```
- *後置處理*（回應送出後）：
  ```php
  class AfterMiddleware {
      public function handle(Request $request, Closure $next): Response {
          $response = $next($request);
          // 後置動作
          return $response;
      }
  }
  ```

---

## 4. **註冊 Middleware**

### *全域 Middleware*
- 讓 middleware 作用於`所有請求`：
  ```php
  use App\Http\Middleware\EnsureTokenIsValid;
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->append(EnsureTokenIsValid::class);
  })
  ```
- `append` 加在全域 middleware 最後，
  `prepend` 加在最前。
- **自訂全域 middleware stack**：
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->use([
          \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
          // \Illuminate\Http\Middleware\TrustHosts::class,
          \Illuminate\Http\Middleware\TrustProxies::class,
          \Illuminate\Http\Middleware\HandleCors::class,
          \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
          \Illuminate\Http\Middleware\ValidatePostSize::class,
          \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
          \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
      ]);
  })
  ```

### *路由 Middleware*
- 指定 middleware 只作用於`特定路由`：
  ```php
  use App\Http\Middleware\EnsureTokenIsValid;
  Route::get('/profile', function () { /* ... */ })->middleware(EnsureTokenIsValid::class);
  Route::get('/', function () { /* ... */ })->middleware([First::class, Second::class]);
  ```

### *排除 Middleware*
- 可用 `withoutMiddleware` 排除 group 內特定 middleware：
  ```php
  Route::middleware([EnsureTokenIsValid::class])->group(function () {
      Route::get('/', function () { /* ... */ });
      Route::get('/profile', function () { /* ... */ })->withoutMiddleware([EnsureTokenIsValid::class]);
  });
  Route::withoutMiddleware([EnsureTokenIsValid::class])->group(function () {
      Route::get('/profile', function () { /* ... */ });
  });
  ```
- **注意**：`只能移除 route middleware，無法移除全域 middleware`。

---

## 5. **Middleware 群組（Group）**
- 可將多個 middleware *組成群組*，方便一次套用：
  ```php
  use App\Http\Middleware\First;
  use App\Http\Middleware\Second;
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->appendToGroup('group-name', [First::class, Second::class]);
      $middleware->prependToGroup('group-name', [First::class, Second::class]);
  })
  ```
- *路由套用群組*：
  ```php
  Route::get('/', function () { /* ... */ })->middleware('group-name');
  Route::middleware(['group-name'])->group(function () { /* ... */ });
  ```
- *Laravel 內建 web/api 群組*：
  - `web`： EncryptCookies, 
            AddQueuedCookiesToResponse, 
            StartSession, 
            ShareErrorsFromSession, 
            ValidateCsrfToken, 
            SubstituteBindings
  - `api`：SubstituteBindings

- *自訂/調整群組*：
  ```php
  use App\Http\Middleware\EnsureTokenIsValid;
  use App\Http\Middleware\EnsureUserIsSubscribed;
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->web(append: [EnsureUserIsSubscribed::class]);
      $middleware->api(prepend: [EnsureTokenIsValid::class]);
      $middleware->web(replace: [StartSession::class => StartCustomSession::class]);
      $middleware->web(remove: [StartSession::class]);
  })
  ```
- *完全自訂群組內容*：
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->group('web', [
          \Illuminate\Cookie\Middleware\EncryptCookies::class,
          \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
          \Illuminate\Session\Middleware\StartSession::class,
          \Illuminate\View\Middleware\ShareErrorsFromSession::class,
          \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
          \Illuminate\Routing\Middleware\SubstituteBindings::class,
      ]);
      $middleware->group('api', [
          \Illuminate\Routing\Middleware\SubstituteBindings::class,
      ]);
  })
  ```

---

## 6. **Middleware 別名（Alias）**
- 可為 middleware 設定 *短別名* ，方便路由引用：
  ```php
  use App\Http\Middleware\EnsureUserIsSubscribed;
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->alias([
          'subscribed' => EnsureUserIsSubscribed::class
      ]);
  })
  Route::get('/profile', function () { /* ... */ })->middleware('subscribed');
  ```
- *Laravel 內建常用別名*：auth、
                        auth.basic、
                        auth.session、
                        cache.headers、
                        can、
                        guest、
                        password.confirm、
                        precognitive、
                        signed、
                        subscribed、
                        throttle、
                        verified ...

---

## 7. **Middleware 執行順序（Priority）**
- 可用 `priority()` 指定 middleware *執行順序*：
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->priority([
          \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
          \Illuminate\Cookie\Middleware\EncryptCookies::class,
          \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
          \Illuminate\Session\Middleware\StartSession::class,
          \Illuminate\View\Middleware\ShareErrorsFromSession::class,
          \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
          \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
          \Illuminate\Routing\Middleware\ThrottleRequests::class,
          \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
          \Illuminate\Routing\Middleware\SubstituteBindings::class,
          \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
          \Illuminate\Auth\Middleware\Authorize::class,
      ]);
  })
  ```

---

## 8. **Middleware 參數**
- Middleware *可接收額外參數*：
  ```php
  class EnsureUserHasRole {
      public function handle(Request $request, Closure $next, string $role): Response {
          if (! $request->user()->hasRole($role)) {
              // Redirect...
          }
          return $next($request);
      }
  }
  ```
- *路由指定參數*：
  ```php
  Route::put('/post/{id}', function (string $id) { /* ... */ })->middleware(EnsureUserHasRole::class.':editor');
  Route::put('/post/{id}', function (string $id) { /* ... */ })->middleware(EnsureUserHasRole::class.':editor,publisher');
  ```

---

## 9. **Terminable Middleware（回應送出後處理）**
- 若 middleware 有 `terminate` 方法，*回應送出後會自動呼叫*（需 FastCGI）：
  ```php
  class TerminatingMiddleware {
      public function handle(Request $request, Closure $next): Response {
          return $next($request);
      }
      public function terminate(Request $request, Response $response): void {
          // ...
      }
  }
  ```
- 若要 `handle/terminate `用同一實例，需在 `AppServiceProvider` 註冊 `singleton`：
  ```php
  use App\Http\Middleware\TerminatingMiddleware;
  public function register(): void {
      $this->app->singleton(TerminatingMiddleware::class);
  }
  ```

---
