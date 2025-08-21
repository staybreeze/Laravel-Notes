# 1. *Laravel Eloquent: API Resources 筆記*

---

## 1.1 **簡介**

API Resource 提供 `Eloquent 與 JSON 回應之間` 的 *轉換層* ，可彈性控制 __輸出欄位、關聯、格式__ 等。比起直接用 `toJson`，`Resource` 提供更細緻的 *序列化與自訂能力*。

```php
// 1. 不用 Resource，直接回傳 Eloquent 資料
Route::get('/users', function () {
    return User::all(); // 直接回傳，欄位、格式不可控
});

// 回傳結果（可能包含所有欄位，甚至敏感資料）：
/*
[
    {
        "id": 1,
        "name": "Alice",
        "email": "alice@example.com",
        "password": "hashed...",
        "created_at": "...",
        "updated_at": "..."
    },
    ...
]
*/

// 2. 用 Resource 格式化回傳
use App\Http\Resources\UserResource;

Route::get('/users', function () {
    return UserResource::collection(User::all());
});

// UserResource::collection() 不是完全等於 ResourceCollection，
// 但它會回傳一個匿名的 ResourceCollection，
// 裡面每筆資料都用 UserResource 格式化，
// 效果和自訂的 UserCollection 類似，
// 只是少了自訂 meta、links 等額外功能。

// Controller 範例
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id);

        // 回傳 Resource，格式化資料
        return new UserResource($user);
    }
}

// UserResource 範例
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // 只回傳需要的欄位，格式可自訂
        ];
    }
}

// 回傳結果（乾淨、統一、可預期）：
/*
[
    {
        "id": 1,
        "name": "Alice"
    },
    ...
]
*/
```
---

## 1.2 **產生 Resource 類別**

- 產生 *單一模型* Resource：

```bash
php artisan make:resource UserResource
```

---

- 產生 *Resource Collection*（集合）：

```bash
php artisan make:resource User --collection
# 或
php artisan make:resource UserCollection
```

---

- *Resource* 會放在 `app/Http/Resources` 目錄下，繼承 `Illuminate\Http\Resources\Json\JsonResource`。

- *Collection Resource* 也放在相同目錄下，繼承 `Illuminate\Http\Resources\Json\ResourceCollection`。

---

- *Collection Resource 跟一般集合的差異*

 - `Collection Resource` 是 **API 資源專用**，可以用一般 Collection 的方法（如 `map、filter`），同時還有自己的方法（如 `toArray、with、additional、response`）。

<!-- 
     toArray：定義主要資料格式（API 回傳內容）。
     with：加上最外層 meta 資訊。
     additional：動態加上 meta 或其他額外資料。
     response：自訂回傳的 Response 物件（如狀態碼、標頭）。 -->

 - 主要用於 *API 回傳格式化* ，支援`自訂欄位、包裝、meta 資訊`等
 - 可**自動**處理`分頁、資料包裝、附加 meta、links` 等

 - *一般集合（Collection）* 只負責 *資料操作*，不負責格式化或包裝

 ```php
 // 一般 Collection
 $users = User::all(); // 回傳 Collection，只是資料陣列

 // Collection Resource
 return new UserCollection(User::all());
 // 回傳格式化後的 JSON，支援自訂欄位、meta、分頁等
 ```
---

## 1.3 **Resource 基本用法**

- `Resource` 代表 *單一模型* ，需實作 `toArray` 方法，回傳 __要輸出的陣列__：

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 可直接用 $this 存取模型屬性：
 * 
 * 在 Resource 裡，$this 代表被包裝的 Eloquent 模型實例，
 * 所以你可以直接用 $this->屬性 取得模型的資料，
 * 例如 $this->id、$this->name、$this->email 等。
 * 
 * 不需要額外取得模型，只要用 $this 就能存取所有欄位和關聯資料。
 */
class UserResource extends JsonResource
{
    /**
     * 將資源轉為陣列。
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

---

- *回傳 Resource*：

```php
use App\Http\Resources\UserResource;
use App\Models\User;

Route::get('/user/{id}', function (string $id) {
    return new UserResource(User::findOrFail($id));
});
```

---

- 也可用 model 的 `toResource` 方法自動尋找對應 *Resource*：

```php
return User::findOrFail($id)->toResource();
```

---

## 1.4 **Resource Collection 用法**

- 回傳 *多筆* 或 *分頁* 時，建議用 `Resource::collection`：

```php
use App\Http\Resources\UserResource;
use App\Models\User;

Route::get('/users', function () {
    return UserResource::collection(User::all());
});
```
<!-- 用 UserResource::collection() 會把每筆 User 資料都用 UserResource 格式化，
回傳的結果是統一格式的多筆資料。
跟直接回傳 User::all() 相比，
可以自訂欄位、格式、隱藏敏感資料，
更適合 API 回傳需求。 -->

---

- 也可用 collection 的 `toResourceCollection` 方法自動尋找對應 `ResourceCollection：`

```php
return User::all()->toResourceCollection();
```

---

## 1.5 **自訂 Resource Collection**

- 若需自訂 `meta、links` 等，可建立 __專屬 Collection 類別__：

```bash
php artisan make:resource UserCollection
```

---

- 實作 `toArray` 方法：

```php
class UserCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection, 
            // 那 data 是原始模型資料（Eloquent Model 的 toArray() 結果），
            // 還沒經過 Resource 格式化。

            // 'data' => UserResource::collection($this->collection)，
            // 那 data 就是經過 Resource 格式化的結果。

            // 如果你用的是 UserCollection 且遵守命名規則，
            // Laravel 會自動用 UserResource 來格式化每筆資料，
            // 你不用特別寫 'data' => UserResource::collection($this->collection)，
            // 直接用 $this->collection 就會自動套用單一 Resource 格式。

            'links' => [
                'self' => 'link-value', // 自訂連結資訊，可依需求擴充
            ],
        ];
    }
}
```

---

- 回傳 *自訂* Collection：

```php
use App\Http\Resources\UserCollection;
use App\Models\User;

Route::get('/users', function () {
    return new UserCollection(User::all());
});
```

---

- 也可用 `toResourceCollection` 自動尋找：

```php
return User::all()->toResourceCollection();
```

---

## 1.6 **保留 Collection Key**

- `Resource Collection` *預設* 會 _重設 key_，可於 Resource 設定 `public $preserveKeys = true;` 來 _保留原始 key_：

```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * 是否保留 collection key。
     *
     * @var bool
     */
    public $preserveKeys = true;
}
```

---

- 使用方式：

```php
use App\Http\Resources\UserResource;
use App\Models\User;

/**
 * $preserveKeys = true 的作用
 *
 * 跟你用 UserResource::collection(User::all()->keyBy->id) 有直接關係：
 * 
 * - 當 $preserveKeys 為 true，ResourceCollection 會保留原本集合的 key（例如 id）
 * - 如果是 false，回傳的 JSON 會自動用數字索引（0, 1, 2...）
 * 
 * 你的寫法 keyBy->id 會讓集合的 key 變成使用者 id，
 * 加上 $preserveKeys = true，API 回傳的 JSON 就會以 id 為 key，
 * 物件格式：key 是你指定的（如 id）
 * 例如：
  {
    "1": {...},
    "2": {...}
  }
 * 
 * 如果沒設 preserveKeys 或設為 false，則回傳：
 * 陣列格式：key 是隱藏的數字索引(只有順序)
  [
    {...}, // id=1
    {...}, // id=2
  ]
 */

// 取得所有使用者資料，依 id 分組，並用 UserResource 集合格式化回傳
Route::get('/users', function () {
    // User::all()->keyBy->id 取得所有使用者並以 id 為 key
    // UserResource::collection(...) 轉成 ResourceCollection，格式化 API 回傳
    return UserResource::collection(User::all()->keyBy
```

---

## 1.7 **自訂 ResourceCollection 對應的 Resource**

- `ResourceCollection` 會 *自動推斷單一資源類別* （如 `UserCollection` 會用 `UserResource`）。

<!-- 如果你用 UserCollection extends ResourceCollection，
     Laravel 會自動判斷要用哪個單一 Resource（如 UserResource）來格式化每筆資料，
     你不用手動指定，
     只要命名規則正確（UserCollection 對應 UserResource），
     Laravel 會自動用 UserResource 處理集合裡的每筆 User 資料。 -->

- 可自訂 `$collects` 屬性 *指定對應* 的 Resource：

```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

// UserCollection 用來包裝多筆資料的 API 回傳格式
class UserCollection extends ResourceCollection
{
    /**
     * 指定此集合對應的 Resource。
     *
     * @var string
     */
    public $collects = Member::class; // 這個集合中的每個元素都會用 Member Resource 格式化
}
``` 

---

# 2. *Writing Resources*

## 2.1 **基本用法**

- `Resource` 只需 __將模型轉為陣列__，實作 `toArray` 方法即可：

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

---

- 回傳 `Resource`：

```php
use App\Models\User;

Route::get('/user/{id}', function (string $id) {
    return User::findOrFail($id)->toUserResource();
});
```

---

## 2.2 **關聯資料**（Relationships）

- 在 `toArray` 內可直接嵌入其他 `Resource` 或 `Resource::collection`：

```php
use App\Http\Resources\PostResource;

public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        'posts' => PostResource::collection($this->posts),
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}
// 讓 posts 關聯資料每一筆都經過 PostResource 的 toArray() 格式化，
// 確保回傳的關聯資料結構一致且符合你自訂的格式。
```

---

- 若 *只在* __已載入時包含關聯__，請參考「條件關聯」章節。

---

## 2.3 **Resource Collections**

- Resource collection 會將 *多筆模型* 轉為 __陣列__，可直接用 `toResourceCollection`：

  - 因為 API 常常要一次回傳多筆資料（例如列表），
    `Resource collection` 可以把**多筆模型** _格式化成統一的陣列格式_。
 
  - 跟單筆的差別：
    - _單筆_ 用 `Resource`（如 `new UserResource($user)`），只 __包裝一筆資料__。
    - _多筆_ 用 `Resource collection`（如 `UserResource::collection($users)`），__包裝多筆資料，回傳陣列__。

   
```php
use App\Models\User;

Route::get('/users', function () {
    return User::all()->toResourceCollection();
});
```

---

- 若需自訂 `meta`，需自訂 `ResourceCollection`：

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
```

---

- *回傳* 自訂 Collection：

```php
use App\Http\Resources\UserCollection;
use App\Models\User;

Route::get('/users', function () {
    return new UserCollection(User::all());
});
```

---

- 也可用 `toResourceCollection` 自動尋找：

```php
return User::all()->toResourceCollection();
```

---

## 2.4 **Data Wrapping**（資料包裝）

- `Resource` 回應預設會包在 `data 的 key` 下：

```json
{
    "data": [
        { "id": 1, "name": "..." },
        { "id": 2, "name": "..." }
    ]
}
```

---

- 若要 *移除* 最外層 `data`，於 `AppServiceProvider` *boot* 方法呼叫：

```php
use Illuminate\Http\Resources\Json\JsonResource;

public function boot(): void
{
    JsonResource::withoutWrapping();
}
```

- **僅影響最外層**，不會移除自訂 collection 內的 data key。

```php
[
    { "id": 1, "name": "..." },
    { "id": 2, "name": "..." }
]
```

---

- `巢狀 collection` 若都包 `data`，Laravel 會自動避免**雙層包裝**。

```php
return new UserCollection(User::with('posts')->get());

// 這裡的 with('posts') 是Eloquent 的預載入關聯（eager loading），
// 意思是查詢所有 User 時，會同時把每個 User 的 posts（文章）資料一起查出來，
// 避免 N+1 查詢問題，提升效能。

// 如果 UserCollection 和 PostCollection 都包裝成
{
  "data": [
    {
      "id": 1,
      "posts": {
        "data": [
          { "id": 10 },
          { "id": 11 }
        ]
      }
    }
  ]
}

// Laravel 會自動避免出現這種
{
  "data": [
    {
      "id": 1,
      "posts": {
        "data": {
          "data": [ ... ] // ❌ 不會出現這種雙層 data
        }
      }
    }
  ]
}

// Laravel 會幫你處理好巢狀 Resource 的 data 包裝，不會讓回傳結果出現多層重複的 data 欄位，
// 讓 API 回傳格式更乾淨、易讀
{
  "data": [
    {
      "id": 1,
      "name": "Alice",
      "posts": {
        "data": [
          { "id": 10, "title": "Post A" },
          { "id": 11, "title": "Post B" }
        ]
      }
    },
    {
      "id": 2,
      "name": "Bob",
      "posts": {
        "data": [
          { "id": 12, "title": "Post C" }
        ]
      }
    }
  ]
}
```

---

## 2.5 **分頁**（Pagination）

- Resource collection 支援 *分頁*，回傳時自動帶有 `meta 與 links`：


```php
use App\Http\Resources\UserCollection;
use App\Models\User;

Route::get('/users', function () {
    return new UserCollection(User::paginate());
    // 只會回傳「本頁」的資料，並自動包含分頁資訊（meta、links）
    // meta 會有總頁數、目前頁數、總筆數等
    // links 會有上一頁、下一頁、第一頁、最後一頁等分頁連結
});
```

---

- 也可用 `toResourceCollection`：

```php
return User::paginate()->toResourceCollection();
```

---

- *分頁回應範例*：

```json
{
    "data": [...],
    "links": { ... },
    "meta": { ... }
}
```

---

- *自訂* 分頁 `meta/links`：

```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function paginationInformation($request, $paginated, $default)
    {
         // 可自訂分頁資訊，加入自定義連結或 meta 資料
        $default['links']['custom'] = 'https://example.com';
        return $default;
    }
}
```

---

## 2.6 **條件屬性**（Conditional Attributes）

- 只在 *特定條件* 下輸出欄位：

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        'secret' => $this->when($request->user()->isAdmin(), 'secret-value'),
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}
```

---

- *第二參數* 可用 `closure`：

```php
'secret' => $this->when($request->user()->isAdmin(), function () {
    return 'secret-value';
}),
```

---

- 只在 *屬性存在* 時輸出：

```php
'name' => $this->whenHas('name'),
```

---

- 只在 *屬性非 null* 時輸出：

```php
'name' => $this->whenNotNull($this->name),
```

---

- *多個欄位* 同時條件輸出：

```php
// mergeWhen 不可用於混合字串與數字 key 的陣列。
// 也就是說，mergeWhen 只能合併純字串 key 或純數字 key 的陣列，不能同時混用。
// 如果陣列同時有字串 key 和數字 key，會導致回傳格式錯誤或資料遺失。

// 範例：
$this->mergeWhen($request->user()->isAdmin(), [
    'first-secret' => 'value',   // 字串 key
    'second-secret' => 'value',  // 字串 key
    // 不能和 [0 => 'xxx'] 這種數字 key 混用
]);
```

---

## 2.7 **條件關聯**（Conditional Relationships）

- 只在 *已載入* 時輸出關聯：

```php
use App\Http\Resources\PostResource;

'posts' => PostResource::collection($this->whenLoaded('posts')),
// 'posts' => PostResource::collection($this->whenLoaded('posts')),
// 用於 Resource，只有當模型有預先載入 posts 關聯（with('posts')）時，才會回傳 posts 欄位
// 並且 posts 會用 PostResource 集合格式化
// 範例：User::with('posts')->get()
// 沒有 with('posts') 時，posts 欄位不會出現
```

---

- *關聯計數條件* 輸出：

```php
// 'posts_count' => $this->whenCounted('posts'),
// 用於 Resource，當模型有 withCount('posts') 時，才會自動加入 posts_count 欄位
// 範例：User::withCount('posts')->get()
// 回傳結果會有 posts_count 欄位，否則不會出現

use App\Models\User;
use App\Http\Resources\UserResource;

// 查詢時加上 withCount('posts')，會自動計算每個使用者的貼文數量
$users = User::withCount('posts')->get();

// Resource 範例
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // 當查詢有 withCount('posts') 時，才會有 posts_count 欄位
            'posts_count' => $this->whenCounted('posts'),
        ];
    }
}

// 回傳結果範例：
/*
[
    {
        "id": 1,
        "name": "Alice",
        "posts_count": 5
    },
    {
        "id": 2,
        "name": "Bob",
        "posts_count": 2
    }
]
*/

// Resource 的作用是讓你可以自訂回傳格式、欄位名稱、條件顯示等，
// 例如你想要只在某些情況下才回傳 posts_count，或改欄位名稱、加 meta 資訊，
// 這時用 Resource 會更彈性、更安全。
```

---

- 其他 *聚合條件* 輸出：

```php
'words_avg' => $this->whenAggregated('posts', 'words', 'avg'), // 文章 words 欄位的平均值
'words_sum' => $this->whenAggregated('posts', 'words', 'sum'), // 文章 words 欄位的總和
'words_min' => $this->whenAggregated('posts', 'words', 'min'), // 文章 words 欄位的最小值
'words_max' => $this->whenAggregated('posts', 'words', 'max'), // 文章 words 欄位的最大值
```

---

## 2.8 **Pivot 條件輸出**

- *多對多* `pivot` 欄位條件輸出：

```php
'expires_at' => $this->whenPivotLoaded('role_user', function () {
    return $this->pivot->expires_at;
}),
// 用於 Resource，只有當多對多關聯有載入 pivot 資料（如 with('roles')），
// 且 pivot 表名為 role_user 時，才會回傳 expires_at 欄位
// 範例：User::with('roles')->get()
// roles 關聯的 pivot 欄位 expires_at 會出現在 Resource 回傳資料中
```

---

- 自訂 `pivot model`：

```php
'expires_at' => $this->whenPivotLoaded(new Membership, function () {
    return $this->pivot->expires_at;
}),
// 用於 Resource，只有當多對多關聯有載入 pivot 資料（如 with('memberships')），
// 且 pivot 關聯模型為 Membership 時，才會回傳 expires_at 欄位
// 範例：User::with('memberships')->get()
// memberships 關聯的 pivot 欄位 expires_at 會出現在 Resource 回傳資料中
```

---

- `pivot accessor` 非 `pivot` 時：

```php
'expires_at' => $this->whenPivotLoadedAs('subscription', 'role_user', function () {
    return $this->subscription->expires_at;
}),
// 用於 Resource，當多對多關聯的 pivot 資料（role_user）被載入，
// 且 pivot 關聯在模型中命名為 subscription 時，才會回傳 expires_at 欄位
// 範例：User::with('subscriptions')->get()
// subscriptions 關聯的 pivot 欄位 expires_at 會出現在 Resource 回傳資料中
```

---

## 2.9 **Meta Data**

<!-- Meta data（中繼資料） 是指「描述資料本身的資訊」，
     例如分頁時的總筆數、目前頁數、總頁數等，
     不是主要資料內容，而是用來說明或補充資料的結構、狀態或屬性。 -->

- 於 `toArray` 內自訂 `meta/links`：

```php
public function toArray(Request $request): array
{
    return [
        'data' => $this->collection,
        'links' => [
            'self' => 'link-value',
        ],
    ];
}
```

---

- 於 `with` 方法回傳最外層 `meta`：

<!-- with 在 Resource 裡是用來加上額外的 meta 資訊，
     而在 Eloquent 查詢裡，with 才是用來預載入關聯資料（eager loading）。
     兩者用途不同。 -->

<!-- 不用特別呼叫 with()，
     只要你在 Resource 或 ResourceCollection 裡定義了 with() 方法，
     API 回傳時就會自動包含你設定的 meta 資訊。 -->

```php
public function with(Request $request): array
{
    // 用於 Resource 或 ResourceCollection，回傳時自動加上 meta 資訊
    return [
        'meta' => [
            'key' => 'value', // 可自訂 meta 欄位內容
        ],
    ];
}

{
  "data": [ ... ],      // 主要資料
  "meta": {
    "key": "value"      // 你自訂的 meta 資訊
  }
}

```

---

- 於 `controller` 直接加 `meta`：

```php
return User::all()
    ->load('roles') // 載入 roles 關聯
    ->toResourceCollection() // 轉成 ResourceCollection，格式化回傳
    ->additional(['meta' => [
        'key' => 'value', // 加上自訂 meta 資訊
    ]]);
```

---

## 2.10 **Resource Responses**

- 可直接回傳 `Resource`，也可用 `response()` 進一步自訂 *HTTP* 回應：

```php
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// 註解：查詢 id=1 的使用者，轉成 Resource 格式，回傳時加上自訂 header
Route::get('/user', function () {
    return User::find(1)
        ->toResource()                // 轉成 UserResource
        ->response()                  // 取得 Response 實例
        ->header('X-Value', 'True');  // 加上自訂 header
        // 如果已經在 Resource 裡實作了 withResponse() 方法，
        // API 回傳時會自動加上你定義的 header，
        // 所以不需要再額外寫 ->header('X-Value', 'True')，
        // 除非要加更多不同的 header。
});
```

---

- 於 `Resource 類別內` 自訂 `withResponse` 方法：

<!-- 
這個 withResponse() 方法會在 API 回傳時自動附加你自訂的 header（例如 X-Value: True）到 Response 物件裡，
你不用額外呼叫，
只要定義在 Resource 類別裡，回傳時就會自動加上。 
-->

```php
namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
        ];
    }

    // withResponse 可用來在回傳時加上自訂 header 或修改 response
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->header('X-Value', 'True');
    }
}
```