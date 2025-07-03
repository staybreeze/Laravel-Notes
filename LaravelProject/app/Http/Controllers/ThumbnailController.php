<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * ThumbnailController
 *
 * 對應 Route::singleton('photos.thumbnail', ThumbnailController::class)
 * 以及 Route::apiSingleton('photos.thumbnail', ThumbnailController::class)->creatable()
 *
 * 產生的路由：
 * GET    /photos/{photo}/thumbnail           -> show   photos.thumbnail.show
 * GET    /photos/{photo}/thumbnail/edit      -> edit   photos.thumbnail.edit
 * PUT    /photos/{photo}/thumbnail           -> update photos.thumbnail.update
 * DELETE /photos/{photo}/thumbnail           -> destroy photos.thumbnail.destroy
 * GET    /photos/{photo}/thumbnail/create    -> create  photos.thumbnail.create（creatable）
 * POST   /photos/{photo}/thumbnail           -> store   photos.thumbnail.store（creatable）
 */
class ThumbnailController extends Controller
{
    /**
     * 顯示縮圖
     * GET /photos/{photo}/thumbnail
     * @param int $photo
     */
    public function show($photo)
    {
        return response()->json([
            'message' => '顯示 photo ' . $photo . ' 的縮圖',
        ]);
    }

    /**
     * 顯示編輯縮圖表單
     * GET /photos/{photo}/thumbnail/edit
     * @param int $photo
     */
    public function edit($photo)
    {
        return response()->json([
            'message' => '顯示 photo ' . $photo . ' 的縮圖編輯表單',
        ]);
    }

    /**
     * 更新縮圖
     * PUT /photos/{photo}/thumbnail
     * @param Request $request
     * @param int $photo
     */
    public function update(Request $request, $photo)
    {
        return response()->json([
            'message' => '更新 photo ' . $photo . ' 的縮圖',
        ]);
    }

    /**
     * 刪除縮圖（僅 destroyable/apiSingleton 會產生此路由）
     * DELETE /photos/{photo}/thumbnail
     * @param int $photo
     */
    public function destroy($photo)
    {
        return response()->json([
            'message' => '刪除 photo ' . $photo . ' 的縮圖',
        ]);
    }

    /**
     * 顯示新增縮圖表單（僅 creatable 會產生此路由）
     * GET /photos/{photo}/thumbnail/create
     * @param int $photo
     */
    public function create($photo)
    {
        return response()->json([
            'message' => '顯示 photo ' . $photo . ' 的新增縮圖表單',
        ]);
    }

    /**
     * 新增縮圖（僅 creatable 會產生此路由）
     * POST /photos/{photo}/thumbnail
     * @param Request $request
     * @param int $photo
     */
    public function store(Request $request, $photo)
    {
        return response()->json([
            'message' => '為 photo ' . $photo . ' 新增縮圖',
        ]);
    }
} 