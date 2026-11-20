@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-ok-text']) }}>
        {{ $status }}
    </div>
@endif
