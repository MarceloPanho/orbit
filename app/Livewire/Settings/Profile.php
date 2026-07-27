<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Validate;
use Livewire\Component;

class Profile extends Component
{
    // max:255 espelha o `string` da coluna: sem isso o SQLite trunca calado.
    #[Validate('nullable|string|max:255')]
    public string $nome = '';

    public bool $salvo = false;

    public function mount(): void
    {
        $this->nome = auth()->user()?->name ?? '';
    }

    public function salvar(): void
    {
        $this->validate();

        $this->nome = trim($this->nome);

        auth()->user()->update(['name' => $this->nome]);

        $this->salvo = true;
    }

    public function updatedNome(): void
    {
        $this->salvo = false;
    }

    public function render()
    {
        return view('livewire.settings.profile');
    }
}
