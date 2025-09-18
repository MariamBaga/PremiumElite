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
                    <label>Statut du dossier</label>
                    <select name="statut[]" class="form-control" multiple>
                        <option value="en_appel">En appel</option>
                        <option value="injoignable">Injoignable</option>
                        <option value="nouveau_rendez_vous">Rendez-vous</option>
                        <option value="pbo_sature">PBO saturé</option>
                        <option value="zone_depouvue">Zone dépourvue</option>
                        <option value="active">Activé</option>
                        <option value="realise">Réalisé</option>
                    </select>
                    <small class="form-text text-muted">Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs statuts.</small>
                </div>
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

            <button class="btn btn-primary mt-3">
                <i class="fas fa-download"></i> Télécharger
            </button>
        </form>
    </div>
</div>
@stop
