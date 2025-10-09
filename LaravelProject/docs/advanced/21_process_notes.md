# *Laravel Process 筆記*

---

## 1. **簡介**（Introduction）

### 1.1 *什麼是 process？*

- __process（程序）__：在作業系統中，指的是`一個正在執行的應用程式或指令`（如你在終端機輸入 `ls -la`、`php artisan migrate`，每一個都是一個 process）。

- 每個 `process` 都有自己的 __記憶體空間、執行緒、環境變數、工作目錄__ 等。

- process 是`作業系統層面`的概念，指的是 __一個正在執行的應用程式或指令__。

- 當你啟動 Laravel 應用程式（例如執行 `php artisan serve` 或透過 `Web Server` 啟動），作業系統會為該執行環境分配一個 process。


<!-- process（程序）是作業系統層面的意思，
     因為它是由作業系統負責管理和分配資源（如記憶體、CPU），
     每個 process 都有自己的執行空間和資源，
     作業系統會決定哪些 process 何時執行、如何溝通、何時終止。 -->
     
<!-- 「作業系統層面」指的是這些功能是由 OS（如 Windows、Linux、macOS）負責，
      而不是單一程式或應用自己管理。 -->

<!-- process（程序）是作業系統的概念，
     Laravel 只是執行在某個 process 裡，
     本身不負責管理 process。 -->

<!-- Laravel 本身不能直接管理或生成 process，
     但你可以用 PHP 的 proc_open、Symfony Process 套件，
     或呼叫外部指令來啟動新的 process，
     這些都是依賴作業系統的功能，不是 Laravel 內建。 -->

---

- __Process 是只有一個，還是一個行為就有一個？__

    - *單一 Process*
        - __在內建伺服器中__：
            - 如果你使用像 PHP 的`內建伺服器`（例如 `php artisan serve`），通常只有 _一個 process 負責處理所有的 HTTP Request_。
            - 在像 `php artisan serve` 的環境中，*Web Server 和 Process 是同一個程序*。
            - 這個 process 是 *持續運行* 的，等待並處理每個進入的 HTTP Request。
            - 每個行為（例如`路由匹配、控制器`執行）都在這個單一 process 中完成。
            - 如果你啟動 Laravel 應用程式的方式是：`php artisan serve`，那麼你使用的是 單一 Process 架構。

    - *多個 Process*
        - __在多進程架構中__：
            - 如果你使用像 `Apache` 或 `Nginx` 配合 `PHP-FPM`（PHP FastCGI Process Manager），__每個 HTTP Request 可能會啟動一個新的 process__。

            <!-- FastCGI 是一種網頁伺服器與應用程式之間的通訊協定，
                 用來提升 CGI（Common Gateway Interface）的效能，
                 支援長時間運作的程序、減少啟動開銷，
                 常用於 PHP、Python 等動態網頁技術。 -->

            <!-- CGI（Common Gateway Interface）是一種早期網頁技術標準，
                 用來讓網頁伺服器和外部程式（如 PHP、Perl、Python）互動。
                 當使用者在瀏覽器發送請求時，伺服器會根據 CGI 規範啟動外部程式，
                 外部程式處理資料後，把結果（通常是 HTML）回傳給伺服器，再由伺服器回應給使用者。
                 CGI 的優點是可以「產生動態內容」，但缺點是每次請求「都要重新啟動程式」，效能較低。
                 後來才有 FastCGI、PHP-FPM 等改良方案，提升效能和資源利用率。 -->

            <!-- - 輸入資料格式（如環境變數、POST/GET 資料）
                 - 程式如何啟動與結束
                 - 輸出資料格式（如 HTTP header、內容）
                 - 錯誤處理方式
                 - 與伺服器溝通的流程 -->

            - 在像 `Nginx` 或 `Apache` 配合 `PHP-FPM` 的環境中，*Web Server 和 Process 是分離的*。
            - 流程：
                    - `Web Server` 接收 *HTTP Request*。
                    - `Web Server` 將請求代理給 *PHP-FPM*。
                    - `PHP-FPM` 為每個請求分配一個獨立的 *Process。*
                    - `Process` 處理請求，並返回 *Response。*
                    - `Web Server` 接收 Response，並返回給*客戶端*。

            - `PHP-FPM` 是 __多進程架構__，會為每個 `Request` 分配一個獨立的 process。
            - 這意味著每個行為（例如處理 `/users` 的 Request）都會在自己的 process 中執行。
            - 如果你的 `Web Server` 是 `Nginx` 或 `Apache`，並且 PHP 是透過 `PHP-FPM` 運行，那麼你使用的是 *多  Process 架構*。
            - `Nginx：` 檢查是否有 `fastcgi_pass` 指令，表示使用 `PHP-FPM`：
                     ```bash
                     location ~ \.php$ {
                      fastcgi_pass 127.0.0.1:9000;
                      fastcgi_index index.php;
                      include fastcgi_params;
                     }
                     ```
            - `Apache：` 檢查是否啟用了 `mod_proxy_fcgi` 模組，並指向 `PHP-FPM`：
                      ```bash
                      <FilesMatch \.php$>
                      SetHandler "proxy:unix:/var/run/php/php7.4-fpm.sock|fcgi://localhost"
                      </FilesMatch>
                      ```
            - *反向代理* 是 `網路層的中介`，負責：
                    - __接收__ 客戶端的請求。
                    - 將 __請求分配__ 到`後端伺服器`（如 _Web Server_ 或 _應用程式伺服器_）。
                    - 負責 __負載平衡__、__安全性__（如 HTTPS）、__請求過濾__ 等。

            - *Web Server* 是`伺服器端的入口`，負責：
                    - 提供 __靜態資源__（如 HTML、CSS、JS）。
                    - __處理 HTTP 請求__，或者將請求代理給`後端應用程式`（如 Laravel）。
                    - 與`後端應用程式`（如 PHP-FPM）協作，執行動態邏輯。

            - *分離架構*：__客戶端（Client） → 反向代理（Nginx） → Web Server（Apache） → Laravel（PHP-FPM）__
                    - 某些架構中，反向代理和 Web Server 是 __分離__ 的：
                        - `反向代理`（Nginx 或 Apache）：
                            - 接收客戶端的請求。
                            - 將請求代理給 Web Server。
                        - `Web Server`（Nginx 或 Apache）：
                            - *處理請求*，或者將請求代理給後端應用程式（如 Laravel）。
                                - __靜態資源處理__：
                                    - 如果請求的 URL 對應到伺服器上的`靜態檔案`（如 HTML、CSS、JS、圖片），Web Server 直接返回該檔案。
                                    - 請求 `/images/logo.png`，Web Server 返回 logo.png 檔案。
                                - __動態請求處理__：
                                    - 如果請求需要`執行後端邏輯`（如 Laravel 的路由匹配、控制器執行），Web Server 將請求代理給後端應用程式（如 PHP-FPM）。
                                    - 請求 `/users`，Web Server 將請求代理給 Laravel，Laravel 返回 JSON 資料。
                                - __錯誤處理__：
                                    - 如果`請求的 URL 不存在`或`伺服器無法處理`，Web Server 返回錯誤回應（如 _404 Not Found_ 或 _500 Internal Server Error_）。
                        - `後端應用程式`（Laravel）：
                            - __執行業務邏輯__，並生成 HTTP Response。

            - *合併架構*：__客戶端（Client） → Nginx（反向代理 + Web Server） → Laravel（PHP-FPM）__
                    - 在某些架構中，反向代理和 Web Server 是 __同一個軟體__（例如 Nginx 同時充當反向代理和 Web Server）：
                        - Nginx 作為`反向代理`：
                            - 接收客戶端的請求。
                            - 將請求代理給後端應用程式（如 Laravel）。
                        - Nginx 作為 `Web Server`：
                            - 提供靜態資源，並與 _PHP-FPM_ 協作執行動態邏輯。
                        - `後端應用程`式（Laravel）：
                            - 執行業務邏輯，並生成 HTTP Response。

---

- __Process 的數量取決於執行環境__

    - *單一 Process 的情況*
        - __PHP 的內建伺服器__（例如 `php artisan serve`）：
            - 只有一個 process 持續運行，__負責處理所有的 HTTP Request__。
            - 每個行為（例如路由匹配、控制器執行）都在這個單一 process 中完成。
            - *快速啟動*：
                - `不需要`額外配置 Web Server（如 Nginx 或 Apache），__只需執行指令即可啟動__。
            - *適合開發*：
                提供`快速測試`和`開發`的環境。
                
    - *多個 Process 的情況*
        - __PHP-FPM 配合 Nginx 或 Apache__：
            - `PHP-FPM` 是多進程架構，會為每個 HTTP Request 分配一個`獨立的 process`。
                - PHP-FPM（PHP FastCGI Process Manager）是一個*專門用來處理 PHP 程式的進程管理器*。
                - 它採用 *多進程架構*，可以同時處理多個 HTTP Request。
                - *高性能*：
                    - 適合`生產環境`，能夠處理高併發的請求。
                - *與 Web Server 配合*：
                    - 通常與 Nginx 或 Apache 配合使用，提供更靈活的配置和功能。
            - 每個 process 是獨立的，並且只負責處理一個 Request。
            - 當 Request 完成後，該 process _可能會被回收或重新分配_ 給下一個 Request。

---

- __Process 的行為與數量的關係__

    - *單一 Process 的行為*
        - __在單一 process 中（例如 `php artisan serve`），所有的行為都在同一個 process 中完成__：
            - 等待 HTTP Request。
            - 路由匹配。
            - 控制器執行。
            - 返回 Response。

    - *多個 Process 的行為*
        - __在多進程架構中（例如 PHP-FPM），每個 HTTP Request 都會啟動一個獨立的 process__：
            - *Process 1*：負責處理 `/users` 的 Request。
            - *Process 2*：負責處理 `/posts` 的 Request。
            - *Process 3*：負責處理 `/comments` 的 Request。

---

- __Web Server__
    - Web Server 是 *HTTP Request 的入口*，負責接收來自`客戶端`（瀏覽器或 API 客戶端）的請求。
    - 它的主要功能包括：
        - *接收 HTTP Request*：從網路中接收請求。
        - *代理請求*：將請求轉發給後端應用程式（如 Laravel）。
        - *返回 HTTP Response*：將後端應用程式生成的 Response 返回給客戶端。

---

### 1.2 *Laravel Process 的用途*

- __執行外部指令__：如 `shell script`、`系統工具`、`第三方 CLI`。
- __取得執行結果__：可取得`標準輸出`（stdout）、`錯誤輸出`（stderr）、`exit code`。
- __非同步執行__：可讓指令在`背景執行`，不阻塞主程式。
- __管線串接__：可將多個指令`串接`（pipe）起來，像在 `shell` 用 `|` 一樣。
- __測試與假資料__：可在測試時 `fake` 外部指令，避免真的執行系統命令。

---

### 1.3 *常見應用場景*

- 自動化部署、資料備份、批次處理
- 呼叫 `ffmpeg`、`imagemagick` 等 `CLI` 工具處理檔案
<!-- ffmpeg：一個強大的影音處理工具，能轉檔、剪輯、壓縮、抽取音訊、合併影片等，支援多種影音格式。 -->
<!-- imagemagick：一個功能齊全的圖片處理工具，能轉檔、縮放、裁切、加水印、調整顏色等，支援多種圖片格式。 -->
- 執行 `shell script` 或 `bash` 指令
- 在 Laravel 任務、排程、API 內部觸發外部工具

---

### 1.4 *實際例子*

```php
use Illuminate\Support\Facades\Process;

// Laravel 提供了一個名為 Process Facade 的工具，用來執行外部程序或指令。這個工具可以幫助你在 Laravel 應用程式中與作業系統層面的 process（程序） 進行互動。

// Process Facade 是 Laravel 提供的一個工具，用來執行外部指令或程序（例如 Shell Script、系統命令）。
// 它基於 Symfony 的 Process Component，封裝了與外部程序交互的功能。

// Symfony 是一個開源的 PHP 框架，提供許多可重用的元件（如路由、事件、郵件等），
// 可用來開發高效能、可維護的 Web 應用程式。
// Laravel 也大量使用 Symfony 的底層元件來實作功能。

// 功能：
    // 執行外部指令（如 ls、bash、php artisan）。
    // 捕捉程序的輸出（標準輸出和錯誤輸出）。
    // 支援非同步執行和即時輸出。
    // 管理程序的執行狀態（如檢查是否正在執行、是否超時）。

// 執行 ls -la 並取得結果
$result = Process::run('ls -la');

if ($result->successful()) {
    echo $result->output(); // 顯示目錄內容
}
```

---

- Laravel 封裝 `Symfony Process` 元件，提供簡潔、易用的 API，讓你能方便地從 Laravel 應用程式中`呼叫外部程序`。

  重點特色：
    - API 精簡、語意明確
    - 支援 *同步與非同步* 執行
    - 可自訂 *工作目錄、環境變數、超時* 等
    - 方便 *測試與假資料*

---

## 2. **呼叫外部程序**（Invoking Processes）

### 2.1 *同步執行*

- 使用 `Process::run()` **執行指令並等待結束**
<!-- Process::run()：同步執行指令，會等指令執行完才繼續，取得完整結果。 -->

```php
use Illuminate\Support\Facades\Process;

$result = Process::run('ls -la');
return $result->output(); // 取得標準輸出
```

---

### 2.2 *檢查程序結果*

- `run()` 回傳 `ProcessResult`，可用下列方法檢查：

```php

$result = Process::run('ls -la');

$result->successful();    // 是否成功（exit code = 0）
$result->failed();        // 是否失敗
$result->exitCode();      // 取得 exit code
$result->output();        // 取得標準輸出（stdout）
$result->errorOutput();   // 取得錯誤輸出（stderr）
```

---

### 2.3 *非同步執行*

- 使用 `Process::start()` **非同步執行，主程式可繼續執行**
<!-- Process::start()：非同步啟動指令，啟動後不會等指令結束，可以同時處理其他事情。 -->

```php
// 使用 Process::start() 非同步執行外部程式
$process = Process::start('bash import.sh');
/*
    1. `Process::start()`：
       - 啟動外部程式（如 `bash import.sh`）並以非同步方式執行。
       - 主程式不會被阻塞，可以繼續執行其他邏輯。
       - 適合需要在外部程式執行期間處理其他工作的場景。
*/

// 主程式可以繼續執行其他邏輯
// ...

// 等待外部程式完成並取得結果
$result = $process->wait();
/*
    1. `wait()` 的作用：
       - 等待外部程式執行完成，確保可以安全地取得執行結果。
       - 如果不使用 `wait()`，主程式可能在外部程式完成之前嘗試使用結果，導致資料不完整或錯誤。
    2. 使用場景：
       - 當主程式需要依賴外部程式的執行結果（例如匯入資料的結果）時，必須使用 `wait()`。
       - 如果外部程式的結果與主程式無關（例如記錄日誌或觸發通知），可以不使用 `wait()`。
    3. 非同步的好處：
       - 主程式可以在等待外部程式完成的同時執行其他邏輯，提高效率。
*/
```

---

## 3. **例外處理**（Throwing Exceptions）

### 3.1 *自動丟出例外*

- 若 **exit code > 0**，`throw()` 會丟出 `ProcessFailedException`

```php
// Symfony\Component\Process\Process 和 Symfony\Component\Process\Exception\ProcessFailedException 是 Symfony 提供的工具，任何 PHP 檔案都可以使用它們，只要你有正確地引入這些類別並且 Symfony 的 Process 套件已安裝。
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

// 啟動外部程式
$process = Process::start('bash import.sh');

// 等待外部程式完成
$result = $process->wait();

// 如果外部程式的 exit code > 0，丟出 ProcessFailedException
if ($process->getExitCode() > 0) {
    $process->throw();
    /*
        1. `exit code`：
           - 外部程式執行完成後，會返回一個 exit code（退出碼）。
           - `exit code` 是用來表示程式執行的狀態：
             - `0`：表示程式執行成功。
             - `> 0`：表示程式執行失敗，通常代表錯誤。
        2. `getExitCode()`：
           - 取得外部程式的 exit code。
           - 如果 exit code > 0，表示程式執行失敗。
        3. `throw()`：
           - 如果 exit code > 0，會丟出 `ProcessFailedException`。
           - `ProcessFailedException` 是用來表示外部程式執行失敗的例外。
           - 可以捕捉這個例外並進行錯誤處理。
    */
}

// 處理結果
echo $result;
```

---

### 3.2 *條件丟出例外*

- `throwIf($condition)` **條件成立**時，丟 _例外_

```php
$result = Process::run('ls -la')->throwIf($condition);
```

---

## 4. **程序選項**（Process Options）

### 4.1 *指定工作目錄*

- **工作目錄**是指`指令執行時的基準目錄`。
- 某些指令（如 ls 或 bash import.sh）`需要在特定目錄中執行`，才能正確找到檔案或資源。

```php
use Symfony\Component\Process\Process;

require 'vendor/autoload.php';

// 指定工作目錄並執行指令
$result = Process::path(__DIR__)->run('ls -la');
/*
    1. `Process::path(__DIR__)`：
       - 指定工作目錄（Working Directory），表示指令會在該目錄中執行。
       - `__DIR__` 是 PHP 的魔術常數，表示目前程式碼所在檔案的目錄。
    2. `run('ls -la')`：
       - 執行指令 `ls -la`，列出工作目錄中的檔案和目錄。
    3. 為什麼要指定工作目錄：
       - 某些指令（如 `ls` 或 `bash import.sh`）需要在特定目錄中執行。
       - 如果不指定工作目錄，指令會在預設的工作目錄（通常是專案的根目錄）中執行，可能導致指令無法正確執行或找不到檔案。
    4. 回傳結果：
       - `run()` 方法執行指令後，返回指令的輸出結果（如檔案列表）。
*/
```

---

### 4.2 *標準輸入*

- **標準輸入**是指`將資料直接傳遞給外部程式的輸入管道`（stdin/Standard Input）。
  - 標準輸入（stdin） 是一種 __資料流__，用於**將資料從使用者或程式傳遞給外部程式**。
  - 它是 __程式與外部環境__（例如命令列或其他程式）之間的 __溝通管道__ 之一。
  
- 某些**指令**（如 cat 或其他需要輸入的程式）可以`透過標準輸入接收資料`。

```php
use Symfony\Component\Process\Process;

require 'vendor/autoload.php';

// 傳遞標準輸入並執行指令
$result = Process::input('Hello World')->run('cat');
/*
    1. `Process::input('Hello World')`：
       - 使用 `input()` 方法將資料（如 `'Hello World'`）傳遞給外部程式的標準輸入。
       - `'Hello World'` 是要傳遞的資料。
    2. `run('cat')`：
       - `cat` 是外部程式，負責接收標準輸入的資料。
       - 標準輸入的內容是 `'Hello World'`。
       - 執行指令 `cat`，該指令會從標準輸入接收資料並輸出。
       - 在這裡，`cat` 會輸出 `'Hello World'`。
    3. 為什麼要使用標準輸入：
       - 某些指令需要透過標準輸入接收資料（例如 `cat`、`grep` 或其他需要輸入的程式）。
       - 使用標準輸入可以避免依賴檔案，直接傳遞資料給外部程式。
    4. 回傳結果：
       - `run()` 方法執行指令後，返回指令的輸出結果（如 `'Hello World'`）。
*/
```
<!-- cat：用來顯示、串接檔案內容。例如 `cat file.txt` 會把 file.txt 的內容顯示出來。 -->
<!-- grep：用來搜尋檔案內容，支援關鍵字、正則表達式。例如 `grep error log.txt` 會在 log.txt 裡找出含有 error 的行。 -->

---

### 4.3 *超時設定*

- **超時設定** 用於`限制外部程式的執行時間`，避免程式因外部指令執行過久而阻塞。
- **預設** 執行時間限制為 _60 秒_，超過時間會丟出 `ProcessTimedOutException`。


```php
use Symfony\Component\Process\Process;

require 'vendor/autoload.php';

// 設定超時時間為 120 秒
$result = Process::timeout(120)->run('bash import.sh');
/*
    1. `Process::timeout(120)`：
       - 設定外部程式的最大執行時間為 120 秒。
       - 如果外部程式執行超過 120 秒，會丟出 `ProcessTimedOutException`。
    2. `run('bash import.sh')`：
       - 執行指令 `bash import.sh`。
       - 如果指令在 120 秒內完成，返回執行結果。
    3. 為什麼需要超時設定：
       - 防止外部程式執行過久，導致主程式阻塞或資源耗盡。
    4. 回傳結果：
       - 如果指令在設定的時間內完成，返回指令的輸出結果。
       - 如果超時，丟出 `ProcessTimedOutException`。
*/
```

---

#### 4.3.1 **永不超時**

- *永不超時* 用於`取消執行時間限制`，允許外部程式執行直到完成。

```php
$result = Process::forever()->run('bash import.sh');
/*
    1. `Process::forever()`：
       - 設定外部程式永不超時。
       - 外部程式可以執行直到完成，不受時間限制。
    2. 使用場景：
       - 當外部程式的執行時間不可預測，且需要等待其完成時使用。
    3. 注意事項：
       - 永不超時可能導致主程式長時間阻塞，需謹慎使用。
*/
```

---

#### 4.3.2 **閒置超時**

- *閒置超時* 用於`限制外部程式在無輸出的情況下的最大等待時間`。
- `idleTimeout`：無輸出時的最大秒數

```php
$result = Process::timeout(60)->idleTimeout(30)->run('bash import.sh');
/*
    1. `Process::timeout(60)`：
       - 設定外部程式的最大執行時間為 60 秒。
    2. `idleTimeout(30)`：
       - 設定外部程式在無輸出的情況下的最大等待時間為 30 秒。
       - 如果外部程式在 30 秒內沒有任何輸出，會丟出 `ProcessTimedOutException`。
    3. 使用場景：
       - 當外部程式可能長時間執行，但需要定期輸出進度或結果時使用。
    4. 回傳結果：
       - 如果指令在設定的時間內完成，返回指令的輸出結果。
       - 如果超時或閒置超時，丟出 `ProcessTimedOutException`。

       
*/
```

- *外部程式可能卡住或停止輸出*

   - 外部程式可能因某些原因`卡住`（例如 _死循環、等待資源_），導致長時間沒有任何輸出。
   - 即使程式仍在執行，主程式可能無法判斷它是否正常工作。
   
   - __idleTimeout 的作用__：
      - 防止外部程式在執行期間停止輸出，確保主程式不會長時間等待。

   - __使用場景__：
      - *防止無限等待*
         - 如果外部程式因某些原因停止輸出（例如死循環或卡住），閒置超時`可以終止程式`，避免主程式長時間等待。

---

- *外部程式的進度監控*

   - 某些外部程式（例如匯入資料或備份）可能`需要定期輸出進度`。
   - 如果程式長時間沒有輸出，可能表示程式卡住或出現問題。

   - __idleTimeout 的作用__：
      - 確保外部程式定期輸出進度，避免卡住。

   - __使用場景__：
      - *長時間執行的外部程式*
         - 如果外部程式需要`執行很長時間`（例如匯入大量資料），但需要`定期輸出進度`，使用 `閒置超時` 可以避免程式卡住。
---

### 4.4 *環境變數*

- **環境變數** 是指`在執行外部程式時，提供給程式的設定值或參數`。
- **預設情況下**，外部程式會`繼承系統的環境變數`。
- 可用 `env()` 設定，預設 __會__ 繼承系統環境變數

```php
use Symfony\Component\Process\Process;

require 'vendor/autoload.php';

// 設定環境變數並執行指令
$result = Process::forever()
    ->env(['IMPORT_PATH' => __DIR__])
    ->run('bash import.sh');
/*
    1. `env(['IMPORT_PATH' => __DIR__])`：
       - 使用 `env()` 方法設定環境變數。
       - `'IMPORT_PATH' => __DIR__`：設定名為 `IMPORT_PATH` 的環境變數，值為目前檔案所在的目錄。
    2. `run('bash import.sh')`：
       - 執行指令 `bash import.sh`，該指令可以使用設定的環境變數。
    3. 預設行為：
       - 外部程式會繼承系統的環境變數。
    4. 使用場景：
       - 當外部程式需要依賴特定的環境變數（如檔案路徑或設定值）時使用。
    5. 回傳結果：
       - 返回指令的輸出結果。
*/
```

---

#### 4.4.1 **移除繼承的環境變數**

- *移除繼承的環境變數* 用於`清除系統的預設環境變數，並提供自訂的環境`。

```php
$result = Process::forever()
    ->env(['LOAD_PATH' => false])
    ->run('bash import.sh');
/*
    1. `env(['LOAD_PATH' => false])`：
       - 使用 `env()` 方法移除名為 `LOAD_PATH` 的環境變數。
       - 設定值為 `false` 表示移除該環境變數。
    2. 使用場景：
       - 當外部程式不需要某些系統環境變數，或需要完全自訂的環境時使用。
    3. 注意事項：
       - 移除環境變數可能導致外部程式執行失敗，需確保程式的依賴環境正確。
*/
```

---

### 4.5 *TTY 模式*

- **TTY 模式** 用於`啟用互動式指令的支援`，例如  `vim`（文字編輯器）、`top`（系統監控工具）、`nano`（文字編輯器），或其他需要終端互動的程式。

- TTY 的全名是 `Teletypewriter`，源自早期的電傳打字機（Teletype），在現代計算機中，它指的是 **終端設備** 或 **虛擬終端**，用於 __與使用者進行互動__。

- **一般指令**（如 `ls、php artisan`）__`只需要`執行並返回結果__，這些指令不需要與終端互動。
- **互動式指令**（如 `vim、top`）__`需要`直接與終端交互，顯示輸入介面或動態內容__。

```php
Process::forever()->tty()->run('vim');
/*
    1. `tty()`：
       - 使用 `tty()` 方法啟用 TTY 模式。
       - TTY 模式允許外部程式與使用者進行互動，例如顯示終端輸入介面。
    2. `run('vim')`：
       - 執行指令 `vim`，啟動互動式文字編輯器。
    3. 使用場景：
       - 當需要執行互動式指令（如 `vim`、`top` 或其他需要終端輸入的程式）時使用。
    4. 注意事項：
       - TTY 模式僅適用於支援互動的指令，對於非互動式指令（如 `ls`）無效。
*/
```

---

## 5. **程序輸出**（Process Output）

### 5.1 *取得標準輸出與錯誤輸出*

```php
use Symfony\Component\Process\Process;

require 'vendor/autoload.php';

// 執行指令並取得輸出
$result = Process::run('ls -la');
echo $result->output();      // 標準輸出
echo $result->errorOutput(); // 錯誤輸出
/*
    1. `output()`：
       - 取得外部程式的標準輸出（stdout）。
       - 標準輸出通常包含指令的執行結果，例如檔案列表。
    2. `errorOutput()`：
       - 取得外部程式的錯誤輸出（stderr）。
       - 錯誤輸出通常包含指令的錯誤訊息，例如檔案不存在或權限不足。
    3. 使用場景：
       - 當需要分別處理指令的執行結果和錯誤訊息時使用。
*/
```

---

### 5.2 *即時輸出（real-time output）*

- **即時輸出** 用於`在指令執行期間，立即顯示外部程式的輸出`。
- 傳入 **closure** 於 `run` **第二參數**，取得即時輸出

```php
$result = Process::run('ls -la', function (string $type, string $output) {
    echo $output; // $type: 'stdout' 或 'stderr'
});
/*
    1. `run('ls -la', closure)`：
       - 第二個參數是 closure，用於處理即時輸出。
       - `$type`：輸出的類型，可能是 `'stdout'`（標準輸出）或 `'stderr'`（錯誤輸出）。
       - `$output`：即時輸出的內容。
    2. 使用場景：
       - 當需要即時顯示指令的執行結果或錯誤訊息時使用。
    3. 注意事項：
       - 即時輸出適合長時間執行的指令，例如匯入或備份操作。
*/
```

---

### 5.3 *輸出斷言*

- **輸出斷言** 用於`檢查指令的輸出是否包含特定字串`。
- 檢查輸出`是否`包含特定字串

```php
if (Process::run('ls -la')->seeInOutput('laravel')) {
    // ...
}
/*
    1. `seeInOutput('laravel')`：
       - 檢查指令的標準輸出是否包含字串 `'laravel'`。
       - 如果包含，返回 `true`；否則返回 `false`。
    2. 使用場景：
       - 當需要根據指令的輸出內容執行特定邏輯時使用。
    3. 注意事項：
       - 適合用於檢查執行結果是否符合預期，例如檢查檔案是否存在。
*/
```

---

### 5.4 *關閉輸出（節省記憶體）*

- **關閉輸出** 用於`避免儲存指令的輸出`，節省記憶體。
- 若不需輸出，可用 `quietly` 節省記憶體

```php
$result = Process::quietly()->run('bash import.sh');
/*
    1. `quietly()`：
       - 使用 `quietly()` 方法關閉輸出。
       - 指令的執行結果和錯誤訊息不會被儲存。
    2. 使用場景：
       - 當指令的輸出不重要，且需要節省記憶體時使用。
    3. 注意事項：
       - 關閉輸出後，無法取得執行結果或錯誤訊息。
*/
```

---

## 6. **管線**（Pipelines）

### 6.1 *多程序串接（pipe）*

- **管線**用於 `將一個程序的輸出作為下一個程序的輸入，實現多程序串接`。

```php
use Illuminate\Process\Pipe;
use Illuminate\Support\Facades\Process;

$result = Process::pipe(function (Pipe $pipe) {
    $pipe->command('cat example.txt'); // 第一個指令：讀取檔案內容
    $pipe->command('grep -i "laravel"'); // 第二個指令：搜尋檔案中包含 "laravel" 的行
});
/*
    1. `Process::pipe()`：
       - 使用管線模式，將多個指令串接起來。
       - 第一個指令的輸出會作為第二個指令的輸入。
    2. `$pipe->command()`：
       - 定義管線中的指令。
       - 第一個指令 `cat example.txt`：讀取檔案內容。
       - 第二個指令 `grep -i "laravel"`：搜尋檔案中包含 "laravel" 的行。
    3. 使用場景：
       - 當需要多個指令串接執行時使用，例如處理檔案或資料流。
    4. 回傳結果：
       - 返回管線的執行結果。
*/

if ($result->successful()) {
    // 檢查管線是否執行成功
    echo $result->output();
}
```

---

### 6.2 *傳入指令陣列*

- **指令陣列** 用於`簡化管線的定義，直接傳入多個指令`。

```php
$result = Process::pipe([
    'cat example.txt', // 第一個指令：讀取檔案內容
    'grep -i "laravel"', // 第二個指令：搜尋檔案中包含 "laravel" 的行
]);
/*
    1. `Process::pipe([])`：
       - 傳入指令陣列，定義管線中的指令。
       - 第一個指令 `cat example.txt`：讀取檔案內容。
       - 第二個指令 `grep -i "laravel"`：搜尋檔案中包含 "laravel" 的行。
    2. 使用場景：
       - 當管線的指令較簡單且不需要即時輸出時使用。
    3. 回傳結果：
       - 返回管線的執行結果。
*/
```

---

### 6.3 *即時輸出*

- **即時輸出** 用於`在管線執行期間，立即顯示每個指令的輸出`。

```php
$result = Process::pipe(function (Pipe $pipe) {
    $pipe->command('cat example.txt'); // 第一個指令：讀取檔案內容
    $pipe->command('grep -i "laravel"'); // 第二個指令：搜尋檔案中包含 "laravel" 的行
}, function (string $type, string $output) {
    echo $output; // 即時顯示輸出
});
/*
    1. `Process::pipe()`：
       - 使用管線模式，定義多個指令。
    2. 第二個參數（closure）：
       - 用於處理即時輸出。
       - `$type`：輸出的類型，可能是 `'stdout'`（標準輸出）或 `'stderr'`（錯誤輸出）。
       - `$output`：即時輸出的內容。
    3. 使用場景：
       - 當需要即時顯示管線的執行結果或錯誤訊息時使用。
    4. 回傳結果：
       - 返回管線的執行結果。
*/
```

---

### 6.4 *指定 key*

- **指定 key** 用於`為管線中的指令命名，方便在即時輸出中辨識每個指令`。

```php
$result = Process::pipe(function (Pipe $pipe) {
    $pipe->as('first')->command('cat example.txt'); // 第一個指令：讀取檔案內容，命名為 "first"
    $pipe->as('second')->command('grep -i "laravel"'); // 第二個指令：搜尋檔案中包含 "laravel" 的行，命名為 "second"
})->start(function (string $type, string $output, string $key) {
    echo "[$key] $output"; // 即時顯示輸出，並附加指令的 key
});
/*
    1. `$pipe->as('key')->command()`：
       - 使用 `as()` 方法為指令命名。
       - 第一個指令 `cat example.txt`：命名為 "first"。
       - 第二個指令 `grep -i "laravel"`：命名為 "second"。
    2. `start()`：
       - 開始執行管線，並處理即時輸出。
       - `$key`：指令的命名，用於辨識輸出的來源。
    3. 使用場景：
       - 當管線中包含多個指令，且需要辨識每個指令的輸出時使用。
    4. 回傳結果：
       - 返回管線的執行結果。
*/
```

---

## 7. **非同步程序**（Asynchronous Processes）

### 7.1 *啟動非同步程序*

- **非同步程序** `允許外部程式在背景執行，主程式可以繼續執行其他邏輯`。

```php
use Symfony\Component\Process\Process;

require 'vendor/autoload.php';

// 啟動非同步程序
$process = Process::timeout(120)->start('bash import.sh');
/*
    1. `Process::timeout(120)`：
       - 設定外部程式的最大執行時間為 120 秒。
       - 如果外部程式執行超過 120 秒，會丟出 `ProcessTimedOutException`。
    2. `start('bash import.sh')`：
       - 啟動外部程式 `bash import.sh`，以非同步方式執行。
       - 主程式不會被阻塞，可以繼續執行其他邏輯。
    3. 使用場景：
       - 當外部程式需要長時間執行，且主程式需要處理其他工作時使用。
*/

while ($process->running()) {
    // 檢查程序是否仍在執行
    echo "Process is running...\n";
    sleep(1); // 每秒檢查一次
}
/*
    1. `$process->running()`：
       - 檢查外部程式是否仍在執行。
       - 返回 `true` 表示程序正在執行，返回 `false` 表示程序已完成。
    2. 使用場景：
       - 當需要監控外部程式的執行狀態時使用。
    3. 注意事項：
       - 可以在迴圈中執行其他邏輯，例如顯示進度或記錄日誌。
*/

$result = $process->wait(); // 等待結束並取得結果
/*
    1. `$process->wait()`：
       - 等待外部程式執行完成，並返回執行結果。
       - 如果程序執行成功，返回標準輸出（stdout）。
       - 如果程序執行失敗，丟出 `ProcessFailedException`。
    2. 使用場景：
       - 當主程式需要依賴外部程式的執行結果時使用。
*/
```

---

## 8. **程序 ID 與訊號**（Process IDs and Signals）

### 8.1 *取得程序 ID*

- **程序 ID（PID）** 是`外部程式執行時的唯一識別碼，用於管理或操作該程序`。

```php
use Symfony\Component\Process\Process;

require 'vendor/autoload.php';

// 啟動外部程式
$process = Process::start('bash import.sh');
/*
    1. `Process::start()`：
       - 啟動外部程式 `bash import.sh`，以非同步方式執行。
       - 返回一個 `Process` 實例，用於管理該程序。
*/

return $process->id();
/*
    1. `$process->id()`：
       - 取得外部程式的程序 ID（PID）。
       - PID 是系統分配給每個執行中的程序的唯一識別碼。
    2. 使用場景：
       - 當需要管理或操作特定程序（例如傳送訊號或終止程序）時使用。
    3. 注意事項：
       - PID 只能在程序啟動後取得，且程序必須正在執行。
*/
```

---

### 8.2 *傳送訊號*

- **訊號** 是用於`與外部程式溝通的工具，可以用來控制程序的行為`（例如暫停、繼續或終止）。

```php
$process->signal(SIGUSR2);
/*
    1. `$process->signal(SIGUSR2)`：
       - 使用 `signal()` 方法向外部程式傳送訊號。
       - `SIGUSR2` 是 PHP 提供的訊號常數，表示使用者定義的訊號。
    2. 使用場景：
       - 當需要控制外部程式的行為（例如暫停、繼續或終止）時使用。
    3. 訊號常數：
       - PHP 提供多種訊號常數，常見的有：
         - `SIGTERM`：請求程序正常終止。
         - `SIGKILL`：強制終止程序（不可捕捉）。
         - `SIGSTOP`：暫停程序。
         - `SIGCONT`：繼續執行暫停的程序。
         - `SIGUSR1` / `SIGUSR2`：使用者定義的訊號。
    4. 注意事項：
       - 傳送訊號的程序必須有權限操作目標程序。
       - 某些訊號可能需要外部程式明確支援才能生效。
*/
```

---

## 9. **非同步程序輸出**（Asynchronous Process Output）

### 9.1 *取得最新輸出*

- **最新輸出** 用於`在非同步程序執行期間，取得自上次呼叫後的標準輸出（stdout）和錯誤輸出（stderr）`。

```php
use Symfony\Component\Process\Process;

require 'vendor/autoload.php';

// 啟動非同步程序
$process = Process::timeout(120)->start('bash import.sh');

while ($process->running()) {
    echo $process->latestOutput();      // 取得自上次呼叫後的 stdout
    echo $process->latestErrorOutput(); // 取得自上次呼叫後的 stderr
    sleep(1); // 每秒檢查一次
}
/*
    1. `$process->latestOutput()`：
       - 取得自上次呼叫後的標準輸出（stdout）。
       - 適合用於監控程序的最新執行結果。
    2. `$process->latestErrorOutput()`：
       - 取得自上次呼叫後的錯誤輸出（stderr）。
       - 適合用於監控程序的最新錯誤訊息。
    3. 使用場景：
       - 當需要即時監控程序的執行結果或錯誤訊息時使用。
    4. 注意事項：
       - 每次呼叫後，輸出會被清空，僅返回最新的內容。
*/
```

---

### 9.2 *即時輸出*

- **即時輸出** 用於`在非同步程序執行期間，立即顯示每次輸出的內容`。

```php
$process = Process::start('bash import.sh', function (string $type, string $output) {
    echo $output; // 即時顯示輸出
});

$result = $process->wait();
/*
    1. `Process::start()`：
       - 啟動外部程式 `bash import.sh`，以非同步方式執行。
       - 第二個參數是 closure，用於處理即時輸出。
    2. `$type`：
       - 輸出的類型，可能是 `'stdout'`（標準輸出）或 `'stderr'`（錯誤輸出）。
    3. `$output`：
       - 即時輸出的內容。
    4. 使用場景：
       - 當需要即時顯示程序的執行結果或錯誤訊息時使用。
    5. 注意事項：
       - 即時輸出適合長時間執行的程序，例如匯入或備份操作。
*/
```

---

### 9.3 *條件等待*

- **條件等待** 用於`等待程序的輸出符合特定條件後，再繼續執行`。

```php
$process = Process::start('bash import.sh');

$process->waitUntil(function (string $type, string $output) {
    return $output === 'Ready...'; // 當輸出符合條件時結束等待
});
/*
    1. `$process->waitUntil()`：
       - 等待程序的輸出符合特定條件後再繼續執行。
       - 第二個參數是 closure，用於定義條件。
    2. `$type`：
       - 輸出的類型，可能是 `'stdout'`（標準輸出）或 `'stderr'`（錯誤輸出）。
    3. `$output`：
       - 即時輸出的內容。
    4. 使用場景：
       - 當需要根據程序的輸出內容決定是否繼續執行時使用。
    5. 注意事項：
       - 如果程序的輸出永遠不符合條件，可能導致程式卡住。
*/
```

---

### 9.4 *確認未超時*

- **確認未超時** 用於`在非同步程序執行期間，檢查程序是否已超時`。

```php
$process = Process::timeout(120)->start('bash import.sh');

while ($process->running()) {
    $process->ensureNotTimedOut(); // 確認程序未超時
    // ...
    sleep(1);
}
/*
    1. `$process->ensureNotTimedOut()`：
       - 檢查程序是否已超過設定的執行時間（timeout）。
       - 如果程序已超時，丟出 `ProcessTimedOutException`。
    2. 使用場景：
       - 當需要定期檢查程序的執行狀態，並確保未超時時使用。
    3. 注意事項：
       - 如果程序已超時，需捕捉例外並進行錯誤處理。
*/
```

---

## 10. **並行程序池**（Concurrent Processes）

### 10.1 *建立程序池*

- **程序池** `允許同時執行多個外部程式，並行處理任務以提高效率`。

```php
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Process;

$pool = Process::pool(function (Pool $pool) {
    $pool->path(__DIR__)->command('bash import-1.sh'); // 第一個程序
    $pool->path(__DIR__)->command('bash import-2.sh'); // 第二個程序
    $pool->path(__DIR__)->command('bash import-3.sh'); // 第三個程序
})->start(function (string $type, string $output, int $key) {
    echo "[$key] $output"; // 即時顯示每個程序的輸出
});
/*
    1. `Process::pool()`：
       - 建立程序池，允許同時執行多個外部程式。
       - 使用 closure 定義程序池中的指令。
    2. `$pool->path(__DIR__)->command()`：
       - 定義程序池中的指令及其工作目錄。
       - 第一個指令 `bash import-1.sh`：執行第一個匯入程序。
       - 第二個指令 `bash import-2.sh`：執行第二個匯入程序。
       - 第三個指令 `bash import-3.sh`：執行第三個匯入程序。
    3. `start()`：
       - 啟動程序池，並使用 closure 處理即時輸出。
       - `$type`：輸出的類型（`stdout` 或 `stderr`）。
       - `$output`：即時輸出的內容。
       - `$key`：程序的索引，用於辨識輸出的來源。
*/

while ($pool->running()->isNotEmpty()) {
    echo "Processes are running...\n";
    sleep(1); // 每秒檢查一次
}
/*
    1. `$pool->running()->isNotEmpty()`：
       - 檢查程序池中是否仍有正在執行的程序。
       - 返回 `true` 表示程序池中有程序正在執行。
    2. 使用場景：
       - 當需要監控程序池的執行狀態時使用。
*/

$results = $pool->wait();
/*
    1. `$pool->wait()`：
       - 等待程序池中的所有程序完成執行，並返回執行結果。
       - 每個程序的結果會以陣列形式返回。
    2. 使用場景：
       - 當需要取得所有程序的執行結果時使用。
*/
```

---

### 10.2 *取得結果*

- **取得結果** 用於`檢查程序池中每個程序的執行結果`。

```php
$results = $pool->wait();
echo $results[0]->output(); // 顯示第一個程序的標準輸出
/*
    1. `$pool->wait()`：
       - 等待程序池中的所有程序完成執行，並返回執行結果。
    2. `$results[0]->output()`：
       - 取得第一個程序的標準輸出（stdout）。
    3. 使用場景：
       - 當需要檢查每個程序的執行結果時使用。
*/
```

---

### 10.3 *同步啟動並取得結果*

- **同步啟動** `允許同時啟動多個程序，並直接返回執行結果`。

```php
[$first, $second, $third] = Process::concurrently(function (Pool $pool) {
    $pool->path(__DIR__)->command('ls -la');       // 第一個程序
    $pool->path(app_path())->command('ls -la');   // 第二個程序
    $pool->path(storage_path())->command('ls -la'); // 第三個程序
});
echo $first->output(); // 顯示第一個程序的標準輸出
/*
    1. `Process::concurrently()`：
       - 同時啟動多個程序，並直接返回執行結果。
       - 使用 closure 定義程序池中的指令。
    2. `$pool->path()->command()`：
       - 定義程序池中的指令及其工作目錄。
       - 第一個指令 `ls -la`：列出目前目錄的檔案。
       - 第二個指令 `ls -la`：列出 `app` 目錄的檔案。
       - 第三個指令 `ls -la`：列出 `storage` 目錄的檔案。
    3. `$first->output()`：
       - 取得第一個程序的標準輸出（stdout）。
    4. 使用場景：
       - 當需要同步啟動多個程序並直接取得結果時使用。
*/
```

---

## 11. **命名程序池**（Naming Pool Processes）

### 11.1 *指定 key 取得結果*

- **命名程序池** `允許為每個程序指定唯一的 key，方便在結果中辨識和操作特定程序`。

```php
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Process;

$pool = Process::pool(function (Pool $pool) {
    $pool->as('first')->command('bash import-1.sh');  // 第一個程序，命名為 "first"
    $pool->as('second')->command('bash import-2.sh'); // 第二個程序，命名為 "second"
    $pool->as('third')->command('bash import-3.sh');  // 第三個程序，命名為 "third"
})->start(function (string $type, string $output, string $key) {
    echo "[$key] $output"; // 即時顯示每個程序的輸出，並附加程序的 key
});
/*
    1. `Process::pool()`：
       - 建立程序池，允許同時執行多個外部程式。
       - 使用 closure 定義程序池中的指令。
    2. `$pool->as('key')->command()`：
       - 使用 `as()` 方法為程序指定唯一的 key。
       - 第一個指令 `bash import-1.sh`：命名為 "first"。
       - 第二個指令 `bash import-2.sh`：命名為 "second"。
       - 第三個指令 `bash import-3.sh`：命名為 "third"。
    3. `start()`：
       - 啟動程序池，並使用 closure 處理即時輸出。
       - `$type`：輸出的類型（`stdout` 或 `stderr`）。
       - `$output`：即時輸出的內容。
       - `$key`：程序的命名，用於辨識輸出的來源。
*/

$results = $pool->wait();
/*
    1. `$pool->wait()`：
       - 等待程序池中的所有程序完成執行，並返回執行結果。
       - 每個程序的結果會以 key-value 的形式返回。
       - Key 是程序的命名（如 "first"、"second"、"third"）。
    2. 使用場景：
       - 當需要取得特定程序的執行結果時使用。
*/

return $results['first']->output();
/*
    1. `$results['first']->output()`：
       - 取得名為 "first" 的程序的標準輸出（stdout）。
       - 使用 key 辨識程序，方便操作特定程序的結果。
    2. 使用場景：
       - 當需要檢查特定程序的執行結果時使用。
*/
```

---

## 12. **程序池 ID 與訊號**（Pool Process IDs and Signals）

### 12.1 *取得所有程序 ID*

- **程序 ID（PID）** 是`每個外部程式執行時的唯一識別碼，用於管理或操作該程序`。

```php
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Process;

$pool = Process::pool(function (Pool $pool) {
    $pool->command('bash import-1.sh');
    $pool->command('bash import-2.sh');
    $pool->command('bash import-3.sh');
});

$pool->start(); // 啟動程序池

$processIds = $pool->running()->each->id(); // 取得所有正在執行的程序的 PID
echo "Running Process IDs: " . implode(', ', $processIds) . "\n";
// Running Process IDs: 12345, 12346, 12347
/*
    1. `$pool->running()`：
       - 取得程序池中所有正在執行的程序。
       - 返回一個集合（Collection），包含所有正在執行的程序。
    2. `each->id()`：
       - 使用 `each` 方法迭代集合，並取得每個程序的 ID（PID）。
       - PID 是系統分配給每個執行中的程序的唯一識別碼。
    3. 使用場景：
       - 當需要管理或操作程序池中所有正在執行的程序時使用。
    4. 回傳結果：
       - 返回一個陣列，包含所有程序的 PID。
*/
```

---

### 12.2 *傳送訊號給所有程序*

- **訊號** 是用於`與外部程式溝通的工具，可以用來控制程序的行為`（例如暫停、繼續或終止）。

```php
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Process;

$pool = Process::pool(function (Pool $pool) {
    $pool->command('bash import-1.sh'); // 第一個程序
    $pool->command('bash import-2.sh'); // 第二個程序
    $pool->command('bash import-3.sh'); // 第三個程序
});

$pool->start(); // 啟動程序池

$pool->signal(SIGUSR2);
/*
    1. `$pool->signal(SIGUSR2)`：
       - 使用 `signal()` 方法向程序池中的所有程序傳送訊號。
       - `SIGUSR2` 是 PHP 提供的訊號常數，表示使用者定義的訊號。
    2. 使用場景：
       - 當需要控制程序池中所有程序的行為（例如暫停、繼續或終止）時使用。
    3. 訊號常數：
       - PHP 提供多種訊號常數，常見的有：
         - `SIGTERM`：請求程序正常終止。
         - `SIGKILL`：強制終止程序（不可捕捉）。
         - `SIGSTOP`：暫停程序。
         - `SIGCONT`：繼續執行暫停的程序。
         - `SIGUSR1` / `SIGUSR2`：使用者定義的訊號。
    4. 注意事項：
       - 傳送訊號的程序必須有權限操作目標程序。
       - 某些訊號可能需要外部程式明確支援才能生效。
*/
```

---

```bash
#!/bin/bash

# 捕捉 SIGUSR2 訊號
trap 'echo "Received SIGUSR2 signal"; exit 0' SIGUSR2

# 模擬長時間執行的任務
echo "Task started"
while true; do
    sleep 1
done
```

---

- 執行程式
`./signal-handler.sh`

- 輸出結果
`Task started`

---

- 1. **Shell Script 是什麼？**

   - 1.1 *定義*
      - Shell Script 是一種**用來執行 `命令列指令` 的腳本檔案**，通常用於`自動化系統操作`或`執行批次任務`。
      - 它是基於 `Shell`（例如 Bash、Zsh）的語言編寫的。

   - 1.2 *與 PHP 的區別*
      - **PHP 檔案**：
         - 用於開發 Web 應用程式或後端邏輯。
         - 通常執行在 `Web Server`（如 Nginx、Apache）或 `CLI`（命令列介面）中。

      - **Shell Script**：
         - 用於`直接與作業系統交互`，執行`系統層面`的操作（如檔案管理、程序控制）。
         - 通常執行在`終端機`中。

---

- 2. **Shell Script 的用途**

   - 2.1 *系統層面的操作*
      - Shell Script 可以`執行系統指令`，例如：
         - `檔案操作`：建立、刪除、移動檔案。
         - `程序管理`：啟動、停止、監控程序。
         - `網路操作`：檢查網路狀態、下載檔案。

   - 2.2 *自動化任務*
      - Shell Script 可以用來`自動化重複性操作`，例如：
         - `定期備份資料`。
         - `部署應用程式`。
         - `監控系統資源`。

   - 2.3 *與其他程式交互*
      - `Shell Script` 可以與**其他程式**（如 PHP、Python）或**工具**（如 curl、grep）交互，執行複雜的工作流程。

---

## 13. **測試**（Testing）

### 13.1 *假資料*（Faking Processes）

#### 13.1.1 **基本假資料**

```php
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route;

Route::get('/import', function () {
    Process::run('bash import.sh'); // 執行指令
    return 'Import complete!';
});
/*
    1. `Process::run()`：
       - 執行外部指令 `bash import.sh`。
       - 在測試中可以使用 `Process::fake()` 模擬指令的執行。
    2. 使用場景：
       - 當需要測試外部指令的執行行為時使用。
*/
```

---

#### 13.1.2 **測試時 fake**

- `Process::fake()`：
   - *模擬* 所有指令的執行，避免真正執行外部程式。

- `Process::assertRan()`：
   - 斷言指令 `bash import.sh` *是否* 被執行。

```php
// 測試範例
use Illuminate\Support\Facades\Process;

Process::fake(); // 模擬所有指令的執行

$response = $this->get('/import'); // 模擬 HTTP 請求

Process::assertRan('bash import.sh'); // 斷言指令是否執行
/*
    1. `Process::fake()`：
       - 模擬所有指令的執行，避免真正執行外部程式。
    2. `Process::assertRan()`：
       - 斷言指令 `bash import.sh` 是否被執行。
    3. 使用場景：
       - 測試指令是否正確執行，且避免真正執行外部程式。
*/
```

---

#### 13.1.3 **條件斷言**

- `Process::assertRan()`：
   - 使用 closure 定義條件，斷言指令 *是否* 符合特定條件。

```php
Process::assertRan(function (PendingProcess $process, ProcessResult $result) {
    return $process->command === 'bash import.sh' &&
           $process->timeout === 60;
});
/*
    1. `Process::assertRan()`：
       - 使用 closure 定義條件，斷言指令是否符合特定條件。
    2. `$process->command`：
       - 指令的名稱（如 `bash import.sh`）。
    3. `$process->timeout`：
       - 指令的超時設定。
    4. 使用場景：
       - 當需要檢查指令的執行行為是否符合特定條件時使用。
*/
```

---

#### 13.1.4 **自訂假結果**

- `Process::result()`：
   - 定義指令的 *執行結果* ，包括`標準輸出`（stdout）、`錯誤輸出`（stderr）和`退出碼`（exit code）。

```php
Process::fake([
    '*' => Process::result( // 這表示所有指令都會回傳你設定的模擬結果，* 是萬用字元，代表任何指令。
        output: 'Test output',
        errorOutput: 'Test error output',
        exitCode: 1,
    ),
]);
/*
    1. `Process::fake()`：
       - 使用自訂的假結果模擬指令的執行。
    2. `Process::result()`：
       - 定義指令的執行結果，包括標準輸出（stdout）、錯誤輸出（stderr）和退出碼（exit code）。
    3. 使用場景：
       - 當需要模擬指令的執行結果時使用。
*/
```

---

#### 13.1.5 **指定指令假資料**

```php
Process::fake([
    'cat *' => Process::result(
        output: 'Test "cat" output',
    ),
   //  'cat *' 指的是 指令 cat 後面可以跟任意參數或檔案名稱。這裡的 * 是一種通配符（Wildcard），表示匹配所有可能的內容
    'ls *' => Process::result(
        output: 'Test "ls" output',
    ),
]);

$response = Process::run('cat example.txt');
echo $response->output(); // 輸出：Test "cat" output

$response = Process::run('ls -la');
echo $response->output(); // 輸出：Test "ls" output

/*
    1. `Process::fake()`：
       - 用於測試環境中，模擬外部指令的執行，而不真正執行指令。
       - 可以指定特定指令的假資料，模擬其執行結果。
       - 避免在測試中執行實際的系統指令，確保測試的安全性和穩定性。

    2. `'cat *' => Process::result(output: 'Test "cat" output')`：
       - 模擬指令 `cat *` 的執行結果。
       - `Process::result()` 用於定義指令的假資料。
       - `output: 'Test "cat" output'` 表示指令的標準輸出（stdout）。
       - 當測試中執行 `cat` 指令時，會返回 `'Test "cat" output'` 作為結果。

    3. `'ls *' => Process::result(output: 'Test "ls" output')`：
       - 模擬指令 `ls *` 的執行結果。
       - `output: 'Test "ls" output'` 表示指令的標準輸出（stdout）。
       - 當測試中執行 `ls` 指令時，會返回 `'Test "ls" output'` 作為結果。

    4. 使用場景：
       - 測試外部指令的執行行為，而不真正執行指令。
       - 模擬特定指令的執行結果，測試應用程式的邏輯是否正確。
       - 適合用於需要依賴外部指令的功能測試，例如檔案操作或系統指令。

    5. 注意事項：
       - `Process::fake()` 只在測試環境中使用，避免影響實際應用程式的執行。
       - 必須確保模擬的指令和結果符合測試需求。
       - 如果指令未被模擬，Laravel 會丟出例外，提醒指令未被假資料覆蓋。

    6. 回傳結果：
       - 當測試中執行 `cat *` 或 `ls *` 指令時，會返回指定的假資料。
       - 不會真正執行指令，確保測試的安全性和可控性。
*/
```

---

#### 13.1.6 **以字串指定假資料**

```php
Process::fake([
    'cat *' => 'Test "cat" output',
    'ls *' => 'Test "ls" output',
]);
/*
    1. `Process::fake()`：
       - 使用字串直接定義指令的標準輸出（stdout）。
    2. 使用場景：
       - 當需要快速模擬指令的執行結果時使用。
*/
```

---

#### 13.1.7 **多次呼叫同指令**

- `Process::sequence()`：
   - 定義指令的 *執行結果序列* ，模擬 *多次呼叫同指令* 的行為。

```php
Process::fake([
    'ls *' => Process::sequence()
        ->push(Process::result('First invocation'))
        ->push(Process::result('Second invocation')),
]);
/*
    1. `Process::sequence()`：
       - 定義指令的執行結果序列，模擬多次呼叫同指令的行為。
    2. 使用場景：
       - 當需要模擬指令的多次執行結果時使用。
*/
```

---

#### 13.1.8 **假非同步程序**

- `Process::describe()`：
   - 定義 *非同步程序的執行行為* ，包括 _多次輸出_ 和 _退出碼_。

```php
Process::fake([
    'bash import.sh' => Process::describe()
        ->output('First line of standard output') // 定義第一行標準輸出
        ->errorOutput('First line of error output') // 定義第一行錯誤輸出
        ->output('Second line of standard output') // 定義第二行標準輸出
        ->exitCode(0) // 定義程序的退出碼為 0（表示成功）
        ->iterations(3), // 定義程序執行 3 次迭代
]);
/*
    1. `Process::describe()`：
       - 定義非同步程序的執行行為，包括多次輸出和退出碼。
    2. 使用場景：
       - 當需要模擬非同步程序的執行行為時使用。
*/
// 每次迭代的輸出
//   標準輸出（stdout）：
//      第一次迭代輸出：First line of standard output
//      第二次迭代輸出：Second line of standard output
//      第三次迭代輸出：First line of standard output（迭代會循環定義的輸出）
//   錯誤輸出（stderr）：
//      第一次迭代輸出：First line of error output
//      第二次迭代輸出：無錯誤輸出（因為只定義了一次錯誤輸出）
//      第三次迭代輸出：First line of error output（迭代會循環定義的輸出）
//   退出碼（exit code）：
//      每次迭代的退出碼為 0（表示成功）。

// 第一次迭代輸出
// 標準輸出：First line of standard output
// 錯誤輸出：First line of error output
// 退出碼：0

// 第二次迭代輸出
// 標準輸出：Second line of standard output
// 錯誤輸出：（無錯誤輸出）
// 退出碼：0

// 第三次迭代輸出
// 標準輸出：First line of standard output
// 錯誤輸出：First line of error output
// 退出碼：0
```

---

### 13.2 *斷言方法（Assertions）*

#### 13.2.1 **assertRan**

- `是否`被執行，並檢查執行行為是否符合特定條件。

```php
Process::assertRan('ls -la');
Process::assertRan(fn ($process, $result) =>
    $process->command === 'ls -la' &&
    $process->path === __DIR__ &&
    $process->timeout === 60
);
/*
    1. `Process::assertRan()`：
       - 斷言指令是否被執行，並檢查執行行為是否符合特定條件。
*/
```

---

#### 13.2.2 **assertDidntRun**

- 斷言指令`是否未`被執行。

```php
Process::assertDidntRun('ls -la');
Process::assertDidntRun(fn (PendingProcess $process, ProcessResult $result) =>
    $process->command === 'ls -la'
);
/*
    1. `Process::assertDidntRun()`：
       - 斷言指令是否未被執行。
*/
```

---

#### 13.2.3 **assertRanTimes**

- 被執行的`次數`是否符合預期。

```php
Process::assertRanTimes('ls -la', times: 3);
Process::assertRanTimes(function (PendingProcess $process, ProcessResult $result) {
    return $process->command === 'ls -la';
}, times: 3);
/*
    1. `Process::assertRanTimes()`：
       - 斷言指令被執行的次數是否符合預期。
*/
```

---

#### 13.2.4 **防止未假資料的程序**

- `Process::preventStrayProcesses()`：
   - *防止* __未定義假資料的指令被執行__。
   - 如果指令未被模擬，會丟出例外。

```php
Process::preventStrayProcesses();
Process::fake([
    'ls *' => 'Test output...',
]);
Process::run('ls -la'); // 回傳假資料
Process::run('bash import.sh'); // 未假資料會丟例外
/*
    1. `Process::preventStrayProcesses()`：
       - 防止未定義假資料的指令被執行。
       - 如果指令未被模擬，會丟出例外。
    2. 使用場景：
       - 確保所有指令都被模擬，避免真正執行外部程式。
*/
```

---