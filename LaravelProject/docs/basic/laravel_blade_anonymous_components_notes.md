# *Laravel Blade Anonymous Components 匿名元件*

---

## 1. **簡介與核心概念**

- *Anonymous Components（匿名元件）* 只需一個 Blade 檔，`無需 class`，適合簡單、重複性高的 UI 區塊。
- *生活化比喻*： 匿名元件就像「現成積木」，拿來即用、免組裝。

---

## 2. **建立與使用匿名元件**

- *建立*：直接在 `resources/views/components` 下建立 Blade 檔
 ```bash
  # 產生 alert 元件
  touch resources/views/components/alert.blade.php
  ```

- **渲染**：
  ```html
  <x-alert />
  <!-- 若在子目錄：<x-inputs.button /> -->
  ```

- **Index 元件**：
  - 若有 `多個相關檔案`，可在目錄下建立同名檔作為 `root` 元件。
  - 範例檔案結構：

    - /resources/views/components/accordion/accordion.blade.php
      <div class="accordion">
      {{ $slot }}
      </div>

    - /resources/views/components/accordion/item.blade.php
      <div class="accordion-item">
      {{ $slot }}
      </div>

  - 使用方式：
    ```html
    <x-accordion>
        <x-accordion.item>
            Item Content
        </x-accordion.item>
        <x-accordion.item>
            Another Item Content
        </x-accordion.item>
    </x-accordion>
    ```
  - **解釋**：
    - `accordion.blade.php` 是*根元件*，負責包裹所有子元件。
    - `item.blade.php` 是*子元件*，負責*渲染*每個項目內容。

---

## 3. **@props 與資料屬性**

- *`@props`*：定義哪些`屬性`會變成`變數`，其餘進 `$attributes`
  ```html
  @props(['type' => 'info', 'message'])
  <!-- 
      1. `@props` 用於定義元件的屬性。
      2. `['type' => 'info', 'message']`：
        - `type`：屬性名稱，預設值為 `'info'`。
        - `message`：屬性名稱，沒有預設值，必須傳入。
      3. 任何未定義在 `@props` 中的屬性，會自動進入 `$attributes`。
  -->

  <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
      <!-- 
          1. `$attributes->merge()`：
            - 用於合併屬性到 HTML 標籤中。
            - 這裡將 `class` 屬性合併為 `'alert alert-'.$type`。
            - 如果使用者傳入其他屬性（例如 `class="custom-class"`），會與預設值合併。
          2. `'alert alert-'.$type`：
            - 根據 `type` 的值動態生成類別名稱，例如 `'alert alert-info'`。
      -->
      {{ $message }}
      <!-- 
          1. `{{ $message }}`：
            - 渲染元件的 `message` 屬性內容。
            - 例如，若 `message="This is an alert"`，則輸出該訊息。
      -->
  </div>
  ```

- *使用*：
  ```html
  <x-alert type="error" :message="$message" class="mb-4" />
  ```

- *預設值*：@props(['type' => 'info'])

---

## 4. **@aware 父元件資料注入**

- *`@aware`*：讓**子元件可取得父元件屬性**（必須由父元件 HTML 屬性傳入）
  ```html
  <!-- /components/menu/index.blade.php -->
  @props(['color' => 'gray'])
  <!-- 
      1. `@props(['color' => 'gray'])`：
        - 定義父元件的屬性 `color`，預設值為 `'gray'`。
      2. `$attributes->merge(['class' => 'bg-'.$color.'-200'])`：
        - 合併屬性，動態生成 `class`，例如 `bg-gray-200`。
  -->
  <ul {{ $attributes->merge(['class' => 'bg-'.$color.'-200']) }}>
      {{ $slot }}
  </ul>

  <!-- /components/menu/item.blade.php -->
  @aware(['color' => 'gray'])
  <!-- 
      1. `@aware(['color' => 'gray'])`：
        - 讓子元件可以取得父元件的 `color` 屬性。
        - 必須由父元件的 HTML 屬性傳入，不能取得父元件的 `@props` 預設值。
      2. `$attributes->merge(['class' => 'text-'.$color.'-800'])`：
        - 合併屬性，動態生成 `class`，例如 `text-gray-800`。
  -->
  <li {{ $attributes->merge(['class' => 'text-'.$color.'-800']) }}>
      {{ $slot }}
  </li>
  ```

  ```html
  <!-- 使用範例 -->
  <x-menu color="blue">
      <x-menu.item>Item 1</x-menu.item>
      <x-menu.item>Item 2</x-menu.item>
  </x-menu>
  ```

  ```html
  <!-- 輸出結果 -->
  <ul class="bg-blue-200">
      <li class="text-blue-800">Item 1</li>
      <li class="text-blue-800">Item 2</li>
  </ul>
  ```

- *註解*
  - **父元件的屬性傳遞**：
    - `父元件`的屬性（如 color="blue"）會傳遞給子元件，子元件可以使用 `@aware` 來取得該屬性。

  - **限制**：
    - `子元件`只能取得`父元件`的 HTML 屬性。
    - 父元件的 `@props` *預設值*（如 color => 'gray'）無法被 `@aware` 取得。

  - **動態生成樣式**：
    - 父元件的屬性值（如 color="blue"）會影響子元件的樣式，實現`樣式的動態生成`。

---

## 5. **自訂匿名元件路徑與命名空間**

- *註冊其他匿名元件路徑*
  ```php
  // 註冊匿名元件路徑
  Blade::anonymousComponentPath(__DIR__.'/../components');
  # <x-panel />
  /*
      1. Blade::anonymousComponentPath() 用於註冊匿名元件所在的目錄。
      2. __DIR__.'/../components'：指定匿名元件的目錄路徑。
      3. <x-panel />：匿名元件的名稱，對應於該目錄下的 panel.blade.php。
      4. 使用此方法後，Blade 模板可以直接使用 <x-panel> 來渲染該元件。
  */

  // 註冊匿名元件路徑並設定命名空間
  Blade::anonymousComponentPath(__DIR__.'/../components', 'dashboard');
  # <x-dashboard::panel />
  /*
      1. Blade::anonymousComponentPath() 第二個參數用於設定命名空間前綴。
      2. __DIR__.'/../components'：指定匿名元件的目錄路徑。
      3. 'dashboard'：設定匿名元件的命名空間前綴。
      4. <x-dashboard::panel />：匿名元件的名稱，對應於該目錄下的 panel.blade.php。
      5. 使用此方法後，Blade 模板可以使用 <x-dashboard::panel> 來渲染該元件。
  */
  ```

---

## 6. **Layout 與繼承**

- *用元件作 layout*
  ```html
  <!-- /components/layout.blade.php -->
  <html>
      <head>
          <title>{{ $title ?? 'Todo Manager' }}</title>
          <!-- 
              1. `$title` 是元件的屬性，透過 `<x-slot:title>` 傳入。
              2. 如果未傳入 `$title`，則使用預設值 `'Todo Manager'`。
          -->
      </head>
      <body>
          <h1>Todos</h1>
          <hr/>
          {{ $slot }}
          <!-- 
              1. `$slot` 是元件的主要內容區域。
              2. 子元件或內容會插入到這裡。
          -->
      </body>
  </html>
  <!-- --- -->
  <!-- /tasks.blade.php -->
  <x-layout>
      <x-slot:title>Custom Title</x-slot:title>
      <!-- 
          1. `<x-slot:title>` 用於傳遞 `title` 屬性到元件。
          2. 此處的值為 `'Custom Title'`，會覆蓋元件的預設值 `'Todo Manager'`。
      -->
      @foreach ($tasks as $task)
          <div>{{ $task }}</div>
          <!-- 
              1. 使用 `@foreach` 迴圈渲染 `$tasks`。
              2. 每個 `$task` 的內容會顯示在 `<div>` 中。
          -->
      @endforeach
  </x-layout>
  ```

- *傳統繼承*
  ```html
  @extends('layouts.app')
  <!-- 
      1. `@extends` 用於指定要繼承的 Blade 模板。
      2. `'layouts.app'` 是要繼承的模板檔案，通常位於 `resources/views/layouts/app.blade.php`。
  -->

  @section('title', 'Page Title')
  <!-- 
      1. `@section` 用於定義模板中的區塊。
      2. `'title'` 是區塊名稱，`'Page Title'` 是該區塊的內容。
  -->

  @section('sidebar')
      @@parent
      <!-- 
          1. `@@parent` 用於保留父模板中的 `sidebar` 區塊內容。
          2. 子模板的內容會附加到父模板的 `sidebar` 區塊後。
      -->
      <p>...</p>
  @endsection

  @section('content')
      ...
  @endsection
  <!-- 
      1. 定義 `content` 區塊的內容。
      2. 此內容會替換父模板中的 `@yield('content')`。
  -->
  ```

- *`@yield` 可設預設值*
  ```html
  @yield('content', 'Default content')
  <!-- 
      1. `@yield` 用於顯示指定區塊的內容。
      2. `'content'` 是區塊名稱。
      3. `'Default content'` 是預設值，當該區塊未被覆蓋時會顯示此內容。
  -->
  ```
---

## 7. **表單輔助與驗證錯誤**

- *@csrf*：產生 CSRF token 欄位
- *@method*：產生 _method 欄位（PUT/PATCH/DELETE）
  ```html
  <form method="POST" action="/update">
    @method('PUT')
    @csrf
    <!-- 
        1. `@method('PUT')` 會生成一個隱藏的 `<input>` 欄位。
        2. 該欄位告訴 Laravel 此請求的 HTTP 方法是 PUT，而不是 POST。
    -->
    <button type="submit">Update</button>
  </form>
  ```

  ```html
  <!-- 輸出結果 -->
  <form method="POST" action="/update">
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="_token" value="csrf_token_value">
    <button type="submit">Update</button>
  </form>
  ```

- *@error*：顯示欄位錯誤訊息
  ```html
  <input class="@error('email') is-invalid @enderror" />
  @error('email') 
  <div>{{ $message }}</div> 
  @enderror
  <!-- 
      1. `@error('email')` 用於檢查 `email` 欄位是否有驗證錯誤。
      2. 如果有錯誤，`$message` 會包含該欄位的錯誤訊息。
      3. `is-invalid` 是 Bootstrap 的樣式類別，用於標記錯誤的輸入框。
  -->
  @error('email', 'login') 
      <div>{{ $message }}</div>
  @enderror
  <!-- 
      1. 第二個參數 `'login'` 指定錯誤包（error bag）。
      2. 用於處理多個表單的錯誤訊息。
  -->
  ```

  ```html
  <!-- 輸出結果（有錯誤時） -->
  <input class="is-invalid" name="email" />
  <div>The email field is required.</div>
  ```

  ```html
  <!-- 輸出結果（無錯誤時） -->
  <input name="email" />
  ```
---

## 8. **Stack 與 @push/@prepend**

- *@push('scripts') ... @endpush*：將內容推送到指定的 `stack（堆疊）` ，通常用於在 layout 中的特定位置插入內容。
  ```html
  @push('scripts')
      <script src="/js/app.js"></script>
  @endpush
  <!-- 
      1. `@push('scripts')`：將內容推送到名為 `scripts` 的 stack。
      2. `<script src="/js/app.js"></script>`：推送的內容。
      3. `@endpush`：結束推送。
  -->
  ```

- *@stack('scripts')*：在 layout 中的指定位置渲染 `stack` 的內容。
  ```html
  <head>
      @stack('scripts')
  </head>
  <!-- 
      1. `@stack('scripts')`：渲染名為 `scripts` 的 stack 的所有內容。
      2. 所有使用 `@push('scripts')` 推送的內容會在這裡渲染。
  -->
  ```
  
- *@prepend('scripts') ... @endprepend*：將內容插入到 `stack` 的最前面，**優先**於其他推送的內容。
  ```html
  @prepend('scripts')
      <script src="/js/priority.js"></script>
  @endprepend
  <!-- 
      1. `@prepend('scripts')`：將內容插入到名為 `scripts` 的 stack 的最前面。
      2. `<script src="/js/priority.js"></script>`：插入的內容。
      3. `@endprepend`：結束插入。
  -->
  ```

- *@pushIf($cond, 'scripts') ... @endPushIf*：根據條件推送內容到指定 `stack`。
  ```html
  @pushIf($cond, 'scripts')
      <script src="/js/conditional.js"></script>
  @endPushIf
  <!-- 
      1. `@pushIf($cond, 'scripts')`：如果 `$cond` 為 `true`，則推送內容到名為 `scripts` 的 stack。
      2. `<script src="/js/conditional.js"></script>`：推送的內容。
      3. `@endPushIf`：結束條件推送。
  -->
  ```
  
－ *總範例*
  ```html
  <!-- Layout 文件 -->
  <!-- resources/views/layouts/app.blade.php -->
  <!DOCTYPE html>
  <html>
  <head>
      <title>My App</title>
      @stack('scripts')
      <!-- 
          1. `@stack('scripts')`：渲染名為 `scripts` 的 stack 的所有內容。
          2. 所有推送到 `scripts` 的內容會在這裡顯示。
      -->
  </head>
  <body>
      <h1>Welcome to My App</h1>
      @yield('content')
  </body>
  </html>
  ```

  ```html
  <!-- 子模版 -->
  <!-- resources/views/pages/home.blade.php -->
  @extends('layouts.app')

  @push('scripts')
      <script src="/js/app.js"></script>
  @endpush

  @prepend('scripts')
      <script src="/js/priority.js"></script>
  @endprepend

  @section('content')
      <p>This is the home page.</p>
  @endsection
  ```

  ```html
  <!-- 輸出結果 -->
  <!DOCTYPE html>
  <html>
  <head>
      <title>My App</title>
      <script src="/js/priority.js"></script>
      <script src="/js/app.js"></script>
  </head>
  <body>
      <h1>Welcome to My App</h1>
      <p>This is the home page.</p>
  </body>
  </html>
  ```
---

## 9. **Service Injection 與 Inline/Fragment**

- *@inject*：`注入服務`
  ```html
  @inject('metrics', 'App\Services\MetricsService')
  <div>Monthly Revenue: {{ $metrics->monthlyRevenue() }}</div>
    <!-- 
      1. `@inject('metrics', 'App\Services\MetricsService')`：
         - 將 `App\Services\MetricsService` 類別注入到 Blade 模板中，並賦值給 `$metrics`。
      2. `{{ $metrics->monthlyRevenue() }}`：
         - 使用注入的服務類別的方法 `monthlyRevenue()`，顯示月收入。
  -->
  ```

- *Blade::render*：渲染 `inline Blade` 字串
  - **適合使用的地方**
    - `控制器`：快速生成 HTML 回應。
    - `Artisan 命令`：生成動態內容。
    - `API 回應`：生成 HTML 片段作為 *JSON* 回應的一部分。
    - `動態渲染`：在需要快速渲染 Blade 模板片段的地方。
  - **注意事項**
    - `不適合複雜模板`：
      - *Blade::render()* 適合用於簡單的模板片段，複雜的模板應使用完整的 Blade 檔案。
    - `資料傳遞`：
      - 必須以*陣列形式*傳遞資料，並確保 Blade 字串中的變數名稱與陣列鍵名一致。
    - `性能`：
      - 適合*小型片段渲染*，頻繁使用可能影響性能。
  ```php
  Blade::render('Hello, {{ $name }}', ['name' => 'Julian Bashir']);
  <!-- 
    1. `Blade::render()`：
       - 用於渲染 Blade 字串，並傳入資料。
    2. `'Hello, {{ $name }}'`：
       - Blade 字串模板。
    3. `['name' => 'Julian Bashir']`：
       - 傳入的資料，`$name` 的值為 `'Julian Bashir'`。
    4. 輸出結果：
       Hello, Julian Bashir
  -->
  ```

- *@fragment ... @endfragment*：`只回傳片段`
  ```html
  @fragment('user-list')
      <ul>
          <li>User 1</li>
          <li>User 2</li>
      </ul>
  @endfragment
  <!-- 
      1. `@fragment('user-list')`：
        - 定義名為 `user-list` 的片段。
      2. `<ul>...</ul>`：
        - 片段的內容。
      3. `@endfragment`：
        - 結束片段定義。
  -->
  ```

  ```php
  <!-- 使用片段 -->
  view('dashboard')->fragment('user-list');
  <!-- 
      1. `view('dashboard')`：
        - 渲染 `dashboard` 視圖。
      2. `->fragment('user-list')`：
        - 只回傳名為 `user-list` 的片段內容。
      3. 輸出結果：
        <ul>
            <li>User 1</li>
            <li>User 2</li>
        </ul>
  -->
  ```

---

## 10. **自訂指令與 Echo Handler**

- *自訂指令*
  ```php
  use Illuminate\Support\Facades\Blade;
  Blade::directive('datetime', fn($exp) => "<?php echo ($exp)->format('m/d/Y H:i'); ?>");
  # @datetime($var)
  /*
    1. Blade::directive() 用於註冊自訂指令。
    2. 'datetime' 是指令名稱，對應於 Blade 模板中的 @datetime。
    3. fn($exp) => "<?php echo ($exp)->format('m/d/Y H:i'); ?>"：
       - 定義指令的行為，將傳入的日期時間物件格式化為 'm/d/Y H:i' 格式。
    4. 使用方式：
       @datetime($var)
       - $var 必須是日期時間物件（如 Carbon）。
    5. 輸出結果：
       如果 $var 是 '2023-10-01 14:30:00'，則輸出 '10/01/2023 14:30'。
  */
  ```

- *自訂 echo handler*
  ```php
  use Illuminate\Support\Facades\Blade;
  Blade::stringable(function (Money $money) { return $money->formatTo('en_GB'); });
  # {{ $money }}
  /*
    1. Blade::stringable() 用於註冊自訂的 Echo Handler。
    2. function (Money $money)：
       - 定義如何處理 Money 類型的物件。
       - return $money->formatTo('en_GB')：將 Money 格式化為英國貨幣格式。
    3. 使用方式：
       {{ $money }}
       - $money 必須是 Money 類型的物件。
    4. 輸出結果：
       如果 $money 的值是 1000，則輸出 '£1,000.00'。
  */
  ```

- *自訂 if 指令*
  ```php
  use Illuminate\Support\Facades\Blade;
  Blade::if('disk', fn($v) => config('filesystems.default') === $v);
  # @disk('local') ... @elsedisk('s3') ... @enddisk
  /*
    1. Blade::if() 用於註冊自訂的條件指令。
    2. 'disk' 是指令名稱，對應於 Blade 模板中的 @disk。
    3. fn($v) => config('filesystems.default') === $v：
       - 定義條件邏輯，判斷檔案系統是否為指定的值。
    4. 使用方式：
       @disk('local')
           <p>Using local disk</p>
       @elsedisk('s3')
           <p>Using S3 disk</p>
       @enddisk
       - 根據檔案系統的設定值顯示不同的內容。
    5. 輸出結果：
       如果 config('filesystems.default') 是 'local'，則顯示 'Using local disk'。
       如果是 's3'，則顯示 'Using S3 disk'。
  */
  ```

---