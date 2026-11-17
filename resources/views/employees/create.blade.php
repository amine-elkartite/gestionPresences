@extends('layouts.app')

@section('title', 'Ajouter un employé | Gestion des Absences')
@section('page-title', 'Ajouter un employé')

@section('content')
    <div class="page-head">
        <div>
            <div class="page-kicker">Nouveau profil</div>
            <h1 class="page-heading">Ajouter un employé</h1>
            <div class="page-subtitle">Créez l’accès PIN et les informations RH principales.</div>
        </div>
    </div>

    <div class="card-box">
        <form method="POST" action="{{ route('employees.store') }}" data-submit-state>
            @include('employees._form', ['button' => 'Créer l’employé'])
        </form>
    </div>
@endsection
