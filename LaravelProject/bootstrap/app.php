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
        //
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
