<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PodcastPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 建構子：可在這裡傳入事件相關資料
     */
    public function __construct()
    {
        //
    }

    /**
     * 若要廣播事件，可自訂頻道
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
} 