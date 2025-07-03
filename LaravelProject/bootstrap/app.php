<?php

// 本檔案（bootstrap/app.php）是 Laravel 應用程式的啟動設定檔，
// 負責初始化應用程式、載入各種設定，並自動載入 routes 目錄下的路由檔案。
// 這裡會指定 web.php 等路由檔案，讓 Laravel 能正確處理各種路由請求。

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    // 這裡透過 withRouting 方法，指定要自動載入的路由檔案，例如 web.php（網頁路由）、console.php（指令路由）等。
    // Laravel 會根據這些設定，自動將對應的路由檔案載入到應用程式中。
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // -----------------------------------------------------------------------------
        // 全域 Middleware 註冊說明
        // -----------------------------------------------------------------------------
        // 1. append/prepend：將 middleware 加到全域 middleware stack 的最後/最前面
        //    $middleware->append(\App\Http\Middleware\EnsureTokenIsValid::class);
        //    $middleware->prepend(\App\Http\Middleware\EnsureTokenIsValid::class);
        // 2. use：完全自訂全域 middleware stack，順序可調整
        //    $middleware->use([
        //        \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
        //        // \Illuminate\Http\Middleware\TrustHosts::class,
        //        \Illuminate\Http\Middleware\TrustProxies::class,
        //        \Illuminate\Http\Middleware\HandleCors::class,
        //        \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
        //        \Illuminate\Http\Middleware\ValidatePostSize::class,
        //        \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        //        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        //        // 你自己的 middleware
        //        \App\Http\Middleware\EnsureTokenIsValid::class,
        //    ]);
        // -----------------------------------------------------------------------------
        // 注意：全域 middleware 會在每一次 HTTP 請求時自動執行
        // 建議只將「所有請求都需檢查」的 middleware 註冊為全域 middleware
        // 例如：安全檢查、全站日誌、全站 header 處理等
        // -----------------------------------------------------------------------------

        // -----------------------------------------------------------------------------
        // Middleware Groups（中介層群組）設計理念與團隊註解規範
        // -----------------------------------------------------------------------------
        // 1. 中介層群組讓多個 middleware 以群組方式管理，方便在路由或控制器上一次套用多個中介層，提升可讀性與維護性。
        // 2. 單一職責原則：每個 middleware 只做一件事，方便重用與測試。
        // 3. 群組命名清楚：如 web、api、admin、auth，避免混淆。
        // 4. 註解完整：每個群組與 middleware 用途、順序、依賴關係都應加註解。
        // 5. 調整順序需測試：部分 middleware 有順序依賴（如 session 必須在 CSRF 前）。
        // 6. 避免重複註冊：同一 middleware 不應重複出現在同一群組。
        // -----------------------------------------------------------------------------

        // [範例] appendToGroup/prependToGroup 用法
        // $middleware->appendToGroup('group-name', [
        //     \App\Http\Middleware\First::class,   // 加在群組尾端，適合後置處理
        //     \App\Http\Middleware\Second::class,
        // ]);
        // $middleware->prependToGroup('group-name', [
        //     \App\Http\Middleware\First::class,   // 加在群組前端，適合前置驗證
        //     \App\Http\Middleware\Second::class,
        // ]);

        // [範例] 動態調整預設 web/api 群組
        // $middleware->web(append: [
        //     \App\Http\Middleware\EnsureUserIsSubscribed::class, // web 群組尾端加上，確保用戶已訂閱
        // ]);
        // $middleware->api(prepend: [
        //     \App\Http\Middleware\EnsureTokenIsValid::class,     // api 群組前端加上，驗證 API Token
        // ]);
        // $middleware->web(replace: [
        //     \Illuminate\Session\Middleware\StartSession::class => \App\Http\Middleware\StartCustomSession::class, // 取代預設
        // ]);
        // $middleware->web(remove: [
        //     \Illuminate\Session\Middleware\StartSession::class, // 移除預設
        // ]);

        // [範例] 完全自訂 web/api 群組內容
        // $middleware->group('web', [
        //     \Illuminate\Cookie\Middleware\EncryptCookies::class, // 處理 cookie 加密
        //     \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, // 回應時加上 cookie
        //     \Illuminate\Session\Middleware\StartSession::class, // 啟動 session
        //     \Illuminate\View\Middleware\ShareErrorsFromSession::class, // session 錯誤訊息
        //     \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, // CSRF 驗證
        //     \Illuminate\Routing\Middleware\SubstituteBindings::class, // 路由模型綁定
        //     // \Illuminate\Session\Middleware\AuthenticateSession::class,
        // ]);
        // $middleware->group('api', [
        //     // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        //     // 'throttle:api',
        //     \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // ]);

        // -----------------------------------------------------------------------------
        // Middleware Aliases（中介層別名）設計理念與團隊註解規範
        // -----------------------------------------------------------------------------
        // 1. 別名（alias）可為 middleware 指定簡短名稱，方便在路由或群組中引用，提升可讀性與維護性。
        // 2. 適合 class 名稱過長或常用的 middleware。
        // 3. 建議團隊統一命名規則，避免與內建 alias 衝突。
        // 4. 註解每個 alias 對應的 middleware class 與用途。
        // -----------------------------------------------------------------------------

        // [範例] 自訂 middleware alias
        // use App\Http\Middleware\EnsureUserIsSubscribed;
        // $middleware->alias([
        //     'subscribed' => EnsureUserIsSubscribed::class, // 用 'subscribed' 取代完整 class 名稱
        // ]);

        // [範例] 路由使用 middleware alias
        // Route::get('/profile', function () {
        //     // ...
        // })->middleware('subscribed');

        // [補充] Laravel 內建 middleware alias 對照表
        // | Alias              | Middleware 類別                                                         |
        // |--------------------|-------------------------------------------------------------------------|
        // | auth               | Illuminate\Auth\Middleware\Authenticate                                 |
        // | auth.basic         | Illuminate\Auth\Middleware\AuthenticateWithBasicAuth                   |
        // | auth.session       | Illuminate\Session\Middleware\AuthenticateSession                      |
        // | cache.headers      | Illuminate\Http\Middleware\SetCacheHeaders                             |
        // | can                | Illuminate\Auth\Middleware\Authorize                                    |
        // | guest              | Illuminate\Auth\Middleware\RedirectIfAuthenticated                     |
        // | password.confirm   | Illuminate\Auth\Middleware\RequirePassword                              |
        // | precognitive       | Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests       |
        // | signed             | Illuminate\Routing\Middleware\ValidateSignature                        |
        // | subscribed         | \\Spark\Http\Middleware\VerifyBillableIsSubscribed                      |
        // | throttle           | Illuminate\Routing\Middleware\ThrottleRequests 或 ThrottleRequestsWithRedis |
        // | verified           | Illuminate\Auth\Middleware\EnsureEmailIsVerified                        |
        // -----------------------------------------------------------------------------
        // 常見錯誤與維護建議
        // -----------------------------------------------------------------------------
        // - 調整 middleware 順序後，務必測試 session、CSRF、認證等功能是否正常。
        // - 群組命名與用途要明確，避免混用。
        // - 每段程式碼務必補充中英文註解，說明用途、依賴、易混淆點與維護建議。
        // - alias 命名需唯一，避免與內建 alias 衝突。
        // - 變更 alias 時，需同步檢查所有路由引用。
        // - 建議每個 alias 加上用途註解，方便團隊查閱。
        // -----------------------------------------------------------------------------

        // -----------------------------------------------------------------------------
        // Middleware Priority（中介層執行順序）設計理念與團隊註解規範
        // -----------------------------------------------------------------------------
        // 1. 某些情境下，需強制指定 middleware 執行順序（如 session 必須在 CSRF 前）。
        // 2. 當無法直接控制路由上 middleware 的順序時，可用 priority 方法明確指定。
        // 3. 建議只針對有順序依賴的 middleware 設定 priority，避免過度複雜。
        // 4. 調整順序後，務必測試相關功能（如 session、CSRF、認證等）。
        // -----------------------------------------------------------------------------

        // [範例] 指定 middleware 執行優先順序
        // $middleware->priority([
        //     \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        //     \Illuminate\Cookie\Middleware\EncryptCookies::class,
        //     \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        //     \Illuminate\Session\Middleware\StartSession::class,
        //     \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        //     \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        //     \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        //     \Illuminate\Routing\Middleware\ThrottleRequests::class,
        //     \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        //     \Illuminate\Routing\Middleware\SubstituteBindings::class,
        //     \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
        //     \Illuminate\Auth\Middleware\Authorize::class,
        // ]);

        // -----------------------------------------------------------------------------
        // Middleware Parameters（中介層參數）設計理念與團隊註解規範
        // -----------------------------------------------------------------------------
        // 1. middleware 可接收額外參數，提升彈性（如角色驗證、權限等動態條件）。
        // 2. 參數會在 $next 之後依序傳入 handle 方法。
        // 3. 路由指定時以 : 分隔 middleware 名稱與參數，參數間用逗號分隔。
        // 4. 建議參數命名明確，並於 middleware 註解用途與格式。
        // -----------------------------------------------------------------------------

        // [範例] 自訂 middleware 接收參數
        // class EnsureUserHasRole {
        //     public function handle(Request $request, Closure $next, string $role): Response {
        //         if (! $request->user()->hasRole($role)) {
        //             // 可導向錯誤頁或回應 403
        //         }
        //         return $next($request);
        //     }
        // }

        // [範例] 路由指定 middleware 參數
        // Route::put('/post/{id}', function (string $id) {
        //     // ...
        // })->middleware(EnsureUserHasRole::class.':editor');
        // // 多參數用逗號分隔
        // Route::put('/post/{id}', function (string $id) {
        //     // ...
        // })->middleware(EnsureUserHasRole::class.':editor,publisher');

        // -----------------------------------------------------------------------------
        // 常見錯誤與維護建議
        // -----------------------------------------------------------------------------
        // - 參數順序需與 handle 方法一致，否則會出現型別錯誤。
        // - 建議於 middleware 註解參數格式與用途，方便團隊查閱。
        // - 參數內容建議加型別檢查與預設值，提升健壯性。
        // -----------------------------------------------------------------------------

        // -----------------------------------------------------------------------------
        // Terminable Middleware（可終止中介層）設計理念與團隊註解規範
        // -----------------------------------------------------------------------------
        // 1. 若 middleware 需在回應送出後執行額外工作（如日誌、資源釋放、非同步通知），可實作 terminate 方法。
        // 2. terminate 方法會在 HTTP response 已送出瀏覽器後自動呼叫（需 FastCGI 支援）。
        // 3. handle 方法負責請求前/中處理，terminate 處理請求後（如記錄、清理、非同步任務）。
        // 4. 若需 handle/terminate 共用同一 middleware 實例，請於 AppServiceProvider 註冊 singleton。
        // -----------------------------------------------------------------------------

        // [範例] Terminable Middleware 實作
        // class TerminatingMiddleware {
        //     public function handle(Request $request, Closure $next): Response {
        //         return $next($request);
        //     }
        //     public function terminate(Request $request, Response $response): void {
        //         // 回應送出後執行（如日誌、通知、資源釋放）
        //     }
        // }

        // [範例] 註冊 singleton 以共用同一 middleware 實例
        // use App\Http\Middleware\TerminatingMiddleware;
        // public function register(): void {
        //     $this->app->singleton(TerminatingMiddleware::class);
        // }

        // -----------------------------------------------------------------------------
        // 常見錯誤與維護建議
        // -----------------------------------------------------------------------------
        // - terminate 僅適用於支援 FastCGI 的伺服器環境。
        // - 若需共用狀態，務必註冊 singleton，否則 handle/terminate 會是不同實例。
        // - terminate 適合處理非同步、延遲、資源釋放等工作，避免阻塞主流程。
        // - 請於 middleware 註解 terminate 用途與注意事項，方便團隊維護。
        // -----------------------------------------------------------------------------

        // -----------------------------------------------------------------------------
        // CSRF（跨站請求偽造）攻擊原理、攻擊流程、Laravel 防護機制、SPA、排除 URI、測試環境、Header 實作與安全層級說明
        // -----------------------------------------------------------------------------
        // [攻擊原理]
        // CSRF（Cross-site request forgery，跨站請求偽造）是一種攻擊手法，
        // 攻擊者會誘使已認證的使用者在不知情下，對受害網站發送未授權的請求。
        // 例如：
        //   - 攻擊者網站建立一個指向 https://your-application.com/user/email 的 POST 表單，
        //     並自動提交，讓受害者在登入狀態下誤觸，導致 email 被竄改。
        //   - 攻擊流程：
        //     <form action="https://your-application.com/user/email" method="POST">
        //         <input type="email" value="malicious-email@example.com">
        //     </form>
        //     <script>document.forms[0].submit();</script>
        //   - 只要受害者登入且有權限，攻擊就能成功。
        //
        // [Laravel 防護機制]
        // - Laravel 會為每個使用者 session 自動產生一組 CSRF token，並存於 session 內，攻擊者無法取得。
        // - 每次 session 重生時，token 也會更新。
        // - ValidateCsrfToken middleware（預設在 web group）會自動驗證請求中的 token 是否與 session 相符。
        // - 若不符則拋出 419 錯誤，防止偽造請求。
        //
        // [取得 token]
        // - $token = $request->session()->token();
        // - $token = csrf_token();
        //
        // [表單防護]
        // - 所有 POST、PUT、PATCH、DELETE 表單都必須帶有 _token 欄位。
        // - 建議使用 Blade @csrf 指令自動產生隱藏欄位：
        //   <form method="POST" action="/profile">
        //       @csrf
        //       <!-- 等同於： -->
        //       <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        //   </form>
        //
        // [Header 實作：X-CSRF-TOKEN & X-XSRF-TOKEN]
        // - 除了檢查 POST 參數，ValidateCsrfToken middleware 也會檢查 X-CSRF-TOKEN header。
        // - 可將 token 存在 <meta name="csrf-token" content="{{ csrf_token() }}">，
        //   並用 jQuery 自動帶入 header：
        //   $.ajaxSetup({
        //       headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        //   });
        // - Laravel 也會在回應自動帶一個加密的 XSRF-TOKEN cookie，
        //   前端可將其值設為 X-XSRF-TOKEN header（如 Angular、Axios 會自動處理）。
        // - resources/js/bootstrap.js 預設已引入 Axios，會自動帶 X-XSRF-TOKEN header。
        //
        // [CSRF token 傳遞方式與定義]
        // - form 欄位 _token：
        //   來源：Blade @csrf 指令或 <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        //   定義：HTML 表單隱藏欄位，傳統表單送出時自動帶入，Laravel 後端會驗證此值。
        //   適用：傳統 HTML form 提交（POST/PUT/PATCH/DELETE）。
        // - X-CSRF-TOKEN header：
        //   來源：前端 JS 讀取 <meta name="csrf-token" ...>，用 jQuery、fetch、Axios 等自動帶入 header。
        //   定義：AJAX 請求時，將 CSRF token 放在 HTTP header，Laravel 後端會驗證此值。
        //   適用：AJAX、SPA、前端框架自訂請求。
        // - X-XSRF-TOKEN header：
        //   來源：Laravel 會自動在回應帶一個加密的 XSRF-TOKEN cookie，前端（如 Angular、Axios）自動讀取 cookie 並帶入 header。
        //   定義：HTTP header，內容為 XSRF-TOKEN cookie 的值，Laravel 後端會驗證此值。
        //   適用：支援自動 XSRF cookie 的前端框架（如 Angular、Axios 預設行為）。
        //
        // [安全層級與原理比較]
        // - 不論是 form 欄位 _token、X-CSRF-TOKEN header、X-XSRF-TOKEN header，
        //   本質都是將唯一、不可預測、與 session 綁定的 token 傳給後端驗證，安全性一致。
        // - header 型態不是「更高層級」，而是「更彈性、現代化」的實作，特別適合 AJAX/SPA。
        // - Laravel 會自動支援所有方式，三者只要有一個正確即可通過。
        // - header 傳遞可避免瀏覽器自動填表、方便全域設定，對現代前端開發更友善。
        //
        // [SPA & API 專案]
        // - 若前端為 SPA（如 Vue/React）且後端為 Laravel API，請參考 Laravel Sanctum 文件，
        //   以正確方式處理 API 認證與 CSRF 防護。
        //   https://laravel.com/docs/sanctum
        //
        // [排除特定 URI 的 CSRF 防護]
        // - 某些情境（如第三方 webhook、外部服務 callback）無法帶 CSRF token，
        //   可將這些 URI 排除於 CSRF 防護之外：
        //   $middleware->validateCsrfTokens(except: [
        //       'stripe/*',
        //       'http://example.com/foo/bar',
        //       'http://example.com/foo/*',
        //   ]);
        // - 建議這類路由盡量不要放在 web middleware group，或明確排除。
        // - 請謹慎排除，避免誤將敏感路由暴露於 CSRF 風險。
        //
        // [測試環境]
        // - 執行自動化測試時，Laravel 會自動停用 CSRF middleware，方便測試流程。
        //
        // [維護建議]
        // - 團隊所有表單務必統一使用 @csrf，避免遺漏。
        // - 若自訂前端框架，請確保 _token 欄位正確帶入。
        // - 若遇 419 錯誤，請檢查 session、token 是否正確傳遞。
        // - 若有自訂 middleware group，請確認 CSRF 防護已正確加入。
        // - 團隊註解應說明 CSRF 防護原理與常見攻擊手法，方便新手理解。
        // -----------------------------------------------------------------------------
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// 路由自訂（Routing Customization）
// 預設情況下，所有路由會在 bootstrap/app.php 透過 withRouting 方法自動載入：
// ->withRouting(
//     web: __DIR__.'/../routes/web.php',
//     commands: __DIR__.'/../routes/console.php',
//     health: '/up',
// )->create();
// 
// [routes 資料夾結構範例]
// 傳統結構（只有 web.php、api.php）：
// routes/
// ├── web.php      // 前台網頁路由
// ├── api.php      // API 路由
// ├── console.php  // Artisan 指令路由
//


// 若需額外載入自訂路由檔案，可用 then 參數傳入 closure，在其中註冊額外路由：
// ->withRouting(
//     web: __DIR__.'/../routes/web.php',
//     commands: __DIR__.'/../routes/console.php',
//     health: '/up',
//     then: function () {
//         Route::middleware('api')
//             ->prefix('webhooks')
//             ->name('webhooks.')
//             ->group(base_path('routes/webhooks.php'));
//     },
// )
//
// 進階結構（多檔案分組）：
// routes/
// ├── web.php         // 前台網頁路由
// ├── api.php         // API 路由
// ├── admin.php       // 後台管理路由
// ├── webhooks.php    // Webhook 路由
// ├── partner.php     // 合作夥伴專用路由
// ├── console.php     // Artisan 指令路由


// 若要完全自訂所有路由註冊，可用 using 參數，所有 HTTP 路由都需自行註冊：
// ->withRouting(
//     commands: __DIR__.'/../routes/console.php',
//     using: function () {
//         Route::middleware('api')
//             ->prefix('api')
//             ->group(base_path('routes/api.php'));
//
//         Route::middleware('web')
//             ->group(base_path('routes/web.php'));
//     },
// )
