@props(['active' => 'dashboard'])

@php
    $isRetro = auth()->user()?->isRetro();

    $modules = [
        'financas' => ['label' => 'Finanças', 'children' => ['Visão geral', 'Transações', 'Orçamentos', 'Metas']],
        'agenda'   => ['label' => 'Agenda',   'children' => ['Hoje', 'Semana', 'Eventos', 'Tarefas']],
        'notas'    => ['label' => 'Notas',    'children' => ['Todas as notas', 'Por tag', 'Favoritas']],
        'habitos'  => ['label' => 'Hábitos',  'children' => ['Hoje', 'Histórico', 'Metas']],
    ];
@endphp

<aside style="width:220px; flex-shrink:0; height:100%; background:var(--orbit-bg-panel); border-right:0.5px solid var(--orbit-border); display:flex; flex-direction:column; overflow:hidden;">

    {{-- Logo --}}
    <a href="{{ route('home') }}" style="height:48px; flex-shrink:0; display:flex; align-items:center; gap:8px; padding:0 16px; text-decoration:none; border-bottom:0.5px solid var(--orbit-border);">
        @if($isRetro)
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="10" stroke="var(--orbit-accent)" stroke-width="1.5"/>
                <circle cx="12" cy="12" r="6" stroke="var(--orbit-accent)" stroke-width="0.5" stroke-dasharray="2 2"/>
                <path d="M12 10.5v3M10.5 12h3" stroke="var(--orbit-accent)" stroke-width="1"/>
                <rect x="19" y="5" width="3" height="3" fill="var(--orbit-accent)"/>
            </svg>
            <span style="color:var(--orbit-fg); font-size:13px; letter-spacing:2px;">ORBIT</span>
        @else
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="10" stroke="var(--orbit-accent)" stroke-width="1.5"/>
                <circle cx="12" cy="12" r="6" stroke="var(--orbit-fg-subtle)" stroke-width="0.5" stroke-dasharray="2 2"/>
                <circle cx="12" cy="12" r="1.5" fill="var(--orbit-fg)"/>
                <circle cx="20.5" cy="6.5" r="1.5" fill="var(--orbit-accent)"/>
            </svg>
            <span style="color:var(--orbit-fg); font-size:15px; font-weight:500; letter-spacing:-1px;">orbit</span>
        @endif
    </a>

    <nav style="flex:1; overflow:hidden; padding:12px 8px;" x-data="{ open: 'financas' }">

        {{-- Seção: módulos --}}
        @if($isRetro)
            <div style="font-size:9px; color:var(--orbit-fg-subtle); padding:4px 8px; white-space:nowrap; overflow:hidden;">── MODULOS ──────────────</div>
        @else
            <div style="font-size:10px; color:var(--orbit-fg-subtle); text-transform:uppercase; letter-spacing:0.08em; padding:4px 8px;">Módulos</div>
        @endif

        {{-- Dashboard --}}
        <a href="{{ route('home') }}" style="
            display:flex; align-items:center; gap:8px; text-decoration:none; margin-top:4px;
            @if($isRetro)
                font-size:12px; padding:5px 8px;
                color: {{ $active === 'dashboard' ? 'var(--orbit-accent)' : 'var(--orbit-fg-muted)' }};
            @else
                font-size:14px; padding:8px 8px; border-radius:6px;
                color: {{ $active === 'dashboard' ? 'var(--orbit-accent)' : 'var(--orbit-fg-muted)' }};
                background: {{ $active === 'dashboard' ? 'color-mix(in srgb, var(--orbit-accent) 10%, transparent)' : 'transparent' }};
            @endif
        ">
            @if($isRetro)
                <span>{{ $active === 'dashboard' ? '>' : ' ' }}</span><span>Dashboard</span>
            @else
                <span aria-hidden="true">◎</span><span>Dashboard</span>
            @endif
        </a>

        {{-- Módulos expansíveis (accordion: uma aba aberta por vez) --}}
        @foreach($modules as $key => $module)
            <button
                type="button"
                @click="open = open === '{{ $key }}' ? null : '{{ $key }}'"
                style="
                    width:100%; display:flex; align-items:center; gap:8px; background:none; border:none; cursor:pointer; text-align:left; margin-top:2px;
                    @if($isRetro)
                        font-size:12px; padding:5px 8px; color:var(--orbit-fg-muted); font-family:var(--orbit-font-mono);
                    @else
                        font-size:14px; padding:8px 8px; border-radius:6px; color:var(--orbit-fg-muted); font-family:var(--orbit-font-sans);
                    @endif
                "
            >
                <span style="font-size:9px;" x-text="open === '{{ $key }}' ? '▼' : '▶'">▶</span>
                <span>{{ $module['label'] }}</span>
            </button>

            <div x-show="open === '{{ $key }}'" x-transition x-cloak>
                @foreach($module['children'] as $child)
                    <a href="#" style="
                        display:block; text-decoration:none;
                        @if($isRetro)
                            font-size:11px; padding:3px 8px 3px 20px; color:var(--orbit-fg-subtle);
                        @else
                            font-size:13px; padding:6px 8px 6px 28px; color:var(--orbit-fg-subtle);
                        @endif
                    " onmouseover="this.style.color='var(--orbit-fg)'" onmouseout="this.style.color='var(--orbit-fg-subtle)'">
                        @if($isRetro)│ @endif{{ $child }}
                    </a>
                @endforeach
            </div>
        @endforeach

        {{-- Seção: sistema --}}
        @if($isRetro)
            <div style="font-size:9px; color:var(--orbit-fg-subtle); padding:12px 8px 4px; white-space:nowrap; overflow:hidden;">── SISTEMA ──────────────</div>
        @else
            <div style="border-top:1px solid var(--orbit-border); margin:12px 8px 0;"></div>
            <div style="font-size:10px; color:var(--orbit-fg-subtle); text-transform:uppercase; letter-spacing:0.08em; padding:8px 8px 4px;">Sistema</div>
        @endif

        {{-- Configurações --}}
        <a href="{{ route('settings') }}" style="
            display:flex; align-items:center; gap:8px; text-decoration:none; margin-top:4px;
            @if($isRetro)
                font-size:12px; padding:5px 8px;
                color: {{ $active === 'settings' ? 'var(--orbit-accent)' : 'var(--orbit-fg-muted)' }};
            @else
                font-size:14px; padding:8px 8px; border-radius:6px;
                color: {{ $active === 'settings' ? 'var(--orbit-accent)' : 'var(--orbit-fg-muted)' }};
                background: {{ $active === 'settings' ? 'color-mix(in srgb, var(--orbit-accent) 10%, transparent)' : 'transparent' }};
            @endif
        ">
            @if($isRetro)
                <span>{{ $active === 'settings' ? '>' : ' ' }}</span><span>Configurações</span>
            @else
                <span aria-hidden="true">⚙</span><span>Configurações</span>
            @endif
        </a>

        {{-- Ajuda --}}
        <a href="#" style="
            display:flex; align-items:center; gap:8px; text-decoration:none; margin-top:2px; color:var(--orbit-fg-muted);
            @if($isRetro) font-size:12px; padding:5px 8px; @else font-size:14px; padding:8px 8px; border-radius:6px; @endif
        ">
            @if($isRetro)<span> </span>@else<span aria-hidden="true">?</span>@endif
            <span>Ajuda</span>
        </a>
    </nav>
</aside>
