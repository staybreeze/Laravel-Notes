# *Laravel CSRF Protection 跨站請求偽造防護 筆記*

---

## 1. **簡介與核心概念**

- **CSRF（Cross-site request forgery，跨站請求偽造）**是一種攻擊手法，*讓未授權指令在已認證用戶名下執行*。
- Laravel 內建 CSRF 防護，確保每個重要`請求都來自合法用戶`。
- *生活化比喻*：CSRF token 就像「__專屬密碼__」，每個使用者（或每個 session）都有自己的密碼，每次操作都要驗證，防止他人冒用或偽造請求。

---

## 2. **攻擊範例說明**

- 想像有一個 `/user/email` 路由，_允許用戶更改 email_：

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
    <!-- 攻擊者可以透過各種方式誘導用戶，例如：

    1. 發送釣魚郵件，內容包含惡意連結或表單
    2. 在社群網站、論壇貼上惡意連結
    3. 透過廣告或第三方網站嵌入隱藏表單或自動提交腳本

    只要用戶在已登入狀態下點擊或瀏覽這些內容，
    瀏覽器就會自動帶上 session cookie，
    導致未經授權的請求被送到你的應用程式。 -->
  ```
- 若無 CSRF 防護，攻擊者只要誘導用戶點擊惡意網站，就能竄改用戶資料。

---

## 3. **Laravel CSRF 防護原理**

- Laravel 會為每個 *活躍 session* 產生一組 _隨機 CSRF token_。
- 這個 token 會儲存在 session，__每次 session regenerate 時都會更新__。
- 只有 *合法用戶* 能取得 token，攻擊者無法預知。
- __所有 `POST、PUT、PATCH、DELETE` 請求都會自動驗證 CSRF token__。

<!-- CSRF token 之所以只有本人拿得到，是因為：

        1. token 只存在於使用者自己的 session，攻擊者無法直接取得。
        2. 當你瀏覽自己的表單頁面時，Laravel 會把 token 放在表單的 hidden 欄位。
        3. 攻擊者無法預先知道你的 token，也無法在他自己的網站產生你的 token。
        4. 只有你本人瀏覽、提交表單時，才會帶上正確的 token。

    所以，攻擊者即使誘導你送出請求，也無法帶上正確的 CSRF token，
    伺服器就能判斷這不是你本人操作。

    如果你是在自己網站的表單頁面被誘導點擊「送出」按鈕，
    那確實會把正確的 CSRF token 一起送出去，
    這種情況下，CSRF 防護就擋不住，
    所以 CSRF 防護主要是防止「第三方網站」偽造請求，
    而不是防止你自己網站上的表單被你本人送出。

    攻擊者無法在他自己的網站產生你的 CSRF token，
    所以他只能用假的 token，這時 Laravel 就能擋下。
    但如果你已經在自己網站頁面，惡意腳本讓你送出表單，
    這屬於 XSS（跨站腳本）攻擊，不是 CSRF。

    第三方網站偽造請求是指：
    攻擊者在非你網站的網頁上放一個表單或腳本，
    誘導你瀏覽並自動向你的網站發送請求，
    利用你已登入的身分，讓你的瀏覽器帶上 session，
    但因為沒有正確的 CSRF token，
    Laravel 就能擋下這種偽造請求。

    擊者會在自己的網站做一個表單，
    誘使你用自己的瀏覽器送出請求到你的伺服器，
    如果你的伺服器沒有 CSRF 防護，
    攻擊者就能成功偽造請求，竄改你的資料。 -->

---

【補充說明】

1. *什麼是同源政策（Same-Origin Policy, SOP）？*

    - 瀏覽器安全機制，規定 **不同協議（protocol）、網域（domain）、埠號（port）** 的 JS __不能互相存取 cookie、localStorage 等敏感資料__。
      <!-- 這是瀏覽器的基本安全規則，防止不同網站之間互相竊取資料。 -->
    - 例如：`evil.com` 的 JS 不能讀取 `bank.com` 的 cookie。
      <!-- 即使你同時開著兩個網站，A 網站的 JS 也無法直接存取 B 網站的 cookie。 -->
    - 但`瀏覽器發送請求`時，會自動`帶上對應網域的 cookie`（如 session-id）。
      <!-- 只要是對 bank.com 發送請求（不論是表單、圖片、AJAX），瀏覽器都會自動附帶 bank.com 的 cookie。 -->

2. *為什麼 CSRF Token 比 session-id 更安全？*

    - `session-id` 只要被瀏覽器帶上，伺服器就認為是合法用戶。
      <!-- 伺服器只看 session-id 是否有效，不管這個請求是不是你本人操作。 -->
    - 攻擊者雖無法竊取 `session-id`，但能誘導你的瀏覽器自動帶上 `session-id` 發送偽造請求（CSRF 攻擊）。
      <!-- 攻擊者利用你已登入的狀態，讓你的瀏覽器自動發送請求，冒充你本人。 -->
    - `CSRF Token` 是一組 _隨機字串_，僅合法用戶能取得，且必須隨請求一同提交。
      <!-- 只有你本人登入後，頁面才會產生正確的 CSRF Token，攻擊者無法預知。 -->
    - **攻擊者無法預知或取得 CSRF Token**，因此即使能誘導請求，也無法偽造正確的 token，請求會被伺服器拒絕。
      <!-- 攻擊者即使能讓你的瀏覽器發送請求，也無法帶上正確的 CSRF Token，請求會被擋下。 -->

    - 簡單例子：
    <!-- 此例重點：不是 bank 向 bank 發起請求，而是你在瀏覽 evil.com 時，evil.com 利用你的瀏覽器自動帶上 bank.com 的 session-id，偽造你本人對 bank.com 發起操作（例如轉帳、改密碼）。 -->

      1. 你在 `bank.com` 登入，`session-id` 已存在 `cookie`。
         <!-- 你已經登入，瀏覽器有 bank.com 的 session-id。 -->
      2. 攻擊者在 `evil.com` 放一個表單指向` bank.com`，瀏覽器會自動帶上 `session-id`。
         <!-- 你瀏覽惡意網站時，瀏覽器自動帶上 bank.com 的 session-id。 -->
      3. 但表單內無法帶上正確的 `CSRF Token`，請求會被 `bank.com` *拒絕*。
         <!-- 因為缺少正確的 CSRF Token，bank.com 會判斷這不是你本人操作，直接拒絕。 -->


---

## 4. **取得與使用 CSRF Token**

- *取得 token*：

  ```php
  use Illuminate\Http\Request; // 引入 Request 物件，方便取得 session 等資訊
  Route::get('/token', function (Request $request) {
      $token = $request->session()->token(); // 從 session 物件取得 CSRF token（舊寫法，Laravel 7 以前）
      $token = csrf_token(); // 直接用輔助函式取得 CSRF token（推薦寫法）
      // ...  // 這裡可以將 token 回傳給前端或做其他用途
  });
  ```

---
  
- *Blade 表單自動帶入 token*：

  ```html
  <form method="POST" action="/profile">
      @csrf
      <!-- 等同於... -->
      <input type="hidden" name="_token" value="{{ csrf_token() }}" />
  </form>
  ```
---

- *驗證機制*：

  - `Illuminate\Foundation\Http\Middleware\ValidateCsrfToken` 會**自動驗證**請求中的 token 是否與 session 相符。

---

## 5. **SPA 與 API 的 CSRF 防護**

- 若是 SPA，建議使用 Laravel` Sanctum`，詳見 Sanctum 官方文件。

---

## 6. **排除特定 URI 不驗證 CSRF**

- 有些 *webhook*（如 Stripe）`無法帶 CSRF token`，可排除驗證：

  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->validateCsrfTokens(except: [
          'stripe/*',
          'http://example.com/foo/bar',
          'http://example.com/foo/*',
      ]);
  })
  ```

- *建議*：這類路由盡量不要放在 `web middleware group` 內。
<!--     因為 web middleware group 會自動套用 CSRF 驗證、Session 等功能，
         而 webhook 通常是外部服務（如 Stripe）發送，
         無法帶 CSRF token，也不需要 Session，
         所以建議這類路由用 api 群組或自訂 middleware，
         避免不必要的驗證失敗或效能浪費。 -->

- *測試時*：所有路由**預設**都不驗證 CSRF。

---

## 7. **X-CSRF-TOKEN 標頭（AJAX/前端）**

<!--  這樣做是因為 AJAX 請求（如 jQuery 的 $.ajax）不會自動帶表單 hidden 欄位的 CSRF token，
      所以需要用 X-CSRF-TOKEN 標頭手動傳送 token，
      Laravel 會檢查這個標頭，確保請求安全。
      用 meta tag 可以讓前端程式碼方便取得 token 並自動加到每次 AJAX 請求裡。 -->

- 除了檢查 `POST` 參數，Laravel 也會檢查 `X-CSRF-TOKEN` 標頭。

- *前端可用 meta tag 注入 token*：
  ```html
  <meta name="csrf-token" content="{{ csrf_token() }}">
  ```
- *jQuery 自動帶入*：
  ```js
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  ```

---

## 8. **X-XSRF-TOKEN Cookie（SPA/現代前端）**

<!-- SPA（單頁應用）通常用 AJAX 技術（如 fetch、Axios）直接發送 API 請求到後端，
     而不是用 <form> 標籤提交傳統表單。
     這樣可以即時更新畫面、不需整頁重新載入。 -->

<!-- 傳統網站用 <form> 是因為每次送出資料都會重新載入或跳轉頁面，
     而 SPA 用 AJAX 是為了讓頁面不重載、即時互動，
     提升使用者體驗和效能。 -->

<!--  這樣設計是為了讓 SPA 或 AJAX 請求也能安全通過 CSRF 驗證。
      因為 SPA 前端通常不會用傳統表單，
      所以 Laravel 會用 cookie 傳遞 CSRF token，
      前端框架再自動把 cookie 的值加到 X-XSRF-TOKEN 標頭，
      讓伺服器能正確驗證每次請求的身分。 -->

- Laravel 會自動在回應帶上加密的 `XSRF-TOKEN cookie`。
- 前端可將此 `cookie` 值設為 `X-XSRF-TOKEN 標頭`。
- *Angular、Axios 等現代前端框架會自動處理*。
- Laravel 預設 `resources/js/bootstrap.js` 已設定 __Axios 自動帶 X-XSRF-TOKEN__。

---