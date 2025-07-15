<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\ProcessPodcast;
use App\Jobs\ImportCsv;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Bus;

// 測試（PodcastJobTest）
// 用途：用來驗證 queue 功能是否正確運作，包含：
// 任務是否正確被派送
// 任務鏈、批次是否正確建立
// 測試 queue 不會真的執行任務，僅驗證派送行為

class PodcastJobTest extends TestCase
{
    /** @test */
    public function it_dispatches_process_podcast_job()
    {
        Queue::fake(); // 攔截所有 queue，不會真的執行
        ProcessPodcast::dispatch(1); // 派送任務
        Queue::assertPushed(ProcessPodcast::class); // 斷言有派送該任務
        Queue::assertPushedOn('default', ProcessPodcast::class); // 斷言派送到 default queue
    }

    /** @test */
    public function it_dispatches_job_chain()
    {
        Bus::fake(); // 攔截所有 job chain
        Bus::chain([
            new ProcessPodcast(1),
            new ProcessPodcast(2),
        ])->dispatch(); // 派送任務鏈
        Bus::assertChained([
            ProcessPodcast::class,
            ProcessPodcast::class,
        ]); // 斷言鏈中包含這兩個任務
    }

    /** @test */
    public function it_dispatches_job_batch()
    {
        Bus::fake(); // 攔截所有 batch
        Bus::batch([
            new ImportCsv(1, 100),
            new ImportCsv(101, 200),
        ])->name('CSV 匯入')->dispatch(); // 派送批次任務
        Bus::assertBatched(function ($batch) {
            // 斷言批次名稱與任務數量正確
            return $batch->name === 'CSV 匯入' && $batch->jobs->count() === 2;
        });
    }
} 