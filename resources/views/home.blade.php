<x-layouts.app>
    <x-sidebar active="dashboard" />

    <div style="flex:1; overflow-y:auto; padding:32px; display:flex; flex-direction:column;">

        {{-- Saudação --}}
        <x-ui.page-header title="Bom dia, Marcelo." subtitle="Terça-feira, 1 de julho de 2025" />

        {{-- Cards de resumo --}}
        <div style="display:flex; gap:16px; margin-top:24px; flex-wrap:wrap;">
            <x-ui.stat-card label="Saldo do mês"   value="R$ —,——"              icon="◎" />
            <x-ui.stat-card label="Próximo evento" value="Nenhum evento hoje"   icon="▶" />
            <x-ui.stat-card label="Hábitos hoje"   value="0 / 0 concluídos"     icon="▼" />
            <x-ui.stat-card label="Notas recentes" value="Nenhuma nota recente" icon="?" />
        </div>

        {{-- Área vazia --}}
        <div style="flex:1; display:flex; align-items:center; justify-content:center;">
            <p style="color:var(--orbit-fg-subtle); font-size:13px;">Selecione um módulo na barra lateral para começar.</p>
        </div>
    </div>
</x-layouts.app>
