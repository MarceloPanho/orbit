<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_lists_all_modules_and_children(): void
    {
        User::factory()->create();

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Finanças')->assertSee('Visão geral')->assertSee('Transações')
            ->assertSee('Orçamentos')->assertSee('Metas')
            ->assertSee('Agenda')->assertSee('Semana')->assertSee('Eventos')->assertSee('Tarefas')
            ->assertSee('Notas')->assertSee('Todas as notas')->assertSee('Por tag')->assertSee('Favoritas')
            ->assertSee('Hábitos')->assertSee('Histórico')
            ->assertSee('Dashboard')->assertSee('Configurações')->assertSee('Ajuda');
    }

    public function test_accordion_starts_with_financas_open(): void
    {
        User::factory()->create();

        $this->get('/')->assertSee("x-data=\"{ open: 'financas' }\"", false);
    }

    public function test_retro_sidebar_uses_terminal_wordmark(): void
    {
        User::factory()->create(['theme' => 'retro']);

        $this->get('/')->assertSee('ORBIT');
    }
}
