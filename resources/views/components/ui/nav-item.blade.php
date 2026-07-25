{{-- Item de navegação (link) com estado ativo.
       No modern mostra o `icon`; no retro mostra o marcador `>` quando ativo.
       badge=true acende um ponto no accent à direita (ex.: atualização disponível). --}}
@props(['href' => '#', 'icon' => null, 'active' => false, 'badge' => false])

@php
    $isRetro = auth()->user()?->isRetro();
    $color   = $active ? 'var(--orbit-accent)' : 'var(--orbit-fg-muted)';

    if ($isRetro) {
        $style = "display:flex; align-items:center; gap:8px; text-decoration:none; font-size:12px; padding:5px 8px; color:{$color};";
    } else {
        $bg    = $active ? 'color-mix(in srgb, var(--orbit-accent) 10%, transparent)' : 'transparent';
        $style = "display:flex; align-items:center; gap:8px; text-decoration:none; font-size:14px; padding:8px 8px; border-radius:6px; color:{$color}; background:{$bg};";
    }
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['style' => $style]) }}>
    @if($isRetro)
        <span>{{ $active ? '>' : ' ' }}</span>
    @elseif($icon)
        <span aria-hidden="true">{{ $icon }}</span>
    @endif
    <span>{{ $slot }}</span>
    @if($badge)
        <span aria-label="atualização disponível" title="Atualização disponível"
              style="margin-left:auto; width:6px; height:6px; border-radius:50%; background:var(--orbit-accent); flex-shrink:0;"></span>
    @endif
</a>
