<?php

// 本檔案（routes/api.php）專門用來定義無狀態的 API 路由，這些路由會自動套用 api middleware group，
// 並且所有路由都會自動加上 /api 前綴（例如 /api/user），不需手動加在每個路由前。
// 這些 API 路由通常使用 Token 驗證（如 Sanctum），且不會存取 session 狀態。

// 若要修改前綴，可在 bootstrap/app.php 的 withRouting 設定 apiPrefix，例如：
// ->withRouting(
//     api: __DIR__.'/../routes/api.php',
//     apiPrefix: 'api/admin',
// )
//
// 執行 install:api 指令會安裝 Laravel Sanctum，提供簡單且強大的 API Token 認證機制，
// 可用於第三方 API 使用者、SPA 或行動應用程式的認證，並自動建立本檔案。

// 依賴注入（Dependency Injection）用法請參考 routes/web.php。

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('chat/{roomId}/messages', [App\Http\Controllers\ChatController::class, 'index']);
Route::post('chat/{roomId}/messages', [App\Http\Controllers\ChatController::class, 'store']);

// ------------------------------------------------------------
// 文章管理 RESTful API 路由
// ------------------------------------------------------------
use App\Http\Controllers\Api\ArticleController;

// 使用 Route::apiResource 自動生成 API 專用的 RESTful 路由
// 會產生：GET、POST、GET、PUT、DELETE 五個路由，不包含 create/edit（因為 API 不需要表單頁面）
Route::apiResource('articles', ArticleController::class);

// 額外的文章相關 API 路由
Route::patch('articles/{id}/publish', [ArticleController::class, 'publish']);   // 發布文章
Route::patch('articles/{id}/unpublish', [ArticleController::class, 'unpublish']); // 取消發布文章
