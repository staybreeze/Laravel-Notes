# *Laravel Blade Templates 樣板 筆記*

---

## 1. **簡介與核心概念**

- *Blade 是 Laravel 內建的簡潔強大樣板引擎*，支援 PHP 語法、零額外效能負擔。
- 檔案副檔名為 `.blade.php`，通常放在 `resources/views`。
- *生活化比喻*： Blade 就像「`智慧型 HTML 編輯器`」，讓你寫 view 更快、更安全。

---

## 2. **Blade 基本用法與資料顯示**

- *回傳 Blade view*

  ```php
  return view('greeting', ['name' => 'Finn']);
  ```

---

- *顯示資料*

  ```html
  Hello, {{ $name }}.
  ```

---

- *自動防止 XSS*：`{{ }}` 會自動 `escape`

  - 廣泛用於描述在程式設計或網頁開發中`對特殊字元進行處理的行為`。

  - __escape 的作用__：

    - *HTML 編碼*：將特殊的 HTML 字元（如 <, >, &, " 等）轉換為它們的`HTML 實體`，__這樣瀏覽器就不會將它們解釋為 HTML 或 JavaScript，而是作為*純文字顯示*__。
    
      - **例如**：
        - *原始輸入*：<script>alert('Hacked!');</script>
        - *編碼後*：&lt;script&gt;alert(&#039;Hacked!&#039;);&lt;/script&gt;
        - *瀏覽器顯示*：<script>alert('Hacked!');</script>（純文字）
        
- *可嵌入任意 PHP*

  ```html
  The current UNIX timestamp is {{ time() }}.
  ```

---

- *不轉義顯示*（小心 XSS！）

  ```html
  Hello, {!! $name !!}.
  ```

---

- *關閉 HTML entity double encoding*

  ```php
  // 在 App\Providers\AppServiceProvider 的 boot 方法中設定
  Blade::withoutDoubleEncoding();
  // 此設定會關閉 Blade 模板引擎對 HTML 實體的「雙重編碼」。
  // 也就是說，如果資料已經被編碼過（例如 `&lt;`），Blade 不會再次對其進行編碼。
  ```

---

- `{{ }}`：Blade __會__ 自動解析，輸出 PHP 變數內容（並 `escape`）。
- `@{{ }}`：Blade _不會_ 解析，原樣輸出 `{{ }}`，通常給 __JS 框架__（如 __Vue__）用。
- `{{!!!!}}`：Blade _不會_ 解析，因為不是標準 `Blade` 語法，會原樣輸出。

*總結*：

`@{{ }}` 和 `{{!!!!}}` _都不會_ 被 Blade 解析，會原樣輸出到 HTML。
差別在於 `@{{ }}` 是 __專門給 JS 框架用__，`{{!!!!}}` __只是一般字串__，Blade 不認得就不處理。

*補充*：

`HTML` 本身不會解析這些內容，只會原樣顯示在網頁上。
只有 _JS_ 框架（如 __Vue__）才會進一步處理 `{{ }}` 語法。

<!-- 
@{{ }} 會原樣輸出成 {{ }}，
這時如果有 JS 框架（如 Vue），就會被 JS 框架解析。
{{ }} 如果沒加 @，Blade 會先解析（輸出 PHP 變數），
如果 Blade 沒解析，JS 框架才會處理。
其他非標準語法（如 {{!!!!}}）都只會原樣顯示，不會被 JS 框架或 Blade 處理。
-->

---

## 3. **Blade 與 JS 框架、@verbatim**

- *避免 JS curly braces 被 Blade 處理*

  ```html
  Hello, @{{ name }}

  <!-- 如果你直接寫 {{ name }}，Blade 會把它當作 PHP 變數輸出 
       Blade 會嘗試輸出 $name 的值。
  
       如果你寫 @{{ name }}，Blade 會保留原本的 JS curly braces，
       這樣 JS 前端框架（如 Vue）才能正確解析 {{ name }}，不會被 Blade 取代成 PHP 變數。-->
  ```

<!-- 
PHP：後端程式語言，負責資料處理與邏輯運算。
Blade：Laravel 的模板引擎，負責把 PHP 變數和邏輯嵌入到 HTML，並在伺服器端解析。
HTML/JS：前端頁面，瀏覽器解析並顯示，JS 框架（如 Vue）會再處理自己的語法（如 {{ name }}）。

流程：
Blade 先在伺服器端解析模板，把 PHP 變數和指令轉成 HTML。
跳脫（如 @{{ name }} 或 @@if()) 可以讓 Blade 保留原始字元，不解析成 PHP。
最後產生的 HTML 交給瀏覽器，JS 框架再解析自己的語法。
這樣可以讓 PHP、Blade、JS 各自處理自己的邏輯，互不干擾。 
-->


---

- *`@` 符號可跳脫 Blade 指令*

  ```html
  @@if() 
  <!-- 會輸出 @if()
       這樣可以讓 Blade 保留原始 @ 字元，不會當成 Blade 指令處理。
       如果沒有跳脫，Blade 會把 @if() 當成條件判斷指令，
       會嘗試解析並執行 PHP 的 if 判斷，
       而不是原樣輸出 @if() 到 HTML。 -->
  ```
<!-- 
保留原始 @if() 的用途通常是：

你要在前端（例如教學、文件、JS 框架）顯示 Blade 語法範例，而不是執行判斷。
或是 JS 框架（如 Vue）有自己的 @if() 語法，你不希望被 Blade 解析。 

-->
---

- *`@verbatim` 區塊*：`大範圍跳脫 Blade`

  ```html
  @verbatim
      <div>{{ name }}</div>
  @endverbatim
  ```

---

## 4. **JSON 輸出與 Js::from**

- *安全輸出 JSON 給 JS*

  ```html
  <script>
      // 使用 Js::from() 將 PHP 陣列安全地轉成 JavaScript 物件
      var app = {{ Js::from($array) }};
      // 這樣可以把 PHP 的 $array 內容直接嵌入到前端 JS，
      // 避免手動序列化或 XSS 風險。
  </script>
  ```

- *註解*： 避免直接用 `json_encode`，
          `Js::from` 會自動 `escape`

---

## 5. **Blade 指令（Directives）**

### *條件判斷*

- `@if` / `@elseif` / `@else` / `@endif`
- `@unless` / `@endunless`
- `@isset` / `@endisset`
- `@empty` / `@endempty`

- **認證判斷**：`@auth`、`@guest`（可指定 guard）

- **環境判斷**：`@production`、`@env('staging')`、`@env(['staging', 'production'])`

- **區塊判斷**：`@hasSection('name')`、`@sectionMissing('name')`

- **Session/Context 判斷**：`@session('key')`、`@context('key')`

---

### *Switch 判斷*

- `@switch` / `@case` / `@break` / `@default` / `@endswitch`

---

### *迴圈*

- `@for` / `@endfor`
- `@foreach` / `@endforeach`
- `@forelse` / `@empty` / `@endforelse`
- `@while` / `@endwhile`

- **跳過/中斷**：`@continue`、`@break`（可加條件）

- **$loop 變數**：

  - `$loop->index`：目前迴圈的 __索引__（從 0 開始）
  - `$loop->iteration`：目前 __迴圈的次數__（從 1 開始）
  - `$loop->remaining`：__剩下__ 幾次迴圈
  - `$loop->count`：__總共__ 要跑幾次迴圈

  - `$loop->first`：__是否__ 為第一次迴圈（布林值）
  - `$loop->last`：__是否__ 為最後一次迴圈（布林值）
  - `$loop->even`：目前迴圈 __是否__ 為偶數次（布林值）
  - `$loop->odd`：目前迴圈 __是否__ 為奇數次（布林值）

  - `$loop->depth`：巢狀迴圈的 __深度__
  - `$loop->parent`：__上一層__ 迴圈的 `$loop` 物件
---

### *條件 class/style 屬性*

- `@class([...])`、`@style([...])`

  ```html
  <span @class([
      'p-4',
      'font-bold' => $isActive,
      'text-gray-500' => ! $isActive,
      'bg-red' => $hasError,
  ])></span>
  <span @style([
      'background-color: red',
      'font-weight: bold' => $isActive,
  ])></span>
  ```

---

### *表單輔助屬性*

- `@checked`、`@selected`、`@disabled`、`@readonly`、`@required`

  ```html
  <input type="checkbox" @checked(old('active', $user->active)) >

  <option @selected(old('version') == $version)>{{ $version }}</option>

  <button @disabled($errors->isNotEmpty())>Submit</button>

  <input @readonly($user->isNotAdmin()) >
  <input @required($user->isAdmin()) >
  ```

---

## 6. **子視圖與元件**

- *@include*：`引入子` view 時，會自動`繼承父` view 的所有變數，

  ```php
  @include('shared.errors') // 引入 shared/errors.blade.php 部分視圖
  @include('view.name', ['status' => 'complete']) // 引入 view/name.blade.php，並傳入 status 參數

  ```

---

- *@includeIf / @includeWhen / @includeUnless / @includeFirst*

  ```php
  @includeIf('view.name', ['status' => 'complete']) 
  // 如果 view.name 存在才引入

  @includeWhen($boolean, 'view.name', ['status' => 'complete']) 
  // 當 $boolean 為 true 時才引入 view.name

  @includeUnless($boolean, 'view.name', ['status' => 'complete']) 
  // 當 $boolean 為 false 時才引入 view.name

  @includeFirst(['custom.admin', 'admin'], ['status' => 'complete']) 
  // 引入第一個存在的 view（custom.admin 或 admin）
  ```

---

- *@each*：`集合渲染`

  ```php
  @each('view.name', $jobs, 'job', 'view.empty')
  // 對 $jobs 集合的每個元素，使用 view.name 部分視圖，並傳入 job 變數
  // 如果 $jobs 為空，則引入 view.empty 視圖
  ```

---

- *@once / @pushOnce / @prependOnce*：`只渲染一次`

  ```php
  @once
    @push('scripts')
        <script>...</script>
    @endpush
  @endonce
  // 只會執行一次，避免重複插入相同內容
  // @push 是 Blade 的指令，
  // 意思是「將內容加入指定區塊的最後面」，
  // 可以多次插入，內容會依序累加。
  // 常用於 script、style 等區塊。

  @pushOnce('scripts')
      <script>...</script>
  @endPushOnce
  // 只會 push 一次到 'scripts' 區塊，避免重複

  @prependOnce('scripts')
    <script>...</script>
  @endPrependOnce
  // 只會 prepend 一次到 'scripts' 區塊，避免重複插入相同內容
  // @prependOnce 是 Blade 的指令，
  // 意思是「只在指定區塊最前面插入一次內容」，
  // 避免同樣的內容被重複插入。
  // 常用於 script、style 等區塊。
  ```

---

## 7. **原生 PHP 與 @use**

- *@php 區塊*：

  ```php
  @php $counter = 1; @endphp
  ```

---

- *@use*：引入 `class/function/const`，可 `alias、群組`

  ```php
  @use('App\Models\Flight') // 引入 Flight 類別
  @use('App\Models\Flight', 'FlightModel') // 引入並命名為 FlightModel
  @use('App\Models\{Flight, Airport}') // 一次引入多個類別

  @use(function App\Helpers\format_currency) // 引入函式
  @use(const App\Constants\MAX_ATTEMPTS) // 引入常數
  
  @use(function App\Helpers\{format_currency, format_date}) // 一次引入多個函式
  @use(const App\Constants\{MAX_ATTEMPTS, DEFAULT_TIMEOUT}) // 一次引入多個常數
  ```

---

## 8. **Blade 註解**

- *Blade 註解不會出現在 HTML*

  ```html
  {{-- 這是 Blade 註解 --}}
  ```

---