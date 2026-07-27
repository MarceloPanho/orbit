@php
    // Embutido no modal, o componente não carrega o chrome de página: quem dá
    // título e respiro é o próprio modal.
    $shell = $embedded
        ? 'display:flex; flex-direction:column; gap:16px;'
        : 'flex:1; overflow-y:auto; padding:32px; display:flex; flex-direction:column; gap:24px;';
@endphp

<div style="{{ $shell }}">

    @unless($embedded)
        <x-ui.page-header title="Categorias" subtitle="Categorias dos gastos" />
    @endunless

    <x-ui.card :title="$editingId ? 'Editar categoria' : 'Nova categoria'">
        <form wire:submit="saveExpenseCategory" style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end;">
            <div style="flex:2; min-width:200px;">
                <x-ui.input label="Nome" type="text" placeholder="Ex.: Comida, Casa…" wire:model="name" />
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

            @forelse($categories as $category)
            <x-ui.tr wire:key="categories-{{ $category->id }}">
                <x-ui.td tone="strong">{{ $category->name }}</x-ui.td>
                <x-ui.td align="right" nowrap>
                    <x-ui.action-button icon="✎" wire:click="editExpenseCategory({{ $category->id }})">Editar</x-ui.action-button>
                    <x-ui.action-button icon="✕" tone="danger"
                        x-on:click="$dispatch('confirm', {
                            type: 'error',
                            title: 'Excluir categoria',
                            message: {{ Js::from('Deseja realmente excluir a categoria «'.$category->name.'»?') }},
                            confirmText: 'Excluir',
                            onConfirm: () => $wire.deleteExpenseCategory({{ $category->id }}),
                        })">Excluir</x-ui.action-button>
                </x-ui.td>
            </x-ui.tr>
            @empty
            <x-ui.tr>
                <x-ui.td colspan="2" align="center" tone="subtle" style="padding:32px;">Nenhuma categoria encontrada.</x-ui.td>
            </x-ui.tr>
            @endforelse
        </x-ui.table>

        <x-ui.pagination :paginator="$categories" :page-name="$pageName" />
    </x-ui.card>

</div>
