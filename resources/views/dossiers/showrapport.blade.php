@extends('adminlte::page')

@section('title', 'Liste des dossiers avec rapport ou RDV')

@section('content_header')
    <h1>Rapports & Rendez-vous</h1>
@stop

@section('content')
    @foreach($dossiers as $dossier)
        <div class="card mb-3">
            <div class="card-header">
                Dossier #{{ $dossier->id }} - {{ $dossier->client->nom ?? 'N/A' }}
            </div>
            <div class="card-body">
                <p><strong>Statut :</strong> {{ $dossier->statut }}</p>
                <p><strong>Date planifiée :</strong> {{ $dossier->date_planifiee ?? '—' }}</p>
                <p><strong>Description :</strong> {{ $dossier->description ?? '—' }}</p>

                @if($dossier->rapport_intervention)
                    <p><strong>Rapport d'intervention :</strong> {{ $dossier->rapport_intervention }}</p>
                @endif

                @if($dossier->rapport_satisfaction)
                    <p><strong>Rapport de satisfaction :</strong> <a href="{{ Storage::url($dossier->rapport_satisfaction) }}" target="_blank" class="btn btn-primary btn-sm">
                        📄 Voir rapport PDF
                    </a></p>

                @endif
            </div>
        </div>
    @endforeach
@stop
