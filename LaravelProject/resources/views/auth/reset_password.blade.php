@extends('welcome')

@section('content')
<div class="container">
    <h2>重設密碼</h2>
    {{-- 顯示狀態訊息 --}}
    @if (session('status'))
        <div style="color: green;">{{ session('status') }}</div>
    @endif
    {{-- 顯示錯誤訊息 --}}
    @if ($errors->any())
        <div style="color: red;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    {{-- 密碼重設表單 --}}
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        {{-- 隱藏欄位：token --}}
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label for="email">電子郵件：</label>
            <input type="email" name="email" id="email" value="{{ $email ?? old('email') }}" required autofocus>
        </div>
        <div>
            <label for="password">新密碼：</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div>
            <label for="password_confirmation">確認新密碼：</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required>
        </div>
        <button type="submit">重設密碼</button>
    </form>
</div>
@endsection 