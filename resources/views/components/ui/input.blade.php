{{-- Campo de texto com label opcional. Demais atributos (type, placeholder,
     name, value...) passam direto para o <input>. Um style extra do chamador
     sobrescreve o padrão (ex.: width:auto na barra de filtro). --}}
@props(['label' => null])

@php
    $isRetro = auth()->user()?->isRetro();
    $radius  = $isRetro ? 'var(--orbit-radius-sm)' : 'var(--orbit-radius)';
    $labelStyle = $isRetro
        ? 'font-size:9px; font-family:var(--orbit-font-mono); color:var(--orbit-fg-subtle); text-transform:uppercase; display:block; margin-bottom:4px;'
        : 'font-size:11px; color:var(--orbit-fg-subtle); text-transform:uppercase; letter-spacing:0.08em; display:block; margin-bottom:6px;';
    $fieldStyle = "width:100%; box-sizing:border-box; background:var(--orbit-bg-panel); border:var(--orbit-border-width) solid var(--orbit-border-strong); border-radius:{$radius}; color:var(--orbit-fg); padding:8px 10px; font-family:inherit; font-size:13px;";
@endphp

@if($label)<label style="{{ $labelStyle }}">{{ $label }}</label>@endif
<input {{ $attributes->merge(['style' => $fieldStyle]) }}>
