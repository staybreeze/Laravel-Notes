{{-- =========================================================================
[Blade 資料顯示範例]
本範例展示如何在 Blade 模板安全顯示資料與執行 PHP 運算
========================================================================== --}}
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>Blade 筆記與元件範例</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h1>Blade 筆記與元件範例</h1>
    <p>歡迎，{{ $name }}！這是 Blade 筆記與元件教學頁面。</p>

    {{-- Alert 元件範例 --}}
    <x-alert class="mb-3">
        <x-slot:title>
            範例警告
        </x-slot>
        這是一個 Blade 元件 slot 實作範例。
    </x-alert>

    {{-- 更多 Blade 筆記與元件教學可依需求擴充 --}}
</body>
</html> 