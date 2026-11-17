@extends('layouts.app')

@section('title', 'Employés | Gestion des Absences')
@section('page-title', 'Employés')

@section('content')

<div class="page-head">
    <div>
        <div class="page-kicker">Répertoire</div>
        <h1 class="page-heading">Employés</h1>
        <div class="page-subtitle">Gérez les profils, PIN et statuts des collaborateurs.</div>
    </div>

    <a class="btn btn-primary px-4 py-2" href="{{ route('employees.create') }}">
        <x-icon name="plus" /> Ajouter un employé
    </a>
</div>

<div class="card-box">
    <div class="table-toolbar">
        <div>
            <h2 class="section-title">Liste des employés</h2>
            <div class="section-sub">{{ $employees->total() ?? $employees->count() }} profils trouvés</div>
        </div>

        <form method="GET" action="{{ route('employees.index') }}" class="d-flex flex-wrap gap-2">
            <input class="form-control search-field"
                   name="search"
                   value="{{ $search }}"
                   placeholder="Rechercher un employé" aria-label="Rechercher un employé">

            <button class="btn btn-outline-primary px-4">
                Rechercher
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Poste</th>
                    <th>Statut</th>
                    <th>Date d’embauche</th>
                    <th>PIN</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td>
                            <div class="employee-identity"><x-user-avatar :name="$employee->name" /><div class="employee-cell">
                                <span class="employee-name">{{ $employee->name }}</span>
                                <span class="employee-meta">{{ $employee->department ?? 'Personnel' }}</span>
                            </div></div>
                        </td>
                        <td>{{ $employee->email ?? '-' }}</td>
                        <td>{{ $employee->phone ?? '-' }}</td>
                        <td>{{ $employee->position ?? '-' }}</td>

                        <td>
                            <span class="status-badge {{ $employee->status === 'active' ? 'status-badge--present' : 'status-badge--default' }}">
                                {{ $employee->status === 'active' ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>

                        <td>{{ $employee->hire_date?->format('Y-m-d') ?? '-' }}</td>

                        <td class="fw-semibold text-muted">
                            {{ $employee->user?->pin_code ?? '-' }}
                        </td>

                        <td class="text-end">
                            <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                <a class="icon-button" title="Modifier le profil" aria-label="Modifier {{ $employee->name }}"
                                   href="{{ route('employees.edit', $employee) }}">
                                    <x-icon name="pencil" />
                                </a>

                                <form method="POST"
                                      action="{{ route('employees.destroy', $employee) }}"
                                      onsubmit="return confirm('Supprimer cet employé ?')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="icon-button text-danger" title="Supprimer l’employé" aria-label="Supprimer {{ $employee->name }}">
                                        <x-icon name="trash-2" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-4">
                            Aucun employé trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $employees->links() }}
    </div>
</div>

@endsection
