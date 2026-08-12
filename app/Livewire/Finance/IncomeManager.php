<?php

namespace App\Livewire\Finance;

use App\Models\Income;
use App\Models\IncomeCategory;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class IncomeManager extends Component
{
    use WithPagination;

    public ?int $editingId = null;
    public string $description = '';
    public ?string $value = null;
    public ?int $incomeCategoryId = null;
    public string $receivedAt = '';

    public string $search = '';
    public ?int $filterCategoryId = null;

    public function mount(): void
    {
        $this->receivedAt = today()->toDateString();
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
            'description' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'incomeCategoryId' => ['required', 'exists:income_categories,id'],
            'receivedAt' => ['required', 'date'],
        ];
    }

    protected array $validationAttributes = [
        'description' => 'descrição',
        'value' => 'valor',
        'incomeCategoryId' => 'categoria',
        'receivedAt' => 'data',
    ];


    public function saveIncome(): void
    {
        $data = $this->validate();

        $attributes = [
            'description' => $data['description'],
            'value' => $data['value'],
            'income_category_id' => $data['incomeCategoryId'],
            'received_at' => $data['receivedAt'],
        ];

        if ($this->editingId) {
            Income::findOrFail($this->editingId)->update($attributes);
        } else {
            Income::create($attributes);
        }

        $this->resetForm();
    }

    public function editIncome(int $id): void
    {
        $income = Income::findOrFail($id);

        $this->editingId = $income->id;
        $this->description = $income->description;
        $this->value = (string) $income->value;
        $this->incomeCategoryId = $income->income_category_id;
        $this->receivedAt = $income->received_at->toDateString();
        $this->resetValidation();
    }

    public function deleteIncome(int $id): void
    {
        Income::findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'description', 'value', 'incomeCategoryId']);
        $this->receivedAt = today()->toDateString();
        $this->resetValidation();
    }

    #[On('income-categories-updated')]
    public function refreshCategories(): void
    {
        if ($this->incomeCategoryId && ! IncomeCategory::whereKey($this->incomeCategoryId)->exists()) {
            $this->incomeCategoryId = null;
        }

        if ($this->filterCategoryId && ! IncomeCategory::whereKey($this->filterCategoryId)->exists()) {
            $this->filterCategoryId = null;
            $this->resetPage();
        }
    }

    public function render()
    {
        // Base filtrada — reaproveitada na tabela (paginada) e nos agregados (totais).
        $base = Income::query()
            ->when($this->search !== '', fn ($q) => $q->where('description', 'like', '%'.$this->search.'%'))
            ->when($this->filterCategoryId, fn ($q) => $q->where('income_category_id', $this->filterCategoryId));

        $incomes = (clone $base)
            ->with(['category'])
            ->latest('received_at')
            ->latest('id')
            ->paginate(10);

        // Totais consideram TODOS os registros filtrados, não só a página atual.
        $total = (clone $base)->sum('value');

        return view('livewire.finance.income-manager', [
            'incomes' => $incomes,
            'categories' => IncomeCategory::orderBy('name')->get(),
            'total' => $total,
        ]);
    }
}
