# *Laravel Validation 驗證教學*

---

## 1. **驗證簡介與快速上手**

- *Laravel 提供多種驗證資料的方式*，最常用的是 `Request` 實例的 `validate()` 方法。
- 也支援*自訂 Form Request*、*手動 Validator* 等進階用法。
- *驗證規則*非常豐富，包含格式、唯一性、長度、巢狀資料等。
- *生活化比喻*： 驗證就像「資料的守門員」，只有合格的資料才能進入系統。

---

## 2. **路由與 Controller 範例**

```php
// routes/web.php
use App\Http\Controllers\PostController;
Route::get('/post/create', [PostController::class, 'create']);
Route::post('/post', [PostController::class, 'store']);
```
- GET 顯示表單，POST 儲存資料。

```php
// app/Http/Controllers/PostController.php
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller {
    // 顯示表單
    public function create(): View {
        return view('post.create');
    }
    // 儲存資料
    public function store(Request $request): RedirectResponse {
        // 驗證與儲存...
        $post = /** ... */;
        return to_route('post.show', ['post' => $post->id]);
    }
}
```

---

## 3. **驗證規則寫法**

- *字串寫法*
  ```php
  // 以字串方式撰寫規則，規則用 | 分隔
  // 'title' 欄位必填、唯一且最大 255 字元
  // 'body' 欄位必填
  $request->validate([
      'title' => 'required|unique:posts|max:255',
      'body' => 'required',
  ]);
  ```
- *陣列寫法*
  ```php
  // 以陣列方式撰寫規則，適合條件式、複雜規則
  $request->validate([
      'title' => ['required', 'unique:posts', 'max:255'], // 多個規則分開寫
      'body' => ['required'],
  ]);
  ```
- *指定 error bag*
  ```php
  // validateWithBag 可指定錯誤訊息存入特定 error bag，適合多表單同頁時分開顯示錯誤
  $request->validateWithBag('post', [
      'title' => ['required', 'unique:posts', 'max:255'],
      'body' => ['required'],
  ]);
  ```
- *bail*：遇到第一個錯誤就停止該欄位後續驗證
  ```php
  // bail：只要遇到第一個錯誤就停止該欄位後續驗證，適合欄位有依賴關係時
  $request->validate([
      'title' => 'bail|required|unique:posts|max:255',
      'body' => 'required',
  ]);
  ```
- *巢狀欄位（dot 語法）*
  ```php
  // 巢狀資料可用 dot 語法驗證，例如 author.name
  $request->validate([
      'author.name' => 'required',
      'author.description' => 'required',
  ]);
  ```
- *欄位名稱含點號需跳脫*
  ```php
  // 欄位名稱本身有點號時需用 \. 跳脫
  $request->validate([
      'v1\\.0' => 'required',
  ]);
  ```

---

## 4. **錯誤訊息顯示與 $errors 變數、@error 指令**

- *驗證失敗時* 會自動重導 *回前頁* ，錯誤訊息與輸入資料 *自動存入 session*。
- `$errors` 變數（`Illuminate\Support\MessageBag`）*自動注入所有 view，可直接使用*：

```html
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

- **@error 指令** 可顯示 *單一欄位錯誤*：
```html
<input name="title" class="@error('title') is-invalid @enderror">
@error('title')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror
// named error bag
@error('title', 'post') ... @enderror
```

- *補充*： `$errors` 變數由 `ShareErrorsFromSession` middleware 注入，永遠可用。

---

## 5. **表單回填 old()**

- *驗證失敗重導時，所有輸入資料自動存入 session*。
- 可用 `old()` 輔助函式回填：

```html
<input type="text" name="title" value="{{ old('title') }}">
```
- 也可用 `$request->old('title')` 於 Controller 取得。

---

## 6. **XHR/JSON 驗證回應格式**

- *XHR（AJAX）請求驗證失敗時，Laravel 會自動回傳 422 狀態碼與 JSON 錯誤訊息*。
- 範例：
```json
{
    "message": "The team name must be a string. (and 4 more errors)",
    "errors": {
        "team_name": [
            "The team name must be a string.",
            "The team name must be at least 1 characters."
        ],
        "authorization.role": [
            "The selected authorization.role is invalid."
        ],
        "users.0.email": [
            "The users.0.email field is required."
        ],
        "users.2.email": [
            "The users.2.email must be a valid email address."
        ]
    }
}
```
- *補充*： 巢狀欄位會自動轉為 dot 語法。

---

## 7. **Optional/Nullable 欄位**

- Laravel 預設有 `TrimStrings`、`ConvertEmptyStringsToNull` middleware。
- 欄位可為 null 時，請加 `nullable`：
  ```php
  $request->validate([
      'publish_at' => 'nullable|date',
  ]);
  ```
- *生活化比喻*： nullable 就像「可留空欄位」，optional 就像「可不填欄位」。

---

## 8. **Form Request 驗證（進階）**

### *Form Request 與 Controller 的互動*
- **Form Request** 是 Laravel 專為 *表單驗證* 與 *授權封裝* 的請求類別，繼承自 `Illuminate\Foundation\Http\FormRequest`。
- *生活化比喻*： Form Request 就像「專屬驗證小幫手」，Controller 只需專心處理業務邏輯。

#### **典型開發與互動流程**
1. *建立 Form Request 類別*

   `php artisan make:request StoreUserRequest`

   - 類別會放在 `app/Http/Requests/` 目錄下。
   
2. *實作驗證與授權邏輯*
   - 必要方法：`rules()`、`authorize()`
   - 可選方法：`messages()`、`attributes()`、`prepareForValidation()`、`after()`、`passedValidation()`

3. *Controller 型別提示該 Request*
   - 只要在 Controller 方法參數型別提示 Form Request，Laravel 會 **自動注入** 並執行驗證/授權：
   ```php
   use App\Http\Requests\StoreUserRequest;
   public function store(StoreUserRequest $request) {
       // ...
   }
   ```
4. *自動驗證與授權*
   - 進入 Controller **前**，會自動執行 rules() 驗證與 authorize() 授權
   - **驗證失敗** 自動重導回前頁，*錯誤訊息* 自動帶入 session
   - **授權失敗** 自動回傳 403，Controller 不會執行

5. *取得驗證通過的資料*
   - 只會包含 **rules()** 通過的欄位
   ```php
   $validated = $request->validated();
   // 只取部分欄位
   $safe = $request->safe()->only(['name', 'email']);
   ```
6.**錯誤處理與自訂*
   - 錯誤訊息可用 `$errors` 變數在 Blade 顯示
   - 可自訂 messages()、attributes()、prepareForValidation()、after()、passedValidation() 等方法

#### **Controller 與 Form Request 範例**
```php
// app/Http/Requests/StoreUserRequest.php
class StoreUserRequest extends FormRequest {
    public function authorize() { 
      return true; 
      }

    public function rules() {
        return [
            'name' => 'required',
            'email' => 'required|email',
        ];
    }
    // 可自訂 messages、attributes、prepareForValidation ...
}

// app/Http/Controllers/UserController.php
public function store(StoreUserRequest $request) {
    $validated = $request->validated();
    // 使用 $validated 儲存資料
}
```

#### **常見互動重點**
- Controller 不需再寫 `$request->validate()`，一切自動處理
- *驗證失敗* 自動重導，錯誤訊息自動帶到 view
- *授權失敗* 自動回傳 403
- 可用 `$request->validated()` 取得乾淨資料
- 可用 `$request->safe()->only([...])` 取部分欄位
- 可自訂錯誤訊息、欄位名稱、驗證前/後處理

---

### *rules 方法*

  - 此方法用來定義「**每個欄位的驗證規則**」。
  - 回傳一個陣列，key 為**欄位名稱**，value 為**規則字串或陣列**。
  - 常見規則：required、email、max、unique、exists...（可複合使用）
  - 支援陣列語法、Rule 類別、條件式規則等進階用法。
  - 實務範例：
    - `return ['email' => 'required|email', 'age' => 'nullable|integer|min:18'];`
    - `return ['status' => [Rule::in(['draft', 'published'])]];`

```php
public function rules(): array {
    return [
        'title' => 'required|unique:posts|max:255',
        'body' => 'required',
    ];
}
```

### *authorize 方法*

  - 這個方法 **不是用來檢查 CSRF** ，而是用來判斷「**目前用戶是否有權限執行這個請求**」。
  - 若回傳 false，Laravel 會自動回傳 403 Forbidden，controller 不會執行。
  - 常用於：只有管理員、本人、特定角色才能送出表單。
  - 範例：`return auth()->user()->id === $this->route('id');`

  - **預設 true**：所有人都能通過授權檢查，無權限限制
  - 若需權限檢查，可改寫如下：

  - 例如：只有管理員能通過
  - return auth()->user()->is_admin;

  - 例如：只有本人能修改自己的資料
  - return auth()->id() === $this->route('user');

```php
public function authorize(): bool {
    return true;
}
```

### *messages 方法（自訂錯誤訊息）*

  - 可自訂 **每個欄位+規則** 的錯誤訊息。
  - key 格式為「欄位.規則」，如 `title.required`。
  - 支援 :attribute、:min、:max 等 placeholder。
  - 適合用於多語系、客製化友善訊息。

  - **'title.required'**：當 title 欄位未填時，顯示「標題必填」
  - **'body.required'**：當 body 欄位未填時，顯示「內容必填」
  - 你也可以用 :attribute、:min、:max 等 placeholder 讓訊息更動態
  - 例如 'title.max' => '標題不可超過 :max 字元'
```php
public function messages(): array {
    return [
        'title.required' => '標題必填',
        'body.required' => '內容必填',
    ];
}
```

### *attributes 方法（自訂欄位名稱）*

  - 可自訂**錯誤訊息中**，顯示的欄位名稱。
  - 例如將 email 顯示為「電子郵件」。
  - 適合用於多語系、欄位名稱較技術化時。

```php
public function attributes(): array {
    return [
        'email' => '電子郵件',
    ];
}
```

### *prepareForValidation 方法（驗證前預處理）*

  - 在**驗證規則執行前**，先對輸入資料做**預處理（如格式化、合併欄位）**。
  - 常用於自動補齊欄位、轉換格式、去除空白等。
  - 例如自動產生 slug、合併多個欄位為一個。

```php
protected function prepareForValidation(): void {
    $this->merge([
        'slug' => Str::slug($this->slug),
    ]);
}
```

### *after 方法（進階驗證）*

  - 可在**所有規則驗證完後**，進行進階/跨欄位驗證。
  - 回傳一個 closure 陣列，每個 closure 可自訂錯誤訊息。
  - 適合複雜邏輯、需存取多欄位、外部資源等情境。
  - 也可回傳 invokable class。

```php
public function after(): array {
    return [
        function (Validator $validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Something is wrong with this field!');
            }
        }
    ];
}
```

### *passedValidation 方法（驗證通過後處理）*

  - 當所有**驗證通過後**，自動執行。
  - 可用於進一步處理資料、格式轉換、補充欄位等。
  - 例如自動覆寫某些欄位、加密密碼等。

```php
protected function passedValidation(): void {
    $this->replace(['name' => 'Taylor']);
}
```

### *自訂驗證失敗後的 redirect*

  - 可自訂**驗證失敗時**，要重導的路徑或 route 名稱。
  - 適合多表單、多步驟流程。

```php
protected $redirect = '/dashboard';
// 或
protected $redirectRoute = 'dashboard';
```

### *停止於第一個驗證失敗*

  - 預設會驗證所有欄位，`stopOnFirstFailure()` 可讓驗證遇到第一個錯誤就停止。
  - 適合表單欄位有依賴關係、只需回報一個錯誤時。

```php
if ($validator->stopOnFirstFailure()->fails()) {
    // ...
}
```

### *自動重導與 validate 方法*

- **validate()**：自動驗證資料，失敗時自動重導回前頁並帶入錯誤訊息與 old input
- 適合 Controller 內快速驗證，XHR/JSON **請求失敗時**，自動回傳 422 JSON
- **validateWithBag('post')**：*指定錯誤訊息*存入特定 error bag，適合多表單同頁時分開顯示錯誤
```php

Validator::make($request->all(), [
    // 'title' 欄位必填、唯一且最大 255 字元，未通過時會自動產生錯誤訊息
    'title' => 'required|unique:posts|max:255',
    // 'body' 欄位必填
    'body' => 'required',
])->validate();
// 失敗自動重導或回傳 JSON，錯誤訊息自動存入 session，old input 也自動帶回
// validateWithBag('post') 可指定錯誤訊息存入 $errors->post，適合多表單同頁時分開顯示錯誤
```

### *Named Error Bags（多表單錯誤命名）*

- **多表單同頁時**，可用 named error bag 區分錯誤訊息
- **withErrors($validator, 'login')** 會將錯誤訊息存入 $errors->login
```php

// 例如：登入表單驗證失敗時
return redirect('/login')->withErrors($validator, 'login');
// 例如：註冊表單驗證失敗時
return redirect('/register')->withErrors($validator, 'register');
```
```php
// 也可用 validateWithBag 快速驗證並指定 error bag
$request->validateWithBag('register', [
    'name' => 'required',
    'email' => 'required|email',
    'password' => 'required|min:8',
]);
// 驗證失敗時，錯誤訊息會自動存到 $errors->register
`
```html
{{-- Blade 取用：$errors->login->first('email')，只顯示 login error bag 的 email 錯誤 --}}
@if ($errors->login->any())
    <div class="alert alert-danger">
        {{ $errors->login->first('email') }}
    </div>
@endif
{{-- 另一個表單的錯誤 --}}
@if ($errors->register->any())
    <div class="alert alert-danger">
        {{ $errors->register->first('email') }}
    </div>
@endif
// 預設 $errors 就是 $errors->default
```

### *自訂錯誤訊息（多種方式）*

- **Validator::make** *第三個參數*可直接傳自訂訊息陣列
- **'required' => '此欄位必填'**：所有 required 規則都顯示這句話
- 支援 :attribute、:other、:size、:min、:max、:input、:values 等 placeholder
```php

// 針對特定欄位+規則可用 dot 語法
$validator = Validator::make($input, $rules, [
    'required' => '此欄位必填',
]);
$messages = [
    // email 欄位未填時顯示「請輸入 Email！」
    'email.required' => '請輸入 Email！',
];
```

### *自訂屬性名稱與 values*

- **Validator::make** *第四個參數*可自訂欄位名稱顯示，錯誤訊息會自動帶入
```php

$validator = Validator::make($input, $rules, $messages, [
    // 將 email 欄位顯示為「電子郵件」
    'email' => '電子郵件',
]);
// 語系檔 values 區塊可自訂規則值顯示（如選單、狀態、類型等友善名稱）
'values' => [
    'payment_type' => [
        // 將 cc 顯示為「信用卡」
        'cc' => '信用卡'
    ],
],
```

### *進階 after 驗證*

- 可在驗證後再加自訂檢查，適合跨欄位、外部資源、複雜邏輯
```php

// 傳 closure，$validator->errors()->add('field', '訊息') 可手動加入錯誤
$validator->after(function ($validator) {
    // 若自訂條件不符，手動加入錯誤訊息
    if ($this->somethingElseIsInvalid()) {
        $validator->errors()->add('field', '這個欄位有問題！');
    }
});
// 也可傳 invokable class 陣列，適合複雜驗證
$validator->after([
    new ValidateUserStatus, // 可用 invokable class
    new ValidateShippingTime,
    function ($validator) { /* ... */ },
]);
```

### *取得驗證通過的資料*
```php
// validated() 取得所有通過驗證的資料（已過濾未通過欄位）
$validated = $validator->validated();
// safe() 取得 ValidatedInput，可用 only/except/all/merge/collect，適合進一步處理
$validated = $validator->safe()->only(['name', 'email']); // 只取指定欄位
$validated = $validator->safe()->except(['name', 'email']); // 排除指定欄位
$validated = $validator->safe()->all(); // 取全部
$merged = $validator->safe()->merge(['name' => 'Taylor']); // 合併新資料
$collection = $validator->safe()->collect(); // 轉為 Collection
// ValidatedInput 可迭代、可陣列存取
foreach ($validator->safe() as $key => $value) { /* ... */ }
$email = $validator->safe()['email'];
```

### *操作錯誤訊息 MessageBag*

- **$validator->errors()** 取得 MessageBag 實例，方便操作錯誤訊息
- 可取單一欄位第一個錯誤、所有錯誤、判斷欄位有無錯誤
- **Blade 內的 $errors 變數**，也是 MessageBag

```php
$errors = $validator->errors();
echo $errors->first('email'); // 取 email 欄位第一個錯誤
foreach ($errors->get('email') as $message) { /* ... */ } // 取 email 欄位所有錯誤
foreach ($errors->all() as $message) { /* ... */ } // 取所有欄位所有錯誤
if ($errors->has('email')) { /* ... */ } // 判斷 email 欄位有無錯誤
```

### *語系檔自訂錯誤訊息與屬性／值*

- 內建錯誤訊息在 `lang/{語系}/validation.php`
- 可用 `php artisan lang:publish` 產生語系檔，方便多語系維護
- **custom 區塊** 可針對 *欄位+規則* 自訂訊息，
  **attributes 區塊** 自訂 *欄位名稱*，
  **values 區塊** 自訂 *規則值顯示*

- 語系檔就像「*錯誤訊息翻譯字典*」，讓提示語更貼近用戶
```php

'custom' => [
    'email' => [
        // email 欄位未填時顯示「請輸入 Email」
        'required' => '請輸入 Email',
        // email 欄位過長時顯示「Email 太長！」
        'max' => 'Email 太長！'
    ],
],
'attributes' => [
    // 將 email 欄位顯示為「電子郵件」
    'email' => '電子郵件',
],
'values' => [
    'payment_type' => [
        // 將 cc 顯示為「信用卡」
        'cc' => '信用卡'
    ],
],
```
- **values 區塊應用**：有些驗證訊息會用到 *:value 佔位符*（如 required_if:payment_type,cc），可讓錯誤訊息顯示友善名稱
- 這種設計讓錯誤訊息**更貼近用戶語言**，避免顯示技術縮寫或代碼，適合用於選單、狀態、類型等欄位的友善顯示
- **生活化比喻**： 語系檔就像「錯誤訊息翻譯字典」，可讓提示語更貼近用戶。

### *語系檔 values 區塊的應用*
- 有些驗證訊息會用到 `:value` 佔位符，例如：
  ```php
  Validator::make($request->all(), [
      // 當 payment_type 欄位為 cc 時，credit_card_number 欄位必填
      'credit_card_number' => 'required_if:payment_type,cc'
  ]);
  ```
- 預設錯誤訊息會是：
  > The credit card number field is required when payment type is cc.

- 你可以在語系檔 `values` 區塊自訂顯示：
  ```php
  'values' => [
      'payment_type' => [
          // 將 cc 顯示為「credit card」
          'cc' => 'credit card'
      ],
  ],
  ```
- 這樣錯誤訊息就會變成：
  > The credit card number field is required when payment type is credit card.
- 
> **詳細註解**
  - `values` 區塊的設計目的是讓驗證訊息中的 `:value` 佔位符能顯示「友善名稱」而非技術縮寫或代碼。
  - 只要驗證訊息有 `:value` 佔位符，且 values 區塊有對應設定，Laravel 會自動將原本的值（如 cc）替換成你設定的顯示名稱（如 credit card）。
  - 這對於下拉選單、狀態、類型等欄位特別有用，能讓錯誤訊息更貼近用戶語言。
  - 常見用途：付款方式、狀態、角色、分類等欄位的錯誤提示。
  - 這種設計讓錯誤訊息更易懂、更專業，也方便多語系維護。

---

## 12. **驗證規則總覽**

> *本章節彙整 Laravel 所有內建驗證規則，依類型分組，並附上中文說明、範例與記憶小技巧。*

### *章節大綱*
- 12.1 布林類（Booleans）
- 12.2 字串類（Strings）
- 12.3 數字類（Numbers）
- 12.4 陣列類（Arrays）
- 12.5 日期類（Dates）
- 12.6 檔案類（Files）
- 12.7 資料庫類（Database）
- 12.8 工具/其他（Utilities）

---

### 12.1 *布林類（Booleans）*

- **accepted**
  - *欄位值必須是「同意」狀態*（yes、on、1、"1"、true、"true"）
  - 常用於「服務條款同意」勾選框
  - 範例：
    ```php
    'terms' => 'accepted'
    ```
  - *比喻*： 就像打勾同意才可送出表單

- **accepted_if:anotherfield,value,...**
  - *當另一欄位為指定值時，該欄位必須為「同意」*
  - 範例：
    ```php
    'terms' => 'accepted_if:role,admin'
    ```

- **boolean**
  - *欄位值必須可轉為布林值*（true、false、1、0、"1"、"0"）
  - 可加 `strict` 參數，僅接受 true/false：
    ```php
    'is_active' => 'boolean:strict'
    ```

- **declined**
  - *欄位值必須是「拒絕」狀態*（no、off、0、"0"、false、"false"）
  - 範例：
    ```php
    'newsletter' => 'declined'
    ```

- **declined_if:anotherfield,value,...**
  - *當另一欄位為指定值時，該欄位必須為「拒絕」*
  - 範例：
    ```php
    'newsletter' => 'declined_if:role,guest'
    ```

---

### 12.2 *字串類（Strings）*

- **active_url**
  - *欄位必須是有效的 URL，且主機名稱需有有效的 DNS A 或 AAAA 記錄*
  - 範例：
    ```php
    'website' => 'active_url'
    ```
  - *註解*： 只驗證 DNS，不驗證網址內容是否存在

- **alpha**
  - *欄位必須全部為字母（Unicode 字母，含各國語言）*
  - 可加 `ascii` 參數僅允許 a-z/A-Z：
    ```php
    'name' => 'alpha:ascii'
    ```

- **alpha_dash**
  - *欄位必須為字母、數字、破折號（-）、底線（_）*
  - 可加 `ascii` 參數僅允許英文與數字：
    ```php
    'username' => 'alpha_dash:ascii'
    ```

- **alpha_num**
  - *欄位必須為字母或數字（Unicode）*
  - 可加 `ascii` 參數僅允許英文與數字：
    ```php
    'code' => 'alpha_num:ascii'
    ```

- **ascii**
  - *欄位必須全部為 7-bit ASCII 字元*
  - 範例：
    ```php
    'slug' => 'ascii'
    ```

- **confirmed**
  - *欄位必須有一個「_confirmation」結尾的欄位且值相同*
  - 常用於密碼、Email 確認：
    ```php
    'password' => 'confirmed' // 需有 password_confirmation 欄位
    ```
  - 可自訂確認欄位名稱：
    ```php
    'username' => 'confirmed:repeat_username'
    ```

- **current_password**
  - *欄位必須與目前登入者密碼相符*
  - 可指定 guard：
    ```php
    'password' => 'current_password:api'
    ```

- **different:field**
  - *欄位值必須與指定欄位不同*
  - 範例：
    ```php
    'new_password' => 'different:old_password'
    ```

- **doesnt_start_with:foo,bar,...**
  - *欄位值不得以指定字串開頭*
  - 範例：
    ```php
    'slug' => 'doesnt_start_with:admin,sys'
    ```

- **doesnt_end_with:foo,bar,...**
  - *欄位值不得以指定字串結尾*
  - 範例：
    ```php
    'slug' => 'doesnt_end_with:tmp,log'
    ```

- **email**
  - *欄位必須為合法 Email 格式*
  - 可加驗 RFC、DNS、嚴格、過濾等：
    ```php
    'email' => 'email:rfc,dns'
    ```
  - *比喻*： 就像郵件地址必須正確才能寄達

- **ends_with:foo,bar,...**
  - *欄位值必須以指定字串結尾*
  - 範例：
    ```php
    'filename' => 'ends_with:.jpg,.png'
    ```

- **enum**
  - *欄位值必須為指定 Enum 類別的有效值*
  - 範例：
    ```php
    use App\Enums\Status;
    'status' => [Rule::enum(Status::class)]
    ```

- **hex_color**
  - *欄位必須為合法的 16 進位色碼*
  - 範例：
    ```php
    'color' => 'hex_color'
    ```

- **in:foo,bar,...**
  - *欄位值必須在指定清單內*
  - 範例：
    ```php
    'role' => 'in:admin,editor,user'
    ```

- **json**
  - *欄位必須為合法 JSON 字串*
  - 範例：
    ```php
    'meta' => 'json'
    ```

- **lowercase**
  - *欄位必須全為小寫*
  - 範例：
    ```php
    'tag' => 'lowercase'
    ```

- **mac_address**
  - *欄位必須為合法 MAC 位址*
  - 範例：
    ```php
    'device_mac' => 'mac_address'
    ```

- **not_in:foo,bar,...**
  - *欄位值不得在指定清單內*
  - 範例：
    ```php
    'username' => 'not_in:admin,root,sys'
    ```

- **not_regex:pattern**
  - *欄位值不得符合指定正則表達式*
  - 範例：
    ```php
    'username' => 'not_regex:/^admin_/'
    ```

- **regex:pattern**
  - *欄位值必須符合指定正則表達式*
  - 範例：
    ```php
    'phone' => 'regex:/^09[0-9]{8}$/'
    ```

- **size:value**
  - *字串長度必須等於 value*
  - 範例：
    ```php
    'code' => 'size:6'
    ```

- **starts_with:foo,bar,...**
  - *欄位值必須以指定字串開頭*
  - 範例：
    ```php
    'slug' => 'starts_with:prod,dev'
    ```

- **string**
  - *欄位必須為字串*
  - 若允許為 null，需加 nullable：
    ```php
    'nickname' => 'string|nullable'
    ```

- **uppercase**
  - *欄位必須全為大寫*
  - 範例：
    ```php
    'code' => 'uppercase'
    ```

- **url**
  - *欄位必須為合法 URL*
  - 可指定協定：
    ```php
    'homepage' => 'url:http,https'
    ```

- **ulid**
  - *欄位必須為合法 ULID*
  - 範例：
    ```php
    'order_id' => 'ulid'
    ```

- **uuid**
  - *欄位必須為合法 UUID（可指定版本）*
  - 範例：
    ```php
    'user_id' => 'uuid:4'
    ```

---

### 12.3 *數字類（Numbers）*

- **between:min,max**
  - *數值必須介於 min 與 max 之間（含邊界）*
  - 適用於字串長度、數字、陣列元素數、檔案大小
  - 範例：
    ```php
    'age' => 'between:18,65'
    'score' => 'between:60,100'
    ```

- **decimal:min,max**
  - *必須為數字，且小數位數介於 min 與 max 之間*
  - 範例：
    ```php
    'price' => 'decimal:2,4' // 2~4 位小數
    ```

- **digits:value**
  - *必須為指定長度的整數*
  - 範例：
    ```php
    'phone' => 'digits:10'
    ```

- **digits_between:min,max**
  - *必須為介於 min~max 長度的整數*
  - 範例：
    ```php
    'code' => 'digits_between:4,6'
    ```

- **gt:field**
  - *必須大於指定欄位或值*
  - 範例：
    ```php
    'end' => 'gt:start'
    ```

- **gte:field**
  - *必須大於等於指定欄位或值*
  - 範例：
    ```php
    'end' => 'gte:start'
    ```

- **integer**
  - *必須為整數（PHP FILTER_VALIDATE_INT 可接受的型別）*
  - 若需驗證為數字，建議搭配 numeric
  - 範例：
    ```php
    'count' => 'integer'
    ```

- **lt:field**
  - *必須小於指定欄位或值*
  - 範例：
    ```php
    'start' => 'lt:end'
    ```

- **lte:field**
  - *必須小於等於指定欄位或值*
  - 範例：
    ```php
    'start' => 'lte:end'
    ```

- **max:value**
  - *數值、長度、陣列元素數、檔案大小不得超過 value*
  - 範例：
    ```php
    'score' => 'max:100'
    ```

- **max_digits:value**
  - *整數最大長度*
  - 範例：
    ```php
    'code' => 'max_digits:8'
    ```

- **min:value**
  - *數值、長度、陣列元素數、檔案大小不得小於 value*
  - 範例：
    ```php
    'score' => 'min:60'
    ```

- **min_digits:value**
  - *整數最小長度*
  - 範例：
    ```php
    'code' => 'min_digits:4'
    ```

- **multiple_of:value**
  - *必須為 value 的倍數*
  - 範例：
    ```php
    'amount' => 'multiple_of:5'
    ```

- **numeric**
  - *必須為數字*
  - 可加 `strict` 參數，僅接受 int/float 型別（字串數字會被視為無效）：
    ```php
    'price' => 'numeric:strict'
    ```

- **same:field**
  - *必須與指定欄位值相同*
  - 範例：
    ```php
    'confirm_amount' => 'same:amount'
    ```

- **size:value**
  - *數值必須等於 value*
  - 範例：
    ```php
    'seats' => 'integer|size:10'
    ```

---

### 12.4 *陣列類（Arrays）*

- **array**
  - *欄位必須為 PHP 陣列*
  - 可指定允許的 key：
    ```php
    'user' => 'array:name,username'
    ```
  - *註解*： 建議明確指定允許的 key，避免多餘資料

- **between:min,max**
  - *陣列元素數必須介於 min 與 max 之間*
  - 範例：
    ```php
    'tags' => 'array|between:1,5'
    ```

- **contains:foo,bar,...**
  - *陣列必須包含所有指定值*
  - 可用 Rule::contains 流暢建構：
    ```php
    use Illuminate\Validation\Rule;
    'roles' => [
        'required',
        'array',
        Rule::contains(['admin', 'editor']),
    ]
    ```

- **distinct**
  - *陣列元素不得重複*
  - 可加 strict/ignore_case 參數：
    ```php
    'foo.*.id' => 'distinct:strict'
    'foo.*.id' => 'distinct:ignore_case'
    ```

- **in_array:anotherfield.***
  - *欄位值必須存在於另一陣列欄位的值中*
  - 範例：
    ```php
    'selected' => 'in_array:options.*'
    ```

- **in_array_keys:value.***
  - *欄位必須為陣列，且至少有一個 key 為指定值*
  - 範例：
    ```php
    'config' => 'array|in_array_keys:timezone'
    ```

- **list**
  - *欄位必須為「連續索引」的陣列（0~n-1）*
  - 範例：
    ```php
    'items' => 'list'
    ```

- **max:value**
  - *陣列元素數不得超過 value*
  - 範例：
    ```php
    'tags' => 'array|max:5'
    ```

- **min:value**
  - *陣列元素數不得小於 value*
  - 範例：
    ```php
    'tags' => 'array|min:2'
    ```

- **size:value**
  - *陣列元素數必須等於 value*
  - 範例：
    ```php
    'tags' => 'array|size:3'
    ```

---

### 12.5 *日期類（Dates）*

- **after:date**
  - *必須為指定日期之後*
  - 可指定日期字串或欄位名稱
  - 範例：
    ```php
    'start_date' => 'required|date|after:tomorrow'
    'finish_date' => 'required|date|after:start_date'
    ```
  - *比喻*： 像是活動結束日必須在開始日之後

- **after_or_equal:date**
  - *必須為指定日期之後或等於該日期*
  - 範例：
    ```php
    'start_date' => 'required|date|after_or_equal:today'
    ```

- **before:date**
  - *必須為指定日期之前*
  - 範例：
    ```php
    'end_date' => 'required|date|before:2025-01-01'
    ```

- **before_or_equal:date**
  - *必須為指定日期之前或等於該日期*
  - 範例：
    ```php
    'end_date' => 'required|date|before_or_equal:today'
    ```

- **date**
  - *必須為合法日期字串（strtotime 可解析）*
  - 範例：
    ```php
    'birthday' => 'date'
    ```

- **date_equals:date**
  - *必須等於指定日期*
  - 範例：
    ```php
    'event_date' => 'date_equals:2024-06-01'
    ```

- **date_format:format,...**
  - *必須符合指定日期格式（支援多格式）*
  - 範例：
    ```php
    'published_at' => 'date_format:Y-m-d H:i:s'
    ```

- **different:field**
  - *必須與指定欄位不同（可用於日期欄位）*
  - 範例：
    ```php
    'start_date' => 'different:end_date'
    ```

- **timezone**
  - *必須為合法時區識別字串*
  - 可指定範圍：
    ```php
    'timezone' => 'required|timezone:all'
    'timezone' => 'required|timezone:Africa'
    'timezone' => 'required|timezone:per_country,US'
    ```

---

### 12.6 *檔案類（Files）*

- **between:min,max**
  - *檔案大小必須介於 min 與 max KB 之間*
  - 範例：
    ```php
    'file' => 'between:100,1024' // 100KB~1MB
    ```

- **dimensions**
  - *圖像檔案必須符合指定尺寸限制*
  - 可用 Rule::dimensions 流暢建構：
    ```php
    use Illuminate\Validation\Rule;
    'avatar' => [
        'required',
        Rule::dimensions()->maxWidth(1000)->maxHeight(500)->ratio(3/2),
    ]
    ```

- **extensions:foo,bar,...**
  - *檔案副檔名必須在指定清單內*
  - 範例：
    ```php
    'photo' => 'extensions:jpg,png'
    ```

- **file**
  - *必須為成功上傳的檔案*
  - 範例：
    ```php
    'document' => 'file'
    ```

- **image**
  - *必須為圖像檔案（jpg, jpeg, png, bmp, gif, webp）*
  - 可加 allow_svg 允許 SVG：
    ```php
    'logo' => 'image:allow_svg'
    ```

- **max:value**
  - *檔案大小不得超過 value KB*
  - 範例：
    ```php
    'file' => 'max:2048' // 2MB
    ```

- **mimetypes:text/plain,...**
  - *檔案 MIME 類型必須在指定清單內*
  - 範例：
    ```php
    'video' => 'mimetypes:video/avi,video/mpeg'
    ```

- **mimes:foo,bar,...**
  - *檔案 MIME 類型必須對應指定副檔名*
  - 範例：
    ```php
    'photo' => 'mimes:jpg,bmp,png'
    ```

- **size:value**
  - *檔案大小必須等於 value KB*
  - 範例：
    ```php
    'file' => 'size:512'
    ```

---

### 12.7 *資料庫類（Database）*

- **exists:table,column**
  - *欄位值必須存在於指定資料表欄位*
  - 可指定欄位、連線、Eloquent Model、條件等
  - 範例：
    ```php
    'state' => 'exists:states'
    'state' => 'exists:states,abbreviation'
    'user_id' => 'exists:App\\Models\\User,id'
    'email' => 'exists:connection.staff,email'
    'states' => ['array', Rule::exists('states', 'abbreviation')]
    'email' => [
        'required',
        Rule::exists('staff')->where(fn($q) => $q->where('account_id', 1)),
    ]
    ```

- **unique:table,column**
  - *欄位值必須在資料表中唯一*
  - 可指定欄位、連線、Eloquent Model、條件、忽略 ID、忽略軟刪除等
  - 範例：
    ```php
    // 指定 table 與 column
    'email' => 'unique:users,email_address'
    // 指定 Eloquent Model
    'email' => 'unique:App\\Models\\User,email_address'
    // 指定連線
    'email' => 'unique:connection.users,email_address'
    // 忽略某 ID
    use Illuminate\Validation\Rule;
    'email' => [
        'required',
        Rule::unique('users')->ignore($user->id),
        Rule::unique('users')->ignore($user)->withoutTrashed(),
        Rule::unique('users', 'email_address')->ignore($user->id),
        Rule::unique('users')->where(fn($q) => $q->where('account_id', 1)),
    ]
    ```
  - *註解*： ignore 只能用系統產生的唯一值，避免 SQL injection

---

### 12.8 *工具/其他（Utilities）*

- **any_of**
  - *欄位必須符合任一組規則*
  - 可用 **Rule::anyOf** 流暢建構：
    ```php
    use Illuminate\Validation\Rule;
    'username' => [
        'required',
        Rule::anyOf([
            ['string', 'email'],
            ['string', 'alpha_dash', 'min:6'],
        ]),
    ]
    ```

- **bail**
  - *遇到第一個錯誤就停止該欄位後續驗證*
  - 範例：
    ```php
    'title' => 'bail|required|unique:posts|max:255'
    ```

- **exclude**
  - *驗證後從回傳資料中排除該欄位*
  - 範例：
    ```php
    'token' => 'exclude'
    ```

- **exclude_if:anotherfield,value**
  - *當另一欄位為指定值時，排除該欄位*
  - 範例：
    ```php
    'role_id' => 'exclude_if:is_admin,true'
    ```

- **exclude_unless:anotherfield,value**
  - *除非另一欄位為指定值，否則排除該欄位*
  - 範例：
    ```php
    'role_id' => 'exclude_unless:type,admin'
    ```

- **exclude_with:anotherfield**
  - *當另一欄位存在時，排除該欄位*
  - 範例：
    ```php
    'role_id' => 'exclude_with:token'
    ```

- **exclude_without:anotherfield**
  - *當另一欄位不存在時，排除該欄位*
  - 範例：
    ```php
    'role_id' => 'exclude_without:token'
    ```

- **filled**
  - *欄位存在時必須有值*
  - 範例：
    ```php
    'nickname' => 'filled'
    ```

- **missing**
  - *欄位不得出現在輸入資料中*
  - 範例：
    ```php
    'debug' => 'missing'
    ```

- **missing_if:anotherfield,value,...**
  - *當另一欄位為指定值時，該欄位不得出現*
  - 範例：
    ```php
    'debug' => 'missing_if:env,production'
    ```

- **missing_unless:anotherfield,value**
  - *除非另一欄位為指定值，否則該欄位不得出現*
  - 範例：
    ```php
    'debug' => 'missing_unless:env,local'
    ```

- **missing_with:foo,bar,...**
  - *只要任一指定欄位存在，該欄位不得出現*
  - 範例：
    ```php
    'debug' => 'missing_with:token,api_key'
    ```

- **missing_with_all:foo,bar,...**
  - *所有指定欄位都存在時，該欄位不得出現*
  - 範例：
    ```php
    'debug' => 'missing_with_all:token,api_key'
    ```

- **nullable**
  - *欄位可為 null*
  - 範例：
    ```php
    'publish_at' => 'nullable|date'
    ```

- **present**
  - *欄位必須存在於輸入資料中*
  - 範例：
    ```php
    'token' => 'present'
    ```

- **present_if:anotherfield,value,...**
  - *當另一欄位為指定值時，該欄位必須存在*
  - 範例：
    ```php
    'token' => 'present_if:type,api'
    ```

- **present_unless:anotherfield,value**
  - *除非另一欄位為指定值，否則該欄位必須存在*
  - 範例：
    ```php
    'token' => 'present_unless:type,web'
    ```

- **present_with:foo,bar,...**
  - *只要任一指定欄位存在，該欄位必須存在*
  - 範例：
    ```php
    'token' => 'present_with:api_key'
    ```

- **present_with_all:foo,bar,...**
  - *所有指定欄位都存在時，該欄位必須存在*
  - 範例：
    ```php
    'token' => 'present_with_all:api_key,session_id'
    ```

- **prohibited**
  - *欄位必須不存在或為空*
  - 範例：
    ```php
    'debug' => 'prohibited'
    ```

- **prohibited_if:anotherfield,value,...**
  - *當另一欄位為指定值時，該欄位必須不存在或為空*
  - 範例：
    ```php
    'debug' => 'prohibited_if:env,production'
    ```

- **prohibited_if_accepted:anotherfield,...**
  - *當另一欄位為 accepted 狀態時，該欄位必須不存在或為空*
  - 範例：
    ```php
    'debug' => 'prohibited_if_accepted:terms'
    ```

- **prohibited_if_declined:anotherfield,...**
  - *當另一欄位為 declined 狀態時，該欄位必須不存在或為空*
  - 範例：
    ```php
    'debug' => 'prohibited_if_declined:newsletter'
    ```

- **prohibited_unless:anotherfield,value,...**
  - *除非另一欄位為指定值，否則該欄位必須不存在或為空*
  - 範例：
    ```php
    'debug' => 'prohibited_unless:env,local'
    ```

- **prohibits:anotherfield,...**
  - *若該欄位存在，另一欄位必須不存在或為空*
  - 範例：
    ```php
    'token' => 'prohibits:debug'
    ```

- **required**
  - *欄位必須存在且不可為空*
  - 範例：
    ```php
    'title' => 'required'
    ```

- **required_if:anotherfield,value,...**
  - *當另一欄位為指定值時，該欄位必須存在且不可為空*
  - 範例：
    ```php
    'credit_card_number' => 'required_if:payment_type,cc'
    ```

- **required_if_accepted:anotherfield,...**
  - *當另一欄位為 accepted 狀態時，該欄位必須存在且不可為空*
  - 範例：
    ```php
    'agreement' => 'required_if_accepted:terms'
    ```

- **required_if_declined:anotherfield,...**
  - *當另一欄位為 declined 狀態時，該欄位必須存在且不可為空*
  - 範例：
    ```php
    'reason' => 'required_if_declined:newsletter'
    ```

- **required_unless:anotherfield,value,...**
  - *除非另一欄位為指定值，否則該欄位必須存在且不可為空*
  - 範例：
    ```php
    'phone' => 'required_unless:type,company'
    ```

- **required_with:foo,bar,...**
  - *只要任一指定欄位存在且有值，該欄位必須存在且不可為空*
  - 範例：
    ```php
    'address' => 'required_with:zipcode'
    ```

- **required_with_all:foo,bar,...**
  - *所有指定欄位都存在且有值時，該欄位必須存在且不可為空*
  - 範例：
    ```php
    'address' => 'required_with_all:zipcode,country'
    ```

- **required_without:foo,bar,...**
  - *只要任一指定欄位不存在或為空，該欄位必須存在且不可為空*
  - 範例：
    ```php
    'email' => 'required_without:phone'
    ```

- **required_without_all:foo,bar,...**
  - *所有指定欄位都不存在或為空時，該欄位必須存在且不可為空*
  - 範例：
    ```php
    'email' => 'required_without_all:phone,address'
    ```

- **required_array_keys:foo,bar,...**
  - *欄位必須為陣列，且必須包含指定 key*
  - 範例：
    ```php
    'options' => 'required_array_keys:timezone,locale'
    ```

- **sometimes**
  - *有時候才驗證（欄位存在時才驗證）*
  - 範例：
    ```php
    'nickname' => 'sometimes|string'
    ```
- **same:field**
  - *該欄位的值必須與指定欄位相同*
  - 範例：
    ```php
    'password' => 'same:password_confirmation'
    'confirm_amount' => 'same:amount'
    ```
  - *註解*： 常用於密碼、金額等雙重確認欄位

- **size:value**
  - *欄位必須符合指定大小*
  - 字串：長度必須等於 value
  - 數字：數值必須等於 value（需搭配 numeric/integer）
  - 陣列：元素數必須等於 value
  - 檔案：大小必須等於 value（KB）
  - 範例：
    ```php
    // 字串長度 12
    'title' => 'size:12';
    // 整數等於 10
    'seats' => 'integer|size:10';
    // 陣列 5 個元素
    'tags' => 'array|size:5';
    // 檔案大小 512KB
    'image' => 'file|size:512';
    ```

- **starts_with:foo,bar,...**
  - *欄位值必須以指定字串開頭*
  - 範例：
    ```php
    'slug' => 'starts_with:prod,dev'
    ```

- **string**
  - *欄位必須為字串*
  - 若允許為 null，需加 nullable：
    ```php
    'nickname' => 'string|nullable'
    ```

- **timezone**
  - *欄位必須為合法時區識別字串*
  - 可指定範圍：
    ```php
    'timezone' => 'required|timezone:all'
    'timezone' => 'required|timezone:Africa'
    'timezone' => 'required|timezone:per_country,US'
    ```
  - *註解*： 支援 PHP DateTimeZone::listIdentifiers 的所有參數

- **unique:table,column**
  - *欄位值必須在資料表中唯一*
  - 可指定欄位、連線、Eloquent Model、條件、忽略 ID、忽略軟刪除等
  - 範例：
    ```php
    // 指定 table 與 column
    'email' => 'unique:users,email_address'
    // 指定 Eloquent Model
    'email' => 'unique:App\\Models\\User,email_address'
    // 指定連線
    'email' => 'unique:connection.users,email_address'
    // 忽略某 ID
    use Illuminate\Validation\Rule;
    'email' => [
        'required',
        Rule::unique('users')->ignore($user->id),
        Rule::unique('users')->ignore($user)->withoutTrashed(),
        Rule::unique('users', 'email_address')->ignore($user->id),
        Rule::unique('users')->where(fn($q) => $q->where('account_id', 1)),
    ]
    ```
  - *註解*： ignore 只能用系統產生的唯一值，避免 SQL injection

- **uppercase**
  - *欄位必須全為大寫*
  - 範例：
    ```php
    'code' => 'uppercase'
    ```

- **url**
  - *欄位必須為合法 URL*
  - 可指定協定：
    ```php
    'homepage' => 'url:http,https'
    'game' => 'url:minecraft,steam'
    ```

- **ulid**
  - *欄位必須為合法 ULID*
  - 範例：
    ```php
    'order_id' => 'ulid'
    ```

- **uuid**
  - *欄位必須為合法 UUID（可指定版本）*
  - 範例：
    ```php
    'user_id' => 'uuid:4'
    ```

---

## 13. **條件式驗證與進階用法**

### *條件式驗證（Conditionally Adding Rules）*

- **exclude_if / exclude_unless**
  - *根據其他欄位值，條件性排除某欄位的驗證*
  - 範例：
    ```php
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
    ```

- **sometimes**
  - *欄位存在時才驗證*
  - 範例：
    ```php
    $validator = Validator::make($data, [
        'email' => 'sometimes|required|email',
    ]);
    ```

- **Validator::sometimes 方法**
  - *可用於更複雜的條件式驗證*
  - 範例：
    ```php
    $validator->sometimes('reason', 'required|max:500', function ($input) {
        return $input->games >= 100;
    });
    $validator->sometimes(['reason', 'cost'], 'required', function ($input) {
        return $input->games >= 100;
    });
    ```
  - *註解*： $input 會是 Fluent 實例，可存取所有輸入資料

  - **陣列條件式驗證**
    - *可針對陣列每個元素做條件驗證*
    - 範例：
      ```php
      $validator->sometimes('channels.*.address', 'email', function ($input, $item) {
          return $item->type === 'email';
      });
      $validator->sometimes('channels.*.address', 'url', function ($input, $item) {
          return $item->type !== 'email';
      });
      ```

### *陣列與巢狀驗證*  
- **array:name,username**
  - *限制陣列只能有指定 key*
  - 範例：
    ```php
    'user' => 'array:name,username'
    ```
- **dot notation**
  - *巢狀欄位可用點號語法*
  - 範例：
    ```php
    'photos.profile' => 'required|image'
    'users.*.email' => 'email|unique:users'
    'users.*.first_name' => 'required_with:users.*.last_name'
    ```
- **自訂訊息支援 * 號**
  - 範例：
    ```php
    'custom' => [
        'users.*.email' => [
            'unique' => 'Each user must have a unique email address',
        ]
    ]
    ```
- **錯誤訊息可用 :index, :position 佔位符**
  - 範例：
    ```php
    'photos.*.description.required' => 'Please describe photo #:position.'
    'photos.*.attributes.*.string' => 'Invalid attribute for photo #:second-position.'
    ```
- **Rule::forEach**
  - *可針對陣列每個元素動態指定規則*
  - 範例：
    ```php
    use App\Rules\HasPermission;
    'companies.*.id' => Rule::forEach(function ($value, $attribute) {
        return [
            Rule::exists(Company::class, 'id'),
            new HasPermission('manage-company', $value),
        ];
    }),
    ```

### *檔案驗證進階*  
- **File::types / min / max / image / dimensions**
  - *可用流暢 API 驗證檔案型態、大小、圖像尺寸*
  - 範例：
    ```php
    use Illuminate\Validation\Rules\File;
    Validator::validate($input, [
        'attachment' => [
            'required',
            File::types(['mp3', 'wav'])->min('1kb')->max('10mb'),
        ],
        'photo' => [
            'required',
            File::image()->min(1024)->max(12 * 1024)
                ->dimensions(Rule::dimensions()->maxWidth(1000)->maxHeight(500)),
        ],
    ]);
    ```
  - *註解*： image 預設不允許 SVG，需 image:allow_svg 或 File::image(allowSvg: true)

### *密碼驗證進階*  
- **Password::min / letters / mixedCase / numbers / symbols / uncompromised**
  - *可自訂密碼複雜度與資料外洩檢查*
  - 範例：
    ```php
    use Illuminate\Validation\Rules\Password;
    'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()]
    ```
  - *可全域預設密碼規則 Password::defaults()*

### *自訂驗證規則*  
- **make:rule Uppercase**
  - *artisan 指令產生自訂規則類別*
  - 範例：
    ```bash
    php artisan make:rule Uppercase
    ```
- **rule object validate 方法**
  - 範例：
    ```php
    class Uppercase implements ValidationRule {
        public function validate(string $attribute, mixed $value, Closure $fail): void {
            if (strtoupper($value) !== $value) {
                $fail('The :attribute must be uppercase.');
            }
        }
    }
    ```
- **DataAwareRule / ValidatorAwareRule**
  - *可取得全部資料或 validator 實例*
- **使用 Closure 當作規則**
  - 範例：
    ```php
    'title' => [
        'required',
        'max:255',
        function ($attribute, $value, $fail) {
            if ($value === 'foo') {
                $fail("The {$attribute} is invalid.");
            }
        },
    ]
    ```
- **隱含規則（implicit）**
  - *可用 --implicit 產生必定執行的自訂規則*
  - 範例：
    ```bash
    php artisan make:rule Uppercase --implicit
    ```

## 3.1 **三種驗證方式的差異與選用時機**

> **註解**：
  - `$request->validate()`：
    - 最簡單、最常用的驗證方式，直接在 Controller 內驗證。
    - 驗證失敗*自動重導回前頁，錯誤訊息與 old input 自動存入 session*。
    - 適合簡單表單、*一次性驗證*。
    - **缺點**：驗證規則寫在 Controller，難以複用、測試。
>
  - `Validator::make()`：
    - 手動建立 Validator 實例，適合需要自訂錯誤訊息、進階驗證、條件式驗證、手動控制流程時。
    - 可用於 API、複雜流程、非 HTTP 請求驗證。
    - 驗證失敗時需 *自行處理錯誤*（可用 `validate()` 方法自動重導）。
    - **優點**：彈性高、可自訂 after、messages、attributes、safe、errors 等。
>
  - **Form Request**（自訂請求類別）：
    - 使用 `php artisan make:request XxxRequest` 產生，將驗證與授權邏輯封裝成獨立類別。
    - Controller 只需型別提示該 Request，Laravel 會自動執行驗證與授權。
    - 適合大型專案、複雜表單、需複用驗證邏輯、易於測試與維護。
    - 可自訂 rules、authorize、messages、attributes、prepareForValidation、after、passedValidation 等方法。
    - **優點**：結構清晰、易於複用、可單元測試、支援進階授權。

---

- **完整對比範例**：

```php
// 1. $request->validate()：最簡單、最常見
public function store(Request $request) {
    // 直接驗證，失敗自動重導回前頁，錯誤訊息自動存入 session
    $validated = $request->validate([
        'title' => 'required', // 必填
        'body' => 'required',  // 必填
    ]);
    // $validated 只包含通過驗證的欄位
    // ...後續可直接用 $validated 儲存資料
}

// 2. Validator::make()：進階用法
public function store(Request $request) {
    // 手動建立 Validator 實例
    $validator = Validator::make($request->all(), [
        'title' => 'required',
        'body' => 'required',
    ]);
    // 驗證失敗時需自行處理（可自訂重導、訊息、API 回應等）
    if ($validator->fails()) {
        // 失敗時重導並帶入錯誤訊息與 old input
        return back()->withErrors($validator)->withInput();
    }
    // 取得通過驗證的資料
    $validated = $validator->validated();
    // ...後續可用 $validated
}

// 3. Form Request：推薦大型專案、複用需求
// app/Http/Requests/StorePostRequest.php
class StorePostRequest extends FormRequest {
    // 定義驗證規則
    public function rules() {
        return [
            'title' => 'required',
            'body' => 'required',
        ];
    }
    // 定義授權邏輯
    public function authorize() {
        return true; // 或自訂權限檢查
    }
    // 可自訂 messages、attributes、prepareForValidation ...
}
// Controller 只需型別提示該 Request，Laravel 會自動驗證與授權
public function store(StorePostRequest $request) {
    $validated = $request->validated(); // 只包含通過驗證的欄位
    // ...
}
```

> **小結**：
  - 小型/一次性表單可用 `$request->validate()`。
  - 需進階控制、API、複雜流程用 `Validator::make()`。
  - 複雜/大型/複用需求，推薦用 *Form Request* 封裝驗證。

