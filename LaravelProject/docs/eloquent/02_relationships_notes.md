# *Laravel Eloquent: Relationships 筆記*

---

## 1. **Introduction**

資料表之間常有關聯，例如：`一篇文章有多個留言`、`一筆訂單屬於某個用戶`。
Eloquent 支援多種常見關聯型態：

- One To One
- One To Many
- Many To Many
- Has One Through
- Has Many Through
- One To One (Polymorphic)
- One To Many (Polymorphic)
- Many To Many (Polymorphic)

---

## 2. **Defining Relationships**

Eloquent 關聯定義為`Model 的方法`，回傳`關聯物件`，可*鏈式查詢*。

```php
$user->posts()->where('active', 1)->get();
```

---

## 3. **One to One / Has One**

### 3.1 *定義一對一關聯*

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Model
{
    /**
     * Get the phone associated with the user.
     */
    public function phone(): HasOne
    {
        return $this->hasOne(Phone::class);
    }
}
```

---

- 取得**關聯資料** 可用`動態屬性`：

```php
$phone = User::find(1)->phone;
```

---

- 預設外鍵為 `user_id`，可自訂：

```php
return $this->hasOne(Phone::class, 'foreign_key');
return $this->hasOne(
    RelatedModel::class,  // 關聯的 Model 類別
    'foreign_key',        // 外鍵（在關聯表中的欄位）
    'local_key'           // 本地鍵（在當前表中的欄位）
);
// 完整語法
return $this->hasOne(
    Phone::class,    // 關聯的 Model
    'user_id',       // 外鍵：phones 表中的 user_id 欄位
    'id'             // 本地鍵：users 表中的 id 欄位
);
```

---

### 3.2 *定義反向關聯*（`belongsTo`）

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phone extends Model
{
    /**
     * Get the user that owns the phone.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

- 可自訂`外鍵`與 `owner key`：

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'foreign_key');
}

public function user(): BelongsTo
{
    return $this->belongsTo(
    ParentModel::class,   // 1. 父 Model（我屬於誰） 
    'foreign_key',        // 2. 外鍵（我表中指向父表的欄位）
    'owner_key'           // 3. 擁有者鍵（父表中被指向的欄位）
    );
    // 完整語法
    return $this->belongsTo(
        User::class,     // 關聯的父 Model
        'user_id',       // 外鍵：phones 表中的 user_id 欄位
        'id'             // 擁有者鍵：users 表中的 id 欄位
    );
}
```

---

## 4. **One to Many / Has Many**

### 4.1 *定義一對多關聯*

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    /**
     * Get the comments for the blog post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

---

- 預設外鍵為 `post_id`，可自訂：

```php
return $this->hasMany(Comment::class, 'foreign_key');
return $this->hasMany(Comment::class, 'foreign_key', 'local_key');
```

---

- 取得**所有留言**：

```php
use App\Models\Post;

$comments = Post::find(1)->comments;

foreach ($comments as $comment) {
    // ...
}
```

---

- 可加**條件查詢**：

```php
$comment = Post::find(1)->comments()
        ->where('title', 'foo')
        ->first();
```

---

### 4.2 *自動 Hydrate Parent Models on Children*（`chaperone`）

- `Hydrate`（水合/注入）在 Laravel 中是指「**將`資料庫查詢結果`轉換為 Eloquent Model 物件**」的過程。

- Eloquent **預設** `lazy loading` 不會自動 `hydrate parent`。
  - *不會自動 hydrate parent*：`當你查詢子 Model 時，Laravel 不會自動建立並填充父 Model 物件`，即使你稍後會用到它們。

<!-- 其實反方向也成立：

     查詢父 Model（例如 Post::all()），預設也不會自動 hydrate 子 Model（comments），
     除非你用 with('comments') 進行 eager loading。

     預設只會 hydrate 你查詢的那個 Model 實例。
     關聯的 parent 或 child，都要用 eager loading（with()）或 chaperone 才會自動建立。 -->

  - *預設行為*（不會自動 hydrate parent）
  ```php
  // 查詢所有文章
  $posts = Post::all();

  // 此時 Laravel 只查詢 posts 表：
  // SQL: SELECT * FROM posts

  // 父 Model (User) 還沒有被 hydrate
  // $posts 中的每個 $post 都沒有預先準備好 user 物件

  foreach ($posts as $post) {
      // 第一次存取 user 時才會查詢和 hydrate
      echo $post->user->name;  
      // SQL: SELECT * FROM users WHERE id = ? (每個 post 都會執行一次)
  }

  // 問題：如果有 100 篇文章，就會執行 100 次用戶查詢（N+1 問題）

  ```

---

  - *Chaperone 行為*（會自動 hydrate parent/自動 Eager Loading）

  ```php
  // 查詢所有文章（假設啟用了 chaperone）
  $posts = Post::all();

  // Laravel 會自動：
  // 1. 查詢 posts：SELECT * FROM posts
  // 2. 收集所有 user_id：[1, 2, 3, 4, 5...]
  // 3. 一次查詢所有父 Model：SELECT * FROM users WHERE id IN (1,2,3,4,5...)
  // 4. 自動 hydrate 這些 User 物件
  // 5. 自動建立 post → user 的關聯

  foreach ($posts as $post) {
      // 不會再執行資料庫查詢，直接使用已經 hydrate 的 User 物件
      echo $post->user->name;  // 沒有額外 SQL
  }

---

- 若要**自動帶入 parent**，`hasMany` 可加 `chaperone`。

- `chaperone` 意思：陪護者 / 監護人 / 陪同者 / 伴護 / 看護

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Chaperone 就像一個貼心的陪護者

class Post extends Model
{
    public function comments(): HasMany
    {   
        // Chaperone 會「陪護」這個關聯
        // 確保當你需要 parent model 時，它已經準備好了
        return $this->hasMany(Comment::class)->chaperone();
    }
}
```
<!-- chaperone() 通常是用在子 Model 的關聯方法，
     也就是在「兒子」的 model（例如 Comment）裡，
     針對 belongsTo(Post::class) 這種 parent 關聯加上 ->chaperone()，
     這樣查詢子 Model 時，parent 會自動 hydrate。

     你看到的範例是在父 Model（Post）的 hasMany 關聯加 chaperone()，
     這樣做是讓「查詢父 Model 時，子 Model 也能自動 hydrate parent」，
     但最常見的用法還是在子 Model 的 belongsTo 關聯加 chaperone()。 -->

<!-- chaperone() 可以用在任何關聯方法（不論是父或子），
     目的是讓關聯的另一端自動 hydrate，避免 N+1 問題。
     不只限於子 Model 的 belongsTo，父 Model 的 hasMany 也能加。 -->

---

- 也可在 `eager loading` 時加 `chaperone`：

```php
use App\Models\Post;

$posts = Post::with([
    'comments' => fn ($comments) => $comments->chaperone(),
])->get();
```

<!-- 在查詢父 Model 並 with 子 Model時，
     如果程式裡又會用到「子 Model 關聯的父 Model」（例如 $comment->post），
     這時加上 chaperone() 可以讓子 Model 的父關聯也一起預載入，
     避免 N+1 查詢問題。 -->

```php
use App\Models\Post;

// 一次查詢所有 Post 及其 comments，並讓每個 comment 也自動有 parent post
$posts = Post::with([
    'comments' => fn ($query) => $query->chaperone(),
])->get();

// 迴圈存取時，不會有 N+1 查詢
foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        // 這裡 $comment->post 已經預載入，不會再查詢資料庫
        echo $comment->post->title;
    }
}
```

---

- **為什麼 with() 沒有完全解決 N+1？**

- *關鍵理解：方向性問題*

```php
$posts = Post::with('comments')->get();
// 這個 with() 解決的是：Post → Comments 的 N+1
// 但沒有解決：Comment → Post 的 N+1`
```

---

- *`with('comments')` 實際做了什麼*

```php
$posts = Post::with('comments')->get();

// Laravel 執行的 SQL：
// 1. SELECT * FROM posts
// 2. SELECT * FROM comments WHERE post_id IN (1,2,3,4,5...)

// 結果：每個 Post 都有完整的 comments 集合
```

---

- *問題出現在哪裡*

```php
foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        // 這裡就是問題所在！
        echo $comment->post->title;
        // 每個 comment 物件並沒有預先載入它的 parent post
    }
}
```

---

- *當 Laravel 查詢 comments 時*

```php
$comments = Comment::whereIn('post_id', [1,2,3,4,5])->get();

// 每個 Comment 物件是這樣的：
Comment {
    id: 1,
    post_id: 1,
    content: "...",
    post: null  // 沒有被預載入！
}

// 所以當你存取 $comment->post 時：
echo $comment->post->title; // 觸發新的查詢！
// SQL: SELECT * FROM posts WHERE id = 1
```

---

- *錯誤認知*（以為解決了）

```php
$posts = Post::with('comments')->get();
// 以為只會有 2 個查詢：posts + comments
```

---

- *實際情況*（還是有 N+1）

```php
$posts = Post::with('comments')->get();

foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        echo $comment->post->title; // 每個 comment 都查詢一次 post！
    }
}

// 實際查詢數：
// 1. SELECT * FROM posts
// 2. SELECT * FROM comments WHERE post_id IN (...)
// 3. SELECT * FROM posts WHERE id = 1  (第一個 comment)
// 4. SELECT * FROM posts WHERE id = 1  (第二個 comment)
// 5. SELECT * FROM posts WHERE id = 2  (第三個 comment)
// ... 還是 N+1 問題！
```

---

- *方案 1：雙向 Eager Loading*

```php
$posts = Post::with(['comments.post'])->get();
// 現在 comments 也預載入了它們的 post

foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        echo $comment->post->title; // 不會再查詢！
    }
}
```

---

- *方案 2：使用 Chaperone*

```php
class Comment extends Model
{
    public function post()
    {
        return $this->belongsTo(Post::class)->chaperone();
    }
}

// 現在即使不手動 eager loading，也不會有 N+1
$posts = Post::with('comments')->get();
foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        echo $comment->post->title; // Chaperone 自動處理！
    }
}
```
---

### 4.3 *One to Many (Inverse) / Belongs To*

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    /**
     * Get the post that owns the comment.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
```

---

- 可自訂外鍵與 `owner key`：

```php
public function post(): BelongsTo
{
    return $this->belongsTo(Post::class, 'foreign_key');
}

public function post(): BelongsTo
{
    return $this->belongsTo(Post::class, 'foreign_key', 'owner_key');
}
```

---

### 4.4 *Default Models*（`withDefault`）

- 可用 `withDefault()` 指定關聯為 `null` 時的預設 model：

```php

// === Basic withDefault() ===
public function user(): BelongsTo
{
    // withDefault() 提供預設模型，當關聯不存在時使用
    // 避免取得 null，而是回傳一個空的 User 模型實例
    // 所有屬性都是預設值（通常是 null 或空字串）
    return $this->belongsTo(User::class)->withDefault();
}

// 使用範例：
// $post = Post::find(1);
// echo $post->user->name; // 即使沒有關聯用戶，也不會出錯
//                         // 會顯示空字串而不是造成 "trying to get property of null" 錯誤

---

// === 帶預設值的 withDefault() ===
public function user(): BelongsTo
{
    // withDefault() 接受陣列參數，設定預設模型的屬性值
    // 當關聯不存在時，會建立一個具有指定預設值的 User 模型
    return $this->belongsTo(User::class)->withDefault([
        'name' => 'Guest Author',  // 設定預設名稱
        'email' => 'guest@example.com',  // 可以設定多個預設值
    ]);
}

// 使用範例：
// $post = Post::find(1); // 假設這篇文章沒有關聯的用戶
// echo $post->user->name;  // 輸出: "Guest Author"
// echo $post->user->email; // 輸出: "guest@example.com"

---

// === 動態預設值的 withDefault() ===
public function user(): BelongsTo
{
    // withDefault() 接受 Closure，可以動態設定預設模型
    // 第一個參數是預設的 User 模型實例
    // 第二個參數是當前的 Post 模型實例（父模型）
    return $this->belongsTo(User::class)->withDefault(function (User $user, Post $post) {
        // 可以根據當前 Post 的資料動態設定 User 的預設值
        $user->name = 'Guest Author';
        $user->email = 'guest@example.com';
        
        // 也可以根據 Post 的屬性來設定不同的預設值
        if ($post->category === 'technical') {
            $user->name = 'Technical Writer';
        }
        
        // 可以設定任何屬性，包括計算出來的值
        $user->created_at = now();
    });
}

// 使用範例：
// $post = Post::find(1);
// echo $post->user->name; // 根據 post 的 category 顯示不同的預設作者名稱

---

// === 完整的實際應用範例 ===
class Post extends Model
{
    public function author(): BelongsTo
    {
        // 為文章提供預設作者，避免顯示錯誤
        return $this->belongsTo(User::class, 'author_id')->withDefault([
            'name' => '匿名作者',
            'avatar' => '/images/default-avatar.png',
            'bio' => '這位作者選擇保持匿名',
        ]);
    }
    
    public function reviewer(): BelongsTo
    {
        // 為文章審核者提供動態預設值
        return $this->belongsTo(User::class, 'reviewer_id')->withDefault(function (User $reviewer, Post $post) {
            $reviewer->name = '待指派審核者';
            $reviewer->email = 'pending@review.com';
            
            // 根據文章狀態設定不同的預設審核者
            if ($post->status === 'draft') {
                $reviewer->name = '草稿無需審核';
            }
        });
    }
}

---

// === 使用時的好處 ===
// 不使用 withDefault() 的問題：
$post = Post::find(1);
if ($post->user) {  // 需要檢查是否為 null
    echo $post->user->name;
} else {
    echo 'Unknown Author';  // 手動處理預設值
}

// 使用 withDefault() 的優勢：
$post = Post::find(1);
echo $post->user->name;  // 永遠不會是 null，直接使用即可
                         // 大幅簡化程式碼，避免重複的 null 檢查

---

// === 在 Blade 樣板中的應用 ===
// 不需要額外的 null 檢查
@foreach($posts as $post)
    <div class="post">
        <h3>{{ $post->title }}</h3>
        <p>作者：{{ $post->user->name }}</p>  {{-- 永遠安全 --}}
        <img src="{{ $post->user->avatar }}" alt="Avatar">
    </div>
@endforeach

---

// === 注意事項 ===
// 1. withDefault() 只影響當關聯不存在（為 null）的情況
// 2. 如果關聯存在，withDefault() 不會被使用
// 3. 預設模型不會被保存到資料庫，只存在於記憶體中
// 4. 可以結合其他查詢方法使用，如 with()、where() 等
```

---

### 4.5 *Querying Belongs To Relationships*（`whereBelongsTo`）

- 可用 `whereBelongsTo` 快速查詢：

```php
use App\Models\Post;

/**
 * 查詢屬於特定 User 的所有 Post
 * 自動推斷關聯名稱和外鍵
 */
$posts = Post::whereBelongsTo($user)->get();
// 等同於：Post::where('user_id', $user->id)->get()
// Laravel 自動從 belongsTo 關聯推斷出 user_id 欄位

/**
 * 查詢屬於多個 User 的 Post（批次查詢）
 */
$users = User::where('vip', true)->get(); // 取得所有 VIP 用戶
$posts = Post::whereBelongsTo($users)->get();
// 等同於：Post::whereIn('user_id', $users->pluck('id'))->get()
// 查詢所有 VIP 用戶發表的文章

/**
 * 指定特定的關聯名稱
 * 當 Model 有多個 belongsTo 關聯到同一個 Model 時使用
 */
$posts = Post::whereBelongsTo($user, 'author')->get();
// 使用 'author' 關聯而不是預設的 'user' 關聯
// 等同於：Post::where('author_id', $user->id)->get()

// 實際使用情境：
class Post extends Model 
{
    // 文章作者
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // 文章編輯者
    public function author(): BelongsTo  
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    
    // 文章審核者
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}

// 查詢特定用戶作為不同角色的文章
$userPosts = Post::whereBelongsTo($user)->get();           // 用戶發表的文章
$authoredPosts = Post::whereBelongsTo($user, 'author')->get();   // 用戶編輯的文章  
$reviewedPosts = Post::whereBelongsTo($user, 'reviewer')->get(); // 用戶審核的文章
```

---

## 5. **Has One of Many**

- 取得 *一對多* 關聯中的最新、最舊、最大等`單一資料`。

```php
class User extends Model
{
    /**
     * 一對多：取得用戶的所有訂單
     * 回傳：Collection<Order>
     */
    // 可以只定義 HasMany，不定義 HasOne
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * 一對一（特殊）：從多個訂單中取得最新的一筆
     * 回傳：Order|null
     */
    // 也可以只定義 HasOne，不定義 HasMany
    public function latestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->latestOfMany();
        // 等同於：->orderBy('created_at', 'desc')->limit(1)
    }
    
    /**
     * 一對一（特殊）：從多個訂單中取得最舊的一筆
     * 回傳：Order|null
     */
    public function oldestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->oldestOfMany();
        // 等同於：->orderBy('created_at', 'asc')->limit(1)
    }
    
    /**
     * 一對一（特殊）：從多個訂單中取得金額最大的一筆
     * 回傳：Order|null
     */
    public function largestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->ofMany('price', 'max');
        // 等同於：->orderBy('price', 'desc')->limit(1)
    }
}

// 使用範例
$user = User::find(1);

$allOrders = $user->orders;        // Collection：所有訂單
$newest = $user->latestOrder;      // Order|null：最新一筆
$oldest = $user->oldestOrder;      // Order|null：最舊一筆  
$expensive = $user->largestOrder;  // Order|null：最貴一筆
```

---

- 也可用 `one()` __將 hasMany 轉 hasOne__：

```php
public function orders(): HasMany
{
    return $this->hasMany(Order::class);
}

public function largestOrder(): HasOne
{
    return $this->orders()->one()->ofMany('price', 'max');
}
```

---

- `HasManyThrough` 也可用 `one()` 轉 `HasOneThrough`：
- **取多筆 -> 取一筆**

```php
// 假設有這樣的關聯結構：
// User -> Projects -> Deployments

class User extends Model
{
    /**
     * 用戶的所有專案 (一對多)
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
    
    /**
     * 通過專案取得所有部署記錄 (一對多穿透)
     */
    public function deployments(): HasManyThrough
    {
        return $this->hasManyThrough(Deployment::class, Project::class);
    }
    
    /**
     * 從所有部署記錄中取得最新的一筆 (一對一穿透)
     */
    public function latestDeployment(): HasOneThrough
    {
        return $this->deployments()->one()->latestOfMany();
        //           ^^^^^^^^^^^^^ ^^^^^ ^^^^^^^^^^^^^
        //           基礎關聯      轉換   篩選條件
    }

    // 基礎的 HasManyThrough 關聯
    $this->deployments() 
    // 等同於：$this->hasManyThrough(Deployment::class, Project::class)
    // SQL 概念：取得用戶所有專案的所有部署記錄

    // 將 HasManyThrough 轉換為 HasOneThrough
    $this->deployments()->one()
    // 從「一對多穿透」變成「一對一穿透」
    // 但還沒有限制條件，需要進一步篩選

    // 在穿透關聯中找到最新的一筆
    $this->deployments()->one()->latestOfMany()
    // 加上 ORDER BY created_at DESC LIMIT 1
}
```

---

- **一對多穿透** (HasManyThrough)
```sql
User (John)
├── Project A
│   ├── Deployment 1 ✅
│   ├── Deployment 2 ✅  
│   └── Deployment 3 ✅
└── Project B
    ├── Deployment 4 ✅
    └── Deployment 5 ✅

結果：取得所有 5 筆 Deployments
```

---

- **一對一穿透** (HasOneThrough)
```sql
User (John)
├── Project A
│   ├── Deployment 1
│   ├── Deployment 2  
│   └── Deployment 3
└── Project B
    ├── Deployment 4
    └── Deployment 5 ✅ (最新的一筆)

結果：只取得 1 筆 Deployment (最新的)
```

```sql
-- users 表
id | name
1  | John

-- projects 表  
id | user_id | name
1  | 1       | Website
2  | 1       | API

-- deployments 表
id | project_id | version | created_at
1  | 1          | v1.0    | 2024-01-01
2  | 1          | v1.1    | 2024-01-15  
3  | 2          | v2.0    | 2024-01-20  ← 最新
4  | 2          | v2.1    | 2024-01-10
```

---

### 5.1 *Advanced Has One of Many Relationships*

- 可用 `ofMany` 傳入**多欄位**排序與條件：

```php
/**
 * 取得商品的「當前有效價格」
 * 從多個價格記錄中，找出「已發布且最新」的那一筆
 */
public function currentPricing(): HasOne
{
    return $this->hasOne(Price::class)->ofMany([
        // 第一個參數：排序條件陣列（優先順序由上到下）
        'published_at' => 'max',  // 優先條件：取發布時間最晚的
        'id' => 'max',            // 次要條件：如果發布時間相同，取 ID 最大的
    ], function (Builder $query) {
        // 第二個參數：額外的查詢條件（篩選器）
        $query->where('published_at', '<', now());
        // 只考慮「已經發布」的價格（發布時間早於現在）
        // 排除未來才會生效的價格
    });
}
```
```php
// 1. 基礎關聯
$this->hasOne(Price::class)
// 建立與 Price 模型的一對一關聯

// 2. 篩選條件
function (Builder $query) {
    $query->where('published_at', '<', now());
}
// 只考慮 published_at < 現在時間的價格記錄
// 過濾掉「未來價格」

// 3. 排序選擇
->ofMany([
    'published_at' => 'max',  // 第一排序：最晚發布的
    'id' => 'max',            // 第二排序：ID 最大的
])
// 從符合條件的價格中，選出最適合的一筆
```

```sql
-- prices 表
id | product_id | price | published_at
1  | 1          | 100   | 2024-01-01 10:00:00
2  | 1          | 120   | 2024-01-15 14:00:00  
3  | 1          | 110   | 2024-01-15 14:00:00  ← 同一時間，但 id 較大
4  | 1          | 150   | 2024-12-31 23:59:59  ← 未來價格，會被過濾掉
-- 結果：記錄 3（published_at 最大，且在相同時間中 id 最大）
-- hasOne()->ofMany() 只會回傳一筆資料
```

---

## 6. **Has One Through**

- `hasOneThrough`：*A 經由 B 取得 C* 的一對一關聯。

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Mechanic extends Model
{
    /**
     * Get the car's owner.
     */
    public function carOwner(): HasOneThrough
    {
        return $this->hasOneThrough(Owner::class, Car::class);
    }
}
```

---

- 也可用 `through` 語法：

```php
/**
 * Laravel HasOneThrough 關聯的語法糖
 * 前提：需要先定義好 cars() 和 owner() 基礎關聯
 */

/**
 * 字串語法：使用關聯名稱字串
 * 透過 'cars' 關聯穿透到 'owner' 關聯
 */
// String based syntax...
return $this->through('cars')->has('owner');
//            ^^^^^^^^      ^^^^^^^^^^^^
//            |             |
//            |             └─ 目標關聯：Car model 的 owner() 關聯
//            └─ 中間關聯：當前 model 的 cars() 關聯
//
// 等同於傳統語法：
// return $this->hasOneThrough(Owner::class, Car::class);

/**
 * 動態語法：使用駝峰命名的魔術方法
 * Laravel 自動將駝峰命名轉換為對應的關聯名稱
 */
// Dynamic syntax...
return $this->throughCars()->hasOwner();
//            ^^^^^^^^^^^^   ^^^^^^^^^^
//            |              |
//            |              └─ hasOwner() = has('owner')
//            └─ throughCars() = through('cars')
//
// 完全等同於上面的字串語法

/**
 * 運作原理：
 * 1. through('cars') 找到當前 model 的 cars() 關聯定義
 * 2. 從 cars() 關聯推斷出中間 Model (Car::class)
 * 3. has('owner') 找到 Car model 的 owner() 關聯定義  
 * 4. 從 owner() 關聯推斷出目標 Model (Owner::class)
 * 5. 自動建立 HasOneThrough 關聯
 */

/**
 * 完整的關聯定義範例：
 */
class Mechanic extends Model
{
    /**
     * 機械師的車輛 (一對多基礎關聯)
     */
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }
    
    /**
     * 機械師的車主 (一對一穿透關聯 - 字串語法)
     * 透過車輛關聯找到車主
     */
    public function owner(): HasOneThrough
    {
        return $this->through('cars')->has('owner');
        // 路徑：Mechanic → Car (through cars) → Owner (has owner)
    }
    
    /**
     * 機械師的車主 (一對一穿透關聯 - 動態語法)
     * 與上面功能完全相同，只是語法不同
     */
    public function ownerAlt(): HasOneThrough
    {
        return $this->throughCars()->hasOwner();
        // throughCars() 等同於 through('cars')
        // hasOwner() 等同於 has('owner')
    }
}

class Car extends Model
{
    /**
     * 車輛的車主 (多對一基礎關聯)
     * 這個關聯會被 has('owner') 引用
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }
}

/**
 * 使用範例：
 */
$mechanic = Mechanic::find(1);
$owner = $mechanic->owner;  // 取得機械師負責的車輛的車主（單一車主）

/**
 * 語法糖的優勢：
 * 1. 更直觀：直接使用關聯名稱而非 Model 類別名稱
 * 2. 自動推斷：Laravel 自動從現有關聯推斷 Model 類別
 * 3. 重構友善：修改 Model 類別名稱時不需要更新穿透關聯
 * 4. 可讀性高：程式碼更接近自然語言描述
 */
```
- `through('cars')`：指定 __中間關聯__ 的名稱
- `has('owner')`：指定 __目標關聯__ 的名稱
- `throughCars()`：through('cars') 的駝峰語法糖
- `hasOwner()`：has('owner') 的駝峰語法糖

---

- **內建has、through駝峰語法糖**

```php
// Laravel Model 內部的簡化版實現：
class Model
{
    public function __call($method, $parameters)
    {
        // 檢查是否為 through* 開頭的方法
        if (str_starts_with($method, 'through')) {
            // throughCars → cars
            $relation = Str::snake(substr($method, 7));
            return $this->through($relation);
        }
        
        // 檢查是否為 has* 開頭的方法  
        if (str_starts_with($method, 'has')) {
            // hasOwner → owner
            $relation = Str::snake(substr($method, 3));
            return $this->has($relation);
        }
        
        // 其他魔術方法處理...
    }
}
```

```php
// Laravel Eloquent Model 內建的 __call() 方法
// 會自動處理這些模式：

// 遇到 'through' 開頭：
$this->throughCars()        // 自動轉為 through('cars')
$this->throughProjects()    // 自動轉為 through('projects')  
$this->throughUserData()    // 自動轉為 through('user_data')

// 遇到 'has' 開頭：
$this->hasOwner()          // 自動轉為 has('owner')
$this->hasManager()        // 自動轉為 has('manager')
$this->hasUserProfile()    // 自動轉為 has('user_profile')
```
---

### 6.1 *Key Conventions*

- 可自訂**中介表、最終表**的`外鍵`與 `local key`：

```php
public function carOwner(): HasOneThrough
{
    return $this->hasOneThrough(
        Owner::class,
        Car::class,
        'mechanic_id', // Foreign key on the cars table...
        'car_id', // Foreign key on the owners table...
        'id', // Local key on the mechanics table...
        'id' // Local key on the cars table...
}
```

---

- 也可用 `through` 語法重用已定義關聯：

```php
// String based syntax...
return $this->through('cars')->has('owner');
// Dynamic syntax...
return $this->throughCars()->hasOwner();
```

---

## 7. **Has Many Through**

- `hasManyThrough`：*A 經由 B 取得多個 C* 的一對多關聯。

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Application extends Model
{
    /**
     * Get all of the deployments for the application.
     */
    public function deployments(): HasManyThrough
    {
        return $this->hasManyThrough(Deployment::class, Environment::class);
    }
}
```

---

- 也可用 `through` 語法：

```php
// String based syntax...
return $this->through('environments')->has('deployments');
// Dynamic syntax...
return $this->throughEnvironments()->hasDeployments();
```

---

### 7.1 **Key Conventions**

- 可自訂*中介表、最終表*的`外鍵`與`local key`：

```php
public function deployments(): HasManyThrough
{
    return $this->hasManyThrough(
        Deployment::class,
        Environment::class,
        'application_id', // Foreign key on the environments table...
        'environment_id', // Foreign key on the deployments table...
        'id', // Local key on the applications table...
        'id' // Local key on the environments table...
    );
}
```

---

- 也可用 `through` 語法重用已定義關聯：

```php
// String based syntax...
return $this->through('environments')->has('deployments');
// Dynamic syntax...
return $this->throughEnvironments()->hasDeployments();
``` 

## 8. **Polymorphic Relationship**

*多型關聯*（Polymorphic Relationships）允許`子模型`（如 Comment、Image）可`同時屬於多種不同型態的父模型`（如 Post、User、Video），僅需一組`關聯欄位`即可實現彈性關聯。

---

### 8.1 *一對一／多型*（One to One Polymorphic）

#### 8.1.1 **資料表結構**

- `images` 表 *同時可關聯* `posts` 或 `users`，透過 `imageable_id` 與 `imageable_type` 欄位記錄*父模型*型態與主鍵。

```php
posts
    id - integer
    name - string
users
    id - integer
    name - string
images
    id - integer
    url - string
    imageable_id - integer
    imageable_type - string
```

---

- `imageable_type` 會存放父模型的 *完整類別名稱*（如 `App\Models\Post` 或 `App\Models\User`）。

---

#### 8.1.2 **模型定義**

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    /**
     * 取得多型父模型（User 或 Post）。
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
        // Laravel 自動推斷：
        // - 關聯名稱：'imageable' (方法名)
        // - Type 欄位：'imageable_type'
        // - ID 欄位：'imageable_id'
}
    }
}

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Post extends Model
{
    /**
     * 取得貼文的圖片。
     */
    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class User extends Model
{
    /**
     * 取得使用者的圖片。
     */
    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
```

---

#### 8.1.3 **關聯存取**

- 取得*貼文的圖片*：

```php
use App\Models\Post;

$post = Post::find(1);
$image = $post->image;
```

---

- 取得*圖片的父模型*（可能是 Post 或 User）：

```php
use App\Models\Image;

$image = Image::find(1);
$imageable = $image->imageable;
```

---

#### 8.1.4 **自訂 Key 規則**

- 可自訂*多型關聯*的 `id/type` 欄位名稱，*第一個參數*建議用 `__FUNCTION__`：

```php
/**
 * 取得圖片所屬的模型。
 */
public function imageable(): MorphTo
{
    return $this->morphTo(__FUNCTION__, 'imageable_type', 'imageable_id');
    //                   ^^^^^^^^^^^^
    //                   自動取得方法名稱 'imageable'
}

// 等同於：
public function imageable(): MorphTo
{
    return $this->morphTo('imageable', 'imageable_type', 'imageable_id');
    //                   ^^^^^^^^^^^
    //                   硬編碼字串
}
```
```php
// ✅ 使用 __FUNCTION__ 的好處：
public function imageable(): MorphTo
{
    return $this->morphTo(__FUNCTION__, 'imageable_type', 'imageable_id');
}

// 如果重構時改變方法名稱：
public function imageableModel(): MorphTo  // 改名了
{
    return $this->morphTo(__FUNCTION__, 'imageable_type', 'imageable_id');
    //                   ^^^^^^^^^^^^
    //                   自動變成 'imageableModel'，保持一致
}

// ❌ 硬編碼的問題：
public function imageableModel(): MorphTo  // 改名了
{
    return $this->morphTo('imageable', 'imageable_type', 'imageable_id');
    //                   ^^^^^^^^^^^
    //                   還是舊名稱，可能導致錯誤
}
```

---

### 8.2 *一對多／多型*（One to Many Polymorphic）

#### 8.2.1 **資料表結構**

- `comments` 表可 *同時關聯* `posts` 或 `videos`，透過 `commentable_id` 與 `commentable_type`。

```php
posts
    id - integer
    title - string
    body - text
videos
    id - integer
    title - string
    url - string
comments
    id - integer
    body - text
    commentable_id - integer
    commentable_type - string
```

---

#### 8.2.2 **模型定義**

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    /**
     * 取得多型父模型（Post 或 Video）。
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    /**
     * 取得貼文的所有留言。
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Video extends Model
{
    /**
     * 取得影片的所有留言。
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

---

#### 8.2.3 **關聯存取**

- 取得*貼文的所有留言*：

```php
use App\Models\Post;

$post = Post::find(1);
foreach ($post->comments as $comment) {
    // ...
}
```

---

- 取得*留言的父模型*（Post 或 Video）：

```php
use App\Models\Comment;

$comment = Comment::find(1);
$commentable = $comment->commentable;
```

---

#### 8.2.4 **自動載入父模型**（`Chaperone`）

- *預先載入*（`eager loading`）時，*若在迴圈中存取子模型的父模型*，會產生 N+1 問題：

```php
$posts = Post::with('comments')->get();
foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        echo $comment->commentable->title;
    }
}
```

---

- *`with('comments')` 只解決了一個方向*

```php
$posts = Post::with('comments')->get();
// 這個 eager loading 解決的是：Post → Comments 的 N+1
// 但沒有解決：Comment → Commentable 的 N+1
```

---

- *`with('comments')` 實際做了什麼*

```php
$posts = Post::with('comments')->get(); // 只 eager loading 了第

// Laravel 執行的 SQL：
// 1. SELECT * FROM posts
// 2. SELECT * FROM comments WHERE post_id IN (1,2,3,4,5...)

// 結果：每個 Post 都有完整的 comments 集合
// 但每個 Comment 物件沒有預載入它的 commentable 關聯
```

---

- *問題出現在哪裡*

```php
$posts = Post::with('comments')->get();  // 只 eager loading 了第一層
foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        // 這裡就是問題所在！
        echo $comment->commentable->title;  // 第二層沒有 eager loading！
        // 每個 comment 需要查詢它的 commentable（多型關聯）
        // 但 commentable 沒有被預載入！
    }
}

```

---

- *實際的查詢過程*

```php
// 假設有 3 篇文章，每篇有 2 個評論

// Step 1: 載入文章
// SQL: SELECT * FROM posts  (1 個查詢)

// Step 2: 載入評論（with('comments') 處理）  
// SQL: SELECT * FROM comments WHERE post_id IN (1,2,3)  (1 個查詢)

// Step 3: 存取 commentable（問題開始）
foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        echo $comment->commentable->title;
        // 每個 comment 都會觸發查詢！
        // SQL: SELECT * FROM users WHERE id = ? (第1個評論的 commentable)
        // SQL: SELECT * FROM posts WHERE id = ? (第2個評論的 commentable) 
        // SQL: SELECT * FROM users WHERE id = ? (第3個評論的 commentable)
        // ... 總共 6 個額外查詢！
    }
}

// 總查詢數：1 + 1 + 6 = 8 個查詢（還是 N+1 問題！）
```

---

- *多型關聯特別容易有 N+1*

```sql
-- comments 表結構（多型關聯範例）
-- 評論可以屬於不同類型的模型：用戶、文章、產品等

id | content | commentable_type | commentable_id
-- |---------|------------------|---------------
1  | "Good"  | App\Models\User  | 1             -- 評論給 User ID 1
2  | "Nice"  | App\Models\Post  | 5             -- 評論給 Post ID 5  
3  | "Cool"  | App\Models\User  | 2             -- 評論給 User ID 2
4  | "Wow"   | App\Models\Post  | 3             -- 評論給 Post ID 3

-- 欄位說明：
-- id: 評論的主鍵
-- content: 評論內容
-- commentable_type: 被評論物件的 Model 類別名稱（多型關聯的類型欄位）
-- commentable_id: 被評論物件的 ID（多型關聯的 ID 欄位）

-- 多型關聯的特點：
-- 1. commentable_type 存放完整的 Model 類別名稱
-- 2. commentable_id 存放對應 Model 的主鍵 ID
-- 3. 同一個 comments 表可以關聯到多種不同的 Model

-- 對應的關聯關係：
-- Comment ID 1 → User ID 1 (users 表)
-- Comment ID 2 → Post ID 5 (posts 表)
-- Comment ID 3 → User ID 2 (users 表)  
-- Comment ID 4 → Post ID 3 (posts 表)

-- 查詢複雜性：
-- 當需要載入所有評論的 commentable 時：
-- 1. 先查詢 comments 表
-- 2. 分析 commentable_type，發現有 User 和 Post 兩種類型
-- 3. 分組 commentable_id：
--    User: [1, 2]
--    Post: [5, 3]
-- 4. 分別查詢不同的表：
--    SELECT * FROM users WHERE id IN (1, 2)
--    SELECT * FROM posts WHERE id IN (5, 3)
-- 5. 重新組合結果對應回各個評論

-- 如果沒有 eager loading，會產生 N+1 問題：
-- SELECT * FROM comments                    (1 個查詢)
-- SELECT * FROM users WHERE id = 1         (comment 1)
-- SELECT * FROM posts WHERE id = 5         (comment 2)
-- SELECT * FROM users WHERE id = 2         (comment 3)  
-- SELECT * FROM posts WHERE id = 3         (comment 4)
-- 總共 5 個查詢！

-- 正確的 eager loading 只需要 3 個查詢：
-- SELECT * FROM comments                    (1 個查詢)
-- SELECT * FROM users WHERE id IN (1, 2)   (1 個查詢)
-- SELECT * FROM posts WHERE id IN (5, 3)   (1 個查詢)
```

---

- *方案 1：嵌套 Eager Loading*

```php
// ✅ 正確：eager loading 兩個層級
$posts = Post::with(['comments.commentable'])->get();
//                   ^^^^^^^^^^ ^^^^^^^^^^^^^
//                   第一層      第二層
//                   Post→Comment  Comment→Commentable

foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        echo $comment->commentable->title; // 現在不會有 N+1 問題！
    }
}
```

---

- *方案 2：使用 Chaperone*

```php
class Comment extends Model
{
    public function commentable(): MorphTo
    {
        return $this->morphTo()->chaperone();
        // Chaperone 會自動處理父模型的載入
    }
}

// 現在即使不手動 eager loading，也不會有 N+1
$posts = Post::with('comments')->get();
foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        echo $comment->commentable->title; // Chaperone 自動處理！
    }
}
```

---

- 可於關聯定義時加上 `chaperone()`，自動**將父模型 hydrate 至子模型**：

```php
class Post extends Model
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->chaperone();
    }
}
```

---

- 或於 `eager loading` 時動態加上：

```php
use App\Models\Post;

$posts = Post::with([
    'comments' => fn ($comments) => $comments->chaperone(),
])->get();
```

---

### 8.3 *一對多／多型的 One of Many*（One of Many Polymorphic）

- 可快速取得`多型關聯`中「最新」或「最舊」的**單一關聯模型**。

```php

/**
* 基礎：一對多多型關聯（取得所有圖片）
*/
// 只定義 MorphMany，不定義 MorphOne -> 完全可以獨立存在！
public function images(): MorphMany
{
    return $this->morphMany(Image::class, 'imageable');
    // 回傳：Collection<Image>（多張圖片）
}

// 完整的多型關聯定義（所有參數）
public function latestImage(): MorphOne
{
    return $this->morphOne(
        Image::class, 
        'imageable',           // 關聯名稱前綴
        'imageable_type',      // type 欄位名稱
        'imageable_id'         // id 欄位名稱
    )->latestOfMany();
}

/**
 * 特化：一對一多型關聯（從多張圖片中取一張最新的）
 */
// 只定義 MorphOne，不定義 MorphMany -> 完全可以獨立存在！
    }
public function latestImage(): MorphOne
{
    return $this->morphOne(Image::class, 'imageable')->latestOfMany();
    //                                   ^^^^^^^^^^^
    //                                   Laravel 會自動推斷：
    //                                   - imageable_type
    //                                   - imageable_id
}

/**
 * 取得使用者最舊的圖片。
 */
public function oldestImage(): MorphOne
{
    return $this->morphOne(Image::class, 'imageable')->oldestOfMany();
}
```

---

- 也可自訂**排序欄位**與**聚合函式**：

```php
/**
 * 取得使用者最受歡迎的圖片
 * 從用戶的所有圖片中，選出按讚數最多的那一張
 * 
 * @return MorphOne 一對一多型關聯，回傳單一 Image 物件或 null
 */
public function bestImage(): MorphOne
{
    return $this->morphOne(Image::class, 'imageable')->ofMany('likes', 'max');
    //     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^
    //     建立多型關聯                                選擇條件
    //     ↓                                        ↓
    //     User morphOne Image                      找出 likes 欄位最大值的那一筆
    //     透過 imageable_type + imageable_id
    //
    // 參數解析：
    // - Image::class: 目標 Model（圖片模型）
    // - 'imageable': 多型關聯的前綴名稱
    //   * 自動推斷 imageable_type 和 imageable_id 欄位
    // - ofMany('likes', 'max'): 篩選條件
    //   * 'likes': 比較的欄位名稱（圖片的按讚數）
    //   * 'max': 取最大值（最多按讚的）
}

/**
 * 對應的資料庫結構和查詢邏輯：
 */

/*
-- images 表結構
CREATE TABLE images (
    id INT PRIMARY KEY,
    url VARCHAR(255),
    likes INT DEFAULT 0,           -- 按讚數欄位
    imageable_type VARCHAR(255),   -- 多型關聯：所屬模型類型
    imageable_id INT               -- 多型關聯：所屬模型 ID
);

-- 範例資料
INSERT INTO images VALUES 
(1, 'photo1.jpg', 10, 'App\\Models\\User', 1),  -- User 1 的圖片，10 個讚
(2, 'photo2.jpg', 25, 'App\\Models\\User', 1),  -- User 1 的圖片，25 個讚 ← 最多
(3, 'photo3.jpg', 15, 'App\\Models\\User', 1),  -- User 1 的圖片，15 個讚
(4, 'photo4.jpg', 30, 'App\\Models\\Post', 5);  -- Post 5 的圖片，30 個讚

-- 當呼叫 $user1->bestImage 時，Laravel 會執行類似這樣的查詢：
SELECT * FROM images 
WHERE imageable_type = 'App\\Models\\User' 
  AND imageable_id = 1 
ORDER BY likes DESC 
LIMIT 1;

-- 結果：會回傳 photo2.jpg（25 個讚）
*/

/**
 * 使用範例：
 */
/*
$user = User::find(1);

// 取得最受歡迎的圖片
$bestImage = $user->bestImage;  // Image|null

if ($bestImage) {
    echo "最受歡迎的圖片：{$bestImage->url}";
    echo "按讚數：{$bestImage->likes}";
} else {
    echo "用戶沒有圖片";
}

// 搭配其他關聯比較
$allImages = $user->images;        // Collection<Image> - 所有圖片
$latestImage = $user->latestImage; // Image|null - 最新圖片
$oldestImage = $user->oldestImage; // Image|null - 最舊圖片  
$bestImage = $user->bestImage;     // Image|null - 最受歡迎圖片
*/

/**
 * 方法特點總結：
 * 
 * 1. 回傳類型：Image|null（單一物件，不是 Collection）
 * 2. 多型關聯：同一個 images 表可服務多種模型（User、Post、Product 等）
 * 3. 智能篩選：從多張圖片中自動選出按讚數最高的
 * 4. 效能優化：使用 SQL 的 ORDER BY + LIMIT，不是 PHP 排序
 * 5. 空值安全：如果用戶沒有圖片，回傳 null 而不是錯誤
 * 
 * 等同於傳統寫法：
 * return $this->images()->orderBy('likes', 'desc')->limit(1);
 * 但 ofMany() 更語義化且支援複雜的多欄位排序
 */
```

---

### 8.4 *多對多／多型*（Many to Many Polymorphic）

#### 8.4.1 **資料表結構**

- `taggables` 為 *pivot table*，記錄 `tag 與多型父模型`（Post、Video）之間的關聯。

```php
posts
    id - integer
    name - string
videos
    id - integer
    name - string
tags
    id - integer
    name - string
taggables
    tag_id - integer
    taggable_id - integer
    taggable_type - string
```

---

#### 8.4.2 **模型定義**

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Post extends Model
{
    /**
     * 取得貼文的所有標籤。
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
```

---

- `Tag 模型`需 *為每個父模型* 定義一個 `morphedByMany` 關聯：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    /**
     * 取得所有被此標籤標記的貼文。
     */
    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }

    /**
     * 取得所有被此標籤標記的影片。
     */
    public function videos(): MorphToMany
    {
        return $this->morphedByMany(Video::class, 'taggable');
    }
}
```

---

#### 8.4.3 **關聯存取**

- 取得*貼文的所有標籤*：

```php
use App\Models\Post;

$post = Post::find(1);
foreach ($post->tags as $tag) {
    // ...
}
```

---

- 取得 *標籤所屬的* `所有貼文或影片`：

```php
use App\Models\Tag;

$tag = Tag::find(1);
foreach ($tag->posts as $post) {
    // ...
}
foreach ($tag->videos as $video) {
    // ...
}
```

---

### 8.5 *自訂多型類型*（Custom Polymorphic Types）

- Laravel 預設會將**完整類別名稱**存於 `type` 欄位（如 `App\Models\Post`），可透過 `enforceMorphMap` **自訂型別字串**，讓資料庫更易維護：

```php
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * 設定多型關聯的類別映射表
 * 將冗長的完整類別名稱映射為簡短的別名
 */
Relation::enforceMorphMap([
    'post' => 'App\\Models\\Post',    // 別名 'post' 對應 Post 模型
    'video' => 'App\\Models\\Video',  // 別名 'video' 對應 Video 模型
    'user' => 'App\\Models\\User',    // 別名 'user' 對應 User 模型
    'product' => 'App\\Models\\Product', // 可以添加更多映射
]);

/**
 * 建議放置位置：AppServiceProvider 的 boot() 方法
 */
// app/Providers/AppServiceProvider.php
class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 在應用啟動時註冊多型映射
        Relation::enforceMorphMap([
            'post' => 'App\\Models\\Post',
            'video' => 'App\\Models\\Video',
        ]);
    }
}
```

---

- 建議於 `AppServiceProvider` 的 `boot` 方法中設定。

- 若導入 `morph map`，需將資料庫既有 `type` 欄位值轉換為對應的 `map` 名稱。

- **沒有 Morph Map 時**

```sql
-- comments 表資料（使用完整類別名稱）
id | content | commentable_type        | commentable_id
1  | "Good"  | App\Models\Post         | 1
2  | "Nice"  | App\Models\Video        | 5
3  | "Cool"  | App\Models\Post         | 2
4  | "Wow"   | App\Models\User         | 3

-- 問題：
-- 1. 類別名稱很長，占用更多空間
-- 2. 如果改變命名空間，資料庫需要大量更新
-- 3. 不夠優雅和簡潔
```

---

- **使用 Morph Map 後**

```sql
-- comments 表資料（使用簡短別名）
id | content | commentable_type | commentable_id
1  | "Good"  | post             | 1
2  | "Nice"  | video            | 5
3  | "Cool"  | post             | 2
4  | "Wow"   | user             | 3

-- 優勢：
-- 1. 類別標識更簡潔
-- 2. 節省儲存空間
-- 3. 重構時更容易維護
-- 4. 資料庫內容更易讀
```

---

- 取得 **morph alias** 或 **反查類別名稱** ：

```php
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * 取得模型的 morph alias（別名）
 */
$post = new Post();
$alias = $post->getMorphClass();
echo $alias; // 輸出：'post'（而不是 'App\Models\Post'）

/**
 * 反查：從別名取得完整類別名稱
 */
$class = Relation::getMorphedModel('post');
echo $class; // 輸出：'App\Models\Post'

/**
 * 實際使用範例
 */
$comment = Comment::find(1);
echo $comment->commentable_type; // 'post'（資料庫中存的是別名）

$commentableClass = Relation::getMorphedModel($comment->commentable_type);
echo $commentableClass; // 'App\Models\Post'（取得完整類別名稱）

// 取得實際的模型實例
$commentable = $comment->commentable; // Post 模型實例
```

---

### 8.6 *動態關聯*（Dynamic Relationships）

- 可於執行階段**動態註冊關聯**，常用於 `package` 開發。
- 使用 `resolveRelationUsing` 註冊，建議**明確指定 key 名稱**：

```php
use App\Models\Order;
use App\Models\Customer;

/**
 * 動態定義 Order 模型的 customer 關聯
 * 在不修改 Order 模型類別的情況下，從外部註冊關聯關係
 */
Order::resolveRelationUsing('customer', function (Order $orderModel) {
    // 定義 Order belongsTo Customer 的關聯
    // 使用 customer_id 作為外鍵
    return $orderModel->belongsTo(Customer::class, 'customer_id');
});

// 使用方式：
// $order = Order::find(1);
// $customer = $order->customer; // 可以直接使用，就像在模型中定義的關聯一樣

// 適用場景：
// 1. 第三方套件擴展模型關聯
// 2. 條件式關聯定義
// 3. 避免修改核心模型類別
// 4. 動態關聯註冊
```

---

## 9. **Scoped Relationships**

- 常見做法：在 `Model` 增加方法，__為關聯加上額外條件__。

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    /**
     * Get the user's posts.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->latest();
    }

    /**
     * Get the user's featured posts.
     */
    public function featuredPosts(): HasMany
    {
        return $this->posts()->where('featured', true);
    }
}
```

---

- 若要讓透過 `featuredPosts()` create 的資料自動帶上 `featured=true`，可用 `withAttributes`：

```php
public function featuredPosts(): HasMany
{
    return $this->posts()->withAttributes(['featured' => true]);
}
```

---

- `withAttributes` 會*自動加 where 條件*，也會讓 `create` 時，*自動帶入該屬性*：

```php
$post = $user->featuredPosts()->create(['title' => 'Featured Post']);
$post->featured; // true
```

---

- 若只想 `create` 時帶入，*不加 where 條件*，可設 `asConditions: false`：

```php
return $this->posts()->withAttributes(['featured' => true], asConditions: false);
```

---

## 9. **Many to Many Relationships**


### 9.1 *Table Structure*

- 需**三張表**：`users、roles、role_user`（中介表，欄位 `user_id, role_id`）。
- **中介表名稱** 預設為`兩 model 名稱字母`排序組合。

---

### 9.2 *Model Structure*

- 定義 `belongsToMany` 關聯：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
```

---

- 取得**所有角色**：

```php
use App\Models\User;

$user = User::find(1);

foreach ($user->roles as $role) {
    // ...
}
```

---

- 可加**條件查詢**：

```php
$roles = User::find(1)->roles()->orderBy('name')->get();
```

---

- **自訂** 中介表名稱與 key：

```php
return $this->belongsToMany(Role::class, 'role_user');
return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
```

---

### 9.3 *Defining the Inverse of the Relationship*

- **另一端也定義** `belongsToMany：`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /**
     * The users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
```

---

### 9.4 *Retrieving Intermediate Table Columns*

- 可透過 `pivot` 屬性取得**中介表欄位**：

```php
use App\Models\User;

$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

---

- 若**中介表**有 __額外欄位__，需 `withPivot` 指定：

```php
return $this->belongsToMany(Role::class)->withPivot('active', 'created_by');
//                                       ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//                                       取得中間表的額外欄位資料
//                                       讓你可以存取 pivot.active 和 pivot.created_by
```
```php
// 建立 migration
`php artisan make:migration create_user_role_table`

// 在 migration 檔案中：
public function up()
{
    Schema::create('user_role', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained();     // 預設欄位
        $table->foreignId('role_id')->constrained();     // 預設欄位
        $table->boolean('active')->default(true);        // 額外欄位 1
        $table->unsignedBigInteger('created_by');         // 額外欄位 2
        $table->timestamps();
    });
}
```

---

- **沒有 `withPivot()`**

```php
return $this->belongsToMany(Role::class);

// 只能存取預設的 ID 欄位
$user->roles->first()->pivot->user_id;  // ✅ 可以
$user->roles->first()->pivot->role_id;  // ✅ 可以  
$user->roles->first()->pivot->active;   // ❌ 無法存取
```

---

- **有 `withPivot()`**

```php
return $this->belongsToMany(Role::class)->withPivot('active', 'created_by');

// 現在可以存取指定的額外欄位
$user->roles->first()->pivot->user_id;    // ✅ 可以
$user->roles->first()->pivot->role_id;    // ✅ 可以
$user->roles->first()->pivot->active;     // ✅ 現在可以存取了！
$user->roles->first()->pivot->created_by; // ✅ 現在可以存取了！
```

---

- 若要**自動維護** `created_at/updated_at`，需 `withTimestamps`：

```php
return $this->belongsToMany(Role::class)->withTimestamps();
//                                       ^^^^^^^^^^^^^^^^^
//                                       讓中間表自動管理 created_at 和 updated_at 欄位
```

---

### 9.5 *Customizing the pivot Attribute Name*

- 可用 `as()` 自訂 `pivot` **屬性名稱**：

```php
return $this->belongsToMany(Podcast::class)
    ->as('subscription')
    ->withTimestamps();
```

---

- **取得時**：

```php
$users = User::with('podcasts')->get();
foreach ($users->flatMap->podcasts as $podcast) {
    echo $podcast->subscription->created_at;
}
```

---

- **預設情況**（沒有 `as()`）

```php
return $this->belongsToMany(Podcast::class)->withTimestamps();

// 使用時：
$podcast = $user->podcasts->first();
echo $podcast->pivot->created_at;  // 固定使用 'pivot' 這個屬性名稱
//             ^^^^^
//             固定的屬性名稱
```

---

- **自訂情況**（使用 `as()`）

```php
return $this->belongsToMany(Podcast::class)
    ->as('subscription')  // 自訂屬性名稱為 'subscription'
    ->withTimestamps();

// 使用時：
$podcast = $user->podcasts->first();
echo $podcast->subscription->created_at;  // 現在使用 'subscription' 而不是 'pivot'
//             ^^^^^^^^^^^^
//             自訂的屬性名稱
```

---

- **資料庫結構**（`完全相同`）

```sql
-- user_podcast 中間表（欄位沒有改變）
CREATE TABLE user_podcast (
    user_id INT,
    podcast_id INT,
    created_at TIMESTAMP,  -- 欄位名稱還是一樣
    updated_at TIMESTAMP   -- 欄位名稱還是一樣
);
```

---

- **只是存取方式改變**

```php
// 預設方式
$podcast->pivot->created_at;        // 使用 'pivot'

// 自訂方式  
$podcast->subscription->created_at; // 使用 'subscription'

// 實際上存取的都是同一個資料！
```

---

- **語義化更清楚**

```php

// 不夠直觀
$podcast->pivot->created_at;           // 什麼 pivot？

// 更有意義
$podcast->subscription->created_at;    // 訂閱時間！
$user->groups->first()->membership->role; // 會員身份！
$student->courses->first()->enrollment->grade; // 註冊成績！
```

---

- **更符合業務邏輯**

```php

class User extends Model
{
    public function podcasts(): BelongsToMany
    {
        return $this->belongsToMany(Podcast::class)
            ->as('subscription')      // 訂閱關係
            ->withPivot('status', 'plan')
            ->withTimestamps();
    }
    
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
            ->as('enrollment')        // 註冊關係
            ->withPivot('grade', 'completed')
            ->withTimestamps();
    }
}

// 使用時更清楚
echo $podcast->subscription->status;  // 訂閱狀態
echo $course->enrollment->grade;      // 註冊成績
```

---

### 9.6 *Filtering Queries via Intermediate Table Columns*

- 可用   `wherePivot`、
        `wherePivotIn`、
        `wherePivotNotIn`、
        `wherePivotBetween`、
        `wherePivotNotBetween`、
        `wherePivotNull`、
        `wherePivotNotNull` 篩選：

```php
// 篩選中間表的 approved 欄位等於 1 的關聯記錄
return $this->belongsToMany(Role::class)
    ->wherePivot('approved', 1);

// 篩選中間表的 priority 欄位值在 [1, 2] 範圍內的關聯記錄
return $this->belongsToMany(Role::class)
    ->wherePivotIn('priority', [1, 2]);

// 篩選中間表的 priority 欄位值不在 [1, 2] 範圍內的關聯記錄
return $this->belongsToMany(Role::class)
    ->wherePivotNotIn('priority', [1, 2]);

// 篩選 2020 年內建立的訂閱記錄（中間表的 created_at 在指定時間範圍內）
return $this->belongsToMany(Podcast::class)
    ->as('subscriptions')
    ->wherePivotBetween('created_at', ['2020-01-01 00:00:00', '2020-12-31 00:00:00']);

// 篩選非 2020 年建立的訂閱記錄（中間表的 created_at 不在指定時間範圍內）
return $this->belongsToMany(Podcast::class)
    ->as('subscriptions')
    ->wherePivotNotBetween('created_at', ['2020-01-01 00:00:00', '2020-12-31 00:00:00']);

// 篩選尚未過期的訂閱（中間表的 expired_at 欄位為 null）
return $this->belongsToMany(Podcast::class)
    ->as('subscriptions')
    ->wherePivotNull('expired_at');

// 篩選已過期的訂閱（中間表的 expired_at 欄位不為 null）
return $this->belongsToMany(Podcast::class)
    ->as('subscriptions')
    ->wherePivotNotNull('expired_at');
```

---

- `withPivotValue()` 既是**查詢時**的 _篩選條件_，也是**建立關聯時**的 _預設值設定_！ 

```php
// 1. 查詢時：只回傳符合條件的關聯
// 2. 建立時：自動設定預設值
class User extends Model
{
    public function activeRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivotValue(['approved' => 1]); // 預設值
    }
}

// 查詢時：只取得已核准的角色
$activeRoles = $user->activeRoles; // 只有 approved=1 的角色會被查出

// 建立時：自動設定 approved=1
$user->activeRoles()->attach($roleId); // 新增關聯時，approved 欄位會自動設為 1

// attach - 會自動帶入 withPivotValue 設定的值
$user->activeRoles()->attach($roleId); // 新增關聯，pivot 欄位（如 approved）會用預設值

// sync - 會自動帶入預設值
$user->activeRoles()->sync([$roleId]); // 同步關聯，只保留指定 roleId，pivot 欄位用預設值

// toggle - 會自動帶入預設值
$user->activeRoles()->toggle($roleId); // 切換關聯，有則移除、無則新增，pivot 欄位用預設值

// save（如果是透過關聯）
$user->activeRoles()->save($role); // 儲存關聯模型，pivot 欄位也會自動處理
```

```php
// ❌ 直接 update pivot 不會自動帶入
$user->roles()->updateExistingPivot($roleId, ['status' => 'updated']);

// ❌ 手動 fill 也不會自動帶入（因為 fill 是針對模型本身）
$role->fill(['name' => 'Admin']); // 這是 Role 模型，不是 pivot
```

---

### 9.7 *Ordering Queries via Intermediate Table Columns*

- 可用 `orderByPivot` 排序：

```php
/**
 * 取得使用者的金牌徽章，依照 pivot 的 created_at 欄位由新到舊排序
 */
public function goldBadges()
{
    return $this->belongsToMany(Badge::class) // 多對多關聯
        ->where('rank', 'gold')               // 只取 rank 為 gold 的徽章
        ->orderByPivot('created_at', 'desc'); // 依 pivot 表的 created_at 由新到舊排序
}
```

---

### 9.8 *Defining Custom Intermediate Table Models*

- 可用 `using` 指定**自訂 pivot model**，需繼承 `Illuminate\Database\Eloquent\Relations\Pivot`：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /**
     * 角色與用戶的多對多關聯
     * 使用自訂的中間模型 RoleUser 來處理中間表邏輯
     * 
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(RoleUser::class);
        //                                       ^^^^^^^^^^^^^^^^^^^^^^
        //                                       指定使用自訂的中間模型，而非預設的 pivot
        //                                       讓中間表可以有自己的邏輯、關聯和方法
    }
}

// 對應的自訂中間模型 RoleUser：
class RoleUser extends Pivot
{
    public $incrementing = true;  // ← 自動遞增主鍵
    //     ^^^^^^^^^^^^^
    //     告訴 Laravel 這個中間表有自增主鍵
    
    protected $fillable = ['user_id', 'role_id', 'created_by'];
    
    // 其他中間模型的邏輯...
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

// 使用方式：
$role = Role::find(1);
$users = $role->users; // 回傳與角色關聯的用戶集合
// Laravel 自動建立 RoleUser 實例
$pivotModel = $users->first()->pivot; // 取得 RoleUser 實例而非普通 pivot
// $pivotModel 是 RoleUser 實例！
// 如果沒有指定 using()，取得的是普通的 pivot 物件
```

---

- 若 `pivot model` 有**自動遞增主鍵**，需設 `public $incrementing = true`；

```php
public $incrementing = true;
```

---

## 10. **Querying Relations**

Eloquent *關聯查詢* 可直接呼叫 *關聯方法* 取得`查詢建構器`，進一步串接查詢條件，靈活組合各種查詢。所有關聯方法皆可作為查詢建構器使用。

---

### 10.1 *關聯查詢基礎*

- `關聯方法`回傳 __查詢建構器__，可繼續`串接條件`：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    /**
     * 取得使用者的所有貼文。
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
```

---

- 查詢 `posts` 關聯並加上條件：

```php
use App\Models\User;

$user = User::find(1);
$user->posts()->where('active', 1)->get();
```

---

- *關聯查詢* 可使用`所有 Query Builder 方法`。

---

### 10.2 *orWhere 子句的注意事項*

- 直接串接 `orWhere` 會與關聯條件同層，可能導致查詢結果不正確：

```php
$user->posts()
//  ->where(user_id = $user->id); // 關聯方法 posts() 會自動加上這個條件
    ->where('active', 1)
    ->orWhere('votes', '>=', 100)
    ->get();
```

---

- 上述 SQL：

```sql
select *
from posts
where user_id = ? and active = 1 or votes >= 100
```

---

- 正確做法：用 `where(function)` 群組條件：

```php
use Illuminate\Database\Eloquent\Builder;

$posts = $user->posts()
//  ->where(user_id = $user->id); // 關聯方法 posts() 會自動加上這個條件
    ->where(function (Builder $query) {
        return $query->where('active', 1)
            ->orWhere('votes', '>=', 100);
    })
    ->get();
```

---

- 正確 SQL：

```sql
select *
from posts
where user_id = ? and (active = 1 or votes >= 100)
```

---

### 10.3 *關聯方法 vs. 動態屬性*

- 若不需額外條件，可直接用**屬性方式**存取（`lazy loading`）：

```php
use App\Models\User;

class User extends Model
{
    // 實際上沒有 $posts 屬性定義
    // public $posts; // ← 這個屬性並不存在
    
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}

$user = User::find(1);

// 動態屬性存取（無括號）
$posts = $user->posts;    // ← 動態屬性！實際上這個屬性不存在
//              ^^^^^     //   透過魔術方法存在：__get() 讓它「看起來存在」，有對應的實現邏輯：posts() 方法提供實際功能
//              這會直接回傳 Collection<Post>，已經執行查詢了

// 方法呼叫（有括號）
$posts = $user->posts();  // ← ← 注意有括號 ()，這是呼叫關連方法
//              ^^^^^^^   //    回傳 HasMany 關聯建構器物件
//              這會回傳 HasMany 關聯物件，可以繼續串接查詢條件
```

---

- **動態屬性** 會 `延遲載入（lazy loading）`，建議 **大量存取時** 用 `eager loading`。

---

### 10.4 *查詢關聯存在*（`has / whereHas`）

- 查詢有**至少一筆**關聯的資料：

```php
use App\Models\Post;

// 至少有一則留言的貼文
$posts = Post::has('comments')->get();
```

---

- **指定數量**：

```php
// 至少有三則留言的貼文
$posts = Post::has('comments', '>=', 3)->get();
```

---

- **巢狀關聯**（dot notation）：

```php
// 有留言且留言有圖片的貼文
$posts = Post::has('comments.images')->get();
```

---

- `whereHas` ** 可加上條件**：

```php
use Illuminate\Database\Eloquent\Builder;

// 有留言內容開頭為 code 的貼文
$posts = Post::whereHas('comments', function (Builder $query) {
    $query->where('content', 'like', 'code%');
})->get();

// 有十則留言內容開頭為 code 的貼文
$posts = Post::whereHas('comments', function (Builder $query) {
    $query->where('content', 'like', 'code%');
}, '>=', 10)->get();
```

---

- `Eloquent` **不支援跨資料庫** 的關聯查詢。

<!-- Eloquent 的關聯（如 hasMany、belongsTo）只能用在同一個資料庫的資料表之間，
     不能直接查詢「A 資料庫的表」關聯「B 資料庫的表」。
     如果資料分散在不同資料庫，Eloquent 關聯無法自動處理，需用原生 SQL 或 Query Builder。 -->

---

### 10.5 *多對多關聯存在查詢*（`whereAttachedTo`）

- 查詢有**多對多關聯**的模型：

```php
/**
 * 查詢與指定角色有多對多關聯的所有使用者
 * whereAttachedTo() 是 Laravel 提供的多對多關聯查詢方法
 */
$users = User::whereAttachedTo($role)->get();
//            ^^^^^^^^^^^^^^^^^^^^^^^
//            查詢在中間表中與 $role 有關聯記錄的 User
//            等同於檢查 user_role 表中是否存在對應的記錄

// 實際執行的 SQL 類似：
// SELECT users.* 
// FROM users 
// INNER JOIN user_role ON users.id = user_role.user_id 
// WHERE user_role.role_id = ?

// 使用場景範例：
$adminRole = Role::where('name', 'admin')->first();
$adminUsers = User::whereAttachedTo($adminRole)->get();  // 取得所有管理員用戶

// 也可以傳入 ID：
$adminUsers = User::whereAttachedTo(1)->get();  // 直接傳入角色 ID
//            ^^^^^^^^^^^^^^^^^^^^^^^^
//            查詢「與 ID=1 的角色有關聯」的所有用戶
// 假設 roles 表：
// id=1, name='admin'
// id=2, name='editor'

// 假設 user_role 中間表：
// user_id=5, role_id=1  (用戶5有admin角色)
// user_id=8, role_id=1  (用戶8有admin角色)  
// user_id=9, role_id=2  (用戶9有editor角色)
// 查詢結果：[用戶5, 用戶8]
// 因為他們在中間表中都與 role_id=1 有關聯


// 相反的查詢（查詢與指定用戶有關聯的角色）：
$specificUser = User::find(1);
$userRoles = Role::whereAttachedTo($specificUser)->get();  
//           ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//           查詢「與指定用戶有關聯」的所有角色
// 假設 user_role 中間表：
// user_id=1, role_id=1  (用戶1有admin角色)
// user_id=1, role_id=3  (用戶1有manager角色)
// user_id=2, role_id=2  (用戶2有editor角色)
// 查詢結果：[admin角色, manager角色]
// 因為在中間表中，用戶1與這兩個角色有關聯
```

---

- **傳入集合**，查詢`有任一關聯`的模型：

```php
/**
 * 先查詢符合條件的標籤集合
 * 找出名稱包含 'laravel' 的所有標籤
 */
$tags = Tag::whereLike('name', '%laravel%')->get();
// 假設查詢結果：[Tag1: 'laravel-tips', Tag2: 'laravel-news', Tag3: 'laravel-tutorial']

/**
 * 查詢與任一指定標籤有關聯的文章
 * whereAttachedTo() 接受集合時，會查詢與集合中「任何一個」模型有關聯的記錄
 */
$posts = Post::whereAttachedTo($tags)->get();
//            ^^^^^^^^^^^^^^^^^^^^^^
//            查詢在中間表中與 $tags 集合中任一標籤有關聯的 Post
//            等同於 OR 條件：與 Tag1 有關聯 OR 與 Tag2 有關聯 OR 與 Tag3 有關聯

// 實際執行的 SQL 類似：
// SELECT posts.* 
// FROM posts 
// INNER JOIN post_tag ON posts.id = post_tag.post_id 
// WHERE post_tag.tag_id IN (1, 2, 3)  -- Tag1, Tag2, Tag3 的 ID

// 使用場景範例：
$techTags = Tag::whereIn('name', ['PHP', 'JavaScript', 'Python'])->get();
$techPosts = Post::whereAttachedTo($techTags)->get();  // 取得所有與技術標籤相關的文章

$userInterests = $user->interestedTags;  // 用戶感興趣的標籤
$recommendedPosts = Post::whereAttachedTo($userInterests)->get();  // 推薦文章
```

---

### 10.6 *Inline 關聯存在查詢*（`whereRelation / whereMorphRelation`）

- **單一條件** 可用 `whereRelation、whereMorphRelation`：

```php
use App\Models\Post;

// 有未審核留言的貼文
$posts = Post::whereRelation('comments', 'is_approved', false)->get();

// 指定運算子
$posts = Post::whereRelation(
    'comments', 'created_at', '>=', now()->subHour()
)->get();
```

---

### 10.7 *查詢關聯不存在*（`doesntHave / whereDoesntHave`）

- 查詢**沒有任何**留言的貼文：

```php
use App\Models\Post;

$posts = Post::doesntHave('comments')->get();
//            ^^^^^^^^^^^^^^^^^^^^^^^^
//            查詢「沒有任何評論」的文章
//            等同於查詢 comments 關聯為空的 Post
```

---

- **加上條件**：

```php
use Illuminate\Database\Eloquent\Builder;

$posts = Post::whereDoesntHave('comments', function (Builder $query) {
    $query->where('content', 'like', 'code%');
})->get();
```

---

- **巢狀關聯**不存在查詢：

```php
use Illuminate\Database\Eloquent\Builder;

$posts = Post::whereDoesntHave('comments.author', function (Builder $query) {
    $query->where('banned', 1);
})->get();
//     ^^^^^^^^^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//     嵌套關聯路徑          條件：作者被封鎖 (banned = 1)
//     Post → Comment → Author
```

---

### 10.8 *多型關聯查詢*（`whereHasMorph / whereDoesntHaveMorph`）

```php
// 1. whereHasMorph - 查詢子模型 (Comment)
Comment::whereHasMorph(
    'commentable',                // 參數1：關聯名稱
    [Post::class, Video::class],  // 參數2：模型類別
    function (Builder $query) {   // 參數3：查詢條件（可選）
        $query->where('title', 'like', 'code%');
    }
)->get();
// 3個參數（條件查詢）
// 目標：Comment / 條件：父模型的屬性

// 2. whereDoesntHaveMorph - 查詢子模型 (Comment)  
$comments = Comment::whereDoesntHaveMorph('commentable', [Post::class], function ($query) {
    $query->where('title', 'like', 'code%');
})->get();
// 目標：Comment / 條件：沒有符合條件的父模型

// 3. whereMorphedTo - 查詢子模型 (Comment)
// $post 必須是一個具體的 Post 模型實例
$post = Post::find(1);  // 取得 ID=1 的 Post 實例

Comment::whereMorphedTo('commentable', $post)
//                      ^^^^^^^^^^^^^  ^^^^^
//                      參數1：關聯名稱   參數2：模型實例
//                                            具體的 Post 實例
//                                             不是 Post::class
//                                               不是 Post ID
// 2個參數（精確匹配特定實例）
// 自動查詢：commentable_type = 'Post' AND commentable_id = $post->id
// 目標：Comment / 條件：特定的父模型實例
```

---

- 查詢**多型關聯**存在：

```php
use App\Models\Comment;
use App\Models\Post;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;

/**
 * 查詢多型關聯中符合條件的評論
 * whereHasMorph: 查詢「有」符合條件的多型關聯記錄
 */
// 查詢關聯到 Post 或 Video，且 title 開頭為 code 的留言
$comments = Comment::whereHasMorph(
    // 參數1：多型關聯名稱
    'commentable',                    // 多型關聯名稱 (Comment 的 commentable 關聯)
    // 參數2：目標模型類型陣列
    [Post::class, Video::class],      // 限制多型關聯的目標類型 (只檢查 Post 和 Video)
    // 參數3：查詢條件回調函數
    function (Builder $query) {       // 對目標模型的條件查詢
        $query->where('title', 'like', 'code%');  // 目標模型的 title 欄位以 'code' 開頭
    }
)->get();
// 結果：回傳所有評論，這些評論屬於「標題以 code 開頭」的 Post 或 Video

/**
 * 查詢多型關聯中不符合條件的評論  
 * whereDoesntHaveMorph: 查詢「沒有」符合條件的多型關聯記錄
 */
// 查詢關聯到 Post，且 title 不為 code 開頭的留言
$comments = Comment::whereDoesntHaveMorph(
    'commentable',                    // 多型關聯名稱
    Post::class,                      // 只檢查 Post 類型 (單一類型，不是陣列)
    function (Builder $query) {       // 對 Post 模型的條件查詢
        $query->where('title', 'like', 'code%');  // Post 的 title 欄位以 'code' 開頭
    }
)->get();
// 結果：回傳所有評論，這些評論「不是」屬於「標題以 code 開頭的 Post」
//       包括：1. 屬於標題不以 code 開頭的 Post 的評論
//            2. 屬於其他類型模型 (如 Video, User) 的評論

// 對應的關聯結構：
 class Comment extends Model {
     public function commentable(): MorphTo {
         return $this->morphTo();  // 多型關聯，可以指向 Post, Video, User 等
     }
 }

// 資料庫結構：
// comments: id, content, commentable_type, commentable_id
// posts: id, title, content
// videos: id, title, description
```

---

- 依多型類型**調整查詢欄位**：

```php
use Illuminate\Database\Eloquent\Builder;

/**
 * 進階多型關聯查詢：根據不同的目標模型類型使用不同的查詢條件
 * 回調函數可以接收第二個參數 $type 來判斷當前處理的模型類型
 */
$comments = Comment::whereHasMorph(
    'commentable',                           // 多型關聯名稱
    [Post::class, Video::class],             // 要檢查的模型類型陣列
    function (Builder $query, string $type) {  // 回調函數現在接收兩個參數
        // 根據模型類型決定要查詢的欄位
        $column = $type === Post::class ? 'content' : 'title';
        //        ^^^^^^^^^^^^^^^^^^^^^^   ^^^^^^^^^   ^^^^^^^
        //        如果是 Post 類型          使用 content 欄位
        //                                 否則使用 title 欄位
        
        // 對不同欄位套用相同的查詢條件
        $query->where($column, 'like', 'code%');
    }
)->get();

// 實際執行邏輯：
// 1. 當檢查 Post 時：WHERE posts.content LIKE 'code%'
// 2. 當檢查 Video 時：WHERE videos.title LIKE 'code%'

// 結果：回傳所有評論，這些評論屬於：
// - content 欄位以 'code' 開頭的 Post，或
// - title 欄位以 'code' 開頭的 Video

// 使用場景：
// - 不同模型有不同的欄位結構，但需要類似的查詢邏輯
// - 統一搜尋不同類型內容的相關評論
// - 根據模型類型調整查詢策略

// 對應的模型結構：
 class Post extends Model {
   // 欄位：id, title, content, created_at...
}
class Video extends Model {
   // 欄位：id, title, description, url, created_at...
}
```

---

- 查詢**屬於特定父模型**的`多型子模型(目標)`：

```php
/**
 * 查詢屬於特定模型實例的評論
 * whereMorphedTo() 可以精確指定多型關聯的目標模型實例
 */
$comments = Comment::whereMorphedTo('commentable', $post)
    //                ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    //                查詢屬於這個特定 $post 實例的評論
    //                等同於：commentable_type = 'App\Models\Post' 
    //                       AND commentable_id = $post->id
    
    ->orWhereMorphedTo('commentable', $video)
    // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    // 或者查詢屬於這個特定 $video 實例的評論
    // 等同於：OR (commentable_type = 'App\Models\Video' 
    //            AND commentable_id = $video->id)
    
    ->get();

// 實際執行的 SQL 類似：
// SELECT * FROM comments 
// WHERE (commentable_type = 'App\\Models\\Post' AND commentable_id = ?)
//    OR (commentable_type = 'App\\Models\\Video' AND commentable_id = ?)

// 使用前提：
// $post = Post::find(1);   // 特定的文章實例
// $video = Video::find(5); // 特定的影片實例

// 查詢結果：
// 回傳所有屬於「這篇特定文章」或「這支特定影片」的評論

// 使用場景：
// - 查詢多個特定內容的所有評論
// - 合併不同類型內容的評論列表
// - 建立跨類型的內容評論統計

// 等同的傳統寫法：
 $comments = Comment::where(function($query) use ($post, $video) {
     $query->where(function($q) use ($post) {
         $q->where('commentable_type', Post::class)
           ->where('commentable_id', $post->id);
     })->orWhere(function($q) use ($video) {
         $q->where('commentable_type', Video::class)
           ->where('commentable_id', $video->id);
     });
 })->get();
```

---

### 10.9 *查詢所有多型關聯型態*（`* 萬用字元`）

- 使用 `* 萬用字元`查詢**所有多型型態**：

```php
use Illuminate\Database\Eloquent\Builder;

/**
 * 使用萬用字元 '*' 查詢所有可能的多型關聯類型
 * 不限制目標模型類型，對所有相關的模型套用相同條件
 */
$comments = Comment::whereHasMorph('commentable', '*', function (Builder $query) {
    $query->where('title', 'like', 'foo%');
})->get();
//                                                ^^^
//                                                萬用字元：代表所有可能的模型類型
//                                                等同於 [Post::class, Video::class, User::class, ...]

// 查詢邏輯：
// 1. 檢查 Comment 的 commentable 關聯
// 2. 不管 commentable 指向什麼類型的模型 (Post, Video, User, Product...)
// 3. 只要該模型有 title 欄位且以 'foo' 開頭，就符合條件
// 4. 回傳所有符合條件的 Comment

// 實際執行時，Laravel 會：
// 1. 掃描資料庫中所有不同的 commentable_type 值
// 2. 對每種類型執行相應的查詢
// 3. 合併所有結果

// 等同於手動指定所有類型：
// $comments = Comment::whereHasMorph(
//     'commentable', 
//     [Post::class, Video::class, User::class, Product::class, ...],
//     function (Builder $query) {
//         $query->where('title', 'like', 'foo%');
//     }
// )->get();

// 使用場景：
// - 不確定有哪些模型類型與 Comment 關聯
// - 想要搜尋所有類型中符合條件的內容
// - 動態系統中模型類型可能經常變化

// 注意事項：
// - 所有相關模型都必須有 'title' 欄位，否則會出錯
// - 效能可能較差，因為需要查詢多個不同的表
// - 建議在明確知道模型類型時使用具體的類別陣列
```

---

## 11. **Aggregating Related Models**

Eloquent 提供多種 *聚合方法*，可直接對關聯模型進行`計數、加總、平均`等運算，*無需實際載入所有* 關聯資料。

---

### 11.1 *計算關聯數量*（`withCount / loadCount`）

- 使用 `withCount` 可直接取得**關聯數量**，結果會在模型上新增 `{relation}_count` 屬性：

```php
use App\Models\Post;

// 在查詢模型的同時載入計數
$posts = Post::withCount('comments')->get();
//             ^^^^^^^^^^^^^^^^^^^^
//           在 SELECT 查詢時就包含計數

foreach ($posts as $post) {
    echo $post->comments_count;
}
```

---

- 可**同時計算多個**關聯，並對特定關聯`加上條件`：

```php
use Illuminate\Database\Eloquent\Builder;

$posts = Post::withCount(['votes', 'comments' => function (Builder $query) {
    $query->where('content', 'like', 'code%');
}])->get();

echo $posts[0]->votes_count;
echo $posts[0]->comments_count;
```

---

- 可**自訂 count 欄位名稱（alias／別名）**，同時取得`多種條件`下的計數：

```php
use Illuminate\Database\Eloquent\Builder;

$posts = Post::withCount([
    'comments',
    'comments as pending_comments_count' => function (Builder $query) {
        $query->where('approved', false);
    },
])->get();

echo $posts[0]->comments_count;
echo $posts[0]->pending_comments_count;
```

---

- **已取得模型後**，可用 `loadCount` **延遲載入**關聯數量：
- 如果**還沒取得**模型，應該用 `withCount()` 而不是 `loadCount()`！

- `loadCount`

```php
// 已經有模型實例，之後才載入計數
$book = Book::first();       // 第一次查詢：載入模型
$book->loadCount('genres');  // 第二次查詢：「只」載入計數

// 現在可以存取計數，無需載入實際的 Genre 記錄
echo $book->genres_count;    // 直接使用計數
//        ^^^^^^^^^^^^^ 
//        自動產生的計數屬性

// 優勢：
// 1. 只執行 COUNT() 查詢
// 2. 節省記憶體
// 3. 更快的查詢速度
```

---

- `load` -> `count()`

```php
$book = Book::first();
$book->load('genres');           // 載入所有 Genre 記錄
$count = $book->genres->count(); // 在 PHP 中計數

// 問題：
// 1. 從資料庫載入所有 Genre 記錄（可能很多）
// 2. 佔用更多記憶體
// 3. 網路傳輸更多資料
```

---

- **加上條件**：

```php
$book->loadCount(['reviews' => function (Builder $query) {
    $query->where('rating', 5);
}]);
```

---

- 若與 `select()` 合併，需將 `withCount` 寫在 **select 之後**：

```php
$posts = Post::select(['title', 'body'])
    ->withCount('comments')
    ->get();
//   ^^^^^^^^^^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^
//   只選取特定欄位          同時載入評論計數
//   (不包含所有欄位)        (額外加上 comments_count)
// 結果：只有 title, body + comments_count 欄位
// SELECT title, body, (SELECT COUNT(*) FROM comments...) as comments_count FROM posts
```

---

- vs 只有 `withCount()`

```php
$posts = Post::withCount('comments')->get();
// 結果：所有 Post 欄位 + comments_count
// SELECT posts.*, (SELECT COUNT(*) FROM comments...) as comments_count FROM posts
```

---

### 11.2 *其他聚合方法*（`withMin / withMax / withAvg / withSum / withExists`）

- 可直接取得關聯的**最小值、最大值、平均、加總**、*是否存在*等。
- **統一格式**：
  - {關聯}_{聚合函數}_{欄位}
  - {relation}_{function}_{column}

```php
use App\Models\Post;

// 第一種：計算 comments 關聯中 votes 欄位的總和
$posts = Post::withSum('comments', 'votes')->get();
//             ^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//             對每篇 Post 的所有 comments 的 votes 欄位求和
//             結果：$post->comments_sum_votes

// 第二種：計算 comments 和 votes 兩個關聯的記錄數量總和
$posts = Post::withSum(['comments', 'votes'])->get();
//             ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//             分別計算 comments 關聯和 votes 關聯的記錄數量
//             結果：$post->comments_sum 和 $post->votes_sum
```

---

- 可**自訂**聚合結果名稱（alias）：

```php
$posts = Post::withSum('comments as total_comments', 'votes')->get();
//             ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//             關聯名稱 + as + 自訂別名

// 1. 找到 comments 關聯
// 2. 計算這個關聯中所有記錄的 votes 欄位總和
// 3. 把結果存為 total_comments 屬性（而不是預設的 comments_sum_votes）

// 實際 SQL：
// SELECT posts.*, 
//        (SELECT SUM(votes) FROM comments WHERE post_id = posts.id) as total_comments
// FROM posts

foreach ($posts as $post) {
    echo $post->total_comments;  // 使用自訂的別名，而不是預設的 comments_sum_votes
}}
```

---

- **已取得模型後**，可用 `loadSum` 等**延遲載入**聚合結果：

```php
$post = Post::first();
$post->loadSum('comments', 'votes');
```

---

- 與 `select()` 合併時，聚合方法需寫在 **select 之後**：

```php
$posts = Post::select(['title', 'body'])
    ->withExists('comments')
    ->get();
```

---

### 11.3 *多型關聯的聚合*（`morphWithCount / loadMorphCount`）

- **多型關聯** 可針對`不同型態的父模型`分別聚合：

```php
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * 查詢活動動態，並為不同類型的父模型載入不同的關聯計數
 * 使用 morphWithCount 根據多型關聯的類型載入對應的計數
 * 
 * 重要：morphWithCount 必須在 with() 預載入中使用才有效！
 * 因為它需要在關聯載入過程中同時執行計數查詢，避免 N+1 問題
 */
$activities = ActivityFeed::with([
    'parentable' => function (MorphTo $morphTo) {
        //              ^^^^^^^^^^^^^^^^^^^^^^
        //              多型關聯的預載入回調函數
        //              沒有這個預載入，morphWithCount 不會執行
        
        $morphTo->morphWithCount([
            Photo::class => ['tags'],      // 如果 parentable 是 Photo，載入 tags_count
            Post::class => ['comments'],   // 如果 parentable 是 Post，載入 comments_count
        ]);
        //    ^^^^^^^^^^^^^^^^^^^^^^^^^^^
        //    必須配合 with() 使用：
        //    1. 在預載入時就知道所有要載入的模型類型
        //    2. 批次執行計數查詢，而不是逐一查詢
        //    3. 計數結果直接附加到載入的模型上
        //    
        //    如果沒有 with()：
        //    - morphWithCount 不會觸發
        //    - 每次存取 parentable 都會產生額外查詢
        //    - tags_count/comments_count 屬性不存在
    }
])->get();

// 使用結果：
 foreach ($activities as $activity) {
     if ($activity->parentable instanceof Photo) {
         echo $activity->parentable->tags_count;     // Photo 的標籤數量
     } elseif ($activity->parentable instanceof Post) {
         echo $activity->parentable->comments_count; // Post 的評論數量
     }
 }

// 對應的關聯結構：
 class ActivityFeed extends Model {
     public function parentable(): MorphTo {
         return $this->morphTo(); // 可以指向 Photo, Post 等不同模型
     }
 }

// 實際執行的 SQL（with + morphWithCount 的效果）：
// 1. SELECT * FROM activity_feeds;
// 2. SELECT photos.*, (SELECT COUNT(*) FROM photo_tag WHERE photo_id = photos.id) as tags_count 
//    FROM photos WHERE id IN (1,3,7);
// 3. SELECT posts.*, (SELECT COUNT(*) FROM comments WHERE post_id = posts.id) as comments_count 
//    FROM posts WHERE id IN (5,8,12);
// 總共只有 3 次查詢，不管有多少個 activity！`
```
---

- **已取得模型後**，可用 `loadMorphCount` **延遲載入** 多型聚合：

```php
$activities = ActivityFeed::with('parentable')->get();

$activities->loadMorphCount('parentable', [
    Photo::class => ['tags'],
    Post::class => ['comments'],
]);
```

---

## 12. **Eager Loading**

`Eloquent` *預設* 為`「延遲載入」（lazy loading）`，即僅`在存取關聯屬性時才查詢資料庫`。若需預先載入關聯（避免 N+1 問題），可使用「預先載入」（eager loading）。

---

### 12.1 *N+1 問題與 eager loading*

- **直接存取** 關聯屬性會產生 `N+1` 問題：

```php
use App\Models\Book;

$books = Book::all();

foreach ($books as $book) {
    echo $book->author->name;
}
```

---

- 若有 25 本書，會查詢 1 次 books + 25 次 authors。

---

- 使用 `eager loading` 只需 2 次查詢：

```php
$books = Book::with('author')->get();

foreach ($books as $book) {
    echo $book->author->name;
}
```

---

- SQL 範例：

```sql
select * from books
select * from authors where id in (1, 2, 3, ...)
```

---

### 12.2 *載入多個/巢狀關聯*

- 載入**多個關聯**：

```php
$books = Book::with(['author', 'publisher'])->get();
//             ^^^^^^^^^^^^^^^^^^^^^^^^
//             同時預載入 author 和 publisher 兩個關聯
```

---

- **資料表結構**

```sql
-- books 表:
┌────┬───────────┬───────────┬──────────────┐
│ id │ title     │ author_id │ publisher_id │
├────┼───────────┼───────────┼──────────────┤
│ 1  │ 書名A      │ 10        │ 5            │
└────┴───────────┴───────────┴──────────────┘

-- authors 表:        publishers 表:
┌────┬────────┐   ┌────┬──────────┐
│ id │ name   │   │ id │ name     │
├────┼────────┤   ├────┼──────────┤
│ 10 │ 作者A   │   │ 5  │ 出版社A  │
└────┴────────┘   └────┴──────────┘
```
---

- 巢狀 `eager loading`（**dot notation**）：

```php
$books = Book::with('author.contacts')->get();
//             ^^^^^^^^^^^^^^^^^^^^
//             預載入嵌套關聯：Book → Author → Contacts
//             一次查詢載入書籍、作者和作者的聯絡方式
```

---

- **資料表結構**

```sql
-- books 表:
┌────┬───────────┬───────────┐
│ id │ title     │ author_id │
├────┼───────────┼───────────┤
│ 1  │ 書名A     │ 10        │
└────┴───────────┴───────────┘

-- authors 表:
┌────┬────────┐
│ id │ name   │
├────┼────────┤
│ 10 │ 作者A  │
└────┴────────┘

-- contacts 表:  ← 獨立的表，不是 authors 的欄位
┌────┬───────────┬────────┬─────────────────┐
│ id │ author_id │ type   │ value           │
├────┼───────────┼────────┼─────────────────┤
│ 1  │ 10        │ email  │ author@mail.com │
│ 2  │ 10        │ phone  │ 0912345678      │
└────┴───────────┴────────┴─────────────────┘

-- books 表
CREATE TABLE books (
    id INT,
    title VARCHAR,
    author_id INT  -- 外鍵指向 authors
);

-- authors 表  
CREATE TABLE authors (
    id INT,
    name VARCHAR
);

-- contacts 表
CREATE TABLE contacts (
    id INT,
    author_id INT,  -- 外鍵指向 authors
    type VARCHAR,   -- email, phone, address 等
    value VARCHAR
);
```

---

- 巢狀 `eager loading`（**巢狀陣列**）：

```php
// 在 with() 中，Laravel 只關心「載入哪個關聯」
// 不管關聯類型是什麼，都用同樣的字串表示
'author' => [
    'contacts',    // 載入 contacts 關聯（不管是 hasMany），可能是任何關聯類型
    'publisher',   // 載入 publisher 關聯（不管是 belongsTo），可能是任何關聯類型
    //  實際的關聯類型要看模型中的關聯定義
],

// Laravel 會自動：
// 1. 檢查 Author 模型的 contacts() 方法
// 2. 發現是 hasMany，執行對應的查詢
// 3. 檢查 Author 模型的 publisher() 方法  
// 4. 發現是 belongsTo，執行對應的查詢
```

---

### 12.3 *多型關聯巢狀 eager loading*

- `morphTo` 關聯可針對 **`不同型態`載入`不同巢狀關聯`** ：

```php
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * 查詢活動動態，並根據多型關聯的不同類型預載入不同的關聯資料
 * 使用 morphWith 根據 parentable 的實際類型載入對應的關聯
 */
$activities = ActivityFeed::query()
    ->with(['parentable' => function (MorphTo $morphTo) {
        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
        // 預載入 parentable 多型關聯，並在回調中指定不同類型的額外關聯
        
        $morphTo->morphWith([
            Event::class => ['calendar'],  // 如果 parentable 是 Event，同時載入 calendar 關聯
            Photo::class => ['tags'],      // 如果 parentable 是 Photo，同時載入 tags 關聯  
            Post::class => ['author'],     // 如果 parentable 是 Post，同時載入 author 關聯
        ]);
        //    ^^^^^^^^^^^^^^^^^^^^^^^^^^^
        //    根據多型關聯的實際類型，載入不同的額外關聯資料
        //    避免在迴圈中重複查詢這些關聯
    }])->get();

// 使用結果：
 foreach ($activities as $activity) {
     if ($activity->parentable instanceof Event) {
         echo $activity->parentable->title;          //  Event 的 title 欄位
         echo $activity->parentable->calendar->name;  // Event 的 calendar 已預載入
     } elseif ($activity->parentable instanceof Photo) {
         echo $activity->parentable->url;                // Photo 的 url 欄位（沒有 title）
         echo $activity->parentable->tags->pluck('name');// Photo 的 tags 已預載入
     } elseif ($activity->parentable instanceof Post) {
         echo $activity->parentable->title;         //  Post 的 title 欄位
         echo $activity->parentable->author->name;   // Post 的 author 已預載入
     }
 }

// 對應的關聯結構：
 class ActivityFeed extends Model {
     public function parentable(): MorphTo {
         return $this->morphTo(); // 可以指向 Event, Photo, Post 等
     }
 }
 class Event extends Model {
     public function calendar(): BelongsTo { 
         return $this->belongsTo(Calendar::class);
        }
 }
 class Photo extends Model {
     public function tags(): BelongsToMany {
         return $this->belongsToMany(Tag::class); 
        }
 }
 class Post extends Model {
     public function author(): BelongsTo {
         return $this->belongsTo(User::class);
        }
 }
```

---

- **關聯層次圖**

```sql
ActivityFeed
│
├─ morphTo('parentable') ─┐
│                         │
│  ┌─────────────────────────────────┐
│  │                                 │
│  ▼                                 ▼
│ Event ──belongsTo──→ Calendar    Photo ──belongsToMany──→ Tag
│  │                                 │
│  └─ morphMany(ActivityFeed) [可選]  │
│                                    └─ morphMany(ActivityFeed) [可選]
│
│  ▼
│ Post ──belongsTo──→ User (Author)
│  │
│  └─ morphMany(ActivityFeed) [可選]
```

---

### 12.4 *指定關聯欄位*

- 只載入關聯的**部分欄位**：

```php
$books = Book::with('author:id,name,book_id')->get();
//             ^^^^^^^^^^^^^^^^^^^^^^^^^^^
//             預載入 author 關聯，但只選取指定欄位
//             只載入 id, name, book_id 三個欄位，節省記憶體
```

---

- **必須**包含 `id` 與`外鍵欄位`。

---

### 12.5 *模型預設 eager loading*

- 於**模型中**定義 `$with` 屬性，**預設**載入關聯：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    /**
     * 預設載入的關聯 (Eager Loading by Default)
     * 每次查詢 Book 模型時都會自動載入這些關聯，避免 N+1 問題
     *
     * @var array
     */
    protected $with = ['author'];
    //         ^^^^^ ^^^^^^^^^^
    //         Laravel 的魔術屬性，定義預設要載入的關聯
    //                  每次執行 Book::all()、Book::find() 等查詢時
    //                  都會自動執行 ->with('author')

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
        // 定義與 Author 的多對一關聯
        // books.author_id → authors.id
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
        // 定義與 Genre 的多對一關聯  
        // books.genre_id → genres.id
        // 注意：genre 沒有在 $with 中，所以不會自動載入
    }
}

// 使用效果：
// $books = Book::all();  // 自動載入 author，但不載入 genre
// 等同於：Book::with('author')->get();

// 如果要暫時停用預設載入：
// $books = Book::without('author')->get();

// 如果要載入額外關聯：
// $books = Book::with('genre')->get();  // 載入 author + genre
```

---

- 單次查詢**移除預設**關聯：

```php
$books = Book::without('author')->get();
```

---

- 單次查詢**只載入特定**關聯：

```php
$books = Book::withOnly('genre')->get();
```

---

### 12.6 *限制 eager loading 查詢條件*

- 可對 `eager loading` 關聯加上**查詢條件**：

```php
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;

$users = User::with(['posts' => function (Builder $query) {
    $query->where('title', 'like', '%code%');
}])->get();
```

---

- **其他查詢方法**亦可串接：

```php
$users = User::with(['posts' => function (Builder $query) {
    $query->orderBy('created_at', 'desc');
}])->get();
```

---

### 12.7 *多型關聯 eager loading 條件限制*

- `morphTo` 關聯可針對**不同型態**加上**查詢條件**：

```php
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * 查詢評論並載入多型關聯，同時對不同類型的目標模型套用不同的查詢條件
 * 使用 constrain 方法根據多型關聯的類型添加額外的查詢約束
 */
$comments = Comment::with(['commentable' => function (MorphTo $morphTo) {
    //                    ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    //                    預載入 commentable 多型關聯，並在回調中設定條件約束
    
    $morphTo->constrain([
        Post::class => function ($query) {
            $query->whereNull('hidden_at');
            //     ^^^^^^^^^^^^^^^^^^^^^^
            //     對 Post 類型的 commentable 添加條件：只載入未隱藏的文章
            //     即 hidden_at 欄位為 null 的文章
        },
        Video::class => function ($query) {
            $query->where('type', 'educational');
            //     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
            //     對 Video 類型的 commentable 添加條件：只載入教育類型的影片
            //     即 type 欄位等於 'educational' 的影片
        },
    ]);
    //  ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    //  constrain 讓你對不同類型的多型關聯目標套用不同的查詢條件
    //  如果 commentable 指向的模型不符合條件，該 comment 的 commentable 會是 null
}])->get();

// 使用結果：
 foreach ($comments as $comment) {
     if ($comment->commentable) {  // 檢查是否符合條件
         if ($comment->commentable instanceof Post) {
            //  這個 Post 一定是 hidden_at 為 null 的
             echo $comment->commentable->title;
         } elseif ($comment->commentable instanceof Video) {
            //  這個 Video 一定是 type = 'educational' 的
             echo $comment->commentable->title;
         }
     } else {
        //  commentable 為 null，表示不符合設定的條件
         echo "相關內容不符合條件或已被隱藏";
     }
 }

// 實際執行的 SQL：
// 1. SELECT * FROM comments;
// 2. SELECT * FROM posts WHERE id IN (...) AND hidden_at IS NULL;
// 3. SELECT * FROM videos WHERE id IN (...) AND type = 'educational';
```

---

### 12.8 *`withWhereHas`：篩選主模型 + 預載入關聯(eager loading)*

- 只查詢**有特定條件關聯**的`主模型`，並同時 `eager load`：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // 一個用戶有多篇文章
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// 查詢有精選文章的用戶，並預載入所有文章
$users = User::with('posts')
    ->whereHas('posts', function ($query) {
        $query->where('featured', true); // 只篩選有精選文章的用戶
    })
    ->get();

// 結果範例
foreach ($users as $user) {
    echo $user->name . " 的文章：\n";
    foreach ($user->posts as $post) {
        echo "- " . $post->title . "\n";
    }
}

// 做了兩件事：
// 1. 只回傳「有精選文章」的用戶 (篩選主模型)
// 2. 預載入這些用戶的 posts 關聯 (避免 N+1)


// 說明：
- 只回傳有精選文章的用戶（例如：用戶A、用戶C）
- 用戶A->posts: [精選文章1, 普通文章2]  // 載入所有文章
- 用戶C->posts: [精選文章5]
- 用戶B 被排除，因為沒有精選文章


// 如果不用 withWhereHas，可以分開寫 whereHas 和 with：

$users = User::with(['posts' => function ($query) {
        $query->where('featured', true); // 只載入精選文章
    }])
    ->whereHas('posts', function ($query) {
        $query->where('featured', true); // 只篩選有精選文章的用戶
    })
    ->get();
```
```php
// 想要「有訂單的客戶」及其訂單
User::withWhereHas('orders', function ($query) {
    $query->where('status', 'completed');
})

```

---

- **vs `with()` = 載入所有主模型 + 篩選關聯資料**

```php
// 想要「所有用戶」但只載入「精選文章」
User::with(['posts' => function ($query) {
    $query->where('featured', true);
}])

// 只做一件事：
// 1. 回傳所有用戶
// 2. 但每個用戶只載入精選文章 (關聯篩選，不是用戶篩選)

// 回傳所有用戶，但只載入精選文章
結果: [用戶A, 用戶B, 用戶C]
用戶A->posts: [精選文章1]           // 只載入精選文章
用戶B->posts: []                   // 空集合，因為沒有精選文章
用戶C->posts: [精選文章5]
// 用戶B 仍然存在，但 posts 是空的
```

---

### 12.9 *Deferred Eager Loading* 或 *Conditional Eager Loading*

- 這章節討論的是 `load()` 預載入的「**時機與條件策略**」，而非 `load()` 方法本身的用法與功能，或者 `load()` 只能如此使用的局限性。

- `Deferred Eager Loading` = **延遲預載入**
  - *先取得主模型*，__延後決定 是否預載入關聯__
  - 不是說 `load()` 的功能
  - 是說 *預載入的時機* = 延遲到取得模型之後

  ```php
  // 總是會載入，只是延遲時機
  $books = Book::all();
  $books->load('author');      // 延遲但無條件
  ```

---

- `Conditional Eager Loading` = **條件預載入**
  - *根據條件決定* 是否預載入關聯
  - 不是說 `load()` 的功能
  - 是說 *預載入的條件* = 根據條件決定是否/如何預載入

  ```php
  // 查詢時就根據條件決定
  $books = $needAuthors 
      ? Book::with('author')->get()    // 有條件但不延遲
      : Book::all();
  ```

---

- **延遲 + 條件預載入**

```php
// 既延遲時機，又有條件
$books = Book::all();
if ($needAuthors) {
    $books->load('author');  // 延遲 + 條件
}
```

---

- **`with()` vs `load()`**

```php
// with() 和 load() 都是預載入（Eager Loading）
$users = User::with('posts')->get();    // 查詢時預載入
$users = User::all()->load('posts');    // 後續預載入
```

---

- 加上**查詢條件**：

```php
$author->load(['books' => function (Builder $query) {
    $query->orderBy('published_date', 'asc');
}]);
```

---

- 只在**尚未載入時**，才載入：

```php
// 只在尚未載入時才載入
$book = Book::first();
$book->loadMissing('author');  // 如果已經載入過，就不會重複查詢
```

---

- **一般 Lazy Loading**

```php
$books = Book::all();

// 每本書的第一次存取都是 lazy loading
echo $books[0]->author->name;  // 查詢 author
echo $books[1]->author->name;  // 查詢 author  
echo $books[2]->author->name;  // 查詢 author
// 每次都查詢！

// 用 load() 避免 lazy loading
$books->load('author');        // 批次載入所有 author
echo $books[0]->author->name;  // 不查詢
echo $books[1]->author->name;  // 不查詢
echo $books[2]->author->name;  // 不查詢
```

---

- **模型內建的關聯快取(避免重複查詢的快取機制)**

```php
$book = Book::first();

// 關聯快取
$author1 = $book->author;    // 第1次：查詢資料庫，結果儲存在模型內部
                             // Lazy loading：查詢並快取
$author2 = $book->author;    // 第2次：直接從模型內部取得，不查詢資料庫
                             // 快取命中：不查詢

$book->load('author');       // ❌ 完全無用！
//                              因為 author 關聯已經載入了
//                              load() 檢查到已載入，直接跳過

$author3 = $book->author;    // 還是使用第1次的快取資料

// 強制重新載入
$book = $book->fresh(['author']); // 重新查詢
$author4 = $book->author;         // 使用新資料

```

---

- **loadMissing() 智慧載入，進行重複調用保護**

```php
$book = Book::first();

$book->loadMissing('author');  // 第1次：檢查發現沒有載入，所以執行查詢
$book->loadMissing('author');  // 第2次：檢查發現已經載入，所以跳過查詢
$author = $book->author;       // 使用已載入的資料
```

---

### 12.10 *多型關聯的延遲預載入*

- 取得**主模型後**，針對 `morphTo` 關聯載入**不同巢狀關聯**：

```php
// 強調「延遲」適合：重點是「先取模型，後決定載入」
$activities = ActivityFeed::all();  // 先取得
    $activities->loadMorph(...);    // 延遲決定是否載入

$activities = ActivityFeed::with('parentable')  // 1. 預載入 parentable
    ->get()                                     // 2. 執行查詢，取得 Collection
    // 強調「條件」適合：重點是「不同類型載入不同關聯」
    ->loadMorph('parentable', [                 // 3. 對 Collection 中的每個模型
        Event::class => ['calendar'],           //    根據 parentable 類型
        Photo::class => ['tags'],               //    載入額外的關聯
        Post::class => ['author'],
    ]);

// 這樣會出錯！loadMorph 不是查詢建構器方法
$activities = ActivityFeed::with('parentable')
    ->loadMorph('parentable', [...])  // ❌ Error: Method not found
    ->get();
```

---

### 12.11 *自動 eager loading*（beta）

- **全域**啟用自動 `eager loading`：

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 啟用自動預載入關聯功能
        Model::automaticallyEagerLoadRelationships();
        //    ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
        //    Laravel 10+ 的功能，自動偵測並預載入關聯
        //    避免 N+1 查詢問題
    }
}
```

---

- 針對**單一** `collection` 啟用(`集合層級／Collection Level`)：

```php
$users = User::where('vip', true)->get();
return $users->withRelationshipAutoloading();
```

---

- **不同層級**

```php
// 全域層級 (Global Level):
// 影響整個應用程式
Model::automaticallyEagerLoadRelationships();
// 所有的模型查詢都會自動載入關聯

// 查詢層級 (Query Level):
// 影響單次查詢
User::with('posts')->where('vip', true)->get();
// 只有這次查詢會預載入 posts

// 集合層級 (Collection Level):
// 影響已經取得的資料集合
$users = User::where('vip', true)->get();  // ← 這是一個集合
return $users->withRelationshipAutoloading();     // ← 對這個集合啟用功能
//     ^^^^^^ 只有這個特定的 $users 集合受影響
```

---

- **集合層級影響範圍**

```php
// 檔案：UserController.php
public function showVipUsers()
{
    $users = User::where('vip', true)->get();
    $users->withRelationshipAutoloading();  // 只影響這個 $users
    
    return $users;
}

public function showAllUsers()
{
    $allUsers = User::all();  // 這個 $allUsers 不受影響
    // $allUsers 沒有自動載入功能
    
    return $allUsers;
}
```

```php
// 檔案：PostController.php
public function index()
{
    $users = User::where('active', true)->get();
    // 這個 $users 也不受影響，沒有自動載入功能
    
    foreach ($users as $user) {
        echo $user->posts->count();  // 可能造成 N+1 問題
    }
}
```

---

- **集合層級可傳遞延續**

```php
// Controller
public function index()
{
    $users = User::where('vip', true)->get()
                ->withRelationshipAutoloading();  // ✅ 啟用
    
    $this->sendToService($users);
}

// Service  
public function sendToService($users)
{
    // ✅ 仍然有自動載入功能
    $this->sendToRepository($users);
}

// Repository
public function sendToRepository($users) 
{
    // ✅ 仍然有自動載入功能
    return view('users.index', compact('users'));
}

// Blade 視圖
@foreach($users as $user)
    {{ $user->posts->count() }}  // ✅ 仍然會自動載入
@endforeach
```

---

- **集合層級可能失敗**

```php
// 序列化
$users = User::where('vip', true)->get()
            ->withRelationshipAutoloading();
$serialized = serialize($users);

// 反序列化
$unserialized = unserialize($serialized);
// ❌ 自動載入功能可能失效

$users = User::where('vip', true)->get()
            ->withRelationshipAutoloading();

$array = $users->toArray();  // ❌ 轉成陣列，失去物件特性
```
---

### 12.12 *禁止 lazy loading*

- **全域** 禁止 `lazy loading`，建議於**非 production 環境**啟用：

```php
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    Model::preventLazyLoading(! $this->app->isProduction());
}
```

---

- **偵測**：_程式碼中哪個地方正在使用延遲載入_

```php
/**
 * 設定延遲載入違規的處理方式
 * 當發生 N+1 查詢問題時的監控和記錄機制
 */
Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
    // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ 
    // 設定當偵測到延遲載入違規時要執行的回調函數
    
    $class = $model::class;
    //       ^^^^^^^^^^^^^ 取得觸發違規的模型類別名稱
    
    info("Attempted to lazy load [{$relation}] on model [{$class}].");
    //   ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    //   記錄到 log 檔案：嘗試對某個模型進行延遲載入某個關聯
    //   例如：Attempted to lazy load [posts] on model [App\Models\User].
});

// 使用場景：
// 1. 開發階段偵測 N+1 問題
// 2. 生產環境監控效能問題
// 3. 幫助開發者找出需要預載入的關聯

// 搭配其他設定使用：
// Model::preventLazyLoading();  // 完全禁止延遲載入
// Model::preventLazyLoading(!app()->isProduction());  // 只在非生產環境禁止

// 觸發情況範例：
$users = User::all(); // 沒有預載入 posts
foreach ($users as $user) {
    echo $user->posts->count(); // ← 這裡會觸發違規處理器
}
// 
// Log 輸出：
// Attempted to lazy load [posts] on model [App\Models\User].
```

---

- **開發環境：`嚴格模式`**

```php
// AppServiceProvider::boot()
if (app()->environment('local')) {
    // 開發時禁止延遲載入，強制使用預載入
    Model::preventLazyLoading();
    
    Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
        throw new \Exception("請使用 with('{$relation}') 預載入！");
    });
}
```

---

- **生產環境：`監控模式`**

```php
if (app()->environment('production')) {
    // 生產環境允許延遲載入，但記錄問題
    Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
        Log::warning("效能問題: {$model::class} lazy loading {$relation}");
    });
}
```

---

- **實務建議**

```php
// 階段1：開發初期 - 注重功能實現，使用預設行為
$user->posts->count();

// 階段2：效能優化 - 發現效能問題時，啟用偵測
Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
    info("需要優化: {$relation}");
});

// 階段3：效能優化 - 根據偵測結果，添加預載入
User::with('posts')->get();
```

---

- **早期 Laravel (`便利優先`)**

```php
// 便利性：Eloquent 讓新手容易上手
// 只提供 Lazy Loading，開發方便
$user = User::first();
echo $user->posts->count();  // 簡單直覺，自動載入

// 效能問題：但在迴圈中就糟糕了
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->count();  // N+1 查詢問題！
}
```

---

- **現代 Laravel 效能意識＋實務建議**

```php
// 階段1：開發初期 - 注重功能實現，使用預設行為
$user->posts->count();

// 階段2：效能優化 - 發現效能問題時，啟用偵測
Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
    info("需要優化: {$relation}");
});

// 階段3：效能優化 - 根據偵測結果，添加預載入
User::with('posts')->get();
```

---

## 13. **Inserting and Updating Related Models**

### 13.1 *save / saveMany*

- 透過關聯的 `save` 方法**新增子模型**，會**自動設定外鍵**：

```php
use App\Models\Comment;
use App\Models\Post;

$comment = new Comment(['message' => 'A new comment.']);
$post = Post::find(1);
$post->comments()->save($comment);
```

---

- **新增多筆**：

```php
$post = Post::find(1);
$post->comments()->saveMany([
    new Comment(['message' => 'A new comment.']),
    new Comment(['message' => 'Another new comment.']),
]);
```

---

- 若需**即時取得最新關聯**，請呼叫 `refresh()` 重新載入：

```php
$post->comments()->save($comment);
$post->refresh();
$post->comments;
```

---

### 13.2 *push / pushQuietly*

- `push` 會**遞迴儲存**模型及所有已載入的關聯：

```php
$post = Post::find(1);
//      ^^^^^^^^^^^^^ 取得 ID 為 1 的文章

$post->comments[0]->message = 'Message';
//    ^^^^^^^^^^^ 存取第一個評論（會觸發 lazy loading 載入 comments 關聯）
//                ^^^^^^^ 修改評論的 message 欄位

$post->comments[0]->author->name = 'Author Name';
//                 ^^^^^^ 存取評論的作者（會觸發 lazy loading 載入 author 關聯）
//                        ^^^^ 修改作者的 name 欄位

$post->push();
//    ^^^^^^ 批次推送：儲存 post 本身及所有相關的關聯模型變更
//           等同於依序執行：
//           - $post->save()
//           - $post->comments[0]->save()  
//           - $post->comments[0]->author->save()
```

---

- `pushQuietly` **不會觸發事件**：

```php
$post->pushQuietly();
```

---

### 13.3 *create / createMany / createQuietly*

- `create` 直接用**陣列**建立**關聯模型**：

```php
$post = Post::find(1);

// 這裡的「關聯模型」是指：
// Post 模型的關聯對象 → Comment 模型
// 不是建立「關聯本身」，而是建立「關聯指向的模型」
$comment = $post->comments()->create([
            //    ^^^^^^^^^ 這是關聯方法
            //                ^^^^^^ 建立 Comment 模型（不是建立關聯）
    'message' => 'A new comment.',
]);
//                ^^^^^^^^^^^^^^^ 在 comments 表中新增記錄
//   ^^^^^^^ 這是 comments 表的 message 欄位
```
- `create()` **只能建立一筆記錄**，要建立**多**筆需要用 `createMany()`！

---

- **建立多筆**：

```php
$post = Post::find(1);
$post->comments()->createMany([
    ['message' => 'A new comment.'],
    ['message' => 'Another new comment.'],
]);
```

---

- **靜默建立**（不觸發事件）：

```php
$user = User::find(1);
$user->posts()->createQuietly([
    'title' => 'Post title.',
]);
$user->posts()->createManyQuietly([
    ['title' => 'First post.'],
    ['title' => 'Second post.'],
]);
```

---

- 亦可用 `findOrNew`、
        `firstOrNew`、
        `firstOrCreate`、
        `updateOrCreate` 等方法。

- 使用 `create` 前請確認 `mass assignment` 設定。

---

### 13.4 *Belongs To：associate / dissociate*

- 指定**子模型的父模型**：

```php
use App\Models\Account;

$account = Account::find(10);
//         ^^^^^^^^^^^^^^^^^ 取得 ID 為 10 的 Account 模型

$user->account()->associate($account);
//    ^^^^^^^^^^  ^^^^^^^^^ ^^^^^^^^^
//    關聯方法      綁定方法   要綁定的模型
//    
//    將 $user 與 $account 建立關聯關係
//    實際上是設定 $user->account_id = 10
//    
//    associate() 的作用：
//    1. 用於 BelongsTo 關聯的綁定操作
//    2. 自動取得 $account->id 並設定到 $user->account_id
//    3. 比手動設定外鍵更安全，有型別檢查
//    4. 語意清楚，明確表達「建立關聯」的意圖
//    
//    等同於：$user->account_id = $account->id;
//    但更安全且具有更好的可讀性

$user->save();
//    ^^^^^^ 儲存變更到資料庫
//           執行 UPDATE users SET account_id = 10 WHERE id = ?
//           
//           注意：associate() 只是在記憶體中設定關聯
//           必須呼叫 save() 才會真正寫入資料庫
//           
//           如果不呼叫 save()，關聯變更會在請求結束後消失
```

```php
// 必須先在 User 模型中定義關聯
class User extends Model 
{
    public function account(): BelongsTo  // ← 這個關聯定義是必須的！
    {
        return $this->belongsTo(Account::class);
    }
}

// 然後才能使用 associate()
$user->account()->associate($account);
//    ^^^^^^^^^^ 這裡呼叫的是上面定義的關聯方法
```


---

- **解除**父模型：

```php
$user->account()->dissociate();
//    ^^^^^^^^^^  ^^^^^^^^^^^
//    關聯方法     解除綁定方法
//    
//    解除 $user 與 account 的關聯關係
//    實際上是設定 $user->account_id = null
//    
//    dissociate() 的作用：
//    1. 用於 BelongsTo 關聯的解除操作
//    2. 將外鍵設定為 null，切斷關聯
//    3. 是 associate() 的相反操作
//    4. 不會刪除 Account 模型，只是解除連結
//    
//    等同於：$user->account_id = null;
//    但語意更清楚，表達「解除關聯」的意圖

$user->save();
//    ^^^^^^ 儲存變更到資料庫
//           執行 UPDATE users SET account_id = NULL WHERE id = ?
//           
//           解除關聯後：
//           - $user->account 會回傳 null
//           - 原本的 Account 模型仍然存在，沒有被刪除
//           - 只是這個用戶不再屬於任何帳戶
```

---

### 13.5 *Many to Many：attach / detach / sync / toggle / updateExistingPivot*

- **中間表**方法群：

- `attach/detach`：

```php
$user = User::find(1);

$user->roles()->attach($roleId);
//              ^^^^^^ 附加角色到用戶（多對多關聯）
//                     在中間表新增一筆記錄

$user->roles()->attach($roleId, ['expires' => $expires]);
//                              ^^^^^^^^^^^^^^^^^^^^^^^ 附加角色並設定額外欄位
//                                                      在中間表新增記錄和 expires 資料

$user->roles()->detach($roleId);
//              ^^^^^^ 移除特定角色
//                     從中間表刪除指定記錄

$user->roles()->detach();
//              ^^^^^^ 移除所有角色
//                     清空該用戶的所有角色關聯

$user->roles()->detach([1, 2, 3]);
//              ^^^^^^ ^^^^^^^^^^^ 批次移除多個角色
//                     從中間表刪除指定的多筆記錄

$user->roles()->attach([
    1 => ['expires' => $expires],
    2 => ['expires' => $expires],
]);
//              ^^^^^^ 批次附加多個角色並設定額外欄位
//                     一次新增多筆記錄到中間表
```

---

- `sync/syncWithPivotValues/syncWithoutDetaching`：

```php
$user->roles()->sync([1, 2, 3]);
//              ^^^^ ^^^^^^^^^^^ 同步角色：只保留指定的角色
//                               移除其他角色，確保用戶只有這 3 個角色

$user->roles()->sync([1 => ['expires' => true], 2, 3]);
//              ^^^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ 同步角色並設定額外欄位
//                   角色1有額外資料，角色2、3沒有額外資料
//                   最終用戶只有這 3 個角色

$user->roles()->syncWithPivotValues([1, 2, 3], ['active' => true]);
//              ^^^^^^^^^^^^^^^^^^^ ^^^^^^^^^^^ ^^^^^^^^^^^^^^^^^^^ 同步角色並為所有角色設定相同的額外欄位
//                                               所有指定角色都會有 active = true

$user->roles()->syncWithoutDetaching([1, 2, 3]);
//              ^^^^^^^^^^^^^^^^^^^ ^^^^^^^^^^^ 只新增不移除的同步
//                                               確保用戶有這些角色，但保留其他現有角色
```

---

- `toggle`：

```php
$user->roles()->toggle([1, 2, 3]);
//              ^^^^^^ ^^^^^^^^^^^ 切換角色狀態
//                                 如果用戶有這個角色就移除，沒有就新增
//                                 對每個角色進行「開關」操作

$user->roles()->toggle([
    1 => ['expires' => true],
    2 => ['expires' => true],
]);
//              ^^^^^^ 切換角色並設定額外欄位
//                     如果角色不存在就新增並設定 expires
//                     如果角色存在就移除

// 假設用戶目前有角色 [1, 3]
$user->roles()->toggle([1, 2, 3]);

// 結果：
// 角色1：存在 → 移除 ❌
// 角色2：不存在 → 新增 ✅  
// 角色3：存在 → 移除 ❌
// 最終用戶只有角色 [2]
```

---

- **更新 pivot table**：

```php
$user = User::find(1);
$user->roles()->updateExistingPivot($roleId, [
    'active' => false,
]);
//               ^^^^^^^^^^^^^^^^^^^ ^^^^^^^ ^^^^^^^^^^^^^^^^^^
//               更新現有中間表記錄   角色ID   要更新的額外欄位
//          
//               更新中間表中已存在記錄的 pivot 欄位
//               只更新額外欄位（如 active, expires 等）
//               不會新增或刪除關聯，只修改現有關聯的屬性
//          
//                實際執行：
//               UPDATE user_role 
//               SET active = false 
//               WHERE user_id = 1 AND role_id = $roleId
```

---

### 13.6 *Touching Parent Timestamps*

- Laravel 魔術屬性，定義要「**觸碰**」的關聯：`protected $touches = ['post']`;

- **子模型** 更新時，自動更新 **父模型** 的 `updated_at`：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    /**
     * 需自動更新時間戳的關聯
     * 當 Comment 被建立、更新或刪除時，會自動更新相關聯模型的 updated_at
     *
     * @var array
     */
    protected $touches = ['post'];
    //         ^^^^^^^   ^^^^^^^^
    //          Laravel 魔術屬性，定義要「觸碰」的關聯
    //                   當此模型變更時，post 關聯的 updated_at 會自動更新

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
        // 定義與 Post 的多對一關聯
        // comments.post_id → posts.id
    }
}

// 實際效果：
   $comment = Comment::create(['message' => 'New comment', 'post_id' => 1]);
// 
// 執行的 SQL：
// 1. INSERT INTO comments (message, post_id, created_at, updated_at) VALUES (...)
// 2. UPDATE posts SET updated_at = NOW() WHERE id = 1  ← 自動執行！
//
// $comment->update(['message' => 'Updated comment']);
// 
// 執行的 SQL：
// 1. UPDATE comments SET message = 'Updated comment', updated_at = NOW() WHERE id = ?
// 2. UPDATE posts SET updated_at = NOW() WHERE id = 1  ← 自動執行！
//
// $comment->delete();
//
// 執行的 SQL：
// 1. DELETE FROM comments WHERE id = ?
// 2. UPDATE posts SET updated_at = NOW() WHERE id = 1  ← 自動執行！
```

---

- 僅使用 `save` 方法時，會**自動更新父模型 `timestamp`**。
