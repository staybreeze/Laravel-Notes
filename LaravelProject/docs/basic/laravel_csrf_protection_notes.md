# Laravel CSRF Protection 跨站請求偽造防護

---

## 1. **簡介與核心概念**

- **CSRF（Cross-site request forgery，跨站請求偽造）**是一種攻擊手法，*讓未授權指令在已認證用戶名下執行*。
- Laravel 內建 CSRF 防護，確保每個重要請求都來自合法用戶。
- *生活化比喻*： CSRF token 就像「一次性密碼」，每次操作都要驗證，防止他人冒用。

---

## 2. **攻擊範例說明**

- 想像有一個 `/user/email` 路由，允許用戶更改 email：
  ```html
  <form action="https://your-application.com/user/email" method="POST">
      <input type="email" value="malicious-email@example.com">
  </form>
  <!--
    1. 即使使用者已登入（session cookie 會自動帶上），攻擊者仍可誘導用戶的瀏覽器發送這個請求。
    2. 伺服器會認為這是合法用戶的操作，因為 session 存在且有效。
    3. 這就是 CSRF 攻擊的本質：利用用戶現有的 session 身份，發送未經授權的請求。
    4. 因此，僅有 session 驗證還不夠，必須額外驗證 CSRF Token，確保請求真的是用戶本人操作，而非被第三方網站誘導。
  -->
  <script>document.forms[0].submit();</script>
  ```
- 若無 CSRF 防護，攻擊者只要誘導用戶點擊惡意網站，就能竄改用戶資料。

---

## 3. **Laravel CSRF 防護原理**

- Laravel 會為每個 *活躍 session* 產生一組隨機 CSRF token。
- 這個 token 會儲存在 session，每次 session regenerate 時都會更新。
- 只有 *合法用戶* 能取得 token，攻擊者無法預知。
- **所有 POST、PUT、PATCH、DELETE 請求都會自動驗證 CSRF token。**

【補充說明】
1. *什麼是同源政策（Same-Origin Policy, SOP）？*
    - 瀏覽器安全機制，規定 **不同網域（domain）、協議（protocol）、埠號（port）** 的 JS 不能互相存取 cookie、localStorage 等敏感資料。
      <!-- 這是瀏覽器的基本安全規則，防止不同網站之間互相竊取資料。 -->
    - 例如：evil.com 的 JS 不能讀取 bank.com 的 cookie。
      <!-- 即使你同時開著兩個網站，A 網站的 JS 也無法直接存取 B 網站的 cookie。 -->
    - 但瀏覽器發送請求時，會自動帶上對應網域的 cookie（如 session-id）。
      <!-- 只要是對 bank.com 發送請求（不論是表單、圖片、AJAX），瀏覽器都會自動附帶 bank.com 的 cookie。 -->

2. *為什麼 CSRF Token 比 session-id 更安全？*
    - session-id 只要被瀏覽器帶上，伺服器就認為是合法用戶。
      <!-- 伺服器只看 session-id 是否有效，不管這個請求是不是你本人操作。 -->
    - 攻擊者雖無法竊取 session-id，但能誘導你的瀏覽器自動帶上 session-id 發送偽造請求（CSRF 攻擊）。
      <!-- 攻擊者利用你已登入的狀態，讓你的瀏覽器自動發送請求，冒充你本人。 -->
    - CSRF Token 是一組隨機字串，僅合法用戶能取得，且必須隨請求一同提交。
      <!-- 只有你本人登入後，頁面才會產生正確的 CSRF Token，攻擊者無法預知。 -->
    - **攻擊者無法預知或取得 CSRF Token**，因此即使能誘導請求，也無法偽造正確的 token，請求會被伺服器拒絕。
      <!-- 攻擊者即使能讓你的瀏覽器發送請求，也無法帶上正確的 CSRF Token，請求會被擋下。 -->
    - 簡單例子：
      1. 你在 bank.com 登入，session-id 已存在 cookie。
         <!-- 你已經登入，瀏覽器有 bank.com 的 session-id。 -->
      2. 攻擊者在 evil.com 放一個表單指向 bank.com，瀏覽器會自動帶上 session-id。
         <!-- 你瀏覽惡意網站時，瀏覽器自動帶上 bank.com 的 session-id。 -->
      3. 但表單內無法帶上正確的 CSRF Token，請求會被 bank.com 拒絕。
         <!-- 因為缺少正確的 CSRF Token，bank.com 會判斷這不是你本人操作，直接拒絕。 -->


---

## 4. **取得與使用 CSRF Token**

- **取得 token**：
  ```php
  use Illuminate\Http\Request; // 引入 Request 物件，方便取得 session 等資訊
  Route::get('/token', function (Request $request) {
      $token = $request->session()->token(); // 從 session 物件取得 CSRF token（舊寫法，Laravel 7 以前）
      $token = csrf_token(); // 直接用輔助函式取得 CSRF token（推薦寫法）
      // ...  // 這裡可以將 token 回傳給前端或做其他用途
  });
  ```
- **Blade 表單自動帶入 token**：
  ```html
  <form method="POST" action="/profile">
      @csrf
      <!-- 等同於... -->
      <input type="hidden" name="_token" value="{{ csrf_token() }}" />
  </form>
  ```
- **驗證機制**：
  - `Illuminate\Foundation\Http\Middleware\ValidateCsrfToken` 會自動驗證請求中的 token 是否與 session 相符。

---

## 5. **SPA 與 API 的 CSRF 防護**

- 若是 SPA，建議使用 Laravel Sanctum，詳見 Sanctum 官方文件。

---

## 6. **排除特定 URI 不驗證 CSRF**

- 有些 *webhook*（如 Stripe）無法帶 CSRF token，可排除驗證：
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->validateCsrfTokens(except: [
          'stripe/*',
          'http://example.com/foo/bar',
          'http://example.com/foo/*',
      ]);
  })
  ```
- *建議*：這類路由盡量不要放在 web middleware group 內。
- **測試時**：所有路由預設都不驗證 CSRF。

---

## 7. **X-CSRF-TOKEN 標頭（AJAX/前端）**

- 除了檢查 POST 參數，Laravel 也會檢查 `X-CSRF-TOKEN` 標頭。
- **前端可用 meta tag 注入 token**：
  ```html
  <meta name="csrf-token" content="{{ csrf_token() }}">
  ```
- **jQuery 自動帶入**：
  ```js
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  ```

---

## 8. **X-XSRF-TOKEN Cookie（SPA/現代前端）**

- Laravel 會自動在回應帶上加密的 `XSRF-TOKEN` cookie。
- 前端可將此 cookie 值設為 `X-XSRF-TOKEN` 標頭。
- **Angular、Axios 等現代前端框架會自動處理**。
- Laravel 預設 `resources/js/bootstrap.js` 已設定 Axios 自動帶 X-XSRF-TOKEN。

---