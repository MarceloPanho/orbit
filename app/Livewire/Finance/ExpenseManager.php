<?php

namespace App\Livewire\Finance;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use Livewire\Component;

class ExpenseManager extends Component
{
    // --- Formulário de gasto ---
    public ?int $editingId = null;
    public string $item = '';
    public ?string $value = null;
    public ?int $expenseCategoryId = null;
    public ?int $paymentMethodId = null;
    public string $spentAt = '';

    // --- Gestão de categorias / métodos ---
    public string $newCategory = '';
    public string $newMethod = '';
    public ?int $editingCategoryId = null;
    public string $editingCategoryName = '';
    public ?int $editingMethodId = null;
    public string $editingMethodName = '';

    // --- Filtros da tabela ---
    public string $search = '';
    public ?int $filterCategoryId = null;

    public function mount(): void
    {
        $this->spentAt = today()->toDateString();
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

    public function addCategory(): void
    {
        $data = $this->validate(
            ['newCategory' => ['required', 'string', 'max:255', 'unique:expense_categories,name']],
            attributes: ['newCategory' => 'categoria'],
        );

        ExpenseCategory::create(['name' => $data['newCategory']]);
        $this->newCategory = '';
        $this->dispatch('close-modal', name: 'add-category');
    }

    public function startEditCategory(int $id): void
    {
        $category = ExpenseCategory::findOrFail($id);
        $this->editingCategoryId = $category->id;
        $this->editingCategoryName = $category->name;
    }

    public function saveCategory(): void
    {
        $data = $this->validate(
            ['editingCategoryName' => ['required', 'string', 'max:255', 'unique:expense_categories,name,'.$this->editingCategoryId]],
            attributes: ['editingCategoryName' => 'categoria'],
        );

        ExpenseCategory::findOrFail($this->editingCategoryId)->update(['name' => $data['editingCategoryName']]);
        $this->reset(['editingCategoryId', 'editingCategoryName']);
    }

    public function deleteCategory(int $id): void
    {
        $category = ExpenseCategory::findOrFail($id);

        if ($category->expenses()->exists()) {
            session()->flash('error', "A categoria \"{$category->name}\" está em uso e não pode ser excluída.");

            return;
        }

        $category->delete();
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
        $expenses = Expense::query()
            ->with(['category', 'paymentMethod'])
            ->when($this->search !== '', fn ($q) => $q->where('item', 'like', '%'.$this->search.'%'))
            ->when($this->filterCategoryId, fn ($q) => $q->where('expense_category_id', $this->filterCategoryId))
            ->latest('spent_at')
            ->latest('id')
            ->get();

        $total = $expenses->sum('value');

        $topCategory = $expenses
            ->groupBy(fn ($e) => $e->category?->name)
            ->map(fn ($group) => $group->sum('value'))
            ->sortDesc()
            ->keys()
            ->first();

        return view('livewire.finance.expense-manager', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'methods' => PaymentMethod::orderBy('name')->get(),
            'total' => $total,
            'count' => $expenses->count(),
            'topCategory' => $topCategory ?? '—',
        ]);
    }
}
