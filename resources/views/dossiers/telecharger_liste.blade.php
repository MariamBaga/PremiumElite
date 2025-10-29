@extends('adminlte::page')

@section('title', 'Télécharger la liste des dossiers')

@section('content')
<div class="container py-4">

    <h2 class="text-center mb-4 fw-bold text-primary">
        <i class="fas fa-file-download me-2"></i> Exporter la liste des dossiers
    </h2>

    @if(session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    <div class="card shadow-lg border-0 rounded-4 p-4">
        <form action="{{ route('dossiers.telechargerListe') }}" method="GET" class="row g-3 align-items-end">

            @role('admin|superadmin')
                <div class="col-md-4">
                    <label for="team_id" class="form-label">Équipe :</label>
                    <select name="team_id" id="team_id" class="form-select" required>
                        <option value="">-- Sélectionner une équipe --</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @endrole

            <div class="col-md-4">
                <label for="statut" class="form-label">Statut des dossiers :</label>
                <select name="statut" id="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="realise">Réalisés</option>
                    <option value="en_cours">En cours</option>
                    <option value="planifie">Planifiés</option>
                    <option value="annule">Annulés</option>
                </select>
            </div>

            <div class="col-md-4 text-center">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-download me-2"></i> Télécharger
                </button>
            </div>
        </form>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour à la liste des dossiers
        </a>
    </div>

</div>
@endsection
