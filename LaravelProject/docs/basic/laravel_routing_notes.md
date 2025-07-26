# *Laravel 路由（Routing)*

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

### 1.2 *路由檔案位置*
- 所有路由都定義在 `routes/` 目錄下：
    - `web.php`：網頁介面路由，預設 **有 session、CSRF** 等功能。
    - `api.php`：API 路由，預設 **無 session、CSRF**，URI 會自動加上 `/api` 前綴。
    - 其他：`console.php`（Artisan 指令）、`channels.php`（Broadcast 頻道）。

```php
// 典型 web 路由
Route::get('/user', [UserController::class, 'index']);
```

### 1.3 *API 路由*
- 使用 `php artisan install:api` 可快速建立 API 架構（含 Sanctum 驗證、routes/api.php）。
- API 路由預設：
    - **無** session 狀態
    - **無** CSRF 保護
    - URI 會自動加上 `/api` 前綴

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
Route::any('/', function () {
    // ...
});
```
*注意*：多個相同 URI 的路由，應先定義 `get/post/put/patch/delete/options`，再定義 `any/match/redirect`，避免覆蓋。

---

## 3. **依賴注入與請求物件**

- 路由 callback 可 *型別提示* 依賴，Laravel 會自動注入：

```php
use Illuminate\Http\Request;
Route::get('/users', function (Request $request) {
    // ...
});
```

---

## 4. **CSRF 保護**

- *web 路由* 的 `POST/PUT/PATCH/DELETE` 表單必須帶 CSRF token：

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

### 5.2 *視圖路由*

```php
Route::view('/welcome', 'welcome');
Route::view('/welcome', 'welcome', ['name' ='Taylor']);
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
- 可在 `bootstrap/app.php` 透過 `withRouting` 新增自訂路由檔。
- 也可完全自訂註冊流程（using 參數）。

### 7.2 *路由群組（Group）*
- 可共用 `middleware、prefix、name、domain` 等屬性。

```php
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', function () {})->name('users');
});
```

### 7.3 *Controller 群組*
```php
Route::controller(OrderController::class)->group(function () {
    Route::get('/orders/{id}', 'show');
    Route::post('/orders', 'store');
});
```

### 7.4 *子網域路由*
```php
Route::domain('{account}.example.com')->group(function () {
    Route::get('/user/{id}', function ($account, $id) {
        // ...
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

### 8.2 *選填參數*
```php
Route::get('/user/{name?}', function ($name = 'John') {
    return $name;
});
```

### 8.3 *正則約束*
```php
Route::get('/user/{id}', function ($id) {})->where('id', '[0-9]+');
Route::get('/user/{name}', function ($name) {})->where('name', '[A-Za-z]+');
```
- 也可用 `whereNumber、whereAlpha、whereAlphaNumeric、whereUuid、whereUlid、whereIn` 等輔助方法。

### *8.4 全域參數約束*
```php
// AppServiceProvider::boot()
Route::pattern('id', '[0-9]+');
```

### 8.5 *編碼斜線*
- 允許最後一段參數包含 `/`：
```php
Route::get('/search/{search}', function ($search) {
    return $search;
})->where('search', '.*');
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
$url = route('profile', ['id' =1, 'photos' ='yes']); // /user/1/profile?photos=yes
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
- 若找不到資料，會自動回傳 404。

### 10.2 *自訂主鍵*
```php
Route::get('/posts/{post:slug}', function (Post $post) {
    return $post;
});
// Model 可覆寫 getRouteKeyName() 來全域指定主鍵
```

### 10.3 *巢狀綁定與 ScopeBindings*
```php
Route::get('/users/{user}/posts/{post:slug}', function (User $user, Post $post) {
    return $post;
});
// 會自動以 user->posts 關聯查詢 post
Route::scopeBindings()->group(function () {
    // ...
});
```

### 10.4 *明確綁定與自訂解析邏輯*
```php
// AppServiceProvider::boot()
Route::model('user', User::class);
Route::bind('user', function ($value) {
    return User::where('name', $value)->firstOrFail();
});
```
- Model 也可覆寫 `resolveRouteBinding()` 來自訂解析。

### 10.5 *Enum 綁定*
```php
enum Category: string { case Fruits = 'fruits'; case People = 'people'; }
Route::get('/categories/{category}', function (Category $category) {
    return $category->value;
});
```

---

## 11. **Fallback 路由**

- 當無其他路由 *符合時* 執行：
```php
Route::fallback(function () {
    // ...
});
```

---

## 12. **流量限制（Rate Limiting）**

- 在 `AppServiceProvider::boot()` 註冊：
```php
use Illuminate\Cache\RateLimiting\Limit;
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```
- 路由加上 `throttle middleware`：
```php
Route::middleware(['throttle:api'])->group(function () {
    Route::post('/audio', function () {});
});
```
- 支援 Redis 儲存流量限制。

---

## 13. **表單方法偽裝（Method Spoofing）**

- HTML form 不支援 PUT/PATCH/DELETE，需加 `_method` 欄位：
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
$route = Route::current();
$name = Route::currentRouteName();
$action = Route::currentRouteAction();
```

---

## 15. **CORS 跨來源資源共享**

- Laravel 內建 HandleCors middleware，自動處理 `OPTIONS 請求`。
- 可用 `php artisan config:publish cors` 產生 cors.php 設定檔。

---

## 16. **路由快取**

- 生產環境建議快取路由，加速啟動：
```bash
php artisan route:cache
php artisan route:clear
```
*比喻*：像是把地圖路線預先畫好，查詢更快，但每次新增路由都要重新快取。

---