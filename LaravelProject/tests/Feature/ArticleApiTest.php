<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * 測試取得所有文章列表
     */
    public function test_can_get_all_articles(): void
    {
        // 建立測試資料
        Article::factory(3)->create();

        // 發送 API 請求
        $response = $this->getJson('/api/articles');

        // 驗證回應
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'content',
                            'author',
                            'is_published',
                            'view_count'
                        ]
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => '文章列表取得成功'
                ]);
    }

    /**
     * 測試搜尋文章功能
     */
    public function test_can_search_articles(): void
    {
        // 建立包含特定關鍵字的文章
        Article::factory()->create(['title' => 'Laravel 測試文章']);
        Article::factory()->create(['title' => '其他文章']);

        // 發送搜尋請求
        $response = $this->getJson('/api/articles?search=Laravel');

        // 驗證回應
        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.title', 'Laravel 測試文章');
    }

    /**
     * 測試篩選已發布文章
     */
    public function test_can_filter_published_articles(): void
    {
        // 建立已發布和未發布的文章
        Article::factory()->create(['is_published' => true]);
        Article::factory()->create(['is_published' => false]);

        // 發送篩選請求
        $response = $this->getJson('/api/articles?published=true');

        // 驗證回應
        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.is_published', true);
    }

    /**
     * 測試建立新文章
     */
    public function test_can_create_article(): void
    {
        $articleData = [
            'title' => '測試文章標題',
            'content' => '這是一篇測試文章的內容，內容長度超過十個字。',
            'author' => '測試作者',
            'is_published' => true
        ];

        // 發送建立請求
        $response = $this->postJson('/api/articles', $articleData);

        // 驗證回應
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'content',
                        'author',
                        'is_published',
                        'view_count'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => '文章建立成功',
                    'data' => [
                        'title' => '測試文章標題',
                        'content' => '這是一篇測試文章的內容，內容長度超過十個字。',
                        'author' => '測試作者',
                        'is_published' => true
                    ]
                ]);

        // 驗證資料庫中確實建立了文章
        $this->assertDatabaseHas('articles', [
            'title' => '測試文章標題',
            'author' => '測試作者'
        ]);
    }

    /**
     * 測試建立文章時的驗證錯誤
     */
    public function test_cannot_create_article_with_invalid_data(): void
    {
        $invalidData = [
            'title' => '', // 空標題
            'content' => '太短', // 內容太短
            'author' => '' // 空作者
        ];

        // 發送建立請求
        $response = $this->postJson('/api/articles', $invalidData);

        // 驗證回應
        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors' => [
                        'title',
                        'content',
                        'author'
                    ]
                ])
                ->assertJson([
                    'success' => false,
                    'message' => '驗證失敗'
                ]);
    }

    /**
     * 測試取得單一文章
     */
    public function test_can_get_single_article(): void
    {
        // 建立測試文章
        $article = Article::factory()->create();

        // 發送請求
        $response = $this->getJson("/api/articles/{$article->id}");

        // 驗證回應
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'content',
                        'author',
                        'is_published',
                        'view_count'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => '文章取得成功',
                    'data' => [
                        'id' => $article->id,
                        'title' => $article->title
                    ]
                ]);

        // 驗證瀏覽次數有增加
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'view_count' => $article->view_count + 1
        ]);
    }

    /**
     * 測試取得不存在的文章
     */
    public function test_cannot_get_nonexistent_article(): void
    {
        // 發送請求到不存在的 ID
        $response = $this->getJson('/api/articles/999');

        // 驗證回應
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => '文章不存在'
                ]);
    }

    /**
     * 測試更新文章
     */
    public function test_can_update_article(): void
    {
        // 建立測試文章
        $article = Article::factory()->create();

        $updateData = [
            'title' => '更新後的標題',
            'content' => '更新後的內容，內容長度超過十個字。'
        ];

        // 發送更新請求
        $response = $this->putJson("/api/articles/{$article->id}", $updateData);

        // 驗證回應
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => '文章更新成功',
                    'data' => [
                        'id' => $article->id,
                        'title' => '更新後的標題',
                        'content' => '更新後的內容，內容長度超過十個字。'
                    ]
                ]);

        // 驗證資料庫確實更新了
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => '更新後的標題'
        ]);
    }

    /**
     * 測試刪除文章
     */
    public function test_can_delete_article(): void
    {
        // 建立測試文章
        $article = Article::factory()->create();

        // 發送刪除請求
        $response = $this->deleteJson("/api/articles/{$article->id}");

        // 驗證回應
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => '文章刪除成功'
                ]);

        // 驗證資料庫中確實刪除了
        $this->assertDatabaseMissing('articles', [
            'id' => $article->id
        ]);
    }

    /**
     * 測試發布文章
     */
    public function test_can_publish_article(): void
    {
        // 建立未發布的文章
        $article = Article::factory()->create(['is_published' => false]);

        // 發送發布請求
        $response = $this->patchJson("/api/articles/{$article->id}/publish");

        // 驗證回應
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => '文章發布成功',
                    'data' => [
                        'id' => $article->id,
                        'is_published' => true
                    ]
                ]);

        // 驗證資料庫確實更新了
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'is_published' => true
        ]);
    }

    /**
     * 測試取消發布文章
     */
    public function test_can_unpublish_article(): void
    {
        // 建立已發布的文章
        $article = Article::factory()->create(['is_published' => true]);

        // 發送取消發布請求
        $response = $this->patchJson("/api/articles/{$article->id}/unpublish");

        // 驗證回應
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => '文章取消發布成功',
                    'data' => [
                        'id' => $article->id,
                        'is_published' => false
                    ]
                ]);

        // 驗證資料庫確實更新了
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'is_published' => false
        ]);
    }
}
