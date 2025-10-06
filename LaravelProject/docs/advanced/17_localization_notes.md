# *Laravel 語系（Localization） 筆記*

在 Laravel 中，`localization`（本地化）指的是讓你的應用程式 **能夠支援多語系**，根據使用者的語言或地區顯示不同的文字內容。這通常包含「_翻譯字串_」、「_日期/時間格式_」、「_數字/貨幣格式_」等，讓同一個網站能自動切換成繁體中文、英文、日文等不同語言。

---

## 1. **什麼是 Localization？**

-  _本地化（localization, l10n）_：讓你的系統根據「__使用者的語言、地區__」自動切換顯示內容。
-  例如：中文用戶看到「_歡迎！_」，英文用戶看到「_Welcome!_」。

-  _國際化（internationalization, i18n）_：讓系統具備 __多語系能力__，`localization` 則是實際切換與顯示。

<!-- l10n 是把 「 localization」 的首字母 l 和尾字母 n 保留，中間 10 個字母用數字 10 取代，
     這種縮寫方式常用於國際化（i18n）、本地化（l10n）等詞，
     方便簡短表示長單字。 -->

---

## 2. **Laravel 如何做 Localization？**

### (1) *語言檔*（`Language Files`）

-  所有翻譯字串都放在 `resources/lang/{語系}/` 目錄下。
-  每個語系目錄下有多個 PHP 檔案，每個檔案回傳一個 `key=>value 陣列`。

---

### (2) *取得翻譯字串*

-  用 `__('messages.welcome')` 或 `@lang('messages.welcome')` 取得對應語系的翻譯。

-  例如：

-  `resources/lang/zh_TW/messages.php`: `return ['welcome' => '歡迎！']`;
    這是 Laravel 語言檔的「繁體中文」版本，檔案放在 `resources/lang/zh_TW/messages.php`，內容是 _key 為 'welcome'_，_value 為『歡迎！』_。
    只要系統語言設定為 __zh_TW__，`__('messages.welcome')` 會顯示『歡迎！』。

-  `resources/lang/en/messages.php`: `return ['welcome' => 'Welcome!']`;
    這是 Laravel 語言檔的「英文」版本，檔案放在 `resources/lang/en/messages.php`，內容是 _key 為 'welcome'_，_value 為 'Welcome!'_。
    只要系統語言設定為 __en__，`__('messages.welcome') 會顯示` 'Welcome!'。

-  `在 Blade 或 Controller`：`echo __('messages.welcome')`;
    用 `__('messages.welcome')` 這個方法，Laravel 會根據目前語系 __自動選擇正確的語言檔，顯示對應語言內容__。
    例如：

      - 當語系為 __zh_TW__，畫面會顯示「歡迎！」
      - 當語系為 __en__，畫面會顯示 'Welcome!'
    
    這是 Laravel 多語系最基本、最常用的做法，讓你輕鬆支援多國語言。

---

### (3) *切換語系*

-  預設語系在 `config/app.php` 的 __locale__ 設定。
-  也可用 `App::setLocale('en')` 動態切換語系。

---

### (4) *複數、參數替換*

-  支援 __複數型態__（如 `apple/apples`）、__動態參數__（如 `:count、:name`）。
-  例如：`trans_choice('messages.apples', 5, ['count' => 5])`;

   ```php
   // resources/lang/en/messages.php
   return [
       'apples' => '{0} No apples|{1} One apple|[2,*] :count apples',
   ];
   ```
   <!--  根據數量自動選擇正確的語言（複數、單數） -->
   <!-- 第一個 5 是要判斷的數量（決定用單數還是複數），
        第二個 ['count' => 5] 是傳給語言檔的參數，
        讓翻譯內容可以顯示這個數字（例如 :count apples 會變成 5 apples）。 -->

---

### (5) *系統訊息本地化*

-  Laravel 內建的 __驗證、認證、密碼重設__ 等訊息都可在 `resources/lang/{語系}/` 下找到並自訂。

---

## 3. **實際應用場景**

-  `多國語系網站、後台管理系統、API 回應、日期/時間/貨幣格式` 自動切換。

---

## 4. **小結**

-  localization 在 Laravel 就是「__讓你的系統能多語系顯示、在地化所有內容__」。
-  只要維護好語言檔，程式就能 __自動根據語系__ 切換所有訊息。
-  這對 _國際化、台灣/香港/大陸/海外_ 多語網站都非常重要。

---

## 1. **語系檔案結構**

Laravel 支援 __兩種語系__ 檔案管理方式：

- *陣列語系檔*（短鍵）：

  - `lang/{locale}/messages.php`

  - 適合 __結構化、分群管理__
  - 範例：

    ```php
    // lang/en/messages.php
    return [
        'welcome' => 'Welcome to our application!',
    ];
    // lang/zh-TW/messages.php
    return [
        'welcome' => '歡迎使用本系統！',
    ];
    ```

---

- *JSON 語系檔*（以原文為 key）：

  Laravel 也支援 JSON 格式的語言檔，`key 為原文`（通常是英文），`value 為翻譯內容`。
  這種格式適合 __大量、動態字串__，或 __不想手動定義 key__ 的情境。

  - `lang/en.json`, `lang/zh-TW.json`
    每個語系一個 JSON 檔，放在 `resources/lang` 目錄下。

  - 適合 __大量、動態字串__
    只要在程式中用` __('原文')`，Laravel 會自動對應到 JSON 檔的翻譯。

  - 範例：

    ```json
    {
      // key 為原文 "I love programming."，value 為翻譯 "我愛寫程式。"
      "I love programming.": "我愛寫程式。"
    }

      {{ __('I love programming.') }}
    ```

---

## 2. **建立語系檔案**

Laravel 預設不含 `lang` 目錄。可用 artisan 指令產生：

```shell
php artisan lang:publish
```

---

產生後 *結構範例*：

```php
resources/lang/
├── en/                  // 英文語言資料夾
│   └── messages.php     // 英文 PHP 陣列語言檔
├── zh-TW/               // 繁體中文語言資料夾
│   └── messages.php     // 繁體中文 PHP 陣列語言檔
├── en.json              // 英文 JSON 語言檔（適合前端）
└── zh-TW.json           // 繁體中文 JSON 語言檔（適合前端）
```

---

## 3. **語系設定**

- `config/app.php`：

  ```php
  // 先讀取 .env 檔案裡的 APP_LOCALE 設定，如果 .env 沒有設定，就用 'en' 當預設值。
  'locale' => env('APP_LOCALE', 'en'),              // 預設語言（如 en、zh-TW）
  'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'), // 備援語言（找不到翻譯時使用）
  ```

---

- `.env`：

  ```php
  APP_LOCALE=zh-lTW           // 預設語言設為繁體中文
  APP_FALLBACK_LOCALE=en     // 備援語言設為英文（找不到翻譯時使用）
  ```

---

## 4. **動態切換語系**

```php
use Illuminate\Support\Facades\App;

Route::get('/greeting/{locale}', function (string $locale) {
    if (! in_array($locale, ['en', 'zh-TW'])) {
        abort(400);
    }
    App::setLocale($locale);
    // ...
});
```

---

取得 *目前語系*：

```php
$locale = App::currentLocale();
if (App::isLocale('zh-TW')) { /* ... */ }
```

---

## 5. **取得翻譯字串**

- *短鍵語系檔*：

  ```php
  __('messages.welcome')
  // 若不存在，回傳 'messages.welcome'
  ```

---

- *JSON 語系檔*：

  ```php
  __('I love programming.')
  // 若不存在，回傳 'I love programming.'
  ```

---

- *Blade*：

  ```html
  {{ __('messages.welcome') }}
  ```

---

## 6. **參數替換**

語系檔可用 `:name` 佔位符：

```php
'welcome' => 'Welcome, :name',
```

---

*呼叫時傳入陣列*：

```php
echo __('messages.welcome', ['name' => 'Vincent']);
// Welcome, Vincent
```

- 大寫/首字大寫 *佔位符自動對應*：

  - `:NAME` → 全大寫
  - `:Name` → 首字大寫

---

## 7. **物件參數格式化**（stringable）

- Laravel 語系字串支援 __傳入物件參數__，會 __自動呼叫物件__ 的 `__toString()` 方法。

- 你也可以用 `Lang::stringable()` __全域自訂某類物件的顯示格式__（如`金額、日期`等）。

若 __佔位符傳入物件__，Laravel 會呼叫其 `__toString`。如需自訂格式，可在 `AppServiceProvider` 註冊：

```php
use Illuminate\Support\Facades\Lang; // 引入 Laravel 語系 Facade
use Money\Money; // 假設你有一個 Money 金額物件

public function boot(): void
{
    // 註冊 Money 物件的全域格式化邏輯，讓語系字串遇到 Money 物件時自動呼叫 formatTo('zh_TW')
    Lang::stringable(function (Money $money) {
        return $money->formatTo('zh_TW'); // 將金額格式化為台幣顯示
    });
}
```
- *應用情境*：這樣你在語系字串中用 `:amount 佔位符`時，傳入 __Money 物件__ 會自動顯示正確格式。
- *注意*：可註冊多個 `stringable`，支援多型、不同物件類型。

---

### *實際使用範例*

__假設你要顯示金額，並希望自動格式化為台幣__：

1. 語系檔（`resources/lang/zh_TW/messages.php`）

```php
// 定義一個含有 :amount 佔位符的語系字串
return [
    'price' => '價格：:amount'
];
```

---

2. `AppServiceProvider` 註冊 `stringable`（如前面設定）

```php
// 這段只需設定一次，讓 Money 物件自動格式化
use Illuminate\Support\Facades\Lang;
use Money\Money;

public function boot(): void
{
    // 遇到 Money 物件時，自動呼叫 formatTo('zh_TW')
    Lang::stringable(function (Money $money) {
        return $money->formatTo('zh_TW');
    });
}
```

<!-- 
Lang::stringable() 是 Laravel 10 新增的功能，
它會自動在翻譯字串插值時呼叫你註冊的格式化邏輯，
不需要你手動呼叫。 
-->

<!-- 
當你用 __('messages.price', ['amount' => $money]) 這種語法時，
Laravel 會自動檢查參數型別（這裡是 Money 物件），
如果型別有註冊過 stringable，就會自動呼叫你設定的格式化方法（formatTo('zh_TW')）。 
-->

---

3. __實際在程式中使用__

```php
// 建立一個 Money 物件，並將它作為參數傳給語系字串
$money = new Money(1000, 'TWD');

// 在 Controller 或 Blade 取得翻譯字串，:amount 會自動被格式化
// 輸出：價格：NT$1,000
// PHP 寫法：
echo __('messages.price', ['amount' => $money]);

// Blade 寫法：
// 假設 $money 是 Money 物件
{{ __('messages.price', ['amount' => $money]) }}
```

---

4. 流程說明

- 你在語系檔用 `:amount` 佔位符。
- 在程式中把 `Money 物件`當作參數傳進去。
- Laravel 會 __自動呼叫你註冊的格式化邏輯，__ 把` Money 物件`轉成台幣格式字串。
- 畫面就會正確顯示「價格：NT$1,000」。

---

### *`__toString()` 方法的實際使用範例*

```php
// 定義一個有 __toString() 方法的物件
class Product
{
    public $name;
    public function __construct($name)
    {
        $this->name = $name;
    }
    // __toString 讓物件自動轉字串
    public function __toString()
    {
        return $this->name . '（超人氣商品）';
    }
}

// 語系檔（resources/lang/zh_TW/messages.php）
return [
    'product_info' => '商品資訊：:product'
];

// 實際在程式中使用
$product = new Product('iPhone 15 Pro');
// :product 會自動呼叫 $product 的 __toString() 方法
// 輸出：商品資訊：iPhone 15 Pro（超人氣商品）
echo __('messages.product_info', ['product' => $product]);
```
- 如果你沒用 `Lang::stringable()`，Laravel 會 __自動呼叫物件__ 的 `__toString()` 方法，讓你可以直接把物件當作語系字串參數顯示自訂格式。 

- 如果物件沒有 `__toString()` 方法，也沒用 `Lang::stringable()`，
  直接當作語系字串參數時會發生錯誤（通常是 `TypeError`），
  因為 PHP 不知道怎麼把物件轉成字串。

---

## 8. **複數化**（Pluralization）

- *短鍵語系檔*：

  ```php
  'apples' => '有一顆蘋果|有很多顆蘋果',
  ```

---

- *JSON 語系檔*：

  ```json
  {
    "There is one apple|There are many apples": "有一顆蘋果|有很多顆蘋果"
  }
  ```

---

- *多區間*：

  ```php
  'apples' => '{0} 沒有蘋果|[1,19] 有一些蘋果|[20,*] 有很多蘋果',
  ```

---

- *取得複數翻譯*：

  ```php
  echo trans_choice('messages.apples', 10);
  // 根據數量自動選擇
  ```

---

- *帶參數*：

  ```php
  'minutes_ago' => '{1} :value 分鐘前|[2,*] :value 分鐘前',
  echo trans_choice('time.minutes_ago', 5, ['value' => 5]);
  ```

---

- *:count 內建佔位符*：

  ```php
  'apples' => '{0} 沒有蘋果|{1} 只有一顆|[2,*] 有 :count 顆',
  ```

---

## 9. **Pluralizer 多語系**

`Eloquent` 等會用 `pluralizer` 進行 *複數化*，__預設英文__。可在 `AppServiceProvider` 設定：

```php
use Illuminate\Support\Pluralizer;
public function boot(): void
{
    Pluralizer::useLanguage('french'); // 支援 french, norwegian-bokmal, portuguese, spanish, turkish
}
```
- 若自訂 pluralizer，建議 `model table name` __顯式指定__。

<!-- 如果你自訂了 Pluralizer（複數化規則），
     例如改用法文、西班牙文等，
     Laravel 會根據語言自動推斷模型的資料表名稱（複數型態），
     但有些語言規則不同，可能推斷結果不是你想要的，
     所以建議你在模型裡明確指定 protected $table = 'your_table_name';，
     避免自動推斷出錯。 -->

```php
use Illuminate\Support\Pluralizer;

public function boot(): void
{
    // 切換複數推定語言為西班牙文
    Pluralizer::useLanguage('spanish');
}

// 例如：模型名稱 Product
// Laravel 會自動推定資料表名稱為 productos（西班牙文複數）

---

// 平常使用
use Illuminate\Support\Str;

// 單數轉複數
echo Str::plural('libro'); // 輸出：libros

// 複數轉單數
echo Str::singular('libros'); // 輸出：libro
```

```php
// 顯式指定資料表名稱，就是在模型裡直接設定 $table 屬性
class Product extends Model
{
    protected $table = 'my_products'; // 明確指定資料表名稱，不用自動推定
}
// 這樣不管 Pluralizer 用哪國語言，Eloquent 都會用你指定的 my_products 作為資料表名稱，
// 不會自動推定，也不會因語言切換而出錯。
// 顯式指定 $table 就是不套用複數化規則。
```

---

## 10. **套件語系覆寫**

如果你安裝的套件有自己的語言檔，
你可以在` lang/vendor/{套件名稱}/{語言}` 路徑下建立檔案 __來覆寫套件的語言內容__。

例如：

`lang/vendor/hearthfire/zh-TW/messages.php`

只要在覆寫檔案裡定義 _你要修改的 key_，
_沒定義的 key_ 會自動使用原套件的語言檔內容，不會影響其他翻譯。

---

- __官方範例補充__：

  - 假設套件名稱為 `skyrim/hearthfire`，要覆寫英文語系的 `messages.php`，只需建立：

    `lang/vendor/hearthfire/en/messages.php`
    
  - 檔案內容只需放 __要覆寫的 key__，_其餘會`自動` fallback 至套件原始語系檔_。
  
```php
// key 覆寫的意義：
// 你只要在覆寫檔案裡定義「想要改的 key」（即你要自訂的翻譯字串），其他沒定義的 key 會自動 fallback 用原套件語系內容。
// 這樣可以只改你需要的字串，不用整份語系檔都複製，維護更簡單。
// 覆寫流程：
// 1. 原本套件 messages.php 可能有：
    return [
      'welcome' => 'Welcome to Hearthfire!',
      'logout' => 'Logout',
      'profile' => 'Profile',
    ];
// 2. 你只想改 welcome，就在 lang/vendor/hearthfire/zh-TW/messages.php：
    return [
      'welcome' => '歡迎來到 Hearthfire！',
    ];
// 3. 結果：
    __('messages.welcome') 會顯示「`歡迎來到 Hearthfire！`」（你覆寫的內容）
    __('messages.logout')、__('messages.profile') 會自動 fallback 用原本套件內容
// 這就是 Laravel 語系 key 覆寫的設計，讓你只需維護有差異的部分，其他自動繼承。
```

---

## 11. **注意事項**

- 語系 `key 不可與檔名衝突`，否則會回傳整個檔案內容。
- 地區語系目錄建議用` ISO 15897 格式`（如 __zh_TW、en_GB__）。

---

## 12. **常用 artisan 指令**

- *發佈語系檔*：

  ```bash
  php artisan lang:publish
  ```

---

- *快速切換語系*（測試用）：
  ```php
  // App::setLocale('zh-TW'); 用法說明：
  // 這行會把 Laravel 應用程式目前語系切換成繁體中文（zh-TW），影響本次請求所有多語系顯示。
  // 常見用法：
  //   1. 使用者切換語言時，controller 裡呼叫 App::setLocale('zh-TW')。
  //   2. API 根據 header 或參數自動切換語系。
  // 注意：只影響本次 request，不會永久改變 config/app.php 的預設語系。
  // 範例：
  use Illuminate\Support\Facades\App;
  App::setLocale('zh-TW'); // 之後 __('messages.welcome') 會載入 zh-TW 語系內容
  ```

---

## 13. **實作範例**

### 1. *建立語系檔*

```shell
php artisan lang:publish
```

---

### 2. *新增自訂語系內容*

```php
// lang/zh-TW/messages.php
return [
    'greeting' => '哈囉 :name',
    'apples' => '{0} 沒有蘋果|{1} 只有一顆|[2,*] 有 :count 顆',
];
```

---

### 3. *取得翻譯*

```php
echo __('messages.greeting', ['name' => '小明']);
// 哈囉 小明

echo trans_choice('messages.apples', 0); // 沒有蘋果
echo trans_choice('messages.apples', 1); // 只有一顆
echo trans_choice('messages.apples', 5, ['count' => 5]); // 有 5 顆
```

---

### 4. *AppServiceProvider 註冊 stringable/pluralizer*

```php
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Pluralizer;
use Money\Money;

public function boot(): void
{
    Lang::stringable(function (Money $money) {
        return $money->formatTo('zh_TW');
    });
    Pluralizer::useLanguage('french');
}
``` 

---
