@extends('layouts.app')

@section('title', 'Rapports | Gestion des Absences')
@section('page-title', 'Tableau des rapports')

@section('content')



<div class="page-head">
    <div>
        <div class="page-kicker">Analyse</div>
        <h1 class="page-heading">Rapports</h1>
        <div class="page-subtitle">
            Synthèse quotidienne et historique mensuel.
        </div>
    </div>
</div>

<div class="metric-grid metric-grid-five">
    <x-stat-card label="Employés" :value="$summary['employees'] ?? 0" tone="primary" icon="users" subtitle="Effectif suivi" />
    <x-stat-card label="Présents" :value="$summary['present'] ?? 0" tone="present" icon="user-check" subtitle="Présences du jour" />
    <x-stat-card label="Absents" :value="$summary['absent'] ?? 0" tone="absent" icon="user-x" subtitle="Absences du jour" />
    <x-stat-card label="En congé" :value="$summary['conge'] ?? 0" tone="conge" icon="calendar-days" subtitle="Congés du jour" />
    <x-stat-card label="En repos" :value="$summary['repos'] ?? 0" tone="repos" icon="coffee" subtitle="Repos du jour" />
</div>

<div class="card-box mb-4">

    <div class="panel-head">
        <div>
            <h2 class="section-title">Filtres</h2>

            <div class="section-sub">
                Choisir le jour et le mois à afficher
            </div>
        </div>
    </div>

    <form
        method="GET"
        action="{{ route('reports.index') }}"
        class="row g-3 align-items-end"
    >

        <div class="col-lg-5">
            <label class="form-label" for="field-reports-1">Jour</label>

            <input id="field-reports-1"
                class="form-control control-lg"
                name="day"
                type="date"
                value="{{ $day }}"
            >
        </div>

        <div class="col-lg-5">
            <label class="form-label" for="field-reports-2">Mois</label>

            <input id="field-reports-2"
                class="form-control control-lg"
                name="month"
                type="month"
                value="{{ $month }}"
            >
        </div>

        <div class="col-lg-2">
            <button class="btn btn-primary w-100 control-lg">
                Afficher
            </button>
        </div>

    </form>

</div>



<div class="card-box">

    <div class="panel-head">
        <div>
            <h2 class="section-title">
                Rapport mensuel
            </h2>

            <div class="section-sub">
                {{ $month }}
            </div>
        </div>
    </div>

    <div class="d-flex flex-column gap-3">

        @forelse ($reportRows as $employeeName => $rows)

            @php
                $totalHours = round($rows->sum('worked_minutes') / 60, 2);
            @endphp

            <div class="report-group">

                <div class="report-group-head">

                    <div class="employee-cell">

                        <span class="employee-name">
                            {{ $employeeName }}
                        </span>

                        <span class="employee-meta">
                            {{ $rows->count() }} lignes
                        </span>

                    </div>

                    <span class="status-badge status-badge--default">
                        {{ $totalHours }} h
                    </span>

                </div>

                <div class="p-3">

                    <div class="table-responsive">

                        <table class="table table-sm align-middle">

                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Entrée</th>
                                    <th>Sortie</th>
                                    <th>Heures</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($rows as $attendance)



                                    <tr>

                                        <td class="fw-semibold">
                                            {{ \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') }}
                                        </td>

                                        <td>
                                            <x-status-badge :status="$attendance->status" />
                                        </td>

                                        <td>

                                         {{ $attendance->attendance?->check_in_time
    ? \Carbon\Carbon::parse($attendance->attendance->check_in_time)->format('H:i')
    : '-' }}

                                        </td>

                                        <td>

                                         {{ $attendance->attendance?->check_out_time
    ? \Carbon\Carbon::parse($attendance->attendance->check_out_time)->format('H:i')
    : '-' }}

                                        </td>

                                        <td>
                                            {{ round(($attendance->worked_minutes ?? 0) / 60, 2) }} h
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        @empty

            <div class="text-muted">
                Aucun rapport mensuel disponible.
            </div>

        @endforelse

    </div>

</div>

@endsection
