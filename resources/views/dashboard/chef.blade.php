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

    <div class="row">
        <div class="col-lg-4">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalDossiers }}</h3>
                    <p>Dossiers total</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $ouverts }}</h3>
                    <p>Dossiers ouverts</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $realises }}</h3>
                    <p>Dossiers réalisés</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Corbeille équipe --}}
    <div class="card">
        <div class="card-header">Corbeille - dossiers à traiter</div>
        <!-- <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Réf.</th>
                            <th>Abonné</th>
                            <th>Statut</th>
                            <th>Equipe</th>
                            <th>Planifiée</th>
                            <th>Contrainte / Report</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teamInbox as $dossier)
                            <tr>
                                <td>{{ $dossier['ref'] }}</td>
                                <td>{{ $dossier['client'] }}</td>
                                <td>{{ \Illuminate\Support\Str::headline($dossier['statut']->value) }}</td>

                                <td>{{ $dossier['team'] }}</td>
                                <td>{{ $dossier['date']?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>
                                    @if ($dossier['contrainte'])
                                        Contrainte: {{ $dossier['contrainte'] }}
                                    @endif
                                    @if ($dossier['report'])
                                        Report: {{ $dossier['report']->format('d/m/Y H:i') }}
                                    @endif
                                </td>
                                <td class="text-end">
                                    {{-- Clôturer --}}
                                    <form action="{{ route('teams.inbox.close', [$dossier['team_id'], $dossier['id']]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Clôturer</button>
                                    </form>

                                    {{-- Notifier contrainte --}}
                                    <form
                                        action="{{ route('teams.inbox.constraint', [$dossier['team_id'], $dossier['id']]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        <input type="text" name="contrainte" placeholder="Préciser la contrainte"
                                            required>
                                        <button type="submit" class="btn btn-sm btn-warning">Notifier contrainte</button>
                                    </form>

                                    {{-- Reporter RDV --}}
                                    <form
                                        action="{{ route('teams.inbox.reschedule', [$dossier['team_id'], $dossier['id']]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        <input type="datetime-local" name="date_rdv" required>
                                        <button type="submit" class="btn btn-sm btn-info">Reporter</button>
                                    </form>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div> -->
    </div>

    {{-- Derniers dossiers réalisés --}}
    <div class="card">
        <div class="card-header">Derniers dossiers réalisés</div>
        <!-- <div class="card-body p-0">
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
                        @foreach ($lastDossiers as $d)
                            <tr>
                                <td>{{ $d->reference }}</td>
                                <td>{{ $d->client?->displayName }}</td>
                                <td>{{ \Illuminate\Support\Str::headline($d->statut?->value ?? $d->statut) }}</td>
                                <td>{{ $d->assignedTeam?->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div> -->
    </div>

@stop
