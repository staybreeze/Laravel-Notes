@if (session('message'))
    <div>{{ session('message') }}</div>
@endif

<p>你的機密內容：</p>
<div>{{ $secret }}</div> 