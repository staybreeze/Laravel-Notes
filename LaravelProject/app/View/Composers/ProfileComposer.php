<?php

namespace App\View\Composers;

use App\Repositories\UserRepository;
use Illuminate\View\View;

/**
 * ProfileComposer
 * --------------------------------------------------------------------------
 * 用途：
 * - 自動注入資料到 admin.profile 視圖（View Composer 機制）。
 * - 只要在 AppServiceProvider 註冊，渲染 admin.profile.blade.php 時就會自動執行 compose 方法。
 * - 建構子可注入任何服務（如 UserRepository），Laravel 會自動解析。
 * - compose 方法內用 $view->with('count', ...) 注入 count 變數，視圖內可直接用 {{ $count }}。
 * - 不需在 controller 或 route 傳 count，所有渲染該視圖的地方都自動有資料。
 * - 適合 sidebar、全站統計、通知、共用元件等「每次都要」的資料。
 * - 讓資料來源集中管理，減少重複、維護方便。
 * --------------------------------------------------------------------------
 */
class ProfileComposer
{
    /**
     * 建構子可自動注入依賴
     */
    public function __construct(
        protected UserRepository $users,
    ) {}

    /**
     * 綁定資料到視圖
     */
    public function compose(View $view): void
    {
        // 注入 count 變數到視圖
        $view->with('count', $this->users->count());
    }
} 