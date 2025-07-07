<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory, BroadcastsEvents;

    protected $fillable = [
        'room_id',
        'user_id',
        'content',
    ];

    public function broadcastOn(string $event): array
    {
        return [new \Illuminate\Broadcasting\PresenceChannel('chat.' . $this->room_id)];
    }
} 