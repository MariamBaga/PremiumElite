@extends('adminlte::page')

@section('title', 'FTTH')
@section('content_header')
    <h1>Dossier FTTH</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            {{-- FILTRES --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-2">
                    <select name="type" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Type --</option>
                        <option value="residentiel" @selected(request('type') === 'residentiel')>Résidentiel</option>
                        <option value="professionnel" @selected(request('type') === 'professionnel')>Professionnel</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <input name="search" class="form-control" placeholder="Recherche (nom, tel, email…)"
                        value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <input name="numero_ligne" class="form-control" placeholder="N° ligne"
                        value="{{ request('numero_ligne') }}">
                </div>

                <div class="col-md-2">
                    <input name="numero_point_focal" class="form-control" placeholder="Point focal"
                        value="{{ request('numero_point_focal') }}">
                </div>

                <div class="col-md-3">
                    <input name="localisation" class="form-control" placeholder="Localisation"
                        value="{{ request('localisation') }}">
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <input type="date" name="date_paiement_from" class="form-control"
                            value="{{ request('date_paiement_from') }}">
                        <input type="date" name="date_paiement_to" class="form-control"
                            value="{{ request('date_paiement_to') }}">
                    </div>
                    <small class="text-muted">Période paiement</small>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <input type="date" name="date_affect_from" class="form-control"
                            value="{{ request('date_affect_from') }}">
                        <input type="date" name="date_affect_to" class="form-control"
                            value="{{ request('date_affect_to') }}">
                    </div>
                    <small class="text-muted">Période affectation</small>
                </div>







                <div class="col-md-3">
                    <div class="input-group">
                        <select name="sort" class="form-control">
                            @php $sort = request('sort','created_at'); @endphp
                            <option value="created_at" @selected($sort === 'created_at')>Tri: création</option>
                            <option value="nom" @selected($sort === 'nom')>Nom</option>
                            <option value="prenom" @selected($sort === 'prenom')>Prénom</option>
                            <option value="raison_sociale" @selected($sort === 'raison_sociale')>Raison sociale</option>
                            <option value="numero_ligne" @selected($sort === 'numero_ligne')>N° ligne</option>
                            <option value="numero_point_focal" @selected($sort === 'numero_point_focal')>Point focal</option>
                            <option value="localisation" @selected($sort === 'localisation')>Localisation</option>
                            <option value="date_paiement" @selected($sort === 'date_paiement')>Date paiement</option>
                            <option value="date_affectation" @selected($sort === 'date_affectation')>Date affectation</option>
                        </select>
                        <select name="dir" class="form-control">
                            @php $dir = request('dir','desc'); @endphp
                            <option value="asc" @selected($dir === 'asc')>Asc</option>
                            <option value="desc" @selected($dir === 'desc')>Desc</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="per_page" class="form-control">
                        @foreach ([10, 15, 50, 100] as $n)
                            <option value="{{ $n }}" @selected(request('per_page', 15) == $n)>{{ $n }}/page
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 text-end">
                    <button class="btn btn-outline-primary w-100">Filtrer</button>
                </div>
                @can('clients.create')
                    <div class="col-md-2 text-end">
                        <a href="{{ route('clients.create') }}" class="btn btn-primary w-100">Nouveau Dossier FTTH</a>
                    </div>
                @endcan

            </form>


            <!-- Bouton Supprimer tous
                                        <div class="col-md-2 text-end">
                                            <form id="deleteAllForm" action="{{ route('clients.deleteAll') }}" method="POST"
                                                  onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer TOUS les clients ? Cette action est irréversible.')"
                                                  class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger w-100">
                                                    Supprimer tous
                                                </button>
                                            </form>
                                        </div> -->


            <!-- // Importer -->
            @can('clients.create')
                <div class="col-md-3">
                    <form action="{{ route('clients.import') }}" method="POST" enctype="multipart/form-data"
                        class="d-flex gap-2">
                        @csrf
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                        <button class="btn btn-primary w-100">Importer</button>
                    </form>
                </div>
            @endcan
            @if (session('success'))
                <div class="alert alert-success mt-3">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger mt-3">{{ session('error') }}</div>
            @endif

            <!-- importe -->

            {{-- TABLEAU --}}
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>


                        <tr>
                            <th>#</th>
                            <th>Abonner</th>
                            <th class="text-nowrap">Type</th>
                            <th class="text-nowrap">Téléphone</th>
                            <th class="text-nowrap">Email</th>
                            <th class="text-truncate" style="max-width:160px;">Localisation</th>
                            <th class="text-nowrap">N° ligne</th>
                            <th class="text-nowrap">Point focal</th>
                            <th class="text-nowrap">Date paiement</th>
                            <th class="text-nowrap">Date affect.</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            use Carbon\Carbon;

                            $today = Carbon::today();
                        @endphp

                        @forelse($clients as $c)
                            @php
                                $dossier = $c->lastDossier ?? null;

                                $class = '';
                                if ($dossier && $dossier->date_paiement) {
                                    $days = $today->diffInDays(Carbon::parse($dossier->date_paiement));
                                    if ($days > 3) {
                                        $class = 'table-danger'; // rouge
                                    } elseif ($days > 1) {
                                        $class = 'table-warning'; // jaune
                                    }
                                }
                            @endphp

                            <tr class="{{ $class }}">
                                <td>{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</td>
                                <td class="text-truncate" style="max-width:220px;">
                                    {{ $c->displayName }}
                                </td>
                                <td>{{ ucfirst($c->type) }}</td>
                                <td class="text-nowrap">{{ $c->telephone }}</td>
                                <td class="text-nowrap">{{ $c->email }}</td>
                                <td class="text-truncate" style="max-width:160px;">
                                    {{ $c->localisation ?? $c->adresse_ligne1 }}</td>
                                <td class="text-nowrap">{{ $c->numero_ligne }}</td>
                                <td class="text-nowrap">{{ $c->numero_point_focal }}</td>
                                <td class="text-nowrap">{{ optional($c->lastDossier?->date_paiement)->format('d/m/Y') }}
                                </td>
                                <td class="text-nowrap">
                                    {{ optional($c->lastDossier?->date_affectation)->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap gap-1 justify-content-end align-items-center">

                                        {{-- Ouvrir / Éditer --}}
                                        <a class="btn btn-sm btn-outline-secondary"
                                            href="{{ route('clients.show', $c) }}">Ouvrir</a>
                                        <a class="btn btn-sm btn-outline-primary"
                                            href="{{ route('clients.edit', $c) }}">Éditer</a>

                                        @php
                                            $dossier =
                                                $c->lastDossier ??
                                                new \App\Models\DossierRaccordement(['client_id' => $c->id]);
                                        @endphp


                                        @can('dossiers.assign')
                                            {{-- Créer un dossier si aucun n'existe --}}
                                            {{-- Affectation équipe --}}
                                            <form method="POST" action="{{ route('dossiers.assign-team', $dossier) }}"
                                                class="d-inline-flex">
                                                @csrf
                                                <select name="assigned_team_id" class="form-select form-select-sm"
                                                    onchange="this.form.submit()">
                                                    <option value="">-- Équipe --</option>
                                                    @foreach (\App\Models\Team::all() as $team)
                                                        <option value="{{ $team->id }}" @selected($dossier->assigned_team_id == $team->id)>
                                                            {{ $team->name }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @endcan
                                        {{-- Statut --}}
                                        @can('updateStatus', $dossier)
                                            <form method="POST" action="{{ route('dossiers.status', $dossier) }}"
                                                class="d-inline-flex align-items-center gap-1">
                                                @csrf
                                                 <select name="statut" class="form-select form-select-sm statut-select"
                                data-dossier-id="{{ $dossier->id }}" required>
                                @php $user = auth()->user(); @endphp
                                @foreach (\App\Enums\StatutDossier::labels() as $value => $label)
                                    @if (
                                        $value === \App\Enums\StatutDossier::EN_EQUIPE->value &&
                                            $user->hasRole('chef_equipe') &&
                                            !$user->hasAnyRole(['superadmin', 'coordinateur']))
                                        @continue
                                    @endif
                                    <option value="{{ $value }}" @selected($dossier->statut?->value === $value)>

                                    {{ $label }}</option>
                                @endforeach
                            </select>

                                                <button class="btn btn-sm btn-primary">OK</button>
                                            </form>
                                        @endcan

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">Aucun dossier FTTH</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="mt-2">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
@stop
