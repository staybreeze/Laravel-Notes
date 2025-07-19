# Laravel Request Lifecycle 筆記

## 1. *簡介*（Introduction）

理解 Laravel 框架運作流程，有助於開發時更有信心、減少「魔法感」。本章將高層次說明 Laravel 請求生命週期。

> 補充說明：
> Laravel 請求生命週期（Request Lifecycle）描述的是「*一個 HTTP 請求從進入框架到回應結束，Laravel 框架內部的完整處理流程*」。
> 
> 而我們在開發時常說的「*Route → Controller → Service → Repository → Model → View*」則是**應用層/業務邏輯層**的資料流動，重點在於資料與邏輯的分層設計。
> 
> 兩者層次不同，前者是*框架底層運作*，後者是*你自己設計的程式邏輯*。理解兩者差異，有助於更全面掌握 Laravel 的運作。
>
> **補充註解：**
> 你原本理解的「Route → Controller → Service → Repository → Model → View」這個流程，並不是 Laravel 官方所說的 Request 生命週期，而是屬於應用層（業務邏輯層）的資料流動與分層設計。
>
> | 名稱                   | 內容重點                                   | 層次         |
> |------------------------|--------------------------------------------|--------------|
> | Request 生命週期       | Laravel 框架從收到 HTTP 請求到回應結束的完整底層流程 | 框架底層     |
> | 業務邏輯層資料流       | 你的程式碼如何處理資料、分層、回應（MVC、Service、Repo等） | 應用/業務層 |

> **魔法感（Magic）**：指的是框架自動幫你完成很多事情，但你不清楚背後原理，只覺得「怎麼這樣就可以了？」這種感覺。魔法感會讓開發變簡單，但遇到問題時不易 debug 或客製化，因此理解底層運作能減少魔法感、提升信心。
> 
> 英文常直接稱為 magic，例如 “Laravel does a lot of magic behind the scenes.” 或 “This framework feels too magical.” 在技術圈這是常見的用法。

---

## 2. *生命週期總覽*（Lifecycle Overview）

### 2.1 **第一步：入口檔案**

所有請求進入點為 `public/index.php`，由 web server（Apache/Nginx）導向此檔。index.php 主要功能：
- 載入 `Composer autoloader`
- 載入 `bootstrap/app.php` 取得 Application 實例
- 建立 `Service Container`

  // *Application Instance*：整個 Laravel 應用的「服務容器」，負責管理所有服務與元件。

  > **Application 實例（Application Instance）**：
  > - Laravel 啟動時，會建立一個 `Illuminate\Foundation\Application` 類別的物件，這個物件就稱為「Application 實例」。
  > - 這個類別的原始碼在 `vendor/laravel/framework/src/Illuminate/Foundation/Application.php`。
  > - 它裡面寫好了大量方法，負責控制 Laravel 的各個層面（如服務註冊、啟動、事件、路由、請求、回應、依賴注入等），是整個框架的「大腦」。
  > - 它是整個 Laravel 應用程式的 **「核心容器」（Service Container）**，負責管理**所有服務、元件、設定與依賴注入**。
  > - 你可以把它想像成一個「總控中心」，所有的服務（如資料庫、路由、事件、認證等）都會註冊到這個容器裡，之後要用時再從這裡取出。
  > - 這個實例會在 `bootstrap/app.php` 被建立，並貫穿整個請求生命週期。

  > Application 實例讓 Laravel 可以高度模組化、彈性擴充，也方便測試與維護。
  
  > **Application 實例補充說明**：
    > **原因與說明**
    > Laravel 是「*無狀態*」的 Web 應用框架，遵循 HTTP 請求/回應的 stateless 特性。
    > 每當有一個新的請求進來（不論是網頁、API、AJAX），`public/index.php` *都會重新執行一次，從頭建立一個新的 Application 實例*。
    > 這個 Application 實例會在本次請求中，負責管理所有服務、元件、依賴注入、事件、路由等，直到回應送出、*請求結束*後，這個實例就會被*銷毀*。
    > 下一個請求來時，會再重新建立一個全新的 Application 實例，彼此之間完全獨立。
    
    > **白話理解**
    > 你可以把每次請求想像成「開一台全新的車」來載客人，載完一趟就報廢，下一個客人再開一台新的車。
    > 這樣做的好處是：
    > - *不會有資料殘留，安全性高*。
    > - *不同請求互不影響，容易維護與除錯*。
    
    > **補充**
    > 這也是為什麼 Laravel 的「*Service Container*」和「*Service Provider*」每次請求都會重新註冊、啟動。
    > 只有在特殊情境（如 Swoole、Octane 這類常駐記憶體的 PHP 伺服器）才會有 Application 實例重用的狀況，但那是進階主題。
    
    > **為什麼不會有很多個 Application 實例同時存在？**
    > - 每個請求都是獨立的 PHP 執行流程。
    > - 在*傳統的 PHP 運作模式（如 Apache、Nginx + FPM）*，每一個 HTTP 請求都會*啟動一個新的 PHP 處理程序（process）*，執行完畢後就結束、*釋放所有記憶體*。
    > - Application 實例只存在於單一請求生命週期內，請求結束後這個實例就會被銷毀，記憶體也會被回收。
    > - 不會有「很多個 Application 實例」同時堆積在記憶體裡，因為每個請求結束後，該實例就不存在了。
    > - 這裡的「記憶體」指的是**伺服器的 RAM（主記憶體）**，也就是執行 PHP/Laravel 程式的那台主機的記憶體。不是瀏覽器的記憶體，也不是使用者電腦的記憶體。每次請求結束後，伺服器會自動釋放這次請求用到的所有物件和資源，下一次請求會重新建立新的 Application 實例。

    > **白話比喻**
    > 你可以想像成：
    > - 每次有客人來餐廳（HTTP 請求），就臨時搭一個小廚房（Application 實例）來做菜。
    > - 客人吃完走了，這個小廚房就拆掉，下一個客人來再搭一個新的。
    > - 不會有很多小廚房同時堆在一起。
    
    > **例外情況**
    > 只有在「常駐型」PHP 伺服器（如 Laravel Octane、Swoole）下，Application 實例才有可能被重複利用，這時就要特別注意狀態管理與記憶體釋放。但一般情況下（大多數 Laravel 專案）都不會有這個問題。

    > **同一個 client 同時多個請求的情況**
    > - 每一個 HTTP 請求（*不論來自哪個 client*）都會產生一個 Application 實例。
    > - 如果同一個 client（例如同一個瀏覽器）同時發出多個請求（如多分頁、AJAX 並發），伺服器會為「每一個請求」各自產生一個 Application 實例，這些實例彼此獨立、互不影響。
    > - 但同一個請求的處理過程中，只會有一個 Application 實例。
    
    > 白話：你同時開三個分頁，伺服器就同時開三台車（Application 實例）來服務你，每台車只服務一趟，服務完就消失。每個請求都是獨立的，不會互相干擾。

### 2.2 **HTTP / Console Kernels**

Laravel 會根據*請求型態*，將請求送至 HTTP kernel 或 Console kernel：
- **HTTP Kernel**：
  - 類別為 `App\Http\Kernel`，繼承自 `Illuminate\Foundation\Http\Kernel`。
  - 負責處理*所有 HTTP（網頁）請求*。
  - 主要功能是調度 *Middleware、處理路由、回應請求*。
- **Console Kernel（命令列核心）**：
  - 類別為 `App\Console\Kernel`，繼承自 `Illuminate\Foundation\Console\Kernel`。
  - 專門負責處理 *Artisan 指令（命令列任務）*。
  - 主要功能是註冊所有 *Artisan 指令*、*排程（Schedule）*、以及*啟動時需要的 console middleware*。
  - 當你執行 `php artisan ...` 時，請求會進入 Console Kernel，由它負責調度與執行對應的指令。
  
- Application 實例會根據請求型態（HTTP 或 Console）分別啟動對應的 Kernel，確保網頁與命令列任務都能正確運作。

- **小結：** Application 實例同時支援 HTTP 與 Console 兩種 Kernel，讓 Laravel 能同時處理網頁請求與命令列任務，兩者都由 Application 實例統一管理。

### 2.3 **Service Providers 啟動**

> **Service Provider（服務提供者）**：
> Laravel 中的 Service Provider 是一種特殊的**類別**，負責「*註冊*」和「*啟動*」應用程式所需的*各種服務*（如資料庫、事件、驗證、路由等）。

> - 每個 Service Provider 通常會在 `app/Providers` 目錄下，並在 `config/app.php` 的 `providers` 陣列中註冊。
> - 主要有兩個方法：`register()`（註冊服務到 **Service Container**）和 `boot()`（啟動服務、執行初始化動作）。
> - Laravel 啟動時會依序執行所有 Service Provider，確保所有功能都已經準備好。

> Service Provider 是 Laravel 框架「*擴充性*」和「*模組化*」的核心機制，讓你可以很方便地新增、替換或自訂各種功能。

流程：
1. 依序實例化所有 provider
2. 先呼叫所有 provider 的 `register` 方法
3. 再呼叫所有 provider 的 `boot` 方法

> 只有所有 binding 都註冊完畢後，才會執行 boot。

*自訂/第三方* provider 清單可見於 `bootstrap/providers.php`。

### 2.4 **Routing 路由分派**

所有 provider 註冊完畢後，Request 會交給 *router* 處理：
- 分派至對應 route/controller
- 執行 route 專屬 middleware

*Middleware* 可過濾/檢查請求（如驗證登入、維護模式等）。有些 middleware 全域套用，有些僅限特定 route。

若請求通過所有 middleware，則執行 route/controller 方法，回傳 Response。

### 2.5 **回應處理與送出**

Controller 回傳 Response 後，Response 會再經過 middleware 鏈，最終由 kernel 的 handle 方法回傳給 application 實例，並呼叫 Response 的 `send` 方法，將內容送至瀏覽器。

---

## 3. *Service Provider 重點*（Focus on Service Providers）

Service Provider 是 Laravel 啟動的核心：
- **Application 實例建立**
- **註冊所有 Service Provider**
- **Request 交給已啟動的 Application 處理**

自訂 provider 皆放於 `app/Providers` 目錄。

預設的 `AppServiceProvider` 通常很空，可在此加入*自訂 bootstrapping* 或 *service container 綁定*。大型專案可拆分多個 provider，分別管理不同服務的啟動。 

---

## *Laravel 請求生命週期（Request Lifecycle）*

1. **入口檔案（Entry Point）**
   - 所有 HTTP 請求都會進入 `public/index.php`。
   // 入口檔案：所有請求的起點，負責載入自動加載器與啟動框架。
   // 白話：所有網頁請求都會先到這個檔案，這是 Laravel 的大門。

2. **自動加載（Autoload）**
   - 由 Composer 產生的 `vendor/autoload.php` 負責*自動載入所有 PHP 類別*。
   // *Composer*：PHP 的套件管理工具。
   // *Autoload*：自動載入類別機制，讓你不用手動引入每個檔案。
   // 白話：自動幫你把需要的 PHP 類別檔案載入進來，省去手動 require 的麻煩。

3. **啟動框架（Bootstrap）**
   - 載入 `bootstrap/app.php`，建立*應用程式實例（Application Instance）*。
   // *Application Instance*：整個 Laravel 應用的「服務容器」，負責管理所有服務與元件。
   // 白話：準備好 Laravel 的主體，像是把所有零件組裝成一台車。
      > 補充註解：每一次 HTTP 請求都會重新產生一個全新的 Application 實例，請求結束後這個實例就會被銷毀、記憶體釋放，不會累積。這是 PHP 傳統運作模式的設計，確保每個請求都是獨立、乾淨的環境。只有在特殊常駐型伺服器（如 Octane、Swoole）才會有 Application 實例重用的情況。

4. **HTTP Kernel 啟動**
   - 建立 `App\Http\Kernel` 實例，處理 HTTP 請求。
   // *Kernel*：核心流程控制器，*決定請求如何被處理*。
   // 白話：決定這台車要怎麼開、怎麼走流程。

5. **載入服務提供者（Service Providers）**
   - *啟動*所有註冊的服務提供者，*註冊*服務到服務容器。
   // *Service Provider*：註冊、啟動各種功能（如資料庫、驗證、事件等）的類別。
   // 白話：把所有功能（像資料庫、登入、信件等）都準備好，讓你隨時可以用。

6. **處理中介層（Middleware）**
   - 請求會經過一連串中介層（如驗證、CSRF、防護等）。
   // *Middleware*：在請求與回應之間攔截、處理資料的元件。
   // 白話：像過濾網，檢查請求是否合法、需不需要登入等。

7. **路由（Routing）**
   - 根據請求的 URL 與方法，找到對應的路由與控制器。
   // *Route*：定義 URL 與對應處理邏輯的規則。
   // *Controller*：負責處理請求邏輯的類別。
   // 白話：決定這個網址要交給哪個程式處理。

8. **執行控制器動作（Controller Action）**
   - 執行對應的控制器方法，處理業務邏輯，回傳回應。
   // *Action*：控制器中的一個方法，對應一個業務操作。
   // 白話：開始執行你寫的功能（例如查資料、計算、存檔等）。

9. **產生回應（Response）**
   - 控制器回傳的資料會被包裝成 Response 物件。
   // *Response*：HTTP 回應物件，負責輸出到瀏覽器。
   // 白話：把結果包裝好，準備送回給使用者。

10. **回應經過中介層（Middleware）**
    - 回應資料會再經過一遍中介層（如加密、壓縮等）。
    // 這是「出站」中介層，與進站相對。
    // 白話：回應出去前再檢查、加工一次（例如加密、壓縮）。

11. **送出回應（Send Response）**
    - 最終將回應送到使用者瀏覽器，結束本次請求。
    // 白話：把資料送回給用戶，這次請求就完成了。

--- 

Client
  ↓
public/index.php (入口檔案)
> 所有網頁請求都會先到這個檔案，這是 Laravel 的大門。
  ↓
Composer Autoload
> 自動幫你把需要的 PHP 類別檔案載入進來，省去手動 require 的麻煩。
  ↓
bootstrap/app.php 建立 Application 實例
> 準備好 Laravel 的主體，像是把所有零件組裝成一台車。
  ↓
App\Http\Kernel 啟動
> 決定這台車要怎麼開、怎麼走流程。
  ↓
載入 Service Providers
> 把所有功能（像資料庫、登入、信件等）都準備好，讓你隨時可以用。
  ↓
執行 Middleware
> 像過濾網，檢查請求是否合法、需不需要登入等。
  ↓
Routing 路由分派
> 決定這個網址要交給哪個程式處理。
  ↓
Controller 處理
> 開始執行你寫的功能（例如查資料、計算、存檔等）。
  ↓
Service / Repository / Model
> 白話：進一步處理資料、存取資料庫、商業邏輯等。
  ↓
產生 Response
> 白話：把結果包裝好，準備送回給使用者。
  ↓
Response 經過 Middleware
> 白話：回應出去前再檢查、加工一次（例如加密、壓縮）。
  ↓
回傳給 Client
> 白話：把資料送回給用戶，這次請求就完成了。 

---

**補充：Application、Service Container、Service Provider 的概念與關係**

- **Application**：
  - Laravel 啟動時建立的「*核心物件*」，類別為 `Illuminate\Foundation\Application`。
  - 本質上就是 Service Container 的本體（繼承自 Container）。
  - 集中管理 *所有服務、元件、設定與依賴注入* ，是整個框架的「大腦」和「總控中心」。
  - 比喻：一台剛組裝好的新車，裡面裝好了所有零件（服務、元件），每次請求都開一台新車跑流程。

- **Service Container（服務容器）**：
  - 用來「*註冊、解析、管理*」各種服務（物件、元件、依賴）的容器。
  - *Application 實例* 就是 Service Container 的實現。
  - 提供 *依賴注入、管理服務生命週期、單例、綁定、解析*。
  - 比喻：自動配件工廠，你要什麼服務，它就幫你組裝好給你。

- **Service Provider（服務提供者）**：
  - 一種「註冊服務」到 Service Container 的類別，通常放在 `app/Providers` 目錄下。
  - 負責在 *啟動時* 註冊和啟動各種服務、元件、事件、設定。
  - 讓 Laravel 可以模組化、擴充、客製化各種功能。
  - 比喻：安裝工人，負責把各種零件（服務）安裝到車子（Application/Service Container）裡。
  
  > **補充說明：註冊服務的主體與目標**
  > - 「註冊服務」這個動作的主體是 Service Provider，也就是你會在 Service Provider 的 `register()` 方法裡寫下要註冊哪些服務。
  > - 但「被註冊」的對象是 Service Container，所有服務（物件、單例、閉包、設定等）都會被註冊到 Service Container 裡。
  > - 具體流程：
  >   1. Service Provider 的 `register()` 方法被 Laravel 執行。
  >   2. 在 `register()` 裡呼叫 `$this->app->bind()`、`$this->app->singleton()` 等方法，把服務註冊到 Service Container。
  >   3. 之後你就可以用 `app('service_name')` 或 type-hint 方式解析這些服務。
  > - 簡單比喻：Service Provider = 註冊櫃檯（負責登記），Service Container = 倉庫（實際存放東西）。
  > - 結論：註冊服務這個動作是 Service Provider 的責任，但註冊的目標是 Service Container。
  > - 沒有 Service Provider，Service Container 也可以直接註冊服務（例如在 bootstrap/app.php 直接寫），但 Laravel 的設計是用 Service Provider 來集中管理註冊流程。

**三者關係總結**：
- Application 是整個 Laravel 的「大腦」和「總控中心」，本質上就是一個 Service Container。
- Service Provider 負責把各種服務註冊到 Application（Service Container）裡。
- 你要用的所有服務，最後都會透過 Application/Service Container 來取得。 

> 補充註解：
> **Service Container（服務容器）的類別**是 `Illuminate\Container\Container`，而 **Application 的類別**是 `Illuminate\Foundation\Application`。
> *Application 是 Container 的子類別（`Application extends Container`），所以 Application 既是 Application，也是 Service Container*。
> 你可以把 Service Container 想像成「工廠」，Application 則是「工廠的總部」，本身就是一個工廠，但還多了很多管理、調度、啟動的功能。 
> Illuminate\Foundation\Application **繼承** Illuminate\Container\Container。

> - *Application 實例*（Illuminate\Foundation\Application）本身就是 *Service Container*（Illuminate\Container\Container）的子類別，**兩者本質上是同一個物件**。
> - *Service Provider* 註冊的所有服務，實際上是存進 Application 實例（Service Container 實例）的**內部屬性**（如 bindings、instances 等）。
> - 你在任何地方 *type-hint* 依賴，Laravel 會根據 Service Provider 註冊的規則，從這個 Application 實例解析並注入對應的物件。
> - 所有服務的註冊、解析、依賴注入，最終都發生在同一個 *Application 實例（Service Container 實例）*裡。
---

> **Service Provider、Service Container、Application 關係線狀圖**
>
> 1. *Service Provider*（服務提供者）
>    │
>    │ 1.1 負責 *註冊各種服務*（如資料庫、事件、快取、路由、第三方元件等）
>    │ 1.2 每個 Service Provider 會在 *啟動時* 執行 **register()**、**boot()**方法
>    │
>    ▼
> 2. *Service Container*（服務容器）
>    │ 2.1 類別為 Illuminate.Container.Container
>    │ 2.2 Service Container：
>    │   - **註冊服務**：把各種服務（如資料庫、快取、Log、第三方元件等）註冊到容器裡
>    │   - **管理生命週期**：決定服務是每次都 new（多例），還是只 new 一次大家共用（單例）
>    │   - **依賴注入**：自動幫你把需要的 *物件塞進去*（constructor/method injection）
>    │   - **綁定/解析**：支援多種綁定方式（bind、singleton、scoped、條件綁定等），需要時 *自動解析出服務*
>    │   - **解耦合**：讓你的程式碼 *不用自己 new 物件* ，全部交給容器管理，方便測試與擴充
>    │
>    ▼
> 3. *Application*（應用程式實例）
>    │ 3.1 類別為 Illuminate.Foundation.Application
>    │ 3.2 Application **繼承自 Service Container**，本質上就是一個更強大的 Service Container
>    │ 3.3 Application 除了有 Service Container 的功能，還負責：
>    │   - **啟動流程**：載入 Service Provider、執行 boot 方法、初始化框架
>    │   - **Kernel 調度**：根據請求類型（HTTP/Console）選擇對應的 Kernel 處理
>    │   - **事件系統**：管理事件的註冊、觸發、監聽
>    │   - **路由系統**：管理路由的註冊、解析、分派
>    │   - **請求處理**：建立 Request 物件、處理 HTTP 請求
>    │   - **回應處理**：建立 Response 物件、處理 HTTP 回應
>    │ 3.4 每次請求都會建立一個全新的 Application 實例，確保每個請求的狀態、資源、依賴都是獨立的
>    │
>    ▼
> 4. 你在 Controller、Service、Middleware、Job、Event 等地方 *type-hint* 的依賴，
>    都是由 Application（Service Container）來解析與注入，
>    而這些服務大多是由 Service Provider 註冊進來的

> **type-hint（型別提示）** 是指在函式、方法或建構子的參數前面標註型別，告訴 PHP 這個參數必須是什麼類型。
> 例如：`public function __construct(FooService $foo)` 這裡的 `FooService` 就是 type-hint，Laravel 會根據這個型別自動注入對應的服務。

> - PHP 的 type-hint 可以標註 **「類別」、「介面」** 或 **「原生型別」（int、string、array...）**。
> - Laravel 的 *自動注入（依賴注入）* 只會發生在 **「類別」或「介面」** 型別，且只在建構子、Controller action、屬性注入（Laravel 9+）、或用容器 call 方法時才會自動注入。
> - 一般方法的 **原生型別 type-hint（如 int、string、array）** 只是 PHP 的型別檢查，Laravel 不會自動注入，必須自己傳值。

> - 常見的 type-hint 會出現在建構子（或方法）參數上，且只能標註「類別」或「介面」名稱。
> - *Laravel 只會根據「類別」或「介面」的 type-hint 自動注入對應的物件，原生型別（如 int、string、array）不會自動注入*。

> - Laravel 的**自動注入**必須同時滿足兩個條件：
> 1. 進入*依賴注入流程*（如建構子、Controller action、屬性注入、容器 call 方法）
> 2. 參數 *type-hint 是「類別」或「介面」*
> - 只有 type-hint 沒有經過依賴注入，Laravel 不會自動注入。
> - 只有依賴注入但 type-hint 不是類別/介面，Laravel 也不會自動注入。

> **總結：**
> - *Service Provider* 負責「**註冊**」服務
> - *Service Container* 負責「**管理、解析**」服務
> - *Application* 是 Service Container 的子類別，負責**整個框架的啟動與調度**
> - 你所有用到的服務，最後都會透過 Application/Service Container 來取得