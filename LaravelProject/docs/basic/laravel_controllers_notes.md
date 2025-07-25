
# *Laravel Controllers 筆記*

## 1. **Controller 基礎**
- *Controller* 可將相關的請求處理邏輯集中於一個類別，讓程式更有組織。
- *生活化比喻*： Controller 就像「櫃檯人員」，負責接收、分流、處理各種請求。
- 預設放在 `app/Http/Controllers` 目錄。
- **ontroller 並非一定要繼承 base class*，但通常會繼承 `App\Http\Controllers\Controller` 以共用邏輯。

### *建立 Controller*
- 使用 artisan 指令：
  ```bash
  `php artisan make:controller UserController
  ```
- 進階參數：
  - `--invokable`：產生單一動作 Controller
  - `--resource`：產生資源控制器（含 CRUD 方法）
  - `--api`：產生 API 專用 Controller（不含 create/edit）
  - `--model=ModelName`：自動 type-hint Model
  - `--requests`：自動產生 FormRequest 類別
- Controller 可有多個 public 方法，對應不同路由。
- 範例：
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
- 路由綁定：
  ```php
  use App\Http\Controllers\UserController;
  Route::get('/user/{id}', [UserController::class, 'show']);
  ```

---

## 2. **單一動作 Controller（Invokable）**
- 若 Controller 只處理一個動作，可只定義 `__invoke` 方法：
  ```php
  namespace App\Http\Controllers;
  class ProvisionServer extends Controller {
      public function __invoke() {
          // ...
      }
  }
  ```
- 路由綁定時 *只需指定類別*：
  ```php
  use App\Http\Controllers\ProvisionServer;
  Route::post('/server', ProvisionServer::class);
  ```
- 建立 invokable controller：
  ```bash
  `php artisan make:controller ProvisionServer --invokable
  ```
- **Controller stub 可自訂**：
  ```bash
  `php artisan stub:publish
  # 修改 stubs/controller.stub ...
  ```

---

## 3. **Controller Middleware**
- Middleware 可在 route 註冊：
  ```php
  Route::get('/profile', [UserController::class, 'show'])->middleware('auth');
  ```
- 也可在 Controller 內 *靜態宣告* middleware：
  ```php
  use Illuminate\Routing\Controllers\HasMiddleware;
  use Illuminate\Routing\Controllers\Middleware;
  class UserController extends Controller implements HasMiddleware {
      public static function middleware(): array {
          return [
              'auth',
              new Middleware('log', only: ['index']),
              new Middleware('subscribed', except: ['store']),
          ];
      }
  }
  ```
- 也可用 *closure* 定義 *inline middleware*：
  ```php
  use Closure;
  use Illuminate\Http\Request;
  public static function middleware(): array {
      return [
          function (Request $request, Closure $next) {
              // ...
              return $next($request);
          },
      ];
  }
  ```

---

## 4. **Resource Controller（資源控制器）**
- 適合對 *Eloquent Model* 執行 CRUD 操作。
- 建立 *資源控制器*：
  ```bash
  `php artisan make:controller PhotoController --resource
  # 進階：
  php artisan make:controller PhotoController --model=Photo --resource --requests
  ```
- 註冊資源路由：
  ```php
  use App\Http\Controllers\PhotoController;
  Route::resource('photos', PhotoController::class);
  ```
- 一行註冊多個資源：
  ```php
  Route::resources([
      'photos' => PhotoController::class,
      'posts' => PostController::class,
  ]);
  ```
- *資源路由對應動作*：
  | HTTP 動詞 | URI | 方法 | Route Name |
  |---|---|---|---|
  | GET | /photos | index | photos.index |
  | GET | /photos/create | create | photos.create |
  | POST | /photos | store | photos.store |
  | GET | /photos/{photo} | show | photos.show |
  | GET | /photos/{photo}/edit | edit | photos.edit |
  | PUT/PATCH | /photos/{photo} | update | photos.update |
  | DELETE | /photos/{photo} | destroy | photos.destroy |
- *Partial Resource Routes*：
  ```php
  Route::resource('photos', PhotoController::class)->only(['index', 'show']);
  Route::resource('photos', PhotoController::class)->except(['create', 'store', 'update', 'destroy']);
  ```
- *API 專用資源路由*：
  ```php
  Route::apiResource('photos', PhotoController::class);
  Route::apiResources([
      'photos' => PhotoController::class,
      'posts' => PostController::class,
  ]);
  ```
- *建立 API Controller*：
  ```bash
  `php artisan make:controller PhotoController --api
  ```

---

## 5. **巢狀資源、Shallow Nesting、Scoped**
- *巢狀資源*：
  ```php
  Route::resource('photos.comments', PhotoCommentController::class);
  ```
- *Shallow nesting*：
  ```php
  Route::resource('photos.comments', CommentController::class)->shallow();
  ```
- *Scoped*（巢狀資源自動綁定父子關係）：
  ```php
  Route::resource('photos.comments', PhotoCommentController::class)->scoped([
      'comment' => 'slug',
  ]);
  ```

---

## 6. **資源路由進階技巧**
- *自訂路由名稱*：
  ```php
  Route::resource('photos', PhotoController::class)->names([
      'create' => 'photos.build'
  ]);
  ```
- *自訂參數名稱*：
  ```php
  Route::resource('users', AdminUserController::class)->parameters([
      'users' => 'admin_user'
  ]);
  ```
- *本地化 verbs*：
  ```php
  Route::resourceVerbs([
      'create' => 'crear',
      'edit' => 'editar',
  ]);
  ```
- *Supplemental Routes（補充自訂路由）*：
  ```php
  Route::get('/photos/popular', [PhotoController::class, 'popular']);
  Route::resource('photos', PhotoController::class);
  ```
- *保持 Controller 精簡*：若有太多非標準動作，建議拆分多個 Controller。

---

## 7. **Missing/withTrashed/軟刪除模型**
- *自訂找不到模型時的行為（missing）*：
  ```php
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Redirect;
  Route::resource('photos', PhotoController::class)
      ->missing(function (Request $request) {
          return Redirect::route('photos.index');
      });
  ```
- *withTrashed（支援軟刪除模型）*：
  ```php
  Route::resource('photos', PhotoController::class)->withTrashed();
  Route::resource('photos', PhotoController::class)->withTrashed(['show']);
  ```

---

## 8. **Singleton Resource Controller（單例資源控制器）**
- 適合 `profile、thumbnail` 這種一對一資源。
- 註冊 *singleton*：
  ```php
  Route::singleton('profile', ProfileController::class);
  ```
- *可巢狀於一般資源*：
  ```php
  Route::singleton('photos.thumbnail', ThumbnailController::class);
  ```
- *Creatable singleton*：
  ```php
  Route::singleton('photos.thumbnail', ThumbnailController::class)->creatable();
  ```
- *Destroyable singleton*：
  ```php
  Route::singleton('photos.thumbnail', ThumbnailController::class)->destroyable();
  ```
- *API singleton*：
  ```php
  Route::apiSingleton('profile', ProfileController::class);
  Route::apiSingleton('photos.thumbnail', ProfileController::class)->creatable();
  ```

---

## 9. **Middleware 與資源控制器**
- 可用 `middleware、middlewareFor、withoutMiddlewareFor` 精細控制：
  ```php
  Route::resource('users', UserController::class)
      ->middleware(['auth', 'verified'])
      ->middlewareFor('show', 'auth')
      ->middlewareFor('update', 'auth')
      ->withoutMiddlewareFor('index', ['auth', 'verified'])
      ->withoutMiddlewareFor(['create', 'store'], 'verified')
      ->withoutMiddlewareFor('destroy', 'subscribed');
  Route::singleton('profile', ProfileController::class)
      ->middleware('auth')
      ->middlewareFor('show', 'auth');
  Route::apiSingleton('profile', ProfileController::class)
      ->middlewareFor(['show', 'update'], 'auth');
  ```

---

## 10. **依賴注入（Constructor/Method Injection）**
- *建構子注入*：
  ```php
  class UserController extends Controller {
      public function __construct(protected UserRepository $users) {}
  }
  ```
- *方法注入*：
  ```php
  public function store(Request $request): RedirectResponse {
      $name = $request->name;
      // ...
  }
  ```
- `路由參數`與`依賴` *可同時注入*：
  ```php
  public function update(Request $request, string $id): RedirectResponse {
      // ...
  }
  ```

--- 