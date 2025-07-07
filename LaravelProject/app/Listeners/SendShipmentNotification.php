<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;
use DateTime;

class SendShipmentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * 指定 queue 連線名稱
     * @var string|null
     */
    public $connection = 'sqs';

    /**
     * 指定 queue 名稱
     * @var string|null
     */
    public $queue = 'listeners';

    /**
     * 指定延遲秒數
     * @var int
     */
    public $delay = 60;

    /**
     * 最大重試次數
     * @var int
     */
    public $tries = 5;

    /**
     * 指定每次重試間隔秒數
     * @var int
     */
    public $backoff = 3;

    /**
     * 建構子，可注入依賴
     */
    public function __construct() {}

    /**
     * 處理事件
     */
    public function handle(OrderShipped $event): void
    {
        // 取得訂單資料
        $order = $event->order;
        // ... 實際處理邏輯 ...
        // 若需釋放回 queue 可用 $this->release(30);
    }

    /**
     * 決定是否要進入 queue
     */
    public function shouldQueue(OrderShipped $event): bool
    {
        // 只有訂單金額大於 5000 才進 queue
        return $event->order->subtotal >= 5000;
    }

    /**
     * 失敗時的處理
     */
    public function failed(OrderShipped $event, Throwable $exception): void
    {
        // 可記錄 log、通知等
    }

    /**
     * 指定 queue 連線名稱（動態）
     */
    public function viaConnection(): string
    {
        return 'sqs';
    }

    /**
     * 指定 queue 名稱（動態）
     */
    public function viaQueue(): string
    {
        return 'listeners';
    }

    /**
     * 指定延遲秒數（動態）
     */
    public function withDelay(OrderShipped $event): int
    {
        return $event->order->highPriority ? 0 : 60;
    }

    /**
     * 指定重試截止時間
     */
    public function retryUntil(): DateTime
    {
        return now()->addMinutes(5);
    }

    /**
     * 指定重試間隔（進階：可回傳陣列做 exponential backoff）
     * @return list<int>
     */
    public function backoff(): array
    {
        return [1, 5, 10];
    }
} 