{{-- Controles de paginação no estilo orbit.
     Uso: <x-ui.pagination :paginator="$items" />
     Espera um LengthAwarePaginator (resultado de ->paginate()).
     Os botões chamam previousPage/nextPage/gotoPage do trait WithPagination.
     pageName acompanha o usado no ->paginate(); só precisa ser informado quando
     há mais de um paginador na mesma tela (senão os dois disputam o ?page= da URL). --}}
@props(['paginator', 'pageName' => 'page'])

@if($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last    = $paginator->lastPage();
        $start   = max(1, $current - 1);
        $end     = min($last, $current + 1);

        $btn = 'display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; padding:0 8px;'
            . ' font-size:12px; font-family:inherit; border-radius:var(--orbit-radius-sm);'
            . ' border:var(--orbit-border-width) solid var(--orbit-border); background:transparent;'
            . ' color:var(--orbit-fg-muted); cursor:pointer;';
        $active   = 'border-color:color-mix(in srgb, var(--orbit-accent) 45%, transparent);'
            . ' background:color-mix(in srgb, var(--orbit-accent) 14%, transparent); color:var(--orbit-accent);';
        $disabled = 'opacity:.4; cursor:not-allowed;';
        $dots     = 'display:inline-flex; align-items:center; padding:0 4px; color:var(--orbit-fg-subtle); font-size:12px;';
    @endphp

    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:12px 16px; border-top:0.5px solid var(--orbit-border);">

        <div style="font-size:12px; color:var(--orbit-fg-subtle);">
            Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </div>

        <div style="display:flex; align-items:center; gap:4px;">
            {{-- Anterior --}}
            <button type="button"
                @unless($paginator->onFirstPage()) wire:click="previousPage('{{ $pageName }}')" @endunless
                @disabled($paginator->onFirstPage())
                style="{{ $btn }}{{ $paginator->onFirstPage() ? $disabled : '' }}"
                aria-label="Página anterior">‹</button>

            {{-- Primeira página + reticências --}}
            @if($start > 1)
                <button type="button" wire:click="gotoPage(1, '{{ $pageName }}')" style="{{ $btn }}">1</button>
                @if($start > 2)<span style="{{ $dots }}">…</span>@endif
            @endif

            {{-- Janela ao redor da página atual --}}
            @for($page = $start; $page <= $end; $page++)
                @if($page === $current)
                    <span style="{{ $btn }}{{ $active }}" aria-current="page">{{ $page }}</span>
                @else
                    <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" style="{{ $btn }}">{{ $page }}</button>
                @endif
            @endfor

            {{-- Reticências + última página --}}
            @if($end < $last)
                @if($end < $last - 1)<span style="{{ $dots }}">…</span>@endif
                <button type="button" wire:click="gotoPage({{ $last }}, '{{ $pageName }}')" style="{{ $btn }}">{{ $last }}</button>
            @endif

            {{-- Próxima --}}
            <button type="button"
                @if($paginator->hasMorePages()) wire:click="nextPage('{{ $pageName }}')" @endif
                @disabled(! $paginator->hasMorePages())
                style="{{ $btn }}{{ $paginator->hasMorePages() ? '' : $disabled }}"
                aria-label="Próxima página">›</button>
        </div>
    </div>
@endif
