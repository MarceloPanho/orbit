<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_defaults_to_modern(): void
    {
        $user = User::factory()->create();

        $this->assertSame('modern', $user->fresh()->theme);
        $this->assertFalse($user->isRetro());
    }

    public function test_theme_is_mass_assignable(): void
    {
        $user = User::factory()->create();
        $user->update(['theme' => 'retro']);

        $this->assertSame('retro', $user->fresh()->theme);
        $this->assertTrue($user->fresh()->isRetro());
    }
}
