# *Laravel Collection 筆記*

---

## 1. **Collection 介紹與特性**

- *Collection*（集合）是 Laravel 提供的 `Illuminate\Support\Collection` 類別，讓你用更直覺、鏈式、函式式的方式操作陣列資料。
- 支援 `map、filter、reduce、reject、each、pluck、groupBy` 等大量常用方法。
- 幾乎所有 *Eloquent* 查詢結果都會 __自動回傳 Collection 實例__。
- Collection 方法大多 **不可變**（immutable），__每次操作都回傳新的 Collection，不會改變原本資料__。
- 支援 *鏈式操作* ，讓資料處理更簡潔。

<!-- 集合（Collection）和陣列不是完全相對的概念。
     Laravel 的 Collection 是「包裝陣列」的物件，
     讓你可以用物件導向方式操作資料，
     但底層還是用陣列儲存資料，
     兩者可以互相轉換、混用。 -->

---

*範例：鏈式操作與不可變性*

```php
$collection = collect(['Taylor', 'Abigail', null]) // 建立一個 Collection，內容是 'Taylor'、'Abigail' 和 null
    ->map(function (?string $name) {               // 對每個元素做 map 映射，$name 可能是 string 或 null
        return strtoupper($name);                  // 把 $name 轉成大寫（null 會變成 null，不會報錯）
    })
    ->reject(function ($name) {                    // 過濾掉不需要的元素
        return empty($name);                       // 如果 $name 是空（null、''），就會被移除
    });
// 結果：['TAYLOR', 'ABIGAIL']
```

---

### *關於 `?string` 語法說明*

`?string` 是 PHP 7.1+ 引入的 **可空類型（Nullable Types）** 語法。

---

#### **基本概念**

- `string` = *必須* 是字串類型
- `?string` = 可以是字串類型，*也可以是 `null`*

---

#### **語法對比**

```php
// PHP 7.1 之前（舊寫法）
function processName($name) {
    // $name 可以是任何類型
}

// PHP 7.1+ 新寫法
function processName(?string $name) {
    // $name 可以是 string 或 null
}

function processName(string $name) {
    // $name 必須是 string，不能是 null
}
```

---

#### **為什麼需要 `?string`？**

```php
// 如果寫成 string（沒有 ?）
$collection = collect(['Taylor', 'Abigail', null])
    ->map(function (string $name) {  // ❌ 會報錯！
        return strtoupper($name);
    });
// 錯誤：TypeError: Argument 1 passed to closure must be of type string, null given

// 正確寫法：加上 ?
$collection = collect(['Taylor', 'Abigail', null])
    ->map(function (?string $name) {  // ✅ 正確！
        return strtoupper($name);
    });
```

---

#### **其他可空類型範例**

```php
function example(?int $number) { }      // 可以是 int 或 null
function example(?array $data) { }      // 可以是 array 或 null
function example(?bool $flag) { }       // 可以是 bool 或 null
function example(?User $user) { }       // 可以是 User 物件或 null
```

---

#### **白話解釋**

- `?string` = 「_這個參數可能是字串，也可能是空值_」
- 就像說「這個盒子可能裝著字串，也可能是空的」
- 這樣設計讓程式碼更安全，明確告訴 PHP 和開發者這個參數可能包含 `null` 值

---

## **不可變性（Immutability）說明**

Laravel 的 Collection 操作遵循 *不可變性原則* ，每次操作都會 *回傳新的 Collection 物件* ，`原始資料保持不變`。

---

### *Collection 的不可變性範例*

```php
// 原始陣列
$original = ['Taylor', 'Abigail', null];

// 不可變操作 - 每次操作都回傳新的 Collection
$collection = collect($original)
    ->map(function (?string $name) {
        return strtoupper($name);
    })
    ->reject(function ($name) {
        return empty($name);
    });

// 原始陣列 $original 保持不變
// 結果：['TAYLOR', 'ABIGAIL']
```

---

### *不可變性的特點*

1. __原始資料不變__：操作後原始資料`保持不變`
2. __回傳新物件__：每次操作都回傳`新的 Collection 物件`
3. __鏈式操作__：可以`安全地`進行鏈式呼叫
4. __函數式風格__：符合`函數式程式設計`原則

---

### *與可變性的對比*

```php
// 可變性（不推薦）
$array = ['a', 'b', 'c'];
array_push($array, 'd'); // 直接修改原始陣列

// 不可變性（推薦）
$array = ['a', 'b', 'c'];
$newArray = collect($array)->push('d'); // 回傳新的 Collection
```

---

### *實際應用範例*

```php
// 原始資料
$users = [
    ['name' => 'Taylor', 'age' => 25],
    ['name' => 'Abigail', 'age' => 30],
    ['name' => 'John', 'age' => 35]
];

// 不可變操作鏈
$result = collect($users)
        ->filter(fn($user) => $user['age'] > 25) // 新集合
        ->map(fn($user) => $user['name'])        // 新集合
        ->sort()                                 // 新集合
        ->values();                              // 新集合

// 原始 $users 陣列保持不變
// 結果：['Abigail', 'John']

```

---

## 2. **建立 Collection 的方式**

- 使用 `collect()` 輔助函式：

```php
$collection = collect([1, 2, 3]);
```

---

- 使用 `Collection::make()`：

```php
use Illuminate\Support\Collection;
$collection = Collection::make(['a', 'b', 'c']);
```

---

- 使用 `Collection::fromJson()`：

```php
$json = '[1,2,3]';
$collection = Collection::fromJson($json);
```

---

- *Eloquent 查詢結果* 自動回傳 Collection：

```php
$users = User::where('active', 1)->get(); // $users 是 Collection
```

---

## 3. **Collection 常用方法與實作**

- *map*：對每個元素執行 callback，回傳新 Collection

```php
$upper = collect(['a', 'b'])
       ->map(function ($v) { return strtoupper($v); });
// ['A', 'B']
```

---

- *filter*：只保留 callback 回傳 `true` 的元素

```php
$filtered = collect([1, 2, 3, 4])
          ->filter(function ($v) { return $v > 2; });
// [3, 4]
```

---

- *reject*：只保留 callback 回傳 `false` 的元素

```php
$rejected = collect([1, 2, 3, 4])
          ->reject(function ($v) { return $v > 2; });
// [1, 2]
```

---

- *each*：對每個元素執行 callback，`不回傳新 Collection`

```php
$collection = collect([1, 2, 3]);
$result = $collection->each(function ($v) { echo $v; });
// $result 跟 $collection 是同一個物件，不是新的集合
```

---

- *reduce*：累加/聚合所有元素

```php
$sum = collect([1, 2, 3])
     ->reduce(function ($carry, $item) { return $carry + $item; }, 0);
// 6
```

---

- *pluck*：取出所有元素的某個 key

```php
$names = collect([
    ['name' => 'A'],
    ['name' => 'B'],
])->pluck('name');
// ['A', 'B']
```

---

- *groupBy*：依 key 分組

```php
$grouped = collect([
    ['type' => 'fruit', 'name' => 'apple'],
    ['type' => 'fruit', 'name' => 'banana'],
    ['type' => 'vegetable', 'name' => 'carrot'],
])->groupBy('type');
// 'fruit' => [...], 'vegetable' => [...]
```

---

## 4. **Collection Macro 擴充機制**

- Collection 支援「_Macroable_」特性，可 __在執行時`動態`新增自訂方法__。
- 用法：呼叫 `Collection::macro('方法名', function() { ... })`。
- 建議在 `ServiceProvider` 的 `boot` 方法中註冊 macro。

---

*範例：新增 `toUpper` 方法*

```php
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

Collection::macro('toUpper', function () {
    return $this->map(function (string $value) {
        return Str::upper($value);
    });
});

$collection = collect(['first', 'second']);
$upper = $collection->toUpper();
// ['FIRST', 'SECOND']
```

---

## 5. **Macro 支援參數**

- Macro function *可接受額外參數，並可在內部使用 use 傳遞*。

---

### *Collection Macro：`toLocale` 批次翻譯*

```php
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

// 定義一個 Collection 的自訂方法 toLocale
Collection::macro('toLocale', function (string $locale) {
    // $this 代表目前的 Collection 實例
    // 這裡用 map 逐一處理 Collection 裡的每個值
    return $this->map(function (string $value) use ($locale) {
        // 用 Laravel 的 Lang::get 依指定語系翻譯每個字串
        // $value：要取得的語言鍵（如 'messages.welcome'）。
        // []：替換用的參數陣列（如 ['name' => 'Alice']），預設空陣列。
        // $locale：指定語系（如 'en'、'zh-TW'），可選，預設用目前語系。
        return Lang::get($value, [], $locale);
    });
});

// 建立一個 Collection，內容是 ['first', 'second']
$collection = collect(['first', 'second']);

// 呼叫自訂的 toLocale 方法，將每個字串翻譯成西班牙語（'es'）
$translated = $collection->toLocale('es');
```

---

#### **白話說明**

- 這段程式碼自訂了一個 Collection 的方法 `toLocale`，可以讓你直接把一個字串陣列「_批次翻譯_」成指定語系。
- 用法就像原生 Collection 方法一樣，可以鏈式呼叫。
- 適合用於多語系資料處理，例如 __批次將多個 key 轉成不同語言的顯示文字__。

---

#### **應用場景**

- _多語系選單、批次翻譯介面文字、API 回傳多語系資料_ 等。

---

## 6. **小結**

- `Collection` 讓陣列操作更直覺、鏈式、可讀性高
- 幾乎所有 _Eloquent_ 查詢都回傳 `Collection`
- 支援 _Macro 動態擴充_，團隊可自訂常用方法
- 建議將 `Macro` 註冊於 `ServiceProvider boot` 方法