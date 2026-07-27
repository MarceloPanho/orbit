<x-layouts.app>
    <x-sidebar active="settings" />

    <div style="flex:1; overflow-y:auto; padding:32px;">
        <x-ui.page-header title="Configurações" subtitle="Perfil, atualizações e tema visual do Orbit." />

        <div style="margin-top:24px;">
            <livewire:settings.profile />
        </div>

        <div style="margin-top:24px;">
            <livewire:settings.update-manager />
        </div>

        <div style="margin-top:24px;">
            <livewire:settings.theme-switcher />
        </div>
    </div>
</x-layouts.app>
