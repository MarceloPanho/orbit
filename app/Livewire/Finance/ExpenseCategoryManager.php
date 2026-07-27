<?php

namespace App\Livewire\Finance;

use App\Models\ExpenseCategory;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseCategoryManager extends Component
{
    use WithPagination;

    // Nome do paginador. Fixo e distinto de 'page' porque este componente também
    // roda embutido no ExpenseManager, que já publica o seu próprio ?page= na URL.
    private const PAGE_NAME = 'catPage';

    // Quando embutido (modal), a view dispensa o chrome de página.
    public bool $embedded = false;

    public ?int $editingId = null;
    public string $name = '';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories', 'name')->ignore($this->editingId),
            ],
        ];
    }

    protected array $validationAttributes = [
        'name' => 'nome',
    ];

    // ================= Categorias =================

    public function saveExpenseCategory(): void
    {
        $data = $this->validate();

        $attributes = [
            'name' => $data['name'],
        ];

        if ($this->editingId) {
            ExpenseCategory::findOrFail($this->editingId)->update($attributes);
        } else {
            ExpenseCategory::create($attributes);
        }

        $this->resetForm();
        $this->dispatch('expense-categories-updated');
    }

    public function editExpenseCategory(int $id): void
    {
        $expenseCategory = ExpenseCategory::findOrFail($id);

        $this->editingId = $expenseCategory->id;
        $this->name = $expenseCategory->name;
        $this->resetValidation();
    }

    public function deleteExpenseCategory(int $id): void
    {
        $category = ExpenseCategory::findOrFail($id);

        if ($category->expenses()->exists()) {
            $this->dispatch('notify',
                type: 'error',
                title: 'Não foi possível excluir',
                message: "A categoria «{$category->name}» está vinculada a lançamentos e não pode ser excluída.",
            );

            return;
        }

        $category->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        $this->dispatch('expense-categories-updated');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'editingId']);
        $this->resetValidation();
    }

    // ================= Render =================

    public function render()
    {
        return view('livewire.finance.expense-category-manager', [
            'categories' => ExpenseCategory::orderBy('name')->paginate(10, pageName: self::PAGE_NAME),
            'pageName' => self::PAGE_NAME,
        ]);
    }
}
