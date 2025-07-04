{{-- resources/views/components/button.blade.php --}}

<button
    {{ $attributes->class(['btn', 'btn-danger' => ($danger ?? false)])->merge(['type' => 'button']) }}
>
    {{ $slot }}
</button> 