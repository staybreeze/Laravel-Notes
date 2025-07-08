<?php
// 路徑：app/Http/Controllers/CacheLockDemoController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;

class CacheLockDemoController extends Controller
{
    // 取得鎖並釋放
    public function getLock()
    {
        $lock = Cache::lock('demo_lock', 10);
        if ($lock->get()) {
            // 取得鎖後執行
            $result = '取得鎖成功';
            $lock->release();
        } else {
            $result = '鎖已被其他程序取得';
        }
        return $result;
    }

    // 取得鎖（closure 自動釋放）
    public function getLockWithClosure()
    {
        Cache::lock('demo_lock', 10)->get(function () {
            // 取得鎖後自動釋放
            sleep(2); // 模擬執行
        });
        return '已自動釋放鎖';
    }

    // block 等待鎖
    public function blockLock()
    {
        $lock = Cache::lock('demo_lock', 10);
        try {
            $lock->block(5); // 最多等 5 秒
            $result = 'block 取得鎖成功';
        } catch (LockTimeoutException $e) {
            $result = 'block 超時，無法取得鎖';
        } finally {
            $lock->release();
        }
        return $result;
    }

    // block + closure
    public function blockLockWithClosure()
    {
        Cache::lock('demo_lock', 10)->block(5, function () {
            sleep(2);
        });
        return 'block+closure 已自動釋放鎖';
    }

    // owner token 跨程序釋放
    public function getLockWithOwner()
    {
        $lock = Cache::lock('demo_lock_owner', 30);
        if ($lock->get()) {
            $token = $lock->owner();
            // 模擬將 token 傳給其他程序
            return '取得鎖，token: ' . $token;
        }
        return '鎖已被其他程序取得';
    }

    // 使用 owner token 釋放鎖
    public function releaseLockWithOwner(Request $request)
    {
        $token = $request->input('token');
        if ($token) {
            Cache::restoreLock('demo_lock_owner', $token)->release();
            return '已用 owner token 釋放鎖';
        }
        return '請提供 token';
    }

    // 強制釋放鎖
    public function forceReleaseLock()
    {
        Cache::lock('demo_lock_owner')->forceRelease();
        return '已強制釋放鎖';
    }
} 