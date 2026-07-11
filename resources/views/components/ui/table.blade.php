{{-- Tabela slot-based. Rola horizontalmente em telas estreitas.
     A moldura (superfície/borda) vem do x-ui.card; aqui só a tabela.
       - slot `head`: as células <x-ui.th> (o <tr> do cabeçalho é montado aqui)
       - slot padrão: as linhas <x-ui.tr> do corpo
       - slot `foot`: linhas do rodapé (o chamador fornece <tr>/<x-ui.tr>) --}}
@props(['minWidth' => '640px'])

<div style="overflow-x:auto;">
    <table style="width:100%; border-collapse:collapse; min-width:{{ $minWidth }};">
        @isset($head)
            <thead><tr>{{ $head }}</tr></thead>
        @endisset
        <tbody>{{ $slot }}</tbody>
        @isset($foot)
            <tfoot>{{ $foot }}</tfoot>
        @endisset
    </table>
</div>
