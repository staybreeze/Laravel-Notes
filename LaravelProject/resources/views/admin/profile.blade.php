{{--
    resources/views/admin/profile.blade.php
    巢狀視圖範例，顯示 Admin Profile: {{ $name }}
    並顯示全域共用變數 key，以及 View Composer 注入的 count 變數。
--}}
<html>
    <body>
        <h1>Admin Profile: {{ $name }}</h1>
        <p>全域變數 key: {{ $key ?? '（未設定）' }}</p>
        <p>用戶總數（由 View Composer 注入）: {{ $count ?? '（未注入）' }}</p>
    </body>
</html> 