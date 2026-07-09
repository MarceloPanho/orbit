<?php

namespace Tests\Feature;

use App\Livewire\Settings\ThemeSwitcher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ThemeSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_renders_both_theme_cards(): void
    {
        User::factory()->create();

        $this->get('/settings')
            ->assertOk()
            ->assertSeeLivewire(ThemeSwitcher::class)
            ->assertSee('Modern — minimalista escuro')
            ->assertSee('Retro — terminal CRT');
    }

    public function test_set_theme_persists_and_redirects(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ThemeSwitcher::class)
            ->call('setTheme', 'retro')
            ->assertRedirect('/');

        $this->assertSame('retro', $user->fresh()->theme);
    }

    public function test_invalid_theme_is_ignored(): void
    {
        $user = User::factory()->create(['theme' => 'modern']);

        Livewire::actingAs($user)
            ->test(ThemeSwitcher::class)
            ->call('setTheme', 'neon')
            ->assertNoRedirect();

        $this->assertSame('modern', $user->fresh()->theme);
    }
}
