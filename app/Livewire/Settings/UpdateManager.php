<?php

namespace App\Livewire\Settings;

use App\Support\AppUpdater;
use Livewire\Component;
use Native\Desktop\Facades\App as NativeApp;

class UpdateManager extends Component
{
    public bool $checking = false;

    public function check(AppUpdater $updater): void
    {
        $updater->checkNow();

        $this->dispatch('notify',
            type: $updater->hasUpdate() ? 'info' : 'success',
            title: $updater->hasUpdate() ? 'Atualização disponível' : 'Tudo em dia',
            message: $updater->hasUpdate()
                ? "Há {$updater->behind()} commit(s) novo(s) no GitHub."
                : 'Você já está na versão mais recente.',
        );
    }

    public function update(AppUpdater $updater): void
    {
        if (! $updater->canUpdate()) {
            $this->dispatch('notify',
                type: 'warning',
                title: 'Não é possível atualizar',
                message: $updater->isDirty()
                    ? 'Há alterações locais não commitadas neste clone.'
                    : 'Não há atualização disponível.',
            );

            return;
        }

        $updater->launchUpdate();

        // O script reescreve vendor/ e public/build; o app precisa sair de cena.
        // Ele mesmo reabre o Orbit pelo launcher ao terminar.
        if ($updater->runningInDesktop()) {
            NativeApp::quit();

            return;
        }

        $this->dispatch('notify',
            type: 'info',
            title: 'Atualizando',
            message: 'A atualização está rodando em segundo plano. Recarregue a página em instantes.',
        );
    }

    public function render(AppUpdater $updater)
    {
        return view('livewire.settings.update-manager', [
            'status' => $updater->status(),
            'behind' => $updater->behind(),
            'dirty' => $updater->isDirty(),
            'canUpdate' => $updater->canUpdate(),
            'checkedAt' => $updater->checkedAt(),
        ]);
    }
}
