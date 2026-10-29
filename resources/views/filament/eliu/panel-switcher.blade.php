@if (filament()->auth()->check() && $panels !== [])
    <nav class="eliu-panel-switcher" aria-label="Painéis">
        @foreach ($panels as $panel)
            <div @class(['fi-topbar-item', 'fi-active' => $panel['active']])>
                <a
                    href="{{ $panel['url'] }}"
                    class="fi-topbar-item-btn"
                    @if ($panel['active']) aria-current="page" @endif
                >
                    <span class="fi-topbar-item-label">{{ $panel['label'] }}</span>
                </a>
            </div>
        @endforeach
    </nav>
@endif
