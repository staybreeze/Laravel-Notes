@if (session('message'))
    <div>{{ session('message') }}</div>
@endif

<p>請驗證您的電子郵件，否則無法使用完整功能。</p>
<form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit">重新寄送驗證信</button>
</form> 