<?php

namespace App\Support;

use Native\Desktop\Facades\Window;

/**
 * Maximiza a janela principal na primeira vez que ela aparece.
 *
 * O ->maximized() do NativePHP não serve no Linux. Ele registra um afterOpen,
 * então o maximize sai logo depois do window/open — e nesse instante a janela
 * ainda está com show:false, porque o electron-plugin só chama show() no
 * did-finish-load. O gerenciador de janelas ignora a maximização de uma janela
 * que ainda não foi mapeada e o Electron ainda perde as dimensões pedidas no
 * caminho: medido no Ubuntu/X11, a janela abria em 1440x749 em (240,155), em vez
 * dos 1920x1011 da área de trabalho, e o _NET_WM_STATE saía vazio — nem
 * maximizada, nem do tamanho certo.
 *
 * Com a janela já visível o mesmo maximize funciona (_NET_WM_STATE_MAXIMIZED_HORZ
 * e _VERT aparecem). É por isso que aqui ele vem do evento WindowShown, e não do
 * builder.
 */
class JanelaPrincipal
{
    public const ID = 'main';

    /**
     * Marcador de execução, não de versão: existe só para dizer "esta janela já
     * foi maximizada nesta sessão do app". Fica ao lado do orbit-version do
     * CompiledViews, fora de config('view.compiled') pelo mesmo motivo — o
     * view:clear apaga tudo que estiver lá dentro.
     */
    public static function marcador(): string
    {
        return storage_path('orbit-janela-maximizada');
    }

    /**
     * Chamado no boot do app nativo, antes de abrir a janela, para que cada
     * execução maximize uma vez.
     */
    public static function esquecer(): void
    {
        @unlink(static::marcador());
    }

    /**
     * O WindowShown dispara a cada did-finish-load, ou seja, em todo carregamento
     * de página cheia — inclusive nos full-reload que o vite manda no make dev.
     * Sem o marcador, uma janela que o usuário desmaximizou de propósito voltaria
     * a maximizar sozinha na navegação seguinte.
     *
     * @return bool se maximizou agora
     */
    public static function maximizarUmaVez(string $id): bool
    {
        if ($id !== static::ID || is_file(static::marcador())) {
            return false;
        }

        touch(static::marcador());

        Window::maximize(static::ID);

        return true;
    }
}
