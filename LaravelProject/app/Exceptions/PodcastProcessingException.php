<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;

class PodcastProcessingException extends Exception implements ShouldntReport
{
    protected $podcastId;
    protected $processingStep;

    public function __construct($message = "", $podcastId = null, $processingStep = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->podcastId = $podcastId;
        $this->processingStep = $processingStep;
    }

    /**
     * 取得播客 ID
     */
    public function getPodcastId()
    {
        return $this->podcastId;
    }

    /**
     * 取得處理步驟
     */
    public function getProcessingStep()
    {
        return $this->processingStep;
    }

    /**
     * 這個例外不會被記錄（因為實作了 ShouldntReport）
     * 但我們可以自訂渲染邏輯
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Podcast Processing Failed',
                'message' => '播客處理暫時失敗，請稍後再試',
                'podcast_id' => $this->podcastId,
            ], 503);
        }

        return response()->view('errors.podcast_processing', [
            'podcastId' => $this->podcastId,
            'processingStep' => $this->processingStep,
            'message' => $this->getMessage(),
        ], 503);
    }
} 