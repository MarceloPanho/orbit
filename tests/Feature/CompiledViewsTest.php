<?php

namespace Tests\Feature;

use App\Support\CompiledViews;
use Tests\TestCase;

class CompiledViewsTest extends TestCase
{
    private string $compilado;

    protected function setUp(): void
    {
        parent::setUp();

        // Diretório compilado próprio: o view:clear apaga tudo que estiver nele,
        // e apontar para o storage do repositório destruiria o cache real a cada
        // execução da suíte.
        $this->compilado = storage_path('framework/testing/views-'.uniqid());
        @mkdir($this->compilado, 0755, true);

        config(['view.compiled' => $this->compilado]);

        @unlink(CompiledViews::marcador());
    }

    protected function tearDown(): void
    {
        foreach (glob($this->compilado.'/*') ?: [] as $arquivo) {
            @unlink($arquivo);
        }

        @rmdir($this->compilado);
        @unlink(CompiledViews::marcador());

        parent::tearDown();
    }

    private function viewCompiladaFalsa(): string
    {
        $caminho = $this->compilado.'/abc123.php';
        file_put_contents($caminho, '<?php /* view da versão anterior */');

        return $caminho;
    }

    public function test_limpa_quando_nao_ha_marcador(): void
    {
        // Primeiro boot depois de instalar por cima de uma versão que não
        // gravava marcador — precisa limpar por precaução.
        $view = $this->viewCompiladaFalsa();

        $this->assertTrue(CompiledViews::limparSeVersaoMudou('0.1.1'));
        $this->assertFileDoesNotExist($view);
    }

    public function test_limpa_quando_a_versao_muda(): void
    {
        // É o caso que quebrou a v0.1.1: o cache trazia a view da v0.1.0 e ela
        // era renderizada contra as classes novas, dando "Undefined variable".
        CompiledViews::limparSeVersaoMudou('0.1.0');

        $view = $this->viewCompiladaFalsa();

        $this->assertTrue(CompiledViews::limparSeVersaoMudou('0.1.1'));
        $this->assertFileDoesNotExist($view);
        $this->assertSame('0.1.1', file_get_contents(CompiledViews::marcador()));
    }

    public function test_nao_limpa_quando_a_versao_e_a_mesma(): void
    {
        // Sem esta guarda, todo boot descartaria o cache e a primeira navegação
        // pagaria a recompilação de todas as views.
        CompiledViews::limparSeVersaoMudou('0.1.1');

        $view = $this->viewCompiladaFalsa();

        $this->assertFalse(CompiledViews::limparSeVersaoMudou('0.1.1'));
        $this->assertFileExists($view);
    }

    public function test_marcador_sobrevive_ao_view_clear(): void
    {
        // O ViewClearCommand faz glob("{$path}/*") e apaga tudo — arquivos e
        // subdiretórios. Se o marcador morasse ali, ele sumiria junto e a
        // limpeza voltaria a rodar em todo boot, para sempre.
        $this->assertStringNotContainsString(
            realpath($this->compilado),
            realpath(dirname(CompiledViews::marcador())) ?: dirname(CompiledViews::marcador()),
            'O marcador de versão não pode ficar dentro de config("view.compiled").'
        );

        CompiledViews::limparSeVersaoMudou('0.1.1');

        $this->assertFileExists(CompiledViews::marcador());
    }
}
