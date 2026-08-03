<?php

namespace App\Livewire\Finance;

use App\Models\PaymentMethod;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentMethodManager extends Component
{
    use WithPagination;

    // Nome do paginador. Fixo e distinto de 'page' porque este componente também
    // roda embutido no ExpenseManager, que já publica o seu próprio ?page= na URL.
    private const PAGE_NAME = 'payPage';

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
                Rule::unique('payment_methods', 'name')->ignore($this->editingId),
            ],
        ];
    }

    protected array $validationAttributes = [
        'name' => 'nome',
    ];

    public function savePaymentMethod(): void
    {
        $data = $this->validate();

        $attributes = [
            'name' => $data['name'],
        ];

        if ($this->editingId) {
            PaymentMethod::findOrFail($this->editingId)->update($attributes);
        } else {
            PaymentMethod::create($attributes);
        }

        $this->resetForm();
        $this->dispatch('payment-method-updated');
    }

    public function editPaymentMethod(int $id): void
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        $this->editingId = $paymentMethod->id;
        $this->name = $paymentMethod->name;
        $this->resetValidation();
    }

    public function deletePaymentMethod(int $id): void
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        if ($paymentMethod->expenses()->exists()) {
            $this->dispatch('notify',
                type: 'error',
                title: 'Não foi possível excluir',
                message: "O método de pagamento «{$paymentMethod->name}» está vinculado a lançamentos e não pode ser excluída.",
            );

            return;
        }

        $paymentMethod->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        $this->dispatch('payment-method-updated');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'editingId']);
        $this->resetValidation();
    }

    // ================= Render =================

    public function render()
    {
        return view('livewire.finance.payment-method-manager', [
            'paymentMethods' => PaymentMethod::orderBy('name')->paginate(10, pageName: self::PAGE_NAME),
            'pageName' => self::PAGE_NAME,
        ]);
    }
}
