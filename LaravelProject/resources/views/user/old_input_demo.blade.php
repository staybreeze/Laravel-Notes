{{-- resources/views/user/old-input-demo.blade.php --}}
{{-- 示範如何用 old() 回填表單欄位 --}}
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>Old Input Demo</title>
</head>
<body>
    <h1>Old Input Demo</h1>
    <form method="POST" action="/user/old-input-demo">
        @csrf
        <label for="username">使用者名稱：</label>
        <input type="text" id="username" name="username" value="{{ old('username') }}">
        <br>
        <label for="email">Email：</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}">
        <br>
        <label for="password">密碼：</label>
        <input type="password" id="password" name="password">
        <br>
        <button type="submit">送出</button>
    </form>
    <hr>
    <div>
        <strong>old('username')：</strong> {{ old('username') }}<br>
        <strong>old('email')：</strong> {{ old('email') }}
    </div>
</body>
</html> 