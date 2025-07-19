<?php

namespace App\Services;

class PingdomDowntimeNotifier implements DowntimeNotifier
{
    public function notify(string $message): void
    {
        // 這裡可以寫實際通知邏輯
        echo "[Pingdom] 通知：$message\n";
    }
} 