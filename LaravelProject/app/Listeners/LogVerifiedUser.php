<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;

class LogVerifiedUser
{
    public function handle(Verified $event)
    {
        // 這裡可以記錄日誌、發送歡迎信等
        // $event->user 可取得已驗證的 user
    }
} 