<?php

namespace App\Providers;

use App\Support\JanelaPrincipal;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Native\Desktop\Events\Windows\WindowShown;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Aqui e não no NativeAppServiceProvider: aquele boot() roda uma vez só,
        // no POST /_native/api/booted. O WindowShown chega numa requisição
        // posterior (POST /_native/api/events), que bootstrapa o Laravel do zero
        // — um listener registrado lá não existiria mais. Registro explícito
        // porque o bootstrap/app.php não chama withEvents(), então não há
        // descoberta automática em app/Listeners.
        Event::listen(
            WindowShown::class,
            fn (WindowShown $event) => JanelaPrincipal::maximizarUmaVez($event->id),
        );
    }
}
