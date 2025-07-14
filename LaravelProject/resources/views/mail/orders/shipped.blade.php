<x-mail::message>
# 訂單已出貨

您的訂單已經寄出！

{{-- 按鈕元件，點擊可查看訂單 --}}
<x-mail::button :url="$url" color="success">
查看訂單
</x-mail::button>

{{-- 突顯區塊，提醒聯絡客服 --}}
<x-mail::panel>
如有任何問題，請聯絡客服。
</x-mail::panel>

感謝您的支持！<br>
{{ config('app.name') }}
</x-mail::message> 