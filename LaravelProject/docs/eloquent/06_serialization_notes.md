#  *Laravel Eloquent: Serialization 筆記*

---

## 1. **簡介**

Eloquent 提供便捷方法 __將模型與關聯轉為`陣列`或 `JSON`__，並可細緻控制序列化時包含哪些屬性。

- 進階 `JSON 序列化 `建議參考 `API Resource` 章節。

---

## 2. **模型與集合序列化**

### 2.1 *序列化為陣列*

- `toArray()` 會**遞迴轉換**所有屬性與已載入的關聯（包含`巢狀關聯`）：

```php
use App\Models\User;

$user = User::with('roles')->first();
return $user->toArray();
```

---

- **只轉換`屬性`**（不含關聯）：

```php
$user = User::first(); // 單一模型
return $user->attributesToArray();
// 取得模型的原始屬性陣列（不含關聯、accessor、cast後的值）
// 回傳格式：['id' => 1, 'name' => 'Alice', ...]
```

---

- **集合**也可直接 `toArray`：

```php
$users = User::all(); // 集合（Collection）
return $users->toArray();
```

---

### 2.2 *序列化為 JSON*

- `toJson()` 會**遞迴轉換**所有屬性與關聯：

```php
$user = User::find(1);

// 直接轉成 JSON 字串（預設格式）
return $user->toJson();

// 轉成 JSON 字串，並用漂亮的格式（縮排、換行）
return $user->toJson(JSON_PRETTY_PRINT);
```

---

- 也可**直接轉型**為`字串`：

```php
return (string) User::find(1);
```

---

- `路由/控制器` 直接 __回傳__ *模型* 或 *集合*，Laravel 會自動 __序列化為 JSON__：

```php
Route::get('/users', function () {
    return User::all();
});
```

---

- __關聯__ 會自動包含於 JSON，且 *關聯名稱* 會自動轉為 `snake_case`。

```php
/**
 * 關聯是指 Eloquent 模型的關聯資料，例如 hasMany、belongsTo、hasOne、belongsToMany 等。
 * 
 * 例如 User 有 posts 關聯（hasMany），
 * 當你 return User::all() 時，若有用 with('posts') 預載入，
 * 回傳的 JSON 會自動包含 posts 欄位，且名稱會自動轉成 snake_case（如 posts_comments）。
 *
 * 範例：
 * User::with('posts')->get();
 * 回傳結果：
 * [
 *   {
 *     "id": 1,
 *     "name": "Alice",
 *     "posts": [ ... ] // 這就是關聯資料
 *   }
 * ]
 */
 ```

---

## 3. **隱藏/顯示屬性**

- 用 `$hidden` 屬性 _隱藏欄位_（如`密碼`）：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 序列化時要隱藏的屬性或關聯。
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',    // 隱藏密碼屬性
        'posts',       // 隱藏 posts 關聯（關聯方法名稱）
        // 只要把關聯方法名稱加進來，序列化時就不會出現在 JSON
    ];
}
```

- *隱藏關聯*：將 __關聯方法名稱__ 加入 `$hidden`。

<!-- 如果你不想讓某個關聯（例如 posts）在模型序列化（toArray()、toJson()）時被回傳，
     只要把關聯方法名稱（如 'posts'）加入模型的 $hidden 屬性即可。 -->

```php
protected $hidden = ['posts'];`

```
---

- 用 `$visible` 屬性設定「*白名單*」：

- *白名單*：__只允許出現在`序列化`（toArray/toJson）結果中的欄位或關聯，其他都會被隱藏__。

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 序列化時要顯示的屬性。
     *
     * @var array
     */
    protected $visible = ['first_name', 'last_name'];
}
```

---

### 3.1 *臨時調整可見/隱藏屬性*

- `makeVisible`：**臨時顯示**隱藏屬性。

```php
// 只要有 Eloquent 模型實例（如 $user），就可用。
$user = User::find(1);

// 假設 'email' 欄位原本在 $hidden 裡，被隱藏
// 用 makeVisible('email') 可以暫時顯示 email 欄位
return $user->makeVisible('email')->toArray();

// 結果會包含 email 欄位，即使它原本被隱藏
{
  "id": 1,
  "name": "Alice",
  "email": "alice@example.com"
  // ...其他欄位...
}
```

---

- `makeHidden`：**臨時隱藏**屬性。

```php
return $user->makeHidden('attribute')->toArray();
```

---

- `setVisible`/`setHidden`：**臨時覆蓋**所有可見/隱藏屬性。

```php
// 動態設定要顯示的欄位（白名單）
return $user->setVisible(['id', 'name'])->toArray();
// 只會回傳 id 和 name 欄位

// 動態設定要隱藏的欄位（黑名單）
return $user->setHidden(['email', 'password', 'remember_token'])->toArray();
// email、password、remember_token 欄位不會出現在回傳結果
```

---

## 4. **附加屬性**（Appends）

- 可將 *非資料表欄位* 的 `accessor` 加入序列化：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 判斷是否為管理員。
     * 定義 accessor 後，若要讓 is_admin 欄位自動加入序列化結果，
     * 必須把 'is_admin' 加入 $appends 屬性。
     * 如果你有寫 protected $appends = ['is_admin'];，
     * is_admin 這個 accessor 會自動出現在 toArray() 或 toJson() 的結果裡。
     * 
     * 如果你沒寫 $appends，
     * is_admin 只會在你直接存取 $user->is_admin 時有效，
     * 但不會自動出現在序列化結果。
     */
    protected $appends = ['is_admin'];

    protected function isAdmin(): Attribute
    {
        return new Attribute(
            get: fn () => 'yes',
        );
    }
}

// 範例：
// $user = User::first();
// return $user->toArray();
// 回傳結果會包含 'is_admin' 欄位，即使資料表沒有這個欄位
```

---

- 加入 `$appends` 屬性：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 序列化時要附加的 accessor。
     *
     * @var array
     */
    protected $appends = ['is_admin'];
    // 定義在 $appends 的 accessor 會自動加入序列化結果
    // 但仍會受 $visible / $hidden 控制，可動態顯示或隱藏
}
```

- 會 *同時* 受 `$visible`/`$hidden` 控制。

---

### 4.1 *執行時動態附加*

- `append`/`setAppends`：

```php
// 動態加入 accessor 欄位到序列化結果

return $user->append('is_admin')->toArray();
// 只這次序列化時加入 'is_admin' 欄位

return $user->setAppends(['is_admin'])->toArray();
// 設定要序列化的 accessor 欄位（可一次設定多個），只影響這次序列化
```

---

## 5. **日期序列化**

- *全域*自訂日期格式：

```php
protected function serializeDate(DateTimeInterface $date): string
{
    return $date->format('Y-m-d');
}
```

---

- *單一屬性* 自訂格式：

```php
protected function casts(): array
{
    return [
        'birthday' => 'date:Y-m-d',
        'joined_at' => 'datetime:Y-m-d H:00',
    ];
} 
```