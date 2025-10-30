@extends('adminlte::page')

@section('title', 'Export Dossiers par Équipe et Statut')

@section('content_header')
    <h1><i class="fas fa-file-export"></i> Export des Dossiers par Équipe et Statut</h1>
@stop

@section('content')

@php
    use App\Enums\StatutDossier;
    $statuts = StatutDossier::labels();
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('export.view.team') }}" class="row g-3">
            <div class="col-md-4">
                <label for="team_id" class="form-label">Équipe :</label>
                <select name="team_id" id="team_id" class="form-control" required>
                    <option value="">-- Sélectionner une équipe --</option>
                    @foreach($equipes as $equipe)
                        <option value="{{ $equipe->id }}" {{ ($teamId ?? '') == $equipe->id ? 'selected' : '' }}>
                            {{ $equipe->name ?? 'Équipe '.$equipe->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="statut" class="form-label">Statut :</label>
                <select name="statut" id="statut" class="form-control" required>
                    <option value="">-- Sélectionner un statut --</option>
                    @foreach($statuts as $key => $label)
                        <option value="{{ $key }}" {{ ($statut ?? '') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

@if(isset($teamId) && isset($statut))
<div class="card mt-4 shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            Résultats pour <span class="text-primary">{{ $equipes->firstWhere('id', $teamId)->name ?? 'Équipe '.$teamId }}</span> — Statut :
            <span class="badge bg-info text-dark">{{ $statuts[$statut] ?? ucfirst($statut) }}</span>
        </h5>
        <div>
            <a href="{{ route('export.team.pdf', ['teamId' => $teamId, 'statut' => $statut]) }}" class="btn btn-danger me-2">
                <i class="fa fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('export.team.excel', ['teamId' => $teamId, 'statut' => $statut]) }}" class="btn btn-success">
                <i class="fa fa-file-excel"></i> Excel
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <table class="table table-striped table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Client</th>
                    <th>Téléphone</th>
                    <th>Statut</th>
                    <th>Équipe</th>
                    <th>Date RDV</th>
                    <th>Port</th>
                    <th>Linéaire</th>
                    <th>Type de câble</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dossiers as $dossier)
                <tr>
                    <td>{{ $dossier->client->displayName ?? '-' }}</td>
                    <td>{{ $dossier->client->telephone ?? '-' }}</td>
                    <td>
                        <span class="badge bg-secondary">
                            {{ $dossier->statut instanceof \App\Enums\StatutDossier ? $dossier->statut->value : $dossier->statut }}
                        </span>
                    </td>
                    <td>{{ $dossier->assigned_team->name ?? '-' }}</td>
                    <td>{{ $dossier->date_planifiee ?? '-' }}</td>
                    <td>{{ $dossier->port ?? '-' }}</td>
                    <td>{{ $dossier->lineaire_m ?? '-' }}</td>
                    <td>{{ $dossier->type_cable ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-3">
                        <i class="fas fa-info-circle"></i> Aucun dossier trouvé pour cette équipe/statut.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
            {{ $dossiers->links('pagination::bootstrap-5') }}
        </div>
</div>
@endif
@stop
