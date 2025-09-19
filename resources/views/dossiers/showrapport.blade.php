@extends('adminlte::page')

@section('title', 'Liste des dossiers avec rapport ou RDV')

@section('content_header')
    <h1>Rapports & Rendez-vous</h1>
@stop

@section('content')
    @forelse($dossiers as $dossier)
        <div class="card mb-3">
            <div class="card-header">
                {{-- 🔹 Nom ou Raison Sociale du client --}}
                Client :
                <strong>
                    {{ $dossier->client?->nom ?? ($dossier->client?->raison_sociale ?? '—') }}
                </strong>

                {{-- 🔹 Contact du client (téléphone ou email) --}}
                @if ($dossier->client?->telephone || $dossier->client?->email)
                    <span class="ms-3">
                        📞 {{ $dossier->client?->telephone ?? '—' }}
                        @if ($dossier->client?->email)
                            | ✉️ {{ $dossier->client->email }}
                        @endif
                    </span>
                @endif

                {{-- Statut du dossier --}}
                <span class="badge bg-info ms-3">
                    {{ strtoupper(str_replace('_', ' ', $dossier->statut?->value ?? '—')) }}
                </span>
            </div>

            <div class="card-body">
                <dl class="row mb-2">
                    <dt class="col-sm-4">Date planifiée / RDV</dt>
                    <dd class="col-sm-8">{{ optional($dossier->date_planifiee)->format('d/m/Y H:i') ?? '—' }}</dd>

                    <dt class="col-sm-4">Description / Commentaire</dt>
                    <dd class="col-sm-8">{{ $dossier->description ?? '—' }}</dd>

                    @if($dossier->raison_non_activation)
                        <dt class="col-sm-4">Raison non activation</dt>
                        <dd class="col-sm-8">{{ $dossier->raison_non_activation }}</dd>
                    @endif
                </dl>

                {{-- Rapport d'intervention --}}
                @if($dossier->rapport_intervention)
                    <p><strong>Rapport d'intervention :</strong> {{ $dossier->rapport_intervention }}</p>
                @endif

                {{-- Rapport signé --}}
                @if($dossier->rapport_satisfaction)
                    <p>
                        <strong>Rapport Signé :</strong>
                        <a href="{{ Storage::url($dossier->rapport_satisfaction) }}" target="_blank" class="btn btn-primary btn-sm">
                            📄 Voir le fichier
                        </a>
                    </p>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-info">Aucun dossier avec rapport ou RDV trouvé.</div>
    @endforelse

    {{ $dossiers->links('pagination::bootstrap-5') }}
@stop
