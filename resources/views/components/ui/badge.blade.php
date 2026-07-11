{{-- Pill/etiqueta. tone: neutral | accent | success | danger --}}
@props(['tone' => 'neutral'])

@php
    $isRetro = auth()->user()?->isRetro();
    $radius  = $isRetro ? 'var(--orbit-radius-sm)' : '999px';

    $tones = [
        'neutral' => 'background:var(--orbit-bg-panel); color:var(--orbit-fg-muted); border-color:var(--orbit-border-strong);',
        'accent'  => 'background:color-mix(in srgb, var(--orbit-accent) 14%, transparent); color:var(--orbit-accent); border-color:color-mix(in srgb, var(--orbit-accent) 45%, transparent);',
        'success' => 'background:color-mix(in srgb, var(--orbit-success) 14%, transparent); color:var(--orbit-success); border-color:color-mix(in srgb, var(--orbit-success) 45%, transparent);',
        'danger'  => 'background:color-mix(in srgb, var(--orbit-danger) 14%, transparent); color:var(--orbit-danger); border-color:color-mix(in srgb, var(--orbit-danger) 45%, transparent);',
    ];
    $toneStyle = $tones[$tone] ?? $tones['neutral'];

    $base = "display:inline-block; font-size:11px; padding:2px 8px; border:var(--orbit-border-width) solid; border-radius:{$radius};"
        . ($isRetro ? ' font-family:var(--orbit-font-mono);' : '');
@endphp

<span {{ $attributes->merge(['style' => $base.$toneStyle]) }}>{{ $slot }}</span>
