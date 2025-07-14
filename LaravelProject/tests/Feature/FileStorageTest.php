<?php

declare(strict_types=1); // 強制型別檢查（PHP 7+ 建議）

namespace Tests\Feature; // 命名空間，代表這是 Feature 測試

use Illuminate\Support\Facades\Storage; // 匯入 Storage Facade
use Illuminate\Foundation\Testing\RefreshDatabase; // 匯入資料庫重設 trait（本測試未用到 DB，但習慣性加上）
use Tests\TestCase; // 匯入 Laravel 測試基底類別

class FileStorageTest extends TestCase // 定義 FileStorageTest 測試類別，繼承 TestCase
{
    use RefreshDatabase; // 使用資料庫重設 trait（本測試未用到 DB，可省略）

    /** @test */
    public function can_write_and_read_local_disk_file()
    {
        // 寫入檔案
        Storage::disk('local')->put('test.txt', '內容測試');
        // ↑ 將字串內容寫入 local disk 的 test.txt

        // 讀取檔案
        $content = Storage::disk('local')->get('test.txt');
        // ↑ 讀取剛剛寫入的檔案內容

        $this->assertEquals('內容測試', $content);
        // ↑ 斷言讀到的內容正確

        // 刪除檔案
        Storage::disk('local')->delete('test.txt');
        // ↑ 刪除剛剛寫入的檔案

        $this->assertFalse(Storage::disk('local')->exists('test.txt'));
        // ↑ 斷言檔案已不存在
    }

    /** @test */
    public function can_write_and_read_public_disk_file()
    {
        Storage::disk('public')->put('public-test.txt', '公開內容');
        // ↑ 將字串內容寫入 public disk 的 public-test.txt

        $content = Storage::disk('public')->get('public-test.txt');
        // ↑ 讀取剛剛寫入的檔案內容

        $this->assertEquals('公開內容', $content);
        // ↑ 斷言讀到的內容正確

        Storage::disk('public')->delete('public-test.txt');
        // ↑ 刪除剛剛寫入的檔案

        $this->assertFalse(Storage::disk('public')->exists('public-test.txt'));
        // ↑ 斷言檔案已不存在
    }

    /** @test */
    public function can_generate_public_url_for_public_disk_file()
    {
        Storage::disk('public')->put('url-test.txt', '網址測試');
        // ↑ 將字串內容寫入 public disk 的 url-test.txt

        $url = asset('storage/url-test.txt');
        // ↑ 產生 public disk 檔案的公開網址

        $this->assertStringContainsString('/storage/url-test.txt', $url);
        // ↑ 斷言網址字串包含正確路徑

        Storage::disk('public')->delete('url-test.txt');
        // ↑ 刪除剛剛寫入的檔案
    }

    /** @test */
    public function can_build_on_demand_disk_and_write_file()
    {
        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/build-test'),
        ]);
        // ↑ 動態建立一個本地磁碟，根目錄為 storage/app/build-test

        $disk->put('dynamic.txt', '動態磁碟內容');
        // ↑ 將內容寫入動態磁碟的 dynamic.txt

        $this->assertEquals('動態磁碟內容', $disk->get('dynamic.txt'));
        // ↑ 斷言讀到的內容正確

        $disk->delete('dynamic.txt');
        // ↑ 刪除剛剛寫入的檔案
    }

    /** @test */
    public function s3_disk_put_and_get_can_be_mocked_or_skipped()
    {
        // 若有設定 s3，可測試 Storage::disk('s3')->put/get
        // 若無，建議 mock 或 skip
        $this->markTestSkipped('S3 測試需設定 S3 驅動與憑證');
        // ↑ 跳過 S3 測試（因為沒設定 S3 驅動與憑證）
    }
} 