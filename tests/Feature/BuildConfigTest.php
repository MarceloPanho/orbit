<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BuildConfigTest extends TestCase
{
    public function test_app_id_identifica_o_orbit(): void
    {
        // O app_id vira a pasta de dados em %APPDATA%/~/.config. Mudá-lo depois
        // de distribuir órfã o banco de quem já instalou.
        $this->assertSame('com.marcelopanho.orbit', config('nativephp.app_id'));
    }

    public function test_versao_vem_do_ambiente_de_build(): void
    {
        $this->assertSame('0.0.0', config('nativephp.version'));
    }

    public function test_icone_do_windows_esta_onde_o_builder_procura(): void
    {
        // InstallsAppIcon::installIcon() copia de public/, não de resources/icons/.
        $this->assertFileExists(public_path('icon.ico'));
        $this->assertFileExists(public_path('icon.png'));
    }

    public function test_env_example_usa_o_nome_correto_do_app(): void
    {
        // O workflow de release faz `cp .env.example .env` antes do build, e o
        // NativePHP deriva o nome do instalador, o atalho e a pasta de
        // userData (%APPDATA%/~/.config) de config('app.name'). Com
        // APP_NAME=Laravel aqui, o CI empacotaria "Laravel-1.0.0-setup.exe" e
        // guardaria o banco de todo mundo em %APPDATA%\Laravel — trocar isso
        // depois do primeiro release órfã os dados já instalados.
        $conteudo = file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString('APP_NAME=Orbit', $conteudo);
    }

    public function test_env_example_liga_o_updater_no_ambiente_de_build(): void
    {
        // config('nativephp.updater.enabled') tem default `true`, mas ele só
        // vale dentro do PHP. Quem decide se o instalador nasce atualizável é o
        // electron-builder.mjs do vendor, que lê a variável do PROCESSO:
        //
        //   const updaterEnabled = process.env.NATIVEPHP_UPDATER_ENABLED === 'true';
        //   ...(updaterEnabled ? { publish: updaterConfig } : {})
        //
        // Sem ela o bloco `publish` some inteiro: o app é empacotado sem
        // app-update.yml e a Release sai sem latest.yml/latest-linux.yml — que
        // é justamente o arquivo que o electron-updater busca. O app instala,
        // abre e nunca mais se atualiza, sem erro nenhum.
        //
        // O BuildCommand do NativePHP não exporta essa chave (só exporta
        // NATIVEPHP_UPDATER_CONFIG), então a única fonte é o .env — e o CI monta
        // o .env dele com `cp .env.example .env`.
        $conteudo = file_get_contents(base_path('.env.example'));

        $this->assertMatchesRegularExpression(
            '/^NATIVEPHP_UPDATER_ENABLED=true$/m',
            $conteudo,
            'Sem NATIVEPHP_UPDATER_ENABLED=true no .env.example o build sai sem `publish` e o auto-update morre em silêncio.'
        );
    }

    public function test_updater_aponta_para_o_repositorio_certo(): void
    {
        // Estes defaults são o que vale quando GITHUB_OWNER/GITHUB_REPO não
        // estão no ambiente (build local). Errados, o electron-updater
        // consultaria o feed de outro repositório.
        $github = config('nativephp.updater.providers.github');

        $this->assertSame('github', config('nativephp.updater.default'));
        $this->assertSame('MarceloPanho', $github['owner']);
        $this->assertSame('orbit', $github['repo']);
        // As tags do repositório são v1.2.3; sem o prefixo o updater procura
        // uma tag "1.2.3" que não existe.
        $this->assertTrue($github['vPrefixedTagName']);
    }

    #[DataProvider('chavesQueNaoPodemVazarParaOBundle')]
    public function test_chaves_de_desenvolvimento_sao_removidas_do_bundle(string $chave): void
    {
        $this->assertContains(
            $chave,
            config('nativephp.cleanup_env_keys'),
            "{$chave} precisa ser removida do .env empacotado — ver ManagesEnvFile::cleanEnvFile()."
        );
    }

    public static function chavesQueNaoPodemVazarParaOBundle(): array
    {
        return [
            // APP_DEBUG=true faria o app empacotado usar o SQLite de dentro do
            // asar, que é somente-leitura. Ausente, o default de config/app.php
            // (false) vale.
            ['APP_DEBUG'],
            // Ausente, config/app.php assume 'production'.
            ['APP_ENV'],
        ];
    }

    public function test_app_key_nao_e_removida_do_bundle(): void
    {
        // APP_KEY não pode ser removida: o app não sobe sem ela
        // (MissingAppKeyException). O NativePHP não gera chave em runtime.
        $this->assertNotContains(
            'APP_KEY',
            config('nativephp.cleanup_env_keys'),
            'APP_KEY deve permanecer no .env empacotado — o app não sobe sem ela.'
        );
    }

    public function test_config_app_mantem_os_defaults_de_que_a_remocao_depende(): void
    {
        // Remover APP_ENV/APP_DEBUG do bundle só é seguro enquanto estes forem
        // os defaults. Se alguém trocar para env('APP_DEBUG', true), o app
        // empacotado volta a apontar o banco para dentro do asar — em silêncio.
        $config = file_get_contents(config_path('app.php'));

        $this->assertStringContainsString("env('APP_ENV', 'production')", $config);
        $this->assertStringContainsString("env('APP_DEBUG', false)", $config);
    }
}
