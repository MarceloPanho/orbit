@php
    $money = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $hasDeps = $categories->isNotEmpty();
@endphp

<div style="flex:1; overflow-y:auto; padding:32px; display:flex; flex-direction:column; gap:24px;">

    <x-ui.page-header title="Renda/Recebimentos" subtitle="Seus lançamentos de entrada." />

    @if(session('error'))
        <div style="padding:12px 16px; border-radius:var(--orbit-radius); background:color-mix(in srgb, var(--orbit-danger) 12%, transparent); border:var(--orbit-border-width) solid color-mix(in srgb, var(--orbit-danger) 45%, transparent); color:var(--orbit-danger); font-size:13px;">
            {{ session('error') }}
        </div>
    @endif


    <x-ui.card :title="$editingId ? 'Editar lançamento' : 'Novo lançamento'">
        @unless($hasDeps)
            <div style="margin-bottom:12px; font-size:13px; color:var(--orbit-fg-subtle);">
                Crie ao menos uma categoria antes de lançar uma entrada.
            </div>
        @endunless

        <form wire:submit="saveIncome" style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end;">
            <div style="flex:2; min-width:200px;">
                <x-ui.input label="Descrição" type="text" placeholder="Ex.: Salário, Freelance..." wire:model="description" />
                @error('description') <span style="font-size:11px; color:var(--orbit-danger);">{{ $message }}</span> @enderror
            </div>
            <div style="flex:1; min-width:120px;">
                <x-ui.input label="Valor" type="number" step="0.01" min="0" placeholder="0,00" wire:model="value" />
                @error('value') <span style="font-size:11px; color:var(--orbit-danger);">{{ $message }}</span> @enderror
            </div>
            <div style="flex:1; min-width:140px;">
                <x-ui.select label="Categoria" wire:model="incomeCategoryId">
                    <x-slot:action>
                        <button type="button"
                            x-on:click="$dispatch('open-modal', 'manage-categories')"
                            title="Gerenciar categorias"
                            style="background:none; border:none; padding:0; cursor:pointer; color:var(--orbit-fg-subtle); display:inline-flex; line-height:0;"><x-ui.icon name="plus-circle" :size="15" /></button>
                    </x-slot:action>
                    <option value="">Selecione…</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-ui.select>
                @error('incomeCategoryId') <span style="font-size:11px; color:var(--orbit-danger);">{{ $message }}</span> @enderror
            </div>
            <div style="flex:1; min-width:120px;">
                <x-ui.input label="Data" type="date" wire:model="receivedAt" />
                @error('receivedAt') <span style="font-size:11px; color:var(--orbit-danger);">{{ $message }}</span> @enderror
            </div>

            <x-ui.button type="submit" :disabled="! $hasDeps" style="{{ $hasDeps ? '' : 'opacity:.5; cursor:not-allowed;' }}">
                {{ $editingId ? 'Salvar' : 'Adicionar' }}
            </x-ui.button>
            @if($editingId)
                <x-ui.button type="button" variant="ghost" wire:click="resetForm">Cancelar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    <x-ui.modal name="manage-categories" title="Categorias" maxWidth="720px">
        <livewire:finance.income-category-manager :embedded="true" :key="'manage-categories'" />
    </x-ui.modal>

    <x-ui.card title="Lançamentos" flush>
        <x-slot:actions>
            <x-ui.input type="text" placeholder="Buscar…" wire:model.live.debounce.300ms="search" style="width:auto; min-width:160px; padding:6px 10px;" />
            <x-ui.select wire:model.live="filterCategoryId" style="width:auto; padding:6px 10px;">
                <option value="">Todas as categorias</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-ui.select>
        </x-slot:actions>

        <x-ui.table>
            <x-slot:head>
                <x-ui.th>Data</x-ui.th>
                <x-ui.th>Descrição</x-ui.th>
                <x-ui.th>Categoria</x-ui.th>
                <x-ui.th align="right">Valor</x-ui.th>
                <x-ui.th align="right">Ações</x-ui.th>
            </x-slot:head>

            @forelse($incomes as $income)
                <x-ui.tr wire:key="income-{{ $income->id }}">
                    <x-ui.td tone="subtle" nowrap mono>{{ $income->received_at->format('d/m/Y') }}</x-ui.td>
                    <x-ui.td tone="strong">{{ $income->description }}</x-ui.td>
                    <x-ui.td><x-ui.badge>{{ $income->category?->name ?? '—' }}</x-ui.badge></x-ui.td>
                    <x-ui.td align="right" tone="success">{{ $money($income->value) }}</x-ui.td>
                    <x-ui.td align="right" nowrap>
                        <x-ui.action-button icon="✎" wire:click="editIncome({{ $income->id }})">Editar</x-ui.action-button>
                        <x-ui.action-button icon="✕" tone="danger"
                            x-on:click="$dispatch('confirm', {
                                type: 'error',
                                title: 'Excluir entrada',
                                message: {{ Js::from('Deseja realmente excluir a entrada «'.$income->description.'»?') }},
                                confirmText: 'Excluir',
                                onConfirm: () => $wire.deleteIncome({{ $income->id }}),
                            })">Excluir</x-ui.action-button>
                    </x-ui.td>
                </x-ui.tr>
            @empty
                <x-ui.tr>
                    <x-ui.td colspan="5" align="center" tone="subtle" style="padding:32px;">Nenhuma entrada encontrada.</x-ui.td>
                </x-ui.tr>
            @endforelse

            @if($incomes->isNotEmpty())
                <x-slot:foot>
                    <tr>
                        <x-ui.td colspan="3" align="right" tone="subtle" style="border-bottom:none;">Total</x-ui.td>
                        <x-ui.td align="right" tone="strong" style="border-bottom:none; font-weight:600;">{{ $money($total) }}</x-ui.td>
                        <x-ui.td style="border-bottom:none;"></x-ui.td>
                    </tr>
                </x-slot:foot>
            @endif
        </x-ui.table>

        <x-ui.pagination :paginator="$incomes" />
    </x-ui.card>

</div>
