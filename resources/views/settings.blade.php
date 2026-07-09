<x-layouts.app>
    <x-sidebar active="settings" />

    <main style="flex:1; overflow-y:auto; padding:32px;">
        <h1 style="font-size:18px; font-weight:500; color:var(--orbit-fg); margin:0 0 4px;">Configurações</h1>
        <p style="font-size:12px; color:var(--orbit-fg-subtle); margin:0 0 24px;">Escolha o tema visual do Orbit.</p>

        <livewire:settings.theme-switcher />
    </main>
</x-layouts.app>
