<?php

namespace App\Livewire\Finance;

use App\Models\IncomeCategory;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class IncomeCategoryManager extends Component
{
    use WithPagination;

    private const PAGE_NAME = 'catPage';

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
                Rule::unique('income_categories', 'name')->ignore($this->editingId),
            ],
        ];
    }

    protected array $validationAttributes = [
        'name' => 'nome',
    ];


    public function saveIncomeCategory(): void
    {
        $data = $this->validate();

        $attributes = [
            'name' => $data['name'],
        ];

        if ($this->editingId) {
            IncomeCategory::findOrFail($this->editingId)->update($attributes);
        } else {
            IncomeCategory::create($attributes);
        }

        $this->resetForm();
        $this->dispatch('income-categories-updated');
    }

    public function editIncomeCategory(int $id): void
    {
        $incomeCategory = IncomeCategory::findOrFail($id);

        $this->editingId = $incomeCategory->id;
        $this->name = $incomeCategory->name;
        $this->resetValidation();
    }

    public function deleteIncomeCategory(int $id): void
    {
        $category = IncomeCategory::findOrFail($id);

        if ($category->incomes()->exists()) {
            $this->dispatch('notify',
                type: 'error',
                title: 'Não foi possível excluir',
                message: "A categoria «{$category->name}» está vinculada a recebimentos e não pode ser excluída.",
            );

            return;
        }

        $category->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        $this->dispatch('income-categories-updated');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'editingId']);
        $this->resetValidation();
    }

    // ================= Render =================

    public function render()
    {
        return view('livewire.finance.income-category-manager', [
            'categories' => IncomeCategory::orderBy('name')->paginate(10, pageName: self::PAGE_NAME),
            'pageName' => self::PAGE_NAME,
        ]);
    }
}
