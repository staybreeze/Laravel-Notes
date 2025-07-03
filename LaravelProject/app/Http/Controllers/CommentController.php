<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Photo;
use App\Models\Comment;

/**
 * CommentController
 *
 * 對應 Route::resource('photos.comments', CommentController::class)->shallow();
 * 會自動產生「淺層巢狀」RESTful 路由：
 * - index/create/store 會有 photo id
 * - show/edit/update/destroy 只會有 comment id
 *
 * 產生的路由：
 * GET    /photos/{photo}/comments           -> index
 * GET    /photos/{photo}/comments/create    -> create
 * POST   /photos/{photo}/comments           -> store
 * GET    /comments/{comment}                -> show
 * GET    /comments/{comment}/edit           -> edit
 * PUT    /comments/{comment}                -> update
 * DELETE /comments/{comment}                -> destroy
 */
class CommentController extends Controller
{
    /**
     * 顯示某張照片的所有留言
     * GET /photos/{photo}/comments
     * @param int|Photo $photo
     */
    public function index($photo)
    {
        return response()->json([
            'message' => '顯示 photo ' . (is_object($photo) ? $photo->id : $photo) . ' 的所有留言',
        ]);
    }

    /**
     * 顯示新增留言表單
     * GET /photos/{photo}/comments/create
     * @param int|Photo $photo
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
     * @param Request $request
     * @param int|Photo $photo
     */
    public function store(Request $request, $photo)
    {
        return response()->json([
            'message' => '為 photo ' . (is_object($photo) ? $photo->id : $photo) . ' 新增留言',
        ]);
    }

    /**
     * 顯示單一留言
     * GET /comments/{comment}
     * @param int|Comment $comment
     */
    public function show($comment)
    {
        return response()->json([
            'message' => '顯示 comment ' . (is_object($comment) ? $comment->id : $comment),
        ]);
    }

    /**
     * 顯示編輯留言表單
     * GET /comments/{comment}/edit
     * @param int|Comment $comment
     */
    public function edit($comment)
    {
        return response()->json([
            'message' => '顯示 comment ' . (is_object($comment) ? $comment->id : $comment) . ' 編輯表單',
        ]);
    }

    /**
     * 更新留言
     * PUT/PATCH /comments/{comment}
     * @param Request $request
     * @param int|Comment $comment
     */
    public function update(Request $request, $comment)
    {
        return response()->json([
            'message' => '更新 comment ' . (is_object($comment) ? $comment->id : $comment),
        ]);
    }

    /**
     * 刪除留言
     * DELETE /comments/{comment}
     * @param int|Comment $comment
     */
    public function destroy($comment)
    {
        return response()->json([
            'message' => '刪除 comment ' . (is_object($comment) ? $comment->id : $comment),
        ]);
    }
} 