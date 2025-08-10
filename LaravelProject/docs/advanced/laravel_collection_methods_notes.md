# *Laravel Collection 方法庫*

---

## **方法快速索引**

- *after*：取得`指定元素之後`的項目
- *all*：取得`原始陣列`
- *average / avg*：計算`平均值`

- *before*：取得`指定元素之前`的項目

- *chunk*：`分割`集合為多個小集合
- *chunkWhile*：依`條件分割`集合
- *collapse*：壓平成`一維集合`
- *collapseWithKeys*：`壓平並保留 key`
- *collect*：轉為 Collection 實例
- *combine*：組成`關聯陣列`（key/value）
- *concat*：`合併集合`（重新編號）
- *contains*：判斷是否`包含指定項目`
- *containsOneItem*：`是否`只包含一個項目
- *containsStrict*：`嚴格比對`是否包含
- *count*：計算數量
- *countBy*：依值或條件`分組計數`
- *crossJoin*：笛卡兒積（所有排列組合）

- *dd*：dump 並終止程式
- *diff*：`取差集`（原集合有、對方沒有）
- *diffAssoc*：`取差集`（含 key 比對）
- *diffAssocUsing*：自訂 key 比對`取差集`
- *diffKeys*：只比對 key `取差集`
- *doesntContain*：判斷`是否不包含`
- *dot*：將多維陣列轉為 `dot notation`
- *dump*：dump 集合內容
- *duplicates*：找出`重複值`
- *duplicatesStrict*：`嚴格比對`重複值

- *each*：遍歷每個項目
- *eachSpread*：展開`每個項目傳入 callback`
- *ensure*：確保集合`有指定 key`
- *every*：所有項目`皆符合條件`
- *except*：`排除`指定 key

- *filter*：依條件`過濾`
- *first*：取得`第一個項目`
- *firstOrFail*：第一個項目，找不到拋例外
- *firstWhere*：`第一個符合條件的項目`
- *flatMap*：`展開並映射`
- *flatten*：壓平成`一維`
- *flip*：key/value `互換`
- *forget*：`移除`指定 key
- *forPage*：分頁
- *fromJson*：`從 JSON 產生集合`

- *get*：`取得`指定 key 的值
- *groupBy*：依`條件分組`

- *has*：是否`有`指定 key
- *hasAny*：是否`有任一`指定 key

- *implode*：`合併`為字串
- *intersect*：`取交集`
- *intersectUsing*：自訂比對`取交集`
- *intersectAssoc*：含 key 比對`取交集`
- *intersectAssocUsing*：自訂 key 比對`取交集`
- *intersectByKeys*：只比對 key `取交集`
- *isEmpty*：是否為空
- *isNotEmpty*：是否不為空

- *join*：`合併為字串`（可自訂分隔）

- *keyBy*：依指定 key `重新索引`
- *keys*：取得所有 key

- *last*：取得`最後一個項目`
- *lazy*：轉為 `LazyCollection`

- *macro*：註冊`自訂方法`
- *make*：建立新集合
- *map*：`映射`每個項目
- *mapInto*：`轉型為指定類別`
- *mapSpread*：展開`傳入 callback`
- *mapToGroups*：`分組映射`
- *mapWithKeys*：映`射並指定 key`
- *max*：最大值
- *median*：中位數
- *merge*：合併集合（保留 key）
- *mergeRecursive*：`遞迴合併`
- *min*：最小值
- *mode*：眾數
- *multiply*：每個值乘上指定數

- *nth*：`每 n 個取一個`

- *only*：`只`保留指定 key

- *pad*：`補滿長度`
- *partition*：依條件`分割為兩組`
- *percentage*：計算百分比
- *pipe*：`傳入 callback 處理`
- *pipeInto*：`傳入指定類別`
- *pipeThrough*：依序`傳入多個 callback`
- *pluck*：取出指定 key 的值
- *pop*：`移除並回傳最後一個`
- *prepend*：`前面插入`一個值
- *pull*：`取出並移除指定 key`
- *push*：`加入一個值到尾端`
- *put*：`設定指定 key 的值`

- *random*：隨機取出一個或多個
- *range*：產生`範圍集合`
- *reduce*：累加處理
- *reduceSpread*：累加並展開回傳
- *reject*：`排除`不符合條件
- *replace*：取代值
- *replaceRecursive*：遞迴取代
- *reverse*：反轉順序

- *search*：`搜尋值回傳 key`
- *select*：`只保留符合條件`
- *shift*：`移除並回傳第一個`
- *shuffle*：`隨機排序`
- *skip*：`跳過前 n 個`
- *skipUntil*：`直到條件成立才開始`
- *skipWhile*：`條件成立時跳過`
- *slice*：`切片`
- *sliding*：`滑動視窗分組`
- *sole*：`唯一符合條件的項目`
- *some*：`是否有任一`符合條件
- *sort*：排序
- *sortBy*：依指定條件排序
- *sortByDesc*：依指定條件反向排序
- *sortDesc*：反向排序
- *sortKeys*：依 key 排序
- *sortKeysDesc*：key 反向排序
- *sortKeysUsing*：自訂 key 排序
- *splice*：`移除並插入`
- *split*：> 為多組
- *splitIn*：平均分割為 n 組
- *sum*：加總

- *take*：取前 n 個
- *takeUntil*：直到條件成立前取
- *takeWhile*：條件成立時取
- *tap*：`操作後回傳自身`
- *times*：重複 n 次
- *toArray*：轉為陣列
- *toJson*：轉為 JSON
- *transform*：`轉換每個項目(原陣列)`

- *undot*：dot notation 轉回多維
- *union*：`合併`（保留 key）
- *unique*：`去除重複值`
- *uniqueStrict*：嚴格比對去重
- *unless*：條件`不成立`時執行
- *unlessEmpty*：`非空時執行`
- *unlessNotEmpty*：空時執行
- *unwrap*：`取出包裝內容`

- *value*：取得指定 key 的值
- *values*：重編索引

- *when*：條件成立時執行
- *whenEmpty*：空時執行
- *whenNotEmpty*：非空時執行
- *where*：條件過濾
- *whereStrict*：嚴格條件過濾
- *whereBetween*：介於範圍
- *whereIn*：在指定陣列內
- *whereInStrict*：嚴格比對在陣列內
- *whereInstanceOf*：`指定類別實例`
- *whereNotBetween*：不在範圍內
- *whereNotIn*：不在指定陣列內
- *whereNotInStrict*：嚴格比對不在陣列內
- *whereNotNull*：不為 null
- *whereNull*：為 null
- *wrap*：`包裝`成集合

- *zip*：`壓縮`多個集合

---

## **常見相對方法對照表**

| 方法A           | 方法B             | 關係說明                                   |
|----------------|------------------|------------------------------------------|
| *filter*     | *reject*       | filter 保留符合條件，reject 移除符合條件      |
| *contains*   | *doesntContain*| 是否包含 / 是否不包含                      |
| *unique*     | *duplicates*   | 取唯一值 / 取重複值                        |
| *uniqueStrict*| *duplicatesStrict*| 嚴格唯一 / 嚴格重複                    |
| *where*      | *whereNotIn*   | where 保留符合條件，whereNotIn 移除指定值    |
| *whereIn*    | *whereNotIn*   | 在指定陣列內 / 不在指定陣列內                |
| *whereNull*  | *whereNotNull* | 為 null / 不為 null                        |
| *skip*       | *take*         | 跳過前 n 個 / 取前 n 個                     |
| *skipUntil*  | *takeUntil*    | 跳到條件成立 / 取到條件成立                  |
| *skipWhile*  | *takeWhile*    | 跳到條件不成立 / 取到條件不成立              |
| *first*      | *last*         | 取第一個 / 取最後一個                       |
| *min*        | *max*          | 最小值 / 最大值                             |
| *sortBy*     | *sortByDesc*   | 正向排序 / 反向排序                         |
| *sortKeys*   | *sortKeysDesc* | key 正向排序 / key 反向排序                 |
| *merge*      | *union*        | 合併（key 相同會覆蓋）/ 合併（key 相同保留原值）|
| *only*       | *except*       | 只保留指定 key / 排除指定 key               |
| *every*      | *some*         | 全部符合 / 任一符合                         |
| *isEmpty*    | *isNotEmpty*   | 是否為空 / 是否不為空                       |
| *when*       | *unless*       | 條件成立執行 / 條件不成立執行                |
| *whenEmpty*  | *whenNotEmpty* | 為空時執行 / 不為空時執行                   |
| *pluck*      | *pull*         | 取出指定 key 值 / 取出並移除指定 key         |
| *shift*      | *pop*          | 移除並回傳第一個 / 移除並回傳最後一個        |
| *flatten*    | *undot*        | 壓平成一維 / dot notation 還原多維           |
| *diff*       | *intersect*    | 差集 / 交集                                 |
| *diffAssoc*  | *intersectAssoc*| key+value 差集 / key+value 交集           |
| *diffKeys*   | *intersectByKeys*| key 差集 / key 交集                      |
| *replace*    | *replaceRecursive*| 一層取代 / 遞迴取代                      |
| *chunk*      | *split*        | 固定大小分組 / 固定組數分組                  |
| *map*        | *transform*    | 產生新集合 / 直接改變原集合                  |

- 補充：有些方法沒有完全對稱的「反義」方法（如 all、containsOneItem），但大多數常用方法都有`設計成對`。這份表格有助於理解 Collection API 的設計，也方便記憶和查找。

---


## **after()**
- *說明*： 回傳指`定元素之後`的項目，若找不到或已是最後一個則回傳 null。
          是比對「值」或用條件判斷，不是單純用索引。
- *語法*：`$collection->after($value, $strict = false)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
$collection->after(3); // 4
$collection->after(5); // null
// 嚴格比對：
collect([2, 4, 6, 8])->after('4', strict: true); // null
// 傳入 closure：
collect([2, 4, 6, 8])->after(function ($item, $key) {
    // 這個 closure 會找出第一個大於 5 的元素（也就是 6）
    return $item > 5;
}); // 8
```
-   這裡回傳 8，不是 6，因為 `after()` 的設計是：
    1. 先找到條件成立的元素（這裡是 6）
    2. 然後回傳『它之後的那個元素』（這裡是 8）
    3. 不是回傳自己！
    4. 如果已經是最後一個，則回傳 null
---

## **all()**
- *說明*：取得 Collection 內部的原始陣列。
- *語法*：`$collection->all()`
- *範例*：
```php
collect([1, 2, 3])->all(); // [1, 2, 3]
```

---

## **average() / avg()**
- *說明*：計算平均值。`average()` 是 `avg()` 的別名。
- *語法*：`$collection->avg($key = null)`
- *範例*：
```php
collect([["foo" => 10], ["foo" => 10], ["foo" => 20], ["foo" => 40]])->avg('foo'); // 20
collect([1, 1, 2, 4])->avg(); // 2
```

---

## **before()*
- *說明*：回傳指定元素之前`的項目，若找不到或已是第一個則回傳 null。
- *語法*：`$collection->before($value, $strict = false)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
$collection->before(3); // 2
$collection->before(1); // null
collect([2, 4, 6, 8])->before('4', strict: true); // null
collect([2, 4, 6, 8])->before(function ($item, $key) {
    return $item > 5;
}); // 4
```

---

## **chunk()**
- *說明*：將集合分割成多個`指定大小`的小集合。
- *語法*：`$collection->chunk($size)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5, 6, 7]);
$chunks = $collection->chunk(4);
$chunks->all(); // [[1, 2, 3, 4], [5, 6, 7]]
```
- **應用**：常用於 view 分組顯示，例如 Bootstrap 格線。

---

## **chunkWhile()**
- *說明*：根據 `callback 條件分割集合，直到條件不符才分下一組。
- *語法*：`$collection->chunkWhile(Closure $callback)`
- *範例*：
```php
$collection = collect(str_split('AABBCCCD'));
// str_split('AABBCCCD') 會把字串拆成陣列：['A', 'A', 'B', 'B', 'C', 'C', 'C', 'D']
// 所以 $collection 內容是：['A', 'A', 'B', 'B', 'C', 'C', 'C', 'D']

$chunks = $collection->chunkWhile(function ($value, $key, $chunk) {
    // $value：目前這個元素的值（例如 'A'）
    // $key：目前這個元素的索引（例如 0, 1, 2...）
    // $chunk：目前正在累積的這一組（chunk），是一個 Collection
    // $chunk 是一個動態累積的集合，隨著 chunkWhile 方法的執行，會逐步累積符合條件的元素。
    // 每次執行回呼函式（callback）時，$chunk 代表目前正在處理的那一組（chunk）。
    return $value === $chunk->last();
    // 判斷條件：如果「目前的值」跟「這一組最後一個值」一樣，就繼續放進同一組
    // 如果不一樣，就分到下一組
});

$chunks->all(); // [['A', 'A'], ['B', 'B'], ['C', 'C', 'C'], ['D']]
// 結果是：每一組都是「連續相同字母」分在一起。
```
- 原理說明：
    - `chunkWhile()` 會依序檢查每個元素，根據你給的 callback 條件，決定要不要繼續把元素放進同一組。
    - 只要 callback 回傳 `true`，元素就繼續放進目前這一組；回傳 `false`，就開新的一組。
    - 這個範例的條件是「*跟目前這組的最後一個一樣就同組*」，所以會把連續一樣的字母分在一起。

- 流程：
    1. 第一個 'A' → 新的一組 ['A']
    2. 第二個 'A' → 跟上一個一樣，放同一組 ['A', 'A']
    3. 第一個 'B' → 跟上一組最後一個 'A' 不一樣，開新組 ['B']
    4. 第二個 'B' → 跟上一個 'B' 一樣，放同一組 ['B', 'B']
    5. 三個 'C' → 依序都跟上一個 'C' 一樣，全部同組 ['C', 'C', 'C']
    6. 'D' → 跟上一個 'C' 不一樣，開新組 ['D']

- `chunkWhile()` 適合用來「*依連續條件分組*」，callback 可以自訂各種分組邏輯，非常彈性。

---

## **collapse()**
- *說明*：將多維陣列或集合`壓平成一維集合`。
- *語法*：`$collection->collapse()`
- *範例*：
```php
$collection = collect([
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
]);
$collapsed = $collection->collapse();
$collapsed->all(); // [1, 2, 3, 4, 5, 6, 7, 8, 9]
```

---

## **collapseWithKeys()**
- *說明*：壓平集合並保留原本的 key。
- *語法*：`$collection->collapseWithKeys()`
- *範例*：
```php
$collection = collect([
    ['first'  => collect([1, 2, 3])],
    ['second' => [4, 5, 6]],
    ['third'  => collect([7, 8, 9])]
]);
$collapsed = $collection->collapseWithKeys();
$collapsed->all();
// [
//     'first'  => [1, 2, 3],
//     'second' => [4, 5, 6],
//     'third'  => [7, 8, 9],
// ]
```

---

## **collect()**
- *說明*：回傳一個新的 Collection 實例，內容與原集合相同。
- *語法*：`$collection->collect()`
- *範例*：
```php
$collectionA = collect([1, 2, 3]);
$collectionB = $collectionA->collect();
$collectionB->all(); // [1, 2, 3]
```
- *補充*：常用於將 `LazyCollection` 轉為一般 Collection。

---

## **combine()**
- *說明*：將集合的值作為 key，另一個陣列的值作為 value，組成關聯陣列。
  - 注意：`combine()` 不是單純把兩個陣列合併在一起，而是「一個當 key，一個當 value」配對成新的關聯陣列。
  - 你可以想像成「欄位名稱」配「欄位值」，像表格的欄位與資料。
  - 跟 `array_merge()` 不同，`array_merge()` 只是把兩個陣列接在一起，combine() 則是 key-value 配對。
- *語法*：`$collection->combine($array)`
- *範例*：
```php
$collection = collect(['name', 'age']);
$combined = $collection->combine(['George', 29]);
$combined->all(); // ['name' => 'George', 'age' => 29]
// array_merge(['name', 'age'], ['George', 29]) 會得到 ['name', 'age', 'George', 29]
// combine() 則是 ['name' => 'George', 'age' => 29]
```

---

## **concat()**：英文「concatenate」，意思是「串接」、「連接在一起」。
- *說明*：將指定陣列或集合的值`加到原集合尾端`（會重新編號 key）。
- *語法*：`$collection->concat($items)`
- *範例*：
```php
$collection = collect(['John Doe']);
$concatenated = $collection->concat(['Jane Doe'])->concat(['name' => 'Johnny Doe']);
$concatenated->all(); // ['John Doe', 'Jane Doe', 'Johnny Doe']
```
- *補充*：若要保留 key，請用 merge 方法。

---

## **contains()**
- *說明*：判斷集合是否`包含指定項目`，可傳入值、key/value、或 closure。
- *語法*：`$collection->contains($value)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
$collection->contains(function ($value, $key) {
    return $value > 5;
}); // false

$collection = collect(['name' => 'Desk', 'price' => 100]);
$collection->contains('Desk'); // true
$collection->contains('New York'); // false

$collection = collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 100],
]);
$collection->contains('product', 'Bookcase'); // false
```
- *補充*：預設為「寬鬆比對」，嚴格比對請用 `containsStrict`。

---

## **containsOneItem()**
- *說明*：判斷`集合是否只包含`一個項目。
- *語法*：`$collection->containsOneItem($callback = null)`
- *範例*：
```php
collect([])->containsOneItem(); // false
collect(['1'])->containsOneItem(); // true
collect(['1', '2'])->containsOneItem(); // false
collect([1, 2, 3])->containsOneItem(fn ($item) => $item === 2); // true
```
 1. `fn ($item) => $item === 2` 是 PHP 7.4+ 的箭頭函式，
    等同於 `function ($item) { return $item === 2; }`
 2. 這個 callback 會被套用到集合的每個元素：
    - `1 === 2 → false`
    - `2 === 2 → true`
    - `3 === 2 → false`
 3. 只有「2」這個元素讓 callback 回傳 true
 4. `containsOneItem()` 會判斷「有且只有一個」元素讓 callback 回傳 true，所以這裡回傳 true
 5. 如果有兩個 2，則會回傳 false

- 生活化比喻：
    - 就像你在一堆號碼牌裡找「只有一張是 2」這件事，這時候答案是 true。

---

## **containsStrict()**
- *說明*：判斷集合是否「嚴格包含」指定的值（用 === 比較，值與型別都要相同）。
- *語法*：`$collection->containsStrict($value)`
- *範例*：
```php
collect([1, 2, 3])->containsStrict(2); // true
// ↑ 2（數字）存在於集合中，型別也相同，回傳 true

collect([1, 2, 3])->containsStrict('2'); // false
// ↑ '2'（字串）雖然值一樣，但型別不同，=== 比較會回傳 false

collect(['id' => 1, 'name' => 'Vincent'])->containsStrict('Vincent'); // true
// ↑ 'Vincent' 這個字串存在於集合中，型別也相同，回傳 true

collect(['id' => 1, 'name' => 'Vincent'])->containsStrict('vincent'); // false
// ↑ 'vincent'（小寫）與 'Vincent'（大寫）不同，=== 比較會回傳 false

// 物件比對：
$obj = new stdClass();
$obj->a = 1;
collect([$obj])->containsStrict($obj); // true
// ↑ 集合裡的物件和 $obj 是同一個實體，回傳 true

collect([$obj])->containsStrict(new stdClass()); // false
// ↑ 雖然內容一樣，但不是同一個物件實體，=== 比較會回傳 false
```
- 原理說明：
    - `containsStrict()` 會用 === 來比對集合裡的每個元素和你給的值，
    - 只有「*值與型別都相同*」才會回傳 true。
    - 常見用於需要精確比對型別的情境，例如區分數字 2 和字串 '2'。

- 生活化比喻：
    - 就像你在找一把鑰匙，不只要形狀一樣，連材質、顏色都要完全一樣才算數。

---

## **count()**
- *說明*：回傳集合項目`數量`。
- *語法*：`$collection->count()`
- *範例*：
```php
$collection = collect([1, 2, 3, 4]);
$collection->count(); // 4
```

---

## **countBy()**
- *說明*：統計集合中各值`出現次數`，可自訂分組依據。
- *語法*：`$collection->countBy($callback = null)`
- *範例*：
```php
$collection = collect([1, 2, 2, 2, 3]);
$counted = $collection->countBy();
$counted->all(); // [1 => 1, 2 => 3, 3 => 1]
// ↑ 逐行註解：
// 1. 建立一個集合 [1, 2, 2, 2, 3]
// 2. 呼叫 countBy()，沒給 callback，會直接統計每個值出現幾次
// 3. 結果是 [1 => 1, 2 => 3, 3 => 1]，代表 1 出現 1 次，2 出現 3 次，3 出現 1 次

$collection = collect(['alice@gmail.com', 'bob@yahoo.com', 'carlos@gmail.com']);
$counted = $collection->countBy(function ($email) {
    return substr(strrchr($email, '@'), 1);
});
$counted->all(); // ['gmail.com' => 2, 'yahoo.com' => 1]
```
1. 建立一個集合 `['alice@gmail.com', 'bob@yahoo.com', 'carlos@gmail.com']`
2. 呼叫 `countBy()`，傳入 callback，callback 會取 email 的網域（@ 後面的字串）
   - `strrchr($email, '@')` 會取得「最後一個 @ 之後到結尾」的字串，例如 '@gmail.com'
     → `strrchr('alice@gmail.com'`, '@') 結果是 '@gmail.com'
   - `substr(..., 1)` 會去掉 @，只留下 'gmail.com' 或 'yahoo.com'
     → `substr('@gmail.com', 1) `結果是 'gmail.com'
     - `substr()` 的語法：substr($string, $start, $length = null)
     - $start 是「起始索引」，從 0 開始。substr($str, 1) 代表「從索引 1（第二個字元）開始取到結尾」，所以會去掉第 0 個字元。
     - 例如：
       | 字元   | @ | g | m | a | i | l | . | c | o | m |
       |--------|---|---|---|---|---|---|---|---|---|---|
       | 索引   | 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 |
       substr('@gmail.com', 1) 結果是 'gmail.com'
     - substr 的英文原意是「*sub-string*」，意思是「子字串」或「截取字串」
     - [語源補充] substr 是 substring（子字串）的縮寫：sub-（子、下層）+ string（字串），
       所以 substr() 這個函式就是「從原本字串中擷取一部分」的意思，
       這個命名在各種程式語言都很常見，直觀又好記。


-[補充] `strrchr()` 是 PHP 內建字串函式，會回傳「*最後一次出現指定字元」到結尾的所有內容*。
    - 常用來切割 email 網域、檔案副檔名等。
    - 原理說明：
        - 沒給 callback 時，`countBy()` 直接統計每個值的出現次數。
        - 有給 callback 時，會用 `callback 的回傳值分組`，統計每組的數量。
        - 結果都是一個關聯陣列，key 是值（或分組依據），value 是出現次數。

生活化比喻：
    - 就像你統計班上每個人穿什麼顏色的衣服（callback 回傳顏色），最後得到「紅 5 人、藍 8 人、綠 2 人」這種分組統計。

---

## **crossJoin()（笛卡爾積）**
- *說明*：產生集合與其他集合（或陣列）的「  所有可能配對組合    」，也就是數學上的笛卡爾積（Cartesian Product）。
- *數學記號*：A × B
- *語法*：`$collection->crossJoin($array1, $array2, ...)`
- *範例*：
```php
$collection = collect([1, 2]);
$result = $collection->crossJoin(['a', 'b']);
```
$result->all() 結果：
[
  [1, 'a'],
  [1, 'b'],
  [2, 'a'],
  [2, 'b']
]
- 逐行註解：
    1. $collection 是 [1, 2]
    2. crossJoin(['a', 'b']) 會把 [1, 2] 和 ['a', 'b'] 做所有可能的配對
    3. 1 配 a、1 配 b、2 配 a、2 配 b
    4. 結果是所有「一個來自第一組、一個來自第二組」的組合

- 生活化比喻：
    就像你有兩件上衣（白、黑），三條褲子（牛仔、卡其、運動），
    所有「上衣+褲子」的穿搭組合，就是這兩個集合的笛卡爾積。

- [補充] `c`rossJoin()` 可支援多個集合，會產生多維配對組合。
    - 例如：
        - collect([1, 2])->crossJoin(['a', 'b'], ['I', 'II'])
        - 結果：
        [
        [1, 'a', 'I'], [1, 'a', 'II'],
        [1, 'b', 'I'], [1, 'b', 'II'],
        [2, 'a', 'I'], [2, 'a', 'II'],
        [2, 'b', 'I'], [2, 'b', 'II']
        ]

---

## **dd()**
- *說明*：dump 集合內容並終止程式（等同於 dd()）。
- *語法*：`$collection->dd()`
- *範例*：
```php
$collection = collect(['John Doe', 'Jane Doe']);
$collection->dd();
// 輸出：
// array:2 [
//   0 => "John Doe"
//   1 => "Jane Doe"
// ]
```
- *補充*：若只想 dump 不終止，請用 dump()。

---

## **diff()**
- *說明*：回傳只存在於原集合、但不在給定集合的元素（差集運算）。
- *語法*：`$collection->diff($otherArrayOrCollection)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
$diffed = $collection->diff([2, 4, 6]);
$diffed->all(); // [1, 3, 5]
// ↑ 逐行註解：
// 1. 建立一個集合 [1, 2, 3, 4, 5]
// 2. 呼叫 diff([2, 4, 6])，意思是「把 2、4、6 當作要排除的元素」
// 3. 結果是 [1, 3, 5]，因為 2 和 4 在給定陣列裡，所以被排除，剩下 1、3、5
```
- 原理說明：
    - `diff()` 會把原集合裡「有出現在給定集合」的元素排除掉，只留下「自己有、對方沒有」的元素。
    比對方式是寬鬆比對（==），只比值，不比型別。

- 生活化比喻：
    - 就像你有一份名單，想知道「有哪些人是我有、對方沒有的」，這時候就用 diff()。

補充：
- 如果要嚴格比對型別，可用 `diffStrict()`
- 如果要比對 key-value，可用 `diffAssoc()` 或 `diffKeys()`

---

## **diffAssoc()**
- *說明*：根據 key 與 value 比較，回傳**原集合中**`不存在於給定集合的鍵值對`。
- *語法*：`$collection->diffAssoc($items)`
- *範例*：
```php
$collection = collect([
    'color' => 'orange',
    'type' => 'fruit',
    'remain' => 6,
]);
$diff = $collection->diffAssoc([
    'color' => 'yellow',
    'type' => 'fruit',
    'remain' => 3,
    'used' => 6,
]);
$diff->all(); // ['color' => 'orange', 'remain' => 6]
```

---

## **diffAssocUsing()**
- *說明*：同 diffAssoc，但可自訂 key 比對邏輯（callback）。
- *語法*：`$collection->diffAssocUsing($items, $callback)`
- *範例*：
```php
$collection = collect([
    'color' => 'orange',
    'type' => 'fruit',
    'remain' => 6,
]);
$diff = $collection->diffAssocUsing([
    'Color' => 'yellow',
    'Type' => 'fruit',
    'Remain' => 3,
], 'strnatcasecmp');
// 裡用 PHP 內建的 strnatcasecmp（不分大小寫的字串自然排序）
$diff->all(); // ['color' => 'orange', 'remain' => 6]
```
- 這裡的 callback 是「*自訂比對邏輯*」的函式，會拿到兩個值（通常是 key 或 value），你必須回傳一個整數：
   - **小於 0**：代表第一個值小於第二個值
   - **等於 0**：代表兩個值相等
   - **大於 0**：代表第一個值大於第二個值
 - 這種寫法跟 PHP 的 `usort()`、`array_diff_uassoc()` 等自訂比對函式一樣
 - 通常可以用 PHP 7+ 的「`太空船運算子`」<=>，自動回傳 -1、0、1，例如：`return $a <=> $b`;
 - Laravel 這類方法底層其實就是呼叫 PHP 內建的 `array_diff_uassoc` 來做運算

- *補充*：callback 必須回傳整數（<0, =0, >0），底層用 PHP 的 `array_diff_uassoc`。

---

## **diffKeys()**
- *說明*：只根據 key 比較，回傳 **原集合中** `不存在於給定集合的鍵值對`。
- *語法*：`$collection->diffKeys($items)`
- *範例*：
```php
$collection = collect([
    'one' => 10,
    'two' => 20,
    'three' => 30,
    'four' => 40,
    'five' => 50,
]);
$diff = $collection->diffKeys([
    'two' => 2,
    'four' => 4,
    'six' => 6,
    'eight' => 8,
]);
$diff->all(); // ['one' => 10, 'three' => 30, 'five' => 50]
```

---

## **doesntContain()**
- *說明*：判斷集合`是否`「**不包含**」指定項目，可傳值、key/value、closure。
- *語法*：`$collection->doesntContain($value)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
$collection->doesntContain(function ($value, $key) {
    return $value < 5;
}); // false

$collection = collect(['name' => 'Desk', 'price' => 100]);
$collection->doesntContain('Table'); // true
$collection->doesntContain('Desk'); // false

$collection = collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 100],
]);
$collection->doesntContain('product', 'Bookcase'); // true
```
- *補充*：預設為「`寬鬆比對`」。

---

## **dot()**
- *說明*：將多維集合`壓平成一維`，key 以 dot notation 表示層級。
- *語法*：`$collection->dot()`
- *範例*：
```php
$collection = collect(['products' => ['desk' => ['price' => 100]]]);
$flattened = $collection->dot();
$flattened->all(); // ['products.desk.price' => 100]
```

---

## **dump()**
- *說明*：dump 集合內容（不會終止程式）。
- *語法*：`$collection->dump()`
- *範例*：
```php
$collection = collect(['John Doe', 'Jane Doe']);
$collection->dump();
// array:2 [
//   0 => "John Doe"
//   1 => "Jane Doe"
// ]
```
- *補充*：若要 dump 並終止，請用 dd()。

---

## **duplicates()**
- *說明*：找出集合中`重複的值`（回傳重複項的 **key/value**）。
- *語法*：`$collection->duplicates($key = null)`
- *範例*：
```php
$collection = collect(['a', 'b', 'a', 'c', 'b']);
$collection->duplicates(); // [2 => 'a', 4 => 'b']

$employees = collect([
    ['email' => 'abigail@example.com', 'position' => 'Developer'],
    ['email' => 'james@example.com', 'position' => 'Designer'],
    ['email' => 'victoria@example.com', 'position' => 'Developer'],
]);
$employees->duplicates('position'); // [2 => 'Developer']
```

---

## **duplicatesStrict()**
- *說明*：同 duplicates，但用嚴格比對（===）。
- *語法*：`$collection->duplicatesStrict($key = null)`

---

## **each()**
- *說明*：對集合中的每個元素「逐一執行你給的 callback 函式」。常用於遍歷集合、做副作用（如列印、寫檔、累加外部變數等），`不會改變集合內容`。
- *語法*：`$collection->each(Closure $callback)`
- *範例*：
```php
$collection = collect([1, 2, 3]);
$collection->each(function ($item, $key) {
    echo "索引 $key 的值是 $item\n";
});
// 輸出：
//      索引 0 的值是 1
//      索引 1 的值是 2
//      索引 2 的值是 3
// 逐行註解：
//      1. 建立一個集合 [1, 2, 3]
//      2. each() 會把 callback 套用到每個元素，$item 是值，$key 是索引
//      3. 這裡 callback 只是單純列印，不會改變集合內容
```
- 注意事項：
    - `each()` 不會改變集合內容，只是「遍歷」每個元素。
    - 如果你想「轉換」集合內容，應該用 `map()`。
    - 如果 *callback 回傳 false*，`each()` 會中斷遍歷（類似 break）。

- 生活化比喻：
    - 就像你拿著一疊考卷，對每一張都「*看一眼、做個記號*」，但考卷本身內容沒變。

- 補充：
    - 如果你要「收集」處理後的結果，請用 `map()` 或 `filter()`，不要用 `each()`。

---

## **eachSpread()**
- *說明*：和 each() 類似，但`會把集合中的每個「子陣列」或「子集合」的元素「展開」當作 callback 的多個參數`。

[補充] 什麼是「*展開*」？
- 「展開」就是把一個`陣列（如 [1, 'A']）`裡的多個值，分別當作多個參數傳給 callback。
  例如 callback($number, $letter)，每次會收到 $number=1, $letter='A'，而不是收到一個陣列。
- 如果用 `each()`，callback 只會收到一個元素（這裡是一個陣列）；
  用 `eachSpread()`，callback 會收到多個參數（這裡是陣列裡的每個值）。
- 生活化比喻：就像你有一盒便當（[飯, 菜, 肉]），`each()` 是把整盒便當交給你，`eachSpread()` 是把飯、菜、肉分開，分別放到你面前。

- *語法*：`$collection->eachSpread(Closure $callback)`
- *範例*：
```php
$collection = collect([
    [1, 'A'],
    [2, 'B'],
    [3, 'C'],
]);
$collection->eachSpread(function ($number, $letter) {
    echo "數字：$number，字母：$letter\n";
});
// 輸出：
//      數字：1，字母：A
//      數字：2，字母：B
//      數字：3，字母：C
// 逐行註解：
//      1. 建立一個集合，每個元素都是一個陣列（[數字, 字母]）
//      2. eachSpread() 會把每個子陣列「展開」成多個參數傳給 callback
//      3. callback 依序收到 $number, $letter，然後列印出來
```
- 注意事項：
    - `eachSpread()` 只適用於集合的每個元素本身是陣列或可展開的結構。
    - 如果 *callback 回傳 false*，`eachSpread()` 會中斷遍歷（類似 break）。
    - 回傳值是*原本的 Collection*（不會產生新集合）。

- 生活化比喻：
    - 就像你有一疊成績單，每一張上面有「*學號、姓名、分數*」，你想要一次把這三個欄位分別拿出來處理，而不是整張成績單丟進去。

- 補充：
    - 如果你只想單純遍歷每個元素（不需要展開），用 `each()` 就好。
    - 如果你要收集處理後的結果，請用 `mapSpread()`。

---

## **ensure()**
- *說明*：確保集合所有元素皆為`指定型別`，否則丟出例外。
- *語法*：`$collection->ensure($type)`
- *範例*：
```php
$collection->ensure(User::class);
$collection->ensure([User::class, Customer::class]);
$collection->ensure('int');
```
- *補充*：僅檢查當下內容，後續仍可加入其他型別。

---

## **every()**
- *說明*：判斷集合裡「`每一個元素`」是否都符合你給的條件（callback）。`全部都符合`才回傳 true，只要有一個不符合就回傳 false。
- *語法*：`$collection->every(Closure $callback)`
- *範例*：
```php
$collection = collect([2, 4, 6]);
$result = $collection->every(fn($item) => $item % 2 === 0); // true
// ↑ 逐行註解：
// 1. 建立一個集合 [2, 4, 6]
// 2. 呼叫 every()，傳入 callback：$item % 2 === 0
//    - 2 % 2 === 0 → true
//    - 4 % 2 === 0 → true
//    - 6 % 2 === 0 → true
// 3. 全部都符合條件（都是偶數），所以回傳 true

$collection = collect([2, 3, 6]);
$result = $collection->every(fn($item) => $item % 2 === 0); // false
// ↑ 逐行註解：
// 1. 建立一個集合 [2, 3, 6]
// 2. 呼叫 every()，傳入 callback：$item % 2 === 0
//    - 2 % 2 === 0 → true
//    - 3 % 2 === 0 → false（有一個不符合）
//    - 6 % 2 === 0 → true
// 3. 只要有一個不符合，就回傳 false
```
- 原理說明：
    - `every()` 只有在「*每一個元素都符合條件*」時才會回傳 true，只要有一個不符合就會回傳 false。

- 生活化比喻：
    - 就像老師檢查全班作業，只有「*每個人都交了*」才算通過，只要有一個沒交就不通過。

---

## **except()**
- *說明*：`排除指定 key`，回傳剩餘項目。
- *語法*：`$collection->except($keys)`
- *範例*：
```php
$collection = collect(['product_id' => 1, 'price' => 100, 'discount' => false]);
$filtered = $collection->except(['price', 'discount']);
$filtered->all(); // ['product_id' => 1]
```
- *補充*：相反操作請用 only。

---

## **filter()**
- *說明*：依條件`過濾集合`，保留符合條件的項目。
- *語法*：`$collection->filter(Closure $callback = null)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4]);
$filtered = $collection->filter(function ($value, $key) {
    return $value > 2;
});
$filtered->all(); // [3, 4]

$collection = collect([1, 2, 3, null, false, '', 0, []]);
$collection->filter()->all(); // [1, 2, 3]
```
- *補充*：相反操作請用 reject。

---

## **first()**
- *說明*：取得`第一個符合條件`的項目，無條件則取第一個。
- *語法*：`$collection->first(Closure $callback = null)`
- *範例*：
```php
collect([1, 2, 3, 4])->first(function ($value, $key) {
    return $value > 2;
}); // 3
collect([1, 2, 3, 4])->first(); // 1
```
- *補充*：找不到時回傳 null。

---

## **firstOrFail()**
- *說明*：同 first，但找不到時丟出 `ItemNotFoundException`。
- *語法*：`$collection->firstOrFail(Closure $callback = null)`
- *範例*：
```php
collect([1, 2, 3, 4])->firstOrFail(function ($value, $key) {
    return $value > 5;
}); // 拋出例外
collect([])->firstOrFail(); // 拋出例外
```

---

## **firstWhere()**
- *說明*：取得`第一個指定 key/value 符合`的項目。
- *語法*：`$collection->firstWhere($key, $operator = null, $value = null)`
- *範例*：
```php
$collection = collect([
    ['name' => 'Regena', 'age' => null],
    ['name' => 'Linda', 'age' => 14],
    ['name' => 'Diego', 'age' => 23],
    ['name' => 'Linda', 'age' => 84],
]);
$collection->firstWhere('name', 'Linda'); // ['name' => 'Linda', 'age' => 14]
$collection->firstWhere('age', '>=', 18); // ['name' => 'Diego', 'age' => 23]
$collection->firstWhere('age'); // ['name' => 'Linda', 'age' => 14]
```

---

## **flatMap()**
- *說明*：遍歷集合並`映射，最後壓平成一層`。
- *語法*：`$collection->flatMap(Closure $callback)`
- *範例*：
```php
$collection = collect([
    ['name' => 'Sally'],
    ['school' => 'Arkansas'],
    ['age' => 28]
]);
$flattened = $collection->flatMap(function ($values) {
    return array_map('strtoupper', $values); // 將陣列 $values 中的每個值都轉成大寫，回傳新陣列
});
$flattened->all(); // ['name' => 'SALLY', 'school' => 'ARKANSAS', 'age' => '28']
```
- 註解：
    - `array_map` 的語法是 `array_map(callable $callback, array $array)`
    - 第一個參數 $callback 可以**直接寫函式名稱（字串）**，如 'strtoupper'，代表會把這個函式套用到陣列的每個元素。
    - 所以 `array_map('strtoupper', $values)` 會把 $values 裡的每個元素都丟進 strtoupper 處理，產生新陣列。
- 範例：
    ```php
    -   $values = ['laravel', 'php', 'collection'];
    -   $result = array_map('strtoupper', $values); // ['LARAVEL', 'PHP', 'COLLECTION']
    ```
---

## **flatten()**
- *說明*：flatten 英文意思是「壓平、弄平」。在程式設計裡，flatten 代表把多維（巢狀）的結構壓平成一維（單層），讓所有元素都在同一層。這個命名在各種語言都很常見，語意直觀。
- 生活比喻：就像把一疊有高低起伏的紙張用力壓平，最後變成一張平坦的紙。壓平多維集合為一維，可指定深度。
- *語法*：`$collection->flatten($depth = INF)`
- *參數說明*：
    - `$depth`：壓平的深度，預設為 `INF`，代表「無限深度」，也就是會把所有巢狀結構全部壓平成一層。
        - 如果只想壓平一層，可以傳入 1，例如：`$collection->flatten(1)`。
        - `INF` 是 PHP 的一個特殊常數，代表「無限大」（infinity），在這裡用來表示「壓平到最深」。
- *範例*：
```php
$collection = collect([
    'name' => 'Taylor',
    'languages' => [
        'PHP', 'JavaScript'
    ]
]);
$flattened = $collection->flatten();
$flattened->all(); // ['Taylor', 'PHP', 'JavaScript']


$collection = collect([1, [2, [3, [4]]]]);
$collection->flatten();    // [1, 2, 3, 4]      // 預設無限深度，全部壓平
collection->flatten(1);   // [1, 2, [3, [4]]]  // 只壓平一層
// 註解：flatten(1) 只會把最外層的巢狀結構攤平，裡面更深的陣列不會被攤平。
//      例如原本 [1, [2, [3, [4]]]]，壓平一層後變成 [1, 2, [3, [4]]]，
//      其中 [3, [4]] 還是維持原本的巢狀結構。
//      如果用 flatten()（預設無限深度），就會全部攤平成 [1, 2, 3, 4]
//      可以想像成「只打開外箱，裡面的小盒子還沒打開」。

$collection = collect([
    'Apple' => [
        [
            'name' => 'iPhone 6S',
            'brand' => 'Apple'
        ],
    ],
    'Samsung' => [
        [
            'name' => 'Galaxy S7',
            'brand' => 'Samsung'
        ],
    ],
]);
$products = $collection->flatten(1); // flatten(1) 只壓平一層，Apple/Samsung 這兩個 key 的陣列會被攤平，裡面的每個商品（陣列）還是維持原本結構
$products->values()->all();
// [
//     ['name' => 'iPhone 6S', 'brand' => 'Apple'],
//     ['name' => 'Galaxy S7', 'brand' => 'Samsung'],
// ]
// 註解：
// 原本 $collection 結構：
// [
//   'Apple' => [ [商品1] ],
//   'Samsung' => [ [商品2] ]
// ]
// 執行 flatten(1) 後，最外層的品牌 key（Apple、Samsung）會被移除，
// 只留下裡面的商品資料（每個商品還是陣列）。
// 也就是說，原本的品牌分組消失，變成單純的商品清單。
// 這就是「壓平一層」的效果。
```

---

## **flip()**
- *說明*：將集合的 key 與 value `互換`。
- *語法*：`$collection->flip()`
- *範例*：
```php
$collection = collect(['name' => 'Taylor', 'framework' => 'Laravel']);
$flipped = $collection->flip();
$flipped->all(); // ['Taylor' => 'name', 'Laravel' => 'framework']
```
- 註解：
    - 為什麼叫 flip？flip 英文是「翻轉」的意思，這個方法就是把 key 和 value 互換，
    - 就像把一張卡片翻過來，正反面對調。
    - 例如原本是 `name => Taylor`，flip 後變成 `Taylor => name`。
    - 這樣可以讓你用 value 當 key 來快速查找。

- 生活化比喻：
    - 就像你有一張名牌，正面寫名字，反面寫職稱，`flip()` 就是把名牌翻過來看反面。
    - 或像撲克牌正反面互換。

---

## **forget()**
- *說明*：移除指定 key 的項目（`直接修改原集合，不回傳新集合`）。
- *語法*：`$collection->forget($keys)`
- *範例*：
```php
$collection = collect(['name' => 'Taylor', 'framework' => 'Laravel']);
$collection->forget('name'); // ['framework' => 'Laravel']
$collection->forget(['name', 'framework']); // []
```
- *補充*：此方法會直接影響原集合。

---

## **forPage()**
- *說明*：依分頁邏輯，回傳`指定頁數`的項目。
- *語法*：`$collection->forPage($page, $perPage)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
$chunk = $collection->forPage(2, 3);
$chunk->all(); // [4, 5, 6]
```

---

## **fromJson()**
- *說明*：`靜態方法`，將 JSON 字串轉為 Collection 實例。
- *語法*：`Collection::fromJson($json)`
- *範例*：
```php
use Illuminate\Support\Collection;
$json = json_encode([
    'name' => 'Taylor Otwell',
    'role' => 'Developer',
    'status' => 'Active',
]);
$collection = Collection::fromJson($json);
```

---

## **get()**
- *說明*：取得`指定 key 的值`，若不存在可給預設值或 callback。
- *語法*：`$collection->get($key, $default = null)`
- *範例*：
```php
$collection = collect(['name' => 'Taylor', 'framework' => 'Laravel']);
$value = $collection->get('name'); // Taylor
$value = $collection->get('age', 34); // 34
$value = $collection->get('email', function () {
    return 'taylor@example.com';
}); // taylor@example.com
```

---

## **groupBy()**
- *說明*：依指定 key 或 callback 將集合分組，可多層分組。
- *語法*：`$collection->groupBy($key, $preserveKeys = false)`
- *範例*：
```php
$collection = collect([
    ['account_id' => 'account-x10', 'product' => 'Chair'],
    ['account_id' => 'account-x10', 'product' => 'Bookcase'],
    ['account_id' => 'account-x11', 'product' => 'Desk'],
]);
$grouped = $collection->groupBy('account_id');
$grouped->all();
// [
//     'account-x10' => [
//         ['account_id' => 'account-x10', 'product' => 'Chair'],
//         ['account_id' => 'account-x10', 'product' => 'Bookcase'],
//     ],
//     'account-x11' => [
//         ['account_id' => 'account-x11', 'product' => 'Desk'],
//     ],
// ]

// callback 分組
$grouped = $collection->groupBy(function ($item, $key) {
    return substr($item['account_id'], -3);
});
$grouped->all();
// [
//     'x10' => [...],
//     'x11' => [...],
// ]

// 多層分組
$data = collect([
    10 => ['user' => 1, 'skill' => 1, 'roles' => ['Role_1', 'Role_3']],
    20 => ['user' => 2, 'skill' => 1, 'roles' => ['Role_1', 'Role_2']],
    30 => ['user' => 3, 'skill' => 2, 'roles' => ['Role_1']],
    40 => ['user' => 4, 'skill' => 2, 'roles' => ['Role_2']],
]);
$result = $data->groupBy(['skill', function ($item) {
    return $item['roles'];
}], preserveKeys: true);
```

- 補充說明：
    上述多層分組的結果如下：
    `$result->all()` 會得到：
    ```php
    [
        1 => [
            ['Role_1', 'Role_3'] => [
                10 => ['user' => 1, 'skill' => 1, 'roles' => ['Role_1', 'Role_3']],
            ],
            ['Role_1', 'Role_2'] => [
                20 => ['user' => 2, 'skill' => 1, 'roles' => ['Role_1', 'Role_2']],
            ],
        ],
        2 => [
            ['Role_1'] => [
                30 => ['user' => 3, 'skill' => 2, 'roles' => ['Role_1']],
            ],
            ['Role_2'] => [
                40 => ['user' => 4, 'skill' => 2, 'roles' => ['Role_2']],
            ],
        ],
    ]
    ```
- 但注意：
    *roles 是陣列*，PHP 會自動把**陣列 key 轉成字串**（例如 "a:2:{i:0;s:6:\"Role_1\";i:1;s:6:\"Role_3\";}" 這種序列化字串），
    所以實際上 key 會是`陣列的序列化字串`。

- 也就是說，這個 groupBy 會先依 skill 分兩組（1、2），每組再依 roles（陣列）分組，每組裡面是原本的資料，key 都會保留（因為 `preserveKeys: true`）。

- 如果你用 `dd($result->all())`，你會看到 key 是 skill，下一層 key 是 roles 的序列化字串，value 是對應的原始資料。

- 範例輸出（key 已簡化）：
    ```php
    [
        1 => [
            'a:2:{i:0;s:6:"Role_1";i:1;s:6:"Role_3";}' => [
                10 => ['user' => 1, 'skill' => 1, 'roles' => ['Role_1', 'Role_3']],
            ],
            'a:2:{i:0;s:6:"Role_1";i:1;s:6:"Role_2";}' => [
                20 => ['user' => 2, 'skill' => 1, 'roles' => ['Role_1', 'Role_2']],
            ],
        ],
        2 => [
            'a:1:{i:0;s:6:"Role_1";}' => [
                30 => ['user' => 3, 'skill' => 2, 'roles' => ['Role_1']],
            ],
            'a:1:{i:0;s:6:"Role_2";}' => [
                40 => ['user' => 4, 'skill' => 2, 'roles' => ['Role_2']],
            ],
        ],
    ]
    ```
這是*因為 PHP 陣列當作 key 時，會**自動序列化成字串***。

---

## **has()**
- *說明*：判斷集合`是否有`指定 key。
- *語法*：`$collection->has($key)`
- *範例*：
```php
$collection = collect(['account_id' => 1, 'product' => 'Desk', 'amount' => 5]);
$collection->has('product'); // true
$collection->has(['product', 'amount']); // true
$collection->has(['amount', 'price']); // false
```

---

## **hasAny()**
- *說明*：判斷集合`是否有任一`指定 key。
- *語法*：`$collection->hasAny($keys)`
- *範例*：
```php
$collection = collect(['account_id' => 1, 'product' => 'Desk', 'amount' => 5]);
$collection->hasAny(['product', 'price']); // true
$collection->hasAny(['name', 'price']); // false
```

---

## **implode()**
- *說明*：將集合內容以指定字串連接，可指定 key 或 callback。
- *語法*：`$collection->implode($glue, $key = null)`
- *範例*：
```php
$collection = collect([
    ['account_id' => 1, 'product' => 'Desk'],
    ['account_id' => 2, 'product' => 'Chair'],
]);
$collection->implode('product', ', '); // 'Desk, Chair'

collect([1, 2, 3, 4, 5])->implode('-'); // '1-2-3-4-5'

$collection->implode(function ($item, $key) {
    return strtoupper($item['product']);
}, ', '); // 'DESK, CHAIR'
```

---

## **intersect()**
- *說明*：取交集，回傳`同時存在於`給定集合的值（保留原 key）。
- *語法*：`$collection->intersect($items)`
- *範例*：
```php
$collection = collect(['Desk', 'Sofa', 'Chair']);
$intersect = $collection->intersect(['Desk', 'Chair', 'Bookcase']);
$intersect->all(); // [0 => 'Desk', 2 => 'Chair']
```

---

## **intersectUsing()**
- *說明*：同 intersect，但可`自訂比對邏輯（callback）`。
- *語法*：`$collection->intersectUsing($items, $callback)`
- *範例*：
```php
$collection = collect(['Desk', 'Sofa', 'Chair']);
//   建立一個集合，內容是 'Desk', 'Sofa', 'Chair'
$intersect = $collection->intersectUsing(['desk', 'chair', 'bookcase'], function ($a, $b) {
    return strcasecmp($a, $b);
});
//   intersectUsing 會取交集，但比對方式由你自訂
//   這裡傳入的陣列是 ['desk', 'chair', 'bookcase']
//   callback 用 strcasecmp($a, $b)（不區分大小寫的字串比較）
//   會把集合裡每個值 $a 拿去和給定陣列每個值 $b 做比較
//   只要 callback 回傳 0（代表相等），就算交集
//   所以 'Desk' 會和 'desk' 比對成功，'Chair' 會和 'chair' 比對成功
//   'Sofa' 沒有對應的 'sofa'，所以不會被包含
$intersect->all(); // [0 => 'Desk', 2 => 'Chair']
//   結果：只留下 'Desk' 和 'Chair'，key 保留原本集合的 key（0、2）
```

---

## **intersectAssoc()**
- *說明*：根據 key 與 value 取交集，回傳同時存在於給定集合的`鍵值對`。
- *語法*：`$collection->intersectAssoc($items)`
- *範例*：
```php
$collection = collect([
    'color' => 'red',
    'size' => 'M',
    'material' => 'cotton'
]);
$intersect = $collection->intersectAssoc([
    'color' => 'blue',
    'size' => 'M',
    'material' => 'polyester'
]);
$intersect->all(); // ['size' => 'M']
```

---

## **intersectAssocUsing()**
- *說明*：同 intersectAssoc，但可`自訂 key/value 比對邏輯（callback）`。
- *語法*：`$collection->intersectAssocUsing($items, $callback)`
- *範例*：
```php
$collection = collect([
    'color' => 'red',
    'Size' => 'M',
    'material' => 'cotton',
]);
$intersect = $collection->intersectAssocUsing([
    'color' => 'blue',
    'size' => 'M',
    'material' => 'polyester',
], function ($a, $b) {
    return strcasecmp($a, $b);
});
$intersect->all(); // ['Size' => 'M']
```

---

## **intersectByKeys()**
- *說明*：只根據 key 取交集，回傳同時存在於給定集合的鍵值對。
- *語法*：`$collection->intersectByKeys($items)`
- *範例*：
```php
$collection = collect([
    'serial' => 'UX301', 'type' => 'screen', 'year' => 2009,
]);
$intersect = $collection->intersectByKeys([
    'reference' => 'UX404', 'type' => 'tab', 'year' => 2011,
]);
$intersect->all(); // ['type' => 'screen', 'year' => 2009]
```

---

## **isEmpty()**
- *說明*：判斷集合是否為空。
- *語法*：`$collection->isEmpty()`
- *範例*：
```php
collect([])->isEmpty(); // true
```

---

## **isNotEmpty()**
- *說明*：判斷集合是否不為空。
- *語法*：`$collection->isNotEmpty()`
- *範例*：
```php
collect([])->isNotEmpty(); // false
```

---

## **join()**
- *說明*：將集合內容`以字串連接`，可自訂最後一個元素的連接字串。
- *語法*：`$collection->join($glue, $finalGlue = null)`
- *範例*：
```php
collect(['a', 'b', 'c'])->join(', '); // 'a, b, c'
collect(['a', 'b', 'c'])->join(', ', ', and '); // 'a, b, and c'
collect(['a', 'b'])->join(', ', ' and '); // 'a and b'
collect(['a'])->join(', ', ' and '); // 'a'
collect([])->join(', ', ' and '); // ''
```

---

## **keyBy()**
- *說明*：依指定 key 或 callback `重新索引集合`。
- *語法*：`$collection->keyBy($key)`
- *範例*：
```php
$collection = collect([
    ['product_id' => 'prod-100', 'name' => 'Desk'],
    ['product_id' => 'prod-200', 'name' => 'Chair'],
]);
$keyed = $collection->keyBy('product_id');
$keyed->all();
// [
//     'prod-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
//     'prod-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
// ]

$keyed = $collection->keyBy(function ($item, $key) {
    return strtoupper($item['product_id']);
});
$keyed->all();
// [
//     'PROD-100' => [...],
//     'PROD-200' => [...],
// ]
```

---

## **keys()**
- *說明*：取得集合所有 key。
- *語法*：`$collection->keys()`
- *範例*：
```php
$collection = collect([
    'prod-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
    'prod-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
]);
$keys = $collection->keys();
$keys->all(); // ['prod-100', 'prod-200']
```

---

## **last()**
- *說明*：取得`最後一個符合條件`的項目，無條件則取最後一個。
- *語法*：`$collection->last(Closure $callback = null)`
- *範例*：
```php
collect([1, 2, 3, 4])->last(function ($value, $key) {
    return $value < 3;
}); // 2
collect([1, 2, 3, 4])->last(); // 4
```
- *補充*：找不到時回傳 null。

---

## **lazy()**
- *說明*：將集合轉為 `LazyCollection`，適合大量資料處理。
- *語法*：`$collection->lazy()`
- *範例*：
```php
$lazyCollection = collect([1, 2, 3, 4])->lazy();
$lazyCollection::class; // Illuminate\Support\LazyCollection
$lazyCollection->all(); // [1, 2, 3, 4]
```
- *補充*：可大幅**減少記憶體用量**。

---

## **macro()**
- *說明*：`靜態方法`，註冊自訂 Collection 方法。
- *語法*：`Collection::macro($name, $callback)`
- *補充*：詳見官方文件「Extending Collections」。

---

## **make()**
- *說明*：`靜態方法`，建立新的 Collection 實例。
- *語法*：`Collection::make($items)`
- *範例*：
```php
use Illuminate\Support\Collection;
$collection = Collection::make([1, 2, 3]);
```

---

## **map()**
- *說明*：遍歷集合並映射每個項目，回傳新集合。
- *語法*：`$collection->map(Closure $callback)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
$multiplied = $collection->map(function ($item, $key) {
    return $item * 2;
});
$multiplied->all(); // [2, 4, 6, 8, 10]
```
- *補充*：**不會改變原集合**，若要直接改變請用 `transform`。

---

## **mapInto()**
- *說明*：遍歷集合，將每個值`傳入指定類別建構子`，回傳**新集合**。
- *語法*：`$collection->mapInto($class)`
- *範例*：
```php
class Currency {
    function __construct(public string $code) {}
}
$collection = collect(['USD', 'EUR', 'GBP']);
$currencies = $collection->mapInto(Currency::class);
$currencies->all(); // [Currency('USD'), Currency('EUR'), Currency('GBP')]
```

---

## **mapSpread()**
- *說明*：遍歷集合，將每個子陣列`展開傳入 callback`，回傳**新集合**。
- *語法*：`$collection->mapSpread(Closure $callback)`
- *範例*：
```php
$collection = collect([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);
// 建立一個集合，內容是 0~9

$chunks = $collection->chunk(2);
// chunk(2) 會把集合每2個分成一組，得到 [[0,1], [2,3], [4,5], [6,7], [8,9]]

$sequence = $chunks->mapSpread(function ($even, $odd) {
    // mapSpread 的 callback 參數數量，會根據每個子陣列的元素個數自動展開
    // 例如：子陣列有 2 個元素就是 function ($a, $b)
    //       子陣列有 3 個元素就是 function ($a, $b, $c)
    // 例如：
    //   collect([[1,2,3],[4,5,6]])->mapSpread(function($a,$b,$c){ return $a+$b+$c; });
    //   這裡 callback 會收到三個參數
    return $even + $odd;
});
// mapSpread 會把每個子陣列展開成多個參數傳給 callback
// 這裡每組都是兩個數字，$even 和 $odd
// callback 回傳 $even + $odd，也就是每組兩個數字相加
// 所以結果依序是 0+1, 2+3, 4+5, 6+7, 8+9

$sequence->all(); // [1, 5, 9, 13, 17]
// 得到的新集合是 [1, 5, 9, 13, 17]
```

---

## **mapToGroups()**
- *說明*：`依 closure 分組映射`，回傳分組後的新集合。
- *語法*：`$collection->mapToGroups(Closure $callback)`
- *範例*：
```php
$collection = collect([
    [ 'name' => 'John Doe', 'department' => 'Sales' ],
    [ 'name' => 'Jane Doe', 'department' => 'Sales' ],
    [ 'name' => 'Johnny Doe', 'department' => 'Marketing' ]
]);
$grouped = $collection->mapToGroups(function ($item, $key) {
    return [$item['department'] => $item['name']];
});
$grouped->all();
// [
//     'Sales' => ['John Doe', 'Jane Doe'],
//     'Marketing' => ['Johnny Doe'],
// ]
$grouped->get('Sales')->all(); // ['John Doe', 'Jane Doe']
```

---

## **mapWithKeys()**
- *說明*：遍歷集合，callback 回傳 key/value，組成新集合。
- *語法*：`$collection->mapWithKeys(Closure $callback)`
- *範例*：
```php
$collection = collect([
    [ 'name' => 'John', 'department' => 'Sales', 'email' => 'john@example.com' ],
    [ 'name' => 'Jane', 'department' => 'Marketing', 'email' => 'jane@example.com' ]
]);
$keyed = $collection->mapWithKeys(function ($item, $key) {
    return [$item['email'] => $item['name']];
});
$keyed->all();
// [
//     'john@example.com' => 'John',
//     'jane@example.com' => 'Jane',
// ]
``` 

---

## **max()**
- *說明*：取得集合中最大值，可指定 key。
- *語法*：`$collection->max($key = null)`
- *範例*：
```php
$max = collect([
    ['foo' => 10],
    ['foo' => 20]
])->max('foo'); // 20
$max = collect([1, 2, 3, 4, 5])->max(); // 5
```

---

## **median()**
- *說明*：取得集合`中位數`，可指定 key。
- *語法*：`$collection->median($key = null)`
- *範例*：
```php
$median = collect([
    ['foo' => 10],
    ['foo' => 10],
    ['foo' => 20],
    ['foo' => 40]
])->median('foo'); // 15
$median = collect([1, 1, 2, 4])->median(); // 1.5
```

---

## **merge()**
- *說明*：合併集合與給定陣列/集合，`key 相同則覆蓋，數字 key 會追加`。
- *語法*：`$collection->merge($items)`
- *範例*：
```php
$collection = collect(['product_id' => 1, 'price' => 100]);
$merged = $collection->merge(['price' => 200, 'discount' => false]);
$merged->all(); // ['product_id' => 1, 'price' => 200, 'discount' => false]

$collection = collect(['Desk', 'Chair']);
$merged = $collection->merge(['Bookcase', 'Door']);
$merged->all(); // ['Desk', 'Chair', 'Bookcase', 'Door']
```

---

## **mergeRecursive()**
- *說明*：**遞迴合併集合**與**給定陣列/集合**，`key 相同則合併為陣列`。
- *語法*：`$collection->mergeRecursive($items)`
- *範例*：
```php
$collection = collect(['product_id' => 1, 'price' => 100]);
$merged = $collection->mergeRecursive([
    'product_id' => 2,
    'price' => 200,
    'discount' => false
]);
$merged->all(); // ['product_id' => [1, 2], 'price' => [100, 200], 'discount' => false]
```

---

## **min()**
- *說明*：取得集合中最小值，可指定 key 或使用 callback 自訂比較邏輯。
- *語法*：`$collection->min($key = null)`
- *範例*：
```php
// 基本用法：直接比較數值
$min = collect([1, 2, 3, 4, 5])->min(); // 1
// ↑ 逐行註解：
// 1. collect([1, 2, 3, 4, 5]) 建立一個包含 5 個數字的集合
// 2. ->min() 呼叫 min() 方法，不傳參數
// 3. 不傳參數時，會直接比較集合中的每個元素
// 4. 比較過程：1 < 2 < 3 < 4 < 5，所以最小值是 1
// 5. 回傳 1

$min = collect([10, 5, 8, 2, 9])->min(); // 2
// ↑ 逐行註解：
// 1. collect([10, 5, 8, 2, 9]) 建立一個包含 5 個數字的集合
// 2. ->min() 呼叫 min() 方法
// 3. 比較過程：2 < 5 < 8 < 9 < 10，所以最小值是 2
// 4. 回傳 2

// 指定 key：比較關聯陣列中指定欄位的值
$min = collect([
    ['name' => 'Desk', 'price' => 200],
    ['name' => 'Chair', 'price' => 100],
    ['name' => 'Bookcase', 'price' => 150]
])->min('price'); // 100
// ↑ 逐行註解：
// 1. collect([...]) 建立一個集合，每個元素都是關聯陣列
// 2. 每個關聯陣列有 'name' 和 'price' 兩個欄位
// 3. ->min('price') 指定要比較 'price' 欄位的值
// 4. 比較過程：
//    - 第一個元素：price = 200
//    - 第二個元素：price = 100
//    - 第三個元素：price = 150
// 5. 100 < 150 < 200，所以最小值是 100
// 6. 回傳 100

// 比較字串（按字母順序）
$min = collect(['apple', 'banana', 'cherry'])->min(); // 'apple'
// ↑ 逐行註解：
// 1. collect(['apple', 'banana', 'cherry']) 建立字串集合
// 2. ->min() 不傳參數，直接比較字串
// 3. 字串比較按字母順序（ASCII 順序）：
//    - 'apple' 的 'a' 在字母表中最前面
//    - 'banana' 的 'b' 在 'a' 後面
//    - 'cherry' 的 'c' 在 'b' 後面
// 4. 所以 'apple' 是最小的
// 5. 回傳 'apple'

$min = collect(['zebra', 'apple', 'banana'])->min(); // 'apple'
// ↑ 逐行註解：
// 1. collect(['zebra', 'apple', 'banana']) 建立字串集合
// 2. 字串比較按字母順序：
//    - 'apple' 的 'a' 最前面
//    - 'banana' 的 'b' 第二
//    - 'zebra' 的 'z' 最後面
// 3. 所以 'apple' 是最小的
// 4. 回傳 'apple'

// 比較日期
$dates = collect([
    '2023-01-15',
    '2023-03-20', 
    '2023-02-10'
]);
$min = $dates->min(); // '2023-01-15'
// ↑ 逐行註解：
// 1. $dates = collect([...]) 建立日期字串集合
// 2. 日期字串可以按字母順序比較（因為格式統一）
// 3. 比較過程：
//    - '2023-01-15' 的 '01' 最小
//    - '2023-02-10' 的 '02' 第二
//    - '2023-03-20' 的 '03' 最大
// 4. 所以 '2023-01-15' 是最早的日期
// 5. 回傳 '2023-01-15'

// 空集合會回傳 null
$min = collect([])->min(); // null
// ↑ 逐行註解：
// 1. collect([]) 建立一個空集合
// 2. ->min() 嘗試找最小值
// 3. 但集合中沒有元素可以比較
// 4. 所以回傳 null

// 混合型別比較（會自動轉型）
$min = collect([1, '2', 3, '4'])->min(); // 1
// ↑ 逐行註解：
// 1. collect([1, '2', 3, '4']) 建立混合型別集合
// 2. 包含數字 1, 3 和字串 '2', '4'
// 3. PHP 會嘗試自動轉型比較：
//    - 1（數字）保持 1
//    - '2'（字串）轉為數字 2
//    - 3（數字）保持 3
//    - '4'（字串）轉為數字 4
// 4. 比較：1 < 2 < 3 < 4
// 5. 所以最小值是 1
// 6. 回傳 1

$min = collect(['1', 2, '3', 4])->min(); // '1'
// ↑ 逐行註解：
// 1. collect(['1', 2, '3', 4]) 建立混合型別集合
// 2. 包含字串 '1', '3' 和數字 2, 4
// 3. 當字串在第一位時，PHP 會按字串比較：
//    - '1' 的 '1' 在字母表中最前面
//    - '3' 的 '3' 在 '1' 後面
//    - 2 和 4 會被轉為字串 '2', '4' 比較
// 4. 所以 '1' 是最小的
// 5. 回傳 '1'

// 物件集合比較
class Product {
    public function __construct(public int $price) {}
}
$products = collect([
    new Product(200),
    new Product(100),
    new Product(150)
]);
$min = $products->min('price'); // 100
// ↑ 逐行註解：
// 1. class Product {...} 定義一個 Product 類別
// 2. public function __construct(public int $price) {} 是建構子
//    - public int $price 是建構子參數，同時宣告為公開屬性
//    - 等同於：public int $price; public function __construct(int $price) { $this->price = $price; }
// 3. $products = collect([...]) 建立物件集合
//    - new Product(200) 建立價格為 200 的產品物件
//    - new Product(100) 建立價格為 100 的產品物件
//    - new Product(150) 建立價格為 150 的產品物件
// 4. ->min('price') 指定要比較每個物件的 price 屬性
// 5. 比較過程：
//    - 第一個物件：price = 200
//    - 第二個物件：price = 100
//    - 第三個物件：price = 150
// 6. 100 < 150 < 200，所以最小值是 100
// 7. 回傳 100
```
- 原理說明：
    - `min()` 會遍歷集合中的所有元素，找出最小值
    - *不指定 key 時*，直接比較元素本身的值
    - *指定 key 時*，會比較每個元素的指定欄位值
    - 比較規則遵循 PHP 的標準比較邏輯：
        - **數字**：數值大小比較
        - **字串**：字母順序比較（ASCII 順序）
        - **日期**：時間先後比較
        - **混合型別**：會嘗試自動轉型比較

- 生活化比喻：
    - 就像在一堆價格標籤中找出最便宜的，或在一堆名字中找出字母順序最前面的。

- 注意事項：
    - *空集合* 會回傳 null
    - 如果集合中有 *無法比較的元素*，可能會產生意外結果
    - 對於複雜物件，建議 *明確指定要比較的屬性*

---

## **mode()**
- *說明*：取得集`合眾數（出現次數最多的值）`，可指定 key。mode 是統計學術語，代表「最常出現的數值」。
- *語法*：`$collection->mode($key = null)`
- *範例*：
```php
// 基本用法：找出最常出現的數值
$mode = collect([1, 1, 2, 4])->mode(); // [1]
// ↑ 逐行註解：
// 1. collect([1, 1, 2, 4]) 建立數字集合
// 2. 統計每個數字出現次數：
//    - 1 出現 2 次
//    - 2 出現 1 次
//    - 4 出現 1 次
// 3. 1 出現最多次（2 次），所以眾數是 1
// 4. 回傳 [1]（陣列格式，因為可能有多個眾數）

// 指定 key：找出關聯陣列中指定欄位最常出現的值
$mode = collect([
    ['foo' => 10],
    ['foo' => 10],
    ['foo' => 20],
    ['foo' => 40]
])->mode('foo'); // [10]
// ↑ 逐行註解：
// 1. collect([...]) 建立關聯陣列集合
// 2. ->mode('foo') 指定要統計 'foo' 欄位的值
// 3. 統計每個值出現次數：
//    - 10 出現 2 次
//    - 20 出現 1 次
//    - 40 出現 1 次
// 4. 10 出現最多次，所以眾數是 10
// 5. 回傳 [10]

// 多個眾數的情況
$mode = collect([1, 1, 2, 2])->mode(); // [1, 2]
// ↑ 逐行註解：
// 1. collect([1, 1, 2, 2]) 建立數字集合
// 2. 統計每個數字出現次數：
//    - 1 出現 2 次
//    - 2 出現 2 次
// 3. 1 和 2 都出現 2 次，都是最多次
// 4. 所以有兩個眾數：1 和 2
// 5. 回傳 [1, 2]

// 字串的眾數
$mode = collect(['apple', 'banana', 'apple', 'cherry', 'banana'])->mode(); // ['apple', 'banana']
// ↑ 逐行註解：
// 1. collect([...]) 建立字串集合
// 2. 統計每個字串出現次數：
//    - 'apple' 出現 2 次
//    - 'banana' 出現 2 次
//    - 'cherry' 出現 1 次
// 3. 'apple' 和 'banana' 都出現最多次（2 次）
// 4. 所以眾數是 ['apple', 'banana']
// 5. 回傳 ['apple', 'banana']

// 沒有眾數的情況（每個值都只出現一次）
$mode = collect([1, 2, 3, 4, 5])->mode(); // []
// ↑ 逐行註解：
// 1. collect([1, 2, 3, 4, 5]) 建立數字集合
// 2. 每個數字都只出現 1 次
// 3. 沒有「最常出現」的數值
// 4. 所以沒有眾數
// 5. 回傳空陣列 []

// 物件集合的眾數
class Student {
    public function __construct(public string $grade) {}
}
$students = collect([
    new Student('A'),
    new Student('B'),
    new Student('A'),
    new Student('C'),
    new Student('B'),
    new Student('A')
]);
$mode = $students->mode('grade'); // ['A']
// ↑ 逐行註解：
// 1. 建立學生物件集合，每個學生有 grade 屬性
// 2. ->mode('grade') 統計 grade 屬性值的出現次數
// 3. 統計結果：
//    - 'A' 出現 3 次
//    - 'B' 出現 2 次
//    - 'C' 出現 1 次
// 4. 'A' 出現最多次（3 次），所以眾數是 'A'
// 5. 回傳 ['A']
```
- *為什麼叫 "mode"？*
    - `mode` 是統計學術語，中文稱為「眾數」
    - 在統計學中，mode 代表「**一組數據中出現頻率最高的數值**」
    - 與 `mean（平均值）` 和 `median（中位數`）並稱為「**集中趨勢**」的三個重要指標
    - 生活例子：
        - **班上同學的身高**：165cm 出現最多次，165cm 就是眾數
        - **商店商品價格**：$100 的商品最多，$100 就是眾數
        - **學生考試分數**：80 分的人最多，80 分就是眾數

- 原理說明：
    - `mode() `會統計集合中每個值出現的次數
    - 找出出現次數最多的值（可能有多個）
    - 回傳**陣列格式**，因為可能有多個眾數
    - 如果每個值都只出現一次，則回傳**空陣列**

- 生活化比喻：
    就像統計班上同學最喜歡的顏色，如果紅色和藍色都各有 5 個人喜歡，
    其他顏色都少於 5 個人，那麼紅色和藍色就是「眾數」。

- 常見用途：
    - **統計分析**：找出最常見的數值
    - **資料分析**：了解資料的集中趨勢
    - **商業分析**：找出最受歡迎的產品或服務

---

## **multiply()**
- *說明*：將集合內容重複指定次數，回傳新集合。
- *語法*：`$collection->multiply($times)`
- *範例*：
```php
$users = collect([
    ['name' => 'User #1', 'email' => 'user1@example.com'],
    ['name' => 'User #2', 'email' => 'user2@example.com'],
])->multiply(3);
// [
//   ['name' => 'User #1', ...], ['name' => 'User #2', ...],
//   ['name' => 'User #1', ...], ['name' => 'User #2', ...],
//   ['name' => 'User #1', ...], ['name' => 'User #2', ...],
// ]
```

---

## **nth()**:名稱來自英文「n-th」，意思是 *「第 n 個」、「每隔 n 個」* 的意思。
- *說明*：每 n 個取一個，可指定`起始 offset`。
- *語法*：`$collection->nth($step, $offset = 0)`
- *範例*：
```php
$collection = collect(['a', 'b', 'c', 'd', 'e', 'f']);
// 建立一個集合，內容是 'a', 'b', 'c', 'd', 'e', 'f'

$collection->nth(4); // ['a', 'e']
// 取每 4 個元素取一個（預設從第 0 個開始）
// 也就是取 index 0、4 的元素，結果是 ['a', 'e']

$collection->nth(4, 1); // ['b', 'f']
// 取每 4 個元素取一個，從 index 1 開始
// 也就是取 index 1、5 的元素，結果是 ['b', 'f']
```

---

## **only()**
- *說明*：只保留指定 key 的項目。
- *語法*：`$collection->only($keys)`
- *範例*：
```php
$collection = collect([
    'product_id' => 1,
    'name' => 'Desk',
    'price' => 100,
    'discount' => false
]);
$filtered = $collection->only(['product_id', 'name']);
$filtered->all(); // ['product_id' => 1, 'name' => 'Desk']

```
---

## **pad()**
- *說明*：pad 方法會用`指定的值填充集合，直到集合長度達到指定大小`。這個方法行為與 PHP 的 array_pad` 相同。
- *語法*：`$collection->pad($size, $value)`
- *範例*：
```php
$collection = collect(['A', 'B', 'C']);
// 建立一個集合，內容是 'A', 'B', 'C'

$filtered = $collection->pad(5, 0);
// pad(5, 0)：將集合長度補到 5，不足的用 0 補在右邊
$filtered->all(); // ['A', 'B', 'C', 0, 0]

$filtered = $collection->pad(-5, 0);
// pad(-5, 0)：長度補到 5，不足的用 0 補在左邊（負數代表左邊補）
$filtered->all(); // [0, 0, 'A', 'B', 'C']
```
- *補充*：如果指定的大小小於等於原本長度，則不會補值。

---

## **partition()**
- *說明*：partition 方法可用來將集合依條件`分成兩組`，常與 PHP `陣列解構`一起用。
- *語法*：`$collection->partition(Closure $callback)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5, 6]);
// 建立一個集合，內容是 1~6

[$underThree, $equalOrAboveThree] = $collection->partition(function (int $i) {
    return $i < 3;
});
// partition 會依條件分成兩組
// $underThree 內容是小於 3 的 [1, 2]
// $equalOrAboveThree 內容是大於等於 3 的 [3, 4, 5, 6]

$underThree->all(); // [1, 2]
$equalOrAboveThree->all(); // [3, 4, 5, 6]
```

---

## **percentage()**
- *說明*：percentage 方法可快速計算集合中符合條件的項目`百分比`。
- *語法*：`$collection->percentage(Closure $callback, $precision = 2)`
- *範例*：
```php
$collection = collect([1, 1, 2, 2, 2, 3]);
// 建立一個集合

$percentage = $collection->percentage(fn (int $value) => $value === 1);
// 計算值為 1 的項目百分比，預設小數點兩位
// 結果：33.33

$percentage = $collection->percentage(fn (int $value) => $value === 1, precision: 3);
// precision: 3 代表小數點三位
// 結果：33.333
```

---

## **pipe()**
- *說明*：pipe 方法會`將集合傳入給定的 closure，並回傳 closure 執行結果`。適合需要對集合做複雜處理或轉換的場景。
- *語法*：`$collection->pipe(Closure $callback)`
- *範例*：
```php
// 基本用法：複雜的資料轉換
$collection = collect([1, 2, 3, 4, 5]);
$result = $collection->pipe(function (Collection $collection) {
    // 先過濾偶數
    $filtered = $collection->filter(fn($n) => $n % 2 === 0);
    // 再乘以 2
    $doubled = $filtered->map(fn($n) => $n * 2);
    // 最後加總
    return $doubled->sum();
});
// 結果：12 (2*2 + 4*2 = 4 + 8 = 12)

// 轉換為其他格式
$users = collect([
    ['name' => 'John', 'age' => 25],
    ['name' => 'Jane', 'age' => 30],
    ['name' => 'Bob', 'age' => 35]
]);
$result = $users->pipe(function (Collection $collection) {
    // 轉換為 JSON 格式
    return $collection->toJson();
});
// 結果：'[{"name":"John","age":25},{"name":"Jane","age":30},{"name":"Bob","age":35}]'

// 建立新的物件
$numbers = collect([1, 2, 3, 4, 5]);
$stats = $numbers->pipe(function (Collection $collection) {
    return (object) [
        'count' => $collection->count(),
        'sum' => $collection->sum(),
        'average' => $collection->avg(),
        'min' => $collection->min(),
        'max' => $collection->max()
    ];
});
// 結果：{count: 5, sum: 15, average: 3, min: 1, max: 5}

// 條件處理
$data = collect([1, 2, 3, 4, 5]);
$result = $data->pipe(function (Collection $collection) {
    if ($collection->count() > 3) {
        return $collection->take(3)->sum();
    } else {
        return $collection->sum();
    }
});
// 結果：6 (因為有5個元素 > 3，所以只取前3個：1+2+3=6)

// 與外部服務整合
$emails = collect(['user1@example.com', 'user2@example.com']);
$result = $emails->pipe(function (Collection $collection) {
    // 模擬發送郵件給所有用戶
    $collection->each(function ($email) {
        // Mail::to($email)->send(new WelcomeEmail());
        echo "發送郵件給: $email\n";
    });
    return "已發送 {$collection->count()} 封郵件";
});
// 結果：已發送 2 封郵件

// 資料驗證和清理
$rawData = collect(['  john  ', 'jane', '  bob  ']);
$cleanData = $rawData->pipe(function (Collection $collection) {
    return $collection
        ->map(fn($name) => trim($name))  // 去除空白
        ->filter(fn($name) => !empty($name))  // 過濾空值
        ->map(fn($name) => ucfirst($name))  // 首字母大寫
        ->values();  // 重新索引
});
// 結果：['John', 'Jane', 'Bob']
```
- *為什麼要用 pipe() 而不是直接呼叫？*
    - 當你需要對集合做「多步驟複雜處理」時
    - 當你需要「條件性處理」時
    - 當你需要「轉換為不同格式」時
    - 當你需要「與外部服務整合」時
    - 當你需要「建立新的資料結構」時

- 生活化比喻：
    - 就像水管（pipe），把水（資料）傳進去，經過處理後出來不同的東西。
    - 例如：自來水 → 過濾器 → 加熱器 → 熱茶

- *與直接呼叫的差異*：
    - **直接呼叫**：$collection->sum() - *簡單、直接*
    - `pipe()`：- *複雜、可組合*
    ```php
    $collection->pipe(fn($c) => $c->filter()->map()->sum()) 
    ```
---

## **pipeInto()**
- *說明*：pipeInto 方法會`建立指定類別的新實例，並將集合傳入建構子`。
- *語法*：`$collection->pipeInto($class)`
- *範例*：
```php
class ResourceCollection
{
    public function __construct(
        public Collection $collection,
    ) {}
}

$collection = collect([1, 2, 3]);
// 建立一個集合

$resource = $collection->pipeInto(ResourceCollection::class);
// 會建立 ResourceCollection 實例，並把集合傳進去

$resource->collection->all(); // [1, 2, 3]
```

---

## **pipeThrough()**
- *說明*：pipeThrough 方法會將`集合依序傳入多個 closure，回傳最終結果`。
- *語法*：`$collection->pipeThrough(array $callbacks)`
- *範例*：
```php
use Illuminate\Support\Collection;

$collection = collect([1, 2, 3]);
// 建立一個集合

$result = $collection->pipeThrough([
    function (Collection $collection) {
        return $collection->merge([4, 5]);
    },
    function (Collection $collection) {
        return $collection->sum();
    },
]);
// 先 merge([4,5])，再 sum()
// 結果：15
```

---

## **pluck()**
- *說明*：pluck 方法可取得集合中所有指定 key 的值。
- *語法*：`$collection->pluck($value, $key = null)`
- *範例*：
```php
$collection = collect([
    ['product_id' => 'prod-100', 'name' => 'Desk'],
    ['product_id' => 'prod-200', 'name' => 'Chair'],
]);
// 建立一個集合，每個元素是關聯陣列

$plucked = $collection->pluck('name');
// 取得所有 name 欄位
$plucked->all(); // ['Desk', 'Chair']

$plucked = $collection->pluck('name', 'product_id');
// 取得所有 name，並用 product_id 當 key
$plucked->all(); // ['prod-100' => 'Desk', 'prod-200' => 'Chair']

// 支援 dot notation 取巢狀值
$collection = collect([
    [
        'name' => 'Laracon',
        'speakers' => [
            'first_day' => ['Rosa', 'Judith'],
        ],
    ],
    [
        'name' => 'VueConf',
        'speakers' => [
            'first_day' => ['Abigail', 'Joey'],
        ],
    ],
]);
$plucked = $collection->pluck('speakers.first_day');
$plucked->all(); // [['Rosa', 'Judith'], ['Abigail', 'Joey']]

// 若 key 重複，後面的會覆蓋前面的
$collection = collect([
    ['brand' => 'Tesla',  'color' => 'red'],
    ['brand' => 'Pagani', 'color' => 'white'],
    ['brand' => 'Tesla',  'color' => 'black'],
    ['brand' => 'Pagani', 'color' => 'orange'],
]);
$plucked = $collection->pluck('color', 'brand');
$plucked->all(); // ['Tesla' => 'black', 'Pagani' => 'orange']
```

---

## **pop()**
- *說明*：pop 方法會`移除並回傳集合最後一個元素`，集合為空時回傳 null。可傳入數字一次移除多個。
- *語法*：`$collection->pop($count = 1)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
// 建立一個集合

$collection->pop(); // 5
// 移除並回傳最後一個元素

$collection->all(); // [1, 2, 3, 4]
// 剩下的集合

$collection = collect([1, 2, 3, 4, 5]);
$collection->pop(3); // collect([5, 4, 3])
// 一次移除三個，回傳一個新的集合

$collection->all(); // [1, 2]
// 剩下的集合
```

---

## **prepend()**
- *說明*：prepend 方法會`將元素加到集合最前面`，可指定 key。
- *語法*：`$collection->prepend($value, $key = null)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
// 建立一個集合

$collection->prepend(0);
// 在最前面加上 0
$collection->all(); // [0, 1, 2, 3, 4, 5]

$collection = collect(['one' => 1, 'two' => 2]);
$collection->prepend(0, 'zero');
// 在最前面加上 key 為 'zero' 的 0
$collection->all(); // ['zero' => 0, 'one' => 1, 'two' => 2]
```

---

## **pull()**
- *說明*：pull 方法會移`除並回傳指定 key 的元素`。
- *語法*：`$collection->pull($key)`
- *範例*：
```php
$collection = collect(['product_id' => 'prod-100', 'name' => 'Desk']);
// 建立一個集合

$collection->pull('name'); // 'Desk'
// 移除並回傳 key 為 'name' 的元素

$collection->all(); // ['product_id' => 'prod-100']
// 剩下的集合
```

---

## **push()**
- *說明*：push 方法會將元素加到集合`尾端`。
- *語法*：`$collection->push($value)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4]);
// 建立一個集合

$collection->push(5);
// 在尾端加上 5
$collection->all(); // [1, 2, 3, 4, 5]
```

---


## **put()**
- *說明*：put 方法會設定集合中指定 key 的值（若 key 不存在則新增）。
- *語法*：`$collection->put($key, $value)`
- *範例*：
```php
$collection = collect(['product_id' => 1, 'name' => 'Desk']);
// 建立一個集合

$collection->put('price', 100);
// 設定 key 為 'price' 的值為 100，若不存在則新增

$collection->all(); // ['product_id' => 1, 'name' => 'Desk', 'price' => 100]
```

---

## **random()**
- *說明*：random 方法會隨機回傳集合中的一個或多個元素。可接受數字或 closure 來動態決定取出數量。
- *語法*：`$collection->random($number = null)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
// 建立一個集合

$collection->random();
// 隨機回傳一個元素，例如 4

$random = $collection->random(3);
// 隨機回傳三個元素，回傳一個新的集合
$random->all(); // [2, 4, 5]（隨機）

// 若集合數量不足，會丟出例外

// 也可傳 closure，closure 會收到整個集合
use Illuminate\Support\Collection;
$random = $collection->random(fn (Collection $items) => min(10, count($items)));
$random->all(); // [1, 2, 3, 4, 5]（隨機）
// ↑ 逐行註解：
// 1. fn (Collection $items) => min(10, count($items)) 是一個箭頭函數
// 2. $items 參數是整個集合本身
// 3. count($items) 計算集合總數
// 4. min(10, count($items)) 取 10 和集合總數的較小值
// 5. 如果集合有 5 個元素，min(10, 5) = 5，所以會隨機取出全部 5 個
// 6. 如果集合有 15 個元素，min(10, 15) = 10，所以會隨機取出 10 個

// 更多 closure 範例：
$collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]);
$random = $collection->random(function (Collection $items) {
    $count = count($items);
    if ($count < 5) return $count;        // 少於 5 個就全部取出
    if ($count < 50) return 5;            // 5-49 個取 5 個
    return 10;                            // 50 個以上取 10 個
});
// 這裡會取出 5 個元素，因為集合有 15 個元素（5-49 範圍）

// 等價寫法（不使用 closure）：
$random = $collection->random(min(10, $collection->count()));
```
- *原理說明*：
    - `random()` 方法可以接受**數字**或 **closure** 作為參數
    - 傳入 closure 時，closure 會`收到整個集合作為參數`
    - closure 必須**回傳一個整數**，代表要隨機取出的元素數量
    - 這種設計讓你可以在執行時動態決定取出數量，而不是寫死一個數字

- *生活化比喻*：
    - 就像你去抽獎，原本規定只能抽 10 張，但如果獎品總數不到 10 張，就全部抽完。closure 就是這個「智能判斷」的邏輯。

- *常見用途*：
    - `限制最大取出數量`，避免記憶體問題
    - 根據集合大小`動態調整`取出數量
    - 實現複雜的取樣邏輯（如分層抽樣）

---

## **range()**
- *說明*：range 方法會產生一個`指定範圍`的整數集合。
- *語法*：`collect()->range($start, $end)`
- *範例*：
```php
$collection = collect()->range(3, 6);
// 產生 [3, 4, 5, 6]
$collection->all(); // [3, 4, 5, 6]
```

---

## **reduce()**
- *說明*：reduce 方法會將集合縮減為單一值，每次迭代都將前一次的結果帶入下一次。
- *語法*：`$collection->reduce(Closure $callback, $initial = null)`
- *範例*：
```php
$collection = collect([1, 2, 3]);
// 建立一個集合

total = $collection->reduce(function (?int $carry, int $item) {
    return $carry + $item;
});
// $carry 初始為 null，第一次執行時 $carry + $item = null + 1 = 1
// 之後依序累加，結果 6

$collection->reduce(function (int $carry, int $item) {
    return $carry + $item;
}, 4);
// 指定初始值 4，結果 10

// reduce 也會傳 key
$collection = collect([
    'usd' => 1400,
    'gbp' => 1200,
    'eur' => 1000,
]);
$ratio = [
    'usd' => 1,
    'gbp' => 1.37,
    'eur' => 1.22,
];
$collection->reduce(function (int $carry, int $value, string $key) use ($ratio) {
    return $carry + ($value * $ratio[$key]);
}, 0);
// 依 key 取匯率加總，結果 4264
```

---

## **reduceSpread()**
- *說明*：reduceSpread 方法會**將集合縮減為多個值（陣列），每次迭代都將前一次的多個結果帶入下一次**。與 `reduce()` 不同，`reduceSpread()` 可以同時維護多個累加值。
- *語法*：`$collection->reduceSpread(Closure $callback, ...$initial)`
- *範例*：
```php
// 基本用法：同時維護多個累加值
$numbers = collect([1, 2, 3, 4, 5]);
[$sum, $count, $max] = $numbers->reduceSpread(function (int $sum, int $count, int $max, int $number) {
    return [
        $sum + $number,    // 累加總和
        $count + 1,        // 累加數量
        max($max, $number) // 更新最大值
    ];
}, 0, 0, 0); // 初始值：sum=0, count=0, max=0
// 結果：$sum = 15, $count = 5, $max = 5

// 複雜範例：處理圖片批次，考慮積分限制
[$creditsRemaining, $batch] = Image::where('status', 'unprocessed')
    ->get()
    ->reduceSpread(function (int $creditsRemaining, Collection $batch, Image $image) {
        // 1. Image::where('status', 'unprocessed') 查詢未處理的圖片
        // 2. ->get() 取得圖片集合
        // 3. ->reduceSpread() 開始縮減處理
        // 4. callback 參數：
        //    - $creditsRemaining：剩餘積分（int）
        //    - $batch：已選中的圖片批次（Collection）
        //    - $image：目前處理的圖片（Image 物件）
        
        if ($creditsRemaining >= $image->creditsRequired()) {
            // 1. 檢查剩餘積分是否足夠處理這張圖片
            // 2. $image->creditsRequired() 取得這張圖片需要的積分
            
            $batch->push($image);
            // 1. 將這張圖片加入批次集合
            // 2. $batch 是 Collection，可以用 push() 方法
            
            $creditsRemaining -= $image->creditsRequired();
            // 1. 扣除這張圖片需要的積分
            // 2. 更新剩餘積分
        }
        
        return [$creditsRemaining, $batch];
        // 1. 回傳陣列，包含兩個值
        // 2. 這兩個值會在下一次迭代時分別傳入 callback 的前兩個參數
        // 3. 這就是「展開」的意思：陣列會被展開成多個參數
    }, $creditsAvailable, collect());
    // 1. $creditsAvailable：初始積分（第一個參數的初始值）
    // 2. collect()：空的集合（第二個參數的初始值）
    // 3. 這兩個初始值會傳入第一次 callback 的前兩個參數
    // 常見疑問：初始值為什麼比 callback 參數少一個？
    // --------------------------------------------------
    // 1. reduceSpread 的 callback 參數格式通常是：
    //    function ($a, $b, $item)
    //    - $a、$b 是你要維護的狀態（累加值）
    //    - $item 是每次迭代的集合元素（自動由 Collection 傳入）
    // 2. 初始值的數量 = 狀態參數的數量（不包含集合元素本身）
    //    - 例如 callback 有三個參數，最後一個是集合元素，
    //      你只要給前面兩個的初始值
    // 3. 集合元素（如 $image、$item、$student）不用給初始值，
    //    每次迭代時 Collection 會自動傳入
    // 4. 實例：
    //    [$a, $b] = $collection->reduceSpread(function ($a, $b, $item) {...}, $aInit, $bInit);
    //    只要給 $aInit, $bInit 兩個初始值
    // 5. 生活化比喻：
    //    - 你在記帳時，會準備「總金額」和「最大單筆」兩個欄位，
    //      但「每一筆交易」是自動一筆一筆來的，不用你準備
    //    - 所以你只要準備要累加的欄位初始值，資料本身會自動傳進來
    //
    // 結論：
    // - 你要維護幾個狀態，就給幾個初始值
    // - 集合元素不用給初始值，因為每次迭代自動傳入
    // - 所以 reduceSpread(..., $aInit, $bInit) 這樣的寫法完全正確！


// 購物車範例：同時計算總價和商品數量
$cart = collect([
    ['name' => 'Apple', 'price' => 10, 'quantity' => 2],
    ['name' => 'Banana', 'price' => 5, 'quantity' => 3],
    ['name' => 'Orange', 'price' => 8, 'quantity' => 1]
]);

[$totalPrice, $totalItems, $itemList] = $cart->reduceSpread(
    function (float $totalPrice, int $totalItems, array $itemList, array $item) {
        // 1. callback 參數：
        //    - $totalPrice：累計總價（float）
        //    - $totalItems：累計商品數量（int）
        //    - $itemList：商品清單（array）
        //    - $item：目前處理的商品（array）
        
        $itemTotal = $item['price'] * $item['quantity'];
        // 1. 計算這個商品的總價（單價 × 數量）
        
        return [
            $totalPrice + $itemTotal,                    // 累加總價
            $totalItems + $item['quantity'],             // 累加商品數量
            array_merge($itemList, [$item['name']])      // 加入商品名稱到清單
        ];
        // 1. 回傳陣列，包含三個更新後的值
        // 2. 這些值會在下一次迭代時傳入 callback 的前三個參數
    },
    0.0,    // 初始總價
    0,      // 初始商品數量
    []      // 初始商品清單
);
// 結果：$totalPrice = 43.0, $totalItems = 6, $itemList = ['Apple', 'Banana', 'Orange']

// 學生成績統計範例
$students = collect([
    ['name' => 'John', 'score' => 85],
    ['name' => 'Jane', 'score' => 92],
    ['name' => 'Bob', 'score' => 78],
    ['name' => 'Alice', 'score' => 95]
]);

[$passCount, $failCount, $averageScore, $topStudent] = $students->reduceSpread(
    function (int $passCount, int $failCount, float $totalScore, ?array $topStudent, array $student) {
        // 1. callback 參數：
        //    - $passCount：及格人數（int）
        //    - $failCount：不及格人數（int）
        //    - $totalScore：總分（float）
        //    - $topStudent：最高分學生（array 或 null）
        //    - $student：目前處理的學生（array）
        
        $score = $student['score'];
        $isPass = $score >= 60;
        
        return [
            $passCount + ($isPass ? 1 : 0),              // 更新及格人數
            $failCount + ($isPass ? 0 : 1),              // 更新不及格人數
            $totalScore + $score,                        // 累加總分
            $topStudent === null || $score > $topStudent['score'] ? $student : $topStudent  // 更新最高分學生
        ];
    },
    0,      // 初始及格人數
    0,      // 初始不及格人數
    0.0,    // 初始總分
    null    // 初始最高分學生
);
$averageScore = $averageScore / $students->count(); // 計算平均分
// 結果：$passCount = 4, $failCount = 0, $averageScore = 87.5, $topStudent = ['name' => 'Alice', 'score' => 95]
```
- *原理說明*：
    - `reduceSpread()` 與 `reduce()` 的主要差異：
    - `reduce()`：只能維護一個累加值。只能追蹤一個值，如總和。
    - `reduceSpread()`：可以同時維護多個累加值。可以同時追蹤多個相關的值。
    - callback 的回傳值必須是**陣列**，陣列元素數量要與參數**數量一致**
    - 陣列會被「展開」成多個參數，傳入下一次 callback
    - 初始值數量要與 callback 參數數量一致（除了最後一個參數是集合元素）

- *生活化比喻*：
    - 就像你在記帳時，同時記錄：
        - 總支出
        - 交易次數
        - 最大單筆支出
        - 支出清單
- 每次有新交易時，這四個數字都會同時更新。

- *常見用途*：
    - **複雜的統計計算**
    - **批次處理時**，需要維護多個狀態
    - **購物車計算**（總價、數量、清單）
    - **成績統計**（及格人數、平均分、最高分）

---

## **reject()**
- *說明*：reject 方法會過濾集合，`移除 callback 回傳 true 的項目`（與 `filter` 相反）。
- *語法*：`$collection->reject(Closure $callback)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4]);
// 建立一個集合

$filtered = $collection->reject(function (int $value, int $key) {
    return $value > 2;
});
// 移除大於 2 的項目
$filtered->all(); // [1, 2]
```

---

## **replace()**
- *說明*：replace 方法類似 `merge`，但會覆蓋數字 **key** 的值。
- *語法*：`$collection->replace($items)`
- *範例*：
```php
$collection = collect(['Taylor', 'Abigail', 'James']);
// 建立一個集合

$replaced = $collection->replace([1 => 'Victoria', 3 => 'Finn']);
// 1 號位被 'Victoria' 覆蓋，3 號位新增 'Finn'
$replaced->all(); // ['Taylor', 'Victoria', 'James', 'Finn']
```

---

## **replaceRecursive()**
- *說明*：replaceRecursive 方法會`遞迴覆蓋集合內容`，巢狀陣列也會被覆蓋。
- *語法*：`$collection->replaceRecursive($items)`
- *範例*：
```php
$collection = collect([
    'Taylor',
    'Abigail',
    [
        'James',
        'Victoria',
        'Finn'
    ]
]);
// 建立一個集合，第三個元素是陣列

$replaced = $collection->replaceRecursive([
    'Charlie',
    2 => [1 => 'King']
]);
// 第一個元素被 'Charlie' 覆蓋，第三個元素的第二個值被 'King' 覆蓋
$replaced->all(); // ['Charlie', 'Abigail', ['James', 'King', 'Finn']]
```

---

## **reverse()**
- *說明*：reverse 方法會`反轉`集合順序，保留原本的 key。
- *語法*：`$collection->reverse()`
- *範例*：
```php
$collection = collect(['a', 'b', 'c', 'd', 'e']);
// 建立一個集合

$reversed = $collection->reverse();
// 反轉順序，key 也反轉
$reversed->all();
/*
    [
        4 => 'e',
        3 => 'd',
        2 => 'c',
        1 => 'b',
        0 => 'a',
    ]
*/
```

---

## **search()**
- *說明*：search 方法會搜尋集合，`找到值時`，**回傳 key**，找不到回傳 false。
- *語法*：`$collection->search($value, $strict = false)`
- *範例*：
```php
$collection = collect([2, 4, 6, 8]);
// 建立一個集合

$collection->search(4); // 1
// 找到 4，回傳 key 1

collect([2, 4, 6, 8])->search('4', strict: true); // false
// 嚴格比對型別，'4'（字串）找不到

// 也可傳 closure
collect([2, 4, 6, 8])->search(function (int $item, int $key) {
    return $item > 5;
}); // 2
// 找到第一個大於 5 的值，回傳 key 2
```

---

## **select()**
- *說明*：select 方法會選出集合中`指定 key 的欄位`，類似 SQL SELECT。
- *語法*：`$collection->select($keys)`
- *範例*：
```php
$users = collect([
    ['name' => 'Taylor Otwell', 'role' => 'Developer', 'status' => 'active'],
    ['name' => 'Victoria Faith', 'role' => 'Researcher', 'status' => 'active'],
]);
// 建立一個集合

$users->select(['name', 'role']);
/*
    [
        ['name' => 'Taylor Otwell', 'role' => 'Developer'],
        ['name' => 'Victoria Faith', 'role' => 'Researcher'],
    ],
*/
```

---

## **shift()**
- *說明*：shift 方法會`移除，並回傳集合第一個元素`。可傳入數字一次移除多個。
- *語法*：`$collection->shift($count = 1)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
// 建立一個集合

$collection->shift(); // 1
// 移除並回傳第一個元素

$collection->all(); // [2, 3, 4, 5]
// 剩下的集合

$collection = collect([1, 2, 3, 4, 5]);
$collection->shift(3); // collect([1, 2, 3])
// 一次移除三個，回傳一個新的集合

$collection->all(); // [4, 5]
// 剩下的集合
```

---

## **shuffle()**
- *說明*：shuffle 方法會`隨機打亂`集合順序。
- *語法*：`$collection->shuffle()`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
// 建立一個集合

$shuffled = $collection->shuffle();
// 隨機打亂順序
$shuffled->all(); // [3, 2, 5, 1, 4]（隨機）
```

---

## **skip()**
- *說明*：skip 方法會`跳過前 n 個元素`，回傳**剩下**的集合。
- *語法*：`$collection->skip($count)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
// 建立一個集合

$collection = $collection->skip(4);
// 跳過前 4 個，剩下 [5, 6, 7, 8, 9, 10]
$collection->all(); // [5, 6, 7, 8, 9, 10]
```

---

## **skipUntil()**
- *說明*：skipUntil 方法會`跳過前面所有元素，直到條件成立`才開始回傳**剩下**的集合。
- *語法*：`$collection->skipUntil($valueOrCallback)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4]);
// 建立一個集合

$subset = $collection->skipUntil(function (int $item) {
    return $item >= 3;
});
// 跳過直到遇到大於等於 3，剩下 [3, 4]
$subset->all(); // [3, 4]

// 也可直接傳值
$subset = $collection->skipUntil(3);
// 跳過直到遇到 3，剩下 [3, 4]
$subset->all(); // [3, 4]
```

---

## **skipWhile()**
- *說明*：skipWhile 方法會`跳過前面所有元素，直到條件不成立`才開始回傳**剩下**的集合。
- *語法*：`$collection->skipWhile(Closure $callback)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4]);
// 建立一個集合

$subset = $collection->skipWhile(function (int $item) {
    return $item <= 3;
});
// 跳過小於等於 3 的，剩下 [4]
$subset->all(); // [4]
```

---

## **slice()**
- *說明*：slice 方法會`從指定索引開始`，回傳一段集合。
- *語法*：`$collection->slice($offset, $length = null)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
// 建立一個集合

$slice = $collection->slice(4);
// 從 index 4 開始，回傳 [5, 6, 7, 8, 9, 10]
$slice->all(); // [5, 6, 7, 8, 9, 10]

$slice = $collection->slice(4, 2);
// 從 index 4 開始，取 2 個
$slice->all(); // [5, 6]
```

---

## **sliding()**
- *說明*：sliding 方法會產生「`滑動視窗`」的分組集合。
- *語法*：`$collection->sliding($size, $step = 1)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
// 建立一個集合

$chunks = $collection->sliding(2);
// 每 2 個為一組，視窗滑動 1 格
$chunks->toArray(); // [[1, 2], [2, 3], [3, 4], [4, 5]]

// 可搭配 eachSpread 使用
// $transactions->sliding(2)->eachSpread(function (Collection $previous, Collection $current) {
//     $current->total = $previous->total + $current->amount;
// });

$chunks = $collection->sliding(3, step: 2);
// 每 3 個為一組，視窗每次滑動 2 格
$chunks->toArray(); // [[1, 2, 3], [3, 4, 5]]
```

---

## **sole()**
- *說明*：sole 方法會`回傳唯一符合條件的元素`，若沒有或超過一個會丟例外。
- *語法*：`$collection->sole($keyOrCallback = null, $operator = null, $value = null)`
- *範例*：
```php
collect([1, 2, 3, 4])->sole(function (int $value, int $key) {
    return $value === 2;
}); // 2

$collection = collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 100],
]);
$collection->sole('product', 'Chair');
// 回傳唯一符合條件的元素

$collection = collect([
    ['product' => 'Desk', 'price' => 200],
]);
$collection->sole();
// 只有一個元素時直接回傳
```
- *補充*：找不到會丟 `ItemNotFoundException`，超過一個會丟 `MultipleItemsFoundException`。

---

## **sort()**
- *說明*：sort 方法會排序集合，**保留原本的 key**。
- *語法*：`$collection->sort($callback = null)`
- *範例*：
```php
$collection = collect([5, 3, 1, 2, 4]);
// 建立一個集合

$sorted = $collection->sort();
// 排序後 key 仍保留
$sorted->values()->all(); // [1, 2, 3, 4, 5]

// 可傳 callback 自訂排序
```

---

## **sortBy()**
- *說明*：sortBy 方法會`依指定 key 排序集合`，保留原本的 key。
- *語法*：`$collection->sortBy($key, $options = SORT_REGULAR, $descending = false)`
- *範例*：
```php
$collection = collect([
    ['name' => 'Desk', 'price' => 200],
    ['name' => 'Chair', 'price' => 100],
    ['name' => 'Bookcase', 'price' => 150],
]);
// 建立一個集合

$sorted = $collection->sortBy('price');
$sorted->values()->all();
/*
    [
        ['name' => 'Chair', 'price' => 100],
        ['name' => 'Bookcase', 'price' => 150],
        ['name' => 'Desk', 'price' => 200],
    ]
*/

// 可傳 sort flag 或 closure
```

---

## **sortByDesc()**
- *說明*：sortByDesc 方法同 sortBy，但反向排序。
- *語法*：`$collection->sortByDesc($key, $options = SORT_REGULAR)`
- *範例*：
```php
// 用法同 sortBy，只是結果反向
```

---

## **sortDesc()**
- *說明*：sortDesc 方法會`反向排序`集合。
- *語法*：`$collection->sortDesc()`
- *範例*：
```php
$collection = collect([5, 3, 1, 2, 4]);
$sorted = $collection->sortDesc();
$sorted->values()->all(); // [5, 4, 3, 2, 1]
```

---

## **sortKeys()**
- *說明*：sortKeys 方法會依 `key` 排序集合。
- *語法*：`$collection->sortKeys($options = SORT_REGULAR, $descending = false)`
- *範例*：
```php
$collection = collect([
    'id' => 22345,
    'first' => 'John',
    'last' => 'Doe',
]);
$sorted = $collection->sortKeys();
$sorted->all();
/*
    [
        'first' => 'John',
        'id' => 22345,
        'last' => 'Doe',
    ]
*/
```

---

## **sortKeysDesc()**
- *說明*：sortKeysDesc 方法同 sortKeys，但反向排序。
- *語法*：`$collection->sortKeysDesc($options = SORT_REGULAR)`
- *範例*：
```php
// 用法同 sortKeys，只是結果反向
```

---

## **sortKeysUsing()**
- *說明*：sortKeysUsing 方法會用`自訂 callback` 排序 key。
- *語法*：`$collection->sortKeysUsing($callback)`
- *範例*：
```php
// 建立一個關聯陣列集合，key 分別是 'ID'、'first'、'last'
$collection = collect([
    'ID' => 22345,
    'first' => 'John',
    'last' => 'Doe',
]);
// 用 sortKeysUsing() 依自訂規則排序 key，這裡用 PHP 內建的 strnatcasecmp（不分大小寫的字串自然排序）
$sorted = $collection->sortKeysUsing('strnatcasecmp');
// 取得排序後的結果，key 會依字母順序（不分大小寫）排列
$sorted->all();
/*
    [
        'first' => 'John', // 'first' 的 f 在字母表最前面
        'ID' => 22345,     // 'ID' 的 i 在中間
        'last' => 'Doe',   // 'last' 的 l 在最後
    ]
*/
// callback 必須回傳整數（<0, =0, >0），你也可以自己寫 callback，例如：
// $collection->sortKeysUsing(function($a, $b) {
//     return strcmp($a, $b); // 這是區分大小寫的字串比較
// });
// 小於 0：$a 排在 $b 前面；等於 0：$a 和 $b 相等；大於 0：$a 排在 $b 後面
```
---

## **splice()**
- *說明*：splice 方法會`移除，並回傳集合中一段元素`，可指定起始與長度，也可插入新元素。
- *語法*：`$collection->splice($offset, $length = null, $replacement = [])`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
$chunk = $collection->splice(2);
// 從 index 2 開始移除到結尾，回傳 [3, 4, 5]
$chunk->all(); // [3, 4, 5]
$collection->all(); // [1, 2]

$collection = collect([1, 2, 3, 4, 5]);
$chunk = $collection->splice(2, 1);
// 從 index 2 開始移除 1 個，回傳 [3]
$chunk->all(); // [3]
$collection->all(); // [1, 2, 4, 5]

$collection = collect([1, 2, 3, 4, 5]);
$chunk = $collection->splice(2, 1, [10, 11]);
// 從 index 2 開始移除 1 個，並插入 10, 11
$chunk->all(); // [3]
$collection->all(); // [1, 2, 10, 11, 4, 5]
```
- *為什麼叫 splice？*
    - splice 英文意思是「**剪接」、「接合**」，常用於繩子、膠卷、DNA 等領域，表示剪掉一段再接上新東西。
    - 在程式設計裡，splice 代表「**剪掉一段元素，並可插入新元素**」，原本的集合會被改變。
- *生活化比喻*：
  - 像剪膠卷，把中間一段剪掉，再接上另一段膠卷
  - 像剪繩子，把一段剪下來，再接新繩子進去
  - 生物學的 RNA **剪接（splicing）**也是這個意思
- 所以 `splice()` 這個方法就是「剪接」的彈性操作，既能**移除元素，也能插入新元素**

---

## **split()**
- *說明*：split 方法會將集合`平均分成指定數量`的群組。
- *語法*：`$collection->split($numberOfGroups)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
$groups = $collection->split(3);
$groups->all(); // [[1, 2], [3, 4], [5]]
```

---

## **splitIn()**
- *說明*：splitIn 方法會將集合分成指定數量的群組，`前面的群組會盡量填滿`。
- *語法*：`$collection->splitIn($numberOfGroups)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
$groups = $collection->splitIn(3);
$groups->all(); // [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10]]
```

---

## **sum()**
- *說明*：sum 方法會回傳集合所有項目的總和。
- *語法*：`$collection->sum($keyOrCallback = null)`
- *範例*：
```php
collect([1, 2, 3, 4, 5])->sum(); // 15
// 直接加總所有數字

$collection = collect([
    ['name' => 'JavaScript: The Good Parts', 'pages' => 176],
    ['name' => 'JavaScript: The Definitive Guide', 'pages' => 1096],
]);
$collection->sum('pages'); // 1272
// 指定 key，會加總每個元素的 pages 欄位

$collection = collect([
    ['name' => 'Chair', 'colors' => ['Black']],
    ['name' => 'Desk', 'colors' => ['Black', 'Mahogany']],
    ['name' => 'Bookcase', 'colors' => ['Red', 'Beige', 'Brown']],
]);
$collection->sum(function (array $product) {
    return count($product['colors']);
}); // 6
// 傳 closure，回傳每個元素的顏色數量，最後加總
```

---

## **take()**
- *說明*：take 方法會回傳`前 n 個元素`的新集合。
- *語法*：`$collection->take($number)`
- *範例*：
```php
$collection = collect([0, 1, 2, 3, 4, 5]);
$chunk = $collection->take(3);
$chunk->all(); // [0, 1, 2]
// 取前三個

$chunk = $collection->take(-2);
$chunk->all(); // [4, 5]
// 傳負數則取最後 n 個
```

---

## **takeUntil()**
- *說明*：takeUntil 方法會回傳`從頭開始，直到條件成立前`的所有元素。
- *語法*：`$collection->takeUntil($valueOrCallback)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4]);
$subset = $collection->takeUntil(function (int $item) {
    return $item >= 3;
});
$subset->all(); // [1, 2]
// 直到遇到大於等於 3 為止

$subset = $collection->takeUntil(3);
$subset->all(); // [1, 2]
// 直到遇到 3 為止
```

---

## **takeWhile()**
- *說明*：takeWhile 方法會回傳`從頭開始，直到條件不成立`為止的所有元素。
- *語法*：`$collection->takeWhile(Closure $callback)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4]);
$subset = $collection->takeWhile(function (int $item) {
    return $item < 3;
});
$subset->all(); // [1, 2]
// 只要小於 3 就取，遇到 3 就停止
```

---

## **tap()**
- *說明*：tap 方法會把集合傳給 `callback 做副作用`，然後回傳原集合本身。
- *語法*：`$collection->tap(Closure $callback)`
- *範例*：
```php
collect([2, 4, 3, 1, 5])
    // 建立一個集合，內容是 [2, 4, 3, 1, 5]
    ->sort()
    // 對集合做排序，結果變成 [1, 2, 3, 4, 5]
    ->tap(function (Collection $collection) {
        // tap() 會把排序後的集合傳進這個 callback
        Log::debug('Values after sorting', $collection->values()->all());
        // 這行只是做副作用（例如寫 log），不會改變集合內容
        // 這裡是把排序後的值寫進 debug log
    })
    ->shift();
```
- `shift()` 會**移除並回傳集合的第一個元素**（這裡是 1）
- 剩下的集合是 [2, 3, 4, 5]
- `tap()` 的用途：適合用來「**在鏈式操作中插入副作用**」，例如 debug、log、通知等，不會改變集合本身，callback 執行完後會回傳原集合，讓你可以繼續鏈式操作。
- *生活化比喻*：就像你在流水線上檢查產品品質（做個記錄），但產品本身不會被改變，還是會繼續往下流動。
- 這裡 tap 只是用來 debug，不會影響集合本身
- *補充說明*：
    - `tap()` 之所以「**回傳原集合本身**」，是為了讓你在鏈式操作中插入副作用（如 debug、log、通知等）後，
    - 還能繼續往下操作，不會中斷資料流程。
    - 如果 `tap()` 不回傳原集合，鏈式操作就會斷掉，無法繼續寫下去。
    - 這種設計讓你可以在任何地方插入 `tap()`，做完副作用後，資料還能原封不動地傳下去。
- *生活化比喻*：
    - 就像工廠流水線上的「**檢查站**」，檢查站（tap）會記錄產品資訊（副作用），
    - 但產品本身不會被改變，還是會繼續往下流到下一站。
    - 如果檢查站不把產品傳下去，後面的流程就斷掉了！
    - 所以 `tap()` 的設計就是「**做完副作用，然後把資料原封不動地傳下去，讓你繼續用**」。

---

## **times()**
- *說明*：times 是`靜態方法`，會呼叫 closure n 次，產生新集合。
- *語法*：`Collection::times($number, Closure $callback)`
- *範例*：
```php
$collection = Collection::times(10, function (int $number) {
    return $number * 9;
});
$collection->all(); // [9, 18, 27, 36, 45, 54, 63, 72, 81, 90]
```

---

## **toArray()**
- *說明*：toArray 方法會把集合轉成`純 PHP 陣列`，巢狀物件也會轉成陣列。
- *語法*：`$collection->toArray()`
- *範例*：
```php
$collection = collect(['name' => 'Desk', 'price' => 200]);
$collection->toArray();
/*
    [
        ['name' => 'Desk', 'price' => 200],
    ]
*/
// 若要取得原始陣列，請用 all()
```
- *補充說明*：
    - 呼叫 `toArray()` 之後，回傳的是純 PHP 陣列，這個變數就不能再用 **Collection** 的方法（如 map、filter、sum 等），只能用陣列的語法和函式。
- 範例：
    ```php
    $collection = collect([1, 2, 3]);
    $array = $collection->toArray(); // $array 是純陣列
    $array->map(fn($v) => $v * 2); // ❌ 錯誤！陣列沒有 map 方法
    array_map(fn($v) => $v * 2, $array); // 正確用法
    ```
    - 如果還想用 Collection 方法，請保留 Collection 物件，不要轉陣列。
---

## **toJson()**
- *說明*：toJson 方法會把集合轉成 `JSON 字串`。
- *語法*：`$collection->toJson()`
- *範例*：
```php
$collection = collect(['name' => 'Desk', 'price' => 200]);
$collection->toJson(); // '{"name":"Desk", "price":200}'
```

---

## **transform()**
- *說明*：transform 方法會用 callback 處理每個元素，並`直接改變集合本身`。
- *語法*：`$collection->transform(Closure $callback)`
- *範例*：
```php
$collection = collect([1, 2, 3, 4, 5]);
$collection->transform(function (int $item, int $key) {
    return $item * 2;
});
$collection->all(); // [2, 4, 6, 8, 10]
// 注意：transform 會直接改變原集合，若要產生新集合請用 map
```

---

## **undot()**
- *說明*：undot 方法會把 dot notation 的一`維集合還原成多維集合`。
- *語法*：`$collection->undot()`
- *範例*：
```php
$person = collect([
    'name.first_name' => 'Marie',
    'name.last_name' => 'Valentine',
    'address.line_1' => '2992 Eagle Drive',
    'address.line_2' => '',
    'address.suburb' => 'Detroit',
    'address.state' => 'MI',
    'address.postcode' => '48219'
]);
$person = $person->undot();
$person->toArray();
/*
    [
        "name" => [
            "first_name" => "Marie",
            "last_name" => "Valentine",
        ],
        "address" => [
            "line_1" => "2992 Eagle Drive",
            "line_2" => "",
            "suburb" => "Detroit",
            "state" => "MI",
            "postcode" => "48219",
        ],
    ]
*/
```

---

## **union()**
- *說明*：union 方法會把`給定陣列加到集合後面，key 重複時保留原集合的值`。
- *語法*：`$collection->union($array)`
- *範例*：
```php
$collection = collect([1 => ['a'], 2 => ['b']]);
$union = $collection->union([3 => ['c'], 1 => ['d']]);
$union->all(); // [1 => ['a'], 2 => ['b'], 3 => ['c']]
```
- *為什麼叫 union？*
    - union 英文意思是「**聯合、合併**」，在數學集合論裡，union（`聯集`）指的是「**把兩個集合的所有元素合在一起，不重複**」。
    - 在程式設計裡，union 通常代表「**合併兩個集合/陣列**」，Laravel 的 `union()` 就是把給定陣列加到原集合後面。
    - 差異：`merge()` *key 重複時會覆蓋*，`union()` *key 重複時保留原集合的值*。
- *生活化比喻*：
    - 像兩個班級合併成一個大班級（聯集），如果有同名同姓的同學，原班的同學優先保留。

---

## **unique()**
- *說明*：unique 方法會回傳集合中`唯一的項目`，保留原 key。
- *語法*：`$collection->unique($keyOrCallback = null)`
- *範例*：
```php
$collection = collect([1, 1, 2, 2, 3, 4, 2]);
$unique = $collection->unique();
$unique->values()->all(); // [1, 2, 3, 4]

$collection = collect([
    ['name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'],
    ['name' => 'iPhone 5', 'brand' => 'Apple', 'type' => 'phone'],
    ['name' => 'Apple Watch', 'brand' => 'Apple', 'type' => 'watch'],
    ['name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'],
    ['name' => 'Galaxy Gear', 'brand' => 'Samsung', 'type' => 'watch'],
]);
$unique = $collection->unique('brand');
$unique->values()->all();
/*
    [
        ['name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'],
        ['name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'],
    ]
*/
// 也可傳 closure 決定唯一性
$unique = $collection->unique(function (array $item) {
    return $item['brand'].$item['type'];
});
$unique->values()->all();
/*
    [
        ['name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'],
        ['name' => 'Apple Watch', 'brand' => 'Apple', 'type' => 'watch'],
        ['name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'],
        ['name' => 'Galaxy Gear', 'brand' => 'Samsung', 'type' => 'watch'],
    ]
*/

```
- *預設*為寬鬆比對，嚴格比對請用 `uniqueStrict`
- *補充說明*：
- `unique()` 只保留第一次出現的值，**保留原本的 key（index）**，所以結果的 key 會跳號，不會有空的 index。
- *範例*：
    ```php
    $collection = collect([1, 1, 2, 2, 3, 4, 2]);
    $unique = $collection->unique();
    $unique->all(); - [0 => 1, 2 => 2, 4 => 3, 5 => 4]
    ```
- *如果想要 key 重新編號為連續數字，請再接 `values()`*：
    ```php
    $unique = $collection->unique()->values();
    $unique->all(); // [1, 2, 3, 4]
    ```

---

## **uniqueStrict()**
- *說明*：uniqueStrict 方法同 unique，但用嚴格比對（===）。
- *語法*：`$collection->uniqueStrict($keyOrCallback = null)`

---

## **unless()**
- *說明*：unless 方法會在`條件為 false 時執行` callback，`否則執行第二個 callback（可選）`。
- *語法*：`$collection->unless($value, Closure $callback, Closure $default = null)`
- *範例*：
```php
$collection = collect([1, 2, 3]);

$collection->unless(true, function (Collection $collection, bool $value) {
    return $collection->push(4);
});
// unless 的第一個參數是 true，代表「條件成立」
// unless 的設計是「只有條件為 false 時才執行 callback」
// 這裡條件為 true，所以 callback 不會被執行，集合內容不變

$collection->unless(false, function (Collection $collection, bool $value) {
    return $collection->push(5);
});
// unless 的第一個參數是 false，代表「條件不成立」
// 這時候會執行 callback，把 5 加到集合尾端
// 集合內容變成 [1, 2, 3, 5]

$collection->all(); // [1, 2, 3, 5]
// 取得目前集合內容，結果是 [1, 2, 3, 5]

// 也可傳第二個 callback，條件為 true 時執行
$collection = collect([1, 2, 3]);
// 建立一個集合，內容是 [1, 2, 3]

$collection->unless(true, function (Collection $collection, bool $value) {
    return $collection->push(4);
}, function (Collection $collection, bool $value) {
    return $collection->push(5);
});
// unless 的第一個參數是 true，代表「條件成立」
// 這時候會執行第二個 callback（也就是條件為 true 時的處理）
// 所以會把 5 加到集合尾端
// 集合內容變成 [1, 2, 3, 5]

$collection->all(); // [1, 2, 3, 5]
// 取得目前集合內容，結果是 [1, 2, 3, 5]

// 進階範例：
// 1. 根據集合是否為空來決定行為
$users = collect([]);
$users->unless($users->isNotEmpty(), function ($collection) {
    // 如果集合是空的，新增一個預設用戶
    $collection->push(['name' => 'Guest']);
});
$users->all(); // [['name' => 'Guest']]

// 2. 搭配 Eloquent 查詢條件
$users = User::where('active', true)->get();
$users->unless($users->count() > 0, function ($collection) {
    // 如果沒有任何啟用用戶，寄出警告通知
    Notification::send(Admin::all(), new NoActiveUserWarning());
});

// 3. unless() + 第二個 callback（條件為 true 時的處理）
$collection = collect([1, 2, 3]);
$collection->unless(
    $collection->contains(2),
    function ($c) { $c->push(99); }, // 沒有 2 時加 99
    function ($c) { $c->push(100); } // 有 2 時加 100
);
$collection->all(); // [1, 2, 3, 100]

// 4. unless() 搭配外部變數
$isAdmin = false;
$actions = collect(['view']);
$actions->unless($isAdmin, function ($c) {
    $c->push('request_approval');
});
$actions->all(); // ['view', 'request_approval']

// 5. unless() 搭配鏈式操作
$numbers = collect([1, 2, 3, 4, 5]);
$numbers
    ->filter(fn($n) => $n % 2 === 0)
    ->unless($numbers->count() > 10, function ($c) {
        $c->push(100);
    });
$numbers->all(); // [1, 2, 3, 4, 5, 100]

```
- *小結*：`unless() `適合用在「**條件不成立時才做某事**」的情境，可搭配各種判斷、Eloquent、外部變數、鏈式操作，讓程式更直覺。

---

## **unlessEmpty()**
- *說明*：unlessEmpty 是 whenNotEmpty 的別名。

## **unlessNotEmpty()**
- *說明*：unlessNotEmpty 是 whenEmpty 的別名。

---

## **unwrap()**
- *說明*：unwrap 是`靜態方法`，會**取出`集合包裝`的內容**。
- *語法*：`Collection::unwrap($value)`
- *範例*：
```php
Collection::unwrap(collect('John Doe')); // ['John Doe']
Collection::unwrap(['John Doe']); // ['John Doe']
Collection::unwrap('John Doe'); // 'John Doe'
```

---

## **value()**
- *說明*：value 方法會取得集合`第一個元素的指定 key 值`。
- *語法*：`$collection->value($key)`
- *範例*：
```php
$collection = collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Speaker', 'price' => 400],
]);
$value = $collection->value('price'); // 200
```

---

## **values()**
- *說明*：values 方法會`重設集合的 key`，回傳新集合。
- *語法*：`$collection->values()`
- *範例*：
```php
$collection = collect([
    10 => ['product' => 'Desk', 'price' => 200],
    11 => ['product' => 'Desk', 'price' => 200],
]);
$values = $collection->values();
$values->all();
/*
    [
        0 => ['product' => 'Desk', 'price' => 200],
        1 => ['product' => 'Desk', 'price' => 200],
    ]
*/
```

---

## **when()**
- *說明*：when 方法會在條件為 `true` 時執行 callback，否則執行第二個 callback（可選）。
- *語法*：`$collection->when($value, Closure $callback, Closure $default = null)`
- *範例*：
```php
$collection = collect([1, 2, 3]);
$collection->when(true, function (Collection $collection, bool $value) {
    return $collection->push(4);
});
$collection->when(false, function (Collection $collection, bool $value) {
    return $collection->push(5);
});
$collection->all(); // [1, 2, 3, 4]

// 也可傳第二個 callback，條件為 false 時執行
$collection = collect([1, 2, 3]);
$collection->when(false, function (Collection $collection, bool $value) {
    return $collection->push(4);
}, function (Collection $collection, bool $value) {
    return $collection->push(5);
});
$collection->all(); // [1, 2, 3, 5]
```

---

## **whenEmpty()**
- *說明*：whenEmpty 方法會在`集合為空時執行` callback，否則執行第二個 callback（可選）。
- *語法*：`$collection->whenEmpty(Closure $callback, Closure $default = null)`
- *範例*：
```php
$collection = collect(['Michael', 'Tom']);
$collection->whenEmpty(function (Collection $collection) {
    return $collection->push('Adam');
});
$collection->all(); // ['Michael', 'Tom']

$collection = collect();
$collection->whenEmpty(function (Collection $collection) {
    return $collection->push('Adam');
});
$collection->all(); // ['Adam']

// 也可傳第二個 callback，集合不為空時執行
$collection = collect(['Michael', 'Tom']);
$collection->whenEmpty(function (Collection $collection) {
    return $collection->push('Adam');
}, function (Collection $collection) {
    return $collection->push('Taylor');
});
$collection->all(); // ['Michael', 'Tom', 'Taylor']
```

---

## **whenNotEmpty()**
- *說明*：whenNotEmpty 方法會在`集合不為空時執行` callback，否則執行第二個 callback（可選）。
- *語法*：`$collection->whenNotEmpty(Closure $callback, Closure $default = null)`
- *範例*：
```php
$collection = collect(['Michael', 'Tom']);
$collection->whenNotEmpty(function (Collection $collection) {
    return $collection->push('Adam');
});
$collection->all(); // ['Michael', 'Tom', 'Adam']

$collection = collect();
$collection->whenNotEmpty(function (Collection $collection) {
    return $collection->push('Adam');
});
$collection->all(); // []

// 也可傳第二個 callback，集合為空時執行
$collection = collect();
$collection->whenNotEmpty(function (Collection $collection) {
    return $collection->push('Adam');
}, function (Collection $collection) {
    return $collection->push('Taylor');
});
$collection->all(); // ['Taylor']
```

---

## **where()**
- *說明*：where 方法會`依 key/value 過濾集合`，預設**寬鬆比對**。
- *語法*：`$collection->where($key, $operator = null, $value = null)`
- *範例*：
```php
$collection = collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 100],
    ['product' => 'Bookcase', 'price' => 150],
    ['product' => 'Door', 'price' => 100],
]);
$filtered = $collection->where('price', 100);
$filtered->all();
/*
    [
        ['product' => 'Chair', 'price' => 100],
        ['product' => 'Door', 'price' => 100],
    ]
*/

// 可傳運算子
$collection = collect([
    ['name' => 'Jim', 'deleted_at' => '2019-01-01 00:00:00'],
    ['name' => 'Sally', 'deleted_at' => '2019-01-02 00:00:00'],
    ['name' => 'Sue', 'deleted_at' => null],
]);
$filtered = $collection->where('deleted_at', '!=', null);
$filtered->all();
/*
    [
        ['name' => 'Jim', 'deleted_at' => '2019-01-01 00:00:00'],
        ['name' => 'Sally', 'deleted_at' => '2019-01-02 00:00:00'],
    ]
*/
```

---

## **whereStrict()**
- *說明*：whereStrict 方法同 where，但用嚴格比對（===）。

---

## **whereBetween()**
- *說明*：whereBetween 方法會過濾指定 key 值在`範圍內`的元素。
- *語法*：`$collection->whereBetween($key, array $range)`
- *範例*：
```php
$collection = collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 80],
    ['product' => 'Bookcase', 'price' => 150],
    ['product' => 'Pencil', 'price' => 30],
    ['product' => 'Door', 'price' => 100],
]);
$filtered = $collection->whereBetween('price', [100, 200]);
$filtered->all();
/*
    [
        ['product' => 'Desk', 'price' => 200],
        ['product' => 'Bookcase', 'price' => 150],
        ['product' => 'Door', 'price' => 100],
    ]
*/
```

---

## **whereIn()**
- *說明*：whereIn 方法會過濾指定 key 值在`給定陣列內`的元素。
- *語法*：`$collection->whereIn($key, array $values)`
- *範例*：
```php
$collection = collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 100],
    ['product' => 'Bookcase', 'price' => 150],
    ['product' => 'Door', 'price' => 100],
]);
$filtered = $collection->whereIn('price', [150, 200]);
$filtered->all();
/*
    [
        ['product' => 'Desk', 'price' => 200],
        ['product' => 'Bookcase', 'price' => 150],
    ]
*/

```
- *`whereBetween()` vs `whereIn()` 差異說明*：
--------------------------------------------------
1. `whereBetween($key, [$min, $max])`：用於「**連續範圍**」過濾，保留 key 值在 [$min, $max] 之間的元素。
    例：`$collection->whereBetween('price', [100, 200])`; 
    - 100 ≤ price ≤ 200
2. `whereIn($key, $valuesArray)`：用於「**多個指定值**」過濾，保留 key 值等於陣列中任一個的元素。
    例：`$collection->whereIn('price', [150, 200])`; 
    - price 等於 150 或 200
3. 生活化比喻：
   - **whereBetween**：像是在找「分數在 60~80 分之間」的學生
   - **whereIn**：像是在找「分數是 60、70、90」這幾個特定分數的學生
4. 小結：whereBetween 用於「連續範圍」；whereIn 用於「多個指定值」
---

## **whereInStrict()**
- *說明*：whereInStrict 方法同 whereIn，但用嚴格比對（===）。

---

## **whereInstanceOf()**
- *說明*：whereInstanceOf 方法會`過濾指定類別的元素`。
- *語法*：`$collection->whereInstanceOf($class)`
- *範例*：
```php
use App\Models\User;
use App\Models\Post;

$collection = collect([
    new User,
    new User,
    new Post,
]);
$filtered = $collection->whereInstanceOf(User::class);
$filtered->all(); // [App\\Models\\User, App\\Models\\User]
```

---

## **whereNotBetween()**
- *說明*：whereNotBetween 方法會過濾指定 key 值不在範圍內的元素。
- *語法*：`$collection->whereNotBetween($key, array $range)`
- *範例*：
```php
$collection = collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 80],
    ['product' => 'Bookcase', 'price' => 150],
    ['product' => 'Pencil', 'price' => 30],
    ['product' => 'Door', 'price' => 100],
]);
$filtered = $collection->whereNotBetween('price', [100, 200]);
$filtered->all();
/*
    [
        ['product' => 'Chair', 'price' => 80],
        ['product' => 'Pencil', 'price' => 30],
    ]
*/
```

---

## **whereNotIn()**
- *說明*：whereNotIn 方法會過濾指定 key 值不在給定陣列內的元素。
- *語法*：`$collection->whereNotIn($key, array $values)`
- *範例*：
```php
$collection = collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 100],
    ['product' => 'Bookcase', 'price' => 150],
    ['product' => 'Door', 'price' => 100],
]);
$filtered = $collection->whereNotIn('price', [150, 200]);
$filtered->all();
/*
    [
        ['product' => 'Chair', 'price' => 100],
        ['product' => 'Door', 'price' => 100],
    ]
*/
```

---

## **whereNotInStrict()**
- *說明*：whereNotInStrict 方法同 whereNotIn，但用嚴格比對（===）。

---

## **whereNotNull()**
- *說明*：whereNotNull 方法會回傳指定 key 不為 null 的元素。
- *語法*：`$collection->whereNotNull($key)`
- *範例*：
```php
$collection = collect([
    ['name' => 'Desk'],
    ['name' => null],
    ['name' => 'Bookcase'],
]);
$filtered = $collection->whereNotNull('name');
$filtered->all();
/*
    [
        ['name' => 'Desk'],
        ['name' => 'Bookcase'],
    ]
*/
```

---


## **whereNull()**
- *說明*：whereNull 方法會回傳指定 key 為 null 的元素。
- *語法*：`$collection->whereNull($key)`
- *範例*：
```php
$collection = collect([
    ['name' => 'Desk'],
    ['name' => null],
    ['name' => 'Bookcase'],
]);
// 建立一個集合

$filtered = $collection->whereNull('name');
// 過濾出 name 為 null 的元素

$filtered->all();
/*
    [
        ['name' => null],
    ]
*/
```

---

## **wrap()**
- *說明*：wrap 是`靜態方法`，會將給定值**包裝**成 Collection 實例。
- *語法*：`Collection::wrap($value)`
- *範例*：
```php
use Illuminate\Support\Collection;

$collection = Collection::wrap('John Doe');
// 單一值會被包成集合 ['John Doe']
$collection->all(); // ['John Doe']

$collection = Collection::wrap(['John Doe']);
// 陣列會直接包成集合
$collection->all(); // ['John Doe']

$collection = Collection::wrap(collect('John Doe'));
// 已經是 Collection 會直接回傳
$collection->all(); // ['John Doe']
```

---

## **zip()**
- *說明*：zip 方法會將集合與給定陣列（或集合）`依索引合併成多維陣列`。
- *語法*：`$collection->zip($array)`
- *範例*：
```php
$collection = collect(['Chair', 'Desk']);
// 建立一個集合

$zipped = $collection->zip([100, 200]);
// 依索引合併，第一個配第一個，第二個配第二個
$zipped->all(); // [['Chair', 100], ['Desk', 200]]

```
- *為什麼叫 zip？*
    - zip 英文是「**拉鍊**」的意思，拉鍊的兩邊齒輪會一一對齊扣在一起。
    - 在程式設計裡，zip 代表「**把兩個（或多個）陣列依照索引一一配對**」，就像拉鍊一樣，第一個配第一個、第二個配第二個……
    - Laravel 的 `zip()` 方法就是把兩個集合/陣列「拉鍊式」配對成多維陣列。
- 生活化比喻：
    - 像有一排名字和一排分數，要一一配對成 [名字, 分數] 的組合，或像兩條拉鍊的齒輪一個一個扣在一起。
- 如果長度不同，會以**最短的為主**，多的會被丟掉。

---


## **Higher Order Messages（高階訊息）**
- *說明*：Collection 支援「`高階訊息`」語法，讓你可以用更簡潔的方式對集合做常見操作。這些方法會以「`動態屬性`」的方式存取，語法更直觀、易讀。
- **支援的方法**：
  average、avg、contains、each、every、filter、first、flatMap、groupBy、keyBy、map、max、min、partition、reject、skipUntil、skipWhile、some、sortBy、sortByDesc、sum、takeUntil、takeWhile、unique

### *用法說明*
- 你可以直接用「`->方法名->屬性`」的方式，對集合的每個元素執行某個方法或取某個屬性。

#### **範例一：批次呼叫物件方法**
```php
use App\Models\User;

$users = User::where('votes', '>', 500)->get();
// 取得所有 votes > 500 的 User 集合

$users->each->markAsVip();
// 等同於 $users->each(function ($user) { $user->markAsVip(); });
// 會對每個 user 執行 markAsVip() 方法
```

#### **範例二：批次取屬性並加總**
```php
$users = User::where('group', 'Development')->get();
// 取得所有 group = Development 的 User 集合

return $users->sum->votes;
// 等同於 $users->sum('votes');
// 會把每個 user 的 votes 屬性加總
```

### *實作原理簡介*
- 高階訊息是利用 **PHP 的 __get** 魔術方法實作，當你**存取不存在的屬性時**，`Collection` 會回傳一個 `proxy 物件`，這個 proxy 會攔截你後續的「->屬性」或「->方法」呼叫，然後**自動對集合每個元素執行對應操作**。
- 這種語法讓你不用寫 callback，直接用「->方法->屬性」就能批次操作，非常適合 `Eloquent` 集合或物件集合。
- **白話解釋**：
    -  高階訊息讓你可以直接對集合裡的每個物件批次呼叫方法或屬性，例如 `$users->each->markAsVip()`，
       會自動對每個 user 執行 `$user->markAsVip()`，不用自己寫 callback。
-  **技術原理**：
    -  Laravel `Collection` 利用 *PHP 的 __get* 魔術方法和 `proxy 代理物件`，
       當你寫 `$users->each->markAsVip()`，其實是 Collection 攔截這個存取，回傳一個特殊 proxy，
       這個 proxy 會記住你要呼叫的 `markAsVip()`，然後自動對集合每個元素都執行一次。
-  **生活化比喻**：
    -  就像你有一群人（集合），你只要說「大家都舉手！」，每個人就會自動舉手，不用一個一個點名。
-  **範例**：
    -  *傳統寫法*：
        ```php
       $users->each(function ($user) { $user->markAsVip(); });
       ```
    -  *高階訊息*：
       ```php
       $users->each->markAsVip(); // 更簡潔直覺
       ```

---

## **Lazy Collections（惰性集合）**

### *介紹*
- LazyCollection 是 Laravel 針對**大量資料處理**設計的「惰性」集合，底層利用 **PHP Generator（產生器）**，`可以只在需要時才載入資料，極大減少記憶體用量`。
- 適合處理大型檔案、資料庫大量資料等情境。

---

### *基本用法*

#### 1. **處理大型檔案**
```php
use App\Models\LogEntry;
use Illuminate\Support\LazyCollection;

LazyCollection::make(function () {
    $handle = fopen('log.txt', 'r');
    while (($line = fgets($handle)) !== false) {
        yield $line;
    }
    fclose($handle);
})
->chunk(4)
->map(function (array $lines) {
    return LogEntry::fromLines($lines);
})
->each(function (LogEntry $logEntry) {
    // 處理每一筆 log
});
```
- 只會一次載入一小部分資料進記憶體，適合處理超大檔案。

#### 2. **逐筆處理大量 Eloquent**
```php
use App\Models\User;

// 傳統 Collection：會一次載入所有資料
$users = User::all()->filter(fn(User $user) => $user->id > 500);

// LazyCollection：只會一次載入一筆
$users = User::cursor()->filter(fn(User $user) => $user->id > 500);
foreach ($users as $user) {
    echo $user->id;
}
```
- `cursor()` 會回傳 `LazyCollection`，記憶體用量極低。

---

### *建立 LazyCollection*
```php
use Illuminate\Support\LazyCollection;

LazyCollection::make(function () {
    $handle = fopen('log.txt', 'r');
    while (($line = fgets($handle)) !== false) {
        yield $line;
    }
    fclose($handle);
});
```
- 傳入一個 **generator function**，`yield` 每一筆資料。

---

### *支援的方法*
- 幾乎所有 `Collection 方法` 都可用於 `LazyCollection`（因為都實作 `Illuminate\Support\Enumerable 介面`）。
- 但**不支援**會改變集合本身的方法（如 shift、pop、prepend 等）。

---

### *LazyCollection 專屬方法*

#### **takeUntilTimeout()**
- 依`時間限制`取資料，`超過時間自動停止`。
```php
$lazyCollection = LazyCollection::times(INF)
    ->takeUntilTimeout(now()->addMinute());
$lazyCollection->each(function (int $number) {
    dump($number);
    sleep(1);
});
```

#### **tapEach()**
- 只有`在「取出」元素時`才執行 callback，適合串接 take、all 等。
```php
$lazyCollection = LazyCollection::times(INF)->tapEach(function (int $value) {
    dump($value);
});
$array = $lazyCollection->take(3)->all(); // 只會 dump 1, 2, 3
```

#### **throttle()**

`$collection->throttle(int $count)`
*$count*：**每一批要處理的元素數量**（必填，正整數）。
*回傳值*：LazyCollection，每次產生一個包含 $count 筆資料的陣列（最後一批可能不足 $count 筆）。

- **語源說明**：
  - throttle 英文原意為「*節流閥*」，在機械或汽車中用來控制流量或速度。程式設計中則引申為「*限制操作頻率*」。

- **設計理念**：
  - 處理大量資料時，若每筆都即時處理，可能導致資源耗盡或 API 被限流。throttle 讓你能「*每 N 筆執行一次動作*」，有效分批處理，避免過載。

- **生活化比喻**：
  - 就像高速公路收費站一次只放行幾台車，避免塞車；throttle 讓資料「*分批通過*」，系統不會被瞬間大量資料壓垮。

- **常見用途**：
  - 大量寄送 email、API 批次請求、資料分批寫入、避免觸發 `rate limit`。

- **常見誤用**：
  - throttle *不是「延遲」*每一筆資料，而*是「分批」*處理。若要每筆間隔時間，應用 `sleep` 或 `chunk + sleep`。

- **範例**：

```php
use Illuminate\Support\LazyCollection;

LazyCollection::make(range(1, 10))
    ->throttle(3)
    ->each(function ($item, $key) {
        echo "第 ".($key+1)." 批：".json_encode($item)."\n";
    });
```
-  輸出：
第 1 批：[1,2,3]
第 2 批：[4,5,6]
第 3 批：[7,8,9]
第 4 批：[10]

- **進階應用**：
  - 可搭配 each、map、tap 等方法，實現分批處理與即時回饋。

- **補充：如果要每筆間隔 sleep 或分批 sleep**：

1. *每筆 sleep 範例*：
```php
LazyCollection::make(range(1, 5))
    ->each(function ($item) {
        echo $item."\n";
        sleep(1); // 每筆間隔 1 秒
    });
```

2. *chunk + sleep 範例（每批 sleep）*：
```php
LazyCollection::make(range(1, 10))
    ->chunk(3)
    ->each(function ($chunk, $key) {
        echo "第 ".($key+1)." 批：".json_encode($chunk)."\n";
        sleep(2); // 每批間隔 2 秒
    });
```
- 輸出：
第 1 批：[1,2,3]
第 2 批：[4,5,6]
第 3 批：[7,8,9]
第 4 批：[10]

---

#### **remember()**
- 記住已經取出的資料，下次 `enumerate` 不會重複查詢。
```php
$users = User::cursor()->remember();
$users->take(5)->all(); // 前 5 筆查詢
$users->take(20)->all(); // 前 5 筆從 cache，其餘查詢
```

---

### *小結*
- `LazyCollection` 適合處理「**資料量大到無法一次載入**」的情境。
- 只要用 `cursor()`、`LazyCollection::make()` 等方式產生，就能用大部分 Collection 方法進行鏈式操作，且記憶體用量極低。

---

## **Eloquent 與 Collection 常見問題整理**

### 1. *Eloquent 查詢多筆資料時，回傳的是 Collection 嗎？*
- 是的！像 `User::where(...)->get(`)、`User::all()` 這類查詢，回傳的都是 `Eloquent Collection 實例`。
- 這個 Collection 可以直接用所有 Collection 方法（map、filter、sum、pluck、each...）。
- 範例：
```php
$users = User::where('active', true)->get(); // $users 是 Collection
$names = $users->pluck('name');
$vipUsers = $users->filter(fn($user) => $user->is_vip);
$totalVotes = $users->sum('votes');
```

### 2. *Repository 層可以自然使用 Collection 方法嗎？*
- **只要 repo 層回傳的是 Collection**，後續就能自然、鏈式地使用所有 Collection 方法。
- 你也可以在 repo 層內部就先用 Collection 方法處理好資料再回傳。

### 3. *為什麼只取一筆資料時，不是 Collection？*
- 用 `User::find(1)`、`User::first()` 這類方法時，回傳的是**單一 Model 物件**（如 User），不是 Collection。
- 這樣設計讓你可以直接用物件屬性和方法，操作更直覺。
- 範例：
```php
$user = User::find(1);
if ($user) {
    $name = $user->name;
    $user->markAsVip();
    $user->update(['is_vip' => true]);
}
```

### 4. *單一物件想用 Collection 方法怎麼辦？*
- 可以用 collect([$user]) 把單一物件包成集合，再用 Collection 方法。
- 範例：
```php
$user = User::find(1);
$collection = collect([$user]);
$collection->map(fn($u) => $u->name);
```
- 但通常**只處理一筆資料時，直接用物件本身就好**。

### *5. 小結*
- 查詢**多筆資料** → 回傳 `Collection`，可以用所有 Collection 方法。
- 查詢**單一資料** → 回傳 `Model 物件`，直接用物件屬性和方法。
- 這樣設計讓你在不同情境下都能用最直覺的方式處理資料。

### *補充：單一 Model 物件和 Collection 物件的差異*

- 雖然**單一物件**（如 `$user = User::find(1)`）和**集合**（如 $`users = User::where(...)->get()`）都用 `$obj->` 的語法，但本質完全不同：
    - **單一物件**：代表一筆資料，只能用單一物件的方法（如 `$user->name`、`$user->update()`），*不能用 Collection 方法*（如 map、filter、pluck）。
    - **集合物件**：代表一組資料，可以用 Collection 的方法（如 `$users->map()`、`$users->filter()`、`$users->pluck()`），每個元素才是單一物件。

- **怎麼分辨？**
    - 單一物件只能用單一物件的方法，不能用 Collection 方法。
    - 集合物件可以用 Collection 方法，每個元素才是單一物件。

- **範例對比**：
```php
// 單一物件
$user = User::find(1);
$user->name; // OK
$user->map(fn($u) => $u->name); // ❌ 錯誤！單一物件不能用 map

// 集合物件
$users = User::where('active', true)->get();
$users->map(fn($u) => $u->name); // OK
$users->name; // ❌ 錯誤！集合沒有 name 屬性
```
- **小結**：*單一物件*和*集合物件*雖然語法都用 `->`，但型別和可用方法完全不同。
           單一物件 = 一筆資料，
           集合物件 = 一組資料（多筆）。
           遇到錯誤時，先檢查變數型別！
