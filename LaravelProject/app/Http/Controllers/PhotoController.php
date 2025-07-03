<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Photo;

/**
 * PhotoController（資源控制器 Resource Controller）
 *
 * [設計理念]
 * - 每個 Eloquent model（如 Photo、Movie）都可視為一種「資源」，通常會有相同的 CRUD 操作（建立、查詢、更新、刪除）。
 * - Laravel 的資源路由可用一行 Route::resource，自動對應所有 CRUD 路由與控制器方法，提升開發效率與一致性。
 *
 * [Artisan 指令快速產生]
 *   php artisan make:controller PhotoController --resource
 *   // 自動產生 CRUD 方法的控制器
 *   php artisan make:controller PhotoController --model=Photo --resource
 *   // 自動產生 FormRequest
 *   php artisan make:controller PhotoController --model=Photo --resource --requests
 *   // 產生 API 專用控制器（不含 create/edit）
 *   php artisan make:controller PhotoController --api
 *
 * [路由註冊]
 *   use App\Http\Controllers\PhotoController;
 *   Route::resource('photos', PhotoController::class);
 *   // 可同時註冊多個資源控制器：
 *   Route::resources([
 *       'photos' => PhotoController::class,
 *       'posts' => PostController::class,
 *   ]);
 *
 * [Partial Resource Routes（部分資源路由）]
 * - 只註冊部分 action：
 *   Route::resource('photos', PhotoController::class)->only(['index', 'show']);
 * - 排除部分 action：
 *   Route::resource('photos', PhotoController::class)->except(['create', 'store', 'update', 'destroy']);
 *
 * [API Resource Routes]
 * - API 常排除 create/edit（HTML 表單頁），可用 apiResource：
 *   Route::apiResource('photos', PhotoController::class);
 * - 同時註冊多個 API 資源控制器：
 *   Route::apiResources([
 *       'photos' => PhotoController::class,
 *       'posts' => PostController::class,
 *   ]);
 *
 * [CRUD 對應表]
 * | 動詞      | URI                    | 方法    | 路由名稱         |
 * |-----------|-----------------------|---------|-----------------|
 * | GET       | /photos               | index   | photos.index    |
 * | GET       | /photos/create        | create  | photos.create   |
 * | POST      | /photos               | store   | photos.store    |
 * | GET       | /photos/{photo}       | show    | photos.show     |
 * | GET       | /photos/{photo}/edit  | edit    | photos.edit     |
 * | PUT/PATCH | /photos/{photo}       | update  | photos.update   |
 * | DELETE    | /photos/{photo}       | destroy | photos.destroy  |
 *
 * [進階用法]
 * - missing：自訂找不到模型時的行為
 *   Route::resource('photos', PhotoController::class)
 *       ->missing(function (Request $request) {
 *           return Redirect::route('photos.index');
 *       });
 * - withTrashed：支援軟刪除模型
 *   Route::resource('photos', PhotoController::class)->withTrashed();
 *   // 只針對 show 支援軟刪除：->withTrashed(['show'])
 * - --model：產生時自動 type-hint 綁定 Model
 * - --requests：產生時自動建立 store/update 的 FormRequest 類別
 *
 * [團隊建議]
 * - 建議所有 Eloquent 資源皆用資源控制器統一管理 CRUD。
 * - 每個方法加上用途註解，方便維護。
 * - 若有特殊行為（如 missing、withTrashed），請於路由註解說明。
 * - 可用 php artisan route:list 快速檢查所有資源路由。
 */
class PhotoController extends Controller
{
    /**
     * 顯示所有照片
     * GET /photos
     */
    public function index()
    {
        return response()->json([
            'message' => '顯示所有照片',
        ]);
    }

    /**
     * 顯示新增照片表單
     * GET /photos/create
     */
    public function create()
    {
        return response()->json([
            'message' => '顯示新增照片表單',
        ]);
    }

    /**
     * 新增照片
     * POST /photos
     * @param Request $request
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => '新增照片',
        ]);
    }

    /**
     * 顯示單一照片
     * GET /photos/{photo}
     * @param int|Photo $photo
     */
    public function show($photo)
    {
        return response()->json([
            'message' => '顯示 photo ' . (is_object($photo) ? $photo->id : $photo),
        ]);
    }

    /**
     * 顯示編輯照片表單
     * GET /photos/{photo}/edit
     * @param int|Photo $photo
     */
    public function edit($photo)
    {
        return response()->json([
            'message' => '顯示 photo ' . (is_object($photo) ? $photo->id : $photo) . ' 的編輯表單',
        ]);
    }

    /**
     * 更新照片
     * PUT/PATCH /photos/{photo}
     * @param Request $request
     * @param int|Photo $photo
     */
    public function update(Request $request, $photo)
    {
        return response()->json([
            'message' => '更新 photo ' . (is_object($photo) ? $photo->id : $photo),
        ]);
    }

    /**
     * 刪除照片
     * DELETE /photos/{photo}
     * @param int|Photo $photo
     */
    public function destroy($photo)
    {
        return response()->json([
            'message' => '刪除 photo ' . (is_object($photo) ? $photo->id : $photo),
        ]);
    }
} 