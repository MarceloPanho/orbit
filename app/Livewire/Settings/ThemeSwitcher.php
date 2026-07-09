<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class ThemeSwitcher extends Component
{
    public string $currentTheme;

    public function mount(): void
    {
        $this->currentTheme = auth()->user()?->theme ?? 'modern';
    }

    public function setTheme(string $theme): void
    {
        if (! in_array($theme, ['modern', 'retro'])) {
            return;
        }

        auth()->user()->update(['theme' => $theme]);
        $this->currentTheme = $theme;

        // recarrega a página para aplicar a classe no <html>
        $this->redirect(request()->header('Referer') ?? '/');
    }

    public function render()
    {
        return view('livewire.settings.theme-switcher');
    }
}
