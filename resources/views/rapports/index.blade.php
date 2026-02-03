@extends('adminlte::page')

@section('title', 'Rapport d’activité')

@section('content_header')
    <h1>Rapport d’activité</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">Filtres</div>
    <div class="card-body">
        <form method="POST" action="{{ route('rapports.export') }}">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <label>Date début</label>
                    <input type="date" name="date_from" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Date fin</label>
                    <input type="date" name="date_to" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Équipe</label>
                    <select name="team_id" class="form-control">
                        <option value="">Toutes les équipes</option>
                        @foreach(\App\Models\Team::all() as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Cases à cocher pour les statuts --}}
            <div class="mt-3">
                <label><strong>Statuts du dossier</strong></label><br>
                @php
                $statuts = [
        'indisponible' => 'Indisponible',
        'injoignable' => 'Injoignable',
        'pbo_sature' => 'PBO saturé',
        'zone_depourvue' => 'Zone dépourvue',
        'realise' => 'Réalisé',
        'en_appel' => 'En appel',
        'en_equipe' => 'En équipe',
        'active' => 'Activé',
        'nouveau_rendez_vous' => 'Nouveau rendez-vous',
    ];
                @endphp

                @foreach($statuts as $value => $label)
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="statut[]" value="{{ $value }}" id="statut_{{ $value }}" class="form-check-input">
                        <label class="form-check-label" for="statut_{{ $value }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>

            <div class="row mt-3">
                <div class="col-md-4">
                    <label>Format</label>
                    <select name="format" class="form-control" required>
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="csv">CSV (.csv)</option>
                        <option value="pdf">PDF (.pdf)</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary">
                    <i class="fas fa-download"></i> Télécharger la sélection
                </button>

                <button class="btn btn-success" name="all_statuses" value="1">
                    <i class="fas fa-download"></i> Télécharger tous les statuts
                </button>
            </div>
        </form>
    </div>
</div>
@stop
