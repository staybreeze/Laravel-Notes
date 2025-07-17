<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    /**
     * 註解：
     * - 在 class（類別）的方法裡可以直接用 $this，不需要 constructor。
     * - $this 代表目前這個 Controller 物件。
     * - Laravel 會自動建立 Controller 實體並呼叫對應方法。
     */

    /**
     * 更新文章（Gate 範例）
     */
    public function updateWithGate(Request $request, Article $article)
    {
        // Gate 檢查：判斷目前登入者是否有 'update-article' 權限，並傳入 $article 作為參數
        if (!Gate::allows('update-article', $article)) {
            // 沒有權限時，回傳 403 Forbidden 並顯示自訂訊息
            abort(403, '無權限更新此文章');
        }
        // ...執行更新邏輯
        return response('更新成功');
    }

    /**
     * 更新文章（Policy 範例）
     */
    public function updateWithPolicy(Request $request, Article $article)
    {
        // Policy 檢查：呼叫 $this->authorize，會自動根據 ArticlePolicy 的 update 方法判斷權限
        $this->authorize('update', $article); // 不通過自動 403
        // ...執行更新邏輯
        return response('更新成功');
    }

    /**
     * 建立文章（Policy create 範例）
     */
    public function store(Request $request)
    {
        // 檢查是否有建立 Article 的權限，會呼叫 ArticlePolicy 的 create 方法
        $this->authorize('create', Article::class);
        // ...執行建立邏輯
        return response('建立成功');
    }

    /**
     * 更新文章（Policy 支援額外參數）
     */
    public function updateWithExtra(Request $request, Article $article)
    {
        // 取得額外參數 category_id
        $categoryId = $request->input('category_id');
        // 授權時傳遞多個參數，會對應到 Policy 的 update(User $user, Article $article, $categoryId)
        $this->authorize('update', [$article, $categoryId]);
        // ...執行更新邏輯
        return response('更新成功（含分類驗證）');
    }
} 