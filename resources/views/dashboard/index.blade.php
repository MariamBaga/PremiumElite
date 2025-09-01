@extends('adminlte::page')

@section('title', 'Tableau de bord')
@section('plugins.Chartjs', true)

@section('content_header')
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="m-0">Tableau de bord FTTH</h1>
    <form method="GET" class="d-flex" style="gap:.5rem;">
      <input type="date" name="date_from" class="form-control" value="{{ $from }}">
      <input type="date" name="date_to" class="form-control" value="{{ $to }}">
      <button class="btn btn-outline-primary">Appliquer</button>
    </form>
  </div>
@stop

@section('content')

  {{-- =======================
       LIGNE 1 : KPI globaux
       ======================= --}}
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-primary">
        <div class="inner">
          <h3>{{ $totalDossiers }}</h3>
          <p>Dossiers total</p>
        </div>
        <div class="icon"><i class="fas fa-layer-group"></i></div>
        <a href="{{ route('dossiers.index') }}" class="small-box-footer">
          Voir les dossiers <i class="fas fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ $ouverts }}</h3>
          <p>Dossiers ouverts</p>
        </div>
        <div class="icon"><i class="fas fa-folder-open"></i></div>
        <a href="{{ route('dossiers.index', ['statut'=>'en_appel']) }}" class="small-box-footer">
          Filtrer <i class="fas fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $realises }}</h3>
          <p>Raccordements réalisés</p>
        </div>
        <div class="icon"><i class="fas fa-check-circle"></i></div>
        <span class="small-box-footer">Taux de réussite: {{ $tauxReussite }}%</span>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3>{{ $pboSature }}</h3>
          <p>Cas PBO saturé</p>
        </div>
        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        <a href="{{ route('dossiers.index', ['statut'=>'pbo_sature']) }}" class="small-box-footer">
          Voir <i class="fas fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
  </div>

  {{-- =======================
       LIGNE 2 : Clients + KPI équipes (7 jours)
       ======================= --}}
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ $totalClients }}</h3>
          <p>Clients total</p>
        </div>
        <div class="icon"><i class="fas fa-users"></i></div>
        <a href="{{ route('clients.index') }}" class="small-box-footer">
          Voir les clients <i class="fas fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>

    @php $teamsKpi = $teamsKpi ?? collect(); @endphp
    @foreach($teamsKpi as $tk)
      <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
          <div class="inner">
            <h3>{{ $tk['done_last7'] ?? 0 }}</h3>
            <p>{{ $tk['team_name'] ?? 'Équipe' }} — réalisés (7 j.)</p>
          </div>
          <div class="icon"><i class="fas fa-user-cog"></i></div>
          <a href="{{ route('teams.show',$tk['team_id']) }}" class="small-box-footer">
            Voir l’équipe <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
    @endforeach
  </div>

  {{-- =======================
       LIGNE 3 : Volumes par jour + Répartition statuts
       ======================= --}}
  <div class="row">
    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-header">Volumes par jour</div>
        <div class="card-body">
        <div style="height:300px;">
  <canvas id="chartVolumes"></canvas>
</div>

          <div class="mt-2 text-muted">
            Délai moyen (création → réalisation) : <strong>{{ $avgDelayDays }} jours</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header">Répartition par statut</div>
        <div class="card-body">
        <div style="height:300px;">
  <canvas id="chartStatuts"></canvas>
</div>

        </div>
      </div>
    </div>
  </div>

  {{-- =======================
       LIGNE 4 : Top zones + Types de service
       ======================= --}}
  <div class="row">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">Top zones (dossiers)</div>
        <div class="card-body">
        <div style="height:300px;">
          <canvas id="chartZones" ></canvas>
          </div>
        </div>
      </div>
    </div>

    @php $topTeams = $topTeams ?? collect(); @endphp
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Répartition par type de service</span>
          @if($topTeams->isNotEmpty())
            <span class="text-muted small">+ Top équipes</span>
          @endif
        </div>
        <div class="card-body">
        <div style="height:300px;">
          <canvas id="chartTypes" ></canvas>
            </div>
          @if($topTeams->isNotEmpty())
            <hr>
            <div style="height:300px;">
            <canvas id="chartTopTeams"></canvas>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- =======================
       LIGNE 5 : Dossiers par équipe (7 j)
       ======================= --}}
  @php $teamDaily = $teamDaily ?? collect(); @endphp
  @if($teamDaily->isNotEmpty())
    <div class="card">
      <div class="card-header">Dossiers réalisés par équipe (7 derniers jours)</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Équipe</th><th>Date</th><th class="text-end">Réalisés</th></tr></thead>
            <tbody>
              @foreach($teamDaily as $row)
                <tr>
                  <td>{{ $row['team'] }}</td>
                  <td class="text-nowrap">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                  <td class="text-end">{{ $row['n'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endif

  {{-- =======================
       LIGNE 6 : Boîte équipe / corbeille (dossiers non finalisés)
       ======================= --}}
  @php $teamInbox = $teamInbox ?? collect(); @endphp
  @if($teamInbox->isNotEmpty())
    <div class="card">
      <div class="card-header">Boîte équipe — dossiers à traiter</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Réf.</th><th>Équipe</th><th>Abonner</th><th>Statut</th>
                <th>Date planifiée</th><th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($teamInbox as $it)
                <tr>
                  <td><a href="{{ route('clients.show',$it['id']) }}">{{ $it['ref'] }}</a></td>
                  <td>{{ $it['team'] }}</td>
                  <td>{{ $it['client'] }}</td>
                  <td><span class="badge bg-warning text-dark">{{ \Illuminate\Support\Str::headline($it['statut']) }}</span></td>
                  <td class="text-nowrap">{{ $it['date'] ? \Illuminate\Support\Carbon::parse($it['date'])->format('d/m/Y H:i') : '—' }}</td>
                  <td class="text-end">
                    @can('teams.view')
                      @if(!empty($it['team_id']))
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('teams.inbox',['team'=>$it['team_id']]) }}">
                          Ouvrir la boîte
                        </a>
                      @endif
                    @endcan
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endif

  {{-- =======================
       LIGNE 7 : Interventions & derniers dossiers
       ======================= --}}
  <div class="row">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">Interventions ({{ $from }} → {{ $to }})</div>
        <div class="card-body">
          <p class="mb-1">Volume : <strong>{{ $intervCount }}</strong></p>
          <p class="mb-0">Durée moyenne : <strong>{{ $intervAvgDuration }} min</strong></p>
          <hr>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead><tr><th>Dossier</th><th>Technicien</th><th>Début</th><th>Fin</th><th>État</th></tr></thead>
              <tbody>
                @foreach($lastInterventions as $i)
                  <tr>
                    <td><a href="{{ route('clients.show',$i->dossier) }}">{{ $i->dossier?->reference }}</a></td>
                    <td>{{ $i->technicien?->name }}</td>
                    <td class="text-nowrap">{{ optional($i->debut)->format('d/m/Y H:i') }}</td>
                    <td class="text-nowrap">{{ optional($i->fin)->format('d/m/Y H:i') }}</td>
                    <td>{{ ucfirst($i->etat) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">Derniers dossiers</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead><tr><th>Réf.</th><th>Client</th><th>Statut</th><th>Type</th><th>Planifiée</th></tr></thead>
              <tbody>
                @foreach($lastDossiers as $d)
                  <tr>
                    <td><a href="{{ route('dossiers.show',$d) }}">{{ $d->reference }}</a></td>
                    <td>{{ $d->client?->displayName }}</td>
                    <td><span class="badge bg-secondary">{{ \Illuminate\Support\Str::headline($d->statut?->value ?? $d->statut) }}</span></td>
                    <td class="text-nowrap">{{ ucfirst($d->type_service) }}</td>
                    <td class="text-nowrap">{{ optional($d->date_planifiee)->format('d/m/Y H:i') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer text-end">
          <a href="{{ route('dossiers.index') }}" class="btn btn-outline-primary btn-sm">Voir tout</a>
        </div>
      </div>
    </div>
  </div>

@stop

@section('js')
<script>
  // === Données ===
  const labels   = @json($labels);
  const created  = @json($created);
  const realised = @json($realised);

  const byStatut = @json($byStatut);
  const byType   = @json($byTypeService);
  const byZone   = @json($byZone);

  // Helpers de création sécurisée (évite erreurs si canvas absent)
  function makeChart(id, cfg){
    const el = document.getElementById(id);
    if(!el) return;
    new Chart(el, cfg);
  }

  // Courbe volumes
  makeChart('chartVolumes', {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: 'Créés',    data: created,  fill: false, tension: .3, spanGaps: true },
        { label: 'Réalisés', data: realised, fill: false, tension: .3, spanGaps: true },
      ]
    },
    options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
  });

  // Donut statuts
  makeChart('chartStatuts', {
    type: 'doughnut',
    data: { labels: Object.keys(byStatut), datasets: [{ data: Object.values(byStatut) }] },
    options: { responsive:true, maintainAspectRatio:false }
  });

  // Bar zones
  makeChart('chartZones', {
    type: 'bar',
    data: {
      labels: byZone.map(z => z.zone ?? '—'),
      datasets: [{ label: 'Dossiers', data: byZone.map(z => z.c) }]
    },
    options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
  });

  // Donut types de service
  makeChart('chartTypes', {
    type: 'doughnut',
    data: { labels: Object.keys(byType), datasets: [{ data: Object.values(byType) }] },
    options: { responsive:true, maintainAspectRatio:false }
  });

  // Bar Top équipes (si présent)
  @php $topTeams = $topTeams ?? collect(); @endphp
  @if($topTeams->isNotEmpty())
  makeChart('chartTopTeams', {
    type: 'bar',
    data: {
      labels: @json($topTeams->pluck('team')),
      datasets: [{ label: 'Réalisés', data: @json($topTeams->pluck('done')) }]
    },
    options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true } } }
  });
  @endif
</script>
@stop
