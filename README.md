<p align="center">
  <img src="resources/icons/orbit.png" width="112" alt="Orbit">
</p>

<h1 align="center">Orbit</h1>

<p align="center">
  <strong>Sistema operacional pessoal.</strong><br>
  Finanças, agenda, notas e hábitos em um só lugar e 100% na sua máquina.
</p>

<p align="center">
  <a href="https://github.com/MarceloPanho/orbit/releases/latest"><img alt="Última versão" src="https://img.shields.io/github/v/release/MarceloPanho/orbit?style=flat-square&label=vers%C3%A3o&color=6d28d9"></a>
  <img alt="Plataformas" src="https://img.shields.io/badge/plataformas-Windows%20%7C%20Linux-333?style=flat-square">
  <img alt="Sem nuvem" src="https://img.shields.io/badge/dados-100%25%20locais-16a34a?style=flat-square">
</p>

<p align="center">
  <a href="#baixar">Baixar</a> ·
  <a href="#o-que-tem-dentro">O que tem dentro</a> ·
  <a href="#privacidade">Privacidade</a> ·
  <a href="#desenvolvimento">Desenvolvimento</a>
</p>

---

<p align="center">
  <img src="docs/media/orbit-preview.png" alt="A janela do Orbit" width="880">
</p>

---

## Baixar

Pegue o instalador da sua plataforma na **[página de Releases](https://github.com/MarceloPanho/orbit/releases/latest)** e execute.

| Plataforma | Arquivo a baixar | Como instalar |
|---|---|---|
| **Windows** 10/11 | `Orbit-x.y.z-setup.exe` | Duplo clique. Cria atalho na área de trabalho. |
| **Linux** (qualquer distro) | `Orbit-x.y.z.AppImage` | `chmod +x Orbit-*.AppImage` e execute. **Se atualiza sozinho.** |
| **Linux** (Debian/Ubuntu) | `orbit_x.y.z_amd64.deb` | `sudo apt install ./orbit_*.deb` — **pelo terminal**, veja abaixo. |

> [!IMPORTANT]
> **Ubuntu 24.04+: o AppImage precisa do FUSE 2**, que deixou de vir instalado.
> Sem ele o app não abre e o erro (`dlopen(): error loading libfuse.so.2`) parece
> defeito do programa, mas é só dependência:
>
> ```bash
> sudo apt install libfuse2t64   # 22.04 e anteriores: libfuse2
> ```
>
> **O `.deb` não instala com duplo clique** no 24.04+. A Central de Aplicativos
> virou snap-first e perdeu o plugin que trata `.deb` local, então o clique não
> faz nada — sem erro, sem janela. Instale pelo terminal com o comando da tabela,
> ou use o `gdebi` (`sudo apt install gdebi`) para ter um instalador gráfico.

> [!TIP]
> **No Linux, prefira o AppImage.** Ele se atualiza sem pedir senha e baixa só os
> blocos que mudaram. O `.deb` também recebe atualização automática pelo app, mas
> exige senha de administrador a cada uma (instala em `/opt`, via `pkexec dpkg -i`)
> e rebaixa o pacote inteiro, porque só o AppImage é publicado com blockmap.
>
> O AppImage, por outro lado, não cria ícone nem entrada de menu sozinho — é da
> natureza do formato. Para integrá-lo ao sistema (move para `~/Applications`,
> extrai o ícone oficial do bundle e cria o atalho no menu):
>
> ```bash
> curl -fsSLO https://raw.githubusercontent.com/MarceloPanho/orbit/main/scripts/install-appimage.sh
> bash install-appimage.sh ~/Downloads/Orbit-*.AppImage
> ```
>
> Quem já clonou o repositório roda direto: `./scripts/install-appimage.sh`.

> [!NOTE]
> **No Windows, o SmartScreen vai reclamar.** O instalador não é assinado
> digitalmente (o certificado é pago), então aparece *"O Windows protegeu o seu PC"*.
> Clique em **Mais informações** → **Executar assim mesmo**.

O primeiro boot leva de 30 a 60 segundos, o app está preparando o banco local.
As aberturas seguintes são imediatas.

### Atualizações

Depois de instalado, o Orbit cuida disso sozinho: ele checa por uma versão nova
ao abrir, baixa em segundo plano e instala no próximo reinício. Em
**Configurações → Atualizações** dá para forçar a verificação, acompanhar o
download e reiniciar na hora.

### Onde ficam os seus dados

Num banco SQLite **fora da pasta de instalação** `%APPDATA%\Orbit` no Windows,
`~/.config/Orbit` no Linux. Desinstalar e reinstalar preserva tudo; para fazer
backup, copie essa pasta.

## O que tem dentro

| | Status |
|---|---|
| 💸 **Finanças**: despesas, categorias de gasto e recebimentos | Disponível |
| 💸 **Finanças**: demais funcionalidades | Em construção |
| 🗓️ **Agenda** | Em construção |
| 📝 **Notas** | Em construção |
| 🔁 **Hábitos** | Em construção |

O Orbit é um app **desktop de verdade** (janela própria, atalho, ícone na barra),
não um site aberto no navegador. Ele roda inteiramente offline.

## Privacidade

`DISK:LOCAL · NET:MIN · PRIV:MAX`

Sem conta, sem login, sem telemetria, sem nuvem. Seus dados nunca saem da sua
máquina, ficam no SQLite local e ponto.

A **única** chamada de rede que o app faz é a checagem de nova versão contra as
Releases do GitHub, no boot e quando você clica em "verificar agora". É ela que
sustenta o auto-update. Fora isso, o Orbit não fala com ninguém.

## Desenvolvimento

**Requisitos:** PHP 8.3+ com `sqlite3`, `xml`, `curl` e `zip` · Composer 2 · Node.js 20+ · Git

```bash
git clone https://github.com/MarceloPanho/orbit.git
cd orbit
make install
```

Comandos do dia a dia:

```bash
make dev          # app desktop (NativePHP/Electron) + vite com hot reload
make web          # no navegador (http://localhost:8000)
make test         # roda a suíte de testes
make update       # atualiza este clone a partir do origin
make build-linux  # instalador local para TESTE — nunca distribua (leva sua APP_KEY junto)
make build-clean  # remove os artefatos de build
```

### Stack

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![NativePHP Desktop](https://img.shields.io/badge/NativePHP%20Desktop-2-47848F?style=flat-square&logo=electron&logoColor=white)](https://nativephp.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-8-646CFF?style=flat-square&logo=vite&logoColor=white)](https://vite.dev)
[![SQLite](https://img.shields.io/badge/SQLite-003B57?style=flat-square&logo=sqlite&logoColor=white)](https://sqlite.org)

O NativePHP embrulha o app num Electron e embarca o PHP junto é por isso que o
instalador não pede nenhuma dependência da sua máquina.
