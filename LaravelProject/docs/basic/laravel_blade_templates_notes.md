# *Laravel Blade Templates 樣板*

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
- *顯示資料*
  ```html
  Hello, {{ $name }}.
  ```
- *自動防止 XSS*：`{{ }}` 會自動 `escape`
  - 廣泛用於描述在程式設計或網頁開發中`對特殊字元進行處理的行為`。
  - **escape 的作用**：
    - *HTML 編碼*：將特殊的 HTML 字元（如 <, >, &, " 等）轉換為它們的`HTML 實體`，這樣瀏覽器就不會將它們解釋為 HTML 或 JavaScript，而是作為*純文字顯示*。
      - **例如**：
        - *原始輸入*：<script>alert('Hacked!');</script>
        - *編碼後*：&lt;script&gt;alert(&#039;Hacked!&#039;);&lt;/script&gt;
        - *瀏覽器顯示*：<script>alert('Hacked!');</script>（純文字）
        
- *可嵌入任意 PHP*
  ```html
  The current UNIX timestamp is {{ time() }}.
  ```
- *不轉義顯示*（小心 XSS！）
  ```html
  Hello, {!! $name !!}.
  ```
- *關閉 HTML entity double encoding*
  ```php
  // 在 App\Providers\AppServiceProvider 的 boot 方法中設定
  Blade::withoutDoubleEncoding();
  // 此設定會關閉 Blade 模板引擎對 HTML 實體的「雙重編碼」。
  // 也就是說，如果資料已經被編碼過（例如 `&lt;`），Blade 不會再次對其進行編碼。
  ```

---

## 3. **Blade 與 JS 框架、@verbatim**

- *避免 JS curly braces 被 Blade 處理*
  ```html
  Hello, @{{ name }}
  ```
- *`@` 符號可跳脫 Blade 指令*
  ```html
  @@if() 
  <!-- 會輸出 @if() -->
  ```
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
      var app = {{ Js::from($array) }};
  </script>
  ```
- *註解*： 避免直接用 `json_encode`，`Js::from` 會自動 `escape`

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

### *Switch 判斷*
- `@switch` / `@case` / `@break` / `@default` / `@endswitch`

### *迴圈*
- `@for` / `@endfor`
- `@foreach` / `@endforeach`
- `@forelse` / `@empty` / `@endforelse`
- `@while` / `@endwhile`
- **跳過/中斷**：`@continue`、`@break`（可加條件）
- **$loop 變數**：
  - `$loop->index`、`iteration`、`remaining`、`count`、`first`、`last`、`even`、`odd`、`depth`、`parent`

### *條件 class/style 屬性*
- `@class([...])`、`@style([...])`
- 範例：
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

### *表單輔助屬性*
- `@checked`、`@selected`、`@disabled`、`@readonly`、`@required`
- 範例：
  ```html
  <input type="checkbox" @checked(old('active', $user->active)) >
  <option @selected(old('version') == $version)>{{ $version }}</option>
  <button @disabled($errors->isNotEmpty())>Submit</button>
  <input @readonly($user->isNotAdmin()) >
  <input @required($user->isAdmin()) >
  ```

---

## 6. **子視圖與元件**

- *@include*：引入子 view，繼承父 view 變數
  ```html
  @include('shared.errors')
  @include('view.name', ['status' => 'complete'])
  ```
- *@includeIf / @includeWhen / @includeUnless / @includeFirst*
  ```html
  @includeIf('view.name', ['status' => 'complete'])
  @includeWhen($boolean, 'view.name', ['status' => 'complete'])
  @includeUnless($boolean, 'view.name', ['status' => 'complete'])
  @includeFirst(['custom.admin', 'admin'], ['status' => 'complete'])
  ```
- *@each*：`集合渲染`
  ```html
  @each('view.name', $jobs, 'job', 'view.empty')
  ```
- *@once / @pushOnce / @prependOnce*：`只渲染一次`
  ```html
  @once
      @push('scripts')
          <script>...</script>
      @endpush
  @endonce
  @pushOnce('scripts') ... @endPushOnce
  ```

---

## 7. **原生 PHP 與 @use**

- *@php 區塊*：
  ```html
  @php $counter = 1; @endphp
  ```
- *@use*：引入 c`lass/function/const`，可 `alias、群組`
  ```html
  @use('App\Models\Flight')
  @use('App\Models\Flight', 'FlightModel')
  @use('App\Models\{Flight, Airport}')
  @use(function App\Helpers\format_currency)
  @use(const App\Constants\MAX_ATTEMPTS)
  @use(function App\Helpers\{format_currency, format_date})
  @use(const App\Constants\{MAX_ATTEMPTS, DEFAULT_TIMEOUT})
  ```

---

## 8. **Blade 註解**

- *Blade 註解不會出現在 HTML*
  ```html
  {{-- 這是 Blade 註解 --}}
  ```

---