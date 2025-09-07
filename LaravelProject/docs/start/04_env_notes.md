# *Laravel 環境變數（.env） 筆記*

## 1. **環境變數基本語法**（Basic Syntax)

### 1.1 *變數定義規則*

- 環境變數**一定要使用`雙引號`**包圍，避免特殊字元造成解析問題。

```bash
# ✅ 正確：使用雙引號
APP_NAME="My Application"

# ❌ 錯誤：不使用引號
APP_NAME=My Application

# ❌ 錯誤：使用單引號
APP_NAME='My Application'
```

---

### 1.2 *常見環境變數範例*

```bash
# 應用程式基本設定
APP_NAME="My Application"      # 應用程式名稱
APP_ENV=local                 # 執行環境（local、production 等）
APP_KEY=base64:your-key-here  # 加密用的金鑰
APP_DEBUG=true                # 是否開啟除錯模式
APP_URL=http://localhost      # 應用程式網址

# 資料庫連線設定
DB_CONNECTION=mysql           # 資料庫類型
DB_HOST=127.0.0.1             # 資料庫主機
DB_PORT=3306                  # 資料庫連接埠
DB_DATABASE=laravel           # 資料庫名稱
DB_USERNAME=root              # 資料庫使用者
DB_PASSWORD=                  # 資料庫密碼

# 快取與會話設定
CACHE_DRIVER=file             # 快取儲存方式
SESSION_DRIVER=file           # Session 儲存方式
QUEUE_CONNECTION=sync         # 隊列執行方式（同步）
```

---

## 2. **環境變數加密**（Environment Variable Encryption)

### 2.1 *基本加密指令*

- 對 `.env` 檔案進行加密，會生成新檔案 `.env.encrypted`。

```bash
# 加密 .env 檔案
php artisan env:encrypt
```

---

### 2.2 *進階加密選項*

#### 2.2.1 **指定加密金鑰**

- *手動指定* 金鑰進行加密，確保加密安全性。

```bash
# 使用指定的金鑰進行加密
php artisan env:encrypt --key=3UVsEgGVK36XN82KKeyLFMhvosbZN1aF
```

---

#### 2.2.2 **指定環境檔案**

- 對指定環境的 `.env` 檔案進行加密，適用於多環境部署。

```bash
# 對 staging 環境的 .env.staging 檔案進行加密
# 將生成新檔案 .env.staging.encrypted
php artisan env:encrypt --env=staging
```

---

## 3. **環境變數解密**（Environment Variable Decryption)

### 3.1 *基本解密指令*

- 透過 `LARAVEL_ENV_ENCRYPTION_KEY` 的值對 `.env.encrypted` 進行解密。

```bash
# 使用環境變數中的金鑰進行解密
php artisan env:decrypt
```

---

### 3.2 *進階解密選項*

#### 3.2.1 **手動金鑰解密**

- *手動指定* 金鑰進行解密，適用於`金鑰不在環境變數中`的情況。

```bash
# 使用指定的金鑰進行解密
php artisan env:decrypt --key=3UVsEgGVK36XN82KKeyLFMhvosbZN1aF
```

---

#### 3.2.2 **指定解密方法**

- 使用 `--cipher` 參數*指定解密方法*，支援不同的加密演算法。

```bash
# 使用 AES-128-CBC 解密方法
php artisan env:decrypt --key=qUWuNRdfuImXcKxZ --cipher=AES-128-CBC
```

---

#### 3.2.3 **指定環境檔案**

- 對*指定環境的加密檔案*進行解密。

```bash
# 對 .env.staging.encrypted 進行解密
php artisan env:decrypt --env=staging
```

---

#### 3.2.4 **強制覆蓋**

- 加上 `--force` 參數時，即使目標檔案已經存在，也會直接覆蓋，不會跳出警告或詢問。

```bash
# 強制覆蓋現有檔案
php artisan env:decrypt --force
```

---

## 4. **加密金鑰管理**（Encryption Key Management)

### 4.1 *金鑰生成*

- Laravel 會**自動生成**加密金鑰，也可手動指定。

```bash
# 生成新的應用程式金鑰
php artisan key:generate

# 生成新的環境變數加密金鑰
php artisan env:encrypt --key=$(openssl rand -hex 32)
```

---

### 4.2 *金鑰儲存*

- 加密金鑰**應安全儲存**，避免外洩。

```bash
# 在環境變數中設定加密金鑰
LARAVEL_ENV_ENCRYPTION_KEY=your-encryption-key-here

# 或在 .env 檔案中設定（僅限開發環境）
LARAVEL_ENV_ENCRYPTION_KEY=your-encryption-key-here
```

---

## 5. **多環境配置管理**（Multi-Environment Configuration)

### 5.1 *環境檔案命名慣例*

```bash
# 開發環境
.env.local          # 本地開發（gitignore）
.env.development    # 開發環境

# 測試環境
.env.testing        # 測試環境
.env.staging        # 預備環境

# 生產環境
.env.production     # 生產環境
.env.encrypted      # 加密後的環境檔案
```

---

### 5.2 *環境檔案加密流程*

```bash
# 1. 加密開發環境檔案
php artisan env:encrypt --env=development

# 2. 加密測試環境檔案
php artisan env:encrypt --env=staging

# 3. 加密生產環境檔案
php artisan env:encrypt --env=production

# 4. 部署時解密對應環境檔案
php artisan env:decrypt --env=production
```

---

## 6. **最佳實踐建議**（Best Practices)

### 6.1 *安全性建議*

- ✅ **建議**：在`生產環境`使用加密的 `.env` 檔案
- ✅ **建議**：`定期更換`加密金鑰
- ✅ **建議**：將加密金鑰儲存在`安全的密鑰管理系統中`
- ❌ **避免**：將`未加密`的 `.env` 檔案提交到版本控制系統
- ❌ **避免**：在程式碼中，`硬編碼`敏感資訊

---

### 6.2 *部署建議*

- ✅ **建議**：使用 `CI/CD 流程自動化環境`檔案管理
- ✅ **建議**：在部署腳本中加入`解密步驟`
- ✅ **建議**：為不同環境維護`不同`的加密金鑰
- ❌ **避免**：手動管理多個環境的配置檔案

---

### 6.3 *開發建議*

- ✅ **建議**：使用 `.env.example` 作為範本檔案
- ✅ **建議**：在團隊中`統一`環境變數命名規範
- ✅ **建議**：`定期`檢查和清理未使用的環境變數
- ❌ **避免**：在開發環境中儲存生產環境的敏感資訊

---

## 7. **常見使用場景**（Common Use Cases)

### 7.1 *開發環境設定*

```bash
# .env.local（本地開發）設定範例
APP_ENV=local           # 設定為本地開發環境（本地獨有）
APP_DEBUG=true          # 開啟除錯模式，顯示詳細錯誤訊息
DB_HOST=127.0.0.1       # 資料庫主機為本機
DB_DATABASE=laravel_dev # 使用本地開發資料庫（本地獨有）
DB_USERNAME=root        # 資料庫使用者
DB_PASSWORD=            # 資料庫密碼（本地可留空）

# 加密本地環境檔案，本地環境可選擇加密 .env 檔案
php artisan env:encrypt --env=local
```

---

### 7.2 *測試環境設定*

```bash
# .env.testing（測試環境）
APP_ENV=testing           # 設定為測試環境（測試獨有）
APP_DEBUG=false           # 測試環境通常關閉除錯模式
DB_DATABASE=laravel_test  # 使用測試專用資料庫（測試獨有）
CACHE_DRIVER=array        # 使用記憶體快取，測試時不寫入檔案（測試獨有）
SESSION_DRIVER=array      # 使用記憶體 session，測試時不寫入檔案（測試獨有）

# 加密測試環境檔案，測試環境建議加密 .env 檔案
php artisan env:encrypt --env=testing
```

---

### 7.3 *生產環境設定*

```bash
# .env.production（生產環境）
APP_ENV=production                # 設定為生產環境（生產獨有）
APP_DEBUG=false                   # 關閉除錯模式，生產環境務必設為 false
APP_URL=https://example.com       # 設定正式網址（生產獨有）
DB_HOST=production-db.example.com # 設定正式資料庫主機（生產獨有）
DB_PASSWORD=secure-password       # 設定正式資料庫密碼，請用強密碼（生產獨有）

# 加密生產環境檔案，生產環境建議加密 .env 檔案
php artisan env:encrypt --env=production
```

---

## 8. **故障排除**（Troubleshooting)

### 8.1 *加密問題*

```bash
# 檢查加密金鑰是否正確設定
echo $LARAVEL_ENV_ENCRYPTION_KEY

# 重新生成加密金鑰
php artisan env:encrypt --key=$(openssl rand -hex 32)

# 檢查檔案權限
ls -la .env*
```


---

### 8.2 *解密問題*

```bash
# 檢查加密檔案是否存在
ls -la .env.encrypted

# 使用強制覆蓋解密
php artisan env:decrypt --force

# 檢查解密後的檔案內容
cat .env
```

---

### 8.3 *環境變數載入問題*

```bash
# 清除配置快取
php artisan config:clear

# 重新快取配置
php artisan config:cache

# 檢查環境變數是否正確載入
php artisan config:show
```

---

## 9. **相關指令參考**（Related Commands)

| 指令                        | 說明                   | 使用場景         |
|-----------------------------|-----------------------|----------------|
| `php artisan env:encrypt`   | *加密* 環境變數檔案    | 部署前準備       |
| `php artisan env:decrypt`   | *解密* 環境變數檔案    | 部署時使用       |
| `php artisan key:generate`  | *生成* 應用程式金鑰    | 新專案設定       |
| `php artisan config:clear`  | *清除* 配置快取        | 環境變數更新後   |
| `php artisan config:cache`  | *快取* 配置設定        | 生產環境優化     |

---

## 10. **安全注意事項**（Security Considerations)

### 10.1 *金鑰保護*

- 🔒 **重要**：加密金鑰是保護環境變數的關鍵，`必須`妥善保管
- 🔒 **重要**：`不要`在版本控制系統中（Git、SVN）儲存加密金鑰
- 🔒 **重要**：`定期更換`加密金鑰，降低安全風險

---

### 10.2 *檔案權限*

- 🔒 **重要**：確保 `.env` 檔案權限設定正確（通常為 600）
- 🔒 **重要**：`限制`對環境檔案的存取權限
- 🔒 **重要**：在生產環境中使用適當的`檔案權限設定`

---

### 10.3 *部署安全*

- 🔒 **重要**：在部署過程中`安全傳輸`加密金鑰
- 🔒 **重要**：使用`安全的通訊管道`傳輸敏感資訊
- 🔒 **重要**：在部署完成後`清理`臨時檔案 