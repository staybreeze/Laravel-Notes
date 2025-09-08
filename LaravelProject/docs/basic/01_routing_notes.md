# *Laravel 路由（Routing) 筆記*

---

## 1. **路由基礎**

### 1.1 *基本路由*

```php
use Illuminate\Support\Facades\Route;

// 最簡單的路由，直接對應 URI 與 Closure
Route::get('/greeting', function () {
    return 'Hello World'; // 回傳字串
});
```

**比喻**：像是在地圖上標記一個地點，訪問這個地點就會觸發對應的行為。

---

### 1.2 *路由檔案位置*

- 所有路由都定義在 `routes/` 目錄下：

    - `web.php`：網頁介面路由，預設 **有 session、CSRF** 等功能。
    - `api.php`：API 路由，預設 **無 session、CSRF**，URI 會自動加上 `/api` 前綴。
    - 其他：`console.php`（Artisan 指令）、`channels.php`（Broadcast 頻道）。

```php
// 典型 web 路由
Route::get('/user', [UserController::class, 'index']);
```

---

### 1.3 *API 路由*

- 使用 `php artisan install:api` 可快速建立 API 架構（含 `Sanctum 驗證`、`routes/api.php`）。

- API 路由預設：
    - **無** session 狀態
    - **無** CSRF 保護
    - URI 會自動加上 `/api` 前綴

- API 路由預設*沒有 session 狀態*，是因為 API 通常是「__無狀態__」設計，
  - `每次請求都獨立，不會儲存使用者資料或登入狀態`，
  - 這樣可以提升效能、擴充性，也方便跨平台存取。

```php
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
```

---

## 2. **路由方法與 HTTP 動詞**

- Laravel 支援所有常見 HTTP 動詞：
- `get`、`post`、`put`、`patch`、`delete`、`options`

- 可用 `match` 註冊多個動詞、`any` 註冊所有動詞：

```php
Route::match(['get', 'post'], '/', function () {
    // ...
});

// 當收到任何 HTTP 方法（GET、POST、PUT、DELETE 等）對 '/' 路徑的請求時，執行這個 function
Route::any('/', function () {
    // ... 
});
```
*注意*：多個相同 URI 的路由，應 __先__ 定義 `get/post/put/patch/delete/options`，__再__ 定義 `any/match/redirect`，避免覆蓋。

---

## 3. **依賴注入與請求物件**

- 路由 `callback` 可 *型別提示* 依賴，Laravel 會自動注入：

```php
use Illuminate\Http\Request;
Route::get('/users', function (Request $request) {
    // ...
});
```

---

## 4. **CSRF 保護**

- *web 路由* 的 `POST/PUT/PATCH/DELETE` 表單 __必須帶 CSRF token__：

```html
<form method="POST" action="/profile">
    @csrf
    ...
</form>
```

---

## 5. **重導與視圖路由**

### 5.1 *重導路由*

```php
Route::redirect('/here', '/there'); // 302 預設
Route::redirect('/here', '/there', 301); // 自訂狀態碼
Route::permanentRedirect('/here', '/there'); // 301
```

---

### 5.2 *視圖路由*

```php
Route::view('/welcome', 'welcome'); // 設定 /welcome 路由，直接回傳 welcome 視圖，不帶參數
Route::view('/welcome', 'welcome', ['name' => 'Taylor']); // 設定 /welcome 路由，回傳 welcome 視圖，並傳入 name 參數（值為 'Taylor'）
```

---

## 6. **列出所有路由**

```bash
php artisan route:list
php artisan route:list -v   # 顯示 middleware
php artisan route:list --path=api  # 篩選路徑
php artisan route:list --except-vendor  # 排除套件路由
```

---

## 7. **路由自訂與分組**

### 7.1 *路由檔案自訂*

- 可在 `bootstrap/app.php` 透過 `withRouting` 新增**自訂路由檔**。
- 也可完全`自訂註冊流程`（__using 參數__）。

---

### 7.2 *路由群組（Group）*

- 可共用 `middleware、prefix、name、domain` 等屬性。

- `middleware`：可設定一組**中介層**（如 `auth`），保護群組內所有路由。
- `prefix`：可設定路由**前綴**（如 `admin`），讓群組內路由網址都加上 `/admin`。
- `name()`：用來給路由**命名**，方便用 `route('admin.users')` 產生網址或做跳轉。
- `domain`：可以設定路由只在**特定網域**下生效，例如 `admin.example.com`。

```php
Route::middleware(['auth'])      // 設定 auth 中介層，群組內路由都需驗證
    ->prefix('admin')            // 路由網址前綴 /admin
    ->name('admin.')             // 路由名稱前綴 admin.
    ->group(function () {
        Route::get('/users', function () {})
            ->name('users');     // 路由名稱為 admin.users
    });
    // 這段程式碼會讓 /admin/users 路由必須通過 auth 中介層驗證，路由名稱為 admin.users，網址前綴為 /admin。
```

---

### 7.3 *Controller 群組*

```php
Route::controller(OrderController::class)->group(function () {
    Route::get('/orders/{id}', 'show');   // 進入 /orders/{id} 會呼叫 OrderController 的 show 方法
    Route::post('/orders', 'store');      // POST /orders 會呼叫 OrderController 的 store 方法
});
```

---

### 7.4 *子網域路由*

```php
Route::domain('{account}.example.com')->group(function () {
    Route::get('/user/{id}', function ($account, $id) {
        // $account 會自動取得子網域名稱
        // $id 取得路由參數
        // 這裡可以根據不同帳號子網域做不同處理
    });
});
```

---

## 8. **路由參數**

### 8.1 *必填參數*

```php
Route::get('/user/{id}', function ($id) {
    return 'User '.$id;
});
```

---

### 8.2 *選填參數*

```php
Route::get('/user/{name?}', function ($name = 'John') {
    return $name;
});
```

<!-- 
之所以是選填，是因為路由參數 {name?} 加了問號，
代表這個參數是選填，
而 $name = 'John' 是設定預設值，
如果沒傳參數就會用 'John'。
兩者搭配才能做到「選填且有預設值」。 
-->

---

### 8.3 *正則約束*

```php
Route::get('/user/{id}', function ($id) {})->where('id', '[0-9]+');
Route::get('/user/{name}', function ($name) {})->where('name', '[A-Za-z]+');
```

- 也可用 `whereNumber`、
        `whereAlpha`、
        `whereAlphaNumeric`、
        `whereUuid`、
        `whereUlid`、
        `whereIn` 等輔助方法，**限制**路由參數格式。

---

### *8.4 全域參數約束*

```php
// AppServiceProvider::boot()
// pattern 是用來設定路由參數的格式限制（正則表達式）
Route::pattern('id', '[0-9]+'); // 全域限制所有 id 路由參數只能是數字

```

---

### 8.5 *編碼斜線*

- 允許最後一段參數包含 `/`：

```php
Route::get('/search/{search}', function ($search) {
    return $search;
})->where('search', '.*'); // 限制 search 參數可以是任何內容（包含斜線等特殊字元）
// 在正則表達式裡，. 代表「任意一個字元」
// .* 代表「任意長度的任意字元」
```

---

## 9. **命名路由**

- *方便產生 URL 或重導*：

```php
Route::get('/user/profile', function () {})->name('profile');
$url = route('profile');
return redirect()->route('profile');
```
- *傳遞參數*：

```php
Route::get('/user/{id}/profile', function ($id) {})->name('profile');
$url = route('profile', ['id' => 1, 'photos' => 'yes']); // /user/1/profile?photos=yes
```

---

## 10. **路由模型綁定（Model Binding）**

### 10.1 *隱式綁定*

```php
use App\Models\User;

Route::get('/users/{user}', function (User $user) {
    return $user->email;
});
```
- **參數名稱** 需與 **URI 段落** 一致，Laravel 會 **自動注入** 對應 Model。
- 若找不到資料，會自動回傳 `404`。

---

### 10.2 *自訂主鍵*

```php
// 這段程式碼會讓 /posts/{slug} 路由自動根據 slug 欄位查詢 Post 模型，
// 如果你在 Post 模型裡覆寫 getRouteKeyName()，就能全域指定用哪個欄位作為路由主鍵。
// slug 原本是英文單字，意思是「簡短且有意義的字串」，
// 在網頁開發裡，通常指用來代表資料、放在網址裡的唯一識別字串

// 這個路由會根據 slug 欄位查詢 Post 模型
Route::get('/posts/{post:slug}', function (Post $post) {
    return $post;
});
// 假設有一筆資料：Post::create(['title' => 'Hello', 'slug' => 'hello-world']);

// 使用網址 /posts/hello-world
// Laravel 會自動用 slug = 'hello-world' 查詢 Post，並注入到 $post 參數
```

<!-- 
這段程式碼會讓 /posts/{post} 路由自動根據 {post} 參數查詢 Post 模型，
預設會用 id 欄位查詢，
例如 /posts/1 會查詢 id = 1 的 Post，
然後把查到的 Post 物件注入到 $post 參數。 
-->

<!-- 
如果 {post} 是字串（例如 /posts/hello-world），
Laravel 會用 id = 'hello-world' 查詢 Post，
但通常查不到資料，因為 id 欄位是數字型，
除非你覆寫 getRouteKeyName() 讓它用 slug 查詢，
否則字串參數會查不到結果，回傳 404。 
-->

```php
// Model 可覆寫 getRouteKeyName() 來全域指定主鍵
// 全域範圍只限於該模型（例如 Post）
class Post extends Model
{
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
```

<!-- 
路由主鍵是指在路由參數裡用來查詢模型的欄位，
預設是資料表的 id 欄位，
但你可以自訂（如 slug），
讓 Laravel 根據你指定的主鍵自動查詢資料，
並把結果注入到控制器或閉包參數。 
-->

---

### 10.3 *巢狀綁定與 ScopeBindings*

```php
use App\Models\User;
use App\Models\Post;

// 這個路由會自動以 user->posts 關聯查詢 post
Route::get('/users/{user}/posts/{post:slug}', function (User $user, Post $post) {
    return $post;
});

Route::scopeBindings()->group(function () {
    // 在這個群組內的路由都會啟用 scope bindings
    // 例如：
    Route::get('/users/{user}/posts/{post:slug}', function (User $user, Post $post) {
        return $post;
    });
});
// 詳細運作說明：

// 1. 路由 `/users/{user}/posts/{post:slug}` 被定義在 `scopeBindings()` 群組內。
// 2. 當你訪問 `/users/1/posts/hello-world` 時：
//    - `{user}` 會自動解析為 id=1 的 User 模型。
//    - `{post:slug}` 會用 slug='hello-world' 查詢 Post 模型。
// 3. 因為啟用 `scopeBindings()`，Laravel 會用 `$user->posts()->where('slug', 'hello-world')` 查詢 Post，
//    - 只會找到屬於該 User 的 Post（而不是所有 Post）。
// 4. 如果該 User 沒有 slug 為 `hello-world` 的 Post，則會回傳 404。
// 5. 如果有，則將該 Post 注入到 `$post` 參數，並回傳。

// 這樣可以確保子資源（Post）一定隸屬於父資源（User），  
// 避免跨使用者存取資料，提升安全性與一致性。

---

// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // 定義 posts 關聯：一個 User 有多個 Post
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

---

// app/Models/Post.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // 指定路由主鍵為 slug
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
```

---

### 10.4 *明確綁定與自訂解析邏輯*

```php
// AppServiceProvider::boot()
Route::model('user', User::class); // 讓 {user} 路由參數自動解析為 User 模型（預設用主鍵查詢）

Route::bind('user', function ($value) {
    // 自訂解析邏輯：用 name 欄位查詢 User
    return User::where('name', $value)->firstOrFail();
});
```

---

- Model 也可覆寫 `resolveRouteBinding()` 來自訂解析。

```php
class User extends Model
{
    public function resolveRouteBinding($value, $field = null)
    {
        // 例如用 name 欄位查詢
        return $this->where('name', $value)->firstOrFail();
    }
}
```

---

### 10.5 *Enum 綁定*

```php
enum Category: string {
    case Fruits = 'fruits';
    case People = 'people';
}

Route::get('/categories/{category}', function (Category $category) {
    // 路由參數 {category} 會自動解析成 Category enum
    return $category->value; // 回傳 enum 的值（如 'fruits' 或 'people'）
});
```

---

## 11. **Fallback 路由**

- 當`無`其他路由 *符合時* 執行：

```php
Route::fallback(function () {
    // 當所有路由都不符合時，會執行這個 fallback 路由
    // 通常用來回傳 404 或自訂錯誤頁面
    return response()->view('errors.404', [], 404);
});
```

---

## 12. **流量限制（Rate Limiting）**

- 在 `AppServiceProvider::boot()` 註冊：

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

// 設定 API 的速率限制，每分鐘最多 60 次，依使用者 id 或 IP 區分
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)
    ->by($request->user()?->id ?: $request->ip());
// ?-> 是 PHP 的 「Null Safe Operator」，代表「如果物件存在就取屬性，否則回傳 null」。

// a ?: b 是 「Elvis operator」，
// 判斷的是「a 是否為 false 值」（包含 null、false、0、空字串等），
// 不是只判斷 null，只是此例 null，所以才會說「如果左邊為 null 或 false，就用右邊的值」。

//  a ?? b 是 「null 合併運算子」，只會在 a 為 null 時才用 b。
});
```

---

- 路由加上 `throttle middleware`：

```php
// throttle:api 會自動套用你在 RateLimiter 註冊的 api 流量限制規則
Route::middleware(['throttle:api'])->group(function () {
    Route::post('/audio', function () {
        // 這個路由會套用 api 速率限制（每分鐘最多 60 次）
    });
});
```
- 支援 `Redis` 儲存流量限制，可提升效能與分散式環境下的一致性。

---

## 13. **表單方法偽裝（Method Spoofing）**

- HTML form 不支援 `PUT/PATCH/DELETE`，需加 `_method` 欄位：
```html
<form action="/example" method="POST">
    <input type="hidden" name="_method" value="PUT">
    @csrf
</form>
```
- Blade 可用 `@method('PUT')`。

---

## 14. **取得目前路由資訊**

```php
use Illuminate\Support\Facades\Route;

$route = Route::current();             // 取得目前的 Route 物件
$name = Route::currentRouteName();     // 取得目前路由名稱
$action = Route::currentRouteAction(); // 取得目前路由對應的 Controller@method
```

---

## 15. **CORS 跨來源資源共享**

- Laravel 內建 __HandleCors middleware__，自動處理 `OPTIONS 請求`。

  - 會 *自動回應* `瀏覽器`發出的 __OPTIONS 預檢請求__，並 *設定 CORS 標頭*（如 Access-Control-Allow-Origin、Access-Control-Allow-Methods 等），讓 _跨域 API 請求_ 能被`瀏覽器` *安全地送出與接收*。

  - _瀏覽器_ 會在跨域請求時先發送 `OPTIONS` 預檢請求給 API，
    _API 端_（Laravel）`回應並設定 CORS 標頭`，
    _瀏覽器_ `收到正確標頭後`，才會`繼續發送`真正的 API 請求。
    這是瀏覽器的安全機制，Laravel 只是負責回應和設定標頭，讓跨域請求能被瀏覽器允許。

- 可用 `php artisan config:publish cors` 產生 `cors.php` 設定檔。
  - *自訂* CORS（跨域）相關設定

---

## 16. **路由快取**

- 生產環境建議快取路由，加速啟動：

```bash
php artisan route:cache
php artisan route:clear
```
*比喻*：像是把地圖路線預先畫好，查詢更快，但每次新增路由都要重新快取。

---