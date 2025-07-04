{{-- =========================================================================
[Creating and Rendering Views]
建立與渲染視圖（官方文件翻譯與實作註解）
==========================================================================

1. 建立視圖檔案
---------------------------------------------------------------------------
- 你可以直接在 resources/views 目錄下建立 .blade.php 檔案，
  例如 greeting.blade.php。
- 也可以用 Artisan 指令自動建立：
    php artisan make:view greeting
- .blade.php 副檔名代表這是 Blade 模板，可用 Blade 指令（如 {{ $name }}、@if、@foreach 等）。

2. 回傳視圖
---------------------------------------------------------------------------
- 路由或控制器可用 view() 輔助函式回傳視圖，並傳遞資料：
    Route::get('/', function () {
        return view('greeting', ['name' => 'James']);
    });

- 也可用 View facade：
    use Illuminate\Support\Facades\View;
    return View::make('greeting', ['name' => 'James']);

- 第一個參數是視圖檔名（不含副檔名），第二個參數是資料陣列，會自動轉成變數傳給視圖。

3. 巢狀視圖（Nested View Directories）
---------------------------------------------------------------------------
- 視圖可放在子目錄，如 resources/views/admin/profile.blade.php
- 取用時用「點記法」：
    return view('admin.profile', $data);
- 注意：目錄名稱不能有 . 字元。

4. 取得第一個存在的視圖
---------------------------------------------------------------------------
- 可用 View::first(['custom.admin', 'admin'], $data) 依序找出第一個存在的視圖。

5. 判斷視圖是否存在
---------------------------------------------------------------------------
- 用 View::exists('admin.profile') 回傳 true/false。

6. 傳遞資料給視圖
---------------------------------------------------------------------------
- 用陣列傳遞多個變數：
    return view('greetings', ['name' => 'Victoria']);
- 在視圖內用 {{ $name }} 取得。

- 也可用 with() 鏈式傳遞：
    return view('greeting')
        ->with('name', 'Victoria')
        ->with('occupation', 'Astronaut');

7. 全域共用資料
---------------------------------------------------------------------------
- 可用 View::share('key', 'value') 讓所有視圖都能取得該變數。
- 建議寫在 AppServiceProvider 的 boot() 方法內。
--}}

{{-- =========================================================================
[View Composers]
視圖作曲家（官方文件翻譯與實作註解）
==========================================================================

- View Composer 是在視圖渲染時自動執行的 callback 或 class method。
- 適合將「每次渲染該視圖都需要的資料」集中管理，避免重複撰寫邏輯。
- 特別適合同一視圖被多個路由/控制器共用時，統一注入資料。

【View Composer 與 Controller 傳資料比較】
---------------------------------------------------------------------------
- Controller 傳資料：
    - 每個 controller 或 route 都要手動傳資料給 view。
    - 適合「只在單一路由/控制器」需要的資料。
    - 優點：資料來源明確，彈性高。
    - 缺點：多個地方共用同一視圖時，容易重複、遺漏。
- View Composer：
    - 自動注入資料到指定視圖，controller 不需手動傳。
    - 適合「多個路由/控制器共用」且每次都要的資料（如 sidebar、全站統計、通知等）。
    - 優點：集中管理、減少重複、維護方便。
    - 缺點：資料來源較隱性，debug 時要注意資料流。

1. 註冊 Composer
---------------------------------------------------------------------------
- 在 AppServiceProvider 的 boot() 方法註冊：
    use App\View\Composers\ProfileComposer;
    use Illuminate\Support\Facades\View;
    View::composer('admin.profile', ProfileComposer::class);

- 也可用 closure：
    use Illuminate\View\View as ViewInstance;
    View::composer('welcome', function (ViewInstance $view) {
        // $view->with('key', 'value');
    });

- 綁定多個視圖：
    View::composer(['profile', 'dashboard'], MultiComposer::class);

- 綁定所有視圖（* 為萬用字元）：
    View::composer('*', function (ViewInstance $view) {
        // 全域注入資料
    });

2. Composer 類別範例
---------------------------------------------------------------------------
class ProfileComposer
{
    public function __construct(
        protected UserRepository $users,
    ) {}

    public function compose(View $view): void
    {
        $view->with('count', $this->users->count());
    }
}

3. View Creator
---------------------------------------------------------------------------
- 與 Composer 類似，但在視圖實例化後立即執行
    use App\View\Creators\ProfileCreator;
    View::creator('profile', ProfileCreator::class);

4. 優化 Blade 編譯效能
---------------------------------------------------------------------------
- 預設 Blade 模板會在請求時自動編譯，有效能損耗。
- 可用 php artisan view:cache 預先編譯所有視圖（建議部署時執行）。
- 用 php artisan view:clear 清除快取。
--}} 