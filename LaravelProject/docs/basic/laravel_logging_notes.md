
# *Laravel Logging 日誌*

---

## 1. **簡介與核心概念**

- **Laravel 提供強大且彈性的日誌系統**，可將訊息記錄到檔案、系統錯誤日誌，甚至 Slack。
- 日誌以「**Channel**」為單位，每個 channel 代表一種寫入方式（如單一檔案、Slack、系統日誌等）。
- *生活化比喻*： Channel 就像「不同的收件箱」，你可以同時把訊息寄到多個收件箱。
- Laravel 底層使用 **Monolog**，支援多種 handler，可高度自訂。

---

## 2. **設定檔與 Channel 驅動**

- 所有日誌設定都在 `config/logging.php`。
- 預設使用 `stack` channel，可同時聚合多個 channel。
- *常見 Channel 驅動*：
  - **single**：單一檔案
  - **daily**：每日輪替檔案
  - **slack**：發送到 Slack
  - **syslog**：系統日誌
  - **errorlog**：PHP 系統錯誤日誌
  - **papertrail**：雲端日誌服務
  - **monolog**：自訂 Monolog handler
  - **custom**：自訂工廠產生 channel
  - **stack**：聚合多個 channel
- *註解*： 每個 channel 都有 driver，driver 決定訊息實際寫入方式。
- *可自訂 channel name*：
  ```php
  'stack' => [
      'driver' => 'stack',
      'name' => 'channel-name',
      'channels' => ['single', 'slack'],
  ],
  ```

---

## 3. **Channel 設定細節**

- *stack channel*
  - 可聚合多個 channel
  - 範例：
    ```php
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
        'ignore_exceptions' => false,
    ],
    ```
- *single/daily channel*
  - 可設定 bubble、permission、locking
  - daily 可設保留天數 days
  - 範例：
    ```php
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'days' => 14,
        'permission' => 0644,
        'locking' => false,
    ],
    ```
- *slack channel*
  - 需設定 webhook url
  - 可自訂 username、emoji、level
  - 範例：
    ```php
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'critical',
    ],
    ```
- *papertrail channel*
  - 需設定 host、port
  - 範例：
    ```php
    'papertrail' => [
        'driver' => 'monolog',
        'handler' => Monolog\Handler\SyslogUdpHandler::class,
        'handler_with' => [
            'host' => env('PAPERTRAIL_URL'),
            'port' => env('PAPERTRAIL_PORT'),
        ],
    ],
    ```
- *deprecations channel*
  - 可專門記錄 PHP/Laravel 棄用警告
  - 範例：
    ```php
    'deprecations' => [
        'driver' => 'single',
        'path' => storage_path('logs/php-deprecation-warnings.log'),
    ],
    ```
  - 也可用 LOG_DEPRECATIONS_CHANNEL、LOG_DEPRECATIONS_TRACE 設定：
    ```php
    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],
    ```

---

## 4. **Log 等級與訊息寫入**

- *等級（Level）*：emergency > alert > critical > error > warning > notice > info > debug
- 每個 channel 可設定最低 level，低於此等級不會記錄。
- *寫入方法（Log facade）*：
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
- *比喻*： 等級就像「重要性標籤」，只有達標才會被送到指定收件箱。
- *範例*：
  ```php
  Log::info('使用者登入', ['id' => $user->id]);
  Log::channel('slack')->error('系統異常！');
  Log::stack(['single', 'slack'])->warning('多管道警告');
  ```
- *官方 Controller 範例*：
  ```php
  namespace App\Http\Controllers;
  use App\Models\User;
  use Illuminate\Support\Facades\Log;
  use Illuminate\View\View;
  class UserController extends Controller {
      public function show(string $id): View {
          Log::info('Showing the user profile for user: {id}', ['id' => $id]);
          return view('user.profile', [
              'user' => User::findOrFail($id)
          ]);
      }
  }
  ```

---

## 5. **Contextual 資訊與全域 Context**

- 可傳遞陣列作為 context，讓訊息更有意義：
  ```php
  Log::info('User {id} failed to login.', ['id' => $user->id]);
  ```
- *withContext*：只影響後續同一 channel 的 log
  ```php
  Log::withContext(['request-id' => $uuid]);
  ```
- *shareContext*：全域影響所有 channel
  ```php
  Log::shareContext(['request-id' => $uuid]);
  ```
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
          $requestId = (string) Str::uuid();
          Log::withContext(['request-id' => $requestId]);
          $response = $next($request);
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

- *channel()*：指定單一 channel 寫入
  ```php
  Log::channel('slack')->info('Something happened!');
  ```
- *stack()*：臨時組合多個 channel
  ```php
  Log::stack(['single', 'slack'])->info('Something happened!');
  ```
- *build()*：臨時建立 channel
  ```php
  Log::build([
    'driver' => 'single',
    'path' => storage_path('logs/custom.log'),
  ])->info('Something happened!');
  ```
- *臨時 channel 也可組 stack*：
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

- *tap*：可自訂 Monolog 實例（如 formatter）
  - 範例：
    ```php
    'single' => [
        'driver' => 'single',
        'tap' => [App\Logging\CustomizeFormatter::class],
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'replace_placeholders' => true,
    ],
    ```
  - tap 類別需*實作 __invoke(Logger $logger)*
  - tap 類別範例：
    ```php
    namespace App\Logging;
    use Illuminate\Log\Logger;
    use Monolog\Formatter\LineFormatter;
    class CustomizeFormatter {
        public function __invoke(Logger $logger): void {
            foreach ($logger->getHandlers() as $handler) {
                $handler->setFormatter(new LineFormatter(
                    '[%datetime%] %channel%.%level_name%: %message% %context% %extra%'
                ));
            }
        }
    }
    ```
- *monolog driver*：可指定 handler、formatter、processors
  - 範例：
    ```php
    'logentries' => [
        'driver'  => 'monolog',
        'handler' => Monolog\Handler\SyslogUdpHandler::class,
        'handler_with' => [
            'host' => 'my.logentries.internal',
            'port' => '10000',
        ],
    ],
    'browser' => [
        'driver' => 'monolog',
        'handler' => Monolog\Handler\BrowserConsoleHandler::class,
        'formatter' => Monolog\Formatter\HtmlFormatter::class,
        'formatter_with' => [
            'dateFormat' => 'Y-m-d',
        ],
    ],
    'memory' => [
        'driver' => 'monolog',
        'handler' => Monolog\Handler\StreamHandler::class,
        'processors' => [
            Monolog\Processor\MemoryUsageProcessor::class,
            [
                'processor' => Monolog\Processor\PsrLogMessageProcessor::class,
                'with' => ['removeUsedContextFields' => true],
            ],
        ],
    ],
    'newrelic' => [
        'driver' => 'monolog',
        'handler' => Monolog\Handler\NewRelicHandler::class,
        'formatter' => 'default',
    ],
    ```
- *custom driver*：完全自訂 Monolog 實例
  - via 指定工廠類別，需實作 __invoke(array $config): Logger
  - 範例：
    ```php
    'channels' => [
        'example-custom-channel' => [
            'driver' => 'custom',
            'via' => App\Logging\CreateCustomLogger::class,
        ],
    ],
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

- *Pail*：官方即時 tail log 工具，支援所有 driver
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
  - `--filter`：依型別、檔案、訊息、stack trace 過濾
  - `--message`：只過濾訊息內容
  - `--level`：只顯示特定等級
  - `--user`：只顯示特定使用者的 log
  - 範例：
    ```bash
    php artisan pail --filter="QueryException"
    php artisan pail --message="User created"
    php artisan pail --level=error
    php artisan pail --user=1
    ```
- *比喻*： Pail 就像「即時監控螢幕」，讓你隨時掌握系統動態。

---