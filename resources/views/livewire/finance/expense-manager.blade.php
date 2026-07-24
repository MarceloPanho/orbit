@php
    $money = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $hasDeps = $categories->isNotEmpty() && $methods->isNotEmpty();
@endphp

<div style="flex:1; overflow-y:auto; padding:32px; display:flex; flex-direction:column; gap:24px;">

    {{-- Cabeçalho --}}
    <x-ui.page-header title="Gastos" subtitle="Seus lançamentos de saída." />

    {{-- Aviso de erro (ex.: exclusão bloqueada por uso) --}}
    @if(session('error'))
        <div style="padding:12px 16px; border-radius:var(--orbit-radius); background:color-mix(in srgb, var(--orbit-danger) 12%, transparent); border:var(--orbit-border-width) solid color-mix(in srgb, var(--orbit-danger) 45%, transparent); color:var(--orbit-danger); font-size:13px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Resumo --}}
    <div style="display:flex; gap:16px; flex-wrap:wrap;">
        <x-ui.stat-card label="Total"          value="{{ $money($total) }}" icon="◎" />
        <x-ui.stat-card label="Lançamentos"    value="{{ $count }}"         icon="▤" />
        <x-ui.stat-card label="Maior categoria" value="{{ $topCategory }}"  icon="▲" />
    </div>

    {{-- Formulário: novo lançamento / edição inline --}}
    <x-ui.card :title="$editingId ? 'Editar lançamento' : 'Novo lançamento'">
        @unless($hasDeps)
            <div style="margin-bottom:12px; font-size:13px; color:var(--orbit-fg-subtle);">
                Crie ao menos uma categoria e um método de pagamento abaixo antes de lançar um gasto.
            </div>
        @endunless

        <form wire:submit="saveExpense" style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end;">
            <div style="flex:2; min-width:200px;">
                <x-ui.input label="Descrição" type="text" placeholder="Ex.: Mercado, Uber, Aluguel…" wire:model="item" />
                @error('item') <span style="font-size:11px; color:var(--orbit-danger);">{{ $message }}</span> @enderror
            </div>
            <div style="flex:1; min-width:120px;">
                <x-ui.input label="Valor" type="number" step="0.01" min="0" placeholder="0,00" wire:model="value" />
                @error('value') <span style="font-size:11px; color:var(--orbit-danger);">{{ $message }}</span> @enderror
            </div>
            <div style="flex:1; min-width:140px;">
                <x-ui.select label="Categoria" wire:model="expenseCategoryId">
                    <x-slot:action>
                        <button type="button"
                            x-on:click="$dispatch('open-modal', 'manage-categories')"
                            title="Gerenciar categorias"
                            style="background:none; border:none; padding:0; cursor:pointer; color:var(--orbit-fg-subtle); font-size:13px; line-height:1;">⚙</button>
                    </x-slot:action>
                    <option value="">Selecione…</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-ui.select>
                @error('expenseCategoryId') <span style="font-size:11px; color:var(--orbit-danger);">{{ $message }}</span> @enderror
            </div>
            <div style="flex:1; min-width:120px;">
                <x-ui.input label="Data" type="date" wire:model="spentAt" />
                @error('spentAt') <span style="font-size:11px; color:var(--orbit-danger);">{{ $message }}</span> @enderror
            </div>
            <div style="flex:1; min-width:120px;">
                <x-ui.select label="Método" wire:model="paymentMethodId">
                    <option value="">Selecione…</option>
                    @foreach($methods as $method)
                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                    @endforeach
                </x-ui.select>
                @error('paymentMethodId') <span style="font-size:11px; color:var(--orbit-danger);">{{ $message }}</span> @enderror
            </div>

            <x-ui.button type="submit" :disabled="! $hasDeps" style="{{ $hasDeps ? '' : 'opacity:.5; cursor:not-allowed;' }}">
                {{ $editingId ? 'Salvar' : 'Adicionar' }}
            </x-ui.button>
            @if($editingId)
                <x-ui.button type="button" variant="ghost" wire:click="resetForm">Cancelar</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    {{-- Gestão de métodos de pagamento (categorias ficam no modal) --}}
    <x-ui.card title="Métodos de pagamento">
        <x-slot:actions>
            <x-ui.button type="button" variant="ghost" icon="+" x-on:click="$dispatch('open-modal', 'add-method')">Cadastrar</x-ui.button>
        </x-slot:actions>

        <div style="display:flex; flex-direction:column; gap:6px;">
            @forelse($methods as $method)
                <div wire:key="method-{{ $method->id }}" style="display:flex; gap:8px; align-items:center;">
                    @if($editingMethodId === $method->id)
                        <x-ui.input type="text" wire:model="editingMethodName" wire:keydown.enter="saveMethod" style="flex:1;" />
                        <x-ui.button type="button" wire:click="saveMethod">OK</x-ui.button>
                    @else
                        <span style="flex:1; font-size:13px; color:var(--orbit-fg);">{{ $method->name }}</span>
                        <button type="button" wire:click="startEditMethod({{ $method->id }})" title="Renomear" style="background:none; border:none; cursor:pointer; color:var(--orbit-fg-subtle); font-size:13px;">✎</button>
                        <button type="button"
                            x-on:click="$dispatch('confirm', {
                                type: 'error',
                                title: 'Excluir método',
                                message: {{ Js::from('Deseja realmente excluir o método «'.$method->name.'»?') }},
                                confirmText: 'Excluir',
                                onConfirm: () => $wire.deleteMethod({{ $method->id }}),
                            })"
                            title="Excluir" style="background:none; border:none; cursor:pointer; color:var(--orbit-danger); font-size:13px;">✕</button>
                    @endif
                </div>
            @empty
                <span style="font-size:13px; color:var(--orbit-fg-subtle);">Nenhum método ainda.</span>
            @endforelse
        </div>
    </x-ui.card>

    {{-- Modal: gestão completa de categorias (mesmo componente da página /finance/expense-category) --}}
    <x-ui.modal name="manage-categories" title="Categorias" maxWidth="720px">
        <livewire:finance.expense-category-manager :embedded="true" :key="'manage-categories'" />
    </x-ui.modal>

    {{-- Modal: cadastrar método de pagamento --}}
    <x-ui.modal name="add-method" title="Novo método de pagamento">
        <form wire:submit="addMethod" style="display:flex; flex-direction:column; gap:12px;">
            <div>
                <x-ui.input label="Nome" type="text" placeholder="Ex.: Cartão, Pix, Dinheiro…" wire:model="newMethod" />
                @error('newMethod') <span style="font-size:11px; color:var(--orbit-danger);">{{ $message }}</span> @enderror
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <x-ui.button type="button" variant="ghost" x-on:click="show = false">Cancelar</x-ui.button>
                <x-ui.button type="submit">Salvar</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Tabela de lançamentos --}}
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
                <x-ui.th>Método</x-ui.th>
                <x-ui.th align="right">Valor</x-ui.th>
                <x-ui.th align="right">Ações</x-ui.th>
            </x-slot:head>

            @forelse($expenses as $expense)
                <x-ui.tr wire:key="expense-{{ $expense->id }}">
                    <x-ui.td tone="subtle" nowrap mono>{{ $expense->spent_at->format('d/m/Y') }}</x-ui.td>
                    <x-ui.td tone="strong">{{ $expense->item }}</x-ui.td>
                    <x-ui.td><x-ui.badge>{{ $expense->category?->name ?? '—' }}</x-ui.badge></x-ui.td>
                    <x-ui.td>{{ $expense->paymentMethod?->name ?? '—' }}</x-ui.td>
                    <x-ui.td align="right" tone="danger">− {{ $money($expense->value) }}</x-ui.td>
                    <x-ui.td align="right" nowrap>
                        <x-ui.action-button icon="✎" wire:click="editExpense({{ $expense->id }})">Editar</x-ui.action-button>
                        <x-ui.action-button icon="✕" tone="danger"
                            x-on:click="$dispatch('confirm', {
                                type: 'error',
                                title: 'Excluir lançamento',
                                message: {{ Js::from('Deseja realmente excluir o lançamento «'.$expense->item.'»?') }},
                                confirmText: 'Excluir',
                                onConfirm: () => $wire.deleteExpense({{ $expense->id }}),
                            })">Excluir</x-ui.action-button>
                    </x-ui.td>
                </x-ui.tr>
            @empty
                <x-ui.tr>
                    <x-ui.td colspan="6" align="center" tone="subtle" style="padding:32px;">Nenhum lançamento encontrado.</x-ui.td>
                </x-ui.tr>
            @endforelse

            @if($expenses->isNotEmpty())
                <x-slot:foot>
                    <tr>
                        <x-ui.td colspan="4" align="right" tone="subtle" style="border-bottom:none;">Total</x-ui.td>
                        <x-ui.td align="right" tone="strong" style="border-bottom:none; font-weight:600;">{{ $money($total) }}</x-ui.td>
                        <x-ui.td style="border-bottom:none;"></x-ui.td>
                    </tr>
                </x-slot:foot>
            @endif
        </x-ui.table>

        <x-ui.pagination :paginator="$expenses" />
    </x-ui.card>

</div>
