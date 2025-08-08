# *Laravel Notifications 筆記*

---

## 1. **簡介**（Introduction）

- Laravel 除了支援 Email，還可發送通知到多種渠道（如 `Email、SMS、Slack、Broadcast、Database` 等）。
- 可`同時`發送到多個渠道，也可`儲存於資料庫`供前端顯示。
- 通知適合用於「`短訊息、即時提醒`」用途，例如：帳單已付通知、系統警告等。

---

## 2. **產生通知類別**（Generating Notifications）

### 2.1 *Artisan 指令產生通知*

- 通知類別預設放在 `app/Notifications` 目錄。
- 若目錄不存在，執行指令時會自動建立。

```bash
php artisan make:notification InvoicePaid
```

- 產生後會有一個包含 `via` 方法與多個訊息建構方法（如 `toMail`、`toDatabase`）的類別。

---

## 3. **發送通知**（Sending Notifications）

### 3.1 *使用 Notifiable Trait*

- `App\Models\User` **預設**已 `use Notifiable`，可用 `$user->notify()` 發送通知。
- 任何 Model 皆可 `use Notifiable`。

```php
use App\Notifications\InvoicePaid;

$user->notify(new InvoicePaid($invoice));
```

---

### 3.2 *使用 Notification Facade*

- 適合`一次發送給多個 notifiable 實體`。

```php
use Illuminate\Support\Facades\Notification;

// 使用 Notification Facade 發送通知
Notification::send($users, new InvoicePaid($invoice)); 
/*
    1. `Notification::send()`：
       - 用於發送通知給多個 notifiable 實體（如使用者）。
       - `$users` 是一個集合（Collection），包含所有需要接收通知的實體。
       - `new InvoicePaid($invoice)` 是通知的內容，通常是通知類的實例。
       - 發送通知的行為可能是延遲的（根據通知的配置）。
    2. 使用場景：
       - 當需要通知多個使用者（如多個客戶收到付款通知）時使用。
*/

Notification::sendNow($developers, new DeploymentCompleted($deployment));
/*
    1. `Notification::sendNow()`：
       - 用於立即發送通知給多個 notifiable 實體（如開發者）。
       - `$developers` 是一個集合（Collection），包含所有需要接收通知的實體。
       - `new DeploymentCompleted($deployment)` 是通知的內容，通常是通知類的實例。
       - 與 `send()` 不同，`sendNow()` 強制立即發送通知，而不考慮延遲配置。
    2. 使用場景：
       - 當需要立即通知多個使用者（如開發者收到部署完成通知）時使用。
*/
```

---

## 4. **指定發送渠道**（Specifying Delivery Channels）

- 每個通知類別都有 `via` 方法，決定要發送到哪些渠道（如 mail、database、broadcast、vonage、slack）。
- 可根據 `$notifiable` 動態決定渠道。

```php
public function via(object $notifiable): array
{
    // 根據 $notifiable 的屬性動態決定發送渠道
    return $notifiable->prefers_sms ? ['vonage'] : ['mail', 'database'];
}
/*
    1. `via(object $notifiable): array`：
       - 定義通知的發送渠道。
       - `$notifiable` 是接收通知的實體（例如使用者模型）。
       - 返回一個陣列，指定通知的發送渠道。

    2. `$notifiable->prefers_sms`：
       - 根據 `$notifiable` 的屬性（例如 `prefers_sms`）動態決定發送渠道。
       - 如果 `$notifiable->prefers_sms` 為 `true`，則使用 `vonage`（SMS）作為發送渠道。
       - 如果 `$notifiable->prefers_sms` 為 `false`，則使用 `mail` 和 `database` 作為發送渠道。

    3. 使用場景：
       - 根據使用者的偏好或屬性，動態選擇通知的發送方式。
       - 例如：
         - 使用者偏好 SMS 通知時，使用 `vonage`。
         - 使用者偏好 Email 通知時，使用 `mail`。
         - 同時記錄通知到資料庫，使用 `database`。

    4. 注意事項：
       - `via` 方法返回的渠道必須是系統支持的通知渠道（如 `mail`、`database` 等）。
       - 每個渠道需要在通知類中定義具體的發送邏輯（例如 `toMail`、`toDatabase`）。
*/
```

---

## 5. **通知佇列與延遲**（Queueing & Delaying Notifications）

### 5.1 *讓通知進入佇列*

- 實作 `ShouldQueue` 並 `use Queueable trait`。
- `make:notification` 產生的類別已**自動** import 相關 `interface/trait`。

```php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;
    // ...
}
```

- 發送時**自動進入佇列**：
```php
$user->notify(new InvoicePaid($invoice));
```

---

### 5.2 *延遲發送*

- 可用 `delay()` 方法延遲發送：

```php
$delay = now()->addMinutes(10);
$user->notify((new InvoicePaid($invoice))->delay($delay));
```

- 針對`不同渠道`延遲：
```php
$user->notify((new InvoicePaid($invoice))->delay([
    'mail' => now()->addMinutes(5),
    'sms' => now()->addMinutes(10),
]));
```

- 也可在 **通知類別內定義** `withDelay` 方法：
```php
public function withDelay(object $notifiable): array
{
    return [
        'mail' => now()->addMinutes(5),
        'sms' => now()->addMinutes(10),
    ];
}
```

---

### 5.3 *自訂佇列連線與 queue 名稱*

- 在建構子呼叫 `onConnection()` 指定連線：

```php
public function __construct()
{
    $this->onConnection('redis');
}
```

- 針對`不同渠道`指定連線：

```php
public function viaConnections(): array
{
    return [
        'mail' => 'redis',
        'database' => 'sync',
    ];
}
```

- 針對不同渠道指定`queue 名稱`：

```php
public function viaQueues(): array
{
    return [
        'mail' => 'mail-queue',
        'slack' => 'slack-queue',
    ];
}
```

---

### 5.4 *Queued Notification Middleware*

- 可定義 `middleware` 方法，根據 `channel` 決定中介層：

```php
use Illuminate\Queue\Middleware\RateLimited;

public function middleware(object $notifiable, string $channel)
{
    // 根據通知的發送渠道（channel）設置隊列的中介層（middleware）
    return match ($channel) {
        'mail' => [new RateLimited('postmark')], // 如果渠道是 'mail'，設置 RateLimited 中介層，限制使用 'postmark' 發送速率
        'slack' => [new RateLimited('slack')],  // 如果渠道是 'slack'，設置 RateLimited 中介層，限制使用 'slack' 發送速率
        default => [], // 如果渠道不是 'mail' 或 'slack'，默認不設置任何中介層
    };
}
```

---

### 5.5 *與資料庫交易共用時的 afterCommit*

- 若 queue 連線 `after_commit` 設為 **false，可用** `afterCommit()` 確保`通知在交易提交後才發送`：

```php
$user->notify((new InvoicePaid($invoice))->afterCommit());
```

- 也可在建構子呼叫：

```php
public function __construct()
{
    $this->afterCommit();
}
```

---

### 5.6 *shouldSend 條件判斷*

- 可定義 `shouldSend` 方法，決定`是否`真的要發送：

```php
public function shouldSend(object $notifiable, string $channel): bool
{
    return $this->invoice->isPaid();
}
```

---

## 6. **On-Demand 通知**（On-Demand Notifications）

- 可直接指定 email、sms、slack 等 `ad-hoc` 路由，不需是 `User model`。
- ad-hoc 是拉丁文，意思是「專門的」或「臨時的」。在技術語境中，ad-hoc 路由 指的是 `臨時指定的通知目標`，而不需要依賴特定的模型（例如 User 模型）。
- ad-hoc 路由 是指`在發送通知時，直接指定目標`（如 Email 地址、電話號碼或 Slack 頻道），而`不是通過模型`（如 User 模型）來獲取目標。
- 它是一種靈活的方式，允許你在通知發送時臨時指定目標。

```php
use Illuminate\Support\Facades\Notification;

// 使用 Notification Facade 指定發送渠道和目標
Notification::route('mail', 'taylor@example.com') // 指定 Email 發送渠道和目標地址
    ->route('vonage', '5555555555') // 指定 Vonage (SMS) 發送渠道和目標電話號碼
    ->route('slack', '#slack-channel') // 指定 Slack 發送渠道和目標頻道
    ->notify(new InvoicePaid($invoice)); // 發送通知，使用 InvoicePaid 通知類
/*
    1. `Notification::route()`：
       - 用於指定通知的發送渠道和目標。
       - 第一個參數是渠道名稱（如 `mail`、`vonage`、`slack`）。
       - 第二個參數是目標地址（如 Email、電話號碼或 Slack 頻道）。
    2. `notify()`：
       - 發送通知，使用指定的通知類（如 `InvoicePaid`）。
*/

Notification::route('mail', [
    'barrett@example.com' => 'Barrett Blair',
])->notify(new InvoicePaid($invoice));
/*
    1. `Notification::route()`：
       - 指定 Email 發送渠道，並提供目標地址和名稱（如 `barrett@example.com` 和 `Barrett Blair`）。
    2. 使用場景：
       - 當需要發送通知到特定的 Email 地址並附加名稱時使用。
*/

Notification::routes([
    'mail' => ['barrett@example.com' => 'Barrett Blair'], // 指定 Email 發送渠道和目標地址
    'vonage' => '5555555555', // 指定 Vonage (SMS) 發送渠道和目標電話號碼
])->notify(new InvoicePaid($invoice));
/*
    1. `Notification::routes()`：
       - 用於一次性指定多個通知渠道和目標。
       - 接受一個陣列，陣列的鍵是渠道名稱（如 `mail`、`vonage`），值是目標地址。
    2. 使用場景：
       - 當需要同時指定多個通知渠道和目標時使用。
*/
```

---

## 7. **Mail 通知**（Mail Notifications）

### 7.1 *toMail 方法*
- 定義 `toMail` 方法，回傳 `MailMessage` 實例。
- 可用方法 
        `greeting`：設定郵件的**問候語**。
        `line`：新增郵件的**內容行**。
        `action`：新增**帶有按鈕的行**，通常用於行動連結。
        `lineIf`：根據**條件**新增郵件的內容行。
        `error`：設定**錯誤樣式**的郵件訊息。
        `subject`：設定郵件的**主題**。
        `from`：設定郵件的**寄件人**。
        `mailer`：指定使用的**郵件寄送器**。
        `view`：使用自訂的**Blade 模板**作為郵件內容。
        `text`：設定**純文字**郵件內容。
        `attach`：附加**單一檔案**。
        `attachMany`：附加**多個檔案**。
        `attachData`：附加**原始檔案**資料。
        `tag`：為郵件新增**標籤**。
        `metadata`：新增郵件的**自訂元資料**。
        `withSymfonyMessage`：直接操作 **Symfony** 的郵件物件。

```php
public function toMail(object $notifiable): MailMessage
{
    $url = url('/invoice/'.$this->invoice->id);
    return (new MailMessage)
            ->greeting('Hello!')
            ->line('One of your invoices has been paid!')
            ->lineIf($this->amount > 0, "Amount paid: {$this->amount}")
            ->action('View Invoice', $url)
            ->line('Thank you for using our application!');
}
```

---

### 7.2 *錯誤訊息*

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
            ->error()
            ->subject('Invoice Payment Failed')
            ->line('...');
}
```

---

### 7.3 *自訂 view 或 text*

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)->view(
        'mail.invoice.paid', ['invoice' => $this->invoice]
    );
}

public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)->view(
        ['mail.invoice.paid', 'mail.invoice.paid-text'],
        ['invoice' => $this->invoice]
    );
}

public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)->text(
        'mail.invoice.paid-text', ['invoice' => $this->invoice]
    );
}
```

---

### 7.4 *自訂寄件人、收件人、主旨、mailer*

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->from('barrett@example.com', 'Barrett Blair')
        ->subject('Notification Subject')
        ->mailer('postmark')
        ->line('...');
}
```

- `Model` 可自訂**收件人**：
```php
public function routeNotificationForMail(Notification $notification): array|string
{
    return [$this->email_address => $this->name];
}
```

---

### 7.5 *自訂模板*

- 可發佈通知模板到 `resources/views/vendor/notifications`：

```bash
php artisan vendor:publish --tag=laravel-notifications
```

---

### 7.6 *附件*

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->greeting('Hello!')
        ->attach('/path/to/file');
}

public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->attach('/path/to/file', [
            'as' => 'name.pdf',
            'mime' => 'application/pdf',
            // MIME 類型（Multipurpose Internet Mail Extensions）。
            // MIME 類型用於告訴接收端（如瀏覽器或郵件客戶端）附加檔案的格式或類型，以便正確處理或顯示檔案。
        ]);
}

public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->attachMany([
            '/path/to/forge.svg',
            '/path/to/vapor.svg' => [
                'as' => 'Logo.svg',
                'mime' => 'image/svg+xml',
            ],
        ]);
}

public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->attachData($this->pdf, 'name.pdf', [
            'mime' => 'application/pdf',
        ]);
}
```

---

### 7.7 *Tag 與 Metadata*

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->tag('upvote')
        ->metadata('comment_id', $this->comment->id);
        // 元資料（Metadata） 是附加到 Email 的額外資訊，通常用於追蹤或記錄通知的相關上下文。
        // metadata 是 Laravel 的通知系統中用於向通知的 Email 添加自訂的元資料（Metadata）。這些元資料通常不會顯示在 Email 的內容中，但可以被郵件服務提供商（如 Postmark、Mailgun）用來進行追蹤或分析。
}
```

---

### 7.8 *自訂 Symfony Message*

- 自訂 Symfony Message 是指在 Laravel 的通知系統中，`使用 Symfony 的 Email 類 來直接操作底層的郵件物件，進行更細粒度的自訂`，例如**添加自訂標頭、修改 MIME 類型、設置元資料**等。

- Laravel 的**通知系統**和**郵件系統**基於 `Symfony` 的郵件功能構建，透過 `withSymfonyMessage` 方法，開發者可以直接操作 `Symfony` 的 `Email 物件` ，進行更底層的自訂。

```php
use Symfony\Component\Mime\Email;

public function toMail(object $notifiable): MailMessage
{
    // 定義通知的 Email 內容，並使用 Symfony 的 Email 物件進行自訂
    return (new MailMessage)
        ->withSymfonyMessage(function (Email $message) {
            // 使用 Symfony 的 Email 物件添加自訂標頭
            $message->getHeaders()->addTextHeader(
                'Custom-Header', 'Header Value' // 添加名為 'Custom-Header' 的自訂標頭，值為 'Header Value'
            );
        });
}
```

---

### 7.9 *回傳 Mailable*

- **Mailable 的作用**
  - Mailable 是 Laravel 的`郵件建構器`，用於`生成 Email 的內容`。
  - 通過 `Mailable 類 `，可以定義 Email 的主題、內容、附件等。
- **使用場景**
  - 當通知的 Email 內容需`要更複雜的邏輯`（如自訂模板或附件）時，可以使用 Mailable 類。
- **注意事項**
  - *Mailable 的靈活性*：
    - `Mailable 類`提供了**更靈活的方式**來構建 Email，例如使用自訂模板、添加附件等。
  - *通知的接收者*：
    - 通過` $notifiable->email` 設定 Email 的接收者。
  - *與 MailMessage 的區別*：
    - `MailMessage` 是**通知系統的高層次 API**，適合快速構建簡單的 Email。
    - `Mailable 類` 提供**更底層**的功能，適合需要自訂模板或複雜邏輯的場景。

```php
use App\Mail\InvoicePaid as InvoicePaidMailable; // 引入自訂的 Mailable 類
use Illuminate\Mail\Mailable; // 引入 Laravel 的 Mailable 基類

public function toMail(object $notifiable): Mailable
{
    // 回傳一個 Mailable 實例，用於生成 Email 通知
    return (new InvoicePaidMailable($this->invoice)) // 使用自訂的 InvoicePaidMailable 類，並傳入通知的資料（如 $this->invoice）
        ->to($notifiable->email); // 設定 Email 的接收者，使用 $notifiable 的 email 屬性
}
```

---

### 7.10 *On-Demand 通知的收件人*

- **`AnonymousNotifiable`的作用**
  - *匿名通知類：*
    - `AnonymousNotifiable` 是 Laravel 提供的類，用於`處理 On-Demand 通知`。
    - `On-Demand 通知` **允許臨時指定通知的接收者，而不需要依賴系統中的模型**（如 User 模型）。
- **`routeNotificationFor`方法**
  - *作用*：
    - 用於**獲取通知的目標地址**（如 Email 地址）。
  - *使用場景*：
    - 當通知的**接收者**是`匿名通知類`時，使用 `routeNotificationFor` 方法**獲取目標地址**。

```php
use App\Mail\InvoicePaid as InvoicePaidMailable; // 引入自訂的 Mailable 類
use Illuminate\Notifications\AnonymousNotifiable; // 引入 Laravel 的匿名通知類
use Illuminate\Mail\Mailable; // 引入 Laravel 的 Mailable 基類

public function toMail(object $notifiable): Mailable
{
    // 判斷通知的接收者是否是匿名通知類
    $address = $notifiable instanceof AnonymousNotifiable
        ? $notifiable->routeNotificationFor('mail') // 如果是匿名通知，使用 routeNotificationFor 方法獲取 Email 地址
        : $notifiable->email; // 如果是一般的通知接收者，直接使用 email 屬性

    // 回傳一個 Mailable 實例，用於生成 Email 通知
    return (new InvoicePaidMailable($this->invoice))
        ->to($address); // 設定 Email 的接收者
}
```

---

### 7.11 *預覽通知*

- 可直接在 `route` 或 `controller` 回傳 `MailMessage` **預覽設計**：

```php
use App\Models\Invoice; // 引入 Invoice 模型，用於查詢 Invoice 資料
use App\Notifications\InvoicePaid; // 引入 InvoicePaid 通知類，用於生成通知內容

Route::get('/notification', function () {
    // 從資料庫中查找 ID 為 1 的 Invoice
    $invoice = Invoice::find(1); // 查詢 Invoice 模型，模擬通知的資料來源

    // 使用 InvoicePaid 通知類，並生成 Email 通知內容
    return (new InvoicePaid($invoice)) // 建立 InvoicePaid 通知實例，並傳入 Invoice 資料
        ->toMail($invoice->user); // 調用 toMail 方法，生成 Email 通知內容，並傳入 Invoice 的關聯使用者
    /*
        1. InvoicePaid 通知類的 toMail 方法會生成 MailMessage 實例。
        2. MailMessage 包含 Email 的主題、內容行、按鈕等設計。
        3. Laravel 會將 MailMessage 渲染成 HTML，並直接顯示在瀏覽器中。
        4. 此程式碼僅用於預覽通知的設計，不會實際發送 Email。
    */
});
```

```php
// 值接寄送通知，非預覽
use App\Models\Invoice; // 引入 Invoice 模型，用於查詢 Invoice 資料
use App\Notifications\InvoicePaid; // 引入 InvoicePaid 通知類，用於生成通知內容
use Illuminate\Support\Facades\Notification; // 引入 Laravel 的通知 Facade

Route::get('/notification/send', function () {
    // 從資料庫中查找 ID 為 1 的 Invoice
    $invoice = Invoice::find(1); // 查詢 Invoice 模型，模擬通知的資料來源

    // 使用 Laravel 的通知系統發送通知
    Notification::send($invoice->user, new InvoicePaid($invoice)); // 發送通知給 Invoice 的關聯使用者
    /*
        1. Notification::send() 方法用於發送通知。
        2. 第一個參數是通知的接收者（如 Invoice 的關聯使用者）。
        3. 第二個參數是通知類的實例（如 InvoicePaid）。
        4. Laravel 會根據通知類中的 via 方法決定通知的發送渠道（如 Email）。
    */

    return 'Notification sent successfully!';
});
```

---

## 8. **Markdown 郵件通知**（Markdown Mail Notifications）

### 8.1 *產生 Markdown 通知*

- 使用 `--markdown` 參數產生通知與**對應 Markdown 模板**：

```bash
php artisan make:notification InvoicePaid --markdown=mail.invoice.paid
```

- 在通知類別的 `toMail` 方法中，使用 `markdown()` 指定模板與資料：
```php
public function toMail(object $notifiable): MailMessage
{
    $url = url('/invoice/'.$this->invoice->id);
    return (new MailMessage)
        ->subject('Invoice Paid')
        ->markdown('mail.invoice.paid', ['url' => $url]);
}
```

---

### 8.2 *撰寫 Markdown 模板*

- Markdown 通知模板結合 Blade component 與 Markdown 語法：

```php
<x-mail::message>
# Invoice Paid

Your invoice has been paid!

<x-mail::button :url="$url">
View Invoice
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
```
- 注意：Markdown 標準下，縮排會被視為 `code block` ，請勿多餘縮排。

---

### 8.3 *常用元件*

- **Button**：
```php
<x-mail::button :url="$url" color="green">
View Invoice
</x-mail::button>
```
- **Panel**：
```php
<x-mail::panel>
這是面板內容。
</x-mail::panel>
```
- **Table**：
```php
<x-mail::table>
| Laravel       | Table         | Example       |
| ------------- | :-----------: | ------------: |
| Col 2 is      | Centered      | $10           |
| Col 3 is      | Right-Aligned | $20           |
</x-mail::table>
```

---

### 8.4 *自訂元件與 CSS*

- 發佈元件到專案：
```bash
php artisan vendor:publish --tag=laravel-mail
```

- **新增主題**：於 `html/themes` 目錄新增 CSS，並於 `config/mail.php` 設定。
- **自訂 CSS**：直接修改 `resources/views/vendor/mail/html/themes/default.css`，
               或新增自訂主題`resources/views/vendor/mail/html/themes/invoice.css`。
               
```php
// config/mail.php
'theme' => 'invoice', // 設定使用自訂的主題
```

- `單一通知`自訂主題：
```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->theme('invoice') // 使用自訂的主題
        ->subject('Invoice Paid') // 設定 Email 的主題
        ->markdown('mail.invoice.paid', ['url' => $url]); // 使用 Markdown 模板
}
```

---

## 9. **資料庫通知**（Database Notifications）

### 9.1 *前置作業*

- 建立`通知資料表`：
```bash
php artisan make:notifications-table
php artisan migrate
```
- 若使用 `UUID/ULID`，請將 `migration` 內 `morphs` 改為 **uuidMorphs/ulidMorphs**。

---

### 9.2 *格式化資料庫通知*

- 定義 `toDatabase` 或 `toArray` 方法，回傳**純陣列**：

```php
public function toArray(object $notifiable): array
{
    return [
        'invoice_id' => $this->invoice->id,
        'amount' => $this->invoice->amount,
    ];
}
```

- 自訂 `type` 與 `read_at` 欄位：
```php
public function databaseType(object $notifiable): string
{
    return 'invoice-paid';
}

public function initialDatabaseReadAtValue(): ?Carbon
{
    return null;
}
```
- `toArray` 也用於 `broadcast`，若要**分開**，請定義 `toDatabase`。

---

### 9.3 *存取通知*

- 取得**所有通知**：
```php
$user = App\Models\User::find(1);
foreach ($user->notifications as $notification) {
    echo $notification->type;
}
```
- 取得**未讀通知**：
```php
foreach ($user->unreadNotifications as $notification) {
    echo $notification->type;
}
```

- **標記為已讀**：
```php
foreach ($user->unreadNotifications as $notification) {
    $notification->markAsRead();
}
// 或批次：
$user->unreadNotifications->markAsRead();
// 或直接更新：
$user->unreadNotifications()->update(['read_at' => now()]);
```

- **刪除通知**：
```php
$user->notifications()->delete();
```

---

## 10. **Broadcast 通知**（Broadcast Notifications）

### 10.1 *前置作業*

- 需先設定 `Laravel event broadcasting`。

---

### 10.2 *格式化 Broadcast 通知*

- 定義 `toBroadcast` 方法，回傳 `BroadcastMessage` 實例：

```php
use Illuminate\Notifications\Messages\BroadcastMessage;

public function toBroadcast(object $notifiable): BroadcastMessage
{
    return new BroadcastMessage([
        'invoice_id' => $this->invoice->id,
        'amount' => $this->invoice->amount,
    ]);
}
```

- 設定 **queue 連線/名稱**：
```php
return (new BroadcastMessage($data))
    ->onConnection('sqs')
    ->onQueue('broadcasts');
```

- 自訂 **type 欄位**：
```php
public function broadcastType(): string
{
    return 'broadcast.message';
}
```

---

### 10.3 *前端監聽通知*

- `Laravel Echo` 監聽 `private channel`：

```js
Echo.private('App.Models.User.' + userId)
    .notification((notification) => {
        console.log(notification.type);
    });
```

- **React/Vue hook**：
```js
import { useEchoNotification } from "@laravel/echo-react";

useEchoNotification(
    `App.Models.User.${userId}`,
    (notification) => {
        console.log(notification.type);
    },
    'App.Notifications.InvoicePaid',
);
```

- **自訂 channel**：
```php
public function receivesBroadcastNotificationsOn(): string
{
    return 'users.'.$this->id;
}
```

---

## 11. **SMS 通知（Vonage）**（SMS Notifications）

### 11.1 *前置作業*

- 安裝套件：
```bash
composer require laravel/vonage-notification-channel guzzlehttp/guzzle
```

- 設定 `.env`：
```bash
VONAGE_KEY=xxx
VONAGE_SECRET=xxx
VONAGE_SMS_FROM=15556666666
```

---

### 11.2 *格式化 SMS 通知*

- 定義 `toVonage` 方法，回傳 `VonageMessage` 實例：

```php
use Illuminate\Notifications\Messages\VonageMessage;

public function toVonage(object $notifiable): VonageMessage
{
    return (new VonageMessage)
        ->content('Your SMS message content');
}
```
- **Unicode 內容**：
```php
public function toVonage(object $notifiable): VonageMessage
{
    return (new VonageMessage)
        ->content('Your unicode message')
        ->unicode();
}
```

- **自訂發送者**：
```php
public function toVonage(object $notifiable): VonageMessage
{
    return (new VonageMessage)
        ->content('Your SMS message content')
        ->from('15554443333');
}
```

- 加入 **client reference**：
```php
public function toVonage(object $notifiable): VonageMessage
{
    return (new VonageMessage)
        ->clientReference((string) $notifiable->id)
        ->content('Your SMS message content');
}
```

---

### 11.3 *路由 SMS 通知*

- **Model** 實作 `routeNotificationForVonage`：

```php
public function routeNotificationForVonage(Notification $notification): string
{
    return $this->phone_number;
}
```

---

## 12. **Slack 通知**（Slack Notifications）

### 12.1 *前置作業*

- 安裝套件：
```bash
composer require laravel/slack-notification-channel
```

- 建立 `Slack App`，並設定 **chat:write、chat:write.public、chat:write.customize** 權限。

- 取得 **Bot User OAuth Token**，設定於 `config/services.php`：
```php
'slack' => [
    'notifications' => [
        'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
        'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
    ],
],
```
- 若需發送到**外部 workspace**，需進行 `App Distribution`，並可用 `Socialite` 取得 token。

---

### 12.2 *格式化 Slack 通知*

- 定義 `toSlack` 方法，回傳 `SlackMessage` 實例，可用 `Block Kit API` 建構豐富訊息：

```php
use Illuminate\Notifications\Slack\BlockKit\Blocks\ContextBlock; // 引入 Slack BlockKit 的 ContextBlock，用於顯示上下文資訊
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock; // 引入 Slack BlockKit 的 SectionBlock，用於顯示主要內容
use Illuminate\Notifications\Slack\SlackMessage; // 引入 SlackMessage，用於構建 Slack 通知

public function toSlack(object $notifiable): SlackMessage
{
    return (new SlackMessage) // 建立 SlackMessage 實例，用於構建 Slack 通知
        ->text('One of your invoices has been paid!') // 設定通知的主要文字內容，顯示在 Slack 的訊息中
        ->headerBlock('Invoice Paid') // 添加標題區塊，顯示 "Invoice Paid" 作為通知的標題
        ->contextBlock(function (ContextBlock $block) { // 添加上下文區塊，用於顯示額外的資訊
            $block->text('Customer #1234'); // 在上下文區塊中顯示 "Customer #1234"
        })
        ->sectionBlock(function (SectionBlock $block) { // 添加主要內容區塊，用於顯示通知的詳細資訊
            $block->text('An invoice has been paid.'); // 在主要內容區塊中顯示 "An invoice has been paid."
            $block->field("*Invoice No:*\n1000")->markdown(); // 添加欄位，顯示發票號碼，並啟用 Markdown 格式
            $block->field("*Invoice Recipient:*\ntaylor@laravel.com")->markdown(); // 添加欄位，顯示收件人 Email，並啟用 Markdown 格式
        })
        ->dividerBlock() // 添加分隔線區塊，用於分隔內容
        ->sectionBlock(function (SectionBlock $block) { // 添加另一個主要內容區塊，用於顯示結尾訊息
            $block->text('Congratulations!'); // 在主要內容區塊中顯示 "Congratulations!"
        });
}
```

- 也可直接用 `Block Kit Builder` 產生的 JSON：
```php
public function toSlack(object $notifiable): SlackMessage
{
    $template = <<<JSON
        {"blocks": [
            {"type": "header", "text": {"type": "plain_text", "text": "Team Announcement"}},
            {"type": "section", "text": {"type": "plain_text", "text": "We are hiring!"}}
        ]}
    JSON;
    return (new SlackMessage)
        ->usingBlockKitTemplate($template);
}
```

---

### 12. *互動與確認*

- 啟用 `Slack App Interactivity`，設定 `Request URL`。

- 使用 **actionsBlock、button、confirm** 等元件：
```php
use Illuminate\Notifications\Slack\BlockKit\Blocks\ActionsBlock; // 引入 Slack BlockKit 的 ActionsBlock，用於添加互動按鈕
use Illuminate\Notifications\Slack\BlockKit\Composites\ConfirmObject; // 引入 ConfirmObject，用於設置按鈕的確認對話框

public function toSlack(object $notifiable): SlackMessage
{
    return (new SlackMessage) // 建立 SlackMessage 實例，用於構建 Slack 通知
        ->actionsBlock(function (ActionsBlock $block) { // 添加 ActionsBlock，用於顯示互動按鈕
            $block->button('Acknowledge Invoice') // 添加按鈕，標籤為 "Acknowledge Invoice"
                ->primary() // 設置按鈕為主要樣式（通常是藍色）
                ->confirm( // 為按鈕設置確認對話框
                    'Acknowledge the payment and send a thank you email?', // 確認對話框的描述文字
                    function (ConfirmObject $dialog) { // 使用 ConfirmObject 設置對話框的選項
                        $dialog->confirm('Yes'); // 設置確認按鈕的文字為 "Yes"
                        $dialog->deny('No'); // 設置取消按鈕的文字為 "No"
                    }
                );
        });
}
```

- 可用 `dd()` 快速預覽 Block Kit：
```php
return (new SlackMessage)
    ->text('One of your invoices has been paid!')
    ->headerBlock('Invoice Paid')
    ->dd();
```

---

### 12.4 *路由 Slack 通知*

- Model 實作 `routeNotificationForSlack`，可回傳：
  - `null`：用預設 channel
  - **字串**：指定 channel（如 `#support-channel`）
  - `SlackRoute::make($channel, $token)`：發送到外部 workspace

```php
use Illuminate\Notifications\Slack\BlockKit\Blocks\ActionsBlock; // 引入 ActionsBlock，用於添加互動按鈕
use Illuminate\Notifications\Slack\BlockKit\Composites\ConfirmObject; // 引入 ConfirmObject，用於設置按鈕的確認對話框

public function routeNotificationForSlack(Notification $notification): mixed // 定義 Slack 路由方法，回傳通知的目標 Slack 頻道或路由
{
    return '#support-channel'; // 回傳固定的 Slack 頻道名稱 "#support-channel"，通知將發送到該頻道
}

// 或

use Illuminate\Notifications\Slack\SlackRoute; // 引入 SlackRoute，用於構建 Slack 路由，包含頻道和 Token

public function routeNotificationForSlack(Notification $notification): mixed // 定義 Slack 路由方法，回傳通知的目標 Slack 路由
{
    return SlackRoute::make($this->slack_channel, $this->slack_token); // 使用 SlackRoute::make() 方法，指定頻道和 Token，頻道名稱來自 $this->slack_channel，Token 來自 $this->slack_token
}
```

---

## 13. **通知本地化**（Localizing Notifications）

- 可用 `locale()` 指定*通知語系*：
```php
$user->notify((new InvoicePaid($invoice))->locale('es'));
Notification::locale('es')->send($users, new InvoicePaid($invoice));
```

- 若 Model 實作 `HasLocalePreference`，會自動用 `preferredLocale：`
```php
use Illuminate\Contracts\Translation\HasLocalePreference;
class User extends Model implements HasLocalePreference
{
    public function preferredLocale(): string
    {
        return $this->locale;
    }
}
```

---

## 14. **通知測試**（Testing Notifications）

- 使用 `Notification::fake()` 可*攔截所有通知*，方便測試。

- 斷言方法：
```php
use Illuminate\Support\Facades\Notification; // 引入 Notification Facade，用於測試通知的行為

Notification::fake(); // 假設通知系統，阻止實際發送通知，並記錄通知的行為以供測試
// ...

Notification::assertNothingSent(); // 斷言沒有任何通知被發送

Notification::assertSentTo([$user], OrderShipped::class); // 斷言通知 OrderShipped 已發送給指定的使用者 $user

Notification::assertNotSentTo([$user], AnotherNotification::class); // 斷言通知 AnotherNotification 沒有發送給指定的使用者 $user

Notification::assertCount(3); // 斷言總共發送了 3 個通知

// 傳入 closure 進行更細緻斷言：
Notification::assertSentTo(
    $user, // 指定接收通知的使用者
    function (OrderShipped $notification, array $channels) use ($order) { // 使用閉包進行細緻斷言
        return $notification->order->id === $order->id; // 斷言通知的 order ID 與測試的 order ID 相符
    }
);

// 測試 on-demand 通知：
Notification::assertSentOnDemand(OrderShipped::class); // 斷言通知 OrderShipped 是以 on-demand 方式發送的

Notification::assertSentOnDemand(
    OrderShipped::class, // 指定通知類型
    function (OrderShipped $notification, array $channels, object $notifiable) use ($user) { // 使用閉包進行細緻斷言
        return $notifiable->routes['mail'] === $user->email; // 斷言通知的 mail 路由與使用者的 email 相符
    }
);
```

---

## 15. **通知事件**（Notification Events）

- *發送前* 會觸發 `NotificationSending` 事件，*發送後* 觸發 `NotificationSent` 事件。
- 可監聽事件並存取 *notifiable、notification、channel、response* 等屬性。

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    NotificationSending::class => [
        CheckNotificationStatus::class, // 註冊事件處理器
    ],
];
```

```php
use Illuminate\Notifications\Events\NotificationSending; // 引入 NotificationSending 事件

class CheckNotificationStatus
{
    public function handle(NotificationSending $event): bool // 定義事件處理器，接收 NotificationSending 事件
    {
        // $event->channel, $event->notifiable, $event->notification
        // 可使用 $event 的屬性檢查通知的相關資訊：
        // - $event->channel: 發送的渠道（如 mail、slack）
        // - $event->notifiable: 接收通知的實體（如 User 模型）
        // - $event->notification: 通知的實例

        if ($event->notification instanceof SpecificNotification) { // 檢查通知是否屬於 SpecificNotification 類型
            return false; // 如果是 SpecificNotification，阻止通知的發送
        }

        return true; // 如果不是 SpecificNotification，允許通知繼續發送
    }
}

use Illuminate\Notifications\Events\NotificationSent;
class LogNotification
{
    public function handle(NotificationSent $event): void
    {
        // $event->channel, $event->notifiable, $event->notification, $event->response
    }
}
```

---

## 16. **自訂通知渠道**（Custom Channels）

- 可`自訂通知 driver`，只需實作 `send($notifiable, $notification)` 方法。
- 在通知類別的 `via` 回傳自訂 channel 類別名稱。

```php
namespace App\Notifications; // 定義命名空間，表示這些類別屬於 App\Notifications

use Illuminate\Notifications\Notification; // 引入 Notification 類，用於構建通知

// 自訂通知渠道，用於處理語音通知的發送邏輯。
class VoiceChannel // 定義自訂的通知渠道 VoiceChannel
{
    public function send(object $notifiable, Notification $notification): void // 定義 send 方法，用於發送通知
    {
        $message = $notification->toVoice($notifiable); // 調用通知類的 toVoice 方法，生成 VoiceMessage 實例
        // 實際發送邏輯...
        // 在這裡實現將 VoiceMessage 發送到語音系統的邏輯
    }
}

// 定義發票支付通知，並指定使用語音渠道發送。
class InvoicePaid extends Notification // 定義 InvoicePaid 通知類，表示發票已支付的通知
{
    use Queueable; // 使用 Queueable Trait，允許通知使用隊列進行異步處理

    public function via(object $notifiable): string // 定義 via 方法，指定通知的發送渠道
    {
        return VoiceChannel::class; // 指定使用 VoiceChannel 作為通知的發送渠道
    }

    public function toVoice(object $notifiable): VoiceMessage // 定義 toVoice 方法，生成 VoiceMessage 實例
    {
        // ...
        // 在這裡構建 VoiceMessage 的內容，例如語音通知的文字或音頻檔案
        return new VoiceMessage('Your invoice has been paid.');
    }
}

// 發送通知
Notification::send($user, new InvoicePaid($invoice));
```

---