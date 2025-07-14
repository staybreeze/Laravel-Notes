<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * FileManagerService
 *
 * 本 Service 專責檔案上傳、列表、下載、刪除，僅操作 Storage，不記錄資料表。
 * 適合「只需檔案本身、不需額外屬性/歸屬」的情境。
 * 若需檔案屬性、歸屬、查詢、管理，建議加上 Model/Repository。
 */
class FileManagerService
{
    /**
     * 上傳多個檔案到指定目錄，回傳所有檔案路徑
     * 僅將檔案存入 Storage，不記錄資料表。
     *
     * @param UploadedFile[] $files
     * @param string $dir
     * @param string $disk
     * @return array
     */
    public function uploadFiles(array $files, string $dir = 'uploads', string $disk = 'public'): array
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $file->store($dir, $disk);
        }
        return $paths;
    }

    /**
     * 取得指定目錄下所有檔案（不含子目錄）
     * 僅從 Storage 取得檔案清單，不查詢資料表。
     *
     * @param string $dir
     * @param string $disk
     * @return array
     */
    public function listFiles(string $dir = 'uploads', string $disk = 'public'): array
    {
        return Storage::disk($disk)->files($dir);
    }

    /**
     * 下載指定檔案
     * 僅從 Storage 下載檔案，不驗證歸屬或權限。
     *
     * @param string $path
     * @param string $disk
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadFile(string $path, string $disk = 'public')
    {
        return Storage::disk($disk)->download($path);
    }

    /**
     * 刪除指定檔案
     * 僅從 Storage 刪除檔案，不同步資料表。
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function deleteFile(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->delete($path);
    }
} 