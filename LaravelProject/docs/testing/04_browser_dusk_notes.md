# *Laravel Dusk 瀏覽器自動化 測試 筆記*

---

## 1. **簡介**（Introduction）

Laravel `Dusk` 提供直覺、易用的 __瀏覽器自動化與測試 API__。  
*預設* 使用 `ChromeDriver`，*無需* 安裝 `JDK` 或 `Selenium`，也可自訂其他 Selenium 相容 driver。

Dusk 是 Laravel 官方推出的*外部套件*，  
`專門用來撰寫和執行瀏覽器自動化測試`（例如 _模擬點擊、填表、驗證畫面內容_ 等）。  

安裝 Dusk 後，你可以*用簡單的語法操作瀏覽器*，  
**不需要**自己寫 `Selenium` 或 `WebDriver` 的低階程式碼。

---

## 2. **安裝與設定**（Installation）

### 2.1 *安裝 Dusk 套件*

```bash
composer require laravel/dusk --dev
```

---

### 2.2 *執行 dusk:install*

```bash
php artisan dusk:install
```

此指令會建立 `tests/Browser` 目錄，並在裡面放入一個範例 *Dusk 測試檔案*，以及安裝 `ChromeDriver`。

---

### 2.3 *設定 APP_URL*

請於 `.env` 設定 `APP_URL`，需與 __瀏覽器存取網址一致__。 
- 必須和你用 __瀏覽器打開網站的網址一樣__ ，這樣 **Dusk 測試** 才能正確存取你的網站。

- 如果你本機網站網址是 `http://localhost:8000`，  
  `.env` 裡就要設定：  
  `APP_URL=http://localhost:8000`

- 如果你用 `http://my-app.test` 存取，  
  `.env` 裡就要設定：  
  `APP_URL=http://my-app.test`

- 若用 Laravel `Sail`，請參考 Sail 官方文件。

---

### 2.4 *ChromeDriver 版本管理*

```bash
php artisan dusk:chrome-driver           # 安裝最新版
php artisan dusk:chrome-driver 86        # 安裝指定版本
php artisan dusk:chrome-driver --all     # 安裝所有支援版本
php artisan dusk:chrome-driver --detect  # 自動偵測 Chrome 版本
```

---

若遇**執行權限**問題，請執行：

```bash
chmod -R 0755 vendor/laravel/dusk/bin/
```

---

### 2.5 *使用其他瀏覽器*（`Selenium`）

`Selenium` 不是瀏覽器本身，而是一個**瀏覽器自動化工具**，  
它可以**控制多種瀏覽器**（像 _Chrome、Firefox、Safari、Edge）_，  
讓你 __用程式模擬使用者操作瀏覽器__ 。

在 `Dusk` 測試裡，除了**預設**的 `ChromeDriver`，  
也可以用 `Selenium` 來控制其他瀏覽器，  
例如用 _Firefox、Safari 或 PhantomJS_ 來執行自動化測試。

可自訂 `tests/DuskTestCase.php`，移除 `startChromeDriver`，並修改 `driver` 方法：

```php
use Facebook\WebDriver\Remote\RemoteWebDriver;

protected function driver(): RemoteWebDriver
{
    // 建立一個 RemoteWebDriver 實例，連線到 Selenium 伺服器（http://localhost:4444/wd/hub）
    // 並指定使用 PhantomJS 作為瀏覽器
    return RemoteWebDriver::create(
        'http://localhost:4444/wd/hub', DesiredCapabilities::phantomjs()
    );
}
```

---

## 3. **建立與執行測試**（Getting Started）

### 3.1 *產生 Dusk 測試*

```bash
php artisan dusk:make LoginTest
```

---

### 3.2 *資料庫重置*（不可用 RefreshDatabase）

__Dusk 測試不可用__ `RefreshDatabase`，請用：

`DatabaseMigrations`
`DatabaseTruncation`

---

#### 3.2.1 **DatabaseMigrations**

```php
use Illuminate\Foundation\Testing\DatabaseMigrations;
uses(DatabaseMigrations::class);
```

每次測試 __都重建__ 資料表，較慢。

---

#### 3.2.2 **DatabaseTruncation**

```php
use Illuminate\Foundation\Testing\DatabaseTruncation;
uses(DatabaseTruncation::class);
```

僅第一次 migrate，之後只 `truncate`（*清空資料表內容*） 資料表，較快。

---

- **DatabaseTruncation** 可自訂  `$tablesToTruncate`、
                                `$exceptTables`、
                                `$connectionsToTruncate`**屬性**，控制哪些資料表、哪些連線要執行 truncate，或排除不清空的資料表。

- 可定義 `beforeTruncatingDatabase`、`afterTruncatingDatabase` **方法**，在清空資料表前後執行自訂邏輯（例如初始化、記錄、通知等）。

```php
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use DatabaseTruncation;

    // 只清空 users 資料表
    protected $tablesToTruncate = ['users'];

    // 排除 users 資料表，不清空（和上面二選一）
    // protected $exceptTables = ['users'];

    // 只針對 mysql 連線執行 truncate
    protected $connectionsToTruncate = ['mysql'];

    // 清空前執行
    protected function beforeTruncatingDatabase(): void
    {
        // 這裡可以寫清空前要做的事
    }

    // 清空後執行
    protected function afterTruncatingDatabase(): void
    {
        // 這裡可以寫清空後要做的事
    }

    public function test_example()
    {
        // 測試邏輯...
    }
}
```

---

### 3.3 *執行測試*

```bash
php artisan dusk
php artisan dusk:fails
php artisan dusk --group=foo
```

---

### 3.4 *手動啟動 ChromeDriver*

一般情況下 Laravel Dusk 會自動執行 `startChromeDriver()`。

如需**手動啟動** `ChromeDriver`：

1. 請 _註解或移除_ `DuskTestCase` 類別內的 `startChromeDriver()` 方法，  
   這樣 Laravel Dusk __就不會自動啟動 ChromeDriver__。

2. 自行在 _終端機_ 啟動 `ChromeDriver`，並指定 `port`（例如 9515）：

```bash
./chromedriver --port=9515
```

3. 在 `DuskTestCase` 類別裡調整 `driver` 方法，**指定連線到你啟動的 port**：
   ```php
   protected function driver(): RemoteWebDriver
   {
       return RemoteWebDriver::create(
           'http://localhost:9515', DesiredCapabilities::chrome()
       );
   }
   ```

- 這樣可以讓你自行管理 `ChromeDriver` 的啟動與連線，  
  適合需要 __自訂瀏覽器啟動方式__ 或在 __特殊環境下__ 執行 Dusk 測試。


---

### 3.5 *環境檔處理*

建立 `.env.dusk.{environment}`，Dusk 執行時會自**動切換並還原**。

**概念**

- 當你執行 Dusk 測試時，Laravel 會自動把 `.env.dusk.{environment}`（例如 `.env.dusk.local`）複製成 `.env`，讓測試用專屬的環境設定。
- 測試結束後，Laravel 會 _自動還原_ 原本的 `.env` 檔案，避免影響正式環境。
- 這樣可以 _讓 Dusk 測試和開發、正式環境分開_ ，避免資料庫、API、設定等互相干擾。

**用途**

- 測試時用不同的資料庫、API、設定，不會影響平常開發或正式運作。
- 方便在 _多環境_（__local、staging、production__）下執行瀏覽器測試。

---

## 4. **瀏覽器操作基礎**（Browser Basics）

### 4.1 *建立瀏覽器實例與基本操作*

```php
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;

// 使用 DatabaseMigrations trait，每次測試前自動 migrate 資料庫
uses(DatabaseMigrations::class);

test('basic example', function () {
    // 建立一個測試用使用者
    $user = User::factory()->create([
        'email' => 'taylor@laravel.com',
    ]);

    // 使用 Dusk 的瀏覽器自動化 API 模擬登入流程
    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/login')                   // 造訪登入頁
            ->type('email', $user->email)           // 輸入 email
            ->type('password', 'password')          // 輸入密碼
            ->press('Login')                        // 按下登入按鈕
            ->assertPathIs('/home');                // 斷言登入後跳轉到 /home
    });
});
```

---

### 4.2 *多瀏覽器測試*

```php
$this->browse(function (Browser $first, Browser $second) {
    // 第一個瀏覽器登入 User 1，造訪 /home，等待訊息出現
    $first->loginAs(User::find(1))
        ->visit('/home')
        ->waitForText('Message');

    // 第二個瀏覽器登入 User 2，造訪 /home，等待訊息出現，輸入訊息並送出
    $second->loginAs(User::find(2))
        ->visit('/home')
        ->waitForText('Message')
        ->type('message', 'Hey Taylor')
        ->press('Send');

    // 第一個瀏覽器等待收到訊息，並斷言畫面有 'Jeffrey Way'
    $first->waitForText('Hey Taylor')
        ->assertSee('Jeffrey Way');
});
```
- 這個範例展示 Dusk 可以`同時操作`多個瀏覽器分頁，模擬多 _位使用者互動_（例如`聊天室`）。

---

### 4.3 *頁面導覽*

```php
$browser->visit('/login'); // 造訪指定網址
$browser->visitRoute($routeName, $parameters); // 造訪指定路由（可帶參數）
$browser->back(); // 返回上一頁
$browser->forward(); // 前往下一頁
$browser->refresh(); // 重新整理頁面
```

---

### 4.4 *視窗大小與位置*

```php
$browser->resize(1920, 1080); // 設定瀏覽器視窗大小為 1920x1080
$browser->maximize();         // 最大化瀏覽器視窗
$browser->fitContent();       // 自動調整視窗大小以符合內容
$browser->disableFitOnFailure(); // 停用測試失敗時自動調整視窗
$browser->move($x = 100, $y = 100); // 移動瀏覽器視窗到指定座標
```

---

### 4.5 *Browser Macro 自訂方法*

可於 `ServiceProvider` 註冊 `macro`：

```php
use Laravel\Dusk\Browser;

// 註冊 scrollToElement macro，讓 Browser 可以自訂滾動到指定元素
Browser::macro('scrollToElement', function (string $element = null) {
    // 執行 JavaScript，讓網頁自動滾動到指定元素的位置
    $this->script("$('html, body').animate({ scrollTop: $('$element').offset().top }, 0);");
    return $this;
});
```

---

__使用 macro__：

```php
$browser->scrollToElement('#credit-card-details'); // 滾動到指定的網頁元素
```
- 這樣可以擴充 Dusk 的 *Browser 物件* ，加入自訂功能，讓測試更方便。

---

### 4.6 *認證登入*

```php
$browser->loginAs(User::find(1)) // 以指定使用者身份登入
    ->visit('/home');             // 造訪 /home 頁面
```
- 這樣可以在 Dusk 測試中**模擬使用者`登入後`的操作流程**。

---

### 4.7 *Cookie 操作*

```php
$browser->cookie('name');              // 取得名為 'name' 的 cookie
$browser->cookie('name', 'Taylor');    // 設定名為 'name' 的 cookie 值為 'Taylor'

$browser->plainCookie('name');         // 取得未加密的 cookie
$browser->plainCookie('name', 'Taylor'); // 設定未加密的 cookie

$browser->deleteCookie('name');        // 刪除名為 'name' 的 cookie
```

---

### 4.8 *執行 JavaScript*

```php
$browser->script('document.documentElement.scrollTop = 0'); // 執行單一 JavaScript 指令
$browser->script([
    'document.body.scrollTop = 0',
    'document.documentElement.scrollTop = 0',
]); // 執行多個 JavaScript 指令
$output = $browser->script('return window.location.pathname'); // 執行 JS 並取得回傳值
```
- 可用於 __操作網頁、取得資料或驗證畫面狀態__ 。

---

### 4.9 *截圖與快照*

```php
$browser->screenshot('filename'); // 擷取整個畫面並儲存為指定檔名
$browser->screenshotElement('#selector', 'filename'); // 擷取指定元素的畫面，儲存為指定檔名
$browser->responsiveScreenshots('filename'); // 擷取多種螢幕尺寸的畫面（響應式），儲存為指定檔名
```
---

### 4.10 *儲存 Console Log 與 Page Source*

```php
$browser->storeConsoleLog('filename'); // 儲存瀏覽器的 console log 到指定檔案
$browser->storeSource('filename');     // 儲存目前頁面的 HTML 原始碼到指定檔案
``` 

---

## 5. **元素互動**（Interacting With Elements）

### 5.1 *Dusk Selectors*（推薦的元素選取方式）

傳統 `CSS selector` **易因前端變動而失效**，Dusk 建議於 __實際運行的__ HTML 元素加上 `dusk` 屬性， __測試時__ 用 `@` 前綴選取：

```html
<button dusk="login-button">Login</button>
```

```php
$browser->click('@login-button'); // 透過 dusk 屬性選取並點擊按鈕
```

---

可自訂 `selector 屬性名稱`（如改用 `data-dusk`）：

```php
use Laravel\Dusk\Dusk;
Dusk::selectorHtmlAttribute('data-dusk');
```

- 這樣就可以在 HTML 元素上用 `data-dusk="login-button"` 來標記，  
  Dusk 測試時會自動用這個屬性選取元素。

`dusk` 或 `data-dusk` 在 HTML 裡是「__屬性（attribute）__」，  
但在 Dusk 裡，這個屬性是用來「__選取（selector）__」元素的依據，  
所以官方稱為 **selector 屬性名稱**，  
意思是：_Dusk 會根據你設定的屬性來「選取」要操作的元素_。

HTML 本身沒有「_selector_」這個概念，  
**selector** 是 `CSS` 和 `JavaScript`（例如 jQuery）用來「_選取_」HTML 元素的語法，  
像 `.class`、`#id`、`[attribute=value]` 都是 **selector**。

---

### 5.2 *取得/設定值、文字、屬性*

```php
$value = $browser->value('selector'); // 取得 value
$browser->value('selector', 'value'); // 設定 value

$value = $browser->inputValue('field'); // 取得 input by name
$text = $browser->text('selector'); // 取得顯示文字
$attribute = $browser->attribute('selector', 'value'); // 取得屬性值

// 取得 input 欄位的值
$value = $browser->value('#username'); // 取得 id 為 username 的 input value

// 設定 input 欄位的值
$browser->value('#username', 'Alice'); // 設定 id 為 username 的 input value 為 'Alice'
$value=$browser->value('#username', 'Alice'); //不能這樣用

// 取得 name 為 email 的 input value
$email = $browser->inputValue('email');

// 取得按鈕的顯示文字
$text = $browser->text('button[type="submit"]');

// 取得元素的屬性值
$attr = $browser->attribute('#profile', 'data-user-id');
// 取得 id 為 profile 的元素上，`data-user-id` 這個屬性的值，  
// 例如 `<div id="profile" data-user-id="123">`，  
// 這行程式會取得 `123`。
```

---

### 5.3 *表單互動*

#### 5.3.1 **輸入欄位**

```php
// 在 name 為 email 的 input 欄位輸入 email
$browser->type('email', 'taylor@laravel.com');

// 先在 name 為 tags 的欄位輸入 foo，再 append 其他內容
$browser->type('tags', 'foo')->append('tags', ', bar, baz');

// 清空 email 欄位
$browser->clear('email');

// 慢速逐字輸入手機號碼
$browser->typeSlowly('mobile', '+1 (202) 555-5555');

// 慢速輸入，間隔 300 毫秒
$browser->typeSlowly('mobile', '+1 (202) 555-5555', 300);

// 慢速 append 標籤內容
$browser->appendSlowly('tags', ', bar, baz');
```

__補充說明__

- `typeSlowly` 和 `appendSlowly` 會模擬使用者 _「逐字」輸入_，  
  適合測試前端 JavaScript 事件（如 keyup、input）或動態驗證。
- `append` 和 `appendSlowly` 是在原本欄位內容 _後面「加上」新字串_，不是直接覆蓋原本內容。
- 這些方法讓 Dusk 測試 _更貼近真實操作_ ，可驗證表單互動、動態欄位、標籤輸入等複雜情境。


---

#### 5.3.2 **下拉選單**

```php
$browser->select('size', 'Large'); // 選取 value，模擬使用者在 name 為 size 的下拉選單選擇 'Large'，可用於驗證表單送出或資料處理流程
$browser->select('size'); // 隨機選取，模擬使用者隨機選擇一個選項，適合測試不同選擇情境或壓力測試
$browser->select('categories', ['Art', 'Music']); // 多選，模擬使用者在多選欄位同時選擇 'Art' 和 'Music'，適合測試複選欄位的資料處理
```

---

#### 5.3.3 **Checkbox / Radio**

```php
$browser->check('terms'); // 勾選，模擬使用者勾選 name 為 terms 的 checkbox
$browser->uncheck('terms'); // 取消勾選，模擬使用者取消勾選 checkbox
$browser->radio('size', 'large'); // 選取 radio，模擬使用者選擇 name 為 size、value 為 large 的 radio 按鈕
```

---

#### 5.3.4 **檔案上傳**

```php
$browser->attach('photo', __DIR__.'/photos/mountains.png'); // 上傳檔案，模擬使用者在 name 為 photo 的 input 欄位選擇 mountains.png 檔案
```

---

#### 5.3.5 **按鈕**

```php
$browser->press('Login'); // 按鈕文字或 selector，模擬使用者點擊「Login」按鈕
$browser->pressAndWaitFor('Save'); // 按下「Save」按鈕並等待頁面或元素啟用
$browser->pressAndWaitFor('Save', 1); // 按下「Save」按鈕，最多等 1 秒等待啟用
```

---

#### 5.3.6 **連結**

```php
$browser->clickLink($linkText); // 依文字點擊，模擬使用者點擊指定文字的超連結
if ($browser->seeLink($linkText)) { /* ... */ } // 判斷連結是否可見，適合驗證頁面是否有特定連結
```

---

### 5.4 *鍵盤操作*

```php
$browser->keys('selector', ['{shift}', 'taylor'], 'swift'); // 複雜輸入，模擬在指定欄位先按住 shift 輸入 taylor，再輸入 swift
$browser->keys('.app', ['{command}', 'j']); // 鍵盤快捷鍵，模擬在 .app 元素上按下 command+j 組合鍵
```

---

#### 5.4.1 **Fluent 鍵盤互動**

```php
use Laravel\Dusk\Browser;
use Laravel\Dusk\Keyboard;
// Laravel Dusk 的 Keyboard 類別本身就實作了 fluent interface，
use Tests\TestCase;

class KeyboardTest extends TestCase
{
    public function test_keyboard_actions()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/input-page')
                ->withKeyboard(function (Keyboard $keyboard) {
                    $keyboard->press('c')         // 按下 c 鍵
                        ->pause(1000)             // 暫停 1 秒
                        ->release('c')            // 放開 c 鍵
                        ->type(['c', 'e', 'o']);  // 依序輸入 c、e、o
                });
        });
    }
}
```

---

#### 5.4.2 **自訂 Keyboard Macro**

```php
// tests/Browser/KeyboardMacroTest.php
use Laravel\Dusk\Browser;
use Laravel\Dusk\Keyboard;
use Laravel\Dusk\OperatingSystem;
use Facebook\WebDriver\WebDriverKeys;
use Tests\TestCase;

// 建議將 macro 註冊移到 ServiceProvider 或 setUp 方法，這裡示範直接在 setUp
class KeyboardMacroTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 註冊 copy 快捷鍵 macro
        Keyboard::macro('copy', function (string $element = null) {
            $this->type([
                OperatingSystem::onMac() ? WebDriverKeys::META : WebDriverKeys::CONTROL, 'c',
            ]);
            return $this;
        });

        // 註冊 paste 快捷鍵 macro
        Keyboard::macro('paste', function (string $element = null) {
            $this->type([
                OperatingSystem::onMac() ? WebDriverKeys::META : WebDriverKeys::CONTROL, 'v',
            ]);
            return $this;
        });
    }

    public function test_copy_and_paste()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/input-page')
                ->withKeyboard(function (Keyboard $keyboard) {
                    $keyboard->copy();  // 執行複製快捷鍵
                    $keyboard->paste(); // 執行貼上快捷鍵
                });
        });
    }
}
```
- 這樣可以在 Dusk 測試中直接呼叫 `$keyboard->copy()` 或 `$keyboard->paste()`，  
  自動根據作業系統選擇正確的快捷鍵。

---

```php
$browser->click('@textarea') // 點擊第一個 textarea
    ->withKeyboard(fn (Keyboard $keyboard) => $keyboard->copy()) // 執行複製快捷鍵
    ->click('@another-textarea') // 點擊另一個 textarea
    ->withKeyboard(fn (Keyboard $keyboard) => $keyboard->paste()); // 執行貼上快捷鍵
```
- 這樣可以模擬使用者`在一個欄位複製內容，再貼到另一個欄位`。

---

### 5.5 *滑鼠操作*

```php
$browser->click('.selector'); // 點擊指定 selector 的元素
$browser->clickAtXPath('//div[@class = "selector"]'); // 以 XPath 選取並點擊元素
$browser->clickAtPoint($x = 0, $y = 0); // 點擊指定座標位置

$browser->doubleClick(); // 在目前元素上雙擊
$browser->doubleClick('.selector'); // 在指定 selector 元素上雙擊

$browser->rightClick(); // 在目前元素上右鍵點擊
$browser->rightClick('.selector'); // 在指定 selector 元素上右鍵點擊

$browser->clickAndHold('.selector'); // 在指定元素上按住滑鼠左鍵
$browser->clickAndHold()->pause(1000)->releaseMouse(); // 按住滑鼠左鍵 1 秒後放開

$browser->controlClick(); // 在目前元素上按住 Ctrl 並點擊
$browser->controlClick('.selector'); // 在指定 selector 元素上按住 Ctrl 並點擊

$browser->mouseover('.selector'); // 滑鼠移到指定 selector 元素上
```

---

#### 5.5.1 **拖曳操作**

```php
$browser->drag('.from-selector', '.to-selector'); // 拖曳元素，從 .from-selector 拖到 .to-selector
$browser->dragLeft('.selector', $pixels = 10);    // 將指定元素向左拖曳 10 像素
$browser->dragRight('.selector', $pixels = 10);   // 將指定元素向右拖曳 10 像素

$browser->dragUp('.selector', $pixels = 10);      // 將指定元素向上拖曳 10 像素
$browser->dragDown('.selector', $pixels = 10);    // 將指定元素向下拖曳 10 像素
$browser->dragOffset('.selector', $x = 10, $y = 10); // 將指定元素拖曳到指定 x, y 偏移量
```

---

### 5.6 *JavaScript Dialogs*

dialog（JavaScript Dialogs）是指 __瀏覽器彈出的對話框__。  
例如 `alert`、`confirm`、`prompt`，常用來 __顯示訊息、要求使用者確認或輸入資料__。  
Dusk 可以模擬、驗證這些對話框的行為。

```php
$browser->waitForDialog($seconds = null); // 等待瀏覽器彈出對話框
$browser->assertDialogOpened('Dialog message'); // 斷言對話框已開啟，並驗證訊息
$browser->typeInDialog('Hello World'); // 在對話框輸入內容
$browser->acceptDialog(); // 接受（確定）對話框
$browser->dismissDialog(); // 關閉（取
```

---

### 5.7 *iframe 互動*

```php
$browser->withinFrame('#credit-card-details', function ($browser) {
    $browser->type('input[name="cardnumber"]', '4242424242424242') // 在 iframe 內輸入卡號
        ->type('input[name="exp-date"]', '1224')                   // 輸入到期日
        ->type('input[name="cvc"]', '123')                         // 輸入安全碼
        ->press('Pay');                                            // 按下付款按鈕
});
```

---

### 5.8 *Scoping Selectors*（區域限定操作）

**區域限定操作**是指：  
`只在指定的網頁區塊（例如某個 div、table、form）內操作或驗證元素`，  
不會影響整個頁面，這樣可以避免選取到其他區塊同名的元素，讓測試更精確、更安全。

主要是用 `$browser->with()` 方法，只 __在指定 selector 的區塊內操作或驗證元素__ 。
也可以用 `$browser->elsewhere()` 或 `$browser->elsewhereWhenAvailable()`在區塊外操作或驗證其他元素。

```php
$browser->with('.table', function (Browser $table) {
    // with：只在 .table 區塊內操作，避免選到其他區塊同名元素
    $table->assertSee('Hello World') // 在 .table 區塊內斷言看到 'Hello World'
        ->clickLink('Delete');       // 點擊 'Delete' 連結
});

$browser->with('.table', function (Browser $table) {
    // elsewhere：在 .table 區塊外操作其他 selector
    $browser->elsewhere('.page-title', function (Browser $title) {
        $title->assertSee('Hello World'); // 在 .page-title 區塊外斷言看到 'Hello World'
    });
    // elsewhereWhenAvailable：等指定 selector 出現後才操作
    $browser->elsewhereWhenAvailable('.page-title', function (Browser $title) {
        $title->assertSee('Hello World'); // 等待 .page-title 區塊出現後再斷言
    });
});
```

---

### 5.9 *等待元素/條件*

#### 5.9.1 **基本等待**

```php
$browser->pause(1000); // 暫停 1000 毫秒（1 秒）
$browser->pauseIf(App::environment('production'), 1000); // 如果在 production 環境才暫停 1 秒
$browser->pauseUnless(App::environment('testing'), 1000); // 如果不是 testing 環境才暫停 1 秒
```

---

#### 5.9.2 **等待 selector/text/link/input**

```php
// 這些 wait 方法預設最多等 5 秒（可用第二參數自訂秒數），超過則測試失敗
// 例如 $browser->waitFor('.selector', 10); 最多等 10 秒

$browser->waitFor('.selector'); // 等待指定 selector 元素出現
$browser->waitFor('.selector', 1); // 最多等 1 秒

$browser->waitForTextIn('.selector', 'Hello World'); // 等待指定區塊內出現指定文字
$browser->waitForTextIn('.selector', 'Hello World', 1); // 最多等 1 秒

$browser->waitUntilMissing('.selector'); // 等待指定元素消失
$browser->waitUntilMissing('.selector', 1); // 最多等 1 秒

$browser->waitUntilEnabled('.selector'); // 等待元素變成可用（enabled）
$browser->waitUntilEnabled('.selector', 1); // 最多等 1 秒

$browser->waitUntilDisabled('.selector'); // 等待元素變成不可用（disabled）
$browser->waitUntilDisabled('.selector', 1); // 最多等 1 秒

$browser->waitForText('Hello World'); // 等待整個頁面出現指定文字
$browser->waitForText('Hello World', 1); // 最多等 1 秒

$browser->waitUntilMissingText('Hello World'); // 等待整個頁面指定文字消失
$browser->waitUntilMissingText('Hello World', 1); // 最多等 1 秒

$browser->waitForLink('Create'); // 等待頁面出現指定文字的連結
$browser->waitForLink('Create', 1); // 最多等 1 秒

$browser->waitForInput($field); // 等待指定 input 欄位出現
$browser->waitForInput($field, 1); // 最多等 1 秒
```

---

#### 5.9.3 **等待路徑/頁面重載**

```php
$browser->waitForLocation('/secret'); // 等待瀏覽器導向 /secret 頁面
$browser->waitForLocation('https://example.com/path'); // 等待瀏覽器導向指定網址

$browser->waitForRoute($routeName, $parameters); // 等待導向指定路由（可帶參數）
$browser->waitForReload(function (Browser $browser) {
    $browser->press('Submit'); // 按下 Submit 並等待頁面重新載入
})->assertSee('Success!');
$browser->clickAndWaitForReload('.selector')->assertSee('something'); // 點擊元素並等待頁面重新載入
```

---

#### 5.9.4 **等待 JS/Vue 條件/事件**

```php
$browser->waitUntil('App.data.servers.length > 0'); // 等待 JS 條件成立
$browser->waitUntil('App.data.servers.length > 0', 1); // 最多等 1 秒等待 JS 條件成立

$browser->waitUntilVue('user.name', 'Taylor', '@user'); // 等待 Vue 組件 user.name 變成 'Taylor'
$browser->waitUntilVueIsNot('user.name', null, '@user'); // 等待 Vue 組件 user.name 不為 null

$browser->waitForEvent('load'); // 等待頁面 load 事件
$browser->with('iframe', function (Browser $iframe) {
    $iframe->waitForEvent('load'); // 在 iframe 內等待 load 事件
});
$browser->waitForEvent('load', '.selector'); // 等待指定元素 load 事件
$browser->waitForEvent('scroll', 'document'); // 等待 document 觸發 scroll 事件
$browser->waitForEvent('resize', 'window', 5); // 最多等 5 秒等待 window resize 事件
```

---

#### 5.9.5 **waitUsing**（自訂等待條件）

```php
$browser->waitUsing(10, 1, function () use ($something) {
    return $something->isReady(); // 每 1 秒檢查一次，最多等 10 秒
}, "Something wasn't ready in time."); // 超過時間未達條件則顯示錯誤訊息
```

---

### 5.10 *滾動元素至可見區域*

```php
$browser->scrollIntoView('.selector') // 捲動頁面讓指定元素進入可見範圍
    ->click('.selector');             // 點擊該元素
``` 

---

## 6. **可用斷言方法總覽**（Available Assertions）

Dusk 提供豐富的*斷言方法*，可針對 _頁面、URL、Cookie、元素、Vue component_ 等進行驗證。

---

### 6.1 *URL 與頁面斷言*

```php
$browser->assertTitle($title); // 頁面標題相符
$browser->assertTitleContains($title); // 標題包含

$browser->assertUrlIs($url); // URL 完全相符

$browser->assertSchemeIs($scheme); // URL scheme 相符
$browser->assertSchemeIsNot($scheme); // URL scheme 不相符

$browser->assertHostIs($host); // host 相符
$browser->assertHostIsNot($host); // host 不相符

$browser->assertPortIs($port); // port 相符
$browser->assertPortIsNot($port); // port 不相符

$browser->assertPathBeginsWith('/home'); // path 開頭
$browser->assertPathEndsWith('/home'); // path 結尾
$browser->assertPathContains('/home'); // path 包含
$browser->assertPathIs('/home'); // path 完全相符
$browser->assertPathIsNot('/home'); // path 不相符

$browser->assertRouteIs($name, $parameters); // route 相符

$browser->assertQueryStringHas($name); // query string 有參數
$browser->assertQueryStringHas($name, $value); // query string 有參數且值相符
$browser->assertQueryStringMissing($name); // query string 無參數

$browser->assertFragmentIs('anchor'); // hash fragment 相符
$browser->assertFragmentBeginsWith('anchor'); // fragment 開頭
$browser->assertFragmentIsNot('anchor'); // fragment 不相符
```

---

### 6.2 *Cookie 斷言*

```php
$browser->assertHasCookie($name); // 有加密 cookie
$browser->assertHasPlainCookie($name); // 有未加密 cookie

$browser->assertCookieMissing($name); // 無加密 cookie
$browser->assertPlainCookieMissing($name); // 無未加密 cookie

$browser->assertCookieValue($name, $value); // 加密 cookie 值
$browser->assertPlainCookieValue($name, $value); // 未加密 cookie 值
```

---

### 6.3 *內容與元素斷言*

```php
$browser->assertSee($text); // 頁面有文字
$browser->assertDontSee($text); // 頁面無文字
$browser->assertSeeIn($selector, $text); // 指定 selector 有文字
$browser->assertDontSeeIn($selector, $text); // 指定 selector 無文字
$browser->assertSeeAnythingIn($selector); // selector 有任意文字
$browser->assertSeeNothingIn($selector); // selector 無任何文字

$browser->assertCount($selector, $count); // selector 出現次數

$browser->assertScript('window.isLoaded'); // JS 表達式為 true
$browser->assertScript('document.readyState', 'complete'); // JS 結果相符

$browser->assertSourceHas($code); // 原始碼有內容
$browser->assertSourceMissing($code); // 原始碼無內容

$browser->assertSeeLink($linkText); // 有連結
$browser->assertDontSeeLink($linkText); // 無連結

$browser->assertInputValue($field, $value); // input 值相符
$browser->assertInputValueIsNot($field, $value); // input 值不相符

$browser->assertChecked($field); // checkbox 已勾選
$browser->assertNotChecked($field); // checkbox 未勾選
$browser->assertIndeterminate($field); // checkbox 不確定狀態

$browser->assertRadioSelected($field, $value); // radio 已選
$browser->assertRadioNotSelected($field, $value); // radio 未選

$browser->assertSelected($field, $value); // select 已選
$browser->assertNotSelected($field, $value); // select 未選

$browser->assertSelectHasOptions($field, $values); // select 有多個選項
$browser->assertSelectMissingOptions($field, $values); // select 無多個選項
$browser->assertSelectHasOption($field, $value); // select 有單一選項
$browser->assertSelectMissingOption($field, $value); // select 無單一選項

$browser->assertValue($selector, $value); // 元素 value 相符
$browser->assertValueIsNot($selector, $value); // 元素 value 不符

$browser->assertAttribute($selector, $attribute, $value); // 屬性值相符
$browser->assertAttributeMissing($selector, $attribute); // 無屬性
$browser->assertAttributeContains($selector, $attribute, $value); // 屬性包含
$browser->assertAttributeDoesntContain($selector, $attribute, $value); // 屬性不包含
$browser->assertAriaAttribute($selector, $attribute, $value); // aria-* 屬性
$browser->assertDataAttribute($selector, $attribute, $value); // data-* 屬性

$browser->assertVisible($selector); // 元素可見

$browser->assertPresent($selector); // 元素存在於原始碼
$browser->assertNotPresent($selector); // 元素不存在於原始碼

$browser->assertMissing($selector); // 元素不可見

$browser->assertInputPresent($name); // input 存在
$browser->assertInputMissing($name); // input 不存在

$browser->assertDialogOpened($message); // JS dialog 開啟

$browser->assertEnabled($field); // 欄位啟用
$browser->assertDisabled($field); // 欄位停用

$browser->assertButtonEnabled($button); // 按鈕啟用
$browser->assertButtonDisabled($button); // 按鈕停用

$browser->assertFocused($field); // 欄位 focus
$browser->assertNotFocused($field); // 欄位未 focus
```

---

### 6.4 *認證斷言*

```php
$browser->assertAuthenticated(); // 已登入
$browser->assertGuest(); // 未登入
$browser->assertAuthenticatedAs($user); // 以指定 user 登入
```

---

### 6.5 *Vue component 斷言*

```php
$browser->assertVue('user.name', 'Taylor', '@profile-component'); // 屬性值相符
$browser->assertVueIsNot($property, $value, $componentSelector = null); // 屬性值不符
$browser->assertVueContains($property, $value, $componentSelector = null); // 陣列包含
$browser->assertVueDoesntContain($property, $value, $componentSelector = null); // 陣列不包含
```
```php
// 例：驗證 Vue 組件 user.name 屬性值
test('vue', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertVue('user.name', 'Taylor', '@profile-component'); // 斷言 @profile-component 的 user.name 為 'Taylor'
    });
});
```

---

## 7. **Pages 與 Components**（頁面物件與元件）

### 7.1 *Pages*（頁面物件）

Dusk Pages 可`將複雜操作封裝成易讀的動作方法`，並可定義常用 `selector 快捷鍵`。

---

#### 7.1.1 **產生 Page 物件**

```bash
php artisan dusk:page Login
```

Page 物件預設放在 `tests/Browser/Pages` 目錄。

---

#### 7.1.2 **Page 方法說明**

- `url()`：回傳*該頁面路徑*
- `assert(Browser $browser)`：斷言目*前在此頁面*
- `elements()`：定義 *selector 快捷鍵*（見下方）

```php
// 這個範例只是把路徑和斷言包成方法，  
// 還沒真正做到「Page 物件模式」的封裝。  
// Page 物件通常會獨立成一個類別，  
// 專門描述某個頁面的操作和驗證，  
// 讓測試更有結構、更容易維護。
use Laravel\Dusk\Browser;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function url(): string
    {
        return '/login'; // 回傳登入頁的路徑
    }

    public function assert(Browser $browser): void
    {
        $browser->visit($this->url()) // 造訪登入頁
            ->assertPathIs($this->url()); // 斷言目前路徑為 /login
    }

    public function test_login_page_url()
    {
        $this->browse(function (Browser $browser) {
            $this->assert($browser);
        });
    }
}
```

```php
// `tests/Browser/Pages/LoginPage.php` 這個類別就是 Page，專門描述登入頁的路徑、操作和驗證。
// Page 物件模式會把「頁面路徑、操作方法、驗證方法」都包在一個獨立類別（如 `LoginPage`），讓測試更有結構、更容易重複使用和維護。
// Page 物件模式適合大型專案或複雜流程，可以讓測試更清楚、可讀性更高。

// Page 物件**不能單獨使用**，  
// 必須在 Dusk 測試類別裡引用（use），  
// 然後用 Page 物件的方法來操作和驗證頁面。  
// Page 物件只是測試的輔助工具，  
// 不是獨立執行的程式。

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class LoginPage extends Page
{
    public function url()
    {
        return '/login'; // 登入頁路徑
    }

    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url()); // 斷言目前路徑為 /login
    }

    public function fillAndSubmit(Browser $browser, $email, $password)
    {
        $browser->type('email', $email)
                ->type('password', $password)
                ->press('Login');
    }
}
```

```php
// `tests/Browser/LoginPageTest.php` 是結合 Page 物件的 Dusk 測試，
// 會引用 Page 類別來操作頁面，比完全一般測試更有結構。
// 完全的一般測試則是在 test 類別裡直接寫路徑和斷言，沒有抽象成 Page 物件，程式較零散、不易擴充。

use Tests\Browser\Pages\LoginPage;
use Laravel\Dusk\Browser;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_login_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new LoginPage) // 使用 Page 物件
                    ->assertPathIs('/login');

            // 使用 Page 物件的方法填寫並送出表單
            (new LoginPage)->fillAndSubmit($browser, 'taylor@laravel.com', 'password');
        });
    }
}
```

---

#### 7.1.3 **導覽與載入 Page**

```php

use Tests\Browser\Pages\Login;
$browser->visit(new Login); // 在測試裡引用 Page 物件，造訪登入頁
```
- *Page 物件* 必須在`測試類別裡`這樣使用，不能單獨執行。


---

若已經透過操作（例如`點擊連結`）*跳轉到新頁面*時，可以用 `on(new Page物件)` 載入該頁面的 Page 物件，讓後續測試能用 Page 物件的方法來操作和驗證。：

```php
use Tests\Browser\Pages\CreatePlaylist;
$browser->visit('/dashboard')
    ->clickLink('Create Playlist')
    ->on(new CreatePlaylist) // 載入 Page 物件，讓後續操作有結構
    ->assertSee('@create');
```

---

#### 7.1.4 **Shorthand Selectors**（快捷 selector）

於 page 物件的 `elements()` 方法定義：

```php
// 在 Page 物件的 elements() 方法裡定義快捷 selector
public function elements(): array
{
    return [
        // '@email' 是自訂的快捷名稱，代表 input[name=email] 這個元素
        '@email' => 'input[name=email]',
    ];
}
```

*使用方式*：

```php
$browser->type('@email', 'taylor@laravel.com');
```

---

#### 7.1.5 **全域快捷 selector**

`tests/Browser/Pages/Page.php` 內可定義 `siteElements()`，全站通用：

```php
// 在 tests/Browser/Pages/Page.php 內定義 siteElements()，可讓所有 Page 物件共用
public static function siteElements(): array
{
    return [
        // '@element' 是全站通用的快捷 selector，代表 #selector 這個元素
        '@element' => '#selector',
    ];
}
```

---

#### 7.1.6 **自訂 Page 方法**

可於 page 物件自訂*常用動作*：

```php
// 可於 page 物件自訂常用動作，讓測試更簡潔
public function createPlaylist(Browser $browser, string $name): void
{
    $browser->type('name', $name)    // 輸入播放清單名稱
        ->check('share')             // 勾選分享選項
        ->press('Create Playlist');  // 按下建立按鈕
}
```

---

*使用方式*：

```php
use Tests\Browser\Pages\Dashboard;
$browser->visit(new Dashboard)
    ->createPlaylist('My Playlist')
    ->assertSee('My Playlist');
```

---

### 7.2 *Components*（元件物件）

*Component* 適合封`裝全站重複出現的 UI/功能`（如 __日期選擇器、導覽列__ 等），**不綁定特定 URL。**

---

#### 7.2.1 **產生 Component**

```bash
php artisan dusk:component DatePicker
```

Component 物件預設放在 `tests/Browser/Components` 目錄。

---

#### 7.2.2 **Component 範例**

```php
namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

// 定義 DatePicker 組件，繼承 Dusk 的 BaseComponent
class DatePicker extends BaseComponent
{
    // 定義組件的主選取器
    public function selector(): string
    {
        return '.date-picker'; // 組件的根元素 CSS selector
    }

    // 驗證組件是否可見
    public function assert(Browser $browser): void
    {
        $browser->assertVisible($this->selector()); // 斷言 date-picker 元素可見
    }

    // 定義組件內常用元素的快捷 selector
    public function elements(): array
    {
        return [
            '@date-field' => 'input.datepicker-input', // 日期輸入欄位
            '@year-list' => 'div > div.datepicker-years', // 年份選單區塊
            '@month-list' => 'div > div.datepicker-months', // 月份選單區塊
            '@day-list' => 'div > div.datepicker-days', // 日期選單區塊
        ];
    }

    // 自訂選擇日期的動作
    public function selectDate(Browser $browser, int $year, int $month, int $day): void
    {
        $browser->click('@date-field') // 點擊日期欄位，打開日期選擇器
            // 在年份選單區塊內選擇指定年份
            ->within('@year-list', function (Browser $browser) use ($year) {
                $browser->click($year);
            })
            // 在月份選單區塊內選擇指定月份
            ->within('@month-list', function (Browser $browser) use ($month) {
                $browser->click($month);
            })
            // 在日期選單區塊內選擇指定日期
            ->within('@day-list', function (Browser $browser) use ($day) {
                $browser->click($day);
            });
    }
}
```

---

#### 7.2.3 **Component 使用方式**

```php
use Tests\Browser\Components\DatePicker;

// 用 within 方法在 DatePicker 元件範圍內操作
$browser->visit('/')
    ->within(new DatePicker, function (Browser $browser) {
        $browser->selectDate(2019, 1, 30); // 在元件內選擇日期
    })
    ->assertSee('January'); // 斷言頁面有 January
```

---

可用 `component` 方法取得 __元件範圍的 browser 實例__：

```php
// 用 component 方法取得元件範圍的 browser 實例
$datePicker = $browser->component(new DatePickerComponent);
$datePicker->selectDate(2019, 1, 30); // 操作元件
$datePicker->assertSee('January');    // 驗證元件內容
```

---

### 7.3 *CI 整合*（Continuous Integration）

#### 7.3.1 **Heroku CI**

於 `app.json` 設定 `Chrome buildpack` 與 `scripts`：

```json
{
  "environments": {
    "test": {
      "buildpacks": [
        { "url": "heroku/php" },
        { "url": "https://github.com/heroku/heroku-buildpack-chrome-for-testing" }
      ],
      "scripts": {
        "test-setup": "cp .env.testing .env", // 測試前複製環境檔
        "test": "nohup bash -c './vendor/laravel/dusk/bin/chromedriver-linux --port=9515 > /dev/null 2>&1 &' && nohup bash -c 'php artisan serve --no-reload > /dev/null 2>&1 &' && php artisan dusk" // 啟動 chromedriver、Laravel 伺服器並執行 Dusk 測試
      }
    }
  }
}
```

---

#### 7.3.2 **Travis CI**

`.travis.yml` 範例：

```yaml
language: php
php:
  - 8.2
addons:
  chrome: stable
install:
  - cp .env.testing .env # 複製測試環境檔
  - travis_retry composer install --no-interaction --prefer-dist # 安裝依賴
  - php artisan key:generate # 產生 APP_KEY
  - php artisan dusk:chrome-driver # 安裝 ChromeDriver
before_script:
  - google-chrome-stable --headless --disable-gpu --remote-debugging-port=9222 http://localhost & # 啟動 Chrome 瀏覽器
  - php artisan serve --no-reload & # 啟動 Laravel 伺服器
script:
  - php artisan dusk # 執行 Dusk 測試
```

---

#### 7.3.3 **GitHub Actions**

`.github/workflows/ci.yml` 範例：

```yaml
name: CI
on: [push]
jobs:
  dusk-php:
    runs-on: ubuntu-latest
    env:
      APP_URL: "http://127.0.0.1:8000"
      DB_USERNAME: root
      DB_PASSWORD: root
      MAIL_MAILER: log
    steps:
      - uses: actions/checkout@v4
      - name: Prepare The Environment
        run: cp .env.example .env # 複製環境設定檔
      - name: Create Database
        run: |
          sudo systemctl start mysql
          mysql --user="root" --password="root" -e "CREATE DATABASE `my-database` character set UTF8mb4 collate utf8mb4_bin;" # 建立測試資料庫
      - name: Install Composer Dependencies
        run: composer install --no-progress --prefer-dist --optimize-autoloader # 安裝 PHP 套件
      - name: Generate Application Key
        run: php artisan key:generate # 產生 Laravel APP_KEY
      - name: Upgrade Chrome Driver
        run: php artisan dusk:chrome-driver --detect # 安裝/升級 ChromeDriver
      - name: Start Chrome Driver
        run: ./vendor/laravel/dusk/bin/chromedriver-linux --port=9515 & # 啟動 ChromeDriver
      - name: Run Laravel Server
        run: php artisan serve --no-reload & # 啟動 Laravel 伺服器
      - name: Run Dusk Tests
        run: php artisan dusk # 執行 Dusk 瀏覽器測試
      - name: Upload Screenshots
        if: failure()
        uses: actions/upload-artifact@v4
        with:
          name: screenshots
          path: tests/Browser/screenshots # 測試失敗時上傳截圖
      - name: Upload Console Logs
        if: failure()
        uses: actions/upload-artifact@v4
        with:
          name: console
          path: tests/Browser/console # 測試失敗時上傳 console log
```

---

#### 7.3.4 **Chipper CI**

`.chipperci.yml` 範例：

```yaml
version: 1
environment:
  php: 8.2
  node: 16
services:
  - dusk
on:
   push:
      branches: .*
pipeline:
  - name: Setup
    cmd: |
      cp -v .env.example .env # 複製環境設定檔
      composer install --no-interaction --prefer-dist --optimize-autoloader # 安裝 PHP 套件
      php artisan key:generate # 產生 APP_KEY
      cp -v .env .env.dusk.ci # 複製 Dusk 測試環境檔
      sed -i "s@APP_URL=.*@APP_URL=http://$BUILD_HOST:8000@g" .env.dusk.ci # 設定 APP_URL
  - name: Compile Assets
    cmd: |
      npm ci --no-audit # 安裝前端依賴
      npm run build # 編譯前端資源
  - name: Browser Tests
    cmd: |
      php -S [::0]:8000 -t public 2>server.log & # 啟動 PHP 內建伺服器
      sleep 2 # 等待伺服器啟動
      php artisan dusk:chrome-driver $CHROME_DRIVER # 安裝 ChromeDriver
      php artisan dusk --env=ci # 執行 Dusk 測試（使用 ci 環境）
```