<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\WebProcessor;

class CreateCustomLogger
{
    /**
     * 建立自訂的 Monolog 實例
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('custom');
        
        // 添加檔案處理器
        $logger->pushHandler(new StreamHandler(
            storage_path('logs/custom.log'),
            Logger::DEBUG
        ));
        
        // 添加處理器
        $logger->pushProcessor(new MemoryUsageProcessor());
        $logger->pushProcessor(new WebProcessor());
        
        return $logger;
    }
} 