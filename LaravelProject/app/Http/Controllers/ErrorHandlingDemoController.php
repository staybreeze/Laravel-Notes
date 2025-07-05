<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\InvalidOrderException;
use App\Exceptions\PodcastProcessingException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ErrorHandlingDemoController extends Controller
{
    /**
     * 示範基本的錯誤處理
     */
    public function basicErrorHandling()
    {
        try {
            // 模擬可能出錯的操作
            $result = $this->riskyOperation();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            // 記錄錯誤但不中斷流程
            report($e);
            
            return response()->json([
                'success' => false,
                'message' => '操作失敗，請稍後再試'
            ], 500);
        }
    }

    /**
     * 示範自訂例外
     */
    public function customException()
    {
        $orderId = 'ORD-' . time();
        
        // 拋出自訂例外
        throw new InvalidOrderException(
            '訂單金額超過限制',
            $orderId,
            422
        );
    }

    /**
     * 示範不記錄的例外
     */
    public function nonReportableException()
    {
        $podcastId = 'POD-' . time();
        
        // 這個例外不會被記錄
        throw new PodcastProcessingException(
            '播客處理暫時失敗',
            $podcastId,
            'audio_processing'
        );
    }

    /**
     * 示範 abort 輔助函式
     */
    public function abortDemo(Request $request)
    {
        $action = $request->get('action');
        
        switch ($action) {
            case 'not_found':
                abort(404, '找不到指定的資源');
                break;
                
            case 'unauthorized':
                abort(401, '您沒有權限執行此操作');
                break;
                
            case 'forbidden':
                abort(403, '此操作被禁止');
                break;
                
            case 'validation':
                abort(422, '資料驗證失敗');
                break;
                
            case 'server_error':
                abort(500, '伺服器內部錯誤');
                break;
                
            default:
                return response()->json([
                    'message' => '請指定 action 參數',
                    'available_actions' => [
                        'not_found', 'unauthorized', 'forbidden', 
                        'validation', 'server_error'
                    ]
                ]);
        }
    }

    /**
     * 示範條件性錯誤處理
     */
    public function conditionalErrorHandling(Request $request)
    {
        $userId = $request->get('user_id');
        $action = $request->get('action');
        
        try {
            // 模擬資料庫查詢
            $user = $this->findUser($userId);
            
            if (!$user) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'User not found',
                        'message' => '找不到指定的用戶'
                    ], 404);
                }
                
                abort(404, '找不到指定的用戶');
            }
            
            // 檢查權限
            if (!$this->canPerformAction($user, $action)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Unauthorized',
                        'message' => '您沒有權限執行此操作'
                    ], 403);
                }
                
                abort(403, '您沒有權限執行此操作');
            }
            
            return response()->json([
                'success' => true,
                'user' => $user,
                'action' => $action
            ]);
            
        } catch (\Exception $e) {
            // 記錄錯誤
            Log::error('Conditional error handling failed', [
                'user_id' => $userId,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Internal Server Error',
                    'message' => '系統錯誤，請稍後再試'
                ], 500);
            }
            
            abort(500, '系統錯誤，請稍後再試');
        }
    }

    /**
     * 示範錯誤處理最佳實踐
     */
    public function bestPractices()
    {
        return response()->json([
            'message' => '錯誤處理最佳實踐示範',
            'examples' => [
                'basic_error_handling' => url('/demo/basic-error-handling'),
                'custom_exception' => url('/demo/custom-exception'),
                'non_reportable' => url('/demo/non-reportable'),
                'abort_demo' => url('/demo/abort?action=not_found'),
                'conditional' => url('/demo/conditional?user_id=123&action=edit'),
            ],
            'tips' => [
                'always_log_errors' => '總是記錄錯誤資訊',
                'use_custom_exceptions' => '使用自訂例外處理業務邏輯',
                'provide_helpful_messages' => '提供有用的錯誤訊息',
                'handle_json_requests' => '正確處理 JSON 請求',
                'use_appropriate_status_codes' => '使用適當的 HTTP 狀態碼'
            ]
        ]);
    }

    /**
     * 模擬有風險的操作
     */
    private function riskyOperation()
    {
        // 模擬隨機錯誤
        if (rand(1, 10) > 7) {
            throw new \Exception('隨機錯誤發生');
        }
        
        return '操作成功';
    }

    /**
     * 模擬查找用戶
     */
    private function findUser($userId)
    {
        // 模擬資料庫查詢
        if ($userId == '123') {
            return [
                'id' => 123,
                'name' => '測試用戶',
                'email' => 'test@example.com'
            ];
        }
        
        return null;
    }

    /**
     * 模擬權限檢查
     */
    private function canPerformAction($user, $action)
    {
        // 模擬權限檢查邏輯
        $allowedActions = ['view', 'edit', 'delete'];
        
        return in_array($action, $allowedActions);
    }
} 