<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 【前置/後置中介層範例】
 *
 * Middleware（中介層）可在請求進入應用程式前（前置）或處理完畢後（後置）執行任務。
 *
 * - 前置（Before Middleware）：
 *   在 $next($request) 之前執行，例如：驗證、權限檢查、日誌記錄等。
 *   實作範例：
 *     // 前置：驗證 API Token，未通過則拒絕請求
 *     public function handle(Request $request, Closure $next): Response {
 *         if ($request->header('X-API-TOKEN') !== 'my-secret-token') {
 *             return response('Unauthorized', 403);
 *         }
 *         return $next($request);
 *     }
 *
 * - 後置（After Middleware）：
 *   在 $next($request) 之後執行，例如：修改 response、加 header、記錄回應日誌等。
 *   實作範例：
 *     // 後置：所有回應都自動加上自訂 Header
 *     public function handle(Request $request, Closure $next): Response {
 *         $response = $next($request);
 *         $response->headers->set('X-Custom-Header', 'MyValue');
 *         return $response;
 *     }
 *
 * - 前後置同時處理：
 *   一個 middleware 也可以同時做前置與後置，只要在 $next($request) 前後都加邏輯即可。
 *   實作範例：
 *     // 前置：驗證，後置：記錄日誌
 *     public function handle(Request $request, Closure $next): Response {
 *         if ($request->input('token') !== 'my-secret-token') {
 *             return redirect('/home');
 *         }
 *         $response = $next($request);
 *         \Log::info('Request passed CheckAndLog middleware', [
 *             'user' => $request->user()?->id,
 *             'uri' => $request->getRequestUri(),
 *         ]);
 *         return $response;
 *     }
 *
 * 設計理念：
 *   Middleware 就像一層層的洋蔥，HTTP 請求需逐層通過，每層都可檢查、過濾、甚至拒絕請求。
 *
 * 用法：
 * 1. 註冊於 Kernel.php 的 $routeMiddleware
 * 2. 路由加 middleware('token.valid')
 *
 * 其他設計理念、依賴注入、維護建議請見下方註解。
 */

class EnsureTokenIsValid
{
    /**
     * 【進階設計理念與依賴注入說明】
     *
     * 1. 依賴注入（Dependency Injection）：
     *    可在 constructor 注入服務（如 Logger、Auth、Repository 等），Laravel 會自動解析。
     *    這樣設計有助於單元測試、擴充與維護。
     *    範例：
     *      public function __construct(SomeService $service) {
     *          $this->service = $service;
     *      }
     *
     * 2. 維護建議：
     *    - 複雜邏輯建議拆分多個 middleware，維護更清楚。
     *    - 盡量讓 middleware 單一職責，方便日後調整與測試。
     *    - 註解應說明「做什麼」、「為什麼這樣設計」、「怎麼用」。
     *
     * 3. 設計理念：
     *    - Middleware 就像一層層的洋蔥，請求需逐層通過，每層都可檢查、過濾、甚至拒絕請求。
     *    - 可彈性設計為前置、後置或前後置同時處理。
     */

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
