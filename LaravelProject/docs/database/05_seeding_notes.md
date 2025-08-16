# *Laravel Seeding 筆記*

---

## 1. **Seeding 簡介**

Seeding (播種)，在 Laravel 中指的是 *初始化資料庫的過程* 。它用來`向資料庫中插入測試資料、預設資料或假資料`，方便開發和測試。

Laravel 內建 `seeder 類別` ，可用來*快速填充資料庫測試資料*。

所有 seeder 類別都放在 `database/seeders` 目錄。預設有一個 `DatabaseSeeder` 類別，可用 `call` 方法呼叫其他 seeder，控制執行順序。

- `seeding` 過程會*自動關閉* `mass assignment` 保護。
- Mass Assignment 保護 是 Laravel 的一種安全機制，用來防止在模型中*批量賦值*（Mass Assignment）時，意外更新或插入不應該被修改的欄位。
- Laravel 透過 `$fillable` 或 $`guarded` 屬性，限制哪些欄位可以被批量賦值。


---

## 2. **撰寫 Seeder**

```bash
php artisan make:seeder UserSeeder
```
- 產生的 seeder 會放在 `database/seeders` 目錄。

Seeder class 預設只有一個 `run` 方法，執行 `db:seed` 指令時會呼叫。

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder; // 引入 Seeder 類，所有 Seeder 都繼承自這個類
use Illuminate\Support\Facades\DB; // 引入 DB Facade，用於直接操作資料庫
use Illuminate\Support\Facades\Hash; // 引入 Hash Facade，用於加密密碼
use Illuminate\Support\Str; // 引入 Str 類，用於生成隨機字串

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // 使用 DB Facade 向 users 資料表插入一筆假資料
        DB::table('users')->insert([
            'name' => Str::random(10), // 生成一個長度為 10 的隨機字串作為使用者名稱
            'email' => Str::random(10).'@example.com', // 生成一個隨機字串並附加 @example.com 作為電子郵件
            'password' => Hash::make('password'), // 將 'password' 字串加密後存入資料庫
        ]);
    }
}
```
- `run` 方法可用 `query builder` 或 `Eloquent factories` 插入資料。
- `run` 方法可自動*注入依賴*（type-hint 會自動解析）。

---

## 3. **使用 Model Factories**

```php
use App\Models\User; // 引入 User 模型，方便使用其 Factory 和關聯方法

public function run(): void
{
    User::factory() // 使用 User 模型的 Factory 來生成假資料
        ->count(50) // 指定要生成 50 個使用者
        ->hasPosts(1) // 為每個使用者生成 1 篇關聯的 Post（假資料）
        // hasPosts() 是基於模型的關聯方法（如 posts()）生成假資料。
        // 如果是 profile，需要在模型中定義 profile() 方法，並設計好資料表的關聯欄位。
        // Factory 會自動根據模型的關聯生成假資料，前提是資料表和模型的關聯已設計好。
        ->create(); // 將生成的假資料插入資料庫
}
```
- 可用 `factory` 快速*產生大量資料*。

---

## 4. **呼叫其他 Seede**

- *call 方法*：
  - 用於在一個 Seeder 中，`執行其他 Seeder 類別`。
  - 這樣可以將大型 Seeder 拆分成多個小型 Seeder，方便管理和維護。

- *用途*：
  - 當`需要初始化多個資料表`（例如 users、posts、comments），`可以將每個資料表的 Seeder 分開，然後在主 Seeder 中依次呼叫它們`。

```php
public function run(): void
{
    $this->call([ // 使用 call 方法執行多個 Seeder 類別
        UserSeeder::class, // 執行 UserSeeder，生成使用者的假資料或初始化資料
        PostSeeder::class, // 執行 PostSeeder，生成文章的假資料或初始化資料
        CommentSeeder::class, // 執行 CommentSeeder，生成評論的假資料或初始化資料
    ]);
}
```

---

- 可用 call 方法**呼叫多個 seeder**，方便拆分大型 seeder。

```php
// UserSeeder
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // 匯入 DB facade，用於資料庫操作
use Illuminate\Support\Str;        // 匯入 Str 工具類

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 新增一筆隨機使用者資料到 users 資料表
        DB::table('users')->insert([
            'name' => Str::random(10),                  // 隨機 10 字元名稱
            'email' => Str::random(10).'@example.com',  // 隨機 email
            'password' => bcrypt('password'),           // 密碼加密
        ]);
    }
}
```

```php
// PostSeeder
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // 新增一筆文章資料到 posts 資料表
        DB::table('posts')->insert([
            'title' => 'Sample Post',                   // 文章標題
            'content' => 'This is a sample post content.', // 文章內容
            'user_id' => 1,                             // 關聯到使用者 id 1
        ]);
    }
}
```

```php
// CommentSeeder
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        // 新增一筆留言資料到 comments 資料表
        DB::table('comments')->insert([
            'content' => 'This is a sample comment.', // 留言內容
            'post_id' => 1,                           // 關聯到文章 id 1
        ]);
    }
}
```

---

## 5. **靜音 Model Events**

```php
namespace Database\Seeders; // 定義 Seeder 類別的命名空間，所有 Seeder 類別都放在 Database\Seeders 命名空間中

use Illuminate\Database\Seeder; // 引入 Seeder 類，所有 Seeder 都繼承自這個類
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // 引入 WithoutModelEvents Trait，用於避免模型事件在 Seeder 中觸發

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents; // 使用 WithoutModelEvents Trait，防止 Seeder 中的模型操作觸發事件（例如 created、updated 等）

    public function run(): void
    {
        $this->call([ // 使用 call 方法執行其他 Seeder 類別
            UserSeeder::class, // 呼叫 UserSeeder 類別，執行使用者資料的填充邏輯
        ]);
    }
}
```
- 使用 `WithoutModelEvents trait`，可讓 `seeding` 過程不 `dispatch model events`。
- 即使 call 其他 seeder 也會靜音。

---

## 6. **執行 Seeder**

```bash
php artisan db:seed
# 執行 DatabaseSeeder 類別中的 run() 方法
# DatabaseSeeder 是 Laravel 預設的主 Seeder，通常用於呼叫多個其他 Seeder（例如 UserSeeder、PostSeeder 等）
# 適合用於初始化整個資料庫，填充所有需要的假資料或預設資料

php artisan db:seed --class=UserSeeder
# 只執行 UserSeeder 類別中的 run() 方法
# 用於單獨填充 users 資料表的假資料或初始化資料
# 適合用於測試或更新特定資料表的資料，而不影響其他 Seeder

```

---

- 預設執行 `Database\Seeders\DatabaseSeeder`，可用 `--class` *指定 seeder*。

```bash
php artisan migrate:fresh --seed
# 刪除所有資料表（不管有無 prefix），並重新執行所有 migrations
# 在 migrations 完成後，執行 DatabaseSeeder 類別中的 run() 方法
# 適合用於開發或測試環境，快速重建資料庫並填充假資料或初始化資料

php artisan migrate:fresh --seed --seeder=UserSeeder
# 刪除所有資料表（不管有無 prefix），並重新執行所有 migrations
# 在 migrations 完成後，執行指定的 UserSeeder 類別中的 run() 方法
# 適合用於開發或測試環境，快速重建資料庫並只填充 users 資料表的假資料或初始化資料
```
- `migrate:fresh --seed` 會*重建資料表並執行 seeder*。
- `--seeder` 可*指定 seeder*。

---

## 7. **Production 強制執行 Seeder**

```bash
php artisan db:seed --force
```
- 生產環境下會要求確認，`--force` 可跳過提示直接執行。

---