<?php

namespace App\Providers;

use Native\Desktop\Facades\Window;
use Native\Desktop\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        // hideMenu() esconde a barra (File, Edit...) sem destruir o menu da
        // aplicação — os atalhos de zoom do Electron (Ctrl+=/Ctrl+-/Ctrl+0)
        // vêm do menu View e continuam funcionando.
        Window::open()
            ->hideMenu();
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
