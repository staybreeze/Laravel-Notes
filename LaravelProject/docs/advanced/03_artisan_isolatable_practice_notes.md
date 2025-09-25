# *Laravel Isolatable 指令實作範例*

---

## 1. **基本 Isolatable 指令**

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

- 執行時加 `--isolated`，*同一時間只允許一個執行*：

  ```bash
  php artisan mail:send 1 --isolated
  ```

- 自訂`失敗時的狀態碼`：

  ```bash
  php artisan mail:send 1 --isolated=12
  ```

---

## 2. **進階自訂 Lock ID**

```php
public function isolatableId(): string
{
    // 依參數自訂唯一 lock key
    return $this->argument('user');
    // lock key 用來識別「隔離執行」的唯一任務，  
    // 可以避免同一個參數（如 user）重複執行指令，  
    // 確保同時只會有一個相同任務在執行，提升安全性與穩定性。
}
```

---

## 3. **進階自訂 Lock 過期時間**

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

## 4. **實作重點**

- 只要 *implements Isolatable*，Laravel 會自動幫你加上 `--isolated` 參數
- 執行時加 `--isolated`，*會用 cache 做 lock*，__確保同一時間只有一個指令執行__
- __不會排隊，搶不到 lock 直接結束__
- 適合`高併發、排程、批次`等場景 