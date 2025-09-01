@extends('adminlte::page')
@section('title','Modifier Dossier Abonné')
@section('content_header')
<h1>Modifier le Dossier Abonné #{{ $client->id }}</h1>
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

    <form method="POST" action="{{ route('clients.update',$client) }}">
      @csrf @method('PUT')
      <div class="row">

        {{-- ---------------- Client ---------------- --}}
        <div class="col-12 mb-3"><h5 class="border-bottom pb-1">Informations Client</h5></div>

        <div class="col-md-3 mb-3">
          <label>Type</label>
          <select name="type" id="type_client" class="form-control" required>
            <option value="residentiel" @selected(old('type',$client->type)==='residentiel')>Résidentiel</option>
            <option value="professionnel" @selected(old('type',$client->type)==='professionnel')>Professionnel</option>
          </select>
        </div>

        {{-- Identité --}}
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

        {{-- Autres infos --}}
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

        {{-- ---------------- Dossier FTTH ---------------- --}}
        <div class="col-12 mb-3 mt-4"><h5 class="border-bottom pb-1">Informations Dossier FTTH</h5></div>

        <div class="col-md-3 mb-3">
          <label>Type de service</label>
          <select name="type_service" class="form-control">
            <option value="residentiel" @selected(old('type_service', $client->type_service)==='residentiel')>Résidentiel</option>
            <option value="professionnel" @selected(old('type_service', $client->type_service)==='professionnel')>Professionnel</option>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label>PBO</label>
          <input name="pbo" class="form-control" value="{{ old('pbo', $client->pbo) }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>PM</label>
          <input name="pm" class="form-control" value="{{ old('pm', $client->pm) }}">
        </div>
        <div class="col-md-3 mb-3">
          <label>Statut</label>
          <select name="statut" class="form-control">
            @foreach(\App\Enums\StatutDossier::cases() as $statut)
                <option value="{{ $statut->value }}" @selected(old('statut', $client->statut ?? '') === $statut->value)>
                    {{ \App\Enums\StatutDossier::labels()[$statut->value] }}
                </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label>Technicien assigné</label>
          <select name="assigned_to" class="form-control">
            <option value="">-- Aucun --</option>
            @foreach(\App\Models\User::role('technicien')->get() as $tech)
              <option value="{{ $tech->id }}" @selected(old('assigned_to', $client->assigned_to)==$tech->id)>{{ $tech->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label>Équipe assignée</label>
          <select name="assigned_team_id" class="form-control">
            <option value="">-- Aucun --</option>
            @foreach(\App\Models\Team::all() as $team)
              <option value="{{ $team->id }}" @selected(old('assigned_team_id', $client->assigned_team_id)==$team->id)>{{ $team->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label>Description / Observations</label>
          <textarea name="description" class="form-control">{{ old('description', $client->description) }}</textarea>
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
