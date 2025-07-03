<?php

namespace App\Http\Controllers;

/**
 * ProvisionServer（單一動作控制器，Single Action Controller）
 *
 * [設計理念]
 * - 當某個控制器只需處理一個複雜動作時，可用單一 __invoke 方法設計，讓程式結構更聚焦、易維護。
 * - 適合專責處理單一業務邏輯（如伺服器佈建、批次任務、Webhook 處理等）。
 *
 * [Artisan 指令快速產生]
 *   php artisan make:controller ProvisionServer --invokable
 *
 * [基本範例]
 *   public function __invoke() {
 *       // 執行單一動作邏輯
 *   }
 *
 * [路由綁定單一動作控制器]
 *   use App\Http\Controllers\ProvisionServer;
 *   Route::post('/server', ProvisionServer::class);
 *   // 註冊時只需指定控制器類別，不需指定方法
 *
 * [補充]
 * - 單一動作控制器可提升複雜業務邏輯的可讀性與可測試性。
 * - 建議於檔案頂部加上用途註解，方便團隊維護。
 */
class ProvisionServer extends Controller
{
    /**
     * Provision a new web server.
     * 佈建新伺服器的單一動作控制器
     */
    public function __invoke()
    {
        // ... 這裡撰寫伺服器佈建邏輯 ...
    }
} 