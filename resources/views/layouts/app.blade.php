<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Portal Universo do Saber') }}</title>
    @vite(['resources/js/app.js'])
    @stack('head')
</head>
<body class="legacy-app">
    <x-menu :title="$sidebarTitle ?? config('app.name', 'Portal Universo do Saber')"
            :items="$menuItems ?? []"
            :current="$currentMenu ?? null" />

    <main id="main-content" class="main-content @yield('main-class')">
        <x-header :alerts="$alerts ?? []"
                  :avatar="$avatar ?? null"
                  :name="$userName ?? ''"
                  :profile="$profile ?? null"
                  :user-menu="$userMenu ?? []" />

        <div class="content-wrapper">
            @yield('content')
            {{ $slot ?? '' }}
        </div>
    </main>

    @stack('scripts')
</body>
</html>
