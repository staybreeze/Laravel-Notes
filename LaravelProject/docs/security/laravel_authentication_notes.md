# Laravel Authentication（認證）完整筆記

---

## 1. *認證系統總覽*

Laravel 認證系統讓你能快速、安全地為 Web 應用程式實作*登入、登出、API Token 驗證*等功能。

- **核心概念**：
  - **Guards（守衛）**：決定「*如何*」驗證用戶（如 session、token）。
  - **Providers（提供者）**：決定「*從哪裡*」取得用戶資料（如 Eloquent、Query Builder）。
- **Starter Kits**：官方快速腳手架，幫你產生完整認證流程。
- **API 認證**：支援 Passport（OAuth2）與 Sanctum（簡易 Token）。

---

## 2.*認證設定檔*

- 設定檔位置：`config/auth.php`
- 主要設定：
  - `guards`：定義多種守衛（如 web、api、admin）。
  - `providers`：定義用戶資料來源（如 users、admins）。
  - `passwords`：密碼重設相關設定。

---

## 3. *Guards 與 Providers*

### 3.1 **Guards（守衛）**
- 決定「如何」驗證用戶。
- 內建：
  - `session`（預設，Web 用，靠 session/cookie 維持登入狀態）
  - `token`（API 用，靠 API Token 驗證）
- 可自訂多組（如 admin、api、user）。

### 3.2 **Providers（提供者）**
- 決定「從哪裡」取得用戶資料。
- 內建：
  - `eloquent`（預設，對應 Eloquent Model，如 App\Models\User）
  - `database`（直接查資料表，不用 Eloquent）
- 可自訂（如連接 LDAP、外部 API）。

---

## 4. *Starter Kits（快速腳手架）*

- 官方推薦：用 starter kit 快速建立認證功能。
- 常用套件：
  - **Laravel Breeze**：簡單、現代、支援 Blade/React/Vue。
  - **Laravel Jetstream**：進階，支援 2FA、團隊、API Token。
  - **Laravel Fortify**：純後端，提供 API 路由（無 UI）。
- 安裝範例：
  ```bash
  composer require laravel/breeze --dev
  php artisan breeze:install
  php artisan migrate
  npm install && npm run dev
  ```
- 完成後，瀏覽 `/login`、`/register` 即可看到登入註冊頁。

---

## 5. *資料庫設計*

- 預設 User Model：`App\Models\User`（在 `app/Models/User.php`）
- 預設 users 資料表：
  - `id`、`name`、`email`、`password`、`remember_token`（100 字元，支援「記住我」功能）
- 密碼欄位建議長度：**至少 60 字元**（支援 bcrypt/hash）
- 預設 migration 已包含必要欄位。

---

## 6. *內建認證流程（Web）*

### 6.1 **登入流程**
-   用戶送出帳號密碼 
    → 檢查正確性 
    → 寫入 session 
    → 發送 session cookie 
    → 後續請求自動帶 cookie。

- 主要 API：
  - `Auth::attempt(['email' => $email, 'password' => $pw])`：嘗試登入。
  - `Auth::user()`：取得目前登入用戶。
  - `Auth::id()`：取得目前登入用戶 ID。
  - `Auth::check()`：判斷是否已登入。
  - `Auth::logout()`：登出。

### 6.2 **取得目前用戶**
- 方式一：
  ```php
  use Illuminate\Support\Facades\Auth;
  $user = Auth::user();
  $id = Auth::id();
  ```
- 方式二（Controller 內）：
  ```php
  public function update(Request $request) {
      $user = $request->user();
  }
  ```

### 6.3 **判斷是否登入**
```php
if (Auth::check()) {
    // 已登入
}
```

---

## 7. *手動認證（Manually Authenticating Users）*

Laravel 允許你不用 Starter Kit 也能直接用 Auth 類別手動處理登入、登出、記住我等認證流程。

### 7.1 **基本登入（Auth::attempt）**

- 用於處理登入表單提交。
- 範例：
  ```php
  // 載入 Laravel 的認證 Facade
  use Illuminate\Support\Facades\Auth;

  // 驗證請求資料，確保格式正確
  $credentials = $request->validate([
      // email 欄位必填且必須為合法 email 格式
      'email' => ['required', 'email'],
      // password 欄位必填
      'password' => ['required'],
  ]);

  // 嘗試登入，credentials 會自動比對資料庫帳密，成功回傳 true
  if (Auth::attempt($credentials)) {
      // 登入成功後，強制重新產生 session id，防止 Session Fixation 攻擊
      $request->session()->regenerate(); 
      // 導向用戶原本想去的頁面（如無則預設到 dashboard）
      return redirect()->intended('dashboard');
  }

  // 登入失敗，回傳錯誤訊息
  return back()->withErrors([
      // 錯誤訊息內容
      'email' => '帳號或密碼錯誤',
  ])->onlyInput('email'); // 只保留 email 欄位的輸入值，密碼不會保留
  
  ```
- 備註：
  - *credentials* 陣列的 key 對應資料表欄位（如 email、password）。
  - 密碼不用自己 hash，Laravel 會自動比對雜湊值。
  - 登入成功後建議呼叫 `$request->session()->regenerate()`。
  - `redirect()->intended()` 會導回原本被攔截的頁面。

- **Auth::attempt** 基本語法與註解範例

```php
// 準備登入用的帳號密碼陣列，key 對應資料表欄位
$credentials = [
    'email' => $request->input('email'), // 用戶輸入的 email
    'password' => $request->input('password'), // 用戶輸入的密碼
];

// 嘗試登入，$credentials 會自動比對資料庫帳密
// 第二參數 $remember 可選，true 代表啟用「記住我」功能
if (Auth::attempt($credentials, $remember = false)) {
    // 登入成功，系統會自動建立 session
    // 可在這裡導向首頁或 dashboard
} else {
    // 登入失敗，帳密錯誤或其他條件不符
    // 可在這裡回傳錯誤訊息
}
```

### 7.2 **指定額外條件**

- 可在 credentials 陣列加上其他條件（如 active 狀態）：
  ```php
  if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 1])) {
      // 登入成功且 active
  }
  ```
- 進階條件可用 closure：
  ```php
  // 載入 Eloquent 查詢建構器
  use Illuminate\Database\Eloquent\Builder;
  // 嘗試登入，除了帳號密碼外，還可用 closure 加進階條件
  if (Auth::attempt([
      // email 欄位
      'email' => $email,
      // password 欄位
      'password' => $password,
      // 進階條件：必須有 activeSubscription 關聯（如訂閱中）
      fn (Builder $query) => $query->has('activeSubscription'),
  ])) {
      // 登入成功且有訂閱
  }
  ```

### 7.3 **attemptWhen 進階驗證**

- 可用 attemptWhen 進行更細緻的驗證：
  ```php
  // 載入 Eloquent 查詢建構器
  use Illuminate\Database\Eloquent\Builder;
  // 嘗試登入，credentials 只驗證 email 與 password
  if (Auth::attemptWhen([
      'email' => $email,
      'password' => $password,
  ], function (User $user) {
      // 進階驗證條件：必須未被封鎖
      return $user->isNotBanned();
  })) {
      // 登入成功且未被封鎖
  }
  ```

### 7.4 **指定 Guard 登入**

- 可用於*多用戶系統*（如 admin/user）：
  ```php
  <!-- 如果帳密正確（也就是 $credentials 能在 admin guard 對應的用戶表找到對應帳號），就會登入成功，if 內的程式碼會執行。 -->
  if (Auth::guard('admin')->attempt($credentials)) {
      // admin guard 登入成功
  }
  ```
- guard 名稱需對應 `config/auth.php` 設定。

### **7.5 記住我（Remember Me）**

- 登入時第二參數設為 true：
  ```php
  // 嘗試登入，$remember 代表是否啟用「記住我」功能（通常來自表單的 checkbox）
  if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
      // 如果登入成功，且 $remember 為 true，系統會在瀏覽器設置一個長效的「記住我」cookie
      // 這樣下次即使 session 過期，用戶仍可自動登入
      // 這裡的程式碼區塊可放登入成功後的處理
  }
  ```
- 需確保 users 資料表有 `remember_token` 欄位。
  - **說明**：`remember_token` 欄位用來儲存「記住我」的 token，Laravel 會自動管理這個欄位。沒有這個欄位，「記住我」功能無法正常運作。
- 判斷是否用記住我登入：
  ```php
  // 判斷目前登入狀態是否是透過「記住我」cookie 自動登入
  if (Auth::viaRemember()) {
      // 如果是，這裡可以做額外處理（如顯示提示、加強安全檢查等）
  }
  ```

### 7.6 **直接登入 User 實例（Auth::login）**

- 用於*註冊後自動登入*、特殊情境：
  ```php
  // 直接登入指定的 User 實例，$user 必須是實作 Authenticatable 介面的模型（通常是 App\Models\User）
  // 注意：只有實作 Illuminate\Contracts\Auth\Authenticatable 介面的物件才能使用 Auth::login
  // Laravel 預設的 App\Models\User 已經支援，若自訂模型也必須實作此介面
  Auth::login($user);
  // 直接登入並啟用「記住我」功能（第二參數 true）
  Auth::login($user, true);
  // 指定 guard（如 admin）直接登入
  Auth::guard('admin')->login($user);
  ```

### 7.7 **依 ID 登入（loginUsingId）**

- 直接用*主鍵*登入：
  ```php
  // 注意：loginUsingId 會自動查詢 User 模型，該模型必須實作 Illuminate\Contracts\Auth\Authenticatable 介面
  // Laravel 預設的 App\Models\User 已經支援，若自訂模型也必須實作此介面
  // 直接用主鍵（如 id=1）登入該用戶，會自動查詢 User 模型
  Auth::loginUsingId(1);
  // 直接用主鍵登入，並啟用「記住我」功能（remember: true）
  Auth::loginUsingId(1, remember: true);
  ```

### 7.8 **單次請求登入（once）**

- 只在*本次請求*有效，不會產生 session/cookie：
  ```php
  if (Auth::once($credentials)) {
      // 單次請求登入
  }
  ```

---

## 8. *路由保護與 Middleware*

### 8.1 **保護路由**
- 使用 `auth` middleware：
  ```php
  Route::get('/dashboard', function () {
      // 只有登入用戶可進入
  })->middleware('auth');
  ```
- **指定守衛**：
  ```php
  Route::get('/admin', function () {
      // 只有 admin 守衛驗證通過者可進入
  })->middleware('auth:admin');
  ```

### 8.2 **未登入自動導向**
- 預設未登入會被導向 `login` route。
- 可在 `bootstrap/app.php` 用 `redirectGuestsTo` 自訂：
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->redirectGuestsTo('/login');
      // 或用 closure
      $middleware->redirectGuestsTo(fn (Request $request) => route('login'));
  })
  ```

### 8.3 **已登入自動導向**
- 用 `guest` middleware 保護註冊/登入頁，已登入者自動導向 dashboard。
- 可用 `redirectUsersTo` 自訂：
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->redirectUsersTo('/dashboard');
      // 或用 closure
      $middleware->redirectUsersTo(fn (Request $request) => route('dashboard'));
  })
  ```

---

## 9. *API 認證（Sanctum & Passport）*

### 9.1 **Sanctum（推薦）**
- **Sanctum 是什麼？**
  - Sanctum 是 Laravel 官方推出的輕量級 API 認證套件，適合 SPA（單頁應用）、行動 App、第一方 Web + API 等情境。
  - 它同時支援 *session cookie（前端登入）*與 *API Token（行動裝置、第三方 API）*兩種模式。
  - 與 Passport 相比，Sanctum 安裝簡單、學習曲線低，適合大多數需要「用戶登入＋API Token」的應用。
  - 若需要完整 *OAuth2* 流程（如第三方授權、client credentials），才建議用 Passport。
- 適用：SPA、行動 App、第一方 Web + API。
- 支援 session cookie + API Token 雙模式。
- 安裝：
  ```php
  # 安裝 Sanctum 套件
  composer require laravel/sanctum
  # 發佈 Sanctum 設定與 migration 檔案
  php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
  # 執行 migration，建立 sanctum 相關資料表
  php artisan migrate
  ```
- 設定：`config/sanctum.php`，User Model 加入 `HasApiTokens` trait。
  - **說明**：`HasApiTokens` trait 讓 User 可以產生/驗證 API Token。
- 用法：
  - 前端登入後自動帶 session cookie。
  - API 請求帶 `Authorization: Bearer <token>`。
  - 支援「能力（abilities）」限制 token 權限。
    - **說明**：可細分不同 token 的權限（如只能讀、只能寫）。
- 參考：官方文件 [Sanctum How It Works](https://laravel.com/docs/sanctum#how-it-works)
  - **說明**：官方文件有更完整的安裝、設定、用法與安全建議。

### 9.2 **Passport（進階 OAuth2）**
- **Passport 是什麼？**
  - Passport 是 Laravel 官方推出的*完整 OAuth2 認證套件*，適合需要第三方授權、client credentials、授權碼流程等進階 API 認證情境。
  - 它支援所有 OAuth2 標準流程，適合多方整合、開放 API、第三方應用登入等需求。
  - 與 Sanctum 相比，Passport 功能更完整但安裝與設定較複雜，學習曲線較高。
  - 一般內部 API 或單純用戶登入建議用 Sanctum，只有需要 OAuth2 標準時才用 Passport。
- 適用：需要完整 OAuth2 流程（第三方授權、client credentials 等）。
- 安裝：
  ```php
  // 安裝 Passport 套件
  composer require laravel/passport
  // 執行 migration，建立 passport 相關資料表
  php artisan migrate
  // 產生加密金鑰與 client 設定
  php artisan passport:install
  ```
- 較複雜，除非有 OAuth2 需求，建議用 Sanctum。

---

## 10. *登入節流（Login Throttling）*

- **Starter Kit** 內建登入節流，預設多次失敗會鎖定 1 分鐘。
  - 防止暴力破解，連續登入失敗會暫時鎖定帳號或 IP。
- 範例：
  - 依帳號/Email + IP 限制。
    - 同一帳號或同一 IP 多次失敗會被暫時鎖定。
- 自訂其他路由節流：用 `throttle` middleware 或 RateLimiter。
  - 可針對特定路由（如註冊、API）自訂流量限制規則。
- 參考：`docs/laravel_rate_limiting_notes.md`

---

## 11. *實務註解與最佳實踐*

- **不要自己實作密碼雜湊**，用 Laravel 內建 hash/bcrypt。
- **記得加上 remember_token 欄位**，支援「記住我」功能。
- **API 認證建議用 Sanctum**，除非有 OAuth2 需求才用 Passport。
- **多守衛（guards）**：可同時支援多種用戶（如 admin/user），各自 session/token。
- **善用 middleware**：auth、guest、throttle、verified 等。
- **登入/註冊/密碼重設**：Starter Kit/Fortify 皆有現成 API。
- **保護敏感路由**：所有需要登入的頁面都要加 `auth` middleware。
- **API 路由**：建議用 `auth:sanctum` middleware。
- **自訂登入導向**：可用 redirectGuestsTo/redirectUsersTo 客製化 UX。
- **登入失敗訊息**：避免洩漏帳號是否存在，統一回傳「帳號或密碼錯誤」。
- **審計/紀錄登入登出**：可用事件監聽（Login/Logout Event）記錄用戶行為。

---

## 12. *常見 Q&A*

- Q: 如何同時支援 Web 與 API 認證？
  - A: 用 web guard 處理 session/cookie，api guard 處理 token，Sanctum 可同時支援。
- Q: 如何自訂用戶表？
  - A: 在 `config/auth.php` 設定 provider，指向自訂 Model 與資料表。
- Q: 如何取得目前登入用戶？
  - A: 用 `Auth::user()` 或 `request()->user()`。
- Q: 如何登出？
  - A: 用 `Auth::logout()`，API Token 則刪除 token。
- Q: 如何限制登入次數？
  - A: 用 throttle middleware 或 RateLimiter。

---

## 13. 參考文件

- [Laravel 官方認證文件](https://laravel.com/docs/authentication)
- [Laravel Sanctum 文件](https://laravel.com/docs/sanctum)
- [Laravel Passport 文件](https://laravel.com/docs/passport)
- [Laravel Fortify 文件](https://laravel.com/docs/fortify)
- [Starter Kits 比較](https://laravel.com/docs/starter-kits)
- [Rate Limiting 筆記](./laravel_rate_limiting_notes.md)

---

## 14. *HTTP Basic Authentication*

### 14.1 **基本用法**
- 只需在路由加上 `auth.basic` middleware：
  ```php
  Route::get('/profile', function () {
      // 只有通過 HTTP Basic 認證的用戶可進入
  })->middleware('auth.basic');
  ```
- 預設用 email 欄位作為帳號。
- 瀏覽器會自動跳出帳密輸入視窗。
- 適合快速測試、內部工具、API 測試。

### 14.2 **FastCGI 注意事項**
- **FastCGI 是什麼？*
  - FastCGI（Common Gateway Interface）是一種 *Web* 伺服器與*應用程式*（如 PHP）之間的*高效通訊協定*。
  - 與傳統 CGI 相比，FastCGI 會重複利用常駐的 PHP 程序，效能更高。
  - 在 Apache + PHP-FPM（FastCGI Process Manager）架構下，常見於高流量網站。
  - 若用 Apache + FastCGI，*HTTP Basic Auth header* 可能不會自動傳遞，需在 **.htaccess** 加特殊設定。
- 若用 Apache + FastCGI，需在 .htaccess 加：
  ```apache
  RewriteCond %{HTTP:Authorization} ^(.+)$
  RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
  ```

### 14.3 **Stateless Basic Auth（無狀態）**
- 不產生 session/cookie，適合 *API*：
  ```php
  // app/Http/Middleware/AuthenticateOnceWithBasicAuth.php
  namespace App\Http\Middleware;
  // 匿名函式型別提示
  use Closure;
  // 載入 Laravel 的 Request 物件
  use Illuminate\Http\Request;
  // 載入 Laravel 的認證 Facade
  use Illuminate\Support\Facades\Auth;
  // 載入回應型別
  use Symfony\Component\HttpFoundation\Response;

  class AuthenticateOnceWithBasicAuth
  {
      // 處理進入 middleware 的請求
      public function handle(Request $request, Closure $next): Response
      {
          // 執行 HTTP Basic Auth 驗證（無狀態，不產生 session/cookie）
          // Auth::onceBasic() 驗證失敗時會直接回傳 401 Unauthorized response

          // Auth::onceBasic() 是 Laravel 提供的 HTTP Basic 認證（Basic Auth）快速用法
          // - 只針對本次請求驗證帳密，不會產生 session/cookie（無狀態）
          // - 驗證失敗會自動回傳 401，瀏覽器跳出帳密輸入框
          // - 驗證成功才會繼續往下走（但不會登入，只針對這次請求有效）
          // - 適合內部 API、測試、簡單保護，不建議用於正式用戶登入
          // 運作流程：
          //   1. 檢查 HTTP header 是否有帳密（Authorization header）
          //   2. 沒有就回傳 401，瀏覽器跳出輸入框
          //   3. 有就比對資料庫，成功繼續，失敗回 401

          // 驗證成功時回傳 null，繼續執行 $next($request)
          // 為什麼驗證成功要回傳 null？
          //   → null 代表「沒事，請繼續」，讓請求進入下一個 middleware 或 controller
          //   → 驗證失敗時才會回傳 response 直接中斷
          // 為什麼回傳 null 可以繼續？
          //   → 因為 ?: 運算子遇到 null 會執行右邊的 $next($request)
          //   → 這是 Laravel middleware 常見的流程分支設計

          // 補充：?: 和三元運算子的差異
          //   - 三元運算子語法：A ? B : C，條件成立回傳 B，不成立回傳 C
          //   - Elvis operator（?:）語法：A ?: C，A 有值就回傳 A，否則回傳 C
          //   - 其實 A ?: C 等同於 A ? A : C
          //   - 適合用於「有值就用自己，沒值才用預設」的情境
          //   - 例：
          //       $user = $input ?: 'Guest'; // $input 有值就用 $input，否則用 'Guest'
          //       $user = $input ? $input : 'Guest'; // 三元運算子寫法

          // ?: 是 PHP 的 null 合併運算子（又稱 Elvis operator），等同於 if-else 判斷：
          //   return A ?: B; 等同於 if (A) { return A; } else { return B; }
          // 常見用法：
          //   - 當左邊運算式為 null/false/空字串時，會執行右邊的運算式
          //   - 可用於 middleware、預設值、簡化條件分支等
          //   - 例如：$value = $input ?: 'default'; // 若 $input 為空則用 'default'

          // 白話說明：
          //   如果 Auth::onceBasic() 有回傳值（驗證失敗，回傳 401 response），就直接 return 這個 response，請求結束
          //   如果 Auth::onceBasic() 回傳 null（驗證成功），就 return $next($request)，請求繼續往下走
          //   等同於：
          //   if (Auth::onceBasic()) {
          //       return Auth::onceBasic();
          //   } else {
          //       return $next($request);
          //   }
          
          return Auth::onceBasic() ?: $next($request);
      }
  }
  ```
  - 路由加上 middleware：
    ```php
    Route::get('/api/user', function () {
        // ...
    })->middleware(AuthenticateOnceWithBasicAuth::class);
    ```

---

## 15. *登出與 Session 失效*

### 15.1 **登出**
- 用 `Auth::logout()` 移除登入資訊：
  ```php
  use Illuminate\Http\Request;
  use Illuminate\Http\RedirectResponse;
  use Illuminate\Support\Facades\Auth;

  public function logout(Request $request): RedirectResponse
  {
      Auth::logout();
      $request->session()->invalidate();
      $request->session()->regenerateToken();
      return redirect('/');
  }
  ```
- 建議同時失效 session 並重生 CSRF token。

### 15.2 **只登出其他裝置**
- 需加上 `auth.session` middleware：
  ```php
  Route::middleware(['auth', 'auth.session'])->group(function () {
      // ...
  });
  ```
- 用 `Auth::logoutOtherDevices($currentPassword)` 只登出*其他裝置*：
  ```php
  // Auth::logoutOtherDevices($password) 用法註解：
  // - 只登出「其他裝置」的 session，保留目前這個 session（你現在用的瀏覽器/裝置不會被登出）
  // - 常用於更改密碼、帳號安全設定時，強制其他地方全部登出
  // - 流程：
  //   1. 驗證你輸入的密碼正確
  //   2. 只保留目前這個 session，其他 session（其他裝置/瀏覽器）全部登出
  //   3. 不會把你現在這個 session 登出
  // - 與 Auth::logout()（全部登出）不同，logoutOtherDevices 只影響其他裝置
  // - 範例：
  //     Auth::logoutOtherDevices($request->input('password'));
  // - 注意：必須傳入正確密碼，否則不會執行
  Auth::logoutOtherDevices($currentPassword);
  ```

---

## 16. *密碼確認（Password Confirmation）*

### 16.1 **密碼確認流程**
- 適用於*敏感操作（如設定、刪除帳號）*。
- 需兩條路由：
  1. **顯示密碼確認表單**：
     ```php
     // 顯示密碼確認表單的路由
     Route::get('/confirm-password', function () {
         // 回傳密碼確認的 Blade 視圖（auth/confirm-password.blade.php）
         return view('auth.confirm_password');
     })->middleware('auth')->name('password.confirm');
     // middleware('auth')：只有已登入用戶才能進入
     // name('password.confirm')：方便在其他地方用 route('password.confirm') 產生網址
     ```
  2. **處理密碼確認**：
     ```php
     use Illuminate\Http\Request;
     // 處理密碼確認表單送出的路由
     Route::post('/confirm-password', function (Request $request) {
         // 驗證用戶輸入的密碼是否正確
         if (!Hash::check($request->password, $request->user()->password)) {
             // 密碼錯誤，回傳錯誤訊息
             return back()->withErrors([
                 'password' => '密碼錯誤，請重新輸入。',
             ]);
         }
         // 密碼正確，將密碼確認時間存入 session
         $request->session()->passwordConfirmed();
         // 導向原本要執行的敏感操作頁面
         return redirect()->intended();
     })->middleware('auth');
     // middleware('auth')：只有已登入用戶才能進入
     ```
- 受保護路由加上 `password.confirm` middleware：
  ```php
  Route::get('/settings', function () {
      // ...
  })->middleware(['password.confirm']);
  ```
- 密碼確認有效時間可調整 `config/auth.php` 的 `password_timeout`。

---

## 17. *客製 Guard 與 Provider*

### 17.1 **自訂 Guard**
- 在 *ServiceProvider* 的 *boot* 方法註冊：
  ```php
  use Illuminate\Support\Facades\Auth;
  Auth::extend('jwt', function ($app, $name, array $config) {
      return new JwtGuard(Auth::createUserProvider($config['provider']));
  });
  ```