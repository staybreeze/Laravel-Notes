# *Laravel Validation 驗證 筆記*

---

## 1. **驗證簡介與快速上手**

- *Laravel 提供多種驗證資料的方式*，最常用的是 `Request` 實例的 `validate()` 方法。
- 也支援 *自訂 Form Request* 、* 手動 Validator* 等進階用法。
- *驗證規則* 非常豐富，包含 __格式、唯一性、長度、巢狀資料__ 等。
- *生活化比喻*： 驗證就像「__資料的守門員__」，只有合格的資料才能進入系統。

<!-- 
validate() 驗證失敗時，Laravel 會回傳 422 Unprocessable Entity，
不是 401（未授權）也不是 403（禁止存取）。
401 通常用於認證失敗，403 用於權限不足，
422 則是資料驗證不通過。 
-->

---

## 2. **路由與 Controller 範例**

```php
// routes/web.php
use App\Http\Controllers\PostController;

Route::get('/post/create', [PostController::class, 'create']);
Route::post('/post', [PostController::class, 'store']);
```
- __GET__ 顯示表單，__POST__ 儲存資料。

---

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
        $post = /* ... */;
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

---

- *陣列寫法*

  ```php
  // 以陣列方式撰寫規則，適合條件式、複雜規則
  $request->validate([
      'title' => ['required', 'unique:posts', 'max:255'], // 多個規則分開寫
      'body' => ['required'],
  ]);
  ```

---

- *指定 error bag*

  ```php
  // validateWithBag 可指定錯誤訊息存入特定 error bag，適合多表單同頁時分開顯示錯誤
  $request->validateWithBag('post', [
      'title' => ['required', 'unique:posts', 'max:255'],
      'body' => ['required'],
  ]);
  ```

---

- *bail*：遇到 __第一個錯誤__ 就停止該欄位後續驗證

  ```php
  // bail：只要遇到第一個錯誤就停止該欄位後續驗證，適合欄位有依賴關係時
  $request->validate([
      'title' => 'bail|required|unique:posts|max:255',
      'body' => 'required',
  ]);
  ```

---

- *巢狀欄位*（__dot 語法__）

  ```php
  // 巢狀資料可用 dot 語法驗證，例如 author.name
  $request->validate([
      'author.name' => 'required',
      'author.description' => 'required',
  ]);
  ```

---

- *欄位名稱含點號需跳脫*

  ```php
  // 欄位名稱本身有點號時需用 \. 跳脫
  $request->validate([
      'v1\\.0' => 'required', // 欄位名稱本身有點號時需用 \. 跳脫，避免被當成巢狀欄位解析
  ]);
  ```

---

## 4. **錯誤訊息顯示與 `$errors` 變數、`@error` 指令**

- __驗證失敗時__ 會自動重導 *回前頁* ，錯誤訊息與輸入資料 __自動存入 session__。
- `$errors` 變數（`Illuminate\Support\MessageBag`）__自動注入所有 view，可直接使用__：

```php
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

---

- __@error 指令__ 可顯示 _單一欄位錯誤_：

```php
<input name="title" class="@error('title') is-invalid @enderror">
@error('title')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror
// named error bag
@error('title', 'post') ... @enderror
```

- `$errors` 變數由 `ShareErrorsFromSession` middleware 注入，__永遠可用__。
- `ShareErrorsFromSession` middleware 是 Laravel __已預設在 web 路由群組__ 裡的

---

## 5. **表單回填 old()**

- *驗證失敗重導時，所有輸入資料自動存入 session*。

- 可用 `old()` 輔助函式回填：

```php
<input type="text" name="title" value="{{ old('title') }}">
```
- 也可用 `$request->old('title')` 於 __Controller__ 取得。

---

## 6. **XHR/JSON 驗證回應格式**

- *XHR（AJAX）請求驗證失敗時，Laravel 會自動回傳 `422 Unprocessable Entity（資料驗證不通過）` 狀態碼與 JSON 錯誤訊息*。

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

- 巢狀欄位會 _自動_ 轉為 dot 語法。

---

## 7. **Optional/Nullable 欄位**

- Laravel 預設有 `TrimStrings`、`ConvertEmptyStringsToNull` middleware。

- 欄位可為 `null` 時，請加 `nullable`：

  ```php
  // 欄位可為 null 時，請加 nullable
  $request->validate([
      'publish_at' => 'nullable|date',
  ]);

  // 欄位可不填時（optional），直接不加 required
  $request->validate([
      'title' => 'string', // 可不填，沒填就不驗證
  ]);
  ```

- *生活化比喻*： `nullable` 就像「__可留空欄位__」，`optional` 就像「__可不填欄位__」。

---

## 8. **Form Request** 驗證（進階）

### *Form Request 與 Controller 的互動*

- __Form Request__ 是 Laravel 專為 *表單驗證* 與 *授權封裝* 的請求類別，繼承自 `Illuminate\Foundation\Http\FormRequest`。

<!-- 
它會自動執行 rules() 驗證和 authorize() 授權，
你不用在 Controller 裡手動呼叫 $request->validate()。
-->

- *生活化比喻*： Form Request 就像「__專屬驗證小幫手__」，Controller 只需專心處理業務邏輯。


---

#### **典型開發與互動流程**

1. *建立 Form Request 類別*

   `php artisan make:request StoreUserRequest`

   - 類別會放在 `app/Http/Requests/` 目錄下。
   
2. *實作驗證與授權邏輯*

   - 必要方法：`rules()`、`authorize()`
   - 可選方法：`messages()`、`attributes()`、`prepareForValidation()`、`after()`、`passedValidation()`

3. *Controller 型別提示該 Request*

   - 只要在 Controller 方法參數 __型別提示__`Form Request`，Laravel 會 __自動注入__ 並執行驗證/授權：

   ```php
   use App\Http\Requests\StoreUserRequest;
   public function store(StoreUserRequest $request) {
       // ...
   }
   ```

4. *自動驗證與授權*

   - 進入 Controller __前__，會自動執行 `rules()` 驗證與 `authorize()` 授權
   - __`驗證`失敗__ 自動 __重導回前頁__，*錯誤訊息* __自動帶入 session__
   - __`授權`失敗__ 自動回傳 `403`，Controller 不會執行

5. *取得`驗證通過`的資料*

   - 只會包含 __rules()__ 通過的欄位

   ```php
   $validated = $request->validated();
   // 只取部分欄位
   $safe = $request->safe()->only(['name', 'email']);
   ```

6. *錯誤處理與自訂*

   - 錯誤訊息可用 `$errors` 變數在 Blade 顯示
   - 可自訂 `messages()、attributes()、prepareForValidation()、after()、passedValidation()` 等方法


---

#### **Controller 與 Form Request 範例**

```php
// app/Http/Requests/StoreUserRequest.php
class StoreUserRequest extends FormRequest {
  
    // authorize() 回傳 false 時，Laravel 會回傳 403 Forbidden（授權失敗，無權限）。
    // 如果是未登入（未通過認證），Laravel 會回傳 401 Unauthorized（未授權）。
    public function authorize() { 
      return true; 
      }

    // rules() 驗證失敗時，Laravel 會回傳 422 Unprocessable Entity（資料驗證不通過）。
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

---

#### **常見互動重點**

- Controller _不需_ 再寫 `$request->validate()`，一切自動處理
- *`驗證`失敗* 自動重導，錯誤訊息自動帶到 view
- *`授權`失敗* 自動回傳 __403__

<!-- 
Laravel 的 `$request->validate()` 會根據路由型態決定回應方式：

- 如果是「網頁路由」（回傳 Blade 頁面），驗證失敗時會自動重導回前頁，並把錯誤訊息帶到 view。
- 如果是「API 路由」（回傳 JSON），驗證失敗時會回傳 422 Unprocessable Entity，錯誤訊息會包含在回應的 JSON body 裡。 -->

- 可用 `$request->validated()` 取得 __乾淨資料__
- 可用 `$request->safe()->only([...])` 取 __部分欄位__
- 可自訂錯誤訊息、欄位名稱、驗證前/後處理

---

### *rules 方法*

  - 此方法用來定義「__每個欄位的`驗證`規則__」。
  - 回傳一個 _陣列_，`key` 為 __欄位名稱__，`value` 為 __規則字串或陣列__。

  - 常見規則：`required、email、max、unique、exists...`（可複合使用）
  - 支援 __陣列語法、Rule 類別、條件式規則__ 等進階用法。

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

---

### *authorize 方法*

  - 這個方法 __`不是`用來檢查 CSRF__ ，而是用來判斷「__目前用戶是否有權限執行這個請求__」。
  - 若回傳 `false`，Laravel 會自動回傳 `403 Forbidden`，controller 不會執行。

  - 常用於：只有 __管理員、本人、特定角色__ 才能送出表單。

  - 範例：`return auth()->user()->id === $this->route('id');`

  - __預設 `true`__： _所有人都能通過授權檢查，無權限限制_

  - 若需權限檢查，可改寫如下：

  - 只有 _管理員_ 能通過
  - `return auth()->user()->is_admin`;

  - 只有 _本人_ 能修改自己的資料
  - `return auth()->id() === $this->route('user')`;

```php
public function authorize(): bool {
    return true;
}
```

---

### *messages 方法*（__自訂錯誤訊息__）

  - 可自訂 __每個欄位+規則__ 的錯誤訊息。
  - `key` 格式為「_欄位.規則_」，如 `title.required`。

  - 支援 __:attribute、:min、:max__ 等 placeholder。

  - 適合用於多語系、客製化友善訊息。

  - __'title.required'__：當 title 欄位未填時，顯示「_標題必填_」
  - __'body.required'__：當 body 欄位未填時，顯示「_內容必填_」

  - 例如 `'title.max'` =>` '標題不可超過 :max 字元'`

```php
public function messages(): array {
    return [
        'title.required' => '標題必填',
        'body.required' => '內容必填',
    ];
}
```

---

### *attributes 方法*（__自訂欄位名稱__）

  - 可自訂 __錯誤訊息中__ ，顯示的欄位名稱。

  - 例如將 email 顯示為「電子郵件」。

  - 適合用於多語系、欄位名稱較技術化時。

```php
public function attributes(): array {
    return [
        'email' => '電子郵件',
    ];
}
```

---

### *prepareForValidation 方法*（__驗證`前`預處理__）

  - 在 __驗證規則執行前__，先對輸入資料做 __預處理（如格式化、合併欄位）__。

  - 常用於自`動補齊欄位、轉換格式、去除空白`等。

  - 例如 __自動產生 slug、合併多個欄位為一個__。

```php
protected function prepareForValidation(): void {
    // merge() 的意思是把新的資料合併到目前的請求資料裡
    // 這裡是把格式化後的 slug 合併進來，讓後續驗證和處理都用正確格式
    $this->merge([
        'slug' => Str::slug($this->slug),
    ]);
}
// slug 是一種「網址友善」的字串格式，
// 通常用於文章、標題等資料的網址片段，
// 會把空白、特殊字元轉成連字號（-），全部小寫，
// 例如：Laravel 入門教學 會變成 laravel-入門教學。
```

---

### *after 方法*（__進階驗證__）

  - 可在 __所有規則驗證完後__，進行`進階/跨欄位驗證`。
  - 回傳一個 `closure` 陣列，每個 `closure` 可 _自訂錯誤訊息_。

  - 適合 __複雜邏輯、需存取多欄位、外部資源__ 等情境。
  - 也可回傳 `invokable class`。

```php
public function after(): array {
    return [
        // after() 方法可在驗證結束後執行額外邏輯
        function (Validator $validator) {
            // 如果自訂條件不符合，手動加入錯誤訊息
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Something is wrong with this field!');
            }
        }
    ];
}
```

---

### *passedValidation 方法*（__驗證通過`後`處理__）

  - 當所有 __驗證通過後__，自動執行。

  - 可用於進一步 _處理資料、格式轉換、補充欄位_ 等。

  - 例如 __自動覆寫某些欄位、加密密碼__ 等。

```php
protected function passedValidation(): void {
    $this->replace(['name' => 'Taylor']);
}
```

---

### *自訂驗證失敗後的 redirect*

  - 可自訂 __驗證失敗時__，要`重導`的路徑或 route 名稱。
  - 適合 __多表單、多步驟__ 流程。

```php
protected $redirect = '/dashboard';
// 或
protected $redirectRoute = 'dashboard';
```

---

### *停止於第一個驗證失敗*

  - **預設** 會驗證所有欄位，`stopOnFirstFailure()` 可讓驗證`遇到第一個錯誤`就停止。
  - 適合 __表單欄位有`依賴關係`、只需回報一個錯誤時__。

```php
if ($validator->stopOnFirstFailure()->fails()) {
    // 啟用 stopOnFirstFailure，驗證遇到第一個錯誤就停止，不再檢查其他規則
    // ...後續處理
    // stopOnFirstFailure() 只是設定驗證遇到第一個錯誤就停止，
    // 但你還是要用 fails() 來判斷是否驗證失敗。
    // 不能單獨只用 stopOnFirstFailure()，
    // 因為它不會回傳結果，
    // 必須搭配 fails() 才能知道驗證有沒有通過。
}
```

---

### *自動重導與 validate 方法*

- `$request->validate()`：_自動驗證資料_，失敗時自動`重導回前頁`並帶入 _錯誤訊息與 old input_

  - 適合 Controller 內快速驗證，XHR/JSON __請求失敗時__，自動回傳 `422 JSON`

- `validateWithBag('post')`：*指定錯誤訊息* 存入特定 `error bag`，適合 __`多表單同頁時`分開顯示錯誤__

```php
Validator::make($request->all(), [
    // 'title' 欄位必填、唯一且最大 255 字元，未通過時會自動產生錯誤訊息
    'title' => 'required|unique:posts|max:255',
    // 'body' 欄位必填
    'body' => 'required',
])->validate();
// 失敗自動重導或回傳 JSON，錯誤訊息自動存入 session，old input 也自動帶回
// validateWithBag('post') 可指定錯誤訊息存入 $errors->post，適合多表單同頁時分開顯示錯誤

// 和$request->validate([...]);
// 本質上功能一樣，
// 都是用來驗證資料，
// 只是寫法不同，
// $request->validate() 是 Laravel 的語法糖，底層也是呼叫 Validator。
```

---

__FormRequest 差異__：

- FormRequest 是`獨立類別`，將驗證與授權邏輯集中管理（`rules()`、`authorize()`）。
- 驗證與授權會在進入 Controller `前`自動執行，程式更乾淨、可重複使用。
- 適合複雜或多次重用的表單驗證。

---

### *Named Error Bags*（__多表單錯誤命名__）

- __多表單同頁時__，可用 `named error bag` 區分錯誤訊息
- `withErrors($validator, 'login')` 會將錯誤訊息存入 `$errors->login`

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
```

```php
// Blade 取用：$errors->login->first('email')，只顯示 login error bag 的 email 錯誤
@if ($errors->login->any())
    <div class="alert alert-danger">
        {{ $errors->login->first('email') }}
    </div>
@endif
// 另一個表單的錯誤
@if ($errors->register->any())
    <div class="alert alert-danger">
        {{ $errors->register->first('email') }}
    </div>
@endif
// 預設 $errors 就是 $errors->default
```

---

### *自訂錯誤訊息*（__多種方式__）

- `Validator::make` *第三個參數* 可直接傳`自訂訊息陣列`
- __'required' => '此欄位必填'__：所有 required 規則都顯示這句話

- 支援 __:attribute、:other、:size、:min、:max、:input、:values__ 等 placeholder

```php

$validator = Validator::make($input, $rules, [
    'required' => '此欄位必填',
]);

// 如果只設定 'required' => '此欄位必填'，
// 所有欄位只要違反 required 規則，
// 都會顯示「此欄位必填」這個通用錯誤訊息，
// 不會針對特定欄位顯示不同訊息。

---

// 針對特定欄位+規則可用 dot 語法
$messages = [
    // email 欄位未填時顯示「請輸入 Email！」
    'email.required' => '請輸入 Email！',
];

if ($validator->fails()) {
    $errors = $validator->errors();
    // $errors->first('email') 會顯示「此欄位必填」或「請輸入 Email！」
}
```

---

### *自訂屬性名稱與 values*

- __Validator::make__ *第四個參數* 可`自訂欄位名稱顯示`，錯誤訊息會自動帶入

```php
public function store(Request $request)
{
    $input = $request->all();
    $rules = [/* 驗證規則 */];
    $messages = [/* 自訂錯誤訊息 */];

    $validator = Validator::make($input, $rules, $messages, [
        'email' => '電子郵件', // 自訂欄位名稱顯示
    ]);

    // ...後續驗證與處理
}
// 如果 email 欄位驗證失敗（例如沒填或格式錯誤），
// 錯誤訊息會顯示你自訂的內容（如「請輸入電子郵件！」或「電子郵件格式不正確！」），
// 而欄位名稱會顯示「電子郵件」而不是「email」。
```

```php
// resources/lang/zh-TW/validation.php
// 語系檔 values 區塊可自訂規則值顯示（如選單、狀態、類型等友善名稱）
'values' => [
    'payment_type' => [
        // 將 cc 顯示為「信用卡」
        'cc' => '信用卡'
    ],
],
```

---

### *進階 after 驗證*

- 可在 __驗證後__ 再加自訂檢查，適合`跨欄位、外部資源、複雜邏輯`

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

---

### *取得`驗證通過`的資料*

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

---

### *操作錯誤訊息 MessageBag*

- `$validator->errors()` 取得 `MessageBag` 實例，方便操作**錯誤訊息**

- 可取`單一欄位`第一個錯誤、所有錯誤、判斷欄位有無錯誤

- __Blade 內的 $errors 變數__，也是 `MessageBag`

```php
$errors = $validator->errors();
echo $errors->first('email'); // 取 email 欄位第一個錯誤

foreach ($errors->get('email') as $message) { /* ... */ } // 取 email 欄位所有錯誤
foreach ($errors->all() as $message) { /* ... */ } // 取所有欄位所有錯誤

if ($errors->has('email')) { /* ... */ } // 判斷 email 欄位有無錯誤
```

---

### *語系檔自訂錯誤訊息與屬性／值*

- __內建錯誤訊息__ 在 `lang/{語系}/validation.php`

- 可用 `php artisan lang:publish` 產生語系檔，方便多語系維護

- __custom 區塊__ 可針對 _欄位+規則_ 自訂訊息，
  __attributes 區塊__ 自訂 _欄位名稱_，
  __values 區塊__ 自訂 _規則值顯示_

- 語系檔就像「_錯誤訊息翻譯字典_」，讓提示語更貼近用戶

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
- __values 區塊應用__：有些驗證訊息會用到 *:value 佔位符*（如 `required_if:payment_type,cc`），可讓錯誤訊息`顯示友善名稱`
- 這種設計讓錯誤訊息 __更貼近用戶語言__，避免顯示技術縮寫或代碼，適合用於`選單、狀態、類型`等欄位的友善顯示
- __生活化比喻__： 語系檔就像「_錯誤訊息翻譯字典」_，可讓提示語更貼近用戶。

---

### *語系檔 values 區塊的應用*

- 有些驗證訊息會用到 `:value` 佔位符，例如：

  ```php
  Validator::make($request->all(), [
      // 當 payment_type 欄位為 cc 時，credit_card_number 欄位必填
      'credit_card_number' => 'required_if:payment_type,cc'
  ]);
  ```

- **預設** __錯誤訊息__ 會是：

  `The credit card number field is required when payment type is cc.`

- 你可以在語系檔 `values` 區塊自訂顯示：

  ```php
  'values' => [
      'payment_type' => [
          // 將 cc 顯示為「credit card」
          'cc' => 'credit card'
      ],
  ],
  ```
- 這樣 __錯誤訊息__ 就會變成：

  `The credit card number field is required when payment type is credit card.`

- __詳細註解__

  - `values` 區塊的設計目的是讓驗證訊息中的 `:value` 佔位符能顯示「_友善名稱_」而非技術縮寫或代碼。
  - 只要驗證訊息有 `:value` 佔位符，且 `values 區塊`有對應設定，Laravel 會自動將`原本的值（如 cc）替換成你設定的顯示名稱（如 credit card）`。
  - 這對於 _下拉選單、狀態、類型_ 等欄位特別有用，能讓錯誤訊息更貼近用戶語言。
  - 常見用途：_付款方式、狀態、角色、分類_ 等欄位的錯誤提示。
  - 這種設計讓錯誤訊息更易懂、更專業，也方便多語系維護。

---

## 12. **驗證規則總覽**

### *章節大綱*

- 12.1 布林類（__Booleans__）
- 12.2 字串類（__Strings__）
- 12.3 數字類（__Numbers__）
- 12.4 陣列類（__Arrays__）
- 12.5 日期類（__Dates__）
- 12.6 檔案類（__Files__）
- 12.7 資料庫類（__Database__）
- 12.8 工具/其他（__Utilities__）

---

### 12.1 *布林類*（__Booleans__）

- __accepted__

  - *欄位值必須是「`同意`」狀態*（`yes、on、1、"1"、true、"true"`）
  - 常用於「服務條款同意」勾選框
  - 範例：

    ```php
    'terms' => 'accepted'
    ```

  - *比喻*： 就像打勾同意才可送出表單

---

- __accepted_if:anotherfield,value,...__

  - *當另一欄位為指定值時，該欄位必須為「`同意`」*
  - 範例：

    ```php
    'terms' => 'accepted_if:role,admin'
    ```

---

- __declined__

  - *欄位值必須是「`拒絕`」狀態*（`no、off、0、"0"、false、"false`）
  - 範例：

    ```php
    'newsletter' => 'declined'
    ```

---

- __declined_if:anotherfield,value,...__

  - *當另一欄位為指定值時，該欄位必須為「`拒絕`」*
  - 範例：

    ```php
    'newsletter' => 'declined_if:role,guest'
    ```

---

- __boolean__

  - *欄位值必須`可轉為`布林值*（`true、false、1、0、"1"、"0"`）
  - 可加 `strict` 參數，僅接受 `true/false`：

    ```php
    'is_active' => 'boolean:strict'
    ```

---


---

### 12.2 *字串類*（__Strings__）

- __active_url__

  - *欄位必須是`有效的` URL，且`主機名稱`需有有效的 DNS A 或 AAAA 記錄*
  - 範例：

    ```php
    'website' => 'active_url'
    ```

  - *註解*： 只驗證 `DNS`，不驗證網址內容是否存在

---

- __alpha__

  - *欄位必須`全部為字母`*（__Unicode 字母__，含各國語言）
  - 可加 `ascii` 參數僅允許 `a-z/A-Z`：

    ```php
    'name' => 'alpha:ascii'
    ```

---

- __alpha_dash__

  - *欄位必須為`字母、數字、破折號（-）、底線（_）`*
  - 可加 `ascii` 參數僅允許`英文與數字`：

    ```php
    'username' => 'alpha_dash:ascii'
    ```

---

- __alpha_num__

  - *欄位必須為`字母或數字`*（__Unicode__）
  - 可加 `ascii` 參數僅允許`英文與數字`：

    ```php
    'code' => 'alpha_num:ascii'
    ```

---

- __ascii__

  - *欄位必須全部為 `7-bit ASCII 字元`*
  - 範例：

    ```php
    'slug' => 'ascii'
    ```

---

- __confirmed__

  - *欄位必須有一個「`_confirmation`」結尾的欄位且值相同*
  - 常用於`密碼、Email` 確認：

    ```php
    'password' => 'confirmed' // 需有 password_confirmation 欄位
    ```

  - 可**自訂**確認欄位名稱：

    ```php
    // 可自訂確認欄位名稱，讓驗證規則使用自訂的確認欄位
    'username' => 'confirmed:repeat_username'
    // 這樣 username 欄位會和 repeat_username 欄位做一致性驗證
    ```

---

- __current_password__

  - *欄位必須與`目前登入者密碼`相符*
  - 可指定 `guard`：

    ```php
    'password' => 'current_password:api'
    // 驗證 password 欄位是否與目前 api guard 登入者的密碼一致
    ```

---

- __different:field__

  - *欄位值必須與`指定欄位`不同*
  - 範例：

    ```php
    'new_password' => 'different:old_password'
    // new_password 欄位的值必須和 old_password 欄位不同，常用於修改密碼時防止重複
    ```

---

- __doesnt_start_with:foo,bar,...__

  - *欄位值不得以指定`字串開頭`*
  - 範例：

    ```php
    'slug' => 'doesnt_start_with:admin,sys'
    ```

---

- __doesnt_end_with:foo,bar,...__

  - *欄位值不得以指定`字串結尾`*
  - 範例：

    ```php
    'slug' => 'doesnt_end_with:tmp,log'
    ```

---

- __email__

  - *欄位必須為合法 `Email` 格式*
  - 可`加驗 RFC、DNS、嚴格、過濾`等：

    ```php
    'email' => 'email:rfc,dns'
    ```

  - *比喻*： 就像郵件地址必須正確才能寄達

---

- __ends_with:foo,bar,...__

  - *欄位值必須以指定`字串結尾`*
  - 範例：

    ```php
    'filename' => 'ends_with:.jpg,.png'
    ```

---

- __enum__

  - *欄位值必須為指定 `Enum` 類別的有效值*
  - 範例：

    ```php
    use App\Enums\Status;

    'status' => [Rule::enum(Status::class)]
    ```

---

- __hex_color__

  - *欄位必須為合法的 `16 進位色碼`*
  - 範例：

    ```php
    'color' => 'hex_color'
    ```

---

- __in:foo,bar,...__

  - *欄位值必須在`指定清單`內*
  - 範例：

    ```php
    'role' => 'in:admin,editor,user'
    ```

---

- __json__

  - *欄位必須為合法 `JSON` 字串*
  - 範例：

    ```php
    'meta' => 'json'
    ```

---

- __lowercase__

  - *欄位必須全為`小寫`*
  - 範例：

    ```php
    'tag' => 'lowercase'
    ```

---

- __mac_address__

  - *欄位必須為合法 `MAC 位址`*
  - 範例：

    ```php
    'device_mac' => 'mac_address'
    ```

---

- __not_in:foo,bar,...__

  - *欄位值不得在`指定清單`內*
  - 範例：

    ```php
    'username' => 'not_in:admin,root,sys'
    ```

---

- __not_regex:pattern__

  - *欄位值不得符合指定`正則表達式`*
  - 範例：

    ```php
    'username' => 'not_regex:/^admin_/'
    // username 欄位的值不能以 admin_ 開頭
    ```

---

- __regex:pattern__

  - *欄位值必須符合指定`正則表達式`*
  - 範例：

    ```php
    'phone' => 'regex:/^09[0-9]{8}$/'
    ```

---

- __size:value__

  - *字串`長度`必須等於 value*
  - 範例：

    ```php
    'code' => 'size:6'
    ```

---

- __starts_with:foo,bar,...__

  - *欄位值必須以指定`字串開頭`*
  - 範例：

    ```php
    'slug' => 'starts_with:prod,dev'
    ```

---

- __string__

  - *欄位必須為`字串`*
  - 若允許為 `null`，需加 `nullable`：

    ```php
    'nickname' => 'string|nullable'
    ```

---

- __uppercase__

  - *欄位必須全為`大寫`*
  - 範例：

    ```php
    'code' => 'uppercase'
    ```

---

- __url__

  - *欄位必須為`合法 URL`*
  - 可指定協定：

    ```php
    'homepage' => 'url:http,https'
    ```

---

- __ulid__

  - *欄位必須為`合法 ULID`*，產生`全域唯一識別碼`的格式。
  - ULID（Universally Unique Lexicographically Sortable Identifier）
  - 格式如 `01F8MECHZX3TBDSZ7XRADM79XV`，__可排序__，適合*資料庫索引*。
  - 範例：

    ```php
    'order_id' => 'ulid'
    ```

---

- __uuid__

  - *欄位必須為`合法 UUID`*，產生全域唯一識別碼的格式。
  - UUID（Universally Unique Identifier）
  - 格式如 `550e8400-e29b-41d4-a716-446655440000`，__不可排序__。
  - 可指定版本（如 uuid:4）。
  - 範例：

    ```php
    'user_id' => 'uuid:4'
    ```

---

### 12.3 *數字類*（__Numbers__）

- __between:min,max__

  - *數值必須`介於 min 與 max 之間`*（__含邊界）
  - 適用於`字串長度、數字、陣列元素數、檔案大小`
  - 範例：

    ```php
    'age' => 'between:18,65'
    'score' => 'between:60,100'
    ```

---

- __decimal:min,max__

  - *必須為數字，且`小數位數`介於 min 與 max 之間*
  - 範例：

    ```php
    'price' => 'decimal:2,4' // 2~4 位小數
    ```

---

- __digits:value__

  - *必須為`指定長度的`整數*
  - 範例：

    ```php
    'phone' => 'digits:10'
    ```

---

- __digits_between:min,max__

  - *必須`為介於 min~max 長度的`整數*
  - 範例：

    ```php
    'code' => 'digits_between:4,6'
    ```

---

- __gt:field__

  - *必須`大於`指定欄位或值*
  - 範例：

    ```php
    'end' => 'gt:start'
    ```

---

- __gte:field__

  - *必須`大於等於`指定欄位或值*
  - 範例：

    ```php
    'end' => 'gte:start'
    ```

---

- __integer__

  - *必須為`整數`*（__PHP FILTER_VALIDATE_INT 可接受的型別__）
  - 若需驗證為數字，建議搭配 `numeric`
  - 範例：

    ```php
    'count' => 'integer'
    ```

---

- __lt:field__

  - *必須`小於`指定欄位或值*
  - lt 代表 _less than_（小於）
  - 範例：

    ```php
    'start' => 'lt:end'
    ```

---

- __lte:field__

  - *必須`小於等於`指定欄位或值*
  - 範例：

    ```php
    'start' => 'lte:end'
    ```

---

- __max:value__

  - *`數值、長度、陣列元素數、檔案`大小不得超過 value*
  - 範例：

    ```php
    'score' => 'max:100'
    ```

---

- __max_digits:value__

  - *整數`最大長度`*
  - 範例：

    ```php
    'code' => 'max_digits:8'
    ```

---

- __min:value__

  - *`數值、長度、陣列元素數、檔案`大小不得小於 value*
  - 範例：

    ```php
    'score' => 'min:60'
    ```

---

- __min_digits:value__

  - *整數`最小長度`*
  - 範例：

    ```php
    'code' => 'min_digits:4'
    ```

---

- __multiple_of:value__

  - *必須為 value 的`倍數`*
  - 範例：

    ```php
    'amount' => 'multiple_of:5'
    ```

---

- __numeric__

  - *必須為`數字`*
  - 可加 `strict` 參數，僅接受 `int/float` 型別（字串數字會被視為無效）：

    ```php
    'price' => 'numeric:strict'
    ```

---

- __same:field__

  - *必須與指定欄位值`相同`*
  - 範例：

    ```php
    'confirm_amount' => 'same:amount'
    ```

---

- __size:value__

  - *數值必須`等於` value*
  - 範例：

    ```php
    'seats' => 'integer|size:10'
    ```

---

### 12.4 *陣列類*（__Arrays__）

- __array__

  - *欄位必須為 `PHP 陣列`*
  - 可指定允許的 `key`：

    ```php
    'user' => 'array:name,username'
    // user 欄位必須是陣列，且只允許 name 和 username 這兩個 key
    ```

  - *註解*： 建議明確指定允許的 key，避免多餘資料

---

- __between:min,max__

  - *`陣列元素數`必須介於 min 與 max 之間*
  - 範例：

    ```php
    'tags' => 'array|between:1,5'
    ```

---

- __contains:foo,bar,...__

  - *陣列必須`包含所有`指定值*
  - 可用 `Rule::contains` 流暢建構：

    ```php
    use Illuminate\Validation\Rule;

    $rules = [
        'roles' => [
            'required',                // roles 欄位必填
            'array',                   // 必須是陣列
            Rule::contains(['admin', 'editor']), // 陣列必須包含 admin 和 editor 這兩個值
        ],
    ];

    // 驗證範例
    $request->validate($rules);
    ```

---

- __distinct__

  - *陣列元素`不得重複`*
  - 可加 `strict/ignore_case` 參數：

    ```php
    'foo.*.id' => 'distinct:strict'        // foo 陣列中每個 id 必須完全唯一（嚴格比對）
    'foo.*.id' => 'distinct:ignore_case'   // foo 陣列中每個 id 必須唯一（忽略大小寫）
    ```

---

- __in_array:anotherfield.*__

  - *欄位值必須存在於另一陣列欄位的值中*
  - 範例：

    ```php
    'selected' => 'in_array:options.*'
    // selected 欄位的值必須在 options 陣列的任一值中
    ```

---

- __in_array_keys:value.*__

  - *欄位必須為陣列，且至少有一個 key 為指定值*
  - 範例：

    ```php
    'config' => 'array|in_array_keys:timezone'
    // config 欄位必須是陣列，且至少有一個 key 是 timezone
    ```

---

- __list__

  - *欄位必須為「`連續索引`」的陣列*（索引必須是 0~n-1，不能跳號）
  - 範例：

    ```php
    'items' => 'list'
    // items 欄位必須是連續索引的陣列，例如 [0, 1, 2, ...]
    ```

---

- __max:value__

  - *陣列元素數`不得超過` value*
  - 範例：

    ```php
    'tags' => 'array|max:5'
    ```

---

- __min:value__

  - *陣列元素數`不得小於` value*
  - 範例：

    ```php
    'tags' => 'array|min:2'
    ```

---

- __size:value__

  - *陣列元素數必須`等於` value*
  - 範例：

    ```php
    'tags' => 'array|size:3'
    ```

---

### 12.5 *日期類*（__Dates__）

- __after:date__

  - *必須為指定`日期之後`*
  - 可指定日期字串或欄位名稱
  - 範例：

    ```php
    'start_date' => 'required|date|after:tomorrow'
    // start_date 必須在明天之後

    'finish_date' => 'required|date|after:start_date'
    // finish_date 必須在 start_date 之後
    ```

  - *比喻*：像是活動結束日必須在開始日之後

---

- __after_or_equal:date__

  - *必須為指定日期`之後或等於`該日期*
  - 範例：

    ```php
    'start_date' => 'required|date|after_or_equal:today'
    // start_date 必須在今天或之後
    ```

---

- __before:date__

  - *必須為指定`日期之前`*
  - 範例：

    ```php
    'end_date' => 'required|date|before:2025-01-01'
    ```

---

- __before_or_equal:date__

  - *必須為指定日期`之前或等於`該日期*
  - 範例：

    ```php
    'end_date' => 'required|date|before_or_equal:today'
    ```

---

- __date__

  - *必須為合法日期字串*（可被 strtotime 解析）
  - 範例：

    ```php
    'birthday' => 'date'
    // birthday 欄位必須為合法日期字串，例如 2025-08-14
    ```

---

- __date_equals:date__

  - *必須`等於`指定日期*
  - 範例：

    ```php
    'event_date' => 'date_equals:2024-06-01'
    ```

---

- __date_format:format,...__

  - *必須符合指定`日期格式`*（__支援多格式__）
  - 範例：

    ```php
    'published_at' => 'date_format:Y-m-d H:i:s'
    ```

---

- __different:field__

  - *必須與指定欄位`不同`*（__可用於日期欄位__）
  - 範例：

    ```php
    'start_date' => 'different:end_date'
    ```

---

- __timezone__

  - *必須為合法時區識別字串*
  - 可指定範圍：

    ```php
    'timezone' => 'required|timezone:all'
    // 必須是所有合法時區

    'timezone' => 'required|timezone:Africa'
    // 必須是 Africa 區域的合法時區

    'timezone' => 'required|timezone:per_country,US'
    // 必須是美國（US）合法時區
    ```

---

### 12.6 *檔案類*（__Files__）

- __between:min,max__

  - *檔案`大小`必須介於 min 與 max `KB` 之間*
  - 範例：

    ```php
    'file' => 'between:100,1024' // 100KB~1MB
    ```

---

- __dimensions__

  - *圖像檔案必須符合指定`尺寸限制`*
  - 可用 `Rule::dimensions` 流暢建構：

    ```php
    use Illuminate\Validation\Rule;

    $rules = [
        'avatar' => [
            'required', // 必須上傳 avatar 欄位
            'image',    // 必須是圖像檔案
            Rule::dimensions()->maxWidth(1000)->maxHeight(500)->ratio(3/2), // 圖片最大寬度 1000、高度 500，比例 3:2
        ],
    ];

    // 驗證範例
    $request->validate($rules);
    ```

---

- __extensions:foo,bar,...__

  - *檔案`副檔名`必須在`指定清單內`*
  - 範例：

    ```php
    'photo' => 'extensions:jpg,png'
    ```

---

- __file__

  - *必須為`成功上傳`的檔案*
  - 範例：

    ```php
    'document' => 'file'
    ```

---

- __image__

  - *必須為圖像檔案*（__jpg, jpeg, png, bmp, gif, webp__）
  - 可加 `allow_svg` 允許 SVG：

    ```php
    'logo' => 'image:allow_svg'
    ```

---

- __max:value__

  - *檔案`大小`不得超過 value KB*
  - 範例：

    ```php
    'file' => 'max:2048' // 2MB
    ```

---

- __mimetypes:text/plain,...__

  - *檔案 `MIME 類型` 必須在`指定清單`內*
  - 範例：

    ```php
    'video' => 'mimetypes:video/avi,video/mpeg'
    ```

---

- __mimes:foo,bar,...__

  - *檔案 `MIME 類型` 必須對應 `指定副檔名`*
  - 範例：

    ```php
    'photo' => 'mimes:jpg,bmp,png'
    ```

---

- __size:value__

  - *檔案`大小`必須等於 value KB*
  - 範例：

    ```php
    'file' => 'size:512'
    ```

---

### 12.7 *資料庫類*（__Database__）

- __exists:table,column__

  - *欄位值必須存在於指定資料表欄位*
  - 可指定欄位、連線、Eloquent Model、條件等
  - 範例：

    ```php
    'state' => 'exists:states'
    // state 欄位的值必須存在於 states 資料表的主鍵欄位

    'state' => 'exists:states,abbreviation'
    // state 欄位的值必須存在於 states 資料表的 abbreviation 欄位

    'user_id' => 'exists:App\\Models\\User,id'
    // user_id 必須存在於 User 模型的 id 欄位

    'email' => 'exists:connection.staff,email'
    // email 必須存在於指定連線的 staff 資料表 email 欄位

    'states' => ['array', Rule::exists('states', 'abbreviation')]
    // states 必須是陣列，且每個值都必須存在於 states 資料表的 abbreviation 欄位

    'email' => [
        'required',
        Rule::exists('staff')->where(fn($q) => $q->where('account_id', 1)),
    ]
    // email 必須存在於 staff 資料表，且 account_id 為 1
    ```

---

- __unique:table,column__

  - *欄位值必須在資料表中`唯一`*
  - 可指定`欄位、連線、Eloquent Model、條件、忽略 ID、忽略軟刪除`等
  - 範例：

    ```php
    // 指定 table 與 column
    'email' => 'unique:users,email_address'
    // email 欄位必須在 users 資料表的 email_address 欄位唯一

    // 指定 Eloquent Model
    'email' => 'unique:App\\Models\\User,email_address'
    // email 欄位必須在 User 模型的 email_address 欄位唯一

    // 指定連線
    'email' => 'unique:connection.users,email_address'
    // email 欄位必須在指定連線的 users 資料表 email_address 欄位唯一

    // 忽略某 ID
    use Illuminate\Validation\Rule;
    'email' => [
        'required',
        Rule::unique('users')->ignore($user->id),
        // 忽略指定 id，常用於更新時排除自己

        Rule::unique('users')->ignore($user)->withoutTrashed(),
        // 忽略指定模型，且排除軟刪除資料

        Rule::unique('users', 'email_address')->ignore($user->id),
        // 指定欄位並忽略 id

        Rule::unique('users')->where(fn($q) => $q->where('account_id', 1)),
        // 加入額外條件
    ]
    ```

  - *註解*： `ignore` 只能用 __系統產生的唯一值__，避免 SQL injection
    <!-- ignore 只能用系統產生的唯一值（如 id 或模型主鍵），
        不能用使用者輸入的資料， 
        這樣可以避免惡意注入 SQL 語法，
        提升安全性，防止 SQL injection 攻擊。 -->

---

### 12.8 *工具/其他*（__Utilities__）

- __any_of__

  - *欄位必須符合`任一`組規則*
  - 可用 __Rule::anyOf__ 流暢建構：

    ```php
    use Illuminate\Validation\Rule;

    $rules = [
        'username' => [
            'required', // 必填
            Rule::anyOf([
                ['string', 'email'], // 可以是 email 格式
                ['string', 'alpha_dash', 'min:6'], // 或是至少 6 字元的英數字、底線、橫線
            ]),
        ],
    ];

    // 驗證範例
    $request->validate($rules);
    ```

---

- __bail__

  - *遇到`第一個錯誤`就停止該欄位後續驗證*
  - 範例：

    ```php
    'title' => 'bail|required|unique:posts|max:255'
    ```

---

- __exclude__

  - *`驗證後`從回傳資料中`排除該欄位`*
  - 範例：

    ```php
    'token' => 'exclude'
    ```

---

- __exclude_if:anotherfield,value__

  - *當`另一欄位`為指定值時，`排除該欄位`*
  - 範例：

    ```php
    'role_id' => 'exclude_if:is_admin,true'
    ```

---

- __exclude_unless:anotherfield,value__

  - *除非`另一欄位`為指定值，否則`排除該欄位*`
  - 範例：

    ```php
    'role_id' => 'exclude_unless:type,admin'
    // 除非 type 欄位為 admin，否則 role_id 欄位會被排除驗證
    ```

---

- __exclude_with:anotherfield__

  - *當`另一欄位`存在時，`排除該欄位`*
  - 範例：

    ```php
    'role_id' => 'exclude_with:token'
    ```

---

- __exclude_without:anotherfield__

  - *當`另一欄位`不存在時，`排除該欄位`*
  - 範例：

    ```php
    'role_id' => 'exclude_without:token'
    ```

---

- __filled__

  - *欄位存在時`必須有值`*
  - 範例：

    ```php
    'nickname' => 'filled'
    ```

---

- __missing__

  - *欄位`不得出現`在輸入資料中*
  - 範例：

    ```php
    'debug' => 'missing'
    ```

---

- __missing_if:anotherfield,value,...__

  - *當`另一欄位`為指定值時，該欄位`不得出現`*
  - 範例：

    ```php
    'debug' => 'missing_if:env,production'
    // 如果 env 欄位值是 production，debug 欄位就必須 missing（不存在）。
    // if 是「條件為真才執行」。
    ```

---

- __missing_unless:anotherfield,value__

  - *除非`另一欄位`為指定值，否則該欄位`不得出現`*
  - 範例：

    ```php
    'debug' => 'missing_unless:env,local'
    // 如果 env 不是 local，debug 欄位就必須 missing（不存在）。
    // unless 是「條件為假才執行」。
    ```

---

- __missing_with:foo,bar,...__

  - *只要`任一指定欄位存在`，該欄位`不得出現`*
  - 範例：

    ```php
    'debug' => 'missing_with:token,api_key'
    ```

---

- __missing_with_all:foo,bar,...__

  - *所有`指定欄位都存在`時，該欄位`不得出現`*
  - 範例：

    ```php
    'debug' => 'missing_with_all:token,api_key'
    ```

---

- __nullable__

  - *欄位可為 `null`*
  - 範例：

    ```php
    'publish_at' => 'nullable|date'
    ```

---

- __present__

  - *欄位必須存在於`輸入資料中`*
  - 範例：

    ```php
    'token' => 'present'
    // token 欄位必須存在於輸入資料中，可以是空值但不能缺少
    ```
    <!-- 「輸入資料中」指的是用戶送來的請求資料，
          例如表單欄位、API 傳來的 JSON、URL query 等，
          只要有這個欄位（即使值是空的），就算存在於輸入資料中。 -->
---

- __present_if:anotherfield,value,...__

  - *當`另一欄位`為指定值時，該欄位`必須存在`*
  - 範例：

    ```php
    'token' => 'present_if:type,api'
    ```

---

- __present_unless:anotherfield,value__

  - *除非`另一欄位`為指定值，否則該欄位`必須存在`*
  - 範例：

    ```php
    'token' => 'present_unless:type,web'
    ```

---

- __present_with:foo,bar,...__

  - *只要`任一指定欄位`存在，該欄位`必須存在`*
  - 範例：

    ```php
    'token' => 'present_with:api_key'
    ```

---

- __present_with_all:foo,bar,...__

  - *所有`指定欄位都存在`時，該欄位`必須存在`*
  - 範例：

    ```php
    'token' => 'present_with_all:api_key,session_id'
    ```

---

- __prohibited__

  - *欄位必須`不存在或為空`*
  - 範例：

    ```php
    'debug' => 'prohibited'
    ```

---

- __prohibited_if:anotherfield,value,...__

  - *當`另一欄位`為指定值時，該欄位`必須不存在或為空`*
  - 範例：

    ```php
    'debug' => 'prohibited_if:env,production'
    ```

---

- __prohibited_if_accepted:anotherfield,...__

  - *當`另一欄位`為 `accepted`` 狀態時，該欄位`必須不存在或為空`*
  - 範例：

    ```php
    'debug' => 'prohibited_if_accepted:terms'
    ```

---

- __prohibited_if_declined:anotherfield,...__

  - *當`另一欄位`為 `declined` 狀態時，該欄位`必須不存在或為空`*
  - 範例：

    ```php
    'debug' => 'prohibited_if_declined:newsletter'
    ```

---

- __prohibited_unless:anotherfield,value,...__

  - *除非`另一欄位`為指定值，否則該欄位`必須不存在或為空`*
  - 範例：

    ```php
    'debug' => 'prohibited_unless:env,local'
    ```

---

- __prohibits:anotherfield,...__

  - *若`該欄位存在`，另一欄位`必須不存在或為空`*
  - 範例：

    ```php
    'token' => 'prohibits:debug'
    ```

---

- __required__

  - *欄位必須`存在且不可為空`*
  - 範例：

    ```php
    'title' => 'required'
    ```

---

- __required_if:anotherfield,value,...__

  - *當`另一欄位`為指定值時，該欄位必須`存在且不可為空`*
  - 範例：

    ```php
    'credit_card_number' => 'required_if:payment_type,cc'
    ```

---

- __required_if_accepted:anotherfield,...__

  - *當`另一欄位`為 `accepted` 狀態時，該欄位必須`存在且不可為空`*
  - 範例：

    ```php
    'agreement' => 'required_if_accepted:terms'
    ```

---

- __required_if_declined:anotherfield,...__

  - *當`另一欄位`為 `declined` 狀態時，該欄位必須`存在且不可為空`*
  - 範例：

    ```php
    'reason' => 'required_if_declined:newsletter'
    ```

---

- __required_unless:anotherfield,value,...__

  - *除非`另一欄位`為指定值，否則該欄位必須`存在且不可為空`*
  - 範例：

    ```php
    'phone' => 'required_unless:type,company'
    ```

---

- __required_with:foo,bar,...__

  - *只要`任一指定欄位`存在且有值，該欄位必須`存在且不可為空`*
  - 範例：

    ```php
    'address' => 'required_with:zipcode'
    ```

---

- __required_with_all:foo,bar,...__

  - *所有`指定欄位都`存在且有值時，該欄位必須`存在且不可為空`*
  - 範例：

    ```php
    'address' => 'required_with_all:zipcode,country'
    ```

---

- __required_without:foo,bar,...__

  - *只要`任一指定欄位`不存在或為空，該欄位必須`存在且不可為空`*
  - 範例：

    ```php
    'email' => 'required_without:phone'
    // 如果 phone 欄位不存在或為空，email 欄位就必須存在且不可為空
    ```

---

- __required_without_all:foo,bar,...__

  - *所有`指定欄位都`不存在或為空時，該欄位必須`存在且不可為空`*
  - 範例：

    ```php
    'email' => 'required_without_all:phone,address'
    ```

---

- __required_array_keys:foo,bar,...__

  - *欄位必須為`陣列`，且必須`包含指定 key`*
  - 範例：

    ```php
    'options' => 'required_array_keys:timezone,locale'
    // options 欄位必須是陣列，且必須包含 timezone 和 locale 這兩個 key
    ```

---

- __sometimes__

  - *`有`時候才驗證*（__欄位存在時才驗證__）
  - 範例：

    ```php
    'nickname' => 'sometimes|string'
    ```

---

- __same:field__

  - *該欄位的值必須與指定欄位`相同`*
  - 範例：

    ```php
    'password' => 'same:password_confirmation'
    'confirm_amount' => 'same:amount'

    ```

  - *註解*： 常用於`密碼、金額`等雙重確認欄位

---

- __size:value__

  - *欄位必須符合指定`大小`*

  - _字串_：`長度`必須等於 value
  - _數字_：`數值`必須等於 value（需搭配 `numeric/integer`）
  - _陣列_：`元素數`必須等於 value
  - _檔案_：`大小`必須等於 value（KB）
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

---

- __starts_with:foo,bar,...__

  - *欄位值必須以指定`字串開頭`*
  - 範例：

    ```php
    'slug' => 'starts_with:prod,dev'
    ```

---

- __string__

  - *欄位必須為`字串`*
  - 若允許為 `null`，需加 `nullable`：

    ```php
    'nickname' => 'string|nullable'
    ```

---

- __timezone__

  - *欄位必須為合法時區識別字串*
  - 可指定範圍：

    ```php
    'timezone' => 'required|timezone:all'
    // 必須是所有合法時區

    'timezone' => 'required|timezone:Africa'
    // 必須是 Africa 區域的合法時區

    'timezone' => 'required|timezone:per_country,US'
    // 必須是美國（US）合法時區
    ```

  - *註解*：可根據需求 __限制時區範圍__，提升資料正確性與一致性。
           支援 PHP `DateTimeZone::listIdentifiers` 的所有參數

---

- __unique:table,column__

  - *欄位值必須在資料表中`唯一`*
  - 可指定`欄位、連線、Eloquent Model、條件、忽略 ID、忽略軟刪除`等
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

- __uppercase__

  - *欄位必須全為`大寫`*
  - 範例：

    ```php
    'code' => 'uppercase'
    ```

---

- __url__

  - *欄位必須為`合法 URL`*
  - 可指定協定：

    ```php
    'homepage' => 'url:http,https'
    'game' => 'url:minecraft,steam'
    ```

---

- __ulid__

  - *欄位必須為合法 `ULID`*
  - 範例：

    ```php
    'order_id' => 'ulid'
    ```

---

- __uuid__

  - *欄位必須為合法 `UUID`*（__可指定版本__）
  - 範例：

    ```php
    'user_id' => 'uuid:4'
    ```

---

## 13. **條件式驗證與進階用法**

### *條件式驗證*（__Conditionally Adding Rules__）

- __exclude_if / exclude_unless__

  - *根據其他欄位值，條件性`排除`某欄位的驗證*
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

---

- __sometimes__

  - *欄位`存在時`才驗證*
  - 範例：

    ```php
    $validator = Validator::make($data, [
        'email' => 'sometimes|required|email',
    ]);
    ```

---

- __Validator::sometimes 方法__

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

  - *註解*： `$input` 會是 `Fluent` 實例，__可存取所有輸入資料__

---

  - __陣列條件式驗證__

    - *可針對陣列`每個元素`做條件驗證*
    - 範例：
      ```php
      $validator->sometimes('channels.*.address', 'email', function ($input, $item) {
          // 如果 channels 陣列的元素 type 為 email，address 欄位要驗證 email 格式
          return $item->type === 'email';
      });
      $validator->sometimes('channels.*.address', 'url', function ($input, $item) {
          // 如果 channels 陣列的元素 type 不是 email，address 欄位要驗證 url 格式
          return $item->type !== 'email';
      });
      ```

---

### *陣列與巢狀驗證*  

- __array:name,username__

  - *限制陣列`只能有指定 key`*
  - 範例：

    ```php
    'user' => 'array:name,username'
    ```

---

- __dot notation__

  - *巢狀欄位可用`點號語法`*
  - 範例：

    ```php
    'photos.profile' => 'required|image'
    'users.*.email' => 'email|unique:users'
    'users.*.first_name' => 'required_with:users.*.last_name'
    ```

---

- __自訂訊息支援 * 號__

  - 範例：

    ```php
    'custom' => [
        // * 代表任意索引，可針對陣列每個元素自訂錯誤訊息
        'users.*.email' => [
            'unique' => 'Each user must have a unique email address',
        ]
    ]
    ```

---

- __錯誤訊息可用 :index, :position 佔位符__

  - 範例：

    ```php
    'photos.*.description.required' => 'Please describe photo #:position.'
    'photos.*.attributes.*.string' => 'Invalid attribute for photo #:second-position.'
    // :position 會自動帶入陣列元素的位置，讓錯誤訊息更明確
    ```
---

- __Rule::forEach__

  - *可針對陣列每個元素`動態指定規則`*
  - 範例：

    ```php
    use App\Rules\HasPermission;

    'companies.*.id' => Rule::forEach(function ($value, $attribute) {
        // 可針對 companies 陣列每個 id 元素動態指定規則
        // 檢查 id 是否存在於 Company 模型
        // 檢查是否有 manage-company 權限
        return [
            Rule::exists(Company::class, 'id'),
            new HasPermission('manage-company', $value),
        ];
    }),
    ```

---

### *檔案驗證進階*

- __File::types / min / max / image / dimensions__

  - *可用流暢 API 驗證檔案`型態、大小、圖像尺寸`*
  - 範例：

    ```php
    use Illuminate\Validation\Rules\File;

    Validator::validate($input, [
        'attachment' => [
            'required',
            // 驗證檔案型態必須是 mp3 或 wav，大小 1kb~10mb
            File::types(['mp3', 'wav'])->min('1kb')->max('10mb'),
        ],
        'photo' => [
            'required',
            // 驗證必須是圖像檔案，大小 1024~12288kb，且尺寸限制
            File::image()->min(1024)->max(12 * 1024)
                ->dimensions(Rule::dimensions()->maxWidth(1000)->maxHeight(500)),
        ],
    ]);
    // 註解：image 預設不允許 SVG，需 image:allow_svg 或 File::image(allowSvg: true)
    ```

---

### *密碼驗證進階*

- __Password::min / letters / mixedCase / numbers / symbols / uncompromised__

  - *可自訂`密碼複雜度`與`資料外洩檢查`*
  - 範例：

    ```php
    use Illuminate\Validation\Rules\Password;

    'password' => [
        'required',
        'confirmed',
        // 密碼至少 8 字元，包含字母、大小寫、數字、符號，且未被洩漏
        Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()
    ]

    // 可全域預設密碼規則：Password::defaults()
    // app/Providers/AppServiceProvider.php 的 boot() 方法裡。
    use Illuminate\Validation\Rules\Password;

    public function boot()
    {
        Password::defaults(function () {
            return Password::min(8)->letters()->mixedCase()->numbers()->symbols();
        });
    }
    ```


---

### *自訂驗證規則*  

- __make:rule Uppercase__

  - *artisan 指令產生`自訂規則類別`*
  - 範例：

    ```bash
    `php artisan make:rule Uppercase`
    ```

---

- __rule object validate 方法__（`驗證規則物件`）

  - 範例：

    ```php
    class Uppercase implements ValidationRule {
        public function validate(string $attribute, mixed $value, Closure $fail): void {
            // 檢查 value 是否全為大寫
            if (strtoupper($value) !== $value) {
                $fail('The :attribute must be uppercase.');
            }
        }
    }
    // 可自訂驗證規則物件，validate 方法用於驗證邏輯
    ```

---

- __DataAwareRule / ValidatorAwareRule__

- 可取得 *全部資料* 或 *validator 實例*，方便進行更複雜的驗證邏輯

```php
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;

class MatchOtherField implements DataAwareRule, ValidatorAwareRule
{
    protected $data;
    protected $validator;

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 取得其他欄位資料進行比對
        if ($value !== ($this->data['other_field'] ?? null)) {
            $fail('The :attribute must match other_field.');
        }
    }
}
```

---

- __使用 Closure 當作規則__

  - 範例：

    ```php
    'title' => [
        'required',
        'max:255',
        function ($attribute, $value, $fail) {
            // 自訂驗證邏輯，當值為 foo 時，驗證失敗
            if ($value === 'foo') {
                $fail("The {$attribute} is invalid.");
            }
        },
    ]
    ```

---

- __隱含規則（implicit）__

  - 可用 `--implicit` 產生*必定執行的自訂規則*
  - 範例：

    ```bash
    `php artisan make:rule Uppercase --implicit`
    ```
    
    ```php
    use App\Rules\Uppercase;

    $request->validate([
        'name' => ['required', new Uppercase],
    ]);
    // name 欄位即使為 null 或空字串，Uppercase 規則也會執行
    ```

---

## 14. __三種驗證方式的差異與選用時機__

  - `$request->validate()`：

    - 最簡單、最常用的驗證方式，直接在 `Controller` 內驗證。
    - 驗證失敗 *自動重導回前頁，錯誤訊息與 old input 自動存入 session* 。
    - 適合簡單表單、*一次性驗證* 。
    - __缺點__：驗證規則寫在 Controller，難以複用、測試。

  - `Validator::make()`：

    - 手動建立 `Validator 實例`，適合需要`自訂錯誤訊息、進階驗證、條件式驗證、手動控制流程`時。
    - 可用於 API、複雜流程、非 HTTP 請求驗證。
    - 驗證失敗時需 *自行處理錯誤*。
    - __優點__：彈性高、可自訂 `after、messages、attributes、safe、errors` 等。

  - `Form Request`（自訂請求類別）：

    - 使用 `php artisan make:request XxxRequest` 產生，將`驗證與授權邏輯封裝成獨立類別`。
    - Controller 只需 _型別提示_ 該 Request，Laravel 會自動執行驗證與授權。
    - 適合大型專案、複雜表單、需複用驗證邏輯、易於測試與維護。
    - 可自訂 `rules、authorize、messages、attributes、prepareForValidation、after、passedValidation` 等方法。
    - __優點__：結構清晰、易於複用、可單元測試、支援進階授權。

---

- __完整對比範例__

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

---

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

---

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

---

__小結__

  - 小型/一次性表單可用 `$request->validate()`。
  - 需進階控制、API、複雜流程用 `Validator::make()`。
  - 複雜/大型/複用需求，推薦用 *Form Request* 封裝驗證。

