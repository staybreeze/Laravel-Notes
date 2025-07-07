<?php

namespace App\Listeners;

use App\Events\PodcastProcessed;
use App\Events\PodcastPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPodcastNotification
{
    /**
     * 建構子
     */
    public function __construct()
    {
        //
    }

    /**
     * 同時監聽 PodcastProcessed 與 PodcastPublished 事件
     *
     * @param PodcastProcessed|PodcastPublished $event
     */
    public function handle(PodcastProcessed|PodcastPublished $event): void
    {
        if ($event instanceof PodcastProcessed) {
            // 處理 PodcastProcessed 事件
        } elseif ($event instanceof PodcastPublished) {
            // 處理 PodcastPublished 事件
        }
    }
} 