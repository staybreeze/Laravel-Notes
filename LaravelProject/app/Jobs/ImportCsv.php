<?php
namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;

// 批次匯入任務（ImportCsv Job）
// 用途：當有大量 CSV 檔案需要分段匯入時，將每一段匯入任務丟到 queue，並用 batch 管理整體進度與失敗狀態。
// 特點：
// 支援 Laravel 批次（Batch）功能，可追蹤進度、取消整批任務
// 若批次被取消，任務自動略過
// 適合大量資料分段處理

class ImportCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * 匯入的起始與結束行數（分段）
     * @var int
     */
    public $start;
    public $end;

    /**
     * 建構子，注入分段起訖
     * @param int $start
     * @param int $end
     */
    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * 任務執行主體，這裡撰寫實際 CSV 匯入邏輯
     * 若 batch 被取消則自動略過
     */
    public function handle()
    {
        if ($this->batch()?->cancelled()) return; // 若批次被取消則不執行
        // 這裡撰寫實際 CSV 匯入邏輯
    }

    /**
     * 指定 Job Middleware
     * - SkipIfBatchCancelled: 若 batch 被取消則自動略過此 job
     * @return array
     */
    public function middleware(): array
    {
        return [new SkipIfBatchCancelled];
    }
} 