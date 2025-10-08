# *Laravel Package Development（套件開發） 筆記*

---

## 1. **簡介**（Introduction）

Laravel 套件是`擴充功能`的主要方式。
分為：
- *Stand-alone（獨立）*：如 `Carbon`、`Pest`，任何 PHP 專案皆可用
- *Laravel 專用*：可含 `route、controller、view、config` 等

---

## 2. **Facade 與 Contracts 注意事項**（A Note on Facades）

- 應用程式可用 `facade` 或 `contract`，測試性相近
- *套件開發*建議用 `contract`，測試可用 Orchestral Testbench

---

## 3. **Package Discovery**（自動註冊）

- 在 composer.json `extra.laravel.providers`/`aliases` 註冊 *provider/facade*，Laravel 會自動載入

```json
"extra": {
    "laravel": {
        "providers": [
            "Barryvdh\\Debugbar\\ServiceProvider"
        ],
        "aliases": {
            "Debugbar": "Barryvdh\\Debugbar\\Facade"
        }
    }
}
```

### 3.1 *Opt-out*

- 使用者可在專案 composer.json `dont-discover` **排除套件**

```json
"extra": {
    "laravel": {
        "dont-discover": ["barryvdh/laravel-debugbar"]
    }
}
```
- **全部停用**：`"dont-discover": ["*"]`

---

## 4. **Service Providers**（服務提供者）

- 套件與 Laravel 溝通的橋樑，負責綁定 container、註冊資源（view/config/lang/route）
- 請繼承 `Illuminate\Support\ServiceProvider`，需安裝 `illuminate/support`

---

## 5. **資源註冊與發佈**（Resources）

### 5.1 *Config*

- `publishes` **讓 config 可被複製到專案 config 目錄**
- `mergeConfigFrom` 於 **register 合併預設 config**

```php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CourierServiceProvider extends ServiceProvider
{
    /**
     * Boot 方法：負責資源的註冊與發佈
     */
    public function boot(): void
    {
        // **Config 資源發佈**
        $this->publishes([
            __DIR__.'/../config/courier.php' => config_path('courier.php'),
            /*
                1. `publishes` 方法：
                   - 將指定的檔案（如 config/courier.php）發佈到專案的 config 目錄。
                   - 使用者可以執行 `php artisan vendor:publish` 來複製這些檔案。
                   - php artisan vendor:publish --tag=config
                2. `__DIR__.'/../config/courier.php'`：
                   - 套件中的原始 config 檔案路徑。
                3. `config_path('courier.php')`：
                   - 專案中的 config 目錄路徑。
            */
        ]);

        // **Routes 資源載入**
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        /*
            1. `loadRoutesFrom` 方法：
               - 載入指定的路由檔案（如 routes/web.php）。
               - 套件的路由檔案會自動被 Laravel 載入。
            2. `__DIR__.'/../routes/web.php'`：
               - 套件中的路由檔案路徑。
        */

        // **Migrations 資源發佈**
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'migrations');
        /*
            1. `publishes` 方法：
               - 將指定的 migrations 檔案發佈到專案的 migrations 目錄。
               - 使用者可以執行 `php artisan vendor:publish --tag=migrations` 來複製這些檔案。
            2. `__DIR__.'/../database/migrations`：
                   - 套件中的 migrations 檔案路徑。
            3. `database_path('migrations')`：
                   - 專案中的 migrations 目錄路徑。
        */

        // **Language Files**
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'courier');
        /*
            1. `loadTranslationsFrom` 方法：
               - 註冊語系檔案的路徑和命名空間。
               - 第一個參數是語系檔案的路徑。
               - 第二個參數是命名空間（如 'courier'）。
            2. 使用方式：
               trans('courier::messages.welcome')
               - 從 'courier' 命名空間的語系檔案中取得 'messages.welcome' 的翻譯。
        */

        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');
        /*
            1. `loadJsonTranslationsFrom` 方法：
               - 註冊 JSON 格式的語系檔案。
               - 第一個參數是 JSON 語系檔案的路徑。
            2. 使用方式：
               trans('welcome')
               - 從 JSON 語系檔案中取得 'welcome' 的翻譯。
        */

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/courier'),
        ], 'lang');
        /*
            1. `publishes` 方法：
               - 將套件的語系檔案發佈到專案的語系目錄。
               - 使用者可以執行 `php artisan vendor:publish --tag=lang` 來複製語系檔案。
            2. `__DIR__.'/../lang'`：
               - 套件中的語系檔案路徑。
            3. `$this->app->langPath('vendor/courier')`：
               - 專案中的語系目錄路徑。
        */

        // **Views**
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'courier');
        /*
            1. `loadViewsFrom` 方法：
               - 註冊 View 的路徑和命名空間。
               - 第一個參數是 View 的路徑。
               - 第二個參數是命名空間（如 'courier'）。
            2. 使用方式：
               view('courier::dashboard')
               - 從 'courier' 命名空間的 View 中載入 'dashboard'。
        */

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/courier'),
        ], 'views');
        /*
            1. `publishes` 方法：
               - 將套件的 View 檔案發佈到專案的 View 目錄。
               - 使用者可以執行 `php artisan vendor:publish --tag=views` 來複製 View 檔案。
            2. `__DIR__.'/../resources/views'`：
               - 套件中的 View 檔案路徑。
            3. `resource_path('views/vendor/courier')`：
               - 專案中的 View 目錄路徑。
        */
    }

    /**
     * Register 方法：負責資源的合併與初始化
     */
    public function register(): void
    {
        // **Config 合併**
        $this->mergeConfigFrom(
            __DIR__.'/../config/courier.php', 'courier'
            /*
                1. `mergeConfigFrom` 方法：
                   - 將套件中的 config 檔案與專案中的 config 檔案合併。
                   - 如果專案中已存在相同的設定，專案的設定會覆蓋套件的預設值。
                2. `__DIR__.'/../config/courier.php`：
                   - 套件中的 config 檔案路徑。
                3. `'courier'`：
                   - 合併到專案中的 config 名稱。
            */
        );
    }
}
```

### 5.2 *Routes*

```php
public function boot(): void
{
    $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
}
```

### 5.3 *Migrations*

```php
public function boot(): void
{
    $this->publishesMigrations([
        __DIR__.'/../database/migrations' => database_path('migrations'),
    ]);
}
```

### 5.4 *Language Files*

- `loadTranslationsFrom` 註冊語系
- `loadJsonTranslationsFrom` 註冊 JSON 語系
- `publishes` 可發佈語系檔

```php
public function boot(): void
{
    $this->loadTranslationsFrom(__DIR__.'/../lang', 'courier');
    $this->loadJsonTranslationsFrom(__DIR__.'/../lang');
    $this->publishes([
        __DIR__.'/../lang' => $this->app->langPath('vendor/courier'),
    ]);
}
// 使用：trans('courier::messages.welcome')
```

### 5.5 *Views*

- `loadViewsFrom` 註冊 **view** 路徑與 **namespace**
- 可覆寫：`resources/views/vendor/{package}`
- `publishes` 可發佈 view

```php
public function boot(): void
{
    $this->loadViewsFrom(__DIR__.'/../resources/views', 'courier');
    $this->publishes([
        __DIR__.'/../resources/views' => resource_path('views/vendor/courier'),
    ]);
}
// 使用：view('courier::dashboard')
```

### 5.6 *Blade Components*

- `Blade::component` 註冊**單一 component**
- `Blade::componentNamespace` 註冊**命名空間**
- 匿名 component `放在 views/components` 目錄

```php
// app/Providers/CourierServiceProvider.php
namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CourierServiceProvider extends ServiceProvider
{
    /**
     * Boot 方法：負責資源的註冊
     */
    public function boot(): void
    {
        // 註冊單一 Blade Component
        Blade::component('package-alert', AlertComponent::class);
        /*
            1. `Blade::component()`：
               - 註冊單一 Blade Component。
               - 第一個參數是元件的名稱（如 'package-alert'）。
               - 第二個參數是元件的類別（如 AlertComponent::class）。
            2. 使用方式：
               <x-package-alert type="error" message="Something went wrong!" />
        */

        // 註冊命名空間 Blade Components
        Blade::componentNamespace('Nightshade\\Views\\Components', 'nightshade');
        /*
            1. `Blade::componentNamespace()`：
               - 註冊命名空間下的所有 Blade Components。
               - 第一個參數是命名空間（如 'Nightshade\\Views\\Components'）。
               - 第二個參數是命名空間前綴（如 'nightshade'）。
            2. 使用方式：
               <x-nightshade::calendar />
        */
    }

    /**
     * Register 方法：負責資源的初始化
     */
    public function register(): void
    {
        //
    }
}
```

### 5.7**About Command*

- 可用 `AboutCommand::add` 增加 `about` 指令資訊

```php
// app/Providers/CourierServiceProvider.php
namespace App\Providers;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;

class CourierServiceProvider extends ServiceProvider
{
    /**
     * Boot 方法：負責資源的註冊
     */
    public function boot(): void
    {
        // 註冊 About 指令資訊
        AboutCommand::add('My Package', fn () => [
            'Version' => '1.0.0',
            'Author' => 'John Doe',
            'License' => 'MIT',
        ]);
        /*
            1. `AboutCommand::add()`：
               - 用於向 `php artisan about` 指令添加自訂資訊。
               - 第一個參數是資訊的名稱（如 'My Package'）。
               - 第二個參數是回呼函式，返回一個陣列，包含要顯示的資訊。
            2. 使用方式：
               執行 `php artisan about`，會顯示以下內容：
               My Package
                   Version: 1.0.0
                   Author: John Doe
                   License: MIT
        */
    }

    /**
     * Register 方法：負責資源的初始化
     */
    public function register(): void
    {
        //
    }
}
```

### 5.8 *Commands*

- `commands` 註冊 **Artisan 指令**
- `optimizes` 註冊 **optimize/clear 指令**

```php
// app/Providers/CourierServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CourierServiceProvider extends ServiceProvider
{
    /**
     * Boot 方法：負責資源的註冊
     */
    public function boot(): void
    {
        // 確保程式在 Console 中執行
        if ($this->app->runningInConsole()) {
            // **註冊 Artisan 指令**
            $this->commands([
                InstallCommand::class,
                NetworkCommand::class,
            ]);
            /*
                1. `commands` 方法：
                   - 用於註冊自訂的 Artisan 指令。
                   - 傳入指令類別的陣列。
                2. `InstallCommand::class`：
                   - 指令類別，通常位於 `app/Console/Commands/InstallCommand.php`。
                3. `NetworkCommand::class`：
                   - 指令類別，通常位於 `app/Console/Commands/NetworkCommand.php`。
                4. 使用方式：
                   php artisan install
                   php artisan network
            */

            // **註冊 optimize/clear 指令**
            $this->optimizes(
                optimize: 'package:optimize',
                clear: 'package:clear-optimizations',
            );
            /*
                1. `optimizes` 方法：
                   - 註冊自訂的 `optimize` 和 `clear` 指令。
                   - `optimize`：執行優化的指令名稱。
                   - `clear`：清除優化的指令名稱。
                2. 使用方式：
                   php artisan package:optimize
                   php artisan package:clear-optimizations
            */
        }
    }

    /**
     * Register 方法：負責資源的初始化
     */
    public function register(): void
    {
        //
    }
}
```

### 5.9 **Public Assets**

- `publishes` 發佈 `public` 資源
- 可用 `tag` 分組

```php
// app/Providers/CourierServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CourierServiceProvider extends ServiceProvider
{
    /**
     * Boot 方法：負責資源的註冊與發佈
     */
    public function boot(): void
    {
        // **Public Assets 資源發佈**
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/courier'),
        ], 'public');
        /*
            1. `publishes` 方法：
               - 將套件的 public 資源檔案發佈到專案的 public 目錄。
               - 使用者可以執行 `php artisan vendor:publish --tag=public` 來複製這些檔案。
            2. `__DIR__.'/../public'`：
               - 套件中的 public 資源檔案路徑。
            3. `public_path('vendor/courier')`：
               - 專案中的 public 目錄路徑，通常是 `public/vendor/courier`。
            4. `'public'`：
               - 定義發佈的標籤名稱，方便使用者選擇性地發佈 public 資源。
        */
    }

    /**
     * Register 方法：負責資源的初始化
     */
    public function register(): void
    {
        //
    }
}
```

### 5.10 **Publishing File Groups**

- 可用 `tag` 分組發佈資源

```php
// app/Providers/CourierServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CourierServiceProvider extends ServiceProvider
{
    /**
     * Boot 方法：負責資源的註冊與發佈
     */
    public function boot(): void
    {
        // **Config 資源發佈**
        $this->publishes([
            __DIR__.'/../config/package.php' => config_path('package.php'),
        ], 'courier-config');
        /*
            1. `publishes` 方法：
               - 將套件的 config 檔案發佈到專案的 config 目錄。
               - 使用者可以執行 `php artisan vendor:publish --tag=courier-config` 來複製這些檔案。
            2. `__DIR__.'/../config/package.php'`：
               - 套件中的 config 檔案路徑。
            3. `config_path('package.php')`：
               - 專案中的 config 目錄路徑。
            4. `'courier-config'`：
               - 定義發佈的標籤名稱，方便使用者選擇性地發佈 config 資源。
        */

        // **Migrations 資源發佈**
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'courier-migrations');
        /*
            1. `publishes` 方法：
               - 將套件的 migrations 檔案發佈到專案的 migrations 目錄。
               - 使用者可以執行 `php artisan vendor:publish --tag=courier-migrations` 來複製這些檔案。
            2. `__DIR__.'/../database/migrations/'`：
               - 套件中的 migrations 檔案路徑。
            3. `database_path('migrations')`：
               - 專案中的 migrations 目錄路徑。
            4. `'courier-migrations'`：
               - 定義發佈的標籤名稱，方便使用者選擇性地發佈 migrations 資源。
        */
    }

    /**
     * Register 方法：負責資源的初始化
     */
    public function register(): void
    {
        //
    }
}
```

- 使用者可用 `tag` 或 `provider` 發佈：
```bash
php artisan vendor:publish --tag=courier-config
php artisan vendor:publish --provider="Your\Package\ServiceProvider"
``` 