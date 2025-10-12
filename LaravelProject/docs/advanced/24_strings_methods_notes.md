# *Laravel 字串（Strings）方法庫*

---

## **全域函式 / 輔助方法**

- *__*：`翻譯語系`字串。
- *class_basename*：取得`類別名稱`（去除 __Namespace__）。
- *e*：HTML `字元轉義`。
- *preg_replace_array*：依序用 __陣列值__`取代字串 pattern`。
- *str*：建立 `Fluent String 實例`。
- *trans*：翻譯語系字串（`同 __）`。
- *trans_choice*：`根據數量`選擇語系字串。

---

## **Str:: 靜態方法**

- *Str::after*：取指定`字串之後`。
- *Str::afterLast*：取`最後一次`指定字串之後。
- *Str::ascii*：轉為 `ASCII`。
- *Str::apa*：`APA` 標題格式。

<!-- 
主要單字首字母大寫（如名詞、動詞、形容詞等）
連接詞、冠詞、介系詞等小字通常小寫（除非在開頭）
Str::apa('the quick brown fox jumps over the lazy dog');
// "The Quick Brown Fox Jumps Over the Lazy Dog" 
-->

- *Str::headline*：轉為`標題格式`（每個單字首字母大寫，並自動處理分隔符號）。
- *Str::title*：轉為`標題格式`（每個單字首字母大寫，但不會自動處理分隔符號）。

<!-- Str::headline('user_profile_data'); // "User Profile Data" -->
<!-- Str::title('user_profile_data');    // "User_Profile_Data" -->

- *Str::before*：取指定`字串之前`。
- *Str::beforeLast*：取`最後一次指`定字串之前。
- *Str::between*：取兩`字串之間`。
- *Str::betweenFirst*：取`最小範圍`兩字串之間。

- *Str::camel*：轉 `camelCase`。
- *Str::charAt*：取指定`索引字元`。

- *Str::chopStart*：`移除開頭`指定字串。
- *Str::chopEnd*：`移除結尾`指定字串。

- *Str::contains*： __是否__`包含`指定字串。
- *Str::containsAll*： __是否__ 同時`包含多個`字串。
- *Str::doesntContain*： __是否__`不包含`指定字串。


- *Str::deduplicate*：`合併重複`字元。

- *Str::excerpt*：`擷取關鍵字片段`。

- *Str::finish*：`結尾補上`指定字串。
- *Str::fromBase64*：`Base64 解碼`。

- *Str::inlineMarkdown*：將單行或內嵌的 Markdown 文本轉換為 HTML。
- *Str::markdown*：將完整的 Markdown 文本轉換為 HTML。

- *Str::is*： __是否__`符合模式`。
- *Str::isAscii*： __是否__ 為 `ASCII`。
- *Str::isJson*： __是否__ 為 `JSON 字串`。
- *Str::isUlid*： __是否__ 為 `ULID`。
- *Str::isUrl*： __是否__ 為 `URL`。
- *Str::isUuid*： __是否__ 為 `UUID`。

- *Str::kebab*：轉 kebab-case。

- *Str::length*：字串`長度`。
- *Str::limit*：`限制長度`。

- *Str::mask*：`遮罩`字串。
- *Str::match*：`正則比對`。
- *Str::matchAll*：`正則全比對`。

- *Str::orderedUuid*：產生`有序 UUID`。

- *Str::padBoth*：`左右`補齊。
- *Str::padLeft*：`左`補齊。
- *Str::padRight*：`右`補齊。

- *Str::singular*：轉`單數`。
- *Str::plural*：轉`複數`。

- *Str::password*：產生`隨機密碼`。
- *Str::pluralStudly*：Studly 複數。
- *Str::position*：尋找`字串位置`。
- *Str::studly*：轉 `StudlyCase`。

<!-- 
StudlyCase：每個單字首字母大寫、沒有分隔符號，例如：UserProfile、OrderItem
Studly 複數：就是把 StudlyCase 字串轉成複數，例如：UserProfile → UserProfiles
Laravel 的 Str::studly() 會把字串轉成 StudlyCase，
而複數通常用 Str::pluralStudly()（Laravel 11 新增）來處理。 
-->

<!-- 
StudlyCase（帕斯卡命名法）：每個單字首字母都大寫，例如：UserProfile、OrderItem
camelCase（駱駝命名法）：第一個單字首字母小寫，後面每個單字首字母大寫，例如：userProfile、orderItem 
-->

- *Str::random*：`隨機字串`。
- *Str::remove*：`移除`指定字串。
- *Str::repeat*：`重複`字串。

- *Str::replace*：`取代`字串。
- *Str::replaceArray*：陣列`依序`取代。
- *Str::replaceFirst*：取代`第一個`。
- *Str::replaceLast*：取代`最後一個`。
- *Str::replaceMatches*：`正則`取代。
- *Str::replaceStart*：`開頭`取代。
- *Str::replaceEnd*：`結尾`取代。

- *Str::reverse*：`反轉`字串。

- *Str::slug*：產生 `slug`。
- *Str::snake*：轉 `snake_case`。
- *Str::squish*：`壓縮空白`。

- *Str::start*：`開頭補上`指定字串。
- *Str::startsWith*： __是否__ 以指定字串`開頭`。
- *Str::doesntStartWith*： __是否__`不以`指定字串`開頭`。

- *Str::endsWith*： __是否__`以`指定字串`結尾`。
- *Str::doesntEndWith*： __是否__`不以`指定字串`結尾`。

- *Str::substr*：子字串。
- *Str::substrCount*：子字串`出現次數`。
- *Str::substrReplace*：子字串`取代`。

- *Str::swap*：多組字串`交換`。

- *Str::toBase64*：Base64 編碼。
- *Str::transliterate*：音譯。

- *Str::words*：取`前 n 個單字`。
- *Str::take*：取`前/後 n 字元`。

<!-- 
Str::words('Laravel is a powerful framework', 3, '...'); 
// "Laravel is a..." 
-->

<!-- 
Str::take('Laravel', 4);   
// "Lara"

Str::take('Laravel', -3);  
// "vel" 
-->

- *Str::trim*：去除`首尾`空白。
- *Str::ltrim*：去除`左側`空白。
- *Str::rtrim*：去除`右側`空白。

- *Str::upper*：轉`大寫`。
- *Str::lower*：轉`小寫`。
- *Str::ucfirst*：首字`大寫`。
- *Str::lcfirst*：首字`小寫`。
- *Str::ucsplit*：依大寫`分割為陣列`。

<!-- 
Laravel 11 新增了 Str::ucsplit() 方法，
可以根據大寫字母分割字串，
但沒有 lcsplit 方法。
-->

- *Str::ulid*：產生 ULID。
- *Str::uuid*：產生 UUID。
- *Str::uuid7*：產生 UUIDv7。

- *Str::wordCount*：單字數。
- *Str::wordWrap*：`自動換行`。

- *Str::unwrap*：`去除`包裹字元。
- *Str::wrap*：`包裹`字串。 

---

## **Str:: 靜態方法獨有**

- *afterArray*：多組 `after` 批次處理。

- *beforeArray*：多組 `before` 批次處理。

- *orderedUuid*：產生`有序 UUID`。

- *password*：產生`隨機密碼`。

- *random*：產生`隨機字串`。

- *replaceFirstArray*：多組 `replaceFirst` 批次處理。
- *replaceLastArray*：多組 `replaceLast` 批次處理。

- *ulid*：產生 ULID。
- *uuid*：產生 UUID。
- *uuid7*：產生 UUID v7。

- __補充__：大多數 Str:: 靜態方法都能用 `Fluent String 物件`鏈式呼叫，但上述這些「_產生型_」或「_批次處理型_」方法僅存在於 `Str:: 靜態方法`，Fluent String 物件沒有。

---

## **Fluent String 方法索引**

- *after*：取得指定字串`之後`的內容。
- *afterLast*：取得`最後一次`指定字串之後的內容。
- *append*：在`字串後面`加上內容。（**僅 Fluent String**）
- *prepend*：在`前面`加上內容。（**僅 Fluent String**）
- *ascii*：轉為 `ASCII` 字元。

- *apa*：轉為 `APA` 標題格式（主要單字首字母大寫，連接詞等小字小寫）。
- *headline*：轉為`標題格式`（每個單字首字母大寫，並自動處理分隔符號）。
- *title*：轉為`標題格式`（每個單字首字母大寫，不處理分隔符號）。

- *basename*：取得`路徑的檔名`。
- *before*：取得指定`字串之前`的內容。
- *beforeLast*：取得`最後一次`指定字串之前的內容。
- *between*：取得兩個`字串之間`的內容。
- *betweenFirst*：取得`第一組出現`的兩個字串之間的內容。

- *camel*：轉為 `camelCase`。
- *charAt*：取得指定`索引`的字元。
- *classBasename*：取得`類別名稱`（去除 __Namespace__）。

- *chopStart*：`移除開頭`指定內容。
- *chopEnd*：`移除結尾`指定內容。

- *contains*：判斷 __是否__`包含`指定內容。
- *containsAll*：判斷 __是否__ 同時`包含`多個內容。

- *encrypt*：`加密字串`。（**僅 Fluent String**）
- *decrypt*：`解密字串`。（**僅 Fluent String**）

- *deduplicate*：`合併連續重複字元`。
- *dirname*：取得`路徑的上層目錄`。（**僅 Fluent String**）

- *endsWith*：`判斷結尾` __是否__ 為指定內容。
- *doesntEndWith*：判斷結尾 __不是__ 指定內容。

- *start*：確保`開頭`有指定內容。
- *startsWith*：判斷`開頭` __是否__ 為指定內容。
- *doesntStartWith*：判斷開頭 __不是__ 指定內容。

- *exactly*：判斷 __是否__ `完全相同`。（**僅 Fluent String**）
- *excerpt*：擷取`關鍵字附近片段`。
- *explode*：用分隔符`切割`字串為 Collection。（**僅 Fluent String**）

- *finish*：確保`結尾有指定內容`。
- *fromBase64*：Base64 解碼。

- *hash*：`雜湊字串`。（**僅 Fluent String**）

- *inlineMarkdown*：Markdown 轉 inline HTML。
- *markdown*：Markdown 轉 HTML。

- *is*：判斷`是否`符合萬用字元模式。
- *isAscii*：判斷`是否`為 ASCII 字元。
- *isEmpty*：判斷`是否`為空字串。（**僅 Fluent String**）
- *isNotEmpty*：判斷`是否`不為空字串。（**僅 Fluent String**）
- *isJson*：判斷`是否`為合法 JSON。
- *isUlid*：判斷`是否`為 ULID。
- *isUrl*：判斷`是否`為合法 URL。
- *isUuid*：判斷`是否`為 UUID。
- *isMatch*：判斷`是否`符合正則。（**僅 Fluent String**）

- *kebab*：轉為 `kebab-case`。

- *upper*：轉為`大寫`。
- *lower*：轉為`小寫`。
- *ucfirst*：`首字大寫`。
- *lcfirst*：`首字小寫`。
- *ucsplit*：依大寫`分割為陣列`。

- *split*：`正則分割`字串為 Collection。（**僅 Fluent String**）

- *length*：取得字串`長度`。
- *limit*：`限制`字串長度。

- *mask*：`遮蔽`部分字元。
- *match*：`正則`擷取第一個符合內容。
- *matchAll*：`正則`擷取所有符合內容。

- *newLine*：加上`換行符號`。（**僅 Fluent String**）

- *padBoth*：`左右`補滿至指定長度。
- *padLeft*：`左側`補滿至指定長度。
- *padRight*：`右側`補滿至指定長度。

- *pipe*：`傳入函式或閉包處理`。（**僅 Fluent String**）
- *singular*：轉為`單數`。
- *plural*：轉為`複數`。
- *position*：取得`子字串首次出現位置`。

- *remove*：`移除`指定內容。
- *repeat*：`重複`字串。

- *replace*：`取代`內容。
- *replaceArray*：`依序用陣列`內容取代。
- *replaceFirst*：只取代`第一個`符合內容。
- *replaceLast*：只取代`最後一個`符合內容。
- *replaceMatches*：`正則`取代所有符合片段。
- *replaceStart*：`開頭為指定內容`才取代。
- *replaceEnd*：`結尾為指定內容`才取代。

- *scan*：依 `sscanf 格式`解析字串。（**僅 Fluent String**）
- *slug*：轉為` URL 友善格式`。
- *snake*：轉為 `snake_case`。
- *squish*：`移除多餘空白`。

<!-- 
squish 會移除字串中所有多餘空白（包含中間、前後），
只保留單一空白分隔，
而 trim 只會移除字串前後的空白，不會處理中間。 
-->

- *stripTags*：`移除 HTML 標籤`。（**僅 Fluent String**）
- *studly*：轉為 `StudlyCase`。

- *substr*：`擷取`子字串。
- *substrReplace*：子字串`取代`。

- *swap*：多組對應值`批次取代`。

- *take*：`取`前/後幾個字元。
- *tap*：執行閉包後`回傳自身`。（**僅 Fluent String**）
- *test*：`正則測試` __是否__ 符合。（**僅 Fluent String**）
- *toBase64*：Base64 編碼。
- *toHtmlString*：轉為 `HtmlString 物件`。（**僅 Fluent String**）
- *toUri*：轉為 `URI 格式`。（**僅 Fluent String**）
- *transliterate*：音譯字串。

- *trim*：去除`前後`空白。
- *ltrim*：去除`左側`空白。
- *rtrim*：去除`右側`空白。

- *when*：`條件成立時`執行閉包。（**僅 Fluent String**）
- *whenContains*：包含指定`內容時`執行閉包。（**僅 Fluent String**）
- *whenContainsAll*：同時`包含多個內容`時執行閉包。（**僅 Fluent String**）

- *whenStartsWith*：`開頭為指定內容時`執行閉包。（**僅 Fluent String**）
- *whenDoesntStartWith*：`開頭不是指定內容時`執行閉包。（**僅 Fluent String**）

- *whenEndsWith*：`結尾為指定內容時`執行閉包。（**僅 Fluent String**）
- *whenDoesntEndWith*：`結尾不是指定內容時`執行閉包。（**僅 Fluent String**）


- *whenEmpty*：`為空時`執行閉包。（**僅 Fluent String**）
- *whenNotEmpty*：`不為空時`執行閉包。（**僅 Fluent String**）

- *whenExactly*：`完全相同時`執行閉包。（**僅 Fluent String**）
- *whenNotExactly*：`不完全相同時`執行閉包。（**僅 Fluent String**）

- *whenIs*：`符合萬用字元模式時`執行閉包。（**僅 Fluent String**）
- *whenIsAscii*：為 ASCII 時執行閉包。（**僅 Fluent String**）
- *whenIsUlid*：為 ULID 時執行閉包。（**僅 Fluent String**）
- *whenIsUuid*：為 UUID 時執行閉包。（**僅 Fluent String**）

- *whenTest*：`符合正則時`執行閉包。（**僅 Fluent String**）

- *wordCount*：`計算單字數量`。（**僅 Fluent String**）
- *words*：`限制`單字數。

- *wrap*：`包裹`字串。
- *unwrap*：`去除`開頭與結尾指定字元。

---

# *詳細說明*

## **全域函式 / 輔助方法**

### *__()*

`翻譯語系檔案中`的字串或 key。

```php
echo __('Welcome to our application');
echo __('messages.welcome'); // 
```

<!-- 
messages 是語言檔名（如 resources/lang/zh-TW/messages.php）
welcome 是語言檔裡的 key
這樣會取得 messages.php 裡 welcome 對應的翻譯內容。 
-->

<!-- 
__('messages.welcome')：單維（只有一層 key）
__('messages.user.login')：多維（user 是第一層 key，login 是第二層 key）
__('messages.order.success')：多維（order 是第一層 key，success 是第二層 key） 
-->

- *若 key 不存在，會直接回傳原字串*
  - 如果查不到翻譯，Laravel 會`直接回傳你查詢的 key`，不會報錯或回傳空白，方便偵錯與開發。

  __範例__：
    - 假設語系檔沒有 `messages.hello` 這個 key
    
    ```php
    echo __('messages.hello'); // 輸出：messages.hello
    ```

  - __註解__：查不到翻譯時，畫面會直接顯示 key，方便你發現遺漏的翻譯。

---

### *class_basename()*

取得`類別名稱`（去除 __namespace__）。

```php
$class = class_basename('Foo\Bar\Baz'); // Baz
```

- __註解__：`class_basename` 可用來取得類別的「_短名稱_」，常用於`顯示、日誌、動態產生檔名`等場合，讓你不用處理冗長的 namespace 路徑。

---

### *e()*

執行 PHP 的 `htmlspecialchars`，**預設** `double_encode` 為 `true`。

```php
echo e('<html>foo</html>'); // &lt;html&gt;foo&lt;/html&gt;
```

- __註解__：`e()` 主要用於將 HTML 特殊字元轉為*安全的實體*，*防止 XSS 攻擊*。常用於 Blade 模板或任何需要安全輸出的場合，確保用戶輸入不會被當成 HTML 執行。

---

### *preg_replace_array()*

依序用`陣列值取代`字串中的 pattern。

```php
$string = 'The event will take place between :start and :end';
// 字串裡有兩個佔位符 :start 和 :end
$replaced = preg_replace_array('/:[a-z_]+/', ['8:30', '9:00'], $string);
// The event will take place between 8:30 and 9:00
```

- __註解__：`preg_replace_array()` 會根據你 __提供的正則 pattern__，從左到右依序尋找字串中每個符合的部分，然後用陣列中的值一一取代。

- 適合用於模板字串的 __多參數動態填值、SQL 查詢組裝、訊息自動帶入__ 多個參數等情境。

- __範例__：

    ```php
    $template = 'Hi :name, your order #:order will be delivered at :time.';
    $result = preg_replace_array('/:[a-z_]+/', ['Vincent', 'A123', '18:00'], $template);
    ```

- __結果__：
    
    ```php
    'Hi Vincent, your order #A123 will be delivered at 18:00.'
    ```

---

### *str()*

回傳 `Stringable` 實例，可鏈式操作字串，相當於 `Str::of`。

```php
$string = str('Taylor')->append(' Otwell'); // 'Taylor Otwell'
// 不傳參數時回傳 Str 實例
$snake = str()->snake('FooBar'); // 'foo_bar'
```

- __註解__：`str()` 讓你可以用 __物件導向、鏈式語法__ 處理字串，讓多步驟字串處理更直覺、可讀性更高。常用於需要多次轉換、組合、格式化字串的場合。

---

### *trans()*

`翻譯語系 key`，若不存在則回傳 key。

```php
echo trans('messages.welcome');
```

- __註解__：`trans()` 用於 __多語系網站__，_根據 key 取得對應語系檔的翻譯內容_。若 key 不存在，會直接回傳 key 本身，方便偵錯。
<!-- 
trans() 和 __() 在 Laravel 裡功能幾乎一樣，
都是用來取得語言檔的翻譯內容，
__('messages.welcome') 和 trans('messages.welcome') 都會輸出 歡迎光臨。

差異：
__() 是 Laravel 推薦的新寫法，語法更簡潔。
trans() 是舊寫法，功能相同，仍可用。
-->

- __範例__：

    ```php
    <!-- resources/lang/zh-TW/messages.php 內容： -->
    return ['welcome' => '歡迎光臨'];
    echo trans('messages.welcome'); // 輸出：歡迎光臨
    ```

---

### *trans_choice()*

`根據數量`選擇語系 key，若不存在則回傳 key。

```php
echo trans_choice('messages.notifications', $unreadCount);
```

- __註解__：`trans_choice()` 用於*根據數量自動選擇單數/複數*等不同翻譯內容，常見於 __通知、商品數量__ 等場合。

- __範例__：

```php
    // resources/lang/zh-TW/messages.php 內容：
    return ['notifications' => '{0} 沒有新通知|{1} 你有一則新通知|[2,*] 你有 :count 則新通知'];
    // 說明：
    // {0} 代表數量為 0 時顯示「沒有新通知」
    // {1} 代表數量為 1 時顯示「你有一則新通知」
    // [2,*] 代表數量為 2 以上時顯示「你有 :count 則新通知」，其中 :count 會自動帶入實際數字
    echo trans_choice('messages.notifications', 0); // 輸出：沒有新通知
    echo trans_choice('messages.notifications', 1); // 輸出：你有一則新通知
    echo trans_choice('messages.notifications', 5); // 輸出：你有 5 則新通知
```

---

## **Str:: 靜態方法**

### *Str::after()*

回傳指定`字串之後`的內容，若找不到則回傳原字串。

```php
use Illuminate\Support\Str;
$slice = Str::after('This is my name', 'This is'); // ' my name'
```

---

### *Str::afterLast()*

回傳`最後一次出現指定字串之後`的內容，若找不到則回傳原字串。

```php
$slice = Str::afterLast('App\Http\Controllers\Controller', '\\'); // 'Controller'
```
<!-- 
在 PHP 字串裡，
反斜線 \ 是跳脫字元，
如果你只寫 '\Controller'，PHP 會把 \C 當成特殊字元，
所以要用兩個反斜線 \\，代表字串裡的單一反斜線。
 -->

---

### *afterArray()*

- __用途__

多組 after 批次處理，依序對`字串陣列`每個元素執行 after。

- __語法__

```php
$result = Str::afterArray(['foo:bar', 'baz:qux'], ':'); // ['bar', 'qux']
```

- __註解__：可用於`批次處理多個字串`，取得 *`每個字串`指定分隔符後* 的內容。

- __白話解釋__：就是「一次處理一堆字串，全部都 after」。

- __應用場景__：批次解析多個 `key:value` 字串。

---

### *beforeArray()*

- __用途__

多組 before 批次處理，依序對`字串陣列`每個元素執行 before。

- __語法__

```php
$result = Str::beforeArray(['foo:bar', 'baz:qux'], ':'); // ['foo', 'baz']
```

- __註解__：可用於批次處理多個字串，取得 *`每個字串`指定分隔符前* 的內容。

- __白話解釋__：就是「一次處理一堆字串，全部都 before」。

- __應用場景__：批次解析多個 `key:valu`e` 字串。

---

### *Str::apa()*

依 `APA 標準`將字串轉為標題格式。

```php
$title = Str::apa('Creating A Project'); // 'Creating a Project'
```

- __註解__：apa 是「_APA 標題格式_」（美國心理學會出版規範），會`將每個單字的第一個字母大寫`（__連接詞、介系詞、冠詞__ 等除外），常用於學術論文、書籍標題等標準化英文標題。

---

### *Str::ascii()*

嘗試將字串轉為 `ASCII`。

```php
$slice = Str::ascii('û'); // 'u'
```

<!-- 
ASCII（American Standard Code for Information Interchange，美國資訊交換標準碼）
是一種字元編碼標準，
用來表示英文字母、數字、符號等，
每個字元都對應一個數值（0~127），
常用於電腦、程式設計和網路通訊。 
-->

---

### *Str::before()*

回傳指定`字串之前`的內容。

```php
$slice = Str::before('This is my name', 'my name'); // 'This is '
```

---

### *Str::beforeLast()*

回傳`最後一次出現指定字串之前`的內容。

```php
$slice = Str::beforeLast('This is my name, is it?', 'is'); // 'This is my name, '
```

---

### *Str::between()*

回傳兩個`字串之間`的內容。

```php
$slice = Str::between('This is my name', 'This', 'name'); // ' is my '
```

---

### *Str::betweenFirst()*

回傳`最小範圍內兩個字串之間`的內容。

```php
$slice = Str::betweenFirst('[a] bc [d]', '[', ']'); // 'a'
// 只會取得第一個 [ 和第一個 ] 之間的內容，
// 也就是 'a'，
// bc 不會被包含在結果裡。
```

---

### *Str::camel()*

將字串轉為 `camelCase`。

```php
$converted = Str::camel('foo_bar'); // 'fooBar'
```

---

### *Str::charAt()*

取得指定`索引的字元`，超出範圍回傳 `false`。

```php
$character = Str::charAt('This is my name.', 6); // 's'
```

---

### *Str::chopStart()*

若 __字串開頭為指定值__，`移除第一個`出現的該值。

```php
$url = Str::chopStart('https://laravel.com', 'https://'); // 'laravel.com'
// 可傳陣列，任一符合即移除
$url = Str::chopStart('http://laravel.com', ['https://', 'http://']); // 'laravel.com'
```

---

### *Str::chopEnd()*

若 __字串結尾為指定值__，`移除最後一個`出現的該值。

```php
$url = Str::chopEnd('app/Models/Photograph.php', '.php'); // 'app/Models/Photograph'
// 可傳陣列，任一符合即移除
$url = Str::chopEnd('laravel.com/index.php', ['/index.html', '/index.php']); // 'laravel.com'
```

---

### *Str::contains()*

判斷字串 __是否__ `包含`指定值，**預設**_區分大小寫_。

```php
$contains = Str::contains('This is my name', 'my'); // true
$contains = Str::contains('This is my name', ['my', 'foo']); // true
$contains = Str::contains('This is my name', 'MY', ignoreCase: true); // true
```

---

### *Str::containsAll()*

判斷字串 __是否__ 同時`包含陣列中所有值`。

```php
$containsAll = Str::containsAll('This is my name', ['my', 'name']); // true
$containsAll = Str::containsAll('This is my name', ['MY', 'NAME'], ignoreCase: true); // true
```

- __註解__：`containsAll()` 會檢查字串 __是否__ 同時包含陣列中的所有子字串。

- `ignoreCase`: true 代表*比對時忽略大小寫*。
- 例如：'This is my name' 同時包含 'MY' 和 'NAME'（不分大小寫），所以回傳 `true`。

---

### *Str::doesntContain()*

判斷字串 __是否__ `不包含指定值`，**預設**_區分大小寫_。

```php
$doesntContain = Str::doesntContain('This is name', 'my'); // true
$doesntContain = Str::doesntContain('This is name', ['my', 'foo']); // true
$doesntContain = Str::doesntContain('This is name', 'MY', ignoreCase: true); // true
```

---

### *Str::deduplicate()*

將 __`連續重複的字元`合併為一個__，**預設**_為空白_。

```php
$result = Str::deduplicate('The   Laravel   Framework'); // 'The Laravel Framework'
$result = Str::deduplicate('The---Laravel---Framework', '-'); // 'The-Laravel-Framework'
```

---

### *Str::doesntEndWith()*

判斷字串`是否不以`指定值結尾。

```php
$result = Str::doesntEndWith('This is my name', 'dog'); // true
$result = Str::doesntEndWith('This is my name', ['this', 'foo']); // true
$result = Str::doesntEndWith('This is my name', ['name', 'foo']); // false
```

- __註解__：

- *第一行*：原字串不是以 'dog' 結尾，回傳 `true`。
- *第二行*：原字串結尾不是 'this' 也不是 'foo'，回傳 `true`。
- *第三行*：原字串結尾是 'name'，所以「不是」這個條件不成立，回傳 `false`。

- *補充說明*：
    - 當第`二個參數`是**陣列**時，只要原字串有「__以陣列裡任一個字串__」開頭/結尾，就會回傳 `false`。
    - 只有當全部都不符合時，才會回傳 `true`。

---

### *Str::doesntStartWith()*

判斷字串`是否不以`指定值開頭。

```php
$result = Str::doesntStartWith('This is my name', 'That'); // true
$result = Str::doesntStartWith('This is my name', ['This', 'That', 'There']); // false
```

---

### *Str::endsWith()*

判斷字串`是否以`指定值結尾。

```php
$result = Str::endsWith('This is my name', 'name'); // true
$result = Str::endsWith('This is my name', ['name', 'foo']); // true
$result = Str::endsWith('This is my name', ['this', 'foo']); // false
```

---

### *Str::excerpt()*

__`擷取`包含指定關鍵字的片段，並在`前後`自動加上省略符號__（可自訂`半徑`與`省略字串`，預設為 ...）。

<!-- 
excerpt 英文意思是「摘錄」、「節選」，
通常指從文章或內容中擷取一小段重點文字。 
-->

```php
$excerpt = Str::excerpt('This is my name', 'my', ['radius' => 3]); // '...is my na...'
// 取得包含關鍵字 'my' 並前後各取 3 個字元的摘錄，超出部分用 ... 取代
$excerpt = Str::excerpt('This is my name', 'name', ['radius' => 3, 'omission' => '(...) ']); // '(...) my name'
```

- __註解__：`excerpt` 會 _從字串中擷取出包含關鍵字的片段_，
           `radius` 代表 _關鍵字左右_ 各取幾個字元，
           `omission` 可自訂 _省略字串_。

- 例如第一行，會抓出 'my' 左右各 3 個字元，前後加上 '...'，結果為 '...is my na...'。
- 適合用於搜尋**結果摘要**、**重點片段顯示**等場景。
- __補充說明__：
    - `radius` 是「__最多__」*左右各取幾個字元*，但遇 __到空白、標點、字串邊界時，實際取到的字元可能會少於設定值__。
    - 這是為了讓片段顯示更自然、不會斷詞。

```php
use Illuminate\Support\Str;

// 原字串
$text = 'Hello, this is my name!';

// 擷取 'my'，radius 設 3
$excerpt = Str::excerpt($text, 'my', ['radius' => 3]);
// 結果: "...is my na..."
// 左右各取最多 3 個字元，但遇到空白或標點就停止

// 擷取 'Hello'，radius 設 5
$excerpt = Str::excerpt($text, 'Hello', ['radius' => 5]);
// 結果: "Hello, th..."
// 因為 'Hello' 已在字串開頭，左邊沒字元可取
```

---

### *Str::finish()*

在字串`結尾補上`指定字串（若尚未結尾）。

```php
$adjusted = Str::finish('this/string', '/'); // 'this/string/'
$adjusted = Str::finish('this/string/', '/'); // 'this/string/'
```

---

### *tr::fromBase64()*

`Base64` 字串 __解碼__。

```php
$decoded = Str::fromBase64('TGFyYXZlbA=='); // 'Laravel'
```

---

### *Str::headline()*

將字串（駝峰、底線、連字號）轉為`每字首大寫、空白分隔`。

```php
$headline = Str::headline('steve_jobs'); // 'Steve Jobs'
$headline = Str::headline('EmailNotificationSent'); // 'Email Notification Sent'
```

---

### *Str::markdown()*

將 Markdown 轉為 `HTML`。

```php
$html = Str::markdown('# Laravel'); // <h1>Laravel</h1>
$html = Str::markdown('# Taylor <b>Otwell</b>', ['html_input' => 'strip']); // <h1>Taylor Otwell</h1>
// 安全選項同 inlineMarkdown
```

---

### *Str::inlineMarkdown()*

將 Markdown 轉為 `inline HTML`，不包 block 元素。

```php
$html = Str::inlineMarkdown('**Laravel**'); // <strong>Laravel</strong>
// 安全選項
Str::inlineMarkdown('Inject: <script>alert("Hello XSS!");</script>', [
    'html_input' => 'strip',
    'allow_unsafe_links' => false,
]);
// Inject: alert(&quot;Hello XSS!&quot;);
```

---

### *Str::is()*

判斷字串`是否符合`指定模式（`*` 可作萬用字元）。

```php
// ( '比對規則' , '對象')
$matches = Str::is('foo*', 'foobar'); // true
$matches = Str::is('baz*', 'foobar'); // false
$matches = Str::is('*.jpg', 'photo.JPG', ignoreCase: true); // true
```

---

### *Str::isAscii()*

判斷字串 __是否__ 為 `7 bit ASCII`。

```php
$isAscii = Str::isAscii('Taylor'); // true
$isAscii = Str::isAscii('ü'); // false
```

---

### *Str::isJson()*

判斷字串 __是否__ 為`合法 JSON`。

```php
// 陣列格式：[1,2,3]（這是合法 JSON，代表一個數字陣列）
// 物件格式：{"a":"b"}（這也是合法 JSON，代表一個鍵值對物件）
$result = Str::isJson('[1,2,3]'); // true
$result = Str::isJson('{"first": "John", "last": "Doe"}'); // true
$result = Str::isJson('{first: "John", last: "Doe"}'); // false
```

---

### *Str::isUlid()*

判斷字串 __是否__ 為`合法 ULID`。

```php
$isUlid = Str::isUlid('01gd6r360bp37zj17nxb55yv40'); // true
$isUlid = Str::isUlid('laravel'); // false
```

---

### *Str::isUuid()*

判斷字串 __是否__ 為`合法 UUID`。

```php
$isUuid = Str::isUuid('a0a2a2d2-0b87-4a18-83f2-2529882be2de'); // true
$isUuid = Str::isUuid('laravel'); // false
```

---

### *Str::kebab()*

轉為 `kebab-case`。

```php
$converted = Str::kebab('fooBar'); // 'foo-bar'
```

---

### *Str::lcfirst()*

`首字小寫`。

```php
$string = Str::lcfirst('Foo Bar'); // 'foo Bar'
```

---

### *Str::length()*

取得字串`長度`。

```php
$length = Str::length('Laravel'); // 7
```

---

### *Str::limit()*

`限制`字串長度，**預設**_結尾加 ..._。

```php
$truncated = Str::limit('The quick brown fox jumps over the lazy dog', 20); // 'The quick brown fox...'
$truncated = Str::limit('The quick brown fox jumps over the lazy dog', 20, ' (...)'); // 'The quick brown fox (...)'
// preserveWords: true 可保留完整單字
$truncated = Str::limit('The quick brown fox', 12, preserveWords: true); // 'The quick...'
```

---

### *Str::lower()*

`轉小寫`。

```php
$converted = Str::lower('LARAVEL'); // 'laravel'
```

---

### *Str::mask()*

`遮罩`字串，可指定起始與長度。

```php
$string = Str::mask('taylor@example.com', '*', 3); // tay***************
$string = Str::mask('taylor@example.com', '*', -15, 3); // tay***@example.com
```

---

### *Str::match()*

回傳`符合正則`的 __第一個片段__。

```php
$result = Str::match('/bar/', 'foo bar'); // 'bar'
$result = Str::match('/foo (.*)/', 'foo bar'); // 'bar'
// '/foo (.*)/'：
// 這是一個正則表達式，用來匹配字串中以 foo 開頭，後面跟著一個空格，再接任意內容的部分。
// (.*) 是正則表達式中的捕獲群組（Capture Group），表示匹配任意字元（.*）並捕獲它。

// 正則表達式 /foo (.*)/ 會在字串 'foo bar' 中查找：
// 匹配 foo。
// 匹配空格。
// 捕獲空格後的內容（bar）。

$result = Str::match('/baz/', 'foo bar'); // ''
```

**註解**：`match` 會用正則表達式比對字串，回傳第一個符合的片段。

- 若正則有分組（如 `(.*)`），會回傳第一個分組的內容。
- 如果找不到符合的內容，會*回傳空字串*。
- 例如：
    - 第一行，找到 'bar'，回傳 'bar'。
    - 第二行，分組 `(.*)` 會抓到 'bar'，回傳 'bar'。
    - 第三行，找不到 'baz'，回傳空字串。

---

### *Str::matchAll()*

回傳`所有符合正則`的片段（Collection）。

```php
$result = Str::matchAll('/bar/', 'bar foo bar'); // collect(['bar', 'bar'])
$result = Str::matchAll('/f(\w*)/', 'bar fun bar fly'); // collect(['un', 'ly'])
```

---

### *Str::orderedUuid()*

產生「_時間戳優先_」的 UUID。

```php
return (string) Str::orderedUuid();
```
- __語法__

```php
$uuid = (string) Str::orderedUuid(); // 'f47ac10b-58cc-4372-a567-0e02b2c3d479'
```

<!-- 
orderedUuid() 和 ULID 都能產生有順序的唯一識別碼，
效果類似，都可用於排序和索引，
但格式不同（UUID vs ULID），
實作細節也不一樣。 
-->

- __註解__：適合`需要時間排序`的唯一識別碼。

- __白話解釋__：跟 `uuid` 類似，但有時間順序。

- __應用場景__：

  - *資料庫主鍵*
  - *分散式系統唯一識別*

---

### *Str::padBoth()*

`左右`補齊至指定長度。

```php
$padded = Str::padBoth('James', 10, '_'); // '__James___'
$padded = Str::padBoth('James', 10); // '  James   '
```

---

### *Str::padLeft()*

`左側`補齊至指定長度。

```php
$padded = Str::padLeft('James', 10, '-='); // '-=-=-James'
$padded = Str::padLeft('James', 10); // '     James'
```

---

### *Str::padRight()*

`右側`補齊至指定長度。

```php
$padded = Str::padRight('James', 10, '-'); // 'James-----'
$padded = Str::padRight('James', 10); // 'James     '
```

---

### *Str::password()*

產生`隨機密碼`（**預設** _32 字元_，含字母、數字、符號、空白）。

```php
$password = Str::password(); // 'EbJo2vE-AS:U,$%_gkrV4n,q~1xy/-_4'
$password = Str::password(12); // 'qwuar>#V|i]N'
```

---

### *Str::plural()*

`單字轉複數`，支援多語系。

```php
$plural = Str::plural('car'); // 'cars'
$plural = Str::plural('child'); // 'children'
$plural = Str::plural('child', 2); // 'children'
$singular = Str::plural('child', 1); // 'child'
```

---

### *Str::pluralStudly()*

`StudlyCase 單字轉複數`。

```php
$plural = Str::pluralStudly('VerifiedHuman'); // 'VerifiedHumans'
$plural = Str::pluralStudly('UserFeedback'); // 'UserFeedback'
$plural = Str::pluralStudly('VerifiedHuman', 2); // 'VerifiedHumans'
$singular = Str::pluralStudly('VerifiedHuman', 1); // 'VerifiedHuman'
```

- __註解__：`StudlyCase` 是 __每個單字首字大寫、單字間無底線或連字號__（如 VerifiedHuman、UserFeedback）。

- `pluralStudly` 會將 `StudlyCase` 單字自動轉為複數，**第二參數**可指定數量（`1`=單數，`2`=複數）。

- 第一行：'VerifiedHuman' 轉複數，變 'VerifiedHumans'。
- 第二行：'UserFeedback' 本身不可數，結果不變。
- 第三行：數量 `2`，複數，結果 'VerifiedHumans'。
- 第四行：數量 `1`，單數，結果 'VerifiedHuman'。

---

### *str::position()*

回傳子字串`首次出現位置`，找不到回傳 `false`。

```php
$position = Str::position('Hello, World!', 'Hello'); // 0
$position = Str::position('Hello, World!', 'W'); // 7
$position = Str::position('Hello, World!', 'foo'); // false
```

- __註解__：`position` 會回 傳 _子字串_ 在原字串中`第一次出現的位置`（從 0 開始算）。找不到則回傳 `false`。

- 第一行：'Hello' 在最開頭，索引是 0，回傳 0。
- 第二行：'W' 在第 7 個字元，回傳 7。
- 第三行：'foo' 沒有出現，回傳 `false`。

---

### *Str::random()*

產生 _指定長度_ 的`隨機字串`。

```php
$random = Str::random(40);
// 產生一個長度為 40 的隨機英數字串，例如：
// "k8Jf2aQw9XzT1bL6pR3sV0yUeWqZ4mN7oP5cD8hSgFjK2lB"

// 測試可 fake
Str::createRandomStringsUsing(fn () => 'fake-random-string');
Str::createRandomStringsNormally();
```

---

### *Str::remove()*

`移除`字串中的 _指定值_（可陣列）。

```php
$string = 'Peter Piper picked a peck of pickled peppers.';
$removed = Str::remove('e', $string); // 'Ptr Pipr pickd a pck of pickld ppprs.'
// 第三參數 false 可忽略大小寫
```

---

### *Str::repeat()*

`重複字串`。

```php
$repeat = Str::repeat('a', 5); // 'aaaaa'
```

---

### *Str::replace()*

`取代`字串，可選擇 __是否__ 區分大小寫。

```php
$string = 'Laravel 11.x';
$replaced = Str::replace('11.x', '12.x', $string); // 'Laravel 12.x'
$replaced = Str::replace('php', 'Laravel', 'PHP Framework for Web Artisans', caseSensitive: false); // 'Laravel Framework for Web Artisans'
```

---

### *Str::replaceArray()*

依序用`陣列值`取代字串中的 pattern。

```php
$string = 'The event will take place between ? and ?';
$replaced = Str::replaceArray('?', ['8:30', '9:00'], $string); // 'The event will take place between 8:30 and 9:00'
```

---

### *replaceFirstArray()*

- __用途__

多組 `replaceFirst` 批次處理，依序對字串陣列每個元素執行 `replaceFirst`。

- __語法__

```php
$result = Str::replaceFirstArray('foo', 'bar', ['foo1', 'foo2', 'baz']); // ['bar1', 'bar2', 'baz']

// 參數解釋
// 'foo'：
//        要被替換的字串。
//        在每個陣列元素中，尋找第一個匹配的 'foo'。
// 'bar'：
//        替換的字串。
//        如果找到 'foo'，就替換為 'bar'。

// ['foo1', 'foo2', 'baz']：
//        要進行替換的陣列。
//        每個元素都會被檢查是否包含 'foo'。
```

- __註解__：只取代每個字串的`第一個符合內容`。

- __白話解釋__：一次處理一堆字串，全部都 `replaceFirst`。

- __應用場景__：`批次修正`多個字串的前綴。

---

### *replaceLastArray()*

- __用途__

多組 `replaceLast` 批次處理，依序對字串陣列每個元素執行 `replaceLast`。

- __語法__

```php
$result = Str::replaceLastArray('foo', 'bar', ['1foo', '2foo', 'baz']); // ['1bar', '2bar', 'baz']
```

- __註解__：只取代每個字串的`最後一個符合內容`。

- __白話解釋__：一次處理一堆字串，全部都 `replaceLast`。

- __應用場景__：`批次修正`多個字串的結尾。

---

### *Str::replaceFirst()*

取代`第一個出現`的指定字串。

```php
$replaced = Str::replaceFirst('the', 'a', 'the quick brown fox jumps over the lazy dog'); // 'a quick brown fox jumps over the lazy dog'
```

---

### *Str::replaceLast()*

取代`最後一個出現`的指定字串。

```php
$replaced = Str::replaceLast('the', 'a', 'the quick brown fox jumps over the lazy dog'); // 'the quick brown fox jumps over a lazy dog'
```

---

### *Str::replaceMatches()*

`正則取代`所有符合的片段。

```php
$replaced = Str::replaceMatches('/[^A-Za-z0-9]++/', '', '(+1) 501-555-1000'); // '15015551000'
// 可用 closure 處理
$replaced = Str::replaceMatches('/\d/', fn($matches) => '['.$matches[0].']', '123'); // '[1][2][3]'
```

---

### *Str::replaceStart()*

若`開頭`為指定字串則取代。

```php
$replaced = Str::replaceStart('Hello', 'Laravel', 'Hello World'); // 'Laravel World'
$replaced = Str::replaceStart('World', 'Laravel', 'Hello World'); // 'Hello World'
// 說明：只有當字串「開頭」是 'World' 才會被替換，這裡開頭是 'Hello'，所以不會替換
```

---

### *Str::replaceEnd()*

若`結尾`為指定字串則取代。

```php
$replaced = Str::replaceEnd('World', 'Laravel', 'Hello World'); // 'Hello Laravel'
$replaced = Str::replaceEnd('Hello', 'Laravel', 'Hello World'); // 'Hello World'
```

---

### *Str::reverse()*

`反轉`字串。

```php
$reversed = Str::reverse('Hello World'); // 'dlroW olleH'
```

---

### *Str::singular()*

`複數轉單數`，支援多語系。

```php
$singular = Str::singular('cars'); // 'car'
$singular = Str::singular('children'); // 'child'
```

---

### *Str::slug()*

`產生 URL 友善 slug`。

```php
$slug = Str::slug('Laravel 5 Framework', '-'); // 'laravel-5-framework'
```

---

### *Str::snake()*

轉為 `snake_case`。

```php
$converted = Str::snake('fooBar'); // 'foo_bar'
$converted = Str::snake('fooBar', '-'); // 'foo-bar'
```

---

### *Str::squish()*

移除`多餘空白`。

```php
$string = Str::squish('    laravel    framework    '); // 'laravel framework'
```

---

### *Str::start()*

在字串`開頭補上`指定字串（若尚未開頭）。

```php
$adjusted = Str::start('this/string', '/'); // '/this/string'
$adjusted = Str::start('/this/string', '/'); // '/this/string'
```

---

### *Str::startsWith()*

判斷字串`是否以`指定值開頭。

```php
$result = Str::startsWith('This is my name', 'This'); // true
$result = Str::startsWith('This is my name', ['This', 'That', 'There']); // true
```

---

### *Str::studly()*

轉為 `StudlyCase`。

```php
$converted = Str::studly('foo_bar'); // 'FooBar'
```

---

### *Str::substr()*

回傳`指定起始與長度`的子字串。

```php
$converted = Str::substr('The Laravel Framework', 4, 7); // 'Laravel'
```

---

### *Str::substrCount()*

回傳子字串`出現次數`。

```php
$count = Str::substrCount('If you like ice cream, you will like snow cones.', 'like'); // 2
``` 

---

### *Str::substrReplace()*

取代`字串中指定位置的內容`。

- __更白話說明__

 - `substrReplace` 可以「_在字串的某個位置，把一段內容換成你指定的新內容_」，也可以「_在某個位置插入新內容_」。

- 語法：`Str::substrReplace(原字串, 新內容, 起始位置, [長度])`
  - _原字串_：你要處理的字串
  - _新內容_：你要插入或取代的內容
  - _起始位置_：從第幾個字元開始（從 0 算起）
  - _長度（可選）_：要取代幾個字元（`不寫就把後面全部換掉`；寫 __0__ 就是`插入`）

```php

$result = Str::substrReplace('1300', ':', 2); // '13:'
// 1 3 0 0
// 0 1 2 3  ← 索引
//     ↑
//     2（（從這裡開始，後面都換成 :）
// 結果：'13:'
//
$result = Str::substrReplace('1300', ':', 2, 0); // '13:00'
// 1 3 0 0
// 0 1 2 3
//     ↑
//     2（在這裡插入 :，原本的 0 0 往後推）
// 結果：'13:00'
```

- __補充說明__

 - `沒寫長度（只給三個參數）時`，會把起始位置後面全部**換成新內容**。
 - 長度寫 **0** 時，代表`只插入新內容，不會刪除任何東西`。
 - 這和 PHP 原生 `substr_replace` 行為一致。

- __例如__

  ```php
  Str::substrReplace('abcdef', 'X', 2); - 'abX'
  Str::substrReplace('abcdef', 'X', 2, 0); // 'abXcdef'
  ```

---

### *Str::swap()*

用多組`對應值`批次 __取代字串__（`strtr`）。

```php
$string = Str::swap([
    'Tacos' => 'Burritos',
    'great' => 'fantastic',
], 'Tacos are great!'); // 'Burritos are fantastic!'
```

---

### *Str::take()*

`取`前 n 個字元。

```php
$taken = Str::take('Build something amazing!', 5); // 'Build'
```

---

### *Str::title()*

轉為 __標題格式__（`每字首大寫`）。

```php
$converted = Str::title('a nice title uses the correct case'); // 'A Nice Title Uses The Correct Case'
```

---

### *Str::toBase64()*

轉為 `Base64` 字串。

```php
$base64 = Str::toBase64('Laravel'); // 'TGFyYXZlbA=='
```

---

### *Str::transliterate()*

嘗試將`字串音譯`為最接近的 `ASCII`。

```php
$email = Str::transliterate('ⓣⓔⓢⓣ@ⓛⓐⓡⓐⓥⓔⓛ.ⓒⓞⓜ'); // 'test@laravel.com'
```

---

### *Str::trim()*

去除`首尾空`白（含 `unicode` 空白）。

```php
$string = Str::trim(' foo bar '); // 'foo bar'
```

---

### *Str::ltrim()*

去除`開頭空白`（含 unicode 空白）。

```php
$string = Str::ltrim('  foo bar  '); // 'foo bar  '
```

---

### *Str::rtrim()*

去除`結尾空白`（含 unicode 空白）。

```php
$string = Str::rtrim('  foo bar  '); // '  foo bar'
```

---

### *Str::ucfirst()*

`首字大寫`。

```php
$string = Str::ucfirst('foo bar'); // 'Foo bar'
```

---

### *Str::ucsplit()*

依大寫字母 __分割為`陣列`__。

```php
$segments = Str::ucsplit('FooBar'); // ['Foo', 'Bar']
```

---

### *Str::upper()*

`轉大寫`。

```php
$string = Str::upper('laravel'); // 'LARAVEL'
```

---

### *Str::ulid()*

產生 `ULID`（__時間排序__ 唯一識別碼）。

```php
$ulid = (string) Str::ulid(); // '01gd6r360bp37zj17nxb55yv40'
// 取得 ULID 時間
$date = Carbon::createFromId((string) Str::ulid());
// 測試可 fake
Str::createUlidsUsing(fn() => new Ulid('01HRDBNHHCKNW2AK4Z29SN82T9'));
Str::createUlidsNormally();
```

---

### *Str::unwrap()*

__去除__ 字串`開頭與結尾`的指定字元。

```php
Str::unwrap('-Laravel-', '-'); // 'Laravel'
Str::unwrap('{framework: "Laravel"}', '{', '}'); // 'framework: "Laravel"'
```

---

### *Str::uuid()*

產生 `UUID v4`。

```php
$uuid = (string) Str::uuid();
// 測試可 fake
Str::createUuidsUsing(fn() => Uuid::fromString('eadbfeac-5258-45c2-bab7-ccb9b5ef74f9'));
Str::createUuidsNormally();
```

---

### *Str::uuid7()*

產生 `UUID v7`，可 __指定時間__。

```php
$uuid7 = (string) Str::uuid7();
$uuid7 = (string) Str::uuid7(time: now());
```

---

### *Str::wordCount()*

回傳`字串單字數`。

```php
Str::wordCount('Hello, world!'); // 2
```

---

### *Str::wordWrap()*

`自動換行`至 __指定長度__。

```php
$text = "The quick brown fox jumped over the lazy dog."
Str::wordWrap($text, characters: 20, break: "<br />\n");
// The quick brown fox<br />\njumped over the lazy<br />\ndog.
```

---

### *Str::words()*

`限制`字串 __單字數__，超過加上`結尾字串`。

```php
Str::words('Perfectly balanced, as all things should be.', 3, ' >>>'); // 'Perfectly balanced, as >>>'
```

---

### *Str::wrap()*


將一段字串「_包裹_」在你指定的`前綴`（before）和`後綴`（after）之間。

- __用途__

將主體`字串前後`加上指定內容，常用於自動加 __引號、括號、HTML 標籤、格式化輸出__ 等。

- __語法__

```php
Str::wrap($string, before: '', after: '')
```
- __$string__：要被包裹的`主體字串`。
- __before__：要加在主體字串`前面的內容`（可省略）。
- __after__：要加在主體字串`後面的內容`（可省略）。

---

__參數詳細解釋__

| 參數      | 型態   | 預設值 | 說明                                   |
|-----------|--------|--------|----------------------------------------|
| `$string`   | string | 無     | 要被包裹的主體字串                     |
| `before`    | string | ''     | 要加在主體字串前面的內容（可省略）      |
| `after`     | string | ''     | 要加在主體字串後面的內容（可省略）      |


---

- __範例__

```php
Str::wrap('is', before: 'This ', after: ' Laravel!'); // 'This is Laravel!'
Str::wrap('Laravel', before: 'Hello, '); // 'Hello, Laravel'
Str::wrap('Laravel', after: ' is great!'); // 'Laravel is great!'
Str::wrap('PHP', before: '[', after: ']'); // '[PHP]'
Str::wrap('A', before: '(', after: ')'); // '(A)'
Str::wrap('Laravel'); // 'Laravel'
```

---

__圖解__

假設你呼叫：

```php
Str::wrap('中心', before: '【', after: '】');
```

_圖解如下_：

```
before   $string   after
  ↓        ↓        ↓
'【'   + '中心' + '】'
----------------------
      '【中心】'
```

---

__常見問題 Q&A__

- Q1：*如果 before 或 after 是空字串？*
  - 那一邊就不會加東西，結果就是原字串或只加一邊。

- Q2：*可以同時加多層嗎？*

  - 可以多次呼叫 `Str::wrap()`，例如：

    ```php
    Str::wrap(Str::wrap('A', before: '[', after: ']'), before: '(', after: ')'); // '([A])'
    ```

- Q3：*before/after 可以是任何字串嗎？*
  - 可以，甚至可以是 __表情符號、HTML 標籤、特殊符號__ 等。

- Q4：*和 concat、.（點號）有什麼差別？*
  - `Str::wrap()` 是專門設計來「__同時__」加前後字串，比你手動拼接更直覺、可讀性高，且支援具名參數。

---

__實用小技巧__

- *HTML 標籤包裹*
  
```php
  Str::wrap('內容', before: '<b>', after: '</b>'); // '<b>內容</b>'
  ```
- *自動產生引號*
  
```php
  Str::wrap('Laravel', before: '"', after: '"'); // '"Laravel"'
  ```
- *多層巢狀包裹*
  
```php
  Str::wrap(Str::wrap('A', before: '[', after: ']'), before: '(', after: ')'); // '([A])'
  ```

---

__常見誤區提醒__

- *具名參數*：`before:`、`after:` 是 `PHP 8+ 的語法`，舊版 PHP 不能用這種寫法，只能用順序傳參數。
- *不會自動加空白*：如果你想要有空白，要自己在 `before/after` 裡加空白。
  - 例：`before: 'Hello, '`（有空白）

---

__圖解：多層包裹__

假設你想要這樣的效果：`((A))`

```php
Str::wrap(Str::wrap('A', before: '(', after: ')'), before: '(', after: ')');
// 第一次：'A' → '(A)'
// 第二次：'(A)' → '((A))'
```

- __應用場景__：

  - 產生 `HTML 標籤`
  - 自動`加引號、括號`
  - 格式化輸出（如 `[INFO] 訊息`）
  - 產生`自訂格式的字串`

---

## **Fluent String 物件簡介**

`Fluent String` 提供更流暢、*物件導向* 的字串操作，*可鏈式* 呼叫多個方法。

```php
use Illuminate\Support\Str;

// after
$slice = Str::of('This is my name')->after('This is'); // ' my name'
// afterLast
$slice = Str::of('App\\Http\\Controllers\\Controller')->afterLast('\\'); // 'Controller'
// apa
$converted = Str::of('a nice title uses the correct case')->apa(); // 'A Nice Title Uses the Correct Case'
// append
$string = Str::of('Taylor')->append(' Otwell'); // 'Taylor Otwell'
// ascii
$string = Str::of('ü')->ascii(); // 'u'
// basename
$string = Str::of('/foo/bar/baz')->basename(); // 'baz'
$string = Str::of('/foo/bar/baz.jpg')->basename('.jpg'); // 'baz'
// before
$slice = Str::of('This is my name')->before('my name'); // 'This is '
// beforeLast
$slice = Str::of('This is my name')->beforeLast('is'); // 'This '
// between
$converted = Str::of('This is my name')->between('This', 'name'); // ' is my '
// betweenFirst
$converted = Str::of('[a] bc [d]')->betweenFirst('[', ']'); // 'a'
// camel
$converted = Str::of('foo_bar')->camel(); // 'fooBar'
// charAt
$character = Str::of('This is my name.')->charAt(6); // 's'
// classBasename
$class = Str::of('Foo\\Bar\\Baz')->classBasename(); // 'Baz'
// chopStart
$url = Str::of('https://laravel.com')->chopStart('https://'); // 'laravel.com'
$url = Str::of('http://laravel.com')->chopStart(['https://', 'http://']); // 'laravel.com'
// chopEnd
$url = Str::of('https://laravel.com')->chopEnd('.com'); // 'https://laravel'
$url = Str::of('http://laravel.com')->chopEnd(['.com', '.io']); // 'http://laravel'
// contains
$contains = Str::of('This is my name')->contains('my'); // true
$contains = Str::of('This is my name')->contains(['my', 'foo']); // true
$contains = Str::of('This is my name')->contains('MY', ignoreCase: true); // true
// containsAll
$containsAll = Str::of('This is my name')->containsAll(['my', 'name']); // true
$containsAll = Str::of('This is my name')->containsAll(['MY', 'NAME'], ignoreCase: true); // true
``` 

---

### *after()*

- __用途__

取得指定`字串之後`的所有內容。如果找不到指定字串，會回傳原字串。

- __語法__

```php
$slice = Str::of('This is my name')->after('This is'); // ' my name'
```

- __註解__：
  - 只會找`第一個出現`的指定字串。
  - 找不到時，回傳原字串。

---

### *afterLast()*

- __用途__

取得`最後一次出現`指定字串`之後`的所有內容。如果找不到，回傳原字串。

- __語法__

```php
$slice = Str::of('App\\Http\\Controllers\\Controller')->afterLast('\\'); // 'Controller'
```

- __註解__：常用於`取得檔名、類別名稱`等。

---

### *apa()*

- __用途__

將字串`轉為 APA 標題格式`（每個單字首字大寫，符合 APA 標準）。

- __語法__

```php
$converted = Str::of('a nice title uses the correct case')->apa(); // 'A Nice Title Uses the Correct Case'
```

- __註解__：適合`論文、標題`自動格式化。

---

### *append()*

- __用途__

在字串`後面加上`指定內容。

- __語法__

```php
$string = Str::of('Taylor')->append(' Otwell'); // 'Taylor Otwell'
```

---

### *ascii()*

- __用途__

將字串轉為 `ASCII` 字元（*去除重音、特殊符號*）。

- __語法__

```php
$string = Str::of('ü')->ascii(); // 'u'
```

---

### *basename()*

- __用途__

_取得_`路徑的最後一段`（檔名），可選擇去除副檔名。

- __語法__

```php
Str::of('/foo/bar/baz')->basename(); // 'baz'
Str::of('/foo/bar/baz.jpg')->basename('.jpg'); // 'baz'
```

---

### *before()*

- __用途__

取得指定`字串之前`的所有內容。

- __語法__

```php
$slice = Str::of('This is my name')->before('my name'); // 'This is '
```

---

### *beforeLast()*

- __用途__

取得`最後一次出現`指定字串`之前`的所有內容。

- __語法__

```php
$slice = Str::of('This is my name, is it?')->beforeLast('is'); // 'This is my name, '
```

---

### *between()*

- __用途__

取得兩個指定`字串之間`的內容。

- __語法__

```php
$converted = Str::of('This is my name')->between('This', 'name'); // ' is my '
```

---

### *betweenFirst()*

- __用途__

取得`第一組出現`的兩個`字串之間`的內容（遇到多組時只取最小範圍）。

- __語法__

```php
$converted = Str::of('[a] bc [d]')->betweenFirst('[', ']'); // 'a'
```

---

### *camel()*

- __用途__

將字串轉為 `camelCase（駝峰式命名）`。

- __語法__

```php
$converted = Str::of('foo_bar')->camel(); // 'fooBar'
```

---

### *charAt()*

- __用途__

取得指定`索引位置`的`字元`。超出範圍時回傳 ``false``。

- __語法__

```php
$character = Str::of('This is my name.')->charAt(6); // 's'
```

---

### *classBasename()*

- __用途__

取得`類別名稱`（去除命名空間）。

- __語法__

```php
$class = Str::of('Foo\\Bar\\Baz')->classBasename(); // 'Baz'
```

---

### *chopStart()*

- __用途__

_如果_ 字串`開頭`是指定內容，`移除第一個出現`的指定內容。可傳陣列。

- __語法__

```php
$url = Str::of('https://laravel.com')->chopStart('https://'); // 'laravel.com'
$url = Str::of('http://laravel.com')->chopStart(['https://', 'http://']); // 'laravel.com'
```

---

### *chopEnd()*

- __用途__

_如果_ 字串`結尾`是指定內容，`移除最後一個出現`的指定內容。可傳陣列。

- __語法__

```php
$url = Str::of('https://laravel.com')->chopEnd('.com'); // 'https://laravel'
$url = Str::of('http://laravel.com')->chopEnd(['.com', '.io']); // 'http://laravel'
```

---

### *contains()*

- __用途__

判斷字串 __是否__ `包含`指定內容。*預設*區分大小寫。

- __語法__

```php
$contains = Str::of('This is my name')->contains('my'); // true
$contains = Str::of('This is my name')->contains(['my', 'foo']); // true
$contains = Str::of('This is my name')->contains('MY', ignoreCase: true); // true
```

---

### *containsAll()*

- __用途__

判斷字串 __是否__ 同時`包含`陣列中所有內容。

- __語法__

```php
$containsAll = Str::of('This is my name')->containsAll(['my', 'name']); // true
$containsAll = Str::of('This is my name')->containsAll(['MY', 'NAME'], ignoreCase: true); // true
```

---

### *decrypt()*

- __用途__

`解密字串`（需搭配 Laravel 加密功能）。

- __語法__

```php
// 假設 $encrypted 是加密過的 Fluent String 物件
$decrypted = $encrypted->decrypt(); // 'secret'
```

---

### *deduplicate()*

- __用途__

將連續`重複的字元合併成一個`。*預設*合併空白，可自訂字元。

- __語法__

```php
$result = Str::of('The   Laravel   Framework')->deduplicate(); // 'The Laravel Framework'
$result = Str::of('The---Laravel---Framework')->deduplicate('-'); // 'The-Laravel-Framework'
```

---

### *dirname()*

<!-- 
dirname 的全稱是 directory name，
意思是「目錄名稱」，
用來取得檔案路徑的上層目錄。 
-->

- __用途__

取得路徑的`上層目錄`。可指定往上幾層。

- __語法__

```php
$string = Str::of('/foo/bar/baz')->dirname(); // '/foo/bar'
$string = Str::of('/foo/bar/baz')->dirname(2); // '/foo'
```

---

### *doesntEndWith()*

- __用途__

判斷字串`結尾不是`指定內容。可傳陣列。

- __語法__

```php
$result = Str::of('This is my name')->doesntEndWith('dog'); // true
$result = Str::of('This is my name')->doesntEndWith(['this', 'foo']); // true
$result = Str::of('This is my name')->doesntEndWith(['name', 'foo']); // false
```

---

### *doesntStartWith()*

- __用途__

判斷字串`開頭不是`指定內容。可傳陣列。

- __語法__

```php
$result = Str::of('This is my name')->doesntStartWith('That'); // true
$result = Str::of('This is my name')->doesntStartWith(['This', 'That', 'There']); // true
```

- __註解__

- 只要`開頭不是`陣列中任何一個值，就會回傳 `true`。
- 如果開頭是陣列中任一個值，則回傳 `false`。

- __白話說明__

  - 就是「不是這些開頭」才會回傳 `true`。
  - 常用於`排除特定前綴`的字串。

---

### *encrypt()*

- __用途__

`加密字串`（需搭配 Laravel 加密功能）。

- __語法__

```php
$encrypted = Str::of('secret')->encrypt();
```

- __註解__

  - 回傳加密後的字串，通常用於`敏感資料儲存`。
  - 解密請用 `decrypt()` 方法。

---

### *endsWith()*

- __用途__

判斷字串`結尾是否`為指定內容。可傳陣列。

- __語法__

```php
$result = Str::of('This is my name')->endsWith('name'); // true
$result = Str::of('This is my name')->endsWith(['name', 'foo']); // true
$result = Str::of('This is my name')->endsWith(['this', 'foo']); // false
```

- __註解__

  - 只要`結`尾是陣列中任一個值，就會回傳 `true`。
  - 結尾都不是陣列中任何一個值，才會回傳 `false`。

- __白話說明__

  - 就是「有這些結尾」才會回傳 `true`。
  - 常用於檢查副檔名、網址結尾等。

---

### *exactly()*

- __用途__

判斷字串 __是否__ 與另一字串`完全相同`。

- __語法__

```php
$result = Str::of('Laravel')->exactly('Laravel'); // true
```

- __註解__：`完全比對`，包含大小寫、空白等。

- __白話說明__：就是「一模一樣」才會 `true`，任何一點不同都會 `false`。

---

### *excerpt()*

- __用途__

從字串中`擷取`出包含指定關鍵字的片段，可設定*前後顯示幾個字元*與*省略符號*。

- __語法__

```php
$excerpt = Str::of('This is my name')->excerpt('my', ['radius' => 3]); // '...is my na...'
$excerpt = Str::of('This is my name')->excerpt('name', ['radius' => 3, 'omission' => '(...) ']); // '(...) my name'
```

- __註解__

  - `radius` 代表關鍵字*左右各顯示*幾個字元，預設 100。
  - `omission` 可自訂省略符號，*預設*為 "..."。
  - 找不到關鍵字時回傳 `null`。

- __白話說明__

  - 就像「_自動摘要_」功能，會抓出關鍵字附近的內容，前後太長就用省略符號補上。
  - 適合做搜尋結果摘要、重點提示。

---

### *explode()*

- __用途__

用指定`分隔符`切割字串，回傳 Collection。

- __語法__

```php
$collection = Str::of('foo bar baz')->explode(' '); // collect(['foo', 'bar', 'baz'])
```

- __白話說明__

  - 類似 `PHP 的 explode`，但回傳 `Laravel Collection`，方便後續鏈式操作。

---

### *finish()* vs `start()`

- __用途__

確保字串`結尾`_有指定內容_，若已存在則`不重複加`。

- __語法__

```php
$adjusted = Str::of('this/string')->finish('/'); // 'this/string/'
$adjusted = Str::of('this/string/')->finish('/'); // 'this/string/'
```

- __白話說明__：常用於`路徑、網址`等，確保結尾有斜線或特定符號。

---

### *fromBase64()*

- __用途__

將 `Base64` 編碼字串解碼。

- __語法__

```php
$decoded = Str::of('TGFyYXZlbA==')->fromBase64(); // 'Laravel'
```

---

### *hash()*

- __用途__

用指定演算法`雜湊字串`。

- __語法__

```php
$hashed = Str::of('secret')->hash(algorithm: 'sha256');
// '2bb80d537b1da3e38bd30361aa855686bde0eacd7162fef6a25fe97bf527a25b'
```

- __白話說明__：常用於`密碼、簽章`等安全需求。

---

### *headline()*

- __用途__

將字串轉為`每個單字首字大寫`的標題格式。

- __語法__

```php
$headline = Str::of('taylor_otwell')->headline(); // 'Taylor Otwell'
$headline = Str::of('EmailNotificationSent')->headline(); // 'Email Notification Sent'
```

- __白話說明__：會自動判斷`底線、駝峰、連字號`等，轉成標題格式。

---

### *inlineMarkdown()*

- __用途__

將 GitHub 風格 Markdown 轉為 `inline HTML`，不包 block-level 標籤。

- __語法__

```php
$html = Str::of('**Laravel**')->inlineMarkdown(); // <strong>Laravel</strong>
$html = Str::of('Inject: <script>alert("Hello XSS!");</script>')->inlineMarkdown([
    'html_input' => 'strip',
    'allow_unsafe_links' => false,
]);
// Inject: alert(&quot;Hello XSS!&quot;);
```

- __註解__：預設會處理 `XSS 風險`，建議用 `html_input` 選項。

---

### *is()*

- __用途__

判斷字串`是否`符合指定模式（* 可作萬用字元）。

- __語法__

```php
$matches = Str::of('foobar')->is('foo*'); // true
$matches = Str::of('foobar')->is('baz*'); // false
```

- __白話說明__：`*` 代表任意字元，像 `shell` 的萬用字元。

---

### *isAscii()*

- __用途__

判斷字串`是否`為 ASCII 字元。

- __語法__

```php
$result = Str::of('Taylor')->isAscii(); // true
$result = Str::of('ü')->isAscii(); // false
```

---

### *isEmpty()*

- __用途__

判斷字串`是否`為空（trim 後）。

- __語法__

```php
$result = Str::of('  ')->trim()->isEmpty(); // true
$result = Str::of('Laravel')->trim()->isEmpty(); // false
```

---

### *isNotEmpty()*

- __用途__

判斷字串`是否`不為空（trim 後）。

- __語法__

```php
$result = Str::of('  ')->trim()->isNotEmpty(); // false
$result = Str::of('Laravel')->trim()->isNotEmpty(); // true
```

---

### *isJson()*

- __用途__

判斷字串`是否`為合法 JSON。

- __語法__

```php
$result = Str::of('[1,2,3]')->isJson(); // true
$result = Str::of('{"first": "John", "last": "Doe"}')->isJson(); // true
$result = Str::of('{first: "John", last: "Doe"}')->isJson(); // false
```

- __白話說明__：只要是標準 `JSON` 格式（*鍵值都要加雙引號*），才會回傳 `true`。

---

### *isUlid()*

- __用途__

判斷字串`是否`為合法 ULID。

- __語法__

```php
$result = Str::of('01gd6r360bp37zj17nxb55yv40')->isUlid(); // true
$result = Str::of('Taylor')->isUlid(); // false
```

- __白話說明__：ULID 是一種*唯一識別碼*，類似 UUID，但有時間排序特性。

---

### *isUrl()*

- __用途__

判斷字串`是否`為合法 URL，可自訂協定。

- __語法__

```php
$result = Str::of('http://example.com')->isUrl(); // true
$result = Str::of('Taylor')->isUrl(); // false
$result = Str::of('http://example.com')->isUrl(['http', 'https']); // true
```

- __白話說明__：預設支援多種協定，可用陣列限制只接受 `http/https`。

---

### *isUuid()*

- __用途__

判斷字串`是否`為 UUID。

- __語法__

```php
$result = Str::of('5ace9ab9-e9cf-4ec6-a19d-5881212a452c')->isUuid(); // true
$result = Str::of('Taylor')->isUuid(); // false
```

- __白話說明__：UUID 是一種常見的`唯一識別`碼格式。

---

### *kebab()*

- __用途__

將字串轉為 `kebab-case`（小寫加連字號）。

- __語法__

```php
$converted = Str::of('fooBar')->kebab(); // 'foo-bar'
```

---

### *lcfirst()*

- __用途__

將字串`第一個字元`轉小寫。

- __語法__

```php
$string = Str::of('Foo Bar')->lcfirst(); // 'foo Bar'
```

---

### *length()*

- __用途__

取得字串`長度`。

- __語法__

```php
$length = Str::of('Laravel')->length(); // 7
```

---

### *limit()*

- __用途__

將字串`截斷至指定長度`，可自訂 __結尾符號__，並可選擇保留完整單字。

- __語法__

```php
$truncated = Str::of('The quick brown fox jumps over the lazy dog')->limit(20); // 'The quick brown fox...'
$truncated = Str::of('The quick brown fox jumps over the lazy dog')->limit(20, ' (...)'); // 'The quick brown fox (...)'
$truncated = Str::of('The quick brown fox')->limit(12, preserveWords: true); // 'The quick...'
```

- __白話說明__

  - 可用於`文章摘要、標題截斷`等。
  - *preserveWords*: `true` 會避免把單字切一半。

---

### *lower()*

- __用途__

將字串轉`為小寫`。

- __語法__

```php
$result = Str::of('LARAVEL')->lower(); // 'laravel'
```

---

### *markdown()*

- __用途__

將 GitHub 風格 `Markdown` 轉為 HTML。

- __語法__

```php
$html = Str::of('# Laravel')->markdown(); // <h1>Laravel</h1>
$html = Str::of('# Taylor <b>Otwell</b>')->markdown([
    'html_input' => 'strip',
]); // <h1>Taylor Otwell</h1>
```

- __註解__：預設支援 `raw HTML`，建議用 `html_input` 選項避免 XSS。

---

__Markdown Security__

- 預設 `Markdown` 支援 `raw HTML`，直接渲染用戶輸入時有 `XSS 風險`。
- 建議用 `html_input` 選項設為 `strip` 或 `escape`，並可用 `allow_unsafe_links` 控制連結安全。
- 如需允許部分 HTML，建議再用 `HTML Purifier` 過濾。

```php
Str::of('Inject: <script>alert("Hello XSS!");</script>')->markdown([
    'html_input' => 'strip',
    'allow_unsafe_links' => false,
]);
// <p>Inject: alert(&quot;Hello XSS!&quot;);</p>
```

---

### *mask()*

- __用途__

用指定字元`遮蔽`字串的一部分，常用於隱藏信箱、電話等敏感資訊。

- __語法__

```php
$string = Str::of('taylor@example.com')->mask('*', 3); // tay***************
$string = Str::of('taylor@example.com')->mask('*', -15, 3); // tay***@example.com
$string = Str::of('taylor@example.com')->mask('*', 4, -4); // tayl**********.com
```

- __註解__：`第三、四參數`可用*負數*，代表*從*\字串尾端算起*。

- __白話說明__：適合遮蔽帳號、電話、信箱等部分內容。

---

### *match()*

- __用途__

用`正則表達式`擷取`第一個符合`的內容。

- __語法__

```php
$result = Str::of('foo bar')->match('/bar/'); // 'bar'
$result = Str::of('foo bar')->match('/foo (.*)/'); // 'bar'
```

- __註解__

  - 若有分組，回傳`第一個分組`內容。
  - 找不到時回傳`空字串`。

---

### *matchAll()*

- __用途__

用`正則表達式`擷取所有符合的內容，回傳 Collection。

- __語法__

```php
$result = Str::of('bar foo bar')->matchAll('/bar/'); // collect(['bar', 'bar'])
$result = Str::of('bar fun bar fly')->matchAll('/f(\w*)/'); // collect(['un', 'ly'])
```

- __註解__

  - 有分組時只回傳`分組內容`。
  - 沒有符合時回傳空 `Collection`。

---

### *isMatch()*

- __用途__

判斷字串`是否`符合正則表達式。

- __語法__

```php
$result = Str::of('foo bar')->isMatch('/foo (.*)/'); // true
$result = Str::of('laravel')->isMatch('/foo (.*)/'); // false
```

---

### *newLine()*

- __用途__

在字串`後面`加上`換行符號`（\n）。

- __語法__

```php
$padded = Str::of('Laravel')->newLine()->append('Framework');
// 'Laravel\nFramework'
```

---

### *padBoth()*

- __用途__

在字串`左右兩側補滿`指定字元，直到`指定長度`。

- __語法__

```php
$padded = Str::of('James')->padBoth(10, '_'); // '__James___'
$padded = Str::of('James')->padBoth(10); // '  James   '
```

---

### *padLeft()*

- __用途__

在字串`左側補滿`指定字元，直到指定長度。

- __語法__

```php
$padded = Str::of('James')->padLeft(10, '-='); // '-=-=-James'
$padded = Str::of('James')->padLeft(10); // '     James'
```

---

### *padRight()*

- __用途__

在字串`右側補滿`指定字元，直到指定長度。

- __語法__

```php
$padded = Str::of('James')->padRight(10, '-'); // 'James-----'
$padded = Str::of('James')->padRight(10); // 'James     '
```

---

### *pipe()*

- __用途__

將字串`傳入`指定函式或閉包進行`轉換`。

- __語法__

```php
$hash = Str::of('Laravel')->pipe('md5')->prepend('Checksum: '); // 'Checksum: a5c95b86291ea299fcbe64458ed12702'
$closure = Str::of('foo')->pipe(function (Stringable $str) {
     return 'bar';
}); // 'bar'
```

---

### *plural()*

- __用途__

將`單數`字串轉為`複數`，支援多語系。

- __語法__

```php
$plural = Str::of('car')->plural(); // 'cars'
$plural = Str::of('child')->plural(); // 'children'
$plural = Str::of('child')->plural(2); // 'children'
$plural = Str::of('child')->plural(1); // 'child'
```

- __註解__：`第二參數`可指定數量，*1* 時回傳`單數`，*其他* 回傳 `複數`。

---

### *position()*

- __用途__

取得子字串在主字串中`第一次出現的位置`，找不到回傳 `false`。

- __語法__

```php
$position = Str::of('Hello, World!')->position('Hello'); // 0
$position = Str::of('Hello, World!')->position('W'); // 7
```

---

### *prepend()*

- __用途__

在字串`前面加上`指定內容。

- __語法__

```php
$string = Str::of('Framework')->prepend('Laravel '); // 'Laravel Framework'
```

---

### *remove()*

- __用途__

`移除`字串中的指定內容，`可傳陣列`。

- __語法__

```php
$string = Str::of('Arkansas is quite beautiful!')->remove('quite'); // 'Arkansas is beautiful!'
$string = Str::of('Arkansas is quite beautiful!')->remove(['quite', 'is']); // 'Arkansas beautiful!'
$string = Str::of('Arkansas is quite beautiful!')->remove('IS', false); // 'Arkansas  quite beautiful!'
```

- __註解__：`第二參數`設為 `false` 時，會 *忽略大小寫* 。

---

### *repeat()*

- __用途__

`重複`字串指定次數。

- __語法__

```php
$repeated = Str::of('a')->repeat(5); // 'aaaaa'
```

---

### *replace()*

- __用途__

`取代`字串中的指定內容，可選擇 __是否__ `區分大小寫`。

- __語法__

```php
$replaced = Str::of('Laravel 6.x')->replace('6.x', '7.x'); // 'Laravel 7.x'
$replaced = Str::of('macOS 13.x')->replace('macOS', 'iOS', caseSensitive: false); // 'iOS 13.x'
```

- __註解__：*預設*區分大小寫。

---

### *replaceArray()*

- __用途__

依序用`陣列內容`取代字串中的指定符號。

- __語法__

```php
$replaced = Str::of('The event will take place between ? and ?')
          ->replaceArray('?', ['8:30', '9:00']); // 'The event will take place between 8:30 and 9:00'
```

---

### *replaceFirst()*

- __用途__

只取代`第一個`出現的指定內容。

- __語法__

```php
$replaced = Str::of('the quick brown fox jumps over the lazy dog')
          ->replaceFirst('the', 'a'); // 'a quick brown fox jumps over the lazy dog'
```

---

### *replaceLast()*

- __用途__

只取代`最後一個`出現的指定內容。

- __語法__

```php
$replaced = Str::of('the quick brown fox jumps over the lazy dog')
          ->replaceLast('the', 'a'); // 'the quick brown fox jumps over a lazy dog'
```

---

### *replaceMatches()*

- __用途__

用`正則表達式`取代所有符合的內容，可用字串或閉包。

- __語法__

```php
$replaced = Str::of('(+1) 501-555-1000')
          ->replaceMatches('/[^A-Za-z0-9]++/', ''); // '15015551000'
$replaced = Str::of('123')
          ->replaceMatches('/\d/', function (array $matches) {

            return '['.$matches[0].']';
            
            }); // '[1][2][3]'
```

---

### *replaceStart()*

- __用途__

只有在字串`開頭`是指定內容時才取代。

- __語法__

```php
$replaced = Str::of('Hello World')->replaceStart('Hello', 'Laravel'); // 'Laravel World'
$replaced = Str::of('Hello World')->replaceStart('World', 'Laravel'); // 'Hello World'
```

---

### *replaceEnd()*

- __用途__

只有在字串`結尾`是指定內容時才取代。

- __語法__

```php
$replaced = Str::of('Hello World')->replaceEnd('World', 'Laravel'); // 'Hello Laravel'
$replaced = Str::of('Hello World')->replaceEnd('Hello', 'Laravel'); // 'Hello World'
```

---

### *scan()*

- __用途__

依照 `sscanf` 格式解析字串，**回傳 `Collection`**。

<!-- 
sscanf 是 PHP 的字串解析函式，
可以根據格式字串，把資料從字串中「拆解」出來，
常用於從複雜字串中擷取多個欄位。

sscanf 可以解析任意多個欄位，
只要格式字串和變數數量對應即可。
-->

<!-- 
sscanf('123 John 45.6', '%d %s %f', $id, $name, $score);

// '123 John 45.6'：原始字串

// '%d %s %f'：格式字串
//   %d：整數（會對應到 $id）
//   %s：字串（會對應到 $name）
//   %f：浮點數（會對應到 $score）

// $id = 123      // 取得第一個欄位（整數）
// $name = 'John' // 取得第二個欄位（字串）
// $score = 45.6  // 取得第三個欄位（浮點數） 
-->

<!-- 
可以根據需求用不同的格式：

%d：整數
%s：字串
%f：浮點數
還有其他格式（如 %x 十六進位、%c 字元等），
格式可以自由組合，不是固定的。 
-->

- __語法__

```php
$collection = Str::of('filename.jpg')->scan('%[^.].%s'); // collect(['filename', 'jpg'])
// 過程：
// 1. Str::of('filename.jpg') 產生 Fluent String 物件。
// 2. scan('%[^.].%s') 用 sscanf 格式解析字串：
//    - %[^.]：匹配所有不是 '.' 的字元（即 'filename'）
//    - .    ：匹配字元 '.'（分隔符）
//    - %s   ：匹配剩下的字串（即 'jpg'）
// 3. 解析結果組成 Collection：collect(['filename', 'jpg'])
```

---

### *singular()*

- __用途__

將字串`轉為單數`，支援多語系。

- __語法__

```php
$singular = Str::of('cars')->singular(); // 'car'
$singular = Str::of('children')->singular(); // 'child'
```

---

### *slug()*

- __用途__

將字串轉為 `URL 友善的 slug 格式`。

- __語法__

```php
$slug = Str::of('Laravel Framework')->slug('-'); // 'laravel-framework'
```

---

### *snake()*

- __用途__

將字串轉為 `snake_case`。

- __語法__

```php
$converted = Str::of('fooBar')->snake(); // 'foo_bar'
```

---

### *split()*

- __用途__

用`正則表達式分割`字串，**回傳 `Collection`**。

- __語法__

```php
$segments = Str::of('one, two, three')->split('/[\s,]+/'); // collect(["one", "two", "three"])
```

---

### *squish()*

- __用途__

`移除多餘空白`，單字間只保留一個空白。

- __語法__

```php
$string = Str::of('    laravel    framework    ')->squish(); // 'laravel framework'
```

---

### *start()* vs `finish()`

- __用途__

確保字串`開頭`有指定內容，若已存在則不重複加。

- __語法__

```php
$adjusted = Str::of('this/string')->start('/'); // '/this/string'
$adjusted = Str::of('/this/string')->start('/'); // '/this/string'
```

---

### *startsWith()*

- __用途__

判斷字串開頭`是否`為指定內容。

- __語法__

```php
$result = Str::of('This is my name')->startsWith('This'); // true
```

---

### *stripTags()*

- __用途__

`移除`字串中的所有 HTML 與 PHP 標籤，可指定`保留標籤`。

- __語法__

```php
$result = Str::of('<a href="https://laravel.com">Taylor <b>Otwell</b></a>')->stripTags(); // 'Taylor Otwell'
$result = Str::of('<a href="https://laravel.com">Taylor <b>Otwell</b></a>')->stripTags('<b>'); // 'Taylor <b>Otwell</b>'
```

---

### *studly()*

- __用途__

將字串轉為 `StudlyCase`（每個單字首字大寫，無分隔符號）。

- __語法__

```php
$converted = Str::of('foo_bar')->studly(); // 'FooBar'
```

---

### *substr()*

- __用途__

回傳字串`從指定位置開始、指定長度的子字串`。

- __語法__

```php
$string = Str::of('Laravel Framework')->substr(8); // 'Framework'
$string = Str::of('Laravel Framework')->substr(8, 5); // 'Frame'
```

---

### *substrReplace()*

- __用途__

從指定位置開始，`取代指定長度的內容`。長度為 *0* 時代表`插入`。

- __語法__

```php
$string = Str::of('1300')->substrReplace(':', 2); // '13:'
$string = Str::of('The Framework')->substrReplace(' Laravel', 3, 0); // 'The Laravel Framework'
```

- __註解__：

  - `第三參數`省略時，會`取代到字串結尾`。
  - 長度為 *0* 時，代表`插入`不取代。

<!-- 
replace 依「內容比對」取代，用來取代字串中的「指定內容」（整個子字串）。
substr_replace 依「位置」取代，是在「指定位置」插入、取代或刪除字串（根據起始位置和長度）。

// replace
str_replace('abc', 'XYZ', 'abc123'); // "XYZ123"

// substr_replace
substr_replace('abc123', 'XYZ', 0, 3); // "XYZ123"
// 從位置 0 開始，取代 3 個字元為 'XYZ' 
-->

---

### *swap()*

- __用途__

用陣列`批次取代`多組內容。

- __語法__

```php
$string = Str::of('Tacos are great!')->swap([
    'Tacos' => 'Burritos',
    'great' => 'fantastic',
]); // 'Burritos are fantastic!'
```

---

### *take()*

- __用途__

取出字串`開頭指定數量`的字元。

- __語法__

```php
$taken = Str::of('Build something amazing!')->take(5); // 'Build'
```

---

### *tap()*

- __用途__

將字串傳入閉包中操作，無論閉包回傳什麼，原字串會`繼續往下傳遞`。

- __語法__

```php
$string = Str::of('Laravel')
    ->append(' Framework')
    ->tap(function (Stringable $string) {
        dump('String after append: '.$string);
    })
    ->upper(); // 'LARAVEL FRAMEWORK'
```

---

### *test()*

- __用途__

判斷字串`是否`符合`正則表達式`。

- __語法__

```php
$result = Str::of('Laravel Framework')->test('/Laravel/'); // true
```

---

### *title()*

- __用途__

將字串轉為 `Title Case`（每個單字首字大寫）。

- __語法__

```php
$converted = Str::of('a nice title uses the correct case')->title(); // 'A Nice Title Uses The Correct Case'
```

---

### *toBase64()*

- __用途__

將字串轉為 `Base64` 編碼。

- __語法__

```php
$base64 = Str::of('Laravel')->toBase64(); // 'TGFyYXZlbA=='
```

---

### *toHtmlString()*

- __用途__

將字串轉為 `HtmlString` 物件，`Blade` 模板渲染時不會被 `escape`。

- __語法__

```php
$htmlString = Str::of('Nuno Maduro')->toHtmlString();
```

---

### *toUri()*

- __用途__

將字串轉為 `Uri 物件`。

- __語法__

```php
$uri = Str::of('https://example.com')->toUri();
```

---

### *transliterate()*

- __用途__

將字串轉為最接近的 `ASCII` 形式。

- __語法__

```php
$email = Str::of('ⓣⓔⓢⓣ@ⓛⓐⓡⓐⓥⓔⓛ.ⓒⓞⓜ')->transliterate(); // 'test@laravel.com'
```

---

### *trim()*

- __用途__

去除字串`前後的空白`或指`定字元`，支援 `Unicode` 空白。

- __語法__

```php
$string = Str::of('  Laravel  ')->trim(); // 'Laravel'
$string = Str::of('/Laravel/')->trim('/'); // 'Laravel'
```

---

### *ltrim()*

- __用途__

去除字串`左側的空白`或`指定字元`，支援 `Unicode` 空白。

- __語法__

```php
$string = Str::of('  Laravel  ')->ltrim(); // 'Laravel  '
$string = Str::of('/Laravel/')->ltrim('/'); // 'Laravel/'
```

---

### *rtrim()*

- __用途__

去除字串`右側的空白`或`指定字元`，支援 `Unicode` 空白。

- __語法__

```php
$string = Str::of('  Laravel  ')->rtrim(); // '  Laravel'
$string = Str::of('/Laravel/')->rtrim('/'); // '/Laravel'
```

---

### *ucfirst()*

- __用途__

將字串的`第一個字元轉為大寫`。

- __語法__

```php
$string = Str::of('foo bar')->ucfirst(); // 'Foo bar'
```

---

### *ucsplit()*

- __用途__

`依大寫字母分割字串`，**回傳 `Collection`**。

- __語法__

```php
$segments = Str::of('FooBar')->ucsplit(); // collect(['Foo', 'Bar'])
```

---

### *unwrap()*

- __用途__

`去除`字串 __開頭與結尾__ 的指定字元。

- __語法__

```php
Str::of('-Laravel-')->unwrap('-'); // 'Laravel'
Str::of('{framework: "Laravel"}')->unwrap('{', '}'); // 'framework: "Laravel"'
```

---

### *upper()*

- __用途__

將字串轉為`大寫`。

- __語法__

```php
$adjusted = Str::of('laravel')->upper(); // 'LARAVEL'
```

---

### *when()*

- __用途__

當條件為 `true` 時執行指定閉包，否則可執行另一個閉包。

- __語法__

```php
$string = Str::of('Taylor')
    ->when(true, function (Stringable $string) {
        return $string->append(' Otwell');
    }); // 'Taylor Otwell'
```

---

### *whenContains()*

- __用途__

當字串`包含`指定內容時執行閉包，可傳陣列。

- __語法__

```php
Str::of('tony stark')->whenContains(['tony', 'hulk'], function (Stringable $string) {
    return $string->title();
});

$string = Str::of('tony stark')->whenContains('tony', function (Stringable $string) {
    return $string->title();
}); // 'Tony Stark'
```

---

### *whenContainsAll()*

- __用途__

當字串同時`包含所有`指定內容時執行閉包。

- __語法__

```php
$string = Str::of('tony stark')->whenContainsAll(['tony', 'stark'], function (Stringable $string) {
    return $string->title();
}); // 'Tony Stark'
```

---

### *whenDoesntEndWith()*

- __用途__

當字串`結尾不是指定`內容時執行閉包。

- __語法__

```php
$string = Str::of('disney world')->whenDoesntEndWith('land', function (Stringable $string) {
    return $string->title();
}); // 'Disney World'
```

---

### *whenDoesntStartWith()*

- __用途__

當字串`開頭不是指定`內容時執行閉包。

- __語法__

```php
$string = Str::of('disney world')->whenDoesntStartWith('sea', function (Stringable $string) {
    return $string->title();
}); // 'Disney World'
```

---

### *whenEmpty()*

- __用途__

當字串`為空時`執行閉包。

- __語法__

```php
$string = Str::of('  ')->trim()->whenEmpty(function (Stringable $string) {
    return $string->prepend('Laravel');
}); // 'Laravel'
```

---

### *whenNotEmpty()*

- __用途__

當字串`不為空時`執行閉包。

- __語法__

```php
$string = Str::of('Framework')->whenNotEmpty(function (Stringable $string) {
    return $string->prepend('Laravel ');
}); // 'Laravel Framework'
```

---

### *whenStartsWith()*

- __用途__

當字串`開頭是指定內容時`執行閉包。

- __語法__

```php
$string = Str::of('disney world')->whenStartsWith('disney', function (Stringable $string) {
    return $string->title();
}); // 'Disney World'
```

---

### *whenEndsWith()*

- __用途__

當字串`結尾是指定內容時`執行閉包。

- __語法__

```php
$string = Str::of('disney world')->whenEndsWith('world', function (Stringable $string) {
    return $string->title();
}); // 'Disney World'
```

---

### *whenExactly()*

- __用途__

當字串`完全等於`指定內容時執行閉包。

- __語法__

```php
$string = Str::of('laravel')->whenExactly('laravel', function (Stringable $string) {
    return $string->title();
}); // 'Laravel'
```

---

### *whenNotExactly()*

- __用途__

當字串`不等於`指定內容時執行閉包。

- __語法__

```php
$string = Str::of('framework')->whenNotExactly('laravel', function (Stringable $string) {
    return $string->title();
}); // 'Framework'
```

---

### *whenIs()*

- __用途__

當字串`符合`指定萬用字元模式時執行閉包。

- __語法__

```php
$string = Str::of('foo/bar')->whenIs('foo/*', function (Stringable $string) {
    return $string->append('/baz');
}); // 'foo/bar/baz'
```

---

### *whenIsAscii()*

- __用途__

當字串為 `ASCII` 時執行閉包。

- __語法__

```php
$string = Str::of('laravel')->whenIsAscii(function (Stringable $string) {
    return $string->title();
}); // 'Laravel'
```

---

### *whenIsUlid()*

- __用途__

當字串為合法 `ULID` 時執行閉包。

- __語法__

```php
$string = Str::of('01gd6r360bp37zj17nxb55yv40')->whenIsUlid(function (Stringable $string) {
    return $string->substr(0, 8);
}); // '01gd6r36'
```

---

### *whenIsUuid()*

- __用途__

當字串為合法 `UUID` 時執行閉包。

- __語法__

```php
$string = Str::of('a0a2a2d2-0b87-4a18-83f2-2529882be2de')->whenIsUuid(function (Stringable $string) {
    return $string->substr(0, 8);
}); // 'a0a2a2d2'
```

---

### *whenTest()*

- __用途__

當字串`符合`_正則表達式_ 時執行閉包。

- __語法__

```php
$string = Str::of('laravel framework')->whenTest('/laravel/', function (Stringable $string) {
    return $string->title();
}); // 'Laravel Framework'
```

---

### *wordCount()*

- __用途__

`計算`字串中的`單字數量`。

- __語法__

```php
Str::of('Hello, world!')->wordCount(); // 2
```

- __註解__：

  - 會自動`忽略標點符號`，只計算`單字`。
  - 適合用於`字數統計`、`輸入驗證`等。

- __白話說明__：就是「有幾個單字」會回傳幾。

---

### *words()*

- __用途__

`限制`字串單字數，超過時`加上結尾字串`。

- __語法__

```php
$string = Str::of('Perfectly balanced, as all things should be.')->words(3, ' >>>'); // 'Perfectly balanced, as >>>'
```

- __註解__：

  - `第一參數`為保留的*單字數*。
  - `第二參數`為超過時加上的*結尾字串*（可省略，**預設**為 ...）。

- __白話說明__：適合做文章摘要、標題預覽等。

---

### *wrap()*

- __用途__

將主體字串`前後加上`指定內容，常用於 __自動加引號、括號、HTML 標籤、格式化輸出__ 等。

- __語法__

```php
Str::of($string)->wrap(before: '', after: '');
```

- _$string_：要被包裹的主體字串。
- _before_：要加在主體字串前面的內容（可省略）。
- _after_：要加在主體字串後面的內容（可省略）。

- __範例__

```php
Str::of('is')->wrap(before: 'This ', after: ' Laravel!'); // 'This is Laravel!'
Str::of('Laravel')->wrap(before: 'Hello, '); // 'Hello, Laravel'
Str::of('Laravel')->wrap(after: ' is great!'); // 'Laravel is great!'
Str::of('PHP')->wrap(before: '[', after: ']'); // '[PHP]'
Str::of('A')->wrap(before: '(', after: ')'); // '(A)'
Str::of('Laravel')->wrap('"'); // '"Laravel"'
```

__圖解__

_假設你呼叫_：

```php
Str::of('中心')->wrap(before: '【', after: '】');
```

_圖解如下_：

```
before   $string   after
  ↓        ↓        ↓
'【'   + '中心' + '】'
----------------------
      '【中心】'
```

__巢狀包裹__

可多次呼叫 `wrap` 達到多層包裹：

```php
Str::of(Str::of('A')->wrap(before: '[', after: ']'))->wrap(before: '(', after: ')'); // '([A])'
```

__常見誤區提醒__

- _具名參數_：`before:`、`after:` 是 PHP 8+ 的語法，舊版 PHP 不能用這種寫法，只能用順序傳參數。
- _不會自動加空白_：如果你想要有空白，要自己在 before/after 裡加空白。
  - 例：`before: 'Hello, '`（有空白）
- _before/after 可為任意字串_：可用於 HTML 標籤、符號、表情符號等。

- __應用場景__：

  - 產生 HTML 標籤
  - 自動加引號、括號
  - 格式化輸出（如 `[INFO] 訊息`）
  - 產生自訂格式的字串

- __白話說明__：`wrap` 就是「_自動幫你把字串前後加上你想要的東西_」，不用自己手動拼接，語意更清楚。

---

## **Fluent String 建立方式比較：`str()` vs `Str::of()`**

Laravel 的 `Fluent String`（*Stringable* 物件）有兩種常見建立方式，功能完全一樣：

---

### 1. *str() 輔助函式*（Laravel 8+ 推薦）

```php
str('hello')->upper()->append(' world');
```
- `Laravel 8` 之後推薦的寫法。
- **不需要 use**，直接用 `str()`。
- 回傳 `Stringable` 物件，支援鏈式操作。

---

### 2. *Str::of() 靜態方法*（Laravel 6+）

```php
use Illuminate\Support\Str;
Str::of('hello')->upper()->append(' world');
```
- `Laravel 6` 開始提供的寫法。
- **需要 use** `Illuminate\Support\Str;`
- 也是回傳 `Stringable` 物件，支援鏈式操作。

---

### *差異與建議*

- 兩種寫法**本質一樣**，都會回傳 `Stringable` 物件，方法完全相同。
- `str()` 是語法糖，底層其實就是呼叫 `Str::of()`。
- __新專案建議用 `str()`__，更簡潔、可讀性高。
- 舊專案或文件、筆記常見 `Str::of()`，也完全沒問題。

---

### *小結*

- 只要看到 `str('...')->` 或 `Str::of('...')->`，都代表「__建立一個 Fluent String 物件__」。
- 兩種都能用，選你喜歡的風格即可。

---

## **靜態方法 vs 動態方法 比較**

### *靜態方法*（Static Method）

- 屬於「_類別本身_」的方法，**不需建立物件**就能呼叫。
- 呼叫方式：`類別::方法()`
- __不能存取`物件的屬性`，只能用`類別層級`的資料__。
- 適合工具性、全域性、無狀態的操作。
- Laravel 例子：`Str::upper('abc')`

---

#### **PHP 範例**

```php
class MathTool {
    public static function add($a, $b) {
        return $a + $b;
    }
}

// 不用 new，直接呼叫
$result = MathTool::add(3, 5); // 8
```

---

#### **常見疑問補充**

一般會覺得「_類別就是要 new 物件才能用_」，但 `static` 方法是特例。

你可以把 `static` 方法想像成「工具箱裡的螺絲起子」：

- _一般方法（非 static）_＝你要先買一個工具箱（`new 物件`），才能拿裡面的工具來用。
- _靜態方法（static）_＝這個工具直接掛在牆上（類別本身），你隨時可以拿來用，不用先買工具箱。

__重點__：

- `靜態方法不會用到物件的屬性（$this）`，只會用到類別層級的資料。
- 適合做「__工具性、全域性、無狀態__」的操作。
- 例如 Laravel 的 `Str::upper('abc')`，你不需要 `new Str`，直接用就好。

---

### *動態方法*（Instance Method）

- 屬於「_物件_」的方法，必須先建立物件（`new`）才能呼叫。
- 呼叫方式：`$物件->方法()`
- 可以`存取物件的屬性`，能操作物件的狀態。
- 適合需要 __保存狀態、鏈式操作__ 的情境。
- Laravel 例子：`str('abc')->upper()` 或 `Str::of('abc')->upper()`

---

#### **PHP 範例**

```php
class Person {
    public $name;
    public function sayHello() {
        return 'Hello, I am ' . $this->name;
    }
}

$vincent = new Person();
$vincent->name = 'Vincent';
echo $vincent->sayHello(); // Hello, I am Vincent
```

---

### *差異整理表*
 
| 類型       | 呼叫方式                | 是否要 new 物件   |    能否存取物件屬性 | 例子                         |
|-----------|------------------------|-----------------|------------------|------------------------------|
| `靜態方法` | __類別::方法()__         | 不用            | 否               | `Str::upper('abc')`           |
| `動態方法` | __$物件->方法()__        | 要              | 可以             | `str('abc')->upper()`         |

---

## **`靜態方法`與`動態方法`：實務差異與選用時機**

### 1. *功能差異*

- __`靜態方法`（`Str::`）__：適合「_單步操作_」或「_一次性處理_」字串，無法鏈式操作。
- __`動態方法`（Fluent String, `str(), Str::of()`）__：適合「_多步驟處理_」或「_需要鏈式操作_」的情境，可連續呼叫多個方法。

- __範例__

比較：

```php
// 靜態方法
Str::upper('hello world'); // 'HELLO WORLD'
Str::replace('world', 'Laravel', 'hello world'); // 'hello Laravel'

// 動態方法（Fluent String）
str('  hello world  ')
    ->trim()
    ->replace('world', 'Laravel')
    ->upper()
    ->toString(); // 'HELLO LARAVEL'
```

---

### 2. *什麼時候只能用`動態方法`？*

- 需要 __多步驟、鏈式處理__ 字串時。
- 有些方法 __只存在於 Stringable 物件__，如 `append()`、`prepend()`、`pipe()`、`tap()` 等。
- 某些「_條件式_」或「_狀態保存_」的操作，只有動態方法支援。

---

### 3. *什麼時候用`靜態方法`就夠？*

- 只需要`單一步驟`處理，或只是單純轉大小寫、取代、判斷等。
- 不需要鏈式操作，也`不需要保存中間狀態`。

---

### 4. *實務選用建議*

- __簡單處理用靜態方法__，快速、直覺。
- __複雜、多步驟處理用動態方法__，可讀性高、彈性強。
- 兩種方法 __都很常用__，不是「一種就夠」的關係。

---

### 5. *例子比較表*

| 需求                     | 靜態方法可行 | 動態方法可行 | 推薦用法         |
|--------------------------|--------------|--------------|------------------|
| 單步轉大寫               | ✔            | ✔            | 靜態或動態皆可   |
| 先 trim 再轉大寫         | ✘            | ✔            | 動態方法         |
| 先 trim、再取代、再加字首| ✘            | ✔            | 動態方法         |
| 只判斷是否包含           | ✔            | ✔            | 靜態或動態皆可   |

---

### 6. *小結*
- __不是所有情境都能只用靜態方法__，尤其是多步驟、鏈式處理時。
- 你可以根據需求選擇，兩種都要會用，這樣寫程式才會又快又彈性！

---

#### **進階補充：`靜態方法`與`動態方法`的本質差異**

1. *`靜態方法`到底「取用」什麼？*

   - 靜態方法只能存取「_類別本身_」的`靜態屬性`和`靜態方法`，`不能存取物件（instance）`專屬的屬性和方法。
   - 靜態方法**沒有** `$this`，因為它不是屬於某個物件，而是屬於「類別」這個藍圖本身。
   - 你可以想像「類別本身」就像一個全域的工具櫃，裡面放著大家都能用的工具（_static 屬性/方法_）。
   
```php
   class Demo {
       public static $count = 0;
       public static function add() {
           self::$count++;
       }
       public $name;
       public function setName($n) { $this->name = $n; }
   }
   Demo::add(); // OK，操作 static $count
   // Demo::setName('Vincent'); // 錯誤，不能用類別直接呼叫動態方法
   ```

---

2. *`靜態方法`「會不會影響」原本的類別內容？*

   - 靜態方法操作的是「_類別層級_」的資料（`static 屬性`），這些資料是「_所有人共用_」的。
   - 如果你在`靜態方法`裡改變 `static 屬性`，會影響到所有地方（因為大家共用同一份）。
   - 但它**不會影響到任何物件的屬性**，因為根本沒物件存在。
   
```php
   class Counter {
       public static $count = 0;
       public static function add() { self::$count++; }
   }
   Counter::add();
   Counter::add();
   echo Counter::$count; // 2，因為大家共用同一個 $count
   ```
---

3. *`動態方法`（instance method）才會有「獨立的 new」*

   - 你 `new` 出來的每個物件，__都有自己的屬性、自己的資料，互不影響__。
   - 動態方法可以操作自己的屬性`（$this->xxx）`，不會動到別的物件或類別本身的 `static` 屬性。
   
```php
   class Person {
       public $name;
       public function setName($n) {
         $this->name = $n; 
         }
   }
   $a = new Person();
   $b = new Person();
   $a->setName('A');
   $b->setName('B');
   echo $a->name; // A
   echo $b->name; // B
   ```

---

4. *總結比喻*

   - `靜態方法`像「_公告欄_」：大家都看同一份，改了就全體都變。
   - `動態方法`像「_個人記事本_」：每個人有自己的，互不干擾。

---

#### **`static` 影響範圍與邊界**

1. *`static` 影響範圍是什麼？*

   - `static 屬性/方法`是「__類別本身__」的，不屬於任何一個物件。
   - 只要是**同一個類別（class）**，不管你在哪裡呼叫、呼叫幾次，大家都共用同一份 `static` 屬性。

__同一個類別，`static` 會共用__
   
```php
   class Counter {
       public static $count = 0;
       public static function add() {
           self::$count++;
       }
   }
   Counter::add();
   Counter::add();
   echo Counter::$count; // 2

   $a = new Counter();
   $b = new Counter();
   $a->add();
   $b->add();
   echo Counter::$count; // 4
   ```
   - 不管用類別還是 `new` 出來的物件呼叫，都是改同一份 static 屬性。

---

2. *不同類別，各自有自己的 `static`*
   
```php
   class A {
       public static $value = 0;
   }
   class B {
       public static $value = 0;
   }
   A::$value = 5;
   B::$value = 10;
   echo A::$value; // 5
   echo B::$value; // 10
   ```
   - `static` 屬性是「__每個類別自己有一份__」，不同類別互不影響。

---

3. *繼承的 `static` 會共用嗎？*
   
```php
   class ParentClass {
       public static $data = 1;
   }
   class ChildClass extends ParentClass {}
   ParentClass::$data = 5;
   echo ChildClass::$data; // 5
   ChildClass::$data = 10;
   echo ParentClass::$data; // 10
   ```
   - 在 PHP 裡，`static` 屬性是繼承下來的，`父子類別共用同一份`（除非子類別自己宣告一個同名 static 屬性）。

---

4. *`static` 影響「整個專案」嗎？*

   - 只會影響「_同一個類別_」的所有地方，不會影響到其他類別。
   - 你在專案任何地方改了這個類別的 static 屬性，所有用到這個類別的地方都會看到最新的值。

---

5. *`static` 的「邊界」是什麼？*
   - 邊界就是「_類別_」本身。
   - 只要是同一個 class，static 屬性/方法就是共用的。
   - 不同 class，static 屬性互不干擾。
   - 如果有繼承，`父子類別預設共用同一份`（除非子類別自己宣告一個同名 static 屬性）。

6. *生活化比喻*

   - `static` 像「__公司公告欄__」：這家 _公司（class）_ 所有 _員工（物件）_ 都看同一個 _公告欄（static 屬性）_，公告一改，大家都看到新內容。
   - `動態屬性`像「__員工個人記事本__」：每個 _員工（物件）_ 有自己的 _記事本（屬性）_，互不干擾。

---

#### **PHP 的傳值與傳址**

1. *傳值*（pass by value）
   - 傳進去的是「_副本_」，函式裡怎麼改，外面的變數都不會變。
   - 例子：
     
     ```php
     function foo($x) { 
        $x = 10; 
       }
     $a = 5;
     foo($a);
     echo $a; // 5
     ```
     - `foo` 裡面改的是 `$x` 的副本，`$a` 不受影響。

---

2. *傳址*（pass by reference，傳參考）
   - 傳進去的是「_原本的位址_」，函式裡怎麼改，外面的變數也會跟著變。
   - 例子：
     
     ```php
     function foo(&$x) {
         $x = 10;
        }
     $a = 5;
     foo($a);
     echo $a; // 10
     ```
     - `foo` 裡面改 `$x`，其實就是改 `$a`，因為兩個`指向同一個記憶體位置`。

---

3. *雙向影響*
   - `傳址`時，不管在裡面改還是外面改，都是同一份資料，彼此都會影響。
   - 例子：
     
     ```php
     function foo(&$x) {
         $x = 20; // 這裡改了，外面的變數也會變
     }
     $a = 5;
     foo($a);
     echo $a; // 20

     $a = 99; // 外面改了
     function bar(&$y) {
         echo $y; // 99
     }
     bar($a); // 會印出 99
     ```

---

   **更多雙向影響例子**：
   
   ```php
   function addOne(&$num) {
       $num++;
   }

   $a = 1;
   $b =& $a;     // $b 是 $a 的參考

   addOne($b);   // $b 變 2，$a 也變 2
   echo $a;      // 2
   echo $b;      // 2

   $a = 99;      // 改 $a，$b 也會變
   echo $b;      // 99
   ```

---

   **陣列、物件的雙向影響**：
   
   ```php
   function addTitle(&$arr) {
       $arr['name'] = 'Mr. ' . $arr['name'];
   }

   $data = ['name' => 'John'];
   addTitle($data);
   echo $data['name']; // Mr. John

   $data['name'] = 'Mary';  // 外面改成 Mary
   addTitle($data);         // 函式裡會用到最新的 Mary
   echo $data['name'];      // Mr. Mary
   ```

---

4. *`傳值`需要回傳才能改變*
   - `傳值`時，__函式裡改的是`副本`，要改變原本變數必須`回傳並重新賦值`__。
   - 例子：
     
     ```php
     function foo($x) {
         $x = $x + 10;
         return $x;  // 要回傳結果
     }
     $a = 5;
     $a = foo($a);   // 把回傳值再賦值給 $a
     echo $a;        // 15
     ```

---

   **比較三種方式**：
   
   ```php
   // 1. 傳址（直接改原本）
   function foo1(&$x) {
       $x = $x + 10;
   }
   $a = 5;
   foo1($a);        // 直接改 $a
   echo $a;         // 15

---

   // 2. 傳值 + 回傳
   function foo2($x) {
       $x = $x + 10;
       return $x;
   }
   $a = 5;
   $a = foo2($a);   // 回傳並重新賦值
   echo $a;         // 15

---

   // 3. 傳值（不處理回傳）
   function foo3($x) {
       $x = $x + 10;
   }
   $a = 5;
   foo3($a);        // $a 不會變
   echo $a;         // 5
   ```

---

5. *生活化比喻*

   - __傳址__：你和朋友共用一個「_雲端記事本_」，你改內容，朋友馬上看到；朋友改內容，你也馬上看到。
   - __傳值 + 回傳__：你複製一份給朋友，朋友改完後把改好的內容`貼回你的原本`。
   - __傳值（不處理）__：你複製一份給朋友，朋友改他的副本，你的原本沒動。

---

6. *`static 屬性`與`傳址的`類比*

   - `static 屬性/方法` ≈ __所有人__ 都用同一份資料（像 _傳址_）
   - `動態屬性/方法` ≈ __每個人__ 有自己的資料（像 _傳值_）
   - 但 `static` 跟`傳值/傳址`是不同層次的東西，只是「_共用_」這個行為有點像「_傳址_」的效果。

---