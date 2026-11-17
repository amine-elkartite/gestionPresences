@props(['name', 'placement' => 'top'])
<details class="dropdown user-menu user-menu--{{ $placement }}">
    <summary class="{{ $placement === 'bottom' ? 'profile-trigger' : 'avatar-trigger' }}" title="Menu utilisateur" aria-label="Menu utilisateur">
        <x-user-avatar :name="$name" />
        @if($placement === 'bottom')<span class="profile-copy"><strong>{{ $name }}</strong><small>Administrateur</small></span>@endif
        <x-icon name="chevrons-up-down" class="profile-chevron" />
    </summary>
    <div class="dropdown-panel account-dropdown">
        <div class="account-dropdown-identity"><x-user-avatar :name="$name" /><div><strong>{{ $name }}</strong><span><x-icon name="shield-check" />Administrateur</span></div></div>
        <button type="button" data-open-dialog="profile-dialog"><x-icon name="circle-user-round" />Mon profil</button>
        <button type="button" data-open-dialog="settings-dialog"><x-icon name="settings-2" />Paramètres</button>
        <div class="dropdown-divider"></div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="text-danger"><x-icon name="log-out" />Déconnexion</button></form>
    </div>
</details>
