<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Debug\ShouldntReport;

class InvalidOrderException extends Exception
{
    protected $orderId;

    public function __construct($message = "", $orderId = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->orderId = $orderId;
    }

    /**
     * 取得訂單 ID
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * 報告例外（自訂記錄邏輯）
     */
    public function report(): void
    {
        // 記錄到特殊檔案
        \Log::channel('orders')->error('Invalid order detected', [
            'order_id' => $this->orderId,
            'message' => $this->getMessage(),
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ]);

        // 可以發送通知給管理員
        // Notification::route('mail', 'admin@example.com')
        //     ->notify(new InvalidOrderNotification($this->orderId));
    }

    /**
     * 渲染例外為 HTTP 回應
     */
    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Invalid Order',
                'message' => $this->getMessage(),
                'order_id' => $this->orderId,
            ], 422);
        }

        return response()->view('errors.invalid-order', [
            'orderId' => $this->orderId,
            'message' => $this->getMessage(),
        ], 422);
    }

    /**
     * 取得例外的上下文資訊
     */
    public function context(): array
    {
        return [
            'order_id' => $this->orderId,
            'user_id' => auth()->id(),
            'request_url' => request()->url(),
        ];
    }
} 