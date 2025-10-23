@props([
    'alerts' => [],
    'avatar' => null,
    'name' => '',
    'profile' => null,
    'userMenu' => null,
    'isSidebarCollapsed' => false,
    'isSidebarOverlay' => false,
    'isSidebarOpen' => false,
])

@php
    $preparedAlerts = [];
    foreach ($alerts as $alert) {
        if (is_array($alert)) {
            $message = $alert['message'] ?? null;
            if (! $message) {
                continue;
            }

            $preparedAlerts[] = [
                'message' => $message,
                'type' => $alert['type'] ?? 'success',
            ];
            continue;
        }

        if (is_string($alert) && $alert !== '') {
            $preparedAlerts[] = [
                'message' => $alert,
                'type' => 'success',
            ];
        }
    }

    if (empty($preparedAlerts) && isset($alerts['message'])) {
        $preparedAlerts[] = [
            'message' => $alerts['message'],
            'type' => $alerts['type'] ?? 'success',
        ];
    }

    $defaultUserMenu = [
        [
            'label' => 'Termos de Uso',
            'url' => '/termos-uso.html',
            'icon' => 'fa-file-contract',
            'attributes' => ['target' => '_blank', 'rel' => 'noopener'],
        ],
        [
            'label' => 'Política de Privacidade',
            'url' => '/politica-privacidade.html',
            'icon' => 'fa-user-shield',
            'attributes' => ['target' => '_blank', 'rel' => 'noopener'],
        ],
        [
            'label' => 'Sair',
            'url' => '#',
            'icon' => 'fa-sign-out-alt',
            'attributes' => ['id' => 'btn-sair'],
        ],
    ];

    $userMenu = is_array($userMenu) ? $userMenu : [];

    if ($profile === 'Professor') {
        array_unshift($defaultUserMenu, [
            'label' => 'Meu Cadastro',
            'url' => 'meu-cadastro-professor.php',
            'icon' => 'fa-user',
        ]);
    }

    $dropdownItems = array_merge($defaultUserMenu, $userMenu);
@endphp

@php
    $menuAriaExpanded = $isSidebarOverlay ? ($isSidebarOpen ? 'true' : 'false') : ($isSidebarCollapsed ? 'false' : 'true');
@endphp

<header class="layout-header top-nav" data-header-component>
    <div class="layout-header__menu">
        <button class="menu-toggle"
                id="menu-toggle"
                type="button"
                aria-label="Alternar menu lateral"
                aria-expanded="{{ $menuAriaExpanded }}"
                data-menu-toggle>
            <i class="fa fa-bars" aria-hidden="true"></i>
        </button>
    </div>

    <div id="alertas-area" class="layout-header__alerts" @class(['oculto' => empty($preparedAlerts)])>
        @foreach ($preparedAlerts as $alert)
            <div class="alert alert-{{ $alert['type'] }}">
                {!! $alert['message'] !!}
            </div>
        @endforeach
    </div>

    <div class="layout-header__user user-area">
        @if ($avatar)
            <div class="user-img">
                <img src="{{ $avatar }}" alt="Avatar" />
            </div>
        @endif
        @if ($name)
            <span class="user-name">{{ $name }}</span>
        @endif
        <button class="user-dropdown-toggle"
                type="button"
                aria-haspopup="true"
                aria-expanded="false"
                onclick="window.toggleUserMenu?.()">
            <i class="fa fa-chevron-down" aria-hidden="true"></i>
        </button>
        <div id="user-menu" class="user-menu" hidden>
            @foreach ($dropdownItems as $link)
                @php
                    $attrs = $link['attributes'] ?? [];
                    $attrString = '';
                    foreach ($attrs as $attr => $value) {
                        if (is_string($attr)) {
                            $attrString .= ' ' . $attr . '="' . e($value) . '"';
                        }
                    }
                @endphp
                <a href="{{ $link['url'] ?? '#' }}"{!! $attrString !!}>
                    @if (! empty($link['icon']))
                        <i class="fa {{ $link['icon'] }}" aria-hidden="true"></i>
                    @endif
                    {{ $link['label'] ?? '' }}
                </a>
            @endforeach
        </div>
    </div>
</header>
