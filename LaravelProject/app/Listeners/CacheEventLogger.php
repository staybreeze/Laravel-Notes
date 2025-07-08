<?php
// 路徑：app/Listeners/CacheEventLogger.php
namespace App\Listeners;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Support\Facades\Log;

class CacheEventLogger
{
    // 快取命中
    public function handleCacheHit(CacheHit $event)
    {
        Log::info('Cache 命中', ['key' => $event->key, 'value' => $event->value]);
    }
    // 快取未命中
    public function handleCacheMissed(CacheMissed $event)
    {
        Log::info('Cache 未命中', ['key' => $event->key]);
    }
    // 快取寫入
    public function handleKeyWritten(KeyWritten $event)
    {
        Log::info('Cache 寫入', ['key' => $event->key, 'value' => $event->value]);
    }
    // 快取刪除
    public function handleKeyForgotten(KeyForgotten $event)
    {
        Log::info('Cache 刪除', ['key' => $event->key]);
    }
} 