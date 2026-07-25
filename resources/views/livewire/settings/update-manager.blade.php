@php
    // O status é gravado pelo launcher no boot (scripts/check-update.sh).
    $never   = $status === null;
    $offline = ! $never && ! ($status['ok'] ?? false);
    $current = $status['current'] ?? null;
@endphp

<div>
    <x-ui.card title="Atualizações">
        <x-slot:actions>
            <button type="button" wire:click="check" wire:loading.attr="disabled"
                style="background:none; border:none; padding:0; cursor:pointer; color:var(--orbit-fg-subtle); font-size:12px;">
                <span wire:loading.remove wire:target="check">verificar agora</span>
                <span wire:loading wire:target="check">verificando…</span>
            </button>
        </x-slot:actions>

        <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">

            <div style="flex:1; min-width:240px; display:flex; flex-direction:column; gap:6px;">

                {{-- Estado principal --}}
                @if($never)
                    <span style="font-size:13px; color:var(--orbit-fg);">Nenhuma verificação feita ainda.</span>
                @elseif($behind > 0)
                    <span style="font-size:13px; color:var(--orbit-fg);">
                        <span style="color:var(--orbit-accent);">●</span>
                        {{ $behind }} {{ $behind === 1 ? 'atualização disponível' : 'atualizações disponíveis' }}
                    </span>
                @else
                    <span style="font-size:13px; color:var(--orbit-fg);">✓ Você está na versão mais recente.</span>
                @endif

                {{-- Versão local --}}
                @if($current)
                    <span style="font-size:12px; color:var(--orbit-fg-subtle); font-family:var(--orbit-font-mono, inherit);">
                        versão local {{ $current }}@if(! empty($status['current_date'])) · {{ $status['current_date'] }} @endif
                    </span>
                @endif

                {{-- Metadados da checagem --}}
                @if($checkedAt)
                    <span style="font-size:11px; color:var(--orbit-fg-subtle);">
                        verificado {{ $checkedAt->diffForHumans() }}
                        @if($offline) · sem conexão na última tentativa @endif
                    </span>
                @endif
            </div>

            {{-- Ação --}}
            <div>
                @if($canUpdate)
                    <x-ui.button type="button" icon="↓"
                        x-on:click="$dispatch('confirm', {
                            type: 'info',
                            title: 'Atualizar o Orbit',
                            message: 'O app vai fechar, atualizar e reabrir sozinho. Leva de 10 a 60 segundos.',
                            confirmText: 'Atualizar',
                            onConfirm: () => $wire.update(),
                        })">Atualizar agora</x-ui.button>
                @else
                    <x-ui.button type="button" icon="↓" disabled
                        style="opacity:.5; cursor:not-allowed;">Atualizar agora</x-ui.button>
                @endif
            </div>
        </div>

        {{-- Por que o botão está travado --}}
        @if($dirty)
            <div style="margin-top:12px; padding:10px 12px; border-radius:var(--orbit-radius); font-size:12px; line-height:1.5;
                        background:color-mix(in srgb, var(--orbit-warning) 10%, transparent);
                        border:var(--orbit-border-width) solid color-mix(in srgb, var(--orbit-warning) 40%, transparent);
                        color:var(--orbit-fg-muted);">
                Este clone tem alterações locais não commitadas, então a atualização automática fica bloqueada —
                ela usa <code>git pull --ff-only</code> e nunca sobrescreve trabalho local.
                Commite ou descarte as mudanças para liberar o botão.
            </div>
        @endif
    </x-ui.card>
</div>
