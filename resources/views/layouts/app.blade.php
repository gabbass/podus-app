<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Portal Universo do Saber') }}</title>
    @vite(['resources/js/app.js', 'resources/css/pustyle.css'])
    @stack('head')
</head>
@php
    $sidebarConfig = array_merge([
        'collapsed' => $sidebarCollapsed ?? false,
        'overlay' => $sidebarOverlay ?? false,
        'open' => $sidebarOpen ?? false,
        'breakpoints' => $menuBreakpoints ?? null,
    ], $sidebarState ?? []);

    $menuBreakpoints = $sidebarConfig['breakpoints'] ?? null;
    $bodyClasses = array_filter([
        'legacy-app',
        $sidebarConfig['collapsed'] ? 'is-sidebar-collapsed' : null,
        $sidebarConfig['overlay'] ? 'is-sidebar-overlay' : null,
        $sidebarConfig['open'] ? 'is-sidebar-open' : null,
    ]);
    $menuBreakpointsAttr = $menuBreakpoints ? e(json_encode($menuBreakpoints)) : null;
@endphp

<body class="{{ implode(' ', $bodyClasses) }}"
      data-layout-container
      @if ($menuBreakpointsAttr)
          data-menu-breakpoints="{{ $menuBreakpointsAttr }}"
      @endif>
    <x-menu :title="$sidebarTitle ?? config('app.name', 'Portal Universo do Saber')"
            :items="$menuItems ?? []"
            :current="$currentMenu ?? null"
            :collapsed="$sidebarConfig['collapsed']"
            :overlay-open="$sidebarConfig['open']" />

    <div class="sidebar-backdrop" data-sidebar-backdrop hidden></div>

    <main id="main-content" class="layout-main main-content @yield('main-class')" data-layout-main>
        <x-header :alerts="$alerts ?? []"
                  :avatar="$avatar ?? null"
                  :name="$userName ?? ''"
                  :profile="$profile ?? null"
                  :user-menu="$userMenu ?? []"
                  :is-sidebar-collapsed="$sidebarConfig['collapsed']"
                  :is-sidebar-overlay="$sidebarConfig['overlay']"
                  :is-sidebar-open="$sidebarConfig['open']" />

        <div class="layout-main__content content-wrapper">
            @yield('content')
            {{ $slot ?? '' }}
        </div>
    </main>

    @stack('scripts')
</body>
</html>
