{{-- Título de seção da navegação ("Módulos", "Sistema").
       divider: adiciona separação acima (linha no modern, respiro no retro). --}}
@props(['label', 'divider' => false])

@php $isRetro = auth()->user()?->isRetro(); @endphp

@if($isRetro)
    <div style="font-size:9px; color:var(--orbit-fg-subtle); padding:{{ $divider ? '12px 8px 4px' : '4px 8px' }}; white-space:nowrap; overflow:hidden;">── {{ \Illuminate\Support\Str::upper($label) }} ──────────────</div>
@else
    @if($divider)
        <div style="border-top:1px solid var(--orbit-border); margin:12px 8px 0;"></div>
    @endif
    <div style="font-size:10px; color:var(--orbit-fg-subtle); text-transform:uppercase; letter-spacing:0.08em; padding:{{ $divider ? '8px 8px 4px' : '4px 8px' }};">{{ $label }}</div>
@endif
