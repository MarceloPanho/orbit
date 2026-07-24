{{-- Select com label opcional. As <option> vão no slot.
     O slot `action` rende ao lado do label (ex.: atalho para gerenciar as opções). --}}
@props(['label' => null, 'action' => null])

@php
    $isRetro = auth()->user()?->isRetro();
    $radius  = $isRetro ? 'var(--orbit-radius-sm)' : 'var(--orbit-radius)';
    $labelStyle = $isRetro
        ? 'font-size:9px; font-family:var(--orbit-font-mono); color:var(--orbit-fg-subtle); text-transform:uppercase; display:block; margin-bottom:4px;'
        : 'font-size:11px; color:var(--orbit-fg-subtle); text-transform:uppercase; letter-spacing:0.08em; display:block; margin-bottom:6px;';
    $fieldStyle = "width:100%; box-sizing:border-box; background:var(--orbit-bg-panel); border:var(--orbit-border-width) solid var(--orbit-border-strong); border-radius:{$radius}; color:var(--orbit-fg); padding:8px 10px; font-family:inherit; font-size:13px;";
@endphp

@if($label && $action)
    <div style="display:flex; align-items:center; gap:6px; margin-bottom:{{ $isRetro ? '4px' : '6px' }};">
        <label style="{{ $labelStyle }} margin-bottom:0;">{{ $label }}</label>
        {{ $action }}
    </div>
@elseif($label)
    <label style="{{ $labelStyle }}">{{ $label }}</label>
@endif
<select {{ $attributes->merge(['style' => $fieldStyle]) }}>{{ $slot }}</select>
