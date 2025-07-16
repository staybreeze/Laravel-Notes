<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use App\Jobs\Heartbeat;
use App\Tasks\DeleteRecentUsers;

// --- Closure 任務：每天凌晨清空 recent_users ---
Schedule::call(function () {
    DB::table('recent_users')->delete();
})->daily();

// --- Invokable 物件 ---
// class DeleteRecentUsers { public function __invoke() { ... } }
Schedule::call(new DeleteRecentUsers)->daily();// 每天執行 DeleteRecentUsers 任務


// --- Artisan 指令（字串/類別）---
Schedule::command('emails:send Taylor --force')->dailyAt('1:00');
// Schedule::command(SendEmailsCommand::class, ['Taylor', '--force'])->daily();

// --- Artisan Closure 指令 ---
Artisan::command('delete:recent-users', function () {
    DB::table('recent_users')->delete();
})->purpose('Delete recent users')->daily();

// --- Queue Job ---
Schedule::job(new Heartbeat)->everyFiveMinutes();
// 指定 queue/connection
// Schedule::job(new Heartbeat, 'heartbeats', 'sqs')->everyFiveMinutes();

// --- Shell 指令 ---
Schedule::exec('node /home/forge/script.js')->daily();

// --- 頻率與條件 ---
// 每週一下午 1 點執行
Schedule::call(fn()=>info('每週一下午1點'))->weekly()->mondays()->at('13:00');
// 平日 8:00~17:00 每小時執行
Schedule::command('foo')->weekdays()->hourly()->between('8:00', '17:00');

// --- 防止重疊 ---
Schedule::command('emails:send')->withoutOverlapping(10);

// --- 單一伺服器執行 ---
Schedule::command('report:generate')->fridays()->at('17:00')->onOneServer();
// 指定 lock cache store
// Schedule::useCache('database');

// --- 背景執行 ---
Schedule::command('analytics:report')->daily()->runInBackground();

// --- Artisan 指令（closure 範例）---
// - 註冊一個自訂的 Artisan 指令 inspire，執行時會顯示一句勵志語錄
// - 你可以在終端機輸入 php artisan inspire 來執行這個指令
// - ->purpose() 用來設定這個指令的用途說明，會顯示在 php artisan list
// - $this->comment(Inspiring::quote()) 會輸出一段隨機勵志語錄到終端機
//
 Artisan::command('inspire', function () {
       // 輸出一段隨機勵志語錄到終端機
       $this->comment(Inspiring::quote());
   })->purpose('Display an inspiring quote');

// --- 維護模式下強制執行 ---
Schedule::command('emails:send')->evenInMaintenanceMode();

// --- 群組 ---
Schedule::daily()
    ->onOneServer()
    ->timezone('America/New_York')
    ->group(function () {
        Schedule::command('emails:send --force');
        Schedule::command('emails:prune');
    });
