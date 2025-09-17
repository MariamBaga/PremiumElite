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
  <label>Ligne</label>
  <input name="ligne" class="form-control" value="{{ old('ligne', $dossier->ligne ?? '') }}">
</div>

<div class="col-md-3 mb-3">
  <label>Contact</label>
  <input name="contact" class="form-control" value="{{ old('contact', $dossier->contact ?? '') }}">
</div>

<div class="col-md-3 mb-3">
  <label>Service accès</label>
  <select name="service_acces" class="form-control">
    <option value="FTTH" @selected(old('service_acces', $dossier->service_acces ?? '')=='FTTH')>FTTH</option>
    <option value="Cuivre" @selected(old('service_acces', $dossier->service_acces ?? '')=='Cuivre')>Cuivre</option>
  </select>
</div>

<div class="col-md-3 mb-3">
  <label>Localité</label>
  <input name="localite" class="form-control" value="{{ old('localite', $dossier->localite ?? '') }}">
</div>

<div class="col-md-3 mb-3">
  <label>Catégorie</label>
  <select name="categorie" class="form-control">
    <option value="B2B" @selected(old('categorie', $dossier->categorie ?? '')=='B2B')>B2B</option>
    <option value="B2C" @selected(old('categorie', $dossier->categorie ?? '')=='B2C')>B2C</option>
  </select>
</div>

<div class="col-md-3 mb-3">
  <label>Date réception raccordement</label>
  <input type="date" name="date_reception_raccordement" class="form-control"
         value="{{ old('date_reception_raccordement', $dossier->date_reception_raccordement ?? '') }}">
</div>

<div class="col-md-3 mb-3">
  <label>Date fin travaux</label>
  <input type="date" name="date_fin_travaux" class="form-control"
         value="{{ old('date_fin_travaux', $dossier->date_fin_travaux ?? '') }}">
</div>

<div class="col-md-3 mb-3">
  <label>Port</label>
  <input name="port" class="form-control" value="{{ old('port', $dossier->port ?? '') }}">
</div>

<div class="col-md-3 mb-3">
  <label>PBO linéaire utilisé</label>
  <input name="pbo_lineaire_utilise" class="form-control" value="{{ old('pbo_lineaire_utilise', $dossier->pbo_lineaire_utilise ?? '') }}">
</div>

<div class="col-md-3 mb-3">
  <label>Nb poteaux implantés</label>
  <input name="nb_poteaux_implantes" class="form-control" value="{{ old('nb_poteaux_implantes', $dossier->nb_poteaux_implantes ?? '') }}">
</div>

<div class="col-md-3 mb-3">
  <label>Nb armements poteaux</label>
  <input name="nb_armements_poteaux" class="form-control" value="{{ old('nb_armements_poteaux', $dossier->nb_armements_poteaux ?? '') }}">
</div>

<div class="col-md-3 mb-3">
  <label>Taux reporting J1</label>
  <input name="taux_reporting_j1" class="form-control" value="{{ old('taux_reporting_j1', $dossier->taux_reporting_j1 ?? '') }}">
</div>

<div class="col-md-3 mb-3">
  <label>Actif ?</label>
  <select name="is_active" class="form-control">
    <option value="1" @selected(old('is_active', $dossier->is_active ?? 0)==1)>Oui</option>
    <option value="0" @selected(old('is_active', $dossier->is_active ?? 0)==0)>Non</option>
  </select>
</div>

<div class="col-md-6 mb-3">
  <label>Observation</label>
  <textarea name="observation" class="form-control">{{ old('observation', $dossier->observation ?? '') }}</textarea>
</div>

<div class="col-md-6 mb-3">
  <label>Pilote raccordement</label>
  <input name="pilote_raccordement" class="form-control" value="{{ old('pilote_raccordement', $dossier->pilote_raccordement ?? '') }}">
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
