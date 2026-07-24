{{-- Elemento base de modal (Alpine).
     Abre/fecha por eventos de janela, compatível com Alpine e Livewire:
       • abrir:  <button x-on:click="$dispatch('open-modal', 'nome')">
       • fechar: $dispatch('close-modal', 'nome')  ou  $this->dispatch('close-modal', name: 'nome')
     O corpo vai no slot padrão; o cabeçalho mostra o título e o botão de fechar.

     Props:
       name     — identificador único usado pelos eventos open/close-modal (obrigatório)
       title    — título opcional no cabeçalho
       maxWidth — largura máxima do painel (padrão 480px) --}}
@props(['name', 'title' => null, 'maxWidth' => '640px'])

@php $isRetro = auth()->user()?->isRetro(); @endphp

<div
    x-data="{ show: false }"
    x-on:open-modal.window="(($event.detail?.name ?? $event.detail) === '{{ $name }}') && (show = true)"
    x-on:close-modal.window="(($event.detail?.name ?? $event.detail) === '{{ $name }}') && (show = false)"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    style="position:fixed; inset:0; z-index:50;"
>
    {{-- Backdrop escuro --}}
    <div
        x-show="show"
        x-transition.opacity
        style="position:absolute; inset:0; background:rgba(0,0,0,0.55);"
    ></div>

    {{-- Camada de centralização (flex fixo — x-show fica no pai, não aqui) --}}
    <div
        x-on:click.self="show = false"
        style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:24px 16px; overflow-y:auto;"
    >
        {{-- Painel --}}
        <div
            x-show="show"
            x-transition
            style="position:relative; width:100%; max-width:{{ $maxWidth }}; background:var(--orbit-bg-surface); border:var(--orbit-border-width) solid var(--orbit-border); border-radius:var(--orbit-radius); overflow:hidden; box-shadow:0 24px 64px rgba(0,0,0,0.35);"
        >
        @if($title !== null)
            <div style="padding:{{ $isRetro ? '4px 12px' : '14px 16px' }}; border-bottom:0.5px solid var(--orbit-border); display:flex; align-items:center; gap:12px;">
                <h3 style="{{ $isRetro
                    ? 'font-size:9px; color:var(--orbit-fg-subtle); text-transform:uppercase; margin:0;'
                    : 'font-size:13px; font-weight:500; color:var(--orbit-fg-muted); margin:0;' }}">{{ $isRetro ? \Illuminate\Support\Str::upper($title) : $title }}</h3>
                <button type="button" x-on:click="show = false" aria-label="Fechar" style="margin-left:auto; background:none; border:none; cursor:pointer; color:var(--orbit-fg-subtle); font-size:15px; line-height:1;">✕</button>
            </div>
        @endif

        <div style="padding:{{ $isRetro ? '12px' : '16px' }};">{{ $slot }}</div>
        </div>
    </div>
</div>
