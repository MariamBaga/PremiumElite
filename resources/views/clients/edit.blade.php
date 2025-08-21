@extends('adminlte::page')
@section('title','Modifier client')
@section('content_header')<h1>Modifier le client #{{ $client->id }}</h1>@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('clients.update',$client) }}">
      @csrf
      @method('PUT')
      <div class="row">
        <div class="col-md-3 mb-3">
          <label>Type</label>
          <select name="type" class="form-control" required>
            <option value="residentiel" @selected($client->type==='residentiel')>Résidentiel</option>
            <option value="professionnel" @selected($client->type==='professionnel')>Professionnel</option>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label>Nom</label>
          <input name="nom" class="form-control" value="{{ $client->nom }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Prénom</label>
          <input name="prenom" class="form-control" value="{{ $client->prenom }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Raison sociale</label>
          <input name="raison_sociale" class="form-control" value="{{ $client->raison_sociale }}">
        </div>

        <div class="col-md-3 mb-3">
          <label>Téléphone</label>
          <input name="telephone" class="form-control" value="{{ $client->telephone }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" value="{{ $client->email }}">
        </div>

        <div class="col-md-6 mb-3">
          <label>Adresse</label>
          <input name="adresse_ligne1" class="form-control" value="{{ $client->adresse_ligne1 }}" required>
        </div>
        <div class="col-md-6 mb-3">
          <label>Complément d’adresse</label>
          <input name="adresse_ligne2" class="form-control" value="{{ $client->adresse_ligne2 }}">
        </div>

        <div class="col-md-3 mb-3">
          <label>Ville</label>
          <input name="ville" class="form-control" value="{{ $client->ville }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Zone</label>
          <input name="zone" class="form-control" value="{{ $client->zone }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Latitude</label>
          <input type="number" step="0.0000001" name="latitude" class="form-control" value="{{ $client->latitude }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Longitude</label>
          <input type="number" step="0.0000001" name="longitude" class="form-control" value="{{ $client->longitude }}">
        </div>
      </div>

      <button class="btn btn-primary">Mettre à jour</button>
      <a href="{{ route('clients.show',$client) }}" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
</div>
@stop
