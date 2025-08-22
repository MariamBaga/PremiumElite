@extends('adminlte::page')
@section('title','Modifier client')
@section('content_header')<h1>Modifier le client #{{ $client->id }}</h1>@stop

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

    <form method="POST" action="{{ route('clients.update',$client) }}">
      @csrf @method('PUT')

      <div class="row">
        {{-- Type --}}
        <div class="col-md-3 mb-3">
          <label>Type</label>
          <select name="type" id="type_client" class="form-control" required>
            <option value="residentiel" @selected(old('type',$client->type)==='residentiel')>Résidentiel</option>
            <option value="professionnel" @selected(old('type',$client->type)==='professionnel')>Professionnel</option>
          </select>
        </div>

        {{-- Identité résidentiel --}}
        <div class="col-md-3 mb-3 bloc-resi">
          <label>Nom</label>
          <input name="nom" class="form-control @error('nom') is-invalid @enderror"
                 value="{{ old('nom', $client->nom) }}">
          @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3 mb-3 bloc-resi">
          <label>Prénom</label>
          <input name="prenom" class="form-control @error('prenom') is-invalid @enderror"
                 value="{{ old('prenom', $client->prenom) }}">
          @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Identité pro --}}
        <div class="col-md-3 mb-3 bloc-pro">
          <label>Raison sociale</label>
          <input name="raison_sociale" class="form-control @error('raison_sociale') is-invalid @enderror"
                 value="{{ old('raison_sociale', $client->raison_sociale) }}">
          @error('raison_sociale')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Contacts --}}
        <div class="col-md-3 mb-3">
          <label>Téléphone</label>
          <input name="telephone" class="form-control @error('telephone') is-invalid @enderror"
                 value="{{ old('telephone', $client->telephone) }}">
          @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3 mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email', $client->email) }}">
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Adresse --}}
        <div class="col-md-6 mb-3">
          <label>Adresse</label>
          <input name="adresse_ligne1" class="form-control @error('adresse_ligne1') is-invalid @enderror"
                 value="{{ old('adresse_ligne1', $client->adresse_ligne1) }}" required>
          @error('adresse_ligne1')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 mb-3">
          <label>Complément d’adresse</label>
          <input name="adresse_ligne2" class="form-control @error('adresse_ligne2') is-invalid @enderror"
                 value="{{ old('adresse_ligne2', $client->adresse_ligne2) }}">
          @error('adresse_ligne2')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-3 mb-3">
          <label>Ville</label>
          <input name="ville" class="form-control @error('ville') is-invalid @enderror"
                 value="{{ old('ville', $client->ville) }}">
          @error('ville')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3 mb-3">
          <label>Zone</label>
          <input name="zone" class="form-control @error('zone') is-invalid @enderror"
                 value="{{ old('zone', $client->zone) }}">
          @error('zone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- 🆕 Champs ajoutés --}}
        <div class="col-md-3 mb-3">
          <label>N° de ligne</label>
          <input name="numero_ligne" class="form-control @error('numero_ligne') is-invalid @enderror"
                 value="{{ old('numero_ligne', $client->numero_ligne) }}">
          @error('numero_ligne')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3 mb-3">
          <label>N° point focal</label>
          <input name="numero_point_focal" class="form-control @error('numero_point_focal') is-invalid @enderror"
                 value="{{ old('numero_point_focal', $client->numero_point_focal) }}">
          @error('numero_point_focal')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3 mb-3">
          <label>Localisation</label>
          <input name="localisation" class="form-control @error('localisation') is-invalid @enderror"
                 value="{{ old('localisation', $client->localisation) }}">
          @error('localisation')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3 mb-3">
          <label>Date de paiement</label>
          <input type="date" name="date_paiement" class="form-control @error('date_paiement') is-invalid @enderror"
                 value="{{ old('date_paiement', optional($client->date_paiement)->format('Y-m-d')) }}">
          @error('date_paiement')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3 mb-3">
          <label>Date d’affectation</label>
          <input type="date" name="date_affectation" class="form-control @error('date_affectation') is-invalid @enderror"
                 value="{{ old('date_affectation', optional($client->date_affectation)->format('Y-m-d')) }}">
          @error('date_affectation')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Géo --}}
        <div class="col-md-3 mb-3">
          <label>Latitude</label>
          <input type="number" step="0.0000001" name="latitude"
                 class="form-control @error('latitude') is-invalid @enderror"
                 value="{{ old('latitude', $client->latitude) }}">
          @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3 mb-3">
          <label>Longitude</label>
          <input type="number" step="0.0000001" name="longitude"
                 class="form-control @error('longitude') is-invalid @enderror"
                 value="{{ old('longitude', $client->longitude) }}">
          @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <button class="btn btn-primary">Mettre à jour</button>
      <a href="{{ route('clients.show',$client) }}" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
</div>
@stop

@push('js')
<script>
  // Afficher/masquer les blocs selon le type
  function toggleIdentityBlocks() {
    const type = document.getElementById('type_client').value;
    document.querySelectorAll('.bloc-resi').forEach(el => el.style.display = (type === 'residentiel') ? '' : 'none');
    document.querySelectorAll('.bloc-pro').forEach(el => el.style.display   = (type === 'professionnel') ? '' : 'none');
  }
  document.addEventListener('DOMContentLoaded', () => {
    toggleIdentityBlocks();
    document.getElementById('type_client').addEventListener('change', toggleIdentityBlocks);
  });
</script>
@endpush
