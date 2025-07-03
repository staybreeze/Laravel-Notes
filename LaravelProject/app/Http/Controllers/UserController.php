<?php

/**
 * UserController
 *
 * [設計理念]
 * - 控制器（Controller）用於將相關的請求處理邏輯集中於單一類別，提升程式結構的可維護性與可讀性。
 * - 例如 UserController 可統一處理所有與使用者相關的顯示、建立、更新、刪除等請求。
 *
 * [目錄結構]
 * - 所有控制器預設存放於 app/Http/Controllers 目錄下。
 *
 * [Artisan 指令快速產生]
 *   php artisan make:controller UserController
 *
 * [基本範例]
 *   // 顯示指定使用者的個人資料
 *   public function show(string $id): View {
 *       return view('user.profile', [
 *           'user' => User::findOrFail($id)
 *       ]);
 *   }
 *
 * [路由綁定控制器方法]
 *   use App\Http\Controllers\UserController;
 *   Route::get('/user/{id}', [UserController::class, 'show']);
 *   // 當請求符合 URI，會自動呼叫 UserController 的 show 方法，並將路由參數傳入。
 *
 * [Controller Middleware 實作說明]
 * - 可於路由層指定 middleware（推薦小型專案/單一 action）
 * - 可於控制器內實作 HasMiddleware 介面，集中管理多 action 共用 middleware
 * - 支援 only/except 條件、Closure middleware
 * - 建議團隊統一用法並加上用途註解
 *
 * [團隊建議]
 * - 控制器不一定要繼承 base class，但繼承 Controller 可共用通用方法。
 * - 建議團隊將同類型邏輯集中於同一控制器，並加上中英文註解，方便維護。
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;
use Closure;
use App\Repositories\UserRepository;

class UserController extends Controller implements HasMiddleware
{
    /**
     * [依賴注入範例]
     * 建構子注入：自動注入 UserRepository 實例
     * 適合所有 action 都會用到的服務
     *
     * @param UserRepository $users 使用者資料倉儲
     */
    public function __construct(
        protected UserRepository $users,
    ) {}

    /**
     * 指定該控制器要套用的 middleware
     * - 'auth'：所有 action 都需驗證登入
     * - 'log'：僅 index action 需記錄日誌
     * - 'subscribed'：除了 store 以外都需檢查訂閱
     * - Closure middleware：可用於臨時、簡單邏輯
     * Laravel 會自動去找這個控制器有沒有 middleware() 方法，有的話就把裡面設定的 middleware 套用到對應的 action。
     * action 就是 Controller 裡面的一個「方法」，每個方法對應一種「動作」或「功能」。
     */
    public static function middleware(): array
    {
        return [
            'auth', // 所有 action 都會套用 'auth' middleware（必須登入）
            new Middleware('log', only: ['index']), // 只有 index action 會套用 'log' middleware（僅記錄列表頁的日誌）
            new Middleware('subscribed', except: ['store']), // 除了 store action 以外都會套用 'subscribed' middleware（檢查訂閱狀態）
            // 內聯 Closure middleware 範例
            function (Request $request, Closure $next) {
                // ... inline middleware 邏輯 ...
                // 這裡可以寫臨時、簡單的 middleware 處理邏輯
                return $next($request); // 放行請求，繼續往下個 middleware 或 controller 執行
            },
        ];
    }

    /**
     * 顯示指定使用者的個人資料
     */
    public function show(string $id): View
    {
        return view('user.profile', [
            'user' => User::findOrFail($id)
        ]);
    }

    /**
     * [方法依賴注入範例]
     * 方法注入：自動注入 Request 實例
     * 適合僅特定 action 需要的依賴
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $name = $request->name;
        // 儲存使用者...
        return redirect('/users');
    }

    /**
     * [路由參數與依賴注入順序範例]
     * 依賴注入參數需寫在前，路由參數寫在後
     *
     * Route::put('/user/{id}', [UserController::class, 'update']);
     */
    public function update(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        // 更新使用者...
        return redirect('/users');
    }
}