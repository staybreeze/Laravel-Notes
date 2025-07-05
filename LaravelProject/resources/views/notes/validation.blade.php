{{--
    # Laravel Validation（驗證）教學

    本文件完整介紹 Laravel 資料驗證用法、API、錯誤訊息、表單回填、進階技巧。
    適用於表單驗證、API 輸入驗證、資料庫唯一性檢查等場合。
    內容皆有中文註解、章節分明、逐行說明、生活化比喻。
--}}

{{-- ========================= --}}
{{-- # 1. 驗證介紹與常見場景 --}}
{{-- ========================= --}}

{{--
    Laravel 提供多種驗證方式，最常用為 Request 實例的 validate() 方法。
    支援多種驗證規則（required、unique、max、nullable...），可驗證欄位唯一性、格式、巢狀資料等。
    常見場景：表單送出、API 輸入、資料庫檢查。
--}}

{{-- ========================= --}}
{{-- # 2. 路由、Controller、表單驗證範例 --}}
{{-- ========================= --}}

{{--
    // routes/web.php
    use App\Http\Controllers\PostController;
    Route::get('/post/create', [PostController::class, 'create']);
    Route::post('/post', [PostController::class, 'store']);
    // GET 顯示表單，POST 儲存資料

    // app/Http/Controllers/PostController.php
    use Illuminate\Http\Request;
    class PostController extends Controller {
        public function create() {
            return view('post.create');
        }
        public function store(Request $request) {
            // 驗證資料
            $validated = $request->validate([
                'title' => 'required|unique:posts|max:255',
                'body' => 'required',
            ]);
            // 驗證通過繼續執行...
            // $post = ...
            return redirect('/posts');
        }
    }
--}}

{{-- ========================= --}}
{{-- # 3. 驗證規則寫法 --}}
{{-- ========================= --}}

{{--
    // 字串寫法
    $request->validate([
        'title' => 'required|unique:posts|max:255',
        'body' => 'required',
    ]);

    // 陣列寫法
    $request->validate([
        'title' => ['required', 'unique:posts', 'max:255'],
        'body' => ['required'],
    ]);
    
    // 指定 error bag
    $request->validateWithBag('post', [ ... ]);

    // bail：遇到第一個錯誤就停止該欄位後續驗證
    $request->validate([
        'title' => 'bail|required|unique:posts|max:255',
        'body' => 'required',
    ]);

    // 巢狀欄位（dot 語法）
    $request->validate([
        'author.name' => 'required',
        'author.description' => 'required',
    ]);
    
    // 欄位名稱含點號需跳脫
    $request->validate([
        'v1\.0' => 'required',
    ]);
--}}

{{-- ========================= --}}
{{-- # 4. 錯誤訊息顯示與 $errors 變數 --}}
{{-- ========================= --}}

{{--
    驗證失敗時自動重導回前頁，錯誤訊息與輸入資料自動存入 session。
    $errors 變數（Illuminate\Support\MessageBag）自動注入所有 view，可直接使用：
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    // @error 指令可顯示單一欄位錯誤
    <input name="title" class="@error('title') is-invalid @enderror">
    @error('title')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    // named error bag
    @error('title', 'post') ... @enderror
--}}

{{-- ========================= --}}
{{-- # 5. XHR 驗證、JSON 回應格式 --}}
{{-- ========================= --}}

{{--
    用 XHR 的瀏覽器 API 進行 AJAX 的非同步存取請求驗證失敗時，Laravel 會自動回傳 422 狀態碼與 JSON 錯誤訊息。
    
    實際範例：
    前端發送資料：
    {
        "team_name": "",           // 空字串（驗證失敗）
        "authorization": {
            "role": "invalid_role" // 無效角色（驗證失敗）
        }
    }
    
    Laravel 回傳 422 錯誤：
    {
        "message": "驗證失敗，請檢查輸入資料",
        "errors": {
            "team_name": [
                "團隊名稱不能為空"
            ],
            "authorization.role": [
                "選擇的角色無效"
            ]
        }
    }
    
    說明：
    - status: 422（驗證失敗狀態碼）
    - message: 整體錯誤訊息
    - errors: 各欄位的詳細錯誤訊息
    - 巢狀欄位（如 authorization.role）會自動轉為 dot 語法
--}}

{{-- ========================= --}}
{{-- # 6. 表單回填與 old() --}}
{{-- ========================= --}}

{{--
    驗證失敗重導時，所有輸入資料自動存入 session，可用 old() 輔助函式回填：
    <input type="text" name="title" value="{{ old('title') }}">
    // 也可用 $request->old('title')
--}}

{{-- ========================= --}}
{{-- # 7. Optional/Nullable 欄位 --}}
{{-- ========================= --}}

{{--
    Laravel 預設有 TrimStrings、ConvertEmptyStringsToNull middleware，
    若欄位可為 null，請加 nullable：
    $request->validate([
        'publish_at' => 'nullable|date',
    ]);
--}}

{{-- ========================= --}}
{{-- # 7.1 Optional/Nullable 詳細說明 --}}
{{-- ========================= --}}

{{--
    Optional 和 Nullable 的差異：
    
    - optional：欄位可以不存在於請求中
    - nullable：欄位可以為 null 值
    
    實際範例：
    // 1. 只有 nullable（欄位必須存在，但可以為 null）
    $request->validate([
        'publish_at' => 'nullable|date',  // 可以傳 null 或日期
        'description' => 'nullable|string|max:500',  // 可以傳 null 或字串
    ]);
    
    // 2. 只有 optional（欄位可以不存在）
    $request->validate([
        'tags' => 'optional|array',  // 可以不傳這個欄位
        'metadata' => 'optional|json',  // 可以不傳這個欄位
    ]);
    
    // 3. 同時使用（欄位可以不存在，存在時可以為 null）
    $request->validate([
        'avatar' => 'optional|nullable|image|max:2048',  // 可以不傳，或傳 null
        'bio' => 'optional|nullable|string|max:1000',    // 可以不傳，或傳 null
    ]);
    
    生活化比喻：
    - optional：就像選填欄位，可以不填寫
    - nullable：就像可以留空的欄位，填寫了但內容是空的
--}}

{{-- ========================= --}}
{{-- # 7.2 實際使用場景 --}}
{{-- ========================= --}}

{{--
    常見使用場景：
    
    1. 個人資料編輯：
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',      // 電話可以留空
        'avatar' => 'optional|nullable|image',    // 頭像可以不傳或留空
        'bio' => 'optional|nullable|string|max:500', // 簡介可以不傳或留空
    ]);
    
    2. 文章發布：
    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'publish_at' => 'nullable|date|after:now',  // 發布時間可以留空（立即發布）
        'tags' => 'optional|array',                 // 標籤可以不傳
        'featured_image' => 'optional|nullable|image', // 特色圖片可以不傳或留空
    ]);
    
    3. 商品管理：
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'description' => 'nullable|string',        // 描述可以留空
        'sale_price' => 'nullable|numeric|min:0',  // 特價可以留空
        'images' => 'optional|array|min:1',        // 圖片陣列可以不傳
    ]);
--}}

{{-- ========================= --}}
{{-- # 8. 錯誤訊息自訂與語系檔 --}}
{{-- ========================= --}}

{{--
    錯誤訊息可於 lang/{語系}/validation.php 語系檔自訂。
    若無 lang 目錄可用 php artisan lang:publish 產生。
    可複製到其他語系目錄進行翻譯。
    // 生活化比喻：像是自訂表單提示語，讓用戶更容易理解錯誤原因。
--}}

{{-- ========================= --}}
{{-- # 8.1 語系檔設定流程 --}}
{{-- ========================= --}}

{{--
    1. 產生語系檔：
    php artisan lang:publish
    
    2. 建立中文語系目錄：
    mkdir -p lang/zh-TW
    
    3. 複製英文語系檔到中文：
    cp lang/en/validation.php lang/zh-TW/validation.php
    
    4. 編輯中文語系檔：
    // lang/zh-TW/validation.php
--}}

{{-- ========================= --}}
{{-- # 8.2 語系檔結構說明 --}}
{{-- ========================= --}}

{{--
    語系檔包含三個主要區塊：
    
    1. attributes：欄位名稱翻譯
    'attributes' => [
        'email' => '電子郵件',
        'password' => '密碼',
        'name' => '姓名',
        'phone' => '電話號碼',
    ],
    
    2. values：欄位值翻譯
    'values' => [
        'gender' => [
            'male' => '男性',
            'female' => '女性',
        ],
        'status' => [
            'active' => '啟用',
            'inactive' => '停用',
        ],
    ],
    
    3. custom：自訂錯誤訊息
    'custom' => [
        'email' => [
            'required' => '請輸入電子郵件',
            'email' => '請輸入有效的電子郵件格式',
            'unique' => '此電子郵件已被使用',
        ],
        'password' => [
            'min' => '密碼至少需要 :min 個字元',
            'confirmed' => '密碼確認不符',
        ],
    ],
--}}

{{-- ========================= --}}
{{-- # 8.3 實際範例 --}}
{{-- ========================= --}}

{{--
    完整的中文語系檔範例：
    
    // lang/zh-TW/validation.php
    <?php
    
    return [
        'accepted' => ':attribute 必須接受。',
        'accepted_if' => '當 :other 為 :value 時，:attribute 必須接受。',
        'active_url' => ':attribute 不是一個有效的網址。',
        'after' => ':attribute 必須要晚於 :date。',
        'after_or_equal' => ':attribute 必須要等於 :date 或更晚。',
        'alpha' => ':attribute 只能以字母組成。',
        'alpha_dash' => ':attribute 只能以字母、數字、連接線(-)及底線(_)組成。',
        'alpha_num' => ':attribute 只能以字母及數字組成。',
        'array' => ':attribute 必須為陣列。',
        'before' => ':attribute 必須要早於 :date。',
        'before_or_equal' => ':attribute 必須要等於 :date 或更早。',
        'between' => [
            'array' => ':attribute: 必須有 :min - :max 個元素。',
            'file' => ':attribute 必須介於 :min 至 :max KB 之間。',
            'numeric' => ':attribute 必須介於 :min 至 :max 之間。',
            'string' => ':attribute 必須介於 :min 至 :max 個字元之間。',
        ],
        'boolean' => ':attribute 必須為布林值。',
        'confirmed' => ':attribute 確認欄位不一致。',
        'current_password' => '密碼錯誤。',
        'date' => ':attribute 不是有效的日期。',
        'date_equals' => ':attribute 必須等於 :date。',
        'date_format' => ':attribute 不符合 :format 的格式。',
        'declined' => ':attribute 必須拒絕。',
        'declined_if' => '當 :other 為 :value 時，:attribute 必須拒絕。',
        'different' => ':attribute 與 :other 必須不同。',
        'digits' => ':attribute 必須是 :digits 位數字。',
        'digits_between' => ':attribute 必須介於 :min 至 :max 位數字。',
        'dimensions' => ':attribute 圖片尺寸不正確。',
        'distinct' => ':attribute 欄位值重複。',
        'doesnt_end_with' => ':attribute 不能以下列之一結尾：:values。',
        'doesnt_start_with' => ':attribute 不能以下列之一開頭：:values。',
        'email' => ':attribute 必須是有效的電子郵件地址。',
        'ends_with' => ':attribute 結尾必須包含下列之一：:values。',
        'enum' => '所選的 :attribute 無效。',
        'exists' => '所選的 :attribute 無效。',
        'extensions' => ':attribute 必須包含以下副檔名：:values。',
        'file' => ':attribute 必須是一個檔案。',
        'filled' => ':attribute 不能留空。',
        'gt' => [
            'array' => ':attribute 必須多於 :value 個元素。',
            'file' => ':attribute 必須大於 :value KB。',
            'numeric' => ':attribute 必須大於 :value。',
            'string' => ':attribute 必須多於 :value 個字元。',
        ],
        'gte' => [
            'array' => ':attribute 必須多於或等於 :value 個元素。',
            'file' => ':attribute 必須大於或等於 :value KB。',
            'numeric' => ':attribute 必須大於或等於 :value。',
            'string' => ':attribute 必須多於或等於 :value 個字元。',
        ],
        'hex_color' => ':attribute 必須是有效的十六進位顏色代碼。',
        'image' => ':attribute 必須是一張圖片。',
        'in' => '所選的 :attribute 無效。',
        'in_array' => ':attribute 沒有在 :other 中。',
        'integer' => ':attribute 必須是一個整數。',
        'ip' => ':attribute 必須是一個有效的 IP 位址。',
        'ipv4' => ':attribute 必須是一個有效的 IPv4 位址。',
        'ipv6' => ':attribute 必須是一個有效的 IPv6 位址。',
        'json' => ':attribute 必須是正確的 JSON 字串。',
        'lowercase' => ':attribute 必須是小寫。',
        'lt' => [
            'array' => ':attribute 必須少於 :value 個元素。',
            'file' => ':attribute 必須小於 :value KB。',
            'numeric' => ':attribute 必須小於 :value。',
            'string' => ':attribute 必須少於 :value 個字元。',
        ],
        'lte' => [
            'array' => ':attribute 必須少於或等於 :value 個元素。',
            'file' => ':attribute 必須小於或等於 :value KB。',
            'numeric' => ':attribute 必須小於或等於 :value。',
            'string' => ':attribute 必須少於或等於 :value 個字元。',
        ],
        'mac_address' => ':attribute 必須是一個有效的 MAC 位址。',
        'max' => [
            'array' => ':attribute 最多有 :max 個元素。',
            'file' => ':attribute 不能大於 :max KB。',
            'numeric' => ':attribute 不能大於 :max。',
            'string' => ':attribute 不能多於 :max 個字元。',
        ],
        'max_digits' => ':attribute 不能超過 :max 位數字。',
        'mimes' => ':attribute 必須為 :values 檔案格式。',
        'mimetypes' => ':attribute 必須為 :values 檔案格式。',
        'min' => [
            'array' => ':attribute 至少有 :min 個元素。',
            'file' => ':attribute 不能小於 :min KB。',
            'numeric' => ':attribute 不能小於 :min。',
            'string' => ':attribute 不能少於 :min 個字元。',
        ],
        'min_digits' => ':attribute 必須至少有 :min 位數字。',
        'missing' => ':attribute 必須缺少。',
        'missing_if' => '當 :other 為 :value 時，:attribute 必須缺少。',
        'missing_unless' => '除非 :other 為 :value，否則 :attribute 必須缺少。',
        'missing_with' => '當 :values 存在時，:attribute 必須缺少。',
        'missing_with_all' => '當 :values 都存在時，:attribute 必須缺少。',
        'multiple_of' => ':attribute 必須為 :value 的倍數。',
        'not_in' => '所選的 :attribute 無效。',
        'not_regex' => ':attribute 的格式無效。',
        'numeric' => ':attribute 必須為一個數字。',
        'password' => [
            'letters' => ':attribute 必須包含至少一個字母。',
            'mixed' => ':attribute 必須包含至少一個大寫字母和一個小寫字母。',
            'numbers' => ':attribute 必須包含至少一個數字。',
            'symbols' => ':attribute 必須包含至少一個符號。',
            'uncompromised' => '給定的 :attribute 出現在資料外洩中。請選擇不同的 :attribute。',
        ],
        'present' => ':attribute 欄位必須存在。',
        'present_if' => '當 :other 為 :value 時，:attribute 欄位必須存在。',
        'present_unless' => '除非 :other 為 :value，否則 :attribute 欄位必須存在。',
        'present_with' => '當 :values 存在時，:attribute 欄位必須存在。',
        'present_with_all' => '當 :values 都存在時，:attribute 欄位必須存在。',
        'prohibited' => ':attribute 欄位被禁止。',
        'prohibited_if' => '當 :other 為 :value 時，:attribute 欄位被禁止。',
        'prohibited_unless' => '除非 :other 為 :value，否則 :attribute 欄位被禁止。',
        'prohibits' => ':attribute 欄位禁止 :other 存在。',
        'regex' => ':attribute 的格式無效。',
        'required' => ':attribute 不能為空。',
        'required_array_keys' => ':attribute 欄位必須包含以下條目：:values。',
        'required_if' => '當 :other 為 :value 時 :attribute 不能為空。',
        'required_if_accepted' => '當 :other 被接受時，:attribute 欄位是必需的。',
        'required_unless' => '當 :other 不為 :values 時 :attribute 不能為空。',
        'required_with' => '當 :values 存在時 :attribute 不能為空。',
        'required_with_all' => '當 :values 都存在時 :attribute 不能為空。',
        'required_without' => '當 :values 不存在時 :attribute 不能為空。',
        'required_without_all' => '當 :values 都不存在時 :attribute 不能為空。',
        'same' => ':attribute 與 :other 必須相同。',
        'size' => [
            'array' => ':attribute 必須為 :size 個元素。',
            'file' => ':attribute 的大小不能為 :size KB。',
            'numeric' => ':attribute 的大小不能為 :size。',
            'string' => ':attribute 必須是 :size 個字元。',
        ],
        'starts_with' => ':attribute 開頭必須包含下列之一：:values。',
        'string' => ':attribute 必須是一個字串。',
        'timezone' => ':attribute 必須是一個正確的時區值。',
        'unique' => ':attribute 已經存在。',
        'uploaded' => ':attribute 上傳失敗。',
        'uppercase' => ':attribute 必須是大寫。',
        'url' => ':attribute 的格式無效。',
        'ulid' => ':attribute 必須是有效的 ULID。',
        'uuid' => ':attribute 必須是有效的 UUID。',
        
        'attributes' => [
            'name' => '姓名',
            'email' => '電子郵件',
            'password' => '密碼',
            'phone' => '電話號碼',
            'address' => '地址',
            'title' => '標題',
            'content' => '內容',
            'description' => '描述',
            'price' => '價格',
            'quantity' => '數量',
        ],
        
        'values' => [
            'gender' => [
                'male' => '男性',
                'female' => '女性',
            ],
            'status' => [
                'active' => '啟用',
                'inactive' => '停用',
            ],
        ],
        
        'custom' => [
            'email' => [
                'required' => '請輸入電子郵件',
                'unique' => '此電子郵件已被使用',
            ],
            'password' => [
                'confirmed' => '密碼確認不符',
                'min' => '密碼至少需要 :min 個字元',
            ],
        ],
    ];
--}}

{{-- ========================= --}}
{{-- # 8.4 使用方式 --}}
{{-- ========================= --}}

{{--
    1. 設定應用程式語系：
    // config/app.php
    'locale' => 'zh-TW',
    
    2. 在驗證中使用：
    $request->validate([
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);
    
    3. 錯誤訊息會自動使用中文：
    // 原本：The email field is required.
    // 現在：電子郵件不能為空。
    
    生活化比喻：
    就像把英文說明書翻譯成中文，讓用戶更容易理解錯誤原因。
--}}

{{-- ========================= --}}
{{-- # 9. Form Request 驗證補充筆記 --}}
{{-- ========================= --}}

{{--
    ## 建立 Form Request
    使用 artisan 指令建立：
    php artisan make:request StorePostRequest

    產生的類別會放在 app/Http/Requests/ 目錄下。
    每個 Form Request 會有 authorize() 與 rules() 方法。

    ## 使用方式
    1. 在 Controller 方法型別提示 StorePostRequest
    2. 驗證會在進入 Controller 前自動執行
    3. 驗證失敗自動重導回前頁，錯誤訊息自動帶到 view
    4. 授權失敗自動回傳 403 HTTP 狀態碼，Controller 不會執行

    ## 典型開發流程
    - 建立 Form Request 類別
    - 實作 rules()/authorize()/messages()/attributes()/after()/prepareForValidation() 等
    - Controller 型別提示該 Request
    - 直接用 $request->validated() 取得資料
    - 可用 $request->safe()->only([...]) 或 except([...]) 取部分欄位

    ## 生活化比喻
    Form Request 就像「專屬驗證小幫手」，在你進入 Controller 前，先幫你把資料檢查好、權限確認好，Controller 只需專心處理業務邏輯。
--}}

{{-- ========================= --}}
{{-- # 9.1 Form Request 實際使用情況 --}}
{{-- ========================= --}}

{{--
    ## 基本 Form Request（教學範例）
    
    // StorePostRequest.php - 簡單版本
    class StorePostRequest extends FormRequest
    {
        public function authorize()
        {
            return true;  // 預設：允許所有請求
        }

        public function rules()
        {
            return [
                'title' => 'required|string|max:255',
                'body' => 'required|string',
            ];
        }
        // 沒有自訂 messages() 和 attributes()，使用語系檔預設值
    }
    
    // Controller 使用
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();  // 取得驗證過的資料
        $only = $request->safe()->only(['title', 'body']);  // 只取部分欄位
        $except = $request->safe()->except(['slug']);  // 排除部分欄位
        
        // 儲存資料...
        return redirect()->back()->with('success', '驗證通過！');
    }
    
    ## 完整 Form Request（實際專案）
    
    // StorePostRequest.php - 完整版本
    class StorePostRequest extends FormRequest
    {
        public function authorize()
        {
            // 檢查用戶是否有權限建立文章
            return $this->user()->can('create', Post::class);
        }

        public function rules()
        {
            return [
                'title' => 'required|string|max:255',
                'body' => 'required|string|min:10',
                'category_id' => 'required|exists:categories,id',
                'tags' => 'optional|array',
                'tags.*' => 'string|max:50',
                'publish_at' => 'nullable|date|after:now',
            ];
        }

        public function messages()
        {
            return [
                'title.required' => '請輸入文章標題',
                'title.max' => '標題不能超過 255 個字元',
                'body.required' => '請輸入文章內容',
                'body.min' => '文章內容至少需要 10 個字元',
                'category_id.required' => '請選擇文章分類',
                'category_id.exists' => '選擇的分類不存在',
                'publish_at.after' => '發布時間必須在現在之後',
            ];
        }

        public function attributes()
        {
            return [
                'title' => '文章標題',
                'body' => '文章內容',
                'category_id' => '文章分類',
                'tags' => '標籤',
                'publish_at' => '發布時間',
            ];
        }

        public function prepareForValidation()
        {
            // 在驗證前處理資料
            $this->merge([
                'title' => trim($this->title),
                'body' => strip_tags($this->body),
            ]);
        }

        public function after(Validator $validator)
        {
            // 額外的驗證邏輯
            if ($this->title === 'forbidden') {
                $validator->errors()->add('title', '標題不能為 forbidden');
            }
        }
    }
--}}

{{-- ========================= --}}
{{-- # 9.2 什麼時候需要自訂方法 --}}
{{-- ========================= --}}

{{--
    ## 預設行為（不需要自訂）
    
    1. authorize() 預設回傳 true
    2. messages() 預設使用語系檔
    3. attributes() 預設使用語系檔
    4. 沒有 prepareForValidation() 和 after()
    
    ## 需要自訂的情況
    
    1. authorize() - 需要權限檢查時
       - 檢查用戶角色
       - 檢查資源擁有權
       - 檢查業務邏輯權限
    
    2. messages() - 需要特殊錯誤訊息時
       - 業務相關的錯誤訊息
       - 多語系支援
       - 品牌化的錯誤提示
    
    3. attributes() - 需要自訂欄位名稱時
       - 中文欄位名稱
       - 業務術語
       - 多語系支援
    
    4. prepareForValidation() - 需要預處理資料時
       - 清理資料（trim、strip_tags）
       - 格式化資料
       - 設定預設值
    
    5. after() - 需要複雜驗證邏輯時
       - 跨欄位驗證
       - 資料庫查詢驗證
       - 業務規則驗證
    
    生活化比喻：
    就像餐廳的服務流程，基本流程是固定的，但根據不同需求可以加入：
    - 會員驗證（authorize）
    - 特殊菜單說明（messages）
    - 客製化服務（prepareForValidation）
    - 額外檢查（after）
--}}

{{-- ========================= --}}
{{-- # 10. 手動建立 Validator（Validator::make） --}}
{{-- ========================= --}}

{{--
    ## 基本用法
    use Illuminate\Support\Facades\Validator;
    $validator = Validator::make($data, $rules, $messages = [], $customAttributes = []);
    // $data：要驗證的資料（如 $request->all()）
    // $rules：驗證規則
    // $messages：自訂錯誤訊息（可省略）
    // $customAttributes：自訂欄位名稱（可省略）

    if ($validator->fails()) {
        // 驗證失敗，重導回前頁並帶錯誤訊息與輸入資料
        return redirect('/post/create')
            ->withErrors($validator)
            ->withInput();
    }
    // 取得所有驗證通過的資料
    $validated = $validator->validated();
    // 只取部分欄位
    $only = $validator->safe()->only(['title', 'body']);
    // 排除部分欄位
    $except = $validator->safe()->except(['slug']);
--}}

{{--
    ## 停止於第一個錯誤
    $validator->stopOnFirstFailure();
    if ($validator->fails()) { ... }
--}}

{{--
    ## 自動重導（與 request->validate 類似）
    Validator::make($data, $rules)->validate();
    // 驗證失敗自動重導或回傳 JSON
    // validateWithBag('bagName') 可指定 error bag
--}}

{{--
    ## Named Error Bag
    return redirect('/register')->withErrors($validator, 'login');
    // view 取用：$errors->login->first('email')
--}}

{{--
    ## 自訂錯誤訊息與屬性名稱
    $messages = [
        'required' => '此欄位必填',
        'email.required' => '請輸入 Email',
    ];
    $attributes = [
        'email' => '電子郵件',
    ];
    $validator = Validator::make($data, $rules, $messages, $attributes);
--}}

{{--
    ## after 方法（進階驗證）
    $validator->after(function ($validator) {
        if (/* 條件 */) {
            $validator->errors()->add('field', '自訂錯誤訊息');
        }
    });
    // 也可傳陣列，支援 invokable class
    $validator->after([
        new ValidateUserStatus,
        new ValidateShippingTime,
        function ($validator) { /* ... */ },
    ]);
--}}

{{--
    ## 生活化比喻
    Validator::make 就像「臨時驗證小隊」，你可以隨時召集他們來幫你檢查資料，並根據結果決定要不要讓資料通過。
--}} 

{{-- ========================= --}}
{{-- # 9.3 Form Request 工作流程與自動驗證機制 --}}
{{-- ========================= --}}

{{--
    ## Form Request 完整工作流程
    
    ### 1. Form Request 先定義驗證規則
    // StorePostRequest.php
    class StorePostRequest extends FormRequest
    {
        public function rules()
        {
            return [
                'title' => 'required|string|max:255',
                'body' => 'required|string',
            ];
        }
    }
    
    ### 2. Controller 注入時自動執行驗證
    // Controller
    public function store(StorePostRequest $request)  // 注入時自動執行驗證
    {
        // 如果驗證失敗，不會執行到這裡
        // 如果驗證成功，才會執行到這裡
    }
    
    ### 3. Controller 只負責取得驗證過的資料
    // Controller 中只需要這些方法：
    $validated = $request->validated();        // 取得所有驗證過的資料
    $only = $request->safe()->only(['title']); // 只取部分欄位
    $except = $request->safe()->except(['id']); // 排除部分欄位
    
    ## 自動驗證的詳細過程
    
    ### 步驟一：用戶發送請求
    POST /posts
    {
        "title": "我的文章",
        "body": "文章內容"
    }
    
    ### 步驟二：Laravel 自動執行 StorePostRequest 的驗證
    - 檢查 title 是否為必填字串且不超過 255 字元
    - 檢查 body 是否為必填字串
    
    ### 步驟三：驗證失敗的處理
    - 自動重導回前頁
    - 顯示錯誤訊息
    - Controller 方法不會執行
    
    ### 步驟四：驗證成功的處理
    - 執行 Controller 方法
    - 可以直接使用 validated() 取得安全資料
    
    ## 生活化比喻
    
    ### Form Request 就像「安檢門」
    - 先設定檢查規則（什麼可以帶，什麼不能帶）
    - 每個人經過時自動檢查
    - 通過的人才能進入，沒通過的會被攔截
    
    ### Controller 就像「服務台」
    - 只服務通過安檢的人
    - 不需要再檢查，直接處理需求
    - 只需要問「你要什麼服務」
    
    ## 依賴注入 + 自動驗證機制
    
    ### 核心概念
    - Form Request 定義驗證規則
    - Controller 注入時自動執行驗證
    - Controller 方法只處理業務邏輯和取得資料
    
    ### 優勢
    1. **程式碼分離**：驗證邏輯與業務邏輯分開
    2. **自動化**：不需要手動呼叫驗證
    3. **安全性**：確保只有驗證過的資料進入 Controller
    4. **可重用性**：同一個 Form Request 可以在多個 Controller 使用
    5. **可測試性**：驗證邏輯可以獨立測試
    
    ## 實際範例對比
    
    ### 舊方式（手動驗證）
    public function store(Request $request)
    {
        // 手動驗證
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
        
        // 業務邏輯
        Post::create($validated);
    }
    
    ### 新方式（Form Request）
    public function store(StorePostRequest $request)  // 自動驗證
    {
        // 直接取得驗證過的資料
        $validated = $request->validated();
        
        // 業務邏輯
        Post::create($validated);
    }
    
    ## 總結
    
    Form Request 是 Laravel 的「依賴注入 + 自動驗證」機制：
    - **定義階段**：在 Form Request 中設定驗證規則
    - **注入階段**：Controller 注入時自動執行驗證
    - **使用階段**：Controller 只處理業務邏輯和取得安全資料
    
    這樣的設計讓程式碼更乾淨、更安全、更易維護！
--}}

{{-- ========================= --}}
{{-- # 11. 驗證後資料與錯誤訊息操作 --}}
{{-- ========================= --}}

{{--
    ## 取得驗證通過的資料
    // Form Request 或 Validator 實例皆可：
    $validated = $request->validated();
    $validated = $validator->validated();
    // 取得部分欄位
    // safe() 會回傳一個 ValidatedInput 物件，包含所有通過驗證的資料。
    $only = $request->safe()->only(['name', 'email']);
    $except = $request->safe()->except(['name', 'email']);
    $all = $request->safe()->all();
    // safe() 回傳 Illuminate\Support\ValidatedInput，可迭代、可陣列存取
    foreach ($request->safe() as $key => $value) { /* ... */ }
    $email = $request->safe()['email'];
    // merge 合併額外欄位
    $merged = $request->safe()->merge(['name' => 'Taylor Otwell']);
    // collect 轉為 Collection
    $collection = $request->safe()->collect();
--}}

{{--
    ## 錯誤訊息操作（MessageBag）
    $errors = $validator->errors(); // 或 view 內 $errors
    $errors->first('email'); // 取單一欄位第一個錯誤
    $errors->get('email'); // 取欄位所有錯誤（陣列）
    $errors->get('attachments.*'); // 陣列欄位所有錯誤
    $errors->all(); // 所有欄位所有錯誤
    $errors->has('email'); // 判斷欄位是否有錯誤
--}}

{{--
    ## 語系檔自訂錯誤訊息
    1. 產生語系檔：php artisan lang:publish
    2. 編輯 resources/lang/zh-TW/validation.php
    3. custom 區塊可針對欄位+規則自訂訊息：
    'custom' => [
        'email' => [
            'required' => '請輸入 Email',
            'max' => 'Email 太長！'
        ],
    ],
    4. attributes 區塊可自訂欄位名稱：
    'attributes' => [
        'email' => '電子郵件',
    ],
    5. values 區塊可自訂規則值顯示：
    'values' => [
        'payment_type' => [
            'cc' => '信用卡'
        ],
    ],
    // 生活化比喻：語系檔就像「錯誤訊息翻譯字典」，可讓提示語更貼近用戶。
--}}

{{-- ========================= --}}
{{-- # 12. 常用驗證規則總覽（簡潔版，適合快速查閱） --}}
{{-- ========================= --}}

{{--
    本區塊適合需要快速查找規則名稱、分類、常見用途時使用。
    分類：
    - 布林類：accepted, accepted_if, boolean, declined, declined_if
    - 字串類：active_url, alpha, alpha_dash, alpha_num, ascii, confirmed, current_password, different, doesnt_start_with, doesnt_end_with, email, ends_with, enum, hex_color, in, ip, json, lowercase, mac_address, max, min, not_in, regex, not_regex, same, size, starts_with, string, uppercase, url, ulid, uuid
    - 數字類：between, decimal, digits, digits_between, gt, gte, integer, lt, lte, max, max_digits, min, min_digits, multiple_of, numeric, same, size
    - 陣列類：array, between, contains, distinct, in_array, in_array_keys, list, max, min, size
    - 日期類：after, after_or_equal, before, before_or_equal, date, date_equals, date_format, different, timezone
    - 檔案類：between, dimensions, extensions, file, image, max, mimes, mimetypes, size
    - 資料庫類：exists, unique
    - 工具類：any_of, bail, exclude, exclude_if, exclude_unless, exclude_with, exclude_without, filled, missing, missing_if, missing_unless, missing_with, missing_with_all, nullable, present, present_if, present_unless, present_with, present_with_all, prohibited, prohibited_if, prohibited_if_accepted, prohibited_if_declined, prohibited_unless, prohibits, required, required_if, required_if_accepted, required_if_declined, required_unless, required_with, required_with_all, required_without, required_without_all, required_array_keys, sometimes
    常見用途：
    - accepted/accepted_if：同意條款
    - active_url：網址格式
    - after/before：日期比較
    - alpha/alpha_dash/alpha_num：字母/數字/底線/破折號
    - array：陣列
    - bail：遇到第一個錯誤就停止
    - between/min/max/size：長度、數值、陣列、檔案大小
    - boolean：布林值
    - confirmed：密碼確認
    - contains：陣列包含
    - current_password：驗證密碼
    - date/date_equals/date_format：日期格式
    - decimal：小數點
    - declined/declined_if：拒絕
    - different/same：不同/相同
    - digits/digits_between：數字長度
    - distinct：陣列唯一
    - email：Email 格式
    - ends_with/starts_with：字串開頭/結尾
    - enum：enum 類型
    - exists/unique：資料庫存在/唯一
    - exclude/exclude_if/...：條件排除
    - file/image：檔案/圖片
    - filled：有值時必驗證
    - gt/gte/lt/lte：大於/小於/等於
    - in/not_in：指定值
    - integer/numeric：整數/數字
    - json：JSON 格式
    - list：連續索引陣列
    - mac_address/ip/ipv4/ipv6：網路格式
    - mimes/mimetypes/extensions：檔案副檔名/MIME
    - missing/present：必須（不）存在
    - multiple_of：倍數
    - nullable：可為 null
    - prohibited/prohibits：禁止/互斥
    - regex/not_regex：正規表達式
    - required/required_if/...：必填/條件必填
    - sometimes：有出現才驗證
    - string：字串
    - timezone：時區
    - ulid/uuid：ULID/UUID
    - url：網址格式
--}}

{{-- ========================= --}}
{{-- # 12-1. 常用驗證規則總覽（詳細版，適合深入學習） --}}
{{-- ========================= --}}

{{--
    boolean
    公式：'field' => 'boolean'
    說明：可轉為布林值（true/false/1/0/"1"/"0"）。

    declined
    公式：'field' => 'declined'
    說明：必須為 no/off/0/false。

    declined_if
    公式：'field' => 'declined_if:anotherfield,value,...'
    說明：若另一欄位為指定值時，必須為 declined。

    ## 字串類
    alpha
    公式：'field' => 'alpha' 或 'alpha:ascii'
    說明：僅允許字母（可選 ascii 限制）。

    alpha_dash
    公式：'field' => 'alpha_dash' 或 'alpha_dash:ascii'
    說明：僅允許字母、數字、底線、破折號。

    alpha_num
    公式：'field' => 'alpha_num' 或 'alpha_num:ascii'
    說明：僅允許字母、數字。

    confirmed
    公式：'password' => 'confirmed'
    說明：需有 password_confirmation 欄位且值相同。

    email
    公式：'email' => 'email' 或 'email:rfc,dns'
    進階：Rule::email()->rfcCompliant()->validateMxRecord()
    說明：Email 格式，可加 rfc/dns/spoof/strict。

    ends_with
    公式：'field' => 'ends_with:foo,bar'
    說明：必須以 foo 或 bar 結尾。

    in
    公式：'field' => 'in:foo,bar'
    進階：Rule::in(['foo','bar'])
    說明：必須在指定值內。

    not_in
    公式：'field' => 'not_in:foo,bar'
    進階：Rule::notIn(['foo','bar'])
    說明：不能在指定值內。

    max/min/size
    公式：'field' => 'max:10|min:3|size:5'
    說明：字串長度、陣列數量、數值大小、檔案大小。

    regex/not_regex
    公式：'field' => 'regex:/pattern/'
    說明：正規表達式。

    required
    公式：'field' => 'required'
    說明：必填。

    same/different
    公式：'field' => 'same:other' / 'different:other'
    說明：必須與 other 欄位相同/不同。

    starts_with
    公式：'field' => 'starts_with:foo,bar'
    說明：必須以 foo 或 bar 開頭。

    string
    公式：'field' => 'string'
    說明：必須為字串。

    url
    公式：'field' => 'url' 或 'url:http,https'
    說明：網址格式，可指定協定。

    ## 數字類
    between
    公式：'field' => 'between:min,max'
    說明：數值、字串、陣列、檔案大小範圍。

    digits/digits_between
    公式：'field' => 'digits:5' / 'digits_between:3,6'
    說明：數字長度。

    integer/numeric
    公式：'field' => 'integer' / 'numeric'
    說明：整數/數字。

    max/min
    公式：'field' => 'max:10|min:3'
    說明：數值、字串、陣列、檔案大小。

    multiple_of
    公式：'field' => 'multiple_of:5'
    說明：必須為 5 的倍數。

    ## 陣列類
    array
    公式：'field' => 'array'
    說明：必須為陣列。

    contains
    公式：Rule::contains(['foo','bar'])
    說明：陣列必須包含指定值。

    distinct
    公式：'field.*' => 'distinct' 或 'distinct:strict'
    說明：陣列元素不可重複。

    in_array
    公式：'field' => 'in_array:other.*'
    說明：必須存在於另一陣列欄位。

    list
    公式：'field' => 'list'
    說明：必須為連續索引陣列。

    ## 日期類
    after/before
    公式：'field' => 'after:date' / 'before:date'
    進階：Rule::date()->after(today()->addDays(7))
    說明：必須在指定日期之後/之前。

    date/date_equals/date_format
    公式：'field' => 'date' / 'date_equals:2024-01-01' / 'date_format:Y-m-d'
    說明：日期格式驗證。

    timezone
    公式：'field' => 'timezone' / 'timezone:all'
    說明：必須為合法時區。

    ## 檔案類
    file/image
    公式：'field' => 'file' / 'image' / 'image:allow_svg'
    說明：必須為檔案/圖片。

    mimes/mimetypes/extensions
    公式：'field' => 'mimes:jpg,png' / 'mimetypes:image/jpeg' / 'extensions:jpg,png'
    說明：檔案副檔名/MIME 類型。

    dimensions
    公式：Rule::dimensions()->maxWidth(1000)->ratio(3/2)
    說明：圖片尺寸、比例。

    ## 資料庫類
    exists
    公式：'field' => 'exists:table,column'
    進階：Rule::exists('table','column')->where(fn($q)=>...)
    說明：資料庫存在。

    unique
    公式：'field' => 'unique:table,column'
    進階：Rule::unique('users')->ignore($user->id)
    說明：資料庫唯一。

    ## 工具類
    bail
    公式：'field' => 'bail'
    說明：遇到第一個錯誤就停止該欄位驗證。

    exclude/exclude_if/exclude_unless...
    公式：Rule::excludeIf(fn()=>...)
    說明：條件排除欄位。

    filled
    公式：'field' => 'filled'
    說明：有值時必須通過驗證。

    nullable
    公式：'field' => 'nullable'
    說明：可為 null。

    present
    公式：'field' => 'present'
    說明：必須存在於輸入資料。

    prohibited/prohibits
    公式：'field' => 'prohibited' / 'prohibits:other'
    進階：Rule::prohibitedIf(fn()=>...)
    說明：禁止出現/互斥。

    required/required_if/required_unless...
    公式：'field' => 'required_if:other,value' / Rule::requiredIf(fn()=>...)
    說明：必填、條件必填。

    sometimes
    公式：'field' => 'sometimes'
    說明：有出現才驗證。

    accepted_if
    公式：'field' => 'accepted_if:anotherfield,value,...'
    說明：若另一欄位為指定值時，必須為 accepted。

    ## 生活化比喻
    驗證規則就像「表單守門員」，每個規則負責一種檢查，組合起來能守住資料品質。
--}}

{{-- ========================= --}}
{{-- # 13. 條件式驗證規則與進階用法 --}}
{{-- ========================= --}}

{{--
    ## exclude_if / exclude_unless
    語法：
    'field' => 'exclude_if:anotherfield,value|required|其他規則'
    'field' => 'exclude_unless:anotherfield,value|required|其他規則'
    說明：
    - exclude_if：若 anotherfield 為 value，該欄位會被排除（不驗證、不回傳）
    - exclude_unless：只有 anotherfield 為 value 時才驗證該欄位
    - 常用於「有預約才驗證日期」等情境
    範例：
    $validator = Validator::make($data, [
        'has_appointment' => 'required|boolean',
        'appointment_date' => 'exclude_if:has_appointment,false|required|date',
        'doctor_name' => 'exclude_if:has_appointment,false|required|string',
    ]);
    // 或
    $validator = Validator::make($data, [
        'has_appointment' => 'required|boolean',
        'appointment_date' => 'exclude_unless:has_appointment,true|required|date',
        'doctor_name' => 'exclude_unless:has_appointment,true|required|string',
    ]);
--}}

{{--
    ## sometimes 規則
    語法：'field' => 'sometimes|required|其他規則'
    說明：只有該欄位有出現在輸入資料時才驗證（常用於部分欄位可選填的情境）
    範例：
    $validator = Validator::make($data, [
        'email' => 'sometimes|required|email',
    ]);
--}}

{{--
    ## Validator::sometimes 方法（進階條件式驗證）
    語法：
    $validator->sometimes('field', 'required|max:500', function (Fluent $input) {
        return $input->games >= 100;
    });
    說明：
    - 可根據 closure 條件動態加上驗證規則
    - $input 為 Fluent，可存取所有輸入資料
    - 支援多欄位、陣列欄位
    - 適合複雜條件（如「遊戲數超過 100 才必填原因」）
    範例：
    $validator->sometimes(['reason', 'cost'], 'required', function (Fluent $input) {
        return $input->games >= 100;
    });
    // 陣列欄位進階：
    $validator->sometimes('channels.*.address', 'email', function (Fluent $input, Fluent $item) {
        return $item->type === 'email';
    });
    $validator->sometimes('channels.*.address', 'url', function (Fluent $input, Fluent $item) {
        return $item->type !== 'email';
    });
--}} 


{{-- ========================= --}}
{{-- # 13-1. 陣列欄位進階用法 --}}
{{-- ========================= --}}

{{--
    // 陣列欄位進階：根據陣列中每個項目的類型，動態決定驗證規則
    // 前端資料範例：
    // {
    //     "channels": [
    //         {"type": "email", "address": "user@example.com"},
    //         {"type": "website", "address": "https://example.com"},
    //         {"type": "phone", "address": "https://telegram.me/user"}
    //     ]
    // }
    
    // 當 type 是 "email" 時，address 欄位必須是 email 格式
    $validator->sometimes('channels.*.address', 'email', function (Fluent $input, Fluent $item) {
        return $item->type === 'email';  // 條件：如果這個項目的 type 是 email
    });
    
    // 當 type 不是 "email" 時，address 欄位必須是 URL 格式
    $validator->sometimes('channels.*.address', 'url', function (Fluent $input, Fluent $item) {
        return $item->type !== 'email';  // 條件：如果這個項目的 type 不是 email
    });
    
    // 驗證結果：
    // - channels[0].address 會驗證 email 格式（因為 type="email"）
    // - channels[1].address 會驗證 URL 格式（因為 type="website"）
    // - channels[2].address 會驗證 URL 格式（因為 type="phone"）
--}} 

{{-- ========================= --}}
{{-- # 13-1. 條件式驗證 Controller 實作範例 --}}
{{-- ========================= --}}

{{--
    // app/Http/Controllers/ConditionalValidatorDemoController.php
    namespace App\Http\Controllers;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\View\View;
    use Illuminate\Http\RedirectResponse;

    class ConditionalValidatorDemoController extends Controller
    {
        // 顯示表單
        public function create(): View
        {
            return view('demo.validator.conditional');
        }
        // 處理表單送出
        public function store(Request $request): RedirectResponse
        {
            $validator = Validator::make($request->all(), [
                'has_appointment' => 'required|boolean',
                'appointment_date' => 'exclude_if:has_appointment,false|required|date',
                'doctor_name' => 'exclude_if:has_appointment,false|required|string',
                'email' => 'sometimes|required|email',
            ]);
            // 進階條件式驗證：遊戲數超過 100 才必填 reason
            $validator->sometimes('reason', 'required|max:500', function ($input) {
                return isset($input->games) && $input->games >= 100;
            });
            if ($validator->fails()) {
                return redirect()->route('conditional.validator.create')
                    ->withErrors($validator)
                    ->withInput();
            }
            return redirect()->route('conditional.validator.create')->with('success', '條件式驗證通過！');
        }
    }
--}}

{{-- ========================= --}}
{{-- # 13-2. 條件式驗證 View 實作範例 --}}
{{-- ========================= --}}

{{--
    // resources/views/demo/validator/conditional.blade.php
    <!DOCTYPE html>
    <html lang="zh-Hant">
    <head>
        <meta charset="UTF-8">
        <title>條件式驗證教學範例</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
    <body class="p-4">
        <h1>條件式驗證教學範例</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ url('/demo/validator/conditional') }}">
            @csrf
            <div class="mb-3">
                <label for="has_appointment" class="form-label">有預約？</label>
                <select name="has_appointment" id="has_appointment" class="form-select">
                    <option value="1" @if(old('has_appointment')==='1') selected @endif>是</option>
                    <option value="0" @if(old('has_appointment')==='0') selected @endif>否</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="appointment_date" class="form-label">預約日期</label>
                <input type="date" name="appointment_date" id="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" value="{{ old('appointment_date') }}">
                @error('appointment_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="doctor_name" class="form-label">醫師姓名</label>
                <input type="text" name="doctor_name" id="doctor_name" class="form-control @error('doctor_name') is-invalid @enderror" value="{{ old('doctor_name') }}">
                @error('doctor_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email（有填才驗證）</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="games" class="form-label">遊戲數（超過100才必填原因）</label>
                <input type="number" name="games" id="games" class="form-control @error('games') is-invalid @enderror" value="{{ old('games') }}">
                @error('games')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="reason" class="form-label">原因（遊戲數超過100才必填）</label>
                <input type="text" name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason') }}">
                @error('reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">送出</button>
        </form>
    </body>
    </html>
--}}

{{--
    生活化比喻：條件式驗證就像「智慧守門員」，會根據現場狀況決定要不要檢查某些資料，讓表單驗證更彈性、更貼近實際需求。
--}} 