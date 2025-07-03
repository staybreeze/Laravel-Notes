<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * ProfileController
 *
 * 對應 Route::singleton('profile', ProfileController::class)
 * 以及 Route::apiSingleton('profile', ProfileController::class)
 *
 * 產生的路由：
 * GET    /profile           -> show   profile.show
 * GET    /profile/edit      -> edit   profile.edit
 * PUT    /profile           -> update profile.update
 * DELETE /profile           -> destroy profile.destroy（僅 apiSingleton）
 */
class ProfileController extends Controller
{
    /**
     * 顯示個人資料
     * GET /profile
     */
    public function show()
    {
        return response()->json([
            'message' => '顯示個人資料',
        ]);
    }

    /**
     * 顯示編輯個人資料表單
     * GET /profile/edit
     */
    public function edit()
    {
        return response()->json([
            'message' => '顯示個人資料編輯表單',
        ]);
    }

    /**
     * 更新個人資料
     * PUT /profile
     * @param Request $request
     */
    public function update(Request $request)
    {
        return response()->json([
            'message' => '更新個人資料',
        ]);
    }

    /**
     * 刪除個人資料（僅 apiSingleton 會產生此路由）
     * DELETE /profile
     */
    public function destroy()
    {
        return response()->json([
            'message' => '刪除個人資料',
        ]);
    }
} 