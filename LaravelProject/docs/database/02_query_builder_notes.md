# *Laravel Query Builder 筆記*

---

## 1. **Query Builder 簡介**

Laravel 的 `Query Builder` 提供一個方便、流暢的介面來建立與執行` SQL 查詢`，支援所有 Laravel 支援的資料庫系統。

- *內建 PDO 參數綁定*：
  - 防止 `SQL Injection`。
  - `僅適用`於 **查詢的值**（例如 WHERE 條件中的值），而`不適用`於 **欄位名稱** 或 **表名稱**。

- *自動清理字串*：
  - 不需手動清理字串，`Query Builder` 會自動**處理特殊字元**，確保安全性。

- *注意事項*
  - **PDO 的限制**：
    - PDO `不支援綁定欄位名稱或表名稱`，因此在使用 `Query Builder` 時，切勿讓*使用者*輸入直接決定*查詢的欄位名稱或表名稱*。
    - 僅適用於查詢的值，欄位名稱或表名稱需使用 *白名單驗證* 。
      - *白名單驗證* 是一種安全性措施，用於`限制使用者輸入的值只能是預先定義的合法範圍內的值`。
      - **定義**
        - *白名單* 是一組`預先定義的合法值或範圍。`
        - *白名單驗證* 是指在使用者輸入之前，檢查輸入是否屬於白名單中的合法值。
      - **與黑名單的區別**
        - *白名單*：只允許合法的輸入，其他輸入一律拒絕。
        - *黑名單*：拒絕特定的非法輸入，但可能漏掉其他未預期的非法輸入。
      - **白名單更安全**：
        - 白名單驗證是`主動的`，只允許合法值。
        - 黑名單驗證是`被動的`，可能漏掉未列入黑名單的非法值。

- **安全的查詢值綁定**
```php
// 使用 Query Builder 建立安全的查詢
$users = DB::table('users')
    ->where('id', '=', $id) // 綁定查詢值，防止 SQL Injection
    ->get();
```

- 欄位名稱的**危險的做法**
```php
$column = $_GET['column']; // 使用者輸入的欄位名稱
$stmt = $pdo->query("SELECT $column FROM users"); // 直接插入使用者輸入，存在 SQL Injection 風險

// 攻擊者輸入惡意欄位名稱
// column = "name; DROP TABLE users;"

// 如果直接插入使用者輸入，SQL 語句會變成：
// SELECT name; DROP TABLE users; FROM users

// 這會導致 users 表被刪除。
```

- 欄位名稱的**白名單驗證**
```php
// 定義白名單
$allowedColumns = ['name', 'email', 'created_at'];
$column = $request->input('column'); // 使用者輸入的欄位名稱

// 驗證欄位名稱是否在白名單中
if (!in_array($column, $allowedColumns)) {
    throw new Exception('Invalid column name'); // 如果欄位名稱不合法，拋出例外
}

// 使用 Query Builder 查詢
$results = DB::table('users')
    ->select($column) // 安全，因為欄位名稱已被驗證
    ->get();
```

- 使用 `ORM（Eloquent）`避免直接操作 SQL
```php
// 使用 Eloquent 模型進行查詢
$users = User::select(['name', 'email'])->get(); // 安全，避免直接插入使用者輸入
```

---

### 1.1 *執行查詢*

#### 1.1.1 **取得所有資料表資料**

```php
use Illuminate\Support\Facades\DB;

$users = DB::table('users')->get();

foreach ($users as $user) {
    echo $user->name; // 取出每一筆資料的 name 欄位
}
```
- `get()` 回傳 `Illuminate\Support\Collection`，每筆資料為 `stdClass` 物件。
- 可用物件屬性方式存取欄位。

---

#### 1.1.2 **取得單筆資料/欄位**

```php
$user = DB::table('users')->where('name', 'John')->first();
$email = DB::table('users')->where('name', 'John')->value('email');
$userById = DB::table('users')->find(3);
```
- `first()`：回傳*第一筆資料*（`stdClass`），找不到回傳 null。
- `firstOrFail()`：找不到會丟出 `RecordNotFoundException`，自動回傳 404。
- `value('欄位')`：直接取得*欄位值*。
- `find(id)`：依*主鍵查詢*。

---

#### 1.1.3 **pluck 取出欄位集合**

```php
$titles = DB::table('users')->pluck('title');
$titlesWithName = DB::table('users')->pluck('title', 'name');
// [
//     'Alice' => 'Developer',
//     'Bob' => 'Manager',
//     'Charlie' => 'Designer',
// ]
```
- `pluck('欄位')`：回傳*欄位值*的集合。
- `pluck('值欄位', '鍵欄位')`：*指定集合的 key*。兩個參數都是資料表中的欄位。

---

#### 1.1.4 **分塊查詢 chunk/lazy**

```php
DB::table('users')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        // 處理每 100 筆資料
    }
});

DB::table('users')->orderBy('id')->lazy()->each(function ($user) {
    // 逐筆處理
});
```
- `chunk(數量, 閉包)`：*分批查詢*，適合大量資料。

- `lazy()`： 使用 `LazyCollection`，它會逐筆從資料庫載入資料，而不是一次性載入所有資料
  - **記憶體友善**：*只載入需要處理的資料*，適合處理大量資料。
  - **逐筆查詢**：每次*只載入一筆*資料，並在處*理完後釋放記憶體*。

- 一般的 `foreach` 行為
  - 如果使用 `DB::table('users')->get()`，資料庫會*一次性載入所有資料到記憶體中*，然後再使用 foreach 進行迭代。
  - **一次性載入**：所有資料會一次性載入到記憶體中。
  - **記憶體消耗大**：如果資料量很大，可能導致記憶體不足。

- 若查詢時*同時更新資料*，建議用 `chunkById` 或 `lazyById`，*避免資料異動導致分頁錯亂*。

---

#### 1.1.5 **聚合查詢 Aggregates**

- `聚合查詢`（Aggregates） 是指*對資料進行統計或計算的查詢操作*。

```php
$count = DB::table('users')->count();
$maxPrice = DB::table('orders')->max('price');
$avgPrice = DB::table('orders')->where('finalized', 1)->avg('price');
```
- 常用聚合方法：`count`、`max`、`min`、`avg`、`sum`。

---

#### 1.1.6 **判斷資料是否存在 exists/doesntExist**

```php
if (DB::table('orders')->where('finalized', 1)->exists()) {
    // 有資料
}
if (DB::table('orders')->where('finalized', 1)->doesntExist()) {
    // 無資料
}
```

---

### 1.2 *Select 語句與原生表達式*

#### 1.2.1 **select/addSelect/distinct**

```php
$users = DB::table('users')->select('name', 'email as user_email')->get();
$users = DB::table('users')->distinct()->get();
$query = DB::table('users')->select('name');
$users = $query->addSelect('age')->get();
```
- `select()`：自訂*查詢欄位*。
- `addSelect()`：在原有 select 基礎上*再加欄*位。
- `distinct()`：*去除重複*資料。

---

#### 1.2.2 **原生 SQL 表達式**

```php
$users = DB::table('users')
    ->select(DB::raw('count(*) as user_count, status'))
    ->where('status', '<>', 1)
    ->groupBy('status')
    ->get();
```
- `DB::raw()`：插入原生 SQL 字串，**注意 SQL Injection 風險**。

---

##### *常用 raw 方法*

```php
$orders = DB::table('orders')->selectRaw('price * ? as price_with_tax', [1.0825])->get(); 
// 使用 selectRaw 插入原生 SQL 表達式，計算 price 欄位的含稅價格，稅率為 1.0825，並返回含稅價格作為 price_with_tax 欄位
// SELECT price * 1.0825 as price_with_tax
// FROM orders;

$orders = DB::table('orders')->whereRaw('price > IF(state = "TX", ?, 100)', [200])->get(); 
// 使用 whereRaw 插入原生 SQL 表達式，根據 state 欄位的值判斷 price 的篩選條件：如果 state 是 "TX"，則篩選 price > 200；否則篩選 price > 100

$orders = DB::table('orders')->select('department', DB::raw('SUM(price) as total_sales'))
          ->groupBy('department')
          ->havingRaw('SUM(price) > ?', [2500])
          ->get(); 
          // 使用 select 和 DB::raw 計算每個 department 的銷售總額（SUM(price)），並使用 groupBy 按 department 分組，最後使用 havingRaw 篩選銷售總額大於 2500 的部門

$orders = DB::table('orders')->orderByRaw('updated_at - created_at DESC')->get(); 
// 使用 orderByRaw 插入原生 SQL 表達式，根據 updated_at - created_at 的結果進行降序排序，排序邏輯是訂單的更新時間與創建時間的差值

$orders = DB::table('orders')->select('city', 'state')->groupByRaw('city, state')->get(); 
// 使用 groupByRaw 插入原生 SQL 表達式，按 city 和 state 欄位進行分組，返回每個城市和州的唯一組合
```
- `selectRaw`、`whereRaw`、`havingRaw`、`orderByRaw`、`groupByRaw`：插入原生 SQL。

---

### 1.3 *Join 語法*

#### 1.3.1 **基本 join/leftJoin/rightJoin/crossJoin**

```php
$users = DB::table('users')
         ->join('contacts', 'users.id', '=', 'contacts.user_id')
         ->join('orders', 'users.id', '=', 'orders.user_id')
         ->select('users.*', 'contacts.phone', 'orders.price')
         ->get();
$users = DB::table('users')->leftJoin('posts', 'users.id', '=', 'posts.user_id')->get();
$users = DB::table('users')->rightJoin('posts', 'users.id', '=', 'posts.user_id')->get();
$sizes = DB::table('sizes')->crossJoin('colors')->get();
```
- `join`：內連接(**INNER JOIN**)。
- `leftJoin`、`rightJoin`：左/右連接。
- `crossJoin`：*笛卡兒積*。
  - 笛卡爾積是**兩個資料表的所有可能組合**。
  - 它**不需要匹配條件**，直接返回兩個資料表的所有可能組合。
  - 如果第一個資料表有 m 列，第二個資料表有 n 列，則笛卡爾積的結果集會有 m * n 列。
  - **注意事項**
    - *笛卡爾積的結果集可能非常大*
      - 如果資料表 A 有 1000 列，資料表 B 有 1000 列，笛卡爾積的結果集會有 1000 * 1000 = 1,000,000 列。
      - 在大型資料表中，生成笛卡爾積可能會`導致性能問題`。
    - *避免不必要的笛卡爾積*
      - 笛卡爾積通常是由`錯誤的 JOIN 操作`或`缺少條件`引起的。
      - 確保在 JOIN 中正確指定 ON 或 WHERE 條件。

  ```sql      
  SELECT *
  FROM A
  CROSS JOIN B;
  ```
  - **資料表 A**
  | id   | name     |
  |------|----------|
  | 1    | Alice    |
  | 2    | Bob      |

  - **資料表 B**
  | product_id | product_name |
  |------------|--------------|
  | 101        | Laptop       |
  | 102        | Phone        |

  - **笛卡爾積的結果**
  | id   | name     | product_id | product_name |
  |------|----------|------------|--------------|
  | 1    | Alice    | 101        | Laptop       |
  | 1    | Alice    | 102        | Phone        |
  | 2    | Bob      | 101        | Laptop       |
  | 2    | Bob      | 102        | Phone        |

- `FULL OUTER JOIN`
```php

$leftJoin = DB::table('A')
    ->leftJoin('B', 'A.id', '=', 'B.product_id')
    ->select('A.id', 'A.name', 'B.product_id', 'B.product_name');

$rightJoin = DB::table('B')
    ->rightJoin('A', 'A.id', '=', 'B.product_id')
    ->select('A.id', 'A.name', 'B.product_id', 'B.product_name');

$fullOuterJoin = $leftJoin->union($rightJoin)->get();
```

**INNER JOIN**
| id | name   | product_id | product_name |
|----|--------|------------|--------------|
| 1  | Alice  | 1          | Laptop       |
| 2  | Bob    | 2          | Phone        |

**LEFT JOIN**
| id | name     | product_id | product_name |
|----|----------|------------|--------------|
| 1  | Alice    | 1          | Laptop       |
| 2  | Bob      | 2          | Phone        |
| 3  | Charlie  | NULL       | NULL         |

**RIGHT JOIN**
| id   | name   | product_id | product_name |
|------|--------|------------|--------------|
| 1    | Alice  | 1          | Laptop       |
| 2    | Bob    | 2          | Phone        |
| NULL | NULL   | 4          | Tablet       |

**FULL OUTER JOIN**
| id   | name     | product_id | product_name |
|------|----------|------------|--------------|
| 1    | Alice    | 1          | Laptop       |
| 2    | Bob      | 2          | Phone        |
| 3    | Charlie  | NULL       | NULL         |
| NULL | NULL     | 4          | Tablet       |

---

#### 1.3.2 **進階 join 條件**

```php
DB::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')->orOn(/* ... */);
    })
    ->get();
// SELECT *
// FROM users
// JOIN contacts
// ON users.id = contacts.user_id
//    OR /* ... */;

DB::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
            ->where('contacts.user_id', '>', 5);
    })
    ->get();
// SELECT *
// FROM users
// JOIN contacts
// ON users.id = contacts.user_id
// WHERE contacts.user_id > 5;

```
- join 的第二參數可用`閉包`，取得 `JoinClause` 實例，進行複雜條件設定。

---

#### 1.3.3 **子查詢 join/subquery join/lateral join**

- *子查詢 Join*（Subquery Join）
  - **定義**
    - 子查詢 Join 是指`將一個子查詢的結果作為一個虛擬表，並與主查詢進行 JOIN 操作`。
    - 子查詢是`靜態`的，`執行一次後`生成一個固定的結果集（虛擬表）。
    - 主查詢只需要匹配靜態的子查詢結果，`執行效率較高`。
    - Laravel 提供了 `joinSub`、`leftJoinSub` 和 `rightJoinSub` 方法來實現子查詢 Join。
  - **使用場景**
    - 當需要`基於子查詢的結果`進行匹配時使用。
    - 例如：查詢每個使用者的最新文章，並將其與使用者表進行連接。

```php
$latestPosts = DB::table('posts')
    ->select('user_id', DB::raw('MAX(created_at) as last_post_created_at')) // 查詢每個使用者的最新文章時間
    ->where('is_published', true) // 篩選已發佈的文章
    ->groupBy('user_id'); // 按 user_id 分組

$users = DB::table('users')
    ->joinSub($latestPosts, 'latest_posts', function ($join) {
        $join->on('users.id', '=', 'latest_posts.user_id'); // 將 users.id 與 latest_posts.user_id 進行匹配
    })->get();
```
```sql
SELECT users.*, latest_posts.last_post_created_at
FROM users
JOIN (
    SELECT user_id, MAX(created_at) as last_post_created_at
    FROM posts
    WHERE is_published = true
    GROUP BY user_id
) as latest_posts
ON users.id = latest_posts.user_id;
```

- **資料表 posts**
| id   | user_id | title   | created_at | is_published |
|------|---------|---------|------------|--------------|
| 1    | 1       | Post A  | 2023-01-01 | true         |
| 2    | 1       | Post B  | 2023-01-02 | true         |
| 3    | 2       | Post C  | 2023-01-03 | true         |

- **資料表 users**
| id   | name     |
|------|----------|
| 1    | Alice    |
| 2    | Bob      |

- **結果**
| id   | name     | last_post_created_at |
|------|----------|-----------------------|
| 1    | Alice    | 2023-01-02            |
| 2    | Bob      | 2023-01-03            |

- **行為**
  - *子查詢的執行*：
    - 子查詢獨立執行，生成一個`靜態的結果集`（虛擬表 `latest_posts`）。
      | user_id | last_post_created_at |
      |---------|----------------------|
      | 1       | 2023-01-04           |
      | 2       | 2023-01-05           |
    - `子查詢的結果不依賴主查詢的欄位`。
  - *匹配的行為*：
    - 主查詢使用 `ON users.id = latest_posts.user_id` 將主查詢的欄位與子查詢的結果進行匹配。
    - 這種匹配是**主查詢的行為**，而**不是子查詢執行時的依賴**。
  - *子查詢的結果*：
    - 每個 `user_id` 的最新文章的創建時間（匯總結果）。
  - *主查詢的匹配*：
    - 主查詢中的 `users.id` 與子查詢中的 `latest_posts.user_id` 進行匹配。
    - 匹配的行為是**主查詢的邏輯**，而不是子查詢執行時的依賴。

- *Lateral Join*
  - **定義**
    - Lateral Join 是一種特殊的子查詢 Join，`允許子查詢引用外部查詢的欄位`。
    - 子查詢是`動態的`，會根據主查詢的每一列執行一次。
    - 它的子查詢可以`基於主查詢的每一列進行動態計算`。
    - 如果主查詢有很多列，子查詢的執行次數會非常多，導致`更高的計算成本`。
  - **使用場景**
    - 當需要基於`主查詢的每一列`進行子查詢時使用。
    - 例如：查詢每個使用者的最新 3 篇文章。

```php
// Lateral join（MySQL 8.0.14+、PostgreSQL、SQL Server 支援）
$latestPosts = DB::table('posts')
    ->select('id as post_id', 'title as post_title', 'created_at as post_created_at') // 查詢文章的基本資訊
    ->whereColumn('user_id', 'users.id') // 動態匹配 user_id 與 users.id
    // 子查詢中的 posts.user_id 與主查詢中的 users.id 進行動態匹配。
    // 這表示子查詢的結果會根據主查詢的每一列（users.id）進行篩選。
    // 子查詢 $latestPosts 不會獨立執行，而是根據主查詢的每一列（users.id）動態生成結果。
    // 例如：
    // 當主查詢的 users.id = 1 時，子查詢只返回 posts.user_id = 1 的文章。
    // 當主查詢的 users.id = 2 時，子查詢只返回 posts.user_id = 2 的文章。
    //     ->orderBy('created_at', 'desc') // 按文章創建時間降序排列
    //     ->limit(3); // 只取最新的 3 篇文章

$users = DB::table('users')
    ->joinLateral($latestPosts, 'latest_posts') // 使用 Lateral Join
    // latest_posts 是子查詢的別名（alias）。它代表子查詢的結果，並將其作為一個虛擬表來使用。
    // joinLateral 將子查詢的結果（latest_posts）與主查詢（users 表）進行連接。
    // 子查詢可以動態引用主查詢的欄位（例如 users.id）。

    // 第一個參數是子查詢（Query Builder 的實例），它定義了要執行的子查詢邏輯。
    // 第二個參數是子查詢的別名（alias），用於在主查詢中引用子查詢的結果。
    // 別名是必需的，因為子查詢的結果需要一個名稱來在主查詢中使用。
    // 在主查詢中，可以通過 latest_posts.post_id、latest_posts.post_title 等欄位引用子查詢的結果。
    ->get();
```

```sql
SELECT users.*, latest_posts.post_id, latest_posts.post_title, latest_posts.post_created_at
FROM users
JOIN LATERAL (
    SELECT id as post_id, title as post_title, created_at as post_created_at
    FROM posts
    WHERE posts.user_id = users.id
    ORDER BY created_at DESC
    LIMIT 3
) as latest_posts;
```

- **資料表 posts**
| id   | user_id | title   | created_at | is_published |
|------|---------|---------|------------|--------------|
| 1    | 1       | Post A  | 2023-01-01 | true         |
| 2    | 1       | Post B  | 2023-01-02 | true         |
| 3    | 2       | Post C  | 2023-01-03 | true         |

- **資料表 users**
| id   | name     |
|------|----------|
| 1    | Alice    |
| 2    | Bob      |

- **結果**
| id   | name     | post_id | post_title | post_created_at |
|------|----------|---------|------------|-----------------|
| 1    | Alice    | 2       | Post B     | 2023-01-02      |
| 1    | Alice    | 1       | Post A     | 2023-01-01      |
| 2    | Bob      | 3       | Post C     | 2023-01-03      |

- **行為**
  - *子查詢的執行*：
    - 子查詢`依賴主查詢的欄位（users.id）`，並根據主查詢的每一列`動態生成結果`。
    - 子查詢不獨立執行，而是針對主查詢的每一列執行一次。
    - 當主查詢的 `users.id = 1` 時，子查詢執行以下邏輯：
    ```sql
    SELECT id as post_id, title as post_title, created_at as post_created_at
    FROM posts
    WHERE posts.user_id = 1
    ORDER BY created_at DESC
    LIMIT 3;
    ```
    - 當主查詢的 `users.id = 2` 時，子查詢執行以下邏輯：
    ```sql
    SELECT id as post_id, title as post_title, created_at as post_created_at
    FROM posts
    WHERE posts.user_id = 2
    ORDER BY created_at DESC
    LIMIT 3;
    ```
  - *匹配的行為*：
    - `子查詢的結果直接基於主查詢的欄位生成`，因此不需要額外的匹配邏輯。
  - *子查詢的結果*：
    - 每個使用者的最新 3 篇文章。
  - *主查詢的匹配*：
    - 主查詢中的 `users.id` 與子查詢中的 `posts.user_id` 動態匹配。
    - 子查詢的結果已經基於主查詢的欄位生成，因此不需要額外的匹配邏輯。

- **子查詢 Join 與 Lateral Join 的區別**
| 特性           | 靜態子查詢（普通 JOIN）              | 動態子查詢（LATERAL JOIN）          |
|----------------|----------------------------------|-----------------------------------|
| 子查詢的執行     | 子查詢獨立執行，與主查詢無關          | 子查詢依賴主查詢的欄位，動態生成結果    |
| 匹配的行為       | 主查詢使用 ON 進行匹配              | 子查詢的結果直接基於主查詢的欄位生成    |
| 子查詢的結果      | 每個使用者的匯總結果（例如最新文章時間）| 每個使用者的最新 3 篇文章            |
| 使用場景         | 用於靜態的子查詢結果                 | 用於動態匹配主查詢的每一列           |
| 性能            | 子查詢結果通常較固定                 | Lateral Join 更靈活，但可能影響性能  |

---

### 1.4 *Union 查詢*

```php
$first = DB::table('users')->whereNull('first_name');
$users = DB::table('users')->whereNull('last_name')->union($first)->get();
```
- `union`：合併兩個查詢，**去除重複**。
- `unionAll`：合併查詢，**不去重複**。

---

### 1.5 *完整 Controller 範例*

```php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * 顯示所有使用者列表。
     */
    public function index(): View
    {
        $users = DB::table('users')->get();
        // 回傳 Blade 視圖，傳遞 users 變數
        return view('user.index', ['users' => $users]);
    }
}
```
- 實務上常於 Controller 內查詢資料並傳遞給 Blade。

---

### 1.6 *Laravel Collection 補充*

- `Query Builder` 查詢結果為 `Illuminate\Support\Collection`，可用各種方法處理資料。
- 常用方法：`map`、`filter`、`reduce`、`pluck`、`each`、`first`、`last`。

```php
$users = DB::table('users')->get();
$names = $users->pluck('name'); // 取出所有 name 欄位
$activeUsers = $users->filter(fn($user) => $user->active);
```

---

### 1.7 *chunkById/lazyById 條件分組範例*

```php
DB::table('users')->where(function ($query) {
    $query->where('credits', 1)
          ->orWhere('credits', 2);
// SELECT * FROM users
// WHERE credits = 1 OR credits = 2
// ORDER BY id ASC
// LIMIT 100;
// 因為 chunkById(100, function ($users) { ... }) 的行為需要分批處理資料，因此需要使用 where 條件包起來，以確保查詢邏輯正確地應用到每次分批的查詢中。
})->chunkById(100, function ($users) {
    foreach ($users as $user) {
        DB::table('users')->where('id', $user->id)
                          ->update(['credits' => 3]);
//                          UPDATE users
//                          SET credits = 3
//                          WHERE id = 1;

//                          UPDATE users
//                          SET credits = 3
//                          WHERE id = 2;

//                          UPDATE users
//                          SET credits = 3
//                          WHERE id = 3;

// -- 持續執行，直到處理完第一批記錄
    }
});
```
- `chunkById/lazyById` 會**自動加上主鍵條件**，若有複雜 `where`，建議用閉包分組。

---

### 1.8 *Raw SQL Injection 警語補充*

- **警語**： 只要用到 `DB::raw` 或 `*Raw` 方法，Laravel 無法保證查詢安全，請務必自行檢查與過濾輸入，避免 SQL Injection。

---

### 1.9 *join 進階用法補充*

```php
DB::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
             ->orOn('users.email', '=', 'contacts.email')
             ->where('contacts.verified', true)
             ->orWhere('contacts.level', '>', 5);
    })
    ->get();
```
- JoinClause 支援 `on`、`orOn`、`where`、`orWhere`，可組合複雜條件。

---

### 1.10 *lateral join 支援版本註解*

- `joinLateral`、`leftJoinLateral` 僅支援 PostgreSQL、MySQL 8.0.14+、SQL Server。
- 使用時請確認資料庫版本。

---

### 1.11 *unionAll 範例補充*

```php
$first = DB::table('users')->whereNull('first_name');
$users = DB::table('users')->whereNull('last_name')->unionAll($first)->get();
```
- `unionAll`：合併查詢，**不去除重複資料**。


---

## 2. **Where 條件查詢**

### 2.1 *基本 where 語法*

#### 2.1.1 **單一條件與多條件查詢**

```php
// 基本 where 語法
$users = DB::table('users')
    ->where('votes', '=', 100)
    ->where('age', '>', 35)
    ->get();

// 省略 = 號
$users = DB::table('users')->where('votes', 100)->get();

// 多欄位陣列查詢
$users = DB::table('users')->where([
    'first_name' => 'Jane',
    'last_name' => 'Doe',
])->get();

// 多條件陣列（每個條件為陣列）
$users = DB::table('users')->where([
    ['status', '=', '1'],
    ['subscribed', '<>', '1'],
])->get();
```
- where 支援多種寫法，陣列可快速組合多條件。
- 支援所有資料庫運算子（=、>=、<、<>、like...）。

---

#### 2.1.2 **型別陷阱**

- `MySQL/MariaDB`中，當字串與數字進行比較時，*字串會自動轉換為數字*。
- 例：`User::where('secret', 0)`，若 secret 欄位為 'aaa'，也會被查出。
  - 如果字串無法轉換為有效的數字（例如 'aaa'），MySQL 會將其轉換為 0。
  - 如果字串的內容是有效的數字（例如 '123'），MySQL 會將其轉換為該數字（123）。
  
- **建議**： 查詢前務必型別轉換。

---

### 2.2 *orWhere 與條件分組*

```php
// orWhere 基本用法
$users = DB::table('users')
    ->where('votes', '>', 100)
    ->orWhere('name', 'John')
    ->get();

// orWhere 分組（建議用閉包避免邏輯錯誤）
$users = DB::table('users')
    ->where('votes', '>', 100)
    ->orWhere(function ($query) {
        $query->where('name', 'Abigail')
            ->where('votes', '>', 50);
    })
    ->get();
```
- `orWhere` 會用 OR 連接條件，複雜邏輯建議用閉包分組。

---

### 2.3 *whereNot / orWhereNot 條件反向*

```php
$products = DB::table('products')
    ->whereNot(function ($query) {
        $query->where('clearance', true)
            ->orWhere('price', '<', 10);
    })
    ->get();
```
- `whereNot`、`orWhereNot` 可反向條件群組。

---

### 2.4 *whereAny / whereAll / whereNone 多欄位條件*

```php
// 任一欄位 like
$users = DB::table('users')
    ->where('active', true)
    ->whereAny([
        'name', 'email', 'phone',
    ], 'like', 'Example%')
    ->get();

// 所有欄位皆 like
$posts = DB::table('posts')
    ->where('published', true)
    ->whereAll([
        'title', 'content',
    ], 'like', '%Laravel%')
    ->get();

// 無任一欄位 like
$posts = DB::table('albums')
    ->where('published', true)
    ->whereNone([
        'title', 'lyrics', 'tags',
    ], 'like', '%explicit%')
    ->get();
```
- `whereAny`：**任一**欄位符合條件。
- `whereAll`：所有欄位**皆需**符合。
- `whereNone`：所有欄位**皆不**符合。

---

### 2.5 *JSON where 查詢*

```php
// JSON 欄位查詢
$users = DB::table('users')
    ->where('preferences->dining->meal', 'salad')
    ->get();

// whereJsonContains/whereJsonDoesntContain
$users = DB::table('users')->whereJsonContains('options->languages', 'en')->get();
$users = DB::table('users')->whereJsonDoesntContain('options->languages', 'en')->get();

// 多值查詢（MariaDB/MySQL/PostgreSQL 支援）
$users = DB::table('users')->whereJsonContains('options->languages', ['en', 'de'])->get();

// 查詢 JSON key
$users = DB::table('users')->whereJsonContainsKey('preferences->dietary_requirements')->get();
$users = DB::table('users')->whereJsonDoesntContainKey('preferences->dietary_requirements')->get();

// 查詢 JSON 陣列長度
$users = DB::table('users')->whereJsonLength('options->languages', 0)->get();
$users = DB::table('users')->whereJsonLength('options->languages', '>', 1)->get();
```
- 支援 MariaDB 10.3+、MySQL 8.0+、PostgreSQL 12+、SQL Server 2017+、SQLite 3.39+。

---

### 2.6 *whereLike / whereNotLike / orWhereLike / orWhereNotLike*

```php
$users = DB::table('users')->whereLike('name', '%John%')->get();
$users = DB::table('users')->whereLike('name', '%John%', caseSensitive: true)->get();
$users = DB::table('users')->whereNotLike('name', '%John%')->get();
$users = DB::table('users')->orWhereLike('name', '%John%')->get();
$users = DB::table('users')->orWhereNotLike('name', '%John%')->get();
```
- `whereLike` 預設**不分大小寫**，可用 `caseSensitive` 參數。
- `SQL Server` 不支援 `caseSensitive。`

---

### 2.7 *whereIn / whereNotIn / orWhereIn / orWhereNotIn*

```php
$users = DB::table('users')->whereIn('id', [1, 2, 3])->get();
$users = DB::table('users')->whereNotIn('id', [1, 2, 3])->get();

// 子查詢
$activeUsers = DB::table('users')->select('id')->where('is_active', 1);
$users = DB::table('comments')->whereIn('user_id', $activeUsers)->get();
```
- `whereIn/whereNotIn` 支援陣列或子查詢。
- **大量整數**建議用 `whereIntegerInRaw/whereIntegerNotInRaw`。

---

### 2.8 *whereBetween / whereNotBetween / whereBetweenColumns / whereNotBetweenColumns*

```php
$users = DB::table('users')->whereBetween('votes', [1, 100])->get();
$users = DB::table('users')->whereNotBetween('votes', [1, 100])->get();
$patients = DB::table('patients')->whereBetweenColumns('weight', ['minimum_allowed_weight', 'maximum_allowed_weight'])->get();
$patients = DB::table('patients')->whereNotBetweenColumns('weight', ['minimum_allowed_weight', 'maximum_allowed_weight'])->get();
```
- `whereBetweenColumns`：**欄位值**介於兩個欄位之間。

---

### 2.9 *whereValueBetween / whereValueNotBetween*

```php
$products = DB::table('products')->whereValueBetween(100, ['min_price', 'max_price'])->get();
$products = DB::table('products')->whereValueNotBetween(100, ['min_price', 'max_price'])->get();
```
- `whereValueBetween`：**值**介於兩欄位之間。

---

### 2.10 *whereNull / whereNotNull / orWhereNull / orWhereNotNull*

```php
$users = DB::table('users')->whereNull('updated_at')->get();
$users = DB::table('users')->whereNotNull('updated_at')->get();
```
- `whereNull/whereNotNull` 判斷欄位是否為 `NULL`。

---

### 2.11 *whereDate / whereMonth / whereDay / whereYear / whereTime*

```php
$users = DB::table('users')->whereDate('created_at', '2016-12-31')->get();
$users = DB::table('users')->whereMonth('created_at', '12')->get();
$users = DB::table('users')->whereDay('created_at', '31')->get();
$users = DB::table('users')->whereYear('created_at', '2016')->get();
$users = DB::table('users')->whereTime('created_at', '=', '11:20:45')->get();
```
- `whereDate/whereMonth/whereDay/whereYear/whereTime` 可針對日期欄位查詢。

---

### 2.12 *wherePast / whereFuture / whereToday / whereBeforeToday / whereAfterToday*

```php
$invoices = DB::table('invoices')->wherePast('due_at')->get();
$invoices = DB::table('invoices')->whereFuture('due_at')->get();
$invoices = DB::table('invoices')->whereNowOrPast('due_at')->get();
$invoices = DB::table('invoices')->whereNowOrFuture('due_at')->get();
$invoices = DB::table('invoices')->whereToday('due_at')->get();
$invoices = DB::table('invoices')->whereBeforeToday('due_at')->get();
$invoices = DB::table('invoices')->whereAfterToday('due_at')->get();
$invoices = DB::table('invoices')->whereTodayOrBefore('due_at')->get();
$invoices = DB::table('invoices')->whereTodayOrAfter('due_at')->get();
```
- `wherePast/whereFuture/whereToday`... 可針對日期欄位進行各種時間條件查詢。

---

### 2.13 *whereColumn / orWhereColumn*

```php
$users = DB::table('users')->whereColumn('first_name', 'last_name')->get();
$users = DB::table('users')->whereColumn('updated_at', '>', 'created_at')->get();
$users = DB::table('users')->whereColumn([
    ['first_name', '=', 'last_name'],
    ['updated_at', '>', 'created_at'],
])->get();
```
- `whereColumn` 可比較兩個欄位值。

---

### 2.14 *邏輯分組（grouping）*

```php
$users = DB::table('users')
    ->where('name', '=', 'John')
    ->where(function ($query) {
        $query->where('votes', '>', 100)
            ->orWhere('title', '=', 'Admin');
    })
    ->get();
```
- 傳入閉包可將多個 where 條件分組，產生**括號效果**。

---

## 3. **進階 Where 條件**

### 3.1 *whereExists 子查詢*

```php
$users = DB::table('users')
    ->whereExists(function ($query) {
        $query->select(DB::raw(1))
            ->from('orders')
            ->whereColumn('orders.user_id', 'users.id');
    })
    ->get();

// 或直接傳入查詢物件
$orders = DB::table('orders')
        ->select(DB::raw(1))
        ->whereColumn('orders.user_id', 'users.id');
$users = DB::table('users')->whereExists($orders)->get();
```

- **固定值**
  - `SELECT 1` 是一個*固定的值*，表示*子查詢的結果*。
  - 它的作用是告訴 SQL 查詢引擎，子查詢只需要返回一個值（通常是 1），而*不需要返回具體的欄位*。
- **配合 WHERE EXISTS**
  - 在 `WHERE EXISTS` 中，子查詢的重點是`判斷子查詢是否有結果`，而不是返回具體的資料。
  - `SELECT 1` 是一種*簡化的寫法*，表示只需要判斷子查詢是否存在。
- **性能**
  - `SELECT 1` 不需要返回具體的欄位，因此執行*效率更高*。
  - SQL 查詢引擎只需要判斷子查詢是否有結果，而不需要處理具體的欄位資料。
- **簡化邏輯**
  - 在 `WHERE EXISTS` 中，子查詢的重點是判斷是否存在符合條件的記錄，而不是返回具體的資料。
  - 使用 `SELECT 1` 可以簡化子查詢的邏輯。

- **產生 SQL**：
```sql
select * from users where exists (
    select 1 from orders where orders.user_id = users.id
)
```
- `whereExists` 可用於判斷關聯資料**是否**存在。

---

### 3.2 *子查詢 where 子句*

```php
// 比較子查詢結果與值
$users = User::where(function ($query) {
    $query->select('type')
          ->from('membership')
          ->whereColumn('membership.user_id', 'users.id')
          ->orderByDesc('membership.start_date')
          ->limit(1);
}, 'Pro')->get();

// 比較欄位與子查詢結果
$incomes = Income::where('amount', '<', function ($query) {
    $query->selectRaw('avg(i.amount)')->from('incomes as i');
})->get();
```
- 可用 `closure` 或查詢物件作為 `where` 子查詢。

---

### 3.3 *全文檢索 whereFullText*

- 使用`全文檢索`（Full-Text Search）來篩選資料。
- 適用於 MySQL 的全文索引（Full-Text Index），通常用於**高效地查詢大段文字內容**。

```php
$users = DB::table('users')
    ->whereFullText('bio', 'web developer') // 使用全文檢索篩選 bio 欄位中包含 "web developer" 的記錄
    ->get(); // 取得符合條件的所有記錄
             // 回傳結果
             // id	name	bio
             // 1	Alice	Experienced web developer.
             // 3	Charlie	Web developer specializing in PHP.
```
- 支援 `MariaDB、MySQL、PostgreSQL`，需建立全文索引。
- 會自動產生 `MATCH AGAINST` 或`對應 SQL`。

---

## 4. **排序、分組、限制、條件查詢**

### 4.1 *排序 orderBy/最新/隨機*

```php
// 單欄位排序
$users = DB::table('users')->orderBy('name', 'desc')->get();
// 多欄位排序
$users = DB::table('users')->orderBy('name', 'desc')->orderBy('email', 'asc')->get();
// 預設升冪，降冪可用 orderByDesc
$users = DB::table('users')->orderByDesc('verified_at')->get();
// JSON 欄位排序
$corporations = DB::table('corporations')->where('country', 'US')->orderBy('location->state')->get();
// 最新/最舊
$user = DB::table('users')->latest()->first();
// 隨機排序
$randomUser = DB::table('users')->inRandomOrder()->first();
```
- `orderBy` 可多次呼叫，支援多欄位排序。
- `latest/oldest` 預設用 `created_at`。
- `inRandomOrder` 取得**隨機資料**。

---

#### 4.1.1 **移除排序 reorder**

```php
$query = DB::table('users')->orderBy('name');
$unorderedUsers = $query->reorder()->get();
$usersOrderedByEmail = $query->reorder('email', 'desc')->get();
$usersOrderedByEmail = $query->reorderDesc('email')->get();
```
- `reorder` 可*移除所有排序條件*，並可重新指定排序。

---

### 4.2 *分組 groupBy/having/havingBetween*

```php
$users = DB::table('users')
       ->groupBy('account_id')
       ->having('account_id', '>', 100)
       ->get();

// 多欄位分組
$users = DB::table('users')
       ->groupBy('first_name', 'status')
       ->having('account_id', '>', 100)
       ->get();

// havingBetween 範圍
$report = DB::table('orders')
        ->selectRaw('count(id) as number_of_orders, customer_id')
        ->groupBy('customer_id')
        ->havingBetween('number_of_orders', [5, 15])
        ->get();
```
- `groupBy` 可 **多欄位**。
- `having` 支援 **各種運算子**。
- `havingBetween` **範圍查詢**。

---

### 4.3 *限制 limit/offset*

```php
$users = DB::table('users')->offset(10)->limit(5)->get();
```
- `offset` **跳過** 前 n 筆。
- `limit` **取** n 筆。

---

### 4.4 *條件式查詢 when*

- `when`
  - when 用於根據條件**動態地**添加查詢邏輯。
  - 它可以**根據布林值或其他條件**，執行不同的查詢邏輯。

- `where`
  - where 用於**直接添加篩選條件**到查詢中。
  - 它是**固定的**，無法根據條件動態地改變查詢邏輯。

```php
$role = $request->input('role');
$users = DB::table('users')
       ->when($role, function ($query, $role) {
           $query->where('role_id', $role);
       })
       ->get();

// 可傳第三個參數，條件不成立時執行
$sortByVotes = $request->boolean('sort_by_votes'); 
// 從 HTTP 請求中取得 'sort_by_votes' 的布林值，決定是否按 votes 排序

$users = DB::table('users') // 查詢 users 表
       ->when($sortByVotes, function ($query, $sortByVotes) { // 如果 $sortByVotes 為 true，執行第一個回呼函式
           $query->orderBy('votes'); // 按 votes 欄位進行排序
       }, function ($query) { // 如果 $sortByVotes 為 false，執行第二個回呼函式
           $query->orderBy('name'); // 按 name 欄位進行排序
       })
       ->get(); // 執行查詢並取得結果
```
- `when` 可根據條件決定是否加入查詢條件。
- 第三個參數為 `else` 條件。

---

## 5. **資料新增、更新、刪除**

### 5.1 *insert 新增資料*

```php
// 單筆新增
DB::table('users')->insert([
    'email' => 'kayla@example.com',
    'votes' => 0
]);

// 多筆新增
DB::table('users')->insert([
    ['email' => 'picard@example.com', 'votes' => 0],
    ['email' => 'janeway@example.com', 'votes' => 0],
]);

// 忽略錯誤（如重複鍵）
DB::table('users')->insertOrIgnore([
    ['id' => 1, 'email' => 'sisko@example.com'], // 插入第一筆資料，id 為 1，email 為 sisko@example.com
    ['id' => 2, 'email' => 'archer@example.com'], // 插入第二筆資料，id 為 2，email 為 archer@example.com
]);

// 插入多筆資料到 users 表。
// 如果插入的資料違反唯一性約束（例如 id 已存在），該筆資料會被忽略，而不會拋出錯誤。
// 適合用於避免重複插入資料。

// 子查詢插入
DB::table('pruned_users')->insertUsing([
    'id', 'name', 'email', 'email_verified_at' // 定義要插入的欄位
], DB::table('users')->select(
    'id', 'name', 'email', 'email_verified_at' // 從 users 表中選取這些欄位
)->where('updated_at', '<=', now()->subMonth())); // 篩選 updated_at 在一個月前的記錄
// insertUsing：
// 從子查詢的結果插入資料到 pruned_users 表。
// 第一個參數是要插入的欄位（id, name, email, email_verified_at）。
// 第二個參數是子查詢，定義要插入的資料來源。

// 子查詢：
// 從 users 表中選取 id, name, email, email_verified_at 欄位。
// 篩選 updated_at 在一個月前的記錄（updated_at <= now()->subMonth()）。

```
- `insertOrIgnore` **會忽略重複鍵**等錯誤。
- `insertUsing` 可用子查詢**批次插入**。

---

#### 5.1.1 **自動遞增主鍵 insertGetId**

- 於`插入一筆資料到資料表，並返回該筆資料的主鍵值`（通常是自動遞增的 id）。它的作用除了正常插入資料外，還能讓你在插入後立即取得該筆資料的唯一識別碼，方便後續操作。

```php
$id = DB::table('users')->insertGetId([
    'email' => 'john@example.com', // 插入的 email 欄位值為 'john@example.com'
    'votes' => 0 // 插入的 votes 欄位值為 0
    // insertGetId：
    // 用於插入一筆資料到資料表，並返回該筆資料的主鍵（通常是自動遞增的 id）。
    // 適合用於需要插入資料後立即取得該筆資料的唯一識別碼。

    // 插入的資料：
    // email 欄位的值為 'john@example.com'。
    // votes 欄位的值為 0。
    
    // 返回值：
    // $id 會保存插入的這筆資料的主鍵值（例如 id）。
]);
```
- 回傳`自動遞增 id`。
- `PostgreSQL` 預設抓 `id` 欄位，如需其他 `sequence` *可傳第二參數*。

---

### 5.2 *upsert 批次新增或更新*

```php
DB::table('flights')->upsert(
    [
        ['departure' => 'Oakland', 'destination' => 'San Diego', 'price' => 99], 
        // 第一筆資料：出發地 Oakland，目的地 San Diego，價格 99
        ['departure' => 'Chicago', 'destination' => 'New York', 'price' => 150] 
        // 第二筆資料：出發地 Chicago，目的地 New York，價格 150
    ],
    ['departure', 'destination'], // 第二參數：唯一性檢查的欄位組合，檢查 departure 和 destination 是否已存在
    ['price'] // 第三參數：如果資料已存在，更新 price 欄位
);
```
- 依據第二參數欄位**判斷唯一性**，存在則更新，否則新增。
- 除 `SQL Server` 外，**必須有唯一鍵或主鍵**。
- `MariaDB/MySQL` 會**自動**依據主鍵/唯一鍵判斷。

---

### 5.3 *update 更新資料*

```php
$affected = DB::table('users')
    ->where('id', 1)
    ->update(['votes' => 1]);
```
- 回傳受影響筆數。

---

#### 5.3.1 **updateOrInsert 條件更新或新增**

```php
DB::table('users')
    ->updateOrInsert(
        ['email' => 'john@example.com', 'name' => 'John'], 
        // 條件：檢查是否存在 email 為 'john@example.com' 且 name 為 'John' 的記錄
        ['votes' => '2'] 
        // 如果存在，更新 votes 欄位為 '2'；如果不存在，插入新記錄，並設置 votes 欄位為 '2'
    );

// 支援 closure 動態決定欄位
DB::table('users')->updateOrInsert(
    ['user_id' => $user_id], // 條件：檢查是否存在 user_id 為 $user_id 的記錄
    fn ($exists) => $exists ? 
    [ // 如果記錄存在，執行 Closure 的第一個分支
        'name' => $data['name'], // 更新 name 欄位為 $data['name']
        'email' => $data['email'], // 更新 email 欄位為 $data['email']
    ] 
    : 
    [ // 如果記錄不存在，執行 Closure 的第二個分支
        'name' => $data['name'], // 插入 name 欄位，值為 $data['name']
        'email' => $data['email'], // 插入 email 欄位，值為 $data['email']
        'marketable' => true, // 插入 marketable 欄位，值為 true
    ],
);
```
- **先**查詢條件，存在則更新，不存在則新增。

---

#### 5.3.2 **更新 JSON 欄位**

```php
$affected = DB::table('users')
    ->where('id', 1)
    ->update(['options->enabled' => true]);
```
- 支援 MariaDB 10.3+、MySQL 5.7+、PostgreSQL 9.5+。

---

### 5.4 *increment/decrement 數值遞增/遞減*

```php
DB::table('users')->increment('votes'); // 將 users 表中的 votes 欄位值遞增 1

DB::table('users')->increment('votes', 5); // 將 users 表中的 votes 欄位值遞增 5

DB::table('users')->decrement('votes'); // 將 users 表中的 votes 欄位值遞減 1

DB::table('users')->decrement('votes', 5); // 將 users 表中的 votes 欄位值遞減 5

DB::table('users')->increment('votes', 1, ['name' => 'John']); // 將 votes 欄位值遞增 1，並將 name 欄位更新為 'John'

DB::table('users')->incrementEach([
    'votes' => 5, // 將 votes 欄位值遞增 5
    'balance' => 100, // 將 balance 欄位值遞增 100
]);
```
- `incrementEach/decrementEach` 可**同時多欄位**。

---

### 5.5 **delete 刪除資料**

```php
$deleted = DB::table('users')->delete();
$deleted = DB::table('users')->where('votes', '>', 100)->delete();
```
- 回傳受影響筆數。

---

## 6. **鎖定、重用、除錯**

### 6.1 *悲觀鎖定 sharedLock/lockForUpdate*

- *悲觀鎖定*(Pessimistic Locking) 是`一種鎖定策略，假設資料可能會被其他交易修改，因此在操作資料之前，會先對資料加鎖`。
- 悲觀鎖定的目的是`防止競爭條件`（Race Condition），`確保資料的一致性`。

```php
// 共享鎖
DB::table('users')
    ->where('votes', '>', 100) // 篩選 votes 欄位值大於 100 的記錄
    ->sharedLock() // 使用共享鎖，允許其他交易讀取，但不允許修改
    ->get(); // 執行查詢並取得結果
    // sharedLock：
    // 在資料庫中使用共享鎖（LOCK IN SHARE MODE）。
    // 允許其他交易讀取被鎖定的記錄，但不允許修改。
    // 適合用於需要保證資料一致性的讀取操作。
    // SELECT *
    // FROM users
    // WHERE votes > 100
    // OCK IN SHARE MODE;
    
// 排他鎖
DB::table('users')
    ->where('votes', '>', 100) // 篩選 votes 欄位值大於 100 的記錄
    ->lockForUpdate() // 使用排他鎖，禁止其他交易讀取或修改
    ->get(); // 執行查詢並取得結果
    // lockForUpdate：
    // 在資料庫中使用排他鎖（FOR UPDATE）。
    // 禁止其他交易讀取或修改被鎖定的記錄。
    // 適合用於需要更新資料的操作，防止競爭條件。
    // SELECT *
    // FROM users
    // WHERE votes > 100
    // FOR UPDATE;
```
- `sharedLock`：
  - 在資料庫中使用 **共享鎖**（LOCK IN SHARE MODE）。
  - 允許其他交易*讀取*被鎖定的記錄，但不允許修改。
  - 適合用於需要**保證資料一致性的讀取操作**。

- `lockForUpdate`：
  - 在查詢期間，對選定的記錄加上 **排他鎖**（Exclusive Lock）。
  - **禁止**其他交易*讀取（共享鎖）* 或 *修改（排他鎖）*，直到當前交易完成。
  - 適合用於需要**更新資料**的操作，防止競爭條件。

- 建議搭配 `transaction` 使用，確保資料一致性。

---

#### 6.1.1 **transaction 交易範例**

```php
DB::transaction(function () { // 開啟資料庫交易，確保所有操作要麼全部成功，要麼全部回滾

    $sender = DB::table('users')->lockForUpdate()->find(1); 
    // 對 id 為 1 的使用者加上排他鎖，禁止其他交易讀取或修改該記錄
    $receiver = DB::table('users')->lockForUpdate()->find(2);
     // 對 id 為 2 的使用者加上排他鎖，禁止其他交易讀取或修改該記錄

    if ($sender->balance < 100) { // 檢查 sender 的餘額是否小於 100
        throw new RuntimeException('Balance too low.'); // 如果餘額不足，拋出例外並回滾交易
    }

    DB::table('users')->where('id', $sender->id)->update([ // 更新 sender 的餘額
        'balance' => $sender->balance - 100 // 減少 100
    ]);

    DB::table('users')->where('id', $receiver->id)->update([ // 更新 receiver 的餘額
        'balance' => $receiver->balance + 100 // 增加 100
    ]);
});
```
- *交易失敗*，會`自動回滾並釋放鎖定`。

---

### 6.2 *查詢重用 tap/pipe*

- *查詢重用*(Reusable Query)的意思是`將查詢邏輯封裝成可重用的元件`（例如類別或函式），以便在多個查詢中使用，避免重複撰寫相同的查詢邏輯。這樣可以提高程式碼的可讀性和可維護性，並支援動態邏輯。

- **查詢重用的核心概念**
  - *封裝邏輯*：
    - 將`查詢條件、排序、分頁`等邏輯**封裝**在類別或函式中。
    - 例如：篩選條件、分頁邏輯。
  - *重用邏輯*：
    - `在不同的查詢中使用相同的封裝邏輯`，**避免重複**撰寫。
    - 例如：篩選目的地的邏輯可以在多個查詢中使用。
  - *動態邏輯*：
    - 支援`根據條件改變查詢行為`，例如動態篩選或排序。

```php
// 可將查詢條件封裝成物件重複使用
class DestinationFilter {
    public function __construct(private ?string $destination) {} // 建構子，接收目的地參數（可以為 null）

    public function __invoke($query) { // 定義可調用的行為，當類別被當作函式使用時執行
        $query->when($this->destination, function ($query) { 
            // 如果 $destination 不為 null，執行篩選邏輯
            $query->where('destination', $this->destination); 
            // 篩選 destination 欄位等於 $destination 的記錄
        });
    }
}
// 使用 tap
DB::table('flights') // 查詢 flights 表
    ->tap(new DestinationFilter($destination)) // 使用 tap 方法，將 DestinationFilter 的邏輯應用到查詢中
    ->orderByDesc('price') // 按 price 欄位降序排列
    ->get(); // 執行查詢並取得結果

// pipe 用於執行查詢並回傳其他型別
class Paginate {
    public function __construct(private $sortBy = 'timestamp', private $sortDirection = 'desc', private $perPage = 25) {} // 建構子，接收排序欄位、方向和每頁記錄數

    public function __invoke($query) { // 定義可調用的行為，當類別被當作函式使用時執行
        return $query->orderBy($this->sortBy, $this->sortDirection) // 按指定欄位和方向排序
                     ->paginate($this->perPage, pageName: 'p'); // 分頁查詢，指定每頁記錄數和分頁參數名稱
    }
}
$flights = DB::table('flights') // 查詢 flights 表
    ->tap(new DestinationFilter($destination)) // 使用 tap 方法，將 DestinationFilter 的邏輯應用到查詢中
    ->pipe(new Paginate); // 使用 pipe 方法，執行 Paginate 的邏輯，並返回分頁結果
```
- `tap` 和 `pipe` 是 `Laravel Query Builder` 中的方法，並不是專屬於 Collection 的方法。它們主要用於查詢的邏輯重用和結果處理

- `tap`：
  - 用於在查詢中**應用封裝的邏輯**（例如篩選條件）。
  - 它允許你在查詢中**插入可重用的邏輯**，並保持查詢的流式操作。

- `pipe`：
  - 用於**執行查詢並進一步處理結果，返回其他型別的資料**（例如分頁結果）。
  - 適合用於需要**對查詢結果進行後續操作或轉換的場景**。

---

### 6.3 *查詢除錯 dd/dump/dumpRawSql/ddRawSql*

```php
<?php
// 使用 dd() 終止程式執行並輸出查詢物件的內容
DB::table('users')->where('votes', '>', 100)->dd(); 
// Laravel 的 dump and die 方法，輸出查詢物件的內容（包含查詢條件、結構等詳細資訊），並終止程式執行。

// 使用 dump() 輸出查詢物件的內容但不終止程式執行
DB::table('users')->where('votes', '>', 100)->dump(); 
// Laravel 的 dump 方法，輸出查詢物件的內容（包含查詢條件、結構等詳細資訊），但不終止程式執行。

// 使用 dumpRawSql() 輸出查詢的原始 SQL 語句但不終止程式執行
DB::table('users')->where('votes', '>', 100)->dumpRawSql(); 
// 輸出查詢的原始 SQL 語句（不包含綁定的參數值），但不終止程式執行。

// 使用 ddRawSql() 輸出查詢的原始 SQL 語句並終止程式執行
DB::table('users')->where('votes', '>', 100)->ddRawSql(); 
// 輸出查詢的原始 SQL 語句（不包含綁定的參數值），並終止程式執行。
```
- `dd`：
  - 顯示查詢的 SQL 語句和綁定的參數。
    - SELECT * FROM users WHERE votes > ?;
    - [100]
  - **終止**程式執行。

- `dump`：
  - 顯示查詢的 SQL 語句和綁定的參數。
    - SELECT * FROM users WHERE votes > ?;
    - [100]
  - **繼續**執行程式。
  
- `dumpRawSql/ddRawSql`：
  - 顯示 原始 SQL 語句（包含佔位符 ?），但不顯示綁定的參數值。
    - SELECT * FROM users WHERE votes > ?;
    - 不顯示綁定的參數值
  - `dumpRawSql` **繼續**執行程式。
  - `ddRawSql` **終止**程式執行。

---