# *Laravel HTTP Session 工作階段*

---

## 1. **簡介與核心概念**

- *Session（工作階段）* 讓 HTTP 應用在多次請求間保存用戶資料。
- 常用於登入狀態、購物車、訊息提示等。
- Laravel 提供多種 session 後端（file、cookie、database、redis...），API 統一且易用。
- *生活化比喻*： Session 就像「臨時置物櫃」，每個用戶有自己的櫃子，跨頁都能存取。

---

## 2. **Laravel 的 Session 與原生 PHP Session 的區別**

### *存儲方式*
- **Laravel Session**：支援 *多種存儲後端* ，例如檔案、資料庫、Redis、Memcached、DynamoDB 等，並且可以通過設定檔靈活切換。
- **原生 PHP Session**：通常存儲在 *伺服器* 的檔案系統中（`/tmp` 資料夾）。

### *加密與安全性*
- **Laravel Session**：預設會 *加密存儲* 的資料，並且可以防止 Session Fixation 攻擊（通過 `regenerate()` 方法）。
- **原生 PHP Session**：需要開發者 *手動處理* 加密和安全性。

### *API 易用性*
- **Laravel Session**：提供 *統一的 API* （例如 `session()` helper 和 `$request->session()`），讓開發者可以方便地讀取、寫入、刪除 Session 資料。
- **原生 PHP Session**：使用 `$_SESSION` 超級全域變數，操作方式較為基礎。

### *擴展性*
- **Laravel Session**：支援自訂 *Session Driver* ，開發者可以實作自己的儲存後端（例如 MongoDB）。
- **原生 PHP Session**：擴展性有限，通常只能使用檔案或少數快取系統。

### *功能*
- **Laravel Session**：提供*額外功能*，例如 Flash Data（閃存資料）、Session Blocking（阻塞機制）等。
- **原生 PHP Session**：沒有這些高階功能。

---

#### **總結**

- Laravel 的 Session 是基於原生 PHP Session 的 *一層抽象* ，提供了更強大的功能和更靈活的存儲選項。
- 它適合用於現代的 Web 應用程式，特別是在需要跨多次請求保存用戶資料的情境下，例如登入狀態、購物車、訊息提示等。

## 3. **設定與驅動**

- *設定檔*： `config/session.php`
  - Laravel 的 Session 設定檔，包含**所有驅動的配置選項**。
  - 開發者可以在此檔案中設定 Session 的存儲方式、存活時間等。

- *常見 driver*：
  - **file**：
    - 存於 `storage/framework/sessions`。
    - 將 Session 資料存儲在*伺服器的檔案系統中*，類似於原生 PHP Session 的存儲方式。
    - 適合小型應用或開發環境。

  - **cookie**：
      - 將 *Session 資料存儲* 在用戶端的 Cookie 中，並進行加密。
      - 與原生的 Cookie 不同，Laravel 的 Cookie 驅動會直接存儲 *完整的 Session 資料*，而不是僅存 Session-ID。
      - 原生 PHP Session 通常只在 Cookie 中存儲 Session-ID，伺服器端會根據 Session-ID 查找對應的 Session 資料。
      - Laravel 的 Cookie 驅動適合 *不需要伺服器端* 存儲的情境，但受限於 Cookie 的大小限制（通常 4KB）。
      - 由於資料存儲在 *用戶端*，Laravel 會對 Cookie 進行加密以確保安全性，防止資料被篡改或洩漏。

  - **database**：
    - 將 Session 資料存儲在 *資料庫中* ，適合需要持久化且可擴展的應用。
    - 比檔案存儲更可靠，適合多伺服器環境。

  - **memcached/redis**：
    - 使用 Memcached 或 Redis 快取系統存儲 Session 資料。
    - 提供 *高效能存取* ，適合大型應用或需要快速存取的情境。

  - **dynamodb**：
    - 使用 AWS DynamoDB 存儲 Session 資料，適合 *雲端應用* 。
    - 提供高可用性和擴展性，適合分散式系統。

  - **array**：
    - 僅測試用，不會持久化。
    - 將 Session 資料存儲在陣列中，僅用於測試環境。
    - 資料不會持久化，適合快速測試功能。

- *注意*： 預設 driver 可用 `SESSION_DRIVER` 環境變數設定。
  - 可透過 `.env` 檔案中的 `SESSION_DRIVER` 變數設定 Session 的存儲方式。

### *Driver 前置作業*
- **database**：
  - *需有 sessions 資料表*。
  - 產生 migration：
    ```bash
    php artisan make:session-table
    php artisan migrate
    ```
    - Laravel 的 database 驅動需要在資料庫中建立 `sessions` 資料表。
    - 使用 Artisan 指令生成 migration，並執行 migration 建立資料表。

- **redis**：
  - 需安裝 phpredis 擴充或 predis 套件。
  - 設定連線用 `SESSION_CONNECTION` 變數。
    - Redis 驅動需要 Redis 伺服器的支援，並且需要安裝 PHP 的 Redis 擴充套件（如 phpredis 或 predis）。
    - 可透過 `.env` 檔案中的 `SESSION_CONNECTION` 變數設定 Redis 的連線資訊。

---

### *為什麼 Laravel 需要這些驅動？*
- **靈活性**：Laravel 提供 *多種存儲選項*，讓開發者可以根據應用需求選擇最適合的存儲方式。
- **擴展性**：相比原生 PHP Session，Laravel 的 Session *支援更多存儲後端*（如 Redis、DynamoDB），適合分散式系統或大型應用。
- **高效能**：使用 *快取型驅動*（如 Redis、Memcached）可以提高 Session 存取速度，適合需要快速存取的情境。
- **安全性**：Laravel 的 Session *預設加密存儲*，並提供防止 *Session Fixation* 攻擊的功能。
- **統一 API**：Laravel 提供 *統一的 Session API*，讓開發者可以方便地操作 Session，而不需要直接使用 `$_SESSION`。

### *與原生 PHP Session 的不同*：
- **存儲方式**：原生 PHP Session 主要存儲在 *檔案系統中* ，而 Laravel 支援 *多種存儲後端* 。
- **安全性**：Laravel *預設加密* Session 資料，原生 PHP Session 需要手動處理。
- **功能**：Laravel 提供額外功能（如 Flash Data、Session Blocking），原生 PHP Session 沒有這些高階功能。

---

## 4. **Session 讀取與操作**

### *取得資料*
- 兩種方式：*Request 實例*、全域 session *helper*
- **Request 實例**：
  ```php
  $value = $request->session()->get('key');
  $value = $request->session()->get('key', 'default');
  $value = $request->session()->get('key', function () { return 'default'; });
  ```
- **全域 helper**：
  ```php
  $value = session('key');
  $value = session('key', 'default');
  session(['key' => 'value']);
  ```
- **註解**： 兩種方式皆可用於測試（assertSessionHas）。

### *官方 Controller 範例*
  ```php
  namespace App\Http\Controllers;
  use Illuminate\Http\Request;
  use Illuminate\View\View;
  class UserController extends Controller {
      public function show(Request $request, string $id): View {
          $value = $request->session()->get('key');
          // ...
          $user = $this->users->find($id);
          return view('user.profile', ['user' => $user]);
      }
  }
  ```

### *取得全部/部分資料*
  ```php
  $all = $request->session()->all();
  $only = $request->session()->only(['username', 'email']);
  $except = $request->session()->except(['username', 'email']);
  ```

### *判斷資料是否存在*
  ```php
  $request->session()->has('users');      // 存在且不為 null
  $request->session()->exists('users');   // 存在（即使為 null）
  $request->session()->missing('users');  // 不存在
  if ($request->session()->has('users')) { /* ... */ }
  if ($request->session()->exists('users')) { /* ... */ }
  if ($request->session()->missing('users')) { /* ... */ }
  ```

---

## 4. **Session 寫入、刪除與操作**

### *寫入資料*
  ```php
  $request->session()->put('key', 'value');
  session(['key' => 'value']);
  ```

### *陣列 push*
  ```php
  $request->session()->push('user.teams', 'developers');
  ```
  - 只有 *陣列類型的 Session 資料* 可以使用 push 方法。
  - 此方法會將新的值（例如 'developers'）添加到指定的陣列（例如 'user.teams'）中。
  - 如果 'user.teams' 尚未存在，Laravel 會自動初始化為一個陣列並添加值。

### *取出並刪除（pull）*
  ```php
  $value = $request->session()->pull('key', 'default');
  ```

### *自增/自減*
  ```php
  $request->session()->increment('count');
  $request->session()->increment('count', 2);
  $request->session()->decrement('count');
  $request->session()->decrement('count', 2);
  ```

---

## 5. **Flash Data（閃存資料）**

- *只在下次請求可用，常用於狀態訊息、提示*
- **寫入**：
  ```php
  $request->session()->flash('status', 'Task was successful!');
  ```
- **延長壽命**：
  ```php
  $request->session()->reflash(); // 全部延長
  $request->session()->keep(['username', 'email']); // 指定 key 延長
  ```
- **只在本次請求有效**：
  ```php
  $request->session()->now('status', 'Task was successful!');
  ```

---

## 6. **刪除與清空 Session**

- *刪除單一/多個 key*：
  ```php
  $request->session()->forget('name');
  $request->session()->forget(['name', 'status']);
  ```
- *清空全部*：
  ```php
  $request->session()->flush();
  ```

---

## 7. **Session ID 管理與安全**

- *重生 Session ID（防止 session fixation 攻擊）*
  ```php
  $request->session()->regenerate();
  ```
  - 使用場景：
    - 當用戶 **登入** 或 **執行敏感操作** 時，重生 Session ID 可以防止 session fixation 攻擊。
    - session fixation 攻擊是指攻 `擊者強制用戶使用已知的 Session ID，從而竊取用戶的會話`。
    - 通過 **重生 Session ID** ，可以確保攻擊者無法預測或控制用戶的 Session ID。

- *重生並清空所有資料*
  ```php
  $request->session()->invalidate();
  ```
  - 使用場景：
    - 當用戶 **登出** 或需要 **完全重置 Session** 時，使用 invalidate 方法。
    - 此方法會重生 Session ID 並清空所有 Session 資料，確保用戶的會話完全重置。
- *註解*： Laravel 登入流程會自動 regenerate。

---

## 8. **Session Blocking（阻塞）**

- *功能*
  - 防止 **同一 session** 在`同時處理多個請求時`導致資料丟失。
  - 適用於需要確保資料一致性的場景，例如更新用戶資料或處理訂單。

- *需求*
  - 必須使用支援 *atomic lock* 的 driver（例如 file、database、redis、memcached、dynamodb、mongodb、array...）。
  - atomic lock 是一種機制，`確保同一時間只有一個請求可以操作 session`。

- **用法**
  ```php
  Route::post('/profile', function () { /* ... */ })->block($lockSeconds = 10, $waitSeconds = 10);
    // 使用 block() 方法阻塞 session 操作。
    // - $lockSeconds：最多鎖定 session 的時間（例如 10 秒）。在這段時間內，其他請求無法操作該 session。
    // - $waitSeconds：等待鎖釋放的時間（例如 10 秒）。如果鎖未釋放，請求會等待指定時間後再嘗試操作。
    // 此方法確保同一 session 不會因多個請求同時操作導致資料不一致或丟失。
  
  Route::post('/order', function () { /* ... */ })->block($lockSeconds = 10, $waitSeconds = 10);
    // 適用於處理訂單的場景。
    // 例如：用戶提交訂單時，可能會因多次點擊提交按鈕導致多個請求同時操作 session。
    // 使用 block() 方法可以防止這種情況，確保只有一個請求能操作 session，避免重複提交或資料衝突。
  
  Route::post('/profile', function () { /* ... */ })->block();
    // 預設 block() 方法的兩個參數（$lockSeconds 和 $waitSeconds）皆為 10 秒。
    // 如果未指定參數，Laravel 會使用預設值。
    // 此方法適合需要簡單阻塞 session 的場景，例如更新用戶資料。
  ```
  ### *原因與必要性*
  - **資料一致性**
    - 在多個請求同時操作同一 session 時，可能會導致資料不一致或丟失。
    - 例如：一個請求正在更新 session 資料，另一個請求同時讀取未更新的資料，可能導致錯誤。
  
  - **避免競爭條件**
    - 當多個請求同時操作 session 時，可能會出現 *競爭條件（Race Condition）*，導致資料衝突。
    - 使用 `block()` 方法可以確保 *同一時間只有一個請求能操作 session* 。
  
  - **適用場景**
    - *更新用戶資料*：防止多個請求同時修改用戶資料導致不一致。
    - *處理訂單*：防止重複提交或資料錯亂。
    - *其他需要確保資料完整性的操作*：例如支付流程或敏感操作。
  
  ### *技術背景*
  - **Atomic Lock**：
    - `block()` 方法依賴於支持 *原子鎖（Atomic Lock）* 的 driver，例如 file、database、redis 等。
    - 原子鎖是一種機制，*確保同一時間只有一個操作能成功執行*，其他操作需等待鎖釋放。
    - 這種機制可以有效避免並發操作導致的資料問題。
- *比喻*： 就像「同一把鑰匙同時只能開一個櫃子」，避免資料衝突。

---

## 9. **自訂 Session Driver**

- *可自訂儲存後端，需實作 PHP `SessionHandlerInterface`*
  - Laravel 支援自訂 Session Driver，開發者可以實作自己的儲存後端（例如 MongoDB）。
  - 必須實作 PHP 的 `SessionHandlerInterface`，以符合 Session 的操作規範。

- *範例*：
  ```php
  namespace App\Extensions;
  class MongoSessionHandler implements \SessionHandlerInterface {
      public function open($savePath, $sessionName) {
          // 初始化儲存後端，例如連接 MongoDB。
      }

      public function close() {
          // 關閉儲存後端的連線。
      }

      public function read($sessionId) {
          // 根據 sessionId 從儲存後端讀取資料，並回傳字串。
      }

      public function write($sessionId, $data) {
          // 將資料寫入儲存後端，並與 sessionId 綁定。
      }

      public function destroy($sessionId) {
          // 刪除指定 sessionId 的資料。
      }

      public function gc($lifetime) {
          // 清除過期的 session 資料（通常快取型儲存後端可留空）。
      }
  }
  ```
- *方法說明*：
  - **open/close**：通常 file driver 才需實作，其他可留空
  - **read**：回傳指定 sessionId 的資料字串
  - **write**：寫入 sessionId 對應的資料
  - **destroy**：刪除 sessionId 對應資料
  - **gc**：清除過期 session（快取型可留空）

- *註冊 driver*：
  ```php
  namespace App\Providers;
  use App\Extensions\MongoSessionHandler;
  use Illuminate\Contracts\Foundation\Application;
  use Illuminate\Support\Facades\Session;
  use Illuminate\Support\ServiceProvider;
  
  class SessionServiceProvider extends ServiceProvider {
      public function register(): void {}
  
      public function boot(): void {
          // 註冊自訂的 Session Driver，名稱為 'mongo'。
          Session::extend('mongo', function (Application $app) {
              return new MongoSessionHandler;
          });
      }
  }
  ```
- *設定 `SESSION_DRIVER=mongo`*

  - 在 .env 檔案中設定 `SESSION_DRIVER=mongo`，以啟用自訂的 MongoDB Session Driver。

---