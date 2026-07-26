<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\On;
use Livewire\Component;
use Native\Desktop\Facades\App;
use Native\Desktop\Facades\AutoUpdater;

/**
 * Controle manual do auto-update do NativePHP.
 *
 * O Electron já checa sozinho no boot (checkForUpdatesAndNotify), então esta
 * tela é o caminho manual: verificar agora, baixar e reiniciar para instalar.
 *
 * O estado vem dos eventos que o processo Electron empurra para o renderer —
 * o preload livewire-dispatcher.js os reemite como 'native:<FQCN do evento>'.
 * Por isso não há polling nem arquivo de status em disco.
 */
class UpdateManager extends Component
{
    public string $estado = 'ocioso';

    public ?string $versaoDisponivel = null;

    public int $progresso = 0;

    public ?string $mensagemErro = null;

    public string $versaoAtual = '';

    public bool $rodandoNoDesktop = false;

    public function mount(): void
    {
        // env() direto é furado aqui: o app empacotado roda `artisan optimize`
        // no boot e cacheia o config, então uma leitura de env() pode não
        // refletir mais nada depois disso. config('nativephp-internal.running')
        // é o valor já resolvido no cache.
        $this->rodandoNoDesktop = (bool) config('nativephp-internal.running');

        // NATIVEPHP_APP_VERSION só existe no ambiente do build (é isso que
        // vira config('nativephp.version')) — nada escreve esse valor no .env
        // empacotado, então ele fica congelado em '0.0.0' pra sempre depois do
        // primeiro `artisan optimize`. No app instalado, a versão de verdade
        // vem do próprio Electron via App::version(); fora dele (make web,
        // testes) não há Electron para perguntar, então caímos no config.
        $this->versaoAtual = $this->rodandoNoDesktop
            ? App::version()
            : (string) config('nativephp.version');
    }

    public function verificar(): void
    {
        $this->mensagemErro = null;
        $this->estado = 'verificando';

        AutoUpdater::checkForUpdates();
    }

    public function baixar(): void
    {
        $this->estado = 'baixando';
        $this->progresso = 0;

        AutoUpdater::downloadUpdate();
    }

    public function instalar(): void
    {
        // Fecha o app e reabre já atualizado; não há o que salvar antes.
        AutoUpdater::quitAndInstall();
    }

    #[On('native:Native\\Desktop\\Events\\AutoUpdater\\CheckingForUpdate')]
    public function aoVerificar(): void
    {
        $this->estado = 'verificando';
    }

    #[On('native:Native\\Desktop\\Events\\AutoUpdater\\UpdateAvailable')]
    public function aoEncontrarAtualizacao(?string $version = null): void
    {
        $this->estado = 'disponivel';
        $this->versaoDisponivel = $version;
    }

    #[On('native:Native\\Desktop\\Events\\AutoUpdater\\UpdateNotAvailable')]
    public function aoNaoEncontrarAtualizacao(): void
    {
        $this->estado = 'atualizado';
    }

    #[On('native:Native\\Desktop\\Events\\AutoUpdater\\DownloadProgress')]
    public function aoProgredirDownload(float $percent = 0): void
    {
        $this->estado = 'baixando';
        $this->progresso = (int) floor($percent);
    }

    #[On('native:Native\\Desktop\\Events\\AutoUpdater\\UpdateDownloaded')]
    public function aoConcluirDownload(?string $version = null): void
    {
        $this->estado = 'pronto';
        $this->versaoDisponivel = $version ?? $this->versaoDisponivel;
        $this->progresso = 100;
    }

    #[On('native:Native\\Desktop\\Events\\AutoUpdater\\Error')]
    public function aoFalhar(?string $message = null): void
    {
        $this->estado = 'erro';
        $this->mensagemErro = $message ?: 'Não foi possível falar com o servidor de atualizações.';
    }

    #[On('native:Native\\Desktop\\Events\\AutoUpdater\\UpdateCancelled')]
    public function aoCancelarDownload(?string $version = null): void
    {
        // Sem isso o estado trava em "baixando" para sempre: um download
        // cancelado (ex.: perda de conexão) não dispara Error nem
        // UpdateDownloaded, só este evento. A atualização continua
        // disponível, só o download é que parou.
        $this->estado = 'disponivel';
        $this->versaoDisponivel = $version ?? $this->versaoDisponivel;
        $this->progresso = 0;
    }

    public function render()
    {
        return view('livewire.settings.update-manager');
    }
}
