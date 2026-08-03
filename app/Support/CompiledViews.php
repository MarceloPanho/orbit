<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;

/**
 * Descarta as views compiladas quando a versão instalada muda.
 *
 * O NativePHP aponta o VIEW_COMPILED_PATH para dentro do userData — a mesma
 * pasta que guarda o banco e que sobrevive de propósito às atualizações. Só que
 * o Blade recompila comparando mtime: se o arquivo compilado for mais novo que
 * o .blade.php, ele considera o cache válido. Numa atualização isso acontece
 * sozinho quando a versão anterior ainda estava aberta enquanto os arquivos
 * novos chegavam ao disco.
 *
 * O sintoma é uma view da versão anterior sendo renderizada contra as classes
 * da nova. Foi assim que a v0.1.1 quebrou a tela de Configurações com
 * "Undefined variable $rodandoNoDesktop": o pacote trazia o blade certo, mas o
 * cache servia o antigo.
 */
class CompiledViews
{
    /**
     * Fora de config('view.compiled') de propósito: o view:clear faz
     * glob("{$path}/*") e apaga tudo que estiver lá dentro, inclusive
     * subdiretórios. Um marcador ali seria apagado junto e a limpeza passaria a
     * rodar em todo boot.
     */
    public static function marcador(): string
    {
        return storage_path('orbit-version');
    }

    /**
     * @return bool se limpou agora
     */
    public static function limparSeVersaoMudou(string $versao): bool
    {
        $marcador = static::marcador();

        if (is_file($marcador) && file_get_contents($marcador) === $versao) {
            return false;
        }

        Artisan::call('view:clear');

        file_put_contents($marcador, $versao);

        return true;
    }
}
