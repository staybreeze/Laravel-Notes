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

Route::get('/greeting', function () {
    return 'Hello World';
});

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
