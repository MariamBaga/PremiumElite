@extends('adminlte::page')

@section('title', 'FTTH')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>FTTH </h1>
        <div>
            {{-- Liens "Créer" contextuels selon l’onglet --}}
            @php $tab = request('tab','clients'); @endphp
            @if ($tab === 'clients')
                <a href="{{ route('clients.create') }}" class="btn btn-primary">Nouveau Dossier Abonné</a>
            @else
                @can('dossiers.create')
                    <a href="{{ route('dossiers.create') }}" class="btn btn-primary">Nouveau dossier FTTH</a>
                @endcan
            @endif
        </div>
    </div>
@stop

@section('content')
@php
    $tab = request('tab','clients'); // 'clients' ou 'dossiers'
@endphp

<div class="card">
  <div class="card-body">
    {{-- Onglets --}}
    <ul class="nav nav-pills mb-3" role="tablist">
      <li class="nav-item">
        <a class="nav-link {{ $tab==='clients' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['tab'=>'clients','page'=>null]) }}">
           Dossier Abonné
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $tab==='dossiers' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['tab'=>'dossiers','page'=>null]) }}">
           Dossiers FTTH
        </a>
      </li>
    </ul>

    {{-- Contenu des onglets --}}
    <div class="tab-content">

      {{-- ======== ONGLET CLIENTS ======== --}}
      <div class="tab-pane fade {{ $tab==='clients' ? 'show active' : '' }}" id="tab-clients" role="tabpanel">
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

                <div class="col-md-2 text-end">
                    <a href="{{ route('clients.create') }}" class="btn btn-primary w-100">Nouveau client</a>
                </div>


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
            <div class="col-md-3">
                <form action="{{ route('clients.import') }}" method="POST" enctype="multipart/form-data"
                    class="d-flex gap-2">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                    <button class="btn btn-primary w-100">Importer</button>
                </form>
            </div>

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
                            <th>Client</th>
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
                        @forelse($clients as $c)
                            <tr>
                                <td>{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</td>
                                <td class="text-truncate" style="max-width:220px;">
                                    @if ($c->type === 'professionnel')
                                        {{ $c->raison_sociale ?? 'Entreprise' }}
                                    @else
                                        {{ trim(($c->prenom ?? '') . ' ' . ($c->nom ?? 'Client')) }}
                                    @endif
                                </td>
                                <td>{{ ucfirst($c->type) }}</td>
                                <td class="text-nowrap">{{ $c->telephone }}</td>
                                <td class="text-nowrap">{{ $c->email }}</td>
                                <td class="text-truncate" style="max-width:160px;">
                                    {{ $c->localisation ?? $c->adresse_ligne1 }}</td>
                                <td class="text-nowrap">{{ $c->numero_ligne }}</td>
                                <td class="text-nowrap">{{ $c->numero_point_focal }}</td>
                                <td class="text-nowrap">{{ optional($c->date_paiement)->format('d/m/Y') }}</td>
                                <td class="text-nowrap">{{ optional($c->date_affectation)->format('d/m/Y') }}</td>
                                <td class="text-end">

                                    {{-- Ouvrir / Éditer --}}
                                    <a class="btn btn-sm btn-outline-secondary"
                                        href="{{ route('clients.show', $c) }}">Ouvrir</a>
                                    <a class="btn btn-sm btn-outline-primary"
                                        href="{{ route('clients.edit', $c) }}">Éditer</a>



                                    {{-- Si un dossier existe déjà pour ce client, actions rapides --}}
                                    @if ($c->dossier)
                                        {{-- Affectation équipe/technicien --}}
                                        <form action="{{ route('dossiers.assign', $c->dossier) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <select name="technicien_id"
                                                class="form-select form-select-sm d-inline w-auto"
                                                onchange="this.form.submit()">
                                                <option value="">-- Affecter --</option>
                                                @foreach (\App\Models\User::role('technicien')->get() as $tech)
                                                    <option value="{{ $tech->id }}" @selected($c->dossier->technicien_id == $tech->id)>
                                                        {{ $tech->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>

                                        {{-- Qualification rapide --}}
                                        <form action="{{ route('dossiers.status', $c->dossier) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <select name="statut" class="form-select form-select-sm d-inline w-auto"
                                                onchange="this.form.submit()">
                                                @foreach (\App\Enums\StatutDossier::labels() as $value => $label)
                                                    <option value="{{ $value }}" @selected($c->dossier->statut->value == $value)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
                                </td>



                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">Aucun client</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                {{ $clients->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
      </div>

      {{-- ======== ONGLET DOSSIERS ======== --}}
      <div class="tab-pane fade {{ $tab==='dossiers' ? 'show active' : '' }}" id="tab-dossiers" role="tabpanel">
        <div class="card">
          <div class="card-body">

            {{-- FILTRES DOSSIERS --}}
            <form method="GET" class="row g-2 mb-3">
              <input type="hidden" name="tab" value="dossiers">

              <div class="col-md-3">
                <select name="statut" class="form-control" onchange="this.form.submit()">
                  <option value="">-- Statut --</option>
                  @foreach($statuts as $value => $label)
                    <option value="{{ $value }}" @selected(request('statut')===$value)>{{ $label }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3">
                <select name="type_service" class="form-control" onchange="this.form.submit()">
                  <option value="">-- Type de service --</option>
                  <option value="residentiel"  @selected(request('type_service')==='residentiel')>Résidentiel</option>
                  <option value="professionnel" @selected(request('type_service')==='professionnel')>Professionnel</option>
                </select>
              </div>

              <div class="col-md-2">
                <select name="per_page_d" class="form-control">
                  @foreach ([10,15,50,100] as $n)
                    <option value="{{ $n }}" @selected(request('per_page_d',15)==$n)>{{ $n }}/page</option>
                  @endforeach
                </select>
              </div>

              <div class="col">
                @can('dossiers.create')
                  <a href="{{ route('clients.create') }}" class="btn btn-primary float-end">Nouveau dossier</a>
                @endcan
              </div>
            </form>

            {{-- TABLEAU DOSSIERS --}}
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Réf.</th>
                    <th>Dossier Abonné</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Technicien</th>
                    <th>Planifiée</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($dossiers as $d)
                    <tr>
                      <td>{{ $d->reference }}</td>
                      <td>{{ $d->client->displayName ?? ($d->client?->raison_sociale ?? trim(($d->client?->prenom ?? '').' '.($d->client?->nom ?? ''))) }}</td>
                      <td>{{ ucfirst($d->type_service) }}</td>
                      <td>
                        <span class="badge bg-info">
                          {{ \App\Enums\StatutDossier::labels()[$d->statut->value] ?? $d->statut->value }}
                        </span>
                      </td>
                      <td>{{ $d->technicien?->name ?? '-' }}</td>
                      <td>{{ optional($d->date_planifiee)->format('d/m/Y H:i') }}</td>
                      <td>
                        <a href="{{ route('clients.show',$d) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="7" class="text-center text-muted">Aucun dossier</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="mt-2">
              {{ $dossiers->appends(request()->except('page') + ['tab'=>'dossiers'])->links('pagination::bootstrap-5') }}
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@stop
