# 1. *Eloquent: Mutators & Casting 筆記*

---

## 1.1 **簡介**

`Accessors`（存取器）、`Mutators`（修改器）、`Attribute Casting`（屬性轉型）可讓你在存取或設定 Eloquent 屬性時 *自動轉換資料*。例如：__加密/解密、JSON 轉陣列__ 等。

```php
/**
 * Laravel Accessor & Mutator 命名困惑解析
 * 
 * 問題：為什麼 Accessor 叫「存取器」，但只有「取」沒有「存」？
 */

namespace App\Models;

use App\Support\Address;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 地址屬性的 Accessor & Mutator
     */
    protected function address(): Attribute
    {
        return Attribute::make(
            // 🔍 Accessor（存取器）- 實際上只有「取」的功能
            get: fn (mixed $value, array $attributes) => new Address(
       //   ^^^ 
            //   「get」= 取值/讀取
            //   更準確的中文：「取值器」或「讀取器」
            //   
            //   觸發時機：當讀取 $user->address 時
            //   功能：從資料庫欄位組合成 Address 物件
                $attributes['address_line_one'],
                $attributes['address_line_two'],
            ),
            
            // 🔧 Mutator（修改器）- 實際上是「存」的功能  
            set: fn (Address $value) => [            // 「修改」屬性時觸發
       //   ^^^
            //   「set」= 設值/寫入
            //   更準確的中文：「設值器」或「寫入器」
            //   
            //   觸發時機：當設定 $user->address = new Address(...) 時
            //   功能：將 Address 物件拆解成資料庫欄位
                'address_line_one' => $value->lineOne,
                'address_line_two' => $value->lineTwo,
            ],
            //                            ^^^^^^^^^^^^^^^^^^ 設定新值
        );
    }
}

/**
 * 🤔 命名困惑解析
 * 
  1.__ Accessor = 存取器__
 *    - 字面意思：存取（存 + 取）
 *    - 實際功能：只有「取」
 *    - 更好翻譯：取值器、讀取器、getter
 * 
  2. __Mutator = 修改器 __ 
 *    - 字面意思：修改
 *    - 實際功能：「存」/設定
 *    - 更好翻譯：設值器、寫入器、setter
 * 
  3. __為什麼叫 Accessor？__
 *    - 程式設計中「access」通常指「存取/訪問」（偏向讀取）
 *    - 類似：存取檔案 = 讀取檔案
 *    - 但在中文翻譯時容易產生混淆
 */

// 💡 使用範例
$user = User::find(1);

// 觸發 Accessor（取值器）
$address = $user->address;           // get: 從資料庫欄位組合成物件

// 觸發 Mutator（設值器）  
$user->address = new Address(        // set: 將物件拆解成資料庫欄位
    '123 Main Street',
    'Apartment 4B'
);
$user->save();

/**
 * 🎯 建議的理解方式
 * 
 * - Accessor = 取值器（只管讀取/輸出）
 * - Mutator = 設值器（只管寫入/輸入）
 * - get = 讀取時執行的邏輯
 * - set = 設定時執行的邏輯
 * 
 * 這樣就不會被「存取」這個翻譯搞混了！
 */
 ```

---

## 1.2 **Accessors & Mutators**

### 1.2.1 *定義 Accessor*（存取器）

- `Accessor` 會在**存取屬性時**自動呼叫，`方法名稱`需為屬性**駝峰式命名**。
- 回傳 `Illuminate\Database\Eloquent\Casts\Attribute` 實例。

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 取得使用者的名字。
     */
    protected function firstName(): Attribute
    //                 ^^^^^^^^^ ^^^^^^^^^
    //                 方法名稱   回傳型別
    //                
    //                 定義 first_name 欄位的存取器 (Accessor)
    //                 方法名稱對應資料庫欄位 (snake_case)
    {
        return Attribute::make(
        //     ^^^^^^^^^  ^^^^
        //     建立屬性    方法
        //     
        //     建立新的屬性存取器實例
            get: fn (string $value) => ucfirst($value),
            //   ^^^ ^^^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^
            //   取值 原始資料庫值   轉換後回傳值
            //   
            //   當存取 $user->first_name 時：
            //   從資料庫取得原始值 → 執行 ucfirst() → 回傳首字母大寫的值
        );
    }
}
```

---

- **存取方式**：

```php
$user = User::find(1);
// 直接存取屬性
$firstName = $user->first_name;        // ✅ 觸發 address() accessor

//           ^^^^^ ^^^^^^^^^^
//           模型  存取器屬性
//           
//           存取 first_name 存取器
//           自動執行 firstName() 方法中的 get 邏輯
//           回傳轉換後的值（如：首字母大寫）

// 轉換為陣列
$array = $user->toArray();        // ✅ 觸發所有 accessor（如果在 $appends 中）

// 轉換為 JSON
$json = $user->toJson();          // ✅ 觸發所有 accessor（如果在 $appends 中）

// 序列化
echo json_encode($user);          // ✅ 觸發所有 accessor（如果在 $appends 中）

// 直接從資料庫取得原始值
$user = User::find(1);
$rawValue = $user->getOriginal('address');       // ❌ 不觸發，取得原始值
$rawValue = $user->getRawOriginal('address');    // ❌ 不觸發，取得原始值
```

---

- 若要讓計算屬性出現在 `array/JSON`，需用 `$appends`。

```php
class User extends Model
{
    protected $appends = ['first_name', 'last_name'];
    //         ^^^^^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^
    //         附加屬性  要序列化的存取器
    //         
    //         將存取器屬性加入 toArray() 和 toJson() 輸出
    //         預設存取器不會出現在序列化結果中

    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
        );
    }

    // 使用結果：
    // $user->toArray()  包含 first_name
    // $user->toJson()   包含 first_name
}
```

---

- `序列化` (Serialization) 是*將物件或資料結構轉換成`可儲存或傳輸格式`的過程*。

```php
// 序列化：將 PHP 物件轉換成其他格式
$user = User::find(1);          // Eloquent 模型物件

// 轉換成陣列格式
$array = $user->toArray();      // 序列化成陣列
// ['id' => 1, 'name' => 'John', 'email' => 'john@example.com']

// 轉換成 JSON 格式  
$json = $user->toJson();        // 序列化成 JSON 字串
// '{"id":1,"name":"John","email":"john@example.com"}'
```

---

### 1.2.2 *多屬性組合 Value Object*

- `get 閉包`可接收第二參數 `$attributes`，取得 __所有原始屬性__：

```php
use App\Support\Address;
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function address(): Attribute
//                 ^^^^^^^ ^^^^^^^^^
//                 方法名稱 回傳型別
//                
//                 定義 address 欄位的存取器 (Accessor)
//                 將多個資料庫欄位組合成單一物件
{
    return Attribute::make(
        get: fn (mixed $value, array $attributes) => new Address(
        //   ^^^ ^^^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^ ^^^ ^^^^^^^
        //   取值 目前欄位值     所有模型屬性       建立 Address物件
        //   
        //   當存取 $user->address 時：
        //   1. 取得所有模型屬性 ($attributes)
        //   2. 從中提取 address_line_one 和 address_line_two
        //   3. 建立並回傳 Address 物件實例
            $attributes['address_line_one'],
            //^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^
            //從屬性陣列 取得地址第一行
            $attributes['address_line_two'],
            //^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^
            //從屬性陣列 取得地址第二行
        ),
    );
}
```

---

### 1.2.3 *Accessor 快取*

- 回傳物件時，Eloquent 會**自動快取並同步物件內容**。
- 可用 `shouldCache()` **快取原始型別**（如字串、布林）。
- 用 `withoutObjectCaching()` 可**關閉**物件快取。

```php
protected function hash(): Attribute
{
    return Attribute::make(
        // ✅ 箭頭函數（簡潔）：「=>」後面就是傳統函數 {} 大括號內的內容
        get: fn (string $value) => bcrypt(gzuncompress($value)),
        // ❌ 傳統寫法（冗長）          ↑         ↑
        //                           外層       內層
        //     
        // 讀法：「對 value 先解壓縮，再加密」
        get: function (string $value) {
            $uncompressed = gzuncompress($value);
            $encrypted = bcrypt($uncompressed);
            return $encrypted;
            },
    )->shouldCache();
}

// 簡單轉換
get: fn (string $value) => strtoupper($value),

// 多重處理
get: fn (string $value) => trim(strtolower($value)),

// 條件處理
get: fn (?string $value) => $value ? ucfirst($value) : null,

// JSON 處理
get: fn (string $value) => json_decode($value, true),
```

---

### 1.2.4 *定義 Mutator*（修改器）

- **set 閉包**會在`設定屬性時`自動呼叫。

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 互動使用者名字。
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }
}
```

---

- **使用方式**：

```php
// 先取得模型實例
$user = new User();

// 直接賦值
$user->address = new Address('123 Main St', 'Apt 4');  // ✅ 觸發 address() mutator

// 使用 fill()
$user->fill([
    'address' => new Address('456 Oak Ave', 'Suite 2'), // ✅ 觸發 address() mutator
]);

// 使用 create()
User::create([
    'name' => 'John',
    'address' => new Address('789 Pine St', ''),        // ✅ 觸發 address() mutator
]);

// 使用 update()
// 呼叫模型實例的 update() 方法
$user->update([
    'address' => new Address('321 Elm St', 'Unit B'),   // ✅ 觸發 address() mutator
]);

// 直接操作資料庫
// 直接在查詢建構器上呼叫 update()
User::where('id', 1)->update([
    'address_line_one' => '123 Main St'             // ❌ 不觸發，直接更新資料庫
]);

// 使用 DB facade
DB::table('users')->where('id', 1)->update([
    'address_line_one' => '123 Main St'             // ❌ 不觸發，繞過 Eloquent
]);
```

---

### 1.2.5 *Mutator 設定多個屬性*

- `set 閉包`可回傳 __陣列__，設定`多個欄位`：

```php
use App\Support\Address;
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function address(): Attribute
{
    return Attribute::make(
        get: fn (mixed $value, array $attributes) => new Address(
        //   ^^^ ^^^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^ ^^^ ^^^^^^^
        //   存取器 目前欄位值   所有模型屬性       建立 Address物件
        //   
        //   當讀取 $user->address 時：
        //   1. 取得所有模型屬性 ($attributes)
        //   2. 從中提取 address_line_one 和 address_line_two
        //   3. 建立並回傳 Address 物件實例
            $attributes['address_line_one'],
            $attributes['address_line_two'],
        ),
        set: fn (Address $value) => [
        //   ^^^ ^^^^^^^ ^^^^^^^ ^^^ ^
        //   修改器 Address物件   回傳 陣列
        //   
        //   當設定 $user->address = new Address(...) 時：
        //   1. 接收 Address 物件
        //   2. 提取物件的 lineOne 和 lineTwo 屬性
        //   3. 回傳關聯陣列，指定要更新的資料庫欄位
            'address_line_one' => $value->lineOne,
            //^^^^^^^^^^^^^^^^^^ ^^^ ^^^^^^^^^^^^^^
            //資料庫欄位名稱       從   Address物件屬性
            'address_line_two' => $value->lineTwo,
            //^^^^^^^^^^^^^^^^^^ ^^^ ^^^^^^^^^^^^^^
            //資料庫欄位名稱       從   Address物件屬性
        ],
    );
}
```

---

## 1.3 **Attribute Casting**（屬性轉型）

- 直接於 `casts()` 方法回傳 *`陣列設定`屬性型別*。
- 支援型別：
```php
            array       // 將 JSON 字串轉換為「PHP 陣列」
            boolean     // 將數值 0/1 或字串 '0'/'1' 轉換為「true/false」
            collection  // 將 JSON 字串轉換為「Illuminate\Support\Collection 實例」
            date        // 將日期字串轉換為「Carbon 日期物件」（只包含日期，不含時間）
            datetime    // 將日期時間字串轉換為「Carbon 日期時間物件」
            decimal     // 將數值轉換為「指定精度的十進位數字字串」（指定小數位數）
            // 例如：protected $casts = ['price' => 'decimal:2'];
            // 存入 123.456，讀出來是 '123.46'（字串，保留 2 位小數）

            double      // 將數值轉換為「雙精度浮點數」
            // 例如：protected $casts = ['weight' => 'double'];
            // 存入 12.34，讀出來是 12.34（數值型態，精度較高）

            encrypted   // 自動加密儲存、解密讀取的「字串資料」
            float       // 將數值轉換為「浮點數」
            integer     // 將數值轉換為「整數」
            object      // 將 JSON 字串轉換為「stdClass 物件」
            real        // 將數值轉換為「實數」（浮點數的別名）
            string      // 將數值轉換為「字串」
            timestamp   // 將時間戳記轉換為「Carbon 物件」
            enum        // 將字串值轉換為「指定的 Enum 類別實例」
```

---

- *`Cast` = 真正的「存取」(存 + 取)*

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 屬性轉型設定。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
        ];
    }
}
```

---

- **存取時**自動轉型：

```php
$user = App\Models\User::find(1);
if ($user->is_admin) {
    // ...
}
```

---

- **動態新增** cast：

```php
// mergeCasts() 是 Laravel Eloquent 的內建方法！
$user->mergeCasts([
//    ^^^^^^^^^^ ^^^^
//    方法名稱    新增的轉型規則
//    
//    動態為模型實例新增額外的屬性轉型規則
//    不會影響模型類別的預設 $casts 設定
    'is_admin' => 'integer',
    //^^^^^^^^^ ^^^^^^^^^^
    //屬性名稱   轉型類型
    //
    //將 is_admin 屬性轉換為整數
    //例如：資料庫的 '1' 字串 → PHP 的 1 整數
    'options' => 'object',
    //^^^^^^^ ^^^^^^^^^
    //屬性名稱 轉型類型
    //
    //將 options 屬性轉換為物件
    //例如：資料庫的 JSON 字串 → PHP 的 stdClass 物件
]);

// 使用結果：
// $user->is_admin  會自動轉為 integer
// $user->options   會自動轉為 object
```

---

- **限制**：_不可與關聯或主鍵同名_。

```php
/**
 * mergeCasts() 的限制說明與範例
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $primaryKey = 'id';  // 主鍵
    
    protected $casts = [
        'settings' => 'array',
    ];

    // 定義關聯
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
    
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}

// 使用範例
$user = User::find(1);

// ✅ 正確用法：與關聯和主鍵不同名
$user->mergeCasts([
    'is_admin' => 'boolean',     // ✅ 可以，不與關聯或主鍵同名
    'metadata' => 'object',      // ✅ 可以，不與關聯或主鍵同名
    'score' => 'float',          // ✅ 可以，不與關聯或主鍵同名
]);

// ❌ 錯誤用法：與主鍵同名
$user->mergeCasts([
    'id' => 'string',            // ❌ 不可以！'id' 是主鍵
]);

// ❌ 錯誤用法：與關聯同名
$user->mergeCasts([
    'posts' => 'array',          // ❌ 不可以！'posts' 是關聯方法名稱
    'comments' => 'object',      // ❌ 不可以！'comments' 是關聯方法名稱
]);

/**
 * 為什麼有這個限制？
 * 
  1. __主鍵衝突__
 *    - 主鍵有特殊用途，不應該被 cast 影響
 *    - 可能破壞 Eloquent 的內部機制
 * 
  2. __關聯衝突__
 *    - $user->posts 應該回傳關聯資料，不是 cast 後的屬性
 *    - cast 會覆蓋關聯的存取，造成功能異常
 */

// 實際衝突範例
$user = User::find(1);

// 如果允許對關聯名稱做 cast
$user->mergeCasts(['posts' => 'array']);

// 這時候會發生什麼？
$posts = $user->posts;
// 應該回傳 Post 模型的 Collection
// 還是回傳 cast 成 array 的資料庫欄位？
// → 造成混亂和不可預期的行為

/**
 * 🎯 安全的做法
 */
class User extends Model
{
    // 使用不同的屬性名稱
    protected $casts = [
        'post_ids' => 'array',        // ✅ 儲存文章 ID 陣列
        'post_count' => 'integer',    // ✅ 文章數量
        'user_meta' => 'object',      // ✅ 使用者元資料
    ];
    
    // 關聯保持原名
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
```

```php
// 相關的內建方法
$user->getCasts();                    // 取得所有 cast 規則
$user->mergeCasts(['key' => 'type']); // 合併 cast 規則
$user->addCast('key', 'type');        // 新增單個 cast（如果存在的話）
```
---

### 1.3.1 *Stringable Casting*

- 使用 `AsStringable::class` 轉為 `Stringable` 物件：

```php
/**
 * Laravel Stringable Casting 說明
 * 
 * 什麼是 Stringable？能做什麼？
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsStringable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected function casts(): array
    {
        return [
            'directory' => AsStringable::class,
            //^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^
            //屬性名稱   轉為 Stringable 物件
            //
            //將資料庫的字串轉換為 Illuminate\Support\Stringable 實例
            //提供豐富的字串操作方法，支援鏈式呼叫
        ];
    }
}

/**
 * 🎯 Stringable 物件的強大功能
 */

$user = User::find(1);
// 假設 directory = '/home/user/documents'

// ✅ 鏈式字串操作
$result = $user->directory
    ->replace('/home', '/Users')     // 替換路徑
    ->append('/photos')              // 附加子目錄
    ->finish('/')                    // 確保結尾有斜線
    ->__toString();                  // 轉回字串
// 結果：'/Users/user/documents/photos/'

// ✅ 路徑處理
$cleanPath = $user->directory
    ->ltrim('/')                     // 移除開頭斜線
    ->rtrim('/')                     // 移除結尾斜線
    ->explode('/')                   // 分割成陣列
    ->filter()                       // 過濾空值
    ->implode('/');                  // 重新組合

// ✅ 字串驗證與轉換
if ($user->directory->startsWith('/home')) {
    $newPath = $user->directory
        ->after('/home/')            // 取得 '/home/' 之後的部分
        ->before('/documents')       // 取得 '/documents' 之前的部分
        ->studly();                  // 轉為 StudlyCase
}

/**
 * 🔍 與一般字串的差異
 */

// ❌ 一般字串：需要多行程式碼
$directory = $user->getRawOriginal('directory'); // 取得原始字串
$directory = str_replace('/home', '/Users', $directory);
$directory = $directory . '/photos';
$directory = rtrim($directory, '/') . '/';

// ✅ Stringable：優雅的鏈式操作
$directory = $user->directory
    ->replace('/home', '/Users')
    ->append('/photos')
    ->finish('/');

/**
 * 📊 常用的 Stringable 方法
 */

$text = $user->directory; // Stringable 實例

// 字串轉換
$text->upper();          // 轉大寫
$text->lower();          // 轉小寫
$text->title();          // 首字母大寫
$text->camel();          // 駝峰命名
$text->snake();          // 蛇形命名

// 字串操作
$text->append('suffix'); // 附加文字
$text->prepend('prefix'); // 前綴文字
$text->replace('old', 'new'); // 替換
$text->trim();           // 去除空白

// 字串檢查
$text->contains('sub');  // 是否包含
$text->startsWith('/'); // 是否以...開始
$text->endsWith('/');   // 是否以...結束
$text->isEmpty();       // 是否為空

// 字串提取
$text->substr(0, 10);   // 擷取子字串
$text->after('marker'); // 標記之後的內容
$text->before('marker'); // 標記之前的內容

/**
 * 💡 實際應用場景
 */

class User extends Model
{
    protected function casts(): array
    {
        return [
            'full_name' => AsStringable::class,
            'bio' => AsStringable::class,
            'file_path' => AsStringable::class,
        ];
    }
}

$user = User::find(1);

// 姓名處理
$displayName = $user->full_name
    ->trim()                    // 去除空白
    ->title()                   // 首字母大寫
    ->limit(20, '...');         // 限制長度

// 檔案路徑處理
$safePath = $user->file_path
    ->replace('\\', '/')        // 統一路徑分隔符
    ->ltrim('/')               // 移除開頭斜線
    ->finish('/');             // 確保結尾斜線

// 自我介紹處理
$shortBio = $user->bio
    ->stripTags()              // 移除 HTML 標籤
    ->limit(100)               // 限制字數
    ->finish('...');           // 加上省略號

/**
 * 🎯 為什麼使用 Stringable Casting？
 * 
 * 1. 鏈式操作：優雅的連續字串處理
 * 2. 方法豐富：內建大量實用的字串方法
 * 3. 可讀性高：程式碼更易於理解和維護
 * 4. 效能好：延遲執行，只在需要時才轉換
 * 5. 類型安全：IDE 提供完整的自動補全
 */
```

---

### 1.3.2 *Array / JSON Casting*

- `array` cast 會自動將 `JSON 欄位 `轉為 `PHP 陣列` ，設定時 *自動序列化*：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 取得模型的屬性轉型規則
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    //                ^^^^^ ^^^^^^^^^
    //                方法名 回傳型別
    //                
    //                定義屬性的轉型規則（Cast）
    //                替代傳統的 protected $casts 屬性
    //                支援動態邏輯和條件判斷
    {
        return [
            'options' => 'array',
            //^^^^^^^ ^^^^^^^^
            //屬性名稱  轉型類型
            //
            //將 options 欄位進行雙向轉換：
            //讀取：JSON 字串 → PHP 陣列
            //設定：PHP 陣列 → JSON 字串
            //
            //使用：
            //$user->options = ['theme' => 'dark'];  // 陣列自動轉為 JSON 存入資料庫
            //$settings = $user->options;            // JSON 自動轉為陣列回傳
        ];
    }
}
```

---

- **直接更新** JSON 欄位：
  - 這個功能與 `Cast` 無關，是 Laravel 的 `JSON` 欄位操作功能！

```php
$user = User::find(1);
$user->update(['options->key' => 'value']);
//              ^^^^^^^^ ^^^^^ ^^^^^^^
//              JSON欄位 路徑  新值
//              
//              使用 JSON 路徑語法直接更新 JSON 欄位中的特定鍵值
//              不需要先讀取整個 JSON，修改後再寫回
//              Laravel 會產生對應的 SQL JSON 更新語句
//
//              例如：options = {"theme": "dark", "lang": "en"}
//              執行後：options = {"theme": "dark", "lang": "en", "key": "value"}
//
//              實際 SQL：
//              UPDATE users SET options = JSON_SET(options, '$.key', 'value') WHERE id = 1
```

---

- 若需儲存 **unescaped unicode**，可用 `json:unicode`：

<!-- 

unescaped unicode 指的是「未跳脫的 Unicode 字元」，
也就是直接顯示原始字元（例如中文、emoji），
而不是像 \u4e2d\u6587 這種 Unicode 編碼格式。

即使是 unescaped unicode，底層還是用 Unicode 編碼儲存字元，
只是顯示時直接呈現原始字元，不再用 \uXXXX 這種跳脫格式。
 -->

```php
protected function casts(): array
{
    return [
        'options' => 'json:unicode',
        //           ^^^^^ ^^^^^^^
        //           JSON  Unicode選項
        //           
        //           使用 JSON_UNESCAPED_UNICODE 旗標進行 JSON 編碼
        //           保持中文、表情符號等 Unicode 字元的原始形式
        //           而非轉換為 \uXXXX 的轉義序列
        //
        //           範例：
        //           一般 json: {"name":"張三","emoji":"😊"} 
        //                   → {"name":"\u5f35\u4e09","emoji":"\ud83d\ude0a"}
        //           
        //           json:unicode: {"name":"張三","emoji":"😊"}
        //                      → {"name":"張三","emoji":"😊"}
    ];
}
```

---

- **`Unicode` 說明**

```php
/**
 * 🌍 Unicode 是什麼？
 * 
 * Unicode 是一個國際標準，為世界上所有的文字和符號
 * 分配唯一的數字代碼（碼點 Code Point）
 */

// 每個字元都有一個唯一的 Unicode 碼點
'A'   → U+0041    // 拉丁字母 A
'中'  → U+4E2D    // 中文字「中」
'😊'  → U+1F60A   // 笑臉表情符號
'€'   → U+20AC    // 歐元符號

/**
 * 📊 Unicode 的目的
 */

// 解決不同編碼系統的混亂問題：
// 早期編碼系統：
// - ASCII：只支援英文（128個字元）
// - Big5：中文繁體
// - GB2312：中文簡體
// - Shift_JIS：日文

// Unicode 統一標準：
// - 支援全世界所有語言
// - 包含表情符號、數學符號、古文字等
// - 目前約有 140,000+ 個字元

/**
 * 🔍 什麼是 Unicode Escape？
 */

// Unicode 字元有兩種表示方式：

// 1. 原始形式（Unescaped）
"張三"     // 直接顯示中文字元
"😊"       // 直接顯示表情符號

// 2. 轉義形式（Escaped）  
"\u5f35\u4e09"           // 張三 的 Unicode 編碼
"\ud83d\ude0a"           // 😊 的 Unicode 編碼

/**
 * 📊 JSON 編碼的差異
 */

$data = [
    'name' => '張三',
    'emoji' => '😊',
    'message' => 'Hello 世界'
];

// 一般 JSON 編碼（Escaped）
json_encode($data);
// 結果：{"name":"\u5f35\u4e09","emoji":"\ud83d\ude0a","message":"Hello \u4e16\u754c"}

// Unescaped Unicode JSON 編碼
json_encode($data, JSON_UNESCAPED_UNICODE);
// 結果：{"name":"張三","emoji":"😊","message":"Hello 世界"}

/**
 * 🎯 Laravel Cast 中的應用
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected function casts(): array
    {
        return [
            // 一般 json cast
            'settings' => 'json',
            
            // Unescaped unicode json cast
            'profile' => 'json:unicode',
        ];
    }
}

$user = new User();
$user->settings = ['name' => '李四', 'city' => '台北'];
$user->profile = ['bio' => '你好世界 👋', 'hobby' => '程式設計'];

// 儲存到資料庫時的差異：

// settings 欄位（一般 json）：
 {"name":"\u674e\u56db","city":"\u53f0\u5317"}

 profile 欄位（json:unicode）：
 {"bio":"你好世界 👋","hobby":"程式設計"}

/**
 * 💡 為什麼需要 Unescaped Unicode？
 */

 1. `可讀性`：直接查看資料庫時能看懂內容
// MySQL 查詢結果：
// 一般：{"name":"\u5f35\u4e09"}          ❌ 難以閱讀
// Unicode：{"name":"張三"}               ✅ 容易閱讀

 2. `儲存空間`：某些情況下更節省空間
// 一般："\u5f35\u4e09" (12 字元)
// Unicode："張三" (2 字元)

 3. `偵錯方便`：錯誤訊息更容易理解
// 一般：Error in field "\u59d3\u540d"
// Unicode：Error in field "姓名"

/**
 * 🎯 總結
 * 
  __Unicode 是什麼？__
 * - 全球統一的字元編碼標準
 * - 為每個字元分配唯一的數字代碼
 * - 支援所有語言、符號、表情符號
 * 
  __Escaped vs Unescaped__
 * - Escaped Unicode：  \u5f35\u4e09 （編碼形式）
 * - Unescaped Unicode：張三 （原始形式）
 * 
  __Laravel json:unicode 的優勢__
 * - 讓 JSON 保持人類可讀的 Unicode 字元
 * - 直接查看資料庫時能看懂內容
 * - 偵錯和維護更容易
 */
```
---

### 1.3.3 *ArrayObject / Collection Casting*

- Laravel `save()` 方法的關鍵邏輯

```php
// Laravel 的 save() 方法邏輯：
public function save()
{
    // 1. 檢查是否有 dirty 屬性
    if (!$this->isDirty()) {
        return false;  // 沒有變更，直接返回，不執行 SQL
    }
    
    // 2. 只有當 isDirty() = true 時，才會執行 UPDATE SQL
    // ...執行實際的資料庫更新
}

// 如果一般 array 沒有驗證「髒不髒」，
// 你修改了內容但 Eloquent 不會偵測到，
// 呼叫 save() 時資料庫不會更新，
// 導致資料可能沒被正確儲存。
// 但體感上是已經完成save()。

/**
 * 🎯 這就是一切問題的根源！
 */

// ❌ 一般 array cast 的問題鏈
$user->options['theme'] = 'dark';    // 修改陣列內容
↓
$user->isDirty('options');           // false（Laravel 偵測不到變更）
↓  
$user->save();                       // 因為 !isDirty() = true，直接 return false
↓
沒有執行 UPDATE SQL                   // 資料庫沒有更新

// ✅ AsArrayObject 的解決鏈
$user->options['theme'] = 'dark';    // 修改 ArrayObject
↓
`ArrayObject` 通知 Laravel 有變更      // 內建的通知機制
↓
$user->isDirty('options');           // true（Laravel 知道有變更）
↓
$user->save();                       // 通過 isDirty() 檢查，執行 UPDATE
↓
執行 UPDATE SQL                      // 資料庫成功更新

/**
 * 💡 總結
 * 
  關鍵就在於 isDirty() 的檢查！

 * - save() 一定會檢查 isDirty()
 * - 只有 isDirty() = true 才會執行 UPDATE
 * - AsArrayObject 讓 Laravel 能正確偵測陣列變更
 * - 一般 array 無法觸發 dirty 標記
 */
```

---

- `AsArrayObject` 讓 JSON 欄位可**安全修改 offset**：

```php
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

protected function casts(): array
{
    return [
        'options' => AsArrayObject::class,
        //           ^^^^^^^^^^^^^^^^^^^
        //           將 JSON 轉為 ArrayObject 實例
        //           修改 offset 會自動標記 dirty，save() 時會更新
        //
        //           vs 一般 'array'：修改 offset 不會觸發 dirty 追蹤
    ];
}

// 使用範例
$user = User::find(1);

// ✅ AsArrayObject：安全修改
$user->options['theme'] = 'dark';     // 自動標記為 dirty
$user->options['lang'] = 'zh-TW';     // 每次修改都會追蹤
unset($user->options['old_key']);     // 刪除也會追蹤
$user->save();                        // 會更新到資料庫

// ❌ 一般 array：不安全修改
// protected $casts = ['options' => 'array'];
// $user->options['theme'] = 'dark';  // 不會標記為 dirty
// $user->save();                     // 不會更新到資料庫！

// 檢查 dirty 狀態
$user->options['new'] = 'value';
$user->isDirty('options');            // true（AsArrayObject 會自動標記）
$user->getDirty();                    // ['options' => ArrayObject]
```

**重點說明**
- *dirty* = 屬性`已修改但未儲存`的標記，Laravel 用來追蹤變更
- *offset* = __陣列索引__（如 `['key']`），指`陣列中的特定位置`
- *安全修改* = `修改後`會正確觸發 dirty 追蹤，確保 save() 時更新到資料庫
- *問題根源*：一般 `array cast` 返回純 `PHP 陣列`，Laravel 無法偵測內部變更
- *解決方案*：`AsArrayObject` 實作了**變更通知機制**，自動告知 Laravel 有修改

**適用場景**
- 需要`頻繁修改 JSON 欄位`的部分內容
- 想要使用直觀的陣列語法操作
- `避免`每次都要整個替換 JSON 欄位

---

- `AsCollection` 轉為 `Laravel Collection`：
  - 讓 **JSON** 欄位 __自動轉成__ `Laravel Collection`
  - 可直接用 Collection 的**各種方法**操作資料
  - 適合處理陣列型態的 JSON 欄位，讓資料處理更方便

```php
use Illuminate\Database\Eloquent\Casts\AsCollection;

protected function casts(): array
{
    return [
        'options' => AsCollection::class,
        // 讀取時自動轉成 Collection
    ];
}

// 假設資料庫 options 欄位內容：
// [{"name":"A","active":true},{"name":"B","active":false}]

$user = User::find(1);

// Collection 方法舉例
$names = $user->options->pluck('name');         // 取得所有 name 欄位
$active = $user->options->where('active', true);// 篩選 active 為 true 的項目
$count = $user->options->count();               // 計算項目數量
$list = $user->options->map(fn($item) => $item['name']); // 對每個項目做處理

// 轉回陣列
$array = $user->options->toArray();
```

---

- **指定自訂** Collection 類別：

```php
use App\Collections\OptionCollection;
use Illuminate\Database\Eloquent\Casts\AsCollection;

protected function casts(): array
{
    return [
        'options' => AsCollection::using(OptionCollection::class),
        // 使用自訂 Collection 類別 OptionCollection
        // 讀取 options 欄位時，會自動轉成 OptionCollection 實例
        // 可在 OptionCollection 中擴充自訂方法，讓資料操作更方便
    ];
}

// 範例：假設 OptionCollection 有自訂方法 activeNames()
$user = User::find(1);
$names = $user->options->activeNames(); // 直接呼叫自訂方法處理資料
```

---

- 指定 collection 裡**每個元素**的物件型別：

```php
use App\ValueObjects\Option;
use Illuminate\Database\Eloquent\Casts\AsCollection;

protected function casts(): array
{
    return [
        'options' => AsCollection::of(Option::class),
        // 讀取 options 欄位時，會自動將每個項目轉成 Option 物件
        // 讓你可以直接用 Option 的方法或屬性操作每個元素
    ];
}

// 範例：假設 Option 有 isActive 屬性
$user = User::find(1);
$activeOptions = $user->options->filter(fn($option) => $option->isActive);
```

---

- **物件** 需實作 `Arrayable` 與 `JsonSerializable`：

```php
namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

// Option 物件用於表示一個選項，通常搭配 Laravel cast 機制
// 讓資料庫 JSON 欄位的每個元素都能自動轉成物件並安全序列化
class Option implements Arrayable, JsonSerializable
{
    public string $name;
    public mixed $value;
    public bool $isLocked;

    // 建構子：用陣列初始化物件屬性，方便從資料庫資料直接建立物件
    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->value = $data['value'];
        $this->isLocked = $data['is_locked'];
    }

    // toArray：回傳物件所有屬性，讓 Laravel 可以序列化這個物件
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'is_locked' => $this->isLocked,
        ];
    }

    // jsonSerialize：讓物件可以直接被 json_encode() 處理，回傳陣列格式
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

// 這樣設計可以讓你在程式中用物件方式操作 JSON 欄位資料，並且能安全儲存回資料庫
```

---

### 1.3.4 *日期轉型*（Date Casting）

- **預設** `created_at/updated_at` 會轉為 `Carbon 實例` 。

- 可**自訂**日期格式：

```php
// 自訂是指你可以指定日期格式，讓 created_at 轉成 Carbon 物件後，
// 在序列化（如 toArray() 或 toJson()）時，輸出你設定的格式。
protected function casts(): array
{
    return [
        'created_at' => 'datetime:Y-m-d',
    ];
}
```

---

- **全域** 自訂 __序列化格式__：

- 這個「**全域自訂`日期序列化`格式**」是針對 `指定 Eloquent` 模型的 _所有日期欄位_，只要在你的模型（通常是 Model 基底類別）裡加上 `serializeDate() `方法就可以了，不需要寫在 `provider。`

<!-- 這個設定只會影響你有加 serializeDate() 方法的那個模型，
     不會影響其他模型，
     每個模型可以有自己的日期序列化格式。 -->

```php
/**
 * 全域自訂序列化格式
 * 
 * 意思是：所有日期欄位（如 created_at, updated_at）在模型序列化（toArray/toJson）時，
 * 都會用你指定的格式顯示。
 * 
 * 寫法：在你的 Eloquent 模型（通常是 Model 基底類別）裡加上 serializeDate 方法。
 */

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class User extends Model
{
    // 全域自訂日期序列化格式
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
        // 所有日期欄位序列化時都會用這個格式
    }
}

// 這樣 $user->toArray() 或 $user->toJson() 時，所有日期欄位都會是 "2025-08-09" 這種格式
```

---

- **資料庫** 儲存格式：

```php
protected $dateFormat = 'U';
// 這樣日期欄位會以 UNIX timestamp（秒數）格式儲存到資料庫
// 例如：1691577600
```
- 建議*全程*使用 UTC。
- 時區統一用 UTC，可`避免跨地區時間混亂問題`

---

### 1.3.5 *Enum Casting*

- 直接將 *屬性* 轉型為 `PHP Enum`：

```php
use App\Enums\ServerStatus;

protected function casts(): array
{
    return [
        'status' => ServerStatus::class,
        // 讀取時自動轉成 ServerStatus Enum 實例
        // 設定時可直接用 Enum 物件或字串
    ];
}

// 範例
$user = User::find(1);
if ($user->status === ServerStatus::Active) {
    // 可以直接用 Enum 判斷狀態
}
```

---

- **陣列 Enum**：

```php
use App\Enums\ServerStatus;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;

protected function casts(): array
{
    return [
        'statuses' => AsEnumCollection::of(ServerStatus::class),
        // 讓 statuses 欄位自動轉成 ServerStatus Enum 的 Collection
        // 可直接用 Collection 方法操作多個 Enum
    ];
}

// 範例
$user = User::find(1);
foreach ($user->statuses as $status) {
    if ($status === ServerStatus::Active) {
        // 可直接判斷每個 Enum 狀態
    }
}
```

---

### 1.3.6 *Encrypted Casting*

- `encrypted`
  屬性加密儲存、解密讀取（字串型態）

- `encrypted:array`
  **陣列型態** 自動加密儲存、解密讀取

- `encrypted:collection`
  **Collection 型態** 自動加密儲存、解密讀取

- `encrypted:object`
  **物件型態** 自動加密儲存、解密讀取

- `AsEncryptedArrayObject`
  使用 **ArrayObject 型態** 自動加密儲存、解密讀取

- `AsEncryptedCollection`
  使用 **Collection 型態** 自動加密儲存、解密讀取

- 資料庫欄位需為 `TEXT` 或更大
  加密後資料長度增加，建議使用 TEXT 型態以上欄位

- `旋轉金鑰`需 **手動重加密**
  更換 _加密金鑰_ 時，需自行將舊資料解密後再重新加密儲存
---

### 1.3.7 *Query Time Casting*

- **查詢時臨時** 套用 cast：

```php
use App\Models\Post;
use App\Models\User;

$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
        ->whereColumn('user_id', 'users.id')
])
->withCasts([
    'last_posted_at' => 'datetime', // 臨時將 last_posted_at 欄位轉型為 Carbon 日期物件
])
->get();

// 說明：
// withCasts 可在查詢時臨時指定欄位型態，不需修改模型本身的 casts 設定
// 例如 last_posted_at 會自動轉成 Carbon 實例，方便後續日期操作
``` 

---

# 2. *Custom Casts*

## 2.1 **自訂 CastsAttributes**

- `Artisan 指`令建立 *cast class*：

```bash
php artisan make:cast AsJson
```

---

- 自訂 cast class 需實作 `CastsAttributes` 介面，必須有 `get` 與 `set` 方法。
- 範例：自訂 json cast：

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * AsJson Cast
 * 
 * 讓欄位自動在讀取時 json_decode，儲存時 json_encode。
 * 可用於自訂 cast，讓 Eloquent 屬性自動處理 JSON 格式。
 */
class AsJson implements CastsAttributes
{
    /**
     * Cast the given value.
     * 讀取資料庫時自動轉成 PHP 陣列
     */
    public function get(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): array {
        return json_decode($value, true);
    }

    /**
     * Prepare the given value for storage.
     * 儲存到資料庫前自動轉成 JSON 字串
     */
    public function set(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): string {
        return json_encode($value);
    }
}
```

---

- 設定於*模型*：

```php
namespace App\Models;

use App\Casts\AsJson;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected function casts(): array
    {
        return [
            'options' => AsJson::class,
        ];
    }
}
```

---

## 2.2 **Value Object Casting**

- 可將 `多個欄位` 組合成 `一個物件`，`set` 時回傳 __陣列__。
- 範例：Address value object cast：

```php
namespace App\Casts;

use App\ValueObjects\Address; // 假設 Address 類別放在這裡
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * AsAddress Cast
 * 
 * 讓 address 欄位自動在讀取時組成 Address 物件，儲存時拆成多個欄位。
 * 適合複合型態的資料欄位。
 */
class AsAddress implements CastsAttributes
{
    /**
     * Cast the given value.
     * 讀取資料庫時，將 address 欄位組成 Address 物件
     */
    public function get(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): Address {
        return new Address(
            $attributes['address_line_one'],
            $attributes['address_line_two']
        );
    }

    /**
     * Prepare the given value for storage.
     * 儲存到資料庫前，將 Address 物件拆成多個欄位
     */
    public function set(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): array {
        if (! $value instanceof Address) {
            throw new InvalidArgumentException('The given value is not an Address instance.');
        }

        return [
            'address_line_one' => $value->lineOne,
            'address_line_two' => $value->lineTwo,
        ];
    }
}
```

```php
namespace App\ValueObjects;

class Address
{
    public string $lineOne;
    public string $lineTwo;

    public function __construct(?string $lineOne, ?string $lineTwo)
    {
        $this->lineOne = $lineOne ?? '';
        $this->lineTwo = $lineTwo ?? '';
    }
}
```

---

- 存取 `value object` 時，內容會 *自動同步* 回 model，且會快取。
- 若要 *關閉快取*，在 `cast class` 宣告 public `withoutObjectCaching = true`：

```php
class AsAddress implements CastsAttributes
{
    public bool $withoutObjectCaching = true;
    // ...
}
```

---

- 若 `value object` 需序列化為 `array/JSON` ，建議實作 `Arrayable` 與 `JsonSerializable`。
- 若*無法修改第三方物件*，可讓 cast class 實作 `SerializesCastableAttributes`，自訂 __serialize 方法__：

```php
class AsAddress implements CastsAttributes, SerializesCastableAttributes
{
    // ... get() 和 set() 方法 ...

    /**
     * 自訂序列化方法
     */
    public function serialize(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): string {
        return (string) $value;
    }
}
// 這樣當模型序列化（如 toArray/toJson）時，
// Laravel 會呼叫你自訂的 serialize 方法。
```

---

## 2.3 **Inbound Only Casts**（僅入站）

- *僅處理* `set`，*不處理* `get`，需實作 `CastsInboundAttributes` 介面。

- Artisan 指令：

```bash
php artisan make:cast AsHash --inbound
```

---

- 範例：

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Model;

class AsHash implements CastsInboundAttributes
{
    public function __construct(
        protected string|null $algorithm = null,
    ) {}

    public function set(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): string {
        return is_null($this->algorithm)
            ? bcrypt($value)
            : hash($this->algorithm, $value);
    }
}
```

---

## 2.4 **Cast 參數**

- 可於 casts 設定時用 `:` 傳遞參數，會傳給 __cast class 的 constructor__：

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsHash;

class User extends Model
{
    protected function casts(): array
    {
        return [
            'secret' => AsHash::class . ':sha256',  // 這裡的 sha256 會傳給 AsHash 的建構子
            // 設定 secret 欄位自動以 sha256 演算法雜湊儲存
            // Eloquent 的 $casts 屬性是用來設定欄位型別轉型，不需要建構子。
            // 只要在模型裡設定 $casts 陣列即可。
        ];
    }
}

// 使用範例
$user = new User();
$user->secret = 'my-password';  // 設定時自動雜湊
$user->save();                  // 資料庫儲存的是 sha256 雜湊值

// 讀取時
echo $user->secret;             // 只會拿到雜湊值，無法還原原始密碼

// 比對範例
if (AsHash::check('my-password', $user->secret)) {
    // 密碼正確
}
```

---

## 2.5 **Cast 值比較**（ComparesCastableAttributes）

- 若需自訂值比較邏輯，實作 `ComparesCastableAttributes` 介面，實作 `compare` 方法：

```php
public function compare(
    Model $model,
    string $key,
    mixed $firstValue,
    mixed $secondValue
): bool {
    // 實際運作：用於 cast 類別判斷屬性值是否有變更
    // 例如 Eloquent 儲存前，會用 compare 來判斷新舊值是否不同
    // 如果回傳 false，Laravel 會認定有變更並標記 dirty
    return $firstValue === $secondValue;
}
```

---

## 2.6 **Castable 物件**

- 讓 __value object__ 自訂 cast class，需實作 `Castable` 介面，並實作 `castUsing` 方法：

```php
use App\ValueObjects\Address;

protected function casts(): array
{
    return [
        'address' => Address::class,
        // 讓 address 欄位自動轉成 Address 物件
        // 讀取時會自動建立 Address 實例，儲存時自動序列化
    ];
}
```

---

- `value object`：

```php
namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use App\Casts\AsAddress;

class Address implements Castable
{
    // 讓 Address 物件可用於 Eloquent casts
    // castUsing 回傳自訂 cast 類別（如 AsAddress）
    public static function castUsing(array $arguments): string
    {
        return AsAddress::class;
    }
}
```

---

- 可**傳遞參數**給 `castUsing：`

```php
protected function casts(): array
{
    return [
        'address' => Address::class . ':argument',
        // 這樣寫可以把 'argument' 傳給 Address::castUsing 方法
        // 讓自訂 cast 類別根據參數調整行為
        // 例如：可用不同格式、驗證規則、地區設定等
    ];
}

// 範例：Address value object
class Address implements \Illuminate\Contracts\Database\Eloquent\Castable
{
    public static function castUsing(array $arguments): string
    {
        // $arguments 會收到 ['argument']
        // 可根據參數決定要用哪個 cast 類別或怎麼處理
        return AsAddress::class;
    }
}
```

---

## 2.7 **Castable + 匿名類別**

- 可於 value object 的 `castUsing` __回傳匿名 class__，直接實作 `CastsAttributes`：

```php
// 可於 value object 的 castUsing 回傳匿名 class，直接實作 CastsAttributes：

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Address implements Castable
{
    public static function castUsing(array $arguments): CastsAttributes
    {
        // 直接回傳匿名 class，實作 CastsAttributes 介面
        return new class implements CastsAttributes
        {
            public function get(
                Model $model,
                string $key,
                mixed $value,
                array $attributes,
            ): Address {
                // 讀取時自動組成 Address 物件
                return new Address(
                    $attributes['address_line_one'],
                    $attributes['address_line_two']
                );
            }

            public function set(
                Model $model,
                string $key,
                mixed $value,
                array $attributes,
            ): array {
                // 儲存時自動拆成多個欄位
                return [
                    'address_line_one' => $value->lineOne,
                    'address_line_two' => $value->lineTwo,
                ];
            }
        };
    }
}

// 這種寫法可以讓 Address value object 直接在模型 cast 時用匿名 class 實作轉型邏輯，不需額外建立獨立 cast 類別。
```