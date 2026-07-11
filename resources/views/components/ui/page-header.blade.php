@props(['title', 'subtitle' => null])

@php $isRetro = auth()->user()?->isRetro(); @endphp

<div style="display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap;">
    <div>
        <h1 style="font-size:22px; font-weight:500; color:var(--orbit-fg); margin:0;">{{ $isRetro ? '[ '.\Illuminate\Support\Str::upper($title).' ]' : $title }}</h1>
        @if($subtitle)
            <p style="font-size:13px; color:var(--orbit-fg-subtle); margin:4px 0 0;">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div style="display:flex; align-items:center; gap:8px;">{{ $actions }}</div>
    @endisset
</div>
