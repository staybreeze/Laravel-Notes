# Laravel Broadcasting 前端（Client Side）安裝與實作

---

## 1. Broadcasting 前端安裝與設定

### 1.1 安裝 Echo 及對應套件

#### Reverb / Pusher / Ably（皆需 pusher-js）
# 路徑：終端機指令
```bash
npm install --save-dev laravel-echo pusher-js
```

---

### 1.2 建立 Echo 實例（resources/js/bootstrap.js）
#### Reverb
```js
// 路徑：LaravelProject/resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

#### Pusher
```js
// 路徑：LaravelProject/resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

#### Ably
```js
// 路徑：LaravelProject/resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_ABLY_PUBLIC_KEY,
    wsHost: 'realtime-pusher.ably.io',
    wsPort: 443,
    disableStats: true,
    encrypted: true,
});
```

---

### 1.3 .env 環境變數設定

#### Pusher 範例
```env
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

#### Reverb/Ably 也有對應的 VITE_REVERB_APP_KEY、VITE_ABLY_PUBLIC_KEY 等

---

### 1.4 編譯前端資產
```bash
npm run build
# 或
npm run dev
```

---

## 2. 事件定義與授權

### 2.1 定義事件並實作 ShouldBroadcast
```php
// app/Events/OrderShipmentStatusUpdated.php
namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class OrderShipmentStatusUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('orders.' . $this->order->id);
    }
}
```

---

### 2.2 頻道授權（routes/channels.php）
```php
// 路徑：LaravelProject/routes/channels.php
use App\Models\Order;
use App\Models\User;

Broadcast::channel('orders.{orderId}', function (User $user, int $orderId) {
    return $user->id === Order::findOrNew($orderId)->user_id;
});
```

---

## 3. 前端 Echo 監聽事件

### 3.1 Vue/React/JS
```js
// 路徑：LaravelProject/resources/js/components/OrderStatus.js

window.Echo.private('orders.' + orderId)
    .listen('OrderShipmentStatusUpdated', (e) => {
        console.log(e.order);
        // 更新 UI
    });
```

#### React Hook 寫法（@laravel/echo-react）
```js
// 路徑：LaravelProject/resources/js/components/OrderStatus.js

import { useEcho } from "@laravel/echo-react";

useEcho(
    `orders.${orderId}`,
    "OrderShipmentStatusUpdated",
    (e) => {
        console.log(e.order);
    },
);
```

---

## 4. Q&A 註解

// Q: 為什麼要用 private channel？  
// A: 保護用戶資料，只有授權用戶才能訂閱該頻道。

// Q: broadcastOn() 可以回傳多個頻道嗎？  
// A: 可以，回傳 array 即可。

// Q: 前端事件名稱怎麼寫？  
// A: 預設用事件 class 名稱（如 OrderShipmentStatusUpdated），可用 broadcastAs() 自訂。

// Q: 一定要用 queue 嗎？  
// A: 是，所有廣播事件都會進 queue，必須啟動 queue worker。

// Q: 如何測試？  
// A: 可用 log driver 或 null driver，本地開發可用 log 驗證事件內容。

---

## 5. 小結

- Broadcasting 讓 Laravel 事件即時推送到前端，實現 WebSocket。
- 前端 Echo 支援多種驅動（Reverb、Pusher、Ably），設定方式彈性。
- 頻道授權、事件命名、queue 啟動都要注意。
- 適合即時互動、通知、多人協作等場景。

---

## 6. 完整實作範例

### 6.1 Controller 觸發事件
```php
// app/Http/Controllers/OrderController.php
namespace App\Http\Controllers;

use App\Events\OrderShipmentStatusUpdated;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function updateShipmentStatus(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        // 假設這裡更新出貨狀態
        $order->shipment_status = $request->input('status');
        $order->save();

        // 觸發事件，推播到前端
        OrderShipmentStatusUpdated::dispatch($order);

        return response()->json(['success' => true]);
    }
}
```

---

### 6.2 Job 觸發事件（如需非同步處理）
```php
// app/Jobs/NotifyOrderShipmentStatus.php
namespace App\Jobs;

use App\Events\OrderShipmentStatusUpdated;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyOrderShipmentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {
        $order = Order::find($this->orderId);
        if ($order) {
            OrderShipmentStatusUpdated::dispatch($order);
        }
    }
}
```

---

### 6.3 前端元件實作

#### Vue 3 Composition API
```js
// 路徑：LaravelProject/resources/js/components/OrderStatus.vue

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const orderId = 123; // 假設有訂單 ID
const shipmentStatus = ref('');

function handleStatusUpdate(e) {
    shipmentStatus.value = e.order.shipment_status;
    alert('訂單狀態已更新：' + shipmentStatus.value);
}

onMounted(() => {
    window.Echo.private('orders.' + orderId)
        .listen('OrderShipmentStatusUpdated', handleStatusUpdate);
});

onUnmounted(() => {
    window.Echo.leave('orders.' + orderId);
});
</script>

<template>
  <div>
    <p>目前出貨狀態：{{ shipmentStatus }}</p>
  </div>
</template>
```

#### React Hook
```js
// 路徑：LaravelProject/resources/js/components/OrderStatus.js

import { useState } from 'react';
import { useEcho } from '@laravel/echo-react';

export default function OrderStatus({ orderId }) {
    const [status, setStatus] = useState('');

    useEcho(
        `orders.${orderId}`,
        'OrderShipmentStatusUpdated',
        (e) => {
            setStatus(e.order.shipment_status);
            alert('訂單狀態已更新：' + e.order.shipment_status);
        },
    );

    return <div>目前出貨狀態：{status}</div>;
}
```

---

// 這樣您就有 Controller、Job、事件、頻道授權、前端元件一條龍的完整 Broadcasting 實作範例！ 

---

## 7. Broadcasting 概念總覽與進階細節

### 7.1 Broadcasting 核心概念
- Broadcasting 讓伺服器端事件推送到前端 JS，實現 WebSocket 即時互動。
- 支援多種驅動：Reverb、Pusher Channels、Ably。
- 前端用 Laravel Echo 套件接收事件。

---

### 7.2 頻道（Channel）類型
- **Public Channel**：任何人都能訂閱，無需認證。
- **Private Channel**：需登入且授權才能訂閱（如 user、order 等私有資料）。
- **Presence Channel**：進階版 private channel，可追蹤誰在線上。

---

### 7.3 事件定義與廣播

// 單一頻道
```php
public function broadcastOn()
{
    return new PrivateChannel('orders.' . $this->order->id);
}
```
// 多頻道
```php
public function broadcastOn(): array
{
    return [
        new PrivateChannel('orders.' . $this->order->id),
        // 其他頻道
    ];
}
```
// 自訂事件名稱
```php
public function broadcastAs(): string
{
    return 'server.created';
}
```
// 自訂 payload
```php
public function broadcastWith(): array
{
    return ['id' => $this->user->id];
}
```

---

### 7.4 頻道授權（routes/channels.php）
```php
Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    return $user->id === \App\Models\Order::findOrNew($orderId)->user_id;
});
```

---

### 7.5 前端 Echo 監聽
```js
window.Echo.private('orders.' + orderId)
    .listen('OrderShipmentStatusUpdated', (e) => {
        console.log(e.order);
    });
// 若有自訂事件名稱：
.listen('.server.created', ...)
```

---

### 7.6 廣播 Queue 設定、條件式廣播、交易整合

// Q: 廣播事件會進 queue 嗎？
// A: 會，預設進 queue，可自訂 connection/queue。
```php
public $connection = 'redis';
public $queue = 'default';
// 或
public function broadcastQueue(): string { return 'default'; }
```
// Q: 要同步廣播？
// A: 用 ShouldBroadcastNow 介面。

// Q: 條件式廣播？
```php
public function broadcastWhen(): bool
{
    return $this->order->value > 100;
}
```
// Q: 與資料庫交易整合？
// A: 事件 implements ShouldDispatchAfterCommit，確保 commit 後才 dispatch。
```php
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
class ServerCreated implements ShouldBroadcast, ShouldDispatchAfterCommit {}
```

---

### 7.7 小結
- Broadcasting 讓事件即時推送到前端，支援多種頻道與授權。
- 事件 class 要 implements ShouldBroadcast，定義 broadcastOn()。
- 可自訂事件名稱、payload、queue、條件。
- 頻道授權、queue 啟動、資料一致性都要注意。 

---

## 8. 前端 Echo 監聽 OrderShipmentStatusUpdated 實作範例

### Vue 3 Composition API
```js
<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const orderId = 123;
const shipmentStatus = ref('');

function handleStatusUpdate(e) {
    shipmentStatus.value = e.order.shipment_status;
    alert('訂單狀態已更新：' + shipmentStatus.value);
}

onMounted(() => {
    window.Echo.private('orders.' + orderId)
        .listen('OrderShipmentStatusUpdated', handleStatusUpdate);
});

onUnmounted(() => {
    window.Echo.leave('orders.' + orderId);
});
</script>

<template>
  <div>
    <p>目前出貨狀態：{{ shipmentStatus }}</p>
  </div>
</template>
```

### React Hook
```js
// 路徑：LaravelProject/resources/js/components/OrderStatus.js

import { useState } from 'react';
import { useEcho } from '@laravel/echo-react';

export default function OrderStatus({ orderId }) {
    const [status, setStatus] = useState('');

    useEcho(
        `orders.${orderId}`,
        'OrderShipmentStatusUpdated',
        (e) => {
            setStatus(e.order.shipment_status);
            alert('訂單狀態已更新：' + e.order.shipment_status);
        },
    );

    return <div>目前出貨狀態：{status}</div>;
}
```

---

## 9. Broadcasting 頻道授權（Authorizing Channels）

### 9.1 為什麼要授權？
- Private/Presence Channel 需要驗證「目前登入的 user 是否有權限訂閱這個頻道」。
- Laravel 會自動註冊 /broadcasting/auth 路由，Echo 會自動發送授權請求。

---

### 9.2 定義授權邏輯（routes/channels.php）
```php
// 路徑：LaravelProject/routes/channels.php
use App\Models\User;
use App\Models\Order;

Broadcast::channel('orders.{orderId}', function (User $user, int $orderId) {
    return $user->id === Order::findOrNew($orderId)->user_id;
});
```
- 第一個參數是目前登入的 user，後面是頻道的 wildcard 參數。
- 回傳 true 代表授權通過，false 代表拒絕。

---

### 9.3 Model Binding
```php
Broadcast::channel('orders.{order}', function (User $user, Order $order) {
    return $user->id === $order->user_id;
});
```
- 直接 type-hint Model，Laravel 會自動注入。

---

### 9.4 多重認證守衛（guards）
```php
Broadcast::channel('channel', function () {
    // ...
}, ['guards' => ['web', 'admin']]);
```

---

### 9.5 Artisan 工具
- 查看所有授權 callback：
  ```bash
  php artisan channel:list
  ```

---

### 9.6 使用 Channel Class 管理授權
```bash
php artisan make:channel OrderChannel
```
- 註冊 channel class：
```php
use App\Broadcasting\OrderChannel;
Broadcast::channel('orders.{order}', OrderChannel::class);
```
- Channel class 實作 join 方法：
```php
namespace App\Broadcasting;

use App\Models\Order;
use App\Models\User;

class OrderChannel
{
    public function join(User $user, Order $order): array|bool
    {
        return $user->id === $order->user_id;
    }
}
```

---

### 9.7 Q&A 註解
// Q: Private/Presence Channel 為什麼要授權？  
// A: 保護敏感資料，只有有權限的 user 才能訂閱。

// Q: 授權邏輯寫在哪？  
// A: 寫在 routes/channels.php，用 Broadcast::channel 註冊。

// Q: 可以用 Model Binding 嗎？  
// A: 可以，直接 type-hint Model，Laravel 會自動注入。

// Q: 可以用 class 管理授權嗎？  
// A: 可以，artisan make:channel 產生 class，註冊到 routes/channels.php。

// Q: 沒有通過授權會怎樣？  
// A: 前端 Echo 會自動收到授權失敗，無法訂閱頻道。 

---

## 10. Broadcasting 進階用法與補充

### 10.1 只推播給其他人（toOthers）
- 避免自己收到重複推播（如表單送出後，自己已經有資料）。
```php
broadcast(new OrderShipmentStatusUpdated($order))->toOthers();
```
- 事件需 use InteractsWithSockets trait。

---

### 10.2 多連線支援（via/broadcastVia）
- 即時指定 driver：
```php
broadcast(new OrderShipmentStatusUpdated($order))->via('pusher');
```
- 事件 class 預設 driver：
```php
use Illuminate\Broadcasting\InteractsWithBroadcasting;
class OrderShipmentStatusUpdated implements ShouldBroadcast
{
    use InteractsWithBroadcasting;
    public function __construct() { 
        
        $this->broadcastVia('pusher'); 
        
        }
}
```

---

### 10.3 匿名事件（Anonymous Events）
- 不用自訂事件 class，也能推播事件到前端。
```php
Broadcast::on('orders.'.$order->id)->send();
Broadcast::on('orders.'.$order->id)
    ->as('OrderPlaced')
    ->with($order)
    ->send();
Broadcast::private('orders.'.$order->id)->send();
Broadcast::presence('channels.'.$channel->id)->send();
Broadcast::on('orders.'.$order->id)->sendNow(); // 立即推播
Broadcast::on('orders.'.$order->id)->toOthers()->send(); // 只推播給其他人
```

---

### 10.4 Rescue 機制（ShouldRescue）
- 推播失敗時自動捕捉例外，不會影響主流程，適合非關鍵推播。
```php
use Illuminate\Contracts\Broadcasting\ShouldRescue;
class ServerCreated implements ShouldBroadcast, ShouldRescue
{
    // ...
}
```

---

### 10.5 Q&A 註解
// Q: 為什麼要用 toOthers？  
// A: 避免自己收到重複推播（如表單送出後，自己已經有資料）。

// Q: 匿名事件有什麼用？  
// A: 不用自訂事件 class，也能快速推播事件到前端，適合臨時、簡單需求。

// Q: broadcastVia/via 差在哪？  
// A: via 是即時指定 driver，broadcastVia 是事件 class 預設 driver。

// Q: ShouldRescue 有什麼用？  
// A: 推播失敗時自動捕捉例外，不會影響主流程，適合非關鍵推播。 

---

## 11. toOthers 與匿名事件前後端完整實作

### 11.1 toOthers 前後端完整流程

#### 後端
```php
// app/Events/TaskCreated.php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TaskCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $task;
    public function __construct($task)
    {
        $this->task = $task;
    }
    public function broadcastOn()
    {
        return ['tasks'];
    }
}
```

```php
// app/Http/Controllers/TaskController.php
use App\Events\TaskCreated;

public function store(Request $request)
{
    $task = Task::create($request->all()); // 建立一個新的任務，資料來自前端表單
    // 只推播給其他人（不推播給自己，避免自己收到重複事件）
    broadcast(new TaskCreated($task))->toOthers(); // 廣播事件，但只推播給其他人，自己這個瀏覽器不會收到
    return response()->json($task); // 回傳新建立的任務資料給前端
}
```

#### 前端
```js
// 建立任務時，自己直接更新列表
axios.post('/task', task)
    .then((response) => {
        this.tasks.push(response.data);
    });

// 監聽推播事件，只有其他人會收到
window.Echo.channel('tasks')
    .listen('TaskCreated', (e) => {
        this.tasks.push(e.task);
    });
```

---

### 11.2 匿名事件前端監聽範例

#### 後端
```php
Broadcast::on('orders.'.$order->id)
    ->as('OrderPlaced')
    ->with($order)
    ->send();
```

#### 前端
```js
window.Echo.channel('orders.' + orderId)
    .listen('.OrderPlaced', (e) => {
        console.log('收到匿名事件', e);
    });
```

--- 

## 12. 前端接收 Broadcasting 事件（Receiving Broadcasts）

### 12.1 監聽事件（Listen for Events）
```js
// Public channel
Echo.channel(`orders.${orderId}`)
    .listen('OrderShipmentStatusUpdated', (e) => {
        console.log(e.order.name);
    });

// Private channel
Echo.private(`orders.${orderId}`)
    .listen('OrderShipmentStatusUpdated', (e) => {
        console.log(e.order.name);
    });

// 鏈式監聽多個事件
Echo.private(`orders.${orderId}`)
    .listen('OrderShipmentStatusUpdated', ...)
    .listen('OrderShipped', ...)
    .listen('OrderPaid', ...);
```

---

### 12.2 停止監聽/離開頻道
```js
// 路徑：LaravelProject/resources/js/components/OrderStatus.js
// 停止監聽某事件（不離開頻道）
Echo.private(`orders.${orderId}`)
    .stopListening('OrderShipmentStatusUpdated'); // 只停止監聽這個事件，頻道連線還在

// 離開頻道（推薦在頁面卸載、聊天室切換時呼叫）
Echo.leaveChannel(`orders.${orderId}`); // 只離開指定頻道，釋放資源，避免重複監聽
// 或同時離開 private/presence
Echo.leave(`orders.${orderId}`); // 完全離開頻道，Presence Channel 會同步線上狀態
```

// 補充說明：
// - 為什麼要 leave/leaveChannel？
//   1. 釋放資源，避免重複監聽與記憶體浪費。
//   2. 用戶離開頁面或聊天室時，應離開頻道，否則會繼續收到不相關推播。
//   3. Presence Channel 需同步線上成員，沒 leave 會造成「幽靈用戶」。
// - 為什麼要 stopListening？
//   1. 暫時不處理某事件，或動態切換監聽事件時使用。
//   2. 避免 callback 重複註冊，導致一個事件多次觸發。

---

### 12.3 命名空間（Namespace）
```js
window.Echo = new Echo({
    broadcaster: 'pusher',
    namespace: 'App.Other.Namespace'
});
// 或監聽時加 . 前綴
Echo.channel('orders')
    .listen('.Namespace\\Event\\Class', (e) => { ... });
```

---

### 12.4 React/Vue Hook 用法

#### React
```js
import { useEcho } from "@laravel/echo-react";

useEcho(
    `orders.${orderId}`,
    "OrderShipmentStatusUpdated",
    (e) => {
        console.log(e.order);
    },
);

// 監聽多個事件
useEcho(
    `orders.${orderId}`,
    ["OrderShipmentStatusUpdated", "OrderShipped"],
    (e) => {
        console.log(e.order);
    },
);

// 型別安全
useEcho<OrderData>(`orders.${orderId}`, "OrderShipmentStatusUpdated", (e) => {
    console.log(e.order.id);
    console.log(e.order.user.id);
});

// 手動控制
const { leaveChannel, leave, stopListening, listen } = useEcho(...);
stopListening(); // 停止監聽
listen();        // 重新監聽
leaveChannel();  // 離開頻道
leave();         // 離開所有相關頻道
```

#### Vue
// Vue 3 也有對應的 Echo hook（如 useEcho/useEchoPublic/useEchoPresence）。

---

### 12.5 Public/Presence Channel Hook
```js
// Public channel
import { useEchoPublic } from "@laravel/echo-react";
useEchoPublic("posts", "PostPublished", (e) => {
    console.log(e.post);
});

// Presence channel
import { useEchoPresence } from "@laravel/echo-react";
useEchoPresence("chatroom.1", "MessageSent", (e) => {
    console.log(e.message);
});
```

---

### 12.6 Presence Channel 成員列表實作範例

#### 後端
```php
// routes/channels.php
Broadcast::channel('chatroom.{roomId}', function ($user, $roomId) {
    return Room::findOrNew($roomId)->users->contains($user->id);
});
```

#### 前端（React）
```js
import { useEchoPresence } from "@laravel/echo-react";
import { useState } from "react";

export default function ChatRoom({ roomId }) {
    const [members, setMembers] = useState([]);
    const [messages, setMessages] = useState([]);

    useEchoPresence(
        `chatroom.${roomId}`,
        "MessageSent",
        (e, { members: currentMembers }) => {
            setMessages((msgs) => [...msgs, e.message]);
            setMembers(currentMembers);
        }
    );

    return (
        <div>
            <h3>線上成員：</h3>
            <ul>
                {members.map((m) => (
                    <li key={m.id}>{m.name}</li>
                ))}
            </ul>
            <h3>訊息：</h3>
            <ul>
                {messages.map((msg, i) => (
                    <li key={i}>{msg}</li>
                ))}
            </ul>
        </div>
    );
}
```

---

### 12.7 Q&A 註解
// Q: listen 跟 stopListening 差在哪？  
// A: listen 是監聽事件，stopListening 是停止監聽但不離開頻道。

// Q: leave 跟 leaveChannel 差在哪？  
// A: leave 會同時離開 private/presence channel，leaveChannel 只離開指定頻道。

// Q: 事件名稱要加 namespace 嗎？  
// A: 預設不用，Echo 會自動加 App\Events\，如需自訂可用 . 前綴。

// Q: React/Vue hook 有什麼好處？  
// A: 自動管理頻道生命週期，元件卸載時自動離開頻道，程式更簡潔。 

---

## 13. Presence Channel（存在頻道）重點整理

### 13.1 概念與用途
- Presence Channel 是 Private Channel 的進階版，除了安全性外，還能讓前端知道「有哪些用戶在線上」。
- 適合聊天室、協作、即時線上人員列表等場景。

---

### 13.2 授權 callback 實作
```php
use App\Models\User;
Broadcast::channel('chat.{roomId}', function (User $user, int $roomId) {
    if ($user->canJoinRoom($roomId)) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false;
});
```
- 必須回傳用戶資料陣列，前端才能顯示成員列表。

---

### 13.3 前端 Echo.join 用法
```js
Echo.join(`chat.${roomId}`)
    .here((users) => {
        // users 為目前所有在線成員
        console.log('目前在線', users);
    })
    .joining((user) => {
        // 有新成員加入
        console.log('加入', user.name);
    })
    .leaving((user) => {
        // 有成員離開
        console.log('離開', user.name);
    })
    .error((error) => {
        console.error(error);
    });
```

---

### 13.4 廣播事件與監聽
```php
// 事件的 broadcastOn() 回傳 PresenceChannel
use Illuminate\Broadcasting\PresenceChannel;
public function broadcastOn(): array
{
    return [
        new PresenceChannel('chat.'.$this->message->room_id),
    ];
}

// 廣播事件
broadcast(new NewMessage($message));
broadcast(new NewMessage($message))->toOthers();
```

```js
// 前端監聽事件
Echo.join(`chat.${roomId}`)
    .listen('NewMessage', (e) => {
        // 收到新訊息
        console.log(e);
    });
```

---

### 13.5 Q&A 註解
// Q: Presence Channel 跟 Private Channel 差在哪？  
// A: Presence Channel 除了安全性，還能讓前端知道有哪些用戶在線（如聊天室成員列表）。

// Q: 授權 callback 要回傳什麼？  
// A: 要回傳用戶資料陣列（如 id、name），前端才能顯示成員列表。

// Q: Echo.join 跟 Echo.private 差在哪？  
// A: join 用於 presence channel，private 用於 private channel。join 支援 here/joining/leaving 事件。

// Q: 可以同時監聽成員變動和自訂事件嗎？  
// A: 可以，Echo.join 支援 here/joining/leaving 也支援 listen 監聽自訂事件。

---

### 13.6 前後端完整實作範例

#### 後端（routes/channels.php）
```php
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    if ($user->canJoinRoom($roomId)) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false;
});
```

#### 前端（JS）
```js
Echo.join(`chat.${roomId}`)
    .here((users) => {
        // users: 所有在線成員
    })
    .joining((user) => {
        // 新成員加入
    })
    .leaving((user) => {
        // 成員離開
    })
    .listen('NewMessage', (e) => {
        // 收到新訊息
    });
```