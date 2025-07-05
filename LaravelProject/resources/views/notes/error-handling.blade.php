{{-- ========================= --}}
{{-- # Laravel Error Handling 錯誤處理完整教學 --}}
{{-- ========================= --}}

{{--
    ## 目錄
    1. 錯誤處理介紹與基本概念
    2. 錯誤處理配置與設定
    3. 錯誤與例外的報告（Reporting Exceptions）
    4. 全域 Log Context 與 Exception Context
    5. report() 輔助函式與去重複（deduplicate）
    6. Log Level（例外分級）
    7. 忽略例外（dontReport、ShouldntReport、stopIgnoring）
    8. 渲染例外（render 方法自訂回應）
    9. JSON/HTML 回應與 shouldRenderJsonWhen
    10. respond() 全域自訂回應
    11. 例外類別內自訂 report/render 方法
    12. 節流與速率限制（throttle、Lottery、Limit）
    13. HTTP 例外與 abort 輔助函式
    14. 自訂 HTTP 錯誤頁面（404、500、fallback、vendor:publish）
--}}

{{-- ========================= --}}
{{-- # 1. 錯誤處理介紹與基本概念 --}}
{{-- ========================= --}}

{{--
    ## 什麼是錯誤處理？
    
    錯誤處理是應用程式中處理異常情況的機制，就像「安全網」一樣，當程式出現問題時，
    能夠優雅地處理並提供適當的回應，而不是讓整個應用程式崩潰。

    ## Laravel 錯誤處理的特點
    
    1. **開箱即用**：新專案建立時，錯誤處理已經配置完成
    2. **靈活配置**：可以自訂錯誤處理邏輯
    3. **環境適應**：開發和生產環境有不同的錯誤顯示策略
    4. **記錄完整**：自動記錄錯誤資訊供除錯使用

    ## 生活化比喻
    
    ### 錯誤處理就像「醫院急診室」
    - **預設配置**：就像醫院有基本的急診流程
    - **自訂處理**：可以根據不同病情制定特殊處理流程
    - **環境適應**：開發環境像「實習醫院」（詳細資訊），生產環境像「正式醫院」（保護隱私）
    - **記錄追蹤**：就像病歷記錄，方便後續追蹤和改進

    ### 錯誤處理就像「汽車安全系統」
    - **安全氣囊**：當發生碰撞時自動啟動保護
    - **故障燈**：提醒駕駛有問題需要處理
    - **備用系統**：主要系統故障時的替代方案
    - **維修手冊**：記錄問題供技師參考
--}}

{{-- ========================= --}}
{{-- # 2. 錯誤處理配置與設定 --}}
{{-- ========================= --}}

{{--
    ## 基本配置檔案
    
    ### bootstrap/app.php
    Laravel 11 的新配置方式：
    
    // 程式碼範例：
    // <?php
    // 
    // use Illuminate\Foundation\Application;
    // use Illuminate\Foundation\Configuration\Exceptions;
    // use Illuminate\Foundation\Configuration\Middleware;
    // 
    // return Application::configure(basePath: dirname(__DIR__))
    //     ->withRouting(
    //         web: __DIR__.'/../routes/web.php',
    //         commands: __DIR__.'/../routes/console.php',
    //         health: '/up',
    //     )
    //     ->withMiddleware(function (Middleware $middleware) {
    //         //
    //     })
        ->withExceptions(function (Exceptions $exceptions) {
            // 這裡配置錯誤處理邏輯
        })->create();

    ### config/app.php
    // 傳統配置檔案（Laravel 舊版）：
    
    // 程式碼範例：
    // <?php
    
    return [
        'debug' => env('APP_DEBUG', false),
        'env' => env('APP_ENV', 'production'),
        // ... 其他配置
    ];

    ## 環境變數設定
    
    ### .env 檔案
    開發環境
    APP_DEBUG=true
    APP_ENV=local
    
    生產環境
    APP_DEBUG=false
    APP_ENV=production

    ## 配置說明
    
    ### APP_DEBUG 設定
    - true：顯示詳細錯誤資訊（開發環境）
    - false：只顯示基本錯誤訊息（生產環境）
    
    ### 為什麼生產環境要關閉 DEBUG？
    - 安全性：避免暴露敏感資訊（資料庫密碼、檔案路徑等）
    - 用戶體驗：不讓用戶看到技術細節
    - 效能：減少錯誤處理的開銷
    - 專業性：提供統一的錯誤頁面

    ## 生活化比喻
    
    ### APP_DEBUG 就像「汽車儀表板」
    - 開發模式（true）：像技師模式，顯示所有技術細節
    - 生產模式（false）：像一般駕駛模式，只顯示必要資訊
    
    ### 環境配置就像「餐廳服務」
    - 開發環境：像廚房內部，可以看到所有製作過程
    - 生產環境：像用餐區，只看到精美的成品
--}}

{{-- ========================= --}}
{{-- # 3. 錯誤與例外的報告（Reporting Exceptions） --}}
{{-- ========================= --}}

{{--
    ### 什麼是 Exception 報告？
    報告（report）就是「記錄」或「上報」錯誤，可以寫進 log，也可以送到外部服務（如 Sentry、Flare）。
    Laravel 預設會根據 logging 設定自動記錄所有例外。

    #### 進階自訂報告
    你可以在 bootstrap/app.php 用 withExceptions 的 report 方法，針對特定例外自訂報告方式：

    // 例：自訂 InvalidOrderException 的報告
    use App\Exceptions\InvalidOrderException;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (InvalidOrderException $e) {
            // 這裡可以自訂 log、通知、外部上報等
        });
    })

    > 生活化比喻：
    > 報告就像「醫院遇到特殊病例時，會通知專科醫師或上報衛生局」。

    #### 停止預設 log 行為
    預設自訂 report callback 執行後，Laravel 還是會照 logging 設定記錄一次。
    如果你想「只自訂，不要預設 log」，可以：
    用 ->stop() 或 callback return false

    // 例：
    $exceptions->report(function (InvalidOrderException $e) {
        // ...
    })->stop();
    // 或
    $exceptions->report(function (InvalidOrderException $e) {
        return false;
    });

    #### reportable 例外
    你也可以在 Exception 類別內直接定義 report() 方法，Laravel 會自動呼叫。
--}}

{{-- ========================= --}}
{{-- # 4. 全域 Log Context 與 Exception Context --}}
{{-- ========================= --}}

{{--
    ### 全域 Log Context
    Laravel 會自動把目前登入用戶的 ID 加到每個 log（如果有登入）。
    你也可以用 context() 方法，加入自訂全域 context：

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->context(fn () => [
            'foo' => 'bar',
        ]);
    })

    ### 例外類別自訂 context
    有時候某個例外需要額外資訊，可以在 Exception 類別內加 context() 方法：

    // 例：
    class InvalidOrderException extends Exception {
        public function context(): array {
            return ['order_id' => $this->orderId];
        }
    }

    > 生活化比喻：
    > context 就像「病歷註記」，每次記錄都可以加上當下的特殊狀態。
--}}

{{-- ========================= --}}
{{-- # 5. report() 輔助函式與去重複（deduplicate） --}}
{{-- ========================= --}}

{{--
    ### report() 輔助函式
    有時你只想「記錄」例外但不中斷流程，可以用 report($e)：

    // 範例：
    public function isValid(string $value): bool {
        try {
            // 驗證邏輯...
        } catch (Throwable $e) {
            report($e); // 只記錄，不中斷
            return false;
        }
    }

    ### 去重複（deduplicate）
    如果同一個例外被 report 多次，會產生重複 log。
    可用 dontReportDuplicates()，只記錄第一次：

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReportDuplicates();
    })

    // 範例：
    $original = new RuntimeException('Whoops!');
    report($original); // 會記錄
    try {
        throw $original;
    } catch (Throwable $caught) {
        report($caught); // 不會重複記錄
    }
    report($original); // 不會重複記錄
    report($caught);   // 不會重複記錄

    > 生活化比喻：
    > deduplicate 就像「醫院同一個病人一天只掛一次號」，避免重複記錄。
--}}

{{-- ========================= --}}
{{-- # 6. Log Level（例外分級） --}}
{{-- ========================= --}}

{{--
    ### log level 介紹
    log 有分等級（debug/info/warning/error/critical...），影響訊息嚴重性與記錄管道。
    你可以針對特定例外自訂 log level：

    use PDOException;
    use Psr\Log\LogLevel;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->level(PDOException::class, LogLevel::CRITICAL);
    })

    ### Log Level 詳細分級說明

    #### 1. DEBUG（除錯）
    - 用途：開發時期的詳細資訊
    - 內容：變數值、函式呼叫、執行流程
    - 範例：SQL 查詢、API 請求參數、快取命中率
    - 處理方式：只在開發環境記錄，生產環境通常關閉
    - 生活化比喻：就像「醫生寫病歷」，記錄每個細節

    ```php
    // 範例：設定驗證例外為 DEBUG 等級
    $exceptions->level(ValidationException::class, LogLevel::DEBUG);
    ```

    #### 2. INFO（資訊）
    - 用途：一般資訊性訊息
    - 內容：正常操作記錄、狀態變更、用戶行為
    - 範例：用戶登入、訂單建立、檔案上傳
    - 處理方式：記錄但不緊急，用於統計分析
    - 生活化比喻：就像「醫院掛號」，記錄每個就診

    ```php
    // 範例：設定業務例外為 INFO 等級
    $exceptions->level(OrderCreatedException::class, LogLevel::INFO);
    ```

    #### 3. NOTICE（注意）
    - 用途：需要注意但不嚴重的情況
    - 內容：非預期但可處理的情況
    - 範例：使用過時的 API、效能警告、配置建議
    - 處理方式：記錄並可能需要後續關注
    - 生活化比喻：就像「醫生提醒」，需要注意但不緊急

    ```php
    // 範例：設定效能警告為 NOTICE 等級
    $exceptions->level(SlowQueryException::class, LogLevel::NOTICE);
    ```

    #### 4. WARNING（警告）
    - 用途：潛在問題或異常情況
    - 內容：可能導致問題但尚未造成錯誤
    - 範例：資料庫連線慢、記憶體使用率高、外部服務延遲
    - 處理方式：記錄並監控，可能需要人工介入
    - 生活化比喻：就像「急診室警告」，需要關注但不會立即致命

    ```php
    // 範例：設定驗證例外為 WARNING 等級
    $exceptions->level(ValidationException::class, LogLevel::WARNING);
    ```

    #### 5. ERROR（錯誤）
    - 用途：實際發生的錯誤
    - 內容：功能無法正常運作，但系統仍可運行
    - 範例：檔案讀取失敗、API 呼叫失敗、資料庫查詢錯誤
    - 處理方式：記錄並可能需要修復，影響用戶體驗
    - 生活化比喻：就像「急診室處理」，需要立即處理

    ```php
    // 範例：設定一般例外為 ERROR 等級
    $exceptions->level(Exception::class, LogLevel::ERROR);
    ```

    #### 6. CRITICAL（嚴重）
    - 用途：嚴重錯誤，可能影響系統穩定性
    - 內容：關鍵功能失效、資料遺失風險、安全問題
    - 範例：資料庫連線失敗、認證系統故障、支付處理錯誤
    - 處理方式：立即記錄並發送警報，需要緊急處理
    - 生活化比喻：就像「重症監護」，需要立即搶救

    ```php
    // 範例：設定資料庫例外為 CRITICAL 等級
    $exceptions->level(PDOException::class, LogLevel::CRITICAL);
    ```

    #### 7. ALERT（警報）
    - 用途：需要立即行動的嚴重問題
    - 內容：系統部分功能完全失效
    - 範例：整個資料庫離線、主要服務不可用
    - 處理方式：立即通知管理員，可能需要重啟服務
    - 生活化比喻：就像「火警警報」，需要立即疏散

    ```php
    // 範例：設定系統例外為 ALERT 等級
    $exceptions->level(SystemException::class, LogLevel::ALERT);
    ```

    #### 8. EMERGENCY（緊急）
    - 用途：系統完全無法運作
    - 內容：整個應用程式崩潰、無法恢復
    - 範例：伺服器硬碟故障、記憶體耗盡、核心服務崩潰
    - 處理方式：立即通知所有相關人員，可能需要重啟伺服器
    - 生活化比喻：就像「醫院停電」，整個系統癱瘓

    ```php
    // 範例：設定致命例外為 EMERGENCY 等級
    $exceptions->level(FatalErrorException::class, LogLevel::EMERGENCY);
    ```

    ### 實際應用範例

    ```php
    // 根據例外類型設定不同的 Log Level
    ->withExceptions(function (Exceptions $exceptions) {
        // 資料庫相關例外 - 嚴重等級
        $exceptions->level(PDOException::class, LogLevel::CRITICAL);
        $exceptions->level(QueryException::class, LogLevel::CRITICAL);
        
        // 驗證例外 - 警告等級
        $exceptions->level(ValidationException::class, LogLevel::WARNING);
        
        // 認證例外 - 錯誤等級
        $exceptions->level(AuthenticationException::class, LogLevel::ERROR);
        $exceptions->level(AuthorizationException::class, LogLevel::ERROR);
        
        // 業務邏輯例外 - 資訊等級
        $exceptions->level(InvalidOrderException::class, LogLevel::INFO);
        
        // 開發時期例外 - 除錯等級
        if (app()->environment('local')) {
            $exceptions->level(LogicException::class, LogLevel::DEBUG);
        }
    })
    ```

    ### 等級優先順序
    從低到高：DEBUG < INFO < NOTICE < WARNING < ERROR < CRITICAL < ALERT < EMERGENCY

    ### 配置建議
    - 開發環境：記錄 DEBUG 以上所有等級
    - 測試環境：記錄 INFO 以上等級
    - 生產環境：記錄 WARNING 以上等級
    - 監控系統：重點關注 ERROR 以上等級

    > 生活化比喻：
    > log level 就像「醫院分級」，從門診（DEBUG）到急診（ERROR）到重症監護（CRITICAL），
    > 根據嚴重程度分流處理，確保資源合理分配。
--}}

{{-- ========================= --}}
{{-- # 7. 忽略例外（dontReport、ShouldntReport、stopIgnoring） --}}
{{-- ========================= --}}

{{--
    ### dontReport 忽略例外
    有些例外你永遠不想記錄，可用 dontReport：

    use App\Exceptions\InvalidOrderException;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport([
            InvalidOrderException::class,
        ]);
    })

    ### ShouldntReport 介面
    也可以讓 Exception 實作 ShouldntReport 介面，Laravel 會自動忽略：

    use Illuminate\Contracts\Debug\ShouldntReport;
    class PodcastProcessingException extends Exception implements ShouldntReport {}

    ### stopIgnoring 取消忽略
    Laravel 內建會自動忽略 404/419 等例外。
    如果你想讓某些例外「不要被忽略」，可用 stopIgnoring：

    use Symfony\Component\HttpKernel\Exception\HttpException;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->stopIgnoring(HttpException::class);
    })

    > 生活化比喻：
    > dontReport/ShouldntReport 就像「醫院不記錄小感冒」，stopIgnoring 則是「這個特殊病例要記錄」。
--}}

{{-- ========================= --}}
{{-- # 8. 渲染例外（render 方法自訂回應） --}}
{{-- ========================= --}}

{{--
    ### 什麼是渲染例外？
    渲染（render）就是「把例外轉成 HTTP 回應」，決定用戶看到什麼頁面。

    ### 自訂渲染邏輯
    你可以用 render 方法，針對特定例外自訂回應：

    use App\Exceptions\InvalidOrderException;
    use Illuminate\Http\Request;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (InvalidOrderException $e, Request $request) {
            return response()->view('errors.invalid-order', status: 500);
        });
    })

    ### 覆蓋內建例外渲染
    也可以覆蓋 Laravel 內建的例外，如 NotFoundHttpException：

    use Illuminate\Http\Request;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Record not found.'
                ], 404);
            }
        });
    })

    > 生活化比喻：
    > render 就像「翻譯官」，把技術錯誤翻譯成用戶能理解的語言。
--}}

{{-- ========================= --}}
{{-- # 9. JSON/HTML 回應與 shouldRenderJsonWhen --}}
{{-- ========================= --}}

{{--
    ### 自動判斷回應格式
    Laravel 會根據請求的 Accept header 自動判斷要回 JSON 還是 HTML。

    ### 自訂判斷邏輯
    你可以用 shouldRenderJsonWhen 自訂判斷邏輯：

    use Illuminate\Http\Request;
    use Throwable;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('admin/*')) {
                return true; // admin 路徑都回 JSON
            }
    
            return $request->expectsJson(); // 其他看 Accept header
        });
    })

    > 生活化比喻：
    > shouldRenderJsonWhen 就像「接待員」，根據客人身份決定用什麼語言溝通。
--}}

{{-- ========================= --}}
{{-- # 10. respond() 全域自訂回應 --}}
{{-- ========================= --}}

{{--
    ### 全域回應自訂
    很少用到，但可以自訂整個 HTTP 回應：

    use Symfony\Component\HttpFoundation\Response;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }
    
            return $response;
        });
    })

    > 生活化比喻：
    > respond 就像「總經理」，可以修改任何對外的回應內容。
--}}

{{-- ========================= --}}
{{-- # 11. 例外類別內自訂 report/render 方法 --}}
{{-- ========================= --}}

{{--
    ### 在例外類別內定義方法
    除了在 bootstrap/app.php 配置，也可以直接在例外類別內定義：

    // <?php
    namespace App\Exceptions;
    
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    
    class InvalidOrderException extends Exception
    {
        /**
         * Report the exception.
         */
        public function report(): void
        {
            // 自訂報告邏輯
        }
    
        /**
         * Render the exception as an HTTP response.
         */
        public function render(Request $request): Response
        {
            return response(/* ... */);
        }
    }

    ### 條件性渲染
    如果例外繼承自已可渲染的例外，可以 return false 使用預設：

    public function render(Request $request): Response|bool
    {
        if (/* 判斷是否需要自訂渲染 */) {
            return response(/* ... */);
        }
    
        return false; // 使用預設渲染
    }

    ### 條件性報告
    也可以條件性決定是否報告：

    public function report(): bool
    {
        if (/* 判斷是否需要自訂報告 */) {
            // 自訂報告邏輯
            return true;
        }
    
        return false; // 使用預設報告
    }

    > 生活化比喻：
    > 例外類別內的方法就像「個人專屬處理流程」，每個例外都有自己的 SOP。
--}}

{{-- ========================= --}}
{{-- # 12. 節流與速率限制（throttle、Lottery、Limit） --}}
{{-- ========================= --}}

{{--
    ### 為什麼需要節流？
    當應用程式報告大量例外時，可能會：
    - 塞爆 log 檔案
    - 耗盡外部服務配額
    - 影響效能

    ### 隨機採樣（Lottery）
    用機率決定是否記錄例外：

    use Illuminate\Support\Lottery;
    use Throwable;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->throttle(function (Throwable $e) {
            return Lottery::odds(1, 1000); // 千分之一機率記錄
        });
    })

    ### 條件性採樣
    只對特定例外採樣：

    use App\Exceptions\ApiMonitoringException;
    use Illuminate\Support\Lottery;
    use Throwable;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->throttle(function (Throwable $e) {
            if ($e instanceof ApiMonitoringException) {
                return Lottery::odds(1, 1000);
            }
        });
    })

    ### 速率限制（Limit）
    限制每分鐘記錄數量：

    use Illuminate\Broadcasting\BroadcastException;
    use Illuminate\Cache\RateLimiting\Limit;
    use Throwable;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->throttle(function (Throwable $e) {
            if ($e instanceof BroadcastException) {
                return Limit::perMinute(300); // 每分鐘最多 300 次
            }
        });
    })

    ### 自訂限制鍵值
    預設用例外類別當鍵值，可以自訂：

    use Illuminate\Broadcasting\BroadcastException;
    use Illuminate\Cache\RateLimiting\Limit;
    use Throwable;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->throttle(function (Throwable $e) {
            if ($e instanceof BroadcastException) {
                return Limit::perMinute(300)->by($e->getMessage());
            }
        });
    })

    ### 混合使用
    可以同時使用 Lottery 和 Limit：

    use App\Exceptions\ApiMonitoringException;
    use Illuminate\Broadcasting\BroadcastException;
    use Illuminate\Cache\RateLimiting\Limit;
    use Illuminate\Support\Lottery;
    use Throwable;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->throttle(function (Throwable $e) {
            return match (true) {
                $e instanceof BroadcastException => Limit::perMinute(300),
                $e instanceof ApiMonitoringException => Lottery::odds(1, 1000),
                default => Limit::none(),
            };
        });
    })

    > 生活化比喻：
    > throttle 就像「交通管制」，避免例外「塞車」影響系統效能。
--}}

{{-- ========================= --}}
{{-- # 13. HTTP 例外與 abort 輔助函式 --}}
{{-- ========================= --}}

{{--
    ### HTTP 例外介紹
    有些例外描述 HTTP 錯誤碼，如 404（找不到頁面）、401（未授權）、500（伺服器錯誤）。

    ### abort 輔助函式
    從應用程式任何地方產生 HTTP 錯誤回應：

    // 基本用法
    abort(404); // 產生 404 錯誤

    // 帶訊息
    abort(403, 'Unauthorized action.');

    // 帶自訂標題
    abort(404, 'Page not found.', ['title' => 'Custom Title']);

    ### 常見 HTTP 狀態碼
    - 400：Bad Request（請求錯誤）
    - 401：Unauthorized（未授權）
    - 403：Forbidden（禁止訪問）
    - 404：Not Found（找不到）
    - 419：Page Expired（頁面過期，CSRF 錯誤）
    - 422：Unprocessable Entity（驗證錯誤）
    - 429：Too Many Requests（請求過多）
    - 500：Internal Server Error（伺服器錯誤）
    - 503：Service Unavailable（服務不可用）

    ### 實際應用範例
    // 在控制器中
    public function show($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            abort(404, 'User not found.');
        }
        
        if (!auth()->user()->can('view', $user)) {
            abort(403, 'You cannot view this user.');
        }
        
        return view('users.show', compact('user'));
    }

    > 生活化比喻：
    > abort 就像「緊急按鈕」，當遇到無法處理的情況時，立即停止並顯示適當的錯誤訊息。
--}}

{{-- ========================= --}}
{{-- # 14. 自訂 HTTP 錯誤頁面 --}}
{{-- ========================= --}}

{{--
    ### 自訂錯誤頁面
    Laravel 可以為不同 HTTP 狀態碼建立自訂錯誤頁面。

    ### 建立錯誤頁面
    在 resources/views/errors/ 目錄下建立對應的 Blade 檔案：

    // 404 錯誤頁面：resources/views/errors/404.blade.php
    <!DOCTYPE html>
    <html>
    <head>
        <title>Page Not Found</title>
    </head>
    <body>
        <h1>404 - Page Not Found</h1>
        <p>{{ $exception->getMessage() }}</p>
        <a href="{{ url('/') }}">Back to Home</a>
    </body>
    </html>

    // 500 錯誤頁面：resources/views/errors/500.blade.php
    <!DOCTYPE html>
    <html>
    <head>
        <title>Server Error</title>
    </head>
    <body>
        <h1>500 - Server Error</h1>
        <p>Something went wrong. Please try again later.</p>
        <a href="{{ url('/') }}">Back to Home</a>
    </body>
    </html>

    ### 錯誤頁面變數
    錯誤頁面可以存取以下變數：
    - $exception：例外物件
    - $exception->getMessage()：錯誤訊息
    - $exception->getCode()：錯誤代碼

    ### 發佈預設錯誤頁面
    可以發佈 Laravel 預設的錯誤頁面範本：

    // 終端機指令：
    php artisan vendor:publish --tag=laravel-errors

    ### 後備錯誤頁面（Fallback）
    可以建立通用的後備頁面：

    // 4xx 錯誤後備頁面：resources/views/errors/4xx.blade.php
    <!DOCTYPE html>
    <html>
    <head>
        <title>Client Error</title>
    </head>
    <body>
        <h1>Client Error</h1>
        <p>Something went wrong with your request.</p>
        <a href="{{ url('/') }}">Back to Home</a>
    </body>
    </html>

    // 5xx 錯誤後備頁面：resources/views/errors/5xx.blade.php
    <!DOCTYPE html>
    <html>
    <head>
        <title>Server Error</title>
    </head>
    <body>
        <h1>Server Error</h1>
        <p>Something went wrong on our end.</p>
        <a href="{{ url('/') }}">Back to Home</a>
    </body>
    </html>

    ### 注意事項
    - 404、500、503 不會使用後備頁面，Laravel 有內建專用頁面
    - 要自訂這些頁面，需要建立對應的 404.blade.php、500.blade.php、503.blade.php
    - 錯誤頁面應該簡潔、友善，避免技術細節

    ### 實際應用範例
    // 美觀的 404 頁面範例
    // <!DOCTYPE html>
    // <html lang="zh-TW">
    // <head>
    //     <meta charset="UTF-8">
    //     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    //     <title>找不到頁面 - 404</title>
    //     <style>
    //         body {
    //             font-family: 'Arial', sans-serif;
    //             background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    //             margin: 0;
    //             padding: 0;
    //             display: flex;
    //             justify-content: center;
    //             align-items: center;
    //             min-height: 100vh;
    //         }
    //         .error-container {
    //             text-align: center;
    //             color: white;
    //             padding: 2rem;
    //         }
    //         .error-code {
    //             font-size: 8rem;
    //             font-weight: bold;
    //             margin: 0;
    //             text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    //         }
    //         .error-message {
    //             font-size: 1.5rem;
    //             margin: 1rem 0;
    //         }
    //         .home-link {
    //             display: inline-block;
    //             background: rgba(255,255,255,0.2);
    //             color: white;
    //             text-decoration: none;
    //             padding: 1rem 2rem;
    //             border-radius: 50px;
    //             margin-top: 2rem;
    //             transition: background 0.3s;
    //         }
    //         .home-link:hover {
    //             background: rgba(255,255,255,0.3);
    //         }
    //     </style>
    // </head>
    // <body>
    //     <div class="error-container">
    //         <h1 class="error-code">404</h1>
    //         <p class="error-message">糟糕！找不到您要的頁面</p>
    //         <p>這個頁面可能已經被移除、重新命名，或者暫時無法使用。</p>
    //         <a href="{{ url('/') }}" class="home-link">回到首頁</a>
    //     </div>
    // </body>
    // </html>

    > 生活化比喻：
    > 自訂錯誤頁面就像「客製化道歉信」，當服務出問題時，用友善的方式向用戶說明情況。
--}}

{{-- ========================= --}}
{{-- # 最佳實踐與總結 --}}
{{-- ========================= --}}

{{--
    ## 錯誤處理最佳實踐

    ### 1. 開發環境 vs 生產環境
    - 開發環境：開啟詳細錯誤資訊，方便除錯
    - 生產環境：關閉詳細資訊，保護敏感資料

    ### 2. 錯誤分類與處理
    - 用戶錯誤：400、401、403、404、422 等
    - 系統錯誤：500、503 等
    - 網路錯誤：429、502 等

    ### 3. 記錄策略
    - 重要錯誤：完整記錄，包含 context
    - 一般錯誤：基本記錄
    - 預期錯誤：可忽略或輕量記錄

    ### 4. 用戶體驗
    - 友善訊息：避免技術術語
    - 明確指引：告訴用戶下一步該做什麼
    - 一致設計：錯誤頁面風格統一

    ### 5. 監控與警報
    - 即時監控：使用 Sentry、Flare 等工具
    - 錯誤統計：定期分析錯誤趨勢
    - 自動警報：重要錯誤即時通知

    ## 生活化總結

    ### 錯誤處理就像「完整的醫療體系」
    1. 急診室：立即處理緊急情況
    2. 分診系統：根據嚴重程度分流
    3. 專科醫師：針對特定問題專業處理
    4. 病歷記錄：完整記錄所有資訊
    5. 康復指導：提供後續建議和指引

    ### 錯誤處理就像「智慧交通系統」
    1. 交通號誌：控制流量，避免塞車
    2. 替代道路：主要道路故障時的備案
    3. 即時資訊：告知駕駛路況和建議
    4. 事故處理：快速處理和恢復
    5. 預防措施：定期維護和改進

    ## 結語

    Laravel 的錯誤處理系統提供了完整、靈活且強大的錯誤管理機制。
    透過適當的配置和自訂，可以建立穩定、友善且易於維護的應用程式。
    記住：好的錯誤處理不僅是技術問題，更是用戶體驗的重要組成部分。
--}}
    //     throw $original;
    // } catch (Throwable $caught) {
    //     report($caught); // 不會重複記錄
    // }
    // report($original); // 不會重複記錄
    // report($caught);   // 不會重複記錄

    > 生活化比喻：
    > deduplicate 就像「醫院同一個病人一天只掛一次號」，避免重複記錄。
--}}
