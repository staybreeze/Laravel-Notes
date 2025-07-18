<?php

/**
 * UserController
 *
 * [設計理念]
 * - 控制器（Controller）用於將相關的請求處理邏輯集中於單一類別，提升程式結構的可維護性與可讀性。
 * - 例如 UserController 可統一處理所有與使用者相關的顯示、建立、更新、刪除等請求。
 *
 * [目錄結構]
 * - 所有控制器預設存放於 app/Http/Controllers 目錄下。
 *
 * [Artisan 指令快速產生]
 *   php artisan make:controller UserController
 *
 * [基本範例]
 *   // 顯示指定使用者的個人資料
 *   public function show(string $id): View {
 *       return view('user.profile', [
 *           'user' => User::findOrFail($id)
 *       ]);
 *   }
 *
 * [路由綁定控制器方法]
 *   use App\Http\Controllers\UserController;
 *   Route::get('/user/{id}', [UserController::class, 'show']);
 *   // 當請求符合 URI，會自動呼叫 UserController 的 show 方法，並將路由參數傳入。
 *
 * [Controller Middleware 實作說明]
 * - 可於路由層指定 middleware（推薦小型專案/單一 action）
 * - 可於控制器內實作 HasMiddleware 介面，集中管理多 action 共用 middleware
 * - 支援 only/except 條件、Closure middleware
 * - 建議團隊統一用法並加上用途註解
 *
 * [團隊建議]
 * - 控制器不一定要繼承 base class，但繼承 Controller 可共用通用方法。
 * - 建議團隊將同類型邏輯集中於同一控制器，並加上中英文註解，方便維護。
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;
use Closure;
use App\Repositories\UserRepository;

class UserController extends Controller implements HasMiddleware
{
    /**
     * [依賴注入範例]
     * 建構子注入：自動注入 UserRepository 實例
     * 適合所有 action 都會用到的服務
     *
     * @param UserRepository $users 使用者資料倉儲
     */
    public function __construct(
        protected UserRepository $users,
    ) {}

    /**
     * 指定該控制器要套用的 middleware
     * - 'auth'：所有 action 都需驗證登入
     * - 'log'：僅 index action 需記錄日誌
     * - 'subscribed'：除了 store 以外都需檢查訂閱
     * - Closure middleware：可用於臨時、簡單邏輯
     * Laravel 會自動去找這個控制器有沒有 middleware() 方法，有的話就把裡面設定的 middleware 套用到對應的 action。
     * action 就是 Controller 裡面的一個「方法」，每個方法對應一種「動作」或「功能」。
     */
    public static function middleware(): array
    {
        return [
            'auth', // 所有 action 都會套用 'auth' middleware（必須登入）
            new Middleware('log', only: ['index']), // 只有 index action 會套用 'log' middleware（僅記錄列表頁的日誌）
            new Middleware('subscribed', except: ['store']), // 除了 store action 以外都會套用 'subscribed' middleware（檢查訂閱狀態）
            // 內聯 Closure middleware 範例
            function (Request $request, Closure $next) {
                // ... inline middleware 邏輯 ...
                // 這裡可以寫臨時、簡單的 middleware 處理邏輯
                return $next($request); // 放行請求，繼續往下個 middleware 或 controller 執行
            },
        ];
    }

    /**
     * 顯示指定使用者的個人資料
     */
    public function showUser(string $id): View
    {
        return view('user.profile', [
            'user' => User::findOrFail($id)
        ]);
    }

    /**
     * [方法依賴注入範例]
     * 方法注入：自動注入 Request 實例
     * 適合僅特定 action 需要的依賴
     *
     * @param Request $request 目前 HTTP 請求物件，Laravel 會自動注入
     * @return RedirectResponse 重導回 /users 頁面
     *
     * 範例：取得表單欄位 $name = $request->input('name');
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        // 取得表單欄位 name
        $name = $request->input('name');
        // 儲存使用者...
        return redirect('/users');
    }

    /**
     * [依賴注入與路由參數順序範例]
     *
     * 若 controller 方法同時需要依賴注入（如 Request）與路由參數（如 id），
     * 應將依賴注入參數寫在前面，路由參數寫在後面。
     *
     * 例如：Route::put('/user/{id}', [UserController::class, 'update']);
     *
     * @param Request $request 目前 HTTP 請求物件，Laravel 會自動注入
     * @param string $id 路由參數 {id}，自動帶入
     * @return RedirectResponse 重導回 /users 頁面
     */
    public function update(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        // 這裡 $request 會自動注入，$id 會自動帶入路由參數
        // 更新使用者...
        return redirect('/users');
    }

    /**
     * [Request Header 操作範例]
     * 示範如何取得自訂 header、判斷 header 是否存在、取得 Bearer Token
     *
     * 路由綁定範例：
     * Route::get('/user/header-demo', [UserController::class, 'demoHeaderExample']);
     *
     * @param Request $request 目前 HTTP 請求物件
     * @return \Illuminate\Http\JsonResponse 回傳 header 相關資訊
     */
    public function demoHeaderExample(Request $request)
    {
        // 取得自訂標頭 'X-Header-Name'，若不存在則回傳 null
        $headerValue = $request->header('X-Header-Name');

        // 取得自訂標頭 'X-Header-Name'，若不存在則回傳 'default'
        $headerValueWithDefault = $request->header('X-Header-Name', 'default');

        // 判斷是否有 'X-Header-Name' 這個標頭
        $hasHeader = $request->hasHeader('X-Header-Name');

        // 取得 Authorization 標頭中的 Bearer Token
        // Bearer Token 是一種「持有者令牌」：只要你持有這個 token，就代表你有權限存取 API。
        $bearerToken = $request->bearerToken();

        // 回傳測試結果
        return response()->json([
            'headerValue' => $headerValue,
            'headerValueWithDefault' => $headerValueWithDefault,
            'hasHeader' => $hasHeader,
            'bearerToken' => $bearerToken,
        ]);
    }

    /**
     * [Request IP 取得範例]
     * 示範如何取得用戶端 IP 及所有代理轉發的 IP 陣列
     *
     * 路由綁定範例：
     * Route::get('/user/ip-demo', [UserController::class, 'showIpExample']);
     *
     * @param Request $request 目前 HTTP 請求物件
     * @return \Illuminate\Http\JsonResponse 回傳 IP 相關資訊
     *
     * 安全提醒：IP 位址可被偽造、代理、NAT 轉換，僅供參考，不能作為唯一身分驗證依據。
     */
    public function showIpExample(Request $request)
    {
        // 取得單一 IP 位址（最接近 Laravel 的用戶端 IP，可能是代理 IP）
        $ip = $request->ip();

        // 取得所有經過代理的 IP 位址陣列，最原始用戶端 IP 在最後一個
        $ips = $request->ips();

        // 回傳 JSON 結果
        return response()->json([
            'ip' => $ip,
            'ips' => $ips,
        ]);
    }

    /**
     * [Content Negotiation（內容協商）範例]
     * 示範如何檢查 Accept header、判斷用戶端可接受的內容型態
     *
     * 路由綁定範例：
     * Route::get('/user/content-demo', [UserController::class, 'contentNegotiationExample']);
     *
     * @param Request $request 目前 HTTP 請求物件
     * @return \Illuminate\Http\JsonResponse 回傳內容協商相關資訊
     */
    public function contentNegotiationExample(Request $request)
    {
        // 取得所有用戶端可接受的 content-type 陣列（依照 Accept header）
        $contentTypes = $request->getAcceptableContentTypes();

        // 判斷用戶端是否接受 text/html 或 application/json 其中一種
        $acceptsHtmlOrJson = $request->accepts(['text/html', 'application/json']);

        // 取得用戶端最偏好的 content-type（若都不接受則回傳 null）
        $preferred = $request->prefers(['text/html', 'application/json']);

        // 判斷用戶端是否明確期望 JSON 回應
        $expectsJson = $request->expectsJson();

        // 回傳 JSON 結果
        return response()->json([
            'contentTypes' => $contentTypes,
            'acceptsHtmlOrJson' => $acceptsHtmlOrJson,
            'preferred' => $preferred,
            'expectsJson' => $expectsJson,
        ]);
    }

    /**
     * [PSR-7 Request 實作範例]
     * 示範如何在 Controller 方法中 type-hint PSR-7 request 並取得資訊
     * PSR-7 是 PHP-FIG（PHP Framework Interop Group）制定的「HTTP訊息介面標準」。
     * PSR-7 定義了 HTTP 請求（Request）與回應（Response）等物件的統一介面，讓不同 PHP 框架/套件可以互通。
     * PSR-7 Request 代表一個「不可變」的 HTTP 請求物件，包含 method、uri、headers、body 等資訊。
     *
     * 路由綁定範例：
     * Route::get('/user/psr7-demo', [UserController::class, 'psr7Example']);
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR-7 標準的 request 物件
     * @return \Illuminate\Http\JsonResponse 回傳部分 PSR-7 request 資訊
     *
     * 注意：需先安裝 symfony/psr-http-message-bridge 與 nyholm/psr7 套件
     * composer require symfony/psr-http-message-bridge nyholm/psr7
     */
    public function psr7Example(\Psr\Http\Message\ServerRequestInterface $request)
    {
        // 取得 PSR-7 request 的 method、uri、headers
        $method = $request->getMethod();
        $uri = (string)$request->getUri();
        $headers = $request->getHeaders();

        return response()->json([
            'method' => $method,
            'uri' => $uri,
            'headers' => $headers,
        ]);
    }

    /**
     * [Request Input 取得範例]
     * 示範如何取得各種輸入資料（all、collect、input、query、json、string、integer、boolean、array、date、enum...）
     *
     * 路由綁定範例：
     * Route::match(['get', 'post'], '/user/input-demo', [UserController::class, 'inputExample']);
     *
     * @param Request $request 目前 HTTP 請求物件
     * @return \Illuminate\Http\JsonResponse 回傳各種 input 取得結果
     */
    public function inputExample(Request $request)
    {
        // =====================================================================
        // Request Input 取得方法詳細說明
        // =====================================================================

        // ---------------------------------------------------------------------
        // 1. all() - 取得所有輸入資料（array）
        // ---------------------------------------------------------------------
        // 用途：取得所有輸入資料，包含 POST、GET、JSON 等所有來源
        // 回傳：array - 包含所有輸入資料的陣列
        // 適用：需要處理所有輸入資料時，如日誌記錄、資料驗證等
        $all = $request->all();

        // ---------------------------------------------------------------------
        // 2. collect() - 取得所有輸入資料（Collection）
        // ---------------------------------------------------------------------
        // 用途：取得所有輸入資料，但回傳 Laravel Collection 物件
        // 回傳：Collection - 可進行鏈式操作（map、filter、each 等）
        // 適用：需要對輸入資料進行複雜處理時，如資料轉換、過濾等
        $allCollection = $request->collect();

        // ---------------------------------------------------------------------
        // 3. collect('key') - 取得特定鍵值的 Collection
        // ---------------------------------------------------------------------
        // 用途：取得特定鍵值的資料，並轉為 Collection 物件
        // 回傳：Collection - 該鍵值對應的資料集合
        // 適用：處理陣列資料時，如多選表單、巢狀資料等
        $users = $request->collect('users');

        // ---------------------------------------------------------------------
        // 4. input('key', 'default') - 取得單一輸入值
        // ---------------------------------------------------------------------
        // 用途：取得指定鍵值的輸入資料，支援點記法存取巢狀資料
        // 回傳：mixed - 對應的輸入值，若不存在則回傳 null 或預設值
        // 參數：
        //   - key: 要取得的鍵值，支援點記法（如 'user.name'）
        //   - default: 當鍵值不存在時的回傳值（可選）
        $name = $request->input('name');                    // 取得 name 欄位
        $nameWithDefault = $request->input('name', 'Sally'); // 若不存在則回傳 'Sally'

        // ---------------------------------------------------------------------
        // 5. input('array.*.key') - 點記法存取巢狀陣列
        // ---------------------------------------------------------------------
        // 用途：使用點記法存取巢狀陣列中的特定資料
        // 語法：
        //   - 'products.0.name': 取得 products 陣列中第 0 個元素的 name 屬性
        //   - 'products.*.name': 取得 products 陣列中所有元素的 name 屬性（回傳陣列）
        $productName = $request->input('products.0.name');   // 取得第一個產品的名稱
        $productNames = $request->input('products.*.name');  // 取得所有產品名稱陣列

        // ---------------------------------------------------------------------
        // 6. input() - 取得所有輸入資料（array）
        // ---------------------------------------------------------------------
        // 用途：不傳參數時，等同於 all() 方法
        // 回傳：array - 所有輸入資料
        $allInput = $request->input();

        // ---------------------------------------------------------------------
        // 7. query('key', 'default') - 只取得 Query String 參數
        // ---------------------------------------------------------------------
        // 用途：只從 URL 查詢字串（GET 參數）取得資料，不包含 POST 資料
        // 回傳：mixed - 對應的查詢參數值
        // 適用：處理 GET 請求、分頁參數、搜尋條件等
        $queryName = $request->query('name');                    // 取得 ?name=value 中的 value
        $queryNameDefault = $request->query('name', 'Helen');    // 若不存在則回傳 'Helen'
        $allQuery = $request->query();                           // 取得所有查詢參數

        // ---------------------------------------------------------------------
        // 8. input('user.name') - 取得 JSON 輸入中的巢狀資料
        // ---------------------------------------------------------------------
        // 用途：從 JSON 請求中取得巢狀物件資料
        // 適用：API 開發、AJAX 請求等 JSON 格式資料
        $jsonUserName = $request->input('user.name'); // 從 {"user": {"name": "John"}} 取得 "John"

        // ---------------------------------------------------------------------
        // 9. string('key') - 取得字串型別的輸入值
        // ---------------------------------------------------------------------
        // 用途：確保取得字串型別的輸入值，並可進行字串操作
        // 回傳：Stringable - Laravel 的 Stringable 物件，支援鏈式字串操作
        // 適用：需要對輸入字串進行處理時，如去除空白、轉換大小寫等
        $stringName = $request->string('name')->trim(); // 取得 name 並去除前後空白

        // ---------------------------------------------------------------------
        // 10. integer('key', 'default', 'min', 'max') - 取得整數型別的輸入值
        // ---------------------------------------------------------------------
        // 用途：確保取得整數型別的輸入值，並可設定預設值、最小值、最大值
        // 回傳：int - 整數值
        // 參數：
        //   - key: 要取得的鍵值
        //   - default: 預設值（當鍵值不存在或無法轉換時）
        //   - min: 最小值（可選）
        //   - max: 最大值（可選）
        // 適用：分頁、數量限制、ID 等需要整數的場景
        $perPage = $request->integer('per_page'); // 取得分頁大小，若無法轉換則回傳 0

        // ---------------------------------------------------------------------
        // 11. boolean('key') - 取得布林型別的輸入值
        // ---------------------------------------------------------------------
        // 用途：將輸入值轉換為布林值
        // 回傳：bool - 布林值
        // 轉換規則：
        //   - true: "1", "true", "on", "yes", 1, true
        //   - false: "0", "false", "off", "no", 0, false, null, ""
        // 適用：開關設定、狀態標記等
        $archived = $request->boolean('archived'); // 取得是否已封存

        // ---------------------------------------------------------------------
        // 12. array('key') - 取得陣列型別的輸入值
        // ---------------------------------------------------------------------
        // 用途：確保取得陣列型別的輸入值
        // 回傳：array - 陣列值，若輸入不是陣列則回傳空陣列
        // 適用：多選表單、標籤、權限等陣列資料
        $versions = $request->array('versions'); // 取得版本陣列

        // ---------------------------------------------------------------------
        // 13. date('key', 'format', 'timezone') - 取得日期型別的輸入值
        // ---------------------------------------------------------------------
        // 用途：將輸入值轉換為 Carbon 日期物件
        // 回傳：Carbon|null - Carbon 日期物件或 null
        // 參數：
        //   - key: 要取得的鍵值
        //   - format: 日期格式（可選，預設自動偵測）
        //   - timezone: 時區（可選，預設使用應用程式時區）
        // 適用：生日、建立時間、到期日等日期處理
        $birthday = $request->date('birthday'); // 取得生日日期
        $elapsed = $request->date('elapsed', '!H:i', 'Europe/Madrid'); // 取得特定格式的時間

        // ---------------------------------------------------------------------
        // 14. enum('key', EnumClass, 'default') - 取得列舉型別的輸入值
        // ---------------------------------------------------------------------
        // 用途：將輸入值轉換為指定的列舉型別
        // 回傳：Enum|null - 列舉值或 null
        // 參數：
        //   - key: 要取得的鍵值
        //   - EnumClass: 列舉類別
        //   - default: 預設值（可選）
        // 適用：狀態管理、類型分類等有固定選項的場景
        // 注意：需要先定義對應的 Enum 類別
        // use App\Enums\Status;
        // $status = $request->enum('status', Status::class); // 取得狀態列舉
        // $statusWithDefault = $request->enum('status', Status::class, Status::Pending); // 帶預設值
        // use App\Enums\Product;
        // $productsEnum = $request->enums('products', Product::class); // 取得產品陣列列舉

        // ---------------------------------------------------------------------
        // 15. 動態屬性存取 - $request->key
        // ---------------------------------------------------------------------
        // 用途：使用物件屬性語法直接存取輸入值
        // 回傳：mixed - 對應的輸入值
        // 適用：簡潔的程式碼寫法，適合簡單的輸入存取
        // 注意：若鍵值不存在會回傳 null，不會拋出錯誤
        $dynamicName = $request->name; // 等同於 $request->input('name')

        // ---------------------------------------------------------------------
        // 16. only(['key1', 'key2']) - 只取得指定的輸入值
        // ---------------------------------------------------------------------
        // 用途：只取得指定的輸入欄位，其他欄位會被忽略
        // 回傳：array - 只包含指定欄位的陣列
        // 適用：需要過濾敏感資料、只處理特定欄位等場景
        $onlyInput = $request->only(['username', 'password']); // 只取得使用者名稱和密碼

        // ---------------------------------------------------------------------
        // 17. except('key') - 排除指定的輸入值
        // ---------------------------------------------------------------------
        // 用途：取得除了指定欄位外的所有輸入值
        // 回傳：array - 排除指定欄位的陣列
        // 適用：需要排除敏感資料、隱藏欄位等場景
        $exceptInput = $request->except('credit_card'); // 取得除了信用卡號外的所有資料

        return response()->json([
            'all' => $all,
            'allCollection' => $allCollection,
            'users' => $users,
            'name' => $name,
            'nameWithDefault' => $nameWithDefault,
            'productName' => $productName,
            'productNames' => $productNames,
            'allInput' => $allInput,
            'queryName' => $queryName,
            'queryNameDefault' => $queryNameDefault,
            'allQuery' => $allQuery,
            'jsonUserName' => $jsonUserName,
            'stringName' => $stringName,
            'perPage' => $perPage,
            'archived' => $archived,
            'versions' => $versions,
            'birthday' => $birthday,
            'elapsed' => $elapsed,
            // 'status' => $status,
            // 'statusWithDefault' => $statusWithDefault,
            // 'productsEnum' => $productsEnum,
            'dynamicName' => $dynamicName,
            'onlyInput' => $onlyInput,
            'exceptInput' => $exceptInput,
        ]);
    }

    /**
     * [Request Input Presence 取得與合併範例]
     * 示範如何判斷 input 是否存在、是否為空、合併 input 等
     *
     * 路由綁定範例：
     * Route::match(['get', 'post'], '/user/input-presence-demo', [UserController::class, 'inputPresenceExample']);
     *
     * @param Request $request 目前 HTTP 請求物件
     * @return \Illuminate\Http\JsonResponse 回傳 presence 相關測試結果
     */
    public function inputPresenceExample(Request $request)
    {
        // has：判斷單一 input 是否存在（即使為空字串也算存在）
        $hasName = $request->has('name');
        // has：判斷多個 input 是否都存在
        $hasNameEmail = $request->has(['name', 'email']);
        // hasAny：判斷多個 input 是否有任一存在
        $hasAny = $request->hasAny(['name', 'email']);

        // whenHas：若 input 存在則執行第一個 closure，不存在則執行第二個 closure
        $whenHasResult = null;
        $request->whenHas('name', function ($input) use (&$whenHasResult) {
            $whenHasResult = "name 存在，值為：$input";
        }, function () use (&$whenHasResult) {
            $whenHasResult = "name 不存在";
        });

        // filled：判斷 input 是否存在且不為空字串
        $filledName = $request->filled('name');
        // isNotFilled：判斷 input 是否不存在或為空字串
        $isNotFilledName = $request->isNotFilled('name');
        // isNotFilled（多個）：判斷多個 input 是否都不存在或為空字串
        $isNotFilledAll = $request->isNotFilled(['name', 'email']);
        // anyFilled：判斷多個 input 是否有任一存在且不為空字串
        $anyFilled = $request->anyFilled(['name', 'email']);

        // whenFilled：若 input 存在且不為空字串則執行第一個 closure，否則執行第二個 closure
        $whenFilledResult = null;
        $request->whenFilled('name', function ($input) use (&$whenFilledResult) {
            $whenFilledResult = "name 有值，值為：$input";
        }, function () use (&$whenFilledResult) {
            $whenFilledResult = "name 沒有值或為空字串";
        });

        // missing：判斷 input 是否不存在
        $missingName = $request->missing('name');
        // whenMissing：若 input 不存在則執行第一個 closure，否則執行第二個 closure
        $whenMissingResult = null;
        $request->whenMissing('name', function () use (&$whenMissingResult) {
            $whenMissingResult = "name 缺少";
        }, function () use (&$whenMissingResult) {
            $whenMissingResult = "name 存在";
        });

        // merge：強制合併新 input（若 key 已存在會覆蓋）
        $request->merge(['votes' => 0]);
        // mergeIfMissing：僅在 key 不存在時才合併
        $request->mergeIfMissing(['votes' => 1]);
        // 取得合併後的 votes 值
        $votes = $request->input('votes');

        return response()->json([
            'hasName' => $hasName,
            'hasNameEmail' => $hasNameEmail,
            'hasAny' => $hasAny,
            'whenHasResult' => $whenHasResult,
            'filledName' => $filledName,
            'isNotFilledName' => $isNotFilledName,
            'isNotFilledAll' => $isNotFilledAll,
            'anyFilled' => $anyFilled,
            'whenFilledResult' => $whenFilledResult,
            'missingName' => $missingName,
            'whenMissingResult' => $whenMissingResult,
            'votes' => $votes,
        ]);
    }

    /**
     * [什麼是舊輸入（Old Input）？]
     * -----------------------------------------------------------------------------
     * - 舊輸入不是「舊時代的寫法」，而是指「上一次請求時用戶填寫的表單資料」。
     * - Laravel 會暫時把這些資料存在 session，讓你在下次請求時可以自動回填表單欄位。
     * - 最常見於「表單驗證失敗」時，讓使用者不用重填剛剛輸入的資料。
     * - 當你送出表單（POST），如果驗證失敗，Laravel 會把你剛剛填的資料「暫存」到 session，然後重導回表單頁。
     * - 你回到表單頁時，可以用 old('欄位名') 或 $request->old('欄位名') 取得剛剛填過的值，自動回填到 input 欄位。
     * - 好處：提升用戶體驗（表單驗證失敗時不用重填）、可選擇只回填非敏感欄位（如不回填密碼）。
     * -----------------------------------------------------------------------------
     * [Old Input（舊輸入）範例]
     * 示範如何將 input 暫存到 session、重導時帶回、以及如何取得 old input
     *
     * 路由綁定範例：
     * Route::post('/user/old-input-demo', [UserController::class, 'oldInputExample']);
     * Route::get('/user/old-input-demo', [UserController::class, 'showOldInputForm']);
     *
     * @param Request $request 目前 HTTP 請求物件
     * @return \Illuminate\Http\RedirectResponse
     */
    public function oldInputExample(Request $request)
    {
        // flash：將所有 input 存到 session，供下次請求使用（通常用於表單驗證失敗時回填）
        $request->flash();
        // flashOnly：只存指定欄位（如只存 username、email，不存密碼等敏感資料）
        // $request->flashOnly(['username', 'email']);
        // flashExcept：存除指定欄位以外的所有欄位（如排除密碼）
        // $request->flashExcept('password');

        // =====================================================================
        // withInput() 方法詳細說明
        // =====================================================================
        
        // ---------------------------------------------------------------------
        // 方法一：withInput() - 將所有輸入資料存到 session
        // ---------------------------------------------------------------------
        // 用途：重導時自動將「所有」輸入資料暫存到 session，供下次請求使用
        // 回傳：RedirectResponse - 重導回應物件
        // 適用：表單驗證失敗時，需要回填所有欄位（除了敏感資料）
        // 注意：會將所有 POST 資料都存到 session，包含密碼等敏感資料
        // 前端使用：在 Blade 模板中用 old('欄位名') 取得暫存的資料
        return redirect('/user/old-input-demo')->withInput();
        
        // ---------------------------------------------------------------------
        // 方法二：withInput($request->except('password')) - 排除敏感資料
        // ---------------------------------------------------------------------
        // 用途：重導時只將「部分」輸入資料暫存到 session，排除敏感欄位
        // 回傳：RedirectResponse - 重導回應物件
        // 參數：array - 要暫存的資料陣列（通常用 except() 排除敏感欄位）
        // 適用：表單驗證失敗時，需要回填大部分欄位，但排除密碼等敏感資料
        // 安全性：避免密碼等敏感資料被暫存在 session 中
        // 前端使用：在 Blade 模板中用 old('欄位名') 取得暫存的資料
        return redirect('/user/old-input-demo')->withInput($request->except('password'));
        
        // ---------------------------------------------------------------------
        // 實際使用場景比較
        // ---------------------------------------------------------------------
        // 場景一：註冊表單驗證失敗
        // - 用戶填寫：username、email、password、password_confirmation
        // - 驗證失敗：email 格式錯誤
        // - 使用 withInput()：所有欄位都會回填，包含密碼（不安全）
        // - 使用 withInput($request->except(['password', 'password_confirmation']))：只回填 username、email
        
        // 場景二：個人資料更新表單驗證失敗
        // - 用戶填寫：name、email、phone、address
        // - 驗證失敗：phone 格式錯誤
        // - 使用 withInput()：所有欄位都會回填（安全，因為沒有敏感資料）
        
        // 場景三：登入表單驗證失敗
        // - 用戶填寫：email、password
        // - 驗證失敗：email 不存在
        // - 使用 withInput($request->except('password'))：只回填 email，不回填密碼
        
        // ---------------------------------------------------------------------
        // 安全性考量
        // ---------------------------------------------------------------------
        // 1. 密碼欄位：永遠不要用 withInput() 回填密碼
        // 2. 信用卡號：避免回填信用卡相關資料
        // 3. 個人識別碼：如身份證號、護照號等
        // 4. 其他敏感資料：根據業務需求決定
        
        // ---------------------------------------------------------------------
        // 前端 Blade 模板使用範例
        // ---------------------------------------------------------------------
        // <input type="text" name="username" value="{{ old('username') }}">
        // <input type="email" name="email" value="{{ old('email') }}">
        // <input type="password" name="password"> <!-- 密碼欄位不設 value -->
        
        // ---------------------------------------------------------------------
        // 其他 withInput 用法
        // ---------------------------------------------------------------------
        // 只回填特定欄位：
        // return redirect()->withInput($request->only(['username', 'email']));
        
        // 排除多個敏感欄位：
        // return redirect()->withInput($request->except(['password', 'password_confirmation', 'credit_card']));
        
        // 回填巢狀資料：
        // return redirect()->withInput($request->only(['user.name', 'user.email']));
    }

    /**
     * 顯示 old input 的表單頁面
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showOldInputForm(Request $request)
    {
        // 取得 old input（可用於自訂邏輯，通常 Blade 模板直接用 old('欄位') 較方便）
        $oldUsername = $request->old('username');
        // 回傳表單頁面，欄位會自動用 old() 回填
        return view('user.old_input_demo', [
            'oldUsername' => $oldUsername,
        ]);
    }

    /**
     * [Cookies（取得與設定）]
     * -----------------------------------------------------------------------------
     * 1. 定義：
     *    - Cookie 是瀏覽器與伺服器之間用來「儲存小型資料」的機制，資料會隨每次請求自動帶到伺服器。
     *    - Cookie 有大小限制（通常 4KB），且可設定有效期、路徑、網域、是否加密、HttpOnly 等屬性。
     *    - 在 Laravel，所有自訂 cookie 預設都會加密與簽章，防止竄改與竊取。
     *    - 你可以用 response 實例的 cookie 方法，將 cookie 附加到回應送給用戶端，也可用 Cookie facade queue 方法預先排入 cookie，或用 cookie() 輔助產生 cookie 實例。
     *    - 你可以透過 Illuminate\Http\Request 的 cookie 方法取得請求中的 cookie 值。
     *
     * 2. 實作範例：
     *    Route::get('/user/cookie-demo', [UserController::class, 'cookieExample']);
     *    Route::get('/user/response-cookie', [UserController::class, 'responseCookie']);
     *    Route::get('/user/response-cookie-queue', [UserController::class, 'responseCookieQueue']);
     *    Route::get('/user/response-cookie-expire', [UserController::class, 'responseCookieExpire']);
     *
     * 3. 方法說明（各種設定 Cookie 寫法差異與用途）：
     *    - $request->cookie('name')：取得名稱為 name 的 cookie 值，若不存在則回傳 null。自動解密並驗證簽章，若 cookie 被竄改會視為無效。
     *    - response()->cookie('name', 'value', $minutes)：
     *        立即在回應上附加 cookie，最常見、簡單的寫法。
     *        範例：return response('內容')->cookie('user_id', 123, 60);
     *        適用：單一回應時直接加 cookie。
     *    - response()->cookie('name', 'value', $minutes, $path, $domain, $secure, $httpOnly)：
     *        可自訂 cookie 的路徑、網域、是否加密、是否 httpOnly 等屬性。
     *        範例：return response('內容')->cookie('user_id', 123, 60, '/', '.example.com', true, true);
     *        適用：需要跨子網域、只允許 HTTPS、加強安全時。
     *    - Cookie::queue('name', 'value', $minutes)：
     *        預先排入 cookie，Laravel 會在本次回應自動加上這個 cookie。
     *        範例：Cookie::queue('user_id', 123, 60);
     *        適用：無法直接操作 response 物件時（如在 middleware、service、事件等），或多處都要 queue，Laravel 會自動合併。
     *    - cookie('name', 'value', $minutes)：
     *        產生一個 Cookie 實例物件，不會自動加到回應。
     *        範例：$cookie = cookie('user_id', 123, 60); return response('內容')->cookie($cookie);
     *        適用：需要先產生 cookie 物件，之後再決定要不要加到回應，或條件式加 cookie。
     *    - response()->withoutCookie('name')：讓 cookie 立即過期。
     *    - Cookie::expire('name')：預先排入過期 cookie。
     *
     * 4. 小結比較表：
     *    | 寫法                                      | 立即加到回應 | 可自訂參數 | 適合場景                       |
     *    |--------------------------------------------|--------------|------------|-------------------------------|
     *    | response()->cookie('n', 'v', m)            | ✅           | ❌         | 最常見，簡單加 cookie          |
     *    | response()->cookie('n', 'v', m, ...)       | ✅           | ✅         | 需自訂路徑/網域/安全屬性時     |
     *    | Cookie::queue('n', 'v', m)                 | ⏳(自動)     | ❌         | 無法直接操作 response 時       |
     *    | cookie('n', 'v', m)                        | ❌(產生物件) | ✅         | 先產生 cookie，後續再加到回應  |
     *
     * 5. 安全性建議：
     *    - 儲存敏感資料時，請加密（Laravel 預設會加密自訂 cookie）。
     *    - 建議設置 httpOnly（JS 無法存取，防止 XSS 攻擊）。
     *    - 建議設置 secure（僅 HTTPS 傳送，防止竊聽）。
     *    - 不要在 cookie 存放密碼、信用卡號等高敏感資料。
     *    - 注意 cookie 大小限制（4KB），超過會被截斷。
     *
     * 6. 實務補充：
     *    - Cookie::queue 也支援自訂參數（可傳 Cookie 實例）。
     *    - cookie() 產生的物件可傳給 queue 或 response。
     *    - Laravel 會自動加密、簽章自訂 cookie，防止用戶端竄改。
     *    - 取得 cookie 時自動解密，若被竄改會視為無效。
     * -----------------------------------------------------------------------------
     */
    // 取得 cookie
    public function cookieExample(Request $request)
    {
        // 取得名為 'name' 的 cookie 值
        $cookieValue = $request->cookie('name');
        return response()->json([
            'cookieValue' => $cookieValue,
        ]);
    }
    // 直接附加 cookie 到回應
    public function responseCookie() {
        return response('Hello World')->cookie('name', 'value', 10);
    }
    // 用 Cookie facade queue cookie
    public function responseCookieQueue() {
        \Illuminate\Support\Facades\Cookie::queue('queued_name', 'queued_value', 10);
        return response('Cookie queued!');
    }
    // 讓 cookie 立即過期
    public function responseCookieExpire() {
        return response('Hello World')->withoutCookie('name');
    }

    /**
     * [Files（檔案上傳）]
     * -----------------------------------------------------------------------------
     * 1. 定義：
     *    - 你可以透過 Illuminate\Http\Request 的 file 方法或動態屬性取得上傳的檔案。
     *    - 回傳的 UploadedFile 物件可用多種方法操作檔案（如路徑、延伸、儲存等）。
     *
     * 2. 實作範例：
     *    Route::post('/user/file-demo', [UserController::class, 'fileExample']);
     *    Blade：<form method="POST" enctype="multipart/form-data" action="/user/file-demo"> ... </form>
     *
     * 3. 方法說明：
     *    - $request->file('photo') / $request->photo：取得上傳檔案（UploadedFile 實例），無檔案時回傳 null。
     *    - $request->hasFile('photo')：判斷是否有檔案上傳。
     *    - $request->file('photo')->isValid()：檢查檔案是否成功上傳。
     *    - $request->photo->path()：取得暫存檔案的完整路徑。
     *    - $request->photo->extension()：根據內容猜測副檔名。
     *    - $request->photo->store('images')：將檔案存到 storage/app/images，檔名自動產生。
     *    - $request->photo->storeAs('images', 'filename.jpg')：自訂檔名儲存。
     * -----------------------------------------------------------------------------
     */
    public function fileExample(Request $request)
    {
        // 判斷是否有檔案上傳
        $hasFile = $request->hasFile('photo');
        $isValid = false;
        $path = null;
        $extension = null;
        $storedPath = null;
        $storedPathAs = null;
        if ($hasFile) {
            $file = $request->file('photo');
            // 檢查檔案是否成功上傳
            $isValid = $file->isValid();
            // 取得暫存檔案路徑
            $path = $file->path();
            // 取得副檔名
            $extension = $file->extension();
            // 儲存檔案（自動產生檔名）
            $storedPath = $file->store('images');
            // 儲存檔案（自訂檔名）
            $storedPathAs = $file->storeAs('images', 'demo.jpg');
        }
        return response()->json([
            'hasFile' => $hasFile,
            'isValid' => $isValid,
            'path' => $path,
            'extension' => $extension,
            'storedPath' => $storedPath,
            'storedPathAs' => $storedPathAs,
        ]);
    }

    /**
     * [HTTP Responses（HTTP 回應）]
     * -----------------------------------------------------------------------------
     * 1. 定義：
     *    - 所有路由與控制器都應回傳一個 HTTP 回應給用戶端。
     *    - Laravel 支援回傳字串、陣列（自動轉 JSON）、Eloquent 模型/集合（自動轉 JSON）、Response 實例、View 等多種型態。
     *
     * 2. 實作範例：
     *    Route::get('/user/response-demo/string', [UserController::class, 'responseString']);
     *    Route::get('/user/response-demo/array', [UserController::class, 'responseArray']);
     *    Route::get('/user/response-demo/model/{user}', [UserController::class, 'responseModel']);
     *    Route::get('/user/response-demo/custom', [UserController::class, 'responseCustom']);
     *
     * 3. 方法說明：
     *    - return '字串'：自動轉成 HTTP 回應。
     *    - return [1,2,3]：自動轉成 JSON 回應。
     *    - return $model/$collection：自動轉成 JSON，會隱藏 hidden 屬性。
     *    - return response($content, $status)：自訂回應內容與狀態碼。
     *    - ->header('key', 'value')：加單一 header。
     *    - ->withHeaders([...])：加多個 header。
     * -----------------------------------------------------------------------------
     */
    // 回傳字串
    public function responseString() {
        return 'Hello World';
    }
    // 回傳陣列（自動轉 JSON）
    public function responseArray() {
        return [1, 2, 3];
    }
    // 回傳 Eloquent 模型（自動轉 JSON）
    public function responseModel(\App\Models\User $user) {
        return $user;
    }
    // 回傳自訂 Response 實例，含 header
    public function responseCustom() {
        return response('Hello World', 200)
            ->header('Content-Type', 'text/plain')
            ->header('X-Header-One', 'Header Value')
            ->withHeaders([
                'X-Header-Two' => 'Header Value',
            ]);
    }

    /**
     * [Redirects（重導）]
     * -----------------------------------------------------------------------------
     * 1. 定義：
     *    - RedirectResponse 是用來讓用戶端自動跳轉到其他網址的 HTTP 回應。
     *    - Laravel 提供 redirect()、back()、redirect()->route() 等多種重導方式。
     *    - 常用於表單送出後、驗證失敗、權限檢查等情境。
     *
     * 2. 實作範例：
     *    Route::get('/user/redirect-demo/simple', [UserController::class, 'redirectSimple']);
     *    Route::post('/user/redirect-demo/back', [UserController::class, 'redirectBack']);
     *    Route::get('/user/redirect-demo/route', [UserController::class, 'redirectRoute']);
     *    Route::get('/user/redirect-demo/route-param/{id}', [UserController::class, 'redirectRouteParam']);
     *    Route::get('/user/redirect-demo/route-model/{user}', [UserController::class, 'redirectRouteModel']);
     *
     * 3. 方法說明：
     *    - redirect('/url')：重導到指定網址。
     *    - back()：重導回前一頁（需 session 支援）。
     *    - redirect()->route('route_name', [...])：重導到命名路由，可帶參數。
     *    - redirect()->route('route_name', [$model])：可直接帶 Eloquent 模型，會自動取主鍵。
     *    - 若路由參數定義為 {id:slug}，可自訂 getRouteKey() 讓重導參數自動帶 slug。
     * -----------------------------------------------------------------------------
     */
    // 最簡單的重導
    public function redirectSimple() {
        return redirect('/user/response-demo/string');
    }
    // 重導回前一頁（常用於表單驗證失敗）
    public function redirectBack() {
        return back()->withInput();
    }
    // 重導到命名路由
    public function redirectRoute() {
        return redirect()->route('user.profile', ['id' => 1]);
    }
    // 重導到命名路由並帶參數
    public function redirectRouteParam($id) {
        return redirect()->route('user.profile', ['id' => $id]);
    }
    // 重導到命名路由並帶 Eloquent 模型
    public function redirectRouteModel(\App\Models\User $user) {
        return redirect()->route('user.profile', [$user]);
    }

    /**
     * [Redirecting to Controller Actions / External Domains（重導到控制器或外部網址）]
     * -----------------------------------------------------------------------------
     * 1. 定義：
     *    - 你可以用 redirect()->action() 直接重導到指定控制器方法。
     *    - 也可以用 redirect()->away() 重導到外部網站（不經 Laravel 驗證/編碼）。
     *
     * 2. 實作範例：
     *    Route::get('/user/redirect-demo/action', [UserController::class, 'redirectAction']);
     *    Route::get('/user/redirect-demo/action-param', [UserController::class, 'redirectActionParam']);
     *    Route::get('/user/redirect-demo/away', [UserController::class, 'redirectAway']);
     *
     * 3. 方法說明：
     *    - redirect()->action([Controller::class, 'method'])：重導到控制器 action。
     *    - redirect()->action([Controller::class, 'method'], ['id' => 1])：帶參數重導。
     *    - redirect()->away('https://...')：重導到外部網址，不經 Laravel 驗證。
     * -----------------------------------------------------------------------------
     */
    // 重導到控制器 action
    public function redirectAction() {
        return redirect()->action([UserController::class, 'responseString']);
    }
    // 重導到控制器 action 並帶參數
    public function redirectActionParam() {
        return redirect()->action([UserController::class, 'responseModel'], ['user' => 1]);
    }
    // 重導到外部網址
    public function redirectAway() {
        return redirect()->away('https://www.google.com');
    }

    /**
     * [Redirecting With Flashed Session Data（重導並閃存 session 資料）]
     * -----------------------------------------------------------------------------
     * 1. 定義：
     *    - 重導時常會同時將訊息或表單資料暫存（flash）到 session，方便下個請求顯示提示或回填表單。
     *    - 常見於成功訊息、錯誤訊息、表單驗證失敗等情境。
     *
     * 2. 實作範例：
     *    Route::post('/user/redirect-flash', [UserController::class, 'redirectWithFlash']);
     *    Route::post('/user/redirect-flash-input', [UserController::class, 'redirectWithInput']);
     *    Blade：@if (session('status')) ... @endif
     *
     * 3. 方法說明：
     *    - redirect('/url')->with('key', 'value')：重導並閃存訊息到 session。
     *    - session('key')：在下個請求取得閃存訊息。
     *    - redirect()->withInput()：重導並閃存目前 request 的 input 資料。
     *    - old('欄位')：在下個請求回填表單欄位。
     * -----------------------------------------------------------------------------
     */
    // 重導並閃存訊息
    public function redirectWithFlash() {
        return redirect('/user/response-demo/string')->with('status', 'Profile updated!');
    }
    // 重導並閃存 input
    public function redirectWithInput(Request $request) {
        return back()->withInput();
    }

    /**
     * [Other Response Types（其他回應型態：View/JSON/Download/File）]
     * -----------------------------------------------------------------------------
     * 1. 定義：
     *    - Laravel 提供 response() 輔助函式產生多種回應型態：View、JSON、JSONP、檔案下載、檔案直接顯示等。
     *    - 可自訂 HTTP 狀態碼、header。
     *
     * 2. 實作範例：
     *    Route::get('/user/response-view', [UserController::class, 'responseView']);
     *    Route::get('/user/response-json', [UserController::class, 'responseJson']);
     *    Route::get('/user/response-jsonp', [UserController::class, 'responseJsonp']);
     *    Route::get('/user/response-download', [UserController::class, 'responseDownload']);
     *    Route::get('/user/response-file', [UserController::class, 'responseFile']);
     *
     * 3. 方法說明：
     *    - response()->view('view', $data, $status)：回傳 view，可自訂狀態碼與 header。
     *    - view('view', $data)：全域 view 輔助函式，簡單回傳 view。
     *    - response()->json($array)：回傳 JSON，Content-Type 自動設為 application/json。
     *    - response()->json($array)->withCallback($cb)：回傳 JSONP。
     *    - response()->download($path, $name, $headers)：強制下載檔案。
     *    - response()->file($path, $headers)：直接顯示檔案（如圖片、PDF）。
     * -----------------------------------------------------------------------------
     */
    // 回傳 view 並自訂 header
    public function responseView() {
        return response()->view('welcome', ['name' => 'Vincent'], 200)
            ->header('Content-Type', 'text/html');
    }
    // 回傳 JSON
    public function responseJson() {
        return response()->json([
            'name' => 'Abigail',
            'state' => 'CA',
        ]);
    }
    // 回傳 JSONP
    public function responseJsonp(Request $request) {
        return response()->json([
            'name' => 'Abigail',
            'state' => 'CA',
        ])->withCallback($request->input('callback', 'callback'));
    }
    // 檔案下載
    public function responseDownload() {
        $path = public_path('robots.txt'); // 範例：下載 public/robots.txt
        return response()->download($path, 'my-robots.txt');
    }
    // 檔案直接顯示
    public function responseFile() {
        $path = public_path('robots.txt'); // 範例：顯示 public/robots.txt
        return response()->file($path);
    }

    /**
     * [Streamed Responses（串流回應）]
     * -----------------------------------------------------------------------------
     * 1. 定義：
     *    - 串流回應（Streamed Response）是伺服器「邊產生資料、邊傳給用戶端」的技術，不需等全部資料產生完才傳送。
     *    - 適合大量資料、即時互動、AI 生成、即時監控等場景。
     *
     * 2. 有什麼用處？
     *    - 節省記憶體、提升效能：如大檔案下載、匯出大量資料時，伺服器不用一次載入全部。
     *    - 提升用戶體驗：如 AI 文字生成、聊天室、即時通知，讓用戶「馬上看到部分結果」。
     *    - 支援現代前端框架的資料流需求：React/Vue 可用 stream 方式持續接收資料，做出更流暢的 UI。
     *
     * 3. 實務場景舉例：
     *    - AI 文字生成（如 ChatGPT）
     *    - 大型報表/匯出（如 10 萬筆 CSV）
     *    - 即時監控/日誌串流
     *    - Server-Sent Events (SSE) 即時推播
     *
     * 4. 技術細節：
     *    - response()->stream(fn() {...})：最基本串流，echo 什麼就傳什麼。
     *    - response()->streamJson([...])：分段傳送 JSON。
     *    - response()->eventStream(fn() {...})：SSE 事件串流。
     *    - response()->streamDownload(fn() {...}, $filename)：邊產生檔案邊下載。
     *
     * 5. 圖解：
     *    - 伺服器傳送第一段資料 → 用戶端馬上收到顯示
     *    - 伺服器繼續傳送下一段 → 用戶端持續顯示
     *    - ...直到結束
     *
     * 6. 小結：
     *    - Streamed Response 適合「資料量大」或「需要即時互動」的場景。
     *    - 可大幅減少伺服器記憶體用量，提升用戶體驗，是現代 Web/AI/即時應用不可或缺的技術。
     *
     * 7. 實作範例：
     *    Route::get('/user/response-stream', [UserController::class, 'responseStream']);
     *    Route::get('/user/response-stream-json', [UserController::class, 'responseStreamJson']);
     *    Route::get('/user/response-event-stream', [UserController::class, 'responseEventStream']);
     *    Route::get('/user/response-stream-download', [UserController::class, 'responseStreamDownload']);
     *
     * 8. 方法說明：
     *    - response()->stream(fn() {...}, $status, $headers)：一般串流回應。
     *    - response()->streamJson([...])：漸進式傳送 JSON。
     *    - response()->eventStream(fn() {...})：SSE 事件串流。
     *    - response()->streamDownload(fn() {...}, $filename, $headers)：串流下載。
     * -----------------------------------------------------------------------------
     */
    // 一般串流回應
    public function responseStream() {
        return response()->stream(function () {
            foreach (['developer', 'admin'] as $string) {
                echo $string . PHP_EOL;
                ob_flush();
                flush();
                sleep(1);
            }
        }, 200, ['X-Accel-Buffering' => 'no']);
    }
    // 串流 JSON 回應
    public function responseStreamJson() {
        $users = [
            ['id' => 1, 'name' => 'Vincent'],
            ['id' => 2, 'name' => 'Abigail'],
        ];
        return response()->streamJson(['users' => $users]);
    }
    // SSE 事件串流
    public function responseEventStream() {
        return response()->eventStream(function () {
            foreach ([1, 2, 3] as $i) {
                yield new \Illuminate\Http\StreamedEvent(
                    event: 'update',
                    data: "count: $i",
                );
                sleep(1);
            }
        });
    }
    // 串流下載
    public function responseStreamDownload() {
        return response()->streamDownload(function () {
            echo "Vincent,Abigail\n";
            echo "1,2\n";
        }, 'users.csv');
    }

    /**
     * [Response Macro 實作範例]
     * -----------------------------------------------------------------------------
     * 你可以在 AppServiceProvider 註冊 macro，例如 caps，然後在 controller 直接呼叫 response()->caps('foo')。
     * -----------------------------------------------------------------------------
     */
    public function responseCaps() {
        return response()->caps('foo'); // 回傳 'FOO'
    }
}