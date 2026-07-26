<?php

namespace Tests\Feature;

use App\Livewire\Settings\UpdateManager;
use Livewire\Livewire;
use Native\Desktop\Events\AutoUpdater\CheckingForUpdate;
use Native\Desktop\Events\AutoUpdater\DownloadProgress;
use Native\Desktop\Events\AutoUpdater\Error;
use Native\Desktop\Events\AutoUpdater\UpdateAvailable;
use Native\Desktop\Events\AutoUpdater\UpdateCancelled;
use Native\Desktop\Events\AutoUpdater\UpdateDownloaded;
use Native\Desktop\Events\AutoUpdater\UpdateNotAvailable;
use Native\Desktop\Facades\App;
use Native\Desktop\Facades\AutoUpdater;
use Tests\TestCase;

class UpdateManagerTest extends TestCase
{
    public function test_comeca_ocioso_mostrando_a_versao_atual(): void
    {
        // Fora do app empacotado (config('nativephp-internal.running') falso
        // por padrão nos testes) não há Electron para perguntar a versão, então
        // este é o caminho de fallback: config('nativephp.version') direto.
        config(['nativephp.version' => '1.2.3']);

        Livewire::test(UpdateManager::class)
            ->assertSet('estado', 'ocioso')
            ->assertSet('versaoAtual', '1.2.3')
            ->assertSet('rodandoNoDesktop', false);
    }

    public function test_no_app_empacotado_a_versao_vem_do_electron(): void
    {
        // config('nativephp.version') é congelado em build-time e nunca reflete
        // uma atualização instalada depois; no app empacotado a fonte de
        // verdade é o próprio Electron via App::version().
        config(['nativephp-internal.running' => true, 'nativephp.version' => '0.0.0']);
        App::shouldReceive('version')->once()->andReturn('2.5.0');

        Livewire::test(UpdateManager::class)
            ->assertSet('rodandoNoDesktop', true)
            ->assertSet('versaoAtual', '2.5.0');
    }

    public function test_evento_de_verificando_muda_o_estado(): void
    {
        Livewire::test(UpdateManager::class)
            ->dispatch('native:'.ltrim(CheckingForUpdate::class, '\\'))
            ->assertSet('estado', 'verificando');
    }

    public function test_evento_de_atualizacao_disponivel_expoe_a_versao(): void
    {
        // O nome vem da própria classe do evento (sem barra invertida na frente):
        // o preload livewire-dispatcher.js do Electron já remove essa barra antes
        // de despachar para o Livewire, então testar o literal manualmente aqui
        // só provaria "o componente escuta o que o componente escuta".
        Livewire::test(UpdateManager::class)
            ->dispatch('native:'.ltrim(UpdateAvailable::class, '\\'), version: '2.0.0')
            ->assertSet('estado', 'disponivel')
            ->assertSet('versaoDisponivel', '2.0.0');
    }

    public function test_evento_de_nenhuma_atualizacao_volta_para_ocioso(): void
    {
        Livewire::test(UpdateManager::class)
            ->set('estado', 'verificando')
            ->dispatch('native:'.ltrim(UpdateNotAvailable::class, '\\'), version: '1.0.0')
            ->assertSet('estado', 'atualizado');
    }

    public function test_progresso_do_download_e_refletido(): void
    {
        Livewire::test(UpdateManager::class)
            ->dispatch('native:'.ltrim(DownloadProgress::class, '\\'), percent: 42.7)
            ->assertSet('estado', 'baixando')
            ->assertSet('progresso', 42);
    }

    public function test_download_concluido_libera_a_instalacao(): void
    {
        Livewire::test(UpdateManager::class)
            ->dispatch('native:'.ltrim(UpdateDownloaded::class, '\\'), version: '2.0.0')
            ->assertSet('estado', 'pronto');
    }

    public function test_erro_e_exibido_sem_derrubar_o_componente(): void
    {
        Livewire::test(UpdateManager::class)
            ->dispatch('native:'.ltrim(Error::class, '\\'), message: 'sem conexão')
            ->assertSet('estado', 'erro')
            ->assertSet('mensagemErro', 'sem conexão');
    }

    public function test_evento_de_cancelamento_tira_do_estado_baixando(): void
    {
        // Um download cancelado (ex.: perda de conexão) não dispara Error nem
        // UpdateDownloaded — sem este listener o estado travava em "baixando".
        Livewire::test(UpdateManager::class)
            ->set('estado', 'baixando')
            ->set('progresso', 40)
            ->dispatch('native:'.ltrim(UpdateCancelled::class, '\\'), version: '2.0.0')
            ->assertSet('estado', 'disponivel')
            ->assertSet('versaoDisponivel', '2.0.0')
            ->assertSet('progresso', 0);
    }

    public function test_fora_do_app_empacotado_o_updater_nao_opera(): void
    {
        // Em make web / make dev não há Electron: a tela precisa dizer isso em
        // vez de oferecer um botão morto.
        Livewire::test(UpdateManager::class)
            ->assertSet('rodandoNoDesktop', false);
    }

    public function test_verificar_chama_o_autoupdater_e_limpa_erro_anterior(): void
    {
        AutoUpdater::shouldReceive('checkForUpdates')->once();

        Livewire::test(UpdateManager::class)
            ->set('mensagemErro', 'erro antigo')
            ->call('verificar')
            ->assertSet('estado', 'verificando')
            ->assertSet('mensagemErro', null);
    }

    public function test_baixar_chama_o_autoupdater_e_zera_o_progresso(): void
    {
        AutoUpdater::shouldReceive('downloadUpdate')->once();

        Livewire::test(UpdateManager::class)
            ->set('progresso', 55)
            ->call('baixar')
            ->assertSet('estado', 'baixando')
            ->assertSet('progresso', 0);
    }

    public function test_instalar_chama_o_autoupdater_para_reiniciar(): void
    {
        AutoUpdater::shouldReceive('quitAndInstall')->once();

        Livewire::test(UpdateManager::class)
            ->call('instalar');
    }
}
