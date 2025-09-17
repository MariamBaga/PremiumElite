@extends('adminlte::page')
@section('title', 'Nouveau client')
@section('content_header')
    <h1>Nouveau Dossier d'Abonné</h1>
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

                {{-- ---------------- Client ---------------- --}}
                <div class="col-12 mb-3">
                    <h5 class="border-bottom pb-1">Informations Client</h5>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Type</label>
                    <select name="type" id="type_client" class="form-control" required>
                        <option value="residentiel" @selected(old('type') === 'residentiel')>Résidentiel</option>
                        <option value="professionnel" @selected(old('type') === 'professionnel')>Professionnel</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3 bloc-resi">
                    <label>Nom</label>
                    <input name="nom" class="form-control" value="{{ old('nom') }}">
                </div>
                <div class="col-md-3 mb-3 bloc-resi">
                    <label>Prénom</label>
                    <input name="prenom" class="form-control" value="{{ old('prenom') }}">
                </div>
                <div class="col-md-3 mb-3 bloc-pro">
                    <label>Raison sociale</label>
                    <input name="raison_sociale" class="form-control" value="{{ old('raison_sociale') }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Téléphone</label>
                    <input name="telephone" class="form-control" value="{{ old('telephone') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>

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
                </div>
                <div class="col-md-3 mb-3">
                    <label>Date d’affectation</label>
                    <input type="date" name="date_affectation" class="form-control" value="{{ old('date_affectation') }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Latitude</label>
                    <input type="number" step="0.0000001" name="latitude" class="form-control" value="{{ old('latitude') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Longitude</label>
                    <input type="number" step="0.0000001" name="longitude" class="form-control" value="{{ old('longitude') }}">
                </div>

                {{-- ---------------- Dossier FTTH ---------------- --}}
                <div class="col-12 mb-3 mt-4">
                    <h5 class="border-bottom pb-1">Informations Dossier FTTH</h5>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Type de service</label>
                    <select name="type_service" class="form-control">
                        <option value="residentiel" @selected(old('type_service') === 'residentiel')>Résidentiel</option>
                        <option value="professionnel" @selected(old('type_service') === 'professionnel')>Professionnel</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>PBO</label>
                    <input name="pbo" class="form-control" value="{{ old('pbo') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>PM</label>
                    <input name="pm" class="form-control" value="{{ old('pm') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Statut</label>
                    <select name="statut" class="form-control">
                        @foreach (\App\Enums\StatutDossier::cases() as $statut)
                            <option value="{{ $statut->value }}" @selected(old('statut') === $statut->value)>
                                {{ \App\Enums\StatutDossier::labels()[$statut->value] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Technicien assigné</label>
                    <select name="assigned_to" class="form-control">
                        <option value="">-- Aucun --</option>
                        @foreach (\App\Models\User::role('technicien')->get() as $tech)
                            <option value="{{ $tech->id }}" @selected(old('assigned_to') == $tech->id)>{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Équipe assignée</label>
                    <select name="assigned_team_id" class="form-control">
                        <option value="">-- Aucun --</option>
                        @foreach (\App\Models\Team::all() as $team)
                            <option value="{{ $team->id }}" @selected(old('assigned_team_id') == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Ligne, Contact, Service Acces --}}
                <div class="col-md-3 mb-3">
                    <label>Ligne</label>
                    <input type="text" name="ligne" class="form-control" value="{{ old('ligne') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Contact</label>
                    <input type="text" name="contact" class="form-control" value="{{ old('contact') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Service accès</label>
                    <select name="service_acces" class="form-control">
                        <option value="">-- Sélectionner --</option>
                        <option value="FTTH" @selected(old('service_acces')==='FTTH')>FTTH</option>
                        <option value="Cuivre" @selected(old('service_acces')==='Cuivre')>Cuivre</option>
                    </select>
                </div>

                {{-- Localité, Catégorie --}}
                <div class="col-md-3 mb-3">
                    <label>Localité</label>
                    <input type="text" name="localite" class="form-control" value="{{ old('localite') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Catégorie</label>
                    <select name="categorie" class="form-control">
                        <option value="">-- Sélectionner --</option>
                        <option value="B2C" @selected(old('categorie')==='B2C')>B2C</option>
                        <option value="B2B" @selected(old('categorie')==='B2B')>B2B</option>
                    </select>
                </div>

                {{-- Dates réception et fin travaux --}}
                <div class="col-md-3 mb-3">
                    <label>Date réception</label>
                    <input type="date" name="date_reception_raccordement" class="form-control" value="{{ old('date_reception_raccordement') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Date fin travaux</label>
                    <input type="date" name="date_fin_travaux" class="form-control" value="{{ old('date_fin_travaux') }}">
                </div>

                {{-- PBO, PM, Port --}}

                <div class="col-md-3 mb-3">
                    <label>Port</label>
                    <input name="port" class="form-control" value="{{ old('port') }}">
                </div>

                {{-- PBO linéaire, poteaux, armements --}}
                <div class="col-md-3 mb-3">
                    <label>PBO/Linéaire</label>
                    <input name="pbo_lineaire_utilise" class="form-control" value="{{ old('pbo_lineaire_utilise') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Poteaux</label>
                    <input name="nb_poteaux_implantes" type="number" class="form-control" value="{{ old('nb_poteaux_implantes') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Armements</label>
                    <input name="nb_armements_poteaux" type="number" class="form-control" value="{{ old('nb_armements_poteaux') }}">
                </div>

                {{-- Taux reporting J+1 --}}
                <div class="col-md-3 mb-3">
                    <label>Taux reporting J+1</label>
                    <select name="taux_reporting_j1" class="form-control">
                        <option value="">-- Sélectionner --</option>
                        <option value="OK" @selected(old('taux_reporting_j1')==='OK')>OK</option>
                        <option value="NOK" @selected(old('taux_reporting_j1')==='NOK')>NOK</option>
                    </select>
                </div>

                {{-- Active --}}
                <div class="col-md-3 mb-3">
                    <label>Active</label>
                    <select name="is_active" class="form-control">
                        <option value="1" @selected(old('is_active')==1)>Oui</option>
                        <option value="0" @selected(old('is_active')==0)>Non</option>
                    </select>
                </div>

                {{-- Observation --}}
                <div class="col-12 mb-3">
                    <label>Observation</label>
                    <textarea name="observation" class="form-control" rows="3">{{ old('observation') }}</textarea>
                </div>

                {{-- Pilote --}}
                <div class="col-md-3 mb-3">
                    <label>Pilote</label>
                    <input name="pilote_raccordement" class="form-control" value="{{ old('pilote_raccordement') }}">
                </div>

                {{-- Description --}}
                <div class="col-12 mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>

                
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
