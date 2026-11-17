@extends('layouts.app')

@section('title', 'Absences | Gestion des Absences')
@section('page-title', 'Tableau des absences')

@section('content')

<div class="page-head">
    <div>
        <div class="page-kicker">Suivi des motifs</div>
        <h1 class="page-heading">Absences</h1>
        <div class="page-subtitle">Consultez, filtrez et enregistrez les absences.</div>
    </div>
    <button type="button" class="btn btn-primary" data-open-dialog="absence-dialog"><x-icon name="plus" />Ajouter une absence</button>
</div>

<div class="metric-grid metric-grid-three">
    <x-stat-card label="Aujourd’hui" :value="$stats['today']" tone="absent" icon="user-x" subtitle="Absences du jour" />
    <x-stat-card label="Ce mois-ci" :value="$stats['month']" tone="conge" icon="calendar-days" subtitle="Absences mensuelles" />
    <x-stat-card label="Total" :value="$stats['total']" tone="neutral" icon="files" subtitle="Absences enregistrées" />
</div>

<div class="card-box mb-4">
    <div class="panel-head">
        <div>
            <h2 class="section-title">Évolution des absences</h2>
            <div class="section-sub">Tendance du mois en cours</div>
        </div>
    </div>

    @if($chartData->isNotEmpty())
        <div class="absence-chart"><canvas id="absenceChart" aria-label="Évolution des absences du mois" role="img"></canvas></div>
    @else
        <x-empty-state title="Aucune absence ce mois-ci" description="La tendance apparaîtra après un premier enregistrement." icon="chart-no-axes-combined" />
    @endif
</div>

<div class="card-box">
    <div class="table-toolbar">
        <div>
            <h2 class="section-title">Registre des absences</h2>
            <div class="section-sub">Historique filtrable</div>
        </div>

        <form method="GET" class="d-flex flex-wrap gap-2">
            <input class="form-control search-field" name="search" value="{{ $search }}" placeholder="Rechercher un employé" aria-label="Rechercher un employé">
            <input type="date" class="form-control control-lg" name="date" value="{{ $date }}" aria-label="Date des absences">
            <button class="btn btn-outline-primary px-4">Filtrer</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Date</th>
                    <th>Motif</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($absences as $absence)
                    <tr>
                        <td>
                            <div class="employee-identity"><x-user-avatar :name="$absence->user?->employee?->name ?? $absence->user?->name ?? 'Employé'" /><div class="employee-cell">
                                <span class="employee-name">{{ $absence->user?->employee?->name ?? $absence->user?->name }}</span>
                                <span class="employee-meta">{{ $absence->user?->employee?->position ?? '-' }}</span>
                            </div></div>
                        </td>

                        <td class="fw-semibold text-muted">
                            {{ $absence->date->format('Y-m-d') }}
                        </td>

                        <td>
                            @php
                                $reasonKey = strtolower((string) $absence->reason);
                                $reasonBadge = str_contains($reasonKey, 'maladie')
                                    ? 'status-badge--maladie'
                                    : (str_contains($reasonKey, 'cong') ? 'status-badge--conge' : 'status-badge--default');
                            @endphp
                            <span class="status-badge {{ $reasonBadge }}">
                                {{ $absence->reason }}
                            </span>
                        </td>

                        <td class="text-end">
                            <form method="POST" action="{{ route('absences.destroy', $absence) }}" onsubmit="return confirm('Supprimer cette absence ?')">
                                @csrf
                                @method('DELETE')
                                <button class="icon-button text-danger" title="Supprimer cette absence" aria-label="Supprimer cette absence"><x-icon name="trash-2" /></button>
                            </form>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Aucune absence trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $absences->links() }}
    </div>
</div>

<x-absence-dialog :employees="$employees" :date="today()->toDateString()" />
<script src="{{ asset('vendor/chart.umd.js') }}"></script>

<script>
if (document.getElementById('absenceChart')) {
const ctx = document.getElementById('absenceChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData->pluck('date')) !!},
        datasets: [{
            label: 'Absences',
            data: {!! json_encode($chartData->pluck('total')) !!},
            borderWidth: 2,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.08)',
            tension: 0.35,
            fill: true
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
}
</script>

@endsection
