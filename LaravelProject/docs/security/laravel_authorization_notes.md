# *Laravel 授權（Authorization）、Gate、Policy 概念說明*

- **Authorization（授權）**：
  - 指的是「*判斷某個使用者是否有權限執行某個動作*」的機制。
  - 在 Laravel 裡，授權是獨立於「*認證（Authentication）*」的第二道安全防線。

- **Gate**：
  - 適合定義「*單一動作*」的*授權規則*（如 'update-post'、'view-dashboard'）。
  - 用 *closure（匿名函式）*描述授權邏輯，通常用於簡單、臨時、或不特定模型的授權。
  - 每個 Gate 都是獨立的，邏輯分散。

- **Policy**：
  - 適合「*針對某個模型*」集中管理多個*授權動作*（如 view、create、update、delete...）。
  - 是一個 PHP 類別，裡面*每個方法對應一個授權動作*。
  - 維護性高，邏輯集中，適合大型或團隊專案。

---

| 概念         | 用途/說明                                                                 |
|--------------|--------------------------------------------------------------------------|
| Authorization| 判斷「誰能做什麼」的機制，Laravel 授權系統的總稱                         |
| Gate         | 定義單一動作的授權規則，適合簡單/臨時/分散的授權                         |
| Policy       | 針對模型集中管理多個動作的授權規則，適合複雜/大型/可維護性需求           |

- **Gate 和 Policy** 都是「授權」的實作方式，可以混用。
- *Gate* 適合小型、臨時、單一動作；
  *Policy* 適合多動作、模型導向、團隊維護。
- Laravel 會 *自動* 幫你把 Policy 方法當成 Gate 來用，讓你用同一套 API（如 can、authorize）呼叫。

---

# Laravel *授權（Authorization）完整筆記：Gate 與 Policy*

## 1. **介紹**
Laravel 除了內建 *認證（Authentication）* 功能，也提供簡單且有組織的 *授權（Authorization）* 機制，讓你能針對特定 *資源* 或 *動作* 進行權限控管。

- **Gate**：適合用於「與模型*無關*」的授權（如後台儀表板存取權限）。
- **Policy**：適合用於「與模型*有關*」的授權（如文章的新增、編輯、刪除）。
  - 註：這裡的「模型」指的是 Laravel 的 Model（Eloquent Model），即 `app/Models/` 目錄下的資料表對應類別。

Gate    類似    *路由的 closure*   ，
Policy  類似    *controller*      ，兩者可混用。

---

## 2. **Gate（閘門）**
### 2.1 *Gate 定義*
Gate 是一個 **closure**，通常在 `App\Providers\AppServiceProvider` 的 `boot` 方法中用 Gate facade 定義。

```php
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Post;

public function boot(): void
{
    Gate::define('update-post', function (User $user, Post $post) {
        return $user->id === $post->user_id;
    });
}
```

也可用 *class callback*：
```php
Gate::define('update-post', [PostPolicy::class, 'update']);
    // app/Policies/PostPolicy.php
   namespace App\Policies;

   use App\Models\User;
   use App\Models\Post;

   class PostPolicy
   {
       public function update(User $user, Post $post)
       {
           // 你的授權邏輯，例如：
           return $user->id === $post->user_id;
       }
   }
```

### 2.2 *Gate 授權檢查*
- `Gate::allows('ability', $arguments)`
  - 用途：判斷目前登入的使用者是否有某個權限（ability）。
  - **允許時回傳 true，否則 false**。
  - 語法說明：`'ability'` 是**權限名稱**（如 'update-post'），
            `$arguments` 是要**傳給授權邏輯的資料**（如某個 model 實例）。
  - 範例：
  ```php
  `Gate::allows('update-post', $post)` // 檢查目前 user 是否能更新 $post
  ```
  - 回傳值：回傳**布林值（true/false）**，通常用於 if 判斷、流程控制。

- `Gate::denies('ability', $arguments)`
  - 用途：判斷目前登入的使用者是否**沒有**某個權限。
  - **拒絕時回傳 true，否則 false**。
  - 語法說明：`'ability'` 是權限名稱，`$arguments` 是要傳給授權邏輯的資料。
  - 範例：
    ```php
    `Gate::denies('delete-post', $post)` // 檢查目前 user 是否不能刪除 $post
    ```
  - 回傳值：回傳**布林值（true/false）**，通常用於 if 判斷、流程控制。

- `Gate::authorize('ability', $arguments)`
  - 用途：強制授權檢查，若不通過會直接丟出 **403 Forbidden 例外（中斷流程）**。
  - 常用於 Controller 需強制授權時。
  - 語法說明：`'ability'` 是**權限名稱**，
            `$arguments` 是要**傳給授權邏輯的資**。
  - 範例：
    ```php
    `Gate::authorize('update-post', $post)` // 不通過會直接 403
    ```
  - 回傳值(*跟其他人不同*)：通過時回傳 true，不通過時直接丟出 403 例外，適合「必須通過授權才可繼續」的情境。

- `Gate::forUser($user)->allows(...)`
  - 用途：檢查**指定使用者**是否有某個權限，而不是目前登入的 user。
  - 常用於**管理員操作、批次檢查**。
  - 語法說明：`$user` 是要檢查的 **User 實例**，後面語法同上。
  - 範例：
    ```php
    `Gate::forUser($user)->allows('update-post', $post)` // 檢查 $user 是否能更新 $post
    ```
  - 回傳值：回傳**布林值（true/false）**，通常用於 if 判斷、流程控制。

- `Gate::any(['a', 'b'], $arg)` / `Gate::none(['a', 'b'], $arg)`
  - 用途：`any` 只要有其中一個權限通過就回傳 true；
         `none` 需全部都不通過才回傳 true。
  - 適合**複合權限判斷**。
  - 語法說明：`['a', 'b']` 是**權限名稱陣列**，
            `$arg` 是要傳給**授權邏輯的資料**。
  - 範例：
    ```php
    `Gate::any(['edit-post', 'delete-post'], $post)` // 只要有一個權限通過
    `Gate::none(['edit-post', 'delete-post'], $post)` // 兩個都沒權限才通過
    ```
  - 回傳值：回傳**布林值（true/false）**，通常用於 if 判斷、流程控制。

- #### **參數怎麼決定？**
  - 第一個參數 `'ability'`：
    就是你定義 Gate 或 Policy 時的*權限名稱（如 'update-post'）*。

  - 第二個參數 `$arguments`：
    是你*授權邏輯需要的資料*，通常是「要被操作的 Model 實例」。
  - Laravel 會自動把目前*登入的 user 當作 callback 的第一個參數*，你只需傳剩下的（通常是一個 
Model）。
  - 如果 callback 需要多個參數，可以傳陣列（如 `[$post, $category]`）。
  - 實際要傳什麼，請對照你 Gate/Policy 的 callback 參數決定。
  - 範例：
    - 只檢查 user：`Gate::allows('view-dashboard')`
    - 檢查 user + model：`Gate::allows('update-post', $post)`
    - 檢查多個 model：`Gate::allows('special-action', [$post, $category])`


#### **範例**：
```php
if (!Gate::allows('update-post', $post)) {
    abort(403);
}

Gate::authorize('update-post', $post); // 不通過自動 403
```

### 2.3 *Gate 傳遞額外參數*
Gate 的**第二參數**可傳陣列，會依序傳給 closure：

  - Gate 的 closure 第一個參數永遠是目前登入的 user（**Laravel 會自動注入**）。
  - 如果 closure 需要多個參數，Gate 的第二參數可傳陣列，陣列的每個值會依序對應到 closure 的第二、三、四...個參數。
  - 例如：`Gate::allows('create-post', [$category, false])`，$category 會對應到 closure 的第二個參數，false 對應到第三個參數。
  - 這種寫法可用於**需要多個條件判斷**的授權情境。
```php
Gate::define('create-post', function (User $user, Category $category, bool $pinned) {
    // ...
});

Gate::check('create-post', [$category, $pinned]);
```

### 2.4 *Gate 回應物件*
可回傳 `Illuminate\Auth\Access\Response`，**自訂訊息或狀態碼**：
```php
Gate::define('edit-settings', function (User $user) {
    return $user->isAdmin
        ? Response::allow()
        : Response::deny('必須是管理員');
});

$response = Gate::inspect('edit-settings');
if ($response->allowed()) {
    // 通過
} else {
    echo $response->message();
}
```
**Gate::inspect 用法**

  - `Gate::inspect('ability', $arguments)`
    會回傳一個 `Illuminate\Auth\Access\Response` 物件，而不是單純的布林值。
  - 這個物件可以用 `$response->allowed()` 判斷是否通過授權，用 `$response->message()` 取得自訂訊息。
  - 適合需要顯示授權失敗原因、API 回應、或前端友善提示的情境。
  - 例如：
    ```php
       Gate::define('edit-settings', function (User $user) {
           return $user->isAdmin()
               ? Response::allow()
               : Response::deny('只有管理員才能編輯設定！');
       });
       $response = Gate::inspect('edit-settings');
       if ($response->allowed()) {
           // 通過授權
       } else {
           echo $response->message(); // 顯示「只有管理員才能編輯設定！」
       }
    ```
- `Response::denyWithStatus(404)`：自訂 HTTP 狀態碼
- `Response::denyAsNotFound()`：直接 404

### 2.5 *Gate before/after hook*
- `Gate::before(fn($user, $ability) = ...)`
  - 說明：
    - before hook 會在所有 Gate 授權邏輯執行「**之前**」先被呼叫。
    - 可用於**全域超級管理員判斷、全域預設允許/拒絕等**。
    - 回傳非 null（如 true/false）時，會直接決定授權結果，不再執行後續 Gate。
    - `$user` 是**目前登入的 user**，
      `$ability` 是**權限名稱**（如 'update-post'）。

- `Gate::after(fn($user, $ability, $result, $args) = ...)`
  - 說明：
    - after hook 會在所有 Gate 授權邏輯執行「**之後**」被呼叫。
    - 可用於記**錄授權日誌、統一後處理**。
    - **不會影響授權結果**（只能觀察，不能改變結果）。
    - `$user` 是目前登入的 user，
      `$ability` 是權限名稱，
      `$result` 是授權結果（true/false），
      `$args` 是傳入的參數。

### 2.6 *Gate Inline 授權*
- `Gate::allowIf(fn($user) = ...)` /
  `Gate::denyIf(fn($user) = ...)`
  - 說明：
    - 這兩個方法可在程式中「*臨時*」定義授權條件，直接回傳 true/false。
    - 適合用於特殊情境下的即時授權判斷。
    - *不會執行 before/after hook，僅執行你傳入的 closure。*
    - `$user` 是目前登入的 user。

---

## 3. **Policy（政策）**
### 3.1 *Policy 產生*
- Artisan 指令：
  - `php artisan make:policy PostPolicy`（空的）
  - `php artisan make:policy PostPolicy --model=Post`（含 CRUD 範例）
- 產生於 `app/Policies` 目錄

### 3.2 *Policy 方法撰寫*
- 每個方法對應一個**動作**（如 view、create、update、delete...），方法名稱可自訂。
- 方法簽名範例：
```php
public function update(User $user, Post $post): bool
{
    return $user->id === $post->user_id;
}
```
- 也可回傳 **Response** 物件，自訂訊息或狀態碼：
```php
use Illuminate\Auth\Access\Response;
public function update(User $user, Post $post): Response
{
    return $user->id === $post->user_id
        ? Response::allow()
        : Response::deny('你不是這篇文章的作者');
}
```
- *Gate::allows* 仍只回傳**布林值**，
  *Gate::inspect* 可取**得完整 Response 物件**：

  - 這裡的 Response 物件（`Illuminate\Auth\Access\Response`）不是 HTTP Response，而是 **Laravel 授權系統的結果物件**。
  - 可用 `$response->allowed()` 判斷是否通過，`$response->message()` 取得自訂訊息。
  - 若要顯示在畫面或 API 回應，**需自行將訊息帶到前端或回傳給用戶端**。

```php
$response = Gate::inspect('update', $post);
if ($response->allowed()) {
    // 通過
} else {
    echo $response->message();
}
```
- *Gate::authorize('update', $post)* 不通過時，會將 Response 的訊息帶入 403 回應。

#### **自訂 HTTP 狀態碼**
- 可用 `denyWithStatus` 指定*失敗時的 HTTP 狀態碼*：
```php
public function update(User $user, Post $post): Response
{
    return $user->id === $post->user_id
        ? Response::allow()
        : Response::denyWithStatus(404);
}
```
- `denyAsNotFound()` 直接回傳 404：
```php
public function update(User $user, Post $post): Response
{
    return $user->id === $post->user_id
        ? Response::allow()
        : Response::denyAsNotFound();
}
```

#### **無模型方法**
- 有些方法*只需 User*，*不需模型*（如 create）：
```php
public function create(User $user): bool
{
    return $user->role === 'writer';
}
```

#### **訪客授權（允許未登入）**
- 預設 *未登入（guest）* 會自動拒絕，可用 *nullable type-hint* 允許訪客進入方法：
  - nullable type-hint（?型別）允許參數為 null，讓 Policy 方法能接收未登入（訪客）情境。
  - 在 Policy 方法中，`$user` 為 null 代表目前是「未登入」的訪客。
  - 判斷為訪客後，你可以根據需求決定：
    - 直接 return false 拒絕訪客操作（最常見）
    - 或允許訪客執行特定動作（如瀏覽、留言等）
    - 也可給訪客和會員不同權限（如訪客只能留言一次，會員可多次）

```php
public function update(?User $user, Post $post): bool
{
    if (!$user) {
        // 這裡就是訪客
        return false; // 拒絕訪客
        }

    return $user?->id === $post->user_id;
      // 這裡的 ?-- 是 PHP 8 的 Nullsafe Operator（null 安全運算子），
      // 當 $user 為 null 時，$user?->id 會直接回傳 null，不會拋出錯誤。
      // 這樣可以安全地判斷「未登入」的情境，避免 $user 為 null 時出現 Exception。
      // 適合用於允許訪客的授權判斷。
}
```

#### **before 過濾器（全域授權/拒絕）**
- 可在 Policy 類別內定義 before 方法，於*所有授權前先執行：*
```php
public function before(User $user, string $ability): bool|null
{
    if ($user->isAdministrator()) {
        return true; // 管理員全部通過
    }
    return null; // 交由後續方法判斷
}
```
- 若 *before* 回傳 false，所有授權都拒絕；
              回傳 null 則繼續執行對應方法。
- 注意：只有當 Policy 內有對應方法時，before 才會被呼叫。

### 3.3 *Policy 註冊*
- **自動發現**：
  -  Model 在 `app/Models`，
     Policy 在 `app/Policies`，
     且命名規則為 `ModelPolicy`，Laravel 會自動對應
     
- **手動註冊**：
  - 直接用 Gate::policy 指定某個 Model 對應哪個 Policy。
  - 適合需要明確綁定、或自動發現無法正確對應時。

```php
use Illuminate\Support\Facades\Gate;
Gate::policy(Order::class, OrderPolicy::class); // 將 Order 模型綁定到 OrderPolicy
```

- **屬性註冊**（Laravel 10+）：
  - 透過 PHP 8 屬性（attribute）語法，直接在 Model 上標註對應的 Policy。
  - 需 PHP 8+ 與 Laravel 10+，語法簡潔，適合新專案。

```php
#[UsePolicy(OrderPolicy::class)]
class Order extends Model {}
```
- **自訂發現邏輯**：
  - 可自訂 Laravel 如何根據 Model 類別自動推論對應的 Policy 名稱。
  - 適合有*特殊命名規則*或*多模組專案*。
```php
Gate::guessPolicyNamesUsing(fn($modelClass) = ...); // 回傳 Policy 類別名稱
```

### 3.4 *Policy 使用*
- 在 controller 內：
```php
if ($user->can('update', $post)) { ... }
$this->authorize('update', $post); // 不通過自動 403
```
- 在 Blade：

@can('update', $post)
    <button>編輯</button>
@endcan

  - `@can` 是 Blade 模板中的*授權判斷指令*，用於*前端根據權限動態顯示內容*。
  - 語法：`@can('ability', $model)`，第一個參數是權限名稱，第二個是要授權的 Model 實例。
  - 例：
    ```html
    @can('update', $post)
        <a href="{{ route('posts.edit', $post) }}">編輯</a>
    @endcan
    ```
  - 只有當目前 user 有權限 update 這個 $post 時，才會顯示編輯按鈕。
  - 等同於 PHP：`if (Auth::user()->can('update', $post)) { ... }`
  - 相關指令：`@cannot`（無權限時顯示）、`@canany`（有任一權限時顯示）。

---

## 4. **例外處理與自訂訊息**
- Gate/Policy 不通過時可自訂訊息、狀態碼
- `Gate::authorize`、`$this->authorize`、Blade @can 都會自動丟出 403
- 可用 `Response::denyWithStatus/denyAsNotFound` 自訂

---

## 5. **進階技巧**
- *before/after* hook 可全域放行/拒絕
- Gate/Policy 可混用
- 支援多參數、巢狀授權
- 支援自訂發現邏輯、屬性註冊

---

## 6. **實作範例**
### 6.1 *Gate 實作*

1. 在 `AppServiceProvider` 的 `boot` 方法定義 Gate：
```php
Gate::define('update-post', function (User $user, Post $post) {
    return $user->id === $post->user_id;
});
```

2. 在 controller 內檢查：
```php
if (!Gate::allows('update-post', $post)) {
    abort(403);
}
```

### 6.2 *Policy 實作*
1. 產生 Policy：
```shell
php artisan make:policy PostPolicy --model=Post
```

2. 在 controller 內：
```php
$this->authorize('update', $post);
```

3. 在 Blade 內：
```html
@can('update', $post)
    <button>編輯</button>
@endcan
```

---

## 4. **授權動作的多種方式**

### 4.1 *透過 User 模型 can/cannot 方法*
- User 模型內建 can/cannot 方法，可直接 **判斷是否有權限** 執行某動作。
- 若有註冊 Policy，會自動呼叫對應方法；
  若無則呼叫 Gate。
- 範例：
```php
if ($request->user()->cannot('update', $post)) {
    abort(403);
}
// ...
if ($request->user()->can('create', Post::class)) {
    // 可建立文章
}
```

### 4.2 *Gate Facade 授權*
- `Gate::authorize('動作', $model)`
- 不通過自動丟出 403。
- 若動作不需模型（如 create），可傳 class 名稱：
```php
Gate::authorize('update', $post);
Gate::authorize('create', Post::class);
```

### 4.3 *Middleware can 授權*
- 路由可直接用 *can* middleware，進入 controller 前先授權。
- 範例：
```php
Route::put('/post/{post}', function (Post $post) {
    // ...
})->middleware('can:update,post');

// 或用 can 方法
Route::put('/post/{post}', function (Post $post) {
    // ...
})->can('update', 'post');

// 不需模型時
Route::post('/post', function () {
    // ...
})->middleware('can:create,App\\Models\\Post');

Route::post('/post', function () {
    // ...
})->can('create', Post::class);
```

### 4.4 *Blade 模板授權指令*
- **@can/@cannot/@canany** 可根據授權顯示區塊。
- 範例：
```html
@can('update', $post)
    <!-- 可更新 -->
@elsecan('create', App\\Models\\Post::class)
    <!-- 可建立 -->
@else
    <!-- 其他 -->
@endcan

@cannot('update', $post)
    <!-- 不可更新 -->
@endcannot

@canany(['update', 'view', 'delete'], $post)
    <!-- 有任一權限 -->
     <button>管理</button>
@endcanany
```
- 也可用 *Auth::user()->can('動作', $model)* 判斷。

### 4.5 *傳遞額外參數*
- 授權時可傳**陣列**，
  第一個元素決定 policy，
  其餘傳給 policy 方法：

```php
public function update(User $user, Post $post, int $category): bool
{
    return $user->id === $post->user_id && $user->canUpdateCategory($category);
}
// 呼叫：
Gate::authorize('update', [$post, $request->category]);
```

### 4.6 *Inertia 前端授權資訊分享*
- 可在 **HandleInertiaRequests** middleware 的 `share` 方法中，**將授權資訊傳給前端**：
- **說明**：
    - 這段 share 寫法通常放在 `app/Http/Middleware/HandleInertiaRequests.php` 這個檔案的 `share` 方法內。
    - 這是 Laravel Inertia 專案自動產生的 middleware，負責**全域分享**資料給前端。
    - 如果你的專案沒有這個檔案，可以自己建立一個 middleware，並實作 share 方法。
```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request)
{
    return [
        // 將 auth 欄位分享給所有 Inertia 頁面
        'auth' = [
            // 當前登入的 user（如果沒登入就是 null）
            'user' = $request->user(),
            // 權限資訊，這裡以 post 相關權限為例
            'permissions' = [
                'post' = [
                    // 判斷目前 user 是否有建立文章的權限（回傳 true/false）
                    'create' = $request->user()?->can('create', Post::class),
                ],
            ],
        ],
    ];
}
```
- 前端可根據 `permissions` 決定 UI 呈現。
- 範例：
    ```js
    // 在 Vue/React 組件中
    if ($page.props.auth.permissions.post.create) {
        // 顯示「新增文章」按鈕
    }
    ```

  - 這種設計讓前端 UI 可以根據後端權限**動態顯示/隱藏功能**，權限判斷邏輯都集中在後端，前端只負責顯示。
  - 權限結構可依專案需求擴充，例如 edit、delete、view 等。

---

## **Web hook 是什麼？**

  - 「hook」在程式設計中代表「*掛鉤*」或「*掛點*」，讓你可以「掛上」自己的程式碼，當事件發生時系統會自動「勾」到你這段程式。
  - Web hook 就是「網路上的事件掛鉤」：你只要*掛好一個網址*，事件發生時對方就會自動通知你。
  - 生活比喻：像在門上掛鈴鐺（hook），有人開門（事件），鈴鐺就會響（通知你），你不用一直盯著門。

- *Web hook（網路鉤子、網路回呼）是一種即時通知機制*，讓你的應用程式在特定事件發生時，自動向指定的網址（URL）發送 HTTP 請求（通常是 POST），並攜帶事件資料。

### 1. *直白解釋*
- **Web hook 就像「自動推播」**：當某件事發生時（如付款成功、GitHub 有新 commit），系統會自動「推」一包資料到你指定的網址，不用你自己一直去查詢。

### 2. *常見用途*
- 金流通知（付款成功自動通知伺服器）
- GitHub/GitLab 事件通知（如 push、PR、issue）
- 第三方服務整合（如 LINE、Slack、Shopify、Stripe）
- 自動化流程（表單送出、訂單成立等）

### 3. *運作流程*
1. **你（開發者）** 在第三方服務（如金流、GitHub）後台「設定」一個 web hook URL（例如 https://yourapp.com/webhook）。
2. **第三方服務** 在「事件發生」時（如付款成功、push 代碼），會「主動發送」一個 HTTP 請求（通常是 POST）到你設定的 URL。
     - 第三方服務之所以知道要發送到哪個 URL，是因為你（開發者）事先在第三方服務的後台設定了 web hook URL。
     - 這個 URL 會被第三方服務記錄下來，事件發生時就會自動發送到這裡。
     - 生活比喻：就像你在快遞公司留下你的電話，包裹到貨時快遞公司就會打電話通知你。

3. **你的伺服器**（你的應用程式）「收到」這個 HTTP 請求後，會「解析」請求內容，並根據資料「執行」對應的業務邏輯（如更新訂單狀態、發送通知等）。

### 4. *範例*
```php
Route::post('/payment/webhook', function (Request $request) {
    // 解析 $request->all()，更新訂單狀態
});
```

### 5. *與 API Polling 差異*
- **Web hook**：事件發生時主動推送（即時、低流量）
- **Polling**：你*定時去查詢*對方 API（有延遲、浪費流量）

### 6. *小結*
- Web hook 是「事件驅動、即時通知」的技術
- 只要提供一個可接收 HTTP 請求的網址即可
- 廣泛用於自動化、整合、通知等場景 

---

## **Gate 進階用法補充**

### *Gate::forUser*
```php
if (Gate::forUser($user)->allows('update-post', $post)) {
    // 這個 user 可以更新 post
}
if (Gate::forUser($user)->denies('update-post', $post)) {
    // 這個 user 不能更新 post
}
```

  - `forUser($user)` 會「以你指定的 user 身份」來判斷權限，**不管目前登入者是誰**。
  - 常用於「管理員」或「系統」想要**模擬/查詢其他用戶的權限**，例如：
    - 管理員在後台查詢「小明有沒有刪除文章的權限？」
    - 單元測試時模擬不同 user 的權限情境
    - 批次作業根據不同 user 決定可執行動作
  - 跟直接權限判斷（預設用目前登入者）不同，這是「指定 user」的權限查詢，**等於「模擬」那個 user 的權限**。

### *Gate::any / Gate::none*
```php
if (Gate::any(['update-post', 'delete-post'], $post)) {
    // user 只要有其中一個權限就會通過
}
if (Gate::none(['update-post', 'delete-post'], $post)) {
    // user 兩個權限都沒有才會通過
}
```
  - `any`：只要有一個權限通過就回傳 true。
  - `none`：全部都沒通過才回傳 true。

### *Gate::check / allows / denies*
```php
if (Gate::check('create-post', [$category, $pinned])) {
    // 通過
}
if (Gate::allows('update-post', $post)) {
    // 通過
}
if (Gate::denies('update-post', $post)) {
    // 不通過
}
```
  - `Gate::check('ability', $arguments)` 用來判斷「指定能力」是否通過授權，回傳 true/false。
  - `check` 其實等同於 `allows`，都是回傳布林值，語意上 `check` 偏向「檢查」而非「允許」。
  - `allows`：判斷是否有權限（通過回傳 true）。
  - `denies`：判斷是否沒權限（沒通過回傳 true）。
  - 這三個方法都可用於 controller、service、或任何需要授權判斷的地方。
  - 可傳遞陣列作為第二參數，支援多參數授權情境。

### *Gate::authorize*
```php
Gate::authorize('update-post', $post);
// 通過則繼續，不通過自動丟出 403
```
  - `Gate::authorize('ability', $arguments)` 會根據你註冊的 Gate/Policy 來判斷權限。
  - 通過則繼續執行，不通過會自動丟出 `Illuminate\Auth\Access\AuthorizationException`，Laravel 會轉成 403 Forbidden 回應。
  - 適合在 **controller內** 直接授權，讓授權邏輯集中管理、可重複使用。

### *Gate::allowIf / denyIf（Inline 授權）*
```php
Gate::allowIf(fn (User $user) = $user->isAdministrator());
Gate::denyIf(fn (User $user) = $user->banned());
```
  - 這是「**inline（即時/臨時）授權**」寫法，直接用 **closure** 決定授權結果。
  - 只會執行你給的 closure，不會呼叫任何已註冊的 Gate/Policy，也不會觸發 before/after hook。
  - 通過則繼續執行，不通過同樣會自動丟出 `AuthorizationException`，Laravel 會轉成 403。
  - 適合臨時、特殊情境下的授權判斷，不需額外寫 Gate/Policy。
  - 實務上大多數情境建議用 `authorize`，只有臨時需求才用 `allowIf/denyIf`。

### *Gate::before / after*
```php
Gate::before(function (User $user, string $ability) {
    if ($user->isAdministrator()) {
        return true; // 管理員全部通過
    }
    // 回傳 null 則繼續執行原本的 Gate
});

Gate::after(function (User $user, string $ability, $result, $arguments) {
    // 這裡可以記錄授權日誌，不會影響結果
});
```
  - `before` 回傳 **true/false** 會直接決定結果，**null** 則繼續執行原本邏輯。
  - `after` 只能觀察，不會改變結果。

### *Gate::inspect*
```php
$response = Gate::inspect('edit-settings');
if ($response->allowed()) {
    // 通過
} else {
    echo $response->message(); // 顯示自訂訊息
}
```
  - 可取得**完整 Response 物件**，適合 API 或需要顯示錯誤訊息時使用。

### *Gate::define（Class Callback）*
```php
Gate::define('update-post', [PostPolicy::class, 'update']);
```
  - 可直接指定 Policy 類別與方法，**讓 Gate 與 Policy 共用邏輯**。

---

## **Policy 進階用法補充**

### *Policy 方法多參數*
```php
public function update(User $user, Post $post, int $category): bool
{
    return $user->id === $post->user_id && $user->canUpdateCategory($category);
}
// 呼叫時
Gate::authorize('update', [$post, $category]);
```
- 傳陣列時，第一個元素對應模型，後面依序對應方法參數。

### *Policy 無模型方法*
```php
public function create(User $user): bool
{
    return $user->role == 'writer';
}
// 判斷
$user->can('create', Post::class);
Gate::authorize('create', Post::class);
```
- 無模型時傳 class 名稱，Laravel 會自動對應 Policy。

### *Policy nullable type-hint（訪客授權）*
```php
public function update(?User $user, Post $post): bool
{
    return $user?->id === $post->user_id;
}
```
- `?User` 允許未登入（guest）進入方法，$user 為 null 代表訪客。

### *Policy before 方法*
```php
public function before(User $user, string $ability): bool|null
{
    if ($user->isAdministrator()) {
        return true; // 管理員全部通過
    }
    return null; // 交由後續方法判斷
}
```
- before 只會在 Policy 內有對應方法時被呼叫。

### *Policy Response 物件*
```php
use Illuminate\Auth\Access\Response;
public function update(User $user, Post $post): Response
{
    return $user->id === $post->user_id
        ? Response::allow()
        : Response::deny('你不是這篇文章的作者');
}
```
- 回傳 Response 物件可自訂訊息、狀態碼。

### *denyWithStatus / denyAsNotFound*
```php
public function update(User $user, Post $post): Response
{
    return $user->id === $post->user_id
        ? Response::allow()
        : Response::denyWithStatus(404);
}
// 或
public function update(User $user, Post $post): Response
{
    return $user->id === $post->user_id
        ? Response::allow()
        : Response::denyAsNotFound();
}
```
- denyWithStatus 可自訂 HTTP 狀態碼，denyAsNotFound 直接回傳 404。

---

## **授權用法補充**

### *User::can / cannot*
```php
if ($user->can('update', $post)) { ... }
if ($user->cannot('update', $post)) { ... }
```
- 直接在 **User 實例上** 判斷權限，會自動呼叫 Policy 或 Gate。

### *Middleware can*
```php
Route::put('/post/{post}', function (Post $post) {
    // ...
})->middleware('can:update,post');
// 或
Route::post('/post', function () {
    // ...
})->middleware('can:create,App\\Models\\Post');
```
- 可在**路由層**直接授權，**未通過自動 403**。

### *Blade @can / @cannot / @canany*
```html
@can('update', $post)
    <button>編輯</button>
@endcan

@cannot('update', $post)
    <span>無權限</span>
@endcannot

@canany(['update', 'delete'], $post)
    <button>管理</button>
@endcanany
```
- @can 判斷有權限，@cannot 判斷無權限，@canany 任一權限通過。

### *Blade 傳 class 名稱（無模型）*
```html
@can('create', App\Models\Post::class)
    <button>新增文章</button>
@endcan
```
- 適用於 **create** 這類 **不需模型實例** 的授權。

### *Blade 傳遞額外參數*
```html
@can('update', [$post, $category])
    <button>編輯</button>
@endcan
```
- 陣列第一個元素對應模型，後面依序對應 Policy 方法參數。

---

## **其他補充**

### *Gate::policy 註冊*
```php
Gate::policy(Order::class, OrderPolicy::class);
```
  - 手動註冊 Model 與 Policy 的對應關係。

- 註解：
  - `Gate::policy` 用來「**將整個模型（如 Order）綁定到一個 Policy 類別（如 OrderPolicy）**」。
  - 綁定後，*所有針對該模型的授權動作（如 view、create、update、delete）都會自動對應到 Policy 類別內的同名方法*。
  - 適合有多個動作需要授權的模型，讓授權邏輯集中、易於維護。
  - 與 `Gate::define` 差異如下：
   | 比較項目         | Gate::define（單一動作）         | Gate::policy（整個模型）         |
   |-------------- --|--------------------------------|--------------------------------|
   | 用途             | 定義單一 Gate（動作）             | 綁定整個模型到 Policy 類別        |
   | 寫法             | 每個動作都要 define 一次          | 一次註冊，動作自動對應 Policy 方法  |
   | 邏輯位置          | closure 分散在多處               | 集中在 Policy 類別               |
   | 適合情境          | 單一動作、簡單授權、小型專案        | 多動作授權、CRUD、大型專案         |
   | 維護性            | 較低，易分散                     | 較高，集中管理                    |
  - **實務建議**：
    - 小型專案、臨時授權可用 `Gate::define`。
    - 有多個動作的模型、團隊開發、需維護性時，建議用 `Gate::policy + Policy 類別`。

  - **補充說明**：
    - *Policy* 類別裡的每個方法（如 view、create、update、delete...）本質上就像一個 *Gate::define* 的動作。
    - Laravel 會自動把 Policy 方法當成 Gate 來用，當你呼叫 `$user->can('update', $post)` 時，會自動對應到 Policy 的 `update()` 方法。
    - 例如：
     - `Gate::define` 寫法：
       ```php
       Gate::define('update-post', function (User $user, Post $post) {
           return $user->id === $post->user_id;
       });
       // Gate::define 是「一個一個手動註冊的 Gate」。
       ```
     - `Policy 寫法`：
       ```php
       class PostPolicy {
           public function update(User $user, Post $post) {
               return $user->id === $post->user_id;
           }
       }
       ```
       - 並用 `Gate::policy(Post::class, PostPolicy::class)` 綁定
       - Policy 裡的方法，就是「一組命名好的 Gate 授權動作」。
       - Policy 讓你集中管理，而 Gate::define 是分散管理。
    - 這樣你就不用一個一個 define，授權邏輯集中、易於維護。

### *Model attribute 註冊 Policy（Laravel 10+）*
```php
#[UsePolicy(OrderPolicy::class)]
class Order extends Model {}
```
- 直接在 Model 上標註 Policy，需 PHP 8+。

### *Gate::guessPolicyNamesUsing*
```php
Gate::guessPolicyNamesUsing(function (string $modelClass) {
    // 回傳 Policy 類別名稱
    return $modelClass . 'Policy';
});
```
- 可自訂 Model 與 Policy 的自動對應邏輯。

---

## **Inertia 授權資訊分享**
```php
// middleware/HandleInertiaRequests.php
public function share(Request $request)
{
    return [
        ...parent::share($request),
        'auth' = [
            'user' = $request->user(),
            'permissions' = [
                'post' = [
                    'create' = $request->user()?->can('create', Post::class),
                ],
            ],
        ],
    ];
}
```
- 可將授權資訊傳到前端，方便 UI 動態顯示。

--- 