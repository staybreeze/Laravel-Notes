# *Laravel Artisan Console 與 Tinker 筆記*

---

## 1. **Artisan Console 介紹**

*Artisan* 是 Laravel 內建的 __命令列工具__，位於專案根目錄下的 `artisan` 檔案。它提供許多方便的 *指令*，協助你在開發、維護、測試專案。

---

### 1.1 *查看所有 Artisan 指令*

```bash
# 終端機指令
php artisan list
```

---

### 1.2 *查看特定指令的說明*

```bash
# 終端機指令
php artisan help migrate
```

每個指令都可以用 `help` 參數查看詳細說明、可用參數與選項。

---

### 1.3 *Artisan 指令常見分類*

- __路由__：`route:list`、`route:cache`、`route:clear`
- __快取__：`cache:clear`、`config:cache`、`config:clear`、`view:cache`、`view:clear`
- __資料庫__：`migrate`、`migrate:rollback`、`migrate:refresh`、`db:seed`
- __事件/任務__：`queue:work`、`queue:listen`、`schedule:run`
- __測試__：`test`、`make:test`
- __自訂指令__：可用 `make:command` 建立自訂 Artisan 指令

---

### 1.4 *Artisan 指令補充*

- Artisan 指令可接受 _參數與選項_，例如：

```bash
# 終端機指令
  php artisan migrate --seed
  php artisan make:model Post -mcr
  ```

- 可用 `php artisan` 直接執行，或在 `composer.json` scripts 內包裝。

---

## 2. **Laravel Sail**

如果你使用 Laravel Sail（__官方 Docker 開發環境__），執行 Artisan 指令時要加上 `sail`：

```bash
# 終端機指令
./vendor/bin/sail artisan list
```

`Sail` 會在 `Docker` 容器內，執行 Artisan 指令。

---

## 3. **Tinker (REPL)**

Tinker 是 Laravel 的 __互動式命令列（REPL）__，讓你可以直接在命令列操作 `Eloquent 模型、jobs、events` 等。

---

### 3.1 *安裝 Tinker*

Laravel **預設**已安裝 Tinker。如果不小心移除，可以用 `Composer` 重新安裝：

```bash
# 終端機指令
composer require laravel/tinker
```

---

### 3.2 *進入 Tinker 環境*

```bash
# 終端機指令
php artisan tinker
```

---

### 3.3 *發佈 Tinker 設定檔*

```bash
# 終端機指令
php artisan vendor:publish --provider="Laravel\Tinker\TinkerServiceProvider"
```

會產生 `config/tinker.php` 設定檔。

---

### 3.4 *Tinker 常見用途*

```php
// Tinker 互動環境下
App\Models\User::all();
$user = App\Models\User::find(1);
$user->posts;
```
- 執行 `jobs、events、service` 內邏輯
- 快速測試 `helper、facade、collection` 等

---

### 3.5 *Tinker 設定重點*

- __允許執行的 Artisan 指令__ 

  Tinker 只允許執行 *部分 Artisan 指令*（如 `clear-compiled、down、env、inspire、migrate、up、optimize`）。  
  若要允許更多指令，可在 `config/tinker.php` 的 `commands` 陣列中加入：

  ```php
  'commands' => [
      // App\Console\Commands\ExampleCommand::class,
  ],
  ```

---

- __不自動 alias 的類別__  

  Tinker **會自動** alias 類別名稱，若有不想自動 alias 的類別，可加到 `dont_alias` 陣列：

  ```php
  'dont_alias' => [
      App\Models\User::class,
  ],
  ```

---

### 3.6 *Tinker 注意事項*

- Tinker 互動環境下，**派送工作（jobs）** 建議用 `Bus::dispatch` 或 `Queue::push`，不要用 `dispatch` 輔助函式。
- Tinkerwell 是一個進階的 Laravel REPL 工具，支援熱重載、多行編輯、自動補全等功能（需額外購買）。

---

## 4. **Artisan 與 Tinker 常見問題與補充**

- __Artisan 指令無法執行？__
  - 確認 PHP 路徑、權限、專案根目錄。
  - 若用 Sail，請用 `./vendor/bin/sail artisan ...`。

- __Tinker 無法啟動？__
  - 檢查 `laravel/tinker` 是否安裝。
  - 檢查 `config/tinker.php` 設定。

- __自訂 Artisan 指令__
  - 用 `php artisan make:command MyCommand` 建立，程式碼放在 `app/Console/Commands/`。

- __Artisan 指令自動補全__
  - 可安裝 bash/zsh 補全套件，或用 Laravel Pint、Laravel Herd 等工具。

---

## 5. **參考連結**

- [Laravel 官方文件 - Artisan Console](https://laravel.com/docs/artisan)
- [Laravel 官方文件 - Tinker](https://laravel.com/docs/artisan#tinker)
- [Tinkerwell](https://tinkerwell.app/)

---

## 6. **自訂 Artisan 指令**（Command）

### 6.1 *建立自訂指令*

使用 Artisan 指令產生自訂指令類別：

```bash
php artisan make:command SendEmails
```

產生於 `app/Console/Commands/SendEmails.php`。

---

### 6.2 *指令結構說明*

```php
namespace App\Console\Commands;

use App\Models\User;
use App\Support\DripEmailer;
use Illuminate\Console\Command;

class SendEmails extends Command
{
    // 指令名稱與參數
    protected $signature = 'mail:send {user}';
    // 指令描述
    protected $description = 'Send a marketing email to a user';

    // 指令執行內容，透過依賴注入取得 DripEmailer
    public function handle(DripEmailer $drip): void
    {
        // 取得 user 參數，查詢使用者
        $user = User::find($this->argument('user'));
        // 寄送行銷郵件
        $drip->send($user);
    }
}
```
- __$signature__：定義`指令名稱`與`參數格式`
- __$description__：指令`說明`，會顯示在 `php artisan list`
- __handle()__：指令`執行主體`，可自動`注入依賴`

---

### 6.3 *狀態碼與錯誤處理*

- **預設** `成功回傳 0`

- **失敗時可**

```php
$this->error('Something went wrong.');
return 1;
```

- **立即終止**

```php
$this->fail('Something went wrong.');
```

---

### 6.4 *閉包式（Closure-based）指令*

可在 `routes/console.php` 直接定義：

```php
use Illuminate\Support\Facades\Artisan;

// 這裡直接用 closure 定義一個指令，不需額外建立 Command 類別
Artisan::command('mail:send {user}', function (string $user) {
    // $user 會自動對應 signature 裡的 {user} 參數
    // $this 代表底層 Command 實例，可用 info/argument/option/call 等輔助方法
    $this->info("Sending email to: {$user}!");
})->purpose('Send a marketing email to a user'); // 用 purpose() 設定指令描述，顯示於 php artisan list
```

- **特點**

    - `不需建立 class`，適合臨時、簡單、教學用指令
    - 支援 *type-hint* 依賴注入（如 `function (DripEmailer $drip, string $user)`）
    - closure 內 `$this` 可用所有 `Command 輔助方法`
    - *signature 語法與 class 指令一致*，支援 `arguments/options/描述`
    - 可同時存在多個 closure-based 與 class 指令

---

### 6.5 *進階：Isolatable 指令*（`防止重複執行`）

- `Isolatable` 讓你的 Artisan 指令 __同一時間只能有一個實例執行，避免重複__ 執行造成資料競爭、重複處理、資源衝突等問題。
- 適合排`程、批次、同步、匯出`等指令，確保唯一性。

---

#### 6.5.1 **實作 Isolatable**

```php
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class SendEmails extends Command implements Isolatable
{
    // 只要 implements Isolatable，Laravel 會自動幫你加上 --isolated 參數
    // 執行時加 --isolated，會用 cache 做 lock，確保同一時間只有一個指令執行
    // 可自訂 isolatableId() 讓不同參數有不同 lock
    // 可自訂 isolationLockExpiresAt() 控制 lock 過期時間
    // ... signature, description, handle() ...
}
```
- 執行時加 `--isolated`，__同一時間只允許一個執行__：

```bash
php artisan mail:send 1 --isolated
```

---

- 自訂 __失敗時的狀態碼__：

```bash
php artisan mail:send 1 --isolated=12
```

---

#### 6.5.2 **自訂 Lock ID**

```php
// 預設 lock id 用指令名稱，可根據參數自訂唯一 key，讓不同參數可同時執行不同 lock
public function isolatableId(): string
{
    // 以 user 參數作為唯一識別，避免重複執行同一 user 的任務。
    return $this->argument('user');
}
```

---

#### 6.5.3 **自訂 Lock 過期時間**

```php
// 預設 lock 過期 1 小時，可自訂過期時間，避免異常時 lock 永久卡住
use DateTimeInterface;
use DateInterval;

public function isolationLockExpiresAt(): DateTimeInterface|DateInterval
{
    return now()->addMinutes(5);
}
```

- __實作重點__：

 -` 避免重複執行`造成資料錯亂或效能問題
 - 不會`排隊，搶不到 lock 直接結束`
 - 適合`高併發、排程、批次`等場景

---

### 6.6 *實作練習建議*

1. 用 `php artisan make:command HelloWorld` 建立一個簡單指令，顯示 Hello World。
2. 用 `closure-based` 指令在 `routes/console.php` 實作一個 `greet {name}` 指令，顯示問候語。
3. 實作一個 `Isolatable` 指令，模擬長時間任務，並測試 `--isolated` 功能。

---

### 6.7 *範例*

#### 6.7.1 **HelloWorld 指令**

```php
// app/Console/Commands/HelloWorld.php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class HelloWorld extends Command
{
    protected $signature = 'hello:world';
    protected $description = '顯示 Hello World';

    public function handle()
    {
        $this->info('Hello World!');
    }
}
```

---

#### 6.7.2 **Closure-based 指令**

```php
// routes/console.php
Artisan::command('greet {name}', function (string $name) {
    $this->info("Hello, {$name}!");
})->purpose('Say hello to someone');
// purpose 用來設定指令的用途說明，
// 讓你在執行 php artisan list 時，
// 可以看到這個指令的簡短描述，方便了解用途。
```

---

## 7. **Artisan 指令輸入定義與互動提示**

### 7.1 *signature 屬性定義 Arguments 與 Options*

```php
- 所有 `arguments/options` 都用大括號 `{}` 包起來
- 必填參數：`{user}` // 執行指令時必須輸入 user 參數，例如 php artisan mail:send 123
- 選填參數：`{user?}` // user 參數可省略，沒給會是 null 或預設值
- 預設值：`{user=foo}` // user 參數可省略，沒給時預設為 foo
- Option（布林開關）：`{--queue}` // 可加 --queue，沒加為 false，加了為 true，類似開關
- Option（需值）：`{--queue=}` // 必須加 --queue=xxx，否則值為 null
- Option 預設值：`{--queue=default}` // --queue 可省略，沒給時預設為 default
- Option 快捷鍵：`{--Q|queue}` // --Q 或 --queue 都可用，-Q 是快捷鍵
- 輸入陣列：`{user*}`、`{user?*}`、`{--id=*}` // 可輸入多個值，user* 代表多個 user，--id=* 代表多個 id
- 參數/選項描述：`{user : The ID of the user}` // 冒號後面是描述，顯示於 php artisan help
```

---

#### **範例**

```php
protected $signature = 'mail:send
                        {user : The ID of the user}
                        {--queue : Whether the job should be queued}
                        {--Q|queue=default : Queue name (shortcut Q)}
                        {--id=* : 多個 id}';
```

---

### 7.2 *使用方式*

- `php artisan mail:send 1 --queue`
- `php artisan mail:send 1 --queue=default`
- `php artisan mail:send 1 -Qdefault`
- `php artisan mail:send 1 2 3`（user 為陣列）
<!-- 指令會同時處理 ID 為 1、2、3 的使用者。 -->
- `php artisan mail:send --id=1 --id=2`

---

### 7.3 *`PromptsForMissingInput` 互動提示*

- `PromptsForMissingInput` 讓 Artisan 指令 __缺少必填參數時，能自動或自訂互動詢問用戶__，提升 CLI 友善度。
- `promptForMissingArgumentsUsing` 可 __自訂每個缺少參數的提問內容__，讓提示更明確，也可用 `closure` 做進階互動（如搜尋、下拉選單）。

- 可`自訂問題、placeholder`、甚至用 `closure` 做進階互動（如搜尋、下拉選單）。

- `afterPromptingForMissingArguments` __可在所有參數都補齊後，再進一步互動__。

- 實作 `PromptsForMissingInput` 介面，缺少必填參數時，自動互動詢問：

```php
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class SendEmails extends Command implements PromptsForMissingInput
{
    protected $signature = 'mail:send {user}';
    // ...
}
```

- 執行 `php artisan mail:send` 時，__沒給 user，系統會自動互動詢問 user__。

---

- **自訂互動問題**：

```php
protected function promptForMissingArgumentsUsing(): array
{
    return [
        'user' => 'Which user ID should receive the mail?', // 這裡自訂問題內容
    ];
}
```
- 執行時會顯示 `"Which user ID should receive the mail?"` 來詢問 user。

---

- **加入 placeholder**：

```php
protected function promptForMissingArgumentsUsing(): array
{
    return [
        'user' => ['Which user ID should receive the mail?', 'E.g. 123'], // 第二個元素是 placeholder
    ];
}
```
- 問題下方會顯示 `"E.g. 123"` 作為輸入提示。

---

- *完全自訂互動*（`closure`）

```php
use App\Models\User;
use function Laravel\Prompts\search;

protected function promptForMissingArgumentsUsing(): array
{
    return [
        'user' => fn () => search(
            label: 'Search for a user:', // 問題標題
            placeholder: 'E.g. Taylor Otwell', // 輸入提示
            options: fn ($value) =>
                // 這是一個 callback，每當用戶在 CLI prompt 輸入內容時都會被呼叫，$value 是目前輸入的字串
                strlen($value) > 0
                    // 如果有輸入內容，查詢所有 name 包含 $value 的使用者
                    ? User::where('name', 'like', "%{$value}%")
                        ->pluck('name', 'id') // 取出 id => name 陣列
                        ->all() // 轉成純 PHP 陣列
                    // 如果沒輸入內容，回傳空陣列（不顯示選項）
                    : []
        ),
    ];
}
```

---

- **小結**

- 這段程式碼 __會根據用戶目前輸入的字串，從資料庫即時搜尋符合條件的使用者__，
- 並把這些使用者的 `id` 和 `name` 當作選項顯示在 `CLI prompt` 上，
- 適合大量資料的`即時查找`與`互動式選單`。

- `afterPromptingForMissingArguments` 可進一步互動：
```php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Laravel\Prompts\confirm;

protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
{
    $input->setOption('queue', confirm(
        label: 'Would you like to queue the mail?', // 問題內容，顯示在 CLI 上
        default: $this->option('queue') // 預設值，若之前有給 queue 參數就用那個，否則用 false
    ));
    // 會再問一次「Would you like to queue the mail?」讓用戶決定是否要 queue。
    // 用戶回答 yes/no，queue option 會被設為 true/false
    // 之後在 handle() 裡可用 $this->option('queue') 取得用戶選擇
}
```

---

- **queue 在這裡的意思**

 - 通常代表「__是否將這個任務丟入 queue（佇列）背景處理__」
 - 如果 queue 為 `true`，`handle(`) 內可用 queue 系統（如 dispatch job）
 - 如果 queue 為 `false`，則直接同步執行

 **執行流程範例**

 1. `php artisan mail:send`
    → 互動詢問 user（顯示自訂問題與 placeholder）
    → 互動詢問 queue（`afterPromptingForMissingArguments`）
    → `handle()` 執行，顯示結果

 2. `php artisan mail:send 123`
    → 只問 `queue`

 3. `php artisan mail:send 123 --queue`
    → 不再互動，直接執行 `handle()`

---

### 7.4 *補充*

- Laravel `Prompts` 文件有 __更多互動提示用法__  
  例如可以用 `select()`, `text()`, `confirm()` 等方法，  
  讓 __CLI 指令__ 支援`下拉選單、文字輸入、確認對話`等互動。

- `signature` 語法 __可跨多行書寫__  
  例如：
    ```php
    protected $signature = <<<SIG
    mail:send
        {user : 使用者 ID}
        {--force : 強制寄送}
        {--queue= : 指定佇列}
    SIG;
    ```
  這樣可以讓指令參數和選項更清楚易讀。

---

## 8. **指令 I/O：輸入與輸出**

### 8.1 *取得 Arguments 與 Options*

- __取得單一 argument__：`$this->argument('user')`
- __取得所有 arguments__：`$this->arguments()`
- __取得單一 option__：`$this->option('queue')`
- __取得所有 options__：`$this->options()`

- `arguments` 是指令執行時**必填或位置型的參數**，通常用來傳遞主要資料（如 user ID）。
  例如：`php artisan mail:send 1`，`1` 就是 argument。

- `options` 是指令執行時**可選的參數**，通常用 `--` 開頭，  
  用來控制指令行為或附加設定（如指定佇列）。
  例如：`php artisan mail:send 1 --queue=high`，`--queue=high` 就是 option。

---

### 8.2 *互動式輸入*

- **一般提問**：

```php
$name = $this->ask('What is your name?');
$name = $this->ask('What is your name?', 'Taylor'); // 預設值
```

---

- **密碼/隱藏輸入**：

```php
$password = $this->secret('What is the password?');
```

---

- **確認**（是/否）：

```php
if ($this->confirm('Do you wish to continue?')) { /* ... */ }
if ($this->confirm('Do you wish to continue?', true)) { /* 預設為是 */ }
```

---

- **自動補全**（auto-complete）：

```php
$name = $this->anticipate('What is your address?', function (string $input) {
    // 這是一個 closure（匿名函式），每當用戶在 CLI prompt 輸入內容時都會被呼叫
    // $input 代表用戶目前輸入的字串
    // 下面這行會根據 $input 查詢 Address 資料表，找出 name 以 $input 開頭的地址
    return Address::whereLike('name', "{$input}%") // Eloquent 查詢，name 欄位以 $input 開頭
        ->limit(5) // 只取前 5 筆結果，避免 CLI 選項太多
        ->pluck('name') // 只取出 name 欄位，組成陣列（['台北市信義路', '台北市忠孝東路', ...]）
        ->all(); // 轉成純 PHP 陣列，作為自動補全選項
});
// 實際效果：
// - 用戶在 CLI 輸入地址時，系統會即時查詢資料庫，顯示最多 5 個以輸入字串開頭的地址作為自動補全選項。
// - 用戶可以直接選擇其中一個建議，也可以繼續輸入自訂內容。
// - 這種互動方式大幅提升 CLI 使用者體驗，尤其適合大量資料、需快速查找的情境。
// - 與 choice 不同，anticipate 允許用戶輸入任何內容，不會限制只能選建議選項。
// - 適合如「地址、名稱、標籤」等自由輸入又有常見選項的欄位。
```

---

- **多選一**（choice）：

- `$this->choice()` 用於 CLI 互動式選單：
    - 第一個例子：讓使用者從 `['Taylor', 'Dayle']` 選一個名字，`$defaultIndex` 可指定預設選項。
    - 第二個例子：`true` 代表 __允許多選__，回傳多個選擇結果。
        - 第一個 `null`：不指定 __預設選項__（預設不選）。
        - 第二個 `null`：不限制 __最大選擇數量__（可選多個）。
        - `true`：啟用多選模式。

```php
$name = $this->choice('What is your name?', ['Taylor', 'Dayle'], $defaultIndex);
// 允許多選
$names = $this->choice('Select names', ['Taylor', 'Dayle'], null, null, true);
```

---

### 8.3 *輸出訊息*

- __一般訊息__：`$this->info('成功！')`（綠色）  → 顯示綠色「成功！」
- __錯誤訊息__：`$this->error('錯誤！')`（紅色） → 顯示紅色「錯誤！」
- __普通文字__：`$this->line('顯示文字')`       → 顯示一般「顯示文字」
- __註解__：`$this->comment('註解')`           → 顯示黃色「註解」
- __問題__：`$this->question('問題')`（藍色）   → 顯示藍色「問題」
- __警告__：`$this->warn('警告')`（黃色）       → 顯示黃色「警告」
- __空白行__：`$this->newLine()`、`$this->newLine(3)` → 插入 1 或多個空白行

---

### 8.4 *表格輸出*

```php
$this->table(
    ['Name', 'Email'],
    User::all(['name', 'email'])->toArray()
);
```

---

### 8.5 *進度條*

- **自動進度條**

```php
$users = $this->withProgressBar(User::all(), function (User $user) {
    // 這裡會對每個 User 執行 performTask，並顯示進度條
    $this->performTask($user);
});

```

---

- **手動進度條**

```php
$users = App\Models\User::all();
$bar = $this->output->createProgressBar(count($users)); // 建立進度條，總數為使用者數量
$bar->start(); // 開始進度條
foreach ($users as $user) {
    $this->performTask($user); // 執行任務
    $bar->advance();           // 進度條前進一格
}
$bar->finish(); // 結束進度條
```

---

## 9. **進階主題：Stub 自訂與 Signal Handling**

### 9.1 *Stub Customization*（`自訂產生檔案範本`）

- __stub（範本檔案）__ 是 Laravel 用來`產生各種程式碼的「模板」`（如 controller、migration、job、test 等）。
- 當你執行 `make:controller、make:model` 等指令時，Laravel 會根據`對應的 stub 內容產生檔案`。
- stub 其實就是一份帶有「__佔位符__」的 PHP 檔案，Laravel 會把 class 名稱、namespace 等資訊自動帶入。
- 預設會用內建的 stub（在 `vendor/laravel/framework/... 內`），但你可以用 `stub:publish` 來自訂。

- **若需自訂產生內容，可用**：

```bash
php artisan stub:publish
```

---

- 這行指令會把 Laravel 內建的 *stub* `範本檔案`複製到專案根目錄的 `stubs/ `目錄下
- 你可以自由修改這些檔案內容（如 controller.stub、migration.stub 等）
- 之後 `make:controller、make:model` 等指令都會 __優先用__ 你自訂的 stub

- __stub 的佔位符__：
 - stub 檔案裡會有像 `{{ class }}、{{ namespace }}` 這種佔位符
 - Laravel 產生檔案時會自動把這些佔位符替換成實際內容

- __自訂 stub 的流程__：
 1. 執行 `php artisan stub:publish`
 2. 修改 `stubs/` 目錄下的檔案內容（加`註解、統一 code style、加預設 trait..`.）
 3. 之後產生檔案都會自動套用你的自訂內容

- __實務應用情境__：
 - 統一公司或團隊的 `code style`
 - 自動加上`作者、日期、公司版權`等註解
 - 預設加上`常用 trait、interface、欄位`等
 - 讓新產生的檔案更貼近團隊需求，減少重複修改

- __安全性與升級__：
 - 你自訂的 stub 都在 `stubs/` 目錄下，_不會被 Laravel 升級覆蓋_
 - 如果 Laravel 新增了新的 stub，你可以再執行一次 `stub:publish`，_只會新增沒被你自訂過的檔案，不會覆蓋你已經修改過的_

- __小結__：
 - stub 是「_程式碼產生範本_」
 - `stub:publish` 讓你可以自訂這些範本，讓 `make` 指令產生的檔案完全符合你的需求
 - 修改 s`tubs/` 目錄下的檔案，不會影響 `vendor` 內的原始檔案，升級 Laravel 也不會被覆蓋

- __範例：自訂 controller.stub__
 1. 執行 `php artisan stub:publish`
 2. 編輯 `stubs/controller.stub`，在檔案最上方加上：
    /**
     * 作者：Vincent
     * 建立日期：{{ date }}
     */
 3. 之後 `php artisan make:controller TestController` 產生的檔案都會自動有這段註解

---

### 9.2 *Signal Handling*（`訊號處理`）

- Artisan 指令 __可攔截作業系統訊號__（如 `SIGTERM、SIGQUIT`），常用於 __長時間執行的任務__。

- 用 `$this->trap()` 註冊 callback：

```php
$this->trap([SIGTERM, SIGQUIT], function (int $signal) {
    $this->shouldKeepRunning = false;
    dump($signal); // SIGTERM / SIGQUIT
});
```
- 可用於`優雅終止、釋放資源、log` 等。

---

## 10. **指令註冊與 Artisan 事件**

### 10.1 *`withCommands` 註冊自訂目錄/類別*

- Laravel **預設** 只會自動註冊 `app/Console/Commands` 目錄下所有指令 class
- 如果你的專案有「_模組化_」、「_多層目錄_」、「_DDD 架構_」等需求，指令 class 可能會放在其他目錄（如 `app/Domain/Orders/Commands`）
- 或你有些指令 class __不想放在預設目錄__，也可以 __單獨註冊 class__
- 這時就要用 `withCommands` __額外註冊其他目錄或類別__，讓 Laravel 能自動載入這些指令

- **常見應用場景**：

 - _大型專案_ 用 DDD、模組化，指令分散在多個子目錄
 - 套件、共用模組的指令 class _不在預設目錄_
 - 只想註冊 _特定指令 class_，不想全部自動掃描
 - 減少啟動時，_不必要的 class 掃描_，提高效能

- Laravel 預設 __自動註冊__ `app/Console/Commands` 目錄下所有指令。
- 若需註冊其他目錄或單一類別，可在 `bootstrap/app.php` 使用 `withCommands`：

```php
// 註冊自訂目錄
->withCommands([
    __DIR__.'/../app/Domain/Orders/Commands', // 讓 Laravel 掃描這個目錄下的所有指令 class
])

// 或註冊單一指令類別
use App\Domain\Orders\Commands\SendEmails;
->withCommands([
    SendEmails::class, // 只註冊這個 class
])

// 也可同時註冊多個目錄與類別
->withCommands([
    __DIR__.'/../app/Domain/Orders/Commands',
    SendEmails::class,
])
```

- **小結**：

 - __預設__ 只會掃描 `app/Console/Commands`
 - 用 `withCommands` 可讓你自由擴充指令來源，適合大型、模組化、DDD、套件化專案

---

### 10.2 *Artisan Events*（`事件監聽`）

- Artisan 執行時會 `dispatch（發送）` 三個主要事件，讓你可以在指令執行的不同階段做 `log、監控、hook` 等進階應用。
- 這些事件可在 `app/Providers/EventServiceProvider.php` 監聽。

 1. `Illuminate\Console\Events\ArtisanStarting`
    - **Artisan CLI 啟動時**（還沒解析指令前）就會發送這個事件
    - 適合用來做全域初始化、全域 log、環境檢查等

 2. `Illuminate\Console\Events\CommandStarting`
    - **每個指令執行前** 都會發送這個事件
    - 可取得指令名稱、參數、選項等資訊
    - 適合用來記錄指令執行紀錄、權限檢查、審計 log 等

 3. `Illuminate\Console\Events\CommandFinished`
    - **每個指令執行結束後** 都會發送這個事件
    - 可取得指令名稱、參數、選項、執行結果、exit code 等
    - 適合用來記錄執行結果、錯誤通知、統計分析等

---

- *監聽方式*：

 - 在 `app/Providers/EventServiceProvider.php` 的 `$listen` 屬性註冊對應事件與監聽器

```php
protected $listen = [
    \Illuminate\Console\Events\ArtisanStarting::class => [
        \App\Listeners\LogArtisanStarting::class,
    ],
    \Illuminate\Console\Events\CommandStarting::class => [
        \App\Listeners\LogCommandStarting::class,
    ],
    \Illuminate\Console\Events\CommandFinished::class => [
        \App\Listeners\LogCommandFinished::class,
    ],
];
```

---

- log（或其他處理）要寫在 `app/Listeners/` 目錄下你自訂的 __監聽器類別__ 裡
- 監聽器類別的 `handle()` 方法就是你寫 log 的地方
- 例如：

 ```php

 // app/Listeners/LogCommandStarting.php
 class LogCommandStarting {
     public function handle(CommandStarting $event) {
         \Log::info('Artisan 指令開始執行', [
             'command' => $event->command,
             'input' => (string) $event->input,
         ]);/
     }
 }
 ```

---

- 這樣 log 會寫到 `storage/logs/laravel.log`（或你設定的 log channel）
- 你也可以在監聽器裡做 `通知、寫資料庫、呼叫 API` 等

- __監聽器（Listener）可取得事件物件__，進行 `log、通知、hook` 等操作
- 例如 `CommandStarting` 事件物件有 `command、input、output` 屬性可用

---

- **實務應用情境**

 - _全域 log_：記錄`所有`指令的執行紀錄與參數
 - _權限控管_：`限制某些指令`只能特定角色執行
 - _錯誤通知_：`指令失敗時`自動發送通知
 - _執行統計_：分析哪些指令`最常被用、平均執行時間`等

- **小結**

 - Artisan 事件讓你能在 CLI _指令生命週期的各階段_ 做進階監控與自動化
 - 只要 __註冊監聽器__ 即可 _無侵入擴充 CLI 行為_，適合大型專案、DevOps、審計需求
 - log、通知等實際邏輯都寫在 `app/Listeners/` 目錄下的監聽器類別 `handle()` 方法裡

---

## 11. **Artisan Console 指令常用輔助方法總整理**

- Laravel Artisan 指令（Command）繼承自 `Illuminate\Console\Command`，內建許多方便的輔助方法，讓你在 CLI 下能輕鬆互動、取得參數、輸出訊息。

- 以下是最常用的方法與詳細註解：

### 1. *取得參數與選項*

```php
$this->argument('name')         // 取得 signature 裡的 {name} 參數值
$this->arguments()              // 取得所有參數（陣列）
$this->option('queue')          // 取得 --queue 選項的值
$this->options()                // 取得所有選項（陣列）
```
---

### 2. *CLI 互動式輸入*

```php
$this->ask('問題', '預設值')                  // 問用戶一個問題，回傳輸入字串
$this->secret('問題')                        // 問用戶一個問題，輸入時不顯示（適合密碼）
$this->confirm('問題', false)                // 問用戶 yes/no 問題，回傳 true/false
$this->anticipate('問題', ['選項'])          // 自動補全，允許自訂輸入
$this->choice('問題', ['選項'], 預設索引, null, $allowMultiple) // 多選一或多選
```

---

### 3. *CLI 輸出訊息*

```php
$this->info('訊息')           // 輸出綠色訊息（成功、提示）
$this->error('錯誤訊息')      // 輸出紅色訊息（錯誤）
$this->warn('警告訊息')       // 輸出黃色訊息（警告）
$this->comment('註解')        // 輸出灰色註解
$this->question('問題')       // 輸出藍色問題
$this->line('純文字')         // 輸出無顏色純文字
$this->newLine(行數)          // 輸出空白行
```

---

### 4. *CLI 表格與進度條*

```php
$this->table(['欄位'], $rows)                         // 輸出表格，適合顯示多筆資料
$this->withProgressBar($items, function($item){ ... }) // 迴圈時自動顯示進度條
$bar = $this->output->createProgressBar($total)        // 手動建立進度條
$bar->start(); $bar->advance(); $bar->finish();        // 控制進度條
```

---

### 5. *其他常用方法*

```php
$this->call('指令', $參數)                 // 在指令內呼叫其他 Artisan 指令
$this->callSilently('指令', $參數)         // 呼叫其他指令但不輸出內容
$this->trap($signal, $callback)           // 註冊訊號處理（如 SIGTERM）
```

---
