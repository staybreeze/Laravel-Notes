use App\Models\Order;
use App\Models\User;
use App\Models\Room;

Broadcast::channel('orders.{orderId}', function (User $user, int $orderId) {
    return $user->id === Order::findOrNew($orderId)->user_id;
});

Broadcast::channel('chatroom.{roomId}', function (User $user, int $roomId) {
    // 只有房間成員才能加入 PresenceChannel
    return Room::findOrNew($roomId)->users->contains($user->id);
});

Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    return ['id' => $user->id, 'name' => $user->name];
});

// Private Channel：訂單狀態推播授權
// 用於 order.{orderId} 頻道，僅有權限的用戶可訂閱
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    return $user->canViewOrder($orderId);
});

// Presence Channel：聊天室成員同步授權
// 用於 presence-chat.{roomId} 頻道，回傳用戶資訊給前端成員列表
Broadcast::channel('presence-chat.{roomId}', function ($user, $roomId) {
    return ['id' => $user->id, 'name' => $user->name];
}); 