<?php
namespace App\Services;

use App\Contracts\EventPusher;

class RedisEventPusher implements EventPusher
{
    public function push($event)
    {
        // 實際應用會推送事件，這裡僅示範
        return "推送事件到 Redis: $event";
    }
} 