@props(['align' => 'left'])

@php
    $isRetro = auth()->user()?->isRetro();
    $style = "text-align:{$align}; padding:10px 14px; font-size:".($isRetro ? '9px' : '11px').';'
        . ' color:var(--orbit-fg-subtle); text-transform:uppercase; letter-spacing:0.06em; font-weight:500;'
        . ' border-bottom:var(--orbit-border-width) solid var(--orbit-border-strong); white-space:nowrap;'
        . ($isRetro ? ' font-family:var(--orbit-font-mono);' : '');
@endphp

<th {{ $attributes->merge(['style' => $style]) }}>{{ $slot }}</th>
