@extends('welcome')

@section('content')
<div class="container">
    <h2>忘記密碼</h2>
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
    {{-- 忘記密碼表單 --}}
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div>
            <label for="email">電子郵件：</label>
            <input type="email" name="email" id="email" required autofocus>
        </div>
        <button type="submit">寄送密碼重設連結</button>
    </form>
</div>
@endsection 