<x-layouts.app>
    <x-sidebar active="dashboard" />

    <div style="flex:1; overflow-y:auto; padding:32px; display:flex; flex-direction:column;">

        @php
            $agora = now();

            $saudacao = match (true) {
                // A madrugada ainda é "boa noite" — só "hour < 12" daria
                // "Bom dia" à 1h da manhã.
                $agora->hour < 6 => 'Boa noite',
                $agora->hour < 12 => 'Bom dia',
                $agora->hour < 18 => 'Boa tarde',
                default => 'Boa noite',
            };

            $data = Str::ucfirst($agora->isoFormat('dddd, D [de] MMMM [de] YYYY'));

            // Só o primeiro nome: "Boa noite, Marcelo." lê melhor que o nome completo.
            $nome = Str::before(auth()->user()?->name ?? '', ' ');

            // Montado aqui, e não no atributo: aspas duplas dentro de :title="..."
            // fecham o atributo e o Blade engole o resto do arquivo sem erro.
            $titulo = $nome ? "{$saudacao}, {$nome}." : $saudacao;
        @endphp

        {{-- Saudação --}}
        <x-ui.page-header :title="$titulo" :subtitle="$data" />

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
