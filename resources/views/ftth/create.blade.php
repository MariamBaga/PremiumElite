@extends('adminlte::page')

{{-- Active le sélecteur de date/heure d’adminlte --}}
@section('plugins.TempusDominusBs4', true)

@section('title', 'Création — Abonné & Dossier')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Création — Abonner & Dossier</h1>
    </div>
@stop

@section('content')
    @php $tab = request('tab', session('active_tab','client')); @endphp

    <div class="card">
        <div class="card-body">

            {{-- Nav Onglets --}}
            <ul class="nav nav-pills mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'client' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['tab' => 'client']) }}">
                        Nouveau Dossier Abonné
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'dossier' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['tab' => 'dossier']) }}">
                        Nouveau dossier Ftth
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- =================== ONGLET : NOUVEAU CLIENT =================== --}}
                <div class="tab-pane fade {{ $tab === 'client' ? 'show active' : '' }}" id="tab-client" role="tabpanel">
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
                <div class="col-md-6 mb-3">
                    <label>Description / Observations</label>
                    <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                </div>

            </div>

            <button class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">Annuler</a>
        </form>
                </div>

                {{-- =================== ONGLET : NOUVEAU DOSSIER =================== --}}
                <div class="tab-pane fade {{ $tab === 'dossier' ? 'show active' : '' }}" id="tab-dossier"
                    role="tabpanel">
                    {{-- On affiche les erreurs ici si on est sur l’onglet dossier --}}
                    @if ($errors->any() && $tab === 'dossier')
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('dossiers.store') }}">
                        @csrf
                        <input type="hidden" name="tab" value="dossier">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Abonné</label>
                                <select name="client_id" class="form-control" required>
                                    @foreach ($clients as $c)
                                        <option value="{{ $c->id }}" @selected(old('client_id') == $c->id)>
                                            {{ $c->displayName ?? ($c->raison_sociale ?? trim(($c->prenom ?? '') . ' ' . ($c->nom ?? ''))) }}
                                            — {{ $c->telephone }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Type de service</label>
                                <select name="type_service" class="form-control" required>
                                    <option value="residentiel" @selected(old('type_service') === 'residentiel')>Résidentiel</option>
                                    <option value="professionnel" @selected(old('type_service') === 'professionnel')>Professionnel</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Date planifiée</label>
                                <div class="input-group date" id="dtp_planifiee" data-target-input="nearest">
                                    <input type="text" name="date_planifiee"
                                        class="form-control datetimepicker-input @error('date_planifiee') is-invalid @enderror"
                                        data-target="#dtp_planifiee"
                                        value="{{ old('date_planifiee', isset($dossier) ? optional($dossier->date_planifiee)->format('Y-m-d H:i') : '') }}"
                                        placeholder="YYYY-MM-DD HH:mm">
                                    <div class="input-group-append" data-target="#dtp_planifiee"
                                        data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="far fa-clock"></i></div>
                                    </div>
                                </div>
                                @error('date_planifiee')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>PBO</label>
                                <input name="pbo" class="form-control" value="{{ old('pbo') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>PM</label>
                                <input name="pm" class="form-control" value="{{ old('pm') }}">
                            </div>
                            <div class="col-12 mb-3">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary">Enregistrer le dossier</button>
                            <a href="{{ route('dossiers.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        // Rester sur le bon onglet après back/validation server
        (function() {
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab') || '{{ $tab }}';
            if (tab) {
                document.querySelectorAll('.nav-link').forEach(a => {
                    const url = new URL(a.href);
                    if (url.searchParams.get('tab') === tab) a.classList.add('active');
                    else a.classList.remove('active');
                });
            }
        })();

        // Masque/affiche les blocs selon le type client
        function toggleIdentityBlocks() {
            const type = document.getElementById('type_client')?.value;
            document.querySelectorAll('.bloc-resi').forEach(el => el.style.display = (type === 'residentiel') ? '' :
                'none');
            document.querySelectorAll('.bloc-pro').forEach(el => el.style.display = (type === 'professionnel') ? '' :
                'none');
        }
        document.addEventListener('DOMContentLoaded', () => {
            toggleIdentityBlocks();
            const sel = document.getElementById('type_client');
            if (sel) sel.addEventListener('change', toggleIdentityBlocks);

            // DateTime picker (TempusDominus)
            $('#dtp_planifiee').datetimepicker({
                format: 'YYYY-MM-DD HH:mm'
            });
        });
    </script>
@endpush
