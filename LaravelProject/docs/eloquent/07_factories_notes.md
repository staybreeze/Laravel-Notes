# 1. *Laravel Eloquent: Factories 筆記*

---

## 1.1 **簡介**

Eloquent Factory 讓你在測試或資料填充時，快速產生模型`假資料`。可定義每個模型的預設屬性、狀態、關聯等，並支援 `Faker` 產生各種隨機資料。

---

## 1.2 **範例：UserFactory**

`database/factories/UserFactory.php` 範例：

```php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * 當前密碼快取。
     */
    protected static ?string $password;

    /**
     * 預設屬性。
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * 未驗證 email 狀態。
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
```

- `definition()` 回傳 *預設屬性* 陣列。
- 可用 `fake()` 產生 *隨機資料*（Faker）。
- 可用 `state()` 定義資料的 *狀態*（`屬性值`，如「__未驗證__」）。

---

## 1.3 **建立 Factory**

- Artisan 指令：

```bash
php artisan make:factory PostFactory
```

- 產生於 `database/factories` 目錄。

---

## 1.4 **模型與 Factory 對應規則**

- 模型需 `use HasFactory trait`，然後可用 `Model::factory()` 取得對應 `Factory`。
- 預設會尋找 `Database\Factories\{Model}Factory`。
- 若需自訂對應，覆寫模型的 `newFactory` 方法：

```php
use Database\Factories\Administration\FlightFactory;

protected static function newFactory()
{
    return FlightFactory::new();
}
```

---

- 並於 `Factory` 設定 `$model` 屬性：

```php
use App\Administration\Flight;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlightFactory extends Factory
{
    protected $model = Flight::class;
}
```

---

## 1.5 **Factory 狀態**（State）

- 可用 `state` 方法定義 *多種狀態*，並可組合使用：

```php
use Illuminate\Database\Eloquent\Factories\Factory;

public function suspended(): Factory
{
    return $this->state(function (array $attributes) {
        return [
            'account_status' => 'suspended',
        ];
    });
}
```

---

- 內建 `trashed` 狀態（*軟刪除*）：

```php
use App\Models\User;

$user = User::factory()->trashed()->create();
// create()：建立並儲存一筆使用者資料到資料庫。
// trashed()：Factory 的自訂狀態，會把這筆資料的 deleted_at 欄位設為現在時間（代表軟刪除）。
```

---

## 1.6 **Factory Callbacks**

- 可於 `configure 方法` 註冊 `afterMaking/afterCreating` callback：

```php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * 可在建立模型前後執行自訂邏輯
     */
    public function configure(): static
    {
        return $this->afterMaking(function (User $user) {
            // 建立模型但尚未儲存時執行
            // 例如：$user->profile = new Profile();

            // 模型建立但還沒存進資料庫時執行（可設定屬性、關聯等）
            // 例如：預設 profile 屬性（但不會存到資料庫）
            $user->profile = new \App\Models\Profile([
                'bio' => '暫存的個人簡介',
            ]);
        })->afterCreating(function (User $user) {
            // 模型已經存進資料庫後執行（可建立關聯、額外資料等）
            // 模型已儲存到資料庫後執行
            // 例如：$user->roles()->attach($roleId);

            // 例：建立關聯資料
            // 使用者建立後，自動建立一個 profile 關聯
            $user->profile()->create([
                'bio' => '這是自動產生的個人簡介',
            ]);

            // 例：附加角色
            $user->roles()->attach([1, 2]); // 假設角色 id 1, 2
        });
    }
    // ...
}
```

---

- 也可於 `state 方法` 內註冊 callback：

```php
public function suspended(): Factory
{
    return $this->state(function (array $attributes) {
        return [
            'account_status' => 'suspended',
        ];
    })->afterMaking(function (User $user) {
        // ...
    })->afterCreating(function (User $user) {
        // ...
    });
}
```

---

# 2. *Creating Models Using Factories*

## 2.1 **建立模型實例**

- 使用 `HasFactory` trait 後，可用 `Model::factory()` 取得 factory 實例。
- `make()`：*建立模型物件，但不儲存至資料庫*。 只在 _記憶體中_ 建立，不會存到資料庫

```php
use App\Models\User;

$user = User::factory()->make();
// 建立一個 User 實例（只在記憶體中，不會存到資料庫）
// $user 是尚未儲存的模型物件
```

---

- 建立*多筆*：

```php
$users = User::factory()->count(3)->make();
// 建立 3 個 User 實例（只在記憶體中，不會存到資料庫）
// $users 是一個 Collection，裡面有 3 個尚未儲存的 User 物件
```

---

## 2.2 **套用狀態**（States）

- 可`直接串接`狀態方法：

```php
// 可直接串接狀態方法：
// 例如 suspended() 是 Factory 自訂的狀態方法
$users = User::factory()->count(5)->suspended()->make();
// 建立 5 個 suspended 狀態的 User 實例（只在記憶體中，不會存到資料庫）
// 可用於測試或 seeder，快速產生特定狀態的資料
```

---

## 2.3 **覆寫屬性**

- 直接於 `make/create` 傳入陣列*覆寫預設屬性*：

```php
$user = User::factory()->make([
    'name' => 'Abigail Otwell',
]);
// 建立一個 name 為 'Abigail Otwell' 的 User 實例（只在記憶體中，不會存到資料庫）
// $user->name 會是 'Abigail Otwell'
// 如果你沒有用 make(['name' => 'Abigail Otwell']) 指定 name，
// 那麼 $user = User::factory()->make();
// 會使用 Factory 預設的隨機資料（通常是 Faker 產生的名字），
// $user->name 可能是 'John Doe' 或 'Jane Smith' 等隨機值。
```

---

- 或用 `state` 方法：

```php
$user = User::factory()->state([
    'name' => 'Abigail Otwell',
])->make();
```

- `Factory` 建立模型時 會*自動關閉* `mass assignment 保護`。

---

## 2.4 **儲存模型**

- `create()`：*建立並儲存* 至資料庫。

```php
$user = User::factory()->create();
$users = User::factory()->count(3)->create();
```

---

- *覆寫屬性*：

```php
$user = User::factory()->create([
    'name' => 'Abigail',
]);
```

---

## 2.5 **Sequences**（序列狀態）

- 可用 `Sequence` *交錯產生* 不同屬性：

```php
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;

// 建立 10 筆 User 資料，admin 欄位依序為 'Y', 'N', 'Y', 'N', ...
$users = User::factory()
    ->count(10)
    ->state(new Sequence(
        ['admin' => 'Y'],
        ['admin' => 'N'],
    ))
    ->create();
// 每筆資料會依序套用 Sequence 狀態，並存進資料庫
```

---

- `Sequence` 也可用 `closure`，並可取得 `$index/$count`：

```php
$users = User::factory()
    ->count(10)
    ->sequence(fn (Sequence $sequence) => [
        'name' => 'Name ' . $sequence->index, // $sequence->index 代表目前第幾筆（從 0 開始）
        // $sequence->count 代表總共要產生幾筆
    ])
    ->create();
// 這樣每筆 User 的 name 會依序是 Name 0, Name 1, ..., Name 9
```

---

- 也可用 `sequence` 方法簡化：

```php
$users = User::factory()
    ->count(2)
    // 你可以直接在 sequence() 方法裡傳入多個陣列，
    // 不用額外建立 Sequence 物件或寫 closure，
    // 讓程式碼更短、更直覺。
    ->sequence(
        ['name' => 'First User'],
        ['name' => 'Second User'],
    )
    ->create();
```

---

## 2.6 **Factory Relationships**

### 2.6.1 *Has Many 關聯*

- 用 `has 方法` 建立 `hasMany` 關聯：

```php
use App\Models\Post;
use App\Models\User;

// 為什麼 factory 要特別用 has 方法？不是 use App\Models\Post; use App\Models\User; 都寫好了嗎？
// -------------------------------------------------------------
// use 只是引入模型類別，讓你能在程式裡使用 User 和 Post。
// Factory 預設只會建立主模型（如 User），不會自動建立關聯資料（如 Post）。
// 如果你想要主模型同時擁有關聯資料（如 User 有多個 Post），
// 就要用 has() 來指定要建立多少關聯資料，這樣測試或 seeder 時才會自動產生完整的關聯結構。
// -------------------------------------------------------------

// 建立一個 User，同時自動建立 3 筆屬於這個 User 的 Post（hasMany 關聯）
$user = User::factory()
      ->has(Post::factory()->count(3))
      ->create();
```

---

- **明確指定**關聯名稱：

```php
// 建立一個 User，同時自動建立 3 筆 posts 關聯（hasMany），並指定關聯名稱為 'posts'
$user = User::factory()
    ->has(Post::factory()->count(3), 'posts')
    ->create();
// 這樣建立的 User 會自動擁有 3 筆 posts 關聯資料
```


- **差異說明**：
 
```php
$user = User::factory()
      ->has(Post::factory()->count(3))
      ->create();
      // 這會自動建立 3 筆「預設關聯名稱」的 Post（通常是 User 模型裡定義的 posts 關聯）。
  
$user = User::factory()
      ->has(Post::factory()->count(3), 'posts')
      ->create();
      // 這則是「明確指定」關聯名稱為 'posts'，適用於模型有多個 hasMany 關聯時，
     // 或你想要建立特定名稱的關聯資料。
``` 
- **結論**：
  - 如果你的 `User 模型` __posts 關聯名稱__ 就是 `'posts'`，兩者效果一樣。
  - 如果 _有多個關聯或關聯名稱不同_ ，第二種寫法可以指定要建立哪個關聯。

---

- 關聯可套用 `state` 或 `closure`：

```php
$user = User::factory()
    ->has(
        Post::factory()
            ->count(3)
            // 建立每筆 Post 時，根據 User 的 type 屬性設定 user_type 欄位
            ->state(function (array $attributes, User $user) {
                return ['user_type' => $user->type];
            })
        )
    ->create();
// 建立一個 User，同時建立 3 筆 Post，且每筆 Post 的 user_type 會等於該 User 的 type
```

---

- `Magic method` 快速建立：

- `hasPosts(N)` 會**自動建立 N 筆 posts 關聯資料**。

```php
$user = User::factory()->hasPosts(3)->create();
// 建立一個 User，並自動建立 3 筆 posts 關聯

$user = User::factory()->hasPosts(3, ['published' => false])->create();
// 建立一個 User，並建立 3 筆 posts 關聯，且每筆 published 欄位為 false

$user = User::factory()->hasPosts(3, function (array $attributes, User $user) {
    return ['user_type' => $user->type];
})->create();
// 建立一個 User，並建立 3 筆 posts 關聯，且每筆 post 的 user_type 會等於該 User 的 type
```

---

### 2.6.2 *Belongs To 關聯*

- 用 `for 方法` 指定 **父模型**：

```php
use App\Models\Post;
use App\Models\User;

// 建立 3 筆 Post，每筆都關聯到一個 name 為 'Jessica Archer' 的 User
$posts = Post::factory()
    ->count(3)
    ->for(User::factory()->state([
        'name' => 'Jessica Archer',
    ]))
    ->create();
// 這樣會自動建立一個 User（name 為 Jessica Archer），並讓 3 筆 Post 都屬於這個 User
```

---

- **已有** 父模型時：

```php
// 已有父模型時：
// 先建立一個 User，再建立 3 筆 Post，並讓這些 Post 關聯到這個 User

$user = User::factory()->create();
$posts = Post::factory()->count(3)->for($user)->create();
// $posts 裡的每筆 Post 都會自動設定 user_id 為 $user 的 id
```

---

- _Magic method_：`forUser()`

```php
// 直接建立 3 筆 Post，並自動建立一個 name 為 'Jessica Archer' 的 User 作為關聯

$posts = Post::factory()->count(3)->forUser([
    'name' => 'Jessica Archer',
])->create();
// 每筆 Post 都會關聯到這個 User
```

---

### 2.6.3 *Many to Many 關聯*

- 用 `has/hasAttached` 建立**多對多**關聯：

```php
use App\Models\Role;
use App\Models\User;

// 用 has 建立多對多關聯：
// 建立一個 User，並自動建立 3 個 Role，並關聯起來（pivot 欄位預設）
// 用於建立多對多關聯時，只會建立關聯，不會設定 pivot（中介表）上的額外欄位。
// 例如：只建立 User 和 Role 的關聯，pivot 表只有 user_id 和 role_id。

$user = User::factory()
    ->has(Role::factory()->count(3))
    ->create();

// 用 hasAttached 建立多對多關聯，並設定 pivot 欄位：
// 建立一個 User，並自動建立 3 個 Role，關聯時 pivot 欄位 active 設為 true
// 除了建立關聯，還可以設定 pivot 表上的額外欄位（如 active、expires_at 等）。
// 例如：建立 User 和 Role 的關聯，pivot 表除了 user_id 和 role_id，還會設定 active 欄位。
$user = User::factory()
    ->hasAttached(
        Role::factory()->count(3),
        ['active' => true]
    )
    ->create();
```

---

- `hasAttached` 支援 `closure` 狀態、直接傳入 __已存在的模型__：

```php
$user = User::factory()
    ->hasAttached(
        Role::factory()
            ->count(3)
            ->state(function (array $attributes, User $user) {
                return ['name' => $user->name . ' Role'];
            }),
        ['active' => true] // 設定 pivot 欄位
    )
    ->create();

// hasAttached 也可直接傳入已存在的模型
$roles = Role::factory()->count(3)->create();
$user = User::factory()
    ->count(3)
    ->hasAttached($roles, ['active' => true]) // 關聯現有的 Role，並設定 pivot 欄位
    ->create();
```

---

- _Magic method_：`hasRoles()`

```php
// 建立一個 User，並自動建立 1 個 name 為 'Editor' 的 Role 關聯
$user = User::factory()->hasRoles(1, ['name' => 'Editor'])->create();
// $user 會有一個 roles 關聯，且該 Role 的 name 為 'Editor'
```

---

### 2.6.4 *Polymorphic 關聯*

<!-- 一般一對一和多型一對一的用法很類似，
     一般一對多和多型一對多的用法也很類似，
     只是多型關聯會多一個型別欄位（如 morph_type），
     語法和操作方式基本一致，
     差別只在資料表結構和關聯定義。 -->

- _morphMany_：`hasComments()`

```php
use App\Models\Post;

// 建立一個 Post，並自動建立 3 筆 comments 關聯資料
$post = Post::factory()->hasComments(3)->create();
// $post 會有 3 筆 comments 關聯資料
```

---

- _morphTo_：只能用 `for` 並**明確指定**關聯名稱

```php
$comments = Comment::factory()->count(3)->for(
    Post::factory(), 'commentable'
)->create();
// 建立 3 筆 Comment，並將 commentable 關聯指定為 Post
// 適用於多型關聯（morphTo），必須明確指定關聯名稱
```

---

- _morphToMany / morphedByMany_：`hasTags()`

<!-- 多型多對多和一般多對多關聯，
     在 Laravel Factory 裡都可以用 hasAttached() 來建立關聯並設定 pivot 欄位。
     hasAttached() 是通用的建立關聯方法。 -->

```php
use App\Models\Tag;
use App\Models\Video;

// 建立一個 Video，並自動建立 3 個 Tag 關聯（pivot 欄位 public 設為 true）
$videos = Video::factory()
    ->hasAttached(
        Tag::factory()->count(3),
        ['public' => true]
    )
    ->create();

// 建立一個 Video，並自動建立 3 個 public 為 true 的 Tag 關聯
$videos = Video::factory()
    ->hasTags(3, ['public' => true])
    ->create();
```

<!-- 這兩個寫法效果相同，
     都是建立一個 Video，並自動建立 3 個 Tag，
     且 pivot 欄位 public 都設為 true。

     hasAttached() 是通用的關聯建立方法，可以指定關聯模型和 pivot 欄位。
     hasTags() 是 Laravel 針對 tags 關聯自動產生的語法糖，
     本質上也是呼叫 hasAttached()，只是更簡潔。

     總結： 
     兩者都會建立 3 個 public = true 的 Tag 關聯到 Video。 -->

---

## 2.7 **Factory 內定義關聯**

- 在 factory `definition` *直接指定外鍵* 為另一 factory：

```php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

    class PostFactory extends Factory
    {
    // Factory 的 definition 方法，建立資料時 user_id 欄位會自動建立一個 User 並關聯
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),      // 自動建立並關聯一個 User
            'title' => fake()->title(),        // 隨機產生 title
            'content' => fake()->paragraph(),  // 隨機產生 content
        ];
    }
}
/**
 * 
 * 當你用這個 Factory 產生資料（例如 Post::factory()->create()），
 * 會自動建立一個新的 User，並把這個 User 的 id 設定到 user_id 欄位。
 * 
 * 產生的結果（範例）：
 * [
 *   'user_id' => 5,                // 新建立的 User 的 id
 *   'title' => '隨機標題',
 *   'content' => '隨機內容段落'
 * ]
 * 
 * 資料庫也會多一筆 User 資料，並且這筆資料會跟 Post 關聯。
 */
```

---

- 若需 *依賴其他屬性*，可用 `closure`：

```php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    // 指定這個 Factory 對應的模型
    protected $model = \App\Models\Post::class;

    public function definition(): array
    {
        return [
            // 建立 Post 時，user_id 欄位會自動建立一個 User 並關聯
            'user_id' => User::factory(),
            // user_type 欄位用 closure，$attributes 會包含目前這筆資料的所有屬性
            // 這裡可用 $attributes['user_id'] 取得剛建立的 User 的 id
            'user_type' => function (array $attributes) {
                // 根據 user_id 查詢 User，取得 type 屬性
                return User::find($attributes['user_id'])->type;
            },
            'title' => fake()->title(),
            'content' => fake()->paragraph(),
        ];
    }
}

// 說明：
// - 建立資料時會自動建立一個 User，並把 id 填入 user_id。
// - user_type 欄位會根據剛建立的 User 的 type 屬性動態設定。
// - title 和 content 為隨機產生。
```

---

## 2.8 **recycle：重複使用已存在模型**

- `recycle` 可 *讓多個關聯`共用`同一個父模型*：

```php
// recycle 用法說明：
// Airline::factory()->create() 會先建立一個 Airline 實例
// recycle(...) 讓 Ticket factory 在建立 Ticket 時，重複使用這個 Airline 作為關聯
// 不會每次都新建 Airline，所有 Ticket 都會關聯到同一個 Airline

Ticket::factory()
    ->recycle(Airline::factory()->create())
    ->create();
// 建立 Ticket 時，會重複使用已建立的 Airline 作為關聯，不會每次都新建 Airline
```

---

- 也可傳入 `collection`，會隨機選一個：

```php
$airlines = Airline::factory()->count(5)->create(); // 建立 5 筆 Airline 資料

Ticket::factory()
    ->recycle($airlines) // $airlines 是多個 Airline 實例的集合
    ->create();
// 建立 Ticket 時，會從 $airlines 集合中隨機選一個 Airline 作為關聯
``` 