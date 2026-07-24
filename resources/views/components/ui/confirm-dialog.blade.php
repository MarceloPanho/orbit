{{-- Diálogo de confirmação global (estilo "swal"), no visual orbit.
     Colocado UMA vez no layout. Substitui o wire:confirm nativo.

     Dispare de um botão (Alpine), passando a ação a executar ao confirmar:

         <button x-on:click="$dispatch('confirm', {
             type: 'error',
             title: 'Excluir categoria',
             message: 'Tem certeza?',
             confirmText: 'Excluir',
             onConfirm: () => $wire.deleteExpenseCategory(123),
         })">Excluir</button>

     Como onConfirm é uma função JS (não serializada), ela roda no $wire
     do componente que disparou — chamando o método Livewire correto. --}}
@php $isRetro = auth()->user()?->isRetro(); @endphp

<div
    x-data="{
        show: false,
        type: 'warning',
        title: '',
        message: '',
        confirmText: 'Confirmar',
        cancelText: 'Cancelar',
        onConfirm: null,
        icons:  { error: '⚠', warning: '⚠', success: '✓', info: 'ℹ' },
        colors: {
            error:   'var(--orbit-danger)',
            warning: 'var(--orbit-warning, var(--orbit-danger))',
            success: 'var(--orbit-success)',
            info:    'var(--orbit-accent)',
        },
        open(detail) {
            detail = detail || {};
            this.type        = detail.type || 'warning';
            this.title       = detail.title || '';
            this.message     = detail.message || '';
            this.confirmText = detail.confirmText || 'Confirmar';
            this.cancelText  = detail.cancelText || 'Cancelar';
            this.onConfirm   = detail.onConfirm || null;
            this.show        = true;
        },
        accept() {
            const cb = this.onConfirm;
            this.show = false;
            if (cb) cb();
        },
        get color() { return this.colors[this.type] ?? this.colors.warning; },
        get icon()  { return this.icons[this.type] ?? this.icons.warning; },
    }"
    x-on:confirm.window="open($event.detail)"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    style="position:fixed; inset:0; z-index:60;"
>
    {{-- Backdrop --}}
    <div x-show="show" x-transition.opacity style="position:absolute; inset:0; background:rgba(0,0,0,0.55);"></div>

    {{-- Centralização --}}
    <div x-on:click.self="show = false" style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:24px 16px;">

        {{-- Painel --}}
        <div
            x-show="show"
            x-transition
            style="position:relative; width:100%; max-width:400px; background:var(--orbit-bg-surface); border:var(--orbit-border-width) solid var(--orbit-border); border-radius:var(--orbit-radius); overflow:hidden; box-shadow:0 24px 64px rgba(0,0,0,0.35); text-align:center; padding:28px 24px;"
        >
            {{-- Ícone circular --}}
            <div
                x-bind:style="`width:56px; height:56px; margin:0 auto 16px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:26px; line-height:1; color:${color}; background:color-mix(in srgb, ${color} 14%, transparent); border:var(--orbit-border-width) solid color-mix(in srgb, ${color} 45%, transparent);`"
            >
                <span x-text="icon"></span>
            </div>

            {{-- Título (opcional) --}}
            <h3
                x-show="title"
                x-text="title"
                style="font-size:15px; font-weight:600; color:var(--orbit-fg); margin:0 0 6px;{{ $isRetro ? ' text-transform:uppercase; font-family:var(--orbit-font-mono);' : '' }}"
            ></h3>

            {{-- Mensagem --}}
            <p x-text="message" style="font-size:13px; line-height:1.5; color:var(--orbit-fg-muted); margin:0;"></p>

            {{-- Ações --}}
            <div style="margin-top:22px; display:flex; justify-content:center; gap:10px;">
                {{-- Cancelar --}}
                <button
                    type="button"
                    x-on:click="show = false"
                    x-text="cancelText"
                    style="display:inline-flex; align-items:center; justify-content:center; min-width:110px; padding:9px 16px; font-size:13px; font-family:inherit; font-weight:500; color:var(--orbit-fg-muted); background:transparent; border:var(--orbit-border-width) solid var(--orbit-border); border-radius:var(--orbit-radius); cursor:pointer;{{ $isRetro ? ' text-transform:uppercase; font-family:var(--orbit-font-mono);' : '' }}"
                ></button>

                {{-- Confirmar --}}
                <button
                    type="button"
                    x-on:click="accept()"
                    x-text="confirmText"
                    x-bind:style="`display:inline-flex; align-items:center; justify-content:center; min-width:110px; padding:9px 16px; font-size:13px; font-family:inherit; font-weight:500; color:var(--orbit-bg); background:${color}; border:var(--orbit-border-width) solid transparent; border-radius:var(--orbit-radius); cursor:pointer;{{ $isRetro ? ' text-transform:uppercase; font-family:var(--orbit-font-mono);' : '' }}`"
                ></button>
            </div>
        </div>
    </div>
</div>
