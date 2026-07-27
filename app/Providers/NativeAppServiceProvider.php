<?php

namespace App\Providers;

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
        $area = Screen::primary()['workArea'];

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
