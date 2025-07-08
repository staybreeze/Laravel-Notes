<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * ArticleController - 文章管理 RESTful API
 * 
 * 提供完整的 CRUD 操作：
 * - GET    /api/articles          -> index()   (取得所有文章)
 * - POST   /api/articles          -> store()   (建立新文章)
 * - GET    /api/articles/{id}     -> show()    (取得單一文章)
 * - PUT    /api/articles/{id}     -> update()  (更新文章)
 * - DELETE /api/articles/{id}     -> destroy() (刪除文章)
 */
class ArticleController extends Controller
{
    /**
     * 取得所有文章列表
     * GET /api/articles
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // 建立查詢建構器
            $query = Article::query();

            // 搜尋功能
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%");
                });
            }

            // 篩選已發布的文章
            if ($request->has('published')) {
                $published = $request->boolean('published');
                $query->where('is_published', $published);
            }

            // 排序
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 分頁
            $perPage = $request->get('per_page', 10);
            $articles = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => '文章列表取得成功',
                'data' => $articles->items(),
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '取得文章列表失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 建立新文章
     * POST /api/articles
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // 驗證輸入資料
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string|min:10',
                'author' => 'required|string|max:100',
                'is_published' => 'boolean'
            ]);

            // 建立文章
            $article = Article::create($validated);

            return response()->json([
                'success' => true,
                'message' => '文章建立成功',
                'data' => $article
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '驗證失敗',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '建立文章失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得單一文章
     * GET /api/articles/{id}
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $article = Article::findOrFail($id);
            
            // 增加瀏覽次數
            $article->incrementViewCount();

            return response()->json([
                'success' => true,
                'message' => '文章取得成功',
                'data' => $article
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '文章不存在'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '取得文章失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新文章
     * PUT /api/articles/{id}
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $article = Article::findOrFail($id);

            // 驗證輸入資料
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string|min:10',
                'author' => 'sometimes|required|string|max:100',
                'is_published' => 'sometimes|boolean'
            ]);

            // 更新文章
            $article->update($validated);

            return response()->json([
                'success' => true,
                'message' => '文章更新成功',
                'data' => $article
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '驗證失敗',
                'errors' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '文章不存在'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新文章失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 刪除文章
     * DELETE /api/articles/{id}
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $article = Article::findOrFail($id);
            $article->delete();

            return response()->json([
                'success' => true,
                'message' => '文章刪除成功'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '文章不存在'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '刪除文章失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 發布文章
     * PATCH /api/articles/{id}/publish
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function publish(string $id): JsonResponse
    {
        try {
            $article = Article::findOrFail($id);
            $article->publish();

            return response()->json([
                'success' => true,
                'message' => '文章發布成功',
                'data' => $article
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '文章不存在'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '發布文章失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取消發布文章
     * PATCH /api/articles/{id}/unpublish
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function unpublish(string $id): JsonResponse
    {
        try {
            $article = Article::findOrFail($id);
            $article->unpublish();

            return response()->json([
                'success' => true,
                'message' => '文章取消發布成功',
                'data' => $article
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '文章不存在'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '取消發布文章失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
