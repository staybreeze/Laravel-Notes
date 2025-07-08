<?php
// 路徑：app/Http/Controllers/CacheUsageDemoController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheUsageDemoController extends Controller
{
    // 取得不同快取 store
    public function storeDemo()
    {
        $fileValue = Cache::store('file')->get('foo');
        Cache::store('redis')->put('bar', 'baz', 600);
        return ['file' => $fileValue, 'redis' => Cache::store('redis')->get('bar')];
    }

    // 取得快取（含預設值、closure）
    public function getDemo()
    {
        $v1 = Cache::get('key');
        $v2 = Cache::get('key', 'default');
        $v3 = Cache::get('key', function () {
            return DB::table('users')->get();
        });
        return compact('v1', 'v2', 'v3');
    }

    // 判斷快取是否存在
    public function hasDemo()
    {
        return Cache::has('key') ? '存在' : '不存在';
    }

    // increment/decrement/add
    public function incDecDemo()
    {
        Cache::add('num', 0, now()->addHours(4));
        Cache::increment('num');
        Cache::increment('num', 5);
        Cache::decrement('num');
        Cache::decrement('num', 2);
        return Cache::get('num');
    }

    // remember/rememberForever
    public function rememberDemo()
    {
        $users = Cache::remember('users', 600, function () {
            return DB::table('users')->get();
        });
        $forever = Cache::rememberForever('users_forever', function () {
            return DB::table('users')->get();
        });
        return ['users' => $users, 'forever' => $forever];
    }

    // flexible 彈性快取
    public function flexibleDemo()
    {
        $users = Cache::flexible('users_flexible', [5, 10], function () {
            return DB::table('users')->get();
        });
        return $users;
    }

    // pull 取得並刪除
    public function pullDemo()
    {
        $v1 = Cache::pull('key');
        $v2 = Cache::pull('key', 'default');
        return compact('v1', 'v2');
    }

    // put/add/forever
    public function putAddForeverDemo()
    {
        Cache::put('key', 'value', 10);
        Cache::put('key', 'value', now()->addMinutes(10));
        $added = Cache::add('key2', 'value2', 60);
        Cache::forever('key3', 'value3');
        return ['key' => Cache::get('key'), 'key2' => Cache::get('key2'), 'key3' => Cache::get('key3'), 'added' => $added];
    }

    // forget/flush
    public function forgetFlushDemo()
    {
        Cache::forget('key');
        Cache::put('key', 'value', 0);
        Cache::flush();
        return '已刪除/清空快取';
    }

    // memo driver
    public function memoDemo()
    {
        $v1 = Cache::memo()->get('key'); // 第一次會查快取
        $v2 = Cache::memo()->get('key'); // 第二次直接記憶體
        Cache::memo()->put('name', 'Taylor');
        $v3 = Cache::memo()->get('name');
        Cache::memo()->put('name', 'Tim');
        $v4 = Cache::memo()->get('name');
        return compact('v1', 'v2', 'v3', 'v4');
    }

    // cache 輔助函式
    public function helperDemo()
    {
        $v1 = cache('key');
        cache(['key' => 'value'], 60);
        cache(['key2' => 'value2'], now()->addMinutes(10));
        $v2 = cache()->remember('users', 600, function () {
            return DB::table('users')->get();
        });
        return compact('v1', 'v2');
    }
} 