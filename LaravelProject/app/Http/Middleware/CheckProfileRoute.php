<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 判斷目前請求是否為 profile 這條命名路由
        // 這個方法可用於 middleware、controller、service 等任何有 $request 物件的地方
        if ($request->route()->named('profile')) {
            // 這裡可以針對 profile 路由做特別處理
        }

        return $next($request);
    }
} 