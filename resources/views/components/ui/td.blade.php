{{-- Célula do corpo.
       align: left | right | center
       tone:  default | muted | subtle | strong | danger | success
       nowrap: não quebra linha    mono: força fonte monoespaçada --}}
@props(['align' => 'left', 'tone' => 'default', 'nowrap' => false, 'mono' => false])

@php
    $isRetro = auth()->user()?->isRetro();
    $tones = [
        'default' => 'var(--orbit-fg-muted)',
        'muted'   => 'var(--orbit-fg-muted)',
        'subtle'  => 'var(--orbit-fg-subtle)',
        'strong'  => 'var(--orbit-fg)',
        'danger'  => 'var(--orbit-danger)',
        'success' => 'var(--orbit-success)',
    ];
    $color = $tones[$tone] ?? $tones['default'];

    $style = "padding:11px 14px; font-size:13px; border-bottom:var(--orbit-border-width) solid var(--orbit-border); text-align:{$align}; color:{$color};";
    if ($nowrap) { $style .= ' white-space:nowrap;'; }
    if ($mono || $isRetro) { $style .= ' font-family:var(--orbit-font-mono);'; }
    if (! $isRetro && $align === 'right') { $style .= ' font-variant-numeric:tabular-nums;'; }
@endphp

<td {{ $attributes->merge(['style' => $style]) }}>{{ $slot }}</td>
