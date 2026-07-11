{{-- Cartão de indicador (rótulo + valor + ícone). Substitui o home.summary-card.
     No retro o rótulo vira [MAIÚSCULO] e o ícone é omitido. --}}
@props(['label', 'value', 'icon' => null])

@php $isRetro = auth()->user()?->isRetro(); @endphp

<div style="
    flex:1;
    min-width:180px;
    background: var(--orbit-bg-surface);
    border: var(--orbit-border-width) solid var(--orbit-border);
    border-radius: {{ $isRetro ? 'var(--orbit-radius)' : 'var(--orbit-radius-lg)' }};
    padding:16px;
">
    @if($isRetro)
        <div style="font-size:9px; font-family:var(--orbit-font-mono); color:var(--orbit-fg-subtle);">[{{ \Illuminate\Support\Str::upper($label) }}]</div>
        <div style="font-size:16px; font-family:var(--orbit-font-mono); color:var(--orbit-fg); margin-top:8px;">{{ $value }}</div>
    @else
        <div style="display:flex; align-items:center; gap:6px;">
            @if($icon)
                <span style="font-size:16px; color:var(--orbit-fg-subtle);">{{ $icon }}</span>
            @endif
            <span style="font-size:11px; color:var(--orbit-fg-subtle); text-transform:uppercase; letter-spacing:0.08em;">{{ $label }}</span>
        </div>
        <div style="font-size:20px; font-weight:500; color:var(--orbit-fg); margin-top:8px;">{{ $value }}</div>
    @endif
</div>
