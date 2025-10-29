@extends('adminlte::page')

@section('title', 'Mes Fichiers de Dossier')

@section('content')
<div class="container py-4">

    <h2 class="text-center mb-4 fw-bold text-primary">
        <i class="fas fa-folder-open me-2"></i> Mes Fichiers de Dossier
    </h2>

    @if(session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    @if($dossier->statut === 'active')
        <div class="card shadow-lg border-0 rounded-4 p-4 mx-auto" style="max-width: 600px;">
            <h5 class="mb-3 text-success text-center">
                Dossier activé — Téléchargement disponible
            </h5>

            <div class="text-center">
                <p><strong>Client :</strong> {{ $dossier->client->nom ?? 'N/A' }}</p>
                <p><strong>Adresse :</strong> {{ $dossier->client->adresse ?? 'N/A' }}</p>
                <p><strong>Référence dossier :</strong> #{{ $dossier->id }}</p>
            </div>

            <div class="d-grid gap-3 mt-4">
                <a href="{{ route('dossiers.telechargerFichiers', $dossier->id) }}" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i> Télécharger mes fichiers
                </a>
            </div>
        </div>
    @else
        <div class="alert alert-warning text-center">
            <i class="fas fa-clock me-2"></i> Votre dossier n’est pas encore activé.<br>
            Le téléchargement sera disponible une fois la validation effectuée.
        </div>
    @endif

</div>
@endsection
