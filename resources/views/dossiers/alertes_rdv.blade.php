@extends('adminlte::page')

@section('title', 'Alertes Rendez-vous')

@section('content_header')
    <h1>Alertes Rendez-vous</h1>
@stop

@section('content')
@if($rdvs->isEmpty())
    <div class="alert alert-info">Aucun rendez-vous prévu pour aujourd'hui ou demain.</div>
@else
    <div class="alert alert-warning">
        <h4><i class="icon fas fa-bell"></i> Rendez-vous à venir !</h4>
        <ul>
            @foreach($rdvs as $dossier)
                <li>
                    Client : {{ $dossier->client->displayName ?? '-' }} -
                    Date : {{ optional($dossier->date_planifiee)->format('d/m/Y H:i') }} -
                    Référence : {{ $dossier->reference }}
                </li>
            @endforeach
        </ul>
    </div>
@endif
@stop
