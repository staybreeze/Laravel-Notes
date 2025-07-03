<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\AdminUser;

/**
 * AdminUserController
 *
 * 對應 Route::resource('users', AdminUserController::class)->parameters(['users' => 'admin_user'])
 * 會自動產生 /users/{admin_user} 等 RESTful 路由
 *
 * 產生的路由：
 * GET    /users                  -> index
 * GET    /users/create           -> create
 * POST   /users                  -> store
 * GET    /users/{admin_user}     -> show
 * GET    /users/{admin_user}/edit-> edit
 * PUT    /users/{admin_user}     -> update
 * DELETE /users/{admin_user}     -> destroy
 *
 * 注意：路由參數名稱已自訂為 {admin_user}
 */
class AdminUserController extends Controller
{
    /**
     * 顯示所有管理員使用者
     * GET /users
     */
    public function index()
    {
        return response()->json([
            'message' => '顯示所有管理員使用者',
        ]);
    }

    /**
     * 顯示新增管理員使用者表單
     * GET /users/create
     */
    public function create()
    {
        return response()->json([
            'message' => '顯示新增管理員使用者表單',
        ]);
    }

    /**
     * 新增管理員使用者
     * POST /users
     * @param Request $request
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => '新增管理員使用者',
        ]);
    }

    /**
     * 顯示單一管理員使用者
     * GET /users/{admin_user}
     * @param int|AdminUser $admin_user
     */
    public function show($admin_user)
    {
        return response()->json([
            'message' => '顯示 admin_user ' . (is_object($admin_user) ? $admin_user->id : $admin_user),
        ]);
    }

    /**
     * 顯示編輯管理員使用者表單
     * GET /users/{admin_user}/edit
     * @param int|AdminUser $admin_user
     */
    public function edit($admin_user)
    {
        return response()->json([
            'message' => '顯示 admin_user ' . (is_object($admin_user) ? $admin_user->id : $admin_user) . ' 的編輯表單',
        ]);
    }

    /**
     * 更新管理員使用者
     * PUT/PATCH /users/{admin_user}
     * @param Request $request
     * @param int|AdminUser $admin_user
     */
    public function update(Request $request, $admin_user)
    {
        return response()->json([
            'message' => '更新 admin_user ' . (is_object($admin_user) ? $admin_user->id : $admin_user),
        ]);
    }

    /**
     * 刪除管理員使用者
     * DELETE /users/{admin_user}
     * @param int|AdminUser $admin_user
     */
    public function destroy($admin_user)
    {
        return response()->json([
            'message' => '刪除 admin_user ' . (is_object($admin_user) ? $admin_user->id : $admin_user),
        ]);
    }
} 