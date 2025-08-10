*【重要說明】*

**Laravel 常見的「輔助功能」有兩種**：

1. *全域 Helper 函式*
   - 例如：array_get()、str_slug()、route()、view()、config() 等。
   - 這些函式在` Laravel 8 以前`預設全域可用，`無需 use/import`，任何 PHP 檔案都能直接呼叫。
   - `Laravel 8 之後`，部分舊 helper（如 array_get、str_slug）被棄用或移除，官方推薦用`靜態類別方法`取代。

2. *靜態類別方法（Arr、Str、Number 等）*
   - 例如：Arr::get()、Str::slug()、Number::format() 等。
   - 這些是類別的靜態方法，需在檔案上方` use Illuminate\Support\Arr;` 等，才能呼叫。
   - 功能與舊 helper 類似，但更現代、IDE 支援更好，Laravel 官方文件現多推薦這種寫法。

---

*【範例對照】*

**Laravel 7 以前**：

```php
$value = array_get($array, 'key');
$slug = str_slug('Hello World');
```

**Laravel 8 以後（推薦）**：

```php
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

$value = Arr::get($array, 'key');
$slug = Str::slug('Hello World');
```

---

*【結論】*
- 舊版 `helper` 函式（如 array_get）有些已棄用，建議改用 `Arr/Str/Number` 等類別方法。
- 你在閱讀官方文件時，看到要 `use Arr/Str/Number`，就是指這些`靜態方法`，不是全域 `helper。`
- 本筆記會分開整理「全域 helper」與「Arr/Str/Number」靜態方法，方便查閱。

---

# *Laravel Helpers 函式 筆記*

Laravel `helper 函式`（如 route()、view()、config() 等）預設全域可用，無需 `use 或 import`，任何 PHP 檔案都能直接呼叫。
若自訂 `helper`，需在` composer.json` 的 `autoload.files` 註冊或於 `bootstrap/app.php` `require`，才會全域有效。

---

## **陣列與物件（Arr:: / data_ / head / last）**

- *Arr::accessible*：判斷值`是否`可用陣列方式存取。
- *Arr::add*：若 key 不存在則`新增`。
- *Arr::array*：轉為`陣列`。
- *Arr::boolean*：轉為`布林值`。
- *Arr::collapse*：多維陣列`壓平成一維`。
- *Arr::crossJoin*：多陣列`笛卡兒積`。
- *Arr::divide*：`分割` key/value 為兩個陣列。
- *Arr::dot*：多維陣列轉 `dot notation`。
- *Arr::except*：`排除`指定 key。
- *Arr::exists*：判斷 key 是否`存在`。
- *Arr::first*：取得`第一個`符合條件的值。
- *Arr::flatten*：`壓平成一維`。
- *Arr::float*：轉為`浮點數`。
- *Arr::forget*：`移除`指定 key。
- *Arr::from*：建立`新陣列`。
- *Arr::get*：`取得`指定 key 的值。
- *Arr::has*：`判斷`是否有指定 key。
- *Arr::hasAll*：是否全部 key 都存在。
- *Arr::hasAny*：是否有任一 key 存在。
- *Arr::integer*：轉為`整數`。
- *Arr::isAssoc*：是否為`關聯陣列`。
- *Arr::isList*：是否為`索引陣列`。
- *Arr::join*：`合併`為字串。
- *Arr::keyBy*：依指定 key `重新索引`。
- *Arr::last*：取得`最後一個`符合條件的值。
- *Arr::map*：遍歷並轉換每個值。
- *Arr::mapSpread*：`展開`傳入 callback。
- *Arr::mapWithKeys*：遍歷並指定 key。
- *Arr::only*：`只保留`指定 key。
- *Arr::partition*：`依條件分割`為兩組。
- *Arr::pluck*：取出指定 key 的值。
- *Arr::prepend*：`前面插入`一個值。
- *Arr::prependKeysWith*：所有 key `加上前綴`。
- *Arr::pull*：`取出並移除`指定 key。
- *Arr::query*：轉為`查詢字串`。
- *Arr::random*：`隨機`取出一個或多個值。
- *Arr::reject*：`排除`不符合條件。
- *Arr::select*：`只保留`符合條件。
- *Arr::set*：`設定`指定 key 的值。
- *Arr::shuffle*：`隨機排序`。
- *Arr::sole*：`唯一符合`條件的值。
- *Arr::sort*：`排序`。
- *Arr::sortDesc*：`反向`排序。
- *Arr::sortRecursive*：`遞迴`排序。
- *Arr::string*：轉為`字串`。
- *Arr::take*：`取前 n 個`。
- *Arr::toCssClasses*：轉為 CSS `class` 字串。
- *Arr::toCssStyles*：轉為 CSS `style` 字串。
- *Arr::undot*：dot notation `轉回多維`。
- *Arr::where*：條件過濾。
- *Arr::whereNotNull*：過濾非 null。
- *Arr::wrap*：`包裝`成陣列。
- *data_fill*：`填充`陣列/物件指定 key。
- *data_get*：`取`巢狀 key。
- *data_set*：`設定`巢狀 key。
- *data_forget*：`移除`巢狀 key。
- *head*：取得陣列`第一個值`。
- *last*：取得陣列`最後一個值`。

---

### *Arr::accessible()*

判斷值**是否**可用陣列方式存取。

```php
use Illuminate\Support\Arr; // 引入 Laravel 的 Arr 輔助類別
use Illuminate\Support\Collection; // 引入 Laravel 的 Collection 類別

Arr::accessible(['a' => 1, 'b' => 2]); // true，因為陣列本身可以用陣列方式存取（$arr['a']）
Arr::accessible(new Collection); // true，Collection 物件支援陣列式存取（實作 ArrayAccess 介面）
Arr::accessible('abc'); // false，字串不是陣列，也不支援陣列式存取
// stdClass 是 PHP 內建的「標準類別」（standard class，stdClass），代表最基本、最簡單的空物件，常用來臨時存資料或陣列轉物件時產生。
Arr::accessible(new stdClass); // false，stdClass 是 PHP 內建的空物件，不能用陣列方式存取（只能用 -> 屬性存取）
```

---

### *Arr::add()*

`Arr::add($array, $key, $value)`;

若 key 不存在或為 null，則新增 key/value。

```php
use Illuminate\Support\Arr;
Arr::add(['name' => 'Desk'], 'price', 100); // ['name' => 'Desk', 'price' => 100]
Arr::add(['name' => 'Desk', 'price' => null], 'price', 100); // ['name' => 'Desk', 'price' => 100]
```

---

### *Arr::array()*

`Arr::array($array, $key)`;

取出**巢狀陣列**（如不是陣列會丟出例外）。

```php
use Illuminate\Support\Arr;
$array = ['name' => 'Joe', 'languages' => ['PHP', 'Ruby']];
Arr::array($array, 'languages'); // ['PHP', 'Ruby']
Arr::array($array, 'name'); // throws InvalidArgumentException
```

---

### *Arr::boolean()*

`Arr::boolean($array, $key)`;

取出**巢狀布林值**（如不是布林會丟出例外）。

```php
use Illuminate\Support\Arr;
$array = ['name' => 'Joe', 'available' => true];
Arr::boolean($array, 'available'); // true
Arr::boolean($array, 'name'); // throws InvalidArgumentException
```

---

### *Arr::collapse()*

`Arr::collapse($array)`;

只**壓平`最外層`子陣列**，內層巢狀結構不會被遞迴壓平。

```php
use Illuminate\Support\Arr;
Arr::collapse([[1, 2, 3], [4, 5, 6], [7, 8, 9]]); // [1, 2, 3, 4, 5, 6, 7, 8, 9]

Arr::collapse([[1, 2], [3, [4, 5]]]); // [1, 2, 3, [4, 5]]
// 只壓平最外層，內層還是巢狀陣列

Arr::flatten([[1, 2], [3, [4, 5]]]); // [1, 2, 3, 4, 5]
// 會遞迴壓平所有層級，最終完全沒有巢狀
```

---

### *Arr::crossJoin()*

`Arr::crossJoin(...$arrays)`;

多陣列**笛卡兒積**。

```php
use Illuminate\Support\Arr;
Arr::crossJoin([1, 2], ['a', 'b']);
// [ [1, 'a'], [1, 'b'], [2, 'a'], [2, 'b'] ]
Arr::crossJoin([1, 2], ['a', 'b'], ['I', 'II']);
// [ [1, 'a', 'I'], [1, 'a', 'II'], ... ]
```

---

### *Arr::divide()*

`Arr::divide($array)`;

**分割** key/value 為**兩個陣列**。

```php
use Illuminate\Support\Arr;
[$keys, $values] = Arr::divide(['name' => 'Desk']);
// $keys: ['name']
// $values: ['Desk']
```

---

### *Arr::dot()*

`Arr::dot($array, $prepend = '')`;

多維陣列轉 **dot notation**。

```php
use Illuminate\Support\Arr;
$array = ['products' => ['desk' => ['price' => 100]]];
Arr::dot($array); // ['products.desk.price' => 100]
```

---

### *Arr::except()*

`Arr::except($array, $keys)`;

**排除** 指定 key。

```php
use Illuminate\Support\Arr;
$array = ['name' => 'Desk', 'price' => 100];
Arr::except($array, ['price']); // ['name' => 'Desk']
```

---

### *Arr::exists()*

`Arr::exists($array, $key)`;

判斷 key **是否存在**。

```php
use Illuminate\Support\Arr;
$array = ['name' => 'John Doe', 'age' => 17];
Arr::exists($array, 'name'); // true
Arr::exists($array, 'salary'); // false
```

---

### *Arr::first()*

`Arr::first($array, $callback = null, $default = null)`;

取得**第一個符合條件**的值。

```php
use Illuminate\Support\Arr;
$array = [100, 200, 300];
Arr::first($array, function (int $value, int $key) {
    return $value >= 150;
}); // 200
// 可傳預設值：
Arr::first($array, $callback, $default);
```

---

### *Arr::flatten()*

`Arr::flatten($array, $depth = INF`);

會**遞迴壓平所有層級**，最終完全沒有巢狀。

```php
use Illuminate\Support\Arr;
$array = ['name' => 'Joe', 'languages' => ['PHP', 'Ruby']];
Arr::flatten($array); // ['Joe', 'PHP', 'Ruby']
```

---

### *Arr::float()*

取出**巢狀浮點數**（如不是 float 會丟出例外）。

```php
use Illuminate\Support\Arr;
$array = ['name' => 'Joe', 'balance' => 123.45];
Arr::float($array, 'balance'); // 123.45
Arr::float($array, 'name'); // throws InvalidArgumentException
```

---

### *Arr::forget()*

**移除**巢狀 key。

```php
use Illuminate\Support\Arr;
$array = ['products' => ['desk' => ['price' => 100]]];
Arr::forget($array, 'products.desk');
// ['products' => []]
```

---

### *Arr::from()*

`Arr::from($value)`;

將各種型別轉為**純陣列**。

```php
use Illuminate\Support\Arr; // 引入 Arr 輔助類別
Arr::from((object) ['foo' => 'bar']); // ['foo' => 'bar']，將物件轉成陣列
// 支援 Arrayable（如 Collection）、Enumerable、Jsonable、JsonSerializable、Traversable、WeakMap 等型別
// 不管你給的是物件、集合、可遍歷物件、JSON 物件、WeakMap，只要能轉成陣列都會轉成標準 PHP 陣列

// Collection 物件（Arrayable）
use Illuminate\Support\Collection;
$collection = collect(['a' => 1, 'b' => 2]);
Arr::from($collection); // ['a' => 1, 'b' => 2]

// JsonSerializable 物件
class Foo implements JsonSerializable {
    public function jsonSerialize() {
        return ['bar' => 123];
    }
}
$foo = new Foo();
Arr::from($foo); // ['bar' => 123]

// Traversable（可遍歷物件）
$iterator = new ArrayIterator(['x' => 9, 'y' => 8]);
Arr::from($iterator); // ['x' => 9, 'y' => 8]

// WeakMap（PHP 8+）
if (class_exists('WeakMap')) {
    $wm = new WeakMap();
    $obj = new stdClass();
    $wm[$obj] = 'hello';
    Arr::from($wm); // [$obj => 'hello']
}

// Jsonable 物件
use Illuminate\Contracts\Support\Jsonable;
class Bar implements Jsonable {
    public function toJson($options = 0) {
        return json_encode(['baz' => 456]);
    }
}
$bar = new Bar();
Arr::from($bar); // ['baz' => 456]

// 一般陣列、標量
Arr::from(['foo' => 'bar']); // ['foo' => 'bar']
Arr::from(123); // [123]
Arr::from('abc'); // ['abc']
```

---

### *Arr::get()*

`Arr::get($array, $key, $default = null)`;

取得巢狀 key。

```php
use Illuminate\Support\Arr;
$array = ['products' => ['desk' => ['price' => 100]]];
Arr::get($array, 'products.desk.price'); // 100
Arr::get($array, 'products.desk.discount', 0); // 0 是預設值，當 'products.desk.discount' 這個 key 不存在時會回傳 0，避免出現錯誤或 undefined
```

---

### *Arr::has()*

`Arr::has($array, $key)`;

判斷**是否**有指定 key（支援 dot notation）。

```php
use Illuminate\Support\Arr;
$array = ['product' => ['name' => 'Desk', 'price' => 100]];
Arr::has($array, 'product.name'); // true
Arr::has($array, ['product.price', 'product.discount']); // false
```

---

### *Arr::hasAll()*

`Arr::hasAll($array, $keys)`;

**是否全部** key 都存在（支援 dot notation）。

```php
use Illuminate\Support\Arr;
$array = ['name' => 'Taylor', 'language' => 'PHP'];
Arr::hasAll($array, ['name']); // true
Arr::hasAll($array, ['name', 'language']); // true
Arr::hasAll($array, ['name', 'IDE']); // false
```

---

### *Arr::hasAny()*

`Arr::hasAny($array, $keys)`;

**是否有任一** key 存在（支援 dot notation）。

```php
use Illuminate\Support\Arr;
$array = ['product' => ['name' => 'Desk', 'price' => 100]];
Arr::hasAny($array, 'product.name'); // true
Arr::hasAny($array, ['product.name', 'product.discount']); // true
Arr::hasAny($array, ['category', 'product.discount']); // false
```

---

### *Arr::integer()*

`Arr::integer($array, $key)`;

取出**巢狀整數**（如不是 int 會丟出例外）。

```php
use Illuminate\Support\Arr;
$array = ['name' => 'Joe', 'age' => 42];
Arr::integer($array, 'age'); // 42
Arr::integer($array, 'name'); // throws InvalidArgumentException
```

---

### *Arr::isAssoc()*

`Arr::isAssoc($array)`;

**是否**為**關聯陣列**。

```php
use Illuminate\Support\Arr;
Arr::isAssoc(['product' => ['name' => 'Desk', 'price' => 100]]); // true
Arr::isAssoc([1, 2, 3]); // false
```

---

### *Arr::isList()*

`Arr::isList($array)`;

**是否**為**索引陣列**。

```php
use Illuminate\Support\Arr;
Arr::isList(['foo', 'bar', 'baz']); // true
Arr::isList(['product' => ['name' => 'Desk', 'price' => 100]]); // false
```

---

### *Arr::join()*

`Arr::join($array, $glue, $finalGlue = null)`;

**合併為字串**，可自訂最後一個元素的連接字串。

```php
use Illuminate\Support\Arr;
$array = ['Tailwind', 'Alpine', 'Laravel', 'Livewire'];
Arr::join($array, ', '); // Tailwind, Alpine, Laravel, Livewire
Arr::join($array, ', ', ' and '); // Tailwind, Alpine, Laravel and Livewire
```

---

### *Arr::keyBy()*

`Arr::keyBy($array, $key)`;

依指定 key **重新索引**。

```php
use Illuminate\Support\Arr;
$array = [
    ['product_id' => 'prod-100', 'name' => 'Desk'],
    ['product_id' => 'prod-200', 'name' => 'Chair'],
];
Arr::keyBy($array, 'product_id');
// [ 'prod-100' => [...], 'prod-200' => [...] ]
```

---

### *Arr::last()*

`Arr::last($array, $callback = null, $default = null)`;

取得**最後一個符合**條件的值。

```php
use Illuminate\Support\Arr;
$array = [100, 200, 300, 110];
Arr::last($array, function (int $value, int $key) {
    return $value >= 150;
}); // 300
// 可傳預設值：
Arr::last($array, $callback, $default);
```

---

### *Arr::map()*

`Arr::map($array, $callback)`;

遍歷並轉換每個值。

```php
use Illuminate\Support\Arr;
$array = ['first' => 'james', 'last' => 'kirk'];
Arr::map($array, function (string $value, string $key) {
    return ucfirst($value);
}); // ['first' => 'James', 'last' => 'Kirk']
```

---

### *Arr::mapSpread()*

`Arr::mapSpread($array, $callback)`;

**展開**每個子陣列傳入 callback。

```php
use Illuminate\Support\Arr;
$array = [ [0, 1], [2, 3], [4, 5], [6, 7], [8, 9] ];
Arr::mapSpread($array, function (int $even, int $odd) {
    return $even + $odd;
}); // [1, 5, 9, 13, 17]
```

---

### *Arr::mapWithKeys()*

`Arr::mapWithKeys($array, $callback)`;

遍歷並指定 key。

```php
use Illuminate\Support\Arr;
$array = [
    [ 'name' => 'John', 'department' => 'Sales', 'email' => 'john@example.com' ],
    [ 'name' => 'Jane', 'department' => 'Marketing', 'email' => 'jane@example.com' ]
];
Arr::mapWithKeys($array, function (array $item, int $key) {
    return [$item['email'] => $item['name']];
});
// [ 'john@example.com' => 'John', 'jane@example.com' => 'Jane' ]
```

---

### *Arr::only()*

`Arr::only($array, $keys)`;

**只保留**指定 key。

```php
use Illuminate\Support\Arr;
$array = ['name' => 'Desk', 'price' => 100, 'orders' => 10];
$slice = Arr::only($array, ['name', 'price']);
// ['name' => 'Desk', 'price' => 100]
```

---

### *Arr::partition()*

`Arr::partition($array, $callback)`;

依條件**分割**為兩組，可用**陣列解構**。

```php
use Illuminate\Support\Arr;
$numbers = [1, 2, 3, 4, 5, 6];
[$underThree, $equalOrAboveThree] = Arr::partition($numbers, function (int $i) {
    return $i < 3;
});
dump($underThree); // [1, 2]
dump($equalOrAboveThree); // [3, 4, 5, 6]
```

---

### *Arr::pluck()*

`Arr::pluck($array, $value, $key = null)`;

取出指定 key 的所有值，可指定新 key。

```php
use Illuminate\Support\Arr;
$array = [
    ['developer' => ['id' => 1, 'name' => 'Taylor']],
    ['developer' => ['id' => 2, 'name' => 'Abigail']],
];
$names = Arr::pluck($array, 'developer.name');
// ['Taylor', 'Abigail']
$names = Arr::pluck($array, 'developer.name', 'developer.id'); // 取出每筆的 developer.name 當 value，developer.id 當 key，結果為 [1 => 'Taylor', 2 => 'Abigail']
// 例如 $array = [['developer' => ['id' => 1, 'name' => 'Taylor']], ['developer' => ['id' => 2, 'name' => 'Abigail']]]
// 會組成 [1 => 'Taylor', 2 => 'Abigail']
// 注意：pluck 的參數順序是 value, key（不是 key, value），這是為了和 PHP 原生 array_column() 一致，方便記憶與使用
```

---

### *Arr::prepend()*

`Arr::prepend($array, $value, $key = null)`;

在**陣列開頭**插入一個值，可指定 key。

```php
use Illuminate\Support\Arr;
$array = ['one', 'two', 'three', 'four'];
$array = Arr::prepend($array, 'zero');
// ['zero', 'one', 'two', 'three', 'four']
$array = ['price' => 100];
$array = Arr::prepend($array, 'Desk', 'name');
// ['name' => 'Desk', 'price' => 100]
```

---

### *Arr::prependKeysWith()*

`Arr::prependKeysWith($array, $prefix)`;

所有 **key** 加上**前綴**。

```php
use Illuminate\Support\Arr;
$array = [ 'name' => 'Desk', 'price' => 100 ];
$keyed = Arr::prependKeysWith($array, 'product.');
// [ 'product.name' => 'Desk', 'product.price' => 100 ]
```

---

### *Arr::pull()*

`Arr::pull($array, $key, $default = null)`;

取出並**移除指定 key**。

```php
use Illuminate\Support\Arr;
$array = ['name' => 'Desk', 'price' => 100];
$name = Arr::pull($array, 'name');
// $name: Desk
// $array: ['price' => 100]
// 可傳預設值：
$value = Arr::pull($array, $key, $default);
```

---

### *Arr::query()*

`Arr::query($array)`;

轉為**查詢字串**。

```php
use Illuminate\Support\Arr;
$array = [
    'name' => 'Taylor',
    'order' => [ 'column' => 'created_at', 'direction' => 'desc' ]
];
Arr::query($array);
// name=Taylor&order[column]=created_at&order[direction]=desc
```

---

### *Arr::random()*

`Arr::random($array, $number = null, $preserveKeys = false)`;

**隨機取出**一個或多個值。

```php
use Illuminate\Support\Arr;
$array = [1, 2, 3, 4, 5];
$random = Arr::random($array); // 4（隨機）
$items = Arr::random($array, 2); // [2, 5]（隨機）
```

---

### *Arr::reject()*

`Arr::reject($array, $callback)`;

**排除**不符合條件。

```php
use Illuminate\Support\Arr;
$array = [100, '200', 300, '400', 500];
$filtered = Arr::reject($array, function (string|int $value, int $key) {
    return is_string($value);
});
// [0 => 100, 2 => 300, 4 => 500]
```

---

### *Arr::select()*

`Arr::select($array, $keys)`;

**只保留**符合條件。

```php
use Illuminate\Support\Arr;
$array = [
    ['id' => 1, 'name' => 'Desk', 'price' => 200],
    ['id' => 2, 'name' => 'Table', 'price' => 150],
    ['id' => 3, 'name' => 'Chair', 'price' => 300],
];
Arr::select($array, ['name', 'price']);
// [['name' => 'Desk', 'price' => 200], 
// ['name' => 'Table', 'price' => 150], 
// ['name' => 'Chair', 'price' => 300]]
```

---

### *Arr::set()*

`Arr::set($array, $key, $value)`;

**設定** 巢狀 key。

```php
use Illuminate\Support\Arr;
$array = ['products' => ['desk' => ['price' => 100]]];
Arr::set($array, 'products.desk.price', 200);
// ['products' => ['desk' => ['price' => 200]]]
```

---

### *Arr::shuffle()*

`Arr::shuffle($array, $seed = null)`;

**隨機排序**。

```php
use Illuminate\Support\Arr;
$array = Arr::shuffle([1, 2, 3, 4, 5]);
// [3, 2, 5, 1, 4]（隨機）
```

---

### *Arr::sole()*

`Arr::sole($array, $callback = null)`;

**唯一符合**條件的值，否則拋例外。

```php
use Illuminate\Support\Arr;
$array = ['Desk', 'Table', 'Chair'];
$value = Arr::sole($array, fn (string $value) => $value === 'Desk');
// 'Desk'
```

---

### *Arr::sort()*

`Arr::sort($array, $callback = null)`;

**排序**，可自訂 callback。

```php
use Illuminate\Support\Arr;
$array = ['Desk', 'Table', 'Chair'];
$sorted = Arr::sort($array); // ['Chair', 'Desk', 'Table']
$array = [ ['name' => 'Desk'], ['name' => 'Table'], ['name' => 'Chair'] ];
$sorted = array_values(Arr::sort($array, function (array $value) {
    return $value['name'];
}));
// array_values()重新索引排序後的陣列，確保索引是連續的數字（從 0 開始）。
// Laravel 的 Arr::sort 不會重置索引，因此需要用 array_values 來處理。
// [ ['name' => 'Chair'], ['name' => 'Desk'], ['name' => 'Table'] ]
```

---

### *Arr::sortDesc()*

`Arr::sortDesc($array, $callback = null)`;

**反向排序**，可自訂 callback。

```php
use Illuminate\Support\Arr;
$array = ['Desk', 'Table', 'Chair'];
$sorted = Arr::sortDesc($array); // ['Table', 'Desk', 'Chair']
$array = [ ['name' => 'Desk'], ['name' => 'Table'], ['name' => 'Chair'] ];
$sorted = array_values(Arr::sortDesc($array, function (array $value) {
    return $value['name'];
}));
// [ ['name' => 'Table'], ['name' => 'Desk'], ['name' => 'Chair'] ]
```

---

### *Arr::sortRecursive()*

`Arr::sortRecursive($array)`;

**遞迴排序**，數字索引用 `sort`，關聯陣列用 `ksort`。

```php
use Illuminate\Support\Arr;
$array = [
    ['Roman', 'Taylor', 'Li'],
    ['PHP', 'Ruby', 'JavaScript'],
    ['one' => 1, 'two' => 2, 'three' => 3],
];
$sorted = Arr::sortRecursive($array);
// [ ['JavaScript', 'PHP', 'Ruby'], ['one' => 1, 'three' => 3, 'two' => 2], ['Li', 'Roman', 'Taylor'] ]
// 反向排序：
$sorted = Arr::sortRecursiveDesc($array);
```

---

### *Arr::string()*

`Arr::string($array, $key)`;

取出**巢狀字串**（如不是 string 會丟出例外）。

```php
use Illuminate\Support\Arr;
$array = ['name' => 'Joe', 'languages' => ['PHP', 'Ruby']];
$value = Arr::string($array, 'name'); // 'Joe'
$value = Arr::string($array, 'languages'); // throws InvalidArgumentException
```

---

### *Arr::take()*

`Arr::take($array, $limit)`;

**取前 n 個**（或後 n 個，若為負數）。

```php
use Illuminate\Support\Arr;
$array = [0, 1, 2, 3, 4, 5];
$chunk = Arr::take($array, 3); // [0, 1, 2]
$chunk = Arr::take($array, -2); // [4, 5]
```

---

### *Arr::toCssClasses()*

`Arr::toCssClasses($array)`;

條件式產生 **CSS class** 字串。

```php
use Illuminate\Support\Arr;
$isActive = false;
$hasError = true;
$array = ['p-4', 'font-bold' => $isActive, 'bg-red' => $hasError];
$classes = Arr::toCssClasses($array); // 'p-4 bg-red'
```

---

### *Arr::toCssStyles()*

`Arr::toCssStyles($array)`;

條件式產生 **CSS style** 字串。

```php
use Illuminate\Support\Arr;
$hasColor = true;
$array = ['background-color: blue', 'color: blue' => $hasColor];
$classes = Arr::toCssStyles($array); // 'background-color: blue; color: blue;'
```

---

### *Arr::undot()*

`Arr::undot($array)`;

dot notation 轉回**多維**。

```php
use Illuminate\Support\Arr;
$array = [ 'user.name' => 'Kevin Malone', 'user.occupation' => 'Accountant' ];
$array = Arr::undot($array);
// ['user' => ['name' => 'Kevin Malone', 'occupation' => 'Accountant']]
```

---

### *Arr::where()*

`Arr::where($array, $callback)`;

條件過濾。

```php
use Illuminate\Support\Arr;
$array = [100, '200', 300, '400', 500];
$filtered = Arr::where($array, function (string|int $value, int $key) {
    return is_string($value);
});
// [1 => '200', 3 => '400']
```

---

### *Arr::whereNotNull()*

`Arr::whereNotNull($array)`;

移除陣列中所有 **null** 值。

```php
use Illuminate\Support\Arr;
$array = [0, null];
$filtered = Arr::whereNotNull($array);
// [0 => 0]
```

---

### *Arr::wrap()*

`Arr::wrap($value)`;

將值**包裝成陣列**，若已是陣列則原樣回傳，若為 null 則回傳空陣列。

```php
use Illuminate\Support\Arr;
$string = 'Laravel';
$array = Arr::wrap($string); // ['Laravel']
$array = Arr::wrap(null); // []
```

---

### *data_fill()*

`data_fill($target, $key, $value)`;

使用點記法（dot notation）為**巢狀陣列或物件**中的指定鍵設定預設值。
如果**鍵已存在，則保留原值**；如果鍵不存在，則填入指定的預設值。支援萬用字元 * 用於批量操作多層結構。

```php
$data = ['products' => ['desk' => ['price' => 100]]];
data_fill($data, 'products.desk.price', 200);
// ['products' => ['desk' => ['price' => 100]]]
data_fill($data, 'products.desk.discount', 10);
// ['products' => ['desk' => ['price' => 100, 'discount' => 10]]]
$data = [
    'products' => [
        ['name' => 'Desk 1', 'price' => 100],
        ['name' => 'Desk 2'],
    ],
];
data_fill($data, 'products.*.price', 200);
// ['products' => [ ['name' => 'Desk 1', 'price' => 100], ['name' => 'Desk 2', 'price' => 200] ]]
```

---

### *data_get()*

`data_get($target, $key, $default = null)`;

取**巢狀陣列/物件**的值（dot notation），支援萬用字元 * 及 `{first}、{last}`。

```php
$data = ['products' => ['desk' => ['price' => 100]]];
$price = data_get($data, 'products.desk.price'); // 100
$discount = data_get($data, 'products.desk.discount', 0); // 0
$data = [
    'product-one' => ['name' => 'Desk 1', 'price' => 100],
    'product-two' => ['name' => 'Desk 2', 'price' => 150],
];
data_get($data, '*.name'); // ['Desk 1', 'Desk 2']
$flight = [
    'segments' => [
        ['from' => 'LHR', 'departure' => '9:00', 'to' => 'IST', 'arrival' => '15:00'],
        ['from' => 'IST', 'departure' => '16:00', 'to' => 'PKX', 'arrival' => '20:00'],
    ],
];
data_get($flight, 'segments.{first}.arrival'); // 15:00
```

---

### *data_set()*

`data_set($target, $key, $value, $overwrite = true)`;

設定巢狀陣列/物件的值（dot notation），支援萬用字元 *，可選擇**是否**覆蓋。

```php
$data = ['products' => ['desk' => ['price' => 100]]];
data_set($data, 'products.desk.price', 200);
// ['products' => ['desk' => ['price' => 200]]]
$data = [
    'products' => [
        ['name' => 'Desk 1', 'price' => 100],
        ['name' => 'Desk 2', 'price' => 150],
    ],
];
data_set($data, 'products.*.price', 200);
// ['products' => [ ['name' => 'Desk 1', 'price' => 200], ['name' => 'Desk 2', 'price' => 200] ]]
data_set($data, 'products.desk.price', 200, overwrite: false);
// ['products' => ['desk' => ['price' => 100]]]
```

---

### *data_forget()*

`data_forget($target, $key)`;

移除**巢狀陣列/物件**的值（dot notation），支援萬用字元 *。

```php
$data = ['products' => ['desk' => ['price' => 100]]];
data_forget($data, 'products.desk.price');
// ['products' => ['desk' => []]]
$data = [
    'products' => [
        ['name' => 'Desk 1', 'price' => 100],
        ['name' => 'Desk 2', 'price' => 150],
    ],
];
data_forget($data, 'products.*.price');
// ['products' => [ ['name' => 'Desk 1'], ['name' => 'Desk 2'] ]]
```

---

### *head()*

`head($array)`;

取得陣列**第一個值**。

```php
$array = [100, 200, 300];
$first = head($array); // 100
```

---

### *last()*

`last($array)`;

取得陣列**最後一個值**。

```php
$array = [100, 200, 300];
$last = last($array); // 300
```

---

## **數字（Number::）**
- *Number::abbreviate*：`縮寫`數字（如 1.2K）。
- *Number::clamp*：`限制`數值範圍。
- *Number::currency*：格式化為`貨幣`。
- *Number::defaultCurrency / defaultLocale*：設定預設`貨幣/地區`。
- *Number::fileSize*：格式化`檔案大小`。
- *Number::forHumans*：`人類可讀格式`。
- *Number::format*：格式化`數字`。
- *Number::ordinal*：`序數詞`（1st, 2nd...）。
- *Number::pairs*：`分組`為 key/value。
- *Number::parseInt / parseFloat*：`字串`轉`數字`。
- *Number::percentage*：`百分比`格式。
- *Number::spell / spellOrdinal*：`數字`轉`英文拼字`。
- *Number::trim*：`去除多餘 0`。
- *Number::useLocale / withLocale*：`指定地區`。
- *Number::useCurrency / withCurrency*：`指定貨幣`。
---

### *Number::abbreviate()*

`Number::abbreviate($number, $precision = 1)`;

將數字**縮寫**成人類可讀格式。

```php
use Illuminate\Support\Number;
Number::abbreviate(1000); // 1K
Number::abbreviate(489939); // 490K
Number::abbreviate(1230000, precision: 2); // 1.23M
```

---

### *Number::clamp()*

`Number::clamp($number, $min, $max)`;

**限制**數值在指定範圍內。

```php
use Illuminate\Support\Number;
Number::clamp(105, min: 10, max: 100); // 100
Number::clamp(5, min: 10, max: 100); // 10
Number::clamp(10, min: 10, max: 100); // 10
Number::clamp(20, min: 10, max: 100); // 20
```

---

### *Number::currency()*

`Number::currency($number, $in = null, $locale = null, $precision = 2)`;

格式化為**貨幣字串**。

**參數解釋**
*$number*：

必填參數。
`要格式化的數字`（通常是金額）。
例如：1234.56。

*$in*：

選填參數。
指定`貨幣的代碼`（例如 USD、EUR、JPY 等）。
如果未提供，可能會使用系統的預設貨幣。
例如：'USD' 表示美元，'EUR' 表示歐元。

*$locale*：

選填參數。
`指定地區語言代碼，用於格式化數字的樣式`（例如千分位分隔符、小數點符號等）。
如果未提供，可能會使用系統的預設地區。
例如：
'en_US'：美國英文格式（1,234.56）。
'fr_FR'：法國格式（1 234,56）。

*$precision*：

*選填參數*。
指定`小數點後的位數`（預設為 2）。
例如：
2：顯示兩位小數（1234.56）。
0：不顯示小數（1234）。

```php
use Illuminate\Support\Number;
Number::currency(1000); // $1,000.00
Number::currency(1000, in: 'EUR'); // €1,000.00
Number::currency(1000, in: 'EUR', locale: 'de'); // 1.000,00 €
Number::currency(1000, in: 'EUR', locale: 'de', precision: 0); // 1.000 €
```

---

### *Number::defaultCurrency()*

`Number::defaultCurrency()`;

取得**預設貨幣**。

```php
use Illuminate\Support\Number;
Number::defaultCurrency(); // USD
```

---

### *Number::defaultLocale()*

`Number::defaultLocale()`;

取得**預設地區**。

```php
use Illuminate\Support\Number;
Number::defaultLocale(); // en
```

---

### *Number::fileSize()*

`Number::fileSize($bytes, $precision = 1)`;

格式化**檔案大小**。

```php
use Illuminate\Support\Number;
Number::fileSize(1024); // 1 KB
Number::fileSize(1024 * 1024); // 1 MB
Number::fileSize(1024, precision: 2); // 1.00 KB
```

---

### *Number::forHumans()*

`Number::forHumans($number, $precision = 1)`;

**人類可讀格式**。

```php
use Illuminate\Support\Number;
Number::forHumans(1000); // 1 thousand
Number::forHumans(489939); // 490 thousand
Number::forHumans(1230000, precision: 2); // 1.23 million
```

---

### *Number::format()*

`Number::format($number, $precision = 0, $maxPrecision = null, $locale = null)`;

格式化**數字，支援地區、精度**。

```php
use Illuminate\Support\Number;
Number::format(100000); // 100,000
Number::format(100000, precision: 2); // 100,000.00
Number::format(100000.123, maxPrecision: 2); // 100,000.12
Number::format(100000, locale: 'de'); // 100.000
```

---

### *Number::ordinal()*

`Number::ordinal($number)`;

取得**序數詞**。

`ordinal`（序數、序數詞）指的是表示「**順序**」的詞或數字。

```php
use Illuminate\Support\Number;
Number::ordinal(1); // 1st
Number::ordinal(2); // 2nd
Number::ordinal(21); // 21st
```

---

### *Number::pairs()*

`Number::pairs($max, $step, $offset = null)`;

依**範圍與步長**產生數字區間陣列。

```php
use Illuminate\Support\Number;
Number::pairs(25, 10); // [[0, 9], [10, 19], [20, 25]]
// 0~9、10~19、20~25 各是一組區間，最後一組不滿 10 也會補到最大值
Number::pairs(25, 10, offset: 0); // [[0, 10], [10, 20], [20, 25]]
// 0~10、10~20、20~25 各是一組區間，offset:0 代表每組結束值是「起始值+步長」
```

---

### *Number::parseInt()*

`Number::parseInt($value, $locale = null)`;

字串轉**整數**，**支援地區**。

```php
use Illuminate\Support\Number;
Number::parseInt('10.123'); // (int) 10
Number::parseInt('10,123', locale: 'fr'); // (int) 10
```

---

### *Number::parseFloat()*

`Number::parseFloat($value, $locale = null)`;

字串轉**浮點數**，支援地區。

```php
use Illuminate\Support\Number;
Number::parseFloat('10'); // (float) 10.0
Number::parseFloat('10', locale: 'fr'); // (float) 10.0
```

---

### *Number::percentage()*

`Number::percentage($number, $precision = 0, $maxPrecision = null, $locale = null)`;

格式化為**百分比字串**。

```php
use Illuminate\Support\Number;
Number::percentage(10); // 10%
Number::percentage(10, precision: 2); // 10.00%
Number::percentage(10.123, maxPrecision: 2); // 10.12%
Number::percentage(10, precision: 2, locale: 'de'); // 10,00%
```

---

### *Number::spell()*

`Number::spell($number, $locale = null, $after = null, $until = null`);

數字轉**英文拼字**，可指定地區、範圍。

```php
use Illuminate\Support\Number;
Number::spell(102); // one hundred and two
Number::spell(88, locale: 'fr'); // quatre-vingt-huit
Number::spell(10, after: 10); // 10
// after: 10 代表「大於 10 才用英文拼字」，10 不大於 10，所以直接顯示 10
Number::spell(11, after: 10); // eleven
// 11 大於 10，所以顯示 eleven
Number::spell(5, until: 10); // five
// until: 10 代表「小於 10 才用英文拼字」，5 小於 10，所以顯示 five
Number::spell(10, until: 10); // 10
// 10 不小於 10，所以直接顯示 10
```

---

### *Number::spellOrdinal()*

`Number::spellOrdinal($number)`;

序數詞轉**英文拼字**。

```php
use Illuminate\Support\Number;
Number::spellOrdinal(1); // first
Number::spellOrdinal(2); // second
Number::spellOrdinal(21); // twenty-first
```

---

### *Number::trim()*

`Number::trim($number)`;

**去除**小數點後多餘 0。

```php
use Illuminate\Support\Number;
Number::trim(12.0); // 12
Number::trim(12.30); // 12.3
```

---

### *Number::useLocale()*

`Number::useLocale($locale)`;

全域設定**預設地區**。

```php
use Illuminate\Support\Number;
Number::useLocale('de');
```
- 設定全**域預設地區**為德國（de），之後所有數字格式化都會用德國格式
- 例如 1,500 會顯示為 1.500
- `useLocale` 則是全域設定，會影響所有地方
- 【命名說明】Laravel 採用 use/with 命名，是為了語意清楚：
   -  `use` 代表*全域設定*（全站都改用新設定），
   -  `with` 代表*暫時切換*（只在這個 callback 內臨時用新設定，外部不受影響）。
   -  像是「全公司都換制服」vs「只有這次出差暫時換制服」。
- `use` 在英文裡有「採用、設定」的意思。
- `with` 在英文裡有「帶著...一起」的意思。

---

### *Number::withLocale()*

`Number::withLocale($locale, $callback)`;

**暫時切換地區**執行 callback。

```php
use Illuminate\Support\Number;
Number::withLocale('de', function () {
    return Number::format(1500);
});
```
- 這段程式碼只在這個 `callback` 內暫時切換為德國（de）格式，外部不受影響
- 例如這裡 1500 會顯示為 1.500
- `withLocale` 只在這個 function 內暫時切換地區，外部還是原本的格式

---

### *Number::useCurrency()*

`Number::useCurrency($currency)`;

全域設定**預設貨幣**。

```php
use Illuminate\Support\Number;
Number::useCurrency('GBP');
```

---

### *Number::withCurrency()*

`Number::withCurrency($currency, $callback)`;

**暫時切換貨幣**執行 callback。

```php
use Illuminate\Support\Number;
Number::withCurrency('GBP', function () {
    // ...
});
```
- 這段程式碼只在這個 callb`ack 內暫時切換為英鎊（GBP）格式，外部不受影響
- 例如這裡的金額會顯示為 £1,000.00
- `withCurrency` 只在這個*function 內*，暫時切換貨幣，外部還是原本的貨幣
- `useCurrency` 則是*全域設定*，會影響所有地方

---

## **路徑**
- *app_path*：取得 `app 目錄路徑`。
- *base_path*：取得`專案根目錄`。
- *config_path*：`設定檔`路徑。
- *database_path*：`資料庫`路徑。
- *lang_path*：`語言檔`路徑。
- *public_path*：`public 目錄`路徑。
- *resource_path*：`resources 目錄`路徑。
- *storage_path*：`storage 目錄`路徑。
---

### *app_path()*

`app_path($path = '')`;

取得**app 目錄路徑**，可傳入**相對檔案**路徑。

```php
$path = app_path();
$path = app_path('Http/Controllers/Controller.php');
```

---

### *base_path()*

`base_path($path = '')`;

取得**專案根目錄**，可傳入**相對檔案**路徑。

```php
$path = base_path();
$path = base_path('vendor/bin');
```

---

### *config_path()*

`config_path($path = '')`;

取得 **config 目錄**路徑，可傳入**相對檔案**路徑。

```php
$path = config_path();
$path = config_path('app.php');
```

---

### *database_path()*

`database_path($path = '')`;

取得 **database 目錄**路徑，可傳入**相對檔案**路徑。

```php
$path = database_path();
$path = database_path('factories/UserFactory.php');
```

---

### *lang_path()*

`lang_path($path = '')`;

取得 **lang 目錄**路徑，可傳入**相對檔案**路徑。

```php
$path = lang_path();
$path = lang_path('en/messages.php');
```

---

### *public_path()*

`public_path($path = '')`;

取得 **public 目錄**路徑，可傳入**相對檔案**路徑。

```php
$path = public_path();
$path = public_path('css/app.css');
```

---

### *resource_path()*

`resource_path($path = '')`;

取得 **resources 目錄**路徑，可傳入**相對檔案**路徑。

```php
$path = resource_path();
$path = resource_path('sass/app.scss');
```

---

### *storage_path()*

`storage_path($path = '')`;
取得 **storage 目錄**路徑，可傳入**相對檔案**路徑。

```php
$path = storage_path();
$path = storage_path('app/file.txt');
```

---

## **URL**
- *action*：產生 `controller action URL`。
- *asset*：產生 `public 資源 URL`。
- *route*：產生`路由 URL`。
- *secure_asset / secure_url*：`https 資源`。
- *to_route*：`重導`至路由。
- *uri / url*：`產生` URL。
---

### *action()*

`action($action, $parameters = [], $absolute = true)`;

產生指定 **controller** action 的 URL，可帶參數。

```php
use App\Http\Controllers\HomeController;
$url = action([HomeController::class, 'index']);
// 若有參數：
$url = action([UserController::class, 'profile'], ['id' => 1]);
```

---

### *asset()*

`asset($path, $secure = null)`;

產生 **public 資源的 URL**，依請求協定自動切換 **http/https**。

```php
// 生成資源 URL
$url = asset('img/photo.jpg'); // http://example.com/img/photo.jpg

// 強制使用 HTTPS
$url = asset('img/photo.jpg', true); // https://example.com/img/photo.jpg

// 使用自訂資源主機
// 假設 .env 中設定 ASSET_URL=http://cdn.example.com
$url = asset('img/photo.jpg'); // http://cdn.example.com/img/photo.jpg
// asset() 會優先使用 .env 中的 ASSET_URL 作為主機，生成資源的完整 URL。
```

---

### *route()*

`route($name, $parameters = [], $absolute = true)`;

產生指定命名**路由的 URL**，可帶參數。

```php
$url = route('route.name');
$url = route('route.name', ['id' => 1]);
// 預設產生絕對 URL，若要相對路徑：
$url = route('route.name', ['id' => 1], false);
```

---

### *secure_asset()*

`secure_asset($path)`;

產生 **https** 資源的 URL。

```php
$url = secure_asset('img/photo.jpg');
// 產生 https 開頭的資源網址，例如 https://your-domain.com/img/photo.jpg
```

---

### *secure_url()*

`secure_url($path, $parameters = [])`;

產生 **https 完整 URL**，可帶參數。

```php
$url = secure_url('user/profile');
// 產生 https 開頭的完整網址，例如 https://your-domain.com/user/profile
$url = secure_url('user/profile', [1]);
// 產生帶參數的 https 完整網址，例如 https://your-domain.com/user/profile/1
```

---

### *to_route()*

`to_route($name, $parameters = [], $status = 302, $headers = [])`;
產生**重導**至**指定命名路由**的 HTTP 回應。

```php
return to_route('users.show', ['user' => 1]);
// 可帶狀態碼與 header：
return to_route('users.show', ['user' => 1], 302, ['X-Framework' => 'Laravel']);
```

---

### *uri()*

`uri($uri, $parameters = [])`;

產生 **URI 實例**，*可鏈式*設定路徑、查詢參數。

```php
$uri = uri('https://example.com')
        ->withPath('/users')
        ->withQuery(['page' => 1]);
// 產生 https://example.com/users?page=1 這樣的 URI 實例，可再鏈式操作
// withPath 設定路徑，withQuery 設定查詢參數

use App\Http\Controllers\UserController;
$uri = uri([UserController::class, 'show'], ['user' => $user]);
// 產生對應 controller action 的 URI 實例，並帶入參數（如 /user/{user}）

use App\Http\Controllers\UserIndexController;
$uri = uri(UserIndexController::class);
// 產生 invokable controller（只有 __invoke 方法的 controller）的 URI 實例

$uri = uri('users.show', ['user' => $user]);
// 產生命名路由的 URI 實例，並帶入參數（如 route('users.show', ['user' => $user])）
```

---

### *url()*

`url($path = null, $parameters = [], $secure = null)`;

產生**完整 URL**，可帶參數。

```php
$url = url('user/profile');
// 產生 https://your-domain.com/user/profile 這樣的完整網址

$url = url('user/profile', [1]);
// 產生帶參數的完整網址，例如 https://your-domain.com/user/profile/1

$current = url()->current();
// 取得目前頁面的網址（不含 query string）

$full = url()->full();
// 取得目前頁面的完整網址（含 query string）

$previous = url()->previous();
// 取得上一頁的網址
```

---

## **雜項**
- *abort / abort_if / abort_unless*：`終止請求`。
- *app*：取得 `app 實例`。
- *auth*：`認證`。
- *back*：重導`回前頁`。
- *bcrypt*：`加密`密碼。
- *blank / filled*：判斷`空值/有值`。
- *broadcast / broadcast_if / broadcast_unless*：`事件廣播`。
- *cache*：`快取`。
- *class_uses_recursive / trait_uses_recursive*：取得`類別/trait`使用情況。
- *collect*：建立 `Collection`。
- *config*：取得/設定 `config`。
- *context*：取得/設定 `context`。
- *cookie*：操作 `cookie`。
- *csrf_field / csrf_token*：`CSRF`。
- *decrypt / encrypt*：`加解密`。
- *dd / dump*：`debug`。
- *dispatch / dispatch_sync*：`派送任務`。
- *env*：取得 `.env` 變數。
- *event*：`事件`。
- *fake*：產生`假資料`。
- *info / logger*：`log`。
- *literal*：產生`原始字串`。
- *method_field*：產生隱藏 `HTTP method` 欄位。
- *now / today*：現在/今天。
- *old*：取得`舊輸入`。
- *once*：`只執行一次`。
- *optional*：`可選物件`。
- *policy*：取得`policy`。
- *redirect*：`重導`。
- *report / report_if / report_unless*：`例外回報`。
- *request / response*：`請求/回應`。
- *rescue*：`捕捉例外`。
- *resolve*：`解析服務`。
- *retry*：`重試`。
- *session*：`Session` 操作。
- *tap*：`操作後回傳自身`。
- *throw_if / throw_unless*：條件`丟出例外`。
- *transform*：`轉換值`。
- *validator*：`驗證`。
- *value*：`取得值`。
- *view*：產生`view`。
- *with / when*：`條件包裝`。

**範例**：

```php
collect([1,2,3])->sum();
now();
optional($user)->name;
filled('abc'); // true
blank(''); // true
view('welcome');
``` 
---

### *abort()*

`abort($code, $message = '', $headers = [])`;

**終止**請求並回傳指定 HTTP 狀態碼。

- `$code` 的用途是讓開發者可以根據不同的情境，**回傳適合的 HTTP 狀態碼**。

```php
abort(403);
abort(403, 'Unauthorized.', $headers);
```

---

### *abort_if()*

`abort_if($boolean, $code, $message = '', $headers = [])`;

若條件為 **true** ，終止請求並回傳指定 HTTP 狀態碼。

```php
abort_if(! Auth::user()->isAdmin(), 403);
// 可加訊息與 headers
abort_if($condition, 403, 'Unauthorized.', $headers);
```

---

### *abort_unless()*

`abort_unless($boolean, $code, $message = '', $headers = [])`;

若條件為 **false** ，終止請求並回傳指定 HTTP 狀態碼。

```php
abort_unless(Auth::user()->isAdmin(), 403);
// 可加訊息與 headers
abort_unless($condition, 403, 'Unauthorized.', $headers);
```

---

### *app()*

`app($abstract = null, array $parameters = [])`;

取得**服務容器實例**，或解析服務容器中註冊的物件/類別。

- **註解**：
  - Laravel 的「`服務容器`」（Service Container）是一個用來*管理、解析*各種物件與依賴的核心元件，負責**依賴注入**與**物件組裝**。
  - 它就像一個「自動販賣機」或「工廠」，你只要告訴它要什麼類別或服務名稱，它會自動幫你組裝好所有依賴並回傳物件。

  - 這和 Docker 的「容器」完全不同，`Docker` 容器是*用來隔離、運行應用程式的虛擬化技術*，兩者只是名稱相同，概念完全無關。

  - `Service Provider`（如 AppServiceProvider）是用來「*註冊*」服務到服務容器的地方，*實際物件解析與依賴管理則由`服務容器`負責*。
  - 不帶參數時，回傳目前的*服務容器實例*（`Illuminate\Container\Container`）。
  - 傳入*類別名稱*（或介面/抽象名稱）時，會從`服務容器`解析（resolve）並回傳對應的*物件實例*，等同於*依賴注入*。
  - 可額外傳入*建構參數*（$parameters）。
  
  - `app()` 是 Laravel *依賴注入*的核心，讓你隨時取得`服務容器`或解析任何已註冊的服務。
    - 例如：你可以用 `app('request')` 取得目前的 Request 實例，或用 `app(SomeService::class)` 取得自訂服務。

  - 這種設計讓程式更容易測試、擴充與維護。

  - 比喻：可以把 `app()` 想像成一個「工廠」或「倉庫」，你只要告訴它要什麼（類別/服務名稱），它就會幫你生出來，並自動處理好所有依賴。


```php
// 取得服務容器實例
$container = app();

// 解析某個類別（自動注入依賴）
$api = app('HelpSpot\API');

// 解析時傳入建構參數
$foo = app(Foo::class, ['bar' => 'baz']);
```

---

### *auth()*

`auth($guard = null)`;

取得**認證器實例**，可用於取得目前登入使用者。

- **註解**：
  - `$guard` 參數用來指定要使用哪一個「guard」（守衛）來進行認證。
  - guard 是 Laravel *認證系統*的概念，用來定義「*如何*」以及「*從哪裡*」驗證使用者（例如：web、api、admin 等）。
  - 每個 guard 都有自己的設定（如 session、token、provider 等），可以在 `config/auth.php` 裡面設定。

  - `auth('admin')` 代表「*使用 admin 這個 guard 來取得目前登入的使用者*」。這通常用於**多身分系統**，例如：前台會員、後台管理員各自有不同的登入邏輯與資料表。
  - **middleware**（如 `auth:admin`）是用來 *保護路由* ，要求進入該路由時必須通過指定 guard 的認證；
  - 而 `auth('admin')` 則是程式中 *主動取得* 指定 guard 的認證狀態或使用者。

  - 若你有多種使用者身分（如會員、管理員），就會用到多個 guard。
  - 不論 guard 名稱為何， *取得目前登入使用者的方法* 都叫 `user()`，只是回傳的物件型別會依 guard 設定而不同。例如 `auth('web')->user()` 可能回傳 `App\Models\User`，`auth('admin')->user()` 可能回傳 `App\Models\Admin`。
  - 這是因為 `guard` 只是「*認證規則*」的名稱，
  - `user()` 則是「*取得該 guard 下目前登入的使用者*」的方法，兩者概念不同。

  - 比喻：`guard` 就像「櫃檯」，你去「會員櫃檯」查詢會拿到會員資料，去「管理員櫃檯」查詢會拿到管理員資料，但查詢的方法都叫「查詢使用者」。
  - `config/auth.php` 範例：

    ```php
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\\Models\\User::class,
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\\Models\\Admin::class,
        ],
    ],
    ```
- 因此 `auth('admin')->user()` 會回傳 `App\Models\Admin` 的實例，`auth('web')->user()` 會回傳 `App\Models\User` 的實例。


```php
$user = auth()->user(); // 使用預設 guard（通常是 web）
$user = auth('admin')->user(); // 使用名為 admin 的 guard
```

---

### *back()*

`back($status = 302, $headers = [], $fallback = false)`;

**重導**回前一頁。

```php
return back($status = 302, $headers = [], $fallback = '/');
return back();
```

---

### *bcrypt()*

`bcrypt($value, $options = [])`;

使用 Bcrypt **加密**給定字串。

```php
$password = bcrypt('my-secret-password');
```

---

### *blank()*

`blank($value)`;

判斷值**是否**為「`空`」。空字串、null、空集合等皆為 true。

```php
blank(''); // true
blank('   '); // true
blank(null); // true
blank(collect()); // true
blank(0); // false
blank(true); // false
blank(false); // false
```

---

### *broadcast()*

`broadcast($event)`;

**廣播事件**給監聽者。

```php
broadcast(new UserRegistered($user));
broadcast(new UserRegistered($user))->toOthers();
```

---

### *broadcast_if()*

`broadcast_if($boolean, $event)`;

若條件為 **true** ，廣播事件。

```php
broadcast_if($user->isActive(), new UserRegistered($user));
broadcast_if($user->isActive(), new UserRegistered($user))->toOthers();
```

---

### *broadcast_unless()*

`broadcast_unless($boolean, $event)`;
若條件為 **false** ，廣播事件。

```php
broadcast_unless($user->isBanned(), new UserRegistered($user));
broadcast_unless($user->isBanned(), new UserRegistered($user))->toOthers();
```

---

### *cache()*

`cache($key = null, $default = null)`;

取得快取值，或設定快取。

```php
$value = cache('key');
$value = cache('key', 'default');
// 設定快取
cache(['key' => 'value'], 300);
cache(['key' => 'value'], now()->addSeconds(10));
```

---

### *class_uses_recursive()*

`class_uses_recursive($class)`;

取得`類別及其父類別`所用的**所有 trait**。

```php
$traits = class_uses_recursive(App\Models\User::class);
```

---

### *collect()*

`collect($value = null)`;

建立 Collection 實例。

```php
$collection = collect(['Taylor', 'Abigail']);
```

---

### *config()*

`config($key = null, $default = null)`;

取得或設定**設定檔變數**。

```php
$value = config('app.timezone');
$value = config('app.timezone', $default);
// 設定（僅本次請求有效）
config(['app.debug' => true]);
```

---

### *context()*

`context($key = null, $default = null)`;

取得或設定 **context 變數**。

- **註解**：
  - `context()` 是 Laravel 10.33 之後新增的輔助函式，用來「*取得或設定全域 context 變數*」。
  - 這些 `context` 變數只在「*單一請求*」期間有效，不會影響其他請求或用戶。
  - 常用於 *log 追蹤、分散式追蹤（如 trace_id）、自訂全域變數*等，方便在請求生命週期內任何地方安全存取。
  - 可以把 `context()` 想像成「全域便條紙」，你可以在請求的任何地方寫入或讀取資料，*方便跨層級追蹤與記錄*。
  - 記錄 log、例外、事件時，*自動帶入 trace_id、user_id* 等追蹤資訊，提升除錯與追蹤效率。


```php
$value = context('trace_id');
$value = context('trace_id', $default);
// 設定
use Illuminate\Support\Str;
context(['trace_id' => Str::uuid()->toString()]);

// 1. 取得 context 變數
// 假設之前已設定 trace_id
context(['trace_id' => '12345']);

$value = context('trace_id');
echo $value; // 輸出: 12345

// 2. 取得不存在的變數，回傳預設值
$value = context('non_existent_key', 'default_value');
echo $value; // 輸出: default_value

// 3. 設定單一變數
context(['user_id' => 42]);

echo context('user_id'); // 輸出: 42

// 4. 設定多個變數
context([
    'trace_id' => '12345',
    'user_id' => 42,
]);

echo context('trace_id'); // 輸出: 12345
echo context('user_id');  // 輸出: 42

// 5. 取得所有 context 變數
context([
    'trace_id' => '12345',
    'user_id' => 42,
]);

print_r(context());
// 輸出:
// [
//     'trace_id' => '12345',
//     'user_id' => 42,
// ]
```

---

### *cookie()*

`cookie($name = null, $value = null, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true, $raw = false, $sameSite = null)`;

建立新的 cookie 實例。

```php
$cookie = cookie('name', 'value', $minutes);
```

---

### *csrf_field()*

`csrf_field()`;

產生**隱藏的 CSRF token 欄位**（HTML input）。

```php
{{ csrf_field() }}
```

---

### *csrf_token()*

`csrf_token()`;

**取得**目前 CSRF token。

```php
$token = csrf_token();
```

---

### *decrypt()*

`decrypt($value, $unserialize = true)`;

**解密**給定值。

```php
$password = decrypt($value);
```

---

### *dd()*

`dd(...$vars)`;

輸出變數內容並終止執行。

```php
dd($value);
dd($value1, $value2, $value3, ...);
```

---

### *dispatch()*

`dispatch($job)`;

將任務推送至 Laravel **任務佇列**。

```php
dispatch(new App\Jobs\SendEmails);
```

---

### *dispatch_sync()*

`dispatch_sync($job)`;

**立即同步**執行任務。

```php
dispatch_sync(new App\Jobs\SendEmails);
```

---

### *dump()*

`dump(...$vars)`;

輸出變數內容（不中斷執行）。

```php
dump($value);
dump($value1, $value2, $value3, ...);
```

---

### *encrypt()*

`encrypt($value, $serialize = true)`;

**加密**給定值。

```php
$secret = encrypt('my-secret-value');
```

---

### *env()*

`env($key, $default = null)`;

取得 .env 變數值，或回傳預設值。

```php
$env = env('APP_ENV');
$env = env('APP_ENV', 'production');
``` 
---

### *event()*

`event($event, $payload = [])`;

**派送事件**給所有監聽者。

```php
event(new UserRegistered($user));
```

---

### *fake()*

`fake($locale = null)`;

取得 Faker 單例，用於**產生假資料**，可指定語系。

```php
fake()->name();
fake()->unique()->safeEmail();
fake('nl_NL')->name();
```

---

### *filled()*
 vs `blank()`

`filled($value)`;

判斷值**是否**「非空」。

```php
filled(0); // true
filled(true); // true
filled(false); // true
filled(''); // false
filled('   '); // false
filled(null); // false
filled(collect()); // false
```

---

### *info()*

`info($message, $context = [])`;

寫入資訊到應用程式 log。

```php
info('Some helpful information!');
info('User login attempt failed.', ['id' => $user->id]);
```

---

### *literal()*

`literal(...$properties)`;

建立`帶命名屬性`的 **stdClass 實例**。

- **註解**：
  - `literal` 在程式語言中指「*直接寫在程式碼裡的固定值*」，不是變數、不是運算結果，也不是函式回傳值。
  - 常見的 literal 型別有：
    - 數字：`123`、`3.14`
    - 字串：`'hello'`、`"world"`
    - 布林：`true`、`false`
  - 例如：

```php
$name = 'Vincent'; // 'Vincent' 是 string literal
$age = 18;         // 18 是 integer literal
$isAdmin = false;  // false 是 boolean literal
```
- literal 也有「*照字面意思*」的意思，例如 `literal translation`（直譯），和隱喻、抽象相對。
- 在*型別系統*（如 TypeScript）中，`literal type` 指的是「*只能是某個特定字面值*」的型別。


```php
$obj = literal(
    name: 'Joe',
    languages: ['PHP', 'Ruby'],
);
// $obj->name; // 'Joe'
// $obj->languages; // ['PHP', 'Ruby']
```

- 它的主要用途是方便地**創建一個物件（stdClass）**，並為其屬性賦值，讓程式碼更簡潔且具可讀性。

- `literal()` 的功能
  - *快速建立物件*：
    - 使用**命名參數**（named arguments）來定義`屬性和值`，直接生成一個物件（stdClass）。
    - 這比手動建立 stdClass 並逐一設定屬性更簡潔。

  - *提高可讀性*：
    - 透過**命名參數**的方式，程式碼更具語意化，易於理解。

  - *靈活性*：
    - 可以用來傳遞臨時的物件資料，適合用於**測試、快速構建資料結構**等場景。

---

### *logger()*

`logger($message = null, $context = [])`;

寫入 **debug** 訊息到 log，或取得 logger 實例。

```php
logger('Debug message');
logger('User has logged in.', ['id' => $user->id]);
logger()->error('You are not allowed here.');
```

---

### *method_field()*

`method_field($method)`;

產生**隱藏 HTTP method 欄位**（HTML input）。

```php
<form method="POST">
    {{ method_field('DELETE') }}
</form>
```

---

### *now()*

`now($tz = null)`;

取得**目前時間**（Carbon 實例）。

```php
$now = now();
```

---

### *old()*

`old($key = null, $default = null)`;

取得 `session` 中舊輸入值，可設預設值或直接傳 `Eloquent model`。

```php
$value = old('value');
$value = old('value', 'default');
{{ old('name', $user->name) }}
// 等同於
{{ old('name', $user) }}
```

---

### *once()*

`once($callback)`;

執行 `callback` 並**快取結果**於本次請求，重複呼叫回傳相同結果。

- **註解**：
  - `once()` 是 Laravel 的全域輔助函式，用來「*只執行一次*」某個 callback，並在*同一次請求期間快取結果*。
  - 即使你在同一個請求中多次呼叫 `once()` 包裹的函式，*實際只會執行一次*，之後都回傳第一次的結果。
  - 適合用於「*重複呼叫但只需計算一次*」的場景，例如**隨機值、查詢、初始化**等，能**有效避免重複運算或查詢**。
  - 在`物件方法`中使用時，每個物件實例*各自快取*，不會互相影響。
  - 可以把 `once()` 想像成「*本次請求只執行一次的快取機制*」。

```php
function random(): int {
    return once(function () {
        return random_int(1, 1000);
    });
}
random(); // 123
random(); // 123 (快取，不會再執行 random_int)
```

- 於`物件內部`，每個物件實例各自快取
```php
class NumberService {
    public function all(): array {
        return once(fn () => [1, 2, 3]);
    }
}
$service = new NumberService;
$service->all(); // 第一次執行，回傳 [1,2,3]
$service->all(); // 之後都回傳同一結果（快取）
```

---

### *optional()*

`optional($value = null, $callback = null)`;

**包裝物件**，若為 null 則所有屬性/方法回傳 null。

- **註解**：
  - `optional()` 是 Laravel 的全域輔助函式，用來「*包裝一個物件*」，讓你可以*安全地存取*其屬性或方法，**即使該物件為 null 也不會拋出錯誤，而是回傳 null**。
  - 可以把「可能為 null」的物件包裝起來，讓你安全地鏈式存取屬性或方法，不用每次都判斷**是否**為 null。
  - 如果包裝的物件是 null，任何屬性或方法都會回傳 null，不會拋出錯誤。
  - 也可用於「*條件執行 closure*」，只有在物件存在時才執行 closure，否則回傳 null。
  - 常用於*避免*「呼叫 null 物件的屬性或方法」時產生的錯誤（Null Pointer Exception）。
  - 比喻：`optional()` 就像「*保險套件*」，讓你在操作可能為 null 的物件時，不用擔心出錯，所有操作都會自動安全地回傳 null。


```php
return optional($user->address)->street;
// 如果 $user->address 為 null，這裡會回傳 null，不會拋出錯誤

{!! old('name', optional($user)->name) !!}
// 如果 $user 為 null，這裡會回傳 null

// 可傳 closure，若 $value 非 null 執行 closure
return optional(User::find($id), function (User $user) {
    return $user->name;
});
```

---

### *policy()*

`policy($class)`;

取得指定類別的 **policy 實例**。

- **註解**：
  - `policy()` 是 Laravel 的全域輔助函式，用來取得指定類別（通常是 Model）的 *policy 實例*。
  - policy（`授權政策`）是 Laravel 授權系統的核心，用來集中管理「*誰可以對什麼資源做哪些操作*」的邏輯。
  - 你可以用 `policy()` 取得某個 `Model` 對應的 policy 物件，進而呼叫其中的方法進行授權判斷。
  - 常用於自訂授權流程、手動檢查權限、或在非 Controller/Blade 等地方進行授權。
  - 比喻：`policy()` 就像「規則查詢器」，你可以隨時查詢某個資源的行為規則，並根據規則決定**是否**允許操作。


```php
$policy = policy(App\Models\User::class);
// 取得 User Model 對應的 policy 實例

if ($policy->update($user, $targetUser)) {
    // 允許 $user 更新 $targetUser
} else {
    // 不允許
}
```

---

### *redirect()*

`redirect($to = null, $status = 302, $headers = [], $secure = null)`;

產生`重導`回應，或取得 redirector 實例。

```php
return redirect($to = null, $status = 302, $headers = [], $secure = null);
return redirect('/home');
return redirect()->route('route.name');
```

---

### *report()*

`report($exception)`;

回報**例外**至 `exception handler`，可直接傳*例外*或*字串*。

```php
report($e);
report('Something went wrong.');
```

---

### *report_if()*

`report_if($boolean, $exception)`;

若條件為 **true** ，回報例外。

```php
report_if($shouldReport, $e);
report_if($shouldReport, 'Something went wrong.');
```

---

### *report_unless()*

`report_unless($boolean, $exception)`;

若條件為 **false** ，回報例外。

```php
report_unless($reportingDisabled, $e);
report_unless($reportingDisabled, 'Something went wrong.');
```

---

### *request()*

`request($key = null, $default = null)`;

取得目前 **request 實例**，或直接取得輸入值。

```php
$request = request();
$value = request('key', $default);
```

---

### *rescue()*

`rescue($callback, $rescue = null, $report = true)`;

執行 closure，**捕捉例外**並回傳預設值或執行 fallback。

- **註解**：
  - `rescue()` 是 Laravel 的全域輔助函式，用來「**安全執行**」一段*可能會丟出例外的程式碼（closure）*。
  - 如果執行過程中發生例外，會*自動捕捉並回傳你指定的預設值（$rescue），或執行 fallback closure*。
  - 預設會將例外回報給 `exception handler`，你也可以自訂哪些例外要回報或忽略。
  - 常用於「*不希望例外中斷流程*」的場景，例如：資料查詢、外部 API 呼叫、非關鍵操作等。
  - 比喻：`rescue()` 就像「**安全網**」，讓你在執行風險操作時，即使失敗也能優雅處理，不會讓整個程式崩潰。


```php
return rescue(function () {
    return $this->method();
});
// 若發生例外，回傳 null

return rescue(function () {
    return $this->method();
}, false);
// 若發生例外，回傳 false

return rescue(function () {
    return $this->method();
}, function () {
    return $this->failure();
});
// 若發生例外，執行 fallback closure

// 可自訂哪些例外要回報
return rescue(function () {
    return $this->method();
}, report: function (Throwable $throwable) {
    return $throwable instanceof InvalidArgumentException;
});
```

---

### *resolve()*

`resolve($abstract, $parameters = [])`;

透過**服務容器**解析類別或介面。

- **註解**：
  - `resolve()` 是 Laravel 的全域輔助函式，用來*透過服務容器解析（產生）指定的類別或介面實例*。
  - 會自動處理依賴注入，並回傳對應的物件。
  - 常用於動態取得服務、手動解析依賴、或在非自動注入環境下取得物件。
  - 與 `app()` 類似，但語意更明確，強調「*解析*」的動作，適合用於「*我要一個現成的物件*」這種語境。
  - 在測試時可用於替換服務實作，或在工廠、事件、閉包等動態環境下取得依賴。
  - 比喻：`resolve()` 就像「*請服務中心幫你組裝好一個物件*」，你只要說出名稱，所有依賴都會自動準備好。


```php
$api = resolve('HelpSpot\API');
// 解析並取得 HelpSpot\API 服務的實例

$service = resolve(App\Services\ReportService::class, ['type' => 'daily']);
// 傳入建構參數，取得對應服務實例
```

---

### *response()*

`response($content = '', $status = 200, array $headers = [])`;

產生 **response 實例**，或取得 response factory。

```php
return response('Hello World', 200, $headers);
return response()->json(['foo' => 'bar'], 200, $headers);
```

---

### *retry()*

`retry($times, $callback, $sleep = 0, $when = null)`;

**重試**指定次數的 callback，失敗時自動重試。

```php
return retry(5, function () {
    // 最多重試 5 次，每次間隔 100ms
}, 100);
// 自訂每次重試間隔
use Exception;
return retry(5, function () {
    // ...
}, function (int $attempt, Exception $exception) {
    return $attempt * 100;
});
// 以陣列指定每次間隔
return retry([100, 200], function () {
    // 第一次重試 100ms，第二次 200ms
});
// 只在特定例外時重試
use App\Exceptions\TemporaryException;
return retry(5, function () {
    // ...
}, 100, function (Exception $exception) {
    return $exception instanceof TemporaryException;
});
``` 
---

### *session()*

`session($key = null, $default = null)`;

取得或設定 Session 值。

```php
$value = session('key');
session(['chairs' => 7, 'instruments' => 3]);
// 取得 Session 實例
$value = session()->get('key');
session()->put('key', $value);
```

---

### *tap()*

`tap($value, $callback = null)`;

將值傳入 closure 處理後**回傳原值**，常用於**鏈式操作**或**副作用**。

```php
$user = tap(User::first(), function (User $user) {
    $user->name = 'Taylor';
    $user->save();
});
// 不傳 closure 時可直接鏈式呼叫方法
$user = tap($user)->update([
    'name' => $name,
    'email' => $email,
]);
// 類別可用 Tappable trait
return $user->tap(function (User $user) {
    // ...
});
```

---

### *throw_if()*

`throw_if($boolean, $exception, ...$parameters)`;

若條件為 **true** ，丟出指定例外。

```php
throw_if(! Auth::user()->isAdmin(), AuthorizationException::class);
throw_if(
    ! Auth::user()->isAdmin(),
    AuthorizationException::class,
    'You are not allowed to access this page.'
);
```

---

### *throw_unless()*

`throw_unless($boolean, $exception, ...$parameters)`;

若條件為 **false** ，丟出指定例外。

```php
throw_unless(Auth::user()->isAdmin(), AuthorizationException::class);
throw_unless(
    Auth::user()->isAdmin(),
    AuthorizationException::class,
    'You are not allowed to access this page.'
);
```

---

### *today()*

`today($tz = null)`;

取得**今天日期**（Carbon 實例）。

```php
$today = today();
```

---

### *trait_uses_recursive()*

`trait_uses_recursive($trait)`;

**取得 trait 所使用的所有 trait**。

- **註解**：
  - `trait_uses_recursive()` 是 Laravel（實際上是 PHP 社群常用的輔助函式），用來「*取得某個 trait 所 use 的所有 trait*（包含巢狀、遞迴展開）」。
  - 傳入一個 trait 名稱（或物件/類別），會回傳這個 trait 直接或間接（巢狀）use 的所有 trait 名稱（陣列）。
  - 這個函式會遞迴展開所有被 use 的 trait，不只抓最外層，連巢狀 trait 也會一併列出。
  - 常用於分析類別或 trait 的組成、debug、或自動化工具，特別是在多重 trait 組合時。
  - 比喻：就像「*家族樹*」展開，不只看直系，連所有祖先（巢狀 trait）都會列出來。


```php
$traits = trait_uses_recursive(\Illuminate\Notifications\Notifiable::class);
// 取得 Notifiable 這個 trait 以及它內部 use 的所有 trait
```

---

### *transform()*

`transform($value, $callback, $default = null)`;

若值**非空**則執行 closure 並回傳結果，否則回傳預設值。

- **註解**：
  - 這個全域 `transform()` 輔助函式和 `Arr::transform()`、`Collection::transform()` 是不同的東西。
  - 全域 `transform()` 用於「*單一值*」的條件轉換：
    **如果 $value `不是空值`（not blank），就執行 $callback($value) 並回傳結果，否則回傳 $default**。
    
  - `Arr::transform(&$array, $callback)` 則是「*就地*」轉換陣列的每個元素，**會直接改變原陣列內容**。

  - `Collection::transform() `也是「就地」修改集合內容。

  - 差異總結：
    | 名稱                   | 作用對象   | 是否就地修改   | 用途說明                   |
    |-----------------------|-----------|--------------|---------------------------|
    | `transform() `        | 單一值     | 否           | 有值才轉換，否則回傳預設值    |
    | `Arr::transform()`    | 陣列       | 是           | 逐一轉換陣列元素，直接改原陣列 |
    | `Collection::transform()` | 集合   | 是           | 逐一轉換集合元素，直接改原集合 |

  - 全域 `transform()` 適合用於「*有值才轉換，沒值就給預設*」的情境；Arr/Collection 的 `transform` 適合用於*批次處理陣列或集合*。


```php
// 全域 transform()
$callback = function (int $value) {
    return $value * 2;
};
$result = transform(5, $callback); // 10
$result = transform(null, $callback, 'The value is blank'); // The value is blank

// Arr::transform()
use Illuminate\Support\Arr;
$array = [1, 2, 3];
Arr::transform($array, function ($value) {
    return $value * 2;
});
// $array 變成 [2, 4, 6]
```

---

### *validator()*

`validator($data = [], $rules = [], $messages = [], $customAttributes = [])`;

建立新的**驗證器實例**。

```php
$validator = validator($data, $rules, $messages);
```

---

### *value()*

`value($value, ...$args)`;

回傳給定值，若為 closure 則執行 closure 並回傳結果。

- **註解**：
  - `value()` 與 `with()` 行為類似，但語意與設計目的略有不同。
  - `value()` 強調「*取得值*」或「*計算值*」的語意，常用於 `config、預設值、延遲運算`等情境。
  - 若 **$value 是 closure，則執行該 closure 並回傳結果**（可傳額外參數）；**否則直接回傳 $value**。
  - 適合用於「*如果是 closure 就執行，否則直接回傳*」的情境，例如 config('foo', value(...))。
  - 差異總結：
    | 名稱         | 主要用途         | 典型用法             | 語意重點         |
    |-------------|-----------------|--------------------|-----------------|
    | `value()`   | 取得/計算值      | 預設值、延遲運算      | 「取得值」        |
    | `with()`    | 處理/包裝值      | 鏈式、副作用         | 「帶著值做處理」   |


```php
$result = value(true); // true
$result = value(function () {
     return false;
      }); // false

// 可傳額外參數給 closure
$result = value(function ($name) {
     return $name;
      }, 'Taylor'); // 'Taylor'
```

---

### *with()*

`with($value, $callback = null)`;

回傳給定值，若傳 closure 則執行 closure 並回傳結果。

- **註解**：
  - `with()` 強調「*帶著這個值做某件事*」的語意，常用於`鏈式操作、臨時處理、或副作用`。
  - 若**有傳 $callback，則執行 $callback($value) 並回傳結果**；**若 $callback 為 null，直接回傳 $value**。
  - 適合用於「*帶著這個值做某件事*」的語境，例如 `tap/with` 鏈式操作。
  - 與 `value()` 的差異如上表所示。


```php
$callback = function ($value) {
    return is_numeric($value) ? $value * 2 : 0;
};
$result = with(5, $callback); // 10
$result = with(null, $callback); // 0
$result = with(5, null); // 5
```

---

### *view()*

`view($view = null, $data = [], $mergeData = [])`;

取得 **view 實例**。

```php
return view('auth.login');
```

---

### *when()*

`when($value, $callback = null, $default = null)`;

若條件為 **true** 回傳值，否則回傳 null。可傳 closure 作為回傳值。

```php
$value = when(true, 'Hello World');
$value = when(true, fn () => 'Hello World');
// 常用於 Blade 條件屬性
<div {!! when($condition, 'wire:poll="calculate"') !!}>...</div>
``` 

---

## **其他工具（Other Utilities）**

### *Benchmark*

**快速測量**程式區塊**執行時間**（毫秒）。

```php
use Illuminate\Support\Benchmark;
Benchmark::dd(fn () => User::find(1)); // 0.1 ms
// 立即執行 User::find(1)，並輸出執行時間（毫秒）

Benchmark::dd([
    '方案1' => fn () => User::count(),
    '方案2' => fn () => User::all()->count(),
]);
// 同時測量多個方案的執行時間，並比較結果

// 多次執行取平均
Benchmark::dd(fn () => User::count(), iterations: 10);
// 執行 User::count() 10 次，取平均執行時間

// 取得回傳值與耗時
[$count, $duration] = Benchmark::value(fn () => User::count());
// 執行 User::count()，$count 為回傳值，$duration 為耗時（毫秒）
```

---

### *Deferred Functions（defer）*

**延遲**執行 closure，於 **HTTP 回應送出後** ，才執行。

```php
use function Illuminate\Support\defer;

defer(fn () => Metrics::reportOrder($order));
// 註冊一個 closure，於 HTTP 回應送出後才執行（如記錄訂單指標）

// 總是執行

defer(fn () => Metrics::reportOrder($order))->always();
// 註冊 closure 並設定為「無論如何都執行」（即使請求異常結束也會執行）

// 命名並取消

defer(fn () => Metrics::report(), 'reportMetrics');
// 註冊一個命名的 defer 任務，可用於後續取消

defer()->forget('reportMetrics');
// 取消名為 'reportMetrics' 的 defer 任務，不再執行
```
- 測試時可用 `$this->withoutDefer()` 立即執行所有 defer。

---

### *Lottery*

根據**機率**執行 callback，常用於隨機事件。

```php
use Illuminate\Support\Lottery;
Lottery::odds(1, 20)
        ->winner(fn () => $user->won())
        // 設定 1/20 機率時執行 winner callback（$user->won()）
        ->loser(fn () => $user->lost())
        // 其餘情況執行 loser callback（$user->lost()）
        ->choose();
// 執行抽獎，依機率決定呼叫 winner 或 loser

// 可結合查詢慢查報告等
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\DB;
DB::whenQueryingForLongerThan(
    CarbonInterval::seconds(2),
    Lottery::odds(1, 100)->winner(fn () => report('Querying > 2 seconds.')),
);
// 當查詢超過 2 秒時，以 1/100 機率回報警告（report）

// 測試用
Lottery::alwaysWin();
// 之後所有 Lottery 抽獎都會中獎（winner callback 一定執行）
Lottery::alwaysLose();
// 之後所有 Lottery 抽獎都不會中獎（loser callback 一定執行）
Lottery::fix([true, false]);
// 依序固定回傳 true/false，方便測試
Lottery::determineResultsNormally();
// 恢復正常隨機機率判斷
```

---

### *Pipeline*

將資料**依序傳遞**給多個 `closure` 或 `invokable class` 處理。

- **註解**：
  - 你的觀察完全正確！Laravel 的 `middleware`（中介層）機制底層就是用 Pipeline 設計模式實作的。
  - `Pipeline`（管線）是一種設計模式，讓資料可以依序經過一連串的處理步驟，每個步驟都可以改變資料或決定**是否**繼續。
  - 在 Laravel 中，`HTTP request` 進來時，會依序經過所有註冊的 `middleware`，每個 `middleware` 就像 Pipeline 的一個節點。
  - Laravel 內部會用 Pipeline 類別把 Request 物件送進管線，依序傳給每個 middleware，最後交給路由處理。
  - 你在自訂 `middleware、HTTP 請求流程`時，實際上就是在用 Pipeline 模式。
  - 你也可以在自己的程式中用 Pipeline 處理資料流，和 middleware 的用法很像。

```php
use Illuminate\Support\Facades\Pipeline;
$user = Pipeline::send($user)
        ->through([
            function (User $user, Closure $next) {
                // ...
                return $next($user);
                // 處理 $user 後傳給下一個 closure
            },
            function (User $user, Closure $next) {
                // ...
                return $next($user);
                // 處理 $user 後傳給下一個 closure
            },
        ])
    ->then(fn (User $user) => $user);
// 依序經過所有 closure 處理，最後回傳 $user
```

---

### *Sleep*

可測試的 `sleep/usleep` 包裝，支援多種單位與測試斷言。

```php
use Illuminate\Support\Sleep;
Sleep::for(1)->second();
// 休眠 1 秒
Sleep::for(1.5)->minutes();
// 休眠 1.5 分鐘
Sleep::for(500)->milliseconds();
// 休眠 500 毫秒
Sleep::for(5000)->microseconds();
// 休眠 5000 微秒
Sleep::until(now()->addMinute());
// 休眠直到指定時間（如 1 分鐘後）
Sleep::sleep(2); // PHP sleep
// 直接呼叫 PHP 原生 sleep 函式，休眠 2 秒
Sleep::usleep(5000); // PHP usleep
// 直接呼叫 PHP 原生 usleep 函式，休眠 5000 微秒
// 組合單位
Sleep::for(1)->second()->and(10)->milliseconds();
// 先休眠 1 秒，再休眠 10 毫秒
// 測試時 fake
Sleep::fake();
// 啟用假 sleep，測試時不會真的休眠
Sleep::assertSleptTimes(3);
// 斷言 sleep 被呼叫 3 次
Sleep::assertNeverSlept();
// 斷言 sleep 從未被呼叫
Sleep::assertInsomniac();
// 斷言完全沒 sleep
// 進階：同步 Carbon 時間
Sleep::fake(syncWithCarbon: true);
// 啟用假 sleep 並同步 Carbon 時間
```

---

### *Timebox*

確保 closure 執行**至少固定時間**（微秒），常用於安全性需求。

```php
use Illuminate\Support\Timebox;
(new Timebox)->call(function ($timebox) {
    // ...
    // 執行你的程式碼
}, microseconds: 10000);
// 確保 closure 執行至少 10000 微秒（10 毫秒），不足會自動 sleep 補足
```

---

### *URI*

方便建立、操作 URI，支援路由、控制器、查詢字串等。

```php
use Illuminate\Support\Uri;
$uri = Uri::of('https://example.com/path');
// 建立一個指定網址的 URI 實例
$uri = Uri::to('/dashboard');
// 產生相對路徑的 URI 實例
$uri = Uri::route('users.show', ['user' => 1]);
// 產生命名路由的 URI 實例，並帶入參數
$uri = Uri::signedRoute('users.show', ['user' => 1]);
// 產生帶簽章的路由 URI 實例
$uri = Uri::action([UserController::class, 'index']);
// 產生 controller action 的 URI 實例
// 操作 URI
$uri = $uri->withScheme('http')
        // 設定協定為 http
        ->withHost('test.com')
        // 設定主機為 test.com
        ->withPort(8000)
        // 設定 port 為 8000
        ->withPath('/users')
        // 設定路徑為 /users
        ->withQuery(['page' => 2])
        // 設定查詢參數 page=2
        ->withFragment('section-1');
        // 設定 fragment 為 section-1
// 取得各部分
$scheme = $uri->scheme();
// 取得協定（如 http、https）
$host = $uri->host();
// 取得主機名稱
$port = $uri->port();
// 取得 port
$path = $uri->path();
// 取得路徑
$segments = $uri->pathSegments();
// 取得路徑分段陣列
$query = $uri->query();
// 取得查詢參數陣列
$fragment = $uri->fragment();
// 取得 fragment
// 操作查詢字串
$uri = $uri->withQuery(['sort' => 'name']);
// 設定查詢參數 sort=name
$uri = $uri->withQueryIfMissing(['page' => 1]);
// 若 page 參數不存在才設定 page=1
$uri = $uri->replaceQuery(['page' => 1]);
// 取代所有查詢參數為 page=1
$uri = $uri->pushOntoQuery('filter', ['active', 'pending']);
// 將 filter 參數 push 多個值
$uri = $uri->withoutQuery(['page']);
// 移除 page 查詢參數
// 產生重導回應
return $uri->redirect(); 
// 產生一個重導回應，導向此 URI
```

