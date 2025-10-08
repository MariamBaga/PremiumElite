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
                {{-- Rapport signé --}}
                @if($dossier->rapport_satisfaction)
                    @php
                        $path = asset('storage/' . $dossier->rapport_satisfaction);
                        $extension = pathinfo($dossier->rapport_satisfaction, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                    @endphp

                    <p><strong>Rapport signé :</strong></p>

                    @if($isImage)
                        {{-- 🔹 Affichage direct de l’image --}}
                        <div class="mt-2 mb-3">
                            <img src="{{ $path }}"
                                 alt="Rapport signé"
                                 class="img-fluid rounded shadow-sm"
                                 style="max-width: 400px; border: 1px solid #ddd;">
                        </div>
                        <a href="{{ $path }}" target="_blank" class="btn btn-secondary btn-sm">
                            🔍 Voir en taille réelle
                        </a>
                    @else
                        {{-- 🔹 Lien pour fichiers non image --}}
                        <a href="{{ $path }}" target="_blank" class="btn btn-primary btn-sm">
                            📄 Voir le fichier
                        </a>
                    @endif
                @endif


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
