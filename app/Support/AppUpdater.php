<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;

/**
 * Ponte entre a tela de Configurações e os scripts de atualização.
 *
 * A lógica de git vive nos scripts (scripts/check-update.sh e scripts/update.sh),
 * que também são usados pelo launcher e pelo `make update`. Aqui só lemos o
 * status que o launcher gravou no boot e disparamos os scripts.
 */
class AppUpdater
{
    private const STATUS_PATH = 'app/orbit-update.json';

    /** Status gravado pela última checagem; null se nunca checou. */
    public function status(): ?array
    {
        $file = storage_path(self::STATUS_PATH);

        if (! is_file($file)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($file), true);

        return is_array($data) ? $data : null;
    }

    /** Refaz a checagem em foreground (botão "verificar agora"). */
    public function checkNow(): ?array
    {
        Process::path(base_path())
            ->timeout(30)
            ->run(base_path('scripts/check-update.sh'));

        return $this->status();
    }

    public function behind(): int
    {
        return (int) ($this->status()['behind'] ?? 0);
    }

    public function isDirty(): bool
    {
        return (bool) ($this->status()['dirty'] ?? false);
    }

    public function hasUpdate(): bool
    {
        return $this->behind() > 0;
    }

    /** Só atualiza com árvore limpa: o pull nunca deve engolir trabalho local. */
    public function canUpdate(): bool
    {
        return $this->hasUpdate() && ! $this->isDirty();
    }

    public function checkedAt(): ?Carbon
    {
        $at = $this->status()['checked_at'] ?? null;

        return $at ? Carbon::parse($at) : null;
    }

    /**
     * Dispara a atualização desacoplada do app. setsid --fork tira o processo
     * do grupo do PHP, para ele sobreviver ao App::quit() que vem em seguida.
     */
    public function launchUpdate(): void
    {
        Process::path(base_path())->run(
            'setsid --fork bash scripts/update.sh --relaunch >/dev/null 2>&1'
        );
    }

    /** Fora do desktop (make web) não há janela para fechar. */
    public function runningInDesktop(): bool
    {
        return (bool) env('NATIVEPHP_RUNNING');
    }
}
