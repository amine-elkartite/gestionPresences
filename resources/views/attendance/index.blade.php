@extends('layouts.app')

@section('title', 'Présences | Gestion des Absences')
@section('page-title', 'Gestion des présences')

@section('content')

<div class="page-head">
    <div>
        <div class="page-kicker">Pointage</div>
        <h1 class="page-heading">Présences</h1>
        <div class="page-subtitle">Suivi quotidien des entrées, sorties et statuts.</div>
    </div>

    <form method="GET" class="d-flex flex-wrap gap-2">
        <input class="form-control control-lg" type="date" name="date" value="{{ $date }}" aria-label="Date des présences"><input class="form-control" name="search" value="{{ $search }}" placeholder="Rechercher un employé" aria-label="Rechercher un employé">
        <button class="btn btn-outline-primary px-4">Filtrer</button>
    </form>
</div>

<div class="card-box mb-4">
    <div class="panel-head">
        <div>
            <h2 class="section-title">Ajouter une présence</h2>
            <div class="section-sub">Enregistrement manuel d’un pointage</div>
        </div>
    </div>

    <form method="POST" action="{{ route('attendance.store') }}" data-submit-state>
        @csrf

        <div class="row g-3 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label" for="field-attendance-1">Employé</label>
                <select id="field-attendance-1" class="form-select control-lg" name="user_id" required>
                    <option value="">Sélectionner un employé</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->user_id }}" @selected((string) old('user_id') === (string) $employee->user_id)>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <label class="form-label" for="field-attendance-2">Date</label>
                <input id="field-attendance-2" type="date" class="form-control control-lg" name="date" value="{{ $date }}">
            </div>

            <div class="col-lg-2 col-md-6">
                <label class="form-label" for="field-attendance-3">Entrée</label>
                <input id="field-attendance-3" type="time" class="form-control control-lg" name="check_in_time" value="{{ old('check_in_time') }}">
            </div>

            <div class="col-lg-2 col-md-6">
                <label class="form-label" for="field-attendance-4">Sortie</label>
                <input id="field-attendance-4" type="time" class="form-control control-lg" name="check_out_time" value="{{ old('check_out_time') }}">
            </div>

            <div class="col-lg-2 col-md-12">
                <button class="btn btn-primary w-100 control-lg">Enregistrer</button>
            </div>
        </div>
    </form>
</div>

<div class="card-box">
    <div class="panel-head">
        <div>
            <h2 class="section-title">Présences du jour</h2>
            <div class="section-sub">{{ \Carbon\Carbon::parse($date)->locale('fr')->translatedFormat('d F Y') }}</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Statut</th>
                    <th>Entrée</th>
                    <th>Sortie</th>
                    <th>Heures travaillées</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($attendanceRows as $row)


                    <tr>
                        <td>
                            <div class="employee-identity"><x-user-avatar :name="$row->employee->name" /><div class="employee-cell">
                                <span class="employee-name">{{ $row->employee->name }}</span>
                                <span class="employee-meta">{{ $row->employee->position ?? $row->employee->department ?? '-' }}</span>
                            </div></div>
                        </td>

                        <td>
                            <x-status-badge :status="$row->status" />
                        </td>

                        <td class="fw-semibold">{{ $row->check_in_time ? substr($row->check_in_time, 0, 5) : '—' }}</td>
                        <td class="fw-semibold">{{ $row->check_out_time ? substr($row->check_out_time, 0, 5) : '—' }}</td>

                        <td class="fw-bold">
                            {{ $row->worked_hours ?? 0 }} h
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Aucun enregistrement trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
