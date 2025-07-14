<?php
namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HttpClientTest extends TestCase
{
    /**
     * 測試 GET 請求的假回應
     */
    public function test_get_post_fake()
    {
        // 假資料：指定網址回傳固定 JSON
        Http::fake([
            'jsonplaceholder.typicode.com/posts/1' => Http::response(['title' => '測試標題'], 200),
        ]);

        // 發送 GET 請求
        $response = Http::get('https://jsonplaceholder.typicode.com/posts/1');
        // 驗證回應內容
        $this->assertEquals('測試標題', $response['title']);
    }

    /**
     * 測試 POST 請求的假回應
     */
    public function test_post_user_fake()
    {
        // 假資料：指定網址回傳固定 JSON
        Http::fake([
            'jsonplaceholder.typicode.com/users' => Http::response(['id' => 123, 'name' => 'Vincent'], 201),
        ]);

        // 發送 POST 請求
        $response = Http::post('https://jsonplaceholder.typicode.com/users', [
            'name' => 'Vincent',
            'email' => 'vincent@example.com',
        ]);
        // 驗證回應內容
        $this->assertEquals(123, $response['id']);
    }

    /**
     * 驗證是否有發送指定內容的請求
     */
    public function test_assert_sent()
    {
        // 啟用假資料
        Http::fake();

        // 發送 POST 請求
        Http::post('https://jsonplaceholder.typicode.com/users', [
            'name' => 'Vincent',
        ]);

        // 驗證是否有發送正確內容的請求
        Http::assertSent(function ($request) {
            return $request->url() === 'https://jsonplaceholder.typicode.com/users'
                && $request['name'] === 'Vincent';
        });
    }
} 