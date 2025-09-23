
# *Laravel Logging 日誌 筆記*

---

## 1. **簡介與核心概念**

- Laravel 提供強大且彈性的 __日誌系統__，可將訊息記錄到 __檔案、系統錯誤日誌，甚至 Slack__。

- 日誌以「__Channel__」為單位，每個 channel 代表一種`寫入方式`（如`單一檔案、Slack、系統日誌`等）。

<!-- 
這兩個概念確實有關聯，但不完全重疊。

Channel：代表一種「日誌管道」或「日誌類型」，例如：single、slack、syslog。
Driver：決定這個 channel 的「實際寫入方式」，例如：single driver 會寫入單一檔案，slack driver 會發送到 Slack。
關係：
每個 channel 都必須指定一個 driver，
driver 決定 channel 的訊息要怎麼被處理或存放。 
-->

```php
// Channel 是「日誌管道」的名稱
// Driver 是「日誌管道」的寫入方式
// 一個 channel 會對應一個 driver，但 channel 可以有不同 driver。
'channels' => [
    'slack' => [    // 這是 channel
        'driver' => 'slack', // 寫入方式
        'url' => 'https://hooks.slack.com/services/xxx',
        'level' => 'critical',
    ],
    'single' => [    // 這也是 channel
        'driver' => 'single', // 寫入方式
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
    ],
]
```

- *生活化比喻*： Channel 就像「__不同的收件箱__」，你可以同時把訊息寄到多個收件箱。

- Laravel 內建的 `log 系統（Monolog` __直接支援這些層級__，讓你可以用 `Log::debug()`、`Log::info()` 等方法記錄**不同嚴重程度**的訊息。

---

## 2. **設定檔與 Channel 驅動**

- 所有日誌設定都在 `config/logging.php`。

- **預設**使用 `stack` channel，可同時 __聚合多個 channel__。

- *常見 Channel 驅動*：

  - __single__：`單一檔案`
  - __daily__：`每日輪替`檔案
  - __slack__：發送到 `Slack`
  - __syslog__：`系統日誌`
  - __errorlog__：`PHP 系統`錯誤日誌
  - __papertrail__：`雲端`日誌服務
  - __monolog__：`自訂` Monolog handler
  - __custom__：`自訂`工廠產生 channel
  - __stack__：`聚合`多個 channel

- *註解*： __每個 channel 都有 driver__，driver `決定訊息實際寫入方式`。

<!-- 
在 Laravel 的 logging 系統裡，driver 指的是「訊息實際寫入的方式或工具」。
每個 log channel 都會指定一個 driver，決定 log 訊息要怎麼處理或存放。 
-->

---

- *可自訂 channel name*：

  ```php
  'stack' => [ // 定義一個名為 'stack' 的 channel
      'driver' => 'stack', // 使用 'stack' driver，可同時寫入多個 channel
      'name' => 'channel-name', // 自訂這個 stack channel 的名稱（可選）
      'channels' => ['single', 'slack'], // 指定要同時寫入哪些 channel（這裡是 'single' 和 'slack'）
  ],
  ```

---

## 3. **Channel 設定細節**

- *stack channel*

  - 可 __聚合__ 多個 channel
  - 範例：
    ```php
    'stack' => [
        'driver' => 'stack',                 // 使用 stack 驅動，可同時寫入多個 channel
        'channels' => ['single', 'slack'],   // 指定要堆疊的 channel（如 single 檔案、slack 通知）
        'ignore_exceptions' => false,         // 是否忽略 channel 發生的例外，false 代表不忽略
    ],
    ```

---

- *single/daily channel*

  - 可設定 `bubble、permission、locking`

  <!-- bubble：是否讓 log 傳遞到其他 handler，通常預設 true，代表可以繼續往上層處理。
       permission：設定 log 檔案的檔案權限（如 0644），控制誰能讀寫。
       locking：是否啟用檔案鎖定，避免多個程序同時寫入造成衝突。 -->

  - `daily` 可設 __保留天數 days__
  - 範例：

    ```php
    'daily' => [
        'driver' => 'daily',                      // 日誌類型：每日分檔
        'path' => storage_path('logs/laravel.log'),// 日誌檔案路徑
        'days' => 14,                             // 保留天數
        'permission' => 0644,                     // 檔案權限
        'locking' => false,                       // 是否啟用檔案鎖定
        // 可設定 bubble、permission、locking 等參數
    ],
    ```

---

- *slack channel*

  - 需設定 __webhook url__

  <!-- webhook url 是 Slack 提供的「訊息接收網址」，
       你必須在 Slack 建立 Incoming Webhook，
       取得一組網址，
       設定在 Laravel 的 log channel，
       這樣 log 訊息才能自動發送到指定的 Slack 頻道。 -->

  - 可自訂 __username、emoji、level__
  - 範例：

    ```php
    'slack' => [
        'driver' => 'slack',                        // 使用 slack 驅動
        'url' => env('LOG_SLACK_WEBHOOK_URL'),      // 設定 webhook url
        'username' => 'Laravel Log',                // 自訂訊息顯示名稱
        'emoji' => ':boom:',                        // 自訂 emoji
        'level' => 'critical',                      // 只發送 critical 以上等級的 log
    ]
    // 需設定 webhook url，可自訂 username、emoji、level
    ```

---

- *papertrail channel*

<!-- Papertrail 是一個雲端日誌管理服務，
     可以集中收集、搜尋、分析多台伺服器或應用程式的 log，
     方便即時監控和問題排查。 -->

  - 需設定 __host、port__
  - 範例：

    ```php
    'papertrail' => [
        'driver' => 'monolog',                                 // 使用 monolog 驅動
        'handler' => Monolog\Handler\SyslogUdpHandler::class,  // 指定 handler 類別
        'handler_with' => [
            'host' => env('PAPERTRAIL_URL'),                   // 設定 Papertrail 主機
            'port' => env('PAPERTRAIL_PORT'),                  // 設定 Papertrail 連接埠
        ],
    ]
    // 用於將 log 傳送到 Papertrail 日誌服務
    ```

---

- *deprecations channel*

  - 可專門記錄 PHP/Laravel __棄用警告__

  <!-- 「棄用警告」是指程式中「使用了未來版本將移除或不建議使用的功能」，
        PHP 會發出警告，提醒你這些語法或函式已經不建議再用，
        建議改用新的寫法，以免未來升級時出現問題。 -->
        
  - 範例：

    ```php
    'deprecations' => [
        'driver' => 'single',                                      // 使用單一檔案記錄
        'path' => storage_path('logs/php-deprecation-warnings.log'),// 設定記錄路徑
    ],
    ```
  - 也可用 __LOG_DEPRECATIONS_CHANNEL__、__LOG_DEPRECATIONS_TRACE__ 設定：

  <!-- LOG_DEPRECATIONS_CHANNEL 和 LOG_DEPRECATIONS_TRACE
       是 Laravel 的環境變數（.env 設定），
       用來控制「棄用警告」要記錄在哪個 log channel，以及是否記錄堆疊追蹤（trace）。
       你可以在 .env 檔案裡設定這兩個變數，
       Laravel 會自動依據這些設定記錄 PHP 棄用警告。 -->

    ```php
    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),      // 指定記錄 channel
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),           // 是否記錄堆疊追蹤
    ],
    ```

---

## 4. **Log 等級與訊息寫入**

- *等級*（Level）：`emergency > alert > critical > error > warning > notice > info > debug`
- 每個 channel __可設定最低 level__，低於此等級不會記錄。

- *寫入方法*（Log facade）：

  ```php
  use Illuminate\Support\Facades\Log;
  Log::emergency($msg);
  Log::alert($msg);
  Log::critical($msg);
  Log::error($msg);
  Log::warning($msg);
  Log::notice($msg);
  Log::info($msg);
  Log::debug($msg);
  ```

- *比喻*： 等級就像「__重要性標籤__」，只有達標才會被送到指定收件箱。

- *範例*：

  ```php
  Log::info('使用者登入', ['id' => $user->id]);
  Log::channel('slack')->error('系統異常！');
  Log::stack(['single', 'slack'])->warning('多管道警告');
  ```

---

- *官方 Controller 範例*：

  ```php
  namespace App\Http\Controllers;

  use App\Models\User;
  use Illuminate\Support\Facades\Log;
  use Illuminate\View\View;

  class UserController extends Controller {
      public function show(string $id): View {
          // 記錄 info 級別的 log，帶入 id 參數
          Log::info('Showing the user profile for user: {id}', ['id' => $id]);
          return view('user.profile', [
              'user' => User::findOrFail($id)
          ]);
      }
  }
  ```

---

## 5. **Contextual 資訊與全域 Context**

- 可傳遞`陣列`作為 `context`，讓訊息更有意義：

  ```php
  Log::info('User {id} failed to login.', ['id' => $user->id]);
  ```

---

- *withContext*： 只影響後續 `同一 channel` 的 log

  ```php
  Log::withContext(['request-id' => $uuid]);
  ```
  <!-- 當你使用 Log::withContext(['request-id' => $uuid]); 時，
       之後所有 log 訊息都會自動帶上 request-id 這個欄位，
       方便你在 log 檔案裡追蹤同一次請求的所有紀錄，
       有助於除錯和分析請求流程。 -->

<!--        
Log::withContext(['request-id' => $requestId]);
只會影響當前 log 實例（通常是單一 channel），
只在這次 log 呼叫時帶上 context。

Log::shareContext(['request-id' => $requestId]);
會全域影響所有 channel，
之後所有 log 訊息都會自動帶上這個 context。 
-->

```php
// 「當前」是指你呼叫 Log::withContext() 之後，接下來的那一次 log 訊息，
// 或是只影響你用 Log 物件的那個 log 動作，不會影響其他 log channel 或之後的 log 訊息。
Log::withContext(['request-id' => $requestId]);
Log::info('這則 log 會帶有 request-id');

Log::info('這則 log 不會自動帶有 request-id'); // 這裡 context 不會自動延續
```

  ```php
  namespace App\Http\Controllers;

  use Illuminate\Support\Facades\Log;
  use Illuminate\Http\Request;
  use Illuminate\View\View;
  use App\Models\User;

  class UserController extends Controller
  {
      public function show(Request $request, string $id): View
      {
          $uuid = $request->header('X-Request-ID'); // 假設 request header 有 request-id
          Log::withContext(['request-id' => $uuid]); // 設定 log context

          Log::info('Showing the user profile for user: {id}', ['id' => $id]);

          return view('user.profile', [
              'user' => User::findOrFail($id)
          ]);
      }
  }
  ```

---

- *shareContext*： __全域__ 影響所有 channel

  ```php
  Log::shareContext(['request-id' => $uuid]);
  ```

---

- *官方 Middleware 範例*：

  ```php
  namespace App\Http\Middleware;

  use Closure;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Log;
  use Illuminate\Support\Str;
  use Symfony\Component\HttpFoundation\Response;

  class AssignRequestId {
      public function handle(Request $request, Closure $next): Response {
          // 產生唯一的 request-id
          $requestId = (string) Str::uuid();

          // 設定 log context，讓所有 log 都帶有 request-id
          Log::withContext(['request-id' => $requestId]);

          // 執行後續 middleware 或 controller
          $response = $next($request);

          // 在回應 header 加上 request-id，方便前端或追蹤
          $response->headers->set('Request-Id', $requestId);

          return $response;
      }
  }
  ```

- **全域 context 範例**：

  ```php
  Log::shareContext(['request-id' => $requestId]);
  ```

---

## 6. **動態/臨時 Channel 與 Stack**

- *channel()*：指定 __單一 channel__ 寫入

  ```php
  Log::channel('slack')->info('Something happened!');
  ```

---

- *stack()*：臨時組合 __多個 channel__

  ```php
  Log::stack(['single', 'slack'])->info('Something happened!');
  // 「使用 Laravel 的 Log，臨時組合 single 和 slack 兩個 channel，
  // 然後記錄一則 info 等級的 log 訊息 'Something happened!'，
  //這則訊息會同時寫入 single 檔案和發送到 Slack。」
  
  ---

  Log::stack(['single', 'slack'])->info('A'); // 這次同時寫入 single 和 slack
  Log::info('B'); // 只寫入預設 channel，不會再同時寫入 single 和 slack
  ```
  
<!--   
Log::stack(['single', 'slack']) 這個方法只在這一次 log 動作時，暫時組合你指定的多個 channel。
它不會永久建立一個新的 channel，也不會影響 config/logging.php 的設定。
下次你要用 stack 組合，必須再呼叫一次 Log::stack()。 
-->

---

- *build()*： __臨時__ 建立 channel

  ```php
  Log::build([
    'driver' => 'single',
    'path' => storage_path('logs/custom.log'),
  ])->info('Something happened!');
  ```

---

- *臨時 channel 也可組 `stack`*：

  ```php
  $channel = Log::build([
    'driver' => 'single',
    'path' => storage_path('logs/custom.log'),
  ]);
  Log::stack(['slack', $channel])->info('Something happened!');
  ```
- *比喻*： 臨時 channel 就像臨時信箱，隨用隨建。

---

## 7. **Monolog 進階自訂**

- *tap*：可 __自訂 Monolog 實例__（如 formatter）

  - 範例：
    ```php
  'single' => [
      'driver' => 'single',                                 // 單一檔案記錄 log
      'tap' => [App\Logging\CustomizeFormatter::class],      // 使用自訂 formatter
      'path' => storage_path('logs/laravel.log'),            // log 檔案路徑
      'level' => env('LOG_LEVEL', 'debug'),                  // log 等級
      'replace_placeholders' => true,                        // 啟用訊息佔位符替換
  ]
    ```
  - __tap 類別__ 需 *實作 `__invoke(Logger $logger)`*

  - __tap 類別__ 範例：

    ```php
    namespace App\Logging;

    use Illuminate\Log\Logger;
    use Monolog\Formatter\LineFormatter;

    class CustomizeFormatter {
        public function __invoke(Logger $logger): void {
            // 取得 logger 的所有 handler（處理 log 的物件）
            foreach ($logger->getHandlers() as $handler) {
                // 設定每個 handler 使用自訂格式
                $handler->setFormatter(new LineFormatter(
                    // log 格式：時間、頻道、等級、訊息、context、extra
                    '[%datetime%] %channel%.%level_name%: %message% %context% %extra%'
                ));
            }
        }
    }
    ```

---

- *monolog driver*：可指定 `handler、formatter、processors`

  - 範例：

    ```php
    'logentries' => [
        'driver'  => 'monolog',                                 // 使用 monolog 驅動
        'handler' => Monolog\Handler\SyslogUdpHandler::class,   // 使用 Syslog UDP handler
        'handler_with' => [
            'host' => 'my.logentries.internal',                 // 設定主機
            'port' => '10000',                                  // 設定連接埠
        ],
    ],

    'browser' => [
        'driver' => 'monolog',                                  // 使用 monolog 驅動
        'handler' => Monolog\Handler\BrowserConsoleHandler::class, // log 輸出到瀏覽器 console
        'formatter' => Monolog\Formatter\HtmlFormatter::class,  // 使用 HTML 格式
        'formatter_with' => [
            'dateFormat' => 'Y-m-d',                            // 設定日期格式
        ],
    ],

    'memory' => [
        'driver' => 'monolog',                                  // 使用 monolog 驅動
        'handler' => Monolog\Handler\StreamHandler::class,      // 使用 Stream handler
        'processors' => [
            Monolog\Processor\MemoryUsageProcessor::class,      // 加入記憶體用量處理器
            [
                'processor' => Monolog\Processor\PsrLogMessageProcessor::class, // PSR 格式處理器
                'with' => ['removeUsedContextFields' => true],  // 移除已用 context 欄位
            ],
        ],
    ],

    'newrelic' => [
        'driver' => 'monolog',                                  // 使用 monolog 驅動
        'handler' => Monolog\Handler\NewRelicHandler::class,    // 傳送 log 到 NewRelic
        'formatter' => 'default',                               // 使用預設格式
    ],
    ```

---

- *custom driver*：完全 __自訂 Monolog 實例__

  - `via` 指定 __工廠類別__，需實作 `__invoke(array $config): Logger`
  - 範例：

    ```php
    'channels' => [
        'example-custom-channel' => [
            'driver' => 'custom',                        // 使用自訂 log 驅動
            'via' => App\Logging\CreateCustomLogger::class, // 指定自訂 logger 類別
        ],
    ],
    // 可用於擴充 log 行為，完全自訂 log 處理邏輯
    ```
    ```php
    namespace App\Logging;
    use Monolog\Logger;
    class CreateCustomLogger {
        public function __invoke(array $config): Logger {
            return new Logger(/* ... */);
        }
    }
    ```

---

## 8. **Pail 即時日誌監控工具**

- *Pail*：官方 __即時 tail log__ 工具，支援所有 driver

- 安裝：

  ```bash
  composer require --dev laravel/pail
  ```

- 使用：

  ```bash
  php artisan pail
  php artisan pail -v   # 詳細模式
  php artisan pail -vv  # 顯示 exception stack trace
  ```

- *常用過濾*：

  - `--filter`：依 __型別、檔案、訊息、stack trace__ 過濾
  - `--message`：只過濾 __訊息內容__
  - `--level`：只顯示 __特定等級__
  - `--user`：只顯示 __特定使用者的 log__

  - 範例：

    ```bash
    php artisan pail --filter="QueryException"   # 只搜尋包含 QueryException 的 log
    php artisan pail --message="User created"    # 只搜尋訊息包含 User created 的 log
    php artisan pail --level=error               # 只搜尋 error 等級的 log
    php artisan pail --user=1                    # 只搜尋 user id 為 1 的 log
    ```

- *比喻*： `Pail` 就像「__即時監控螢幕__」，讓你隨時掌握系統動態。

---