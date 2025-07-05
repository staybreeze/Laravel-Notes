<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestId
{
    /**
     * 處理傳入的請求
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 生成唯一的請求 ID
        $requestId = (string) Str::uuid();
        
        // 設定日誌上下文
        Log::withContext([
            'request-id' => $requestId,
            'user-agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);
        
        // 記錄請求開始
        Log::info('Request started', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => $request->user()?->id,
        ]);
        
        $response = $next($request);
        
        // 設定回應標頭
        $response->headers->set('Request-Id', $requestId);
        
        // 記錄請求結束
        Log::info('Request completed', [
            'status' => $response->getStatusCode(),
            'duration' => microtime(true) - LARAVEL_START,
        ]);
        
        return $response;
    }
} 