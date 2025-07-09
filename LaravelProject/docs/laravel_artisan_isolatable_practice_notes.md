# Laravel Isolatable 指令實作範例

---

## 1. 基本 Isolatable 指令

```php
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class SendEmails extends Command implements Isolatable
{
    protected $signature = 'mail:send {user}';
    protected $description = 'Send a marketing email to a user';

    public function handle()
    {
        // 指令主體
    }
}
```

- 執行時加 `--isolated`，同一時間只允許一個執行：
  ```bash
  php artisan mail:send 1 --isolated
  ```
- 自訂失敗時的狀態碼：
  ```bash
  php artisan mail:send 1 --isolated=12
  ```

---

## 2. 進階自訂 Lock ID

```php
public function isolatableId(): string
{
    // 依參數自訂唯一 lock key
    return $this->argument('user');
}
```

---

## 3. 進階自訂 Lock 過期時間

```php
use DateTimeInterface;
use DateInterval;

public function isolationLockExpiresAt(): DateTimeInterface|DateInterval
{
    // 只鎖 5 分鐘，避免異常時 lock 永久卡住
    return now()->addMinutes(5);
}
```

---

## 4. 實作重點

- 只要 implements Isolatable，Laravel 會自動幫你加上 --isolated 參數
- 執行時加 --isolated，會用 cache 做 lock，確保同一時間只有一個指令執行
- 不會排隊，搶不到 lock 直接結束
- 適合高併發、排程、批次等場景 