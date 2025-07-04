<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use Illuminate\Support\Facades\View;
use App\View\Composers\ProfileComposer;

// -----------------------------------------------------------------------------
// Rate Limiting（速率限制）
// -----------------------------------------------------------------------------
// 定義全域與 API 速率限制器
// - 'api'：每分鐘 60 次，依 user id 或 IP 區分
// - 'global'：每分鐘 1000 次，所有請求共用
// - 'uploads'：VIP 不限流，一般用戶每分鐘 100 次
// 超過限制時自動回傳 429，可自訂回應內容
// -----------------------------------------------------------------------------
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        Route::pattern('id', '[0-9]+'); // 全專案所有 {id} 參數都只允許數字
        URL::defaults(['locale' => 'zh-TW']); // 全專案所有 {locale} 參數預設為 zh-TW

        // 顯式綁定：所有 {user} 參數都自動注入 User 模型物件
        Route::model('user', User::class);

        // 自訂解析邏輯：用 name 欄位查找 User
        Route::bind('user', function (string $value) {
            return User::where('name', $value)->firstOrFail();
        });

        // -----------------------------------------------------------------------------
        // Rate Limiting（速率限制）
        // -----------------------------------------------------------------------------
        // 定義全域與 API 速率限制器
        // - 'api'：每分鐘 60 次，依 user id 或 IP 區分
        // - 'global'：每分鐘 1000 次，所有請求共用
        // - 'uploads'：VIP 不限流，一般用戶每分鐘 100 次
        // 超過限制時自動回傳 429，可自訂回應內容
        // -----------------------------------------------------------------------------
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(1000)->response(function (Request $request, array $headers) {
                return response('自訂回應內容...', 429, $headers);
            });
        });

        RateLimiter::for('uploads', function (Request $request) {
            return $request->user() && method_exists($request->user(), 'vipCustomer') && $request->user()->vipCustomer()
                ? Limit::none()
                : Limit::perMinute(100);
        });

        // -----------------------------------------------------------------------------
        // 進階速率限制範例
        // -----------------------------------------------------------------------------
        // 1. 分段限制：依 user id 或 IP 分流
        // 2. 多重限制：同時套用多個限制（如每分鐘/每天）
        // 3. 唯一分段鍵：by() 參數建議加前綴避免衝突
        // -----------------------------------------------------------------------------

        // -----------------------------------------------------------------------------
        // uploads-segment：上傳功能分段速率限制
        // 已登入用戶：每人每分鐘 100 次（依 user id 分段）
        // 未登入訪客：每個 IP 每分鐘 10 次（依 IP 分段）
        // by() 用來區分不同用戶或 IP，避免共用同一限制。
        // -----------------------------------------------------------------------------
        RateLimiter::for('uploads-segment', function (Request $request) {
            // 已登入用戶：每人每分鐘 100 次，未登入：每 IP 每分鐘 10 次
            return $request->user()
                ? Limit::perMinute(100)->by('user:' . $request->user()->id)
                : Limit::perMinute(10)->by('ip:' . $request->ip());
        });

        // -----------------------------------------------------------------------------
        // login：登入功能多重速率限制
        // 全站每分鐘 500 次（不分對象）
        // 每個 email 每分鐘 3 次（依 email 分段，防止暴力登入）
        // 回傳陣列代表同時套用多個限制。
        // by() 用 email 當 key，確保每個 email 有自己的限制。
        // -----------------------------------------------------------------------------
        RateLimiter::for('login', function (Request $request) {
            // 全站每分鐘 500 次，每個 email 每分鐘 3 次
            return [
                Limit::perMinute(500),
                Limit::perMinute(3)->by('email:' . $request->input('email')),
            ];
        });

        // -----------------------------------------------------------------------------
        // uploads-advanced：上傳功能進階多重速率限制
        // 依 user id 或 IP 分段，同時限制每分鐘與每天
        // Limit::perMinute(10)：每分鐘 10 次（by key 前綴 minute:）
        // Limit::perDay(1000)：每天 1000 次（by key 前綴 day:）
        // by() 前綴讓每個限制的 key 唯一，避免不同時間單位互相干擾。
        // -----------------------------------------------------------------------------
        RateLimiter::for('uploads-advanced', function (Request $request) {
            // 同時限制每分鐘與每天，每個 by 值加前綴
            $key = $request->user() ? $request->user()->id : $request->ip();
            return [
                Limit::perMinute(10)->by('minute:' . $key),
                Limit::perDay(1000)->by('day:' . $key),
            ];
        });

        // -----------------------------------------------------------------------------
        // Resource Route Verbs 本地化（資源路由動詞本地化）
        // -----------------------------------------------------------------------------
        // 預設 Route::resource 會用英文 create/edit 動詞
        // 可用 resourceVerbs 方法自訂本地語系（如西班牙文、中文等）
        // 建議在 boot 方法開頭設定
        Route::resourceVerbs([
            'create' => 'crear', // 例如西班牙文
            'edit' => 'editar',
        ]);
        // 註冊後，資源路由會產生 /publicacion/crear、/publicacion/{publicaciones}/editar 等本地化 URI
        // Laravel 的 pluralizer 支援多語系，可依需求調整

        // -----------------------------------------------------------------------------
        // [Response Macro（自訂回應輔助方法）]
        // -----------------------------------------------------------------------------
        // 1. 定義：
        //    - 你可以用 Response::macro 定義自訂回應方法，方便在多個路由/控制器重複使用。
        //    - macro 名稱為第一參數，closure 為第二參數，closure 內可自訂回應邏輯。
        //
        // 2. 實作範例：
        //    Response::macro('caps', function (string $value) {
        //        return Response::make(strtoupper($value));
        //    });
        //    // 使用：return response()->caps('foo'); // 回傳 'FOO'
        //
        // 3. 方法說明：
        //    - Response::macro('name', fn...)：註冊自訂回應方法。
        //    - response()->macroName(...)：呼叫自訂 macro。
        // -----------------------------------------------------------------------------
        \Illuminate\Support\Facades\Response::macro('caps', function (string $value) {
            return \Illuminate\Support\Facades\Response::make(strtoupper($value));
        });

        // -----------------------------------------------------------------------------
        // [全域共用資料範例]
        // 用 View::share('key', 'value') 讓所有視圖都能取得 key 變數
        // 建議放在 boot() 方法內，讓所有 Blade 視圖都能直接用 $key
        // -----------------------------------------------------------------------------
        View::share('key', '這是全域共用變數');

        // -----------------------------------------------------------------------------
        // [View Composer 註冊範例]
        // 將 ProfileComposer 綁定到 admin.profile 視圖
        // 每次渲染 admin.profile.blade.php 時，會自動注入 count 變數
        // -----------------------------------------------------------------------------
        View::composer('admin.profile', ProfileComposer::class);
    }
}
