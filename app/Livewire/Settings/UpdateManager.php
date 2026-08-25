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

    public bool $appEmpacotado = false;

    public function mount(): void
    {

        $this->appEmpacotado = (bool) config('nativephp-internal.running')
            && config('app.env') === 'production';

        // NATIVEPHP_APP_VERSION só existe no ambiente do build (é isso que
        // vira config('nativephp.version')) — nada escreve esse valor no .env
        // empacotado, então ele fica congelado em '0.0.0' pra sempre depois do
        // primeiro `artisan optimize`. No app instalado, a versão de verdade
        // vem do próprio Electron via App::version(); fora dele (make web,
        // testes) não há Electron para perguntar, então caímos no config.
        $this->versaoAtual = $this->appEmpacotado
            ? App::version()
            : (string) config('nativephp.version');
    }

    public function verificar(): void
    {
        $this->mensagemErro = null;
        $this->estado = 'verificando';

        AutoUpdater::checkForUpdates();
    }

    /**
     * Rede de segurança para a verificação que nunca volta.
     *
     * O estado 'verificando' é escrito aqui no PHP; sair dele depende de um
     * evento do Electron. Se a ponte de eventos quebra — foi o que aconteceu
     * no pacote Windows, com o livewire-dispatcher.js faltando no bundle —
     * nenhum evento chega, nem o de erro, e a tela fica girando para sempre
     * sem dar pista nenhuma de que algo está errado.
     *
     * Checar versão baixa alguns KB de YAML; 30s é folga generosa.
     */
    public function tempoEsgotado(): void
    {
        // Chega chamada de timer velho depois de o estado já ter mudado —
        // ignorar é o certo, senão um evento que voltou vira erro na tela.
        if ($this->estado !== 'verificando') {
            return;
        }

        $this->estado = 'erro';
        $this->mensagemErro = 'A verificação não respondeu a tempo. Detalhes em updater.log, na pasta de dados do app.';
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
