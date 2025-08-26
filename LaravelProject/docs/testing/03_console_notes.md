# *Laravel Console 測試 筆記*

---

## 1. **簡介**（Introduction）

Laravel 除了*簡化 HTTP 測試*，也提供 *簡單 API* 來測試自訂的 _Artisan Console 指令_。

---

## 2. **成功／失敗斷言**（Success / Failure Expectations）

### 2.1 *assertExitCode / assertNotExitCode*

可用 `artisan` 方法在測試中執行 Artisan 指令，並用 `assertExitCode` 斷言結束碼：

```php
test('console command', function () {
    $this->artisan('inspire')->assertExitCode(0); // 0 代表成功
});
```

---

斷言 *不是* 指定結束碼：

```php
$this->artisan('inspire')->assertNotExitCode(1);
```

---

### 2.2 *assertSuccessful / assertFailed*

大多數指令 __成功時結束碼為 0__，失敗時為非 0。可用下列斷言：

```php
$this->artisan('inspire')->assertSuccessful(); // 成功，斷言指令執行後結束碼為 0（代表成功）。
$this->artisan('inspire')->assertFailed(); // 失敗，斷言指令執行後結束碼為非 0（代表失敗）。
```
非０為「_程式結束碼_」（exit code），代表`指令或程式執行失敗時的狀態碼`。

- __0__：代表執行成功。
- __1、2...__：代表執行失敗，數字不同可能代表*不同錯誤類型*。


---

## 3. **輸入／輸出斷言**（Input / Output Expectations）

### 3.1 *模擬互動輸入 expectsQuestion*

可用 `expectsQuestion` 模擬**互動式問題輸入**：

```php
Artisan::command('question', function () {
    $name = $this->ask('What is your name?'); // 互動式詢問使用者姓名
    $language = $this->choice('Which language do you prefer?', [
        'PHP', 'Ruby', 'Python',
    ]); // 互動式選擇語言
    $this->line('Your name is '.$name.' and you prefer '.$language.'.'); // 輸出結果
});

test('console command', function () {
    $this->artisan('question')
        ->expectsQuestion('What is your name?', 'Taylor Otwell') // 模擬回答姓名
        ->expectsQuestion('Which language do you prefer?', 'PHP') // 模擬選擇語言
        ->expectsOutput('Your name is Taylor Otwell and you prefer PHP.') // 斷言輸出內容
        ->doesntExpectOutput('Your name is Taylor Otwell and you prefer Ruby.') // 斷言不會輸出這行
        ->assertExitCode(0); // 斷言結束碼為 0（成功）
});
```

---

### 3.2 *Prompts expectsSearch*

若指令用到 `Laravel Prompts` 的 `search/multisearch`，可用 `expectsSearch` 模擬**搜尋與選擇**：

`Laravel Prompt` 是 __Laravel 10 之後__**內建的互動式命令列工具**，
讓你在 `Artisan 指令`裡可以更方便地 _設計互動問題、選擇、搜尋_ 等，
像 `ask()`、`choice()`、`search()` 都是 Prompt 的功能，
讓指令更友善、更容易與使用者互動。

```php

test('console command', function () {
    $this->artisan('example')
        ->expectsSearch(
            'What is your name?', // 搜尋提示問題
            search: 'Tay',        // 模擬使用者輸入 Tay 進行搜尋
            answers: [            // 搜尋結果選項
                'Taylor Otwell', 'Taylor Swift', 'Darian Taylor'
            ],
            answer: 'Taylor Otwell' // 模擬選擇 Taylor Otwell 作為答案
        )
        ->assertExitCode(0); // 斷言指令執行成功
});
```

---

### 3.3 *不產生輸出 doesntExpectOutput*

可斷言指令**沒有任何輸出**：

```php
test('console command', function () {
    // 執行 'example' 指令
    $this->artisan('example')
        ->doesntExpectOutput() // 斷言指令執行時沒有任何輸出內容
        ->assertExitCode(0);   // 如果真的沒有輸出，且結束碼為 0，就代表指令執行成功
});
```

__常見場合__

- 指令 _只做_ 資料處理或背景任務，_不需要_ 顯示訊息給使用者。
- 驗證指令執行時 _不會誤印_ debug、錯誤或多餘內容。
- 確保 CI/CD、排程等自動化流程執行時，_輸出乾淨、無雜訊_。

---

### 3.4 *部分輸出 expectsOutputToContain / doesntExpectOutputToContain*

可斷言輸出**包含／不包含**某段文字：

```php
test('console command', function () {
    // 執行 'example' 指令
    $this->artisan('example')
        ->expectsOutputToContain('Taylor') // 斷言輸出內容包含 'Taylor'
        ->assertExitCode(0);               // 斷言指令執行成功（結束碼為 0）
});
```

---

## 4. **確認互動 expectsConfirmation**

指令若有 `yes/no` 確認，可用 `expectsConfirmation`：

```php
$this->artisan('module:import')
    ->expectsConfirmation('Do you really wish to run this command?', 'no') // 模擬使用者選擇 'no' 作為確認答案
    ->assertExitCode(1); // 斷言指令因拒絕執行而結束碼為 1（失敗）
```

---

## 5. **表格輸出 expectsTable**

若指令用 Artisan 的 `table` 方法輸出表格，可用 `expectsTable` 驗證：

```php
$this->artisan('users:all')
    ->expectsTable(
        ['ID', 'Email'], // 斷言輸出表格的標題欄位
        [
            [1, 'taylor@example.com'],   // 斷言表格內容包含這兩筆資料
            [2, 'abigail@example.com'],
        ]
    );
```

---

## 6. **Console 事件**（Console Events）

_預設下_，`Illuminate\Console\Events\CommandStarting` 與 `CommandFinished` 事件在*測試時* __不會被 dispatch__。
_若需啟用_，請在測試類別加上 `Illuminate\Foundation\Testing\WithConsoleEvents` trait。

```php

use Illuminate\Foundation\Testing\WithConsoleEvents;

uses(WithConsoleEvents::class);

// ...
``` 