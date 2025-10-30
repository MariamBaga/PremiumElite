@extends('adminlte::page')

@section('title', 'Clients Activés')
@section('content_header')
    <h1>Liste des Clients Activés</h1>
@stop

@section('content')
<div class="mb-3">
    <a href="{{ route('export.clients.pdf') }}" class="btn btn-danger">
        <i class="fa fa-file-pdf"></i> Télécharger PDF
    </a>
    <a href="{{ route('export.clients.excel') }}" class="btn btn-success">
        <i class="fa fa-file-excel"></i> Télécharger Excel
    </a>
</div>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>Client</th>
            <th>Téléphone</th>
            <th>Statut</th>
            <th>Date RDV</th>
            <th>Port</th>
            <th>Linéaire</th>
            <th>Type de câble</th>
            <th>Localité</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dossiers as $dossier)
        <tr>
            <td>{{ $dossier->client->displayName ?? '-' }}</td>
            <td>{{ $dossier->client->telephone ?? '-' }}</td>
            <td>{{ ucfirst($dossier->statut->value) }}</td>

            <td>{{ $dossier->date_planifiee ?? '-' }}</td>
            <td>{{ $dossier->port ?? '-' }}</td>
            <td>{{ $dossier->lineaire_m ?? '-' }}</td>
            <td>{{ $dossier->type_cable ?? '-' }}</td>
            <td>{{ $dossier->localite ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-3">
            {{ $dossiers->links('pagination::bootstrap-5') }}
        </div>
@stop
