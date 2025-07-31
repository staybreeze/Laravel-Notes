# *Laravel Views 視圖*

---

## 1. **簡介與核心概念**

- **View（視圖）**讓你將 `HTML` 與`程式邏輯`分離，易於維護與重用。
- 檔案放在 `resources/views`，通常用 Blade 樣板語法（.blade.php）。
- *生活化比喻*： View 就像「設計稿」，Controller 負責準備資料，View 負責呈現。

---

## 2. **建立與渲染 View**

- *建立*：
 ```bash
 php artisan make:view greeting
 # 產生 resources/views/greeting.blade.php
 ```
- *回傳 View*：
  ```php
  return view('greeting', ['name' => 'James']);
  # 或 View::make('greeting', ['name' => 'James']);
  ```
- *巢狀目錄*：
  ```php
  return view('admin.profile', $data); // resources/views/admin/profile.blade.php
  ```
- *dot notation*：用 . 代表子目錄
- *View::first*：回傳第一個存在的 view
  ```php
  View::first(['custom.admin', 'admin'], $data);
  ```
- *View::exists*：判斷 view 是否存在
  ```php
  if (View::exists('admin.profile')) { ... }
  ```

---

## 3. **資料傳遞與共享**

- *傳遞資料*：
  ```php
  return view('greetings', ['name' => 'Victoria']);
  // 或
  return view('greeting')->with('name', 'Victoria')->with('occupation', 'Astronaut');
  // return view('greetings', ['name' => 'Victoria', 'occupation' => 'Astronaut']);
  ```
- *全域共享資料*：
  ```php
  View::share('key', 'value'); // 通常寫在 AppServiceProvider boot()
  ```

---

## 4. **View Composer 與 Creator**

- *View Composer*：`每次渲染 view 時自動注入資料`
  - 適合多個 `路由/Controller` 都要用到**同一份資料**
  - **註冊方式**：
    ```php
    <!-- App\Providers\ViewServiceProvider -->
    namespace App\Providers;

    use Illuminate\Support\Facades\View;
    use Illuminate\Support\ServiceProvider;
    use App\Http\View\Composers\ProfileComposer;
    use App\Http\View\Composers\MultiComposer;

    class ViewServiceProvider extends ServiceProvider
    {
        /**
        * Bootstrap any application services.
        *
        * @return void
        */
        public function boot()
        {
            // 使用類別型的 View Composer 為單個視圖註冊邏輯
            View::composer('profile', ProfileComposer::class);

            // 使用閉包型的 View Composer 為單個視圖註冊邏輯
            View::composer('welcome', function ($view) {
                $view->with('key', 'value');
            });

            // 使用類別型的 View Composer 為多個視圖註冊邏輯
            View::composer(['profile', 'dashboard'], MultiComposer::class);

            // 使用閉包型的 View Composer 為所有視圖註冊邏輯
            View::composer('*', function ($view) {
                $view->with('globalKey', 'globalValue');
            });
        }

        /**
        * Register any application services.
        *
        * @return void
        */
        public function register()
        {
            //
        }
    }
    ```

    ```php
    <!-- 確保 ViewServiceProvider 已在 config/app.php 的 providers 陣列中註冊 -->
    'providers' => [
        // 其他服務提供者...
        App\Providers\ViewServiceProvider::class,
    ],
    ```

    ```php
    <!-- app/Http/View/Composers/ProfileComposer.php -->
    namespace App\Http\View\Composers;
    use Illuminate\View\View;

    class ProfileComposer
    {
        /**
        * 將資料綁定到視圖
        *
        * @param  \Illuminate\View\View  $view
        * @return void
        */
        public function compose(View $view)
        {
            // 為 'profile' 視圖準備資料
            $view->with('user', auth()->user());
        }
    }
    ```
    ```php
    namespace App\Http\View\Composers;
    <!-- app/Http/View/Composers/MultiComposer.php -->
    use Illuminate\View\View;

    class MultiComposer
    {
        /**
        * 將資料綁定到多個視圖
        *
        * @param  \Illuminate\View\View  $view
        * @return void
        */
        public function compose(View $view)
        {
            // 為 'profile' 和 'dashboard' 視圖準備共用資料
            $view->with('sharedData', 'This is shared data');
        }
    }
    ```
    ```php
    <!-- 閉包型的 View Composer 不需要單獨的檔案，因為它的邏輯直接寫在 ViewServiceProvider 中 -->
    View::composer('welcome', function ($view) {
        $view->with('key', 'value');
    });

    View::composer('*', function ($view) {
        $view->with('globalKey', 'globalValue');
    });
    ```

- *View Creator*：`view 實例化後立即執行`（比 **composer** 更早）
  ```php
  <!-- app/Providers/ViewServiceProvider.php -->
  namespace App\Providers;

  use Illuminate\Support\Facades\View;
  use Illuminate\Support\ServiceProvider;
  use App\Http\View\Creators\ProfileCreator;

  class ViewServiceProvider extends ServiceProvider
  {
      /**
      * Bootstrap any application services.
      *
      * @return void
      */
      public function boot()
      {
          // 使用類別型的 View Creator 為單個視圖註冊邏輯
          View::creator('profile', ProfileCreator::class);
      }

      /**
      * Register any application services.
      *
      * @return void
      */
      public function register()
      {
          //
      }
  }
  ```

  ```php
  <!-- 確保 ViewServiceProvider 已在 config/app.php 的 providers 陣列中註冊 -->
  'providers' => [
      // 其他服務提供者...
      App\Providers\ViewServiceProvider::class,
  ],
  ```

  ```php
  <!-- App\Http\View\Creators  -->
  namespace App\Http\View\Creators;

  use Illuminate\View\View;

  class ProfileCreator
  {
      /**
      * 將資料綁定到視圖
      *
      * @param  \Illuminate\View\View  $view
      * @return void
      */
      public function create(View $view)
      {
          // 為 'profile' 視圖準備資料
          $view->with('user', auth()->user());
      }
  }
  ```
---

## 5. **View 優化與快取**

- **Blade 會`自動編譯快取`**，但可手動預編譯提升效能：
 ```bash
  php artisan view:cache
  # 清除快取
  php artisan view:clear
  ```
- *比喻*： `View cache` 就像「預先烤好的麵包」，用時更快。

---

## 6. **React/Vue 前端整合**

- 可用 `Inertia.js` 讓 `React/Vue 前端` 與 Laravel 後端無痛整合，像 SPA 但保有 Laravel 路由與權限。
- 官方 `starter kit` 提供最佳實踐範例。

---
