<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoLoginLocalUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_automatically_logged_in_as_local_user(): void
    {
        $user = User::factory()->create();

        $this->get('/')->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_local_user_is_created_automatically_when_database_is_empty(): void
    {
        $this->assertSame(0, User::count());

        $this->get('/')->assertOk();

        $this->assertSame(1, User::count());
        $this->assertAuthenticated();
        $this->assertSame('modern', auth()->user()->theme);
    }

    public function test_theme_can_be_switched_even_on_fresh_database(): void
    {
        // cenário do app desktop: banco recém-migrado, sem seed
        $this->get('/settings')->assertOk();

        \Livewire\Livewire::actingAs(User::first())
            ->test(\App\Livewire\Settings\ThemeSwitcher::class)
            ->call('setTheme', 'retro')
            ->assertRedirect('/');

        $this->assertSame('retro', User::first()->theme);
    }
}
