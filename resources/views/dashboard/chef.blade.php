@extends('adminlte::page')

@section('title', 'Tableau de bord - Chef d’équipe')

@section('content_header')
    <h1>Tableau de bord - Chef d’équipe</h1>
    <form method="GET" class="d-flex gap-2 mb-3">
        <input type="date" name="date_from" class="form-control" value="{{ $from }}">
        <input type="date" name="date_to" class="form-control" value="{{ $to }}">
        <button class="btn btn-outline-primary">Appliquer</button>
    </form>
@stop

@section('content')

<div class="row mb-4">
    {{-- KPIs principaux --}}
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-primary text-white">
            <div class="inner">
                <h3>{{ $totalDossiers }}</h3>
                <p>Dossiers total</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-info text-white">
            <div class="inner">
                <h3>{{ $ouverts }}</h3>
                <p>Dossiers ouverts</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-success text-white">
            <div class="inner">
                <h3>{{ $realises }}</h3>
                <p>Dossiers réalisés</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-secondary text-white">
            <div class="inner">
                <h3>{{ $corbeilleCount }}</h3>
                <p>Dossiers Assigner</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Dossiers actifs</h5>
                <p class="card-text fs-3">{{ $activeCount }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Dossiers avec RDV</h5>
                <p class="card-text fs-3">{{ $rdvCount }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Corbeille équipe --}}
<!-- <div class="card mb-4">
    <div class="card-header">Corbeille - dossiers à traiter</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Réf.</th>
                        <th>Abonné</th>
                        <th>Statut</th>
                        <th>Équipe</th>
                        <th>Planifiée</th>
                        <th>Contrainte / Report</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teamInbox as $dossier)
                        <tr>
                            <td>{{ $dossier['ref'] }}</td>
                            <td>{{ $dossier['client'] ?? '—' }}</td>
                            <td>{{ is_object($dossier['statut']) ? $dossier['statut']->value : $dossier['statut'] }}</td>
                            <td>{{ $dossier['team'] ?? '—' }}</td>
                            <td>{{ $dossier['date'] ? \Carbon\Carbon::parse($dossier['date'])->format('d/m/Y H:i') : '—' }}</td>
                            <td>
                                @if($dossier['contrainte'])
                                    <div>Contrainte: {{ $dossier['contrainte'] }}</div>
                                @endif
                                @if($dossier['report'])
                                    <div>Report: {{ \Carbon\Carbon::parse($dossier['report'])->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>
                            <td class="text-end">
                                {{-- Clôturer --}}
                                <form action="{{ route('teams.inbox.close', [$dossier['team_id'], $dossier['id']]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Clôturer</button>
                                </form>

                                {{-- Notifier contrainte --}}
                                <form action="{{ route('teams.inbox.constraint', [$dossier['team_id'], $dossier['id']]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="text" name="contrainte" placeholder="Préciser la contrainte" required class="form-control form-control-sm d-inline w-auto">
                                    <button type="submit" class="btn btn-sm btn-warning">Notifier</button>
                                </form>

                                {{-- Reporter RDV --}}
                                <form action="{{ route('teams.inbox.reschedule', [$dossier['team_id'], $dossier['id']]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="datetime-local" name="date_rdv" required class="form-control form-control-sm d-inline w-auto">
                                    <button type="submit" class="btn btn-sm btn-info">Reporter</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Aucun dossier dans la corbeille</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div> -->

{{-- Derniers dossiers réalisés --}}
<div class="card">
    <div class="card-header">Derniers dossiers réalisés</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Réf.</th>
                        <th>Abonné</th>
                        <th>Statut</th>
                        <th>Équipe</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lastDossiers as $d)
                        <tr>
                            <td>{{ $d->reference }}</td>
                            <td>{{ $d->client?->displayName ?? '—' }}</td>
                            <td>{{ \Illuminate\Support\Str::headline($d->statut?->value ?? $d->statut) }}</td>
                            <td>{{ $d->assignedTeam?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Aucun dossier réalisé récemment</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('dossiers.partials.alert_nouveau_rdv_modal')
@stop
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Vérifie si le tableau contient des RDV
    @if($rdvDossiers->isNotEmpty())
        var rdvModal = new bootstrap.Modal(document.getElementById('rdvModal'));
        rdvModal.show();
    @endif
});
</script>


