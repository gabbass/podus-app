@extends('layouts.app', [
    'title' => $title ?? 'Painel do Professor',
    'sidebarTitle' => $sidebarTitle ?? 'Portal Universo do Saber',
    'menuItems' => $menuItems ?? [],
    'currentMenu' => $currentMenu ?? null,
    'alerts' => $alerts ?? [],
    'avatar' => $avatar ?? null,
    'userName' => $userName ?? ($nome ?? ''),
    'profile' => $profile ?? ($perfil ?? null),
    'userMenu' => $userMenu ?? [],
])

@section('content')
<div class="content-container" id="content-container">
    <div class="container">
        <div class="page-title">
            <h1>{{ $pageTitle ?? 'Página base' }}</h1>
            <p>{{ $pageDescription ?? 'Página base de layout' }}</p>
        </div>

        {{ $slot ?? '' }}
    </div>
</div>
@endsection
