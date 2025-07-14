<?php

declare(strict_types=1); // 強制型別檢查（PHP 7+ 建議）

namespace Tests\Feature; // 命名空間，代表這是 Feature 測試

use Illuminate\Http\UploadedFile; // 匯入 Laravel 上傳檔案物件
use Illuminate\Support\Facades\Storage; // 匯入 Storage Facade
use Tests\TestCase; // 匯入 Laravel 測試基底類別

class FileManagerTest extends TestCase // 定義 FileManagerTest 測試類別，繼承 TestCase
{
    /** @test */
    public function can_upload_multiple_files_and_list_them()
    {
        Storage::fake('public');
        // ↑ 建立假的 public disk，所有 Storage 操作都只在記憶體，不會真的寫入硬碟

        $files = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
        ];
        // ↑ 產生兩個假的圖片檔案，模擬多檔案上傳

        // 上傳多檔
        $response = $this->postJson('/files/upload', [
            'files' => $files,
        ]);
        // ↑ 發送 POST 請求到 /files/upload，帶入多個檔案，模擬 API 上傳

        $response->assertStatus(200)
            ->assertJsonCount(2, 'paths');
        // ↑ 斷言回應狀態 200，且回傳的 paths 陣列有 2 筆

        // 檔案應存在
        foreach ($response['paths'] as $path) {
            Storage::disk('public')->assertExists($path);
            // ↑ 逐一檢查每個回傳路徑的檔案都真的存在於 public disk
        }

        // 檢查列表
        $list = $this->getJson('/files/list');
        // ↑ 發送 GET 請求到 /files/list，取得檔案列表

        $list->assertStatus(200)
            ->assertJsonStructure(['files']);
        // ↑ 斷言回應狀態 200，且回傳結構有 files 欄位

        $this->assertGreaterThanOrEqual(2, count($list['files']));
        // ↑ 斷言 files 陣列至少有 2 筆（剛剛上傳的檔案）
    }

    /** @test */
    public function can_download_and_delete_file()
    {
        Storage::fake('public');
        // ↑ 建立假的 public disk，所有 Storage 操作都只在記憶體，不會真的寫入硬碟

        $file = UploadedFile::fake()->image('download.jpg');
        // ↑ 產生一個假的圖片檔案

        $path = $file->store('uploads', 'public');
        // ↑ 將假檔案存到 uploads 目錄，disk 為 public，取得實際路徑

        // 下載檔案
        $download = $this->get('/files/download/'.urlencode($path));
        // ↑ 發送 GET 請求到 /files/download/{路徑}，模擬下載檔案

        $download->assertStatus(200);
        // ↑ 斷言下載回應狀態 200

        // 刪除檔案
        $delete = $this->deleteJson('/files/delete/'.urlencode($path));
        // ↑ 發送 DELETE 請求到 /files/delete/{路徑}，模擬刪除檔案

        $delete->assertStatus(200)
            ->assertJson(['deleted' => true]);
        // ↑ 斷言刪除回應狀態 200，且回傳 deleted: true

        Storage::disk('public')->assertMissing($path);
        // ↑ 斷言該檔案已不存在於 public disk
    }
} 