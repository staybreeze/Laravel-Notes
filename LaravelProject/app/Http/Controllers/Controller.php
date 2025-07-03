<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
abstract class Controller
{
    // 範例：在 controller 裡判斷目前請求是否為某條命名路由
    // use Illuminate\Http\Request;
    public function show(Request $request)
    {
        if ($request->route()->named('profile')) {
            // 如果是 profile 這條路由，做特別處理
        }
        // 其他邏輯
    }

    // -----------------------------------------------------------------------------
    // 取得目前請求的路由資訊（Accessing the Current Route）
    // -----------------------------------------------------------------------------
    // 可用 Route facade 的方法取得目前處理請求的路由資訊：
    // - Route::current()              // 取得 Route 物件，可取 uri、參數等
    // - Route::currentRouteName()     // 取得目前路由名稱（需有命名）
    // - Route::currentRouteAction()   // 取得 Controller@method 字串
    // 實務用途：動態顯示狀態、權限判斷、日誌、麵包屑等
    // -----------------------------------------------------------------------------
    // use Illuminate\Support\Facades\Route;
    public function showInfo()
    {
        $route = Route::current();
        $name = Route::currentRouteName();
        $action = Route::currentRouteAction();

        logger("目前路由 URI：" . $route->uri());
        logger("目前路由名稱：" . $name);
        logger("目前路由 Action：" . $action);
    }
}
