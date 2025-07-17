<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Article;

class ArticlePolicy
{
    /**
     * 全域授權：管理員全部通過
     * @param User $user 當前登入的使用者
     * @param string $ability 權限動作名稱（如 update、delete...）
     * @return bool|null 回傳 true 直接通過，null 交由後續方法判斷
     */
    public function before(User $user, string $ability)
    {
        // 如果 User 有 isAdmin 方法且回傳 true，直接授權通過所有動作
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true; // 管理員全部通過，不再檢查後續方法
        }
        return null; // 交由後續對應方法判斷
    }

    /**
     * 判斷使用者是否可以檢視文章
     * @param User $user 當前登入的使用者
     * @param Article $article 目標文章
     * @return bool
     */
    public function view(User $user, Article $article): bool
    {
        // 任何登入者都可檢視文章
        return true;
    }

    /**
     * 判斷使用者是否可以建立文章，需為 writer 角色
     * @param User $user 當前登入的使用者
     * @return bool
     */
    public function create(User $user): bool
    {
        // 只有 role 為 writer 的使用者可建立文章
        return $user->role === 'writer';
    }

    /**
     * 判斷使用者是否可以更新文章，支援額外 categoryId 參數
     * @param User $user 當前登入的使用者
     * @param Article $article 目標文章
     * @param int|null $categoryId 額外分類參數（可選）
     * @return bool
     */
    public function update(User $user, Article $article, int $categoryId = null): bool
    {
        // 判斷是否為作者本人
        $isOwner = $user->id === $article->user_id;
        // 如果有傳 categoryId，則進一步判斷 user 是否有權限更新該分類
        // ($user->canUpdateCategory($categoryId) ?? true) 的意思：
        // - 先呼叫 user 的 canUpdateCategory 方法，傳入 categoryId
        // - 如果該方法回傳 true/false，就用那個結果
        // - 如果該方法回傳 null，則預設為 true（有權限）
        // - 這樣寫可避免 canUpdateCategory 沒實作時導致授權失敗
        // - 設計哲學補充：
        //   - 這屬於「預設寬鬆」（Default Allow）授權設計，只有明確回傳 false 才拒絕，
        //   - 若沒實作（null）則允許，避免因遺漏或未支援分類權限時誤傷用戶。
        $canUpdateCategory = $categoryId ? ($user->canUpdateCategory($categoryId) ?? true) : true;
        // 需同時為作者且有分類權限才可更新
        return $isOwner && $canUpdateCategory;
    }

    /**
     * 判斷使用者是否可以刪除文章
     * @param User $user 當前登入的使用者
     * @param Article $article 目標文章
     * @return bool
     */
    public function delete(User $user, Article $article): bool
    {
        // 只有作者本人可刪除文章
        return $user->id === $article->user_id;
    }
} 