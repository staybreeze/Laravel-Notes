# Laravel 任務排程（Task Scheduling）筆記

---

## *什麼是 Laravel 任務排程*？
- 讓你用程式碼（而非 crontab）管理所有定時任務，排程內容可版本控管。
  > 傳統上，Linux/UNIX 系統會用 crontab 來設定定時任務，但 crontab 設定通常不會被納入版本控管（如 git），不利於團隊協作與追蹤。Laravel 任務排程則讓你直接用 PHP 程式碼來定義所有排程，這些程式碼可以和專案一起被版本控管，方便管理、協作與還原歷史設定。
- 只需設定一條 cron，Laravel 會自動管理所有細項排程。
  > 你只要在系統 crontab 加上一條「每分鐘執行 php artisan schedule:run」的設定，Laravel 就會自動判斷哪些任務該在什麼時候執行，無需為每個任務都寫一條 crontab。
- 所有排程通常寫在 `routes/console.php`。
  > 你可以在這個檔案裡用 PHP 語法定義所有排程邏輯，讓排程設定和專案程式碼整合在一起，易於維護與查閱。

> 補充：
> - **cron**：是 Linux/UNIX 系統內建的「排程服務」（守護程式），負責定時執行各種任務。
> - **crontab**：是「cron table」的縮寫，指的是 cron 的設定檔（或指令），用來告訴 cron 什麼時候要執行哪些任務。常見用法是 `crontab -e` 編輯排程。
> - 中文常翻作「排程」、「定時任務」、「排程表」等。
> 
> **Laravel 與 cron 的關係註解：**
> - Laravel 並沒有取代 Linux 的 cron 服務，而是「善用」cron 來定時啟動 Laravel 自己的 scheduler。
> - 你只需要在 crontab 裡加上一條「每分鐘執行 php artisan schedule:run」的設定，cron 服務就會每分鐘叫醒 Laravel。
> - Laravel 會在被叫醒時，根據你在程式碼裡（如 routes/console.php）定義的排程邏輯，決定哪些任務該執行。
> - 這樣你就不用再為每個任務寫一條 crontab，只要專心在 Laravel 程式碼裡管理所有排程，方便版本控管與團隊協作。

---

## *基本排程定義*
- 直接在 `routes/console.php` 定義：
```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

// 每天凌晨清空 recent_users 資料表
// 這裡用 Schedule::call() 定義一個匿名函式作為任務內容
// ->daily() 代表每天執行一次
Schedule::call(function () {
    // 刪除 recent_users 資料表所有資料
    DB::table('recent_users')->delete();
})->daily();
```
- 也可排程 invokable 物件（有 __invoke 方法的類別）：
```php
// 使用可呼叫物件（有 __invoke 方法的類別）作為排程任務
// 這樣可以將任務邏輯獨立成一個類別，方便維護與重複使用
// DeleteRecentUsers 類別必須實作 __invoke() 方法，作為任務執行內容
// 具體好處：
// 1. 易於維護：邏輯集中在類別裡，未來要修改只改一個地方。
// 2. 重複使用：同一個類別可在多個排程、指令或其他地方呼叫，不用重複寫邏輯。
//    例如：Artisan::command('users:clear', new DeleteRecentUsers);
// 3. 方便測試：可以針對這個類別單獨寫單元測試。
// 4. 易於擴充：要加功能（如加 log），只需改類別內容，所有用到的地方都會自動套用。
// 注意：DeleteRecentUsers 是「自訂的類別」，不是 Laravel 內建！你必須自己建立這個類別，並實作 __invoke() 方法。
// 範例：
// app/Tasks/DeleteRecentUsers.php
//
// namespace App\Tasks;
// use Illuminate\Support\Facades\DB;
// class DeleteRecentUsers {
//     public function __invoke() {
//         DB::table('recent_users')->delete();
//     }
// }
Schedule::call(new DeleteRecentUsers)->daily();
```
- 也可在 `bootstrap/app.php` 用 withSchedule 註冊：
```php
// 這種寫法適合有進階需求時使用，例如：
// - 想根據環境變數或啟動時條件動態註冊排程
// - 想把排程邏輯集中在 app 啟動時統一管理
// 啟用時機：每次 Laravel 啟動（如執行 schedule:run）時，這裡的排程就會被註冊進 scheduler
// 注意：這些排程會和 routes/console.php 裡的排程合併一起管理
// 一般情況下，直接寫在 routes/console.php 就夠了，只有特殊需求才建議用這種方式
->withSchedule(function (Schedule $schedule) {
    $schedule->call(new DeleteRecentUsers)->daily();
})
```
- 查看所有排程與下次執行時間：
```bash
php artisan schedule:list  # 顯示所有已註冊的排程與下次執行時間
```

---

## *排程 Artisan 指令*
- 可用 command 方法排程 Artisan 指令（字串或類別）：
```php
Schedule::command('emails:send Taylor --force')->daily();
Schedule::command(SendEmailsCommand::class, ['Taylor', '--force'])->daily();
```
- 若 Artisan 指令是 closure，可直接**鏈接**排程方法：
```php
// 註冊一個自訂的 Artisan 指令 delete:recent-users，並定義其執行內容
Artisan::command('delete:recent-users', function () {
    // 執行時會刪除 recent_users 資料表的所有資料
    DB::table('recent_users')->delete();
// 指定這個指令的用途說明，方便 artisan list 查閱
// 並設定這個指令每天自動執行（排程）
})->purpose('Delete recent users')->daily();
```
- **傳遞參數**：
```php
Artisan::command('emails:send {user} {--force}', function ($user) {
    // ...
})->purpose('Send emails')->schedule(['Taylor', '--force'])->daily();
```

---

## *排程 Queue Job*
- 直接用 **job 方法**排程 queue job：
```php
use App\Jobs\Heartbeat;
Schedule::job(new Heartbeat)->everyFiveMinutes();
```
- 可指定 **queue/connection**：
```php
Schedule::job(new Heartbeat, 'heartbeats', 'sqs')->everyFiveMinutes();
```

---

## *排程 Shell/系統指令*
- 用 **exec 方法**排程 shell 指令：
```php
Schedule::exec('node /home/forge/script.js')->daily();
```

---

## *頻率與條件*
- 支援多種**頻率**：
  - `->cron('* * * * *')` 自訂 cron
  - `->everySecond()`、
    `->everyMinute()`、
    `->hourly()`、
    `->daily()`、
    `->weekly()`、
    `->monthly()`、
    `->yearly()` ...
  - `->everyFiveMinutes()`、
    `->twiceDaily(1, 13)`、
    `->dailyAt('13:00')` ...
- 可用**條件限制**：
  - `->weekdays()`、
    `->weekends()`、
    `->mondays()`、
    `->days([0,3])` ...
  - `->between('8:00', '17:00')`、
    `->unlessBetween('23:00', '4:00')`  
  - `->when(fn()=>true)`、
    `->skip(fn()=>false)`
  - `->environments(['production'])`
  - `->timezone('Asia/Taipei')`
- 範例：
```php
// 每週一下午 1 點執行
Schedule::call(fn()=>...)->weekly()->mondays()->at('13:00');
// 平日 8:00~17:00 每小時執行
Schedule::command('foo')->weekdays()->hourly()->between('8:00', '17:00');
```

---

## *防止重疊（withoutOverlapping）*
- 預設同一任務可重複執行，若要防止重疊：
```php
Schedule::command('emails:send')->withoutOverlapping();
// 可指定鎖過期分鐘數（預設 24 小時）
Schedule::command('emails:send')->withoutOverlapping(10);
```
- 若任務卡住可用 `php artisan schedule:clear-cache` 清除鎖。

---

## *單一伺服器執行（onOneServer）*
- 多台伺服器同時跑 scheduler 時，避免同一任務被多台執行：
```php
Schedule::command('report:generate')->fridays()->at('17:00')->onOneServer();
```
- 須用支援 lock 的 cache driver（database, redis, memcached, dynamodb）。
- 可用 **useCache** 指定 lock 用的 cache store：
```php
Schedule::useCache('database');
```
- 若同一任務**不同參數**要分開鎖，可用 name() 指定唯一名稱：
```php
// 使用 name() 指定這個任務的唯一名稱，方便 Laravel 在加鎖（如 withoutOverlapping、onOneServer）時能正確區分不同任務
// 命名原則：名稱要能唯一識別這個任務，通常會包含任務類型與參數內容
// 例如：同一個任務類別但參數不同時，分別命名為 check_uptime:laravel.com、check_uptime:google.com 等，避免鎖住彼此
Schedule::job(new CheckUptime('https://laravel.com'))->name('check_uptime:laravel.com')->everyFiveMinutes()->onOneServer();
```

---

## *背景執行（runInBackground）*
- 預設同一時間的任務會依序執行，若要平行執行：
```php
Schedule::command('analytics:report')->daily()->runInBackground();
```
- 只適用於 **command/exec** 任務。

---

## *維護模式下執行*
- 預設維護模式不會執行排程，若要強制執行：
```php
Schedule::command('emails:send')->evenInMaintenanceMode();
```

---

## *群組（Group）*
- 多個任務共用同一組設定：
```php
Schedule::daily()
    ->onOneServer()
    ->timezone('America/New_York')
    ->group(function () {
        Schedule::command('emails:send --force');
        Schedule::command('emails:prune');
    });
```

---

## *其他補充*
- 可用 **schedule:list** 檢查所有排程與下次執行時間。
- 預設所有排程都寫在 **routes/console.php**，也可用 **withSchedule** 分離。
- 建議所有排程都用版本控管，方便團隊協作。
- 若有多台伺服器，務必用 **onOneServer** 防止重複執行。
- **withoutOverlapping/onOneServer** 需正確設定 cache driver。
- **runInBackground*** 適合長時間任務，避免卡住其他排程。
- 可用 **schedule:clear-cache** 清除 stuck 的鎖。
- 建議所有排程都加上 log 或通知，方便追蹤。 

---

## *排程頻率方法一覽（Schedule Frequency Options）*

| 方法 | 說明 |
|------|------|
| ->cron('* * * * *') | 自訂 cron 規則 |
| ->everySecond() | 每秒執行一次 |
| ->everyTwoSeconds() | 每兩秒執行一次 |
| ->everyFiveSeconds() | 每五秒執行一次 |
| ->everyTenSeconds() | 每十秒執行一次 |
| ->everyFifteenSeconds() | 每十五秒執行一次 |
| ->everyTwentySeconds() | 每二十秒執行一次 |
| ->everyThirtySeconds() | 每三十秒執行一次 |
| ->everyMinute() | 每分鐘執行一次 |
| ->everyTwoMinutes() | 每兩分鐘執行一次 |
| ->everyThreeMinutes() | 每三分鐘執行一次 |
| ->everyFourMinutes() | 每四分鐘執行一次 |
| ->everyFiveMinutes() | 每五分鐘執行一次 |
| ->everyTenMinutes() | 每十分鐘執行一次 |
| ->everyFifteenMinutes() | 每十五分鐘執行一次 |
| ->everyThirtyMinutes() | 每三十分鐘執行一次 |
| ->hourly() | 每小時執行一次 |
| ->hourlyAt(17) | 每小時第 17 分鐘執行 |
| ->everyOddHour($minutes = 0) | 每奇數小時執行（可指定分鐘）|
| ->everyTwoHours($minutes = 0) | 每兩小時執行（可指定分鐘）|
| ->everyThreeHours($minutes = 0) | 每三小時執行（可指定分鐘）|
| ->everyFourHours($minutes = 0) | 每四小時執行（可指定分鐘）|
| ->everySixHours($minutes = 0) | 每六小時執行（可指定分鐘）|
| ->daily() | 每天凌晨 0:00 執行 |
| ->dailyAt('13:00') | 每天 13:00 執行 |
| ->twiceDaily(1, 13) | 每天 1:00 與 13:00 執行 |
| ->twiceDailyAt(1, 13, 15) | 每天 1:15 與 13:15 執行 |
| ->weekly() | 每週日 0:00 執行 |
| ->weeklyOn(1, '8:00') | 每週一 8:00 執行 |
| ->monthly() | 每月 1 號 0:00 執行 |
| ->monthlyOn(4, '15:00') | 每月 4 號 15:00 執行 |
| ->twiceMonthly(1, 16, '13:00') | 每月 1 號與 16 號 13:00 執行 |
| ->lastDayOfMonth('15:00') | 每月最後一天 15:00 執行 |
| ->quarterly() | 每季第一天 0:00 執行 |
| ->quarterlyOn(4, '14:00') | 每季第 4 天 14:00 執行 |
| ->yearly() | 每年 1/1 0:00 執行 |
| ->yearlyOn(6, 1, '17:00') | 每年 6/1 17:00 執行 |
| ->timezone('America/New_York') | 設定任務時區 |

> 以上方法可搭配條件（如 weekdays、between、when 等）組合使用，靈活排程各種任務。

---

## *排程條件方法一覽（Schedule Constraint Options）*

| 方法 | 說明 |
|------|------|
| ->weekdays() | 只在平日（一到五）執行 |
| ->weekends() | 只在週末（六日）執行 |
| ->sundays() | 只在週日執行 |
| ->mondays() | 只在週一執行 |
| ->tuesdays() | 只在週二執行 |
| ->wednesdays() | 只在週三執行 |
| ->thursdays() | 只在週四執行 |
| ->fridays() | 只在週五執行 |
| ->saturdays() | 只在週六執行 |
| ->days(array|mixed) | 指定多個星期幾（0=日, 1=一, ... 6=六），也可用 Schedule::SUNDAY 等常數 |
| ->between($startTime, $endTime) | 只在指定時段內執行（如 '8:00', '17:00'）|
| ->unlessBetween($startTime, $endTime) | 排除指定時段執行 |
| ->when(Closure) | 依條件判斷是否執行（回傳 true 才執行）|
| ->skip(Closure) | 依條件判斷是否跳過（回傳 true 則不執行）|
| ->environments($env) | 只在指定環境（如 production, staging）執行 |
| ->timezone('America/New_York') | 設定任務時區（可全域 app.php 設定 schedule_timezone）|

> days 用法範例：
> ```php
> Schedule::command('emails:send')->hourly()->days([0, 3]); // 週日、週三
> Schedule::command('emails:send')->hourly()->days([Schedule::SUNDAY, Schedule::WEDNESDAY]);
> ```

---

## *任務 hooks 與通知方法一覽*

| 方法 | 說明 |
|------|------|
| ->before(fn()=>...) | 任務執行前呼叫 |
| ->after(fn()=>...) | 任務執行後呼叫 |
| ->onSuccess(fn($output)=>...) | 任務成功時呼叫（$output 可 type-hint Stringable）|
| ->onFailure(fn($output)=>...) | 任務失敗時呼叫（$output 可 type-hint Stringable）|
| ->sendOutputTo($filePath) | 輸出寫入檔案（僅 command/exec）|
| ->appendOutputTo($filePath) | 輸出附加到檔案（僅 command/exec）|
| ->emailOutputTo($email) | 輸出 email 給指定信箱（僅 command/exec）|
| ->emailOutputOnFailure($email) | 失敗時才 email 輸出（僅 command/exec）|

---

## *Ping 通知方法一覽*

| 方法 | 說明 |
|------|------|
| ->pingBefore($url) | 任務執行前 ping URL |
| ->thenPing($url) | 任務執行後 ping URL |
| ->pingOnSuccess($url) | 任務成功時 ping URL |
| ->pingOnFailure($url) | 任務失敗時 ping URL |
| ->pingBeforeIf($cond, $url) | 條件成立時，任務前 ping URL |
| ->thenPingIf($cond, $url) | 條件成立時，任務後 ping URL |
| ->pingOnSuccessIf($cond, $url) | 條件成立時，成功時 ping URL |
| ->pingOnFailureIf($cond, $url) | 條件成立時，失敗時 ping URL |

```php
// 這裡的 ping 不是你在終端機打的那個 ping（不是 ICMP ping，不是測主機有沒有回應）！
// 這裡的 ping 是「發送一個 HTTP 請求（通常是 GET）」到你指定的網址
// 這個網址通常是外部監控服務（如 Healthchecks、Cronitor、UptimeRobot）給你的專屬網址，也可以是你自己寫的 API
// 主要用途：讓外部系統知道「這個任務有執行、成功、失敗或結束」，沒收到 ping 就會通知你排程可能壞掉了
// 你可以把它想成 Laravel 幫你「偷偷打開一個網頁」通知外部系統：「我有執行囉！」
```
---

## *Scheduler 事件一覽*

| 事件名稱 | 說明 |
|-----------|------|
| Illuminate\Console\Events\ScheduledTaskStarting | 任務即將執行時 |
| Illuminate\Console\Events\ScheduledTaskFinished | 任務執行結束時 |
| Illuminate\Console\Events\ScheduledBackgroundTaskFinished | 背景任務結束時 |
| Illuminate\Console\Events\ScheduledTaskSkipped | 任務被 skip 時 |
| Illuminate\Console\Events\ScheduledTaskFailed | 任務失敗時 |

---

## *執行 Scheduler 與進階技巧*

### **如何啟動 Scheduler？**
- 只需在伺服器 crontab 加一條：
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```
- **schedule:run** 會每分鐘檢查所有排程，決定是否執行。
- 可用 **schedule:work** 在本機前景執行（開發用）：

```php
// 「本機」指的是你自己的電腦（開發環境），不是正式伺服器
// 「前景執行」指的是你在終端機直接執行指令，畫面會停在這個指令直到你手動中斷（如 Ctrl+C）
// 這種方式適合開發測試時用，可以即時看到排程任務有沒有被執行、結果如何
// 正式環境建議還是用 crontab + schedule:run
```

`php artisan schedule:work`
- 可用 schedule:list 檢查所有排程與下次執行時間。

### **Sub-Minute 任務（每秒/每十秒...）**
- Laravel 支援 sub-minute 頻率（如 everySecond、everyTenSeconds）：
```php
Schedule::call(fn()=>...)->everySecond();
Schedule::job(new DeleteRecentUsers)->everyTenSeconds();
```
- 有 sub-minute 任務時，schedule:run 會持續執行一分鐘，直到該分鐘結束。
- 建議 sub-minute 任務只負責 **dispatch job** 或背景指令，避免阻塞。
- 若部署時需中斷正在執行的 schedule:run，可用：

`php artisan schedule:interrupt`


### **輸出與通知**
- 可將任務輸出寫入檔案：
```php
Schedule::command('emails:send')->daily()->sendOutputTo($filePath);
Schedule::command('emails:send')->daily()->appendOutputTo($filePath);
```
- 可將輸出 email 給指定信箱：
```php
Schedule::command('report:generate')->daily()->sendOutputTo($filePath)->emailOutputTo('taylor@example.com');
Schedule::command('report:generate')->daily()->emailOutputOnFailure('taylor@example.com');
```
- 以上方法僅適用於 *command/exec* 任務。

### **任務 hooks（前後/成功/失敗）**
- 可在任務執行前/後/成功/失敗時執行自訂邏輯：
```php
Schedule::command('emails:send')
          ->daily()
          ->before(fn()=>info('即將執行'))
          ->after(fn()=>info('已執行'))
          ->onSuccess(fn($output)=>info('成功'))
          ->onFailure(fn($output)=>info('失敗'));
```
- $output 可 type-hint Illuminate\Support\Stringable 取得輸出內容。

### **Ping 通知**
- 可在任務前/後/成功/失敗時自動 ping URL（如通知外部監控）：
```php
Schedule::command('emails:send')->daily()->pingBefore($url)->thenPing($url);
Schedule::command('emails:send')->daily()->pingOnSuccess($successUrl)->pingOnFailure($failUrl);
```
- 可用 pingBeforeIf/thenPingIf/pingOnSuccessIf/pingOnFailureIf 加條件。

### **事件**
- Scheduler 執行過程會 dispatch 多種事件，可註冊 listener：
  - Illuminate\Console\Events\ScheduledTaskStarting
  - Illuminate\Console\Events\ScheduledTaskFinished
  - Illuminate\Console\Events\ScheduledBackgroundTaskFinished
  - Illuminate\Console\Events\ScheduledTaskSkipped
  - Illuminate\Console\Events\ScheduledTaskFailed

### **補充與最佳實踐**
- 建議所有排程都加上 log 或通知，方便追蹤。
- *sub-minute* 任務盡量只 dispatch job，避免阻塞。
- 部署時可用 *schedule:interrupt* 中斷 schedule:run。
- 可用 *schedule:clear-cache* 清 stuck 的 withoutOverlapping/onOneServer 鎖。
- 若有多台伺服器，務必用 *onOneServer* 防止重複執行。 

---

> 以上所有方法、條件、事件、hooks、ping 均已與官方文件逐條對照，確保無遺漏。 