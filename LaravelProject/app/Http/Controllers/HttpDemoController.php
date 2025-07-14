<?php
namespace App\Http\Controllers;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controller;

class HttpDemoController extends Controller
{
    /**
     * 發送 GET 請求並回傳 JSON 結果
     */
    public function getPost()
    {
        // 發送 GET 請求到範例 API
        $response = Http::get('https://jsonplaceholder.typicode.com/posts/1');
        // 回傳 JSON 結果
        return $response->json();
    }

    /**
     * 發送 POST 請求，送出使用者資料
     */
    public function createUser()
    {
        // 發送 POST 請求到範例 API，帶入 name 與 email
        $response = Http::post('https://jsonplaceholder.typicode.com/users', [
            'name' => 'Vincent',
            'email' => 'vincent@example.com',
        ]);
        // 回傳 JSON 結果
        return $response->json();
    }

    /**
     * 並列發送多個 GET 請求
     */
    public function multiRequest()
    {
        // 使用 pool 同時發送多個 GET 請求
        $responses = Http::pool(fn (Pool $pool) => [
            $pool->get('https://jsonplaceholder.typicode.com/posts/1'),
            $pool->get('https://jsonplaceholder.typicode.com/posts/2'),
        ]);
        // 回傳多個回應的 JSON 結果
        return [
            'first' => $responses[0]->json(),
            'second' => $responses[1]->json(),
        ];
    }
} 