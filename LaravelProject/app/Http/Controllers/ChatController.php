<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // 取得聊天室訊息
    public function index($roomId)
    {
        return Message::where('room_id', $roomId)->with('user')->orderBy('id')->get();
    }

    // 發送訊息（自動廣播）
    public function store(Request $request, $roomId)
    {
        $message = Message::create([
            'room_id' => $roomId,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);
        return $message->load('user');
    }

    public function sendMessage(Request $request, $roomId)
    {
        // 儲存訊息
        $message = Message::create([
            'room_id' => $roomId,
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
        ]);

        // 廣播到 Presence Channel
        broadcast(new NewMessage($message))->toOthers();

        return response()->json($message);
    }
} 