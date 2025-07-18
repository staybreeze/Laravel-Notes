{{--
    # Laravel URL 產生器（URL Generation）教學

    本文件完整介紹 Laravel 中常用的 URL 產生輔助函式與進階用法，包含 url()、route()、簽名網址等。
    適用於 Blade 模板、API 回應、重導等場合。
    所有內容皆有完整中文註解、逐行說明與實際渲染結果。
--}}

{{-- ========================= --}}
{{-- # 1. 基本用法：url() 輔助函式 --}}
{{-- ========================= --}}

@php
    // 假設有一篇文章
    $post = (object)['id' => 1];
@endphp

{{-- 範例：產生文章網址 --}}
產生文章網址：<br>
<code>
    $post = App\Models\Post::find(1);<br>
    echo url("/posts/{$post->id}");
</code>
<br>
實際渲染結果：<br>
<strong>{{ url("/posts/{$post->id}") }}</strong>
<br>
<em>輸出範例：http://localhost/posts/1</em>
<br><br>

{{--
    url() 會自動帶入目前請求的協定（http/https）與主機名稱。
    生活化比喻：就像導航地圖自動幫你補齊完整地址。
--}}

{{-- ========================= --}}
{{-- # 2. 產生帶查詢參數的網址：url()->query() --}}
{{-- ========================= --}}

{{-- 範例：產生帶查詢參數的網址 --}}
產生帶查詢參數的網址：<br>
<code>
    echo url()->query('/posts', ['search' => 'Laravel']);
</code>
<br>
實際渲染結果：<br>
<strong>{{ url()->query('/posts', ['search' => 'Laravel']) }}</strong>
<br>
<em>輸出範例：http://localhost/posts?search=Laravel</em>
<br><br>

{{-- 範例：原本網址已經有查詢參數，會自動合併 --}}
<code>
    echo url()->query('/posts?sort=latest', ['search' => 'Laravel']);
</code>
<br>
實際渲染結果：<br>
<strong>{{ url()->query('/posts?sort=latest', ['search' => 'Laravel']) }}</strong>
<br>
<em>輸出範例：http://localhost/posts?sort=latest&search=Laravel</em>
<br><br>

{{-- 範例：若查詢參數重複，會以後者為主 --}}
<code>
    echo url()->query('/posts?sort=latest', ['sort' => 'oldest']);
</code>
<br>
實際渲染結果：<br>
<strong>{{ url()->query('/posts?sort=latest', ['sort' => 'oldest']) }}</strong>
<br>
<em>輸出範例：http://localhost/posts?sort=oldest</em>
<br><br>

{{-- 範例：陣列查詢參數 --}}
@php
    $url = url()->query('/posts', ['columns' => ['title', 'body']]);
@endphp
<code>
    $url = url()->query('/posts', ['columns' => ['title', 'body']]);
    echo $url;
</code>
<br>
實際渲染結果（已編碼）：<br>
<strong>{{ $url }}</strong>
<br>
實際渲染結果（解碼後）：<br>
<strong>{{ urldecode($url) }}</strong>
<br>
<em>輸出範例：http://localhost/posts?columns%5B0%5D=title&columns%5B1%5D=body</em>
<br>
<em>解碼後：http://localhost/posts?columns[0]=title&columns[1]=body</em>
<br><br>

{{-- ========================= --}}
{{-- # 3. 取得目前/前一個網址 --}}
{{-- ========================= --}}

{{--
    url() 不帶參數時，會回傳 Illuminate\Routing\UrlGenerator 實例，可取得目前網址、完整網址、前一頁網址等：
--}}
<code>
    // 取得目前網址（不含 query string）
    echo url()->current();
    // 取得目前網址（含 query string）
    echo url()->full();
    // 取得前一個請求的完整網址
    echo url()->previous();
    // 取得前一個請求的路徑
    echo url()->previousPath();
</code>
<br>
<strong>目前網址（不含 query）：</strong> {{ url()->current() }}<br>
<em>輸出範例：http://localhost/notes/url-generation</em><br>
<strong>目前網址（含 query）：</strong> {{ url()->full() }}<br>
<em>輸出範例：http://localhost/notes/url-generation?tab=examples</em><br>
<strong>前一個網址：</strong> {{ url()->previous() }}<br>
<em>輸出範例：http://localhost/dashboard</em><br>
{{-- previousPath() 需 Laravel 11+，如不支援可註明 --}}
{{-- <strong>前一個路徑：</strong> {{ url()->previousPath() }}<br> --}}
<br>

{{-- ========================= --}}
{{-- # 4. URL Facade 用法 --}}
{{-- ========================= --}}

{{--
    也可用 URL Facade 取得目前網址：
    use Illuminate\Support\Facades\URL;
    echo URL::current();
--}}

{{-- ========================= --}}
{{-- # 5. 產生命名路由網址：route() --}}
{{-- ========================= --}}

{{--
    route() 可產生命名路由的網址，不需綁定實際路徑，路由變更時不需修改呼叫處。
    範例：
    Route::get('/post/{post}', ...)->name('post.show');
    echo route('post.show', ['post' => 1]);
    // http://example.com/post/1
--}}
<code>
    // 單一參數
    echo route('post.show', ['post' => 1]);
    // 多參數
    echo route('comment.show', ['post' => 1, 'comment' => 3]);
    // 額外參數會自動加到 query string
    echo route('post.show', ['post' => 1, 'search' => 'rocket']);
</code>
<br>
<em>輸出範例：</em><br>
<em>單一參數：http://localhost/post/1</em><br>
<em>多參數：http://localhost/post/1/comment/3</em><br>
<em>額外參數：http://localhost/post/1?search=rocket</em><br>
{{-- 實際渲染需有對應路由，這裡僅展示語法 --}}

{{--
    傳遞 Eloquent Model 也可自動取出主鍵：
    echo route('post.show', ['post' => $post]);
--}}

{{-- ========================= --}}
{{-- # 6. 簽名網址（Signed URLs） --}}
{{-- ========================= --}}

{{--
    簽名網址可防止網址被竄改，常用於公開但需驗證的連結（如退訂信）。
    使用 URL Facade 的 signedRoute 產生：
    use Illuminate\Support\Facades\URL;
    return URL::signedRoute('unsubscribe', ['user' => 1]);
    // 可選 absolute: false 只簽名路徑不含網域
    return URL::signedRoute('unsubscribe', ['user' => 1], absolute: false);
    // 產生有時效的簽名網址
    return URL::temporarySignedRoute('unsubscribe', now()->addMinutes(30), ['user' => 1]);
--}}

{{-- ========================= --}}
{{-- # 6.1 簽名網址原理與 APP_KEY --}}
{{-- ========================= --}}

{{--
    簽名網址是透過 APP_KEY 和加密演算法產生的安全網址。
    
    APP_KEY 格式：
    base64:j8TzK9mN2pQ5rS7vX1yA3bC6dE9fG2hJ4kL8mN1pQ4rS7vX0yA3bC6dE9fG2hJ
    
    產生簽名流程：
    1. 取得網址路徑和參數
    2. 使用 APP_KEY 作為密鑰
    3. 用 HMAC-SHA256 演算法產生簽名
    4. 將簽名加到網址的 signature 參數
    
    驗證簽名流程：
    1. 從網址取出路徑和參數
    2. 使用相同的 APP_KEY
    3. 重新計算簽名
    4. 比較是否與網址中的簽名一致
    
    生活化比喻：
    APP_KEY 就像數位印章，簽名就是用印章蓋在網址上的印記，
    驗證時檢查印記是否與印章圖案匹配。
--}}

{{-- ========================= --}}
{{-- # 6.2 簽名網址範例 --}}
{{-- ========================= --}}

<em>輸出範例：</em><br>
<em>簽名網址：http://localhost/unsubscribe/1?signature=abc123...</em><br>
<em>相對簽名：/unsubscribe/1?signature=abc123...</em><br>
<em>時效簽名：http://localhost/unsubscribe/1?signature=abc123&expires=1234567890</em><br>

{{-- ========================= --}}
{{-- # 6.3 簽名網址安全性 --}}
{{-- ========================= --}}

{{--
    簽名網址的安全性保證：
    
    1. APP_KEY 保密：只有你的伺服器知道
    2. 演算法不可逆：無法從簽名反推原始資料
    3. 任何竄改都會被發現：路徑、參數、簽名任一被改都會失敗
    4. 時效性：可以設定過期時間
    
    實際範例：
    原始網址：http://localhost/unsubscribe/1?signature=abc123...
    竄改後：http://localhost/unsubscribe/2?signature=abc123... (會驗證失敗)
--}}

{{-- ========================= --}}
{{-- # 7. 驗證簽名網址 --}}
{{-- ========================= --}}

{{--
    在 Controller 或 Route 中驗證簽名：
    use Illuminate\Http\Request;
    Route::get('/unsubscribe/{user}', function (Request $request) {
        if (! $request->hasValidSignature()) {
            abort(401);
        }
        // ...
    })->name('unsubscribe');

    // 忽略部分查詢參數
    if (! $request->hasValidSignatureWhileIgnoring(['page', 'order'])) {
        abort(401);
    }

    // 也可用 middleware 自動驗證
    Route::post('/unsubscribe/{user}', function (Request $request) {
        // ...
    })->name('unsubscribe')->middleware('signed');
    // 若簽名網址不含網域，middleware 加上 :relative
    Route::post('/unsubscribe/{user}', function (Request $request) {
        // ...
    })->name('unsubscribe')->middleware('signed:relative');
--}}

{{-- ========================= --}}
{{-- # 7.1 hasValidSignatureWhileIgnoring 說明 --}}
{{-- ========================= --}}

{{--
    hasValidSignatureWhileIgnoring() 方法用來驗證簽名網址，但忽略某些查詢參數。
    
    語法：
    if (! $request->hasValidSignatureWhileIgnoring(['page', 'order'])) {
        abort(401);
    }
    
    用途：
    - 保持核心簽名有效性
    - 允許用戶加上分頁、排序等額外參數
    - 提供靈活性而不影響安全性
    
    範例：
    原始簽名網址：http://localhost/unsubscribe/1?signature=abc123
    用戶加上參數：http://localhost/unsubscribe/1?signature=abc123&page=2&order=desc
    驗證時忽略 page 和 order，只驗證核心簽名
    
    生活化比喻：
    就像驗證邀請函時，簽名必須驗證，但座位號和時間可以忽略。
    
    常見忽略的參數：
    - 分頁相關：['page', 'per_page']
    - 排序相關：['sort', 'order', 'direction']
    - 篩選相關：['filter', 'search']
    - 顯示相關：['view', 'layout']
--}}

{{-- ========================= --}}
{{-- # 8. 自訂簽名網址錯誤頁 --}}
{{-- ========================= --}}

{{--
    訪問過期或無效簽名網址時，預設回傳 403，可在 bootstrap/app.php 註冊例外處理：
    use Illuminate\Routing\Exceptions\InvalidSignatureException;
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (InvalidSignatureException $e) {
            return response()->view('errors.link-expired', status: 403);
        });
    })
--}}

{{-- ========================= --}}
{{-- # 9. 小結 --}}
{{-- ========================= --}}

<ul>
    <li>url()：產生完整網址，支援查詢參數、陣列、合併、覆蓋</li>
    <li>url()->current()/full()/previous()：取得目前、前一個網址</li>
    <li>route()：產生命名路由網址，支援多參數、Eloquent Model、查詢參數</li>
    <li>URL Facade：靜態方式產生網址、簽名網址</li>
    <li>簽名網址：signedRoute、temporarySignedRoute，防止網址被竄改</li>
    <li>middleware 驗證簽名、例外自訂</li>
</ul>

{{--
    生活化比喻：
    url() 就像導航地圖自動補齊地址，route() 像是用地標（命名路由）導航，簽名網址像是加密邀請函，只有持有正確簽名的人才能進入。
--}}

{{-- ========================= --}}
{{-- # 10. 產生 Controller Action 的網址：action() --}}
{{-- ========================= --}}

{{--
    action() 可根據 Controller 類別與方法產生對應網址。
    用法：
    use App\Http\Controllers\HomeController;
    $url = action([HomeController::class, 'index']);
    // 若有路由參數，第二參數傳遞關聯陣列：
    $url = action([UserController::class, 'profile'], ['id' => 1]);
    // 實際渲染需有對應路由
--}}
<em>輸出範例：</em><br>
<em>基本用法：http://localhost/home</em><br>
<em>帶參數：http://localhost/user/1/profile</em><br>

{{-- ========================= --}}
{{-- # 11. 流暢 URI 物件操作：Uri 類別 --}}
{{-- ========================= --}}

{{--
    Laravel 的 Illuminate\Support\Uri 提供流暢的 URI 物件操作介面，底層整合 League URI 套件。
    可用於產生、修改、組合網址，與路由、Controller action 無縫整合。
    常用靜態方法：
    use Illuminate\Support\Uri;
    // 由字串產生 URI 物件
    $uri = Uri::of('https://example.com/path');
    // 產生路徑、命名路由、簽名路由、Controller action 對應的 URI 物件
    $uri = Uri::to('/dashboard');
    $uri = Uri::route('users.show', ['user' => 1]);
    $uri = Uri::signedRoute('users.show', ['user' => 1]);
    $uri = Uri::temporarySignedRoute('user.index', now()->addMinutes(5));
    $uri = Uri::action([UserController::class, 'index']);
    $uri = Uri::action(InvokableController::class);
    // 由目前 request 產生 URI 物件
    $uri = $request->uri();
    // 物件可流暢修改：
    $uri = Uri::of('https://example.com')
        ->withScheme('http')
        ->withHost('test.com')
        ->withPort(8000)
        ->withPath('/users')
        ->withQuery(['page' => 2])
        ->withFragment('section-1');
    // 生活化比喻：Uri 物件就像積木，可以隨時組裝、拆解、變更網址各部分。
--}}
<em>輸出範例：</em><br>
<em>Uri::to('/dashboard')：http://localhost/dashboard</em><br>
<em>Uri::route('users.show', ['user' => 1])：http://localhost/users/1</em><br>
<em>流暢修改後：http://test.com:8000/users?page=2#section-1</em><br>

{{-- ========================= --}}
{{-- # 12. URL 預設值：URL::defaults --}}
{{-- ========================= --}}

{{--
    有些專案路由會有共用參數（如 {locale}），每次產生網址都要傳很麻煩。
    可用 URL::defaults(['locale' => ...]) 設定預設值，通常在 middleware 設定：
    use Illuminate\Support\Facades\URL;
    URL::defaults(['locale' => $request->user()->locale]);
    // 設定後，route() 產生網址時可省略 locale 參數。
    // 範例 middleware：
    namespace App\Http\Middleware;
    use Closure;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\URL;
    use Symfony\Component\HttpFoundation\Response;
    class SetDefaultLocaleForUrls
    {
        public function handle(Request $request, Closure $next): Response
        {
            URL::defaults(['locale' => $request->user()->locale]);
            return $next($request);
        }
    }
    // 生活化比喻：像是預設 GPS 城市，導航時不用每次都輸入。
--}}
<em>輸出範例：</em><br>
<em>設定前：route('post.show', ['post' => 1, 'locale' => 'zh-TW'])</em><br>
<em>設定後：route('post.show', ['post' => 1]) // locale 自動帶入</em><br>
<em>結果：http://localhost/zh-TW/post/1</em><br>

{{-- ========================= --}}
{{-- # 13. URL 預設值與 Middleware 優先順序 --}}
{{-- ========================= --}}

{{--
    設定 URL 預設值時，需確保 middleware 執行順序在 SubstituteBindings 之前，否則會影響隱式 model 綁定。
    可在 bootstrap/app.php 設定 middleware 優先順序：
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prependToPriorityList(
            before: \Illuminate\Routing\Middleware\SubstituteBindings::class,
            prepend: \App\Http\Middleware\SetDefaultLocaleForUrls::class,
        );
    })
    // 生活化比喻：像是先設定導航預設城市，再讓導航自動帶出地標。
--}} 