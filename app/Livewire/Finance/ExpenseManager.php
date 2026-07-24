<?php

namespace App\Livewire\Finance;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseManager extends Component
{
    use WithPagination;

    // --- Formulário de gasto ---
    public ?int $editingId = null;
    public string $item = '';
    public ?string $value = null;
    public ?int $expenseCategoryId = null;
    public ?int $paymentMethodId = null;
    public string $spentAt = '';

    // --- Gestão de métodos (categorias vivem no ExpenseCategoryManager) ---
    public string $newMethod = '';
    public ?int $editingMethodId = null;
    public string $editingMethodName = '';

    // --- Filtros da tabela ---
    public string $search = '';
    public ?int $filterCategoryId = null;

    public function mount(): void
    {
        $this->spentAt = today()->toDateString();
    }

    // Volta pra primeira página ao filtrar, evitando páginas vazias.
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategoryId(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'item' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'expenseCategoryId' => ['required', 'exists:expense_categories,id'],
            'paymentMethodId' => ['required', 'exists:payment_methods,id'],
            'spentAt' => ['required', 'date'],
        ];
    }

    protected array $validationAttributes = [
        'item' => 'descrição',
        'value' => 'valor',
        'expenseCategoryId' => 'categoria',
        'paymentMethodId' => 'método',
        'spentAt' => 'data',
    ];

    // ================= Gastos =================

    public function saveExpense(): void
    {
        $data = $this->validate();

        $attributes = [
            'item' => $data['item'],
            'value' => $data['value'],
            'expense_category_id' => $data['expenseCategoryId'],
            'payment_method_id' => $data['paymentMethodId'],
            'spent_at' => $data['spentAt'],
        ];

        if ($this->editingId) {
            Expense::findOrFail($this->editingId)->update($attributes);
        } else {
            Expense::create($attributes);
        }

        $this->resetForm();
    }

    public function editExpense(int $id): void
    {
        $expense = Expense::findOrFail($id);

        $this->editingId = $expense->id;
        $this->item = $expense->item;
        $this->value = (string) $expense->value;
        $this->expenseCategoryId = $expense->expense_category_id;
        $this->paymentMethodId = $expense->payment_method_id;
        $this->spentAt = $expense->spent_at->toDateString();
        $this->resetValidation();
    }

    public function deleteExpense(int $id): void
    {
        Expense::findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'item', 'value', 'expenseCategoryId', 'paymentMethodId']);
        $this->spentAt = today()->toDateString();
        $this->resetValidation();
    }

    // ============ Categorias / Métodos ============

    /**
     * O ExpenseCategoryManager (modal) avisa quando a lista muda. Basta o
     * re-render para os selects se reconstruírem; aqui só cuidamos das seleções
     * que apontavam para uma categoria recém-excluída.
     */
    #[On('expense-categories-updated')]
    public function refreshCategories(): void
    {
        if ($this->expenseCategoryId && ! ExpenseCategory::whereKey($this->expenseCategoryId)->exists()) {
            $this->expenseCategoryId = null;
        }

        if ($this->filterCategoryId && ! ExpenseCategory::whereKey($this->filterCategoryId)->exists()) {
            $this->filterCategoryId = null;
            $this->resetPage();
        }
    }

    public function addMethod(): void
    {
        $data = $this->validate(
            ['newMethod' => ['required', 'string', 'max:255', 'unique:payment_methods,name']],
            attributes: ['newMethod' => 'método'],
        );

        PaymentMethod::create(['name' => $data['newMethod']]);
        $this->newMethod = '';
        $this->dispatch('close-modal', name: 'add-method');
    }

    public function startEditMethod(int $id): void
    {
        $method = PaymentMethod::findOrFail($id);
        $this->editingMethodId = $method->id;
        $this->editingMethodName = $method->name;
    }

    public function saveMethod(): void
    {
        $data = $this->validate(
            ['editingMethodName' => ['required', 'string', 'max:255', 'unique:payment_methods,name,'.$this->editingMethodId]],
            attributes: ['editingMethodName' => 'método'],
        );

        PaymentMethod::findOrFail($this->editingMethodId)->update(['name' => $data['editingMethodName']]);
        $this->reset(['editingMethodId', 'editingMethodName']);
    }

    public function deleteMethod(int $id): void
    {
        $method = PaymentMethod::findOrFail($id);

        if ($method->expenses()->exists()) {
            session()->flash('error', "O método \"{$method->name}\" está em uso e não pode ser excluído.");

            return;
        }

        $method->delete();
    }

    // ================= Render =================

    public function render()
    {
        // Base filtrada — reaproveitada na tabela (paginada) e nos agregados (totais).
        $base = Expense::query()
            ->when($this->search !== '', fn ($q) => $q->where('item', 'like', '%'.$this->search.'%'))
            ->when($this->filterCategoryId, fn ($q) => $q->where('expense_category_id', $this->filterCategoryId));

        $expenses = (clone $base)
            ->with(['category', 'paymentMethod'])
            ->latest('spent_at')
            ->latest('id')
            ->paginate(10);

        // Totais consideram TODOS os registros filtrados, não só a página atual.
        $total = (clone $base)->sum('value');
        $count = (clone $base)->count();

        $topCategoryId = (clone $base)
            ->selectRaw('expense_category_id, SUM(value) as agg')
            ->groupBy('expense_category_id')
            ->orderByDesc('agg')
            ->value('expense_category_id');

        return view('livewire.finance.expense-manager', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'methods' => PaymentMethod::orderBy('name')->get(),
            'total' => $total,
            'count' => $count,
            'topCategory' => $topCategoryId ? (ExpenseCategory::find($topCategoryId)?->name ?? '—') : '—',
        ]);
    }
}
