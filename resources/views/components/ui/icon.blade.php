{{-- Ícone SVG inline do Bootstrap Icons (MIT, viewBox 16×16).

     Inline em vez de pacote Composer: o app usa meia dúzia de ícones e o
     blade-bootstrap-icons traria ~2.000 SVGs no vendor/ para isso. O SVG herda
     currentColor, então acompanha a cor do elemento pai — inclusive o
     filter:brightness do :hover global de button (app.css).

     Para adicionar um ícone: copie os `d` do arquivo em
     https://icons.getbootstrap.com (todos usam viewBox 0 0 16 16).

     Uso:
       <x-ui.icon name="plus-circle" />
       <x-ui.icon name="plus-circle" :size="15" /> --}}
@props(['name', 'size' => 16])

@php
    $icons = [
        'plus-circle' => [
            'M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z',
            'M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z',
        ],
    ];

    // Explícito de propósito: um nome errado renderizando nada silenciosamente
    // custa muito mais tempo para achar do que um erro na cara.
    $paths = $icons[$name] ?? throw new InvalidArgumentException(
        "Ícone «{$name}» não existe em x-ui.icon. Disponíveis: ".implode(', ', array_keys($icons))
    );
@endphp

<svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 16 16"
    fill="currentColor"
    width="{{ $size }}"
    height="{{ $size }}"
    aria-hidden="true"
    {{ $attributes->merge(['style' => 'display:block; flex:none;']) }}
>@foreach($paths as $path)<path d="{{ $path }}" />@endforeach</svg>
