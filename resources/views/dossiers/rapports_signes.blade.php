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

                {{-- 🔹 Contact du client --}}
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
                {{-- 🔹 Rapport signé --}}
                @if ($dossier->rapport_satisfaction)
                    @php
                        // Récupère uniquement le nom du fichier sans le chemin "rapports/"
                        $rapportPath = basename($dossier->rapport_satisfaction);

                        $extension = strtolower(pathinfo($rapportPath, PATHINFO_EXTENSION));

                        // Vérifie si c’est une image
                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                        // Nouveau dossier selon le type
                        $fullPath = asset('rapportdesfichiers/' . $rapportPath);

                    @endphp

                    <p><strong>Rapport signé :</strong></p>

                    @if ($isImage)
                        {{-- 🔹 Affiche directement les images --}}
                        <div class="mt-2 mb-3">
                            <img src="{{ $fullPath }}" alt="Rapport signé"
                                 class="img-fluid rounded shadow-sm"
                                 style="max-width: 400px; border: 1px solid #ddd;">
                        </div>
                        <a href="{{ $fullPath }}" target="_blank" class="btn btn-secondary btn-sm">
                            🔍 Voir en taille réelle
                        </a>
                    @else
                        {{-- 🔹 Lien pour fichiers PDF/Word --}}
                        <a href="{{ $fullPath }}" target="_blank" class="btn btn-primary btn-sm">
                            📄 Voir le rapport
                        </a>
                    @endif
                @endif

                {{-- 🔹 Rapport intervention --}}
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
