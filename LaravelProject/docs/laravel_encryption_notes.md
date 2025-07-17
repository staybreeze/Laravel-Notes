# Laravel 加密（Encryption）完整筆記

## *介紹*
Laravel 的**加密服務**提供簡單且方便的介面，透過 *OpenSSL* 使用 *AES-256* 與 *AES-128* 加密。所有加密值都會以*訊息驗證碼（MAC）簽名*，確保資料未被竄改。

- **加密服務（Encryption Service）**：
  - Laravel 內建的加密服務，提供一組簡單的 API 讓你可以安全地加密與解密資料。
  - 主要用途：保護敏感資料（如 token、私密資訊）在資料庫或 cookie 中的安全性。

- **OpenSSL**：
  - 一個*開源的加密函式庫（library）*，支援多種加密演算法。
  - Laravel 的加密底層就是呼叫 PHP 的 *OpenSSL 擴充套件*來實作。
  - 你可以在 PHP 中用 `openssl_encrypt`、`openssl_decrypt` 等函式進行加解密。

- **AES-256 / AES-128**：
  - **AES**（Advanced Encryption Standard，高級加密標準）：現代最常用的*對稱式加密演算法*。
  - **對稱式加密**：加密與解密都用*同一把「金鑰」*。
  - **AES-256**：金鑰長度為 256 位元（bit），安全性更高。
  - **AES-128**：金鑰長度為 128 位元，速度較快但安全性略低於 256。
  - Laravel 預設使用 *AES-256-CBC（Cipher Block Chaining）*模式。

> **補充說明：什麼是 AES-256-CBC？**
>
> - **AES-256-CBC** 是 Laravel 預設的加密模式：
>   - **AES**：高級加密標準，對稱式加密演算法。
>   - **256**：金鑰長度 256 位元，安全性高。
>   - **CBC（Cipher Block Chaining）**：*密碼分組連鎖模式*，一種區塊加密運作方式。
>
> - **CBC 模式運作原理**：
>   1. 明文會被分成一個個*區塊（block）加密*。
>   2. 每個區塊加密*前*，會先跟前一個密文區塊做 XOR 運算，增加安全性。
>   3. 第一個區塊會用一組隨機產生的「*初始化向量*」（**IV**, Initialization Vector）來 XOR。
>   4. **IV** 會和密文一起儲存，解密時必須用同一組 IV。
> - CBC 模式可*防止重複明文產生重複密文*，提升安全性。

> **補充：為什麼 CBC 模式用 XOR 而不是 AND？**

> - **XOR（異或）**：
>   - 兩個位元相同為 0，不同為 1。
>   - 具有**可逆性**：A ^ B = C，C ^ B = A。
>   - 適合用於加密流程，因為加密和解密都能用同一個運算，容易還原原始資料。
>   - 密碼學常用 XOR 來混合明文與前一個密文區塊（或 IV），增加安全性。
>      - 想像你有兩張紙（A、B），你把它們「重疊」起來（但不是加法，是一種特殊規則：一樣就變 0，不一樣就變 1，這就是 XOR）。
>      - 你只要有其中一張紙（B）和重疊後的結果（C），就能還原出另一張紙（A）。
>      - 這種「可逆」的特性，讓你加密後還能解密回原本的內容。

> - **AND（與）**：
>   - 只有兩個位元都為 1 才是 1，其餘都是 0。
>   - **不可逆**：AND 運算無法還原原始資料（資訊會遺失）。
>   - 不適合用於加密，因為加密後無法安全還原明文。
>      - 如果用 AND（與），就像你把兩張紙重疊，只留下兩張紙都有的部分，其餘都丟掉。
>      - 這樣你**永遠無法還原出原本的兩張紙**，因為資訊已經遺失了。
>      - 所以 AND 不適合用來加密，因為你加密後就「回不去了」。

> - **總結**：
>   - CBC 模式選用 XOR，是因為它可逆且安全，能確保加密資料能正確還原。 
>   - XOR 適合加密，因為「可逆」；AND 不適合，因為「不可逆」。

- **訊息驗證碼（MAC, Message Authentication Code）**：
  - 一種用來驗證資料完整性與來源的「*數位簽章*」。
  - Laravel 加密後，會自動產生一組 *MAC*，附加在加密資料後面。
  - 當你解密時，Laravel 會先檢查 MAC 是否正確，**確保資料在傳輸或儲存過程中沒有被竄改**。
  - MAC 通常是用 *HMAC（Hash-based Message Authentication Code）*實作，會用一組*密鑰*與*雜湊函式*（如 SHA-256）產生。

- **HMAC（Hash-based Message Authentication Code）**：
  - 一種基於*雜湊函式*與*密鑰*的訊息驗證碼演算法。
  - 可防止資料被竄改，常用於 API 簽章、加密驗證等。

---

## 1.*設定*
- 使用加密前，需在 `config/app.php` 設定 `key` 選項。
- 此值由 `.env` 的 `APP_KEY` 變數驅動。
- 產生方式：

```shell
php artisan key:generate
```
- 安裝 Laravel 時會自動產生。

## 2. *加密金鑰輪替（Key Rotation）*
- 更換加密金鑰會導致所有已登入用戶被登出，且舊金鑰加密的資料無法解密。
- 可在 `.env` 設定 `APP_PREVIOUS_KEYS`，以逗號分隔多組舊金鑰：

```
APP_KEY="base64:xxxx...=="
APP_PREVIOUS_KEYS="base64:yyyy...==,base64:zzzz...=="
```
- 加密時永遠用現行金鑰，解密時會依序嘗試所有金鑰，確保金鑰輪替不中斷服務。

> **補充說明：多組金鑰（APP_KEY、APP_PREVIOUS_KEYS）的用途與安全性**
>
> - **金鑰輪替（Key Rotation）**：
>   - 當你需要更換金鑰（如安全政策、疑似洩漏、定期更換）時，可以新增 APP_PREVIOUS_KEYS。
>   - 新加密的資料會用 APP_KEY，舊資料（如 cookie、session、加密欄位）會依序用 APP_PREVIOUS_KEYS 嘗試解密。
>   - 這樣可以「*不中斷服務*」地更換金鑰，避免用戶被強制登出或資料無法解密。
> - **安全建議**：
>   - 輪替後，建議盡快移除最舊、不再需要的金鑰，避免安全風險。
>   - 多組金鑰不會降低安全性，只要每組金鑰都妥善保管。

## 3. *使用加密器（Encrypter）*

### 3.1 **加密字串**
- 使用 `Crypt` facade 的 `encryptString` 方法：

```php
use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encryptString('明文內容');
```
- 加密值會自動加上 *MAC*，防止被竄改。

#### **範例：儲存加密後的 Token**
```php
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class DigitalOceanTokenController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->user()->fill([
            'token' => Crypt::encryptString($request->token),
        ])->save();

        return redirect('/secrets');
    }
}
```

### 3.2 **解密字串**
- 使用 `Crypt` facade 的 `decryptString` 方法：

```php
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

try {
    $decrypted = Crypt::decryptString($encryptedValue);
} catch (DecryptException $e) {
    // 解密失敗處理
}
```
- 若 *MAC 驗證失敗*或*資料被竄改*，會拋出 `DecryptException`。

## 4. *注意事項*
- **APP_KEY** 必須保密且不可洩漏。
- 更換金鑰前，務必評估所有加密資料的影響。
- Laravel 會**自動加密**所有 cookie（包含 session cookie）。
- 加密內容**不可直接用於查詢資料庫**（因每次加密結果不同）。

---

參考官方文件：[Laravel Encryption](https://laravel.com/docs/10.x/encryption) 
