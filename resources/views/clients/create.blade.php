@extends('adminlte::page')
@section('title','Nouveau client')
@section('content_header')
  <h1>Nouveau client</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('clients.store') }}">
      @csrf
      <div class="row">
        {{-- Type --}}
        <div class="col-md-3 mb-3">
          <label>Type</label>
          <select name="type" id="type_client" class="form-control" required>
            <option value="residentiel" @selected(old('type')==='residentiel')>Résidentiel</option>
            <option value="professionnel" @selected(old('type')==='professionnel')>Professionnel</option>
          </select>
        </div>

        {{-- Résidentiel --}}
        <div class="col-md-3 mb-3 bloc-resi">
          <label>Nom</label>
          <input name="nom" class="form-control" value="{{ old('nom') }}">
        </div>
        <div class="col-md-3 mb-3 bloc-resi">
          <label>Prénom</label>
          <input name="prenom" class="form-control" value="{{ old('prenom') }}">
        </div>

        {{-- Professionnel --}}
        <div class="col-md-3 mb-3 bloc-pro">
          <label>Raison sociale</label>
          <input name="raison_sociale" class="form-control" value="{{ old('raison_sociale') }}">
        </div>

        {{-- Contacts --}}
        <div class="col-md-3 mb-3">
          <label>Téléphone</label>
          <input name="telephone" class="form-control" value="{{ old('telephone') }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" value="{{ old('email') }}">
        </div>

        {{-- Adresse --}}
        <div class="col-md-6 mb-3">
          <label>Adresse</label>
          <input name="adresse_ligne1" class="form-control" required value="{{ old('adresse_ligne1') }}">
        </div>
        <div class="col-md-6 mb-3">
          <label>Complément d’adresse</label>
          <input name="adresse_ligne2" class="form-control" value="{{ old('adresse_ligne2') }}">
        </div>

        <div class="col-md-3 mb-3">
          <label>Ville</label>
          <input name="ville" class="form-control" value="{{ old('ville') }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Zone</label>
          <input name="zone" class="form-control" value="{{ old('zone') }}">
        </div>

        {{-- 🆕 Champs ajoutés --}}
        <div class="col-md-3 mb-3">
          <label>N° de ligne</label>
          <input name="numero_ligne" class="form-control" value="{{ old('numero_ligne') }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>N° point focal</label>
          <input name="numero_point_focal" class="form-control" value="{{ old('numero_point_focal') }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Localisation</label>
          <input name="localisation" class="form-control" value="{{ old('localisation') }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Date de paiement</label>
          <input type="date" name="date_paiement" class="form-control" value="{{ old('date_paiement') }}">
          @error('date_paiement')
    <div class="invalid-feedback d-block">{{ $message }}</div>
@enderror
        </div>
        <div class="col-md-3 mb-3">
          <label>Date d’affectation</label>
          <input type="date" name="date_affectation" class="form-control" value="{{ old('date_affectation') }}">
          @error('date_affectation')
    <div class="invalid-feedback d-block">{{ $message }}</div>
@enderror
        </div>

        {{-- Géo --}}
        <div class="col-md-3 mb-3">
          <label>Latitude</label>
          <input type="number" step="0.0000001" name="latitude" class="form-control" value="{{ old('latitude') }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Longitude</label>
          <input type="number" step="0.0000001" name="longitude" class="form-control" value="{{ old('longitude') }}">
        </div>
      </div>

      <button class="btn btn-primary">Enregistrer</button>
      <a href="{{ route('clients.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
</div>
@stop

@push('js')
<script>
  // Masque/affiche les blocs selon le type sélectionné
  function toggleIdentityBlocks() {
    const type = document.getElementById('type_client').value;
    document.querySelectorAll('.bloc-resi').forEach(el => el.style.display = (type === 'residentiel') ? '' : 'none');
    document.querySelectorAll('.bloc-pro').forEach(el => el.style.display = (type === 'professionnel') ? '' : 'none');
  }
  document.addEventListener('DOMContentLoaded', () => {
    toggleIdentityBlocks();
    document.getElementById('type_client').addEventListener('change', toggleIdentityBlocks);
  });
</script>
@endpush
