# *Laravel 語系（Localization）完整筆記*

---
在 Laravel 中，localization（本地化）指的是讓你的應用程式 **能夠支援多語系**，根據使用者的語言或地區顯示不同的文字內容。這通常包含「翻譯字
串」、「日期/時間格式」、「數字/貨幣格式」等，讓同一個網站能自動切換成繁體中文、英文、日文等不同語言。


## 1. **什麼是 Localization？**
-  *本地化（localization, l10n）*：讓你的系統根據「使用者的語言、地區」自動切換顯示內容。
-  例如：中文用戶看到「歡迎！」，英文用戶看到「Welcome!」。
-  國際化（internationalization, i18n）：讓系統具備多語系能力，localization 則是實際切換與顯示。

## 2. **Laravel 如何做 Localization？**

### (1) *語言檔*（Language Files）
-  所有翻譯字串都放在 `resources/lang/{語系}/` 目錄下。
-  每個語系目錄下有多個 PHP 檔案，每個檔案回傳一個 key=>value 陣列。

### (2) *取得翻譯字串*
-  用 **__('messages.welcome')** 或 **@lang('messages.welcome')** 取得對應語系的翻譯。
-  例如：
-  **resources/lang/zh_TW/messages.php**: return ['welcome' => '歡迎！'];
    這是 Laravel 語言檔的「繁體中文」版本，檔案放在 resources/lang/zh_TW/messages.php，內容是 key 為 'welcome'，value 為『歡迎！』。
    只要系統語言設定為 zh_TW，__('messages.welcome') 會顯示『歡迎！』。
-  **resources/lang/en/messages.php**: return ['welcome' => 'Welcome!'];
    這是 Laravel 語言檔的「英文」版本，檔案放在 resources/lang/en/messages.php，內容是 key 為 'welcome'，value 為 'Welcome!'。
    只要系統語言設定為 en，__('messages.welcome') 會顯示 'Welcome!'。
-  **在 Blade 或 Controller**：echo __('messages.welcome');
    用 __('messages.welcome') 這個方法，Laravel 會根據目前語系自動選擇正確的語言檔，顯示對應語言內容。
    例如：
      - 當語系為 zh_TW，畫面會顯示『歡迎！』
      - 當語系為 en，畫面會顯示 'Welcome!'
    這是 Laravel 多語系最基本、最常用的做法，讓你輕鬆支援多國語言。

### (3) *切換語系*
-  預設語系在 **config/app.php** 的 locale 設定。
-  也可用 **App::setLocale('en')** 動態切換語系。

### (4) *複數、參數替換*
-  支援**複數型態**（如 apple/apples）、**動態參數**（如 :count、:name）。
-  例如：trans_choice('messages.apples', 5, ['count' => 5]);

### (5) *系統訊息本地化*
-  Laravel 內建的驗證、認證、密碼重設等訊息都可在 **resources/lang/{語系}/** 下找到並自訂。

## 3. **實際應用場景**
-  多國語系網站、後台管理系統、API 回應、日期/時間/貨幣格式自動切換。

## 4. **小結**
-  localization 在 Laravel 就是「讓你的系統能多語系顯示、在地化所有內容」。
-  只要維護好語言檔，程式就能自動根據語系切換所有訊息。
-  這對國際化、台灣/香港/大陸/海外多語網站都非常重要。

-  如需完整多語系專案範例、進階本地化技巧、如何自動偵測使用者語言等，也可以再問我！

---

## 1. **語系檔案結構**

Laravel 支援兩種語系檔案管理方式：

- *陣列語系檔（短鍵）*：
  - `lang/{locale}/messages.php`
  - 適合結構化、分群管理
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
- *JSON 語系檔（以原文為 key）*：
  Laravel 也支援 JSON 格式的語言檔，**key 為原文**（通常是英文），**value 為翻譯內容**。
  這種格式適合大量、動態字串，或不想手動定義 key 的情境。
  - `lang/en.json`, `lang/zh-TW.json`
    每個語系一個 JSON 檔，放在 resources/lang 目錄下。

  - 適合大量、動態字串
    只要在程式中用 __('原文')，Laravel 會自動對應到 JSON 檔的翻譯。

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

產生後結構範例：
```
/lang
  /en/messages.php
  /zh-TW/messages.php
  en.json
  zh-TW.json
```

---

## 3. **語系設定**

- `config/app.php`：
  ```php
  'locale' => env('APP_LOCALE', 'en'),
  'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
  ```
- `.env`：
  ```env
  APP_LOCALE=zh-TW
  APP_FALLBACK_LOCALE=en
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

取得目前語系：
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
- *JSON 語系檔*：
  ```php
  __('I love programming.')
  // 若不存在，回傳 'I love programming.'
  ```
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
呼叫時傳入陣列：
```php
echo __('messages.welcome', ['name' => 'Vincent']);
// Welcome, Vincent
```
- 大寫/首字大寫佔位符自動對應：
  - `:NAME` → 全大寫
  - `:Name` → 首字大寫

---

## 7. **物件參數格式化（stringable）**

- Laravel 語系字串支援傳入物件參數，會自動呼叫物件的 *__toString()* 方法。
- 你也可以用 *Lang::stringable()* 全域自訂某類物件的顯示格式（如金額、日期等）。

若佔位符傳入物件，Laravel 會呼叫其 `__toString`。如需自訂格式，可在 `AppServiceProvider` 註冊：
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
-【應用情境】這樣你在語系字串中用 *:amount 佔位符*時，傳入 Money 物件會自動顯示正確格式。
-【注意】可註冊多個 stringable，支援多型、不同物件類型。

---

【實際使用範例】
假設你要顯示金額，並希望自動格式化為台幣：

```php
1. 語系檔（resources/lang/zh_TW/messages.php）

// 定義一個含有 :amount 佔位符的語系字串

return [
    'price' => '價格：:amount'
];

2. AppServiceProvider 註冊 stringable（如前面設定）
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

3. 實際在程式中使用
// 建立一個 Money 物件，並將它作為參數傳給語系字串
$money = new Money(1000, 'TWD');

// 在 Controller 或 Blade 取得翻譯字串，:amount 會自動被格式化
// 輸出：價格：NT$1,000
// PHP 寫法：
echo __('messages.price', ['amount' => $money]);

// Blade 寫法：
{{-- 假設 $money 是 Money 物件 --}}
{{ __('messages.price', ['amount' => $money]) }}

4. 流程說明
- 你在語系檔用 :amount 佔位符。
- 在程式中把 Money 物件當作參數傳進去。
- Laravel 會自動呼叫你註冊的格式化邏輯，把 Money 物件轉成台幣格式字串。
- 畫面就會正確顯示「價格：NT$1,000」。
【結論】這樣就能讓語系字串自動支援物件格式化，實現彈性又優雅的多語系顯示！
```
---

【補充】__toString() 方法的實際使用範例

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
-【說明】如果你沒用 Lang::stringable()，Laravel 會自動呼叫物件的 __toString() 方法，讓你可以直接把物件當作語系字串參數顯示自訂格式。 
---

## 8. **複數化（Pluralization）**

- *短鍵語系檔*：
  ```php
  'apples' => '有一顆蘋果|有很多顆蘋果',
  ```
- *JSON 語系檔*：
  ```json
  {
    "There is one apple|There are many apples": "有一顆蘋果|有很多顆蘋果"
  }
  ```
- *多區間*：
  ```php
  'apples' => '{0} 沒有蘋果|[1,19] 有一些蘋果|[20,*] 有很多蘋果',
  ```
- *取得複數翻譯*：
  ```php
  echo trans_choice('messages.apples', 10);
  // 根據數量自動選擇
  ```
- *帶參數*：
  ```php
  'minutes_ago' => '{1} :value 分鐘前|[2,*] :value 分鐘前',
  echo trans_choice('time.minutes_ago', 5, ['value' => 5]);
  ```
- *:count 內建佔位符*：
  ```php
  'apples' => '{0} 沒有蘋果|{1} 只有一顆|[2,*] 有 :count 顆',
  ```

---

## 9. **Pluralizer 多語系**

Eloquent 等會用 pluralizer 進行 *複數化*，預設英文。可在 `AppServiceProvider` 設定：
```php
use Illuminate\Support\Pluralizer;
public function boot(): void
{
    Pluralizer::useLanguage('french'); // 支援 french, norwegian-bokmal, portuguese, spanish, turkish
}
```
> 若自訂 pluralizer，建議 model table name 顯式指定。

---

## 10. **套件語系覆寫**

若套件有自帶語系檔，可在 `lang/vendor/{package}/{locale}` 覆寫：
```
lang/vendor/hearthfire/zh-TW/messages.php
```
只需定義要覆寫的 key，未覆寫的會 fallback 至原套件語系。

- **官方範例補充**：
  - 假設套件名稱為 `skyrim/hearthfire`，要覆寫英文語系的 messages.php，只需建立：
    ```
    lang/vendor/hearthfire/en/messages.php
    ```
  - 檔案內容只需放要覆寫的 key，其餘會自動 fallback 至套件原始語系檔。
  
```php
- // key 覆寫的意義：
- // 你只要在覆寫檔案裡定義「想要改的 key」（即你要自訂的翻譯字串），其他沒定義的 key 會自動 fallback 用原套件語系內容。
- // 這樣可以只改你需要的字串，不用整份語系檔都複製，維護更簡單。
- // 覆寫流程：
- // 1. 原本套件 messages.php 可能有：
-     return [
-       'welcome' => 'Welcome to Hearthfire!',
-       'logout' => 'Logout',
-       'profile' => 'Profile',
-     ];
- // 2. 你只想改 welcome，就在 lang/vendor/hearthfire/zh-TW/messages.php：
-     return [
-       'welcome' => '歡迎來到 Hearthfire！',
-     ];
- // 3. 結果：
-     - __('messages.welcome') 會顯示『歡迎來到 Hearthfire！』（你覆寫的內容）
-     - __('messages.logout')、__('messages.profile') 會自動 fallback 用原本套件內容
- // 這就是 Laravel 語系 key 覆寫的設計，讓你只需維護有差異的部分，其他自動繼承。
```
---

## 11. **注意事項**

- 語系 key 不可與檔名衝突，否則會回傳整個檔案內容。
- 地區語系目錄建議用 ISO 15897 格式（如 zh_TW、en_GB）。

---

## 12. **常用 artisan 指令**

- 發佈語系檔：
  ```shell
  php artisan lang:publish
  ```
- 快速切換語系（測試用）：
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

### 2. *新增自訂語系內容*
```php
// lang/zh-TW/messages.php
return [
    'greeting' => '哈囉 :name',
    'apples' => '{0} 沒有蘋果|{1} 只有一顆|[2,*] 有 :count 顆',
];
```

### 3. *取得翻譯*
```php
echo __('messages.greeting', ['name' => '小明']);
// 哈囉 小明

echo trans_choice('messages.apples', 0); // 沒有蘋果
echo trans_choice('messages.apples', 1); // 只有一顆
echo trans_choice('messages.apples', 5, ['count' => 5]); // 有 5 顆
```

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
