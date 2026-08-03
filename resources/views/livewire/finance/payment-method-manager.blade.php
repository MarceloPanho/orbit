@php
    // Embutido no modal, o componente não carrega o chrome de página: quem dá
    // título e respiro é o próprio modal.
    $shell = $embedded
        ? 'display:flex; flex-direction:column; gap:16px;'
        : 'flex:1; overflow-y:auto; padding:32px; display:flex; flex-direction:column; gap:24px;';
@endphp

<div style="{{ $shell }}">

    @unless($embedded)
        <x-ui.page-header title="Métodos de Pagamento" subtitle="Métodos de pagamento gastos" />
    @endunless

    <x-ui.card :title="$editingId ? 'Editar método de pagamento' : 'Novo método de pagamento'">
        <form wire:submit="savePaymentMethod" style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end;">
            <div style="flex:2; min-width:200px;">
                <x-ui.input label="Nome" type="text" placeholder="Ex.: Pix, Cartão de Crédito..." wire:model="name" />
                @error('name') <span style="font-size:11px; color:var(--orbit-danger);">{{ $message }}</span> @enderror
            </div>

            <x-ui.button type="submit">
                {{ $editingId ? 'Salvar' : 'Adicionar' }}
            </x-ui.button>
            @if($editingId)
                <x-ui.button type="button" variant="ghost" wire:click="resetForm">Cancelar</x-ui.button>
            @endif
        </form>
    </x-ui.card>


    <x-ui.card flush>
        <x-ui.table>
            <x-slot:head>
                <x-ui.th>Nome</x-ui.th>
                <x-ui.th align="right">Ações</x-ui.th>
            </x-slot:head>

            @forelse($paymentMethods as $paymentMethod)
            <x-ui.tr wire:key="categories-{{ $paymentMethod->id }}">
                <x-ui.td tone="strong">{{ $paymentMethod->name }}</x-ui.td>
                <x-ui.td align="right" nowrap>
                    <x-ui.action-button icon="✎" wire:click="editPaymentMethod({{ $paymentMethod->id }})">Editar</x-ui.action-button>
                    <x-ui.action-button icon="✕" tone="danger"
                        x-on:click="$dispatch('confirm', {
                            type: 'error',
                            title: 'Excluir categoria',
                            message: {{ Js::from('Deseja realmente excluir a categoria «'.$paymentMethod->name.'»?') }},
                            confirmText: 'Excluir',
                            onConfirm: () => $wire.deletePaymentMethod({{ $paymentMethod->id }}),
                        })">Excluir</x-ui.action-button>
                </x-ui.td>
            </x-ui.tr>
            @empty
            <x-ui.tr>
                <x-ui.td colspan="2" align="center" tone="subtle" style="padding:32px;">Nenhuma categoria encontrada.</x-ui.td>
            </x-ui.tr>
            @endforelse
        </x-ui.table>

        <x-ui.pagination :paginator="$paymentMethods" :page-name="$pageName" />
    </x-ui.card>

</div>
