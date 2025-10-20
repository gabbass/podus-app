@props([
    'title' => config('app.name', 'Portal Universo do Saber'),
    'items' => [],
    'current' => null,
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

<aside class="sidebar active" id="sidebar" data-menu-component>
    <div class="sidebar-header">
        <h3>{{ $title }}</h3>
        <button class="close-sidebar" id="close-sidebar" type="button" aria-label="Fechar menu">
            &times;
        </button>
    </div>
    <ul class="sidebar-menu">
        @foreach ($normalisedItems as $item)
            @php
                $url = $item['url'] ?? '#';
                $isActive = $item['active'] ?? false;
                if (! $isActive) {
                    $isActive = $current === $url || url($url) === url()->current();
                }
            @endphp
            <li>
                <a href="{{ $url }}" @class(['active' => $isActive])>
                    @if (! empty($item['icon']))
                        <i class="fa {{ $item['icon'] }}"></i>
                    @endif
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</aside>
