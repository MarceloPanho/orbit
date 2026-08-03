<?php

namespace App\Providers;

use App\Support\CompiledViews;
use App\Support\JanelaPrincipal;
use Native\Desktop\Facades\App;
use Native\Desktop\Facades\Screen;
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

        CompiledViews::limparSeVersaoMudou(App::version());

        JanelaPrincipal::esquecer();

        $area = Screen::primary()['workArea'];

        // Sem ->maximized() de propósito: ele maximiza cedo demais e quebra até o
        // dimensionamento. Quem maximiza é o JanelaPrincipal, no WindowShown.
        // As medidas abaixo são o estado restaurado e a garantia de que a janela
        // já nasce preenchendo a tela mesmo que o maximize falhe.
        Window::open()
            ->hideMenu()
            ->width($area['width'])
            ->height($area['height'])
            ->position($area['x'], $area['y']);
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
