<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f6f8fb">
    <title>@yield('title', 'Gestion des Absences')</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap.min.css') }}">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    <script src="{{ asset('vendor/lucide.min.js') }}" defer></script>
</head>
<body class="admin-app">
@php
    $uiMessage = function (?string $message): string {
        return [
            'Invalid PIN code.' => 'Code PIN invalide.',
            'This employee account is inactive.' => 'Ce compte employé est inactif.',
            'Check-in recorded successfully.' => 'Entrée enregistrée avec succès.',
            'Check-out recorded successfully.' => 'Sortie enregistrée avec succès.',
            'Saved' => 'Enregistré.',
            'Absence saved successfully.' => 'Absence enregistrée avec succès.',
            'Absence deleted successfully.' => 'Absence supprimée avec succès.',
            'Employee created successfully.' => 'Employé créé avec succès.',
            'Employee updated successfully.' => 'Employé mis à jour avec succès.',
            'Employee deleted successfully.' => 'Employé supprimé avec succès.',
            'Weekly planning saved successfully.' => 'Planning hebdomadaire enregistré avec succès.',
            'Invalid planning role selected.' => 'Rôle de planning sélectionné invalide.',
            'The pin code field is required.' => 'Le code PIN est obligatoire.',
            'The selected action is invalid.' => 'L’action sélectionnée est invalide.',
            'The user id field is required.' => 'L’employé est obligatoire.',
            'The selected user id is invalid.' => 'L’employé sélectionné est invalide.',
            'The date field is required.' => 'La date est obligatoire.',
            'The date field must be a valid date.' => 'La date doit être valide.',
            'The check in time field must match the format H:i,H:i:s.' => 'L’heure d’entrée doit respecter le format HH:MM.',
            'The check out time field must match the format H:i,H:i:s.' => 'L’heure de sortie doit respecter le format HH:MM.',
            'The reason field is required.' => 'Le motif est obligatoire.',
            'The reason field must be a string.' => 'Le motif doit être du texte.',
            'The reason field must not be greater than 255 characters.' => 'Le motif ne doit pas dépasser 255 caractères.',
            'The name field is required.' => 'Le nom est obligatoire.',
            'The name field must be a string.' => 'Le nom doit être du texte.',
            'The name field must not be greater than 255 characters.' => 'Le nom ne doit pas dépasser 255 caractères.',
            'The email field must be a valid email address.' => 'L’adresse email doit être valide.',
            'The email field must not be greater than 255 characters.' => 'L’adresse email ne doit pas dépasser 255 caractères.',
            'The email has already been taken.' => 'Cette adresse email est déjà utilisée.',
            'The phone field must be a string.' => 'Le téléphone doit être du texte.',
            'The phone field must not be greater than 30 characters.' => 'Le téléphone ne doit pas dépasser 30 caractères.',
            'The position field must be a string.' => 'Le poste doit être du texte.',
            'The position field must not be greater than 120 characters.' => 'Le poste ne doit pas dépasser 120 caractères.',
            'The department field must be a string.' => 'Le département doit être du texte.',
            'The department field must not be greater than 120 characters.' => 'Le département ne doit pas dépasser 120 caractères.',
            'The status field is required.' => 'Le statut est obligatoire.',
            'The selected status is invalid.' => 'Le statut sélectionné est invalide.',
            'The hire date field must be a valid date.' => 'La date d’embauche doit être valide.',
            'The pin code field must not be greater than 20 characters.' => 'Le code PIN ne doit pas dépasser 20 caractères.',
            'The pin code has already been taken.' => 'Ce code PIN est déjà utilisé.',
            'The search field must be a string.' => 'La recherche doit être du texte.',
            'The search field must not be greater than 255 characters.' => 'La recherche ne doit pas dépasser 255 caractères.',
            'The month field must match the format Y-m.' => 'Le mois doit respecter le format AAAA-MM.',
            'The week field is required.' => 'La semaine est obligatoire.',
            'The schedule field must be an array.' => 'Le planning doit être une liste valide.',
        ][$message ?? ''] ?? (string) $message;
    };
@endphp
@php
    $currentUser = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
    $userName = $currentUser?->name ?? 'Administrateur';
    $navigation = [
        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Tableau de bord', 'icon' => 'layout-dashboard'],
        ['route' => 'employees.index', 'match' => 'employees.*', 'label' => 'Employés', 'icon' => 'users'],
        ['route' => 'attendance.index', 'match' => 'attendance.*', 'label' => 'Présences', 'icon' => 'user-check'],
        ['route' => 'absences.index', 'match' => 'absences.*', 'label' => 'Absences', 'icon' => 'user-x'],
        ['route' => 'planning.index', 'match' => 'planning.*', 'label' => 'Planning', 'icon' => 'calendar-days'],
        ['route' => 'reports.index', 'match' => 'reports.*', 'label' => 'Rapports', 'icon' => 'chart-no-axes-combined'],
    ];
    $activePage = collect($navigation)->first(fn ($item) => request()->routeIs($item['match']))['label'] ?? 'Espace RH';
@endphp
<a class="skip-link" href="#main-content">Aller au contenu</a>
<button type="button" class="sidebar-overlay" data-close-sidebar aria-label="Fermer la navigation" hidden></button>
<aside class="sidebar" id="app-sidebar" aria-label="Navigation principale">
    <a href="{{ route('admin.dashboard') }}" class="brand" title="Gestion des Absences">
        <span class="brand-mark">GA<span></span></span>
        <span class="brand-copy"><strong>Gestion des Absences</strong><small>ESPACE RH</small></span>
    </a>
    <div class="workspace-label"><span class="workspace-dot"></span><span>Gestion du personnel</span><x-icon name="chevrons-up-down" /></div>
    <div class="nav-section-label">ESPACE DE TRAVAIL</div>
    <nav>
        @foreach($navigation as $item)
            <a class="nav-link {{ request()->routeIs($item['match']) ? 'active' : '' }}" href="{{ route($item['route']) }}" title="{{ $item['label'] }}" @if(request()->routeIs($item['match'])) aria-current="page" @endif>
                <x-icon :name="$item['icon']" /><span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
    <div class="sidebar-bottom">
        <button type="button" class="nav-link sidebar-collapse" data-toggle-sidebar title="Réduire la navigation" aria-label="Réduire la navigation"><x-icon name="panel-left-close" /><span>Réduire le menu</span></button>
        <div class="sidebar-profile"><x-user-menu :name="$userName" placement="bottom" /></div>
    </div>
</aside>
<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <button class="icon-button mobile-menu" type="button" data-toggle-sidebar aria-label="Ouvrir la navigation" aria-controls="app-sidebar" aria-expanded="false" title="Navigation"><x-icon name="menu" /></button>
            <nav class="breadcrumbs" aria-label="Fil d’Ariane"><span>Gestion des Absences</span><x-icon name="chevron-right" /><span aria-current="page">{{ $activePage }}</span></nav>
        </div>
        <div class="topbar-actions">
            <button type="button" class="global-search" data-open-dialog="search-dialog"><x-icon name="search" /><span>Rechercher dans votre espace</span><kbd>⌘ K</kbd></button>
            <details class="dropdown notifications">
                <summary class="icon-button" aria-label="Notifications" title="Notifications"><x-icon name="bell" /></summary>
                <div class="dropdown-panel notification-panel"><h2>Notifications</h2>
                    @if(session('success'))<p>{{ $uiMessage(session('success')) }}</p>
                    @else<x-empty-state title="Vous êtes à jour" description="Aucune nouvelle notification." icon="bell-off" />@endif
                </div>
            </details>
            <span class="topbar-divider"></span>
            <x-user-menu :name="$userName" />
        </div>
    </header>
    <main id="main-content" class="page-content" tabindex="-1">
        @if(session('success'))
            <div class="app-toast" role="status" data-toast><span class="toast-symbol"><x-icon name="circle-check" /></span><span>{{ $uiMessage(session('success')) }}</span><button type="button" class="icon-button" data-dismiss-toast aria-label="Fermer la notification" title="Fermer"><x-icon name="x" /></button></div>
        @endif
        @if(session('error'))<div class="alert alert-danger" role="alert">{{ $uiMessage(session('error')) }}</div>@endif
        @if($errors->any())
            <div class="alert alert-danger" role="alert"><strong>Veuillez corriger les erreurs</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $uiMessage($error) }}</li>@endforeach</ul></div>
        @endif
        @yield('content')
        <footer class="page-footer"><span>Gestion des Absences</span><span>Espace administrateur</span></footer>
    </main>
</div>

<dialog id="search-dialog" class="app-dialog search-dialog" aria-labelledby="search-title">
    <div class="dialog-head"><h2 id="search-title">Rechercher</h2><button type="button" class="icon-button" data-close-dialog title="Fermer" aria-label="Fermer"><x-icon name="x" /></button></div>
    <form method="GET" action="{{ route('employees.index') }}" class="dialog-search">
        <label class="visually-hidden" for="global-query">Nom, département ou poste</label>
        <x-icon name="search" /><input id="global-query" name="search" placeholder="Nom, département ou poste…" autofocus required maxlength="255"><button type="submit" class="icon-button" aria-label="Rechercher les employés" title="Rechercher"><x-icon name="arrow-right" /></button>
    </form>
    <div class="search-links"><span class="nav-section-label">ACCÈS RAPIDE</span>@foreach($navigation as $item)<a href="{{ route($item['route']) }}"><x-icon :name="$item['icon']" />{{ $item['label'] }}<x-icon name="arrow-up-right" /></a>@endforeach</div>
</dialog>
<x-account-panels :user="$currentUser" :name="$userName" />
@stack('dialogs')
@stack('scripts')
</body>
</html>
