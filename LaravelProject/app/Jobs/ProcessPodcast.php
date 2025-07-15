<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

class ProcessPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 要處理的 Podcast ID
     * @var int
     */
    public $podcastId;

    /**
     * 最大嘗試次數，超過會進入 failed_jobs
     * @var int
     */
    public $tries = 3;

    /**
     * 失敗重試間隔（秒）
     * @var int
     */
    public $backoff = 5;

    /**
     * 若 model 不存在自動丟棄 job
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * 建構子，注入 podcast id
     * @param int $podcastId
     */
    public function __construct($podcastId)
    {
        $this->podcastId = $podcastId;
    }

    /**
     * 任務執行主體，這裡撰寫實際 Podcast 處理邏輯
     * 例如：轉檔、分析、通知等
     */
    public function handle()
    {
        // 這裡撰寫實際 Podcast 處理邏輯
        // 例如：Podcast::find($this->podcastId)->process();
    }

    /**
     * 任務失敗時自動呼叫
     * 可用於通知用戶、記錄錯誤、回滾操作等
     * @param Throwable|null $exception
     */
    public function failed(?Throwable $exception)
    {
        // 這裡可通知用戶、記錄錯誤等
    }

    /**
     * 指定 Job Middleware
     * - RateLimited: 限制同一時間只能有一個任務執行（防止資源過度消耗）
     * - WithoutOverlapping: 防止同一 podcast 被多個 job 同時處理
     * @return array
     */
    public function middleware(): array
    {
        return [
            new RateLimited('podcast-process'), // 限流
            new WithoutOverlapping($this->podcastId), // 同一 podcast 不重疊
        ];
    }
} 