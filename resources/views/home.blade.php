<x-layouts.app>
    <x-sidebar active="dashboard" />

    <div style="flex:1; overflow-y:auto; padding:32px; display:flex; flex-direction:column;">

        {{-- Saudação --}}
        <div>
            <h1 style="font-size:22px; font-weight:500; color:var(--orbit-fg); margin:0;">Bom dia, Marcelo.</h1>
            <p style="font-size:13px; color:var(--orbit-fg-subtle); margin:4px 0 0;">Terça-feira, 1 de julho de 2025</p>
        </div>

        {{-- Cards de resumo --}}
        <div style="display:flex; gap:16px; margin-top:24px; flex-wrap:wrap;">
            <x-home.summary-card label="Saldo do mês"   value="R$ —,——"              icon="◎" />
            <x-home.summary-card label="Próximo evento" value="Nenhum evento hoje"   icon="▶" />
            <x-home.summary-card label="Hábitos hoje"   value="0 / 0 concluídos"     icon="▼" />
            <x-home.summary-card label="Notas recentes" value="Nenhuma nota recente" icon="?" />
        </div>

        {{-- Área vazia --}}
        <div style="flex:1; display:flex; align-items:center; justify-content:center;">
            <p style="color:var(--orbit-fg-subtle); font-size:13px;">Selecione um módulo na barra lateral para começar.</p>
        </div>
    </div>
</x-layouts.app>
