@extends('layouts.app')

@section('title', 'Modifier un employé | Gestion des Absences')
@section('page-title', 'Modifier un employé')

@section('content')
    <div class="page-head">
        <div>
            <div class="page-kicker">Mise à jour</div>
            <h1 class="page-heading">Modifier un employé</h1>
            <div class="page-subtitle">Informations personnelles et professionnelles du collaborateur.</div>
        </div>
    </div>

    <div class="card-box">
        <form method="POST" action="{{ route('employees.update', $employee) }}" data-submit-state>
            @method('PUT')
            @include('employees._form', ['button' => 'Mettre à jour l’employé'])
        </form>
    </div>
@endsection
