# *Laravel Request Lifecycle 筆記*

---

## 1. **簡介**（Introduction）

理解 Laravel 框架`運作流程`，有助於開發時更有信心、減少「魔法感」。本章將高層次說明 Laravel 請求生命週期。

---

*補充說明*：

Laravel `請求生命週期`（Request Lifecycle）描述的是「__一個 HTTP 請求從進入框架到回應結束，Laravel 框架內部的完整處理流程__」。

而我們在開發時常說的「_Route → Controller → Service → Repository → Model → View_」則是 **應用層/業務邏輯層** 的資料流動，重點在於`資料與邏輯的分層設計`。

兩者層次不同，前者是**框架底層運作**，後者是**你自己設計的程式邏輯**。理解兩者差異，有助於更全面掌握 Laravel 的運作。

---

*補充註解*：

你原本理解的「_Route → Controller → Service → Repository → Model → View_」這個流程，並不是 Laravel 官方所說的 Request 生命週期，而是屬於應用層（業務邏輯層）的資料流動與分層設計。

| 名稱                   | 內容重點                                             | 層次       |
|------------------------|----------------------------------------------------|-----------|
| `Request 生命週期 `     | Laravel 框架從收到 HTTP 請求到回應結束的完整底層流程      | 框架底層   |
| `業務邏輯層資料流 `      | 你的程式碼如何處理資料、分層、回應（MVC、Service、Repo等） | 應用/業務層 |

---

*魔法感（Magic）*：指的是框 __架自動幫你完成很多事情，但你不清楚背後原理__，只覺得「怎麼這樣就可以了？」這種感覺。魔法感會讓開發變簡單，但遇到問題時不易 debug 或客製化，因此 __理解底層運作能減少魔法感、提升信心__。

英文常直接稱為 __magic__，例如 “Laravel does a lot of magic behind the scenes.” 或 “This framework feels too magical.” 在技術圈這是常見的用法。

---

## 2. **生命週期總覽**（Lifecycle Overview）

### 2.1 *第一步：入口檔案*

所有請求進入點為 `public/index.php`，由 `web server`（__Apache/Nginx__）導向此檔。

__index.php__ 主要功能：

- 載入 `Composer autoloader.php` _第三方套件_ 和 _專案所有類別_
- 載入 `bootstrap/app.php` 取得 *Application 實例*
- 建立 `Service Container`

  - *Application Instance*：整個 Laravel 應用的「__服務容器__」，負責`管理所有服務與元件`。

    - __元件__ 指的是 Laravel 應用裡的`各種功能模組`，例如：

      *路由（Router）、事件（Event）、快取（Cache）、資料庫（Database）、Session、Queue* 等，這些都算是服務容器管理的元件。
      偏向「__架構__」，也就是 _架構分類_。

    - __服務__ 是指 Laravel 提供的`功能或資源`，
    
      例如：*資料庫連線、郵件發送、快取、認證、日誌、事件*等，這些都可以透過服務容器註冊和取得。
      偏向「__實際用途__」，_實際被容器管理和提供給程式使用的功能_。

---

  **Application 實例（Application Instance）**：

  - Laravel 啟動時，會建立一個 `Illuminate\Foundation\Application` 類別的物件，這個物件就稱為「__Application 實例__」。
  - 這個類別的原始碼在 `vendor/laravel/framework/src/Illuminate/Foundation/Application.php`。
  - 它裡面寫好了大量方法，負責 _控制 Laravel 的各個層面_（如 __服務註冊、啟動、事件、路由、請求、回應、依賴注入__ 等），是整個框架的「_大腦_」。
  - 它是整個 Laravel 應用程式的 *「核心容器」（Service Container）*，負責管 __所有服務、元件、設定與依賴注入__。
  - 你可以把它想像成一個「_總控中心_」，所有的服務（如資料庫、路由、事件、認證等）都會註冊到這個容器裡，之後要用時再從這裡取出。
  - 這個實例會在 `bootstrap/app.php` 被建立，並貫穿整個請求生命週期。

  Application 實例，讓 Laravel 可以高度模組化、彈性擴充，也方便測試與維護。
  
---

  **Application 實例補充說明**：

  *原因與說明*

  Laravel 是「__無狀態__」的 Web 應用框架，遵循 HTTP 請求/回應的 `stateless` 特性。

  每當有一個新的請求進來（不論是網頁、API、AJAX），`public/index.php` **都會重新執行一次，從頭建立一個`新的` Application 實例**。

  這個 Application 實例會在本次請求中，負責管理所有服務、元件、依賴注入、事件、路由等，直到回應送出、**請求結束後**，這個實例就會被**銷毀**。

  下一個請求來時，會再重新建立一個`全新的 Application 實例`，彼此之間完全獨立。
    
  *白話理解*
    
  你可以把每次請求想像成「_開一台全新的車_」來載客人，載完一趟就報廢，下一個客人再開一台新的車。
  這樣做的好處是：
   
  - **不會有資料殘留，安全性高**。
  - **不同請求互不影響，容易維護與除錯**。
    
  *補充*
  
  這也是為什麼 Laravel 的「__Service Container__」和「__Service Provider__」`每次請求都會重新註冊、啟動`。
  只有在特殊情境（如 `Swoole、Octane` 這類常駐記憶體的 PHP 伺服器）才會有 _Application 實例重用_ 的狀況，但那是進階主題。
    
  *為什麼不會有很多個 Application 實例同時存在？*
  
  - 每個請求都是獨立的 PHP 執行流程。
  - 在**傳統的 PHP 運作模式（如 Apache、Nginx + FPM）**，每一個 HTTP 請求都會**啟動一個新的 PHP `處理程序`（process）**，執行完畢後就結束、**釋放所有記憶體**。
  - _Application 實例_ `只存在於單一請求生命週期內`，請求結束後這個實例就會被銷毀，記憶體也會被回收。
  - 不會有「_很多個 Application 實例_」同時堆積在記憶體裡，因為每個請求結束後，該實例就不存在了。
  - 這裡的「記憶體」指的是**伺服器的 RAM（主記憶體）**，也就是執行 PHP/Laravel 程式的那台主機的記憶體。不是瀏覽器的記憶體，也不是使用者電腦的記憶體。每次請求結束後，伺服器會自動釋放這次請求用到的所有物件和資源，下一次請求會重新建立新的 Application 實例。

  *白話比喻*
  
  你可以想像成：
  - 每次有客人來餐廳（`HTTP 請求`），就臨時搭一個小廚房（`Application 實例`）來做菜。
  - 客人吃完走了，這個小廚房就拆掉，下一個客人來再搭一個新的。
  - 不會有很多小廚房同時堆在一起。
    
  *例外情況*
  
  只有在 **「常駐型」PHP 伺服器**（如 `Laravel Octane、Swoole`）下，`Application 實例`才有可能被重複利用，這時就要特別注意 _狀態管理_ 與 _記憶體釋放_。但一般情況下（大多數 Laravel 專案）都不會有這個問題。

  *同一個 client 同時多個請求的情況*
  
  - 每一個 HTTP 請求（__不論來自哪個 client__）都會產生`一個 Application 實例`。
  - **如果同一個 client（例如同一個瀏覽器）同時發出多個請求（如多分頁、AJAX 並發）**，伺服器會為「_每一個請求_」各自產生一個 `Application 實例`，這些實例彼此獨立、互不影響。
  - 但同一個請求的處理過程中，只會有一個 `Application 實例`。
    
  白話：你同時開三個分頁，伺服器就同時開三台車（`Application 實例`）來服務你，每台車只服務一趟，服務完就消失。每個請求都是獨立的，不會互相干擾。

---

### 2.2 *HTTP / Console Kernels*

- 版本差異（重要）

  - __Laravel 10__ 及更早版本：使用 `App\Http\Kernel` 與 `App\Console\Kernel` 管理 _HTTP_ 與 _Console_ 流程。
  
  - __Laravel 11+（含 12）__：不再產生上述兩個 Kernel 類別。
  - _HTTP 流程與中介層由_ `bootstrap/app.php` 透過 `Application::configure()->withRouting()->withMiddleware()` 管理
  - _Console 相關由_ `routes/console.php` 與排程 API 設定。

---

- （Laravel 10 及更早版本）**HTTP Kernel**：

  - 類別為 `App\Http\Kernel`，繼承自 `Illuminate\Foundation\Http\Kernel`。
  - 負責處理*所有 HTTP（網頁）請求*。
  - 主要功能是調度 *Middleware、處理路由、回應請求*。

- （Laravel 10 及更早版本）**Console Kernel（命令列核心）**：

  - 類別為 `App\Console\Kernel`，繼承自 `Illuminate\Foundation\Console\Kernel`。
  - 專門負責處理 *Artisan 指令（命令列任務）*。
  - 主要功能是註冊所有 *Artisan 指令*、*排程（Schedule）*、以及*啟動時需要的 console middleware*。
  - 當你執行 `php artisan ...` 時，請求會進入 Console Kernel，由它`負責調度與執行對應的指令`。

---

- （Laravel 11+）**HTTP/Console 管線**：

  - 由 `bootstrap/app.php` _統一設定_：
    - `->withRouting(...)` 指定 `routes/web.php`、`routes/api.php`、`routes/console.php` 等路由入口。
    - `->withMiddleware(...)` 設定全域、中介層群組與別名、優先順序等。

---

- `Application 實例`會根據 _請求型態_（HTTP 或 Console）分別調度對應的流程，確保網頁與命令列任務都能正確運作。

- **小結**：

  - Laravel 10 及更早版本：透過 `App\Http\Kernel` 與 `App\Console\Kernel`。
  - Laravel 11+：改由 `bootstrap/app.php` 的 __設定鏈__ 管理 HTTP 與 Console 流程。

---

### 2.3 *Service Providers 啟動*

**Service Provider（服務提供者）**：

Laravel 中的 Service Provider 是一種特殊的**類別**，負責「_註冊_」和「_啟動_」應用程式所需的*各種服務*（如資料庫、事件、驗證、路由等）。

- 每個 Service Provider 通常會在 `app/Providers` 目錄下，並在 `config/app.php` 的 `providers` 陣列中註冊。
- 主要有兩個方法：`register()`（註冊服務到 **Service Container**）和 `boot()`（**啟動服務、執行初始化動作**）。
- Laravel 啟動時會 _依序執行_ 所有 Service Provider，確保所有功能都已經準備好。

Service Provider 是 Laravel 框架「_擴充性_」和「_模組化_」的核心機制，讓你可以很方便地 __新增、替換或自訂__ 各種功能。

流程：
1. 依序`實例化`所有 provider
2. 先呼叫所有 provider 的 `register` 方法
3. 再呼叫所有 provider 的 `boot` 方法

只有所有 `binding`（把服務或元件 _註冊到_ 服務容器） 都註冊完畢後，才會執行 `boot`。

*自訂/第三方* provider 清單可見於 `bootstrap/providers.php`。

---

### 2.4 *Routing 路由分派*

所有 provider 註冊完畢後，Request 會交給 **router** 處理：
- 分派至對應 `route/controller`
- 執行 `route` 專屬 `middleware`

**Middleware** 可`過濾/檢查`請求（如驗證登入、維護模式等）。有些 middleware 全域套用，有些僅限特定 route。

若請求通過所有 middleware，則執行 route/controller 方法，回傳 Response。

---

### 2.5 *回應處理與送出*

Controller 回傳 Response 後，Response 會再經過 middleware 鏈，最終由（Laravel 10-）`kernel` 的 `handle` 方法或（Laravel 11+）框架內建 `HTTP 管線`處理，並呼叫 **Response** 的 `send` 方法，將內容送至瀏覽器。

Controller → Response  
  ↓  
Middleware 鏈  
  ↓  
（Laravel 10-）Kernel 的 handle 方法／（Laravel 11+）HTTP 管線  
  ↓  
Application 實例  
  ↓  
Response 的 send 方法  
  ↓  
瀏覽器

---

## 3. **Service Provider 重點**（Focus on Service Providers）

Service Provider 是 Laravel 啟動的核心：

- _Application 實例建立_
- _註冊所有 Service Provider_
- _Request 交給已啟動的 Application 處理_

**自訂** provider 皆放於 `app/Providers` 目錄。

預設的 `AppServiceProvider` 通常很空，可在此加入 *自訂`bootstrapping`* 或 *`service container`綁定*。大型專案可拆分多個 provider，分別管理不同服務的啟動。 

<!-- 
bootstrapping 指的是「啟動初始化」的過程，
在 Laravel 裡，通常是指在應用程式啟動時執行一些設定、註冊、初始化動作，
例如：設定全域參數、事件監聽、服務綁定等，
讓系統在正式運作前先做好必要準備。 
-->

---

## **Laravel 請求生命週期**（Request Lifecycle）

1. *入口檔案（Entry Point）*
   - 所有 HTTP 請求都會進入 `public/index.php`。
    - 入口檔案：所有請求的起點，負責 __載入自動加載器__ 與 __啟動框架__。
    - 白話：所有網頁請求都會先到這個檔案，這是 Laravel 的大門。

2. *自動加載（Autoload）*
   - 由 __Composer__ 產生的 `vendor/autoload.php` 負責**自動載入所有 PHP 類別**。
    - __Composer__：PHP 的`套件管理工具`。
    - __Autoload__：自動`載入類別`機制，讓你不用手動引入每個檔案。
    - 白話：自動幫你把需要的 PHP 類別檔案載入進來，省去手動 require 的麻煩。

3. *啟動框架（Bootstrap）*
   - 載入 `bootstrap/app.php`，建立**應用程式實例（Application Instance）**。
    - **Application Instance**：整個 Laravel 應用的「服務容器」，負責 __管理所有服務與元件__ 。
    - 白話：準備好 Laravel 的主體，像是把所有零件組裝成一台車。
      __補充註解__：每一次 HTTP 請求都會`重新產生一個全新`的 `Application 實例`，_請求結束後這個實例就會被銷毀、記憶體釋放，不會累積_。這是 PHP 傳統運作模式的設計，確保每個請求都是獨立、乾淨的環境。只有在特殊`常駐型伺服器`（如 `Octane、Swoole`）才會有 Application 實例重用的情況。

4. *HTTP 流程啟動*
   - （Laravel 10 及更早版本）建立 `App\Http\Kernel` 實例，處理 HTTP 請求。
   - （Laravel 11+）由 `bootstrap/app.php` 中的 `->withMiddleware(...)` 與 _框架內建管線_ 啟動 HTTP 流程。
    - __Kernel/管線__：核心流程控制，**決定請求如何被處理**。
    - 白話：決定這台車要怎麼開、怎麼走流程。

5. *載入服務提供者（Service Providers）*
   - **註冊**服務到服務容器，**啟動**所有註冊的服務提供者。
    - __Service Provider__：註冊、啟動`各種功能（如資料庫、驗證、事件等）的類別`。
    - 白話：把所有功能（像資料庫、登入、信件等）都準備好，讓你隨時可以用。

6. *處理中介層（Middleware）*
   - 請求會經過一連串中介層（如 _驗證、CSRF、防護_ 等）。
    - __Middleware__：在`請求與回應之間`攔截、處理資料的元件。
    - 白話：像過濾網，檢查請求是否合法、需不需要登入等。

7. *路由（Routing）*
   - 根據請求的 URL 與方法，找到對應的路由與控制器。
    - __Route__：定義 URL 與`對應`處理邏輯的規則。
    - __Controller__：負責處理`請求邏輯`的類別。
    - 白話：決定這個網址要交給哪個程式處理。

8. *執行控制器動作（Controller Action）*
   - 執行對應的控制器方法，處理`業務邏輯`，回傳回應。
    - __Action__：控制器中的一個`方法`，對應一個`業務操作`。
    - 白話：開始執行你寫的功能（例如查資料、計算、存檔等）。

9. *產生回應（Response）*
   - 控制器回傳的資料會被包裝成 Response 物件。
    - __Response__：HTTP `回應物件`，負責輸出到瀏覽器。
    - 白話：把結果包裝好，準備送回給使用者。

10. *回應經過中介層（Middleware）*
    - 回應資料會再經過一遍中介層（如`加密、壓縮`等）。
     - 這是「出站」中介層，與進站相對。
     - 白話：回應出去前再檢查、加工一次（例如加密、壓縮）。

11. *送出回應（Send Response）*
    - 最終將回應送到使用者瀏覽器，結束本次請求。
     - 白話：把資料送回給用戶，這次請求就完成了。

--- 

`Client`
  ↓
`public/index.php` (入口檔案)
所有網頁請求都會先到這個檔案，這是 Laravel 的大門。
  ↓
`Composer Autoload`
自動幫你把需要的 PHP 類別檔案載入進來，省去手動 require 的麻煩。
  ↓
`bootstrap/app.php` 建立 __Application 實例__
準備好 Laravel 的主體，像是把所有零件組裝成一台車。
  ↓
HTTP 流程啟動（Laravel 10-：`App\Http\Kernel`；Laravel 11+：`bootstrap/app.php` 的 `withMiddleware`）
決定這台車要怎麼開、怎麼走流程。
  ↓
載入 `Service Providers`
把所有功能（像資料庫、登入、信件等）都準備好，讓你隨時可以用。
  ↓
執行 `Middleware`
像過濾網，檢查請求是否合法、需不需要登入等。
  ↓
`Routing` 路由分派
決定這個網址要交給哪個程式處理。
  ↓
`Controller` 處理
開始執行你寫的功能（例如查資料、計算、存檔等）。
  ↓
`Service / Repository / Model`
白話：進一步處理資料、存取資料庫、商業邏輯等。
  ↓
產生 `Response`
白話：把結果包裝好，準備送回給使用者。
  ↓
`Response 經過 Middleware`
白話：回應出去前再檢查、加工一次（例如加密、壓縮）。
  ↓
`回傳給 Client`
白話：把資料送回給用戶，這次請求就完成了。 

---

*補充：`Application、Service Container、Service Provider` 的概念與關係*

- **Application**：
  - Laravel 啟動時建立的「_核心物件_」，類別為 `Illuminate\Foundation\Application`。
  - 本質上就是 `Service Container` 的本體（繼承自 Container）。
  - __集中管理__ *所有服務、元件、設定與依賴注入* ，是整個框架的「大腦」和「總控中心」。
  - 比喻：一台剛組裝好的新車，裡面裝好了所有零件（服務、元件），每次請求都開一台新車跑流程。

- **Service Container（服務容器）**：
  - 用來「_註冊、解析、管理_」各種服務（物件、元件、依賴）的容器。
  - `Application 實例` 就是 Service Container 的實現。
  - 提供 *依賴注入、管理服務生命週期、單例、綁定、解析*。
  - 比喻：自動配件工廠，你要什麼服務，它就幫你組裝好給你。

- **Service Provider（服務提供者）**：
  - 一種「_註冊服務_」到 Service Container 的類別，通常放在 `app/Providers` 目錄下。
  - 負責在 `啟動時` 註冊和啟動各種服務、元件、事件、設定。
  - 讓 Laravel 可以模組化、擴充、客製化各種功能。
  - 比喻：安裝工人，負責把各種零件（服務）安裝到車子（`Application/Service Container`）裡。
  
  *補充說明：註冊服務的主體與目標*

  - 「__註冊服務__」這個動作的主體是 `Service Provider`，也就是你會在 Service Provider 的 `register()` 方法裡寫下要註冊哪些服務。
  - 但「__被註冊__」的對象是 `Service Container`，所有服務（__物件、單例、閉包、設定等__）都會被註冊到 Service Container 裡。
  
  - __具體流程__：
    1. Service Provider 的 `register()` 方法被 Laravel 執行。
    2. 在 `register()` 裡呼叫 `$this-app-bind()`、`$this-app-singleton()` 等方法，把服務註冊到 Service Container。
    3. 之後你就可以用 `app('service_name')` 或 `type-hint` 方式解析這些服務。

  - __簡單比喻__：_Service Provider = 註冊櫃檯（負責登記）_
                _Service Container = 倉庫（實際存放東西）_

  - __結論__：註冊服務這個動作是 Service Provider 的責任，但註冊的目標是 Service Container。

  - 沒有 Service Provider，Service Container 也可以直接註冊服務（例如在 `bootstrap/app.php` 直接寫），但 Laravel 的設計是用 *Service Provider* 來`集中管理註冊流程`。

---

*三者關係總結*：

- __Application__ 是整個 Laravel 的「大腦」和「總控中心」，本質上就是一個 Service Container。
- __Service Provider__ 負責把各種服務註冊到 Application（Service Container）裡。
- 你要用的所有服務，最後都會透過 `Application/Service Container` 來取得。 

---

*補充註解*：

__Service Container（服務容器）的類別__ 是 `Illuminate\Container\Container`，
而 __Application 的類別__ 是 `Illuminate\Foundation\Application`。

*Application 是 Container 的子類別（`Application extends Container`），所以 Application 既是 Application，也是 Service Container*。
你可以把 Service Container 想像成「工廠」，Application 則是「工廠的總部」，本身就是一個工廠，但還多了很多 __管理、調度、啟動__ 的功能。 

`Illuminate\Foundation\Application` __繼承__ `Illuminate\Container\Container`。

- __Application 實例__（`Illuminate\Foundation\Application`）本身就是 *Service Container*（`Illuminate\Container\Container`）的子類別，__兩者本質上是同一個物件__。

- __Service Provider__ 註冊的所有服務，實際上是存進 Application 實例（Service Container 實例）的 __內部屬性__（如 _bindings、instances_ 等）。

- 你在任何地方 *type-hint* 依賴，Laravel 會`根據 Service Provider 註冊的規則，從這個 Application 實例解析並注入對應的物件`。

- 所有服務的註冊、解析、依賴注入，最終都發生在同一個 *Application 實例（Service Container 實例）*裡。

---

*`Service Provider、Service Container、Application` 關係線狀圖*

1. **Service Provider**（服務提供者）
   │
   │ 1.1 負責 *註冊各種服務*（如資料庫、事件、快取、路由、第三方元件等）
   │ 1.2 每個 Service Provider 會在 *啟動時* 執行 `register()`、`boot()`方法
   │
   ▼
2. **Service Container**（服務容器）
   │ 2.1 類別為 `Illuminate.Container.Container`
   │ 2.2 Service Container：
   │   - *註冊服務*：把各種服務（如 __資料庫、快取、Log、第三方元件__ 等）註冊到容器裡
   │   - *管理生命週期*：決定服務是每次都 `new（多例）`，還是只 `new（一次）` 大家共用（單例）
   │   - *依賴注入*：自動幫你把需要的 **物件塞進去**（`constructor/method injection`）
   │   - *綁定/解析*：支援多種綁定方式（__bind、singleton、scoped、條件綁定__ 等），需要時 *自動解析出服務*
   │   - *解耦合*：讓你的程式碼 **不用自己 new 物件** ，全部交給容器管理，方便測試與擴充
   │
   ▼
3. **Application**（應用程式實例）
   │ 3.1 類別為 `Illuminate.Foundation.Application`
   │ 3.2 Application *繼承自 Service Container*，本質上就是一個更強大的 Service Container
   │ 3.3 Application 除了有 Service Container 的功能，還負責：
   │   - *啟動流程*：__載入__ Service Provider、__執行__ boot 方法、__初始化__ 框架
   │   - *HTTP/Console 調度*：
   │     - （Laravel 10-）透過對應的 `App\Http\Kernel`、`App\Console\Kernel`
   │     - （Laravel 11+）由 `bootstrap/app.php` 的 `withRouting`、`withMiddleware` 管理
   │   - *事件系統*：管理事件的 __註冊、觸發、監聽__
   │   - *路由系統*：管理路由的 __註冊、解析、分派__
   │   - *請求處理*：建立 __Request 物件、處理 HTTP 請求__
   │   - *回應處理*：建立 __Response 物件、處理 HTTP 回應__
   │ 3.4 每次請求都會建立一個`全新的 Application 實例`，確保每個請求的狀態、資源、依賴都是獨立的
   │
   ▼
4. 你在 _Controller、Service、Middleware、Job、Event_ 等地方 *type-hint* 的依賴，
   都是由 Application（Service Container）來 __解析與注入__，
   而這些服務大多是由 __Service Provider 註冊進來的__

*type-hint（型別提示）* 是指`在函式、方法或建構子的參數前面標註型別`，告訴 PHP 這個參數必須是什麼類型。
例如：`public function __construct(FooService $foo)` 這裡的 `FooService` 就是 *type-hint*，Laravel 會根據這個型別自動注入對應的服務。

- PHP 的 type-hint 可以標註 **「類別」、「介面」** 或 **「原生型別」（int、string、array...）**。

- Laravel 的 *自動注入（依賴注入）* 只會發生在 **「類別」或「介面」** 型別，且只在`建構子、Controller action、屬性`注入（Laravel 9+）、或用容器 `call` 方法時才會自動注入。

- `一般方法`的 **`原生型別` type-hint（如 int、string、array）** 只是 PHP 的`型別檢查`，Laravel 不會自動注入，必須自己傳值。

- 常見的 *type-hint* 會出現在`建構子（或方法）參數`上，且只能標註「__類別__」或「__介面__」名稱。

- *Laravel 只會根據「`類別`」或「`介面`」的 type-hint 自動注入對應的物件，`原生型別`（如 int、string、array）不會自動注入*。

- Laravel 的**自動注入**必須同時滿足兩個條件：

1. 進入*依賴注入流程*（如 __建構子、Controller action、屬性注入、容器 call 方法__）
2. 參數 *type-hint 是「__類別__」或「__介面__」*
- 只有 type-hint 沒有經過依賴注入，Laravel 不會自動注入。
- 只有依賴注入但 type-hint 不是類別/介面，Laravel 也不會自動注入。

*總結*：

- __Service Provider__ 負責「__註冊__」服務
- __Service Container__ 負責「__管理、解析__」服務
- __Application__ 是 `Service Container` 的子類別，負責 __整個框架的啟動與調度__

- 你所有用到的服務，最後都會透過 `Application/Service Container` 來取得