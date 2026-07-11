{{-- Card com cabeçalho opcional (título + ações) e corpo.
     Substitui o antigo orbit-block; mantém as classes orbit-block-title/footer
     que o themes.css usa para o frame terminal do tema retro.
     flush: remove o padding do corpo (ex.: quando o corpo é uma tabela). --}}
@props(['title' => null, 'flush' => false])

@php $isRetro = auth()->user()?->isRetro(); @endphp

<div style="background:var(--orbit-bg-surface); border:var(--orbit-border-width) solid var(--orbit-border); border-radius:var(--orbit-radius); overflow:hidden;">

    @if($title !== null || isset($actions))
        @if($isRetro)
            <div style="background:var(--orbit-bg-panel); padding:4px 12px; border-bottom:0.5px solid var(--orbit-border); display:flex; align-items:center; gap:12px;">
                <span class="orbit-block-title" style="font-size:9px; color:var(--orbit-fg-subtle)">{{ \Illuminate\Support\Str::upper($title ?? '') }}</span>
                @isset($actions)<span style="margin-left:auto; display:flex; align-items:center; gap:8px;">{{ $actions }}</span>@endisset
            </div>
        @else
            <div style="padding:12px 16px; border-bottom:0.5px solid var(--orbit-border); display:flex; align-items:center; gap:12px;">
                @if($title !== null)<h3 style="font-size:13px; font-weight:500; color:var(--orbit-fg-muted); margin:0;">{{ $title }}</h3>@endif
                @isset($actions)<div style="margin-left:auto; display:flex; align-items:center; gap:8px;">{{ $actions }}</div>@endisset
            </div>
        @endif
    @endif

    <div style="{{ $flush ? '' : 'padding:'.($isRetro ? '12px' : '16px').';' }}">{{ $slot }}</div>

    @if($isRetro)
        <div style="padding:2px 12px; border-top:0.5px solid var(--orbit-border);">
            <span class="orbit-block-footer" style="font-size:8px; color:var(--orbit-fg-subtle)"></span>
        </div>
    @endif
</div>
