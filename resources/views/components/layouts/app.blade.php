<!DOCTYPE html>
<html lang="pt-BR" class="{{ auth()->user()?->theme ?? 'modern' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orbit</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="background: var(--orbit-bg); color: var(--orbit-fg); font-family: var(--orbit-font-sans); margin:0; display:flex; flex-direction:column; height:100vh; overflow:hidden;">

    {{-- Scanlines: só tema retro --}}
    @if(auth()->user()?->isRetro())
        <div class="orbit-scanlines" aria-hidden="true"></div>
    @endif

    {{-- Topbar terminal: só tema retro --}}
    @if(auth()->user()?->isRetro())
        <div style="height:26px; flex-shrink:0; background:var(--orbit-bg-panel); display:flex; align-items:center; padding:0 16px; justify-content:space-between; border-bottom:0.5px solid var(--orbit-border);">
            <span style="font-size:9px; color:var(--orbit-fg-subtle)">ORBIT_OS v0.1.0</span>
            <span style="font-size:9px; color:var(--orbit-fg-subtle)">[ PERSONAL OPERATING SYSTEM ]</span>
            <span style="font-size:9px; color:var(--orbit-fg-subtle)" x-data x-text="new Date().toLocaleString('pt-BR', {weekday:'short', day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit'})"></span>
        </div>
    @endif

    {{-- Conteúdo principal --}}
    <main style="flex:1; display:flex; overflow:hidden;">
        {{ $slot }}
    </main>

    {{-- Rodapé de status: só tema retro --}}
    @if(auth()->user()?->isRetro())
        <div style="height:18px; flex-shrink:0; background:var(--orbit-bg-panel); display:flex; align-items:center; padding:0 16px; gap:16px; border-top:0.5px solid var(--orbit-border);">
            <span style="font-size:8px; color:var(--orbit-fg-subtle)">ORBIT_OS v0.1.0</span>
            <span style="font-size:8px; color:var(--orbit-border-strong)">│</span>
            <span style="font-size:8px; color:var(--orbit-fg-subtle)">DISK:LOCAL</span>
            <span style="font-size:8px; color:var(--orbit-border-strong)">│</span>
            <span style="font-size:8px; color:var(--orbit-fg-subtle)">NET:OFF</span>
            <span style="font-size:8px; color:var(--orbit-border-strong)">│</span>
            <span style="font-size:8px; color:var(--orbit-fg-subtle)">PRIV:MAX</span>
            <span style="margin-left:auto" class="orbit-cursor"></span>
        </div>
    @endif

    @livewireScripts
</body>
</html>
