<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;

class LoggingDemoController extends Controller
{
    /**
     * 顯示日誌示範頁面
     */
    public function index(): View
    {
        Log::info('User accessed logging demo page');
        
        return view('demo.logging.index');
    }
    
    /**
     * 示範基本日誌記錄
     */
    public function basicLogging(): array
    {
        // 不同等級的日誌記錄
        Log::emergency('系統緊急狀況！');
        Log::alert('需要立即處理的警報');
        Log::critical('嚴重錯誤發生');
        Log::error('一般錯誤訊息');
        Log::warning('警告訊息');
        Log::notice('注意事項');
        Log::info('一般資訊');
        Log::debug('除錯資訊');
        
        return [
            'message' => '基本日誌記錄完成',
            'logs' => '請檢查 storage/logs/laravel.log'
        ];
    }
    
    /**
     * 示範情境資料記錄
     */
    public function contextualLogging(Request $request): array
    {
        $userId = $request->input('user_id', 1);
        $action = $request->input('action', 'login');
        
        // 記錄用戶行為
        Log::info('User {action} attempt', [
            'user_id' => $userId,
            'action' => $action,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        // 記錄錯誤情境
        if ($action === 'login') {
            Log::warning('Failed login attempt', [
                'user_id' => $userId,
                'attempts' => 3,
                'last_attempt' => now(),
            ]);
        }
        
        return [
            'message' => '情境資料記錄完成',
            'user_id' => $userId,
            'action' => $action
        ];
    }
    
    /**
     * 示範指定 Channel 記錄
     */
    public function channelLogging(): array
    {
        // 寫入特定 channel
        Log::channel('single')->info('這條訊息只會寫入 single channel');
        
        // 寫入多個 channels
        Log::stack(['single', 'daily'])->info('這條訊息會寫入多個 channels');
        
        // 動態建立 channel
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/dynamic.log'),
        ])->info('動態建立的 channel 訊息');
        
        return [
            'message' => 'Channel 記錄完成',
            'channels' => ['single', 'daily', 'dynamic']
        ];
    }
    
    /**
     * 示範例外處理與日誌記錄
     */
    public function exceptionLogging(): array
    {
        try {
            // 模擬可能出錯的操作
            $result = 10 / 0;
        } catch (Exception $e) {
            // 記錄例外詳情
            Log::error('Division by zero error', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'message' => '例外已記錄',
                'error' => $e->getMessage()
            ];
        }
        
        return ['message' => '操作成功'];
    }
    
    /**
     * 示範效能監控日誌
     */
    public function performanceLogging(): array
    {
        $startTime = microtime(true);
        
        // 模擬耗時操作
        sleep(1);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // 記錄效能資訊
        Log::info('Performance measurement', [
            'operation' => 'demo_operation',
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ]);
        
        return [
            'message' => '效能監控完成',
            'execution_time_ms' => round($executionTime, 2)
        ];
    }
    
    /**
     * 示範業務邏輯日誌
     */
    public function businessLogging(Request $request): array
    {
        $orderId = $request->input('order_id', 'ORD-' . uniqid());
        $amount = $request->input('amount', 100);
        
        // 記錄訂單建立
        Log::channel('orders')->info('Order created', [
            'order_id' => $orderId,
            'amount' => $amount,
            'user_id' => $request->user()?->id,
            'status' => 'pending',
        ]);
        
        // 記錄付款處理
        Log::channel('payments')->info('Payment processed', [
            'order_id' => $orderId,
            'amount' => $amount,
            'payment_method' => 'credit_card',
            'status' => 'success',
        ]);
        
        // 記錄安全事件
        Log::channel('security')->notice('High value transaction', [
            'order_id' => $orderId,
            'amount' => $amount,
            'risk_level' => $amount > 1000 ? 'high' : 'low',
        ]);
        
        return [
            'message' => '業務邏輯記錄完成',
            'order_id' => $orderId,
            'amount' => $amount
        ];
    }
    
    /**
     * 測試所有日誌等級
     */
    public function testAllLevels(): array
    {
        $levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];
        
        foreach ($levels as $level) {
            Log::$level("測試 {$level} 等級訊息", [
                'level' => $level,
                'timestamp' => now(),
                'test_id' => uniqid(),
            ]);
        }
        
        return [
            'message' => '所有等級測試完成',
            'levels' => $levels
        ];
    }
} 