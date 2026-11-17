@extends('layouts.app')
@section('title', 'Tableau de bord | Gestion des Absences')
@section('content')
@php
    $selectedDate = \Carbon\Carbon::parse($today)->locale('fr');
    $isToday = $selectedDate->isToday();
    $metrics = [
        ['label' => 'Présents', 'value' => $presentToday, 'tone' => 'present', 'icon' => 'user-check'],
        ['label' => 'Absents', 'value' => $absentToday, 'tone' => 'absent', 'icon' => 'user-x'],
        ['label' => 'En congé', 'value' => $congeToday, 'tone' => 'conge', 'icon' => 'calendar-days'],
        ['label' => 'En repos', 'value' => $reposToday, 'tone' => 'repos', 'icon' => 'coffee'],
    ];
    $statusFilters = ['' => 'Tous', 'present' => 'Présents', 'absent' => 'Absents', 'late' => 'Retards', 'conge' => 'Congés', 'repos' => 'Repos', 'not_planned' => 'Non planifiés'];
    $chartHasData = collect($weeklyStats)->sum('present') + collect($weeklyStats)->sum('absent') > 0;
    $weekStart = $selectedDate->copy()->startOfWeek();
@endphp

<div class="page-head dashboard-head">
    <div>
        <div class="page-kicker"><span class="live-dot"></span>TABLEAU DE BORD</div>
        <h1 class="page-heading">Vue quotidienne</h1>
        <p class="page-subtitle">Suivez les présences, absences et disponibilités de votre équipe.</p>
    </div>
    <div class="page-head-actions">
        <span class="current-date"><x-icon name="calendar-days" />{{ ucfirst($selectedDate->translatedFormat('l d F Y')) }}</span>
        <button type="button" class="btn btn-primary" data-open-dialog="absence-dialog"><x-icon name="plus" />Ajouter une absence</button>
    </div>
</div>

<div class="overview-heading"><h2>Votre équipe en un coup d’œil</h2><span><x-icon name="users" /><strong>{{ $totalEmployees }}</strong> employés</span></div>
<div class="metric-grid">
    @foreach($metrics as $metric)
        <x-stat-card :label="$metric['label']" :value="$metric['value']" :tone="$metric['tone']" :icon="$metric['icon']" :subtitle="$totalEmployees ? round($metric['value'] / $totalEmployees * 100).' % de l’équipe' : 'Aucun employé enregistré'" />
    @endforeach
</div>

<div class="analytics-grid">
    <section class="analytics-section" aria-labelledby="weekly-title">
        <div class="panel-head">
            <div><h2 class="section-title" id="weekly-title">Présence cette semaine</h2><p class="section-sub">{{ $weekStart->translatedFormat('d M') }} – {{ $weekStart->copy()->endOfWeek()->translatedFormat('d M Y') }}</p></div>
            <div class="chart-legend"><span><i class="legend-dot legend-present"></i>Présents</span><span><i class="legend-dot legend-absent"></i>Absents</span></div>
        </div>
        <div class="weekly-chart" data-chart-frame>
            <div class="chart-loading" data-chart-loading><x-skeleton /></div>
            <canvas id="weekly-chart" data-values="{{ json_encode($weeklyStats) }}" aria-label="Présences et absences par jour de la semaine" role="img" aria-describedby="weekly-data"></canvas>
            @if(!$chartHasData)<div class="chart-empty"><x-icon name="chart-no-axes-column" /><strong>Aucune présence ou absence cette semaine</strong><span>Les données enregistrées apparaîtront ici.</span></div>@endif
        </div>
        <table id="weekly-data" class="visually-hidden"><caption>Présences et absences cette semaine</caption><thead><tr><th>Jour</th><th>Présents</th><th>Absents</th></tr></thead><tbody>@foreach($weeklyStats as $day)<tr><th>{{ $day['date'] }}</th><td>{{ $day['present'] ?? 'À venir' }}</td><td>{{ $day['absent'] ?? 'À venir' }}</td></tr>@endforeach</tbody></table>
    </section>
    <section class="rate-section" aria-labelledby="rate-title">
        <div class="panel-head"><div><h2 class="section-title" id="rate-title">Taux de présence</h2><p class="section-sub">{{ $isToday ? 'Aujourd’hui' : $selectedDate->translatedFormat('d F Y') }}</p></div><span class="info-hint" tabindex="0" title="Présents parmi les employés attendus : présents + absents." aria-label="Présents parmi les employés attendus : présents + absents."><x-icon name="info" /></span></div>
        <div class="presence-donut {{ $presenceRate === null ? 'is-empty' : '' }}" style="--rate: {{ $presenceRate ?? 0 }}%;" role="img" aria-label="{{ $presenceRate === null ? 'Aucun employé attendu' : $presenceRate.' % des employés attendus sont présents' }}">
            <div><strong>{{ $presenceRate === null ? '—' : $presenceRate.'%' }}</strong><span>{{ $presenceRate === null ? 'Non disponible' : 'de présence' }}</span></div>
        </div>
        <div class="rate-summary"><span>Présents / attendus</span><strong>{{ $presentToday }} / {{ $expectedEmployees }}</strong></div>
        <div class="rate-summary"><span>Non planifiés</span><strong>{{ $notPlanned }}</strong></div>
    </section>
</div>

<section class="data-section" aria-labelledby="attendance-title" id="attendance-table">
    <div class="panel-head">
        <div><h2 id="attendance-title" class="section-title">{{ $isToday ? 'Présences du jour' : 'Présences du '.$selectedDate->format('d/m/Y') }} <span class="count-badge">{{ $dailyRows->total() }}</span></h2><p class="section-sub">Suivez les entrées, sorties et statuts de vos employés.</p></div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.dashboard', array_merge(request()->except('page'), ['export' => 'csv'])) }}"><x-icon name="download" />Exporter</a>
    </div>
    <form method="GET" action="{{ route('admin.dashboard') }}#attendance-table" class="dashboard-filters" data-filter-form>
        <div class="filter-tabs" role="group" aria-label="Filtrer par statut">
            @foreach($statusFilters as $key => $label)
                <button type="submit" name="status" value="{{ $key }}" class="filter-tab {{ $status === $key ? 'active' : '' }}" aria-pressed="{{ $status === $key ? 'true' : 'false' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="filter-controls">
            <label class="search-input"><x-icon name="search" /><span class="visually-hidden">Rechercher un employé</span><input name="search" value="{{ $search }}" placeholder="Rechercher un employé…" maxlength="255"></label>
            <label class="select-input"><span class="visually-hidden">Département</span><x-icon name="building-2" /><select name="department" aria-label="Département"><option value="">Tous les départements</option>@foreach($departments as $item)<option @selected($department === $item)>{{ $item }}</option>@endforeach</select></label>
            <label class="date-input"><span class="visually-hidden">Date des présences</span><input type="date" name="date" value="{{ $today }}" required aria-label="Date des présences"></label>
            <button type="submit" class="icon-button filter-apply" name="status" value="{{ $status }}" title="Appliquer les filtres" aria-label="Appliquer les filtres"><x-icon name="sliders-horizontal" /></button>
            @if($search || $department || $status)<a class="icon-button" href="{{ route('admin.dashboard', ['date' => $today]) }}#attendance-table" title="Réinitialiser les filtres" aria-label="Réinitialiser les filtres"><x-icon name="rotate-ccw" /></a>@endif
        </div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle attendance-table">
            <thead><tr><th scope="col">Employé</th><th scope="col">Statut</th><th scope="col">Entrée</th><th scope="col">Sortie</th><th scope="col">Heures</th><th scope="col">Motif</th><th scope="col" class="text-end"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
                @forelse($dailyRows as $row)
                    <tr>
                        <td><div class="employee-identity"><x-user-avatar :name="$row->employee->name" /><div class="employee-cell"><span class="employee-name">{{ $row->employee->name }}</span><span class="employee-meta">{{ $row->employee->position ?: $row->employee->department ?: 'Employé' }}</span></div></div></td>
                        <td><x-status-badge :status="$row->display_status" /></td>
                        <td class="time-cell">{{ $row->check_in_time ? substr($row->check_in_time, 0, 5) : '—' }}</td>
                        <td class="time-cell">{{ $row->check_out_time ? substr($row->check_out_time, 0, 5) : '—' }}</td>
                        <td class="time-cell">{{ $row->check_in_time && $row->check_out_time ? intdiv($row->worked_minutes, 60).'h '.str_pad($row->worked_minutes % 60, 2, '0', STR_PAD_LEFT).'m' : '—' }}</td>
                        <td class="reason-cell">{{ $row->absence_reason ?: '—' }}</td>
                        <td class="text-end">
                            <details class="dropdown row-actions" data-floating-menu>
                                <summary class="icon-button" aria-label="Actions pour {{ $row->employee->name }}" title="Actions"><x-icon name="ellipsis" /></summary>
                                <div class="dropdown-panel">
                                    <button type="button" data-open-dialog="employee-profile-dialog" data-profile="{{ json_encode($row->employee->only(['name', 'email', 'phone', 'position', 'department', 'status'])) }}"><x-icon name="user-round" />Voir le profil</button>
                                    <a href="{{ route('attendance.index', ['date' => $today, 'search' => $row->employee->name]) }}"><x-icon name="clock-3" />Voir la présence</a>
                                    <button type="button" data-open-dialog="attendance-dialog" data-attendance="{{ json_encode(['user_id' => $row->employee->user_id, 'name' => $row->employee->name, 'check_in_time' => substr($row->check_in_time ?? '', 0, 5), 'check_out_time' => substr($row->check_out_time ?? '', 0, 5)]) }}"><x-icon name="pencil" />Modifier la présence</button>
                                    <a href="{{ route('employees.edit', $row->employee) }}"><x-icon name="user-pen" />Modifier le profil</a>
                                    @if($row->employee->status === 'active')<button type="button" data-open-dialog="absence-dialog" data-employee="{{ $row->employee->user_id }}"><x-icon name="calendar-plus" />Ajouter une absence</button>@endif
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state title="Aucun employé trouvé" description="Essayez une autre recherche ou modifiez les filtres." icon="search-x" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer"><span>{{ $dailyRows->firstItem() ?? 0 }}–{{ $dailyRows->lastItem() ?? 0 }} sur {{ $dailyRows->total() }} employés</span><nav class="pagination-controls" aria-label="Pagination des présences">
        @if($dailyRows->onFirstPage())<button class="icon-button" disabled aria-label="Page précédente"><x-icon name="chevron-left" /></button>@else<a class="icon-button" href="{{ $dailyRows->previousPageUrl() }}#attendance-table" aria-label="Page précédente" title="Page précédente"><x-icon name="chevron-left" /></a>@endif
        <span>Page {{ $dailyRows->currentPage() }} sur {{ $dailyRows->lastPage() }}</span>
        @if($dailyRows->hasMorePages())<a class="icon-button" href="{{ $dailyRows->nextPageUrl() }}#attendance-table" aria-label="Page suivante" title="Page suivante"><x-icon name="chevron-right" /></a>@else<button class="icon-button" disabled aria-label="Page suivante"><x-icon name="chevron-right" /></button>@endif
    </nav></div>
</section>

<div class="secondary-grid">
    <section class="insight-section" aria-labelledby="reasons-title">
        <div class="panel-head"><div><h2 class="section-title" id="reasons-title">Motifs d’absence</h2><p class="section-sub">Répartition des absences enregistrées {{ $isToday ? 'aujourd’hui' : 'le '.$selectedDate->format('d/m/Y') }}</p></div><x-icon name="chart-no-axes-column-increasing" class="muted-icon" /></div>
        @forelse($absenceMotifs as $motif)
            <div class="motif-row"><div><span>{{ $motif->reason }}</span><strong>{{ $motif->total }}</strong></div><progress value="{{ $motif->total }}" max="{{ $absenceMotifs->sum('total') }}" aria-label="{{ $motif->reason }}"></progress></div>
        @empty
            <x-empty-state title="Aucune absence enregistrée {{ $isToday ? 'aujourd’hui' : 'à cette date' }}" description="Les motifs apparaîtront ici lorsqu’un employé sera déclaré absent." icon="calendar-check-2" />
        @endforelse
    </section>
    <section class="insight-section" aria-labelledby="activity-title">
        <div class="panel-head"><div><h2 class="section-title" id="activity-title">Activité récente</h2><p class="section-sub">Derniers événements de la journée sélectionnée</p></div><x-icon name="activity" class="muted-icon" /></div>
        @forelse($recentActivity as $event)
            <div class="activity-row"><span class="activity-symbol activity-{{ $event['tone'] }}"><x-icon :name="$event['tone'] === 'present' ? 'log-in' : 'calendar-x-2'" /></span><div><strong>{{ $event['name'] }}</strong><p>{{ $event['label'] }}</p></div><time datetime="{{ $event['time']->toIso8601String() }}" title="{{ $event['time']->format('d/m/Y H:i') }}">{{ $event['time']->toDateString() === $today ? $event['time']->format('H:i') : $event['time']->format('d/m H:i') }}</time></div>
        @empty
            <x-empty-state title="Aucune activité enregistrée" description="Les entrées, sorties et absences de votre équipe apparaîtront ici." icon="clock-3" />
        @endforelse
    </section>
</div>

<x-absence-dialog :employees="$employees" :date="$today" />
<dialog id="employee-profile-dialog" class="app-dialog" aria-labelledby="employee-profile-title">
    <div class="dialog-head"><h2 id="employee-profile-title">Profil employé</h2><button type="button" class="icon-button" data-close-dialog aria-label="Fermer" title="Fermer"><x-icon name="x" /></button></div>
    <dl class="dialog-body profile-fields">@foreach(['name' => 'Nom', 'email' => 'Email', 'phone' => 'Téléphone', 'position' => 'Poste', 'department' => 'Département', 'status' => 'Statut'] as $key => $label)<div><dt>{{ $label }}</dt><dd data-profile-field="{{ $key }}">—</dd></div>@endforeach</dl>
    <div class="dialog-footer"><button type="button" class="btn btn-outline-secondary" data-close-dialog>Fermer</button></div>
</dialog>
<dialog id="attendance-dialog" class="app-dialog" aria-labelledby="attendance-dialog-title" @if(old('_form') === 'attendance' && $errors->any()) data-reopen @endif>
    <form method="POST" action="{{ route('attendance.store') }}" data-submit-state>
        @csrf
        <input type="hidden" name="_form" value="attendance"><input type="hidden" name="user_id" value="{{ old('user_id') }}"><input type="hidden" name="date" value="{{ $today }}">
        <div class="dialog-head"><div><h2 id="attendance-dialog-title">Modifier la présence</h2><p data-attendance-name>{{ $employees->firstWhere('user_id', old('user_id'))?->name }}</p></div><button type="button" class="icon-button" data-close-dialog aria-label="Fermer" title="Fermer"><x-icon name="x" /></button></div>
        <div class="dialog-body"><p class="section-sub">{{ $selectedDate->translatedFormat('d F Y') }}</p><div class="row g-3"><div class="col-6"><label for="edit-entry" class="form-label">Entrée</label><input id="edit-entry" type="time" name="check_in_time" value="{{ old('check_in_time') }}" class="form-control"></div><div class="col-6"><label for="edit-exit" class="form-label">Sortie</label><input id="edit-exit" type="time" name="check_out_time" value="{{ old('check_out_time') }}" class="form-control"></div></div></div>
        <div class="dialog-footer"><button class="btn btn-outline-secondary" type="button" data-close-dialog>Annuler</button><button class="btn btn-primary" type="submit"><x-icon name="check" />Enregistrer</button></div>
    </form>
</dialog>
@push('scripts')<script src="{{ asset('vendor/chart.umd.js') }}" defer></script>@endpush
<noscript><style>.chart-loading { display: none; }</style><p class="section-sub">JavaScript est nécessaire pour afficher les graphiques et ouvrir les fenêtres de saisie.</p></noscript>
@endsection
