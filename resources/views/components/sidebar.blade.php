@props(['active' => 'dashboard'])

@php
    $isRetro = auth()->user()?->isRetro();

    $modules = [
        'financas' => ['label' => 'Finanças', 'children' => ['Gastos' => 'expense', 'Categorias' => 'expense-category','Métodos de Pagamento' => 'payment-method']],
        //'financas' => ['label' => 'Finanças', 'children' => ['Dashboard' => null, 'Gastos' => 'expense', 'Renda/Recebimentos' => null, 'Investimentos' => null, 'Assinaturas' => null, 'Categorias' => 'expense-category','Métodos de Pagamento' => 'payment-method']],
        // 'agenda'   => ['label' => 'Agenda',   'children' => ['Hoje', 'Semana', 'Eventos', 'Tarefas']],
        // 'notas'    => ['label' => 'Notas',    'children' => ['Todas as notas', 'Por tag', 'Favoritas']],
        // 'habitos'  => ['label' => 'Hábitos',  'children' => ['Hoje', 'Histórico', 'Metas']],
    ];

    $openModule = null;

    foreach ($modules as $key => $module) {
        if (in_array(request()->route()?->getName(), array_filter($module['children']), true)) {
            $openModule = $key;
            break;
        }
    }
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

    {{-- overflow-y:auto, não hidden: com os módulos todos ativos numa tela baixa
         o menu passa da altura da sidebar, e com hidden os últimos itens
         (Configurações, Ajuda) sumiam sem barra de rolagem para alcançá-los. --}}
    <nav style="flex:1; overflow-y:auto; overflow-x:hidden; padding:12px 8px;" x-data="{ open: @js($openModule) }">

        {{-- Seção: módulos --}}
        <x-ui.nav-section label="Módulos" />

        {{-- Dashboard --}}
        <x-ui.nav-item :href="route('home')" icon="◎" :active="$active === 'dashboard'" style="margin-top:4px;">Dashboard</x-ui.nav-item>

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

            <div x-show="open === '{{ $key }}'" x-transition @style(['display:none' => $openModule !== $key])>
                @foreach($module['children'] as $child => $childRoute)
                     <a href="{{ $childRoute ? route($childRoute) : '#' }}" style="
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
        <x-ui.nav-section label="Sistema" divider />

        {{-- Configurações --}}
        <x-ui.nav-item :href="route('settings')" icon="⚙" :active="$active === 'settings'" style="margin-top:4px;">Configurações</x-ui.nav-item>

        {{-- Ajuda --}}
        <x-ui.nav-item href="#" icon="?" style="margin-top:2px;">Ajuda</x-ui.nav-item>
    </nav>
</aside>
