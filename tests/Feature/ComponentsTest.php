<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ComponentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_orbit_block_renders_modern_header(): void
    {
        $this->actingAs(User::factory()->create(['theme' => 'modern']));

        $html = Blade::render('<x-orbit-block title="Resumo">conteudo</x-orbit-block>');

        $this->assertStringContainsString('Resumo', $html);
        $this->assertStringContainsString('conteudo', $html);
        $this->assertStringNotContainsString('orbit-block-title', $html);
    }

    public function test_orbit_block_renders_retro_terminal_frame(): void
    {
        $this->actingAs(User::factory()->create(['theme' => 'retro']));

        $html = Blade::render('<x-orbit-block title="Resumo">conteudo</x-orbit-block>');

        $this->assertStringContainsString('RESUMO', $html);
        $this->assertStringContainsString('orbit-block-title', $html);
        $this->assertStringContainsString('orbit-block-footer', $html);
    }

    public function test_summary_card_modern_shows_icon_and_label(): void
    {
        $this->actingAs(User::factory()->create(['theme' => 'modern']));

        $html = Blade::render('<x-home.summary-card label="Saldo do mês" value="R$ —,——" icon="◎"/>');

        $this->assertStringContainsString('Saldo do mês', $html);
        $this->assertStringContainsString('R$ —,——', $html);
        $this->assertStringContainsString('◎', $html);
    }

    public function test_summary_card_retro_uses_bracketed_label_without_icon(): void
    {
        $this->actingAs(User::factory()->create(['theme' => 'retro']));

        $html = Blade::render('<x-home.summary-card label="Saldo do mês" value="R$ —,——" icon="◎"/>');

        $this->assertStringContainsString('[SALDO DO MÊS]', $html);
        $this->assertStringNotContainsString('◎', $html);
    }
}
