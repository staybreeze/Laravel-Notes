<?php
// 路徑：app/Http/Controllers/CacheDemoController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class CacheDemoController extends Controller
{
    // 寫入快取（60 秒）
    public function putCache()
    {
        Cache::put('demo_key', '這是快取內容', 60);
        return '已寫入快取';
    }

    // 讀取快取
    public function getCache()
    {
        $value = Cache::get('demo_key', '預設值');
        return '快取內容：' . $value;
    }

    // remember 快取查詢
    public function rememberUsers()
    {
        $users = Cache::remember('users.all', 600, function() {
            // 這裡會查詢所有用戶，並快取 10 分鐘
            return User::all();
        });
        return $users;
    }

    // 刪除快取
    public function forgetCache()
    {
        Cache::forget('demo_key');
        return '已刪除快取';
    }

    // tags 快取（僅支援 redis/memcached）
    public function tagCache()
    {
        Cache::tags(['groupA', 'groupB'])->put('tag_key', '標籤快取內容', 120);
        return '已寫入 tags 快取';
    }

    // 清除 tag 下所有快取
    public function flushTagCache()
    {
        Cache::tags('groupA')->flush();
        return '已清除 groupA 下所有快取';
    }
} 