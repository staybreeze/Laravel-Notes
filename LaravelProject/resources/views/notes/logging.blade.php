<?php

{{-- # Laravel Logging 日誌記錄完整教學 --}}

{{--
    ## 目錄
    1. Logging 介紹與基本概念
    2. 配置檔案與設定
    3. 可用的 Channel Drivers
    4. Channel 配置選項
    5. 環境變數配置
    6. 實際使用範例
    7. 最佳實踐與建議
--}}

{{-- ========================= --}}
{{-- # 1. Logging 介紹與基本概念 --}}
{{-- ========================= --}}

{{--
    ## 什麼是 Laravel Logging？
    
    Laravel 提供了強大的日誌記錄服務，可以將訊息記錄到檔案、系統錯誤日誌，
    甚至發送到 Slack 來通知整個團隊。這就像「應用程式的日記本」，記錄所有重要事件。

    ## 核心概念

    ### Channels（通道）
    - 每個 channel 代表一種特定的日誌記錄方式
    - 例如：single channel 寫入單一檔案，slack channel 發送到 Slack
    - 可以根據嚴重程度寫入多個 channels

    ### Monolog 庫
    - Laravel 底層使用 Monolog 庫
    - 提供多種強大的日誌處理器
    - 可以混合搭配來客製化日誌處理

    ## 生活化比喻
    
    ### Logging 就像「醫院病歷系統」
    - **Channels**：就像不同的記錄方式（電子病歷、紙本病歷、簡訊通知）
    - **Monolog**：就像病歷系統的核心引擎，支援多種記錄格式
    - **Severity**：就像病情嚴重程度（輕微、一般、嚴重、危急）
    - **Stack**：就像綜合病歷，整合多種記錄方式

    ### Logging 就像「餐廳營運記錄」
    - **Channels**：不同的記錄方式（點餐系統、廚房記錄、財務報表）
    - **Daily Rotation**：就像每日結帳，舊記錄歸檔
    - **Slack Integration**：就像即時通知廚師有新訂單
    - **Error Logging**：記錄客訴和問題，用於改進服務
--}}

{{-- ========================= --}}
{{-- # 2. 配置檔案與設定 --}}
{{-- ========================= --}}

{{--
    ## 配置檔案位置
    
    ### config/logging.php
    所有日誌記錄的配置選項都在這個檔案中，包括：
    - 應用程式的日誌 channels
    - 每個 channel 的配置選項
    - 預設的 stack channel 設定

    ## 預設配置
    
    ### Stack Channel
    Laravel 預設使用 stack channel，它會聚合多個日誌 channels 成單一 channel：

    ```php
    // config/logging.php
    'default' => env('LOG_CHANNEL', 'stack'),

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
    ],
    ```

    ## 基本配置結構
    
    ```php
    // config/logging.php 基本結構
    return [
        'default' => env('LOG_CHANNEL', 'stack'),
        
        'deprecations' => [
            'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
            'trace' => env('LOG_DEPRECATIONS_TRACE', false),
        ],
        
        'channels' => [
            // 各種 channels 配置
        ],
    ];
    ```

    ## 生活化比喻
    
    ### 配置就像「餐廳菜單設計」
    - **default**：就像預設的套餐選擇
    - **channels**：就像不同的服務項目（內用、外帶、外送）
    - **stack**：就像綜合套餐，包含多種服務
    - **deprecations**：就像過期食材的處理方式
--}}

{{-- ========================= --}}
{{-- # 3. 可用的 Channel Drivers --}}
{{-- ========================= --}}

{{--
    ## Channel Drivers 總覽
    
    每個 log channel 都由一個 "driver" 驅動，決定如何和在哪裡記錄日誌訊息。
    以下是 Laravel 應用程式中可用的所有 log channel drivers：

    ### 1. single - 單一檔案記錄
    - **用途**：將所有日誌寫入單一檔案
    - **適用場景**：小型應用程式、開發環境
    - **配置範例**：
    ```php
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
    ```

    ### 2. daily - 每日輪換檔案
    - **用途**：每天建立新的日誌檔案，自動輪換
    - **適用場景**：生產環境、需要歷史記錄
    - **配置範例**：
    ```php
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14, // 保留 14 天
    ],
    ```

    ### 3. slack - Slack 通知
    - **用途**：將日誌訊息發送到 Slack
    - **適用場景**：即時警報、團隊通知
    - **配置範例**：
    ```php
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => env('LOG_LEVEL', 'critical'),
    ],
    ```

    ### 4. stack - 多通道聚合
    - **用途**：將多個 channels 聚合為單一 channel
    - **適用場景**：同時使用多種記錄方式
    - **配置範例**：
    ```php
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
        'ignore_exceptions' => false,
    ],
    ```

    ## 深入理解 Log Stacks

    ### 什麼是 Log Stack？
    Log Stack 允許您將多個 channels 組合成單一的日誌通道，提供便利性。
    這就像「綜合套餐」，一次記錄可以同時發送到多個目的地。

    ### 生產環境配置範例
    ```php
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['syslog', 'slack'], 
            'ignore_exceptions' => false,
        ],
     
        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],
     
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],
    ],
    ```

    ### Stack 配置解析
    1. **channels 選項**：指定要聚合的 channels（syslog 和 slack）
    2. **ignore_exceptions**：是否忽略個別 channel 的例外
    3. **level 過濾**：每個 channel 可以設定不同的最低等級

    ### Log Levels 等級系統
    Monolog 提供 RFC 5424 規範定義的所有日誌等級，按嚴重程度從高到低：

    | 等級 | 嚴重程度 | 說明 |
    |------|----------|------|
    | emergency | 最高 | 系統完全無法運作 |
    | alert | 很高 | 需要立即行動 |
    | critical | 高 | 嚴重錯誤 |
    | error | 中高 | 一般錯誤 |
    | warning | 中 | 警告訊息 |
    | notice | 中低 | 注意事項 |
    | info | 低 | 一般資訊 |
    | debug | 最低 | 除錯資訊 |

    ### 實際運作範例

    #### 範例 1：Debug 等級訊息
    ```php
    Log::debug('An informational message.');
    ```
    **結果**：
    - ✅ syslog：會記錄（level: debug）
    - ❌ slack：不會發送（level: critical，debug < critical）

    #### 範例 2：Emergency 等級訊息
    ```php
    Log::emergency('The system is down!');
    ```
    **結果**：
    - ✅ syslog：會記錄（level: debug，emergency > debug）
    - ✅ slack：會發送（level: critical，emergency > critical）

    #### 範例 3：Error 等級訊息
    ```php
    Log::error('Database connection failed');
    ```
    **結果**：
    - ✅ syslog：會記錄（level: debug，error > debug）
    - ❌ slack：不會發送（level: critical，error < critical）

    ### 進階 Stack 配置

    #### 1. 條件性 Stack
    ```php
    'stack' => [
        'driver' => 'stack',
        'channels' => [
            'single',           // 所有訊息都記錄到檔案
            'slack_critical',   // 只有 critical 以上發送到 Slack
            'email_emergency',  // 只有 emergency 發送郵件
        ],
        'ignore_exceptions' => false,
    ],
    ```

    #### 2. 環境特定 Stack
    ```php
    // 開發環境
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'daily'],
        'ignore_exceptions' => false,
    ],

    // 生產環境
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack', 'papertrail'],
        'ignore_exceptions' => false,
    ],
    ```

    #### 3. 業務邏輯 Stack
    ```php
    'stack' => [
        'driver' => 'stack',
        'channels' => [
            'general',      // 一般日誌
            'orders',       // 訂單相關
            'payments',     // 付款相關
            'security',     // 安全相關
        ],
        'ignore_exceptions' => false,
    ],
    ```

    ### ignore_exceptions 選項

    #### 設為 false（預設）
    ```php
    'ignore_exceptions' => false,
    ```
    - 如果某個 channel 發生例外，會影響其他 channels
    - 適合需要確保所有 channels 都正常運作的情況

    #### 設為 true
    ```php
    'ignore_exceptions' => true,
    ```
    - 如果某個 channel 發生例外，不會影響其他 channels
    - 適合需要容錯的情況

    ### 實際應用場景

    #### 1. 開發環境 Stack
    ```php
    'dev_stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'daily'],
        'ignore_exceptions' => false,
    ],
    ```
    - 記錄到單一檔案和每日檔案
    - 方便開發者查看和除錯

    #### 2. 生產環境 Stack
    ```php
    'prod_stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack', 'papertrail'],
        'ignore_exceptions' => true,
    ],
    ```
    - 每日檔案輪換
    - 重要錯誤即時通知 Slack
    - 集中化日誌管理 Papertrail

    #### 3. 監控 Stack
    ```php
    'monitor_stack' => [
        'driver' => 'stack',
        'channels' => ['syslog', 'slack', 'email'],
        'ignore_exceptions' => false,
    ],
    ```
    - 系統級別記錄
    - 即時團隊通知
    - 管理員郵件警報

    ### 效能考量

    #### 1. Channel 數量
    ```php
    // 好的做法：適量的 channels
    'channels' => ['daily', 'slack', 'papertrail'],

    // 避免：過多的 channels
    'channels' => ['single', 'daily', 'slack', 'papertrail', 'email', 'sms', 'webhook'],
    ```

    #### 2. 等級過濾
    ```php
    // 好的做法：適當的等級過濾
    'slack' => [
        'driver' => 'slack',
        'level' => 'critical', // 只發送重要訊息
    ],

    // 避免：所有等級都發送
    'slack' => [
        'driver' => 'slack',
        'level' => 'debug', // 會發送太多訊息
    ],
    ```

    ### 除錯技巧

    #### 1. 測試 Stack 配置
    ```php
    // 測試不同等級的訊息
    Log::debug('Debug message');
    Log::info('Info message');
    Log::warning('Warning message');
    Log::error('Error message');
    Log::critical('Critical message');
    Log::emergency('Emergency message');
    ```

    #### 2. 檢查 Channel 狀態
    ```php
    // 檢查特定 channel 是否正常
    try {
        Log::channel('slack')->info('Test message');
        echo 'Slack channel is working';
    } catch (Exception $e) {
        echo 'Slack channel error: ' . $e->getMessage();
    }
    ```

    #### 3. 監控 Stack 效能
    ```php
    $startTime = microtime(true);
    Log::info('Performance test message');
    $endTime = microtime(true);
    
    $executionTime = ($endTime - $startTime) * 1000;
    echo "Log execution time: {$executionTime} ms";
    ```

    ## 生活化比喻
    
    ### Log Stack 就像「綜合快遞服務」
    - **channels**：就像不同的配送方式（空運、海運、陸運）
    - **level**：就像包裹的優先級（普通、急件、特急）
    - **ignore_exceptions**：就像是否容許部分配送失敗
    - **stack**：就像「一單多送」服務，一次寄送多個目的地

    ### Log Stack 就像「餐廳訂位系統」
    - **channels**：就像不同的通知方式（簡訊、電話、Email）
    - **level**：就像訂位的重要程度（一般、VIP、緊急）
    - **stack**：就像「多重確認」系統，確保客人收到通知

    ### 5. papertrail - Papertrail 服務
    - **用途**：發送到 Papertrail 日誌管理服務
    - **適用場景**：集中化日誌管理
    - **配置範例**：
    ```php
    'papertrail' => [
        'driver' => 'monolog',
        'level' => env('LOG_LEVEL', 'debug'),
        'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
        'handler_with' => [
            'host' => env('PAPERTRAIL_URL'),
            'port' => env('PAPERTRAIL_PORT'),
            'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
        ],
    ],
    ```

    ### 6. syslog - 系統日誌
    - **用途**：寫入系統日誌
    - **適用場景**：伺服器級別的日誌記錄
    - **配置範例**：
    ```php
    'syslog' => [
        'driver' => 'syslog',
        'level' => env('LOG_LEVEL', 'debug'),
    ],
    ```

    ### 7. errorlog - 錯誤日誌
    - **用途**：寫入 PHP 錯誤日誌
    - **適用場景**：與其他 PHP 應用程式共用日誌
    - **配置範例**：
    ```php
    'errorlog' => [
        'driver' => 'errorlog',
        'level' => env('LOG_LEVEL', 'debug'),
    ],
    ```

    ### 8. monolog - 自訂 Monolog 處理器
    - **用途**：使用任何支援的 Monolog 處理器
    - **適用場景**：需要特殊日誌處理邏輯
    - **配置範例**：
    ```php
    'monolog' => [
        'driver' => 'monolog',
        'level' => env('LOG_LEVEL', 'debug'),
        'handler' => StreamHandler::class,
        'formatter' => env('LOG_MONOLOG_FORMATTER'),
        'formatter_with' => [
            'format' => null,
            'dateFormat' => null,
            'allowInlineLineBreaks' => true,
            'ignoreEmptyContextAndExtra' => true,
        ],
        'handler_with' => [
            'bubble' => true,
        ],
    ],
    ```

    ### 9. custom - 自訂處理器
    - **用途**：呼叫指定的 factory 來建立 channel
    - **適用場景**：完全自訂的日誌處理邏輯
    - **配置範例**：
    ```php
    'custom' => [
        'driver' => 'custom',
        'via' => \App\Logging\CustomLogger::class,
    ],
    ```

    ## 生活化比喻
    
    ### Channel Drivers 就像「不同的通訊方式」
    - **single**：就像「個人日記」，所有事情寫在同一本
    - **daily**：就像「每日報表」，每天一份新文件
    - **slack**：就像「即時通訊」，立即通知團隊
    - **stack**：就像「綜合報告」，同時使用多種方式
    - **papertrail**：就像「中央檔案室」，集中管理所有記錄
    - **syslog**：就像「系統公告欄」，與其他系統共用
--}}

{{--
### 日誌通道 driver 說明

#### 什麼是 driver？
- driver 代表「日誌通道的驅動方式」，決定這個 channel 背後用什麼技術或方式來記錄日誌。
- 生活化比喻：driver 就像「快遞公司」，你要寄包裹（log），可以選擇郵局（single）、黑貓（daily）、宅配通（slack）、多家一起寄（stack）…

#### 常見 driver 類型
| driver      | 說明                                   | 實際用途範例                      |
|-------------|----------------------------------------|-----------------------------------|
| single      | 單一檔案                               | 全部日誌寫到一個檔案              |
| daily       | 每日檔案                               | 每天自動產生一個新日誌檔案        |
| slack       | 發送到 Slack（即時通訊）               | 重大錯誤即時通知團隊              |
| syslog      | 寫到系統 syslog                        | 伺服器層級日誌                    |
| errorlog    | 寫到 PHP 的 error_log                  | 伺服器錯誤日誌                    |
| monolog     | 直接用 Monolog 的 handler/formatter    | 進階自訂、串接第三方服務           |
| custom      | 完全自訂，自己建立 Logger 實例         | 需要特殊邏輯或複合功能             |
| stack       | 聚合多個 channel（driver）             | 一次寫入多個地方                   |
| null        | 不記錄任何東西（丟棄日誌）             | 測試或關閉日誌                    |

#### 配置範例
```php
'channels' => [
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
    ],
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
    ],
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
    ],
]
```
--}}

{{-- ========================= --}}
{{-- # 4. Channel 配置選項 --}}
{{-- ========================= --}}

{{--
    ## 通用配置選項

    ### level - 日誌等級
    設定此 channel 記錄的最低等級：
    ```php
    'level' => env('LOG_LEVEL', 'debug'),
    ```
    
    等級從低到高：debug < info < notice < warning < error < critical < alert < emergency

    ### name - Channel 名稱
    自訂 channel 名稱，預設為當前環境名稱：
    ```php
    'name' => 'my-custom-channel',
    ```

    ## Single 和 Daily Channels 配置

    ### bubble - 訊息冒泡
    表示訊息處理後是否應該冒泡到其他 channels：
    ```php
    'bubble' => true, // 預設值
    ```

    ### locking - 檔案鎖定
    寫入前嘗試鎖定日誌檔案：
    ```php
    'locking' => false, // 預設值
    ```

    ### permission - 檔案權限
    日誌檔案的權限設定：
    ```php
    'permission' => 0644, // 預設值
    ```

    ### days - 保留天數（僅 Daily Channel）
    每日日誌檔案保留的天數：
    ```php
    'days' => 14, // 預設值
    ```

    ## Slack Channel 配置

    ### url - Webhook URL
    Slack webhook 的 URL：
    ```php
    'url' => env('LOG_SLACK_WEBHOOK_URL'),
    ```

    ### username - 發送者名稱
    在 Slack 中顯示的發送者名稱：
    ```php
    'username' => 'Laravel Log',
    ```

    ### emoji - 表情符號
    在 Slack 中顯示的表情符號：
    ```php
    'emoji' => ':boom:',
    ```

    ### level - 通知等級
    只有達到此等級才會發送到 Slack：
    ```php
    'level' => 'critical', // 預設只發送 critical 以上
    ```

    ## Papertrail Channel 配置

    ### host 和 port
    Papertrail 服務的主機和埠號：
    ```php
    'host' => env('PAPERTRAIL_URL'),
    'port' => env('PAPERTRAIL_PORT'),
    ```

    ## 完整配置範例

    ```php
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'slack'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'bubble' => true,
            'locking' => false,
            'permission' => 0644,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'bubble' => true,
            'locking' => false,
            'permission' => 0644,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],
    ],
    ```

    ## 生活化比喻
    
    ### 配置選項就像「餐廳服務設定」
    - **level**：就像「服務等級」，VIP 客戶優先處理
    - **bubble**：就像「轉介服務」，處理完後轉給其他部門
    - **locking**：就像「包廂預訂」，避免同時使用
    - **permission**：就像「門禁權限」，誰可以存取
    - **days**：就像「食材保存期限」，過期自動清理
--}}

{{-- ========================= --}}
{{-- # 5. 環境變數配置 --}}
{{-- ========================= --}}

{{--
    ## 環境變數設定

    ### .env 檔案配置
    ```env
    # 預設日誌通道
    LOG_CHANNEL=stack
    
    # 日誌等級
    LOG_LEVEL=debug
    
    # Slack Webhook URL
    LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
    
    # Papertrail 配置
    PAPERTRAIL_URL=logs.papertrailapp.com
    PAPERTRAIL_PORT=12345
    
    # 過時警告配置
    LOG_DEPRECATIONS_CHANNEL=null
    LOG_DEPRECATIONS_TRACE=false
    ```

    ## 環境特定配置

    ### 開發環境 (.env.local)
    ```env
    LOG_CHANNEL=single
    LOG_LEVEL=debug
    LOG_DEPRECATIONS_CHANNEL=null
    ```

    ### 測試環境 (.env.testing)
    ```env
    LOG_CHANNEL=single
    LOG_LEVEL=debug
    LOG_DEPRECATIONS_CHANNEL=null
    ```

    ### 生產環境 (.env.production)
    ```env
    LOG_CHANNEL=stack
    LOG_LEVEL=warning
    LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
    PAPERTRAIL_URL=logs.papertrailapp.com
    PAPERTRAIL_PORT=12345
    ```

    ## 過時警告配置

    ### 配置過時警告記錄
    ```php
    // config/logging.php
    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],
    ```

    ### 建立過時警告專用通道
    ```php
    'channels' => [
        'deprecations' => [
            'driver' => 'single',
            'path' => storage_path('logs/php-deprecation-warnings.log'),
            'level' => 'debug',
        ],
    ],
    ```

    ## 生活化比喻
    
    ### 環境變數就像「餐廳分店設定」
    - **開發環境**：就像「廚房測試區」，詳細記錄每個步驟
    - **測試環境**：就像「試營運」，記錄重要事件
    - **生產環境**：就像「正式營業」，只記錄重要問題和警報
    - **過時警告**：就像「食材過期提醒」，記錄即將淘汰的功能
--}}

{{-- ========================= --}}
{{-- # 6. 實際使用範例 --}}
{{-- ========================= --}}

{{--
    ## 基本使用

    ### 使用 Log Facade
    ```php
    use Illuminate\Support\Facades\Log;

    // 不同等級的日誌記錄
    Log::emergency('系統緊急情況');
    Log::alert('需要立即注意');
    Log::critical('嚴重錯誤');
    Log::error('一般錯誤');
    Log::warning('警告訊息');
    Log::notice('注意事項');
    Log::info('一般資訊');
    Log::debug('除錯資訊');

    // 帶上下文的日誌記錄
    Log::info('用戶登入', [
        'user_id' => $user->id,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);

    // 例外記錄
    try {
        // 風險操作
    } catch (Exception $e) {
        Log::error('操作失敗', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
    ```

    ### 使用 Logger 實例
    ```php
    use Illuminate\Log\LogManager;

    $logger = app(LogManager::class);

    $logger->info('使用 Logger 實例記錄');
    $logger->error('錯誤訊息', ['context' => '額外資訊']);
    ```

    ## 自訂 Channel 使用

    ### 指定特定 Channel
    ```php
    // 使用特定 channel
    Log::channel('slack')->error('發送到 Slack 的錯誤');

    // 使用多個 channels
    Log::stack(['single', 'slack'])->info('同時記錄到檔案和 Slack');
    ```

    ### 建立自訂 Channel
    ```php
    // 在 config/logging.php 中定義
    'channels' => [
        'orders' => [
            'driver' => 'daily',
            'path' => storage_path('logs/orders.log'),
            'level' => 'info',
            'days' => 30,
        ],
        
        'payments' => [
            'driver' => 'single',
            'path' => storage_path('logs/payments.log'),
            'level' => 'warning',
        ],
    ],

    // 在程式碼中使用
    Log::channel('orders')->info('新訂單建立', ['order_id' => $order->id]);
    Log::channel('payments')->warning('付款失敗', ['payment_id' => $payment->id]);
    ```

    ## 實際應用範例

    ### 用戶認證日誌
    ```php
    // app/Http/Controllers/Auth/LoginController.php
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            Log::info('用戶登入成功', [
                'user_id' => Auth::id(),
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended('/dashboard');
        }

        Log::warning('登入失敗', [
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'reason' => '密碼錯誤',
        ]);

        return back()->withErrors(['email' => '登入資訊不正確']);
    }
    ```

    ### 訂單處理日誌
    ```php
    // app/Services/OrderService.php
    public function createOrder(array $data)
    {
        try {
            $order = Order::create($data);
            
            Log::channel('orders')->info('訂單建立成功', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'total_amount' => $order->total_amount,
                'items_count' => $order->items->count(),
            ]);

            return $order;
        } catch (Exception $e) {
            Log::channel('orders')->error('訂單建立失敗', [
                'user_id' => $data['user_id'] ?? null,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw $e;
        }
    }
    ```

    ### 系統監控日誌
    ```php
    // app/Console/Commands/SystemHealthCheck.php
    public function handle()
    {
        // 檢查資料庫連線
        try {
            DB::connection()->getPdo();
            Log::info('資料庫連線正常');
        } catch (Exception $e) {
            Log::critical('資料庫連線失敗', [
                'error' => $e->getMessage(),
                'connection' => config('database.default'),
            ]);
        }

        // 檢查磁碟空間
        $diskUsage = disk_free_space('/') / disk_total_space('/') * 100;
        if ($diskUsage < 10) {
            Log::alert('磁碟空間不足', [
                'free_space_percent' => $diskUsage,
                'free_space_gb' => disk_free_space('/') / 1024 / 1024 / 1024,
            ]);
        }

        // 檢查記憶體使用
        $memoryUsage = memory_get_usage(true) / 1024 / 1024;
        if ($memoryUsage > 512) {
            Log::warning('記憶體使用過高', [
                'memory_usage_mb' => $memoryUsage,
            ]);
        }
    }
    ```

    ## 生活化比喻
    
    ### 日誌記錄就像「餐廳營運記錄」
    - **用戶登入**：就像「客人進門登記」
    - **訂單處理**：就像「點餐和出餐記錄」
    - **系統監控**：就像「廚房設備檢查」
    - **錯誤記錄**：就像「客訴和問題記錄」
    - **自訂 Channel**：就像「不同部門的專用記錄本」
--}}

{{-- ========================= --}}
{{-- # 7. 最佳實踐與建議 --}}
{{-- ========================= --}}

{{--
    ## 配置最佳實踐

    ### 1. 環境特定配置
    ```php
    // 開發環境：詳細記錄
    'default' => 'single',
    'level' => 'debug',

    // 生產環境：重要記錄
    'default' => 'stack',
    'level' => 'warning',
    'channels' => ['daily', 'slack'],
    ```

    ### 2. 日誌等級策略
    - **DEBUG**：開發時期詳細資訊
    - **INFO**：一般操作記錄
    - **WARNING**：需要注意的情況
    - **ERROR**：實際發生的錯誤
    - **CRITICAL**：嚴重問題
    - **ALERT**：需要立即行動
    - **EMERGENCY**：系統完全故障

    ### 3. 檔案管理策略
    ```php
    // 使用 daily driver 自動輪換
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
        'days' => 30, // 保留 30 天
    ],
    ```

    ### 4. 敏感資訊處理
    ```php
    // 避免記錄敏感資訊
    Log::info('用戶操作', [
        'user_id' => $user->id,
        'action' => 'password_change',
        // 不要記錄：'old_password' => $oldPassword,
        // 不要記錄：'new_password' => $newPassword,
    ]);
    ```

    ## 效能最佳實踐

    ### 1. 避免過度記錄
    ```php
    // 不好的做法：記錄太多細節
    Log::debug('每個請求的詳細資訊', [
        'request_data' => $request->all(),
        'response_data' => $response->getContent(),
    ]);

    // 好的做法：只記錄重要資訊
    Log::info('API 請求', [
        'endpoint' => $request->path(),
        'method' => $request->method(),
        'status_code' => $response->getStatusCode(),
        'response_time' => $responseTime,
    ]);
    ```

    ### 2. 使用適當的 Channel
    ```php
    // 業務邏輯使用專用 channel
    Log::channel('orders')->info('訂單處理');
    Log::channel('payments')->warning('付款問題');

    // 系統問題使用預設 channel
    Log::error('系統錯誤');
    ```

    ### 3. 非同步記錄（適用於高流量）
    ```php
    // 使用隊列處理日誌記錄
    Log::channel('slack')->queue('高流量日誌訊息');
    ```

    ## 監控與警報

    ### 1. 設定 Slack 警報
    ```php
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Alert',
        'emoji' => ':warning:',
        'level' => 'critical', // 只有嚴重問題才通知
    ],
    ```

    ### 2. 使用外部監控服務
    ```php
    // Papertrail 集中化日誌管理
    'papertrail' => [
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => SyslogUdpHandler::class,
        'handler_with' => [
            'host' => env('PAPERTRAIL_URL'),
            'port' => env('PAPERTRAIL_PORT'),
        ],
    ],
    ```

    ### 3. 定期清理舊日誌
    ```bash
    # 建立清理命令
    php artisan make:command CleanOldLogs

    # 在命令中實作清理邏輯
    $files = Storage::disk('logs')->files();
    foreach ($files as $file) {
        $lastModified = Storage::disk('logs')->lastModified($file);
        if (now()->diffInDays($lastModified) > 30) {
            Storage::disk('logs')->delete($file);
        }
    }
    ```

    ## 除錯技巧

    ### 1. 使用 Context 提供更多資訊
    ```php
    Log::error('資料庫查詢失敗', [
        'sql' => $query->toSql(),
        'bindings' => $query->getBindings(),
        'user_id' => auth()->id(),
        'request_url' => request()->fullUrl(),
    ]);
    ```

    ### 2. 使用 Log Levels 進行過濾
    ```php
    // 在開發環境記錄所有等級
    'level' => 'debug',

    // 在生產環境只記錄重要等級
    'level' => 'warning',
    ```

    ### 3. 建立日誌分析腳本
    ```php
    // 分析錯誤趨勢
    $errorLogs = file_get_contents(storage_path('logs/laravel.log'));
    $errorCount = substr_count($errorLogs, 'ERROR');
    $criticalCount = substr_count($errorLogs, 'CRITICAL');
    
    Log::info('日誌統計', [
        'total_errors' => $errorCount,
        'critical_errors' => $criticalCount,
        'date' => now()->toDateString(),
    ]);
    ```

    ## 生活化比喻
    
    ### 最佳實踐就像「餐廳營運 SOP」
    - **環境配置**：就像「不同時段的服務標準」
    - **日誌等級**：就像「不同重要程度的通知」
    - **檔案管理**：就像「定期整理和歸檔」
    - **敏感資訊**：就像「保護客戶隱私」
    - **效能優化**：就像「提高服務效率」
    - **監控警報**：就像「即時問題處理」
    - **除錯技巧**：就像「問題診斷和解決」

    ## 結語

    Laravel 的日誌系統提供了強大而靈活的日誌記錄功能。
    透過適當的配置和使用，可以建立完整的應用程式監控和除錯體系。
    記住：好的日誌記錄不僅是技術需求，更是維護和改進應用程式的重要工具。
--}}

{{--
## Building Log Stacks

### 什麼是 Stack Driver？
Stack driver 讓你可以把多個 log channel 組合成一個「堆疊」通道，像是一次寄送多個快遞，讓一條 log 同時記錄到多個地方。

### 配置範例
```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['syslog', 'slack'],
        'ignore_exceptions' => false,
    ],
    'syslog' => [
        'driver' => 'syslog',
        'level' => env('LOG_LEVEL', 'debug'),
        'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
        'replace_placeholders' => true,
    ],
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
        'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
        'level' => env('LOG_LEVEL', 'critical'),
        'replace_placeholders' => true,
    ],
],
```

### 配置解析
- `stack` 聚合了 `syslog` 和 `slack` 兩個 channel。
- `ignore_exceptions` 設為 false 時，某個 channel 出錯會影響其他 channel；設為 true 則不會。
- 每個 channel 可設定自己的最低等級（level），只有達到等級才會記錄。

### Log Level 等級說明
| 等級      | 嚴重程度 | 說明             |
|-----------|----------|------------------|
| emergency | 最高     | 系統完全無法運作 |
| alert     | 很高     | 需要立即行動     |
| critical  | 高       | 嚴重錯誤         |
| error     | 中高     | 一般錯誤         |
| warning   | 中       | 警告訊息         |
| notice    | 中低     | 注意事項         |
| info      | 低       | 一般資訊         |
| debug     | 最低     | 除錯資訊         |

### 實際應用範例
```php
Log::debug('An informational message.');
// syslog 會記錄（level: debug）
// slack 不會發送（level: critical，debug < critical）

Log::emergency('The system is down!');
// syslog 會記錄（emergency > debug）
// slack 會發送（emergency > critical）
```

### 生活化比喻
- Stack 就像「一單多送」的快遞服務，根據包裹重要性（level），決定送到哪些目的地（channel）。
- 也像餐廳訂位系統，重要訊息（緊急訂位）會同時通知簡訊、電話、Email。

### 最佳實踐建議
- 生產環境建議：重要訊息（critical 以上）才發送到即時通訊（如 Slack），避免訊息轟炸。
- 測試時可用 Log::channel('stack')->info('測試訊息') 驗證所有通道是否正常。
- 適量設置 channel，避免效能負擔。
--}}

{{--
## 寫入日誌訊息（Writing Log Messages）

### 1. 基本用法
Laravel 提供 Log facade，支援 RFC 5424 規範的八種等級：
- emergency
- alert
- critical
- error
- warning
- notice
- info
- debug

#### 範例
```php
use Illuminate\Support\Facades\Log;

Log::emergency($message);
Log::alert($message);
Log::critical($message);
Log::error($message);
Log::warning($message);
Log::notice($message);
Log::info($message);
Log::debug($message);
```
- 預設會寫入 logging 設定檔指定的預設 channel。

#### 控制器實例
```php
public function show(string $id): View
{
    Log::info('Showing the user profile for user: {id}', ['id' => $id]);
    return view('user.profile', [
        'user' => User::findOrFail($id)
    ]);
}
```

---

### 2. 傳遞情境資料（Contextual Information）
- 可傳遞陣列作為第二參數，讓 log 訊息更有意義。
```php
Log::info('User {id} failed to login.', ['id' => $user->id]);
```
- 生活化比喻：就像在日誌裡加上「備註欄」，方便未來追蹤。

---

### 3. 全域情境資料（withContext 與 shareContext）
- **withContext**：設定後，之後的 log 都會帶上這些資料（僅限目前請求）。
```php
Log::withContext(['request-id' => $requestId]);
```
- **shareContext**：跨所有 channel、所有 log 實例都會帶上這些資料。
```php
Log::shareContext(['request-id' => $requestId]);
```
- 常見應用：記錄 request-id、user-id，方便追蹤請求流程。

#### Middleware 範例
```php
public function handle(Request $request, Closure $next): Response
{
    $requestId = (string) Str::uuid();
    Log::withContext(['request-id' => $requestId]);
    $response = $next($request);
    $response->headers->set('Request-Id', $requestId);
    return $response;
}
```

---

### 4. 指定 Channel 寫入
- 可用 `Log::channel('channel_name')` 指定寫入特定 channel。
```php
Log::channel('slack')->info('Something happened!');
```
- 也可用 `Log::stack(['single', 'slack'])` 同時寫入多個 channel。
```php
Log::stack(['single', 'slack'])->info('Something happened!');
```
- 生活化比喻：像是選擇要通知哪些部門（Email、簡訊、Line 群組）。

---

### 5. 動態（On-Demand）Channel
- 可在程式中臨時建立 channel，不需預先寫在設定檔。
```php
Log::build([
  'driver' => 'single',
  'path' => storage_path('logs/custom.log'),
])->info('Something happened!');
```
- 也可將動態 channel 加入 stack：
```php
$channel = Log::build([
  'driver' => 'single',
  'path' => storage_path('logs/custom.log'),
]);
Log::stack(['slack', $channel])->info('Something happened!');
```
- 生活化比喻：臨時開一個新群組，專門記錄某次活動。

---

### 6. 最佳實踐建議
- 重要操作、異常、用戶行為建議都記錄，方便日後追蹤。
- 使用情境資料（context）讓 log 更有意義。
- 適當分級，避免低等級訊息淹沒重要訊息。
- 測試時可用 `Log::info('測試訊息', [...])` 驗證 log 是否正確寫入。
--}}

{{--
## Monolog Channel 自訂化

### 1. 自訂 Monolog 配置（Customizing Monolog for Channels）

#### 什麼是 Tap？
- Tap 允許您在 channel 建立後，自訂 Monolog 實例
- 就像「插入」自訂邏輯到現有的 channel 中
- 生活化比喻：就像在現有的水龍頭上加裝過濾器

#### 配置範例
```php
'single' => [
    'driver' => 'single',
    'tap' => [App\Logging\CustomizeFormatter::class],
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'replace_placeholders' => true,
],
```

#### Tap 類別實作
```php
<?php
namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class CustomizeFormatter
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                '[%datetime%] %channel%.%level_name%: %message% %context% %extra%'
            ));
        }
    }
}
```

---

### 2. 建立 Monolog Handler Channels

#### 使用 monolog driver
```php
'logentries' => [
    'driver'  => 'monolog',
    'handler' => Monolog\Handler\SyslogUdpHandler::class,
    'handler_with' => [
        'host' => 'my.logentries.internal.datahubhost.company.com',
        'port' => '10000',
    ],
],
```

#### 可用的 Monolog Handlers
- Monolog\Handler\SyslogUdpHandler：UDP syslog
- Monolog\Handler\BrowserConsoleHandler：瀏覽器控制台
- Monolog\Handler\NewRelicHandler：New Relic
- Monolog\Handler\StreamHandler：檔案串流
- Monolog\Handler\RotatingFileHandler：輪換檔案

---

### 3. Monolog Formatters

#### 自訂 Formatter
```php
'browser' => [
    'driver' => 'monolog',
    'handler' => Monolog\Handler\BrowserConsoleHandler::class,
    'formatter' => Monolog\Formatter\HtmlFormatter::class,
    'formatter_with' => [
        'dateFormat' => 'Y-m-d',
    ],
],
```

#### 使用 Handler 預設 Formatter
```php
'newrelic' => [
    'driver' => 'monolog',
    'handler' => Monolog\Handler\NewRelicHandler::class,
    'formatter' => 'default',
],
```

#### 常用 Formatters
- LineFormatter：單行格式
- HtmlFormatter：HTML 格式
- JsonFormatter：JSON 格式
- NormalizerFormatter：標準化格式

---

### 4. Monolog Processors

#### 配置 Processors
```php
'memory' => [
    'driver' => 'monolog',
    'handler' => Monolog\Handler\StreamHandler::class,
    'handler_with' => [
        'stream' => 'php://stderr',
    ],
    'processors' => [
        // 簡單語法
        Monolog\Processor\MemoryUsageProcessor::class,
        
        // 帶選項
        [
            'processor' => Monolog\Processor\PsrLogMessageProcessor::class,
            'with' => ['removeUsedContextFields' => true],
        ],
    ],
],
```

#### 常用 Processors
- MemoryUsageProcessor：記錄記憶體使用量
- PsrLogMessageProcessor：PSR-3 訊息處理
- WebProcessor：記錄 Web 請求資訊
- UidProcessor：產生唯一 ID

---

### 5. 透過 Factories 建立自訂 Channels

#### 配置自訂 Driver
```php
'channels' => [
    'example-custom-channel' => [
        'driver' => 'custom',
        'via' => App\Logging\CreateCustomLogger::class,
    ],
],
```

#### Factory 類別實作
```php
<?php
namespace App\Logging;

use Monolog\Logger;

class CreateCustomLogger
{
    public function __invoke(array $config): Logger
    {
        return new Logger('custom');
    }
}
```

---

### 6. 使用 Laravel Pail 即時監控日誌

#### 安裝
```bash
composer require --dev laravel/pail
```

#### 基本使用
```bash
# 開始監控日誌
php artisan pail

# 增加詳細度
php artisan pail -v

# 最大詳細度（包含例外堆疊）
php artisan pail -vv
```

#### 過濾功能
```bash
# 按類型過濾
php artisan pail --filter="QueryException"

# 按訊息過濾
php artisan pail --message="User created"

# 按等級過濾
php artisan pail --level=error

# 按用戶過濾
php artisan pail --user=1
```

---

### 7. 實際應用場景

#### 1. 自訂日誌格式
```php
class CustomFormatter
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
            ));
        }
    }
}
```

#### 2. 記錄額外資訊
```php
'web' => [
    'driver' => 'monolog',
    'handler' => Monolog\Handler\StreamHandler::class,
    'handler_with' => [
        'stream' => storage_path('logs/web.log'),
    ],
    'processors' => [
        Monolog\Processor\WebProcessor::class,
        Monolog\Processor\MemoryUsageProcessor::class,
    ],
],
```

#### 3. 多環境配置
```php
// 開發環境
'dev' => [
    'driver' => 'monolog',
    'handler' => Monolog\Handler\StreamHandler::class,
    'handler_with' => [
        'stream' => 'php://stdout',
    ],
    'formatter' => Monolog\Formatter\JsonFormatter::class,
],

// 生產環境
'prod' => [
    'driver' => 'monolog',
    'handler' => Monolog\Handler\SyslogUdpHandler::class,
    'handler_with' => [
        'host' => env('SYSLOG_HOST'),
        'port' => env('SYSLOG_PORT'),
    ],
],
```

---

### 8. 最佳實踐建議

#### 1. 效能考量
- 避免在 processors 中執行耗時操作
- 適當使用 formatter 減少日誌大小
- 考慮使用非同步 handlers

#### 2. 安全性
- 避免記錄敏感資訊（密碼、token）
- 使用適當的日誌等級
- 定期清理舊日誌檔案

#### 3. 除錯技巧
- 使用 Pail 即時監控
- 設定適當的過濾條件
- 記錄足夠的上下文資訊

---

### 9. 生活化比喻

#### Tap 就像「水龍頭過濾器」
- 在現有的水龍頭（channel）上加裝過濾器（tap）
- 可以過濾、淨化、改變水流（日誌格式）

#### Processors 就像「食材處理器」
- 在烹飪前處理食材（日誌訊息）
- 可以切片、調味、包裝（加入額外資訊）

#### Pail 就像「即時監控器」
- 像監控攝影機一樣即時查看日誌
- 可以設定警報、過濾、錄影（記錄）
--}}

{{--
### Slack 通道說明

#### 什麼是 Slack？
- Slack 是一個團隊協作的即時通訊平台，類似 Line、Discord、Microsoft Teams。
- 在 Laravel logging 裡，slack channel 代表「把日誌訊息發送到 Slack 群組」。
- 不是寫到本地的檔案或資料夾，而是透過網路 API 把訊息推送到 Slack 的聊天室。

#### 配置範例
```php
'slack' => [
    'driver' => 'slack',
    'url' => env('LOG_SLACK_WEBHOOK_URL'),
    'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
    'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
    'level' => env('LOG_LEVEL', 'critical'),
    'replace_placeholders' => true,
],
```

#### 生活化比喻
- log 檔案：像是你家裡的日記本，只有你自己看得到。
- slack channel：像是你在公司群組裡大聲說話，大家都會即時收到通知。

#### 實際應用
- 當系統發生重大錯誤（如 critical、emergency），Laravel 會自動把訊息「推播」到 Slack 群組，讓工程師、管理員即時收到警報。
- 這樣不用一直盯著 log 檔案，也不會漏掉重要訊息。

#### 小結
- Slack 不是 log 檔案或資料夾，而是**一種即時通知通道**。
- 主要用途是讓團隊即時收到重要日誌訊息，提升反應速度與協作效率。
--}} 