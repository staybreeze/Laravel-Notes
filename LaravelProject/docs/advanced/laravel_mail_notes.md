# *Laravel Mail 筆記*

---

## **介紹**
Laravel Mail 提供簡潔、現代的郵件 API，底層採用 *Symfony Mailer*，支援 SMTP、Mailgun、Postmark、Resend、Amazon SES、sendmail 等多種驅動，讓你能快速整合本地或雲端郵件服務。


【名詞註解】
- *Laravel Mail*：Laravel 框架**內建**的寄送電子郵件功能。
- *API*：應用程式介面，讓你用簡單的程式語法呼叫寄信功能。
- *Symfony Mailer*：一個專門處理郵件寄送的 **PHP 函式庫**，Laravel Mail 的底層就是用它。
- *SMTP*：**最傳統的郵件傳送協定**，像 Gmail、Yahoo 都支援。
- *Mailgun*：**雲端郵件服務**，適合大量寄信與追蹤信件狀態。
- *Postmark*：另一種**雲端郵件服務**，主打高送達率與速度。
- *Resend*：新興的郵件 API 服務，主打簡單、現代化。
- *Amazon SES*：Amazon 提供的大量郵件寄送服務。
- *sendmail*：**伺服器內建的寄信程式**，常見於 Linux 主機。
- *本地郵件服務*：在**自己主機上**直接寄信（如 SMTP、sendmail）。
- *雲端郵件服務*：用**第三方服務商**幫你寄信（如 Mailgun、Postmark、Amazon SES、Resend）。

---

## **設定檔重點**

### *config/mail.php*
- `default`：預設 mailer 名稱（如 smtp、mailgun、failover...）。
- `mailers`：每個 mailer 可有獨立設定與 transport。
- 可同時設定多組 mailer，並用 failover/roundrobin 實現高可用或負載平衡。

*範例*：
```php
// config/mail.php
return [
    'default' => env('MAIL_MAILER', 'smtp'),
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        ],
        'mailgun' => [
            'transport' => 'mailgun',
        ],
        'failover' => [
            'transport' => 'failover',
            'mailers' => ['smtp', 'mailgun'],
        ],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],
];
```

### *config/services.php*
- 各雲端郵件服務（Mailgun、Postmark、SES、Resend 等）API 金鑰、domain、endpoint 設定。

*範例*：
```php
// config/services.php
return [
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
];
```

---

## **各大主流 Driver 安裝與設定**

### *1. SMTP*
- Laravel 預設支援，無需額外安裝。
- 設定 *.env*：
<!--
這是在 .env 檔案裡設定 SMTP 伺服器的連線資訊：
- MAIL_MAILER：指定使用 smtp 驅動
- MAIL_HOST/MAIL_PORT：SMTP 伺服器主機與連接埠
- MAIL_USERNAME/MAIL_PASSWORD：登入帳號密碼
- MAIL_ENCRYPTION：加密方式（通常用 tls）
- MAIL_FROM_ADDRESS/MAIL_FROM_NAME：預設寄件人信箱與名稱
-->
```php
// 這是在 .env 檔案裡設定 SMTP 伺服器的連線資訊：
MAIL_MAILER=smtp           // MAIL_MAILER：指定使用 smtp 驅動
MAIL_HOST=smtp.mailtrap.io // MAIL_HOST/MAIL_PORT：SMTP 伺服器主機與連接埠
MAIL_PORT=2525
MAIL_USERNAME=xxx          // MAIL_USERNAME/MAIL_PASSWORD：登入帳號密碼
MAIL_PASSWORD=xxx
MAIL_ENCRYPTION=tls        // MAIL_ENCRYPTION：加密方式（通常用 tls）
MAIL_FROM_ADDRESS=hello@example.com // MAIL_FROM_ADDRESS/MAIL_FROM_NAME：預設寄件人信箱與名稱
MAIL_FROM_NAME="Example"
```

### *2. Mailgun*
<!--
Mailgun 是一個雲端郵件發送服務，適合大量寄送應用程式郵件（如註冊、通知、行銷信），主打 API 操作簡單、信件追蹤、彈性高。
你只要註冊 Mailgun，取得 API 金鑰，並在 Laravel 設定好，就能用 Laravel Mail 直接寄信。
-->
- 安裝 Mailgun 驅動：
<!--
這行指令會安裝 Mailgun 的 PHP 套件，讓 Laravel 能用 Mailgun API 寄信。
-->
```bash
composer require symfony/mailgun-mailer symfony/http-client
```
- **config/mail.php**：
<!--
設定 mailgun 為預設 mailer，並在 mailers 裡新增 mailgun 設定。
- 'default' 設為 mailgun，表示預設用 mailgun 寄信
- 'mailers' 裡新增 mailgun 設定，'transport' 指定為 mailgun
-->
```php
'default' => env('MAIL_MAILER', 'mailgun'),
'mailers' => [
    'mailgun' => [
        // transport 代表「寄信的傳送方式或驅動」，這裡指定用 mailgun 服務
        'transport' => 'mailgun',
        // client 參數可用來設定 HTTP 客戶端的額外選項，例如 API 請求的逾時（timeout）秒數，單位為秒。這對於 API 型郵件服務（如 mailgun、postmark）特別有用，能避免請求卡住太久。
        // 'client' => ['timeout' => 5],
    ],
],
```
- **config/services.php**：
<!--
設定 Mailgun 的 API 網域、金鑰、端點等資訊，Laravel 會用這些資訊連線到 Mailgun 服務。
-->
```php
'mailgun' => [
    'domain' => env('MAILGUN_DOMAIN'),
    'secret' => env('MAILGUN_SECRET'),
    'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    'scheme' => 'https',
],
```

### *3. Postmark*
<!--
Postmark 是一個雲端郵件發送服務，主打高送達率、速度快、API 操作簡單，適合寄送系統通知信、驗證信等重要郵件。
你只要註冊 Postmark，取得 API 金鑰，並在 Laravel 設定好，就能用 Laravel Mail 直接寄信。
-->
- 安裝 Postmark 驅動：
<!--
這行指令會安裝 Postmark 的 PHP 套件，讓 Laravel 能用 Postmark API 寄信。
-->
```bash
composer require symfony/postmark-mailer symfony/http-client
```
- **config/mail.php**：
<!--
設定 postmark 為預設 mailer，並在 mailers 裡新增 postmark 設定。
- 'default' 設為 postmark，表示預設用 postmark 寄信
- 'mailers' 裡新增 postmark 設定，'transport' 指定為 postmark
- 可設定 message_stream_id 來分流信件
-->
```php
'default' => env('MAIL_MAILER', 'postmark'),
'mailers' => [
    'postmark' => [
        // transport 代表「寄信的傳送方式或驅動」，這裡指定用 postmark 服務
        'transport' => 'postmark',
        'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
        // client 參數可用來設定 HTTP 客戶端的額外選項，例如 API 請求的逾時（timeout）秒數，單位為秒。
        // 'client' => ['timeout' => 5],
    ],
],
```
- **config/services.php**：
<!--
設定 Postmark 的 API 金鑰，Laravel 會用這個金鑰連線到 Postmark 服務。
-->
```php
'postmark' => [
    'token' => env('POSTMARK_TOKEN'),
],
```

### *4. Resend*
<!--
Resend 是一個新興的雲端郵件 API 服務，主打簡單、現代化、易於整合，適合開發者快速串接應用程式郵件發送。
-->
- 安裝 Resend 驅動：
<!--
這行指令會安裝 Resend 的 PHP 套件，讓 Laravel 能用 Resend API 寄信。
-->
```bash
composer require resend/resend-php
```
- **config/mail.php**：
<!--
設定 resend 為預設 mailer，並在 mailers 裡新增 resend 設定。
- 'default' 設為 resend，表示預設用 resend 寄信
- 'mailers' 裡新增 resend 設定，'transport' 指定為 resend
-->
```php
'default' => env('MAIL_MAILER', 'resend'),
'mailers' => [
    'resend' => [
        // transport 代表「寄信的傳送方式或驅動」，這裡指定用 resend 服務
        'transport' => 'resend',
    ],
],
```
- **config/services.php**：
<!--
設定 Resend 的 API 金鑰，Laravel 會用這個金鑰連線到 Resend 服務。
-->
```php
'resend' => [
    'key' => env('RESEND_KEY'),
],
```

### *5. Amazon SES*
<!--
Amazon SES（Simple Email Service）是 AWS 提供的大量郵件發送服務，適合企業、SaaS、平台大量寄送通知、行銷郵件，主打高可靠性、彈性、低成本。
-->
- 安裝 Amazon SES 驅動：
<!--
這行指令會安裝 AWS SDK，讓 Laravel 能用 Amazon SES API 寄信。
-->
```bash
composer require aws/aws-sdk-php
```
- **config/mail.php**：
<!--
設定 ses 為預設 mailer，並在 mailers 裡新增 ses 設定，可加上 options 設定 SES 進階參數（如標籤、設定集）。
- 'default' 設為 ses，表示預設用 ses 寄信
- 'mailers' 裡新增 ses 設定，'transport' 指定為 ses
- 'options' 可設定 SES 的進階參數
-->
```php
'default' => env('MAIL_MAILER', 'ses'),
'mailers' => [
    'ses' => [
        // transport 代表「寄信的傳送方式或驅動」，這裡指定用 Amazon SES 服務
        'transport' => 'ses',
        // client 參數可用來設定 HTTP 客戶端的額外選項，例如 API 請求的逾時（timeout）秒數，單位為秒。
        // 'client' => ['timeout' => 5],
        'options' => [
            'ConfigurationSetName' => 'MyConfigurationSet',
            'EmailTags' => [
                ['Name' => 'foo', 'Value' => 'bar'],
            ],
        ],
    ],
],
```
- **config/services.php**：
<!--
設定 Amazon SES 的金鑰、區域等資訊，Laravel 會用這些資訊連線到 AWS SES 服務。
-->
```php
'ses' => [
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'token' => env('AWS_SESSION_TOKEN'), // 若用臨時憑證
],
```

### *6. MailerSend*
<!--
MailerSend 是一個現代化雲端郵件 API 服務，主打簡單整合、彈性高、支援豐富的郵件功能，適合開發者與企業寄送應用程式郵件。
-->
- 安裝 MailerSend 驅動：
<!--
這行指令會安裝 MailerSend 的 Laravel 套件，讓 Laravel 能用 MailerSend API 寄信。
-->
```bash
composer require mailersend/laravel-driver
```
- **.env**：
<!--
在 .env 設定 MailerSend 相關參數，包含 mailer 名稱、寄件人、API 金鑰等。
-->
```php
MAIL_MAILER=mailersend
MAIL_FROM_ADDRESS=app@yourdomain.com
MAIL_FROM_NAME="App Name"
MAILERSEND_API_KEY=your-api-key
```
- **config/mail.php**：
<!--
新增 mailersend mailer 設定，'transport' 指定為 mailersend。
-->
```php
'mailersend' => [
    // transport 代表「寄信的傳送方式或驅動」，這裡指定用 mailersend 服務
    'transport' => 'mailersend',
],
```

---

## **Failover 與 Round Robin 配置**

### *Failover*
- 可設定多個 mailer，主 mailer 失敗時自動切換備援。
<!--
Failover（備援）是一種高可用機制，當主要 mailer 寄信失敗時，會自動切換到備用 mailer 繼續嘗試寄信，確保信件不會因單一服務異常而遺失。
適合對寄信成功率要求高的系統。
-->
- **config/mail.php**：
<!--
設定一個 failover mailer，裡面列出多個 mailer 名稱（如 postmark、mailgun、sendmail），當主 mailer 失敗時會自動換下一個 mailer 嘗試寄信。
'default' 設為 failover，表示預設用這種備援機制。
-->
```php
'mailers' => [
    'failover' => [
        // transport 代表「寄信的傳送方式或驅動」，這裡指定用 failover（備援）機制
        'transport' => 'failover',
        'mailers' => [
            'postmark',
            'mailgun',
            'sendmail',
        ],
    ],
],
'default' => env('MAIL_MAILER', 'failover'),
```

### *Round Robin*
- 多個 mailer 輪流分擔寄信（負載平衡）。
<!--
Round Robin（輪詢）是一種負載平衡機制，會讓多個 mailer 輪流分擔寄信工作，平均分散流量，減少單一服務壓力。
所謂「輪詢機制」就是每次有新信要寄時，依序輪流選用不同的 mailer。例如第一次用 A，第二次用 B，第三次用 C，第四次又回到 A，如此循環。這樣可以避免所有信件都集中在同一個服務，達到分流與分散風險的效果。
適合大量寄信、需要分散風險或流量的情境。
-->
- **config/mail.php**：
<!--
設定一個 roundrobin mailer，裡面列出多個 mailer 名稱（如 ses、postmark），每次寄信會輪流使用不同 mailer。
'default' 設為 roundrobin，表示預設用這種輪詢機制。
-->
```php
'mailers' => [
    'roundrobin' => [
        // transport 代表「寄信的傳送方式或驅動」，這裡指定用 roundrobin（輪詢）機制
        'transport' => 'roundrobin',
        'mailers' => [
            'ses',
            'postmark',
        ],
    ],
],
'default' => env('MAIL_MAILER', 'roundrobin'),
```

---

## **產生 Mailable 類別**

<!--
Mailable 是 Laravel 用來定義郵件內容、主旨、收件人、附件等的專屬類別。
每一種郵件（如訂單通知、驗證信）都建議用獨立 mailable class 管理，方便維護與擴充。
-->
- 每種郵件都建議用獨立 mailable class，放在 **app/Mail** 目錄。
<!--
使用 artisan 指令可以快速產生 mailable 類別，不用手動建立檔案。
-->
- Artisan 指令：
```bash
php artisan make:mail OrderShipped
```
<!--
執行上面指令後，會在 app/Mail 目錄下產生 OrderShipped.php 檔案。
你可以在這個類別裡自訂收件人、主旨、內容、附件等細節，讓每種郵件有獨立的設定與邏輯。
-->
- 產生 app/Mail/OrderShipped.php，可自訂收件人、主旨、內容、附件等。

---

## **Writing Mailables（進階撰寫與設定）**

<!--
以下介紹的 envelope()、content() 等方法，都是定義在你自訂的 mailable 類別裡（例如 app/Mail/OrderShipped.php）。
每一種郵件都會有一個對應的 mailable class，這些 class 都放在 app/Mail/ 目錄下。
envelope() 用來設定主旨、寄件人、標籤等信件屬性；content() 用來設定郵件內容樣板與傳遞資料。
-->

### 1. *envelope()* 方法
- 設定主旨、寄件人（from）、回覆地址（replyTo）、標籤（tags）、元資料（metadata）、自訂 Symfony Message（using）。
- 範例：
```php
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Symfony\Component\Mime\Email;

public function envelope(): Envelope
{
    return new Envelope(
        from: new Address('jeffrey@example.com', 'Jeffrey Way'), // 寄件人
        replyTo: [
            new Address('taylor@example.com', 'Taylor Otwell'), // 回覆地址
        ],
        subject: '訂單已出貨', // 主旨
        tags: ['shipment'], // 標籤
        metadata: [
            'order_id' => $this->order->id, // 元資料
        ],
        using: [
            function (Email $message) {
                // 可自訂 Symfony Message，例如設定優先權
                $message->priority(1);
            },
        ]
    );
}
```
<!--
如果你想全域設定寄件人、回覆地址，可以在 config/mail.php 裡設定。
-->
- 若全域寄件人、回覆地址皆相同，可於 **config/mail.php** 設定：
```php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
    'name' => env('MAIL_FROM_NAME', '範例名稱'),
],
'reply_to' => [
    'address' => 'example@example.com',
    'name' => 'App Name',
],
```
## **其他補充**
- *API 驅動*（Mailgun、Postmark、Resend、MailerSend）通常比 *SMTP* 更快更穩。
- 可於 mailable 的 headers() 方法自訂郵件 header（如 SES List Management）。
- 參考官方文件可設定更多細節（如超時、區域、憑證、標籤等）。 

<!--
content() 方法同樣定義在 mailable 類別（如 app/Mail/OrderShipped.php）裡，用來設定郵件內容樣板與傳遞資料。
-->
### 2. *content()* 方法
- 設定 HTML 與純文字模板、傳遞資料（public 屬性或 with 參數）。
- 範例：
```php
use Illuminate\Mail\Mailables\Content;

// 方式一：public 屬性自動注入
public function __construct(public Order $order) {}

public function content(): Content
{
    return new Content(
        view: 'mail.orders.shipped', // HTML 模板
        text: 'mail.orders.shipped-text', // 純文字模板
    );
}
// Blade 可直接用 $order

// 方式二：with 參數自訂資料
public function __construct(protected Order $order) {}

public function content(): Content
{
    return new Content(
        view: 'mail.orders.shipped',
        with: [
            'orderName' => $this->order->name,
            'orderPrice' => $this->order->price,
        ],
    );
}
// Blade 用 $orderName、$orderPrice
```

### 3. *attachments()* 方法
- 支援本地檔案、Storage、原始資料、Attachable 物件。
- 範例：
```php
use Illuminate\Mail\Mailables\Attachment;

public function attachments(): array
{
    return [
        // 本地檔案
        Attachment::fromPath(storage_path('app/invoice.pdf'))
            ->as('發票.pdf')
            ->withMime('application/pdf'),
        // Storage 檔案
        Attachment::fromStorage('orders/' . $this->order->id . '/receipt.pdf')
            ->as('收據.pdf')
            ->withMime('application/pdf'),
        // 原始資料
        Attachment::fromData(fn () => $this->pdfData, '報表.pdf')
            ->withMime('application/pdf'),
        // Attachable 物件
        $this->photo,
    ];
}
```

### 4. *內嵌圖片（Inline Attachments）*
- Blade 模板內：
```blade
<img src="{{ $message->embed($inlineImagePath) }}">
<img src="{{ $message->embedData($data, 'example-image.jpg') }}">
```
- $message 變數自動注入於 Blade 模板。

### 5. *Attachable 物件*
- 只要物件實作 **Illuminate\Contracts\Mail\Attachable** 介面，即可直接傳入 attachments。
- 範例：
```php
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Mail\Attachment;

class Photo extends Model implements Attachable
{
    public function toMailAttachment(): Attachment
    {
        return Attachment::fromPath('/path/to/file');
    }
}
```

### 6. *headers()*方法
- 可自訂郵件標頭（Message-Id、References、任意自訂標頭）。
- 範例：
```php
use Illuminate\Mail\Mailables\Headers;

public function headers(): Headers
{
    return new Headers(
        messageId: 'order-' . $this->order->id . '@example.com',
        references: ['previous-message@example.com'],
        text: [
            'X-Order-Header' => 'Order-' . $this->order->id,
        ],
    );
}
```

---

### *小結*
- **envelope()**：寄件人、回覆、主旨、標籤、元資料、自訂 Message
- **content()**：HTML/純文字模板、資料傳遞
- **attachments()**：多種附件型態
- **headers()**：自訂標頭
- 內嵌圖片、Attachable 物件皆支援
- 標籤/元資料可供第三方郵件服務追蹤 

---

## **Markdown Mailables（Markdown 郵件）**

### 1. *介紹*
- Markdown mailable 讓你在郵件中直接使用 Laravel 內建的 Markdown 樣板與元件，快速產生美觀、響應式的郵件。
- 自動產生 HTML 與純文字版本，支援 Blade + Markdown 語法。

### 2. *產生 Markdown Mailable*
- 使用 Artisan 指令：
```bash
php artisan make:mail OrderShipped --markdown=mail.orders.shipped
```
- 會建立 `app/Mail/OrderShipped.php`，並自動產生 `resources/views/mail/orders/shipped.blade.php`。

### 3. *content() 使用 markdown 參數*
```php
use Illuminate\Mail\Mailables\Content;

public function content(): Content
{
    return new Content(
        markdown: 'mail.orders.shipped', // 指定 Markdown 樣板
        with: [
            'url' => $this->orderUrl, // 傳遞資料給樣板
        ],
    );
}
```
- `markdown` 參數取代 `view`，指定 Markdown 樣板路徑。

### 4. *Markdown 樣板語法與元件*
- 樣板檔案：`resources/views/mail/orders/shipped.blade.php`
- 範例：
```html
<x-mail::message>
# 訂單已出貨

您的訂單已經寄出！

<x-mail::button :url="$url" color="success">
查看訂單
</x-mail::button>

<x-mail::panel>
如有任何問題，請聯絡客服。
</x-mail::panel>

<x-mail::table>
| 商品名稱   | 數量 | 價格   |
| ---------- | :--: | -----: |
| Laravel T  |  2   | $100   |
| PHP Book   |  1   | $50    |
</x-mail::table>

感謝您的支持！<br>
{{ config('app.name') }}
</x-mail::message>
```
- **x-mail::message**：外層容器，會自動套用主題與樣式。
- **x-mail::button**：產生置中的按鈕，支援 `url` 與 `color`（primary、success、error）。
- **x-mail::panel**：突顯區塊。
- **x-mail::table**：Markdown 表格自動轉為 HTML 表格。

### 5. *匯出元件與自訂主題、CSS*
- 匯出元件：
```bash
php artisan vendor:publish --tag=laravel-mail
```
- 會將所有 Markdown 元件與主題 CSS 匯出到 `resources/views/vendor/mail`。
- `html/themes/default.css` 可自訂 CSS，會自動轉為 inline style。
- 新增 CSS 檔到 `resources/views/vendor/mail/html/themes/`，如 `mytheme.css`。
- 設定全域主題（`config/mail.php`）：
```php
'markdown' => [
    'theme' => 'mytheme',
    'paths' => [
        resource_path('views/vendor/mail'),
    ],
],
```
- 或在單一 mailable 設定 `$theme` 屬性：
```php
public $theme = 'mytheme';
```

### 6. *完整實作範例*

#### **app/Mail/OrderShipped.php**
```php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShipped extends Mailable
{
    use Queueable, SerializesModels;

    public string $orderUrl;

    // 可自訂主題
    public $theme = 'default';

    /**
     * 建構子，注入訂單網址
     */
    public function __construct(string $orderUrl)
    {
        $this->orderUrl = $orderUrl;
    }

    /**
     * 設定信件 Envelope（主旨）
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '訂單已出貨'
        );
    }

    /**
     * 設定內容，使用 markdown 樣板
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.orders.shipped',
            with: [
                'url' => $this->orderUrl,
            ],
        );
    }
}
```

#### **resources/views/mail/orders/shipped.blade.php**
```blade
<x-mail::message>
# 訂單已出貨

您的訂單已經寄出！

<x-mail::button :url="$url" color="success">
查看訂單
</x-mail::button>

<x-mail::panel>
如有任何問題，請聯絡客服。
</x-mail::panel>

感謝您的支持！<br>
{{ config('app.name') }}
</x-mail::message>
```

---

## **Sending Mail（寄送郵件）**

### 1. *基本寄信*
- 使用 Mail facade 的 **to 方法**指定收件人，send 方法傳入 mailable 實例。
- 可接受 email、user 實例或 user 集合。
```php
Mail::to($user)->send(new OrderShipped($order));
Mail::to('test@example.com')->send(new OrderShipped($order));
Mail::to([$user1, $user2])->send(new OrderShipped($order));
```
- 也可鏈式設定 cc、bcc：
```php
Mail::to($user)
    ->cc($ccUsers)
    ->bcc($bccUsers)
    ->send(new OrderShipped($order));
```

### 2. *迴圈寄信注意事項*
- **每次都要 new 一個 mailable 實例，避免收件人累積**：
```php
foreach (["a@example.com", "b@example.com"] as $recipient) {
    Mail::to($recipient)->send(new OrderShipped($order));
}
```

### 3. *指定 mailer*
- 可用 mailer 方法指定特定 mailer（如 postmark）：
```php
Mail::mailer('postmark')->to($user)->send(new OrderShipped($order));
```

### 4. *佇列寄信（Queueing Mail）*
- 使用 **queue** 方法將郵件推送到背景佇列：
```php
Mail::to($user)->queue(new OrderShipped($order));
```
- **延遲寄送**：
```php
Mail::to($user)->later(now()->addMinutes(10), new OrderShipped($order));
```
- **指定佇列連線與名稱**：
```php
$message = (new OrderShipped($order))
    ->onConnection('sqs')
    ->onQueue('emails');
Mail::to($user)->queue($message);
```
- 若 mailable 實作 **ShouldQueue 介面**，無論 send/queue 都會自動進入佇列：
```php
use Illuminate\Contracts\Queue\ShouldQueue;
class OrderShipped extends Mailable implements ShouldQueue { /* ... */ }
```

### 5. *afterCommit（交易後寄信）*
- **若資料庫交易尚未提交，佇列郵件可能讀不到最新資料**。
- 可用 afterCommit 方法確保交易完成後才寄信：
```php
Mail::to($user)->send((new OrderShipped($order))->afterCommit());
```
- 也可在建構子呼叫 $this->afterCommit()。

### 6. *佇列失敗處理*
- mailable 可定義 failed(Throwable $exception) 方法處理失敗：
```php
public function failed(Throwable $exception) {
    // 記錄錯誤、通知管理員等
}
```

### 7. *渲染 mailable 內容*
- 可用 **render()** 取得 mailable 的 HTML 字串內容：
```php
// 產生一個新的 OrderShipped mailable 實例，並傳入 $order 物件
// render() 方法會將 mailable 內容渲染成 HTML 字串
$html = (new OrderShipped($order))->render();
```

### 8. *預覽 mailable*
- 路由或 controller 直接 return mailable，可在瀏覽器預覽設計：
```php
Route::get('/mailable', function () {
    $order = App\Models\Order::find(1);
    return new App\Mail\OrderShipped($order);
});
```

### 9. *多語系寄信*
- 可用 locale 方法切換語系：
```php
Mail::to($user)->locale('ja')->send(new OrderShipped($order));
```
- 若 user 實作 **HasLocalePreference 介面**，會自動用該語系：
```php
use Illuminate\Contracts\Translation\HasLocalePreference;
class User extends Model implements HasLocalePreference {
    public function preferredLocale(): string { return $this->locale; }
}
Mail::to($user)->send(new OrderShipped($order)); // 自動用 $user->locale
```

---

## **Testing（郵件測試）**

### 1. *Mailable 內容測試*
- 可直接對 mailable 實例斷言內容、收件人、主旨、附件、標籤、元資料等。
```php
// 定義一個測試，名稱為 'mailable content'
test('mailable content', function () {
    // 建立一個假的使用者資料
    $user = User::factory()->create();
    // 建立一個新的 OrderShipped 郵件物件，並傳入 $user
    $mailable = new OrderShipped($user);
    // 斷言寄件人是 jeffrey@example.com
    $mailable->assertFrom('jeffrey@example.com');
    // 斷言收件人是 taylor@example.com
    $mailable->assertTo('taylor@example.com');
    // 斷言有 cc 給 abigail@example.com
    $mailable->assertHasCc('abigail@example.com');
    // 斷言有 bcc 給 victoria@example.com
    $mailable->assertHasBcc('victoria@example.com');
    // 斷言回覆信箱是 tyler@example.com
    $mailable->assertHasReplyTo('tyler@example.com');
    // 斷言主旨是「訂單已出貨」
    $mailable->assertHasSubject('訂單已出貨');
    // 斷言有標籤 shipment
    $mailable->assertHasTag('shipment');
    // 斷言有元資料 order_id，值為 $user->id
    $mailable->assertHasMetadata('order_id', $user->id);
    // 斷言 HTML 內容有出現 $user->email
    $mailable->assertSeeInHtml($user->email);
    // 斷言 HTML 內容有依序出現這兩段文字
    $mailable->assertSeeInOrderInHtml(['訂單已出貨', '感謝您的支持']);
    // 斷言純文字內容有出現 $user->email
    $mailable->assertSeeInText($user->email);
    // 斷言有附加指定路徑的檔案
    $mailable->assertHasAttachment('/path/to/file');
    // 斷言有附加原始資料 $pdfData，檔名 name.pdf，MIME 類型為 PDF
    $mailable->assertHasAttachedData($pdfData, 'name.pdf', ['mime' => 'application/pdf']);
    // 斷言有從 storage 附加檔案
    $mailable->assertHasAttachmentFromStorage('/path/to/file', 'name.pdf', ['mime' => 'application/pdf']);
    // 斷言有從 s3 儲存空間附加檔案
    $mailable->assertHasAttachmentFromStorageDisk('s3', '/path/to/file', 'name.pdf', ['mime' => 'application/pdf']);
});
```

### 2. *Mail fake 寄送測試*
- 使用 Mail::fake() 可防止實際寄信，並可斷言寄送行為。
```php
// 啟用 Mail fake，所有寄信動作都不會真的寄出，而是被攔截下來
Mail::fake();
// ...執行寄信邏輯...
// 斷言沒有任何信件被寄出
Mail::assertNothingSent();
// 斷言有寄出 OrderShipped 這個 mailable
Mail::assertSent(OrderShipped::class);
// 斷言有寄出 2 封 OrderShipped mailable
Mail::assertSent(OrderShipped::class, 2);
// 斷言有寄出 OrderShipped 給 test@example.com
Mail::assertSent(OrderShipped::class, 'test@example.com');
// 斷言沒有寄出 AnotherMailable 這種信件
Mail::assertNotSent(AnotherMailable::class);
// 斷言總共寄出 3 封信
Mail::assertSentCount(3);
// 斷言有將 OrderShipped mailable 推送到佇列
Mail::assertQueued(OrderShipped::class);
// 斷言沒有將 OrderShipped mailable 推送到佇列
Mail::assertNotQueued(OrderShipped::class);
// 斷言總共推送 2 封信到佇列
Mail::assertQueuedCount(2);
```
- 可傳 closure 進行更細緻斷言：
```php
// 斷言有寄出至少一封 OrderShipped，且該信件的 order id 等於 $order->id
// 這種寫法，不是要回傳資料，而是要「回傳 true 或 false」來判斷「有沒有寄出符合條件的信件」。
// 你傳入一個 closure（匿名函式），Laravel 會把所有寄出的 OrderShipped mailable 一個一個丟進這個 closure。
// 只要 closure 回傳 true，這個斷言就成立（代表有寄出至少一封符合條件的信）。
// 如果 closure 都回傳 false，斷言就會失敗。
Mail::assertSent(function (OrderShipped $mail) use ($order) {
    return $mail->order->id === $order->id;
});
// 斷言有寄出至少一封 OrderShipped，且收件人為 $user->email，主旨為「訂單已出貨」
Mail::assertSent(OrderShipped::class, function (OrderShipped $mail) use ($user) {
    return $mail->hasTo($user->email) && $mail->hasSubject('訂單已出貨');
});
```
- 斷言附件：
```php
// 斷言有寄出至少一封 OrderShipped，且有附加指定的 PDF 檔案
Mail::assertSent(OrderShipped::class, function (OrderShipped $mail) {
    return $mail->hasAttachment(
        Attachment::fromPath('/path/to/file')->as('name.pdf')->withMime('application/pdf')
    );
});
```

### 3. *斷言未寄送/未佇列*
```php
// 斷言沒有任何信件被寄出或推送到佇列
Mail::assertNothingOutgoing();
// 斷言沒有寄出符合條件的 OrderShipped 郵件
Mail::assertNotOutgoing(function (OrderShipped $mail) use ($order) {
    return $mail->order->id === $order->id;
});
```

### 4. *mailable render 預覽*
- 可直接取得 mailable 的 HTML 字串內容：
```php
// 產生一個新的 OrderShipped mailable 實例，並傳入 $order 物件
// render() 方法會將 mailable 內容渲染成 HTML 字串
$html = (new OrderShipped($order))->render();
```

### 5. *測試用 log/mailtrap/mailpit 驅動*

log、mailtrap、mailpit 都是開發與測試時常用的郵件驅動，**讓你不會真的把信寄出去，而是方便你檢查信件內容**。
- **log 驅動**：把信件內容寫到 log 檔案（如 storage/logs/laravel.log），不會真的寄出，適合只想檢查內容格式。
- **mailtrap**：線上郵件沙盒，信件只會進 mailtrap，不會寄到真實用戶，適合團隊協作與集中管理測試信件。
- **mailpit**：本地郵件測試伺服器，信件可在本機網頁即時預覽，適合本地開發與 Laravel Sail 預設環境。
- 這些工具能大幅提升開發安全性與效率。

- Laravel Sail 預設支援 mailpit，瀏覽 http://localhost:8025 可預覽。

### 6. *alwaysTo 全域收件人*
- 可於 ServiceProvider boot 方法設定本地開發時所有信件都寄到指定信箱：
```php
if ($this->app->environment('local')) {
    Mail::alwaysTo('test@example.com');
}
```

### 7. *郵件事件*
<!--
郵件事件是 Laravel 提供的事件系統，讓你可以在郵件寄出前後「攔截」或「監聽」郵件行為，執行自訂邏輯。
常見事件：
- MessageSending：信件即將寄出時觸發（可用來阻擋、修改內容、記錄等）
- MessageSent：信件成功寄出後觸發（可用來記錄、通知、後續處理等）
用途：
- 寄信前後自動記錄 log
- 寄信後通知管理員
- 寄信前檢查內容或加密
- 寄信後觸發其他業務流程
-->
```php
// 匯入 MessageSending 事件類別
use Illuminate\Mail\Events\MessageSending;

// 定義一個事件監聽器類別
class LogMessage {
    // handle 方法會在事件觸發時被呼叫
    public function handle(MessageSending $event) {
        // 這裡可以寫自訂邏輯，例如記錄寄信內容到 log
        // $event->message 取得即將寄出的郵件物件
        // ...
    }
}
```

### 8. *自訂 transport 擴充*
- 可自訂**郵件傳送驅動**，繼承 Symfony\Component\Mailer\Transport\AbstractTransport，並於 ServiceProvider 註冊。
```php
Mail::extend('mailchimp', function (array $config = []) {
    $client = new ApiClient;
    $client->setApiKey($config['key']);
    return new MailchimpTransport($client);
});
```
- config/mail.php 新增 mailer 設定即可使用。

--- 

### 9. *自訂郵件傳送驅動（Custom Transport）*
<!--
有時你需要串接 Laravel 預設未支援的郵件服務（如 Mailchimp Transactional），可以自訂 Transport 並註冊於 ServiceProvider。
自訂 transport 的情境：
- 你要用的郵件服務 Laravel 沒有內建支援
- 你想要完全自訂寄信流程或 API 整合
實作步驟：
1. 建立自訂 Transport 類別，繼承 Symfony\Component\Mailer\Transport\AbstractTransport，實作 doSend 方法
2. 在 ServiceProvider 的 boot 方法用 Mail::extend 註冊自訂 transport
3. 在 config/mail.php 新增 mailer 設定，指定 transport 名稱
之後即可用 Mail::mailer('自訂名稱') 寄信
-->

#### **步驟一：自訂 Transport 類別**
```php
// app/Mail/MailchimpTransport.php

// 匯入 Mailchimp Transactional API 的 PHP 客戶端
use MailchimpTransactional\ApiClient;
// 匯入 Symfony 的郵件相關類別
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

// 定義自訂的 MailchimpTransport 類別，繼承 AbstractTransport
class MailchimpTransport extends AbstractTransport
{
    /**
     * 建構子，注入 Mailchimp API client
     */
    public function __construct(
        protected ApiClient $client, // 這裡用 protected 屬性注入 Mailchimp API 客戶端
    ) {
        parent::__construct(); // 呼叫父類別建構子
    }

    /**
     * 實作郵件實際發送邏輯
     */
    protected function doSend(SentMessage $message): void
    {
        // 將 Symfony 的 SentMessage 轉換成 Email 物件
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        // 呼叫 Mailchimp API 寄送郵件
        $this->client->messages->send(['message' => [
            'from_email' => $email->getFrom(), // 設定寄件人
            'to' => collect($email->getTo())->map(function (Address $email) {
                // 將收件人轉換成 Mailchimp 需要的格式
                return ['email' => $email->getAddress(), 'type' => 'to'];
            })->all(),
            'subject' => $email->getSubject(), // 設定主旨
            'text' => $email->getTextBody(),   // 設定純文字內容
        ]]);
    }

    /**
     * 傳回 transport 字串識別
     */
    public function __toString(): string
    {
        return 'mailchimp'; // 這個 transport 的識別名稱
    }
}
```

#### **步驟二：於 ServiceProvider 註冊自訂 Transport**
```php
// app/Providers/AppServiceProvider.php

// 匯入自訂的 MailchimpTransport
use App\Mail\MailchimpTransport;
// 匯入 Laravel 的 Mail 門面
use Illuminate\Support\Facades\Mail;
// 匯入 Mailchimp API 客戶端
use MailchimpTransactional\ApiClient;

public function boot(): void
{
    // 註冊自訂的 mailchimp transport 到 Laravel
    Mail::extend('mailchimp', function (array $config = []) {
        $client = new ApiClient;
        $client->setApiKey($config['key']);
        return new MailchimpTransport($client);
    });
}
```

#### **步驟三：config/mail.php 設定 mailer**
```php
// config/mail.php

'mailchimp' => [
    'transport' => 'mailchimp', // 指定使用自訂的 mailchimp transport
    'key' => env('MAILCHIMP_API_KEY'), // API 金鑰
    // ...其他自訂參數
],
```
// **之後即可用 Mail::mailer('mailchimp') 寄信**

#### 其他 **Symfony 官方維護郵件傳送驅動（共通整合步驟）**
<!--
Brevo（原名 Sendinblue）是一個國際雲端郵件服務，支援交易郵件、行銷郵件、SMS 等多種功能，API 整合簡單，適合需要大量
寄信或行銷自動化的企業。
特色：
- 支援交易郵件、行銷郵件、簡訊、聯絡人管理等多功能
- API 整合容易，適合 Laravel、PHP、Node.js 等
- 高送達率、彈性計費、歐洲品牌（GDPR 友善）
- 適合新創、中小企業、歐洲市場
與 Mailgun、Postmark 類似，都是第三方雲端郵件服務，但 Brevo 更強調行銷自動化與多元通訊。

Symfony 官方維護的郵件服務（如 Mailgun、Postmark、Brevo/Sendinblue 等）整合步驟大致相同，僅差在套件名稱、API 金鑰與部分特殊參數。
以下為共通整合模板，後面針對各服務補充差異。
-->

##### *共通整合步驟*
1. **安裝對應套件**
```bash
composer require symfony/brevo-mailer symfony/http-client
# composer require symfony/xxx-mailer symfony/http-client
# xxx 請替換為 mailgun、postmark、brevo 等
```

2. **設定 API 金鑰**
在 `config/services.php` 加入：
```php
'brevo' => [
    'key' => env('BREVO_API_KEY'),
// 'xxx' => [
//     依各服務需求填寫 domain、key、token、secret 等
// ],
```

3. **註冊 transport（如需自訂）**
部分服務（如 Brevo）需在 `AppServiceProvider` 的 `boot` 方法註冊：
```php
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
// use Symfony\Component\Mailer\Bridge\Xxx\Transport\XxxTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

public function boot(): void
{
        Mail::extend('brevo', function () {
        return (new BrevoTransportFactory)->create(
    // Mail::extend('xxx', function () {
        // return (new XxxTransportFactory)->create(
            new Dsn(
                'brevo+api',
                // 'xxx+api',
                'default',
                config('services.brevo.key')
                // config('services.xxx.key') // 或其他金鑰欄位
            )
        );
    };
}
```

4. **設定 mailer**
在 `config/mail.php` 加入：
```php
'brevo' => [
    'transport' => 'brevo',
// 'xxx' => [
//     'transport' => 'xxx',
//      ...其他自訂參數
],
```

5. **寄信時指定 mailer**
```php
Mail::mailer('brevo')->to($user)->send(new OrderShipped($order));
// Mail::mailer('xxx')->to($user)->send(new OrderShipped($order));
```

---

##### *各服務差異補充*

- **Mailgun**
  - 套件名稱：`symfony/mailgun-mailer`
  - services.php：`'mailgun' => ['domain' => ..., 'secret' => ..., 'endpoint' => ...]`
  - Laravel 內建支援，通常不需自訂 transport

- **Postmark**
  - 套件名稱：`symfony/postmark-mailer`
  - services.php：`'postmark' => ['token' => ...]`
  - Laravel 內建支援，通常不需自訂 transport

- **Brevo (Sendinblue)**
  - 套件名稱：`symfony/brevo-mailer`
  - services.php：`'brevo' => ['key' => ...]`
  - 需自訂 transport（如上方範例）
<!--
下表整理了 Mailgun、Postmark、Brevo 等常見郵件服務的整合差異，包含套件名稱、services.php 設定範例，以及是否需要自訂 transport，方便你快速對照選用。
-->
| 服務     | 套件名稱                  | services.php 設定範例                                      | 是否需自訂 transport |
|----------|---------------------------|------------------------------------------------------------|---------------------|
| Mailgun  | symfony/mailgun-mailer    | 'mailgun' => ['domain' => ..., 'secret' => ...]            | 否（Laravel 內建）  |
| Postmark | symfony/postmark-mailer   | 'postmark' => ['token' => ...]                             | 否（Laravel 內建）  |
| Brevo    | symfony/brevo-mailer      | 'brevo' => ['key' => ...]                                  | 是                  |

--- 
