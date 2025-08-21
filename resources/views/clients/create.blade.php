@extends('adminlte::page')
@section('title','Nouveau client')
@section('content_header')<h1>Nouveau client</h1>@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('clients.store') }}">
      @csrf
      <div class="row">
        <div class="col-md-3 mb-3">
          <label>Type</label>
          <select name="type" class="form-control" required>
            <option value="residentiel">Résidentiel</option>
            <option value="professionnel">Professionnel</option>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label>Nom</label>
          <input name="nom" class="form-control">
        </div>
        <div class="col-md-3 mb-3">
          <label>Prénom</label>
          <input name="prenom" class="form-control">
        </div>
        <div class="col-md-3 mb-3">
          <label>Raison sociale</label>
          <input name="raison_sociale" class="form-control">
        </div>

        <div class="col-md-3 mb-3">
          <label>Téléphone</label>
          <input name="telephone" class="form-control">
        </div>
        <div class="col-md-3 mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control">
        </div>

        <div class="col-md-6 mb-3">
          <label>Adresse</label>
          <input name="adresse_ligne1" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label>Complément d’adresse</label>
          <input name="adresse_ligne2" class="form-control">
        </div>

        <div class="col-md-3 mb-3">
          <label>Ville</label>
          <input name="ville" class="form-control">
        </div>
        <div class="col-md-3 mb-3">
          <label>Zone</label>
          <input name="zone" class="form-control">
        </div>
        <div class="col-md-3 mb-3">
          <label>Latitude</label>
          <input type="number" step="0.0000001" name="latitude" class="form-control">
        </div>
        <div class="col-md-3 mb-3">
          <label>Longitude</label>
          <input type="number" step="0.0000001" name="longitude" class="form-control">
        </div>
      </div>

      <button class="btn btn-primary">Enregistrer</button>
      <a href="{{ route('clients.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
</div>
@stop
