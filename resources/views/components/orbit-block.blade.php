@props(['title' => ''])

@php $isRetro = auth()->user()?->isRetro(); @endphp

<div style="
    background: var(--orbit-bg-surface);
    border: var(--orbit-border-width) solid var(--orbit-border);
    border-radius: var(--orbit-radius);
    overflow: hidden;
">
    @if($isRetro)
        <div style="background:var(--orbit-bg-panel); padding:4px 12px; border-bottom:0.5px solid var(--orbit-border);">
            <span class="orbit-block-title" style="font-size:9px; color:var(--orbit-fg-subtle)">{{ \Illuminate\Support\Str::upper($title) }}</span>
        </div>
    @else
        <div style="padding:12px 16px; border-bottom:0.5px solid var(--orbit-border);">
            <h3 style="font-size:13px; font-weight:500; color:var(--orbit-fg-muted); margin:0;">{{ $title }}</h3>
        </div>
    @endif

    <div style="padding: {{ $isRetro ? '12px' : '16px' }};">
        {{ $slot }}
    </div>

    @if($isRetro)
        <div style="padding:2px 12px; border-top:0.5px solid var(--orbit-border);">
            <span class="orbit-block-footer" style="font-size:8px; color:var(--orbit-fg-subtle)"></span>
        </div>
    @endif
</div>
