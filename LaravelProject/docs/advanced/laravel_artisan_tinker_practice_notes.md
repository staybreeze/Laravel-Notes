# *Laravel Artisan 與 Tinker 實作範例*

---

## 1. **Artisan 常用指令實作**

### 1.1 *路由列表*
```bash
php artisan route:list
```

### 1.2 *快取清除*
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 1.3 *執行資料庫遷移與填充*
```bash
php artisan migrate
php artisan db:seed
php artisan migrate:refresh --seed
```

### 1.4 *建立 Model、Controller、Migration*
```bash
php artisan make:model Post -mcr
# 會同時建立 Model、Migration、Controller、Resource
```

### 1.5 *建立自訂 Artisan 指令*
```bash
php artisan make:command HelloWorld
```

產生後會在 `app/Console/Commands/HelloWorld.php`，範例內容：
```php
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

執行：
```bash
php artisan hello:world
```

---

## 2. **Tinker 常用互動操作**

### 2.1 *進入 Tinker*
```bash
php artisan tinker
```

### 2.2 *查詢所有使用者*
```php
App\Models\User::all();
```

### 2.3 *新增一筆資料*
```php
$user = new App\Models\User();
$user->name = 'Vincent';
$user->email = 'vincent@example.com';
$user->password = bcrypt('secret');
$user->save();
```

### 2.4 *查詢單一資料*
```php
$user = App\Models\User::find(1);
$user->email;
```

### 2.5 *更新資料*
```php
$user = App\Models\User::find(1);
$user->name = '新名字';
$user->save();
```

### 2.6 *刪除資料*
```php
$user = App\Models\User::find(1);
$user->delete();
```

### 2.7 *使用 Collection 與 Helper*
```php
collect([1,2,3])->map(fn($v) => $v * 2);
str('laravel')->upper();
```

---

## 3. **Tinker 進階應用**

### 3.1 *派送 Job*
```php
use App\Jobs\SendEmailJob;
Bus::dispatch(new SendEmailJob($user));
```

### 3.2 *觸發 Event*
```php
use App\Events\UserRegistered;
event(new UserRegistered($user));
```

### 3.3 *操作關聯資料*
```php
$user = App\Models\User::find(1);
$user->posts; // 取得該使用者的所有文章
```

### 3.4 *使用 Query Builder*
```php
DB::table('users')->where('email', 'like', '%@gmail.com')->get();
```

---

## 4. **Artisan 與 Tinker 實作小技巧**

- Artisan 指令可用 `--help` 查看所有參數
- Tinker 支援多行輸入，可用 `Shift+Enter` 換行
- 可在 Tinker 直接呼叫 Laravel 服務、Facade、Helper
- Tinker 支援自動補全（部分終端機需設定）

---

## 5. **自訂 Artisan 指令實作**

### 5.1 *建立自訂指令*
```bash
php artisan make:command SendEmails
```

### 5.2 *指令結構與範例*
```php
namespace App\Console\Commands;

use App\Models\User;
use App\Support\DripEmailer;
use Illuminate\Console\Command;

class SendEmails extends Command
{
    protected $signature = 'mail:send {user}';
    protected $description = 'Send a marketing email to a user';

    public function handle(DripEmailer $drip): void
    {
        $drip->send(User::find($this->argument('user')));
    }
}
```

### 5.3 *狀態碼與錯誤處理*
```php
$this->error('Something went wrong.');
return 1;
// 或
$this->fail('Something went wrong.');
```

### 5.4 *閉包式（Closure-based）指令*
```php
// routes/console.php
use Illuminate\Support\Facades\Artisan;

// 這裡直接用 closure 定義一個指令，不需額外建立 Command 類別
Artisan::command('greet {name}', function (string $name) {
    // $name 會自動對應 signature 裡的 {name} 參數
    // $this 代表底層 Command 實例，可用 info/argument/option/call 等輔助方法
    $this->info("Hello, {$name}!");
})->purpose('Say hello to someone'); // 用 purpose() 設定指令描述，顯示於 php artisan list
```
- **特點**：
 - 不需建立 class，適合 *臨時、簡單、教學用指令*
 - 支援 *type-hint* 依賴注入（如 function (DripEmailer $drip, string $user)）
 - closure 內 *$this 可用所有 Command 輔助方法*
 - signature 語法與 class 指令一致，支援 arguments/options/描述
 - 可同時存在多個 closure-based 與 class 指令

### 5.5 *進階：Isolatable 指令*
```php
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class SendEmails extends Command implements Isolatable
{
    // ... signature, description, handle() ...
    public function isolatableId(): string
    {
        return $this->argument('user');
    }
    public function isolationLockExpiresAt(): \DateTimeInterface|\DateInterval
    {
        return now()->addMinutes(5);
    }
}
```
執行：
```bash
php artisan mail:send 1 --isolated
php artisan mail:send 1 --isolated=12
```

### 5.6 *實作練習*
- 建立 HelloWorld 指令，顯示 Hello World
- 在 routes/console.php 實作 greet {name} 指令，顯示問候語
- 實作一個 Isolatable 指令，模擬長時間任務，測試 --isolated

### 5.7 *範例*
#### **HelloWorld 指令**
```php
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

#### **Closure-based 指令**
```php
// routes/console.php
Artisan::command('greet {name}', function (string $name) {
    $this->info("Hello, {$name}!");
})->purpose('Say hello to someone');
```

---

## 6. **指令輸入定義與互動提示實作**

### 6.1 *signature 定義範例*
```php
protected $signature = 'mail:send
                        {user : The ID of the user}
                        {--queue : Whether the job should be queued}
                        {--Q|queue=default : Queue name (shortcut Q)}
                        {--id=* : 多個 id}';
```

### 6.2 *使用方式*
```bash
php artisan mail:send 1 --queue
php artisan mail:send 1 --queue=default
php artisan mail:send 1 -Qdefault
php artisan mail:send 1 2 3
php artisan mail:send --id=1 --id=2
```

### 6.3 *PromptsForMissingInput 互動提示實作*

#### **基本自動互動**
```php
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class SendEmails extends Command implements PromptsForMissingInput
{
    protected $signature = 'mail:send {user}';
    // ...
}
```

#### **自訂互動問題**
```php
protected function promptForMissingArgumentsUsing(): array
{
    return [
        'user' => 'Which user ID should receive the mail?',
    ];
}
```

#### **加入 placeholder**
```php
protected function promptForMissingArgumentsUsing(): array
{
    return [
        'user' => ['Which user ID should receive the mail?', 'E.g. 123'],
    ];
}
```

#### **完全自訂互動（closure）**
```php
use App\Models\User;
use function Laravel\Prompts\search;

protected function promptForMissingArgumentsUsing(): array
{
    return [
        'user' => fn () => search(
            label: 'Search for a user:',
            placeholder: 'E.g. Taylor Otwell',
            options: fn ($value) => strlen($value) > 0
                ? User::where('name', 'like', "%{$value}%")->pluck('name', 'id')->all()
                : []
        ),
    ];
}
```

#### **afterPromptingForMissingArguments 進一步互動**
```php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Laravel\Prompts\confirm;

protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
{
    $input->setOption('queue', confirm(
        label: 'Would you like to queue the mail?',
        default: $this->option('queue')
    ));
}
```

---

## 7. **指令 I/O 輸入與輸出實作**

### 7.1 *取得 Arguments 與 Options*
```php
// 取得單一 argument
$userId = $this->argument('user');
// 取得所有 arguments
$arguments = $this->arguments();
// 取得單一 option
$queueName = $this->option('queue');
// 取得所有 options
$options = $this->options();
```

### 7.2 *互動式輸入*
```php
// 一般提問
$name = $this->ask('What is your name?');
$name = $this->ask('What is your name?', 'Taylor');
// 密碼/隱藏輸入
$password = $this->secret('What is the password?');
// 確認
if ($this->confirm('Do you wish to continue?')) { /* ... */ }
if ($this->confirm('Do you wish to continue?', true)) { /* 預設為是 */ }
// 自動補全
$name = $this->anticipate('What is your name?', ['Taylor', 'Dayle']);
$name = $this->anticipate('What is your address?', function (string $input) {
    return Address::whereLike('name', "{$input}%")->limit(5)->pluck('name')->all();
});
// 多選一
$name = $this->choice('What is your name?', ['Taylor', 'Dayle'], 0);
// 多選
$names = $this->choice('Select names', ['Taylor', 'Dayle'], null, null, true);
```

### 7.3 *輸出訊息*
```php
$this->info('The command was successful!');
$this->error('Something went wrong!');
$this->line('Display this on the screen');
$this->comment('This is a comment');
$this->question('This is a question');
$this->warn('This is a warning');
$this->newLine();
$this->newLine(3);
```

### 7.4 *表格輸出*
```php
use App\Models\User;
$this->table(
    ['Name', 'Email'],
    User::all(['name', 'email'])->toArray()
);
```

### 7.5 *進度條*
```php
// 自動進度條
$users = $this->withProgressBar(User::all(), function (User $user) {
    $this->performTask($user);
});
// 手動進度條
$users = App\Models\User::all();
$bar = $this->output->createProgressBar(count($users));
$bar->start();
foreach ($users as $user) {
    $this->performTask($user);
    $bar->advance();
}
$bar->finish();
```

---

## 8. **進階主題實作：Stub 自訂與 Signal Handling**

### 8.1 *Stub Customization*
```bash
php artisan stub:publish
# 修改 stubs/ 目錄下的檔案，之後 make:controller 等都會用自訂內容
```

### 8.2 *Signal Handling*
```php
// 指令內攔截 SIGTERM、SIGQUIT
$this->trap([SIGTERM, SIGQUIT], function (int $signal) {
    $this->shouldKeepRunning = false;
    dump($signal); // SIGTERM / SIGQUIT
});
// 常用於長時間任務的優雅終止
```

---

## 9. **指令註冊與 Artisan 事件實作**

### 9.1 *withCommands 註冊範例*
```php
// bootstrap/app.php
->withCommands([
    __DIR__.'/../app/Domain/Orders/Commands',
    App\Domain\Orders\Commands\SendEmails::class,
])
```

### 9.2 *Artisan Events 監聽範例*
```php
// app/Providers/EventServiceProvider.php
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
// 監聽器內可記錄 log、統計、hook 等
```

---
