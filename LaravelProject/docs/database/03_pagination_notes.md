# *Laravel Pagination（分頁） 筆記*

---

## 1. **分頁簡介**

Laravel 分頁（Pagination）與 `Query Builder`、`Eloquent` 深度整合，提供零設定、易用且彈性的分頁功能。
- 預設分頁樣式支援 `Tailwind CSS`，也可切換 `Bootstrap`。
- 分頁物件可直接在 Blade 以 `{{ $users->links() }}` 呈現。

---

### 1.1 *Tailwind/Bootstrap 分頁樣式*
- **預設**為 `Tailwind`，若用 Tailwind 4.x，`resources/css/app.css` 已自動設定。
- `Bootstrap` 樣式可於 `AppServiceProvider` 設定。

---

## 2. **基本用法**

### 2.1 *Query Builder 分頁*

```php
// 基本分頁，每頁 15 筆
$users = DB::table('users')->paginate(15);
```
- `paginate(每頁筆數)` 會自動處理 `limit/offset`。
- 頁碼自動偵測 `page query string`。

---

### 2.1.1 *Controller 範例*

```php
class UserController extends Controller
{
    public function index(): View
    {
        return view('user.index', [
            'users' => DB::table('users')->paginate(15)
        ]);
    }
}
```

---

### 2.2 *Simple Pagination*

```php
$users = DB::table('users')->simplePaginate(15);
```
- 只顯示「**上一頁/下一頁**」，`不查詢總頁數`，效能較佳。

---

### 2.3 *Eloquent 分頁*

```php
$users = User::paginate(15);
$users = User::where('votes', '>', 100)->paginate(15);
$users = User::where('votes', '>', 100)->simplePaginate(15);
$users = User::where('votes', '>', 100)->cursorPaginate(15);
```
- Eloquent 語法與 Query Builder 幾乎一致。

---

### 2.4 *多分頁器同頁使用*

- 多分頁器同頁使用的場合通常出現在`需要在同一個網頁中顯示多個不同的資料列表，並且每個列表都有自己的分頁功能`。
  - 例如：
    - 一個頁面同時顯示 **使用者列表** 和 **文章列表**。
    - 每個列表需要獨立的分頁器，避免 URL 中的分頁參數衝突。

```php
public function index(Request $request)
{
    // 第一個分頁器：顯示使用者列表
    $users = User::where('votes', '>', 100)->paginate(15, ['*'], 'users');
    // 第一個分頁器，使用 'users' 作為 query string 名稱
    // example.com?users=1

    // 第二個分頁器：顯示文章列表
    $posts = Post::where('published', true)->paginate(10, ['*'], 'posts');
    // 第二個分頁器，使用 'posts' 作為 query string 名稱
    // example.com?posts=1

    // 將分頁器結果傳遞到視圖
    return view('dashboard', compact('users', 'posts'));
    // example.com?users=1&posts=1
}

```
- **第二參數** 用於指`定要選取的欄位`。預設值是 ['*']，表示選取`所有欄位`。

- **第三參數** 可自訂 `query string 名稱`，避免多分頁器衝突。

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>使用者列表</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Votes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->votes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <!-- 使用者分頁器 -->
    {{ $users->links('pagination::bootstrap-4') }}

    <h1>文章列表</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Published At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($posts as $post)
                <tr>
                    <td>{{ $post->id }}</td>
                    <td>{{ $post->title }}</td>
                    <td>{{ $post->published_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <!-- 文章分頁器 -->
    {{ $posts->links('pagination::bootstrap-4') }}
</body>
</html>
```

---

## 3. **Cursor 分頁**

```php
$users = DB::table('users')->orderBy('id')->cursorPaginate(15);
```
- 需有 `orderBy` ，且*排序欄*位必須唯一。
- 適合`大量資料、無限捲動`。
- URL 參數為 *cursor 字串*。

---

### 3.1 *Offset vs Cursor 分頁 SQL 對照*

```sql
-- Offset 分頁
select * from users order by id asc limit 15 offset 15;
-- Cursor 分頁
select * from users where id > 15 order by id asc limit 15;
```
- `Cursor` 分頁效能佳、*不會漏資料或重複*。
- `缺點`：*無法* 顯示總頁數、僅支援「上一頁/下一頁」。

### 3.2 *Cursor 分頁產生的 URL 範例*

- cursor 分頁產生的 URL 範例：
  
  `http://localhost/users?cursor=eyJpZCI6MTUsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0`
  ```php
  <!-- 解碼後的內容 -->
  {
    "id": 15,
    "_pointsToNextItems": true
  }
  <!-- id：表示下一頁的起始記錄的主鍵值（例如 id = 15）。 -->
  <!-- _pointsToNextItems：表示游標指向下一頁的記錄。 -->

  <!-- 第一頁查詢：
  查詢資料表的前 N 筆記錄（例如 LIMIT 15）。
  返回的結果包含游標，指向下一頁的起始位置。
  
  下一頁查詢：
  使用游標中的唯一標識（例如 id = 15）作為起始位置，查詢下一頁的資料。 -->
  ```
  - 參數名稱為 `cursor`，值為編碼字串，代表**分頁游標位置**。
  - 與 **offset 分頁** 的 `?page=2` 不同。

  - **分頁游標位置** 是指 `Cursor 分頁 使用的方式，用來標記分頁的起始位置`。它是一種基於資料的*唯一標識*（通常是主鍵或其他唯一欄位）來進行分頁的技術，與傳統的 Offset 分頁 不同。

  - **分頁游標位置的概念**
    - *Cursor 分頁*：
      - 使用`游標（cursor）`來`標記分頁的起始位置`。
      - 游標是一個編碼字串，包含`分頁的唯一標識（例如主鍵 id），用來確定下一頁的起始資料`。
    - *游標的作用*：
      - 游標`指向資料表中的某一筆記錄`，分頁查詢會從該記錄開始。
      - 它避免了 Offset 分頁 的性能問題（例如資料量大時的慢查詢）。
  - **比較**
    - *Offset 分頁*
      - 使用 `LIMIT` 和 `OFFSET` 來進行分頁。
      - URL 範例：http://localhost/users?page=2
      - 問題：
        - 當`資料量很大時`，OFFSET 的性能會下降，因為資料庫需要跳過大量記錄。
        - OFFSET 分頁的本質是「`跳過前 N 筆記錄`」。
        - 資料庫在執行 OFFSET 時，`必須先掃描並跳過前面的記錄，然後再返回需要的記錄`。
        - 跳過的記錄**不會被直接丟棄**，而是**仍然需要被掃描和排序**，這會消耗大量資源
        - 隨著頁碼增大，跳過的記錄數量越多，資料庫需要處理的工作量也越大。
    - *Cursor 分頁*
      - 使用`游標`（cursor）來`標記分頁的起始位置`。
      - URL 範例：http://localhost/users?cursor=eyJpZCI6MTUsIl9wb2ludHNUb25leHRJdGVtcyI6dHJ1ZX0
      - 優勢：
        - 性能更高，因為游標直接`指向下一頁的起始記錄`，避免了跳過大量記錄的操作。

---

## 4. **手動建立分頁器**

- 可用 `Paginator`、`LengthAwarePaginator`、`CursorPaginator` *手動分頁*。
- `Paginator/CursorPaginator` *不需* 總筆數
- `LengthAwarePaginator` *需* 傳入總數。
- 手動分頁時，需先用 `array_slice` 切割資料。

---

### 4.1 *手動建立分頁器範例*

```php
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\CursorPaginator;

// 假設 $items 是已取得的資料陣列
$page = request('page', 1);
$perPage = 15;
$slice = array_slice($items, ($page - 1) * $perPage, $perPage);

// LengthAwarePaginator（有總數）
$paginator = new LengthAwarePaginator($slice, count($items), $perPage, $page, [
    'path' => Paginator::resolveCurrentPath(),
    'pageName' => 'page',
]);

// Paginator（無總數）
$paginator = new Paginator($slice, $perPage, $page, [
    'path' => Paginator::resolveCurrentPath(),
    'pageName' => 'page',
]);

// CursorPaginator（游標分頁）
$paginator = new CursorPaginator($slice, $perPage, null, [
    'path' => Paginator::resolveCurrentPath(),
    'pageName' => 'cursor',
]);
```
- **手動分頁**時需先用 `array_slice` 切割資料。
- `LengthAwarePaginator` **需**傳入總數
- `Paginator/CursorPaginator` **不需**。

---

## 5. **分頁連結與自訂 URL**

### 5.1 *自訂分頁連結路徑*

```php
$users->withPath('/admin/users');
```
- 產生的`分頁連結`會以指定路徑為主。

---

### 5.2 *附加查詢參數*

```php
$users->appends(['sort' => 'votes']);
$users = User::paginate(15)->withQueryString();
```
- `appends` 可加**單一或多個**查詢參數。
- `withQueryString` 會自動附加**目前所有查詢參數**。

---

### 5.3 *附加 hash fragment*

 - 「哈希片段」 或 「URL片段」。它指的是 URL 中以 # 開頭的部分，用來`標記網頁中的某個位置或片段`。

```php
$users = User::paginate(15)->fragment('users');
// example.com?page=1#users
```
- 產生的分頁連結會加上 `#users`。

---

## 6. **Blade 顯示分頁結果**

```php
<div class="container">
    @foreach ($users as $user)
        {{ $user->name }}
    @endforeach
</div>

{{ $users->links() }}
```
- `links()` 會*自動產生分頁連結*，預設為 `Tailwind` 樣式。
- 可用 `onEachSide(5)` 控制中間顯示頁數範圍。
  - 控制分頁器中間顯示的頁碼範圍，即在*當前頁碼*的`左右兩側顯示的頁碼數量`。
  - 例如：如果當前頁碼是 6，並且使用 `onEachSide(5)`，分頁器會顯示當前頁碼的左右各 5 個頁碼。
    - 左側：1, 2, 3, 4, 5
    - 當前頁碼：6
    - 右側：7, 8, 9, 10, 11
```php
{{ $users->onEachSide(5)->links() }}
```

---

## 7. **分頁結果轉 JSON**

```php
Route::get('/users', function () {
    return User::paginate();
});
```
- 分頁物件可直接回傳，*會自動轉成 JSON*，包含 `meta` 資訊與 `data` 陣列。

---

## 8. **自訂分頁視圖**

- `links('view.name')` 可指定自訂分頁視圖。
- 可用 `php artisan vendor:publish --tag=laravel-pagination` 匯出預設分頁視圖到 `resources/views/vendor/pagination`。
- *AppServiceProvider* 可用 `Paginator::defaultView('view-name')`、`defaultSimpleView('view-name')` 設定全域預設。
- *Bootstrap* 樣式：`Paginator::useBootstrapFive()`、`useBootstrapFour()`。

---

### 8.1 *AppServiceProvider 設定分頁視圖*

- 如果你的應用中有多個分頁器，並且希望所有分頁器使用**相同的樣式**，可以透過 `AppServiceProvider` 全域設定分頁視圖，避免每次都手動指定。

- **分頁樣式** 指的就是 `分頁器中頁碼的顯示方式`，包括：

  - *頁碼的排列*：
    - 如 1 2 3 4 5 等頁碼。
    - 當前頁碼通常會高亮或加上特殊樣式（例如加粗或背景色）。
  - *上一頁和下一頁的按鈕*：
    - 如 « Previous 和 Next »。
    - 用於快速導航到上一頁或下一頁。
  - *分頁器的整體 HTML 結構*：
    - 分頁器的外觀（例如使用 <nav>、<ul> 和 <li> 標籤）。
    - 分頁器的樣式（例如是否使用 Bootstrap 或 Tailwind CSS）。

```php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 設定預設分頁視圖
        Paginator::defaultView('view-name');
        // 設定 simple 分頁視圖
        Paginator::defaultSimpleView('view-name');
    }
}
```
- 可全域自訂`普通分頁`與 `simple 分頁的 Blade 視圖`。

```html
<nav>
    <ul class="pagination">
        @if ($paginator->onFirstPage())
            <li class="disabled"><span>&laquo;</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active"><span>{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a></li>
        @else
            <li class="disabled"><span>&raquo;</span></li>
        @endif
    </ul>
</nav>
```

---

### 8.2 *切換 Bootstrap 分頁樣式*

- 如果你希望分頁器的 HTML 樣式符合你的`前端框架`（例如 Bootstrap、Tailwind CSS），可以透過自訂 Blade 視圖來實現。

```php
use Illuminate\Pagination\Paginator;

public function boot(): void
{
    // 切換為 Bootstrap 5 樣式
    Paginator::useBootstrapFive();
    // 切換為 Bootstrap 4 樣式
    Paginator::useBootstrapFour();
}
```
- 可於 `AppServiceProvider` 設定。

---

### 8.3 *links 傳自訂 view 與參數*

```php
{{-- 指定自訂分頁視圖 --}}
{{ $paginator->links('view.name') }}
{{-- 傳遞額外參數 --}}
{{ $paginator->links('view.name', ['foo' => 'bar']) }}
```

---

### 8.4 *分頁 JSON 結構範例*

```json
{
   "total": 50,
   "per_page": 15,
   "current_page": 1,
   "last_page": 4,
   "current_page_url": "http://laravel.app?page=1",
   "first_page_url": "http://laravel.app?page=1",
   "last_page_url": "http://laravel.app?page=4",
   "next_page_url": "http://laravel.app?page=2",
   "prev_page_url": null,
   "path": "http://laravel.app",
   "from": 1,
   "to": 15,
   "data":[
        {
            // Record...
        },
        {
            // Record...
        }
   ]
}
```
- 直接回傳分頁物件即可自動產生上述 JSON。

---

## 9. **分頁物件方法總覽**

### 9.1 *LengthAwarePaginator/Paginator 常用方法*
- `count()`：目前頁面**資料數**
- `currentPage()`：目前頁碼

- `firstItem()`：本頁第**一筆資料的編號**，而不是資料本身。這個編號是基於分頁的 資料序列，即資料在整個結果集中的位置。

- `getOptions()`：取得**分頁選項**
  - 分頁選項 是指在 Laravel 的分頁功能中，`可以透過額外的設定來控制分頁的行為或結果`。
    - 每頁顯示的*資料數量*。
    - 選取的*欄位*。
    - 分頁器的 *query string* 名稱。
    - 其他分頁相關的設定（如分頁樣式）。

- `getUrlRange($start, $end)`：產生一段**分頁連結**

- `hasPages()`：**是否**有多頁
- `hasMorePages()`：**是否**有下一頁

- `items()`：本頁**所有資料**

- `lastItem()`：本頁**最後一筆**資料編號
- `lastPage()`：**最後一頁**頁碼（simplePaginate 無此方法）

- `nextPageUrl()`：**下一頁連結**

- `onFirstPage()`：**是否**為第一頁
- `onLastPage()`：**是否**為最後一頁

- `perPage()`：**每頁筆數**
- `previousPageUrl()`：上一頁連結

- `total()`：**總筆數**（simplePaginate 無此方法）

- `url($page)`：指定頁碼的**連結**

- `getPageName()`：取得**分頁參數名稱**

- `setPageName($name)`：設定**分頁參數名稱**

- `through($callback)`：**資料轉換**

---

### 9.2 *CursorPaginator 常用方法*
- `count()`：目前頁面**資料數**
- `cursor()`：目前游標

- `getOptions()`：取得**分頁選項**
- `getCursorName()`：取得游標**參數名稱**

- `hasPages()`：**是否**有多頁
- `hasMorePages()`：**是否**有下一頁

- `items()`：本頁**所有資料**

- `nextCursor()`：**下一頁**游標
- `nextPageUrl()`：**下一頁**連結

- `onFirstPage()`：**是否**為第一頁
- `onLastPage()`：**是否**為最後一頁

- `perPage()`：每頁筆數
- `previousCursor()`：**上一頁**游標
- `previousPageUrl()`：**上一頁**連結

- `setCursorName()`：設定**游標參數名稱**

- `url($cursor)`：指定**游標的連結**

---