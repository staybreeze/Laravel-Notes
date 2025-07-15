<?php
namespace App\Jobs\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

// 自訂 Middleware（RateLimited）
// 用途：限制同一時間只能有一個任務執行（如每 5 秒只允許 1 個），避免資源過度消耗或 API 被濫用。
// 可複用：可套用於任何需要限流的 Job。

class RateLimited
{
    /**
     * Job Middleware 主體，限制同一 key 在指定秒數內只能執行一次
     * @param object $job 當前 Job 實例
     * @param \Closure $next 下一步 callback
     */
    public function handle(object $job, Closure $next): void
    {
        // 以 Redis throttle 實現限流：5 秒內只允許 1 個 job 執行
        Redis::throttle('key')->block(0)->allow(1)->every(5)
            ->then(function () use ($job, $next) {
                // 取得鎖，繼續執行 job
                $next($job);
            }, function () use ($job) {
                // 無法取得鎖，延遲 5 秒後重派
                $job->release(5);
            });
    }
} 