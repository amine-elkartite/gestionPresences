@extends('layouts.app')

@section('title', 'Planning | Gestion des Absences')
@section('page-title', 'Planning')

@section('content')



<div class="page-head">
    <div>
        <div class="page-kicker">Organisation hebdomadaire</div>
        <h1 class="page-heading">Planning</h1>
        <div class="page-subtitle">
            Du {{ $weekStart->format('d/m/Y') }} au {{ $weekEnd->format('d/m/Y') }}
        </div>
    </div>

    <div class="planning-toolbar">
        <a class="btn btn-outline-secondary" href="{{ route('planning.index', ['week' => $weekStart->copy()->subWeek()->toDateString()]) }}" title="Semaine précédente" aria-label="Semaine précédente"><x-icon name="chevron-left" /></a>
        <a class="btn btn-outline-secondary" href="{{ route('planning.index', ['week' => now()->startOfWeek()->toDateString()]) }}">Semaine actuelle</a>
        <a class="btn btn-outline-secondary" href="{{ route('planning.index', ['week' => $weekStart->copy()->addWeek()->toDateString()]) }}" title="Semaine suivante" aria-label="Semaine suivante"><x-icon name="chevron-right" /></a>
        <button type="button" class="btn btn-outline-primary" onclick="window.print()"><x-icon name="printer" />Imprimer</button>
    </div>
</div>

<div class="card-box">
    <div class="panel-head">
        <div>
            <h2 class="section-title">TAZA Gare Routière</h2>
            <div class="section-sub">Affectations par période et par jour</div>
        </div>
    </div>

    <form method="POST" action="{{ route('planning.bulk-save') }}" data-submit-state>
        @csrf
        <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">

        <div class="planning-shell table-responsive">
            <table class="station-planning">
                <thead>
                    <tr>
                        <th class="day-head text-start">Employé</th>
                        @foreach ($days as $day)
                            <th class="day-head">
                                <span class="day-name">{{ $day->locale('fr')->translatedFormat('l') }}</span>
                                <span class="day-date">{{ $day->format('d/m') }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    <tr class="period-row">
                        <th colspan="8"><span class="period-pill">Matin</span></th>
                    </tr>

                    @foreach ($periodEmployees['MATIN'] as $employee)
                        @include('planning.partials.employee-row', ['period' => 'MATIN'])
                    @endforeach

                    <tr class="period-row">
                        <th colspan="8"><span class="period-pill">Soir</span></th>
                    </tr>

                    @foreach ($periodEmployees['SOIR'] as $employee)
                        @include('planning.partials.employee-row', ['period' => 'SOIR'])
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="save-row">
            <button class="btn btn-primary px-4" type="submit"><x-icon name="save" />Enregistrer la semaine</button>
        </div>
    </form>
</div>

@endsection
