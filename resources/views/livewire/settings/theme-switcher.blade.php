{{-- Cards de preview: cada card mostra o visual do próprio tema,
     independente do tema ativo — por isso usam hex literais. --}}
<x-ui.card style="display:flex; gap:16px; flex-wrap:wrap;" title="Temas">

    {{-- Card MODERN --}}
    <button
        type="button"
        wire:click="setTheme('modern')"
        style="
            width:260px; text-align:left; cursor:pointer; padding:0; overflow:hidden;
            background:#0F0F0F;
            border:{{ $currentTheme === 'modern' ? '1.5px solid var(--orbit-accent)' : '0.5px solid var(--orbit-border)' }};
            border-radius:var(--orbit-radius-lg);
        "
    >
        <div style="padding:20px; font-family:'Inter', system-ui, sans-serif;">
            <div style="color:#E8E8E6; font-size:15px; font-weight:500; letter-spacing:-1px;">orbit</div>
            <div style="margin-top:12px; height:6px; width:64px; background:#6366F1; border-radius:3px;"></div>
            <div style="margin-top:8px; height:4px; width:120px; background:#2A2A2A; border-radius:2px;"></div>
            <div style="margin-top:4px; height:4px; width:96px; background:#2A2A2A; border-radius:2px;"></div>
        </div>
        <div style="padding:10px 20px; border-top:0.5px solid #2A2A2A; color:#C8C7C0; font-size:12px; font-family:'Inter', system-ui, sans-serif;">
            Modern — minimalista escuro
        </div>
    </button>

    {{-- Card RETRO --}}
    <button
        type="button"
        wire:click="setTheme('retro')"
        style="
            width:260px; text-align:left; cursor:pointer; padding:0; overflow:hidden; position:relative;
            background:#080C0F;
            border:{{ $currentTheme === 'retro' ? '1.5px solid var(--orbit-accent)' : '0.5px solid var(--orbit-border)' }};
            border-radius:var(--orbit-radius-lg);
        "
    >
        {{-- scanlines locais do preview --}}
        <div aria-hidden="true" style="position:absolute; inset:0; pointer-events:none; background:repeating-linear-gradient(to bottom, transparent 0px, transparent 2px, rgba(0,0,0,0.18) 2px, rgba(0,0,0,0.18) 4px);"></div>
        <div style="padding:20px; font-family:'Share Tech Mono', 'Courier New', monospace;">
            <div style="color:#00C8E8; font-size:14px; letter-spacing:2px;">ORBIT<span style="display:inline-block; width:6px; height:12px; background:#00C8E8; vertical-align:middle; margin-left:4px; animation:blink 1s step-end infinite;"></span></div>
            <div style="margin-top:12px; color:#C8DDE8; font-size:10px;">&gt; SISTEMA OPERACIONAL PESSOAL</div>
            <div style="margin-top:4px; color:#2A6A7A; font-size:10px;">└─ CARREGANDO ──┘</div>
        </div>
        <div style="padding:10px 20px; border-top:0.5px solid #1A3A50; color:#8AAABB; font-size:11px; font-family:'Share Tech Mono', 'Courier New', monospace;">
            Retro — terminal CRT
        </div>
    </button>

</x-ui.card>
