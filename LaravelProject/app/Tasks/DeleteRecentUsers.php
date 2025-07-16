<?php

namespace App\Tasks;

use Illuminate\Support\Facades\DB;

class DeleteRecentUsers
{
    public function __invoke()
    {
        // 清空 recent_users 資料表
        DB::table('recent_users')->delete();
    }
} 