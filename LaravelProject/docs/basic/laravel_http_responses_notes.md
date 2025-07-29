
# *Laravel HTTP Responses 回應*

---

## 1. **基本回應型態**

- *字串回應*：
  ```php
  Route::get('/', function () {
      return 'Hello World';
  });
  ```
- *陣列回應*： `自動轉為 JSON`
  ```php
  Route::get('/', function () {
      return [1, 2, 3];
  });
  ```
- *Eloquent Model/Collection*： `自動轉為 JSON，會隱藏 hidden 屬性`
  ```php
  use App\Models\User;
  Route::get('/user/{user}', function (User $user) {
      return $user;
  });
  ```

---

## 2. **Response 物件與自訂標頭**

- *完整 Response 實例*：`可自訂狀態碼、標頭`
  ```php
  Route::get('/home', function () {
      return response('Hello World', 200)
          ->header('Content-Type', 'text/plain');
  });
  ```      
      - 使用 `response()` 函數建立完整的 HTTP 回應。
      - 'Hello World' 是回應的內容，200 是 HTTP 狀態碼（表示成功）。
      - 使用 `header()` 方法設定回應的標頭，例如 Content-Type 為 'text/plain'。
      - `text/plain` 是一種 MIME 類型，用於表示 *純文字內容* 。
      - 當 HTTP 回應的 Content-Type 標頭設為 `text/plain` 時，瀏覽器或客戶端會將回應內容視為純文字，而 *不進行任何格式化或解析* 。
      - 例如：回應內容可能是 "Hello World"，瀏覽器會直接顯示該文字，而不進行 HTML 渲染或其他處理。

- *多個標頭*：
  ```php

  return response($content)
      ->header('Content-Type', $type)
      ->header('X-Header-One', 'Header Value')
      ->header('X-Header-Two', 'Header Value');
      // 使用多次 header() 方法設定多個回應標頭。
  
  // 或
  return response($content)->withHeaders([
      'Content-Type' => $type,
      'X-Header-One' => 'Header Value',
      'X-Header-Two' => 'Header Value',
  ]);
      // 使用 withHeaders() 方法一次性設定多個回應標頭。
      // Content-Type 指定回應的內容類型，例如 'application/json'。
      // 自訂標頭（如 X-Header-One 和 X-Header-Two）可用於傳遞額外的資訊。
  ```

- *Cache-Control Middleware*：
  ```php
  Route::middleware('cache.headers:public;max_age=2628000;etag')->group(function () {
      Route::get('/privacy', function () { /* ... */ });
      Route::get('/terms', function () { /* ... */ });
  });
  ```
      - 使用 `cache.headers` 中介層設定 Cache-Control 標頭。
      - **'public'** 表示回應可被任何人快取。
      - **'max_age=2628000'** 設定快取的最大存活時間（以秒為單位）。
      - **'etag'** 啟用 ETag 標頭，用於檢查資源是否已更改。
      - 此中介層可用於一組路由，方便統一管理快取策略。
---

## 3. **Cookie 操作**

- *附加 Cookie*：
  ```php
  return response('Hello World')->cookie('name', 'value', $minutes);
  // 在回應中附加名為 'name' 的 Cookie，值為 'value'，存活時間為 $minutes 分鐘。

  // 進階：
  return response('Hello World')->cookie('name', 'value', $minutes, $path, $domain, $secure, $httpOnly);
  ```
  - 附加 Cookie 並指定其他屬性：
    - `$path`：Cookie 的有效路徑（例如 '/'）。
    - `$domain`：Cookie 的有效域名（例如 'example.com'）。
    - `$secure`：是否僅在 HTTPS 下傳送 Cookie。
    -` $httpOnly`：是否僅允許 HTTP 存取 Cookie（禁止 JavaScript 存取）。

- *Queue Cookie*：
  - Queue Cookie 是 **將 Cookie 排入佇列**，並在下一次回應中附加到 HTTP 回應中。
  - 它的作用是 **延遲 Cookie 的設置**，直到回應被送出時才執行。
  - 這對於需要在多個地方設置 Cookie，但希望統一在回應中附加時非常有用。
  ```php
  use Illuminate\Support\Facades\Cookie;
  Cookie::queue('name', 'value', $minutes);
  // 將名為 'name' 的 Cookie 排入佇列，值為 'value'，存活時間為 $minutes 分鐘。
  // Cookie 會在下一次回應中附加。
  ```
- *產生 Cookie 實例*：
  ```php
  $cookie = cookie('name', 'value', $minutes);
  // 使用 cookie() 函數產生名為 'name' 的 Cookie 實例，值為 'value'，存活時間為 $minutes 分鐘。
  
  return response('Hello World')->cookie($cookie);
  // 將產生的 Cookie 實例附加到回應中，回應內容為 'Hello World'。
  ```
- *移除 Cookie*：
  ```php
  return response('Hello World')->withoutCookie('name');
  // 從回應中移除名為 'name' 的 Cookie。

  Cookie::expire('name');
  // 立即使名為 'name' 的 Cookie 過期。
  ```
- *Cookie 加密*：`預設全加密`，若要排除：
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->encryptCookies(except: ['cookie_name']);
      // 排除名為 'cookie_name' 的 Cookie，不進行加密。
  })
  ```

---

## 4. **重導（Redirect）**

- *基本重導*：
  ```php
  return redirect('/home/dashboard'); 
  // 重導到指定的路徑 '/home/dashboard'。

  return back()->withInput(); 
  // 重導回上一頁，並帶回輸入資料（例如表單資料）。
  ```
- *命名路由重導*：
  ```php
  return redirect()->route('login'); 
  // 重導到命名路由 'login'。
  
  return redirect()->route('profile', ['id' => 1]); 
  // 重導到命名路由 'profile'，並傳遞參數 'id'。
  
  return redirect()->route('profile', [$user]); 
  // 可直接傳 Model，Laravel 會自動解析 Model 的 route key。
  ```
- *自訂 route key*：Model 可覆寫 `getRouteKey()`
  ```php
  public function getRouteKey(): mixed {
      return $this->slug; 
  }
  ```
    - Laravel **預設使用 Model 的主鍵**（通常是 'id'）作為 URL 的識別。
      - 例如：當你在路由中使用 Model 綁定時，URL 可能是 `'/articles/1'`，其中 '1' 是主鍵。

    - 如果你希望使用其他欄位（例如 'slug'）**作為 URL 的識別**，可以覆寫 `getRouteKey()` 方法。
    - 'slug' 是一個常見的欄位，用於生成更具可讀性的 URL，例如 `'my-article-title'`。

    - 當覆寫 `getRouteKey()` 後：
      - Laravel 會在生成 URL 時使用 'slug' 作為識別，而不是主鍵。
      - URL 可能變成 `'/articles/my-article-title'` 而不是 '/articles/1'。

    - 同時，*路由解析時，Laravel 會根據 'slug' 查找 Model，而不是主鍵*。
      - 例如：當訪問 '/articles/my-article-title' 時，Laravel 會自動查找 slug 為 'my-article-title' 的文章，而不是 id 為 1 的文章。

- *Controller action 重導*：
  ```php
  use App\Http\Controllers\UserController;
  
  return redirect()->action([UserController::class, 'index']); 
  // 重導到指定 Controller 的 index 方法。
  
  return redirect()->action([UserController::class, 'profile'], ['id' => 1]); 
  // 重導到指定 Controller 的 profile 方法，並傳遞參數 'id'。
  ```
- *外部網址重導*：
  ```php
  return redirect()->away('https://www.google.com'); 
  // 重導到外部網址，例如 'https://www.google.com'。
  ```
- *重導並閃存資料*：

  - 使用 `with()` 方法將資料閃存到 session 中。

  ```php
  return redirect('/dashboard')->with('status', 'Profile updated!'); 
  // 'status' 是鍵，'Profile updated!' 是值。
  // 重導到指定路徑 '/dashboard'，並將資料暫時存入 session。

  // Blade 顯示：
  @if (session('status')) 
      <div class="alert alert-success">{{ session('status') }}</div> 
  @endif
  // 使用 `session()` 函數存取閃存的資料。
  // 如果 session 中存在 'status'，顯示成功訊息。

  ```
  - **閃存的概念**：
    - 閃存是指 *將資料暫時存入 session 中，僅在下一次 HTTP 請求中有效*。
    - 它的作用是 *讓資料在重導後仍然可以被存取*，例如用於顯示成功訊息或錯誤提示。
    - 閃存資料的特性是短暫的，*僅在下一次請求中有效*，之後會自動清除。

- *重導並帶回輸入*：
  ```php
  return back()->withInput(); 
  // 重導回上一頁，並帶回輸入資料（例如表單資料）。
  ```

---

## 5. **進階 Response 型態**

- *View 回應*：
  ```php
  return response()->view('hello', $data, 200)->header('Content-Type', $type);
  // 回應一個視圖（Blade 模板），例如 'hello'。
  // $data 是傳遞給視圖的資料，200 是 HTTP 狀態碼（表示成功）。
  // 使用 header() 方法設定回應的標頭，例如 Content-Type。
  ```
- *JSON 回應*：
  ```php
  return response()->json(['name' => 'Abigail', 'state' => 'CA']);
  // 回應 JSON 格式的資料，例如 {'name': 'Abigail', 'state': 'CA'}。
  
  return response()->json([...])->withCallback($request->input('callback'));
  // 回應 JSON 格式的資料，允許跨域請求。
  // 使用 withCallback() 方法，將回應包裹在指定的 JavaScript 函數中。
  ```
- *檔案下載*：
  - 使用者`會看到下載提示`，檔案會被下載到本地端。

  ```php
  return response()->download($pathToFile);
  // 提供檔案下載，$pathToFile 是檔案的路徑。

  return response()->download($pathToFile, $name, $headers);
  // 提供檔案下載，並指定檔案名稱 $name 和自訂標頭 $headers。
  // $name 是下載後的檔案名稱，例如 'example.pdf'。
  // $headers 是自訂的 HTTP 標頭，例如 Content-Type 或 Cache-Control。
  ```
- *檔案直接顯示*：
  - 使用者`不會看到下載提示`，檔案會`直接在瀏覽器中打開（例如 PDF 或圖片）`。

  ```php
  return response()->file($pathToFile);
  // 在瀏覽器中直接顯示檔案內容，$pathToFile 是檔案的路徑。

  return response()->file($pathToFile, $headers);
  // 在瀏覽器中直接顯示檔案內容，並指定自訂標頭 $headers。
  // $headers 是自訂的 HTTP 標頭，例如 Content-Type 或 Cache-Control。
  ```
- *串流回應*：
  ```php
  Route::get('/stream', function () {
      return response()->stream(function (): void {
          foreach (['developer', 'admin'] as $string) {
              echo $string;
              ob_flush(); 
              flush(); 
              sleep(2);
          }
      });
  });
  ```
  - 回應一個串流，允許逐步輸出資料。
  - 使用 `stream()` 方法，回應的內容會 **逐步輸出** ，例如每隔 2 秒輸出一段文字。
  - `ob_flush()` 和 `flush()` 用於 **清空緩衝區，確保資料立即傳送到客戶端** 。

- *Generator 串流*：
  ```php
  Route::post('/chat', function () {
      return response()->stream(function (): Generator {
          $stream = OpenAI::client()->chat()->createStreamed(...);
          foreach ($stream as $response) {
              yield $response->choices[0];
          }
      });
  });
  ```
  - 使用 Generator 串流回應，**允許逐步輸出資料**。
  - `yield` 用於 **逐步傳送資料到客戶端** ，適合處理大型或即時資料。
  - 例如：OpenAI 的聊天串流回應，每次回傳一部分資料。

- *streamJson*：`大量資料分段傳送`
  ```php
  use App\Models\User;
  Route::get('/users.json', function () {
      return response()->streamJson([
          'users' => User::cursor(),
      ]);
  });
  ```
  - 使用 `streamJson` 方法分段傳送 JSON 格式的資料。
  - `User::cursor()` 用於 **逐步查詢資料庫**，避免一次性載入大量資料。
  - 適合處理大型資料集，減少記憶體使用。

- *eventStream（SSE）*：
  - 使用 `Server-Sent Events (SSE)` 進行`事件串流`，**允許伺服器向客戶端推送即時更新**。
  - `yield` 用於 **逐步傳送事件資料**，例如聊天回應。
  ```php

  use Illuminate\Http\StreamedEvent;
  Route::get('/chat', function () {
      return response()->eventStream(function () {
          $stream = OpenAI::client()->chat()->createStreamed(...);
          foreach ($stream as $response) {
              yield $response->choices[0];
          }
      });
  });
  // 自訂事件名稱
  yield new StreamedEvent(event: 'update', data: $response->choices[0]);
  // 使用 StreamedEvent 自訂事件名稱和資料。
  
  // 自訂結束事件
  return response()->eventStream(function () {
      // ...
  }, endStreamWith: new StreamedEvent(event: 'update', data: '</stream>'));
  // 使用 endStreamWith 方法設定串流結束事件。
  ```
- *streamDownload*：`直接串流下載`
  ```php
  use App\Services\GitHub;
  return response()->streamDownload(function () {
      echo GitHub::api('repo')
          ->contents()
          ->readme('laravel', 'laravel')['contents'];
  }, 'laravel-readme.md');
  ```
  - 使用 `streamDownload` 方法進行檔案串流下載。
  - 適合 **處理即時生成的檔案** ，例如從 GitHub API 取得 README 檔案並直接下載。
  - **第二個參數** `'laravel-readme.md' 是*下載檔案的名稱*。

---
## **串流的概念**

- *串流（Streaming）*：
  串流是一種 *資料傳輸方式*，允許伺服器 *逐步傳送* 資料到客戶端，而不是一次性傳送所有資料。這種方式適合處理`大型資料集`或`即時更新`的應用，例如：
  - 即時聊天
  - 實時通知
  - 大型資料集的分段載入

- *串流的特性*：
  1. **逐步傳送**：資料會`分段傳送到客戶端，客戶端可以在接收到部分資料後立即處理，而不需要等待所有資料完成。
  2. **降低記憶體使用**：伺服器`不需要一次性載入`所有資料，適合處理大型資料集。
  3. **即時性**：適合需要`即時更新`的場景，例如聊天或即時數據。

- *Laravel 串流的應用*：
  Laravel 提供多種串流回應方式，例如：
  - **stream**：逐步輸出一般資料。
  - **streamJson**：分段傳送 JSON 格式的資料。
  - **eventStream（SSE）**：使用 `Server-Sent Events` 進行事件串流，適合即時推送更新。
  - **streamDownload**：即時生成檔案並提供下載。

- *前端串流消費*：
  前端可以使用工具（如 `@laravel/stream-react` 或 `@laravel/stream-vue`）來消費串流回應，並處理串流資料、事件、錯誤等。

## 6. **前端串流消費（React/Vue）**

- *@laravel/stream-react*、
  *@laravel/stream-vue* 套件可消費 `stream/eventStream/streamJson`
  - 這些套件提供前端工具，用於消費 Laravel 的串流回應（如 stream、eventStream、streamJson）。
  - 適合用於即時更新的應用，例如聊天、通知或大型資料集的分段載入。

- *useStream*
  *useJsonStream*
  *useEventStream* 提供串流資料、事件、錯誤、取消、id 等 hook
  - 這些 `hook` 提供簡單的 API，用於處理串流資料、事件、錯誤、取消操作，以及管理串流的唯一 ID。

- *需 CSRF token*，可用 `meta tag` 注入
  - 串流請求需要 CSRF token 進行驗證，可在 HTML 的 meta 標籤中注入 CSRF token。

- *可自訂事件名稱、結束訊號、資料 glue*
  - 支援自訂事件名稱（例如 'update'）、結束訊號（例如 '</stream>'），以及資料的拼接方式（glue）。

- *useStream/useEventStream/useJsonStream 範例*：
  ```js
  import { useStream, useEventStream, useJsonStream } from "@laravel/stream-react";

  // useStream：用於處理一般串流
  const { data, isFetching, isStreaming, send } = useStream("chat");

  // useEventStream：用於處理事件串流（SSE）
  const { message } = useEventStream("/chat");

  // useJsonStream：用於處理 JSON 格式的串流
  const { data: jsonData, send: sendJson } = useJsonStream("users");
  ```
- *取消串流*：
  ```js
  const { data, cancel } = useStream("chat");
  <button onClick={cancel}>Cancel</button>
  // 使用 cancel 方法取消正在進行的串流。
  ```
- *多組件共用 stream id*：
  ```js
  const { data, id } = useStream("chat");
  // 第一個組件初始化串流並取得唯一 ID。
  
  const { isFetching, isStreaming } = useStream("chat", { id });
  // 第二個組件使用相同的 stream ID，共享串流狀態。
  ```
- *EventSource 手動消費 SSE*：
  ```js
  const source = new EventSource('/chat');
  source.addEventListener('update', (event) => {
      if (event.data === '</stream>') {
          source.close();
          return;
      }
      console.log(event.data);
  });
  ```
  - 使用原生 `EventSource API` 手動消費 `Server-Sent Events (SSE)`。
  - `update` 是 *事件名稱，* `event.data` 是 *事件的資料* 。
  - 當接收到結束訊號（例如 '</stream>'）時，關閉串流。
---

## 7. **Response Macro（自訂回應）**

- *定義 macro*：
  ```php
  namespace App\Providers;
  use Illuminate\Support\Facades\Response;
  use Illuminate\Support\ServiceProvider;

  class AppServiceProvider extends ServiceProvider {
      public function boot(): void {
          // 定義一個名為 'caps' 的 Response Macro。
          Response::macro('caps', function (string $value) {
              // Macro 的功能是將輸入的字串轉換為大寫，並回應該字串。
              return Response::make(strtoupper($value));
          });
      }
  }

  // 使用
  return response()->caps('foo');
  // 呼叫自訂的 'caps' Macro，將 'foo' 轉換為大寫並回應 'FOO'。
  ```

---
