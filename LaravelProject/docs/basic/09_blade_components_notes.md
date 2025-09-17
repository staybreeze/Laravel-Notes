# *Laravel Blade Components 元件 筆記*

---

## 1. **簡介與核心概念**

- *Blade Components（元件）* 讓你將 `UI 區塊` 封裝成**可重用、可組合**的單元。
- 支援 `class-based` 與 `anonymous（匿名）元件 `，`slot` 概念類似 `section`，但更彈性。
- *生活化比喻*： __Component__ 就像「積木」，__slot__ 就像「積木上的插槽」，可自由組合內容。

---

## 2. **建立元件**

- *Class-based 元件*

```bash
php artisan make:component Alert
# 產生 app/View/Components/Alert.php 與 resources/views/components/alert.blade.php
```

---

- *子目錄元件*

```bash
php artisan make:component Forms/Input
# 產生 app/View/Components/Forms/Input.php 與 resources/views/components/forms/input.blade.php
```

---

- *匿名元件*（只有 Blade 檔）

<!-- 匿名元件就是只有 Blade 檔案，
     沒有對應的 PHP 類別，
     只用 Blade 標記和資料即可使用，簡化元件開發流程。 -->

<!-- 不像一般元件會有一個 PHP class（例如 app/View/Components/Example.php），
     匿名元件只需要 Blade 檔（例如 resources/views/components/example.blade.php），
     不需要額外寫 PHP 類別檔案。 -->

<!-- 元件的 PHP 類別檔案（例如 app/View/Components/Example.php）
     用來處理元件的邏輯、資料準備、屬性驗證等，
     可以在類別裡寫方法、接收參數，
     讓元件更有彈性和複雜功能。
     匿名元件則只用 Blade 標記，不處理 PHP 邏輯。 -->

```bash
php artisan make:component forms.input --view
# 產生 resources/views/components/forms/input.blade.php
```

- *註解*： 專案內`元件`自動註冊，`套件`需手動註冊。
<!-- Laravel 會自動註冊你專案內的元件（例如 resources/views/components），
     但如果是外部套件的元件，
     就需要用 Blade::component() 或 Blade::componentNamespace() 手動註冊，
     才能在 Blade 裡使用。 -->

<!-- 專案內的元件：
     指的是你自己專案裡建立的 Blade 元件，
     例如 resources/views/components/alert.blade.php 或 app/View/Components/Alert.php。

     外部元件：
     指的是來自 Laravel 套件、第三方 package 的元件，
     通常放在 vendor 目錄或套件自己的目錄，
     需要手動註冊才能在你的專案裡使用。 -->

---

## 3. **手動註冊與套件元件**

- *單一註冊*

  ```php
  <!-- `App\Providers\AppServiceProvider` 或自訂的 ServiceProvider 的 `boot()` 方法 -->
  Blade::component('package-alert', Alert::class); // 註冊 Blade 元件 <x-package-alert />，對應 Alert 類別
  # <x-package-alert />
  ```

---

- *命名空間自動註冊*

  ```php
  Blade::componentNamespace('Nightshade\\Views\\Components', 'nightshade'); 
  // 註冊元件命名空間，讓 <x-nightshade::alert /> 對應 Nightshade\Views\Components\Alert 類別
  # <x-nightshade::calendar />
  // 註冊元件命名空間可以讓你用 <x-命名空間::元件名稱 /> 的方式，
  // 直接引用不同目錄或套件的元件，
  // 方便管理、避免名稱衝突，也讓元件更有結構性。
  ```

  ```php
  <!-- app/Providers/AppServiceProvider.php -->
  namespace App\Providers;

  use Illuminate\Support\ServiceProvider;
  use Illuminate\Support\Facades\Blade;
  use App\View\Components\Alert;

  class AppServiceProvider extends ServiceProvider
  {
      /**
       * Bootstrap any application services.
       *
       * @return void
       */
      public function boot()
      {
          // 單一註冊 Blade 元件
          Blade::component('package-alert', Alert::class);
          // 註冊一個名為 <x-package-alert /> 的元件
          // 當使用 <x-package-alert /> 時，會渲染 Alert 類別對應的元件邏輯

          // 命名空間自動註冊 Blade 元件
          Blade::componentNamespace('Nightshade\\Views\\Components', 'nightshade');
          // 註冊一個命名空間為 nightshade 的元件
          // 當使用 <x-nightshade::calendar /> 時，會自動對應到 Nightshade\Views\Components\Calendar 類別
      }
  }
  ```

---

## 4. **元件渲染與巢狀**

- *基本渲染*

  ```html
  <x-alert />
  <x-user-profile />
  <x-inputs.button />
  ```

---

- *Index 元件*（目錄同名自動 root）

<!-- 如果你在 `resources/views/components/example/index.blade.php` 建立元件，
     Blade 會自動讓 `<x-example />` 使用這個 index 元件，
     不需要寫 `<x-example-index />`，目錄名稱就是元件名稱。
     這叫「目錄同名自動 root」。 -->

  ```html
  <x-card>
      <x-card.header>...</x-card.header> <!-- 卡片標頭 -->
      <x-card.body>...</x-card.body>     <!-- 卡片內容 -->
  </x-card>
  
  ```

  ```php
  <!-- 條件渲染：元件 class 可實作 shouldRender() 方法，決定元件是否要顯示。 
       例如： public function shouldRender() { return auth()->check(); } -->

  class Card extends Component
  {
        public function shouldRender()
        {
            return auth()->check(); // 只有登入時才渲染元件
        }
  }
```

---

## 5. **資料傳遞與屬性**

- *HTML 屬性傳遞*

  - __字串__ 直接寫， __變數/表達式__ 用 `:前綴`

  ```html
  <x-alert type="error" :message="$message" />
  ```

---

- *class 屬性 `camelCase` 對應 `kebab-case`*

  ```php
  public function __construct(public string $alertType) {

  }
  // Blade 元件標籤用 <x-alert alert-type="danger" />，會自動對應到 $alertType 屬性
  # <x-alert alert-type="danger" />
  ```

---

- *短屬性語法*

  ```html
  <x-profile :$userId :$name />
   <!-- 等同 <x-profile :user-id="$userId" :name="$name" /> -->
  ```

---

- *JS 框架屬性跳脫*

    ```html
  <x-button ::class="{ danger: isDeleting }">Submit</x-button>
  <!-- 
      使用 `::class` 是為了告訴 Blade 模板引擎不要解析這個屬性，
      而是直接保留原始的 `:class`，將其輸出到 HTML 中。
      這樣可以正確地傳遞給前端框架（如 Vue.js 或 Alpine.js）進行處理。

      例如：
      - Blade 會直接輸出：
        <button :class="{ danger: isDeleting }">Submit</button>
      - 這樣 `:class` 可以被 Vue.js 或 Alpine.js 解釋為 JavaScript 表達式。

      補充：
      1. `::class` 是 Blade 提供的跳脫語法，用於避免 Blade 將 `:class` 視為 PHP 表達式進行解析。
      2. 如果直接使用 `:class`，Blade 會嘗試解析為 PHP 表達式，可能導致錯誤或無法正確輸出。
      3. `::` 的作用是保留屬性的原始形式，適合用於需要傳遞給前端框架的屬性（如 Vue.js 的 `:class` 或 `:style`）。

      注意：
      - `::class` 適用於所有需要保留原始屬性的情境，例如：
        <x-button ::style="{ color: isRed ? 'red' : 'blue' }"></x-button>
        Blade 會輸出：
        <button :style="{ color: isRed ? 'red' : 'blue' }"></button>
      - 如果屬性值來自 PHP 表達式，則應使用 `:class`，例如：
        <x-button :class="$isActive ? 'active' : 'inactive'">Submit</x-button>
        Blade 會解析 PHP 表達式並輸出：
        <button class="active">Submit</button>（假設 $isActive 為 true）。
  -->
  ```

---

- *元件方法可在模板呼叫*

  ```html
  <!-- resources/views/components/dropdown-option.blade.php -->
  <option {{ $isSelected($value) ? 'selected' : '' }} value="{{ $value }}">{{ $label }}</option>
  <!-- 
      1. 這段程式碼展示了如何在 Blade 模板中呼叫元件的方法。
      2. `$isSelected($value)` 是元件中的一個方法，用於檢查當前的 `$value` 是否被選中。
      3. 如果 `$isSelected($value)` 回傳 `true`，則會輸出 `selected`，否則輸出空字串。
      4. `value="{{ $value }}"` 和 `{{ $label }}` 分別用於設定選項的值和顯示的標籤。

      範例輸出：
      假設 `$value = 1` 且 `$isSelected(1)` 回傳 `true`，則輸出：
      <option selected value="1">Label</option>
  -->
  ```

  ```php
  <!-- app/View/Components/DropdownOption.php -->

  namespace App\View\Components;

  use Illuminate\View\Component;

  class DropdownOption extends Component
  {
      public $value;
      public $label;
      public $selectedValue;

      public function __construct($value, $label, $selectedValue)
      {
          $this->value = $value;
          $this->label = $label;
          $this->selectedValue = $selectedValue;
      }

      // 方法：檢查當前值是否被選中
      public function isSelected($value)
      {
          return $this->selectedValue == $value;
      }

      public function render()
      {
          return view('components.dropdown-option');
      }
  }
  ```

---

- *依賴注入*：建構子可自動注入服務

  - **依賴注入的作用**
    - 依賴注入允許元件的建構子自動接收所需的服務或類別，無需手動實例化。
    - Laravel 的`服務容器`會自動解析並提供這些依賴。

  - **工作原理**
    - 當 Blade 渲染元件時，Laravel 會`檢查元件的建構子參數`。
    - 如果建構子需要某個`類別`（如 `UserService`），Laravel 會`自動實例化`該類別並將其注入。

  - **優點**
    - *簡化程式碼*：不需要手動實例化依賴。
    - *提高可測試性*：可以`輕鬆替換`依賴（例如在測試中使用模擬物件）。
    - *增強可維護性*：`依賴關係清晰`，程式碼更易於理解。

  - **適用場景**
    - 當`元件`需要使用`服務`（如 `UserService`） 或 `內建類別`（如 `Request`）時，可以使用依賴注入來簡化邏輯。

  ```php
  <!-- app/View/Components/UserProfile.php -->
  namespace App\View\Components;

  use Illuminate\View\Component;
  use App\Services\UserService;

  class UserProfile extends Component
  {
      public $user;

      /**
      * 建構子中注入 UserService
      *
      * @param UserService $userService 自定義的服務類別，用於處理使用者相關邏輯
      */
      public function __construct(UserService $userService)
      {
          // 使用注入的服務來獲取當前已登入的使用者資料
          $this->user = $userService->getAuthenticatedUser();
      }

      /**
      * 定義元件的渲染邏輯
      *
      * @return \Illuminate\View\View|string
      */
      public function render()
      {
          // 返回對應的 Blade 模板
          return view('components.user-profile');
      }
  }
  ```

  ```html
  <!-- resources/views/components/user-profile.blade.php -->
  <div>
    <!-- 顯示使用者的名稱 -->
    <h1>{{ $user->name }}</h1>
    <!-- 顯示使用者的電子郵件 -->
    <p>{{ $user->email }}</p>
  </div>
  ```

  ```php
  <!-- app/Services/UserService.php -->
  namespace App\Services;

  use App\Models\User;

  class UserService
  {
      /**
      * 獲取當前已登入的使用者
      *
      * @return User|null
      */
      public function getAuthenticatedUser()
      {
          // 使用 Laravel 的 auth() 幫助函式來獲取當前使用者
          return auth()->user();
      }
  }
  ```
  ```html
  <!-- 任何blade -->
  <x-user-profile />
  <!-- 
      當渲染 <x-user-profile /> 時：
      1. Laravel 會自動解析 UserProfile 元件類別。
      2. 透過依賴注入，Laravel 會自動實例化 UserService 並將其注入到元件的建構子中。
      3. 元件使用 UserService 獲取當前使用者資料，並將其傳遞到 Blade 模板中。
  -->
  ```
---

## 6. **$attributes 與屬性合併**

- *非建構子屬性自動進入 $attributes*

  ```php
  class Alert extends Component {
      public function __construct(public string $type, public string $message) {
        // 這裡 type 和 message 會進入建構子，
        // 但 class="mt-4" 沒有在建構子裡定義，
        // 所以會自動進入 $attributes，
        // 你可以在 Blade 裡用 {{ $attributes }} 輸出它。
        }
  }
  ```

  ```html
  <x-alert type="error" :message="$message" class="mt-4" />
  <div {{ $attributes }}>...</div>
  <!-- 
      1. 在 Blade 元件中，非建構子定義的屬性（如 `class="mt-4"`）會自動進入 `$attributes`。
      2. `$attributes` 是一個屬性集合，包含所有未在元件建構子中定義的屬性。
      3. 在這裡，`class="mt-4"` 會被存入 `$attributes`，並可在模板中使用。
  -->

---

- *合併 class 屬性*

  ```html
  <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
      {{ $message }}
  </div>
  # <x-alert type="error" :message="$message" class="mb-4" />
  # => <div class="alert alert-error mb-4">...</div>
  <!-- 
      1. 使用 `$attributes->merge()` 方法可以合併屬性值。
      2. 在這裡，`class="alert alert-error"` 是元件的預設值。
      3. 如果使用者傳入 `class="mb-4"`，則會合併為 `class="alert alert-error mb-4"`。
  -->
  ```

---

- *條件合併 class*

  ```html
  <div {{ $attributes->class(['p-4', 'bg-red' => $hasError]) }}>{{ $message }}</div>
  <!-- 
      1. 使用 `$attributes->class()` 方法可以條件性地合併 `class` 屬性。
      2. 在這裡，`p-4` 是無條件添加的類別。
      3. 如果 `$hasError` 為 `true`，則會添加 `bg-red` 類別。
      4. 最終輸出會根據條件動態生成 `class` 屬性。
  -->
  ```

---

- *合併其他屬性*

  ```html
  <button {{ $attributes->class(['p-4'])->merge(['type' => 'button']) }}>{{ $slot }}</button>
  <!-- 
      1. 使用 `$attributes->class()` 方法合併 `class` 屬性。
      2. 使用 `$attributes->merge()` 方法合併其他屬性（如 `type`）。
      3. 在這裡，`class="p-4"` 和 `type="button"` 是預設值。
      4. 如果使用者傳入其他屬性，則會與預設值合併。
  -->
  ```

---

- *非 class 屬性預設值*

  ```html
  <button {{ $attributes->merge(['type' => 'button']) }}>{{ $slot }}</button> 

  <!-- 
       1. `$attributes->merge(['type' => 'button'])` 設定預設屬性 type="button"。
       2. 如果元件標籤沒指定 type，按鈕會是 <button type="button">。
       3. 如果元件標籤有指定 type（如 <x-button type="submit">），merge 會自動用指定值覆蓋預設值，產生 <button type="submit">。
       4. `$attributes` 會包含所有非建構子屬性（例如 class、id、type 等）。
       5. `$slot` 代表元件內容（按鈕文字）。
  -->
  <!-- 預設結果 -->
  <x-button>Click Me</x-button>
  => <button type="button">Click Me</button>

  <!-- 覆蓋結果 -->
  <x-button type="submit">Send</x-button>
  => <button type="submit">Send</button>

  <!-- 這是教學常用的標記方式，  
       `# <x-button type="submit">Submit</x-button>` 表示 Blade 元件的使用方式，  
       `# => <button type="submit">Submit</button>` 表示元件渲染後產生的 HTML 結果。  
     `  =>` 代表「產生結果」或「輸出結果」，不是程式語法，只是說明用。 -->

  ```

---

- *prepend 合併*

  ```html
  <div {{ $attributes->merge(['data-controller' => $attributes->prepends('profile-controller')]) }}>{{ $slot }}</div>
  <!-- 
      1. 使用 `$attributes->prepends()` 方法可以在屬性值的開頭添加內容。
      2. 在這裡，`data-controller` 的值會以 `profile-controller` 開頭。
      3. 如果使用者傳入其他值，則會將其附加在 `profile-controller` 之後。
  -->
  ```

---

- *屬性過濾/查詢*

  - `filter`、`whereStartsWith`、`whereDoesntStartWith`、`first`、`has`、`hasAny`、`get`、`only`、`except`

    - __filter__
    - 用於過濾 `$attributes` 集合中的屬性，根據條件返回符合的屬性集合。

      ```php
      $attributes->filter(fn($value, $key) => $key === 'class');
      <!-- 只返回鍵為 `class` 的屬性。 -->
      ```

    - __whereStartsWith__
    - 返回`以`指定字串開頭的屬性。

      ```php
      $attributes->whereStartsWith('data-');
      <!-- 返回所有以 `data-` 開頭的屬性，例如 `data-controller`。 -->
      ```

    - __whereDoesntStartWith__
    - 返回`不以`指定字串開頭的屬性。

      ```php
      $attributes->whereDoesntStartWith('data-');
      <!-- 返回所有不以 `data-` 開頭的屬性。 -->
      ```

    - __first__
    - 返回集合中的`第一個屬性`。

      ```php
      $attributes->first();
      <!-- 返回 `$attributes` 集合中的第一個屬性。 -->
      ```

    - __has__
    - 檢查`是否存在`指定屬性。

      ```php
      $attributes->has('class');
      <!-- 如果存在 `class` 屬性，返回 `true`，否則返回 `false`。 -->
      ```

    - __hasAny__
    - 檢查`是否存在任意`指定屬性。

      ```php
      $attributes->hasAny(['class', 'id']);
      <!-- 如果存在 `class` 或 `id` 屬性，返回 `true`。 -->
      ```

    - __get__
    - `獲取`指定屬性的值。

      ```php
      $attributes->get('class');
      <!-- 返回 `class` 屬性的值，如果不存在則返回 `null`。 -->
      ```

    - __only__
    - 返回`指定`屬性集合。

      ```php
      $attributes->only(['class', 'id']);
      <!-- 返回只包含 `class` 和 `id` 的屬性集合。 -->
      ```

    - __except__
    - `排除`指定屬性，返回剩餘的屬性集合。

      ```php
      $attributes->except(['class', 'id']);
      <!-- 返回不包含 `class` 和 `id` 的屬性集合。 -->
      ```
---

## 7. **Slot**（插槽）

- *預設 slot*：`{{ $slot }}`

  ```html
  <x-alert>內容</x-alert>
  ```
  
 - 1. 預設的 `slot` 是**元件的主要內容區域**。
 - 2. 在這裡，「內容」 會被傳遞到元件內的 `{{ $slot }}`。
 - 3. 範例輸出：
         <div class="alert">內容</div>

      ```php
      // app/View/Components/Alert.php
      namespace App\View\Components;

      use Illuminate\View\Component;

      class Alert extends Component
      {
          public $type;

          public function __construct($type = 'info')
          {
              $this->type = $type;
          }

          public function render()
          {
              return view('components.alert');
          }
      }
      ```
      ```php
      // resources/views/components/alert.blade.php
      <div class="alert alert-{{ $type }}">
          {{ $slot }}
      </div>
      ```
      ```php
      // 使用範例
      <x-alert type="error">This is an error message.</x-alert>
      ```
      ```php
      // 輸出結果
      <div class="alert alert-error">
          This is an error message.
      </div>
      ```

---

- *命名 slot*

  ```html
  <x-alert>
      <x-slot:title>Server Error</x-slot>
      <strong>Whoops!</strong> Something went wrong!
  </x-alert>
  ```
 - 1. `命名 slot` 允許為元件的**特定區域**提供內容。
 - 2. `<x-slot:title>` 將內容 「**Server Error**」 傳遞到元件內的 `{{ $title }}`。
 - 3. 範例輸出：
       <div class="alert">
           <h1>Server Error</h1>
           <strong>Whoops!</strong> Something went wrong!
       </div>

      ```php
      // app/View/Components/Alert.php
      namespace App\View\Components;

      use Illuminate\View\Component;

      class Alert extends Component
      {
          public $type;

          public function __construct($type = 'info')
          {
              $this->type = $type;
          }

          public function render()
          {
              return view('components.alert');
          }
      }
      ```

      ```php
      // resources/views/components/alert.blade.php
      <div class="alert alert-{{ $type }}">
          @isset($title)
              <h1>{{ $title }}</h1>
          @endisset
          {{ $slot }}
      </div>
      ```
      ```php
      // 使用範例
      <x-alert type="error">
          <x-slot:title>Server Error</x-slot:title>
          <strong>Whoops!</strong> Something went wrong!
      </x-alert>
      ```
      ```php
      // 輸出結果
      <div class="alert alert-error">
          <h1>Server Error</h1>
          <strong>Whoops!</strong> Something went wrong!
      </div>
      ```

---

- *slot 判斷*

  ```php
  @if ($slot->isEmpty()) ... @endif
  @if ($slot->hasActualContent()) ... @endif
  ```
 - 1. `$slot->isEmpty()`：檢查 `slot` 是否為**空**。
 - 2. `$slot->hasActualContent()`：檢查 `slot` 是否**有實際內容**（非空白）。
 - 3. 範例：
       @if ($slot->isEmpty())
           <p>No content provided.</p>
       @endif

      ```php
      // app/View/Components/Alert.php
      namespace App\View\Components;

      use Illuminate\View\Component;

      class Alert extends Component
      {
          public $type;

          public function __construct($type = 'info')
          {
              $this->type = $type;
          }

          public function render()
          {
              return view('components.alert');
          }
      }
      ```
      ```php
      // resources/views/components/alert.blade.php
      <div class="alert alert-{{ $type }}">
          @if ($slot->isEmpty())
              <p>No content provided.</p>
          @else
              {{ $slot }}
          @endif
      </div>
      ```
      ```php
      // 使用範例
      <x-alert type="info"></x-alert>
      ```
      ```php
      // 輸出結果
      <div class="alert alert-info">
          <p>No content provided.</p>
      </div>
      ```

---

- *scoped slot*：slot 內可用 `$component` 取元件**方法/屬性**

  ```html
  <x-alert>
      <x-slot:title>{{ $component->formatAlert('Server Error') }}</x-slot>
      ...
  </x-alert>
  ```
 - 1. `Scoped slot` 允許在 slot 中使用元件的**屬性或方法**。
 - 2. `$component->formatAlert('Server Error')` 是元件中的**方法**，返回**格式化的標題**。
 - 3. 範例輸出：
       <div class="alert">
           <h1>Formatted: Server Error</h1>
           ...
       </div>

      ```php
      // app/View/Components/Alert.php
      namespace App\View\Components;

      use Illuminate\View\Component;

      class Alert extends Component
      {
          public $type;

          public function __construct($type = 'info')
          {
              $this->type = $type;
          }

          public function formatAlert($message)
          {
              return strtoupper($message);
          }

          public function render()
          {
              return view('components.alert');
          }
      }
      ```
      ```php
      // resources/views/components/alert.blade.php
      <div class="alert alert-{{ $type }}">
          @isset($title)
              <h1>{{ $title }}</h1>
          @endisset
          {{ $slot }}
      </div>
      ```
      ```php
      // 使用範例
      <x-alert type="error">
          <x-slot:title>{{ $component->formatAlert('Server Error') }}</x-slot:title>
          <strong>Whoops!</strong> Something went wrong!
      </x-alert>
      ```
      ```php
      // 輸出結果
      <div class="alert alert-error">
          <h1>SERVER ERROR</h1>
          <strong>Whoops!</strong> Something went wrong!
      </div>
      ```

---

- *slot 屬性*

  ```html
  <x-card>
      <x-slot:heading class="font-bold">Heading</x-slot>
      ...
      <x-slot:footer class="text-sm">Footer</x-slot>
  </x-card>
  <!-- slot 變數可用 $heading->attributes->class([...]) -->
  ```
 - 1. `Slot` 可以接受**屬性**，例如 `class="font-bold"`。
 - 2. 在元件內，可以使用 `$heading->attributes->class([...])` 操作 slot 的屬性。
 - 3. 範例輸出：
       <div class="card">
           <div class="heading font-bold">Heading</div>
           ...
           <div class="footer text-sm">Footer</div>
       </div>

      ```php
      // app/View/Components/Card.php
      namespace App\View\Components;

      use Illuminate\View\Component;

      class Card extends Component
      {
          public function render()
          {
              return view('components.card');
          }
      }
      ```
      ```php
      // resources/views/components/card.blade.php
      <div class="card">
          <div {{ $heading->attributes->class(['heading', 'font-bold']) }}>
              {{ $heading }}
          </div>
          <div class="content">
              {{ $slot }}
          </div>
          <div {{ $footer->attributes->class(['footer', 'text-sm']) }}>
              {{ $footer }}
          </div>
      </div>
      ```
      ```php
      // 使用範例
      <x-card>
          <x-slot:heading class="font-bold text-lg">Card Heading</x-slot:heading>
          <p>This is the card content.</p>
          <x-slot:footer class="text-gray-500">Card Footer</x-slot:footer>
      </x-card>
      ```
      ```php
      // 輸出結果
      <div class="card">
          <div class="heading font-bold text-lg">Card Heading</div>
          <div class="content">
              <p>This is the card content.</p>
          </div>
          <div class="footer text-gray-500">Card Footer</div>
      </div>
      ```

---

## 8. **Inline 與動態元件**

- *inline component*：`render()` 直接回傳 Blade 字串

  - **什麼是 Inline Component？**
    - Inline Component 是指在元件的 `render()` 方法中`直接回傳 Blade 模板字串，而不是使用對應的 Blade 模板檔案`。
    - 適合用於簡單的元件，無需建立額外的 Blade 模板檔案。

  ```php
  <!-- app/View/Components/Alert.php -->
  namespace App\View\Components;

  use Illuminate\View\Component;

  class Alert extends Component
  {
      public function render(): string
      {
          // 直接回傳 Blade 模板字串
          return <<<'blade'
              <div class="alert alert-danger">{{ $slot }}</div>
          blade;
      }
  }
  ```

  ```html
  <!-- 使用範例 -->
  <x-alert>這是一個錯誤訊息。</x-alert>
  ```

  ```html
  <!-- 輸出結果 -->
  <div class="alert alert-danger">這是一個錯誤訊息。</div>
  ```

---

- *inline component artisan 指令*

 ```bash
  php artisan make:component Alert --inline
 ```

---

- *動態元件*

  - **什麼是動態元件？**

    - `動態元件`允許根據`變數的值`動態渲染不同的元件。
    - 使用 <x-dynamic-component> 標籤，並透過 `:component` 屬性 __指定元件名稱__。

  ```php

  namespace App\View\Components;
  <!-- app/View/Components/Alert.php -->
  use Illuminate\View\Component;

  class Alert extends Component
  {
      public $type;

      public function __construct($type = 'info')
      {
          $this->type = $type;
      }

      public function render()
      {
          return view('components.alert');
      }
  }
  ```

  ```php
  <!-- resources/views/components/alert.blade.php -->
  <div class="alert alert-{{ $type }}">
      {{ $slot }}
  </div>
  ```

  ```html
  <!-- 使用範例 -->
  @php
    $componentName = 'alert';
  @endphp

  <x-dynamic-component :component="$componentName" type="error" class="mt-4">
      這是一個動態元件的錯誤訊息。
  </x-dynamic-component>
  ```

  ```html
  <!-- 輸出結果 -->
  <div class="alert alert-error mt-4">
      這是一個動態元件的錯誤訊息。
  </div>
  ```

---

## 9. **保留字與安全**

### *保留字*
- Laravel 元件的保留字包括：
  - `data`
  - `render`
  - `resolveView`
  - `shouldRender`
  - `view`
  - `withAttributes`
  - `withName`

---

### *注意事項*

1. 這些名稱是 Laravel 元件的保留字，**具有特定的功能或用途**。
2. **不能將這些名稱用作元件的 `public property` 或 `method`**，否則會導致衝突或錯誤。
3. **例外**：
   - *`data`*：可以安全地作為 `public property` 使用，因為 Laravel 並沒有對它進行特殊處理。
   - *`render`*：必須作為**方法**使用，用於定義元件的渲染邏輯，返回 Blade 模板或 HTML 字串。
4. **是合法的使用方式**：
   - *`data`*：可以作為屬性名稱，例如 `public $data`。
   - *`render`*：必須作為方法名稱，並返回視圖或 HTML。
   - **其他保留字**（如 `view`、`withAttributes` 等）：只能按照 Laravel 的預期用途使用，不能覆蓋或用作屬性名稱。

---

### *保留字的用途*
- `render`：用於定義 __元件的渲染邏輯__，返回 Blade 模板或 HTML 字串。
- `shouldRender`：用於 __條件性地決定__ 元件是否應該渲染。
- `withAttributes`：用於 __合併屬性到元件__。
- `view`：用於指定元件對應的 Blade 模板。
- `resolveView`：Laravel 內部用來 __解析元件__ 的視圖。
- `withName`：用於 __設定__ 元件的名稱。

---

### *範例與解釋*

#### **合法的使用**

```php
namespace App\View\Components;

use Illuminate\View\Component;

class Example extends Component
{
    public $data; // 合法，Laravel 不會對此產生衝突
    public $title;

    public function render()
    {
        return view('components.example');
    }
}
```

---

#### **非法的使用**

```php
namespace App\View\Components;

use Illuminate\View\Component;

class Example extends Component
{
    public $view; // 錯誤，`view` 是 Laravel 的保留字，不能作為 public property

    public function render()
    {
        return view('components.example');
    }

    public function withAttributes() // 錯誤，`withAttributes` 是 Laravel 的內部方法
    {
        // 自定義邏輯
    }
}
```

---

- **如何避免衝突？**

  - 如果需要使用類似於保留字的名稱，可以選擇其他名稱來避免衝突。例如：

    - 將 `view` 改為 `templateView。`
    - 將 `render` 改為 `customRender。`

```php
namespace App\View\Components;

use Illuminate\View\Component;

class Example extends Component
{
    public $templateView; // 使用其他名稱避免衝突
    public $customRender;

    public function render()
    {
        return view('components.example');
    }
}
```

---

## 10. **手動註冊與套件元件**

- *單一註冊*

  - 使用 `Blade::component` 方法可以手動註冊單個元件。
  - 註冊後，可以在 Blade 模板中使用 `<x-元件名稱 />` 來渲染該元件。
  ```php
  <!-- app/Providers/AppServiceProvider.php -->
  namespace App\Providers;

  use Illuminate\Support\ServiceProvider;
  use Illuminate\Support\Facades\Blade;

  class AppServiceProvider extends ServiceProvider
  {
      public function boot()
      {
          // 單一註冊元件
          Blade::component('package-alert', \App\View\Components\AlertComponent::class);
      }
  }
  # package-alert：元件的名稱，對應於 <x-package-alert />。
  # AlertComponent::class：元件的類別，通常位於 app/View/Components 或套件的元件目錄中。
  # <x-package-alert />
  # <x-package-alert type="error">This is an error message.</x-package-alert>
  ```

  ```php
  <!-- app/View/Components/AlertComponent.php -->
  namespace App\View\Components;

  use Illuminate\View\Component;

  class AlertComponent extends Component
  {
      public $type;

      public function __construct($type = 'info')
      {
          $this->type = $type;
      }

      public function render()
      {
          return view('components.alert-component');
      }
  }
  ```

  ```php
  // resources/views/components/alert-component.blade.php
  <div class="alert alert-{{ $type }}">
      {{ $slot }}
  </div>
  ```

  ```php
  // 使用元件
  <x-package-alert type="error">
      This is an error message.
  </x-package-alert>
  ```

  ```php
  // 輸出結果
  <div class="alert alert-error">
      This is an error message.
  </div>
  ```

---

- *命名空間自動註冊*

  - 使用 `Blade::componentNamespace `方法可以**批量註冊某個命名空間下的所有元件**。
  - 註冊後，可以在 Blade 模板中使用 <x-命名空間::元件名稱 /> 來渲染元件。
  ```php
  <!-- app/Providers/AppServiceProvider.php -->
  namespace App\Providers;

  use Illuminate\Support\ServiceProvider;
  use Illuminate\Support\Facades\Blade;

  class AppServiceProvider extends ServiceProvider
  {
      public function boot()
      {
          // 命名空間自動註冊
          Blade::componentNamespace('Nightshade\\Views\\Components', 'nightshade');
      }
  }
  # Nightshade\\Views\\Components：元件所在的命名空間。
  # nightshade：元件的命名空間前綴，對應於 <x-nightshade::元件名稱 />。
  # <x-nightshade::calendar />
  ```

  ```php
  <!-- Nightshade/Views/Components/Calendar.php -->
  namespace Nightshade\Views\Components;

  use Illuminate\View\Component;

  class Calendar extends Component
  {
      public function render()
      {
          return view('nightshade.components.calendar');
      }
  }
  ```

  ```php
  <!-- resources/views/nightshade/components/calendar.blade.php -->
  <div class="calendar">
    <p>This is the calendar component.</p>
  </div>
  ```

  ```html
 <!-- 使用元件 -->
  <x-nightshade::calendar />

  ```html
  <!-- 輸出結果 -->
  <div class="calendar">
    <p>This is the calendar component.</p>
  </div>
  ```
---