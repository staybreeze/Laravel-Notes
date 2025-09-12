
# *Laravel Controllers 筆記*

---

## 1. **Controller 基礎**

- *Controller* 可將相關的`請求處理邏輯`集中於一個類別，讓程式更有組織。
- *生活化比喻*： Controller 就像「櫃檯人員」，負責接收、分流、處理各種請求。
- 預設放在 `app/Http/Controllers` 目錄。
- _controller 並非一定要繼承 base class_，但通常會繼承 `App\Http\Controllers\Controller` 以共用邏輯。

---

### *建立 Controller*

- 使用 artisan 指令：

  ```bash
  `php artisan make:controller UserController
  ```

---

- **進階參數**

  - `--invokable`：產生*單一動作 Controller*
  - `--resource`：產生*資源控制器*（含 __CRUD 方法__）
  - `--api`：產生 *API* 專用 Controller（不含 __create/edit__）
  - `--model=ModelName`：自動 *type-hint* Model
  - `--requests`：自動產生 *FormRequest* 類別

- Controller 可有多個 `public` 方法，對應不同路由。

---

- **路由綁定**

  ```php
  use App\Http\Controllers\UserController;
  Route::get('/user/{id}', [UserController::class, 'show']);
  ```

  ```php
  namespace App\Http\Controllers;
  use App\Models\User;
  use Illuminate\View\View;
  class UserController extends Controller {
      /**
       * 顯示指定使用者的個人頁
       */
      public function show(string $id): View {
          return view('user.profile', [
              'user' => User::findOrFail($id)
          ]);
      }
  }
  ```

---

## 2. **單一動作 Controller**（Invokable）

- 若 Controller `只處理一個動作`，可只定義 `__invoke` 方法：

  ```php
  namespace App\Http\Controllers;
  class ProvisionServer extends Controller {
      public function __invoke() {
          // ...
      }
  }
  ```
---

- 路由綁定時 *只需指定類別*：

  ```php
  use App\Http\Controllers\ProvisionServer;
  Route::post('/server', ProvisionServer::class);
  ```

---

- *建立* invokable controller：

  ```bash
  `php artisan make:controller ProvisionServer --invokable
  ```

- *Controller stub 可自訂*：
  ```bash
  `php artisan stub:publish
  # 修改 stubs/controller.stub ...
  stubs/controller.stub 是 Laravel 用來產生 Controller 的程式碼範本檔案。

  「# 修改 stubs/controller.stub ...」意思是：
  你可以編輯這個檔案，自訂 Controller 的預設內容，
  之後用 artisan 指令產生 Controller 時，就會套用你修改過的樣板。

  your-project-root/
  ├── app/
  ├── stubs/
  │   └── controller.stub
  ├── config/
  ├── routes/
  ...

  ```
<!-- 
stub（樣板）是指「程式碼範本」，
Laravel 會用 stub 來產生 Controller、Model 等檔案的預設內容，
你可以自訂 stub 內容，讓 artisan 指令產生的檔案符合你的需求。 
-->
---

## 3. **Controller Middleware**

- Middleware 可在 `route` 註冊：

  ```php
  Route::get('/profile', [UserController::class, 'show'])->middleware('auth');
  ```

---

- 也可在 Controller 內 *靜態宣告* middleware：

  ```php
    use Illuminate\Routing\Controllers\HasMiddleware;
    use Illuminate\Routing\Controllers\Middleware;

    class UserController extends Controller implements HasMiddleware
    {
        // 定義 Controller 的 middleware
        public static function middleware(): array
        {
            return [
                'auth', // 所有方法都套用 auth middleware
                new Middleware('log', only: ['index']), // 只有 index 方法套用 log middleware
                new Middleware('subscribed', except: ['store']), // 除了 store 以外都套用 subscribed middleware
            ];
        }
    }

    // 使用方式：只要你在路由裡指定 UserController，這些 middleware 就會自動套用
    Route::resource('users', UserController::class);
  ```

---

- 也可用 *closure* 定義 *inline middleware*：

  ```php
    use Illuminate\Routing\Controllers\HasMiddleware;
    use Closure;
    use Illuminate\Http\Request;

    class ExampleController implements HasMiddleware
    {
        public static function middleware(): array {
              return [
              // 直接在 middleware 陣列裡定義匿名 middleware
                function (Request $request, Closure $next) {
                    // 這裡可以寫自訂邏輯，例如檢查 header 或參數
                    // ...
                    return $next($request); // 通過則繼續後續流程
                },
            ];
        }
    }
  ```

---

## 4. **Resource Controller**（資源控制器）

- 適合對 *Eloquent Model* 執行 CRUD 操作。

- 建立 *資源控制器*

  ```bash
  `php artisan make:controller PhotoController --resource
  # 進階：
  php artisan make:controller PhotoController --model=Photo --resource --requests
  ```

---

- *註冊資源路由*

  ```php
  use App\Http\Controllers\PhotoController;
  Route::resource('photos', PhotoController::class);
  ```

---

- *一行註冊多個資源*

  ```php
  Route::resources([
      'photos' => PhotoController::class,
      'posts' => PostController::class,
  ]);
  ```

---

- *資源路由對應動作*

| HTTP 動詞    | URI                   | 方法        | Route Name      | 說明                       |
|-------------|-----------------------|-------------|-----------------|----------------------------|
| GET         | /photos               | `index`     | photos.index    | 列出所有資料               |
| GET         | /photos/create        | `create`    | photos.create   | `顯示`新增表單頁面           |
| POST        | /photos               | `store`     | photos.store    | 儲存新資料                 |
| GET         | /photos/{photo}       | `show`      | photos.show     | `顯示`單一資料               |
| GET         | /photos/{photo}/edit  | `edit`      | photos.edit     | `顯示`編輯表單頁面           |
| PUT/PATCH   | /photos/{photo}       | `update`    | photos.update   | 更新資料                   |
| DELETE      | /photos/{photo}       | `destroy`   | photos.destroy  | 刪除資料                   |
---

---

- *Partial Resource Routes*

  ```php
  Route::resource('photos', PhotoController::class)->only(['index', 'show']);
  Route::resource('photos', PhotoController::class)->except(['create', 'store', 'update', 'destroy']);
  ```

---

- *API 專用資源路由*

  ```php
  Route::apiResource('photos', PhotoController::class);
  Route::apiResources([
      'photos' => PhotoController::class,
      'posts' => PostController::class,
  ]);
  ```

---

- *建立 API Controller*

  ```bash
  `php artisan make:controller PhotoController --api
  ```

<!--  API 路由不需要 create 和 edit 方法，
      因為這兩個通常是回傳 HTML 表單頁面法（回傳 view），
      API 只處理資料，不回傳表單，
      所以只保留 index、store、show、update、destroy。 -->
      
<!-- 
在 API 架構下，create 和 edit 方法原本是用來回傳 HTML 表單頁面（讓使用者輸入或編輯資料），但 API Controller 不負責回傳表單，只負責資料存取（回傳 JSON）。
因此，API 路由只保留 index、store、show、update、destroy 方法。
其中 show 方法會回傳單一資料（如某一筆 JSON），前端取得資料後自己產生編輯表單並填入資料，不需要後端提供 edit 方法。
簡單說，API 只回傳資料，畫面和表單都由前端負責，edit 的功能已由 show 取代。 
-->

---

## 5. **巢狀資源、Shallow Nesting、Scoped**

- *巢狀資源*

  ```php
  Route::resource('photos.comments', PhotoCommentController::class); // 定義巢狀資源路由，管理某張照片底下的留言
  // 會產生像 /photos/{photo}/comments/{comment} 這種路由
  // 讓 PhotoCommentController 負責處理留言的 CRUD 操作
  ```

---

- *Shallow nesting*

  ```php
  // 定義巢狀資源路由，並啟用 shallow nesting
  Route::resource('photos.comments', CommentController::class)->shallow();

  // 啟用 shallow() 後，像刪除、編輯等單一留言的路由
  // 只會是 /comments/{comment}，不需要 /photos/{photo}/comments/{comment}
  // 讓路由更簡潔，只有建立和列表才需要父資源 id
  ```

---

- *Scoped*（巢狀資源`自動綁定`父子關係）：

  ```php
  // 定義巢狀資源路由，並啟用 scoped 綁定
  Route::resource('photos.comments', PhotoCommentController::class)->scoped([
      'comment' => 'slug', // 讓 {comment} 路由參數用 slug 查詢，而不是預設主鍵 id
  ]);

  // 這樣 /photos/{photo}/comments/{comment} 路由會自動用 slug 查詢 comment
  // 並且會自動綁定父子關係（只查詢該 photo 底下的 comment）

  // slug 查詢是指用資料表中的 slug 欄位（通常是唯一且有意義的字串）來查詢資料，
  // 而不是用預設的主鍵 id。
  // 例如：/comments/hello-world 會用 slug = 'hello-world' 查詢 comment。
  ```

---

## 6. **資源路由進階技巧**

- *自訂路由名稱*

  ```php
  Route::resource('photos', PhotoController::class)->names([
      'create' => 'photos.build' // 將 create 路由命名為 photos.build
  ]);
  // 這樣 /photos/create 路由的名稱就會是 photos.build，
  // 方便用 route('photos.build') 產生網址或做跳轉。
  ```

---

- *自訂參數名稱*

  ```php
  Route::resource('users', AdminUserController::class)->parameters([
      'users' => 'admin_user' // 將 {users} 路由參數改名為 {admin_user}
  ]);
  // 例如 /users/{admin_user}，Controller 會取得 $admin_user 參數 
  // 這代表你把原本的 {users} 路由參數名稱改成 {admin_user}，
  // 所以路由會變成 /users/{admin_user}，
  // Controller 方法裡也會收到 $admin_user 這個參數，
  // 而不是預設的 $users。
  // 這樣可以讓參數名稱更符合你的資料結構或命名習慣。

  // 你可以一開始就用 Route::resource('admin_user', AdminUserController::class)，
  // 但這樣網址會變成 /admin_user/{admin_user}，
  // 如果你想保留 /users/{admin_user} 這種 RESTful 路徑，
  // 又想參數名稱更明確，就可以用 parameters 來自訂參數名稱。
  ```

---

- *本地化 verbs*

  ```php
  Route::resourceVerbs([
      'create' => 'crear', // 將 create 路由動詞改為西班牙文 crear
      'edit'   => 'editar', // 將 edit 路由動詞改為西班牙文 editar
  ]);
  // 這樣 /photos/crear 和 /photos/{photo}/editar 就會取代原本的 /create 和 /edit
  ```

---

- *Supplemental Routes*（補充自訂路由）

<!-- 
除了標準 RESTful 資源路由（Route::resource），
你還可以自訂補充路由（如 /photos/popular），
讓 Controller 支援額外的功能或特殊需求，
不受 RESTful 標準限制。 
-->

  ```php
  Route::get('/photos/popular', [PhotoController::class, 'popular']); // 自訂熱門照片路由
  Route::resource('photos', PhotoController::class);                  // 標準 RESTful 資源路由
  ```

- *保持 Controller 精簡*，若有太多非標準動作，建議拆分多個 Controller。

---

## 7. **Missing/withTrashed/軟刪除模型**

- *自訂找不到模型時的行為*（missing）

  ```php
  // 自訂找不到模型時的行為（missing）
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Redirect;

  Route::resource('photos', PhotoController::class)
      ->missing(function (Request $request) {
          // 當找不到指定的 photo 時，導回照片列表頁
          return Redirect::route('photos.index');
      });
  ```

---

- *withTrashed*（支援軟刪除模型）

  ```php
  Route::resource('photos', PhotoController::class)->withTrashed(); 
  // 讓所有資源路由都能查詢包含軟刪除（trashed）的資料

  Route::resource('photos', PhotoController::class)->withTrashed(['show']); 
  // 只有 show 路由能查詢包含軟刪除的資料，其他路由不受影響
  ```

---

## 8. **Singleton Resource Controller**（單例資源控制器）

- singleton 路由管 適合 `profile（個人檔案）`、`thumbnail（縮圖）` 這種「__一對一資源__」，每個主體只有一份資料。

- 註冊 *singleton*

  ```php
  Route::singleton('profile', ProfileController::class);
  // 預設 singleton 路由只會有 show、edit、update，
  // 不會有 create、store、index、destroy。
  // 要有 create/store 或 destroy，
  // 必須分別加上 ->creatable() 或 ->destroyable()

  // 適合像 profile 這種每人只有一份的資源
  // 因為 singleton 資源代表「每人只有一份」資料
  // 不需要列出多筆（index），也不需要刪除（destroy）

  // 因為 singleton 資源通常代表「唯一」或「個人專屬」資料，
  // 像 profile 這種資源，通常不允許直接刪除，
  // 而是只能編輯或更新，
  // 所以不會產生 destroy 路由。
  ```
---

- *可巢狀於一般資源*

  ```php
  Route::singleton('photos.thumbnail', ThumbnailController::class);
  // // 產生 /photos/{photo}/thumbnail 這種路由，管理單一照片的縮圖資源
  ```

---

- *Creatable singleton*

  ```php
  Route::singleton('photos.thumbnail', ThumbnailController::class)->creatable();
  // 讓 singleton 路由支援建立（create/store）縮圖資源
  // 例如 /photos/{photo}/thumbnail/create 和 POST /photos/{photo}/thumbnail

  // 預設 singleton 路由不會有 create/store，
  // 你必須用 ->creatable() 才會產生 /create 和 POST 路由，
  // 讓你可以建立這個一對一資源。
  ```

---

- *Destroyable singleton*

  ```php
  Route::singleton('photos.thumbnail', ThumbnailController::class)->destroyable();
  // 讓 singleton 路由支援刪除（DELETE）縮圖資源
  // 例如 DELETE /photos/{photo}/thumbnail

  // 預設 singleton 路由不會有 destroy（刪除），
  //但你可以用 ->destroyable() 額外啟用刪除功能，
  // 這時就會產生 DELETE 路由，
  // 例如 /photos/{photo}/thumbnail 可以被刪除。
  ```

---

- *API singleton*

  ```php
  Route::apiSingleton('profile', ProfileController::class);
  // 註冊 API 單一資源路由，只產生 show、update，不會有 create、edit、index、destroy

  Route::apiSingleton('photos.thumbnail', ProfileController::class)->creatable();
  // 註冊 API 單一資源路由，並支援建立（POST），例如 POST /photos/{photo}/thumbnail
  ```

---

## 9. **Middleware 與資源控制器**

- 可用 `middleware、middlewareFor、withoutMiddlewareFor` 精細控制：

  ```php
  Route::resource('users', UserController::class)
      ->middleware(['auth', 'verified']) // 所有 users 路由都套用 auth、verified middleware

      ->middlewareFor('show', 'auth') // show 路由額外套用 auth
      ->middlewareFor('update', 'auth') // update 路由額外套用 auth

      ->withoutMiddlewareFor('index', ['auth', 'verified']) // index 路由移除 auth、verified
      ->withoutMiddlewareFor(['create', 'store'], 'verified') // create、store 路由移除 verified
      ->withoutMiddlewareFor('destroy', 'subscribed'); // destroy 路由移除 subscribed

  Route::singleton('profile', ProfileController::class)
      ->middleware('auth') // 所有 profile 路由都套用 auth
      
      ->middlewareFor('show', 'auth'); // show 路由額外套用 auth

  Route::apiSingleton('profile', ProfileController::class)
      ->middlewareFor(['show', 'update'], 'auth'); // show、update 路由套用 auth
  ```

---

## 10. **依賴注入**（Constructor/Method Injection）

- *建構子注入*

  ```php
  class UserController extends Controller {
      // 依賴注入 UserRepository，讓 Controller 可直接使用 $users
      public function __construct(protected UserRepository $users) {}
  }
  ```

---

- *方法注入*

  ```php
  public function store(Request $request): RedirectResponse {
      $name = $request->name;
      // ...
  }
  ```

---

- `路由參數`與`依賴` *可同時注入*

  ```php
  public function update(Request $request, string $id): RedirectResponse {
      // 你可以在 Controller 方法同時取得依賴（如 Request）和路由參數（如 $id）。
      // $request 會自動注入 HTTP 請求物件
      // $id 會自動注入路由參數
      // ...更新邏輯

      // : RedirectResponse 代表這個方法會回傳一個重定向（redirect）結果，
      // 這是型別提示（type hint），讓程式更明確。
  }
  ```

--- 