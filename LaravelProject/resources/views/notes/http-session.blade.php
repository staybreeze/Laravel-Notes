{{--
    # Laravel HTTP Session 教學

    本文件完整介紹 Laravel Session 用法、設定、API、進階技巧與自訂驅動。
    適用於用戶登入、訊息提示、跨請求資料保存等場合。
    內容皆有中文註解、章節分明、逐行說明、生活化比喻。
--}}

{{-- ========================= --}}
{{-- # 1. Session 介紹與用途 --}}
{{-- ========================= --}}

{{--
    HTTP 是無狀態協定，Session 提供跨多次請求保存用戶資料的能力。
    常見用途：登入狀態、訊息提示、購物車、暫存資料等。
    Laravel 提供多種 Session 後端（file、cookie、database、memcached、redis、dynamodb、array），統一 API 操作。
--}}

{{-- ========================= --}}
{{-- # 2. Session 設定與支援驅動 --}}
{{-- ========================= --}}

{{--
    設定檔：config/session.php
    主要設定：
    - driver：資料儲存方式（file、cookie、database、memcached、redis、dynamodb、array）
    - SESSION_DRIVER 環境變數可指定

    各驅動說明：
    - file：預設，存於 storage/framework/sessions
    - cookie：加密後存於 cookie
    - database：存於資料庫（需 sessions 資料表）
    - memcached/redis：快取型儲存
    - dynamodb：AWS DynamoDB
    - array：僅測試用，資料不會持久化
--}}

{{-- ========================= --}}
{{-- # 3. 驅動前置作業 --}}
{{-- ========================= --}}

{{--
    database：需有 sessions 資料表
    php artisan make:session-table
    php artisan migrate
    redis：需安裝 phpredis 擴充或 predis 套件，SESSION_CONNECTION 可指定連線
--}}

{{-- ========================= --}}
{{-- # 4. Session 取得與儲存 --}}
{{-- ========================= --}}

{{--
    兩種方式：Request 實例、全域 session() 輔助函式
    // 透過 Request
    $value = $request->session()->get('key');
    $value = $request->session()->get('key', 'default');
    $value = $request->session()->get('key', function () { return 'default'; });

    // 透過全域 session() 輔助函式
    $value = session('key');
    $value = session('key', 'default');
    session(['key' => 'value']);

    // 取得全部 session
    $data = $request->session()->all();

    // 只取部分/排除部分
    $data = $request->session()->only(['username', 'email']);
    $data = $request->session()->except(['username', 'email']);

    // 判斷是否存在
    $request->session()->has('users'); // 存在且不為 null
    $request->session()->exists('users'); // 存在（即使為 null）
    $request->session()->missing('users'); // 不存在
--}}

{{-- ========================= --}}
{{-- # 5. Session 寫入、push、拉取、刪除 --}}
{{-- ========================= --}}

{{--
    // 寫入
    $request->session()->put('key', 'value');
    session(['key' => 'value']);

    // push 陣列
    $request->session()->push('user.teams', 'developers');

    // pull 取出並刪除
    $value = $request->session()->pull('key', 'default');

    // increment/decrement（遞增/遞減）
    $request->session()->increment('count');        // 數值 +1
    $request->session()->increment('count', 2);     // 數值 +2
    $request->session()->decrement('count');        // 數值 -1
    $request->session()->decrement('count', 2);     // 數值 -2

    // forget/flush（忘記/清空）
    $request->session()->forget('name');            // 刪除單一項目
    $request->session()->forget(['name', 'status']); // 刪除多個項目
    $request->session()->flush();                   // 清空所有 Session 資料
--}}

{{-- ========================= --}}
{{-- # 5.1 increment/decrement 詳細說明 --}}
{{-- ========================= --}}

{{--
    increment() 和 decrement() 方法用於數值型 Session 的遞增和遞減操作。
    
    語法：
    $request->session()->increment('key');           // 預設 +1
    $request->session()->increment('key', $amount);  // 指定增加數量
    $request->session()->decrement('key');           // 預設 -1
    $request->session()->decrement('key', $amount);  // 指定減少數量
    
    實際範例：
    // 購物車數量管理
    session()->increment('cart_count');              // 加入商品
    session()->decrement('cart_count');              // 移除商品
    
    // 頁面點擊次數統計
    session()->increment('page_views');              // 頁面被訪問
    
    // 遊戲計分
    session()->increment('score', 10);               // 得分 +10
    session()->decrement('lives');                   // 生命 -1
    
    生活化比喻：
    就像計數器，可以往上加或往下減，常用於需要累計或扣減的場景。
--}}

{{-- ========================= --}}
{{-- # 5.2 forget/flush 詳細說明 --}}
{{-- ========================= --}}

{{--
    forget() 和 flush() 方法用於刪除 Session 資料。
    
    語法：
    $request->session()->forget('key');              // 刪除單一項目
    $request->session()->forget(['key1', 'key2']);   // 刪除多個項目
    $request->session()->flush();                    // 清空所有資料
    
    實際範例：
    // 登出時刪除用戶相關資料
    session()->forget('user_id');                    // 只刪除用戶 ID
    session()->forget(['user_id', 'username']);      // 刪除用戶相關資料
    
    // 清空購物車
    session()->forget('cart_items');                 // 只清空購物車
    session()->flush();                              // 清空所有 Session（完全登出）
    
    // 清除特定類型的資料
    session()->forget(['temp_data', 'form_data']);   // 清除暫存資料
    
    生活化比喻：
    forget() 就像忘記特定事情，flush() 就像清空整個記憶。
    forget() 是選擇性遺忘，flush() 是全部遺忘。
--}}

{{-- ========================= --}}
{{-- # 6. Flash Data（一次性訊息） --}}
{{-- ========================= --}}

{{--
    // flash：下次請求可用，之後自動刪除
    $request->session()->flash('status', 'Task was successful!');

    // reflash：延長所有 flash 資料一次
    $request->session()->reflash();

    // keep：延長指定 flash 資料
    $request->session()->keep(['username', 'email']);
    
    // now：僅本次請求有效
    $request->session()->now('status', 'Task was successful!');
--}}

{{-- ========================= --}}
{{-- # 7. Session ID 再生與失效 --}}
{{-- ========================= --}}

{{--
    // 再生 session id（防止 session fixation 攻擊）
    $request->session()->regenerate();
    
    // 再生並清空所有資料
    $request->session()->invalidate();
--}}

{{-- ========================= --}}
{{-- # 8. Session Blocking（阻擋並發寫入） --}}
{{-- ========================= --}}

{{--
    須使用支援 atomic lock 的快取驅動（memcached、redis、database、file、array...）
    // 路由加上 block 方法
    Route::post('/profile', function () { ... })->block($lockSeconds = 10, $waitSeconds = 10);

    // $lockSeconds：鎖定最長秒數

    // $waitSeconds：等待鎖定最長秒數

    // 不傳參數預設 10 秒
    
    // 生活化比喻：像是銀行櫃檯一次只服務一人，避免資料衝突。
--}}

{{-- ========================= --}}
{{-- # 9. 自訂 Session Driver --}}
{{-- ========================= --}}

{{--
    若內建驅動不符需求，可自訂 SessionHandlerInterface 實作：
    namespace App\Extensions;
    class MongoSessionHandler implements \SessionHandlerInterface {
        public function open($savePath, $sessionName) {}
        public function close() {}
        public function read($sessionId) {}
        public function write($sessionId, $data) {}
        public function destroy($sessionId) {}
        public function gc($lifetime) {}
    }
    // 註冊自訂驅動（ServiceProvider boot 方法）：
    use Illuminate\Support\Facades\Session;
    Session::extend('mongo', function ($app) {
        return new MongoSessionHandler;
    });
    // 設定 SESSION_DRIVER=mongo
    // 生活化比喻：像是自訂保險箱，自己決定資料怎麼存。
--}} 