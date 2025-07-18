{{-- =========================================================================
[Blade 資料顯示範例]
本範例展示如何在 Blade 模板安全顯示資料與執行 PHP 運算
========================================================================== --}}
<!DOCTYPE html>
<html>
<head>
    <title>Blade Demo</title>
</head>
<body>
    {{-- 顯示從路由傳遞進來的 name 變數 --}}
    <h1>Hello, {{ $name }}!</h1>
    {{-- 直接在 Blade 內執行 PHP 函式 --}}
    <p>現在時間戳：{{ time() }}</p>

    {{-- 顯示未經 escape 的內容（小心 XSS！） --}}
    <p>未轉義顯示：{!! '<b>粗體字</b>' !!}</p>

    {{-- Blade 與 JS 框架衝突解法：@ 轉義 --}}
    <p>JS 框架用法：@{{ name }}</p>

    {{-- Js::from 輸出安全 JSON --}}
    @php $array = ['foo' => 'bar', 'num' => 123]; @endphp
    <script>
        // 建議用 Js::from 輸出 JSON，避免 XSS
        var app = {{ Illuminate\Support\Js::from($array) }};
    </script>

    {{-- @verbatim 區塊，保留原始 {{ }} 給前端框架 --}}
    @verbatim
        <div>
            這裡的 {{ name }} 不會被 Blade 處理，適合前端框架
        </div>
    @endverbatim

    {{-- =========================================================================
    [Blade 指令（Directives）範例]
    官方語法與常用指令展示，皆附中文註解
    ========================================================================= --}}
    @php
        $records = [1, 2];
        $i = 2;
        session(['status' => '這是 session 狀態訊息']);
    @endphp

    {{-- 條件判斷 if/elseif/else --}}
    @if (count($records) === 1)
        只有一筆資料！
    @elseif (count($records) > 1)
        有多筆資料！
    @else
        沒有資料！
    @endif

    {{-- unless 判斷 --}}
    <!-- (! 條件) -->
    @unless (empty($records))
        有資料存在（unless 範例）
    @endunless

    {{-- isset/empty 判斷 --}}
    @isset($records)
        $records 已定義
    @endisset
    @empty($records)
        $records 為空
    @endempty

    {{-- switch 指令 --}}
    @switch($i)
        @case(1)
            第一種情況
            @break
        @case(2)
            第二種情況
            @break
        @default
            預設情況
    @endswitch

    {{-- session 指令 --}}
    @session('status')
        <div class="p-4 bg-green-100">
            {{ $value }}
        </div>
    @endsession

    {{-- =========================================================================
    [Blade 迴圈指令（Loops）範例]
    官方語法與常用迴圈展示，皆附中文註解
    ========================================================================= --}}
    @php
        $users = [
            (object)['id' => 1, 'name' => 'Alice', 'type' => 1, 'number' => 3, 'posts' => [1,2]],
            (object)['id' => 2, 'name' => 'Bob', 'type' => 2, 'number' => 5, 'posts' => [3]],
        ];
    @endphp

    {{-- for 迴圈 --}}
    @for ($i = 0; $i < 3; $i++)
        <div>for 目前值：{{ $i }}</div>
    @endfor

    {{-- foreach 迴圈 --}}
    @foreach ($users as $user)
        <p>foreach 用戶：{{ $user->id }} - {{ $user->name }}</p>
    @endforeach

    {{-- forelse 迴圈 --}}
    {{--
        @forelse 是 Blade 專屬語法，結合 foreach 與空集合判斷：
        - 有資料時，像 foreach 一樣跑每一筆。
        - 無資料時，自動執行 @empty ... @endforelse 區塊。
        - 常用於列表頁、查詢結果頁，讓程式碼更簡潔。
        等價寫法：
        @if (count($users) > 0)
            @foreach ($users as $user)
                ...
            @endforeach
        @else
            ...
        @endif
    --}}
    @forelse ($users as $user)
        <li>forelse 用戶：{{ $user->name }}</li>
    @empty
        <p>沒有用戶</p>
    @endforelse

    {{-- while 迴圈（僅示範，避免無窮迴圈） --}}
    {{--
        @while 是 Blade 支援的原生 PHP while 迴圈語法：
        - 用法與 PHP 完全相同。
        - 適合需要明確條件控制的場景。
        - 注意避免無窮迴圈，建議僅用於簡單、可控的情境。
        範例：
        @php $j = 0; @endphp
        @while ($j < 2)
            ...
            @php $j++; @endphp
        @endwhile
        // 只會執行兩次，分別顯示 j=0, j=1。
    --}}
    @php $j = 0; @endphp
    @while ($j < 2)
        <span>while 目前值：{{ $j }}</span>
        @php $j++; @endphp
    @endwhile

    {{-- foreach 內用 @continue/@break --}}
    @foreach ($users as $user)
        @continue($user->type == 1) {{-- 跳過 type=1 --}}
        <div>continue/break 範例：{{ $user->name }}</div>
        @break($user->number == 5) {{-- number=5 時中斷 --}}
    @endforeach

    {{-- $loop 變數用法（Blade 專屬智慧型迴圈資訊） --}}
    {{--
        $loop 是 Laravel Blade 模板引擎「獨有」的智慧型迴圈資訊變數：
        - 只在 @foreach/@forelse 內有效，原生 PHP foreach 沒有這功能。
        - Blade 會自動注入 $loop，方便取得索引、次數、首尾、巢狀層級等資訊。
        - 常見屬性與用途：
            $loop->index      // 目前索引（從 0 開始），可用於顯示序號、分隔線等
            $loop->iteration  // 目前次數（從 1 開始），常用於顯示第幾筆
            $loop->remaining  // 剩餘次數，適合倒數顯示
            $loop->count      // 總數，常用於總筆數顯示
            $loop->first      // 是否第一筆，適合加標題、首行樣式
            $loop->last       // 是否最後一筆，適合加結尾、分隔線
            $loop->even/odd   // 偶數/奇數次，適合交錯配色
            $loop->depth      // 巢狀層級，巢狀迴圈時可用於縮排、樣式
            $loop->parent     // 巢狀時取得上一層 $loop，可追蹤父層索引/屬性
        - 讓 Blade 模板更容易寫出有條件的列表、巢狀結構、分隔線、首尾標記等。
        - 原生 PHP 若要達到同樣效果，需自行設計計數器與判斷。
        - 巢狀迴圈時，$loop->parent 可取得外層 $loop 物件，方便多層資料處理。
    --}}
    <ul>
    @foreach ($users as $user)
        @if ($loop->first)
            <li>這是第一筆</li>
        @endif
        <li>{{ $user->name }}（索引：{{ $loop->index }}，次數：{{ $loop->iteration }}，剩餘：{{ $loop->remaining }}）</li>
        @if ($loop->last)
            <li>這是最後一筆</li>
        @endif
    @endforeach
    </ul>

    {{-- =========================================================================
    [$loop 變數屬性完整展示]
    $loop 變數可於 foreach/forelse 內取得迴圈資訊，以下為所有屬性與中文註解
    ========================================================================= --}}
    <ul>
    @foreach ($users as $user)
        <li>
            {{-- $loop->index：目前索引（從 0 開始） --}}
            索引：{{ $loop->index }}

            {{-- $loop->iteration：目前次數（從 1 開始） --}}
            次數：{{ $loop->iteration }}

            {{-- $loop->remaining：剩餘次數 --}}
            剩餘：{{ $loop->remaining }}

            {{-- $loop->count：總數 --}}
            總數：{{ $loop->count }}

            {{-- $loop->first：是否第一筆 --}}
            @if($loop->first)第一筆@endif

            {{-- $loop->last：是否最後一筆 --}}
            @if($loop->last)最後一筆@endif

            {{-- $loop->even/$loop->odd：偶數/奇數次 --}}
            @if($loop->even)偶數@endif@if($loop->odd)奇數@endif

            {{-- $loop->depth：巢狀層級 --}}
            巢狀層級：{{ $loop->depth }}
        </li>
    @endforeach
    </ul>

    {{-- 巢狀 $loop->parent 用法展示 --}}
    <ul>
    @foreach ($users as $user)
        @foreach ($user->posts as $post)
            <li>
                用戶：{{ $user->name }}
                貼文ID：{{ $post }}

                {{-- $loop->depth：巢狀層級 --}}
                巢狀層級：{{ $loop->depth }}
                
                {{-- $loop->parent->index：父層索引 --}}
                父層索引：{{ $loop->parent->index }}
            </li>
        @endforeach
    @endforeach
    </ul>

    {{-- =========================================================================
    [條件樣式、屬性、子視圖、原生 PHP、註解範例]
    Blade 進階指令與輔助語法完整展示，皆附中文註解
    ========================================================================= --}}
    @php
        $isActive = false;
        $hasError = true;
        $user = (object)['active' => true, 'isNotAdmin' => fn() => false, 'isAdmin' => fn() => true];
        $product = (object)['versions' => ['v1', 'v2']];
        $errors = collect(['error1']);
        $jobs = [
            (object)['id' => 1, 'name' => 'Job1'],
            (object)['id' => 2, 'name' => 'Job2'],
        ];
        $emptyJobs = [];
    @endphp

    {{-- @class 條件 class 樣式 --}}
    <!-- @class 是 Blade 的條件 class 樣式語法糖，讓你可以根據條件自動組合 CSS class 字串。 -->
    <span @class([
        'p-4', // 永遠加上
        'font-bold' => $isActive, // $isActive 為 true 才加
        'text-gray-500' => ! $isActive, // $isActive 為 false 才加
        'bg-red' => $hasError, // $hasError 為 true 才加
    ])>class 範例</span>
    {{-- 輸出：<span class="p-4 text-gray-500 bg-red"></span> --}}

    {{-- @style 條件 style 樣式 --}}
    @php $isActive = true; @endphp
    <span @style([
        'background-color: red', // 永遠加上
        'font-weight: bold' => $isActive, // $isActive 為 true 才加
    ])>style 範例</span>
    {{-- 輸出：<span style="background-color: red; font-weight: bold;"></span> --}}

    {{-- @checked/@selected/@disabled/@readonly/@required 條件屬性 --}}
    <input type="checkbox" name="active" value="active" @checked(old('active', $user->active)) />
    <select name="version">
        @foreach ($product->versions as $version)
            <option value="{{ $version }}" @selected(old('version') == $version)>{{ $version }}</option>
        @endforeach
    </select>
    <button type="submit" @disabled($errors->isNotEmpty())>送出</button>
    <input type="email" name="email" value="email@laravel.com" @readonly($user->isNotAdmin()) />
    <input type="text" name="title" value="title" @required($user->isAdmin()) />

    {{-- @include 子視圖引入 --}}
    {{--
        @include('shared.errors')
        - 用途：插入另一個 Blade 視圖（如共用錯誤訊息、表單片段）。
        - 適用：重複區塊、共用頁首/頁尾。
        - 注意：子視圖會繼承父視圖所有變數。
    --}}
    {{-- @include('shared.errors') --}} {{-- 範例，需有 shared/errors.blade.php --}}

    {{-- 傳遞額外資料 --}}
    {{--
        @include('view.name', ['status' => 'complete'])
        - 用途：引入子視圖時，額外傳遞資料。
        - 適用：子視圖需要特定變數時。
        - 注意：只在該子視圖有效。
    --}}
    {{-- @include('view.name', ['status' => 'complete']) --}}

    {{-- @includeIf：僅在視圖存在時引入 --}}
    {{--
        @includeIf('view.name', ['status' => 'complete'])
        - 用途：只有視圖存在時才 include，不存在不報錯。
        - 適用：可選模組、動態片段。
        - 注意：比 @include 更安全，避免拋例外。
    --}}
    {{-- @includeIf('view.name', ['status' => 'complete']) --}}

    {{-- @includeWhen/@includeUnless：根據條件引入 --}}
    {{--
        @includeWhen(true, 'view.name', ['status' => 'complete'])
        @includeUnless(false, 'view.name', ['status' => 'complete'])
        - 用途：根據條件決定是否 include。
        - 適用：權限、狀態、環境等條件式片段。
        - 注意：@includeWhen(條件, 視圖, 資料)，@includeUnless(條件, 視圖, 資料)（條件為 false 時才 include）。
    --}}
    {{-- @includeWhen(true, 'view.name', ['status' => 'complete']) --}}
    {{-- @includeUnless(false, 'view.name', ['status' => 'complete']) --}}
    
    {{-- @includeFirst：引入陣列中第一個存在的視圖 --}}
    {{--
        @includeFirst(['custom.admin', 'admin'], ['status' => 'complete'])
        - 用途：依序檢查多個視圖，找到第一個存在的就 include。
        - 適用：多主題、覆寫、fallback。
        - 注意：只會 include 第一個存在的視圖。
    --}}
    {{-- @includeFirst(['custom.admin', 'admin'], ['status' => 'complete']) --}}

    {{-- @each 集合渲染 --}}
    {{--
        @each('view.name', $jobs, 'job')
        @each('view.name', $emptyJobs, 'job', 'view.empty')
        - 用途：對集合每一筆都 include 一個子視圖。
        - 語法：@each('子視圖', 集合, 變數名, [空集合視圖])
        - 適用：大量重複區塊（如列表、卡片、表格 row）。
        - 注意：子視圖只會拿到單一資料，不會繼承父視圖變數。
    --}}
    {{-- @each('view.name', $jobs, 'job') --}} {{-- 需有 view.name --}}
    {{-- @each('view.name', $emptyJobs, 'job', 'view.empty') --}}

    {{-- @once/@pushOnce/@prependOnce --}}
    {{--
        @once ... @endonce
        - 用途：區塊只渲染一次，常用於 JS/CSS 只插入一次。
        @pushOnce/@prependOnce
        - 用途：只會 push/prepend 一次到指定 stack，避免重複插入。
        - 適用：元件、迴圈中只需插入一次的資源。
    --}}
    @once
        @push('scripts')
            <script>// JS 只插入一次</script>
        @endpush
    @endonce
    @pushOnce('scripts')
        <script>// JS 只插入一次（pushOnce）</script>
    @endPushOnce

    {{-- @php 原生 PHP 區塊 --}}
    {{--
        @php ... @endphp
        - 用途：在 Blade 內直接執行原生 PHP 程式碼。
        - 適用：簡單運算、宣告變數、呼叫 PHP 函式。
        - 注意：不建議寫複雜邏輯，Blade 主要負責顯示層。
    --}}
    @php $counter = 1; @endphp
    <div>PHP 變數：{{ $counter }}</div>

    {{-- @use 引入 class/function/const --}}
    {{-- @use('App\\Models\\Flight') --}}
    {{-- @use('App\\Models\\Flight', 'FlightModel') --}}
    {{-- @use('App\\Models\\{Flight, Airport}') --}}
    {{-- @use(function App\\Helpers\\format_currency) --}}
    {{-- @use(const App\\Constants\\MAX_ATTEMPTS) --}}
    {{-- @use(function App\\Helpers\\{format_currency, format_date}) --}}
    {{-- @use(const App\\Constants\\{MAX_ATTEMPTS, DEFAULT_TIMEOUT}) --}}

    {{-- Blade 註解範例 --}}
    {{-- 這是 Blade 註解，不會出現在 HTML --}}

    {{-- =========================================================================
    [Blade 元件（Components）完整範例]
    官方元件建立、註冊、呼叫、資料傳遞、slot、命名空間等條列註解
    ========================================================================= --}}
    {{-- 1. 類別元件（Class-based Component）建立 --}}
    {{--
    php artisan make:component Alert
    // 產生 app/View/Components/Alert.php 與 resources/views/components/alert.blade.php
    --}}
    {{-- 2. 子目錄元件 --}}
    {{--
    php artisan make:component Forms/Input
    // 產生 app/View/Components/Forms/Input.php 與 resources/views/components/forms/input.blade.php
    --}}
    {{-- 3. 匿名元件（Anonymous Component） --}}
    {{--
    php artisan make:component forms.input --view
    // 產生 resources/views/components/forms/input.blade.php，可用 <x-forms.input /> 呼叫
    --}}
    {{-- 4. Inline 元件 --}}
    {{--
    php artisan make:component Alert --inline
    // 產生只有 class，render() 直接回傳 Blade 字串
    --}}
    {{-- 5. 動態元件 --}}
    {{--
    <x-dynamic-component :component="$componentName" class="mt-4" />
    --}}
    {{-- 6. 元件自動註冊 --}}
    {{--
    專案內元件自動註冊於 app/View/Components 與 resources/views/components
    --}}
    {{-- 7. 套件元件手動註冊 --}}
    {{--
    use Illuminate\Support\Facades\Blade;
    Blade::component('package-alert', Alert::class); // <x-package-alert />
    Blade::componentNamespace('Nightshade\\Views\\Components', 'nightshade'); // <x-nightshade::calendar />
    --}}
    {{-- 8. 元件呼叫與巢狀 --}}
    {{--
    <x-alert />
    <x-user-profile />
    <x-inputs.button />
    <x-card>
        <x-card.header>...</x-card.header>
        <x-card.body>...</x-card.body>
    </x-card>
    --}}
    {{-- 9. 資料傳遞與屬性 --}}
    {{--
    <x-alert type="error" :message="$message" />
    // PHP constructor 用 camelCase，HTML 屬性用 kebab-case
    public function __construct(public string $alertType) {}
    <x-alert alert-type="danger" />
    // 短屬性語法 <x-profile :$userId :$name />
    // JS 框架衝突用 ::class
    <x-button ::class="{ danger: isDeleting }">Submit</x-button>
    --}}
    {{-- 10. 屬性合併與 $attributes 操作 --}}
    {{--
    <x-alert type="error" :message="$message" class="mt-4" />
    <div {{ $attributes }}>...</div>
    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
    <div {{ $attributes->class(['p-4', 'bg-red' => $hasError]) }}>
    <button {{ $attributes->class(['p-4'])->merge(['type' => 'button']) }}>{{ $slot }}</button>
    <div {{ $attributes->merge(['data-controller' => $attributes->prepends('profile-controller')]) }}>{{ $slot }}</div>
    // $attributes->filter/whereStartsWith/whereDoesntStartWith/first/has/hasAny/get/only/except
    --}}
    {{-- 11. Slot（插槽） --}}
    {{--
    <x-alert>
        <x-slot:title>Title</x-slot>
        內容
    </x-alert>
    // $slot->isEmpty()、$slot->hasActualContent()、scoped slot $component
    <x-alert><x-slot:title>{{ $component->formatAlert('Server Error') }}</x-slot>...</x-alert>
    // slot 屬性 <x-slot:heading class="font-bold">...</x-slot>，view 內 $heading->attributes
    // @props(['heading', 'footer'])
    --}}
    {{-- 12. Inline View --}}
    {{--
    public function render(): string {
        return <<<'blade'
            <div class="alert alert-danger">{{ $slot }}</div>
        blade;
    }
    --}}
    {{-- 13. 動態元件 --}}
    {{--
    <x-dynamic-component :component="$componentName" class="mt-4" />
    --}}
    {{-- 14. 關鍵字保留 --}}
    {{--
    data、render、resolveView、shouldRender、view、withAttributes、withName 為保留字，不能作為 public 屬性/方法
    --}}

    {{-- =========================================================================
    [Blade 元件 $attributes（屬性包）完整範例]
    官方屬性包自動傳遞、合併、查詢、過濾等條列註解（每點皆有對應範例，並逐行中文註解）
    ======================================================================== --}}

    {{-- 1. 自動屬性包 $attributes（非 constructor 屬性自動進入 $attributes） --}}
    {{--
    // 說明：
    // 當你在呼叫 Blade 元件時，傳給元件的屬性如果「不是」元件 class 建構子（constructor）有定義的參數，這些屬性就會自動被放進 $attributes 這個屬性包裡。
    // 例如 class、id、data-* 等 HTML 屬性。
    // 範例：
    // app/View/Components/Alert.php
    // public function __construct($type = 'info') { $this->type = $type; }
    // resources/views/components/alert.blade.php
    <div {{ $attributes }}>內容</div> {{-- $attributes 會自動帶入所有非 constructor 屬性，如 class、id --}}
    // 呼叫：
    <x-alert type="error" class="mt-4" id="main-alert" /> 
    {{-- 
        // 呼叫 alert 元件，傳入 type、class、id 三個屬性
        // - type="error" 會進入元件 class 的 $type（如果有 constructor 定義），但這個元件 blade 沒用到 $type，所以不影響 class
        // - class="mt-4" 會合併到 $attributes，最終 class 會是 alert alert-danger mt-4
        // - id="main-alert" 也會進入 $attributes，直接加到 <div> 上
        // - slot 沒內容，div 內是空的
    --}}

    // 實際渲染結果：
    <div class="alert alert-danger mt-4" id="main-alert">
        {{-- slot 沒內容，這裡是空的 --}}
    </div>

    {{-- 2. merge 合併預設屬性 --}}
    {{--
    // 說明：合併預設 class 或其他屬性，呼叫元件時可再疊加。
    // 範例：
    <div 
        {{ $attributes->merge(['class' => 'alert alert-'.$type]) }} {{-- 預設 class="alert alert-類型"，呼叫時可再加 class --}}
    >
        {{ $message }}
    </div>
    // 呼叫：
    <x-alert type="error" class="mb-4" /> {{-- class="mb-4" 會合併進來 --}}
    // 最終 class="alert alert-error mb-4"
    --}}

    {{-- 3. class 條件合併 --}}
    {{--
    // 說明：根據條件自動合併 class。
    // 範例：
    <div 
        {{ $attributes->class(['p-4', 'bg-red' => $hasError]) }} {{-- 預設加 p-4，$hasError 為 true 時加 bg-red --}}
    >
        {{ $message }}
    </div>
    // 呼叫：
    <x-alert :has-error="true" /> {{-- $hasError 為 true，會有 class="p-4 bg-red" --}}
    // 最終 class="p-4 bg-red"
    --}}

    {{-- 4. class+merge 鏈式合併 --}}
    {{--
    // 說明：可先 class() 再 merge()，合併多種屬性。
    // 範例：
    <button 
        {{ $attributes->class(['p-4'])->merge(['type' => 'button']) }} {{-- 先合併 class="p-4"，再預設 type="button" --}}
    >
        {{ $slot }} {{-- $slot 會顯示元件標籤內的內容 --}}
    </button>
    // 呼叫：
    <x-button class="btn-primary" type="submit">送出</x-button> {{-- class="btn-primary" 會合併，type="submit" 會覆蓋預設 --}}
    // 最終 <button class="btn-primary p-4" type="submit">送出</button>
    --}}

    {{-- 5. 非 class 屬性合併（會覆蓋） --}}
    {{--
    // 說明：merge 會覆蓋同名屬性。
    // 範例：
    <button 
        {{ $attributes->merge(['type' => 'button']) }} {{-- 預設 type="button"，呼叫時有 type 會被覆蓋 --}}
    >
        {{ $slot }}
    </button>
    // 呼叫：
    <x-button type="submit">Submit</x-button> {{-- type="submit" 會覆蓋預設 --}}
    // 最終 <button type="submit">Submit</button>
    --}}

    {{-- 6. prepends 合併 --}}
    {{--
    // 說明：prepend 可在屬性前加值。
    // 範例：
    <div 
        {{ $attributes->merge(['data-controller' => $attributes->prepends('profile-controller')]) }} {{-- data-controller 會加上 profile-controller --}}
    >
        {{ $slot }}
    </div>
    // 呼叫：
    <x-profile data-controller="user" /> {{-- 會變成 data-controller="profile-controller user" --}}
    --}}

    {{-- 7. filter 屬性過濾 --}}
    {{--
    // 說明：只保留符合條件的屬性。
    // 範例：
    {{ $attributes->filter(fn (string $value, string $key) => $key == 'foo') }} {{-- 只保留 foo 屬性 --}}
    // 呼叫：
    <x-demo foo="bar" baz="qux" /> {{-- 只會輸出 foo="bar" --}}
    --}}

    {{-- 8. whereStartsWith/whereDoesntStartWith --}}
    {{--
    // 說明：篩選屬性名稱開頭。
    // 範例：
    {{ $attributes->whereStartsWith('wire:model') }} {{-- 只保留 wire:model 開頭的屬性 --}}
    // 呼叫：
    <x-debug-attrs wire:model="name" wire:model.lazy="email" foo="bar" /> {{-- 只會輸出 wire:model 相關屬性 --}}
    --}}

    {{-- 9. first 取得第一個屬性 --}}
    {{--
    // 說明：取得第一個符合條件的屬性。
    // 範例：
    {{ $attributes->whereStartsWith('wire:model')->first() }} {{-- 取得 wire:model 開頭的第一個屬性 --}}
    // 呼叫：
    <x-debug-attrs wire:model="name" wire:model.lazy="email" /> {{-- 只會輸出 name --}}
    --}}

    {{-- 10. has/hasAny 判斷屬性是否存在 --}}
    {{--
    // 說明：判斷屬性是否存在。
    // 範例：
    @if ($attributes->has('class'))<div>Class attribute is present</div>@endif {{-- 有 class 屬性才顯示 --}}
    @if ($attributes->has(['name', 'class']))<div>All of the attributes are present</div>@endif {{-- name、class 都有才顯示 --}}
    @if ($attributes->hasAny(['href', ':href', 'v-bind:href']))<div>One of the attributes is present</div>@endif {{-- 只要有其中一個就顯示 --}}
    // 呼叫：
    <x-link class="foo" name="bar" />
    --}}

    {{-- 11. get/only/except 取得/排除屬性 --}}
    {{--
    // 說明：取得、只取、排除屬性。
    // 範例：
    {{ $attributes->get('class') }} {{-- 取得 class 屬性 --}}
    {{ $attributes->only(['class']) }} {{-- 只保留 class 屬性 --}}
    {{ $attributes->except(['class']) }} {{-- 排除 class 屬性 --}}
    // 呼叫：
    <x-link class="foo" href="#" data-id="abc" /> {{-- only 只會輸出 class，except 會排除 class --}}
    --}}

    {{-- -------------------------------------------------------------------------
    [實際可執行 $attributes 範例]
    --------------------------------------------------------------------------
    1. 建立元件 resources/views/components/button.blade.php：
        <button
            {{ $attributes->merge(['type' => 'button', 'class' => 'btn']) }}
        >
            {{ $slot }}
        </button>

    2. 呼叫元件傳遞屬性：
        <x-button>預設按鈕</x-button>
        <x-button class="btn-primary">主色按鈕</x-button>
        <x-button type="submit">送出</x-button>
        <x-button data-id="123" class="btn-danger">危險操作</x-button>

    3. 條件 class 合併：
        // resources/views/components/alert.blade.php
        <div {{ $attributes->class([
            'alert',
            'alert-danger' => $type === 'danger',
            'alert-success' => $type === 'success',
        ]) }}>
            {{ $slot }}
        </div>
        // 呼叫：
        <x-alert type="danger" class="mb-2">錯誤訊息</x-alert>
        <x-alert type="success">成功訊息</x-alert>

    4. 取得/判斷/過濾屬性：
        // resources/views/components/input.blade.php
        <input
            {{ $attributes->merge(['class' => 'form-control']) }}
        >
        @if ($attributes->has('required'))
            <span class="text-danger">*</span>
        @endif
        // 呼叫：
        <x-input required placeholder="請輸入..." />

    5. only/except 範例：
        // resources/views/components/link.blade.php
        <a
            {{ $attributes->only(['href', 'target', 'class']) }}
        >
            {{ $slot }}
        </a>
        // 呼叫：
        <x-link href="https://laravel.com" class="link" data-id="abc">Laravel</x-link>

    6. whereStartsWith/whereDoesntStartWith 範例：
        // resources/views/components/debug-attrs.blade.php
        @php
            $wireAttrs = $attributes->whereStartsWith('wire:model');
        @endphp
        @foreach($wireAttrs as $key => $value)
            <div>{{ $key }} = {{ $value }}</div>
        @endforeach
        // 呼叫：
        <x-debug-attrs wire:model="name" wire:model.lazy="email" foo="bar" />
    --------------------------------------------------------------------------
    --}}

  {{-- =========================================================================
    [Blade 元件 Reserved Keywords 與 Slot（插槽）完整範例]
    官方保留字、slot、多 slot、slot 判斷、scoped slot、slot 屬性、@props 條列註解（每點皆有對應範例，並逐行中文註解）
    ======================================================================== --}}

    {{-- 1. Reserved Keywords（保留字） --}}
    {{--
    下列關鍵字為 Blade 元件內部保留，不能作為 public 屬性或方法：
    data、render、resolveView、shouldRender、view、withAttributes、withName
    --}}

    {{-- 2. 單一 slot 用法 --}}
    {{--
    // /resources/views/components/alert.blade.php
    <div class="alert alert-danger">
        {{ $slot }} {{-- $slot 會顯示元件標籤內的內容 --}}
    </div>
    // 呼叫：
    <x-alert /> {{-- 這裡 slot 內容為空 --}}
    --}}

    {{-- 3. 多 slot 用法 --}}
    {{--
    // /resources/views/components/alert.blade.php
    <span class="alert-title">{{ $title }}</span> {{-- $title slot 內容 --}}
    <div class="alert alert-danger">
        {{ $slot }} {{-- 主 slot 內容 --}}
    </div>
    // 呼叫：
    <x-alert>
        <x-slot:title>
            Server Error {{-- 傳給 $title slot --}}
        </x-slot>
        <strong>Whoops!</strong> Something went wrong! {{-- 主 slot --}}
    </x-alert>
    --}}

    {{-- 4. slot 判斷 isEmpty/hasActualContent --}}
    {{--
    <span class="alert-title">{{ $title }}</span>
    <div class="alert alert-danger">
        @if ($slot->isEmpty()) {{-- 判斷 slot 是否為空 --}}
            This is default content if the slot is empty. {{-- slot 為空時顯示預設內容 --}}
        @else
            {{ $slot }} {{-- slot 有內容時顯示 slot --}}
        @endif
    </div>
    @if ($slot->hasActualContent()) {{-- slot 內容不只註解時 --}}
        The scope has non-comment content.
    @endif
    --}}

    {{-- 5. scoped slot（$component） --}}
    {{--
    <x-alert>
        <x-slot:title>
            {{ $component->formatAlert('Server Error') }} {{-- 使用元件方法處理 slot 內容 --}}
        </x-slot>
        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
    --}}

    {{-- 6. slot 屬性 --}}
    {{--
    <x-card class="shadow-sm">
        <x-slot:heading class="font-bold">
            Heading {{-- heading slot 並加上 class --}}
        </x-slot>
        Content {{-- 主 slot --}}
        <x-slot:footer class="text-sm">
            Footer {{-- footer slot 並加上 class --}}
        </x-slot>
    </x-card>
    // 於元件 view 內：
    @props([
        'heading',
        'footer',
    ])
    <div {{ $attributes->class(['border']) }}>
        <h1 {{ $heading->attributes->class(['text-lg']) }}>
            {{ $heading }} {{-- heading slot 內容 --}}
        </h1>
        {{ $slot }} {{-- 主 slot --}}
        <footer {{ $footer->attributes->class(['text-gray-700']) }}>
            {{ $footer }} {{-- footer slot 內容 --}}
        </footer>
    </div>
    --}}

    {{-- slot 是什麼？ --}}
    {{--
    slot（插槽）就是「元件裡預留給你放內容的地方」。
    - 呼叫元件時，標籤中間放的內容都會自動被塞進 slot。
    - 讓元件更彈性、可重用。

    生活化比喻：
    - 元件像便當盒，slot 就是主菜區，你呼叫元件時放進去的內容都會自動放到 slot。

    最簡單範例：
    // resources/views/components/alert.blade.php
    <div class="alert">
        {{ $slot }}  {{-- 這裡就是插槽，會顯示呼叫元件時放進來的內容 --}}
    </div>

    // 呼叫：
    <x-alert>
        這是一個警告訊息！
    </x-alert>
    // 渲染結果：
    <div class="alert">
        這是一個警告訊息！
    </div>

    slot 的用途：
    - 讓元件內容可自訂、彈性。
    - 支援多 slot（如標題、內容、footer）。
    - 可判斷 slot 是否有內容（$slot->isEmpty()）。

    小結：
    - slot 就是元件的「內容插槽」，讓你決定元件裡要顯示什麼內容。
    - 呼叫元件時，標籤中間的內容都會自動進到 slot。
    - 多 slot 讓你可以有多個插槽（如標題、footer）。
    --}}

    {{-- =========================================================================
    [實作範例] Blade alert 元件 slot 實際用法
    完整示範 slot、title slot、class 合併
    ========================================================================= --}}
    {{--
    // 呼叫方式一：只有主 slot
    <x-alert>
        這是一個警告訊息！
    </x-alert>

    // 呼叫方式二：有 title slot
    <x-alert>
        <x-slot:title>
            重要通知
        </x-slot>
        請盡快處理您的帳號問題。
    </x-alert>

    // 呼叫方式三：自訂 class
    <x-alert class="mb-4">
        <x-slot:title>
            錯誤
        </x-slot>
        操作失敗，請重試。
    </x-alert>
    --}} 

    {{-- =========================================================================
    [Blade 元件渲染與資料傳遞完整範例]
    官方元件呼叫、資料傳遞、屬性命名、方法、依賴注入、閉包渲染等條列註解
    ========================================================================= --}}
    {{-- 1. 元件渲染方式 --}}
    {{--
    <x-alert />
    <x-user-profile />
    <x-inputs.button />
    --}}

    {{-- 2. 條件渲染 shouldRender --}}
    {{--
    // 在元件 class 實作 shouldRender()， 回傳 false 則不渲染
    public function shouldRender(): bool {
        return Str::length($this->message) > 0;
    }
    --}}

    {{-- 3. Index 元件（目錄同名自動 <x-card>） --}}
    {{--
    <x-card>
        <x-card.header>...</x-card.header>
        <x-card.body>...</x-card.body>
    </x-card>
    --}}

    {{-- 4. 資料傳遞與屬性 --}}
    {{--
    <x-alert type="error" :message="$message" />
    // class constructor 內 public 屬性自動注入 view
    public function __construct(public string $type, public string $message) {}
    // view 內直接 {{ $type }}、{{ $message }}
    --}}

    {{-- 5. 屬性命名規則 --}}
    {{--
    // PHP constructor 用 camelCase，HTML 屬性用 kebab-case
    public function __construct(public string $alertType) {}
    <x-alert alert-type="danger" />
    --}}

    {{-- 6. 短屬性語法 --}}
    {{--
    <x-profile :$userId :$name />
    // 等同 <x-profile :user-id="$userId" :name="$name" />
    --}}

    {{-- 7. JS 框架衝突（::class） --}}
    {{--
    <x-button ::class="{ danger: isDeleting }">Submit</x-button>
    // Blade 會輸出 <button :class="{ danger: isDeleting }">Submit</button>
    --}}

    {{-- 8. 元件方法呼叫 --}}
    {{--
    // class 內 public function isSelected(string $option): bool
    <option {{ $isSelected($value) ? 'selected' : '' }} value="{{ $value }}">{{ $label }}</option>
    --}}

    {{-- 9. render 閉包存取 componentName/attributes/slot --}}
    {{--
    public function render(): Closure {
        return function (array $data) {
            // $data['componentName']、$data['attributes']、$data['slot']
            return '<div {{ $attributes }}>Components content</div>';
        };
    }
    --}}

    {{-- 10. 依賴注入 --}}
    {{--
    public function __construct(
        public AlertCreator $creator,
        public string $type,
        public string $message,
    ) {}
    --}}

    {{-- 11. 隱藏屬性/方法 $except --}}
    {{--
    protected $except = ['type'];
    --}}

    {{-- =========================================================================
    [Blade Inline Component Views、動態元件、手動註冊完整範例]
    官方 inline 元件、動態元件、套件手動註冊、命名空間條列註解
    ========================================================================= --}}
    {{-- 1. Inline Component View（inline 元件） --}}
    {{--
    // class 內 render() 直接回傳 Blade 字串
    public function render(): string {
        return <<<'blade'
            <div class="alert alert-danger">
                {{ $slot }}
            </div>
        blade;
    }
    --}}

    {{-- 2. 產生 inline 元件指令 --}}
    {{--
    php artisan make:component Alert --inline
    // 只產生 class，無 blade 檔，render() 直接回傳內容
    --}}

    {{-- 3. 動態元件 --}}
    {{--
    // $componentName = "secondary-button";
    <x-dynamic-component :component="$componentName" class="mt-4" />
    --}}

    {{-- 4. 手動註冊元件（套件/非預設目錄） --}}
    {{--
    use Illuminate\Support\Facades\Blade;
    use VendorPackage\View\Components\AlertComponent;
    public function boot(): void {
        Blade::component('package-alert', AlertComponent::class);
    }
    // <x-package-alert />
    --}}

    {{-- 5. componentNamespace 自動註冊命名空間 --}}
    {{--
    use Illuminate\Support\Facades\Blade;
    public function boot(): void {
        Blade::componentNamespace('Nightshade\\Views\\Components', 'nightshade');
    }
    // <x-nightshade::calendar />
    // <x-nightshade::color-picker />
    // 子目錄支援 dot 語法
    --}}

    {{-- =========================================================================
    [Blade Anonymous Components（匿名元件）完整範例]
    官方匿名元件、index、@props、@aware、anonymousComponentPath 條列註解
    ========================================================================= --}}
    {{-- 1. 匿名元件（無 class，僅 blade 檔） --}}
    {{--
    // resources/views/components/alert.blade.php
    <div class="alert alert-danger">{{ $slot }}</div>
    // 呼叫：<x-alert />
    --}}

    {{-- 2. 子目錄匿名元件 --}}
    {{--
    // resources/views/components/inputs/button.blade.php
    <x-inputs.button />
    --}}

    {{-- 3. 匿名 index 元件 --}}
    {{--
    // resources/views/components/accordion/accordion.blade.php
    // resources/views/components/accordion/item.blade.php
    <x-accordion>
        <x-accordion.item>...</x-accordion.item>
    </x-accordion>
    --}}

    {{-- 4. @props 指定資料屬性與預設值 --}}
    {{--
    // resources/views/components/alert.blade.php
    @props(['type' => 'info', 'message'])
    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
    // 呼叫：<x-alert type="error" :message="$message" class="mb-4" />
    --}}

    {{-- 5. @aware 取得父元件資料 --}}
    {{--
    // resources/views/components/menu/index.blade.php
    @props(['color' => 'gray'])
    <ul {{ $attributes->merge(['class' => 'bg-'.$color.'-200']) }}>{{ $slot }}</ul>
    // resources/views/components/menu/item.blade.php
    @aware(['color' => 'gray'])
    <li {{ $attributes->merge(['class' => 'text-'.$color.'-800']) }}>{{ $slot }}</li>
    // 呼叫：
    <x-menu color="purple">
        <x-menu.item>...</x-menu.item>
    </x-menu>
    --}}

    {{-- 6. anonymousComponentPath 註冊自訂匿名元件路徑 --}}
    {{--
    // AppServiceProvider boot()
    Blade::anonymousComponentPath(__DIR__.'/../components');
    // <x-panel />
    Blade::anonymousComponentPath(__DIR__.'/../components', 'dashboard');
    // <x-dashboard::panel />
    --}}

    {{-- =========================================================================
    [Blade Layouts（元件式與繼承式）完整範例]
    官方元件式 layout、繼承式 layout、slot、@extends、@section、@yield、@parent 條列註解
    ========================================================================= --}}
    {{-- 1. 元件式 Layout（推薦現代用法） --}}
    {{--
    // resources/views/components/layout.blade.php
    <html>
        <head>
            <title>{{ $title ?? 'Todo Manager' }}</title>
        </head>
        <body>
            <h1>Todos</h1>
            <hr/>
            {{ $slot }}
        </body>
    </html>
    // resources/views/tasks.blade.php
    <x-layout>
        @foreach ($tasks as $task)
            <div>{{ $task }}</div>
        @endforeach
    </x-layout>
    // 傳遞 slot:title
    <x-layout>
        <x-slot:title>
            Custom Title
        </x-slot>
        @foreach ($tasks as $task)
            <div>{{ $task }}</div>
        @endforeach
    </x-layout>
    // 路由
    use App\Models\Task;
    Route::get('/tasks', function () {
        return view('tasks', ['tasks' => Task::all()]);
    });
    --}}

    {{-- 2. 繼承式 Layout（@extends/@section/@yield） --}}
    {{--
    // resources/views/layouts/app.blade.php
    <html>
        <head>
            <title>App Name - @yield('title')</title>
        </head>
        <body>
            @section('sidebar')
                This is the master sidebar.
            @show
            <div class="container">
                @yield('content')
            </div>
        </body>
    </html>
    // resources/views/child.blade.php
    @extends('layouts.app')
    @section('title', 'Page Title')
    @section('sidebar')
        @@parent
        <p>This is appended to the master sidebar.</p>
    @endsection
    @section('content')
        <p>This is my body content.</p>
    @endsection
    // @yield 可帶預設值：@yield('content', 'Default content')
    --}}

    {{-- =========================================================================
    [Blade Forms（表單）相關指令完整範例]
    官方 @csrf、@method、@error、error bag 條列註解
    ========================================================================= --}}
    {{-- 1. CSRF 欄位（@csrf） --}}
    {{--
    <form method="POST" action="/profile">
        @csrf
        ...
    </form>
    // 產生隱藏欄位 <input type="hidden" name="_token" value="...">
    --}}

    {{-- 2. HTTP 動詞偽造（@method） --}}
    {{--
    <form action="/foo/bar" method="POST">
        @method('PUT')
        ...
    </form>
    // 產生隱藏欄位 <input type="hidden" name="_method" value="PUT">
    --}}

    {{-- 3. 驗證錯誤訊息（@error） --}}
    {{--
    <!-- /resources/views/post/create.blade.php -->
    <label for="title">Post Title</label>
    <input id="title" type="text" class="@error('title') is-invalid @enderror" />
    @error('title')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    --}}

    {{-- 4. @error + @else 用法 --}}
    {{--
    <!-- /resources/views/auth.blade.php -->
    <label for="email">Email address</label>
    <input id="email" type="email" class="@error('email') is-invalid @else is-valid @enderror" />
    --}}

    {{-- 5. error bag 多表單錯誤 --}}
    {{--
    <!-- /resources/views/auth.blade.php -->
    <label for="email">Email address</label>
    <input id="email" type="email" class="@error('email', 'login') is-invalid @enderror" />
    @error('email', 'login')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    --}}

    {{-- =========================================================================
    [Blade Stacks、Service Injection、Inline Blade、Fragments 完整範例]
    官方 @push/@stack/@prepend/@inject/Blade::render/@fragment 條列註解
    ========================================================================= --}}
    {{-- 1. Stacks（@push/@stack/@prepend/@pushIf） --}}
    {{--
    // 子視圖或區塊 push 內容到指定 stack
    @push('scripts')
        <script src="/example.js"></script>
    @endpush

    // 條件 push
    @pushIf($shouldPush, 'scripts')
        <script src="/example.js"></script>
    @endPushIf
    // layout 或父視圖渲染 stack
    <head>
        @stack('scripts')
    </head>

    // prepend 內容到 stack 最前面
    @prepend('scripts')
        This will be first...
    @endprepend
    @push('scripts')
        This will be second...
    @endpush
    --}}

    {{-- 2. Service Injection（@inject） --}}
    {{--
    @inject('metrics', 'App\\Services\\MetricsService')
    <div>
        Monthly Revenue: {{ $metrics->monthlyRevenue() }}.
    </div>
    --}}

    {{-- 3. Inline Blade Templates（Blade::render） --}}
    {{--
    use Illuminate\Support\Facades\Blade;
    return Blade::render('Hello, {{ $name }}', ['name' => 'Julian Bashir']);
    // 自動寫入 storage/framework/views
    // 若要渲染後自動刪除快取檔：
    return Blade::render('Hello, {{ $name }}', ['name' => 'Julian Bashir'], deleteCachedView: true);
    --}}

    {{-- 4. Fragments（@fragment、fragment 方法） --}}
    {{--
    // dashboard.blade.php
    @fragment('user-list')
        <ul>
            @foreach ($users as $user)
                <li>{{ $user->name }}</li>
            @endforeach
        </ul>
    @endfragment
    // 只回傳 fragment
    return view('dashboard', ['users' => $users])->fragment('user-list');
    // 條件 fragment
    return view('dashboard', ['users' => $users])->fragmentIf($request->hasHeader('HX-Request'), 'user-list');
    // 多個 fragment
    view('dashboard', ['users' => $users])->fragments(['user-list', 'comment-list']);
    view('dashboard', ['users' => $users])->fragmentsIf($request->hasHeader('HX-Request'), ['user-list', 'comment-list']);
    --}}

    {{-- =========================================================================
    [Extending Blade（自訂指令、stringable、Blade::if）完整範例]
    官方 directive、stringable、if/elsedirective/unlessdirective 條列註解
    ========================================================================= --}}
    {{-- 1. 自訂 Blade 指令（Blade::directive） --}}
    {{--
    // AppServiceProvider boot()
    Blade::directive('datetime', function (string $expression) {
        return "<?php echo ($expression)->format('m/d/Y H:i'); ?>";
    });
    
    // Blade 用法：@datetime($var)
    // 產生 PHP：<?php echo ($var)->format('m/d/Y H:i'); ?>
    // 修改 directive 後需執行 php artisan view:clear 清除快取
    --}}

    {{-- 2. 自訂 echo handler（Blade::stringable） --}}
    {{--
    use Money\Money;
    Blade::stringable(function (Money $money) {
        return $money->formatTo('en_GB');
    });
    // Blade 直接 {{ $money }} 會自動呼叫 formatTo
    --}}
    
    {{-- 3. 自訂 if 指令（Blade::if） --}}
    {{--
    Blade::if('disk', function (string $value) {
        return config('filesystems.default') === $value;
    });
    // Blade 用法：
    @disk('local')
        <!-- The application is using the local disk... -->
    @elsedisk('s3')
        <!-- The application is using the s3 disk... -->
    @else
        <!-- The application is using some other disk... -->
    @enddisk
    @unlessdisk('local')
        <!-- The application is not using the local disk... -->
    @enddisk
    --}}
</body>
</html> 