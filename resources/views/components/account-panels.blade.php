@props(['user' => null, 'name'])
@foreach(['profile' => 'Mon profil', 'settings' => 'Préférences'] as $section => $title)
<dialog id="{{ $section }}-dialog" class="app-dialog account-dialog" aria-labelledby="{{ $section }}-title">
    <div class="account-heading">
        <div class="account-heading-label"><span class="account-heading-icon"><x-icon name="user-round-cog" /></span><div><span class="account-eyebrow">ESPACE PERSONNEL</span><h2 id="{{ $section }}-title">Compte & préférences</h2></div></div>
        <button type="button" class="icon-button" data-close-dialog aria-label="Fermer" title="Fermer"><x-icon name="x" /></button>
    </div>
    <div class="account-layout">
        <nav class="account-navigation" aria-label="Paramètres du compte">
            <span class="account-nav-label">VOTRE COMPTE</span>
            <button type="button" class="account-nav-item {{ $section === 'profile' ? 'is-active' : '' }}" data-open-dialog="profile-dialog" @if($section === 'profile') aria-current="page" @endif><x-icon name="circle-user-round" />Mon profil<x-icon name="chevron-right" /></button>
            <button type="button" class="account-nav-item {{ $section === 'settings' ? 'is-active' : '' }}" data-open-dialog="settings-dialog" @if($section === 'settings') aria-current="page" @endif><x-icon name="sliders-horizontal" />Préférences<x-icon name="chevron-right" /></button>
            <div class="account-workspace"><span class="brand-mark">GA</span><div><strong>Gestion des Absences</strong><span>Espace administrateur</span></div></div>
        </nav>
        <div class="account-content">
            @if($section === 'profile')
                <div class="account-section-heading"><h3>Mon profil</h3><span class="account-role"><x-icon name="shield-check" />Administrateur</span></div>
                <div class="account-identity">
                    <x-user-avatar :name="$name" />
                    <div><h4>{{ $name }}</h4><span>Gestion des Absences</span></div>
                </div>
                <section class="account-section" aria-labelledby="account-information-title">
                    <h4 id="account-information-title">Informations du compte</h4>
                    <dl class="account-facts">
                        <div><dt>Nom complet</dt><dd>{{ $name }}</dd></div>
                        <div><dt>Identifiant du compte</dt><dd>{{ $user ? 'GA-'.str_pad($user->id, 4, '0', STR_PAD_LEFT) : 'Non disponible' }}</dd></div>
                        <div><dt>Rôle</dt><dd>Administrateur</dd></div>
                        <div><dt>Membre depuis</dt><dd>{{ $user?->created_at?->locale('fr')->translatedFormat('d F Y') ?? 'Non disponible' }}</dd></div>
                    </dl>
                </section>
                <section class="account-section account-access" aria-labelledby="account-access-title">
                    <span class="account-access-icon"><x-icon name="key-round" /></span>
                    <div><h4 id="account-access-title">Connexion au compte</h4><p>Authentification par code PIN</p></div>
                    <x-icon name="lock-keyhole" class="muted-icon" />
                </section>
            @else
                <div class="account-section-heading"><h3>Préférences</h3><span class="device-label"><x-icon name="monitor" />Cet appareil</span></div>
                <section class="account-section account-density" aria-labelledby="density-title">
                    <h4 id="density-title">Densité des tableaux</h4>
                    <fieldset class="density-options">
                        <legend class="visually-hidden">Densité des tableaux</legend>
                        @foreach(['comfortable' => 'Confortable', 'compact' => 'Compacte'] as $density => $label)
                            <label class="density-option">
                                <input type="radio" name="table-density" value="{{ $density }}" @checked($density === 'comfortable')>
                                <span class="density-preview density-preview--{{ $density }}" aria-hidden="true"><span></span><span></span><span></span><span></span></span>
                                <span class="density-caption">{{ $label }}<span class="density-check"><x-icon name="check" /></span></span>
                            </label>
                        @endforeach
                    </fieldset>
                </section>
                <section class="account-section" aria-labelledby="navigation-preference-title">
                    <h4 id="navigation-preference-title">Navigation</h4>
                    <label class="setting-row" for="compact-navigation"><span class="setting-symbol"><x-icon name="panel-left" /></span><span>Menu latéral compact</span><input class="setting-switch" type="checkbox" role="switch" id="compact-navigation"></label>
                </section>
                <section class="account-section" aria-labelledby="accessibility-preference-title">
                    <h4 id="accessibility-preference-title">Accessibilité</h4>
                    <label class="setting-row" for="reduce-motion"><span class="setting-symbol"><x-icon name="sparkles" /></span><span>Réduire les animations</span><input class="setting-switch" type="checkbox" role="switch" id="reduce-motion"></label>
                </section>
                <p class="preferences-status" role="status" data-preferences-status><x-icon name="check-check" /><span>Préférences enregistrées sur cet appareil</span></p>
            @endif
        </div>
    </div>
    <div class="account-footer">
        @if($section === 'profile')
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="account-signout"><x-icon name="log-out" />Déconnexion</button></form>
        @else
            <button type="button" class="account-reset" data-reset-preferences><x-icon name="rotate-ccw" />Réinitialiser</button>
        @endif
        <button type="button" class="btn btn-primary" data-close-dialog>Terminé<x-icon name="check" /></button>
    </div>
</dialog>
@endforeach
