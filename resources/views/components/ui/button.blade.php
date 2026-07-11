{{-- variant: primary (sólido) | ghost (tint de accent) --}}
@props(['variant' => 'primary', 'icon' => null, 'type' => 'button'])

@php
    $isRetro = auth()->user()?->isRetro();
    $radius  = $isRetro ? 'var(--orbit-radius-sm)' : 'var(--orbit-radius)';

    $base = "display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-family:inherit; font-size:13px; border-radius:{$radius}; padding:9px 16px;"
        . ($isRetro ? ' text-transform:uppercase; font-family:var(--orbit-font-mono);' : ' font-weight:500;');

    $variants = [
        'primary' => 'background:var(--orbit-accent); color:var(--orbit-bg); border:var(--orbit-border-width) solid transparent;',
        'ghost'   => 'background:color-mix(in srgb, var(--orbit-accent) 14%, transparent); color:var(--orbit-accent); border:var(--orbit-border-width) solid color-mix(in srgb, var(--orbit-accent) 45%, transparent);',
    ];
    $variantStyle = $variants[$variant] ?? $variants['primary'];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['style' => $base.$variantStyle]) }}>
    @if($icon)<span aria-hidden="true">{{ $icon }}</span>@endif
    <span>{{ $slot }}</span>
</button>
