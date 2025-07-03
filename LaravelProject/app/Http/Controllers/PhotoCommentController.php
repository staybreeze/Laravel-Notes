<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Photo;
use App\Models\Comment;

/**
 * PhotoCommentController
 *
 * 巢狀資源路由控制器範例
 * 對應 Route::resource('photos.comments', PhotoCommentController::class)
 * 會自動產生 /photos/{photo}/comments/{comment} 等巢狀 RESTful 路由
 *
 * - $photo 參數會自動注入父資源（Photo 模型或 id）
 * - $comment 參數會自動注入子資源（Comment 模型或 id）
 * - 可搭配 Route Model Binding 直接注入模型
 */
class PhotoCommentController extends Controller
{
    /**
     * 顯示某張照片的所有留言
     * GET /photos/{photo}/comments
     *
     * @param int|Photo $photo
     */
    public function index($photo)
    {
        // $photo 可以是 id 或 Photo 模型（若有 Route Model Binding）
        // 取得該照片的所有留言
        // return Comment::where('photo_id', $photo->id ?? $photo)->get();
        return response()->json([
            'message' => '顯示 photo ' . (is_object($photo) ? $photo->id : $photo) . ' 的所有留言',
        ]);
    }

    /**
     * 顯示新增留言表單
     * GET /photos/{photo}/comments/create
     */
    public function create($photo)
    {
        return response()->json([
            'message' => '顯示 photo ' . (is_object($photo) ? $photo->id : $photo) . ' 的新增留言表單',
        ]);
    }

    /**
     * 新增留言
     * POST /photos/{photo}/comments
     */
    public function store(Request $request, $photo)
    {
        // $request->input('content') 可取得留言內容
        return response()->json([
            'message' => '為 photo ' . (is_object($photo) ? $photo->id : $photo) . ' 新增留言',
        ]);
    }

    /**
     * 顯示單一留言
     * GET /photos/{photo}/comments/{comment}
     */
    public function show($photo, $comment)
    {
        return response()->json([
            'message' => '顯示 photo ' . (is_object($photo) ? $photo->id : $photo) . ' 的 comment ' . (is_object($comment) ? $comment->id : $comment),
        ]);
    }

    /**
     * 顯示編輯留言表單
     * GET /photos/{photo}/comments/{comment}/edit
     */
    public function edit($photo, $comment)
    {
        return response()->json([
            'message' => '顯示 photo ' . (is_object($photo) ? $photo->id : $photo) . ' 的 comment ' . (is_object($comment) ? $comment->id : $comment) . ' 編輯表單',
        ]);
    }

    /**
     * 更新留言
     * PUT/PATCH /photos/{photo}/comments/{comment}
     */
    public function update(Request $request, $photo, $comment)
    {
        return response()->json([
            'message' => '更新 photo ' . (is_object($photo) ? $photo->id : $photo) . ' 的 comment ' . (is_object($comment) ? $comment->id : $comment),
        ]);
    }

    /**
     * 刪除留言
     * DELETE /photos/{photo}/comments/{comment}
     */
    public function destroy($photo, $comment)
    {
        return response()->json([
            'message' => '刪除 photo ' . (is_object($photo) ? $photo->id : $photo) . ' 的 comment ' . (is_object($comment) ? $comment->id : $comment),
        ]);
    }
} 