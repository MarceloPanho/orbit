<?php

namespace Tests\Feature;

use App\Livewire\Settings\UpdateManager;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateManagerTest extends TestCase
{
    public function test_tempo_esgotado_transforma_verificacao_pendurada_em_erro(): void
    {
        // O bug do pacote Windows: o livewire-dispatcher.js não entrava no
        // bundle, nenhum evento do Electron chegava e a tela ficava em
        // "Verificando…" para sempre — sem erro, sem pista. O timeout é a
        // única saída desse estado quando a ponte de eventos está morta.
        Livewire::test(UpdateManager::class)
            ->set('estado', 'verificando')
            ->call('tempoEsgotado')
            ->assertSet('estado', 'erro')
            ->assertSet('mensagemErro', 'A verificação não respondeu a tempo. Detalhes em updater.log, na pasta de dados do app.');
    }

    public function test_tempo_esgotado_ignora_timer_atrasado(): void
    {
        // Cada render em 'verificando' arma um timer novo. Sem esta guarda, um
        // timer velho disparando depois de o evento ter chegado transformaria
        // uma verificação bem-sucedida em erro na cara do usuário.
        Livewire::test(UpdateManager::class)
            ->set('estado', 'disponivel')
            ->set('versaoDisponivel', '0.1.3')
            ->call('tempoEsgotado')
            ->assertSet('estado', 'disponivel')
            ->assertSet('mensagemErro', null);
    }

    public function test_evento_de_atualizacao_disponivel_sai_do_estado_verificando(): void
    {
        // O caminho feliz que o Windows nunca alcançou: é este evento, vindo do
        // Electron pelo livewire-dispatcher.js, que tira a tela do "Verificando…".
        Livewire::test(UpdateManager::class)
            ->set('estado', 'verificando')
            ->call('aoEncontrarAtualizacao', '0.1.3')
            ->assertSet('estado', 'disponivel')
            ->assertSet('versaoDisponivel', '0.1.3');
    }
}
