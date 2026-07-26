<div>
    <x-ui.card title="Atualizações">
        <x-slot:actions>
            @if($rodandoNoDesktop)
                <button type="button" wire:click="verificar" wire:loading.attr="disabled"
                    style="background:none; border:none; padding:0; cursor:pointer; color:var(--orbit-fg-subtle); font-size:12px;">
                    <span wire:loading.remove wire:target="verificar">verificar agora</span>
                    <span wire:loading wire:target="verificar">verificando…</span>
                </button>
            @endif
        </x-slot:actions>

        <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">

            <div style="flex:1; min-width:240px; display:flex; flex-direction:column; gap:6px;">

                @if(! $rodandoNoDesktop)
                    <span style="font-size:13px; color:var(--orbit-fg);">
                        Atualização automática só existe na versão instalada.
                    </span>
                    <span style="font-size:12px; color:var(--orbit-fg-subtle);">
                        Neste clone de desenvolvimento, atualize com <code>make update</code>.
                    </span>
                @elseif($estado === 'verificando')
                    <span style="font-size:13px; color:var(--orbit-fg);">Verificando atualizações…</span>
                @elseif($estado === 'disponivel')
                    <span style="font-size:13px; color:var(--orbit-fg);">
                        <span style="color:var(--orbit-accent);">●</span>
                        Versão {{ $versaoDisponivel }} disponível
                    </span>
                @elseif($estado === 'baixando')
                    <span style="font-size:13px; color:var(--orbit-fg);">Baixando… {{ $progresso }}%</span>
                    <div style="height:4px; border-radius:2px; background:var(--orbit-bg-subtle); overflow:hidden;">
                        <div style="height:100%; width:{{ $progresso }}%; background:var(--orbit-accent); transition:width .2s;"></div>
                    </div>
                @elseif($estado === 'pronto')
                    <span style="font-size:13px; color:var(--orbit-fg);">
                        ✓ Versão {{ $versaoDisponivel }} baixada — reinicie para instalar.
                    </span>
                @elseif($estado === 'atualizado')
                    <span style="font-size:13px; color:var(--orbit-fg);">✓ Você está na versão mais recente.</span>
                @elseif($estado === 'erro')
                    <span style="font-size:13px; color:var(--orbit-fg);">Não foi possível verificar atualizações.</span>
                @else
                    <span style="font-size:13px; color:var(--orbit-fg);">Nenhuma verificação feita ainda.</span>
                @endif

                <span style="font-size:12px; color:var(--orbit-fg-subtle); font-family:var(--orbit-font-mono, inherit);">
                    versão instalada {{ $versaoAtual }}
                </span>
            </div>

            <div>
                @if($rodandoNoDesktop && $estado === 'disponivel')
                    <x-ui.button type="button" icon="↓" wire:click="baixar">Baixar</x-ui.button>
                @elseif($rodandoNoDesktop && $estado === 'pronto')
                    <x-ui.button type="button" icon="↻"
                        x-on:click="$dispatch('confirm', {
                            type: 'info',
                            title: 'Reiniciar o Orbit',
                            message: 'O app vai fechar e reabrir já atualizado. Leva alguns segundos.',
                            confirmText: 'Reiniciar',
                            onConfirm: () => $wire.instalar(),
                        })">Reiniciar e instalar</x-ui.button>
                @endif
            </div>
        </div>

        @if($estado === 'erro')
            <div style="margin-top:12px; padding:10px 12px; border-radius:var(--orbit-radius); font-size:12px; line-height:1.5;
                        background:color-mix(in srgb, var(--orbit-warning) 10%, transparent);
                        border:var(--orbit-border-width) solid color-mix(in srgb, var(--orbit-warning) 40%, transparent);
                        color:var(--orbit-fg-muted);">
                {{ $mensagemErro }}
            </div>
        @endif
    </x-ui.card>
</div>
