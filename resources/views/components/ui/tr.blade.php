{{-- Linha do corpo com realce no hover. --}}
<tr {{ $attributes }}
    onmouseover="this.style.background='var(--orbit-bg-panel)'"
    onmouseout="this.style.background='transparent'"
>{{ $slot }}</tr>
