# Laravel 電子郵件驗證（Email Verification）完整筆記

## *介紹*
許多 Web 應用程式會要求使用者在使用前驗證其電子郵件地址。Laravel 內建了方便的服務來處理電子郵件驗證，無需每次都手動實作。

## 1. *Model 準備*
- 確認 `App\Models\User` 實作 `Illuminate\Contracts\Auth\MustVerifyEmail` 介面：

```php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
// as 是 PHP 的命名空間別名語法，讓長名稱可用簡短別名
// 這裡把 Illuminate\Foundation\Auth\User 取名為 Authenticatable，方便 class 繼承與避免命名衝突
use Illuminate\Foundation\Auth\User as Authenticatable;
// User 模型繼承 Authenticatable（類別），代表具備 Laravel 認證、登入、密碼等功能（extends 只能繼承一個 class）
//   - Authenticatable 是 Laravel 內建的 User 基底類別，提供所有 Auth 相關功能（如登入、密碼驗證、remember token 等）
//   - 用 extends 表示「User 是 Authenticatable 的子類別」，會自動繼承其所有屬性與方法
//   - PHP 一個類別只能 extends 一個父類別（單一繼承）
// 同時 implements MustVerifyEmail（介面），代表這個模型必須實作信箱驗證相關方法（implements 可實作多個 interface）
//   - MustVerifyEmail 是 Laravel 定義的介面（interface），規定 User 必須有 email 驗證相關方法
//   - 用 implements 表示「User 必須實作 MustVerifyEmail 介面規定的方法」
//   - PHP 一個類別可以 implements 多個介面（用逗號分隔）
// extends 用於「繼承父類別」；implements 用於「實作介面」——這是 PHP OOP 的語法規定
//   - extends 只能用在 class，代表繼承
//   - implements 只能用在 interface，代表必須實作
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    // use Notifiable：引入 Laravel 的 Notifiable trait，讓 User 可以發送通知（如 email、SMS、database notification 等）
    // trait 是 PHP 的語法，讓你可以在 class 裡「混入」一組方法，不用繼承也能用
    // ...
}
```
- 加入後，註冊新用戶時會自動寄送驗證信。
- 若自行實作註冊流程，註冊成功後需手動 *dispatch* 事件：

```php
use Illuminate\Auth\Events\Registered;
event(new Registered($user));
```

## 2. *資料庫準備*
- `users` 資料表需有 `email_verified_at` 欄位（Laravel 預設 migration 已包含）。

## 3. *路由設定*
需定義三個路由：

### (1) **顯示驗證提示頁**
```php
Route::get('/email/verify', function () {
    return view('auth.verify_email');
})->middleware('auth')->name('verification.notice');
// - name('verification.notice') 是這個路由的名稱，方便用 route('verification.notice') 取得網址
// - Laravel 內建的 verified middleware 會自動檢查用戶 email 是否已驗證
// - 如果未驗證，middleware 會自動 redirect 到名為 verification.notice 的路由（即這一頁）
// - 這是官方慣例設計，必須有這個名稱的路由，否則會報錯
// - 這個頁面內容需自行設計，通常顯示「請驗證信箱」提示
```
- 命名必須為 `verification.notice`，未驗證用戶會自動被導向此頁。
- 頁面內容需自行設計。

### (2) **驗證處理路由**
```php
use Illuminate\Foundation\Auth\EmailVerificationRequest; // 引入 Laravel 內建的 Email 驗證請求物件

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    // 用戶點擊驗證信連結時，會進到這個路由
    $request->fulfill(); // 標記用戶已驗證（更新 email_verified_at），並觸發 Verified 事件
    return redirect('/home'); // 驗證成功後導向首頁（可自訂成其他頁面）
})
->middleware(['auth', 'signed']) // middleware 說明：
    // 'auth'：必須登入才能驗證（避免未登入者亂用連結）
    // 'signed'：驗證連結必須有正確簽章，防止連結被竄改
->name('verification.verify'); // 路由命名為 verification.verify，方便系統內部或通知信產生連結
// - 用戶點擊驗證信連結後會進到這個路由
// - $request->fulfill() 會：
//   1. 標記該用戶的 email_verified_at 欄位為現在時間（即已驗證）
//   2. 觸發 Illuminate\Auth\Events\Verified 事件（可用於後續自訂邏輯）
// - 驗證成功後 redirect 到 /home（可改成任何頁面）
// - 之後再訪問需要驗證的頁面，verified middleware 會放行，不再導向驗證提示頁
```

### (3) **重新寄送驗證信**
```php
use Illuminate\Http\Request; // 引入 Laravel 的 HTTP 請求物件

Route::post('/email/verification-notification', function (Request $request) {
    // 這個路由用於重新寄送驗證信，通常會在驗證提示頁放一個「重新寄送」按鈕
    $request->user()->sendEmailVerificationNotification(); // 呼叫目前登入 user 的方法，寄出驗證信
    return back()->with('message', 'Verification link sent!'); // 寄送後回到上一頁，並帶一個訊息
})
->middleware(['auth', 'throttle:6,1']) // middleware 說明：
    // 'auth'：必須登入才能寄送驗證信
    // 'throttle:6,1'：限制 1 分鐘內最多只能寄送 6 次，防止濫用
->name('verification.send'); // 路由命名為 verification.send，方便系統內部呼叫
```
- 可在驗證提示頁放一個重新寄送的按鈕。

## 4. **保護路由**
- 使用 `verified` middleware 限制僅已驗證用戶可存取：

```php
Route::get('/profile', function () {
    // 僅已驗證用戶可進入
})->middleware(['auth', 'verified']);
// - 'auth'：必須登入
//   → 這個 middleware 會檢查用戶是否已登入，未登入會自動導向登入頁
// - 'verified'：必須已驗證 email，否則會自動被導向 verification.notice 頁面
//   → 這個 middleware 會檢查用戶 email 是否已驗證，未驗證會自動導向「請驗證信箱」提示頁（verification.notice）
```

## 5. **驗證信自訂**
- 可在 `AppServiceProvider` 的 `boot` 方法自訂驗證信內容：

```php
use Illuminate\Auth\Notifications\VerifyEmail; // 引入驗證信通知類別
use Illuminate\Notifications\Messages\MailMessage; // 引入郵件訊息類別

public function boot(): void
{
    // 自訂驗證信內容
    VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
        return (new MailMessage)
            ->subject('驗證您的電子郵件地址') // 信件主旨
            ->line('請點擊下方按鈕完成驗證。') // 內文
            ->action('驗證電子郵件', $url); // 按鈕文字與連結
    });
}

// - 這段程式碼放在 AppServiceProvider 的 boot 方法內。
// - 你可以自訂驗證信的主旨、內容、按鈕文字與連結。
// - $notifiable 代表要被通知的 user，$url 是驗證連結。
```

## 6. **事件**
- 驗證過程會觸發 `Illuminate\Auth\Events\Verified` 事件。
- 若自行處理驗證，需手動 *dispatch* 該事件。

## 7. 注意事項
- 建議使用 Laravel *Starter Kit（如 Breeze、Jetstream）*可自動產生所有驗證相關功能與頁面。
- 驗證信連結*有時效性*，過期需重新寄送。
- 驗證信寄送需設定好 *mail 驅動*與*環境變數*。

---

## User **模型內建常用方法（Laravel 內建）**

只要 User 模型繼承 *Authenticatable*、implements *MustVerifyEmail*、use *Notifiable*，會自動擁有以下常用方法：

### 1. **認證/登入相關（來自 Authenticatable）**
- `getAuthIdentifierName()`：取得主鍵欄位名稱（通常是 id）
- `getAuthIdentifier()`：取得主鍵值
- `getAuthPassword()`：取得密碼欄位值
- `getRememberToken()`：取得 remember_token
- `setRememberToken($value)`：設定 remember_token
- `getRememberTokenName()`：取得 remember_token 欄位名稱

### 2. **通知相關（來自 Notifiable trait）**
- `notify($notification)`：發送通知（可寄信、SMS、database notification）
- `routeNotificationForMail()`：取得 email 通知的收件人
- `sendPasswordResetNotification($token)`：發送密碼重設通知

### 3. **Email 驗證相關（來自 MustVerifyEmail）**
- `hasVerifiedEmail()`：判斷 email 是否已驗證
- `markEmailAsVerified()`：標記 email 已驗證
- `sendEmailVerificationNotification()`：發送 email 驗證信

### 4. **其他**
- `toArray()`：轉換為陣列（Eloquent Model 內建）
- `toJson()`：轉換為 JSON

```php
// 這段是 User 模型自動擁有的功能總結：
// - 只要 User 模型繼承 Authenticatable、implements MustVerifyEmail、use Notifiable，
//   就會自動擁有所有 Laravel 認證、通知、email 驗證相關方法，無需自己實作。
// - 這是因為 Laravel 透過繼承（class）、介面（interface）、trait（use）自動注入這些功能。
// - 另外 User 也是 Eloquent Model，所以也會有 save、find、delete、update、where... 等 ORM 方法。
// - 如果 use 其他 Laravel 內建 trait（如 HasApiTokens、SoftDeletes），也會多出對應的方法。
```

參考官方文件：[Laravel Email Verification](https://laravel.com/docs/10.x/verification) 