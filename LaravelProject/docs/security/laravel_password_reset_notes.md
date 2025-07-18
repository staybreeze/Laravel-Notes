# Laravel 密碼重設（Password Reset）完整筆記

## *介紹*
大多數 Web 應用都會提供「**忘記密碼**」功能。
Laravel 內建**密碼重設服務**，能**自動**寄送重設連結並安全地重設密碼。

## 1. *設定*
- 設定檔位於 `config/auth.php`，可選擇 `database` 或 `cache` 作為密碼重設資料儲存方式。
- 預設為 `database`，需有 `password_resets` 資料表（Laravel 預設 migration 已含）。
- 若用 `cache`，可指定獨立的 **cache store**，避免被 cache:clear 清除。

## 2. *Model 準備*
- `App\Models\User` 必須 use `Illuminate\Notifications\Notifiable` trait（Laravel 預設已含）。
- 並實作 `Illuminate\Contracts\Auth\CanResetPassword` 介面（Laravel 預設已含）。

## 3. *路由與流程*

### 3.1 **請求密碼重設連結**
- *顯示請求表單*：

```php
// 定義「忘記密碼」頁面的 GET 路由
Route::get('/forgot-password', function () {
    // 回傳 resources/views/auth/forgot-password.blade.php 視圖，顯示忘記密碼表單
    return view('auth.forgot_password');
})
// 只允許未登入（訪客）使用此路由，已登入者會被導向其他頁面
->middleware('guest')
// 路由命名為 password.request，方便在程式或 Blade 中用 route('password.request') 產生網址
->name('password.request');
```
- 表單需有 email 欄位。

- *處理表單送出*：

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

// 處理忘記密碼表單送出（POST 請求）
Route::post('/forgot-password', function (Request $request) {
    // 驗證 email 欄位必填且格式正確
    $request->validate(['email' => 'required|email']);

    // 呼叫 Laravel 內建 Password::sendResetLink 方法，寄送密碼重設信件
    $status = Password::sendResetLink(
        $request->only('email') // 只取出 email 欄位
    );

    // 根據寄送狀態回傳不同訊息
    return $status === Password::RESET_LINK_SENT
        // 寄送成功，回傳狀態訊息
        ? back()->with(['status' => __($status)])
        // 寄送失敗，回傳錯誤訊息
        : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');
```

### 3.2 **重設密碼**
- *顯示重設密碼表單*：

```php
Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset_password', ['token' => $token]);
})->middleware('guest')->name('password.reset');
```
- 表單需有 email、password、password_confirmation、token 欄位。

- *處理密碼重設*：

```php
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

// 處理密碼重設表單送出（POST 請求）
Route::post('/reset-password', function (Request $request) {
    // 驗證表單欄位：email、password、password_confirmation、token 都必填，且密碼需符合規則
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    // 呼叫 Laravel 內建 Password::reset 方法，執行密碼重設邏輯
    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'), // 只取出需要的欄位
        function ($user, $password) {
            // 密碼重設成功時的處理：將新密碼雜湊後儲存
            $user->password = Hash::make($password);
            $user->save();
            // 可選：自動登入使用者
            // Auth::login($user);
        }
    );

    // 根據重設狀態回傳不同訊息
    return $status === Password::PASSWORD_RESET
        // 密碼重設成功，導向登入頁並顯示成功訊息
        ? redirect()->route('login')->with('status', __($status))
        // 密碼重設失敗，回到表單並顯示錯誤訊息
        : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');
```

## 4. *清除過期 Token*
- 使用 **database** driver 時，過期 token 仍會留在資料庫，可用指令清除：

```shell
php artisan auth:clear-resets
```
- 可加入 *s*cheduler** 自動清理：

```php
use Illuminate\Support\Facades\Schedule;
Schedule::command('auth:clear-resets')->everyFifteenMinutes();
```

## 5. *客製化*
- **重設連結網址**：可在 `AppServiceProvider` 的 boot 方法自訂：

```php
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;

ResetPassword::createUrlUsing(function (User $user, string $token) {
    return 'https://example.com/reset-password?token=' . $token;
});
```

- **自訂通知內容**：可覆寫 User model 的 `sendPasswordResetNotification` 方法：

```php
use App\Notifications\ResetPasswordNotification;

public function sendPasswordResetNotification($token): void
{
    $url = 'https://example.com/reset-password?token=' . $token;
    $this->notify(new ResetPasswordNotification($url));
}
```

## 6. *注意事項*
- 若要**自訂語系**，需發佈 lang 檔案：

> Laravel 密碼重設流程的提示訊息（如「密碼重設連結已寄出」、「密碼重設成功」等）會自動從語系檔（如 resources/lang/zh-TW/passwords.php）讀取。
> 預設專案可能沒有這些語系檔，必須先用下列指令將官方語系檔案發佈到專案，才能自訂訊息內容（如改為繁體中文或自訂文案）。

```shell 
php artisan lang:publish
```
- 密碼重設流程需自行設計 Blade 視圖。
- 建議使用 *Laravel Starter Kit* 可自動產生所有相關頁面與流程。
- 若用 *cache* driver，請**勿**用 email 當作其他 cache key。

## 7. *Laravel 內建密碼重設相關 Methods*

- **Password::sendResetLink(array $credentials)**
  - *寄送密碼重設連結*到指定 email。
  - 用法：`Password::sendResetLink(['email' => $email])`
  - *回傳狀態碼*（如 Password::RESET_LINK_SENT、Password::INVALID_USER）。

- **Password::reset(array $credentials, Closure $callback)**
  - 執行*密碼重設流程*：自動驗證 token、email、密碼等欄位，全部通過後才會執行你提供的 callback（通常用來更新使用者密碼）。

  - 執行*密碼重設流程*：
    - 這個方法會自動處理整個密碼重設的標準流程，包括：
      - 驗證 token 是否有效（token 是寄到 email 的那組亂數字串）
      - 驗證 email 是否存在於資料庫
      - 驗證新密碼是否符合規則（如長度、確認密碼一致等）
    - 如果以上全部通過，才會執行你提供的 callback，通常用來將新密碼雜湊後儲存到資料庫。
    - callback 會自動注入 `$user`（Eloquent 實例）與 `$password`（明文新密碼），你只需負責更新密碼即可。
    - 此方法會自動處理 token 失效、事件派送、狀態回傳等細節。
    - 常見於密碼重設表單送出時呼叫。
  - 用法：
    ```php
    Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
        $user->password = Hash::make($password);
        $user->save();
    });
    ```
  - *回傳狀態碼*（如 Password::PASSWORD_RESET、Password::INVALID_TOKEN）。

- **Password::broker($name = null)**
  - 取得*指定密碼重設 broker 實例*（預設為 users）。
  - 用於*多身分系統*（如前台會員、後台管理員各自有密碼重設流程）。
  - 用法：`Password::broker('admins')->sendResetLink([...])`
    - broker 代表「*密碼重設流程的執行者*」，可用於多身分系統（如前台會員、後台管理員各自有密碼重設流程）。
    - 你可以在 `config/auth.php` 設定多個 broker，例如 users、admins。
    - 用法：`Password::broker('admins')->sendResetLink([...])` 會用 admins broker 的設定與資料表。
    - 若不指定，預設用 *users* broker。
    - 常見於需要針對不同身分（如管理員、一般用戶）分開處理密碼重設時使用。

- **Password::getRepository()**
  - 取得*密碼重設 token 的儲存庫（repository）實例* -> `PasswordBrokerTokenRepository`
  - 可用於自訂 token 儲存邏輯或進階擴充。
    - 這個方法會回傳一個 `PasswordBrokerTokenRepository` 物件，負責*管理密碼重設 token 的產生、儲存、驗證與刪除*。

    - `PasswordBrokerTokenRepository` 是 Laravel 內建的類別，實際上預設為 `Illuminate\Auth\Passwords\DatabaseTokenRepository`，負責密碼重設 token 的所有資料庫操作。

    - Laravel 會根據 `config/auth.php` 設定自動建立這個 repository，通常不需手動操作。
    - 常見於進階*自訂密碼重設流程*時使用，例如：
      - 想自訂 token 的儲存方式（如改用 Redis、外部服務等）
      - 需要手動產生、驗證、刪除 token
      - 進行安全性加強或整合特殊驗證邏輯
    - 一般開發不需手動呼叫，僅進階需求才會用到。
    - 回傳內容為一個 repository 物件，可呼叫如 *create、exists、delete* 等方法操作 token。

// 這些方法皆可在 **controller、route、service** 內直接呼叫，配合驗證、回傳狀態與自訂流程。

---

參考官方文件：[Laravel Password Reset](https://laravel.com/docs/10.x/passwords) 