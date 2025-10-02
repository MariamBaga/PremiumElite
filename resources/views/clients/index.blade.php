@extends('adminlte::page')

@section('title', 'FTTH')

@section('content_header')
    <h1>Dossier FTTH</h1>
@stop
@push('css')
    <style>
        .scroll-top-wrapper {
            overflow-x: scroll !important;
            overflow-y: hidden;
            height: 16px;
            background: #f8f9fa;
            border-bottom: 1px solid #ccc;
            scrollbar-color: #007bff #e9ecef;
            scrollbar-width: thin;
        }

        .scroll-top-wrapper::-webkit-scrollbar {
            height: 10px;
        }

        .scroll-top-wrapper::-webkit-scrollbar-thumb {
            background: #007bff;
            border-radius: 4px;
        }

        .scroll-top-wrapper::-webkit-scrollbar-track {
            background: #e9ecef;
        }


        .highlight-yellow {
            background-color: #fff3cd !important;
            /* jaune clair */
        }

        .highlight-orange {
            background-color: #ffe0b2 !important;
            /* orange clair */
        }

        .highlight-red {
            background-color: #f8d7da !important;
            /* rouge clair */
        }
    </style>
@endpush


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
                    <input name="localisation" class="form-control" placeholder="Localisation (client)"
                        value="{{ request('localisation') }}">
                </div>

                {{-- 🆕 Filtres dossier --}}
                <div class="col-md-2">
                    <select name="service_acces" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Service --</option>
                        <option value="FTTH" @selected(request('service_acces') === 'FTTH')>FTTH</option>
                        <option value="Cuivre" @selected(request('service_acces') === 'Cuivre')>Cuivre</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="categorie" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Catégorie --</option>
                        <option value="B2C" @selected(request('categorie') === 'B2C')>B2C</option>
                        <option value="B2B" @selected(request('categorie') === 'B2B')>B2B</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="active" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Active --</option>
                        <option value="1" @selected(request('active') === '1')>Oui</option>
                        <option value="0" @selected(request('active') === '0')>Non</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <input name="localite" class="form-control" placeholder="Localité (dossier)"
                        value="{{ request('localite') }}">
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <input type="date" name="date_paiement_from" class="form-control"
                            value="{{ request('date_paiement_from') }}">
                        <input type="date" name="date_paiement_to" class="form-control"
                            value="{{ request('date_paiement_to') }}">
                    </div>
                    <small class="text-muted">Période paiement (client)</small>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <input type="date" name="date_affect_from" class="form-control"
                            value="{{ request('date_affect_from') }}">
                        <input type="date" name="date_affect_to" class="form-control"
                            value="{{ request('date_affect_to') }}">
                    </div>
                    <small class="text-muted">Période affectation (client)</small>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <input type="date" name="date_recep_from" class="form-control"
                            value="{{ request('date_recep_from') }}">
                        <input type="date" name="date_recep_to" class="form-control"
                            value="{{ request('date_recep_to') }}">
                    </div>
                    <small class="text-muted">Période réception racc. (dossier)</small>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <input type="date" name="date_fin_from" class="form-control"
                            value="{{ request('date_fin_from') }}">
                        <input type="date" name="date_fin_to" class="form-control" value="{{ request('date_fin_to') }}">
                    </div>
                    <small class="text-muted">Période fin travaux (dossier)</small>
                </div>

                <div class="col-md-2">
                    <select name="statut" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Statut --</option>
                        @foreach (\App\Enums\StatutDossier::labels() as $value => $label)
                            <option value="{{ $value }}"
                                {{ request()->get('statut') == $value ? 'selected' : '' }}>{{ $label }}</option>
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

            {{-- Import --}}
            @can('clients.create')
                <div class="mb-3">
                    <form action="{{ route('clients.import') }}" method="POST" enctype="multipart/form-data"
                        class="d-flex gap-2">
                        @csrf
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                        <button class="btn btn-primary">Importer</button>
                    </form>
                </div>
            @endcan

            @if (session('success'))
                <div class="alert alert-success mt-3">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger mt-3">{{ session('error') }}</div>
            @endif

            {{-- TABLEAU DataTables (nouvelle structure) --}}
            {{-- Wrapper pour scrollbar horizontale en haut --}}
            @can('clients.delete')
                <div style="text-align: center; margin: 1rem 0;">
                    <form action="{{ route('clients.purgeAll') }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            style="background-color: #b71c1c; border-color: #b71c1c; color: #fff; padding: 0.35rem 0.75rem; font-size: 0.875rem; border-radius: 0.25rem; cursor: pointer;"
                            onclick="return confirm('⚠️ Cette action va SUPPRIMER TOUS les clients et leurs dossiers. Êtes-vous sûr ?');">
                            Supprimer TOUS (Clients + Dossiers)
                        </button>
                    </form>
                </div>
            @endcan

            <form action="{{ route('clients.delete-multiple') }}" method="POST" id="bulkDeleteForm">
                @csrf
                @method('DELETE')
                @can('clients.delete')
                    <button type="submit" class="btn btn-danger mb-2"
                        onclick="return confirm('Supprimer les clients sélectionnés avec leurs dossiers ?')">
                        Supprimer sélection
                    </button>
                @endcan
                <div class="scroll-top-wrapper mb-1" style="overflow-x:auto; overflow-y:hidden; height:20px;"></div>

                <div class="table-responsive" style="max-height:600px; overflow-y:auto; overflow-x:hidden;">
                    <table id="dossiersTable" class="table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                @can('clients.delete')
                                    <th><input type="checkbox" id="checkAll"></th>
                                @endcan
                                <th>#</th>
                                <th>CLIENT</th>
                                <th>LIGNE</th>
                                <th>Contact</th>
                                <th>Service</th>
                                <th>LOCALITE</th>
                                <th>Catégorie</th>
                                <th>Réception</th>
                                <th>Fin Travaux</th>
                                <th>Port</th>
                                <th>PBO / Linéaire</th>
                                <th>Poteaux</th>
                                <th>Armements</th>
                                <th>Statut</th>
                                <th>Reporting J+1</th>
                                <th>ACTIVE</th>
                                <th>Observation</th>
                                <th>Pilote</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $i => $c)
                                @php
                                    $d = $c->lastDossier;

                                    // On calcule le nombre de jours écoulés depuis la date de réception
                                    $jours =
                                        $d && $d->date_reception_raccordement
                                            ? \Carbon\Carbon::parse($d->date_reception_raccordement)->diffInDays(now())
                                            : 0;

                                    // Couleur selon le nombre de jours
                                    $highlightClass = '';
                                    if ($jours > 3) {
                                        $highlightClass = 'highlight-red';
                                    } elseif ($jours > 2) {
                                        $highlightClass = 'highlight-orange';
                                    } elseif ($jours > 1) {
                                        $highlightClass = 'highlight-yellow';
                                    }
                                @endphp

                                <tr class="{{ $highlightClass }}">
                                    @can('clients.delete')
                                        <td>
                                            <input type="checkbox" name="clients[]" value="{{ $c->id }}"
                                                class="client-checkbox">
                                        </td>
                                    @endcan
                                    <td>{{ $i + $clients->firstItem() }}</td>
                                    <td class="text-truncate" style="max-width:220px;">{{ $c->displayName }}</td>
                                    <td class="text-nowrap">{{ $d?->ligne ?? $c->numero_ligne }}</td>
                                    <td class="text-nowrap">{{ $d?->contact ?? $c->telephone }}</td>
                                    <td class="text-nowrap">{{ $d?->service_acces }}</td>
                                    <td class="text-nowrap">{{ $d?->localite }}</td>
                                    <td class="text-nowrap">{{ $d?->categorie }}</td>
                                    <td class="text-nowrap">
                                        {{ optional($d?->date_reception_raccordement)->format('d/m/Y') }}
                                    </td>
                                    <td class="text-nowrap">{{ optional($d?->date_fin_travaux)->format('d/m/Y') }}</td>
                                    <td class="text-nowrap">{{ $d?->port }}</td>
                                    <td class="text-nowrap">{{ $d?->pbo_lineaire_utilise }}</td>
                                    <td class="text-nowrap">{{ $d?->nb_poteaux_implantes }}</td>
                                    <td class="text-nowrap">{{ $d?->nb_armements_poteaux }}</td>
                                    <td class="text-nowrap">
                                        {{ $d?->statut_label ?? (\App\Enums\StatutDossier::labels()[$d?->statut?->value ?? 'en_appel'] ?? $d?->statut?->value) }}
                                    </td>
                                    <td class="text-nowrap">{{ $d?->taux_reporting_j1 }}</td>
                                    <td class="text-nowrap">
                                        @if ($d?->is_active)
                                            <span class="badge bg-success">Oui</span>
                                        @else
                                            <span class="badge bg-secondary">Non</span>
                                        @endif
                                    </td>
                                    <td class="text-truncate" style="max-width:220px;">{{ $d?->observation }}</td>
                                    <td class="text-nowrap">{{ $d?->pilote_raccordement }}</td>
                                    <td class="text-end">
                                        <div class="d-flex flex-wrap gap-1 justify-content-end align-items-center">
                                            <a class="btn btn-sm btn-outline-secondary"
                                                href="{{ route('clients.show', $c) }}">Ouvrir</a>
                                            @can('clients.edit')
                                                <a class="btn btn-sm btn-outline-primary"
                                                    href="{{ route('clients.edit', $c) }}">Éditer</a>
                                            @endcan

                                            @php
                                                $dossier =
                                                    $d ?? new \App\Models\DossierRaccordement(['client_id' => $c->id]);
                                            @endphp

                                            @can('dossiers.assign')
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
                                                            <option value="{{ $value }}" @selected(($dossier->statut?->value ?? 'en_appel') === $value)>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button class="btn btn-sm btn-primary">OK</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </form>


            @include('dossiers.partials.rapport_modal')
            @include('dossiers.partials.nouveau_rdv_modal')

            @include('dossiers.partials.zone_depourvue_modal')
            @include('dossiers.partials.injoignables')
            @include('dossiers.partials.pob_sature_modal')
            @include('dossiers.partials.indisponible_modal')


            @include('dossiers.partials.realise_modal')
        </div>
        <div class="mt-3">
            {{ $clients->links('pagination::bootstrap-5') }}
        </div>


    </div>
@stop

@push('css')
    {{-- optionnel: compacter les cellules --}}
    <style>
        .dataTables_wrapper .dataTables_length select {
            padding-right: 24px;
        }

        .table td,
        .table th {
            white-space: nowrap;
        }
    </style>
@endpush

@push('js')
    {{-- DataTables est déjà packagé avec AdminLTE. Si besoin, assure-toi que ces plugins sont enable dans config/adminlte.php --}}
    <script>
        const table = $('#dossiersTable').DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            responsive: true,
            autoWidth: false
        });

        // ✅ Gestion du « tout sélectionner »
        $(document).on('change', '#checkAll', function() {
            const checked = this.checked;
            $('.client-checkbox').prop('checked', checked);
        });

        // ✅ Si une case individuelle change, mettre à jour l’entête
        $(document).on('change', '.client-checkbox', function() {
            const all = $('.client-checkbox').length;
            const checked = $('.client-checkbox:checked').length;
            $('#checkAll').prop('checked', all === checked);
        });

        // ✅ Empêcher la suppression si rien n’est coché
        $('#bulkDeleteForm').on('submit', function(e) {
            if (!$('.client-checkbox:checked').length) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins un client avant de supprimer.');
            }
        });



        // Synchroniser scroll horizontal en haut
        const topWrapper = document.querySelector('.scroll-top-wrapper');
        const tableWrapper = document.querySelector('.table-responsive');

        // Crée un clone invisible du tableau pour scrollbar top
        const cloneTable = tableWrapper.querySelector('table').cloneNode(true);
        cloneTable.style.visibility = 'hidden';
        cloneTable.style.pointerEvents = 'none';
        topWrapper.appendChild(cloneTable);

        topWrapper.addEventListener('scroll', () => {
            tableWrapper.scrollLeft = topWrapper.scrollLeft;
        });

        tableWrapper.addEventListener('scroll', () => {
            topWrapper.scrollLeft = tableWrapper.scrollLeft;
        });

        document.querySelectorAll('.statut-select').forEach(select => {
            select.dataset.oldValue = select.value;

            select.addEventListener('change', function() {
                const dossierId = this.dataset.dossierId;
                let modal;

                if (this.value === 'nouveau_rendez_vous') {
                    document.getElementById('nouveauRdvDossierId').value = dossierId;
                    modal = new bootstrap.Modal(document.getElementById('nouveauRdvModal'));
                    modal.show();
                    this.value = this.dataset.oldValue; // on garde pour RDV

                } else if (this.value === 'active') {
                    document.getElementById('rapportDossierId').value = dossierId;
                    modal = new bootstrap.Modal(document.getElementById('rapportModal'));
                    modal.show();
                    // ne pas remettre l'ancienne valeur

                    // ==== AJOUTS ====
                } else if (this.value === 'injoignable') {
                    document.getElementById('injoignableDossierId').value = dossierId;
                    modal = new bootstrap.Modal(document.getElementById('injoignableModal'));
                    modal.show();
                    this.value = this.dataset.oldValue;

                } else if (this.value === 'pbo_sature') {
                    document.getElementById('pboSatureDossierId').value = dossierId;
                    modal = new bootstrap.Modal(document.getElementById('pboSatureModal'));
                    modal.show();
                    this.value = this.dataset.oldValue;

                } else if (this.value === 'realise') {
                    document.getElementById('realiseDossierId').value = dossierId;
                    modal = new bootstrap.Modal(document.getElementById('realiseModal'));
                    modal.show();
                    this.value = this.dataset.oldValue;
                } else if (this.value === 'zone_depourvue') {
                    document.getElementById('zoneDepourvueDossierId').value = dossierId;
                    modal = new bootstrap.Modal(document.getElementById('zoneDepourvueModal'));
                    modal.show();
                    this.value = this.dataset.oldValue;
                } else if (this.value === 'indisponible') {
                    document.getElementById('indisponibleDossierId').value = dossierId;
                    const modal = new bootstrap.Modal(document.getElementById('indisponibleModal'));
                    modal.show();
                    this.value = this.dataset.oldValue; // on garde l'ancien statut visuellement
                } else {
                    this.dataset.oldValue = this.value;
                }
            });
        });




        // ===== Déjà existant =====
        document.getElementById('rapportForm').addEventListener('submit', function() {
            const dossierId = document.getElementById('rapportDossierId').value;
            const select = document.querySelector(`.statut-select[data-dossier-id="${dossierId}"]`);
            if (select) {
                select.value = 'active'; // Mettre à jour visuellement le select
                select.dataset.oldValue = 'active';
            }
        });
    </script>
@endpush
