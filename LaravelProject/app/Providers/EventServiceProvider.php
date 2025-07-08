<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\CacheEventLogger;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Cache\Events\KeyForgotten;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // ... existing code ...
        // 快取事件監聽
        CacheHit::class => [
            [CacheEventLogger::class, 'handleCacheHit'],
        ],
        CacheMissed::class => [
            [CacheEventLogger::class, 'handleCacheMissed'],
        ],
        KeyWritten::class => [
            [CacheEventLogger::class, 'handleKeyWritten'],
        ],
        KeyForgotten::class => [
            [CacheEventLogger::class, 'handleKeyForgotten'],
        ],
        // ... existing code ...
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
} 