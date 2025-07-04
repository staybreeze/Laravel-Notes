<?php

namespace App\Repositories;

class UserRepository
{
    /**
     * 回傳用戶總數（範例固定回傳 42，可改為查詢資料庫）
     */
    public function count(): int
    {
        return 42;
    }
} 