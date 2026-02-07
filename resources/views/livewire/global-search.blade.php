<div class="app-global-search" x-data="{ open: false }" x-on:click.away="open = false" x-on:global-search-updated.window="open = true">
    <div class="relative">
        <input
            type="text"
            wire:model.live.debounce.300ms="query"
            placeholder="Recherche globale..."
            data-global-search-input
            class="app-input app-global-search-input"
            x-on:focus="open = true"
        />
        <span class="app-global-search-hint">Ctrl + K</span>
    </div>

    <div x-show="open" x-transition class="app-global-search-panel" style="display: none;">
        @if ($results->isEmpty())
            <div class="px-4 py-3 text-sm text-slate-500">Aucun resultat.</div>
        @else
            @foreach ($results as $result)
                <a href="{{ $result['url'] }}" wire:navigate class="app-global-search-item">
                    <span class="app-global-search-type">{{ $result['type'] }}</span>
                    <div class="flex-1">
                        <div class="font-semibold text-slate-900">{{ $result['label'] }}</div>
                        @if ($result['meta'])
                            <div class="text-xs text-slate-500">{{ $result['meta'] }}</div>
                        @endif
                    </div>
                </a>
            @endforeach
        @endif
    </div>
</div>
