@extends('adminlte::page')
@section('title','Client #'.$client->id)
@section('content_header')<h1>Client #{{ $client->id }}</h1>@stop

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">Informations</div>
      <div class="card-body">
        <dl class="row">
          <dt class="col-sm-3">Type</dt><dd class="col-sm-9">{{ ucfirst($client->type) }}</dd>
          @if($client->type === 'professionnel')
            <dt class="col-sm-3">Raison sociale</dt><dd class="col-sm-9">{{ $client->raison_sociale }}</dd>
          @else
            <dt class="col-sm-3">Nom</dt><dd class="col-sm-9">{{ $client->nom }}</dd>
            <dt class="col-sm-3">Prénom</dt><dd class="col-sm-9">{{ $client->prenom }}</dd>
          @endif
          <dt class="col-sm-3">Téléphone</dt><dd class="col-sm-9">{{ $client->telephone ?? '-' }}</dd>
          <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $client->email ?? '-' }}</dd>
          <dt class="col-sm-3">Adresse</dt><dd class="col-sm-9">{{ $client->adresse_ligne1 }} {{ $client->adresse_ligne2 }}</dd>
          <dt class="col-sm-3">Ville / Zone</dt><dd class="col-sm-9">{{ $client->ville }} / {{ $client->zone }}</dd>
          <dt class="col-sm-3">Coordonnées</dt><dd class="col-sm-9">{{ $client->latitude }}, {{ $client->longitude }}</dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">Actions</div>
      <div class="card-body d-flex gap-2">
        <a href="{{ route('clients.edit',$client) }}" class="btn btn-primary">Éditer</a>
        <form method="POST" action="{{ route('clients.destroy',$client) }}" onsubmit="return confirm('Supprimer ce client ?')">
          @csrf @method('DELETE')
          <button class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>
@stop


inp
