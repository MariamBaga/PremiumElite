@extends('adminlte::page')

@section('title', 'Tableau de bord')
@section('plugins.Chartjs', true)

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <h1>Tableau de bord FTTH</h1>
    <form method="GET" class="d-flex" style="gap:.5rem;">
      <input type="date" name="date_from" class="form-control" value="{{ $from }}">
      <input type="date" name="date_to" class="form-control" value="{{ $to }}">
      <button class="btn btn-outline-primary">Appliquer</button>
    </form>
  </div>
@stop

@section('content')
<div class="row">
  {{-- KPI cards --}}
  <div class="col-lg-3 col-6">
    <div class="small-box bg-primary">
      <div class="inner">
        <h3>{{ $totalDossiers }}</h3>
        <p>Dossiers total</p>
      </div>
      <div class="icon"><i class="fas fa-layer-group"></i></div>
      <a href="{{ route('dossiers.index') }}" class="small-box-footer">Voir les dossiers <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>

  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ $ouverts }}</h3>
        <p>Dossiers ouverts</p>
      </div>
      <div class="icon"><i class="fas fa-folder-open"></i></div>
      <a href="{{ route('dossiers.index', ['statut'=>'a_traiter']) }}" class="small-box-footer">Filtrer <i class="fas fa-arrow-circle-right"></i></a>
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
      <a href="{{ route('dossiers.index', ['statut'=>'pbo_sature']) }}" class="small-box-footer">Voir <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>

<div class="row">
  {{-- Courbe Créés vs Réalisés --}}
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">Volumes par jour</div>
      <div class="card-body">
        <canvas id="chartVolumes" height="100"></canvas>
        <div class="mt-2 text-muted">
          Délai moyen (création → réalisation) : <strong>{{ $avgDelayDays }} jours</strong>
        </div>
      </div>
    </div>
  </div>

  {{-- Donut par statut --}}
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">Répartition par statut</div>
      <div class="card-body">
        <canvas id="chartStatuts" height="180"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row">
  {{-- Bar par zone --}}
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">Top zones (dossiers)</div>
      <div class="card-body">
        <canvas id="chartZones" height="140"></canvas>
      </div>
    </div>
  </div>

  {{-- Doughnut par type de service --}}
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">Répartition par type de service</div>
      <div class="card-body">
        <canvas id="chartTypes" height="140"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row">
  {{-- Interventions --}}
  <div class="col-lg-6">
    <div class="card">
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
                <td><a href="{{ route('dossiers.show',$i->dossier) }}">{{ $i->dossier?->reference }}</a></td>
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

  {{-- Derniers dossiers --}}
  <div class="col-lg-6">
    <div class="card">
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
  // Données injectées depuis PHP
  const labels   = @json($labels);
  const created  = @json($created);
  const realised = @json($realised);

  const byStatut = @json($byStatut);
  const byType   = @json($byTypeService);
  const byZone   = @json($byZone);

  // Graph: courbe volumes
  new Chart(document.getElementById('chartVolumes'), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        { label: 'Créés',    data: created,  fill: false, tension: .3 },
        { label: 'Réalisés', data: realised, fill: false, tension: .3 },
      ]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });

  // Donut statuts
  new Chart(document.getElementById('chartStatuts'), {
    type: 'doughnut',
    data: {
      labels: Object.keys(byStatut),
      datasets: [{ data: Object.values(byStatut) }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });

  // Bar zones
  new Chart(document.getElementById('chartZones'), {
    type: 'bar',
    data: {
      labels: byZone.map(z => z.zone ?? '—'),
      datasets: [{ label: 'Dossiers', data: byZone.map(z => z.c) }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });

  // Donut types de service
  new Chart(document.getElementById('chartTypes'), {
    type: 'doughnut',
    data: {
      labels: Object.keys(byType),
      datasets: [{ data: Object.values(byType) }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });
</script>
@stop
