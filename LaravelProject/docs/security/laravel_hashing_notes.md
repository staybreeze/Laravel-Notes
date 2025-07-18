# Laravel Hash 專有名詞註解

- **Hash facade**
  - Laravel 內建的靜態門面（facade），提供*密碼雜湊*與*驗證*的簡單 API。
  - 常用方法：`Hash::make()`（雜湊密碼）、
            `Hash::check()`（驗證密碼）、
            `Hash::needsRehash()`（判斷是否需要重新雜湊）。

- **Bcrypt**
  - 一種安全的*密碼雜湊演算法*，專為密碼儲存設計。
  - 特點：有「*work factor*」（成本參數），可調整計算複雜度，讓暴力破解變得更困難。
  - Laravel 預設註冊、登入、密碼重設都用 Bcrypt。

- **Argon2**
  - 另一種*現代密碼雜湊演算法*，2015 年密碼學競賽冠軍。
  - 分為 Argon2i、Argon2d、Argon2id 三種模式，Laravel 支援 Argon2i 與 Argon2id。
  - 特點：可調整*記憶體成本*與*運算時間*，對抗 GPU/ASIC 攻擊更有效。

- **work factor（成本參數）**
  - Bcrypt 的一個*設定值*，決定雜湊運算的複雜度（預設 10）。
  - 數值越高，計算越慢，安全性越高，但也會消耗更多伺服器資源。
  - 可隨硬體效能提升而調高，讓密碼雜湊永遠保持「夠慢」以防止暴力破解。

- **密碼雜湊（Hashing）**
  - 將*明文密碼*經過*不可逆的運算*轉換成一串*亂碼（hash）*，即使資料庫被盜也無法還原原始密碼。
  - 驗證時是「*比對雜湊值*」而不是比對明文。

- **雜湊驗證（Hash Check）**
  - 用 `Hash::check(明文, 雜湊值)` 來驗證密碼是否正確。
  - 不會還原明文，只會比對運算結果。

---

# Laravel 雜湊（Hashing）完整筆記

## *介紹*
Laravel 的 **Hash facade** 提供安全的 **Bcrypt** 與 **Argon2** 密碼雜湊，預設註冊與登入皆使用 Bcrypt。Bcrypt 的 **work factor** 可調整，能隨硬體效能提升而增加計算成本，提升安全性。

## 1. *設定*
- Laravel 預設使用 bcrypt 雜湊驅動。
- 可用 `.env` 的 `HASH_DRIVER` **變數指定雜湊演算法（支援 bcrypt、argon、argon2id）**。
- 若需自訂所有雜湊選項，可發佈完整設定檔：

```shell
php artisan config:publish hashing
```

## 2. *基本用法*

### 2.1 **密碼雜湊**
- 使用 `Hash::make` 產生密碼雜湊：

```php
use Illuminate\Support\Facades\Hash;

$hashed = Hash::make('plain-text');
```

#### **範例：更新使用者密碼**
```php
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        // 驗證新密碼長度...
        $request->user()->fill([
            'password' => Hash::make($request->newPassword)
        ])->save();
        // - fill() 是 Eloquent 的批量賦值方法，可一次性設定多個欄位的值（如 token）
        // - fill() 只會改物件屬性，不會自動存進資料庫，需搭配 save() 才會寫入
        // - 只有 $fillable 屬性中列出的欄位才能被 fill，防止批量賦值漏洞
        return redirect('/profile');
    }
}
```

### 2.2 **調整 Bcrypt Work Factor**
- 可用 `rounds` 選項調整 Bcrypt 雜湊強度（預設值已足夠）：

```php
$hashed = Hash::make('password', [
    // rounds：Bcrypt 的 work factor（成本參數），數值越高，計算越慢，安全性越高
    // 預設值通常是 10，這裡設為 12 會更慢更安全，但也會消耗更多 CPU
    'rounds' => 12,
]);
```

### 2.3 **調整 Argon2 Work Factor**
- 可用 `memory`、`time`、`threads` 選項調整 Argon2 強度：

```php
$hashed = Hash::make('password', [
    // memory：雜湊時使用的記憶體（單位：KB），數值越高越安全但越耗資源
    'memory' => 1024,
    // time：運算迭代次數，數值越高越安全但越慢
    'time' => 2,
    // threads：運算時使用的執行緒數量，通常設為 CPU 核心數
    'threads' => 2,
]);
```
- 詳細參數請參考 PHP Argon 官方文件。

### 2.4 **驗證密碼**
- 使用 `Hash::check` 驗證*明文密碼*與*雜湊值*是否相符：

```php
// 語法：Hash::check('明文密碼', '雜湊值')
if (Hash::check($inputPassword, $user->password)) {
    // 密碼正確
} else {
    // 密碼錯誤
}
```

### 2.5 **需要重新雜湊（Rehash）**
- 使用 `Hash::needsRehash` 判斷雜湊值是否需要用新參數重新雜湊：

```php
// 語法：Hash::needsRehash('雜湊值')
if (Hash::needsRehash($user->password)) {
    // 需要重新雜湊（例如 work factor 提高、演算法更換）
    $user->password = Hash::make($inputPassword);
    $user->save();
}
```

### 2.6 **雜湊演算法驗證**
- `Hash::check` 會先驗證雜湊值是否由目前設定的演算法產生。
- 若演算法不同，會拋出 `RuntimeException`。
- 若需支援多種演算法（如遷移），可在 `.env` 設定：
```
HASH_VERIFY=false
```
> Laravel 10.13+ 新增的密碼雜湊驗證相容性選項。
> 預設 `HASH_VERIFY=true`，Laravel 會*嚴格檢查密碼雜湊值是否由目前設定的演算法產生（如 bcrypt）*。
> 設為 `false`，Laravel 只要能*正確比對密碼就通過，不管雜湊值來源演算法*。
> 適合密碼演算法遷移期或多系統整合時暫時開啟，遷移完成後建議設回 `true` 以提升安全性。

#### *明文密碼如何進行雜湊（hash）？*
- 使用 `Hash::make('明文密碼')` 進行雜湊。
- Laravel 會根據 `config/hashing.php` 設定的 `driver`（如 bcrypt、argon、argon2id）自動選擇演算法。
- 產生的雜湊值會包含演算法資訊，方便日後驗證與升級。

```php
// 將明文密碼進行雜湊
$hashedPassword = Hash::make('my-password'); // 會根據 config/hashing.php 的 driver 自動選擇演算法
```

```php
// 假設目前預設演算法為 bcrypt
// $inputPassword：使用者輸入的明文密碼
// $user->password：資料庫中已雜湊的密碼字串
if (Hash::check($inputPassword, $user->password)) {
    // 當 HASH_VERIFY=true 時，只有密碼正確且 hash 值是用目前設定演算法（如 bcrypt）產生才會通過
    // 當 HASH_VERIFY=false 時，只要密碼正確就通過，不管 hash 值是什麼演算法
    
    
    // Laravel 會自動偵測 $user->password（第二個參數）是用哪種演算法產生
    // 只要 hash 字串能正確驗證明文密碼，不管演算法為何都會通過
    // 例如：
    // - 開頭是 $2y$ 代表 bcrypt，Laravel 會用 bcrypt 驗證
    // - 開頭是 $argon2id$ 代表 argon2id，Laravel 會用 argon2id 驗證
    // 這個自動判斷不受 config/hashing.php driver 影響（只要 HASH_VERIFY=false）
    // 方便多種演算法共存，支援密碼升級或多系統整合
} else {
    // 密碼錯誤，或（HASH_VERIFY=true 時）hash 演算法不符
}
// 若 $user->password 是用不同演算法產生，且 HASH_VERIFY=true，會拋出 RuntimeException
// 實務建議：
// 1. 密碼演算法遷移期，先設 HASH_VERIFY=false，讓舊密碼能登入，並於登入時用 needsRehash 自動升級 hash
// 2. 遷移完成後，設回 HASH_VERIFY=true，強制所有密碼都用新演算法
```

## 3. *注意事項*
- 密碼雜湊**不可逆**，僅能驗證，不可還原明文。
- work factor 越高，安全性越高但運算越慢。
- 不同演算法的雜湊值**格式不同**，請勿混用。
- 密碼雜湊不可用於加密一般資料（僅適用**密碼驗證**）。

---

參考官方文件：[Laravel Hashing](https://laravel.com/docs/10.x/hashing) 