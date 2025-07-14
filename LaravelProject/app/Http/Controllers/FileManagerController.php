<?php

namespace App\Http\Controllers;

use App\Services\FileManagerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class FileManagerController extends Controller
{
    protected FileManagerService $service;

    public function __construct(FileManagerService $service)
    {
        $this->service = $service;
    }

    /**
     * 多檔案上傳
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240', // 單檔最大 10MB
        ]);
        $paths = $this->service->uploadFiles($request->file('files'));
        return response()->json(['paths' => $paths]);
    }

    /**
     * 列出所有檔案
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $files = $this->service->listFiles();
        return response()->json(['files' => $files]);
    }

    /**
     * 下載檔案
     * @param string $path
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(string $path)
    {
        return $this->service->downloadFile($path);
    }

    /**
     * 刪除檔案
     * @param string $path
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $path)
    {
        $result = $this->service->deleteFile($path);
        return response()->json(['deleted' => $result]);
    }
} 