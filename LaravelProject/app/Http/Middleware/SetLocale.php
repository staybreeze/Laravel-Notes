<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * 根據 request 參數或 header 動態切換語系
     */
    public function handle($request, Closure $next)
    {
        // 1. 從 query string 取得 ?lang=zh-TW
        $locale = $request->query('lang');

        // 2. 或從 header 取得 Accept-Language
        if (!$locale) {
            $locale = $request->header('Accept-Language');
        }

        // 3. 檢查語系是否支援，預設 fallback 為 config/app.php 設定
        $supported = ['zh-TW', 'en', 'ja'];
        if ($locale && in_array($locale, $supported)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
} 