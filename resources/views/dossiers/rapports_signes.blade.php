@extends('adminlte::page')

@section('title', 'Rapports Signés')

@section('content_header')
    <h1>Rapports Signés</h1>
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
                <span class="badge bg-success ms-3">
                    {{ strtoupper(str_replace('_', ' ', $dossier->statut?->value ?? '—')) }}
                </span>

            </div>

            <div class="card-body">
                {{-- Rapport signé --}}
                <p>
                    <strong>Rapport Signé :</strong>
                    <a href="{{ Storage::url($dossier->rapport_satisfaction) }}" target="_blank"
                        class="btn btn-primary btn-sm">
                        📄 Voir le fichier
                    </a>
                </p>

                {{-- Rapport intervention (texte) --}}
                @if ($dossier->rapport_intervention)
                    <p><strong>Rapport Intervention :</strong> {{ $dossier->rapport_intervention }}</p>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-info">Aucun rapport signé trouvé.</div>
    @endforelse

    {{ $dossiers->links('pagination::bootstrap-5') }}
@stop
