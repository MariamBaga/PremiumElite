@extends('adminlte::page')

@section('title', 'FTTH — Fiche')

@section('content_header')
  @php
    // Normalisation : on peut arriver avec $client, $dossier, ou les deux.
    if (!isset($client) && isset($dossier)) { $client = $dossier->client; }
    $tab = request('tab', isset($dossier) ? 'dossier' : 'client');
  @endphp
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h1 class="mb-0">
        @if($tab==='client')
          Client #{{ $client->id }}
          <small class="text-muted">— {{ $client->displayName }}</small>
        @else
          Dossier {{ $dossier->reference }}
          <small class="text-muted">— {{ $dossier->client->displayName }}</small>
        @endif
      </h1>
      <div class="mt-2">
        <ul class="nav nav-pills">
          <li class="nav-item">
            <a class="nav-link {{ $tab==='client' ? 'active' : '' }}"
               href="{{ request()->fullUrlWithQuery(['tab'=>'client']) }}">
               Client
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ $tab==='dossier' ? 'active' : '' }}"
               href="{{ request()->fullUrlWithQuery(['tab'=>'dossier']) }}"
               @if(!isset($dossier)) onclick="event.preventDefault();" title="Aucun dossier en contexte" class="nav-link disabled" @endif>
               Dossier
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div class="d-flex gap-2">
      @if($tab==='client')
        <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary">Éditer le client</a>
        <form method="POST" action="{{ route('clients.destroy', $client) }}"
              onsubmit="return confirm('Supprimer ce client ?')">
          @csrf @method('DELETE')
          <button class="btn btn-danger">Supprimer</button>
        </form>
        @can('dossiers.create')
          <a href="{{ route('dossiers.create') }}?client_id={{ $client->id }}" class="btn btn-outline-secondary">
            Nouveau dossier
          </a>
        @endcan
      @elseif(isset($dossier))
        <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary">Tous les dossiers</a>
      @endif
    </div>
  </div>
@stop

@section('content')
  @php
    // Pré-chargements utiles pour ne pas dupliquer la logique plus bas
    $dossiersForClient = isset($client)
      ? $client->dossiers()->latest()->limit(8)->get()
      : collect();
  @endphp

  {{-- ========================= ONGLET CLIENT ========================= --}}
  @if($tab==='client')
  <div class="row">
    {{-- Colonne principale --}}
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-header">Informations</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-3">Type</dt>
            <dd class="col-sm-9">{{ ucfirst($client->type) }}</dd>

            @if ($client->type === 'professionnel')
              <dt class="col-sm-3">Raison sociale</dt>
              <dd class="col-sm-9">{{ $client->raison_sociale ?? '-' }}</dd>
            @else
              <dt class="col-sm-3">Nom</dt>
              <dd class="col-sm-9">{{ $client->nom ?? '-' }}</dd>
              <dt class="col-sm-3">Prénom</dt>
              <dd class="col-sm-9">{{ $client->prenom ?? '-' }}</dd>
            @endif


            <dt class="col-sm-3">Adresse</dt>
            <dd class="col-sm-9">
              {{ $client->adresse_ligne1 }}
              @if ($client->adresse_ligne2) — {{ $client->adresse_ligne2 }} @endif
            </dd>

            <dt class="col-sm-3">Ville / Zone</dt>
            <dd class="col-sm-9">{{ $client->ville ?? '-' }} / {{ $client->zone ?? '-' }}</dd>

            <dt class="col-sm-3">Localisation</dt>
            <dd class="col-sm-9">{{ $client->localisation ?? '-' }}</dd>

            <dt class="col-sm-3">N° de ligne</dt>
            <dd class="col-sm-9 text-nowrap">{{ $client->numero_ligne ?? '-' }}</dd>

            <dt class="col-sm-3">Point focal</dt>
            <dd class="col-sm-9 text-nowrap">{{ $client->numero_point_focal ?? '-' }}</dd>

            <dt class="col-sm-3">Date de paiement</dt>
            <dd class="col-sm-9 text-nowrap">{{ optional($client->date_paiement)->format('d/m/Y') ?? '-' }}</dd>

            <dt class="col-sm-3">Date d’affectation</dt>
            <dd class="col-sm-9 text-nowrap">{{ optional($client->date_affectation)->format('d/m/Y') ?? '-' }}</dd>

            <dt class="col-sm-3">Coordonnées</dt>
            <dd class="col-sm-9">
              @if (!is_null($client->latitude) && !is_null($client->longitude))
                {{ $client->latitude }}, {{ $client->longitude }}
                <a class="btn btn-xs btn-outline-secondary ms-2" target="_blank"
                  href="https://www.google.com/maps?q={{ $client->latitude }},{{ $client->longitude }}">
                  Voir sur la carte
                </a>
              @else
                -
              @endif
            </dd>
          </dl>
        </div>
      </div>

      {{-- Dossiers liés --}}
      @can('dossiers.view')
      <div class="card mb-3">
        <div class="card-header">
          Dossiers associés
          <span class="badge bg-primary ms-1">{{ $client->dossiers_count ?? $client->dossiers()->count() }}</span>
        </div>
        <div class="card-body p-0">
          @if ($dossiersForClient->isEmpty())
            <div class="p-3 text-muted">Aucun dossier pour ce Abonné.</div>
          @else
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>Référence</th>
                    <th>Statut</th>
                    <th class="text-nowrap">Type</th>
                    <th class="text-nowrap">Planifiée</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($dossiersForClient as $d)
                    <tr>
                      <td>{{ $d->reference }}</td>
                      <td>{{ \Illuminate\Support\Str::headline($d->statut?->value ?? $d->statut) }}</td>
                      <td class="text-nowrap">{{ ucfirst($d->type_service) }}</td>
                      <td class="text-nowrap">{{ optional($d->date_planifiee)->format('d/m/Y H:i') }}</td>
                      <td class="text-end">
                        @can('dossiers.view')
                          <a href="{{ route('ftth.fiche', ['dossier' => $d->id, 'tab' => 'dossier']) }}"
                             class="btn btn-sm btn-outline-secondary">
                             Ouvrir
                          </a>
                        @endcan
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
        <div class="card-footer text-end">
          @can('dossiers.view')
          <a href="{{ route('dossiers.index', ['search' => $client->id]) }}"
             class="btn btn-outline-primary btn-sm">
            Voir tous les dossiers
          </a>
          @endcan
        </div>
      </div>
      @endcan
    </div>

    {{-- Colonne actions --}}
    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header">Actions</div>
        <div class="card-body d-flex flex-column gap-2">
          <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary">Éditer</a>
          <form method="POST" action="{{ route('clients.destroy', $client) }}"
                onsubmit="return confirm('Supprimer ce client ?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger">Supprimer</button>
          </form>

          @can('dossiers.create')
          <a href="{{ route('dossiers.create') }}?client_id={{ $client->id }}"
             class="btn btn-outline-primary">
            Créer un dossier de raccordement
          </a>
          @endcan


          <a href="mailto:{{ $client->email }}" class="btn btn-outline-secondary"
             @disabled(empty($client->email))>
            Envoyer un email
          </a>
        </div>
      </div>
    </div>
  </div>
  @endif

  {{-- ========================= ONGLET DOSSIER ========================= --}}
  @if($tab==='dossier' && isset($dossier))
  <div class="row">
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-header">Informations</div>
        <div class="card-body">
          <dl class="row">
            <dt class="col-sm-3">Abonner</dt>
            <dd class="col-sm-9">
              {{ $dossier->client->displayName }} ({{ $dossier->client->telephone }})
              <a class="btn btn-xs btn-outline-secondary ms-2"
                 href="{{ route('ftth.fiche', ['client' => $dossier->client_id, 'tab' => 'client']) }}">
                 Ouvrir la fiche d'abonné
              </a>
            </dd>

            <dt class="col-sm-3">Statut</dt>
            <dd class="col-sm-9">
              <span class="badge bg-info">
                {{ \App\Enums\StatutDossier::labels()[$dossier->statut->value] ?? $dossier->statut->value }}
              </span>
            </dd>

            <dt class="col-sm-3">Technicien</dt>
            <dd class="col-sm-9">{{ $dossier->technicien?->name ?? '-' }}</dd>

            <dt class="col-sm-3">Planifiée</dt>
            <dd class="col-sm-9">{{ optional($dossier->date_planifiee)->format('d/m/Y H:i') ?? '-' }}</dd>

            <dt class="col-sm-3">Réalisée</dt>
            <dd class="col-sm-9">{{ optional($dossier->date_realisation)->format('d/m/Y H:i') ?? '-' }}</dd>

            <dt class="col-sm-3">Notes</dt>
            <dd class="col-sm-9">{{ $dossier->description ?? '-' }}</dd>
          </dl>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">Historique des statuts</div>
        <div class="card-body table-responsive">
          <table class="table">
            <thead><tr><th>Date</th><th>De</th><th>À</th><th>Par</th><th>Commentaire</th></tr></thead>
            <tbody>
            @forelse($dossier->statuts as $h)
              <tr>
                <td>{{ $h->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ \App\Enums\StatutDossier::labels()[$h->ancien_statut] ?? '-' }}</td>
                <td>{{ \App\Enums\StatutDossier::labels()[$h->nouveau_statut] ?? $h->nouveau_statut }}</td>
                <td>{{ $h->user?->name ?? '-' }}</td>
                <td>{{ $h->commentaire ?? '-' }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-muted">Aucun historique.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">Tentatives de contact</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dossiers.tentatives.store',$dossier) }}" class="row g-2 mb-3">
            @csrf
            <div class="col-md-3"><input name="methode" class="form-control" placeholder="appel/sms/email" required></div>
            <div class="col-md-3"><input name="resultat" class="form-control" placeholder="joignable..." required></div>
            <div class="col-md-4"><input name="notes" class="form-control" placeholder="notes"></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Ajouter</button></div>
          </form>
          <ul class="list-group">
            @forelse($dossier->tentatives->sortByDesc('effectuee_le') as $t)
              <li class="list-group-item d-flex justify-content-between">
                <span><strong>{{ $t->methode }}</strong> — {{ $t->resultat }} ({{ $t->effectuee_le->format('d/m/Y H:i') }})</span>
                <span class="text-muted">{{ $t->user?->name }}</span>
              </li>
            @empty
              <li class="list-group-item text-muted">Aucune tentative.</li>
            @endforelse
          </ul>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">Interventions</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dossiers.interventions.store',$dossier) }}" class="row g-2 mb-3">
            @csrf
            <div class="col-md-3"><input type="datetime-local" name="debut" class="form-control"></div>
            <div class="col-md-3"><input type="datetime-local" name="fin" class="form-control"></div>
            <div class="col-md-3">
              <select name="etat" class="form-control">
                <option value="en_cours">En cours</option>
                <option value="realisee">Réalisée</option>
                <option value="suspendue">Suspendue</option>
              </select>
            </div>
            <div class="col-md-3"><button class="btn btn-outline-primary w-100">Ajouter</button></div>
            <div class="col-12 mt-2">
              <textarea name="observations" class="form-control" placeholder="Observations" rows="2"></textarea>
            </div>
          </form>
          <ul class="list-group">
            @forelse($dossier->interventions()->latest()->get() as $i)
              <li class="list-group-item">
                <strong>{{ ucfirst($i->etat) }}</strong> —
                {{ $i->debut?->format('d/m/Y H:i') }} → {{ $i->fin?->format('d/m/Y H:i') }}
                <span class="float-end">{{ $i->technicien?->name }}</span>
                <div class="text-muted">{{ $i->observations }}</div>
              </li>
            @empty
              <li class="list-group-item text-muted">Aucune intervention.</li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    {{-- Colonne latérale (assignations / statut / rapport) --}}
    <div class="col-lg-4">
      @can('dossiers.assign')
      <div class="card mb-3">
        <div class="card-header">Affectation à une équipe</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dossiers.assign', $dossier) }}" class="mb-3">
            @csrf
            <div class="mb-3">
              <label class="form-label">Équipe assignée</label>
              <select name="assigned_team_id" class="form-control">
                <option value="">-- Aucune équipe --</option>
                @foreach(\App\Models\Team::all() as $team)
                  <option value="{{ $team->id }}" @selected($dossier->assigned_team_id == $team->id)>{{ $team->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label>Date planifiée</label>
              <input type="datetime-local" name="date_planifiee" class="form-control"
                     value="{{ optional($dossier->date_planifiee)->format('Y-m-d\TH:i') }}">
            </div>
            <button class="btn btn-primary w-100">Assigner</button>
          </form>
          @error('assigned_team_id') <div class="text-danger">{{ $message }}</div> @enderror

          @if($dossier->team)
            <div class="mt-2 text-muted">Équipe actuelle : <strong>{{ $dossier->team->name }}</strong></div>
          @endif
        </div>
      </div>
      @endcan

      @can('dossiers.update')
      <div class="card mb-3">
        <div class="card-header">Rapport / Synthèse intervention</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dossiers.rapport.save',$dossier) }}">
            @csrf
            <div class="row g-2">
              <div class="col-md-12">
                <label>État</label>
                <select name="etat" class="form-control" required>
                  <option value="pon">En cours (PON)</option>
                  <option value="contraintes">Contrainte</option>
                  <option value="reporte">Reporté</option>
                  <option value="realise">Réalisé</option>
                </select>
              </div>

              <div class="col-md-4"><label>MSAN</label><input name="msan" class="form-control"></div>
              <div class="col-md-4"><label>FAT</label><input name="fat" class="form-control"></div>
              <div class="col-md-4"><label>Port</label><input name="port" class="form-control"></div>

              <div class="col-md-4"><label>Port disponible</label><input name="port_disponible" class="form-control"></div>
              <div class="col-md-4"><label>Type de câble</label><input name="type_cable" class="form-control" value="CPC"></div>
              <div class="col-md-4"><label>Linéaire (m)</label><input type="number" name="lineaire_m" class="form-control"></div>

              <div class="col-md-6"><label>Puissance FAT (dBm)</label><input type="number" step="0.01" name="puissance_fat_dbm" class="form-control"></div>
              <div class="col-md-6"><label>Puissance PTO (dBm)</label><input type="number" step="0.01" name="puissance_pto_dbm" class="form-control"></div>

              <div class="col-md-6"><label>Date de report</label><input type="datetime-local" name="date_report" class="form-control"></div>
              <div class="col-md-6"><label>Contrainte (si applicable)</label><input name="contrainte" class="form-control" placeholder="Abonner absent, PBO saturé..."></div>

              <div class="col-12">
                <label>Détails du rapport (libre)</label>
                <textarea name="rapport_installation[poteaux]" class="form-control" rows="3"
                          placeholder="Poteaux trav.: 8; Poteaux armés: 7; GPS…"></textarea>
              </div>
            </div>
            <div class="mt-2 text-end">
              <button class="btn btn-primary">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
      @endcan

      @can('updateStatus', $dossier)
      <div class="card">
        <div class="card-header">Mettre à jour le statut</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dossiers.status',$dossier) }}">
            @csrf
            <div class="mb-2">
              <label>Nouveau statut</label>
              <select name="statut" class="form-control" required>
                @foreach(\App\Enums\StatutDossier::labels() as $value=>$label)
                  <option value="{{ $value }}" @selected($dossier->statut->value===$value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-2">
              <label>Commentaire</label>
              <textarea name="commentaire_statut" class="form-control" rows="2" placeholder="Optionnel"></textarea>
            </div>
            <button class="btn btn-primary w-100">Mettre à jour</button>
          </form>
        </div>
      </div>
      @endcan
    </div>
  </div>
  @endif
@stop
