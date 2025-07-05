<?php

// 所有 Laravel 路由都定義在 routes 目錄下的路由檔案中。
// 這些檔案會根據 bootstrap/app.php 的設定自動被 Laravel 載入。
// 本檔案（routes/web.php）專門用來定義網頁介面的路由，
// 並自動套用 web middleware group，提供 session 狀態與 CSRF 保護等功能。

// Laravel 路由可註冊各種 HTTP 動詞的路由：
// Route::get($uri, $callback);      // 處理 GET 請求
// Route::post($uri, $callback);     // 處理 POST 請求
// Route::put($uri, $callback);      // 處理 PUT 請求
// Route::patch($uri, $callback);    // 處理 PATCH 請求
// Route::delete($uri, $callback);   // 處理 DELETE 請求
// Route::options($uri, $callback);  // 處理 OPTIONS 請求

// 若需同時回應多個 HTTP 動詞，可用 match 或 any：
// Route::match(['get', 'post'], '/', function () { ... });
// Route::any('/', function () { ... });

// 當多條路由共用相同 URI 時，請先定義 get、post、put、patch、delete、options，
// 再定義 any、match、redirect，以確保請求能正確對應到預期的路由。
//
// Dependency Injection(依賴注入)
// 路由的 callback 可以型別提示（type-hint）所需的依賴，Laravel 會自動注入對應物件。
// 例如型別提示 Illuminate\Http\Request，會自動注入目前的 HTTP 請求物件：
//
// use Illuminate\Http\Request;
// Route::get('/users', function (Request $request) {
//     // 可直接使用 $request 取得請求資料
// });

// CSRF 保護
// 所有指向 POST、PUT、PATCH、DELETE 路由的 HTML 表單，都必須包含 CSRF token 欄位，否則請求會被拒絕。
// 在 Blade 模板中可用 @csrf 自動產生 token 欄位：
//
// <form method="POST" action="/profile">
//     @csrf
//     ...
// </form>

// Redirect Routes（重導路由）
// 若要定義一個重導到其他 URI 的路由，可使用 Route::redirect 方法：
// Route::redirect('/here', '/there'); // 預設回傳 302 狀態碼
//
// 可用第三個參數自訂狀態碼：
// Route::redirect('/here', '/there', 301); // 301 永久重導
//
// 或使用 Route::permanentRedirect 方法直接產生 301 永久重導：
// Route::permanentRedirect('/here', '/there');
//
// 注意：在重導路由中，destination 與 status 這兩個參數名稱為 Laravel 保留，不能作為路由參數使用。

// View Routes（直接回傳視圖的路由）
// 如果路由只需要回傳一個 view，可以用 Route::view 方法：
// Route::view('/welcome', 'welcome'); // 直接回傳 welcome 視圖
//
// 也可以傳遞資料給 view：
// Route::view('/welcome', 'welcome', ['name' => 'Taylor']);
//
// 注意：view、data、status、headers 這幾個參數名稱為 Laravel 保留，不能作為路由參數使用。

// 路由列表指令（Listing Your Routes）
// 可用 php artisan route:list 快速檢視所有已定義的路由：
// php artisan route:list
//
// 若要顯示每條路由的 middleware 與 middleware group，可加 -v 參數：
// php artisan route:list -v
//
// 若要展開 middleware group 內容，可加 -vv：
// php artisan route:list -vv
//
// 只顯示以特定 URI 開頭的路由：
// php artisan route:list --path=api
//
// 隱藏第三方套件定義的路由：
// php artisan route:list --except-vendor
//
// 只顯示第三方套件定義的路由：
// php artisan route:list --only-vendor

// Route Parameters（路由參數）
// 必填參數：可在路由 URI 中用 {參數名} 來擷取網址片段，例如：
// Route::get('/user/{id}', function (string $id) {
//     return 'User ' . $id;
// });
//
// 可同時定義多個參數：
// Route::get('/posts/{post}/comments/{comment}', function (string $postId, string $commentId) {
//     // ...
// });
//
// 路由參數名稱只能用英文字母與底線（_），會依照順序自動注入 callback 或 controller 的參數中。
//
// 參數與依賴注入：如果有依賴注入，請將路由參數寫在依賴之後，例如：
// use Illuminate\Http\Request;
// Route::get('/user/{id}', function (Request $request, string $id) {
//     return 'User ' . $id;
// });
//
// 可選參數：在參數名稱後加 ?，並給對應變數預設值，例如：
// Route::get('/user/{name?}', function (?string $name = null) {
//     return $name;
// });
// Route::get('/user/{name?}', function (?string $name = 'John') {
//     return $name;
// });

// Regular Expression Constraints（正規表達式限制）
// 可用 where 方法限制路由參數格式：
// Route::get('/user/{name}', function (string $name) {
//     // ...
// })->where('name', '[A-Za-z]+'); // 只允許英文字母
//
// Route::get('/user/{id}', function (string $id) {
//     // ...
// })->where('id', '[0-9]+'); // 只允許數字
//
// Route::get('/user/{id}/{name}', function (string $id, string $name) {
//     // ...
// })->where(['id' => '[0-9]+', 'name' => '[a-z]+']);
//
// 常用限制有輔助方法：
// Route::get('/user/{id}/{name}', function (string $id, string $name) {
//     // ...
// })->whereNumber('id')->whereAlpha('name');

// Route::get('/user/{name}', function (string $name) {
//     // ...
// })->whereAlphaNumeric('name');

// Route::get('/user/{id}', function (string $id) {
//     // ...
// })->whereUuid('id');

// Route::get('/user/{id}', function (string $id) {
//     // ...
// })->whereUlid('id');

// Route::get('/category/{category}', function (string $category) {
//     // ...
// })->whereIn('category', ['movie', 'song', 'painting']);
//
// 若參數不符限制，Laravel 會自動回傳 404。
//
// 全域參數限制：可在 App\Providers\AppServiceProvider 的 boot 方法中設定 Route::pattern，例如：
// use Illuminate\Support\Facades\Route;
// public function boot(): void
// {
//     Route::pattern('id', '[0-9]+'); // 全專案所有 {id} 參數都只允許數字
// }
// 這樣所有用到 {id} 的路由都會自動套用這個限制。

// Encoded Forward Slashes（允許斜線）
// Laravel 路由參數預設不允許 /（斜線），如果要允許，需用 where('參數名', '.*') 明確指定：
// Route::get('/search/{search}', function (string $search) {
//     return $search;
// })->where('search', '.*');
// 注意：這種寫法只能用在最後一個參數，否則路由會解析錯誤。

// Named Routes（命名路由）
// 可用 name 方法為路由命名，方便產生網址或重導：
// Route::get('/user/profile', function () { ... })->name('profile');
// Route::get('/user/profile', [UserProfileController::class, 'show'])->name('profile');
// 路由名稱應唯一。
//
// 補充：只要路由有命名（->name('xxx')），整個 Laravel 專案（controller、middleware、service、Blade、api.php、web.php 等）
// 都可以用這個名稱來產生網址或重導，不限於定義的檔案。
//
// 產生網址：$url = route('profile');
// 產生重導：return redirect()->route('profile'); 或 return to_route('profile');
//
// 範例：
// Route::get('/user/{id}/profile', function (string $id) {
//     // 這裡可以根據 $id 做查詢或顯示
//     return "User Profile: " . $id;
// })->name('profile');
//
// 產生帶參數網址：
// $url = route('profile', ['id' => 1]); 
// 產生 /user/1/profile 網址
//
// 多餘參數會自動加到 query string：
// $url = route('profile', ['id' => 1, 'photos' => 'yes']); 
// 產生 /user/1/profile?photos=yes
//
// 可用 URL::defaults 設定全域預設參數，例如多語系網站：
// 在 AppServiceProvider 的 boot 方法中：
// use Illuminate\Support\Facades\URL;
// public function boot(): void
// {
//     URL::defaults(['locale' => 'zh-TW']);
// }
//
// 路由定義：
// Route::get('/{locale}/user/{id}/profile', function ($locale, $id) { ... })->name('profile');
//
// 產生網址時沒帶 locale，會自動用預設值：
// $url = route('profile', ['id' => 1]); // 產生 /zh-TW/user/1/profile
//
// 也可以手動覆蓋：
// $url = route('profile', ['id' => 1, 'locale' => 'en']); // 產生 /en/user/1/profile
//
// 判斷目前路由名稱：
// $request->route()->named('profile')
// 可用於 middleware（常用於權限判斷）、controller、service 等任何有 $request 物件的地方，判斷目前請求是否為 profile 這條命名路由。
// 範例（middleware）：
// public function handle($request, Closure $next)
// {
//     if ($request->route()->named('profile')) {
//         // 如果是 profile 這條路由，做特別處理
//     }
//     return $next($request);
// }
// 範例（controller）：
// public function show(Request $request)
// {
//     if ($request->route()->named('profile')) {
//         // controller 內也可判斷
//     }
// }

// Route Groups（路由群組）
// 路由群組可讓多條路由共用屬性（如 middleware、prefix、namespace 等），不用每條都重複設定。
// 群組內的 middleware、where 條件會合併，prefix、name 會自動加在每條路由前面。
//
// 範例：為群組內所有路由套用多個 middleware
// Route::middleware(['first', 'second'])->group(function () {
//     Route::get('/', function () {
//         // 會套用 first & second middleware
//     });
//
//     Route::get('/user/profile', function () {
//         // 也會套用 first & second middleware
//     });
// });
//
// 群組屬性合併說明：
// middleware、where 會合併（多層群組會全部套用）
// prefix、name 會自動加在每條路由前面（多層會連接起來）
// namespace 會自動處理分隔符
//
// where：限制群組內所有路由參數的格式
// Route::where(['id' => '[0-9]+'])->group(function () {
//     Route::get('/user/{id}', function ($id) {
//         // 這裡的 {id} 只允許數字
//     });
//     Route::get('/order/{id}', function ($id) {
//         // 這裡的 {id} 也只允許數字
//     });
// });
//
// namespace：指定群組內所有路由的控制器命名空間（Laravel 8 以下常用）
// Route::namespace('Admin')->group(function () {
//     Route::get('/dashboard', 'DashboardController@index');
//     // 這裡會自動對應到 App\Http\Controllers\Admin\DashboardController
// });
//
// 補充：namespace 只會自動對應到你指定的命名空間（如 Admin），
// 不會對應到其他命名空間（如 AdminB、Other）。
// 例如：
// Route::namespace('AdminB')->group(function () {
//     Route::get('/dashboard', 'DashboardController@index');
//     // 這裡才會對應到 App\Http\Controllers\AdminB\DashboardController
// });

// Route Prefixes（路由網址前綴）
// 用 prefix 方法可以讓群組內所有路由的網址自動加上前綴：
// Route::prefix('admin')->group(function () {
//     Route::get('/users', function () {
//         // 這條路由的網址會是 /admin/users
//     });
// });
//
// Route Name Prefixes（路由名稱前綴）
// 用 name 方法可以讓群組內所有路由的名稱自動加上前綴：
// Route::name('admin.')->group(function () {
//     Route::get('/users', function () {
//         // 這條路由的名稱會是 admin.users
//     })->name('users');
// });
//
// 注意：prefix 會自動加在網址最前面，name 會自動加在路由名稱最前面（通常要記得加上 .）
//
// 補充說明：
// 群組的 name（Route::name('admin.')）是名稱前綴，單一路由的 name（->name('users')）是名稱本身，
// 兩者會自動合併，這條路由的完整名稱就是 admin.users。
// 這樣可以讓你有組織地管理大量路由名稱，例如 route('admin.users')。

// Controllers（群組共用控制器）
// 如果一組路由都用同一個 controller，可以用 controller 方法指定：
// use App\Http\Controllers\OrderController;
//
// Route::controller(OrderController::class)->group(function () {
//     Route::get('/orders/{id}', 'show');   // 對應到 OrderController@show
//     Route::post('/orders', 'store');      // 對應到 OrderController@store
// });
//
// 好處：不用每條路由都寫完整的 [OrderController::class, 'show']，讓路由更簡潔、維護更方便。

// Subdomain Routing（子網域路由）
// 可以用 domain 方法定義子網域路由，子網域也能當作參數傳進來：
// Route::domain('{account}.example.com')->group(function () {
//     Route::get('/user/{id}', function (string $account, string $id) {
//         // $account 會是子網域（如 aaa），$id 是網址參數
//     });
// });
//
// 補充說明：
// 子網域就是網址前面那一段（如 aaa.example.com 的 aaa），
// 子網域路由可以根據不同子網域定義不同的路由，
// 並把子網域當作參數（如 $account）傳進 controller 或 closure function。
// 例如 aaa.example.com/user/123，$account 會是 aaa，$id 會是 123。
// 子網域路由要寫在 root domain 路由之前，才不會被覆蓋。
// 適合 SaaS 平台、企業分站、多帳號管理等情境。

// Route Model Binding（路由模型綁定）
// 只要型別提示是 Eloquent 模型，且變數名稱和路由參數一致，Laravel 會自動注入對應的模型物件：
// use App\Models\User;
//
// Route::get('/users/{user}', function (User $user) {
//     return $user->email; // $user 會是對應 ID 的 User 物件
// });
//
// 如果找不到對應的資料，Laravel 會自動回傳 404。
//
// Controller 也可以用：
// 先在route註冊路由
// use App\Http\Controllers\UserController;
//
// Route::get('/users/{user}', [UserController::class, 'show']);

// 在controller中使用
// use App\Models\User;
// public function show(User $user)
// {
//     return view('user.profile', ['user' => $user]);
// }
//
// 好處：不用自己寫 User::find($id)，更簡潔，自動處理 404，安全性更高。

// Soft Deleted Models（軟刪除模型）
// 預設情況下，隱式模型綁定不會抓出軟刪除的資料。
// 如果要包含軟刪除的資料，可以在路由後面加上 withTrashed()：
// use App\Models\User;
//
// Route::get('/users/{user}', function (User $user) {
//     return $user->email;
// })->withTrashed();
//
// 適合管理後台、審核、還原功能等情境，需要查詢已被軟刪除的資料。

// Customizing the Key（自訂主鍵）
// 可以在路由參數加上 :欄位名稱，讓模型綁定用該欄位查詢：
// use App\Models\Post;
//
// Route::get('/posts/{post:slug}', function (Post $post) {
//     return $post;
// });
//
// 也可以在模型裡覆寫 getRouteKeyName 方法，讓所有綁定都用指定欄位：
// public function getRouteKeyName(): string
// {
//     return 'slug';
// }
//
// 巢狀綁定與自動關聯（Scoping）：
// 巢狀綁定時，Laravel 會自動根據父模型的關聯來查詢子模型：
// use App\Models\User;
// use App\Models\Post;
//
// Route::get('/users/{user}/posts/{post:slug}', function (User $user, Post $post) {
//     return $post;
// });
// 這裡會自動用 $user->posts()->where('slug', ...) 查詢
//
// 強制啟用巢狀綁定：
// Route::get('/users/{user}/posts/{post}', function (User $user, Post $post) {
//     return $post;
// })->scopeBindings();
//
// 或整個群組：
// Route::scopeBindings()->group(function () { ... });
//
// 強制關閉巢狀綁定：
// Route::get('/users/{user}/posts/{post:slug}', function (User $user, Post $post) {
//     return $post;
// })->withoutScopedBindings();
//
// 好處：可以用 slug、uuid 等欄位查詢模型，巢狀路由自動根據父子關聯查詢，安全又方便。

// Implicit Enum Binding（隱式 Enum 綁定）
// 路由參數型別提示 Enum，只有合法 Enum 值才會進入 function，否則自動 404：
// use App\Enums\Category;
//
// Route::get('/categories/{category}', function (Category $category) {
//     return $category->value;
// });
//
// 只有 /categories/fruits 或 /categories/people 會進入 function，其他值會自動 404。
// 好處：不用自己驗證參數是否合法，參數自動轉成 Enum 物件，程式更安全、可讀性更高。

// Explicit Binding（顯式綁定）
// 你可以在 AppServiceProvider 的 boot 方法裡用 Route::model 明確指定參數對應的模型：
// use App\Models\User;
// use Illuminate\Support\Facades\Route;
//
// public function boot(): void
// {
//     Route::model('user', User::class);
// }
//
// 這樣所有 {user} 參數都會自動注入 User 模型物件。
// 如果找不到資料會自動回傳 404。
//
// 自訂解析邏輯：
// 可以用 Route::bind 自訂參數如何查詢模型，例如用 name 查找：
// public function boot(): void
// {
//     Route::bind('user', function (string $value) {
//         return User::where('name', $value)->firstOrFail();
//     });
// }
//
// 也可以在模型裡覆寫 resolveRouteBinding 方法來自訂查詢邏輯：
// public function resolveRouteBinding($value, $field = null)
// {
//     return $this->where('name', $value)->firstOrFail();
// }
//
// 巢狀綁定時可覆寫 resolveChildRouteBinding 方法：
// public function resolveChildRouteBinding($childType, $value, $field)
// {
//     return parent::resolveChildRouteBinding($childType, $value, $field);
// }

// 補充說明：
// 如果模型有自訂 resolveRouteBinding，route 只要型別提示對應模型即可，Laravel 會自動用自訂邏輯查詢。
// 例如 User 模型有自訂用 name 查找：
// Route::get('/users/{user}', function (User $user) {
//     // $user 會自動用 name 欄位查找
// });
// 你不用在 route 裡特別寫 :name，Laravel 會自動呼叫 resolveRouteBinding。

// -----------------------------------------------------------------------------
// 套用速率限制（Rate Limiter）到路由
// -----------------------------------------------------------------------------
// 使用 throttle middleware，名稱對應 AppServiceProvider 定義的 RateLimiter
// 例如：throttle:uploads-segment、throttle:login、throttle:uploads-advanced
// 可套用到單一路由或路由群組
// -----------------------------------------------------------------------------

// 單一路由範例
Route::post('/audio', function () {
    // ...
})->middleware('throttle:uploads-segment');

// 路由群組範例
Route::middleware(['throttle:uploads-segment'])->group(function () {
    Route::post('/audio', function () {
        // ...
    });
    Route::post('/video', function () {
        // ...
    });
});

// -----------------------------------------------------------------------------
// 若需強制用 Redis 管理速率限制，請在 bootstrap/app.php 設定：
// $middleware->throttleWithRedis();
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
// Fallback Route（後備路由）
// -----------------------------------------------------------------------------
// 當所有已定義的路由皆無法匹配時，Laravel 會自動執行此路由。
// 通常用於自訂 404 頁面或統一處理未定義路由的情境。
// 注意：此路由應放在所有路由定義的最後，否則會攔截掉後面的路由。
// 此路由會自動套用 web middleware group，也可額外指定其他 middleware。
// -----------------------------------------------------------------------------

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ErrorHandlingDemoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user', [UserController::class, 'index']);

Route::fallback(function () {
    // 這裡可以回傳自訂的 404 頁面
    return response()->view('errors.404', [], 404);
    // 或直接回傳訊息
    // return '找不到頁面', 404;
});

// -----------------------------------------------------------------------------
// 路由指定 middleware 範例
// -----------------------------------------------------------------------------
// 1. 單一路由：->middleware(EnsureTokenIsValid::class)
use App\Http\Middleware\EnsureTokenIsValid;
Route::get('/profile', function () {
    // ...
})->middleware(EnsureTokenIsValid::class);

// 2. 多個 middleware：->middleware([First::class, Second::class])
// Route::get('/', function () {
//     // ...
// })->middleware([First::class, Second::class]);

// 3. 用註冊名稱（建議）：->middleware('token.valid')
// Route::get('/profile', function () {
//     // ...
// })->middleware('token.valid');

// 4. 路由群組：Route::middleware(['auth', 'token.valid'])->group(...)
// Route::middleware(['auth', 'token.valid'])->group(function () {
//     Route::get('/dashboard', ...);
//     Route::get('/settings', ...);
// });
// -----------------------------------------------------------------------------

// ------------------------------------------------------------
// UserController 路由註冊範例
// ------------------------------------------------------------

// 顯示指定使用者的個人資料
// 當請求符合 /user/{id}，會自動呼叫 UserController 的 show 方法，並將 id 傳入
Route::get('/user/{id}', [UserController::class, 'show']);

// ------------------------------------------------------------
// ProvisionServer 單一動作控制器路由註冊範例
// ------------------------------------------------------------
use App\Http\Controllers\ProvisionServer;

// 佈建新伺服器的單一動作控制器
// 當收到 POST /server 請求時，會自動呼叫 ProvisionServer 的 __invoke 方法
Route::post('/server', ProvisionServer::class);

// ------------------------------------------------------------
// PhotoController 部分資源路由註冊範例（僅允許查詢）
// ------------------------------------------------------------
use App\Http\Controllers\PhotoController;

// 僅允許查詢照片列表與單一照片（不允許新增、編輯、刪除等敏感操作）
// 適用於前台公開查詢、API 查詢等情境，提升安全性與維護性
// 可用 only 或 except 寫法，效果相同，擇一註冊即可
Route::resource('photos', PhotoController::class)->only(['index', 'show']);
// 或：排除不需要的 action（效果相同，僅供參考）
Route::resource('photos', PhotoController::class)->except(['create', 'store', 'update', 'destroy']);
// 實務建議：團隊可根據語意偏好選擇 only 或 except，並加上註解說明。

/*
// 一行註冊所有 CRUD 路由，對應 PhotoController 的 index/create/store/show/edit/update/destroy 方法
// Route::resource('photos', PhotoController::class); // 僅供參考，實務上請依需求註冊

// 進階用法（僅供參考）：
// - 若需自訂找不到模型時的行為，可用 missing
// Route::resource('photos', PhotoController::class)
//     ->missing(function (Request $request) {
//         return Redirect::route('photos.index');
//     });
// - 若需支援軟刪除模型，可用 withTrashed
// Route::resource('photos', PhotoController::class)->withTrashed();
*/

// ------------------------------------------------------------
// Nested Resource（巢狀資源路由）註冊範例
// ------------------------------------------------------------
use App\Http\Controllers\PhotoCommentController;

// 巢狀資源路由：讓每張照片可有多個留言
// 產生 /photos/{photo}/comments/{comment} 等巢狀路由，並自動對應 PhotoCommentController
Route::resource('photos.comments', PhotoCommentController::class);

// ------------------------------------------------------------
// Scoping Resource Routes（巢狀資源路由自動綁定）註冊範例
// ------------------------------------------------------------
// 使用 scoped 方法可自動將子資源綁定到父資源，並可指定子資源用哪個欄位查詢
// 例如：/photos/{photo}/comments/{comment:slug}，會自動用 $photo->comments()->where('slug', ...) 查詢
Route::resource('photos.comments', PhotoCommentController::class)->scoped([
    'comment' => 'slug',
]);
// 若不指定欄位，預設用主鍵 id
Route::resource('photos.comments', PhotoCommentController::class)->scoped();
// 進階：可指定子資源用哪個欄位查詢
Route::resource('photos.comments', PhotoCommentController::class)->scoped(['comment' => 'slug']);

// ------------------------------------------------------------
// Shallow Nesting（淺層巢狀路由）註冊範例
// ------------------------------------------------------------
use App\Http\Controllers\CommentController;

// 淺層巢狀：只有 index/create/store 需要父層 ID，show/edit/update/destroy 只用子資源 ID
// 產生：
// GET    /photos/{photo}/comments           -> index
// GET    /photos/{photo}/comments/create    -> create
// POST   /photos/{photo}/comments           -> store
// GET    /comments/{comment}                -> show
// GET    /comments/{comment}/edit           -> edit
// PUT    /comments/{comment}                -> update
// DELETE /comments/{comment}                -> destroy
Route::resource('photos.comments', CommentController::class)->shallow();

// ------------------------------------------------------------
// Naming Resource Routes（自訂資源路由名稱）註冊範例
// ------------------------------------------------------------
// 可用 names 陣列自訂部分或全部資源路由名稱
// 例如將 create action 的路由名稱改為 photos.build，其餘維持預設
// 這是說：「把 /photos/create 這條路由的名稱，從預設的 photos.create 改成 photos.build。」
// 網址還是 /photos/create，controller 方法還是 create()，都沒變。
// 只有「路由名稱」變成 photos.build。
// 你在 Blade 或 PHP 裡要產生網址時：
// route('photos.build') // 會得到 /photos/create 這個網址
// 你要重導到新增照片頁時：
// return redirect()->route('photos.build');
Route::resource('photos', PhotoController::class)->names([
    'create' => 'photos.build',
]);
// 也可同時自訂多個 action 名稱
// Route::resource('photos', PhotoController::class)->names([
//     'index' => 'photos.list',
//     'create' => 'photos.build',
//     'show' => 'photos.detail',
// ]);

// ------------------------------------------------------------
// Naming Resource Route Parameters（自訂資源路由參數名稱）註冊範例
// ------------------------------------------------------------
use App\Http\Controllers\AdminUserController;

// 預設 Route::resource 會用資源名稱的單數型作為路由參數（如 users -> {user}）
// 可用 parameters 方法自訂參數名稱，例如改為 {admin_user}
Route::resource('users', AdminUserController::class)->parameters([
    'users' => 'admin_user',
]);
// 產生的 show 路由為 /users/{admin_user}

// ------------------------------------------------------------
// Singleton Resource Controllers（單例資源控制器）註冊範例
// ------------------------------------------------------------
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ThumbnailController;

// 單例資源：僅有一個實例（如 profile、thumbnail），不需帶 id
// 只產生 show/edit/update 路由，不產生 create/store/destroy
// 用在「某個資源在系統裡永遠只有一個」的情境，
// 也就是「單例資源」的 RESTful 路由。
Route::singleton('profile', ProfileController::class);
// 每個使用者只有一份個人資料，不會有多份 profile
// singleton 路由（如 /profile）適合用在「每個使用者有一份自己的單例資源」的情境，
// 但不是「全站只有一個」，而是「每個登入者各自有一個」。
// 產生：
// GET    /profile           -> show   profile.show
// GET    /profile/edit      -> edit   profile.edit
// PUT    /profile           -> update profile.update

// 單例資源可巢狀於標準資源下
Route::singleton('photos.thumbnail', ThumbnailController::class);
// 每張照片只有一個縮圖（thumbnail）
// 產生：
// GET    /photos/{photo}/thumbnail           -> show   photos.thumbnail.show
// GET    /photos/{photo}/thumbnail/edit      -> edit   photos.thumbnail.edit
// PUT    /photos/{photo}/thumbnail           -> update photos.thumbnail.update

// 若需支援 create/store/destroy，可用 creatable/destroyable
// Route::singleton('photos.thumbnail', ThumbnailController::class)->creatable();
// 產生：
// GET    /photos/{photo}/thumbnail/create    -> create  photos.thumbnail.create
// POST   /photos/{photo}/thumbnail           -> store   photos.thumbnail.store
// GET    /photos/{photo}/thumbnail           -> show    photos.thumbnail.show
// GET    /photos/{photo}/thumbnail/edit      -> edit    photos.thumbnail.edit
// PUT    /photos/{photo}/thumbnail           -> update  photos.thumbnail.update
// DELETE /photos/{photo}/thumbnail           -> destroy photos.thumbnail.destroy
// 若只需 destroy 路由，可用 destroyable
// Route::singleton('photos.thumbnail', ThumbnailController::class)->destroyable();

// ------------------------------------------------------------
// API Singleton Resources（API 單例資源控制器）註冊範例
// ------------------------------------------------------------
// apiSingleton：註冊 API 專用單例資源，不產生 create/edit 路由
Route::apiSingleton('profile', ProfileController::class);
// 產生：
// GET    /profile           -> show   profile.show
// PUT    /profile           -> update profile.update
// DELETE /profile           -> destroy profile.destroy

// 若需支援 store/destroy，可用 creatable
Route::apiSingleton('photos.thumbnail', ProfileController::class)->creatable();
// 產生：
// POST   /photos/{photo}/thumbnail           -> store   photos.thumbnail.store
// GET    /photos/{photo}/thumbnail           -> show    photos.thumbnail.show
// PUT    /photos/{photo}/thumbnail           -> update  photos.thumbnail.update
// DELETE /photos/{photo}/thumbnail           -> destroy photos.thumbnail.destroy

// ------------------------------------------------------------
// Middleware and Resource Controllers（資源路由與中介層）註冊範例
// ------------------------------------------------------------
// use App\Http\Controllers\UserController; // 已於檔案前方 use，這裡不需重複 use

// 1. 套用 middleware 於所有資源路由
Route::resource('users', UserController::class)
    ->middleware(['auth', 'verified']);
Route::singleton('profile', ProfileController::class)
    ->middleware('auth');

// 2. 只套用 middleware 於特定 action
Route::resource('users', UserController::class)
    ->middlewareFor('show', 'auth');
Route::apiResource('users', UserController::class)
    ->middlewareFor(['show', 'update'], 'auth');
Route::resource('users', UserController::class)
    ->middlewareFor('show', 'auth')
    ->middlewareFor('update', 'auth');
Route::apiResource('users', UserController::class)
    ->middlewareFor(['show', 'update'], ['auth', 'verified']);
// singleton/apiSingleton 也可用 middlewareFor
Route::singleton('profile', ProfileController::class)
    ->middlewareFor('show', 'auth');
Route::apiSingleton('profile', ProfileController::class)
    ->middlewareFor(['show', 'update'], 'auth');

// 3. 排除特定 action 的 middleware
Route::middleware(['auth', 'verified', 'subscribed'])->group(function () {
    Route::resource('users', UserController::class)
        ->withoutMiddlewareFor('index', ['auth', 'verified'])
        ->withoutMiddlewareFor(['create', 'store'], 'verified')
        ->withoutMiddlewareFor('destroy', 'subscribed');
});

// ------------------------------------------------------------
// Request 依賴注入於 route closure 範例
// ------------------------------------------------------------
// 你可以在路由閉包（closure）型別提示 Request，Laravel 會自動注入目前請求物件
// 範例：
// use Illuminate\Http\Request;
// Route::get('/', function (Request $request) {
//     // 這裡的 $request 會自動注入
//     $ip = $request->ip(); // 取得用戶 IP
//     $name = $request->input('name'); // 取得查詢字串或表單欄位
// });

// ------------------------------------------------------------
// Request 物件常用方法與範例（擴充）
// ------------------------------------------------------------
// 這些方法的來源
// Illuminate\Http\Request 是 Laravel 內建的「請求物件」。
// 它繼承自 Symfony 的 Request 類別，並加上 Laravel 自己的擴充功能。
// 你在 controller 或 route closure 注入 Request $request，就可以直接用這些方法。
// 取得目前請求路徑（不含網域、不含 query string）
// $uri = $request->path(); // 例如 foo/bar
//
// 判斷路徑是否符合指定模式（* 為萬用字元）
// if ($request->is('admin/*')) { /* ... */ }
//
// 判斷路由名稱是否符合指定模式（常用於命名路由）
// if ($request->routeIs('admin.*')) { /* ... */ }
//
// 取得完整網址
// $url = $request->url(); // 不含 query string
// $fullUrl = $request->fullUrl(); // 含 query string
//
// 加上/移除查詢參數
// $newUrl = $request->fullUrlWithQuery(['type' => 'phone']);
// $newUrl2 = $request->fullUrlWithoutQuery(['type']);
//
// 取得主機資訊
// $host = $request->host(); // 只回傳主機名稱
// $httpHost = $request->httpHost(); // 主機＋port
// $schemeAndHost = $request->schemeAndHttpHost(); // http(s)://主機
//
// 取得與判斷 HTTP 方法
// $method = $request->method(); // 取得 HTTP 動詞
// if ($request->isMethod('post')) { /* ... */ }

// ------------------------------------------------------------
// Request Header 操作範例路由
// ------------------------------------------------------------
// [Request Header 操作範例路由]
// 這個路由會呼叫 UserController 的 demoHeaderExample 方法，
// 示範如何取得 request header、判斷 header 是否存在、取得 Bearer Token。
Route::get('/user/header-demo', [UserController::class, 'demoHeaderExample']); 

// [Request IP 取得範例路由]
// 這個路由會呼叫 UserController 的 showIpExample 方法，
// 示範如何取得 request 的 ip() 與 ips()，並提醒安全性。
Route::get('/user/ip-demo', [UserController::class, 'showIpExample']);

// ------------------------------------------------------------
// Content Negotiation（內容協商）範例路由
// ------------------------------------------------------------
// 這個路由會呼叫 UserController 的 contentNegotiationExample 方法，
// 示範如何檢查 Accept header、判斷用戶端可接受的內容型態。
Route::get('/user/content-demo', [UserController::class, 'contentNegotiationExample']);

// ------------------------------------------------------------
// PSR-7 Request 實作範例路由
// ------------------------------------------------------------
// 這個路由會呼叫 UserController 的 psr7Example 方法，
// 示範如何 type-hint PSR-7 request 並取得資訊。
Route::get('/user/psr7-demo', [UserController::class, 'psr7Example']);

// ------------------------------------------------------------
// Request Input 取得範例路由
// ------------------------------------------------------------
// 這個路由會呼叫 UserController 的 inputExample 方法，
// 示範如何取得各種輸入資料（all、input、query、json、string、integer、boolean、array、date、enum...）。
Route::match(['get', 'post'], '/user/input-demo', [UserController::class, 'inputExample']);

// ------------------------------------------------------------
// Request Input Presence 取得與合併範例路由
// ------------------------------------------------------------
// 這個路由會呼叫 UserController 的 inputPresenceExample 方法，
// 示範如何判斷 input 是否存在、是否為空、合併 input 等。
Route::match(['get', 'post'], '/user/input-presence-demo', [UserController::class, 'inputPresenceExample']);

// ------------------------------------------------------------
// Old Input（舊輸入）範例路由
// ------------------------------------------------------------
// POST：送出表單並將 input 存入 session，重導回表單頁
Route::post('/user/old-input-demo', [UserController::class, 'oldInputExample']);
// GET：顯示表單頁，並用 old() 回填欄位
Route::get('/user/old-input-demo', [UserController::class, 'showOldInputForm']);

// ------------------------------------------------------------
// Cookies 取得範例路由
// ------------------------------------------------------------
// 這個路由會呼叫 UserController 的 cookieExample 方法，
// 示範如何取得加密 cookie。
Route::get('/user/cookie-demo', [UserController::class, 'cookieExample']);

// ------------------------------------------------------------
// Files（檔案上傳）範例路由
// ------------------------------------------------------------
// 這個路由會呼叫 UserController 的 fileExample 方法，
// 示範如何取得、驗證、儲存上傳檔案。
Route::post('/user/file-demo', [UserController::class, 'fileExample']);

// ------------------------------------------------------------
// HTTP Responses（HTTP 回應）範例路由
// ------------------------------------------------------------
// 回傳字串
Route::get('/user/response-demo/string', [UserController::class, 'responseString']);
// 回傳陣列（自動轉 JSON）
Route::get('/user/response-demo/array', [UserController::class, 'responseArray']);
// 回傳 Eloquent 模型（自動轉 JSON）
Route::get('/user/response-demo/model/{user}', [UserController::class, 'responseModel']);
// 回傳自訂 Response 實例，含 header
Route::get('/user/response-demo/custom', [UserController::class, 'responseCustom']);

// ------------------------------------------------------------
// Attaching Cookies to Responses（回應附加 Cookie）範例路由
// ------------------------------------------------------------
// 直接附加 cookie 到回應
Route::get('/user/response-cookie', [UserController::class, 'responseCookie']);
// 用 Cookie facade queue cookie
Route::get('/user/response-cookie-queue', [UserController::class, 'responseCookieQueue']);
// 讓 cookie 立即過期
Route::get('/user/response-cookie-expire', [UserController::class, 'responseCookieExpire']);

// ------------------------------------------------------------
// Redirects（重導）範例路由
// ------------------------------------------------------------
// 最簡單的重導
Route::get('/user/redirect-demo/simple', [UserController::class, 'redirectSimple']);
// 重導回前一頁（常用於表單驗證失敗）
Route::post('/user/redirect-demo/back', [UserController::class, 'redirectBack']);
// 重導到命名路由
Route::get('/user/redirect-demo/route', [UserController::class, 'redirectRoute']);
// 重導到命名路由並帶參數
Route::get('/user/redirect-demo/route-param/{id}', [UserController::class, 'redirectRouteParam']);
// 重導到命名路由並帶 Eloquent 模型
Route::get('/user/redirect-demo/route-model/{user}', [UserController::class, 'redirectRouteModel']);

// ------------------------------------------------------------
// Redirecting to Controller Actions / External Domains 範例路由
// ------------------------------------------------------------
// 重導到控制器 action
Route::get('/user/redirect-demo/action', [UserController::class, 'redirectAction']);
// 重導到控制器 action 並帶參數
Route::get('/user/redirect-demo/action-param', [UserController::class, 'redirectActionParam']);
// 重導到外部網址
Route::get('/user/redirect-demo/away', [UserController::class, 'redirectAway']);

// ------------------------------------------------------------
// Redirecting With Flashed Session Data 範例路由
// ------------------------------------------------------------
// 重導並閃存訊息
Route::post('/user/redirect-flash', [UserController::class, 'redirectWithFlash']);
// 重導並閃存 input
Route::post('/user/redirect-flash-input', [UserController::class, 'redirectWithInput']);

// ------------------------------------------------------------
// Other Response Types（View/JSON/Download/File）範例路由
// ------------------------------------------------------------
// 回傳 view 並自訂 header
Route::get('/user/response-view', [UserController::class, 'responseView']);
// 回傳 JSON
Route::get('/user/response-json', [UserController::class, 'responseJson']);
// 回傳 JSONP
Route::get('/user/response-jsonp', [UserController::class, 'responseJsonp']);
// 檔案下載
Route::get('/user/response-download', [UserController::class, 'responseDownload']);
// 檔案直接顯示
Route::get('/user/response-file', [UserController::class, 'responseFile']);

// ------------------------------------------------------------
// Streamed Responses（串流回應）範例路由
// ------------------------------------------------------------
// 一般串流回應
Route::get('/user/response-stream', [UserController::class, 'responseStream']);
// 串流 JSON 回應
Route::get('/user/response-stream-json', [UserController::class, 'responseStreamJson']);
// SSE 事件串流
Route::get('/user/response-event-stream', [UserController::class, 'responseEventStream']);
// 串流下載
Route::get('/user/response-stream-download', [UserController::class, 'responseStreamDownload']);

// ------------------------------------------------------------
// Response Macro（自訂回應輔助方法）範例路由
// ------------------------------------------------------------
// 回傳大寫字串（自訂 macro）
Route::get('/user/response-caps', [UserController::class, 'responseCaps']);

// -----------------------------------------------------------------------------
// [View 範例路由] 回傳 greeting 視圖，傳遞 name 變數
// -----------------------------------------------------------------------------
Route::get('/greeting', function () {
    // 傳遞資料給 greeting.blade.php，$name 會被渲染
    return view('greeting', ['name' => 'James']);
});

// -----------------------------------------------------------------------------
// [View Facade 實作範例]
// -----------------------------------------------------------------------------
use Illuminate\Support\Facades\View;
Route::get('/greeting-facade', function () {
    return View::make('greeting', ['name' => 'James']);
});

// -----------------------------------------------------------------------------
// [巢狀視圖範例] resources/views/admin/profile.blade.php
// -----------------------------------------------------------------------------
Route::get('/admin/profile', function () {
    $data = ['name' => 'Admin'];
    return view('admin.profile', $data);
});

// -----------------------------------------------------------------------------
// [判斷視圖是否存在範例]
// -----------------------------------------------------------------------------
Route::get('/check-view', function () {
    if (View::exists('admin.profile')) {
        return 'admin.profile 視圖存在';
    }
    return 'admin.profile 視圖不存在';
});

// --------------------------------------------------------------------------
// [Blade 資料顯示範例路由]
// 傳遞 name 變數給 Blade 視圖，供前端顯示與測試
// --------------------------------------------------------------------------
Route::get('/blade-demo', function () {
    return view('blade-demo', ['name' => 'Blade 用戶']);
});


// =========================
// 驗證教學範例路由
// =========================
use App\Http\Controllers\ValidationDemoController;
Route::get('/demo/validation/create', [ValidationDemoController::class, 'create']);
Route::post('/demo/validation', [ValidationDemoController::class, 'store']);

// =========================
// 手動 Validator 教學範例路由
// =========================
use App\Http\Controllers\ManualValidatorDemoController;
Route::get('/demo/validator/create', [ManualValidatorDemoController::class, 'create']);
Route::post('/demo/validator', [ManualValidatorDemoController::class, 'store']);

// 錯誤處理示範路由
Route::prefix('demo')->group(function () {
    Route::get('/basic-error-handling', [ErrorHandlingDemoController::class, 'basicErrorHandling']);
    Route::get('/custom-exception', [ErrorHandlingDemoController::class, 'customException']);
    Route::get('/non-reportable', [ErrorHandlingDemoController::class, 'nonReportableException']);
    Route::get('/abort', [ErrorHandlingDemoController::class, 'abortDemo']);
    Route::get('/conditional', [ErrorHandlingDemoController::class, 'conditionalErrorHandling']);
    Route::get('/best-practices', [ErrorHandlingDemoController::class, 'bestPractices']);
});

// 測試 404 錯誤頁面
Route::get('/test-404', function () {
    abort(404, '這是測試的 404 錯誤');
});

// 測試自訂錯誤頁面
Route::get('/test-invalid-order', function () {
    throw new App\Exceptions\InvalidOrderException('測試無效訂單', 'TEST-123', 422);
}); 