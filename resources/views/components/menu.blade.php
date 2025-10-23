@props([
    'title' => config('app.name', 'Portal Universo do Saber'),
    'items' => [],
    'current' => null,
    'collapsed' => false,
    'overlayOpen' => false,
])

@php
    $normalisedItems = [];
    foreach ($items as $item) {
        if (! is_array($item)) {
            continue;
        }

        $normalisedItems[] = [
            'label' => $item['label'] ?? ($item[0] ?? null),
            'url' => $item['url'] ?? ($item[1] ?? '#'),
            'icon' => $item['icon'] ?? ($item[2] ?? null),
            'active' => $item['active'] ?? ($item[3] ?? null),
        ];
    }

    $current = $current ?? request()->path();
@endphp

@php
    $sidebarClasses = [
        'layout-sidebar',
        'sidebar',
        'active' => $overlayOpen,
    ];
@endphp

<aside id="sidebar"
       data-menu-component
       @class($sidebarClasses)
       @if ($collapsed)
           data-sidebar-collapsed="1"
       @endif>
    <div class="layout-sidebar__header sidebar-header">
        <div class="layout-sidebar__title">
            <h3>{{ $title }}</h3>
        </div>
        <button class="layout-sidebar__close close-sidebar"
                id="close-sidebar"
                type="button"
                aria-label="Fechar menu">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <nav class="layout-sidebar__nav" aria-label="Menu principal">
        <ul class="layout-sidebar__list sidebar-menu">
            @foreach ($normalisedItems as $item)
                @php
                    $url = $item['url'] ?? '#';
                    $isActive = $item['active'] ?? false;
                    if (! $isActive) {
                        $isActive = $current === $url || url($url) === url()->current();
                    }
                @endphp
                <li class="layout-sidebar__item">
                    <a href="{{ $url }}" @class(['layout-sidebar__link', 'is-active' => $isActive])>
                        @if (! empty($item['icon']))
                            <i class="fa {{ $item['icon'] }}" aria-hidden="true"></i>
                        @endif
                        <span class="layout-sidebar__label">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</aside>
