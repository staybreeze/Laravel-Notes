<?php

/*
|-------------------------------------------------------------------------------
| CORS（跨來源資源共享）說明與設定重點
|-------------------------------------------------------------------------------
| 1. 什麼是 CORS？
|    CORS（跨來源資源共享）是一種瀏覽器安全機制，允許瀏覽器從一個網域的網頁請求另一個網域的資源。
|    例如：前端在 http://localhost:3000，API 在 http://api.example.com，就會遇到 CORS 問題。
|
| 2. Laravel 如何處理 CORS？
|    Laravel 內建 HandleCors middleware（位置：vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php），
|    並已自動註冊在 app/Http/Kernel.php 的 $middleware 屬性（全域 middleware），不需手動加入。
|    這個 middleware 會自動處理所有 OPTIONS 請求（CORS 預檢請求），自動加上正確的 CORS headers。
|
| 3. 什麼是 OPTIONS 請求？
|    OPTIONS 是一種 HTTP 方法，瀏覽器在跨網域請求（如 PUT、PATCH、DELETE 或帶自訂 header）時，
|    會自動先發送一個 OPTIONS 請求（預檢請求 Preflight Request），詢問伺服器允許哪些來源、方法、header。
|    伺服器（Laravel）收到後會回應允許的資訊，瀏覽器才會繼續發送真正的 API 請求。
|    範例：
|      OPTIONS /api/user/1 HTTP/1.1
|      Origin: http://localhost:3000
|      Access-Control-Request-Method: PUT
|      Access-Control-Request-Headers: Content-Type
|    伺服器回應：
|      HTTP/1.1 204 No Content
|      Access-Control-Allow-Origin: http://localhost:3000
|      Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
|      Access-Control-Allow-Headers: Content-Type
|
| 4. 如何自訂 CORS 設定？
|    a. 發佈 CORS 設定檔：
|       執行 php artisan vendor:publish --tag=laravel-cors
|       會在 config 目錄下產生 cors.php 設定檔。
|       （舊版用 php artisan config:publish cors，新版請用 vendor:publish）
|    b. 編輯本檔案即可自訂允許來源、方法、headers 等。
|
| 這些設定讓團隊能完整理解 CORS 概念、Laravel 處理方式、HandleCors 位置與 OPTIONS 請求原理。
|-------------------------------------------------------------------------------
*/

return [
    // -----------------------------------------------------------------------------
    // CORS 設定（跨來源資源共享）
    // -----------------------------------------------------------------------------
    // 這裡可自訂允許的來源、方法、headers 等。
    // Laravel 會自動處理 OPTIONS 預檢請求與回應 CORS headers。
    // 若需允許前端跨網域存取 API，請設定 allowed_origins。
    // supports_credentials 設 true 可允許帶 cookie。
    // -----------------------------------------------------------------------------

    // 哪些路徑要套用 CORS 設定，通常 API 路徑即可
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // 允許哪些 HTTP 方法（* 代表全部）
    'allowed_methods' => ['*'],

    // 允許哪些來源（* 代表全部，可改成 ['http://localhost:3000', ...]）
    'allowed_origins' => ['*'],

    // 允許符合哪些正規表達式的來源
    'allowed_origins_patterns' => [],

    // 允許哪些 headers（* 代表全部）
    'allowed_headers' => ['*'],

    // 回應時會額外暴露哪些 headers 給前端
    'exposed_headers' => [],

    // 預檢請求的快取秒數（0 代表不快取）
    'max_age' => 0,

    // 是否允許帶 cookie（如需跨網域帶認證資訊要設 true）
    'supports_credentials' => false,
]; 