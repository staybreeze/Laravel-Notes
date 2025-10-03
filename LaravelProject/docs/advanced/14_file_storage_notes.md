# *什麼是 Laravel 檔案儲存系統？*

- `Laravel 檔案儲存系統`：
就是 Laravel 幫你包好的一套「__存檔案、讀檔案、刪檔案__」的工具，讓你不用自己寫一堆複雜的檔案操作程式。

- `Flysystem`：
這是 PHP 社群寫的一個「__檔案操作底層套件__」，Laravel 直接拿來用，讓你可以用同一套程式碼操作`本機硬碟、雲端空間、FTP、SFTP...`。

<!-- 
FTP（File Transfer Protocol）是用來在網路上傳輸檔案的協定，
但資料傳輸過程不加密，安全性較低。

SFTP（SSH File Transfer Protocol）也是用來傳輸檔案，
但它是基於 SSH，資料傳輸有加密，安全性較高。 
-->

<!-- 
SSH（Secure Shell）是一種安全的遠端連線協定，
可以加密地連線到伺服器、執行指令、傳輸檔案，
常用於管理 Linux 伺服器和安全檔案傳輸。 
-->

- `driver（驅動）`：
就是「__你要用哪一種儲存方式__」的意思，例如 `local`（本機）、`s3`（Amazon S3 雲端）、`sftp`（遠端伺服器）等。

- `local`：
指的是「__本機硬碟__」，也就是你的伺服器本身的 `storage/app` 目錄。

- `SFTP`：
一種安全的 __檔案傳輸協定__，可以 __把檔案傳到遠端伺服器__。

- `Amazon S3`：
亞馬遜的 __雲端檔案儲存服務__，適合`大量檔案、全球存取`。

- `雲端/本地`：
雲端就是像 `S3` 這種 __網路上的儲存空間__，`本地`就是你自己 __伺服器的硬碟__。

- `一致 API`：
不管你用哪一種 driver，__操作方式都一樣__。例如 `Storage::put('a.txt', '內容')`，可以存到本地，也可以存到 S3，只要換 driver 名稱。

---

## **實際例子**

- 你在開發時用 `local driver`，檔案會存到 `storage/app/avatars`。
- 上線到`正式環境`時，把 `driver` 換成 `s3`，檔案就會自動存到 `Amazon S3`。
- 你的程式碼完全不用改，因為 `Storage API `是一致的。

---

## **小結**

- Laravel *File Storage* 讓你用 __同一套程式碼，操作本機硬碟、SFTP、S3 等各種儲存空間__。
- *Flysystem* 是 __底層負責跟各種儲存空間溝通的套件__。
- *driver* 代表你 __要用哪一種儲存方式__。
- 一致 API 讓你不用管底層細節，程式碼永遠一樣。

---

# *`Storage`（檔案儲存系統） 跟 `Model/Repository`(資料表) 有什麼差別？*

- `Storage` 預設只操作「__檔案本身__」，`不會自動寫入資料庫`（DB）。
- 你只想 _存檔案、讀檔案、刪檔案_，不需要 _查詢、分頁、歸屬、描述_ 等，就只用 `Storage` 就好。

- `Model/Repository` 只有在你「__需要追蹤檔案`屬性、歸屬、查詢`__」時才會用到。

- 例如你想知道「_這個檔案是誰上傳的、什麼時候上傳、檔案描述、分類、標籤_」等，就要在 DB 建一個檔案資料表，並用 `Model/Repository` 來查詢。

- `Repository` 主要是「__封裝資料庫 CRUD 操作__」，協助你把「_檔案資訊_」和「_實體檔案_」的操作包在一起。

---

## **實際例子**

### *只用 Storage*（不進 DB）

```php
// 只會把檔案存到 storage/app/uploads
Storage::put('uploads/a.txt', '內容');
```

---

### *同時存 DB*（有 Model/Repo）

```php
// 1. 先存檔案到 Storage
$path = Storage::put('uploads', $file);
// 2. 再存一筆資料到 DB
FileAttachment::create([
    'user_id' => auth()->id(),
    'path' => $path,
    'original_name' => $file->getClientOriginalName(),
    'size' => $file->getSize(),
    'mime_type' => $file->getMimeType(),
]);
```

- 這樣你就可以用 __Model 查詢、分頁、搜尋檔案__，並且知道每個檔案屬於誰、什麼時候上傳、檔案描述等。

---

## **小結**

- *Storage* 適合「__只要檔案本身__」的情境。
- *Model/Repository* 適合「__需要查詢、管理、歸屬、描述__」等進階需求。
- 實務上，很多專案會同時用 `Storage + Model/Repository`。

---

# *Laravel File Storage 筆記*

## **設定檔重點**

### *`config/filesystems.php`*

- 所有 `disk` __設定__ 皆在此檔案。
- 每個 `disk` 代表 __一個儲存驅動與路徑__，可同時設定多個 disk。
- 範例：

```php
'default' => env('FILESYSTEM_DISK', 'local'),
'disks' => [
    // local disk：本機硬碟，僅程式內部存取，無法直接用網址存取
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'), // 檔案實際存放在 storage/app
    ],
    // public disk：本機硬碟，公開檔案，執行 php artisan storage:link 後可用網址存取
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'), // 檔案存放在 storage/app/public
        'url' => env('APP_URL').'/storage',   // 對外公開網址前綴（如 http://localhost/storage）
        'visibility' => 'public',             // 設為公開，瀏覽器可直接存取
    ],
    // s3 disk：Amazon S3 雲端儲存，適合大量檔案、全球存取
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    ],
],
```

---

## *Local Driver*

- local driver 操作 `storage/app` 目錄。
- 範例：

```php
use Illuminate\Support\Facades\Storage;

Storage::disk('local')->put('example.txt', '內容');
```

---

## **Public Disk 與 Symbolic Link**

- public disk 預設對應 `storage/app/public`。
  - public disk 是 Laravel 預設用來存放「__要讓`外部`（瀏覽器）可以直接存取__」的檔案，例如`圖片、附件`等。

- __若要讓 public disk 檔案可被網頁存取，需建立 `symbolic link`__：
  - 必須建立一個「_符號連結_」，讓 `public/storage` 指向 `storage/app/public`，這樣網址 `/storage/xxx` 才能對應到實體檔案。

```bash
#  執行這行指令會自動建立符號連結
php artisan storage:link 
#  ↑ 這行會在 public 目錄下建立一個 storage 的符號連結，指向 storage/app/public
# 這樣 /public/storage/xxx 就會對應到 storage/app/public/xxx，讓瀏覽器能直接存取檔案
```

---

- __建立後可用 asset 取得網址__：
  - `asset()` 是 Laravel _產生`靜態資源`網址_ 的輔助函式，這樣就能用網址存取 `public disk` 的檔案。

```php
echo asset('storage/file.txt'); // 產生 http://你的網域/storage/file.txt
// ↑ asset() 是 Laravel 產生 public 目錄下靜態資源網址的輔助函式
//   這裡會自動產生對應 public/storage 的網址，讓你在 Blade 模板或 API 回傳時直接取得可公開存取的檔案網址
```

---

- __`config/filesystems.php` 可設定多個 link__：
  - 你可以在設定檔裡自訂 _多個符號連結_，讓不同目錄都能對外公開。

```php
'links' => [
    public_path('storage') => storage_path('app/public'), // 預設的公開目錄
    public_path('images') => storage_path('app/images'),  // 你也可以自訂其他公開目錄
],
// ↑ 這段設定可以讓你自訂多個符號連結，舉例：public/images 也能對應到 storage/app/images
//   只要執行 php artisan storage:link，所有設定的 link 都會自動建立
```

---

- __移除 link__：
  - 若要移除所有已設定的符號連結，可用這個指令。

```bash
php artisan storage:unlink
#  ↑ 這行指令會移除所有已設定的符號連結（如 public/storage、public/images 等）
#    若不需要對外公開檔案時可使用
```

---

### *白話補充*

- *為什麼要 symbolic link？* 
  Laravel 為了安全，**預設** `storage/app` 目錄下的檔案外 __部無法直接存取__。  
  只有 `storage/app/public` 這個子目錄，經由 __symbolic link（`public/storage → storage/app/public`）後__，才能讓瀏覽器用網址存取。

- *asset() 有什麼用？*
  `asset()` 會 __自動產生 public 目錄下的`靜態資源網址`__，搭配 `symbolic link` 就能讓你在前端、API、郵件等地方正確取得檔案網址。

---

## **S3 Driver 設定**

- 需*安裝套件*：

```bash
composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies
```

---

- *.env 設定*：

```php
AWS_ACCESS_KEY_ID=xxx                  // AWS 帳號的 Access Key
AWS_SECRET_ACCESS_KEY=xxx              // AWS 帳號的 Secret Key
AWS_DEFAULT_REGION=us-east-1           // 預設 AWS 區域（如美東）
AWS_BUCKET=xxx                         // S3 儲存桶名稱
AWS_USE_PATH_STYLE_ENDPOINT=false      // 是否使用 Path-Style Endpoint（通常保持 false）
```

- `config/filesystems.php` 設定 s3 disk。

---

## **FTP/SFTP Driver 設定**

- *FTP 需安裝*：
  - `FTP（File Transfer Protocol）`是一種「__檔案傳輸協定__」，可以讓你 _把檔案從一台電腦傳到另一台伺服器_。
  - 安全性較低，資料傳輸過程**不加密**。

```bash
composer require league/flysystem-ftp "^3.0"
```

---

- *SFTP 需安裝*：
  - `SFTP（SSH File Transfer Protocol）`是一種「__安全的檔案傳輸協定__」。
  - 所有資料都會**加密**，安全性比 FTP 高很多。

```bash
composer require league/flysystem-sftp-v3 "^3.0"
```

---

- *FTP 範例*：

  - 這是設定 `FTP disk` 的範例，適合用於 __公司內部或不需要高安全性__ 的檔案傳輸。

```php
'ftp' => [
    'driver' => 'ftp', // 指定使用 FTP 協定
    'host' => env('FTP_HOST'), // FTP 伺服器位址
    'username' => env('FTP_USERNAME'), // 登入帳號
    'password' => env('FTP_PASSWORD'), // 登入密碼
    // 'port' => env('FTP_PORT', 21), // 連接埠，預設 21
    // 'root' => env('FTP_ROOT'), // 預設目錄（可選）
    // 'passive' => true, // 被動模式，解決防火牆問題（可選）
    // 'ssl' => true, // 是否啟用 SSL 加密（FTP over SSL，安全性比純 FTP 高，但還是比 SFTP 低）（可選）
    // 'timeout' => 30, // 連線逾時秒數（可選）
    // 有些設定被註解掉，是因為它們是可選參數，
    // 只有需要時才會啟用或設定，
    // 預設情況下用不到就可以不用寫，
    // 如果有特殊需求（如改 port、啟用 SSL、設定 root 目錄），
    // 再把註解拿掉並設定對應值即可。
],
```

---

- *SFTP 範例*：

  - 這是設定 `SFTP disk` 的範例，適合需要 __高安全性__ 的檔案傳輸（如`雲端主機、銀行、醫療`等）。

```php
'sftp' => [
    'driver' => 'sftp', // 指定使用 SFTP 協定
    'host' => env('SFTP_HOST'), // SFTP 伺服器位址
    'username' => env('SFTP_USERNAME'), // 登入帳號
    'password' => env('SFTP_PASSWORD'), // 登入密碼（或用 privateKey）
    // 'privateKey' => env('SFTP_PRIVATE_KEY'), // SSH 私鑰路徑（用於金鑰登入）
    // 'passphrase' => env('SFTP_PASSPHRASE'), // SSH 私鑰密碼（如有加密）
    // 'visibility' => 'private', // 預設檔案權限（private=0600, public=0644）
    // 'directory_visibility' => 'private', // 目錄權限（private=0700, public=0755）
    // 'port' => env('SFTP_PORT', 22), // 連接埠，預設 22
    // 'root' => env('SFTP_ROOT', ''), // 預設目錄（可選）
    // 'timeout' => 30, // 連線逾時秒數（可選）
    // 'useAgent' => true, // 是否用 SSH agent（可選）
],
```

---

## **Scoped 與 Read-Only Disks**

- *Scoped disk 需安裝*

```bash
composer require league/flysystem-path-prefixing "^3.0"
#  ↑ 安裝 Flysystem 的路徑前綴套件，讓你可以限制 disk 只能操作某個子目錄（scoped disk）
```

---

- *設定範例*

```php
's3-videos' => [
    'driver' => 'scoped', // 指定使用 scoped driver
    'disk' => 's3',       // 這個 scoped disk 其實是包在 s3 disk 外層
    'prefix' => 'path/to/videos', // 只允許操作 S3 上 path/to/videos 這個子目錄
],
// ↑ 這樣設定後，Storage::disk('s3-videos')->put('a.mp4', ...) 只會寫到 S3 的 path/to/videos/a.mp4
```

---

- *Read-only disk 需安裝*

```bash
composer require league/flysystem-read-only "^3.0"
#  ↑ 安裝 Flysystem 的唯讀套件，讓你可以把 disk 設成唯讀模式，防止寫入或刪除
```

---

- *設定範例*：

```php
's3-videos' => [
    'driver' => 's3',
    // ... 其他 S3 設定
    'read-only' => true, // 啟用唯讀模式，這個 disk 只能讀取，不能寫入或刪除
],
// ↑ 這樣設定後，Storage::disk('s3-videos')->put(...) 會失敗，只能讀取檔案
```

---

## **S3 相容服務**（MinIO、DO Spaces、Cloudflare R2 等）

- 只需調整 `endpoint` 設定即可：

```php
'endpoint' => env('AWS_ENDPOINT', 'https://minio:9000'),
// ↑ 設定 S3 相容服務的 endpoint，例如 MinIO 的服務位址
//   只要這裡填對，Storage 就能用同一套 API 操作 MinIO、DO Spaces、Cloudflare R2 等
```

---

- `MinIO` 需設 `AWS_URL` 供 __URL 產生__：

```bash
AWS_URL=http://localhost:9000/local
#  ↑ 若用 MinIO，必須設定 AWS_URL，這樣 Storage::url() 產生的網址才會正確指向 MinIO
```

---

## **Storage Facade 用法**

- `put`：

```php
Storage::put('avatars/1', $content); // 預設 disk
// ↑ 將內容寫入 avatars/1 檔案，使用預設的 disk（通常是 local 或 public）
Storage::disk('s3')->put('avatars/1', $content); // 指定 disk
// ↑ 將內容寫入 S3 上的 avatars/1 檔案，明確指定使用 s3 disk
```

---

- *動態建立 disk*：

```php
$disk = Storage::build([
    'driver' => 'local', // 指定使用 local driver（本地硬碟）
    'root' => '/path/to/root', // 設定這個 disk 的根目錄
]);
// ↑ 動態建立一個本地儲存的 disk，根目錄設為 /path/to/root
$disk->put('image.jpg', $content);
// ↑ 將內容寫入剛剛動態建立的 disk 下的 image.jpg 檔案
//   注意：如果是圖片檔案，$content 必須是二進位資料（binary string），不能是檔案路徑或其他型別
//   正確做法：$content = file_get_contents('/path/to/image.jpg');
//   file_get_contents() 會把圖片檔案讀成二進位字串，這樣才能正確寫入 Storage。
```

---

## **檔案讀取、下載與網址**

### 1. *取得檔案內容*

- __取得檔案內容__（`字串`）

```php
$contents = Storage::get('file.jpg');
```

---

- __取得 JSON 檔案並解碼__

```php
$orders = Storage::json('orders.json');
// 解碼是把 JSON 格式的字串轉成 PHP 陣列或物件
```

---

- __判斷檔案是否存在/不存在__

```php
if (Storage::disk('s3')->exists('file.jpg')) { /* ... */ }
if (Storage::disk('s3')->missing('file.jpg')) { /* ... */ }
```

---

### 2. *下載檔案*

- __產生下載回應__

```php
return Storage::download('file.jpg');                    // 下載檔案，檔名預設為 file.jpg
return Storage::download('file.jpg', $name, $headers);   // 下載檔案，並自訂檔名與 HTTP 標頭
```

---

### 3. *取得檔案公開網址*

- __取得公開網址__：

```php
$url = Storage::url('file.jpg');
```

---

- `local driver` 須放在 `storage/app/public`，並建立 `symbolic link`。

- 可於 `disk` 設定 `url host`：

```php
'public' => [
    'driver' => 'local',                        // 使用本地儲存
    'root' => storage_path('app/public'),        // 檔案根目錄
    'url' => env('APP_URL').'/storage',          // 對外公開網址
    'visibility' => 'public',                    // 檔案公開可存取
    'throw' => false,                            // 發生錯誤時不丟出例外
],
```

---

### 4. *臨時網址*（Temporary URLs）

- __產生臨時網址__（local/s3）

```php
$url = Storage::temporaryUrl('file.jpg', now()->addMinutes(5));
// ↑ 產生一個 5 分鐘內有效的臨時網址，讓使用者可短暫存取 file.jpg（支援 local/s3）
```

---

- `local driver` 須於 `disk` 設定 `serve`：

```php
'local' => [
    'driver' => 'local',                        // 使用本地儲存
    'root' => storage_path('app/private'),      // 指定根目錄
    'serve' => true,                            // 啟用 serve，才能產生臨時網址
    'throw' => false,                           // 發生錯誤時不丟例外
],
// ↑ 若要讓 local disk 支援臨時網址，必須加上 serve: true
```

---

- __S3 可傳遞額外參數__

```php
$url = Storage::temporaryUrl('file.jpg', now()->addMinutes(5), [
    'ResponseContentType' => 'application/octet-stream', // 設定回應的 Content-Type
    'ResponseContentDisposition' => 'attachment; filename=file2.jpg', // 下載時的檔名
]);
// ↑ S3 臨時網址可自訂回應標頭，例如強制下載、指定檔名
```

---

- __自訂臨時網址產生邏輯__（如自訂 `route`）

```php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
Storage::disk('local')->buildTemporaryUrlsUsing(
    function (string $path, DateTime $expiration, array $options) {
        return URL::temporarySignedRoute(
            'files.download', // 你自訂的下載 route 名稱
            $expiration,      // 有效期限
            array_merge($options, ['path' => $path]) // 傳遞檔案路徑等參數
        );
    }
);
// ↑ 可自訂產生臨時網址的邏輯，例如用 Laravel route 產生簽名網址
```

---

### 5. *S3 臨時上傳網址*（`temporaryUploadUrl`）

- 僅支援 `s3` driver，產生 __可供前端直接上傳的臨時網址__：

```php
['url' => $url, 'headers' => $headers] = Storage::temporaryUploadUrl('file.jpg', now()->addMinutes(5));
// ↑ 產生一組臨時上傳網址與 headers，前端可直接 PUT 上傳檔案到 S3，5 分鐘內有效
```

---

### 6. *檔案資訊*

- __取得檔案大小__（`bytes`）

```php
$size = Storage::size('file.jpg');
// ↑ 取得檔案大小（單位：位元組）
```

---

- __取得最後修改時間__（`UNIX timestamp`）

```php
$time = Storage::lastModified('file.jpg');
// ↑ 取得檔案最後修改時間（UNIX 時間戳）
// UNIX 時間戳（UNIX timestamp）是指自 1970 年 1 月 1 日 00:00:00 UTC 到現在所經過的秒數，
// 常用於程式儲存和比較時間。
```

---

- __取得 MIME type__：

```php
$mime = Storage::mimeType('file.jpg');
// ↑ 取得檔案的 MIME 類型（如 image/jpeg）
// MIME 類型（Multipurpose Internet Mail Extensions）是用來描述「檔案內容格式的標準」，
// 例如 text/plain 表示純文字、image/jpeg 表示 JPEG 圖片，
// 讓瀏覽器或程式知道如何處理和顯示檔案。
```

---

- __取得檔案路徑__：

```php
$path = Storage::path('file.jpg');
// ↑ 取得檔案在本地硬碟上的實體路徑（僅限 local driver）
```

--- 

## **檔案寫入、上傳與權限**

### 1. *寫入檔案內容*

- 寫入`字串`或 `resource`：

```php
Storage::put('file.jpg', $contents); // 儲存檔案內容（字串、二進位資料）
Storage::put('file.jpg', $resource); // 也可直接儲存檔案資源（如 fopen 取得的 resource）
```

---

- 寫入 __失敗時回傳 false__，若 disk 設定 `'throw' => true`，則會丟出 `League\Flysystem\UnableToWriteFile` 例外：

```php
if (!Storage::put('file.jpg', $contents)) {
    // 寫入失敗
}
// config/filesystems.php
'public' => [
    'driver' => 'local',
    // ...
    'throw' => true,
],
```

---

### 2. *prepend/append 寫入檔案開頭/結尾*

```php
Storage::prepend('file.log', '開頭文字'); // 在檔案開頭新增內容
Storage::append('file.log', '結尾文字'); // 在檔案結尾新增內容

Storage::prepend('file.log', '[2025-08-15] 系統啟動'); // 在 log 檔最前面加上啟動訊息
Storage::append('file.log', '[2025-08-15] 使用者登入'); // 在 log 檔最後加上登入紀錄
```

---

### 3. *檔案複製與移動*

```php
Storage::copy('old/file.jpg', 'new/file.jpg');
// ↑ 將 old/file.jpg 檔案「複製」一份到 new/file.jpg
//   舊檔案還會保留，等於有兩份一樣的檔案
Storage::move('old/file.jpg', 'new/file.jpg');
// ↑ 將 old/file.jpg 檔案「移動」到 new/file.jpg
//   舊檔案會被刪除，只剩下新路徑的檔案（等於重新命名或搬家）
```

---

### 4. *自動串流上傳*（`putFile/putFileAs`）

- putFile 會自動產生 __唯一檔名__，`putFileAs` 可自訂檔名：

```php
use Illuminate\Http\File;
$path = Storage::putFile('photos', new File('/path/to/photo'));
$path = Storage::putFileAs('photos', new File('/path/to/photo'), 'photo.jpg');
```

---

- __可指定 visibility__：

```php
Storage::putFile('photos', new File('/path/to/photo'), 'public'); // 上傳檔案到 photos 資料夾，並設為公開
```

---

### 5. *上傳檔案*（UploadedFile）

- `store/storeAs/storePublicly/storePubliclyAs` 方法

```php
$path = $request->file('avatar')->store('avatars');
// ↑ 將上傳的 avatar 檔案存到 avatars 目錄，檔名自動產生唯一值，回傳路徑（預設用預設 disk）

$path = $request->file('avatar')->storeAs('avatars', $request->user()->id);
// ↑ 將上傳的 avatar 檔案存到 avatars 目錄，檔名自訂為目前登入使用者的 id，回傳路徑（預設用預設 disk）

$path = $request->file('avatar')->store('avatars/'.$request->user()->id, 's3');
// ↑ 將上傳的 avatar 檔案存到 S3 的 avatars/使用者id 目錄，檔名自動產生唯一值，回傳路徑

$path = $request->file('avatar')->storeAs('avatars', $request->user()->id, 's3');
// ↑ 將上傳的 avatar 檔案存到 S3 的 avatars 目錄，檔名自訂為使用者 id，回傳路徑

$path = $request->file('avatar')->storePublicly('avatars', 's3');
// ↑ 將上傳的 avatar 檔案公開存到 S3 的 avatars 目錄，檔名自動產生唯一值，回傳路徑（權限設為 public）

$path = $request->file('avatar')->storePubliclyAs('avatars', $request->user()->id, 's3');
// ↑ 將上傳的 avatar 檔案公開存到 S3 的 avatars 目錄，檔名自訂為使用者 id，回傳路徑（權限設為 public）
```

---

- __也可用 Storage facade 上傳__：

```php
$path = Storage::putFile('avatars', $request->file('avatar'));
// ↑ 將上傳的 avatar 檔案存到 avatars 目錄，檔名自動產生唯一值，回傳路徑
$path = Storage::putFileAs('avatars', $request->file('avatar'), $request->user()->id);
// ↑ 將上傳的 avatar 檔案存到 avatars 目錄，檔名自訂為使用者 id，回傳路徑
```

---

### 6. *取得檔案名稱與副檔名*

- __不安全__（可能被竄改）

```php
$name = $file->getClientOriginalName();           // 取得上傳檔案的原始檔名（用戶可自訂，可能被竄改）
$extension = $file->getClientOriginalExtension(); // 取得上傳檔案的副檔名（用戶可自訂，可能被竄改）

// 這些方法取得的是使用者上傳時提供的檔名和副檔名，
// 使用者可以自行修改或偽造這些資訊，
// 所以直接使用可能有安全風險，例如覆蓋重要檔案或執行惡意程式。
```

---

- __建議用 `hashName/extension` 取得安全檔名與副檔名__：

```php
$name = $file->hashName();      // 取得安全、唯一的檔名（由系統產生）
$extension = $file->extension(); // 取得根據檔案內容判斷的副檔名（較安全）
// 這樣可以避免使用者竄改檔名或副檔名造成安全問題。
```

---

### 7. *權限*（Visibility）

- __寫入時指定 visibility__：

```php
Storage::put('file.jpg', $contents, 'public');
// ↑ 寫入檔案時直接指定權限為 public（公開），讓檔案可被外部存取
```

---

- __查詢與設定已存在檔案的 visibility__：

```php
$visibility = Storage::getVisibility('file.jpg');
// ↑ 查詢 file.jpg 目前的權限（回傳 'public' 或 'private'）
Storage::setVisibility('file.jpg', 'public');
// ↑ 將已存在的 file.jpg 權限設為 public（公開）
```

---

- __上傳檔案時指定 public 權限__：

```php
$path = $request->file('avatar')->storePublicly('avatars', 's3');
// ↑ 上傳 avatar 檔案到 S3 的 avatars 目錄，並設為公開（public），回傳路徑
$path = $request->file('avatar')->storePubliclyAs('avatars', $request->user()->id, 's3');
// ↑ 上傳 avatar 檔案到 S3 的 avatars 目錄，檔名自訂為使用者 id，並設為公開（public），回傳路徑
```

---

### 8. *local driver 權限對應*

- `public` = 0755（目錄）、0644（檔案）
- `private` = 0700（目錄）、0600（檔案）
- 可於 `config/filesystems.php` 自訂：

```php
'local' => [
    'driver' => 'local',                        // 使用本地儲存
    'root' => storage_path('app'),              // 指定根目錄
    'permissions' => [                          // 檔案與目錄權限設定
        'file' => [
            'public' => 0644,                   // 公開檔案權限
            'private' => 0600,                  // 私有檔案權限
        ],
        'dir' => [
            'public' => 0755,                   // 公開目錄權限
            'private' => 0700,                  // 私有目錄權限
        ],
    ],
    'throw' => false,                           // 發生錯誤時不丟例外
],
```

---

## **檔案與目錄刪除、目錄操作、測試、客製驅動**

### 1. *刪除檔案*

- __刪除單一或多個檔案__：

```php
Storage::delete('file.jpg');
Storage::delete(['file.jpg', 'file2.jpg']);
```

---

- __指定 disk 刪除__：

```php
Storage::disk('s3')->delete('path/file.jpg');
```
---

### 2. *目錄操作*

- 取得目錄下 __所有檔案__：

```php
$files = Storage::files($directory); // 不含子目錄
$files = Storage::allFiles($directory); // 含子目錄
```

---

- __取得目錄下所有子目錄__：

```php
$dirs = Storage::directories($directory); // 不含子目錄
$dirs = Storage::allDirectories($directory); // 含子目錄
```

---

- __建立目錄__：

```php
Storage::makeDirectory($directory);
```

---

- __刪除目錄__（含所有檔案）：

```php
Storage::deleteDirectory($directory);
```

---

### 3. *測試 Storage*

- `Storage::fake/persistentFake` 建立測試磁_：

```php
Storage::fake('photos');
// ↑ 建立一個假的 photos disk，所有 Storage 操作都只會在記憶體中，不會真的寫入硬碟
// Storage::persistentFake('photos'); // 保留檔案不自動刪除（測試結束後檔案還在）

use Illuminate\Http\UploadedFile;
UploadedFile::fake()->image('photo1.jpg');
// ↑ 產生一個假的圖片檔案 photo1.jpg，可用於測試上傳

Storage::disk('photos')->assertExists('photo1.jpg');
// ↑ 斷言 photos disk 下 photo1.jpg 檔案存在

Storage::disk('photos')->assertExists(['photo1.jpg', 'photo2.jpg']);
// ↑ 斷言 photos disk 下多個檔案都存在

Storage::disk('photos')->assertMissing('missing.jpg');
// ↑ 斷言 photos disk 下 missing.jpg 檔案不存在

Storage::disk('photos')->assertMissing(['missing.jpg', 'non-existing.jpg']);
// ↑ 斷言 photos disk 下多個檔案都不存在

Storage::disk('photos')->assertCount('/wallpapers', 2);
// ↑ 斷言 /wallpapers 目錄下有 2 個檔案

Storage::disk('photos')->assertDirectoryEmpty('/wallpapers');
// ↑ 斷言 /wallpapers 目錄為空
```

---

### 4. *客製驅動*（`Custom Filesystems`）

- 以 `Dropbox` 為例：

- **安裝套件**：

```bash
composer require spatie/flysystem-dropbox
#  ↑ 安裝 Dropbox 的 Flysystem 驅動套件
```

---

- __ServiceProvider__ 註冊：

```php
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;

public function boot(): void
{
    Storage::extend('dropbox', function (Application $app, array $config) {
        $adapter = new DropboxAdapter(new DropboxClient(
            $config['authorization_token'] // 你的 Dropbox API Token
        ));
        return new FilesystemAdapter(
            new Filesystem($adapter, $config), // 建立 Flysystem 實例
            $adapter,                         // 傳入 DropboxAdapter
            $config                           // 傳入設定
        );
    });
}
// ↑ 註冊自訂 dropbox driver，讓 Storage::disk('dropbox') 可用
```

---

- `config/filesystems.php` 設定 `dropbox` disk 即可。
    - ↑ 在 `config/filesystems.php` 新增 `dropbox` disk 設定，即可直接用 `Storage::disk('dropbox')` 操作 Dropbox

--- 

- __補充說明__：

    - `store($dir, $disk)` 是 `UploadedFile`（上傳檔案物件）的內建方法，不是 `Storage Facade` 的方法
    - 用於 __表單上傳檔案時，直接將檔案存到指定目錄與 disk__

    - *範例*：
        - `$path = $request->file('avatar')->store('avatars', 'public')`;
        -  ↑ 將上傳的 avatar 檔案存到 public disk 的 avatars 目錄，__檔名自動產生唯一值，回傳路徑__

    - *相關方法*：
        - `storeAs($dir, $filename, $disk)`：__自訂檔名__
        - `storePublicly($dir, $disk)`：__存公開檔案__
        - `storePubliclyAs($dir, $filename, $disk)`：__公開且自訂檔名__ 