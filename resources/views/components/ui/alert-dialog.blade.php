{{-- Diálogo de alerta global (estilo "swal"), no visual orbit.
     Colocado UMA vez no layout. Dispare de qualquer componente Livewire:

         $this->dispatch('notify',
             type: 'error',                 // error | warning | success | info (padrão: info)
             title: 'Não foi possível excluir',
             message: 'A categoria está em uso.',
         );

     Também funciona via Alpine: $dispatch('notify', { type:'success', message:'Salvo!' }) --}}
@php $isRetro = auth()->user()?->isRetro(); @endphp

<div
    x-data="{
        show: false,
        type: 'info',
        title: '',
        message: '',
        icons:  { error: '✕', warning: '⚠', success: '✓', info: 'ℹ' },
        colors: {
            error:   'var(--orbit-danger)',
            warning: 'var(--orbit-warning, var(--orbit-danger))',
            success: 'var(--orbit-success)',
            info:    'var(--orbit-accent)',
        },
        open(detail) {
            detail = detail || {};
            this.type    = detail.type || 'info';
            this.title   = detail.title || '';
            this.message = detail.message || '';
            this.show    = true;
        },
        get color() { return this.colors[this.type] ?? this.colors.info; },
        get icon()  { return this.icons[this.type] ?? this.icons.info; },
    }"
    x-on:notify.window="open($event.detail)"
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

            {{-- Ação --}}
            <div style="margin-top:22px; display:flex; justify-content:center;">
                <button
                    type="button"
                    x-on:click="show = false"
                    x-bind:style="`display:inline-flex; align-items:center; justify-content:center; min-width:120px; padding:9px 16px; font-size:13px; font-family:inherit; font-weight:500; color:var(--orbit-bg); background:${color}; border:var(--orbit-border-width) solid transparent; border-radius:var(--orbit-radius); cursor:pointer;{{ $isRetro ? ' text-transform:uppercase; font-family:var(--orbit-font-mono);' : '' }}`"
                >OK</button>
            </div>
        </div>
    </div>
</div>
