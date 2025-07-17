@extends('welcome')

@section('content')
<div class="container">
    <h2>密碼雜湊與驗證範例</h2>

    {{-- 1. 更新密碼（會自動雜湊） --}}
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <div>
            <label for="newPassword">新密碼：</label>
            <input type="password" name="newPassword" id="newPassword" required>
        </div>
        <button type="submit">更新密碼</button>
    </form>
    <hr>

    {{-- 2. 驗證明文密碼是否正確 --}}
    <form method="POST" action="{{ route('password.check') }}">
        @csrf
        <div>
            <label for="plain">驗證密碼：</label>
            <input type="password" name="plain" id="plain" required>
        </div>
        <button type="submit">驗證密碼</button>
    </form>
    <hr>

    {{-- 3. 檢查密碼是否需要重新雜湊 --}}
    <form method="POST" action="{{ route('password.needsRehash') }}">
        @csrf
        <button type="submit">檢查密碼是否需重新雜湊</button>
    </form>
</div>
@endsection 