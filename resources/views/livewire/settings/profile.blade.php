<div>
    <x-ui.card title="Perfil">
        <form wire:submit="salvar" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">

            <div style="flex:1; min-width:240px;">
                <x-ui.input
                    label="Seu nome"
                    wire:model.live.debounce.400ms="nome"
                    maxlength="255"
                    placeholder="Como devemos te chamar?"
                    autocomplete="off" />

                @error('nome')
                    <span style="display:block; margin-top:6px; font-size:12px; color:var(--orbit-warning);">{{ $message }}</span>
                @enderror
            </div>

            <div style="display:flex; align-items:center; gap:12px;">
                <x-ui.button type="submit" icon="✓">Salvar</x-ui.button>

                @if($salvo)
                    <span style="font-size:12px; color:var(--orbit-fg-subtle);">salvo</span>
                @endif
            </div>
        </form>

        <p style="margin:12px 0 0; font-size:12px; color:var(--orbit-fg-subtle); line-height:1.5;">
            É só o que aparece na saudação da tela inicial. Fica no banco local, como
            todo o resto — não é conta, não é login, não vai para lugar nenhum.
        </p>
    </x-ui.card>
</div>
