{{-- resources/views/components/alert.blade.php --}}

<div {{ $attributes->class(['alert', 'alert-danger']) }}>
    @isset($title)
        <span class="alert-title">{{ $title }}</span>
    @endisset
    {{ $slot }} {{-- 主內容 slot --}}
</div> 