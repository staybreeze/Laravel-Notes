# 1. *Eloquent: Collections 筆記* 

---

## 1.1 **簡介**

所有回傳多筆模型的 `Eloquent` 方法（如 get、關聯存取等）都會回傳 `Illuminate\Database\Eloquent\Collection` 實例。
Eloquent collection 擴充自 Laravel base collection，擁有豐富的鏈式操作方法。

---

- 可 *直接迭代* collection：

```php
use App\Models\User;

$users = User::where('active', 1)->get();
//       ^^^^^^^^^^^^^^^^^^^^^^^^^^^ 回傳 Eloquent Collection

foreach ($users as $user) {  // ← 直接用 foreach，不需要其他方法
    echo $user->name;
}
```

---

- *如果不能直接迭代*（假設情況）：

```php
// ❌ 如果需要額外步驟（假設）
$users = User::all();
$array = $users->toArray();  // 需要轉換
foreach ($array as $user) {
    echo $user['name'];
}

// 或者
foreach ($users->getItems() as $user) {  // 需要特殊方法
    echo $user->name;
}
```

---

- *Collection 實作了 `Iterator`*

```php
// Eloquent Collection 內部實作了這些介面
class Collection implements Iterator, Countable, ArrayAccess
{
    // 實作 Iterator 介面的方法
    public function current();  // 回傳目前的元素
    public function key();      // 回傳目前的索引/鍵
    public function next();     // 移動到下一個元素
    public function rewind();   // 重設到第一個元素
    public function valid();    // 檢查目前位置是否有效
}
}

// Eloquent Collection 內部實作了這些介面
class Collection implements Iterator, Countable, ArrayAccess
{
    private $items = [];        // 儲存實際資料
    private $position = 0;      // 目前迭代位置
    
    // 實作 Iterator 介面的方法
    public function current() { 
        return $this->items[$this->position];  // 回傳目前的 User 模型
    }
    
    public function next() { 
        $this->position++;  // 移動到下一個位置
    }
    
    public function key() { 
        return $this->position;  // 回傳目前的索引（0, 1, 2...）
    }
    
    public function valid() { 
        return isset($this->items[$this->position]);  // 檢查是否還有資料
    }
    
    public function rewind() { 
        $this->position = 0;  // 重設到開頭
    }
}

// 所以可以直接 foreach
foreach ($collection as $key => $item) {
    // PHP 自動呼叫這些方法：
    // 1. rewind()     - 重設到開頭
    // 2. valid()      - 檢查是否有資料
    // 3. current()    - 取得目前元素
    // 4. key()        - 取得目前索引
    // 5. next()       - 移動到下一個
    // 6. valid()      - 再次檢查...
    // 重複步驟 3-6 直到 valid() 回傳 false
}
```

---

- *Iterator實際例子*

```php
$users = User::where('active', 1)->get();
//       ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ Collection 物件

// 因為實作 Iterator，所以可以：
foreach ($users as $index => $user) {
    //             ^^^^^ key() 方法回傳
    //                       ^^^^^ current() 方法回傳
    echo "User {$index}: {$user->name}";
}

// 因為實作 Countable，所以可以：
echo "總共 " . count($users) . " 個用戶";

// 因為實作 ArrayAccess，所以可以：
echo $users[0]->name;      // 存取第一個用戶
echo $users->first()->name; // 或用 Collection 方法
```

---

- *沒有 `Iterator` 的情況*

```php
// 如果 Collection 沒有實作 Iterator
class BadCollection 
{
    private $items = [];
    
    // 沒有實作 Iterator 介面
}

$collection = new BadCollection();

// ❌ 這樣會出錯
foreach ($collection as $item) {
    // Error: BadCollection 不能被迭代
}

// 必須提供其他方法
foreach ($collection->getItems() as $item) {
    // 需要額外的方法
}
```

---

- *其他迭代方式*

```php
$users = User::all();

// 方式1：直接迭代（最常用）
foreach ($users as $user) {
    echo $user->name;
}

// 方式2：使用 Collection 方法
$users->each(function ($user) {
    echo $user->name;
});

// 方式3：轉成陣列再迭代
foreach ($users->toArray() as $userData) {
    echo $userData['name'];
}

// 方式4：使用索引
for ($i = 0; $i < $users->count(); $i++) {
    echo $users[$i]->name;
}
```

---

- 可鏈式 `map/reduce` 操作：

```php
$names = User::all()->reject(function (User $user) {
    return $user->active === false;
})->map(function (User $user) {
    return $user->name;
});
//       ^^^^^^^^^ ^^^^^^ ^^^ ^^^^
//       取所有用戶 過濾   轉換 取名稱
//       
//       1. 取得所有用戶
//       2. 排除 active = false 的用戶
//       3. 將剩餘用戶轉換成姓名陣列
//       
//       結果：活躍用戶的姓名集合
```

---

## 1.2 **Eloquent Collection 轉換**

- 多數 collection 方法會回傳新的 Eloquent collection，
  但    `collapse`、
        `latten`、
        `flip`、
        `keys`、
        `pluck`、
        `zip` 
        會回傳 *base collection*。

- 若 `map` 結果不含 Eloquent model，也會自動轉為 *base collection*。

---

- *Eloquent Collection*

```php
$users = User::all();  // Eloquent Collection
// 包含 User 模型，有額外的 Eloquent 方法
$users->load('posts');     // 可以預載入關聯
$users->modelKeys();       // 可以取得模型鍵
```

---

- *Base Collection*

```php
$names = User::all()->pluck('name');  // Base Collection
// 一般集合方法，Base Collection 可用
// 只是純資料，沒有 Eloquent 功能
// $names->load('posts');  // ❌ 沒有這些方法
```

---

- *Eloquent 專用方法*（只有 Eloquent Collection 可用）

```php
$users = User::all();  // Eloquent Collection

// ❌ Base Collection 沒有這些方法
$users->load('posts');              // 預載入關聯
$users->loadMissing('posts');       // 智慧載入關聯
$users->loadMorph('parentable', []); // 多型關聯載入
$users->loadCount('posts');         // 載入關聯計數
$users->loadMax('posts', 'created_at'); // 載入關聯最大值
$users->loadMin('posts', 'created_at'); // 載入關聯最小值
$users->loadSum('posts', 'views');   // 載入關聯總和
$users->loadAvg('posts', 'rating');  // 載入關聯平均
$users->modelKeys();                // 取得所有模型主鍵 [1,2,3]
$users->fresh();                    // 重新從資料庫載入
$users->fresh(['posts']);           // 重新載入並預載入關聯
$users->toQuery();                  // 轉成查詢建構器
$users->contains(1);                // 檢查是否包含特定主鍵
$users->diff($other);               // Eloquent 版本的差集（比較主鍵）
$users->intersect($other);          // Eloquent 版本的交集（比較主鍵）
$users->unique();                   // Eloquent 版本的去重複（比較主鍵）
$users->only([1, 2, 3]);           // 只保留指定主鍵的模型
$users->except([1, 2, 3]);         // 排除指定主鍵的模型
$users->find(1);                   // 在集合中找特定主鍵的模型
$users->withRelationshipAutoloading(); // 啟用自動關聯載入
```

---

- *重要限制*：一旦變成 Base Collection 就回不去了
  - **無法從 `Base Collection` 變回 `Eloquent Collection`**

```php
$users = User::all()              // EloquentCollection
    ->filter(fn($u) => $u->active) // 還是 EloquentCollection
    ->pluck('name')               // 變成 Base Collection
    ->unique()                    // 還是 Base Collection
    ->load('posts');              // ❌ 錯誤！Base Collection 沒有 load 方法
```

---

- *正確的順序*

```php
$result = User::all()             // EloquentCollection
    ->load('posts')               // EloquentCollection (先載入關聯)
    ->filter(fn($u) => $u->active) // EloquentCollection
    ->pluck('name')               // Base Collection (最後提取資料)
    ->unique()                    // Base Collection
    ->sort();                     // Base Collection
```

---

- *回傳 `Base Collection` 的方法*

  - **Laravel 的判斷邏輯**：
    - 如果方法回傳的內容是「`Eloquent 模型`」→ `EloquentCollection`
    - 如果方法回傳的內容是「`純資料`」→ `Base Collection`

```php
// 這些資料轉換方法會回傳 Base Collection（純資料）
$users = User::all();

$users->pluck('name');     // ['John', 'Jane'] - Base Collection／提取欄位 → 字串陣列
$users->keys();            // [0, 1, 2] - Base Collection ／提取索引 → 數字陣列  
$users->flip();            // 翻轉資料 - Base Collection／翻轉 → 混合資料
$users->collapse();        // 攤平陣列 - Base Collection／攤平 → 混合資料
$users->flatten();         // 扁平化 - Base Collection／扁平化 → 混合資料
$users->zip($other);       // 壓縮合併 - Base Collection／合併 → 混合資料

// Eloquent 輔助方法（回傳純資料）
$users->modelKeys();       // 模型主鍵 → 數字陣列

// map 如果不回傳模型，也變 Base Collection
$users->map(fn($u) => $u->name);  // ['John', 'Jane'] - Base Collection
```

---

- *`map()`回傳模型* - 保持 `Eloquent Collection`

```php
$users = User::all();

// 回傳模型 - 還是 EloquentCollection
$processedUsers = $users->map(function ($user) {
    $user->processed = true;  // 修改模型屬性
    return $user;             // 回傳 User 模型
});

echo get_class($processedUsers);  // "Illuminate\Database\Eloquent\Collection"

// 還能使用 Eloquent 方法
$processedUsers->load('posts');     // ✅ 可以
$processedUsers->modelKeys();       // ✅ 可以
```

---

- *`map()`回傳資料* - 變成 `Base Collection`

```php
$users = User::all();

// 回傳字串 - 變成 Base Collection
$names = $users->map(fn($u) => $u->name);  // 回傳字串

echo get_class($names);  // "Illuminate\Support\Collection"

// 不能使用 Eloquent 方法
$names->load('posts');    // ❌ 錯誤！
```

---

- *為什麼會這樣？*

```php
// Eloquent Collection 期望內容是模型
$users = User::all();  // [User, User, User] - Eloquent Collection

// 一旦內容不是模型，就變成 Base Collection
$names = $users->pluck('name');  // ['John', 'Jane'] - Base Collection
$ids = $users->pluck('id');      // [1, 2, 3] - Base Collection
```

---

## 1.3 **常用方法**

`Eloquent collection` 繼承 *base collection* 所有方法，並額外提供下列輔助方法（多數回傳 Eloquent collection，部分如 `modelKeys` 回傳 *base collection*）：

---

- `append($attributes)`：批次為每個模型*附加屬性*。

```php
$users->append('team');
//     ^^^^^^ ^^^^^^
//     附加方法 訪問器名稱
//     
//     為集合中的每個模型附加一個訪問器屬性
//     將 'team' 訪問器加入模型的 toArray() 輸出中
//
//     結果：$users->toArray() 會包含 team 欄位
//     [
//         ['id' => 1, 'name' => 'John', 'team' => 'Development'],
//         ['id' => 2, 'name' => 'Jane', 'team' => 'Marketing'],
//     ]

$users->append(['team', 'is_admin']);
//     ^^^^^^ ^^^^^^^^^^^^^^^^^^^
//     附加方法 多個訪問器名稱
//     
//     批次附加多個訪問器屬性
//     將 'team' 和 'is_admin' 都加入 toArray() 輸出
//
//     結果：$users->toArray() 會包含兩個額外欄位
//     [
//         ['id' => 1, 'name' => 'John', 'team' => 'Development', 'is_admin' => false],
//         ['id' => 2, 'name' => 'Jane', 'team' => 'Marketing', 'is_admin' => true],
//     ]
```

---

- 步驟1：*必須先在模型中定義訪問器*

```php
// User.php - 必須先有這個
class User extends Model
{
    // 必須先定義 team 訪問器
    public function getTeamAttribute(): string
    {
        if ($this->department_id === 1) {
            return 'Development';
        } elseif ($this->department_id === 2) {
            return 'Marketing';
        }
        return 'General';
    }
}
```

---

- 步驟2：*使用 `append()` 將訪問器加入序列化*

```php
$users = User::all();

// append() 只是「啟用」已存在的訪問器用於序列化
$users->append('team');

// 現在 toArray() 會包含 team
$array = $users->toArray();
// [
//     ['id' => 1, 'name' => 'John', 'team' => 'Development'],
//     ['id' => 2, 'name' => 'Jane', 'team' => 'Marketing'],
// ]
```

---

- *沒有訪問器會怎樣？*

```php
// 如果 User 模型中沒有定義 getTeamAttribute()
class User extends Model
{
    // 沒有 getTeamAttribute() 方法
}

$users = User::all()->append('team');
$array = $users->toArray();

// 結果：team 會是 null
// [
//     ['id' => 1, 'name' => 'John', 'team' => null],
//     ['id' => 2, 'name' => 'Jane', 'team' => null],
// ]
```

---

- *`append() `的真正作用*

```php
// 訪問器本來就存在，可以直接存取
$user = User::first();
echo $user->team;  // "Development" (訪問器運作)

// 但 toArray() 預設不包含訪問器
$array = $user->toArray();
// ['id' => 1, 'name' => 'John']  ← 沒有 team

// append() 讓 toArray() 也包含訪問器
$user->append('team');
$array = $user->toArray();
// ['id' => 1, 'name' => 'John', 'team' => 'Development']  ← 有 team
```

---

- *完整範例*

```php
// 1. 在模型中定義訪問器
class User extends Model
{
    public function getTeamAttribute(): string
    {
        return match($this->department_id) {
            1 => 'Development',
            2 => 'Marketing', 
            3 => 'Sales',
            default => 'General'
        };
    }
    
    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }
}

// 2. 使用訪問器
$user = User::first();
echo $user->team;      // "Development" ← 訪問器本來就能用
echo $user->is_admin;  // true

// 3. 序列化時預設不包含
echo $user->toJson();  // {"id":1,"name":"John"} ← 沒有 team, is_admin

// 4. append() 讓序列化包含訪問器
$user->append(['team', 'is_admin']);
echo $user->toJson();  // {"id":1,"name":"John","team":"Development","is_admin":true}
```

---

- `contains($key, $operator = null, $value = null)`：判斷 collection *是否包含* 指定主鍵或模型。

```php
$users->contains(1);
//      ^^^^^^^^ ^^^
//      檢查方法  主鍵值
//      
//      檢查集合中是否包含主鍵為 1 的模型
//      回傳 true/false

$users->contains(User::find(1));
//      ^^^^^^^^ ^^^^^^^^^^^^^
//      檢查方法  完整模型實例
//      
//      檢查集合中是否包含這個特定的模型實例
//      比較模型的主鍵來判斷
//      回傳 true/false
```

---

- `diff($items)`：比較兩個集合，*回傳* `僅存在於原集合而不存在於指定集合中的模型`。

```php
$users = $users->diff(User::whereIn('id', [1, 2, 3])->get());
//       ^^^^^ ^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//       原集合 差集  要排除的模型集合
//       
//       從 $users 集合中移除 ID 為 1, 2, 3 的用戶
//       比較模型的主鍵來判斷差異
//       回傳不包含指定用戶的新集合
//
//       結果：假設原本 $users 有 ID [1, 2, 3, 4, 5] 的用戶
//       執行後 $users 只剩 ID [4, 5] 的用戶
//       被移除的是 ID 1, 2, 3 的用戶
```

---

- `except($keys)`：*排除* 指定 __主鍵的模型__。

```php
$users = $users->except([1, 2, 3]);
//       ^^^^^ ^^^^^^ ^^^^^^^^^^^
//       原集合 排除   主鍵陣列
//       
//       排除主鍵為 1, 2, 3 的模型
//       直接根據模型主鍵移除指定的模型
//       回傳不包含這些主鍵的新集合
//
//       結果：假設原本 $users 有 ID [1, 2, 3, 4, 5] 的用戶
//       執行後 $users 只剩 ID [4, 5] 的用戶
```

---

- `find($key)`：依主鍵 *尋找* 模型。

```php
$users = User::all();
//       ^^^^^^^^^^^ 取得所有用戶（EloquentCollection）

$user = $users->find(1);
//      ^^^^^ ^^^^ ^^^
//      集合  查找 主鍵
//      
//      在已載入的集合中查找主鍵為 1 的模型
//      不會執行新的資料庫查詢，只在記憶體中搜尋
//      如果找到回傳 User 模型，找不到回傳 null
```

---

- `findOrFail($key)`：找不到則丟出 `ModelNotFoundException`。

```php
$users = User::all();
//       ^^^^^^^^^^^ 取得所有用戶（EloquentCollection）

$user = $users->findOrFail(1);
//      ^^^^^ ^^^^^^^^^^^ ^^^
//      集合  查找或失敗   主鍵
//      
//      在已載入的集合中查找主鍵為 1 的模型
//      如果找到回傳 User 模型
//      如果找不到拋出 ModelNotFoundException 例外
//      不會執行資料庫查詢，只在記憶體中搜尋
```

---

- `fresh($with = [])`：*重新* 從資料庫取得 *最新模型*，可指定 `eager load` 關聯。

```php
$users = $users->fresh();
//       ^^^^^   ^^^^^
//       集合    重新載入
//       
//       重新從資料庫載入集合中的所有模型
//       捨棄記憶體中的變更，取得最新資料
//       回傳 EloquentCollection

$users = $users->fresh('comments');
//       ^^^^^ ^^^^^ ^^^^^^^^^^^
//       集合  重新載入 預載入關聯
//       
//       重新從資料庫載入所有模型並預載入 comments 關聯
//       確保取得最新資料和關聯資料
//       回傳 EloquentCollection
```

---

- `intersect($items)`：回傳 *同時存在* 於指定 collection 的模型。

```php
$users = $users->intersect(User::whereIn('id', [1, 2, 3])->get());
//       ^^^^^ ^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//       原集合 交集方法   指定的模型集合
//       
//       取得兩個集合的交集（共同存在的模型）
//       比較模型的主鍵來判斷相同性
//       回傳同時存在於兩個集合中的模型
//
//       結果：假設原本 $users 有 ID [2, 3, 4, 5] 的用戶
//       執行後 $users 只剩 ID [2, 3] 的用戶（共同部分）
```

---

- `load($relations)`：*批次* `eager load` 關聯。

```php
$users->load(['comments', 'posts']);
//      ^^^^ ^^^^^^^^^^^^^^^^^^^^^^
//      預載入 多個關聯
//     
//      為集合中的所有用戶預載入 comments 和 posts 關聯
//      避免 N+1 查詢問題
//      修改原集合，不回傳新集合

$users->load('comments.author');
//      ^^^^ ^^^^^^^^^^^^^^^^^
//      預載入 巢狀關聯
//     
//      預載入用戶的評論，以及每個評論的作者
//      使用點語法載入多層關聯
//      一次解決多層 N+1 問題

$users->load(['comments', 'posts' => fn ($query) => $query->where('active', 1)]);
//     ^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//     預載入 帶條件的關聯載入
//     
//     載入 comments（全部）和 posts（只載入 active = 1 的文章）
//     使用閉包函數對關聯查詢添加條件
//     靈活控制載入的關聯資料
```

---

- `loadMissing($relations)`：*僅載入尚未載入* 的關聯。

```php
$users->loadMissing(['comments', 'posts']);
//     ^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^^^^
//     智慧載入    多個關聯
//     
//     只載入尚未載入的關聯，跳過已載入的關聯
//     避免重複載入相同資料
//     比 load() 更有效率

$users->loadMissing('comments.author');
//     ^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^
//     智慧載入    巢狀關聯
//     
//     智慧載入用戶的評論和評論作者
//     只載入缺少的關聯層級
//     不會重複載入已存在的關聯

$users->loadMissing(['comments', 'posts' => fn ($query) => $query->where('active', 1)]);
//     ^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//     智慧載入    帶條件的關聯載入
//     
//     智慧載入 comments 和符合條件的 posts
//     只載入尚未載入的關聯，並對 posts 加上條件
//     結合效能優化與條件篩選
```

---

- `modelKeys()`：取得 *所有模型的主鍵陣列* 。

```php
$users->modelKeys(); // [1, 2, 3, 4, 5]
//     ^^^^^^^^^^ 
//     取得模型主鍵
//     
//     提取集合中所有模型的主鍵值
//     回傳 Base Collection（純數字陣列）
//     常用於批次操作或條件查詢
//
//     結果：[1, 2, 3, 4, 5] - 所有用戶的 ID 陣列
```

---

- `makeVisible($attributes)`：*顯示* 原本 __隱藏的屬性__。

```php
$users = $users->makeVisible(['address', 'phone_number']);
//       ^^^^^ ^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^
//       集合  顯示隱藏屬性 要顯示的隱藏欄位
//       
//       讓原本隱藏的屬性在序列化時顯示出來
//       覆蓋模型的 $hidden 設定
//       影響 toArray() 和 toJson() 的輸出
//
//       結果：address 和 phone_number 會出現在陣列/JSON 中
//       即使它們在模型中被設為 hidden
```

---

- `makeHidden($attributes)`：*隱藏* 原本 __顯示的屬性__。

```php
$users = $users->makeHidden(['address', 'phone_number']);
//       ^^^^^ ^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^
//       集合  隱藏屬性   要隱藏的欄位
//       
//       讓指定的屬性在序列化時隱藏起來
//       覆蓋模型的預設可見性設定
//       影響 toArray() 和 toJson() 的輸出
//
//       結果：address 和 phone_number 不會出現在陣列/JSON 中
//       即使它們在模型中是可見的
```

---

- `only($keys)`：*只保留* 指定 __主鍵的模型__。

```php
$users = $users->only([1, 2, 3]);
//       ^^^^^ ^^^^ ^^^^^^^^^^^
//       集合  保留  主鍵陣列
//       
//       只保留主鍵為 1, 2, 3 的模型
//       移除集合中其他所有模型
//       回傳只包含指定主鍵模型的新集合
//
//       結果：假設原本 $users 有 ID [1, 2, 3, 4, 5] 的用戶
//       執行後 $users 只剩 ID [1, 2, 3] 的用戶
```

---

- `partition`：依*條件分割* collection，回傳 `Illuminate\Support\Collection`，內含 Eloquent collection。

```php
$partition = $users->partition(fn ($user) => $user->age > 18);
//           ^^^^^ ^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//           集合  分割方法  分割條件（年齡大於18）
//           
//           根據條件將集合分割成兩個子集合
//           回傳 Base Collection 包含兩個 Eloquent Collection
//                 [外層容器]      [   內容1   ,   內容2   ]

dump($partition::class);    // Illuminate\Support\Collection
//   ^^^^^^^^^^^ 外層容器是 Base Collection（因為內容不是模型）

dump($partition[0]::class); // Illuminate\Database\Eloquent\Collection  
//   ^^^^^^^^^^^^^^ 第一個子集合：符合條件的模型（age > 18）

dump($partition[1]::class); // Illuminate\Database\Eloquent\Collection
//   ^^^^^^^^^^^^^^ 第二個子集合：不符合條件的模型（age <= 18）

// 使用方式：
$adults = $partition[0];     // 成年用戶
$minors = $partition[1];     // 未成年用戶
```

---

- `setVisible($attributes)`：*暫時覆蓋* 所有模型的 `visible` 屬性。

```php
$users = User::all();

// 假設 User 模型原本 visible = ['id', 'name', 'email', 'created_at']
echo $users->toJson();  
// 結果：[{"id":1,"name":"John","email":"john@example.com","created_at":"2023-01-01"}]

// 使用 setVisible() 暫時覆蓋
$users = $users->setVisible(['id', 'name']);
//       ^^^^^  ^^^^^^^^^^ ^^^^^^^^^^^^^^^
//       集合   覆蓋方法    新的可見欄位
//       
//       暫時改變集合中每個模型的 $visible 屬性
//       原本：$visible = ['id', 'name', 'email', 'created_at']
//       現在：$visible = ['id', 'name']  ← 暫時覆蓋

echo $users->toJson();  
// 結果：[{"id":1,"name":"John"}]  ← 只剩 id 和 name
//       email 和 created_at 被隱藏了

// 重要：這不會影響資料庫，只影響序列化輸出
echo $users[0]->email;  // 還是可以存取，只是不會出現在 JSON 中
```

---

- `setHidden($attributes)`：*暫時覆蓋* 所有模型的 `hidden` 屬性。

```php
$users = User::all();

// 假設 User 模型原本 hidden = ['password']
echo $users->toJson();  
// 結果：[{"id":1,"name":"John","email":"john@example.com","phone":"123456"}]
//       password 已經被隱藏

// 使用 setHidden() 暫時覆蓋
$users = $users->setHidden(['email', 'password', 'phone']);
//       ^^^^^  ^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//       集合   覆蓋方法   新的隱藏欄位
//       
//       暫時改變集合中每個模型的 $hidden 屬性
//       原本：$hidden = ['password']
//       現在：$hidden = ['email', 'password', 'phone']  ← 暫時覆蓋

echo $users->toJson();  
// 結果：[{"id":1,"name":"John"}]  ← email 和 phone 也被隱藏了
//       
// 重要：資料還在模型中，只是序列化時不顯示
echo $users[0]->email;  // "john@example.com" ← 還是可以存取
echo $users[0]->phone;  // "123456" ← 還是可以存取
```

---

- `toQuery()`：回傳 `whereIn` 條件的 *Eloquent 查詢建構器* 。

<!-- toQuery() 是 Laravel 10 之後新增的方法，
     可以把 Eloquent Collection 轉成 Query Builder，
     這樣才能對多筆模型直接批次更新。 -->

```php
// 舊方法
// 批次更新只能用查詢物件
User::where('role', 'user')->update([
    'status' => 'Administrator',
]);
```

```php
use App\Models\User;

$users = User::where('status', 'VIP')->get();
//       ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ 取得狀態為 VIP 的用戶集合

$users->toQuery()->update([
    'status' => 'Administrator',
]);
//     ^^^^^^^ ^^^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^
//     集合    轉查詢  批次更新
//     
//     將 EloquentCollection 轉回查詢建構器
//     對相同條件的記錄執行批次更新
//     將所有 VIP 用戶的狀態改為 Administrator
//
//     實際執行的 SQL：
//     UPDATE users SET status = 'Administrator' WHERE status = 'VIP'
```

---

- *Collection 沒有 `update()` 方法*

```php
$users = User::where('status', 'VIP')->get();

$users->update([
    'status' => 'Administrator',
]);
// ❌ 錯誤！EloquentCollection 沒有 update() 方法
// Error: Method update does not exist
```

---

- *Collection 可用的方法*

```php
$users = User::where('status', 'VIP')->get();

// ❌ 這些方法 Collection 都沒有
$users->update();     // 不存在
$users->delete();     // 不存在  
$users->insert();     // 不存在

// ✅ Collection 只有這些方法
$users->each(function ($user) {
    $user->update(['status' => 'Administrator']);  // 逐一更新（N次查詢）
});

$users->map();        // 轉換
$users->filter();     // 篩選
$users->load();       // 預載入
// ... 其他 Collection 方法
```

---

- 方式1：*`toQuery()` + `update`*（推薦）

```php
$users = User::where('status', 'VIP')->get();

$users->toQuery()->update([
    'status' => 'Administrator',
]);
// ✅ 執行 1 次 SQL：
// UPDATE users SET status = 'Administrator' WHERE status = 'VIP'
```

---

- 方式2：*`each()` 逐一更新*（效能差）
 - 因為 `each()` 是對集合中的**每個模型執行操作**，而每個模型都有 `update()` 方法！

```php
$users = User::where('status', 'VIP')->get();  // EloquentCollection

$users->each(function ($user) {
//            ^^^^^^^^^ 這是單個 User 模型，不是集合
    $user->update(['status' => 'Administrator']);
//  ^^^^^ 單個 User 模型有 update() 方法
});
// ❌ 執行 N 次 SQL：
// UPDATE users SET status = 'Administrator' WHERE id = 1
// UPDATE users SET status = 'Administrator' WHERE id = 2  
// UPDATE users SET status = 'Administrator' WHERE id = 3
// ... N 次查詢
```

---

- 方式3：*直接在查詢建構器上更新*（最佳）

```php
User::where('status', 'VIP')->update([
    'status' => 'Administrator',
]);
// ✅ 最直接，執行 1 次 SQL
```

---

- *為什麼需要 `toQuery()`？*

```php
// Collection 是「已載入的模型集合」
$users = User::where('status', 'VIP')->get();  // EloquentCollection
// 此時資料已經從資料庫取出，存在記憶體中

// toQuery() 重建原始查詢條件
$query = $users->toQuery();  // Builder
// 相當於：User::where('status', 'VIP')

// 然後可以執行批次操作
$query->update(['status' => 'Administrator']);
```

---

- *實際對比*

```php
// 假設有 1000 個 VIP 用戶

// ❌ 這樣會出錯
$users->update(['status' => 'Administrator']);

// ❌ 這樣執行 1000 次查詢
$users->each(fn($user) => $user->update(['status' => 'Administrator']));

// ✅ 這樣只執行 1 次查詢
$users->toQuery()->update(['status' => 'Administrator']);

// ✅ 這樣也只執行 1 次查詢（最佳）
User::where('status', 'VIP')->update(['status' => 'Administrator']);
```
---

- `unique($key = null, $strict = false)`：移除主鍵 *重複* 的模型。

```php
$users = $users->unique();
//       ^^^^^ ^^^^^^
//       集合  去重複
//       
//       根據模型的主鍵去除重複的模型
//       保留第一次出現的模型實例
//       回傳去重後的 EloquentCollection
//
//       結果：如果集合中有相同主鍵的模型，只保留第一個
//       常用於合併多個查詢結果後去重
```

---

## 1.4 **自訂 Collection**

- 可透過 `CollectedBy` *attribute* 或覆寫 `newCollection` 方法，讓**模型**回傳自訂 `collection：`

```php
namespace App\Models;

use App\Support\UserCollection;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Model;

#[CollectedBy(UserCollection::class)]
//^^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^^^
//屬性         自訂集合類別
//
//指定 User 模型使用自訂的 UserCollection 類別
//當執行 User::all() 或其他查詢時，會回傳 UserCollection 而非預設的 EloquentCollection

// EloquentCollection (Laravel 內建)
//     ↑ extends
// UserCollection (你的自訂)
//     ↑ 指定使用 (#[CollectedBy] 或 newCollection)
// User Model

//讓你可以為特定模型定義專屬的集合方法
class User extends Model
{
    // ...
}
```

---

- 或覆寫 `newCollection` 方法：

```php
namespace App\Models;

use App\Support\UserCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 建立自訂 Eloquent Collection 實例。
     *
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $models
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function newCollection(array $models = []): Collection
    //              ^^^^^^^^^^^^^
    //              覆寫父類方法
    //              
    //              當 Laravel 需要建立 Collection 時會呼叫此方法
    //              例如：User::all()、User::where()->get() 等
    {
        return new UserCollection($models);
        //     ^^^ ^^^^^^^^^^^^^^ ^^^^^^^
        //     回傳 自訂集合類別    模型陣列
        //     
        //     替代預設的 EloquentCollection
        //     讓 User 查詢回傳 UserCollection 實例
        //     可以在 UserCollection 中定義專屬的業務邏輯方法
    }
}
```

---

- 若要**全域**自訂 collection，可於所有模型共用的 `base model` 實作 `newCollection。` 

```php
// BaseModel.php - 所有模型的基底類別
namespace App\Models;

use App\Support\CustomEloquentCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    /**
     * 建立自訂 Eloquent Collection 實例（全域）。
     */
    public function newCollection(array $models = []): Collection
    {
        return new CustomEloquentCollection($models);
        //     ^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^ ^^^^^^^
        //     回傳 全域自訂集合              模型陣列
        //     
        //     所有繼承 BaseModel 的模型都會使用此集合
        //     在 CustomEloquentCollection 中定義通用的業務方法
    }
}

// User.php - 繼承 BaseModel
class User extends BaseModel  // 繼承 BaseModel 而非 Model
{
    // User::all() 會回傳 CustomEloquentCollection
}

// Post.php - 繼承 BaseModel  
class Post extends BaseModel  // 繼承 BaseModel 而非 Model
{
    // Post::all() 也會回傳 CustomEloquentCollection
}
```