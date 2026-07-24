{{-- Botão de ação em pílula (ícone + rótulo) para linhas de tabela.
     Em repouso é discreto (borda hairline, texto suave); no hover ganha
     fundo tint + cor semântica. A cor vem da custom property --tone, então
     o :hover (em app.css) funciona sem conflitar com estilo inline.

     tone: accent (padrão) | danger
     Uso:
       <x-ui.action-button icon="✎" wire:click="edit(1)">Editar</x-ui.action-button>
       <x-ui.action-button icon="✕" tone="danger" x-on:click="...">Excluir</x-ui.action-button> --}}
@props(['icon' => null, 'tone' => 'accent'])

@php
    $isRetro = auth()->user()?->isRetro();
    $tones = [
        'accent' => 'var(--orbit-accent)',
        'danger' => 'var(--orbit-danger)',
    ];
    $color = $tones[$tone] ?? $tones['accent'];

    $style = '--tone:'.$color.';';
    if ($isRetro) {
        $style .= 'text-transform:uppercase; font-family:var(--orbit-font-mono);';
    }
@endphp

<button
    type="button"
    {{ $attributes->merge(['class' => 'orbit-action-btn', 'style' => $style]) }}
>
    @if($icon)<span aria-hidden="true" style="font-size:12px; line-height:1;">{{ $icon }}</span>@endif
    <span>{{ $slot }}</span>
</button>
