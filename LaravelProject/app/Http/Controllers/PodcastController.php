<?php
namespace App\Http\Controllers;

use App\Jobs\ProcessPodcast;
use App\Jobs\ImportCsv;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Throwable;
// Podcast 處理任務（ProcessPodcast Job）
// 用途：當有新 Podcast 上傳時，將「處理 Podcast」這個耗時任務（如轉檔、分析、通知等）丟到背景 queue 執行，不會卡住使用者的網頁請求。
// 特點：
// 可設定最大重試次數、失敗重試間隔
// 若 Podcast 資料已被刪除，任務自動丟棄
// 支援限流（RateLimited）與防止同一 Podcast 重複處理（WithoutOverlapping）
// 失敗時可自動通知或記錄

// PodcastController
// 用途：提供多種 queue 派送方式的 API 端點（或範例），包含：
// 單一任務派送
// 延遲派送
// 指定 queue/connection
// 任務鏈（Job Chaining）
// 批次任務（Job Batching）

// 這樣的功能適合什麼場景？
// 任何需要背景處理的耗時任務，如：檔案處理、影音轉檔、批次匯入、通知推送、API 呼叫等
// 高併發、需分流/限流/防重複的任務
// 需要追蹤進度、可取消、可重試的批次作業
// 希望系統穩定、可測試、易於維護的專案

// 實際應用舉例
// 使用者上傳 Podcast → 立即回應「上傳成功」→ 背景自動處理 Podcast（轉檔、分析、通知）
// 管理員上傳大量會員資料（CSV）→ 分段批次匯入 → 匯入進度可查詢、可取消
// 任何需防止同一資源被多次同時處理的情境
class PodcastController extends Controller
{
    /**
     * 派送單一 Job，將 Podcast 處理任務丟到 queue
     */
    public function dispatchSingle(Request $request): RedirectResponse
    {
        $podcastId = 1;
        // 立即派送到預設 queue
        ProcessPodcast::dispatch($podcastId);
        return redirect('/podcasts');
    }

    /**
     * 延遲派送，10 分鐘後才執行
     */
    public function dispatchDelayed(Request $request): RedirectResponse
    {
        $podcastId = 2;
        // 延遲 10 分鐘後派送
        ProcessPodcast::dispatch($podcastId)->delay(now()->addMinutes(10));
        return redirect('/podcasts');
    }

    /**
     * 指定 queue/connection 派送
     */
    public function dispatchToQueue(Request $request): RedirectResponse
    {
        $podcastId = 3;
        // 派送到 redis 連線的 processing queue
        ProcessPodcast::dispatch($podcastId)->onQueue('processing')->onConnection('redis');
        return redirect('/podcasts');
    }

    /**
     * Job Chaining：多個任務串接，前一個成功才執行下一個
     */
    public function dispatchChain(Request $request): RedirectResponse
    {
        Bus::chain([
            new ProcessPodcast(4),
            function () {
                // 這裡可放 closure 任務，例如寄信通知
            },
        ])->onQueue('chain')->dispatch();
        return redirect('/podcasts');
    }

    /**
     * Job Batching：批次大量任務，並可追蹤進度與失敗
     */
    public function dispatchBatch(Request $request): RedirectResponse
    {
        $batch = Bus::batch([
            new ImportCsv(1, 100),
            new ImportCsv(101, 200),
        ])->name('CSV 匯入')->then(function (Batch $batch) {
            // 全部成功時執行，例如通知管理員
        })->catch(function (Batch $batch, Throwable $e) {
            // 有任務失敗時執行，例如記錄錯誤
        })->dispatch();
        return redirect('/podcasts');
    }
} 