<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_modern_theme_without_terminal_chrome(): void
    {
        User::factory()->create(['theme' => 'modern']);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('class="modern"', false)
            ->assertDontSee('orbit-scanlines', false)
            ->assertDontSee('ORBIT_OS v0.1.0')
            ->assertDontSee('DISK:LOCAL');
    }

    public function test_renders_retro_theme_with_terminal_chrome(): void
    {
        User::factory()->create(['theme' => 'retro']);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('class="retro"', false)
            ->assertSee('orbit-scanlines', false)
            ->assertSee('ORBIT_OS v0.1.0')
            ->assertSee('[ PERSONAL OPERATING SYSTEM ]')
            ->assertSee('DISK:LOCAL')
            ->assertSee('NET:OFF')
            ->assertSee('PRIV:MAX');
    }

    public function test_renders_modern_theme_when_no_user_exists(): void
    {
        $this->get('/')->assertOk()->assertSee('class="modern"', false);
    }

    public function test_home_shows_greeting_and_summary_cards(): void
    {
        User::factory()->create();

        $this->get('/')
            ->assertSee('Bom dia, Marcelo.')
            ->assertSee('Terça-feira, 1 de julho de 2025')
            ->assertSee('Saldo do mês')
            ->assertSee('Próximo evento')->assertSee('Nenhum evento hoje')
            ->assertSee('Hábitos hoje')->assertSee('0 / 0 concluídos')
            ->assertSee('Notas recentes')->assertSee('Nenhuma nota recente')
            ->assertSee('Selecione um módulo na barra lateral para começar.');
    }
}
